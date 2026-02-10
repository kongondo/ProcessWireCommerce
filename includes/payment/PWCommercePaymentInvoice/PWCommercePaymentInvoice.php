<?php

namespace ProcessWire;

// ====================
class PWCommercePaymentInvoice extends PWCommercePayment implements PWCommerceAddons
{

	// Invoice payments will stay unpaid
	public $delayedPayment = true;

	// /**
  * Init.
  *
  * @return mixed
  */
 public function init() {
	// 	$this->currency = $this->defaultCurrency;
	// }

	// TODO @KONGONDO PORT
	/**
	 *   construct.
	 *
	 * @return mixed
	 */
	public function __construct() {
		// @KONGONDO TODO IMPORTANT! SO THAT WE GET PARENTS $this->products() (WireArray)
		parent::__construct();
		$this->currency = $this->defaultCurrency;
	}

	# === ABSTRACT PARENT CLASS METHODS === #
	// @see PWCommercePayments.php

	/**
	 * Create Order.
	 *
	 * @param mixed $createOrderValues
	 * @param bool $debug
	 * @return mixed
	 */
	protected function createOrder($createOrderValues = null, bool $debug = false) {
		// @note: here just to fulfil PWCommercePayment abstract class requirement
	}

	// Set up your server to receive a call from the client
	/**
	 * This function can be used to capture an order payment by passing the approved
	 *
	 * @param mixed $orderId
	 * @param bool $debug
	 * @return mixed
	 */
	protected function captureOrder($orderId, bool $debug = false) {
		// @note: here just to fulfil PWCommercePayment abstract class requirement
	}

	/**
	 * Is Successful Payment Capture.
	 *
	 * @param mixed $response
	 * @param array $options
	 * @return bool
	 */
	protected function isSuccessfulPaymentCapture($response, array $options = []) {
		// @note: here just to fulfil PWCommercePayment abstract class requirement
		return true;
	}

	/**
	 * Render.
	 *
	 * @return string|mixed
	 */
	protected function render() {
		// note: this is little nonsense, since it redirects; never actually renders!

		// -------------------
		$orderID = $this->session->get('orderId');
		// TODO @KONGONDO AMENDMENT
		// TODO make form a partial for invoice form WIP
		$formTemplate = $this->pwcommerce->getPWCommerceTemplate("confirm-invoice-payment-html.php");
		// @NOTE CURRENTLY 'invoice' not configurable or set to session
		$invoiceUrl = $this->session->checkoutPageHttpURL . "invoice/" . $orderID . "/";
		$formTemplate->set("invoiceUrl", $invoiceUrl);
		return $formTemplate->render();
	}

	/**
	 * Get Fields Schema.
	 *
	 * @return mixed
	 */
	protected function getFieldsSchema() {
		// @note: here just to fulfil PWCommercePayment abstract class requirement
		$schema = [];
		return $schema;
	}

	# === IMPLEMENTED INTERFACE CLASS METHODS === #

	/**
	 * Get Class Name.
	 *
	 * @return mixed
	 */
	public function getClassName() {
		$className = "PWCommercePaymentInvoice";
		return $className;
	}

	/**
	 * Get Type.
	 *
	 * @return mixed
	 */
	public function getType() {
		return $this->_("payment");
	}

	/**
	 * Get Title.
	 *
	 * @return mixed
	 */
	public function getTitle() {
		return $this->_("Invoice");
	}

	/**
	 * Get Description.
	 *
	 * @return mixed
	 */
	public function getDescription() {
		$description = $this->_("PWCommerce invoice payment allows your customers to order now, pay later.");
		return $description;
	}

	# === CLASS-SPECIFIC METHODS === #

	/**
	 * Process Payment.
	 *
	 * @return mixed
	 */
	public function processPayment() {
		// Because of $delayedPayment, order will stay unpaid, but successful
		return true;
	}

	// TODO DELETE WHEN DONE - NOT IN USE
	/**
	 * Get Module Config Inputfields.
	 *
	 * @param array $data
	 * @return mixed
	 */
	public static function getModuleConfigInputfields(array $data) {
		$inputfields = new InputfieldWrapper();
		return $inputfields;
	}
}