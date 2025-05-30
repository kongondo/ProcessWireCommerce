<?php

namespace ProcessWire;

trait TraitPWCommerceCaptureOrder {

	public function captureOrder($paymentOrderID = 0, $debug = false) {
		// TODO TESTING ONLY! DELETE WHEN DONE
		$input = $this->wire('input');

		// @see TraitPWCommercePayment::getPaymentClass
		// create new payment instance
		$payment = $this->getPaymentClass();

		if (empty($payment)) {

			// if here, it most likely means we have 'lost the order session'
			// we attempt to get the order details using the input->get->order_id from the order cache
			// we will also verify other details

			// @see TraitPWCommercePayment::setPaymentProvider
			$this->setPaymentProvider($this->session->paymentProviderID);

			$payment = $this->getPaymentClass();
		}

		// ---------------
		/** @var obj $response */
		$response = $payment->captureOrder($paymentOrderID, $debug);

		// -----------
		// TODO: ERROR HANDLING HERE OR IN PWCommerce?? - HERE! + processing!
		// $this->processPayment()

		// PROCESS PAYMENT CAPTURE RESPONSE
		// CAPTURE ORDER (i.e. take payment)
		// - of interest are:
		// $response->statusCode: e.g. 201 is good TODO @see PayPal status codes
		// $response->result: this is the big one!
		// $response->result->id : THE PP CREATED ORDER ID
		// $response->result->status: HERE WE WANT'COMPLETED'
		// $response->result->purchase_units:
		// THIS IS AN ARRAY:
		// + WE WANT THE FIRST ITEM in the array
		// + it is a stdClass object
		// + it has several properties we need to confirm order paid + corrent amount paid
		// + $purchaseUnits = $response->result->purchase_units
		// + $purchaseUnits->reference_id: INT -> we set this ourselves to our $order->id when we created the order
		// + $purchaseUnits->invoice_id: INT -> - DITTO -
		// @note: can also get amount from $purchaseUnits->payments (stdClass)
		// + $amount = $purchaseUnits->amount
		// + $amountCurrency = $amount->currency: STR: WE NEED THIS TO MATCH OUR SHOP'S/TRANSACTION CURRENCY
		// + $amountValue = $amount->value: FLOAT: WE NEED THIS TO MATCH OUR ORDER AMOUNT/TOTAL!
		// if above two don't validate, irrespective of status, it is a fail (?) dispute?

		if ($this->isSuccessfulPaymentCapture($response)) {
			// @see TraitPWCommercePostProcessOrder::postCaptureOrder
			$response = $this->postCaptureOrder($response);
		} else {
			// TODO: NEED TO HANDLE ERROR! INCLUDING URLSEGMENT!

		}

		// -------------
		// return $response to client
		return $response;
	}

	public function isSuccessfulPaymentCapture($response) {

		// IF ORDER SESSION IS 'LOST'
		// we will get order expected amounts and ID from order cache
		if (!$this->session->orderId) {
			// GET ORDER DETAILS FROM CACHE
			$orderID = $this->session->get(PwCommerce::ORDER_LOST_SESSION_ORDER_ID_NAME);
			$orderCache = $this->pwcommerce->getOrderCache($orderID);

			// TODO - IF CACHE EMPTY FOR SOME REASON? DOUBTFUL! 404???

			$options = [
				// TODO ORDERID HERE NEEDS TO ADAPT TO LOST SESSION! ALSO AMOUNTS MIGHT NEED ADAPTING! - get from cache or set to session when lost? might confuse! stick to getting from cache; also clearer!
				'expected_order_id' => $orderID,
				'expected_amount_value_in_cents' => $orderCache['expected_amount_in_cents'],
				'expected_order_currency' => $this->getShopCurrency(),
			];
			;
		} else {
			// GET ORDER DETAILS FROM SESSION
			// --------

			// @see TraitPWCommerceOrderTotals::getOrderGrandTotalMoney
			// options with values to compare against $response values
			$expectedAmountMoney = $this->getOrderGrandTotalMoney();
			$expectedAmountValueInCents = (int) $expectedAmountMoney->getAmount();
			$options = [
				// TODO ORDERID HERE NEEDS TO ADAPT TO LOST SESSION! ALSO AMOUNTS MIGHT NEED ADAPTING! - get from cache or set to session when lost? might confuse! stick to getting from cache; also clearer!
				'expected_order_id' => $this->session->orderId,
				'expected_amount_value_in_cents' => $expectedAmountValueInCents,
				'expected_order_currency' => $this->getShopCurrency(),
			];
		}

		// @see TraitPWCommercePayment::getPaymentClass
		$payment = $this->getPaymentClass();

		// ~~~~ HOOK ~~~~
		// TODO CONFIRM WORKS!
		// @see TraitPWCommerceHooks::isSuccessfulPaymentCaptureHook
		$this->isSuccessfulPaymentCaptureHook($response, $options);
		return $payment->isSuccessfulPaymentCapture($response, $options);
	}
}
