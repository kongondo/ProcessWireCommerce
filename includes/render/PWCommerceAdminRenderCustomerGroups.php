<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Customer Groups
 *
 * Class to render content for PWCommerce Admin Module executeCustomerGroups().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderCustomerGroups for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */


class PWCommerceAdminRenderCustomerGroups extends WireData
{



	/**
	 * Get View Customer Group.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	protected function getViewCustomerGroup($page) {
		// get the view URL if item is unlocked
		$out = $this->getViewItemURL($page);
		// add published and locked status if applicable
		$status = [];
		if ($page->isLocked()) {
			$status[] = $this->_('locked');
		}

		// TODO: DO WE REALLY NEED THIS STATUS FOR ORDERS???
		if ($page->isUnpublished()) {
			$status[] = $this->_('unpublished');
		}
		$statusString = implode(', ', $status);
		if ($statusString) {
			$out .= "<small class='block italic mt-1'>{$statusString}</small>";
		}
		// $out = "<a href='{$adminURL}orders/edit/?id={$page->id}'>{$page->title}</a>";
		return $out;
	}

	/**
	 * Get View Item U R L.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getViewItemURL($page) {

		// TODO: CHECK IF UNLOCKED FIRST!
		$adminURL = $this->options['admin_url'];

		// if page is locked, don't show edit URL
		if ($page->isLocked()) {
			$out = "<span>{$page->title}</span>";
		} else {
			$out = "<a href='{$adminURL}customer-groups/view/?id={$page->id}'>{$page->title}</a>";
		}
		return $out;

		# +++++++
		// TODO: SHOULD BE ABLE TO VIEW EVEN IF LOCKED!
		$adminURL = $this->options['admin_url'];
		// TODO: CHANGE TITLE HERE TO ORDER NUMBER!
		// $out = "<a href='{$adminURL}orders/view/?id={$page->id}'>{$page->title}</a>";
		$out = "<a href='{$adminURL}customer-groups/view/?id={$page->id}'>{$page->id}</a>";
		return $out;
	}

	/**
	 * Render single customer group view headline to append to the Process headline in PWCommerce.
	 *
	 * @param Page $customerGroupPage
	 * @return string|mixed
	 */
	public function renderViewItemHeadline(Page $customerGroupPage) {
		$headline = $this->_('View customer group');
		// TODO MORE? TITLE?!
		$headline .= ": {$customerGroupPage->title}";
		return $headline;
	}

	/**
	 * Render the markup for a single customer group view.
	 *
	 * @param Page $customerGroupPage
	 * @return string|mixed
	 */
	public function renderViewItem(Page $customerGroupPage) {

		$this->customerGroupPage = $customerGroupPage;

		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		$out = "";
		// get the customer group by its ID
		if (!$customerGroupPage->id) {
			// TODO: return in markup for consistency!
			$out = "<p>" . $this->_('Customer group was not found!') . "</p>";
		} else {
			$out = $this->buildViewCustomerGroup();
		}

		//----------------Customer-roupgenerate final markup
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			// 'classes' => 'pwcommerce_order_view',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		$wrapper->add($field);

		return $wrapper->render();
	}

	/**
	 * Build View Customer Group.
	 *
	 * @return mixed
	 */
	private function buildViewCustomerGroup() {
		$out = "<p>STILL WORKING ON THIS GUI! WE WILL SHOW CRITERIA HERE + MATCHED CUSTOMERS BUT LIMIT THESE? ALTHOUGH USING FINDARRAY.</p>";
		return $out;
		// ====
		$out =
			// customer group (main)
			$this->renderXXXMain() .
			"<hr>" .
			// criteria: customers
			$this->renderXXXCustomerCriteria() .
			// "<hr>" .
			// criteria: order
			$this->renderXXXOrderCriteria();

		// -------
		return $out;
	}


	// ~~~~~~~~~~



	/**
	 * Get Results Table Headers.
	 *
	 * @return mixed
	 */
	protected function getResultsTableHeaders() {
		return [
			// TITLE
			// TODO: make these classes generic? e.g. for th percent width?
			[$this->_('Title'), 'pwcommerce_customer_groups_table_title'],
			// DESCRIPTION
			[$this->_('Description'), 'pwcommerce_customer_groups_table_description'],
			// USAGE
			[$this->_('Usage'), 'pwcommerce_customer_groups_table_usage'],
		];
	}

	/**
	 * Get No Results Table Records.
	 *
	 * @return mixed
	 */
	protected function getNoResultsTableRecords() {
		$noResultsTableRecords = $this->_('No customer groups found.');
		return $noResultsTableRecords;
	}

	/**
	 * Get Results Table Row.
	 *
	 * @param Page $page
	 * @param mixed $editItemTitle
	 * @return mixed
	 */
	protected function getResultsTableRow($page, $editItemTitle) {
		// get the count of products referencing this customer group
		$referencingCustomersCount = $page->references(true)->count;
		$referencingCustomersCountString = !empty($referencingCustomersCount) ? $referencingCustomersCount : $this->_('Customer Group not in use');
		$row = [
			// TITLE
			$editItemTitle,
			// DESCRIPTION
			$this->getCustomerGroupDescription($page), // @note: truncated!
			// USAGE TODO: CUSTOMERS REFERENCING THIS CUSTOMER GROUP
			$referencingCustomersCountString,

		];
		return $row;
	}



	/**
	 * Get Customer Group Description.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getCustomerGroupDescription($page) {
		$description = $page->get(PwCommerce::DESCRIPTION_FIELD_NAME);
		$out = $this->wire('sanitizer')->truncate($description, 150);
		// -----
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
			'add_new_item_label' => $this->_('Add new customer group'),
			// add new url
			'add_new_item_url' => "{$adminURL}customer-groups/add/",
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
				// IS UNUSED IN CUSTOMERS
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
		// SELECT data as customer_group_id
		// FROM field_pwcommerce_customer_groups
		// GROUP BY customer_group_id

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::CUSTOMER_GROUPS_FIELD_NAME,
			'select_columns' => ['data AS customer_group_id'],
			'group_by_columns' => ['customer_group_id']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);

		if (!empty($results)) {
			$customerGroupsIDs = array_column($results, 'customer_group_id');
			$customerGroupsIDsSelector = implode("|", $customerGroupsIDs);
			// NOTE: we want IDs of unused customer groups!
			$selector = ",id!={$customerGroupsIDsSelector}";
		}

		// ----
		return $selector;

	}


}