<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Loader: Trait class to load PWCommerce Classes on demand.
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

trait TraitPWCommerceLoader {

	# ~~~~~~~~ CLASSES  ~~~~~~~~

	/**
	 * Get P W Commerce Class By Name.
	 *
	 * @param mixed $className
	 * @param array $options
	 * @return mixed
	 */
	protected function getPWCommerceClassByName($className, array $options = []) {
		$classPath = $this->getPWCommerceClassPath($className);

		if (!empty($classPath)) {
			// REQUIRE THE CLASS FILE
			$this->requireOncePWCommerceFile($classPath);
			// return new $className();
			// $options = [];
			// $pwcommerceClass = new PWCommerceActions($options);
			// $className = "ProcessWire\PWCommerceActions";
			$className = "ProcessWire\\$className";
			if (!empty($options)) {

				$pwcommerceClass = new $className($options);
			} else {

				$pwcommerceClass = new $className;
			}

			return $pwcommerceClass;
		}

	}

	/**
	 * Load P W Commerce Class By Name.
	 *
	 * @param mixed $className
	 * @return mixed
	 */
	protected function loadPWCommerceClassByName($className) {
		$classPath = $this->getPWCommerceClassPath($className);

		if (!empty($classPath)) {
			// REQUIRE THE CLASS FILE
			$this->requireOncePWCommerceFile($classPath);
		}
	}

	/**
	 * Get All P W Commerce Required Classes Paths.
	 *
	 * @return mixed
	 */
	private function getAllPWCommerceRequiredClassesPaths() {
		$paths = [
			// addons
			'addons' => [
				'PWCommerceAddons',
			],
			// api
			'api' => [
				'PWCommerceImport',
			],
			// crud
			'crud' => [
				'PWCommerceActions',
			],
			// geopolitical
			'geopolitical' => [
				'PWCommerceContinents',
				'PWCommerceCountries',
				'PWCommerceCurrencies',
				'PWCommerceLocales',
				'PWCommerceTerritories',
				'PWCommerceWeightsAndMeasures',
			],
			// helper
			'helper' => [
				// TODO DELETE IF NOT IN USE
				'PWCommerceCustomers',
				'PWCommerceGiftCards',
			],
			// inputfields
			'inputfield/InputfieldPWCommerceAttributeOptions' => [
				'InputfieldPWCommerceAttributeOptions',
			],
			'inputfield/InputfieldPWCommerceGiftCardProductVariants' => [
				'InputfieldPWCommerceGiftCardProductVariants',
			],
			// install
			'install' => [
				'PWCommerceInstaller',
			],
			// payments
			'payment/PWCommercePayment' => [
				'PWCommercePayment',
			],
			'payment/PWCommercePaymentInvoice' => [
				'PWCommercePaymentInvoice',
			],
			'payment/PWCommercePaymentPayPal' => [
				'PWCommercePaymentPayPal',
			],
			'payment/PWCommercePaymentStripe' => [
				'PWCommercePaymentStripe',
			],
			// render
			'render' => [
				'PWCommerceAdminRenderAddons',
				'PWCommerceAdminRenderAttributes',
				'PWCommerceAdminRenderBrands',
				'PWCommerceAdminRenderCategories',
				'PWCommerceAdminRenderCheckoutSettings',
				'PWCommerceAdminRenderCustomerGroups',
				'PWCommerceAdminRenderCustomers',
				'PWCommerceAdminRenderDimensions',
				'PWCommerceAdminRenderDiscounts',
				'PWCommerceAdminRenderDownloads',
				'PWCommerceAdminRenderGeneralSettings',
				'PWCommerceAdminRenderGiftCardProducts',
				'PWCommerceAdminRenderGiftCards',
				'PWCommerceAdminRenderInstaller',
				'PWCommerceAdminRenderInventory',
				'PWCommerceAdminRenderLegalPages',
				'PWCommerceAdminRenderOrders',
				'PWCommerceAdminRenderPaymentProviders',
				'PWCommerceAdminRenderProducts',
				'PWCommerceAdminRenderProperties',
				'PWCommerceAdminRenderReports',
				'PWCommerceAdminRenderShipping',
				'PWCommerceAdminRenderShopHome',
				'PWCommerceAdminRenderTags',
				'PWCommerceAdminRenderTaxRates',
				'PWCommerceAdminRenderTaxSettings',
				'PWCommerceAdminRenderTypes',
			],
		];
		// ------
		return $paths;
	}

	/**
	 * Get P W Commerce Class Path.
	 *
	 * @param mixed $className
	 * @return mixed
	 */
	private function getPWCommerceClassPath($className) {
		$allClassesNames = $this->getAllPWCommerceRequiredClassesPaths();
		$includePath = NULL;
		$includeClassType = NULL;
		$includeClassName = NULL;
		$isFoundClassName = false;

		foreach ($allClassesNames as $type => $classes) {
			foreach ($classes as $class) {
				if ($class === $className) {
					$includeClassType = $type;
					$includeClassName = $className;
					$isFoundClassName = true;
					break;
				}
			}
			// ----
			// breeak outerloop if class name found
			if (!empty($isFoundClassName)) {
				break;
			}
		}

		// +++++++++

		if (!empty($includeClassType)) {
			$includePath = '/../../includes/';
			$includePath .= "{$includeClassType}/{$includeClassName}.php";
		}

		return $includePath;
	}

	# ~~~~~~~~

	/**
	 * Require Once P W Commerce File.
	 *
	 * @param mixed $filePath
	 * @return mixed
	 */
	private function requireOncePWCommerceFile($filePath) {
		require_once __DIR__ . $filePath;
	}

}
