<?php

namespace ProcessWire;

/**
 * PWCommerce: Inputfield Gift Card Product Variants.
 *
 * Class to help manage creation and deletion of Gift Card Product Variants for PWCommerce.
 * It acts as a virtual Inputfield (VirtualInputfieldPWCommerceGiftCardProductVariants)
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceGiftCardProductVariants for PWCommerce
 * Copyright (C) 2023 by Francis Otieno
 * MIT License
 *
 */



class InputfieldPWCommerceGiftCardProductVariants extends WireData
{





	/**
	 * @var Page
	 *
	 */
	private $page; // TODO; DELETE IF NOT IN USE



	/**
	 * Construct
	 *
	 * @param Page $page The current Gift Card Product page this virtual field was called from.
	 *
	 */
	//  public function __construct(Page $page) { // TODO; DELETE IF NOT IN USE
	public function __construct() {


		parent::__construct();
	}

	// TODO - CHANGE THIS NO LONGER NEEDED AS WE ISSUE GIFT CARDS MANUALLY ELSEWHERE

	// extra content to PRE-PEND to InputfieldPWCommerceRuntimeMarkup with respect to this field
	// @note: TODO: we MIGHT still handle any JS interactions here!
	# TODO @NOTE WE USE THIS TO OPEN AND INTERACT WITH MODAL TO MANUALLY ISSUE GC IN THE BACKEN
	public function getPrependContent($page, $name) {
		$pageID = $page->id;





		// for reuse later in markup builders
		$this->page = $page;
		$this->name = $name; // 'pwcommerce_runtime_markup'

		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		# TODO EDIT AS NEEDED!

		//------------------- track gift card product variants parent (GCP) (InputfieldHidden)
		$options = [
			'id' => "pwcommerce_gift_card_product_parent_page_id{$pageID}",
			'name' => 'pwcommerce_gift_card_product_parent_page_id',
			// TODO: UNSURE IF STILL NEEDED??? ALPINE?
			'value' => $pageID, // store the parent ID of the new/incoming gift card product variant/item [new Page()]
		];

		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);


		$out = "";
		$classes = '';

		// CHECK IF WE HAVE AT LEAST ONE GCPV that is enabled + has a price


