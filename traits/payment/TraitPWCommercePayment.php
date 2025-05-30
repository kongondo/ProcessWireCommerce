<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Payment: Trait class for PWCommerce Payment.
 *
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerce Class for PWCommerce
 * Copyright (C) 2024 by Francis Otieno
 * MIT License
 *
 */

trait TraitPWCommercePayment {

	protected $paymentClass;
	protected $paymentClassName;
	protected $paymentProviderID;
	// public $isLostOrderSession = false;

	private function getCustomerOrderFormPaymentProviders() {

		$activePaymentProvidersRadioOptions = [];

		/** @var array $paymentProviders */
		// get all active payment providers/gateways for this shop
		$paymentProviders = $this->pwcommerce->getActivePaymentProviders();
		// build payment providers radio options
		foreach ($paymentProviders as $paymentGateway) {
			$activePaymentProvidersRadioOptions[$paymentGateway['id']] = $paymentGateway['title'];
		}

		$options =
			[
				'type' => 'radio',
				'name' => 'pwcommerce_order_payment_id',
				'label' => $this->_('Payment'),
				'radio_options' => $activePaymentProvidersRadioOptions,
			];
		// ---------
		// get the radio field
		$field = $this->getInputfieldForCustomerForm($options);

		// -----------
		return $field;
	}

	/**
	 * Returns path to Payment Class file, checking if core vs non-core payment addon.
	 *
	 * @return string $path;
	 */
	private function getPaymentClassFilePath() {
		if (!empty($this->isNonCorePaymentProvider())) {
			$path = $this->wire('config')->paths->templates . "pwcommerce/addons/";
		} else {
			$path = __DIR__ . "/../../includes/payment/";
		}

		// --------
		return $path;
	}

	/**
	 * Returns class name of Payment Class file, checking if core vs non-core payment addon.
	 *
	 * @return string $path;
	 */
	private function getPaymentClassName($paymentProviderTitle) {
		if (!empty($this->isNonCorePaymentProvider())) {
			$paymentClassName = $this->pwcommerce->getNonCorePaymentProviderClassNameByID($this->paymentProviderID);
		} else {
			// @note: TODO @see $this->setPaymentProvider(): now built using provider page title
			$paymentClassName = "PWCommercePayment{$paymentProviderTitle}";
		}

		// --------
		return $paymentClassName;
	}

	// @note: important to call after $this->paymentProviderID has been set!
	private function isNonCorePaymentProvider() {
		$nonCorePaymentProvidersIDs = $this->pwcommerce->getNonCorePaymentProvidersIDs();
		return in_array((int) $this->paymentProviderID, $nonCorePaymentProvidersIDs);
	}

