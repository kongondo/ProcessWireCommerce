<?php

namespace ProcessWire;

trait TraitPWCommerceActionsSettings
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ SETTINGS ~~~~~~~~~~~~~~~~~~

	private function actionTaxSettings() {

		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!

		//------------------
		// GOOD TO GO

		// get the tax settings page
		$page = $this->wire('pages')->get("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",name=taxes");

		// we didn't get the page; abort
		// TODO: meaningful error? e.g. tax settings page not found?
		if (empty($page->id)) {
			return null;
		}

		// process the settings
		$input = $this->actionInput;

		$taxSettings = [
			'prices_include_taxes' => (int) $input->pwcommerce_tax_settings_prices_include_taxes ? true : false,
			'shop_country_tax_rate' => (float) $input->pwcommerce_tax_settings_shop_country_tax_rate,
			'charge_taxes_on_shipping_rates' => (int) $input->pwcommerce_tax_settings_charge_taxes_on_shipping_rates ? true : false,
			'charge_eu_digital_goods_vat_taxes' => (int) $input->pwcommerce_tax_settings_charge_eu_digital_goods_vat_taxes ? true : false,
		];

		// if prices include taxes is checked, a shop country tax rate MUST be specified
		if (!empty($taxSettings['prices_include_taxes']) && empty($taxSettings['shop_country_tax_rate'])) {
			$this->error($this->_('Shop country tax rate needs to be entered!'));
			// abort save
			return null;
		}

		// TODO: SHOULD WE MAKE 'shop_country_tax_rate' 0 if include taxes not in use?

		// prepare the JSON string to save as tax settings
		$taxSettingsJSON = json_encode($taxSettings);
		// assign to settings field
		$page->pwcommerce_settings = $taxSettingsJSON;
		//-------------
		// save the page's 'pwcommerce_settings' field
		$page->save('pwcommerce_settings');

		// --------------------
		// prepare messages
		$notice = $this->_('Saved tax settings.');

		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO? check if really saved first?
		];

		//-------
		return $result;
	}

	private function actionGeneralSettings() {

		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!

		//------------------
		// good to go

		// get the general settings page
		$page = $this->wire('pages')->get("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",name=general");

		// we didn't get the page; abort
		// TODO: meaningful error? e.g. general settings page not found?
		if (empty($page->id)) {
			return null;
		}

		// process the settings
		$input = $this->actionInput; // @note this is $input->post!!
		$sanitizer = $this->wire('sanitizer');

		// expected general settings properties
		$generalSettingsProperties = [
			'shop_name',
			'shop_email',
			'shop_from_email',
			'legal_name_of_business',
			'address_line_one',
			'address_line_two',
			'city',
			'postal_code',
			'country',
			'phone',
			// ------
			// BANK DETAILS
			'bank_name',
			'bank_account_name',
			'bank_sort_code',
			'bank_account_number',
			'bank_iban',
			'bank_bic',
			// -------
			'enable_addons',
			'shop_currency',
			'shop_currency_format',
			'timezone',
			'date_format',
			'time_format',
			'weights_and_measures_system',
			'default_product_properties',
			'product_weight_property',
			'product_quick_filters_low_stock_threshold',
			'product_price_fields_type',
			'order_prefix',
			'order_suffix',
			'order_least_sales_threshold',
			'order_most_sales_threshold',
			'images_minimum_width',
			'images_minimum_height',
			'images_maximum_width',
			'images_maximum_height',
			'images_minimum_filesize',
			'images_maximum_filesize',
			'images_allowed_file_extensions',
			'downloads_minimum_filesize',
			'downloads_maximum_filesize',
			'allowed_downloads_file_extensions',
			'rest_of_the_world_shipping_zone',
			'gui_navigation_type',
			'gui_quick_filters_and_advanced_search'
		];

		$sanitizeAsIntegers = [
			// orders
			'order_least_sales_threshold',
			'order_most_sales_threshold',
			// products
			'product_quick_filters_low_stock_threshold',
			// images
			'images_minimum_width',
			'images_minimum_height',
			'images_maximum_width',
			'images_maximum_height',
			'images_minimum_filesize',
			'images_maximum_filesize',
			// files
			'downloads_minimum_filesize',
			'downloads_maximum_filesize',
		];
		// TODO FOR 'gui_navigation_type' ADD METHOD TO RUN MODULE REFRESH! IF 'DROPDOWN' MENU IS INVOLVED -> $modules->refresh()

		// process inputs
		$generalSettings = [];
		$inputPrefix = "pwcommerce_general_settings_";
		$guiNavigationTypeCurrentValue = $this->getCurrentGuiNavigationType();
		$guiNavigationTypeIncomingValue = '';
		foreach ($generalSettingsProperties as $property) {
			$inputName = "{$inputPrefix}{$property}";
			// email TODO: show error invalid email?
			// if ($property === 'shop_email') {
			if (in_array($property, ['shop_email', 'shop_from_email'])) {
				$value = $sanitizer->email($input->$inputName);
			} else if (in_array($property, $sanitizeAsIntegers)) {
				// min or max values
				// TODO: SHOULD WE SAVE ZEROS OR LEAVE BLANK OR NULL? WHAT IF ZERO WAS ACTUALLY INPUT?
				// TODO: OR SHOULD WE LEAVE THAT FOR DISPLAY TO HANDLE? LEAVE IT TO DISPLAY!
				$value = (int) $input->$inputName;

			} else if (in_array($property, ['default_product_properties', 'product_weight_property', 'rest_of_the_world_shipping_zone'])) {
				// special treatment of of InputfieldTextTags for default product properties,product weight property and rest of the world shipping zone
				// it sends saved values with '_' as prefix
				// we remove these
				$value = str_replace('_', '', $sanitizer->text($input->$inputName));
				// then sanitize as array of integers
				$value = $sanitizer->intArrayVal(explode(" ", $value));
				// for 'product_weight_property' and 'rest_of_the_world_shipping_zone' we only need to save one value
				if (in_array($property, ['product_weight_property', 'rest_of_the_world_shipping_zone'])) {
					$value = !empty($value[0]) ? $value[0] : 0;
				}
			} else {
				$value = $sanitizer->text($input->$inputName);
			}
			//---------
			$generalSettings[$property] = $value;

			// ------
			// GUI NAVIGATION TYPE
			// to track for special
			if ($inputName === 'pwcommerce_general_settings_gui_navigation_type') {
				$guiNavigationTypeIncomingValue = $value;
			}
		}
		// ------
		// SPECIAL HANDLING OF 'gui_navigation_type'
		// if creating afresh, we need to create an 'addons' page
		// else if disabling but was previously enabled AND box 'pwcommerce_general_settings_delete_addons_settings_page' is checked
		// then will need to delete the page
		// TODO - NEED TO ADD NOTICES FROM THIS!

		$this->processGUINavigationTypePage($guiNavigationTypeCurrentValue, $guiNavigationTypeIncomingValue);
		// ------
		// SPECIAL HANDLING OF 'enable_addons'
		// if creating afresh, we need to create an 'addons' page
		// else if disabling but was previously enabled AND box 'pwcommerce_general_settings_delete_addons_settings_page' is checked
		// then will need to delete the page
		// TODO - NEED TO ADD NOTICES FROM THIS!
		$addonsNotice = $this->processAddonsPage();
		//----------------
		// prepare the JSON string to save as general settings
		$generalSettingsJSON = json_encode($generalSettings);
		// assign to settings field
		$page->set('pwcommerce_settings', $generalSettingsJSON);
		//-------------
		// save the page's 'pwcommerce_settings' field
		$page->save('pwcommerce_settings');

		// TODO ALSO NEED TO ADD UPDATING OF EXTENSIONS OF pwcommerce_images and pwcommerce_file!!!

		// --------------------
		// prepare messages
		$notice = $this->_('Saved general settings.');
		if (!empty($addonsNotice)) {
			$notice .= " " . $addonsNotice;
		}

		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO? check if really saved first?
		];

		//-------
		return $result;
	}

	private function getCurrentGuiNavigationType() {
		$generalSettings = $this->pwcommerce->getShopGeneralSettings();
		$currentGuiNavigationType = $generalSettings->get('gui_navigation_type');
		// ---
		return $currentGuiNavigationType;
	}

	private function processGUINavigationTypePage($guiNavigationTypeCurrentValue, $guiNavigationTypeIncomingValue) {
		// note: side menu only option is 'side_menu_only'
		$dropdownNavigationTypes = [
			'side_and_dropdown_menus',
			'dropdown_menu_only'
		];
		// -----
		if ((in_array($guiNavigationTypeCurrentValue, $dropdownNavigationTypes)) && (in_array($guiNavigationTypeIncomingValue, $dropdownNavigationTypes))) {
			// NOTHING TO DO: DROPDOWNS ALREADY INVOLVED
		} else if ((in_array($guiNavigationTypeCurrentValue, $dropdownNavigationTypes)) || (in_array($guiNavigationTypeIncomingValue, $dropdownNavigationTypes))) {
			// we had or are going to have a 'dropdown' menu type
			// we need further processing
			// ---------
			// hide/unhide PWCommerce page
			$shopAdminPWCommerceRootPage = $this->page->child("name=" . PwCommerce::CHILD_PAGE_NAME . ",include=all");
			if ($guiNavigationTypeIncomingValue === 'side_menu_only') {
				// unhide pwcommerce page
				$shopAdminPWCommerceRootPage->removeStatus(Page::statusHidden);
			} else {
				// hide pwcommerce page
				$shopAdminPWCommerceRootPage->addStatus(Page::statusHidden);
			}
			// ------
			// save the page
			$shopAdminPWCommerceRootPage->save();
			// =======
			// TODO DOES NOT SEEM TO WORK HERE; LOGOUT NEEDED FIRST! ADD TO DOCS
			// module refresh needed either way
			$this->modules->refresh();
		}
	}

	private function actionCheckoutSettings() {

		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!

		//------------------
		// good to go

		// get the checkout settings page
		$page = $this->wire('pages')->get("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",name=checkout");

		// we didn't get the page; abort
		// TODO: meaningful error? e.g. checkout settings page not found?
		if (empty($page->id)) {
			return null;
		}

		// process the settings
		$input = $this->actionInput;
		$sanitizer = $this->wire('sanitizer');

		// expected checkout settings properties
		$checkoutSettingsProperties = [
			'account_requirement_at_checkout',
			'company_name_field_at_checkout',
			'shipping_address_phone_number_field_at_checkout',
			'show_billing_address_fields_by_default_at_checkout',
			'use_shipping_address_as_the_billing_address_by_default',
			'require_a_confirmation_step_before_purchase',
			'enable_address_autocompletion',
			'enable_automatic_fulfillment_after_an_order_has_been_paid',
			'automatically_send_abandoned_checkout_emails',
			'send_abandoned_checkout_emails_to',
			'send_abandoned_checkout_emails_after',
		];

		$sanitizeAsIntegers = [
			// abandoned checkouts
			'send_abandoned_checkout_emails_after',
		];

		$checkoutSettings = [];
		$inputPrefix = "pwcommerce_checkout_settings_";
		foreach ($checkoutSettingsProperties as $property) {
			$inputName = "{$inputPrefix}{$property}";
			if (in_array($property, $sanitizeAsIntegers)) {
				// min or max values
				// TODO: SHOULD WE SAVE ZEROS OR LEAVE BLANK OR NULL? WHAT IF ZERO WAS ACTUALLY INPUT?
				// TODO: OR SHOULD WE LEAVE THAT FOR DISPLAY TO HANDLE? LEAVE IT TO DISPLAY!
				// TODO: IN OUR CASE, IT WILL NOT BE AN OPTION FOR ABANDONED CHECKOUTS EMAIL AFTER!!!
				$value = (int) $input->$inputName;
			} else {
				$value = $sanitizer->text($input->$inputName);
			}
			//---------
			$checkoutSettings[$property] = $value;
		}
		//----------------

		// prepare the JSON string to save as checkout settings
		$checkoutSettingsJSON = json_encode($checkoutSettings);
		// assign to settings field
		$page->pwcommerce_settings = $checkoutSettingsJSON;
		//-------------
		// save the page's 'pwcommerce_settings' field
		$page->save('pwcommerce_settings');

		// --------------------
		// prepare messages
		$notice = $this->_('Saved checkout settings.');

		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO? check if really saved first?
		];

		//-------
		return $result;
	}

}
