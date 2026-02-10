<?php
namespace ProcessWire;

/**
 * PWCommerce: Finder.
 *
 * Finder for PWCommerce. Used for frontend API for PWCommerce for short syntax page finder for pwcommerce-related pages.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceFinder for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */


trait TraitPWCommerceFinder
{



	/**
	 * Find.
	 *
	 * @param mixed $selector
	 * @return mixed
	 */
	public function find($selector) {
		// TODO:IF EMPTY ERROR? SILENT? SUPS ONLY?
		if (!empty($selector)) {
			$selector = $this->convertShortSyntaxSelectorToFullSyntax($selector);
			// results should include pages user can't view since pwcommerce pages are under /admin/
			$selector .= "," . PwCommerce::CHECK_ACCESS_ZERO;
			// pass to $pages->find()
			return $this->wire('pages')->find($selector);
		}
	}

	/**
	 * Get.
	 *
	 * @param mixed $selector
	 * @return mixed
	 */
	public function get($selector) {
		// TODO:IF EMPTY ERROR? SILENT? SUPS ONLY?
		if (!empty($selector)) {
			$selector = $this->convertShortSyntaxSelectorToFullSyntax($selector);
			// results should include pages user can't view since pwcommerce pages are under /admin/
			$selector .= "," . PwCommerce::CHECK_ACCESS_ZERO;
			// pass to $pages->get()
			return $this->wire('pages')->get($selector);
		}
	}

	/**
	 * Find Raw.
	 *
	 * @param mixed $selector
	 * @param mixed $fields
	 * @param array $options
	 * @return mixed
	 */
	public function findRaw($selector, $fields = null, array $options = []) {
		// TODO: FIND RAW WILL BE PROBLEMATIC FOR FIELDS WITH RUNTIMEMARKUP IF NO FIELD GIVEN IN OPTIONS SINCE IT WILL TRY TO LOAD THE FIELD FROM THE DATABASE!

		// ------
		// TODO:IF EMPTY ERROR? SILENT? SUPS ONLY?
		if (!empty($selector)) {
			$selector = $this->convertShortSyntaxSelectorToFullSyntax($selector);
			// results should include pages user can't view since pwcommerce pages are under /admin/
			$selector .= "," . PwCommerce::CHECK_ACCESS_ZERO;
			if (empty($fields)) {
				// make sure fields has at least id to avoid SQL errors for pwcommerce features that use InputfieldRuntimeMarkup, e.g. products
				$fields = ['id', 'title'];
			}

			// pass to $pages->findRaw()
			return $this->wire('pages')->findRaw($selector, $fields, $options);
		}
	}

	/**
	 * Get Raw.
	 *
	 * @param mixed $selector
	 * @param mixed $fields
	 * @param array $options
	 * @return mixed
	 */
	public function getRaw($selector, $fields = null, array $options = []) {
		// TODO:IF EMPTY ERROR? SILENT? SUPS ONLY?
		if (!empty($selector)) {
			$selector = $this->convertShortSyntaxSelectorToFullSyntax($selector);
			// results should include pages user can't view since pwcommerce pages are under /admin/
			$selector .= "," . PwCommerce::CHECK_ACCESS_ZERO;
			if (empty($fields)) {
				// make sure fields has at least id to avoid SQL errors for pwcommerce features that use InputfieldRuntimeMarkup, e.g. products
				$fields = ['id', 'title'];
			}
			// pass to $pages->getRaw()
			return $this->wire('pages')->getRaw($selector, $fields, $options);
		}
	}

