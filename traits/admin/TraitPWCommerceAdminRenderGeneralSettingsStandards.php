<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsStandards
{
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ STANDARDS TAB  ~~~~~~~~~~~~~~~~~~
	private function getStandardsTab() {
		//--------------
		// for weights and measures system
		$weightsAndMeasuresSystemRadioOptions = [
			'metric' => $this->_('Metric'),
			'imperial' => $this->_('Imperial'),
		];

		//------------------
		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_general_settings_standards_tab',
				'title' => $this->_('Standards'),
			],
			// TODO: DURING SAVE, NEED TO ALSO STORE SHOP CURRENCY CODE! AS PER THE SAVED SHOP COUNTRY! SO, NEED TO CONFIRM SHOP COUNTRY TYPED HAS NO TYPOS? OR???
			// TODO - NEED TO SAVE THIS! alphabetic_code
			// TODO: THINK ABOUT ADDING OVERRIDE FOR THOUSANDS SEPARATOR AND DECIMAL POINT FOR CURRENCIES; CURRENTLY, THIS IS ENFORCED BY THE locale_codes SETTING. e.g. de-DE will enforce german currency and decimals formatting
			// TODO - RELATED TO ABOVE, CONSIDER ADDING SETTING FOR DISPLAY SYMBOL, OR CURRENCY NAME, ETC. for now, we just use fallback
			'inputfields' => [
				// shop currency
				'shop_currency' => [
					'type' => 'tags',
					'name' => 'pwcommerce_general_settings_shop_currency',
					'label' => $this->_('Shop Currency'),
					'columnWidth' => 50,
					'maxItems' => 1,
					// TODO - SET TAGS LIST HERE ISSUE! THIS IS BECAUSE OUR SAVED VALUE AND ACTUAL VALUES ARE DIFFERENT!
					'set_tags_list' => $this->getCurrencies(),
					// @note: InputfieldTextTags will not allow a tag if  this condition is not met:
					// !$allowUserTags && ($isPageField || !$this->tagsUrl)
					// so we add this fake-url but don't useAjax to allow this to get through
					'tagsUrl' => 'fake-url',
					'value' => $this->getGeneralSettingValue('shop_currency'),
					'required' => true,
				],

				// currency format
				'shop_currency_format' => [
					'type' => 'tags',
					'name' => 'pwcommerce_general_settings_shop_currency_format',
					'label' => $this->_('Currency Format'),
					// TODO: rephrase?
					'notes' => $this->_("This setting will be used to display currencies formatted according to selected locale."),
					'columnWidth' => 50,
					'maxItems' => 1,
					// TODO - SET TAGS LIST HERE ISSUE! THIS IS BECAUSE OUR SAVED VALUE AND ACTUAL VALUES ARE DIFFERENT!
					'set_tags_list' => $this->getCurrenciesFormats(),
					// @note: InputfieldTextTags will not allow a tag if  this condition is not met:
					// !$allowUserTags && ($isPageField || !$this->tagsUrl)
					// so we add this fake-url but don't useAjax to allow this to get through
					'tagsUrl' => 'fake-url',
					'value' => $this->getGeneralSettingValue('shop_currency_format'),
					// 'required' => true, // TODO: REQUIRED?
				],

				// date format
				'date_format' => [
					'type' => 'select',
					'name' => 'pwcommerce_general_settings_date_format',
					'label' => $this->_('Date Format'),
					'columnWidth' => 50,
					'select_options' => $this->getDateFormats(),
					'value' => $this->getGeneralSettingValue('date_format'),
				],

				// time format
				'time_format' => [
					'type' => 'select',
					'name' => 'pwcommerce_general_settings_time_format',
					'label' => $this->_('Time Format'),
					'columnWidth' => 50,
					'select_options' => $this->getTimeFormats(),
					'value' => $this->getGeneralSettingValue('time_format'),
				],

				// timezone
				'timezone' => [
					'type' => 'tags',
					'name' => 'pwcommerce_general_settings_timezone',
					'label' => $this->_('Timezone'),
					'columnWidth' => 50,
					'maxItems' => 1,
					'set_tags_list' => $this->getTimeZoneIdentifiers(),
					'value' => $this->getGeneralSettingValue('timezone'),
				],

				// weights and measures system
				'weights_and_measures_system' => [
					'type' => 'radio',
					'name' => 'pwcommerce_general_settings_weights_and_measures_system',
					'label' => $this->_('Weights and Measures'),
					'columnWidth' => 50,
					'radio_options' => $weightsAndMeasuresSystemRadioOptions,
					'value' => $this->getGeneralSettingValue('weights_and_measures_system'),

				],

			],

		];

		return $tabAndContents;
	}

}
