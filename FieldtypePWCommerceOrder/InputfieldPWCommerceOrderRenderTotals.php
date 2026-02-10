<?php

namespace ProcessWire;

/**
 * PWCommerce: InputfieldPWCommerceOrder -> InputfieldPWCommerceOrderRenderTotals
 *
 * Helper render class for InputfieldPWCommerceOrder.
 * For displaying order taxes and totals.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceOrderRenderTotals for PWCommerce
 * Copyright (C) 2022 by Francis Otieno
 * MIT License
 *
 */



class InputfieldPWCommerceOrderRenderTotals extends WireData
{

	// =============
	protected $page;
	private $order; // the order


	// ----------
	private $xstoreOrder; // the alpinejs store used by this inputfield.
	private $xstore; // the full prefix to the alpine store used by this inputfield
	private $xstoreClient; // shortcut to the client object in the alpinejs store in this inputfield.
	private $ajaxPostURL;


	/**
	 *   construct.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	public function __construct($page) {

		$this->page = $page;




		// ==================
		$this->xstoreOrder = 'InputfieldPWCommerceOrderStore';
		// i.e., '$store.InputfieldPWCommerceOrderStore'
		$this->xstore = "\$store.{$this->xstoreOrder}";
		// @note: even client-only data lives under 'order_whole_data'
		$this->xstoreClient = "{$this->xstore}.order_whole_data.client";
		$this->ajaxPostURL = $this->wire('config')->urls->admin . PwCommerce::PWCOMMERCE_SHOP_PAGE_IN_ADMIN_NAME . '/ajax/';
	}

	/**
	 * Render the input area for order taxes and totals
	 *
	 * @param WireData $order
	 * @return mixed
	 */
	public function ___render(WireData $order) {
		$this->order = $order;

		return $this->getOrderTotalsMarkup();
	}

	/**
	 * Get Order Totals Markup.
	 *
	 * @return mixed
	 */
	private function getOrderTotalsMarkup() {
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		// checkbox for 'use custom shipping' fee
		$wrapper->add($this->getCheckboxForOrderDoNotChargeTaxes());
		// order totals breakdown
		$wrapper->add($this->getMarkupForOrderTotalsBreakdown());
		return $wrapper->render();
	}

	/**
	 * Get Checkbox For Order Do Not Charge Taxes.
	 *
	 * @return mixed
	 */
	private function getCheckboxForOrderDoNotChargeTaxes() {
		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";

		// is_charge_taxes_manual_exemption
		$description = $this->_("Taxes are calculated automatically. Tick this box if you do not want taxes applied to the order.");
		$notes = $this->_("Please not that if the customer is tax exempt the setting here will have no effect.");
		$options = [
			'id' => "pwcommerce_order_apply_manual_tax_exemption",
			'name' => "pwcommerce_order_apply_manual_tax_exemption",
			'label' => $this->_('Tax exemption'),
			'label2' => $this->_('Do not charge taxes on this order'),
			'description' => $description,
			'notes' => $notes,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_manual_order',
			'checked' => empty($this->order->isChargeTaxesManualExemption) ? false : true,
			// 'value' => 1
		];

		$field = $this->pwcommerce->getInputfieldCheckbox($options);

		$field->attr([
			'x-model' => "{$xstoreOrderWholeData}.isChargeTaxesManualExemption",
			'x-on:change' => 'handleIsChargeTaxesManualExemptionChange',
		]);

		// -------
		return $field;
	}

