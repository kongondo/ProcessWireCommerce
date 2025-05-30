<?php

namespace ProcessWire;

/**
 * PWCommerce: InputfieldPWCommerceOrder -> InputfieldPWCommerceOrderRenderAddProducts
 *
 * Helper render class for InputfieldPWCommerceOrder.
 * For adding products to an order.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceOrderRenderAddProducts for PWCommerce
 * Copyright (C) 2022 by Francis Otieno
 * MIT License
 *
 */



class InputfieldPWCommerceOrderRenderAddProducts extends WireData
{




	// =============
	protected $page;
	private $name;
	// -------


	// ----------
	private $xstoreOrder; // the alpinejs store used by this inputfield.
	// TODO DELETE IF NOT IN USE
	private $xstore; // the full prefix to the alpine store used by this inputfield



	public function __construct($page) {

		$this->page = $page;



		// GET UTILITIES CLASS



		// ==================
		$this->xstoreOrder = 'InputfieldPWCommerceOrderStore';
	}

	/**
	 * Render the markup for add products to order
	 *
	 */
	public function ___render($fieldName) {
		// @note: we will need this for ajax in renderAddNewProduct()
		$this->name = $fieldName;
		return $this->getOrderAddProductsToOrderMarkup();
	}

	private function renderAddNewProduct() {
		$pageID = $this->page->id;
		$name = $this->name;

		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		$adminEdit = $this->wire('config')->urls->admin . "page/edit/?id=" . $pageID . "&field=" . $name;
		// search box - for finding product to add to order
		$options = [
			'id' => "pwcommerce_order_search_products",
			'name' => "pwcommerce_order_search_products",
			'label' => $this->_('Search Products'),
			'placeholder' => $this->_('Begin typing to search for products'),
			'get_url' => $adminEdit,
			'target' => "#search-results", // for hx-target
		];

		$searchBoxField = $this->pwcommerce->getSearchBox($options);
		$wrapper->add($searchBoxField);

		// found products list
		// @note: data inserted by htmx but also accessed by alpine
		$out = "<div id='search-results'></div>";

		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'classes' => 'pwcommerce_order_add_products',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		$wrapper->add($field);

		return $wrapper->render();
	}

	public function renderProductsSearchResults() {

		$input = $this->wire('input');

		$sanitizer = $this->wire('sanitizer');
		$pages = $this->wire('pages');
		$out = "";

		// pwcommerce_order_search_products
		// Sanitize text for a search on title and body fields
		$q = $input->get->text('pwcommerce_order_search_products'); // text search query
		// TODO: sort parent? parent id? @note/TODO we are excluding hidden and unpublished here!
		// TODO: ADD ENABLED + QUANTITY CHECKER!
		// $results = $pages->find("limit=10,template=pwcommerce-product|pwcommerce-product-variant,sort=parent.id,title%=" . $sanitizer->selectorValue($q));

		// GET AND JOIN THE OR-GROUP SELECTORS for Products without variants; Products with variants; and Variants
		$q = $sanitizer->selectorValue($q);
		$selector = $this->getSelectorForProductsSearch($q);
		$results = $pages->find($selector);
		if (!$results->count()) {
			$out = $this->noProductResultsFound($q);
		} else {
			// FIRST, PROCESS RESULTS TO ARRANGE THEM UNDER THEIR PARENTS
			$processedResults = $this->getProcessedProductResults($results);

			// BUILD RESPONSE
			$response = $this->buildResponseForProductResults($processedResults);
			$out = $response['response_string'];
			$xdata = $response['xdata'];

			// PREPARE SAVED ORDER LINE ITEMS DATA TO SEND TO BROWSER FOR USE BY ALPINE JS
			$dataJSON = $this->buildJSONResponseForProductResultsForAlpine($xdata);
			//---------
			// @note: passing as script doesn't work as leads to race condition between htmx and alpine. so, we use above instead
			//   $data2 = [];
			//   $data2['order_line_items_products_search_results'] = $xdata;
			//   $script = "<script>ProcessWire.config.InputfieldPWCommerceOrderLineItemSearchResults = " . json_encode($xdata) . ';</script>';
			//---------
			// init results (data) for alpine + return response for htxm
			$out =
				"<div x-init='initProductSearchResultsData($dataJSON)'>" .
				"<ul  class='list-none pl-0'>{$out}</ul>" .
				"</div>";
		}

		//---------------
		return $out;
	}

