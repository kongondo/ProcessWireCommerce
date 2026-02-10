<?php

namespace ProcessWire;

/**
 * PWCommerce: InputfieldPWCommerceDiscountRenderOrderDiscount
 *
 * Inputfield for FieldtypePWCommerceDiscount, the field that stores and outputs values of a PWCommerce discount.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceDiscountRenderOrderDiscount for PWCommerce
 * Copyright (C) 2023 by Francis Otieno
 * MIT License
 *
 */



class InputfieldPWCommerceDiscountRenderOrderDiscount extends WireData
{



	protected $page;
	protected $discount;
	protected $discountType;
	// field: pwcommerce_discounts_apply_to (FieldtypePWCommerceDiscountsApplyTo)
	protected $discountAppliesTo;
	// field: pwcommerce_discounts_eligibility (FieldtypePWCommerceDiscountsEligibility)
	// @note:for order discount, elibility only applies to customers
	protected $discountCustomerEligibility;
	protected $discountCustomerEligibilityType;
	protected $field;
	// -----
	protected $shopCurrencySymbolString = "";
	// ----
	protected $xstoreDiscount; // the alpinejs store used by this inputfield.
	protected $xstore; // the full prefix to the alpine store used by inputfield
	// ++++++++++
	protected $inputErrors = [];
	// -------
	protected $isCustomersFeatureInstalled;
	protected $isCustomerGroupsFeatureInstalled;
	protected $isProductCategoriesFeatureInstalled;


	/**
	 *   construct.
	 *
	 * @param Page $page
	 * @param mixed $field
	 * @return mixed
	 */
	public function __construct($page, $field) {
		parent::__construct();
		// TODO????
		$this->page = $page;
		$this->field = $field;
		// --------
		/** @var WireData $this->discount */
		$this->discount = $this->page->get(PwCommerce::DISCOUNT_FIELD_NAME);
		/** @var WireArray $this->discountAppliesTo */
		$this->discountAppliesTo = $this->page->get(PwCommerce::DISCOUNT_APPLIES_TO_FIELD_NAME);
		/** @var WireArray $this->discountCustomerEligibility */
		$this->discountCustomerEligibility = $this->page->get(PwCommerce::DISCOUNT_ELIGIBILITY_FIELD_NAME);
		/** @var string $this->discount */
		$this->discountType = $this->discount->discountType;

		// ----------
		$shopCurrencySymbolString = $this->pwcommerce->renderShopCurrencySymbolString();
		if (strlen($shopCurrencySymbolString)) {
			$this->shopCurrencySymbolString = " " . $shopCurrencySymbolString;
		}
		// --------
		// SET DISCOUNT ELIGIBILITY TYPE
		// one of 'all_customers'| 'customer_groups' | 'specific_customers'
		$this->setDiscountCustomerEligibilityType();
		// ==================
		$this->xstoreDiscount = 'InputfieldPWCommerceDiscountStore';
		// i.e., '$store.InputfieldPWCommerceDiscountStore'
		$this->xstore = "\$store.{$this->xstoreDiscount}";
		// i.e., '$store.InputfieldPWCommerceDiscountStore'
		$this->ajaxPostURL = $this->wire('config')->urls->admin . PwCommerce::PWCOMMERCE_SHOP_PAGE_IN_ADMIN_NAME . '/ajax/';
		// --------
		// +++++++++++++++
		// SET CHECKS FOR OPTIONAL FEATURES INSTALLATION
		// these are for 'customers', 'customer groups' and 'product categories'
		// will determine if some markup is rendered in GUI or not.
		$this->setOptionalFeaturesChecks();

		//
	}

	/**
	 * Set Discount Customer Eligibility Type.
	 *
	 * @return mixed
	 */
	protected function setDiscountCustomerEligibilityType() {
		$firstItemDiscountEligibility = $this->discountCustomerEligibility->first();
		if (!empty($firstItemDiscountEligibility)) {
			$this->discountCustomerEligibilityType = $firstItemDiscountEligibility->itemType;
		}

	}

	/**
	 * Set Optional Features Checks.
	 *
	 * @return mixed
	 */
	protected function setOptionalFeaturesChecks() {
		$customersFeature = 'customers';
		$customerGroupsFeature = 'customer_groups';
		$productCategories = 'product_categories';
		// -------
		$this->isCustomersFeatureInstalled = !empty($this->pwcommerce->isOptionalFeatureInstalled($customersFeature));
		$this->isCustomerGroupsFeatureInstalled = !empty($this->pwcommerce->isOptionalFeatureInstalled($customerGroupsFeature));
		$this->isProductCategoriesFeatureInstalled = !empty($this->pwcommerce->isOptionalFeatureInstalled($productCategories));

	}

	/**
	 * Render the entire input area for order discount
	 *
	 * @return mixed
	 */
	public function ___render() {
		$xinit = $this->getInitValuesForAlpineJS();
		$out =
			"<div id='pwcommerce_order_discount_wrapper' {$xinit}>" .
			// TODO ADD 2*COLUMN GRID HERE
			$this->buildForm() .
			"</div>";
		return $out;
	}

	/**
	 * Build Form.
	 *
	 * @return mixed
	 */
	protected function buildForm() {
		//
		// header
		$discountTypeHeader = $this->getDiscountsFormHeader();
		// wrapper
		/** @var InputfieldWrapper $wrapper */
		$wrapper = $this->getDiscountsFormWrapper();
		//----------------------
		$out = $discountTypeHeader . $wrapper->render();
		return $out;
	}

	/**
	 * Get Discounts Form Header.
	 *
	 * @return mixed
	 */
	protected function getDiscountsFormHeader() {
		$discountTypeHeader =
			// discount type header
			// "<h3>" . $this->_('Amount off order') . "</h3>";
			// "<h4>" . $this->_('Amount off order') . "</h4>";
			"<h4>" . $this->_('Order Discount (amount off order)') . "</h4>";
		// ------
		return $discountTypeHeader;
	}

	/**
	 * Get Discounts Form Wrapper.
	 *
	 * @return mixed
	 */
	protected function getDiscountsFormWrapper() {
		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		// METHOD
		$wrapper = $this->renderDiscountMethod($wrapper);
		// VALUE
		$wrapper = $this->renderDiscountValue($wrapper);
		// MINIMUM REQUIREMENT
		$wrapper = $this->renderDiscountMinimumRequirement($wrapper);
		// CUSTOMER ELIGIBILITY
		$wrapper = $this->renderDiscountCustomerEligibility($wrapper);
		// // MAXIMUM DISCOUNT USES
		$wrapper = $this->renderDiscountMaximumUses($wrapper);
		// ACTIVE DATES
		$wrapper = $this->renderDiscountActiveDates($wrapper);

		// -- OTHER --
		// TODO DELETE IF NOT IN USE!
		// $wrapper = $this->renderDiscountHiddenInputs($wrapper);

		// --------
		return $wrapper;
	}

	/**
	 * Get Init Values For Alpine J S.
	 *
	 * @return mixed
	 */
	protected function getInitValuesForAlpineJS() {
		$radioValues = $this->getInitValuesArrayForAlpineJS();
		$radioValuesJSON = json_encode($radioValues);

		$out = "x-init='initDiscountRadioElements({$radioValuesJSON})'";
		// -------
		return $out;
	}

	/**
	 * Get Init Values Array For Alpine J S.
	 *
	 * @return mixed
	 */
	protected function getInitValuesArrayForAlpineJS() {
		$radioValues = [
			'discount_method_type_selected' => $this->getValueForDiscountMethod(),
			'discount_value_type_selected' => $this->getValueForDiscountType(),
			'discount_minimum_requirement_selected' => $this->getValueForDiscountMinimumRequirement(),
		];

		// -------
		return $radioValues;
	}

	### METHOD ###
	/**
	 * Render Discount Method.
	 *
	 * @param mixed $wrapper
	 * @return string|mixed
	 */
	protected function renderDiscountMethod($wrapper) {
		// radio to select discount method
		$field = $this->getMarkupForDiscountMethodRadioField();
		$wrapper->add($field);
		// text input for custom discount code
		$field = $this->getMarkupForDiscountMethodCodeTextField();
		$wrapper->add($field);
		// button to generate discount code (for text input for custom code)
		$field = $this->getMarkupForDiscountMethodGenerateCodeButton();
		$wrapper->add($field);
		// text input for automatic discount
		$field = $this->getMarkupForDiscountMethodAutomaticTextField();
		$wrapper->add($field);
		// divider markup for sections that need it
		$field = $this->getMarkupForDiscountSectionsDividerMarkupField("discount_method");
		$wrapper->add($field);
		// -----
		return $wrapper;
	}

	/**
	 * Get Value For Discount Method.
	 *
	 * @return mixed
	 */
	private function getValueForDiscountMethod() {
		$value = $this->discount->isAutomaticDiscount ? 'automatic_discount' : 'discount_code';
		//------
		return $value;
	}

