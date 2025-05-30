<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Inventory
 *
 * Class to render content for PWCommerce Admin Module executeInventory().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderInventory for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */


class PWCommerceAdminRenderInventory extends WireData
{

	private $adminURL;
	private $ajaxPostURL;
	private $selectorStart;

	private $quickFilterSelectorArray;
	private $isQuickFilterShowVariantsOnly;
	private $quickFilterValue;

	public function __construct($options = null) {
		if (is_array($options)) {
			$this->adminURL = $options['admin_url'];
			$this->ajaxPostURL = $options['ajax_post_url'];
			if (!empty($options['selector_start'])) {
				$this->selectorStart = $options['selector_start'];
			}
		}

	}


	protected function renderResults($selector = null) {

		// INVENTORY VIEW REQUIRES A SPECIAL SELECTOR
		// @note: we need to include both variants and products without variants and exlude the parent products of the former. we also need filtering by title to work as if they were searching using titles for main products with variants, etc
		//------------

		$input = $this->wire('input');
		// pwcommerce_quick_filter_value
		if ($this->wire('config')->ajax && $input->pwcommerce_quick_filter_value) {
			// BULK VIEW QUICK FILTER
			// ----------
			// TRACK SELECTOR FOR QUICK FILTER
			// we'll need it later in $this->buildInventorySelector()
			$this->quickFilterSelectorArray = $this->getSelectorForQuickFilter();

		}

		// FORCE TEMPLATE TO MATCH PWCOMMERCE PRODUCTS OR PRODUCT VARIANTS ONLY MODIFY title and parent.title selectors as needed
		// @note: we use OR-groups selector here. The first group matches all products that don't use variants. The second group matches all product variants.
		$selector = $this->buildInventorySelector($selector);
		// + INCLUDE ALL + EXLUDE TRASH +
		$selector .= ",sort=parent.title,include=all,status<" . Page::statusTrash;

		//------------
		// ADD START IF APPLICABLE (ajax pagination)
		if (!empty($this->selectorStart)) {
			$start = (int) $this->selectorStart;
			$selector .= ",start={$start}";
		}

		//-----------------------

		// TODO: work on this! e.g. inlude all???

		// TODO: NEED TO FIGURE OUT HOW TO HANDLE FILTERING BY DATA, E.G. TITLE CAN MATCH THE PRODUCT ITSELF AND THAT WILL MATCH SINCE IT IS NOT INSIDE AN OR-GROUP AS PER OUR SELECTOR! NEED TO FIGURE OUT HOW TO REMOVE IT and add it to the first or group as title= and to the second one as parent.title=? OR parent.title|title=

		$pages = $this->wire('pages')->find($selector);

		// TODO: TESTING GETTING STOCK VALUES AND SENDING TO CLIENT FOR ALPINE MODEL SKU, QUANTITY, OVERSELL AND ENABLED IN ORDER FOR THOSE TO 'LIVE UPDATE' AFTER APPLY 'ACCEPT'
		// TODO: WILL IT UPDATE WITH AJAX FILTER OF INVENTORY???? I.E. INPUTFIELDSELECTOR
		// TODO: efficient?
		$inventoryStockValues = [];
		foreach ($pages as $page) {
			// $stockValues = $page->pwcommerce_product_stock->getArray();
			// $stockValues = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME)->getArray();
			$stockValues = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
			if (!empty($stockValues)) {
				$stockValuesArray = $stockValues->getArray();
				//$inventoryStockValues[] = $stockValues;
				// @note: so we get an Object in JavaScript instead of Array above
				$inventoryStockValues[$page->id] = $stockValuesArray;
			}

		}

		$script = "<script>ProcessWire.config." . $this . "=" . json_encode($inventoryStockValues) . ';</script>';

		//-----------------

		// BUILD FINAL MARKUP TO RETURN TO ProcessPwCommerce::pagesHandler()
		// @note: important: don't remove the class 'pwcommerce_inputfield_selector'! we need it for htmx (hx-include)
		$out =
			"<div id='pwcommerce_bulk_edit_custom_lister' class='pwcommerce_inputfield_selector pwcommerce_show_highlight mt-5'>" .
			// BULK EDIT ACTIONS
			$this->getBulkEditActionsPanel() .
			// PAGINATION STRING (e.g. 1 of 25)
			"<h3 id='pwcommerce_bulk_edit_custom_lister_pagination_string'>" . $pages->getPaginationString('') . "</h3>" .
			// SCRIPT INVENTORY CONFIG VALUES FOR ALPINE JS
			// @note: important that this appears before table below! otherwise not init on time!
			"<div x-init='initInventoryData'>" . $script . "</div>" .
			// TABULATED RESULTS (if pages found, else 'none found' message is rendered)
			$this->getResultsTable($pages) .
			// HIDDEN INPUT FOR HTMX
			// set the context for differentiation when in ajax page
			"<input type='hidden' value='inventory' name='pwcommerce_inputfield_selector_context'>" .
			// PAGINATION (render the pagination navigation)
			$this->pwcommerce->getPagination($pages, $this->paginationOptions()) .
			//---------------
			"</div>";

		return $out;
	}

	public function renderSingleInlineEditResults($pageID) {
		$page = $this->wire('pages')->get((int) $pageID);
		// TODO: IF NO PAGE FOUND? REDIRECT?!!!
		$tableRow = $this->getResultsTableRow($page);
		$row = $tableRow['row'];
		$attrs = $tableRow['attrs'];

		// build the single row of inventory item with updated values after inline-edit
		$tableAttrsString = $this->getSingleInlineEditResultsTableRowAttrs($attrs);
		$out = "<tr {$tableAttrsString}>" .
			// GET ROW TDs
			$this->getSingleInlineEditResultsTableRowCells($row) .
			"</tr>";
		return $out;
	}

