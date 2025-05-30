<?php

namespace ProcessWire;

// load InputfieldPWCommerceDiscountRenderOrderDiscount class if not yet loaded by $pwcommerce
$inputfieldPWCommerceDiscountRenderOrderDiscountClassPath = __DIR__ . "/InputfieldPWCommerceDiscountRenderOrderDiscount.php";
require_once $inputfieldPWCommerceDiscountRenderOrderDiscountClassPath;

/**
 * PWCommerce: InputfieldPWCommerceDiscountRenderBuyXGetYDiscount
 *
 * Inputfield for FieldtypePWCommerceDiscount, the field that stores and outputs values of a PWCommerce discount.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceDiscountRenderBuyXGetYDiscount for PWCommerce
 * Copyright (C) 2023 by Francis Otieno
 * MIT License
 *
 */



// class InputfieldPWCommerceDiscountRenderBuyXGetYDiscount extends WireData
class InputfieldPWCommerceDiscountRenderBuyXGetYDiscount extends InputfieldPWCommerceDiscountRenderOrderDiscount
{



	private $discountAppliesToType;
	private $discountBuyXEligibilityType;
	private $discountBuyXEligibilityItems;
	// for extra errors, i.e. for 'buy x eligibility issues'
	private $extraInputErrors = [];
	// to help with tracking cases where customer eligibility is valid but 'buy x' is invalid
	private $isValidDiscountsBuyXEligibility;



	public function __construct($page, $field) {
		parent::__construct($page, $field);
		// @NOTE: WE INHERIT BELOW PROPS FROM PARENT CLASS 'InputfieldPWCommerceDiscountRenderOrderDiscount'

		// --------
		// SET DISCOUNT APPLIES TO TYPE
		// one of 'categories_get_y'| 'products_get_y'
		$this->setDiscountAppliesToGetYMode();
		// SET DISCOUNT ELIGIBILITY EXCLUSIVE ITEMS
		// @note: $this->discountCustomerEligibility CONTAINS BOTH 'BUY X' ITEMS AND 'CUSTOMER ELIGIBILITY' ITEMS
		// here we split these
		$this->setDiscountEligibilityExclusiveItems();
		// SET DISCOUNT ELIGIBILITY FOR BUY X TYPE
		// one of 'products_buy_x'| 'categories_buy_x'
		$this->setDiscountEligibilityBuyXMode();
		// --------

	}

	private function setDiscountAppliesToGetYMode() {
		$firstItemDiscountAppliesTo = $this->discountAppliesTo->first();
		if (!empty($firstItemDiscountAppliesTo)) {
			$this->discountAppliesToType = $firstItemDiscountAppliesTo->itemType;
		}

	}

	private function setDiscountEligibilityExclusiveItems() {
		// SET DISCOUNT ELIGIBILITY EXCLUSIVE ITEMS
		// @note: $this->discountCustomerEligibility CONTAINS BOTH 'BUY X' ITEMS AND 'CUSTOMER ELIGIBILITY' ITEMS
		// here we split these
		// @note: we could have used remove, filter, etc as well
		// --------
		$discountBuyXEligibilityItems = new WireArray();
		$discountCustomerEligibilityItems = new WireArray();
		$allDiscountEligibilityItems = $this->discountCustomerEligibility;
		$allowedBuyXEligibilityItemTypes = ['categories_buy_x', 'products_buy_x'];
		$allowedCustomerEligibilityItemTypes = ['all_customers', 'specific_customers', 'customer_groups'];
		foreach ($allDiscountEligibilityItems as $discountEligibilityItem) {
			if (in_array($discountEligibilityItem->itemType, $allowedBuyXEligibilityItemTypes)) {
				// BUY X ITEM TYPES
				$discountBuyXEligibilityItems->add($discountEligibilityItem);
			} else if (in_array($discountEligibilityItem->itemType, $allowedCustomerEligibilityItemTypes)) {
				// CUSTOMER ITEM TYPES
				$discountCustomerEligibilityItems->add($discountEligibilityItem);
			}
		}
		// ------
		// set to class properties
		// buy x items: @note: setting to a private class property
		$this->discountBuyXEligibilityItems = $discountBuyXEligibilityItems;
		// customer: @note: overwriting origanal parent value!
		$this->discountCustomerEligibility = $discountCustomerEligibilityItems;

	}

	protected function setDiscountCustomerEligibilityType() {
		// @note: THIS NEEDS TO GET ONLY 'CUSTOMERS'
		// IT NEEDS TO EXCLUDE 'products_buy_x' OR 'categories_buy_x'!
		// $firstItemDiscountEligibility = $this->discountCustomerEligibility->first();
		$selector = "itemType!=products_buy_x|categories_buy_x";

		$firstItemDiscountEligibility = $this->discountCustomerEligibility->get($selector);
		if (!empty($firstItemDiscountEligibility)) {
			$this->discountCustomerEligibilityType = $firstItemDiscountEligibility->itemType;
		}

	}