	/**
	 * Get Markup For Discount Method Radio Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountMethodRadioField() {
		//------------------- pwcommerce_discount_method (getInputfieldRadios)

		$radioOptions = [
			'discount_code' => __('Discount code'),
			'automatic_discount' => __('Automatic discount'),
		];

		$value = $this->getValueForDiscountMethod();

		$options = [
			'id' => "pwcommerce_discount_method",
			'name' => 'pwcommerce_discount_method',
			'label' => $this->_('Method'),
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 33,
			// 'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_discounts_radios_wrapper',
			'radio_options' => $radioOptions,
			'value' => $value,
		];

		$field = $this->pwcommerce->getInputfieldRadios($options);

		// +++++++++
		// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change
		$field->wrapAttr('data-discount-radio-change-type', 'discount_method_type');

		// -------
		return $field;
	}

	/**
	 * Get Markup For Discount Method Code Text Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountMethodCodeTextField() {
		//------------------- pwcommerce_discount_method_code (getInputfieldText)
		$options = [
			'id' => "pwcommerce_discount_method_code",
			'name' => "pwcommerce_discount_method_code",
			'label' => $this->_('Discount Code'),
			'notes' => $this->_('Customers must enter this code at checkout.'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 75,
			// 'size' => 50,
			'show_if' => "pwcommerce_discount_method=discount_code",
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
			'value' => $this->discount->code
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		// $field->appendMarkup = "<hr>";
		// ----
		return $field;
	}

	/**
	 * Get Markup For Discount Method Automatic Text Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountMethodAutomaticTextField() {
		//------------------- pwcommerce_discount_method_automatic (getInputfieldText)
		$options = [
			'id' => "pwcommerce_discount_method_automatic",
			'name' => "pwcommerce_discount_method_automatic",
			'label' => $this->_('Title'),
			'notes' => $this->_('Customers will see this in their cart and at checkout.'),
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 50,
			'show_if' => "pwcommerce_discount_method=automatic_discount",
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
			'value' => $this->discount->code
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		// $field->appendMarkup = "<hr>";
		// ----
		return $field;
	}

	/**
	 * Get Markup For Discount Method Generate Code Button.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountMethodGenerateCodeButton() {
		//------------------- pwcommerce_discount_method_code_generate (getInputfieldButton)
		$pageID = $this->page->id;
		$adminEditURL = $this->wire('config')->urls->admin . "page/edit/";
		$ajaxgGetURL = "{$adminEditURL}?id={$pageID}&field=pwcommerce_discount";
		// ---
		$options = [
			'id' => "pwcommerce_discount_method_code_generate",
			'name' => "pwcommerce_discount_method_code_generate",
			// 'type' => 'button',
			'label' => $this->_('Generate'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 25,
			'show_if' => "pwcommerce_discount_method=discount_code",
			'small' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'secondary' => true,
			// 'icon' => 'paper-plane'
		];

		$field = $this->pwcommerce->getInputfieldButton($options);
		// ++++++++
		// spinner
		$extraMarkup = "<span id='pwcommerce_discount_spinner_indicator' class='htmx-indicator'>" .
			"<i class='fa fa-fw fa-spin fa-spinner'></i>" .
			$this->_("Please wait") .
			"&#8230;" .
			"</span>";
		$field->appendMarkup = $extraMarkup;
		$field->attr([
			// HTMX
			'hx-get' => $ajaxgGetURL,
			'hx-indicator' => '#pwcommerce_discount_spinner_indicator',
			// 'hx-target' => 'li#wrap_pwcommerce_discount_method_code',
			'hx-target' => '#pwcommerce_discount_method_code',
			'hx-swap' => 'outerHTML',
		]);
		// ---
		return $field;
	}

	### VALUE ###
	/**
	 * Render Discount Value.
	 *
	 * @param mixed $wrapper
	 * @return string|mixed
	 */
	protected function renderDiscountValue($wrapper) {
		// radio to select discount value type
		$field = $this->getMarkupForDiscountValueTypeRadioField();
		$wrapper->add($field);
		// TODO USE ONE TEXT FIELD + MODEL DESCRIPTION WITH ALPINE - maybe spine + use allow html in desc
		// text input for percentage discount value
		// $field = $this->getMarkupForDiscountValuePercentageTextField();
		// $wrapper->add($field);
		// text input for fixed discount value
		// $field = $this->getMarkupForDiscountValueFixedTextField();
		// =========
		// text input for BOTH percentage & fixed discount value
		// @NOTE: WE x-show the respective descriptions!
		$field = $this->getMarkupForDiscountValueTextField();
		$wrapper->add($field);
		// divider markup for sections that need it
		$field = $this->getMarkupForDiscountSectionsDividerMarkupField("discount_value");
		$wrapper->add($field);
		// -----
		return $wrapper;
	}

	/**
	 * Get Value For Discount Type.
	 *
	 * @return mixed
	 */
	protected function getValueForDiscountType() {
		$value = !empty($this->discount->discountType) ? $this->discount->discountType : 'whole_order_percentage';
		//------
		return $value;
	}

	/**
	 * Get Radio Options For Discount Value Type.
	 *
	 * @return mixed
	 */
	protected function getRadioOptionsForDiscountValueType() {
		$radioOptions = [
			'whole_order_percentage' => __('Percentage'),
			'whole_order_fixed' => __('Fixed'),
		];
		// ----
		return $radioOptions;
	}

