<?php

namespace ProcessWire;

/**
 * PWCommerce: Payment
 *
 * Base class for PWCommerce Payment Classes to implement.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommercePayment for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */


// ====================
abstract class PWCommercePayment extends WireData implements Module
{

	protected $id;
	protected $currency = '';

	protected $processUrl = '';
	protected $notifyUrl = '';
	protected $failureUrl = '';
	protected $cancelUrl = '';

	protected $failureReason = '';

	protected $customer;
	protected $products;

	public function __construct() {
		$this->products = new WireArray();

	}

	// ~~~~~~~~~~~~~

	########################
	// +++++++++++++
	// ABSTRACT METHODS

	// @note: not in use since implemented in PWCommerceAddons Interface which all payment gateways implement.
	/**
	 * Returns the client friendly title for the payment.
	 * @return string
	 */
	// abstract public function getTitle();

	/**
	 * Render frontend markup for the payment.
	 *
	 * @return string
	 */
	abstract protected function render();

	/**
	 * Create an order using payment.
	 *
	 * Varies depending on payment gateway.
	 * Can be used to create payment intent for gateways such as Stripe or PayPal.
	 * Can be used to create order, authorize and capture for gateways such as Authorize.Net.
	 *
	 * @param WireData $createOrderValues
	 * @param boolean $debug
	 * @return void
	 */
	abstract protected function createOrder(WireData $createOrderValues, $debug = false);

	/**
	 * Capture an order using payment.
	 *
	 * Varies depending on payment gateway.
	 * Can be used to retrieve payment intent for gateways such as Stripe.
	 * Can be used to execute payment for gateways such as PayPal.
	 * Can be used to create order, authorize and capture for gateways such as Authorize.Net.
	 *
	 * @param int $orderId
	 * @param boolean $debug
	 * @return void
	 */
	abstract protected function captureOrder($orderId, $debug = false);

	/**
	 * Confirm payment capture was successfull.
	 *
	 * Ideally check various aspects of payment to confirm.
	 * For instance amount, currency, order ID, etc.
	 *
	 * @param object $response
	 * @param array $options
	 * @return boolean
	 */
	abstract protected function isSuccessfulPaymentCapture($response, $options = []);

	/**
	 * Returns fields schema for building GUI for editing fields/inputs for the payment gateway.
	 *
	 * @see documentation.
	 * @return array $schema.
	 */
	abstract protected function getFieldsSchema();

	// +++++++++++++
	// NOT IN USE FOR NOW

	/**
	 * Returns the reason of failure
	 * @return string
	 */
	// abstract public function getFailureReasonx();

	########################
	// +++++++++++++
	// NON-ABSTRACT METHODS
	// ~~~~~~~~~~~~~
	public function getTotalAmount() {

		/** @var float $subtotal */
		$subtotal = $this->pwcommerce->getOrderTotalAmount();
		$total = $this->pwcommerce->getOrder()->totalPrice;

		// return $subtotal;
		// ############
		// TODO DELETE THIS METHOD AND BELOW EVENTUALLY
		// TODO - DELETE IF NOT IN USE! OUR CALCULATIONS DONE DIFFERENTLY AND THIS DOES NOT INCLUDE HANDLING FEES
		// $total = 0;
		// foreach ($this->products as $product) {
		//   $total = $total + $product->total;
		// }
		return $total;
	}

	// public function addProduct($title, $price, $quantity, $tax_percentage = null) {
	// 	// ############
	// 	// TODO DELETE THIS METHOD AND BELOW EVENTUALLY

	// 	$product = new PWCommercePaymentProduct($title, $price, $quantity, $tax_percentage);
	// 	$this->products->add($product);
	// }

	###########
	# >>> TODO - DELETE IF NO LONGER IN USER <<<

	// ~~~~~~~~~~~~~

	/**
	 * Set numeric id for the payment. Usually order id. Some value required, since used to verify the payment.
	 * @param integer $desc
	 */
	public function setId($id) {
		// TODO DELETE AS NO LONGER NEEDED!

		// TODO @KONGONDO DO WE STILL NEED THIS IN NEW PAYPAL PAYMENT?
		$id = (int) $id;
		if ($id < 1)
			throw new WireException("ID is not valid");
		$this->id = $id;
	}

	/**
	 * Set currency code for the payment in uppercase
	 * @param string $currency ie. USD|EUR
	 */
	public function setCurrency($currency) {

		$this->currency = $currency;
	}

	/**
	 * Set the url, where payment will be processed. This will be the url where you will load this same
	 * module and call $payment->processPayment()
	 * @param string $url
	 */
	public function setProcessUrl($url) {

		// @KONGONDO DELETE IF NO LONGER IN USE!
		$this->processUrl = $url;
	}

	// @kongondo addition
	public function setInvoiceUrl($url) {

		// @KONGONDO DELETE IF NO LONGER IN USE!
		$this->invoiceUrl = $url;
	}

	/**
	 * Set the url where payment processor redirects user if payment fails.
	 * @param string $url
	 */
	public function setFailureUrl($url) {

		$this->failureUrl = $url;
	}

	/**
	 * Set the url where payment processor redirects user if user cancelles the payment. Usually same as failureUrl
	 * @param string $url
	 */
	public function setCancelUrl($url) {

		$this->cancelUrl = $url;
	}

	/**
	 * Set the reason of failure
	 * @param string $string
	 */
	public function setFailureReason($string) {

		$this->failureReason = $string;
	}

	/**
	 * Process the payment
	 * @return bool true|false depending if the payment was successful
	 */
	public function processPayment() {
	}
}