	/**
	 * Build table row attrs for htmx response after single inline edit of inventory item.
	 *
	 * @param array $attrs Array with the attributes and values for the row.
	 * @return string $out Attribute and values for the tr element.
	 */
	private function getSingleInlineEditResultsTableRowAttrs(array $attrs) {
		$out = "";
		foreach ($attrs as $attribute => $value) {
			$out .= "{$attribute}='$value' ";
		}
		//--------------
		// add role='row'
		$out .= "role='row'";
		$out = trim($out);
		return $out;
	}

	/**
	 * Build table row cells for htmx response after single inline edit of inventory item.
	 *
	 * @param array $cells Array with the values to build row cells <td>.
	 * @return string $out Table row cells for the tr element.
	 */
	private function getSingleInlineEditResultsTableRowCells(array $cells) {
		$out = "";
		foreach ($cells as $cell) {
			$out .= "<td>{$cell}</td>";
		}
		//--------------
		return $out;
	}

	// ~~~~~~~~~~
	private function buildInventorySelector($selectorString) {

		$finalSelectorArray = [];
		$untouchedSelectorStringArray = [];
		$untouchedFinalSelectorString = '';
		$productsWithoutVariantsExtraSelectorString = '';
		$variantsExtraSelectorString = '';

		// enforce to string for strpos for PHP 8+
		$selectorString = strval($selectorString);
		//--------------------
		// FORCE DEFAULT LIMIT IF NO USER LIMIT SET
		if (strpos($selectorString, 'limit=') === false) {
			$limit = 10;
			$selectorString = rtrim("limit={$limit}," . $selectorString, ",");
		}

		// SPECIAL CONSIDERATION FOR 'title and parent.title' in SELECTOR
		//  we need to treat this situation differently.
		// we make assumptions about intentions of user
		// we subsequently rebuild the selector in order to push them into our ORg-groups in order to return meaningful results
		// ## @note: ONLY here refers to one of 'title' or 'parent.title' BUT NOT the other ##
		// Three scenarios here:
		// ------------------------
		// a. $selectorString contains a 'title' selector ONLY.
		// logic: Title here could refer to one of three things: (i) title of the product without variants; (ii) title of the variant itself: or (iii) the variant's parent's title
		// conclusion: we search for title for all three above
		// rebuilt selector: (template=pwcommerce-product....,title%=),(template=pwcommerce-product-variant...,title|parent.title%=)
		// b. $selectorString contains a 'parent.title' selector ONLY.
		// logic: This is quite explicit. It cannot really be about a product without variants parent's title! In this case, we assume they want to match variants by their parent product titles.
		// conclusion: move 'parent.title' to the VARIANT SIDE of the OR-group.
		// rebuilt selector: (template=pwcommerce-product-variant....,parent.title%=co) <- @note: operator just an example
		// c. $selectorString contains BOTH a 'title' and 'parent.title'
		// logic: This also seems rather explicit. The 'parent.title' tell us they want the title of the parent of the variant
		// conclusion: we search for title for the product side of the OR-groups and parent.title for the OR-groups side of the variant
		// rebuilt selector: (template=pwcommerce-product....,title%=),(template=pwcommerce-product-variant...,parent.title%=)

		// find any 'title' or 'parent.title' selectors
		if (strpos($selectorString, 'title') !== false) {

			$selectors = new Selectors($selectorString);

			// SCENARIOS PREP
			//---------------
			// scenario a or c
			$productsWithoutVariantsExtraSelectorStringArray = [];
			//---------------
			// scenario b or c
			$variantsExtraSelectorStringArray = [];

			//--------
			// iterate and get this Selectors objects

			foreach ($selectors as $selector) {

				//------------------

				if (in_array('title', $selector->fields)) {
					// scenario a or c: search for title on products side of OR-groups AND search for title|parent.title on variants side of OR-group
					$productsWithoutVariantsExtraSelectorStringArray[] = $selector->str;
					$variantsModifiedTitleSelectorString = "title|parent.title{$selector->operator}" . implode('|', $selector->values);
					$variantsExtraSelectorStringArray[] = $variantsModifiedTitleSelectorString;
				} elseif (in_array('parent.title', $selector->fields)) {
					// scenario b or c: move to variants side of OR-groups
					$variantsExtraSelectorStringArray[] = $selector->str;
				} else {
					// untouched selector
					$untouchedSelectorStringArray[] = $selector->str;
				}
			}
			// end loop

			//-------------------------

			//-------------------------
			// build the extra selector string for products without variants only for OR-groups
			$productsWithoutVariantsExtraSelectorString = implode(',', $productsWithoutVariantsExtraSelectorStringArray);
			//-------------------------
			// build the extra selector string for variants only for OR-groups
			$variantsExtraSelectorString = implode(',', $variantsExtraSelectorStringArray);
		}
		// end: if 'title' in string
		else {
			$untouchedSelectorStringArray[] = $selectorString;
		}

		// ++++++++++++ BUILD FINAL SELECTORS ++++++++
		// add the untouched final selector string to final selector array if we have one
		// --------------------
		// FINAL UNTOUCHED PART OF SELECTOR
		$untouchedFinalSelectorString = implode(',', $untouchedSelectorStringArray);
		if (!empty($untouchedFinalSelectorString)) {
			$finalSelectorArray[] = $untouchedFinalSelectorString;
		}
		// --------------------
		// FINAL SELECTOR FOR PRODUCTS WITHOUT VARIANTS ONLY for OR-groups
		if (empty($this->isQuickFilterShowVariantsOnly)) {
			// @note: OR:group
			// add the products without variants final  selector string to final selector array
			$productsWithoutVariantsFinalSelectorString = $this->getSelectorForProductsWithoutVariantsOnly($productsWithoutVariantsExtraSelectorString);
			$finalSelectorArray[] = $productsWithoutVariantsFinalSelectorString;

		}

		// --------------------
		// FINAL SELECTOR FOR VARIANTS ONLY for OR-groups
		// add the variants final  selector string to final selector array
		// @note: OR:group
		$variantsFinalSelectorString = $this->getSelectorForVariantsOnly($variantsExtraSelectorString);
		$finalSelectorArray[] = $variantsFinalSelectorString;


		//---------------
		$finalSelectorString = implode(',', $finalSelectorArray);

		//--------------
		return $finalSelectorString;
	}

