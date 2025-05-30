<?php

namespace ProcessWire;

/**
 * PWCommerce: Process Render Payment Providers
 *
 * Class to render content for PWCommerce Process Module executePaymentProviders().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderPaymentProviders for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */




class PWCommerceAdminRenderPaymentProviders extends WireData
{

	private $adminURL;

	private $paymentProvider;
	private $paymentProviderConfigs;
	private $nonCorePaymentProviderClassName;


	// ----------

	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->adminURL = $options['admin_url'];
		}
	}

	/**
	 * Render single payment provider special edit headline to append to the Process headline in PWCommerce.
	 *
	 * @return string $out Headline string to append to the main Process headline.
	 */
	public function renderSpecialEditItemHeadline(Page $paymentProvider)
	{
		$headline = $this->_('Edit Payment Provider');
		if ($paymentProvider->id) {
			// TODO: need to make sure this TITLE is formatted correctly!  TODO: if suffix or prefix, add to paymentProvider id, else show title!
			$headline .= ": {$paymentProvider->title}";
		}
		return $headline;
	}

	public function renderSpecialEditItem(Page $paymentProvider)
	{

		// if attempt to edit in-built invoice payment provider page OR
		// edit a locked payment provider via direct url access
		// we redirect to payment providers bulk edit dashboard
		if ($paymentProvider->name === 'invoice' || $paymentProvider->isLocked()) {
			$url = $this->adminURL . "payment-providers/";
			// TODO: REPHRASE OR NONDESCRIPT LIKE THIS?
			$this->warning($this->_('This page cannot be edited.'));
			$this->session->redirect($url);
		}

		// -----------
		$this->paymentProvider = $paymentProvider;
		/** @var InputfieldWrapper $wrapper */
		$wrapper = $this->buildSpecialEditPaymentProvider();
		// --------
		return $wrapper;
	}

	private function buildSpecialEditPaymentProvider()
	{
		/** @var array $schema */
		// TODO NEED TO ADD CLASS NAME FOR CUSTOM PAYMENT PROVIDER ADDONS!
		$schema = $this->getPaymentProviderConfigFieldsData();

		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		if (empty($schema)) {
			return $wrapper;
		}

		//-------------

		// loop through inputfields details for payment provider and build inputfields
		$cnt = 0;
		$paymentProviderInputNamesAndTypes = [];
		foreach ($schema as $inputfield) {

			if ($cnt === 0) {
				// we want a margin top on the first inputfield
				$inputfield['wrapper_classes'] = 'pwcommerce_first_field mt-7';
				$inputfield['wrapClass'] = true;
			}
			// -------
			// TODO: WE NEED TO SET SAVED VALUES!
			$field = $this->getInputfieldForEditPaymentProvider($inputfield);
			// set field value from saved values
			$inputfieldName = $inputfield['name'];
			$value = $this->getPaymentProviderSettingValue($inputfieldName);
			$field->attr('value', $value);
			if (in_array($inputfield['type'], ['checkbox'])) {
				$checked = !empty($value) ? true : false;
				$field->checked($checked);
			}
			$wrapper->add($field);

			// -------------
			// for later processing in save in PWCommerceActions, we need to know the names and types of the inputs to expect
			// these will be unique per payment provider, in many cases
			// @note: pipe-separated
			// TODO DO THIS EARLIER SO WE ADD IN SCHEMA SO TAKEN CARE OF IN LOOP?
			$paymentProviderInputNamesAndTypes[] = $inputfield['name'] . "|" . $inputfield['type'];
			// ------------
			$cnt++;
		}

		// @note: add custom payment addon class name
		if (!empty($this->pwcommerce->nonCorePaymentProviderClassName)) {
			$paymentProviderInputNamesAndTypes[] = 'addon_class_name' . "|pascal";
		}

		// ------------
		// ADD HIDDEN INPUT TO STORE SCHEMA type and name
		// this will enable PWCommerceActions to know inputs to expect and how to process them
		// @note: for custom addons, we also add class name!
		// @note: in PWCommerceActions::actionPaymentProviders we won't sanitizer the classname! ok? or use sanitizer->pascalCase()
		$paymentProviderInputNamesAndTypesCSV = implode(",", $paymentProviderInputNamesAndTypes);

		$options = [
			'id' => "pwcommerce_payment_provider_schema_inputs_names_and_types",
			'name' => 'pwcommerce_payment_provider_schema_inputs_names_and_types',
			'value' => $paymentProviderInputNamesAndTypesCSV,
		];
		//------------------- schema_inputs_names_and_types (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		// ------------
		// ADD HIDDEN INPUT TO STORE PAYMENT PROVIDER PAGE ID
		// this will enable PWCommerceActions to easily retrieve the page
		$options = [
			'id' => "pwcommerce_payment_provider_page_id",
			'name' => 'pwcommerce_payment_provider_page_id',
			'value' => $this->paymentProvider->id,
		];
		//------------------- payment_provider_page_id (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		// ------------
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPwCommerce::renderSpecialEditItem know that we are ready to save
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

		//------------------- save button (getInputfieldButton)
		// @note: not needed here. It is added in  ProcessPwCommerce::renderSpecialEditItem so that it is output 'below' the InputfieldWrapper, similar to processwire pages

		// -----
		return $wrapper;
	}

	private function getPaymentProviderConfigFieldsData()
	{
		// TODO: NEEDS TO BE IN CONTEXT FOR THE CURRENT EDIT! E.G. PAYPAL, STRIPE, ETC

		// TODO: FOR NOW, WE JUST CHECK USING KNOWN NAMES! WHEN WE START ACCEPTING THIRD-PARTY GATEWAYS, WE WE WILL TIGHTEN THIS CHECK
		$paymentProviderConfigFieldsData = [];
		// ------
		$paymentClass = !empty($this->isNonCorePaymentProvider()) ? $this->getNonCorePaymentProviderClass() : $this->getPaymentProviderClass();

		// TODO NEED TO THROW ERROR IF CLASS NOT FOUND!
		if (!empty($paymentClass)) {
			/** @var array $paymentClass->getFieldsSchema() */
			$paymentProviderConfigFieldsData = $paymentClass->getFieldsSchema();
		}
		// ----------
		return $paymentProviderConfigFieldsData;
	}

	private function getPaymentProviderClass()
	{
		$paymentClass = null;
		$className = "";
		// GET PROVIDER CLASS NAME
		// TODO: this needs to be more dynamic!
		if ($this->paymentProvider->name === 'paypal') {
			// PAYPAL
			// $className = "PWCommercePaymentPayPal";
			// $pwcommercePaymentGatewaysPath = "";
			// ------------
			$className = "PWCommercePaymentPayPal";
		} elseif ($this->paymentProvider->name === 'stripe') {
			// STRIPE
			$className = "PWCommercePaymentStripe";
		}
		// ------
		if (!empty($className)) {
			$paymentProviderConfigs = $this->getPaymentProviderConfigs();
			$paymentClass = $this->pwcommerce->getPWCommerceClassByName($className, $paymentProviderConfigs);
		}

		// ----------
		return $paymentClass;
	}

	private function getNonCorePaymentProviderClass()
	{

		$paymentClass = null;
		$addonsSettings = $this->pwcommerce->getAddonsSettings();

		$paymentProviderAddonConfigs = array_filter($addonsSettings, fn($item) => (int) $item['pwcommerce_addon_page_id'] === (int) $this->paymentProvider->id);

		$addonClassName = null;
		if (!empty($paymentProviderAddonConfigs)) {
			$addonClassName = array_keys($paymentProviderAddonConfigs)[0];
		}

		// --------------
		$paymentProviderConfigs = $this->getPaymentProviderConfigs();

		if (!empty($addonClassName)) {
			// $addonClassName = $paymentProviderConfigs['addon_class_name'];
			// TODO DELETE IF NOT NEEDED
			$this->nonCorePaymentProviderClassName = $addonClassName;

			// $addonClassName = $this->nonCorePaymentProviderClassName;

			$addonClassFilePath = $this->wire('config')->paths->templates . "pwcommerce/addons/{$addonClassName}/{$addonClassName}.php";
			$files = $this->wire('files');
			if ($files->exists($addonClassFilePath, 'readable')) {

				// ========
				require_once $addonClassFilePath;
				// instantiate class
				// $nameSpacedClassName = "\ProcessWire\\" . $addonClassName;
				$nameSpacedClassName = "\ProcessWire\\$addonClassName";
				$paymentProviderConfigs = $this->getPaymentProviderConfigs();
				/** @var object $addonClass */
				$paymentClass = new $nameSpacedClassName($paymentProviderConfigs);
			}
		}
		// ----------
		return $paymentClass;
	}

	private function getPaymentProviderConfigs()
	{
		/** @var array $paymentProviderConfigs */
		$paymentProviderConfigs = [
			// set some default values for provider
			// helpful in various cases
			// e.g. in case no configs saved yet
			// and we don't want an empty $options in
			//  TraitPWCommerceLoader::getPWCommerceClassByName
			'id' => $this->paymentProvider->id,
			'name' => $this->paymentProvider->name,
		];


		if (!empty($this->paymentProvider->pwcommerce_settings)) {
			$paymentProviderConfigs2 = json_decode($this->paymentProvider->pwcommerce_settings, true);

			$paymentProviderConfigs = array_merge($paymentProviderConfigs, $paymentProviderConfigs2);
		}

		// ----------------
		$this->paymentProviderConfigs = $paymentProviderConfigs;

		return $paymentProviderConfigs;
	}

	private function getInputfieldForEditPaymentProvider($options)
	{
		// TODO: DELETE UNREQUIRED ONES!
		// TODO: CONSIDER SUPPORTING EMAIL FIELD IN THE FUTURE!
		$type = $options['type'];
		if (in_array($type, ['text', 'number', 'email'])) {
			$field = $this->pwcommerce->getInputfieldText($options);
		} else if ($type === 'textarea') {
			$field = $this->pwcommerce->getInputfieldTextarea($options);
		} else if ($type === 'radio') {
			$field = $this->pwcommerce->getInputfieldRadios($options);
		} else if ($type === 'checkbox') {
			$field = $this->pwcommerce->getInputfieldCheckbox($options);
		}
		return $field;
	}

	private function getPaymentProviderSettingValue($setting)
	{
		$paymentProviderConfigs = $this->paymentProviderConfigs;
		// TODO: SHOULD WE SAVE ZEROS OR LEAVE BLANK OR NULL? WHAT IF ZERO WAS ACTUALLY INPUT?
		// TODO: OR SHOULD WE LEAVE THAT FOR DISPLAY TO HANDLE? LEAVE IT TO DISPLAY!
		$value = isset($paymentProviderConfigs[$setting]) ? $paymentProviderConfigs[$setting] : null;
		return $value;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~


	protected function getResultsTableHeaders()
	{
		return [
			// TITLE
			[$this->_('Title'), 'pwcommerce_payment_providers_table_title'],
			// USAGE
			[$this->_('Active'), 'pwcommerce_payment_providers_table_active'],
		];
	}

	protected function getResultsTableRow($page, $editItemTitle)
	{
		$active = $this->_('Activated');
		$inactive = $this->_('Not activated');
		$activePaymentProviderString = !empty($page->isUnpublished()) ? $inactive : $active;
		// ALSO SHOW if IN LIVE vs SANDBOX mode
		$isLive = $this->isLivePaymentProvider($page);
		$isLive = $this->_('Live payments');
		$isNotLive = $this->_('Test payments');
		$livePaymentProviderString = !empty($this->isLivePaymentProvider($page)) ? $isLive : $isNotLive;

		if($page->name === 'invoice'){
			$statusString = $activePaymentProviderString;
		}
		else {
			$statusString = "{$activePaymentProviderString}, {$livePaymentProviderString}";
		}



		// ---------------
		$row = [
			// TITLE
			$editItemTitle,
			// ACTIVE TODO: ACTIVE PAYMENT PROVIDER (i.e. published)
			// TODO -> in frontend form need to skip inactive! make sure finder does not return them!
			// $activePaymentProviderString,
			$statusString,
		];
		return $row;
	}

	protected function getNoResultsTableRecords()
	{
		$noResultsTableRecords = $this->_('No payment providers found.');
		return $noResultsTableRecords;
	}


	protected function getEditItemURL($page)
	{
		$label = '';

		$paymentProviderSettings = $this->getPaymentProviderSettings($page);
		// display payment method label if available
		if (!empty($paymentProviderSettings['payment_method_label'])) {
			$label = " ({$paymentProviderSettings['payment_method_label']})";
			$label = "<small>{$label}</small>";
		}

		// if page is locked OR is invoice, don't show edit URL
		if ($page->isLocked() || $page->name === 'invoice') {
			$out = "<span>{$page->title}{$label}</span>";
		} else {
			$out = "<a href='{$this->adminURL}payment-providers/edit/?id={$page->id}'>{$page->title}</a>{$label}";
		}
		return $out;
	}

	protected function getBulkEditActionsPanel($adminURL)
	{
		$actions = [
			// @note: means published
			'activate' => $this->_('Active'),
			// @note: means unpublished
			'deactivate' => $this->_('Inactive'),
			'lock' => $this->_('Lock'),
			'unlock' => $this->_('Unlock'),
			// TODO: UNSURE ABOUT DELETION? OR IF NOT PRESENT, JUST HIDE?
			// 'trash' => $this->_('Trash'),
			// 'delete' => $this->_('Delete'),
		];
		$options = [
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		// TODO: NEED TO HIDE ADD NEW!
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	private function getPaymentProviderSettings($page)
	{
		$paymentProviderSettings = [];
		$paymentProviderSettingsJSON = $page->get(PwCommerce::SETTINGS_FIELD_NAME);
		if (!empty($paymentProviderSettingsJSON)) {
			$paymentProviderSettings = json_decode($paymentProviderSettingsJSON, true);
		}
		return $paymentProviderSettings;
	}

	private function isLivePaymentProvider($page){
		$paymentProviderSettings = $this->getPaymentProviderSettings($page);
		$isLive = !empty($paymentProviderSettings['is_live']);
		return $isLive;
	}



	// ~~~~~~~~~~~~~~~~~~ ADDONS/NON-CORE PAYMENT PROVIDERS ~~~~~~~~~~~

	// TODO DELETE WHEN DONE AS DOING DIFFERENTLY! OR AMEND!




	private function isNonCorePaymentProvider()
	{
		$namesOfCorePaymentAddons = $this->pwcommerce->getNamesOfCorePaymentAddons();

		return !in_array($this->paymentProvider->name, $namesOfCorePaymentAddons);
	}
}
