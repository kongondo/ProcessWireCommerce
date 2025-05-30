<?php

namespace ProcessWire;

/**
 * PWCommerce: InputfieldPWCommerceOrder -> InputfieldPWCommerceOrderRenderDiscounts
 *
 * Helper render class for InputfieldPWCommerceOrder.
 * For displaying order discounts.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceOrderRenderDiscounts for PWCommerce
 * Copyright (C) 2022 by Francis Otieno
 * MIT License
 *
 */


class InputfieldPWCommerceOrderRenderDiscounts extends WireData
{


	// =============
	protected $page;
	private $order; // the order


	// ----------
	private $xstoreOrder; // the alpinejs store used by this inputfield.
	private $xstore; // the full prefix to the alpine store used by this inputfield
	private $xstoreClient; // shortcut to the client object in the alpinejs store in this inputfield.
	private $ajaxPostURL;
	// -----
	private $shopCurrencySymbolString = "";



	public function __construct($page) {

		// $this->page = $page;



		// GET UTILITIES CLASS



		// ----------
		// set shop currency symbol if available
		$this->setShopCurrencySymbolString();

		// ==================
		$this->xstoreOrder = 'InputfieldPWCommerceOrderStore';
		// i.e., '$store.InputfieldPWCommerceOrderStore'
		$this->xstore = "\$store.{$this->xstoreOrder}";
		// @note: even client-only data lives under 'order_whole_data'
		$this->xstoreClient = "{$this->xstore}.order_whole_data.client";
		$this->ajaxPostURL = $this->wire('config')->urls->admin . PwCommerce::PWCOMMERCE_SHOP_PAGE_IN_ADMIN_NAME . '/ajax/';
	}

	/**
	 * Render the entire input area for order
	 *
	 */
	public function ___render(WireData $order) {
		$this->order = $order;

		// -------------
		return $this->getMainOrderDiscountsSummaryMarkup();
	}

	private function renderEditModals() {

		//---------------
		// TODO: CHECK IF ISSUES: RENAMED ID FROM 'pwcommerce_order_main_edit_modals'
		// @note: these open in modals! they interact with alpine JS
		$out = "<div id='pwcommerce_order_edit_modal_for_line_item_discounts' x-data='InputfieldPWCommerceOrderData'>" .
			// TODO DELETE WHEN DONE IF NO LONGER IN USE, I.E. USING INLINE ONE FOR MAIN ORDER!
			// initialise 'edit whole order discount' for use with alpine
			// $this->getModalMarkupForEditOrderMainDiscount() .
			// initialise 'edit an order line item discount' for use with alpine
			$this->getModalMarkupForEditOrderLineItemDiscount() .

			//-------------
			"</div>";

		return $out;
	}

	private function setShopCurrencySymbolString() {
		// if currency locale set..
		// grab symbol; we use on price fields description
		$shopCurrencySymbolString = $this->pwcommerce->renderShopCurrencySymbolString();
		if (strlen($shopCurrencySymbolString)) {
			$this->shopCurrencySymbolString = " " . $shopCurrencySymbolString;
		}

	}

	// TODO DELETE WHEN DONE
	// modal for single order line item add/edit discount
	private function getModalMarkupForEditOrderLineItemDiscount() {
		$xstore = "{$this->xstore}.edit_current_order_line_item_discount";
		//--------------
		$headerText = $this->_('Discount for');
		$header = "<span>{$headerText} </span><span x-text='{$xstore}.productTitle'></span>";
		// $body = $wrapper->render();
		// TODO will be deleted!
		$body = $this->getMarkupForEditOrderLineItemDiscountOLD();

		$applyButton = $this->pwcommerce->getModalActionButton(['x-on:click' => 'updateSingleLineItemDiscount'], 'apply');
		$cancelButton = $this->pwcommerce->getModalActionButton(['x-on:click' => 'resetEditSingleLineItemDiscountAndClose'], 'cancel');
		$footer = "<div class='ui-dialog-buttonset'>{$applyButton}{$cancelButton}</div>";
		$xproperty = 'is_current_edit_order_line_item_discount_modal_open';
		$size = '5x-large';
		// wrap content in modal for adding/editing an order line item's discount
		// modal options
		$options = [
			// $header The modal title pane markup.
			'header' => $header,
			// $body The main content markup.
			'body' => $body,
			// $footer The footer markup.
			'footer' => $footer,
			// $xstore The alpinejs store with the property that will be modelled to show/hide the modal.
			'xstore' => $this->xstoreOrder,
			// $xproperty The alpinejs property that will be modelled to show/hide the modal.
			'xproperty' => $xproperty,
			// $size The size of the modal requested.
			'size' => $size,
		];
		$out = $this->pwcommerce->getModalMarkup($options);
		return $out;
	}

