<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Discounts
 *
 * Class to render content for PWCommerce Admin Module executeDiscounts().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderDiscounts for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */


class PWCommerceAdminRenderDiscounts extends WireData
{

	# ----------
	// the ALPINE JS store used by this Class
	private $xstoreProcessPWCommerce;
	// the full prefix to the ALPINE JS store used by this Class
	private $xstore;

	/**
	 *   construct.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __construct($options = null) {
		if (is_array($options)) {
			$this->xstoreProcessPWCommerce = $options['xstoreProcessPWCommerce'];
			// i.e., '$store.ProcessPWCommerceStore'
			$this->xstore = $options['xstore'];
		}
	}


	/**
	 * Get Results Table Headers.
	 *
	 * @return mixed
	 */
	protected function getResultsTableHeaders() {
		return [
			// TITLE
			[$this->_('Title'), 'pwcommerce_discounts_table_title'],
			// STATUS
			[$this->_('Status'), 'pwcommerce_discounts_table_status'],
			// METHOD
			[$this->_('Method'), 'pwcommerce_discounts_table_method'],
			// TYPE
			[$this->_('Type'), 'pwcommerce_discounts_table_type'],
			// USAGE
			[$this->_('Usage'), 'pwcommerce_discounts_table_usage'],
		];
	}

	/**
	 * Get No Results Table Records.
	 *
	 * @return mixed
	 */
	protected function getNoResultsTableRecords() {
		$noResultsTableRecords = $this->_('No discounts found.');
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

		// ---------
		// discount method strings
		$discountMethodAutomatic = $this->_('Automatic');
		$discountMethodCode = $this->_('Code');
		// set each row

		$discount = $page->get(PwCommerce::DISCOUNT_FIELD_NAME);
		$discountMethod = $discount->isAutomatic ? $discountMethodAutomatic : $discountMethodCode;
		// ---------
		// @NOTE: we count the number of times this discount ID appears in the table 'pwcommerce_order-discounts' in the subfield 'discount_id'
		// get the count of orders using this discount
		// $discountUsageCount = $this->getDiscountUsageInOrdersCount($page);
		$discountUsageCount = $discount->discountGlobalUsage;
		$discountLimitTotal = $discount->discountLimitTotal;
		if (!empty($discountUsageCount)) {
			// if limit total, we shoud usage over limit
			// e.g. 10/50
			if (!empty($discountLimitTotal)) {
				$class = (int) $discountUsageCount === (int) $discountLimitTotal ? " class='pwcommerce_error'" : '';
				$discountUsageCountString = "<span{$class}>{$discountUsageCount}&sol;{$discountLimitTotal}</span>";
			} else {
				// no limit total; just show usage
				$discountUsageCountString = "<span>{$discountUsageCount}</span>";
			}
		} else {
			$discountUsageCountString = $this->_('Discount not used on any order');
		}

		$row = [
			// TITLE
			$editItemTitle,
			// STATUS
			$this->getDiscountStatusString($page, $discount),
			// METHOD
			$discountMethod,
			// TYPE
			$this->getDiscountTypeString($discount),
			// USAGE TODO: ORDERS WHERE THIS DISCOUNT HAS BEEN USED
			$discountUsageCountString,

		];
		return $row;
	}


