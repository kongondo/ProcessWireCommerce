<?php

namespace ProcessWire;

trait TraitPWCommerceActionsNewItem
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ NEW ITEM ~~~~~~~~~~~~~~~~~~

	/**
	 * Create a new child page for a given context.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	public function addNewItemAction($input) {

		$result = [
			'notice' => $this->_('Error encountered. Could not create new item.'),
			'notice_type' => 'error',
		];

		$sanitizer = $this->wire('sanitizer');
		// if no action context, return
		if (!$this->actionContext) {
			return $result;
		}
		// ##################

		// @note: just for convenience
		$this->actionInput = $input;

		# SPECIAL ACTIONS
		if ($this->actionContext === 'tax-rates') {
			// if adding new countries (for tax rates) - special action
			return $this->addNewCountriesAction($input);
		} elseif ($this->actionContext === 'gift-cards') {
			// if manually issue gift cards - special action
			return $this->addNewManualIssueGiftCardAction($input);
		} else if ($this->actionContext === 'customers') {
			// if adding new customer - special action
			return $this->addNewCustomerAction($input);
		}

		// basic add new (single) item/page
		// ##################

		$template = $this->getContextAddNewItemTemplate(); // Template|Null
		$parent = $this->getContextAddNewItemParent(); // Page|Null

		// error: template not found
		if (!$template) {
			$result['notice'] = $this->_('Required template not found!');
			return $result;
		}

		// error: parent not found
		if (!$parent) {
			$result['notice'] = $this->_('Parent page not found!');
			return $result;
		}

		//---------------
		// GOOD TO PROCEED TO NEXT STEP
		$languages = $this->wire('languages');
		$sanitizer = $this->wire('sanitizer');

		$missingTitleNotice = $this->_('A title is required!');

		// item TITTLE
		if ($this->actionContext === 'orders') {
			// IF ORDER, TITLE CAN BE CUSTOM OR AUTOMATICALLY GENERATED
			$title = $this->getNewOrderTitle($input->pwcommerce_add_new_item_title);
		} else if ($this->actionContext === 'discounts') {
			// IF DISCOUNT, TITLE IS AUTOMATICALLY GENERATED
			$title = $this->getNewDiscountTitle();
		} else {
			// 'usual' title
			$title = $sanitizer->text($input->pwcommerce_add_new_item_title);
		}
		// error: title not found
		if (!$title) {
			// $result['notice'] = $this->_('A title is required!');

			$result['notice'] = $missingTitleNotice;
			return $result;
		}

		// first check if page already exists (under this parent)
		// BUT ALSO CHECK IF DUPLICATE TITLES ALLOWED!
		// $name = $sanitizer->pageName($title);
		// @SEE NOTES ABOUT $beautify!
		$name = $sanitizer->pageName($title, true);
		$pageIDExists = $this->wire('pages')->getRaw("parent_id={$parent->id},name=$name", 'id');
		$isAllowDuplicateTitle = !empty((int) $input->pwcommerce_add_new_item_title_allow_duplicate);
		// error: child page under this parent already exists
		if (!empty($pageIDExists) && empty($isAllowDuplicateTitle)) {
			// CHILD PAGE ALREADY EXISTS!
			$notice = sprintf(__("A page with the title %s already exists!"), $title);
			$result['notice'] = $notice;
			return $result;
		}

		//---------
		// GOOD TO GO!
		$page = new Page();
		$page->template = $template;
		$page->parent = $parent;
		$page->title = $title;
		$page->name = $name;

		// unpublish page on save (i.e., NO Save + Publish button)
		if (empty((int) $input->pwcommerce_save_and_publish_new_button)) {
			$page->addStatus(Page::statusUnpublished);
		}

		// add other languages
		if ($languages) {
			foreach ($languages as $language) {
				// skip default language as already set above
				if ($language->name == 'default') {
					continue;
				}
				// set language title
				$inputLanguageTitle = "pwcommerce_add_new_item_title__{$language->id}";
				$languageTitle = $sanitizer->text($input->$inputLanguageTitle);
				if (!empty($languageTitle)) {
					$page->title->setLanguageValue($language, $languageTitle);
				}
				// set page name for this language
				// @SEE NOTES ABOUT $beautify!
				$name = $sanitizer->pageName($languageTitle, true);
				$page->setName($name, $language);
				// +++++++++
				// set page as active in this language
				$page->set("status$language", 1);
			}
		}

		// run extra operations on add new if needed
		if ($this->isContextRunExtraAddNewItemOperations()) {
			$page = $this->runContextExtraAddNewItemOperations($page);
		}

		//------------------
		// SAVE the new page
		$page->save();

		// error: could not save page for some reason
		if (!$page->id) {
			$result['notice'] = $this->_('An error prevented the page from being created!');
			return $result;
		}

		// --------------------
		// prepare messages

		// TODO: rephrase: item or page?
		$notice = sprintf(__("Created page %s."), $page->title);
		$result = [
			'notice' => $notice,
			'notice_type' => 'success',
			'new_item_id' => $page->id, // @note: needed for redirection to edit it
		];

		//-------
		return $result;
	}

	/**
	 * For add new page for a given context, get the template.
	 *
	 * @return mixed
	 */
	private function getContextAddNewItemTemplate() {
		$template = null;
		$addNewContextsTemplates = [
			'attributes' => 'pwcommerce-attribute',
			'brands' => 'pwcommerce-brand',
			// ++++++++
			'categories' => 'pwcommerce-category',
			'collections' => 'pwcommerce-category',
			// ++++++++
			'tax-rates' => 'pwcommerce-country',
			// TODO WORK ON HOW TO ADD THIS! GUI, ETC!
			'dimensions' => 'pwcommerce-dimension',
			'downloads' => 'pwcommerce-download',
			'legal-pages' => 'pwcommerce-legal-page',
			'orders' => 'pwcommerce-order',
			//'' => 'pwcommerce-payment-provider',
			'products' => 'pwcommerce-product',
			// TODO WORK ON HOW TO ADD THIS! GUI, ETC!
			'properties' => 'pwcommerce-property',
			'shipping' => 'pwcommerce-shipping-zone',
			'tags' => 'pwcommerce-tag',
			'types' => 'pwcommerce-type',
			# =========
			'gift-card-products' => 'pwcommerce-gift-card-product',
			'gift-cards' => 'pwcommerce-gift-card',
			# TODO -@NOTE; CAN ONLY ADD PROGRAMMATICALLY
			# =========
			'discounts' => 'pwcommerce-discount',
			# @NOTE; CAN ONLY ADD PROGRAMMATICALLY via pre-processing
			# =========
			'customers' => 'pwcommerce-customer',
			'customer-groups' => 'pwcommerce-customer-group',
		];

		$template = !empty($addNewContextsTemplates[$this->actionContext]) ? $addNewContextsTemplates[$this->actionContext] : null;

		return $template;
	}

	/**
	 * For add new page for a given context, get the parent page.
	 *
	 * @return mixed
	 */
	private function getContextAddNewItemParent() {

		$parent = null;
		$addNewContextsParentsTemplates = [
			'attributes' => 'pwcommerce-attributes',
			'brands' => 'pwcommerce-brands',
			// ++++++++
			'categories' => 'pwcommerce-categories',
			'collections' => 'pwcommerce-categories',
			// ++++++++
			'tax-rates' => 'pwcommerce-countries',
			// TODO WORK ON HOW TO ADD THIS! GUI, ETC!
			'dimensions' => 'pwcommerce-dimensions',
			'downloads' => 'pwcommerce-downloads',
			'legal-pages' => 'pwcommerce-legal-pages',
			'orders' => 'pwcommerce-orders',
			//'' => 'pwcommerce-payment-providers',
			'products' => 'pwcommerce-products',
			// TODO WORK ON HOW TO ADD THIS! GUI, ETC!
			'properties' => 'pwcommerce-properties',
			'shipping' => 'pwcommerce-shipping-zones',
			'tags' => 'pwcommerce-tags',
			'types' => 'pwcommerce-types',
			# =========
			'gift-card-products' => 'pwcommerce-gift-card-products',
			'gift-cards' => 'pwcommerce-gift-cards',
			# TODO -@NOTE; CAN ONLY ADD PROGRAMMATICALLY
			# =========
			'discounts' => 'pwcommerce-discounts',
			# @NOTE; CAN ONLY ADD PROGRAMMATICALLY via pre-procesing
			# =========
			'customers' => 'pwcommerce-customers',
			'customer-groups' => 'pwcommerce-customer-groups',
		];

		// get parent page
		if (!empty($addNewContextsParentsTemplates[$this->actionContext])) {
			$templateName = $addNewContextsParentsTemplates[$this->actionContext];
			$parent = $this->wire('pages')->get("template={$templateName}");
		}

		//-------------
		return $parent;
	}

}
