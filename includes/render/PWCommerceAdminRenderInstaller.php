<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Installer.
 *
 * Methods to help render or enhance PWCommerce Inputfields.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderInstaller for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */




class PWCommerceAdminRenderInstaller extends WireData
{

	private $options;
	private $shopProcessPWCommercePageID;
	private $shopProcessPWCommercePageURL;
	private $shopAdminPWCommerceRootPageID;
	// name of the module whose configurations we will use to check installed optional features, i.e. ProcessPWCommerce
	private $configModuleName;
	// -------------


	private $isSecondStageInstallConfiguration = false;
	private $installedOptionalFeatures = [];
	private $installedOtherOptionalSettings = [];
	// ------------
	private $xstore;

	/**
	 *   construct.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __construct($options = null) {
		parent::__construct();

		// TODO????
		if (is_array($options)) {
			$this->options = $options;
		}

		// TODO THROW ERROR IF NOT SET?!
		if (!empty($options['config_module_name'])) {
			$this->configModuleName = $options['config_module_name'];
		}

		// TODO THROW ERROR IF NOT SET?!
		// @note: this is the id of the process page itself for ProcessPWCommerce
		// the page has a single child page called 'pwcommerce'
		// @see ProcessPwCommerce::install for why that child page is required
		if (!empty($options['shop_process_pwcommerce_page_id'])) {
			$this->shopProcessPWCommercePageID = $options['shop_process_pwcommerce_page_id'];
		}

		// TODO THROW ERROR IF NOT SET?!
		// @note: this is the main parent page for all other pwcommerce parent pages
		// i.e. products, orders, categories, settings, etc
		// @see ProcessPwCommerce::install for why this main parent page is required
		if (!empty($options['shop_admin_pwcommerce_root_page_id'])) {
			$this->shopAdminPWCommerceRootPageID = $options['shop_admin_pwcommerce_root_page_id'];
		}

		// the shop home/landing page URL (i.e., the admin page of ProcessPWCommerce module)
		if (!empty($options['shop_process_pwcommerce_page_url'])) {
			$this->shopProcessPWCommercePageURL = $options['shop_process_pwcommerce_page_url'];
		}

		// GET INPUTFIELDS HELPERS


		// GET UTILITIES


		// +++++++++++
		$this->xstore = '$store.ProcessPWCommerceStore';
	}

	/**
	 * Render Installer.
	 *
	 * @param mixed $status
	 * @return string|mixed
	 */
	public function renderInstaller($status) {

		// just in case statuses botched up!
		$out = $this->_('There has been an error. Please contact your system administrator');
		if (empty($status)) {
			return $out;
		}

		// SET VARIABLES
		if ($status === PwCommerce::PWCOMMERCE_SECOND_STAGE_INSTALL_CONFIGURATION_STATUS) {
			// MODIFY A PREVIOUS PWCOMMERCE CONFIGURATION
			// -----------------
			$this->isSecondStageInstallConfiguration = true;
			// TODO DELETE IF NOT IN USE
			// $this->pwcommerceConfigs = $this->pwcommerce->getPWCommerceModuleConfigs($this->configModuleName);
			$this->installedOptionalFeatures = $this->pwcommerce->getPWCommerceInstalledOptionalFeatures($this->configModuleName);
			$this->installedOtherOptionalSettings = $this->pwcommerce->getPWCommerceInstalledOtherOptionalSettings($this->configModuleName);
		} else {
			// FIRST TIME CONFIGURATION
			// ---------------
			// CHECK IF ORDER STATUS TABLE ALREADY ON SERVER
			// TODO DO WE ALSO CHECK 'pwcommerce_cart' table?
			// in true, abort: we need to create ours from scratch
			$orderStatusTableName = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE_NAME;
			// TODO DO WE ALSO CHECK 'pwcommerce_cart' table?
			$orderStatusTableName = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE_NAME;
			if ($this->pwcommerce->isExistPWCommerceCustomTable($orderStatusTableName)) {
				return $this->orderStatusTableAlreadyExistsMarkup();
			}
		}

		$out = $this->renderConfigurePWCommerceMarkup();

		// wrap in x-data
		$out = "<div class='mt-10' x-data='ProcessPWCommerceData' @pwcommerceconfirmrunconfigureinstall.window='handlePWCommerceConfirmRunInstaller'>{$out}</div>";

		return $out;
	}

	/**
	 * Render Configure P W Commerce Markup.
	 *
	 * @return string|mixed
	 */
	private function renderConfigurePWCommerceMarkup() {
		$out = "";
		// @note: render fields below  instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::renderConfigureInstall() we add the output there to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.

		// ----------

		if ($this->isSecondStageInstallConfiguration) {
			// modifying an existing install
			$configureInstallInfo = $this->getModifyPWCommerceConfigurationInfoMarkup();
		} else {
			// first time configuring install
			$configureInstallInfo = $this->getFirstTimePWCommerceConfigurationInfoMarkup();
		}
		// ---------------------
		// $out .= $this->getFirstTimePWCommerceConfigurationInfoMarkup();
		$out .= $configureInstallInfo;
		// divider between info and features
		$out .= "<hr>";
		// render required features list
		$out .= $this->getPWCommerceRequiredFeaturesListMarkup();
		// divider between required and optional features only in larger screens
		$out .= "<hr class='hidden md:block'>";
		// render optional features list
		$out .= $this->getPWCommerceOptionalFeaturesListMarkup();
		// divider between optional features and other optional settings only in larger screens
		$out .= "<hr class='hidden md:block'>";
		// render other optional settings list
		$out .= $this->getPWCommerceOtherOptionalSettingsListMarkup();
		// -----
		// add confirm modal
		// @note: we need this to be inside the form so htmx will submit checkboxes (inputs) without additional code to find them
		$out .= $this->getModalMarkupForConfirmConfigureInstall();

		// ------------
		// ADD HIDDEN INPUT TO SPECIFY ACTION AS 'configure install'
		$options = [
			'id' => "pwcommerce_is_configure_install",
			'name' => 'pwcommerce_is_configure_install',
			'value' => 1,
		];
		//------------------- is_configure_install (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$out .= $field->render();
		// $wrapper->add($field);
		// ------------
		// ------------
		// ADD HIDDEN INPUT WITH ID OF SHOP ADMIN PWCOMMERCE PAGE ID
		// @note: this is the main parent page for all other pwcommerce parent pages
		// needed for help with reverting 'reparenting of some parent pwcommerce pages'
		// @see 'CUSTOM SHOP ROOT PAGE' feature
		$options = [
			'id' => "pwcommerce_shop_admin_pwcommerce_root_page_id",
			'name' => 'pwcommerce_shop_admin_pwcommerce_root_page_id',
			'value' => $this->shopAdminPWCommerceRootPageID,
		];
		//------------------- shop_admin_pwcommerce_root_page_id (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$out .= $field->render();
		// $wrapper->add($field);
		// ------------
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPWCommerce know that we are ready to save
		// @note: here just for consistency with other pages/views
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];
		//------------------- is_ready_to_save (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$out .= $field->render();

		// ----
		// add js configs
		$out .= $this->getPWCommerceOptionalFeaturesScriptMarkup();

		// -------

