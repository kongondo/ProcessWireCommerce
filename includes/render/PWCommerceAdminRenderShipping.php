<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Shipping
 *
 * Class to render content for PWCommerce Admin Module executeShipping().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderShipping for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceAdminRenderShipping extends WireData
{






	private $adminURL;
	private $selectorStart;
	private $restOfTheWorldShippingZoneID;


	/**
	 *   construct.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __construct($options = null) {
		if (is_array($options)) {
			$this->adminURL = $options['admin_url'];
			if (!empty($options['selector_start'])) {
				$this->selectorStart = $options['selector_start'];
			}
		}
		// -----------
		$this->setRestOfTheWorldShippingZoneID();
	}

	/**
	 * Set Rest Of The World Shipping Zone I D.
	 *
	 * @return mixed
	 */
	private function setRestOfTheWorldShippingZoneID() {
		$this->restOfTheWorldShippingZoneID = $this->pwcommerce->getShopRestOfTheWorldShippingZoneID();

		// $isShowRestOfTheWorldCountries = (int) $page->id === $restOfTheWorldShippingZoneID;

	}

	/**
	 * Render Results.
	 *
	 * @param mixed $selector
	 * @return string|mixed
	 */
	public function renderResults($selector = null) {

		// enforce to string for strpos for PHP 8+
		$selector = strval($selector);

		//-----------------
		// FORCE DEFAULT LIMIT IF NO USER LIMIT SET
		if (strpos($selector, 'limit=') === false) {
			$limit = 10;
			$selector = rtrim("limit={$limit}," . $selector, ",");
		}
		//------------
		// FORCE TEMPLATE TO MATCH PWCOMMERCE SHIPPING ONLY + INCLUDE ALL + EXLUDE TRASH
		$selector .= ",template=" . PwCommerce::SHIPPING_ZONE_TEMPLATE_NAME . ",include=all,status<" . Page::statusTrash;
		//------------
		// ADD START IF APPLICABLE (ajax pagination)
		if (!empty($this->selectorStart)) {
			$start = (int) $this->selectorStart;
			$selector .= ",start={$start}";
		}

		//-----------------------

		$pages = $this->wire('pages')->find($selector);

		//-----------------

		// BUILD FINAL MARKUP TO RETURN TO ProcessPwCommerce::pagesHandler()
		// @note: important: don't remove the class 'pwcommerce_inputfield_selector'! we need it for htmx (hx-include)
		$out =
			"<div id='pwcommerce_bulk_edit_custom_lister' class='pwcommerce_inputfield_selector pwcommerce_show_highlight mt-5'>" .
			// BULK EDIT ACTIONS
			$this->getBulkEditActionsPanel() .
			// PAGINATION STRING (e.g. 1 of 25)
			"<h3 id='pwcommerce_bulk_edit_custom_lister_pagination_string'>" . $pages->getPaginationString('') . "</h3>" .
			// TABULATED RESULTS (if pages found, else 'none found' message is rendered)
			$this->getResultsMarkup($pages) .
			// HIDDEN INPUT FOR HTMX
			// set the context for differentiation when in ajax page
			"<input type='hidden' value='shipping' name='pwcommerce_inputfield_selector_context'>" .
			// PAGINATION (render the pagination navigation)
			$this->pwcommerce->getPagination($pages, $this->paginationOptions()) .
			//---------------
			"</div>";

		return $out;
	}

	/**
	 * Return markup for Rest of the World Shipping Zone.
	 *
	 * @return string|mixed
	 */
	protected function renderRestOfTheWorldShippingCountries() {

		/** @var array $restOfTheWorldCountries */
		$restOfTheWorldCountries = $this->getRestOfTheWorldCountries();

		// here we only need the country titles (names)
		$restOfTheWorldCountriesNames = array_column($restOfTheWorldCountries, 'title');

		$countriesString = implode(', ', $restOfTheWorldCountriesNames);

		$out = "";

		// ---------
		$out .= "<p class='italic'>{$countriesString}</p>";

		$out .= "<p class='notes'>" . $this->_("This shipping zone has been specified as the 'Rest of the World Shipping Zone' in the shop. The countries here have not been added to any shipping zone. Hence, they are designated as rest of the world.") . "</p>";

		$out = "<div id='pwcommerce_rest_of_the_world_countries'>{$out}</div>";

		return $out;
	}

	/**
	 * Get Rest Of The World Countries.
	 *
	 * @return mixed
	 */
	private function getRestOfTheWorldCountries() {
		$pages = $this->wire('pages');
		// TODO - LIMIT HERE??? or find raw??/
		$selector = "template=" . PwCommerce::SHIPPING_ZONE_TEMPLATE_NAME . ",include=all,status<" . Page::statusTrash;
		$allShippingZones = $pages->find($selector);

		// GET IDS OF ALL COUNTRIES IN ZONES; THEN FIND ALL SHOPCOUNTRIES WHOSE IDS DON'T MATCH THOSE
		// those will be our Rest of the World Countries!
		// @note: we only come here if the Rest of the World feature is in use in the shop!
		// i.e., in General Settings > Shipping

		################
		/** @var PageArray $this->allShippingZones */

		// unique countr IDs in shipping zones
		$countryIDsInZones = [];
		// $countryNamesInZones = [];

		// loop through shipping zones and get IDs of countries in the zones
		foreach ($allShippingZones as $zone) {

			// get the shipping zone field
			/** @var PageArray $shippingZoneCountries */
			$shippingZoneCountries = $zone->get(PwCommerce::SHIPPING_ZONE_COUNTRIES_FIELD_NAME);
			/** @var array $shippingZoneCountriesIDs */
			$shippingZoneCountriesIDs = $shippingZoneCountries->explode('id');
			// $shippingZoneCountriesNames = $shippingZoneCountries->explode('title');

			$countryIDsInZones = array_merge($countryIDsInZones, $shippingZoneCountriesIDs);
			// $countryNamesInZones = array_merge($countryNamesInZones, $shippingZoneCountriesNames);
		}

		// prepare selector to find shop countries NOT IN ANY shipping zone
		$countryIDsInZonesSelector = implode("|", $countryIDsInZones);

		// find countries whose IDs not in any shipping zone
		$selector = "template=" . PwCommerce::COUNTRY_TEMPLATE_NAME . ",id!={$countryIDsInZonesSelector},include=all,check_access=0,status<" . Page::statusTrash;
		$restOfTheWorldCountriesFields = ["id", 'title'];
		$restOfTheWorldCountries = $pages->findRaw($selector, $restOfTheWorldCountriesFields);
		// ----------

		// ----------
		return $restOfTheWorldCountries;
	}

	/**
	 * Get the options for building the form to add a new Shipping Zone for use in ProcessPWCommerce.
	 *
	 * @return mixed
	 */
	public function getAddNewItemOptions() {
		return [
			'label' => $this->_('Shipping Zone Name'),
			'headline' => $this->_('Add New Shipping Zone'),
		];
	}

	/**
	 * Pagination Options.
	 *
	 * @return mixed
	 */
	public function paginationOptions() {
		//------------
		$paginationOptions = ['base_url' => $this->adminURL . 'shipping/', 'ajax_post_url' => $this->adminURL . 'ajax/'];
		return $paginationOptions;
	}

	/**
	 * Get Results Markup.
	 *
	 * @param mixed $pages
	 * @return mixed
	 */
	private function getResultsMarkup($pages) {

		$out = "";
		if (!$pages->count()) {
			$out = "<p>" . $this->_('No shipping zones found.') . "</p>";
		} else {

			// set each row
			foreach ($pages as $page) {
				// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
				$out .= $this->getShippingZoneDetails($page);
			}
		}
		return $out;
	}

	/**
	 * Get Shipping Zone Details.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getShippingZoneDetails($page) {

		//------------
		// SHIPPING FEES

		$shippingFeeSettings = $page->pwcommerce_shipping_fee_settings;

		// handling fee type: none | fixed | percentage
		$handlingFeeType = $shippingFeeSettings->handlingFeeType;
		// handling fee value
		$handlingFeeValue = $shippingFeeSettings->handlingFeeValue;
		// maximum shipping fee
		$maximumShippingFee = $shippingFeeSettings->maximumShippingFee;

		// TODO: NEED TO SHOW CURRENCY + FORMATTED!
		// HANDLING FEE STRING
		$handlingFeeString = $this->_('Handling Fee: None');
		if (in_array($handlingFeeType, ['fixed', 'percentage'])) {
			if ($handlingFeeType === 'fixed') {
				// TODO REMOVE THIS HARDCODED GPB! REPLACE WITH SHOP CURRENCY!
				// fixed handling fee
				// TODO DELETE WHEN DONE
				// $handlingFeeString = sprintf(__('Handling fee: %1$s %2$.2f.'), $shopCurrency, $handlingFeeValue);
				$handlingFeeString = sprintf(__('Handling fee: %s.'), $this->pwcommerce->getValueFormattedAsCurrencyForShop($handlingFeeValue));
			} else {
				// percentage handling fee
				$handlingFeeString = sprintf(__("Handling fee: %.2f%%."), $handlingFeeValue);
			}
		}
		// MAXIMUM SHIPPING FEE STRING
		$maximumShippingFeeString = sprintf(__('Maximum shipping fee: %s.'), $this->pwcommerce->getValueFormattedAsCurrencyForShop($maximumShippingFee));

		//------------
		# SHIPPING ZONE COUNTRIES #
		// determine if rest of the world versus usual shipping zone country
		if ((int) $page->id === $this->restOfTheWorldShippingZoneID) {
			// rest of the world shipping zone
			$countriesString = $this->renderRestOfTheWorldShippingCountries();
		} else {
			// usual shipping zones
			// $countries = $page->pwcommerce_shipping_zone_countries;
			$countries = $page->get(PwCommerce::SHIPPING_ZONE_COUNTRIES_FIELD_NAME);
			if ($countries->count) {
				$countriesString = $countries->implode(', ', 'title');
			} else {
				$countriesString = $this->_('Zone has no countries set. Please edit this zone to add countries.');
			}
			$countriesString = "<p class='italic'>{$countriesString}</p>";
		}

		// ------------------
		// TODO: NEED TO REMOVE APPLY AND SELECT FROM ACTIONS PANE FOR SHIPPING!
		$out = "<div>" .
			// TODO WIP!
			// title
			$this->getEditItemTitle($page) .
			//------------
			// description
			"<h4>" . $this->_('Description') . "</h4>" .
			$page->pwcommerce_description .
			//------------
			// shipping fees
			"<h4>" . $this->_('Fees') . "</h4>" .
			"<span class='block'>{$handlingFeeString}</span>" .
			"<span class='block'>{$maximumShippingFeeString}</span>" .
			//------------
			// countries
			"<h4>" . $this->_('Countries') . "</h4>" .
			$countriesString .
			//------------
			"<hr />" .

			"</div>";

		return $out;
	}

	/**
	 * Get Edit Item Title.
	 *
	 * @param Page $page
	 * @return mixed
	 */
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

		$statusString = implode(', ', $status);
		if ($statusString) {
			$out .= "<small class='block italic mt-1'>{$statusString}</small>";
		}
		// $out = "<a href='{$adminURL}shipping/edit/?id={$page->id}'>{$page->title}</a>";
		return $out;
	}

	/**
	 * Get Edit Item U R L.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getEditItemURL($page) {
		// if page is locked, don't show edit URL
		if ($page->isLocked()) {
			$out = "<span>{$page->title}</span>";
		} else {
			$out = "<a href='{$this->adminURL}shipping/edit/?id={$page->id}'>{$page->title}</a>";
		}
		return $out;
	}

	/**
	 * Get Bulk Edit Actions Panel.
	 *
	 * @return mixed
	 */
	private function getBulkEditActionsPanel() {
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
			'add_new_item_label' => $this->_('Add new shipping zone'),
			// add new url
			'add_new_item_url' => "{$this->adminURL}shipping/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
			// hide bulk edit
			'is_hide_bulk_edit' => true,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	/**
	 * Get Bulk Edit Checkbox.
	 *
	 * @param int $id
	 * @param mixed $name
	 * @param mixed $xref
	 * @return mixed
	 */
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
}