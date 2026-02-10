<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsProducts
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PRODUCTS TAB  ~~~~~~~~~~~~~~~~~~

	/**
	 * Get Products Tab.
	 *
	 * @return mixed
	 */
	private function getProductsTab() {


		//---------------
		// for default product properties and product weight property

		$customHookURL = "/find-pwcommerce_general_settings_product_properties/";
		$tagsURL = "{$customHookURL}?q={q}";
		$savedDefaultProductProperties = $this->getSavedDefaultProductProperties('default_product_properties');
		$savedProductWeightProperty = $this->getSavedProductWeightProperty('product_weight_property');
		$propertiesURL = $this->adminURL . 'properties';
		$defaultProductPropertiesNotes =
			sprintf($this->_('These [properties](%s) must have been created in advance.'), $propertiesURL);
		$weightProductPropertyNotes =
			sprintf($this->_("This [property](%s) must have been created in advance. For instance, a property called 'Weight'. You will also need to add the property to the products that require it. Products weight values will have to be specified in Kilograms."), $propertiesURL);
		//--------------
		// quick filters thresholds
		$lowStockThresholdValue = (int) $this->getGeneralSettingValue('product_quick_filters_low_stock_threshold');
		if (empty($lowStockThresholdValue)) {
			// use default value: 5
			$lowStockThresholdValue = 5;
		}

		//--------------
		// for radio price fields type
		$priceFieldsRadioOptions = [
			'price_and_compare_price_fields' => $this->_("Use 'price and compare price' fields"),
			'sale_and_normal_price_fields' => $this->_("Use 'sale and normal price' fields"),
		];

		$priceFieldsTypeValue = $this->wire('sanitizer')->fieldName($this->getGeneralSettingValue('product_price_fields_type'));
		if (empty($priceFieldsTypeValue)) {
			// use default value: PRICE & COMPARE FIELDS
			$priceFieldsTypeValue = 'price_and_compare_price_fields';
		}

		// wrap attrs
		// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change
		$priceFieldsTypeWrapAttrs = [
			['dataset' => 'data-general-settings-radio-change-type', 'value' => 'general_settings_product_price_fields_type'],
		];

		# notes (Alpine JS powered!)
		// note the X-INIT VALUE FOR ON LOAD/SAVED VALUE!
		$priceFieldsTypeNotes =
			// wrapper
			"<span x-data='ProcessPWCommerceData' x-init='initGeneralSettingsGUI(`general_settings_product_price_fields_type`,`{$priceFieldsTypeValue}`)' @pwcommercegeneralsettingsradiochangenotification.window='handleGeneralSettingsRadioChange'>" .
			// price_and_compare_price_fields
			"<span x-show='{$this->xstore}.general_settings_product_price_fields_type==`price_and_compare_price_fields`'>" .
			$this->_("Products will have a 'price' field and a 'compare price' field. The price field is what the customer will be shown. It is the unit price of the product. The compare price field can be used to show the customer the previous price of the product. It plays no part in the order transaction. Hence, the price field should always contain a non-zero value! Use this approach if you mainly want to show 'ex' prices.") .
			"</span>" .
			// sale_and_normal_price_fields
			"<span x-show='{$this->xstore}.general_settings_product_price_fields_type==`sale_and_normal_price_fields`'>" .
			$this->_("Products will have a 'sale price' field and a 'normal price' field. If the sale price field contains a value greater than zero, the sale price will be shown to the customer. It is the unit price of the product. If the sale price field is empty or has a value of zero, the normal price is what the customer will see and will have to pay for the product. Use this approach if you periodically run 'sales' but want to easily switch back to the 'normal' price if needed.") .
			"</span>" .
			// close wrapper
			"</span>";

		//------------------
		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_general_settings_products_tab',
				'title' => $this->_('Products'),
			],

			'inputfields' => [

				// default product properties
				'default_product_properties' => [
					'type' => 'tags',
					'name' => 'pwcommerce_general_settings_default_product_properties',
					'label' => $this->_('Default Product Properties'),
					'placeholder' => $this->_('Type at least 3 characters to search for properties'),
					'description' => $this->_('These properties will be automatically added to every new product you create. You will still be able to remove or add these or other properties when editing a product.'),
					'notes' => $defaultProductPropertiesNotes,
					'useAjax' => true,
					'closeAfterSelect' => false,
					'tagsUrl' => $tagsURL,
					// @note: doesn't seem to work after POST-ing; still space separated
					//  'delimiter ' => 'c',
					'columnWidth' => 50,
					// @note: special strategy for InputfieldTextTags that has saved 'pages'
					'set_tags_list' => $savedDefaultProductProperties['set_tags_list'],
					'value' => $savedDefaultProductProperties['value'],
				],

				// product weight property
				'product_weight_property' => [
					'type' => 'tags',
					'name' => 'pwcommerce_general_settings_product_weight_property',
					'label' => $this->_('Product Weight Property'),
					'placeholder' => $this->_('Type at least 3 characters to search for property'),
					'description' => $this->_('Specify the property to be used for storing the weight of a product. This will be required if you intend to use weight-based shipping rates.'),
					// TODO: IN FUTURE OFFER CHOICE OF OTHER WEIGHT MEASUREMENTS, E.G. OUNCES, POUNDS, GRAMS, ETC!
					'notes' => $weightProductPropertyNotes,
					'useAjax' => true,
					'closeAfterSelect' => true,
					'tagsUrl' => $tagsURL,
					// @note: doesn't seem to work after POST-ing; still space separated
					//  'delimiter ' => 'c',
					'columnWidth' => 50,
					'maxItems' => 1,
					// @note: special strategy for InputfieldTextTags that has saved 'pages'
					'set_tags_list' => $savedProductWeightProperty['set_tags_list'],
					'value' => $savedProductWeightProperty['value'],
				],
				// low_stock_threshold (quantity)
				'quick_filters_low_stock_threshold' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_product_quick_filters_low_stock_threshold',
					'min' => 0,
					'step' => 1,
					'label' => $this->_('Low Stock Threshold'),
					'description' => $this->_("Enter the quantity value below which to consider a product as 'low on stock'."),
					'notes' => $this->_("For use by quick filters and highlighting low stock in the products dashboard. The default is 5 items."),
					'columnWidth' => 50,
					'value' => $lowStockThresholdValue,
				],
				// price fields type
				'product_price_fields_type' => [
					'type' => 'radio',
					'name' => 'pwcommerce_general_settings_product_price_fields_type',
					'label' => $this->_('Price Fields'),
					'description' => $this->_('Indicate the type of price fields to use for products.'),
					'radio_options' => $priceFieldsRadioOptions,
					'columnWidth' => 50,
					'value' => $priceFieldsTypeValue,
					// ALPINE POWERED NOTES
					'notes' => $priceFieldsTypeNotes,
					// -------
					'wrapAttr' => $priceFieldsTypeWrapAttrs,
					'entityEncodeText' => false,
				],

			],

		];

		# CHECK IF PRODUCT PROPERTIES FEATURE INSTALLED
		if (empty($this->pwcommerce->isOptionalFeatureInstalled('product_properties'))) {
			# 'product properties' not installed
			# unset 'product properties' settings (in standards tab)

			unset($tabAndContents['inputfields']['default_product_properties'], $tabAndContents['inputfields']['product_weight_property']);
		}

		return $tabAndContents;
	}

	// @note: setting saved values to InputfieldTextTags is a two step process, it seems?
	// we handle it here
	/**
	 * Get Saved Default Product Properties.
	 *
	 * @param mixed $setting
	 * @return mixed
	 */
	private function getSavedDefaultProductProperties($setting) {
		return $this->getSavedProductProperties($setting);
	}

	/**
	 * Get Saved Product Weight Property.
	 *
	 * @param mixed $setting
	 * @return mixed
	 */
	private function getSavedProductWeightProperty($setting) {
		return $this->getSavedProductProperties($setting);
	}

	/**
	 * Generic method for finding and setting product properties IDs and titles.
	 *
	 * @param mixed $setting
	 * @return mixed
	 */
	private function getSavedProductProperties($setting) {
		$value = null;
		$setTagsList = [];
		if (!empty($this->generalSettings[$setting])) {
			$savedValue = $this->generalSettings[$setting];
			if ($setting === 'default_product_properties') {
				// @note: saved as array already if for 'default_product_properties' this is an array
				$pageIDs = implode("|", $savedValue);
			} else {
				// @note: if for 'product_weight_property' this is a single value
				$pageIDs = $savedValue;
			}
			// ==============
			$selector = "id={$pageIDs},include=hidden";
			$pages = $this->wire('pages')->findRaw($selector, 'title');

			// =============
			if (!empty($pages)) {
				// @note: $pages will be in the format $page->id => $page->title
				$value = array_keys($pages);
				$setTagsList = $pages;
			}
		}
		// set_tags_list
		$valuesForInputfieldTextTags = [
			'value' => $value,
			// @note: for $field->val(array),
			'set_tags_list' => $setTagsList, // @note: for $field->setTagsList(array)
		];

		return $valuesForInputfieldTextTags;
	}

}
