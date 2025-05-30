<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Money: Trait class for PWCommerce Money.
 *
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerce Class for PWCommerce
 * Copyright (C) 2024 by Francis Otieno
 * MIT License
 *
 */

// require 'vendor/autoload.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;



trait TraitPWCommerceMoney
{





	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MONEY ~~~~~~~~~~~~~~~~~~

	// private $moneyOptions;
	private $currency;

	/**
	 * Create Money Object from given whole amount and currency.
	 * @param float $amount Whole amount to create Money from.
	 * @param string $currency Currency for the money. Defaults to shop currency.
	 * @return object Money\Money object.
	 */
	public function money(float $amount, string $currency = '') {
		$this->currency = $currency;

		if (empty($this->currency)) {
			$this->currency = $this->getShopCurrency();
		}

		$currency = strtoupper($this->currency);
		// pence, cents, etc
		$amountInUnits = strval($amount * PwCommerce::HUNDRED);

		// $fiver = new Money(500, new Currency('USD'));
		$money = new Money($amountInUnits, new Currency($currency));
		// or shorter:
		// $fiver = Money::USD(500);
		// EXPERIMENT WITH SHORTER

		$money = Money::{"$currency"}($amountInUnits);

		return $money;
	}

	public function currencies() {
		$currencies = new ISOCurrencies();
		// ------
		return $currencies;
	}

