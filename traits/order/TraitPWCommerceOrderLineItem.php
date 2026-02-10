<?php

namespace ProcessWire;

trait TraitPWCommerceOrderLineItem
{
	/**
	 * Gets the session's order line items.
	 *
	 * @param mixed $orderPage
	 * @return mixed
	 */
	public function getOrderLineItems($orderPage = null)
	{

		// if order page NOT GIVEN; get from session
		if (empty($orderPage)) {
			$orderPage = $this->getOrderPage();
		}
		// --------
		/** @var PageArray $orderLineItemsPages */
		$orderLineItemsPages = $this->getOrderLineItemsPages($orderPage);

		// -----------
		// TODO CONFIRM WORKS!
		// --------

		$orderLineItems = new WireArray();
		foreach ($orderLineItemsPages as $orderLineItemPage) {
			/** @var WireData $orderLineItem */
			$orderLineItem = $orderLineItemPage->get(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME);
			// -----------
			// ADD some fields we might require
			// order page ID + created date
			$orderLineItem->orderID = $orderPage->id;
			$orderLineItem->orderCreated = $orderPage->created;
			// --------------
			// ADD TO WIREARRAY
			$orderLineItems->add($orderLineItem);
		}

		return $orderLineItems;
	}

	/**
	 * Gets the session's order line items pages.
	 *
	 * @param mixed $orderPage
	 * @param bool $includeHidden
	 * @return mixed
	 */
	public function getOrderLineItemsPages($orderPage = null, bool $includeHidden = false)
	{

		// @note: init this just to avoid errors in case no order
		$orderLineItemsPages = new PageArray();
		// if order page NOT GIVEN; get from session
		if (empty($orderPage)) {

			$orderPage = $this->getOrderPage();
		}

		if (!empty($orderPage)) {
			// @note: hidden order line item pages are the cart items removed by customer from the basket after checkout started
			// they are left there for reuse in case customer re-adds them
			// they are deleted after final checkout when the session ends @see: postProcessOrderLineItems()
			$includeHiddenSelector = $includeHidden ? ",include=hidden" : '';
			/** @var PageArray $orderLineItemsPages */
			$orderLineItemsPages = $orderPage->children("check_access=0{$includeHiddenSelector}");
		}

		return $orderLineItemsPages;
	}


	/**
	 * Set 'calculated' values for given order line item.
	 *
	 * @param WireData $orderLineItem
	 * @param Page $shippingCountry
	 * @param Page $orderLineItemProductPage
	 * @return mixed
	 */
	private function setOrderLineItemCalculatedValues(WireData $orderLineItem, Page $shippingCountry, Page $orderLineItemProductPage)
	{
		// @note: includes setting and calculating discount values, prices, taxes, etc

		$isVariant = $this->isVariant($orderLineItemProductPage);

		$settings = $isVariant ? $orderLineItemProductPage->parent->pwcommerce_product_settings : $orderLineItemProductPage->pwcommerce_product_settings;
		$orderLineItem->shippingType = $settings->shippingType;

		// ---------------
		/** @var array $productOrVariantPage */
		// contains stock, product stettings, categories, etc values of the order line item page this order line item belongs to.
		$productOrVariantPage = $this->getProductOrVariantPagesForOrderLineItem($orderLineItem->productID, $isVariant);

		// SET: CALCULABLE VALUES
		$orderLineItemCalculatedValuesOptions = [
			/** @var WireData $orderLineItem */
			'order_line_item' => $orderLineItem,
			/** @var array $productOrVariantPage */
			'product_or_variant_page' => $productOrVariantPage,
			/** @var Page $shippingCountry */
			'shipping_country' => $shippingCountry,
			'is_charge_taxes_manual_exemption' => false,
			'is_customer_tax_exempt' => false,
			// new order line items require processing of the 'TRUE' price
			// this takes into account whether prices include taxes or not
			'is_process_order_line_item_product_true_price' => true
		];

		$orderLineItem = $this->pwcommerce->getOrderLineItemCalculatedValues($orderLineItemCalculatedValuesOptions);

		// ------------
		return $orderLineItem;
	}

