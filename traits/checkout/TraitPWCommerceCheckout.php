<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Checkout: Trait class for PWCommerce Checkout.
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


trait TraitPWCommerceCheckout {


	protected $btnClass = '';
	// ---------
	private $isCustomForm;
	private $isUseCustomFormInputNames;
	private $customFormFields;
	// URL SEGMENTS
	private $successUrlSegment = 'success';
	private $confirmationUrlSegment = 'confirmation';
	private $shippingUrlSegment = 'shipping';
	private $invoiceUrlSegment = 'invoice';
	private $postProcessUrlSegment = 'post-process';
	private $cancelUrlSegment = 'cancel';



	/**
	 * Get Checkout U R L Segments Vars.
	 *
	 * @return mixed
	 */
	private function getCheckoutURLSegmentsVars() {
		// the only allowed checkout url segments that render markup!
		// keys are actual segment and values are class props
		// NOTE can be overriden via render $options!
		return [
			'success' => 'successUrlSegment',
			'confirmation' => 'confirmationUrlSegment',
			'shipping' => 'shippingUrlSegment',
			'cancel' => 'cancelUrlSegment',
			// TODO REMOVE FROM LIST?! THESE SEGMENTS NOT FOR RENDERING VIEWS!
			// FOR API USE!
			// 'post-process' => 'postProcessUrlSegment',
			// 'invoice' => 'invoiceUrlSegment',

		];
	}

	/**
	 * Get Checkout U R L Segments Vars For Session.
	 *
	 * @return mixed
	 */
	private function getCheckoutURLSegmentsVarsForSession() {
		// the only allowed checkout url segments that render markup!
		// keys are this class props and values are expected session names in Order class (and related traits)

		return [
			'successUrlSegment' => 'checkoutSuccessUrlSegment',
			'confirmationUrlSegment' => 'checkoutConfirmationUrlSegment',
			'shippingUrlSegment' => 'checkoutShippingUrlSegment',
			'cancelUrlSegment' => 'checkoutCancelUrlSegment',
			// TODO REMOVE FROM LIST?! THESE SEGMENTS NOT FOR RENDERING VIEWS!
			// FOR API USE!
			// 'post-process' => 'postProcessUrlSegment',
			// 'invoice' => 'invoiceUrlSegment',

		];
	}

	/**
	 * Set Checkout Url Segments.
	 *
	 * @param array $options
	 * @return mixed
	 */
	private function setCheckoutUrlSegments($options) {
		// @NOTE: keys are actual segment and values are class props
		// e.g. 'successUrlSegment' [$this->successUrlSegment]
		$allowedUrlSegmentsVars = $this->getCheckoutURLSegmentsVars();

		// ML SITE
		if (!empty($options['url_segments_languages'])) {
			$languageUrlSegmentsOptions = $options['url_segments_languages'];
			// set values
			foreach ($languageUrlSegmentsOptions as $key => $values) {
				// e.g. 'success' => array
				// 1026 => 'compleet'
				// 1182 => 'complete'
				##########
				// ensure allowed var
				if (empty($allowedUrlSegmentsVars[$key])) {
					// skip!
					continue;
				}
				// -------
				// GOOD TO GO
				// get current user language ID. It is what we are trying to match in values
				$userLanguageID = $this->wire('user')->language->id;
				if (!empty($values[$userLanguageID])) {
					$urlSegmentProperty = $allowedUrlSegmentsVars[$key];
					// set to URL segment prop
					// e.g. 'cancelUrlSegment' [$this->cancelUrlSegment]
					$this->$urlSegmentProperty = $values[$userLanguageID];
				}
			}
		} else if (!empty($options['url_segments_custom'])) {
			$customUrlSegmentsOptions = $options['url_segments_custom'];

			// set values
			foreach ($customUrlSegmentsOptions as $key => $value) {
				// e.g. 'success' => 's',
				// 'confirmation' => 'order-confirmation',
				##########
				// ensure allowed var
				if (empty($allowedUrlSegmentsVars[$key])) {
					// skip!
					continue;
				}
				// -------
				// GOOD TO GO
				// ensure segment value not empty
				if (!empty($value)) {
					$urlSegmentProperty = $allowedUrlSegmentsVars[$key];
					// set to URL segment prop
					// e.g. 'cancelUrlSegment' [$this->cancelUrlSegment]
					$this->$urlSegmentProperty = $value;
				}
			}
		}
	}

