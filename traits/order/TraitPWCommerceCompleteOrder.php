<?php

namespace ProcessWire;

trait TraitPWCommerceCompleteOrder {
	public function getCompletedOrder() {

		$response = new WireData();
		$error = null;
		$isErrorFound = false;

		// --------
		$orderPage = $this->getOrderPage();

		// TODO: CHECK IF INDEED ORDER COMPLETED SUCCESSFUL BEFORE RETURNING!

		if (empty($orderPage) || empty($orderPage->id)) {

			$orderId = (int) $this->input->urlSegment2;

			$o = $this->pages->get($orderId);
			// ---------------------------
			if (!empty($o->id) && $o->template->name === PwCommerce::ORDER_TEMPLATE_NAME) {
				$this->setOrderPage($o);
			} else {
				$error = $this->_('Order not found.');
				$isErrorFound = true;
				throw new Wire404Exception("Order not found");
			}
		}

		####################
		// ERROR CHECK: ORDER IS NULL PAGE OR HAS WRONG TEMPLATE
		if (!empty($isErrorFound)) {
			$response->message = $error;
			$response->success = false;
			$response->debug = 'Order was not found. A NullPage and/or a page with a wrong template was returned.';
			return $response;
		}
		####################

		// ------------

		// TODO @KONGONDO - BETTER CHECKS HERE! E.G. ORDER PAYMENT STATUS FOR NON-INVOICE ONES!
		if ($orderPage->isUnpublished()) {
			$error = $this->_('Order not found');
			$isErrorFound = true;
			throw new Wire404Exception("Order not found.");
		}

		####################
		// ERROR CHECK: ORDER IS UNPUBLISHED (hence incomplete)
		if (!empty($isErrorFound)) {
			$response->message = $error;
			$response->success = false;
			$response->debug = 'Order is still unpublished. It means it is incomplete.';
			return $response;
		}
		####################

		$orderID = $this->session->get(PwCommerce::ORDER_LOST_SESSION_ORDER_ID_NAME);

		$compareOrderID = $this->session->orderId;

		if (!$compareOrderID) {
			// SESSION IS LOST
			// get ORDER ID from 'special session variable for lost sessions'
			$compareOrderID = $this->session->get(PwCommerce::ORDER_LOST_SESSION_ORDER_ID_NAME);
		}

		// TODO - WE COULD ALSO GRAB FROM $input->get('order_id');

		// if ((int) $orderPage->id !== $this->session->orderId) {
		if ((int) $orderPage->id !== (int) $compareOrderID) {
			$error = $this->_('Order not found.');
			$isErrorFound = true;
			throw new Wire404Exception("Order not found");
		}

		####################
		// TODO DO WE NEED THIS? WE WON'T REACH HERE DUE TO 404!
		// ERROR CHECK: ORDER ID AND ORDER SESSION ID DO NOT MATCH
		if (!empty($isErrorFound)) {
			$response->message = $error;
			$response->success = false;
			$response->debug = 'Order ID and Session ID mismatch.';
			return $response;
		}
		####################

		// *** GOOD TO GO ***

		$response = new WireData();

		$orderGrandTotalMoney = $this->getOrderGrandTotalMoney();

		$orderGrandTotalAmount = $this->pwcommerce->getWholeMoneyAmount($orderGrandTotalMoney);

		/** @var WireData $this->getOrder() */
		$response->order = $this->getOrder();
		/** @var WireArray $this->getOrderLineItems()*/
		$response->orderLineItems = $this->getOrderLineItems();
		/** @var WireData $this->getOrderCustomer() */
		$response->orderCustomer = $this->getOrderCustomer();
		/** @var float $this->getOrderLineItemsTotalDiscountedWithTax() */
		$response->orderSubtotal = $this->getOrderLineItemsTotalDiscountedWithTax();
		/** @var float $orderGrandTotalAmount */
		$response->orderGrandTotal = $orderGrandTotalAmount;
		$response->success = true;
		// TODO: rephrase?
		$response->message = $this->_("Order was successful.");

		// REMOVE ORDER SESSIONS
		$this->removeOrderSessions();
		// ==============
		// DELETE THE ORDER CACHE
		$this->deleteOrderCache($orderPage->id);

		// doesn't show anything here! -> maybe hook save in ready?
		// $notices = $this->wire->errors();

		// ---------
		return $response;
	}

