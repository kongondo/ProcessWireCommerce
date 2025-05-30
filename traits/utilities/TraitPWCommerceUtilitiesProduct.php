<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesProduct
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PRODUCTS ~~~~~~~~~~~~~~~~~~




	/**
	 * Check if a given product has variants.
	 *
	 * @param Page $product Product to check.
	 * @return boolean If a product has variants.
	 */
	public function isProductWithVariants($product) {
		$oneVariant = (int) $this->wire('pages')->getRaw("template=" . PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME . ",parent={$product},pwcommerce_product_stock.enabled=1", 'id');
		return !empty($oneVariant);
	}

	// CHECK IF ATTRIBUTES FEATURE IS INSTALLED, IN WHICH CASE CAN USE VARIANTS
	public function isVariantsInUse() {
		$installedOptionalFeatures = $this->getPWCommerceInstalledOptionalFeatures(PwCommerce::PWCOMMERCE_PROCESS_MODULE);

		return in_array('product_attributes', $installedOptionalFeatures);
	}

	// is given $product a digital product?
	public function isDigitalProduct($product) {
		$isDigitalProduct = false;
		if (is_array($product)) {
			// if we got an array of product settings
			if (!empty($product['settings'])) {
				// if product setting is under 'settings' key in a getRaw() find
				$isDigitalProduct = $product['settings']['data'] === 'digital';
			} else if (!empty($product['pwcommerce_product_settings'])) {
				// if product setting is under 'pwcommerce_product_settings' key in a getRaw() find
				$isDigitalProduct = $product['pwcommerce_product_settings']['data'] === 'digital';
			} else if (isset($product['shippingType'])) {
				// other array, e.g. from WireData->getArray()
				$isDigitalProduct = $product['shippingType'] === 'digital';
			}
		} else if (is_int($product)) {
			// if given an integer, we get the page
			$fields = ['id', 'parent_id', 'pwcommerce_product_settings' => 'settings'];
			$product = $this->wire('pages')->getRaw("template=" . PwCommerce::PRODUCT_TEMPLATE_NAME . ",id={$product}", $fields);
			if (!empty($product['settings'])) {
				$isDigitalProduct = $product['settings']['data'] === 'digital';
			}
		} else if ($product instanceof Page && $product->template->name === PwCommerce::PRODUCT_TEMPLATE_NAME) {
			// if given a Page object that is a product
			$isDigitalProduct = $product->pwcommerce_product_settings->shippingType === 'digital';
		}
		return $isDigitalProduct;
	}

	/**
	 * Check if given produc tracks inventory.
	 *
	 * @param Page $product Product to check.
	 * @return boolean If template of product shows it is a variant or not.
	 */
	public function isProductTrackInventory(Page $product) {
		/** @var WireData $settings */
		$settings = $product->template->name === PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME ? $product->parent->pwcommerce_product_settings : $product->pwcommerce_product_settings;
		// -------

		return (int) $settings->trackInventory === 1;
	}

	/**
	 * Check if given product or variant is enabled for selling.
	 *
	 * @param Page $product Product to check.
	 * @return boolean If product or variant is enabled for selling.
	 */
	public function isProductEnabledForSelling(Page $product) {
		$stock = $product->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		// -------

		return (int) $stock->enabled === 1;
	}

	/**
	 * Check if given product or variant is enabled for selling.
	 *
	 * @param Page $product Product to check.
	 * @return boolean If product or variant is enabled for selling.
	 */
	public function isProductAllowOverSelling(Page $product) {
		$stock = $product->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		// -------

		return (int) $stock->allowBackorders === 1;
	}


	/**
	 * Check if given product or variant allows overselling.
	 *
	 * This means 'allows back orders'.
	 * @note: Alias for PwCommerce::isProductAllowOverSelling
	 *
	 * @access public
	 * @param Page $product Product to check.
	 * @return bool Whether product or variant allows overselling.
	 */
	public function isProductAllowBackOrders(Page $product) {
		return $this->isProductAllowOverSelling($product);
	}

	/**
	 * Check if shop uses 'sale' and 'normal' price fields.
	 *
	 * @return bool true if sale and normal price in use, else false.
	 */
	public function isUseSaleAndNormalPriceFields() {
		$generalSettings = $this->getShopGeneralSettings();
		// product_price_fields_type
		$isUseSaleAndNormalPriceFields = !empty($generalSettings->product_price_fields_type) && $generalSettings->product_price_fields_type === 'sale_and_normal_price_fields';
		return !empty($isUseSaleAndNormalPriceFields);
	}

	/**
	 * Get the price of the lowest-priced active variant for a given product.
	 *
	 * @param Page $product Product whose lowest-priced variant to get.
	 * @return float Price of the lowest-priced variant.
	 */
	public function getPriceOfLowestPricedEnabledVariantForProduct($product) {
		$lowestPricedVariant = (float) $this->wire('pages')->getRaw("template=" . PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME . ",parent={$product},pwcommerce_product_stock.enabled=1,sort=pwcommerce_product_stock.price", 'pwcommerce_product_stock.price');
		return $lowestPricedVariant;
	}

	/**
	 * Get the price of the highest-priced active variant for a given product.
	 *
	 * @param Page $product Product whose highest-priced variant to get.
	 * @return float Price of the highest-priced variant.
	 */
	public function getPriceOfHighestPricedEnabledVariantForProduct($product) {
		$highestPricedVariant = (float) $this->wire('pages')->getRaw("template=" . PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME . ",parent={$product},pwcommerce_product_stock.enabled=1,sort=-pwcommerce_product_stock.price", 'pwcommerce_product_stock.price');
		return $highestPricedVariant;
	}

	/**
	 * For a given product/variant, get its remaining product/variant quantity from its stock.
	 *
	 * @note: Does not take into account status of track inventory setting.
	 * @note: Does not take into account active items in basket!
	 * @param Page $product Product whose quantity to get.
	 * @return int Quantity of product/variant.
	 */
	public function getProductRemainingStockQuantity(Page $product) {
		$stock = $product->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		// TODO WHAT ABOUT IF DOES NOT TRACK INVENTORY?
		// -------

		return (int) $stock->quantity;
	}

	public function isVariant($page) {
		return $page->template->name === PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME;
	}

	public function ___getProductWeight(Page $product, $weightID) {
		$unitProductWeight = 0;
		// determine if product is a variant or main product (without variants)
		$isVariant = $this->isVariant($product);
		// ---------
		// get the product properties
		// if product is a variant we get its properties from the parent product page
		$properties = $isVariant ? $product->parent->get(PwCommerce::PRODUCT_PROPERTIES_FIELD_NAME) : $product->get(PwCommerce::PRODUCT_PROPERTIES_FIELD_NAME);

		// get the first weight property that matches the shop's weight property ID, just in case there are several for some reason
		$firstWeightProperty = $properties->get("propertyID={$weightID}");
		// ------
		if (!empty($firstWeightProperty)) {
			// get unit weight of product
			$unitProductWeight = (float) $firstWeightProperty->value;

		}
		// ------
		return $unitProductWeight;
	}

	public function getShopProductWeightPropertyID() {
		$generalSettings = $this->getShopGeneralSettings();
		// return isset($generalSettings['product_weight_property']) ? $generalSettings['product_weight_property'] : 0;
		return !empty($generalSettings->product_weight_property) ? $generalSettings->product_weight_property : 0;
	}


}