<?php

namespace ProcessWire;

trait TraitPWCommerceAdminForm
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ FORMS  ~~~~~~~~~~~~~~~~~~

	/**
	 * Builds a basic add new page/item for the context needing it.
	 *
	 * Returns InputfieldForm that includes an InputfieldPageTitle.
	 *
	 * @param array $addNewItemOptions Label for breadcrumb in this view.
	 * @return InputfieldForm $form Add new page Form.
	 */
	public function getBasicAddNewItemForm($addNewItemOptions) {
		$form = $this->pwcommerce->getInputfieldForm();
		//--------
		$useLanguages = $this->wire('languages') ? true : false;
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		$required = isset($addNewItemOptions['required']) ? $addNewItemOptions['required'] : true;

		$options = [
			'id' => "pwcommerce_add_new_item_title",
			'name' => "pwcommerce_add_new_item_title",
			'label' => $addNewItemOptions['label'],
			'useLanguages' => $useLanguages,
			'description' => $addNewItemOptions['description'],
			'notes' => $addNewItemOptions['notes'],
			'required' => $required,
			// TODO: needed?
			'collapsed' => Inputfield::collapsedNever,
			//'classes' => 'pwcommerce_add_new_item',
		];
		$field = $this->pwcommerce->getInputfieldPageTitle($options);
		$wrapper->add($field);
		//-----------------------------

		// ------------
		// ADD REQUIRED HIDDEN INPUT
		// lets renderAddItem() know that we are ready to save
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];
		//------------------- is_ready_to_save (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		//--------------------------------
		// ALL ADD CONTEXTS NEED SAVE BUTTON
		//------------------- save button (getInputfieldButton)
		$options = [
			'id' => "submit_save",
			'name' => "pwcommerce_save_new_button",
			'label' => $this->_('Save'),
			'showInHeader' => true,
		];
		$field = $this->getInputfieldButtonSingleEdit($options);
		// add submit button for single item add  SAVE process views
		$wrapper->add($field);

		// ADD SAVE + PUBLISH BUTTON IF CONTEXT ALLOWS IT
		if ($this->isContextAddNewItemUseSaveAndPublishButton()) {
			//------------------- save + publish button (getInputfieldButton)
			$options = [
				'id' => "submit_save_and_publish",
				'name' => "pwcommerce_save_and_publish_new_button",
				'label' => $this->_('Save + Publish'),
				'secondary' => true,
			];
			$field = $this->getInputfieldButtonSingleEdit($options);
			// add submit button for single item add  SAVE + PUBLISH process views
			$wrapper->add($field);
		}

		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//----------
		return $form;
	}

	/**
	 * Builds the page edit form for page being edited
	 *
	 * Returns output of ProcessPageEdit::execute().
	 *
	 * @param string $href Href for the breadcrumb in this view.
	 * @param string $label Label for breadcrumb in this view.
	 * @return string $editForm Edit page Form markup.
	 */
	public function getEmbeddedEdit($href, $label) {

		// TODO: ALSO NEED to check if is locked! -> for all pages actually!

		// ACCESS CHECK FOR UNDEDITABLE ORDERS!
		// if context is order and we are editing an order, we check if it is OPEN (editable)
		// if it isn't, we redirect to all orders
		// used if URL accessed directly
		if (!$this->isEditable()) {
			// order edit context BUT NOT editable
			// render notice
			$response = [
				'notice_type' => 'warning',
				'notice' => $this->_('This order is no longer editable!')
			];
			$this->renderNotices($response);
			// redirect!
			$this->session->redirect($this->adminURL . $this->context . "/");
		}

		// ---------------------
		// set breadcrumb
		// @note: in some contexts, this will be hooked into and modified @see modifyBreadcrumb()
		$this->breadcrumb($href, $label);
		$currAdminPage = $this->page;
		//=============
		// TODO:THIS MAKES  ProcessPageEdit::buildFormDelete not show! DO WE NEED THIS? MAYBE, CAN USE IT IN SOME CONTEXTS, E.G. ORDERS AS WE DON'T WANT THOSE TO BE DELETED, at least not easily!!!
		//   $id = (int) $this->input->get->id;
		// $this->fuel->page = $this->pages->get($id);
		// TODO: this CONDITION WORKS BUT NOT THE LOGIC INSIDE! It works without the CONDITION. For now, we'll allow delete here and instead use Hook to disallow when editing an Order
		//if ($this->isContextProcessPageEditTrashable()) {
		// $id = (int) $this->input->get->id;
		// $this->fuel->page = $this->pages->get($id);
		//}

		//==============
		$editForm = $this->modules->ProcessPageEdit->execute();
		// $this->fuel->page = $currAdminPage;
		// @note: fix for PHP 8.2 deprecation notice, i.e.
		// PHP Deprecated: Creation of dynamic property ProcessWire\Fuel::$page is deprecated in ..
		$this->fuel->set('page', $currAdminPage);
		return $editForm;
	}


	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PROCESS FORMS  ~~~~~~~~~~~~~~~~~~

	// TODO WIP
	public function processCalculateOrderTaxesAndShipping($input) {
		// @note: pass calculatations and matched rates markup to InputfieldPWCommerceOrder
		$inputfieldPWCommerceOrder = $this->wire('modules')->get('InputfieldPWCommerceOrder');
		$out = $inputfieldPWCommerceOrder->processCalculateOrderTaxesAndShipping($input);
		// ------
		return $out;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~

	private function processAddNewItem($form) {
		$input = $this->wire('input')->post;

		// process form
		$form->processInput($input);
		$errors = $form->getErrors();
		// TODO: BETTER ERROR MESSAGE HERE?
		if (count($errors)) {

			$this->error($this->_('There were errors. No action taken'));
			return;
		}

		//---------------------
		// GOOD TO GO

		// pass to actions to process
		$options = ['action_context' => $this->context];
		$pwcommerceActions = $this->pwcommerce->getPWCommerceClassByName('PWCommerceActions', $options);
		$result = $pwcommerceActions->addNewItemAction($input);

		//-----------------
		// notice(s)
		$this->renderNotices($result);
		// TODO: HERE, NEED TO ACCOUNT FOR CONTEXTS THAT CREATE MULTIPLE NEW ITEMS, HENCE CANNOT REDIRECT TO THEIR EDITS! E.G. CREATE COUNTRIES/TAX RATES! SO, CREATE AN IS CHECKER HERE!
		// ------------
		// ### REDIRECTS ###

		// 1. ERROR NOTICE RETURNED
		if ($result['notice_type'] === 'error') {
			// failed to create page
			// TODO - REDIRECT BACK TO ADD? TODO - JUST RETURN TO SHOW ERRORS?
			return;
		} else if (!$this->isContextRedirectToEditAfterAddNewItem()) {
			// 2. context DOES NOT REDIRECT TO EDIT after add new item; GO TO BULK EDIT
			// redirect to execute page (also ensures previous post values are discarded)
			$this->session->redirect($this->adminURL . $this->context . "/");
		} else if (!empty($result['new_item_id'])) {
			// 3. context REDIRECTS TO EDIT on successful page created
			// @note: this means page was created successfully - TODO: since we passed NO 'error' 'new_item_id' should not be empty anyway!
			$newItemID = $result['new_item_id'];
			// @note: e.g. /processwire/pwcommerce/categories/edit/?id=1234
			$this->session->redirect($this->adminURL . $this->context . "/edit/?id={$newItemID}");
		} else {
			// @note/TODO: JUST A CATCH ALL but we'll probably not get here
			// other unknown
			return;
		}
	}

	private function processBulkEditAction($form) {

		$input = $this->wire('input')->post;
		// process form
		$form->processInput($input);
		$errors = $form->getErrors();

		// TODO: BETTER ERROR MESSAGE HERE?
		if (count($errors)) {
			//
			$this->error($this->_('There were errors. No action taken'));
			return;
		}

		// GOOD TO GO

		// pass to actions to process
		$options = ['action_context' => $this->context];
		$pwcommerceActions = $this->pwcommerce->getPWCommerceClassByName('PWCommerceActions', $options);
		$result = $pwcommerceActions->bulkEditAction($input);

		//-----------------
		// notice(s)
		$this->renderNotices($result);
		// ------------
		// redirect to same page (just to ensure previous post values are discarded)
		$this->session->redirect($this->adminURL . $this->context . "/");
	}

	// e.g. General Settings, Tax Settings, etc
	private function processSingleEdit($form) {

		$input = $this->wire('input')->post;
		// process form
		$form->processInput($input);
		$errors = $form->getErrors();
		// TODO @note -> errors not getting caught for now in our non-tabs forms! e.g. tax settings or payment provider, so, will need to check missing required values ourselves. for tab-based forms, e.g. general settings, getErrors() works fine.
		// TODO: BETTER ERROR MESSAGE HERE?
		if (count($errors)) {

			$this->error($this->_('There were errors. No action taken'));
			return;
		}

		// GOOD TO GO
		// pass to actions to process
		$options = ['action_context' => $this->context];
		$pwcommerceActions = $this->pwcommerce->getPWCommerceClassByName('PWCommerceActions', $options);
		$result = $pwcommerceActions->singleEditAction($input);

		//-----------------
		// notice(s)
		$this->renderNotices($result);
		// ------------

		// AFTER SAVE ACTION
		// TODO: for process views, currently, it is only save + exit
		if (!empty($this->wire('sanitizer')->fieldName($input->_action_value))) {
			// TODO @note: per above, currently only 'save_and_exit' so just go to home page since single process view edits such as general, tax settings, etc, don't have own parent page.
			$url = !empty($this->isContextSpecialEditItem()) ? $this->adminURL . $this->context . "/" : $this->adminURL;
			$this->session->redirect($url);
		} elseif (!empty($result['special_redirect'])) {
			// SPECIAL REDIRECT
			// TODO: ADD SPECIAL REDIRECT FOR ORDER VIEW AFTER ACTION MARK PENDING OR PAID SO AS TO ADD 'view' URLSEGEMENT AND PARAM STRING '?id=1234' SO THAT MARKED ORDER CAN BE VIEWED IN VIEW WITHOUT EDIT BUTTON!
			// TODO: NEED THE SAME FOR PAYMENT PROVIDER!
			$this->session->redirect($this->adminURL . $this->context . $result['special_redirect']);
		} else {
			// DEFAULT REDIRECT
			// redirect to same page (just to ensure previous post values are discarded)
			$this->session->redirect($this->adminURL . $this->context . "/");
		}
	}

	// e.g. Discounts
	private function processPreProcess($form) {

		$input = $this->wire('input')->post;

		// process form
		$form->processInput($input);
		$errors = $form->getErrors();

		// TODO @note -> errors not getting caught for now in our non-tabs forms! e.g. tax settings or payment provider, so, will need to check missing required values ourselves. for tab-based forms, e.g. general settings, getErrors() works fine.
		// TODO: BETTER ERROR MESSAGE HERE?
		if (count($errors)) {

			$this->error($this->_('There were errors. No action taken'));
			return;
		}

		// GOOD TO GO
		// pass to actions to process
		$options = ['action_context' => $this->context];
		$pwcommerceActions = $this->pwcommerce->getPWCommerceClassByName('PWCommerceActions', $options);
		$result = $pwcommerceActions->preProcessAction($input);
		//-----------------
		// notice(s)
		$this->renderNotices($result);
		// ------------

		// AFTER SAVE ACTION
		// TODO: for process views, currently, it is only save + exit
		if (!empty($this->wire('sanitizer')->fieldName($input->_action_value))) {
			// TODO @note: per above, currently only 'save_and_exit' so just go to home page since single process view edits such as general, tax settings, etc, don't have own parent page.
			$url = !empty($this->isContextSpecialEditItem()) ? $this->adminURL . $this->context . "/" : $this->adminURL;
			$this->session->redirect($url);
		} elseif (!empty($result['special_redirect'])) {
			// SPECIAL REDIRECT
			// e.g. to edit newly created discount (created during pre-process)
			// TODO: NEED THE SAME FOR PAYMENT PROVIDER!
			$this->session->redirect($this->adminURL . $this->context . $result['special_redirect']);
		} else {
			// DEFAULT REDIRECT
			// redirect to same page (just to ensure previous post values are discarded)
			$this->session->redirect($this->adminURL . $this->context . "/");
		}
	}

	/**
	 * Process a request for inline-edit.
	 *
	 * @note: mainly used by inline-edit of a table row in inventory context.
	 * @note: Inline-edit is called as a response to an ajax request.
	 *
	 * @return string
	 */
	private function processAjaxSingleInlineEdit() {
		$input = $this->wire('input')->post;

		// GOOD TO GO
		// pass to actions to process
		$options = ['action_context' => $this->context];
		$pwcommerceActions = $this->pwcommerce->getPWCommerceClassByName('PWCommerceActions', $options);
		$result = $pwcommerceActions->singleInlineEditAction($input);

		// TODO: IF ERROR, JUST REDIRECT TO MAIN CONTEXT PAGE?
		if ($result['notice_type'] === 'error') {
			// TODO: DOES NOT WORK PROPERLY! WOULD NEED TO STOP HTMX IN THAT CASE!
			//-----------------
			// @note: only error messages here!
			// notice(s)
			$this->renderNotices($result);
			// redirect to same page (just to ensure previous post values are discarded)
			$this->session->redirect($this->adminURL . $this->context . "/");
		} else {
			// @note/TODO - currently, no SUCCESS notices if in ajax context!
			// instead call the render method for single row edit
			$pageID = $result['inline_edited_item_id'];
			return $this->getSingleInlineEditedMarkup($pageID);
		}
	}

	/**
	 * Process a request for generating a sales report.
	 *
	 * @note: Called as a response to an ajax request.
	 *
	 * @return void
	 */
	private function processAjaxGenerateReport() {
		$input = $this->wire('input')->post;
		// GOOD TO GO
		// pass to actions to process
		$options = ['action_context' => $this->context];
		$pwcommerceActions = $this->pwcommerce->getPWCommerceClassByName('PWCommerceActions', $options);
		$result = $pwcommerceActions->generateSalesReportAction($input);

		// TODO: IF ERROR, JUST REDIRECT TO MAIN CONTEXT PAGE?
		if ($result['notice_type'] === 'error') {
			// TODO: DOES NOT WORK PROPERLY! WOULD NEED TO STOP HTMX IN THAT CASE!
			//-----------------
			// @note: only error messages here!
			// notice(s)
			// $this->renderNotices($result);
			// redirect to same page (just to ensure previous post values are discarded)
			// $this->session->redirect($this->adminURL . $this->context . "/");
			$errorMarkup = "<p class='pwcommerce_error'>{$result['notice']}</p>";
			return $errorMarkup;
		} else {
			// @note/TODO - currently, no SUCCESS notices if in ajax context!
			// instead call the render method for reports
			$reportItems = $result['report_items'];
			return $this->renderGenerateReport($reportItems);
		}
	}
	/**
	 * Process a request to generate and manually issue a gift card.
	 *
	 * @note: Called as a response to an ajax request.
	 *
	 * @return void
	 */
	private function processAjaxManuallyIssueGiftCard() {
		// TODO @NOTE: NOT IN USE FOR NOW; DUE TO DATEPICKER ISSUE, NOT SHOWING IN MODAL, WE NEED TO USE A SINGLE PAGE INSTEAD.
		// TODO - REUSE THE CODE BELOW FOR USUAL POST (NON-AJAX)
		$input = $this->wire('input')->post;

		// GOOD TO GO
		// pass to actions to process

		// TODO PASS TO GIFT CARDS CLASS? OR ACTIONS WHICH THEN CALLS GIFT CARDS CLASS?

		$options = [
			'action_context' => $this->context,
			'action' => 'manually_issue_gift_card' // TODO OK?
		];
		$pwcommerceActions = $this->pwcommerce->getPWCommerceClassByName('PWCommerceActions', $options);
		$result = $pwcommerceActions->manuallyIssueGiftCard($input);

		// TODO: IF ERROR, JUST REDIRECT TO MAIN CONTEXT PAGE?
		if ($result['notice_type'] === 'error') {
			// TODO: DOES NOT WORK PROPERLY! WOULD NEED TO STOP HTMX IN THAT CASE!
			//-----------------
			// @note: only error messages here!
			// notice(s)
			// $this->renderNotices($result);
			// redirect to same page (just to ensure previous post values are discarded)
			// $this->session->redirect($this->adminURL . $this->context . "/");
			$errorMarkup = "<p class='pwcommerce_error'>{$result['notice']}</p>";
			return $errorMarkup;
		} else {
			// SUCCESS
			// @note: markup will be rendered by htmx
			$response = $result['notice'];
			return $response;
		}
	}

	/**
	 * Process a request to create variants for a given product.
	 *
	 * The requests comes after user has previewed variants that would be generated based off their attribute and attribute options choices.
	 * It is a response to an ajax request.
	 *
	 * @return void
	 */
	private function processGenerateVariants() {
		$input = $this->wire('input')->post;
		$pwcommerceActions = $this->pwcommerce->getPWCommerceClassByName('PWCommerceActions');
		$result = $pwcommerceActions->addVariants($input);

		// @notes on below
		// @see: https: //htmx.org/attributes/hx-swap-oob/
		// The hx-swap-oob attribute allows you to specify that some content in a response should be swapped into the DOM somewhere other than the target, that is "Out of Band". This allows you to piggy back updates to other element updates on a response.

		$out = "";

		if ($result['notice_type'] === 'error') {
			// ERROR
			// no variants created
			// TODO: nothing to do here. In the htmx response handler, If our #pwcommerce_product_created_variants_ids does not exist, it means ERROR/TOTAL FAILURE to create variants? Alternatively, we check in the dataset of the 'p' below
		} else {
			// SUCCESS  at least one variant created)
			//-----------------
			// new variants IDs
			// append IDs of new variants in order to refresh the InputfieldPWCommerceRuntimeMarkup list of variants via a subsequent htmx GET request.
			// @note: the name 'pwcommerce_created_items_ids' is generic so can be used by any context that needs it!
			$newVariantsIDs = implode(',', $result['created_variants_pages_ids']);
			$out .=
				"<input type='hidden' id='pwcommerce_product_created_variants_ids' name='pwcommerce_created_items_ids' value='{$newVariantsIDs}' class='pwcommerce_product_created_variants'>";
			// ------------------------
			// new variants options IDs
			// @note: swapping this out of bands as well so we don't get duplicates during subsequent create variants!
			// @note: used to updated 'existing_variants' for use by alpine.js
			$newVariantsOptionsIDs = implode('|', $result['created_variants_options_ids']);
			// for each created variant, append IDs of options IDs that were used to create it
			// @note: will be swapped 'out-of-band' by htmx!
			$out .=
				"<input type='hidden' id='pwcommerce_product_created_variants_options_ids' name='pwcommerce_product_created_variants_options_ids' value='{$newVariantsOptionsIDs}' hx-swap-oob='true'>";
			// ------------------------
			// new variants created timestamp
			// @note: to mitigate the InputfieldTextTags JavaScript error when we trigger subsequent reloads in PWCommerceCommonScripts. This ensures new bunch of variants can always be uniquely identified.
			// @note: swapping this out of bands as well so we don't get duplicates during subsequent create variants!
			$out .=
				"<input type='hidden' id='pwcommerce_created_items_timestamp' name='pwcommerce_created_items_timestamp' value='{$result['created_variants_timestamp']}' class='pwcommerce_product_created_variants'  hx-swap-oob='true'>";
		}
		// THE NOTICE
		// success OR error notice @note: will be swapped 'out-of-band' by htmx!
		$notice = $result['notice'];
		$noticeType = $result['notice_type'];
		$out .= "<p id='pwcommerce_product_variants_creation_outcome' data-notice-type='{$noticeType}' hx-swap-oob='true'>{$notice}</p>";

		return $out;
	}


	private function processAjaxFindAnything() {
		// TODO DEPRECATED SINCE PWCOMMERCE 009; @SEE HOOK 'hookProcessPageSearchLive'
		// TODO DELETE IN NEXT RELEASE
		$input = $this->wire('input')->get;

		$q = $input->get("pwcommerce_find_anything_search_box", "text,selectorValue");

		$results = "";
		// require at least 2 characters
		if (strlen($q) < 2) {
			return $results;
		}

		// ---------

		// TODO: INCLUDE ALL OR LET PROCESSWIRE DETERMINE? OR POST-DETERMINE?

		$templatesNamesSelector = $this->pwcommerceUtilities->getFindAnythingTemplatesSelector();
		// TODO: confirm these sorts!
		// TODO: LIMIT TO 100? less or more? ok? INCLUDE ALL OK?
		// FORCE TEMPLATES TO MATCH PWCOMMERCE FIND ANYTHING TEMPLATES ONLY + INCLUDE ALL + EXLUDE TRASH
		// TODO REVISIT THIS SORT AS WE WANT PRODUCTS TO BE MATCHED BEFORE ORDER LINE ITEMS! ALTERNATIVELY WE CAN FILTER AT ARRAY LEVEL AND MOVE ORDERS TO BE LAST? OR LIMIT ORDER RESULTS?
		// TODO RELATED TO ABOVE, WE ARE GETTING LOTS OF ORDER HITS, HOW TO LIMIT THAT?
		$selector = "template={$templatesNamesSelector},title%={$q}, include=all,sort=template,sort=title,limit=100,status<" . Page::statusTrash;
		//$fields = ['title', 'templates_id', 'status'];
		$fields = ['id', 'title', 'templates_id', 'status', 'parent_id'];
		//$fields = ["templates_id" => "tid", "title" => "label"];

		$results = $this->wire('pages')->findRaw($selector, $fields);

		//--------------
		return $this->renderFindAnythingMarkup($results);
	}

	/**
	 * Process a request to configure pwcommerce install.
	 *
	 * @note: used for both first time and modify pwcommerce install.
	 *
	 * @return void
	 */
	private function processConfigureInstall($form) {
		$input = $this->wire('input')->post;

		// process form
		$form->processInput($input);
		$errors = $form->getErrors();
		// TODO: BETTER ERROR MESSAGE HERE?
		if (count($errors)) {

			$this->error($this->_('There were errors. No action taken'));
			return;
		}

		// GOOD TO GO
		// pass to INSTALLER to process
		$shopAdminPWCommerceRootPage = $this->page->child("name=" . PwCommerce::CHILD_PAGE_NAME . ",include=all");
		$shopAdminPWCommerceRootPageID = $shopAdminPWCommerceRootPage->id;
		$options = [
			'shop_process_pwcommerce_page_id' => $this->page->id,
			'shop_admin_pwcommerce_root_page_id' => $shopAdminPWCommerceRootPageID,
			'config_module_name' => PwCommerce::PWCOMMERCE_PROCESS_MODULE
		];
		$pwcommerceInstaller = $this->pwcommerce->getPWCommerceClassByName('PWCommerceInstaller', $options);
		$status = $this->getConfigurePWCommerceStatus();
		$result = $pwcommerceInstaller->configurePWCommerceInstallAction($input, $status);

		//-----------------
		// notice(s)
		$this->renderNotices($result);

		//  REDIRECT
		// redirect to same page (just to ensure previous post values are discarded)
		$this->session->redirect($this->adminURL . $this->context . "/");
	}

	// TODO @NOTE: FOR NOW NOT USING AJAX TO CONFIGURE INSTALL
	/**
	 * Process a request to configure pwcommerce install.
	 *
	 * @note: used for both first time and modify pwcommerce install.
	 * @note: called as a response to an ajax request.
	 *
	 * @return void
	 */
	private function processAjaxConfigureInstall() {
		$input = $this->wire('input')->post;

		$out = "<p>GOT YOUR REQUEST TO CONFIGURE PWCOMMERCE INSTALL!</p>";

		// GOOD TO GO
		// pass to actions to process

		$shopAdminPWCommerceRootPage = $this->page->child("name=" . PwCommerce::CHILD_PAGE_NAME . ",include=all");
		$shopAdminPWCommerceRootPageID = $shopAdminPWCommerceRootPage->id;
		$options = [
			'shop_process_pwcommerce_page_id' => $this->page->id,
			'shop_admin_pwcommerce_root_page_id' => $shopAdminPWCommerceRootPageID,
			'config_module_name' => PwCommerce::PWCOMMERCE_PROCESS_MODULE
		];
		$pwcommerceInstaller = $this->pwcommerce->getPWCommerceClassByName('PWCommerceInstaller', $options);
		$status = $this->getConfigurePWCommerceStatus();
		$result = $pwcommerceInstaller->configurePWCommerceInstallAction($input, $status);

		// // TODO: IF ERROR, JUST REDIRECT TO MAIN CONTEXT PAGE?
		// if ($result['notice_type'] === 'error') {
		// 	// TODO: DOES NOT WORK PROPERLY! WOULD NEED TO STOP HTMX IN THAT CASE!
		// 	//-----------------
		// 	// @note: only error messages here!
		// 	// notice(s)
		// 	$this->renderNotices($result);
		// 	// redirect to same page (just to ensure previous post values are discarded)
		// 	$this->session->redirect($this->adminURL . $this->context . "/");
		// } else {
		// 	// @note/TODO - currently, no SUCCESS notices if in ajax context!
		// 	// instead call the render method for single row edit
		// 	$pageID = $result['inline_edited_item_id'];
		// 	return $this->getSingleInlineEditedMarkup($pageID);
		// }

		//  TODO DELETE WHEN DONE
		return $out;
	}

	// ~~~~~~~~~~~

	/**
	 * Process a request to configure pwcommerce install.
	 *
	 * @note: used for both first time and modify pwcommerce install.
	 *
	 * @return void
	 */
	private function processCompleteRemoval($form) {
		$input = $this->wire('input')->post;

		// process form
		$form->processInput($input);
		$errors = $form->getErrors();
		// TODO: BETTER ERROR MESSAGE HERE?
		if (count($errors)) {

			$this->error($this->_('There were errors. No action taken.'));
			return;
		}

		// GOOD TO GO
		// pass to INSTALLER to process
		$shopAdminPWCommerceRootPage = $this->page->child("name=" . PwCommerce::CHILD_PAGE_NAME . ",include=all");
		$shopAdminPWCommerceRootPageID = $shopAdminPWCommerceRootPage->id;
		$options = [
			'shop_process_pwcommerce_page_id' => $this->page->id,
			'shop_admin_pwcommerce_root_page_id' => $shopAdminPWCommerceRootPageID,
			'config_module_name' => PwCommerce::PWCOMMERCE_PROCESS_MODULE
		];
		$pwcommerceInstaller = $this->pwcommerce->getPWCommerceClassByName('PWCommerceInstaller', $options);
		$result = $pwcommerceInstaller->completeRemovalOfPWCommerceAction();

		//-----------------
		// notice(s)
		$this->renderNotices($result);

		//  REDIRECT
		// TODO redirect to admin or to modules page? modules! so they can uninstall ProcessP
		// TODO - TEMP FOR TESTING - DELETE WHEN DONE!
		// $this->session->redirect($this->adminURL . $this->context . "/");
		// $this->session->redirect($this->wire('config')->urls->admin);
		// @note: redirect to ProcessPWCommerce module edit page so the superuser can uninstall ProcessPWCommerce. It will also uninstall PWCommerceHooks and PWCommerce modules
		// $this->session->redirect($this->wire('config')->urls->admin . "module/edit?name={$this}");
		$this->session->redirect($this->wire('config')->urls->admin . "module/edit?name=" . PwCommerce::PWCOMMERCE_PROCESS_MODULE);
	}


}