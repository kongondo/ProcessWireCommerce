<?php

namespace ProcessWire;

/**
 * PWCommerce: Inputfield Attibute Options.
 *
 * Class to help manage creation and deletion of Product Attribute Options for PWCommerce.
 * It acts as a virtual Inputfield (VirtualInputfieldPWCommerceAttributeOptions)
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceAttributeOptions for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

class InputfieldPWCommerceAttributeOptions extends WireData
{

	/**
	 * @var Page
	 *
	 */
	private $page; // TODO; DELETE IF NOT IN USE


	/**
	 * Construct
	 *
	 * @param Page $page The current Attribute page this virtual field was called from.
	 *
	 */
	//  public function __construct(Page $page) { // TODO; DELETE IF NOT IN USE
	public function __construct() {


		parent::__construct();
	}

	// extra content to be  to InputfieldPWCommerceRuntimeMarkup with respect to this field
	// @note: TODO: we MIGHT still handle any JS interactions here!
	public function getAppendContent($page, $name) {
		// @note: $name and $page here are provided by the requesting method, e.g. runtime markup module
		return $this->renderFooter($page, $name);
	}

	private function renderFooter($page, $name) {
		// @note: $name and $page here are provided by the requesting method, e.g. runtime markup module
		$pageID = $page->id;

		//------------------- add new attribute option (InputfieldMarkup)
		// @note: SINGLE ADD NEW IN FOOTER OF WRAPPER - CAN ONLY HAVE ONE!
		// TODO: WILL PROBABLY MOVE TO RUNTIME MARKUP SO WE HAVE ONLY ONE ON THE PAGE!

		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		$options = [
			'id' => "pwcommerce_attribute_option_parent_page_id{$pageID}",
			'name' => 'pwcommerce_attribute_option_parent_page_id',
			'value' => $pageID, // store the parent ID of the new/incoming attribute option [new Page()]
		];

		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'classes' => 'pwcommerce_attribute_option_add_new',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $this->renderAddNewLink($page, $name),
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		$wrapper->add($field);
		// @note: return unrendered wrapper
		return $wrapper;
	}

	protected function renderAddNewLink($page, $name) {
		// @note: $name and $page here are provided by the requesting method, e.g. runtime markup module
		$pageID = $page->id;
		$adminEditURL = $this->wire('config')->urls->admin . "page/edit/";
		$adminEdit = "{$adminEditURL}?id={$pageID}&field={$name}&context=new_item";

		/*
												The a.classes explainer
												- pwcommerce_reload_inputfield_runtimemarkup_list: signals to InputfieldPWCommerceRuntimeMarkup that inputfields will need to be reloaded since new item inserted and the JS will need to catch on, e.g. RTE, etc.
												- pwcommerce_run_after_settle_operations: tells InputfieldPWCommerceRuntimeMarkup that htmx-after-settle operations will need to be run.
												- pwcommerce_open_newly_created_inputfieldset: tells InputfieldPWCommerceRuntimeMarkup the specific after-settle action to take.
												- pwcommerce_focus_input_in_newly_created_inputfield: tells InputfieldPWCommerceRuntimeMarkup to focus the InputfieldPageTitle input after new attribute option is added
												*/

		$out =
			"<div id='pwcommerce_attribute_option_add_new_wrapper' class='pwcommerce_add_new_wrapper'>" .
			"<a id='pwcommerce_attribute_option_add_new' class='pwcommerce_reload_inputfield_runtimemarkup_list pwcommerce_run_after_settle_operations pwcommerce_open_newly_created_inputfieldset pwcommerce_focus_input_in_newly_created_inputfield pwcommerce_run_request_indicators_operations' href='#' hx-get='{$adminEdit}' hx-target='#wrap_Inputfield_pwcommerce_runtime_markup > div.InputfieldContent > ul.Inputfields:first-child' hx-swap='beforeend' hx-indicator='#pwcommerce_add_new_attribute_option_spinner_indicator{$pageID}'>" .
			"<i id='pwcommerce_add_new_attribute_option_spinner_indicator{$pageID}' class='pwcommerce_add_new_attribute_option_spinner_indicator pwcommerce_add_new_item_spinner_indicator pwcommerce_spinner_indicator fa fa-fw fa-plus-circle'></i>" .
			// $this->_("Add new attribute option") .// TODO: DELETE IF NOT IN USE
			sprintf(__("Add new %s option"), $page->title) .
			"</a>" .
			"</div>";
		return $out;
	}

	/**
	 * For InputfieldPWCommerceRuntimeMarkup.
	 *
	 * For when new Attribute Option is requested by an attribute in edit.
	 * Return a new blank page of this type that is ready for editing and saving.
	 *
	 * @return Page $newPage The new blank item.
	 */
	public function getBlankItem() {
		$newPage = new Page();
		$template = $this->wire('templates')->get('pwcommerce-attribute-option');
		$newPage->template = $template;
		// @note: blank for now; we'll set a placeholder using JavaScript
		$newPage->title = $this->_('New Unsaved Attribute Option');
		// @note: add a temporary ID to track new pages for this context
		$newPage->id = str_replace('.', '-', microtime(true));
		return $newPage;
	}

	// ~~~~~~~~~~~~~~~~~~

	/**
	 * Process input for the values sent to create new options for this attribute page.
	 * @note: We only handle new or to be deleted options pages here!
	 *
	 */
	public function ___processInput(WireInputData $input) {
		// @note: CREATE NEW ATTRIBUTE OPTIONS is now called DIRECTLY, ONCE from inside InputfieldPWCommerceRuntimeMarkup::processInput for real pwcommerce Inputfields
	}

	public function processInputCreateNewItems(WireInputData $input) {
		$newItems = $input->pwcommerce_is_new_item;
		if (!empty($newItems)) {
			$newAttributeOptionParentID = (int) $input->pwcommerce_attribute_option_parent_page_id;
			$parent = $this->wire('pages')->get("id={$newAttributeOptionParentID}");
			if ($parent) {
				$languages = $this->wire('languages');
				$sanitizer = $this->wire('sanitizer');
				$template = $this->wire('templates')->get('pwcommerce-attribute-option');
				foreach ($newItems as $temporaryIDAsSuffix) {

					// prepare titles and name + will help check if identical exists
					// @note: our title and description are suffixed with 'repeaterxxxx'
					$title = $sanitizer->text($input->{"title_repeater{$temporaryIDAsSuffix}"});
					$name = $sanitizer->pageName($title, true);

					// first check if page already exists (under this parent)
					$pageIDExists = $this->wire('pages')->getRaw("parent_id={$parent->id},name=$name", 'id');
					// TODO: TEST THIS!
					if (!empty($pageIDExists)) {
						// CHILD PAGE (ATTRIBUTE OPTION) ALREADY EXISTS!
						continue;
					}

					// ** GOOD TO GO **

					// CREATE NEW ATTRIBUTE OPTION FOR THIS ATTRIBUTE
					//------------
					$p = new Page();
					$p->template = $template;
					$p->parent = $parent;
					$p->title = $title;
					$p->name = $name;
					// TODO/@NOTE: NOT IN USE FOR NOW!
					// description
					//  $p->pwcommerce_description = $sanitizer->purify($input->{"pwcommerce_description_repeater{$temporaryIDAsSuffix}"});

					// add other languages
					if ($languages) {
						foreach ($languages as $language) {
							// skip default language as already set above
							if ($language->name == 'default') {
								continue;
							}
							// language title
							$languageTitle = $sanitizer->text($input->{"title_repeater{$temporaryIDAsSuffix}__{$language->id}"});
							$p->title->setLanguageValue($language, $languageTitle);
							// TODO: set name too?
							// language description
							// TODO/@NOTE: NOT IN USE FOR NOW!
							//    $languageDescription = $sanitizer->purify($input->{"pwcommerce_description_repeater{$temporaryIDAsSuffix}__{$language->id}"});
							//    $p->pwcommerce_description->setLanguageValue($language, $languageDescription);
							// ++++++++++++
							// set option page as active in other languages
							$p->set("status$language", 1);
						}
					}

					//------------------
					// SAVE the new attribute option page
					$p->save();
				}
				// end loop
			}
		}
	}

	public function processInputDeleteItems(WireInputData $input) {
		$deleteItems = $input->pwcommerce_is_delete_item;
		// page IDs are in one hidden inputfield; get the first array item
		$deleteAttributeOptionsIDsCSV = $deleteItems[0]; // csv string of IDs
		$deleteAttributeOptionsIDsArray = $this->wire('sanitizer')->intArray($deleteAttributeOptionsIDsCSV);
		if (!empty($deleteAttributeOptionsIDsArray)) {
			$deleteAttributeOptionsIDs = implode('|', $deleteAttributeOptionsIDsArray);
			//-------------
			$pages = $this->wire('pages');
			$attributeOptions = $pages->find("id={$deleteAttributeOptionsIDs}");

			// ---------
			// TRASH each attribute option page

			$notDeletedAttributeOptionsTitles = [];
			$deletedAttributeOptionsTitles = [];
			foreach ($attributeOptions as $attributeOption) {
				// locked for edits
				if ($attributeOption->isLocked()) {
					$notDeletedAttributeOptionsTitles[] = $attributeOption->title;
					continue;
				}
				$pages->trash($attributeOption);
				// successfully trashed
				if ($attributeOption->isTrash()) {
					$deletedAttributeOptionsTitles[] = $attributeOption->title;
				}
			}
			// ------
			// NOTICES
			// success
			if (!empty($deletedAttributeOptionsTitles)) {
				$this->message(sprintf(__("Deleted these attribute options: %s."), implode(', ', $deletedAttributeOptionsTitles)));
			}
			// error
			if (!empty($notDeletedAttributeOptionsTitles)) {
				$this->warning(sprintf(__("Could not delete these attribute options: %s."), implode(', ', $notDeletedAttributeOptionsTitles)));
			}
		}
	}
}