	/**
	 * Get Discount Type String.
	 *
	 * @param WireData $discount
	 * @return mixed
	 */
	private function getDiscountTypeString(WireData $discount) {

		$amountOffProducts = $this->_('Amount off Products');
		$discountType = $discount->discountType;
		$label = $this->_('Unknown Discount Type');
		$description = '';

		// discountAppliesToType ; discountType
		if ($discountType === 'free_shipping') {
			// FREE SHIPPING
			$label = $this->_('Free shipping');
			$description = $this->_('Shipping discount');
		} else if (in_array($discountType, ['categories_get_y', 'products_get_y'])) {
			// BUY X GET Y
			$label = $this->_('Buy X Get Y');
			// ---------
			if ($discountType === 'categories_get_y') {
				// GET Y of Category
				$description = $this->_('Categories discount');
			} else {
				// GET Y of Product
				$description = $this->_('Products discount');
			}
		} else if (in_array($discountType, ['whole_order_percentage', 'whole_order_fixed'])) {
			// ORDER DISCOUNT
			$label = $this->_('Amount off Order');
			// ---------
			if ($discountType === 'whole_order_percentage') {
				// Order Percentage
				$description = $this->_('Order percentage discount');
			} else {
				// Order Fixed
				$description = $this->_('Order fixed discount');
			}
		} else if (in_array($discountType, ['products_percentage', 'products_fixed_per_order', 'products_fixed_per_item'])) {
			// PRODUCT DISCOUNT
			$label = $amountOffProducts;
			// ---------
			if ($discountType === 'products_percentage') {
				// Product Percentage
				$description = $this->_('Products percentage discount');
			} else if ($discountType === 'products_fixed_per_order') {
				// Product Fixed, Per Order
				$description = $this->_('Products fixed discount per order');
			} else {
				// Product Fixed, Per Item
				$description = $this->_('Products fixed discount per item');
			}
		} else if (in_array($discountType, ['categories_percentage', 'categories_fixed_per_order', 'categories_fixed_per_item'])) {
			// CATEGORY DISCOUNT
			// $label = $amountOffProducts;
			$label = $this->_('Amount off Categories');
			// ---------
			if ($discountType === 'categories_percentage') {
				// Category Percentage
				$description = $this->_('Categories percentage discount');
			} else if ($discountType === 'categories_fixed_per_order') {
				// Category Fixed, Per Order
				$description = $this->_('Categories fixed discount per order');
			} else {
				// Category Fixed, Per Item
				$description = $this->_('Categories fixed discount per item');
			}
		}

		# =============

		$out = "<span class='block'>{$label}</span>" .
			"<small>{$description}</small>";
		//--------
		return $out;

	}

	/**
	 * Get Discount Status String.
	 *
	 * @param Page $page
	 * @param WireData $discount
	 * @return mixed
	 */
	private function getDiscountStatusString(Page $page, WireData $discount) {
		$startDateTimestamp = (int) $discount->discountStartDate;
		$endDateTimestamp = (int) $discount->discountEndDate;
		$currentTime = time();

		$discountStatusStrings = [
			'active' => $this->_('Active'),
			'scheduled' => $this->_('Scheduled'),
			'expired' => $this->_('Expired'),
			'used_up' => $this->_('Used up'),
			'inactive' => $this->_('Inactive'),
		];
		$out = "";

		if ($page->isUnpublished()) {
			// INACTIVE DISCOUNT; MAYE EVEN EXPIRED?
			$out = $discountStatusStrings['inactive'];
		} else {
			if ($startDateTimestamp > $currentTime) {
				// Scheduled
				$out = $discountStatusStrings['scheduled'];
			} elseif (($startDateTimestamp < $currentTime && $endDateTimestamp > $currentTime) || ($startDateTimestamp < $currentTime && $endDateTimestamp < 1)) {
				// ACTIVE TODO ADD OTHER CONDITIONS, E.G. LIMIT VS GLOBAL USAGE!
				$out = $discountStatusStrings['active'];
			} else if ($endDateTimestamp > 1 && $endDateTimestamp < $currentTime) {
				// EXPIRED
				$out = $discountStatusStrings['expired'];
			}
		}

		// ----
		return $out;
	}

