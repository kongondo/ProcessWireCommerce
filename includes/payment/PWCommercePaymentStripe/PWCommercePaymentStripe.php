<?php

namespace ProcessWire;

// this will init Stripe lib files
require_once __DIR__ . '/../../../vendor/Stripe/init.php';
// init our spl_autoload_register
require_once __DIR__ . '/../../../vendor/autoload.php';

// This is your test secret API key.
// use Stripe\Stripe;
// use Stripe\PaymentIntent;

// Stripe::setApiKey('sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

/**
 *
 * PayPal payment class for ProcessWire
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 * Uses Stripe PHP Payment Intents API for payment.
 * Used in conjuction with Stripe Elements JS SDK on the client.
 *
 *
 */
// ====================

class PWCommercePaymentStripe extends PWCommercePayment implements PWCommerceAddons {

	private $options;
	private $stripeConfigs;
	private $paymentIntent;

	public function __construct(array $stripeConfigs, array $options = []) {
		// TODO @KONGONDO DOES THE PARENT CLASS NEED CONFIGS AS WELL?
		parent::__construct();

		$this->stripeConfigs = $stripeConfigs;
		$this->options = $options;
		// @note getting from shop settings
		$this->currency = $this->pwcommerce->getShopCurrency();
	}

	# === ABSTRACT PARENT CLASS METHODS === #
	// @see PWCommercePayments.php

	// 2. Set up your server to receive a call from the client
	/**
	 *This is the sample function to create an order. It uses the
	 *JSON body returned by buildRequestBody() to create an order.
	 */
	protected function createOrder($createOrderValues = null, $debug = false) {

		$test = 'STRIPE CREATE ORDER CALLED';
		$log = $this->wire('log');
		$log->save("webhook_payload_stripe", $test);
		// -----------------
		// SET STRIPE SECRET API KEY
		// Set your secret key. Remember to switch to your live secret key in production.
		// See your keys here: https://dashboard.stripe.com/apikeys
		$this->setStripeSecretAPIKey();

		// -----------
		// CREATE PAYMENT INTENT
		try {
			// Call API with your client and get a response for your call
			$this->paymentIntent = $this->createPaymentIntent();

			// SAVE PAYMENT INTENT CLIENT SECRET TO SESSION for reuse
			$this->saveSessionPaymentIntentID();

			// TODO UPDATE ORDER CACHE FOR KEYS 'payment_intent' AND 'payment_intent_client_secret'

			$this->setPaymentIntentInformationToOrderCache();
		} catch (\Exception $ex) {
			// TODO CONFIRM EXECPTION NAMESPACE/TYPE!
			// TODO: HOW TO HANDLE THIS GRACEFULLY?
			// print_r($ex->getMessage());

		}
	}

	// >>>>>>>>>>>>>>>>>>>>> CAPTURE TRANSACTION / CAPTURE ORDER {take payment} <<<<<<<<<<<<<<<<<<<<

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


		// @note - FOR NOW we CHECK IF PAYMENT INTENT 'amount_received'
		// we do this later in isSuccessfulPaymentCapture()
		// method will be called by TraitPWCommerceOrder::captureOrder()

