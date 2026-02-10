<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsShipping
{
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ SHIPPING TAB  ~~~~~~~~~~~~~~~~~~
	/**
	 * Get Shipping Tab.
	 *
	 * @return mixed
	 */
	private function getShippingTab() {

		$customHookURL = "/find-pwcommerce_general_settings_shipping_zones/";
		$tagsURL = "{$customHookURL}?q={q}";
		$savedRestOfTheWorldShippingZone = $this->getSavedRestOfTheWorldShippingZone('rest_of_the_world_shipping_zone');
		$shippingZonesURL = $this->adminURL . 'shipping';
		$countriesURL = $this->adminURL . 'tax-rates';
		$restOfTheWorldShippingZoneNotes =
			sprintf($this->_('This [shipping zone](%1$s) must have been created in advance. For instance, a shipping zone called \'Rest of the World\'. This zone will be used if either no other shipping zones have been created or if a customer\'s shipping country does not match any other existing shipping zones. Such customer [countries](%2$s) should still be set up in your shop. However, they don\'t need to be added to any shipping zone.'), $shippingZonesURL, $countriesURL);

		// -----------

		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_general_settings_shipping_tab',
				'title' => $this->_('Shipping'),
			],
			'inputfields' => [
				// rest of the world zone
				// @note: both a fallback if no zones OR if not matching zones

				'rest_of_the_world_shipping_zone' => [
					'type' => 'tags',
					'name' => 'pwcommerce_general_settings_rest_of_the_world_shipping_zone',
					'label' => $this->_('Rest of the World Shipping Zone'),
					'placeholder' => $this->_('Type at least 3 characters to search for shipping zone'),
					// TODO: REPHRASE?
					// TODO: WHAT ABOUT DIGITAL OR EVEN PRODUCTS? DO THEY ALSO NEED A SHIPPING ZONE TO BE MATCHED?
					'description' => $this->_('Specify the shipping zone to be used as a fallback shipping zone. If this is not specified, customers whose locations are not in any existing shipping zones will not be able to complete their orders.'),
					'notes' => $restOfTheWorldShippingZoneNotes,
					'useAjax' => true,
					'closeAfterSelect' => true,
					'tagsUrl' => $tagsURL,
					// @note: doesn't seem to work after POST-ing; still space separated
					//  'delimiter ' => 'c',
					// 'columnWidth' => 50,
					'maxItems' => 1,
					// @note: special strategy for InputfieldTextTags that has saved 'pages'
					'set_tags_list' => $savedRestOfTheWorldShippingZone['set_tags_list'],
					'value' => $savedRestOfTheWorldShippingZone['value'],
				],
			],

		];

		return $tabAndContents;
	}

	/**
	 * Finds saved Rest of The World (ROW) shipping zone.
	 *
	 * @param mixed $setting
	 * @return mixed
	 */
	private function getSavedRestOfTheWorldShippingZone($setting) {
		$value = null;
		$setTagsList = [];
		if (!empty($this->generalSettings[$setting])) {
			$savedValue = $this->generalSettings[$setting];
			// @note: for 'rest_of_the_world_shipping_zone' this is a single value
			$pageIDs = $savedValue;
			// ==============
			$selector = "id={$pageIDs},include=hidden";
			$pages = $this->wire('pages')->findRaw($selector, 'title');

			// =============
			if (!empty($pages)) {
				// @note: $pages will be in the format $page->id => $page->title
				$value = array_keys($pages);
				$setTagsList = $pages;
			}
		}
		// set_tags_list
		$valuesForInputfieldTextTags = [
			'value' => $value,
			// @note: for $field->val(array),
			'set_tags_list' => $setTagsList, // @note: for $field->setTagsList(array)
		];

		return $valuesForInputfieldTextTags;
	}

}
