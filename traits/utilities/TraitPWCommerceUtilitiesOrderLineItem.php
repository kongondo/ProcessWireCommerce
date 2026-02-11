<?php

namespace ProcessWire;


trait TraitPWCommerceUtilitiesOrderLineItem
{


	// ====
	private $taxPercent;
	private $taxRate;
	# IF LIST PRICES INCLUDE TAX
	// base/standard shop/home country tax percent and tax rate
	private $baseTaxPercentForPriceIncludesTax;
	private $baseTaxRateForPriceIncludesTax;
	#################
	private $taxAmountMoney;
	private $taxAmount;
	private $taxAmountAfterDiscountMoney;
	private $taxAmountAfterDiscount;
	private $isTaxOverride;
	private $quantity;
	private $unitDisplayPrice;
	private $unitDisplayPriceMoney;
	private $unitPriceBeforeTaxMoney;
	private $unitPriceAfterTaxMoney;
	private $totalDisplayPrice;
	private $totalDisplayPriceMoney;
	private $totalPriceBeforeTaxMoney;
	private $totalPriceAfterTaxMoney;
	private $totalDiscountsAmountMoney;
	private $totalPriceWithDiscountBeforeTaxMoney;
	private $totalPriceWithDiscountAfterTaxMoney;
	private $unitPriceWithDiscountBeforeTaxMoney;
	private $unitPriceWithDiscountAfterTaxMoney;
	private $isDiscountApplied = false;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ORDER LINE ITEMS ~~~~~~~~~~~~~~~~~~

<?php

namespace ProcessWire;


trait TraitPWCommerceUtilitiesOrderLineItem
{


	// ====
	private $taxPercent;
	private $taxRate;
	# IF LIST PRICES INCLUDE TAX
	// base/standard shop/home country tax percent and tax rate
	private $baseTaxPercentForPriceIncludesTax;
	private $baseTaxRateForPriceIncludesTax;
	#################
	private $taxAmountMoney;
	private $taxAmount;
	private $taxAmountAfterDiscountMoney;
	private $taxAmountAfterDiscount;
	private $isTaxOverride;
	private $quantity;
	private $unitDisplayPrice;
	private $unitDisplayPriceMoney;
	private $unitPriceBeforeTaxMoney;
	private $unitPriceAfterTaxMoney;
	private $totalDisplayPrice;
	private $totalDisplayPriceMoney;
	private $totalPriceBeforeTaxMoney;
	private $totalPriceAfterTaxMoney;
	private $totalDiscountsAmountMoney;
	private $totalPriceWithDiscountBeforeTaxMoney;
	private $totalPriceWithDiscountAfterTaxMoney;
	private $unitPriceWithDiscountBeforeTaxMoney;
	private $unitPriceWithDiscountAfterTaxMoney;
	private $isDiscountApplied = false;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ORDER LINE ITEMS ~~~~~~~~~~~~~~~~~~

	/**
	 * Get This Years Order Line Items.
	 *
	 * @param bool $isRaw
	 * @param array $options
	 * @return mixed
	 */
	public function getThisYearsOrderLineItems(bool $isRaw = true, array $options = []) {
		// TODO WILL NEED TO ADD STATUS COMPLETE TO SELECTOR!
		// $endOflastYearDate = $endOflastYear->getTimestamp();
		// $startOfNextYearDate = $startOfNextYear->getTimestamp();
		$endOfLastYearTimestamp = $this->getEndOfLastYearTimestamp();
		$startOfNextYearTimestamp = $this->getStartOfNextYearTimestamp();
		// TODO: WHY NOT ORDER TEMPLATE HERE OR WE NEED BOTH? FOR THE STATS, MOST LIKELY NEED BOTH?
		// $selector = "template=" . PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME . ",created>$endOfLastYearTimestamp,created<$startOfNextYearTimestamp";
		// TODO: ALTERNATIVELY, GET THE ORDERS IDS FIRST, THEN GET THEIR CHILDREN?
		// TODO WHAT ABOUT LIMITS? IF USING FINDRAW THEN OK WITHOUT?
		$selector = "include=all,check_access=0,template=" . PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME . ",created>$endOfLastYearTimestamp,created<$startOfNextYearTimestamp,status<" . Page::statusTrash;
		if ($isRaw) {
			// findRaw
			$thisYearsOrderLineItems = $this->wire('pages')->findRaw($selector, $options);
		} else {
			// usual find
			$thisYearsOrderLineItems = $this->wire('pages')->find($selector);
		}

		// ---------------
		return $thisYearsOrderLineItems;
	}


	# *******************

	#region
	public function getThisYearsOrderLineItems(bool $isRaw = true, array $options = []) {
		// TODO WILL NEED TO ADD STATUS COMPLETE TO SELECTOR!
		// $endOflastYearDate = $endOflastYear->getTimestamp();
		// $startOfNextYearDate = $startOfNextYear->getTimestamp();
		$endOfLastYearTimestamp = $this->getEndOfLastYearTimestamp();
		$startOfNextYearTimestamp = $this->getStartOfNextYearTimestamp();
		// TODO: WHY NOT ORDER TEMPLATE HERE OR WE NEED BOTH? FOR THE STATS, MOST LIKELY NEED BOTH?
		// $selector = "template=" . PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME . ",created>$endOfLastYearTimestamp,created<$startOfNextYearTimestamp";
		// TODO: ALTERNATIVELY, GET THE ORDERS IDS FIRST, THEN GET THEIR CHILDREN?
		// TODO WHAT ABOUT LIMITS? IF USING FINDRAW THEN OK WITHOUT?
		$selector = "include=all,check_access=0,template=" . PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME . ",created>$endOfLastYearTimestamp,created<$startOfNextYearTimestamp,status<" . Page::statusTrash;
		if ($isRaw) {
			// findRaw
			$thisYearsOrderLineItems = $this->wire('pages')->findRaw($selector, $options);
		} else {
			// usual find
			$thisYearsOrderLineItems = $this->wire('pages')->find($selector);
		}

		// ---------------
		return $thisYearsOrderLineItems;
	}


	# *******************