	private function getSelectorForProductsWithoutVariantsOnly($extraSelectorString = null) {
		$extraQuickFilterVariantSelector = '';

		if (!empty($this->quickFilterSelectorArray)) {
			$extraQuickFilterVariantSelector = $this->quickFilterSelectorArray['product_without_variants'];

		}
		$selector = "template=" . PwCommerce::PRODUCT_TEMPLATE_NAME . ",pwcommerce_product_settings.use_variants=0";
		if (!empty($extraSelectorString)) {
			$selector .= ",{$extraSelectorString}";
		}
		if (!empty($extraQuickFilterVariantSelector)) {
			$selector .= ",{$extraQuickFilterVariantSelector}";
		}
		// final or-group with brackets
		$selector = "({$selector})";
		//---------
		return $selector;
	}

	private function getSelectorForVariantsOnly($extraSelectorString = null) {

		$extraQuickFilterProductSelector = '';

		if (!empty($this->quickFilterSelectorArray)) {
			$extraQuickFilterProductSelector = $this->quickFilterSelectorArray['variant'];

		}

		$selector = "template=" . PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME;
		if (!empty($extraSelectorString)) {
			$selector .= ",{$extraSelectorString}";
		}

		if (!empty($extraQuickFilterProductSelector)) {
			$selector .= ",{$extraQuickFilterProductSelector}";
		}

		// final or-group with brackets
		$selector = "({$selector})";
		//---------
		return $selector;
	}
	// ~~~~~~~~~~

	public function paginationOptions() {
		//------------
		$paginationOptions = ['base_url' => $this->adminURL . 'inventory/', 'ajax_post_url' => $this->adminURL . 'ajax/'];
		return $paginationOptions;
	}

	protected function getCustomListerSettings() {
		return [
			'label' => $this->_('Filter Inventory'),
			'inputfield_selector' => [
				// TODO: DO WE CHANGE THIS TO INCLUDE BOTH TEMPLATES OR REMOVE?
				'initValue' => "template=" . PwCommerce::PRODUCT_TEMPLATE_NAME . "|" . PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME,
				// 'initTemplate' => PwCommerce::xxxxxxxxxx_TEMPLATE_NAME,// @note: can only take one value! but also pulled from initValue above
				'showFieldLabels' => true,
			],

			// TODO; add columns!!!!
		];
	}

	private function getResultsTableHeaders() {
		// TODO: DO WE USE TW CLASSES HERE?
		$selectAllCheckboxName = "pwcommerce_bulk_edit_selected_items_all";
		$xref = 'pwcommerce_bulk_edit_selected_items_all';
		return [
			// SELECT ALL CHECKBOX
			$this->getBulkEditCheckbox('all', $selectAllCheckboxName, $xref),
			// SKU + TITLE (parent title if variant, else product title itself)
			[$this->_('Title'), 'pwcommerce_inventory_table_sku_title'],
			// QUANTITY
			[$this->_('Quantity'), 'pwcommerce_inventory_table_quantity'],
			// OVERSELL
			[$this->_('Oversell'), 'pwcommerce_inventory_table_oversell'],
			// ENABLED
			[$this->_('Enabled'), 'pwcommerce_inventory_table_enabled'],
			// EDIT ROW
			[$this->_('Edit'), 'pwcommerce_inventory_table_edit_row'],
		];
	}

	private function getResultsTable($pages) {

		$out = "";
		if (!$pages->count()) {
			$out = "<p>" . $this->_('No matching items in the inventory.') . "</p>";
		} else {
			$field = $this->modules->get('MarkupAdminDataTable');
			$field->setEncodeEntities(false);
			// set headers (th)
			$field->headerRow($this->getResultsTableHeaders());

			// set each row
			foreach ($pages as $page) {
				$tableRow = $this->getResultsTableRow($page);
				$row = $tableRow['row'];
				$attrs = $tableRow['attrs'];
				$field->row($row, ['attrs' => $attrs]);
			}

			// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
			$out .= $field->render();
			$out .= $this->getHiddenInputForTrackingEditedInventoryItems();
		}
		return $out;
	}

	private function getResultsTableRow(Page $page) {
		$pageID = $page->id;
		$checkBoxesName = "pwcommerce_bulk_edit_selected_items[]";
		// $stock = $page->pwcommerce_product_stock;
		$stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		$data = $stock->getArray();
		$data['id'] = $pageID;
		$stockJSON = \json_encode($data);
		$row = [
			// CHECKBOX
			$this->getBulkEditCheckbox($pageID, $checkBoxesName),
			// SKU + TITLE + INLINE-EDIT INPUT
			$this->getSKUTitleAndInlineEditMarkup($page),
			// QUANTITY + INLINE EDIT INPUT
			$this->getQuantityAndInlineEditMarkup($page),
			// OVERSELL + INLINE EDIT CHECKBOX
			$this->getOversellAndInlineEditMarkup($page),
			// ENABLED + INLINE EDIT CHECKBOX
			$this->getEnabledAndInlineEditMarkup($page),
			// EDIT TODO: alpine.js edit enable/disable
			$this->getInlineEditIcons($page),
		];
		// ---------
		$ajaxPostURL = $this->ajaxPostURL;
		// ---------
		$tableRow = [
			'row' => $row,
			'attrs' => [
				// id so we can retrieve for htmx use
				'id' => "pwcommerce_inventory_row_{$pageID}",
				// so we can show spinner during htmx request TODO: not in use since we don't swap classes @see hx-indicator below which we use in this case.
				// 'class' => 'pwcommerce_run_request_indicators_operations',
				// ALPINE.JS
				// x-data so we can model each row separately and see live preview of values
				'x-data' => $stockJSON,
				// HTMX
				// so we can save only this edited row
				'hx-post' => $ajaxPostURL,
				// so we manually trigger send to server after edit accepted
				// @note: hx-trigger will be triggered via js by the event 'pwcommerceinventoryinlineeditoftablerow' which is a result of an inventory table row being edited and the handler that accepts the edits 'handleInventoryItemInlineEditAccept' triggering the htmx call by sending this event.
				// TODO: DO WE NEED THE DELAY? IS IT LONG ENOUGH IF SO?
				'hx-trigger' => 'pwcommerceinventoryinlineeditoftablerow delay:500ms',
				// so we can replace this TR; otherwise htmx will find the hx-target in '#pwcommerce_bulk_edit_custom_lister' and replace the whole lister markup!
				// so we can replace the whole TR with a new one
				'hx-swap' => 'outerHTML',
				// tell htmx this is the target element for the swap
				'hx-target' => "#pwcommerce_inventory_row_{$pageID}",
				// tell htmx where the request indicator spinner is
				'hx-indicator' => "#pwcommerce_inventory_row_spinner_{$pageID}",
				// @note: needs to be valid JSON!
				'hx-vals' => json_encode(['pwcommerce_inventory_edited_item_id' => $pageID, 'pwcommerce_is_inline_edit' => true, 'pwcommerce_inline_edit_context' => 'inventory']),

			],
		];
		return $tableRow;
	}