	/**
	 * Get Markup For Order Totals Breakdown.
	 *
	 * @return mixed
	 */
	private function getMarkupForOrderTotalsBreakdown() {
		// $order = $this->order;
		// $xstoreOrderWholeData = "{$this->xstore}.order_whole_data";
		$xstore = $this->xstore;

		// ----------

		$out =
			"<hr>" .
			"<h4 id='pwcommerce_order_totals_breakdown_headline'>" . $this->_('Totals') . "</h4>" .
			// TODO NOT IN USE FOR NOW
			// shipping and handling totals $order->orderShippingFeePlusHandlingFeeAmountTotal
			// "<p><span class='mr-1'>" . $this->_('Shipping and Handling Total') . "</span>" .
			// "<span class='font-bold' x-text='formatValueAsCurrency({$xstoreOrderWholeData}.orderShippingFeePlusHandlingFeeAmountTotal)'></span>" .
			// "</p>" .
			// --------------
			// TODO for now we display subtotal instead? this is because we might not have added selected shipping rate so we don't know how much tax is charged on that. ok? In addition we need to check if order was taxable, etc! or just add note? just release like this then refine!
			// taxes $order->orderTaxAmountTotal
			// "<p><span class='mr-1'>" . $this->_('Taxes') . "</span>" .
			// "<span class='font-bold' x-text='formatValueAsCurrency({$xstoreOrderWholeData}.orderTaxAmountTotal)'></span>" .
			// "</p>" .
			// @note: we modal 'temporary_subtotal_price' to avoid infinite loop -> @see notes in JS
			"<p><span class='mr-1'>" . $this->_('Subtotal Price') . "</span>" .
			"<span class='font-bold' x-text='formatValueAsCurrency({$xstore}.temporary_subtotal_price)'></span>" .
			"</p>" .
			// --------------
			// @note: we modal 'temporary_subtotal_price' to avoid infinite loop -> @see notes in JS
			// order total $order->totalPrice
			"<p><span class='mr-1'>" . $this->_('Total') . "</span>" .
			"<span class='font-bold' x-text='formatValueAsCurrency({$xstore}.temporary_total_price)'></span>" .
			"</p>";

		// TODO REVISIT? E.G. LIVE VALUES WITH MULTIPLE SHIPPING RATES?
		$notes = $this->_('Please note that total displayed may not include shipping fee until you have saved your order. Taxes will not be shown until after the order has been saved.');

		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'notes' => $notes,
			'wrapClass' => true,
			'classes' => 'pwcommerce_manual_order',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// -----
		return $field;
	}

	################

	// TODO MOVE TO TOTALS SECTION!
	/**
	 * Get Order Subtotal Summary Markup.
	 *
	 * @return mixed
	 */
	private function getOrderSubtotalSummaryMarkup() {

		$xstoreClient = $this->xstoreClient;
		// TODO: ADD ALPINE JS ATTRIBUTES
		$out =
			"<div>" .
			"<h5>" . $this->_('Subtotal') . "</h5>" .
			"<div>" .
			// TODO: show without discounts? if not, DELETE WHEN DONE??
			"<span x-text='formatValueAsCurrency({$xstoreClient}.client_order_total_cost_without_discounts_shipping_or_taxes)'></span>" .
			"<small class='block'>" . $this->_('before discounts applied') . "</small>" .
			"<span x-text='formatValueAsCurrency({$xstoreClient}.client_subtotal_after_discounts_applied_without_shipping_and_taxes_amount)' class='mt-1 block'></span>" .
			"<small class='block'>" . $this->_('after all discounts applied') . "</small>" .
			"</div>" .
			"</div>";

		return $out;
	}

