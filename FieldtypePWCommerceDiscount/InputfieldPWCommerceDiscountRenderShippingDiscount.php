<?php

namespace ProcessWire;

// load InputfieldPWCommerceDiscountRenderOrderDiscount class if not yet loaded by $pwcommerce
$inputfieldPWCommerceDiscountRenderOrderDiscountClassPath = __DIR__ . "/InputfieldPWCommerceDiscountRenderOrderDiscount.php";
require_once $inputfieldPWCommerceDiscountRenderOrderDiscountClassPath;

/**
 * PWCommerce: InputfieldPWCommerceDiscountRenderShippingDiscount
 *
 * Inputfield for FieldtypePWCommerceDiscount, the field that stores and outputs values of a PWCommerce discount.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceDiscountRenderShippingDiscount for PWCommerce
 * Copyright (C) 2023 by Francis Otieno
 * MIT License
 *
 */

// class InputfieldPWCommerceDiscountRenderShippingDiscount extends WireData
class InputfieldPWCommerceDiscountRenderShippingDiscount extends InputfieldPWCommerceDiscountRenderOrderDiscount
{

	private $discountAppliesToType;
	// for extra errors, i.e. for 'exclude shipping rates over certain amount'
	private $extraInputErrors = [];
	// to help with tracking cases where discountsApplyTo is valid but 'exclude shipping rates over certain amount' is invalid
	private $isValidDiscountsApplyTo;


	public function __construct($page, $field) {
		parent::__construct($page, $field);
		// @NOTE: WE INHERIT BELOW PROPS FROM PARENT CLASS 'InputfieldPWCommerceDiscountRenderOrderDiscount'
		// --------
		// SET DISCOUNT APPLIES TO TYPE
		// one of 'shipping_all_countries'| 'shipping_selected_countries'
		$this->setDiscountAppliesToCountriesMode();
		// --------
		// --------

	}

	private function setDiscountAppliesToCountriesMode() {
		$firstItemDiscountAppliesTo = $this->discountAppliesTo->first();
		if (!empty($firstItemDiscountAppliesTo)) {
			$this->discountAppliesToType = $firstItemDiscountAppliesTo->itemType;
		}
	}

	/**
	 * Render the entire input area for product discount
	 *
	 */
	public function ___render() {
		// @overrides parent::render
		$xinit = $this->getInitValuesForAlpineJS();
		$out =
			"<div id='pwcommerce_shipping_discount_wrapper' {$xinit}>" .
			// TODO ADD 2*COLUMN GRID HERE
			$this->buildForm() .
			"</div>";
		return $out;
	}

	protected function getDiscountsFormHeader() {
		// @overrides parent::getDiscountsFormHeader
		$discountTypeHeader =
			// discount type header
			"<h4>" . $this->_('Shipping Discount (free shipping)') . "</h4>";
		// ------
		return $discountTypeHeader;
	}

	protected function renderDiscountMethod($wrapper) {
		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = parent::renderDiscountMethod($wrapper);

		// REMOVE the 'METHOD' Inputfield since not needed in shipping discount
		// also REMOVE the 'AUTOMATIC' Inputfield
		$removeInputfields = [
			'pwcommerce_discount_method',
			'pwcommerce_discount_method_automatic'
		];
		foreach ($removeInputfields as $removeInputfield) {
			$wrapper->remove($removeInputfield);
		}
		// REMOVE SHOW IF FROM 'pwcommerce_discount_method_code' text box & 'pwcommerce_discount_method_code_generate' button
		$selector = "name=pwcommerce_discount_method_code|pwcommerce_discount_method_code_generate";
		// $child = $wrapper->child('pwcommerce_discount_method_code');
		$children = $wrapper->children($selector);
		// $child->showIf = "";
		foreach ($children as $child) {
			$child->showIf = "";
		}
		// --------
		return $wrapper;
	}

