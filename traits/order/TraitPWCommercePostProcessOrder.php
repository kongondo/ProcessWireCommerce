<?php

namespace ProcessWire;

trait TraitPWCommercePostProcessOrder
{
	private function postCaptureOrder($response)
	{



		// ########
		//  PROCESS PAID ORDER SERVER-SIDE: this will ->
		//  - populate $order->pwcommerce_paid = time()
		//  - add order notes
		//  - publish order
		// #########

		// ****************
		// 1. Set Order Statuses
		// TODO: HERE OR IN PROCESSPAYMENT?
		// SET ORDER STATUS as 'OPEN', PAYMENT STATUS AS 'PAID' and SHIPMENT STATUS AS 'AWAITING FULFILMENT'
		// @note: this has to be done before sendConfirmation() as some of its processes depend on order status. processPayment() will call sendConfirmation(), hence this coming first
		$orderStatus = PwCommerce::ORDER_STATUS_OPEN;
		$paymentStatus = PwCommerce::PAYMENT_STATUS_PAID;
		$fulfilmentStatus = PwCommerce::FULFILMENT_STATUS_AWAITING_FULFILMENT;

		$this->setOrderStatusesAfterOrderCompletion($orderStatus, $paymentStatus, $fulfilmentStatus);


		// ****************
		// 2. Process Payment
		// @note: this will call completeOrder() and sendConfirmation()
		$this->processPayment();


		// --------
		// add a redirect url for JS @note: might also add to markup?? but that would mean controlling dynamism there, i.e. success or fail. Need to do that here server-side!($response, 'result')
		// TODO @note: for PayPal only
		// if (is_object($response->result)) {
		if (property_exists($response, 'result')) {
			$urlSegment = $this->session->checkoutSuccessUrlSegment;
			$response->result->redirectURL = $this->session->checkoutPageHttpURL . $urlSegment . "/";
		}
		// TODO OK? GETTING PHP 8.2 ERROR HERE ABOUT DEPRECATION OF...
		$response->success = true;

		// --------
		return $response;
	}
}
