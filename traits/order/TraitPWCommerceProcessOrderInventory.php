<?php

namespace ProcessWire;



trait TraitPWCommerceProcessOrderInventory
{



	/**
	 * Update Order Product Quantities.
	 *
	 * @param mixed $productPage
	 * @param mixed $quantity
	 * @return mixed
	 */
	private function updateOrderProductQuantities($productPage, $quantity)
	{


		// check if product tracks inventory
		if (empty($this->pwcommerce->isProductTrackInventory($productPage))) {

			// PRODUCT DOES NOT TRACK INVENTORY; SKIP
			return;
		}

		// GOOD TO GO: UPDATE QUANTITIES

		// TODO - BELOW A BIT VERBOSE BUT GOOD FOR CLARITY?!

		/** @var WireData $stock */
		$stock = $productPage->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		$productPage->of(false);
		$currentProductStockQuantity = $stock->quantity;
		$purchasedOrderLineItemQuantity = $quantity;
		// TODO: HANDLE NEGATIVES HERE? e.g. allow back orders!
		$newProductStockQuantity = $currentProductStockQuantity - $purchasedOrderLineItemQuantity;

		// update quantity
		$stock->quantity = $newProductStockQuantity;
		// ---------
		// TODO: maybe not needed as we adjusted $stock above?
		// $productPage->pwcommerce_product_stock = $stock;
		// TODO TEST THIS!
		$productPage->set(PwCommerce::PRODUCT_STOCK_FIELD_NAME, $stock);
		// -------------
		// save updated quantity
		// $productPage->save('pwcommerce_product_stock');
		// TODO TEST THIS!
		$productPage->save(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
	}


	// TODO DELETE WHEN DONE
	/**
	 * Update Order Products Quantities.
	 *
	 * @return mixed
	 */
	private function updateOrderProductsQuantities()
	{
		// UPDATE PRODUCT QUANTITIES FOR PRODUCTS (including variants) THAT TRACK INVENTORY
		/** @var WireArray $orderLineItems */
		$orderLineItems = $this->getOrderLineItems();
		foreach ($orderLineItems as $orderLineItem) {
			// -----------
			/** @var WireData $orderLineItem */

			/** @var Page $productPage */
			// get the product page represented in this line item
			$productPage = $this->wire('pages')->get($orderLineItem->productID);
			// -----------------
			// check if product tracks inventory
			if (empty($this->pwcommerce->isProductTrackInventory($productPage))) {

				// PRODUCT DOES NOT TRACK INVENTORY; SKIP
				continue;
			} else {
				// TODO DELETE WHEN DONE DEBUGGING

			}

			// GOOD TO GO: UPDATE QUANTITIES

			// TODO - BELOW A BIT VERBOSE BUT GOOD FOR CLARITY?!

			/** @var WireData $stock */
			$stock = $productPage->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
			$productPage->of(false);
			$currentProductStockQuantity = $stock->quantity;
			$purchasedOrderLineItemQuantity = $orderLineItem->quantity;
			// TODO; HANDLE NEGATIVES HERE? e.g. allow back orders!
			$newProductStockQuantity = $currentProductStockQuantity - $purchasedOrderLineItemQuantity;

			// update quantity
			$stock->quantity = $newProductStockQuantity;
			// ---------
			// TODO: maybe not needed as we adjusted $stock above?
			// $productPage->pwcommerce_product_stock = $stock;
			// TODO TEST THIS!
			$productPage->set(PwCommerce::PRODUCT_STOCK_FIELD_NAME, $stock);
			// -------------
			// save updated quantity
			// $productPage->save('pwcommerce_product_stock');
			// TODO TEST THIS!
			$productPage->save(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		}
	}
}
