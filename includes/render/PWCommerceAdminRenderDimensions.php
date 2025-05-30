<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Dimensions
 *
 * Class to render content for PWCommerce Admin Module executeDimensions().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderDimensions for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */


class PWCommerceAdminRenderDimensions extends WireData
{



	protected function getResultsTableHeaders() {
		return [
			// TITLE
			// TODO: make these classes generic? e.g. for th percent width?
			[$this->_('Title'), 'pwcommerce_dimensions_table_title'],
			// USAGE
			[$this->_('Usage'), 'pwcommerce_dimensions_table_usage'],
		];
	}

	protected function getResultsTableRow($page, $editItemTitle) {


		// TODO: DON'T NEED TO SPECIFY NAME OF FIELD, E.D. 'pwcommerce_dimensions', or?
		// $referencingProducts = $page->references(true,$nameOfField);
		// @note: true -> 'include=all'
		// get the count of products referencing this dimension
		$referencingProductsCount = $this->getDimensionUsageCount($page);
		$referencingProductsCountString = !empty($referencingProductsCount) ? $referencingProductsCount : $this->_('Dimension not used by any product');
		$row = [
			// TITLE
			$editItemTitle,
			// USAGE TODO: PRODUCTS REFERENCING THIS DIMENSION
			$referencingProductsCountString,

		];
		return $row;
	}

	protected function getNoResultsTableRecords() {
		$noResultsTableRecords = $this->_('No dimensions found.');
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
			'add_new_item_label' => $this->_('Add new dimension'),
			// add new url
			'add_new_item_url' => "{$adminURL}dimensions/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	private function getDimensionUsageCount(Page $page) {
		$productsUsingDimension = $this->wire('pages')->findRaw("template=pwcommerce-product,pwcommerce_product_properties.dimension_id={$page},include=all", 'id');
		return count($productsUsingDimension);
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
		// SELECT dimension_id
		// FROM field_pwcommerce_product_properties
		// GROUP BY dimension_id

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::PRODUCT_PROPERTIES_FIELD_NAME,
			'select_columns' => ['dimension_id'],
			'group_by_columns' => ['dimension_id']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);

		if (!empty($results)) {
			$dimensionsIDs = array_column($results, 'dimension_id');
			$dimensionsIDsSelector = implode("|", $dimensionsIDs);
			// NOTE: we want IDs of unused dimensions!
			$selector = ",id!={$dimensionsIDsSelector}";
		}

		// ----
		return $selector;

	}

}