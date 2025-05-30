<?php

namespace ProcessWire;

trait TraitPWCommerceActionsCountry
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ COUNTRY ~~~~~~~~~~~~~~~~~~

	/**
	 * Create new countries for tax-rates context.
	 *
	 * Can be one or multiple.
	 *
	 * @access private
	 * @param WireInputData $input
	 * @return array $result Outcome of the creation action.
	 */
	private function addNewCountriesAction($input) {

		$result = [
			'notice' => $this->_('Error encountered. Could not create new item.'),
			'notice_type' => 'error',
		];

		// @note: array
		$countriesToAdd = $input->pwcommerce_add_new_countries;
		// error: no county selected/checked
		if (empty($countriesToAdd)) {
			$result['notice'] = $this->_('At least one country should be selected!');
			return $result;
		}

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

		// ----------------------
		// PROCEED

		// GET COUNTRIES
		$countries = $this->pwcommerce->getPWCommerceClassByName('PWCommerceCountries');



		$languages = $this->wire('languages');
		// @note: array of country codes!
		$alreadyAddedCountries = $this->getAlreadyAddedCountries();

		// process countries
		$sanitizer = $this->wire('sanitizer');
		$i = 0;
		foreach ($countriesToAdd as $countryID) {
			$countryCode = $sanitizer->text($countryID);
			$country = $countries->getCountryByCode($countryCode);

			if (!empty($country)) {
				// if country already added, skip it!
				if (in_array($country['id'], $alreadyAddedCountries)) {
					continue;
				}
				//---------
				$title = $sanitizer->text($country['name']);
				// error: country title/nae not found
				if (!$title) {
					// $result['notice'] = $this->_('A country name is required!');
					// return $result;
					continue;
				}

				// first check if page already exists (under this parent)
				$name = $sanitizer->pageName($title, true);
				$pageIDExists = $this->wire('pages')->getRaw("parent_id={$parent->id},name=$name", 'id');
				// error: child page under this parent already exists
				if (!empty($pageIDExists)) {
					// CHILD PAGE ALREADY EXISTS!
					// $notice = sprintf(__("A country with the name %s already exists!"), $title);
					// $result['notice'] = $notice;
					// return $result;
					continue;
				}
				//---------------
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

				// run extra operations on add new country
				// e.g. need to create a single tax rate for the country's base/standard tax rate PLUS save the tax_location_id!
				$page = $this->runCountryExtraAddNewItemOperations($page, $countryCode);

				// TODO: ADD COUNTRY LOCALE INFO, I.E. BASE TAX IF AVAIALABLE

				// set page as active in other languages
				if ($languages) {
					foreach ($languages as $language) {
						// skip default language as already set above
						if ($language->name == 'default') {
							continue;
						}
						$page->set("status$language", 1);
					}
				}
				//------------------
				// SAVE the new page
				$page->save();

				// error: could not save page for some reason
				if (!$page->id) {
					// $result['notice'] = $this->_('An error prevented the page from being created!');
					// return $result;
					continue;
				}

				// note: ADD COUNTRY TERRITORIES - I.E. CHILD PAGES! IF AVAIALABLE
				$countryTerritories = $this->getCountryTerritories($country['id']);
				if (!empty($countryTerritories)) {
					// TODO: in future release do a count of territories created!
					$this->addCountryTerritories($countryTerritories['territories'], $page);
				}
				//---------
				$i++;
			}
			// if not empty country
		}
		// END: FOREACH LOOP
		// ===================

		// TODO: NEED TO CONFIRM ADDED!

		if (!empty($i)) {
			$notice = sprintf(_n("Added %d country.", "Added %d countries.", $i), $i);
			$result = [
				'notice' => $notice,
				'notice_type' => 'success',
			];
		} else {
			// error: could not create any country pages
			$result['notice'] = $this->_('An error prevented country pages from being created!');
			return $result;
		}

		//-------
		return $result;
	}

	private function getAlreadyAddedCountries() {
		// finding countries that have saved location codes. Should return all available since this is not a user editable setting and it is set on create/add new countries/tax rates!
		$countries = $this->wire('pages')->findRaw("template=pwcommerce-country,pwcommerce_tax_rates.tax_location_code!='',,include=all", 'pwcommerce_tax_rates.tax_location_code');
		// TODO: confirm this doesn't break!
		$countryCodes = array_column($countries, 0);
		return $countryCodes;
	}

	private function getCountryLocales() {
		// GET COUNTRIES LOCALES
		$locales = $this->pwcommerce->getPWCommerceClassByName('PWCommerceLocales');
		return $locales;
	}

	private function getCountryBaseTaxRate($countryCode) {
		$countryBaseTaxRate = null;
		$locales = $this->getCountryLocales();
		$countryLocale = $locales->getCountryLocaleByCode($countryCode);
		if (!empty($countryLocale)) {
			$taxRates = !empty($countryLocale['tax_rates']) ? $countryLocale['tax_rates'] : null;
			// if we have tax rates - check if multiple
			if (!empty($taxRates)) {
				foreach ($taxRates as $taxRate) {
					// if available, country base tax rates will be empty string at 'state' index
					if (empty($taxRate['state'])) {
						$countryBaseTaxRate = $taxRate;
						break;
					}
				}
			}
		}
		// -----------
		return $countryBaseTaxRate;
	}

	private function getCountryTerritories($countryCode) {
		// GET COUNTRIES TERRITORIES
		$territories = $this->pwcommerce->getPWCommerceClassByName('PWCommerceTerritories');
		//  ++++
		$countryTerritories = $territories->getCountryTerritoryByCode($countryCode);
		return $countryTerritories;
	}

	private function addCountryTerritories($countryTerritories, $parent) {
		// TODO:IN FUTURE, DO A COUNT HERE OF NUMBER OF COUNTRY TERRITORIES CREATED!
		$template = "pwcommerce-country-territory";
		$sanitizer = $this->wire('sanitizer');
		$languages = $this->wire('languages');
		foreach ($countryTerritories as $countryTerritory) {
			$title = $sanitizer->text($countryTerritory['name']);
			// error: country territory title is empty
			if (!$title) {
				continue;
			}
			//-----------
			$page = new Page();
			$page->template = $template;
			$page->parent = $parent;
			$page->title = $title;
			$page->name = $sanitizer->pageName($title, true);

			// set page as active in other languages
			if ($languages) {
				foreach ($languages as $language) {
					// skip default language as already set above
					if ($language->name == 'default') {
						continue;
					}
					$page->set("status$language", 1);
				}
			}

			//------------
			$page->save();
		}
	}

}
