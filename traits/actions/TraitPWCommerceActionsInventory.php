<?php

namespace ProcessWire;

trait TraitPWCommerceActionsInventory
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INVENTORY ~~~~~~~~~~~~~~~~~~

	/**
	 * Action Inventory.
	 *
	 * @return mixed
	 */
	private function actionInventory() {

		$items = $this->items;
		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!
		if (empty($items)) {
			return null;
		}

		//------------------------------
		// DETERMINE VALUE FOR ACTION
		// bool int 1/0
		$value = in_array($this->action, ['allow_overselling', 'enable_selling']) ? 1 : 0;
		//------------------------------
		// DETERMINE PROPERTY TO ACTION
		// get property to set
		$property = in_array($this->action, ['allow_overselling', 'disallow_overselling']) ? 'allowBackorders' : 'enabled';

		//------------------
		// good to go
		$pages = $this->getItemsToAction();
		$i = 0;
		// action each item
		foreach ($pages as $page) {
			// skip if page is locked
			if ($page->isLocked()) {
				continue;
			}
			// get the stock field
			// $stock = $page->pwcommerce_product_stock;
			$stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
			$stock->$property = $value;
			//-------------
			$i++;
			//-------------
			// save the page's 'pwcommerce_product_stock' field
			// $page->save('pwcommerce_product_stock');
			$page->save(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		}

		// --------------------
		// prepare messages
		if ($this->action === 'allow_overselling') {
			// allow overselling
			$notice = sprintf(_n("Allowed overselling for %d item.", "Allowed overselling for %d items.", $i), $i);
		} elseif ($this->action === 'disallow_overselling') {
			// disallow overselling
			$notice = sprintf(_n("Disallowed overselling for %d item.", "Disallowed overselling for %d items.", $i), $i);
		} elseif ($this->action === 'enable_selling') {
			// enable selling
			$notice = sprintf(_n("Enabled selling for %d item.", "Enabled selling for %d items.", $i), $i);
		} else {
			// disable selling
			$notice = sprintf(_n("Disabled selling for %d item.", "Disabled selling for %d items.", $i), $i);
		}

		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO: WILL DETERMINE BASED ON HOW MANY ITEMS WE COULD ACTION
		];

		//-------
		return $result;
	}


	/**
	 * Post Process Order Status Restock Inventory.
	 *
	 * @param Page $orderPage
	 * @return mixed
	 */
	private function postProcessOrderStatusRestockInventory(Page $orderPage) {
		// @note: given that we are cancelling an existing order, we expect all these line items to have a 'published' status
		/** @var PageArray $orderLineItemsPages */
		$orderLineItemsPages = $orderPage->children("check_access=0");
		// get the related product IDs
		$productsIDs = [];
		foreach ($orderLineItemsPages as $orderLineItemPage) {
			$orderLineItem = $orderLineItemPage->get(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME);
			$productID = $orderLineItem->productID;
			// create key=>value pairs where key=productID and value=quantity
			$productsIDs[$productID] = $orderLineItem->quantity;
		}

		// find the products that track inventory
		$productsIDsSelector = implode("|", array_keys($productsIDs));
		// @note: for variants, this is their parent's field 'pwcommerce_product_settings'
		$selector = "(template=product,settings.trackInventory=1),(template=variant,parent.settings.trackInventory=1),id={$productsIDsSelector}";

		/** @var WireData $stock */
		$products = $this->pwcommerce->find($selector);

		if ($products->count()) {
			$productsTitles = [];
			foreach ($products as $product) {
				// get stock field
				$stock = $product->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
				// get current quantity
				$currentQuantity = (int) $stock->quantity;
				// get quantities to revert/restock wth
				$restockQuantity = (int) $productsIDs[$product->id];
				// -------
				// calculate new quantity
				$newQuantity = $currentQuantity + $restockQuantity;
				// ------
				// set new quantity
				$stock->set('quantity', $newQuantity);
				// ------
				// save
				$product->setAndSave(PwCommerce::PRODUCT_STOCK_FIELD_NAME, $stock);

				// --------
				// build product titles for re-stock notice
				$productsTitles[] = $product->title;
			}

			// -------
			// build notice
			$productsTitlesString = implode(", ", $productsTitles);
			$restockNotice = sprintf(__("Re-stocked product quantities for: %s."), $productsTitlesString);
			$this->message($restockNotice);

		}

	}

}