	private function renderEditModals() {

		//---------------
		// TODO: CHECK IF ISSUES: RENAMED ID FROM 'pwcommerce_order_main_edit_modals'

		// @note: these open in modals! they interact with alpine JS
		$out = "<div id='pwcommerce_order_edit_modal_for_add_products'>" .
			// initialise alpine to use htmx inserted data ($searchbox) - for found products
			$this->getModalMarkupForAddProductsToOrder() .
			//-------------
			"</div>";

		return $out;
	}

	// ~~~~~~~~~~~~~~~

	private function getOrderAddProductsToOrderMarkup() {
		//------------------- add product(s) to order (getInputfieldButton)

		$label = $this->_('Add products to order');
		$options = [
			//    'id' => "pwcommerce_order_line_item_{$action}_selected_product",
			//    'name' => "pwcommerce_order_line_item_{$action}_selected_product",
			'label' => $label,
			'collapsed' => Inputfield::collapsedNever,
			'small' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
		];

		$field = $this->pwcommerce->getInputfieldButton($options);
		$field->attr('x-on:click', 'handleOpenOrderAddProductsModal');

		$button = $field->render();

		$addProductsToOrderLink = "<div id='open_modal_wrapper' class='block mb-5'>{$button}</div>";

		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'classes' => 'pwcommerce_order_add_new',
			// TODO: AMEND TO 'pwcommerce_order_add_products_to_order'
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $addProductsToOrderLink, // TODO: CHANGE TO SMALL BUTTON?
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// -------
		$out = $field->render() . $this->renderEditModals();

		// ------
		return $out;
	}

	/**
	 * Build selector for matching products to add to an order as per search query.
	 *
	 * @param string $q Sanitized backend user query for use as a selector value in the final selector.
	 * @return string $combinedSelector The selector that will be passed to a find().
	 */
	private function getSelectorForProductsSearch($q) {

		$allSelectors = [
			'default' => $this->getSelectorForProductsSearchDefault($q),
			'product_without_variants' => $this->getSelectorForProductsSearchProductsWithoutVariantsOnly(),
			'product_with_variants' => $this->getSelectorForProductsSearchProductsWithVariantsOnly(),
			'variants' => $this->getSelectorForProductsSearchVariantsOnly(),
		];

		$combinedSelector = "";

		foreach ($allSelectors as $key => $selector) {
			// if default selector: no OR-group on it
			if ($key === 'default') {
				$combinedSelector .= "{$selector},"; // @note the trailing comma!
			} else {
				// OR-group parentheses around the selector
				// @note the OR-group parentheses and the trailing comma!
				$combinedSelector .= "({$selector}),";
			}
		}
		$combinedSelector = rtrim($combinedSelector, ',');

		return $combinedSelector;
	}

	/**
	 * Get the default selector part for building the selector to find products to add to an order.
	 *
	 * @param string $q Sanitized backend-user string to use as a selector value to match products.
	 * @return string $selector The default selector.
	 */
	private function getSelectorForProductsSearchDefault($q) {
		// @note: harcoded limit=10 for now TODO?
		// TODO: include=all is ok?
		$selector = "limit=10,sort=parent.id,title%={$q},include=all,status<" . Page::statusTrash;
		return $selector;
	}

