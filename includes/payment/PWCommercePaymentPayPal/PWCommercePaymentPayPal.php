<?php

namespace ProcessWire;

require_once __DIR__ . '/../../../vendor/autoload.php';

// TODO DELETE WHEN DONE
// use PayPalCheckoutSdk\Core\PayPalHttpClient;
// // sandbox/testing environment
// use PayPalCheckoutSdk\Core\SandboxEnvironment;
// // live/production environment
// use PayPalCheckoutSdk\Core\ProductionEnvironment;
// use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
// use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

/**
 *
 * PayPal payment class for ProcessWire
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 * Uses PayPal PHP Checkout SDK API for payment.
 * Used in conjuction with PayPal JS SDK on the client.
 *
 *
 */

// ====================
class PWCommercePaymentPayPal extends PWCommercePayment implements PWCommerceAddons {


	// @kongondo
	private $amount;
	// @kongondo
	private $createOrderValues;
	// @kongondo TODO
	private $isLiveEnvironment;
	// @kongondo TODO
	private $paypalConfigs;

	public function __construct(array $paypalConfigs, array $options = []) {

		parent::__construct();
		// TODO: @kongondo! do we need this reall? there is no defaultCurrency!?
		// $this->currency = $this->defaultCurrency;

		$this->paypalConfigs = $paypalConfigs;
		// -------
		// TODO @kongondo => BETTER CHECKS HERE PLUS ERROR HANDLING!
		// set PayPal settings coming in via configs
		// TODO - NEED TO COME FROM SHOP SETTINGS!
		// $this->currency = $paypalConfigs['currency']; // TODO @KONGONDO OK? - NO; GET FROM SHOP
		// @note: PayPal uses 3-character ISO-4217 codes to specify currencies in fields and variables.
		// @note: currently (December 2021, PayPal supports payments in 25 world currences)
		// @see: https://developer.paypal.com/docs/payouts/reference/country-and-currency-codes/
		// @see: https://www.paypal.com/us/webapps/mpp/country-worldwide
		$this->currency = $this->pwcommerce->getShopCurrency();
	}
	# === ABSTRACT PARENT CLASS METHODS === #
	// @see PWCommercePayments.php

	// 2. Set up your server to receive a call from the client
	/**
	 *This is the sample function to create an order. It uses the
	 *JSON body returned by buildRequestBody() to create an order.
	 */
	protected function createOrder(WireData $createOrderValues, $debug = false) {
		$this->createOrderValues = $createOrderValues;

		// -----------------
		// TODO DELETE WHEN DONE
		// $request = new OrdersCreateRequest();
		$request = new \PayPalCheckoutSdk\Orders\OrdersCreateRequest();

		$request->prefer('return=representation');
		$request->body = $this->buildRequestBody();
		// 3. Call PayPal to set up a transaction
		// $client = PayPalClient::client();
		// $client = PayPalClient::client();
		$client = $this->client();
		// $response = $client->execute($request);

		try {
			// Call API with your client and get a response for your call
			$response = $client->execute($request);
			// If call returns body in response, you can get the deserialized version from the result attribute of the response

		} catch (HttpException $ex) {
			// TODO: HOW TO HANDLE THIS GRACEFULLY?
			echo "CREATE EXCEPTION: " . $ex->statusCode;
			print_r($ex->getMessage());
		}

		// ============

		if ($debug) {
			print "Status Code: {$response->statusCode}\n";
			print "Status: {$response->result->status}\n";
			print "Order ID: {$response->result->id}\n";
			print "Intent: {$response->result->intent}\n";
			print "Links:\n";
			foreach ($response->result->links as $link) {
				print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
			}

			// To print the whole response body, uncomment the following line
			// echo json_encode($response->result, JSON_PRETTY_PRINT);
		}

		// 4. Return a successful response to the client.
		return $response;
	}

	// >>>>>>>>>>>>>>>>>>>>> CAPTURE TRANSACTION / CAPTURE ORDER {take payment} <<<<<<<<<<<<<<<<<<<<
	// @see: https://developer.paypal.com/docs/checkout/reference/server-integration/capture-transaction/

