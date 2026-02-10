<?php

namespace ProcessWire;

// load InputfieldPWCommerceDiscountRenderOrderDiscount class if not yet loaded by $pwcommerce
$inputfieldPWCommerceDiscountRenderOrderDiscountClassPath = __DIR__ . "/InputfieldPWCommerceDiscountRenderOrderDiscount.php";
require_once $inputfieldPWCommerceDiscountRenderOrderDiscountClassPath;

/**
 * PWCommerce: InputfieldPWCommerceDiscountRenderProductsDiscount
 *
 * Inputfield for FieldtypePWCommerceDiscount, the field that stores and outputs values of a PWCommerce discount.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceDiscountRenderProductsDiscount for PWCommerce
 * Copyright (C) 2023 by Francis Otieno
 * MIT License
 *
 */

// class InputfieldPWCommerceDiscountRenderProductsDiscount extends WireData
class InputfieldPWCommerceDiscountRenderProductsDiscount extends InputfieldPWCommerceDiscountRenderOrderDiscount
{

	private $discountAppliesToType;


	/**
	 *   construct.
	 *
	 * @param Page $page
	 * @param mixed $field
	 * @return mixed
	 */
	public function __construct($page, $field) {
		parent::__construct($page, $field);
		// @NOTE: WE INHERIT BELOW PROPS FROM PARENT CLASS 'InputfieldPWCommerceDiscountRenderOrderDiscount'
		// --------
		// SET DISCOUNT APPLIES TO TYPE
		// one of 'products_fixed'| 'products_percentage' | 'categories_percentage' | 'categories_fixed'
		$this->setDiscountAppliesToItemsMode();
		// --------

	}

	/**
	 * Set Discount Applies To Items Mode.
	 *
	 * @return mixed
	 */
	private function setDiscountAppliesToItemsMode() {
		$firstItemDiscountAppliesTo = $this->discountAppliesTo->first();
		if (!empty($firstItemDiscountAppliesTo)) {
			$this->discountAppliesToType = $firstItemDiscountAppliesTo->itemType;
		}
	}

	/**
	 * Render the entire input area for product discount
	 *
	 * @return mixed
	 */
	public function ___render() {
		// @overrides parent::render
		$xinit = $this->getInitValuesForAlpineJS();
		$out =
			"<div id='pwcommerce_products_discount_wrapper' {$xinit}>" .
			// TODO ADD 2*COLUMN GRID HERE
			$this->buildForm() .
			"</div>";
		return $out;
	}

	/**
	 * Get Discounts Form Header.
	 *
	 * @return mixed
	 */
	protected function getDiscountsFormHeader() {
		// @overrides parent::getDiscountsFormHeader
		$labelCategories = $this->_('amount off categories');
		$labelProducts = $this->_('amount off products');
		$discountTypeParts = explode("_", (string) $this->discountType); // e.g. 'categories_fixed_per_item'
		$appliesToTypeBase = $discountTypeParts[0];
		if ($appliesToTypeBase === 'products') {
			$label = $labelProducts;
		} else {
			// default to categories, irrespective
			$label = $labelCategories;
		}
		$discountTypeHeader =
			// discount type header
			// "<h3>" . $this->_('Amount off products') . "</h3>";
			// "<h4>" . $this->_('Amount off products') . "</h4>";
			// "<h4>" . $this->_('Product Discount (amount off products)') . "</h4>";
			"<h4>" . sprintf(__("Product Discount (%s)"), $label) . "</h4>";
		// ------
		return $discountTypeHeader;
	}

	/**
	 * Render Discount Value.
	 *
	 * @param mixed $wrapper
	 * @return string|mixed
	 */
	protected function renderDiscountValue($wrapper) {
		// @overrides parent::renderDiscountValue
		// GET WRAPPER FOR DISCOUNT RENDER VALUE HERE
		$wrapper = parent::renderDiscountValue($wrapper);
		// APPEND DISCOUNT PRODUCTS 'DISCOUNTS APPLIES TO' WRAPPER
		$wrapper = $this->renderDiscountAppliesTo($wrapper);
		// -----
		return $wrapper;
	}

