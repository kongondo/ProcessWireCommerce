<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesCountry
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ COUNTRY ~~~~~~~~~~~~~~~~~~


	/**
	 * Get all countries of the world.
	 *
	 * @note: This does not filter out shop's shipping zone countries.
	 *
	 * @return array $allCountries List of all countries.
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
	 * @note: This does not filter out shop's shipping zone countries.
	 *
	 * @return array $matchedCountry Details of country.
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