	// 2. Set up your server to receive a call from the client
	/**
	 *This function can be used to capture an order payment by passing the approved
	 *order ID as argument.
	 *
	 *@param $orderId
	 *@param $debug
	 *@return
	 */
	protected function captureOrder($orderId, $debug = false) {

		// TODO DELETE WHEN DONE
		// $request = new OrdersCaptureRequest($orderId);
		$request = new \PayPalCheckoutSdk\Orders\OrdersCaptureRequest($orderId);
		$request->prefer('return=representation');

		// 3. Call PayPal to capture an authorization
		// $client = PayPalClient::client();
		$client = $this->client();
		#$response = $client->execute($request);

		try {
			// Call API with your client and get a response for your call
			$response = $client->execute($request);

			// If call returns body in response, you can get the deserialized version from the result attribute of the response
		} catch (HttpException $ex) {
			// TODO: HOW TO HANDLE THIS GRACEFULLY?
			echo "CAPTURE EXCEPTION: " . $ex->statusCode;
			print_r($ex->getMessage());
		}

		// 4. Save the capture ID to your database. Implement logic to save capture to your database for future reference.
		if ($debug) {
			print "Status Code: {$response->statusCode}\n";
			print "Status: {$response->result->status}\n";
			print "Order ID: {$response->result->id}\n";
			print "Links:\n";
			foreach ($response->result->links as $link) {
				print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
			}
			print "Capture Ids:\n";
			foreach ($response->result->purchase_units as $purchase_unit) {
				foreach ($purchase_unit->payments->captures as $capture) {
					print "\t{$capture->id}";
				}
			}
			// To print the whole response body, uncomment the following line
			// echo json_encode($response->result, JSON_PRETTY_PRINT);
		}

		return $response;
	}

	protected function isSuccessfulPaymentCapture($response, $options = []) {
		$isSuccessfulPaymentCapture = false;
		// expected order values
		$expectedOrderID = $options['expected_order_id'];
		$expectedOrderAmountValueInCents = $options['expected_amount_value_in_cents'];
		$expectedOrderCurrency = $options['expected_order_currency'];
		// echo "Expected Order ID: " . $expectedOrderID . "\n";
		// echo "Expected Order Amount Value: " . $expectedOrderAmountValueInCents . "\n";
		// echo "Expected Order Currency: " . $expectedOrderCurrency . "\n";

		// ---------
		// payment provider processed payment values
		/** @var stdClass $purchaseUnits */
		$purchaseUnits = $response->result->purchase_units[0];
		/** @var stdClass $paidAmount */
		$paidAmount = $purchaseUnits->amount;
		// @note: converting to cents TODO ok? why?
		// $paidAmountValueInCents = $paidAmount->value * 100;
		// $paidAmountValueInCents = $this->pwcommerce->getAmountInCents($paidAmount->value);
		$paidAmountMoney = $this->pwcommerce->money($paidAmount->value);
		$paidAmountValueInCents = (int) $paidAmountMoney->getAmount();

		$paidAmountCurrency = $paidAmount->currency_code;



		// TODO: should statuscode be a string?
		// @see: https://developer.paypal.com/docs/api/reference/api-responses/
		// below might be overkill, but need to be 100% certain!
		// TODO @note: below specific to PayPal. when we expand to include other Payment Providers, need to amend!
		if (
			// did we get complete status code
			(int) $response->statusCode === 201 &&
			$response->result->status === 'COMPLETED' &&
			// does the reference/invoice reference we sent with order creation match our expected order ID
			(int) $purchaseUnits->reference_id === (int) $expectedOrderID &&
			// does the paid amount match the total expected order amount
			$paidAmountValueInCents === $expectedOrderAmountValueInCents &&
			// does the payment currency match our shop's currency?
			// TODO: this currency should come from PWCommerceUtilities to get shop currency!
			$paidAmountCurrency == $expectedOrderCurrency

		) {
			$isSuccessfulPaymentCapture = true;
		}


		return $isSuccessfulPaymentCapture;
	}

	protected function render() {

		if ($this->getTotalAmount() <= 0) {
			throw new WireException("Products are not set");
		}

		// get urlSegments for checkout from session
		// note: can be custom ones or language specific ones!
		// e.g. 'success' => array
		// 1026 => 'compleet'
		// 1182 => 'complete'

		// e.g. /checkout/cancel/
		$cancelUrl = $this->session->checkoutPageHttpURL . $this->session->checkoutCancelUrlSegment . "/";
		$failUrl = $this->session->checkoutPageHttpURL . $this->session->checkoutConfirmationUrlSegment . "/?failure=";


		// -----------------
		// TODO DELETE MOST OF THESE AS NO LONGER NEEDED!
		$formTemplate = new TemplateFile(__DIR__ . DIRECTORY_SEPARATOR . "payment_form.php");
		// ---------------
		// set variables for JS
		// for script tag
		$formTemplate->set("currency", $this->currency);
		$formTemplate->set("clientID", $this->getClientID());
		// for custom hidden inputs to tell JS where to redirect
		$formTemplate->set("failUrl", $failUrl);
		$formTemplate->set("cancelUrl", $cancelUrl);

		return $formTemplate->render();
	}

