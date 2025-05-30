<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Tags
 *
 * Class to render content for PWCommerce Process Module executeTags().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderTags for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceAdminRenderTags extends WireData
{

	protected function getResultsTableHeaders() {
		return [
			// TITLE
			// TODO: make these classes generic? e.g. for th percent width?
			[$this->_('Title'), 'pwcommerce_tags_table_title'],
			// USAGE
			[$this->_('Usage'), 'pwcommerce_tags_table_usage'],
		];
	}

	protected function getResultsTableRow($page, $editItemTitle) {
		// TODO: DON'T NEED TO SPECIFY NAME OF FIELD, E.D. 'pwcommerce_tags', or?
		// $referencingProducts = $page->references(true,$nameOfField);
		// @note: true -> 'include=all'
		// get the count of products referencing this tag
		$referencingProductsCount = $page->references(true)->count;
		$referencingProductsCountString = !empty($referencingProductsCount) ? $referencingProductsCount : $this->_('Tag not used by any product');
		$row = [
			// TITLE
			$editItemTitle,
			// USAGE TODO: PRODUCTS REFERENCING THIS TAG
			$referencingProductsCountString,
		];
		return $row;
	}

	protected function getNoResultsTableRecords() {
		$noResultsTableRecords = $this->_('No tags found.');
		return $noResultsTableRecords;
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
			'add_new_item_label' => $this->_('Add new tag'),
			// add new url
			'add_new_item_url' => "{$adminURL}tags/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	##########################



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
			// TODO RENAME TO bottom and top xxx?
			'least_sales' => $this->_('Least Sales'),
			'most_sales' => $this->_('Most Sales'),
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
		// SELECT data as tag_id
		// FROM field_pwcommerce_tags
		// GROUP BY tag_id

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::PRODUCT_TAGS_FIELD_NAME,
			'select_columns' => ['data AS tag_id'],
			'group_by_columns' => ['tag_id']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);

		if (!empty($results)) {
			$tagsIDs = array_column($results, 'tag_id');
			$tagsIDsSelector = implode("|", $tagsIDs);
			// NOTE: we want IDs of unused tags!
			$selector = ",id!={$tagsIDsSelector}";
		}

		// ----
		return $selector;

	}
	private function getSelectorForQuickFilterSales($quickFilterValue) {
		// e.g.
		// SELECT field_pwcommerce_tags.data AS tag_id,
		// 	SUM(field_pwcommerce_order_line_item.quantity) quantity_total
		// FROM field_pwcommerce_tags
		// LEFT JOIN field_pwcommerce_order_line_item
		// ON field_pwcommerce_tags.pages_id=field_pwcommerce_order_line_item.data
		// WHERE field_pwcommerce_order_line_item.data > 0
		// GROUP BY tag_id
		// ORDER BY quantity_total DESC
		// LIMIT 10 -- the high quantity threshold

		$tablePrefix = "field_";
		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::PRODUCT_TAGS_FIELD_NAME,
			'select_columns' => ["{$tablePrefix}pwcommerce_tags.data as tag_id"],
			'sum' => [
				'expression' => "{$tablePrefix}pwcommerce_order_line_item.quantity",
				'summed_column_name' => 'quantity_total',
			],
			'join' => [
				'table' => "{$tablePrefix}pwcommerce_order_line_item",
				'type' => 'LEFT',
				'condition' => "{$tablePrefix}pwcommerce_tags.pages_id={$tablePrefix}pwcommerce_order_line_item.data",
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
			'group_by_columns' => ['tag_id'],
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
			$tagsIDs = array_column($results, 'tag_id');
			$tagsIDsSelector = implode("|", $tagsIDs);
			$selector = ",id={$tagsIDsSelector}";
		}

		// ----
		return $selector;

	}

}