	/**
	 * Selector to match ENABLED products that don't use variants that EITHER track inventory AND are in stock OR if not in stock, ALLOW Overselling, OR DON'T track inventory.
	 *
	 * @note OR-group selector.
	 * @access private
	 * @return string $selector String for above match.
	 */
	private function getSelectorForProductsSearchProductsWithoutVariantsOnly() {
		// @note: will end up as nested OR-group selector
		// @note: matching products, that are enabled, that don't use variants, which if tracking inventory need at least 1 product in stock or if not in stock, allow overselling, OR which are not tracking inventory
		$selector = "template=" . PwCommerce::PRODUCT_TEMPLATE_NAME . ",pwcommerce_product_stock.enabled=1,pwcommerce_product_settings.use_variants=0,(pwcommerce_product_settings.track_inventory=1,(pwcommerce_product_stock.quantity>0),(pwcommerce_product_stock.allow_backorders=1)),(pwcommerce_product_settings.track_inventory=0)";
		return $selector;
	}

	/**
	 * Selector to match products that use variants AND have at least one variant ENABLED that is in stock or allows overselling if this parent product is tracking inventory, or just enabled if this parent product is not tracking inventory.
	 *
	 * @note OR-group selector.
	 * @access private
	 * @return string $selector String for above match.
	 */
	private function getSelectorForProductsSearchProductsWithVariantsOnly() {
		// @note: will end up as nested OR-group selector
		// @note: their stock quantity don't apply as we are selling variants, i.e. variants QUANTITY would matter if tracking.
		// @note: their tracking inventory matters BUT TO THEIR CHILDREN, i.e., the variants themselves!
		// @note: matching products, that use variants and have at least one child (variant) that is enabled.
		$selector = "template=" . PwCommerce::PRODUCT_TEMPLATE_NAME . ",pwcommerce_product_settings.use_variants=1,(pwcommerce_product_settings.track_inventory=0,children=[pwcommerce_product_stock.enabled=1]),(pwcommerce_product_settings.track_inventory=1,children=[pwcommerce_product_stock.enabled=1,(pwcommerce_product_stock.quantity>0),(pwcommerce_product_stock.allow_backorders=1)])";
		return $selector;
	}

	/**
	 * Selector to match ENABLED variants, which, if their PARENT PRODUCT tracks inventory are in stock or if not in stock, allow overselling, OR whose PARENT PRODUCT does not track inventory.
	 *
	 * @note OR-group selector.
	 * @access private
	 * @return string $selector String for above match.
	 */
	private function getSelectorForProductsSearchVariantsOnly() {
		// @note: will end up as nested OR-group selector
		// @note: matching products variants, that are enabled, which, if PARENT is tracking inventory need at least 1 variant in stock OR if not in stock, allow overselling, or whose PARENT is not tracking inventory.
		$selector = "template=" . PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME . ",pwcommerce_product_stock.enabled=1,(parent.pwcommerce_product_settings.track_inventory=0),(parent.pwcommerce_product_settings.track_inventory=1,(pwcommerce_product_stock.quantity>0),(pwcommerce_product_stock.allow_backorders=1)),parent.status<" . Page::statusUnpublished;
		return $selector;
	}