		// ----------
		return $out;
	}

	/**
	 * Render Complete Removal.
	 *
	 * @return string|mixed
	 */
	public function renderCompleteRemoval() {
		$out = $this->renderCompleteRemovalMarkup();
		// wrap in x-data
		$out = "<div id='pwcommerce_complete_removal_warning_wrapper' class='mt-10' x-data='ProcessPWCommerceData' @pwcommerceconfirmruncompleteremoval.window='handlePWCommerceConfirmCompleteRemoval'>{$out}</div>";
		return $out;
	}

	/**
	 * Render Complete Removal Markup.
	 *
	 * @return string|mixed
	 */
	private function renderCompleteRemovalMarkup() {
		$out = "";
		// @note: render fields below  instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::renderCompleteRemoval() we add the output there to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.

		// ----------

		$completeRemovalInfo = $this->_('Use this cleanup tool to completely remove PWCommerce templates, fields and pages. This action cannot be undone. Backup your database if you need to. The tool will also attempt to uninstall all PWCommerce modules. If this fails, you will have to carry this out manually.');

		// ---------------------
		$out .= $completeRemovalInfo;

		// -----
		// add confirm modal
		$out .= $this->getModalMarkupForConfirmCompleteRemoval();

		// ------------
		// ADD HIDDEN INPUT TO SPECIFY ACTION AS 'complete removal'
		$options = [
			'id' => "pwcommerce_is_complete_removal",
			'name' => 'pwcommerce_is_complete_removal',
			'value' => 1,
		];
		//------------------- is_complate_removal (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$out .= $field->render();
		// $wrapper->add($field);
		// ------------
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPWCommerce know that we are ready to save
		// @note: here just for consistency with other pages/views
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];
		//------------------- is_ready_to_save (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$out .= $field->render();

		// ----------
		return $out;
	}

	// TODO DO SAME FOR CART TABLE? ALTHOUGH IT IS NOT AS IMPORTANT FOR THIS INSTALLATION NEED? it gets emptied regularly
	/**
	 * Order Status Table Already Exists Markup.
	 *
	 * @return mixed
	 */
	private function orderStatusTableAlreadyExistsMarkup() {
		$notice = sprintf(__("A table named %s already exists in your database. You will need to either remove or rename it in order for PWCommerce installation to proceed. PWCommerce requires and will install a similarly named table."), PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE_NAME);
		$out = "<p>" .
			$notice .
			"</p>";
		$out = "<div class='mt-10'>{$out}</div>";
		return $out;
	}

	// ~~~~~~~~~~~~~
	/**
	 * Get First Time P W Commerce Configuration Info Markup.
	 *
	 * @return mixed
	 */
	private function getFirstTimePWCommerceConfigurationInfoMarkup() {
		$out = "<div><p>" .

			$this->_('Now that you have installed PWCommerce, it is time to configure your shop. Below, you will find the available shop features. Some of the features are required and will be installed automatically. Other features are optional. If you change your mind about an optional feature, you can always come back here and modify your installation configuration. Please note that some features are dependent on other features in order to work. Selecting such features will automatically cause the dependents to be selected as well.') .
			"</p></div>";
		// ----
		return $out;
	}

	/**
	 * Get Modify P W Commerce Configuration Info Markup.
	 *
	 * @return mixed
	 */
	private function getModifyPWCommerceConfigurationInfoMarkup() {

		$shopLink =
			"<a href='{$this->shopProcessPWCommercePageURL}'>" .
			$this->_('here') .
			"</a>";

		$installedNotice = sprintf(__("PWCommerce has been installed and configured. If you wish, you can modify the configuration. Below, you will find the list of installed shop features. Required features cannot be changed. Optional features can be modified by unselecting them. Otherwise, you can view your shop by clicking %s."), $shopLink);
		$out = "<div><p>" .
			// ----
			$installedNotice .
			// ----
			"<span id='pwcommerce_modify_install_warning' class='block mt-5 mb-5'>" .
			$this->_('Please note that removing an optional feature that was previously installed may lead to data loss. All fields, pages and templates associated with that feature will be irreversibly removed. The removal includes any custom fields you might have added to the templates. You might wish to make a backup before proceeding.') .
			"</span>" .
			// ----
			"</p></div>";
		// ----
		return $out;
	}

	/**
	 * Get P W Commerce Required Features List Markup.
	 *
	 * @return mixed
	 */
	private function getPWCommerceRequiredFeaturesListMarkup() {
		$out = '';

		$feature = $this->_('Feature');
		$description = $this->_('Description');

		// --------
		$out .=
			"<div>" .
			"<h3>" . $this->_('Required') . "</h3>";

		// ----

		$out .=
			"<div class='grid grid-cols-4 gap-4 mb-2'>" .
			// shop feature
			"<div class='col-span-full md:col-span-1 hidden md:block'>" .
			"<h4>" . $feature . "</h4>" .
			"</div>" .
			// feature description
			"<div class='col-span-full md:col-span-3 hidden md:block'>" .
			"<h4>" . $description . "</h4>" .
			"</div>" .
			// ------
			"</div>";

		$requiredFeaturesList = $this->getPWCommerceRequiredFeaturesList();
		// -----------

		foreach ($requiredFeaturesList as $requiredFeature) {
			$featureName = $requiredFeature['feature'];
			$featureDescription = $requiredFeature['description'];
			$out .=
				"<div class='grid grid-cols-4 gap-4 mb-2'>" .
				// shop feature
				"<div class='col-span-full md:col-span-1'>" .
				// "<p><span class='md:hidden'>" . $feature . ": </span>{$featureName}</p>" .
				"<span>{$featureName}</span>" .
				"</div>" .
				// feature description
				"<div class='col-span-full md:col-span-3'>" .
				// "<p><span class='md:hidden'>" . $description . ": </span>{$featureDescription}</p>" .
				"<p>{$featureDescription}</p>" .
				"</div>" .
				"</div>" .
				// divider between features if smaller screen
				"<hr class='md:hidden'>";
		}
		// end of loop
		// ---------
		$out .= "</div>";
		// --------
		return $out;
	}

	/**
	 * Get P W Commerce Required Features List.
	 *
	 * @return mixed
	 */
	private function getPWCommerceRequiredFeaturesList() {
		/*

																																																																				REQUIRED

																																																																				products
																																																																				orders
																																																																				shipping
																																																																				taxes
																																																																				- settings
																																																																				- rates
																																																																				*/
		$requiredFeaturesList = [
			// products
			'products' => [
				'feature' => $this->_('Products'),
				'description' => $this->_('Create and manage shop products.')
			],
			// orders
			'orders' => [
				'feature' => $this->_('Orders'),
				'description' => $this->_('Create and manage shop orders.')
			],
			// shipping
			'shipping' => [
				'feature' => $this->_('Shipping'),
				// TODO: better description!
				'description' => $this->_('Create and manage shipping zones, fees, rates and delivery options.')
			],
			// taxes
			'taxes' => [
				'feature' => $this->_('Taxes'),
				// TODO: better description!
				'description' => $this->_('Create and manage tax settings, shipping countries and their tax rates and tax overrides.')
			]
		];
		// --------
		return $requiredFeaturesList;
	}
	/**
	 * Get P W Commerce Optional Features List Markup.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeaturesListMarkup() {
		$out = '';
		$featureString = $this->_('Feature');
		$description = $this->_('Description');
		// --------
		$out .=
			"<div>" .
			"<h3>" . $this->_('Optional') . "</h3>";
		// ----
		$out .=
			"<div class='grid grid-cols-4 gap-4 mb-2'>" .
			// shop feature
			"<div class='col-span-full md:col-span-1 hidden md:block'>" .
			"<h4>" . $featureString . "</h4>" .
			"</div>" .
			// feature description
			"<div class='col-span-full md:col-span-3 hidden md:block'>" .
			"<h4>" . $description . "</h4>" .
			"</div>" .
			// ------
			"</div>";
		$optionalFeaturesList = $this->getPWCommerceOptionalFeaturesList();
		// -----------

		foreach ($optionalFeaturesList as $feature => $optionalFeature) {
			$featureName = $optionalFeature['feature'];
			$featureDescription = $optionalFeature['description'];
			// ---------------
			// does this optional feature have a dependency? if yes, it requires a alpine js handler
			$isRequiresHandler = $this->isPWCommerceOptionalFeatureCheckboxRequiresHandler($feature);
			// is this optional feature a dependency for another feature? if yes, we will toggle its 'disable' attribute and checked status in JS
			// in relation to its two way dependency
			$isADependency = $this->isPWCommerceOptionalFeatureADependency($feature);
			// is this optional feature a ONE-WAY dependency for another feature? if yes, we will toggle its 'disable' attribute and checked status in JS
			$isAOneWayDependency = $this->isPWCommerceOptionalFeatureAOneWayDependency($feature);
			$isAOneWayDependent = $this->isPWCommerceOptionalFeatureAOneWayDependent($feature);
			$extraClasses = empty($isADependency) ? ' pwcommerce_configure_install_optional_feature' : '';
			// @note: will check if in SECOND STAGE INSTALL CONFIGURATION + IF FEATURE WAS INSTALLED
			$checked = $this->isOptionalFeatureInstalledAdminRenderCheck($feature);
			// -----------
			$options = [
				'feature' => $feature,
				'label' => $featureName,
				'checked' => $checked,
				'is_requires_handler' => $isRequiresHandler,
				'is_a_dependency' => $isADependency,
				'is_a_one_way_dependency' => $isAOneWayDependency,
				'is_a_one_way_dependent' => $isAOneWayDependent,
				'is_installed' => $checked
			];
			$checkbox = $this->getPWCommerceOptionalFeatureCheckbox($options);
			$isOptionalFeatureInstalledMarkup = "";
			if ($checked) {
				// visual that optional feature is currently installed
				$isOptionalFeatureInstalledMarkup =
					"<small class='opacity-50 block md:mb-5'>" .
					$this->_('installed') .
					"</small>";
			}

			// ---------------
			$out .=
				"<div class='grid grid-cols-4 gap-4 mb-2'>" .
				// shop feature
				"<div class='col-span-full md:col-span-1{$extraClasses}'>" .
				// "<p>{$checkbox}<span class='md:hidden'>" . $feature . ": </span>{$featureName}</p>" .
				$checkbox .
				// small visual for is feature installed
				$isOptionalFeatureInstalledMarkup .
				"</div>" .
				// feature description
				"<div class='col-span-full md:col-span-3'>" .
				// "<p><span class='md:hidden'>" . $description . ": </span>{$featureDescription}</p>" .
				"<p>{$featureDescription}</p>" .
				"</div>" .
				"</div>" .
				// divider between features if smaller screen
				"<hr class='md:hidden'>";
		}
		// end loop
		// ---------
		$out .= "</div>";
		// --------
		return $out;
	}

	/**
	 * Get P W Commerce Other Optional Settings List Markup.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOtherOptionalSettingsListMarkup() {
		$out = '';
		$settingString = $this->_('Setting');
		$description = $this->_('Description');
		// --------
		$out .=
			"<div>" .
			"<h3>" . $this->_('Other Optional Settings') . "</h3>";
		// ----
		$out .=
			"<div class='grid grid-cols-4 gap-4 mb-2'>" .
			// optional setting
			"<div class='col-span-full md:col-span-1 hidden md:block'>" .
			"<h4>" . $settingString . "</h4>" .
			"</div>" .
			// setting description
			"<div class='col-span-full md:col-span-3 hidden md:block'>" .
			"<h4>" . $description . "</h4>" .
			"</div>" .
			// ------
			"</div>";
		$otherOptionalSettingsList = $this->getPWCommerceOtherOptionalSettingsList();
		// -----------

		foreach ($otherOptionalSettingsList as $setting => $otherOptionalSetting) {
			$settingName = $otherOptionalSetting['setting'];
			$settingDescription = $otherOptionalSetting['description'];
			// ---------------

			// @note: will check if in SECOND STAGE INSTALL CONFIGURATION + IF FEATURE WAS INSTALLED
			$checked = $this->isOtherOptionalSettingInstalled($setting);
			// -----------
			// checkbox options
			$options = [
				'id' => $setting,
				'name' => $setting,
				'label' => $settingName,
				'collapsed' => Inputfield::collapsedNever,
				'classes' => 'mr-1',
				'wrapClass' => true,
				'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_content_padding_top pwcommerce_override_processwire_inputfield_content_padding_left pwcommerce_is_use_custom_shop_root_page',
				'checked' => empty($checked) ? false : true,
				'value' => 1
			];

			$settingNotes = '';
			if (!empty($otherOptionalSetting['notes'])) {
				$settingNotes = "<p class='notes'>{$otherOptionalSetting['notes']}</p>";
			}
			$field = $this->pwcommerce->getInputfieldCheckbox($options);
			$attrs = $otherOptionalSetting['attrs'];
			$field->attr($attrs);

			$isOtherOptionalSettingInstalledMarkup = "";
			if ($checked) {
				// visual that other optional setting is currently installed
				$isOtherOptionalSettingInstalledMarkup =
					"<small class='opacity-50 block md:mb-5'>" .
					$this->_('installed') .
					"</small>";
			}

			// ---------------
			$out .=
				"<div class='grid grid-cols-4 gap-4 mb-2'>" .
				// other setting
				"<div class='col-span-full md:col-span-1 pwcommerce_configure_install_optional_feature'>" .
				$field->render() .
				// small visual for is setting installed
				$isOtherOptionalSettingInstalledMarkup .
				"</div>" .
				// setting description
				"<div class='col-span-full md:col-span-3'>" .
				"<p>{$settingDescription}</p>" .
				$settingNotes .
				"</div>" .
				"</div>" .
				// divider between settings if smaller screen
				"<hr class='md:hidden'>";
		}
		// end loop
		// ---------
		$out .= "</div>";
		// --------

		##########################

		//  NON-ADMIN SHOP ROOT PAGE SETTINGS 1
		$wrapper1 = $this->pwcommerce->getInputfieldWrapper();

		// USE NON-ADMIN (CUSTOM) SHOP ROOT PAGE
		//------------------- pwcommerce_is_use_custom_shop_root_page (getInputfieldCheckbox)
		$isUseCustomShopRootPage = 'pwcommerce_is_use_custom_shop_root_page';
		$checked = $this->isOtherOptionalSettingInstalled($isUseCustomShopRootPage);
		// checkbox options
		$options = [
			'id' => $isUseCustomShopRootPage,
			'name' => $isUseCustomShopRootPage,
			'label' => $this->_('Use Custom Page as Shop Root Page'),
			// 'notes' => $this->_('Install some pages outside admin root.'),
			// 'label2' => $this->_('xxxxx'),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_content_padding_top pwcommerce_override_processwire_inputfield_content_padding_left pwcommerce_is_use_custom_shop_root_page',
			'checked' => empty($checked) ? false : true,
			'value' => 1
		];

		$nonAdminRootPageSettingDescription = $this->_("Allow some PWCommerce pages to live under 'Home' instead of 'Admin' in the page tree. Useful for directly displaying the pages in the frontend. For instance, '/my-shop/products/'.");
		$nonAdminRootPageSettingNotes = "<p class='notes'>" . $this->_('Install some pages outside admin root.') . "</p>";
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		$isInstalled = empty($checked) ? 0 : 1;
		$field->attr([
			'x-ref' => $isUseCustomShopRootPage,
			'data-is-installed' => $isInstalled,
			'data-optional-setting-name' => 'custom_shop_root_page',
			// 'data-feature-label' => $label, // user friendly name to show if adding/removing feature
			'x-on:change' => 'handlePWCommerceOtherOptionalSettingsChange',
		]);
		$wrapper1->add($field);

		// MARKUP 1 FOR NON-ADMIN SHOP ROOT PAGE
		$out .=
			"<div class='grid grid-cols-4 gap-4 mb-2'>" .
			// other setting
			"<div class='col-span-full md:col-span-1 pwcommerce_configure_install_optional_feature'>" .
			$wrapper1->render() .
			"</div>" .
			// setting description
			"<div class='col-span-full md:col-span-3'>" .
			"<p>{$nonAdminRootPageSettingDescription}</p>" .
			$nonAdminRootPageSettingNotes .
			"</div>" .
			"</div>" .
			// divider between settings if smaller screen
			"<hr class='md:hidden'>";

		// ********

		//  NON-ADMIN SHOP ROOT PAGE SETTINGS 2
		$wrapper2 = $this->pwcommerce->getInputfieldWrapper();

		// SELECT NON-ADMIN SHOP ROOT PAGE ID
		// @note: some core and non-core features still installed under admin
		// e.g. 'orders'
		// page list select options
		//------------------- pwcommerce_custom_shop_root_page_id (getInputfieldPageListSelect)
		$value = 0;
		if (!empty($this->installedOtherOptionalSettings['pwcommerce_custom_shop_root_page_id'])) {
			$value = (int) $this->installedOtherOptionalSettings['pwcommerce_custom_shop_root_page_id'];
		}
		$options = [
			'id' => "pwcommerce_custom_shop_root_page_id",
			'name' => "pwcommerce_custom_shop_root_page_id",
			'label' => $this->_('Custom Shop Root Page'),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_header_padding_left pwcommerce_is_use_custom_shop_root_page',
			'value' => $value,
			// -------
			'show_path' => true,
		];

		$field = $this->pwcommerce->getInputfieldPageListSelect($options);
		$field->showIf = "pwcommerce_is_use_custom_shop_root_page=1";

		$wrapper2->add($field);

		// SELECT PAGES TO INSTALL UNDER NON-ADMIN SHOP ROOT PAGE
		//------------------- pwcommerce_custom_shop_root_page_children (getInputfieldCheckboxes)

		$value = [];
		if (!empty($this->installedOtherOptionalSettings['pwcommerce_custom_shop_root_page_children'])) {
			$value = $this->installedOtherOptionalSettings['pwcommerce_custom_shop_root_page_children'];
		}

		$customShopRootPageAllowedChildrenDetails = $this->getCustomShopRootPageAllowedChildrenDetails();
		$checkboxesOptions = $customShopRootPageAllowedChildrenDetails['checkboxes_options'];

		$options = [
			'id' => "pwcommerce_custom_shop_root_page_children",
			'name' => "pwcommerce_custom_shop_root_page_children",
			'label' => $this->_('Parent Pages for Custom Shop Root Page'),
			// 'checkboxes_options' => [
			// 	'products' => $this->_('Products'),
			// 	'product_brands' => $this->_('Product Brands'),
			// 	'product_categories' => $this->_('Product Categories'),
			// 	'product_tags' => $this->_('Product Tags'),
			// 	'product_types' => $this->_('Product Types'),
			// 	// TODO ENABLE THIS?
			// 	// 'customers' => $this->_('Customers'),
			// 	'legal_pages' => $this->_('Legal Pages'),
			// ],
			'checkboxes_options' => $checkboxesOptions,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_header_padding_left pwcommerce_is_use_custom_shop_root_page',
			// 'show_if' => "pwcommerce_custom_shop_root_page_id>0",
			'show_if' => "pwcommerce_is_use_custom_shop_root_page=1",
			'description' => $this->_("Select parent pages to install under custom shop root page."),
			'notes' => $this->_("For instance, an example product could live under '/my-shop/products/example-product/. If not selected, the parent page would live under /admin/pwcommerce/shop/. Note, these require their respective feature to be installed as well."),
			'value' => $value
		];
		$field = $this->pwcommerce->getInputfieldCheckboxes($options);
		// won't work like this
		// $field->attr([
		// 	'x-ref' => $isUseCustomShopRootPage,
		// 	'data-is-installed' => $checked,
		// 	// 'data-feature-label' => $label, // user friendly name to show if adding/removing feature
		// 	'x-on:change' => 'handlePWCommerceOtherOptionalSettingsChange',
		// ]);

		$wrapper2->add($field);

		// SPECIFY HOW PAGES TO INSTALL UNDER NON-ADMIN SHOP ROOT PAGE SHOULD BE MANAGED IN THE PAGE TREE
		//------------------- pwcommerce_custom_shop_root_page_children_page_tree_management (getInputfieldCheckboxes)

		// @note: UPDATE Sunday 31 December 2023 13:49 'default_page_tree_behaviour' NO LONGER IN USE. Doesn't make much sense + need amending page edit GUI for products.
		$radioOptions = [
			'not_visible_in_page_tree' => __('Hide the parent pages and their children in the page tree'),
			'page_tree_with_redirect' => __('Limited page tree actions and redirect to PWCommerce dashboards'),
			// 'default_page_tree_behaviour' => __('Limited page tree actions but without redirect to PWCommerce dashboards'),
		];

		// dynamic notes
		// ------
		// for x-show for 'not_visible_in_page_tree'
		$notesTextHiddenBehaviour = $this->_('Parent pages and children will be hidden in the page tree. Useful if primary aim for Custom Shop Root page is frontend access.');
		$notes = "<span x-show='{$this->xstore}.custom_shop_root_page_children_page_tree_management==`not_visible_in_page_tree`'>" . $notesTextHiddenBehaviour . "</span>";
		// for x-show for 'page_tree_with_redirect'
		$notesTextRedirectBehaviour = $this->_("Parent pages and children will be visible in the page tree but actions will redirect to respective PWCommerce Dashboards. For instance, if 'edit' is clicked for a product, the product will be opened for editing in Products Dashboard. 'Edit', 'Move' and 'Trash' will not be visible on Parent Pages. 'Move' action will not be visible on the children pages of these Parent Pages. 'New' action will not be visible on product pages to prevent direct creation of product variants.");
		$notes .= "<span x-show='{$this->xstore}.custom_shop_root_page_children_page_tree_management==`page_tree_with_redirect`'>" . $notesTextRedirectBehaviour . "</span>";
		// for x-show for 'default_page_tree_behaviour'
		// $notesTextDefaultBehaviour = $this->_('Parent Pages will be subject to default ProcessWire page tree behaviour. For instance, pages will be editable in usual Page Edit form. Note that trashing parent pages will lead to errors in PWCommerce!');
		// $notes .= "<span x-show='{$this->xstore}.custom_shop_root_page_children_page_tree_management==`default_page_tree_behaviour`'>" . $notesTextDefaultBehaviour . "</span>";

		$value = 'not_visible_in_page_tree';
		if (!empty($this->installedOtherOptionalSettings['pwcommerce_custom_shop_root_page_children_page_tree_management'])) {
			$value = $this->installedOtherOptionalSettings['pwcommerce_custom_shop_root_page_children_page_tree_management'];
		}

		// ------

		$options = [
			'id' => "pwcommerce_custom_shop_root_page_children_page_tree_management",
			'name' => 'pwcommerce_custom_shop_root_page_children_page_tree_management',
			'label' => $this->_('Behaviour and Visibility of Parent Pages in Page Tree'),
			'notes' => $notes,
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 50,
			// 'required' => true,
			'classes' => 'pwcommerce_custom_shop_root_page_children_page_tree_management',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_bottom pwcommerce_discounts_radios_wrapper',
			'radio_options' => $radioOptions,
			'show_if' => "pwcommerce_is_use_custom_shop_root_page=1",
			'value' => $value,
		];

		$field = $this->pwcommerce->getInputfieldRadios($options);

		// TODO CONFIRM => ALPINE DOES NOT WORK WITH PROCESSWIRE RADIOS!
		$field->entityEncodeText = false;


		// +++++++++
		// TODO DO WE NEED THIS? IF YES, CHANGE THE VALUE!
		// init value of selected tree management
		$field->wrapAttr('x-init', "setStoreValue(`custom_shop_root_page_children_page_tree_management`,`{$value}`)");

		$wrapper2->add($field);

		// ++++

		// MARKUP 2 FOR NON-ADMIN SHOP ROOT PAGE

		$out .=
			"<div class='mb-2' @pwcommercecustomshoprootpagetreemanagementradiochangenotification.window='handleCustomShopRootPageTreeManagementChange'>" .
			// other setting
			$wrapper2->render() .
			"</div>" .
			// divider between settings if smaller screen
			"<hr class='md:hidden'>";

		// ------
		return $out;
	}

	/**
	 * Get P W Commerce Optional Features List.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeaturesList() {


		// OPTIONAL
		// --------------
		// products
		// - inventory
		// - categories
		// - tags
		// - attributes (needed by product variants)
		// - types
		// - brands
		// - dimensions (needed by properties)
		// - properties (of no use without dimensions?)
		// - gift cards (TODO)
		// downloads
		// settings
		// - payment providers? e.g. invoice only?
		// - legal pages
		// discounts
		// customers
		// - all customers
		// - customer groups

		//------------
		$optionalFeaturesList = [
			// inventory
			'product_inventory' => [
				'feature' => $this->_('Product Inventory'),
				// TODO: better description!
				'description' => $this->_('View and manage product inventory in one place.')
			],
			// categories
			'product_categories' => [
				'feature' => $this->_('Product Categories'),
				'description' => $this->_("Create and manage shop product categories. Categories are useful for grouping similar products, for instance, 'Kitchen', 'Garden', 'Women'.")
			],
			// tags
			'product_tags' => [
				'feature' => $this->_('Product Tags'),
				// TODO: better description!
				'description' => $this->_('Create and manage shop product tags. Useful when you want to group products for frontend searches, similar items, across categories, etc.')
			],
			// attributes
			'product_attributes' => [
				'feature' => $this->_('Product Attributes'),
				'description' => $this->_('Create and manage shop product attributes. Please note that this
							feature is required if you want to use Product Variants feature.')
			],
			// types
			'product_types' => [
				'feature' => $this->_('Product Types'),
				// TODO: better description!?
				'description' => $this->_("Create and manage shop product types. Useful when you want to group products by type, for instance, 'Phones', 'Books', 'Shirts' and so on.")
			],
			// brands
			'product_brands' => [
				'feature' => $this->_('Product Brands'),
				// TODO: better description!?
				'description' => $this->_("Create and manage shop product brands/vendors/manufacturers. Useful when you want to group products by vendor/manufacturer, for instance, 'Adidas', 'Samsung', 'Puma', etc.")
			],
			// properties
			'product_properties' => [
				'feature' => $this->_('Product Properties'),
				// TODO: better description!?
				'description' => $this->_("Create and manage shop product properties. Useful when you want to further describe products based on various properties such as 'Colour', 'Grade', 'Weight', etc. This feature requires 'Product Dimensions'.")
			],
			// dimensions
			'product_dimensions' => [
				'feature' => $this->_('Product Dimensions'),
				// TODO: better description!?
				'description' => $this->_("Create and manage shop product dimensions. Useful when you want to assign physical and non-physical properties to your products. Those properties will need dimensions, for instance, 'Centimetres', 'Kilograms', 'Litres', etc. This feature is required if you select the feature 'Product Properties'.")
			],
			// gift cards (TODO)
			// 'product_gift_cards' => [
			// 	'feature' => $this->_('Gift Cards'),
			// 	// TODO: better description!?
			// 	'description' => $this->_("Create, issue and manage gift cards")
			// ],
			// gift card products (TODO)
			// 'product_gift_card_products' => [
			// 	'feature' => $this->_('Gift Cards'),
			// 	// TODO: better description!?
			// 	'description' => $this->_("Create and sell gift card products")
			// ],
			// downloads
			'downloads' => [
				'feature' => $this->_('Downloads'),
				// TODO: better description!
				'description' => $this->_("Upload, view and manage product downloads. The downloads can be digital products or files that accompany a product such as 'tickets', 'manuals', and so on.")
			],
			// discounts
			'discounts' => [
				'feature' => $this->_('Discounts'),
				// TODO: better description!?
				'description' => $this->_("Create discounts that can be redeemed by customers or applied automatically at checkout.")
			],
			// customers
			'customers' => [
				'feature' => $this->_('Customers'),
				// TODO: better description!?
				'description' => $this->_("Manage shop customers. These can be created programmatically after checkout or manually in the backend. This feature is required if you want to use 'Customer Groups'.")
			],
			// customer groups
			'customer_groups' => [
				'feature' => $this->_('Customer Groups'),
				// TODO: better description!?
				'description' => $this->_("Create and manage customer groups for your customers. This feature requires the feature 'Customers'.")
			],
			// payment providers
			'payment_providers' => [
				'feature' => $this->_('Payment Providers'),
				// TODO: better description!?
				// TODO: REVISIT THIS! SINCE NEED INVOICE AT LEAST! + NEED TO AUTOINSTALL INVOICE!
				'description' => $this->_("Manage shop payment providers/gateways. These include 'PayPal', 'Stripe', 'Invoice', etc. This feature is not useful if you will be collecting payment using other means, for instance over the counter, over the phone, etc.")
			],
			// legal pages
			'legal_pages' => [
				'feature' => $this->_('Legal Pages'),
				// TODO: better description!
				'description' => $this->_("Create and manage legal pages for your shop. These include 'Shipping Policy', 'Returns Policy', 'Refund Policy', etc.")
			],
		];
		// --------
		return $optionalFeaturesList;
	}

	/**
	 * Get P W Commerce Other Optional Settings List.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOtherOptionalSettingsList() {

		// OTHER OPTIONAL SETTINGS
		// 1. 'categories' titled 'collections'
		// 2.  'pwcommerce_custom_shop_root_page_id'
		// 3. 'pwcommerce_installed_pages_outside_admin_root'
		//------------
		$categoryAsCollectionsSetting = 'categories_are_collections';
		$idNameCategoryAsCollectionsSetting = 'pwcommerce_is_category_collection';
		$isInstalled = $this->isOtherOptionalSettingInstalled($idNameCategoryAsCollectionsSetting);
		$isInstalled = empty($isInstalled) ? 0 : 1;
		$optionalFeaturesList = [
			// categories as collections
			'pwcommerce_is_category_collection' => [
				'setting' => $this->_('Categories are Collections'),
				// TODO: better description!
				'description' => $this->_("Refer to 'Categories' as 'Collections'."),
				'notes' => $this->_("Only applicable if categories feature is installed."),
				'attrs' => [
					'x-ref' => $idNameCategoryAsCollectionsSetting,
					'data-is-installed' => $isInstalled,
					'data-optional-setting-name' => $categoryAsCollectionsSetting,
					// 'data-feature-label' => $label, // user friendly name to show if adding/removing feature
					'x-on:change' => 'handlePWCommerceOtherOptionalSettingsChange',
				]
			],
			// @note: moved to $this->getPWCommerceOtherOptionalSettingsListMarkup() so can use in InputfieldWrapper
			// non-admin shop root page ID
			// @note: some core and non-core features still installed under admin
			// e.g. 'orders'
			// 'pwcommerce_custom_shop_root_page_id' => [
			// 	'setting' => $this->_('Use Non-admin Page Shop Root Page'),
			// 	'description' => $this->_("Allow some PWCommerce pages to live under 'Home' instead of 'Admin' in the page tree. Useful for directly displaying the pages in the frontend.")
			// ],

		];
		// --------
		return $optionalFeaturesList;
	}

	/**
	 * Get Custom Shop Root Page Allowed Children Details.
	 *
	 * @return mixed
	 */
	private function getCustomShopRootPageAllowedChildrenDetails() {
		$customShopRootPageAllowedChildrenDetails = [
			// -----------
			'checkboxes_options' => [
				'products' => $this->_('Products'),
				'product_brands' => $this->_('Product Brands'),
				'product_categories' => $this->_('Product Categories'),
				'product_tags' => $this->_('Product Tags'),
				'product_types' => $this->_('Product Types'),
				// TODO ENABLE THIS?
				// 'customers' => $this->_('Customers'),
				'legal_pages' => $this->_('Legal Pages'),
			],
		];
		// ------
		return $customShopRootPageAllowedChildrenDetails;
	}


	/**
	 * Get P W Commerce Optional Feature Checkbox.
	 *
	 * @param array $options
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeatureCheckbox(array $options) {

		$feature = $options['feature'];
		$label = $options['label'];
		$checked = $options['checked'];
		$isRequiresHandler = $options['is_requires_handler'];
		$isADependency = $options['is_a_dependency'];
		$isAOneWayDependency = $options['is_a_one_way_dependency'];
		$isAOneWayDependent = $options['is_a_one_way_dependent'];
		$isInstalled = empty($options['is_installed']) ? 0 : 1;
		// -------------
		// checkbox options
		$options = [
			'id' => "pwcommerce_configure_install_optional_feature_{$feature}",
			'name' => "pwcommerce_configure_install_optional_feature[]",
			'label' => $label, // @note: empty string just to hide label but keeping label2
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'mr-1',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'checked' => empty($checked) ? false : true,
			'value' => $feature
		];

		$field = $this->pwcommerce->getInputfieldCheckbox($options);

		// ------------------
		// ALL CHECKBOXES NEED AN X-REF and HANDLER
		// we use it to when monitoring addition or removal of optional install features
		// as well as dependencies @see below
		$field->attr([
			'x-ref' => $feature,
			'data-is-installed' => $isInstalled,
			// 'data-feature-label' => $label, // user friendly name to show if adding/removing feature
			'x-on:change' => 'handlePWCommerceOptionalFeatureChange',
		]);
		// -------------
		// FOR DEPENDENT AND DEPENDENCIES
		if (!empty($isRequiresHandler)) {
			$field->attr([
				'data-is-dependent' => 1
			]);
		}
		// ------------
		if (!empty($isADependency)) {
			// optional feature is a dependency, so we disable it
			$field->attr([
				'disabled' => true
			]);
		}
		// ------------
		if (!empty($isAOneWayDependency)) {
			// optional feature is a one-way dependency, so we listen to changes on it
			// for instance, customer groups needs customers but not the other way round
			$field->attr([
				'data-is-one-way-dependency' => 1
			]);
		}
		// ------------
		if (!empty($isAOneWayDependent)) {
			// TODO NEED TO DISABLE THIS ON LOAD IF APPLICABLE?
			// optional feature is a one-way dependent, so we listen to changes on its dependency
			// for instance, customer groups needs customers but not the other way round
			// GET ALL ONE-WAY DEPENDENCIES so we can get the one-way dependent for this one-way dependency
			// array with $key => $value pairs of dependent => one-way-dependency
			$oneWayDependencies = $this->getPWCommerceOptionalFeaturesOneWayDependencies();
			$featureDependent = null;
			foreach ($oneWayDependencies as $oneWayDependenent => $oneWayDependency) {
				if ($oneWayDependency === $feature) {
					$featureDependent = $oneWayDependenent;
					break;
				}
			}

			if ($featureDependent) {
				$isInstalledFeatureDependent = $this->isOptionalFeatureInstalledAdminRenderCheck($featureDependent);
				if (empty($isInstalledFeatureDependent)) {
					$field->attr([
						'disabled' => true
					]);
				}
			}
		}

		// ------------
		return $field->render();
	}

	/**
	 * Get P W Commerce Optional Features Dependencies.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeaturesDependencies() {
		$dependencies =
			[
				// @note: 'key' -> dependent on 'value'
				'product_properties' => 'product_dimensions',
				// 'customer_groups' => 'customers',
				// 'customers' => 'customer_groups',
			];
		return $dependencies;
	}

	/**
	 * Get P W Commerce Optional Features One Way Dependencies.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeaturesOneWayDependencies() {
		$oneWayDependencies =
			[
				// @note: 'key' -> dependency of 'value'
				// 'customer_groups' => 'customers',
				'customers' => 'customer_groups',
			];
		return $oneWayDependencies;
	}

	/**
	 * Get P W Commerce Optional Features Script Markup.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeaturesScriptMarkup() {
		$data = [
			'configure_install_dependencies' => $this->getPWCommerceConfigureInstallOptionalFeaturesDependenciesJSConfigs(),
			'configure_install_one_way_dependencies' => $this->getPWCommerceConfigureInstallOptionalFeaturesOneWayDependenciesJSConfigs(),
			// TODO: delete if not in use
			// 'installed_optional_features' => $this->getPWCommerceInstalledOptionalFeaturesJSConfigs(),
			'is_second_install_configuration' => $this->isSecondStageInstallConfiguration,
			'optional_features_labels' => $this->getPWCommerceOptionalFeaturesLabelsJSConfigs(),
			'other_optional_setting_labels' => $this->getPWCommerceOtherOptionalSettingsLabelsJSConfigs(),
			'no_install_or_uninstall_text' => $this->_('None')
		];
		$script = "<script>ProcessWire.config.PWCommerceAdminRenderInstaller = " . json_encode($data) . ';</script>';
		return $script;
	}

	/**
	 * Get P W Commerce Installed Optional Features J S Configs.
	 *
	 * @return mixed
	 */
	private function getPWCommerceInstalledOptionalFeaturesJSConfigs() {
		$installedOptionalFeatures = $this->pwcommerce->getPWCommerceInstalledOptionalFeatures($this->configModuleName);
		$optionalFeaturesList = $this->getPWCommerceOptionalFeaturesList();
		$installedOptionalFeaturesForJS = [];
		// create list for JavaScript configs
		foreach ($installedOptionalFeatures as $installedOptionalFeatureName) {
			if (!empty($optionalFeaturesList[$installedOptionalFeatureName]['feature'])) {
				$installedOptionalFeaturesForJS[] = $optionalFeaturesList[$installedOptionalFeatureName]['feature'];
			}
		}
		// ----------
		return $installedOptionalFeaturesForJS;
	}

	/**
	 * Get P W Commerce Optional Features Labels J S Configs.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeaturesLabelsJSConfigs() {
		$optionalFeaturesList = $this->getPWCommerceOptionalFeaturesList();
		$optionalFeaturesLabelsForJS = [];
		// create list for JavaScript configs
		foreach ($optionalFeaturesList as $optionalFeatureName => $optionalFeatureValues) {
			$optionalFeaturesLabelsForJS[$optionalFeatureName] = $optionalFeatureValues['feature'];
		}
		// ----------
		return $optionalFeaturesLabelsForJS;
	}

	/**
	 * Get P W Commerce Other Optional Settings Labels J S Configs.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOtherOptionalSettingsLabelsJSConfigs() {
		$otherOptionalSettingsLabelsForJS = [
			'categories_are_collections' => $this->_('Categories are Collections'),
			'custom_shop_root_page' => $this->_('Custom Shop Root Page'),
			'custom_shop_root_page_parent_pages' => $this->getCustomShopRootPageAllowedChildrenDetails()['checkboxes_options']
		];
		// ----------
		return $otherOptionalSettingsLabelsForJS;
	}

	/**
	 * Get P W Commerce Configure Install Optional Features Dependencies J S Configs.
	 *
	 * @return mixed
	 */
	private function getPWCommerceConfigureInstallOptionalFeaturesDependenciesJSConfigs() {
		return $this->getPWCommerceOptionalFeaturesDependencies();
	}

	/**
	 * Get P W Commerce Configure Install Optional Features One Way Dependencies J S Configs.
	 *
	 * @return mixed
	 */
	private function getPWCommerceConfigureInstallOptionalFeaturesOneWayDependenciesJSConfigs() {
		return $this->getPWCommerceOptionalFeaturesOneWayDependencies();
	}

	/**
	 * Modal for confirm configure install.
	 *
	 * @return mixed
	 */
	private function getModalMarkupForConfirmConfigureInstall() {

		$xstore = $this->xstore;
		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		//--------------
		$header = $this->_("Confirm Configure Install");
		// -------
		$info1 = $this->_('This action will install the required features as well as any optional features you have selected. This might take a few seconds depending on your server settings. Please be patient and wait for the installer to finish.');
		$info2 = $this->_('Please confirm this action.');
		// ------
		$body =
			"<div id='pwcommerce_confirm_configure_install' :class='{hidden:{$xstore}.is_show_run_installer_spinner}'>" .
			"<p >" . $info1 . "</p>" .
			$this->getModifyInstallFeaturesToActionMarkup() .
			"<p id='pwcommerce_modify_install_confirm_action'>" . $info2 . "</p>" .
			"</div>";
		// append spinner to show configure install is running
		$body .= $this->getConfigureInstallIsRunningSpinner();

		// $ajaxPostURL = $this->wire('config')->urls->admin . PwCommerce::PWCOMMERCE_SHOP_PAGE_IN_ADMIN_NAME . '/ajax/';
		$applyButtonOptions = [
			// @note: TODO  not using ajax for now! - we submit the form to reload the page
			// HTMX
			// 'hx-post' => $ajaxPostURL,
			// 'hx-target' => '#pwcommerce_confirm_configure_install',
			'x-on:click' => 'handlePWCommerceApplyRunInstaller',
			'type' => 'submit',
			// disable button when installer is running
			// ':disabled' => "{hidden:{$xstore}.is_show_run_installer_spinner}"
			// hide button when installer is running
			':class' => "{hidden:{$xstore}.is_show_run_installer_spinner}"
		];

		$cancelButtonOptions = [
			'x-on:click' => 'resetConfirmConfigureInstallAndClose',
			// hide button when installer is running
			':class' => "{hidden:{$xstore}.is_show_run_installer_spinner}"
		];

		$applyButton = $this->pwcommerce->getModalActionButton($applyButtonOptions, 'apply');
		$cancelButton = $this->pwcommerce->getModalActionButton($cancelButtonOptions, 'cancel');
		$footer = "<div class='ui-dialog-buttonset'>{$applyButton}{$cancelButton}</div>";
		$xproperty = 'is_confirm_run_configure_install_modal_open';
		$size = 'x-large';

		// wrap content in modal for activating/deactivating configure install
		// modal options
		$options = [
			// $header The modal title pane markup.
			'header' => $header,
			// $body The main content markup.
			'body' => $body,
			// $footer The footer markup.
			'footer' => $footer,
			// $xstore The ALPINEJS STORE with the property that will be modelled to show/hide the modal.
			'xstore' => 'ProcessPWCommerceStore',
			// $xproperty The ALPINEJS PROPERTY that will be modelled to show/hide the modal.
			'xproperty' => $xproperty,
			// $size The size of the modal requested.
			'size' => $size,
		];
		$out = $this->pwcommerce->getModalMarkup($options);

		return $out;
	}

	/**
	 * Get Modify Install Features To Action Markup.
	 *
	 * @return mixed
	 */
	private function getModifyInstallFeaturesToActionMarkup() {
		$out = "";
		// first stage install or new: show markup for additions only
		# NOTE:  features + other settings
		$out .=

			"<div>" .
			"<h4>" . $this->_('Additions') . "</h4>" .
			// features
			"<span>" . $this->_('Features') . ": " .
			"<span x-text='getListOfNewFeaturesToAdd' class='italic'></span>" .
			"</span>" .
			// other settings
			"<span class='mt-3 block'>" . $this->_('Other') . ": " .
			"<span x-text='getListOfNewOtherOptionalSettingsToAdd' class='italic'></span>" .
			"</span>" .
			"</div>";
		if ($this->isSecondStageInstallConfiguration) {
			// if second stage install: also show markup for removals
			$out .=
				"<hr>" .
				"<div class='mt-5'>" .
				"<h4>" . $this->_('Removals') . "</h4>" .
				// features
				"<span>" . $this->_('Features') . ": " .
				"<span x-text='getListOfExistingFeaturesToRemove' class='italic'></span>" .
				"</span>" .
				// other settings
				"<span class='mt-3 block'>" . $this->_('Other') . ": " .
				"<span x-text='getListOfExistingOtherOptionalSettingsToRemove' class='italic'></span>" .
				"</span>" .
				"</div>";
		}
		$out .= "<hr>";
		// -----------
		return $out;
	}

	/**
	 * Modal for confirm complete removal.
	 *
	 * @return mixed
	 */
	private function getModalMarkupForConfirmCompleteRemoval() {

		$xstore = $this->xstore;
		// GET WRAPPER FOR ALL INPUTFIELDS HERE

		//--------------

		$header = $this->_("Confirm Complete Removal");

		// -------
		$info1 = $this->_('This action will completely uninstall PWCommerce pages, templates and fields. This process cannot be reversed. You might want to back up your Shop pages data. Your physical files will not be touched. The Cleaner will also attempt to uninstall all PWCommerce modules. If that does not succeed you will need to do it manually. Please be patient and wait for the uninstaller to finish.');
		$info2 = $this->_('Please confirm that you want to completely uninstall PWCommerce.');
		// ------

		$body =
			"<div id='pwcommerce_confirm_complete_removal' class='mt-10' :class='{hidden:{$xstore}.is_show_run_installer_spinner}'>" .
			"<p >" . $info1 . "</p>" .
			"<p >" . $info2 . "</p>" .
			"</div>";
		// append spinner to show remover installer is running
		$body .= $this->getConfigureInstallIsRunningSpinner();

		// -------
		$applyButtonOptions = [
			'type' => 'submit',
			'x-on:click' => 'handlePWCommerceApplyRunInstaller',
			// hide button when installer is running
			':class' => "{hidden:{$xstore}.is_show_run_installer_spinner}"
		];

		$cancelButtonOptions = [
			'x-on:click' => 'resetConfirmCompleteRemovalAndClose',
			// hide button when installer is running
			':class' => "{hidden:{$xstore}.is_show_run_installer_spinner}"
		];

		$applyButton = $this->pwcommerce->getModalActionButton($applyButtonOptions, 'apply');
		$cancelButton = $this->pwcommerce->getModalActionButton($cancelButtonOptions, 'cancel');
		$footer = "<div class='ui-dialog-buttonset'>{$applyButton}{$cancelButton}</div>";
		// --------
		$xproperty = 'is_confirm_run_complete_removal_modal_open';
		$size = 'large';

		// wrap content in modal for activating/deactivating complete removal
		// modal options
		$options = [
			// $header The modal title pane markup.
			'header' => $header,
			// $body The main content markup.
			'body' => $body,
			// $footer The footer markup.
			'footer' => $footer,
			// $xstore The ALPINEJS STORE with the property that will be modelled to show/hide the modal.
			'xstore' => 'ProcessPWCommerceStore',
			// $xproperty The ALPINEJS PROPERTY that will be modelled to show/hide the modal.
			'xproperty' => $xproperty,
			// $size The size of the modal requested.
			'size' => $size,
		];
		$out = $this->pwcommerce->getModalMarkup($options);

		return $out;
	}

	/**
	 * Get Configure Install Is Running Spinner.
	 *
	 * @return mixed
	 */
	private function getConfigureInstallIsRunningSpinner() {
		$xstore = $this->xstore;
		$out =
			"<div id='pwcommerce_confirm_configure_install' class='mt-10' :class='{hidden:!{$xstore}.is_show_run_installer_spinner}'>" .
			"<span>" . $this->_('Please wait. This might take a minute or two') . "&hellip;</span>" .
			"<span class='fa fa-fw fa-spin fa-spinner'></span>" .
			"</div>";
		return $out;
	}

	/**
	 * Is P W Commerce Optional Feature Checkbox Requires Handler.
	 *
	 * @param mixed $dependent
	 * @return bool
	 */
	private function isPWCommerceOptionalFeatureCheckboxRequiresHandler($dependent) {
		// array with $key => $value pairs of dependent => dependency
		$dependencies = $this->getPWCommerceOptionalFeaturesDependencies();
		return isset($dependencies[$dependent]);
	}
	/**
	 * Is P W Commerce Optional Feature A Dependency.
	 *
	 * @param mixed $dependency
	 * @return bool
	 */
	private function isPWCommerceOptionalFeatureADependency($dependency) {
		// array with $key => $value pairs of dependent => dependency
		$dependencies = $this->getPWCommerceOptionalFeaturesDependencies();
		return in_array($dependency, $dependencies);
	}

	/**
	 * Is P W Commerce Optional Feature A One Way Dependency.
	 *
	 * @param mixed $oneWayDependency
	 * @return bool
	 */
	private function isPWCommerceOptionalFeatureAOneWayDependency($oneWayDependency) {
		// array with $key => $value pairs of dependent => one-way-dependency
		$oneWayDependencies = $this->getPWCommerceOptionalFeaturesOneWayDependencies();
		return !empty($oneWayDependencies[$oneWayDependency]);
	}

	/**
	 * Is P W Commerce Optional Feature A One Way Dependent.
	 *
	 * @param mixed $oneWayDependent
	 * @return bool
	 */
	private function isPWCommerceOptionalFeatureAOneWayDependent($oneWayDependent) {
		// array with $key => $value pairs of dependent => one-way-dependency
		$oneWayDependencies = $this->getPWCommerceOptionalFeaturesOneWayDependencies();
		return in_array($oneWayDependent, $oneWayDependencies);
	}

	/**
	 * Is Optional Feature Installed Admin Render Check.
	 *
	 * @param mixed $feature
	 * @return bool
	 */
	private function isOptionalFeatureInstalledAdminRenderCheck($feature) {
		$isOptionalFeatureInstalled = false;
		if (!empty($this->isSecondStageInstallConfiguration)) {
			$isOptionalFeatureInstalled = in_array($feature, $this->installedOptionalFeatures);
		}
		// -----
		return $isOptionalFeatureInstalled;
	}

	/**
	 * Is Other Optional Setting Installed.
	 *
	 * @param mixed $setting
	 * @return bool
	 */
	private function isOtherOptionalSettingInstalled($setting) {
		$isOtherOptionalSettingInstalled = false;
		if (!empty($this->isSecondStageInstallConfiguration)) {
			$isOtherOptionalSettingInstalled = !empty($this->installedOtherOptionalSettings[$setting]);
		}
		// -----
		return $isOtherOptionalSettingInstalled;
	}
}
