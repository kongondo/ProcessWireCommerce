<?php

namespace ProcessWire;

trait TraitPWCommerceAdminContext
{

	protected $selectorStart;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CONTEXTS ~~~~~~~~~~~~~~~~~~

	private function getPWCommerceContextRender() {
		$adminURL = $this->adminURL;
		$ajaxPostURL = $this->ajaxPostURL;
		$options = [
			'current_page_number' => $this->currentPaginationNumberForContext,
			'admin_url' => $adminURL,
			'assets_url' => $this->assetsURL,
			'assets_path' => $this->assetsPath,
			'ajax_post_url' => $ajaxPostURL,
			//  --------------
			'xstoreProcessPWCommerce' => $this->xstoreProcessPWCommerce,
			'xstore' => $this->xstore,
		];

		if ($this->currentPaginationNumberForContext) {
			$this->selectorStart = $options['selector_start'] = $this->getSelectorStart();
		}
		$context = $this->context;

		$standardURLContexts = [
			'addons',
			'attributes',
			'brands',
			'categories',
			'collections',
			'customers',
			'dimensions',
			'discounts',
			'downloads',
			'inventory',
			'orders',
			'products',
			'properties',
			'reports',
			'shipping',
			'tags',
			'types',
		];
		$nonStandardURLContexts = [
			'checkout-settings',
			'customer-groups',
			'general-settings',
			'gift-card-products',
			'gift-cards',
			'legal-pages',
			'payment-providers',
			'tax-rates',
			'tax-settings',
		];

		$contextsRenderClassesRequireOptions = [
			'PWCommerceAdminRenderProducts',
			'PWCommerceAdminRenderInventory',
			'PWCommerceAdminRenderBrands',
			'PWCommerceAdminRenderShipping',
			'PWCommerceAdminRenderTaxRates',
			'PWCommerceAdminRenderGeneralSettings',
			'PWCommerceAdminRenderPaymentProviders',
			'PWCommerceAdminRenderAddons',
			'PWCommerceAdminRenderOrders',
			'PWCommerceAdminRenderReports',
			'PWCommerceAdminRenderGiftCardProducts',
			'PWCommerceAdminRenderGiftCards',
			'PWCommerceAdminRenderDiscounts',
			'PWCommerceAdminRenderCustomers',
			'PWCommerceAdminRenderShopHome',
		];

		# ----------------

		$contextClassName = "";
		$renderClass = "PWCommerceAdminRender";

		$isHome = false;

		if (in_array($context, $standardURLContexts)) {
			if ($context === 'collections') {
				$contextClassName = 'Categories';
			} else {
				$contextClassName = ucfirst($context);
			}
		} else if (in_array($context, $nonStandardURLContexts)) {
			$contextClassNameParts = explode("-", $context);
			foreach ($contextClassNameParts as $contextClassNamePart) {
				$contextClassName .= ucfirst($contextClassNamePart);
			}
		} else {
			$isHome = true;
		}

		if (!empty($isHome)) {
			// SHOP ADMIN LANDING PAGE
			$renderClass .= "ShopHome";
		} else {
			// SHOP OTHER ADMIN PAGES
			$renderClass .= "{$contextClassName}";
		}

		# GET RENDER CLASS
		if (in_array($renderClass, $contextsRenderClassesRequireOptions)) {
			// RENDER CLASS CONSTRUCTOR EXPECTS $options
			$pwcommerceRender = $this->pwcommerce->getPWCommerceClassByName($renderClass, $options);
		} else {
			// RENDER CLASS CONSTRUCTOR DOES NOT EXPECT $options
			$pwcommerceRender = $this->pwcommerce->getPWCommerceClassByName($renderClass);
		}

		return $pwcommerceRender;

	}


	// check if current context/view uses/needs InputfieldSelector
	private function isContextUseInputfieldSelector() {

		// ------------
		// ensure first save after install has been done before running the check
		if (!empty($this->pwcommerce->getShopGeneralSettings())) {
			// return early if advanced search not enabled!
			if (empty($this->pwcommerce->isUseAdvancedSearch())) {
				return false;
			}
		}

		$contextsNotUsingInputfieldSelector = [
			'',
			// @note: home/shop dashboard!
			'tax-settings',
			'general-settings',
			'checkout-settings',
			'payment-providers',
			'reports',
			'addons'
		];
		// ------------
		$isUseInputfieldSelector = !in_array($this->context, $contextsNotUsingInputfieldSelector);
		return $isUseInputfieldSelector;
	}