	public function getMarkupForEditOrderLineItemDiscount() {
		// TODO NEED LINE ITEM!
		// --------
		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		// TODO: @UPDATE: NO NEED TO MODEL NOW AS THESE WILL BE THE INPUTS! SO NO NEED FOR STORE? -> JUST NEED DYNAMIC NAMES HERE!

		// TODO DELETE WHEN DONE
		// $xstore = "{$this->xstore}.edit_current_order_line_item_discount";

		//------------------- line item sku  (getInputfieldMarkup)
		// $skuText = $this->_('SKU');
		// $sku = "<h4 class='mb-1'>{$skuText}: <span x-text='{$xstore}.productSKU'></span></h4>";
		// $options = [
		// 	'skipLabel' => Inputfield::skipLabelHeader,
		// 	'collapsed' => Inputfield::collapsedNever,
		// 	'wrapClass' => true,
		// 	'wrapper_classes' => 'pwcommerce_no_outline',
		// 	'value' => $sku,

		// ];

		// $field = $this->pwcommerce->getInputfieldMarkup($options);
		// $wrapper->add($field);

		//------------------- line item discount type (getInputfieldSelect)

		$selectOptions = [
			'none' => $this->_('None'),
			'percentage' => $this->_('Percentage'),
			'fixed_applied_once' => $this->_('Fixed (applied once)'),
			'fixed_applied_per_item' => $this->_('Fixed (applied per item)'),
		];

		// TODO: add dynamic notes with respect to above
		$options = [
			// 'id' => "pwcommerce_order_line_item_discount_type",
			// TODO: not really needed! @UPDATE: NOW NEEDED! WE NEED IT FOR THIS LINE ITEM!
			// 'name' => 'pwcommerce_order_line_item_discount_type',
			// TODO: SKIP LABEL?
			'label' => $this->_('Discount Type'),
			//  'skipLabel' => Inputfield::skipLabelHeader,
			'description' => $this->_('Line item discount type.'),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_order_line_item_edit_discount',
			'classes' => 'pwcommerce_order_line_item_discount_type',
			'columnWidth' => 50,
			'select_options' => $selectOptions,
		];

		$field = $this->pwcommerce->getInputfieldSelect($options);
		//$field->attr('x-model', "{$xstore}.discountType");
		// TODO: SO, LET'S BIND IDS BUT NOT NAMES! ids important to bind since processwire will give them same id but in alpine we will loop and create as many as we need, hence, line items with duplicate ids!
		$field->attr([
			// 'x-model' => "{$xstore}.discountType",
			'x-model' => "product.discountType",
			'x-on:change' => "handleOrderLineItemDiscountChange(product)",
			'x-bind:id' => '`pwcommerce_order_line_item_discount_type${product.id}`',
			'x-bind:name' => '`pwcommerce_order_line_item_discount_type${product.id}`',

		]);
		$wrapper->add($field);

		//------------------- line item discount value (getInputfieldText)

		// $description = $this->_('Discount value for selected line item discount type');
		$description = $this->_('Line item discount value');
		// -----------
		// markup to target symbols for toggle show depending on whole order discount type
		// symbols are percentage (%) and currency, e.g. €
		// toggle classes

		// x-text='product.discountValue'
		//TODO bind or delete these ids as will not be unique!
		// 'x-bind:class' => $class
		$classPercentage = "{ pwcommerce_hide: product.discountType!==\"percentage\"}";
		$classFixed = "{ pwcommerce_hide: product.discountType!==\"fixed_applied_once\" && product.discountType!==\"fixed_applied_per_item\"}";

		// ----
		// line item fixed discount types
		$description .= "<span :class='{$classPercentage}'> (%)</span>";
		// line item percentage discount
		$description .= "<span :class='{$classFixed}'>{$this->shopCurrencySymbolString}</span>";
		$description .= ".";

		$options = [
			// 'id' => "pwcommerce_order_line_item_discount_value",
			// // TODO: not really needed!
			// 'name' => "pwcommerce_order_line_item_discount_value",
			'type' => 'number',
			'step' => '0.01',
			'min' => 0,
			'label' => $this->_('Discount Value'),
			'description' => $description,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			// doesn't work on load @see workaround with wrapAttr() below.
			//    'show_if' => 'pwcommerce_order_line_item_discount_type!=none',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_order_line_item_edit_discount',
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		$field->entityEncodeText = false;

		// ---------
		$field->attr([
			// 'x-model.number' => "{$xstore}.discountValue",
			// @note: processwire will strip out the '@' - so we use x-on:event instead
			//  '@change' => 'handleOrderLineItemDiscountChange($store.InputfieldPWCommerceOrderStore.edit_current_order_line_item_discount.discountValue)',
			// TODO MIGHT NEED TO CHANGE THIS?! WITH NEW GUI FOR LINE ITEM DISCOUNT!
			'x-model.number' => "product.discountValue",
			'x-on:input' => "handleOrderLineItemDiscountChange(product)",
			'x-bind:id' => '`pwcommerce_order_line_item_discount_value${product.id}`',
			'x-bind:name' => '`pwcommerce_order_line_item_discount_value${product.id}`',

		]);

		// TODO CHANGE THIS HIDDEN TO NOW APPLY TO VALUE ABOVE SO MAYBE XREF?!!!
		// set dynamic alpine JS class attribute for the element wrapping this Inputfield (discountValue getInputfieldText)
		// if discountType === 'none', we hide discountValue input
		// $class = "{ hidden: {$xstore}.discountType==\"none\"}";
		// apply hidden class to discount value if product discount type is empty or none
		$class = "{ hidden: product.discountType==\"none\"||!product.discountType}";
		$field->wrapAttr(
			'x-bind:class',
			$class
		);
		$wrapper->add($field);

		//------------------- line item selected discount type + net price after discount is applied info markup (getInputfieldMarkup)
		$fixedDiscountAppliedPerItemInfo = $this->_('will be taken off each item in this line item');
		$infoText = "<p class='notes' x-show='product.discountType==\"fixed_applied_per_item\"'><span x-text='product.discountValue'></span> {$fixedDiscountAppliedPerItemInfo}.</p>";
		// $infoText .= "<hr>";
		// TODO: show unit/total price?
		// TODO delete if not in use; we now use inline edit so NET value is visible
		// $infoText .= $this->_('Line net price after this discount is applied');

		// @note: now checking the current edit discount directly
		$info = "<p class='my-1'>{$infoText}: <span class='font-bold' x-text='product.totalPriceDiscounted'></span></p>";
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			// 'value' => $info,
			'value' => $infoText,

		];

		// $field = $this->pwcommerce->getInputfieldMarkup($options);
		// $wrapper->add($field);

		// ------
		$out = $wrapper->render();
		return $out;
	}