	/**
	 * Get the fields data to be used to build the backend settings form for PayPal.
	 *
	 * For use in PWCommerce admin backend.
	 * @see documentation for required structure of the schema.
	 *
	 * @access protected
	 * @return array Array of fields configurations.
	 */
	protected function getFieldsSchema() {
		$schema = [
			[
				'name' => 'sandbox_business',
				'type' => 'email',
				'label' => $this->_('PayPal Sandbox Email'),
				'description' => $this->_('Specify the sandbox/testing business email.'),
				'notes' => $this->_('This is the testing business email. This value is found in your app in your PayPal developer dashboard.'),
			],
			[
				'name' => 'sandbox_client_id',
				'type' => 'text',
				'label' => $this->_('Sandbox Client ID'),
				'description' => $this->_('Specify the sandbox/testing client ID.'),
				'notes' => $this->_('This is the testing client ID. This value is found in your app in your PayPal developer dashboard.'),
			],
			[
				'name' => 'sandbox_client_secret',
				'type' => 'text',
				'label' => $this->_('Sandbox Client Secret'),
				'description' => $this->_('Specify the sandbox/testing secret key.'),
				'notes' => $this->_('This is the testing client secret key. This value is found in your app in your PayPal developer dashboard.'),
				'collapsed' => true,
			],
			[
				'name' => 'is_live',
				'type' => 'checkbox',
				'label' => $this->_('Use Live Keys'),
				'description' => $this->_('Specify if this shop is in live/production versus sandbox/testing mode.'),
				'notes' => $this->_('Live mode should be used for real purchases for a shop in production. For testing purposes, please make sure that you uncheck this box.'),
			],
			[
				'name' => 'live_business',
				'type' => 'email',
				'label' => $this->_('PayPal Live Business Email'),
				'description' => $this->_('Specify the live/production business email.'),
				'notes' => $this->_('This is the business email registered with PayPal for production use.'),
			],
			[
				'name' => 'live_client_id',
				'type' => 'text',
				'label' => $this->_('Live Client ID'),
				'description' => $this->_('Specify the live/production client ID for purchases.'),
				'notes' => $this->_('This is the live client ID. This value is found in your app in your PayPal developer dashboard.'),
				'collapsed' => true,
			],
			[
				'name' => 'live_client_secret',
				'type' => 'text',
				'label' => $this->_('Live Client Secret'),
				'description' => $this->_('Specify the live/production secret key for purchases.'),
				'notes' => $this->_('This is the live client secret key. This value is found in your app in your PayPal developer dashboard.'),
				'collapsed' => true,
			],
		];
		return $schema;
	}

	# === IMPLEMENTED INTERFACE CLASS METHODS === #
	// @see PWCommerceAddons.php

	public function getClassName() {
		$className = "PWCommercePaymentPayPal";
		return $className;
	}

	public function getType() {
		return "payment";
	}

	public function getTitle() {
		return $this->_("PayPal");
	}

	public function getDescription() {
		$description = $this->_("PayPal is an online payment system that makes paying for things online and sending and receiving money safe and secure. Check out faster, safer and more easily with PayPal, the service that lets you pay, send money, and accept payments without having to enter your financial details each time.");
		return $description;
	}

	# === CLASS-SPECIFIC METHODS === #

	// >>>>>>>>>>>>>>>>>>>>> SET UP TRANSACTION / CREATE ORDER  <<<<<<<<<<<<<<<<<<<<
	// @see: https://developer.paypal.com/docs/checkout/reference/server-integration/set-up-transaction/

	/**
	 * Returns PayPal HTTP client instance with environment that has access
	 * credentials context. Use this instance to invoke PayPal APIs, provided the
	 * credentials have access.
	 */
	public function client() {
		// return new PayPalHttpClient(PwCommerce::environment());
		// return new PayPalHttpClient($this->environment());
		return new \PayPalCheckoutSdk\Core\PayPalHttpClient($this->environment());
	}

