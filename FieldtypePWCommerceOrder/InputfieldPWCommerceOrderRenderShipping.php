<?php

namespace ProcessWire;

/**
 * PWCommerce: InputfieldPWCommerceOrder -> InputfieldPWCommerceOrderRenderShipping
 *
 * Helper render class for InputfieldPWCommerceOrder.
 * For displaying order shipping and handling.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceOrderRenderShipping for PWCommerce
 * Copyright (C) 2022 by Francis Otieno
 * MIT License
 *
 */


class InputfieldPWCommerceOrderRenderShipping extends WireData
{



	// =============
	protected $page;
	private $order; // the order


	// ----------
	private $xstoreOrder; // the alpinejs store used by this inputfield.
	private $xstore; // the full prefix to the alpine store used by this inputfield
	private $xstoreClient; // shortcut to the client object in the alpinejs store in this inputfield.
	private $ajaxPostURL;

	// ----------
	private $shippingCountry;
	// -----
	private $shopCurrencySymbolString = "";



	public function __construct($page) {

		$this->page = $page;
		// TODO DELETE AS NEEDED
		// parent::init();
		// if we want this modules css and js classes to be autoloaded
		// Any modules that extend: Inputfield, Process or ModuleJS will auto-load their CSS/JS files if they have the same name as the module and appear in the same directory. However, in order for that to work, their init() method has to be called. So if your module extends one of those, and has an init() method, then make sure to call the parent init() method:




		// ==================
		$this->xstoreOrder = 'InputfieldPWCommerceOrderStore';
		// i.e., '$store.InputfieldPWCommerceOrderStore'
		$this->xstore = "\$store.{$this->xstoreOrder}";
		// @note: even client-only data lives under 'order_whole_data'
		$this->xstoreClient = "{$this->xstore}.order_whole_data.client";
		$this->ajaxPostURL = $this->wire('config')->urls->admin . PwCommerce::PWCOMMERCE_SHOP_PAGE_IN_ADMIN_NAME . '/ajax/';
	}

	/**
	 * Render the input area for order shipping
	 *
	 */
	public function ___render(WireData $order) {
		$this->order = $order;

		// if currency locale set..
		// grab symbol; we use on price fields description
		$shopCurrencySymbolString = $this->pwcommerce->renderShopCurrencySymbolString();
		if (strlen($shopCurrencySymbolString)) {
			$this->shopCurrencySymbolString = " " . $shopCurrencySymbolString;
		}
		// -------------
		return $this->getOrderShippingSummaryMarkup();
	}