	#region
	/**
	 * Process 'calculated' values for given order line item.
	 *
	 * @param array $options
	 * @return mixed
	 */
	public function getOrderLineItemCalculatedValues(array $options) {


		// #######################
		# SETUP
		// set as temporary class properties for easier retrieval in various methods
		// $this->orderLineItem = $orderLineItem;
		// $this->productOrVariantPage = $productOrVariantPage;
		// $this->shippingCountry = $shippingCountry;
		// $this->isChargeTaxesManualExemption = $isChargeTaxesManualExemption;
		// $this->isCustomerTaxExempt = $isCustomerTaxExempt;
		// $this->isProcessTruePrice = $isProcessTruePrice;


		/** @var WireData $this->orderLineItem */
		$this->orderLineItem = $options['order_line_item'];
		$this->productOrVariantPage = $options['product_or_variant_page'];
		/** @var Page $this->shippingCountry */
		$this->shippingCountry = $options['shipping_country'];
		$this->isChargeTaxesManualExemption = $options['is_charge_taxes_manual_exemption'];
		$this->isCustomerTaxExempt = $options['is_customer_tax_exempt'];
		$this->isProcessTruePrice = $options['is_process_order_line_item_product_true_price'];
		$orderLineItem = $this->orderLineItem;

		// +++++++++
		// =================
		// SET ORDER LINE ITEM TAXABLE SETTING
		$this->isOrderLineItemTaxable = $this->isOrderLineItemTaxable();
		// SET TAX RATE IF APPLICABLE
		$this->setOrderLineItemTaxRate();

		// NOTE: THESE TWO SET ABOVE BY $this->setOrderLineItemTaxRate()
		$this->taxPercent = $this->orderLineItemTaxPercent;
		$this->taxRate = $this->orderLineItemTaxRate;
		$this->isTaxOverride = $this->isCategoryTaxOverridesApplicable() && $this->isOrderLineItemTaxable ? 1 : 0;
		$this->quantity = $this->orderLineItem->quantity;
		$this->unitDisplayPrice = $this->orderLineItem->unitPrice;
		$this->totalDisplayPrice = $this->orderLineItem->totalPrice;

		// #######################
		# COMPUTATIONS
		## ********** SET CALCULABLE VALUES FOR ORDER LINE ITEM ********** ##

		# SET VALUES FROM DISPLAY PRICES
		// might or might not include tax
		$this->unitDisplayPriceMoney = $this->money($this->unitDisplayPrice);
		// NOTE - WE WORK WITH NET PRICE BELOW! this is to cater for price inc tax situations
		$this->totalDisplayPriceMoney = $this->unitDisplayPriceMoney->multiply($this->quantity);
		// $totalPrice = strval($this->getMoneyTotalAsWholeMoneyAmount($unitDisplayPrice, $quantity));

		# NOTE: values with tax have the suffix 'WithTaxXXX'. Those without don't get this suffix and are considered 'net', unless stated otherwise

		// SET DEFAULTS
		// ++++++++
		$this->taxAmount = 0; // note: discounts not taxed!
		$this->taxAmountMoney = $this->getTaxAmount();
		if (!empty($this->taxAmountMoney)) {
			$this->taxAmount = $this->getWholeMoneyAmount($this->taxAmountMoney);
		}

		// ++++++++
		// (i) schema 'unit_price'
		$unitPriceBeforeTax = 0;
		$this->unitPriceBeforeTaxMoney = $this->getUnitPriceBeforeTax();
		if (!empty($this->unitPriceBeforeTaxMoney)) {
			$unitPriceBeforeTax = $this->getWholeMoneyAmount($this->unitPriceBeforeTaxMoney);
		}

		// (iii) schema 'unit_price_with_tax'
		$unitPriceAfterTax = 0;
		$this->unitPriceAfterTaxMoney = $this->getUnitPriceAfterTax();
		if (!empty($this->unitPriceAfterTaxMoney)) {
			$unitPriceAfterTax = $this->getWholeMoneyAmount($this->unitPriceAfterTaxMoney);
		}


		// ++++++++

		// (i) schema 'total_price'
		$totalPriceBeforeTax = 0;
		$this->totalPriceBeforeTaxMoney = $this->getTotalPriceBeforeTax();
		if (!empty($this->totalPriceBeforeTaxMoney)) {
			$totalPriceBeforeTax = $this->getWholeMoneyAmount($this->totalPriceBeforeTaxMoney);
		}

		// (iii) schema 'total_price_with_tax'
		$totalPriceAfterTax = 0;
		$this->totalPriceAfterTaxMoney = $this->getTotalPriceAfterTax();
		if (!empty($this->totalPriceAfterTaxMoney)) {
			$totalPriceAfterTax = $this->getWholeMoneyAmount($this->totalPriceAfterTaxMoney);
		}


		// ++++++++

		// schema 'total_discounts'
		$totalDiscountsAmount = 0;
		$this->totalDiscountsAmountMoney = $this->getTotalDiscountsAmount();
		if (!empty($this->totalDiscountsAmountMoney)) {
			$totalDiscountsAmount = $this->getWholeMoneyAmount($this->totalDiscountsAmountMoney);
		}


		// ++++++++
		// (ii) schema 'total_price_discounted'
		// if no discount, discounted total before tax equates to total before tax
		$totalPriceWithDiscountBeforeTax = $totalPriceBeforeTax;
		$this->totalPriceWithDiscountBeforeTaxMoney = $this->getTotalPriceWithDiscountBeforeTax();
		if (!empty($this->totalPriceWithDiscountBeforeTaxMoney)) {
			$totalPriceWithDiscountBeforeTax = $this->getWholeMoneyAmount($this->totalPriceWithDiscountBeforeTaxMoney);
		}

		// (iv) schema 'total_price_discounted_with_tax'
		// if no discount, discounted total AFTER tax equates to total AFTER tax
		$totalPriceWithDiscountAfterTax = $totalPriceAfterTax;
		if ($this->totalPriceWithDiscountBeforeTaxMoney->lessThan($this->totalPriceBeforeTaxMoney)) {
			// DISCOUNT WAS APPLIED (BEFORE TAX)
			$this->isDiscountApplied = true;
			// get discounted price with tax
			$this->totalPriceWithDiscountAfterTaxMoney = $this->getTotalPriceWithDiscountAfterTax();
			if (!empty($this->totalPriceWithDiscountAfterTaxMoney)) {
				$totalPriceWithDiscountAfterTax = $this->getWholeMoneyAmount($this->totalPriceWithDiscountAfterTaxMoney);
			}
		}

		// track final price applied after discount removed
		// $this->taxAmountAfterDiscountMoney was set in getTotalPriceWithDiscountAfterTax()
		// if line item is taxable
		$this->taxAmountAfterDiscount = 0; // note: discounts not taxed!
		if (!empty($this->isDiscountApplied) && $this->isOrderLineItemTaxable) {
			$this->taxAmountAfterDiscount = $this->getWholeMoneyAmount($this->taxAmountAfterDiscountMoney);
		}

		// ++++++++
		// (ii) schema 'unit_price_discounted'
		// if no discount, discounted unit before tax equates to unit before tax
		$unitPriceWithDiscountBeforeTax = $unitPriceBeforeTax;
		$this->unitPriceWithDiscountBeforeTaxMoney = $this->getUnitPriceWithDiscountBeforeTax();
		if (!empty($this->unitPriceWithDiscountBeforeTaxMoney)) {
			$unitPriceWithDiscountBeforeTax = $this->getWholeMoneyAmount($this->unitPriceWithDiscountBeforeTaxMoney);
		}


		// (iv) schema 'unit_price_discounted_with_tax'
		// if no discount, discounted unit AFTER tax equates to unit AFTER tax
		$unitPriceWithDiscountAfterTax = $unitPriceAfterTax;
		if ($this->unitPriceWithDiscountBeforeTaxMoney->lessThan($this->unitPriceBeforeTaxMoney)) {
			// DISCOUNT WAS APPLIED (BEFORE TAX)
			// get discounted price with tax
			$this->unitPriceWithDiscountAfterTaxMoney = $this->getUnitPriceWithDiscountAfterTax();
			if (!empty($this->unitPriceWithDiscountAfterTaxMoney)) {
				$unitPriceWithDiscountAfterTax = $this->getWholeMoneyAmount($this->unitPriceWithDiscountAfterTaxMoney);
			}
		}

		// ==========

		// 1.PRODUCT
		// ++ NONE ++
		// +++++++++++++
		// 2. DISCOUNTS
		// 'discount_amount' => (float) $value->discountAmount, // +++

		// ++++++++++++++++++
		$orderLineItem->discountAmount = $totalDiscountsAmount;
		/** @var WireArray $orderLineItemsDiscounts */
		$orderLineItemsDiscounts = $this->orderLineItem->discounts;
		if ($orderLineItemsDiscounts instanceof WireArray && !empty($orderLineItemsDiscounts->count())) {

			$orderLineItem->totalDiscounts = $orderLineItemsDiscounts->count();
			// ------
			if (!empty($this->orderLineItem->isApplyMultipleDiscounts)) {
				// multiple discounts were applied
				$orderLineItem->discountType = 'multiple';
			} else {
				// get the one discount
				$discount = $orderLineItemsDiscounts->first();
				// get the type of the one discount in the WireArray
				$orderLineItem->discountType = $discount->discountType;
				// get the value of the one discount in the WireArray
				$orderLineItem->discountValue = $discount->discountValue;
			}
		}

		// +++++++++++++
		// 3. TAXES
		// 'tax_name' => $sanitizer->text($value->taxName), // +++
		// TODO: STILL SAVE/SHOW THIS IF CUSTOMER IS TAX EXEMPT? yes, maybe, so as to show what customer is exempted from!
		$orderLineItem->taxName = $this->getOrderCountryTaxShortName();
		// ------------------
		// 'tax_percentage' => (float) $value->taxPercentage, // +++
		// @note: takes into account presence of country category tax override
		// @note can also use: $this->orderLineItemTaxRate
		$orderLineItem->taxPercentage = $this->taxPercent;

		// ------------------
		// 'tax_amount_total' => (float) $value->taxAmountTotal, // +++
		// if discount was applied, we get the tax applied after the discount was removed
		if (!empty($this->isDiscountApplied)) {
			// discount was applied: get the total tax applied on total price after discount
			$orderLineItem->taxAmountTotal = $this->taxAmountAfterDiscount;
		} else {
			// discount was NOT applied: get the tax applied on total price
			$orderLineItem->taxAmountTotal = $this->taxAmount;
		}


		// 'is_tax_override' => (int) $value->isTaxOverride, // +++
		// $orderLineItem->isTaxOverride = $this->isCategoryTaxOverridesApplicable() && $this->isOrderLineItemTaxable ? 1 : 0;
		$orderLineItem->isTaxOverride = $this->isTaxOverride;
		// +++++++++++++
		// 4. UNITS
		// 'unit_price' => (float) $value->unitPrice, // +++
		$orderLineItem->unitPrice = $unitPriceBeforeTax;

		// 'unit_price_with_tax' => (float) $value->unitPriceWithTax, // +++
		$orderLineItem->unitPriceWithTax = $unitPriceAfterTax;
		// ------------------
		// 'unit_price_discounted' => (float) $value->unitPriceDiscounted, // +++
		$orderLineItem->unitPriceDiscounted = $unitPriceWithDiscountBeforeTax;
		// ------------------
		// 'unit_price_discounted_with_tax' => (float) $value->unitPriceDiscountedWithTax, // +++
		$orderLineItem->unitPriceDiscountedWithTax = $unitPriceWithDiscountAfterTax;
		// +++++++++++++
		// 5. TOTALS
		// 'total_price' => (float) $value->totalPrice, // +++
		$orderLineItem->totalPrice = $totalPriceBeforeTax;
		// ------------------
		// 'total_price_with_tax' => (float) $value->totalPriceWithTax, // +++
		$orderLineItem->totalPriceWithTax = $totalPriceAfterTax;
		// ------------------
		// 'total_price_discounted' => (float) $value->totalPriceDiscounted, // +++
		$orderLineItem->totalPriceDiscounted = $totalPriceWithDiscountBeforeTax;
		// ------------------
		// 'total_price_discounted_with_tax' => (float) $value->totalPriceDiscountedWithTax, // +++
		$orderLineItem->totalPriceDiscountedWithTax = $totalPriceWithDiscountAfterTax;

		// ------------------

		// +++++++++++++
		// 6. SHIPMENT TODO: MAYBE NOT HERE? SHOULD BE SEPARATE IF ORDER IS COMPLETE
		// 'delivered_date' => $this->_sanitizeValue($value->deliveredDate), // +++
		// TODO! - FOR NOW SET AS CURRENT TIME; HOWEVER, IN FUTURE, CHECK WHOLE ORDER STATUS + IF ORDER LINE ITEM IS DOWNLOAD, ETC
		$orderLineItem->deliveredDate = time();
		// +++++++++++++
		// 7. STATUSES
		// 'status' => (int) $value->status, // +++
		// 'fulfilment_status' => (int) $value->fulfilmentStatus, // +++
		// 'payment_status' => (int) $value->paymentStatus, // +++
		// $orderLineItem->status = TODO!;
		// $orderLineItem->fulfilmentStatus = TODO!;
		// $orderLineItem->paymentStatus = TODO!;
		//-------------------------
		// return the orderLineItem with calculated values now processed
		return $orderLineItem;
	}

	# TAX #