	public function interNationalMoneyFormatter() {
		$currencies = $this->currencies();
		// $vars = get_class_vars(get_class($currencies));
		// $methods = get_class_methods($currencies);
		// locale from shop
		$locale = $this->getShopCurrencyLocale()['locale_code'];
		// $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
		$numberFormatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
		// -------
		$moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);
		// $vars = get_class_vars(get_class($currencies));
		// $methods = get_class_methods($moneyFormatter);
		// ------
		return $moneyFormatter;
	}


	public function formattedMoney(float $amount) {
		$moneyFormatter = $this->interNationalMoneyFormatter();
		$money = $this->money($amount);
		$formattedMoney = $moneyFormatter->format($money); // outputs $1.00
		// ------
		return $formattedMoney;
	}

	public function getWholeMoneyAmount($money): float {
		// NOTE: WE DIVIDE BY 100 SINCE getAmount() returns units (pence, cents, etc) AND ALSO AS a string
		$wholeMoneyAmount = (float) ($money->getAmount() / PwCommerce::HUNDRED);
		// ------
		return $wholeMoneyAmount;
	}

	public function getMoneyTotalAsWholeMoneyAmount(float $unitAmount, int $quantity) {

		$unitAmountMoney = $this->money($unitAmount);
		$totalAmountMoney = $unitAmountMoney->multiply((int) $quantity);
		$wholeMoneyAmount = $this->getWholeMoneyAmount($totalAmountMoney);


		// ------
		return $wholeMoneyAmount;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CURRENCY ~~~~~~~~~~~~~~~~~~

	#####################
	public function getShopCurrency() {
		// TODO CONFIRM WORKS!
		$shopCurrencyData = $this->getShopCurrencyData();
		// note: default to GBP for fresh installs
		$shopCurrency = !empty($shopCurrencyData['alphabetic_code']) ? $shopCurrencyData['alphabetic_code'] : 'GBP';

		// @note: the 3-character ISO-4217 codes (alphabetic_code)
		// @note: not all may be supported by all payment providers!
		// @note: PayPal uses 3-character ISO-4217 codes to specify currencies in fields and variables.
		// TODO - IF OTHER PAYMENT PROVIDERS HAVE DIFFERENT REQUIREMENTS, THEN MAKE THIS METHOD DYNAMIC!
		// return $shopCurrencyData->alphabetic_code;
		return $shopCurrency;
	}


	/**
	 * Return a given value formatted as a currency per shop's currency.
	 *
	 *  @note: This uses the saved shop's currency 'locale' and not necessarily the user's locale!
	 *
	 * @access public
	 * @see https://www.php.net/manual/en/numberformatter.formatcurrency.php
	 * @param integer $value Value to format as a currency.
	 * @param array $shopCurrencyData Currency data for the shop (currency, code, country, etc).
	 * @return string|integer Formatted value or raw value if shop currency not saved yet.
	 */
	public function getValueFormattedAsCurrencyForShop($amount, string $currency = '') {

		// if we got passed shop currency, we use it, else get it afresh from the store
		if (empty($currency)) {
			$shopCurrencyData = $this->getShopCurrencyData();
			// if we didn't get a shop data, just return the raw value
			if (empty($shopCurrencyData)) {
				return $amount;
			}
			$currency = $shopCurrencyData['alphabetic_code'];
		}


		// -----------

		// return $amountFormattedAsCurrencyForShop;
		// TODO DELETE BELOW WHEN DONE; WE NOW USE THE MONEY CLASS!

		// NOTE @UPDATE: WE NEED THE LOCALE, E.G. 'en_GB' and not the country code!
		// @note: $shopCurrencyData['country_code'] is the COUNTRY CODE, e.g. 'CA'
		// @note: $shopCurrencyData['alphabetic_code'] is the 3-letter ISO 4217 currency code indicating the currency to use.
		// create new number formatter instance
		// $numberFormatter = new \NumberFormatter($shopCurrencyData['country_code'], \NumberFormatter::CURRENCY);
		$locale = $this->getShopCurrencyLocale()['locale_code'];
		// $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
		$numberFormatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

		// enforce to float for NumberFormatter::formatCurrency for PHP 8+
		$value = floatval($amount);
		// $valueFormattedAsCurrencyForShop = $numberFormatter->formatCurrency($value, $shopCurrencyData['alphabetic_code']);
		$valueFormattedAsCurrencyForShop = $numberFormatter->formatCurrency($value, $currency);


		// TODO @NOTE: DOES NOT WORK! FOR NOW WILL USE Intl.NumberFormat (https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/NumberFormat) - LOOKS LIKE WE CAN USE IT WITHOUT THE DOUBLE CODE, E.G. 'de-DE'. One or the other works fine.
		// $symbol = $numberFormatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);

		return $valueFormattedAsCurrencyForShop;
	}

	/**
	 * Get the shop's currency data according to its saved shop currency value.
	 *
	 * @access public
	 * @return array $shopCurrencyData Array with shop currency data or empty if shop currency not yet saved.
	 */
	public function getShopCurrencyData() {
		$shopCurrencyData = [];
		$generalSettings = $this->getShopGeneralSettings();
		// GET SHOP'S CURRENCY COUNTRY CODE
		// @note: to avoid confusion between shared currencies like the euro...
		// we store the 'shop currency' as the country code
		// TODO: FOR SWITZERLAND, WE WILL NEED TO SORT OUT SINCE 3 CURRENCIES BUT WE STILL STORE THE ONE COUNTRY CODE! i.e. CH
		$shopCurrencyCountryCode = !empty($generalSettings->shop_currency) ? $generalSettings->shop_currency : null;

		// if no shop currency saved yet, just return empty shop currency data
		if (empty($shopCurrencyCountryCode)) {
			return $shopCurrencyData;
		}
		// ---------------------
		// GOOD TO GO
		// GET ALL CURRENCIES
		$currencies = $this->getPWCommerceCurrenciesClass();
		// GET CURRENCY DATA MATCHING SHOP'S CURRENCY
		$shopCurrencyData = $currencies->getCountryCurrencyByCountryCode($shopCurrencyCountryCode);

		return $shopCurrencyData;
	}

	/**
	 * Get the shop's currency locale according to saved shop currency format value.
	 *
	 * This determines decimal points and thousands separator in currency formating.
	 * Format is generally 'language-id-COUNTRY-ID', e.g. 'en-GB'
	 * Done via JavaScript.
	 *
	 * @access public
	 * @return string $shopCurrencyFormat String that is shop currency locale/format.
	 */
	public function getShopCurrencyLocale() {
		$generalSettings = $this->getShopGeneralSettings();
		$shopCurrencyLocale = null;
		// GET SHOP'S CURRENCY LOCAL CODE
		$shopCurrencyLocaleCode = !empty($generalSettings->shop_currency_format) ? $generalSettings->shop_currency_format : null;
		// ------------
		// GET SHOP'S CURRENCIES FULL LOCALE
		if (!empty($shopCurrencyLocaleCode)) {
			$currencies = $this->getPWCommerceCurrenciesClass();
			// GET CURRENCY LOCALE DATA MATCHING SHOP'S CURRENCY LOCALE CODE
			$shopCurrencyLocale = $currencies->getLocaleByLocaleCode($shopCurrencyLocaleCode);
		}
		// ---------

		return $shopCurrencyLocale;
	}

	/**
	 * Convert floats with non "." decimal points to use "." decimal point according to locale.
	 *
	 * For use in price and percentage fields.
	 * HTML5 number input type requires "." as the decimal
	 * @credits: Borrowed from ProcessWire InputfieldFloat
	 * @param float|string $value
	 * @return string|float Returns string representation of float when value was converted
	 *
	 */
	public function localeConvertValue($value) {

		// no value, return early
		if (!strlen("$value"))
			return $value;
		// ok value
		if (ctype_digit(str_replace('.', '', ltrim($value, '-'))))
			return $value;
		// -------
		// process decimal point
		$locale = localeconv();

		$decimal = $locale['decimal_point'];

		if ($decimal === '.' || strpos($value, $decimal) === false)
			return $value;
		$parts = explode($decimal, $value, 2);

		$value = implode('.', $parts);

		return $value;
	}


	# *************

	/**
	 * Return amount in cents using bcmul of BC Math extension.
	 *
	 * @param float $amount
	 * @return int amount in cents with correct precision applied.
	 */
	public function getAmountInCents($amount) {
		$amountMoney = $this->money($amount);
		$amount = (int) $amountMoney->getAmount();
		return $amount;
	}

	private function getPWCommerceCurrenciesClass() {
		$currencies = $this->getPWCommerceClassByName('PWCommerceCurrencies');
		return $currencies;
	}

}