	/**
	 * Set up and return PayPal PHP SDK environment with PayPal access credentials.
	 * This sample uses SandboxEnvironment. In production, use ProductionEnvironment.
	 */
	public function environment() {
		// ----
		// TODO - MOVE TO PAYPAL SETTINGS!
		// TODO @KONGONDO -> NEED TO CHECK IF IN LIVE VS SANDBOX AND CHANGE CLASS ACCORDINGLY! -> E.G. CAN HAVE INPUTS FOR BOTH SANDBOX AND LIVE AND A TOGGLE OR CHECKBOX FOR IN LIVE VS TEST/SANDBOX. COULD USE SHOWIF?
		// TODO @kongondo need to catch errors, e..g. empty / missing values!
		$paypalConfigs = $this->paypalConfigs;
		if (!empty($paypalConfigs['is_live'])) {
			// LIVE/PRODUCTION ENVIRONMENT
			$clientId = $paypalConfigs['live_client_id'];
			$clientSecret = $paypalConfigs['live_client_secret'];

			// return new ProductionEnvironment($clientId, $clientSecret);
			return new \PayPalCheckoutSdk\Core\ProductionEnvironment($clientId, $clientSecret);
		} else {
			// SANDBOX/TESTING ENVIRONMENT
			$clientId = $paypalConfigs['sandbox_client_id'];
			$clientSecret = $paypalConfigs['sandbox_client_secret'];

			// return new SandboxEnvironment($clientId, $clientSecret);
			return new \PayPalCheckoutSdk\Core\SandboxEnvironment($clientId, $clientSecret);
		}
	}

	// TODO: DELETE IF NOT IN USE
	private function getSandboxCredentials() {
		$credentials = [
			'client_id' => '',
			'client_secret' => ''
		];
		// ----------
		return $credentials;
	}

	private function getLiveCredentials() {
		$credentials = [
			'client_id' => '',
			'client_secret' => ''
		];
		// ----------
		return $credentials;
	}

	private function getClientID() {
		// TODO: NEED TO CHECK FOR EMPTIES, HANDLE ERRORS! BUT CAN DO THAT IN CONSTRUCT MAYBE?
		$paypalConfigs = $this->paypalConfigs;
		$clientID = !empty($paypalConfigs['is_live']) ? $paypalConfigs['live_client_id'] : $paypalConfigs['sandbox_client_id'];
		// --------
		return $clientID;
	}

	/**
	 * Setting up the JSON request body for creating the order with minimum request body. The intent in the
	 * request body should be "AUTHORIZE" for authorize intent flow.
	 *
	 */
	private function buildRequestBody() {
		// @see: https://developer.paypal.com/docs/checkout/standard/integrate/
		$uniqueInvoiceID = $this->createOrderValues->referenceID . "_" . time();

		return array(
			'intent' => 'CAPTURE',
			'application_context' => [
				// TODO DELETE WHEN DONE; NOT NEEDED AND ACTUALLY DISCOURAGED
				// 'return_url' => 'https://example.com/return',
				// 'cancel_url' => 'https://example.com/cancel'
				// TODO ARE THESE IN USE? IF NOT, DELETE!
				'return_url' => $this->processUrl,
				'cancel_url' => $this->cancelUrl
			],
			'purchase_units' => [
				[
					'reference_id' => $this->createOrderValues->referenceID,
					// @KONGONDO TODO
					// 'description' => 'Sporting Goods',
					// 'custom_id' => 'CUST-HighFashions',
					// @KONGONDO TODO - MAKE CUSTOM?
					// TODO: this has to be truly unique! e.g. in testing 2250 may be an order in two different dev environments so will fail! append timestamp! Then use 'reference_id' for our order id comparison
					// 'invoice_id' => $this->createOrderValues->referenceID,
					'invoice_id' => $uniqueInvoiceID,
					'amount' => [
						// TODO: MAYBE JUST PASS DIRECTLY WITH $this->createOrderValues? - NO; also needed in render
						'currency_code' => $this->currency,
						//'value' => '220.00'
						// @note: if we don't use number_format() we get DECIMAL_PRECISION error with some values!
						// 'value' => $this->createOrderValues->amount,
						//
						// @note:
						// - if we don't specify a thousand separator, number_format defaults to comma (",")
						// - the PayPal API will trip on the comma leading to a PayPalHttp\HttpException with the message 'Request is not well-formed, syntactically incorrect, or violates schema.'
						// - the fix for this is NOT TO USE a thousand separator (pass empty string to the 4th parameter)
						//
						// 'value' => number_format($this->createOrderValues->amount, 2)
						'value' => number_format($this->createOrderValues->amount, 2, ".", "")
					]
				]
			]
		);
	}

	##############################

	public function getFailureReason() {
		return $this->session->paypalError;
	}


}