	// TODO DELETE IF NOT IN USE

	public function getMarkupForEditOrderLineItemDiscountOLD() {
		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		$xstore = "{$this->xstore}.edit_current_order_line_item_discount";

		//------------------- line item sku  (getInputfieldMarkup)
		$skuText = $this->_('SKU');
		$sku = "<h4 class='mb-1'>{$skuText}: <span x-text='{$xstore}.productSKU'></span></h4>";
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $sku,

		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		$wrapper->add($field);

		//------------------- line item discount type (getInputfieldSelect)

		$selectOptions = [
			'none' => $this->_('None'),
			'percentage' => $this->_('Percentage'),
			'fixed_applied_once' => $this->_('Fixed (applied once)'),
			'fixed_applied_per_item' => $this->_('Fixed (applied per item)'),
		];

		// TODO: add dynamic notes with respect to above
		$options = [
			'id' => "pwcommerce_order_line_item_discount_type",
			// TODO: not really needed!
			'name' => 'pwcommerce_order_line_item_discount_type',
			// TODO: SKIP LABEL?
			'label' => $this->_('Discount Type'),
			//  'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_order_line_item_edit_discount',
			'columnWidth' => 50,
			'select_options' => $selectOptions,
		];

		$field = $this->pwcommerce->getInputfieldSelect($options);
		//$field->attr('x-model', "{$xstore}.discountType");
		$field->attr([
			'x-model' => "{$xstore}.discountType",
			'x-on:change' => "handleOrderLineItemDiscountChange",
		]);
		$wrapper->add($field);

		//------------------- line item discount value (getInputfieldText)

		$options = [
			'id' => "pwcommerce_order_line_item_discount_value",
			// TODO: not really needed!
			'name' => "pwcommerce_order_line_item_discount_value",
			'type' => 'number',
			'step' => '0.01',
			'min' => 0,
			'label' => $this->_('Discount Value'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			// doesn't work on load @see workaround with wrapAttr() below.
			//    'show_if' => 'pwcommerce_order_line_item_discount_type!=none',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_order_line_item_edit_discount',
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		$field->attr([
			'x-model.number' => "{$xstore}.discountValue",
			// @note: processwire will strip out the '@' - so we use x-on:event instead
			//  '@change' => 'handleOrderLineItemDiscountChange($store.InputfieldPWCommerceOrderStore.edit_current_order_line_item_discount.discountValue)',
			'x-on:input' => "handleOrderLineItemDiscountChange",

		]);

		// set dynamic alpine JS class attribute for the element wrapping this Inputfield (discountValue getInputfieldText)
		// if discountType === 'none', we hide discountValue input
		$class = "{ hidden: {$xstore}.discountType==\"none\"}";
		$field->wrapAttr(
			'x-bind:class',
			$class
		);
		$wrapper->add($field);

		//------------------- line item selected discount type + net price after discount is applied info markup (getInputfieldMarkup)
		$fixedDiscountAppliedPerItemInfo = $this->_('will be taken off each item in this line item');
		$infoText = "<p class='notes' x-show='{$xstore}.discountType==\"fixed_applied_per_item\"'><span x-text='{$xstore}.discountValue'></span> {$fixedDiscountAppliedPerItemInfo}.</p>";
		$infoText .= "<hr>";
		// TODO: show unit/total price?
		// TODO delete if not in use; we now use inline edit so NET value is visible
		// $infoText .= $this->_('Line net price after this discount is applied');

		// @note: now checking the current edit discount directly
		$info = "<p class='my-1'>{$infoText}: <span class='font-bold' x-text='{$xstore}.totalPriceDiscounted'></span></p>";
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $info,

		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		$wrapper->add($field);

		// ------
		$out = $wrapper->render();
		return $out;
	}

	private function getMainOrderDiscountsSummaryMarkup() {

		// TODO THIS NOW CHANGES FOR THE MAIN ORDER DISCOUNT; NO NEED FOR MODAL! JUST WORK IN TEXT!!!

		$xstore = $this->xstore;
		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";
		$xstoreClient = $this->xstoreClient;

		// $addDiscountNote = $this->_('Add order discount'); // TODO not in use for now
		// $editDiscountNote = $this->_('Edit order discount'); // TODO not in use for now
		$applyOrderDiscountNote = $this->_('Apply order discount');
		$fixedAppliedOnceDiscountSubNote = $this->_('fixed discount, applied once');
		$fixedAppliedPerItemDiscountSubNote = $this->_('fixed discount, applied per item');
		// $percentageDiscountSubNote = $this->_('discount');

		$editOrderDiscountMarkup = $this->getMarkupForEditOrderMainDiscount();

		//---------------
		// TODO DELETE IF NO LONGER IN USE!
		// @note: conditionally show add/edit line item discount link
		// $editDiscountAccordion =

		// 	"<a class='block' @click='handleEditWholeOrderDiscount'>" .
		// 	// "<template x-if='!{$xstoreOrderWholeData}.discountValue'><span>{$addDiscountNote}</span></template>" .
		// 	// "<template x-if='{$xstoreOrderWholeData}.discountValue'><span>{$editDiscountNote}</span></template>" .
		// 	"<span>{$applyOrderDiscountNote}</span>" .
		// 	"</a>" .
		// 	$editOrderDiscountMarkup;

		// --------
		$editDiscountAccordion =
			"<div x-data='{selected:null}'>" .
			"<a class='block mb-5' @click='selected !== 1 ? selected = 1 : selected = null'><span>{$applyOrderDiscountNote}</span></a>" .
			"<div class='text-sm px-0 m-0 relative overflow-hidden transition-all max-h-0 duration-700'
			x-ref='container' x-bind:style='selected == 1 ? `max-height: ` + \$refs.container.scrollHeight + `px` : ``'
			aria-hidden='false'>" .
			$editOrderDiscountMarkup .
			"</div>" .
			"</div>";

		//----------------
		$out =
			"<div>" .
			// "<h4>Discounts</h4>" .
			// for discount items .
			"<div class='mb-10'>" .
			// whole order discounts  TODO @SEE NOTE ABOVE WIP!
			"<div>" .
			// ---------
			$editDiscountAccordion .
			$this->_('Whole order discount') .
			// TODO DELETE THIS OLD MARKUP
			// "</div>" .
			// line items discounts total
			// TODO: NEED TO CHECK IF % OR FIXED AS WELL!
			// "<div>" .
			// fixed discount: currency
			// TODO: delete when done
			// "<template x-if='{$xstoreOrderWholeData}.discountType==\"fixed_applied_once\"||{$xstoreOrderWholeData}.discountType==\"fixed_applied_per_item\"'><span></span></template>" .
			// "<template x-if='{$xstoreOrderWholeData}.discountType==\"fixed_applied_once\"||{$xstoreOrderWholeData}.discountType==\"fixed_applied_per_item\"'><span class='ml-1 font-bold' x-text='formatValueAsCurrency({$xstoreOrderWholeData}.discountValue)'></span></template>" .
			// // whole order discount value
			// // "<span class='ml-1 font-bold' x-text='formatValueAsCurrency({$xstoreOrderWholeData}.discountValue)'></span>" .
			// // fixed discount: currency as suffix = TODO! FOR FUTURE
			// //   "<template x-if='!{$xstoreOrderWholeData}.discountValue'><span></span></template>" .
			// // percentage discount (suffix)
			// "<template x-if='{$xstoreOrderWholeData}.discountType==\"percentage\"'><span>%</span></template>" .
			// "<template x-if='{$xstoreOrderWholeData}.discountType==\"fixed_applied_once\"'><span> {$fixedAppliedOnceDiscountSubNote}</span></template>" .
			// "<template x-if='{$xstoreOrderWholeData}.discountType==\"fixed_applied_per_item\"'><span> {$fixedAppliedPerItemDiscountSubNote}</span></template>" .
			// TODO DELETE ABOVE WHEN DONE
			// ++++++++++
			// percentage discount
			"<template x-if='{$xstoreOrderWholeData}.discountType==\"percentage\"'><span class='ml-1 font-bold' x-text='`\${{$xstoreOrderWholeData}.discountValue}%`'></span></template>" .
			// fixed applied once discount
			"<template x-if='{$xstoreOrderWholeData}.discountType==\"fixed_applied_once\"'><span><span class='ml-1 font-bold' x-text='formatValueAsCurrency(`\${{$xstoreOrderWholeData}.discountValue}`)'></span> {$fixedAppliedOnceDiscountSubNote}</span></template>" .
			// fixed applied per item discount
			"<template x-if='{$xstoreOrderWholeData}.discountType==\"fixed_applied_per_item\"'><span><span class='ml-1 font-bold' x-text='formatValueAsCurrency(`\${{$xstoreOrderWholeData}.discountValue}`)'></span> {$fixedAppliedPerItemDiscountSubNote}</span></template>" .
			// ----------
			"</div>" .
			// TODO: rephrase?
			// TODO DELETE THIS OLD MARKUP
			// "<div>" . $this->_('Line items discounts') . "</div>" .
			"<div class='mt-1'>" . $this->_('Line items discounts') .
			// *** all discounts total ***
			// line items total discount amount
			// TODO DELETE THIS OLD MARKUP
			// "<div>" .
			"<span class='ml-1 font-bold' x-text='formatValueAsCurrency({$xstoreClient}.client_total_line_items_discount_amount)'></span>" .
			"</div>" .
			// TODO DELETE THIS OLD MARKUP
			// "<div>" . $this->_('Discounts total') . "</div>" .
			"<div id='pwcommerce_order_discounts_total_wrapper' class='mt-3'>" . $this->_('Discounts total') .
			// line items total discount amount + whole order discount amount (overall discount)
			// TODO DELETE THIS OLD MARKUP
			// "<div>" .
			"<span class='ml-1 font-bold' x-text='formatValueAsCurrency({$xstoreClient}.client_order_plus_line_items_discount_amount)'></span>" .
			"</div>" .
			// --------
			"</div>" .
			// end discounts box
			"</div>";

		// -----------
		$out .= $this->renderEditModals();

		return $out;
	}

	// markup for whole order add/edit discount
	private function getMarkupForEditOrderMainDiscount() {

		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		// ==================
		//------------------- order discount type (getInputfieldSelect)
		$field = $this->getMarkupForEditOrderMainDiscountTypeSelectField();
		$wrapper->add($field);
		//------------------- order discount value (getInputfieldText)
		$field = $this->getMarkupForEditOrderMainDiscountValueTextField();
		$wrapper->add($field);
		//------------------- order selected discount type + order net price after discount is applied info markup (getInputfieldMarkup)
		$field = $this->getMarkupForEditOrderMainDiscountInfoMarkupField();
		$wrapper->add($field);
		// ------
		$out = $wrapper->render();
		return $out;

		// TODO DELETE BELOW AS NO LONGER USING MODAL

		//--------------
		$header = $this->_("Order Discount");
		$body = $wrapper->render();

		$applyButton = $this->pwcommerce->getModalActionButton(['x-on:click' => 'updateWholeOrderDiscount'], 'apply');
		$cancelButton = $this->pwcommerce->getModalActionButton(['x-on:click' => 'resetEditWholeOrderDiscountAndClose'], 'cancel');
		$footer = "<div class='ui-dialog-buttonset'>{$applyButton}{$cancelButton}</div>";
		$xproperty = 'is_edit_whole_order_discount_modal_open';
		$size = '5x-large';

		// wrap content in modal for adding/editing whole order's discount
		// modal options
		$options = [
			// $header The modal title pane markup.
			'header' => $header,
			// $body The main content markup.
			'body' => $body,
			// $footer The footer markup.
			'footer' => $footer,
			// $xstore The alpinejs store with the property that will be modelled to show/hide the modal.
			'xstore' => $this->xstoreOrder,
			// $xproperty The alpinejs property that will be modelled to show/hide the modal.
			'xproperty' => $xproperty,
			// $size The size of the modal requested.
			'size' => $size,
		];
		$out = $this->pwcommerce->getModalMarkup($options);
		return $out;
	}

	private function getMarkupForEditOrderMainDiscountTypeSelectField() {
		// TODO RENAME NAME, ETC BELOW AS WE NOW MODEL THE OBJECT DIRECTLY! NOT IN A MODAL!
		// TODO confirm $xstore!
		// @note: models value for modal edit only!
		//------------------- order discount type (getInputfieldSelect)
		//$xstore = "{$this->xstore}.edit_whole_order_discount";
		// TODO DELETE WHEN DONE; WE MODEL DIRECT OBJECT!
		$xstoreEditWholeOrderDiscount = "{$this->xstore}.edit_whole_order_discount";
		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";

		$selectOptions = [
			'none' => $this->_('None'),
			'percentage' => $this->_('Percentage'),
			'fixed_applied_once' => $this->_('Fixed (applied once)'),
			'fixed_applied_per_item' => $this->_('Fixed (applied per line item)'),
		];

		$options = [
			'id' => "pwcommerce_order_discount_type",
			// TODO: not really needed!
			'name' => 'pwcommerce_order_discount_type',
			// TODO: SKIP LABEL?
			'label' => $this->_('Discount Type'),
			//  'skipLabel' => Inputfield::skipLabelHeader,
			'description' => $this->_('Whole order discount type.'),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_order_edit_discount',
			'columnWidth' => 50,
			'select_options' => $selectOptions,
		];

		$field = $this->pwcommerce->getInputfieldSelect($options);
		// $field->attr('x-model', "{$xstore}.discountType");
		$field->attr([
			// TODO DELETE WHEN DONE; WE MODEL DIRECT OBJECT!
			// 'x-model' => "{$xstoreEditWholeOrderDiscount}.discountType",
			'x-model' => "{$xstoreOrderWholeData}.discountType",
			'x-on:change' => "handleEditWholeOrderDiscountChange",
		]);

		return $field;
	}
	private function getMarkupForEditOrderMainDiscountValueTextField() {

		// TODO RENAME NAME, ETC BELOW AS WE NOW MODEL THE OBJECT DIRECTLY! NOT IN A MODAL!
		// TODO confirm $xstore!
		// @note: models value for modal edit only!
		//------------------- order discount value (getInputfieldText)
		// $xstore = "{$this->xstore}.edit_whole_order_discount";
		// TODO DELETE WHEN DONE; WE MODEL DIRECT OBJECT!
		$xstoreEditWholeOrderDiscount = "{$this->xstore}.edit_whole_order_discount";
		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";

		$description = $this->_('Discount value for selected whole order discount type');
		// -----------
		// markup to target symbols for toggle show depending on whole order discount type
		// symbols are percentage (%) and currency, e.g. €
		// toggle classes
		$wholeOrderDiscountValuePercentageClass = $this->order->discountType === 'percentage' ? '' :
			" class='pwcommerce_hide'";
		$wholeOrderDiscountValueCurrencyClass = in_array($this->order->discountType, ['fixed_applied_once', 'fixed_applied_per_item']) ? '' : " class='pwcommerce_hide'";
		$description .= "<span id='pwcommerce_order_discount_value_percent_symbol'{$wholeOrderDiscountValuePercentageClass}> (%)</span>";
		$description .= "<span id='pwcommerce_order_discount_value_currency_symbol'{$wholeOrderDiscountValueCurrencyClass}>{$this->shopCurrencySymbolString}</span>";
		$description .= ".";

		$options = [
			'id' => "pwcommerce_order_discount_value",
			// TODO: not really needed!
			'name' => "pwcommerce_order_discount_value",
			'type' => 'number',
			'step' => '0.01',
			'min' => 0,
			'label' => $this->_('Discount Value'),
			'description' => $description,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			// doesn't work on load @see workaround with wrapAttr() below.
			//    'show_if' => 'pwcommerce_order_discount_type!=none',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_order_edit_discount',
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		// allow HTML in description
		$field->entityEncodeText = false;
		$field->attr([
			// TODO DELETE WHEN DONE; WE MODEL DIRECT OBJECT!
			// 'x-model.number' => "{$xstoreEditWholeOrderDiscount}.discountValue",
			// 'x-model.number' => "{$xstoreOrderWholeData}.discountValue",
			'x-model' => "{$xstoreOrderWholeData}.discountValue",
			'x-on:input' => "handleEditWholeOrderDiscountChange",
		]);

		// set dynamic alpine JS class attribute for the element wrapping this Inputfield (discountValue getInputfieldText)
		// if discountType === 'none', we hide discountValue input
		// TODO DELETE WHEN DONE; WE MODEL DIRECT OBJECT!
		// $class = "{ hidden: {$xstoreEditWholeOrderDiscount}.discountType==\"none\"}";
		$class = "{ hidden: {$xstoreOrderWholeData}.discountType==\"none\" || !{$xstoreOrderWholeData}.discountType}";
		$field->wrapAttr(
			'x-bind:class',
			$class,
		);

		return $field;
	}

	private function getMarkupForEditOrderMainDiscountInfoMarkupField() {
		// TODO confirm $xstore!
		//------------------- order selected discount type + order net price after discount is applied info markup (getInputfieldMarkup)
		// TODO DELETE WHEN DONE; WE MODEL DIRECT OBJECT!
		$xstore = "{$this->xstore}.edit_whole_order_discount";
		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";

		$fixedDiscountAppliedPerItemInfo = $this->_('will be taken off each line item');
		$infoText = "<p class='notes' x-show='{$xstoreOrderWholeData}.discountType==\"fixed_applied_per_item\"'><span x-text='formatValueAsCurrency({$xstoreOrderWholeData}.discountValue)'></span> {$fixedDiscountAppliedPerItemInfo}.</p>";
		$infoText .= "<hr>";
		// TODO: show TOTAL ORDER  price?
		$infoText .= $this->_('Order net price after this discount is applied');
		// @note: 'getOrderSubtotalWithoutShippingOrTaxes' will be calculated on the fly!
		$info = "<p class='my-1'>{$infoText}: <span class='font-bold' x-text='formatValueAsCurrency(getOrderSubtotalWithoutShippingOrTaxes())'></span><span></span></p>";
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $info,

		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		return $field;
	}
}