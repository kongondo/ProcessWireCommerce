<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Brands
 *
 * Class to render content for PWCommerce Process Module executeBrands().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderBrands for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */


class PWCommerceAdminRenderBrands extends WireData
{

	private $assetsURL;
	public function __construct($options) {
		$this->assetsURL = $options['assets_url'];
	}

	protected function getResultsTableHeaders() {
		return [
			// THUMB
			[$this->_('Logo'), 'pwcommerce_brands_table_logo'],
			// TITLE
			[$this->_('Title'), 'pwcommerce_brands_table_title'],
			// USAGE
			[$this->_('Usage'), 'pwcommerce_brands_table_usage'],
		];
	}

	protected function getNoResultsTableRecords() {
		$noResultsTableRecords = $this->_('No brands found.');
		return $noResultsTableRecords;
	}

	protected function getResultsTableRow($page, $editItemTitle) {
		// get the count of products referencing this brand
		$referencingProductsCount = $page->references(true)->count;
		$referencingProductsCountString = !empty($referencingProductsCount) ? $referencingProductsCount : $this->_('Brand not used by any product');
		$row = [
			// THUMB
			$this->getBrandLogo($page),
			// TITLE
			$editItemTitle,
			// USAGE: PRODUCTS REFERENCING THIS BRAND
			$referencingProductsCountString,
		];
		return $row;
	}


	private function getBrandLogo($page) {
		$firstImage = $page->pwcommerce_images->first();

		// first image found
		if ($firstImage) {
			$class = "w-16 lg:w-24";
			$imageURL = $firstImage->height(260)->url;
		} else {
			$class = "w-12 opacity-25";
			$imageURL = "{$this->assetsURL}icons/no-image-found.svg";
		}

		//---------------
		$out = "<img src='{$imageURL}' class='{$class}'>";
		return $out;
	}

	protected function getBulkEditActionsPanel($adminURL) {
		$actions = [
			'publish' => $this->_('Publish'),
			'unpublish' => $this->_('Unpublish'),
			'lock' => $this->_('Lock'),
			'unlock' => $this->_('Unlock'),
			'trash' => $this->_('Trash'),
			'delete' => $this->_('Delete'),
		];
		$options = [
			// add new link
			'add_new_item_label' => $this->_('Add new brand'),
			// add new url
			'add_new_item_url' => "{$adminURL}brands/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}



	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ QUICK FILTERS  ~~~~~~~~~~~~~~~~~~

	protected function ___getQuickFiltersValues() {
		$filters = [
			// reset/all
			'reset' => $this->_('All'),
			// active
			'active' => $this->_('Active'),// published
			'draft' => $this->_('Draft'),// unpublished
			// unused
			'unused' => $this->_('Unused'),
			// sales
			'least_sales' => $this->_('Least Sales'),
			'most_sales' => $this->_('Most Sales'),
			// logo
			'no_logo' => $this->_('No Logo'),
		];
		// ------
		return $filters;
	}

	private function getAllowedQuickFilterValues() {
		// filters array
		/** @var array $filters */
		$filters = $this->getQuickFiltersValues();
		$allowedQuickFilterValues = array_keys($filters);
		return $allowedQuickFilterValues;
	}

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
				// ACTIVE (PUBLISHED) OR DRAFT (UNPUBLISHED)
				$selector = $this->getSelectorForQuickFilterActive($quickFilterValue);
			} elseif (in_array($quickFilterValue, ['least_sales', 'most_sales'])) {
				// SALES
				$selector = $this->getSelectorForQuickFilterSales($quickFilterValue);
			} else if ($quickFilterValue === 'unused') {
				// IS UNUSED IN PRODUCTS
				$selector = $this->getSelectorForQuickFilterUnused();
			} else if ($quickFilterValue === 'no_logo') {
				// NO LOGO
				$selector = $this->getSelectorForQuickFilterNoLogo();
			}
		}
		return $selector;
	}

	private function getSelectorForQuickFilterActive($quickFilterValue) {
		$selector = '';
		if ($quickFilterValue === 'active') {
			// PUBLISHED
			$selector = ",status<" . Page::statusUnpublished;
		} else if ($quickFilterValue === 'draft') {
			// UNPUBLISHED
			$selector = ",status>=" . Page::statusUnpublished;
		}
		// ----
		return $selector;
	}

	private function getSelectorForQuickFilterUnused() {
		// e.g.
		// SELECT data as brand_id
		// FROM field_pwcommerce_brand
		// GROUP BY brand_id

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::PRODUCT_BRAND_FIELD_NAME,
			'select_columns' => ['data AS brand_id'],
			'group_by_columns' => ['brand_id']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);

		if (!empty($results)) {
			$brandsIDs = array_column($results, 'brand_id');
			$brandsIDsSelector = implode("|", $brandsIDs);
			// NOTE: we want IDs of unused brands!
			$selector = ",id!={$brandsIDsSelector}";
		}

		// ----
		return $selector;

	}

	private function getSelectorForQuickFilterSales($quickFilterValue) {
		// e.g.
		// SELECT field_pwcommerce_brand.data AS brand_id,
		// 	SUM(field_pwcommerce_order_line_item.quantity) quantity_total
		// FROM field_pwcommerce_brand
		// LEFT JOIN field_pwcommerce_order_line_item
		// ON field_pwcommerce_brand.pages_id=field_pwcommerce_order_line_item.data
		// WHERE field_pwcommerce_order_line_item.data > 0
		// GROUP BY brand_id
		// ORDER BY quantity_total DESC
		// LIMIT 10 -- the high quantity threshold

		$tablePrefix = "field_";
		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::PRODUCT_BRAND_FIELD_NAME,
			'select_columns' => ["{$tablePrefix}pwcommerce_brand.data as brand_id"],
			'sum' => [
				'expression' => "{$tablePrefix}pwcommerce_order_line_item.quantity",
				'summed_column_name' => 'quantity_total',
			],
			'join' => [
				'table' => "{$tablePrefix}pwcommerce_order_line_item",
				'type' => 'LEFT',
				'condition' => "{$tablePrefix}pwcommerce_brand.pages_id={$tablePrefix}pwcommerce_order_line_item.data",
			],
			// WHERE
			'conditions' => [
				// data column/subfield {product ID}
				[
					'column_name' => "{$tablePrefix}pwcommerce_order_line_item.data",
					'operator' => '>',
					'column_value' => 0,// to skip empty product ID, just in case
					'column_type' => 'int',
					// i.e. parameter name of the form :name
					// NOTE: excluding since no ambiguity. TraitPWCommerceDatabase::getGroupByQuery will default to 'column_name'
					'param_identifier' => 'order_line_item_data',
				],
			],
			// group
			'group_by_columns' => ['brand_id'],
			// order by
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
			$brandsIDs = array_column($results, 'brand_id');
			$brandsIDsSelector = implode("|", $brandsIDs);
			$selector = ",id={$brandsIDsSelector}";
		}

		// ----
		return $selector;

	}

	private function getSelectorForQuickFilterNoLogo() {
		$selector = ",pwcommerce_images=''";
		// ----
		return $selector;
	}


}