	/**
	 * Get Markup For Discount Value Type Radio Field.
	 *
	 * @return mixed
	 */
	protected function getMarkupForDiscountValueTypeRadioField() {
		//------------------- pwcommerce_discount_value_type (getInputfieldRadios)

		$radioOptions = $this->getRadioOptionsForDiscountValueType();

		// ------
		$value = $this->getValueForDiscountType();

		$options = [
			'id' => "pwcommerce_discount_value_type",
			'name' => 'pwcommerce_discount_value_type',
			'label' => $this->_('Value'),
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
		// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change
		$field->wrapAttr('data-discount-radio-change-type', 'discount_value_type');

		// -------
		return $field;
	}

	/**
	 * Get Markup For Discount Value Text Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountValueTextField() {
		//------------------- pwcommerce_discount_value (getInputfieldText)
		// x-show description for percentage value
		$descriptionTextPercentage = $this->_('Percentage (%) off');
		// $description = "<span x-show='{$this->xstore}.discount_value_type_selected==`whole_order_percentage`'>" . $descriptionTextPercentage . "</span>";
		// @note: we are catering for order, product and categories percentage types
		$percentageTypes = [
			// GENERIC
			// '`percentage`',
			// // WHOLE ORDER
			// '`whole_order_percentage`',
			// // PRODUCTS
			// '`products_percentage`',
			// // CATEGORIES
			// '`categories_percentage`'
			// GENERIC
			'percentage',
			// WHOLE ORDER
			'whole_order_percentage',
			// PRODUCTS
			'products_percentage',
			// CATEGORIES
			'categories_percentage'
		];
		$percentageTypesJSON = json_encode($percentageTypes);
		$percentageXShowString = "{$percentageTypesJSON}.includes({$this->xstore}.discount_value_type_selected)";
		// $percentageTypesString = implode("|", $percentageTypes);
		// $description = "<span x-show='{$this->xstore}.discount_value_type_selected=={$percentageTypesString}'>" . $descriptionTextPercentage . "</span>";
		$description = "<span x-show='{$percentageXShowString}'>" . $descriptionTextPercentage . "</span>";
		// x-show description for fixed value
		$descriptionTextFixed = sprintf(__("Fixed amount %s off"), $this->shopCurrencySymbolString);
		// @note: we are catering for order, product and categories fixed types
		$fixedTypes = [
			// // GENERIC
			// '`fixed`',
			// // WHOLE ORDER
			// '`whole_order_fixed`',
			// // PRODUCTS
			// '`products_fixed_per_order`',
			// '`products_fixed_per_item`',
			// // CATEGORIES
			// '`categories_fixed_per_order`',
			// '`categories_fixed_per_item`',
			'fixed',
			// WHOLE ORDER
			'whole_order_fixed',
			// PRODUCTS
			'products_fixed_per_order',
			'products_fixed_per_item',
			// CATEGORIES
			'categories_fixed_per_order',
			'categories_fixed_per_item',
		];
		// $fixedTypesString = implode("|", $fixedTypes);
		// $description .= "<span x-show='{$this->xstore}.discount_value_type_selected==`whole_order_fixed`'>" . $descriptionTextFixed . "</span>";
		$fixedTypesJSON = json_encode($fixedTypes);
		$fixedXShowString = "{$fixedTypesJSON}.includes({$this->xstore}.discount_value_type_selected)";
		// $description .= "<span x-show='{$this->xstore}.discount_value_type_selected=={$fixedTypesString}'>" . $descriptionTextFixed . "</span>";
		$description .= "<span x-show='{$fixedXShowString}'>" . $descriptionTextFixed . "</span>";

		$options = [
			'id' => "pwcommerce_discount_value",
			'name' => "pwcommerce_discount_value",
			'type' => 'number',
			// 'step' => '0.1',
			'step' => '0.01',
			// TODO 0.01?
			'min' => 0,
			'label' => $this->_('Discount Value'),
			// @note: skipping header label!
			'skipLabel' => Inputfield::skipLabelHeader,
			// 'description' => $this->_('Percentage (%) off.'),
			// @note: x-show by alpine above!
			'description' => $description,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			'size' => 30,
			// 'show_if' => "pwcommerce_discount_value_type=whole_order_percentage",
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top pwcommerce_override_processwire_inputfield_content_padding_bottom',
			'value' => $this->discount->discountValue
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		$field->attr([
			// ==========
			// max percentage value to 100
			// @note: here we just reuse the value of '$percentageXShowString' abov!
			'x-bind:max' => "{$percentageXShowString} ? 100 : ``",
			// ------
			'x-init' => "setStoreValue(`discount_value`,`{$this->discount->discountValue}`)",
			'x-model' => "{$this->xstore}.discount_value",
			// 'x-on:change' => 'handleDiscountValueChange',
			'x-on:change.debounce' => 'handleDiscountValueChange',

		]);
		$field->entityEncodeText = false;
		// $field->appendMarkup = "<hr>";
		// ----
		return $field;
	}

	### MINIMUM REQUIREMENT ###
	/**
	 * Render Discount Minimum Requirement.
	 *
	 * @param mixed $wrapper
	 * @return string|mixed
	 */
	protected function renderDiscountMinimumRequirement($wrapper) {
		// radio to select discount minimum requirement type
		$field = $this->getMarkupForDiscountMinimumRequirementTypeRadioField();
		$wrapper->add($field);
		// TODO USE ONE TEXT FIELD + MODEL DESCRIPTION WITH ALPINE + MODEL STEP WITH ALPINE
		// text input for minimum requirement purchase amount
		// $field = $this->getMarkupForDiscountMinimumRequirementPurchaseAmountTextField();
		// $wrapper->add($field);
		// // text input for minimum requirement items amount
		// $field = $this->getMarkupForDiscountMinimumRequirementQuantityItemsTextField();
		// $wrapper->add($field);
		// =========
		// text input for BOTH purchase amount & items count minimum requirement
		// @NOTE: WE x-show the respective descriptions!
		$field = $this->getMarkupForDiscountMinimumRequirementTextField();
		$wrapper->add($field);
		// divider markup for sections that need it
		$field = $this->getMarkupForDiscountSectionsDividerMarkupField("minimum_requirement");
		$wrapper->add($field);
		// -----
		return $wrapper;
	}

	/**
	 * Get Value For Discount Minimum Requirement.
	 *
	 * @return mixed
	 */
	private function getValueForDiscountMinimumRequirement() {
		$discountMinimumRequirementType = $this->discount->discountMinimumRequirementType;
		$value = !empty($discountMinimumRequirementType) ? $discountMinimumRequirementType : 'none';
		//------
		return $value;
	}

	/**
	 * Get Markup For Discount Minimum Requirement Type Radio Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountMinimumRequirementTypeRadioField() {
		//------------------- pwcommerce_discount_minimum_requirement_type (getInputfieldRadios)

		// TODO: ONLY WORKS FOR THE LABEL! WE NEED TO USE JS EVENT TO HIDE THE INPUT COMPLETELY!
		// x-show label for 'no minimum requirements' radio option
		// $radioTextNoMininumRequirements = $this->_('No minimum requirements');
		// $noneRadio = "<span x-show='{$this->xstore}.discount_method_type_selected==`discount_code`'>" . $radioTextNoMininumRequirements . "</span>";
		$purchaseRadio = sprintf(__("Minimum purchase amount %s"), $this->shopCurrencySymbolString);
		$radioOptions = [
			// TODO NEED TO ADD 'No minimum requirements'  but it is only applicable to non-automatic discount!
			// @BUT CAN ALSO DO THE SAVED ONCE THEN CANNOT CHANGE? HOW?
			'none' => __('No minimum requirements'),
			// 'none' => $noneRadio,
			'purchase' => $purchaseRadio,
			'quantity' => "<span>" . __('Minimum quantity of items') . "</span>",
		];

		$value = $this->getValueForDiscountMinimumRequirement();

		$options = [
			'id' => "pwcommerce_discount_minimum_requirement_type",
			'name' => 'pwcommerce_discount_minimum_requirement_type',
			'label' => $this->_('Minimum Purchase Requirements'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			// 'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_bottom pwcommerce_discounts_radios_wrapper',
			'radio_options' => $radioOptions,
			'value' => $value,
		];

		$field = $this->pwcommerce->getInputfieldRadios($options);
		$field->entityEncodeText = false;

		// TODO CONFIRM => ALPINE DOES NOT WORK WITH PROCESSWIRE RADIOS?

		// +++++++++
		// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change
		$field->wrapAttr('data-discount-radio-change-type', 'discount_minimum_requirement_type');

		// -------
		return $field;
	}

	/**
	 * Get Markup For Discount Minimum Requirement Text Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountMinimumRequirementTextField() {
		//------------------- pwcommerce_discount_minimum_requirement (getInputfieldText)
		// x-show description for purchase amount value
		$descriptionTextPurchaseAmount = sprintf(__("Amount %s"), $this->shopCurrencySymbolString);
		$description = "<span x-show='{$this->xstore}.discount_minimum_requirement_selected==`purchase`'>" . $descriptionTextPurchaseAmount . "</span>";
		// x-show description for items count value
		$descriptionTextItemsCount = $this->_('Quantity of items');
		$description .= "<span x-show='{$this->xstore}.discount_minimum_requirement_selected==`quantity`'>" . $descriptionTextItemsCount . "</span>";

		$options = [
			'id' => "pwcommerce_discount_minimum_requirement",
			'name' => "pwcommerce_discount_minimum_requirement",
			'type' => 'number',
			// 'step' => '0.1',
			'min' => 0,
			'label' => $this->_('Minimum Purchase Amount'),
			'skipLabel' => Inputfield::skipLabelHeader,
			'description' => $description,
			// 'notes' => $this->_('Applies to all products.'),
			// @note: only WHOLE ORDER  and FREE SHIPPING discounts have this apply to all products
			// PRODUCTS, CATEGORIES AND BOGO HAVE THESE AS APPLYING TO SPECIFIC ITEMS
			'notes' => $this->getNoteForDiscountMinimumRequirementTextField(),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			'size' => 30,
			'show_if' => "pwcommerce_discount_minimum_requirement_type!=none",
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top pwcommerce_override_processwire_inputfield_content_padding_bottom',
			'value' => $this->discount->discountMinimumRequirementAmount
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		$field->attr([
			// ==========
			// if 'quantity' step is '1' else for purchase, it is '0.01'
			'x-bind:step' => "{$this->xstore}.discount_minimum_requirement_selected==`quantity` ? 1 : `0.01`",
		]);
		$field->entityEncodeText = false;
		// ----
		return $field;
	}

	/**
	 * Get Note For Discount Minimum Requirement Text Field.
	 *
	 * @return mixed
	 */
	protected function getNoteForDiscountMinimumRequirementTextField() {
		$notes = $this->_('Applies to all products.');
		return $notes;
	}

	### CUSTOMER ELIGIBILITY ###
	/**
	 * Render Discount Customer Eligibility.
	 *
	 * @param mixed $wrapper
	 * @return string|mixed
	 */
	private function renderDiscountCustomerEligibility($wrapper) {
		// radio to select customer eligibility type
		$field = $this->getMarkupForDiscountCustomerEligibilityRadioField();
		$wrapper->add($field);

		################

		// CHECK IF CUSTOMER AND CUSTOMER GROUPS FEATURE INSTALLED
		// -----
		// CHECK IF SHOP HAS CUSTOMERS AND CUSTOMER GROUPS FEATURE installed
		if (!empty($this->isCustomerGroupsFeatureInstalled)) {
			// customer groups (and by extension, customers, in use)
			// selectize text input for searching specific customer groups // TODO NEED TO MAYBE SEARCH PREFIXED ROLES? E.G. pwcommerce-customer-wholesale?
			$field = $this->getMarkupForDiscountCustomerEligibilityCustomerGroupsTextTagsField();
			$wrapper->add($field);
			// selectize text input for searching specific customers
			$field = $this->getMarkupForDiscountCustomerEligibilitySpecificCustomersTextTagsField();
			$wrapper->add($field);
		} else if (!empty($this->isCustomersFeatureInstalled)) {
			// only customer groups in use
			// selectize text input for searching specific customers
			$field = $this->getMarkupForDiscountCustomerEligibilitySpecificCustomersTextTagsField();
			$wrapper->add($field);
		}

		// +++++++++++++

		// ~~~~~~~~~~~~~~~~~~
		// divider markup for sections that need it
		$field = $this->getMarkupForDiscountSectionsDividerMarkupField("customer_eligibility");
		$wrapper->add($field);
		// -----
		return $wrapper;
	}

	/**
	 * Get Markup For Discount Customer Eligibility Radio Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountCustomerEligibilityRadioField() {
		//------------------- pwcommerce_discount_customer_eligibility (getInputfieldRadios)

		$customerElibilityType = $this->discountCustomerEligibilityType;

		$value = !empty($customerElibilityType) ? $customerElibilityType : 'all_customers';
		$radioOptions = [
			'all_customers' => __('All customers'),
			'customer_groups' => __('Specific customer groups'),
			'specific_customers' => __('Specific customers'),
		];

		// -----
		// CHECK IF SHOP HAS CUSTOMERS AND CUSTOMER GROUPS FEATURE are installed
		$isNotNeedRadioInput = false;
		if (empty($this->isCustomersFeatureInstalled)) {
			// customers (and by extension, customer groups, not in use)
			unset($radioOptions['specific_customers']);
			unset($radioOptions['customer_groups']);
			$isNotNeedRadioInput = true;
		} else if (empty($this->isCustomerGroupsFeatureInstalled)) {
			// only customer groups not in use
			unset($radioOptions['customer_groups']);
		}

		// +++++++++++++

		if (empty($isNotNeedRadioInput)) {
			// USE RADIO INPUTS
			// =======
			$options = [
				'id' => "pwcommerce_discount_customer_eligibility",
				'name' => 'pwcommerce_discount_customer_eligibility',
				'label' => $this->_('Customer Eligibility'),
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
				'id' => "pwcommerce_discount_customer_eligibility",
				'name' => 'pwcommerce_discount_customer_eligibility',
				// TODO DO WE MODEL THIS IN ALPINE SO AS TO DEAL WITH DISCOUNT TYPE NUANCES? E.G. 'whole_order_percentage' and 'whole_order_fixed'
				// TODO @UPDATE: WE DO HAVE THIS IN THE RADIO 'pwcommerce_discount_value_type' ALREADY! NOT NEEDED AGAIN! BUT MIGHT NEED FOR OTHER TYPES!
				'value' => 'all_customers'

			];

			$field = $this->pwcommerce->getInputfieldHidden($options);
			$hiddenInput = $field->render();

			$options = [
				'id' => "pwcommerce_discount_customer_eligibility_header",
				// 'skipLabel' => Inputfield::skipLabelHeader,
				'label' => $this->_('Customer Eligibility'),
				'collapsed' => Inputfield::collapsedNever,
				'classes' => 'pwcommerce_gift_card_delivery_times_header',
				'wrapClass' => true,
				'wrapper_classes' => 'pwcommerce_no_outline',
				// 'description' => $this->_('xxxx.'),
				'value' => $this->_('All customers are eligible for this discount. There are no customer eligibility restrictions. ') . $hiddenInput
			];

			$field = $this->pwcommerce->getInputfieldMarkup($options);
		}


		// -------
		return $field;
	}

	/**
	 * Get Markup For Discount Customer Eligibility Customer Groups Text Tags Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountCustomerEligibilityCustomerGroupsTextTagsField() {
		//------------------- pwcommerce_discount_customer_eligibility_customer_groups (getInputfieldTextTags)
		$description = $this->_('Customer groups eligible for this discount.');
		$customHookURL = "/find-pwcommerce_discount_customer_eligibility/";
		$tagsURL = "{$customHookURL}?q={q}&customer_type=customer_groups";
		$value = null;
		$setTagsList = [];
		// ======
		//for setting saved values if applicable
		if ($this->discountCustomerEligibilityType === 'customer_groups') {
			$pageIDs = $this->discountCustomerEligibility->implode("|", 'itemID');

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
		$placeholder = $this->_("Type at least 3 characters to search for customer groups.");

		$options = [
			'id' => "pwcommerce_discount_customer_eligibility_customer_groups",
			// TODO: not really needed!
			'name' => "pwcommerce_discount_customer_eligibility_customer_groups",
			'skipLabel' => Inputfield::skipLabelHeader,
			'label' => $this->_('Specific Customer Groups'),
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
			'show_if' => "pwcommerce_discount_customer_eligibility=customer_groups",
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
	 * Get Markup For Discount Customer Eligibility Specific Customers Text Tags Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountCustomerEligibilitySpecificCustomersTextTagsField() {
		//------------------- pwcommerce_discount_customer_eligibility_specific_customers (getInputfieldTextTags)
		$description = $this->_('Customers eligible for this discount.');
		$customHookURL = "/find-pwcommerce_discount_customer_eligibility/";
		$tagsURL = "{$customHookURL}?q={q}&customer_type=specific_customers";
		$value = null;
		$setTagsList = [];
		// ======
		//for setting saved values if applicable
		if ($this->discountCustomerEligibilityType === 'specific_customers') {
			$pageIDs = $this->discountCustomerEligibility->implode("|", 'itemID');
			$selector = "id={$pageIDs},include=hidden";
			/** @var array $pages */
			$pages = $this->wire('pages')->findRaw($selector, PwCommerce::CUSTOMER_FIELD_NAME . ".email");
			if (!empty($pages)) {
				// @note: $pages will be in the format $page->id => $page->pwcommerce_order_customer.email
				$value = array_keys($pages);
				$setTagsList = $pages;
			}
		}