	/**
	 * Get Order Taxes Summary Markup.
	 *
	 * @return mixed
	 */
	private function getOrderTaxesSummaryMarkup() {

		$xstoreClient = $this->xstoreClient;
		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";

		// MAIN MARKUP
		$out =
			"<div>" .
			// <h4>Taxes</h4> -->
			"<div class='grid grid-cols-2 gap-4 mb-10'>" .
			// enable/disable taxes -->
			// TODO: may make more customisable in future
			// TODO: NEED HTMX HERE TO GET CALCULATIONS FROM SERVER ON CHANGE, INIT?
			"<div class='col-span-full lg:col-span-1'><a @click='handleEditWholeOrderTaxes' class='block'>" . $this->_('Taxes') . "</a></div>" .
			"<div class='col-span-full lg:col-span-1'>" .
			// TODO SORT OUT CURRENCY PREFIXES BELOW!
			// TODO: TWO CHOICES: FORMAT FROM SERVER AND SEND FORMATTED VALUES TO JS; OR SEND LOCALE TO CLIENT TO FORMAT THE VALUES!
			// TODO: MIGHT HAVE TO DO IT CLIENT SIDE SINCE VALUES ARE DYNAMIC AND CHANGE ON THE CLIENT! SO, NEED TO SORT IT OUT ON CLIENT!
			// TODO GET VALUE HERE!!!! HTMX??
			// TODO DELETE WHEN DONE
			// "<span x-text='formatValueAsCurrency({$xstoreClient}.client_order_total_taxes_amount)'></span>" .
			// TODO here, we need to show grayed out or similar or show text saying will refresh this tax total on save!
			"<span x-text='formatValueAsCurrency({$xstoreOrderWholeData}.orderTaxAmountTotal)'></span>" .
			"<p id='pwcommerce_order_calculated_taxes_amount'></p>" .
			"</div>" .
			"</div>" .
			"</div>";

		return $out;
	}

	/**
	 * Get Order Total Summary Markup.
	 *
	 * @return mixed
	 */
	private function getOrderTotalSummaryMarkup() {
		// TODO SORT OUT CURRENCY PREFIX BELOW!
		$xstoreOrderWholeData = "{$this->xstore}.order_whole_data";
		// TODO: ADD ALPINE JS ATTRIBUTES
		// TODO: show calculate here as well or instead?
		$out =
			"<div>" .
			//  TODO: LANGUAGE STRINGS -->
			"<h5>" . $this->_('Order Total') . "</h5>" .
			"<div>" .
			"<span x-text='formatValueAsCurrency({$xstoreOrderWholeData}.totalPrice)'></span>" .
			"</div>" .
			"</div>";

		return $out;
	}

	// TODO AMEND/DELETE IF NO LONGER IN USE
	/**
	 * Get Main Order Calculate Shipping And Taxes Markup.
	 *
	 * @return mixed
	 */
	private function getMainOrderCalculateShippingAndTaxesMarkup() {
		// TODO - WORK ON 'SAVE TO CALCULATE SHIPPING and TAXES' after changes made to order
		$xstore = $this->xstore;
		$ajaxPostURL = $this->ajaxPostURL;
		$calculateShippingAndTaxesParameterJSON = json_encode(['pwcommerce_calculate_shipping_and_taxes' => 1]);
		$indicator = "htmx-indicator";
		$out =
			// @note: full col-span!
			"<div id='pwcommerce_order_calculate_shipping_wrapper' class='mb-5'>" .
			"<span class='hidden' :class='{hidden: !{$xstore}.is_show_calculate_shipping_link}'>" .
			"<a id='pwcommerce_order_calculate_shipping' hx-post='$ajaxPostURL' hx-vals='$calculateShippingAndTaxesParameterJSON' hx-indicator='.{$indicator}'  class='py-1 px-3 rounded-sm'>" .
			"<small class='inline-block'>" . $this->_('Calculate shipping and taxes') . "</small>" .
			"</a>" .
			"<span class='{$indicator} fa fa-fw fa-spin fa-spinner'></span>" .
			"</span>" .
			// grayed-out text to signal inactive but shipping and taxes will need calculating
			"<span id='pwcommerce_order_calculate_shipping_inactive' class='py-1 px-3 rounded-sm opacity-50 cursor-not-allowed' :class='{hidden: {$xstore}.is_show_calculate_shipping_link}'>" .
			"<small class='inline-block'>" . $this->_('Calculate shipping and taxes') . "</small>" .
			"</span>" .
			"</div>";

		// ---------
		return $out;
	}

	## ~~~~~~~~~~~~~~~~~~~

}