	private function completeOrder() {
		// @note: at this point $input->post has been emptied

		$orderPage = $this->getOrderPage();

		// if (!$orderPage || !$orderPage->id || $this->isCartEmpty()) {
		// CHECK IF WE HAVE AN ORDER PAGE
		if (!$orderPage || !$orderPage->id) {
			// $this->session->redirect($this->page->url);
			// $this->session->redirect('/');
			// no order page: return early
			return;
		}

		// CHECK IF ORDER PAGE IS PUBLISHED (order completed)
		// && PAYMENT STATUS IS PAID
		if (empty($orderPage->isUnpublished())) {
			// TODO CAN ALSO CHECK $order ITSELF?
			$order = $this->getOrder();

			# 3. check $order itself as well
			$isOrderPaid = (int) $order->paymentStatus === (int) PwCommerce::PAYMENT_STATUS_PAID;

			if ($isOrderPaid) {
				// order is published and is paid: return early
				return;
			}
		}
		// Add note about successful order
		$orderPage->of(false);

		// ADD ORDER COMPLETE NOTE
		$note = $this->_("Order created");
		$orderPage = $this->addNote($note, $orderPage);
		// --------------

		// ORDER COMPLETED SUCCESSFULLY
		// PUBLISH THE ORDER PAGE
		// Successful orders are published pages
		$orderPage->removeStatus(Page::statusUnpublished);
		$orderPage->save();

		// --------------

		$order = $this->getOrder();

		$orderLineItems = $this->getOrderLineItems();

		$this->pwcommerce->createDownloadCodesForOrder($order, $orderLineItems);

		// ==============
		// EMPTY CART AFTER SUCCESSFUL ORDER COMPLETEION
		// Empty the current cart and session data
		$this->emptyCart();

		// =========
		// DELETE ORDER LINE ITEMS THAT WERE 'ABANDONED'
		// these are line items that were removed from the basket post-order-confirmation, i.e. basket was edited and order re-confirmed
		// they have a hidden status
		// TODO FOR FUTURE RELEASE ALSO PROCESS STATUSES! E.G. DIGITAL + FULL PAYMENT, ETC
		$this->postProcessOrderLineItems();

		// =========
		// UPDATE GLOBAL USAGE OF REDEEMED DISCOUNTS
		$this->updateGlobalUsageOfRedeemedDiscounts();

		// ---------------------------
		// TODO @NOTE: MOVE TO POST PROCESSING SO WE PROCESS THEM IN ONE GO!
		// UPDATE PRODUCT QUANTITIES FOR PRODUCTS (including variants) THAT TRACK INVENTORY
		// $this->updateOrderProductsQuantities();

		// FOR HOOKING ONLY
		$this->orderCompletedHook($orderPage, $orderLineItems);

		return $orderPage;
	}

	private function removeOrderSessions() {
		// @note: called by getCompletedOrder()
		$sessionNames = [
			# order
			'orderId',
			'isInvoiceOrder',
			// ----------
			// NOTE: WE REMOVE LAST
			//'lostSessionOrderID',
			// ----------
			//  help track instances when BASKET/CART changes AFTER an order has been previously confirmed but not completed
			'isOrderConfirmed',
			'removedProductIDsForLineItems',
			# payment
			'paymentProviderTitle',
			'paymentProviderID',
			'stripePaymentIntentID',
			# shipping
			'shippingAddressCountryID',
			'matchedShippingZoneID',
			'matchedShippingZoneRatesIDs',
			'isMatchedMultipleShippingRates',
			'selectedMatchedShippingRateID',
			# customer
			'isSavedOrderCustomer',
			# checkout
			'checkoutPageID',
			'checkoutPageURL',
			'checkoutPageHttpURL',
			'checkoutSuccessUrlSegment',
			'checkoutConfirmationUrlSegment',
			'checkoutShippingUrlSegment',
			'checkoutCancelUrlSegment',
			# discounts and gift cards
			// TODO DO WE NEED MOVE FOR VALUES FOR DISCOUNTS/GIFT CARDS OR SAVE ARRAY TO SESSION?
			// ---
			// REDEEMED GIFT CARDS
			// ids of validly redeemed gift cards (codes) for this order session
			'redeemedGiftCardsIDs',

			// REDEEMED DISCOUNTS
			// ids of validly redeemed discount (codes) for this order session
			'redeemedDiscountsIDs',
			// nested array of (retrieved as WireArray) cart items/order line items and their applied discounts
			'redeemedDiscounts',

		];

		foreach ($sessionNames as $sessionName) {
			$this->session->remove($sessionName);
		}
	}
}