	// check if current context/view uses/needs quick filters.
	private function isContextUseQuickFilters() {

		// ------------
		// ensure first save after install has been done before running the check
		if (!empty($this->pwcommerce->getShopGeneralSettings())) {
			// return early if quick filters not enabled!
			if (empty($this->pwcommerce->isUseQuickFilters())) {
				return false;
			}
		}

		$contextsNotUsingQuickFilters = [
			'',		// @note: home/shop dashboard!
			'shipping',
			'tax-settings',
			'general-settings',
			'checkout-settings',
			'payment-providers',
			'legal-pages',
			'reports',
			'addons'
		];
		// ------------
		$isContextUseQuickFilters = !in_array($this->context, $contextsNotUsingQuickFilters);
		return $isContextUseQuickFilters;
	}

	private function isContextUseBulkEdit() {
		$contextsNotBulkEditing = [
			'',
			// @note: home/shop dashboard!
			'shipping',
			'tax-settings',
			// 'tax-rates', // TODO: might change this!
			'general-settings',
			'checkout-settings',
			// TODO @note in use for now; need to look for edits and activate!
			// 'payment-providers',
			'reports',
			// TODO?
			// 'gift-cards', // @note: since no user editable fields for now???
		];

		$isBulkEditing = !in_array($this->context, $contextsNotBulkEditing);
		//----------
		return $isBulkEditing;
	}

	private function isContextNeedSaveButton() {
		$contextsNeedingSaveButton = [
			'tax-settings',
			'general-settings',
			'checkout-settings',
			// TODO: NOT NEEDED IN BULK EDIT BUT NEEDED IN SINGLE SPECIAL EDIT!!!
			// 'payment-providers',
			// ---------
			// 'gift-cards',
		];
		$isNeedSaveButton = in_array($this->context, $contextsNeedingSaveButton);
		// -----
		return $isNeedSaveButton;
	}

	// check if current context/view uses tabs
	private function isContextUseTabs() {
		$contextsUsingTabs = [
			'general-settings',
			'checkout-settings',
			//'payment-providers',
		];

		$isNeedTabs = in_array($this->context, $contextsUsingTabs);
		//---------
		return $isNeedTabs;
	}

	// check if current context/view needs to modify the Process Breadcrumb
	private function isContextModifyBreadcrumb() {
		// TODO - DO WE NEED TO ADD 'addons' here?
		$contextsNotModifyingBreadcrumb = [
			'',
			// @note: home/shop dashboard!
			'tax-settings',
			'general-settings',
			'checkout-settings',
		];
		//--------------
		$isModifyingBreadcrumb = !in_array($this->context, $contextsNotModifyingBreadcrumb) && in_array($this->urlSegment2, ['add', 'view', 'edit']);
		return $isModifyingBreadcrumb;
	}

	// TODO: DELETE IF NOT N USE - @SEE getEmbeddedEdit() FOR ISSUE
	// check if current context/view allows DELETE TAB (ProcessPageEdit::buildFormDelete) when editing a single item in the embedded form (ProcessPageEdit::execute()) built via getEmbeddedEdit()
	// we don't want ORDER to be trashable this way!
	// @note: only applies to contexts that EDIT using getEmbeddedEdit()
	private function isContextProcessPageEditTrashable() {
		$contextsNotProcessPageEditTrashable = [
			'order',
		];
		//--------------
		$isProcessPageEditTrashable = !in_array($this->context, $contextsNotProcessPageEditTrashable);
		return $isProcessPageEditTrashable;
	}

	// check if current context/view uses/needs the basic add new form for adding new items
	// @note: only applies to contexts that ADD new items
	// e.g. category, product, order, etc
	private function isContextUseBasicAddNewItemForm() {
		$contextsNotUsingBasicAddNewItemForm = [
			//'orders',// TODO: @update: Saturday 04 September 2021 -> now uses basic form as well; with option 'title' TODO: delete when done
			'tax-rates',
			'gift-cards',
			'discounts',
			'products',
			'customers',
		];
		// ------------
		$isUseBasicAddNewItemForm = !in_array($this->context, $contextsNotUsingBasicAddNewItemForm);
		return $isUseBasicAddNewItemForm;
	}

	// check if current context/view uses/needs a save + publish button in the basic add new form for adding new items
	// @note: only applies to contexts that ADD new items
	// e.g. category, product, order, etc
	private function isContextAddNewItemUseSaveAndPublishButton() {
		$contextsNotUsingSaveAndPublishButtonInAddNewItemForm = [
			// @note: we don't allow publishing of new orders until they are marked as complete when editing
			'orders',
		];
		// ------------
		$isUseSaveAndPublishButtonInAddNewItemForm = !in_array($this->context, $contextsNotUsingSaveAndPublishButtonInAddNewItemForm);
		return $isUseSaveAndPublishButtonInAddNewItemForm;
	}

