<?php

namespace ProcessWire;

trait TraitPWCommerceCreateOrder
{

	/**
	 * Create Order.
	 *
	 * @return mixed
	 */
	public function createOrder() {

		// NOTE IF NOT  ROUNDED TO TWO DECIMAL PLACES,  PAYPAL FATAL ERROR!
		$orderGrandTotalMoney = $this->getOrderGrandTotalMoney();
		$amount = $this->pwcommerce->getWholeMoneyAmount($orderGrandTotalMoney);

		// TODO: @KONGONDO - FOR NOW HARDCODED TO PP!
		// create new payment instance
		$payment = $this->getPaymentClass();
		if (empty($payment)) {
			$this->setPaymentProvider($this->session->paymentProviderID);
			$payment = $this->getPaymentClass();
		}

		// @KONGONDO
		// TODO: DELETE WHEN DONE; NOT NEEDED! WE STAY ON SAME PAGE THROUGHOUT
		// $url = $this->page->httpUrl;
		// $url = "https://pwcommerce1.pw3/en/pwcommerce-1-products-tests/checkout/";
		// $orderId = $order->get("id");
		// $payment->setProcessUrl($url . "process/" . $orderId . "/");
		// $payment->setFailureUrl($url . "fail/");
		// $payment->setCancelUrl($url . "cancel/");

		// ---------------
		// TODO @KONGONDO
		$createOrderValues = new WireData();
		$createOrderValues->set('amount', $amount);
		$createOrderValues->set('referenceID', $this->getOrderPage()->id);
		$response = $payment->createOrder($createOrderValues);

		// -----------
		// TODO: ERROR HANDLING HERE OR IN PWCommerce??
		return $response;
	}
}