	/**
	 * Save the final values for checkout url segments to session.
	 *
	 * @return mixed
	 */
	private function setCheckoutUrlSegmentsOptionsToSession() {
		// NOTE: keys are this class props and values are expected session names in Order class (and related traits)
		// e.g. 'successUrlSegment' [$this->successUrlSegment]
		$allowedUrlSegmentsVarsForSession = $this->getCheckoutURLSegmentsVarsForSession();
		// -------
		// set values
		// NOTE: can be custom and/or user language specific!
		foreach ($allowedUrlSegmentsVarsForSession as $urlSegmentClassProp => $urlSegmentSessionName) {
			// e.g. 'confirmationUrlSegment' => 'bevestig-bestelling'
			$this->session->set($urlSegmentSessionName, $this->$urlSegmentClassProp);
		}
	}

	/**
	 * Render.
	 *
	 * @param array $options
	 * @return string|mixed
	 */
	public function render(array $options = []) {
		// for backward compatibility since PWCommerce 010
		return $this->renderCheckout($options);
	}


	/**
	 * Render Checkout.
	 *
	 * @param array $options
	 * @return string|mixed
	 */
	public function renderCheckout(array $options = []) {
		// set render options for Checkout.
		$this->setRenderOptions($options);

		// set checkout custom url segments if any sent
		// @see getCheckoutURLSegmentsVars
		$this->setCheckoutUrlSegments($options);

		// save final values for checkout url segments to the session for later retrieval by payment classes
		$this->setCheckoutUrlSegmentsOptionsToSession();

		# ++++++++++++
		// NOTE LANGUAGE AWARE AND CUSTOM VALUES via $options
		// E.G. 'success' -> 'succes' for languages
		// and 'confirm-order' instead of 'confirmation'.
		$case = $this->input->urlSegment1;

		// CHECK IF ORDER ALREADY PAID VIA A 'WRONG SESSION' + WEBHOOKS
		// if yes and in 'post-process' url segment, force redirect to $this->successUrlSegment
		// but only if session or order cache has not been emptied
		// if they have been emptied, redirect to home

		if (!empty($this->isOrderAlreadyPaid())) {
			// NOTE: if order already paid $this->isOrderAlreadyPaid() will empty the CART
			// NOTE: renderSuccess() will call getCompletedOrder(). This will remove remove sessions
			// in the 'original browser'
			// it will also delete the order cache
			// this means, in the broswer with the lost session, if customer reloads the success page or the post-process
			// page, they will be redirected to the home page (former case) or get an error (latter case)
			if ($case === $this->postProcessUrlSegment) {

				$checkoutPageHttpURL = $this->session->checkoutPageHttpURL;

				if (!empty($checkoutPageHttpURL)) {
					$redirectURL = "{$this->successUrlSegment}/";
					$finalRedirectURL = "{$checkoutPageHttpURL}{$redirectURL}";

				} else {
					// REDIRECT TO HOME PAGE; SESSION ALREADY CLEARED
					// i.e., 'checkoutPageHttpURL' has been deleted from the session
					$finalRedirectURL = "/";
				}


				// ------
				return $this->session->redirect($finalRedirectURL);
			}

		}




		// switch ($this->input->urlSegment1) {
		switch ($case) {
			case '':
				// ----------
				return $this->renderForm();
				break;

			case $this->confirmationUrlSegment:
				return $this->renderConfirmation();

			case $this->shippingUrlSegment:
				return $this->renderShippingConfirmation();

			case $this->invoiceUrlSegment:
				return $this->renderInvoice();

			case $this->successUrlSegment:
				return $this->renderSuccess();

			case $this->cancelUrlSegment:
				return $this->renderCancel();

			case $this->postProcessUrlSegment:
				return $this->renderPostProcess();

			default:
				// TODO - 404 INSTEAD?
				throw new WireException("Error Processing Request");

				break;
		}
	}

