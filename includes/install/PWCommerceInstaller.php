<?php

namespace ProcessWire;

/**
 * PWCommerce: Installer.
 *
 * Installer for PWCommerce. Used for configuring PWCommerce install including cleanup before the module is uninstalled.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceInstaller for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceInstaller extends WireData {




	private $options;
	// name of the module whose configurations we will be modifying after install, i.e. ProcessPWCommerce
	private $configModuleName;
	private $shopProcessPWCommercePageID;
	// the ID of the shopAdminPWCommerceRootPage is the ID of the single child page of the 'admin page' of ProcessPWCommerce ($shopProcessPWCommercePageID)
	// its name is 'pwcommerce' and also uses template 'pwcommerce'
	// it is the parent of all other sections parent pages, i.e. products, orders, settings, categories, etc
	// @see ProcessPwCommerce::install for reasons why we need this page.
	private $shopAdminPWCommerceRootPageID;
	private $actionInput;
	// ------------
	private $optionalFeaturesToInstall;
	private $templatesToInstall;
	private $fieldsToInstall;
	private $pagesToInstall;
	// -------
	// MODIFY INSTALL
	// is this an install modification?
	private $isSecondStageInstallConfiguration = false;
	// were new optional features added?
	private $isAddedNewOptionalFeatures;
	// incoming new optional features to add to install
	// TODO: INSTALL THIS PROP IF NOT IN USE
	private $addedNewOptionalFeatures;
	// were existing optional features removed?
	private $isRemovedExistingOptionalFeatures;
	// existing optional features to remove from install
	private $removedOptionalFeatures = [];
	// for names of  templates of removed optional features that will need to be uninstalled
	private $removedOptionalFeaturesTemplatesToUninstall;
	// for names of fields of removed optional features that will need to be uninstalled
	private $removedOptionalFeaturesFieldsToUninstall;
	// unchanged optional features: these remain in install
	private $unchangedOptionalFeatures = [];
	// ================
	// ONE WAY DEPENDENCIES
	private $addedOneWayDependencyFields = [];
	private $removedOneWayDependencyFields = [];
	// ================
	// OTHER OPTIONAL SETTINGS
	private $isRevertCustomShopRootPageValues; // for 'custom shop root page'
	private $missingFeaturesForOptionalSettings = []; // for 'custom shop root page' children
	private $isErrorCustomShopRootPageValues; // for 'custom shop root page'
	// for use to process 'custom shop root page' selected parent pages in case feature has been removed via $this->partialModificationOfPWCommerceRemovalAction()
	private $removedOptionalFeaturesForCustomShopRootPageParentPages = [];
	// -----------
	private $isSiteMultilingual;
	// -------

	// UNINSTALL
	private $fieldtypesAndInputfieldsToUninstall = [];

	/**
	 *   construct.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __construct($options = null) {
		parent::__construct();
		if (is_array($options)) {
			$this->options = $options;
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

		// TODO THROW ERROR IF NOT SET?!
		if (!empty($options['config_module_name'])) {
			$this->configModuleName = $options['config_module_name'];
		}

		$this->isSiteMultilingual = $this->isSiteMultilingual();

		// GET UTILITIES


	}

	/**
	 * Configure P W Commerce Install Action.
	 *
	 * @param mixed $input
	 * @param mixed $status
	 * @return mixed
	 */
	public function configurePWCommerceInstallAction($input, $status) {

		// SET VARIABLES
		// @note: just for convenience TODO: ok?
		$this->actionInput = $input;
		// init optionalFeaturesToInstall
		$this->optionalFeaturesToInstall = [];
		// CHECK AND SET IF IN SECOND STAGE INSTALL, i.e., modifying existing install
		if ($status === PwCommerce::PWCOMMERCE_SECOND_STAGE_INSTALL_CONFIGURATION_STATUS) {
			// MODIFY A PREVIOUS PWCOMMERCE CONFIGURATION
			$this->isSecondStageInstallConfiguration = true;
		}

		### PREPARE FOR CONFIGURING INSTALLATION ###
		// CONFIGURE OPTIONAL FEATURES
		// @note: sets $this->optionalFeaturesToInstall
		$this->processOptionalFeatures();
		// --------------------------
		// PREPARE TEMPLATES
		// prepare both required and optional templates for installation
		// we prepare templates first in order to determine the fields to install
		// @note: sets $this->templatesToInstall
		$this->prepareTemplates();
		// --------------------------
		// PREPARE FIELDS
		// prepare fields to install based on templates that will be installed
		// the templates specify the fields they will need at the KEY 'fieldgroupFields'
		// @note: sets $this->fieldsToInstall
		$this->prepareFields();
		// --------------------------
		// PREPARE PAGES
		// prepare pages to install based on templates that will be installed
		// @note: sets $this->pagesToInstall
		$this->preparePages();
		// --------------------------

		// RUN INSTALLER!
		$this->runInstaller();

		// ============

		$notice = $this->_('PWCommerce Shop configured successfully.');
		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO? check first?

		];

		//-------
		return $result;
	}

	#########################################

	## PWCOMMERCE GET RAW CONFIGS FOR  TEMPLATES, FIELDS AND PAGES ##

	/**
	 * Get P W Commerce Templates Data.
	 *
	 * @return mixed
	 */
	private function getPWCommerceTemplatesData() {
		$templatesJSON = file_get_contents(__DIR__ . "/install_data/templates_data.json");
		return json_decode($templatesJSON, true);
	}

	/**
	 * Get P W Commerce Template Data By Name.
	 *
	 * @param mixed $templateName
	 * @return mixed
	 */
	private function getPWCommerceTemplateDataByName($templateName) {
		$templateData = null;
		if (empty($templateName))
			return $templateData;
		// ------------
		$pwcommerceTemplates = $this->getPWCommerceTemplatesData();
		if (!empty($pwcommerceTemplates[$templateName])) {
			$templateData = $pwcommerceTemplates[$templateName];
		}
		return $templateData;
	}

	/**
	 * Get P W Commerce Fields Data.
	 *
	 * @return mixed
	 */
	private function getPWCommerceFieldsData() {
		$fieldsJSON = file_get_contents(__DIR__ . "/install_data/fields_data.json");
		return json_decode($fieldsJSON, true);
	}

	/**
	 * Get P W Commerce Field Data By Name.
	 *
	 * @param mixed $fieldName
	 * @return mixed
	 */
	private function getPWCommerceFieldDataByName($fieldName) {
		$fieldData = null;
		if (empty($fieldName))
			return $fieldData;
		// ------------
		$pwcommerceFields = $this->getPWCommerceFieldsData();
		if (!empty($pwcommerceFields[$fieldName])) {
			$fieldData = $pwcommerceFields[$fieldName];
		}
		return $fieldData;
	}

	/**
	 * Get P W Commerce Pages Data.
	 *
	 * @return mixed
	 */
	private function getPWCommercePagesData() {
		$pagesJSON = file_get_contents(__DIR__ . "/install_data/pages_data.json");
		return json_decode($pagesJSON, true);
	}

	#########################################

	## PWCOMMERCE PRE-PROCESS TEMPLATES, FIELDS AND PAGES ##

	/**
	 * Prepare Templates.
	 *
	 * @return mixed
	 */
	private function prepareTemplates() {
		// required templates only -> @note: with the templates data from JSON converted to an array
		$requiredTemplates = $this->getPWCommerceRequiredTemplates();
		// update with pre-processed product template
		$requiredTemplates = $this->specialPreProcessProductTemplate($requiredTemplates);

		// --------------------------------
		// optional templates only @note: NON-FILTERED TO REMOVE UNSELECTED OPTIONS!
		// @note: here key='template-name' and value='template-settings'
		$optionalTemplatesUnfiltered = $this->getPWCommerceOptionalTemplates();
		// all optional features in order to get the names of the templates
		// these template names are the KEYS in $optionalTemplatesUnfiltered
		$allOptionalFeaturesTemplates = $this->getPWCommerceOptionalFeatures();
		// -------
		$optionalTemplatesToInstall = [];
		// filter out NON-SELECTED optional templates
		if (!empty($this->optionalFeaturesToInstall)) {
			foreach ($this->optionalFeaturesToInstall as $optionalFeature) {
				// TODO: SINCE SANITIZED USING SANITIZER OPTION, THESE MUST EXIST BUT NO HARM IN CONFIRMIING!
				// ADD OPTIONAL TEMPLATES
				/** @var array $templatesNamesForThisOptionalFeature */
				$templatesNamesForThisOptionalFeature = $allOptionalFeaturesTemplates[$optionalFeature];
				// ------
				$optionalTemplates = array_intersect_key($optionalTemplatesUnfiltered, array_flip($templatesNamesForThisOptionalFeature));
				// ------
				$optionalTemplatesToInstall = array_merge($optionalTemplatesToInstall, $optionalTemplates);
			}
		}

		// SET FINAL TEMPLATES TO INSTALL
		if (empty($this->isSecondStageInstallConfiguration)) {
			// IF FIRST TIME CONFIGURE
			// combine required and optional templates to install
			$templatesToInstall = $requiredTemplates + $optionalTemplatesToInstall;
		} else {
			// IF MODIFY CONFIG
			// else, templates to install are only optional ones (if changed)
			$templatesToInstall = $optionalTemplatesToInstall;
			// in case we are installing attributes though, we also need to install variants template
			if (in_array('product_attributes', $this->optionalFeaturesToInstall)) {
				// ----------
				// add variants' template
				// @note: we get it from the $requiredTemplates templates above as this has already been pre-processed
				// e.g., 'pwcommerce_downloads' removed from variant templates if not in use
				$templatesToInstall['pwcommerce-product-variant'] = $requiredTemplates['pwcommerce-product-variant'];
			}
		}

		// save for use later
		$this->templatesToInstall = $templatesToInstall;
	}

	/**
	 * Special Pre Process Product Template.
	 *
	 * @param mixed $requiredTemplates
	 * @return mixed
	 */
	private function specialPreProcessProductTemplate($requiredTemplates) {
		//
		// - Here we pre-process pwcommerce-product template to amend its 'fieldgroupFields' and 'fieldgroupContexts'
		// depending on whether the related product optional features are getting installed
		// These apply to:
		// 1. attributes
		// 2. properties
		// -------
		// 3. classification
		// a. categories
		// b. tags
		// c. brand
		// d. type
		// -------
		// 4. downloads
		//

		// $this->optionalFeaturesToInstall

		// GET THE PRODUCT TEMPLATE SETTINGS
		$productTemplate = $requiredTemplates['pwcommerce-product'];
		// TODO DELETE IF NOT IN USE!
		$productTemplateFieldgroupFields = $productTemplate['fieldgroupFields'];
		$productTemplateFieldgroupContexts = $productTemplate['fieldgroupContexts'];

		// CARRY OUT CHECKS
		// 1. attributes
		if (!in_array('product_attributes', $this->optionalFeaturesToInstall)) {
			## ATTTRIBUTES FEATURE NOT GETTING INSTALLED ##
			// this means product variants will not be available
			// -------------------------------
			// unset 'pwcommerce-product-variant' as template will not be needed TODO: will also need to check in code for product variants!
			unset($requiredTemplates['pwcommerce-product-variant']);
			// --------------
			// AMEND FIELDGROUPFIELDS + FIELDGROUPCONTEXTS with respect to ATTRIBUTES
			$productTemplate = $this->removalPreProcessProductTemplateInRelationToAttributes($productTemplate);
		}

		// 2. properties
		if (!in_array('product_properties', $this->optionalFeaturesToInstall)) {
			## PROPERTIES FEATURE NOT GETTING INSTALLED ##
			// this means product properties will not be available
			// --------------
			// AMEND FIELDGROUPFIELDS + FIELDGROUPCONTEXTS with respect to PROPERTIES
			$productTemplate = $this->removalPreProcessProductTemplateInRelationToProperties($productTemplate);
		}

		// 3. classification (categories, tags, brand, type)
		if (empty($this->isInstallProductClassificationFeature())) {
			## NONE OF CLASSIFICATION FEATURES IS GETTING INSTALLED ##
			// this means product classification will not be available
			// --------------
			// AMEND FIELDGROUPFIELDS + FIELDGROUPCONTEXTS with respect to CLASSIFICATION
			$productTemplate = $this->removalPreProcessProductTemplateInRelationToClassification($productTemplate);
		}

		// 4. downloads
		if (!in_array('downloads', $this->optionalFeaturesToInstall)) {
			## DOWNLOADS FEATURE NOT GETTING INSTALLED ##
			// this means product downloads will not be available in pwcommerce-product and pwcommerce-product-variant
			// --------------
			// AMEND FIELDGROUPFIELDS + FIELDGROUPCONTEXTS with respect to DOWNLOADS
			$productTemplate = $this->removalPreProcessProductTemplateInRelationToDownloads($productTemplate);
			// also, amend 'pwcommerce-product-variant' if applicable
			if (!empty($requiredTemplates['pwcommerce-product-variant'])) {
				$productVariantTemplate = $requiredTemplates['pwcommerce-product-variant'];
				$productVariantTemplate = $this->modifyProductVariantTemplateInRelationToDownloads($productVariantTemplate);
				$requiredTemplates['pwcommerce-product-variant'] = $productVariantTemplate;
			}
		}

		// EXTRA FOR PRODUCT VARIANTS
		// if installing ATTRIBUTES BUT DOWNLOADS NOT GETTING INSTALLED OR NOT ALREADY INSTALLED
		// THEN: amend 'pwcommerce-product-variant'
		if (in_array('product_attributes', $this->optionalFeaturesToInstall) && empty($this->isDownloadsInUse())) {
			$productVariantTemplate = $requiredTemplates['pwcommerce-product-variant'];
			$productVariantTemplate = $this->modifyProductVariantTemplateInRelationToAttributes($productVariantTemplate);
			$requiredTemplates['pwcommerce-product-variant'] = $productVariantTemplate;
		}

		// ##########
		// FINAL PRODUCT TEMPLATE
		// FINAL REQUIRED TEMPLATES
		$requiredTemplates['pwcommerce-product'] = $productTemplate;

		// ==================
		return $requiredTemplates;
	}

	/**
	 * Removal Pre Process Product Template In Relation To Attributes.
	 *
	 * @param mixed $productTemplate
	 * @return mixed
	 */
	private function removalPreProcessProductTemplateInRelationToAttributes($productTemplate) {
		$relatedFields = ['pwcommerce_product_attributes', 'pwcommerce_variants_fieldset', 'pwcommerce_runtime_markup', 'pwcommerce_variants_fieldset_END'];
		$relatedContexts = ['pwcommerce_product_attributes', 'pwcommerce_variants_fieldset', 'pwcommerce_runtime_markup', 'pwcommerce_variants_fieldset_END'];
		$productTemplate = $this->removalPreProcessProductTemplateFieldgroupFieldsAndFieldgroupContexts($productTemplate, $relatedFields, $relatedContexts);
		// ----
		return $productTemplate;
	}

	/**
	 * Modify Product Variant Template In Relation To Attributes.
	 *
	 * @param mixed $productVariantTemplate
	 * @return mixed
	 */
	private function modifyProductVariantTemplateInRelationToAttributes($productVariantTemplate) {
		$relatedFields = ['pwcommerce_downloads'];
		$relatedContexts = ['pwcommerce_downloads'];
		$productVariantTemplate = $this->removalPreProcessProductTemplateFieldgroupFieldsAndFieldgroupContexts($productVariantTemplate, $relatedFields, $relatedContexts);
		// ----
		return $productVariantTemplate;
	}

	/**
	 * Is Downloads In Use.
	 *
	 * @return bool
	 */
	private function isDownloadsInUse() {
		$installedOptionalFeatures = $this->pwcommerce->getPWCommerceInstalledOptionalFeatures($this->configModuleName);
		$isDownloadsInUse = in_array('downloads', $this->optionalFeaturesToInstall) || in_array('downloads', $installedOptionalFeatures);
		return $isDownloadsInUse;
	}

	/**
	 * Removal Pre Process Product Template In Relation To Properties.
	 *
	 * @param mixed $productTemplate
	 * @return mixed
	 */
	private function removalPreProcessProductTemplateInRelationToProperties($productTemplate) {
		$relatedFields = ['pwcommerce_properties_fieldset_tab', 'pwcommerce_product_properties', 'pwcommerce_properties_fieldset_tab_END'];
		$relatedContexts = ['pwcommerce_properties_fieldset_tab', 'pwcommerce_product_properties', 'pwcommerce_properties_fieldset_tab_END'];
		$productTemplate = $this->removalPreProcessProductTemplateFieldgroupFieldsAndFieldgroupContexts($productTemplate, $relatedFields, $relatedContexts);
		// ----
		return $productTemplate;
	}

	/**
	 * Removal Pre Process Product Template In Relation To Classification.
	 *
	 * @param mixed $productTemplate
	 * @return mixed
	 */
	private function removalPreProcessProductTemplateInRelationToClassification($productTemplate) {
		$relatedFields = ['pwcommerce_classification_fieldset_tab', 'pwcommerce_type', 'pwcommerce_brand', 'pwcommerce_categories', 'pwcommerce_tags', 'pwcommerce_classification_fieldset_tab_END',];
		$relatedContexts = ['pwcommerce_classification_fieldset_tab', 'pwcommerce_type', 'pwcommerce_brand', 'pwcommerce_categories', 'pwcommerce_tags', 'pwcommerce_classification_fieldset_tab_END',];
		$productTemplate = $this->removalPreProcessProductTemplateFieldgroupFieldsAndFieldgroupContexts($productTemplate, $relatedFields, $relatedContexts);
		// ----
		return $productTemplate;
	}

	/**
	 * Removal Pre Process Product Template In Relation To Downloads.
	 *
	 * @param mixed $productTemplate
	 * @return mixed
	 */
	private function removalPreProcessProductTemplateInRelationToDownloads($productTemplate) {
		$relatedFields = ['pwcommerce_downloads'];
		$relatedContexts = ['pwcommerce_downloads'];
		$productTemplate = $this->removalPreProcessProductTemplateFieldgroupFieldsAndFieldgroupContexts($productTemplate, $relatedFields, $relatedContexts);
		// ----
		return $productTemplate;
	}

	/**
	 * Modify Product Variant Template In Relation To Downloads.
	 *
	 * @param mixed $productVariantTemplate
	 * @return mixed
	 */
	private function modifyProductVariantTemplateInRelationToDownloads($productVariantTemplate) {
		$relatedFields = ['pwcommerce_downloads'];
		$relatedContexts = ['pwcommerce_downloads'];
		$productVariantTemplate = $this->removalPreProcessProductTemplateFieldgroupFieldsAndFieldgroupContexts($productVariantTemplate, $relatedFields, $relatedContexts);
		// ----
		return $productVariantTemplate;
	}

	/**
	 * Removal Pre Process Product Template Fieldgroup Fields And Fieldgroup Contexts.
	 *
	 * @param mixed $productTemplate
	 * @param array $relatedFields
	 * @param array $relatedContexts
	 * @return mixed
	 */
	private function removalPreProcessProductTemplateFieldgroupFieldsAndFieldgroupContexts($productTemplate, array $relatedFields, array $relatedContexts) {
		$productTemplateFieldgroupFields = $productTemplate['fieldgroupFields'];
		$productTemplateFieldgroupContexts = $productTemplate['fieldgroupContexts'];
		# >>> AMEND fieldgroupFields <<< #
		// remove $relatedFields FROM fieldgroupFields
		// @note: these are VALUES in fieldgroupFields ARRAY
		// flip the fieldgroupFields to get the template fields names as KEYS
		$productTemplateFieldgroupFieldsFlipped = array_flip($productTemplateFieldgroupFields);
		foreach ($relatedFields as $relatedField) {
			unset($productTemplateFieldgroupFieldsFlipped[$relatedField]);
		}
		// restore key=>values of product fieldgroupFields with $relatedFields items removed
		$productTemplateFieldgroupFields = array_flip($productTemplateFieldgroupFieldsFlipped);

		# >>> AMEND fieldgroupContexts <<< #
		// @note: these are KEYS in fieldgroupContexts ARRAY
		// remove context that will not be needed
		foreach ($relatedContexts as $relatedContext) {
			unset($productTemplateFieldgroupContexts[$relatedContext]);
		}
		// SET AMENDED ARRAYS BACK TO PRODUCT TEMPLATES ARRAY
		$productTemplate['fieldgroupFields'] = $productTemplateFieldgroupFields;
		$productTemplate['fieldgroupContexts'] = $productTemplateFieldgroupContexts;
		// ----
		return $productTemplate;
	}

	/**
	 * Is Install Product Classification Feature.
	 *
	 * @return bool
	 */
	private function isInstallProductClassificationFeature() {
		$classificationFeatures = ['product_categories', 'product_tags', 'product_types', 'product_brands'];
		$productClassificationFeaturesToInstall = array_intersect($classificationFeatures, $this->optionalFeaturesToInstall);
		return !empty($productClassificationFeaturesToInstall);
	}

	// ~~~~~~~~~~~~~

	/**
	 * Prepare Fields.
	 *
	 * @return mixed
	 */
	private function prepareFields() {

		// TODO: FOR FUTURE RELEASE CONSIDER IF ATTRIBUTES NOT ADDED IN FIRST INSTALL BUT ADDED IN MODIFY INSTALL, DO WE ENABLE PRODUCT VARIANTS THEN?

		$templatesToInstall = $this->templatesToInstall;
		// get all the fields!
		/** @var array $allFields */
		$allFields = $this->getPWCommerceFieldsData();
		// ---
		// REQUIRED FIELDS TO IGNORE IF MODIFYING INSTALL
		// @note: these fields are also used by some optional features
		// hence, we should not remove or re-add them during modify install
		$ignoreFields = $this->getPWCommerceSharedRequiredFields();
		// -----
		// GET ALL FIELDGROUPS IN THESE TEMPLATES
		$templatesToInstallFields = array_column($templatesToInstall, 'fieldgroupFields');
		// GET THE UNIQUE FIELDNAMES
		$fieldNames = [];
		foreach ($templatesToInstallFields as $fieldgroupFields) {
			$uniqueFieldNames = array_flip($fieldgroupFields);
			$fieldNames = array_merge($fieldNames, $uniqueFieldNames);
		}
		// REMOVE TITLE FIELD AS EXPECTED TO BE ALREADY INSTALLED
		unset($fieldNames['title']);

		// @note: we use array_keys since values can be identical hence will be overwritten if we flip!
		$fieldNames = array_keys($fieldNames);
		// TODO: IF MODIFY, THEN REMOVE IMAGES, DESCRIPTION AND TITLE from re-create! -> THESE WILL BE IN OPTIONAL FEATURES! but were already installed for required features

		// CREATE FINAL ARRAY OF FIELDS TO INSTALL
		// @note:  we also remove optional product fields that will not be installed IF IN FIRST CONFIG INSTALL
		// TODO: MOVE TO OWN METHOD?
		$fieldsToInstall = [];
		foreach ($fieldNames as $fieldName) {

			// DETERMINE IF IN FIRST OR MODIFY CONFIG INSTALL
			if (empty($this->isSecondStageInstallConfiguration)) {
				// FIRST CONFIG INSTALL
				// CHECK IF THIS IS A PRODUCT OPTIONAL FEATURE FIELD
				if ($this->isPWCommerceProductOptionalField($fieldName)) {
					// CHECK IF THE PRODUCT OPTIONAL FEATURE FIELD IS SELECTED FOR INSTALL
					if (!$this->isPWCommerceProductOptionalFieldSelectedForInstall($fieldName)) {
						// NOT INSTALLING PRODUCT OPTIONAL FEATURE
						// skip its fields
						continue;
					}
				}
				// MULTILINGUAL TEXTAREA?
				// make description field multilingual if site is multilingual else usual textarea
				// if ($fieldName === 'pwcommerce_description' && empty($this->isSiteMultilingual)) {
				//     // TODO: WRONG PLACE? DOESN'T WORK. WE CHECK IN FIELD CREATION DIRECLTY
				//     $allFields[$fieldName]['type'] = 'FieldtypeTextarea';
				// }
			} else {
				// SECOND/MODIFY CONFIG INSTALL
				// IGNORE SOME REQUIRED FIELDS THAT MIGHT ALSO BE IN OPTIONAL FIELDS
				if (in_array($fieldName, $ignoreFields)) {
					continue;
				}
			}

			// ---------
			$fieldsToInstall[$fieldName] = $allFields[$fieldName];
		}

		// ############
		// PRODUCT FIELDS TO ADD IF PRODUCT-OPTIONAL FEATURE IS ADDED
		// in case an product-optional feature has been added
		// but we ignore ones that will already be present since they are also in required fields
		// @note: this was set in processOptionalFeatures()
		// $this->optionalFeaturesToInstall <---- added optional features
		$productOptionalFieldsNames = $this->getPWCommerceOptionalFeaturesProductFieldsDependencies();

		foreach ($this->optionalFeaturesToInstall as $addedOptionalFeature) {
			if (!empty($productOptionalFieldsNames[$addedOptionalFeature])) {
				$addedOptionalFeatureFieldsNames = $productOptionalFieldsNames[$addedOptionalFeature];
				// loop through the product-optional field, adding each field to $fieldsToInstall
				// TODO - CONSIDER REFACTORING IN FUTURE TO ADD TO FIRST LOOP HERE!
				foreach ($addedOptionalFeatureFieldsNames as $fieldName) {
					// we skip required fields that may also be present in optional features
					// we also skip common/shared optional fields that may already be installed
					// TODO NOT SURE IF STILL IN USE IS WORKING HERE?
					if (in_array($fieldName, $ignoreFields) || $this->isOptionalFeatureFieldStillInUse($fieldName)) {
						continue;
					}
					$fieldsToInstall[$fieldName] = $allFields[$fieldName];
				}
			}
		}

		// ############
		// PRODUCT FIELDS TO REMOVE IF PRODUCT-OPTIONAL FEATURE IS REMOVED
		// in case an product-optional feature has been removed
		// @note: this was set in processOptionalFeatures()
		// $this->removedOptionalFeatures <---- removed optional features
		// ALSO PROCESS OTHER REMOVAL OF FIELDS OF OTHER NON-PRODUCT RELATED FEATURES
		$productOptionalFieldsNames = $this->getPWCommerceOptionalFeaturesProductFieldsDependencies();

		// to track special cases such as 'classification' feature whereby we can be installing and uninstalling a feature simultaneously, e.g. installing 'categories' whilst removing 'tags'
		$fieldsUnsetFromRemoval = [];
		# ++++++++++++
		// to track both product-related and non-product related field namdes for removal
		$removedOptionalFeaturesFieldsToUninstall = [];

		// -------------------
		$templates = $this->wire('templates');
		$fieldgroups = $this->wire('fieldgroups');
		$optionalFeatures = $this->getPWCommerceOptionalFeatures();
		$removedOptionalFeaturesTemplates = array_intersect_key($optionalFeatures, array_flip($this->removedOptionalFeatures));

		foreach ($this->removedOptionalFeatures as $removedOptionalFeature) {

			if (!empty($productOptionalFieldsNames[$removedOptionalFeature])) {
				$removedOptionalFeatureFields = $productOptionalFieldsNames[$removedOptionalFeature];
				// $this->unchangedOptionalFeatures
				// CHECK IF OPTIONAL FEATURE FIELD IS STILL IN USE BY ANOTHER OPTIONAL FEATURE
				// OR IF IS SET TO BE INSTALLED BY AN INCOMING FEATURE
				// @note: for now, this is just for product-optional features
				foreach ($removedOptionalFeatureFields as $key => $fieldName) {
					$isOptionalFeatureFieldStillInUse = $this->isOptionalFeatureFieldStillInUse($fieldName);
					// if ($isOptionalFeatureFieldStillInUse) {
					// TODO THIS ISSET DOES NOT SEEM TO WORK!
					if ($isOptionalFeatureFieldStillInUse || isset($fieldsToInstall[$fieldName])) {
						$fieldsUnsetFromRemoval[$fieldName] = $fieldName;
						unset($removedOptionalFeatureFields[$key]);
					}
				}

				$removedOptionalFeaturesFieldsToUninstall = array_merge($removedOptionalFeaturesFieldsToUninstall, $removedOptionalFeatureFields);
			} else {
				// TODO GET THEIR TEMPLATE THEN GET THE NAMES OF THE FIELDS IN THE TEMPLATE THEN ADD TO $removedOptionalFeaturesFieldsToUninstall;
				$removedOptionalFeatureTemplatesNames = $removedOptionalFeaturesTemplates[$removedOptionalFeature];

				if (!empty($removedOptionalFeatureTemplatesNames)) {
					$removedOptionalFeatureTemplatesSelector = implode("|", $removedOptionalFeatureTemplatesNames);
					$removedOptionalFeatureTemplates = $templates->find("name={$removedOptionalFeatureTemplatesSelector}");
					// ----------
					foreach ($removedOptionalFeatureTemplates as $removedOptionalFeatureTemplate) {
						$fieldgroup = $removedOptionalFeatureTemplate->fieldgroup;
						$removedOptionalFeatureFieldsNames = $fieldgroups->getFieldNames($fieldgroup);
						$removedOptionalFeaturesFieldsToUninstall = array_merge($removedOptionalFeaturesFieldsToUninstall, $removedOptionalFeatureFieldsNames);
					}
				}
			}
		}
		// end foreach ($this->removedOptionalFeatures as $removedOptionalFeature)

		// REMOVE FIELDS TO IGNORE FROM REMOVAL LIST
		$removedOptionalFeaturesFieldsToUninstall = array_diff($removedOptionalFeaturesFieldsToUninstall, $ignoreFields);

		// --------------
		// @note: CHECK NOT TO INSTALL FIELDS ALREADY INSTALLED
		// these were unset from removal above and would include classification fieldsets
		$fieldsToInstall = array_diff_key($fieldsToInstall, $fieldsUnsetFromRemoval);

		// FIELDS TO INSTALL: save for use later
		$this->fieldsToInstall = $fieldsToInstall;
		// FIELDS TO REMOVE/UNINSTALL: save for use later
		$this->removedOptionalFeaturesFieldsToUninstall = $removedOptionalFeaturesFieldsToUninstall;

		########## SPECIAL CUSTOMER-RELATED OPTIONAL FEATURES POST-INSTALL OPERATIONS #########
		// @note: this will amend either of $this->fieldsToInstall OR $this->removedOptionalFeaturesFieldsToUninstall
		$this->processOneWayDependencyOptionalFeatures();

		# ++++++++++++++++++++++++++++++

	}

	/**
	 * Is Optional Feature Field Still In Use.
	 *
	 * @param mixed $fieldName
	 * @return bool
	 */
	private function isOptionalFeatureFieldStillInUse($fieldName) {
		// check if shared optional features fields that are due to be removed are still in use
		// e.g. pwcommerce_classification_fieldset_tab
		$isOptionalFeatureFieldStillInUse = false;
		$productOptionalFieldsNames = $this->getPWCommerceOptionalFeaturesProductFieldsDependencies();
		foreach ($this->unchangedOptionalFeatures as $optionalFeature) {
			if (!empty($productOptionalFieldsNames[$optionalFeature])) {
				$productOptionalFeatureFields = $productOptionalFieldsNames[$optionalFeature];
				if (in_array($fieldName, $productOptionalFeatureFields)) {
					$isOptionalFeatureFieldStillInUse = true;
					break;
				}
			}
		}
		// ---------
		return $isOptionalFeatureFieldStillInUse;
	}

	/**
	 * Prepare Pages.
	 *
	 * @return mixed
	 */
	private function preparePages() {
		// PREPARE PAGES TO INSTALL
		// get all pages!
		/** @var array $allPages */
		$allPages = $this->getPWCommercePagesData();
		// get the required pages!
		/** @var array $requiredPages */
		$requiredPages = $this->getPWCommerceRequiredPages();
		// get the optional pages keys!
		/** @var array $optionalPages */
		// TODO DELETE IF NOT IN USE
		// $optionalPages = $this->getPWCommerceOptionalPages();
		// ========

		// GET REQUIRED PAGES TO INSTALL
		$requiredPagesToInstall = array_intersect_key($allPages, $requiredPages);
		//
		// GET OPTIONAL PAGES TO INSTALL
		// @note: we flip $this->optionalFeaturesToInstall to get features as keys
		$optionalPagesToInstall = array_intersect_key($allPages, array_flip($this->optionalFeaturesToInstall));
		// ##################
		// FINAL ALL PAGES NAMES TO INSTALL
		// SET FINAL TEMPLATES TO INSTALL
		if (empty($this->isSecondStageInstallConfiguration)) {
			// combine required and optional pages to install IF FIRST TIME CONFIGURE
			// $pagesToInstall = $requiredPagesToInstall + $optionalPagesToInstall;
			$pagesToInstall = array_merge($requiredPagesToInstall, $optionalPagesToInstall);
		} else {
			// MODIFY CONFIG
			// else, pages to install are only optional ones (if changed)
			$pagesToInstall = $optionalPagesToInstall;
		}
		// ----------------
		//		//		// -----------
		// save for use later
		$this->pagesToInstall = $pagesToInstall;
	}
	#########################################

	## PWCOMMERCE REQUIRED FEATURES, TEMPLATES, FIELDS AND PAGES ##

	/**
	 * Returns array of required features with nested arrays of the templates they need/install.
	 *
	 * @return mixed
	 */
	private function getPWCommerceRequiredFeatures() {
		// TODO ADD TO LIST IF NEEDED!
		return [
			'products' => [
				'pwcommerce-products',
				'pwcommerce-product',
				// @note: dependency is 'product_attributes'
				'pwcommerce-product-variant'
			],
			'orders' => [
				'pwcommerce-orders',
				'pwcommerce-order',
				'pwcommerce-order-line-item'
			],
			'shipping' => [
				'pwcommerce-shipping-zones',
				'pwcommerce-shipping-zone',
				'pwcommerce-shipping-rate'

			],
			'taxes' => [
				'pwcommerce-countries',
				'pwcommerce-country',
				'pwcommerce-country-territory'
			],
			// @note: not shown in config installer. OK?
			'settings' => ['pwcommerce', 'pwcommerce-settings']
		];
	}

	/**
	 * Get P W Commerce Required Templates.
	 *
	 * @return mixed
	 */
	private function getPWCommerceRequiredTemplates() {
		// FIRST GET ALL REQUIRED FEATURES
		$requiredFeatures = $this->getPWCommerceRequiredFeatures();
		// loop to create array of templates 'names'
		$requiredFeaturesTemplatesNames = [];
		foreach ($requiredFeatures as $requiredFeatureTemplates) {
			$requiredFeaturesTemplatesNames = array_merge($requiredFeaturesTemplatesNames, $requiredFeatureTemplates);
		}
		// --------
		// get all the templates!
		$allTemplates = $this->getPWCommerceTemplatesData();
		// get array containing all the items in $allTemplates which have keys that are present in the flipped $requiredFeaturesTemplatesNames.
		// i.e., the required features templates
		$requiredFeaturesTemplates = array_intersect_key($allTemplates, array_flip($requiredFeaturesTemplatesNames));

		// TODO DELETE WHEN DONE
		// FOR NOW WE DO POST INSTALL PROCESSING to set allowed parents and children
		// $testForSort = $requiredFeaturesTemplates;
		// uasort($testForSort, [$this, 'myCallback']);
		// krsort($testForSort, \SORT_NATURAL);

		return $requiredFeaturesTemplates;
	}

	// TODO DELETE IF NOT IN USE
	/**
	 * My Callback.
	 *
	 * @param mixed $a
	 * @param mixed $b
	 * @return mixed
	 */
	public function myCallback($a, $b) {
		$aParentTemplates = $a['parentTemplates'];
		$bParentTemplates = $b['parentTemplates'];

		if (in_array($a['name'], $b['parentTemplates'])) {
			// IF $a is parent of $b
			return -1;
		}
		if (count($a['parentTemplates']) == count($b["parentTemplates"])) {
			return 0;
		}
		// return (count($a['parentTemplates']) < count($b["parentTemplates"])) ? -1 : 1;
		return (count($a['parentTemplates']) < count($b["parentTemplates"])) ? 1 : -1;
	}

	/**
	 * Is Required Template.
	 *
	 * @param mixed $templateName
	 * @return bool
	 */
	private function isRequiredTemplate($templateName) {
		return isset($this->getPWCommerceRequiredTemplates()[$templateName]);
	}

	/**
	 * Names of required fields that might also be in use in optional templates.
	 *
	 * @return mixed
	 */
	private function getPWCommerceSharedRequiredFields() {
		return ['title', 'pwcommerce_description', 'pwcommerce_images', 'pwcommerce_settings', 'pwcommerce_runtime_markup', 'pwcommerce_product_stock', 'pwcommerce_order_customer'];
	}

	/**
	 * Get P W Commerce Required Pages.
	 *
	 * @return mixed
	 */
	private function getPWCommerceRequiredPages() {
		// TODO ADD TO LIST IF NEEDED!
		return [
			'products' => ['title' => 'Products', 'template' => 'pwcommerce-products'],
			'orders' => ['title' => 'Orders', 'template' => 'pwcommerce-orders'],
			'shipping' => ['title' => 'Shipping Zones', 'template' => 'pwcommerce-shipping-zones'],
			'taxes' => ['title' => 'Countries', 'template' => 'pwcommerce-countries'],
			// @note: not shown in config installer. OK?
			'settings' => ['title' => 'Settings', 'template' => 'pwcommerce']
		];
	}

	// ~~~~~~~~~~~~

	## PWCOMMERCE OPTIONAL FEATURES, TEMPLATES, FIELDS AND PAGES ##

	/**
	 * Process Optional Features.
	 *
	 * @return mixed
	 */
	private function processOptionalFeatures() {

		$input = $this->actionInput;
		$sanitizer = $this->wire('sanitizer');
		$optionalFeaturesToInstall = [];
		// for modify install
		$unchangedOptionalFeatures = [];
		// --------------
		$installedOptionalFeatures = $this->pwcommerce->getPWCommerceInstalledOptionalFeatures($this->configModuleName);
		// pwcommerce_configure_install_optional_feature
		$optionalFeatures = $input->pwcommerce_configure_install_optional_feature;
		// no optional features requested - return early
		// TODO: WE NEED TO CHECK REMOVED FEATURES BEFORE THIS!
		// TRACK REMOVED OPTIONAL FEATURES FOR LATER USE
		if (empty($optionalFeatures)) {
			$this->removedOptionalFeatures = array_diff($installedOptionalFeatures, $optionalFeaturesToInstall, $unchangedOptionalFeatures);
			return;
		}

		// loop through and process
		// @note: we only allow these optional features
		// @note: this is a multi-dimensional array; here, we only need the keys!
		$allowedOptionalFeaturesKeys = array_keys($this->getPWCommerceOptionalFeatures());

		// TODO add removed?

		// -------------
		foreach ($optionalFeatures as $optionalFeature) {
			$feature = $sanitizer->option($optionalFeature, $allowedOptionalFeaturesKeys);
			// skip unrecognised optional features
			if (empty($feature))
				continue;

			// >>>>>>>> CHECK IF FIRST CONFIG vs MODIFY INSTALL <<<<<<<<
			if (!empty($this->isSecondStageInstallConfiguration)) {
				// CHECK UNCHANGED VS NEW
				if (in_array($optionalFeature, $installedOptionalFeatures)) {
					// incoming is inside existing: unchanged => add to UNCHANGED array
					$unchangedOptionalFeatures[] = $optionalFeature;
					// TODO IF CONTINUE THEN NO NEED TO TRACK! @UPDATE: YES! WE NEED TO SO THAT WE CAN PROPERLY UPDATE MODULE CONFIGS!
					continue;
				}
			}
			// -----------
			$optionalFeaturesToInstall[] = $feature;
			// ADD DEPENDENCIES
			// @note: these are 'housed' in templates since it is what we process first
			$optionalFeaturesDependencies = $this->getPWCommerceOptionalFeaturesTemplatesDependencies();
			if (isset($optionalFeaturesDependencies[$optionalFeature])) {
				$dependencyFeature = $optionalFeaturesDependencies[$optionalFeature];
				$optionalFeaturesToInstall[] = $dependencyFeature;
				// ------
			}
		}

		// --------

		// TRACK REMOVED OPTIONAL FEATURES FOR LATER USE
		$removedOptionalFeatures = array_diff($installedOptionalFeatures, $optionalFeaturesToInstall, $unchangedOptionalFeatures);
		// REMOVE DIMENSIONS FROM 'REMOVALS' IF PROPERTIES IS NOT BEING REMOVED
		// this is because dimensions is not sent since its checkbox is disabled
		if (in_array('product_properties', $unchangedOptionalFeatures)) {
			// get the KEY of 'product_dimensions' and UNSET it
			$productDimensionKey = array_search('product_dimensions', $removedOptionalFeatures);
			unset($removedOptionalFeatures[$productDimensionKey]);
			// ---
			// also re-instate inside $unchangedOptionalFeatures
			$unchangedOptionalFeatures[] = 'product_dimensions';
		}
		// TODO REVISIT THIS! CUSTOMERS NO LONGER AUTO INSTALLS CUSTOMER GROUPS! BUT CUSTOMER GROUPS IS DEPENDENT ON CUSTOMERS! SO, ALTER THE LOGIC?! TODO @USE -> $this->processOneWayDependencyOptionalFeatures()
		// REMOVE CUSTOMER GROUPS FROM 'REMOVALS' IF CUSTOMERS IS NOT BEING REMOVED
		// this is because customer groups is not sent since its checkbox is disabled
		// if (in_array('customers', $unchangedOptionalFeatures)) {
		// 	// get the KEY of 'customer_groups' and UNSET it
		// 	$customerGroupsKey = array_search('customer_groups', $removedOptionalFeatures);
		// 	unset($removedOptionalFeatures[$customerGroupsKey]);
		// 	// also re-instate inside $unchangedOptionalFeatures
		// 	$unchangedOptionalFeatures[] = 'customer_groups';
		// }

		// ---------
		// SET PROPERTIES for later use
		$this->optionalFeaturesToInstall = $optionalFeaturesToInstall;
		$this->unchangedOptionalFeatures = $unchangedOptionalFeatures;
		$this->removedOptionalFeatures = $removedOptionalFeatures;

		// TODO IF INSTALLING 'CUSTOMER GROUPS', WE NEED TO CALL THIS LAST AFTER THE TEMPLATE 'pwcommerce-customer-group' has been created in order to make it the template_id of selectable pages template name in the page field 'pwcommerce_customer_groups'! -> so maybe around here 'SPECIAL FIELTYPEPAGE-RELATED SETTINGS'???
		// TODO WE SHOULD ALSO PREVENT FIELD CREATION IF INSTALLING CUSTOMERS BUT NOT CUSTOMER GROUPS! @SEE WHERE WE SET $this->fieldsToInstall
		// $this->processOneWayDependencyOptionalFeatures();

		// ----------
	}

	/**
	 * Returns key=>value pairs of feature => templates for optional features.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeatures() {
		// TODO ADD TO LIST IF NEEDED!
		return [
			// inventory
			// @note: these are products, so no other template needed
			// TODO MIGHT REMOVE THIS! THIS IS ABOUT A VIEW NOT FIELDS OR TEMPLATES! OR TODO - SPECIAL HANDLER FOR IT!
			'product_inventory' => [],
			// categories
			'product_categories' => [
				'pwcommerce-categories',
				'pwcommerce-category'
			],
			// tags
			'product_tags' => [
				'pwcommerce-tags',
				'pwcommerce-tag'
			],
			// attributes
			'product_attributes' => [
				'pwcommerce-attributes',
				'pwcommerce-attribute',
				'pwcommerce-attribute-option'
			],
			// types
			'product_types' => [
				'pwcommerce-types',
				'pwcommerce-type'
			],
			// brands
			'product_brands' => [
				'pwcommerce-brands',
				'pwcommerce-brand'
			],
			// properties @note: dependent on 'product_dimensions'
			'product_properties' => [
				'pwcommerce-properties',
				'pwcommerce-property'
			],
			// dimensions @note: dependency of 'product_properties'
			'product_dimensions' => [
				'pwcommerce-dimensions',
				'pwcommerce-dimension'
			],
			// downloads
			'downloads' => [
				'pwcommerce-downloads',
				'pwcommerce-download'

			],
			// discounts
			'discounts' => [
				'pwcommerce-discounts',
				'pwcommerce-discount'

			],
			// customers @note: dependent on 'customer_groups'
			'customers' => [
				'pwcommerce-customers',
				'pwcommerce-customer'

			],
			// customer groups @note: dependency of 'customers'
			'customer_groups' => [
				'pwcommerce-customer-groups',
				'pwcommerce-customer-group'

			],
			// payment providers
			'payment_providers' => [
				'pwcommerce-payment-providers',
				'pwcommerce-payment-provider'

			],
			// legal pages
			'legal_pages' => [
					'pwcommerce-legal-pages',
					'pwcommerce-legal-page'
				],
		];
	}

	/**
	 * Get P W Commerce Optional Templates.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalTemplates() {
		// FIRST GET ALL OPTIONAL FEATURES
		$optionalFeatures = $this->getPWCommerceOptionalFeatures();
		// loop to create array of templates 'names'
		$optionalFeaturesTemplatesNames = [];
		foreach ($optionalFeatures as $optionalFeatureTemplates) {
			$optionalFeaturesTemplatesNames = array_merge($optionalFeaturesTemplatesNames, $optionalFeatureTemplates);
		}
		// --------
		// get all the templates!
		$allTemplates = $this->getPWCommerceTemplatesData();
		// get array containing all the items in $allTemplates which have keys that are present in the flipped $optionalFeaturesTemplatesNames.
		// i.e., the optional features templates
		$optionalFeaturesTemplates = array_intersect_key($allTemplates, array_flip($optionalFeaturesTemplatesNames));
		// -----------
		return $optionalFeaturesTemplates;
	}

	// TODO DO WE NEED SIMILAR FOR CUSTOMER GROUPS AND CUSTOMERS?

	/**
	 * Get P W Commerce Optional Features Templates Dependencies.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeaturesTemplatesDependencies() {
		return [
			'product_properties' => 'product_dimensions'
		];
	}

	/**
	 * Get P W Commerce Optional Template Feature Name.
	 *
	 * @param mixed $templateName
	 * @return mixed
	 */
	private function getPWCommerceOptionalTemplateFeatureName($templateName) {
		$optionalTemplateFeatureName = null;
		// FIRST GET ALL OPTIONAL FEATURES
		$optionalFeatures = $this->getPWCommerceOptionalFeatures();
		foreach ($optionalFeatures as $optionalFeatureName => $optionalFeatureTemplates) {
			if (in_array($templateName, $optionalFeatureTemplates)) {
				// found the template in this optional feature's templates
				$optionalTemplateFeatureName = $optionalFeatureName;
				break;
			}
		}
		// --------
		return $optionalTemplateFeatureName;
	}

	/**
	 * Returns an array of product optional features and the product fields that depend on them.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeaturesProductFieldsDependencies() {
		// TODO ADD TO LIST IF NEEDED!
		return [
			// categories
			'product_categories' => [
				'pwcommerce_categories',
				'pwcommerce_classification_fieldset_tab',
				'pwcommerce_classification_fieldset_tab_END'
			],
			// tags
			'product_tags' => [
				'pwcommerce_tags',
				'pwcommerce_classification_fieldset_tab',
				'pwcommerce_classification_fieldset_tab_END'
			],
			// types
			'product_types' => [
				'pwcommerce_type',
				'pwcommerce_classification_fieldset_tab',
				'pwcommerce_classification_fieldset_tab_END'
			],
			// brands
			'product_brands' => [
				'pwcommerce_brand',
				'pwcommerce_classification_fieldset_tab',
				'pwcommerce_classification_fieldset_tab_END'
			],
			// attributes
			// @note: will also need to consider template pwcommerce-product-variant
			'product_attributes' => [
				'pwcommerce_product_attributes',
				// @note: attribute options is for variants
				'pwcommerce_product_attributes_options',
				'pwcommerce_attribute_options_fieldset',
				'pwcommerce_attribute_options_fieldset_END',
				// -----------
				'pwcommerce_variants_fieldset',
				'pwcommerce_variants_fieldset_END',
				// @note: removing since needed by other non-product fields, e.g. orders
				// 'pwcommerce_runtime_markup'
			],
			// properties @note: dependent on 'product_dimensions'
			'product_properties' => [
				'pwcommerce_product_properties',
				'pwcommerce_properties_fieldset_tab',
				'pwcommerce_properties_fieldset_tab_END'
			],
			// dimensions @note: dependency of 'product_properties'
			'product_dimensions' => [
				'pwcommerce_properties_fieldset_tab',
				'pwcommerce_properties_fieldset_tab_END'
			],
			// downloads
			'downloads' => [
				'pwcommerce_downloads',
				'pwcommerce_file',
				'pwcommerce_download_settings',

			],

		];
	}

	/**
	 * Get P W Commerce Optional Features Product Fields Dependencies To Install.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeaturesProductFieldsDependenciesToInstall() {
		return array_intersect_key($this->getPWCommerceOptionalFeaturesProductFieldsDependencies(), array_flip($this->optionalFeaturesToInstall));
	}

	/**
	 * Is P W Commerce Product Optional Field.
	 *
	 * @param mixed $fieldName
	 * @return bool
	 */
	private function isPWCommerceProductOptionalField($fieldName) {
		$productOptionalFieldsNames = $this->getPWCommerceOptionalFeaturesProductFieldsDependencies();
		$isProductOptionalField = false;
		foreach ($productOptionalFieldsNames as $fieldsNames) {
			if (in_array($fieldName, $fieldsNames)) {
				$isProductOptionalField = true;
				break;
			}
		}
		// ----------
		return $isProductOptionalField;
	}

	/**
	 * Is P W Commerce Product Optional Field Selected For Install.
	 *
	 * @param mixed $fieldName
	 * @return bool
	 */
	private function isPWCommerceProductOptionalFieldSelectedForInstall($fieldName) {
		$productOptionalFieldsNamesToInstall = $this->getPWCommerceOptionalFeaturesProductFieldsDependenciesToInstall();
		$isProductOptionalFieldToInstall = false;
		foreach ($productOptionalFieldsNamesToInstall as $fieldsNames) {
			if (in_array($fieldName, $fieldsNames)) {
				$isProductOptionalFieldToInstall = true;
				break;
			}
		}
		// ----------
		return $isProductOptionalFieldToInstall;
	}

	/**
	 * Get P W Commerce Optional Pages.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalPages() {
		// TODO ADD TO LIST IF NEEDED!
		// TODO DELETE IF NOT IN USE, ELSE USE THE TITLE AND TEMPLATE IN VALUES FORMAT SIMILAR TO REQUIRED PAGES
		return [
			// @note: not applicable
			// 'product_inventory' => null,
			'product_categories' => 'Categories',
			'product_tags' => 'Tags',
			'product_attributes' => 'Attributes',
			'product_types' => 'Types',
			'product_brands' => 'Brands',
			'product_properties' => 'Properties',
			'product_dimensions' => 'Dimensions',
			'downloads' => 'Downloads',
			'payment_providers' => 'Payment Providers',
			'legal_pages' => 'Legal Pages',
		];
	}

	/**
	 * Get P W Commerce Optional Roles.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalRoles() {
		return [
			'customers' => 'pwcommerce-customer'
		];
	}

	/**
	 * Process One Way Dependency Optional Features.
	 *
	 * @return mixed
	 */
	private function processOneWayDependencyOptionalFeatures() {

		// TODO @UPDATE -> MAYBE JUST ALTER $this->fieldsToInstall or $this->removedOptionalFeaturesFieldsToUninstal

		// TODO @UDATE2 -> ABOVE PLUS LOOPING THROUGH ALL ONE WAY DEPENDENCIES

		$oneWayDependenciesData = $this->getPWCommerceOptionalFeaturesOneWayDependenciesData();
		$requiredFeatures = $this->getPWCommerceRequiredFeatures();

		// e.g. 'discounts' => 'orders values'; 'customer_groups' => 'customer' values
		foreach ($oneWayDependenciesData as $dependencyFeature => $dependentValues) {
			$fields = $dependentValues['fields'];

			# PROCESS DEPENDENCY FEATURE
			if (in_array($dependencyFeature, $this->optionalFeaturesToInstall)) {
				// A DEPENDENCY FEATURE IS BEING INSTALLED
				// add related dependent fields
				// ---------
				// CREATE AND ADD dependent FIELD to dependent template
				// ++++++++++++
				foreach ($fields as $templateName => $fieldName) {

					$fieldRawData = $this->getPWCommerceFieldDataByName($fieldName);
					$this->fieldsToInstall[$fieldName] = $fieldRawData;
					// -------
					// also track the field and the template they need to be ADDED to
					$this->addedOneWayDependencyFields[$templateName] = $fieldName;

					// -------
				}

			} else if (in_array($dependencyFeature, $this->removedOptionalFeatures)) {
				// A DEPENDENCY FEATURE IS BEING UNINSTALLED
				// remove related dependent fields
				// ----------
				// REMOVE dependent FIELD FROM dependent template AND DELETE IT
				// ++++++++++++
				foreach ($fields as $templateName => $fieldName) {
					$this->removedOptionalFeaturesFieldsToUninstall[] = $fieldName;
					// -------
					// also track the field and the template they need to be REMOVED from
					$this->removedOneWayDependencyFields[$templateName] = $fieldName;

				}

			}

			# ++++++++++++++++++++

			# PROCESS DEPENDENT FEATURE
			$dependentFeature = $dependentValues['dependent_feature'];
			// this is for cases where the 'dependency feature' is not getting installed or uninstalled.
			// in that case we need to remove the related fields from the 'dependent' templates
			$isInstallingOptionalDependentFeature = in_array($dependentFeature, $this->optionalFeaturesToInstall) && !in_array($dependencyFeature, $this->optionalFeaturesToInstall);
			$isInstallingCoreDependentFeature = isset($requiredFeatures[$dependentFeature]) && empty($this->isSecondStageInstallConfiguration) && !in_array($dependencyFeature, $this->optionalFeaturesToInstall);
			if ($isInstallingOptionalDependentFeature || $isInstallingCoreDependentFeature) {
				// remove dependent feature fields from dependent template
				// e.g., remove 'pwcommerce_customer_groups' from 'pwcommerce-customer'
				foreach ($fields as $fieldName) {
					$this->removedOptionalFeaturesFieldsToUninstall[] = $fieldName;
					unset($this->fieldsToInstall[$fieldName]);
				}
			}

		}

		## ++++++++++++++

		// TODO WE NEED TO HANDLE ADDING NEW FIELDS TO THE AMENDED TEMPLATES!

		// TODO IF INSTALLING 'CUSTOMER GROUPS', WE NEED TO CALL THIS LAST AFTER THE TEMPLATE 'pwcommerce-customer-group' has been created in order to make it the template_id of selectable pages template name in the page field 'pwcommerce_customer_groups'! -> so maybe around here 'SPECIAL FIELTYPEPAGE-RELATED SETTINGS'???

		return;

		// @note: the main work here is to add or remove the field 'pwcommerce_customer_groups' to/from the template 'pwcommerce-customer'

		if (in_array('customer_groups', $this->optionalFeaturesToInstall)) {
			// CREATE AND ADD FIELD 'pwcommerce_customer_groups'
			// GET SCHEMA FROM FIELDS DATA
			// ++++++++++++
			$this->fieldsToInstall[] = 'pwcommerce_customer_groups';
			// -----
			// get all the fields!
			/** @var array $allFields */
			// $allFields = $this->getPWCommerceFieldsData();
			// $fieldData = $allFields['pwcommerce_customer_groups'];
			// $newField = new Field();
			// $newField->setImportData($fieldData);

			// although the setting is under 'template_id' this is a string; the name of the template to set
			// this makes fieldData portable
			// $selectablePagesTemplateName = $fieldData['template_id'];
			// get the template
			// @note: at this point, the template would already have been created
			// $template = $this->wire('templates')->get("name={$selectablePagesTemplateName}");
			// $newField->template_id = $template->id;
			// // --------
			// // save
			// // --------
			// $newField->save();
			// // ++++++++
			// $templates = $this->wire('templates');
			// $customerTemplate = $templates->get('pwcommerce-customer');
			// $customerFieldgroup = $customerTemplate->fieldgroup;
			// $customerFieldgroup->append($newField);
			// $customerFieldgroup->save();
		} else if (in_array('customer_groups', $this->removedOptionalFeatures)) {
			// REMOVE FIELD 'pwcommerce_customer_groups' FROM 'pwcommerce-customer' AND DELETE IT
			// GET SCHEMA FROM FIELDS DATA
			// ++++++++++++
			$this->removedOptionalFeaturesFieldsToUninstall[] = 'pwcommerce_customer_groups';

			// $templates = $this->wire('templates');
			// $customerTemplate = $templates->get('pwcommerce-customer');
			// $customerFieldgroup = $customerTemplate->fieldgroup;
			// // -------
			// $customerGroupsFieldName = 'pwcommerce_customer_groups';
			// $field = $this->wire('fields')->get("name={$customerGroupsFieldName}");
			// // +++++++++
			// if ($customerFieldgroup->hasField($field)) {
			// 	// TODO - CONFIRM USAGE IN CASE MANY PAGES INVOLVED
			// 	// remove field from product fieldgroup
			// 			// 	$customerFieldgroup->remove($field);
			// 	$customerFieldgroup->save();
			// }

		} else {
		}

		if (in_array('customers', $this->optionalFeaturesToInstall) && !in_array('customer_groups', $this->optionalFeaturesToInstall)) {
			// CUSTOMERS GETTING INSTALLED BUT NOT CUSTOMER GROUPS
			// -----
			// TODO NOT SURE WE NEED THIS? BELOW OK?
			// remove 'pwcommerce_customer_groups' from 'pwcommerce-customer'
			$this->removedOptionalFeaturesFieldsToUninstall[] = 'pwcommerce_customer_groups';
			unset($this->fieldsToInstall['pwcommerce_customer_groups']);
		}

		// special for one way dependency

		# TODO: also cater or NOT MODIFIED!

		# CUSTOMER
		// install fresh and install modify
		// if(customer install){
		// // if customer groups install
		// if(customer groups getting installed also){
		// // add pwcommerce_customer_groups
		// }
		// else {
		// // NOT INSTALL CUSTOMER GROUPS
		// // remove pwcommerce_customer_groups
		// }

		// else if (customer uninstall){

		// }

		// # CUSTOMER GROUP
		// // install fresh and install modify
		// if(customer groups install){
		// }
		// else {
		// }

	}

	/**
	 * Get P W Commerce Optional Features One Way Dependencies Data.
	 *
	 * @return mixed
	 */
	private function getPWCommerceOptionalFeaturesOneWayDependenciesData() {
		$oneWayDependenciesData =
			[
				// @note: 'key' -> dependency feature; 'value' -> dependent feature data
				'customer_groups' => [
					'dependent_feature' => 'customers',
					'fields' => [
						'pwcommerce-customer' => 'pwcommerce_customer_groups'
					]
				],
				'discounts' => [
					'dependent_feature' => 'orders',
					'fields' => [
						'pwcommerce-order' => 'pwcommerce_order_discounts',
						'pwcommerce-order-line-item' => 'pwcommerce_order_discounts',
					]
				],
			];
		return $oneWayDependenciesData;
	}

	## PWCOMMERCE RUN INSTALLER ##
	// configure install of features, templates, fields and pages

	// TODO NEED TO DIFFERENTIATE BETWEEN FIRST TIME VS MODIFY INSTALLS!
	/**
	 * Run Installer.
	 *
	 * @return mixed
	 */
	private function runInstaller() {

		// TODO => IF ATTRIBUTES FEATURE NOT INSTALLED, PRODUCT SHOULD NOT HAVE RUNTIME MARKUP; JUST UNSET IT FROM ITS TEMPLATES FIELDGROUPS;
		// TODO: IF REMOVING ATTRIBUTES POST-INSTALL, THEN NEED TO REMOVE USING THE FIELDGROUP WAY.
		// TODO: IF ADDING ATTRIBUTES, TAGS, ETC, POST-INSTALL, THEN NEED TO ADD THE REQUIRED FIELDGROUPS TO PRODUCT AND PRODUCT VARIANTS!
		// TODO: THIS WILL ALSO NEED TO BE INSERTED IN THE RIGHT PLACES! E.G. pwcommerce_tags needs to go inside classification fieldset!

		# PARTIAL INSTALL AND/OR UNINSTALL #
		// only if in second stage configuration (modification)
		if (!empty($this->isSecondStageInstallConfiguration)) {
			# partial uninstall #
			// process OPTIONAL FEATURES REMOVALS IF PRESENT
			if (!empty($this->removedOptionalFeatures)) {
				// IF MODIFYING INSTALL, WE NEED TO MODIFY TEMPLATES, FIELDS AND PAGES
				// could mean adding new, removing existing or BOTH
				// >>>>> REMOVAL OF OPTIONAL TEMPLATES, FIELDS AND PAGES <<<<<
				$this->partialModificationOfPWCommerceRemovalAction();
			}
		}

		// TODO NEED TO CHECK IF MODIFYING INSTALL AS WELL AND HOW THAT AFFECTS BELOW SET VARIABLES, ESPECIALLY WITH RESPECT TO REMOVING TEMPLATES, PAGES AND FIELDS + SKIPPING ALREADY CREATED ONES!

		// CREATE PWCOMMERCE FIELDS
		$this->createPWCommerceFields();
		// CREATE PWCOMMERCE TEMPLATES
		$this->createPWCommerceTemplates();
		// CREATE PWCOMMERCE PAGES
		$this->createPWCommercePages();
		// CREATE CUSTOM TABLES: 'pwcommerce_order_status' , 'pwcommerce_cart', ETC, but only if in first config install
		$this->createPWCommerceCustomTables();

		########## SPECIAL PRODUCT-RELATED OPTIONAL FEATURES POST-INSTALL OPERATIONS #########

		// only if in second stage configuration (modification)
		if (!empty($this->isSecondStageInstallConfiguration)) {
			# partial install #
			// process OPTIONAL FEATURES ADDITIONS IF PRESENT
			if (!empty($this->optionalFeaturesToInstall)) {
				// IF MODIFYING INSTALL, WE NEED TO MODIFY TEMPLATES, FIELDS AND PAGES
				// >>>>> ADDITION OF OPTIONAL TEMPLATES, FIELDS AND PAGES <<<<<
				$this->specialPartialModificationPostProcessProductTemplate();
			}
		}

		################### SPECIAL FIELTYPEPAGE-RELATED SETTINGS ##########
		$this->setPWCommerceFieldtypePageFieldsExtraSettings();

		################### SPECIAL ROLES-RELATED SETTINGS ##########
		// e.g. customers feature adds role 'pdloper-customer'
		$this->createPWCommerceOptionalRoles();

		################### SPECIAL ONE-WAY DEPENDENCY-RELATED OPERATIONS ##########
		$this->specialModificationProcessAddedOneWayDependencyFields();

		################### OTHER OPTIONS FEATURES SETTINGS ##########
		// e.g. custom shop root page, categories are collections, etc
		/** @var array $otherOptionalSettings */
		$otherOptionalSettings = $this->processOtherOptionalSettings();

		# +++++++++++++++++++++++++

		#############################
		// INVALIDATE FINDANYTHING CACHE
		// invalidate cache since templates added/removed
		$this->invalidatePWCommerceFindAnythingCache();

		#############################

		// SAVE CONFIGS
		// merge retained/unchanged installed optional features + newly added ones
		$installedOptionalFeatures = array_merge($this->unchangedOptionalFeatures, $this->optionalFeaturesToInstall);
		$data = [
			'pwcommerce_install_configuration_status' => PwCommerce::PWCOMMERCE_SECOND_STAGE_INSTALL_CONFIGURATION_STATUS,
			'pwcommerce_installed_optional_features' => $installedOptionalFeatures
		];

		// OTHER OPTIONAL SETTINGS SENT?
		if (!empty($otherOptionalSettings)) {
			// save them!
			$data['pwcommerce_other_optional_settings'] = $otherOptionalSettings;
		}

		$this->pwcommerce->setPWCommerceModuleConfigs($data, $this->configModuleName);

		// @note: we call this here since it depends on $this->isDownloadsInUse() which depends partly on saved configs above!
		################## SPECIAL CUSTOM TABLES ###################

		// CREATE SPECIAL CUSTOM TABLES: 'pwcommerce_download_codes', ETC
		// @note: this can be created/dropped depending on whether downloads in use
		// whether in first or second config install
		$this->checkModifyPWCommerceSpecialCustomTables();
	}

	/**
	 * Create P W Commerce Fields.
	 *
	 * @return mixed
	 */
	private function createPWCommerceFields() {
		if (empty($this->fieldsToInstall))
			return;
		// -------
		$fieldNames = [];
		foreach ($this->fieldsToInstall as $fieldName => $fieldData) {
			// ------
			// MULTILINGUAL TEXTAREA
			// make description field multilingual if site is multilingual and uses 'FieldtypeTextareaLanguage', else usual textarea
			// change pwcommerce_description fieldtype if 'FieldtypeTextareaLanguage' IS NOT INSTALLED
			// if ($fieldName === 'pwcommerce_description' && empty($this->isSiteMultilingual)) {
			if ($fieldName === 'pwcommerce_description' && empty($this->wire('modules')->isInstalled('FieldtypeTextareaLanguage'))) {
				$fieldData['type'] = 'FieldtypeTextarea';
				//
			}

			// ------------
			$field = new Field();
			$field->setImportData($fieldData);
			// --------
			$field->save();
			$fieldNames[] = $fieldName;
		}

		// ---------
		$fieldNames = implode(", ", $fieldNames);
		$notice = sprintf(__("Created fields %s."), $fieldNames);
		$this->message($notice);
	}

	/**
	 * Set P W Commerce Fieldtype Page Fields Extra Settings.
	 *
	 * @return mixed
	 */
	private function setPWCommerceFieldtypePageFieldsExtraSettings() {
		// TODO @note: for now just loop through all available fields and skip non-FieldtypePage ones
		foreach ($this->getPWCommerceFieldsData() as $fieldName => $fieldData) {
			// for FieldtypePage fields we also set their 'template_id' property
			// this is for Selectable Pages Template
			if ($fieldData['type'] === 'FieldtypePage' && !empty($fieldData['template_id'])) {
				// get the field
				$field = $this->wire('fields')->get("name={$fieldName}");
				// just in case
				if (empty($field))
					continue;
				// -------------
				// although the setting is under 'template_id' this is a string; the name of the template to set
				// this makes fieldData portable
				$selectablePagesTemplateName = $fieldData['template_id'];
				// get the template
				$template = $this->wire('templates')->get("name={$selectablePagesTemplateName}");
				$field->template_id = $template->id;
				// --------
				// save
				$field->save();
			}
		}
	}

	/**
	 * Create P W Commerce Templates.
	 *
	 * @return mixed
	 */
	private function createPWCommerceTemplates() {
		if (empty($this->templatesToInstall))
			return;
		// -------
		$templateNames = [];

		foreach ($this->templatesToInstall as $templateName => $templateData) {
			$fieldgroup = new Fieldgroup();
			$fieldgroup->name = $templateName;
			// create template fieldgroup fields
			foreach ($templateData['fieldgroupFields'] as $fieldname)
				$fieldgroup->add($fieldname);

			// --------
			$fieldgroup->save();
			$template = new Template();
			$template->setImportData($templateData);
			$template->save();
			// ------
			// SAVE 'fieldgroupContexts'
			// we need to save this manually
			if (!empty($templateData['fieldgroupContexts'])) {
				foreach ($templateData['fieldgroupContexts'] as $fieldName => $values) {
					// skip fields without fieldgroupContexts data
					if (empty($values)) {
						continue;
					}
					// ----------
					// get the field that is part of this Fieldgroup, in the context of this Fieldgroup.
					$field = $fieldgroup->getFieldContext($fieldName);

					if (empty($field)) {
						// field not found: was perhaps not installed due to a dependent optional feature not getting installed, e.g. 'pwcommerce_customer_groups' requires customer groups
						// skip!
						continue;
					}

					// set values to field (e.g. rows, description, etc)

					foreach ($values as $property => $value) {

						$field->set($property, $value);
					}
					// save this field's contexts data
					$this->wire('fields')->saveFieldgroupContext($field, $fieldgroup);
				}
			}

			// -----
			// track names of added templates
			$templateNames[] = $templateName;
		}

		// RUN POST INSTALL FOR ALLOWED PARENTS AND CHILDREN
		$this->setPWCommerceTemplatesAllowedParentsAndChildren();
		// -----
		$templateNames = implode(", ", $templateNames);
		$notice = sprintf(__("Created templates %s."), $templateNames);
		$this->message($notice);
	}

	/**
	 * Set P W Commerce Templates Allowed Parents And Children.
	 *
	 * @return mixed
	 */
	private function setPWCommerceTemplatesAllowedParentsAndChildren() {
		// SET FAMILY SETTINGS FOR PWCOMMERCE TEMPLATES
		if (empty($this->templatesToInstall))
			return;
		$templates = $this->wire('templates');

		foreach ($this->templatesToInstall as $templateName => $templateData) {
			$template = $templates->get($templateName);

			// SET CHILD TEMPLATES for parent template
			if (!empty($templateData['childTemplates'])) {
				$childTemplatesSelector = implode("|", $templateData['childTemplates']);
				$childTemplates = $templates->find("name={$childTemplatesSelector}");
				$template->childTemplates($childTemplates);
			}

			// ------

			// SET PARENT TEMPLATES for child template
			if (!empty($templateData['parentTemplates'])) {
				$parentTemplatesSelector = implode("|", $templateData['parentTemplates']);
				$parentTemplates = $templates->find("name={$parentTemplatesSelector}");
				$template->parentTemplates($parentTemplates);
			}

			$template->save();
		}
	}

	/**
	 * Create P W Commerce Pages.
	 *
	 * @return mixed
	 */
	private function createPWCommercePages() {
		if (empty($this->pagesToInstall))
			return;

		$sanitizer = $this->wire('sanitizer');
		$pages = $this->wire('pages');

		if (empty($this->isSecondStageInstallConfiguration)) {
			// **** FIRST TIME INSTALL ONLY *****
			// FIRST CREATE THE MAIN PARENT PAGE
			// this will be a single child of the ProcessPWCommerce module's admin page's (shop) child
			// its name is 'pwcommerce'
			// it is the parent of all sections parent pages, i.e. products, orders, categories, settings, etc
			// @see ProcessPwCommerce::install why we need this page
			$parent = $pages->get($this->shopProcessPWCommercePageID);
			$mainShopPage = new Page();
			$mainShopPage->template = 'pwcommerce';
			$mainShopPage->parent = $parent;
			$mainShopPage->title = 'PWCommerce';
			$mainShopPage->name = PwCommerce::CHILD_PAGE_NAME;
			$mainShopPage->save();
		} else {
			// **** SECOND TIME/MODIFY INSTALL ONLY *****
			$mainShopPage = $pages->get($this->shopAdminPWCommerceRootPageID);
		}

		// -------------
		$pageTitles = [];
		// $allTemplates = $this->getPWCommerceTemplatesData();
		// @note: this is the 'pwcommerce' page under the main process module
		// @see above
		// TODO: abort if parent not found?
		// -------
		foreach ($this->pagesToInstall as $feature => $values) {
			$title = $values['title'];
			$templateName = $values['template'];
			// TODO NEED TO THINK HOW TO GET THIS SINCE IN REQUIRED AND OPTIONAL FEATURES ARRAYS, THE VALUES ARE ARRAYS; HOWEVER, WE ONLY NEED THE PARENTS; IN THOSE ARRAYS, THESE ARE THE FIRST; ALTERNATIVELY, REWORK getPWCommerceRequiredPages() and getPWCommerceOptionalPages() so that their values are arrays of title=>xxx and template=>xxx!
			// $template = $this->wire('templates')->get("name={$templateName}");
			// -----------
			$page = new Page();
			// $page->template = $template;
			$page->template = $templateName;
			$page->parent = $mainShopPage;
			$page->title = $title;
			$page->name = $sanitizer->pageName($page->title, true);

			// set page as active in other languages
			if ($this->isSiteMultilingual) {
				foreach ($this->wire('languages') as $language) {
					// skip default language as already set above
					if ($language->name == 'default') {
						continue;
					}
					$page->set("status$language", 1);
				}
			}
			// --------------
			$page->save();
			$pageTitles[] = $title;
			// --------
			// add children if any
			if (!empty($values['children'])) {
				foreach ($values['children'] as $childrenValues) {
					$childPage = new Page();
					$childPage->template = $childrenValues['template'];
					$childPage->parent = $page;
					$childPage->title = $childrenValues['title'];
					$childPage->name = $sanitizer->pageName($childPage->title, true);

					// set child page as active in other languages
					if ($this->isSiteMultilingual) {
						foreach ($this->wire('languages') as $language) {
							// skip default language as already set above
							if ($language->name == 'default') {
								continue;
							}
							$childPage->set("status$language", 1);
						}
					}

					$childPage->save();
					// --------
					$pageTitles[] = $childrenValues['title'];
				}
			}
			// -----

		}
		// -----
		$pageTitles = implode(", ", $pageTitles);
		$notice = sprintf(__("Created pages %s."), $pageTitles);
		$this->message($notice);
	}

	/**
	 * Check Modify P W Commerce Special Custom Tables.
	 *
	 * @return mixed
	 */
	private function checkModifyPWCommerceSpecialCustomTables() {
		// CHECK IF WE NEED TO CREATE OR DROP SPECIAL CUSTOM TABLES
		// these tables depend on selected pwcommerce features
		// @note: TODO -> for now only download codes table!
		$tablesNames = [];
		if (!empty($this->isDownloadsInUse())) {
			// DOWNLOADS IN USE: CREATE DOWNLOADS TABLE IF IT DOESN'T EXIST
			if (empty($this->pwcommerce->isExistPWCommerceCustomTable(PwCommerce::PWCOMMERCE_DOWNLOAD_CODES_TABLE_NAME))) {
				// IF TABLE DOES NOT EXIST, CREATE IT
				$tablesNames[] = PwCommerce::PWCOMMERCE_DOWNLOAD_CODES_TABLE_NAME;
			}
			// -----

			$this->createPWCommerceSpecialCustomTables($tablesNames);
		} else {
			// DOWNLOADS NOT IN USE: DROP DOWNLOADS TABLE IF IT EXISTS
			if (!empty($this->pwcommerce->isExistPWCommerceCustomTable(PwCommerce::PWCOMMERCE_DOWNLOAD_CODES_TABLE_NAME))) {
				// IF TABLE EXISTS, DROP IT
				$tablesNames[] = PwCommerce::PWCOMMERCE_DOWNLOAD_CODES_TABLE_NAME;
			}
			// -----

			$this->dropPWCommerceSpecialCustomTables($tablesNames);
		}
	}

	/**
	 * Create P W Commerce Custom Tables.
	 *
	 * @return mixed
	 */
	private function createPWCommerceCustomTables() {
		// ONLY RUN IF IN FIRST STAGE CONFIGURE INSTALL
		if ($this->isSecondStageInstallConfiguration)
			return;

		// --------------
		$pwcommerceCustomTableNames = $this->getNamesOfPWCommerceCustomTables();
		foreach ($pwcommerceCustomTableNames as $tableName) {
			// just in case, first check if the table exists before trying to create one
			// if one exists, drop it TODO, IS THIS OK?
			if (!empty($this->pwcommerce->isExistPWCommerceCustomTable($tableName))) {
				// IF TABLE EXISTS, DROP IT
				$this->dropTable($tableName);
			}
			// ---------
			// create the custom table
			$this->createPWCommerceCustomTable($tableName);
		}
	}

	/**
	 * Create P W Commerce Special Custom Tables.
	 *
	 * @param mixed $tablesNames
	 * @return mixed
	 */
	private function createPWCommerceSpecialCustomTables($tablesNames) {

		foreach ($tablesNames as $tableName) {
			// just in case, first check if the table exists before trying to create one
			// if one exists, drop it TODO, IS THIS OK?
			if (!empty($this->pwcommerce->isExistPWCommerceCustomTable($tableName))) {

				// IF TABLE EXISTS, DROP IT
				$this->dropTable($tableName);
			}
			// ---------
			// create the custom table
			$this->createPWCommerceCustomTable($tableName);
		}
	}

	/**
	 * Create P W Commerce Custom Table.
	 *
	 * @param mixed $tableName
	 * @return mixed
	 */
	private function createPWCommerceCustomTable($tableName) {

		// ----------------
		$sql = null;

		$database = $this->wire('database');
		// -------------
		// GET SQL STRING FOR CUSTOM TABLE TO CREATE
		// TODO: can make more dynamic?
		if ($tableName === 'pwcommerce_order_status') {
			$sql = $this->getSQLForOrderStatusTable();
		} else if ($tableName === 'pwcommerce_cart') {
			$sql = $this->getSQLForOrderCartTable();
		} else if ($tableName === 'pwcommerce_download_codes') {
			$sql = $this->getSQLForDownloadCodesTable();
		}

		if (empty($sql)) {
			// TODO THROW ERROR!
		}
		// --------
		$query = $database->prepare($sql);
		$result = $query->execute();
		// -----------

		if (!$result) {
			// TODO: SHOULD WE ABORT? MEANING RUN THIS BEFORE OTHER BITS OF THE INSTALLER
			$notice = sprintf(__("Error creating table %s."), $tableName);
			$this->error($notice);
		} else {
			$notice = sprintf(__("Created table %s."), $tableName);
			$this->message($notice);
		}
	}

	/**
	 * Drop P W Commerce Special Custom Tables.
	 *
	 * @param mixed $tablesNames
	 * @return mixed
	 */
	private function dropPWCommerceSpecialCustomTables($tablesNames) {

		foreach ($tablesNames as $tableName) {
			// if TABLE exists, drop it TODO, IS THIS OK?
			if (!empty($this->pwcommerce->isExistPWCommerceCustomTable($tableName))) {

				// IF TABLE EXISTS, DROP IT
				$this->dropTable($tableName);
			}
		}
	}

	/**
	 * Drop Table.
	 *
	 * @param mixed $tableName
	 * @return mixed
	 */
	private function dropTable($tableName) {
		$this->wire('database')->exec("DROP TABLE `" . $tableName . "`");
	}

	/**
	 * Create P W Commerce Optional Roles.
	 *
	 * @return mixed
	 */
	private function createPWCommerceOptionalRoles() {
		$optionalRoles = $this->getPWCommerceOptionalRoles();
		$roles = $this->wire('roles');
		foreach ($optionalRoles as $optionalFeature => $roleName) {
			if (in_array($optionalFeature, $this->optionalFeaturesToInstall)) {
				// add and save the new role
				$role = $roles->add($roleName);
			}
		}
	}
	// ~~~~~~~~~~~

	## PWCOMMERCE RUN PARTIAL MODIFICATION INSTALLER ##

	/**
	 * Special Partial Modification Post Process Product Template.
	 *
	 * @return mixed
	 */
	public function specialPartialModificationPostProcessProductTemplate() {
		// get the names of the features that are special to products
		// we want the values at KEYS
		$specialProductFeatures = array_keys($this->getPWCommerceOptionalFeaturesProductFieldsDependencies());
		$specialProductFeaturesToInstall = array_intersect($specialProductFeatures, $this->optionalFeaturesToInstall);
		// return early if none of out features below are getting installed in second config install
		if (empty($specialProductFeaturesToInstall)) {
			return;
		}


		// Here we post-process pwcommerce-product template to amend its 'fieldgroupFields' and 'fieldgroupContexts'
		// depending on whether the related product optional features that were installed
		// - Most Fields and most templates would have been created OK.
		// - For fields, what we now need to do is to INSERT THEM  in the correct places within the 'pwcommerce-product' and in some cases, 'pwcommerce-product-variant' template(s)
		// - Order of fields is at two levels: Correct order of different fields as well as correct order with respect to inserting between fieldsets and/or fieldsettabs!
		// ----- attributes ------
		// - for templates, if optional feature we are dealing with is 'product_attributes', we will need to install the template 'pwcommerce-product-variant'
		// - we can get its configs directly from templates data! getPWCommerceTemplatesData()
		// - the field 'pwcommerce_product_attributes_options' WILL BE added to the template 'pwcommerce-product-variant' WHEN WE CREATE THE TEMPLATE using the template data. Nothing else needs to be done in this regard
		// - the fields: 'pwcommerce_product_attributes', the variants' fieldsets and runtime markup will need to be inserted in correct order in the template pwcommerce-product
		// ---- classification ----
		// - for categories, tags, type and brand we check if we already have the classification fieldset_tabs
		// - if we do, we don't create them afresh (TODO: confirm this will be already set in fields to install!)
		// - important thing here is to just INSERT related fields (Page fields) in pwcommerce-product template
		// ---- properties ---
		// - for properties we need only do the inserts
		// ---- downloads ---
		// - for downloads we need only do the inserts BUT...also check if we have variants
		// - if yes, we also insert in template pwcommerce-product-variant
		// >>> NOTES <<<
		// a. for inserts, probably easier if we created a new WireArray in the order we want, instead of doing inserts
		// // ---------
		// These apply to:
		// 1. attributes
		// - needs
		// 2. properties
		// -------
		// 3. classification
		// a. categories
		// b. tags
		// c. brand
		// d. type
		// ---------
		// 4. downloads
		//

		$productTemplate = $this->wire('templates')->get('pwcommerce-product');

		//  ----------------

		// 1. attributes
		if (in_array('product_attributes', $this->optionalFeaturesToInstall)) {
			## ATTRIBUTES FEATURE GETTING INSTALLED ##
			// this means we modify pwcommerce-product, pwcommerce-product-variant templates (+create) and pwcommerce-attribute
			// --------------
			// AMEND FIELDGROUPFIELDS + FIELDGROUPCONTEXTS with respect to ATTRIBUTES
			$this->additionPostProcessProductTemplateInRelationToAttributes($productTemplate);
		}

		// 2. properties
		if (in_array('product_properties', $this->optionalFeaturesToInstall)) {
			## PROPERTIES FEATURE GETTING INSTALLED ##
			// this means we modify pwcommerce-product template
			// --------------
			// AMEND FIELDGROUPFIELDS + FIELDGROUPCONTEXTS with respect to DOWNLOADS
			$this->additionPostProcessProductTemplateInRelationToProperties($productTemplate);
		}

		// 3. classification (categories, tags, brand, type)
		if (!empty($this->isInstallProductClassificationFeature())) {
			## CLASSIFICATION FEATURE GETTING INSTALLED ##
			// this means we modify pwcommerce-product template
			// --------------
			// AMEND FIELDGROUPFIELDS + FIELDGROUPCONTEXTS with respect to DOWNLOADS
			$this->additionPostProcessProductTemplateInRelationToClassification($productTemplate);
		}

		// 4. downloads
		if (in_array('downloads', $this->optionalFeaturesToInstall)) {
			## DOWNLOADS FEATURE GETTING INSTALLED ##
			// this means we modify pwcommerce-product and optionally pwcommerce-product-variants templates
			// --------------
			// AMEND FIELDGROUPFIELDS + FIELDGROUPCONTEXTS with respect to DOWNLOADS
			$this->additionPostProcessProductTemplateInRelationToDownloads($productTemplate);
		}
	}

	/**
	 * Addition Post Process Product Template In Relation To Attributes.
	 *
	 * @param mixed $productTemplate
	 * @return mixed
	 */
	private function additionPostProcessProductTemplateInRelationToAttributes($productTemplate) {

		$fieldgroup = $productTemplate->fieldgroup;
		$productTemplateRawData = $this->getPWCommerceTemplateDataByName($productTemplate->name);

		// --------------
		// PREPARE TO ADD THE FIELDS in correct order
		$attributesFields = ['pwcommerce_product_attributes', 'pwcommerce_variants_fieldset', 'pwcommerce_runtime_markup', 'pwcommerce_variants_fieldset_END'];

		// we start with 'pwcommerce_product_settings' as the existing field reference point
		// this field is always present in pwcommerce-product template
		$existingFieldName = 'pwcommerce_product_settings';
		$existingField = $productTemplate->fields->get($existingFieldName);

		// ADD THE FIELDS in correct order
		foreach ($attributesFields as $fieldName) {
			$newField = $this->wire('fields')->get($fieldName);
			$fieldgroup->insertAfter($newField, $existingField);
			// $newField then becomes $existingField
			$existingField = $newField;
		}
		// save the fieldgroup after adding the classification fields
		$fieldgroup->save();

		// ALSO SET 'fieldgroupContexts' FOR THESE FIELDS in 'pwcommerce-product' template
		// we need to save this manually
		foreach ($attributesFields as $fieldName) {
			if (!empty($productTemplateRawData['fieldgroupContexts'][$fieldName])) {
				foreach ($productTemplateRawData['fieldgroupContexts'][$fieldName] as $property => $value) {
					// ----------
					// get the field that is part of this Fieldgroup, in the context of this Fieldgroup.
					$field = $fieldgroup->getFieldContext($fieldName);
					// set values to field (e.g. rows, description, etc)
					$field->set($property, $value);
					// save this field's contexts data
					$this->wire('fields')->saveFieldgroupContext($field, $fieldgroup);
				}
			}
		}

		// finally add to 'pwcommerce-product-variant' as it is in use
		$this->additionPostProcessProductVariantTemplateInRelationToAttributes();
	}

	/**
	 * Addition Post Process Product Variant Template In Relation To Attributes.
	 *
	 * @return mixed
	 */
	private function additionPostProcessProductVariantTemplateInRelationToAttributes() {

		$productVariantTemplateName = 'pwcommerce-product-variant';
		$productVariantTemplateRawData = $this->getPWCommerceTemplateDataByName($productVariantTemplateName);

		// here we are sure 'pwcommerce-product-variant' if in use
		// we will need to determine where to insert
		$newField = $this->wire('fields')->get('pwcommerce_product_attributes_options');
		$productVariantTemplate = $this->wire('templates')->get($productVariantTemplateName);
		$productVariantTemplateFieldgroup = $productVariantTemplate->fieldgroup;

		$variantFields = ['pwcommerce_product_attributes_options'];

		// we will use  'pwcommerce_images' as the existing field reference point
		// this field is always present in pwcommerce-product-variant template
		$existingFieldName = 'pwcommerce_images';
		$existingField = $productVariantTemplate->fields->get($existingFieldName);

		$productVariantTemplateFieldgroup->insertAfter($newField, $existingField);

		if (!empty($this->isDownloadsInUse())) {
			// if downloads were already installed before attributes, i.e. already exist
			// we need need to add the field to pwcommerce-product-variant
			$newField = $this->wire('fields')->get('pwcommerce_downloads');
			$productVariantTemplateFieldgroup->add($newField);
			$variantFields[] = 'pwcommerce_downloads';
		}

		// save the fieldgroup
		$productVariantTemplateFieldgroup->save();
		// -------
		// ALSO SET 'fieldgroupContexts' FOR THIS FIELD in 'pwcommerce-product-variant' template
		// we need to save this manually

		foreach ($variantFields as $fieldName) {
			if (!empty($productVariantTemplateRawData['fieldgroupContexts'][$fieldName])) {
				foreach ($productVariantTemplateRawData['fieldgroupContexts'][$fieldName] as $property => $value) {
					// ----------
					// get the field that is part of this Fieldgroup, in the context of this Fieldgroup.
					$field = $productVariantTemplateFieldgroup->getFieldContext($fieldName);
					// TODO KEEP AN EYE ON THIS! HAVING ISSUES WITH ATTRIBUTES INSTALL/UNINSTALL!
					if (empty($field))
						continue;
					// set values to field (e.g. rows, description, etc)
					$field->set($property, $value);
					// save this field's contexts data
					$this->wire('fields')->saveFieldgroupContext($field, $productVariantTemplateFieldgroup);
				}
			}
		}

	}

	/**
	 * Addition Post Process Product Template In Relation To Properties.
	 *
	 * @param mixed $productTemplate
	 * @return mixed
	 */
	private function additionPostProcessProductTemplateInRelationToProperties($productTemplate) {

		$fieldgroup = $productTemplate->fieldgroup;
		$productTemplateRawData = $this->getPWCommerceTemplateDataByName($productTemplate->name);

		// --------------
		// ADD THE FIELDS in correct order
		$propertiesFields = ['pwcommerce_properties_fieldset_tab', 'pwcommerce_product_properties', 'pwcommerce_properties_fieldset_tab_END'];
		foreach ($propertiesFields as $fieldName) {
			$newField = $this->wire('fields')->get($fieldName);
			$fieldgroup->add($newField);
		}
		// ------------
		// save fieldgroup
		$fieldgroup->save();

		// ALSO SET 'fieldgroupContexts' FOR THESE FIELDS in 'pwcommerce-product' template
		// we need to save this manually
		foreach ($propertiesFields as $fieldName) {
			if (!empty($productTemplateRawData['fieldgroupContexts'][$fieldName])) {
				foreach ($productTemplateRawData['fieldgroupContexts'][$fieldName] as $property => $value) {
					// ----------
					// get the field that is part of this Fieldgroup, in the context of this Fieldgroup.
					$field = $fieldgroup->getFieldContext($fieldName);
					// set values to field (e.g. rows, description, etc)
					$field->set($property, $value);
					// save this field's contexts data
					$this->wire('fields')->saveFieldgroupContext($field, $fieldgroup);
				}
			}
		}
	}

	/**
	 * Addition Post Process Product Template In Relation To Classification.
	 *
	 * @param mixed $productTemplate
	 * @return mixed
	 */
	private function additionPostProcessProductTemplateInRelationToClassification($productTemplate) {

		$fieldgroup = $productTemplate->fieldgroup;
		$productTemplateRawData = $this->getPWCommerceTemplateDataByName($productTemplate->name);

		// ------------
		// PREPARE THE FIELDS
		// @note: KEY -> feature name; VALUE -> field name
		$classificationFields = [
			// type
			'product_types' => 'pwcommerce_type',
			// brand
			'product_brands' => 'pwcommerce_brand',
			// categories
			'product_categories' => 'pwcommerce_categories',
			// tags
			'product_tags' => 'pwcommerce_tags'
		];

		// get only the classification fields being installed
		$classificationFields = array_intersect_key($classificationFields, array_flip($this->optionalFeaturesToInstall));
		$classificationFields = array_values($classificationFields);
		// --------------
		// TODO DELETE WHEN DONE
		// SET THE FIELDS in correct order
		// add the top end of the fieldset tab
		// array_unshift($classificationFields, "pwcommerce_classification_fieldset_tab");
		// add the bottom end of the fieldset tab
		// $classificationFields[] = 'pwcommerce_classification_fieldset_tab_END';

		// FIRST, WE NEED TO INSERT  'pwcommerce_classification_fieldset_tab' if it doesn't exist already
		if (!$fieldgroup->hasField('pwcommerce_classification_fieldset_tab')) {
			// DETERMINE POSITION TO INSERT
			if ($fieldgroup->hasField('pwcommerce_downloads')) {
				// insert after downloads
				$existingFieldName = 'pwcommerce_downloads';
			} else if ($fieldgroup->hasField('pwcommerce_variants_fieldset_END')) {
				// insert after variants
				$existingFieldName = 'pwcommerce_variants_fieldset_END';
			} else {
				// insert after pwcommerce_product_settings
				$existingFieldName = 'pwcommerce_product_settings';
			}
			// --------------
			// get the existing field to insert after
			$existingField = $productTemplate->fields->get($existingFieldName);
			#  DO THE INSERT for the FIELDSET OPEN #
			$newField = $this->wire('fields')->get('pwcommerce_classification_fieldset_tab');
			$fieldgroup->insertAfter($newField, $existingField);
			$fieldgroup->save();

			# DO THE INSERT for the FIELDSET END #
			$existingField = $productTemplate->fields->get('pwcommerce_classification_fieldset_tab');
			$newField = $this->wire('fields')->get('pwcommerce_classification_fieldset_tab_END');
			$fieldgroup->insertAfter($newField, $existingField);
			$fieldgroup->save();
		}

		# DO THE INSERTS FOR CLASSIFICATION FIELDS #
		// INSERT THE CLASSIFICATION FIELDS THEMSELVES, IN BETWEEN THE FIELDSETS
		// @note:existing field to start the loop is the opening fieldset
		// we subsequently change this in the loop
		$existingField = $productTemplate->fields->get('pwcommerce_classification_fieldset_tab');
		foreach ($classificationFields as $fieldName) {
			$newField = $this->wire('fields')->get($fieldName);
			$fieldgroup->insertAfter($newField, $existingField);
			// $newField then becomes $existingField
			$existingField = $newField;
		}
		// save the fieldgroup after adding the classification fields
		$fieldgroup->save();

		# FIELDGROUPCONTEXTS #
		// ALSO SET 'fieldgroupContexts' FOR THE FIELDS in 'pwcommerce-product' template
		// we need to save this manually
		// add the fieldsets to the $classificationFields array first in case they too have contexts
		$classificationFields[] = 'pwcommerce_classification_fieldset_tab';
		$classificationFields[] = 'pwcommerce_classification_fieldset_tab_END';
		// -------------
		foreach ($classificationFields as $fieldName) {
			if (!empty($productTemplateRawData['fieldgroupContexts'][$fieldName])) {
				foreach ($productTemplateRawData['fieldgroupContexts'][$fieldName] as $property => $value) {
					// ----------
					// get the field that is part of this Fieldgroup, in the context of this Fieldgroup.
					$field = $fieldgroup->getFieldContext($fieldName);
					// set values to field (e.g. rows, description, etc)
					$field->set($property, $value);
					// save this field's contexts data
					$this->wire('fields')->saveFieldgroupContext($field, $fieldgroup);
				}
			}
		}
	}

	/**
	 * Addition Post Process Product Template In Relation To Downloads.
	 *
	 * @param mixed $productTemplate
	 * @return mixed
	 */
	private function additionPostProcessProductTemplateInRelationToDownloads($productTemplate) {

		$fieldgroup = $productTemplate->fieldgroup;
		$productTemplateRawData = $this->getPWCommerceTemplateDataByName($productTemplate->name);

		// determine where to insert
		if ($fieldgroup->hasField('pwcommerce_variants_fieldset_END')) {
			// VARIANTS IN USE: insert after variants fieldset end
			$existingFieldName = 'pwcommerce_variants_fieldset_END';
			$isProductVariantsInstalled = true;
		} else {
			// VARIANTS NOT IN USE -> insert after settings fields
			$existingFieldName = 'pwcommerce_product_settings';
			$isProductVariantsInstalled = false;
		}

		$existingField = $productTemplate->fields->get($existingFieldName);

		// DO THE INSERT
		$newField = $this->wire('fields')->get('pwcommerce_downloads');
		$fieldgroup->insertAfter($newField, $existingField);
		$fieldgroup->save();

		// ALSO SET 'fieldgroupContexts' FOR THIS FIELD in 'pwcommerce-product' template
		// 'pwcommerce_downloads'
		// we need to save this manually
		if (!empty($productTemplateRawData['fieldgroupContexts']['pwcommerce_downloads'])) {
			foreach ($productTemplateRawData['fieldgroupContexts']['pwcommerce_downloads'] as $property => $value) {
				// ----------
				// get the field that is part of this Fieldgroup, in the context of this Fieldgroup.
				$field = $fieldgroup->getFieldContext($newField);
				// set values to field (e.g. rows, description, etc)
				$field->set($property, $value);
				// save this field's contexts data
				$this->wire('fields')->saveFieldgroupContext($field, $fieldgroup);
			}
		}

		// add to 'pwcommerce-product-variant' if in use
		if ($isProductVariantsInstalled) {
			$this->additionPostProcessProductVariantTemplateInRelationToDownloads();
		}
	}

	/**
	 * Addition Post Process Product Variant Template In Relation To Downloads.
	 *
	 * @return mixed
	 */
	private function additionPostProcessProductVariantTemplateInRelationToDownloads() {

		$productVariantTemplateName = 'pwcommerce-product-variant';
		$productVariantTemplateRawData = $this->getPWCommerceTemplateDataByName($productVariantTemplateName);

		// add to 'pwcommerce-product-variant' if in use
		// @note: this is always the last field in this template so it is OK to add like this
		$newField = $this->wire('fields')->get('pwcommerce_downloads');
		$productVariantTemplate = $this->wire('templates')->get($productVariantTemplateName);
		$productVariantTemplateFieldgroup = $productVariantTemplate->fieldgroup;
		$productVariantTemplateFieldgroup->add($newField);
		$productVariantTemplateFieldgroup->save();
		// -------
		// ALSO SET 'fieldgroupContexts' FOR THIS FIELD in 'pwcommerce-product-variant' template
		// 'pwcommerce_downloads'
		// we need to save this manually
		if (!empty($productVariantTemplateRawData['fieldgroupContexts']['pwcommerce_downloads'])) {
			foreach ($productVariantTemplateRawData['fieldgroupContexts']['pwcommerce_downloads'] as $property => $value) {
				// ----------
				// get the field that is part of this Fieldgroup, in the context of this Fieldgroup.
				$field = $productVariantTemplateFieldgroup->getFieldContext($newField);
				// set values to field (e.g. rows, description, etc)
				$field->set($property, $value);
				// save this field's contexts data
				$this->wire('fields')->saveFieldgroupContext($field, $productVariantTemplateFieldgroup);
			}
		}
	}

	/**
	 * Special Modification Process Added One Way Dependency Fields.
	 *
	 * @return mixed
	 */
	private function specialModificationProcessAddedOneWayDependencyFields() {
		if (!empty($this->addedOneWayDependencyFields)) {
			// process templates with ADDED one-way dependent fields
			foreach ($this->addedOneWayDependencyFields as $templateName => $fieldName) {
				// get the field to add
				// @note: would already have been created using $this->createPWCommerceFields()
				$field = $this->wire('fields')->get("name={$fieldName}");
				// ---------
				$template = $this->wire('templates')->get($templateName);
				$templateFieldgroup = $template->fieldgroup;
				$templateFieldgroup->append($field);
				$templateFieldgroup->save();
			}

		}

	}

	/**
	 * Special Modification Process Removed One Way Dependency Fields.
	 *
	 * @return mixed
	 */
	private function specialModificationProcessRemovedOneWayDependencyFields() {

		if (!empty($this->removedOneWayDependencyFields)) {
			// process templates with REMOVED one-way dependent fields
			foreach ($this->removedOneWayDependencyFields as $templateName => $fieldName) {
				// get the field to add
				// @note: would already have been created using $this->createPWCommerceFields()
				$field = $this->wire('fields')->get("name={$fieldName}");
				// ---------
				$template = $this->wire('templates')->get($templateName);

				if (!empty($template)) {
					$templateFieldgroup = $template->fieldgroup;
					// +++++++++
					if ($templateFieldgroup->hasField($field)) {
						// TODO - CONFIRM USAGE IN CASE MANY PAGES INVOLVED???
						// remove field from the template
						$templateFieldgroup->remove($field);
						$templateFieldgroup->save();
					}
				}

			}

		}

	}

	// ~~~~~~~~~~~

	## PWCOMMERCE RUN PARTIAL MODIFICATION UNINSTALLER ##

	/**
	 * Partial Modification Of P W Commerce Removal Action.
	 *
	 * @return mixed
	 */
	public function partialModificationOfPWCommerceRemovalAction() {
		// @note:
		// pages to remove:
		// we get these from the templates themselves! we find all pages that are using the templates AND whose parent_id = $this->shopid (important so as to get the parent pages only)
		// fields to remove:
		// for fields we get these from the templates fields, minus the ignore fields (title, description, images, etc). If custom fields included, then dev shouldn't be calling for removal! However, not a hindrance to removal since we will be deleting the template first, meaning the custom fieldis not in use. But from a data point of view, they will lose data since we will delete the pages.
		// we also remove fields not associated with the templates to remove! for instance, if we removed the feature 'tags', the templates to remove are 'tags' and 'tag'. These have only the field 'title'. We won't touch these. However, we will need to remove the associated field(s) in products template! This will be at least one field, i.e. 'pwcommerce_tags', a page field. In case classification features are not in use we will remove the associated fieldsets as well! @note: these all found at $this->removedOptionalFeaturesFieldsToUninstall
		//

		// --------------
		// PARTIAL MODIFICATION: REMOVE PAGES
		$this->partialModificationOfPWCommerceDeletePages();
		//  PARTIAL MODIFICATION: REMOVE TEMPLATES
		$this->partialModificationOfPWCommerceDeleteTemplatesAndFieldgroups();

		//  PARTIAL MODIFICATION: PROCESS REMOVED ONE WAY DEPENDENCY FIELDS from DEPENDENT TEMPLATES
		// @note: needed here to remove fields from context before they are deleted below
		$this->specialModificationProcessRemovedOneWayDependencyFields();
		// PARTIAL MODIFICATION: REMOVE FIELDS
		$this->partialModificationOfPWCommerceDeleteFields();
		// PARTIAL MODIFICATION: REMOVE ROLES
		$this->partialModificationOfPWCommerceDeleteRoles();
		// PARTIAL MODIFICATION: SPECIAL MODIFICATION OR REMOVAL FOR/OF PRODUCT TEMPLATES
		$this->partialPostRemovalModificationOrRemovalOfPWCommerceTemplates();
		// PARTIAL MODIFICATION: SPECIAL MODIFICATION CONFIGS FOR CUSTOM SHOP ROOT PAGE PARENT PAGES ITEMS
		$this->partialPostRemovalModificationOfProcessCustomShopRootSettings();
	}

	/**
	 * Partial Modification Of P W Commerce Delete Pages.
	 *
	 * @return mixed
	 */
	private function partialModificationOfPWCommerceDeletePages() {
		// @note: this will set $this->removedOptionalFeaturesTemplatesToUninstall as well
		$this->prepareRemovedOptionalFeaturesTemplatesNames();
		// ---------------------
		// $removedOptionalFeaturesTemplatesNamesSelector = $this->getRemovedOptionalFeaturesTemplatesNamesSelector();
		// @UPDATE: SINCE PWCOMMERCE 009, SOME SHOP FEATURES CAN LIVE UNDER A NAMED CUSTOM ROOT PAGE. WE ALSO NEED TO FIND THEM! if applicable here
		$removedOptionalFeaturesParentPagesSelector = $this->getMainShopPagesSelectorForPartialModificationOfPWCommerceDeletePages();
		// $removedOptionalFeaturesParentPages = $this->wire('pages')->find("parent_id={$this->shopAdminPWCommerceRootPageID},template={$removedOptionalFeaturesTemplatesNamesSelector},include=all");
		$removedOptionalFeaturesParentPages = $this->wire('pages')->find($removedOptionalFeaturesParentPagesSelector);
		// -----------------
		$this->deletePages($removedOptionalFeaturesParentPages);
	}

	/**
	 * Get Main Shop Pages Selector For Partial Modification Of P W Commerce Delete Pages.
	 *
	 * @return mixed
	 */
	private function getMainShopPagesSelectorForPartialModificationOfPWCommerceDeletePages() {
		$removedOptionalFeaturesTemplatesNamesSelector = $this->getRemovedOptionalFeaturesTemplatesNamesSelector();
		$isUseCustomShopRootPage = $this->pwcommerce->isOtherOptionalSettingInstalled(PwCommerce::PWCOMMERCE_IS_USE_CUSTOM_SHOP_ROOT_PAGE_SETTING_NAME);
		if (!empty($isUseCustomShopRootPage)) {
			// CUSTOM SHOP ROOT PAGE IN USE
			// find the pwcommerce parent pages  features living under it
			// PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_SETTING_NAME
			$installedOtherOptionalSettings = $this->pwcommerce->getPWCommerceInstalledOtherOptionalSettings($this->configModuleName);
			// GET THE ID OF THE CUSTOM SHOP ROOT PAGE!
			$customShopRootPageID = $installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_ID_SETTING_NAME];
			if (!empty($customShopRootPageID)) {
				// ++++++
				// @NOTE: OR:SELECTOR for parent_id!
				$removedOptionalFeaturesParentPagesSelector = "parent_id={$this->shopAdminPWCommerceRootPageID}|{$customShopRootPageID},template={$removedOptionalFeaturesTemplatesNamesSelector},include=all";
			}
		} else {
			// CUSTOM ROOT PAGE NOT IN USE
			$removedOptionalFeaturesParentPagesSelector = "parent_id={$this->shopAdminPWCommerceRootPageID},template={$removedOptionalFeaturesTemplatesNamesSelector},include=all";
		}
		// ---------
		return $removedOptionalFeaturesParentPagesSelector;
	}

	/**
	 * Prepare Removed Optional Features Templates Names.
	 *
	 * @return mixed
	 */
	private function prepareRemovedOptionalFeaturesTemplatesNames() {
		$optionalFeatures = $this->getPWCommerceOptionalFeatures();
		$optionalFeaturesTemplates = array_intersect_key($optionalFeatures, array_flip($this->removedOptionalFeatures));
		// -------------
		// SET TEMPLATES TO REMOVE
		$removedOptionalFeaturesTemplatesToUninstall = [];
		foreach ($optionalFeaturesTemplates as $optionalFeatureTemplates) {
			$removedOptionalFeaturesTemplatesToUninstall = array_merge($removedOptionalFeaturesTemplatesToUninstall, $optionalFeatureTemplates);
		}
		// set to class property for later use
		$this->removedOptionalFeaturesTemplatesToUninstall = $removedOptionalFeaturesTemplatesToUninstall;
	}

	/**
	 * Get Removed Optional Features Templates Names Selector.
	 *
	 * @return mixed
	 */
	private function getRemovedOptionalFeaturesTemplatesNamesSelector() {
		// ------------
		return implode("|", $this->removedOptionalFeaturesTemplatesToUninstall);
	}

	/**
	 * Partial Modification Of P W Commerce Delete Templates And Fieldgroups.
	 *
	 * @return mixed
	 */
	private function partialModificationOfPWCommerceDeleteTemplatesAndFieldgroups() {
		$templates = $this->wire('templates');
		$removedOptionalFeaturesTemplatesNames = $this->removedOptionalFeaturesTemplatesToUninstall;
		$templatesNamesSelector = implode("|", $removedOptionalFeaturesTemplatesNames);
		$removedOptionalFeaturesTemplates = $templates->find("name={$templatesNamesSelector}");
		// TODO HERE AND ALSO IN COMPLETE REMOVAL, WE NEED TO REMOVE THE ASSOCIATED FIELDS FROM THEIR FIELDGROUPS AS WELL IN ORDER FOR DELETEFIELDS TO LATER WORK! OTHERWISE WE GET AN ERROR ABOUT THEE FIELD STILL BEING IN USE. for instance, if we remove categories, we will get error that we cannot delete 'pwcommerce_categories' since it is still in use the field group 'pwcommerce-product'. this means it has to be removed from the template level first! however, since we won't be dealing with 'pwcommerce-product' (it is not getting removed here; but in complete removal might cause issues as well due to order of things/race condition?) we need to do this elsewhere, in another method. For now, these are all product related to easier to deal with. Just call that fieldgroup [pwcommerce-product and pwcommerce-product-variant] and remove matching field(?). Might have to check hasField first @see $bool = $fieldgroup->remove($field); including the note on time taken! also $fieldgroup->save() NEEDS TESTING! + WARNING IF SITE HAS MANY PAGES ALREADY
		// -------------
		$this->deleteTemplatesAndFieldgroups($removedOptionalFeaturesTemplates);
	}

	/**
	 * Partial Modification Of P W Commerce Delete Fields.
	 *
	 * @return mixed
	 */
	private function partialModificationOfPWCommerceDeleteFields() {
		if (empty($this->removedOptionalFeaturesFieldsToUninstall))
			return;
		// ------------------
		// @note: we remove both direct and associated fields
		$fieldsNamesSelector = implode("|", $this->removedOptionalFeaturesFieldsToUninstall);
		// --------------
		$fields = $this->wire('fields');
		$removedOptionalFeaturesFields = $fields->find("name={$fieldsNamesSelector}");
		$this->deleteFields($removedOptionalFeaturesFields, true);
		// IF FEATURE REMOVED IS 'product_attributes', WE NEED TO REMOVE 'pwcommerce_runtime_markup' FROM 'pwcommerce-product'
	}

	/**
	 * Partial Modification Of P W Commerce Delete Roles.
	 *
	 * @return mixed
	 */
	private function partialModificationOfPWCommerceDeleteRoles() {
		$optionalRoles = $this->getPWCommerceOptionalRoles();
		$roles = $this->wire('roles');
		foreach ($optionalRoles as $optionalFeature => $roleName) {
			if (in_array($optionalFeature, $this->removedOptionalFeatures)) {
				$roleDelete = $roles->get($roleName);
				$role = $roles->delete($roleDelete);
			}
		}
	}

	/**
	 * Partial Post Removal Modification Or Removal Of P W Commerce Templates.
	 *
	 * @return mixed
	 */
	private function partialPostRemovalModificationOrRemovalOfPWCommerceTemplates() {
		// IF ATTRIBUTES FEATURE REMOVED
		if (in_array('product_attributes', $this->removedOptionalFeatures)) {
			$templates = $this->wire('templates');
			// REMOVE 'pwcommerce_runtime_markup' from pwcommerce-product tpl
			$productTemplate = $templates->get('pwcommerce-product');
			$productFieldgroup = $productTemplate->fieldgroup;
			$productFieldgroup->remove('pwcommerce_runtime_markup');
			$productFieldgroup->save();
			// DELETE PRODUCT VARIANT PAGES
			// TODO for now, assume not very many so do in one swoop!
			$variantPages = $this->wire('pages')->find("template=pwcommerce-product-variant, include=all");
			$this->deletePages($variantPages);
			// DELETE PRODUCT VARIANT TEMPLATE
			// @note: using find() since deleteTemplatesAndFieldgroups() expects a TemplatesArray
			$productVariantTemplate = $templates->find("name=pwcommerce-product-variant");
			$this->deleteTemplatesAndFieldgroups($productVariantTemplate);
			// DELETE PRODUCT VARIANT-SPECIFIC FIELDS
			$productVariantFieldsNames = ['pwcommerce_product_attributes_options'];
			$productVariantFieldsNamesSelector = implode("|", $productVariantFieldsNames);
			$productVariantFields = $this->wire('fields')->find("name={$productVariantFieldsNamesSelector}");
			$this->deleteFields($productVariantFields);
		}
	}

	/**
	 * Partial Post Removal Modification Of Process Custom Shop Root Settings.
	 *
	 * @return mixed
	 */
	private function partialPostRemovalModificationOfProcessCustomShopRootSettings() {
		// IF FEATURE RELATING TO CUSTOM SHOP ROOT PAGE 'PARENT PAGES' has been removed
		// we need to remove the parent from configs for custom shop root page

		$isUseCustomShopRootPage = $this->pwcommerce->isOtherOptionalSettingInstalled(PwCommerce::PWCOMMERCE_IS_USE_CUSTOM_SHOP_ROOT_PAGE_SETTING_NAME);
		if (!empty($isUseCustomShopRootPage)) {
			// CUSTOM SHOP ROOT PAGE IN USE
			// find the pwcommerce parent pages  features living under it
			// PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_SETTING_NAME
			$installedOtherOptionalSettings = $this->pwcommerce->getPWCommerceInstalledOtherOptionalSettings($this->configModuleName);
			$parentPagesItems = $installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_SETTING_NAME];
			if (!empty($parentPagesItems)) {
				$parentPagesItemsToRemove = [];
				foreach ($parentPagesItems as $feature) {
					if (in_array($feature, $this->removedOptionalFeatures)) {
						$parentPagesItemsToRemove[] = $feature;
					}
				}
				if (!empty($parentPagesItemsToRemove)) {
					// track removed feature related to custom shop root page parent pages!
					// @see: $this->processOtherOptionalSettings()
					$this->removedOptionalFeaturesForCustomShopRootPageParentPages = $parentPagesItemsToRemove;
				}
			}

		}

	}

	/**
	 * Process Other Optional Settings.
	 *
	 * @return mixed
	 */
	private function processOtherOptionalSettings() {
		// Categories refered to as collections
		// pwcommerce_is_category_collection
		// custom root page and children
		// pwcommerce_is_use_custom_shop_root_page; pwcommerce_custom_shop_root_page_id; pwcommerce_custom_shop_root_page_children and pwcommerce_custom_shop_root_page_children_page_tree_management

		// ===
		$input = $this->actionInput;
		$isCategoryACollection = (int) $input->pwcommerce_is_category_collection;
		$isUseCustomShopRootPage = (int) $input->pwcommerce_is_use_custom_shop_root_page;
		$customShopRootPageID = (int) $input->pwcommerce_custom_shop_root_page_id;
		$customShopRootPageChildren = $this->wire('sanitizer')->array($input->pwcommerce_custom_shop_root_page_children, 'fieldName');
		$customShopRootPageChildrenPageTreeManagement = $this->wire('sanitizer')->fieldName($input->pwcommerce_custom_shop_root_page_children_page_tree_management);
		if (!is_array($customShopRootPageChildren)) {
			$customShopRootPageChildren = [];
		}

		// set if product categories feature is installed or incoming
		$optionalFeatures = $input->pwcommerce_configure_install_optional_feature;
		if (is_null($optionalFeatures)) {
			$optionalFeatures = [];
		}
		$isAvailableProductCategoriesFeature = in_array('product_categories', $optionalFeatures);
		$otherOptionalSettings = [];

		# module configs: 'pwcommerce_is_category_collection'
		// @note: we don't save blank
		if (!empty($isCategoryACollection) && !empty($isAvailableProductCategoriesFeature)) {
			$otherOptionalSettings['pwcommerce_is_category_collection'] = $isCategoryACollection;
		}

		# module configs: 'pwcommerce_is_use_custom_shop_root_page','pwcommerce_custom_shop_root_page_id','pwcommerce_custom_shop_root_page_children', 'pwcommerce_custom_shop_root_page_children_page_tree_management'
		// @note: we don't save blanks

		if (!empty($isUseCustomShopRootPage)) {
			// if using a custom shop root page
			if (!empty($customShopRootPageID) && !empty($customShopRootPageChildren)) {
				// if we have the root page ID and its children sent
				$otherOptionalSettings['pwcommerce_is_use_custom_shop_root_page'] = $isUseCustomShopRootPage;
				// -------
				$otherOptionalSettings['pwcommerce_custom_shop_root_page_id'] = $customShopRootPageID;
				$otherOptionalSettings['pwcommerce_custom_shop_root_page_children'] = $customShopRootPageChildren;
				$otherOptionalSettings['pwcommerce_custom_shop_root_page_children_page_tree_management'] = $customShopRootPageChildrenPageTreeManagement;
			}

		}

		// -----
		// @note: for page and template field contexts, we always process
		// i.e. might need to revert to 'categories' from collections
		# categories page process
		$this->processOtherOptionalSettingsCategoriesAsCollections($isCategoryACollection);

		// ++++++++++

		# pwcommerce-product field 'pwcommerce_categories' process
		// $this->processOtherOptionalSettingsCustomShopRootPage($isUseCustomShopRootPage, $customShopRootPageID, $customShopRootPageChildren);
		$this->processOtherOptionalSettingsCustomShopRootPage();

		// TODO BASED ON THE MODIFICATION PROCESS BY processOtherOptionalSettingsCategoriesAsCollections() and processOtherOptionalSettingsCustomShopRootPage(), WE MIGHT NEED TO UNSET SOME ITEMS IN $otherOptionalSettings! E.G. REMOVE CATEGORIES IF SKIPPED IN ABOVE PROCESSES SINCE CATEGORY FEATURE NOT INSTALLED!

		// ===========
		// REMOVE PARENT PAGES WHOSE FEATURE HAVE BEEN UNINSTALLED!
		// i.f. if 'Product Types' was removed as a feature, we need to unset it from selected/ticked parent pages!
		// @note: this was set in $this->partialPostRemovalModificationOfProcessCustomShopRootSettings() via $this->partialModificationOfPWCommerceRemovalAction()
		if (!empty($this->removedOptionalFeaturesForCustomShopRootPageParentPages)) {
			if (!empty($otherOptionalSettings['pwcommerce_custom_shop_root_page_children'])) {
				foreach ($otherOptionalSettings['pwcommerce_custom_shop_root_page_children'] as $key => $feature) {
					if (in_array($feature, $this->removedOptionalFeaturesForCustomShopRootPageParentPages)) {
						// remove the parent page item!
						unset($otherOptionalSettings['pwcommerce_custom_shop_root_page_children'][$key]);
					}
				}
			}
		}

		# HANDLE POST-PROCESSING ISSUES
		// handle error about missing feature selected as parent page
		if (!empty($this->missingFeaturesForOptionalSettings) && !empty($isUseCustomShopRootPage)) {
			$incomingParentPagesItems = [];
			if (!empty($otherOptionalSettings['pwcommerce_custom_shop_root_page_children'])) {
				$incomingParentPagesItems = $otherOptionalSettings['pwcommerce_custom_shop_root_page_children'];
			}

			$missingParentPagesItems = $this->missingFeaturesForOptionalSettings;

			$remainingParentPageItems = array_diff($incomingParentPagesItems, $missingParentPagesItems);
			if (empty($remainingParentPageItems)) {
				// ERROR
				$this->isErrorCustomShopRootPageValues = true;
				$this->isRevertCustomShopRootPageValues = true;
				$this->error($this->_('At least one valid parent page to install under custom shop root page needs to be selected.'));
				// UNSET ALL RELATED VALUES
				unset($otherOptionalSettings['pwcommerce_is_use_custom_shop_root_page']);
				// -------
				unset($otherOptionalSettings['pwcommerce_custom_shop_root_page_id']);
				unset($otherOptionalSettings['pwcommerce_custom_shop_root_page_children']);
				unset($otherOptionalSettings['pwcommerce_custom_shop_root_page_children_page_tree_management']);
			} else {
				// UPDATE WITH NON-MISSING ONLY
				$otherOptionalSettings['pwcommerce_custom_shop_root_page_children'] = $remainingParentPageItems;
				// warn user
				$this->warning($this->_('Missing features must be installed before selecting them for install under custom shop root page.'));
			}

		}

		// handle errors reauiring to abort saving!
		if (!empty($this->isErrorCustomShopRootPageValues) && empty($this->isRevertCustomShopRootPageValues)) {
			// ===========

			# +++++++++ PREPARE SAVED VALUES +++++++++++++++

			$installedOtherOptionalSettings = $this->pwcommerce->getPWCommerceInstalledOtherOptionalSettings($this->configModuleName);

			// USE CUSTOM ROOT PAGE?
			if (!empty($installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_IS_USE_CUSTOM_SHOP_ROOT_PAGE_SETTING_NAME])) {
				$otherOptionalSettings['pwcommerce_is_use_custom_shop_root_page'] = $installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_IS_USE_CUSTOM_SHOP_ROOT_PAGE_SETTING_NAME];
			}
			// CUSTOM ROOT PAGE ID
			if (!empty($installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_ID_SETTING_NAME])) {
				$otherOptionalSettings['pwcommerce_custom_shop_root_page_id'] = (int) $installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_ID_SETTING_NAME];
			}
			// CUSTOM ROOT PAGE CHILDREN
			// i.e. pwcommerce parent pages that will be children of this custom root page
			if (!empty($installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_SETTING_NAME])) {
				$otherOptionalSettings['pwcommerce_custom_shop_root_page_children'] = $installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_SETTING_NAME];
			}
			// CUSTOM ROOT PAGE CHILDREN TREE MANAGEMENT
			// i.e. HOW TO HANDLE the pwcommerce parent pages that will be children of this custom root page IN THE PW PAGE TREE
			if (!empty($installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_PAGE_TREE_MANAGEMENT_SETTING_NAME])) {
				$otherOptionalSettings['pwcommerce_custom_shop_root_page_children_page_tree_management'] = $installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_PAGE_TREE_MANAGEMENT_SETTING_NAME];
			}
		}

		// -----

		return $otherOptionalSettings;

	}

	/**
	 * Process Other Optional Settings Categories As Collections.
	 *
	 * @param mixed $incomingIsCategoryACollection
	 * @return mixed
	 */
	private function processOtherOptionalSettingsCategoriesAsCollections($incomingIsCategoryACollection) {

		// TODO NOT IN USE FOR NOW; THIS IS BECAUSE ONE MIGHT HAVE INSTALLED CATEGORIES BUT THE MODULE CONFIGS NOT YET SAVED; THE CONFIGS ARE SAVED AFTER ALL RUNINSTALLER OPERATIONS OF WHICH THIS IS PART (VIA $this->processOtherOptionalSettings())
		// INSTEAD JUST CHECK IF CATEGORIES PAGE FOUND AND OPTIONALLY IF PRODUCT TEMPLATE HAS FIELD 'pwcommerce_categories'
		// $categoriesFeature = 'product_categories';
		// $isCategoriesFeatureInstalled = $this->pwcommerce->isOptionalFeatureInstalled($categoriesFeature);

		$sanitizer = $this->wire('sanitizer');
		$pages = $this->wire('pages');

		// i. In Fresh Install
		// if (empty($this->isSecondStageInstallConfiguration)) {
		// 	$categoriesPage = $pages->get('template=pwcommerce-categories');
		// 		// 	if (!empty($categoriesPage->id)) {
		// 		$categoriesPage->title = 'Collections';
		// 		$categoriesPage->name = $sanitizer->pageName($categoriesPage->title);
		// 		$categoriesPage->save();
		// 	}
		// }
		// ii. Second stage install

		$savedIsCategoryACollection = $this->pwcommerce->isOtherOptionalSettingInstalled(PwCommerce::PWCOMMERCE_IS_CATEGORY_A_COLLECTION_SETTING_NAME);

		if ((int) $savedIsCategoryACollection !== (int) $incomingIsCategoryACollection) {
			# change has occured

			$categoriesPageTitle = (int) $incomingIsCategoryACollection === 1 ? 'Collections' : 'Categories';

			// ++++ TITLE CHANGE ++++
			$categoriesPage = $pages->get('template=pwcommerce-categories');
			if (!empty($categoriesPage->id)) {
				$categoriesPage->title = $categoriesPageTitle;
				$categoriesPage->name = $sanitizer->pageName($categoriesPage->title, true);
				$categoriesPage->save();
			}

			// ++++ LABEL CHANGE ++++
			$template = $this->wire('templates')->get("name=pwcommerce-product");
			// get field group for template 'pwcommerce-product'
			$templateFieldgroup = $template->fieldgroup;
			// get 'pwcommerce_categories' field in context of the template 'pwcommerce-product'
			$categoriesFieldContext = $templateFieldgroup->getFieldContext('pwcommerce_categories');
			if (!empty($categoriesFieldContext)) {
				// change the field label in context (override)
				// @note IF LABEL IS REVERTING, PW will automatically remove the override in context.
				$label = $categoriesPageTitle === 'Collections' ? $categoriesPageTitle : 'Categories';
				$categoriesFieldContext->label = $label;
				// save field settings in context of template 'pwcommerce-product'
				$this->wire('fields')->saveFieldgroupContext($categoriesFieldContext, $templateFieldgroup);
			}
		}

	}

	// /**
  * Process Other Optional Settings Custom Shop Root Page.
  *
  * @param mixed $incomingIsUseCustomShopRootPage
  * @param int $incomingCustomShopRootPageID
  * @param mixed $incomingCustomShopRootPageChildren
  * @return mixed
  */
 private function processOtherOptionalSettingsCustomShopRootPage($incomingIsUseCustomShopRootPage, $incomingCustomShopRootPageID, $incomingCustomShopRootPageChildren) {
	/**
	 * Process Other Optional Settings Custom Shop Root Page.
	 *
	 * @return mixed
	 */
	private function processOtherOptionalSettingsCustomShopRootPage() {

		$pages = $this->wire('pages');
		$input = $this->actionInput;

		# +++++++++ PREPARE INCOMING VALUES +++++++++++++++

		// this is the ID of the main parent page for all other pwcommerce parent pages
		// the page is the only child of the ProcessPWCommerce admin page 'Shop'
		$shopAdminPWCommerceRootPageID = (int) $input->pwcommerce_shop_admin_pwcommerce_root_page_id;
		// -------------
		$incomingIsUseCustomShopRootPage = (int) $input->pwcommerce_is_use_custom_shop_root_page;
		$incomingCustomShopRootPageID = (int) $input->pwcommerce_custom_shop_root_page_id;
		// @note SANITIZER FIELDNAME
		$incomingParentPagesItems = $this->wire('sanitizer')->array($input->pwcommerce_custom_shop_root_page_children, 'fieldName');
		$incomingParentPagesPageTreeManagement = $this->wire('sanitizer')->fieldName($input->pwcommerce_custom_shop_root_page_children_page_tree_management);

		// ========

		# +++++++++ PREPARE SAVED VALUES +++++++++++++++

		$installedOtherOptionalSettings = $this->pwcommerce->getPWCommerceInstalledOtherOptionalSettings($this->configModuleName);

		// USE CUSTOM ROOT PAGE?
		if (!empty($installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_IS_USE_CUSTOM_SHOP_ROOT_PAGE_SETTING_NAME])) {
			$savedSettingForIsUseCustomShopRootPage = true;
		} else {
			$savedSettingForIsUseCustomShopRootPage = false;
		}
		// CUSTOM ROOT PAGE ID
		if (!empty($installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_ID_SETTING_NAME])) {
			$savedCustomShopRootPageID = (int) $installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_ID_SETTING_NAME];
		} else {
			$savedCustomShopRootPageID = 0;
		}

		// CUSTOM ROOT PAGE CHILDREN
		// i.e. pwcommerce parent pages that will be children of this custom root page
		if (!empty($installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_SETTING_NAME])) {
			$savedParentPagesItems = $installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_SETTING_NAME];
		} else {
			$savedParentPagesItems = [];
		}

		// CUSTOM ROOT PAGE CHILDREN TREE MANAGEMENT
		// i.e. HOW TO HANDLE the pwcommerce parent pages that will be children of this custom root page IN THE PW PAGE TREE
		if (!empty($installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_PAGE_TREE_MANAGEMENT_SETTING_NAME])) {
			$savedParentPagesPageTreeManagement = $installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_PAGE_TREE_MANAGEMENT_SETTING_NAME];
		} else {
			$savedParentPagesPageTreeManagement = 'not_visible_in_page_tree';
		}

		// **************
		// $customShopRootPageAllowedChildrenDetails = $this->getCustomShopRootPageAllowedChildrenDetails();
		// $childrenTemplatesNames = $customShopRootPageAllowedChildrenDetails['templates'];

		// TODO NEED TO CATER FOR MODIFICATIONS; E.G. ADDING PARENT PAGE OR REMOVING OR MIX!

		# +++++++++ DO PRELIMINARY CHECKS +++++++++++++++

		// EMPTY SETTING 'is use custom root page'
		if (empty($incomingIsUseCustomShopRootPage)) {
			// 1. SET DELETE ALL CONFIGS FOR 'CUSTOM ROOT PAGE', i.e.'pwcommerce_is_use_custom_shop_root_page', 'pwcommerce_custom_shop_root_page_id' and 'pwcommerce_custom_shop_root_page_children
			// set TO CLASS PROPERTY
			$this->isRevertCustomShopRootPageValues = true;
			//  -------
			// 2. REVERT ANY PREVIOUS CHANGES if applicable
			$this->revertCustomShopRootPageValues();

		} else {
			// HANDLE NEW|UNCHANGED|REMOVED ITEMS
			// BOTH CUSTOM ROOT PAGE ID and PARENT PAGES FOR THIS CUSTOM ROOT PAGE SHOULD NOT BE EMPTY
			// -------
			# HANDLE ERRORS
			if (empty($incomingCustomShopRootPageID)) {
				$this->isErrorCustomShopRootPageValues = true;
				$this->error($this->_('A custom shop root page needs to be specified.'));
				return;
			} else if (empty($incomingParentPagesItems)) {
				$this->isErrorCustomShopRootPageValues = true;
				$this->error($this->_('At least one parent page to install under custom shop root page needs to be selected.'));
				return;
			}
			# =================
			# GOOD TO GO

			$isChangedCustomShopRootPageID = $savedCustomShopRootPageID !== $incomingCustomShopRootPageID;

			// ----------INTERSECT -------
			// 1. UNCHANGED ITEMS: saved INTERSECT incoming
			$unchangedParentPagesItems = array_intersect($savedParentPagesItems, $incomingParentPagesItems);
			if (!empty($unchangedParentPagesItems) && !empty($isChangedCustomShopRootPageID)) {
				// PROCESS UNCHANGED ITEMS SINCE CUSTOM SHOP ROOT PAGE HAS CHANGED
				$this->processCustomShopRootPageParentItemsValues($unchangedParentPagesItems, $incomingCustomShopRootPageID);
			}

			// ----------DIFF -------
			// 2. NEW ITEMS: incoming DIFF saved
			$newParentPagesItems = array_diff($incomingParentPagesItems, $savedParentPagesItems);
			if (!empty($newParentPagesItems)) {
				$this->processCustomShopRootPageParentItemsValues($newParentPagesItems, $incomingCustomShopRootPageID);
			}

			// 3. REMOVED ITEMS: saved DIFF incoming
			$removedParentPagesItems = array_diff($savedParentPagesItems, $incomingParentPagesItems);
			if (!empty($removedParentPagesItems)) {
				// @note: THIS IS A REVERSAL! Hence new parent will be the default parent 'PWCommerce'
				$this->processCustomShopRootPageParentItemsValues($removedParentPagesItems, $shopAdminPWCommerceRootPageID);
			}
		}

	}

	/**
	 * Revert Custom Shop Root Page Values.
	 *
	 * @return mixed
	 */
	private function revertCustomShopRootPageValues() {

		$installedOtherOptionalSettings = $this->pwcommerce->getPWCommerceInstalledOtherOptionalSettings($this->configModuleName);
		// PREVIOUS SAVED 'USE CUSTOM ROOT PAGE?'
		if (empty($installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_IS_USE_CUSTOM_SHOP_ROOT_PAGE_SETTING_NAME])) {
			return;
		}

		$pages = $this->wire('pages');
		$input = $this->actionInput;

		// +++++++++++++++
		// this is the ID of the main parent page for all other pwcommerce parent pages
		// the page is the only child of the ProcessPWCommerce admin page 'Shop'
		$shopAdminPWCommerceRootPageID = (int) $input->pwcommerce_shop_admin_pwcommerce_root_page_id;
		$shopAdminPWCommerceRootPage = $pages->get($shopAdminPWCommerceRootPageID);
		// ------------

		# WE REVERT ALL POSSIBLE ALLOWED CHILDREN (PWCOMMERCE PARENT PAGES) of the custom root page
		//  we revert both parentTemplates() and parent

		$customShopRootPageAllowedChildrenDetails = $this->getCustomShopRootPageAllowedChildrenDetails();

		$childrenTemplatesNames = $customShopRootPageAllowedChildrenDetails['templates'];

		foreach ($childrenTemplatesNames as $feature => $templateName) {

			// @note: we assume no user-set restrictions will affect this re-parenting!
			$parentPage = $pages->get("template={$templateName}");
			if ($parentPage instanceof NullPage) {
				// @note: we don't expect 'products' in here. TODO?
				$this->missingFeaturesForOptionalSettings[] = $feature;
				continue;
			}

			// +++++++++++++
			// GOOD TO GO

			// A. AMMEND ALLOWED PARENT TEMPLATES
			// get the template first
			// $template = $this->wire('templates')->get($templateName);
			$template = $parentPage->template;

			// GRAB PWCOMMERCE DEFAULTS FOR THIS TEMPLATE
			$templateData = $this->getPWCommerceTemplateDataByName($templateName);
			$parentTemplates = [];
			if (!empty($templateData['parentTemplates'])) {
				$parentTemplates = $templateData['parentTemplates'];
			}

			$template->parentTemplates($parentTemplates);
			// save the template
			$template->save();

			// +++++++++++++

			// B. CHANGE PARENT TO 'DEFAULT PWCOMMERCE ROOT PAGE'
			$parentPage->parent = $shopAdminPWCommerceRootPage;
			$parentPage->save();

		}
	}

	/**
	 * Process root page values for some pwcommerce parent pages.
	 *
	 * @param mixed $parentPagesItems
	 * @param int $shopRootPageID
	 * @return mixed
	 */
	private function processCustomShopRootPageParentItemsValues($parentPagesItems, $shopRootPageID) {

		// ----------
		$pages = $this->wire('pages');
		$shopRootPage = $pages->get($shopRootPageID);
		$shopRootPageTemplateName = $shopRootPage->template->name;

		// TODO DISALLOW USE OF PWCOMMERCE PAGES AS CUSTOM SHOP PARENT PAGE EXCEPT FOR TEMPLATE 'pwcommerce'
		// i.e., prevent accidental/deliberate selection of a pwcommerce page (even under a custom shop root page) as CUSTOM SHOP ROOT PAGE
		// this also prevents recursion! e.g. 'products' parent cannot be 'products'!
		$pwcommerceTemplatesNames = $this->getPWCommerceTemplatesNames();

		if ($shopRootPageTemplateName !== 'pwcommerce' && in_array($shopRootPageTemplateName, $pwcommerceTemplatesNames)) {
			$this->isErrorCustomShopRootPageValues = true;
			$this->error($this->_('Cannot use a PWCommerce page as a custom shop root page!'));
			return;
		}

		// ++++++++++++++++++++++

		$shopRootPageAllowedChildrenDetails = $this->getCustomShopRootPageAllowedChildrenDetails();

		$childrenTemplatesNames = $shopRootPageAllowedChildrenDetails['templates'];

		foreach ($childrenTemplatesNames as $feature => $templateName) {

			if (!in_array($feature, $parentPagesItems)) {
				continue;
			}

			// @note: we assume no user-set restrictions will affect this re-parenting!
			$parentPage = $pages->get("template={$templateName}");
			if ($parentPage instanceof NullPage) {
				// @note: we don't expect 'products' in here. TODO?
				$this->missingFeaturesForOptionalSettings[] = $feature;
				continue;
			}

			// +++++++++++++
			// GOOD TO GO

			// A. AMEND ALLOWED PARENT TEMPLATES
			// get the template first
			// $template = $this->wire('templates')->get($templateName);
			$template = $parentPage->template;

			// GRAB PWCOMMERCE DEFAULTS FOR THIS TEMPLATE
			$templateData = $this->getPWCommerceTemplateDataByName($templateName);
			$parentTemplates = [];
			if (!empty($templateData['parentTemplates'])) {
				$parentTemplates = $templateData['parentTemplates'];
			}

			// A. AMEND ALLOWED PARENT TEMPLATES
			// we also do this first so as not to affect re-parenting below
			$parentTemplates[] = $shopRootPage->template->name;

			$template->parentTemplates($parentTemplates);
			// save the template
			$template->save();

			// +++++++++++++

			// B. CHANGE PARENT TO 'CUSTOM SHOP ROOT PAGE'
			$parentPage->parent = $shopRootPage;
			$parentPage->save();

		}
	}

	/**
	 * Get Custom Shop Root Page Allowed Children Details.
	 *
	 * @return mixed
	 */
	private function getCustomShopRootPageAllowedChildrenDetails() {
		$customShopRootPageAllowedChildrenDetails = $this->pwcommerce->getCustomShopRootPageAllowedChildrenDetails();		// ------
		return $customShopRootPageAllowedChildrenDetails;
	}

	// ~~~~~~~~~~~

	## PWCOMMERCE RUN UNINSTALLER ##

	/**
	 * Complete Removal Of P W Commerce Action.
	 *
	 * @return mixed
	 */
	public function completeRemovalOfPWCommerceAction() {
		// REMOVE PAGES
		$this->completeRemovalOfPWCommerceDeletePages();
		// //  REMOVE TEMPLATES
		$this->completeRemovalOfPWCommerceDeleteTemplatesAndFieldgroups();
		// // REMOVE FIELDS
		$this->completeRemovalOfPWCommerceDeleteFields();
		// // UNINSTALL FIELDS
		$this->completeRemovalOfPWCommerceUninstallFields();
		// DROP PWCOMMERCE CUSTOM TABLES: 'pwcommerce_order_status', 'pwcommerce_cart', ETC
		$this->completeRemovalOfPWCommerceDropCustomTables();
		// REMOVE PWCOMMERCE ROLES
		$this->completeRemovalOfPWCommerceDeleteRoles();
		// INVALIDATE PWCOMMERCE FINDANYTHING CACHE
		// $this->invalidatePWCommerceFindAnythingCache();
		// UNINSTALL THE MAIN PWCOMMERCE MODULES
		// @note: not in use for now. Devs will have to manually uninstall ProcessPWCommerce. It will also uninstall PWCommerceHooks and PWCommerce modules
		// --------------
		// SAVE CONFIGS
		// reset to not configured TODO ok?
		// TODO: for now we set to first install just in case they change their mind
		// otherwise they can uninstall ProcessPWCommerce and that will uninstall PWCommerceHooks and PWCommerce modules as well as delete ProcessPWCommerce module config + Process page!
		$data = ['pwcommerce_install_configuration_status' => PwCommerce::PWCOMMERCE_FIRST_STAGE_INSTALL_CONFIGURATION_STATUS];
		$this->pwcommerce->setPWCommerceModuleConfigs($data, $this->configModuleName);

		// ------
		// TODO WIP!
		$result = [];
		$notice = $this->_('PWCommerce Shop completely removed successfully.');
		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO? check first?

		];
		// -----
		return $result;
	}

	/**
	 * Complete Removal Of P W Commerce Delete Pages.
	 *
	 * @return mixed
	 */
	private function completeRemovalOfPWCommerceDeletePages() {
		// find pwcommerce pages to remove
		// we get this by finding and including all the children of the shop admin page
		// @note: this is the 'pwcommerce' page under the main process module
		// it is an only child of the process module's admin page 'shop'
		// it is the parent of all sections parent pages, i.e. products, orders, categories, settings, etc
		// $parentPages = $this->wire('pages')->find("parent_id={$this->shopAdminPWCommerceRootPageID},include=all");
		// $this->deletePages($parentPages);
		// TODO FOR NOW WE JUST DELETE VIA THE top-most parent page, i.e. mainShopPage called 'pwcommerce'
		// @note: we use find so we can get a PageArray to pass to deletePages()
		// $mainShopPage = $this->wire('pages')->find("id={$this->shopAdminPWCommerceRootPageID},include=all");
		// @UPDATE: SINCE PWCOMMERCE 009, SOME SHOP FEATURES CAN LIVE UNDER A NAMED CUSTOM ROOT PAGE. WE ALSO NEED TO FIND THEM!

		$mainShopPagesSelector = $this->getMainShopPagesSelectorForCompleteRemovalOfPWCommerceDeletePages();
		$mainShopPages = $this->wire('pages')->find($mainShopPagesSelector);

		# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		# 1. DELETE PWCOMMERCE PARENT PAGES AND THEIR DESCENDANT PAGES
		$parentPagesToDelete = $this->findPagesToDelete();
		foreach ($parentPagesToDelete as $page) {
			$this->deletePageAndDescendants($page);
		}
		# 2. DELETE PWCOMMERCE TOP/ROOT PAGE
		// @NOTE: THIS WILL NOT TAKE CARE OF PWCOMMERCE PAGES IN THE TRASH! WE DO THAT NEXT
		$this->deletePages($mainShopPages);
		# 3. DELETE PWCOMMERCE PAGES THAT MAY BE IN THE TRASH
		$this->deleteTrashedPWCommercePages();
	}

	/**
	 * Get Main Shop Pages Selector For Complete Removal Of P W Commerce Delete Pages.
	 *
	 * @return mixed
	 */
	private function getMainShopPagesSelectorForCompleteRemovalOfPWCommerceDeletePages() {
		$mainShopPagesSelector = "id={$this->shopAdminPWCommerceRootPageID},include=all";

		$isUseCustomShopRootPage = $this->pwcommerce->isOtherOptionalSettingInstalled(PwCommerce::PWCOMMERCE_IS_USE_CUSTOM_SHOP_ROOT_PAGE_SETTING_NAME);
		if (!empty($isUseCustomShopRootPage)) {
			// CUSTOM SHOP ROOT PAGE IN USE
			// find the pwcommerce parent pages  features living under it
			// PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_SETTING_NAME
			$installedOtherOptionalSettings = $this->pwcommerce->getPWCommerceInstalledOtherOptionalSettings($this->configModuleName);
			$parentPagesItems = $installedOtherOptionalSettings[PwCommerce::PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_SETTING_NAME];
			if (!empty($parentPagesItems)) {
				$parentPagesItemsTemplates = [];
				$customShopRootPageAllowedChildrenDetails = $this->getCustomShopRootPageAllowedChildrenDetails();
				$childrenTemplatesNames = $customShopRootPageAllowedChildrenDetails['templates'];
				// GET THE TEMPLATES!
				foreach ($childrenTemplatesNames as $feature => $templateName) {
					if (!in_array($feature, $parentPagesItems)) {
						continue;
					}
					// -------
					$parentPagesItemsTemplates[] = $templateName;
				}
				$parentPagesItemsTemplatesSelector = implode("|", $parentPagesItemsTemplates);
				// ++++++
				// @NOTE: OR:GROUPS
				$mainShopPagesSelector = "(id={$this->shopAdminPWCommerceRootPageID}),(template={$parentPagesItemsTemplatesSelector}),include=all";
			}
		} else {
			// CUSTOM ROOT PAGE NOT IN USE
			$mainShopPagesSelector = "id={$this->shopAdminPWCommerceRootPageID},include=all";
		}
		// ---------
		return $mainShopPagesSelector;
	}

	/**
	 * Delete Pages.
	 *
	 * @param PageArray $pages
	 * @return mixed
	 */
	private function deletePages(PageArray $pages) {
		foreach ($pages as $page) {
			// deal with locked pages first, if any
			if ($page->isLocked()) {
				$page->removeStatus(Page::statusLocked);
			}
			# READY TO DELETE
			// +++++++
			// delete page and its children recursively
			$this->wire('pages')->delete($page, true);
		}
	}

	/**
	 * Delete Page And Descendants.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function deletePageAndDescendants(Page $page) {
		// deal with locked pages first, if any
		if ($page->isLocked()) {
			$page->removeStatus(Page::statusLocked);
		}
		// check if page has locked child pages
		$lockedChildren = $this->findLockedChildrenPages($page);
		if (!empty($lockedChildren)) {
			// UNLOCK THEM FIRST!
			$this->unlockLockedChildrenPages($lockedChildren);
		}
		# READY TO DELETE
		// +++++++
		// delete page and its children recursively
		$this->wire('pages')->delete($page, true);
		// free some memory
		$this->wire('pages')->uncacheAll();
	}

	/**
	 * Find Pages To Delete.
	 *
	 * @return mixed
	 */
	private function findPagesToDelete() {
		$availableTemplatesNames = $this->getPWCommerceAvailableParentTemplatesNames();
		$availableTemplatesNamesSelector = implode("|", $availableTemplatesNames);
		$parentPages = $this->wire('pages')->find("template={$availableTemplatesNamesSelector}");
		// -----
		return $parentPages;
	}


	/**
	 * Find Locked Children Pages.
	 *
	 * @param mixed $parentPage
	 * @return mixed
	 */
	private function findLockedChildrenPages($parentPage) {
		$lockedChildren = $this->pwcommerce->findRaw("has_parent={$parentPage},include=all,status>=" . Page::statusLocked, 'id');
		// $lockedChildren = $this->wire('pages')->findRaw("parent={$parentPage},status>=" . Page::statusLocked, 'id');
		// ------
		return $lockedChildren;
	}

	/**
	 * Unlock Locked Children Pages.
	 *
	 * @param array $lockedChildrenIDs
	 * @return mixed
	 */
	private function unlockLockedChildrenPages(array $lockedChildrenIDs) {
		$lockedChildrenIDsSelector = implode("|", $lockedChildrenIDs);
		// we don't expect any parent page to have more than 200 children!
		// OK? VARIANTS? - BUT NOT LOCKABLE VIA GUI; SO IF LOCKED, THEN DELIBERATE? API?
		// TODO - LOOK INTO ABOVE!
		$lockedChildrenPages = $this->wire('pages')->find("id={$lockedChildrenIDsSelector},include=all");
		foreach ($lockedChildrenPages as $lockedChildPage) {
			$lockedChildPage->removeStatus(Page::statusLocked);
			// TODO OK HERE? - NO; IT WILL CLEAR THE SET UNLOCKED IN-MEMORY STATUS! DO IT AFTER
			// $this->wire('pages')->uncacheAll();
		}
	}

	/**
	 * Get P W Commerce Installed Optional Features Parent Template Names.
	 *
	 * @return mixed
	 */
	private function getPWCommerceInstalledOptionalFeaturesParentTemplateNames() {
		$installedOptionalFeaturesParentTemplateNames = [];
		$prefix = "pwcommerce-";
		$installedOptionalFeatures = $this->pwcommerce->getPWCommerceInstalledOptionalFeatures(PwCommerce::PWCOMMERCE_PROCESS_MODULE);
		if (!empty($installedOptionalFeatures)) {
			foreach ($installedOptionalFeatures as $installedOptionalFeature) {
				// skip inventory feature; it has no separate template!
				if (str_contains($installedOptionalFeature, "inventory")) {
					continue;
				}
				// ---------
				// remove 'product_' in 'product' templates
				$parentTemplateName = str_replace("product_", "", $installedOptionalFeature);
				// convert '_' to '-' in order to build the template name
				$parentTemplateName = str_replace("_", "-", $parentTemplateName);
				$parentTemplateName = "{$prefix}{$parentTemplateName}";
				$installedOptionalFeaturesParentTemplateNames[] = $parentTemplateName;
			}
		}
		return $installedOptionalFeaturesParentTemplateNames;


	}

	/**
	 * Get P W Commerce Required Features Parent Templates Names.
	 *
	 * @return mixed
	 */
	private function getPWCommerceRequiredFeaturesParentTemplatesNames() {
		return [
			// 'products'
			'pwcommerce-products',
			// @note: dependency is 'product_attributes'
			// 'pwcommerce-product-variant'
			// 'orders'
			'pwcommerce-orders',
			// 'pwcommerce-order-line-item'
			// 'shipping'
			'pwcommerce-shipping-zones',
			// 'pwcommerce-shipping-rate'
			//  'taxes'
			'pwcommerce-countries',
			// 'pwcommerce-country-territory'
			// 'settings'
			'pwcommerce-settings'
		];
	}


	/**
	 * Get P W Commerce Available Parent Templates Names.
	 *
	 * @return mixed
	 */
	private function getPWCommerceAvailableParentTemplatesNames() {
		$pwcommerceRequiredTemplatesNames = $this->getPWCommerceRequiredFeaturesParentTemplatesNames();
		$pwcommerceOptionalFeaturesTemplatesNames = $this->getPWCommerceInstalledOptionalFeaturesParentTemplateNames();
		$availableTemplatesNames = array_merge($pwcommerceRequiredTemplatesNames, $pwcommerceOptionalFeaturesTemplatesNames);
		// -------
		return $availableTemplatesNames;
	}

	/**
	 * Delete Trashed P W Commerce Pages.
	 *
	 * @return mixed
	 */
	private function deleteTrashedPWCommercePages() {
		$trashedPWCommercePages = $this->findTrashedPWCommercePages();
		if ($trashedPWCommercePages->count()) {
			$this->deletePages($trashedPWCommercePages);
		}
	}

	/**
	 * Find Trashed P W Commerce Pages.
	 *
	 * @return mixed
	 */
	private function findTrashedPWCommercePages() {
		$templatesNamesSelector = $this->getPWCommerceTemplatesNamesSelector();
		$trashedPWCommercePages = $this->wire('pages')->find("template={$templatesNamesSelector}, include=all, has_parent=" . $this->wire('config')->trashPageID);
		// ----
		return $trashedPWCommercePages;
	}

	/**
	 * Complete Removal Of P W Commerce Delete Templates And Fieldgroups.
	 *
	 * @return mixed
	 */
	private function completeRemovalOfPWCommerceDeleteTemplatesAndFieldgroups() {
		// TODO DELETE WHEN DONE
		// $pwcommerceTemplatesNames = array_keys($this->getPWCommerceTemplatesData());
		// $templatesNamesSelector = implode("|", $pwcommerceTemplatesNames);
		$templatesNamesSelector = $this->getPWCommerceTemplatesNamesSelector();
		// --------------
		$templates = $this->wire('templates');
		$pwcommerceTemplates = $templates->find("name={$templatesNamesSelector}");
		$this->deleteTemplatesAndFieldgroups($pwcommerceTemplates);
	}

	/**
	 * Get P W Commerce Templates Names.
	 *
	 * @return mixed
	 */
	private function getPWCommerceTemplatesNames() {
		$pwcommerceTemplatesNames = array_keys($this->getPWCommerceTemplatesData());

		return $pwcommerceTemplatesNames;
	}

	/**
	 * Get P W Commerce Templates Names Selector.
	 *
	 * @return mixed
	 */
	private function getPWCommerceTemplatesNamesSelector() {
		$pwcommerceTemplatesNames = $this->getPWCommerceTemplatesNames();
		$templatesNamesSelector = implode("|", $pwcommerceTemplatesNames);
		return $templatesNamesSelector;
	}

	/**
	 * Delete Templates And Fieldgroups.
	 *
	 * @param TemplatesArray $templates
	 * @return mixed
	 */
	private function deleteTemplatesAndFieldgroups(TemplatesArray $templates) {
		foreach ($templates as $template) {
			$fieldgroup = $template->fieldgroup;
			// ---------------
			// DELETE THE TEMPLATE FIRST
			$this->wire('templates')->delete($template);
			// THEN DELETE ITS FIELDGROUP
			$this->wire('fieldgroups')->delete($fieldgroup);
		}
	}

	// ~~~~~~~~~~~~~~~~

	/**
	 * Complete Removal Of P W Commerce Delete Fields.
	 *
	 * @return mixed
	 */
	private function completeRemovalOfPWCommerceDeleteFields() {
		$pwcommerceFieldsNames = array_keys($this->getPWCommerceFieldsData());
		$fieldsNamesSelector = implode("|", $pwcommerceFieldsNames);
		// --------------
		$fields = $this->wire('fields');
		$pwcommerceFields = $fields->find("name={$fieldsNamesSelector}");
		// prepare the names of the PWCommerce Fieldtypes and Inputfields modules to late uninstall
		$this->preparePWCommerceFieldtypesAndInputfieldsForCompleteRemoval($pwcommerceFields);
		// ------------
		// delete the fields
		$this->deleteFields(($pwcommerceFields));
	}

	/**
	 * Prepare P W Commerce Fieldtypes And Inputfields For Complete Removal.
	 *
	 * @param FieldsArray $fields
	 * @return mixed
	 */
	private function preparePWCommerceFieldtypesAndInputfieldsForCompleteRemoval(FieldsArray $fields) {

		$fieldtypesAndInputfieldsToUninstall = [];
		foreach ($fields as $field) {
			// get the Inputfield (class) for the field
			// NOTE: for Fieldtypes without inputfields, we will skip them in this->completeRemovalOfPWCommerceUninstallFields()
			$inputfield = $field->getInputfield(new NullPage());
			$inputfieldClassName = $inputfield->className;
			// get the Fieldtype (class) for the field
			$fieldType = $field->type;
			$fieldtypeClassName = str_replace("ProcessWire\\", "", get_class($fieldType));
			// WE ONLY WANT PWCOMMERCE FIELDTYPES
			if (strpos($fieldtypeClassName, 'PWCommerce') === false)
				continue;
			// -----------
			//
			// add to array to uninstall later
			$fieldtypesAndInputfieldsToUninstall[$fieldtypeClassName] = $inputfieldClassName;
		}
		// track for later uninstall
		$this->fieldtypesAndInputfieldsToUninstall = $fieldtypesAndInputfieldsToUninstall;
	}

	/**
	 * Delete Fields.
	 *
	 * @param FieldsArray $fields
	 * @param bool $isRemoveFromProductFieldgroup
	 * @return mixed
	 */
	private function deleteFields(FieldsArray $fields, bool $isRemoveFromProductFieldgroup = false) {

		$productVariantFieldgroup = null;
		if (!empty($isRemoveFromProductFieldgroup)) {
			// get product and product-variants fieldgroups to potentially remove optional features' fields from
			// for instance, 'pwcommerce_categories' when we remove the feature 'product_categories'
			// or 'pwcommerce_downloads', or 'pwcommerce_product_attributes_options'
			$templates = $this->wire('templates');
			$productTemplate = $templates->get('pwcommerce-product');
			$productFieldgroup = $productTemplate->fieldgroup;
			// -------------
			$productVariantTemplate = $templates->get('pwcommerce-product-variant');
			if (!empty($productVariantTemplate)) {
				$productVariantFieldgroup = $productVariantTemplate->fieldgroup;
			}
		}
		// -------------
		foreach ($fields as $field) {
			if (!empty($isRemoveFromProductFieldgroup)) {
				// CHECK IF REMOVE FIELD FROM PRODUCT AND PRODUCT VARIANTS FIELDGROUPS
				// ++++++++++++++++++++++++
				if ($productFieldgroup->hasField($field)) {
					// TODO - CONFIRM USAGE IN CASE MANY PAGES INVOLVED
					// remove field from product fieldgroup
					$productFieldgroup->remove($field);
					$productFieldgroup->save();
				}
				// ------------
				if (!empty($productVariantFieldgroup) && $productVariantFieldgroup->hasField($field)) {
					// TODO - CONFIRM USAGE IN CASE MANY PAGES INVOLVED
					// remove field from product-variant fieldgroup
					$productVariantFieldgroup->remove($field);
					$productVariantFieldgroup->save();
				}
			}
			// ---------------
			// DELETE THE FIELD
			$this->wire('fields')->delete($field);
		}
	}

	/**
	 * Uninstall pwcommerce fieldtype and inputfield modules during complete removal action.
	 *
	 * @return mixed
	 */
	private function completeRemovalOfPWCommerceUninstallFields() {
		// loop through all PWCommerce Fields and remove only Custom PWCommerce Fieldtypes and their Inputfields
		foreach ($this->fieldtypesAndInputfieldsToUninstall as $fieldtypeClassName => $inputfieldClassName) {
			// uninstall the Inputield first then the fieldtype
			if ($inputfieldClassName !== 'InputfieldText') {
				// some Fields do not have inputfields; we skip those here
				$this->wire('modules')->uninstall($inputfieldClassName);
			}
			// -------
			$this->wire('modules')->uninstall($fieldtypeClassName);
		}
	}

	// TODO: DELETE IF NOT IN USE
	/**
	 * Filter P W Commerce Fieldtypes.
	 *
	 * @param mixed $type
	 * @return mixed
	 */
	private function filterPWCommerceFieldtypes($type) {
		return strpos($type, 'PWCommerce') !== false;
	}

	/**
	 * Complete Removal Of P W Commerce Drop Custom Tables.
	 *
	 * @return mixed
	 */
	private function completeRemovalOfPWCommerceDropCustomTables() {
		$pwcommerceCustomTableNames = $this->getNamesOfPWCommerceCustomTables();
		// TODO CONFIRM WORKS!
		// also add download codes table
		$pwcommerceCustomTableNames = array_merge($pwcommerceCustomTableNames, $this->getNamesOfPWCommerceSpecialCustomTables());
		foreach ($pwcommerceCustomTableNames as $tableName) {
			// just in case, first check if the table exists before trying to drop it
			if (!empty($this->pwcommerce->isExistPWCommerceCustomTable($tableName))) {
				// IF CUSTOM TABLE EXISTS, DROP IT
				$this->dropTable($tableName);
			}
		}
	}

	/**
	 * Get Names Of P W Commerce Custom Tables.
	 *
	 * @return mixed
	 */
	private function getNamesOfPWCommerceCustomTables() {
		return [
			PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE_NAME,
			PwCommerce::PWCOMMERCE_ORDER_CART_TABLE_NAME
		];
	}

	/**
	 * Get Names Of P W Commerce Special Custom Tables.
	 *
	 * @return mixed
	 */
	private function getNamesOfPWCommerceSpecialCustomTables() {
		return [
			PwCommerce::PWCOMMERCE_DOWNLOAD_CODES_TABLE_NAME,
		];
	}

	/**
	 * Get S Q L For Order Status Table.
	 *
	 * @return mixed
	 */
	private function getSQLForOrderStatusTable() {
		$table = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE_NAME;
		// TODO: REFACTOR TO READ FROM FILE?
		$sql = "
		CREATE TABLE `{$table}` (
			`status_code` smallint(5) unsigned NOT NULL DEFAULT 0,
			`name` varchar(255) CHARACTER SET ascii NOT NULL,
			`description` text CHARACTER SET ascii NOT NULL,
			PRIMARY KEY (`status_code`),
			UNIQUE KEY `name` (`name`),
			FULLTEXT KEY `description` (`description`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

		INSERT INTO `{$table}` (`status_code`, `name`, `description`) VALUES
		(1000,	'Draft Order',	'Manual draft order.'),
		(1001,	'Abandoned',	'Customer did not complete checkout.'),
		(1002,	'Pending',	'Payment received (paid) and stock has been reduced; order is awaiting fulfilment.'),
		(1003,	'Manual verification required',	'Order on hold while some aspect, such as tax-exempt documentation, is manually confirmed. Orders with this status must be updated manually. Capturing funds or other order actions will not automatically update the status of an order marked Manual Verification Required.'),
		(1004,	'Declined',	'Seller has marked the order as declined.'),
		(1005,	'Disputed',	'Customer has initiated a dispute resolution process for the transaction that paid for the order or the seller has marked the order as a fraudulent order.'),
		(2000,	'Cancelled',	'Seller or customer has cancelled an order, due to a stock inconsistency or other reasons. Stock levels will automatically update depending on your Inventory Settings. Canceling an order will not refund the order.'),
		(2999,	'Completed',	'Order has been shipped/picked up, and receipt is confirmed; client has paid for their digital product, and their file(s) are available for download.'),
		(3000,	'Awaiting payment',	'Customer has completed the checkout process, but payment has yet to be confirmed. Authorise-only transactions that are not yet captured have this status.'),
		(3001,	'Authentication required',	'Awaiting action by the customer to authenticate the transaction and/or complete SCA requirements.'),
		(3002,	'Failed',	'Payment failed or was declined (unpaid) or requires authentication (SCA).'),
		(3003,	'Authorised',	'Depending on your checkout settings, payments are either captured manually or automatically. If your store is set up for manual capture, then new credit card payments have a status of Authorised.'),
		(3004,	'Overdue',	'An order has not yet been paid by the due date set in the payment terms.'),
		(3005,	'Unpaid',	'Payment has not yet been captured.'),
		(3999,	'Partially paid',	'A credit card payment has been captured, or a payment using an offline or custom payment method has been marked as received but the capture is less than the full amount of the order.'),
		(4000,	'Paid',	'A credit card payment has been captured, or a payment using an offline or custom payment method has been marked as received.'),
		(4998,	'Partially refunded',	'Seller has partially refunded the order.'),
		(4999,	'Refunded',	'Seller has used the Refund action to refund the whole order.'),
		(5000,	'Void fulfilment',	'Customer did not complete checkout. Fulfilment is void.'),
		(5001,	'Awaiting fulfilment',	'Customer has completed the checkout process and payment has been confirmed.'),
		(5002,	'On hold',	'Awaiting payment - stock is reduced, but you need to confirm payment.'),
		(5003,	'Scheduled',	'Order is marked as scheduled for future fulfilment. For instance, for a pre-paid subscription order.'),
		(5004,	'Awaiting shipment',	'Order has been pulled and packaged and is awaiting collection from a shipping provider.'),
		(5005,	'Shipment delayed',	'Shipment of the order has been delayed.'),
		(6000,	'Partially shipped',	'Part of the order has been shipped, but receipt has not been confirmed.'),
		(6001,	'Shipped',	'Order has been shipped, but receipt has not been confirmed.'),
		(6002,	'Awaiting pickup',	'Order has been packaged and is awaiting customer pickup from a seller-specified location.'),
		(6003,	'Partially fulfilled',	'Part of the order has been shipped and receipt has been confirmed.'),
		(6004,	'Fulfilled',	'Order has been shipped and receipt has been confirmed.'),
		(6005,	'Shipment damaged',	'Customer reports shipment is damaged.'),
		(6006,	'Shipment lost - Customer',	'Customer claims shipment was never delivered.'),
		(6007,	'Shipment lost - Courier',	'Courier reports that shipment is lost.'),
		(6008,	'Delivery refused',	'Customer refused to accept the delivery.');
		";
		// --------
		return $sql;
	}

	/**
	 * Get S Q L For Order Cart Table.
	 *
	 * @return mixed
	 */
	private function getSQLForOrderCartTable() {
		$table = PwCommerce::PWCOMMERCE_ORDER_CART_TABLE_NAME;
		// TODO: REFACTOR TO READ FROM FILE?
		// TODO: variation ID is also a product id? keep both? maybe - meaning can have duplicates? or does it mean we use product_id for parent id if a variation? I don't think we need it! take it out; we'll check using template name!
		// TODO DELETE WHEN DONE!
		// $sql = "
		// CREATE TABLE `{$table}` (
		//     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		//     `session_id` varchar(255) NOT NULL,
		//     `user_id` int(10) unsigned DEFAULT NULL,
		//     `product_id` int(10) unsigned DEFAULT NULL,
		//     `variation_id` varchar(255) NOT NULL DEFAULT '0',
		//     `quantity` int(10) unsigned DEFAULT NULL,
		//     `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		//     PRIMARY KEY (`id`),
		//     KEY `sess_id` (`session_id`)
		//   ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		// ";
		$sql = "
				CREATE TABLE `{$table}` (
						`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						`session_id` varchar(255) NOT NULL,
						`user_id` int(10) unsigned DEFAULT NULL,
						`product_id` int(10) unsigned DEFAULT NULL,
						`quantity` int(10) unsigned DEFAULT NULL,
						`last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						PRIMARY KEY (`id`),
						KEY `sess_id` (`session_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				";
		// --------
		return $sql;
	}

	/**
	 * Get S Q L For Download Codes Table.
	 *
	 * @return mixed
	 */
	private function getSQLForDownloadCodesTable() {
		$table = PwCommerce::PWCOMMERCE_DOWNLOAD_CODES_TABLE_NAME;
		// TODO: REFACTOR TO READ FROM FILE?
		// TODO: variation ID is also a product id? keep both? maybe - meaning can have duplicates? or does it mean we use product_id for parent id if a variation? I don't think we need it! take it out; we'll check using template name!
		// TODO DELETE WHEN DONE!
		// $sql = "
		// CREATE TABLE `{$table}` (
		//     `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		//     `session_id` varchar(255) NOT NULL,
		//     `user_id` int(10) unsigned DEFAULT NULL,
		//     `product_id` int(10) unsigned DEFAULT NULL,
		//     `variation_id` varchar(255) NOT NULL DEFAULT '0',
		//     `quantity` int(10) unsigned DEFAULT NULL,
		//     `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		//     PRIMARY KEY (`id`),
		//     KEY `sess_id` (`session_id`)
		//   ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		// ";
		$sql = "
				CREATE TABLE `{$table}` (
						`code` varchar(255) NOT NULL,
						`download_id` int(10) unsigned NOT NULL,
						`downloads` int(10) unsigned NOT NULL default 0,
						`maximum_downloads` int(10) unsigned NULL,
						`download_expiry` datetime NULL,
						`order_id` int(10) unsigned NULL,
						`last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						PRIMARY KEY (`code`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				";
		// --------
		return $sql;
	}

	/**
	 * Complete Removal Of P W Commerce Delete Roles.
	 *
	 * @return mixed
	 */
	private function completeRemovalOfPWCommerceDeleteRoles() {
		$optionalRoles = $this->getPWCommerceOptionalRoles();
		$roles = $this->wire('roles');
		foreach ($optionalRoles as $roleName) {
			$roleDelete = $roles->get($roleName);
			if ($roleDelete instanceof NullPage) {
				continue;
			}
			$role = $roles->delete($roleDelete);
		}
	}

	/**
	 * Invalidate P W Commerce Find Anything Cache.
	 *
	 * @return mixed
	 */
	private function invalidatePWCommerceFindAnythingCache() {
		$this->wire('cache')->delete(PwCommerce::FIND_ANYTHING_TEMPLATES_CACHE_NAME);
	}

	/**
	 * Get P W Commerce Installed Optional Features.
	 *
	 * @return mixed
	 */
	private function getPWCommerceInstalledOptionalFeatures() {
		$configs = $this->pwcommerce->getPWCommerceModuleConfigs($this->configModuleName);
	}

	// ~~~~~~~~~~~~~~

	/**
	 * Is Site Multilingual.
	 *
	 * @return bool
	 */
	private function isSiteMultilingual() {
		return !empty($this->wire('languages'));
	}
}