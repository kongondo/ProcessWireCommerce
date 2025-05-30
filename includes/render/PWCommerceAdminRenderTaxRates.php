<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Tax Rates (Country Taxes)
 *
 * Class to render content for PWCommerce Admin Module executeTaxRates().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderTaxRates for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceAdminRenderTaxRates extends WireData
{


	private $assetsURL;
	private $countries;
	private $continents;


	public function __construct($options) {
		$this->continents = $this->pwcommerce->getPWCommerceClassByName('PWCommerceContinents');
		$this->countries = $this->pwcommerce->getPWCommerceClassByName('PWCommerceCountries');
		# -----------------
		$this->assetsURL = $options['assets_url'];
	}

	// ~~~~~~~~~~~~~~~

	protected function getResultsTableHeaders() {
		return [
			// TITLE
			[$this->_('Title'), 'pwcommerce_tax_rates_table_title'],
			// COUNTRY TAX RATE
			[$this->_('Country Tax Rate'), 'pwcommerce_tax_rates_table_country_tax_rate'],
			// OVERRIDES
			[$this->_('Overrides'), 'pwcommerce_tax_rates_table_overrides'],
			// TERRITORIES
			[$this->_('Territories'), 'pwcommerce_tax_rates_table_territories'],
			// USAGE
			[$this->_('Zones'), 'pwcommerce_tax_rates_table_usage'],
		];
	}

	protected function getResultsTableRow($page, $editItemTitle) {

		// @note: a country can only have one standard/base tax!
		// any other exceptions go to 'overrides'
		// @note: 'pwcommerce_tax_rates' is a WireArray, to cater for territories multiple taxes (TODO: might change this in future to allow one also??)
		$taxRates = $page->pwcommerce_tax_rates;
		// get the first saved country rate if already saved
		$countryStandardTaxRate = $taxRates->count() ? $taxRates->first()->taxRate : '';
		//---------------
		// get the count of zones referencing this country
		$referencingZonesCount = $page->references(true)->count;
		$referencingZonesCountString = !empty($referencingZonesCount) ? $referencingZonesCount : $this->_('Country is not in any shipping zone');

		//------------
		$row = [
			// TITLE
			$editItemTitle,
			// COUNTRY TAX RATE
			$countryStandardTaxRate,
			// OVERRIDES COUNT
			// $page->pwcommerce_tax_overrides->count,
			$page->get(PwCommerce::TAX_OVERRIDES_FIELD_NAME)->count,
			// TERRITORIES
			$page->numChildren,
			// USAGE TODO: ZONES REFERENCING THIS COUNTRY
			$referencingZonesCountString,
		];
		return $row;
	}

	protected function getNoResultsTableRecords() {
		$noResultsTableRecords = $this->_('No countries found.');
		return $noResultsTableRecords;
	}


	protected function getBulkEditActionsPanel($adminURL) {
		$actions = [
			'publish' => $this->_('Publish'),
			'unpublish' => $this->_('Unpublish'),
			'lock' => $this->_('Lock'),
			'unlock' => $this->_('Unlock'),
			'trash' => $this->_('Trash'),
			'delete' => $this->_('Delete'),
		];
		$options = [
			// add new link
			'add_new_item_label' => $this->_('Add new country'),
			// add new url
			'add_new_item_url' => "{$adminURL}tax-rates/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}


	// ~~~~~~~~~~~~~
	/**
	 * Builds a custom add new page/item for adding a new country.
	 *
	 * Returns InputfieldForm that includes form inputs needed to create new country.
	 *
	 * @return InputfieldForm $form Add new page Form.
	 */
	public function getCustomAddNewItemForm() {
		$form = $this->pwcommerce->getInputfieldForm();
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		$out = "";

		// ++++++++++++++++

		//-------------------countries (getInputfieldMarkup)

		$out .= $this->renderCountriesList();

		$description = $this->_('Add countries that you will be shipping to. If the country has territories (e.g. Provinces, States, etc), these will be automatically added when you save. Later, you will be able to add these countries to shipping zones.');

		$options = [
			'description' => $description,
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// @note: we need this outline to show
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// @note: add custom margin to the div.InputfieldContent.uk-form-controls
		$field->contentClass('mt-5');
		$wrapper->add($field);

		//------------------- is_ready_to_save (getInputfieldHidden)
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPwCommerce::renderAddItem() know that we are ready to save
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];

		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		//------------------- save button (getInputfieldButton)
		$options = [
			'id' => "submit_save",
			'name' => "pwcommerce_save_new_button",
			'type' => 'submit',
			'label' => $this->_('Save'),
		];
		$field = $this->pwcommerce->getInputfieldButton($options);
		$field->showInHeader();
		// add submit button for add new country add  SAVE process views
		$wrapper->add($field);

		//------------------- save + publish button (getInputfieldButton)
		$options = [
			'id' => "submit_save_and_publish",
			'name' => "pwcommerce_save_and_publish_new_button",
			'type' => 'submit',
			'label' => $this->_('Save + Publish'),
			'secondary' => true,
		];
		$field = $this->pwcommerce->getInputfieldButton($options);
		// add submit button for single item add  SAVE + PUBLISH process views
		$wrapper->add($field);

		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//----------
		return $form;
	}

	// @note: uses alpine.js!
	private function renderCountriesList() {

		$out = "";
		// get script with data for building countries list
		$script = $this->getJavaScriptConfigurationsForCountriesList();

		// ================
		$continents = $this->continents->getContinents();
		$foundText = $this->_('Found');

		$out .= "<div x-data='ProcessPWCommerceData' id='pwcommerce_add_new_countries_wrapper'  x-init='initContinentsAndCountriesData'>{$script}";

		// FILTER COUNTRIES TEXT BOX + results count, if searching
		$out .= "<div class='mt-5 mb-5'>" . $this->getFilterCountriesBox() .
			// TODO: NOT WORKING! MAYBE SET STORE VALUE TO CHECK FROM
			//
			// "<span class='mt-1 invisible' :class='isShowFilterFoundCount() || `invisible`'>Found: 0</span>" .
			//  "<hr /></div>";
			// @note: better to use object syntax to bind classes in this case since 'When using object-syntax, Alpine will NOT preserve original classes applied to an element's class attribute.'
			"<small class='invisible' :class='{ invisible: !isShowFilterFoundCount() }'>{$foundText}: <span x-text='getSearchCountryResultsCount'></span></small>" .
			"<hr /></div>";

		// COUNT OF CURRENTLY SELECTED COUNTRIES
		// @note: we default to '0' to avoid 'fouc'
		$out .= "<div><p>" . $this->_('Total selected') . ": <span x-text='getTotalSelectedCountries'>0</span></p></div>";

		// ACCORDION LIST OF CONTINENTS AND COUNTRIES
		$out .= "<ul class='list-none m-0 p-0' x-data='ProcessPWCommerceData'>";

		// @NOTE?TODO: HAVING ISSUES BELOW! ALPINE SAYING getContinentCountries IS NOT FOUND!
		// @SEE https://github.com/alpinejs/alpine/pull/316 && https://github.com/alpinejs/alpine/issues/158
		// decided to use alpine to build inner lists only (i.e., continent countries)
		foreach ($continents as $continent) {
			$continentID = $continent['id'];
			$xref = $continentID;

			// ===============
			// CONTINENT
			$out .= "<li class='py-1.5 px-0 relative pwcommerce_add_new_countries_continent' :class='getContinentClasses(`{$xref}`)'>" .
				//--------
				// we toggle accordion when it is clicked
				"<i class='fa fa-fw cursor-pointer fa-caret-right rotate' :class='isContinentAccordionOpen(`{$xref}`) && `down`' aria-hidden='true' @click.stop='toggleContinentAccordionOpen(`{$xref}`)'></i>" .
				//----------
				// get checkbox for continent
				$this->getContintentCheckbox($continent);
			// ----------
			// CONTINENT COUNTRIES WRAPPER
			$out .= "<ul class='list-none m-0 relative overflow-hidden transition-all max-h-0 duration-700 pwcommerce_add_new_countries_continent_countries_list_wrapper' x-ref='{$xref}' x-bind:style='getContinentAccordionStyles(`{$xref}`)' aria-hidden='false'>";

			// ===============
			// CONTINENT COUNTRY LIST: BUILD USING ALPINE JS
			$out .= "<template x-for='country in getContinentCountries(`{$xref}`)' >" .
				"<li class='text-sm my-1 py-1 px-0'>" .
				// +++ country not yet added list markup +++
				"<template x-if='!isAlreadyAdded(country.id)'>" .
				$this->getNotYetAddedCountryListMarkup() .
				"</template>" .
				// +++ country already added list markup +++
				"<template x-if='isAlreadyAdded(country.id)'>" .
				$this->getAlreadyAddedCountryListMarkup() .
				"</template>" .
				// close country </li>
				"</li>" .
				"</template>";
			// close countries list
			$out .= "</ul>";
			// ----------
			// close continent list
			$out .= "</li>";
		}
		//-------------
		// close outer wrappers
		$out .= "</ul></div>";

		// ----------

		return $out;
	}

	private function getFilterCountriesBox() {
		//------------------- search_country_query (getInputfieldText)
		$options = [
			'id' => "pwcommerce_add_new_countries_countries_filter_box",
			'label' => $this->_('Filter Countries'),
			'placeholder' => $this->_('Start typing to filter countries'),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
		];
		$field = $this->pwcommerce->getInputfieldText($options);
		// search_country_query
		$field->attr([
			// 'x-model' => '$store.ProcessPWCommerceStore.search_country_query',
			// @note: defaults to 250ms
			'x-model.debounce' => '$store.ProcessPWCommerceStore.search_country_query',
			// TODO: is this debounce value ok?
			'x-on:input.debounce.300ms' => 'handleSearchInput', // to handle count of countries found
			//  'x-on:input.debounce.500ms' => 'handleSearchInput', // to handle count of countries found
			//  'x-model.debounce.500ms' => '$store.ProcessPWCommerceStore.search_country_query',
		]);
		return $field->render();
	}

	private function getContintentCheckbox($continent) {

		$continentID = $continentID = $continent['id'];

		// @note: already translated
		$label = "<span class='cursor-pointer ml-3'>" . $continent['name'] . "</span>";
		//--------------
		$options = [
			'id' => "pwcommerce_add_new_countries_continent_{$continentID}",
			// @note: not really needed!
			'name' => "pwcommerce_add_new_countries_continent_{$continentID}",
			'label' => $label,
			// 'label2' => $this->_('xxx'),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $continentID,

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		$field->attr([
			'x-ref' => "pwcommerce_add_new_countries_continent_{$continentID}",
			'x-on:change' => 'handleContinentCheckboxChange',
		]);

		// @note: disable entity encode of label so we can render own markup around continent name
		$field->entityEncodeLabel = false;

		return $field->render();
	}

	// country 'not-yet added added' markup
	private function getNotYetAddedCountryListMarkup() {
		$label = $this->getCountryFlag() . "<span x-text='country.name' class='cursor-pointer' ></span>";
		$out = $this->getCountryCheckbox($label);
		return $out;
	}

	private function getCountryCheckbox($label) {

		$options = [
			'name' => 'pwcommerce_add_new_countries[]',
			//'label' => ' ', // @note: skipping label
			'label' => $label,
			// 'label2' => $this->_('Use custom handling fee'),
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_add_new_countries',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		// TODO: ADD THIS ATTR AND MAYBE EVEN A x-ref? so we can selectall using alpinejs
		$field->attr([
			'x-on:change' => 'handleCountryCheckboxChange',
			// TODO: not really needed?
			'x-bind:id' => "`pwcommerce_add_new_country_checkbox\${country.id}`",
			'x-bind:value' => 'country.id',
			//  'x-show' => "!isAlreadyAdded(country.id)",
			'x-bind:data-country-continent' => '`pwcommerce_add_new_countries_continent_${country.continent}`',
		]);
		// @note: disable entity encode of label so we can render flag and country name markup
		$field->entityEncodeLabel = false;

		return $field->render();
	}

	// country 'already added' markup
	private function getAlreadyAddedCountryListMarkup() {
		$alreadyAddedText = $this->_('already added');
		// @note: x-if requires one root element only!
		$out = "<div>" . $this->getCountryFlag() . "<span x-text='country.name'></span>" .
			"<small class='ml-1'>({$alreadyAddedText})</small></div>";
		return $out;
	}

	private function getCountryFlag() {
		$out = "<img :src='getCountryFlag(country.id)' class='w-7 inline-block mr-3' :class='isAlreadyAdded(country.id) ? `ml-7` : `ml-3 cursor-pointer`'>";
		return $out;
	}

	// TODO: IN FUTURE MOVE TO UTILITIES SO CAN REUSE WITH PWCOMMERCEACTIONS
	private function getAlreadyAddedCountries() {
		// finding countries that have saved location codes. Should return all available since this is not a user editable setting and it is set on create/add new countries/tax rates!
		$countries = $this->wire('pages')->findRaw("template=" . PwCommerce::COUNTRY_TEMPLATE_NAME . ",pwcommerce_tax_rates.tax_location_code!='',include=all", 'pwcommerce_tax_rates.tax_location_code');
		// TODO: confirm this doesn't break!
		$countryCodes = array_column($countries, 0);
		return $countryCodes;
	}

	/**
	 * Inline javascript configuration values for ALL line items in an order for given $data.
	 *
	 * @param array $data
	 * @return string
	 */
	private function getJavaScriptConfigurationsForCountriesList() {
		$data = [
			'geographical_data' => [
				'continents' => $this->continents->getContinents(),
				'countries' => $this->countries->getCountries(),
				'already_added_countries' => $this->getAlreadyAddedCountries(),
				'flags_url' => "{$this->assetsURL}flags/",
			],
		];
		return "<script>ProcessWire.config.PWCommerceProcessRenderTaxRates = " . json_encode($data) . ';</script>';
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ QUICK FILTERS  ~~~~~~~~~~~~~~~~~~

	protected function ___getQuickFiltersValues() {
		$filters = [
			// reset/all
			'reset' => $this->_('All'),
			// active
			'active' => $this->_('Active'),// published
			'draft' => $this->_('Draft'),// unpublished
			// shipping zone
			'not_in_shipping_zone' => $this->_('Not in Shipping Zone'),
			// tax
			'no_tax' => $this->_('No Tax'),
			// TODO add filter for territories??
			// territories
			// 'no_territories' => $this->_('No Territories'),
			// overrides
			'has_overrides' => $this->_('Has Overrides'),
		];

		// ------
		return $filters;
	}

	private function getAllowedQuickFilterValues() {
		// filters array
		/** @var array $filters */
		$filters = $this->getQuickFiltersValues();
		$allowedQuickFilterValues = array_keys($filters);
		return $allowedQuickFilterValues;
	}

	protected function getSelectorForQuickFilter() {
		$input = $this->wire('input');
		$selector = '';
		// NOTE: KEYS -> filter values; VALUEs -> STATUS CONSTANTS
		$allowedQuickFilterValues = $this->getAllowedQuickFilterValues();
		$quickFilterValue = $this->wire('sanitizer')->option($input->pwcommerce_quick_filter_value, $allowedQuickFilterValues);
		if (!empty($quickFilterValue)) {
			// quick filter checks
			// ++++++++++
			if (in_array($quickFilterValue, ['active', 'draft'])) {
				// ACTIVE (PUBLISHED) OR DRAFT (UNPUBLISHED)
				$selector = $this->getSelectorForQuickFilterActive($quickFilterValue);
			} elseif ($quickFilterValue === 'not_in_shipping_zone') {
				// NOT IN SHIPPNG ZONE
				$selector = $this->getSelectorForQuickFilterNotInShippingZone();
			} else if ($quickFilterValue === 'no_tax') {
				// NO TAX SPECIFIED
				$selector = $this->getSelectorForQuickFilterNoTax();
			} else if ($quickFilterValue === 'has_overrides') {
				// HAS OVERRIDES
				$selector = $this->getSelectorForQuickFilterHasOverrides();
			}
		}
		return $selector;
	}

	private function getSelectorForQuickFilterActive($quickFilterValue) {
		$selector = '';
		if ($quickFilterValue === 'active') {
			// PUBLISHED
			$selector = ",status<" . Page::statusUnpublished;
		} else if ($quickFilterValue === 'draft') {
			// UNPUBLISHED
			$selector = ",status>=" . Page::statusUnpublished;
		}
		// ----
		return $selector;
	}

	private function getSelectorForQuickFilterNotInShippingZone() {
		// e.g.
		// SELECT data as country_id
		// FROM field_pwcommerce_shipping_zone_countries
		// GROUP BY country_id

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::SHIPPING_ZONE_COUNTRIES_FIELD_NAME,
			'select_columns' => ['data AS country_id'],
			'group_by_columns' => ['country_id']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);

		if (!empty($results)) {
			$tagsIDs = array_column($results, 'country_id');
			$tagsIDsSelector = implode("|", $tagsIDs);
			// NOTE: we want IDs of unused tags!
			$selector = ",id!={$tagsIDsSelector}";
		}

		// ----
		return $selector;

	}

	private function getSelectorForQuickFilterNoTax() {
		// TODO HOW TO INCLUDE TERRITORIES?
		// this checks both 'NULL' and saved but 0 tax rates
		$selector = ",(" . PwCommerce::TAX_RATES_FIELD_NAME . ".tax_rate=''),(" . PwCommerce::TAX_RATES_FIELD_NAME . "='')";
		// ----
		return $selector;

	}

	private function getSelectorForQuickFilterHasOverrides() {
		$selector = "," . PwCommerce::TAX_OVERRIDES_FIELD_NAME . "!=''";
		// ----
		return $selector;
	}



}