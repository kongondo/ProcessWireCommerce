<?php

namespace ProcessWire;

/**
 * PWCommerce: Currencies
 *
 * Class to deal with currencies for PWCommerce general use.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceCurrencies for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

class PWCommerceCurrencies extends WireData
{

	/**
	 * Get Currencies.
	 *
	 * @return mixed
	 */
	public function getCurrencies() {
		$currencies = [
			0 =>
				[
					'country' => 'Afghanistan',
					'currency' => 'Afghani',
					'alphabetic_code' => 'AFN',
					'numeric_code' => '971',
					'minor_unit' => '2',
					'country_code' => 'AF',
					'locale_codes' =>
						[
							'prs-AF' =>
								[
									'locale_code' => 'prs-AF',
									'currency_symbol' => '؋',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => '٬',
								],
							'ps-AF' =>
								[
									'locale_code' => 'ps-AF',
									'currency_symbol' => 'AFN',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
							'fa-AF' =>
								[
									'locale_code' => 'fa-AF',
									'currency_symbol' => '؋',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => '٬',
								],
						],
				],
			1 =>
				[
					'country' => 'Åland Islands',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'AX',
					'locale_codes' =>
						[
							'sv-AX' =>
								[
									'locale_code' => 'sv-AX',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			2 =>
				[
					'country' => 'Albania',
					'currency' => 'Lek',
					'alphabetic_code' => 'ALL',
					'numeric_code' => '8',
					'minor_unit' => '2',
					'country_code' => 'AL',
					'locale_codes' =>
						[
							'sq-AL' =>
								[
									'locale_code' => 'sq-AL',
									'currency_symbol' => 'ALL',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
							'sq-MK' =>
								[
									'locale_code' => 'sq-MK',
									'currency_symbol' => 'MKD',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			3 =>
				[
					'country' => 'Algeria',
					'currency' => 'Algerian Dinar',
					'alphabetic_code' => 'DZD',
					'numeric_code' => '12',
					'minor_unit' => '2',
					'country_code' => 'DZ',
					'locale_codes' =>
						[
							'ar-DZ' =>
								[
									'locale_code' => 'ar-DZ',
									'currency_symbol' => 'د.ج.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'fr-DZ' =>
								[
									'locale_code' => 'fr-DZ',
									'currency_symbol' => 'DA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
							'kab-DZ' =>
								[
									'locale_code' => 'kab-DZ',
									'currency_symbol' => 'DZD',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			4 =>
				[
					'country' => 'American Samoa',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'AS',
					'locale_codes' =>
						[
							'en-AS' =>
								[
									'locale_code' => 'en-AS',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			5 =>
				[
					'country' => 'Andorra',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'AD',
					'locale_codes' =>
						[
							'ca-AD' =>
								[
									'locale_code' => 'ca-AD',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			6 =>
				[
					'country' => 'Angola',
					'currency' => 'Kwanza',
					'alphabetic_code' => 'AOA',
					'numeric_code' => '973',
					'minor_unit' => '2',
					'country_code' => 'AO',
					'locale_codes' =>
						[
							'pt-AO' =>
								[
									'locale_code' => 'pt-AO',
									'currency_symbol' => 'Kz',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
							'ln-AO' =>
								[
									'locale_code' => 'ln-AO',
									'currency_symbol' => 'AOA',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			7 =>
				[
					'country' => 'Anguilla',
					'currency' => 'East Caribbean Dollar',
					'alphabetic_code' => 'XCD',
					'numeric_code' => '951',
					'minor_unit' => '2',
					'country_code' => 'AI',
					'locale_codes' =>
						[
							'en-AI' =>
								[
									'locale_code' => 'en-AI',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			8 =>
				[
					'country' => 'Antarctica',
					'currency' => 'No universal currency',
					'alphabetic_code' => '',
					'numeric_code' => '',
					'minor_unit' => '',
					'country_code' => 'AQ',
					'locale_codes' =>
						[],
				],
			9 =>
				[
					'country' => 'Antigua and Barbuda',
					'currency' => 'East Caribbean Dollar',
					'alphabetic_code' => 'XCD',
					'numeric_code' => '951',
					'minor_unit' => '2',
					'country_code' => 'AG',
					'locale_codes' =>
						[
							'en-AG' =>
								[
									'locale_code' => 'en-AG',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			10 =>
				[
					'country' => 'Argentina',
					'currency' => 'Argentine Peso',
					'alphabetic_code' => 'ARS',
					'numeric_code' => '32',
					'minor_unit' => '2',
					'country_code' => 'AR',
					'locale_codes' =>
						[
							'es-AR' =>
								[
									'locale_code' => 'es-AR',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			11 =>
				[
					'country' => 'Armenia',
					'currency' => 'Armenian Dram',
					'alphabetic_code' => 'AMD',
					'numeric_code' => '51',
					'minor_unit' => '2',
					'country_code' => 'AM',
					'locale_codes' =>
						[
							'hy-AM' =>
								[
									'locale_code' => 'hy-AM',
									'currency_symbol' => 'AMD',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			12 =>
				[
					'country' => 'Aruba',
					'currency' => 'Aruban Florin',
					'alphabetic_code' => 'AWG',
					'numeric_code' => '533',
					'minor_unit' => '2',
					'country_code' => 'AW',
					'locale_codes' =>
						[
							'nl-AW' =>
								[
									'locale_code' => 'nl-AW',
									'currency_symbol' => 'Afl.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			13 =>
				[
					'country' => 'Australia',
					'currency' => 'Australian Dollar',
					'alphabetic_code' => 'AUD',
					'numeric_code' => '36',
					'minor_unit' => '2',
					'country_code' => 'AU',
					'locale_codes' =>
						[
							'en-AU' =>
								[
									'locale_code' => 'en-AU',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			14 =>
				[
					'country' => 'Austria',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'AT',
					'locale_codes' =>
						[
							'de-AT' =>
								[
									'locale_code' => 'de-AT',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			15 =>
				[
					'country' => 'Azerbaijan',
					'currency' => 'Azerbaijan Manat',
					'alphabetic_code' => 'AZN',
					'numeric_code' => '944',
					'minor_unit' => '2',
					'country_code' => 'AZ',
					'locale_codes' =>
						[
							'az' =>
								[
									'locale_code' => 'az',
									'currency_symbol' => 'AZN',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'az-Cyrl' =>
								[
									'locale_code' => 'az-Cyrl',
									'currency_symbol' => 'AZN',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'az-Cyrl-AZ' =>
								[
									'locale_code' => 'az-Cyrl-AZ',
									'currency_symbol' => 'AZN',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			16 =>
				[
					'country' => 'Bahamas',
					'currency' => 'Bahamian Dollar',
					'alphabetic_code' => 'BSD',
					'numeric_code' => '44',
					'minor_unit' => '2',
					'country_code' => 'BS',
					'locale_codes' =>
						[
							'en-BS' =>
								[
									'locale_code' => 'en-BS',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			17 =>
				[
					'country' => 'Bahrain',
					'currency' => 'Bahraini Dinar',
					'alphabetic_code' => 'BHD',
					'numeric_code' => '48',
					'minor_unit' => '3',
					'country_code' => 'BH',
					'locale_codes' =>
						[
							'ar-BH' =>
								[
									'locale_code' => 'ar-BH',
									'currency_symbol' => 'د.ب.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '٫',
									'thousand_separator' => '٬',
								],
						],
				],
			18 =>
				[
					'country' => 'Bangladesh',
					'currency' => 'Taka',
					'alphabetic_code' => 'BDT',
					'numeric_code' => '50',
					'minor_unit' => '2',
					'country_code' => 'BD',
					'locale_codes' =>
						[
							'bn-BD' =>
								[
									'locale_code' => 'bn-BD',
									'currency_symbol' => '৳',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			19 =>
				[
					'country' => 'Barbados',
					'currency' => 'Barbados Dollar',
					'alphabetic_code' => 'BBD',
					'numeric_code' => '52',
					'minor_unit' => '2',
					'country_code' => 'BB',
					'locale_codes' =>
						[
							'en-BB' =>
								[
									'locale_code' => 'en-BB',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			20 =>
				[
					'country' => 'Belarus',
					'currency' => 'Belarusian Ruble',
					'alphabetic_code' => 'BYN',
					'numeric_code' => '933',
					'minor_unit' => '2',
					'country_code' => 'BY',
					'locale_codes' =>
						[
							'be-BY' =>
								[
									'locale_code' => 'be-BY',
									'currency_symbol' => 'BYN',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'ru-BY' =>
								[
									'locale_code' => 'ru-BY',
									'currency_symbol' => 'Br',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			21 =>
				[
					'country' => 'Belgium',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'BE',
					'locale_codes' =>
						[
							'nl-BE' =>
								[
									'locale_code' => 'nl-BE',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'en-BE' =>
								[
									'locale_code' => 'en-BE',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'fr-BE' =>
								[
									'locale_code' => 'fr-BE',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
							'de-BE' =>
								[
									'locale_code' => 'de-BE',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			22 =>
				[
					'country' => 'Belize',
					'currency' => 'Belize Dollar',
					'alphabetic_code' => 'BZD',
					'numeric_code' => '84',
					'minor_unit' => '2',
					'country_code' => 'BZ',
					'locale_codes' =>
						[
							'es-BZ' =>
								[
									'locale_code' => 'es-BZ',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'en-BZ' =>
								[
									'locale_code' => 'en-BZ',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			23 =>
				[
					'country' => 'Benin',
					'currency' => 'CFA Franc BCEAO',
					'alphabetic_code' => 'XOF',
					'numeric_code' => '952',
					'minor_unit' => '',
					'country_code' => 'BJ',
					'locale_codes' =>
						[
							'fr-BJ' =>
								[
									'locale_code' => 'fr-BJ',
									'currency_symbol' => 'F CFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			24 =>
				[
					'country' => 'Bermuda',
					'currency' => 'Bermudian Dollar',
					'alphabetic_code' => 'BMD',
					'numeric_code' => '60',
					'minor_unit' => '2',
					'country_code' => 'BM',
					'locale_codes' =>
						[
							'en-BM' =>
								[
									'locale_code' => 'en-BM',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			25 =>
				[
					'country' => 'Bhutan',
					'currency' => 'Indian Rupee',
					'alphabetic_code' => 'INR',
					'numeric_code' => '356',
					'minor_unit' => '2',
					'country_code' => 'BT',
					'locale_codes' =>
						[
							'dz-BT' =>
								[
									'locale_code' => 'dz-BT',
									'currency_symbol' => 'BTN',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			26 =>
				[
					'country' => 'Bhutan',
					'currency' => 'Ngultrum',
					'alphabetic_code' => 'BTN',
					'numeric_code' => '64',
					'minor_unit' => '2',
					'country_code' => 'BT',
					'locale_codes' =>
						[
							'dz-BT' =>
								[
									'locale_code' => 'dz-BT',
									'currency_symbol' => 'BTN',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			27 =>
				[
					'country' => 'Bolivia',
					'currency' => 'Boliviano',
					'alphabetic_code' => 'BOB',
					'numeric_code' => '68',
					'minor_unit' => '2',
					'country_code' => 'BO',
					'locale_codes' =>
						[
							'es-BO' =>
								[
									'locale_code' => 'es-BO',
									'currency_symbol' => 'Bs',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'quz-BO' =>
								[
									'locale_code' => 'quz-BO',
									'currency_symbol' => 'BOB',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			28 =>
				[
					'country' => 'Bonaire, Saint Eustatius and Saba',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'BQ',
					'locale_codes' =>
						[
							'nl-BQ' =>
								[
									'locale_code' => 'nl-BQ',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			29 =>
				[
					'country' => 'Bosnia and Herzegovina',
					'currency' => 'Convertible Mark',
					'alphabetic_code' => 'BAM',
					'numeric_code' => '977',
					'minor_unit' => '2',
					'country_code' => 'BA',
					'locale_codes' =>
						[
							'bs' =>
								[
									'locale_code' => 'bs',
									'currency_symbol' => 'BAM',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'bs-Cyrl' =>
								[
									'locale_code' => 'bs-Cyrl',
									'currency_symbol' => 'BAM',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'bs-Cyrl-BA' =>
								[
									'locale_code' => 'bs-Cyrl-BA',
									'currency_symbol' => 'BAM',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			30 =>
				[
					'country' => 'Botswana',
					'currency' => 'Pula',
					'alphabetic_code' => 'BWP',
					'numeric_code' => '72',
					'minor_unit' => '2',
					'country_code' => 'BW',
					'locale_codes' =>
						[
							'en-BW' =>
								[
									'locale_code' => 'en-BW',
									'currency_symbol' => 'P',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'tn-BW' =>
								[
									'locale_code' => 'tn-BW',
									'currency_symbol' => 'BWP',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			31 =>
				[
					'country' => 'Bouvet Island',
					'currency' => 'Norwegian Krone',
					'alphabetic_code' => 'NOK',
					'numeric_code' => '578',
					'minor_unit' => '2',
					'country_code' => 'BV',
					'locale_codes' =>
						[
							'nb-NO' =>
								[
									'locale_code' => 'nb-NO',
									'currency_symbol' => 'kr',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
							'nn-NO' =>
								[
									'locale_code' => 'nn-NO',
									'currency_symbol' => 'NOK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'smj-NO' =>
								[
									'locale_code' => 'smj-NO',
									'currency_symbol' => 'NOK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'se-NO' =>
								[
									'locale_code' => 'se-NO',
									'currency_symbol' => 'NOK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'sma-NO' =>
								[
									'locale_code' => 'sma-NO',
									'currency_symbol' => 'NOK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			32 =>
				[
					'country' => 'Brazil',
					'currency' => 'Brazilian Real',
					'alphabetic_code' => 'BRL',
					'numeric_code' => '986',
					'minor_unit' => '2',
					'country_code' => 'BR',
					'locale_codes' =>
						[
							'pt-BR' =>
								[
									'locale_code' => 'pt-BR',
									'currency_symbol' => 'R$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'es-BR' =>
								[
									'locale_code' => 'es-BR',
									'currency_symbol' => 'R$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			33 =>
				[
					'country' => 'British Indian Ocean Territory',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'IO',
					'locale_codes' =>
						[
							'en-IO' =>
								[
									'locale_code' => 'en-IO',
									'currency_symbol' => 'US$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			34 =>
				[
					'country' => 'Brunei',
					'currency' => 'Brunei Dollar',
					'alphabetic_code' => 'BND',
					'numeric_code' => '96',
					'minor_unit' => '2',
					'country_code' => 'BN',
					'locale_codes' =>
						[
							'ms-BN' =>
								[
									'locale_code' => 'ms-BN',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			35 =>
				[
					'country' => 'Bulgaria',
					'currency' => 'Bulgarian Lev',
					'alphabetic_code' => 'BGN',
					'numeric_code' => '975',
					'minor_unit' => '2',
					'country_code' => 'BG',
					'locale_codes' =>
						[
							'bg-BG' =>
								[
									'locale_code' => 'bg-BG',
									'currency_symbol' => 'лв.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => 'none',
								],
						],
				],
			36 =>
				[
					'country' => 'Burkina Faso',
					'currency' => 'CFA Franc BCEAO',
					'alphabetic_code' => 'XOF',
					'numeric_code' => '952',
					'minor_unit' => '',
					'country_code' => 'BF',
					'locale_codes' =>
						[
							'fr-BF' =>
								[
									'locale_code' => 'fr-BF',
									'currency_symbol' => 'F CFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
							'ff-Latn-BF' =>
								[
									'locale_code' => 'ff-Latn-BF',
									'currency_symbol' => 'F CFA',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			37 =>
				[
					'country' => 'Burundi',
					'currency' => 'Burundi Franc',
					'alphabetic_code' => 'BIF',
					'numeric_code' => '108',
					'minor_unit' => '',
					'country_code' => 'BI',
					'locale_codes' =>
						[
							'fr-BI' =>
								[
									'locale_code' => 'fr-BI',
									'currency_symbol' => 'FBu',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
							'en-BI' =>
								[
									'locale_code' => 'en-BI',
									'currency_symbol' => 'FBu',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
							'rn-BI' =>
								[
									'locale_code' => 'rn-BI',
									'currency_symbol' => 'BIF',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			38 =>
				[
					'country' => 'Cape Verde',
					'currency' => 'Cabo Verde Escudo',
					'alphabetic_code' => 'CVE',
					'numeric_code' => '132',
					'minor_unit' => '2',
					'country_code' => 'CV',
					'locale_codes' =>
						[
							'pt-CV' =>
								[
									'locale_code' => 'pt-CV',
									'currency_symbol' => '​',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '$',
									'thousand_separator' => 'none',
								],
						],
				],
			39 =>
				[
					'country' => 'Cambodia',
					'currency' => 'Riel',
					'alphabetic_code' => 'KHR',
					'numeric_code' => '116',
					'minor_unit' => '2',
					'country_code' => 'KH',
					'locale_codes' =>
						[
							'km-KH' =>
								[
									'locale_code' => 'km-KH',
									'currency_symbol' => 'KHR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			40 =>
				[
					'country' => 'Cameroon',
					'currency' => 'CFA Franc BEAC',
					'alphabetic_code' => 'XAF',
					'numeric_code' => '950',
					'minor_unit' => '',
					'country_code' => 'CM',
					'locale_codes' =>
						[
							'fr-CM' =>
								[
									'locale_code' => 'fr-CM',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
							'en-CM' =>
								[
									'locale_code' => 'en-CM',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			41 =>
				[
					'country' => 'Canada',
					'currency' => 'Canadian Dollar',
					'alphabetic_code' => 'CAD',
					'numeric_code' => '124',
					'minor_unit' => '2',
					'country_code' => 'CA',
					'locale_codes' =>
						[
							'en-CA' =>
								[
									'locale_code' => 'en-CA',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'fr-CA' =>
								[
									'locale_code' => 'fr-CA',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			42 =>
				[
					'country' => 'Cayman Islands',
					'currency' => 'Cayman Islands Dollar',
					'alphabetic_code' => 'KYD',
					'numeric_code' => '136',
					'minor_unit' => '2',
					'country_code' => 'KY',
					'locale_codes' =>
						[
							'en-KY' =>
								[
									'locale_code' => 'en-KY',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			43 =>
				[
					'country' => 'Central African Republic',
					'currency' => 'CFA Franc BEAC',
					'alphabetic_code' => 'XAF',
					'numeric_code' => '950',
					'minor_unit' => '',
					'country_code' => 'CF',
					'locale_codes' =>
						[
							'fr-CF' =>
								[
									'locale_code' => 'fr-CF',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
							'ln-CF' =>
								[
									'locale_code' => 'ln-CF',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			44 =>
				[
					'country' => 'Chad',
					'currency' => 'CFA Franc BEAC',
					'alphabetic_code' => 'XAF',
					'numeric_code' => '950',
					'minor_unit' => '',
					'country_code' => 'TD',
					'locale_codes' =>
						[
							'fr-TD' =>
								[
									'locale_code' => 'fr-TD',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
							'ar-TD' =>
								[
									'locale_code' => 'ar-TD',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => '٬',
								],
						],
				],
			45 =>
				[
					'country' => 'Chile',
					'currency' => 'Chilean Peso',
					'alphabetic_code' => 'CLP',
					'numeric_code' => '152',
					'minor_unit' => '',
					'country_code' => 'CL',
					'locale_codes' =>
						[
							'es-CL' =>
								[
									'locale_code' => 'es-CL',
									'currency_symbol' => 'CLF',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'arn-CL' =>
								[
									'locale_code' => 'arn-CL',
									'currency_symbol' => 'CLF',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			46 =>
				[
					'country' => 'Chile',
					'currency' => 'Unidad de Fomento',
					'alphabetic_code' => 'CLF',
					'numeric_code' => '990',
					'minor_unit' => '4',
					'country_code' => 'CL',
					'locale_codes' =>
						[
							'es-CL' =>
								[
									'locale_code' => 'es-CL',
									'currency_symbol' => 'CLF',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'arn-CL' =>
								[
									'locale_code' => 'arn-CL',
									'currency_symbol' => 'CLF',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			47 =>
				[
					'country' => 'China',
					'currency' => 'Yuan Renminbi',
					'alphabetic_code' => 'CNY',
					'numeric_code' => '156',
					'minor_unit' => '2',
					'country_code' => 'CN',
					'locale_codes' =>
						[
							'zh-CN' =>
								[
									'locale_code' => 'zh-CN',
									'currency_symbol' => '¥',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'mn-Mong-CN' =>
								[
									'locale_code' => 'mn-Mong-CN',
									'currency_symbol' => 'CN¥',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'bo-CN' =>
								[
									'locale_code' => 'bo-CN',
									'currency_symbol' => 'CN¥',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'ug-CN' =>
								[
									'locale_code' => 'ug-CN',
									'currency_symbol' => 'CN¥',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			48 =>
				[
					'country' => 'Christmas Island',
					'currency' => 'Australian Dollar',
					'alphabetic_code' => 'AUD',
					'numeric_code' => '36',
					'minor_unit' => '2',
					'country_code' => 'CX',
					'locale_codes' =>
						[
							'en-CX' =>
								[
									'locale_code' => 'en-CX',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			49 =>
				[
					'country' => 'Cocos (Keeling) Islands',
					'currency' => 'Australian Dollar',
					'alphabetic_code' => 'AUD',
					'numeric_code' => '36',
					'minor_unit' => '2',
					'country_code' => 'CC',
					'locale_codes' =>
						[
							'en-CC' =>
								[
									'locale_code' => 'en-CC',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			50 =>
				[
					'country' => 'Colombia',
					'currency' => 'Colombian Peso',
					'alphabetic_code' => 'COP',
					'numeric_code' => '170',
					'minor_unit' => '2',
					'country_code' => 'CO',
					'locale_codes' =>
						[
							'es-CO' =>
								[
									'locale_code' => 'es-CO',
									'currency_symbol' => 'COU',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			51 =>
				[
					'country' => 'Colombia',
					'currency' => 'Unidad de Valor Real',
					'alphabetic_code' => 'COU',
					'numeric_code' => '970',
					'minor_unit' => '2',
					'country_code' => 'CO',
					'locale_codes' =>
						[
							'es-CO' =>
								[
									'locale_code' => 'es-CO',
									'currency_symbol' => 'COU',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			52 =>
				[
					'country' => 'Comoros',
					'currency' => 'Comorian Franc ',
					'alphabetic_code' => 'KMF',
					'numeric_code' => '174',
					'minor_unit' => '',
					'country_code' => 'KM',
					'locale_codes' =>
						[
							'fr-KM' =>
								[
									'locale_code' => 'fr-KM',
									'currency_symbol' => 'CF',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
							'ar-KM' =>
								[
									'locale_code' => 'ar-KM',
									'currency_symbol' => 'CF',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => '٬',
								],
						],
				],
			53 =>
				[
					'country' => 'Congo (The Democratic Republic of the)',
					'currency' => 'Congolese Franc',
					'alphabetic_code' => 'CDF',
					'numeric_code' => '976',
					'minor_unit' => '2',
					'country_code' => 'CD',
					'locale_codes' =>
						[
							'fr-CD' =>
								[
									'locale_code' => 'fr-CD',
									'currency_symbol' => 'FC',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
							'ln-CD' =>
								[
									'locale_code' => 'ln-CD',
									'currency_symbol' => 'CDF',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'lu-CD' =>
								[
									'locale_code' => 'lu-CD',
									'currency_symbol' => 'CDF',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			54 =>
				[
					'country' => 'Congo (Brazzaville)',
					'currency' => 'CFA Franc BEAC',
					'alphabetic_code' => 'XAF',
					'numeric_code' => '950',
					'minor_unit' => '',
					'country_code' => 'CG',
					'locale_codes' =>
						[
							'fr-CG' =>
								[
									'locale_code' => 'fr-CG',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
							'ln-CG' =>
								[
									'locale_code' => 'ln-CG',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			55 =>
				[
					'country' => 'Cook Islands',
					'currency' => 'New Zealand Dollar',
					'alphabetic_code' => 'NZD',
					'numeric_code' => '554',
					'minor_unit' => '2',
					'country_code' => 'CK',
					'locale_codes' =>
						[
							'en-CK' =>
								[
									'locale_code' => 'en-CK',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			56 =>
				[
					'country' => 'Costa Rica',
					'currency' => 'Costa Rican Colon',
					'alphabetic_code' => 'CRC',
					'numeric_code' => '188',
					'minor_unit' => '2',
					'country_code' => 'CR',
					'locale_codes' =>
						[
							'es-CR' =>
								[
									'locale_code' => 'es-CR',
									'currency_symbol' => '₡',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			57 =>
				[
					'country' => 'Ivory Coast',
					'currency' => 'CFA Franc BCEAO',
					'alphabetic_code' => 'XOF',
					'numeric_code' => '952',
					'minor_unit' => '',
					'country_code' => 'CI',
					'locale_codes' =>
						[
							'fr-CI' =>
								[
									'locale_code' => 'fr-CI',
									'currency_symbol' => 'F CFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			58 =>
				[
					'country' => 'Croatia',
					'currency' => 'Kuna',
					'alphabetic_code' => 'HRK',
					'numeric_code' => '191',
					'minor_unit' => '2',
					'country_code' => 'HR',
					'locale_codes' =>
						[
							'hr-HR' =>
								[
									'locale_code' => 'hr-HR',
									'currency_symbol' => 'kn',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			59 =>
				[
					'country' => 'Cuba',
					'currency' => 'Cuban Peso',
					'alphabetic_code' => 'CUP',
					'numeric_code' => '192',
					'minor_unit' => '2',
					'country_code' => 'CU',
					'locale_codes' =>
						[
							'es-CU' =>
								[
									'locale_code' => 'es-CU',
									'currency_symbol' => 'CUC',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			60 =>
				[
					'country' => 'Cuba',
					'currency' => 'Peso Convertible',
					'alphabetic_code' => 'CUC',
					'numeric_code' => '931',
					'minor_unit' => '2',
					'country_code' => 'CU',
					'locale_codes' =>
						[
							'es-CU' =>
								[
									'locale_code' => 'es-CU',
									'currency_symbol' => 'CUC',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			61 =>
				[
					'country' => 'Curaçao',
					'currency' => 'Netherlands Antillean Guilder',
					'alphabetic_code' => 'ANG',
					'numeric_code' => '532',
					'minor_unit' => '2',
					'country_code' => 'CW',
					'locale_codes' =>
						[
							'nl-CW' =>
								[
									'locale_code' => 'nl-CW',
									'currency_symbol' => 'NAf.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			62 =>
				[
					'country' => 'Cyprus',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'CY',
					'locale_codes' =>
						[
							'el-CY' =>
								[
									'locale_code' => 'el-CY',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'tr-CY' =>
								[
									'locale_code' => 'tr-CY',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'en-CY' =>
								[
									'locale_code' => 'en-CY',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			63 =>
				[
					'country' => 'Czech Republic',
					'currency' => 'Czech Koruna',
					'alphabetic_code' => 'CZK',
					'numeric_code' => '203',
					'minor_unit' => '2',
					'country_code' => 'CZ',
					'locale_codes' =>
						[
							'cs-CZ' =>
								[
									'locale_code' => 'cs-CZ',
									'currency_symbol' => 'Kč',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			64 =>
				[
					'country' => 'Denmark',
					'currency' => 'Danish Krone',
					'alphabetic_code' => 'DKK',
					'numeric_code' => '208',
					'minor_unit' => '2',
					'country_code' => 'DK',
					'locale_codes' =>
						[
							'da-DK' =>
								[
									'locale_code' => 'da-DK',
									'currency_symbol' => 'kr.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			65 =>
				[
					'country' => 'Djibouti',
					'currency' => 'Djibouti Franc',
					'alphabetic_code' => 'DJF',
					'numeric_code' => '262',
					'minor_unit' => '',
					'country_code' => 'DJ',
					'locale_codes' =>
						[
							'fr-DJ' =>
								[
									'locale_code' => 'fr-DJ',
									'currency_symbol' => 'Fdj',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			66 =>
				[
					'country' => 'Dominica',
					'currency' => 'East Caribbean Dollar',
					'alphabetic_code' => 'XCD',
					'numeric_code' => '951',
					'minor_unit' => '2',
					'country_code' => 'DM',
					'locale_codes' =>
						[
							'en-DM' =>
								[
									'locale_code' => 'en-DM',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			67 =>
				[
					'country' => 'Dominican Republic',
					'currency' => 'Dominican Peso',
					'alphabetic_code' => 'DOP',
					'numeric_code' => '214',
					'minor_unit' => '2',
					'country_code' => 'DO',
					'locale_codes' =>
						[
							'es-DO' =>
								[
									'locale_code' => 'es-DO',
									'currency_symbol' => 'RD$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			68 =>
				[
					'country' => 'Ecuador',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'EC',
					'locale_codes' =>
						[
							'es-EC' =>
								[
									'locale_code' => 'es-EC',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'quz-EC' =>
								[
									'locale_code' => 'quz-EC',
									'currency_symbol' => 'US$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			69 =>
				[
					'country' => 'Egypt',
					'currency' => 'Egyptian Pound',
					'alphabetic_code' => 'EGP',
					'numeric_code' => '818',
					'minor_unit' => '2',
					'country_code' => 'EG',
					'locale_codes' =>
						[
							'ar-EG' =>
								[
									'locale_code' => 'ar-EG',
									'currency_symbol' => 'ج.م.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '٫',
									'thousand_separator' => '٬',
								],
						],
				],
			70 =>
				[
					'country' => 'El Salvador',
					'currency' => 'El Salvador Colon',
					'alphabetic_code' => 'SVC',
					'numeric_code' => '222',
					'minor_unit' => '2',
					'country_code' => 'SV',
					'locale_codes' =>
						[
							'es-SV' =>
								[
									'locale_code' => 'es-SV',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			71 =>
				[
					'country' => 'El Salvador',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'SV',
					'locale_codes' =>
						[
							'es-SV' =>
								[
									'locale_code' => 'es-SV',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			72 =>
				[
					'country' => 'Equatorial Guinea',
					'currency' => 'CFA Franc BEAC',
					'alphabetic_code' => 'XAF',
					'numeric_code' => '950',
					'minor_unit' => '',
					'country_code' => 'GQ',
					'locale_codes' =>
						[
							'fr-GQ' =>
								[
									'locale_code' => 'fr-GQ',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
							'pt-GQ' =>
								[
									'locale_code' => 'pt-GQ',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => 'none',
								],
							'es-GQ' =>
								[
									'locale_code' => 'es-GQ',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => 'none',
								],
						],
				],
			73 =>
				[
					'country' => 'Eritrea',
					'currency' => 'Nakfa',
					'alphabetic_code' => 'ERN',
					'numeric_code' => '232',
					'minor_unit' => '2',
					'country_code' => 'ER',
					'locale_codes' =>
						[
							'ar-ER' =>
								[
									'locale_code' => 'ar-ER',
									'currency_symbol' => 'Nfk',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '٫',
									'thousand_separator' => '٬',
								],
							'en-ER' =>
								[
									'locale_code' => 'en-ER',
									'currency_symbol' => 'Nfk',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'ti-ER' =>
								[
									'locale_code' => 'ti-ER',
									'currency_symbol' => 'ERN',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			74 =>
				[
					'country' => 'Estonia',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'EE',
					'locale_codes' =>
						[
							'et-EE' =>
								[
									'locale_code' => 'et-EE',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => 'none',
								],
						],
				],
			75 =>
				[
					'country' => 'Ethiopia',
					'currency' => 'Ethiopian Birr',
					'alphabetic_code' => 'ETB',
					'numeric_code' => '230',
					'minor_unit' => '2',
					'country_code' => 'ET',
					'locale_codes' =>
						[
							'am-ET' =>
								[
									'locale_code' => 'am-ET',
									'currency_symbol' => 'ብር',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			76 =>
				[
					'country' => 'European Union',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					// @note: @custom @kongondo: this is not a real country
					'country_code' => 'EU',
					'locale_codes' => [],
				],
			77 =>
				[
					'country' => 'Falkland Islands',
					'currency' => 'Falkland Islands Pound',
					'alphabetic_code' => 'FKP',
					'numeric_code' => '238',
					'minor_unit' => '2',
					'country_code' => 'FK',
					'locale_codes' =>
						[
							'en-FK' =>
								[
									'locale_code' => 'en-FK',
									'currency_symbol' => '£',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			78 =>
				[
					'country' => 'Faroe Islands',
					'currency' => 'Danish Krone',
					'alphabetic_code' => 'DKK',
					'numeric_code' => '208',
					'minor_unit' => '2',
					'country_code' => 'FO',
					'locale_codes' =>
						[
							'fo-DK' =>
								[
									'locale_code' => 'fo-DK',
									'currency_symbol' => 'DKK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			79 =>
				[
					'country' => 'Fiji',
					'currency' => 'Fiji Dollar',
					'alphabetic_code' => 'FJD',
					'numeric_code' => '242',
					'minor_unit' => '2',
					'country_code' => 'FJ',
					'locale_codes' =>
						[
							'en-FJ' =>
								[
									'locale_code' => 'en-FJ',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			80 =>
				[
					'country' => 'Finland',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'FI',
					'locale_codes' =>
						[
							'fi-FI' =>
								[
									'locale_code' => 'fi-FI',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			81 =>
				[
					'country' => 'France',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'FR',
					'locale_codes' =>
						[
							'fr-FR' =>
								[
									'locale_code' => 'fr-FR',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			82 =>
				[
					'country' => 'French Guiana',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'GF',
					'locale_codes' =>
						[
							'fr-GF' =>
								[
									'locale_code' => 'fr-GF',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			83 =>
				[
					'country' => 'French Polynesia',
					'currency' => 'CFP Franc',
					'alphabetic_code' => 'XPF',
					'numeric_code' => '953',
					'minor_unit' => '',
					'country_code' => 'PF',
					'locale_codes' =>
						[
							'fr-PF' =>
								[
									'locale_code' => 'fr-PF',
									'currency_symbol' => 'FCFP',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			84 =>
				[
					'country' => 'French Southern Territories',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'TF',
					'locale_codes' =>
						[
							'fr-TF' =>
								[
									'locale_code' => 'fr-TF',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			85 =>
				[
					'country' => 'Gabon',
					'currency' => 'CFA Franc BEAC',
					'alphabetic_code' => 'XAF',
					'numeric_code' => '950',
					'minor_unit' => '',
					'country_code' => 'GA',
					'locale_codes' =>
						[
							'fr-GA' =>
								[
									'locale_code' => 'fr-GA',
									'currency_symbol' => 'FCFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			86 =>
				[
					'country' => 'Gambia',
					'currency' => 'Dalasi',
					'alphabetic_code' => 'GMD',
					'numeric_code' => '270',
					'minor_unit' => '2',
					'country_code' => 'GM',
					'locale_codes' =>
						[
							'en-GM' =>
								[
									'locale_code' => 'en-GM',
									'currency_symbol' => 'D',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			87 =>
				[
					'country' => 'Georgia',
					'currency' => 'Lari',
					'alphabetic_code' => 'GEL',
					'numeric_code' => '981',
					'minor_unit' => '2',
					'country_code' => 'GE',
					'locale_codes' =>
						[
							'ka-GE' =>
								[
									'locale_code' => 'ka-GE',
									'currency_symbol' => 'GEL',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			88 =>
				[
					'country' => 'Germany',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'DE',
					'locale_codes' =>
						[
							'de-DE' =>
								[
									'locale_code' => 'de-DE',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			89 =>
				[
					'country' => 'Ghana',
					'currency' => 'Ghana Cedi',
					'alphabetic_code' => 'GHS',
					'numeric_code' => '936',
					'minor_unit' => '2',
					'country_code' => 'GH',
					'locale_codes' =>
						[
							'en-GH' =>
								[
									'locale_code' => 'en-GH',
									'currency_symbol' => 'GH₵',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			90 =>
				[
					'country' => 'Gibraltar',
					'currency' => 'Gibraltar Pound',
					'alphabetic_code' => 'GIP',
					'numeric_code' => '292',
					'minor_unit' => '2',
					'country_code' => 'GI',
					'locale_codes' =>
						[
							'en-GI' =>
								[
									'locale_code' => 'en-GI',
									'currency_symbol' => '£',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			91 =>
				[
					'country' => 'Greece',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'GR',
					'locale_codes' =>
						[
							'el-GR' =>
								[
									'locale_code' => 'el-GR',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			92 =>
				[
					'country' => 'Greenland',
					'currency' => 'Danish Krone',
					'alphabetic_code' => 'DKK',
					'numeric_code' => '208',
					'minor_unit' => '2',
					'country_code' => 'GL',
					'locale_codes' =>
						[
							'da-GL' =>
								[
									'locale_code' => 'da-GL',
									'currency_symbol' => 'kr.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'kl-GL' =>
								[
									'locale_code' => 'kl-GL',
									'currency_symbol' => 'DKK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			93 =>
				[
					'country' => 'Grenada',
					'currency' => 'East Caribbean Dollar',
					'alphabetic_code' => 'XCD',
					'numeric_code' => '951',
					'minor_unit' => '2',
					'country_code' => 'GD',
					'locale_codes' =>
						[
							'en-GD' =>
								[
									'locale_code' => 'en-GD',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			94 =>
				[
					'country' => 'Guadeloupe',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'GP',
					'locale_codes' =>
						[
							'fr-GP' =>
								[
									'locale_code' => 'fr-GP',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			95 =>
				[
					'country' => 'Guam',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'GU',
					'locale_codes' =>
						[
							'en-GU' =>
								[
									'locale_code' => 'en-GU',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			96 =>
				[
					'country' => 'Guatemala',
					'currency' => 'Quetzal',
					'alphabetic_code' => 'GTQ',
					'numeric_code' => '320',
					'minor_unit' => '2',
					'country_code' => 'GT',
					'locale_codes' =>
						[
							'es-GT' =>
								[
									'locale_code' => 'es-GT',
									'currency_symbol' => 'Q',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			97 =>
				[
					'country' => 'Guernsey',
					'currency' => 'Pound Sterling',
					'alphabetic_code' => 'GBP',
					'numeric_code' => '826',
					'minor_unit' => '2',
					'country_code' => 'GG',
					'locale_codes' =>
						[
							'en-GG' =>
								[
									'locale_code' => 'en-GG',
									'currency_symbol' => '£',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			98 =>
				[
					'country' => 'Guinea',
					'currency' => 'Guinean Franc',
					'alphabetic_code' => 'GNF',
					'numeric_code' => '324',
					'minor_unit' => '',
					'country_code' => 'GN',
					'locale_codes' =>
						[
							'fr-GN' =>
								[
									'locale_code' => 'fr-GN',
									'currency_symbol' => 'FG',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			99 =>
				[
					'country' => 'Guinea-Bissau',
					'currency' => 'CFA Franc BCEAO',
					'alphabetic_code' => 'XOF',
					'numeric_code' => '952',
					'minor_unit' => '',
					'country_code' => 'GW',
					'locale_codes' =>
						[
							'pt-GW' =>
								[
									'locale_code' => 'pt-GW',
									'currency_symbol' => 'F CFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => 'none',
								],
						],
				],
			100 =>
				[
					'country' => 'Guyana',
					'currency' => 'Guyana Dollar',
					'alphabetic_code' => 'GYD',
					'numeric_code' => '328',
					'minor_unit' => '2',
					'country_code' => 'GY',
					'locale_codes' =>
						[
							'en-GY' =>
								[
									'locale_code' => 'en-GY',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			101 =>
				[
					'country' => 'Haiti',
					'currency' => 'Gourde',
					'alphabetic_code' => 'HTG',
					'numeric_code' => '332',
					'minor_unit' => '2',
					'country_code' => 'HT',
					'locale_codes' =>
						[
							'fr-HT' =>
								[
									'locale_code' => 'fr-HT',
									'currency_symbol' => '$US',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			102 =>
				[
					'country' => 'Haiti',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'HT',
					'locale_codes' =>
						[
							'fr-HT' =>
								[
									'locale_code' => 'fr-HT',
									'currency_symbol' => '$US',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			103 =>
				[
					'country' => 'Heard Island and McDonald Islands',
					'currency' => 'Australian Dollar',
					'alphabetic_code' => 'AUD',
					'numeric_code' => '36',
					'minor_unit' => '2',
					'country_code' => 'HM',
					'locale_codes' =>
						[
							'en-AU' =>
								[
									'locale_code' => 'en-AU',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			104 =>
				[
					'country' => 'Holy See',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					// @note: @custom @kongondo: this is not a real country
					'country_code' => 'VA',
					'locale_codes' => [],
				],
			105 =>
				[
					'country' => 'Honduras',
					'currency' => 'Lempira',
					'alphabetic_code' => 'HNL',
					'numeric_code' => '340',
					'minor_unit' => '2',
					'country_code' => 'HN',
					'locale_codes' =>
						[
							'es-HN' =>
								[
									'locale_code' => 'es-HN',
									'currency_symbol' => 'L',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			106 =>
				[
					'country' => 'Hong Kong',
					'currency' => 'Hong Kong Dollar',
					'alphabetic_code' => 'HKD',
					'numeric_code' => '344',
					'minor_unit' => '2',
					'country_code' => 'HK',
					'locale_codes' =>
						[
							'en-HK' =>
								[
									'locale_code' => 'en-HK',
									'currency_symbol' => 'HK$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'zh-HK' =>
								[
									'locale_code' => 'zh-HK',
									'currency_symbol' => 'HK$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			107 =>
				[
					'country' => 'Hungary',
					'currency' => 'Forint',
					'alphabetic_code' => 'HUF',
					'numeric_code' => '348',
					'minor_unit' => '2',
					'country_code' => 'HU',
					'locale_codes' =>
						[
							'hu-HU' =>
								[
									'locale_code' => 'hu-HU',
									'currency_symbol' => 'Ft',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			108 =>
				[
					'country' => 'Iceland',
					'currency' => 'Iceland Krona',
					'alphabetic_code' => 'ISK',
					'numeric_code' => '352',
					'minor_unit' => '',
					'country_code' => 'IS',
					'locale_codes' =>
						[
							'is-IS' =>
								[
									'locale_code' => 'is-IS',
									'currency_symbol' => 'ISK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			109 =>
				[
					'country' => 'India',
					'currency' => 'Indian Rupee',
					'alphabetic_code' => 'INR',
					'numeric_code' => '356',
					'minor_unit' => '2',
					'country_code' => 'IN',
					'locale_codes' =>
						[
							'en-IN' =>
								[
									'locale_code' => 'en-IN',
									'currency_symbol' => '₹',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			110 =>
				[
					'country' => 'Indonesia',
					'currency' => 'Rupiah',
					'alphabetic_code' => 'IDR',
					'numeric_code' => '360',
					'minor_unit' => '2',
					'country_code' => 'ID',
					'locale_codes' =>
						[
							'id-ID' =>
								[
									'locale_code' => 'id-ID',
									'currency_symbol' => 'Rp',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			111 =>
				[
					'country' => 'Iran',
					'currency' => 'Iranian Rial',
					'alphabetic_code' => 'IRR',
					'numeric_code' => '364',
					'minor_unit' => '2',
					'country_code' => 'IR',
					'locale_codes' =>
						[
							'fa-IR' =>
								[
									'locale_code' => 'fa-IR',
									'currency_symbol' => 'ریال',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => '٬',
								],
						],
				],
			112 =>
				[
					'country' => 'Iraq',
					'currency' => 'Iraqi Dinar',
					'alphabetic_code' => 'IQD',
					'numeric_code' => '368',
					'minor_unit' => '3',
					'country_code' => 'IQ',
					'locale_codes' =>
						[
							'ar-IQ' =>
								[
									'locale_code' => 'ar-IQ',
									'currency_symbol' => 'د.ع.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => '٬',
								],
						],
				],
			113 =>
				[
					'country' => 'Ireland',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'IE',
					'locale_codes' =>
						[
							'ga-IE' =>
								[
									'locale_code' => 'ga-IE',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'en-IE' =>
								[
									'locale_code' => 'en-IE',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			114 =>
				[
					'country' => 'Isle of Man',
					'currency' => 'Pound Sterling',
					'alphabetic_code' => 'GBP',
					'numeric_code' => '826',
					'minor_unit' => '2',
					'country_code' => 'IM',
					'locale_codes' =>
						[
							'en-IM' =>
								[
									'locale_code' => 'en-IM',
									'currency_symbol' => '£',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			115 =>
				[
					'country' => 'Israel',
					'currency' => 'New Israeli Sheqel',
					'alphabetic_code' => 'ILS',
					'numeric_code' => '376',
					'minor_unit' => '2',
					'country_code' => 'IL',
					'locale_codes' =>
						[
							'he-IL' =>
								[
									'locale_code' => 'he-IL',
									'currency_symbol' => '₪',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			116 =>
				[
					'country' => 'Italy',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'IT',
					'locale_codes' =>
						[
							'it-IT' =>
								[
									'locale_code' => 'it-IT',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			117 =>
				[
					'country' => 'Jamaica',
					'currency' => 'Jamaican Dollar',
					'alphabetic_code' => 'JMD',
					'numeric_code' => '388',
					'minor_unit' => '2',
					'country_code' => 'JM',
					'locale_codes' =>
						[
							'en-JM' =>
								[
									'locale_code' => 'en-JM',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			118 =>
				[
					'country' => 'Japan',
					'currency' => 'Yen',
					'alphabetic_code' => 'JPY',
					'numeric_code' => '392',
					'minor_unit' => '',
					'country_code' => 'JP',
					'locale_codes' =>
						[
							'ja-JP' =>
								[
									'locale_code' => 'ja-JP',
									'currency_symbol' => '￥',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			119 =>
				[
					'country' => 'Jersey',
					'currency' => 'Pound Sterling',
					'alphabetic_code' => 'GBP',
					'numeric_code' => '826',
					'minor_unit' => '2',
					'country_code' => 'JE',
					'locale_codes' =>
						[
							'en-JE' =>
								[
									'locale_code' => 'en-JE',
									'currency_symbol' => '£',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			120 =>
				[
					'country' => 'Jordan',
					'currency' => 'Jordanian Dinar',
					'alphabetic_code' => 'JOD',
					'numeric_code' => '400',
					'minor_unit' => '3',
					'country_code' => 'JO',
					'locale_codes' =>
						[
							'ar-JO' =>
								[
									'locale_code' => 'ar-JO',
									'currency_symbol' => 'د.أ.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '٫',
									'thousand_separator' => '٬',
								],
						],
				],
			121 =>
				[
					'country' => 'Kazakhstan',
					'currency' => 'Tenge',
					'alphabetic_code' => 'KZT',
					'numeric_code' => '398',
					'minor_unit' => '2',
					'country_code' => 'KZ',
					'locale_codes' =>
						[
							'kk-KZ' =>
								[
									'locale_code' => 'kk-KZ',
									'currency_symbol' => 'KZT',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'ru-KZ' =>
								[
									'locale_code' => 'ru-KZ',
									'currency_symbol' => '₸',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			122 =>
				[
					'country' => 'Kenya',
					'currency' => 'Kenyan Shilling',
					'alphabetic_code' => 'KES',
					'numeric_code' => '404',
					'minor_unit' => '2',
					'country_code' => 'KE',
					'locale_codes' =>
						[
							'en-KE' =>
								[
									'locale_code' => 'en-KE',
									'currency_symbol' => 'Ksh',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			123 =>
				[
					'country' => 'Kiribati',
					'currency' => 'Australian Dollar',
					'alphabetic_code' => 'AUD',
					'numeric_code' => '36',
					'minor_unit' => '2',
					'country_code' => 'KI',
					'locale_codes' =>
						[
							'en-KI' =>
								[
									'locale_code' => 'en-KI',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			124 =>
				[
					'country' => 'North Korea',
					'currency' => 'North Korean Won',
					'alphabetic_code' => 'KPW',
					'numeric_code' => '408',
					'minor_unit' => '2',
					'country_code' => 'KP',
					'locale_codes' =>
						[
							'ko-KP' =>
								[
									'locale_code' => 'ko-KP',
									'currency_symbol' => 'KPW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			125 =>
				[
					'country' => 'South Korea',
					'currency' => 'Won',
					'alphabetic_code' => 'KRW',
					'numeric_code' => '410',
					'minor_unit' => '',
					'country_code' => 'KR',
					'locale_codes' =>
						[
							'ko-KR' =>
								[
									'locale_code' => 'ko-KR',
									'currency_symbol' => '₩',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			126 =>
				[
					'country' => 'Kuwait',
					'currency' => 'Kuwaiti Dinar',
					'alphabetic_code' => 'KWD',
					'numeric_code' => '414',
					'minor_unit' => '3',
					'country_code' => 'KW',
					'locale_codes' =>
						[
							'ar-KW' =>
								[
									'locale_code' => 'ar-KW',
									'currency_symbol' => 'د.ك.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '٫',
									'thousand_separator' => '٬',
								],
						],
				],
			127 =>
				[
					'country' => 'Kyrgyzstan',
					'currency' => 'Som',
					'alphabetic_code' => 'KGS',
					'numeric_code' => '417',
					'minor_unit' => '2',
					'country_code' => 'KG',
					'locale_codes' =>
						[
							'ky-KG' =>
								[
									'locale_code' => 'ky-KG',
									'currency_symbol' => 'KGS',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'ru-KG' =>
								[
									'locale_code' => 'ru-KG',
									'currency_symbol' => 'сом',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			128 =>
				[
					'country' => 'Laos',
					'currency' => 'Lao Kip',
					'alphabetic_code' => 'LAK',
					'numeric_code' => '418',
					'minor_unit' => '2',
					'country_code' => 'LA',
					'locale_codes' =>
						[
							'lo-LA' =>
								[
									'locale_code' => 'lo-LA',
									'currency_symbol' => 'LAK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			129 =>
				[
					'country' => 'Latvia',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'LV',
					'locale_codes' =>
						[
							'lv-LV' =>
								[
									'locale_code' => 'lv-LV',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => 'none',
								],
						],
				],
			130 =>
				[
					'country' => 'Lebanon',
					'currency' => 'Lebanese Pound',
					'alphabetic_code' => 'LBP',
					'numeric_code' => '422',
					'minor_unit' => '2',
					'country_code' => 'LB',
					'locale_codes' =>
						[
							'ar-LB' =>
								[
									'locale_code' => 'ar-LB',
									'currency_symbol' => 'ل.ل.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => '٬',
								],
						],
				],
			131 =>
				[
					'country' => 'Lesotho',
					'currency' => 'Loti',
					'alphabetic_code' => 'LSL',
					'numeric_code' => '426',
					'minor_unit' => '2',
					'country_code' => 'LS',
					'locale_codes' =>
						[
							'en-LS' =>
								[
									'locale_code' => 'en-LS',
									'currency_symbol' => 'R',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			132 =>
				[
					'country' => 'Lesotho',
					'currency' => 'Rand',
					'alphabetic_code' => 'ZAR',
					'numeric_code' => '710',
					'minor_unit' => '2',
					'country_code' => 'LS',
					'locale_codes' =>
						[
							'en-LS' =>
								[
									'locale_code' => 'en-LS',
									'currency_symbol' => 'R',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			133 =>
				[
					'country' => 'Liberia',
					'currency' => 'Liberian Dollar',
					'alphabetic_code' => 'LRD',
					'numeric_code' => '430',
					'minor_unit' => '2',
					'country_code' => 'LR',
					'locale_codes' =>
						[
							'en-LR' =>
								[
									'locale_code' => 'en-LR',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			134 =>
				[
					'country' => 'Libya',
					'currency' => 'Libyan Dinar',
					'alphabetic_code' => 'LYD',
					'numeric_code' => '434',
					'minor_unit' => '3',
					'country_code' => 'LY',
					'locale_codes' =>
						[
							'ar-LY' =>
								[
									'locale_code' => 'ar-LY',
									'currency_symbol' => 'د.ل.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			135 =>
				[
					'country' => 'Liechtenstein',
					'currency' => 'Swiss Franc',
					'alphabetic_code' => 'CHF',
					'numeric_code' => '756',
					'minor_unit' => '2',
					'country_code' => 'LI',
					'locale_codes' =>
						[
							'de-LI' =>
								[
									'locale_code' => 'de-LI',
									'currency_symbol' => 'CHF',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => '’',
								],
						],
				],
			136 =>
				[
					'country' => 'Lithuania',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'LT',
					'locale_codes' =>
						[
							'lt-LT' =>
								[
									'locale_code' => 'lt-LT',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			137 =>
				[
					'country' => 'Luxembourg',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'LU',
					'locale_codes' =>
						[
							'fr-LU' =>
								[
									'locale_code' => 'fr-LU',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'de-LU' =>
								[
									'locale_code' => 'de-LU',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'lb-LU' =>
								[
									'locale_code' => 'lb-LU',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			138 =>
				[
					'country' => 'Macau',
					'currency' => 'Pataca',
					'alphabetic_code' => 'MOP',
					'numeric_code' => '446',
					'minor_unit' => '2',
					'country_code' => 'MO',
					'locale_codes' =>
						[
							'zh-MO' =>
								[
									'locale_code' => 'zh-MO',
									'currency_symbol' => 'MOP$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'pt-MO' =>
								[
									'locale_code' => 'pt-MO',
									'currency_symbol' => 'MOP$',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => 'none',
								],
						],
				],
			139 =>
				[
					'country' => 'Macedonia',
					'currency' => 'Denar',
					'alphabetic_code' => 'MKD',
					'numeric_code' => '807',
					'minor_unit' => '2',
					'country_code' => 'MK',
					'locale_codes' =>
						[
							'mk-MK' =>
								[
									'locale_code' => 'mk-MK',
									'currency_symbol' => 'MKD',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'sq-MK' =>
								[
									'locale_code' => 'sq-MK',
									'currency_symbol' => 'MKD',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			140 =>
				[
					'country' => 'Madagascar',
					'currency' => 'Malagasy Ariary',
					'alphabetic_code' => 'MGA',
					'numeric_code' => '969',
					'minor_unit' => '2',
					'country_code' => 'MG',
					'locale_codes' =>
						[
							'fr-MG' =>
								[
									'locale_code' => 'fr-MG',
									'currency_symbol' => 'Ar',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
							'mg-MG' =>
								[
									'locale_code' => 'mg-MG',
									'currency_symbol' => 'MGA',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			141 =>
				[
					'country' => 'Malawi',
					'currency' => 'Malawi Kwacha',
					'alphabetic_code' => 'MWK',
					'numeric_code' => '454',
					'minor_unit' => '2',
					'country_code' => 'MW',
					'locale_codes' =>
						[
							'en-MW' =>
								[
									'locale_code' => 'en-MW',
									'currency_symbol' => 'MK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			142 =>
				[
					'country' => 'Malaysia',
					'currency' => 'Malaysian Ringgit',
					'alphabetic_code' => 'MYR',
					'numeric_code' => '458',
					'minor_unit' => '2',
					'country_code' => 'MY',
					'locale_codes' =>
						[
							'en-MY' =>
								[
									'locale_code' => 'en-MY',
									'currency_symbol' => 'RM',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'ms-MY' =>
								[
									'locale_code' => 'ms-MY',
									'currency_symbol' => 'RM',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			143 =>
				[
					'country' => 'Maldives',
					'currency' => 'Rufiyaa',
					'alphabetic_code' => 'MVR',
					'numeric_code' => '462',
					'minor_unit' => '2',
					'country_code' => 'MV',
					'locale_codes' =>
						[
							'dv-MV' =>
								[
									'locale_code' => 'dv-MV',
									'currency_symbol' => 'MVR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			144 =>
				[
					'country' => 'Mali',
					'currency' => 'CFA Franc BCEAO',
					'alphabetic_code' => 'XOF',
					'numeric_code' => '952',
					'minor_unit' => '',
					'country_code' => 'ML',
					'locale_codes' =>
						[
							'fr-ML' =>
								[
									'locale_code' => 'fr-ML',
									'currency_symbol' => 'F CFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			145 =>
				[
					'country' => 'Malta',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'MT',
					'locale_codes' =>
						[
							'en-MT' =>
								[
									'locale_code' => 'en-MT',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'mt-MT' =>
								[
									'locale_code' => 'mt-MT',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			146 =>
				[
					'country' => 'Marshall Islands',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'MH',
					'locale_codes' =>
						[
							'en-MH' =>
								[
									'locale_code' => 'en-MH',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			147 =>
				[
					'country' => 'Martinique',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'MQ',
					'locale_codes' =>
						[
							'fr-MQ' =>
								[
									'locale_code' => 'fr-MQ',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			148 =>
				[
					'country' => 'Mauritania',
					'currency' => 'Ouguiya',
					'alphabetic_code' => 'MRU',
					'numeric_code' => '929',
					'minor_unit' => '2',
					'country_code' => 'MR',
					'locale_codes' =>
						[
							'fr-MR' =>
								[
									'locale_code' => 'fr-MR',
									'currency_symbol' => 'UM',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
							'ar-MR' =>
								[
									'locale_code' => 'ar-MR',
									'currency_symbol' => 'أ.م.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '٫',
									'thousand_separator' => '٬',
								],
						],
				],
			149 =>
				[
					'country' => 'Mauritius',
					'currency' => 'Mauritius Rupee',
					'alphabetic_code' => 'MUR',
					'numeric_code' => '480',
					'minor_unit' => '2',
					'country_code' => 'MU',
					'locale_codes' =>
						[
							'en-MU' =>
								[
									'locale_code' => 'en-MU',
									'currency_symbol' => 'Rs',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'fr-MU' =>
								[
									'locale_code' => 'fr-MU',
									'currency_symbol' => 'Rs',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			150 =>
				[
					'country' => 'Mayotte',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'YT',
					'locale_codes' =>
						[
							'fr-YT' =>
								[
									'locale_code' => 'fr-YT',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			151 =>
				[
					'country' => 'Mexico',
					'currency' => 'Mexican Peso',
					'alphabetic_code' => 'MXN',
					'numeric_code' => '484',
					'minor_unit' => '2',
					'country_code' => 'MX',
					'locale_codes' =>
						[
							'es-MX' =>
								[
									'locale_code' => 'es-MX',
									'currency_symbol' => 'MXV',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			152 =>
				[
					'country' => 'Mexico',
					'currency' => 'Mexican Unidad de Inversion (UDI)',
					'alphabetic_code' => 'MXV',
					'numeric_code' => '979',
					'minor_unit' => '2',
					'country_code' => 'MX',
					'locale_codes' =>
						[
							'es-MX' =>
								[
									'locale_code' => 'es-MX',
									'currency_symbol' => 'MXV',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			153 =>
				[
					'country' => 'Micronesia',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'FM',
					'locale_codes' =>
						[
							'en-FM' =>
								[
									'locale_code' => 'en-FM',
									'currency_symbol' => 'US$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			154 =>
				[
					'country' => 'Moldova',
					'currency' => 'Moldovan Leu',
					'alphabetic_code' => 'MDL',
					'numeric_code' => '498',
					'minor_unit' => '2',
					'country_code' => 'MD',
					'locale_codes' =>
						[
							'ro-MD' =>
								[
									'locale_code' => 'ro-MD',
									'currency_symbol' => 'L',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'ru-MD' =>
								[
									'locale_code' => 'ru-MD',
									'currency_symbol' => 'L',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			155 =>
				[
					'country' => 'Monaco',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'MC',
					'locale_codes' =>
						[
							'fr-MC' =>
								[
									'locale_code' => 'fr-MC',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			156 =>
				[
					'country' => 'Mongolia',
					'currency' => 'Tugrik',
					'alphabetic_code' => 'MNT',
					'numeric_code' => '496',
					'minor_unit' => '2',
					'country_code' => 'MN',
					'locale_codes' =>
						[
							'mn-MN' =>
								[
									'locale_code' => 'mn-MN',
									'currency_symbol' => 'MNT',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			157 =>
				[
					'country' => 'Montenegro',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'ME',
					'locale_codes' =>
						[
							'sr-Cyrl-ME' =>
								[
									'locale_code' => 'sr-Cyrl-ME',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			158 =>
				[
					'country' => 'Montserrat',
					'currency' => 'East Caribbean Dollar',
					'alphabetic_code' => 'XCD',
					'numeric_code' => '951',
					'minor_unit' => '2',
					'country_code' => 'MS',
					'locale_codes' =>
						[
							'en-MS' =>
								[
									'locale_code' => 'en-MS',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			159 =>
				[
					'country' => 'Morocco',
					'currency' => 'Moroccan Dirham',
					'alphabetic_code' => 'MAD',
					'numeric_code' => '504',
					'minor_unit' => '2',
					'country_code' => 'MA',
					'locale_codes' =>
						[
							'fr-MA' =>
								[
									'locale_code' => 'fr-MA',
									'currency_symbol' => 'MAD',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
							'ar-MA' =>
								[
									'locale_code' => 'ar-MA',
									'currency_symbol' => 'د.م.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			160 =>
				[
					'country' => 'Mozambique',
					'currency' => 'Mozambique Metical',
					'alphabetic_code' => 'MZN',
					'numeric_code' => '943',
					'minor_unit' => '2',
					'country_code' => 'MZ',
					'locale_codes' =>
						[
							'pt-MZ' =>
								[
									'locale_code' => 'pt-MZ',
									'currency_symbol' => 'MTn',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => 'none',
								],
						],
				],
			161 =>
				[
					'country' => 'Myanmar',
					'currency' => 'Kyat',
					'alphabetic_code' => 'MMK',
					'numeric_code' => '104',
					'minor_unit' => '2',
					'country_code' => 'MM',
					'locale_codes' =>
						[
							'my-MM' =>
								[
									'locale_code' => 'my-MM',
									'currency_symbol' => 'MMK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			162 =>
				[
					'country' => 'Namibia',
					'currency' => 'Namibia Dollar',
					'alphabetic_code' => 'NAD',
					'numeric_code' => '516',
					'minor_unit' => '2',
					'country_code' => 'NA',
					'locale_codes' =>
						[
							'en-NA' =>
								[
									'locale_code' => 'en-NA',
									'currency_symbol' => 'ZAR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'af-NA' =>
								[
									'locale_code' => 'af-NA',
									'currency_symbol' => 'ZAR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			163 =>
				[
					'country' => 'Namibia',
					'currency' => 'Rand',
					'alphabetic_code' => 'ZAR',
					'numeric_code' => '710',
					'minor_unit' => '2',
					'country_code' => 'NA',
					'locale_codes' =>
						[
							'en-NA' =>
								[
									'locale_code' => 'en-NA',
									'currency_symbol' => 'ZAR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'af-NA' =>
								[
									'locale_code' => 'af-NA',
									'currency_symbol' => 'ZAR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			164 =>
				[
					'country' => 'Nauru',
					'currency' => 'Australian Dollar',
					'alphabetic_code' => 'AUD',
					'numeric_code' => '36',
					'minor_unit' => '2',
					'country_code' => 'NR',
					'locale_codes' =>
						[
							'en-NR' =>
								[
									'locale_code' => 'en-NR',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			165 =>
				[
					'country' => 'Nepal',
					'currency' => 'Nepalese Rupee',
					'alphabetic_code' => 'NPR',
					'numeric_code' => '524',
					'minor_unit' => '2',
					'country_code' => 'NP',
					'locale_codes' =>
						[
							'ne-NP' =>
								[
									'locale_code' => 'ne-NP',
									'currency_symbol' => 'NPR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			166 =>
				[
					'country' => 'Netherlands',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'NL',
					'locale_codes' =>
						[
							'nl-NL' =>
								[
									'locale_code' => 'nl-NL',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			167 =>
				[
					'country' => 'New Caledonia',
					'currency' => 'CFP Franc',
					'alphabetic_code' => 'XPF',
					'numeric_code' => '953',
					'minor_unit' => '',
					'country_code' => 'NC',
					'locale_codes' =>
						[
							'fr-NC' =>
								[
									'locale_code' => 'fr-NC',
									'currency_symbol' => 'FCFP',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			168 =>
				[
					'country' => 'New Zealand',
					'currency' => 'New Zealand Dollar',
					'alphabetic_code' => 'NZD',
					'numeric_code' => '554',
					'minor_unit' => '2',
					'country_code' => 'NZ',
					'locale_codes' =>
						[
							'en-NZ' =>
								[
									'locale_code' => 'en-NZ',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			169 =>
				[
					'country' => 'Nicaragua',
					'currency' => 'Cordoba Oro',
					'alphabetic_code' => 'NIO',
					'numeric_code' => '558',
					'minor_unit' => '2',
					'country_code' => 'NI',
					'locale_codes' =>
						[
							'es-NI' =>
								[
									'locale_code' => 'es-NI',
									'currency_symbol' => 'C$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			170 =>
				[
					'country' => 'Niger',
					'currency' => 'CFA Franc BCEAO',
					'alphabetic_code' => 'XOF',
					'numeric_code' => '952',
					'minor_unit' => '',
					'country_code' => 'NE',
					'locale_codes' =>
						[
							'fr-NE' =>
								[
									'locale_code' => 'fr-NE',
									'currency_symbol' => 'F CFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			171 =>
				[
					'country' => 'Nigeria',
					'currency' => 'Naira',
					'alphabetic_code' => 'NGN',
					'numeric_code' => '566',
					'minor_unit' => '2',
					'country_code' => 'NG',
					'locale_codes' =>
						[
							'en-NG' =>
								[
									'locale_code' => 'en-NG',
									'currency_symbol' => '₦',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			172 =>
				[
					'country' => 'Niue',
					'currency' => 'New Zealand Dollar',
					'alphabetic_code' => 'NZD',
					'numeric_code' => '554',
					'minor_unit' => '2',
					'country_code' => 'NU',
					'locale_codes' =>
						[
							'en-NU' =>
								[
									'locale_code' => 'en-NU',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			173 =>
				[
					'country' => 'Norfolk Island',
					'currency' => 'Australian Dollar',
					'alphabetic_code' => 'AUD',
					'numeric_code' => '36',
					'minor_unit' => '2',
					'country_code' => 'NF',
					'locale_codes' =>
						[
							'en-NF' =>
								[
									'locale_code' => 'en-NF',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			174 =>
				[
					'country' => 'Northern Mariana Islands',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'MP',
					'locale_codes' =>
						[
							'en-MP' =>
								[
									'locale_code' => 'en-MP',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			175 =>
				[
					'country' => 'Norway',
					'currency' => 'Norwegian Krone',
					'alphabetic_code' => 'NOK',
					'numeric_code' => '578',
					'minor_unit' => '2',
					'country_code' => 'NO',
					'locale_codes' =>
						[
							'nn-NO' =>
								[
									'locale_code' => 'nn-NO',
									'currency_symbol' => 'NOK',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			176 =>
				[
					'country' => 'Oman',
					'currency' => 'Rial Omani',
					'alphabetic_code' => 'OMR',
					'numeric_code' => '512',
					'minor_unit' => '3',
					'country_code' => 'OM',
					'locale_codes' =>
						[
							'ar-OM' =>
								[
									'locale_code' => 'ar-OM',
									'currency_symbol' => 'ر.ع.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '٫',
									'thousand_separator' => '٬',
								],
						],
				],
			177 =>
				[
					'country' => 'Pakistan',
					'currency' => 'Pakistan Rupee',
					'alphabetic_code' => 'PKR',
					'numeric_code' => '586',
					'minor_unit' => '2',
					'country_code' => 'PK',
					'locale_codes' =>
						[
							'en-PK' =>
								[
									'locale_code' => 'en-PK',
									'currency_symbol' => 'Rs',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'ur-PK' =>
								[
									'locale_code' => 'ur-PK',
									'currency_symbol' => 'PKR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			178 =>
				[
					'country' => 'Palau',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'PW',
					'locale_codes' =>
						[
							'en-PW' =>
								[
									'locale_code' => 'en-PW',
									'currency_symbol' => 'US$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			179 =>
				[
					'country' => 'Palestine',
					'currency' => 'No universal currency',
					'alphabetic_code' => '',
					'numeric_code' => '',
					'minor_unit' => '',
					'country_code' => 'PS',
					'locale_codes' =>
						[],
				],
			180 =>
				[
					'country' => 'Panama',
					'currency' => 'Balboa',
					'alphabetic_code' => 'PAB',
					'numeric_code' => '590',
					'minor_unit' => '2',
					'country_code' => 'PA',
					'locale_codes' =>
						[
							'es-PA' =>
								[
									'locale_code' => 'es-PA',
									'currency_symbol' => 'USD',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			181 =>
				[
					'country' => 'Panama',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'PA',
					'locale_codes' =>
						[
							'es-PA' =>
								[
									'locale_code' => 'es-PA',
									'currency_symbol' => 'USD',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			182 =>
				[
					'country' => 'Papua New Guinea',
					'currency' => 'Kina',
					'alphabetic_code' => 'PGK',
					'numeric_code' => '598',
					'minor_unit' => '2',
					'country_code' => 'PG',
					'locale_codes' =>
						[
							'en-PG' =>
								[
									'locale_code' => 'en-PG',
									'currency_symbol' => 'K',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			183 =>
				[
					'country' => 'Paraguay',
					'currency' => 'Guarani',
					'alphabetic_code' => 'PYG',
					'numeric_code' => '600',
					'minor_unit' => '',
					'country_code' => 'PY',
					'locale_codes' =>
						[
							'es-PY' =>
								[
									'locale_code' => 'es-PY',
									'currency_symbol' => 'Gs.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => '.',
								],
							'gn-PY' =>
								[
									'locale_code' => 'gn-PY',
									'currency_symbol' => 'PYG',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			184 =>
				[
					'country' => 'Peru',
					'currency' => 'Sol',
					'alphabetic_code' => 'PEN',
					'numeric_code' => '604',
					'minor_unit' => '2',
					'country_code' => 'PE',
					'locale_codes' =>
						[
							'es-PE' =>
								[
									'locale_code' => 'es-PE',
									'currency_symbol' => 'S/',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			185 =>
				[
					'country' => 'Philippines',
					'currency' => 'Philippine Peso',
					'alphabetic_code' => 'PHP',
					'numeric_code' => '608',
					'minor_unit' => '2',
					'country_code' => 'PH',
					'locale_codes' =>
						[
							'en-PH' =>
								[
									'locale_code' => 'en-PH',
									'currency_symbol' => '₱',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'fil-PH' =>
								[
									'locale_code' => 'fil-PH',
									'currency_symbol' => '₱',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			186 =>
				[
					'country' => 'Pitcairn',
					'currency' => 'New Zealand Dollar',
					'alphabetic_code' => 'NZD',
					'numeric_code' => '554',
					'minor_unit' => '2',
					'country_code' => 'PN',
					'locale_codes' =>
						[
							'en-PN' =>
								[
									'locale_code' => 'en-PN',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			187 =>
				[
					'country' => 'Poland',
					'currency' => 'Zloty',
					'alphabetic_code' => 'PLN',
					'numeric_code' => '985',
					'minor_unit' => '2',
					'country_code' => 'PL',
					'locale_codes' =>
						[
							'pl-PL' =>
								[
									'locale_code' => 'pl-PL',
									'currency_symbol' => 'zł',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => 'none',
								],
						],
				],
			188 =>
				[
					'country' => 'Portugal',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'PT',
					'locale_codes' =>
						[
							'pt-PT' =>
								[
									'locale_code' => 'pt-PT',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => 'none',
								],
						],
				],
			189 =>
				[
					'country' => 'Puerto Rico',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'PR',
					'locale_codes' =>
						[
							'es-PR' =>
								[
									'locale_code' => 'es-PR',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'en-PR' =>
								[
									'locale_code' => 'en-PR',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			190 =>
				[
					'country' => 'Qatar',
					'currency' => 'Qatari Rial',
					'alphabetic_code' => 'QAR',
					'numeric_code' => '634',
					'minor_unit' => '2',
					'country_code' => 'QA',
					'locale_codes' =>
						[
							'ar-QA' =>
								[
									'locale_code' => 'ar-QA',
									'currency_symbol' => 'ر.ق.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '٫',
									'thousand_separator' => '٬',
								],
						],
				],
			191 =>
				[
					'country' => 'Réunion',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'RE',
					'locale_codes' =>
						[
							'fr-RE' =>
								[
									'locale_code' => 'fr-RE',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			192 =>
				[
					'country' => 'Romania',
					'currency' => 'Romanian Leu',
					'alphabetic_code' => 'RON',
					'numeric_code' => '946',
					'minor_unit' => '2',
					'country_code' => 'RO',
					'locale_codes' =>
						[
							'ro-RO' =>
								[
									'locale_code' => 'ro-RO',
									'currency_symbol' => 'RON',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			193 =>
				[
					'country' => 'Russia',
					'currency' => 'Russian Ruble',
					'alphabetic_code' => 'RUB',
					'numeric_code' => '643',
					'minor_unit' => '2',
					'country_code' => 'RU',
					'locale_codes' =>
						[
							'ru-RU' =>
								[
									'locale_code' => 'ru-RU',
									'currency_symbol' => '₽',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			194 =>
				[
					'country' => 'Rwanda',
					'currency' => 'Rwanda Franc',
					'alphabetic_code' => 'RWF',
					'numeric_code' => '646',
					'minor_unit' => '',
					'country_code' => 'RW',
					'locale_codes' =>
						[
							'fr-RW' =>
								[
									'locale_code' => 'fr-RW',
									'currency_symbol' => 'RF',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			195 =>
				[
					'country' => 'Saint Barthélemy',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'BL',
					'locale_codes' =>
						[
							'fr-BL' =>
								[
									'locale_code' => 'fr-BL',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			196 =>
				[
					'country' => 'Saint Helena, Ascension and Tristan Da Cunha',
					'currency' => 'Saint Helena Pound',
					'alphabetic_code' => 'SHP',
					'numeric_code' => '654',
					'minor_unit' => '2',
					'country_code' => 'SH',
					'locale_codes' =>
						[
							'en-SH' =>
								[
									'locale_code' => 'en-SH',
									'currency_symbol' => '£',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			197 =>
				[
					'country' => 'Saint Kitts and Nevis',
					'currency' => 'East Caribbean Dollar',
					'alphabetic_code' => 'XCD',
					'numeric_code' => '951',
					'minor_unit' => '2',
					'country_code' => 'KN',
					'locale_codes' =>
						[
							'en-KN' =>
								[
									'locale_code' => 'en-KN',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			198 =>
				[
					'country' => 'Saint Lucia',
					'currency' => 'East Caribbean Dollar',
					'alphabetic_code' => 'XCD',
					'numeric_code' => '951',
					'minor_unit' => '2',
					'country_code' => 'LC',
					'locale_codes' =>
						[
							'en-LC' =>
								[
									'locale_code' => 'en-LC',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			199 =>
				[
					'country' => 'Saint Martin (French part)',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'MF',
					'locale_codes' =>
						[
							'fr-MF' =>
								[
									'locale_code' => 'fr-MF',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			200 =>
				[
					'country' => 'Saint Pierre and Miquelon',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'PM',
					'locale_codes' =>
						[
							'fr-PM' =>
								[
									'locale_code' => 'fr-PM',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			201 =>
				[
					'country' => 'Saint Vincent and the Grenadines',
					'currency' => 'East Caribbean Dollar',
					'alphabetic_code' => 'XCD',
					'numeric_code' => '951',
					'minor_unit' => '2',
					'country_code' => 'VC',
					'locale_codes' =>
						[
							'en-VC' =>
								[
									'locale_code' => 'en-VC',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			202 =>
				[
					'country' => 'Samoa',
					'currency' => 'Tala',
					'alphabetic_code' => 'WST',
					'numeric_code' => '882',
					'minor_unit' => '2',
					'country_code' => 'WS',
					'locale_codes' =>
						[
							'en-WS' =>
								[
									'locale_code' => 'en-WS',
									'currency_symbol' => 'WS$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			203 =>
				[
					'country' => 'San Marino',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'SM',
					'locale_codes' =>
						[
							'it-SM' =>
								[
									'locale_code' => 'it-SM',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			204 =>
				[
					'country' => 'São Tomé and Príncipe',
					'currency' => 'Dobra',
					'alphabetic_code' => 'STN',
					'numeric_code' => '930',
					'minor_unit' => '2',
					'country_code' => 'ST',
					'locale_codes' =>
						[
							'pt-ST' =>
								[
									'locale_code' => 'pt-ST',
									'currency_symbol' => 'Db',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => 'none',
								],
						],
				],
			205 =>
				[
					'country' => 'Saudi Arabia',
					'currency' => 'Saudi Riyal',
					'alphabetic_code' => 'SAR',
					'numeric_code' => '682',
					'minor_unit' => '2',
					'country_code' => 'SA',
					'locale_codes' =>
						[
							'ar-SA' =>
								[
									'locale_code' => 'ar-SA',
									'currency_symbol' => 'ر.س.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '٫',
									'thousand_separator' => '٬',
								],
						],
				],
			206 =>
				[
					'country' => 'Senegal',
					'currency' => 'CFA Franc BCEAO',
					'alphabetic_code' => 'XOF',
					'numeric_code' => '952',
					'minor_unit' => '',
					'country_code' => 'SN',
					'locale_codes' =>
						[
							'fr-SN' =>
								[
									'locale_code' => 'fr-SN',
									'currency_symbol' => 'F CFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			207 =>
				[
					'country' => 'Serbia',
					'currency' => 'Serbian Dinar',
					'alphabetic_code' => 'RSD',
					'numeric_code' => '941',
					'minor_unit' => '2',
					'country_code' => 'RS',
					'locale_codes' =>
						[
							'sr-RS' =>
								[
									'locale_code' => 'sr-RS',
									'currency_symbol' => 'RSD',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => '.',
								],
						],
				],
			208 =>
				[
					'country' => 'Seychelles',
					'currency' => 'Seychelles Rupee',
					'alphabetic_code' => 'SCR',
					'numeric_code' => '690',
					'minor_unit' => '2',
					'country_code' => 'SC',
					'locale_codes' =>
						[
							'fr-SC' =>
								[
									'locale_code' => 'fr-SC',
									'currency_symbol' => 'SR',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
							'en-SC' =>
								[
									'locale_code' => 'en-SC',
									'currency_symbol' => 'SR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			209 =>
				[
					'country' => 'Sierra Leone',
					'currency' => 'Leone',
					'alphabetic_code' => 'SLL',
					'numeric_code' => '694',
					'minor_unit' => '2',
					'country_code' => 'SL',
					'locale_codes' =>
						[
							'en-SL' =>
								[
									'locale_code' => 'en-SL',
									'currency_symbol' => 'Le',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			210 =>
				[
					'country' => 'Singapore',
					'currency' => 'Singapore Dollar',
					'alphabetic_code' => 'SGD',
					'numeric_code' => '702',
					'minor_unit' => '2',
					'country_code' => 'SG',
					'locale_codes' =>
						[
							'en-SG' =>
								[
									'locale_code' => 'en-SG',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'ta-SG' =>
								[
									'locale_code' => 'ta-SG',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'zh-SG' =>
								[
									'locale_code' => 'zh-SG',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			211 =>
				[
					'country' => 'Sint Maarten (Dutch part)',
					'currency' => 'Netherlands Antillean Guilder',
					'alphabetic_code' => 'ANG',
					'numeric_code' => '532',
					'minor_unit' => '2',
					'country_code' => 'SX',
					'locale_codes' =>
						[
							'nl-SX' =>
								[
									'locale_code' => 'nl-SX',
									'currency_symbol' => 'NAf.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			212 =>
				[
					'country' => 'Sistema Unitario De Compensacion Regional De Pagos \'sucre\'',
					'currency' => 'Sucre',
					'alphabetic_code' => 'XSU',
					'numeric_code' => '994',
					'minor_unit' => 'N.A.',
					// @note: @custom @kongondo: this is not a real country
					// @kongondo: we skip it for now??
					'country_code' => 'XS',
					'locale_codes' => [],
				],
			213 =>
				[
					'country' => 'Slovakia',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'SK',
					'locale_codes' =>
						[
							'sk-SK' =>
								[
									'locale_code' => 'sk-SK',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			214 =>
				[
					'country' => 'Slovenia',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'SI',
					'locale_codes' =>
						[
							'sl-SI' =>
								[
									'locale_code' => 'sl-SI',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			215 =>
				[
					'country' => 'Solomon Islands',
					'currency' => 'Solomon Islands Dollar',
					'alphabetic_code' => 'SBD',
					'numeric_code' => '90',
					'minor_unit' => '2',
					'country_code' => 'SB',
					'locale_codes' =>
						[
							'en-SB' =>
								[
									'locale_code' => 'en-SB',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			216 =>
				[
					'country' => 'Somalia',
					'currency' => 'Somali Shilling',
					'alphabetic_code' => 'SOS',
					'numeric_code' => '706',
					'minor_unit' => '2',
					'country_code' => 'SO',
					'locale_codes' =>
						[
							'so-SO' =>
								[
									'locale_code' => 'so-SO',
									'currency_symbol' => 'SOS',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
							'ar-SO' =>
								[
									'locale_code' => 'ar-SO',
									'currency_symbol' => 'S',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => '٬',
								],
						],
				],
			217 =>
				[
					'country' => 'South Africa',
					'currency' => 'Rand',
					'alphabetic_code' => 'ZAR',
					'numeric_code' => '710',
					'minor_unit' => '2',
					'country_code' => 'ZA',
					'locale_codes' =>
						[
							'en-ZA' =>
								[
									'locale_code' => 'en-ZA',
									'currency_symbol' => 'R',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
							'af-ZA' =>
								[
									'locale_code' => 'af-ZA',
									'currency_symbol' => 'ZAR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			218 =>
				[
					'country' => 'South Georgia and the South Sandwich Islands',
					'currency' => 'No universal currency',
					'alphabetic_code' => '',
					'numeric_code' => '',
					'minor_unit' => '',
					'country_code' => 'GS',
					'locale_codes' =>
						[],
				],
			219 =>
				[
					'country' => 'South Sudan',
					'currency' => 'South Sudanese Pound',
					'alphabetic_code' => 'SSP',
					'numeric_code' => '728',
					'minor_unit' => '2',
					'country_code' => 'SS',
					'locale_codes' =>
						[
							'en-SS' =>
								[
									'locale_code' => 'en-SS',
									'currency_symbol' => '£',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			220 =>
				[
					'country' => 'Spain',
					'currency' => 'Euro',
					'alphabetic_code' => 'EUR',
					'numeric_code' => '978',
					'minor_unit' => '2',
					'country_code' => 'ES',
					'locale_codes' =>
						[
							'es-ES' =>
								[
									'locale_code' => 'es-ES',
									'currency_symbol' => '€',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => 'none',
								],
						],
				],
			221 =>
				[
					'country' => 'Sri Lanka',
					'currency' => 'Sri Lanka Rupee',
					'alphabetic_code' => 'LKR',
					'numeric_code' => '144',
					'minor_unit' => '2',
					'country_code' => 'LK',
					'locale_codes' =>
						[
							'si-LK' =>
								[
									'locale_code' => 'si-LK',
									'currency_symbol' => 'LKR',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'ta-LK' =>
								[
									'locale_code' => 'ta-LK',
									'currency_symbol' => 'Rs.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			222 =>
				[
					'country' => 'Sudan',
					'currency' => 'Sudanese Pound',
					'alphabetic_code' => 'SDG',
					'numeric_code' => '938',
					'minor_unit' => '2',
					'country_code' => 'SD',
					'locale_codes' =>
						[
							'ar-SD' =>
								[
									'locale_code' => 'ar-SD',
									'currency_symbol' => 'ج.س.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '٫',
									'thousand_separator' => '٬',
								],
							'en-SD' =>
								[
									'locale_code' => 'en-SD',
									'currency_symbol' => 'SDG',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			223 =>
				[
					'country' => 'Suriname',
					'currency' => 'Surinam Dollar',
					'alphabetic_code' => 'SRD',
					'numeric_code' => '968',
					'minor_unit' => '2',
					'country_code' => 'SR',
					'locale_codes' =>
						[
							'nl-SR' =>
								[
									'locale_code' => 'nl-SR',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			224 =>
				[
					'country' => 'Svalbard and Jan Mayen',
					'currency' => 'Norwegian Krone',
					'alphabetic_code' => 'NOK',
					'numeric_code' => '578',
					'minor_unit' => '2',
					'country_code' => 'SJ',
					'locale_codes' =>
						[
							'nb-SJ' =>
								[
									'locale_code' => 'nb-SJ',
									'currency_symbol' => 'kr',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			225 =>
				[
					'country' => 'Eswatini',
					'currency' => 'Lilangeni',
					'alphabetic_code' => 'SZL',
					'numeric_code' => '748',
					'minor_unit' => '2',
					'country_code' => 'SZ',
					'locale_codes' =>
						[
							'sz-SZ' =>
								[
									'locale_code' => 'sz-SZ',
									'currency_symbol' => 'SZL',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'en-SZ' =>
								[
									'locale_code' => 'en-SZ',
									'currency_symbol' => 'E',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			226 =>
				[
					'country' => 'Sweden',
					'currency' => 'Swedish Krona',
					'alphabetic_code' => 'SEK',
					'numeric_code' => '752',
					'minor_unit' => '2',
					'country_code' => 'SE',
					'locale_codes' =>
						[
							'sv-SE' =>
								[
									'locale_code' => 'sv-SE',
									'currency_symbol' => 'kr',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			227 =>
				[
					'country' => 'Switzerland',
					'currency' => 'Swiss Franc',
					'alphabetic_code' => 'CHF',
					'numeric_code' => '756',
					'minor_unit' => '2',
					'country_code' => 'CH',
					'locale_codes' =>
						[
							'fr-CH' =>
								[
									'locale_code' => 'fr-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '.',
									'thousand_separator' => ' ',
								],
							'de-CH' =>
								[
									'locale_code' => 'de-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => '’',
								],
							'it-CH' =>
								[
									'locale_code' => 'it-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => '’',
								],
							'rm-CH' =>
								[
									'locale_code' => 'rm-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			228 =>
				[
					'country' => 'Switzerland',
					'currency' => 'WIR Euro',
					'alphabetic_code' => 'CHE',
					'numeric_code' => '947',
					'minor_unit' => '2',
					'country_code' => 'CH',
					'locale_codes' =>
						[
							'fr-CH' =>
								[
									'locale_code' => 'fr-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '.',
									'thousand_separator' => ' ',
								],
							'de-CH' =>
								[
									'locale_code' => 'de-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => '’',
								],
							'it-CH' =>
								[
									'locale_code' => 'it-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => '’',
								],
							'rm-CH' =>
								[
									'locale_code' => 'rm-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			229 =>
				[
					'country' => 'Switzerland',
					'currency' => 'WIR Franc',
					'alphabetic_code' => 'CHW',
					'numeric_code' => '948',
					'minor_unit' => '2',
					'country_code' => 'CH',
					'locale_codes' =>
						[
							'fr-CH' =>
								[
									'locale_code' => 'fr-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => '.',
									'thousand_separator' => ' ',
								],
							'de-CH' =>
								[
									'locale_code' => 'de-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => '’',
								],
							'it-CH' =>
								[
									'locale_code' => 'it-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => '’',
								],
							'rm-CH' =>
								[
									'locale_code' => 'rm-CH',
									'currency_symbol' => 'CHW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			230 =>
				[
					'country' => 'Syria',
					'currency' => 'Syrian Pound',
					'alphabetic_code' => 'SYP',
					'numeric_code' => '760',
					'minor_unit' => '2',
					'country_code' => 'SY',
					'locale_codes' =>
						[
							'ar-SY' =>
								[
									'locale_code' => 'ar-SY',
									'currency_symbol' => 'ل.س.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => '٬',
								],
						],
				],
			231 =>
				[
					'country' => 'Taiwan',
					'currency' => 'New Taiwan Dollar',
					'alphabetic_code' => 'TWD',
					'numeric_code' => '901',
					'minor_unit' => '2',
					'country_code' => 'TW',
					'locale_codes' =>
						[
							'zh-TW' =>
								[
									'locale_code' => 'zh-TW',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			232 =>
				[
					'country' => 'Tajikistan',
					'currency' => 'Somoni',
					'alphabetic_code' => 'TJS',
					'numeric_code' => '972',
					'minor_unit' => '2',
					'country_code' => 'TJ',
					'locale_codes' =>
						[
							'tg-Cyrl-TJ' =>
								[
									'locale_code' => 'tg-Cyrl-TJ',
									'currency_symbol' => 'TJS',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			233 =>
				[
					'country' => 'Tanzania',
					'currency' => 'Tanzanian Shilling',
					'alphabetic_code' => 'TZS',
					'numeric_code' => '834',
					'minor_unit' => '2',
					'country_code' => 'TZ',
					'locale_codes' =>
						[
							'en-TZ' =>
								[
									'locale_code' => 'en-TZ',
									'currency_symbol' => 'TSh',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			234 =>
				[
					'country' => 'Thailand',
					'currency' => 'Baht',
					'alphabetic_code' => 'THB',
					'numeric_code' => '764',
					'minor_unit' => '2',
					'country_code' => 'TH',
					'locale_codes' =>
						[
							'th-TH' =>
								[
									'locale_code' => 'th-TH',
									'currency_symbol' => '฿',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			235 =>
				[
					'country' => 'Timor-Leste',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'TL',
					'locale_codes' =>
						[
							'pt-TL' =>
								[
									'locale_code' => 'pt-TL',
									'currency_symbol' => 'US$',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => 'none',
								],
						],
				],
			236 =>
				[
					'country' => 'Togo',
					'currency' => 'CFA Franc BCEAO',
					'alphabetic_code' => 'XOF',
					'numeric_code' => '952',
					'minor_unit' => '',
					'country_code' => 'TG',
					'locale_codes' =>
						[
							'fr-TG' =>
								[
									'locale_code' => 'fr-TG',
									'currency_symbol' => 'F CFA',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			237 =>
				[
					'country' => 'Tokelau',
					'currency' => 'New Zealand Dollar',
					'alphabetic_code' => 'NZD',
					'numeric_code' => '554',
					'minor_unit' => '2',
					'country_code' => 'TK',
					'locale_codes' =>
						[
							'en-TK' =>
								[
									'locale_code' => 'en-TK',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			238 =>
				[
					'country' => 'Tonga',
					'currency' => 'Pa’anga',
					'alphabetic_code' => 'TOP',
					'numeric_code' => '776',
					'minor_unit' => '2',
					'country_code' => 'TO',
					'locale_codes' =>
						[
							'en-TO' =>
								[
									'locale_code' => 'en-TO',
									'currency_symbol' => 'T$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			239 =>
				[
					'country' => 'Trinidad and Tobago',
					'currency' => 'Trinidad and Tobago Dollar',
					'alphabetic_code' => 'TTD',
					'numeric_code' => '780',
					'minor_unit' => '2',
					'country_code' => 'TT',
					'locale_codes' =>
						[
							'en-TT' =>
								[
									'locale_code' => 'en-TT',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			240 =>
				[
					'country' => 'Tunisia',
					'currency' => 'Tunisian Dinar',
					'alphabetic_code' => 'TND',
					'numeric_code' => '788',
					'minor_unit' => '3',
					'country_code' => 'TN',
					'locale_codes' =>
						[
							'fr-TN' =>
								[
									'locale_code' => 'fr-TN',
									'currency_symbol' => 'DT',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
							'ar-TN' =>
								[
									'locale_code' => 'ar-TN',
									'currency_symbol' => 'د.ت.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			241 =>
				[
					'country' => 'Turkey',
					'currency' => 'Turkish Lira',
					'alphabetic_code' => 'TRY',
					'numeric_code' => '949',
					'minor_unit' => '2',
					'country_code' => 'TR',
					'locale_codes' =>
						[
							'tr-TR' =>
								[
									'locale_code' => 'tr-TR',
									'currency_symbol' => '₺',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			242 =>
				[
					'country' => 'Turkmenistan',
					'currency' => 'Turkmenistan New Manat',
					'alphabetic_code' => 'TMT',
					'numeric_code' => '934',
					'minor_unit' => '2',
					'country_code' => 'TM',
					'locale_codes' =>
						[
							'tk-TM' =>
								[
									'locale_code' => 'tk-TM',
									'currency_symbol' => 'TMT',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			243 =>
				[
					'country' => 'Turks and Caicos Islands',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'TC',
					'locale_codes' =>
						[
							'en-TC' =>
								[
									'locale_code' => 'en-TC',
									'currency_symbol' => 'US$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			244 =>
				[
					'country' => 'Tuvalu',
					'currency' => 'Australian Dollar',
					'alphabetic_code' => 'AUD',
					'numeric_code' => '36',
					'minor_unit' => '2',
					'country_code' => 'TV',
					'locale_codes' =>
						[
							'en-TV' =>
								[
									'locale_code' => 'en-TV',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			245 =>
				[
					'country' => 'Uganda',
					'currency' => 'Uganda Shilling',
					'alphabetic_code' => 'UGX',
					'numeric_code' => '800',
					'minor_unit' => '',
					'country_code' => 'UG',
					'locale_codes' =>
						[
							'en-UG' =>
								[
									'locale_code' => 'en-UG',
									'currency_symbol' => 'USh',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
						],
				],
			246 =>
				[
					'country' => 'Ukraine',
					'currency' => 'Hryvnia',
					'alphabetic_code' => 'UAH',
					'numeric_code' => '980',
					'minor_unit' => '2',
					'country_code' => 'UA',
					'locale_codes' =>
						[
							'uk-UA' =>
								[
									'locale_code' => 'uk-UA',
									'currency_symbol' => 'грн',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			247 =>
				[
					'country' => 'United Arab Emirates',
					'currency' => 'UAE Dirham',
					'alphabetic_code' => 'AED',
					'numeric_code' => '784',
					'minor_unit' => '2',
					'country_code' => 'AE',
					'locale_codes' =>
						[
							'ar-AE' =>
								[
									'locale_code' => 'ar-AE',
									'currency_symbol' => 'د.إ.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			248 =>
				[
					'country' => 'United Kingdom',
					'currency' => 'Pound Sterling',
					'alphabetic_code' => 'GBP',
					'numeric_code' => '826',
					'minor_unit' => '2',
					'country_code' => 'GB',
					'locale_codes' =>
						[
							'en-GB' =>
								[
									'locale_code' => 'en-GB',
									'currency_symbol' => '£',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			249 =>
				[
					'country' => 'United States Minor Outlying Islands',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'UM',
					'locale_codes' =>
						[
							'en-UM' =>
								[
									'locale_code' => 'en-UM',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			250 =>
				[
					'country' => 'United States of America',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'US',
					'locale_codes' =>
						[
							'en-US' =>
								[
									'locale_code' => 'en-US',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			251 =>
				[
					'country' => 'Uruguay',
					'currency' => 'Peso Uruguayo',
					'alphabetic_code' => 'UYU',
					'numeric_code' => '858',
					'minor_unit' => '2',
					'country_code' => 'UY',
					'locale_codes' =>
						[
							'es-UY' =>
								[
									'locale_code' => 'es-UY',
									'currency_symbol' => 'UYW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			252 =>
				[
					'country' => 'Uruguay',
					'currency' => 'Uruguay Peso en Unidades Indexadas (UI)',
					'alphabetic_code' => 'UYI',
					'numeric_code' => '940',
					'minor_unit' => '',
					'country_code' => 'UY',
					'locale_codes' =>
						[
							'es-UY' =>
								[
									'locale_code' => 'es-UY',
									'currency_symbol' => 'UYW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			253 =>
				[
					'country' => 'Uruguay',
					'currency' => 'Unidad Previsional',
					'alphabetic_code' => 'UYW',
					'numeric_code' => '927',
					'minor_unit' => '4',
					'country_code' => 'UY',
					'locale_codes' =>
						[
							'es-UY' =>
								[
									'locale_code' => 'es-UY',
									'currency_symbol' => 'UYW',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			254 =>
				[
					'country' => 'Uzbekistan',
					'currency' => 'Uzbekistan Sum',
					'alphabetic_code' => 'UZS',
					'numeric_code' => '860',
					'minor_unit' => '2',
					'country_code' => 'UZ',
					'locale_codes' =>
						[
							'uz-UZ' =>
								[
									'locale_code' => 'uz-UZ',
									'currency_symbol' => 'UZS',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
							'uz-Cyrl-UZ' =>
								[
									'locale_code' => 'uz-Cyrl-UZ',
									'currency_symbol' => 'UZS',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => ',',
									'thousand_separator' => ' ',
								],
						],
				],
			255 =>
				[
					'country' => 'Vanuatu',
					'currency' => 'Vatu',
					'alphabetic_code' => 'VUV',
					'numeric_code' => '548',
					'minor_unit' => '',
					'country_code' => 'VU',
					'locale_codes' =>
						[
							'en-VU' =>
								[
									'locale_code' => 'en-VU',
									'currency_symbol' => 'VT',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => 'none',
									'thousand_separator' => ',',
								],
							'fr-VU' =>
								[
									'locale_code' => 'fr-VU',
									'currency_symbol' => 'VT',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			256 =>
				[
					'country' => 'Venezuela',
					'currency' => 'Bolívar Soberano',
					'alphabetic_code' => 'VES',
					'numeric_code' => '928',
					'minor_unit' => '2',
					'country_code' => 'VE',
					'locale_codes' =>
						[
							'es-VE' =>
								[
									'locale_code' => 'es-VE',
									'currency_symbol' => 'Bs.S',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => ',',
									'thousand_separator' => '.',
								],
						],
				],
			257 =>
				[
					'country' => 'Vietnam',
					'currency' => 'Dong',
					'alphabetic_code' => 'VND',
					'numeric_code' => '704',
					'minor_unit' => '',
					'country_code' => 'VN',
					'locale_codes' =>
						[
							'vi-VN' =>
								[
									'locale_code' => 'vi-VN',
									'currency_symbol' => '₫',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => '.',
								],
						],
				],
			258 =>
				[
					'country' => 'British Virgin Islands',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'VG',
					'locale_codes' =>
						[
							'en-VG' =>
								[
									'locale_code' => 'en-VG',
									'currency_symbol' => 'US$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			259 =>
				[
					'country' => 'U.S. Virgin Islands',
					'currency' => 'US Dollar',
					'alphabetic_code' => 'USD',
					'numeric_code' => '840',
					'minor_unit' => '2',
					'country_code' => 'VI',
					'locale_codes' =>
						[
							'en-VI' =>
								[
									'locale_code' => 'en-VI',
									'currency_symbol' => '$',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			260 =>
				[
					'country' => 'Wallis and Futuna',
					'currency' => 'CFP Franc',
					'alphabetic_code' => 'XPF',
					'numeric_code' => '953',
					'minor_unit' => '',
					'country_code' => 'WF',
					'locale_codes' =>
						[
							'fr-WF' =>
								[
									'locale_code' => 'fr-WF',
									'currency_symbol' => 'FCFP',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => ' ',
								],
						],
				],
			261 =>
				[
					'country' => 'Western Sahara',
					'currency' => 'Moroccan Dirham',
					'alphabetic_code' => 'MAD',
					'numeric_code' => '504',
					'minor_unit' => '2',
					'country_code' => 'EH',
					'locale_codes' =>
						[
							'ar-EH' =>
								[
									'locale_code' => 'ar-EH',
									'currency_symbol' => 'د.م.',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			262 =>
				[
					'country' => 'Yemen',
					'currency' => 'Yemeni Rial',
					'alphabetic_code' => 'YER',
					'numeric_code' => '886',
					'minor_unit' => '2',
					'country_code' => 'YE',
					'locale_codes' =>
						[
							'ar-YE' =>
								[
									'locale_code' => 'ar-YE',
									'currency_symbol' => 'ر.ي.',
									'currency_symbol_placement' => 'last',
									'decimal_separator' => 'none',
									'thousand_separator' => '٬',
								],
						],
				],
			263 =>
				[
					'country' => 'Zambia',
					'currency' => 'Zambian Kwacha',
					'alphabetic_code' => 'ZMW',
					'numeric_code' => '967',
					'minor_unit' => '2',
					'country_code' => 'ZM',
					'locale_codes' =>
						[
							'en-ZM' =>
								[
									'locale_code' => 'en-ZM',
									'currency_symbol' => 'K',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
			264 =>
				[
					'country' => 'Zimbabwe',
					'currency' => 'Zimbabwe Dollar',
					'alphabetic_code' => 'ZWL',
					'numeric_code' => '932',
					'minor_unit' => '2',
					'country_code' => 'ZW',
					'locale_codes' =>
						[
							'en-ZW' =>
								[
									'locale_code' => 'en-ZW',
									'currency_symbol' => 'ZWL',
									'currency_symbol_placement' => 'first',
									'decimal_separator' => '.',
									'thousand_separator' => ',',
								],
						],
				],
		];
		// ------
		return $currencies;
	}

	/**
	 * Get Locale Codes.
	 *
	 * @return mixed
	 */
	public function getLocaleCodes() {

		$currencies = $this->getCurrencies();
		$localeCodes = [];
		foreach ($currencies as $currency) {
			if (empty($currency['locale_codes']))
				continue;
			foreach ($currency['locale_codes'] as $localeCode => $localeCodeIinfo) {
				$localeCodes[$localeCode] = $localeCodeIinfo;
			}
		}
		return $localeCodes;
	}

	/**
	 * Get Country Currency By Country Code.
	 *
	 * @param mixed $countryCode
	 * @return mixed
	 */
	public function getCountryCurrencyByCountryCode($countryCode) {
		$foundCountryCurrency = null;
		if (empty($countryCode)) {
			return $foundCountryCurrency;
		}
		//----------
		$currencies = $this->getCurrencies();
		foreach ($currencies as $currency) {
			if (\strtolower($currency['country_code']) === \strtolower($countryCode)) {
				$foundCountryCurrency = $currency;
				break;
			}
		}
		return $foundCountryCurrency;
	}

	/**
	 * Get Country Currency By Country Name.
	 *
	 * @param mixed $countryName
	 * @return mixed
	 */
	public function getCountryCurrencyByCountryName($countryName) {
		$foundCountryCurrency = null;
		if (empty($countryName)) {
			return $foundCountryCurrency;
		}
		//----------
		$currencies = $this->getCurrencies();
		foreach ($currencies as $currency) {
			if (\strtolower($currency['country']) === \strtolower($countryName)) {
				$foundCountryCurrency = $currency;
				break;
			}
		}
		return $foundCountryCurrency;
	}

	/**
	 * Get Locale By Locale Code.
	 *
	 * @param mixed $localeCode
	 * @return mixed
	 */
	public function getLocaleByLocaleCode($localeCode) {
		$foundLocaleCode = null;
		if (empty($localeCode)) {
			return $foundLocaleCode;
		}
		//----------
		$localeCodes = $this->getLocaleCodes();
		foreach ($localeCodes as $lcid => $localeCodeIinfo) {
			if (\strtolower($localeCodeIinfo['locale_code']) === \strtolower($localeCode)) {
				$foundLocaleCode = $localeCodeIinfo;
				break;
			}
		}
		return $foundLocaleCode;
	}
}
