<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesGeneralSettings
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ GENERAL SETTINGS ~~~~~~~~~~~~~~~~~~

	/**
	 * Get the shop's general settings.
	 *
	 * @return mixed
	 */
	public function getShopGeneralSettings() {
		// $generalSettings = [];
		$generalSettingsJSON = $this->wire('pages')->getRaw("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",name=general", 'pwcommerce_settings');
		$shopGeneralSettingsArray = [];
		if (!empty($generalSettingsJSON)) {
			$shopGeneralSettingsArray = json_decode($generalSettingsJSON, true);
		}
		$shopGeneralSettings = new WireData();
		$shopGeneralSettings->setArray($shopGeneralSettingsArray);
		// --------
		return $shopGeneralSettings;
	}


	/**
	 * Get Shop Email.
	 *
	 * @return mixed
	 */
	public function getShopEmail() {
		// for sending emails TO
		return $this->getShopGeneralSettings()->shop_email;
	}

	/**
	 * Get Shop From Email.
	 *
	 * @return mixed
	 */
	public function getShopFromEmail() {
		// for sending emails FROM
		return $this->getShopGeneralSettings()->shop_from_email;
	}

	/**
	 * Get Shop Bank Details.
	 *
	 * @return mixed
	 */
	public function getShopBankDetails() {
		/** @var WireData $shopGeneralSettings */
		$shopGeneralSettings = $this->getShopGeneralSettings();
		// shop bank details
		$shopBankDetails = new WireData();
		$shopBankDetails->bankName = $shopGeneralSettings->bank_name;
		$shopBankDetails->bankAccountName = $shopGeneralSettings->bank_account_name;
		$shopBankDetails->bankSortCode = $shopGeneralSettings->bank_sort_code;
		$shopBankDetails->bankAccountNumber = $shopGeneralSettings->bank_account_number;
		$shopBankDetails->bankIBAN = $shopGeneralSettings->bank_iban;
		$shopBankDetails->bankBIC = $shopGeneralSettings->bank_bic;
		// @note: same as BIC
		$shopBankDetails->bankSWIFTCode = $shopGeneralSettings->bank_bic;

		return $shopBankDetails;
	}

	/**
	 * Checks if all the required general settings of the shop have been saved.
	 *
	 * @return bool
	 */
	public function isAllRequiredGeneralSettingsSetUp() {
		$isAllRequiredGeneralSettingsSetUp = true;
		$generalSettings = $this->getShopGeneralSettings();
		if (empty($generalSettings)) {
			$isAllRequiredGeneralSettingsSetUp = false;
		} elseif (
			empty($generalSettings['shop_currency']) ||
			// empty($generalSettings['shop_currency_format']) ||
			empty($generalSettings['shop_email']) ||
			empty($generalSettings['address_line_one']) ||
			empty($generalSettings['city']) ||
			empty($generalSettings['postal_code']) ||
			empty($generalSettings['country']) ||
			empty($generalSettings['images_allowed_file_extensions']) ||
			empty($generalSettings['allowed_downloads_file_extensions'])
		) {
			$isAllRequiredGeneralSettingsSetUp = false;
		}

		// -------------
		return $isAllRequiredGeneralSettingsSetUp;
	}

	/**
	 * Check if shop allows installation and use of addons.
	 *
	 * @return bool
	 */
	public function isShopAllowAddons() {
		$generalSettings = $this->getShopGeneralSettings();
		return !empty($generalSettings['enable_addons']);
	}

	/**
	 * Does shop admin use 'quick filters'?
	 *
	 * @return bool
	 */
	public function isUseQuickFilters() {
		$generalSettings = $this->getShopGeneralSettings();
		// gui_quick_filters_and_advanced_search
		$isUseQuickFilters = !empty($generalSettings['gui_quick_filters_and_advanced_search']) && $generalSettings['gui_quick_filters_and_advanced_search'] !== 'advanced_search_only';
		return !empty($isUseQuickFilters);
	}

	/**
	 * Does shop admin use 'advanced search'?
	 *
	 * @return bool
	 */
	public function isUseAdvancedSearch() {
		$generalSettings = $this->getShopGeneralSettings();
		// gui_quick_filters_and_advanced_search
		$isUseAdvancedSearch = !empty($generalSettings['gui_quick_filters_and_advanced_search']) && $generalSettings['gui_quick_filters_and_advanced_search'] !== 'quick_filters_only';
		return !empty($isUseAdvancedSearch);
	}

	/**
	 * Does shop admin use 'dropdown menu'?
	 *
	 * @return bool
	 */
	public function isUseDropdownMenu() {
		$generalSettings = $this->getShopGeneralSettings();

		// gui_navigation_type
		$isUseQuickFilters = !empty($generalSettings['gui_navigation_type']) && $generalSettings['gui_navigation_type'] !== 'side_menu_only';

		return !empty($isUseQuickFilters);
	}

	/**
	 * Does shop admin use 'side menu'?
	 *
	 * @return bool
	 */
	public function isUseSideMenu() {
		$generalSettings = $this->getShopGeneralSettings();
		// gui_navigation_type
		$isUseSideMenu = !empty($generalSettings['gui_navigation_type']) && $generalSettings['gui_navigation_type'] !== 'dropdown_menu_only';
		return !empty($isUseSideMenu);
	}

}
