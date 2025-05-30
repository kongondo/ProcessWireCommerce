<?php

namespace ProcessWire;

/**
 * PWCommerce: Import.
 *
 * Import class for PWCommerce to import and create various pages. These include products, categories, tags, brands, types, attributes, attribute-options, variants, dimensions, properties
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceImport for PWCommerce
 * Copyright (C) 2022 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceImport extends WireData
{





	private $importItems;
	private $importType;
	private $importOptions = [];
	private $importErrors;
	// -------
	private $attributesAndAttributeOptionsIDsLookup = [];
	private $createdVariantsCount = 0;
	// ---------
	private $isMultilingual;

	public function __construct($importOptions = null) {
		parent::__construct();
		if (is_array($importOptions)) {
			$this->importOptions = $importOptions;
		}

	}

	/**
	 * Check and import given items into shop.
	 *
	 * @param array $items Array of items to import.
	 * @param string $importType The type of import.
	 * @return PWCommerceImport::runImport.
	 */
	public function import(array $items, string $importType) {
		/*
									NOTES:
									- not all shop page types are supported!
									*/

		// -------------
		# error: no items
		if (empty($items)) {
			throw new WireException("Import items not specified!");
		}
		# error: no type
		if (empty($importType)) {
			throw new WireException("Import type not specified!");
		}

		# error: unknown type
		if (!in_array($importType, array_keys($this->getAllTemplatesForImports()))) {
			throw new WireException("Unknown import type!");
		}

		// ====================
		// GOOD TO GO

		// SET IMPORT TYPE AND ITEMS TO CLASS PROPERTIES
		$this->importItems = $items;
		$this->importType = $importType;

		// SET MULTILINGUAL STATUS
		$this->setMultilingualStatus();

		// RUN IMPORT
		return $this->runImport();
	}

	/**
	 * Sets class property to true if shop is multilingual site, else false.
	 *
	 * @return void
	 */
	private function setMultiLingualStatus() {
		$this->isMultilingual = !empty($this->wire('languages'));
	}

	private function setSpecificItemParent($parentTitleOrID) {
		$parent = null;
		$parentTemplateName = $this->getImportTypeParentTemplateName();

		if (!empty($parentTemplateName)) {
			// if parent template name found
			// check if parent title or ID is OK
			$selector = $this->buildSelectorFromPageIDOrTitle($parentTitleOrID);

			// ----------
			if (!empty($selector)) {
				// @note: we include unpublished parents!
				// "template={$parentTemplateName},{$selector},include=all"
				$selector .= ",template={$parentTemplateName},include=all";

				$parent = $this->wire('pages')->get($selector);
			}
		}
		// ---------
		return $parent;
	}

	/**
	 * Get the name of the template of a given import type.
	 *
	 * @return string $templateName Import type template name.
	 */
	private function getImportTypeTemplate() {
		$importType = $this->importType;
		$importTypeTemplateName = null;
		$allImportTemplates = $this->getAllTemplatesForImports();

		// --------
		if (!empty($allImportTemplates[$importType])) {
			$importTypeTemplateName = $allImportTemplates[$importType];
		}
		// -----
		return $importTypeTemplateName;
	}

	/**
	 * Get the parent page of a given import type.
	 *
	 * @param string $importType Import type whose parent page to fetch.
	 * @return Page $parent Import type parent page.
	 */
	private function getImportTypeParent() {
		$parentTemplateName = $this->getImportTypeParentTemplateName();

		// TODO - HERE NOTE THAT SOME TYPES RELY ON THEIR PARENTS, E.G. 'attribute_options' and 'variants'
		$importTypeParent = null;
		if (!empty($parentTemplateName)) {
			$importTypeParent = $this->pwcommerce->get("template={$parentTemplateName}");
		}
		// -----

		return $importTypeParent;
	}

	/**
	 * Get the parent template name for a given import type.
	 *
	 * @return mixed $parentTemplate String if template name found else null.
	 */
	private function getImportTypeParentTemplateName() {
		$importType = $this->importType;
		$allImportParentTemplates = $this->getAllParentTemplatesForImports();

		$parentTemplate = null;
		// --------
		if (!empty($allImportParentTemplates[$importType])) {
			$parentTemplate = $allImportParentTemplates[$importType];
		}

		// ----------
		return $parentTemplate;
	}

	/**
	 * Get the names of templates for all importable types.
	 *
	 * @return array Array with names of  templates for all importable types.
	 */
	private function getAllTemplatesForImports() {
		return
			[
				'attributes' => "pwcommerce-attribute",
				'attribute_options' => "pwcommerce-attribute-option",
				// @note: special: editing via parent
				'brands' => "pwcommerce-brand",
				// @note: has image field
				'categories' => "pwcommerce-category",
				// @note: has description field
				'dimensions' => "pwcommerce-dimension",
				'downloads' => "pwcommerce-download",
				// @note: has custom field + file field
				//    'legal_pages' => "pwcommerce-legal-page",
				//    'orders' => "pwcommerce-order",
				//    'order_line_items' => "pwcommerce-order-line-item", // @note: special: editing via parent
				'products' => "pwcommerce-product",
				// @note: has image, description, pages and description fields
				'variants' => "pwcommerce-product-variant",
				// @note: special: editing via parent
				'properties' => "pwcommerce-property",
				'tags' => "pwcommerce-tag",
				'types' => "pwcommerce-type",
			];
	}

	/**
	 * Get the names of parent templates for all importable types.
	 *
	 * @return array Array with names of parent templates for all importable types.
	 */
	private function getAllParentTemplatesForImports() {
		return
			[
				'attributes' => "pwcommerce-attributes",
				'attribute_options' => "pwcommerce-attribute",
				// @note: special: editing via parent
				'brands' => "pwcommerce-brands",
				'categories' => "pwcommerce-categories",
				'dimensions' => "pwcommerce-dimensions",
				'downloads' => "pwcommerce-downloads",
				//    'legal_pages' => "pwcommerce-legal-pages",
				//    'orders' => "pwcommerce-order",
				//    'order_line_items' => "pwcommerce-order-line-item", // @note: special: editing via parent
				'products' => "pwcommerce-products",
				'variants' => "pwcommerce-product",
				// @note: special: editing via parent
				'properties' => "pwcommerce-properties",
				'tags' => "pwcommerce-tags",
				'types' => "pwcommerce-types",
			];
	}

	/**
	 * Get properties of pwcommerce fields for importable types.
	 *
	 * @return array Array with names of field properties.
	 */
	private function getAllImportPWCommerceFieldsProperties() {
		// TODO COULD USE SETARRAY? NAAH, NEED TO SANITIZE AS BELOW!
		return [
			// 'pwcommerce_description' => null,
			'pwcommerce_download_settings' => [
				'maximumDownloads',
				// int
				'timeToDownload' // text
			],
			'pwcommerce_product_properties' => [
				'value',
				// text
				'propertyID',
				// int
				'dimensionID',
				// int
			],
			'pwcommerce_product_settings' => [
				'shippingType',
				// limited text: 'physical' | 'physical_no_shipping' | 'digital' | 'service'
				'taxable',
				// limited int: 0,1
				'trackInventory',
				// limited int: 0,1
				'useVariants',
				// limited int: 0,1
				'colour',
				// text
			],
			'pwcommerce_product_stock' => [
				'sku',
				// text
				'price',
				// float
				'comparePrice',
				// float
				'quantity',
				// int
				'allowBackorders',
				// limited int: 0,1
				'enabled',
				// limited int: 0,1
			],
		];
	}

	/**
	 * Get names of pwcommerce fields that are language fields if applicable.
	 *
	 * @return array Array with names of potential language fields.
	 */
	private function getPWCommerceLanguageFieldsNames() {
		return [
			'pwcommerce_description'
		];
	}

	/**
	 * Get names of pwcommerce image and file fields.
	 *
	 * @return array Array with names of image and file fields.
	 */
	private function getPWCommerceImageAndFileFieldsNames() {
		return
			[
				'pwcommerce_images',
				'pwcommerce_file',
			];
	}

	/**
	 * Get names of pwcommerce single page  fields.
	 *
	 * @return array Array with names of single page fields.
	 */
	private function getPWCommerceSinglePageFieldsNames() {
		return [
			'pwcommerce_brand',
			'pwcommerce_type'
		];
	}

	/**
	 * Get names of pwcommerce multi page  fields.
	 *
	 * @return array Array with names of multi page fields.
	 */
	private function getPWCommerceMultiPageFieldsNames() {
		return
			[
				'pwcommerce_categories',
				'pwcommerce_downloads',
				'pwcommerce_product_attributes',
				'pwcommerce_product_attributes_options',
				'pwcommerce_tags',
			];
	}

	/**
	 * Get names of pwcommerce custom fields.
	 *
	 * @return array Array with names of custom fields.
	 */
	private function getPWCommerceCustomFieldsNames() {
		return
			[
				'pwcommerce_download_settings',
				'pwcommerce_product_properties',
				'pwcommerce_product_settings',
				'pwcommerce_product_stock',
			];
	}

	/**
	 * Get names of pwcommerce custom fields that can take multiple records.
	 *
	 * These extend FieldtypeMulti.
	 * There blank item is a WireArray.
	 *
	 * @return array Array with names of custom fields that can have multiple records.
	 */
	private function getPWCommerceCustomFieldsNamesForMultipleRecords() {
		return
			[
				'pwcommerce_product_properties',
			];
	}
	// ~~~~~~~~~~

	/**
	 * Get names of pwcommerce single page  fields allowed templates.
	 *
	 * @return array Array with names of single page fields allowed templates.
	 */
	private function getPWCommerceSinglePageFieldsAllowedTemplatesNames() {
		return [
			'pwcommerce_brand' => 'pwcommerce-brand',
			'pwcommerce_type' => 'pwcommerce-type'
		];
	}

	/**
	 * Get names of pwcommerce multi page  fields allowed templates.
	 *
	 * @return array Array with names of multi page fields  allowed templates.
	 */
	private function getPWCommerceMultiPageFieldsAllowedTemplatesNames() {
		return
			[
				'pwcommerce_categories' => 'pwcommerce-category',
				'pwcommerce_downloads' => 'pwcommerce-download',
				'pwcommerce_product_attributes' => 'pwcommerce-attribute',
				'pwcommerce_product_attributes_options' => 'pwcommerce-attribute-option',
				'pwcommerce_tags' => 'pwcommerce-tag',
			];
	}

	/**
	 * Get the allowed template name for a given PWCommerce single page field.
	 *
	 * @param string $fieldName Name of the single page field whose allowed template to get.
	 * @return mixed $fieldNameAllowedTemplateName Name of allowed template if found, else null.
	 */
	private function getSpecificPWCommerceSinglePageFieldAllowedTemplateName($fieldName) {
		$fieldNameAllowedTemplateName = null;
		$allSinglePageFieldsAllowedTemplateNames = $this->getPWCommerceSinglePageFieldsAllowedTemplatesNames();

		if (!empty($allSinglePageFieldsAllowedTemplateNames[$fieldName])) {
			$fieldNameAllowedTemplateName = $allSinglePageFieldsAllowedTemplateNames[$fieldName];
		}
		// -----
		return $fieldNameAllowedTemplateName;
	}

	/**
	 * Get the allowed template name for a given PWCommerce single page field.
	 *
	 * @param string $fieldName Name of the single page field whose allowed template to get.
	 * @return mixed $fieldNameAllowedTemplateName Name of allowed template if found, else null.
	 */
	private function getSpecificPWCommerceMultiPageFieldAllowedTemplateName($fieldName) {
		$fieldNameAllowedTemplateName = null;
		$allMultiPageFieldsAllowedTemplateNames = $this->getPWCommerceMultiPageFieldsAllowedTemplatesNames();

		if (!empty($allMultiPageFieldsAllowedTemplateNames[$fieldName])) {
			$fieldNameAllowedTemplateName = $allMultiPageFieldsAllowedTemplateNames[$fieldName];
		}
		// -----
		return $fieldNameAllowedTemplateName;
	}

	// ~~~~~~~~~~

	/**
	 * Get names of custom fields properties that require a page ID as a value.
	 *
	 * Used to check if valid page and template.
	 *
	 * @return array Array with property => template name pairs.
	 */
	private function getPWCommerceCustomFieldsPropertyIsPage() {
		return [
			'propertyID' => 'pwcommerce-property',
			'dimensionID' => 'pwcommerce-dimension'
		];
	}

	// ~~~~~~~~~~

	/**
	 * Get names of import contexts whereby each import item will need a specified parent.
	 *
	 * @return array Array with names of import contexts whose items needs a specified parent.
	 */
	private function getEachImportItemNeedsSpecificParent() {
		return
			[
				'attribute_options',
				'variants',
			];
	}

	// ~~~~~~~~~~

	private function setMultilingualFieldValues($importItem, Page $newPage, $fieldName) {

		foreach ($this->wire('languages') as $language) {
			// skip default language as already set IN runImport()
			if ($language->name == 'default') {
				continue;
			}
			// ===================
			# VALIDATION: LANGUAGE VALUE FOR THIS LANGUAGE NOT SET; skip
			if (empty($importItem["{$fieldName}"]["{$language->name}"])) {
				// no value in this language: skip

				continue;
			}

			# VALIDATION: LANGUAGE FIELD PROPERTY EMPTY AFTER SANITIZE; SKIP
			$languageFieldValueRaw = $importItem["{$fieldName}"]["{$language->name}"];
			$languageFieldValue = $this->sanitizeImportFieldValue("{$fieldName}", $languageFieldValueRaw);

			if (empty($languageFieldValue)) {

				$this->importErrors["empty_{$fieldName}_after_sanitizer"][] = $languageFieldValueRaw;
				continue;
			}
			// ---

			// set the title language title
			$newPage->$fieldName->setLanguageValue($language, $languageFieldValue);

			// TODO: set name too?
			// set page as active in this language
			$newPage->set("status$language", 1);
		}
		// -------
		return $newPage;
	}

	// ~~~~~~~~~~~~~~

	private function sanitizeImportFieldValue($property, $value) {

		$sanitizer = $this->wire('sanitizer');
		// ----
		$ints = ['maximumDownloads', 'propertyID', 'dimensionID', 'quantity', ''];
		$floats = ['price', 'comparePrice'];
		$limitedValues = [
			'shippingType' => ['physical', 'physical_no_shipping', 'digital', 'service'],
			'taxable' => [0, 1],
			'trackInventory' => [0, 1],
			'useVariants' => [0, 1],
			'allowBackorders' => [0, 1],
			'enabled' => [0, 1],
		];
		$purify = ['pwcommerce_description'];
		$texts = ['timeToDownload', 'value', 'colour', 'sku'];

		// -------
		// SANITIZE VALUE PER TYPE
		if (in_array($property, $ints)) {
			// ints
			$value = (int) $value;

		} else if (in_array($property, $floats)) {
			// floats
			$value = (float) $value;

		} else if (in_array($property, array_keys($limitedValues))) {
			// limitedValues
			$value = $sanitizer->option($value, $limitedValues[$property]);

		} else if (in_array($property, $purify)) {
			// purify
			$value = $sanitizer->purify($value);

		} else {
			// texts
			$value = $sanitizer->text($value);

		}

		// --------

		return $value;
	}

	// ~~~~~~~~~~~~~~

	private function processImportFileOrImageField($importItem, Page $newPage, $fieldName) {

		// -----------
		// loop through and add file/images
		// TODO - check path? what if url? try - catch?
		$filesOrImagesPaths = $importItem["{$fieldName}"];
		foreach ($filesOrImagesPaths as $fileOrImagePath) {

			try {
				$newPage->$fieldName->add($fileOrImagePath);

			} catch (\Throwable $e) {
				//throw $th;

				$this->importErrors["file_error_for_field_{$fieldName}"][] = $e->getMessage();
			}
		}

		// ---------
		return $newPage;
	}

	private function processImportSinglePageField($importItem, Page $newPage, $fieldName) {

		// ---------
		// get the allowed template
		$singlePageFieldAllowedTemplateName = $this->getSpecificPWCommerceSinglePageFieldAllowedTemplateName($fieldName);
		if (!empty($singlePageFieldAllowedTemplateName)) {
			// allowed template name for this single page field found
			// now validate that the given single page field page ID or title exists
			$pageTitleOrID = $importItem["{$fieldName}"];
			$selector = $this->buildSelectorFromPageIDOrTitle($pageTitleOrID);

			// ----------
			if (!empty($selector)) {
				$selector .= ",template={$singlePageFieldAllowedTemplateName}";

				$pageID = (int) $this->wire('pages')->getRaw($selector, 'id');

				if (!empty($pageID)) {
					$newPage->set($fieldName, $pageID);
				}
			}
		}
		// ---------
		return $newPage;
	}

	private function processImportMultiPageField($importItem, Page $newPage, $fieldName) {

		// ---------
		// get the allowed template
		$multiPageFieldAllowedTemplateName = $this->getSpecificPWCommerceMultiPageFieldAllowedTemplateName($fieldName);
		if (!empty($multiPageFieldAllowedTemplateName)) {
			// allowed template name for this multi page field found
			// now validate that the given multi page field page ID or title exists
			$pageTitleOrIDs = $importItem["{$fieldName}"];

			if (!empty($pageTitleOrIDs)) {
				foreach ($pageTitleOrIDs as $pageTitleOrID) {
					// ------this is an array; we loop through it
					$selector = $this->buildSelectorFromPageIDOrTitle($pageTitleOrID);

					// ----------
					if (!empty($selector)) {
						$selector .= ",template={$multiPageFieldAllowedTemplateName}";

						$pageID = (int) $this->wire('pages')->getRaw($selector, 'id');

						if (!empty($pageID)) {
							$newPage->$fieldName->add($pageID);
						}
					}
				}
			}
		}
		// ---------
		return $newPage;
	}

	private function processImportCustomFields($importItem, Page $newPage, $fieldName) {

		// ---------
		// get expected subfields for this field
		$allCustomFieldsSubfields = $this->getAllImportPWCommerceFieldsProperties();

		if (isset($allCustomFieldsSubfields["{$fieldName}"])) {
			$customFieldPropertyIsPagePropertiesAndTemplates = $this->getPWCommerceCustomFieldsPropertyIsPage();
			$customFieldPropertyIsPageProperties = array_keys($customFieldPropertyIsPagePropertiesAndTemplates);
			$subfields = $allCustomFieldsSubfields["{$fieldName}"];

			$subfieldsValues = $importItem["{$fieldName}"];

			// check if $fieldName is of WireArray type
			// i.e., can contain collection/multiple records (FieldtypeMulti)
			$isFieldForMultipleRecords = in_array($fieldName, $this->getPWCommerceCustomFieldsNamesForMultipleRecords());
			// ------
			if (!empty($isFieldForMultipleRecords)) {
				// process fields that can contain multiple records (WireArray)

				foreach ($subfieldsValues as $subfieldsValue) {

					$newSubfieldValue = new WireData();
					foreach ($subfieldsValue as $property => $rawValue) {

						// check if $rawValue can be a page ID or Title
						if (in_array($property, $customFieldPropertyIsPageProperties)) {

							$value = null;
							$selector = $this->buildSelectorFromPageIDOrTitle($rawValue);

							// ----------
							if (!empty($selector)) {
								$pageTemplateName = $customFieldPropertyIsPagePropertiesAndTemplates[$property];
								// @note: we don't include unpublished pages!
								$selector .= ",template={$pageTemplateName}";

								$pageID = (int) $this->wire('pages')->getRaw($selector, 'id');

								if (!empty($pageID)) {
									$value = $pageID;
								}
							}
						} else {
							# sanitize as usual
							// sanitize and set value of subfield for this field

							$value = $this->sanitizeImportFieldValue($property, $rawValue);
						}
						// ------
						// CHECKED OR SANITIZED VALUE

						// SET TO WIRE ARRAY
						// TODO ok?
						if (!is_null($value)) {
							$newSubfieldValue->set($property, $value);
						}
					}

					// SET NEW SUBFIELD VALUE (WireData) to page
					$newPage->$fieldName->add($newSubfieldValue);
				}
			} else {
				// process custom fields that contain single records (WireData)
				foreach ($subfields as $subfield) {
					if (isset($subfieldsValues["{$subfield}"])) {
						$rawValue = $subfieldsValues["{$subfield}"];

						// TODO: keep an eye on this in future if single custom fields can have page as a value!
						// sanitize and set value of subfield for this field
						$value = $this->sanitizeImportFieldValue($subfield, $rawValue);

						$newPage->$fieldName->set($subfield, $value);
					} else {

					}
				}
			}
		}

		// ----------
		return $newPage;
	}

	// ~~~~~~~~~~~

	/**
	 * Build selector part for getting page using its ID or title.
	 *
	 * @param mixed String or Integer for selecting a desired page.
	 * @return string $selector Built selector or empty string if cannot build.
	 */
	private function buildSelectorFromPageIDOrTitle($pageTitleOrID) {
		$selector = "";
		if (!empty((int) $pageTitleOrID)) {
			// can get page using ID
			$id = (int) $pageTitleOrID;

			$selector .= "id={$id}";
		} else if (!ctype_digit("$pageTitleOrID") && strlen($pageTitleOrID)) {
			// get page using its name
			$name = $this->wire('sanitizer')->pageName($pageTitleOrID, true);

			$selector .= "name={$name}";
		}

		// --------
		return $selector;
	}

	// ~~~~~~~~~~~~~~

	/**
	 * Run an import for a given importable type and given import items.
	 *
	 * @return array $result Array with the result of the import.
	 */
	private function runImport() {

		if ($this->importType === 'variants') {
			// GENERATING VARIANTS - DIVERT TO THAT
			return $this->generateVariants();
		}

		#####################
		$isImportNeedSpecificParent = in_array($this->importType, $this->getEachImportItemNeedsSpecificParent());
		/** @var string $templateName */
		$templateName = $this->getImportTypeTemplate();
		$parent = null;
		if (empty($isImportNeedSpecificParent)) {
			# GET KNOWN PARENT based on import type
			/** @var Page $parent */
			$parent = $this->getImportTypeParent();
		}
		// -----
		$multilingualFields = $this->getPWCommerceLanguageFieldsNames();
		$imageAndFileFields = $this->getPWCommerceImageAndFileFieldsNames();
		$singlePageFields = $this->getPWCommerceSinglePageFieldsNames();
		$multiPageFields = $this->getPWCommerceMultiPageFieldsNames();
		$customFields = $this->getPWCommerceCustomFieldsNames();

		// ----------
		$emptyTitlesCount = 0;
		$createdCount = 0;
		// ++++++++++++++++

		# PROCESS IMPORT

		foreach ($this->importItems as $item) {

			// -----------
			# VALIDATION: IMPORT TYPE REQUIRES EACH ITEM TO HAVE NAMED PARENT; NO PARENT FIELD SPECIFIED; SKIP
			if ($isImportNeedSpecificParent) {
				if (empty($item['parent'])) {

					$this->importErrors['item_parent_required_but_not_specified'][] = $item;
					continue;
				} else {
					// set the required parent
					$parent = $this->setSpecificItemParent($item['parent']);
				}
			}

			# VALIDATION:  PARENT NOT FOUND; SKIP
			if (empty($parent) || empty($parent->id)) {

				$this->importErrors['item_parent_not_found'][] = $item;
				continue;
			}

			# VALIDATION: NO PAGE TITLE FIELD; SKIP
			if (empty($item['title'])) {

				// @note: since empty, can only add this as count
				$this->importErrors['empty_titles_count'] = ++$emptyTitlesCount;
				continue;
			}

			# VALIDATION: FOR MULTILINGUAL TITLE; NO DEFAULT TITLE; SKIP
			if ($this->isMultilingual && empty($item['title']['default'])) {

				// @note: since empty, can only add this as count
				$this->importErrors['empty_titles_count'] = ++$emptyTitlesCount;
				continue;
			}

			# VALIDATION: PAGE TITLE EMPTY AFTER SANITIZE; SKIP
			$titleRaw = $this->isMultilingual ? $item['title']['default'] : $item['title'];
			$title = $this->sanitizeImportFieldValue('title', $titleRaw);

			if (empty($title)) {

				$this->importErrors['empty_title_after_sanitizer'][] = $titleRaw;
				continue;
			}

			# VALIDATION: PAGE ALREADY EXISTS AS CHILD OF PARENT PAGE; SKIP
			$isItemExists = $this->isPageAlreadyExists($parent, $title);

			if (!empty($isItemExists)) {
				// item already exists

				$this->importErrors['item_with_identical_title_exists'][] = $title;
				continue;
			}
			// +++++++++++++++

			// GOOD TO GO

			# PREPARE NEW PAGE
			$newPage = new Page();
			$newPage->template = $templateName;
			$newPage->parent = $parent;
			$newPage->title = $title;

			# SET MULTILINGUAL TITLES IF APPLICABLE
			if ($this->isMultilingual) {
				// add language titles
				$newPage = $this->setMultilingualFieldValues($item, $newPage, 'title');

			}
			// end: if multilingual titles
			// -------

			// ------------
			## PROCESS FIELDS OTHER THAN 'title' ##
			foreach ($item as $fieldName => $value) {
				// skip field 'title' as already set above and 'parent' as it is a special property
				if (in_array($fieldName, ['title', 'parent'])) {

					continue;
				}
				// --------------

				#################
				// ------------
				# 1. process multilingual fields (e.g.  'pwcommerce_description')
				if ($this->isMultilingual && in_array($fieldName, $multilingualFields)) {
					// set default value first if available
					if (!empty($value['default'])) {
						$newPage->set($fieldName, $value);
					}
					// add language values for field
					$newPage = $this->setMultilingualFieldValues($item, $newPage, $fieldName);

				}

				#2. process single (non-array) values (e.g. 'pwcommerce_description' in non-language sites)
				else if (!is_array($value) && !in_array($fieldName, $singlePageFields)) {
					$newPage->set($fieldName, $value);

				}

				# 3. process image and file fields (e.g. 'pwcommerce_file' and 'pwcommerce_images')
				else if (in_array($fieldName, $imageAndFileFields)) {
					// @note: since this is a new page, we will need to first save the page before we can add images/files to it!
					// -----------
					// save new page since processing images/files
					$newPage->save();
					// ------------
					// process images/files
					$newPage = $this->processImportFileOrImageField($item, $newPage, $fieldName);

				}

				# 4. process single page fields (e.g. 'pwcommerce_type' and 'pwcommerce_brand')
				else if (in_array($fieldName, $singlePageFields)) {
					$newPage = $this->processImportSinglePageField($item, $newPage, $fieldName);

				}

				# 5. process multi page fields (e.g. 'pwcommerce_categories' and 'pwcommerce_product_attributes')
				else if (in_array($fieldName, $multiPageFields)) {
					$newPage = $this->processImportMultiPageField($item, $newPage, $fieldName);

				}

				# 6. process custom fields (e.g. 'pwcommerce_categories' and 'pwcommerce_product_attributes')
				else if (in_array($fieldName, $customFields)) {
					$newPage = $this->processImportCustomFields($item, $newPage, $fieldName);

				}
				// -------
			}
			// end: loop of other fields (non-title)

			// ===============

			# SET PUBLISHED STATUS
			if (!empty($this->importOptions['is_unpublished'])) {
				$newPage->addStatus(Page::statusUnpublished);
			}

			// +++++++++++

			//  ready to save new item page

			# SAVE the new page
			$newPage->save();
			if (!empty($newPage->id)) {
				$createdCount++;
			}
		}
		// end foreach $this->importItems loop

		//  #######################

		// TODO RETURN ARRAY WITH NOTICE AND NOTICE TYPE SIMILAR TO PWCOMMERCEACTIONS, E.G. SUCCESS AND CREATED 10 BRANDS

		if (!empty($createdCount)) {
			$noticeType = 'sucess';
			// prepare messages
			$notice = sprintf(_n('Imported %1$d item of type %2$s.', 'Imported %1$d items of type %2$s.', $createdCount, $this->importType), $createdCount, $this->importType);
		} else {
			$notice = sprintf(__("Could not import any item of type %s!"), $this->importType);
			$noticeType = 'error';
		}

		// -------
		$result = [
			'notice' => $notice,
			'notice_type' => $noticeType,
			'imported_count' => $createdCount,
		];

		// -------
		return $result;
	}

	/**
	 * Generate variants for a given product based on specified attributes and attribute options.
	 *
	 * @return void
	 */
	private function generateVariants() {

		$pages = $this->wire('pages');
		$createdCount = 0;
		// ++++++++++++++++
		// ------
		// LOOP THROUGH ITEMS, CREATING VARIANTS FROM EACH IF APPROPRIATE
		foreach ($this->importItems as $item) {
			# VALIDATION: PARENT PRODUCT NOT SET; SKIP
			if (empty($item['product'])) {

				$this->importErrors['variants_parent_product_required_but_not_specified'][] = $item;
				continue;
			}

			# VALIDATION: IN GENERATION MODE BUT NO ATRRIBUTES AND ATTRIBUTES OPTIONS SET; SKIP
			if (!empty($this->importOptions['is_auto_generate']) && empty($item['generate_variants'])) {

				$this->importErrors['variants_attributes_and_attribute_options_not_specified'][] = $item;
				continue;
			}

			// ------------
			// GET OR CREATE VARIANTS MAIN PRODUCT  PAGE
			$productTitleOrID = $item['product'];
			$productPage = $this->setVariantProductPage($productTitleOrID);

			# VALIDATION: FOR SOME REASON COULD NOT GET OR CREATE PRODUCT PAGE; SKIP
			if (empty($productPage->id)) {

				$this->importErrors['variants_parent_product_cannot_get_or_create'][] = $item;
				continue;
			}

			// TODO WHAT IF PRODUCT PAGE IS NEW BUT VARIANTS CREATION FAILED? E.G. NO ATTRIBUTES; DELETE?

			# CHECK, GET OR CREATE AND SET ATTRIBUTE & ATTRIBUTE OPTIONS IDs
			/*
													 NOTES
													 - We do this first so that we will have the IDs ready after generating cartesian products
													 - this means we won't have to look for or create attributes and options AFTER generating cartesian products
													 - we will only need a FLAT lookup array with IDs -> title matching pairs for the attributes and the attribute options
													 e.g.
													 [
													 'Pattern' => 1245,
													 'South America' => 3456,
													 etc
													 ]
													 - attribute IDs will be set to $productPage->pwcommerce_product_attributes
													 - attribute options IDs will be set to respective created $variantPage->pwcommerce_product_attributes_options
													 */
			$attributesAndAttributeOptions = $item['generate_variants'];
			$this->setAttributeAndAttributeOptionsIDs($attributesAndAttributeOptions);

			# GENERATE CARTESIAN PRODUCTS TO USE TO GENERATE VARIANTS
			$cartesianProduct = call_user_func_array(array($this, 'createCartesianProduct'), $attributesAndAttributeOptions);

			# SET PRODUCT TO USE VARIANTS
			$productSettings = $productPage->get(PwCommerce::PRODUCT_SETTINGS_FIELD_NAME);
			$productSettings->set('useVariants', 1);
			$productPage->set(PwCommerce::PRODUCT_SETTINGS_FIELD_NAME, $productSettings);

			# SET PRODUCT ATTRIBUTES
			// this is a mutli page field
			$attributesTitlesOrIDs = array_keys($attributesAndAttributeOptions);

			$productPage = $this->setAttributesToProductPage($productPage, $attributesTitlesOrIDs);

			# CREATE AND SAVE PRODUCT VARIANTS
			// this will also set their attributes options
			$this->createVariants($productPage, $cartesianProduct, $item);

			# SAVE PRODUCT
			$productPage->of(false);
			$productPage->save();
			$createdCount++;
			#

			// -------------
			// FREE UP SOME MEMORY
			$pages->uncacheAll(); // free some memory
		}

		// -------------

		if (!empty($this->createdVariantsCount)) {
			$noticeType = 'sucess';
			// prepare messages
			$notice = sprintf(_n('Imported %1$d item of type %2$s.', 'Imported %1$d items of type %2$s.', $createdCount, $this->importType), $this->createdVariantsCount, $this->importType);
			// ------
			// append product notice
			$notice .= " ";
			$notice .= sprintf(_n('Also created or updated %d product.', 'Also created or updated %d products.', $createdCount), $createdCount);
		} else {
			$notice = sprintf(__("Could not import any item of type %s!"), $this->importType);
			$noticeType = 'error';
		}

		// -------
		$result = [
			'notice' => $notice,
			'notice_type' => $noticeType,
			'imported_count' => $this->createdVariantsCount,
		];

		// -------
		return $result;
	}

	private function setVariantProductPage($productTitleOrID) {
		$selector = $this->buildSelectorFromPageIDOrTitle($productTitleOrID);

		// TODO ok memory-wise?
		$productPage = new NullPage();
		// ----------
		if (!empty($selector)) {
			$selector .= ",template=product";

			$productPage = $this->pwcommerce->get($selector);

		}

		// ---------
		// CREATE PAGE IF IT DOESN'T EXIST
		if (empty($productPage->id)) {
			$productPage = $this->createVariantProductPage($productTitleOrID);

		}
		// ------
		return $productPage;
	}

	private function createVariantProductPage($pageTitle) {
		// TODO ok memory-wise?
		$productPage = new NullPage();
		if (!ctype_digit("$pageTitle") && strlen($pageTitle)) {
			// create page using the given title
			$title = $this->wire('sanitizer')->text($pageTitle);

			// -----------
			if (!empty($title)) {
				$productPage = new Page();
				$productPage->template = PwCommerce::PRODUCT_TEMPLATE_NAME;
				$productPage->title = $title;
				# CREATE NEW PRODUCT PAGE
				$productPage->save();
			}
		}
		// ------
		return $productPage;
	}

	private function createVariants(Page $productPage, array $cartesianProducts, array $importItem) {
		$fieldNameAttributeOptions = PwCommerce::PRODUCT_ATTRIBUTES_OPTIONS_FIELD_NAME;
		$fieldNameProductStock = PwCommerce::PRODUCT_STOCK_FIELD_NAME;
		$templateName = PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME;
		// --------------

		// ============
		// PREPARE BULK STOCK VALUES FOR ALL VARIANTS FOR THIS PRODUCT
		// price - generic for all variants
		if (isset($importItem['all_variants_price'])) {
			$price = (float) $importItem['all_variants_price'];

		} else {
			// price - from product or default 0
			$price = !empty($importItem['is_use_product_price']) ? (float) $productPage->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME)->price : 0;

		}

		// ****
		// enabled (default 1/true)
		$enabled = isset($importItem['all_variants_enabled']) ? (int) $importItem['all_variants_enabled'] : 1;
		// ****
		// allow back orders (default 0/false)
		$allowBackorders = isset($importItem['all_variants_allow_back_orders']) ? (int) $importItem['all_variants_allow_back_orders'] : 0;
		// ++++++++++++++
		foreach ($cartesianProducts as $cartesianProduct) {

			// ------------
			$attributesOptionsIDs = [];
			$attributesOptionsTitles = [];
			foreach ($cartesianProduct as $rawAttributeOption) {
				// get the ID of the attribute options from the lookup and prepare it for later setting to the PageArray - multi page field
				$attributesOptionsValues = $this->attributesAndAttributeOptionsIDsLookup["{$rawAttributeOption}"];
				$attributesOptionsIDs[] = $attributesOptionsValues['id'];
				$attributesOptionsTitles[] = $attributesOptionsValues['title'];
			}

			// ---------
			// variant title
			$variantTitle = implode(" / ", $attributesOptionsTitles);
			$title = "{$productPage->title}: $variantTitle";
			$name = $this->wire('sanitizer')->pageName($title, true);

			# VALIDATION: PAGE ALREADY EXISTS AS CHILD OF PARENT PAGE; SKIP
			$isItemExists = $this->isPageAlreadyExists($productPage, $title);

			if (!empty($isItemExists)) {
				// item already exists

				$this->importErrors['variant_with_identical_title_exists'][] = $title;
				continue;
			}

			// --------
			// SETUP SKU VALUES for STOCK
			$stock = [
				'sku' => '',
				'price' => $price,
				'enabled' => $enabled,
				'allowBackorders' => $allowBackorders
			];

			# CREATE NEW VARIANT
			$page = new Page();
			$page->template = $templateName;
			$page->parent = $productPage;
			$page->title = $title;
			$page->name = $name;
			// ----------
			// add attributes options to multi page field
			$page->$fieldNameAttributeOptions->add($attributesOptionsIDs);
			// ------------
			// SET STOCK VALUES
			$page->$fieldNameProductStock->setArray($stock);
			// ---------
			# SAVE VARIANT PAGE!
			// TODO: UNCOMMENT WHEN READY
			$page->save();

			// ------------
			if (!empty($page->id)) {
				$this->createdVariantsCount++;
			}
		}
		// ----------

	}

	private function buildVariantTitle($productTitle, $attributeOptionsTitles) {
	}

	/**
	 * Set attribute and attribute options IDs to a lookup array for adding their IDs to product and variants page fields.
	 *
	 * These are for pwcommerce_product_attributes and pwcommerce_product_attrpwcommerce_product_attributes_options.
	 * This lookup will help after we generate cartersian products by not having to refetch or create the attributes and attribute options then.
	 *
	 * @param array $attributesAndAttributeOptions
	 * @return void
	 */
	private function setAttributeAndAttributeOptionsIDs(array $attributesAndAttributeOptions) {

		// $this->attributesAndAttributeOptionsIDsLookup;
		foreach ($attributesAndAttributeOptions as $attributeTitleOrID => $attributeOptions) {
			// TODO THROW ERROR IF $attributeOptions is not an array?
			// get attribute ID if page exists; else create new
			$attributeValues = $this->getOrSetAttributePage($attributeTitleOrID);

			// @note: here the key must match the variants definitions as sent in 'generate_variants'
			$this->attributesAndAttributeOptionsIDsLookup["{$attributeTitleOrID}"] = $attributeValues;
			// ---------
			#	process attribute options for this attribute
			/*
													 - we get each if it exists, else create a new one
													 - this attribute will be their parent
													 - we add their given titleOrID => set/found ID pairs to $this->attributesAndAttributeOptionsIDsLookup as above
													 */
			$this->processAttributeOptionsForCreatingVariants($attributeValues['id'], $attributeOptions);
		}
	}

	/**
	 * Process attribute options IDs for later adding to variants.
	 *
	 * Populate a lookup array with their IDs.
	 *
	 * @param int $parentAttributeID ID of the attribute whose options to set. Needed if creating new attribute option.
	 * @param array $attributeOptions Attribute options IDs or titles to process.
	 * @return void
	 */
	private function processAttributeOptionsForCreatingVariants($parentAttributeID, $attributeOptions) {

		foreach ($attributeOptions as $attributeOptionTitleOrID) {
			$attributeOptionValues = $this->getOrSetAttributePage($attributeOptionTitleOrID, $parentAttributeID);

			// @note: here the key must match the variants definitions as sent in 'generate_variants'
			$this->attributesAndAttributeOptionsIDsLookup["{$attributeOptionTitleOrID}"] = $attributeOptionValues;
		}
	}

	private function getOrSetAttributePage($attributeTitleOrID, $parentAttributeID = 0) {
		$selector = $this->buildSelectorFromPageIDOrTitle($attributeTitleOrID);

		// ------
		$pageValues = null;
		// set template for creating either attribute or attribute option
		// if we got a $parentAttributeID it means we are processing an attribute option; else an attribute
		if (!empty($parentAttributeID)) {
			$attributeOrAttributeOptionTemplateName = PwCommerce::ATTRIBUTE_OPTION_TEMPLATE_NAME;
			$parent = $parentAttributeID;
		} else {
			$attributeOrAttributeOptionTemplateName = PwCommerce::ATTRIBUTE_TEMPLATE_NAME;
			// TODO GET KNOWN PARENT OF ALL ATTRIBUTES!
			$parent = $this->pwcommerce->get("template=" . PwCommerce::ATTRIBUTES_TEMPLATE_NAME);
		}

		// set parent ID
		// ----------
		if (!empty($selector)) {
			$selector .= ",template={$attributeOrAttributeOptionTemplateName}";
			// ---------------
			// if searching for attribute option, we need to include parent ID in the selector!
			if (!empty($parentAttributeID)) {
				$selector .= ",parent_id={$parentAttributeID}";
			}
			// --------------

			$pageValues = $this->wire('pages')->getRaw($selector, ['id', 'title']);

		}
		if (empty($pageValues)) {

			// attribute or attribute option does not exist
			// create a new one
			$page = new Page();
			$page->template = $attributeOrAttributeOptionTemplateName;
			// TODO GET PROGRAMMATICALLY -> IF ATTRIBUTE, THEN USE USUAL; IF
			$page->parent = $parent;
			$page->title = $this->wire('sanitizer')->text($attributeTitleOrID);
			$page->save();
			$pageValues = ['id' => $page->id, 'title' => $page->title];
		}

		// -----
		return $pageValues;
	}

	/**
	 * Create cartesian product of array of arrays.
	 *
	 * Generates variants
	 * @credits: https://stackoverflow.com/questions/2516599/cartesian-product-of-n-arrays/4743758.
	 * @return array $cartesianProducts.
	 */
	private function createCartesianProduct() {
		$_ = func_get_args();

		if (count($_) == 0) {
			return array();
		}
		$a = array_shift($_);

		if (count($_) == 0) {
			$c = array(array());

		} else {
			// $c = call_user_func_array(__FUNCTION__, $_);
			$c = call_user_func_array(array($this, __FUNCTION__), $_);

		}
		$cartesianProducts = [];

		foreach ($a as $v) {

			foreach ($c as $p) {
				$cartesianProducts[] = array_merge(array($v), $p);
			}

		}

		return $cartesianProducts;
	}

	private function setAttributesToProductPage($productPage, $rawAttributes) {
		$fieldName = PwCommerce::PRODUCT_ATTRIBUTES_FIELD_NAME;

		foreach ($rawAttributes as $rawAttribute) {
			// get the ID of the attribute from the lookup and set it to the PageArray - mutli page field
			$attributeID = $this->attributesAndAttributeOptionsIDsLookup["{$rawAttribute}"];

			$productPage->$fieldName->add($attributeID);
		}
		// ----------
		return $productPage;
	}

	/**
	 * Check if a new item already exists as a child of given parent.
	 *
	 * @param Page $parentPage Parent page to check if already has child with given title.
	 * @param string $pageTitle The title to check if already exists for a child for the given parent page.
	 * @return boolean True if child page already exists, else false.
	 */
	private function isPageAlreadyExists(Page $parentPage, string $pageTitle) {
		$name = $this->wire('sanitizer')->pageName($pageTitle, true);
		$parentID = $parentPage->id;

		// ------
		$child = (int) $this->pwcommerce->getRaw("parent_id={$parentID},name={$name},include=all", 'id');

		return !empty($child);
	}
}