<?php

namespace ProcessWire;

/**
 * PWCommerce: Locales
 *
 * Class to deal with Locales for PWCommerce general use.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceLocales for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

class PWCommerceLocales extends WireData
{

  /**
   * Get Locales.
   *
   * @return mixed
   */
  public function getLocales() {
    $locales = [
      // ### AFRICA ###
      // South Africa
      [
        "country" => "ZA",
        "currency_code" => "ZAR",
        "currency_position" => "left",
        "thousand_separator" => ",",
        "decimal_separator" => ".",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "ZA",
            "state" => "",
            "rate" => "15.0000",
            "name" => "VAT",
            "shipping" => true,
          ],
        ],
      ],
      // ### ASIA ###
      // Bangladesh
      [
        "country" => "BD",
        "currency_code" => "BDT",
        "currency_position" => "left",
        "thousand_separator" => ",",
        "decimal_separator" => ".",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "in",
        "tax_rates" => [
          [
            "country" => "BD",
            "state" => "",
            "rate" => "15.0000",
            "name" => "VAT",
            "shipping" => true,
          ],
        ],
      ],
      // Japan
      [
        "country" => "JP",
        "currency_code" => "JPY",
        "currency_position" => "left",
        "thousand_separator" => ",",
        "decimal_separator" => ".",
        "num_decimals" => 0,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "JP",
            "state" => "",
            "rate" => "8.0000",
            "name" => "Consumption tax",
            "shipping" => true,
          ],
        ],
      ],
      // Nepal
      [
        "country" => "NP",
        "currency_code" => "NPR",
        "currency_position" => "left_space",
        "thousand_separator" => ",",
        "decimal_separator" => ".",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "NP",
            "state" => "",
            "rate" => "13.0000",
            "name" => "VAT",
            "shipping" => true,
          ],
        ],
      ],
      // Thailand
      [
        "country" => "TH",
        "currency_code" => "THB",
        "currency_position" => "left",
        "thousand_separator" => ",",
        "decimal_separator" => ".",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "TH",
            "state" => "",
            "rate" => "7.0000",
            "name" => "VAT",
            "shipping" => true,
          ],
        ],
      ],

      // EUROPE
      // Belgium
      [
        "country" => "BE",
        "currency_code" => "EUR",
        "currency_position" => "left",
        "thousand_separator" => " ",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "BE",
            "state" => "",
            "rate" => "21.0000",
            "name" => "BTW",
            "shipping" => true,
          ],

        ],
      ],
      //   Germany
      [
        "country" => "DE",
        "currency_code" => "EUR",
        "currency_position" => "left",
        "thousand_separator" => ".",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "DE",
            "state" => "",
            "rate" => "19.0000",
            "name" => "Mwst.",
            "shipping" => true,
          ],
        ],
      ],
      //   Spain
      [
        "country" => "ES",
        "currency_code" => "EUR",
        "currency_position" => "right",
        "thousand_separator" => ".",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "ES",
            "state" => "",
            "rate" => "21.0000",
            "name" => "VAT",
            "shipping" => true,
          ],

        ],
      ],
      //   Finland
      [
        "country" => "FI",
        "currency_code" => "EUR",
        "currency_position" => "right_space",
        "thousand_separator" => " ",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "FI",
            "state" => "",
            "rate" => "24.0000",
            "name" => "ALV",
            "shipping" => true,
          ],

        ],
      ],
      //   France
      [
        "country" => "FR",
        "currency_code" => "EUR",
        "currency_position" => "right",
        "thousand_separator" => " ",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "FR",
            "state" => "",
            "rate" => "20.0000",
            "name" => "TVA",
            "shipping" => true,
          ],
        ],
      ],
      //   United Kingdom
      [
        "country" => "GB",
        "currency_code" => "GBP",
        "currency_position" => "left",
        "thousand_separator" => ",",
        "decimal_separator" => ".",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "GB",
            "state" => "",
            "rate" => "20.0000",
            "name" => "VAT",
            "shipping" => true,
          ],
        ],
      ],
      //   Hungary
      [
        "country" => "HU",
        "currency_code" => "HUF",
        "currency_position" => "right_space",
        "thousand_separator" => " ",
        "decimal_separator" => ",",
        "num_decimals" => 0,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "HU",
            "state" => "",
            "rate" => "27.0000",
            "name" => "ÁFA",
            "shipping" => true,
          ],
        ],
      ],
      //   Italy
      [
        "country" => "IT",
        "currency_code" => "EUR",
        "currency_position" => "right",
        "thousand_separator" => ".",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "IT",
            "state" => "",
            "rate" => "22.0000",
            "name" => "IVA",
            "shipping" => true,
          ],
        ],
      ],
      //   Moldova
      [
        "country" => "MD",
        "currency_code" => "MDL",
        "currency_position" => "right_space",
        "thousand_separator" => ".",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "MD",
            "state" => "",
            "rate" => "20.0000",
            "name" => "TVA",
            "shipping" => true,
          ],
        ],
      ],
      //   Netherlands
      [
        "country" => "NL",
        "currency_code" => "EUR",
        "currency_position" => "left",
        "thousand_separator" => ",",
        "decimal_separator" => ".",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "NL",
            "state" => "",
            "rate" => "21.0000",
            "name" => "VAT",
            "shipping" => true,
          ],
        ],
      ],
      //   Norway
      [
        "country" => "NO",

        "currency_code" => "Kr",
        "currency_position" => "left_space",
        "thousand_separator" => ".",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "NO",
            "state" => "",
            "rate" => "25.0000",
            "name" => "MVA",
            "shipping" => true,
          ],
        ],
      ],
      //   Poland
      [
        "country" => "PL",
        "currency_code" => "PLN",
        "currency_position" => "right_space",
        "thousand_separator" => " ",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "PL",
            "state" => "",
            "rate" => "23.0000",
            "name" => "VAT",
            "shipping" => true,
          ],
        ],
      ],
      //   Romania
      [
        "country" => "RO",
        "currency_code" => "RON",
        "currency_position" => "right_space",
        "thousand_separator" => ".",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "RO",
            "state" => "",
            "rate" => "19.0000",
            "name" => "TVA",
            "shipping" => true,
          ],
        ],
      ],
      // Turkey
      [
        "country" => "TR",
        "name" => "",
        "currency_code" => "TRY",
        "currency_position" => "left_space",
        "thousand_separator" => ".",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "TR",
            "state" => "",
            "rate" => "18.0000",
            "name" => "KDV",
            "shipping" => true,
          ],
        ],
      ],
      // NORTH AMERICA
      // Canada
      [
        "country" => "CA",
        "currency_code" => "CAD",
        "currency_position" => "left",
        "thousand_separator" => ",",
        "decimal_separator" => ".",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "CA",
            "state" => "BC",
            "rate" => "7.0000",
            "name" => "PST",
            "shipping" => false,
            "priority" => 2,
          ],
          [
            "country" => "CA",
            "state" => "SK",
            "rate" => "5.0000",
            "name" => "PST",
            "shipping" => false,
            "priority" => 2,
          ],
          [
            "country" => "CA",
            "state" => "MB",
            "rate" => "8.0000",
            "name" => "PST",
            "shipping" => false,
            "priority" => 2,
          ],
          [
            "country" => "CA",
            "state" => "QC",
            "rate" => "9.975",
            "name" => "QST",
            "shipping" => false,
            "priority" => 2,
          ],
          // all canada
          [
            "country" => "CA",
            "state" => "ON",
            "rate" => "13.0000",
            "name" => "HST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "NL",
            "rate" => "13.0000",
            "name" => "HST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "NB",
            "rate" => "13.0000",
            "name" => "HST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "PE",
            "rate" => "14.0000",
            "name" => "HST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "NS",
            "rate" => "15.0000",
            "name" => "HST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "AB",
            "rate" => "5.0000",
            "name" => "GST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "BC",
            "rate" => "5.0000",
            "name" => "GST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "NT",
            "rate" => "5.0000",
            "name" => "GST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "NU",
            "rate" => "5.0000",
            "name" => "GST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "YT",
            "rate" => "5.0000",
            "name" => "GST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "SK",
            "rate" => "5.0000",
            "name" => "GST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "MB",
            "rate" => "5.0000",
            "name" => "GST",
            "shipping" => true,
          ],
          [
            "country" => "CA",
            "state" => "QC",
            "rate" => "5.0000",
            "name" => "GST",
            "shipping" => true,
          ],
        ],
      ],
      // United States
      [
        "country" => "US",
        "currency_code" => "USD",
        "currency_position" => "left",
        "thousand_separator" => ",",
        "decimal_separator" => ".",
        "num_decimals" => 2,
        "weight_unit" => "oz",
        "dimension_unit" => "in",
        "tax_rates" => [
          [
            "country" => "US",
            "state" => "AL",
            "rate" => "4.0000",
            "name" => "State Tax",
            "shipping" => false,
          ],

          [
            "country" => "US",
            "state" => "AZ",
            "rate" => "5.6000",
            "name" => "State Tax",
            "shipping" => false,
          ],

          [
            "country" => "US",
            "state" => "AR",
            "rate" => "6.5000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "CA",
            "rate" => "7.5000",
            "name" => "State Tax",
            "shipping" => false,
          ],

          [
            "country" => "US",
            "state" => "CO",
            "rate" => "2.9000",
            "name" => "State Tax",
            "shipping" => false,
          ],

          [
            "country" => "US",
            "state" => "CT",
            "rate" => "6.3500",
            "name" => "State Tax",
            "shipping" => true,
          ],

          [
            "country" => "US",
            "state" => "DC",
            "rate" => "5.7500",
            "name" => "State Tax",
            "shipping" => true,
          ],

          [
            "country" => "US",
            "state" => "FL",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],

          [
            "country" => "US",
            "state" => "GA",
            "rate" => "4.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "GU",
            "rate" => "4.0000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "HI",
            "rate" => "4.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "ID",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "IL",
            "rate" => "6.2500",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "IN",
            "rate" => "7.0000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "IA",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "KS",
            "rate" => "6.1500",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "KY",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "LA",
            "rate" => "4.0000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "ME",
            "rate" => "5.5000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "MD",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "MA",
            "rate" => "6.2500",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "MI",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "MN",
            "rate" => "6.8750",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "MS",
            "rate" => "7.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "MO",
            "rate" => "4.225",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "NE",
            "rate" => "5.5000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "NV",
            "rate" => "6.8500",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "NJ",
            "rate" => "6.8750",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "NM",
            "rate" => "5.1250",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "NY",
            "rate" => "4.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "NC",
            "rate" => "4.7500",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "ND",
            "rate" => "5.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "OH",
            "rate" => "5.7500",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "OK",
            "rate" => "4.5000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "PA",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "PR",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "RI",
            "rate" => "7.0000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "SC",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "SD",
            "rate" => "4.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "TN",
            "rate" => "7.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "TX",
            "rate" => "6.2500",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "UT",
            "rate" => "5.9500",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "VT",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "VA",
            "rate" => "5.3000",
            "name" => "State Tax",
            "shipping" => false,
          ],
          [
            "country" => "US",
            "state" => "WA",
            "rate" => "6.5000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "WV",
            "rate" => "6.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "WI",
            "rate" => "5.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
          [
            "country" => "US",
            "state" => "WY",
            "rate" => "4.0000",
            "name" => "State Tax",
            "shipping" => true,
          ],
        ],
      ],
      // OCEANIA
      // Australia
      [
        "country" => "AU",

        "currency_code" => "AUD",
        "currency_position" => "left",
        "thousand_separator" => ",",
        "decimal_separator" => ".",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "AU",
            "state" => "",
            "rate" => "10.0000",
            "name" => "GST",
            "shipping" => true,
          ],
        ],
      ],

      // SOUTH AMERICA
      // Brazil
      [
        "country" => "BR",
        "currency_code" => "BRL",
        "currency_position" => "left",
        "thousand_separator" => ".",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [],
      ],
      //   Paraguay
      [
        "country" => "PY",
        "currency_code" => "PYG",
        "currency_position" => "left",
        "thousand_separator" => ".",
        "decimal_separator" => ",",
        "num_decimals" => 2,
        "weight_unit" => "kg",
        "dimension_unit" => "cm",
        "tax_rates" => [
          [
            "country" => "PY",
            "state" => "",
            "rate" => "10.0000",
            "name" => "VAT",
            "shipping" => true,
          ],
        ],
      ],

    ];
    return $locales;
  }

  /**
   * Get Country Locale By Code.
   *
   * @param mixed $countryCode
   * @return mixed
   */
  public function getCountryLocaleByCode($countryCode) {
    $foundCountryLocale = null;
    if (empty($countryCode)) {
      return $foundCountryLocale;
    }
    //----------
    $locales = $this->getLocales();
    foreach ($locales as $locale) {
      if (\strtolower($locale['country']) === \strtolower($countryCode)) {
        $foundCountryLocale = $locale;
        break;
      }
    }
    return $foundCountryLocale;
  }
}
