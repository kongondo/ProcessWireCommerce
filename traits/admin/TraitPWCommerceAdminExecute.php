<?php

namespace ProcessWire;

trait TraitPWCommerceAdminExecute
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ EXECUTES  ~~~~~~~~~~~~~~~~~~

	public function execute() {


		// ========
		// TODO: ADD DESCRIPTIONS FOR EACH VIEW IN FUTURE RELEASE!
		// ----------------
		// TODO  DELETE HEADLINE IF NOT NEEDED!?
		//   $this->headline($this->_("Shop Home"));// TODO: verbose?
		// TODO: confusing?
		$this->headline($this->_("Home"));
		$out = $this->pagesHandler();
		//--------------------
		return $out;
	}

	public function executeOrders() {

		$urlSegment2 = $this->urlSegment2;

		// handle add, view or edit order
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'view', 'edit', 'print-invoice', 'email-invoice'])) {
			// @note: without the trailing slash, we get errors when saving since no context (urlSegment1) found!
			// TODO: see if can force trailing slash
			$breadcrumbHREF = '../../orders/';
			$label = $this->_('Orders'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING an order
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else if ($urlSegment2 === 'view') {
				//--------------------
				// VIEWING an order
				$out = $this->renderViewItem($breadcrumbHREF, $label);
			} else if ($urlSegment2 === 'print-invoice') {
				//--------------------
				// PRINTING A SINGLE INVOICE of an order
				$out = $this->renderPrintItem($breadcrumbHREF, $label);
			} else if ($urlSegment2 === 'email-invoice') {
				// SENDING (email) A SINGLE INVOICE of an order
				// @note: not really a view as it will redirect after send
				$out = $this->renderEmailItem($breadcrumbHREF, $label);
			} else {
				//------------------
				// EDITING an order
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL orders
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeProducts() {
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit product
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			$breadcrumbHREF = '../../products/';
			$label = $this->_('Products'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a product
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a product
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL products
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeInventory() {
		$out = $this->pagesHandler();
		//--------------------
		return $out;
	}

	public function executeCategories() {

		// TODO IMPLEMENT IN FUTURE? i.e. prevent direct access of categories (via url) IF USING COLLECTIONS
		// if (!empty($this->isCategoryACollection)) {
		// 	// 'COLLECTIONS' term not in use; redirect home
		// 	$this->session->redirect($this->wire('page')->url);
		// }

		// if 'cateogies' is named 'collections'
		// @see installer 'other optional settings'
		if (!empty($this->isCategoryACollection)) {
			// set categories dashboard headline as collections
			$this->headline($this->_("Collections"));
			$breadcrumbHREF = '../../collections/';
			$label = $this->_('Collections'); //
		} else {
			$breadcrumbHREF = '../../categories/';
			$label = $this->_('Categories'); //
		}

		// ------
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit category
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			// $breadcrumbHREF = '../../categories/';
			// $label = $this->_('Categories'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a category
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a category
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL categories
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeCollections() {
		if (empty($this->isCategoryACollection) || empty($this->isInstalledProductCategoriesFeature)) {
			// 'COLLECTIONS' term not in use OR 'CATEGORIES' FEATUER NOT INSTALLED; redirect home
			$this->session->redirect($this->wire('page')->url);
		}
		// 'COLLECTIONS' term in use; handle via 'categories' dashboard
		return $this->executeCategories();
	}

	public function executeTags() {
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit tag
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			$breadcrumbHREF = '../../tags/';
			$label = $this->_('Tags'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a tag
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a tag
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL tags
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeAttributes() {
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit attribute
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			$breadcrumbHREF = '../../attributes/';
			$label = $this->_('Attributes'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING an attribute
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING an attribute
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL attributes
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeTypes() {
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit type
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			$breadcrumbHREF = '../../types/';
			$label = $this->_('Types'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a type
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a type
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL types
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeBrands() {
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit brand
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			$breadcrumbHREF = '../../brands/';
			$label = $this->_('Brands'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a brand
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a brand
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL brands
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeDimensions() {
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit dimension
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			$breadcrumbHREF = '../../dimensions/';
			$label = $this->_('Dimensions'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a dimension
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a dimension
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL dimensions
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeProperties() {
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit property
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			$breadcrumbHREF = '../../properties/';
			$label = $this->_('Properties'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a property
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a property
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL properties
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	// --------------
	public function executeDownloads() {
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit download
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			$breadcrumbHREF = '../../downloads/';
			$label = $this->_('Downloads'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a download
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a download
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL downloads
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeShipping() {
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit shipping zone
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			$breadcrumbHREF = '../../shipping/';
			$label = $this->_('Shipping'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a shipping zone
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a shipping zone
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL shipping zones
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeTaxSettings() {
		$this->headline($this->_("Tax Settings"));
		$out = $this->pagesHandler();
		//--------------------
		return $out;
	}

	/**
	 * Country tax rates
	 *
	 * @return string $out Markup of country tax rates.
	 */
	public function executeTaxRates() {
		$this->headline($this->_("Tax Rates"));
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit country tax rate
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			$breadcrumbHREF = '../../tax-rates/';
			$label = $this->_('Tax Rates'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a country tax rate
			if ($urlSegment2 === 'add') {
				// @note: will return a custom add new item form!
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a country tax rate
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL categories
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeReports() {
		// $this->headline($this->_("Reports"));
		$out = $this->pagesHandler();
		//--------------------
		return $out;
	}

	public function executeGeneralSettings() {
		$this->headline($this->_("General Settings"));
		$out = $this->pagesHandler();
		//--------------------
		return $out;
	}

	public function executeCheckoutSettings() {
		$this->headline($this->_("Checkout Settings"));
		$out = $this->pagesHandler();
		//--------------------
		return $out;
	}

	public function executePaymentProviders() {

		$this->headline($this->_("Payment Providers"));
		// $out = $this->pagesHandler();

		$urlSegment2 = $this->urlSegment2;

		// EDIT payment provider
		if ($urlSegment2 === 'edit') {
			// @note: without the trailing slash, we get errors when saving since no context (urlSegment1) found!
			// TODO: see if can force trailing slash
			// TODO CHANGE THIS FOR PAYMENT PROVIDERS!
			// TODO: REFACTOR! CAN'T WE BUIILD breadcrumbHREF FROM $this->context?
			$breadcrumbHREF = '../../payment-providers/';
			$label = $this->_('Payment Providers'); // the label for the link in the breadcrumb
			$out = $this->renderSpecialEditItem($breadcrumbHREF, $label);
		} else {
			//--------------------
			// ALL payment providers
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeLegalPages() {

		$this->headline($this->_("Legal Pages"));
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit legal pages
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			// TODO: REFACTOR! CAN'T WE BUIILD breadcrumbHREF FROM $this->context?
			$breadcrumbHREF = '../../legal-pages/';
			$label = $this->_('Legal Pages'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a legal page
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				// EDITING a legal page
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL legal pages
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeAddons() {

		################
		// TODO HANDLE URL SEGMENT 2 = 'view' AND url segment 3 => 'view_url' value
		// --------------
		// $this->headline($this->_("Shop Addons"));
		$this->headline($this->_("Addons"));
		if (empty($this->pwcommerce->isShopAllowAddons())) {
			// ADDONS NOT enabled
			$shopGeneralSettingsLink =
				"<a href='{$this->adminURL}general-settings/'>" .
				$this->_('General Settings') .
				"</a>";
			$addonsNotEnabledNotice = sprintf(__("Shop is not currently set up to install addons. This can be enabled in %s."), $shopGeneralSettingsLink);
			$out = "<div><p>" .
				// ----
				$addonsNotEnabledNotice .
				"</p></div>";
		} else {
			// ADDONS enabled

			$urlSegment2 = $this->urlSegment2;
			$urlSegment3 = $this->urlSegment3;

			// handle view 'viewable' addon
			if ($urlSegment2 === 'view' && !empty($urlSegment3)) {
				// @note: without the trailing slash, we get errors when saving since no context (urlSegment1) found!
				// TODO: see if can force trailing slash
				$breadcrumbHREF = '../../addons/';
				$label = $this->_('Addons'); // the label for the link in the breadcrumb
				//--------------------
				// VIEWING an addon that might be viewable
				$out = $this->renderViewItem($breadcrumbHREF, $label);
			} else {
				//--------------------
				// ALL addons
				$out = $this->pagesHandler();
			}
		}

		// --------
		return $out;
	}

	public function executeGiftCardProducts() {

		$this->headline($this->_("Gift Card Products"));

		$urlSegment2 = $this->urlSegment2;

		// handle add or edit gift card product
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit'])) {
			// @note: without the trailing slash, we get errors when saving since no context (urlSegment1) found!
			// TODO: see if can force trailing slash
			$breadcrumbHREF = '../../gift-card-products/';
			$label = $this->_('Gift Card Products'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a gift card product
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else {
				//------------------
				// EDITING a gift card product
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL gift card products
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeGiftCards() {

		$this->headline($this->_("Gift Cards"));
		$urlSegment2 = $this->urlSegment2;

		// handle view issued gift cards or manually issue a gift card
		if (!empty($urlSegment2) && in_array($urlSegment2, ['view', 'issue'])) {
			// @note: without the trailing slash, we get errors when saving since no context (urlSegment1) found!
			// TODO: see if can force trailing slash
			$breadcrumbHREF = '../../gift-cards/';
			$label = $this->_('Gift Cards'); // the label for the link in the breadcrumb
			//--------------------
			// VIEWING a GIFT CARD
			if ($urlSegment2 === 'view') {
				$out = $this->renderViewItem($breadcrumbHREF, $label);
			} else {
				//------------------
				// ISSUE a GIFT CARD
				// @note this is similar to adding an item
				// hence we use 'add item'
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL gift cards
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	public function executeDiscounts() {

		$urlSegment2 = $this->urlSegment2;
		// handle add or edit category
		if (!empty($urlSegment2) && in_array($urlSegment2, ['edit'])) {
			$breadcrumbHREF = '../../discounts/';
			$label = $this->_('Discounts'); // the label for the link in the breadcrumb
			// @note: we don't have 'add' discount; instead, pre-processing happens in a modal; in order to get and set discount type before creating a discount. Currently, discount type is not editable once editing starts
			//--------------------
			// EDITING a discount
			if ($urlSegment2 === 'edit') {
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL discounts
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	/**
	 * Customers
	 *
	 * @return string $out Markup of customers.
	 */
	public function executeCustomers() {

		$urlSegment2 = $this->urlSegment2;
		// handle add, view or edit order
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'view', 'edit'])) {
			// @note: without the trailing slash, we get errors when saving since no context (urlSegment1) found!
			// TODO: see if can force trailing slash
			$breadcrumbHREF = '../../customers/';
			$label = $this->_('Customers'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a customer
			if ($urlSegment2 === 'add') {
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else if ($urlSegment2 === 'view') {
				//--------------------
				// VIEWING a customer
				$out = $this->renderViewItem($breadcrumbHREF, $label);
			} else {
				// EDITING a customer
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL customers
			$out = $this->pagesHandler();
		}
		//--------------------
		return $out;

	}

	/**
	 * Customer Groups
	 *
	 * @return string $out Markup of customer groups.
	 */
	public function executeCustomerGroups() {
		$this->headline($this->_("Customer Groups"));
		$urlSegment2 = $this->urlSegment2;
		// handle add or edit customer group
		if (!empty($urlSegment2) && in_array($urlSegment2, ['add', 'edit', 'view'])) {
			$breadcrumbHREF = '../../customer-groups/';
			$label = $this->_('Customer Groups'); // the label for the link in the breadcrumb
			//--------------------
			// ADDING a customer group
			if ($urlSegment2 === 'add') {
				// @note: will return the basic custom add new item form!
				$out = $this->renderAddItem($breadcrumbHREF, $label);
			} else if ($urlSegment2 === 'view') {
				//--------------------
				// VIEWING a customer group
				$out = $this->renderViewItem($breadcrumbHREF, $label);
			} else {
				// EDITING a customer group
				$out = $this->renderEditItem($breadcrumbHREF, $label);
			}
		} else {
			// ALL customer groups
			$out = $this->pagesHandler();
		}

		//--------------------
		return $out;
	}

	// -----------------

	/**
	 * A URL for receiving ajax calls and passing these on for processing.
	 *
	 * Actions include editing, inserting in page (adding) uploading, scanning, (un)publishing, (un)locking, trashing or deleting media.
	 *
	 * @access public
	 * @return string $data JSON-encoded string.
	 *
	 */
	public function executeAjax() {

		# >>>>>>>>>>>>>>>>>>>>>>>>>> AJAX <<<<<<<<<<<<<<<<<<<<<<<<< #
		if ($this->wire('config')->ajax) {

			// TODO: ADD CSRF CHECK HERE
			// determine whodunnit

			$get = $this->wire('input')->get;
			$post = $this->wire('input')->post;
			//------------

			$data = []; // for our JSON to feed back
			$options = []; // various options for action methods

			//----------------------------

			$out = "";
			//---------

			// ################################### $input->post ###################################

			// CALCULATE TAXES and/or SHIPPING CALLED
			// @note: htmx: send back html!
			// TODO: MIGHT MOVE TO INPUTFIELDPWCOMMERCEORDER TO HANDLE AFTER POST HERE
			if ((int) $post->pwcommerce_calculate_shipping_and_taxes) {
				return $this->processCalculateOrderTaxesAndShipping($post);
			}

			// PROCESS GENERATE VARIANTS
			elseif (!empty($post->pwcommerce_product_variant_preview_options_ids)) {
				return $this->processGenerateVariants();
			}
			// PROCESS HTMX REQUEST FOR A SINGLE ROW INLINE EDIT
			elseif (!empty($post->pwcommerce_is_inline_edit)) {
				// TODO: WIP!
				$this->context = $this->wire('sanitizer')->pageName($post->pwcommerce_inline_edit_context);
				// if is 'inline edit', e.g. inventory context, we save edits first then get response from render class
				return $this->processAjaxSingleInlineEdit();
			}

			// PROCESS HTMX REQUEST FOR FETCH PAGES FOR PWCOMMERCE CUSTOM LISTER
			elseif ($post->pwcommerce_inputfield_selector_context) {

				// set context since we are in ajax page but context is elsewhere
				$this->context = $this->wire('sanitizer')->pageName($post->pwcommerce_inputfield_selector_context);
				// check if in 'pagination'
				if ($post->pwcommerce_bulk_edit_custom_lister_pagination) {
					$this->currentPaginationNumberForContext = $this->getCurrentPaginationNumber($post->pwcommerce_bulk_edit_custom_lister_pagination);
					$this->setPaginationNumberCookieForContext();
				}
				//--------------
				// TODO: WIP - PROCESS POST!
				$is = $this->getInputfieldSelector();
				$is->processInput($this->input->post);

				// ---------
				$cookieCurrentListerSelectorStringForContext = strval($this->getLastSelectorForListerCookieForContext());
				$inputFieldSelectorValueString = $post->pwcommerce_inputfield_selector_value;
				if ($inputFieldSelectorValueString !== $cookieCurrentListerSelectorStringForContext) {
					// SELECTOR HAS CHANGED!
					// RESET PAGINATION TO 1!

					// clear/reset $this->currentPaginationNumberForContext
					// TODO: STILL NOT WORKING AS NEEDS TO BE CHECKED EARLIER AND AMENDED BEFORE $this->getPWCommerceContextRender() PASSES $options TO CLASSES!
					$this->currentPaginationNumberForContext = 1;
					$this->setPaginationNumberCookieForContext();
				}

				// TODO: WORK ON THIS!
				$this->selector = $is->value;
				// track in cookie
				// TODO THIS IS STILL BUGGY! IT TRIPS WHEN THIS CHANGES BUT CURRENT PAGE IS NOT IN SYNC LEADING TO wrong computation of $start in $this->getSelectorStart(). we need to reset $this->currentPaginationNumberForContext ONLY WHEN LIMIT CHANGES!
				$this->setPaginationLimitCookieForContext();
				// -------------
				// @note: for tracking last selector in a cookie for the comparison of changes above
				$this->setLastSelectorForListerCookieForContext();
				//---------------
				return $this->pagesHandler(true);
			}
			// PROCESS HTMX REQUEST FOR FETCH PAGES FOR PWCOMMERCE QUICK FILTER
			elseif ($post->pwcommerce_quick_filter_context) {

				// set context since we are in ajax page but context is elsewhere
				$this->context = $this->wire('sanitizer')->pageName($post->pwcommerce_quick_filter_context);


				// ---------

				// SELECTOR HAS CHANGED!
				// RESET PAGINATION TO 1!

				// TODO CONFIRM QUICK FILER STILL WORKS WITH PAGINATION

				// clear/reset $this->currentPaginationNumberForContext
				// TODO: STILL NOT WORKING AS NEEDS TO BE CHECKED EARLIER AND AMENDED BEFORE $this->getPWCommerceContextRender() PASSES $options TO CLASSES!
				$this->currentPaginationNumberForContext = 1;
				$this->setPaginationNumberCookieForContext();


				// track in cookie
				// TODO THIS IS STILL BUGGY! IT TRIPS WHEN THIS CHANGES BUT CURRENT PAGE IS NOT IN SYNC LEADING TO wrong computation of $start in $this->getSelectorStart(). we need to reset $this->currentPaginationNumberForContext ONLY WHEN LIMIT CHANGES!
				$this->setPaginationLimitCookieForContext();
				// -------------
				// @note: for tracking last selector in a cookie for the comparison of changes above
				$this->setLastSelectorForListerCookieForContext();
				//---------------
				return $this->pagesHandler(true);
			}
			// PROCESS HTMX REQUEST FOR GENERATING SALES REPORTS
			elseif (!empty($post->pwcommerce_generate_sales_report)) {
				$this->context = $this->wire('sanitizer')->pageName($post->pwcommerce_generate_sales_report_context);
				return $this->processAjaxGenerateReport();
			}
			# ++++++
			// TODO @NOTE: NOT IN USE FOR NOW; DUE TO DATEPICKER ISSUE, NOT SHOWING IN MODAL, WE NEED TO USE A SINGLE PAGE INSTEAD.
			// PROCESS HTMX REQUEST FOR GENERATE & MANUALLY ISSUE GIFT CARD
			// elseif (!empty($post->pwcommerce_manually_issue_gift_card)) {
			// 	$this->context = $this->wire('sanitizer')->pageName($post->pwcommerce_manually_issue_gift_card_context);
			// 	return $this->processAjaxManuallyIssueGiftCard();
			// }

			// TODO @NOTE: NOT IN USE FOR NOW
			// PROCESS HTMX REQUEST FOR CONFIGURE PWCOMMERCE INSTALL
			// elseif ((int) $post->pwcommerce_is_configure_install) {
			//
			// 	// set context manually since we are in ajax page but context is elsewhere
			// 	$this->context = "configure-pwcommerce";
			// 	return $this->processAjaxConfigureInstall();
			// }

			// PROCESS PWCOMMERCE ADDONS AJAX $_POST REQUESTS
			elseif (!empty((int) $post->is_pwcommerce_addon_ajax_post)) {

				// set context since we are in ajax page but context is elsewhere
				$this->context = 'addons';
				// set string to $this->selector for $this->pagesHandler() to pass to addons with info about type of $input
				// @note: here just reusing this available property, although this is not really a selector
				$this->selector = "POST";
				return $this->pagesHandler(true);
			}

			// PROCESS HTMX REQUEST TO APPLY/SAVE SELECTED ORDER STATUS ACTION + ACCOMPANYING NOTE FOR THE STATUS
			// @UPDATE: SATURDAY 22 APRIL 2023 - NOT DOING AS AJAX; DOING NORMAL POST
			// elseif (!empty((int) $post->pwcommerce_order_status_selected_action_apply)) {
			// 	$this->context = $this->wire('sanitizer')->pageName($post->pwcommerce_order_status_action_context);
			// 	// $this->selector = "POST";
			// 	return $this->processAjaxManuallySetOrderStatus();
			// }

			# ******************************** END: POST ********************************

			################################### $input->get ###################################

			// PROCESS FIND ANYTHING SEARCHES
			// TODO DEPRECATED SINCE PWCOMMERCE 009; @SEE HOOK 'hookProcessPageSearchLive'
			// TODO DELETE IN NEXT RELEASE
			// elseif (!empty($get->pwcommerce_find_anything_search_box)) {
			// 	return $this->processAjaxFindAnything();
			// }

			// PROCESS PWCOMMERCE ADDONS AJAX $_GET REQUESTS
			elseif (!empty((int) $get->is_pwcommerce_addon_ajax_get)) {

				// set context since we are in ajax page but context is elsewhere
				$this->context = 'addons';
				// set string to $this->selector for $this->pagesHandler() to pass to addons with info about type of $input
				// @note: here just reusing this available property, although this is not really a selector
				$this->selector = "GET";
				return $this->pagesHandler(true);
			}
			// PROCESS FETCH MARKUP FOR EDIT FOR SELECTED ORDER STATUS ACTION
			elseif (!empty((int) $get->pwcommerce_order_status_selected_action_fetch_markup)) {
				$this->context = $this->wire('sanitizer')->pageName($get->pwcommerce_order_status_action_context);
				$this->selector = "GET";
				return $this->pagesHandler(true);
			}
			# ++++++++++++


		} // end if ajax

		// if not ajax, go to shop landing page
		else {
			$this->session->redirect($this->wire('page')->url);
		}
	}

	/**
	 * PWCommerce second-step Installer GUI/page.
	 *
	 * Only accessible to Superusers via URL or redirect if new install.
	 * Renders the GUI for completing or modifying PWCommerce installation.
	 *
	 * @return string $out GUI output.
	 */
	public function executeConfigurePWCommerce() {
		return $this->renderConfigureInstall();
	}

	/**
	 * PWCommerce complete removal GUI/page.
	 *
	 * Only accessible to Superusers via URL.
	 * TODO: for now, url has to be typed in! Must include trailing slash!
	 * Renders the GUI for completely removing PWCommerce installation.
	 * This includes templates, fields, pages and attempt to uninstall all its modules.
	 *
	 * @return string $out GUI output.
	 */
	public function executeCompleteRemoval() {
		if (!$this->isSuperUser()) {
			// send non-superusers to shop home page
			$this->session->redirect($this->adminURL);
		}
		return $this->renderCompleteRemoval();
	}
}