	private function getSKUTitleAndInlineEditMarkup($page) {
		// $stock = $page->pwcommerce_product_stock;
		$stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		$editSKUAndTitleMarkup = $this->getSKUAndEditItemTitle($page);
		$pageID = $page->id;
		// ---------
		$options = [
			'id' => "pwcommerce_inventory_item_sku_{$pageID}",
			'name' => "pwcommerce_inventory_item_sku_{$pageID}",
			'xref' => "inventory_item_sku_{$page->id}",
			'xmodel' => "sku",
			'page_id' => $pageID,
			'value' => $stock->sku,
		];

		$skuInlineEditInput = $this->getInventoryInlineEditInput($options);
		// ---------
		//  [@alpine.js]
		// $class = "{ hidden: isInventoryItemInInlineEdit(`{$pageID}`) }";
		//  $out = "<div :class='$class'>" . $editSKUAndTitleMarkup . "</div>" .
		//    $skuInlineEditInput;
		// TODO - DELETE ALTERNATIVE MARKUP ABOVE AND BELOW IF NOT IN USE
		$out =
			// @note: in this alternative markup, we hide the sku and the title so only the input is visible
			// $skuInlineEditInput . "<div :class='$class'>" . $editSKUAndTitleMarkup . "</div>";
			// @note: here, we always show sku and title for better ux; user sees the variant title and the product title and how the sku will look like against these
			$skuInlineEditInput . "<div>" . $editSKUAndTitleMarkup . "</div>";
		return $out;
	}

	private function getQuantityAndInlineEditMarkup($page) {
		// $stock = $page->pwcommerce_product_stock;
		$stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		$quantity = $stock->quantity;
		$pageID = $page->id;
		// ---------
		$options = [
			'id' => "pwcommerce_inventory_item_quantity_{$pageID}",
			'name' => "pwcommerce_inventory_item_quantity_{$pageID}",
			'type' => 'number',
			'step' => 1,
			'min' => 0,
			'xref' => "inventory_item_quantity_{$page->id}",
			'xmodel' => "quantity",
			'page_id' => $pageID,
			'value' => $quantity,
		];
		$quantityInlineEditInput = $this->getInventoryInlineEditInput($options);
		// ---------

		//  [@alpine.js]
		// on inline-edit, we hide the display value quantity
		$class = "{ hidden: isInventoryItemInInlineEdit(`{$pageID}`) }";
		$out = "<div :class='$class'><span x-text='quantity'>" . $quantity . "</span></div>" .
			$quantityInlineEditInput;
		return $out;
	}

	private function getOversellAndInlineEditMarkup($page) {
		// $stock = $page->pwcommerce_product_stock;
		$stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		// @note:  [@alpine.js]
		$oversellString = $this->getBooleanString($stock->allowBackorders, 'allowBackorders');
		$pageID = $page->id;
		// ---------
		$options = [
			'id' => "pwcommerce_inventory_item_oversell_{$pageID}",
			'name' => "pwcommerce_inventory_item_oversell_{$pageID}",
			'label' => $this->_('Oversell'),
			'checked' => (int) $stock->allowBackorders === 1,
			'xref' => "inventory_item_oversell_{$page->id}",
			'xmodel' => "allowBackorders",
			'xchecked' => 'allowBackorders==1',
			'page_id' => $pageID,
			'value' => $pageID,
		];
		$oversellInlineEditCheckbox = $this->getInventoryInlineEditCheckbox($options);
		// ---------
		// on inline-edit, we hide the display value for 'overselling'
		$class = "{ hidden: isInventoryItemInInlineEdit(`{$pageID}`) }";
		$out = "<div :class='$class'>" . $oversellString . "</div>" .
			$oversellInlineEditCheckbox;
		return $out;
	}

	private function getEnabledAndInlineEditMarkup($page) {
		// $stock = $page->pwcommerce_product_stock;
		$stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		// @note:  [@alpine.js]
		$enabledString = $this->getBooleanString($stock->enabled, 'enabled');
		$pageID = $page->id;
		// ---------
		$options = [
			'id' => "pwcommerce_inventory_item_enabled_{$pageID}",
			'name' => "pwcommerce_inventory_item_enabled_{$pageID}",
			'label' => $this->_('Enabled'),
			'checked' => (int) $stock->enabled === 1,
			'xref' => "inventory_item_enabled_{$page->id}",
			'xmodel' => "enabled",
			'xchecked' => 'enabled==1',
			'page_id' => $pageID,
			'value' => $pageID,
		];
		$enabledInlineEditCheckbox = $this->getInventoryInlineEditCheckbox($options);
		// ---------
		// on inline-edit, we hide the display value for 'enabled'
		$class = "{ hidden: isInventoryItemInInlineEdit(`{$pageID}`) }";
		$out = "<div :class='$class'>" . $enabledString . "</div>" .
			$enabledInlineEditCheckbox;
		return $out;
	}