	private function setDiscountEligibilityBuyXMode() {
		// @note: THIS NEEDS TO GET ONLY 'products_buy_x' OR 'categories_buy_x'
		// IT NEEDS TO EXCLUDE CUSTOMERS!
		//
		$firstItemDiscountBuyXEligibility = $this->discountBuyXEligibilityItems->first();
		// $selector = "itemType=products_buy_x|categories_buy_x";
		//
		// $firstItemDiscountBuyXEligibility = $this->discountCustomerEligibility->get($selector);
		if (!empty($firstItemDiscountBuyXEligibility)) {
			$this->discountBuyXEligibilityType = $firstItemDiscountBuyXEligibility->itemType;
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
			"<div id='pwcommerce_buy_x_get_y_discount_wrapper' {$xinit}>" .
			// TODO ADD 2*COLUMN GRID HERE
			$this->buildForm() .
			"</div>";
		return $out;
	}

	protected function getInitValuesArrayForAlpineJS() {
		// @overrides parent::getInitValuesArrayForAlpineJS
		$radioValues = parent::getInitValuesArrayForAlpineJS();

		// @note: 'purchase' OR 'quantity'
		$radioValues['discount_customer_buys_minimum_type_selected'] = $this->getValueForMinimumRequirementType();

		// -------
		return $radioValues;
	}

	protected function getDiscountsFormHeader() {
		// @overrides parent::getDiscountsFormHeader
		$discountTypeHeader =
			// discount type header
			"<h4>" . $this->_('Product Discount (Buy X get Y)') . "</h4>";
		// ------
		return $discountTypeHeader;
	}

	protected function renderDiscountValue($wrapper) {
		// @overrides parent::renderDiscountValue
		// REMOVE the 'RENDER VALUE' Inputfields since not needed in Buy X Get Y discount
		// just return the wrapper without adding anything
		// -----
		return $wrapper;
	}

	protected function renderDiscountMinimumRequirement($wrapper) {
		// @overrides parent::renderDiscountMinimumRequirement
		// TOTALLY REPLACE WITH 'CUSTOMER BUYS X' & 'CUSTOMER GETS Y' INPUTFIELDS
		# customer buys x
		$wrapper = $this->renderDiscountCustomerBuysX($wrapper);
		# customer gets y
		$wrapper = $this->renderDiscountCustomerGetsY($wrapper);
		// -----
		return $wrapper;
	}

	private function getValueForMinimumRequirementType() {
		// @note: 'purchase' or 'quantity'
		$value = !empty($this->discount->discountMinimumRequirementType) ? $this->discount->discountMinimumRequirementType : 'quantity';
		//------
		return $value;
	}

	### MINIMIMUM REQUIREMENT + ELIGIBILITY (CUSTOMER BUYS X) ###
	private function renderDiscountCustomerBuysX($wrapper) {
		// radio to select customer buys x minimum requirement type
		$field = $this->getMarkupForDiscountCustomerBuysMinimumRadioField();
		$wrapper->add($field);
		// text input for specifying customer buys minimum requirement amount or purchase
		$field = $this->getMarkupForDiscountCustomerBuysGetsAmountTextField();
		$wrapper->add($field);
		// radio to select items from for buys x items
		$field = $this->getMarkupForDiscountCustomerBuysGetsItemsFromRadioField();
		$wrapper->add($field);
		// ----
		if (!empty($this->isProductCategoriesFeatureInstalled)) {
			// selectize text input for searching specific categories for 'buys x'
			$field = $this->getMarkupForDiscountCustomerBuysGetsCategoriesTextTagsField();
			$wrapper->add($field);
		}
		// ----
		// selectize text input for searching specific products & variants for 'buys x'
		$field = $this->getMarkupForDiscountCustomerBuysGetsProductsTextTagsField();
		$wrapper->add($field);
		// divider markup for sections that need it
		$field = $this->getMarkupForDiscountSectionsDividerMarkupField("buy_x");
		$wrapper->add($field);
		// -----
		return $wrapper;
	}

	protected function getMarkupForDiscountCustomerBuysMinimumRadioField() {
		//------------------- pwcommerce_discount_customer_buys_minimum_type (getInputfieldRadios)

		$purchaseRadio = sprintf(__("Minimum purchase amount %s"), $this->shopCurrencySymbolString);
		$radioOptions = [
			'quantity' => __('Minimum quantity of items'),
			'purchase' => $purchaseRadio,
		];

		// ------
		// for x-show for buys minimum quantity
		$labelTextQuantity = $this->_('Customer Spends');
		$label = "<span x-show='{$this->xstore}.discount_customer_buys_minimum_type_selected==`quantity`'>" . $labelTextQuantity . "</span>";
		// for x-show for buys minimum purchase
		$labelTextPurchase = $this->_('Customer Buys');
		$label .= "<span x-show='{$this->xstore}.discount_customer_buys_minimum_type_selected==`purchase`'>" . $labelTextPurchase . "</span>";

		// ------
		// @NOTE: 'discountMinimumRequirementType'
		$value = $this->getValueForMinimumRequirementType();

		$options = [
			'id' => "pwcommerce_discount_customer_buys_minimum_type",
			'name' => 'pwcommerce_discount_customer_buys_minimum_type',
			// 'label' => $this->_('Customer Spends SPAN X-SHOW Customer Buys'),
			'label' => $label,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			// 'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_bottom pwcommerce_discounts_radios_wrapper',
			'radio_options' => $radioOptions,
			'value' => $value,
		];

		$field = $this->pwcommerce->getInputfieldRadios($options);

		// TODO CONFIRM => ALPINE DOES NOT WORK WITH PROCESSWIRE RADIOS
		$field->entityEncodeLabel = false;

		// +++++++++
		// TODO DO WE NEED THIS? IF YES, CHANGE THE VALUE!
		// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change
		$field->wrapAttr('data-discount-radio-change-type', 'discount_customer_buys_minimum_type');

		// -------
		return $field;
	}

	### APPLIES TO (GETS Y) ###
	private function renderDiscountCustomerGetsY($wrapper) {
		$mode = "get_y";
		// markup to show customer gets header
		$field = $this->getMarkupForDiscountCustomerGetsYHeaderMarkupField();
		$wrapper->add($field);
		// radio to select items from for get y items
		$field = $this->getMarkupForDiscountCustomerBuysGetsItemsFromRadioField($mode);
		$wrapper->add($field);
		// text input for specifying customer needs to add 'Y' items to their cart
		$field = $this->getMarkupForDiscountCustomerBuysGetsAmountTextField($mode);
		$wrapper->add($field);
		// ----
		if (!empty($this->isProductCategoriesFeatureInstalled)) {
			// selectize text input for searching specific categories for 'gets y'
			$field = $this->getMarkupForDiscountCustomerBuysGetsCategoriesTextTagsField($mode);
			$wrapper->add($field);
		}
		// ----
		// selectize text input for searching specific products & variants for 'gets y'
		$field = $this->getMarkupForDiscountCustomerBuysGetsProductsTextTagsField($mode);
		$wrapper->add($field);
		// radio to select discounted value for get y items
		$field = $this->getMarkupForDiscountCustomerGetsYDiscountedValueRadioField();
		$wrapper->add($field);
		// text input for specifying discount value/amount PERCENT if get y items 'NOT FREE'
		$field = $this->getMarkupForDiscountCustomerGetsYDiscountedValuePercentTextField();
		$wrapper->add($field);
		// checkbox input to toggle show 'Set a maximum number of uses per order'
		$field = $this->getMarkupForDiscountMaximumUsagePerOrderAmountToggleCheckboxField();
		$wrapper->add($field);
		// text input for specifying maximum usage amount
		$field = $this->getMarkupForDiscountMaximumUsagePerOrderAmountTextField();
		$wrapper->add($field);
		// divider markup for sections that need it
		$field = $this->getMarkupForDiscountSectionsDividerMarkupField("applies_to");
		$wrapper->add($field);
		// -----
		return $wrapper;
	}

	private function getMarkupForDiscountCustomerGetsYHeaderMarkupField() {
		//------------------- active dates header markup (getInputfieldMarkup)
		$options = [
			'id' => "pwcommerce_discount_customer_get_y_header",
			// 'skipLabel' => Inputfield::skipLabelHeader,
			'label' => $this->_('Customer Gets'),
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_gift_card_delivery_times_header',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'description' => $this->_('Customers must add the quantity of items specified below to their cart.'),
			'value' => ""
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		// -------
		return $field;
	}

	private function getMarkupForDiscountCustomerGetsYDiscountedValueRadioField() {
		//------------------- pwcommerce_discount_customer_get_y_discounted_value_type (getInputfieldRadios)

		// @NOTE: WE WON'T SAVE THESE VALUES. HOWEVER,
		$radioOptions = [
			'percentage' => __('Percent'),
			'free' => __('Free'),
		];

		$free = (float) 100;

		// ------
// TODO?
		$value = (float) $this->discount->discountValue === $free ? 'free' : 'percentage';

		$options = [
			'id' => "pwcommerce_discount_customer_get_y_discounted_value_type",
			'name' => 'pwcommerce_discount_customer_get_y_discounted_value_type',
			'label' => $this->_('At a Discounted Value'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			// 'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_bottom pwcommerce_discounts_radios_wrapper',
			'radio_options' => $radioOptions,
			'value' => $value,
		];

		$field = $this->pwcommerce->getInputfieldRadios($options);

		// TODO CONFIRM => ALPINE DOES NOT WORK WITH PROCESSWIRE RADIOS?

		// +++++++++
// TODO DO WE NEED THIS? IF YES, CHANGE THE VALUE!
// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change
		$field->wrapAttr('data-discount-radio-change-type', 'discount_value_type');

		// -------
		return $field;
	}

	private function getMarkupForDiscountCustomerGetsYDiscountedValuePercentTextField() {
		//------------------- pwcommerce_discount_value (getInputfieldText)

		$free = (float) 100;

		// ------
		// TODO?
		// if discount is free, no need to show the 100(%); show zero in case they switch to 'percentage'
		$value = (float) $this->discount->discountValue === $free ? 0 : $this->discount->discountValue;

		$options = [
			'id' => "pwcommerce_discount_value",
			'name' => "pwcommerce_discount_value",
			'type' => 'number',
			// 'step' => '0.1',
			'step' => '0.01',
			// TODO 0.01?
			'min' => 0,
			// max percentage value to 100
			'max' => 100,
			'label' => $this->_('Discount Value'),
			// @note: skipping header label!
			'skipLabel' => Inputfield::skipLabelHeader,
			// 'description' => $this->_('Percentage (%) off.'),
			// @note: x-show by alpine above!
			'description' => $this->_('Percentage (%).'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			'size' => 30,
			'show_if' => "pwcommerce_discount_customer_get_y_discounted_value_type=percentage",
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top pwcommerce_override_processwire_inputfield_content_padding_bottom',
			// 'value' => $this->discount->discountValue
			// @see not above about '100%'
			'value' => $value
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		$field->attr([
			// ==========
			// ------
			'x-init' => "setStoreValue(`discount_value`,`{$value}`)",
			'x-model' => "{$this->xstore}.discount_value",
			'x-on:change.debounce' => 'handleDiscountValueChange',

		]);
		$field->entityEncodeText = false;
		// $field->appendMarkup = "<hr>";
		// ----
		return $field;
	}

	private function getMarkupForDiscountMaximumUsagePerOrderAmountToggleCheckboxField() {
		//------------------- pwcommerce_discount_set_maximum_usage_per_order_toggle (getInputfieldCheckbox)
// @note: comes from META! @see: FieldtypePWCommerceDiscount::wakeupValue()
		$checked = !empty($this->discount->maximumUsagePerOrder);
		// ---
		$options = [
			'id' => "pwcommerce_discount_set_maximum_usage_per_order_toggle",
			'name' => "pwcommerce_discount_set_maximum_usage_per_order_toggle",
			// @note: skipping label
			'label' => ' ',
			// 'label2' => $this->_('Exclude shipping rates over a certain amount'),
			'label2' => $this->_('Set a maximum number of uses per order'),
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

	private function getMarkupForDiscountMaximumUsagePerOrderAmountTextField() {
		//------------------- pwcommerce_discount_set_maximum_usage_per_order (getInputfieldText)
		$options = [
			'id' => "pwcommerce_discount_set_maximum_usage_per_order",
			'name' => "pwcommerce_discount_set_maximum_usage_per_order",
			'type' => 'number',
			'step' => '1',
			// 'min' => 1,// @note: chrome error An invalid form control with name='pwcommerce_discount_set_maximum_usage_per_order' is not focusable'. Happens if excludeShippingAmountOver == 0 and show_if is hiding this input
			'min' => 0,
			// @note: skipping label
			'label' => $this->_('Maximum Usage Amount'),
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 75,
			'size' => 30,
			'show_if' => "pwcommerce_discount_set_maximum_usage_per_order_toggle=1",
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top',
			'value' => $this->discount->maximumUsagePerOrder
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		// allow HTML in description
		// $field->entityEncodeText = false;

		return $field;
	}
	### SHARED INPUTS ###

	private function getMarkupForDiscountCustomerBuysGetsAmountTextField($mode = 'buy_x') {

		//------------------- pwcommerce_discount_value (getInputfieldText)
		# @NOTE: this input is reused between buys x and gets y
		// ##############
		$step = null;
		$descriptionTextItemsCount = $this->_('Quantity of items');
		// +++++++++++++++
		if ($mode === 'get_y') {
			// GETS Y VALUES
			$idName = 'pwcommerce_discount_customer_get_y_discounted_items_amount';
			$description = $descriptionTextItemsCount;
			$step = 1;
			// @note: from META!
			$value = $this->discount->getYDiscountedItemsAmount;

		} else {
			// BUYS X VALUES
			############
			$idName = 'pwcommerce_customer_buy_x_amount';
			// $step = "0.1"; // TODO WE NEED TO BIND THIS FOR QUANTITYS!
			$value = $this->discount->discountMinimumRequirementAmount;
			$attrs = [
				// ==========
				// if 'quantity' step is '1' else for purchase, it is '0.01'
				'x-bind:step' => "{$this->xstore}.discount_customer_buys_minimum_type_selected==`quantity` ? 1 : `0.01`",
			];

			// ----
			// x-show description for quantity value
			$descriptionTextQuantity = $this->_('Quantity of items');
			// for quantity
			$description = "<span x-show='{$this->xstore}.discount_customer_buys_minimum_type_selected==`quantity`'>" . $descriptionTextQuantity . "</span>";
			// x-show description for purchase value
			$descriptionTextPurchase = sprintf(__("Amount %s"), $this->shopCurrencySymbolString);
			$description .= "<span x-show='{$this->xstore}.discount_customer_buys_minimum_type_selected==`purchase`'>" . $descriptionTextPurchase . "</span>";
		}

		// ++++++++++++++

		$options = [
			'id' => $idName,
			'name' => $idName,
			'type' => 'number',
			// 'step' => $step,
			// TODO 0.01?
			'min' => 0,
			'label' => $this->_('Amount'),
			// @note: skipping header label!
			'skipLabel' => Inputfield::skipLabelHeader,
			// @note: x-show by alpine above!
			'description' => $description,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			'size' => 30,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top pwcommerce_override_processwire_inputfield_content_padding_bottom',
			'value' => $value
		];

		if (!empty($step)) {
			$options['step'] = $step;
		}

		$field = $this->pwcommerce->getInputfieldText($options);
		$field->entityEncodeText = false;
		// $field->appendMarkup = "<hr>";
		if (!empty($attrs)) {
			$field->attr($attrs);
		}
		// ----
		return $field;
	}

	protected function getMarkupForDiscountCustomerBuysGetsItemsFromRadioField($mode = 'buy_x') {
		//------------------- pwcommerce_discount_customer_buys_gets_items_from_type (getInputfieldRadios)

		// @NOTE WE USE $mode TO MATCH EXPECTED DB VALUE!
		$radioOptions = [
			"products_{$mode}" => __('Specific products'),
			"categories_{$mode}" => __('Specific categories'),
		];

		if ($mode === 'get_y') {
			// GET Y (FieldtypeDiscountsApplyTo)
			// i.e., 'categories_get_y' OR 'products_get_y'
			$savedValue = $this->discountAppliesToType;

		} else {
			// BUY X (FieldtypeDiscountsEligibility)
			// i.e., 'categories_buy_x' OR 'products_buy_x'
			$savedValue = $this->discountBuyXEligibilityType;
		}


		$description = $this->_('Any Items From');

		// ------
		// $value = $savedValue === "categories_{$mode}" ? 'specific_categories' : 'specific_products';
		$value = !empty($savedValue) ? $savedValue : "products_{$mode}";

		// -----
		// CHECK IF SHOP HAS PRODUCT CATEGORIES FEATURE (installed)
		if (!empty($this->isProductCategoriesFeatureInstalled)) {
			// USE RADIO INPUTS
			$options = [
				'id' => "pwcommerce_discount_customer_{$mode}_items_from_type",
				'name' => "pwcommerce_discount_customer_{$mode}_items_from_type",
				// 'label' => $this->_('Any Items From'),
				// @note: skipping header label!
				'skipLabel' => Inputfield::skipLabelHeader,
				'description' => $description,
				'collapsed' => Inputfield::collapsedNever,
				'columnWidth' => 50,
				// 'required' => true,
				'wrapClass' => true,
				'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_bottom pwcommerce_override_processwire_inputfield_content_padding_top pwcommerce_discounts_radios_wrapper',
				'radio_options' => $radioOptions,
				'value' => $value,
			];

			$field = $this->pwcommerce->getInputfieldRadios($options);

			// TODO CONFIRM => ALPINE DOES NOT WORK WITH PROCESSWIRE RADIOS?

			// +++++++++
			// TODO DO WE NEED THIS? IF YES, CHANGE THE VALUE!
			// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change
			$field->wrapAttr('data-discount-radio-change-type', 'discount_value_type');
		} else {
			// USE HIDDEN INPUT INSTEAD
			$options = [
				'id' => "pwcommerce_discount_customer_{$mode}_items_from_type",
				'name' => "pwcommerce_discount_customer_{$mode}_items_from_type",
				'value' => $value,
			];

			$field = $this->pwcommerce->getInputfieldHidden($options);
			// TODO JUST FOR SATISFYING ALPINE.JS BUT NOT NEEDED HERE SINCE DON'T USE RADIOS! WITHOUT IT WE GET ERROR (FROM SERVER) about missing values!
			$field->wrapAttr('data-discount-radio-change-type', 'discount_value_type');
		}


		// -------
		return $field;
	}

	private function getMarkupForDiscountCustomerBuysGetsCategoriesTextTagsField($mode = 'buy_x') {
		// TODO: Unused categories?!
		//------------------- pwcommerce_discount_customer_MODE_categories (getInputfieldTextTags)
		// @NOTE: we reuse this field for buys x and gets y
		// =========
		$idName = "pwcommerce_discount_customer_{$mode}_categories";
		$descriptionBuyX = $this->_('Categories eligible for this discount.');
		$descriptionGetY = $this->_('Categories customer must add to their cart to get the discount.');
		if ($mode === 'get_y') {
			$description = $descriptionGetY;
		} else {
			$description = $descriptionBuyX;
		}
		$customHookURL = "/find-pwcommerce_discount_applies_to/";
		$tagsURL = "{$customHookURL}?q={q}&applies_to_type=specific_categories";
		$value = null;
		$setTagsList = [];

		// +++++++++

		if ($mode === 'get_y') {
			// GET Y (FieldtypeDiscountsApplyTo)
			// i.e., 'categories_get_y'
			$savedDiscountAppliesToTypeValue = $this->discountAppliesToType;
			$savedValuesWireArray = $this->discountAppliesTo;

		} else {
			// BUY X (FieldtypeDiscountsEligibility)
			// i.e., 'categories_buy_x'
			$savedDiscountAppliesToTypeValue = $this->discountBuyXEligibilityType;
			$savedValuesWireArray = $this->discountBuyXEligibilityItems;
		}

		// ======
		//for setting saved values if applicable
		// one of 'categories_buy_x'| 'categories_get_y'
		if ($savedDiscountAppliesToTypeValue === "categories_{$mode}") {
			// $pageIDs = $this->discountAppliesTo->implode("|", 'itemID');
			$pageIDs = $savedValuesWireArray->implode("|", 'itemID');
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
			'id' => $idName,
			// TODO: not really needed!
			'name' => $idName,
			// @note: skipping label
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
			// 'show_if' => "pwcommerce_discount_customer_{$mode}_items_from_type=specific_categories",
			'show_if' => "pwcommerce_discount_customer_{$mode}_items_from_type=categories_{$mode}",
			// 'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
			'value' => $value,
			'set_tags_list' => $setTagsList,
		];

		$field = $this->pwcommerce->getInputfieldTextTags($options);
		// allow HTML in description
		// $field->entityEncodeText = false;

		return $field;
	}

	private function getMarkupForDiscountCustomerBuysGetsProductsTextTagsField($mode = 'buy_x') {
		//------------------- pwcommerce_discount_customer_MODE_products (getInputfieldTextTags)
		// @NOTE: we reuse this field for buys x and gets y
		// =========
		$idName = "pwcommerce_discount_customer_{$mode}_products";
		$descriptionBuyX = $this->_('Products eligible for this discount.');
		$descriptionGetY = $this->_('Products customer must add to their cart to get the discount.');
		if ($mode === 'get_y') {
			$description = $descriptionGetY;
		} else {
			$description = $descriptionBuyX;
		}
		$notes = $this->_('To apply the discount to a product and all its variants, you only need to select the parent product. To apply the discount to selected variants, please specify the variants only.');
		$customHookURL = "/find-pwcommerce_discount_applies_to/";
		$tagsURL = "{$customHookURL}?q={q}&applies_to_type=specific_products";
		$value = null;
		$setTagsList = [];


		$wrapperClasses = "pwcommerce_no_outline";

		// +++++++++

		if ($mode === 'get_y') {
			// GET Y (FieldtypeDiscountsApplyTo)
			// i.e., 'products_get_y'
			$savedDiscountAppliesToTypeValue = $this->discountAppliesToType;
			$savedValuesWireArray = $this->discountAppliesTo;
		} else {
			// BUY X (FieldtypeDiscountsEligibility)
			// i.e., 'products_buy_x'
			$savedDiscountAppliesToTypeValue = $this->discountBuyXEligibilityType;
			$savedValuesWireArray = $this->discountBuyXEligibilityItems;
			// -------

			if (empty($this->isProductCategoriesFeatureInstalled)) {
				// remove extra padding top on div.InputfieldContent
				$wrapperClasses .= " pwcommerce_override_processwire_inputfield_content_padding_top";
			}
		}

		// ======
		//for setting saved values if applicable
		// one of 'products_buy_x'| 'products_get_y'
		if ($savedDiscountAppliesToTypeValue === "products_{$mode}") {
			$pageIDs = $savedValuesWireArray->implode("|", 'itemID');
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
		$placeholder = $this->_("Type at least 3 characters to search for products.");





		$options = [
			'id' => $idName,
			// TODO: not really needed!
			'name' => $idName,
			// @note: skipping label
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
			// 'show_if' => "pwcommerce_discount_customer_{$mode}_items_from_type=specific_products",
			// 'show_if' => "pwcommerce_discount_customer_{$mode}_items_from_type=products_{$mode}",
			// 'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => $wrapperClasses,
			'value' => $value,
			'set_tags_list' => $setTagsList,
		];

		// use show-if only if 'categories' feature installed in shop
		// else always show
		if (!empty($this->isProductCategoriesFeatureInstalled)) {
			$options['show_if'] = "pwcommerce_discount_customer_{$mode}_items_from_type=products_{$mode}";
			// 'required' => true,
		}

		$field = $this->pwcommerce->getInputfieldTextTags($options);
		// allow HTML in description
		// $field->entityEncodeText = false;

		return $field;
	}

	// ~~~~~~~~~~~~~~
	############ PROCESS INPUTS ###########
	// @NOTE: PARENT CLASS WILL DO MAIN PROCESSING
	// HERE WE ONLY PROCESS A FEW INPUTS UNIQUE TO BUY X GET Y DISCOUNT

	protected function processDiscountValueType(WireInputData $input) {
		// @note: 'products_get_y' OR 'categories_get_y'
		// @note: identical to values of $discountAppliesTo->itemType in FieldtypeDiscountsApplTo
		// we just get from that input for 'CUSTOMER GETS ANY ITEMS FROM'
		$discountValueType = $this->processDiscountGetYAppliesToItemType($input);
		// -------
		return $discountValueType;
	}

	protected function processDiscountValue(WireInputData $input) {
		// @note: if 'FREE' this is 100
		$discountValue = 100;
		$percentRadio = $this->wire('sanitizer')->fieldName($input->pwcommerce_discount_customer_get_y_discounted_value_type);
		if ($percentRadio === 'percentage') {
			$discountValue = (float) $input->pwcommerce_discount_value;
			// @note: error checking done in parent::processInput. Empties not allowed
		}
		// -------
		return $discountValue;
	}

	protected function processDiscountMinimumRequirementType($input) {
		//@note: pwcommerce_discount_customer_buys_minimum_type: TEXT IN ALLOWED OPTIONS (purchase|quantity)
		$discountMinimumRequirementTypeRaw = $input->pwcommerce_discount_customer_buys_minimum_type;
		$allowedMinimumRequirementTypes = $this->pwcommerce->getAllowedMinimumRequirementTypes();
		$discountMinimumRequirementType = $this->wire('sanitizer')->option($discountMinimumRequirementTypeRaw, $allowedMinimumRequirementTypes);

		// --------
		return $discountMinimumRequirementType;
	}

	protected function processDiscountMinimumRequirementAmount($input, $discountMinimumRequirementType) {
		// @NOTE: MINIMUMS CAN BE ZERO IN OTHER DISCOUNTS EXCEPT FOR BOGO! HENCE THIS OVERRIDE METHOD
		//@note: pwcommerce_customer_buy_x_amount: FLOAT/INT for 'purchase'/'amount' respectively
		$minimumRequirementeErrorAmount = $this->_('Minimum quantity items must be specified');
		$minimumRequirementeErrorPurchase = $this->_('Minimum purchase amount must be specified');
		$discountMinimumRequirementAmount = 0;

		if (in_array($discountMinimumRequirementType, ['purchase', 'quantity'])) {
			if ($discountMinimumRequirementType === 'purchase') {
				$discountMinimumRequirementAmount = (float) $input->pwcommerce_customer_buy_x_amount;
				$minimumRequirementError = $minimumRequirementeErrorPurchase;
			} else {
				$discountMinimumRequirementAmount = (int) $input->pwcommerce_customer_buy_x_amount;
				$minimumRequirementError = $minimumRequirementeErrorAmount;
			}
			// ----
			// @NOTE: ONLY BOGO DEMANDS NON-EMPTY MINIMUM REQUIRED AMOUNT
			if (empty($discountMinimumRequirementAmount)) {
				// empty minimum requirement value FOR BOGO
				$this->extraInputErrors[] = $minimumRequirementError;
			}
		}

		// --------
		return $discountMinimumRequirementAmount;
	}

	protected function processDiscountMetaData(WireInputData $input) {
		$metaData = '';
		$metaDataArray = [];
		$getYDiscountedItemsAmount = (int) $input->pwcommerce_discount_customer_get_y_discounted_items_amount;

		// -----
		if (!empty($getYDiscountedItemsAmount)) {
			$metaDataArray['get_y_discounted_items_amount'] = $getYDiscountedItemsAmount;
		} else {
			// ERROR: GET Y AMOUNT CANNOT BE EMPTY!
			$this->extraInputErrors[] = $this->_('Customer gets quantity of items cannot be zero');
		}
		#######
		if (!empty((int) $input->pwcommerce_discount_set_maximum_usage_per_order_toggle)) {
			// get maximum usage per order
			$maximumUsagePerOrder = (int) $input->pwcommerce_discount_set_maximum_usage_per_order;

			if (!empty($maximumUsagePerOrder)) {
				// set maximum usage per order
				$metaDataArray['maximum_usage_per_order'] = $maximumUsagePerOrder;
			} else {
				// ERROR: MAXIMUM USAGE PER ORDER IS BEING SET BUT EMPTY!
				$this->extraInputErrors[] = $this->_('Maximum usage per order cannot be zero');
			}
		}
		#######
		if (!empty($metaDataArray)) {
			// build JSON string for meta data
			$metaData = json_encode($metaDataArray);
		}

		// -----
		return $metaData;
	}

	protected function processInputForDiscountsApplyTo(WireInputData $input) {
		# @NOTE: THESE ARE THE 'GET Y' ITEMS IN THE BOGO DISCOUNT
		##########################################################
		// TODO:
		// PROCESS INPUTS FOR 'FieldtypePWCommerceDiscountsApplyTo'
		// $discountAppliesTo
		// @NOTE: for products discount, we save one or more records
		// their itemType is one of: 'categories_get_y' OR 'products_get_y'
		// @NOTE: can get this from FieldtypePWCommerceDiscount::discountType as well!
		// INPUTS:
		// pwcommerce_discount_customer_get_y_items_from_type: radio (categories_get_y|products_get_y) AND
		// pwcommerce_discount_customer_get_y_products
		// pwcommerce_discount_customer_get_y_categories
		$sanitizer = $this->wire('sanitizer');
		$discountsApplyTo = NULL;

		// APPLIES TO TYPE (GET Y)
		$discountGetYAppliesToItemType = $this->processDiscountGetYAppliesToItemType($input);
		# ----------

		// ----
		if (!empty($discountGetYAppliesToItemType)) {
			$field = $this->page->getField(PwCommerce::DISCOUNT_APPLIES_TO_FIELD_NAME);

			/** @var WireArray discountAppliesTo */
			$discountsApplyTo = $field->type->getBlankValue($this->page, $field);

			// ------------
			// GET Y ITEMS (CATEGORIES or PRODUCTS)
			// @note: 'products_get_y' OR 'categories_get_y'
			// we save one or more records BUT each share one itemType
			// -------------

			if ($discountGetYAppliesToItemType === 'categories_get_y') {
				// any GET Y items from categories
				$customerGetsFromInputName = "pwcommerce_discount_customer_get_y_categories";
			} else {
				// any GET Y items from products
				$customerGetsFromInputName = "pwcommerce_discount_customer_get_y_products";
			}

			// @note: space separated values from 'InputfieldTextTags'
			$getsAnyItemsFromIDs = explode(" ", $input->{"{$customerGetsFromInputName}"});

			if (!empty($getsAnyItemsFromIDs)) {
				foreach ($getsAnyItemsFromIDs as $getsAnyItemsFromID) {
					if (empty($getsAnyItemsFromID)) {
						continue;
					}
					// we save each record
					/** @var WireData $discountAppliesTo */
					$discountAppliesTo = $field->type->getBlankRecord();
					// $discountAppliesTo->itemID = (int) $getsAnyItemsFromID;
					// @note: existing selectize items will be prefixed with '_', e.g. '_1234'
					// we remove the prefix
					$discountAppliesTo->itemID = (int) str_replace('_', '', $getsAnyItemsFromID);
					$discountAppliesTo->itemType = $discountGetYAppliesToItemType;
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

	private function processDiscountGetYAppliesToItemType($input) {
		// @note: 'products_get_y' OR 'categories_get_y'
		$discountGetYAppliesToItemTypeRaw = $input->pwcommerce_discount_customer_get_y_items_from_type;
		$allowedAppliesToItemTypes = $this->pwcommerce->getAllowedAppliesToItemTypes();
		$discountGetYAppliesToItemType = $this->wire('sanitizer')->option($discountGetYAppliesToItemTypeRaw, $allowedAppliesToItemTypes);

		// ----
		return $discountGetYAppliesToItemType;
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
		$errorString = $this->_('Missing items for customers to get discount on');
		// ========
		// -----
		return $errorString;
	}

	protected function processInputForDiscountsEligibility(WireInputData $input) {
		# @NOTE: THESE ARE BOTH THE 'BUY X' ITEMS AND THE CUSTOMER ELIGIBILITY IN THE BOGO DISCOUNT
		# THE 'BUY X' will store either of 'categories_buy_x' OR 'products_buy_x'
		# THE CUSTOMER ELIGIBILITY will store one of 'all_customers', 'customer_groups', OR 'specific_customers'
		########################################################################################################
		// TODO:
		// PROCESS INPUTS FOR 'FieldtypePWCommerceDiscountsEligibility'
		// $discountEligibility
		// INPUTS:
		/***
		 * ~ BUY X ~
		 * pwcommerce_discount_buy_x_categories
		 * pwcommerce_discount_buy_x_products
		 * ~ CUSTOMERS ~
		 * pwcommerce_discount_customer_eligibility: radio (all_customers|customer_groups|specific_customers)
		 * pwcommerce_discount_customer_eligibility_customer_groups
		 * pwcommerce_discount_customer_eligibility_specific_customers
		 */

		$sanitizer = $this->wire('sanitizer');
		$field = $this->page->getField(PwCommerce::DISCOUNT_ELIGIBILITY_FIELD_NAME);

		/** @var WireArray $discountsEligibility */
		$discountsEligibility = $field->type->getBlankValue($this->page, $field);
		$allowedEligibilityItemTypes = $this->pwcommerce->getAllowedEligibilityItemTypes();
		// we use these two to stop save if either is empty after processing
		$buysAnyItemsFromCount = 0;
		$customerEligibilityItemsCount = 0;

		###########

		// BUY X ELIGIBILITY TYPE
		$errorStringMissingBuyXItems = $this->_('Missing items for customers to buy from');
		// @note: 'products_buy_x' OR 'categories_buy_x'
		$discountBuyXEligibilityItemTypeRaw = $input->pwcommerce_discount_customer_buy_x_items_from_type;
		$discountBuyXEligibilityItemType = $sanitizer->option($discountBuyXEligibilityItemTypeRaw, $allowedEligibilityItemTypes);

		# ----------

		if (!empty($discountBuyXEligibilityItemType)) {

			if ($discountBuyXEligibilityItemType === 'categories_buy_x') {
				// any BUY X items from categories
				$customerBuysFromInputName = "pwcommerce_discount_customer_buy_x_categories";
			} else {
				// any BUY X items from products
				$customerBuysFromInputName = "pwcommerce_discount_customer_buy_x_products";
			}

			// @note: space separated values from 'InputfieldTextTags'
			$buysAnyItemsFromIDs = explode(" ", $input->{"{$customerBuysFromInputName}"});

			if (!empty($buysAnyItemsFromIDs)) {

				foreach ($buysAnyItemsFromIDs as $buysAnyItemsFromID) {
					if (empty($buysAnyItemsFromID)) {

						continue;
					}
					// we save each record
					/** @var WireData $discountEligibility */
					$discountEligibility = $field->type->getBlankRecord();
					// $discountEligibility->itemID = (int) $buysAnyItemsFromID;
					// @note: existing selectize items will be prefixed with '_', e.g. '_1234'
					// we remove the prefix
					$discountEligibility->itemID = (int) str_replace('_', '', $buysAnyItemsFromID);
					$discountEligibility->itemType = $discountBuyXEligibilityItemType;
					// ---------
					// add to WireArray
					$discountsEligibility->add($discountEligibility);
					// ------
					// track 'buy any items from count'
					$buysAnyItemsFromCount++;
				}
				// CHECK IF ALL BUY ITEMS IDS WERE EMPTY
				if (empty($discountsEligibility->count())) {
					// NOTHING WAS ADDED; NO BUY X ITEMS
					// ERROR: NO BUY ANY ITEMS FROM SPECIFIED
					// add extra error to $extraInputErrors
					$this->extraInputErrors[] = $errorStringMissingBuyXItems;
					// TODO do we also nullify WireArray? or count will deal with that? not really; count can be valid fro customer eligibility but not for buy x items! think!

				}
			} else {
				// ERROR: NO BUY ANY ITEMS FROM SPECIFIED
				// add extra error to $extraInputErrors
				$this->extraInputErrors[] = $errorStringMissingBuyXItems;
				// TODO do we also nullify WireArray? or count will deal with that? not really; count can be valid fro customer eligibility but not for buy x items! think!

			}

		} else {
			// ERROR: NO TYPE SENT FOR SOME REASON
			// add extra error to $extraInputErrors
			$this->extraInputErrors[] = $this->_('Missing items type for customers to buy from');

		}

		# **************************

		// CUSTOMER ELIGIBILITY TYPE
		$discountCustomerEligibilityItemTypeRaw = $input->pwcommerce_discount_customer_eligibility;
		$discountCustomerEligibilityItemType = $sanitizer->option($discountCustomerEligibilityItemTypeRaw, $allowedEligibilityItemTypes);

		# ----------

		if (!empty($discountCustomerEligibilityItemType)) {

			// ----
			if ($discountCustomerEligibilityItemType === 'all_customers') {
				// ALL CUSTOMERS
				// we save only one record
				/** @var WireData $discountEligibility */
				$discountEligibility = $field->type->getBlankRecord();
				$discountEligibility->itemID = 0;
				$discountEligibility->itemType = $discountCustomerEligibilityItemType; // 'all_customers'
				// ---------
				// add to WireArray
				$discountsEligibility->add($discountEligibility);
				// ------
				// track 'customer eligibility items count'
				$customerEligibilityItemsCount++;

			} elseif ($discountCustomerEligibilityItemType === 'customer_groups') {
				// CUSTOMER GROUPS
				// @note: space separated values from 'InputfieldTextTags'
				$customerGroupsIDs = explode(" ", $input->pwcommerce_discount_customer_eligibility_customer_groups);

				if (!empty($customerGroupsIDs)) {
					foreach ($customerGroupsIDs as $customerGroupID) {
						if (empty($customerGroupID)) {
							continue;
						}
						// we save each record
						/** @var WireData $discountEligibility */
						$discountEligibility = $field->type->getBlankRecord();
						// $discountEligibility->itemID = (int) $customerGroupID;
						// @note: existing selectize items will be prefixed with '_', e.g. '_1234'
						// we remove the prefix
						$discountEligibility->itemID = (int) str_replace('_', '', $customerGroupID);
						$discountEligibility->itemType = $discountCustomerEligibilityItemType; // 'customer_groups'
						// ---------
						// add to WireArray
						$discountsEligibility->add($discountEligibility);
						// ------
						// track 'customer eligibility items count'
						$customerEligibilityItemsCount++;
					}
				}
			} elseif ($discountCustomerEligibilityItemType === 'specific_customers') {
				// SPECIFIC CUSTOMERS
				// TODO CONSIDER REFACTOR: SIMILAR TO CUSTOMER GROUPS!
				// @note: space separated values from 'InputfieldTextTags'
				$customersIDs = explode(" ", $input->pwcommerce_discount_customer_eligibility_specific_customers);

				if (!empty($customersIDs)) {
					foreach ($customersIDs as $customerID) {
						if (empty($customerID)) {
							continue;
						}
						// we save each record
						/** @var WireData $discountEligibility */
						$discountEligibility = $field->type->getBlankRecord();
						// $discountEligibility->itemID = (int) $customerID;
						// @note: existing selectize items will be prefixed with '_', e.g. '_1234'
						// we remove the prefix
						$discountEligibility->itemID = (int) str_replace('_', '', $customerID);
						$discountEligibility->itemType = $discountCustomerEligibilityItemType; // 'specific_customers'
						// ---------
						// add to WireArray
						$discountsEligibility->add($discountEligibility);
						// ------
						// track 'customer eligibility items count'
						$customerEligibilityItemsCount++;
					}
				}
			}

			// ========
			// TODO: DELETE WHEN DONE: WE NOW SAVE IN processInput() if no errors overall
			// SAVE PAGE field 'pwcommerce_discounts_eligibility'
			// @note: we need to save always since all previous values could have been deleted but no new ones supplied
			// hence, need to clear old values (TODO?)
			// $this->page->setAndSave(PwCommerce::DISCOUNT_ELIGIBILITY_FIELD_NAME, $discountsEligibility);
			// // ++++++++
			// // if either of  'buysAnyItemsFromCount' OR 'customerEligibilityItemsCount' or '' are empty, we have an error
			// // it means nothing was added to the $discountsEligibility WireArray
			// // NULLIFY the WireArray
			// // if (empty($discountsEligibility->count())) {
			// if ((empty($buysAnyItemsFromCount)) || (empty($customerEligibilityItemsCount))) {
			// 	$discountsEligibility = NULL;
			//
			// }

		}

		// ++++++++
		// if either of  'buysAnyItemsFromCount' OR 'customerEligibilityItemsCount' or '' are empty, we have an error
		// it means nothing was added to the $discountsEligibility WireArray
		// NULLIFY the WireArray
		// if (empty($discountsEligibility->count())) {
		if ((empty($buysAnyItemsFromCount)) || (empty($customerEligibilityItemsCount))) {
			$discountsEligibility = NULL;

		}

		// ------
		return $discountsEligibility;
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