	protected function renderDiscountValue($wrapper) {
		// @overrides parent::renderDiscountValue
		// TODO WE WILL REPLACE THIS WITH SHIPPING COUNTRIES RADIOS
		// TODO? any other way? a bit dirty/hacky! TRY DO IN BUILD FORM INSTEAD OR GETDISCOUNTS WRAPPER INSTEAD!
		// GET WRAPPER FOR DISCOUNT RENDER VALUE HERE
		// $wrapper = parent::renderDiscountValue($wrapper);
		# @NOTE: REPLACING renderDiscountValue with renderDiscountAppliesTo() from this class
		// APPEND DISCOUNT SHIPPING 'DISCOUNTS APPLIES TO' WRAPPER
		$wrapper = $this->renderDiscountAppliesTo($wrapper);
		// ---
		// -----
		return $wrapper;
	}

	protected function getValueForDiscountType() {
		// @overrides parent::getValueForDiscountType
		// @note though: not in use since this type will always be 'free shipping' in addition, the value will always be 'free shipping', with the caveat of 'exclude shipping above amount'
		$value = 'free_shipping';
		//------
		return $value;
	}

	### APPLIES TO ###
	private function renderDiscountAppliesTo($wrapper) {
		// radio to select applies to type
		$field = $this->getMarkupForDiscountAppliesToRadioField();
		$wrapper->add($field);
		// selectize text input for searching specific countries
		$field = $this->getMarkupForDiscountAppliesToSelectedCountriesTextTagsField();
		$wrapper->add($field);
		// checkbox input to toggle show 'Exclude shipping rates over a certain amount'
		$field = $this->getMarkupForDiscountExcludeShippingRatesOverAmountToggleCheckboxField();
		$wrapper->add($field);
		// text input for exclude shipping rates above amount
		$field = $this->getMarkupForDiscountExcludeShippingRatesOverAmountTextField();
		$wrapper->add($field);
		// divider markup for sections that need it
		$field = $this->getMarkupForDiscountSectionsDividerMarkupField("applies_to");
		$wrapper->add($field);
		// -----
		return $wrapper;
	}

	private function getMarkupForDiscountAppliesToRadioField() {
		//------------------- pwcommerce_discount_applies_to (getInputfieldRadios)
		//
		$appliesToType = $this->discountAppliesToType;

		// @note: radio values here match the saved values, i.e. 'shipping_all_countries' OR 'shipping_selected_countries'
		$value = !empty($appliesToType) ? $appliesToType : 'shipping_all_countries';

		$radioOptions = [
			'shipping_all_countries' => __('All countries'),
			'shipping_selected_countries' => __('Selected countries'),
		];

		$options = [
			'id' => "pwcommerce_discount_applies_to",
			'name' => 'pwcommerce_discount_applies_to',
			'label' => $this->_('Applies To'),
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 33,
			// 'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
			'radio_options' => $radioOptions,
			'value' => $value,
		];

		$field = $this->pwcommerce->getInputfieldRadios($options);

		// -------
		return $field;
	}