	/**
	 * Get a text input for use in inline-edit of an inventory item.
	 *
	 * @note: Uses alpine.js.
	 *
	 * @access private
	 * @param array $options
	 * @return string Rendered input markup.
	 */
	private function getInventoryInlineEditInput($options) {

		$defaultOptions = [
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_inline_edit',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
		];

		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}

		//------------------- inventory inline-edit: sku or quantity (getInputfieldText)
		$field = $this->pwcommerce->getInputfieldText($options);
		$pageID = $options['page_id'];
		$class = "{ hidden: !isInventoryItemInInlineEdit(`{$pageID}`) }";
		$field->attr([
			'x-ref' => $options['xref'],
			'x-model' => $options['xmodel'],
		]);

		// ----------------
		// @note: we wrap div around so that we can replace 'hidden' wholesale using object syntax
		// otherwise, other classes in input itself will NOT be preserved!
		$out = "<div class='hidden' :class='$class'>" . $field->render() . "</div>";
		return $out;
	}

	/**
	 * Get a checkbox for use in inline-edit of an inventory item.
	 *
	 * @note: Uses alpine.js.
	 *
	 * @access private
	 * @param array $options
	 * @return string Rendered checkbox markup.
	 */
	private function getInventoryInlineEditCheckbox($options) {
		$defaultOptions = [
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_inline_edit mr-1',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
		];

		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}

		//------------------- inventory inline-edit: oversell or enabled (getInputfieldCheckbox)
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		$pageID = $options['page_id'];
		$class = "{ hidden: !isInventoryItemInInlineEdit(`{$pageID}`) }";
		$field->attr([
			'x-ref' => $options['xref'],
			'x-model' => $options['xmodel'],
			'x-bind:checked' => $options['xchecked'],
		]);

		// ----------------
		// @note: we wrap div around so that we can replace 'hidden' wholesale using object syntax
		// otherwise, other classes in input itself will NOT be preserved!
		$out = "<div class='hidden' :class='$class'>" . $field->render() . "</div>";
		return $out;
	}


	/**
	 * Get markup for product without variant's or variant's sku, title and edit product link.
	 *
	 * @param Page $page The product Page whose markup to build.
	 * @return string $out The markup for the sku, title and edit link.
	 */
	private function getSKUAndEditItemTitle(Page $page) {

		$sku = '';
		// $stock = $page->pwcommerce_product_stock;
		$stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		$noSKU = $this->_('No SKU');
		$skuName = "";
		// if page is variant, we want its parent product for checking statuses + title below
		if ($this->pwcommerce->isVariant($page)) {
			// PRODUCT VARIANT
			$product = $page->parent;
			// also add the variant title for easier identification
			$skuName .= $page->title;
			// append sku if available, else show 'no SKU' [@alpine.js]
			$skuName .= ": " .
				"<template x-if='sku'>" .
				"<span x-text='sku'>" . $stock->sku . "</span>" .
				"</template>" .
				// if no SKU, show 'no SKU' text
				"<template x-if='!sku'>" .
				"<span>" . $noSKU . "</span>" .
				"</template>";
		} else {
			// PRODUCT WITHOUT VARIANTS
			$product = $page;
			//  [@alpine.js]
			$skuName .=
				"<template x-if='sku'>" .
				"<span x-text='sku'>" . $stock->sku . "</span>" .
				"</template>" .
				// if no SKU, show 'no SKU' text
				"<template x-if='!sku'>" .
				"<span>" . $noSKU . "</span>" .
				"</template>";
		}

		// ------------
		if (!empty($skuName)) {
			$sku = "<small class='block'>{$skuName}</small>";
		}

		// get the edit URL if item is unlocked
		$out = $sku . $this->getEditItemURL($product);
		// add spinner for htmx request indicators for this row
		$out .= $this->getInlineEditSpinner($page->id);
		// add published and locked status if applicable
		$status = [];
		if ($product->isLocked()) {
			$status[] = $this->_('locked');
		}

		if ($product->isUnpublished()) {
			$status[] = $this->_('unpublished');
		}

		$statusString = implode(', ', $status);
		if ($statusString) {
			$out .= "<small class='block italic mt-1'>{$statusString}</small>";
		}
		// *********************
		// **** @note: for debug only *****
		//  $out .= "<br>" . $page->template->name;
		// $out .= "<br>" . $page->id;
		// *********************

		return $out;
	}

	private function getEditItemURL(Page $page) {
		// if page is locked, don't show edit URL
		if ($page->isLocked()) {
			$out = "<span>{$page->title}</span>";
		} else {
			$out = "<a href='{$this->adminURL}products/edit/?id={$page->id}'>{$page->title}</a>";
		}
		return $out;
	}

	private function getBulkEditActionsPanel() {
		// TODO USE 'APPLY INLINE-EDITS' HERE? PROBLEM IS WOULD NEED TO SELECT ITEMS FIRST! - OTHERWISE INCONSISTENT WITH OTHER ACTIONS
		$actions = [
			'allow_overselling' => $this->_('Allow overselling'),
			'disallow_overselling' => $this->_('Disallow overselling'),
			'enable_selling' => $this->_('Enable selling'),
			'disable_selling' => $this->_('Disable selling'),
			// TODO: WHAT ABOUT LOCK/UNLOCK A VARIANT? OR WILL JUST CONFUSE?
			'publish' => $this->_('Publish'),
			'unpublish' => $this->_('Unpublish'),
			'lock' => $this->_('Lock'),
			'unlock' => $this->_('Unlock'),
			// TODO: allow these two?
			'trash' => $this->_('Trash'),
			'delete' => $this->_('Delete'),
			// TODO: IN FUTURE ALLOW BULK UPDATE QUANTITY - WILL NEED SMALL MODAL!
		];
		// @note: no 'add new item' here!
		$options = [
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	private function getBulkEditCheckbox($id, $name, $xref = null) {
		$options = [
			'id' => "pwcommerce_bulk_edit_checkbox{$id}",
			'name' => $name,
			'label' => ' ',
			// @note: skipping label
			// 'label2' => $this->_('xxxx'),
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_bulk_edit_selected_items',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $id,

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		$field->attr([
			'x-on:change' => 'handleBulkEditItemCheckboxChange',
		]);

		return $field->render();
	}

	/**
	 * Get boolean yes/no string depending on given value.
	 *
	 * @param integer $value The saved DB value for one of either 'allowBackorders' or 'enabled'.
	 * @param string $property One of either 'allowBackorders' or 'enabled'.
	 * @return string $out Final alpine.js-ed markup for boolean values.
	 */
	private function getBooleanString($value, $property) {
		$yes = $this->_('Yes');
		$no = $this->_('No');
		//return !empty($value) ? $yes : $no;
		// ----------------
		$hiddenYes = !empty($value) ? '' : 'hidden'; // appy hidden if NOT allowBackorders or NOT enabled
		$hiddenNo = !empty($value) ? 'hidden' : ''; // appy hidden if YES allowBackorders or YES enabled
		$out =
			//  [@alpine.js]
			"<span class='{$hiddenYes}' :class='{ hidden: !{$property} }'>" . $yes . "</span>" .
			"<span class='{$hiddenNo}' :class='{ hidden: {$property} }'>" . $no . "</span>";
		return $out;
	}

	/**
	 * Spinner markup for htmx request for inline edit
	 *
	 * @param integer $pageID The ID of the inventory item in this row.
	 * @return string $out Markup of spinner.
	 */
	private function getInlineEditSpinner($pageID) {
		$out = "<span id='pwcommerce_inventory_row_spinner_{$pageID}' class='fa fa-fw fa-spin fa-spinner ml-1 htmx-indicator'></span>";
		return $out;
	}

	private function getInlineEditIcons($page) {
		$out = "";

		// first, check if inventory item is locked for edits
		// if it is a variant, we check the parent product, else check the product itself
		// TODO: WHY check only parent? can't we lock variants? - for now, we check variant itself
		// if ($this->isVariant($page)) {
		//   // is parent product of variant locked?
		//   $isLocked = $page->parent->isLocked();
		// } else {
		//   // is product without variants locked?
		//   $isLocked = $page->isLocked();
		// }
		$isLocked = $page->isLocked();
		// --------------------

		if (empty($isLocked)) {
			$pageID = $page->id;
			$titleEditInventory = $this->_('Edit inventory inline');
			$titleAcceptChanges = $this->_('Accept changes');
			$titleRejectChanges = $this->_('Reject changes');
			// ------------------
			// @note: better to use object syntax to bind classes in this case since 'When using object-syntax, Alpine will NOT preserve original classes applied to an element's class attribute.
			$out .=
				"<div>" .
				// wrapper for edit icon
				"<span :class='{ hidden: isInventoryItemInInlineEdit(`{$pageID}`) }'>" .
				// edit icon
				"<i @click='initInventoryItemInlineEdit(`{$pageID}`)' class='fa fa-fw fa-edit cursor-pointer' title='{$titleEditInventory}'></i>" .
				"</span>" .
				// wrapper for accept and reject edit icons
				"<span class='hidden' :class='{ hidden: !isInventoryItemInInlineEdit(`{$pageID}`) }'>" .
				// accept edit icon
				// @note: handler 'handleInventoryItemInlineEditAccept' will trigger a htmx event as well!
				"<i @click='handleInventoryItemInlineEditAccept(`{$pageID}`)' class='fa fa-fw fa-check cursor-pointer' title='{$titleAcceptChanges}'></i>" .
				// reject edit icon
				"<i @click='handleInventoryItemInlineEditReject(`{$pageID}`)' class='fa fa-fw fa-times cursor-pointer' title='{$titleRejectChanges}'></i>" .
				"</span>" .
				"</div>";
		} else {
			$out .= "<i class='fa fa-fw fa-lock' aria-hidden='true'></i>";
		}

		// --------------
		return $out;
	}

	/**
	 * Hidden input to store IDs of inventory items that have been edited via inline-edit.
	 *
	 * Value is set by alpine.js based off a store value.
	 *
	 * @return string Rendered markup of the hidden input.
	 */
	private function getHiddenInputForTrackingEditedInventoryItems() {
		//------------------- edited_inventory_items_ids (getInputfieldHidden)
		$options = [
			'id' => 'pwcommerce_inventory_edited_inventory_items_ids',
			'name' => 'pwcommerce_inventory_edited_inventory_items_ids',
		];
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$field->attr([
			// 'x-model' => "xxxx",
			'x-bind:value' => '$store.ProcessPWCommerceStore.edited_inventory_items_ids',
		]);
		return $field->render();
	}


	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ QUICK FILTERS  ~~~~~~~~~~~~~~~~~~

	protected function ___getQuickFiltersValues() {
		$filters = [
			// reset/all
			'reset' => $this->_('All'),
			// products
			'active' => $this->_('Active'),// published + enabled
			'draft' => $this->_('Draft'),// unpublished or not enabled
			// variants
			'is_variant' => $this->_('Is Variant'),
			// inventory
			// TODO IN SETTINGS, HAVE SETTING FOR LOW STOCK, MAYBE '5'? BUT MAKE CONFIGURABLE
			'tracks_inventory' => $this->_('Tracks Inventory'),
			'out_of_stock' => $this->_('Out of Stock'),
			'low_inventory' => $this->_('Low Inventory'),
			'allows_backorders' => $this->_('Allows Backorders'),
			'no_sku' => $this->_('No SKU'),
			// sales
			'least_sales' => $this->_('Least Sales'),
			'most_sales' => $this->_('Most Sales'),
			// TODO NOT IN USE FOR NOW!
			// 'on_sale' => $this->_('On Sale'),
			// shipping
			'physical' => $this->_('Physical'),
			'digital' => $this->_('Digital'),
			'service_event' => $this->_('Service/Event'),
			// image
			'no_image' => $this->_('No Image'),
		];
		// TODO NOT IN USE FOR NOW!
		// IF NOT USING 'SALE' AND 'NORMAL' PRICE FIELDS
		// unset 'on_sale'
		// if (empty($this->pwcommerce->isUseSaleAndNormalPriceFields())) {
		// 	// TODO UNCOMMENT WHEN DONE
		// 	// unset($filters['on_sale']);
		// }
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

	protected function getSelectorForQuickFilter(): array {
		$input = $this->wire('input');

		$selectorArray = [];
		// NOTE: KEYS -> filter values; VALUEs -> STATUS CONSTANTS
		$allowedQuickFilterValues = $this->getAllowedQuickFilterValues();

		$quickFilterValue = $this->wire('sanitizer')->option($input->pwcommerce_quick_filter_value, $allowedQuickFilterValues);

		if (!empty($quickFilterValue)) {
			$this->quickFilterValue = $quickFilterValue;
			// quick filter checks
			// ++++++++++
			if (in_array($quickFilterValue, ['active', 'draft'])) {
				// ACTIVE (PUBLISHED + ENABLED) OR DRAFT (UNPUBLISHED OR NOT ENABLED)
				$selectorArray = $this->getSelectorForQuickFilterActive();
			} elseif (in_array($quickFilterValue, ['least_sales', 'most_sales', 'on_sale'])) {
				// SALES
				$selectorArray = $this->getSelectorForQuickFilterSales();
			} else if ($quickFilterValue === 'is_variant') {
				// IS VARIANTS (CHILDREN)
				// $selector = $this->getSelectorForQuickFilterIsVariant();
				// will cause $this->buildInventorySelector() to ignore 'products' part of the selector
				// i.e., will only return variants
				$this->isQuickFilterShowVariantsOnly = true;
			} else if (in_array($quickFilterValue, ['tracks_inventory', 'out_of_stock', 'low_inventory', 'allows_backorders', 'no_sku'])) {
				// TRACKS INVENTORY AND ZERO QUANTITY OR LOW INVENTORY OR ALLOW BACKORDERS
				// @note: 'enabled' is covered under 'active/draft' above
				$selectorArray = $this->getSelectorForQuickFilterInventory();
			} else if (in_array($quickFilterValue, ['physical', 'digital', 'service_event'])) {
				// SHIPPING
				$selectorArray = $this->getSelectorForQuickFilterShipping();
			} else if ($quickFilterValue === 'no_image') {
				// NO IMAGE
				$selectorArray = $this->getSelectorForQuickFilterNoImage();
			}
		}

		// return $selector;
		return $selectorArray;
	}

	private function getSelectorForQuickFilterActive() {
		$quickFilterValue = $this->quickFilterValue;
		$stockFieldName = PwCommerce::PRODUCT_STOCK_FIELD_NAME;
		$selectorArray = [];
		if ($quickFilterValue === 'active') {
			$selectorArray = [
				// product selector
				'product_without_variants' => "status<" . Page::statusUnpublished . ",{$stockFieldName}.enabled=1",
				// variant selector
				'variant' => "parent.status<" . Page::statusUnpublished . ",{$stockFieldName}.enabled=1",
			];
		} else if ($quickFilterValue === 'draft') {
			// UNPUBLISHED OR NOT ENABLED
			// $selector = ",(status>=" . Page::statusUnpublished . "),({$stockFieldName}.enabled=0,{$productSettingsFieldName}.use_variants=0),(children.{$stockFieldName}.enabled=0,{$productSettingsFieldName}.use_variants=1)";

			$selectorArray = [
				// @note: looks like nested or group works!
				// we need either unpublished or not enabled in the selectors
				// product selector
				'product_without_variants' => "(status>=" . Page::statusUnpublished . "),({$stockFieldName}.enabled=0)",
				// variant selector
				'variant' => "(parent.status>=" . Page::statusUnpublished . "),({$stockFieldName}.enabled=0)",
			];

		}


		// ----

		return $selectorArray;
	}

	private function getSelectorForQuickFilterSales() {
		$quickFilterValue = $this->quickFilterValue;
		// e.g.
		// SELECT data as product_id, quantity
		// FROM field_pwcommerce_order_line_item
		// WHERE data > 0
		// AND quantity > 20 -- the high quantity threshold
		// GROUP BY product_id, quantity
		// ORDER BY quantity DESC;

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::ORDER_LINE_ITEM_FIELD_NAME,
			'select_columns' => ['data AS product_id'],
			'sum' => [
				'expression' => 'quantity',
				'summed_column_name' => 'quantity_total',
			],
			'conditions' => [
				// data column/subfield {product ID}
				[
					'column_name' => 'data',
					'operator' => '>',
					'column_value' => 0,// to skip empty product ID, just in case
					'column_type' => 'int',
					// i.e. parameter name of the form :name
					// NOTE: excluding since no ambiguity. TraitPWCommerceDatabase::getGroupByQuery will default to 'column_name'
					// 'param_identifier' => 'data',
				],
			],
			'group_by_columns' => ['product_id'],
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
			$productsIDs = array_column($results, 'product_id');
			$productsIDsSelector = implode("|", $productsIDs);
			// NOTE: getting the product or the parent product if the product id is a variant!
			// NOTE id|children.id doesn't work! it gets the variants parents only
			// hence we ues OR:group
			// $selector = ",(id={$productsIDsSelector}),(children.id={$productsIDsSelector})";
			$salesSelector = "id={$productsIDsSelector}";

		}

		// ++++++++++
		$selectorArray = [
			// product selector
			'product_without_variants' => $salesSelector,
			// variant selector
			'variant' => $salesSelector,
		];



		// ----
		return $selectorArray;
	}

	private function getSelectorForQuickFilterInventory() {
		$quickFilterValue = $this->quickFilterValue;
		// 'tracks_inventory', 'out_of_stock', 'low_inventory', 'allows_backorders', 'no_sku'
		// @NOTE: BELOW, WE CATER FOR BOTH VARIANTS AND PRODUCTS WITHOUT VARIANTS
		$selectorArray = [];
		// ---------
		$productSettingsFieldName = PwCommerce::PRODUCT_SETTINGS_FIELD_NAME;
		$stockFieldName = PwCommerce::PRODUCT_STOCK_FIELD_NAME;
		if ($quickFilterValue === 'tracks_inventory') {
			// selector to check if product tracks inventory
			$productInventorySelector = "{$productSettingsFieldName}.track_inventory=1";
			$variantInventorySelector = "parent.{$productSettingsFieldName}.track_inventory=1";
		} else if ($quickFilterValue === 'out_of_stock') {
			// selector to check if product is out of stock
			// NOTE: HAS TO BE TRACKING INVENTORY + QUANTITY EMPTY
			// NOTE: we check for product without variants and variants separately in OR:group
			// $selector = ",{$productSettingsFieldName}.track_inventory=1,({$stockFieldName}.quantity<1,{$productSettingsFieldName}.use_variants=0),(children.{$stockFieldName}.quantity<1,{$productSettingsFieldName}.use_variants=1)";
			$productInventorySelector = "{$productSettingsFieldName}.track_inventory=1,{$stockFieldName}.quantity<1";
			$variantInventorySelector = "parent.{$productSettingsFieldName}.track_inventory=1,{$stockFieldName}.quantity<1";
		} else if ($quickFilterValue === 'low_inventory') {
			// selector to check if product is low on inventory against a threshold
			// TODO MAKE THRESHOLD CONFIGUARABLE! DEFAULT IS '5'
			// NOTE: HAS TO BE TRACKING INVENTORY + QUANTITY <= threshold BUT NOT ZERO (out of stock)
			// NOTE: we check for product without variants and variants separately in OR:group
			$shopGeneralSettings = $this->pwcommerce->getshopGeneralSettings();
			$lowInventoryThreshold = $shopGeneralSettings->product_quick_filters_low_stock_threshold;
			if (empty($lowInventoryThreshold)) {
				$lowInventoryThreshold = PwCommerce::PWCOMMERCE_LOW_STOCK_THRESHOLD;
			}
			// ++++++++
			// $selector = ",{$productSettingsFieldName}.track_inventory=1,({$stockFieldName}.quantity>0,{$stockFieldName}.quantity<{$lowInventoryThreshold},{$productSettingsFieldName}.use_variants=0),(children.{$stockFieldName}.quantity>0,children.{$stockFieldName}.quantity<{$lowInventoryThreshold},{$productSettingsFieldName}.use_variants=1)";
			$productInventorySelector = "{$productSettingsFieldName}.track_inventory=1,{$stockFieldName}.quantity>0,{$stockFieldName}.quantity<{$lowInventoryThreshold}";
			$variantInventorySelector = "parent.{$productSettingsFieldName}.track_inventory=1,{$stockFieldName}.quantity>0,{$stockFieldName}.quantity<{$lowInventoryThreshold}";
		} else if ($quickFilterValue === 'allows_backorders') {
			// selector to check if product/variant allows overselling/backorders
			$productInventorySelector = "{$stockFieldName}.allow_backorders=1";
			$variantInventorySelector = "{$stockFieldName}.allow_backorders=1";

		} else if ($quickFilterValue === 'no_sku') {
			// selector to check if product/variant has no sku
			$productInventorySelector = "{$stockFieldName}.sku=''";
			$variantInventorySelector = "{$stockFieldName}.sku=''";
		}

		// ++++++++++
		$selectorArray = [
			// product selector
			'product_without_variants' => $productInventorySelector,
			// variant selector
			'variant' => $variantInventorySelector
		];



		// ----

		return $selectorArray;
	}

	private function getSelectorForQuickFilterShipping() {
		$quickFilterValue = $this->quickFilterValue;
		// 'physical' | 'physical_no_shipping' | 'digital' | 'service'
		$productSettingsFieldName = PwCommerce::PRODUCT_SETTINGS_FIELD_NAME;
		// @NOTE: BELOW, WE CATER FOR BOTH VARIANTS AND PRODUCTS WITHOUT VARIANTS
		$selectorArray = [];
		if ($quickFilterValue === 'physical') {
			// selector to return physical products
			// $selector = ",({$productSettingsFieldName}.shipping_type=physical_no_shipping|physical),(parent.{$productSettingsFieldName}.shipping_type=physical_no_shipping|physical)";
			$shippingTypeSelector = "physical_no_shipping|physical";
		} else if ($quickFilterValue === 'digital') {
			// selector to return digital products
			// $selector = ",({$productSettingsFieldName}.shipping_type=digital),(parent.{$productSettingsFieldName}.shipping_type=digital)";
			$shippingTypeSelector = "digital";
		} else if ($quickFilterValue === 'service_event') {
			// selector to return service or events products
			// $selector = ",{$productSettingsFieldName}.shipping_type=service";
			$shippingTypeSelector = "service";
		}

		// ++++++++++
		$selectorArray = [
			// product selector
			'product_without_variants' => "{$productSettingsFieldName}.shipping_type={$shippingTypeSelector}",
			// variant selector
			'variant' => "parent.{$productSettingsFieldName}.shipping_type={$shippingTypeSelector}",
		];



		// ----
		return $selectorArray;
	}

	private function getSelectorForQuickFilterNoImage() {
		$noImageSelector = "pwcommerce_images=''";
		$selectorArray = [
			// product selector
			'product_without_variants' => $noImageSelector,
			// variant selector
			'variant' => $noImageSelector
		];
		// ----
		return $selectorArray;
	}


}