	// check if current context/view redirects to edit the item after add new items
	// otherwise redirects back to bulk edit/view
	// @note: only applies to contexts that ADD new items
	// e.g. category, product, order, etc
	private function isContextRedirectToEditAfterAddNewItem() {
		$contextsNotRedirectingToEditAfterAddNewItem = [
			'tax-rates',
			'gift-cards',
		];
		// ------------
		$isRedirectToEditAfterAddNewItem = !in_array($this->context, $contextsNotRedirectingToEditAfterAddNewItem);
		return $isRedirectToEditAfterAddNewItem;
	}

	// check if current context/view will need a page passed to its renderViewItem methods.
	// e.g. addons
	private function isContextNeedPageForViewtItem() {
		$contextsNotNeedPageForViewItem = [
			// @note: we need to redirect to bulk edit view even though these are single edits
			'addons',
		];
		// ------------
		$isNeedPageForViewItem = !in_array($this->context, $contextsNotNeedPageForViewItem);
		return $isNeedPageForViewItem;
	}

	// check if current context/view is a single special edit item.
	// e.g. payment providers special process single edit
	private function isContextSpecialEditItem() {
		$contextsIsSpecialEdit = [
			// @note: we need to redirect to bulk edit view even though these are single edits
			'payment-providers',
		];
		// ------------
		$isSpecialEditItem = in_array($this->context, $contextsIsSpecialEdit);
		return $isSpecialEditItem;
	}

	private function isContextProcessesOwnForm() {
		$contextsIsProcessesOwnForm = [
			// @note: addons process their own forms
			'addons',
		];
		// ------------
		$isProcessesOwnForm = in_array($this->context, $contextsIsProcessesOwnForm);
		return $isProcessesOwnForm;
	}

	// check if current context/view needs to do some intermediate pre-processing before creating/adding a new item
	// e.g. discounts need an intermediate 'select discount type' before creation
	// TODO DELETE IF NOT IN USE
	private function isContextNeedPreProcess() {
		$contextsNeedingPreProcess = [
			'discounts',
		];
		// ------------
		$isNeedPreProces = in_array($this->context, $contextsNeedingPreProcess);
		return $isNeedPreProces;
	}

	private function isEditable() {
		$isEditable = true;
		$id = (int) $this->input->get->id;
		if ($this->context === 'orders' && $this->urlSegment2 === 'edit') {
			$id = (int) $this->input->get->id;
			$isEditable = $this->isOrderEditable($id);
		}
		// -----------
		return $isEditable;
	}

	private function isOrderEditable($id) {
		$fields = 'pwcommerce_order.order_status';
		// @note: just using template here to be absolutely sure
		$orderStatus = (int) $this->wire('pages')->getRaw("template=" . PwCommerce::ORDER_TEMPLATE_NAME . ",id={$id}", $fields);
		// -------
		return (int) $orderStatus <= PwCommerce::ORDER_STATUS_DRAFT;
	}

	private function isNotInConfigurePWCommercePage() {
		return $this->context !== 'configure-pwcommerce';
	}

	private function removeCookieForContext($cookieName) {
		$this->wire('input')->cookie->remove($cookieName);
	}

	// @note: for future release; not in use for now
	private function getContextBrowserTitle() {
		$out = '';
		return $out;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CONTEXTS FOR CLIENT  ~~~~~~~~~~~~~~~~~~


	/**
	 * Hidden markup output in all views (add/view/edit/bulk) for detection in JavaScript if in 'shop'.
	 *
	 * Since PWCommerce Shop Pages can be edited outside the shop context
	 * we need a way to conditionally init some JavaScript code.
	 * For instance, to only init Alpine.js or htmx if a page is being edited from inside PWCommerce edit form.
	 *
	 * @access private
	 * @return string Hidden input markup for JavaScript to detect.
	 */
	private function getDetectIfInPWCommerceShopContextMarkup() {
		$options = [
			'id' => "pwcommerce_is_in_shop_context",
			'name' => 'pwcommerce_is_in_shop_context',
			'value' => true,
		];
		//------------------- is_in_shop_context (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		return $field->render();
	}

	/**
	 * Hidden markup output in all views (add/view/edit/bulk) to tell JavaScript shop context of current view.
	 *
	 * @note: The values match the context as used here in ProcessPWCommerce and not necessarily those used in InputfieldPWCommerceRuntimeMarkup!
	 * These include: 'products', 'orders', 'general-settings', etc.
	 * They are always plurals.
	 *
	 * @access private
	 * @return string Hidden input markup for JavaScript to detect current ProcessPWCommerce shop context.
	 */
	private function getPWCommerceShopCurrentContextMarkup() {
		$options = [
			'id' => "pwcommerce_shop_current_context",
			'name' => 'pwcommerce_shop_current_context',
			'value' => $this->context,
		];
		//------------------- shop_current_context (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		return $field->render();
	}


}