	/**
	 * Get Value For Discount Type.
	 *
	 * @return mixed
	 */
	protected function getValueForDiscountType() {
		// @overrides parent::getValueForDiscountType
		$discountType = $this->discount->discountType;
		$fixedDiscountTypes = [
			// PRODUCTS
			'products_fixed_per_order',
			'products_fixed_per_item',
			// CATEGORIES
			'categories_fixed_per_order',
			'categories_fixed_per_item',
		];
		// @NOTE: JUST FOR RADIO INPUTS CONVENIENCE
		// for save, we stick to our values above
		// @see parent::processInput
		if (in_array($discountType, $fixedDiscountTypes)) {
			$value = 'fixed';
		} else {
			$value = 'percentage';
		}
		//------
		return $value;
	}

	/**
	 * Get Radio Options For Discount Value Type.
	 *
	 * @return mixed
	 */
	protected function getRadioOptionsForDiscountValueType() {
		// @overrides parent::getRadioOptionsForDiscountValueType
		// TODO FOR SAVE, WE NEED TO PREFIX WITH '_products/_categories'
		$radioOptions = [
			'percentage' => __('Percentage'),
			'fixed' => __('Fixed'),
		];
		// ----
		return $radioOptions;
	}

	### APPLIES TO ###
	/**
	 * Render Discount Applies To.
	 *
	 * @param mixed $wrapper
	 * @return string|mixed
	 */
	private function renderDiscountAppliesTo($wrapper) {
		// radio to select applies to type
		$field = $this->getMarkupForDiscountAppliesToRadioField();
		$wrapper->add($field);
		// ----
		if (!empty($this->isProductCategoriesFeatureInstalled)) {
			// selectize text input for searching specific categories
			$field = $this->getMarkupForDiscountAppliesToSpecificCategoriesTextTagsField();
			$wrapper->add($field);
		}
		// ----
		// selectize text input for searching specific products and variants
		$field = $this->getMarkupForDiscountAppliesToSpecificProductsTextTagsField();
		$wrapper->add($field);
		// checkbox input to toggle show fixed discount apply per order OR per item
		$field = $this->getMarkupForDiscountFixedApplyOnceToggleCheckboxField();
		$wrapper->add($field);
		// divider markup for sections that need it
		$field = $this->getMarkupForDiscountSectionsDividerMarkupField("applies_to");
		$wrapper->add($field);
		// -----
		return $wrapper;
	}

