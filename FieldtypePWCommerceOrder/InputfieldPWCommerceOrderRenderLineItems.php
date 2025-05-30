<?php

namespace ProcessWire;

/**
 * PWCommerce: InputfieldPWCommerceOrder -> InputfieldPWCommerceOrderRenderLineItems
 *
 * Helper render class for InputfieldPWCommerceOrder.
 * For displaying line items of order.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceOrderRenderLineItems for PWCommerce
 * Copyright (C) 2022 by Francis Otieno
 * MIT License
 *
 */


class InputfieldPWCommerceOrderRenderLineItems extends WireData
{


	// =============
	protected $page;
	protected $field;

	private $inputfieldOrderLineItem;



	// =============

	public function __construct($page) {

		$this->page = $page;


		// ----------
		$this->setInputfieldOrderLineItem();
	}

	/**
	 * Get and set InputfieldPWCommerceOrderLineItem to a class property.
	 *
	 * For convenience / reuse.
	 *
	 * @access private
	 * @return void
	 */
	private function setInputfieldOrderLineItem() {
		$inputfieldName = "InputfieldPWCommerceOrderLineItem";
		$this->inputfieldOrderLineItem = $this->wire('modules')->get($inputfieldName);

	}

	/**
	 * Render the entire input area for order
	 *
	 */
	public function ___render() {
		return $this->getOrderLineItemsMarkup();
	}

	/**
	 * Get markup for the line items in this order.
	 *
	 * @note: will include AlpineJS markup.
	 *
	 * @access private
	 * @return string $out Markup for order line items.
	 */
	private function getOrderLineItemsMarkup() {

		$out =
			// TODO DELETE WHEN DONE
			// "<div x-data='InputfieldPWCommerceOrderData' x-init='initWholeOrderData' @pwcommerceordercustomercountrychange.window='handleOrderCustomerCountryChange'>" .
			"<div>" .
			$this->getMarkupForOrderLineItemsForAlpineJS() .

			################
			// TODO RETHINK THIS!
			//  ** TRACKERS: hidden inputs **
			// to help determine original items removed when saving order
			$this->getMainOrderHiddenMarkupForExistingLineItemsIDs() .

			// to track CURRENTLY LIVE order line items PRODUCT IDs in an UNSAVED ORDER to help with CALCULATE SHIPPING, HANDLING AND TOTALS.
			$this->getMainOrderHiddenMarkupForCurrentLiveOrderLineItemsProductsIDs() .

			// to track CURRENTLY LIVE order line items IDs in an UNSAVED ORDER to help with CALCULATE SHIPPING, HANDLING AND TOTALS. Helps get correct inputs for quantity and discounts for existing live order line items
			$this->getMainOrderHiddenMarkupForCurrentLiveOrderLineItemsIDs() .

			"</div>";

		// ------
		return $out;
	}

	## ~~~~~~~~~~~~~~~~~~~

	private function getMarkupForOrderLineItemsForAlpineJS() {
		$out = $this->inputfieldOrderLineItem->getCustomBuildForm();

		// WRAP IT UP
		$out =
			"<div id='order_line_items'>{$out}</div>";
		// ------

		return $out;
	}

	public function getOrderLineItemsConfigValuesForClient() {
		$lineItemsArray = $this->getMainOrderLineItemsArray();

		/** @var array $clientData */
		$clientData = $this->inputfieldOrderLineItem->getOrderLineItemsConfigValuesForClient($lineItemsArray);

		return $clientData;
	}

	/**
	 * Get hidden markup to store CSV of IDs of order's line items.
	 *
	 * To help determine original items removed when saving order.
	 *
	 * @access private
	 * @return string rendered value of hidden field.
	 */
	private function getMainOrderHiddenMarkupForExistingLineItemsIDs() {
		$orderLineItemsIDsCSV = $this->getMainOrderLineItemsIDs();
		//------------------- order_existing_line_items_ids (getInputfieldHidden)
		$options = [
			'id' => "pwcommerce_order_existing_line_items_ids",
			'name' => 'pwcommerce_order_existing_line_items_ids',
			'value' => $orderLineItemsIDsCSV,
		];
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$field->attr([
			'x-ref' => "pwcommerce_order_existing_line_items",
		]);
		return $field->render();
	}