	private function getOrderShippingSummaryMarkup() {

		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		// TODO BELOW NOW CHANGES!!!! NO MODAL FOR SHIPPING NOW! JUST TOGGLE BOXES MAYBE + LINK/BUTTON TO RECALCULATE SHIPPING AND RESULTS APPEAR BELOW USING HTMX + NEED TO ADD INFO HERE OR APPEARING AT TOP OF LINE ITEMS THAT SHIPPING NEEDS RECALCULATION - IF OUR USUAL CHANGES OCCUR!!!

		$xstore = $this->xstore;
		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";
		$xstoreClient = $this->xstoreClient;

		// #############

		// TODO: ADD ALPINE JS ATTRIBUTES
		// TODO: delete if not in use
		$noHandlingFeeNote = $this->_('None');

		// TODO: AMEND BELOW COMMENTS + LOGIC + MARKUP -> NO MORE MODAL HERE!

		// ## FOR NON-MODAL MARKUP - SHIPPING SUMMARY ##
		// TODO WIP!
		$out =

			"<div>" .

			//  <h4>Shipping</h4>
			//  inner grid for shipping items
			//  TODO: need to show this if at least one physical product that requires shipping is in the order
			//  TODO: ADD SHIPPING SUMMARIES STUFF  Calculations based on matched shipping zone, hence address has to be added.
			"<div class='mb-10'>" .
			//  add shipping
			// @note: we listen to click event to set and open order shipping modal. We also listen to a custom event sent after 'calculate shipping' link clicked
			"<div><a @click='handleEditOrderShipping' class='block'>" . $this->_('Edit shipping') . "</a></div>" .
			// shipping (fee) amount
			"<div>" .
			"<span x-text='formatValueAsCurrency({$xstoreOrderWholeData}.shippingFee)'></span>" .
			"</div>" .
			//  handling fee
			"<div><span>" . $this->_('Handling fee') . "</span></div>" .
			"<div>" .
			// handling fee amount
			// TODO: handling fee type currency PREFIX
			//   "<span x-text='{$xstoreOrderWholeData}.handlingFeeAmount'></span>" .
			// @note: we  SHOW THE HANDLING FEE VALUE and NOT the HANDLING FEE AMOUNT!
			// the amount will be monitored internally and added to final totals.
			"<span x-text='{$xstoreOrderWholeData}.handlingFeeValue'></span>" .
			// hanlding fee type is percent SUFFIX
			"<template x-if='{$xstoreOrderWholeData}.handlingFeeType==\"percentage\"'><span>%</span></template>" .
			"</div>" .
			// TODO here, we need to show grayed out or similar or show text saying will refresh this total shipping on save!
			//  all shipping total
			"<div>" . $this->_('Shipping total') . "</div>" .
			"<div>" .
			"<span x-text='formatValueAsCurrency({$xstoreOrderWholeData}.orderShippingFeePlusHandlingFeeAmountTotal)'></span>" .
			"</div>" .
			//------------
			"</div>" .
			//  end shipping box
			"</div>";

		// -------------
		// $out = $button->render() . $checkboxCustomHandling->render() . $textInputCustomHandling->render();

		// -------------

		// calculate shipping and taxes information
		$wrapper->add($this->getInfoForOrderCalculateShipping());

		// $button = $this->getButtonForOrderCalculateShipping();
		// $infoForOrderShippingZoneCalculatedHandlingFee = $this->getCheckboxForOrderUseCustomHandling();
		// $checkboxCustomHandling = $this->getCheckboxForOrderUseCustomHandling();
		// $textInputCustomHandling = $this->getTextInputForOrderCustomHandling();

		// calculate shipping and taxes button
		$wrapper->add($this->getButtonForOrderCalculateShipping());
		// info that shipping and taxes button is disabled
		$wrapper->add($this->getInfoForOrderCalculateShippingButtonDisabled());
		// area to display matched shipping rates
		$wrapper->add($this->getAreaForDisplayingMatchedShippingRates());

		// HANDLING FEE
		// info and target for calculated shipping zone handling fee
		$wrapper->add($this->getInfoForOrderShippingZoneCalculatedHandlingFee());
		// checkbox for 'use custom handling' fee
		$wrapper->add($this->getCheckboxForOrderUseCustomHandlingFee());
		// input for custom handling fee
		$wrapper->add($this->getTextInputForOrderCustomHandlingFee());
		// SHIPPING FEE
		// info and target for calculated shipping zone shipping fee
		$wrapper->add($this->getInfoForOrderShippingZoneCalculatedShippingFee());
		// checkbox for 'use custom shipping' fee
		$wrapper->add($this->getCheckboxForOrderUseCustomShippingFee());
		// input for custom shipping fee
		$wrapper->add($this->getTextInputForOrderCustomShippingFee());

		return $wrapper->render();
	}

	private function getButtonForOrderCalculateShipping() {

		//------------------- calculate shipping (getInputfieldButton)
		// TODO -> NEED TO MOVE TO ELSEWHERE?
		$label = $this->_('Calculate shipping and taxes');
		$options = [
			'id' => "pwcommerce_order_calculate_shipping_button",
			'name' => "pwcommerce_order_calculate_shipping_button",
			'label' => $label,
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => $columnWidth,
			'small' => true,
			// TODO: ok?
			'secondary' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline mb-5',
			'notes' => $this->getSpinnerForOrderCalculateShipping(),
		];

		$field = $this->pwcommerce->getInputfieldButton($options);
		// allow html on notes and description
		$field->entityEncodeText = false;

		// add htmx stuff to button
		$ajaxPostURL = $this->ajaxPostURL;
		$calculateShippingAndTaxesParameterJSON =
			json_encode(['pwcommerce_calculate_shipping_and_taxes' => 1]);
		$indicator = "htmx-indicator";
		$field->attr([
			'hx-post' => $ajaxPostURL,
			'hx-vals' => $calculateShippingAndTaxesParameterJSON,
			'hx-indicator' => $indicator,
			'hx-trigger' => 'pwcommercecalculateshippingandtaxes',
			'hx-swap' => 'none',
			// do not swap out this button
			// alpine js
			'x-on:click' => 'handleCalculateShippingAndTaxes'
		]);

		return $field;
	}