	/**
	 * Set render options for Checkout.
	 *
	 * @param array $options
	 * @return mixed
	 */
	private function setRenderOptions(array $options = []) {
		$defaultOptions = $this->getDefaultRenderOptions();
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}

		// ----------
		// set the render checkout options as class properties
		$this->isCustomForm = $options['is_custom_form'];
		$this->customFormFields = $options['custom_form_fields'];
		$this->isUseCustomFormInputNames = $options['is_use_custom_form_input_names'];
	}

	/**
	 * Get default options for checkout form render.
	 *
	 * @return mixed
	 */
	private function getDefaultRenderOptions() {
		// default options for checkout form render
		$defaultOptions = [
			// determines if to output internal form using renderForm() vs dev using custom form
			'is_custom_form' => false,
			// will hold schema for form inputs if custom form will be used
			'custom_form_fields' => [],
			// when custom form is used, will it use custom input names or identical names to internal form
			'is_use_custom_form_input_names' => false

		];
		return $defaultOptions;
	}


	// --------------

	/**
	 * Render Form.
	 *
	 * @return string|mixed
	 */
	private function renderForm() {

		if ($this->isCartEmpty())
			$message = $this->_("Your cart is empty");
		if (!empty($this->isCustomForm)) {
			// CHECKOUT IS USING CUSTOM FORM
			if ($this->input->post->customerForm) {
				// HANDLE FORM SUBMISSION
				// @note: customerForm is the name of the InputfieldSubmit button in this form
				// @note: $this->initProcessCustomOrderCustomerForm() will redirect to /confirmation/ on successful form processing
				$response = $this->initProcessCustomOrderCustomerForm();
			} else {
				$response = new WireData();
				if (!empty($message)) {
					$response->message = $message;
				}
			}
			// ----------
			$out = $response;
		} else {
			// CHECKOUT IS USING INTERNAL PWCOMMERCE FORM
			if (!empty($message)) {
				$out = $message;
			} else {
				$form = $this->getOrderCustomerForm();
				// @note: customerForm is the name of the InputfieldSubmit button in this form
				// HANDLE FORM SUBMISSION
				if ($this->input->post->customerForm) {

					// ORDER CUSTOMER FORM SUBMITTED
					// @note: customerForm is the name of the InputfieldSubmit below
					// @note: this will return true if no errors found on the form
					// NOTE: IN 'traits\order\TraitPWCommerceProcessOrderForm.php'
					if (!empty($this->processCustomerForm($form))) {
						// NO FORM ERRORS: REDIRECT TO confirmation
						return $this->session->redirect("./{$this->confirmationUrlSegment}/");
					}
				}
				// ------
				// (RE) RENDER THE INBUILT ORDER CUSTOMER FORM
				$out = $form->render();
			}
		}

		return $out;
	}

	/**
	 * Render Confirmation.
	 *
	 * @return string|mixed
	 */
	private function renderConfirmation() {

		// if cart is empty for some reason, go to homepage
		if ($this->isCartEmpty()) {
			$this->session->redirect("/");
		}

		/** @var Page $orderPage */
		$orderPage = $this->getOrderPage();
		if (!$orderPage) {
			$this->session->redirect($this->page->url);
		}


		// MULTIPLE MATCHED SHIPPING RATES

		if ($this->isNeedToSelectFromMultipleMatchedShippingRates()) {
			// redirect to /checkout/shipping/ for customer to confirm their shipping choice
			$redirectURL = "../{$this->shippingUrlSegment}/";
			return $this->session->redirect($redirectURL);
		}

		// ----------------------

		/** @var WireData $order */
		$order = $this->getOrder();
		/** @var WireArray $orderLineItems */
		$orderLineItems = $this->getOrderLineItems();
		/** @var WireData $orderCustomer */
		$orderCustomer = $this->getOrderCustomer();
		/** @var WireArray $orderMatchedShippingRates */
		$orderMatchedShippingRates = $this->getOrderMatchedShippingRates($orderPage);

		$selectedMatchedShippingRateID = (int) $this->session->selectedMatchedShippingRateID;
		if (!empty($selectedMatchedShippingRateID) && $orderMatchedShippingRates->count() > 1) {
			$orderMatchedShippingRates = $orderMatchedShippingRates->filter("shippingRateID={$selectedMatchedShippingRateID}");
		}
		/** @var float $orderSubtotal */
		$orderSubtotal = $this->getOrderLineItemsTotalDiscountedWithTax();
		$orderGrandTotalMoney = $this->getOrderGrandTotalMoney();
		/** @var float $orderGrandTotalAmount */
		$orderGrandTotalAmount = $this->getWholeMoneyAmount($orderGrandTotalMoney);

		// ---------

		// SET HANDLING FEE VALUES
		// @note: method for this also exists in Order -> have these values in $order here anyway
		$orderHandlingFeeValues = $this->setOrderHandlingFeeValues($order);

		// --------------------
		// GRANDTOTAL
		$orderMatchedShippingRatesCount = $orderMatchedShippingRates->count();
		$isOrderGrandTotalComplete = $orderMatchedShippingRatesCount < 2;
		$isOrderWithoutMatchedShippingRates = empty($orderMatchedShippingRatesCount) ? true : false;

		// TODO: TEST THIS!
		// IS ORDER CONFIRMED
		// @note: means although grand total may be complete, customer has not confirmed order
		$isOrderConfirmed = false;

		// ------------

		$out = '';
		$failure = $this->sanitizer->entities($this->input->get->failure);
		if (!empty($failure)) {
			$t = $this->getPWCommerceTemplate("order-payment-declined.php");
			$t->set("paymentDeclinedReason", $failure);
			$out .= $t->render();
		}

		// ==============
		// NOTE
		// here we set the property order to the value of $order so that it can be used in the newly created virtual TemplateFile $t which uses the file "checkout-confirmation-html.php" AS ITS TEMPLATE FILE
		// @note: TemplateFile extends WireData, hence here this is what happens: parent::set($property, $value);
		// @note: $order will subsequenty be set to what template that needs it later on, e.g. @see: how in the file "checkout-confirmation-html.php" $order is set to the TemplateFile "order-customer-information.php"

		########################
		// TODO FOR GIFT CARDS
		$giftCards = new WireArray();
		$giftCard1 = new WireData();
		$giftCard1->id = 1234; // @NOTE PROCESSWIRE PAGE ID FOR THIS GIFT CARD
		// TODO: example test gift card: 7895 8524 7452 5862
		// TODO should we do the **** **** **** 5862 here or in template?
		$giftCard1->lastFourDigits = "5862"; // @NOTE lAST FOR DIGITS FOR THIS GIFT CARD
		$giftCard1->redeemed = 150.25; // TODO CENTS? @NOTE: THIS SHOULD SHOW AS NEGATIVE SO *-1!!!??
		// TODO I DON'T THINK NEEDED?! CAN BE DONE IN TEMPLATE ON THE FLY?
		// TODO if needed here then use $this->getValueFormattedAsCurrencyForShop($giftCard1->redeemed)
		$giftCard1->redeemedAsCurrency = 150.25; // TODO CENTS? @NOTE: THIS SHOULD SHOW AS NEGATIVE SO *-1!!!??
		// ------
		// add GC to GCS array
		$giftCards->add($giftCard1);

		#######################

		$t = $this->getPWCommerceTemplate("checkout-confirmation-html.php");
		$t->set("order", $order);
		$t->set("orderLineItems", $orderLineItems);
		$t->set("orderCustomer", $orderCustomer);
		// TODO - REMOVE FROM HERE; ONLY NEEDED IN renderConfirmShipping??
		$t->set("orderMatchedShippingRates", $orderMatchedShippingRates);
		$t->set("orderHandlingFeeValues", $orderHandlingFeeValues);
		$t->set("orderSubtotal", $orderSubtotal);
		$t->set("isOrderGrandTotalComplete", $isOrderGrandTotalComplete);
		$t->set("isOrderWithoutMatchedShippingRates", $isOrderWithoutMatchedShippingRates);
		$t->set("orderGrandTotal", $orderGrandTotalAmount);
		$t->set("isOrderConfirmed", $isOrderConfirmed);
		// =========
		// TODO WIP FOR GIFTCARDS
		/** @var WireArray $giftCards */
		// $t->set("giftCards", $giftCards);

		$out .= $t->render();

		$out .= $this->getPaymentClass()->render();

		// --------------------
		// IF USING CUSTOM FORM, WE RETURN WIREDATA as response
		if (!empty($this->isCustomForm)) {
			$response = new WireData();
			$response->success = true;
			$response->isProcessedForm = true;
			// TODO: rephrase?
			$response->message = $this->_("Order processed and created successfully.");
			$response->content = $out;
			/** @var WireData $out */
			$out = $response;
		}

		// --------------
		return $out;
	}

	/**
	 * Render Shipping Confirmation.
	 *
	 * @return string|mixed
	 */
	private function renderShippingConfirmation() {
		// IF NO SESSION; REDIRECT
		$this->checkOrderIDSession();
		// IF CART EMPTY, REDIRECT TO HOMEPAGE OK?
		if ($this->isCartEmpty()) {
			$this->session->redirect("/");
		}

		$out = "";

		// ------
		// if ($this->session->isMatchedMultipleShippingRates) {
		if ($this->isNeedToSelectFromMultipleMatchedShippingRates()) {
			/** @var Page $orderPage */
			$orderPage = $this->getOrderPage();

			/** @var WireArray $orderMatchedShippingRates */
			$orderMatchedShippingRates = $this->getOrderMatchedShippingRates($orderPage);
			// $matchedShippingZoneRatesIDs = $this->session->matchedShippingZoneRatesIDs;

			// HANDLE FORM SUBMISSION
			if ($this->input->post->shippingConfirmationForm) {
				// NOTE: IN 'traits\order\TraitPWCommerceProcessOrderForm.php'
				/** @var WireData $response */
				$response = $this->processCustomerShippingConfirmation();
				// -------
				if (!empty($response->success)) {
					// SHIPPING SELECTION SAVED TO ORDER SUCCESSFULLY
					// redirect to confirmation and payment (again)
					$redirectURL = "../{$this->confirmationUrlSegment}/";
					return $this->session->redirect($redirectURL);
				} else {
					// ERRORS FOUND
					return $response;
				}
			}

			// -------
			$t = $this->getPWCommerceTemplate("checkout-shipping-confirmation-html.php");
			$t->set("orderMatchedShippingRates", $orderMatchedShippingRates);
			$out = $t->render();
		}

		if (!empty($this->isCustomForm)) {
			// USING CUSTOM FORM: PREPARE RESPONSE AS WIREDATA

			$response = new WireData();
			$response->success = true;
			$response->isProcessedForm = true;
			// TODO: rephrase?
			$response->message = $this->_("Order is complete.");
			$response->content = $out;
			/** @var WireData $out */
			$out = $response;
		}

		// --------------
		return $out;
	}

	/**
	 * Render Invoice.
	 *
	 * @return string|mixed
	 */
	private function renderInvoice() {
		// If we don't have order id, we will try and find it from url
		// helpful for delayed payment notifications, where we don't have session available TODO?
		$orderID = (int) $this->input->urlSegment2;
		// NOTE IN 'traits\payment\TraitPWCommercePayment.php'
		/** @var WireData $invoiceCreationResponse */
		$invoiceCreationResponse = $this->processInvoice($orderID);

		if (!empty($invoiceCreationResponse->success)) {
			// e.g. $redirectURL = "../../success/";
			$redirectURL = "../../{$this->successUrlSegment}/";
			// $redirectURL = "../../success/{$orderID}/";
			return $this->session->redirect($redirectURL);
		} else {
			$failureReason = $invoiceCreationResponse->message;
			$this->session->redirect("../../{$this->confirmationUrlSegment}/?failure=" . $failureReason);
		}
	}

	/**
	 * Render Post Process.
	 *
	 * @return string|mixed
	 */
	private function renderPostProcess() {


		// NOTE - HERE QUICK POST-PROCESSING FOR PAYMENTz METHODS THAT REQUIRE IT
		// THESE ARE FOR AFTER PAYMENT CHECKS; E.G. STRIPE
		// NOTE: IN 'traits\payment\TraitPWCommercePayment.php'
		$response = $this->postProcessPayment();

		if (!empty($response->success)) {
			// ORDER CREATED SUCCESSFULLY FROM CUSTOM ORDER FORM
			// redirect to confirmation and payment
			// $redirectURL = "success/";
			$redirectURL = "{$this->successUrlSegment}/";
		} else {
			// TODO: FAILURE OR RECONFIRM?
			// $redirectURL = "confirmation/";
			$redirectURL = "{$this->confirmationUrlSegment}/";
		}

		# DETERMINE CHECKOUT PAGE HTTP URL
		// if session is 'lost', we get it from the order cache
		if ($this->isOrderSessionLost()) {
			# SESSION IS LOST
			// get from order cache
			$orderID = $this->session->get(PwCommerce::ORDER_LOST_SESSION_ORDER_ID_NAME);
			$orderCache = $this->pwcommerce->getOrderCache($orderID);
			$checkoutPageHttpURL = $orderCache['checkout_page_http_url'];

			// WE ALSO APPEND URL PARAMS
			$urlParams = "?order_id={$orderCache['order_id']}";
		} else {
			// SESSION NOT LOST
			$checkoutPageHttpURL = $this->session->checkoutPageHttpURL;
			$urlParams = "";
		}

		$finalRedirectURL = "{$checkoutPageHttpURL}{$redirectURL}{$urlParams}";



		// ------
		return $this->session->redirect($finalRedirectURL);
	}

	/**
	 * Render Success.
	 *
	 * @return string|mixed
	 */
	private function renderSuccess() {


		// IF NO SESSION; REDIRECT
		$this->checkOrderIDSession();


		// -----

		// we check session early if this was an invoice order
		// this is because this will be emptied below in $this->getCompletedOrder()
		$isInvoiceOrder = $this->isInvoiceOrder();


		// TODO IF SESSION IS LOST; WE ONLY SHOW MINIMIMAL STUFF!!! ************
		//  TODO maybe need to redirect to success page with order id in url param in cases where session is lost? would make our work easier

		// ------------------
		// GET COMPLETED ORDER + CLEANUP SESSIONS
		// @note: contains objects we need later below, including $order
		/** @var WireData $response */
		$response = $this->getCompletedOrder();

		if (empty($response->success)) {
			// TODO THROW EXCEPTION HERE

		}

		// GET THE CURRENT SESSION'S ORDER ID
		// at this point, an unpublished order has been created
		// and the session orderId has not yet been deleted

		// --------------

		// ---------------
		// CHECK IF THIS ORDER HAS BEEN PAID
		// we check the payment status
		// @NOTE: WE ONLY CHECK FOR NON INVOICE ORDERS!
		$orderPaymentStatus = $response->order->paymentStatus;

		if (empty($isInvoiceOrder)) {

			// NON-INVOICE PAYMENT CHECK
			if ((int) $orderPaymentStatus !== PwCommerce::PAYMENT_STATUS_PAID) {
				// ORDER NOT PAID FOR BUT NOT INVOICE ORDER!, THROW EXCEPTION
				throw new Wire404Exception($this->_("Invalid order"));
			}
		} else {

			// INVOICE PAYMENTS CHECK
			// check if shop accepts invoice payments!
			if (empty($this->isShopAcceptsInvoicePayments())) {
				throw new WireException($this->_("Shop does not accept invoice payments!"));
			}
		}

		// IS ORDER CONFIRMED
		// @note: since we are in success, it means order has been confirmed
		$isOrderConfirmed = true;

		// TODO - SET SESSION VALUE FOR ISORDERSESSIONLOST!!! THEN DECIDE IF TO LIMIT VARIABLES TO TEMPLATE OR DO CHECKS AT TEMPLATE LEVEL?? I.E; TO SHOW OR NOT TO SHOW DETAILS!
		// IF ORDER SESSION LOST
		// ONLY SHOW LIMITED MESSAGE TO CUSTOMER

		// ====================
		$t = $this->getPWCommerceTemplate("order-complete.php");

		if (empty($this->isOrderSessionLost())) {

			// --------------------
			// GRANDTOTAL
			$isOrderGrandTotalComplete = true;


			#############################
			# SESSION ORDER IS NOT LOST!
			# DO NOT LIMIT RESPONSE
			##############################
			/** @var WireData $order */
			$order = $response->order;
			$t->set("order", $order);
			/** @var WireArray $orderLineItems */
			$t->set("orderLineItems", $response->orderLineItems);
			/** @var WireData $orderCustomer */
			$t->set("orderCustomer", $response->orderCustomer);
			// --------------
			/** @var float $orderSubtotal */
			$t->set("orderSubtotal", $response->orderSubtotal);
			/** @var bool $isOrderGrandTotalComplete */
			$t->set("isOrderGrandTotalComplete", $isOrderGrandTotalComplete);
			/** @var float $response->orderGrandTotal */
			$t->set("orderGrandTotal", $response->orderGrandTotal);
			$t->set("isOrderConfirmed", $isOrderConfirmed);
			// --------
			// --------------

			// ADD DOWNLOADS IF ORDER IS FULLY PAID
			if ((int) $orderPaymentStatus === PwCommerce::PAYMENT_STATUS_PAID) {
				// ORDER DOWNLOADS
				/** @var array $downloads */
				$downloads = $this->getDownloadCodesByOrderID($order->id);
				if (!empty($downloads)) {
					$t->set("downloads", $downloads);
				}
			}
		} else {
			#############################
			# SESSION ORDER IS LOST!
			# LIMIT RESPONSE!!!
			##############################

			$t->set("isLostOrderSession", $this->isOrderSessionLost());
			$t->set("orderID", $response->order->id);
		}


		$out = $t->render();

		// --------------------
		// IF USING CUSTOM FORM, WE RETURN WIREDATA as response
		if (!empty($this->isCustomForm)) {
			$response = new WireData();
			$response->success = true;
			$response->isProcessedForm = true;
			// TODO: rephrase?
			$response->message = $this->_("Order is complete.");
			$response->content = $out;
			/** @var WireData $out */
			$out = $response;
		}

		// REMOVE TEMPORARY LOST ORDER SESSION VARIABLE
		$this->session->remove(PwCommerce::ORDER_LOST_SESSION_ORDER_ID_NAME);

		// --------------
		return $out;
	}

	/**
	 * Render Cancel.
	 *
	 * @return string|mixed
	 */
	private function renderCancel() {

		$out = '';
		// ====================
		$t = $this->getPWCommerceTemplate("order-cancelled.php");
		$out .= $t->render();

		// IF USING CUSTOM FORM, WE RETURN WIREDATA as response
		if (!empty($this->isCustomForm)) {
			/** @var WireData $out */
			$out .= $this->renderConfirmation()->content;
			$response = new WireData();
			$response->success = true;
			$response->isProcessedForm = true;
			$response->message = $this->_("Payment was cancelled.");
			$response->content = $out;
			/** @var WireData $out */
			$out = $response;
		} else {
			$out .= $this->renderConfirmation();
		}

		// --------------
		return $out;
	}

	/**
	 * Is Invoice Order.
	 *
	 * @return bool
	 */
	private function isInvoiceOrder() {
		return $this->session->isInvoiceOrder;
	}


	/**
	 * Is Need To Select From Multiple Matched Shipping Rates.
	 *
	 * @return bool
	 */
	private function isNeedToSelectFromMultipleMatchedShippingRates() {


		$isFreeShippingDiscountAppliedToOrder = $this->isFreeShippingDiscountAppliedToOrder();

		// here we check two things: (i) if session->isMatchedMultipleShippingRates IS TRUE AND (ii) if session->isMatchedMultipleShippingRates is empty
		// this is because via ajax, a selected shipping rate could have been selected and later processed in TraitPWCommerceShipping::setOrderPagePWCommerceOrderShippingValues
		$isNeedToSelectFromMultipleMatchedShippingRates = false;
		// if ($this->session->isMatchedMultipleShippingRates && empty($this->session->validPreselectedMatchedShippingRateID)) {
		// TODO ALSO CHECK IF NO FREE SHIPPING DISCOUNT APPLIED TO ORDER!
		// if ($this->session->isMatchedMultipleShippingRates) {
		if ($this->session->isMatchedMultipleShippingRates && empty($isFreeShippingDiscountAppliedToOrder)) {
			$isNeedToSelectFromMultipleMatchedShippingRates = true;
		}
		return $isNeedToSelectFromMultipleMatchedShippingRates;
	}

	/**
	 * Get Order Customer Form.
	 *
	 * @return mixed
	 */
	private function getOrderCustomerForm() {
		// NOTE: IN 'traits\order\TraitPWCommerceCustomerForm.php'
		$form = $this->getCustomerForm();

		$submit = $this->modules->get("InputfieldSubmit");
		$submit->skipLabel = Inputfield::skipLabelBlank;
		$submit->attr("id+name", "customerForm");
		if ($this->btnClass)
			$submit->attr('class', $this->btnClass);
		$submit->value = $this->_("Proceed to confirmation");
		$form->add($submit);

		return $form;
	}

	/**
	 * Set Order Handling Fee Values.
	 *
	 * @param WireData $order
	 * @return mixed
	 */
	private function setOrderHandlingFeeValues(WireData $order) {
		$orderHandlingFeeValues = new WireData();
		$handlingFeeProperties = ['handlingFeeType', 'handlingFeeValue', 'handlingFee'];
		// --------
		foreach ($handlingFeeProperties as $handlingFeeProperty) {
			$orderHandlingFeeValues->set($handlingFeeProperty, $order->$handlingFeeProperty);
		}
		// -----
		return $orderHandlingFeeValues;
	}

	// process a custom form

	/**
	 * Init Process Custom Order Customer Form.
	 *
	 * @return mixed
	 */
	private function initProcessCustomOrderCustomerForm() {

		// @note: in this case, custom form dev has already checked $this->input->post->customerForm
		// or any other POST they are listening to. So, we can proceed TODO?

		// PROCESS CUSTOM ORDER CUSTOMER FORM
		// NOTE: IN 'traits\order\TraitPWCommerceProcessOrderForm.php'
		/** @var WireData $orderCreationResponse */
		$orderCreationResponse = $this->processCustomOrderCustomerForm($this->customFormFields, $this->isUseCustomFormInputNames);

		//----------
		if (!empty($orderCreationResponse->success)) {
			// ORDER CREATED SUCCESSFULLY FROM CUSTOM ORDER FORM
			// redirect to confirmation and payment
			$redirectURL = "./{$this->confirmationUrlSegment}/";
			return $this->session->redirect($redirectURL);
		} else {
			// ERRORS FOUND
			/** @var Array $errors */
			// $errors = $orderCreationResponse->errors;
			return $orderCreationResponse;
		}
	}

	/**
	 * Check Order I D Session.
	 *
	 * @return mixed
	 */
	private function checkOrderIDSession() {
		// IF NO SESSION; REDIRECT
		// but only if 'session is not lost!'
		$orderID = $this->session->get(PwCommerce::ORDER_LOST_SESSION_ORDER_ID_NAME);

		if (!$this->session->orderId && empty($orderID)) {
			// redirect home
			$this->session->redirect("/");
		}
	}

	/**
	 * Is Order Session Lost.
	 *
	 * @return bool
	 */
	protected function isOrderSessionLost() {
		$orderID = $this->session->get(PwCommerce::ORDER_LOST_SESSION_ORDER_ID_NAME);
		$isSessionLost = !$this->session->orderId && !empty($orderID);
		return $isSessionLost;
	}

}
