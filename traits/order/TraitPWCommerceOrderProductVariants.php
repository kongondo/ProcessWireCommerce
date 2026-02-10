<?php

namespace ProcessWire;

trait TraitPWCommerceOrderProductVariants
{

	/**
	 * Get the product or product variant for an order line item.
	 *
	 * @param int $productsOrVariantsID
	 * @param bool $isVariant
	 * @return array
	 */
	private function getProductOrVariantPagesForOrderLineItem($productsOrVariantsID, bool $isVariant = false): array {

		$selector = "id={$productsOrVariantsID}";
		// @note: 'pwcommerce_product_settings' only for main products!
		$fields = ['id' => 'product_id', 'title', 'pwcommerce_product_stock' => 'stock', 'pwcommerce_product_settings' => 'settings', 'pwcommerce_categories' => 'categories', 'parent_id', 'templates_id'];
		// if categories not installed in shop, unset it!
		$productCategoriesFeature = 'product_categories';
		if (empty($this->pwcommerce->isOptionalFeatureInstalled($productCategoriesFeature))) {
			unset($fields['pwcommerce_categories']);
		}
		//-------------
		$productOrVariantPage = $this->wire('pages')->getRaw($selector, $fields);

		if ($isVariant) {
			// variant's parent settings and categories need fetching from parent product
			$parentProductSettingsAndCategories = $this->getVariantParentProductSettingsAndCategories($productOrVariantPage['parent_id']);
			// add variant parent product settings and categories to variant
			$productOrVariantPage = array_merge($productOrVariantPage, $parentProductSettingsAndCategories);
		}

		// ------------------
		return $productOrVariantPage;
	}

	/**
	 * Get Variant Parent Product Settings And Categories.
	 *
	 * @param int $parentID
	 * @return mixed
	 */
	private function getVariantParentProductSettingsAndCategories($parentID) {
		$fields = ['pwcommerce_product_settings' => 'settings', 'pwcommerce_categories' => 'categories'];
		// if categories not installed in shop, unset it!
		$productCategoriesFeature = 'product_categories';
		if (empty($this->pwcommerce->isOptionalFeatureInstalled($productCategoriesFeature))) {
			unset($fields['pwcommerce_categories']);
		}
		$parentProductSettingsAndCategories = $this->wire('pages')->getRaw("id={$parentID}", $fields);
		return $parentProductSettingsAndCategories;
	}

}
