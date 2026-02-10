<?php

namespace ProcessWire;

trait TraitPWCommerceActionsVariants
{

	/**
	 * Add Variants.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	public function addVariants($input)
	{

		$result = [
			'notice' => $this->_('Error encountered. Could not create new product variants.'),
			'notice_type' => 'error',
		];

		// get the template
		$template = $this->wire('templates')->get(PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME);
		// error: template not found
		// just in case no template?
		if (!$template) {
			$result['notice'] = $this->_('Required template not found!');
			return $result;
		}

		// get parent
		$parentID = (int) $input->pwcommerce_product_generate_variants_parent_page_id;
		// error: parent ID not found
		if (empty($parentID)) {
			$result['notice'] = $this->_('Parent ID not found!');
			return $result;
		}

		// get the parent page
		$pages = $this->wire('pages');
		$parent = $pages->get($parentID);

		// error: parent not found
		if (!$parent->id) {
			$result['notice'] = $this->_('Parent page not found!');
			return $result;
		}

		$languages = $this->wire('languages');
		$sanitizer = $this->wire('sanitizer');
		$createdVariantPagesIDs = [];
		$failedCount = 0;
		$failedTitles = [];
		// to use for 'existing_variants' check since page will not have reloaded
		$createdVariantsOptionsIDs = [];

		$variantsOptionsIDs = $input->pwcommerce_product_variant_preview_options_ids;

		$optionsSetsArray = [];

		// LOOP THROUGH THE OPTIONS AND CREATE AN ARRAY OF UNIQUE IDS
		// WE WILL USE THAT TO GRAB TITLES IN ALL LANGUAGES FOR ML SITES
		// WE WILL USE DEFAULT AS 'DEFAULT' IN CASE EMPTY VALUE FOR OTHER LANGUAGES
		$optionsIDsArrayForSelector = [];
		foreach ($variantsOptionsIDs as $optionsIDsString) {
			$optionsIDs = explode(",", $optionsIDsString);
			$optionsSetsArray[] = $optionsIDs;
			// ------------
			$optionsIDsArrayForSelector = array_merge($optionsIDsArrayForSelector, $optionsIDs);
		}

		$optionsIDsArrayForSelector = array_unique($optionsIDsArrayForSelector);

		$optionsIDsSelector = implode("|", $optionsIDsArrayForSelector);

		// ---------
		// GET THE TITLES OF THE OPTIONS IN ALL LANGUAGES

		$titleSubfields = [];

		if ($languages) {
			// prepare fields for selector
			foreach ($languages as $language) {
				// default subfield
				$title = "title.data";
				if ($language->id !== $languages->getDefault()->id) {
					// append language ID to subfield
					$title .= "{$language->id}";
				}
				// add to array of titles
				$titleSubfields[] = $title;
			}
		}



		// ------
		// GET THE TITLES USING FINDRAW
		// note: the template= is just to be extra sure
		$titles = $this->pwcommerce->findRaw("id={$optionsIDsSelector},template=option", $titleSubfields);

		// @NOTE: WE CAN GRAB VARIANTS DIRECTLY FROM  $titles on the fly! e.g.

		// 1220 => array
		// 'title' => array
		// 'data' => 'Zwart'
		// 'data1182' => 'Black'

		// AND

		// 1224 => array
		// 'title' => array
		// 'data' => 'Klein'
		// 'data1182' => 'Small'

		// GIVES US 'Black / Small' => 'Zwart / Klein'


		####################
		foreach ($optionsSetsArray as $option) {
			// our inputs are suffixed with an integer made up of the IDs of the attribute options the variant is made up of ($optionsIDs)
			// e.g. 123456789012 (red:1234;small:5678;cotton:9012)
			// TODO PREPARE THIS BEFORE HAND SO WE GET LANGUAGE VALUES ONCE; OTHERWISE TRACK IN ARRAY; USE FINDRAW? WILL IT WORK WITH LANGUAGES?
			// we build the suffix here
			$inputSuffix = implode("", $option);

			######################


			// we need to loop through option as it contains IDs of all attribute options that make up the variant
			$variantTitlePieces = [];
			foreach ($option as $optionID) {
				$optionID = (int) $optionID;

				if ($languages) {
					// for multilingual sites
					// get DEFAULT LANGUAGE title from $titles
					$variantTitlePiece = $titles[$optionID]['title']['data'];
				} else {
					// single language sites
					$variantTitlePiece = $titles[$optionID]['title'];
				}

				// ---
				// add to variant title pieces
				$variantTitlePieces[] = $variantTitlePiece;
			}

			$variantTitlePiecesStr = implode(" / ", $variantTitlePieces);
			// prefix variant title with parent title in DEFAULT langauge
			$title = "{$parent->title}: {$variantTitlePiecesStr}";
			$title = $sanitizer->text($title);

			$name = $sanitizer->pageName($title, true);
			$pageIDExists = (int) $pages->getRaw("parent_id={$parent->id},name=$name", 'id');

			// error: child page under this parent already exists
			if (!empty($pageIDExists)) {
				// CHILD PAGE ALREADY EXISTS!
				// skip

				$failedTitles[] = $title; // TODO: DELETE IF NOT IN USE
				$failedCount++;
				continue;
			}

			//-------------------------
			// stock
			//------
			$sku = $sanitizer->text($input->{"pwcommerce_product_variant_preview_sku{$inputSuffix}"});
			$price = (float) $input->{"pwcommerce_product_variant_preview_price{$inputSuffix}"};
			$enabledString = $sanitizer->fieldName($input->{"pwcommerce_product_variant_preview_enabled{$inputSuffix}"});
			$enabled = $enabledString === 'enabled' ? 1 : 0;
			$stock = [
				'sku' => $sku,
				// if USING PRICE + COMPARE PRICE FIELDS/APPROACH
				'price' => $price,
				// if USING SALES + NORMAL PRICE FIELDS/APPROACH
				'normalPrice' => $price,
				'enabled' => $enabled,
			];

			//---------
			// GOOD TO GO!
			$page = new Page();
			$page->template = $template;
			$page->parent = $parent;
			$page->title = $title;
			$page->name = $name;

			// ++++++++++
			if ($languages) {
				foreach ($languages as $language) {

					if ($language->id === $languages->getDefault()->id) {
						// skip default language as already set above
						continue;
					}

					// get DEFAULT LANGUAGE title from $titles
					// we need to loop through option as it contains IDs of all attribute options that make up the variant
					$languageTitlePieces = [];
					foreach ($option as $optionID) {
						$optionID = (int) $optionID;
						// i.e. data1234 (dataLanguageID)
						$languageTitlePiece = $titles[$optionID]['title']["data{$language->id}"];
						// ---
						// add to variant title pieces
						$languageTitlePieces[] = $languageTitlePiece;
					}

					$languageTitlePiecesStr = implode(" / ", $languageTitlePieces);
					// prefix variant language title with parent title in this LANGUAGE
					// TODO will this default to 'default' if language title empty?! test!
					$parentLanguageTitle = $parent->getLanguageValue($language, 'title');
					$languageTitle = "{$parentLanguageTitle}: {$languageTitlePiecesStr}";
					$languageTitle = $sanitizer->text($languageTitle);

					// set language title
					if (!empty($languageTitle)) {
						$page->setLanguageValue($language, 'title', $languageTitle);
					}
					// set page name for this language
					// @SEE NOTES ABOUT $beautify!
					$languageName = $sanitizer->pageName($languageTitle, true);
					$page->setName($languageName, $language);
					// +++++++++
					// set page as active in this language
					$page->set("status$language", 1);
				}
			}

			// *********
			// ADD ARRAY OF OPTIONS ID to PAGE REF FIELD FOR OPTIONS
			// $page->pwcommerce_product_attributes_options->add($optionsIDs);
			$page->{PwCommerce::PRODUCT_ATTRIBUTES_OPTIONS_FIELD_NAME}->add($option);
			// SET STOCK VALUES
			$page->{PwCommerce::PRODUCT_STOCK_FIELD_NAME}->setArray($stock);
			//------------------
			// SAVE the new page
			$page->save();
			// check, just in case page save failed
			if ($page->id) {
				$createdVariantPagesIDs[] = $page->id;
				$createdVariantsOptionsIDs[] = implode(',', $option);
			}
		}
		// end $options loop

		// TODO; NEED BETTER CHECK?, E.G. HOW MANY CREATED ETC!
		if (!empty($createdVariantPagesIDs)) {
			$count = count($createdVariantPagesIDs);
			$notice = sprintf(_n("Created %d product variant.", "Created %d product variants.", $count), $count);
			// if failed to create some variants
			if (!empty($failedCount)) {
				// TODO: ADD FAILED TITLES? WHAT IF LOTS? TRUNCATE?
				$notice .= " ";
				$notice .= sprintf(_n("Failed to create %d product variant as it already exists.", "Failed to created %d product variants as they already exist.", $failedCount), $failedCount);
			}
			//----------
			$noticeType = 'success';
		} else {
			$notice = $this->_('Could not create any variants!');
			$noticeType = 'error';
		}

		$result = [
			'notice_type' => $noticeType,
			'notice' => $notice,
			'created_variants_pages_ids' => $createdVariantPagesIDs,
			'failed_create_variants_count' => $failedCount,
			'created_variants_options_ids' => $createdVariantsOptionsIDs,
			// @note: @see PWCommerceCommonScripts.js trigger of reloads for usage
			'created_variants_timestamp' => time(),
		];

		return $result;
	}
}
