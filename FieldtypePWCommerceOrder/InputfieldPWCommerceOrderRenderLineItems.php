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

	/**
	 *   construct.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	public function __construct($page) {

		$this->page = $page;


		// ----------
		$this->setInputfieldOrderLineItem();
	}

	/**
	 * Get and set InputfieldPWCommerceOrderLineItem to a class property.
	 *
	 * @return mixed
	 */
	private function setInputfieldOrderLineItem() {
		$inputfieldName = "InputfieldPWCommerceOrderLineItem";
		$this->inputfieldOrderLineItem = $this->wire('modules')->get($inputfieldName);

	}

	/**
	 * Render the entire input area for order
	 *
	 * @return mixed
	 */
	public function ___render() {
		return $this->getOrderLineItemsMarkup();
	}

	/**
	 * Get markup for the line items in this order.
	 *
	 * @return mixed
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

	/**
	 * Get Markup For Order Line Items For Alpine J S.
	 *
	 * @return mixed
	 */
	private function getMarkupForOrderLineItemsForAlpineJS() {
		$out = $this->inputfieldOrderLineItem->getCustomBuildForm();

		// WRAP IT UP
		$out =
			"<div id='order_line_items'>{$out}</div>";
		// ------

		return $out;
	}

	/**
	 * Get Order Line Items Config Values For Client.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemsConfigValuesForClient() {
		$lineItemsArray = $this->getMainOrderLineItemsArray();

		/** @var array $clientData */
		$clientData = $this->inputfieldOrderLineItem->getOrderLineItemsConfigValuesForClient($lineItemsArray);

		return $clientData;
	}

	/**
	 * Get hidden markup to store CSV of IDs of order's line items.
	 *
	 * @return mixed
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
	 * @return mixed
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
	 * @return mixed
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
	 * @return mixed
	 */
	private function getMainOrderLineItemsIDs() {

		$orderLineItemsIDsCSV = implode(",", $this->getMainOrderLineItemsRaw());
		return $orderLineItemsIDsCSV;
	}

	/**
	 * Find all order line items for this page.
	 *
	 * @param string $fields
	 * @return mixed
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
	 * @return mixed
	 */
	private function getMainOrderLineItems() {
		// TODO, OK?
		/** @var PageArray $lineItemsPages */
		$lineItemsPages = $this->page->children("include=all,check_access=0");

		//---
		return $lineItemsPages;
	}

	/**
	 * Get Main Order Line Items Array.
	 *
	 * @return mixed
	 */
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