<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Categories
 *
 * Class to render content for PWCommerce Process Module executeCategories().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderCategories for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */





class PWCommerceAdminRenderCategories extends WireData
{

	private $isCategoryACollection;

	/**
	 *   construct.
	 *
	 * @return mixed
	 */
	public function __construct() {
		$this->isCategoryACollection = $this->pwcommerce->isOtherOptionalSettingInstalled(PwCommerce::PWCOMMERCE_IS_CATEGORY_A_COLLECTION_SETTING_NAME);
	}


	/**
	 * Get Results Table Headers.
	 *
	 * @return mixed
	 */
	protected function getResultsTableHeaders() {
		return [
			// TITLE
			// TODO: make these classes generic? e.g. for th percent width?
			[$this->_('Title'), 'pwcommerce_categories_table_title'],
			// USAGE
			[$this->_('Usage'), 'pwcommerce_categories_table_usage'],
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

		$notInUseCategoryStr = $this->_('Category not used by any product');
		$notInUseCollectionStr = $this->_('Collection not used by any product');
		$notInUseStr = !empty($this->isCategoryACollection) ? $notInUseCollectionStr : $notInUseCategoryStr;
		// set each row

		// TODO: DON'T NEED TO SPECIFY NAME OF FIELD, E.D. 'pwcommerce_categories', or?
		// $referencingProducts = $page->references(true,$nameOfField);
		// @note: true -> 'include=all'
		// get the count of products referencing this category
		$referencingProductsCount = $page->references(true)->count;
		$referencingProductsCountString = !empty($referencingProductsCount) ? $referencingProductsCount : $notInUseStr;
		$row = [
			// TITLE
			$editItemTitle,
			// USAGE TODO: PRODUCTS REFERENCING THIS CATEGORY
			$referencingProductsCountString,

		];
		return $row;
	}

	/**
	 * Get No Results Table Records.
	 *
	 * @return mixed
	 */
	protected function getNoResultsTableRecords() {
		$notFoundCategoriesStr = $this->_('No categories found.');
		$notFoundCollectionsStr = $this->_('No collections found.');
		$noResultsTableRecords = !empty($this->isCategoryACollection) ? $notFoundCollectionsStr : $notFoundCategoriesStr;
		return $noResultsTableRecords;
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
			'trash' => $this->_('Trash'),
			'delete' => $this->_('Delete'),
		];

		$addNewCategoriesLabel = $this->_('Add new category');
		$addNewCollectionLabel = $this->_('Add new collection');

		if (!empty($this->isCategoryACollection)) {
			// 'COLLECTIONS' TERM IN USE
			$addNewItemLabel = $addNewCollectionLabel;
			$addNewItemURLPart = 'collections';
		} else {
			// 'CATEGORIES' TERM IN USE
			$addNewItemLabel = $addNewCategoriesLabel;
			$addNewItemURLPart = 'categories';
		}

		$options = [
			// add new link
			// 'add_new_item_label' => $this->_('Add new category'),
			'add_new_item_label' => $addNewItemLabel,
			// add new url
			// 'add_new_item_url' => "{$adminURL}categories/add/",
			'add_new_item_url' => "{$adminURL}{$addNewItemURLPart}/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];



		//--------
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

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
			'active' => $this->_('Active'),// published
			'draft' => $this->_('Draft'),// unpublished
			// unused
			'unused' => $this->_('Unused'),
			// sales
			// TODO RENAME TO bottom and top xxx?
			'least_sales' => $this->_('Least Sales'),
			'most_sales' => $this->_('Most Sales'),
		];

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
				// ACTIVE (PUBLISHED) OR DRAFT (UNPUBLISHED)
				$selector = $this->getSelectorForQuickFilterActive($quickFilterValue);
			} elseif (in_array($quickFilterValue, ['least_sales', 'most_sales'])) {
				// SALES
				$selector = $this->getSelectorForQuickFilterSales($quickFilterValue);
			} else if ($quickFilterValue === 'unused') {
				// IS UNUSED IN PRODUCTS
				$selector = $this->getSelectorForQuickFilterUnused();
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

	/**
	 * Get Selector For Quick Filter Unused.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterUnused() {
		// e.g.
		// SELECT data as category_id
		// FROM field_pwcommerce_categories
		// GROUP BY category_id

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::PRODUCT_CATEGORIES_FIELD_NAME,
			'select_columns' => ['data AS category_id'],
			'group_by_columns' => ['category_id']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);

		if (!empty($results)) {
			$categoriesIDs = array_column($results, 'category_id');
			$categoriesIDsSelector = implode("|", $categoriesIDs);
			// NOTE: we want IDs of unused categories!
			$selector = ",id!={$categoriesIDsSelector}";
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
		// SELECT field_pwcommerce_categories.data AS category_id,
		// 	SUM(field_pwcommerce_order_line_item.quantity) quantity_total
		// FROM field_pwcommerce_categories
		// LEFT JOIN field_pwcommerce_order_line_item
		// ON field_pwcommerce_categories.pages_id=field_pwcommerce_order_line_item.data
		// WHERE field_pwcommerce_order_line_item.data > 0
		// GROUP BY category_id
		// ORDER BY quantity_total DESC
		// LIMIT 10 -- the high quantity threshold



		$tablePrefix = "field_";
		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::PRODUCT_CATEGORIES_FIELD_NAME,
			'select_columns' => ["{$tablePrefix}pwcommerce_categories.data as category_id"],
			'sum' => [
				'expression' => "{$tablePrefix}pwcommerce_order_line_item.quantity",
				'summed_column_name' => 'quantity_total',
			],
			'join' => [
				'table' => "{$tablePrefix}pwcommerce_order_line_item",
				'type' => 'LEFT',
				'condition' => "{$tablePrefix}pwcommerce_categories.pages_id={$tablePrefix}pwcommerce_order_line_item.data",
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
			'group_by_columns' => ['category_id'],
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
			$categoriesIDs = array_column($results, 'category_id');
			$categoriesIDsSelector = implode("|", $categoriesIDs);
			$selector = ",id={$categoriesIDsSelector}";
		}

		// ----
		return $selector;

	}



}