	private function getProcessedProductResults($results) {

		// @note: updated this to check if actual field is a language field!
		$currentLanguage = null;
		$isTitleLanguageField = $this->pwcommerce->isTitleLanguageField();
		if ($isTitleLanguageField) {
			if ($this->wire('languages')) {
				$currentLanguage = $this->wire('user')->language;
			}
		}

		// FIRST, PROCESS RESULTS TO ARRANGE THEM UNDER THEIR PARENTS
		$processedResults = [];
		// @note: flat to build final selected list to add to orders on main page from modal
		// it is flat for ease of use and also in main order page, we don't need hierarchy
		// @note: only truly matched below are added, so, may not need parents always
		// $xdata = [];
		$isMatchParent = false;

		// TODO: DECIDE ON THIS getProductData() - DON'T NEED BOTH!

		foreach ($results as $page) {
			$productType = 'product_with_variants';
			// TODO: DELETE IF NOT IN USE!
			// $thumb = '';
			// IF PARENT
			if ($page->template->name === PwCommerce::PRODUCT_TEMPLATE_NAME) {
				$parent = $page;
				$isMatchParent = true;

				$title = $currentLanguage ? $parent->title->getLanguageValue($currentLanguage) : $parent->title;
				$title = $this->wire('sanitizer')->entities($title);
				// TODO: DELETE IF NOT IN USE!
				// $thumb = $this->getProductThumbURL($page);
			}
			// IF NOT PARENT - get parent
			else {
				$parent = $page->parent;
				$title = $currentLanguage ? $parent->title->getLanguageValue($currentLanguage) : $parent->title;
				$title = $this->wire('sanitizer')->entities($title);
				$isMatchParent = false;
			}
			// matched a parent product
			if ($page->id === $parent->id) {
				$title = $currentLanguage ? $parent->title->getLanguageValue($currentLanguage) : $parent->title;
				$title = $this->wire('sanitizer')->entities($title);
				//--------------------
				$productType = 'product_without_variants';
				$processedResults[$parent->id]['main_product'] = $this->getProductData($parent, $productType, $currentLanguage);
				//--------------------
			} else {

				// if child, also add its parent since it might have been missed in the results, meaning above 'if' wouldn't be true
				if (!$isMatchParent) {
					$title = $currentLanguage ? $parent->title->getLanguageValue($currentLanguage) : $parent->title;
					$title = $this->wire('sanitizer')->entities($title);

					//--------------------
					$productType = 'product_with_variants';
					$processedResults[$parent->id]['main_product'] = $this->getProductData($parent, $productType, $currentLanguage);
					//--------------------
				}

				// add child itself
				$title = $currentLanguage ? $page->title->getLanguageValue($currentLanguage) : $page->title;
				$title = $this->wire('sanitizer')->entities($title);
				// TODO: REFACTOR TO OWN METHOD FOR GRABBING PRICE VALUES, TAX EXEMPTION, ETC!
				$unitPrice = $page->pwcommerce_product_stock->price;
				if (empty($unitPrice)) {
					$unitPrice = $page->parent->pwcommerce_product_stock->price;
				}
				// TODO: DELETE IF NOT IN USE
				// $taxable = $page->parent->pwcommerce_product_settings->taxable;
				//--------------------
				$productType = 'variant';
				$processedResults[$parent->id]['variants'][$page->id] = $this->getProductData($page, $productType, $currentLanguage);
				//--------------------
				// TODO: DELETE IF NOT IN USE!
				// $thumb = $this->getProductThumbURL($page);
			}
		}

		return $processedResults;
	}

	// modal for adding products as line items to order
	private function getModalMarkupForAddProductsToOrder() {
		$searchBox = $this->renderAddNewProduct();
		$addButton = $this->pwcommerce->getModalActionButton(['x-on:click' => 'addSelectedProductsToOrder']);
		$cancelButton = $this->pwcommerce->getModalActionButton(['x-on:click' => 'resetSelectedProductsAndClose'], 'cancel');
		$header = $this->_("Add products");
		$body = $searchBox;
		$footer = "<div class='ui-dialog-buttonset'>{$addButton}{$cancelButton}</div>";
		$xproperty = 'is_add_products_modal_open';
		$size = '6x-large';
		// wrap content in modal for adding/editing whole/main order discount
		// modal options
		$options = [
			// $header The modal title pane markup.
			'header' => $header,
			// $body The main content markup.
			'body' => $body,
			// $footer The footer markup.
			'footer' => $footer,
			// $xstore The alpinejs store with the property that will be modelled to show/hide the modal.
			'xstore' => $this->xstoreOrder,
			// $xproperty The alpinejs property that will be modelled to show/hide the modal.
			'xproperty' => $xproperty,
			// $size The size of the modal requested.
			'size' => $size,
		];
		$out = $this->pwcommerce->getModalMarkup($options);
		// -------------
		return $out;
	}

