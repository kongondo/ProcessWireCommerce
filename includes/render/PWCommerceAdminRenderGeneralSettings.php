<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render General Settings
 *
 * Class to render content for PWCommerce Admin Module executeGeneralSettings().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderGeneralSettings for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

// =========
// IMPORT TRAITS FILES
$traitsFiles = ["TraitPWCommerceAdminRenderGeneralSettings"];

foreach ($traitsFiles as $traitFileName) {
	require_once __DIR__ . "/../../traits/admin/{$traitFileName}.php";
}

class PWCommerceAdminRenderGeneralSettings extends WireData
{

	// =============
	// TRAITS

	use TraitPWCommerceAdminRenderGeneralSettings;

	private $generalSettings;
	private $currencies;
	private $countries;
	private $adminURL;
	# ----------
	// the ALPINE JS store used by this Class
	private $xstoreProcessPWCommerce;
	// the full prefix to the ALPINE JS store used by this Class
	private $xstore;


	/**
	 *   construct.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __construct($options = null) {

		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INIT  ~~~~~~~~~~~~~~~~~~
		parent::__construct();
		if (is_array($options)) {
			$this->adminURL = $options['admin_url'];
			$this->xstoreProcessPWCommerce = $options['xstoreProcessPWCommerce'];
			// i.e., '$store.ProcessPWCommerceStore'
			$this->xstore = $options['xstore'];
		}
		//-----------
		$this->currencies = $this->pwcommerce->getPWCommerceClassByName('PWCommerceCurrencies');
		$this->countries = $this->pwcommerce->getPWCommerceClassByName('PWCommerceCountries');
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ TABS  ~~~~~~~~~~~~~~~~~~

	/**
	 * Get Tabs.
	 *
	 * @param InputfieldWrapper $wrapper
	 * @return mixed
	 */
	protected function getTabs(InputfieldWrapper $wrapper) {

		// GET GENERAL SETTINGS PAGE
		// TODO: name ok? or search by title?
		$generalSettingsJSON = $this->wire('pages')->getRaw("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",name=general", 'pwcommerce_settings');

		$generalSettings = [];
		if (!empty($generalSettingsJSON)) {
			$generalSettings = json_decode($generalSettingsJSON, true);
		}

		$this->generalSettings = $generalSettings;

		//-------------------

		$tabsNames = ['main', 'standards', 'orders', 'products', 'images', 'files', 'shipping', 'gui'];

		foreach ($tabsNames as $tabName) {
			$tabContents = $this->getGeneralSettingsTabs($tabName);

			$tab = $this->pwcommerce->getInputfieldWrapper();
			$tabDetails = $tabContents['details'];
			$tab->attr([
				'id' => $tabDetails['id'],
				'class' => 'WireTab',
				'title' => $tabDetails['title'],
			]);
			//------------
			$tabInputfields = $tabContents['inputfields'];
			// loop through inputfields details and build inputfields
			foreach ($tabInputfields as $inputfield) {
				$field = $this->getInputfieldForTab($inputfield);
				if (!empty($inputfield['wrapAttr'])) {
					$field = $this->setWrapAttr($field, $inputfield['wrapAttr']);
				}
				if (isset($inputfield['entityEncodeText'])) {
					// we are not entity encoding text!
					$field->entityEncodeText = false;
				}
				$tab->add($field);
			}
			// add tab to tabs wrapper
			$wrapper->add($tab);
		}

		// ------------
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPwCommerce::pagesHandler know that we are ready to save
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];
		//------------------- is_ready_to_save (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		//--------------
		return $wrapper;
	}

	/**
	 * Get General Settings Tabs.
	 *
	 * @param mixed $tabName
	 * @return mixed
	 */
	public function getGeneralSettingsTabs($tabName) {
		if ($tabName === 'main') {
			$tab = $this->getMainTab();
		} else if ($tabName === 'standards') {
			$tab = $this->getStandardsTab();
		} else if ($tabName === 'orders') {
			$tab = $this->getOrdersTab();
		} else if ($tabName === 'products') {
			$tab = $this->getProductsTab();
		} else if ($tabName === 'images') {
			$tab = $this->getImagesTab();
		} else if ($tabName === 'files') {
			$tab = $this->getFilesTab();
		} else if ($tabName === 'shipping') {
			$tab = $this->getShippingTab();
		} else if ($tabName === 'gui') {
			$tab = $this->getGUITab();
		}

		return $tab;
	}

	/**
	 * Get Inputfield For Tab.
	 *
	 * @param array $options
	 * @return mixed
	 */
	private function getInputfieldForTab($options) {
		$type = $options['type'];
		if (in_array($type, ['text', 'number'])) {
			$field = $this->pwcommerce->getInputfieldText($options);
		} else if ($type === 'textarea') {
			$field = $this->pwcommerce->getInputfieldTextarea($options);
		} else if ($type === 'radio') {
			$field = $this->pwcommerce->getInputfieldRadios($options);
		} else if ($type === 'select') {
			$field = $this->pwcommerce->getInputfieldSelect($options);
		} else if ($type === 'tags') {
			$field = $this->pwcommerce->getInputfieldTextTags($options);
		} else if ($type === 'checkbox') {
			$field = $this->pwcommerce->getInputfieldCheckbox($options);
		} else if ($type === 'markup') {
			$field = $this->pwcommerce->getInputfieldMarkup($options);
		}

		return $field;
	}

	/**
	 * Set Wrap Attr.
	 *
	 * @param mixed $field
	 * @param array $wrapAttrs
	 * @return mixed
	 */
	private function setWrapAttr($field, array $wrapAttrs) {
		foreach ($wrapAttrs as $wrapAttr) {
			$field->wrapAttr($wrapAttr['dataset'], $wrapAttr['value']);
		}
		// --------
		return $field;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ SETTINGS  ~~~~~~~~~~~~~~~~~~

	/**
	 * Get General Setting Value.
	 *
	 * @param mixed $setting
	 * @return mixed
	 */
	private function getGeneralSettingValue($setting) {
		$generalSettings = $this->generalSettings;
		// @note: if saved values are pages, e.g. 'default_product_properties'
		// we use dedicated methods to fetch the pages
		// TODO: SHOULD WE SAVE ZEROS OR LEAVE BLANK OR NULL? WHAT IF ZERO WAS ACTUALLY INPUT?
		// TODO: OR SHOULD WE LEAVE THAT FOR DISPLAY TO HANDLE? LEAVE IT TO DISPLAY!
		$value = isset($generalSettings[$setting]) ? $generalSettings[$setting] : null;
		return $value;
	}


}