<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Products
 *
 * Class to render content for PWCommerce Admin Module executeProducts().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderProducts for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceAdminRenderProducts extends WireData
{

	private $assetsURL;
	private $stock;

	/**
	 *   construct.
	 *
	 * @param array $options
	 * @return mixed
	 */
	public function __construct($options) {
		$this->assetsURL = $options['assets_url'];
	}


	// ~~~~~~~~~~~~~
	/**
	 * Builds a custom add new page/item for adding a new product.
	 *
	 * @return mixed
	 */
	public function getCustomAddNewItemForm() {
		/** @var InputfieldForm $form */
		$form = $this->pwcommerce->getInputfieldForm();
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		// ++++++++++++++++

		//------------------- new product title (getInputfieldPageTitle)
		$useLanguages = $this->wire('languages') ? true : false;
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		$options = [
			'id' => "pwcommerce_add_new_item_title",
			'name' => "pwcommerce_add_new_item_title",
			'label' => $this->_('Product Title'),
			'useLanguages' => $useLanguages,
			'required' => true,
			// TODO: needed?
			'collapsed' => Inputfield::collapsedNever,
			//'classes' => 'pwcommerce_add_new_item',
		];
		$field = $this->pwcommerce->getInputfieldPageTitle($options);
		$wrapper->add($field);

		//------------------- allow duplicate product title for this new product (getInputfieldCheckbox)

		$options = [
			'id' => "pwcommerce_add_new_item_title_allow_duplicate",
			'name' => "pwcommerce_add_new_item_title_allow_duplicate",
			// 'label' => $this->_('Allow duplicate title'),
			'label' => ' ', // @note: skipping label
			'label2' => $this->_('Allow duplicate title'),
			'description' => $this->_("Tick to still create this product in case a product with an identical title already exists."),
			'notes' => $this->_("If ticked, this will create a product with a duplicate title but with a different name. I.e., a suffix will be appended to the product name."),
			'collapsed' => Inputfield::collapsedNever,
		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		// add checkbox
		$wrapper->add($field);

		//------------------- is_ready_to_save (getInputfieldHidden)
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPwCommerce::renderAddItem() know that we are ready to save
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];

		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		//------------------- save button (getInputfieldButton)
		$options = [
			'id' => "submit_save",
			'name' => "pwcommerce_save_new_button",
			'type' => 'submit',
			'label' => $this->_('Save'),
		];
		$field = $this->pwcommerce->getInputfieldButton($options);
		$field->showInHeader();
		// add submit button for add new country add  SAVE process views
		$wrapper->add($field);

		//------------------- save + publish button (getInputfieldButton)
		$options = [
			'id' => "submit_save_and_publish",
			'name' => "pwcommerce_save_and_publish_new_button",
			'type' => 'submit',
			'label' => $this->_('Save + Publish'),
			'secondary' => true,
		];
		$field = $this->pwcommerce->getInputfieldButton($options);
		// add submit button for single item add  SAVE + PUBLISH process views
		$wrapper->add($field);

		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//----------
		return $form;
	}


	/**
	 * Get Results Table Headers.
	 *
	 * @return mixed
	 */
	protected function getResultsTableHeaders() {
		return [
			// THUMB
			[$this->_('Image'), 'pwcommerce_products_table_image'],
			// TITLE
			[$this->_('Title'), 'pwcommerce_products_table_title'],
			// SKU
			[$this->_('SKU'), 'pwcommerce_products_table_sku'],
			// PRICE
			[$this->_('Price'), 'pwcommerce_products_table_price'],
			// QUANTITY
			[$this->_('Quantity'), 'pwcommerce_products_table_quantity'],
		];
	}

	/**
	 * Get Results Table Row.
	 *
	 * @param Page $page
	 * @param mixed $editItemTitle
	 * @return mixed
	 */
	protected function getResultsTableRow($page, $editItemTitle) {

		$this->stock = $stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		$priceRendereredAsCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($stock->price);
		$row = [
			// THUMB
			$this->getProductThumb($page),
			// TITLE
			$editItemTitle,
			// SKU??? - TODO: if has variants?
			$stock->sku,
			// PRICE - TODO: if has variants?
			// $stock->price, // TODO: CURRENCY SYMBOL PLUS FORMAT TO 0.00?
			$priceRendereredAsCurrency, // TODO: HIDE CURRENCY SYMBOL BUT SHOW FORMATTED DECIMAL?
			// QUANTITY TODO: if with variants, then this should be their count or just leave blank or '-'??
			//$stock->quantity,
			$this->getProductQuantityString($page),

		];
		return $row;
	}

	/**
	 * Get No Results Table Records.
	 *
	 * @return mixed
	 */
	protected function getNoResultsTableRecords() {
		$noResultsTableRecords = $this->_('No products found.');
		return $noResultsTableRecords;
	}

	/**
	 * Get Product Thumb.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getProductThumb($page) {
		$firstImage = $page->pwcommerce_images->first();

		// first image found
		if ($firstImage) {
			$class = "w-16 lg:w-24";
			// TODO: SET TIME LIMIT HERE TO 120?
			// TODO; IS WIDTH OR IS IT HEIGHT? HERE OK?
			// $imageURL = $firstImage->height(260)->url; // TODO: USE CSS TO RENDER SMALLER!
			// @note: equal heights and widths
			$imageURL = $firstImage->size(260, 260)->url; // TODO: USE CSS TO RENDER SMALLER!
		} else {
			$class = "w-12 opacity-25";
			$assetsURL = $this->assetsURL;
			$imageURL = $assetsURL . "icons/no-image-found.svg"; // TODO: MAKE IT BIGGER! 260PX HIGH AT LEAST
		}

		//---------------assetsURL WE NEED A DIV HERE?
		$out = "<img src='{$imageURL}' class='{$class}'>";

		return $out;
	}

	/**
	 * Extra Statuses.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	protected function extraStatuses($page) {

		$productSettings = $page->get(PwCommerce::PRODUCT_SETTINGS_FIELD_NAME);
		$stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		$notEnabledStatusString = "";
		$extraStatuses = [];

		if (!empty($productSettings->useVariants)) {
			// PRODUCT WITH VARIANTS
			// get count of non enabled variants (not enabled for selling)
			$fields = PwCommerce::PRODUCT_STOCK_FIELD_NAME . ".enabled";
			$stockNotEnabledSelector = PwCommerce::PRODUCT_STOCK_FIELD_NAME . ".enabled=0";
			$notEnabledVariants = $this->wire('pages')->findRaw("parent={$page->id}, {$stockNotEnabledSelector}, include=all", $fields);
			// ++++++++++++
			if (!empty($notEnabledVariants)) {
				$notEnabledVariantsCount = count($notEnabledVariants);
				// some variants not enabled for selling
				$notEnabledStatusString = sprintf(_n('%d variant not enabled', '%d variants not enabled', $notEnabledVariantsCount), $notEnabledVariantsCount);
				$extraStatuses[] = $notEnabledStatusString;
			}
		} else if (empty($stock->enabled)) {
			// PRODUCT WITHOUT VARIANTS
			// product not enabled for selling
			$notEnabledStatusString = $this->_('not enabled');
			$extraStatuses[] = $notEnabledStatusString;
		}

		return $extraStatuses;

	}

	/**
	 * Get Bulk Edit Actions Panel.
	 *
	 * @param mixed $adminURL
	 * @return mixed
	 */
	protected function getBulkEditActionsPanel($adminURL) {
		$actions = [
			'publish' => $this->_('Publish'),
			'unpublish' => $this->_('Unpublish'),
			'lock' => $this->_('Lock'),
			'unlock' => $this->_('Unlock'),
			// TODO SHOULD WE AMEND TITLE OF CLONED ITEM? CONFIGURABLE? OR IN ACTION 'CLONE (KEEP TITLE)' & CLONE??
			'clone' => $this->_('Clone'),
			'trash' => $this->_('Trash'),
			'delete' => $this->_('Delete'),
		];
		$options = [
			// add new link
			'add_new_item_label' => $this->_('Add new product'),
			// add new url
			'add_new_item_url' => "{$adminURL}products/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	/**
	 * Get Product Quantity String.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getProductQuantityString($page) {

		$out = "";
		$quantity = null;

		$productSettings = $page->get(PwCommerce::PRODUCT_SETTINGS_FIELD_NAME);
		$stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);

		// tracking inventory
		if (!empty($productSettings->trackInventory)) {
			// does it use variants?
			if (!empty($productSettings->useVariants)) {
				// sum of variants quantities
				$fields = PwCommerce::PRODUCT_STOCK_FIELD_NAME . ".quantity";
				// $variants = $this->wire('pages')->findRaw("parent={$page->id}, include=all", 'pwcommerce_product_stock.quantity');
				$variants = $this->wire('pages')->findRaw("parent={$page->id}, include=all", $fields);
				$quantity = array_sum($variants);
			} else {
				// does not use variants
				$quantity = $stock->quantity;
			}
		} else {
			// does not track inventory
			//$out = '&#x2014;'; // em dash (long hyphen)
			// $out = "<small>" . $this->_('Does not track inventory') . "</small>";
			$out = $this->_('Does not track inventory');
		}

		// final string for products TRACKING INVENTORY
		if (is_int($quantity)) {
			$quantity = number_format($quantity);
			// format for product USING variants
			if (!empty($productSettings->useVariants)) {
				$variantsCount = $page->numChildren;
				// TODO; WORK ON THIS  - NOT WORKING AS EXPECTED; PLURAL MESSED UP
				// $out = sprintf(_n('%1$s in stock for %2$d variant', '%1$s in stock for %2$d variants.', $quantity, $variantsCount), $quantity, $variantsCount);
				// @note: this works
				// $out = sprintf(__("%d in stock"), $quantity);
				// @note: '$quantity' is now a string!
				$out = sprintf(__("%s in stock"), $quantity);
				$out .= " ";
				$out .= sprintf(_n('for %d variant', 'for %d variants', $variantsCount), $variantsCount);
			} else {
				// format for product NOT USING variants
				$out = sprintf(__("%d in stock"), $quantity);
			}
		}

		return $out;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ QUICK FILTERS  ~~~~~~~~~~~~~~~~~~

	/**
	 *    get Quick Filters Values.
	 *
	 * @return mixed
	 */
	protected function ___getQuickFiltersValues() {
		$filters = [
			// reset/all
			'reset' => $this->_('All'),
			// active
			'active' => $this->_('Active'),// published + enabled
			'draft' => $this->_('Draft'),// unpublished or not enabled
			// variants
			'has_variants' => $this->_('Has Variants'),
			// inventory
			// TODO IN SETTINGS, HAVE SETTING FOR LOW STOCK, MAYBE '5'? BUT MAKE CONFIGURABLE
			'tracks_inventory' => $this->_('Tracks Inventory'),
			'out_of_stock' => $this->_('Out of Stock'),
			'low_inventory' => $this->_('Low Inventory'),
			// sales
			'least_sales' => $this->_('Least Sales'),
			'most_sales' => $this->_('Most Sales'),
			// TODO NOT IN USE FOR NOW!
			// 'on_sale' => $this->_('On Sale'),
			// shipping
			'physical' => $this->_('Physical'),
			'digital' => $this->_('Digital'),
			'service_event' => $this->_('Service/Event'),
			// image
			'no_image' => $this->_('No Image'),
		];
		// TODO NOT IN USE FOR NOW!
		// IF NOT USING 'SALE' AND 'NORMAL' PRICE FIELDS
		// unset 'on_sale'
		// if (empty($this->pwcommerce->isUseSaleAndNormalPriceFields())) {
		// 	unset($filters['on_sale']);
		// }
		// ------
		return $filters;
	}

	/**
	 * Get Allowed Quick Filter Values.
	 *
	 * @return mixed
	 */
	private function getAllowedQuickFilterValues() {
		// filters array
		/** @var array $filters */
		$filters = $this->getQuickFiltersValues();
		$allowedQuickFilterValues = array_keys($filters);
		return $allowedQuickFilterValues;
	}

	/**
	 * Get Selector For Quick Filter.
	 *
	 * @return mixed
	 */
	protected function getSelectorForQuickFilter() {
		$input = $this->wire('input');

		$selector = '';
		// NOTE: KEYS -> filter values; VALUEs -> STATUS CONSTANTS
		$allowedQuickFilterValues = $this->getAllowedQuickFilterValues();

		$quickFilterValue = $this->wire('sanitizer')->option($input->pwcommerce_quick_filter_value, $allowedQuickFilterValues);

		if (!empty($quickFilterValue)) {
			// quick filter checks
			// ++++++++++
			if (in_array($quickFilterValue, ['active', 'draft'])) {
				// ACTIVE (PUBLISHED + ENABLED) OR DRAFT (UNPUBLISHED OR NOT ENABLED)
				$selector = $this->getSelectorForQuickFilterActive($quickFilterValue);
			} elseif (in_array($quickFilterValue, ['least_sales', 'most_sales', 'on_sale'])) {
				// SALES
				$selector = $this->getSelectorForQuickFilterSales($quickFilterValue);
			} else if ($quickFilterValue === 'has_variants') {
				// HAS VARIANTS (CHILDREN)
				$selector = $this->getSelectorForQuickFilterVariants();
			} else if (in_array($quickFilterValue, ['tracks_inventory', 'out_of_stock', 'low_inventory'])) {
				// TRACKS INVENTORY AND ZERO QUANTITY OR LOW INVENTORY
				$selector = $this->getSelectorForQuickFilterInventory($quickFilterValue);
			} else if (in_array($quickFilterValue, ['physical', 'digital', 'service_event'])) {
				// SHIPPING
				$selector = $this->getSelectorForQuickFilterShipping($quickFilterValue);
			} else if ($quickFilterValue === 'no_image') {
				// NO IMAGE
				$selector = $this->getSelectorForQuickFilterNoImage();
			}
		}

		return $selector;
	}

	/**
	 * Get Selector For Quick Filter Active.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterActive($quickFilterValue) {
		$productSettingsFieldName = PwCommerce::PRODUCT_SETTINGS_FIELD_NAME;
		$stockFieldName = PwCommerce::PRODUCT_STOCK_FIELD_NAME;
		$selector = '';
		if ($quickFilterValue === 'active') {
			// PUBLISHED + ENABLED
			// $selector = ",status<" . Page::statusUnpublished . "," . PwCommerce::PRODUCT_STOCK_FIELD_NAME . ".enabled=1";
			$selector = ",status<" . Page::statusUnpublished . ",({$stockFieldName}.enabled=1,{$productSettingsFieldName}.use_variants=0),(children.{$stockFieldName}.enabled=1,{$productSettingsFieldName}.use_variants=1)";
		} else if ($quickFilterValue === 'draft') {
			// UNPUBLISHED OR NOT ENABLED
			$selector = ",(status>=" . Page::statusUnpublished . "),({$stockFieldName}.enabled=0,{$productSettingsFieldName}.use_variants=0),(children.{$stockFieldName}.enabled=0,{$productSettingsFieldName}.use_variants=1)";

		}

		// ----

		return $selector;
	}

	/**
	 * Get Selector For Quick Filter Sales.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterSales($quickFilterValue) {

		// e.g.
		// SELECT data as product_id, quantity
		// FROM field_pwcommerce_order_line_item
		// WHERE data > 0
		// AND quantity > 20 -- the high quantity threshold
		// GROUP BY product_id, quantity
		// ORDER BY quantity DESC;

		// SELECT data as product_id, quantity
		// FROM field_pwcommerce_order_line_item
		// WHERE data > 0
		// AND quantity > 0
		// AND quantity < 5 -- the low quantity threshold
		// GROUP BY product_id, quantity
		// ORDER BY quantity;
		// *****************

		// TODO - ABOVE ARE WRONG! WE NEED TO GROUP BY PRODUCT ID THEN SUM/COUNT? THE QUANTITY SUBFIELD!
		// ABOVE IS CURRENTLY COUNTING OCCURENCES OF THE PRODUCT ID IN LINE ITEMS! E.G. PRODUCT ID 1840 IN SOME ORDER HAVING A QTY OF 1 IS PICKED UP AS A LOW THRESHOLD!

		// -- THIS WORKS I THINK? ---
		// SELECT data AS product_id,
		// SUM(quantity) quantity_total
		// FROM field_pwcommerce_order_line_item
		// GROUP BY product_id
		// ORDER BY quantity_total DESC
		// LIMIT 10

		// -- most sales --- (DESC)

		# TOP 10 MOST SOLD (QUANTITIES) PRODUCTS
		// SELECT data AS product_id, product_title,
		// 		SUM(quantity) quantity_total
		// FROM field_pwcommerce_order_line_item
		// GROUP BY product_id, product_title
		// ORDER BY quantity_total DESC
		// LIMIT 10 -- the high quantity threshold

		# BOTTOM 10 LEAST SOLD (QUANTITIES) PRODUCTS
		// -- least sales ---
		// SELECT data AS product_id, product_title,
		// 		SUM(quantity) quantity_total
		// FROM field_pwcommerce_order_line_item
		// GROUP BY product_id, product_title
		// ORDER BY quantity_total
		// LIMIT 10 -- the low quantity threshold

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::ORDER_LINE_ITEM_FIELD_NAME,
			'select_columns' => ['data AS product_id'],
			'sum' => [
				'expression' => 'quantity',
				'summed_column_name' => 'quantity_total',
			],
			'conditions' => [
				// data column/subfield {product ID}
				[
					'column_name' => 'data',
					'operator' => '>',
					'column_value' => 0,// to skip empty product ID, just in case
					'column_type' => 'int',
					// i.e. parameter name of the form :name
					// NOTE: excluding since no ambiguity. TraitPWCommerceDatabase::getGroupByQuery will default to 'column_name'
					// 'param_identifier' => 'data',
				],
			],
			'group_by_columns' => ['product_id'],
			'order_by_columns' => ['quantity_total'],
			// 'order_by_descending' => true,
		];

		$shopGeneralSettings = $this->pwcommerce->getshopGeneralSettings();
		if ($quickFilterValue === 'most_sales') {
			// MOST SALES
			$queryOptions['order_by_descending'] = true;
			$mostSalesThreshold = $shopGeneralSettings->order_most_sales_threshold;
			if (empty($mostSalesThreshold)) {
				$mostSalesThreshold = PwCommerce::PWCOMMERCE_MOST_SALES_THRESHOLD;
			}
			$limit = $mostSalesThreshold;

		} else {

			$leastSalesThreshold = $shopGeneralSettings->order_least_sales_threshold;
			if (empty($leastSalesThreshold)) {
				$leastSalesThreshold = PwCommerce::PWCOMMERCE_LEAST_SALES_THRESHOLD;
			}
			$limit = $leastSalesThreshold;
		}

		$queryOptions['limit'] = $limit;

		$results = $this->pwcommerce->processQueryGroupBySum($queryOptions);
		if (!empty($results)) {
			$productsIDs = array_column($results, 'product_id');
			$productsIDsSelector = implode("|", $productsIDs);
			// NOTE: getting the product or the parent product if the product id is a variant!
			// NOTE id|children.id doesn't work! it gets the variants parents only
			// hence we ues OR:group
			$selector = ",(id={$productsIDsSelector}),(children.id={$productsIDsSelector})";

		}


		// ----
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter Variants.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterVariants() {
		$selector = "," . PwCommerce::PRODUCT_SETTINGS_FIELD_NAME . ".use_variants=1,children.count>0";
		// ----
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter Inventory.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterInventory($quickFilterValue) {

		// ============
		$productSettingsFieldName = PwCommerce::PRODUCT_SETTINGS_FIELD_NAME;
		$stockFieldName = PwCommerce::PRODUCT_STOCK_FIELD_NAME;
		$selector = '';
		if ($quickFilterValue === 'tracks_inventory') {
			// selector to check if product tracks inventory
			$selector = ",{$productSettingsFieldName}.track_inventory=1";
		} else if ($quickFilterValue === 'out_of_stock') {
			// selector to check if product is out of stock
			// NOTE: HAS TO BE TRACKING INVENTORY + QUANTITY EMPTY
			// NOTE: we check for product without variants and variants separately in OR:group
			$selector = ",{$productSettingsFieldName}.track_inventory=1,({$stockFieldName}.quantity<1,{$productSettingsFieldName}.use_variants=0),(children.{$stockFieldName}.quantity<1,{$productSettingsFieldName}.use_variants=1)";
		} else if ($quickFilterValue === 'low_inventory') {
			// selector to check if product is low on inventory against a threshold
			// TODO MAKE THRESHOLD CONFIGUARABLE! DEFAULT IS '5'
			// NOTE: HAS TO BE TRACKING INVENTORY + QUANTITY <= threshold BUT NOT ZERO (out of stock)
			// NOTE: we check for product without variants and variants separately in OR:group
			$shopGeneralSettings = $this->pwcommerce->getshopGeneralSettings();
			$lowInventoryThreshold = $shopGeneralSettings->product_quick_filters_low_stock_threshold;
			if (empty($lowInventoryThreshold)) {
				$lowInventoryThreshold = PwCommerce::PWCOMMERCE_LOW_STOCK_THRESHOLD;
			}
			// ++++++++
			$selector = ",{$productSettingsFieldName}.track_inventory=1,({$stockFieldName}.quantity>0,{$stockFieldName}.quantity<{$lowInventoryThreshold},{$productSettingsFieldName}.use_variants=0),(children.{$stockFieldName}.quantity>0,children.{$stockFieldName}.quantity<{$lowInventoryThreshold},{$productSettingsFieldName}.use_variants=1)";
		}




		// ----
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter Shipping.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterShipping($quickFilterValue) {
		// 'physical' | 'physical_no_shipping' | 'digital' | 'service'
		$productSettingsFieldName = PwCommerce::PRODUCT_SETTINGS_FIELD_NAME;
		$selector = '';
		if ($quickFilterValue === 'physical') {
			// selector to return physical products
			$selector = ",{$productSettingsFieldName}.shipping_type=physical_no_shipping|physical";
		} else if ($quickFilterValue === 'digital') {
			// selector to return digital products
			$selector = ",{$productSettingsFieldName}.shipping_type=digital";
		} else if ($quickFilterValue === 'service_event') {
			// selector to return service or events products
			$selector = ",{$productSettingsFieldName}.shipping_type=service";
		}

		// ----
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter No Image.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterNoImage() {
		$selector = ",pwcommerce_images=''";
		// ----
		return $selector;
	}


}