	private function getSelectProductsForOrderCheckbox($productType, $productID, $title, $thumb) {
		$xref = null;
		if ($productType === 'product_with_variants') {
			// product with variants
			$changeJSFunction = "toggleProductWithVariantSelected";
			$xref = "parent_product_{$productID}";
		} elseif ($productType === 'variant') {
			// variant
			$changeJSFunction = "toggleVariantSelected";
			$xref = "variant_{$productID}";
		} else {
			// product without variants/children
			$changeJSFunction = "toggleProductWithoutVariantSelected";
		}

		// @note: already translated
		$label = "<span class='cursor-pointer'>{$thumb}<span class='ml-3'>{$title}</span></span>";

		//-------
		$options = [
			'id' => "pwcommerce_order_line_item_select_product{$productID}",
			//    'name' => "pwcommerce_order_line_item_select_product{$productID}",
			// @note: skipLabel not working here so pw will use name to create label. So, force no label by setting blank name
			//'name' => '',
			'label' => $label,
			// 'skipLabel' => Inputfield::skipLabelMarkup,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $productID,
		];

		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		$field->attr('x-on:change', $changeJSFunction);
		if (!empty($xref)) {
			$field->attr('x-ref', $xref);
		}

		// @note: disable entity encode of label so we can render own markup around product select title
		$field->entityEncodeLabel = false;

		return $field->render();
	}

	private function getProductThumb($thumbURL) {
		$productThumb = '';
		if (!empty($thumbURL)) {
			$class = "w-7 inline-block ml-3";

			//  if (strpos($thumbURL, 'no-image-found.svg') !== false) {
			if ($this->pwcommerce->isGenericNoImageFound($thumbURL)) {
				// for generic no image found, add opacity class
				$class .= " opacity-25";
			}
			$productThumb = "<img src='{$thumbURL}' class='{$class}'>";
		}
		return $productThumb;
	}

	private function getProductThumbURL($page) {
		// TODO: get main products if no variant's? maybe not
		$productThumbURL = null;
		if ($page->pwcommerce_images->count()) {
			$firstImage = $page->pwcommerce_images->first();
			if ($firstImage) {
				$thumb = $firstImage->height(260);
				$productThumbURL = $thumb->url;
			}
		} else {
			// return generic no-image-found
			$assetsURL = $this->pwcommerce->getAssetsURL();
			$productThumbURL = $assetsURL . "icons/no-image-found.svg";
		}
		return $productThumbURL;
	}

	private function getProductData($product, $productType, $currentLanguage) {
		$data = [];
		$title = $currentLanguage ? $product->title->getLanguageValue($currentLanguage) : $product->title;
		$title = $this->wire('sanitizer')->entities($title);
		//---------
		$productSKU = $product->pwcommerce_product_stock->sku;
		$productThumbURL = $this->getProductThumbURL($product);
		$isGenericNoImageFound = $this->pwcommerce->isGenericNoImageFound($productThumbURL);
		//---------------
		if ($productType === 'variant') {
			// variant
			$unitPrice = $product->pwcommerce_product_stock->price;
			if (empty($unitPrice)) {
				$unitPrice = $product->parent->pwcommerce_product_stock->price;
			}
			$taxable = $product->parent->pwcommerce_product_settings->taxable;
			$hasVariants = false;
			$isVariant = 1;
			$productParentID = $product->parent->id;
		} else {
			// main product
			$unitPrice = $product->pwcommerce_product_stock->price;
			$taxable = $product->pwcommerce_product_settings->taxable;
			$hasVariants = $productType === 'product_with_variants' ? true : false;
			$isVariant = 0;
			// @note: for main products, the pseudo parent product ID is 0
			// this is mainly for use with variants since we need to know their parent product
			$productParentID = $product->id;
			$productParentID = 0;
		}

		//-------------
		// @note: ID = 0 DENOTES NEW! @update: no longer in use in this sense! we find new by 'pooling' new items' product ids
		// TODO: DELETE WHEN DONE
		// $data = ['id' => 0, 'title' => $title, 'sku' => $productSKU, 'is_main_product' => $isImainProduct, 'unitPrice' => $unitPrice, 'taxable' => $taxable, 'productID' => $product->id, 'productThumbURL' => $productThumbURL];
		// TODO: REMOVE UNUSED , E.G. 'title'!
		$data = ['id' => $product->id, 'productTitle' => $title, 'productSKU' => $productSKU, 'parent_product' => $productParentID, 'unitPrice' => $unitPrice, 'quantity' => 1, 'taxable' => $taxable, 'productID' => $product->id, 'productThumbURL' => $productThumbURL, 'is_generic_no_image_found' => $isGenericNoImageFound, 'has_variants' => $hasVariants, 'isVariant' => $isVariant, 'totalPrice' => $unitPrice, 'totalPriceDiscounted' => $unitPrice, 'discountType' => 'none', 'discountValue' => 0, 'discountAmount' => 0];

		return $data;
	}

