<?php

namespace ProcessWire;

trait TraitPWCommerceOrderMessage
{
	/**
	 *    send Confirmation.
	 *
	 * @param mixed $orderPage
	 * @return mixed
	 */
	public function ___sendConfirmation($orderPage = null)
	{
		// if no order page, try to get from session
		if (is_null($orderPage)) {
			$orderPage = $this->getOrderPage();
		}

		if (empty($orderPage->id)) {
			// TODO OK?
			// NO ORDER PAGE: ABORT

			return;
		}

		// SET ORDER TO MEMORY
		$this->setOrderPage($orderPage);

		// ----------
		// GOOD TO GO

		$order = $this->getOrder();

		$orderCustomer = $this->getOrderCustomer();

		if (empty($orderCustomer->email)) {
			// NO ORDER CUSTOMER EMAIL: ABORT
			return;
		}
		// ------------
		$orderLineItems = $this->getOrderLineItems();
		// $shopEmail = $this->pwcommerce->getShopEmail();
		// use shop's 'FROM EMAIL ADDRESS' if available
		$shopEmail = $this->pwcommerce->getShopFromEmail();
		if (empty($shopEmail)) {
			$shopEmail = $this->pwcommerce->getShopEmail();
		}

		$orderPaymentStatus = $order->paymentStatus;
		$isOrderGrandTotalComplete = true;

		$orderSubtotal = $this->getOrderLineItemsTotalDiscountedWithTax();

		$orderGrandTotalMoney = $this->getOrderGrandTotalMoney();
		/** @var float $orderGrandTotalAmount */
		$orderGrandTotalAmount = $this->pwcommerce->getWholeMoneyAmount($orderGrandTotalMoney);

		### EMAIL CONFIRMATION ###

		######## prepare email variables ######

		$emailOptions = [];


		// mail to order customer
		// $mail->to($orderCustomer->email);
		$emailOptions['to'] = $orderCustomer->email;

		// @note: NOT OK! this will clear the $mail->to() set above! $mail->to also doesn't work for some reason for checking


		// NOTE: USES SPECIAL 'FROM' EMAIL ADDRESS; SEE ISSUE IN FORUMS ABOUT SENDING EMAIL TO SELF!
		// @see: https://processwire.com/talk/topic/28339-should-order-confirmation-emails-also-be-received-by-store/
		$emailOptions['from'] = $shopEmail;
		$mailSubject = sprintf(__("Your Order - #%s"), $orderPage->id);
		$emailOptions['subject'] = $mailSubject;

		// ====================
		$t = $this->pwcommerce->getPWCommerceTemplate("email-invoice.php");
		$t->set("order", $order);
		$t->set("orderLineItems", $orderLineItems);
		$t->set("orderCustomer", $orderCustomer);
		// -------------
		$t->set("isOrderGrandTotalComplete", $isOrderGrandTotalComplete);
		$t->set("orderSubtotal", $orderSubtotal);
		$t->set("orderGrandTotal", $orderGrandTotalAmount);

		// -----------
		// ADD DOWNLOADS IF ORDER IS FULLY PAID
		$isOrderWithDownloads = false;
		if ((int) $orderPaymentStatus === PwCommerce::PAYMENT_STATUS_PAID) {
			/** @var array $downloads */
			$downloads = $this->pwcommerce->getDownloadCodesByOrderID($orderPage->id);
			if (!empty($downloads)) {
				$isOrderWithDownloads = true;
				$t->set("downloads", $downloads);
			}
		}

		$emailOptions['bodyHTML'] = $t->render();

		// ====================
		$t = $this->pwcommerce->getPWCommerceTemplate("invoice-content-text.php");
		$t->set("order", $order);
		$t->set("orderLineItems", $orderLineItems);
		$t->set("orderCustomer", $orderCustomer);
		$emailOptions['body'] = $t->render();
		/** @var array $result */
		$result = $this->pwcommerce->sendEmail($emailOptions);


		// ++++++++++++++++++
		### ORDER NOTE ###
		$orderPage->of(false);
		$defaultNote = $this->_("Order confirmation emailed to");
		if ((int) $orderPaymentStatus === PwCommerce::PAYMENT_STATUS_PAID) {
			// ==============
			if ($isOrderWithDownloads) {
				// order has downloads note
				$note = sprintf(__("Order confirmation with download links emailed to %s."), $orderCustomer->email);
			} else {
				// order does NOT have downloads note
				$note = sprintf(__('%1$s %2$s.'), $defaultNote, $orderCustomer->email);
			}
		} else {
			// ==============
			// ORDER NOT YET PAID: ADD THIS NOTE
			// ------------
			$note = sprintf(__('%1$s %2$s.'), $defaultNote, $orderCustomer->email);
		}
		$orderPage = $this->addNote($note, $orderPage);
		$orderPage->save();
	}

	/**
	 * Add Note.
	 *
	 * @param mixed $orderNoteText
	 * @param mixed $orderPage
	 * @return mixed
	 */
	public function addNote($orderNoteText, $orderPage)
	{
		$note = $this->pwcommerce->buildNote($orderNoteText);
		$orderPage->pwcommerce_notes->add($note);
		return $orderPage;
	}
}