		// TODO: IS THIS OK? ALLOW MORE?
		$placeholder = $this->_("Type at least 3 characters to search for customers.");

		$options = [
			'id' => "pwcommerce_discount_customer_eligibility_specific_customers",
			// TODO: not really needed!
			'name' => "pwcommerce_discount_customer_eligibility_specific_customers",
			'skipLabel' => Inputfield::skipLabelHeader,
			'label' => $this->_('Specific Customers'),
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
			'show_if' => "pwcommerce_discount_customer_eligibility=specific_customers",
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

	### MAXIMUM USES ###
	/**
	 * Render Discount Maximum Uses.
	 *
	 * @param mixed $wrapper
	 * @return string|mixed
	 */
	private function renderDiscountMaximumUses($wrapper) {
		// checkbox input to toggle show limit total
		$field = $this->getMarkupForDiscountMaximumUsesLimitTotalToggleCheckboxField();
		$wrapper->add($field);
		// text input for discount limit total
		$field = $this->getMarkupForDiscountMaximumUsesLimitTotalTextField();
		$wrapper->add($field);
		// checkbox input to toggle show limit per customer total
		$field = $this->getMarkupForDiscountMaximumUsesLimitPerCustomerToggleCheckboxField();
		$wrapper->add($field);
		// text input for discount limit per customer total
		$field = $this->getMarkupForDiscountMaximumUsesLimitPerCustomerTextField();
		$wrapper->add($field);
		// divider markup for sections that need it
		$field = $this->getMarkupForDiscountSectionsDividerMarkupField("maximum_uses");
		$wrapper->add($field);
		// -----
		return $wrapper;
	}

	/**
	 * Get Markup For Discount Maximum Uses Limit Total Toggle Checkbox Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountMaximumUsesLimitTotalToggleCheckboxField() {
		//------------------- pwcommerce_discount_maximum_uses_limit_total_toggle (getInputfieldCheckbox)
		//
		$checked = !empty($this->discount->discountLimitTotal);
		$options = [
			'id' => "pwcommerce_discount_maximum_uses_limit_total_toggle",
			'name' => "pwcommerce_discount_maximum_uses_limit_total_toggle",
			'label' => $this->_('Maximum discount uses'),
			'label2' => $this->_('Limit number of times this discount can be used in total'),
			'collapsed' => Inputfield::collapsedNever,
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

	/**
	 * Get Markup For Discount Maximum Uses Limit Total Text Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountMaximumUsesLimitTotalTextField() {
		//------------------- pwcommerce_discount_maximum_uses_limit_total (getInputfieldText)
		$options = [
			'id' => "pwcommerce_discount_maximum_uses_limit_total",
			'name' => "pwcommerce_discount_maximum_uses_limit_total",
			'type' => 'number',
			'step' => '1',
			// 'min' => 1,// @note: chrome error An invalid form control with name='pwcommerce_discount_maximum_uses_limit_total' is not focusable'. Happens if discountLimitTotal == 0 and show_if is hiding this input
			'min' => 0,
			'label' => $this->_('Limit Total Amount'),
			'skipLabel' => Inputfield::skipLabelHeader,
			'notes' => $this->_('Maximum number of times this discount can be used in total. Untick the checkbox for no limit.'),
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 75,
			'size' => 30,
			'show_if' => "pwcommerce_discount_maximum_uses_limit_total_toggle=1",
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
			'value' => $this->discount->discountLimitTotal
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		// allow HTML in description
		// $field->entityEncodeText = false;

		return $field;
	}

	/**
	 * Get Markup For Discount Maximum Uses Limit Per Customer Toggle Checkbox Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountMaximumUsesLimitPerCustomerToggleCheckboxField() {
		//------------------- pwcommerce_discount_maximum_uses_limit_per_customer_toggle (getInputfieldCheckbox)
		$checked = !empty($this->discount->discountLimitPerCustomer);
		$options = [
			'id' => "pwcommerce_discount_maximum_uses_limit_per_customer_toggle",
			'name' => "pwcommerce_discount_maximum_uses_limit_per_customer_toggle",
			// @note: skipping label
			'label' => ' ',
			// @note: skipping label
			'label2' => $this->_('Limit number of times this discount can be used per customer'),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top',
			'value' => 1,
			// 'checked' => 'checked',
			// 'checked' => true,
			'checked' => $checked,

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);

		return $field;
	}

	/**
	 * Get Markup For Discount Maximum Uses Limit Per Customer Text Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountMaximumUsesLimitPerCustomerTextField() {
		//------------------- pwcommerce_discount_maximum_uses_limit_per_customer (getInputfieldText)

		$options = [
			'id' => "pwcommerce_discount_maximum_uses_limit_per_customer",
			'name' => "pwcommerce_discount_maximum_uses_limit_per_customer",
			'type' => 'number',
			'step' => '1',
			// 'min' => 1,// @note: chrome error An invalid form control with name='pwcommerce_discount_maximum_uses_limit_per_customer' is not focusable'. Happens if discountLimitPerCustomer == 0 and show_if is hiding this input
			'min' => 0,
			'label' => $this->_('Limit Customer Total Amount'),
			'skipLabel' => Inputfield::skipLabelHeader,
			'notes' => $this->_('Maximum number of times this discount can be used per customer. Untick the checkbox for no limit.'),
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 75,
			'size' => 30,
			'show_if' => "pwcommerce_discount_maximum_uses_limit_per_customer_toggle=1",
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top',
			'value' => $this->discount->discountLimitPerCustomer
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		// allow HTML in description
		// $field->entityEncodeText = false;

		return $field;
	}

	### ACTIVE DATES ###
	/**
	 * Render Discount Active Dates.
	 *
	 * @param mixed $wrapper
	 * @return string|mixed
	 */
	private function renderDiscountActiveDates($wrapper) {
		// markup to show active dates header
		$field = $this->getMarkupForDiscountActiveDatesHeaderMarkupField();
		$wrapper->add($field);
		// datetime input for discount start date
		$field = $this->getMarkupForDiscountActiveDatesStartDateDatetimeField();
		$wrapper->add($field);
		// datetime input for discount end date
		$field = $this->getMarkupForDiscountActiveDatesEndDateDatetimeField();
		$wrapper->add($field);
		// checkbox input to toggle show discount end date
		$field = $this->getMarkupForDiscountActiveDatesSetEndDateCheckboxField();
		$wrapper->add($field);

		// -----
		return $wrapper;
	}

	/**
	 * Get Markup For Discount Active Dates Header Markup Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountActiveDatesHeaderMarkupField() {
		//------------------- active dates header markup (getInputfieldMarkup)
		$options = [
			'id' => "pwcommerce_discount_active_dates_header",
			// 'skipLabel' => Inputfield::skipLabelHeader,
			'label' => $this->_('Active Dates'),
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_gift_card_delivery_times_header',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			// 'description' => $this->_('xxxx.'),
			'value' => $this->_('Set a start date and optionally an end date for this discount.')
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		// -------
		return $field;
	}

	/**
	 * Get Markup For Discount Active Dates Start Date Datetime Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountActiveDatesStartDateDatetimeField() {
		$xstore = $this->xstore;
		//------------------- pwcommerce_discount_active_dates_start (getInputfieldDatetime)

		$options = [
			'id' => "pwcommerce_discount_active_dates_start",
			// TODO: not really needed!
			'name' => "pwcommerce_discount_active_dates_start",
			'type' => 'text',
			'label' => $this->_('Start Date'),
			// 'description' => $description . $extraDescriptionMarkup,
			// 'notes' => $notes,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => date('Y-m-d H:i:s', $this->discount->discountStartDate)
		];

		// TODO FOR NOW ONLY DOING DATE, NOT TIME. OK?
		$field = $this->pwcommerce->getInputfieldDatetime($options);

		// allow HTML in description
		// $field->entityEncodeText = false;
		$field->attr([
			// TODO UNCOMMENT THIS WHEN READY TO INIT THIS VALUE
			// 'x-model' => "{$this->xstore}.active_from",
			// ==========
			// // if no xxxx ?
			// 'x-bind:class' => $opacityClass,
			// // if no denonimation selected; input is disabled
			// 'x-bind:disabled' => $disabled,
			// 'x-ref' => "pwcommerce_discount_active_dates_start",
			'x-ref' => "pwcommerce_discount_active_dates_start",
			// @note: doesn't work; use mutation observer instead
			// 'x-on:change' => 'handleGiftCardEndDateChange',
			'data-discount-mutation-observer-notification-type' => 'date',
		]);

		$field->timeInputSelect = 0;
		$field->defaultToday = 1;

		// $field->set('dateInputFormat', "Y-m-d");
		// @NOTE: NOT IN USE FOR NOW; WE ONLY DO SERVER-SIDE VALIDATION
		// $errorHandlingMarkup = "<small class='pwcommerce_error' x-show='{$xstore}.is_error_discount_active_from_date' x-text='{$xstore}.discount_active_from_date_error_text'></small>";
		// // // $field->prependMarkup($errorHandlingMarkup);
		// $field->appendMarkup($errorHandlingMarkup);
		// +++++++++
		// @note: this sets a data attribute to the parent <li>. We will use mutation observer to listen to changes on this parent element since listening to changes to the jQuery UI text input is not working. We will then get the value of the input (the date, if any) using the id in this data attribute
		$field->wrapAttr('data-discount-mutation-observer-element-id', 'pwcommerce_discount_active_dates_start');

		return $field;
	}

	/**
	 * Get Markup For Discount Active Dates End Date Datetime Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountActiveDatesEndDateDatetimeField() {
		$xstore = $this->xstore;
		//------------------- pwcommerce_discount_active_dates_end (getInputfieldDatetime)
		$value = $this->getDiscountEndDate();

		//

		$options = [
			'id' => "pwcommerce_discount_active_dates_end",
			// TODO: not really needed!
			'name' => "pwcommerce_discount_active_dates_end",
			'type' => 'text',
			'label' => $this->_('End Date'),
			// 'description' => $description . $extraDescriptionMarkup,
			// 'notes' => $notes,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			'show_if' => 'pwcommerce_discount_active_dates_set_end=1',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $value
		];

		// TODO FOR NOW ONLY DOING DATE, NOT TIME. OK?
		$field = $this->pwcommerce->getInputfieldDatetime($options);

		// allow HTML in description
		// $field->entityEncodeText = false;
		$field->attr([
			// TODO UNCOMMENT THIS WHEN READY TO INIT THIS VALUE
			// 'x-model' => "{$this->xstore}.active_to",
			// ==========
			// // if no xxxx ?
			// 'x-bind:class' => $opacityClass,
			// // if no denonimation selected; input is disabled
			// 'x-bind:disabled' => $disabled,
			// 'x-ref' => "pwcommerce_discount_active_dates_end",
			'x-ref' => "pwcommerce_discount_active_dates_end",
			// @note: doesn't work; use mutation observer instead
			// 'x-on:change' => 'handleGiftCardEndDateChange',
			'data-discount-mutation-observer-notification-type' => 'date',
		]);

		$field->timeInputSelect = 0;
		$field->defaultToday = 1;

		// $field->set('dateInputFormat', "Y-m-d");
		// @NOTE: NOT IN USE FOR NOW; WE ONLY DO SERVER-SIDE VALIDATION
		// $errorHandlingMarkup = "<small class='pwcommerce_error' x-show='{$xstore}.is_error_discount_active_to_date' x-text='{$xstore}.discount_active_to_date_error_text'></small>";
		// // // $field->prependMarkup($errorHandlingMarkup);
		// $field->appendMarkup($errorHandlingMarkup);
		// +++++++++
		// @note: this sets a data attribute to the parent <li>. We will use mutation observer to listen to changes on this parent element since listening to changes to the jQuery UI text input is not working. We will then get the value of the input (the date, if any) using the id in this data attribute
		$field->wrapAttr('data-discount-mutation-observer-element-id', 'pwcommerce_discount_active_dates_end');

		return $field;
	}

	/**
	 * Get Discount End Date.
	 *
	 * @return mixed
	 */
	private function getDiscountEndDate() {
		$endDateTimestamp = (int) $this->discount->discountEndDate;
		if ($endDateTimestamp < 1) {
			// non-expiring discount
			$value = "";
		} else {
			$value = date('Y-m-d', $endDateTimestamp);
		}
		return $value;
	}

	/**
	 * Get Markup For Discount Active Dates Set End Date Checkbox Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountActiveDatesSetEndDateCheckboxField() {
		//------------------- pwcommerce_discount_active_dates_set_end (getInputfieldText)
		$checked = !empty($this->getDiscountEndDate());

		$options = [
			'id' => "pwcommerce_discount_active_dates_set_end",
			'name' => "pwcommerce_discount_active_dates_set_end",
			// @note: skipping label
			'label' => ' ',
			'label2' => $this->_('Set end date'),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_content_padding_top',
			'value' => 1,
			'checked' => $checked

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		// ----
		return $field;
	}

	// TODO DELETE IF NOT IN USE
	/**
	 * Render Discount Hidden Inputs.
	 *
	 * @param mixed $wrapper
	 * @return string|mixed
	 */
	private function renderDiscountHiddenInputs($wrapper) {

		$options = [
			'id' => "pwcommerce_discount_type",
			'name' => 'pwcommerce_discount_type',
			// TODO DO WE MODEL THIS IN ALPINE SO AS TO DEAL WITH DISCOUNT TYPE NUANCES? E.G. 'whole_order_percentage' and 'whole_order_fixed'
			// TODO @UPDATE: WE DO HAVE THIS IN THE RADIO 'pwcommerce_discount_value_type' ALREADY! NOT NEEDED AGAIN! BUT MIGHT NEED FOR OTHER TYPES!
			'value' => $this->discountType,
		];

		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);
		// -----
		return $wrapper;
	}