	/**
	 * Get hidden markup to store CSV of Product IDs of order's LIVE line items that are currently present in an unsaved order.
	 *
	 * To help with calculate shipping AND totals in place without saving.
	 *
	 * @access private
	 * @return string rendered value of hidden field.
	 */
	private function getMainOrderHiddenMarkupForCurrentLiveOrderLineItemsProductsIDs() {
		//------------------- order_current_in_edit_line_items_ids (getInputfieldHidden)
		$options = [
			'id' => "pwcommerce_order_live_order_line_items_products_ids",
			'name' => 'pwcommerce_order_live_order_line_items_products_ids',
			// @note: on load, defaults to saved order line items
		];
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$field->attr([
			'x-bind:value' => "getCurrentLiveOrderLineItemsProductsIDsCSV()"
		]);
		return $field->render();
	}

	/**
	 * Get hidden markup to store CSV of IDs of order's LIVE EXISTING and NEW line items that are currently present in an unsaved order.
	 *
	 * This is because existing items will have their quantity and discount inputs name suffixed with the line item ID. We need these values for in place calculate shipping.
	 * @note: for newly ADDED but UNSAVED LIVE line items, this also works since they temporarily use their PRODUCT ID as their line item ID, hence their suffixes will also be correct.
	 * These values help with calculating shipping AND totals in place without saving.
	 *
	 * @access private
	 * @return string rendered value of hidden field.
	 */
	private function getMainOrderHiddenMarkupForCurrentLiveOrderLineItemsIDs() {
		//------------------- order_live_order_line_items_ids (getInputfieldHidden)
		$options = [
			'id' => "pwcommerce_order_live_order_line_items_ids",
			'name' => 'pwcommerce_order_live_order_line_items_ids',
			// @note: on load, defaults to saved order line items
		];
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$field->attr([
			'x-bind:value' => "getCurrentLiveOrderLineItemsIDsCSV()"
		]);
		return $field->render();
	}

	/**
	 * Build CSV of IDs of this order's line items.
	 *
	 * @access private
	 * @return string $orderLineItemsIDsCSV CSV of IDs of order line items.
	 */
	private function getMainOrderLineItemsIDs() {

		$orderLineItemsIDsCSV = implode(",", $this->getMainOrderLineItemsRaw());
		return $orderLineItemsIDsCSV;
	}

	/**
	 * Find all order line items for this page.
	 *
	 * By default, return only their IDs.
	 *
	 * @access private
	 * @param array|string $fields The fields to return for the line items.
	 * @return array $orderLineItemsIDs Array of IDs of this order's line items.
	 */
	private function getMainOrderLineItemsRaw($fields = 'id') {
		// $orderLineItemsIDs = $this->wire('pages')->findRaw("parent_id={$this->page->id},include=all", 'id');
		/** @var array $orderLineItemsIDs */
		$orderLineItemsIDs = $this->wire('pages')->findRaw("parent_id={$this->page->id},include=all", $fields);
		return $orderLineItemsIDs;
	}

	/**
	 * Get the line items for this order page
	 *
	 * @return PageArray
	 */
	private function getMainOrderLineItems() {
		// TODO, OK?
		/** @var PageArray $lineItemsPages */
		$lineItemsPages = $this->page->children("include=all,check_access=0");

		//---
		return $lineItemsPages;
	}

	private function getMainOrderLineItemsArray() {

		// ----------
		/** @var PageArray $lineItemsPages */
		$lineItemsPages = $this->getMainOrderLineItems();

		$lineItemsArray = [];
		// ---------
		if ($lineItemsPages->count()) {
			foreach ($lineItemsPages as $page) {

				// creating array from WireArray
				// TODO: IN FUTURE, GET FROM CONTEXT INSTEAD OF HARDCODING HERE!
				$lineItemArray = $page->get(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME)->getArray();

				// TODO: OK? YES, SINCE SHOULDN'T SAVE EMPTY LINE ITEM!
				if (empty($lineItemArray)) {
					continue;
				}
				$lineItemsArray[] = $lineItemArray;
			} // end loop
		}
		// ----
		return $lineItemsArray;
	}
}