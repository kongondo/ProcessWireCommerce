<?php

namespace ProcessWire;

/**
 * PWCommerce: Process Render Gift Card Products
 *
 * Class to render content for PWCommerce Process Module executePaymentProviders().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceProcessRenderGiftCardProducts for PWCommerce
 * Copyright (C) 2023 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceProcessRenderGiftCardProducts extends WireData
{






	private $options = [];
	private $context;



	/**
	 *   construct.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __construct($options = null) {
		parent::__construct();
		// TODO????
		if (is_array($options)) {
			$this->options = $options;
		}

		//-----------


	}

	/**
	 * Render Results.
	 *
	 * @param mixed $selector
	 * @return string|mixed
	 */
	public function renderResults($selector = null) {

		// enforce to string for strpos for PHP 8+
		$selector = strval($selector);

		//-----------------
		// FORCE DEFAULT LIMIT IF NO USER LIMIT SET
		if (strpos($selector, 'limit=') === false) {
			$limit = 10;
			$selector = rtrim("limit={$limit}," . $selector, ",");
		}

		//------------
		// FORCE TEMPLATE TO MATCH PWCOMMERCE GIFT CARD PRODUCTS ONLY + INCLUDE ALL + EXLUDE TRASH
		$selector .= ",template=" . PwCommerce::GIFT_CARD_PRODUCT_TEMPLATE_NAME . ",include=all,status<" . Page::statusTrash;
		//------------
		// ADD START IF APPLICABLE (ajax pagination)
		$classOptions = $this->options;
		if (!empty($classOptions['selector_start'])) {
			$start = (int) $classOptions['selector_start'];

			$selector .= ",start={$start}";
		}

		//-----------------------

		// TODO: work on this! e.g. inlude all???

		// TODO: for future: need to add variants! i.e. their child pages, if applicable - same for orders - need order items!

		$pages = $this->wire('pages')->find($selector);

		//-----------------

		// BUILD FINAL MARKUP TO RETURN TO ProcessPwCommerce::pagesHandler()
		// @note: important: don't remove the class 'pwcommerce_inputfield_selector'! we need it for htmx (hx-include)
		$out =
			"<div id='pwcommerce_bulk_edit_custom_lister' class='pwcommerce_inputfield_selector pwcommerce_show_highlight mt-5'>" .
			// BULK EDIT ACTIONS
			$this->getBulkEditActionsPanel() .
			// PAGINATION STRING (e.g. 1 of 25)
			"<h3 id='pwcommerce_bulk_edit_custom_lister_pagination_string'>" . $pages->getPaginationString('') . "</h3>" .
			// TABULATED RESULTS (if pages found, else 'none found' message is rendered)
			$this->getResultsTable($pages) .
			// HIDDEN INPUT FOR HTMX
			// set the context for differentiation when in ajax page
			"<input type='hidden' value='gift-card-products' name='pwcommerce_inputfield_selector_context'>" .
			// PAGINATION (render the pagination navigation)
			$this->pwcommerce->getPagination($pages, $this->paginationOptions()) .
			//---------------
			"</div>";

		return $out;
	}

	/**
	 * Get the options for building the form to add a new Gift Card Product for use in ProcessPWCommerce.
	 *
	 * @return mixed
	 */
	public function getAddNewItemOptions() {
		return [
			'label' => $this->_('Gift Card Product Title'),
			'headline' => $this->_('Add New Gift Card Product'),
		];
	}

	/**
	 * Pagination Options.
	 *
	 * @return mixed
	 */
	public function paginationOptions() {
		$adminURL = '';
		// TODO: WE WILL ALWAYS HAVE AN ADMIN URL, BUT JUST SANITY CHECK!
		$classOptions = $this->options;
		if (!empty($classOptions['admin_url'])) {
			$adminURL = $classOptions['admin_url'];
		}
		//------------
		$paginationOptions = ['base_url' => $adminURL . 'gift-card-products/', 'ajax_post_url' => $adminURL . 'ajax/'];
		return $paginationOptions;
	}

	/**
	 * Get Custom Lister Settings.
	 *
	 * @return mixed
	 */
	public function getCustomListerSettings() {
		return [
			'label' => $this->_('Filter Gift Card Products'),
			'inputfield_selector' => [
				'initValue' => "template=" . PwCommerce::GIFT_CARD_PRODUCT_TEMPLATE_NAME,
				'initTemplate' => PwCommerce::GIFT_CARD_PRODUCT_TEMPLATE_NAME,
				'showFieldLabels' => true,
			],

			// TODO; add columns!!!!
		];
	}

	/**
	 * Get Results Table Headers.
	 *
	 * @return mixed
	 */
	private function getResultsTableHeaders() {
		// TODO: DO WE USE TW CLASSES HERE?
		$selectAllCheckboxName = "pwcommerce_bulk_edit_selected_items_all";
		$xref = 'pwcommerce_bulk_edit_selected_items_all';
		return [
			// SELECT ALL CHECKBOX
			$this->getBulkEditCheckbox('all', $selectAllCheckboxName, $xref),
			// THUMB
			[$this->_('Image'), 'pwcommerce_gift_card_products_table_image'],
			// TITLE
			// TODO: make these classes generic? e.g. for th percent width?
			[$this->_('Title'), 'pwcommerce_gift_card_products_table_title'],
			// USAGE
			[$this->_('Denominations'), 'pwcommerce_gift_card_products_table_denominations'],
		];
	}

	//  /**
   * Get Results Table.
   *
   * @param array $items
   * @param array $headerRow
   * @param array $rows
   * @param array $options
   * @return mixed
   */
  public function getResultsTable($items, array $headerRow, array $rows, array $options = []) {
	/**
	 * Get Results Table.
	 *
	 * @param mixed $pages
	 * @return mixed
	 */
	private function getResultsTable($pages) {

		$out = "";
		if (!$pages->count()) {
			$out = "<p>" . $this->_('No gift card products found.') . "</p>";
		} else {
			$field = $this->modules->get('MarkupAdminDataTable');
			$field->setEncodeEntities(false);
			// set headers (th)
			$field->headerRow($this->getResultsTableHeaders());
			$checkBoxesName = "pwcommerce_bulk_edit_selected_items[]";
			// set each row
			foreach ($pages as $page) {
				// TODO - CHANGE THIS TO FIND ORDERS THAT HAVE HAD A GC FROM THIS GCP/GCPV REDEEMED? - WOULD NEED TO GET USING RAW! , BUT GETTING COUNT!
				// get the count of orders whose related GC was used in the payment
				// $reedemedInOrderCount = $page->references(true)->count;
				// $reedemedInOrderCountString = !empty($reedemedInOrderCount) ? $reedemedInOrderCount : $this->_('Gift card product not used in any order purchase. TODO MAYBE ADD 3 DENOMINATIONS, ETC, LIKE PRODUCT VARIANTS?');

				$row = [
					// CHECKBOX
					$this->getBulkEditCheckbox($page->id, $checkBoxesName),
					// THUMB
					$this->getGiftCardProductThumb($page),
					// TITLE
					$this->getEditItemTitle($page),
					// USAGE TODO: ORDRERS WHOSE PURCHASE WAS PAID (part or fully) USING THIS GCP's GCPV's Gift Card??
					// $reedemedInOrderCountString,
					$this->getDenominationsCount($page),

				];
				$field->row($row);
			}
			// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
			$out = $field->render();
		}
		return $out;
	}

	/**
	 * Get Edit Item Title.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getEditItemTitle($page) {
		// get the edit URL if item is unlocked
		$out = $this->getEditItemURL($page);
		// add published and locked status if applicable
		$status = [];
		if ($page->isLocked()) {
			$status[] = $this->_('locked');
		}

		if ($page->isUnpublished()) {
			$status[] = $this->_('unpublished');
		}

		$statusString = implode(', ', $status);
		if ($statusString) {
			$out .= "<small class='block italic mt-1'>{$statusString}</small>";
		}
		// $out = "<a href='{$adminURL}gift-card-products/edit/?id={$page->id}'>{$page->title}</a>";
		return $out;
	}

	/**
	 * Get Edit Item U R L.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getEditItemURL($page) {
		// TODO: CHECK IF UNLOCKED FIRST!
		$adminURL = $this->options['admin_url'];

		// if page is locked, don't show edit URL
		if ($page->isLocked()) {
			$out = "<span>{$page->title}</span>";
		} else {
			$out = "<a href='{$adminURL}gift-card-products/edit/?id={$page->id}'>{$page->title}</a>";
		}
		return $out;
	}

	/**
	 * Get Gift Card Product Thumb.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getGiftCardProductThumb($page) {
		$firstImage = $page->pwcommerce_images->first();

		// first image found
		if ($firstImage) {
			$class = "w-16 lg:w-24";
			// TODO: SET TIME LIMIT HERE TO 120?
			// TODO; IS WIDTH OR IS IT HEIGHT? HERE OK?
			$imageURL = $firstImage->height(260)->url; // TODO: USE CSS TO RENDER SMALLER!
		} else {
			$class = "w-12 opacity-25";
			$assetsURL = $this->options['assets_url'];
			$imageURL = $assetsURL . "icons/no-image-found.svg";
		}

		//---------------
		// TODO: DO WE NEED A DIV HERE?
		$out = "<img src='{$imageURL}' class='{$class}'>";
		return $out;
	}

	/**
	 * Get Denominations Count.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getDenominationsCount($page) {
		$stockFieldName = PwCommerce::PRODUCT_STOCK_FIELD_NAME;
		$selectorArray = [
			'template' => PwCommerce::GIFT_CARD_PRODUCT_VARIANT_TEMPLATE_NAME,
			'parent_id' => $page->id,
			"{$stockFieldName}.price>" => 0,
			"{$stockFieldName}.enabled" => 1,
			'status<' => Page::statusTrash

		];

		$fields = PwCommerce::PRODUCT_STOCK_FIELD_NAME;
		// $giftCardProductVariants = $this->wire('pages')->findRaw($selector, $fields);
		$count = $this->wire('pages')->count($selectorArray, $fields);

		$out = sprintf(_n("%d enabled denomination available.", "%d enabled denominations available.", $count), $count);
		// ----
		return $out;
	}

	/**
	 * Get Bulk Edit Actions Panel.
	 *
	 * @return mixed
	 */
	private function getBulkEditActionsPanel() {
		// TODO: wip!
		# TODO REVISIT THESE! CAN WE TRASH A GCP WHOSE GC IS ACTIVE? YES; SINCE THEY ARE DECOUPLED!
		# GC IS A STAND ALONE ITEM!
		$adminURL = $this->options['admin_url'];
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
			'add_new_item_label' => $this->_('Add new gift card product'),
			// add new url
			'add_new_item_url' => "{$adminURL}gift-card-products/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	/**
	 * Get Bulk Edit Checkbox.
	 *
	 * @param int $id
	 * @param mixed $name
	 * @param mixed $xref
	 * @return mixed
	 */
	private function getBulkEditCheckbox($id, $name, $xref = null) {
		$options = [
			'id' => "pwcommerce_bulk_edit_checkbox{$id}",
			'name' => $name,
			'label' => ' ',
			// @note: skipping label
			// 'label2' => $this->_('Use custom handling fee'),
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_bulk_edit_selected_items',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $id,

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		// TODO: ADD THIS ATTR AND MAYBE EVEN A x-ref? so we can selectall using alpinejs
		$field->attr([
			'x-on:change' => 'handleBulkEditItemCheckboxChange',
		]);

		return $field->render();
	}
}