		// RETRIEVE EXISTING PAYMENT INTENT
		// NOTE: if session is lost, we will get payment intent value from cache instead.
		$paymentIntent = $this->retrieveExistingPaymentIntent();
		return $paymentIntent;
	}

	protected function isSuccessfulPaymentCapture($response, $options = []) {

		$isSuccessfulPaymentCapture = false;
		// *** EXPECTED ***
		// expected order values
		$expectedOrderID = (int) $options['expected_order_id'];
		$expectedOrderAmountValueInCents = (int) $options['expected_amount_value_in_cents'];
		$expectedOrderCurrency = strtoupper($options['expected_order_currency']);

		// ---------
		// *** RECEIVED ***
		// Stripe provider processed payment values
		$purchaseOrderID = (int) $response->metadata['reference_id'];
		$purchaseAmountInCents = (int) $response->amount_received;
		$purchaseAmountCurrency = strtoupper($response->currency);

		// @see: https://developer.paypal.com/docs/api/reference/api-responses/
		// @see: https://stripe.com/docs/payments/intents

		// --------------
		// below might be overkill, but need to be 100% certain!
		if (
			// did we get success status
			$response->status === 'succeeded' &&
			// does the reference/invoice reference we sent with order creation match our expected order ID
			$purchaseOrderID === (int) $expectedOrderID &&
			// does the paid amount match the total expected order amount
			$purchaseAmountInCents === $expectedOrderAmountValueInCents &&
			// does the payment currency match our shop's currency?
			// TODO: this currency should come from PWCommerceUtilities to get shop currency!
			$purchaseAmountCurrency === $expectedOrderCurrency

		) {
			$isSuccessfulPaymentCapture = true;
		}

		return $isSuccessfulPaymentCapture;
	}

	protected function render() {

		// TODO REVISIT THIS!
		if ($this->getTotalAmount() <= 0) {
			throw new WireException("Products are not set");
		}

		/*@note DIFFERENT FROM PAYPAL IN THAT IN PAYPAL, WE CREATE ORDER ON DEMAND, WHEN THE PAY BUTTON IS CLICKED; THEN, WE CAPTURE ORDER IN THE MODAL; IN STRIPE PAYMENT INTENTS, IT IS THE OPPOSITE; WE CREATE THE ORDER IMMEDIATELY WE LAND ON THE CHECKOUT PAYMENT PAGE; I.E. BY THE TIME WE DISPLAY THE CARD FORM, WE SHOULD HAVE CREATED THE PAYMENT INTENT; THIS IS SO THAT WE CAN PASS THE PAYMENT INTENT CLIENT SECRET TO THE FORM ON THE THAT PAGE WHEN WE CALL RENDER; SECONDLY, CAPTURE IS DONE ON CLIENT SIDE AND VERIFIED BY WEBHOOKS or by checking status of PaymentIntent. For now, we use the latter.
																																																																																																																																																																																																																																																																												  TODO - implement webhooks?*/
		$this->createOrder();
		/** @var WireData $order */
		$order = $this->pwcommerce->getOrder();

		$cancelUrl = $this->session->checkoutPageHttpURL . $this->session->checkoutCancelUrlSegment . "/";
		$failUrl = $this->session->checkoutPageHttpURL . $this->session->checkoutConfirmationUrlSegment . "/?failure=";
		// @NOTE CURRENTLY 'post-process' not configurable or set to session
		// $returnUrl = $this->session->checkoutPageHttpURL . "post-process/";
		$returnUrl = $this->session->checkoutPageHttpURL . "post-process/?order_id={$order->id}&cart_order_id={$order->orderID}";

		$intentClientSecret = !empty($this->paymentIntent) ? $this->paymentIntent->client_secret : '';

		// -----------------
		// TODO DELETE MOST OF THESE AS NO LONGER NEEDED!
		$formTemplate = new TemplateFile(__DIR__ . DIRECTORY_SEPARATOR . "payment_form.php");
		// ---------------
		// TODO DELETE THOSE NOT IN USE!
		$formTemplate->set('amountAsCurrency', $this->options['amount_as_currency']);
		// set variables for JS
		// for script tag
		$formTemplate->set("currency", $this->currency);
		$formTemplate->set("clientID", $this->getClientID());
		$formTemplate->set("intentClientSecret", $intentClientSecret);
		// for custom hidden inputs to tell JS where to redirect
		$formTemplate->set("failUrl", $failUrl);
		$formTemplate->set("cancelUrl", $cancelUrl);
		$formTemplate->set("returnUrl", $returnUrl);
		// --------
		// set stripe elements apperance property (with theme and variables)
		$formTemplate->set("stripeElementsAppearance", $this->getStripeElementsAppearance());

		return $formTemplate->render();
	}

	/**
	 * Get the fields data to be used to build the backend settings form for Stripe..
	 *
	 * For use in PWCommerce admin backend.
	 * @see documentation for required structure of the schema.
	 *
	 * @access private
	 * @return array Array of fields configurations.
	 */
	protected function getFieldsSchema() {
		$schema = [
			[
				'name' => 'test_publishable_key',
				'type' => 'text',
				'label' => $this->_('Test Publishable Key'),
				'description' => $this->_('Specify the test/sandbox publishable key.'),
				'notes' => $this->_('This is the testing publishable key. This value is found in your app in your Stripe developer dashboard.'),
			],
			[
				'name' => 'test_secret_key',
				'type' => 'text',
				'label' => $this->_('Test Secret Key'),
				'description' => $this->_('Specify the test/sandbox secret key.'),
				'notes' => $this->_('This is the testing secret key. This value is found in your app in your Stripe developer dashboard.'),
				'collapsed' => true,
			],
			[
				'name' => 'test_webhook_signing_secret',
				'type' => 'text',
				'label' => $this->_('Test Webhook Signing Secret'),
				'description' => $this->_('Specify the test/sandbox webhook signing secret for extra verification of payment statuses.'),
				'notes' => $this->_('This is the testing webhook signing secret. This value is found in your app in your Stripe developer dashboard. This setting is optional but recommended if you will be using Stripe webhooks.'),
				'collapsed' => true,
			],
			// ~~~~ LIVE ~~~~~
			[
				'name' => 'is_live',
				'type' => 'checkbox',
				'label' => $this->_('Use Live Keys'),
				'description' => $this->_('Specify if this shop is in live/production versus test/sandbox mode.'),
				'notes' => $this->_('Live mode should be used for real purchases for a shop in production. For testing purposes, please make sure that you uncheck this box.'),
			],
			[
				'name' => 'live_publishable_key',
				'type' => 'text',
				'label' => $this->_('Live Publishable Key'),
				'description' => $this->_('Specify the live/production publishable key for purchases.'),
				'notes' => $this->_('This is the live publishable key. This value is found in your app in your Stripe developer dashboard.'),
				'collapsed' => true,
			],
			[
				'name' => 'live_secret_key',
				'type' => 'text',
				'label' => $this->_('Live Secret Key'),
				'description' => $this->_('Specify the live/production secret key for purchases.'),
				'notes' => $this->_('This is the live client secret key. This value is found in your app in your Stripe developer dashboard.'),
				'collapsed' => true,
			],
			[
				'name' => 'live_webhook_signing_secret',
				'type' => 'text',
				'label' => $this->_('Live Webhook Signing Secret'),
				'description' => $this->_('Specify the live/production webhook signing secret for extra verification of payment statuses.'),
				'notes' => $this->_('This is the live webhook signing secret. This value is found in your app in your Stripe developer dashboard. This setting is optional but recommended if you will be using Stripe webhooks.'),
				'collapsed' => true,
			],
		];
		return $schema;
	}

	# === IMPLEMENTED INTERFACE CLASS METHODS === #
	// @see PWCommerceAddons.php


	public function getClassName() {
		$className = "PWCommercePaymentStripe";
		return $className;
	}

	public function getType() {
		return "payment";
	}

	public function getTitle() {
		return $this->_("Stripe");
	}

	public function getDescription() {
		$description = $this->_("Stripe is a payment service provider that merchants can use to accept dozens of payment methods, from credit cards to buy now and pay later services. It offers a suite of APIs powering online payment processing and commerce solutions for internet businesses of all sizes.");
		return $description;
	}

	# === CLASS-SPECIFIC METHODS === #

	// >>>>>>>>>>>>>>>>>>>>> SET UP TRANSACTION / CREATE ORDER  <<<<<<<<<<<<<<<<<<<<
	// @see: https://stripe.com/docs/payments/payment-intents

	/**
	 * Sets the secret api key (sk) to be used for Stripe requests.
	 *
	 * @return void
	 */
	public function setStripeSecretAPIKey() {
		// GET API KEYS
		// get the environment api keys variables, i.e. test versus live
		/** @var array $apiKeysForEnvironment */
		$apiKeysForEnvironment = $this->getAPIKeysForEnvironment();
		$clientSecret = $apiKeysForEnvironment['secret_key'];

		// ------------
		// SET STRIPE SECRET API KEY
		$stripe = new \Stripe\Stripe();
		$stripe->setApiKey($clientSecret);
	}

	/**
	 * Get API Keys for Stripe Test versus Live Environment.
	 *
	 * @return array Array with publishable and secret API keys.
	 */
	public function getAPIKeysForEnvironment() {
		// ----
		// TODO @kongondo need to catch errors, e..g. empty / missing values!
		$stripeConfigs = $this->stripeConfigs;
		if (!empty($stripeConfigs['is_live'])) {
			// LIVE/PRODUCTION ENVIRONMENT
			// @note: the Publishable Key (pk)
			// just using $clientId for consitency with our PayPal class
			$clientId = $stripeConfigs['live_publishable_key'];
			// @note: the Secret Key (sk)
			// just using $clientSecret for consitency with our PayPal class
			$clientSecret = $stripeConfigs['live_secret_key'];
			// webhook secret (optional)
			$clientWebhookSigningSecret = $stripeConfigs['live_webhook_signing_secret'];
		} else {
			// SANDBOX/TESTING ENVIRONMENT
			// @note: the Publishable Key (pk)
			// just using $clientId for consitency with our PayPal class
			$clientId = $stripeConfigs['test_publishable_key'];
			// @note: the Secret Key (sk)
			// just using $clientSecret for consitency with our PayPal class
			$clientSecret = $stripeConfigs['test_secret_key'];
			// webhook secret (optional)
			$clientWebhookSigningSecret = $stripeConfigs['test_webhook_signing_secret'];
		}

		// ----------
		return [
			'publishable_key' => $clientId,
			'secret_key' => $clientSecret,
			'webhook_signing_secret' => $clientWebhookSigningSecret
		];
	}

	private function getClientID() {
		// TODO: NEED TO CHECK FOR EMPTIES, HANDLE ERRORS! BUT CAN DO THAT IN CONSTRUCT MAYBE?
		$stripeConfigs = $this->stripeConfigs;
		$clientID = !empty($stripeConfigs['is_live']) ? $stripeConfigs['live_publishable_key'] : $stripeConfigs['test_publishable_key'];
		// --------
		return $clientID;
	}

	private function getStripeClient() {
		// GET API KEYS
		// get the environment api keys variables, i.e. test versus live
		/** @var array $apiKeysForEnvironment */
		$apiKeysForEnvironment = $this->getAPIKeysForEnvironment();
		$clientSecret = $apiKeysForEnvironment['secret_key'];
		// NEW STRIPE CLIENT
		$stripeClient = new \Stripe\StripeClient(
			$clientSecret
		);
		// -----
		return $stripeClient;
	}

	private function createPaymentIntent() {

		$session = $this->wire('session');
		$stripePaymentIntentID = $session->get('stripePaymentIntentID');

		// @note: FIRST, CHECK IF WE RETRIEVE OR UPDATE AN EXISTING PAYMENT INTENT FOR THIS SESSION, ELSE CREATE NEW!
		// TODO, meaning, we always retrieve and compare amount to current cart amount! -> 'amount'
		if (!empty($stripePaymentIntentID)) {
			// RETRIEVE EXISTING PAYMENT INTENT
			$paymentIntent = $this->retrieveExistingPaymentIntent();

			// @note: HERE CHECK IF AMOUNT HAS CHANGED, HENCE UPDATE EXISTING INTENT
			// TODO int ok? since in cents?
			// +++++++++++++
			// CHECK IF ORDER AMOUNT HAS CHANGED
			// $paymentIntentAmountInCents = (int) $paymentIntent->amount;
			// TODO? we use float, just in case, but expect to be full int (?)
			$paymentIntentAmountInCents = (int) $paymentIntent->amount;
			$existingOrderAmountInCents = (int) $this->options['amount_in_cents'];
			if ($paymentIntentAmountInCents !== $existingOrderAmountInCents) {

				// prepare update options array
				$updateOptions = ['amount' => $existingOrderAmountInCents];

				// update and get updated payment intent
				$paymentIntent = $this->updateExistingPaymentIntent($updateOptions);
			} else {
				// TODO DELETE WHEN DONE TESTING

			}
		} else {
			// CREATE NEW PAYMENT INTENT
			$stripePaymentIntent = new \Stripe\PaymentIntent();
			$paymentIntent = $stripePaymentIntent->create($this->buildRequestBody());
		}

		// --------
		return $paymentIntent;
	}

	private function saveSessionPaymentIntentID() {
		if (empty($this->session->stripePaymentIntentID)) {
			$this->session->set('stripePaymentIntentID', $this->paymentIntent->id);
		}
	}

	private function retrieveExistingPaymentIntent() {
		// get stripe client
		$stripeClient = $this->getStripeClient();

		// IF ORDER SESSION IS 'LOST'
		// we will get stripePaymentIntentID from order cache
		if (!$this->session->orderId) {
			// GET PAYMENT INTENT FROM CACHE
			$orderID = $this->session->get(PwCommerce::ORDER_LOST_SESSION_ORDER_ID_NAME);

			$orderCache = $this->pwcommerce->getOrderCache($orderID);

			// TODO - IF CACHE EMPTY FOR SOME REASON? DOUBTFUL! 404???

			$stripePaymentIntentID = $orderCache['payment']['payment_intent'];
		} else {
			// GET PAYMENT INTENT FROM SESSION
			// --------
			// RETRIEVE THE PAYMENT INTENT FOR SESSION
			$stripePaymentIntentID = $this->session->get('stripePaymentIntentID');
		}

		// TODO IF ID EMPTY; ATTEMPT TO GET FROM WIRECACHE
		// TODO EVERYWHERE WE SET OR REMOVE PAYMENT INTENT TO/FROM SESSION, WE ALSO DO THE SAME FOR CACHE
		$paymentIntent = $stripeClient->paymentIntents->retrieve(
			$stripePaymentIntentID,
			[]
		);

		// -------
		return $paymentIntent;
	}

	private function updateExistingPaymentIntent(array $updateOptions) {
		// get payment intent ID from session
		$stripePaymentIntentID = $this->session->get('stripePaymentIntentID');
		// get stripe client
		$stripeClient = $this->getStripeClient();
		// --------
		// UPDATE VALUES
		$paymentIntent = $stripeClient->paymentIntents->update(
			$stripePaymentIntentID,
			$updateOptions
		);
		// return updated payment intent object
		return $paymentIntent;
	}

	/**
	 * Setting up the JSON request body for creating the order with minimum request body. The intent in the
	 * request body should be "AUTHORIZE" for authorize intent flow.
	 *
	 */
	private function buildRequestBody() {

		// TODO: ADD MORE DETAILS HERE, E.G. LINE ITEMS, MAYBE CUSTOMER? why?
		$amountInCents = $this->options['amount_in_cents'];
		$orderID = $this->options['order_id'];
		return [
			// @note: amount in cents for Stripe!
			'amount' => $amountInCents,
			'currency' => $this->currency,
			// With automatic_payment_methods enabled, Stripe automatically detects the payment methods relevant to your customer.
			'automatic_payment_methods' => [
				'enabled' => true,
			],
			// ------------
			// we'll use these to verify request
			'metadata' => [
				'integration_check' => 'accept_a_payment',
				'reference_id' => $orderID,
				// @KONGONDO TODO
				// 'description' => $this->_('Sporting Goods'),
				// 'custom_id' => 'CUST-HighFashions',
				// @KONGONDO TODO - MAKE CUSTOM?
				'invoice_id' => $orderID,
				'idempotency_key' => $orderID // TODO ORDER ID OK?
			],
		];
	}

	private function setPaymentIntentInformationToOrderCache() {

		$setOrderCacheKeys = [
			'payment_intent' => $this->paymentIntent->id,
			'payment_intent_client_secret' => $this->paymentIntent->client_secret
		];
		$orderID = $this->options['order_id'];

		foreach ($setOrderCacheKeys as $key => $value) {
			$this->pwcommerce->setOrderCacheValue($orderID, $key, $value);
		}
	}

	##############################
	# WEBHOOKS HANDLER
	##############################

	protected function ___handleWebhook($payload): int {

		$httpResponseStatus = 200;

		$payloadArray = json_decode($payload, true);
		$orderID = $payloadArray['data']['object']['metadata']['reference_id'];
		$webhookEventID = $payloadArray['id'];

		// PREVENT REPLAY ATTACK!
		if (!empty($this->pwcommerce->isAlreadyProcessedWebhook($orderID, $webhookEventID))) {
			// WEBHOOK ALREADY PROCESSED; ABORT!
			echo '⚠️  Webhook error: already processed.';
			$httpResponseStatus = 400;
			return $httpResponseStatus;
		}

		// -------
		$webhookSigningSecret = $this->getWebhookSecret();

		// VALIDATE EVENT
		$event = NULL;

		try {
			// $event = \Stripe\Event::constructFrom(
			// 	json_decode($payload, true)
			// );
			$event = \Stripe\Event::constructFrom($payloadArray);
		} catch (\UnexpectedValueException $e) {
			// Invalid payload
			echo '⚠️  Webhook error while parsing basic request.';
			// http_response_code(400);
			// exit();
			$httpResponseStatus = 400;
		}
		if ($webhookSigningSecret) {
			// Only verify the event if there is an endpoint secret defined
			// Otherwise use the basic decoded event
			$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
			try {
				$event = \Stripe\Webhook::constructEvent(
					$payload,
					$sigHeader,
					$webhookSigningSecret
				);
			} catch (\Stripe\Exception\SignatureVerificationException $e) {
				// Invalid signature
				echo '⚠️  Webhook error while validating signature.';
				// http_response_code(400);
				// exit();
				$httpResponseStatus = 400;
			}
		}

		#######
		// GOOD TO GO
		// Handle the specific webhook event
		$httpResponseStatus = $this->handleWebhookEvent($event, $payloadArray);

		// -----
		return $httpResponseStatus;
	}

	private function handleWebhookEventOLDDELETE($event, $payloadArray) {

		$httpResponseStatus = 200;

		switch ($event->type) {
			case 'payment_intent.succeeded':
				$paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent

				// TODO - UPDATE WIRECACHE! > CALL THE SET KEY,VALUE METHOD
				// Then define and call a method to handle the successful payment intent.
				$this->updateOrderCache($payloadArray);

				break;
			case 'payment_intent.payment_failed':
				$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod

				// TODO - DO WE NEED THIS?
				// Then define and call a method to handle the successful attachment of a PaymentMethod.
				$this->updateOrderCache($payloadArray);
				break;
			// TODO OTHER CASES? IN FUTURE, CONFIGURABLE? OR HOOKABLE IS ENOUGH?
			default:
				// Unexpected event type
				// error_log('Received unknown event type');

				// TODO 400 HERE?
				$httpResponseStatus = 400;
		}

		// ------
		return $httpResponseStatus;
	}

	private function handleWebhookEvent($event, $payloadArray) {

		// default to unexpected event type
		$httpResponseStatus = 400;
		$acceptedWebhookEvents = $this->getAcceptedWebhookEvents();
		if (in_array($event->type, $acceptedWebhookEvents)) {
			$httpResponseStatus = 200;
			// $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent

			######
			// update order cache
			// call method to handle the event
			$this->updateOrderCache($payloadArray);
		}

		// ------
		return $httpResponseStatus;
	}

	protected function ___getAcceptedWebhookEvents(): array {
		$acceptedWebhookEvents = [
			'payment_intent.succeeded',
			'payment_intent.payment_failed'
		];
		return $acceptedWebhookEvents;
	}

	private function getWebhookSecret() {
		$apiKeysForEnvironment = $this->getAPIKeysForEnvironment();
		$webhookSigningSecret = $apiKeysForEnvironment['webhook_signing_secret'];
		return $webhookSigningSecret;
	}

	private function updateOrderCache(array $payloadArray) {
		// note: can be multiple; we use index = webhook event id
		$webhookEventID = $payloadArray['id'];
		$webhookEventDataObject = $payloadArray['data']['object'];
		$webhookEventType = $payloadArray['type'];
		$orderID = $webhookEventDataObject['metadata']['reference_id'];

		// ++++++++++
		// if payment succeeded, update payment status too
		// NOTE: we let it remain at 3000 (awaiting payment) in case 'payment_intent.failed'
		if ($webhookEventType === 'payment_intent.succeeded') {
			$this->pwcommerce->setOrderCacheValue(
				$orderID,
				'payment_status',
				PwCommerce::PAYMENT_STATUS_PAID
			);
		}
		// ++++++++++
		// set some webhook details to cache as well
		$orderCacheWebhookDetails = [
			// e.g. "evt_3Q66biDJJW03hvkU1cOWQC0X"
			"id" => $webhookEventID,
			// e.g. 1728030492
			"created" => $payloadArray['created'],
			// e.g. "pi_3Q66biDJJW03hvkU1HhPNxjv"
			"payment_intent" => $webhookEventDataObject['id'],
			"client_secret" => $webhookEventDataObject['client_secret'],
			"expected_amount_in_cents" => $webhookEventDataObject['amount'],
			"received_amount_in_cents" => $webhookEventDataObject['amount_received'],
			"reference_id" => $orderID,
			"invoice_id" => $orderID,
			"idempotency_key" => $orderID,
			"currency" => $webhookEventDataObject['currency'],
			// i.e. 'type'
			// e.g. "payment_intent.succeeded"
			"webhook_event_type" => $webhookEventType

		];

		// note: $webhookEventID will be key for $orderCacheWebhookDetails array at ['payment']['webhooks'][$webhookEventID] = $orderCacheWebhookDetails
		$this->pwcommerce->trackWebhookInOrderCache(
			$orderID,
			$webhookEventID,
			$orderCacheWebhookDetails
		);
	}

	##############################
	# OTHER
	##############################

	public function getFailureReason() {
		// TODO CREATE THIS OR DELETE IF NOT IN USE
		return $this->session->stripeError;
	}

	private function getStripeElementsAppearance() {
		// file with $stripeElementsAppearance variable. Contains 'theme' and 'variables' values
		$file = "PWCommercePaymentStripeElementsAppearance.php";
		if (file_exists($this->config->paths->templates . "pwcommerce/" . $file)) {
			// use custom stripe elements appearance
			$path = $this->config->paths->templates . "pwcommerce/" . $file;
		} else {
			// use default stripe elements appearance
			$path = __DIR__ . DIRECTORY_SEPARATOR . $file;
		}
		include_once $path;

		// -------
		//  $stripeElementsAppearance is in $file
		// it has two elements, (i) theme => string and (ii) variables => array
		// here we remove empty elements from variables
		// this is to avoid warnings in Stripe Elements JS about empty values
		// the ternary is to skip 'theme' since it is not an array
		/** @var array $stripeElementsAppearance */
		$filteredStripeElementsAppearance = array_map(fn($item) => is_array($item) ? array_filter($item) : $item, $stripeElementsAppearance);
		// unset 'theme' if it is empty
		if (empty($filteredStripeElementsAppearance['theme'])) {
			unset($filteredStripeElementsAppearance['theme']);
		}

		// unset 'variables' if it is empty
		if (empty($filteredStripeElementsAppearance['variables'])) {
			unset($filteredStripeElementsAppearance['variables']);
		}
		// --------------
		return $filteredStripeElementsAppearance;
	}
}
