<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsGeography
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ GEOGRAPHY  ~~~~~~~~~~~~~~~~~~

	// reformatted countries for selection in inputfieldtexttags (shop currency)
	/**
	 * Get Countries.
	 *
	 * @return mixed
	 */
	private function getCountries() {
		$formattedCountries = [];
		$countries = $this->countries->getCountries();
		foreach ($countries as $country) {
			$formattedCountries[$country['id']] = $country['name'];
		}
		return $formattedCountries;
	}

}
