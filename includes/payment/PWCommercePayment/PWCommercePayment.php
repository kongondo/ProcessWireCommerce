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

	/**
	 *   construct.
	 *
	 * @return mixed
	 */
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
	// /**
  * Get Title.
  *
  * @return mixed
  */
 abstract public function getTitle();

	/**
	 * Render frontend markup for the payment.
	 *
	 * @return string|mixed
	 */
	abstract protected function render();

	/**
	 * Create an order using payment.
	 *
	 * @param WireData $createOrderValues
	 * @param bool $debug
	 * @return mixed
	 */
	abstract protected function createOrder(WireData $createOrderValues, bool $debug = false);

	/**
	 * Capture an order using payment.
	 *
	 * @param mixed $orderId
	 * @param bool $debug
	 * @return mixed
	 */
	abstract protected function captureOrder($orderId, bool $debug = false);

	/**
	 * Confirm payment capture was successfull.
	 *
	 * @param mixed $response
	 * @param array $options
	 * @return bool
	 */
	abstract protected function isSuccessfulPaymentCapture($response, array $options = []);

	/**
	 * Returns fields schema for building GUI for editing fields/inputs for the payment gateway.
	 *
	 * @return mixed
	 */
	abstract protected function getFieldsSchema();

	// +++++++++++++
	// NOT IN USE FOR NOW

	/**
	 * Returns the reason of failure
	 * @return string
	 */
	// /**
  * Get Failure Reasonx.
  *
  * @return mixed
  */
 abstract public function getFailureReasonx();

	########################
	// +++++++++++++
	// NON-ABSTRACT METHODS
	// ~~~~~~~~~~~~~
	/**
	 * Get Total Amount.
	 *
	 * @return mixed
	 */
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

	// /**
  * Add Product.
  *
  * @param mixed $title
  * @param mixed $price
  * @param mixed $quantity
  * @param mixed $tax_percentage
  * @return mixed
  */
 public function addProduct($title, $price, $quantity, $tax_percentage = null) {
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
	 *
	 * @param int $id
	 * @return mixed
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
	 *
	 * @param mixed $currency
	 * @return mixed
	 */
	public function setCurrency($currency) {

		$this->currency = $currency;
	}

	/**
	 * Set the url, where payment will be processed. This will be the url where you will load this same
	 *
	 * @param mixed $url
	 * @return mixed
	 */
	public function setProcessUrl($url) {

		// @KONGONDO DELETE IF NO LONGER IN USE!
		$this->processUrl = $url;
	}

	// @kongondo addition
	/**
	 * Set Invoice Url.
	 *
	 * @param mixed $url
	 * @return mixed
	 */
	public function setInvoiceUrl($url) {

		// @KONGONDO DELETE IF NO LONGER IN USE!
		$this->invoiceUrl = $url;
	}

	/**
	 * Set the url where payment processor redirects user if payment fails.
	 *
	 * @param mixed $url
	 * @return mixed
	 */
	public function setFailureUrl($url) {

		$this->failureUrl = $url;
	}

	/**
	 * Set the url where payment processor redirects user if user cancelles the payment. Usually same as failureUrl
	 *
	 * @param mixed $url
	 * @return mixed
	 */
	public function setCancelUrl($url) {

		$this->cancelUrl = $url;
	}

	/**
	 * Set the reason of failure
	 *
	 * @param mixed $string
	 * @return mixed
	 */
	public function setFailureReason($string) {

		$this->failureReason = $string;
	}

	/**
	 * Process the payment
	 *
	 * @return mixed
	 */
	public function processPayment() {
	}
}