	private function getMarkupForDiscountAppliesToSelectedCountriesTextTagsField() {
		// TODO: WHAT IF COUNTRY IS NOT in any zone? rest of the world?!
		//------------------- pwcommerce_discount_applies_to_selected_countries (getInputfieldTextTags)
		$description = $this->_('Countries eligible for this discount.');
		$customHookURL = "/find-pwcommerce_discount_applies_to/";
		$tagsURL = "{$customHookURL}?q={q}&applies_to_type=selected_countries";
		$value = null;
		$setTagsList = [];

		// ======
		//for setting saved values if applicable
		if ($this->discountAppliesToType === 'shipping_selected_countries') {
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
		$placeholder = $this->_("Type at least 3 characters to search for countries.");

		$options = [
			'id' => "pwcommerce_discount_applies_to_selected_countries",
			// TODO: not really needed!
			'name' => "pwcommerce_discount_applies_to_selected_countries",
			// @note: skipping label
			'skipLabel' => Inputfield::skipLabelHeader,
			'label' => $this->_('Specific Countries'),
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
			'show_if' => "pwcommerce_discount_applies_to=shipping_selected_countries",
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

	private function getMarkupForDiscountExcludeShippingRatesOverAmountToggleCheckboxField() {
		//------------------- pwcommerce_discount_exclude_rates_over_certain_amount_toggle (getInputfieldCheckbox)
// @note: comes from META! @see: FieldtypePWCommerceDiscount::wakeupValue()
		$checked = !empty($this->discount->excludeShippingAmountOver);
		// ---
		$label2 = sprintf(__("Exclude shipping rates over a certain amount %s."), $this->shopCurrencySymbolString);
		$options = [
			'id' => "pwcommerce_discount_exclude_rates_over_certain_amount_toggle",
			'name' => "pwcommerce_discount_exclude_rates_over_certain_amount_toggle",
			'label' => $this->_('Shipping Rates'),
			// 'label2' => $this->_('Exclude shipping rates over a certain amount'),
			'label2' => $label2,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top',
			'value' => 1,
			// 'checked' => 'checked',
			// 'checked' => true,
			'checked' => $checked,

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);

		// -------
		return $field;
	}

	private function getMarkupForDiscountExcludeShippingRatesOverAmountTextField() {
		//------------------- pwcommerce_discount_exclude_rates_over_certain_amount (getInputfieldText)
		$options = [
			'id' => "pwcommerce_discount_exclude_rates_over_certain_amount",
			'name' => "pwcommerce_discount_exclude_rates_over_certain_amount",
			'type' => 'number',
			'step' => '0.01',
			// 'min' => 1,// @note: chrome error An invalid form control with name='pwcommerce_discount_exclude_rates_over_certain_amount' is not focusable'. Happens if excludeShippingAmountOver == 0 and show_if is hiding this input
			'min' => 0,
			// @note: skipping label
			'label' => $this->_('Limit Rate Amount'),
			'skipLabel' => Inputfield::skipLabelHeader,
			'notes' => $this->_('Discount will not be applied if the shipping rate exceeds this amount. Untick the checkbox for no limit.'),
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 75,
			'size' => 30,
			'show_if' => "pwcommerce_discount_exclude_rates_over_certain_amount_toggle=1",
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top',
			'value' => $this->discount->excludeShippingAmountOver
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		// allow HTML in description
		// $field->entityEncodeText = false;

		return $field;
	}

	// ~~~~~~~~~~~~~~
	############ PROCESS INPUTS ###########
	// @NOTE: PARENT CLASS WILL DO MAIN PROCESSING
	// HERE WE ONLY PROCESS A FEW INPUTS UNIQUE TO SHIPPING DISCOUNT

	protected function processDiscountValueType(WireInputData $input) {
		// @note: this is always 'free_shipping'
		// nothing to process; just return the default
		$discountValueType = $this->getValueForDiscountType();
		// -------
		return $discountValueType;
	}

	protected function processDiscountValue(WireInputData $input) {
		// @note: this is always 'free_shipping' hence 100% discount on shipping (bar exclusions)
		// hence, just return 100
		$discountValue = 100;
		// -------
		return $discountValue;
	}

	protected function processDiscountMetaData(WireInputData $input) {
		// @note: only add exclude shipping rates over amount if applicable
		$metaData = '';
		// -----
		if (!empty((int) $input->pwcommerce_discount_exclude_rates_over_certain_amount_toggle)) {
			$excludeShippingRatesOverAmount = (float) $input->pwcommerce_discount_exclude_rates_over_certain_amount;
			if (!empty($excludeShippingRatesOverAmount)) {
				$metaDataArray = [
					'exclude_shipping_amount_over' => $excludeShippingRatesOverAmount
				];
				$metaData = json_encode($metaDataArray);
			}
		}

		// -----
		return $metaData;
	}

	protected function processInputForDiscountsApplyTo(WireInputData $input) {

		// TODO:
		// PROCESS INPUTS FOR 'FieldtypePWCommerceDiscountsApplyTo'
		// $discountAppliesTo
		// @NOTE: for shipping discount, we save one or more records
		// their itemType is one of: 'shipping_all_countries' OR 'shipping_selected_countries'
		// @NOTE: can get this from FieldtypePWCommerceDiscount::discountType as well!
		// INPUTS:
		// pwcommerce_discount_applies_to: selectize input
		$sanitizer = $this->wire('sanitizer');
		$discountsApplyTo = NULL;

		// APPLIES TO TYPE
		$discountAppliesToType = $input->pwcommerce_discount_applies_to;

		$allowedAppliesToItemTypes = $this->pwcommerce->getAllowedAppliesToItemTypes();
		$discountAppliesToItemType = $sanitizer->option($discountAppliesToType, $allowedAppliesToItemTypes);

		# ----------

		// ----
		if (!empty($discountAppliesToItemType)) {
			$field = $this->page->getField(PwCommerce::DISCOUNT_APPLIES_TO_FIELD_NAME);

			/** @var WireArray discountAppliesTo */
			$discountsApplyTo = $field->type->getBlankValue($this->page, $field);

			if ($discountAppliesToItemType === 'shipping_all_countries') {
				// ALL COUNTRIES
				// we save only one record
				/** @var WireData $discountAppliesTo */
				$discountAppliesTo = $field->type->getBlankRecord();
				$discountAppliesTo->itemID = 0;
				$discountAppliesTo->itemType = $discountAppliesToItemType; // 'shipping_all_countries'
				// ---------
				// add to WireArray
				$discountsApplyTo->add($discountAppliesTo);

			} elseif ($discountAppliesToItemType === 'shipping_selected_countries') {
				// SELECTED COUNTRIES
				// @note: space separated values from 'InputfieldTextTags'
				$countriesIDs = explode(" ", $input->pwcommerce_discount_applies_to_selected_countries);

				if (!empty($countriesIDs)) {
					foreach ($countriesIDs as $countryID) {
						if (empty($countryID)) {
							continue;
						}
						// we save each record
						/** @var WireData $discountAppliesTo */
						$discountAppliesTo = $field->type->getBlankRecord();
						// $discountAppliesTo->itemID = (int) $countryID;
						// @note: existing selectize items will be prefixed with '_', e.g. '_1234'
						// we remove the prefix
						$discountAppliesTo->itemID = (int) str_replace('_', '', $countryID);
						$discountAppliesTo->itemType = $discountAppliesToItemType;
						// ---------
						// add to WireArray
						$discountsApplyTo->add($discountAppliesTo);
					}

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
			} else {
				$this->isValidDiscountsApplyTo = true;
			}
			// ========
			// ALSO CHECK IF EXCLUDING SHIPPING RATES OVER A CERTAIN AMOUNT AND IF NO AMOUNT STATED
			if (!$this->isValidExcludeShippingRateAmount($input)) {
				$discountsApplyTo = NULL;
			}

		}

		// -----
		return $discountsApplyTo;

	}

	private function isValidExcludeShippingRateAmount($input) {
		$isValidExcludeShippingRateAmount = true;
		// -----
		if (!empty((int) $input->pwcommerce_discount_exclude_rates_over_certain_amount_toggle)) {
			$excludeShippingRatesOverAmount = (float) $input->pwcommerce_discount_exclude_rates_over_certain_amount;
			if (empty($excludeShippingRatesOverAmount)) {
				$isValidExcludeShippingRateAmount = false;
			}
		}
		// ------
		return $isValidExcludeShippingRateAmount;
	}

	protected function getErrorForDiscountsApplyTo($input = NULL) {
		// TODO
		// for product discounts, error can be about missing radio (applies to shipping_all_countries or shipping_selected_countries) AND/OR missing IDs in selectize, i.e. missing countries IDs. Hence, need to tailor for these two scenarios!
		// TODO for now, just do for items?
		// --------
		// APPLIES TO TYPE
		$errorString = "";
		if (empty($this->isValidDiscountsApplyTo)) {
			$errorString = $this->_('Countries that this discount applies to need to be specified');
		}
		// ========
		// @see $this->processExtraInputErrors()!
		// ALSO CHECK IF EXCLUDING SHIPPING RATES OVER A CERTAIN AMOUNT AND IF NO AMOUNT STATED
		if (!$this->isValidExcludeShippingRateAmount($input)) {
			$extraErrorString = $this->_('Exclude shipping rate amount cannot be zero');
			// add extra error to $extraInputErrors
			$this->extraInputErrors[] = $extraErrorString;
		}
		// -----
		return $errorString;
	}

	protected function processExtraInputErrors($errors) {
		// get parent errors and add to them, if applicable
		$errors = parent::processExtraInputErrors($errors);
		if (!empty($this->extraInputErrors)) {
			// merge errors
			$errors = array_merge($errors, $this->extraInputErrors);
		}
		return $errors;
	}

}