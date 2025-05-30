<?php

namespace ProcessWire;

// ====================
class PWCommercePaymentInvoice extends PWCommercePayment implements PWCommerceAddons
{

	// Invoice payments will stay unpaid
	public $delayedPayment = true;

	// public function init() {
	// 	$this->currency = $this->defaultCurrency;
	// }

	// TODO @KONGONDO PORT
	public function __construct() {
		// @KONGONDO TODO IMPORTANT! SO THAT WE GET PARENTS $this->products() (WireArray)
		parent::__construct();
		$this->currency = $this->defaultCurrency;
	}

	# === ABSTRACT PARENT CLASS METHODS === #
	// @see PWCommercePayments.php

	protected function createOrder($createOrderValues = null, $debug = false) {
		// @note: here just to fulfil PWCommercePayment abstract class requirement
	}

	// Set up your server to receive a call from the client
	/**
	 *This function can be used to capture an order payment by passing the approved
	 *order ID as argument.
	 *
	 *@param orderId
	 *@param debug
	 *@returns
	 */
	protected function captureOrder($orderId, $debug = false) {
		// @note: here just to fulfil PWCommercePayment abstract class requirement
	}

	protected function isSuccessfulPaymentCapture($response, $options = []) {
		// @note: here just to fulfil PWCommercePayment abstract class requirement
		return true;
	}

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

	protected function getFieldsSchema() {
		// @note: here just to fulfil PWCommercePayment abstract class requirement
		$schema = [];
		return $schema;
	}

	# === IMPLEMENTED INTERFACE CLASS METHODS === #

	public function getClassName() {
		$className = "PWCommercePaymentInvoice";
		return $className;
	}

	public function getType() {
		return $this->_("payment");
	}

	public function getTitle() {
		return $this->_("Invoice");
	}

	public function getDescription() {
		$description = $this->_("PWCommerce invoice payment allows your customers to order now, pay later.");
		return $description;
	}

	# === CLASS-SPECIFIC METHODS === #

	public function processPayment() {
		// Because of $delayedPayment, order will stay unpaid, but successful
		return true;
	}

	// TODO DELETE WHEN DONE - NOT IN USE
	public static function getModuleConfigInputfields(array $data) {
		$inputfields = new InputfieldWrapper();
		return $inputfields;
	}
}