	/**
	 * Get Markup For Discount Applies To Radio Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountAppliesToRadioField() {
		// TODO UNSET CATEGORIES IN CASE NOT IN USE; IN WHICH CASE, USE HIDDEN INPUT TO
		//------------------- pwcommerce_discount_applies_to (getInputfieldRadios)
		$label = $this->_('Applies To');
		// -----
		// CHECK IF SHOP HAS PRODUCT CATEGORIES FEATURE (installed)
		if (!empty($this->isProductCategoriesFeatureInstalled)) {
			// USE RADIO INPUTS
			$appliesToType = $this->discountAppliesToType;

			// @note: since this are saved as 'categories_percentage', 'categories_fixed', 'products_percentage' OR 'products_fixed', we need the first part to build the SUFFIX for the final selected options for the radio
			$appliesToTypeParts = explode("_", (string) $appliesToType); // e.g. 'categories_fixed'
			$appliesToTypeSuffix = $appliesToTypeParts[0];
			// append the as suffix, if not empty
			$specificAppliesToType = "";
			if (!empty($appliesToTypeSuffix)) {
				$specificAppliesToType = "specific_{$appliesToTypeSuffix}";
			}

			$radioOptions = [
				'specific_categories' => __('Specific categories'),
				'specific_products' => __('Specific products'),
			];
			// =======
			$value = !empty($specificAppliesToType) ? $specificAppliesToType : 'specific_categories';
			// --------
			$options = [
				'id' => "pwcommerce_discount_applies_to",
				'name' => 'pwcommerce_discount_applies_to',
				'label' => $label,
				'collapsed' => Inputfield::collapsedNever,
				// 'columnWidth' => 33,
				// 'required' => true,
				'wrapClass' => true,
				'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
				'radio_options' => $radioOptions,
				'value' => $value,
			];

			$field = $this->pwcommerce->getInputfieldRadios($options);
		} else {
			// USE HIDDEN INPUT INSTEAD
			$options = [
				'id' => "pwcommerce_discount_applies_to",
				'name' => 'pwcommerce_discount_applies_to',
				'value' => 'specific_products'
			];

			$field = $this->pwcommerce->getInputfieldHidden($options);
			$hiddenInput = $field->render();

			$options = [
				'id' => "pwcommerce_discount_applies_to_header",
				// 'skipLabel' => Inputfield::skipLabelHeader,
				'label' => $label,
				'collapsed' => Inputfield::collapsedNever,
				'classes' => 'pwcommerce_gift_card_delivery_times_header',
				'wrapClass' => true,
				'wrapper_classes' => 'pwcommerce_no_outline',
				// 'description' => $this->_('xxxx.'),
				'value' => $this->_('Specify products that this discount applies to. ') . $hiddenInput
				// 'value' => $hiddenInput
			];

			$field = $this->pwcommerce->getInputfieldMarkup($options);
		}



		// -------
		return $field;
	}

	/**
	 * Get Markup For Discount Applies To Specific Categories Text Tags Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountAppliesToSpecificCategoriesTextTagsField() {
		// TODO: WHAT IF CATEGORY IS NOT USED FOR ANY PRODUCT!
		//------------------- pwcommerce_discount_applies_to_specific_categories (getInputfieldTextTags)
		$description = $this->_('Categories eligible for this discount.');
		$customHookURL = "/find-pwcommerce_discount_applies_to/";
		$tagsURL = "{$customHookURL}?q={q}&applies_to_type=specific_categories";
		$value = null;
		$setTagsList = [];

		// ======
		//for setting saved values if applicable
		if (in_array($this->discountAppliesToType, ['categories_percentage', 'categories_fixed'])) {
			$pageIDs = $this->discountAppliesTo->implode("|", 'itemID');
			$selector = "id={$pageIDs},include=hidden";
			/** @var array $pages */
			$pages = $this->wire('pages')->findRaw($selector, 'title');
			if (!empty($pages)) {
				// @note: $pages will be in the format $page->id => $page->title
				$value = array_keys($pages);
				$setTagsList = $pages;
			}
		}

		// TODO: IS THIS OK? ALLOW MORE?
		$placeholder = $this->_("Type at least 3 characters to search for categories.");

		$options = [
			'id' => "pwcommerce_discount_applies_to_specific_categories",
			// TODO: not really needed!
			'name' => "pwcommerce_discount_applies_to_specific_categories",
			'skipLabel' => Inputfield::skipLabelHeader,
			'label' => $this->_('Specific Categories'),
			// 'description' => $description . $extraDescriptionMarkup,
			'description' => $description,
			'useAjax' => true,
			// 'allowUserTags' => true,
			'closeAfterSelect' => false,
			'tagsUrl' => $tagsURL,
			'placeholder' => $placeholder,
			// 'maxItems' => 1,
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 50,
			'show_if' => "pwcommerce_discount_applies_to=specific_categories",
			// 'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top',
			'value' => $value,
			'set_tags_list' => $setTagsList,
		];

		$field = $this->pwcommerce->getInputfieldTextTags($options);
		// allow HTML in description
		// $field->entityEncodeText = false;

		return $field;
	}

	/**
	 * Get Markup For Discount Applies To Specific Products Text Tags Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountAppliesToSpecificProductsTextTagsField() {
		//------------------- pwcommerce_discount_applies_to_specific_products (getInputfieldTextTags)
		$description = $this->_('Products and variants eligible for this discount.');
		$notes = $this->_('To apply the discount to a product and all its variants, you only need to select the parent product. To apply the discount to selected variants, please specify the variants only.');
		$customHookURL = "/find-pwcommerce_discount_applies_to/";
		$tagsURL = "{$customHookURL}?q={q}&applies_to_type=specific_products";
		$value = null;
		$setTagsList = [];

		// ======
		//for setting saved values if applicable
		if (in_array($this->discountAppliesToType, ['products_percentage', 'products_fixed'])) {
			$pageIDs = $this->discountAppliesTo->implode("|", 'itemID');

			$selector = "id={$pageIDs},include=hidden";
			/** @var array $pages */
			$pages = $this->wire('pages')->findRaw($selector, 'title');
			//
			if (!empty($pages)) {
				// @note: $pages will be in the format $page->id => $page->title
				$value = array_keys($pages);
				$setTagsList = $pages;
			}
		}

		// TODO: IS THIS OK? ALLOW MORE?
		$placeholder = $this->_("Type at least 3 characters to search for products.");

		$options = [
			'id' => "pwcommerce_discount_applies_to_specific_products",
			// TODO: not really needed!
			'name' => "pwcommerce_discount_applies_to_specific_products",
			'skipLabel' => Inputfield::skipLabelHeader,
			'label' => $this->_('Specific Products'),
			// 'description' => $description . $extraDescriptionMarkup,
			'description' => $description,
			'notes' => $notes,
			'useAjax' => true,
			// 'allowUserTags' => true,
			'closeAfterSelect' => false,
			'tagsUrl' => $tagsURL,
			'placeholder' => $placeholder,
			// 'maxItems' => 1,
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 50,
			// 'show_if' => "pwcommerce_discount_applies_to=specific_products",
			// 'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top',
			'value' => $value,
			'set_tags_list' => $setTagsList,
		];

		// use show-if only if 'categories' feature installed in shop
		// else always show
		if (!empty($this->isProductCategoriesFeatureInstalled)) {
			$options['show_if'] = "pwcommerce_discount_applies_to=specific_products";
			// 'required' => true,
		}

		$field = $this->pwcommerce->getInputfieldTextTags($options);
		// allow HTML in description
		// $field->entityEncodeText = false;

		return $field;
	}

	/**
	 * Get Markup For Discount Fixed Apply Once Toggle Checkbox Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountFixedApplyOnceToggleCheckboxField() {
		//------------------- pwcommerce_discount_fixed_apply_once_toggle (getInputfieldCheckbox)

		// $checked = in_array($this->discount->discountType, ['products_fixed_per_order', 'categories_fixed_per_order']);
		$checked = !in_array($this->discount->discountType, ['products_fixed_per_item', 'categories_fixed_per_item']);
		$options = [
			'id' => "pwcommerce_discount_fixed_apply_once_toggle",
			'name' => "pwcommerce_discount_fixed_apply_once_toggle",
			// @note: skipping label
			'label' => ' ',
			'label2' => $this->_('Only apply discount once per order'),
			'notes' => $this->_("If not selected, the amount will be taken off each eligible item in an order."),
			'collapsed' => Inputfield::collapsedNever,
			'show_if' => "pwcommerce_discount_value_type=fixed",
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top pwcommerce_override_processwire_inputfield_content_padding_bottom',
			'value' => 1,
			// 'checked' => 'checked',
			// 'checked' => true,
			'checked' => $checked,

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);

		// -------
		return $field;
	}

	// ~~~~~~~~~~~~~~
	############ PROCESS INPUTS ###########
	// @NOTE: PARENT CLASS WILL DO MAIN PROCESSING
	// HERE WE ONLY PROCESS A FEW INPUTS UNIQUE TO PRODUCTS DISCOUNT

	/**
	 * Process Discount Value Type.
	 *
	 * @param WireInputData $input
	 * @return mixed
	 */
	protected function processDiscountValueType(WireInputData $input) {

		// @note: returns 'categories_fixed', 'categories_percentage', 'products_percentage' OR 'products_fixed'
		$discountValueTypeRaw = $this->getCombinedDiscountValueTypeRaw($input);

		// ++++++++++
		// special for fixed
		$discountValueTypeRawParts = explode("_", $discountValueTypeRaw); // e.g. 'categories_fixed'
		$discountValueTypeSuffixRaw = $discountValueTypeRawParts[1];
		if ($discountValueTypeSuffixRaw === 'fixed') {

			// determine if 'per order' or 'per item'
			$fixedValueTypeSuffix = (int) $input->pwcommerce_discount_fixed_apply_once_toggle ? '_per_order' : '_per_item';

			$discountValueTypeRaw .= $fixedValueTypeSuffix;

		}

		// ++++++++++

		$allowedDiscountTypes = $this->pwcommerce->getAllowedDiscountTypes();
		$discountValueType = $this->wire('sanitizer')->option($discountValueTypeRaw, $allowedDiscountTypes);

		// -------
		return $discountValueType;

	}

	/**
	 * Deduct discount value type from the values of two inputs.
	 *
	 * @param WireInputData $input
	 * @return mixed
	 */
	private function getCombinedDiscountValueTypeRaw(WireInputData $input) {
		# prefix
		// @note: 'categories' OR 'products' after string replace
		$discountValueTypePrefixRaw = str_replace('specific_', '', $input->pwcommerce_discount_applies_to);
		# suffix
		// @note: 'percentage' OR 'fixed'
		$discountValueTypeSuffixRaw = $input->pwcommerce_discount_value_type;
		# combined
		// @note: 'categories_percentage' OR 'products_percentage' OR 'categories_fixed' OR 'products_fixed'
		$discountValueTypeRaw = "{$discountValueTypePrefixRaw}_{$discountValueTypeSuffixRaw}";

		// -------
		return $discountValueTypeRaw;
	}

	/**
	 * Get Discount Applies To Type.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	private function getDiscountAppliesToType($input) {

		// @note: reusing the method to deduct the base discount value type
		// this is because the base values are identical to the applies to values
		// e.g. 'categories_fixed', 'products_percentage', etc
		$discountAppliesToType = $this->getCombinedDiscountValueTypeRaw($input);

		// ----
		return $discountAppliesToType;
	}

	/**
	 * Get Note For Discount Minimum Requirement Text Field.
	 *
	 * @return mixed
	 */
	protected function getNoteForDiscountMinimumRequirementTextField() {
		// @overrides parent::getNoteForDiscountMinimumRequirementTextField
		$notesCategories = $this->_('Applies to eligible categories.');
		$notesProducts = $this->_('Applies to eligible products.');
		$discountTypeParts = explode("_", (string) $this->discountType); // e.g. 'categories_fixed_per_item'
		$appliesToTypeBase = $discountTypeParts[0];
		if ($appliesToTypeBase === 'products') {
			$notes = $notesProducts;
		} else {
			// default to categories, irrespective
			$notes = $notesCategories;
		}
		return $notes;
	}

	/**
	 * Process Input For Discounts Apply To.
	 *
	 * @param WireInputData $input
	 * @return mixed
	 */
	protected function processInputForDiscountsApplyTo(WireInputData $input) {

		// TODO:
		// PROCESS INPUTS FOR 'FieldtypePWCommerceDiscountsApplyTo'
		// $discountAppliesTo
		// @NOTE: for products discount, we save one or more records
		// their itemType is one of: 'categories_percentage' OR 'products_percentage' OR 'categories_fixed' OR 'products_fixed'
		// @NOTE: can get this from FieldtypePWCommerceDiscount::discountType as well!
		// INPUTS:
		// pwcommerce_discount_value_type: radio (percentage|fixed) AND
		// pwcommerce_discount_applies_to: radio (specific_categories|specific_products)
		$sanitizer = $this->wire('sanitizer');
		$discountsApplyTo = NULL;

		// APPLIES TO TYPE
		$discountAppliesToType = $this->getDiscountAppliesToType($input);

		$allowedAppliesToItemTypes = $this->pwcommerce->getAllowedAppliesToItemTypes();
		$discountAppliesToItemType = $sanitizer->option($discountAppliesToType, $allowedAppliesToItemTypes);
		# ----------

		// ----
		if (!empty($discountAppliesToItemType)) {
			$field = $this->page->getField(PwCommerce::DISCOUNT_APPLIES_TO_FIELD_NAME);

			/** @var WireArray discountAppliesTo */
			$discountsApplyTo = $field->type->getBlankValue($this->page, $field);

			// ------------
			// SPECIFIC ITEMS (CATEGORIES or PRODUCTS)
			// we save one or more records BUT each share one itemType
			// -------------
			// input name is one of 'pwcommerce_discount_applies_to_specific_categories' OR 'pwcommerce_discount_applies_to_specific_products'
			$discountAppliesToItemTypeParts = explode("_", $discountAppliesToItemType); // e.g. 'categories_fixed'
			$discountAppliesToItemTypePrefix = $discountAppliesToItemTypeParts[0];
			$inputName = "pwcommerce_discount_applies_to_specific_{$discountAppliesToItemTypePrefix}";

			// @note: space separated values from 'InputfieldTextTags'
			$discountAppliesToPagesIDs = explode(" ", $input->{"{$inputName}"});

			if (!empty($discountAppliesToPagesIDs)) {
				foreach ($discountAppliesToPagesIDs as $discountAppliesToPageID) {
					if (empty($discountAppliesToPageID)) {
						continue;
					}
					// we save each record
					/** @var WireData $discountAppliesTo */
					$discountAppliesTo = $field->type->getBlankRecord();
					// $discountAppliesTo->itemID = (int) $discountAppliesToPageID;
					// @note: existing selectize items will be prefixed with '_', e.g. '_1234'
					// we remove the prefix
					$discountAppliesTo->itemID = (int) str_replace('_', '', $discountAppliesToPageID);
					$discountAppliesTo->itemType = $discountAppliesToItemType;
					// ---------
					// add to WireArray
					$discountsApplyTo->add($discountAppliesTo);
				}

			}

			// ---------

			// ========
			// TODO: DELETE WHEN DONE: WE NOW SAVE IN processInput() if no errors overall
			// SAVE PAGE field 'pwcommerce_discounts_apply_to'
			// @note: we need to save always since all previous values could have been deleted but no new ones supplied
			// hence, need to clear old values (TODO?)
			// $this->page->setAndSave(PwCommerce::DISCOUNT_APPLIES_TO_FIELD_NAME, $discountsApplyTo);
			if (empty($discountsApplyTo->count())) {
				$discountsApplyTo = NULL;

			}

		}

		// -----
		return $discountsApplyTo;

	}

	/**
	 * Get Error For Discounts Apply To.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	protected function getErrorForDiscountsApplyTo($input = NULL) {

		// TODO
		// for product discounts, error can be about missing radio (applies to specific_categories or specific_products) AND/OR missing IDs in selectize, i.e. missing categories or products IDs. Hence, need to tailor for these two scenarios!
		// TODO for now, just do for items? but, see if can get the 'categories' vs 'products' bit
		// --------
		// APPLIES TO TYPE
		$discountAppliesToType = $this->getDiscountAppliesToType($input);
		$allowedAppliesToItemTypes = $this->pwcommerce->getAllowedAppliesToItemTypes();
		$discountAppliesToItemType = $this->wire('sanitizer')->option($discountAppliesToType, $allowedAppliesToItemTypes);
		$discountAppliesToItemTypeParts = explode("_", $discountAppliesToItemType); // e.g. 'categories_fixed'
		$discountAppliesToItemTypePrefix = $discountAppliesToItemTypeParts[0];
		// prepare error message
		if ($discountAppliesToItemTypePrefix === 'categories') {
			$errorString = $this->_('Categories that this discount applies to need to be specified');
		} else if ($discountAppliesToItemTypePrefix === 'products') {
			$errorString = $this->_('Products that this discount applies to need to be specified');
		} else {
			$errorString = $this->_('Items that this discount applies to need to be specified');
		}

		// -----
		return $errorString;
	}

}