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

	protected function getPWCommerceClassByName($className, $options = []) {
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

	protected function loadPWCommerceClassByName($className) {
		$classPath = $this->getPWCommerceClassPath($className);

		if (!empty($classPath)) {
			// REQUIRE THE CLASS FILE
			$this->requireOncePWCommerceFile($classPath);
		}
	}

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

	private function requireOncePWCommerceFile($filePath) {
		require_once __DIR__ . $filePath;
	}

}