	/**
	 * Get Tax Amount.
	 *
	 * @return mixed
	 */
	private function getTaxAmount() {
		############
		$taxAmountMoney = NULL;
		$totalDisplayPriceAmount = $this->getWholeMoneyAmount($this->totalDisplayPriceMoney);

		if ($this->isOrderLineItemTaxable) {
			if ($this->isPricesIncludeTaxes()) {
				// PRICES INCLUDE TAXES
				// ++++++++++++++++
				// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
				// this percent was used to compute the PRICE INC TAX
				$unitPriceIncTaxBasePercent = !empty($this->isTaxOverride) ? $this->taxPercent : $this->getShopCountryTaxRate();
				// $taxAmountMoney = $this->getTaxAmountFromPriceInclusiveTax($this->taxPercent, $totalDisplayPriceAmount);

				// check if customer country is in shop country
				// ---------------

				if (!empty($this->isOrderCustomerShippingAddressInShopCountry())) {
					// 3a. SHOP COUNTRY === CUSTOMER COUNTRY
					$taxAmountMoney = $this->getTaxAmountFromPriceInclusiveTax($unitPriceIncTaxBasePercent, $totalDisplayPriceAmount);
					// get the tax amount
					// NET IS BASED ON THE SHOP COUNTRY TOTAL DISPLAY PRICE!
					// WE THEN ADD TAX TO THAT FOR CUSTOMER COUNTRY
					$totalPriceBeforeTaxMoney = $this->getNetPriceIfPriceInclusiveTax($totalDisplayPriceAmount);
					$taxAmountMoney = $this->totalDisplayPriceMoney->subtract($totalPriceBeforeTaxMoney);
				} else {
					# ~~~~~~~~~~
					// 3b. SHOP COUNTRY !== CUSTOMER COUNTRY
					// GET PRODUCT UNIT PRICE BEFORE TAX
					// GET CUSTOMER COUNTRY TAX PERCENT
					// COMPUTE TAX AMOUNT FOR CUSTOMER COUNTRY
					// ADD TAX AMOUNT FOR CUSTOMER COUNTRY TO BASE PRICE FOR SHOP
					// NOTE: ABOVE TAX PERCENT FOR CUSTOMER COUNTRY ALREADY SET TO $this->taxPercent. @see: TraitPWCommerceTax::setOrderLineItemTaxRate
					# +++++++++++
					// DERIVE NET/PRE-TAX PRICE FROM  UNIT DISPLAY PRICE INC TAX
					// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
					// get the tax amount
					$net = $this->getNetPriceIfPriceInclusiveTax($totalDisplayPriceAmount, false);
					// get the tax amount
					// $taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, round($net, 2));
					$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $net);
				}


			} else {
				// PRICES DO NOT INCLUDE TAXES
				$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $totalDisplayPriceAmount);
				// -----
			}

		}

