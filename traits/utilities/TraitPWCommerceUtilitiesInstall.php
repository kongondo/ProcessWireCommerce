<?php

namespace ProcessWire;



trait TraitPWCommerceUtilitiesInstall
{



	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INSTALL ~~~~~~~~~~~~~~~~~~

	public function getCustomShopRootPageAllowedChildrenDetails() {
		$customShopRootPageAllowedChildrenDetails = [
			// -----------
			'templates' => [
				'products' => 'pwcommerce-products',
				'product_brands' => 'pwcommerce-brands',
				'product_categories' => 'pwcommerce-categories',
				'product_tags' => 'pwcommerce-tags',
				'product_types' => 'pwcommerce-types',
				// TODO ENABLE THIS?
				// 'customers' => 'pwcommerce-customers',
				'legal_pages' => 'pwcommerce-legal-pages',
			]
		];
		// ------
		return $customShopRootPageAllowedChildrenDetails;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INSTALL/UNINSTALL ~~~~~~~~~~~~~~~~~~

	## PWCOMMERCE CONFIGS AND INSTALL ##

	public function getPWCommerceModuleConfigs($configModuleName) {
		return $this->wire('modules')->getConfig($configModuleName);
	}

	private function getPWCommerceModuleConfigsRaw($configModuleName) {
		$pwcommerceModuleConfigs = [];

		$queryOptions = [
			'table' => 'modules',
			'select_columns' => ['class', 'data'],
			'is_not_use_prefix' => true,
			// WHERE
			'conditions' => [
				// global_usage >= limit_total
				// [
				// 	'column_name' => "global_usage",
				// 	'operator' => '>=',
				// 	'column_value' => 'field_pwcommerce_discount.limit_total',
				// 	'column_type' => 'string',
				// 	// i.e. parameter name of the form :name
				// 	// NOTE: excluding since no ambiguity. TraitPWCommerceDatabase::getGroupByQuery will default to 'column_name'
				// 	// 'param_identifier' => 'global_usage',
				// 	'skip_bind' => true,
				// ],
				// limit_total > 0
				[
					'column_name' => "class",
					'operator' => '=',
					'column_value' => $configModuleName,
					'column_type' => 'str',
					// i.e. parameter name of the form :name
					// NOTE: excluding since no ambiguity. TraitPWCommerceDatabase::getGroupByQuery will default to 'column_name'
					// 'param_identifier' => 'limit_total',
				],
			],
		];

		$results = $this->processQuerySelect($queryOptions);
		if (!empty($results)) {
			// we expect only one result
			$pwcommerceModuleConfigsJSON = $results[0]['data'];
			$pwcommerceModuleConfigs = json_decode($pwcommerceModuleConfigsJSON, true);

		}
		return $pwcommerceModuleConfigs;

	}

	public function getPWCommerceInstalledOptionalFeatures($configModuleName, $isUseRawSQL = false) {
		$installedOptionalFeatures = [];
		if (!empty($isUseRawSQL)) {
			// for use in TraitPWCommerceProcessNavigation::postProcessNavItemsForDropdown
			// in order to avoid recursion
			$pwcommerceModuleConfigs = $this->getPWCommerceModuleConfigsRaw($configModuleName);
		} else {
			$pwcommerceModuleConfigs = $this->getPWCommerceModuleConfigs($configModuleName);
		}
		# +++++++++++
		if (!empty($pwcommerceModuleConfigs['pwcommerce_installed_optional_features'])) {
			$installedOptionalFeatures = $pwcommerceModuleConfigs['pwcommerce_installed_optional_features'];
		}
		// -------
		return $installedOptionalFeatures;
	}

	public function getPWCommerceInstalledOtherOptionalSettings($configModuleName) {
		$pwcommerceModuleConfigs = $this->getPWCommerceModuleConfigs($configModuleName);
		$installedOtherOptionalSettings = [];
		if (!empty($pwcommerceModuleConfigs['pwcommerce_other_optional_settings'])) {
			$installedOtherOptionalSettings = $pwcommerceModuleConfigs['pwcommerce_other_optional_settings'];
		}
		// -------
		return $installedOtherOptionalSettings;
	}

	public function setPWCommerceModuleConfigs($data, $configModuleName) {
		$this->wire('modules')->saveConfig($configModuleName, $data);
	}

	public function getPWCommerceOptionalFeatures() {
		// TODO ADD TO LIST IF NEEDED!
		return [
			'product_inventory' => 'inventory',
			'product_categories' => 'categories',
			'product_tags' => 'tags',
			'product_attributes' => 'attributes',
			'product_types' => 'types',
			'product_brands' => 'brands',
			'product_properties' => 'properties',
			'product_dimensions' => 'dimensions',
			'gift_cards' => 'gift-cards',
			'downloads' => 'downloads',
			'discounts' => 'discounts',
			'customers' => 'customers',
			'customer_groups' => 'customer-groups',
			'payment_providers' => 'payment-providers',
			'legal_pages' => 'legal-pages',
		];
	}

	public function isExistPWCommerceCustomTable($customTableName) {
		return $this->wire('database')->tableExists($customTableName);
	}

}