	// ~~~~~~~~~~~~~~~~

	private function buildResponseForProductResults($processedResults) {
		######### // TODO: IF TRACKING INVENTORY, SHOW QUANTITY IN BRACKETS! ######### TODO: future release
		$responseString = "";
		$xdata = [];
		// BUILD RESPONSE
		foreach ($processedResults as $mainProductID => $values) {
			$product = $values['main_product'];
			// ----------------
			if (!empty($values['variants'])) {
				// ==== PRODUCT WITH VARIANTS ====
				$thumb = $this->getProductThumb($product['productThumbURL']);
				$checkbox = $this->getSelectProductsForOrderCheckbox('product_with_variants', $mainProductID, $product['productTitle'], $thumb);
				//----------------
				$xdata[] = $values['main_product'];
				//--------------
				$responseString .= "<li class='mt-5'>{$checkbox}</li><ul class='list-none mt-3'>";
				// get its matched variants
				// TODO: IF VARIANT PRICE IS ZERO, NEED TO USE PRODUCT PRICE!???
				foreach ($values['variants'] as $variantID => $variant) {
					$xdata[] = $variant;
					$thumb = $this->getProductThumb($variant['productThumbURL']);
					$checkbox = $this->getSelectProductsForOrderCheckbox('variant', $variant['productID'], $variant['productTitle'], $thumb);
					//----------------
					$responseString .= "<li class='mt-3'>{$checkbox}</li>";
				}
				$responseString .= "</ul>";
			} else {
				// ==== PRODUCT WITHOUT VARIANTS ====
				$xdata[] = $values['main_product'];
				$thumb = $this->getProductThumb($product['productThumbURL']);

				$checkbox = $this->getSelectProductsForOrderCheckbox('product_without_variants', $mainProductID, $product['productTitle'], $thumb);
				//----------------
				// a product page without variants or its variants not matched TODO: if non-matched, need to get all its variants anyway!
				$responseString .= "<li class='mt-5'>{$checkbox}</li>";
			}
		}
		// end loop of products and potential variants

		$response = [
			'response_string' => $responseString,
			'xdata' => $xdata,
		];

		//---------
		return $response;
	}

	private function buildJSONResponseForProductResultsForAlpine($xdata) {
		$data = [];
		// PREPARE SAVED ORDER LINE ITEMS DATA TO SEND TO BROWSER FOR USE BY ALPINE JS
		$data['order_products_search_results'] = $xdata;
		$dataJSON = json_encode($data);
		//$dataJSON = json_encode($data, JSON_HEX_APOS);
		//$dataJSON = wireEncodeJSON($data);
		return $dataJSON;
	}

	//-------------

	private function noProductResultsFound($query) {
		$query = $this->wire('sanitizer')->entities($query);
		$info = $this->_("No products found matching the search") . " <span class='font-bold'>{$query}</span>";
		$out = "<p>{$info}</p>";
		return $out;
	}
}