	/**
	 * Get Product Variants.
	 *
	 * @param mixed $product
	 * @param bool $isUseRaw
	 * @param mixed $fields
	 * @param array $options
	 * @return mixed
	 */
	public function getProductVariants($product, bool $isUseRaw = true, $fields = null, array $options = []) {
		$pages = $this->wire('pages');
		$selector = null;
		$variants = null;
		// TODO: INCLUDE ALL?
		if ($product instanceof Page && $product->template->name === 'pwcommerce-product') {
			// if we got a product page Page
			$variants = $product->children(PwCommerce::CHECK_ACCESS_ZERO);
		} elseif (is_string($product)) {
			// if we got a product title|name
			$productName = $this->wire('sanitizer')->pageName($product, true);
			$productID = (int) $pages->getRaw("template=pwcommerce-product,name={$productName}", 'id');
			if (!empty($productID)) {
				$selector = "template='variant,parent_id={$productID}";
			}
		} elseif (is_integer($product)) {
			// if we got an id
			$productID = (int) $product;
			if (!empty($productID)) {
				$selector = "template='variant,parent_id={$productID}";
			}
		}
		// ------
		// if we have a selector, use it
		if (!empty($selector)) {
			// results should include pages user can't view since pwcommerce pages are under /admin/
			$selector .= "," . PwCommerce::CHECK_ACCESS_ZERO;
			if (empty($isUseRaw)) {
				// use find()
				$variants = $this->find($selector);
			} else {
				// use findRaw()
				$variants = $this->findRaw($selector, $fields, $options);
			}
		}
		// ---------------
		return $variants;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~
	// TODO MOVE BELOW TO UTILITIES?
	/**
	 * Convert Short Syntax Selector To Full Syntax.
	 *
	 * @param mixed $selector
	 * @return mixed
	 */
	private function convertShortSyntaxSelectorToFullSyntax($selector) {
		$selectors = new Selectors($selector);
		// ------------
		foreach ($selectors as $selector) {
			// TODO: TRYING TO FIX ISSUE WITH LIMIT; i think this is because of array for replacement inbalance as we don't have limit there?
			$fields = $selector->fields;
			// ---------
			// WE ALWAYS REPLACE IN FIELDS
			// WE REPLACE IN VALUES IF EITHER OF THE FOLLOWING APPLIES
			// i. field has 'template' in array OR
			// ii. selector quote IS NOT EMPTY -> means that we could be dealing with OR:group
			// in which case, we could have template=xxx, or field=xxx, in the value
			// in the case of OR:group, it means we could replace valid values, e.g. we could replace 'product' by mistake
			// in cases where the value to search for is actually 'product'
			// TODO: maybe for OR:group, create a new selector, grab operator and split string by that? that would ensure we only replace value if field is template
			// but still always replace field
			// will also help with the operator error we were getting with !=''???
			$string = implode("|", $selector->fields) . $selector->operator . implode("|", $selector->values);
			if (in_array('template', $fields)) {
				// REPLACING SELECTOR TEMPLATE NAMES (VALUE)
				$values = $selector->values;
				// replace short syntax template names
				$values = $this->shortSyntaxToFullSyntaxTemplateNames($values);
				$selector->values = $values;
				// --------------
				$string = implode("|", $selector->fields) . $selector->operator . implode("|", $selector->values);
			} elseif (!empty($selector->quote)) {
				# SPECIAL REPLACEMENTS
				$values = $selector->values;
				// ==============
				// @note for special we also need to replace in this field name itself!
				// @note: this takes care of sub-selectors []
				// @note: e.g.  company=[locations>5, locations.title%=finland]
				if (!empty($fields)) {
					// replace short syntax field names
					$fields = $this->shortSyntaxToFullSyntaxFieldsNames($fields);
					$selector->fields = $fields;
				}
				// =============
				// NEED TO CREATE NEW SELECTORS HERE TO HANDLE THIS.
				// @note: accounts for selector such as description!='' (not empty)
				if (!empty($values)) {
					// TODO: always one item here?
					// process special selectors (e.g. OR:groups and Sub-selectors)
					$values = $this->processSpecialSelectorValues($values[0]);
					$selector->values = $values;
					// --------------
				}
				$string = implode("|", $selector->fields) . $selector->operator . implode("|", $selector->values);
			} else {
				# FIELDS REPLACEMENT
				// replace short syntax field names
				$fields = $this->shortSyntaxToFullSyntaxFieldsNames($fields);
				$selector->fields = $fields;
				// --------------
				$string = implode("|", $selector->fields) . $selector->operator . implode("|", $selector->values);
			}
		}
		// ---------
		return $selectors;
	}

	/**
	 * Short Syntax To Full Syntax Fields Names.
	 *
	 * @param mixed $fields
	 * @return mixed
	 */
	private function shortSyntaxToFullSyntaxFieldsNames($fields) {
		$searchFieldsNames = $this->getFieldsShortNamesForSelectorReplacement();
		$replaceFieldsNames = $this->getFieldsFullNamesForSelectorReplacement();
		return $this->shortSyntaxToFullSyntaxReplace($searchFieldsNames, $replaceFieldsNames, $fields);
	}

	/**
	 * Short Syntax To Full Syntax Template Names.
	 *
	 * @param mixed $values
	 * @return mixed
	 */
	private function shortSyntaxToFullSyntaxTemplateNames($values) {
		$searchTemplateNames = $this->getTemplatesShortNamesForSelectorReplacement();
		$replaceTemplateNames = $this->getTemplatesFullNamesForSelectorReplacement();
		return $this->shortSyntaxToFullSyntaxReplace($searchTemplateNames, $replaceTemplateNames, $values);
	}

	// ----------
	/**
	 * Process Special Selector Values.
	 *
	 * @param mixed $values
	 * @return mixed
	 */
	private function processSpecialSelectorValues($values) {
		$specialSelectors = new Selectors($values);
		$specialStringArray = [];
		// ---------------------
		foreach ($specialSelectors as $specialSelector) {
			$specialFields = $specialSelector->fields;
			$specialValues = $specialSelector->values;
			# TODO: TESTING NESTED SUB-SELECTOR PROCESSING
			// TODO: DOESN'T WORK - NOT SUPPORTED FOR NOW
			// if (!empty($specialSelector->quote)) {

			// 	$nestedSubSelector = implode("|", $specialSelector->fields) . $specialSelector->operator . implode("|", $specialSelector->values);

			// 	$processedNestedSubSelector = $this->processSpecialSelectorValues($nestedSubSelector);

			// }
			// ----------
			// REPLACEMENTS
			if (in_array('template', $specialFields)) {
				// REPLACING SPECIAL SELECTOR TEMPLATE NAMES (VALUE)
				// replace short syntax template names
				$specialValues = $this->shortSyntaxToFullSyntaxTemplateNames($specialValues);
				$specialSelector->values = $specialValues;
			} else {
				// REPLACING SPECIAL SELECTOR FIELD NAMES (FIELD)
				// replace short syntax field names
				$specialFields = $this->shortSyntaxToFullSyntaxFieldsNames($specialFields);
				$specialSelector->fields = $specialFields;
			}
			// --------------------
			// put the selector back again, saving to temporary array
			$specialStringArray[] = implode("|", $specialSelector->fields) . $specialSelector->operator . implode("|", $specialSelector->values);
		}
		// -----------
		// combine back to selector string
		$values = implode(",", $specialStringArray);
		// ------------
		return $values;
	}

	/**
	 * Short Syntax To Full Syntax Replace.
	 *
	 * @param mixed $search
	 * @param mixed $replace
	 * @param mixed $subject
	 * @return mixed
	 */
	private function shortSyntaxToFullSyntaxReplace($search, $replace, $subject) {

		// $testpregreplace = preg_replace("/\b{$search}\b/", $replace, $subject);
		$testpregreplace = preg_replace($search, $replace, $subject);
		$teststrreplace = str_replace($search, $replace, $subject);

		// -
		return preg_replace($search, $replace, $subject);
		// TODO: DELETE WHEN DONE
		return str_replace($search, $replace, $subject);
	}

	/**
	 * Undocumented function
	 *
	 * @return mixed
	 */
	private function getTemplatesShortNamesForSelectorReplacement() {
		// @note: order must match the long-form order!
		return [
			'/\battribute\b/',
			// TODO: had issues with 'attribute' double similar to 'variant' below with 'product-variant'
			// 'attribute-option',
			'/\boption\b/',
			'/\bbrand\b/',
			'/\bcategory\b/',
			'/\bcountry\b/',
			// @note: had issues with 'country' double similar to 'variant' below with 'product-variant'
			// 'country-territory',
			'/\bterritory\b/',
			'/\bcustomer\b/',
			// @note: had issues with 'customer' double similar to 'variant' below with 'product-variant'
			// '/\bcustomer-group\b/',
			'/\bgroup\b/',
			'/\bdiscount\b/',
			'/\bproperty\b/',
			'/\bdimension\b/',
			'/\bdownload\b/',
			'/\blegal-page\b/',
			'/\border\b/',
			'/\bline-item\b/',
			'/\bpayment-provider\b/',
			'/\bproduct\b/',
			// TODO: delete when done + CHECK OTHERS INCLUDING FIELDS FOR PRESENCE OF 'product' as that is also getting replaced, leading to final 'pwcommerce-pwcommerce-variant'
			// 'product-variant',
			'/\bvariant\b/',
			// 'settings',// TODO unsure? if yes, secure!
			'/\bshipping-rate\b/',
			'/\bshipping-zone\b/',
			'/\btag\b/',
			'/\btype\b/',

		];
	}

	/**
	 * Undocumented function
	 *
	 * @return mixed
	 */
	private function getTemplatesFullNamesForSelectorReplacement() {
		return [
			'pwcommerce-attribute',
			'pwcommerce-attribute-option',
			'pwcommerce-brand',
			'pwcommerce-category',
			'pwcommerce-country',
			'pwcommerce-country-territory',
			'pwcommerce-customer',
			'pwcommerce-customer-group',
			'pwcommerce-discount',
			'pwcommerce-property',
			'pwcommerce-dimension',
			'pwcommerce-download',
			'pwcommerce-legal-page',
			'pwcommerce-order',
			'pwcommerce-order-line-item',
			'pwcommerce-payment-provider',
			'pwcommerce-product',
			'pwcommerce-product-variant',
			// 'pwcommerce-settings',// TODO unsure? if yes, secure!
			'pwcommerce-shipping-rate',
			'pwcommerce-shipping-zone',
			'pwcommerce-tag',
			'pwcommerce-type',
		];
	}

	/**
	 * Get Fields Short Names For Selector Replacement.
	 *
	 * @return mixed
	 */
	private function getFieldsShortNamesForSelectorReplacement() {
		// @note: order must match the long-form order!
		return [
			'/\bbrand\b/',
			'/\bcategories\b/',
			'/\bcustomer\b/',
			'/\bcustomer_addresses\b/',
			'/\bcustomer_groups\b/',
			'/\bdescription\b/',
			'/\bdiscount\b/',
			'/\bdiscounts_apply_to\b/',
			'/\bdiscounts_eligibility\b/',
			'/\bdownloads\b/',
			'/\bdownload_settings\b/',
			'/\bfile\b/',
			'/\bimages\b/',
			'/\bnotes\b/',
			// TODO 'order_notes'/??
			'/\border\b/',
			'/\border_customer\b/',
			'/\border_discounts\b/',
			'/\bline_item\b/',
			'/\battributes\b/',
			// NOTE: had issues with 'attribute' double similar to 'variant' in templates with 'product-variant'
			// 'attributes_options',
			'/\boptions\b/',
			'/\bproperties\b/',
			'/\bsettings\b/',
			// @note: pwcommerce_product_settings
			// 'product_stock',
			'/\bstock\b/',
			// 'settings',
			'/\bshipping_fee_settings\b/',
			'/\bshipping_rate\b/',
			'/\bshipping_zone_countries\b/',
			'/\btags\b/',
			'/\btax_overrides\b/',
			'/\btax_rates\b/',
			'/\btype\b/',
		];
	}

	/**
	 * Undocumented function
	 *
	 * @return mixed
	 */
	private function getFieldsFullNamesForSelectorReplacement() {
		return [
			'pwcommerce_brand',
			'pwcommerce_categories',
			'pwcommerce_customer',
			'pwcommerce_customer_addresses',
			'pwcommerce_customer_groups',
			'pwcommerce_description',
			'pwcommerce_discount',
			'pwcommerce_discounts_apply_to',
			'pwcommerce_discounts_eligibility',
			'pwcommerce_downloads',
			'pwcommerce_download_settings',
			'pwcommerce_file',
			'pwcommerce_images',
			'pwcommerce_notes',
			'pwcommerce_order',
			'pwcommerce_order_customer',
			'pwcommerce_order_discounts',
			'pwcommerce_order_line_item',
			'pwcommerce_product_attributes',
			'pwcommerce_product_attributes_options',
			'pwcommerce_product_properties',
			'pwcommerce_product_settings',
			'pwcommerce_product_stock',
			// 'pwcommerce_settings',
			'pwcommerce_shipping_fee_settings',
			'pwcommerce_shipping_rate',
			'pwcommerce_shipping_zone_countries',
			'pwcommerce_tags',
			'pwcommerce_tax_overrides',
			'pwcommerce_tax_rates',
			'pwcommerce_type',
		];
	}
}