	/**
	 * Get Bulk Edit Actions Panel.
	 *
	 * @param mixed $adminURL
	 * @return mixed
	 */
	protected function getBulkEditActionsPanel($adminURL) {

		$label = $this->_('Add new discount');
		$extraCustomMarkup = "<a href='#' @click='handleAddNewDiscount'><i class='fa fa-plus-circle'></i> {$label}</a>";
		$extraCustomMarkup .= $this->getModalMarkupForCreateDiscount();

		//////////////////////

		$actions = [
			'publish' => $this->_('Publish'),
			'unpublish' => $this->_('Unpublish'),
			'lock' => $this->_('Lock'),
			'unlock' => $this->_('Unlock'),
			'trash' => $this->_('Trash'),
			'delete' => $this->_('Delete'),
		];

		$options = [
			// @NOTE: CANNOT CREATE A GIFT CARD!
			// however, can create a gift card product!
			// this is a link to view, create and edit gift card products
			// left side content extra custom markup
			// 'extra_custom_markup' => $viewGiftCardProductsURLMarkup,
			'extra_custom_markup' => $extraCustomMarkup,
			// extra custom markup will be used
			'is_extra_custom_markup' => true,
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}



	//////////////////

	/**
	 * Modal for MANUAL issue of Gift Cards.
	 *
	 * @return mixed
	 */
	private function getModalMarkupForCreateDiscount() {
		// ## CREATE DISCOUNT SELECT TYPE MODALs MARKUP  ##

		$header = $this->_("Select Discount Type");
		$createDiscountSelectTypeModalProperty = "is_create_discount_select_type_modal_open";
		// =======
		// HTMX
		// @note: NOT IN USE FOR NOW

		$body =
			"<div>" .
			"<div id='pwcommerce_create_discount'>" .
			$this->getAddDiscountSelectTypeMarkup() .
			"</div>" .
			// ++++++++
			"</div>"; // end div with x-init
		// ==================================
		// apply button
		$applyButton = $this->renderModalMarkupForCreateDiscountAddButton();
		// cancel button
		$cancelButton = $this->renderModalMarkupForCreateDiscountCancelButton();
		$footer = "<div class='ui-dialog-buttonset'>{$applyButton}{$cancelButton}</div>";
		$xproperty = $createDiscountSelectTypeModalProperty;
		$size = '5x-large';

		// wrap content in modal for activating/deactivating
		// modal options
		$options = [
			// $header The modal title pane markup.
			'header' => $header,
			// $body The main content markup.
			'body' => $body,
			// $footer The footer markup.
			'footer' => $footer,
			// $xstore The alpinejs store with the property that will be modelled to show/hide the modal.
			'xstore' => $this->xstoreProcessPWCommerce,
			// $xproperty The alpinejs property that will be modelled to show/hide the modal.
			'xproperty' => $xproperty,
			// $size The size of the modal requested.
			'size' => $size,
		];
		$out = $this->pwcommerce->getModalMarkup($options);

		return $out;
	}

	/**
	 * Get Add Discount Select Type Markup.
	 *
	 * @return mixed
	 */
	private function getAddDiscountSelectTypeMarkup() {
		$selectDiscountTypeMarkup = $this->getMarkupForDiscountSelectTypeParts();
		//----------------
		$out =
			"<div id='pwcommerce_create_discount_wrapper' x-data='ProcessPWCommerceData'>" .

			########
			// FORM INPUTS
			$selectDiscountTypeMarkup .
			// --------
			// end div#pwcommerce_create_discount_wrapperbox
			"</div>";
		// -----------
		return $out;
	}

	/**
	 * Get Markup For Discount Select Type Parts.
	 *
	 * @return mixed
	 */
	private function getMarkupForDiscountSelectTypeParts() {

		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		$discountTypes = [
			// products discount
			[
				'value' => 'amount_off_products',
				'label' => $this->_('Amount off Products'),
				'description' => $this->_('Product discount')
			],
			// order discount
			[
				'value' => 'amount_off_order',
				'label' => $this->_('Amount off Order'),
				'description' => $this->_('Order discount')
			],
			// buy x get y discount
			[
				'value' => 'buy_x_get_y',
				'label' => $this->_('Buy X Get Y'),
				'description' => $this->_('Product discount'),
				'notes' => $this->_('Experimental. Not for use in production!'),
			],
			// shipping discount
			[
				'value' => 'free_shipping',
				'label' => $this->_('Free Shipping'),
				'description' => $this->_('Shipping discount')
			],
		];

		$out = "<div id='pwcommerce_select_discount_type_wrapper'>" .
			"<p class='description'>" . $this->_('Please select the type of discount you want to create.') . "</p>" .
			"<hr>";

		// build the discount type choices
		$cnt = 0;
		$count = count($discountTypes);
		foreach ($discountTypes as $discountType) {

			if (!$this->wire('user')->isSuperuser() && $discountType['value'] === 'buy_x_get_y') {
				// SKIP FOR NON-SUPS
				// TODO - REMOVE WHEN BOGO FEATURE IS COMPLETED
				continue;
			}

			$notes = "";
			if (isset($discountType['notes'])) {
				$notes = "<small class='notes block mt-1'>{$discountType['notes']}</small>";
			}

			$value = $discountType['value'];
			$label = $discountType['label'];
			$description = $discountType['description'];

			// ----------
			$out .=
				"<div class='cursor-pointer' @click='handleSetDiscountType(`{$value}`)' 		:class='isSelectedDiscountType(`{$value}`) ? `font-bold` : ``'>" .
				"<span class='block'>{$label}</span>" .
				"<small>{$description}</small>" .
				$notes .
				"</div>";
			$cnt++;
			if ($cnt < $count) {
				$out .= "<hr class='block'>";
			}
		}

		// div#pwcommerce_select_discount_type_wrapper
		$out .= "</div>";

		//------------------
		// generate markup for selecting discount types + showing info
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

		//------------------- is_need_pre_process (getInputfieldHidden)
		// lets ProcessPwCommerce::pagesHandler() know that this context need to do some pre-procesing before creating an item
		// in this case, we need to grab the discount type then create a blank discount of that type then redirect to edit it
		$options = [
			'id' => "pwcommerce_is_need_pre_process",
			'name' => 'pwcommerce_is_need_pre_process',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];

		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		// the input for the discount type
		$options = [
			'id' => "pwcommerce_create_discount_type",
			'name' => 'pwcommerce_create_discount_type',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];

		$field = $this->pwcommerce->getInputfieldHidden($options);
		$field->attr([
			'x-model' => "{$this->xstore}.create_discount_type"
		]);

		$wrapper->add($field);

		// ---
		$out = $wrapper->render();

		// ------
		return $out;

	}

	/**
	 * Render Modal Markup For Create Discount Add Button.
	 *
	 * @return string|mixed
	 */
	private function renderModalMarkupForCreateDiscountAddButton() {
		# ALPINE JS #
		$xstore = $this->xstore;
		$applyButtonOptions = [
			'type' => 'submit',
			// 'type' => 'button',
			'name' => 'pwcommerce_create_discount_type_submit',
			# ALPINE JS #
			'x-bind:disabled' => "!{$xstore}.create_discount_type",
			'x-bind:class' => "{$xstore}.create_discount_type ? `` : `opacity-50`",
		];

		// -----------
		$applyButton = $this->pwcommerce->getModalActionButton($applyButtonOptions);

		// ===========
		return $applyButton;
	}
	/**
	 * Get rendered button for the modal for actioning a selected order status.
	 *
	 * @return string
	 */
	private function renderModalMarkupForCreateDiscountCancelButton(): string {
		$cancelButton = $this->pwcommerce->getModalActionButton(['x-on:click' => 'resetDiscountSelectTypeAndCloseModal'], 'cancel');
		return $cancelButton;
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
			'active' => $this->_('Active'),// published + start date and end date
			'scheduled' => $this->_('Scheduled'),// start date + today
			'expired' => $this->_('Expired'),// end date + today
			'draft' => $this->_('Draft'),// unpublished/inactive
			// usage
			'unused' => $this->_('Unused'),
			'least_used' => $this->_('Least Used'), // TODO HOW?
			'most_used' => $this->_('Most Used'),
			// TODO rephrase?
			// TODO NEEDS THRESHOLD
			'nearly_used_up' => $this->_('Few Remaining'),
			'used_up' => $this->_('Used up'),
			// type
			'order_discount' => $this->_('Order'),
			'free_shipping_discount' => $this->_('Free Shipping'),
			'categories_discount' => $this->_('Categories'),
			'products_discount' => $this->_('Products'),
			'buy_x_get_y_discount' => $this->_('Buy X Get Y'),

		];

		if (!$this->wire('user')->isSuperuser()) {
			// SKIP FOR NON-SUPS
			// TODO - REMOVE WHEN BOGO FEATURE IS COMPLETED
			unset($filters['buy_x_get_y_discount']);
		}
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
			if (in_array($quickFilterValue, ['active', 'scheduled', 'expired', 'draft'])) {
				// ACTIVE/SCHEDULED/EXPIRED/INACTIVE OR DRAFT (UNPUBLISHED)
				$selector = $this->getSelectorForQuickFilterActive($quickFilterValue);
			} elseif (in_array($quickFilterValue, ['unused', 'least_used', 'most_used', 'nearly_used_up', 'used_up'])) {
				// USAGE
				$selector = $this->getSelectorForQuickFilterUsage($quickFilterValue);
			} else if (in_array($quickFilterValue, ['order_discount', 'free_shipping_discount', 'categories_discount', 'products_discount', 'buy_x_get_y_discount'])) {
				// TYPE
				$selector = $this->getSelectorForQuickFilterDiscountType($quickFilterValue);
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

		$currentTime = date('Y-m-d H:i:s', time());
		$epochTime = date('Y-m-d H:i:s', 1);


		$selector = '';
		if ($quickFilterValue === 'active') {
			// ACTIVE: published + not expired
			// ($startDateTimestamp < $currentTime && $endDateTimestamp > $currentTime) || ($startDateTimestamp < $currentTime && $endDateTimestamp < 1)
			$activeSelectorArray = [
				",status<" . Page::statusUnpublished,
				"(" . PwCommerce::DISCOUNT_FIELD_NAME . ".active_from<{$currentTime}," . PwCommerce::DISCOUNT_FIELD_NAME . ".active_to>{$currentTime})",
				"(" . PwCommerce::DISCOUNT_FIELD_NAME . ".active_from<{$currentTime}," . PwCommerce::DISCOUNT_FIELD_NAME . ".active_to<{$epochTime})"
			];
			// $selector = ",status<" . Page::statusUnpublished;
			$selector = implode(",", $activeSelectorArray);

		} else if ($quickFilterValue === 'scheduled') {
			// SCHEDULED: published + future
			$selector = "," . PwCommerce::DISCOUNT_FIELD_NAME . ".active_from>{$currentTime},status<" . Page::statusUnpublished;
			// $startDateTimestamp > $currentTime
		} else if ($quickFilterValue === 'expired') {
			// EXPIRED: past
			// $endDateTimestamp > $epochTime && $endDateTimestamp < $currentTime
			$selector = "," . PwCommerce::DISCOUNT_FIELD_NAME . ".active_to>{$epochTime}," . PwCommerce::DISCOUNT_FIELD_NAME . ".active_to<{$currentTime}";
		} else if ($quickFilterValue === 'draft') {
			// INACTIVE: unpublished
			$selector = ",status>=" . Page::statusUnpublished;
		}

		// ----
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter Usage.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterUsage($quickFilterValue) {
		$selector = '';
		// -------
		if ($quickFilterValue === 'unused') {
			// UNUSED
			$selector = $this->getSelectorForQuickFilterUnused();
		} else if (in_array($quickFilterValue, ['least_used', 'most_used'])) {
			// LEAST OR MOST USED
			$selector = $this->getSelectorForQuickFilterLeastOrMostUsed($quickFilterValue);
		} else if ($quickFilterValue === 'nearly_used_up') {
			// NEARLY USED UP
			$selector = $this->getSelectorForQuickFilterNearlyUsedUp();
		} else if ($quickFilterValue === 'used_up') {
			// USED UP
			$selector = $this->getSelectorForQuickFilterUsedUp();
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
		// SELECT discount_id
		// FROM field_pwcommerce_order_discounts
		// GROUP BY discount_id

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::ORDER_DISCOUNTS_FIELD_NAME,
			'select_columns' => ['discount_id'],
			'group_by_columns' => ['discount_id']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);

		if (!empty($results)) {
			$discountsIDs = array_column($results, 'discount_id');
			$discountsIDsSelector = implode("|", $discountsIDs);
			// NOTE: we want IDs of unused disounts!
			$selector = ",id!={$discountsIDsSelector}";
		}


		// ----
		return $selector;

	}

	/**
	 * Get Selector For Quick Filter Least Or Most Used.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterLeastOrMostUsed($quickFilterValue) {



		// e.g.
		// -- least used
		// SELECT COUNT(discount_id), data AS discount_code
		// FROM field_pwcommerce_order_discounts
		// GROUP BY discount_code
		// ORDER BY COUNT(discount_id)
		// LIMIT 10

		// -- most used
		// SELECT COUNT(discount_id), data AS discount_code
		// FROM field_pwcommerce_order_discounts
		// GROUP BY discount_code
		// ORDER BY COUNT(discount_id) DESC
		// LIMIT 10

		$selector = '';
		// NOTE: the 'discount_code' in SELECT and GROUPBY is just for debugging
		$queryOptions = [
			'table' => PwCommerce::ORDER_DISCOUNTS_FIELD_NAME,
			'select_columns' => ['data AS discount_code', 'discount_id'],
			'count' => [
				'count_column' => 'discount_id',
				'counted_column_name' => 'discount_total',
			],
			'group_by_columns' => ['discount_code', 'discount_id'],
			'order_by_count_column' => 'discount_code',
		];

		if ($quickFilterValue === 'most_used') {
			// MOST USED
			$queryOptions['order_by_descending'] = true;
			$limit = PwCommerce::PWCOMMERCE_HIGH_DISCOUNT_USAGE_THRESHOLD;
		} else {
			// LEAST USED
			$limit = PwCommerce::PWCOMMERCE_LOW_DISCOUNT_USAGE_THRESHOLD;
		}

		$queryOptions['limit'] = $limit;

		$results = $this->pwcommerce->processQueryGroupByCount($queryOptions);

		if (!empty($results)) {
			$discountsIDs = array_column($results, 'discount_id');
			$discountsIDsSelector = implode("|", $discountsIDs);
			$selector = ",id={$discountsIDsSelector}";
		}


		// ----
		return $selector;

	}

	/**
	 * Get Selector For Quick Filter Nearly Used Up.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterNearlyUsedUp() {
		// e.g.
		// 	SELECT
		// 		pages_id AS discount_id, code,
		// 		 SUM(cast(limit_total AS signed) - cast(global_usage AS signed)) remaining_unused
		//  FROM
		// 		 field_pwcommerce_discount
		//  -- ignore discounts without total limit usage
		//  WHERE limit_total > 0
		//  GROUP BY
		// 		 discount_id,code
		//  -- filter to threshold
		//  HAVING
		// 		 -- SUM(cast(limit_total AS signed) - cast(global_usage AS signed)) < 10
		// 		 remaining_unused < 10
		//  ORDER BY
		// 		 remaining_unused;

		$selector = "";

		$queryOptions = [
			'table' => PwCommerce::DISCOUNT_FIELD_NAME,
			'select_columns' => ['pages_id AS discount_id', 'code'],
			// sum
			'sum' => [
				// cast to avoid BIGINT out of range error
				'expression' => "cast(limit_total AS signed) - cast(global_usage AS signed)",
				'summed_column_name' => 'remaining_unused',
			],
			// where
			'conditions' => [
				// limit_total > 0
				[
					'column_name' => "limit_total",
					'operator' => '>',
					'column_value' => 0,
					'column_type' => 'int',
					// i.e. parameter name of the form :name
					// NOTE: excluding since no ambiguity. TraitPWCommerceDatabase::getGroupByQuery will default to 'column_name'
					// 'param_identifier' => 'limit_total',
				],
				// limit_total > global_usage
				// i.e., limit not reach yet
				[
					'column_name' => "limit_total",
					'operator' => '>',
					'column_value' => 'global_usage',
					'column_type' => 'string',
					// i.e. parameter name of the form :name
					// NOTE: excluding since no ambiguity. TraitPWCommerceDatabase::getGroupByQuery will default to 'column_name'
					// 'param_identifier' => 'global_usage',
					'skip_bind' => true,
				],
			],

			// group
			'group_by_columns' => ['discount_id', 'code'],
			// having
			'having' => [
				//  filter to threshold
				'expression' => "remaining_unused < " . PwCommerce::PWCOMMERCE_DISCOUNT_NEARLY_USED_UP_THRESHOLD,
			],
			// order by
			'order_by_columns' => ['remaining_unused'],
		];

		$results = $this->pwcommerce->processQueryGroupBySumHaving($queryOptions);

		if (!empty($results)) {
			$discountsIDs = array_column($results, 'discount_id');
			$discountsIDsSelector = implode("|", $discountsIDs);
			$selector = ",id={$discountsIDsSelector}";
		}


		// ----
		return $selector;

	}

	/**
	 * Get Selector For Quick Filter Used Up.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterUsedUp() {
		// e.g.
		// SELECT pages_id as discount_id, code, global_usage, limit_total
		// FROM field_pwcommerce_discount
		// WHERE global_usage >= limit_total
		// AND limit_total > 0

		// $tablePrefix = "field_";
		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::DISCOUNT_FIELD_NAME,
			'select_columns' => ['pages_id AS discount_id', 'code', 'global_usage', 'limit_total'],
			// WHERE
			'conditions' => [
				// global_usage >= limit_total
				[
					'column_name' => "global_usage",
					'operator' => '>=',
					'column_value' => 'field_pwcommerce_discount.limit_total',
					'column_type' => 'string',
					// i.e. parameter name of the form :name
					// NOTE: excluding since no ambiguity. TraitPWCommerceDatabase::getGroupByQuery will default to 'column_name'
					// 'param_identifier' => 'global_usage',
					'skip_bind' => true,
				],
				// limit_total > 0
				[
					'column_name' => "limit_total",
					'operator' => '>',
					'column_value' => 0,
					'column_type' => 'int',
					// i.e. parameter name of the form :name
					// NOTE: excluding since no ambiguity. TraitPWCommerceDatabase::getGroupByQuery will default to 'column_name'
					// 'param_identifier' => 'limit_total',
				],
			],
		];

		$results = $this->pwcommerce->processQuerySelect($queryOptions);

		if (!empty($results)) {
			$discountsIDs = array_column($results, 'discount_id');
			$discountsIDsSelector = implode("|", $discountsIDs);
			$selector = ",id={$discountsIDsSelector}";
		}




		// ----
		return $selector;

	}

	/**
	 * Get Selector For Quick Filter Discount Type.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterDiscountType($quickFilterValue) {

		$selector = "," . PwCommerce::DISCOUNT_FIELD_NAME . ".discount_type=";
		if ($quickFilterValue === 'order_discount') {
			// ORDER DISCOUNTS
			$selector .= "whole_order_percentage|whole_order_fixed";
		} else if ($quickFilterValue === 'free_shipping_discount') {
			// FREE SHIPPING
			$selector .= "free_shipping";
		} else if ($quickFilterValue === 'categories_discount') {
			// CATEGORIES DISCOUNTS
			$selector .= "categories_percentage|categories_fixed_per_order|categories_fixed_per_item";
		} else if ($quickFilterValue === 'products_discount') {
			// PRODUCTS DISCOUNTS
			$selector .= "products_percentage|products_fixed_per_order|products_fixed_per_item";
		} else if ($quickFilterValue === 'buy_x_get_y_discount') {
			// BOGO DISCOUNTS
			$selector .= "categories_get_y|products_get_y";
		}

		// ----
		return $selector;

	}

}