	private function getInfoForOrderCalculateShipping() {
		$description = $this->_("During order creation, in order to see the calculated shipping and handling for this order, please ensure that you have entered customer details including the country. You also need to add at least one product item to the order. You can then click on the button to calculate shipping. The button will not work if the above requirements are not met. Alternatively, save the order with the customer details entered and shipping and handling will also be calculated and saved. If you wish to use custom shipping and/or handling fees, please do so below.");
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'description' => $description,
			'wrapClass' => true,
			'classes' => 'pwcommerce_manual_order_shipping_and_handling_info',
			'wrapper_classes' => 'pwcommerce_no_outline',
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		// -----
		return $field;
	}

	private function getInfoForOrderCalculateShippingButtonDisabled() {
		$xstore = $this->xstore;
		$error = $this->_("You need to add at least one product item to this order and specify a shipping country in order for the 'Calculate shipping and taxes' button to work.");
		$out = "<p class='pb-5 pwcommerce_error hidden' :class='{hidden: !{$xstore}.is_show_recalculate_shipping_and_taxes_error}'>" . $error . "</p>";
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'classes' => 'pwcommerce_manual_order_shipping_and_handling_info',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		// -----
		return $field;
	}

	private function getAreaForDisplayingMatchedShippingRates() {

		// we will display matched shipping rate(s) or errors in this area/markup
		$out = "<div id='pwcommerce_order_matched_shipping_rates'><p class='notes'>" . $this->_('Matched shipping rates will be displayed here.') . "</p></div>";
		// ------------
		// append area for shipping rate calculation errors!
		// TODO POPULATE WITH HTMX? or alpine.js?! also needs hiding if not in use!
		// @update: now moved to server for htmx response. TODO: ok? @see: InputfieldPWCommerce:;getOrderMatchedShippingRatesMarkup
		// $out .= "<div><p id='pwcommerce_order_shipping_rates_calculation_error' class='pwcommerce_error pt-2.5'></p></div>";

		// -----------
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'classes' => 'pwcommerce_manual_order_shipping_and_handling_info',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		// -----
		return $field;
	}

	private function getSpinnerForOrderCalculateShipping() {
		$indicator = "htmx-indicator";
		$out = "<span class='{$indicator} fa fa-fw fa-spin fa-spinner'></span>";
		// ---------
		return $out;
	}

	private function getInfoForOrderShippingZoneCalculatedHandlingFee() {

		$xstore = $this->xstore;
		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";

		$shippingZoneHandlingFeeInfo =
			"<hr>" .
			"<h4 id='pwcommerce_order_shipping_zone_handling_fee_info_headline' class='mt-5'>" . $this->_('Handling') . "</h4>" .
			"<p id='pwcommerce_order_shipping_zone_handling_fee_info'  :class='{hidden: {$xstoreOrderWholeData}.isCustomHandlingFee}'>" .
			$this->_('The handling fee for the shipping zone is') .
			// TODO CHANGE THIS SO CAN TARGET USING HTMX? alternatively, get even in window from htmx then change the value of handling fee which will then trigger this
			"<span x-text=getShippingZoneCalculatedHandlingFee() class='ml-1'></span>" .
			"</p>";

		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'classes' => 'pwcommerce_order_add_new',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $shippingZoneHandlingFeeInfo,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// -----
		return $field;
	}

	private function getCheckboxForOrderUseCustomHandlingFee() {
		// TODO - CHECK CSS AND PADDING HERE! ADJACENT INPUT NOT SYMMETRIC
		// $xstoreOrderWholeData = "{$this->xstore}.order_whole_data";
		$description = $this->_("Check to use a custom handling fee instead of the one calculated from the matched order shipping zone.");
		$columnWidth = 50;
		$options = [
			'id' => "pwcommerce_order_use_custom_handling_fee",
			'name' => "pwcommerce_order_use_custom_handling_fee",
			'label' => $this->_('Custom Handling'),
			'label2' => $this->_('Use custom handling'),
			'description' => $description,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => $columnWidth,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_manual_order',
			'checked' => empty($this->order->isCustomHandlingFee) ? false : true,

		];

		$field = $this->pwcommerce->getInputfieldCheckbox($options);

		$field->attr([
			// 'x-model' => "{$xstoreOrderWholeData}.isCustomHandlingFee",
			'x-on:input' => 'handleUseCustomHandlingFeeChange',
		]);

		// -------
		return $field;
	}

