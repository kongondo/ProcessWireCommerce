<?php

namespace ProcessWire;

/**
 * PWCommerce: Countries
 *
 * Class to deal with Countries for PWCommerce general use.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceCountries for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

class PWCommerceCountries extends WireData
{

	/**
	 * Get all world countries.
	 *
	 * Data are continent, id (ISO), name and territories reference if applicable.
	 *
	 * @access public
	 * @return array $countries Array with countries data.
	 */
	public function getCountries() {
		$countries = [
			// AFRICA
			[
				"continent" => "AFR",
				"id" => "AO",
				"name" => $this->_("Angola"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "AFR",
				"id" => "BF",
				"name" => $this->_("Burkina Faso"),
			],
			[
				"continent" => "AFR",
				"id" => "BI",
				"name" => $this->_("Burundi"),
			],
			[
				"continent" => "AFR",
				"id" => "BJ",
				"name" => $this->_("Benin"),
			],
			[
				"continent" => "AFR",
				"id" => "BW",
				"name" => $this->_("Botswana"),
			],
			[
				"continent" => "AFR",
				"id" => "CD",
				//  "name" => $this->_("Congo (Kinshasa)"),
				"name" => $this->_("Congo (The Democratic Republic of the)"),
			],
			[
				"continent" => "AFR",
				"id" => "CF",
				"name" => $this->_("Central African Republic"),
			],
			[
				"continent" => "AFR",
				"id" => "CG",
				"name" => $this->_("Congo (Brazzaville)"),
			],
			[
				"continent" => "AFR",
				"id" => "CI",
				"name" => $this->_("Ivory Coast"),
			],
			[
				"continent" => "AFR",
				"id" => "CM",
				"name" => $this->_("Cameroon"),
			],
			[
				"continent" => "AFR",
				"id" => "CV",
				"name" => $this->_("Cape Verde"),
			],
			[
				"continent" => "AFR",
				"id" => "DJ",
				"name" => $this->_("Djibouti"),
			],
			[
				"continent" => "AFR",
				"id" => "DZ",
				"name" => $this->_("Algeria"),
			],
			[
				"continent" => "AFR",
				"id" => "EG",
				"name" => $this->_("Egypt"),
			],
			[
				"continent" => "AFR",
				"id" => "EH",
				"name" => $this->_("Western Sahara"),
			],
			[
				"continent" => "AFR",
				"id" => "ER",
				"name" => $this->_("Eritrea"),
			],
			[
				"continent" => "AFR",
				"id" => "ET",
				"name" => $this->_("Ethiopia"),
			],
			[
				"continent" => "AFR",
				"id" => "GA",
				"name" => $this->_("Gabon"),
			],
			[
				"continent" => "AFR",
				"id" => "GH",
				"name" => $this->_("Ghana"),
			],
			[
				"continent" => "AFR",
				"id" => "GM",
				"name" => $this->_("Gambia"),
			],
			[
				"continent" => "AFR",
				"id" => "GN",
				"name" => $this->_("Guinea"),
			],
			[
				"continent" => "AFR",
				"id" => "GQ",
				"name" => $this->_("Equatorial Guinea"),
			],
			[
				"continent" => "AFR",
				"id" => "GW",
				"name" => $this->_("Guinea-Bissau"),
			],
			[
				"continent" => "AFR",
				"id" => "KE",
				"name" => $this->_("Kenya"),
			],
			[
				"continent" => "AFR",
				"id" => "KM",
				"name" => $this->_("Comoros"),
			],
			[
				"continent" => "AFR",
				"id" => "LR",
				"name" => $this->_("Liberia"),
				"territories_reference" => $this->_("Provinces"),
			],
			[
				"continent" => "AFR",
				"id" => "LS",
				"name" => $this->_("Lesotho"),
			],
			[
				"continent" => "AFR",
				"id" => "LY",
				"name" => $this->_("Libya"),
			],
			[
				"continent" => "AFR",
				"id" => "MA",
				"name" => $this->_("Morocco"),
			],
			[
				"continent" => "AFR",
				"id" => "MG",
				"name" => $this->_("Madagascar"),
			],
			[
				"continent" => "AFR",
				"id" => "ML",
				"name" => $this->_("Mali"),
			],
			[
				"continent" => "AFR",
				"id" => "MR",
				"name" => $this->_("Mauritania"),
			],
			[
				"continent" => "AFR",
				"id" => "MU",
				"name" => $this->_("Mauritius"),
			],
			[
				"continent" => "AFR",
				"id" => "MW",
				"name" => $this->_("Malawi"),
			],
			[
				"continent" => "AFR",
				"id" => "MZ",
				"name" => $this->_("Mozambique"),
			],
			[
				"continent" => "AFR",
				"id" => "NA",
				"name" => $this->_("Namibia"),
			],
			[
				"continent" => "AFR",
				"id" => "NE",
				"name" => $this->_("Niger"),
			],
			[
				"continent" => "AFR",
				"id" => "NG",
				"name" => $this->_("Nigeria"),
				"territories_reference" => $this->_("Provinces"),
			],
			[
				"continent" => "AFR",
				"id" => "RE",
				//  "name" => $this->_("Reunion"),
				"name" => $this->_("Réunion"),
			],
			[
				"continent" => "AFR",
				"id" => "RW",
				"name" => $this->_("Rwanda"),
			],
			[
				"continent" => "AFR",
				"id" => "SC",
				"name" => $this->_("Seychelles"),
			],
			[
				"continent" => "AFR",
				"id" => "SD",
				"name" => $this->_("Sudan"),
			],
			[
				"continent" => "AFR",
				"id" => "SH",
				//  "name" => $this->_("Saint Helena"),
				"name" => $this->_("Saint Helena, Ascension and Tristan Da Cunha"),
			],
			[
				"continent" => "AFR",
				"id" => "SL",
				"name" => $this->_("Sierra Leone"),
			],
			[
				"continent" => "AFR",
				"id" => "SN",
				"name" => $this->_("Senegal"),
			],
			[
				"continent" => "AFR",
				"id" => "SO",
				"name" => $this->_("Somalia"),
			],
			[
				"continent" => "AFR",
				"id" => "SS",
				"name" => $this->_("South Sudan"),
			],
			[
				"continent" => "AFR",
				"id" => "ST",
				"name" => $this->_("São Tomé and Príncipe"),
			],
			[
				"continent" => "AFR",
				"id" => "SZ",
				//  "name" => $this->_("Swaziland"),
				"name" => $this->_("Eswatini"),
			],
			[
				"continent" => "AFR",
				"id" => "TD",
				"name" => $this->_("Chad"),
			],
			[
				"continent" => "AFR",
				"id" => "TG",
				"name" => $this->_("Togo"),
			],
			[
				"continent" => "AFR",
				"id" => "TN",
				"name" => $this->_("Tunisia"),
			],
			[
				"continent" => "AFR",
				"id" => "TZ",
				"name" => $this->_("Tanzania"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "AFR",
				"id" => "UG",
				"name" => $this->_("Uganda"),
			],
			[
				"continent" => "AFR",
				"id" => "YT",
				"name" => $this->_("Mayotte"),
			],
			[
				"continent" => "AFR",
				"id" => "ZA",
				"name" => $this->_("South Africa"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "AFR",
				"id" => "ZM",
				"name" => $this->_("Zambia"),
			],
			[
				"continent" => "AFR",
				"id" => "ZW",
				"name" => $this->_("Zimbabwe"),
			],
			// ANTARCTICA
			[
				"continent" => "ANT",
				"id" => "AQ",
				"name" => $this->_("Antarctica"),
			],
			[
				"continent" => "ANT",
				"id" => "BV",
				"name" => $this->_("Bouvet Island"),
			],
			[
				"continent" => "ANT",
				"id" => "GS",
				"name" => $this->_("South Georgia and the South Sandwich Islands"),
			],
			[
				"continent" => "ANT",
				"id" => "HM",
				"name" => $this->_("Heard Island and McDonald Islands"),
			],
			[
				"continent" => "ANT",
				"id" => "TF",
				"name" => $this->_("French Southern Territories"),
			],
			// ASIA
			[
				"continent" => "ASI",
				"id" => "AE",
				"name" => $this->_("United Arab Emirates"),
			],
			[
				"continent" => "ASI",
				"id" => "AF",
				"name" => $this->_("Afghanistan"),
			],
			[
				"continent" => "ASI",
				"id" => "AM",
				"name" => $this->_("Armenia"),
			],
			[
				"continent" => "ASI",
				"id" => "AZ",
				"name" => $this->_("Azerbaijan"),
			],
			[
				"continent" => "ASI",
				"id" => "BD",
				"name" => $this->_("Bangladesh"),
				"territories_reference" => $this->_("Districts"),
			],
			[
				"continent" => "ASI",
				"id" => "BH",
				"name" => $this->_("Bahrain"),
			],
			[
				"continent" => "ASI",
				"id" => "BN",
				"name" => $this->_("Brunei"),
			],
			[
				"continent" => "ASI",
				"id" => "BT",
				"name" => $this->_("Bhutan"),
			],
			[
				"continent" => "ASI",
				"id" => "CC",
				"name" => $this->_("Cocos (Keeling) Islands"),
			],
			[
				"continent" => "ASI",
				"id" => "CN",
				"name" => $this->_("China"),
				// TODO: is this a better reference? issue is that not all the regions are provinces, hence we use states
				//  "territories_reference" => "States",
				"territories_reference" => "Regions",
			],
			[
				"continent" => "ASI",
				"id" => "CX",
				"name" => $this->_("Christmas Island"),
			],
			// TODO: WHY CYPRUS IN ASIA AND EU?
			//    [
			//     "continent" => "ASI",
			//     "id" => "CY",
			//     "name" => $this->_("Cyprus"),
			//    ],
			[
				"continent" => "ASI",
				"id" => "GE",
				"name" => $this->_("Georgia"),
			],
			[
				"continent" => "ASI",
				"id" => "HK",
				"name" => $this->_("Hong Kong"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "ASI",
				"id" => "ID",
				"name" => $this->_("Indonesia"),
				"territories_reference" => $this->_("Provinces"),
			],
			[
				"continent" => "ASI",
				"id" => "IL",
				"name" => $this->_("Israel"),
			],
			[
				"continent" => "ASI",
				"id" => "IN",
				"name" => $this->_("India"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "ASI",
				"id" => "IO",
				"name" => $this->_("British Indian Ocean Territory"),
			],
			[
				"continent" => "ASI",
				"id" => "IQ",
				"name" => $this->_("Iraq"),
			],
			[
				"continent" => "ASI",
				"id" => "IR",
				"name" => $this->_("Iran"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "ASI",
				"id" => "JO",
				"name" => $this->_("Jordan"),
			],
			[
				"continent" => "ASI",
				"id" => "JP",
				"name" => $this->_("Japan"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "ASI",
				"id" => "KG",
				"name" => $this->_("Kyrgyzstan"),
			],
			[
				"continent" => "ASI",
				"id" => "KH",
				"name" => $this->_("Cambodia"),
			],
			[
				"continent" => "ASI",
				"id" => "KP",
				"name" => $this->_("North Korea"),
			],
			[
				"continent" => "ASI",
				"id" => "KR",
				"name" => $this->_("South Korea"),
			],
			[
				"continent" => "ASI",
				"id" => "KW",
				"name" => $this->_("Kuwait"),
			],
			[
				"continent" => "ASI",
				"id" => "KZ",
				"name" => $this->_("Kazakhstan"),
			],
			[
				"continent" => "ASI",
				"id" => "LA",
				"name" => $this->_("Laos"),
			],
			[
				"continent" => "ASI",
				"id" => "LB",
				"name" => $this->_("Lebanon"),
			],
			[
				"continent" => "ASI",
				"id" => "LK",
				"name" => $this->_("Sri Lanka"),
			],
			[
				"continent" => "ASI",
				"id" => "MM",
				"name" => $this->_("Myanmar"),
			],
			[
				"continent" => "ASI",
				"id" => "MN",
				"name" => $this->_("Mongolia"),
			],
			[
				"continent" => "ASI",
				"id" => "MO",
				//  "name" => $this->_("Macao S.A.R., China"),
				"name" => $this->_("Macau"),
			],
			[
				"continent" => "ASI",
				"id" => "MV",
				"name" => $this->_("Maldives"),
			],
			[
				"continent" => "ASI",
				"id" => "MY",
				"name" => $this->_("Malaysia"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "ASI",
				"id" => "NP",
				"name" => $this->_("Nepal"),
				"territories_reference" => $this->_("Zones"),
			],
			[
				"continent" => "ASI",
				"id" => "OM",
				"name" => $this->_("Oman"),
			],
			[
				"continent" => "ASI",
				"id" => "PH",
				"name" => $this->_("Philippines"),
				"territories_reference" => $this->_("Provinces"),
			],
			[
				"continent" => "ASI",
				"id" => "PK",
				"name" => $this->_("Pakistan"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "ASI",
				"id" => "PS",
				//  "name" => $this->_("Palestinian Territory"),
				"name" => $this->_("Palestine"),
			],
			[
				"continent" => "ASI",
				"id" => "QA",
				"name" => $this->_("Qatar"),
			],
			[
				"continent" => "ASI",
				"id" => "SA",
				"name" => $this->_("Saudi Arabia"),
			],
			[
				"continent" => "ASI",
				"id" => "SG",
				"name" => $this->_("Singapore"),
			],
			[
				"continent" => "ASI",
				"id" => "SY",
				"name" => $this->_("Syria"),
			],
			[
				"continent" => "ASI",
				"id" => "TH",
				"name" => $this->_("Thailand"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "ASI",
				"id" => "TJ",
				"name" => $this->_("Tajikistan"),
			],
			[
				"continent" => "ASI",
				"id" => "TL",
				"name" => $this->_("Timor-Leste"),
			],
			[
				"continent" => "ASI",
				"id" => "TM",
				"name" => $this->_("Turkmenistan"),
			],
			[
				"continent" => "ASI",
				"id" => "TW",
				"name" => $this->_("Taiwan"),
			],
			[
				"continent" => "ASI",
				"id" => "UZ",
				"name" => $this->_("Uzbekistan"),
			],
			[
				"continent" => "ASI",
				"id" => "VN",
				"name" => $this->_("Vietnam"),
			],
			[
				"continent" => "ASI",
				"id" => "YE",
				"name" => $this->_("Yemen"),
			],
			// EUROPE
			[
				"continent" => "EUR",
				"id" => "AD",
				"name" => $this->_("Andorra"),
			],
			[
				"continent" => "EUR",
				"id" => "AL",
				"name" => $this->_("Albania"),
			],
			[
				"continent" => "EUR",
				"id" => "AT",
				"name" => $this->_("Austria"),
			],
			[
				"continent" => "EUR",
				"id" => "AX",
				"name" => $this->_("Åland Islands"),
			],
			[
				"continent" => "EUR",
				"id" => "BA",
				"name" => $this->_("Bosnia and Herzegovina"),
			],
			[
				"continent" => "EUR",
				"id" => "BE",
				"name" => $this->_("Belgium"),
			],
			[
				"continent" => "EUR",
				"id" => "BG",
				"name" => $this->_("Bulgaria"),
				"territories_reference" => "States",
			],
			[
				"continent" => "EUR",
				"id" => "BY",
				"name" => $this->_("Belarus"),
			],
			[
				"continent" => "EUR",
				"id" => "CH",
				"name" => $this->_("Switzerland"),
				"territories_reference" => $this->_("Cantons"),
			],
			[
				"continent" => "EUR",
				"id" => "CY",
				"name" => $this->_("Cyprus"),
			],
			[
				"continent" => "EUR",
				"id" => "CZ",
				"name" => $this->_("Czech Republic"),
			],
			[
				"continent" => "EUR",
				"id" => "DE",
				"name" => $this->_("Germany"),
			],
			[
				"continent" => "EUR",
				"id" => "DK",
				"name" => $this->_("Denmark"),
			],
			[
				"continent" => "EUR",
				"id" => "EE",
				"name" => $this->_("Estonia"),
			],
			[
				"continent" => "EUR",
				"id" => "ES",
				"name" => $this->_("Spain"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "EUR",
				"id" => "FI",
				"name" => $this->_("Finland"),
			],
			[
				"continent" => "EUR",
				"id" => "FO",
				"name" => $this->_("Faroe Islands"),
			],
			[
				"continent" => "EUR",
				"id" => "FR",
				"name" => $this->_("France"),
			],
			[
				"continent" => "EUR",
				"id" => "GB",
				"name" => $this->_("United Kingdom"),
			],
			[
				"continent" => "EUR",
				"id" => "GG",
				"name" => $this->_("Guernsey"),
			],
			[
				"continent" => "EUR",
				"id" => "GI",
				"name" => $this->_("Gibraltar"),
			],
			[
				"continent" => "EUR",
				"id" => "GR",
				"name" => $this->_("Greece"),
				"territories_reference" => $this->_("Regions"),
			],
			[
				"continent" => "EUR",
				"id" => "HR",
				"name" => $this->_("Croatia"),
			],
			[
				"continent" => "EUR",
				"id" => "HU",
				"name" => $this->_("Hungary"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "EUR",
				"id" => "IE",
				"name" => $this->_("Ireland"),
				// @note: unsure about this reference!
				"territories_reference" => "Regions",
			],
			[
				"continent" => "EUR",
				"id" => "IM",
				"name" => $this->_("Isle of Man"),
			],
			[
				"continent" => "EUR",
				"id" => "IS",
				"name" => $this->_("Iceland"),
			],
			[
				"continent" => "EUR",
				"id" => "IT",
				"name" => $this->_("Italy"),
				"territories_reference" => $this->_("Provinces"),
			],
			[
				"continent" => "EUR",
				"id" => "JE",
				"name" => $this->_("Jersey"),
			],
			[
				"continent" => "EUR",
				"id" => "LI",
				"name" => $this->_("Liechtenstein"),
			],
			[
				"continent" => "EUR",
				"id" => "LT",
				"name" => $this->_("Lithuania"),
			],
			[
				"continent" => "EUR",
				"id" => "LU",
				"name" => $this->_("Luxembourg"),
			],
			[
				"continent" => "EUR",
				"id" => "LV",
				"name" => $this->_("Latvia"),
			],
			[
				"continent" => "EUR",
				"id" => "MC",
				"name" => $this->_("Monaco"),
			],
			[
				"continent" => "EUR",
				"id" => "MD",
				"name" => $this->_("Moldova"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "EUR",
				"id" => "ME",
				"name" => $this->_("Montenegro"),
			],
			[
				"continent" => "EUR",
				"id" => "MK",
				"name" => $this->_("Macedonia"),
			],
			[
				"continent" => "EUR",
				"id" => "MT",
				"name" => $this->_("Malta"),
			],
			[
				"continent" => "EUR",
				"id" => "NL",
				"name" => $this->_("Netherlands"),
			],
			[
				"continent" => "EUR",
				"id" => "NO",
				"name" => $this->_("Norway"),
			],
			[
				"continent" => "EUR",
				"id" => "PL",
				"name" => $this->_("Poland"),
			],
			[
				"continent" => "EUR",
				"id" => "PT",
				"name" => $this->_("Portugal"),
			],
			[
				"continent" => "EUR",
				"id" => "RO",
				"name" => $this->_("Romania"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "EUR",
				"id" => "RS",
				"name" => $this->_("Serbia"),
			],
			[
				"continent" => "EUR",
				"id" => "RU",
				"name" => $this->_("Russia"),
			],
			[
				"continent" => "EUR",
				"id" => "SE",
				"name" => $this->_("Sweden"),
			],
			[
				"continent" => "EUR",
				"id" => "SI",
				"name" => $this->_("Slovenia"),
			],
			[
				"continent" => "EUR",
				"id" => "SJ",
				"name" => $this->_("Svalbard and Jan Mayen"),
			],
			[
				"continent" => "EUR",
				"id" => "SK",
				"name" => $this->_("Slovakia"),
			],
			[
				"continent" => "EUR",
				"id" => "SM",
				"name" => $this->_("San Marino"),
			],
			[
				"continent" => "EUR",
				"id" => "TR",
				"name" => $this->_("Turkey"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "EUR",
				"id" => "UA",
				"name" => $this->_("Ukraine"),
			],
			[
				"continent" => "EUR",
				"id" => "VA",
				"name" => $this->_("Vatican"),
			],
			// NORTH AMERICA
			[
				"continent" => "NAM",
				"id" => "AG",
				"name" => $this->_("Antigua and Barbuda"),
			],
			[
				"continent" => "NAM",
				"id" => "AI",
				"name" => $this->_("Anguilla"),
			],
			[
				"continent" => "NAM",
				"id" => "AW",
				"name" => $this->_("Aruba"),
			],
			[
				"continent" => "NAM",
				"id" => "BB",
				"name" => $this->_("Barbados"),
			],
			[
				"continent" => "NAM",
				"id" => "BL",
				"name" => $this->_("Saint Barthélemy"),
			],
			[
				"continent" => "NAM",
				"id" => "BM",
				"name" => $this->_("Bermuda"),
			],
			[
				"continent" => "NAM",
				"id" => "BQ",
				"name" => $this->_("Bonaire, Saint Eustatius and Saba"),
			],
			[
				"continent" => "NAM",
				"id" => "BS",
				"name" => $this->_("Bahamas"),
			],
			[
				"continent" => "NAM",
				"id" => "BZ",
				"name" => $this->_("Belize"),
			],
			[
				"continent" => "NAM",
				"id" => "CA",
				"name" => $this->_("Canada"),
				"territories_reference" => $this->_("Provinces"),
			],
			[
				"continent" => "NAM",
				"id" => "CR",
				"name" => $this->_("Costa Rica"),
			],
			[
				"continent" => "NAM",
				"id" => "CU",
				"name" => $this->_("Cuba"),
			],
			[
				"continent" => "NAM",
				"id" => "CW",
				"name" => $this->_("Curaçao"),
			],
			[
				"continent" => "NAM",
				"id" => "DM",
				"name" => $this->_("Dominica"),
			],
			[
				"continent" => "NAM",
				"id" => "DO",
				"name" => $this->_("Dominican Republic"),
			],
			[
				"continent" => "NAM",
				"id" => "GD",
				"name" => $this->_("Grenada"),
			],
			[
				"continent" => "NAM",
				"id" => "GL",
				"name" => $this->_("Greenland"),
			],
			[
				"continent" => "NAM",
				"id" => "GP",
				"name" => $this->_("Guadeloupe"),
			],
			[
				"continent" => "NAM",
				"id" => "GT",
				"name" => $this->_("Guatemala"),
			],
			[
				"continent" => "NAM",
				"id" => "HN",
				"name" => $this->_("Honduras"),
			],
			[
				"continent" => "NAM",
				"id" => "HT",
				"name" => $this->_("Haiti"),
			],
			[
				"continent" => "NAM",
				"id" => "JM",
				"name" => $this->_("Jamaica"),
			],
			[
				"continent" => "NAM",
				"id" => "KN",
				"name" => $this->_("Saint Kitts and Nevis"),
			],
			[
				"continent" => "NAM",
				"id" => "KY",
				"name" => $this->_("Cayman Islands"),
			],
			[
				"continent" => "NAM",
				"id" => "LC",
				"name" => $this->_("Saint Lucia"),
			],
			[
				"continent" => "NAM",
				"id" => "MF",
				"name" => $this->_("Saint Martin (French part)"),
			],
			[
				"continent" => "NAM",
				"id" => "MQ",
				"name" => $this->_("Martinique"),
			],
			[
				"continent" => "NAM",
				"id" => "MS",
				"name" => $this->_("Montserrat"),
			],
			[
				"continent" => "NAM",
				"id" => "MX",
				"name" => $this->_("Mexico"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "NAM",
				"id" => "NI",
				"name" => $this->_("Nicaragua"),
			],
			[
				"continent" => "NAM",
				"id" => "PA",
				"name" => $this->_("Panama"),
			],
			[
				"continent" => "NAM",
				"id" => "PM",
				"name" => $this->_("Saint Pierre and Miquelon"),
			],
			[
				"continent" => "NAM",
				"id" => "PR",
				"name" => $this->_("Puerto Rico"),
			],
			[
				"continent" => "NAM",
				"id" => "SV",
				"name" => $this->_("El Salvador"),
			],
			[
				"continent" => "NAM",
				"id" => "SX",
				//  "name" => $this->_("Saint Martin (Dutch part)"),
				"name" => $this->_("Sint Maarten (Dutch part)"),
			],
			[
				"continent" => "NAM",
				"id" => "TC",
				"name" => $this->_("Turks and Caicos Islands"),
			],
			[
				"continent" => "NAM",
				"id" => "TT",
				"name" => $this->_("Trinidad and Tobago"),
			],
			[
				"continent" => "NAM",
				"id" => "US",
				//  "name" => $this->_("United States"),
				"name" => $this->_("United States of America"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "NAM",
				"id" => "VC",
				"name" => $this->_("Saint Vincent and the Grenadines"),
			],
			[
				"continent" => "NAM",
				"id" => "VG",
				"name" => $this->_("British Virgin Islands"),
			],
			[
				"continent" => "NAM",
				"id" => "VI",
				"name" => $this->_("U.S. Virgin Islands"),
			],
			// OCEANIA
			[
				"continent" => "OCE",
				"id" => "AS",
				"name" => $this->_("American Samoa"),
			],
			[
				"continent" => "OCE",
				"id" => "AU",
				"name" => $this->_("Australia"),
				"territories_reference" => "States",
			],
			[
				"continent" => "OCE",
				"id" => "CK",
				"name" => $this->_("Cook Islands"),
			],
			[
				"continent" => "OCE",
				"id" => "FJ",
				"name" => $this->_("Fiji"),
			],
			[
				"continent" => "OCE",
				"id" => "FM",
				"name" => $this->_("Micronesia"),
			],
			[
				"continent" => "OCE",
				"id" => "GU",
				"name" => $this->_("Guam"),
			],
			[
				"continent" => "OCE",
				"id" => "KI",
				"name" => $this->_("Kiribati"),
			],
			[
				"continent" => "OCE",
				"id" => "MH",
				"name" => $this->_("Marshall Islands"),
			],
			[
				"continent" => "OCE",
				"id" => "MP",
				"name" => $this->_("Northern Mariana Islands"),
			],
			[
				"continent" => "OCE",
				"id" => "NC",
				"name" => $this->_("New Caledonia"),
			],
			[
				"continent" => "OCE",
				"id" => "NF",
				"name" => $this->_("Norfolk Island"),
			],
			[
				"continent" => "OCE",
				"id" => "NR",
				"name" => $this->_("Nauru"),
			],
			[
				"continent" => "OCE",
				"id" => "NU",
				"name" => $this->_("Niue"),
			],
			[
				"continent" => "OCE",
				"id" => "NZ",
				"name" => $this->_("New Zealand"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "OCE",
				"id" => "PF",
				"name" => $this->_("French Polynesia"),
			],
			[
				"continent" => "OCE",
				"id" => "PG",
				"name" => $this->_("Papua New Guinea"),
			],
			[
				"continent" => "OCE",
				"id" => "PN",
				"name" => $this->_("Pitcairn"),
			],
			[
				"continent" => "OCE",
				"id" => "PW",
				//  "name" => $this->_("Belau"),
				"name" => $this->_("Palau"),
			],
			[
				"continent" => "OCE",
				"id" => "SB",
				"name" => $this->_("Solomon Islands"),
			],
			[
				"continent" => "OCE",
				"id" => "TK",
				"name" => $this->_("Tokelau"),
			],
			[
				"continent" => "OCE",
				"id" => "TO",
				"name" => $this->_("Tonga"),
			],
			[
				"continent" => "OCE",
				"id" => "TV",
				"name" => $this->_("Tuvalu"),
			],
			[
				"continent" => "OCE",
				"id" => "UM",
				"name" => $this->_("United States Minor Outlying Islands"),
			],
			[
				"continent" => "OCE",
				"id" => "VU",
				"name" => $this->_("Vanuatu"),
			],
			[
				"continent" => "OCE",
				"id" => "WF",
				"name" => $this->_("Wallis and Futuna"),
			],
			[
				"continent" => "OCE",
				"id" => "WS",
				"name" => $this->_("Samoa"),
			],
			// SOUTH AMERICA
			[
				"continent" => "SAM",
				"id" => "AR",
				"name" => $this->_("Argentina"),
				"territories_reference" => $this->_("Provinces"),
			],
			[
				"continent" => "SAM",
				"id" => "BO",
				"name" => $this->_("Bolivia"),
				"territories_reference" => "States",
			],
			[
				"continent" => "SAM",
				"id" => "BR",
				"name" => $this->_("Brazil"),
				"territories_reference" => "States",
			],
			[
				"continent" => "SAM",
				"id" => "CL",
				"name" => $this->_("Chile"),
			],
			[
				"continent" => "SAM",
				"id" => "CO",
				"name" => $this->_("Colombia"),
			],
			[
				"continent" => "SAM",
				"id" => "EC",
				"name" => $this->_("Ecuador"),
			],
			[
				"continent" => "SAM",
				"id" => "FK",
				"name" => $this->_("Falkland Islands"),
			],
			[
				"continent" => "SAM",
				"id" => "GF",
				"name" => $this->_("French Guiana"),
			],
			[
				"continent" => "SAM",
				"id" => "GY",
				"name" => $this->_("Guyana"),
			],
			[
				"continent" => "SAM",
				"id" => "PE",
				"name" => $this->_("Peru"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "SAM",
				"id" => "PY",
				"name" => $this->_("Paraguay"),
				"territories_reference" => $this->_("States"),
			],
			[
				"continent" => "SAM",
				"id" => "SR",
				"name" => $this->_("Suriname"),
			],
			[
				"continent" => "SAM",
				"id" => "UY",
				"name" => $this->_("Uruguay"),
			],
			[
				"continent" => "SAM",
				"id" => "VE",
				"name" => $this->_("Venezuela"),
			],
		];
		return $countries;
	}

	public function getCountryByCode($countryCode) {
		$foundCountry = null;
		if (empty($countryCode)) {
			return $foundCountry;
		}
		//----------
		$countries = $this->getCountries();
		foreach ($countries as $country) {
			if (\strtolower($country['id']) === \strtolower($countryCode)) {
				$foundCountry = $country;
				break;
			}
		}
		return $foundCountry;
	}

	public function getCountryByName($countryName) {
		$foundCountry = null;
		if (empty($countryName)) {
			return $foundCountry;
		}
		//----------
		$countries = $this->getCountries();
		foreach ($countries as $country) {
			if (\strtolower($country['name']) === \strtolower($countryName)) {
				$foundCountry = $country;
				break;
			}
		}
		return $foundCountry;
	}

	public function getContinentCountries($continentCode) {
		$foundCountries = null;
		if (empty($continentCode)) {
			return $foundCountries;
		}
		//----------
		$countries = $this->getCountries();

		$foundCountries = \array_filter($countries, function ($country) use ($continentCode) {
			return \strtolower($country['continent']) === \strtolower($continentCode);
		});

		return $foundCountries;
	}

	public function getCountryTerritoriesReferenceByCode($countryCode) {
		$foundCountryTerritoriesReference = null;
		if (empty($countryCode)) {
			return $foundCountryTerritoriesReference;
		}
		//----------
		$country = $this->getCountryByCode($countryCode);
		if (!empty($country['territories_reference'])) {
			$foundCountryTerritoriesReference = $country['territories_reference'];
		}
		//-------------
		return $foundCountryTerritoriesReference;
	}

	public function getCountryTerritoriesReferenceByName($countryName) {
		$foundCountryTerritoriesReference = null;
		if (empty($countryName)) {
			return $foundCountryTerritoriesReference;
		}
		//----------
		$country = $this->getCountryByName($countryName);
		if (!empty($country['territories_reference'])) {
			$foundCountryTerritoriesReference = $country['territories_reference'];
		}
		//-------------
		return $foundCountryTerritoriesReference;
	}

	public function getEUCountriesCodes() {
		$euCountriesCodes = [
			"AT",
			"BE",
			"BG",
			"CY",
			"CZ",
			"DE",
			"DK",
			"EE",
			"ES",
			"FI",
			"FR",
			"GR", // OR 'EL' as per EU
			"HR",
			"HU",
			"IE",
			"IT",
			"LT",
			"LU",
			"LV",
			"MT",
			"NL",
			"PL",
			"PT",
			"RO",
			"SE",
			"SI",
			"SK",
		];

		return $euCountriesCodes;
	}

	public function getEUCountries() {
		$euCountries = [];
		// ---------
		foreach ($this->getEUCountriesCodes() as $countryCode) {
			$euCountry = $this->getCountryByCode($countryCode);
			if (!empty($euCountry)) {
				$euCountries[] = $euCountry;
			}
		}

		return $euCountries;
	}

	public function isEUCountry($countryCode) {
		$isEUCountry = false;
		if (empty($countryCode)) {
			return $isEUCountry;
		}
		//----------
		$euCountriesCodes = $this->getEUCountriesCodes();
		foreach ($euCountriesCodes as $euCountryCode) {
			if (\strtolower($euCountryCode) === \strtolower($countryCode)) {
				$isEUCountry = true;
				break;
			}
		}
		return $isEUCountry;
	}
}