		// TODO THIS NOW CHANGES! MARKUP IS FOR ACCORDION AND IT ALL SHOULD COME FROM ABOVE METHOD IF POSSIBLE?
		$addGiftProductVariantsLink = "<div id='issue_gift_card_wrapper' x-data='InputfieldPWCommerceGiftCardProductVariantsData'>{$out}<hr class='mt-3 mb-1'></div>";

		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// 'classes' => 'pwcommerce_gift_card_product_variant_add_new',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $addGiftProductVariantsLink, // TODO: CHANGE TO SMALL BUTTON?
		];

		// add class if not empty
		if (!empty($classes)) {
			# @NOTE - TO REMOVE EXTRA PADDING IF 'issue GFC button' not shown
			$options['classes'] = 'pwcommerce_gift_card_product_variant_no_child';
		}

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		$wrapper->add($field);
		// @note: return unrendered wrapper
		return $wrapper;
	}

	// extra content to APPEND to InputfieldPWCommerceRuntimeMarkup with respect to this field
	// @note: TODO: we MIGHT still handle any JS interactions here!
	public function getAppendContent($page, $name) {
		// @note: $name and $page here are provided by the requesting method, e.g. runtime markup module


		return $this->renderFooter($page, $name);
	}

	private function renderFooter($page, $name) {
		// @note: $name and $page here are provided by the requesting method, e.g. runtime markup module
		$pageID = $page->id;


		//------------------- add new gift card product variant (InputfieldMarkup)
		// @note: SINGLE ADD NEW IN FOOTER OF WRAPPER - CAN ONLY HAVE ONE!
		// TODO: WILL PROBABLY MOVE TO RUNTIME MARKUP SO WE HAVE ONLY ONE ON THE PAGE!

		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		// track the GIFT CARD PRODUCT PARENT PAGE ID
		$options = [
			'id' => PwCommerce::PWCOMMERCE_GIFT_CARD_PRODUCT_VARIANT_PARENT_PAGE_ID_INPUT,
			'name' => PwCommerce::PWCOMMERCE_GIFT_CARD_PRODUCT_VARIANT_PARENT_PAGE_ID_INPUT,
			'value' => $pageID, // store the parent ID of the new/incoming gift card product variant [new Page()]
		];

		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		// track the GIFT CARD PRODUCT PARENT PAGE TITLE
		// if multilingual, we need the default title
		// @note: used to name/rename gift card product variants
		$title = $this->getGiftCardProductVariantsParentTitle($page);

		$options = [
			'id' => PwCommerce::PWCOMMERCE_GIFT_CARD_PRODUCT_VARIANT_PARENT_PAGE_TITLE_INPUT,
			'name' => PwCommerce::PWCOMMERCE_GIFT_CARD_PRODUCT_VARIANT_PARENT_PAGE_TITLE_INPUT,
			// store the parent TITLE of the new/incoming gift card product variant [new Page()]
			'value' => $title,
		];

		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'classes' => 'pwcommerce_gift_card_product_variant_add_new',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $this->renderAddNewLink($page, $name),
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		$wrapper->add($field);
		// @note: return unrendered wrapper
		return $wrapper;
	}

	private function getGiftCardProductVariantsParentTitle(Page $parentPage) {
		$languages = $this->wire('languages');
		if ($languages) {
			// multi-lingual site
			$title = $parentPage->title->getLanguageValue($languages->getDefault());


		} else {
			// single language site
			$title = $parentPage->title;
		}


		// -----
		return $title;
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
													- pwcommerce_focus_input_in_newly_created_inputfield: tells InputfieldPWCommerceRuntimeMarkup to focus the InputfieldPageTitle input after new gift card product variant is added
													*/
		# @NOTE: we use nth-child(2) here since in this context we have both append and prepend markup; Using first-child will cause new item to be inserted before the last gift card product variant; we want it to be inserted after.
		$out =
			"<div id='pwcommerce_gift_card_product_variant_add_new_wrapper' class='pwcommerce_add_new_wrapper'>" .
			"<a id='pwcommerce_gift_card_product_variant_add_new' class='pwcommerce_reload_inputfield_runtimemarkup_list pwcommerce_run_after_settle_operations pwcommerce_open_newly_created_inputfieldset pwcommerce_focus_input_in_newly_created_inputfield pwcommerce_run_request_indicators_operations' href='#' hx-get='{$adminEdit}' hx-target='#wrap_Inputfield_pwcommerce_runtime_markup > div.InputfieldContent > ul.Inputfields:nth-child(2)' hx-swap='beforeend' hx-indicator='#pwcommerce_add_new_gift_card_product_variant_spinner_indicator{$pageID}'>" .
			"<i id='pwcommerce_add_new_gift_card_product_variant_spinner_indicator{$pageID}' class='pwcommerce_add_new_gift_card_product_variant_spinner_indicator pwcommerce_add_new_item_spinner_indicator pwcommerce_spinner_indicator fa fa-fw fa-plus-circle'></i>" .
			// $this->_("Add new gift card product variant") .// TODO: DELETE IF NOT IN USE
			sprintf(__("Add new %s variant"), $page->title) .
			"</a>" .
			"</div>";
		return $out;
	}

	/**
	 * For InputfieldPWCommerceRuntimeMarkup.
	 *
	 * For when new Gift Card Product Variant is requested by an gift card product in edit.
	 * Return a new blank page of this type that is ready for editing and saving.
	 *
	 * @return Page $newPage The new blank item.
	 */
	public function getBlankItem() {
		$newPage = new Page();
		$template = $this->wire('templates')->get(PwCommerce::GIFT_CARD_PRODUCT_VARIANT_TEMPLATE_NAME);
		$newPage->template = $template;
		// @note: blank for now; we'll set a placeholder using JavaScript
		$newPage->title = $this->_('New Unsaved Gift Card Product Variant');
		// @note: add a temporary ID to track new pages for this context
		$newPage->id = str_replace('.', '-', microtime(true));
		$newPage->isNew = true;
		return $newPage;
	}

	/**
	 * For InputfieldPWCommerceRuntimeMarkup.
	 *
	 * For Gift Card Product Variants.
	 * We need to remove 'title' inputfields.
	 * We will handle this in processInputCreateNewItems() for new items.
	 * For existing items, we will handle if denomination changes TODO.
	 * For 'image' inputfields, we need to remove IF ITEM IS NEW.
	 * This is because we cannot upload images until the page is first saved.
	 * Instead, we add own markup about upload GCPV image after save.
	 *
	 * @param InputfieldWrapper $inputfields
	 * @param Page $page
	 * @return void
	 */
	public function getDynamicallyManagedInputfields(InputfieldWrapper $inputfields, Page $page) {






		foreach ($inputfields->children() as $inputfield) {

			$attrs = $inputfield->attr(true);

			$dataFieldName = $inputfield->attr('data-field-name');




			if (strpos($inputfield->name, 'pwcommerce_images') !== false && $page->isNew) {

				# remove pwcommerce image field
				$inputfields->remove($inputfield);
				# prepend markup about image field uploads after save
				$inputfields->append($this->getTemporaryMessageForVariantImage());
			} elseif (strpos($inputfield->id, 'Inputfield_title') !== false) {
				# always remove title field for GCP/V context

				$inputfields->remove($inputfield);
			}
		}

		# ---------
		return $inputfields;
	}

	private function getTemporaryMessageForVariantImage($width = 100) {
		$out =
			"<div class='pt-2.5'><p><i class='fa fa-camera' aria-hidden='true'></i> " .
			$this->_('Images can be added to this new Gift Card Product Variant once you save the page.') .
			"</p></div>";
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			// 'wrapClass' => true,
			'classes' => 'pwcommerce_gift_card_product_variant_add_new',
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
			'columnWidth' => $width,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);

		return $field;
	}


	// ~~~~~~~~~~~~~~~~~~

	private function isGiftCardVariantPageAlreadyExists($parent, $denomination) {
		// first check if page already exists (under this parent)


		$pageIDExists = $this->wire('pages')->getRaw("parent_id={$parent->id}," . PwCommerce::PRODUCT_STOCK_FIELD_NAME . ".price={$denomination}", 'id');
		// TODO: TEST THIS! NOT SURE IT WORKS?!
		return !empty($pageIDExists);
	}
	// ~~~~~~~~~~~~~~~~~~
	public function getPreloadAssetsContent() {
		// SET THIS VIRTUAL INPUTFIELD'S js for loading via runtime markup
		$url = $this->wire('config')->urls->siteModules;
		return [
			['source' => "{$url}PWCommerce/includes/inputfield/InputfieldPWCommerceGiftCardProductVariants/InputfieldPWCommerceGiftCardProductVariants.js"],
		];
	}
	// ~~~~~~~~~~~~~~~~~~

	// TODO: NOT IN USE FOR NOW
	// public function processAjaxRequest(WireInput $input, Page $page) {

	// }
	// ~~~~~~~~~~~~~~~~~~

	/**
	 * Process input for the values sent to create new variants for this gift card product page.
	 * @note: We only handle new or to be deleted variants pages here!
	 *
	 */
	public function ___processInput(WireInputData $input) {
		// @note: CREATE NEW GIFT CARD PRODUCT VARIANTS is now called DIRECTLY, ONCE from inside InputfieldPWCommerceRuntimeMarkup::processInput for real pwcommerce Inputfields
	}

	public function processInputCreateNewItems(WireInputData $input) {
		// TODO @UPDATE- THIS CHANGES - WE NEED TO GET FROM PRODUCT STOCK FIELDS
		# TODO THIS NEEDS TO HANDLE THE DENOMINATION
		$newItems = $input->get('pwcommerce_is_new_item');


		// TODO DELETE IF NOT IN USE
		$createdPagesIDs = [];

		if (!empty($newItems)) {
			$newGiftCardProductVariantParentID = (int) $input->get(PwCommerce::PWCOMMERCE_GIFT_CARD_PRODUCT_VARIANT_PARENT_PAGE_ID_INPUT);

			$parent = $this->wire('pages')->get("id={$newGiftCardProductVariantParentID}");


			if (!$parent instanceof NullPage) {
				$languages = $this->wire('languages');
				$sanitizer = $this->wire('sanitizer');
				$template = $this->wire('templates')->get(PwCommerce::GIFT_CARD_PRODUCT_VARIANT_TEMPLATE_NAME);

				foreach ($newItems as $temporaryIDAsSuffix) {

					// @NOTE: NOW USING A TEMPORARY FORM INPUT TO CAPTURE THIS; not the field itself
					$denomination = (float) $input->{"pwcommerce_product_stock_price{$temporaryIDAsSuffix}"};


					if (empty($denomination)) {
						// SKIP: NO DENOMINATION
						# TODO: OK? SINCE NEED TO ALSO TELL USER ABOUT ERROR! => MAYBE MESSAGES?

						continue;
					}

					# ======

					// prepare titles and name + will help check if identical exists
					// @note: our inputs are suffixed with 'repeaterxxxx'
					$title = $sanitizer->text($parent->title) . ": " . $denomination;
					$name = $sanitizer->pageName($title, true);





					// first check if page already exists (under this parent)
					if (!empty($this->isGiftCardVariantPageAlreadyExists($parent, $denomination))) {
						// CHILD PAGE (GIFT CARD PRODUCT VARIANT) ALREADY EXISTS!

						continue;
					}

					// ** GOOD TO GO **

					// CREATE NEW GIFT CARD PRODUCT VARIANT FOR THIS GIFT CARD PRODUCT
					//------------
					$p = new Page();
					$p->template = $template;
					$p->parent = $parent;
					$p->title = $title;
					$p->name = $name;
					# ----
					//-------------------------
					// stock
					//------
					$sku = $sanitizer->text($input->{"pwcommerce_product_stock_sku{$temporaryIDAsSuffix}"});
					# -----
					$price = (float) $input->{"pwcommerce_product_stock_price{$temporaryIDAsSuffix}"};
					$comparePrice = (float) $input->{"pwcommerce_product_stock_compare_price{$temporaryIDAsSuffix}"};
					# -----
					$quantity = (int) $input->{"pwcommerce_product_stock_quantity{$temporaryIDAsSuffix}"};
					# -----
					$allowBackordersValue = (int) $input->{"pwcommerce_product_stock_allow_backorders{$temporaryIDAsSuffix}"};
					$allowBackorders = empty($allowBackordersValue) ? 0 : 1;
					# ----
					$enabledValue = (int) $input->{"pwcommerce_product_stock_enabled{$temporaryIDAsSuffix}"};
					$enabled = empty($enabledValue) ? 0 : 1;
					$stock = [
						'sku' => $sku,
						'price' => $price,
						'comparePrice' => $comparePrice,
						'quantity' => $quantity,
						'allowBackorders' => $allowBackorders,
						'enabled' => $enabled,
					];
					// SET STOCK VALUES
					// $page->pwcommerce_product_stock->setArray($stock);
					$p->{PwCommerce::PRODUCT_STOCK_FIELD_NAME}->setArray($stock);


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
						}
					}




					//------------------
					// SAVE the new gift card product variant page
					$p->save();

					// TODO NEED MESSAGE FOR CREATED PAGES?! LIKE FOR DELETE?
					# --------
					if (!empty($p->id)) {
						$createdPagesIDs[] = $p->id;
					}
				}
				// end loop
			}
		}

		// ------
		// NOTICES

		if (empty($createdPagesIDs)) {
			// error
			$this->warning($this->_("Could not create any gift card product variants. Please ensure that you specifiy the price/denomination for each gift card product variant."));
		} else {
			// success
			$count = count($createdPagesIDs);
			$notice = sprintf(_n("Created %d gift card product variant.", "Created %d gift card product variants.", $count), $count);
			$this->message($notice);
		}



		// ========
		return $createdPagesIDs;
	}

	public function processInputAmendExistingItems(WireInputData $input, array $createdPagesIDs = []) {
		# TODO HERE WE CHECK IF DENOMINATION VALUE HAS CHANGED AND IF SO, WE ALSO CHANGE THE TITLE AND NAME OF THE GCPV PAGE!




		$existingGiftCardProductVariantsParentID = (int) $input->get(PwCommerce::PWCOMMERCE_GIFT_CARD_PRODUCT_VARIANT_PARENT_PAGE_ID_INPUT);
		$parent = $this->wire('pages')->get("id={$existingGiftCardProductVariantsParentID}");



		# --------
		if (!$parent instanceof NullPage) {


			// CHECK IF GIFT CARD PRODUCT VARIANTS PARENT PAGE HAS A CHANGED TITLE
			$isChangedParentTitle = $this->checkIsChangedParentTitle($input, $parent);

			$sanitizer = $this->wire('sanitizer');
			// @note: if title has changed, it will be in the input
			$parentTitle = $isChangedParentTitle ? $sanitizer->text($input->title) : $parent->title;


			// ***********
			// get current children to CHECK IF THEIR DENOMINATIONS HAVE BEEN CHANGED
			# TODO OKAY TO GET ALL CHILDREN HERE SINCE WE DON'T EXPECT MANY VARIANTS!
			# NOT EVEN 10 MAYBE!

			$existingGiftCardProductVariants = $parent->children('include=all');

			# +++++++++++
			// CHECK EACH CHILDS denomination value; if it will change, then change title & name of the GCPVariant page
			if ($existingGiftCardProductVariants->count()) {
				// $languages = $this->wire('languages');

				foreach ($existingGiftCardProductVariants as $page) {

					// =====
					// SKIP NEWLY CREATED PAGES
					if (in_array($page->id, $createdPagesIDs)) {

						continue;
					}

					// -----------
					$isChanged = false;
					$currentStock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
					$currentDenomination = (float) $currentStock->price;
					$incomingDenomination = (float) $input->{"pwcommerce_product_stock_price{$page->id}"};




					# TODO HOW TO HANDLE INCOMING ALREADY EXISTS TITLE!!!! HOW TO CONTINUE WITHOUTH AFFECTING THINGS ELSEWHERE! LET PW HANDLE IT?
					# -----
					// IF DENOMINATION HAS CHANGED
					if (($currentDenomination !== $incomingDenomination) || ($isChangedParentTitle)) {
						$isChanged = true;

						// prepare titles and name + will help check if identical exists
						// @note: our inputs are suffixed with 'repeaterxxxx'
						// $newTitle = $sanitizer->text($parent->title) . ": " . $incomingDenomination;
						$newTitle = $parentTitle . ": " . $incomingDenomination;
						;
						$newName = $sanitizer->pageName($newTitle, true);


						$page->title = $newTitle;
						$page->name = $newName;
					}

					//------------------
					// SAVE the AMENDED gift card product variant page if needed
					if ($isChanged) {

						$page->save();
					}
				}
			}
		}
	}

	private function checkIsChangedParentTitle(WireInputData $input, Page $parentPage) {
		$sanitizer = $this->wire('sanitizer');
		$incomingGiftCardProductVariantsParentTitle = $sanitizer->text($input->title);
		$existingGiftCardProductVariantsParentTitle = $sanitizer->text($this->getGiftCardProductVariantsParentTitle($parentPage));



		// -------
		return $incomingGiftCardProductVariantsParentTitle !== $existingGiftCardProductVariantsParentTitle;
	}

	public function processInputDeleteItems(WireInputData $input) {
		$deleteItems = $input->pwcommerce_is_delete_item;

		// page IDs are in one hidden inputfield; get the first array item
		$deleteGiftCardProductVariantsIDsCSV = $deleteItems[0]; // csv string of IDs

		$deleteGiftCardProductVariantsIDsArray = $this->wire('sanitizer')->intArray($deleteGiftCardProductVariantsIDsCSV);

		if (!empty($deleteGiftCardProductVariantsIDsArray)) {
			$deleteGiftCardProductVariantsIDs = implode('|', $deleteGiftCardProductVariantsIDsArray);

			//-------------
			$pages = $this->wire('pages');
			$giftCardProductVariants = $pages->find("id={$deleteGiftCardProductVariantsIDs}");

			// ---------
			// TRASH each gift card product variant page

			$notDeletedGiftCardProductVariantsTitles = [];
			$deletedGiftCardProductVariantsTitles = [];
			foreach ($giftCardProductVariants as $giftCardProductVariant) {
				// locked for edits
				if ($giftCardProductVariant->isLocked()) {
					$notDeletedGiftCardProductVariantsTitles[] = $giftCardProductVariant->title;
					continue;
				}
				$pages->trash($giftCardProductVariant);
				// successfully trashed
				if ($giftCardProductVariant->isTrash()) {
					$deletedGiftCardProductVariantsTitles[] = $giftCardProductVariant->title;
				}
			}
			// ------
			// NOTICES
			// success
			if (!empty($deletedGiftCardProductVariantsTitles)) {
				$this->message(sprintf(__("Deleted these gift card product variants: %s."), implode(', ', $deletedGiftCardProductVariantsTitles)));
			}
			// error
			if (!empty($notDeletedGiftCardProductVariantsTitles)) {
				$this->warning(sprintf(__("Could not delete these gift card product variants: %s."), implode(', ', $notDeletedGiftCardProductVariantsTitles)));
			}
		}
	}
}