	private function setPaymentProvider($paymentProviderPageID) {

		$input = $this->wire('input');

		#########
		// GET PAYMENT PROVIDER DETAILS FROM ORDER CACHE
		// note: this will verify the cache and the order exist
		// will also verify that the order is NOT 'complete'
		// i.e. in 'abandoned' (default) [1001] state
		$orderID = (int) $input->get('order_id');
		$cartOrderID = (int) $input->get('cart_order_id');

		if (empty((int) $paymentProviderPageID)) {
			// TODO THROW ERROR HERE?!
			// MOST LIKELY SESSION IS LOST: VERIFY AND DO OTHER CHECKS

			$paymentProviderPageID = $this->getPaymentProviderPageIDFromOrderCache($orderID, $cartOrderID);

			if (empty($paymentProviderPageID)) {
				// ORDER OR ITS CACHE OR PAYMENT PROVIDER DETAILS NOT FOUND
				// ABORT!
				$message = $this->_('Order not found!');
				wire404($message);
			}
		}

		$paymentProviderTitle = $this->wire('pages')->getRaw("template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",id={$paymentProviderPageID}", 'title');
		if (empty($paymentProviderTitle)) {
			// TODO THROW ERROR HERE?!

		}

		// TODO - HERE WE ASSUME THE LOST SESSION BROWSER IS OK TO CONTINUE IN AND SET THE PAYMENT PROVIDER FOUND IN CACHE TO. OK??

		// track payment gateway values
		$this->session->set('paymentProviderID', $paymentProviderPageID);
		$this->session->set('paymentProviderTitle', $paymentProviderTitle);
		$this->paymentProviderID = $paymentProviderPageID;
		// --------------------
		$paymentClassName = $this->getPaymentClassName($paymentProviderTitle);
		$this->paymentClassName = $paymentClassName;

		// -----------------
		// @note: this includes trailing slash!
		/** @var string $paymentClassFilePath */
		$paymentClassFilePath = $this->getPaymentClassFilePath();

		if (!is_file("{$paymentClassFilePath}{$paymentClassName}/{$paymentClassName}.php")) {
			// TODO ERROR HERE!
			return;
		}

		require_once("{$paymentClassFilePath}{$paymentClassName}/{$paymentClassName}.php");
		$class = "\ProcessWire\\" . $paymentClassName;

		// TODO @KONGONDO IMPORTANT!!! SINCE WE NO LONGER HAVE PAYMENT MODULES AS MODULES, WE SAVE THEIR CONFIGS IN THEIR SETTINGS FIELDS. HENCE, WE NEED TO RETRIEVE THEM AND SET THEM AND PASS THEM TO THE PAYMENT CLASS (E.G. PAYPAL). WE CAN PASS AS WIREDATA OR AS stdClass; it's all good

		$paymentClass = $this->getNewPaymentClass($class);
		// ##############
		$this->paymentClass = $paymentClass;
		// ##############

	}

	private function getPaymentProviderPageIDFromOrderCache($orderID, $cartOrderID): int {
		$paymentProviderPageID = 0;

		$orderCache = $this->validateAndGetOrderCache($orderID, $cartOrderID);

		if (!empty($orderCache)) {
			// found cache; get the payment provider ID
			$paymentProviderPageID = (int) $orderCache['payment']['id'];
		}

		return $paymentProviderPageID;
	}

	private function validateAndGetOrderCache($orderID, $cartOrderID) {
		// first, verify we have this order and it is still in abandoned state
		// $orderPageID = (int) $this->pwcommerce->getRaw("template=order,id={$orderID}, pwcommerce_order.id={$cartOrderID}", 'id');
		$fields = ['id', 'pwcommerce_order.order_status'];
		$order = $this->pwcommerce->getRaw("template=order,id={$orderID}, pwcommerce_order.id={$cartOrderID}", $fields);

		$orderCache = NULL;

		// ////////////////////////
		if (!empty($order)) {
			// we found the order
			// TODO - NOT FOR NOW! THIS PREVENTS US FINISHING CHECKS IN 'TraitPWCommerceCheckout::renderSuccess' IN CASES WHERE SESSION IS LOST. This is because 'TraitPWCommercePostProcessOrder::postCaptureOrder' will be called by 'TraitPWCommerceCaptureOrder::captureOrder' and it will set order status to 1002 (open). This means that 'setPaymentProvider()' here will fail and lead to a 404.
			// check the order_status is still in 'abandoned' stage
			// $orderStatus = (int) $order["pwcommerce_order"]['order_status'];
			// if ($orderStatus === PwCommerce::ORDER_STATUS_ABANDONED) {
			// 	// ORDER STATUS AS EXPECTED
			// 	// get the cache
			// 	$orderCache = $this->getOrderCache($orderID);
			// }
			$orderCache = $this->getOrderCache($orderID);
		}

		// ======
		return $orderCache;
	}

	private function getNewPaymentClass(string $class) {

		// $fields = 'name';
		$fields = ['name', 'pwcommerce_settings'];
		// TODO WE NEED TO GET THE NAME OF THE PROVIDER FROM THE CLASS NAME, .E.G. FROM 'ProcessWire\payment', WE NEED Paypal
		// TODO: MAKE THIS MORE ROBUST! + THROW IN OWN FUNCTION
		// TODO: FOR NOW, JUST GET using page ID
		// TODO: NOT SURE WHY THIS IS SHOWING ON BACKEND AS WELL?
		// $paymentProviderName = str_replace("PWCommercePayment", "", wireClassName($class));
		// TODO: FOR INVOICE, WE NEED TO SKIP BELOW CONFIGS! JUST SEND EMPTY ARRAY?
		// $paymentProviderName = $this->wire('sanitizer')->pageName($paymentProviderName);
		$paymentProvider = $this->wire('pages')->getRaw("template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",id={$this->paymentProviderID}", $fields);

		// TODO: IF EMPTY $paymentProvider, THROW ERROR?!

		$paymentProviderName = $paymentProvider['name'];

		// ---------
		$paymentClassConfigs = [];

		// ----------------
		if ($paymentProviderName !== 'invoice') {

			// $paymentProvider = $this->wire('pages')->getRaw("template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",name={$paymentProviderName}", $fields);
			// TODO @KONGONDO THIS RETURNS AN ARRAY; WE ONLY WANT THE FIRST ITEM AT THE SETTINGS KEY
			$paymentProviderConfigs = $paymentProvider['pwcommerce_settings']; // @note JSON

			// $paymentClassConfigs = new WireData();
			// TODO @KONGONDO ARRAY FOR NOW!
			$paymentClassConfigs = json_decode($paymentProviderConfigs, true);
		}

		// ------------

		// TODO PASS EXTRA OPTIONS HERE FOR PAYMENT CLASSES THAT NEED THEM, E.G. PWCommercePaymentStripe
		$orderID = $this->session->get('orderId');

		$amountMoney = $this->getOrderGrandTotalMoney();

		$amount = $this->pwcommerce->getWholeMoneyAmount($amountMoney);

		if (is_null($amount)) {
			// initial amount
			$amount = 0;
		}

		$amountInCents = (int) $amountMoney->getAmount();

		$options = [
			'order_id' => $orderID,
			'amount' => $amount,
			// @note: total amount including shipping and taxes
			'amount_in_cents' => $amountInCents,
			// @note: total amount including shipping and taxes
			'amount_as_currency' => $this->pwcommerce->getValueFormattedAsCurrencyForShop($amount)
		];

		$paymentClass = new $class($paymentClassConfigs, $options);

		#########

		# ++++++++++++++++++++++++++++++
		// ---------
		return $paymentClass;
		// return $paymentClass2;
	}

	public function getPaymentClass() {
		return $this->paymentClass;
	}

	private function processPayment() {
		// NOTE this is a private method; meaning, it is called only within this class; it is called after successful captureorder!

		$orderPage = $this->getOrderPage();

		// If we don't have order id
		// ERROR OUT
		// TODO - ABOVE OK?

		if (empty($orderPage) || empty($orderPage->id)) {
			throw new Wire404Exception("Order not found");
		}

		// ==============
		// PAYMENT WAS SUCCESSFUL
		$orderPage->of(false);
		// ==============

		$title = $this->session->paymentProviderTitle;

		// ADD NOTE ABOUT THE PAYMENT
		$note = $this->_("Order paid using") . " " . $title;
		$orderPage = $this->addNote($note, $orderPage);
		$this->setOrderPage($orderPage);

		// ==============

		// TODO: @note: this does not set payment status! we do that separately
		$this->completeOrder();

		// SEND ORDER CONFIRMATION
		$this->sendConfirmation();
		// NOTE: NOW CALLED BY GETCOMPLETEORDER FOR INVOICES OR DIRECT PAYMENTS; THIS IS SO THAT PWCommerceCheckout::renderSuccess can still access order id without need to depend on urlsegment1!
		// REMOVE SESSIONS
		// $this->removeOrderSessions();

		// -------------
		//   return true;
		// } else {
		//   return false;
		// }

	}

	public function postProcessPayment() {
		// TODO TESTING ONLY! DELETE WHEN DONE
		$input = $this->wire('input');

		// TODO - named 'pwcommerce_order_cache_1234' where '1234' is the order ID!
		// TODO CHANGE THIS TO A GET! - BUT JUST FOR TESTING!
		$orderID = (int) $input->get('order_id');

		$paymentOrderID = 0;

		// TODO - UPDATE - WE NOW CHECK IF WE HAVE A $this->session->orderId
		// IF NOT, SINCE WE ARE IN POST PROCESS, WE ASSUME SESSION IS LOST!

		// IF WE DON'T HAVE AN ORDER ID IN THE SESSION AT THIS STAGE
		// IT MEANS ORDER SESSION IS LOST
		// e.g. a redirect opened the 'wrong browser'
		// if (empty($this->session->orderId)) {
		if (!$this->session->orderId) {
			# SESSION LOST!
			# +++++++++++
			// set $paymentOrderID from $input->get()
			// @see also TraitPWCommercePayment::setPaymentProvider
			// TODO CONFIRM THIS IS IN CACHE!!!!
			$orderID = (int) $input->get('order_id');
			$cartOrderID = (int) $input->get('cart_order_id');
			// confirm orderID is valid
			$orderCache = $this->validateAndGetOrderCache($orderID, $cartOrderID);
			if (!empty($orderCache)) {
				// found cache; set the order ID
				$paymentOrderID = $orderID;
				###########
				# SESSION LOST ->
				// ALSO SET ORDER ID TO TEMPORARY SESSION VARIABLE
				// FOR USE IN COMPLETING ORDER PROCESSES
				// e.g. TraitPWCommerceMainOrder::getOrder will call
				// TraitPWCommerceOrderPage::getOrderPage which needs the order page
				// 'lostSessionOrderID'
				$this->session->set(PwCommerce::ORDER_LOST_SESSION_ORDER_ID_NAME, $orderID);
			} else {
				// TODO - ERROR? 404?
			}

			// SET ORDER PAGE SINCE SESSION IS LOST
			// WILL BE USED LATER, E.G. IN TraitPWCommerceOrderStatus::setOrderStatusesAfterOrderCompletion
			$this->setOrderPage($paymentOrderID);
		} else {
			$paymentOrderID = $this->session->orderId;
		}

		// @note - FOR PAYMENT GATEWAY LIKE STRIPE TO CONFIRM PAYMENT WAS TAKEN, ETC
		return $this->captureOrder($paymentOrderID);
	}

	/**
	 * Process Delayed/Invoice Payments.
	 *
	 * @param int $orderID Order ID from URL Segment 2.
	 * @return WireData $invoiceCreationResponse
	 */
	public function processInvoice($orderID = null) {

		// TODO: HERE WE NEED TO CHECK IF STORE ACCEPTS INVOICE PAYMENTS/ORDERS!
		// TODO: USE UTILITIES FOR THAT!
		// TODO: THROW EXCEPTION RATHER THAN FAIL??
		// TODO HARDCODED FOR NOW UNTIL PORT TO PWCOMMERCE 2!
		// TODO: ALSO USE FOR RENDER PAYMENT METHODS!
		$isShopAcceptsInvoicePayments = $this->isShopAcceptsInvoicePayments();

		if (empty($isShopAcceptsInvoicePayments)) {
			throw new WireException($this->_("Shop does not accept invoice payments!"));
		}

		// -------
		if ($orderID !== $this->session->orderId) {
			throw new Wire404Exception("Order not found");
		}

		// TODO @KONGONDO NEW METHOD
		$invoiceCreationResponse = new WireData();
		// here the most important thing is to see if we have an order and that it has been set up properly
		// also, that customer details are set correctly (TODO?)
		// we don't care about payment and no goods will be sent/downloaded until invoice is settled
		// --------
		$orderPage = $this->getOrderPage();

		// -------

		// If we don't have order id, let's try to find it from orderID
		// url (helpful for delayed payment notifications, where we don't have session available)
		if (empty($orderPage) || empty($orderPage->id)) {
			$orderID = (int) $orderID;

			$o = $this->pages->get($orderID);
			// ---------------------------
			// TODO @KONGONDO AMENDMENT
			// if ($o instanceof PWCommerceOrder) {
			if ($o->template->name === PwCommerce::ORDER_TEMPLATE_NAME) {
				$this->setOrderPage($o);
				$orderPage = $o;
			} else {
				// TODO: JUST RETURN THE FAILURE MESSAGE HERE?
				// throw new Wire404Exception("Order not found");
				$invoiceCreationResponse->success = false;
				$invoiceCreationResponse->message = $this->_("Order not found.");
				return $invoiceCreationResponse;
			}
		}

		// GOOD TO GO
		// if we are here, there is no need to check if ($payment->processPayment()) for Invoice Payments
		// it returns TRUE by default anyway
		// ****************
		// 1. Order page output formatting off

		$orderPage->of(false);
		// ==============
		// TODO @KONGONDO -> COMMENT
		// PUBLISH THE PAGE
		// TODO: CHANGE ORDER STATUS TO COMPLETE/IN-PROGRESS, ETC FOR ORDER, SHIPPING AND PAYMENT!
		// Successful orders are published pages
		// TODO: WE DO THIS IN COMPLETE ORDER!
		// $orderPage->removeStatus(Page::statusUnpublished);
		// $orderPage->save();

		// --------

		// ****************
		// 2. Complete Order
		// COMPLETE ORDER SINCE WE HAVE ESTABLISHED ABOVE THAT IT IS SUCCESSFUL

		$this->completeOrder();

		// -----------
		// ****************
		// 3. Set Order Statuses
		// SET ORDER STATUS as 'OPEN', PAYMENT STATUS AS 'PENDING' and SHIPMENT STATUS AS 'AWAITING FULFILMENT'
		// @note: this has to be done before sendConfirmation() as some of its processes depend on order status
		$orderStatus = PwCommerce::ORDER_STATUS_OPEN;
		$paymentStatus = PwCommerce::PAYMENT_STATUS_AWAITING_PAYMENT;
		$fulfilmentStatus = PwCommerce::FULFILMENT_STATUS_AWAITING_FULFILMENT;
		$this->setOrderStatusesAfterOrderCompletion($orderStatus, $paymentStatus, $fulfilmentStatus);

		// ****************
		// 4. Send  Order Confirmation Email
		// SEND ORDER CONFIRMATION
		$this->sendConfirmation();

		// -------------
		// ****************
		// 5. Prepare Responses
		$invoiceCreationResponse->success = true;
		$invoiceCreationResponse->message = $this->_("Order and invoice created successfully.");
		$this->session->isInvoiceOrder = true;
		// ------
		return $invoiceCreationResponse;
	}

	# ++++++++++++++++++++++++++++++++++

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PAYMENT ~~~~~~~~~~~~~~~~~~

	public function getActivePaymentProviders($isGetSettingsAsWell = false) {
		// TODO: RETURN ARRAY OR WIREARRAY?
		// @note: active payment providers are published pages. inactive ones are unpublished
		// ----------
		// TODO DELETE WHEN DONE
		// $fields = ['id', 'title'];
		// if (!empty($isGetSettingsAsWell)) {
		// 	$fields['pwcommerce_settings'] = 'settings';
		// }

		// @note: we first find with 'settings' this is so we can get 'payment_method_label' if available.
		$fields = ['id', 'title', 'pwcommerce_settings' => 'settings'];

		// -------
		// @note the 'check_access'!
		$paymentProviders = $this->wire('pages')->findRaw("template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",check_access=0,sort=title", $fields);

		foreach ($paymentProviders as $pageID => $paymentProvider) {
			if (empty($paymentProvider['settings'])) {
				// payment provider has not settings (e.g. 'invoice')
				// SKIP IT
				continue;
			}
			// -----------
			$paymentProviderSettingsJSON = $paymentProvider['settings'];
			if (!empty($paymentProviderSettingsJSON)) {
				$paymentProviderSettings = json_decode($paymentProviderSettingsJSON, true);
				if (!empty($paymentProviderSettings['payment_method_label'])) {
					// add 'label'
					$paymentProviders[$pageID]['label'] = $paymentProviderSettings['payment_method_label'];
				}
			}
			// --------
			if (empty($isGetSettingsAsWell)) {
				unset($paymentProviders[$pageID]['settings']);
			}
		}
		// ------
		return $paymentProviders;
	}

	public function isShopAcceptsInvoicePayments() {
		// TODO FOR NOW WE ONLY CHECK IF INVOICE PAYMENT IS ACTIVE (published) - IN FUTURE MIGHT ADD SETTING IN CHECKOUT AS WELL
		$fields = 'id';
		$invoicePayment = $this->wire('pages')->getRaw("template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",name=invoice,status!=unpublished", $fields);

		return !empty((int) $invoicePayment);
	}

	public function isShopUsePaymentProvidersFeature() {
		$fields = 'id';
		$paymentProvidersParentPage = $this->wire('pages')->getRaw("template=" . PwCommerce::PAYMENT_PROVIDERS_TEMPLATE_NAME, $fields);

		return !empty((int) $paymentProvidersParentPage);
	}

	// ~~~~~~~~~~~~~~

	public function runPWCommercePayment($event) {
		$test = 'RUN PWCOMMERCE PAYMENT CALLED';
		$log = $this->wire('log');
		$logName = "trait_pwcommerce_payment";
		$log->save($logName, $test);

		// $entireURL = $event->arguments(0);
		$action = $event->arguments(1);
		$test = "RUN PWCOMMERCE PAYMENT CALLED ACTION: {$action}";
		$log->save($logName, $test);

		// ##############
		if ($this->config->ajax) {
			$test = "RUN PWCOMMERCE PAYMENT CALLED ACTION: AJAX";
			$log->save($logName, $test);
			switch ($action) {

				case 'create':
					//  CREATE ORDER WITH PAYMENT PROVIDER
					/** @var stdClass $response */
					// TODO @KONGONDO -> CHANGED TO CLASS PROPERTY FOR EASIER SYNTAX
					$response = $this->createOrder();
					// return $this->_createOrder();
					break;

				case 'capture':
					// CAPTURE PAYMENT FOR ORDER WITH PAYMENT PROVIDER
					// -------------
					// @note: ALPHANUMERIC
					$paymentOrderID = $this->wire('sanitizer')->purify($this->input->get('orderID'));
					// TODO @kongondo check if $paymentOrderID is empty!
					/** @var stdClass $response */
					// TODO @KONGONDO -> CHANGED TO CLASS PROPERTY FOR EASIER SYNTAX
					$response = $this->captureOrder($paymentOrderID);
					break;
			}

			// -----------
			// JSON response
			// TODO @kongondo - confirm this!
			header('Content-Type: application/json');
			echo json_encode($response->result);

			exit();
		}
	}

	private function isOrderAlreadyPaid() {

		// WE END UP HERE TYPICALLY IN LOST SESSIONS SITUATIONS
		// i.e. order paid in a different browser to the original one with the cart

		$isOrderPaid = false;
		$orderPage = $this->getOrderPage();
		$order = $this->getOrder();
		$orderCache = $this->getOrderCache($orderPage->id);

		# 3 CHECKS TO CONFIRM ORDER IS INDED PAID
		# ----------------
		# #1: CHECK IF order page is still unpublished
		// PwCommerce::PAYMENT_STATUS_PAID > 4000
		if (empty($orderPage->isUnpublished())) {
			# 2. check order cache

			// TODO CAN ALSO CHECK $order ITSELF?
			$isOrderPaid = !empty($orderCache['payment']['payment_status']) && (int) $orderCache['payment']['payment_status'] === (int) PwCommerce::PAYMENT_STATUS_PAID;

			# 3. check $order itself as well
			$isOrderPaid = (int) $order->paymentStatus === (int) PwCommerce::PAYMENT_STATUS_PAID;

		}

		if (!empty($isOrderPaid)) {

			$this->handleOrderIsAlreadyPaid($orderPage->id);
		}

		return $isOrderPaid;

	}

	private function handleOrderIsAlreadyPaid($orderID) {
		$test = 'TESTING LOST SESSION INCIDENT EMPTY CART';

		// ==============
		// EMPTY CART AFTER SUCCESSFUL ORDER COMPLETEION
		// Empty the current cart
		$this->emptyCart();
		// ==============
		// DELETE THE ORDER CACHE
		// $this->deleteOrderCache($orderID);
		// moved to getCompletedOrder where we also remove session values
	}
}
