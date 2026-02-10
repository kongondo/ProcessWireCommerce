<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Downloads
 *
 * Class to render content for PWCommerce Admin Module executeDownloads().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderDownloads for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */


class PWCommerceAdminRenderDownloads extends WireData
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
			[$this->_('Title'), 'pwcommerce_downloads_table_title'],
			// DETAILS
			[$this->_('Details'), 'pwcommerce_downloads_table_details'],
			// MAXIMUM DOWNLOADS
			[$this->_('Maximum Downloads'), 'pwcommerce_downloads_table_maximum_downloads'],
			// TIME TO DOWNLOAD/EXPIRY
			[$this->_('Expiry'), 'pwcommerce_downloads_table_time_to_download'],
			// USAGE
			[$this->_('Usage'), 'pwcommerce_downloads_table_usage'],
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


		// TODO: IN FUTURE RELEASE, FILE THUMB? GRID VIEW?
		// TODO: DON'T NEED TO SPECIFY NAME OF FIELD, E.D. 'pwcommerce_downloads', or?
		// ------------------
		$download = $page->pwcommerce_download_settings;
		// -------------
		// $referencingProducts = $page->references(true,$nameOfField);
		// @note: true -> 'include=all'
		// get the count of products referencing this download
		$referencingProductsCount = $page->references(true)->count;
		$referencingProductsCountString = !empty($referencingProductsCount) ? $referencingProductsCount : $this->_('Download not used by any product');
		$row = [
			// TITLE
			$editItemTitle,
			// INFO
			$this->getDownloadDetails($page),
			// MAXIMUM DOWNLOADS
			$download->maximumDownloads,
			// TIME TO DOWNLOAD/EXPIRY
			$download->timeToDownload,
			// USAGE
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
		$noResultsTableRecords = $this->_('No downloads found.');
		return $noResultsTableRecords;
	}

	/**
	 * Get Download Details.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getDownloadDetails($page) {
		// @note: first works since outputformatting is off!
		// otherwise, this is a single file field
		$file = $page->pwcommerce_file->first();
		if ($file) {
			// add file details TODO: for now, only ext and sizeStr
			$details = [$file->ext, $file->filesizeStr()];
			$detailsString = implode(', ', $details);
		} else {
			// TODO? uk-alert-danger
			$detailsString = $this->_('Missing download file');
		}

		$out = "<small class='block italic'>{$detailsString}</small>";

		//----------
		return $out;
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
			'add_new_item_label' => $this->_('Add new download'),
			// add new url
			'add_new_item_url' => "{$adminURL}downloads/add/",
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
			// unused
			'unused' => $this->_('Unused'),
			// missing download file
			'missing_download_file' => $this->_('No Download File'),
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
			} else if ($quickFilterValue === 'unused') {
				// IS UNUSED IN PRODUCTS
				$selector = $this->getSelectorForQuickFilterUnused();
			} else if ($quickFilterValue === 'missing_download_file') {
				// IS MISSING DOWNLOAD FILE
				$selector = $this->getSelectorForQuickFilterNoDownloadFile();
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
		// SELECT data as download_id
		// FROM field_pwcommerce_downloads
		// GROUP BY download_id

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::PRODUCT_DOWNLOADS_FIELD_NAME,
			'select_columns' => ['data AS download_id'],
			'group_by_columns' => ['download_id']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);

		if (!empty($results)) {
			$tagsIDs = array_column($results, 'download_id');
			$tagsIDsSelector = implode("|", $tagsIDs);
			// NOTE: we want IDs of unused tags!
			$selector = ",id!={$tagsIDsSelector}";
		}

		// ----
		return $selector;

	}
	/**
	 * Get Selector For Quick Filter No Download File.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterNoDownloadFile() {
		$selector = ",pwcommerce_file=''";
		// ----
		return $selector;
	}


}