	private function getTextInputForOrderCustomHandlingFee() {

		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";
		$columnWidth = 50;

		//------------------- order custom_handling_fee (getInputfieldText)
		$description = $this->_('Specify custom handling fee amount for order');
		// append currency symbol string if available
		$description .= $this->shopCurrencySymbolString . '.';
		$options = [
			'id' => "pwcommerce_order_custom_handling_fee",
			'name' => "pwcommerce_order_custom_handling_fee",
			'type' => 'number',
			'step' => '0.01',
			'min' => 0,
			'label' => $this->_('Custom Handling Fee'),
			'description' => $description,
			// 'notes' => $this->_('Optional'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => $columnWidth,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_manual_order',
			'show_if' => "pwcommerce_order_use_custom_handling_fee=1",
			// 'value' => $value->billingAddressMiddleName,
			// TODO ADD VALUE
		];

		$field = $this->pwcommerce->getInputfieldText($options);

		$field->attr([
			'x-model.number' => "{$xstoreOrderWholeData}.handlingFeeAmount",
			'x-on:input' => 'handleCustomHandlingFeeChange',
		]);
		// --------
		return $field;
	}

	// >>>>>>>>>>>>>

	private function getInfoForOrderShippingZoneCalculatedShippingFee() {

		$xstore = $this->xstore;
		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";

		$shippingZoneShippingFeeInfo =
			"<hr>" .
			"<h4>" . $this->_('Shipping') . "</h4>" .
			"<p id='pwcommerce_order_shipping_zone_shipping_fee_info'  :class='{hidden: {$xstoreOrderWholeData}.isCustomShippingFee}'>" .
			$this->_('The shipping fee for the shipping zone is') .
			// TODO CHANGE THIS SO CAN TARGET USING HTMX?
			"<span x-text=getShippingZoneCalculatedShippingFee() class='ml-1'></span>" .
			"</p>";
		// TODO: amend/rephrase?
		$notes = $this->_("If changes have been made to this order, the updated calculated shipping fee will be displayed after you save the order.");

		$shippingZoneShippingFeeInfo .= "<p class='notes' :class='{hidden: {$xstoreOrderWholeData}.isCustomShippingFee}'>" . $notes . "</p>";

		// -------
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			// 'notes' => $notes,
			'wrapClass' => true,
			'classes' => 'pwcommerce_order_add_new',
			// TODO: AMEND TO 'pwcommerce_order_add_products_to_order'
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $shippingZoneShippingFeeInfo,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// -----
		return $field;
	}

	private function getCheckboxForOrderUseCustomShippingFee() {

		// $xstoreOrderWholeData = "{$this->xstore}.order_whole_data";

		$description = $this->_("Check to use a custom shipping fee instead of the one calculated from the matched order shipping zone.");
		$columnWidth = 50;
		$options = [
			'id' => "pwcommerce_order_use_custom_shipping_fee",
			'name' => "pwcommerce_order_use_custom_shipping_fee",
			'label' => $this->_('Custom Shipping'),
			'label2' => $this->_('Use custom shipping'),
			'description' => $description,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => $columnWidth,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_manual_order',
			'checked' => empty($this->order->isCustomShippingFee) ? false : true,

		];

		$field = $this->pwcommerce->getInputfieldCheckbox($options);

		$field->attr([
			// 'x-model' => "{$xstoreOrderWholeData}.isCustomHandlingFee",
			'x-on:input' => 'handleUseCustomShippingFeeChange',
		]);
		// -------
		return $field;
	}

	private function getTextInputForOrderCustomShippingFee() {

		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";
		$columnWidth = 50;

		//------------------- order custom_shipping_fee (getInputfieldText)
		$description = $this->_('Specify custom shipping fee amount for order');
		// append currency symbol string if available
		$description .= $this->shopCurrencySymbolString . '.';
		$options = [
			'id' => "pwcommerce_order_custom_shipping_fee",
			'name' => "pwcommerce_order_custom_shipping_fee",
			'type' => 'number',
			'step' => '0.01',
			'min' => 0,
			'label' => $this->_('Custom Shipping Fee'),
			'description' => $description,
			// 'notes' => $this->_('Optional'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => $columnWidth,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_manual_order',
			'show_if' => "pwcommerce_order_use_custom_shipping_fee=1",
			// 'value' => $value->billingAddressMiddleName,
			// TODO ADD VALUE
		];

		$field = $this->pwcommerce->getInputfieldText($options);

		$field->attr([
			'x-model.number' => "{$xstoreOrderWholeData}.shippingFee",
			'x-on:input' => 'handleCustomShippingFeeChange',
		]);
		// --------
		return $field;
	}
}