	/**
	 * Post-process order line items of completed order
	 *
	 * @return mixed
	 */
	private function postProcessOrderLineItems()
	{

		/** @var Page $orderPage */
		$orderPage = $this->getOrderPage();





		// --------
		/** @var PageArray $orderLineItemsPages */
		$orderLineItemsPages = $this->getOrderLineItemsPages($orderPage, $includeHidden = true);





		foreach ($orderLineItemsPages as $orderLineItemPage) {
			// DELETE HIDDEN ORDER LINE ITEMS (abandoned) AND PROCESS REST FOR QUANTITIES
			// TODO IN FUTURE, ALSO PROCESS FOR STATUSES!
			if ($orderLineItemPage->isHidden()) {

				$orderLineItemPage->delete();
				continue;
			}
			// ----------
			// UPDATE PRODUCT QUANTITIES FOR PRODUCTS (including variants) THAT TRACK INVENTORY
			/** @var WireData $orderLineItem */
			$orderLineItem = $orderLineItemPage->get(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME);
			/** @var Page $productPage */
			// get the product page represented in this line item
			$productPage = $this->wire('pages')->get($orderLineItem->productID);
			$this->updateOrderProductQuantities($productPage, $orderLineItem->quantity);
		}
	}

	/**
	 * Process Order Line Items Detached From Cart.
	 *
	 * @return mixed
	 */
	private function processOrderLineItemsDetachedFromCart()
	{

		// @NOTE: THIS METHOD IS CALLED BEFORE THE LOOP IN parseCart(). This means $orderLineItemsPages might have fewer pages than in the cart in cases where an item is being 're-added' to the cart. This is fine since the issue we check for here is presence in the cart!
		// ---------
		/** @var Page $order */
		$orderPage = $this->getOrderPage();
		// @NOTE: THIS DOES NOT RETURN HIDDEN PAGES 'which we reuse' @see: notes in the method
		/** @var PageArray $orderLineItemsPages */
		$orderLineItemsPages = $this->getOrderLineItemsPages($orderPage);

		// ==============
		// TODO @KONGONDO -> COMMENT
		/** @var stdClass $cartItems */
		$cartItems = $this->getCart();
		$cartItemsProductIDs = array_column($cartItems, 'product_id');

		foreach ($orderLineItemsPages as $orderLineItemPage) {
			/** @var WireData $orderLineItem */
			$orderLineItem = $orderLineItemPage->get(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME);
			$orderLineItemProductID = $orderLineItem->productID;
			if (!in_array($orderLineItemProductID, $cartItemsProductIDs)) {
				// DELETE DETACHED ORDER LINE ITEM
				$orderLineItemPage->delete();
			}
		}
	}

	/**
	 * Process Existing Line Items Removed From Cart.
	 *
	 * @param array $removedProductIDs
	 * @return mixed
	 */
	public function processExistingLineItemsRemovedFromCart(array $removedProductIDs)
	{
		// @NOTE: IF WE ARE HERE, IT MEANS BASKET WAS AMENDED POST-ORDER CONFIRMATION
		$orderPage = $this->getOrderPage();

		$removedProductIDsSelector = implode("|", $removedProductIDs);
		/** @var PageArray $removedExistingOrderLineItemsPages */
		$removedExistingOrderLineItemsPages = $orderPage->children("check_access=0,pwcommerce_order_line_item.product_id={$removedProductIDsSelector}");

		foreach ($removedExistingOrderLineItemsPages as $removedOrderLineItemPage) {
			$removedOrderLineItemPage->of(false);
			$removedOrderLineItemPage->addStatus(Page::statusHidden);
			// SET THE POPULATED $orderLineItem as order line item field value on the order line item page
			$orderLineItem = $removedOrderLineItemPage->get(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME);
			$orderLineItem->quantity = 0;
			$removedOrderLineItemPage->set(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME, $orderLineItem);
			$removedOrderLineItemPage->save();
		}

		// CALL SAVE ORDER TO AMEND ITEMS AND WHOLE ORDER
		// @note: just to be double sure, we check again if we should be here
		// TODO BETTER CHECK WITH getOrderPage()????
		if (!empty($this->session->isOrderConfirmed)) {
			// NOTE: in 'TraitPWCommerceSaveOrder'
			$this->saveOrder();
		}
	}

	/**
	 * Is Order Line Item Already Exists.
	 *
	 * @param mixed $orderLineItemPageName
	 * @param mixed $orderPage
	 * @return bool
	 */
	private function isOrderLineItemAlreadyExists($orderLineItemPageName, $orderPage)
	{
		$isOrderLineItemAlreadyExistsID = $this->wire('pages')->getRaw("template=" . PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME . ", parent={$orderPage}, name={$orderLineItemPageName}", 'id');

		return !empty($isOrderLineItemAlreadyExistsID);
	}
}