	#####

	/**
	 * Get Markup For Discount Sections Divider Markup Field.
	 *
	 * @param mixed $idSuffix
	 * @return mixed
	 */
	protected function getMarkupForDiscountSectionsDividerMarkupField($idSuffix) {
		//------------------- discount method hr divider  markup (getInputfieldMarkup)
		// @note: TODO: temporary solution to some styling quark
		$hrClass = $idSuffix === 'minimum_requirement' ? " class='mt-5'" : '';
		$options = [
			'id' => "pwcommerce_discount_sections_divider_{$idSuffix}",
			'skipLabel' => Inputfield::skipLabelHeader,
			// 'label' => $this->_('XXx'),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			#main .pwcommerce_override_processwire_inputfield_content_padding_bottom>.InputfieldContent {
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top  pwcommerce_override_processwire_inputfield_content_padding_bottom',
			// 'description' => $this->_('xxxx.'),
			'value' => "<hr{$hrClass}>"
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		// -------
		return $field;
	}

	// ~~~~~~~~~~~~~~
	/**
	 * Process Ajax Request.
	 *
	 * @return mixed
	 */
	public function processAjaxRequest() {
		// @NOTE: FOR NOW, WE ONLY DEAL WITH GENERATE DISCOUNT CODE
		$field = $this->getMarkupForDiscountMethodCodeTextField();
		// $value = $this->getUniqueAutomaticDiscountCode();
		$value = $this->pwcommerce->getUniqueAutomaticDiscountCode();

		$field->value = strtoupper($value);
		$out = $field->render();

		// -----
		return $out;
	}

	// ~~~~~~~~~~~~~~

	/**
	 * Process input for the values sent from the shipping rate for this page
	 *
	 * @param WireInputData $input
	 * @return mixed
	 */
	public function ___processInput(WireInputData $input) {

		// **** @NOTE: @UPDATE TODO? THIS IS OK SINCE THIS IS A CUSTOM INPUTFIELD FOR A VERY SPECIFIC NEED! PWCOMMERCE! ****
		// @note: this is now called once from inside InputfieldPWCommerceRuntimeMarkup::processInput!
		// SAVE NEW ITEMS FIRST
		// @note: these were added as empty items via ajax
		// we need to create and save their pages first!
		// $newItems = $input->pwcommerce_is_new_item;
		// if (!empty($newItems)) {
		//   // $this->processInputCreateNewItems($input);
		// }

		// @note -ditto-
		// $deleteItems = $input->pwcommerce_is_delete_item;
		// if (!empty($deleteItems)) {
		//   $this->processInputDeleteItems($input);
		// }

		$sanitizer = $this->wire('sanitizer');


		// @note: we cannot rely on $this->discountType since this is the currently saved value!
		// @see: _construct()
		// instead, get the sent one from $input->pwcommerce_discount_value_type
		//
		// $discountType = $sanitizer->fieldName($input->pwcommerce_discount_value_type);
		//

		/**
		 * 1. SAVE pwcommerce_discount (WireData) values
		 * 2. SAVE pwcommerce_discounts_apply_to (WireArray) values
		 * 3. SAVE pwcommerce_discounts_eligibility (WireArray) values
		 */

		/////
		// EXPECTED INPUTS
		// @NOTE: SOME ARE JUST FOR DETERMINATION; NOT SAVING!

		// pwcommerce_discount_method: radio > DETERMINISTIC for ...code (discount_code) vs ...automatic (automatic_discount)
		// pwcommerce_discount_method_code: text >  $discount->code
		// pwcommerce_discount_method_automatic: text >  $discount->code
		// pwcommerce_discount_method_code_generate: button > N/A for save
		# ----
		// pwcommerce_discount_value_type: radio (whole_order_percentage|whole_order_fixed) > $discount->discountType
		// pwcommerce_discount_value: text.number > FLOAT $discount->discountValue
		# ----
		// pwcommerce_discount_minimum_requirement_type: radio (none|purchase|quantity) > $discount->discountMinimumRequirementType
		// pwcommerce_discount_minimum_requirement > INT(quantity)|FLOAT (purchase) $discount->discountMinimumRequirementAmount {@note:  during runtime float vs int depending on type}
		# ---- @see: $this->processInputForDiscountsEligibility()
		// pwcommerce_discount_customer_eligibility
		// pwcommerce_discount_customer_eligibility_customer_groups
		// pwcommerce_discount_customer_eligibility_specific_customers
		# ----
		// pwcommerce_discount_maximum_uses_limit_total_toggle:  checkbox > DETERMINISTIC for 'pwcommerce_discount_maximum_uses_limit_total'
		// pwcommerce_discount_maximum_uses_limit_total: INT >  $discount->discountLimitTotal
		// pwcommerce_discount_maximum_uses_limit_per_customer_toggle  checkbox > DETERMINISTIC for  'pwcommerce_discount_maximum_uses_limit_per_customer'
		// pwcommerce_discount_maximum_uses_limit_per_customer: INT >  $discount->discountLimitPerCustomer
		# ----
		// pwcommerce_discount_active_dates_start > WILL DEFAULT TO TODAY + WILL BE SANITIZED IN
		// pwcommerce_discount_active_dates_set_end  checkbox > DETERMINISTIC for 'pwcommerce_discount_active_dates_end'
		// pwcommerce_discount_active_dates_end
		//
		# ----
		// pwcommerce_discount_type: hidden field; not in use for order discount
		///

		//-----------------
		// PROCESS & SAVE DISCOUNT VALUES FOR THIS PAGE

		// ++++

		$discount = $this->field->type->getBlankValue($this->page, $this->field);

		// ********************* PROCESS VALUES ******************
		// #############

		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE METHODS BELOW

		// PROCESS DISCOUNT IS AUTOMATIC
		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
		$isAutomaticDiscount = $this->processIsAutomaticDiscount($input);

		// PROCESS DISCOUNT CODE
		$code = $this->processDiscountCode($input);

		// PROCESS DISCOUNT VALUE TYPE
		//@note: pwcommerce_discount_value_type: radio (whole_order_percentage|whole_order_fixed)
		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
		$discountValueType = $this->processDiscountValueType($input);
		if (empty($discountValueType)) {
			// invalid discount value type (for some reason!)
			$this->inputErrors[] = $this->_('Missing discount value type');
		}

		// PROCESS DISCOUNT VALUE
		//@note: pwcommerce_discount_value: FLOAT
		// $discountValue = (float) $input->pwcommerce_discount_value;
		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
		$discountValue = $this->processDiscountValue($input);
		if (empty($discountValue)) {
			// empty discount value
			$this->inputErrors[] = $this->_('Empty discount value');
		}

		// PROCESS DISCOUNT MINIMUM REQUIREMENT TYPE
		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
		$discountMinimumRequirementType = $this->processDiscountMinimumRequirementType($input);
		if (empty($discountMinimumRequirementType)) {
			// invalid discount minimum requirement type (for some reason!)
			$this->inputErrors[] = $this->_('Missing discount minimum requirement type');
		}

		// PROCESS DISCOUNT MINIMUM REQUIREMENT AMOUNT
		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
		$discountMinimumRequirementAmount = $this->processDiscountMinimumRequirementAmount($input, $discountMinimumRequirementType);

		// PROCESS DISCOUNT MAXIMUM USES: lIMIT TOTAL
		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
		$discountLimitTotal = $this->processDiscountLimitTotal($input);

		// PROCESS DISCOUNT MAXIMUM USES: lIMIT PER CUSTOMER
		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
		$discountLimitPerCustomer = $this->processDiscountLimitPerCustomer($input);

		// PROCESS DISCOUNT ACTIVE DATES: START DATE
		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
		$discountStartDate = $this->processDiscountStartDate($input);
		// PROCESS DISCOUNT ACTIVE DATES: END DATE
		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
		$discountEndDate = $this->processDiscountEndDate($input, $discountStartDate);

		// PROCESS DISCOUNT META DATA
		// @note: not needed in some cases
		$discountMetaData = $this->processDiscountMetaData($input);

		# ~~~~~~~~~~~

		// process: FieldtypePWCommerceDiscountsApplyTo
		// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
		$discountsApplyTo = $this->processInputForDiscountsApplyTo($input);
		if (is_null($discountsApplyTo)) {
			// TODO CHANGE PHRASE TO CATER FOR APPLIES TO FOR PRODUCTS! CREATE A METHOD this is because for product discounts, error can be about missing radio (applies to specific_categories or specific_products) AND/OR missing IDs in selectize, i.e. missing categories or products IDs. Hence, need to tailor for these two scenarios!
			// invalid discount value type (for some reason!)
			// $errors[] = $this->_('Missing discount value type');
			// @NOTE: EXTENDING/CHILD CLASSES CAN OVERRIDE THIS METHOD
			$errorForDiscountApplyTo = $this->getErrorForDiscountsApplyTo($input);
			if (!empty($errorForDiscountApplyTo)) {
				$this->inputErrors[] = $errorForDiscountApplyTo;
			}

		}

		# ~~~~~~~~~~~

		// process: FieldtypePWCommerceDiscountsEligibility
		$discountsEligibility = $this->processInputForDiscountsEligibility($input);
		if (is_null($discountsEligibility)) {
			// invalid discount value type (for some reason!)
			$this->inputErrors[] = $this->_('Missing values for customer eligibility');
		}

		// get extra errors from exending classes
		$this->inputErrors = $this->processExtraInputErrors($this->inputErrors);

		// +++++++++++++++++++++
		// check for missing required values
		if (!empty($this->inputErrors)) {
			// ERRORS: ABORT SAVE!
			$this->error(sprintf(__("There were errors.  Please fill these missing values: %s."), implode(', ', $this->inputErrors)));
			// abort if errrors
			// @note: this will return the previous saved values. Ideally, need to return the form in submitted state!
			return;
		}

		// ##############
		// GOOD TO GO
		/** @var WireData $discount */
		$discount = $this->field->type->getBlankValue($this->page, $this->field);
		// is automatic discount?
		$discount->isAutomaticDiscount = $isAutomaticDiscount;
		// code
		$discount->code = $code;
		// discount value type
		$discount->discountType = $discountValueType;
		// discount value
		$discount->discountValue = $discountValue;
		// minimum requirement type
		$discount->discountMinimumRequirementType = $discountMinimumRequirementType;
		// minimum requirement amount
		$discount->discountMinimumRequirementAmount = $discountMinimumRequirementAmount;
		// limit total
		$discount->discountLimitTotal = $discountLimitTotal;
		// limit per customer
		$discount->discountLimitPerCustomer = $discountLimitPerCustomer;
		// start date
		$discount->discountStartDate = $discountStartDate;
		// limit per customer
		$discount->discountEndDate = $discountEndDate;
		// meta data
		// @note: not needed in some cases
		$discount->discountMetaData = $discountMetaData;
		##########
		// GLOBAL USAGE: ENSURE NOT EDITABLE VIA INPUTFIELD!
		// @NOTE: WE PRESERVE EXISTING VALUE SINCE THIS IS SET DYNAMICALLY IN OTHER PROCESSES
		// i.e., with every discount use
		$currentSavedDiscountValues = $this->page->get(PwCommerce::DISCOUNT_FIELD_NAME);
		$discountGlobalUsage = $currentSavedDiscountValues->discountGlobalUsage;
		$discount->discountGlobalUsage = $discountGlobalUsage;
		// -----
		// get comparable currently saved discount values
		// will determine if we have changes to save for InputfieldPWCommerceDiscount
		$compareCurrentSavedDiscountValues = $this->getCurrentSavedDiscountValuesForCompare($discount, $currentSavedDiscountValues);


		// +++++++++

		// if the string value of the processed discount is different from the previous,
		// then save the page
		// @note: we compare using an in-house toString() private method as we don't implement toString() in the field.
		if ($this->toStringInhouse($discount) !== $this->toStringInhouse($compareCurrentSavedDiscountValues)) {

			// ========
			// 1. SAVE PAGE field 'pwcommerce_discount': FieldtypePWCommerceDiscount
			// @NOTE WON'T WORK HERE! JUST SAVE!!
			// $this->attr('value', $discount);
			// $this->trackChange('value');
			$this->page->setAndSave(PwCommerce::DISCOUNT_FIELD_NAME, $discount);
		} else {

		}
		// ========
		// 2. SAVE PAGE field 'pwcommerce_discounts_apply_to': FieldtypePWCommerceDiscountsApplyTo
		$this->page->setAndSave(PwCommerce::DISCOUNT_APPLIES_TO_FIELD_NAME, $discountsApplyTo);
		// ========
		// 3. SAVE PAGE field 'pwcommerce_discounts_eligibility': FieldtypePWCommerceDiscountsEligibility
		// @note: we need to save always since all previous values could have been deleted but no new ones supplied
		// hence, need to clear old values (TODO?)
		$this->page->setAndSave(PwCommerce::DISCOUNT_ELIGIBILITY_FIELD_NAME, $discountsEligibility);
	}

	/**
	 * Process Is Automatic Discount.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	protected function processIsAutomaticDiscount($input) {
		$discountMethod = $this->wire('sanitizer')->fieldName($input->pwcommerce_discount_method);
		$isAutomaticDiscount = $discountMethod === 'automatic_discount';

		// -------
		return $isAutomaticDiscount;
	}

	/**
	 * Process Discount Value Type.
	 *
	 * @param WireInputData $input
	 * @return mixed
	 */
	protected function processDiscountValueType(WireInputData $input) {
		$discountValueTypeRaw = $input->pwcommerce_discount_value_type;
		$allowedDiscountTypes = $this->pwcommerce->getAllowedDiscountTypes();
		$discountValueType = $this->wire('sanitizer')->option($discountValueTypeRaw, $allowedDiscountTypes);

		// -------
		return $discountValueType;
	}

	/**
	 * Process Discount Code.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	protected function processDiscountCode($input) {
		// @note: if discount method is auto > pwcommerce_discount_method_automatic
		// @note: if discount method is code > pwcommerce_discount_method_code
		$sanitizer = $this->wire('sanitizer');
		// -------
		$codeErrorAutomatic = $this->_('Discount title must be specified');
		$codeErrorCode = $this->_('Discount code must be specified');
		$discountMethod = $sanitizer->fieldName($input->pwcommerce_discount_method);
		$isAutomaticDiscount = $discountMethod === 'automatic_discount';

		if ($isAutomaticDiscount) {
			$code = $input->pwcommerce_discount_method_automatic;
			$codeError = $codeErrorAutomatic;
		} else {
			$code = $input->pwcommerce_discount_method_code;
			$codeError = $codeErrorCode;
		}
		$code = $sanitizer->text($code);

		if (empty($code)) {
			// missing code/discount title
			$this->inputErrors[] = $codeError;
		}

		// --
		return $code;
	}

	/**
	 * Process Discount Value.
	 *
	 * @param WireInputData $input
	 * @return mixed
	 */
	protected function processDiscountValue(WireInputData $input) {
		$discountValue = (float) $input->pwcommerce_discount_value;

		// -------
		return $discountValue;
	}

	/**
	 * Process Discount Minimum Requirement Type.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	protected function processDiscountMinimumRequirementType($input) {
		//@note: pwcommerce_discount_minimum_requirement_type: TEXT IN ALLOWED OPTIONS (none|purchase|quantity)
		$discountMinimumRequirementTypeRaw = $input->pwcommerce_discount_minimum_requirement_type;
		$allowedMinimumRequirementTypes = $this->pwcommerce->getAllowedMinimumRequirementTypes();
		$discountMinimumRequirementType = $this->wire('sanitizer')->option($discountMinimumRequirementTypeRaw, $allowedMinimumRequirementTypes);

		// --------
		return $discountMinimumRequirementType;
	}

	/**
	 * Process Discount Minimum Requirement Amount.
	 *
	 * @param mixed $input
	 * @param mixed $discountMinimumRequirementType
	 * @return mixed
	 */
	protected function processDiscountMinimumRequirementAmount($input, $discountMinimumRequirementType) {
		//@note: pwcommerce_discount_minimum_requirement: FLOAT/INT for 'purchase'/'amount' respectively
		// $minimumRequirementeErrorAmount = $this->_('Minimum quantity items must be specified');
		// $minimumRequirementeErrorPurchase = $this->_('Minimum purchase amount must be specified');
		$discountMinimumRequirementAmount = 0;
		if (in_array($discountMinimumRequirementType, ['purchase', 'quantity'])) {
			if ($discountMinimumRequirementType === 'purchase') {
				$discountMinimumRequirementAmount = (float) $input->pwcommerce_discount_minimum_requirement;
				// @NOTE: NOT NEEDED! MINIMUMS CAN BE ZERO! EXCEPT FOR BOGO! IT HAS ITS OVERRIDE METHOD
				// $minimumRequirementError = $minimumRequirementeErrorPurchase;
			} else {
				$discountMinimumRequirementAmount = (int) $input->pwcommerce_discount_minimum_requirement;
				// @NOTE: NOT NEEDED! MINIMUMS CAN BE ZERO!
				// $minimumRequirementError = $minimumRequirementeErrorAmount;
			}
			// ----
			// @NOTE: NOT NEEDED! MINIMUMS CAN BE ZERO! EXCEPT FOR BOGO! IT HAS ITS OVERRIDE METHOD
			// if (empty($discountMinimumRequirementAmount)) {
			// 	// empty minimum requirement value yet requirement type IS NOT 'none'
			// 	$errors[] = $minimumRequirementError;
			// }
		}

		// --------
		return $discountMinimumRequirementAmount;
	}

	/**
	 * Process Discount Limit Total.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	protected function processDiscountLimitTotal($input) {
		//@note: pwcommerce_discount_maximum_uses_limit_total_toggle: INT checkbox if sent
		//@note: pwcommerce_discount_maximum_uses_limit_total: INT

		$discountLimitTotal = 0;
		if (!empty((int) $input->pwcommerce_discount_maximum_uses_limit_total_toggle)) {
			$discountLimitTotal = (int) $input->pwcommerce_discount_maximum_uses_limit_total;
			if (empty($discountLimitTotal)) {
				$this->inputErrors[] = $this->_('Limit total cannot be zero');
			}

		}
		// --------
		return $discountLimitTotal;
	}

	/**
	 * Process Discount Limit Per Customer.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	protected function processDiscountLimitPerCustomer($input) {
		//@note: pwcommerce_discount_maximum_uses_limit_per_customer_toggle: INT checkbox if sent
		//@note: pwcommerce_discount_maximum_uses_limit_per_customer: INT

		$discountLimitPerCustomer = 0;
		if (!empty((int) $input->pwcommerce_discount_maximum_uses_limit_per_customer_toggle)) {
			$discountLimitPerCustomer = (int) $input->pwcommerce_discount_maximum_uses_limit_per_customer;
			if (empty($discountLimitPerCustomer)) {
				$this->inputErrors[] = $this->_('Limit per customer cannot be zero');
			}

		}
		// --------
		return $discountLimitPerCustomer;
	}

	/**
	 * Process Discount Start Date.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	protected function processDiscountStartDate($input) {
		//@note: pwcommerce_discount_active_dates_start: TEXT
		$discountStartDate = $this->wire('sanitizer')->text($input->pwcommerce_discount_active_dates_start);

		if (empty($discountStartDate)) {
			$this->inputErrors[] = $this->_('Start date cannot be empty');
		}
		// --------
		return $discountStartDate;
	}

	/**
	 * Process Discount End Date.
	 *
	 * @param mixed $input
	 * @param mixed $discountStartDate
	 * @return mixed
	 */
	protected function processDiscountEndDate($input, $discountStartDate) {
		//@note: pwcommerce_discount_active_dates_set_end: INT checkbox if sent
		//@note: pwcommerce_discount_active_dates_end: TEXT

		$discountEndDate = "";
		if (!empty((int) $input->pwcommerce_discount_active_dates_set_end)) {
			// if END DATE GETTING SET
			$discountEndDate = $this->wire('sanitizer')->text($input->pwcommerce_discount_active_dates_end);
			if (empty($discountEndDate)) {
				$this->inputErrors[] = $this->_('End date cannot be empty');
			}

			// validate END date
			$this->validateDiscountDates($discountStartDate, $discountEndDate);

		}
		// --------
		return $discountEndDate;
	}

	/**
	 * Process Discount Meta Data.
	 *
	 * @param WireInputData $input
	 * @return mixed
	 */
	protected function processDiscountMetaData(WireInputData $input) {
		// @note: nothing to do for order discount
		$metaData = '';
		// -----
		return $metaData;
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
		// @NOTE: for order discount, we save only one record
		// either 'whole_order_percentage' OR 'whole_order_fixed'
		// @NOTE: can get this from FieldtypePWCommerceDiscount::discountType as well!
		// INPUTS:
		//pwcommerce_discount_value_type: radio (whole_order_percentage|whole_order_fixed)
		$sanitizer = $this->wire('sanitizer');
		$discountsApplyTo = NULL;

		// APPLIES TO TYPE
		$discountAppliesToType = $input->pwcommerce_discount_value_type;
		$allowedAppliesToItemTypes = $this->pwcommerce->getAllowedAppliesToItemTypes();
		$discountAppliesToItemType = $sanitizer->option($discountAppliesToType, $allowedAppliesToItemTypes);
		# ----------

		// ----
		if (!empty($discountAppliesToItemType)) {
			$field = $this->page->getField(PwCommerce::DISCOUNT_APPLIES_TO_FIELD_NAME);

			/** @var WireArray discountAppliesTo */
			$discountsApplyTo = $field->type->getBlankValue($this->page, $field);

			// ------------
			// ALL CUSTOMERS
			// we save only one record
			/** @var WireData $discountAppliesTo */
			$discountAppliesTo = $field->type->getBlankRecord();
			$discountAppliesTo->itemID = 0;
			$discountAppliesTo->itemType = $discountAppliesToItemType; // 'whole_order_percentage'|'whole_order_fixed'
			// ---------
			// add to WireArray
			$discountsApplyTo->add($discountAppliesTo);

			// ========
			// TODO: DELETE WHEN DONE: WE NOW SAVE IN processInput() if no errors overall
			// SAVE PAGE field 'pwcommerce_discounts_apply_to'
			// @note: we need to save always since all previous values could have been deleted but no new ones supplied
			// hence, need to clear old values (TODO?)
			// $this->page->setAndSave(PwCommerce::DISCOUNT_APPLIES_TO_FIELD_NAME, $discountsApplyTo);

		}

		// -----
		return $discountsApplyTo;

	}

	/**
	 * Get Error For Discounts Apply To.
	 *
	 * @param WireInputData $input
	 * @return mixed
	 */
	protected function getErrorForDiscountsApplyTo(WireInputData $input) {
		$errorString = $this->_('Missing discount value type');
		// -----
		return $errorString;
	}

	/**
	 * Process Input For Discounts Eligibility.
	 *
	 * @param WireInputData $input
	 * @return mixed
	 */
	protected function processInputForDiscountsEligibility(WireInputData $input) {
		// TODO:
		// PROCESS INPUTS FOR 'FieldtypePWCommerceDiscountsEligibility'
		// $discountEligibility
		// INPUTS:
		/***
		 * pwcommerce_discount_customer_eligibility: radio (all_customers|customer_groups|specific_customers)
		 * pwcommerce_discount_customer_eligibility_customer_groups
		 * pwcommerce_discount_customer_eligibility_specific_customers
		 */

		$sanitizer = $this->wire('sanitizer');
		$discountsEligibility = NULL;

		// ELIGIBILITY TYPE
		$discountCustomerEligibilityType = $input->pwcommerce_discount_customer_eligibility;
		$allowedEligibilityItemTypes = $this->pwcommerce->getAllowedEligibilityItemTypes();
		$discountEligibilityItemType = $sanitizer->option($discountCustomerEligibilityType, $allowedEligibilityItemTypes);
		# ----------

		if (!empty($discountEligibilityItemType)) {
			$field = $this->page->getField(PwCommerce::DISCOUNT_ELIGIBILITY_FIELD_NAME);

			/** @var WireArray $discountsEligibility */
			$discountsEligibility = $field->type->getBlankValue($this->page, $field);

			// ----
			if ($discountEligibilityItemType === 'all_customers') {
				// ALL CUSTOMERS
				// we save only one record
				/** @var WireData $discountEligibility */
				$discountEligibility = $field->type->getBlankRecord();
				$discountEligibility->itemID = 0;
				$discountEligibility->itemType = $discountEligibilityItemType; // 'all_customers'
				// ---------
				// add to WireArray
				$discountsEligibility->add($discountEligibility);

			} elseif ($discountEligibilityItemType === 'customer_groups') {
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
						$discountEligibility->itemType = $discountEligibilityItemType; // 'customer_groups'
						// ---------
						// add to WireArray
						$discountsEligibility->add($discountEligibility);
					}
				}
			} elseif ($discountEligibilityItemType === 'specific_customers') {
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
						$discountEligibility->itemType = $discountEligibilityItemType; // 'specific_customers'
						// ---------
						// add to WireArray
						$discountsEligibility->add($discountEligibility);
					}
				}
			}

			// ========
			// TODO: DELETE WHEN DONE: WE NOW SAVE IN processInput() if no errors overall
			// SAVE PAGE field 'pwcommerce_discounts_eligibility'
			// @note: we need to save always since all previous values could have been deleted but no new ones supplied
			// hence, need to clear old values (TODO?)
			// $this->page->setAndSave(PwCommerce::DISCOUNT_ELIGIBILITY_FIELD_NAME, $discountsEligibility);
			// ++++++++
			// if WireArray discountsEligibility is empty, we have an error
			// NULLIFY the WireArray
			if (empty($discountsEligibility->count())) {
				$discountsEligibility = NULL;

			}
		}

		// ------
		return $discountsEligibility;
	}

	/**
	 * Process Extra Input Errors.
	 *
	 * @param mixed $errors
	 * @return mixed
	 */
	protected function processExtraInputErrors($errors) {

		// nothing to do here but extending classes can add to the errors
		return $errors;
	}

	/**
	 * Get Current Saved Discount Values For Compare.
	 *
	 * @param WireData $discount
	 * @param WireData $currentSavedDiscountValues
	 * @return mixed
	 */
	private function getCurrentSavedDiscountValuesForCompare(WireData $discount, WireData $currentSavedDiscountValues) {
		$compareCurrentSavedDiscountValues = new WireData();
		foreach ($discount as $key => $value) {
			$setValue = $currentSavedDiscountValues->get($key);
			if (in_array($key, ['discountStartDate', 'discountEndDate'])) {
				// for dates, convert timestamp to string dates
				$setValue = date('Y-m-d', $setValue);
			}

			$compareCurrentSavedDiscountValues->set($key, $setValue);
		}
		//-------
		return $compareCurrentSavedDiscountValues;
	}

	/**
	 * Validate Discount Dates.
	 *
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return mixed
	 */
	private function validateDiscountDates($startDate, $endDate) {
		$error = "";
		$startDateTimestamp = strtotime($startDate);
		$endDateTimestamp = strtotime($endDate);

		// -------
		// ERROR CHECKS
		// ----
		if ($endDateTimestamp < $startDateTimestamp) {
			// error: end date earlier than start date
			$error = $this->_('End date cannot be earlier than start date');
		} elseif ($endDateTimestamp < time()) {
			// error: end date earlier than today
			$error = $this->_('End date cannot be in the past');
		}

		if (!empty($error)) {
			$this->inputErrors[] = $error;
		}

		// ----
	}

	/**
	 * Make a string value to represent the discount values that can be used for comparison purposes.
	 *
	 * @param WireData $item
	 * @return mixed
	 */
	private function toStringInhouse(WireData $item) {
		$string = implode(": ", $item->getArray());

		return $string;
	}
}