		// ---
		return $taxAmountMoney;
	}

	# UNITS #

	/**
	 * Get Unit Price Before Tax.
	 *
	 * @return mixed
	 */
	private function getUnitPriceBeforeTax() {
		// (i) schema 'unit_price'
		$unitPriceBeforeTaxMoney = NULL;
		if (empty($this->isOrderLineItemTaxable)) {
			// 1. LINE ITEM NOT TAXABLE
			// UNIT DISPLAY PRICE EQUALS THE PRE-TAX PRICE
			$unitPriceBeforeTax = $this->unitDisplayPrice;
			$unitPriceBeforeTaxMoney = $this->money($unitPriceBeforeTax);
		} else {
			// LINE ITEM TAXABLE
			# ------------
			if (empty($this->isPricesIncludeTaxes())) {
				// 2. PRICES DO NOT INCLUDE TAX
				// UNIT DISPLAY PRICE EQUALS THE PRE-TAX PRICE
				$unitPriceBeforeTax = $this->unitDisplayPrice;
				$unitPriceBeforeTaxMoney = $this->money($unitPriceBeforeTax);
			} else {
				// 3. PRICES INCLUDE TAX
				// DERIVE NET/PRE-TAX PRICE FROM  UNIT DISPLAY PRICE INC TAX
				// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
				$unitPriceBeforeTaxMoney = $this->getNetPriceIfPriceInclusiveTax($this->unitDisplayPrice);
			}
		}

		// ---
		return $unitPriceBeforeTaxMoney;
	}

	/**
	 * Get Unit Price After Tax.
	 *
	 * @return mixed
	 */
	private function getUnitPriceAfterTax() {
		// (iii) schema 'unit_price_with_tax'
		$unitPriceAfterTaxMoney = NULL;

		if (empty($this->isOrderLineItemTaxable)) {
			// 1. LINE ITEM NOT TAXABLE
			// UNIT DISPLAY PRICE EQUALS THE AFTER TAX PRICE
			$unitPriceAfterTax = $this->unitDisplayPrice;
			$unitPriceAfterTaxMoney = $this->money($unitPriceAfterTax);
		} else {
			// LINE ITEM TAXABLE
			# ------------
			if (empty($this->isPricesIncludeTaxes())) {
				// 2. PRICES DO NOT INCLUDE TAX
				// UNIT DISPLAY PRICE EQUALS THE PRE-TAX PRICE
				// GET TAX PERCENT FOR CUSTOMER COUNTRY
				// CAN BE AN OVERRIDE!
				// ADD TAX AMOUNT TO UNIT DISPLAY PRICE TO GET UNIT AFTER TAX PRICE

				$unitPriceBeforeTax = $this->unitDisplayPrice;
				$unitPriceBeforeTaxMoney = $this->money($unitPriceBeforeTax);

				// get the tax amount
				$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $unitPriceBeforeTax);
				// add the tax amount to the 'price ex tax'
				$unitPriceAfterTaxMoney = $unitPriceBeforeTaxMoney->add($taxAmountMoney);
			} else {
				// 3. PRICES INCLUDE TAX
				// check if customer country is in shop country
				// ---------------
				if (!empty($this->isOrderCustomerShippingAddressInShopCountry())) {
					// 3a. SHOP COUNTRY === CUSTOMER COUNTRY
					// UNIT DISPLAY PRICE EQUALS THE AFTER-TAX PRICE
					$unitPriceAfterTax = $this->unitDisplayPrice;
					$unitPriceAfterTaxMoney = $this->money($unitPriceAfterTax);
				} else {
					# ~~~~~~~~~~
					// 3b. SHOP COUNTRY !== CUSTOMER COUNTRY
					// GET PRODUCT UNIT PRICE BEFORE TAX
					// GET CUSTOMER COUNTRY TAX PERCENT
					// COMPUTE TAX AMOUNT FOR CUSTOMER COUNTRY
					// ADD TAX AMOUNT FOR CUSTOMER COUNTRY TO BASE PRICE FOR SHOP
					// NOTE: ABOVE TAX PERCENT FOR CUSTOMER COUNTRY ALREADY SET TO $this->taxPercent. @see: TraitPWCommerceTax::setOrderLineItemTaxRate
					# +++++++++++
					// DERIVE NET/PRE-TAX PRICE FROM  UNIT DISPLAY PRICE INC TAX
					// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
					// get the tax amount
					// $net = ($this->unitDisplayPrice / ($hundred + $unitPriceIncTaxBasePercent)) * $hundred;
					$net = $this->getNetPriceIfPriceInclusiveTax($this->unitDisplayPrice, false);
					$unitPriceBeforeTaxMoney = $this->money($net);

					// get the tax amount
					$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $net);
					// add the tax amount to the 'price ex tax'
					$unitPriceAfterTaxMoney = $unitPriceBeforeTaxMoney->add($taxAmountMoney);

				}
			}
		}

		// ---
		return $unitPriceAfterTaxMoney;

	}


	/**
	 * Get Unit Price With Discount Before Tax.
	 *
	 * @return mixed
	 */
	private function getUnitPriceWithDiscountBeforeTax() {
		// (ii) schema 'unit_price_discounted'
		// this was already computed. Since it has no tax, we just divide by quantity to get unit price discounted
		// note: if no discount, the total equates to total price before tax
		$unitPriceWithDiscountBeforeTaxMoney = $this->totalPriceWithDiscountBeforeTaxMoney->divide($this->quantity);
		// ---
		return $unitPriceWithDiscountBeforeTaxMoney;
	}

	/**
	 * Get Unit Price With Discount After Tax.
	 *
	 * @return mixed
	 */
	private function getUnitPriceWithDiscountAfterTax() {
		// (iv) schema 'unit_price_discounted_with_tax'
		// if no tax, this will be equal to 'discounted BEFORE tax money'
		$unitPriceWithDiscountAfterTaxMoney = $this->unitPriceWithDiscountBeforeTaxMoney;
		if ($this->isOrderLineItemTaxable) {
			// LINE ITEM IS TAXABLE
			$unitPriceWithDiscountBeforeTaxAmount = $this->getWholeMoneyAmount($this->unitPriceWithDiscountBeforeTaxMoney);
			// get the tax amount
			$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $unitPriceWithDiscountBeforeTaxAmount);
			// add the tax amount to the 'discounted price ex tax'
			$unitPriceWithDiscountAfterTaxMoney = $this->unitPriceWithDiscountBeforeTaxMoney->add($taxAmountMoney);
		}
		// ---
		return $unitPriceWithDiscountAfterTaxMoney;
	}

	# TOTALS #


	/**
	 * Get Total Price Before Tax.
	 *
	 * @return mixed
	 */
	private function getTotalPriceBeforeTax() {
		// NOTE: $this->unitPriceBeforeTaxMoney has already determined if price was ex or inc tax
		// could also use pwcommerce_price_total here, but would have to go through the inc vs ex tax with that
		// (i) schema 'total_price'
		$totalPriceBeforeTaxMoney = NULL;
		if (empty($this->isOrderLineItemTaxable)) {
			// 1. LINE ITEM NOT TAXABLE
			// TOTAL DISPLAY PRICE EQUALS THE TOTAL PRE-TAX PRICE
			$totalPriceBeforeTax = $this->totalDisplayPrice;
			$totalPriceBeforeTaxMoney = $this->money($totalPriceBeforeTax);
		} else {
			// LINE ITEM TAXABLE
			# ------------
			if (empty($this->isPricesIncludeTaxes())) {
				// 2. PRICES DO NOT INCLUDE TAX
				// TOTAL DISPLAY PRICE EQUALS THE TOTAL PRE-TAX PRICE
				$totalPriceBeforeTax = $this->totalDisplayPrice;
				$totalPriceBeforeTaxMoney = $this->money($totalPriceBeforeTax);
			} else {
				// 3. PRICES INCLUDE TAX
				// check if customer country is in shop country
				// ---------------
				if (!empty($this->isOrderCustomerShippingAddressInShopCountry())) {
					// 3a. SHOP COUNTRY === CUSTOMER COUNTRY
					// TOTAL DISPLAY PRICE EQUALS THE TOTAL AFTER-TAX PRICE
					// WE HAVE ALREADY COMPUTED $this->unitPriceAfterTaxMoney
					// THAT HAS TAKEN INTO ACCOUNT 'if customer country is in shop country'
					// IT HAS ALSO DETERMINED THE CORRECT '$unitPriceIncTaxBasePercent'
					// i.e. if override tax percent or shop country standard/base tax percent
					// HERE WE SUBTRACT THE TAX TOTAL FROM BY QUANTITY TO GET TOTAL
					// $totalPriceAfterTax2 = $this->totalDisplayPrice;
					// $totalPriceAfterTaxMoney2 = $this->money($totalPriceAfterTax2);

					$totalPriceBeforeTaxMoney = $this->totalDisplayPriceMoney->subtract($this->taxAmountMoney);

				} else {
					# ~~~~~~~~~~
					// 3b. SHOP COUNTRY !== CUSTOMER COUNTRY
					// GET PRODUCT UNIT PRICE BEFORE TAX
					// GET CUSTOMER COUNTRY TAX PERCENT
					// COMPUTE TAX AMOUNT FOR CUSTOMER COUNTRY
					// ADD TAX AMOUNT FOR CUSTOMER COUNTRY TO BASE PRICE FOR SHOP
					// NOTE: ABOVE TAX PERCENT FOR CUSTOMER COUNTRY ALREADY SET TO $this->taxPercent. @see: TraitPWCommerceTax::setOrderLineItemTaxRate
					# +++++++++++
					// DERIVE NET/PRE-TAX PRICE FROM  UNIT DISPLAY PRICE INC TAX
					// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
					$totalPriceBeforeTaxMoney = $this->getNetPriceIfPriceInclusiveTax($this->totalDisplayPrice);

				}

			}
		}


		// ---
		return $totalPriceBeforeTaxMoney;
	}


	/**
	 * Get Total Price After Tax.
	 *
	 * @return mixed
	 */
	private function getTotalPriceAfterTax() {
		// (iii) schema 'total_price_with_tax'
		$totalPriceAfterTaxMoney = NULL;

		if (empty($this->isOrderLineItemTaxable)) {
			// 1. LINE ITEM NOT TAXABLE
			// TOTAL DISPLAY PRICE EQUALS THE AFTER TAX PRICE
			$totalPriceAfterTax = $this->totalDisplayPrice;
			$totalPriceAfterTaxMoney = $this->money($totalPriceAfterTax);
		} else {
			// LINE ITEM TAXABLE
			# ------------
			if (empty($this->isPricesIncludeTaxes())) {
				// 2. PRICES DO NOT INCLUDE TAX
				// WE HAVE ALREADY COMPUTED $this->unitPriceAfterTaxMoney
				// HERE WE MULTIPLY BY QUANTITY TO GET TOTAL
				// WE USE THE 'CALCULATION PER PRODUCT UNIT' approach
				// TODO: IN FUTUTE CAN MAKE CONFIGURABLE TO USE THIS OR 'CALCULATION BY PRODUCT LINE'. in that case, we would do: '$this->unitPriceBeforeTaxMoney * $this->quantity THEN ADD TAX TO THE TOTAL

				// get total price inc tax by multiplying total inc tax by quantity
				$totalPriceAfterTaxMoney = $this->unitPriceAfterTaxMoney->multiply($this->quantity);
			} else {
				// 3. PRICES INCLUDE TAX
				// check if customer country is in shop country
				// ---------------
				if (!empty($this->isOrderCustomerShippingAddressInShopCountry())) {
					// 3a. SHOP COUNTRY === CUSTOMER COUNTRY
					// TOTAL DISPLAY PRICE EQUALS THE TOTAL AFTER-TAX PRICE
					// WE HAVE ALREADY COMPUTED $this->unitPriceAfterTaxMoney
					// THAT HAS TAKEN INTO ACCOUNT 'if customer country is in shop country'
					// IT HAS ALSO DETERMINED THE CORRECT '$unitPriceIncTaxBasePercent'
					// i.e. if override tax percent or shop country standard/base tax percent
					// HERE WE SUBTRACT THE TAX TOTAL FROM BY QUANTITY TO GET TOTAL
					$totalPriceAfterTax = $this->totalDisplayPrice;
					$totalPriceAfterTaxMoney = $this->money($totalPriceAfterTax);
				} else {
					# ~~~~~~~~~~
					// 3b. SHOP COUNTRY !== CUSTOMER COUNTRY
					$totalPriceBeforeTaxMoney = $this->getNetPriceIfPriceInclusiveTax($this->totalDisplayPrice);
					$totalPriceAfterTaxMoney = $totalPriceBeforeTaxMoney->add($this->taxAmountMoney);
				}
			}


		}

		// ---
		return $totalPriceAfterTaxMoney;

	}

	/**
	 * Get Total Price With Discount Before Tax.
	 *
	 * @return mixed
	 */
	private function getTotalPriceWithDiscountBeforeTax() {
		// (ii) schema 'total_price_discounted'
		// if no discount, discounted total before tax equates to total before tax
		// TODO? FOR NOW JUST NULL IT? no; we will check Money::equals
		// $totalPriceWithDiscountBeforeTaxMoney = NULL;
		$totalPriceWithDiscountBeforeTaxMoney = $this->totalPriceBeforeTaxMoney;
		if (!empty($this->totalDiscountsAmountMoney)) {
			$totalPriceWithDiscountBeforeTaxMoney = $this->totalPriceBeforeTaxMoney->subtract($this->totalDiscountsAmountMoney);
		}
		// ---
		return $totalPriceWithDiscountBeforeTaxMoney;
	}

	/**
	 * Get Total Price With Discount After Tax.
	 *
	 * @return mixed
	 */
	private function getTotalPriceWithDiscountAfterTax() {
		// (iv) schema 'total_price_discounted_with_tax'
		// if no tax, this will be equal to 'discounted BEFORE tax money'
		$totalPriceWithDiscountAfterTaxMoney = $this->totalPriceWithDiscountBeforeTaxMoney;
		if ($this->isOrderLineItemTaxable) {
			// LINE ITEM IS TAXABLE
			$totalPriceWithDiscountBeforeTaxAmount = $this->getWholeMoneyAmount($this->totalPriceWithDiscountBeforeTaxMoney);
			// get the tax amount
			$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $totalPriceWithDiscountBeforeTaxAmount);
			// add the tax amount to the 'discounted price ex tax'
			$totalPriceWithDiscountAfterTaxMoney = $this->totalPriceWithDiscountBeforeTaxMoney->add($taxAmountMoney);
			// track the final tax money(post discount)
			$this->taxAmountAfterDiscountMoney = $taxAmountMoney;
		}
		// ---
		return $totalPriceWithDiscountAfterTaxMoney;
	}

	# DISCOUNT #
	/**
	 * Get Total Discounts Amount.
	 *
	 * @return mixed
	 */
	private function getTotalDiscountsAmount() {
		// schema 'total_discounts'
		// @note: this is the total amout of discounts that have been applied to the order line item
		// @note: it does not include the NET price; just the discount only!
		// 5. TOTALS
		// NOTE TraitPWCommerceUtilitiesDiscount::getOrderLineItemDiscountsAmount
		$totalDiscountsAmountMoney = $this->getOrderLineItemDiscountsAmount();
		// ---
		return $totalDiscountsAmountMoney;
	}


	# UTILITIES #

	/**
	 * Get Net Price If Price Inclusive Tax.
	 *
	 * @param mixed $priceInclusiveTax
	 * @param bool $isReturnMoney
	 * @return mixed
	 */
	private function getNetPriceIfPriceInclusiveTax($priceInclusiveTax, bool $isReturnMoney = true) {
		// if unit price has tax included, get PRICE PORTION WITHOUT TAX
		// Net: (Amount / 100+TAX PERCENT) * 100
		// e.g. if VAT is 20%, Net: (Amount / 120) * 100
		# ++++++++++++++++
		$hundred = PwCommerce::HUNDRED;
		// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
		$unitPriceIncTaxBasePercent = !empty($this->isTaxOverride) ? $this->taxPercent : $this->getShopCountryTaxRate();
		// get the tax amount
		// NET IS BASED ON THE SHOP COUNTRY TOTAL DISPLAY PRICE!
		// WE THEN ADD TAX TO THAT FOR CUSTOMER COUNTRY
		// $net = ($priceInclusiveTax / ($hundred + $unitPriceIncTaxBasePercent)) * $hundred;
		$netAmount = ($priceInclusiveTax / ($hundred + $unitPriceIncTaxBasePercent)) * $hundred;
		$netAmount = round($netAmount, 2);
		if (!empty($isReturnMoney)) {
			$netAmount = $this->money($netAmount);
		}

		// ---------
		return $netAmount;
	}


	################

	/**
	 * Get Order Line Item Delivered Date.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemDeliveredDate() {
		// TODO: UNSURE OF THIS ONE? GET? SET?
		// 6. SHIPMENT TODO: MAYBE NOT HERE? SHOULD BE SEPARATE IF ORDER IS COMPLETE
		// 'delivered_date' => $this->_sanitizeValue($value->deliveredDate), // +++
	}


	/**
	 * Get Order Line Item Product.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemProduct() {
		return $this->productOrVariantPage;
	}

	/**
	 * Get Order Line Item Product Settings.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemProductSettings() {
		$orderLineItemProduct = $this->getOrderLineItemProduct();

		return $orderLineItemProduct['settings'];
	}

	 */
	public function getOrderLineItemCalculatedValues(array $options) {


		// #######################
		# SETUP
		// set as temporary class properties for easier retrieval in various methods
		// $this->orderLineItem = $orderLineItem;
		// $this->productOrVariantPage = $productOrVariantPage;
		// $this->shippingCountry = $shippingCountry;
		// $this->isChargeTaxesManualExemption = $isChargeTaxesManualExemption;
		// $this->isCustomerTaxExempt = $isCustomerTaxExempt;
		// $this->isProcessTruePrice = $isProcessTruePrice;


		/** @var WireData $this->orderLineItem */
		$this->orderLineItem = $options['order_line_item'];
		$this->productOrVariantPage = $options['product_or_variant_page'];
		/** @var Page $this->shippingCountry */
		$this->shippingCountry = $options['shipping_country'];
		$this->isChargeTaxesManualExemption = $options['is_charge_taxes_manual_exemption'];
		$this->isCustomerTaxExempt = $options['is_customer_tax_exempt'];
		$this->isProcessTruePrice = $options['is_process_order_line_item_product_true_price'];
		$orderLineItem = $this->orderLineItem;

		// +++++++++
		// =================
		// SET ORDER LINE ITEM TAXABLE SETTING
		$this->isOrderLineItemTaxable = $this->isOrderLineItemTaxable();
		// SET TAX RATE IF APPLICABLE
		$this->setOrderLineItemTaxRate();

		// NOTE: THESE TWO SET ABOVE BY $this->setOrderLineItemTaxRate()
		$this->taxPercent = $this->orderLineItemTaxPercent;
		$this->taxRate = $this->orderLineItemTaxRate;
		$this->isTaxOverride = $this->isCategoryTaxOverridesApplicable() && $this->isOrderLineItemTaxable ? 1 : 0;
		$this->quantity = $this->orderLineItem->quantity;
		$this->unitDisplayPrice = $this->orderLineItem->unitPrice;
		$this->totalDisplayPrice = $this->orderLineItem->totalPrice;

		// #######################
		# COMPUTATIONS
		## ********** SET CALCULABLE VALUES FOR ORDER LINE ITEM ********** ##

		# SET VALUES FROM DISPLAY PRICES
		// might or might not include tax
		$this->unitDisplayPriceMoney = $this->money($this->unitDisplayPrice);
		// NOTE - WE WORK WITH NET PRICE BELOW! this is to cater for price inc tax situations
		$this->totalDisplayPriceMoney = $this->unitDisplayPriceMoney->multiply($this->quantity);
		// $totalPrice = strval($this->getMoneyTotalAsWholeMoneyAmount($unitDisplayPrice, $quantity));

		# NOTE: values with tax have the suffix 'WithTaxXXX'. Those without don't get this suffix and are considered 'net', unless stated otherwise

		// SET DEFAULTS
		// ++++++++
		$this->taxAmount = 0; // note: discounts not taxed!
		$this->taxAmountMoney = $this->getTaxAmount();
		if (!empty($this->taxAmountMoney)) {
			$this->taxAmount = $this->getWholeMoneyAmount($this->taxAmountMoney);
		}

		// ++++++++
		// (i) schema 'unit_price'
		$unitPriceBeforeTax = 0;
		$this->unitPriceBeforeTaxMoney = $this->getUnitPriceBeforeTax();
		if (!empty($this->unitPriceBeforeTaxMoney)) {
			$unitPriceBeforeTax = $this->getWholeMoneyAmount($this->unitPriceBeforeTaxMoney);
		}

		// (iii) schema 'unit_price_with_tax'
		$unitPriceAfterTax = 0;
		$this->unitPriceAfterTaxMoney = $this->getUnitPriceAfterTax();
		if (!empty($this->unitPriceAfterTaxMoney)) {
			$unitPriceAfterTax = $this->getWholeMoneyAmount($this->unitPriceAfterTaxMoney);
		}


		// ++++++++

		// (i) schema 'total_price'
		$totalPriceBeforeTax = 0;
		$this->totalPriceBeforeTaxMoney = $this->getTotalPriceBeforeTax();
		if (!empty($this->totalPriceBeforeTaxMoney)) {
			$totalPriceBeforeTax = $this->getWholeMoneyAmount($this->totalPriceBeforeTaxMoney);
		}

		// (iii) schema 'total_price_with_tax'
		$totalPriceAfterTax = 0;
		$this->totalPriceAfterTaxMoney = $this->getTotalPriceAfterTax();
		if (!empty($this->totalPriceAfterTaxMoney)) {
			$totalPriceAfterTax = $this->getWholeMoneyAmount($this->totalPriceAfterTaxMoney);
		}


		// ++++++++

		// schema 'total_discounts'
		$totalDiscountsAmount = 0;
		$this->totalDiscountsAmountMoney = $this->getTotalDiscountsAmount();
		if (!empty($this->totalDiscountsAmountMoney)) {
			$totalDiscountsAmount = $this->getWholeMoneyAmount($this->totalDiscountsAmountMoney);
		}


		// ++++++++
		// (ii) schema 'total_price_discounted'
		// if no discount, discounted total before tax equates to total before tax
		$totalPriceWithDiscountBeforeTax = $totalPriceBeforeTax;
		$this->totalPriceWithDiscountBeforeTaxMoney = $this->getTotalPriceWithDiscountBeforeTax();
		if (!empty($this->totalPriceWithDiscountBeforeTaxMoney)) {
			$totalPriceWithDiscountBeforeTax = $this->getWholeMoneyAmount($this->totalPriceWithDiscountBeforeTaxMoney);
		}

		// (iv) schema 'total_price_discounted_with_tax'
		// if no discount, discounted total AFTER tax equates to total AFTER tax
		$totalPriceWithDiscountAfterTax = $totalPriceAfterTax;
		if ($this->totalPriceWithDiscountBeforeTaxMoney->lessThan($this->totalPriceBeforeTaxMoney)) {
			// DISCOUNT WAS APPLIED (BEFORE TAX)
			$this->isDiscountApplied = true;
			// get discounted price with tax
			$this->totalPriceWithDiscountAfterTaxMoney = $this->getTotalPriceWithDiscountAfterTax();
			if (!empty($this->totalPriceWithDiscountAfterTaxMoney)) {
				$totalPriceWithDiscountAfterTax = $this->getWholeMoneyAmount($this->totalPriceWithDiscountAfterTaxMoney);
			}
		}

		// track final price applied after discount removed
		// $this->taxAmountAfterDiscountMoney was set in getTotalPriceWithDiscountAfterTax()
		// if line item is taxable
		$this->taxAmountAfterDiscount = 0; // note: discounts not taxed!
		if (!empty($this->isDiscountApplied) && $this->isOrderLineItemTaxable) {
			$this->taxAmountAfterDiscount = $this->getWholeMoneyAmount($this->taxAmountAfterDiscountMoney);
		}

		// ++++++++
		// (ii) schema 'unit_price_discounted'
		// if no discount, discounted unit before tax equates to unit before tax
		$unitPriceWithDiscountBeforeTax = $unitPriceBeforeTax;
		$this->unitPriceWithDiscountBeforeTaxMoney = $this->getUnitPriceWithDiscountBeforeTax();
		if (!empty($this->unitPriceWithDiscountBeforeTaxMoney)) {
			$unitPriceWithDiscountBeforeTax = $this->getWholeMoneyAmount($this->unitPriceWithDiscountBeforeTaxMoney);
		}


		// (iv) schema 'unit_price_discounted_with_tax'
		// if no discount, discounted unit AFTER tax equates to unit AFTER tax
		$unitPriceWithDiscountAfterTax = $unitPriceAfterTax;
		if ($this->unitPriceWithDiscountBeforeTaxMoney->lessThan($this->unitPriceBeforeTaxMoney)) {
			// DISCOUNT WAS APPLIED (BEFORE TAX)
			// get discounted price with tax
			$this->unitPriceWithDiscountAfterTaxMoney = $this->getUnitPriceWithDiscountAfterTax();
			if (!empty($this->unitPriceWithDiscountAfterTaxMoney)) {
				$unitPriceWithDiscountAfterTax = $this->getWholeMoneyAmount($this->unitPriceWithDiscountAfterTaxMoney);
			}
		}

		// ==========

		// 1.PRODUCT
		// ++ NONE ++
		// +++++++++++++
		// 2. DISCOUNTS
		// 'discount_amount' => (float) $value->discountAmount, // +++

		// ++++++++++++++++++
		$orderLineItem->discountAmount = $totalDiscountsAmount;
		/** @var WireArray $orderLineItemsDiscounts */
		$orderLineItemsDiscounts = $this->orderLineItem->discounts;
		if ($orderLineItemsDiscounts instanceof WireArray && !empty($orderLineItemsDiscounts->count())) {

			$orderLineItem->totalDiscounts = $orderLineItemsDiscounts->count();
			// ------
			if (!empty($this->orderLineItem->isApplyMultipleDiscounts)) {
				// multiple discounts were applied
				$orderLineItem->discountType = 'multiple';
			} else {
				// get the one discount
				$discount = $orderLineItemsDiscounts->first();
				// get the type of the one discount in the WireArray
				$orderLineItem->discountType = $discount->discountType;
				// get the value of the one discount in the WireArray
				$orderLineItem->discountValue = $discount->discountValue;
			}
		}

		// +++++++++++++
		// 3. TAXES
		// 'tax_name' => $sanitizer->text($value->taxName), // +++
		// TODO: STILL SAVE/SHOW THIS IF CUSTOMER IS TAX EXEMPT? yes, maybe, so as to show what customer is exempted from!
		$orderLineItem->taxName = $this->getOrderCountryTaxShortName();
		// ------------------
		// 'tax_percentage' => (float) $value->taxPercentage, // +++
		// @note: takes into account presence of country category tax override
		// @note can also use: $this->orderLineItemTaxRate
		$orderLineItem->taxPercentage = $this->taxPercent;

		// ------------------
		// 'tax_amount_total' => (float) $value->taxAmountTotal, // +++
		// if discount was applied, we get the tax applied after the discount was removed
		if (!empty($this->isDiscountApplied)) {
			// discount was applied: get the total tax applied on total price after discount
			$orderLineItem->taxAmountTotal = $this->taxAmountAfterDiscount;
		} else {
			// discount was NOT applied: get the tax applied on total price
			$orderLineItem->taxAmountTotal = $this->taxAmount;
		}


		// 'is_tax_override' => (int) $value->isTaxOverride, // +++
		// $orderLineItem->isTaxOverride = $this->isCategoryTaxOverridesApplicable() && $this->isOrderLineItemTaxable ? 1 : 0;
		$orderLineItem->isTaxOverride = $this->isTaxOverride;
		// +++++++++++++
		// 4. UNITS
		// 'unit_price' => (float) $value->unitPrice, // +++
		$orderLineItem->unitPrice = $unitPriceBeforeTax;

		// 'unit_price_with_tax' => (float) $value->unitPriceWithTax, // +++
		$orderLineItem->unitPriceWithTax = $unitPriceAfterTax;
		// ------------------
		// 'unit_price_discounted' => (float) $value->unitPriceDiscounted, // +++
		$orderLineItem->unitPriceDiscounted = $unitPriceWithDiscountBeforeTax;
		// ------------------
		// 'unit_price_discounted_with_tax' => (float) $value->unitPriceDiscountedWithTax, // +++
		$orderLineItem->unitPriceDiscountedWithTax = $unitPriceWithDiscountAfterTax;
		// +++++++++++++
		// 5. TOTALS
		// 'total_price' => (float) $value->totalPrice, // +++
		$orderLineItem->totalPrice = $totalPriceBeforeTax;
		// ------------------
		// 'total_price_with_tax' => (float) $value->totalPriceWithTax, // +++
		$orderLineItem->totalPriceWithTax = $totalPriceAfterTax;
		// ------------------
		// 'total_price_discounted' => (float) $value->totalPriceDiscounted, // +++
		$orderLineItem->totalPriceDiscounted = $totalPriceWithDiscountBeforeTax;
		// ------------------
		// 'total_price_discounted_with_tax' => (float) $value->totalPriceDiscountedWithTax, // +++
		$orderLineItem->totalPriceDiscountedWithTax = $totalPriceWithDiscountAfterTax;

		// ------------------

		// +++++++++++++
		// 6. SHIPMENT TODO: MAYBE NOT HERE? SHOULD BE SEPARATE IF ORDER IS COMPLETE
		// 'delivered_date' => $this->_sanitizeValue($value->deliveredDate), // +++
		// TODO! - FOR NOW SET AS CURRENT TIME; HOWEVER, IN FUTURE, CHECK WHOLE ORDER STATUS + IF ORDER LINE ITEM IS DOWNLOAD, ETC
		$orderLineItem->deliveredDate = time();
		// +++++++++++++
		// 7. STATUSES
		// 'status' => (int) $value->status, // +++
		// 'fulfilment_status' => (int) $value->fulfilmentStatus, // +++
		// 'payment_status' => (int) $value->paymentStatus, // +++
		// $orderLineItem->status = TODO!;
		// $orderLineItem->fulfilmentStatus = TODO!;
		// $orderLineItem->paymentStatus = TODO!;
		//-------------------------
		// return the orderLineItem with calculated values now processed
		return $orderLineItem;
	}

	# TAX #


	/**
	 * Get Tax Amount.
	 *
	 * @return mixed
	 */
	private function getTaxAmount() {
		############
		$taxAmountMoney = NULL;
		$totalDisplayPriceAmount = $this->getWholeMoneyAmount($this->totalDisplayPriceMoney);

		if ($this->isOrderLineItemTaxable) {
			if ($this->isPricesIncludeTaxes()) {
				// PRICES INCLUDE TAXES
				// ++++++++++++++++
				// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
				// this percent was used to compute the PRICE INC TAX
				$unitPriceIncTaxBasePercent = !empty($this->isTaxOverride) ? $this->taxPercent : $this->getShopCountryTaxRate();
				// $taxAmountMoney = $this->getTaxAmountFromPriceInclusiveTax($this->taxPercent, $totalDisplayPriceAmount);

				// check if customer country is in shop country
				// ---------------

				if (!empty($this->isOrderCustomerShippingAddressInShopCountry())) {
					// 3a. SHOP COUNTRY === CUSTOMER COUNTRY
					$taxAmountMoney = $this->getTaxAmountFromPriceInclusiveTax($unitPriceIncTaxBasePercent, $totalDisplayPriceAmount);
					// get the tax amount
					// NET IS BASED ON THE SHOP COUNTRY TOTAL DISPLAY PRICE!
					// WE THEN ADD TAX TO THAT FOR CUSTOMER COUNTRY
					$totalPriceBeforeTaxMoney = $this->getNetPriceIfPriceInclusiveTax($totalDisplayPriceAmount);
					$taxAmountMoney = $this->totalDisplayPriceMoney->subtract($totalPriceBeforeTaxMoney);
				} else {
					# ~~~~~~~~~~
					// 3b. SHOP COUNTRY !== CUSTOMER COUNTRY
					// GET PRODUCT UNIT PRICE BEFORE TAX
					// GET CUSTOMER COUNTRY TAX PERCENT
					// COMPUTE TAX AMOUNT FOR CUSTOMER COUNTRY
					// ADD TAX AMOUNT FOR CUSTOMER COUNTRY TO BASE PRICE FOR SHOP
					// NOTE: ABOVE TAX PERCENT FOR CUSTOMER COUNTRY ALREADY SET TO $this->taxPercent. @see: TraitPWCommerceTax::setOrderLineItemTaxRate
					# +++++++++++
					// DERIVE NET/PRE-TAX PRICE FROM  UNIT DISPLAY PRICE INC TAX
					// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
					// get the tax amount
					$net = $this->getNetPriceIfPriceInclusiveTax($totalDisplayPriceAmount, false);
					// get the tax amount
					// $taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, round($net, 2));
					$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $net);
				}


			} else {
				// PRICES DO NOT INCLUDE TAXES
				$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $totalDisplayPriceAmount);
				// -----
			}

		}

		// ---
		return $taxAmountMoney;
	}

	# UNITS #

	/**
	 * Get Unit Price Before Tax.
	 *
	 * @return mixed
	 */
	private function getUnitPriceBeforeTax() {
		// (i) schema 'unit_price'
		$unitPriceBeforeTaxMoney = NULL;
		if (empty($this->isOrderLineItemTaxable)) {
			// 1. LINE ITEM NOT TAXABLE
			// UNIT DISPLAY PRICE EQUALS THE PRE-TAX PRICE
			$unitPriceBeforeTax = $this->unitDisplayPrice;
			$unitPriceBeforeTaxMoney = $this->money($unitPriceBeforeTax);
		} else {
			// LINE ITEM TAXABLE
			# ------------
			if (empty($this->isPricesIncludeTaxes())) {
				// 2. PRICES DO NOT INCLUDE TAX
				// UNIT DISPLAY PRICE EQUALS THE PRE-TAX PRICE
				$unitPriceBeforeTax = $this->unitDisplayPrice;
				$unitPriceBeforeTaxMoney = $this->money($unitPriceBeforeTax);
			} else {
				// 3. PRICES INCLUDE TAX
				// DERIVE NET/PRE-TAX PRICE FROM  UNIT DISPLAY PRICE INC TAX
				// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
				$unitPriceBeforeTaxMoney = $this->getNetPriceIfPriceInclusiveTax($this->unitDisplayPrice);
			}
		}

		// ---
		return $unitPriceBeforeTaxMoney;
	}

	/**
	 * Get Unit Price After Tax.
	 *
	 * @return mixed
	 */
	private function getUnitPriceAfterTax() {
		// (iii) schema 'unit_price_with_tax'
		$unitPriceAfterTaxMoney = NULL;

		if (empty($this->isOrderLineItemTaxable)) {
			// 1. LINE ITEM NOT TAXABLE
			// UNIT DISPLAY PRICE EQUALS THE AFTER TAX PRICE
			$unitPriceAfterTax = $this->unitDisplayPrice;
			$unitPriceAfterTaxMoney = $this->money($unitPriceAfterTax);
		} else {
			// LINE ITEM TAXABLE
			# ------------
			if (empty($this->isPricesIncludeTaxes())) {
				// 2. PRICES DO NOT INCLUDE TAX
				// UNIT DISPLAY PRICE EQUALS THE PRE-TAX PRICE
				// GET TAX PERCENT FOR CUSTOMER COUNTRY
				// CAN BE AN OVERRIDE!
				// ADD TAX AMOUNT TO UNIT DISPLAY PRICE TO GET UNIT AFTER TAX PRICE

				$unitPriceBeforeTax = $this->unitDisplayPrice;
				$unitPriceBeforeTaxMoney = $this->money($unitPriceBeforeTax);

				// get the tax amount
				$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $unitPriceBeforeTax);
				// add the tax amount to the 'price ex tax'
				$unitPriceAfterTaxMoney = $unitPriceBeforeTaxMoney->add($taxAmountMoney);
			} else {
				// 3. PRICES INCLUDE TAX
				// check if customer country is in shop country
				// ---------------
				if (!empty($this->isOrderCustomerShippingAddressInShopCountry())) {
					// 3a. SHOP COUNTRY === CUSTOMER COUNTRY
					// UNIT DISPLAY PRICE EQUALS THE AFTER-TAX PRICE
					$unitPriceAfterTax = $this->unitDisplayPrice;
					$unitPriceAfterTaxMoney = $this->money($unitPriceAfterTax);
				} else {
					# ~~~~~~~~~~
					// 3b. SHOP COUNTRY !== CUSTOMER COUNTRY
					// GET PRODUCT UNIT PRICE BEFORE TAX
					// GET CUSTOMER COUNTRY TAX PERCENT
					// COMPUTE TAX AMOUNT FOR CUSTOMER COUNTRY
					// ADD TAX AMOUNT FOR CUSTOMER COUNTRY TO BASE PRICE FOR SHOP
					// NOTE: ABOVE TAX PERCENT FOR CUSTOMER COUNTRY ALREADY SET TO $this->taxPercent. @see: TraitPWCommerceTax::setOrderLineItemTaxRate
					# +++++++++++
					// DERIVE NET/PRE-TAX PRICE FROM  UNIT DISPLAY PRICE INC TAX
					// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
					// get the tax amount
					// $net = ($this->unitDisplayPrice / ($hundred + $unitPriceIncTaxBasePercent)) * $hundred;
					$net = $this->getNetPriceIfPriceInclusiveTax($this->unitDisplayPrice, false);
					$unitPriceBeforeTaxMoney = $this->money($net);

					// get the tax amount
					$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $net);
					// add the tax amount to the 'price ex tax'
					$unitPriceAfterTaxMoney = $unitPriceBeforeTaxMoney->add($taxAmountMoney);

				}
			}
		}

		// ---
		return $unitPriceAfterTaxMoney;

	}


	/**
	 * Get Unit Price With Discount Before Tax.
	 *
	 * @return mixed
	 */
	private function getUnitPriceWithDiscountBeforeTax() {
		// (ii) schema 'unit_price_discounted'
		// this was already computed. Since it has no tax, we just divide by quantity to get unit price discounted
		// note: if no discount, the total equates to total price before tax
		$unitPriceWithDiscountBeforeTaxMoney = $this->totalPriceWithDiscountBeforeTaxMoney->divide($this->quantity);
		// ---
		return $unitPriceWithDiscountBeforeTaxMoney;
	}

	/**
	 * Get Unit Price With Discount After Tax.
	 *
	 * @return mixed
	 */
	private function getUnitPriceWithDiscountAfterTax() {
		// (iv) schema 'unit_price_discounted_with_tax'
		// if no tax, this will be equal to 'discounted BEFORE tax money'
		$unitPriceWithDiscountAfterTaxMoney = $this->unitPriceWithDiscountBeforeTaxMoney;
		if ($this->isOrderLineItemTaxable) {
			// LINE ITEM IS TAXABLE
			$unitPriceWithDiscountBeforeTaxAmount = $this->getWholeMoneyAmount($this->unitPriceWithDiscountBeforeTaxMoney);
			// get the tax amount
			$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $unitPriceWithDiscountBeforeTaxAmount);
			// add the tax amount to the 'discounted price ex tax'
			$unitPriceWithDiscountAfterTaxMoney = $this->unitPriceWithDiscountBeforeTaxMoney->add($taxAmountMoney);
		}
		// ---
		return $unitPriceWithDiscountAfterTaxMoney;
	}

	# TOTALS #


	/**
	 * Get Total Price Before Tax.
	 *
	 * @return mixed
	 */
	private function getTotalPriceBeforeTax() {
		// NOTE: $this->unitPriceBeforeTaxMoney has already determined if price was ex or inc tax
		// could also use pwcommerce_price_total here, but would have to go through the inc vs ex tax with that
		// (i) schema 'total_price'
		$totalPriceBeforeTaxMoney = NULL;
		if (empty($this->isOrderLineItemTaxable)) {
			// 1. LINE ITEM NOT TAXABLE
			// TOTAL DISPLAY PRICE EQUALS THE TOTAL PRE-TAX PRICE
			$totalPriceBeforeTax = $this->totalDisplayPrice;
			$totalPriceBeforeTaxMoney = $this->money($totalPriceBeforeTax);
		} else {
			// LINE ITEM TAXABLE
			# ------------
			if (empty($this->isPricesIncludeTaxes())) {
				// 2. PRICES DO NOT INCLUDE TAX
				// TOTAL DISPLAY PRICE EQUALS THE TOTAL PRE-TAX PRICE
				$totalPriceBeforeTax = $this->totalDisplayPrice;
				$totalPriceBeforeTaxMoney = $this->money($totalPriceBeforeTax);
			} else {
				// 3. PRICES INCLUDE TAX
				// check if customer country is in shop country
				// ---------------
				if (!empty($this->isOrderCustomerShippingAddressInShopCountry())) {
					// 3a. SHOP COUNTRY === CUSTOMER COUNTRY
					// TOTAL DISPLAY PRICE EQUALS THE TOTAL AFTER-TAX PRICE
					// WE HAVE ALREADY COMPUTED $this->unitPriceAfterTaxMoney
					// THAT HAS TAKEN INTO ACCOUNT 'if customer country is in shop country'
					// IT HAS ALSO DETERMINED THE CORRECT '$unitPriceIncTaxBasePercent'
					// i.e. if override tax percent or shop country standard/base tax percent
					// HERE WE SUBTRACT THE TAX TOTAL FROM BY QUANTITY TO GET TOTAL
					// $totalPriceAfterTax2 = $this->totalDisplayPrice;
					// $totalPriceAfterTaxMoney2 = $this->money($totalPriceAfterTax2);

					$totalPriceBeforeTaxMoney = $this->totalDisplayPriceMoney->subtract($this->taxAmountMoney);

				} else {
					# ~~~~~~~~~~
					// 3b. SHOP COUNTRY !== CUSTOMER COUNTRY
					// GET PRODUCT UNIT PRICE BEFORE TAX
					// GET CUSTOMER COUNTRY TAX PERCENT
					// COMPUTE TAX AMOUNT FOR CUSTOMER COUNTRY
					// ADD TAX AMOUNT FOR CUSTOMER COUNTRY TO BASE PRICE FOR SHOP
					// NOTE: ABOVE TAX PERCENT FOR CUSTOMER COUNTRY ALREADY SET TO $this->taxPercent. @see: TraitPWCommerceTax::setOrderLineItemTaxRate
					# +++++++++++
					// DERIVE NET/PRE-TAX PRICE FROM  UNIT DISPLAY PRICE INC TAX
					// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
					$totalPriceBeforeTaxMoney = $this->getNetPriceIfPriceInclusiveTax($this->totalDisplayPrice);

				}

			}
		}


		// ---
		return $totalPriceBeforeTaxMoney;
	}


	/**
	 * Get Total Price After Tax.
	 *
	 * @return mixed
	 */
	private function getTotalPriceAfterTax() {
		// (iii) schema 'total_price_with_tax'
		$totalPriceAfterTaxMoney = NULL;

		if (empty($this->isOrderLineItemTaxable)) {
			// 1. LINE ITEM NOT TAXABLE
			// TOTAL DISPLAY PRICE EQUALS THE AFTER TAX PRICE
			$totalPriceAfterTax = $this->totalDisplayPrice;
			$totalPriceAfterTaxMoney = $this->money($totalPriceAfterTax);
		} else {
			// LINE ITEM TAXABLE
			# ------------
			if (empty($this->isPricesIncludeTaxes())) {
				// 2. PRICES DO NOT INCLUDE TAX
				// WE HAVE ALREADY COMPUTED $this->unitPriceAfterTaxMoney
				// HERE WE MULTIPLY BY QUANTITY TO GET TOTAL
				// WE USE THE 'CALCULATION PER PRODUCT UNIT' approach
				// TODO: IN FUTUTE CAN MAKE CONFIGURABLE TO USE THIS OR 'CALCULATION BY PRODUCT LINE'. in that case, we would do: '$this->unitPriceBeforeTaxMoney * $this->quantity THEN ADD TAX TO THE TOTAL

				// get total price inc tax by multiplying total inc tax by quantity
				$totalPriceAfterTaxMoney = $this->unitPriceAfterTaxMoney->multiply($this->quantity);
			} else {
				// 3. PRICES INCLUDE TAX
				// check if customer country is in shop country
				// ---------------
				if (!empty($this->isOrderCustomerShippingAddressInShopCountry())) {
					// 3a. SHOP COUNTRY === CUSTOMER COUNTRY
					// TOTAL DISPLAY PRICE EQUALS THE TOTAL AFTER-TAX PRICE
					// WE HAVE ALREADY COMPUTED $this->unitPriceAfterTaxMoney
					// THAT HAS TAKEN INTO ACCOUNT 'if customer country is in shop country'
					// IT HAS ALSO DETERMINED THE CORRECT '$unitPriceIncTaxBasePercent'
					// i.e. if override tax percent or shop country standard/base tax percent
					// HERE WE SUBTRACT THE TAX TOTAL FROM BY QUANTITY TO GET TOTAL
					$totalPriceAfterTax = $this->totalDisplayPrice;
					$totalPriceAfterTaxMoney = $this->money($totalPriceAfterTax);
				} else {
					# ~~~~~~~~~~
					// 3b. SHOP COUNTRY !== CUSTOMER COUNTRY
					$totalPriceBeforeTaxMoney = $this->getNetPriceIfPriceInclusiveTax($this->totalDisplayPrice);
					$totalPriceAfterTaxMoney = $totalPriceBeforeTaxMoney->add($this->taxAmountMoney);
				}
			}


		}

		// ---
		return $totalPriceAfterTaxMoney;

	}

	/**
	 * Get Total Price With Discount Before Tax.
	 *
	 * @return mixed
	 */
	private function getTotalPriceWithDiscountBeforeTax() {
		// (ii) schema 'total_price_discounted'
		// if no discount, discounted total before tax equates to total before tax
		// TODO? FOR NOW JUST NULL IT? no; we will check Money::equals
		// $totalPriceWithDiscountBeforeTaxMoney = NULL;
		$totalPriceWithDiscountBeforeTaxMoney = $this->totalPriceBeforeTaxMoney;
		if (!empty($this->totalDiscountsAmountMoney)) {
			$totalPriceWithDiscountBeforeTaxMoney = $this->totalPriceBeforeTaxMoney->subtract($this->totalDiscountsAmountMoney);
		}
		// ---
		return $totalPriceWithDiscountBeforeTaxMoney;
	}

	/**
	 * Get Total Price With Discount After Tax.
	 *
	 * @return mixed
	 */
	private function getTotalPriceWithDiscountAfterTax() {
		// (iv) schema 'total_price_discounted_with_tax'
		// if no tax, this will be equal to 'discounted BEFORE tax money'
		$totalPriceWithDiscountAfterTaxMoney = $this->totalPriceWithDiscountBeforeTaxMoney;
		if ($this->isOrderLineItemTaxable) {
			// LINE ITEM IS TAXABLE
			$totalPriceWithDiscountBeforeTaxAmount = $this->getWholeMoneyAmount($this->totalPriceWithDiscountBeforeTaxMoney);
			// get the tax amount
			$taxAmountMoney = $this->getTaxAmountFromPriceExclusiveTax($this->taxPercent, $totalPriceWithDiscountBeforeTaxAmount);
			// add the tax amount to the 'discounted price ex tax'
			$totalPriceWithDiscountAfterTaxMoney = $this->totalPriceWithDiscountBeforeTaxMoney->add($taxAmountMoney);
			// track the final tax money(post discount)
			$this->taxAmountAfterDiscountMoney = $taxAmountMoney;
		}
		// ---
		return $totalPriceWithDiscountAfterTaxMoney;
	}

	# DISCOUNT #
	/**
	 * Get Total Discounts Amount.
	 *
	 * @return mixed
	 */
	private function getTotalDiscountsAmount() {
		// schema 'total_discounts'
		// @note: this is the total amout of discounts that have been applied to the order line item
		// @note: it does not include the NET price; just the discount only!
		// 5. TOTALS
		// NOTE TraitPWCommerceUtilitiesDiscount::getOrderLineItemDiscountsAmount
		$totalDiscountsAmountMoney = $this->getOrderLineItemDiscountsAmount();
		// ---
		return $totalDiscountsAmountMoney;
	}


	# UTILITIES #

	/**
	 * Get Net Price If Price Inclusive Tax.
	 *
	 * @param mixed $priceInclusiveTax
	 * @param bool $isReturnMoney
	 * @return mixed
	 */
	private function getNetPriceIfPriceInclusiveTax($priceInclusiveTax, bool $isReturnMoney = true) {
		// if unit price has tax included, get PRICE PORTION WITHOUT TAX
		// Net: (Amount / 100+TAX PERCENT) * 100
		// e.g. if VAT is 20%, Net: (Amount / 120) * 100
		# ++++++++++++++++
		$hundred = PwCommerce::HUNDRED;
		// TAX PERCENT (BASE TAX) IS SHOP'S BASE/STANDARD TAX OR CATEGORY OVERRIDE IF APPLICABLE
		$unitPriceIncTaxBasePercent = !empty($this->isTaxOverride) ? $this->taxPercent : $this->getShopCountryTaxRate();
		// get the tax amount
		// NET IS BASED ON THE SHOP COUNTRY TOTAL DISPLAY PRICE!
		// WE THEN ADD TAX TO THAT FOR CUSTOMER COUNTRY
		// $net = ($priceInclusiveTax / ($hundred + $unitPriceIncTaxBasePercent)) * $hundred;
		$netAmount = ($priceInclusiveTax / ($hundred + $unitPriceIncTaxBasePercent)) * $hundred;
		$netAmount = round($netAmount, 2);
		if (!empty($isReturnMoney)) {
			$netAmount = $this->money($netAmount);
		}

		// ---------
		return $netAmount;
	}


	################

	/**
	 * Get Order Line Item Delivered Date.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemDeliveredDate() {
		// TODO: UNSURE OF THIS ONE? GET? SET?
		// 6. SHIPMENT TODO: MAYBE NOT HERE? SHOULD BE SEPARATE IF ORDER IS COMPLETE
		// 'delivered_date' => $this->_sanitizeValue($value->deliveredDate), // +++
	}


	/**
	 * Get Order Line Item Product.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemProduct() {
		return $this->productOrVariantPage;
	}

	/**
	 * Get Order Line Item Product Settings.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemProductSettings() {
		$orderLineItemProduct = $this->getOrderLineItemProduct();

		return $orderLineItemProduct['settings'];
	}

	/**
	 * Get Order Line Item Product Categories.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemProductCategories() {
		$orderLineItemProduct = $this->getOrderLineItemProduct();
		return isset($orderLineItemProduct['categories']) ? $orderLineItemProduct['categories'] : [];
	}


	public function getOrderLineItemProductCategories() {
		$orderLineItemProduct = $this->getOrderLineItemProduct();
		return isset($orderLineItemProduct['categories']) ? $orderLineItemProduct['categories'] : [];
	}


	/**
	 * Get Line Items For Order.
	 *
	 * @return mixed
	 */
	public function getLineItemsForOrder() {
		// TODO - THIS SHOULD BE GETTING ITEMS THAT ARE CURRENTLY IN ORDER EDIT VIEW! NOT NECESSARILY SAVED ONES! THAT MEANS CHECKING IDS IN INPUT! OR BEING PASSED LINE ITEMS HERE!
		if ($this->order->isLiveCalculateOnly) {

			// TODO WE WILL NEED TO CALCULATE DISCOUNTS ETC!!!! - BUGGY FOR NOW!
			$orderLineItems = $this->order->liveOrderLineItems;

		} else {

			if ($this->isForLiveShippingRateCalculation) {
				// GET LINE ITEMS FROM CORRESPONDING PRODUCTS
				// for use with live shipping rate calculation where order page
				// might not yet have been created
				$orderLineItems = $this->getLinesItemsForLiveShippingRateCalculation();
			} else {
				// GET LINE ITEMS FROM ORDER PAGE FOR ORDER IN SESSION
				$orderLineItems = $this->getLineItemsFromOrderInSession();
			}

			$orderLineItems = $this->addShippableStatusToLineItems($orderLineItems);

		}
		// final order line items

		// ---------
		return $orderLineItems;
	}

	public function getLineItemsForOrder() {
		// TODO - THIS SHOULD BE GETTING ITEMS THAT ARE CURRENTLY IN ORDER EDIT VIEW! NOT NECESSARILY SAVED ONES! THAT MEANS CHECKING IDS IN INPUT! OR BEING PASSED LINE ITEMS HERE!
		if ($this->order->isLiveCalculateOnly) {

			// TODO WE WILL NEED TO CALCULATE DISCOUNTS ETC!!!! - BUGGY FOR NOW!
			$orderLineItems = $this->order->liveOrderLineItems;

		} else {

			if ($this->isForLiveShippingRateCalculation) {
				// GET LINE ITEMS FROM CORRESPONDING PRODUCTS
				// for use with live shipping rate calculation where order page
				// might not yet have been created
				$orderLineItems = $this->getLinesItemsForLiveShippingRateCalculation();
			} else {
				// GET LINE ITEMS FROM ORDER PAGE FOR ORDER IN SESSION
				$orderLineItems = $this->getLineItemsFromOrderInSession();
			}

			$orderLineItems = $this->addShippableStatusToLineItems($orderLineItems);

		}
		// final order line items

		// ---------
		return $orderLineItems;
	}

	/**
	 * Get Line Items From Order In Session.
	 *
	 * @return array
	 */
	private function getLineItemsFromOrderInSession(): array {

		$fields = ['pwcommerce_order_line_item' => 'line_item', 'id'];
		//
		// @note:
		// - we exclude hidden line items pages as they are 'abandoned' and will be deleted when order is completed
		// - 'abandoned' means that the basket was amend post-order confirmation and items REMOVED then the order re-confirmed
		// - this can happen multiple times in an order's life-cyle pre-completion
		// - it means 'abandoned' can be returned to the basket if they are re-added in which case we remove the 'hidden' status and they are no longer in 'abandoned state'
		//
		$orderLineItemsRaw = $this->wire('pages')->findRaw("template=" . PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME . ",parent={$this->orderPage},include=all,check_access=0,status!=hidden", $fields);

		$orderLineItems = array_column($orderLineItemsRaw, 'line_item');

		// TODO: GET AND ADD PRODUCT SETTINGS FOR THESE ORDER LINE ITEMS IN ORDER TO TAG THEM AS SHIPPABLE OR NOT
		// TODO; IF VARIANT, WE DO THE PARENT
		// TODO DELETE WHEN DONE
		// $orderLineItems = $this->addShippableStatusToLineItems($orderLineItemsRaw);
		// -
		return $orderLineItems;
	}

	private function getLineItemsFromOrderInSession(): array {

		$fields = ['pwcommerce_order_line_item' => 'line_item', 'id'];
		//
		// @note:
		// - we exclude hidden line items pages as they are 'abandoned' and will be deleted when order is completed
		// - 'abandoned' means that the basket was amend post-order confirmation and items REMOVED then the order re-confirmed
		// - this can happen multiple times in an order's life-cyle pre-completion
		// - it means 'abandoned' can be returned to the basket if they are re-added in which case we remove the 'hidden' status and they are no longer in 'abandoned state'
		//
		$orderLineItemsRaw = $this->wire('pages')->findRaw("template=" . PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME . ",parent={$this->orderPage},include=all,check_access=0,status!=hidden", $fields);

		$orderLineItems = array_column($orderLineItemsRaw, 'line_item');

		// TODO: GET AND ADD PRODUCT SETTINGS FOR THESE ORDER LINE ITEMS IN ORDER TO TAG THEM AS SHIPPABLE OR NOT
		// TODO; IF VARIANT, WE DO THE PARENT
		// TODO DELETE WHEN DONE
		// $orderLineItems = $this->addShippableStatusToLineItems($orderLineItemsRaw);
		// -
		return $orderLineItems;
	}

	/**
	 * Get Products I Ds In Line Items For Order.
	 *
	 * @return mixed
	 */
	public function getProductsIDsInLineItemsForOrder() {

		// @note: productID saved at 'data' column in the schema!
		$lineItemsProductsIDs = array_column($this->orderLineItems, 'data');

		// --------
		return $lineItemsProductsIDs;
	}

	public function getProductsIDsInLineItemsForOrder() {

		// @note: productID saved at 'data' column in the schema!
		$lineItemsProductsIDs = array_column($this->orderLineItems, 'data');

		// --------
		return $lineItemsProductsIDs;
	}

	/**
	 * Find the products for line items in the order.
	 *
	 * @return mixed
	 */
	public function getProductsPagesInLineItemsForOrder() {
		$lineItemsProductsIDs = $this->getProductsIDsInLineItemsForOrder();
		// ------
		$selectorIDs = implode("|", $lineItemsProductsIDs);
		/** @var PageArray $products */
		$products = $this->wire('pages')->find("id={$selectorIDs},check_access=0");
		// ----------
		return $products;
	}

	# ***********

	/**
	 * Get Single Order Line Item Quantity In Order.
	 *
	 * @param int $productID
	 * @return mixed
	 */
	public function getSingleOrderLineItemQuantityInOrder($productID) {
		$quantity = 0; // TODO DEFAULT 0 OR 1?
		foreach ($this->orderLineItems as $orderLineItem) {
			if ($productID === (int) $orderLineItem['data']) {
				$quantity = (int) $orderLineItem['quantity'];
				break;
			}
		}
		// ------
		return $quantity;
	}

	# *********





	 */
	public function getProductsPagesInLineItemsForOrder() {
		$lineItemsProductsIDs = $this->getProductsIDsInLineItemsForOrder();
		// ------
		$selectorIDs = implode("|", $lineItemsProductsIDs);
		/** @var PageArray $products */
		$products = $this->wire('pages')->find("id={$selectorIDs},check_access=0");
		// ----------
		return $products;
	}

	# ***********

	/**
	 * Get Single Order Line Item Quantity In Order.
	 *
	 * @param int $productID
	 * @return mixed
	 */
	public function getSingleOrderLineItemQuantityInOrder($productID) {
		$quantity = 0; // TODO DEFAULT 0 OR 1?
		foreach ($this->orderLineItems as $orderLineItem) {
			if ($productID === (int) $orderLineItem['data']) {
				$quantity = (int) $orderLineItem['quantity'];
				break;
			}
		}
		// ------
		return $quantity;
	}

	# *********





}