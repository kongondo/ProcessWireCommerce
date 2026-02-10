<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Tax: Trait class for PWCommerce Tax.
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

trait TraitPWCommerceTax
{



	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ TAXES ~~~~~~~~~~~~~~~~~~

	// --------------
	// tax rate at location shop is based
	protected $shopHomeTaxRate;
	protected $isChargeTaxesManualExemption;
	protected $isCustomerTaxExempt;
	// this takes into account above two exemptions + product taxable setting + eu digital goods vat taxes
	protected $isOrderLineItemTaxable;
	// takes into account category tax override on line item
	protected $orderLineItemTaxRate;
	protected $orderLineItemTaxPercent;

	/**
	 * Get the shop's tax settings
	 *
	 * @return mixed
	 */
	public function getShopTaxSettings() {
		$taxSettings = [];
		$taxSettingsJSON = $this->wire('pages')->getRaw("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",name=taxes", 'pwcommerce_settings');

		if (!empty($taxSettingsJSON)) {
			$taxSettings = json_decode($taxSettingsJSON, true);
		}
		return $taxSettings;
	}

	/**
	 * Is Prices Include Taxes.
	 *
	 * @return bool
	 */
	public function isPricesIncludeTaxes() {
		$taxSettings = $this->getShopTaxSettings();
		return !empty($taxSettings['prices_include_taxes']);
	}

	/**
	 * Get Shop Country Tax Rate.
	 *
	 * @return mixed
	 */
	public function getShopCountryTaxRate() {
		$taxSettings = $this->getShopTaxSettings();
		return isset($taxSettings['shop_country_tax_rate']) ? $taxSettings['shop_country_tax_rate'] : null;
	}

	/**
	 * Is Shop Charge Taxes On Shipping Rates.
	 *
	 * @return bool
	 */
	public function isShopChargeTaxesOnShippingRates() {
		$taxSettings = $this->getShopTaxSettings();
		return !empty($taxSettings['charge_taxes_on_shipping_rates']);
	}

	/**
	 * Is Shop Charging E U Digital Goods V A T Taxes.
	 *
	 * @return bool
	 */
	public function isShopChargingEUDigitalGoodsVATTaxes() {
		$taxSettings = $this->getShopTaxSettings();
		return !empty($taxSettings['charge_eu_digital_goods_vat_taxes']);
	}

	/**
	 * Is Shipping Taxable On Order.
	 *
	 * @return bool
	 */
	private function isShippingTaxableOnOrder() {
		$isShippingTaxableOnOrder = false;
		$isShopChargeTaxesOnShippingRates = $this->isShopChargeTaxesOnShippingRates();
		if ($isShopChargeTaxesOnShippingRates && (empty($this->order->isCustomerTaxExempt) && empty($this->order->isChargeTaxesManualExemption))) {
			$isShippingTaxableOnOrder = true;
		}
		// ------------
		return $isShippingTaxableOnOrder;
	}

	# **************************

	// TODO MOVE TO utilities orders traits? utilities checkout traits?

	// TODO: @SEE getOrderLineItemDiscountsAmount() => DELETE BELOW IF THEREFORE NOT IN USE
	// /**
  * Get Product Discount Amount.
  *
  * @return mixed
  */
 public function getProductDiscountAmount() {
	//     // 2. DISCOUNTS
	//     // 'discount_amount' => (float) $value->discountAmount, // +++
	// }
	/**
	 * Get Order Country Tax Data.
	 *
	 * @return mixed
	 */
	public function getOrderCountryTaxData() {
		// 3. TAXES
		// 'tax_name' => $sanitizer->text($value->taxName), // +++
		// 'tax_percentage' => (float) $value->taxPercentage, // +++
		// TODO: CONFIRM OK FOR EMPTIES!
		// @note: a country has only one standard/base tax rate: hence, grab the first and only item in the tax rates WireArray!
		$shippingCountryTaxData = new WireData();
		$firstShippingCountryTaxData = $this->shippingCountry->pwcommerce_tax_rates->first();
		if (!empty($firstShippingCountryTaxData)) {
			$shippingCountryTaxData = $firstShippingCountryTaxData;
		}

		return $shippingCountryTaxData;
	}

	/**
	 * Get Order Country Tax Short Name.
	 *
	 * @return mixed
	 */
	public function getOrderCountryTaxShortName() {
		// 3. TAXES
		// 'tax_name' => $sanitizer->text($value->taxName), // +++
		$shippingCountryTaxData = $this->getOrderCountryTaxData();
		return $shippingCountryTaxData->taxName;
	}

	/**
	 * Get Order Country Tax Percentage.
	 *
	 * @return mixed
	 */
	public function getOrderCountryTaxPercentage() {
		// 3. TAXES
		// 	'tax_percentage' => (float) $value->taxPercentage, // +++
		$shippingCountryTaxData = $this->getOrderCountryTaxData();
		// -----------
		return $shippingCountryTaxData->taxRate;
	}

	/**
	 * Get Order Country Tax Location Code.
	 *
	 * @return mixed
	 */
	public function getOrderCountryTaxLocationCode() {
		//   'tax_location_code' => $sanitizer->text($record->taxLocationCode),
		$shippingCountryTaxData = $this->getOrderCountryTaxData();
		return $shippingCountryTaxData->taxLocationCode;
	}

	/**
	 * Get Order Country Tax Overrides.
	 *
	 * @return mixed
	 */
	public function getOrderCountryTaxOverrides() {
		$shippingCountryTaxOverrides = $this->shippingCountry->pwcommerce_tax_overrides;
		// just in case is empty, to avoid errors in calling methods, we use an empty WireArray
		if (empty($shippingCountryTaxOverrides))
			$shippingCountryTaxOverrides = new WireArray();
		return $shippingCountryTaxOverrides;
	}

	/**
	 * Get Order Country Category Tax Overrides.
	 *
	 * @return mixed
	 */
	public function getOrderCountryCategoryTaxOverrides() {
		$shippingCountryTaxOverrides = $this->getOrderCountryTaxOverrides();
		$shippingCountryCategoryTaxOverrides = $shippingCountryTaxOverrides->find("overrideType=category");
		return $shippingCountryCategoryTaxOverrides;
	}

	/**
	 * Get Order Country Shipping Tax Overrides.
	 *
	 * @return mixed
	 */
	public function getOrderCountryShippingTaxOverrides() {
		$shippingCountryTaxOverrides = $this->getOrderCountryTaxOverrides();
		$shippingCountryShippingTaxOverrides = $shippingCountryTaxOverrides->find("overrideType=shipping");
		return $shippingCountryShippingTaxOverrides;
	}





	/**
	 * Get the category tax override rate for current order line item.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemCategoryTaxOverrideRate() {
		// @note: we return first match! TODO? in future, return highest or lowest override rate?

		// GET THE SHIPPING COUNTRY'S CATEGORY TAX OVERRIDES
		/** @var WireArray $orderCountryCategoryTaxOverrides */
		$orderCountryCategoryTaxOverrides = $this->getOrderCountryCategoryTaxOverrides();
		//----------
		// GET THIS ORDER LINE ITEM'S PRODUCT CATEGORIES
		$orderLineItemProductCategories = $this->getOrderLineItemProductCategories();
		// -------------------
		// CONVERT THE CATEGORIES IDS TO SELECTOR STRING
		$orderLineItemProductCategoriesIDs = implode("|", $orderLineItemProductCategories);
		$selector = "categoryID={$orderLineItemProductCategoriesIDs}";

		// GET THE FIRST MATCHING CATEGORY OVERRIDE
		$firstMatchCategoryOverride = $orderCountryCategoryTaxOverrides->get($selector);
		$categoryTaxOverrideRatePercentage = $firstMatchCategoryOverride->overrideTaxRate;
		//--------
		return $categoryTaxOverrideRatePercentage; // e.g. 5.5
	}

	# ****************

	/**
	 * Set the tax rate to be used for the current order line item.
	 *
	 * @return mixed
	 */
	public function setOrderLineItemTaxRate() {
		// TODO RENAME THIS METHOD
		$taxRate = 0;
		$taxRateAsPercentage = 0;
		if ($this->isOrderLineItemTaxable) {
			// order line item is taxable: check if applying country base tax OR category tax override
			$taxRateAsPercentage = $this->getOrderLineItemTaxRatePercentage();
			// convert percentage to decimal
			// $taxRate = $taxRateAsPercentage / 100;
			$taxRate = $this->getPercentageAsDecimal($taxRateAsPercentage);
		}
		// -----------
		$this->orderLineItemTaxRate = $taxRate;
		$this->orderLineItemTaxPercent = $taxRateAsPercentage;
	}

	# ****************

	/**
	 * Get Tax Country By I D.
	 *
	 * @param int $countryID
	 * @return mixed
	 */
	public function getTaxCountryByID($countryID) {
		$options = ['id', 'pwcommerce_tax_rates', 'pwcommerce_tax_overrides', 'children.count'];
		$countryTaxData = $this->wire('pages')->getRaw("id=$countryID", $options);
		return $countryTaxData;
	}

	# ****************


	/**
	 * Get the applicable tax rate percentage for the current order line item.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemTaxRatePercentage() {
		if ($this->isCategoryTaxOverridesApplicable()) {
			// if a category tax override exists for the product of the order line item, we use it
			$taxRateAsPercentage = $this->getOrderLineItemCategoryTaxOverrideRate();
		} else {
			// else use the country base tax
			// TODO: IN FUTURE, ALSO CONSIDER TERRITORIAL TAXES!
			$taxRateAsPercentage = $this->getOrderCountryTaxPercentage();
		}
		// ---------------------
		return $taxRateAsPercentage;
	}




	// checks if an order line item is taxable
	// based on:
	// (i) order-level setting: e.g. manual order tax exemption OR customer is tax exempt
	// (ii) product-level setting: i.e., is product taxable but also considers EU digital goods taxes for digital products
	// (iii) shipping country category-level-override: i.e. is there a product-category-based tax override

	/**
	 * Is Order Line Item Taxable.
	 *
	 * @return bool
	 */
	public function isOrderLineItemTaxable() {

		$isOrderLineItemTaxable = true;
		//-----------
		if ($this->isCustomerTaxExempt) {
			//   CUSTOMER IS TAX EXEMPT: do not tax the order line item
			$isOrderLineItemTaxable = false;
		} else if ($this->isChargeTaxesManualExemption) {
			//  ORDER MANUAL TAX EXEMPTION (DO NOT CHARGE TAXES on order): do not tax the order line item
			$isOrderLineItemTaxable = false;
		} else {
			//  CHECK IF  PRODUCT ITSELF IS TAXABLE
			if (!$this->isProductInOrderLineItemTaxable()) {
				$isOrderLineItemTaxable = false;
				// PRODUCT ITSELF IS NOT TAXABLE: do digital good + EU + shop settings for this check
				if ($this->isChargeEUDigitalGoodsTax()) {
					// eu digital goods tax does apply: MAKE THE LINE ITEM TAXABLE
					$isOrderLineItemTaxable = true;
				}
			}
		}
		// --------------------
		return $isOrderLineItemTaxable;
	}

	/**
	 * Checks if  the product in the order line item is taxable as per the product settings
	 *
	 * @return bool
	 */
	public function isProductInOrderLineItemTaxable() {
		$orderLineItemProductSettings = $this->getOrderLineItemProductSettings();
		// @note: if no setting for taxable, we assume taxable! TODO? ok?
		$taxable = isset($orderLineItemProductSettings['taxable']) ? (int) $orderLineItemProductSettings['taxable'] : 1;
		return $taxable === 1;
	}

	// /**
  * Is Charge E U Digital Goods Tax.
  *
  * @return bool
  */
 public function isChargeEUDigitalGoodsTax() {
	/**
	 *    is Charge E U Digital Goods Tax.
	 *
	 * @return mixed
	 */
	public function ___isChargeEUDigitalGoodsTax() {
		// TODO MAKE HOOKABLE SO DEVS CAN WORK COMPLEX DIGITAL TAX SCENARIOS
		// is this a digital product?
		$productSettings = ['settings' => $this->getOrderLineItemProductSettings()];

		// -------------
		$isChargeEUDigitalGoodsTax =
			// shop's policy is to charge EU customers EU digital goods vat taxes
			$this->isShopChargingEUDigitalGoodsVATTaxes() &&
			// customer is in the EU
			$this->isOrderCustomerShippingAddressInTheEU() &&
			// the order line item product is a digital product
			$this->isDigitalProduct($productSettings);
		// ----------------
		return $isChargeEUDigitalGoodsTax;
	}

	/**
	 * Is Category Tax Overrides Applicable.
	 *
	 * @return bool
	 */
	public function isCategoryTaxOverridesApplicable() {
		// @note: here we check if at least once of the product categories has an override. We don't check the override value here!
		/** @var WireArray $value */
		$orderCountryCategoryTaxOverrides = $this->getOrderCountryCategoryTaxOverrides();
		// TODO: THEN DO AN ARRAY DIFF? OR INTERSECT OF IDS OF CATEGORIES IN OVERRIDES AND IDS OF ORDER LINE ITEM PRODUCT CATEGORIES+
		$orderLineItemProductCategories = $this->getOrderLineItemProductCategories();

		$isCategoryTaxOverridesApplicable = false;
		foreach ($orderCountryCategoryTaxOverrides as $orderCountryCategoryTaxOverride) {
			if (in_array($orderCountryCategoryTaxOverride->categoryID, $orderLineItemProductCategories)) {
				$isCategoryTaxOverridesApplicable = true;
				break;
			}
		}
		// ----------
		return $isCategoryTaxOverridesApplicable;
	}


	# ************

	/**
	 * Get Shipping Fee With Tax.
	 *
	 * @param mixed $shippingFee
	 * @return mixed
	 */
	public function getShippingFeeWithTax($shippingFee) {
		$taxRateAsPercentage = $this->getShippingTaxRatePercentage();

		// NOTE - WE REUSE THE PRODUCT PRICE EXCLUSIVE TAX METHOD
		// this just grabs the shipping fee
		$shippingFeeTaxMoney = $this->getTaxAmountFromPriceExclusiveTax($taxRateAsPercentage, $shippingFee);


		// add tax to shipping fee
		$shippingFeeMoney = $this->money($shippingFee);
		$shippingFeeWithTaxMoney = $shippingFeeMoney->add($shippingFeeTaxMoney);
		$shippingFeeWithTax = $this->getWholeMoneyAmount($shippingFeeWithTaxMoney);
		// ----------
		return $shippingFeeWithTax;
		// return $shippingFeeWithTaxMoney;
	}

	/**
	 * Get Shipping Tax Rate Percentage.
	 *
	 * @return mixed
	 */
	protected function getShippingTaxRatePercentage() {
		// TODO: HERE NEED TO CHECK IF A SHIPPING TAX OVERRIDE APPLIES!
		// GET THE SHIPPING COUNTRY'S SHIPPING TAX OVERRIDES
		/** @var WireArray $orderCountryShippingTaxOverrides */
		$orderCountryShippingTaxOverrides = $this->getOrderCountryShippingTaxOverrides();
		// IF WE HAVE SHIPPING OVERRIDES
		if (!empty($orderCountryShippingTaxOverrides->count())) {
			// GET THE FIRST MATCHING SHIPPING OVERRIDE
			// @note: we expect only one anyway
			$firstMatchShippingOverride = $orderCountryShippingTaxOverrides->first();
			$taxRateAsPercentage = $firstMatchShippingOverride->overrideTaxRate;
		} else {
			// ELSE USE COUNTRY TAX PERCENTAGE
			$taxRateAsPercentage = $this->getOrderCountryTaxPercentage();
		}
		// ---------
		return $taxRateAsPercentage;
	}

	# ************


	/**
	 * Get Tax Amount From Price Inclusive Tax.
	 *
	 * @param float $taxPercent
	 * @param float $priceInclusiveTax
	 * @return mixed
	 */
	public function getTaxAmountFromPriceInclusiveTax(float $taxPercent, float $priceInclusiveTax) {
		// taxes calculated using the formula below.
		// tax = (tax rate * price) / (1 + tax rate)
		// dividend = (tax rate * price)
		// divisor = (1 + tax rate)
		###########################

		// $taxRate = $taxPercent / 100;
		$taxRate = $taxPercent / PwCommerce::HUNDRED;

		// $taxBottom = (1 + $taxRate);
		$divisor = (1 + $taxRate);

		$price = $this->money($priceInclusiveTax);
		// $taxTop = ($tax_rate * $price)
		// $dividend = $price->multiply($taxRate);
		$dividend = $price->multiply(strval($taxRate));
		$taxAmount = $dividend->divide(strval($divisor));
		// -----
		return $taxAmount;
	}

	/**
	 * Get Tax Amount From Price Exclusive Tax.
	 *
	 * @param float $taxPercent
	 * @param float $priceExclusiveTax
	 * @return mixed
	 */
	public function getTaxAmountFromPriceExclusiveTax(float $taxPercent, float $priceExclusiveTax) {
		// taxes calculated using the formula below.
		// tax = (tax rate * price)

		###########################

		// $taxRate = $taxPercent / 100;
		$taxRate = $taxPercent / PwCommerce::HUNDRED;

		$priceExclusiveTax = strval($priceExclusiveTax);
		$priceMoney = $this->money($priceExclusiveTax); // £20.00
		$taxAmountMoney = $priceMoney->multiply(strval($taxRate));
		// -----
		return $taxAmountMoney;
	}


}