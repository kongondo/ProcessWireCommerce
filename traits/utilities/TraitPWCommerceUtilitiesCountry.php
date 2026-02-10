<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesCountry
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ COUNTRY ~~~~~~~~~~~~~~~~~~


	/**
	 * Get all countries of the world.
	 *
	 * @return mixed
	 */
	public function getAllCountries() {
		require_once __DIR__ . '/../includes/geopolitical/PWCommerceCountries.php';
		$pwcommerceCountries = new PWCommerceCountries();
		$allCountries = $pwcommerceCountries->getCountries();
		// ------------
		return $allCountries;
	}

	/**
	 * Get a single country details by its name.
	 *
	 * @param mixed $countryName
	 * @return mixed
	 */
	public function getCountryDetails($countryName) {
		$allCountries = $this->getAllCountries();
		// ------------
		$matchedCountry = array_filter($allCountries, fn($item) => $item['name'] === $countryName);

		if (!empty($matchedCountry)) {
			// get first matched item
			$matchedCountry = reset($matchedCountry);

		}
		return $matchedCountry;
	}





}