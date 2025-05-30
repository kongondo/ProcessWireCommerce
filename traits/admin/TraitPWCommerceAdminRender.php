<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRender
{

	private $selector;
	private $pwcommerceRender;
	// the ALPINE JS store used by this Class
	private $xstoreProcessPWCommerce;
	// the full prefix to the ALPINE JS store used by this Class
	private $xstore;
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ RENDERERS  ~~~~~~~~~~~~~~~~~~

	// for single EDIT process view pages
	// i.e., tax settings, general settings, payment providers, etc
	// or for ADD NEW item (category, products, etc)
	private function getInputfieldButtonSingleEdit($options = []) {

		$defaultOptions = [
			'id' => null,
			'name' => null,
			'type' => 'submit',
			'label' => null,
			// for action save + exit is needed
			'save_and_exit' => false,
			// to make button secondary if needed
			'secondary' => false,
		];
		//-------------
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		//-----------

		//------------------- save button (getInputfieldButton)

		$field = $this->pwcommerce->getInputfieldButton($options);
		// show in header if needed
		if (!empty($options['showInHeader'])) {
			$field->showInHeader();
		}
		//-------------
		// add value to process save and exit if needed
		if (!empty($options['save_and_exit'])) {
			$actionValue = 'save_and_exit';
			$saveAndExitLabel = $this->_('Save + Exit');
			$icon = 'close';
			$field->addActionValue($actionValue, $saveAndExitLabel, $icon);
		}

		//-------
		return $field;
	}

	private function renderConfigureInstall() {
		// TODO WIP!!!
		$post = $this->wire('input')->post;

		$form = $this->pwcommerce->getInputfieldForm();
		//   $form->attr('id', 'pwcommerce_tabs_wrapper');
		/** @var InputfieldWrapper $wrapper */
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		// pass to INSTALLER to process
		$shopAdminPWCommerceRootPage = $this->page->child("name=" . PwCommerce::CHILD_PAGE_NAME . ",include=all");
		$shopAdminPWCommerceRootPageID = $shopAdminPWCommerceRootPage->id;

		// --------------
		$options = [
			'config_module_name' => PwCommerce::PWCOMMERCE_PROCESS_MODULE,
			// -----------
			// $this Process Module values (i.e., ProcessPWCommerce)
			'shop_process_pwcommerce_page_url' => $this->adminURL, // the URL of the admin page for ProcessPWCommerce (title is 'Shop')
			'shop_process_pwcommerce_page_id' => $this->adminPageID, // the page ID of the above page
			// -----------
			// the single child page of 'Shop' with template 'pwcommerce' and title 'PWCommerce'
			// it is the root parent page of all pwcommerce parent pages
			'shop_admin_pwcommerce_root_page_id' => $shopAdminPWCommerceRootPageID,
		];

		$pwcommerceAdminRenderInstaller = $this->pwcommerce->getPWCommerceClassByName('PWCommerceAdminRenderInstaller', $options);
		// ------
		$status = $this->getConfigurePWCommerceStatus();
		$headline = $this->getConfigurePWCommerceHeadline($status);
		$this->headline($headline);

		// --------
		$out = $pwcommerceAdminRenderInstaller->renderInstaller($status);


		//------------------- content (getInputfieldMarkup)
		// @note: $out here was generated earlier up

		$options = [
			'id' => 'pwcommerce_configure_install_contents_wrapper',
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// add content from current context render class
		$wrapper->add($field);
		// =====
		//------------------- save button (getInputfieldButton)
		// @note: only if installer ready to run
		// in this case, we check if 'pwcommerce order status' table is present in a fresh install
		// TODO DO WE ALSO CHECK 'pwcommerce_cart' table?
		$orderStatusTableName = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE_NAME;
		if (!empty($this->isConfigurePWCommerceComplete) || empty($this->pwcommerce->isExistPWCommerceCustomTable($orderStatusTableName))) {
			$options = [
				'id' => "pwcommerce_configure_install_button",
				'name' => "pwcommerce_configure_install_button",
				'label' => $this->_('Run Configure Install'),
				'showInHeader' => true,
				'type' => 'button'
			];
			$field = $this->getInputfieldButtonSingleEdit($options);
			// TODO: NOT IN USE FOR NOW; DOESN'T WORK FOR BOTTOM BUTTON!
			// we'll use vanilla js for now
			// $field->attr([
			// 	'x-on:click' => 'handlePWCommerceConfirmRunInstaller',
			// ]);
			// add submit button for single page process views
			$wrapper->add($field);
		}

		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//------------------------------
		// HANDLE POST

		// TODO? SEEMS FORM IS ALWAYS POSTED, EVEN ON RELOAD? WHY?
		// TODO - WE NEED TO CATER FOR OTHER NEEDS! E.G. SINGLE EDIT SUCH AS GENERAL SETTINGS!
		// TODO - IN THAT CASE RENAME INPUTFIELD AND CALL IT IS READY TO SAVE?
		// we check if ready to post an action (action selected + at least one item selected)
		if (!empty($post->pwcommerce_is_ready_to_save)) {

			$this->processConfigureInstall($form);
		}
		// ------------------
		// render form
		$out = $form->render();

		// -----------------------
		// add alpinejs
		$out .= $this->getInlineScripts();
		// -------
		return $out;
	}

	// ~~~~~~~~~~~~~~~~~~~
	// QUICK FILTERS
	protected function ___getContextQuickFilters() {
		// get the render for the current context
		$this->pwcommerceRender = $this->getPWCommerceContextRender();

		$contextQuickFilters = $this->renderContextQuickFilters();

		$hxParams = "pwcommerce_quick_filter_value,pwcommerce_quick_filter_context";
		$quickFilterMarkup = "<div id='pwcommerce_quick_filter_wrapper' hx-trigger='pwcommercefetchpagesforquickfilter delay:300ms' hx-target='#pwcommerce_bulk_edit_custom_lister' hx-post='{$this->ajaxPostURL}'  hx-params='{$hxParams}' hx-swap='outerHTML' hx-indicator='#pwcommerce_quick_filter_spinner' x-data='ProcessPWCommerceData'>" .
			$contextQuickFilters .
			// spinner
			"<span id='pwcommerce_quick_filter_spinner' class='fa fa-fw fa-spin fa-spinner htmx-indicator'></span>" .
			"</div>";
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_content_padding_left',
			// if showing label 'quick filters'
			// 'value' => "<div><span>" . $this->_('Quick Filters') . "</span>{$contextQuickFilters}</div>",
			'value' => $quickFilterMarkup,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// -----
		return $field;
	}
	// ~~~~~~~~~~~~~~~~~~~

	// -------------------------
	/**
	 * Enables rendering of a view for adding a new items for a context.
	 *
	 * Items could be a new category, tag, product, order, etc.
	 * @note: Not all context need or use this!
	 *
	 * @param string $breadcrumbHREF The link for breadcrumb.
	 * @param string $breadcrumbLabel The label for breadcrumb.
	 * @return void
	 */
	protected function renderAddItem($breadcrumbHREF, $breadcrumbLabel) {
		// get the render for the current ADD context
		$pwcommerceRender = $this->getPWCommerceContextRender();
		$out = "";
		$post = $this->wire('input')->post;
		//---------
		$defaultOptions = [
			'description' => '',
			'notes' => '',
			'label' => $this->_('Title'),
			'headline' => '',
		];
		// get the options for adding new item(s) for this context
		/** @var array $options */
		$options = $this->getContextAddNewItemOptions();
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}

		// ------------------

		// CHECK IF THE CURRENT CONTEXT USES THE BASIC ADD NEW ITEM FORM
		if ($this->isContextUseBasicAddNewItemForm()) {
			//----------------
			// get the basic form for adding new item for given context
			$form = $this->getBasicAddNewItemForm($options);

		} else {
			// @note: order and any other view that needs it
			// get the custom form for adding new item for given context
			$form = $pwcommerceRender->getCustomAddNewItemForm();
			// TODO WE NEED TO AMEND TO HANDLE 'SPECIAL ADD NEW ITEM' SUCH AS ISSUE GIFT CARD TODO see how we do it in add new country (for taxes) => @SEE PWCommerceActions::addNewItemAction line #251! we return early for 'tax-rates' context! we call a method addNewCountriesAction()...CREATE SIMILAR FOR 'gift-cards', i.e. addNewManualIssueGiftCardAction()!

		}
		// ---------------

		// SET BREADCRUMB AND HEADLINE

		//------------------
		// set custom headline if we have one
		if (!empty($options['headline'])) {
			$this->headline($options['headline']);
		}

		//--------------
		// set breadcrumb
		// @note: in some contexts, this will be hooked into and modified @see modifyBreadcrumb()
		$this->breadcrumb($breadcrumbHREF, $breadcrumbLabel);

		//------------------------------
		// HANDLE POST

		// we check if ready to save add new item
		// @note: used here for consistency with other form handling in the module + future proofing (e.g. check if title is filled on client-side)
		if (!empty($post->pwcommerce_is_ready_to_save)) {

			// SINGLE ADD process views
			$this->processAddNewItem($form);
		}

		//===========================
		// FINAL OUTPUT

		$out .= $form->render();

		//------------------
		// ALL CONTEXTS DETECT IF IN PWCOMMERCE SHOP CONTEXT
		// @note: if a pwcommerce page is being edited in usual edit, will tell JavaScript not to init PWCommerce scripts!
		// @note: if we add after getMenuPanelBelow() it is getting pulled inside the panel markup! Not a biggy, but we don't want that
		$out .= $this->getDetectIfInPWCommerceShopContextMarkup();

		//------------------
		// ALL CONTEXTS SET CURRENT PWCOMMERCE SHOP CONTEXT
		$out .= $this->getPWCommerceShopCurrentContextMarkup();

		//------------------
		// ALL CONTEXTS MENU
		// @note: processwire side panel!
		$out .= $this->getMenuPanel();

		// ------------
		return $out;
	}

	// -------------------------

	/**
	 * Enables rendering of a page as within ProcessPageEdit.
	 *
	 * @param string $breadcrumbHREF The link for breadcrumb.
	 * @param string $breadcrumbLabel The label for breadcrumb.
	 * @return string $out The markup for edit page view.
	 */
	protected function renderEditItem($breadcrumbHREF, $breadcrumbLabel) {
		$out = '';
		//------------------
		// get embeded edit form (ProcessPageEdit::execute())
		$editForm = $this->getEmbeddedEdit($breadcrumbHREF, $breadcrumbLabel);
		// ***********************
		// IF AJAX (FILES/IMAGES UPLOAD) DON'T INTERFERE @NOTE: IF NOT CHECKED, AFFECTS UPLOADS
		if ($this->config->ajax) {
			return $editForm;
		}
		// ***********************

		// continue with usual non-ajax content
		$out .= $editForm;
		//------------------
		// ALL CONTEXTS DETECT IF IN PWCOMMERCE SHOP CONTEXT
		// @note: if a pwcommerce page is being edited in usual edit, will tell JavaScript not to init PWCommerce scripts!
		// @note: if we add after getMenuPanelBelow() it is getting pulled inside the panel markup! Not a biggy, but we don't want that
		$out .= $this->getDetectIfInPWCommerceShopContextMarkup();

		//------------------
		// ALL CONTEXTS SET CURRENT PWCOMMERCE SHOP CONTEXT
		$out .= $this->getPWCommerceShopCurrentContextMarkup();

		//----------
		// ALL CONTEXTS MENU
		// add menu panel
		$out .= $this->getMenuPanel();

		// ---------------
		return $out;
	}

	/**
	 * Enables rendering of an edit form for special edit views such as edit Payment Providers.
	 *
	 * @param string $breadcrumbHREF The link for breadcrumb.
	 * @param string $breadcrumbLabel The label for breadcrumb.
	 * @return string $out The markup for edit page view.
	 */
	protected function renderSpecialEditItem($breadcrumbHREF, $breadcrumbLabel) {
		// get the render for the current SPECIAL EDIT context
		$pwcommerceRender = $this->getPWCommerceContextRender();
		//------------
		// get requested context single item edit ID
		$id = (int) $this->wire('input')->get('id');
		// get the page to edit
		$page = $this->wire('pages')->get("id={$id}");
		// append headline if needed
		$headline = $pwcommerceRender->renderSpecialEditItemHeadline($page);
		//------------------
		// set headline
		$this->headline($headline);
		// set breadcrumb
		$this->breadcrumb($breadcrumbHREF, $breadcrumbLabel);

		// --------------------

		// TODO WIP!!!
		$post = $this->wire('input')->post;

		$form = $this->pwcommerce->getInputfieldForm();

		//------------------
		// get context single item special edit or 'not found page' view if applicable
		/** @var InputfieldWrapper $wrapper */
		$wrapper = $pwcommerceRender->renderSpecialEditItem($page);

		// ###########################
		// save button
		$options = [
			'id' => "submit_save",
			'name' => "pwcommerce_save_button",
			'label' => $this->_('Save'),
			'save_and_exit' => true,
			'showInHeader' => true,
		];
		$field = $this->getInputfieldButtonSingleEdit($options);
		// add submit button for single page process views
		$wrapper->add($field);

		// #############################

		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//------------------------------
		// HANDLE POST

		// @note: we use this ready check just for consistence with $this->pagesHandler() BUT ALSO for future use if required

		if (!empty($post->pwcommerce_is_ready_to_save)) {
			// SINGLE EDIT for this special renderSpecialEditItem such as EDIT PAYMENT PROVIDER
			// TODO!!! WORK ON THIS!
			// TODO @note -> errors not getting caught for now in our non-tabs forms! e.g. tax settings or payment provider, so, will need to check missing required values ourselves. for tab-based forms, e.g. general settings, getErrors() works fine.
			$this->processSingleEdit($form);
		}

		// ----

		## START NEW FORM OUTPUT ##
		// add form to output
		$out = $form->render();

		// ####################################
		//------------------
		// ALL CONTEXTS DETECT IF IN PWCOMMERCE SHOP CONTEXT
		// @note: if a pwcommerce page is being edited in usual edit, will tell JavaScript not to init PWCommerce scripts!
		// @note: if we add after getMenuPanelBelow() it is getting pulled inside the panel markup! Not a biggy, but we don't want that
		$out .= $this->getDetectIfInPWCommerceShopContextMarkup();

		//------------------
		// ALL CONTEXTS SET CURRENT PWCOMMERCE SHOP CONTEXT
		$out .= $this->getPWCommerceShopCurrentContextMarkup();

		//----------
		// ALL CONTEXTS MENU
		// -----------------
		// append menu panel if in use
		$out .= $this->getMenuPanel();

		// ------------
		return $out;
	}

	private function renderViewItem($breadcrumbHREF, $breadcrumbLabel) {
		// get the render for the current VIEW context
		$pwcommerceRender = $this->getPWCommerceContextRender();
		//------------
		// TODO NEED TO AMEND AS ADDONS DON'T DO IDS IN ALL CASES?
		if ($this->isContextNeedPageForViewtItem()) {
			// get requested context single item view ID
			$id = (int) $this->wire('input')->get('id');
			// get the page to view
			$page = $this->wire('pages')->get("id={$id}");
			// append headline if needed
			$headline = $pwcommerceRender->renderViewItemHeadline($page);
			//------------------
			// get context single item view or 'not found page' view if applicable
			$out = $pwcommerceRender->renderViewItem($page);
		} else {
			$headline = $pwcommerceRender->renderViewItemHeadline();
			$out = $pwcommerceRender->renderViewItem();
		}

		//------------------
		// set headline
		$this->headline($headline);
		// set breadcrumb
		$this->breadcrumb($breadcrumbHREF, $breadcrumbLabel);

		$form = $this->pwcommerce->getInputfieldForm();
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		$options = [
			'id' => 'pwcommerce_view_item_context_contents_wrapper',
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// add content from current context render class
		$wrapper->add($field);
		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//------------------------------
		// HANDLE POST
		// TODO WIP!!!
		$post = $this->wire('input')->post;

		// @note: we use this ready check just for consistency with $this->pagesHandler() BUT ALSO for future use if required

		if (!empty($post->pwcommerce_is_ready_to_save)) {
			// SINGLE EDIT for this special renderViewItem view such as VIEW ORDER
			if (!empty($this->isContextProcessesOwnForm())) {
				// if context is handled by a custom file, e.g. addons
				$pwcommerceRender->processForm($form, $post);
			} else {
				// otherwise, handled by PWCommerceActions::singleEditAction
				$this->processSingleEdit($form);
			}
		}

		// ----

		## START NEW FORM OUTPUT ##
		// add form to output
		$out = $form->render();

		// ####################################
		//------------------
		// ALL CONTEXTS DETECT IF IN PWCOMMERCE SHOP CONTEXT
		// @note: if a pwcommerce page is being edited in usual edit, will tell JavaScript not to init PWCommerce scripts!
		// @note: if we add after getMenuPanelBelow() it is getting pulled inside the panel markup! Not a biggy, but we don't want that
		$out .= $this->getDetectIfInPWCommerceShopContextMarkup();

		//------------------
		// ALL CONTEXTS SET CURRENT PWCOMMERCE SHOP CONTEXT
		$out .= $this->getPWCommerceShopCurrentContextMarkup();

		//----------
		// ALL CONTEXTS MENU
		// -----------------
		// append menu panel if in use
		$out .= $this->getMenuPanel();

		// ------------
		return $out;
	}
	protected function renderPrintItem($breadcrumbHREF, $breadcrumbLabel) {
		// get the render for the current VIEW context
		$pwcommerceRender = $this->getPWCommerceContextRender();
		//------------
		// get requested context single item view ID
		$id = (int) $this->wire('input')->get('id');
		// get the page to view
		$page = $this->wire('pages')->get("id={$id}");
		// append headline if needed
		// TODO NEEDED?
		$headline = $pwcommerceRender->renderViewItemHeadline($page);
		//------------------
		// set headline
		$this->headline($headline);
		// set breadcrumb
		$this->breadcrumb($breadcrumbHREF, $breadcrumbLabel);
		//------------------
		// get context single item print page if applicable
		$out = $pwcommerceRender->renderPrintItem($page);

		############### TODO DELETE BELOW IF NOT REQUIRED?? ##############
		// TODO - I DON'T THINK WE NEED A FORM AND WRAPPER?

		$form = $this->pwcommerce->getInputfieldForm();
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		$options = [
			'id' => 'pwcommerce_print_item_context_contents_wrapper',
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// add content from current context render class
		$wrapper->add($field);
		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//------------------------------
		// HANDLE POST
		// TODO WIP!!!
		$post = $this->wire('input')->post;

		// @note: we use this ready check just for consistency with $this->pagesHandler() BUT ALSO for future use if required

		if (!empty($post->pwcommerce_is_ready_to_save)) {
			// SINGLE EDIT for this special renderViewItem view such as VIEW ORDER
			$this->processSingleEdit($form);
		}

		// ----

		## START NEW FORM OUTPUT ##
		// add form to output
		$out = $form->render();

		// TODO I DON'T THINK WE NEED THE BELOW? NO MENU, ETC! -> ALSO, SHOULD WE OPEN IN NEW TAB?
		// TODO OR DO WE NEED JUST CSS FOR CTR+P?

		// ####################################
		//------------------
		// ALL CONTEXTS DETECT IF IN PWCOMMERCE SHOP CONTEXT
		// @note: if a pwcommerce page is being edited in usual edit, will tell JavaScript not to init PWCommerce scripts!
		// @note: if we add after getMenuPanelBelow() it is getting pulled inside the panel markup! Not a biggy, but we don't want that
		$out .= $this->getDetectIfInPWCommerceShopContextMarkup();

		//------------------
		// ALL CONTEXTS SET CURRENT PWCOMMERCE SHOP CONTEXT
		$out .= $this->getPWCommerceShopCurrentContextMarkup();

		//----------
		// ALL CONTEXTS MENU
		// -----------------
		// append menu panel if in use
		$out .= $this->getMenuPanel();

		// ------------
		return $out;
	}
	protected function renderEmailItem($breadcrumbHREF, $breadcrumbLabel) {
		// TODO - CHANGE BELOW? THIS IS NOT REALLY A VIEW! IT WILL REDIRECT TO /order/view/ after sending email!
		// --------------
		// get the render for the current VIEW context
		$pwcommerceRender = $this->getPWCommerceContextRender();
		//------------
		// get requested context single item view ID
		$id = (int) $this->wire('input')->get('id');
		// get the page to view
		$page = $this->wire('pages')->get("id={$id}");
		// append headline if needed
		// TODO NEEDED?
		$headline = $pwcommerceRender->renderViewItemHeadline($page);
		//------------------
		// set headline
		$this->headline($headline);
		// set breadcrumb
		$this->breadcrumb($breadcrumbHREF, $breadcrumbLabel);
		//------------------
		// @note: just getting notice here; email already sent at this point; nothing to render!
		/** @var array $result */
		$result = $pwcommerceRender->renderEmailItem($page);

		// notice(s)
		$this->renderNotices($result);
		$this->session->redirect($this->adminURL . $this->context . $result['special_redirect']);

		// TODO - DELETE BELOW IF NOT REQUIRED AS WE DON'T RENDER ANYTHING HERE!
		$out = "";

		############### TODO DELETE BELOW IF NOT REQUIRED?? ##############
		// TODO - I DON'T THINK WE NEED A FORM AND WRAPPER?

		$form = $this->pwcommerce->getInputfieldForm();
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		$options = [
			'id' => 'pwcommerce_print_item_context_contents_wrapper',
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// add content from current context render class
		$wrapper->add($field);
		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//------------------------------
		// HANDLE POST
		// TODO WIP!!!
		$post = $this->wire('input')->post;

		// @note: we use this ready check just for consistency with $this->pagesHandler() BUT ALSO for future use if required

		if (!empty($post->pwcommerce_is_ready_to_save)) {
			// SINGLE EDIT for this special renderViewItem view such as VIEW ORDER
			$this->processSingleEdit($form);
		}

		// ----

		## START NEW FORM OUTPUT ##
		// add form to output
		$out = $form->render();

		// TODO I DON'T THINK WE NEED THE BELOW? NO MENU, ETC!

		// ####################################
		//------------------
		// ALL CONTEXTS DETECT IF IN PWCOMMERCE SHOP CONTEXT
		// @note: if a pwcommerce page is being edited in usual edit, will tell JavaScript not to init PWCommerce scripts!
		// @note: if we add after getMenuPanelBelow() it is getting pulled inside the panel markup! Not a biggy, but we don't want that
		$out .= $this->getDetectIfInPWCommerceShopContextMarkup();

		//------------------
		// ALL CONTEXTS SET CURRENT PWCOMMERCE SHOP CONTEXT
		$out .= $this->getPWCommerceShopCurrentContextMarkup();

		//----------
		// ALL CONTEXTS MENU
		// -----------------
		// append menu panel if in use
		$out .= $this->getMenuPanel();

		// ------------
		return $out;
	}


	// -------------------------

	protected function renderGenerateReport(array $reportItems) {
		//-------------
		// get the render for the current context
		$pwcommerceRender = $this->getPWCommerceContextRender();
		// ----------------
		return $pwcommerceRender->renderReport($reportItems);
	}

	private function renderNotices($result) {

		$noticeType = $result['notice_type'];
		$notice = $result['notice'];
		// notice(s)
		if ($noticeType === 'success') {
			$this->message($notice);
		} else if ($noticeType === 'warning') {
			$this->warning($notice);
		} else if ($noticeType === 'error') {
			$this->error($notice);
		}
	}

	// -------------------------

	private function getSingleInlineEditedMarkup($pageID) {
		//-------------
		// get the render for the current context
		$pwcommerceRender = $this->getPWCommerceContextRender();
		//----------------------
		// return markup for single inline edit to htmx
		$out = $pwcommerceRender->renderSingleInlineEditResults($pageID);
		// ----------------
		return $out;
	}
	// -------------------------

	private function renderCompleteRemoval() {

		// TODO: NOT WORKING PROPERLY! NOT RECEIVING POST! MAYBE checkPWCommerceConfiguration() ??
		// TODO WIP!!!
		$post = $this->wire('input')->post;

		$form = $this->pwcommerce->getInputfieldForm();
		//   $form->attr('id', 'pwcommerce_tabs_wrapper');
		/** @var InputfieldWrapper $wrapper */
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		// --------------
		// $pwcommerceAdminRenderCompleteRemoval = $this->pwcommerce->getPWCommerceClassByName('PWCommerceAdminRenderInstaller');
		$shopAdminPWCommerceRootPage = $this->page->child("name=" . PwCommerce::CHILD_PAGE_NAME . ",include=all");
		$shopAdminPWCommerceRootPageID = $shopAdminPWCommerceRootPage->id;
		$options = [
			'config_module_name' => PwCommerce::PWCOMMERCE_PROCESS_MODULE,
			// -----------
			// $this Process Module values (i.e., ProcessPWCommerce)
			'shop_process_pwcommerce_page_url' => $this->adminURL, // the URL of the admin page for ProcessPWCommerce (title is 'Shop')
			'shop_process_pwcommerce_page_id' => $this->adminPageID, // the page ID of the above page
			// -----------
			// the single child page of 'Shop' with template 'pwcommerce' and title 'PWCommerce'
			// it is the root parent page of all pwcommerce parent pages
			'shop_admin_pwcommerce_root_page_id' => $shopAdminPWCommerceRootPageID,
		];

		$pwcommerceAdminRenderCompleteRemoval = $this->pwcommerce->getPWCommerceClassByName('PWCommerceAdminRenderInstaller', $options);
		// ------

		$headline = $this->_('Complete Removal');
		$this->headline($headline);

		// --------
		$out = $pwcommerceAdminRenderCompleteRemoval->renderCompleteRemoval();

		//------------------- content (getInputfieldMarkup)
		// @note: $out here was generated earlier up

		$options = [
			'id' => 'pwcommerce_complete_removal_contents_wrapper',
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// add content from current context render class
		$wrapper->add($field);
		// =====
		//------------------- save button (getInputfieldButton)
		$options = [
			'id' => "pwcommerce_complete_removal_button",
			'name' => "pwcommerce_complete_removal_button",
			'label' => $this->_('Run Complete Removal Tool'),
			'showInHeader' => true,
			'type' => 'button'
		];
		$field = $this->getInputfieldButtonSingleEdit($options);
		// TODO: NOT IN USE FOR NOW; DOESN'T WORK FOR BOTTOM BUTTON!
		// we'll use vanilla js for now
		// $field->attr([
		// 	'x-on:click' => 'handlePWCommerceConfirmRunInstaller',
		// ]);
		// add submit button for single page process views
		$wrapper->add($field);

		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//------------------------------
		// HANDLE POST

		// TODO? SEEMS FORM IS ALWAYS POSTED, EVEN ON RELOAD? WHY?
		// TODO - WE NEED TO CATER FOR OTHER NEEDS! E.G. SINGLE EDIT SUCH AS GENERAL SETTINGS!
		// TODO - IN THAT CASE RENAME INPUTFIELD AND CALL IT IS READY TO SAVE?
		// we check if ready to post an action (action selected + at least one item selected)
		if (!empty($post->pwcommerce_is_ready_to_save)) {

			$this->processCompleteRemoval($form);
		}
		// ------------------
		// render form
		$out = $form->render();

		// -----------------------
		// add alpinejs
		$out .= $this->getInlineScripts();
		// -------
		return $out;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ RENDERERS FOR CONTEXTS ~~~~~~~~~~~~~~~~~~

	protected function renderResults($selector = null) {



		// get the render for the current context
		$pwcommerceRender = $this->pwcommerceRender = $this->getPWCommerceContextRender();

		if (method_exists($this->pwcommerceRender, 'renderResults')) {
			return $this->pwcommerceRender->renderResults($selector);
		}
		$input = $this->wire('input');
		$isQuickFilter = false;

		// pwcommerce_quick_filter_value
		if ($this->wire('config')->ajax && $input->pwcommerce_quick_filter_value) {
			// BULK VIEW QUICK FILTER
			$isQuickFilter = true;
		}

		// enforce to string for strpos for PHP 8+
		$selector = strval($selector);



		//-----------------
		// FORCE DEFAULT LIMIT IF NO USER LIMIT SET
		if (strpos($selector, 'limit=') === false) {
			$limit = 10;
			$selector = rtrim("limit={$limit}," . $selector, ",");
		}


		//------------
		// FORCE TEMPLATE TO MATCH PWCOMMERCE CONTEXT TEMPLATE ONLY + INCLUDE ALL + EXLUDE TRASH

		$contextTemplateName = $this->getContextTemplateName();



		// $selector .= ",template=" . PwCommerce::TAG_TEMPLATE_NAME . ",include=all,sort=title,status<" . Page::statusTrash;
		$selector .= ",template={$contextTemplateName},include=all,sort=title,status<" . Page::statusTrash;

		// ----------
		// ADD SELECTOR FOR QUICK FILTER
		if (!empty($isQuickFilter)) {
			$selector .= $pwcommerceRender->getSelectorForQuickFilter();
		}



		//------------
		// ADD START IF APPLICABLE (ajax pagination)
		// TODO DELETE IF NOT IN USE
		// if (!empty($classOptions['selector_start'])) {
		// 	$start = (int) $classOptions['selector_start'];
		// 	$selector .= ",start={$start}";
		// }
		if (!empty($this->selectorStart)) {
			$start = (int) $this->selectorStart;
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
			$pwcommerceRender->getBulkEditActionsPanel($this->adminURL) .
			// PAGINATION STRING (e.g. 1 of 25)
			"<h3 id='pwcommerce_bulk_edit_custom_lister_pagination_string'>" . $pages->getPaginationString('') . "</h3>" .
			// TABULATED RESULTS (if pages found, else 'none found' message is rendered)
			$this->getResultsTable($pages) .
			// HIDDEN INPUT FOR HTMX
			// set the context for differentiation when in ajax page
			"<input type='hidden' value='{$this->context}' name='pwcommerce_inputfield_selector_context'>" .
			// PAGINATION (render the pagination navigation)
			$this->pwcommerce->getPagination($pages, $this->paginationOptions()) .
			//---------------
			"</div>";

		return $out;
	}

	private function getResultsTable($pages) {
		if (method_exists($this->pwcommerceRender, 'getResultsTable')) {
			return $this->pwcommerceRender->getResultsTable($pages);
		}

		#########

		$out = "";
		if (!$pages->count()) {
			// $out = "<p>" . $this->_('No tags found.') . "</p>";
			$out = "<p>" . $this->pwcommerceRender->getNoResultsTableRecords() . "</p>";
		} else {
			$field = $this->modules->get('MarkupAdminDataTable');
			$field->setEncodeEntities(false);
			// set headers (th)
			$headerRow = [];
			// SELECT ALL CHECKBOX
			$headerRow[] = $this->getContextsGenericResultsTableHeaders();
			// OTHER ROWS
			$contextHeaderRow = $this->pwcommerceRender->getResultsTableHeaders();
			$headerRow = array_merge($headerRow, $contextHeaderRow);

			$field->headerRow($headerRow);
			// +++++++++++++
			$checkBoxesName = "pwcommerce_bulk_edit_selected_items[]";
			// set each row

			foreach ($pages as $page) {
				$row = [];
				// CHECKBOX
				$row[] = $this->getBulkEditCheckbox($page->id, $checkBoxesName);
				// TITLE
				$editItemTitle = $this->getEditItemTitle($page);
				// OTHER ROWS
				$contextRow = $this->pwcommerceRender->getResultsTableRow($page, $editItemTitle);
				$row = array_merge($row, $contextRow);
				$field->row($row);
			}
			// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
			$out = $field->render();
		}
		return $out;
	}

	private function getContextsGenericResultsTableHeaders() {
		// TODO: DO WE USE TW CLASSES HERE?
		$selectAllCheckboxName = "pwcommerce_bulk_edit_selected_items_all";
		$xref = 'pwcommerce_bulk_edit_selected_items_all';
		// return [
		// 	// SELECT ALL CHECKBOX
		// 	$this->getBulkEditCheckbox('all', $selectAllCheckboxName, $xref),
		// ];

		// SELECT ALL CHECKBOX
		$headerSelectAllCheckbox = $this->getBulkEditCheckbox('all', $selectAllCheckboxName, $xref);
		// -------
		return $headerSelectAllCheckbox;
	}

	private function getCustomListerSettings() {

		return [
			'label' => $this->getContextListerLabel(),
			'inputfield_selector' => [
				'initValue' => "template=" . $this->getContextTemplateName(),
				'initTemplate' => $this->getContextTemplateName(),
				'showFieldLabels' => true,
			],

			// TODO; add columns!!!!
		];
	}

	private function getContextTemplateName() {
		$contextTemplateName = '';
		$contextsTemplateNames = $this->getContextsTemplateNames();
		if (!empty($contextsTemplateNames[$this->context])) {
			$contextTemplateName = $contextsTemplateNames[$this->context];
		}
		return $contextTemplateName;
	}

	private function getContextsTemplateNames() {
		return [
			'attributes' => PwCommerce::ATTRIBUTE_TEMPLATE_NAME,
			'brands' => PwCommerce::BRAND_TEMPLATE_NAME,
			'categories' => PwCommerce::CATEGORY_TEMPLATE_NAME,
			'collections' => PwCommerce::CATEGORY_TEMPLATE_NAME,
			'customers' => PwCommerce::CUSTOMER_TEMPLATE_NAME,
			'customer-groups' => PwCommerce::CUSTOMER_GROUP_TEMPLATE_NAME,
			'dimensions' => PwCommerce::DIMENSION_TEMPLATE_NAME,
			'discounts' => PwCommerce::DISCOUNT_TEMPLATE_NAME,
			'downloads' => PwCommerce::DOWNLOAD_TEMPLATE_NAME,
			'inventory' => PwCommerce::PRODUCT_TEMPLATE_NAME,
			'legal-pages' => PwCommerce::LEGAL_PAGE_TEMPLATE_NAME,
			'orders' => PwCommerce::ORDER_TEMPLATE_NAME,
			'payment-providers' => PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME,
			'products' => PwCommerce::PRODUCT_TEMPLATE_NAME,
			'properties' => PwCommerce::PROPERTY_TEMPLATE_NAME,
			'shipping' => PwCommerce::SHIPPING_ZONE_TEMPLATE_NAME,
			'tags' => PwCommerce::TAG_TEMPLATE_NAME,
			'tax-rates' => PwCommerce::COUNTRY_TEMPLATE_NAME,
			'types' => PwCommerce::TYPE_TEMPLATE_NAME,

		];
	}

	private function getContextAddNewItemOptions() {
		$pwcommerceRender = $this->getPWCommerceContextRender();
		if (method_exists($pwcommerceRender, 'getAddNewItemOptions')) {
			return $pwcommerceRender->getAddNewItemOptions();
		}
		$contextsAddNewItemOptions = $this->getContextsAddNewItemOptions();
		$contextAddNewItemOptions = $contextsAddNewItemOptions[$this->context];
		return $contextAddNewItemOptions;
	}

	/**
	 * Get the options for building the form to add a new Brand for use in ProcessPWCommerce.
	 *
	 * @access private
	 * @return array array with options for the form.
	 */
	private function getContextsAddNewItemOptions() {

		$contextsAddNewItemOptions =
			[
				'attributes' => [
					'label' => $this->_('Attribute Title'),
					'headline' => $this->_('Add New Attribute'),
				],
				'brands' => [
					'label' => $this->_('Brand Name'),
					'headline' => $this->_('Add New Brand'),
				],
				'categories' => [
					'label' => $this->_('Category Title'),
					'headline' => $this->_('Add New Category'),
				],
				'collections' => [
					'label' => $this->_('Collection Title'),
					'headline' => $this->_('Add New Collection'),
				],
				'customers' => [
					'label' => $this->_('Customer Email'),
					'headline' => $this->_('Add New Customer'),
				],
				'customer-groups' => [
					'label' => $this->_('Customer Group Title'),
					'headline' => $this->_('Add New Customer Group'),
				],
				'dimensions' => [
					'label' => $this->_('Dimension Title'),
					'headline' => $this->_('Add New Dimension'),
				],
				'discounts' => [
					'label' => $this->_('Discount Title'),
					'headline' => $this->_('Add New Discount'),
				],
				'downloads' => [
					'label' => $this->_('Download Title'),
					'headline' => $this->_('Add New Download'),
				],
				'legal-pages' => [
					'label' => $this->_('Legal Page Title'),
					'headline' => $this->_('Add New Legal Page'),
				],
				'products' => [
					'label' => $this->_('Product Title'),
					'headline' => $this->_('Add New Product'),
				],
				'properties' => [
					'label' => $this->_('Property Title'),
					'headline' => $this->_('Add New Product Property'),
				],
				'shipping' => [
					'label' => $this->_('Shipping Zone Name'),
					'headline' => $this->_('Add New Shipping Zone'),
				],
				'tags' => [
					'label' => $this->_('Tag Title'),
					'headline' => $this->_('Add New Tag'),
				],
				'tax-rates' => [
					// 'label' => $this->_('Country Title'),
					'headline' => $this->_('Add New Countries'),
				],
				'types' => [
					'label' => $this->_('Type Title'),
					'headline' => $this->_('Add New Type'),
				],

			];
		return $contextsAddNewItemOptions;
	}



	private function getContextsListerLabels() {

		$contextsListerLabels =
			[
				'attributes' => $this->_('Filter Attributes'),
				'brands' => $this->_('Filter Brands'),
				'categories' => $this->_('Filter Categories'),
				'collections' => $this->_('Filter Collections'),
				'customers' => $this->_('Filter Customers'),
				'customer-groups' => $this->_('Filter Customer Groups'),
				'dimensions' => $this->_('Filter Dimensions'),
				'discounts' => $this->_('Filter Discounts'),
				'downloads' => $this->_('Filter Downloads'),
				'inventory' => $this->_('Filter Inventory'),
				'legal-pages' => $this->_('Filter Legal Pages'),
				'orders' => $this->_('Filter Orders'),
				'products' => $this->_('Filter Products'),
				'properties' => $this->_('Filter Properties'),
				'shipping' => $this->_('Filter Shipping'),
				'tags' => $this->_('Filter Tags'),
				'tax-rates' => $this->_('Filter Countries'),
				'types' => $this->_('Filter Types'),

			];
		return $contextsListerLabels;
	}

	private function getContextListerLabel() {
		$contextsListerLabel = $this->getContextsListerLabels();
		$contextListerLabel = $contextsListerLabel[$this->context];
		return $contextListerLabel;
	}

	private function paginationOptions() {
		//------------
		// TODO CONFIRM BELOW WORKS WITH 'COLLECTIONS' AS CONTEXT!!!
		//------------
		$paginationOptions = ['base_url' => $this->adminURL . $this->context . '/', 'ajax_post_url' => $this->adminURL . 'ajax/'];
		return $paginationOptions;
	}

	private function getEditItemURL($page) {
		if (method_exists($this->pwcommerceRender, 'getEditItemURL')) {
			return $this->pwcommerceRender->getEditItemURL($page);
		}

		#########

		// if page is locked, don't show edit URL
		if ($page->isLocked()) {
			$out = "<span>{$page->title}</span>";
		} else {
			$out = "<a href='{$this->adminURL}{$this->context}/edit/?id={$page->id}'>{$page->title}</a>";
		}
		return $out;
	}

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

		if (method_exists($this->pwcommerceRender, 'extraStatuses')) {
			// get extra status from context, if available
			$extraStatuses = $this->pwcommerceRender->extraStatuses($page);
			if (!empty($extraStatuses)) {
				$status = array_merge($status, $extraStatuses);
			}

		}

		$statusString = implode(', ', $status);
		if ($statusString) {
			$out .= "<small class='block italic mt-1'>{$statusString}</small>";
		}
		// $out = "<a href='{$adminURL}tags/edit/?id={$page->id}'>{$page->title}</a>";
		return $out;
	}

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

	#### ~~~~~~~~~~ CONTEXTS WITH TABS ~~~~~~~~~~ ####

	protected function getTabs($wrapper) {
		// get the render for the current context
		$pwcommerceRender = $this->pwcommerceRender = $this->getPWCommerceContextRender();
		return $pwcommerceRender->getTabs($wrapper);
	}

}