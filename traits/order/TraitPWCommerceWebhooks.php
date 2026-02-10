<?php

namespace ProcessWire;

trait TraitPWCommerceWebhooks {

	private $isNonCorePaymentProvider = false;
	private $paymentProvider;

	/**
	 * Handle P W Commerce Webhook.
	 *
	 * @param HookEvent $event
	 * @return mixed
	 */
	protected function handlePWCommerceWebhook(HookEvent $event) {

		// Get an instance of WireHttp
		$http = new WireHttp();
		$httpResponseStatus = 400;

		// NAMED ARGUMENT (PROVIDER)
		// @NOTE: E.G. 'https://myshop.com/pwcommerce/webhook-stripe/'

		// retrieve as argument
		$providerName = $event->arguments('provider');

		// CLEAN AND VALIDATE

		$isCleanProviderName = $this->cleanAndValidateWebhookProvider($providerName);

		if (empty($isCleanProviderName)) {
			// 400 + exit()
			$http->sendStatusHeader($httpResponseStatus);
			exit();
		}

		// ATTEMPT TO GET PAYMENT PROVIDER
		// @note: must exist + activated
		$paymentProvider = $this->getPaymentProviderForWebhook($providerName);

		if ($paymentProvider instanceof NullPage) {
			// 400 + exit()

			$http->sendStatusHeader($httpResponseStatus);
			exit();
		}

		// ++++++++++++++
		# ***** GOOD TO GO *****
		$this->paymentProvider = $paymentProvider;

		// GET PAYMENT PROVIDER CLASS AND DELEGATE HANDLING OF WEBHOOK TO IT
		$paymentClass = $this->getPaymentProviderClassForWebhook();

		if (empty($paymentClass)) {
			// PAYMENT CLASS NOT FOUND FOR SOME REASON
			// 400 + exit()

			$http->sendStatusHeader($httpResponseStatus);
			exit();
		}

		# CHECK IF PAYMENT CLASS HAS WEBHOOK HANDLER
		// @NOTE can be hookable or not; we check both
		$isClassHaveWebhookHandler = false;
		// TODO IF NOT IN USE
		// $isClassWebhookHandlerHookable = false;

		if (method_exists($paymentClass, '___handleWebhook')) {
			// has a hookable webhook handler
			$isClassHaveWebhookHandler = true;
			$handleWebhookMethod = '___handleWebhook';
		} else if (method_exists($paymentClass, 'handleWebhook')) {
			// has a non-hookable webhook handler
			$isClassHaveWebhookHandler = true;
			$handleWebhookMethod = 'handleWebhook';
		}

		// DO WE HAVE A WEBHOOK HANDLER?
		if (empty($isClassHaveWebhookHandler)) {
			// PAYMENT CLASS DOES NOT IMPLEMENT A WEBHOOK HANDLER METHOD FOR SOME REASON
			// 400 + exit()
			$http->sendStatusHeader($httpResponseStatus);
			exit();
		}

		# GET HTTP RESPONSE FOR WEBHOOK FROM HANDLER
		$payload = $this->wire('files')->fileGetContents(filename: "php://input");
		// $httpResponseStatus = $paymentClass->handleWebhook($payload);
		$httpResponseStatus = $paymentClass->$handleWebhookMethod($payload);

		// SEND STATUS BACK TO WEBHOOK CALLER
		$http->sendStatusHeader($httpResponseStatus);
		exit();
	}

	/**
	 * Clean And Validate Webhook Provider.
	 *
	 * @param mixed $uncleanProviderName
	 * @return bool
	 */
	private function cleanAndValidateWebhookProvider($uncleanProviderName): bool {
		$isValidWebhook = true;
		$cleanProviderName = $this->wire('sanitizer')->pageName($uncleanProviderName, true);

		if ($cleanProviderName !== $uncleanProviderName) {

			$isValidWebhook = false;
		}
		if ($cleanProviderName === 'invoice') {

			$isValidWebhook = false;
		}

		// ---------
		return $isValidWebhook;
	}

	/**
	 * Get Payment Provider For Webhook.
	 *
	 * @param mixed $providerName
	 * @return Page
	 */
	private function getPaymentProviderForWebhook($providerName): Page|NullPage {
		// existing and active (published) payment provider
		$paymentProvider = $this->pwcommerce->get("name={$providerName},status<" . Page::statusUnpublished);

		return $paymentProvider;
	}

	/**
	 * Get Payment Provider Class For Webhook.
	 *
	 * @return mixed
	 */
	private function getPaymentProviderClassForWebhook() {

		$paymentClass = NULL;

		// --------------------
		$paymentClassName = $this->getPaymentProviderClassNameForWebhook();

		// -----------------
		// @note: this includes trailing slash!
		/** @var string $paymentClassFilePath */
		$paymentClassFilePath = $this->getPaymentProviderClassForWebhookFilePath();

		if (!is_file("{$paymentClassFilePath}{$paymentClassName}/{$paymentClassName}.php")) {
			// TODO ERROR HERE! OR SET TO CLASS PROPERTY!

			return $paymentClass;
		}

		require_once("{$paymentClassFilePath}{$paymentClassName}/{$paymentClassName}.php");
		$class = "\ProcessWire\\" . $paymentClassName;

		$paymentClassConfigsJSON = $this->paymentProvider->get('pwcommerce_settings');
		$paymentClassConfigs = json_decode($paymentClassConfigsJSON, true);

		$paymentClass = new $class($paymentClassConfigs);

		// ---------
		return $paymentClass;

	}

	/**
	 * Get Payment Provider Class Name For Webhook.
	 *
	 * @return string
	 */
	private function getPaymentProviderClassNameForWebhook(): string {
		$nonCorePaymentProvidersIDs = $this->pwcommerce->getNonCorePaymentProvidersIDs();
		$paymentProviderID = (int) $this->paymentProvider->id;

		if (in_array($paymentProviderID, $nonCorePaymentProvidersIDs)) {
			// NON-CORE PAYMENT PROVIDER
			$paymentClassName = $this->pwcommerce->getNonCorePaymentProviderClassNameByID($paymentProviderID);
			// track that we are dealing with a non-core payment provider
			$this->isNonCorePaymentProvider = true;

		} else {
			// CORE PAYMENT PROVIDER
			$paymentClassName = "PWCommercePayment{$this->paymentProvider->title}";

		}
		// ---
		return $paymentClassName;
	}

	/**
	 * Returns path to Payment Class file, checking if core vs non-core payment addon.
	 *
	 * @return string
	 */
	private function getPaymentProviderClassForWebhookFilePath(): string {
		// TODO - REFACTOR SINCE SIMILAR TO TraitPWCommercePayment::getPaymentClassFilePath!
		if (!empty($this->isNonCorePaymentProvider)) {
			$path = $this->wire('config')->paths->templates . "pwcommerce/addons/";

		} else {
			$path = __DIR__ . "/../../includes/payment/";

		}

		// --------
		return $path;
	}

}
