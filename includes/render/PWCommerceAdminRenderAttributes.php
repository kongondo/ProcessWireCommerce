<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Attributes
 *
 * Class to render content for PWCommerce Admin Module executeAttributes().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderAttributes for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */


class PWCommerceAdminRenderAttributes extends WireData
{


	/**
	 * Get Results Table Headers.
	 *
	 * @return mixed
	 */
	protected function getResultsTableHeaders() {
		return [
			// TITLE
			// TODO: make these classes generic? e.g. for th percent width?
			[$this->_('Title'), 'pwcommerce_attributes_table_title'],
			// NUMBER OF OPTIONS (CHILDREN)
			[$this->_('Options'), 'pwcommerce_attributes_table_options'],
			// USAGE
			[$this->_('Usage'), 'pwcommerce_attributes_table_usage'],
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
		// get the count of products referencing this attribute
		$referencingProductsCount = $page->references(true)->count;
		$referencingProductsCountString = !empty($referencingProductsCount) ? $referencingProductsCount : $this->_('Attribute not used by any product');
		$row = [
			// TITLE
			$editItemTitle,
			// NUMBER OF OPTIONS (CHILDREN)
			$page->numChildren,
			// USAGE TODO: PRODUCTS REFERENCING THIS ATTRIBUTE
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
		$noResultsTableRecords = $this->_('No attributes found.');
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
		$options = [
			// add new link
			'add_new_item_label' => $this->_('Add new attribute'),
			// add new url
			'add_new_item_url' => "{$adminURL}attributes/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
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
			// options
			'no_attribute_options' => $this->_('No Options'),
			// unused
			'unused' => $this->_('Unused'),
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
			} elseif ($quickFilterValue === 'no_attribute_options') {
				// HAS ATTRIBUTE OPTIONS
				$selector = $this->getSelectorForQuickFilterNoAttributeOptions();
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
	 * Get Selector For Quick Filter No Attribute Options.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterNoAttributeOptions() {
		$selector = ",children.count=0";
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
		// SELECT data as attribute_id
		// FROM field_pwcommerce_attributes
		// GROUP BY attribute_id

		$selector = '';
		// TODO DO WE DO ATTRIBUTES ONLY OR ALSO ATTRIBUTE OPTIONS?
		// TODO SHOULD THEY BE SEPARATE? E.G. UNUSED ATTRIBUTES AND UNUSED ATTRIBUTE OPTIONS? NAAH; WE DON'T SHOW NAMES OF ATTRIBUTES OPTIONS (SIMILAR TO VARIANTS!) SO IT WON'T HELP/WORK OK
		$queryOptions = [
			'table' => PwCommerce::PRODUCT_ATTRIBUTES_FIELD_NAME,
			'select_columns' => ['data AS attribute_id'],
			'group_by_columns' => ['attribute_id']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);

		if (!empty($results)) {
			$attributesIDs = array_column($results, 'attribute_id');
			$attributesIDsSelector = implode("|", $attributesIDs);
			// NOTE: we want IDs of unused attributes!
			$selector = ",id!={$attributesIDsSelector}";
		}

		// ----
		return $selector;

	}





}