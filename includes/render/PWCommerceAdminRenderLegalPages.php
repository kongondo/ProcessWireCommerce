<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Legal Pages
 *
 * Class to render content for PWCommerce Admin Module executeLegalPages().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderLegalPages for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceAdminRenderLegalPages extends WireData
{

	private $datetimeFormat;

	public function __construct() {
		$this->datetimeFormat = $this->pwcommerce->getDateTimeFormat();
	}



	protected function getResultsTableHeaders() {
		return [
			// TITLE
			// TODO: make these classes generic? e.g. for th percent width?
			[$this->_('Title'), 'pwcommerce_legal_pages_table_title'],
			// MODIFIED
			[$this->_('Modified'), 'pwcommerce_legal_pages_table_modified'],
		];
	}

	protected function getResultsTableRow($page, $editItemTitle) {
		$row = [
			// TITLE
			$editItemTitle,
			// MODIFIED
			$this->getLastModifiedDate($page),

		];
		return $row;
	}

	protected function getNoResultsTableRecords() {
		$noResultsTableRecords = $this->_('No legal pages found.');
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
			'add_new_item_label' => $this->_('Add new legal page'),
			// add new url
			'add_new_item_url' => "{$adminURL}legal-pages/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	/**
	 * Build the string for the last modified date of this legal page.
	 *
	 * @credits: ProcessPageEdit::buildFormInfo().
	 * @param Page $page The legal page whose modified date we are building.
	 * @return string $modifiedDate The last modified date string.
	 */
	private function getLastModifiedDate($page) {
		$unknown = '[?]';
		$datetimeFormat = $this->datetimeFormat;
		$lowestDate = strtotime('1974-10-10');
		$modifiedDate = $page->modified > $lowestDate ? date($datetimeFormat, $page->modified) . " " .
			"<span class='detail'>(" . wireRelativeTimeStr($page->modified) . ")</span>" : $unknown;
		//--------------
		return $modifiedDate;
	}
}
