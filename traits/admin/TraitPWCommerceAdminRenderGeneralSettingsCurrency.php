<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsCurrency
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CURRENCY  ~~~~~~~~~~~~~~~~~~

	// reformatted currencies for selection in inputfieldtexttags (shop currency)
	private function getCurrencies() {
		// @note: inputfieldtexttags accepts key => value pairs as 'value' => 'label', i.e. the key of the array at 'set_tags_list' is the value saved. Below, the 'key' is the 'country_code', e.g. 'AF', 'DE', etc.
		// --------
		$formattedCurrencies = [];
		$currencies = $this->currencies->getCurrencies();

		foreach ($currencies as $currency) {
			// @note: 'alphabetic_code' is not unique! e.g. EUR used in many countries
			// so we use 'country_code' instead
			// @note: we have three entries that are not real countries: the EU, the Holy See (Vatican) and SUCRE (Sistema....)
			$formattedCurrencies[$currency['country_code']] = "{$currency['country']} ({$currency['currency']})";
		}

		return $formattedCurrencies;
	}

	// reformatted currencies formats for selection in inputfieldtexttags (shop currency)
	private function getCurrenciesFormats() {
		$formattedCurrencies = [];
		$currencies = $this->currencies->getCurrencies();

		foreach ($currencies as $currency) {
			// @note: 'alphabetic_code' is not unique! e.g. EUR used in many countries
			// so we use 'country_code' instead
			// @note: we have three entries that are not real countries: the EU, the Holy See (Vatican) and SUCRE (Sistema....)
			// $formattedCurrencies[$currency['country_code']] = "{$currency['country']} ({$currency['currency']})";
			if (empty($currency['locale_codes']))
				continue;
			// @note: key is localeCode, i.e. LCID, e.g. en-GB, fr-CA, etc. Value $localeCodes is an array
			foreach ($currency['locale_codes'] as $localeCode => $localeCodes) {
				// $formattedCurrencies[$currency['country_code']] = "{$currency['country']} ({$localeCode})";
				$formattedCurrencies[$localeCode] = "{$currency['country']} ({$localeCode})";
			}
		}

		return $formattedCurrencies;
	}

}
