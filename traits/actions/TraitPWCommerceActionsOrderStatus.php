<?php

namespace ProcessWire;

trait TraitPWCommerceActionsOrderStatus
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ORDER STATUS ~~~~~~~~~~~~~~~~~~

	// set a single order status
	private function manuallySetOrderStatusAction() {

		$input = $this->actionInput;

		$orderPageID = (int) $input->pwcommerce_order_status_order_id_for_selected_action_apply;
		$statusCode = (int) $input->pwcommerce_order_status_selected_action_apply;
		$paymentMethodID = (int) $input->pwcommerce_order_status_payment_method_for_selected_action_apply;
		//
		// 'pwcommerce_order_status_note_for_selected_action_apply': ''
		// pwcommerce_order_status_selected_action_apply: '6004'
		// 'pwcommerce_order_status_order_id_for_selected_action_apply': '3338'
		// pwcommerce_order_status_action_context: 'orders'
		// pwcommerce_order_status_refunded_amount_for_selected_action_apply ||
		// pwcommerce_order_status_paid_amount_for_selected_action_apply
		// pwcommerce_order_status_payment_method_for_selected_action_apply
		// pwcommerce_order_status_notify_customer_for_selected_action_apply

		$sanitizer = $this->wire('sanitizer');
		$pages = $this->wire('pages');

		// -------------------
		// ERROR CHECKING

		$orderPage = $pages->get("template=" . PwCommerce::ORDER_TEMPLATE_NAME . ",id={$orderPageID}");

		if ($orderPage instanceof NullPage) {
			// order page not found
			// $error = $this->_('Error encountered. Order page not found.');
			// $result['notice'] = "<p class='text-red-500'>" . $error . "</p>";
			// return $result;
			return null;
		}

		// invalid order status action
		$isValidStatusCode = $this->pwcommerce->isValidStatusCode($statusCode);

		if (empty($isValidStatusCode)) {
			// invalid order status code/flag
			// $error = $this->_("Error encountered. Invalid order status action.");
			// $result['notice'] = "<p class='text-red-500'>" . $error . "</p>";
			// return $result;
			return null;
		}

		// all four payments needing an amount and/or payment method
		$specialPaymentStatusCodes = [
			PwCommerce::PAYMENT_STATUS_PARTIALLY_PAID,
			PwCommerce::PAYMENT_STATUS_PAID,
			PwCommerce::PAYMENT_STATUS_PARTIALLY_REFUNDED,
			PwCommerce::PAYMENT_STATUS_REFUNDED,
		];

		// these will always need a payment method to be specified
		$isNeedPaymentMethodStatusCodes = [
			PwCommerce::PAYMENT_STATUS_PARTIALLY_PAID,
			PwCommerce::PAYMENT_STATUS_PAID,

		];
		// these will always need a payment amount to be specified
		$isNeedPaymentAmountStatusCodes = [
			PwCommerce::PAYMENT_STATUS_PARTIALLY_PAID,
			PwCommerce::PAYMENT_STATUS_PARTIALLY_REFUNDED,

		];

		// order status action is of type paid OR partially paid but payment method not sent
		if (in_array($statusCode, $isNeedPaymentMethodStatusCodes) && empty($paymentMethodID)) {
			// empty payment method page ID
			// $error = $this->_("Error encountered. No payment method id found.");
			// $result['notice'] = "<p class='text-red-500'>" . $error . "</p>";
			// return $result;
			return null;
		}

		// order status action is of type partial refund OR partial payment but no amount sent
		if (in_array($statusCode, $isNeedPaymentAmountStatusCodes)) {
			$amountProperty = $statusCode == PwCommerce::PAYMENT_STATUS_PARTIALLY_REFUNDED ? 'pwcommerce_order_status_refunded_amount_for_selected_action_apply' : 'pwcommerce_order_status_paid_amount_for_selected_action_apply';
			$paymentAmount = (float) $input->$amountProperty;
			if (empty($paymentAmount)) {
				// empty payment
				// $error = $this->_("Error encountered. No payment amount specified.");
				// $result['notice'] = "<p class='text-red-500'>" . $error . "</p>";
				// return $result;
				return null;
			}
		}

		// order status action is of type paid or part paid but payment method does not exist
		if (in_array($statusCode, $isNeedPaymentMethodStatusCodes)) {
			$paymentMethodPage = $pages->get("template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",id={$paymentMethodID}");

			if ($paymentMethodPage instanceof NullPage) {
				// payment method page not found
				// $error = $this->_('Error encountered. Specified payment method not found.');
				// $result['notice'] = "<p class='text-red-500'>" . $error . "</p>";
				// return $result;
				return null;
			}
		}

		// -------------------
		// GOOD TO GO
		# PROCESSING #
		//
		// - only the $orderPage will be changed (at this moment) TODO WILL CHANGE WHEN WE ADD THE FEATURES Order Returns and Order Refunds
		// - only 2 fields to change, i.e. 'pwcommerce_order' AND 'pwcommerce_notes'
		// - At least 2 changes done:
		// (i) ONE OF subfields in 'pwcommerce_order' (all ints)
		// order_status
		// order_fulfilment_status
		// order_payment_status
		// (ii) [SYSTEM] NOTE in 'pwcommerce_notes' => 'system' note about the changed status
		// ****** OPRIONAL *******
		// (iii) if admin included a custom note about the change =>
		// [ADMIN] NOTE in 'pwcommerce_notes' => 'admin' custom note about the changed status
		// ---------------------------
		// @NOTE:  for now, we don't amend the payment method in the original order. e.g. if it was invoice, and invoice is settled later using Stripe, we don't change that. For now, we handle it at the notes level
		//

		// +++++++++++++++++++

		# 1. AMEND ORDER STATUS
		// determine which of 'order', 'payment' or 'fulfilmen/shipment' status to change
		// get status type

		$statusType = $this->pwcommerce->getOrderStatusTypeByStatusCode($statusCode);

		// set the subfield property (in WireData) to change
		$subfieldProperty = 'orderStatus'; // default to 'order status'
		if ($statusType === 'payment_status') {
			$subfieldProperty = 'paymentStatus';
		} elseif ($statusType === 'fulfilment_status') {
			$subfieldProperty = 'fulfilmentStatus';
		}
		// set value to order field
		// just in case - but maybe not really needed
		$orderPage->of(false);
		/** @var WireData $orderPageOrderField */
		$orderPageOrderField = $orderPage->get(PwCommerce::ORDER_FIELD_NAME);
		$orderPageOrderField->set($subfieldProperty, $statusCode);

		// +++++++++++++++++++

		# 2. ADD ADMIN NOTE (IF SENT/if applicable)
		// get and clean the custom note
		$adminNote = $sanitizer->sanitize($input->pwcommerce_order_status_note_for_selected_action_apply, 'textarea,entities');
		if (!empty($adminNote)) {
			// =========
			// ADD ADMIN NOTE ABOUT ORDER STATUS CHANGE
			$noteText = $adminNote;
			/** @var WireData $note */
			$noteType = 'admin';
			$userID = $this->wire('user')->id;
			$note = $this->pwcommerce->buildNote($noteText, $noteType, $userID);
			$orderPage->pwcommerce_notes->add($note);
		}

		// +++++++++++++++++++

		# 3. ADD SYSTEM NOTE
		// get status title/name
		$statusName = $this->pwcommerce->getOrderStatusByStatusCode($statusCode);
		// =========
		// ADD SYSTEM NOTE ABOUT ORDER STATUS CHANGE

		if (in_array($statusCode, $specialPaymentStatusCodes)) {
			/***** SPECIAL PAYMENT STATUSES ************/
			if ($statusCode == PwCommerce::PAYMENT_STATUS_PARTIALLY_REFUNDED) {
				// add system note about partial refund amount
				$amount = (float) $input->pwcommerce_order_status_refunded_amount_for_selected_action_apply;
			} elseif ($statusCode === PwCommerce::PAYMENT_STATUS_REFUNDED) {
				// add system note about full refund amount
				$amount = $orderPageOrderField->totalPrice;
			} elseif ($statusCode === PwCommerce::PAYMENT_STATUS_PARTIALLY_PAID) {
				// add system note about partial paid amount
				$amount = (float) $input->pwcommerce_order_status_paid_amount_for_selected_action_apply;
			} elseif ($statusCode === PwCommerce::PAYMENT_STATUS_PAID) {
				// add system note about full paid amount
				$amount = $orderPageOrderField->totalPrice;
			}

			// ========
			$amountFormattedAsCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($amount);
			$noteText = sprintf(__('Added status \'%1$s\' to order (amount %2$s).'), $statusName, $amountFormattedAsCurrency);
			if (in_array($statusCode, $isNeedPaymentMethodStatusCodes)) {
				// if part or full pay, indicate the payment method as well
				$noteText .= " " . sprintf(__("Paid using %s."), $paymentMethodPage->title);
			}
		} else {
			/***** NON-SPECIAL PAYMENT STATUSES ************/
			// system note does not need amount or payment method
			$noteText = sprintf(__("Added status '%s' to order."), $statusName);
		}

		/** @var WireData $note */
		$note = $this->pwcommerce->buildNote($noteText);
		$orderPage->pwcommerce_notes->add($note);

		// +++++++++++++++++++

		# 4. PUBLISH ORDER (if applicable)
		// -----
		// if order status is NOT 'DRAFT'
		// also publish order -> signifies no longer a draft
		// TODO? ok?
		if ($statusCode !== PwCommerce::ORDER_STATUS_DRAFT) {
			// NON-DRAFT ORDER: PUBLISH IT
			$orderPage->removeStatus(Page::statusUnpublished);

		} else {
			// DRAFT ORDER: UNPUBISH IT
			$orderPage->addStatus(Page::statusUnpublished);

		}

		// +++++++++++++++++++

		# 5. SAVE THE ORDER PAGE
		$orderPage->save();

		// +++++++++++++++++++

		# 6. SEND GENERIC EMAIL TO CUSTOMER ABOUT UPDATE (IF SENT/if applicable)
		// TODO NOT IN USE FOR NOW! BETTER TO SEND A COMPLETE MESSAGE TO CUSTOMER INSTEAD OF A VERY SHORT ONE SUCH AS 'your order has been marked as pendind' without giving further details!
		// $notifyCustomer = (int) $input->pwcommerce_order_status_notify_customer_for_selected_action_apply;
		// $isNotifyCustomer = false;
		// if (!empty($notifyCustomer)) {
		// 	$isNotifyCustomer = true;
		// 	$this->manuallySetOrderStatusActionNotifyCustomer($orderPage, $statusName);
		// }

		// +++++++++++++++++++

		# 7. SUCCESS NOTICE
		// $notice = sprintf(__("Successfully applied status '%s' to order."), $statusName);
		$notice = sprintf(__("Marked order as %s."), $statusName);
		// ---
		$result = [
			'notice' => $notice,
			'notice_type' => 'success',
			'special_redirect' => "/view/?id={$orderPage->id}"
		];

		// +++++++++++++++++++
		# 8. POST PROCESS AFTER ORDER STATUS CHANGE
		// if applicable

		$isManuallySetOrderStatusNeedsPostProcess = $this->isManuallySetOrderStatusNeedsPostProcess($statusCode);
		if (!empty($isManuallySetOrderStatusNeedsPostProcess)) {
			$this->postProcessOrderStatus($statusCode, $orderPage);
		}

		$this->pwcommerce->manuallySetOrderStatusActionHook($orderPage, $statusName, $statusCode);

		//-------------
		return $result;
	}



	private function manuallySetOrderStatusActionNotifyCustomer(Page $orderPage, string $statusName) {
		// TODO NOT IN USE FOR NOW! BETTER TO SEND A COMPLETE MESSAGE TO CUSTOMER INSTEAD OF A VERY SHORT ONE SUCH AS 'your order has been marked as pending' without giving further details!
		return;
		// @NOTE: ONLY APPLIES TO NON-ORDER STATUSES
		// SEND CUSTOMER AN EMAIL ABOUT THEIR ORDER UPDATE
		// $notice = sprintf(__("Marked order as %s."), $statusName);
	}

	private function isManuallySetOrderStatusNeedsPostProcess($statusCode) {
		$orderStatusesNeedingPostProcessing = $this->orderStatusesNeedingPostProcessing();
		$isManuallySetOrderStatusNeedsPostProcess = in_array($statusCode, $orderStatusesNeedingPostProcessing);
		// ------
		return $isManuallySetOrderStatusNeedsPostProcess;
	}

	private function orderStatusesNeedingPostProcessing() {
		return [
				// order status (2000): cancelled - need to restock if product tracks inventory
			PwCommerce::ORDER_STATUS_CANCELLED
		];
	}

	private function postProcessOrderStatus($statusCode, Page $orderPage) {
		// @note: for now we only have one order status that needs post processing
		// this is 'ORDER STATUS: CANCELLED' The code is 2000
		if ($statusCode === PwCommerce::ORDER_STATUS_CANCELLED) {
			$this->postProcessOrderStatusRestockInventory($orderPage);
		}
	}


	# TODO DELETE IF NO LONGER IN USE
	private function actionMarkOrderAs($isSingle = false) {
		// TODO:@UPDATE: SATURDAY 2 2 APRIL 2023 -> REMOVED THESE FROM BULK EDIT SINCE WE NOW HANDLE ALL STATUSES; THE LIST IS LONG HENCE DOING THIS IN SINGLE ORDER VIEW
		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!

		$isShopUsePaymentProvidersFeature = $this->pwcommerce->isShopUsePaymentProvidersFeature();

		// TODO -> HERE SIGNAL IF SINGLE VERSUS BULK EDIT! WE CAN THEN REDIRECT CORRECTLY!
		// process the settings
		$input = $this->actionInput;
		$sanitizer = $this->wire('sanitizer');

		// #############
		// @note: WE CAN GET HERE VIA SINGLE EDIT OR BULK EDIT

		if (empty($isSingle)) {
			// BULK ORDERS ACTIONS
			// orders to action
			$items = $this->items;
			// action
			$markOrderAsAction = $this->action;
		} else {
			// SINGLE ORDER ACTIONS
			// order to action
			$singleOrderToActionID = (int) $input->pwcommerce_order_action_mark_order_as_order_id;
			$items = [];
			$items[] = $singleOrderToActionID;
			// @note: we set to class property so we can retrieve as pages in later using $this->getItemsToAction()
			$this->items = $items;
			// action
			//------------------
			// WE NEED A 'MARK ORDER AS' ACTION TO BE SPECIFIED
			// @note: we only allow these 'mark order as' action values
			// TODO ADD MORE STATUSES IN FUTURE AS NEEDED!
			$allowedMarkOrderAsActionValues = ['payment_mark_as_pending', 'payment_mark_as_paid', 'shipment_delivered'];
			$markOrderAsAction = $sanitizer->option($input->pwcommerce_order_action_mark_order_as_action_value, $allowedMarkOrderAsActionValues);
			// set action for global use
			$this->action = $markOrderAsAction;
		}

		// ERROR: NO ITEMS
		if (empty($items)) {
			return null;
		}

		// ERROR: NO ACTION
		if (empty($this->action)) {
			// if no 'mark order as' action sent, abort
			return null;
		}

		// ###############

		// ----------
		// IF MARKING ORDER AS PAID, WE NEED A PAYMENT METHOD SPECIFIED
		if ($this->action === 'payment_mark_as_paid') {
			$paymentProviderID = (int) $input->pwcommerce_order_action_mark_order_as_payment_method;
			if (!empty($isShopUsePaymentProvidersFeature)) {
				// SHOP USES PAYMENT PROVIDERS
				// -------------------------
				// get the payment provider page
				$paymentProviderPage = $this->wire('pages')->get($paymentProviderID);
				// ERROR: CANNOT FIND SPECIFIED PAYMENT PROVIDER
				// we didn't get the page; abort
				// TODO: meaningful error? e.g. payment provider page not found?
				if (empty($paymentProviderPage->id)) {
					return null;
				}
				$markOrderAsPaidPaymentMethod = $paymentProviderPage->title;
			} else {
				// SHOP DOES NOT USE PAYMENT PROVIDERS
				// -------------------------
				// we default to payment title/method 'Custom Payment' AND payment provider ID = 0
				// @see: PWCommerceProcessRenderOrders::getModalMarkupForConfirmMarkOrderAsPaidPaymentMethodSelectField
				$markOrderAsPaidPaymentMethod = $this->_('Custom Payment');
			}

			// ERROR: PAYMENT PROVIDER HAS NO TITLE!
			if (empty($markOrderAsPaidPaymentMethod)) {
				// if payment provider has no title: no 'mark order as paid' payment method sent, abort
				return null;
			}
		}

		// ---------
		// good to go
		$pages = $this->getItemsToAction();
		$i = 0;

		// @note: depends on $this->action, so must come after that getting set above!
		$orderActionNotice = $this->getOrderActionNotice();

		// ORDER(S) TO ACTION
		// action each item
		foreach ($pages as $page) {
			// skip if page is locked
			if ($page->isLocked()) {
				continue;
			}

			// ===========

			// GOOD TO GO

			// get the order from the order page
			/** @var WireData $order */
			$order = $page->get(PwCommerce::ORDER_FIELD_NAME);
			// if paid or pending, set order status as 'open' / 'pending/
			// this means it is no longer a draft order (1000)
			$order->orderStatus = PwCommerce::ORDER_STATUS_OPEN; // aka 'pending'
			// if paid or pending, set order fulfilment as 'awaiting fulfilment'
			$order->fulfilmentStatus = PwCommerce::FULFILMENT_STATUS_AWAITING_FULFILMENT;
			// set payment status: awaiting payment OR paid
			// ------
			if ($this->action === 'payment_mark_as_pending') {
				// mark order as pending
				$paymentStatus = PwCommerce::PAYMENT_STATUS_AWAITING_PAYMENT;
			} else if ($this->action === 'payment_mark_as_paid') {
				// mark order as paid
				$paymentStatus = PwCommerce::PAYMENT_STATUS_PAID;
			} else if ($this->action === 'shipment_delivered') {
				// @note: we mark order as
				// TODO!!! -> is this ok? to assume that delivery means order paid as well? YES; FOR NOW; OTHERWISE THEY SHOULD WAIT WITH THE 'MARKING'!??
				// mark order as paid
				$paymentStatus = PwCommerce::PAYMENT_STATUS_PAID;
				// mark order shipment as delivered/fulfilled
				$order->fulfilmentStatus = PwCommerce::FULFILMENT_STATUS_FULFILLED;
				// mark order status as completed
				$order->orderStatus = PwCommerce::ORDER_STATUS_COMPLETED;
			}

			// ---------
			$order->paymentStatus = $paymentStatus;
			// if marking order as paid: set payment method TODO REVISIT THIS! e.g. 'paypal' vs 'PayPal'!
			// TODO NOW UNSURE ABOUT THIS! SHOULD WE BE CHANING IT OR JUST SAVE TO ORDER HISTORY?!!!
			if ($this->action === 'payment_mark_as_paid') {
				$order->paymentMethod = $markOrderAsPaidPaymentMethod;
			}

			// =========
			// ADD NOTE ABOUT ORDER STATUS CHANGE
			$noteText = sprintf(__("Order marked as %s."), $orderActionNotice);
			/** @var WireData $note */
			$note = $this->pwcommerce->buildNote($noteText);
			$page->pwcommerce_notes->add($note);

			// -----
			// also publish order -> signifies no longer a draft
			// TODO? ok?
			$page->removeStatus(Page::statusUnpublished);
			//------------------------------
			$i++;
			// save the page
			$page->save();
		}

		// --------------------
		// NOTICES
		if ($isSingle) {
			// single edit notice
			$notice = sprintf(__("Marked order as %s."), $orderActionNotice);
		} else {
			// prepare messages for bulk edit
			$notice = sprintf(_n('Marked %1$d item as %2$s.', 'Marked %1$d items as %2$s.', $i, $orderActionNotice), $i, $orderActionNotice);
		}
		// PREPARE RESULTS
		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO? check if really saved first?
			// TODO -> ONLY IF SINGLE EDIT!
			// 'special_redirect' => "/view/?id={$page->id}"
		];

		if ($isSingle) {
			$pageID = $pages->first()->id;
			$result['special_redirect'] = "/view/?id={$pageID}";
		}

		//-------
		return $result;
	}
}
