<?php

namespace ProcessWire;



trait TraitPWCommerceUtilitiesDiscount
{




	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ DISCOUNT ~~~~~~~~~~~~~~~~~~

	/**
	 * Get Order Line Item Discounts Amount.
	 *
	 * @return mixed
	 */
	public function getOrderLineItemDiscountsAmount() {

		// TODO UPDATE NOVEMBER 2023 -> THIS NOW CHANGES! FOR THE SAKE OF MULTIPLE DISCOUNTS APPLIED TO ONE LINE ITEM, WE WILL USE A WIREARAY, I.E. $this->orderLineItem->discounts. WE will then LOOP THROUGH IT TO GET EACH DISCOUNTS DISCOUNT TYPE AND SUBSEQUENTLY DISCOUNT VALUE AND COMPUTE AMOUNT; WE WILL NEED TO TRACK A CUMULATIVE NET PRICE TO APPLY THE NEXT DISCOUNT TO!

		// 2. DISCOUNTS
		// 'discount_amount' => (float) $value->discountAmount, // +++

		// TODO NOVEMBER 2023. CHANGE THIS! ONE, NO LONGER NEEDED IN SCHEMA FOR PWCOMMERCE 009+ AND TWO, WE NEED TO ADD A PROPERTY $orderLineItem->discounts WHOSE VALUE IS A WIREARRAY. THE WIREARRAY WILL CONTAIN WIREDATA ITEMS EACH OF WHICH IS AN APPLIED DISCOUNT; EACH WILL HAVE ITS OWN DISCOUNT VALUES; EVENTUALLY WILL BE SAVED TO THE FIELD PWCOMMERCEORDERDISCOUNTS; WE PASS THIS TO PWCommerceUtilities::getOrderLineItemDiscountsAmount() FROM TraitPWCommerceParseCart::parseCart() TO COMPUTE DISCOUNT AMOUNT! ALSO SEE NOTES ON 'FIXED PER ORDER'. HENCE BELOW WE NEED TO LOOP THROUGH THE WIREARRAY AND CALL THE 'IF DISCOUNTYPE === 'XXX' FOR EACH! ALSO NEED TO DO THE CUMULATIVE COMPUTATION IF A SINGLE LINE ITEM HAS MULTIPLE DISCOUNTS! @SEE NOTES!

		// ##################
		// $discountAmount = 0; // if 'none'
		$discountAmountMoney = NULL; // if 'none'
		// $discountType = $this->orderLineItem->discountType;
		// TODO - OLD TO AMEND!
		// if ($discountType === 'fixed_applied_once') {
		// 	// fixed discount: applied once
		// 	$discountAmount = $this->calculateOrderLineItemFixedDiscountAppliedOnceAmount();
		// } else if ($discountType === 'fixed_applied_per_item') {
		// 	// fixed discount: applied per item
		// 	$discountAmount = $this->calculateOrderLineItemFixedDiscountAppliedPerItemAmount();
		// } else if ($discountType === 'percentage') {
		// 	// percentage discount
		// 	$discountAmount = $this->calculateOrderLineItemPercentageDiscountAmount();
		// }
		// TODO: fix legacy such as 'fixed_applied_once

		/** @var WireArray $orderLineItemsDiscounts */
		$orderLineItemsDiscounts = $this->orderLineItem->discounts;
		// if no discounts field value, return early
		if (empty($orderLineItemsDiscounts)) {
			return $discountAmountMoney;
		}

		$orderLineItemsDiscountsCount = $orderLineItemsDiscounts->count();

		// if no discount applied to line item, return early
		if (empty($orderLineItemsDiscountsCount)) {
			return $discountAmountMoney;
		}

		// --------------
		// GOOD TO GO

		// prepare for application of multiple discounts
		$isApplyMultipleDiscounts = $orderLineItemsDiscountsCount > 1;

		$this->orderLineItem->isApplyMultipleDiscounts = $isApplyMultipleDiscounts;

		if (!empty($isApplyMultipleDiscounts)) {
			// INIT FIRST 'CURRENT RUNNING DISCOUNTED UNIT PRICE
			// TODO: could we use some number? negative?
			$this->orderLineItem->currentDiscountedUnitPriceMoney = NULL;
		}

		// prepare discount types checks

		# +++++++++++++
		// FIXED APPLY ONCE DISCOUNTS
		#++++++++++++++++++++++++ IMPORTANT +++++++++++++++++++++#
		// @NOTE: this includes 'computed' discount values from 'whole_order_fixed' THAT GET CONVERTED TO 'fixed per item' in PWCommerceDiscounts::validateAndApplyDiscounts()
		#++++++++++++++++++++++++++++++++++++++++++++++++++++++++#
		$fixedApplyOncePerOrderDiscounts = [
			// TODO: LEGACY! AMEND!
			'fixed_applied_once',
			// -------
			// ORDER-LEVEL
			'whole_order_fixed',
			// -------
			'categories_fixed_per_order',
			'products_fixed_per_order'
		];

		// FIXED APPLY PER ITEM DISCOUNTS
		$fixedApplyPerItemDiscounts = [
			// TODO: LEGACY! AMEND!
			'fixed_applied_per_item',
			// -------
			'categories_fixed_per_item',
			'products_fixed_per_item'
		];

		// PERCENTAGE DISCOUNTS
		#++++++++++++++++++++++++ IMPORTANT +++++++++++++++++++++#
		// @NOTE: this includes 'computed' discount values from 'whole_order_percentage' THAT GET CONVERTED TO 'line item percentage (per item)' in PWCommerceDiscounts::validateAndApplyDiscounts()
		#++++++++++++++++++++++++++++++++++++++++++++++++++++++++#
		$perecentageApplyPerItemDiscounts = [
			// TODO: LEGACY! AMEND!
			'percentage',
			// -------
			// ORDER-LEVEL
			'whole_order_percentage',
			// -------
			'categories_percentage',
			'products_percentage'
		];

		// LOOP THROUGH ORDER LINE ITEMS DISCOUNTS
		// SUM THEM UP AND RETURN TOTAL

		$isInitialDiscountAmount = true;

		foreach ($orderLineItemsDiscounts as $discount) {
			$discountType = $discount->discountType;
			$discountValue = $discount->discountValue;

			// ======

			// NOTE: RETURNING MONEY OBJECTS!
			if (in_array($discountType, $fixedApplyOncePerOrderDiscounts)) {
				// fixed discount: applied once
				$currentDiscountAmountMoney = $this->calculateOrderLineItemFixedDiscountAppliedOnceAmount($discountValue);
			} else if (in_array($discountType, $fixedApplyPerItemDiscounts)) {
				// fixed discount: applied per item
				$currentDiscountAmountMoney = $this->calculateOrderLineItemFixedDiscountAppliedPerItemAmount($discountValue);
			} else if (in_array($discountType, $perecentageApplyPerItemDiscounts)) {
				// percentage discount
				$currentDiscountAmountMoney = $this->calculateOrderLineItemPercentageDiscountAmount($discountValue);
			}

			// TRACK DISOUNT AMOUNT FOR THIS DISCOUNT
			$discount->discountAmount = (float) $this->getWholeMoneyAmount($currentDiscountAmountMoney);

			// --------
			// SUM IT UP for line item
			if (!empty($isInitialDiscountAmount)) {
				// FIRST/INITIAL DISCOUNT AMOUNT TO START WITH
				// create money object
				// $discountAmountMoney = $this->money($currentDiscountAmount);
				$discountAmountMoney = $currentDiscountAmountMoney;
			} else {
				// NOT INITIAL DISCOUNT AMOUNT TO ADD
				// amend money object > add to money object
				// $currentDiscountAmountMoney = $this->money($currentDiscountAmount);
				$discountAmountMoney = $discountAmountMoney->add($currentDiscountAmountMoney);
			}

			$isInitialDiscountAmount = false;

			// ~~~~~~~~
			// RUNNING DECREMENTED TOTAL IF APPLING MULTITPLE DISCOUNTS TO SAME LINE ITEM
			// @note: if applying multiple discounts, subsequent discounts applications need to be based on the current discounted priced,
			// i.e. the NET price
			// WE TRACK THIS IN A RUNTIME PROPERTY IN
		}

		# +++++++++++++++++
		// -------
		return $discountAmountMoney;

	}

	/**
	 * Calculate Order Line Item Percentage Discount Amount.
	 *
	 * @param mixed $discountValue
	 * @return mixed
	 */
	public function calculateOrderLineItemPercentageDiscountAmount($discountValue) {
		// ++++++++++++++++++

		// TODO WE NEED TO BE ABLE TO CUMULATIVELY CALCULATE DISCOUNTS! I.E.IF MULTIPE DISCOUNTS ARE BEING APPLIED, THE UNIT PRICE (?) SHOULD BE MINUS THE PREVIOUS DISCOUNT SET! PASS AS PARAM? IS IT REALLY THE UNIT PRICE THOUGH OR THE TOTAL?
		// TODO ENSURE PERCENTAGE DISCOUNT IS NOT > 100!
		if ($discountValue > 100) {
			$discountValue = 100;
		}

		// ------------

		// >>>>>>>>>>>>>. MONEY <<<<<<<<<<<
		$quantity = (int) $this->orderLineItem->quantity;
		$discountValuePercent = $discountValue;// just for clarity
		$discountValueRate = $discountValuePercent / PwCommerce::HUNDRED;

		// ======
		// IF APPLYING MUTIPLE PERCENTAGE DISCOUNTS TO ONE LINE ITEM
		if (!empty($this->orderLineItem->isApplyMultipleDiscounts)) {
			// FIRST TIME CUMULATIVE
			if (is_null($this->orderLineItem->currentDiscountedUnitPriceMoney)) {
				// >>>>>>>>>>>>>. MONEY <<<<<<<<<<<
				// create MONEY OBJECT
				// TODO WE NOW WORK WITH NET VALUES! THIS IS SO WE AVOID COMPUTING DISCOUNT ON 'TAXED' VALUES!
				// $unitPriceMoney = $this->money($this->orderLineItem->unitPrice);
				$unitPriceMoney = $this->unitPriceBeforeTaxMoney;
				$unitDiscountAmountMoney = $unitPriceMoney->multiply(strval($discountValueRate));
				// INITIAL: SET TO PROP TO TRACK CUMULATIVE
				$this->orderLineItem->currentDiscountedUnitPriceMoney = $unitPriceMoney->subtract($unitDiscountAmountMoney);

			} else {
				// GET CURRENT CUMULATIVE
				// >>>>>>>>>>>>>. MONEY <<<<<<<<<<<
				$currentDiscountedUnitPriceMoney = $this->orderLineItem->currentDiscountedUnitPriceMoney;
				$unitDiscountAmountMoney = $currentDiscountedUnitPriceMoney->multiply(strval($discountValueRate));

				# ^^^^^^^^^^^^^^^^^^^^^^^^^^^

				// SUBSEQUENT: SET NEXT VALUE FOR CUMULATIVE => SUBTRACT
				// >>>>>>>>>>>>>. MONEY <<<<<<<<<<<
				// SUBSEQUENT: SET NEXT VALUE FOR CUMULATIVE => SUBTRACT
				$this->orderLineItem->currentDiscountedUnitPriceMoney = $currentDiscountedUnitPriceMoney->subtract($unitDiscountAmountMoney);


			}
		} else {

			// SINGLE PERCENTAGE DISCOUNT
			// >>>>>>>>>>>>>. MONEY <<<<<<<<<<<
			// create MONEY OBJECT
			// TODO WE NOW WORK WITH NET VALUES! THIS IS SO WE AVOID COMPUTING DISCOUNT ON 'TAXED' VALUES!
			// $unitPriceMoney = $this->money($this->orderLineItem->unitPrice);
			$unitPriceMoney = $this->unitPriceBeforeTaxMoney;
			$unitDiscountAmountMoney = $unitPriceMoney->multiply(strval($discountValueRate));


		}


		// =====================
		# COMPUTE DISCOUNT FINAL AMOUNTS

		// =================
		// discount applied to all items in this order line item
		// >>>>>>>>>>>>>. MONEY <<<<<<<<<<<
		$discountAmountMoney = $unitDiscountAmountMoney->multiply($quantity);


		return $discountAmountMoney;

	}

	/**
	 * TODO
	 *
	 * @param mixed $discountValue
	 * @return mixed
	 */
	public function calculateOrderLineItemFixedDiscountAppliedOnceAmount($discountValue) {
		// TODO CONFIRM MONEY
		// ++++++++++++++++++
		// TODO WE NEED TO BE ABLE TO CUMULATIVELY CALCULATE DISCOUNTS! I.E.IF MULTIPE DISCOUNTS ARE BEING APPLIED, THE UNIT PRICE (?) SHOULD BE MINUS THE PREVIOUS DISCOUNT SET! PASS AS PARAM? IS IT REALLY THE UNIT PRICE THOUGH OR THE TOTAL?

		// create money object
		$discountAmountMoney = $this->money($discountValue);
		return $discountAmountMoney;
	}

	/**
	 * Calculate Order Line Item Fixed Discount Applied Per Item Amount.
	 *
	 * @param mixed $discountValue
	 * @return mixed
	 */
	public function calculateOrderLineItemFixedDiscountAppliedPerItemAmount($discountValue) {
		// ++++++++++++++++++
		// TODO WE NEED TO BE ABLE TO CUMULATIVELY CALCULATE DISCOUNTS! I.E.IF MULTIPE DISCOUNTS ARE BEING APPLIED, THE UNIT PRICE (?) SHOULD BE MINUS THE PREVIOUS DISCOUNT SET! PASS AS PARAM? IS IT REALLY THE UNIT PRICE THOUGH OR THE TOTAL?
		// create money object
		$baseDiscountAmountMoney = $this->money($discountValue);
		// multiply to get total fixed discount applied per item MONEY AMOUNT
		$discountAmountMoney = $baseDiscountAmountMoney->multiply($this->orderLineItem->quantity);
		// ----------
		return $discountAmountMoney;
	}

	// --------


	/**
	 * Get Order Discounted Sub Total.
	 *
	 * @param bool $isForShippingRateCalculation
	 * @return mixed
	 */
	private function getOrderDiscountedSubTotal(bool $isForShippingRateCalculation = false) {
		// TODO: DO MORE TESTS HERE, BOTH INC TAX AND EX TAX!
		// sum of order line items 'total_price_discounted'
		// @NOTE: THIS IS THE FINAL PRE-TAX AND PRE-SHIPPING+HANDLING PRICE!
		// @NOTE HAS INCLUDED DISCOUNTS
		// WE USE IT TO CALCULATE PERCENTAGE HANDLING FEE!


		// sum of order line items 'total_price_discounted'
		// -------------------
		// SKIP IF FOR PRICE-BASED SHIPPING RATE CALCULATION
		// for price-based shipping, only include shippable goods
		$includedShippingTypesForPriceBasedShippingRates = ['physical'];

		// ------------------
		// loop through to get values
		$lineItemsTotalPriceDiscountedAmount = 0;
		$lineItemsTotalPriceDiscountedMoney = $this->money(0);
		foreach ($this->orderLineItems as $orderLineItem) {
			//  TODO: MAYBE IN FUTURE NEED TO PASS TO THIS? getOrderLineItemCalculatedValues();
			// -----------------
			// skip non-shippable if computation is for calculating shipping rates
			if (!empty($isForShippingRateCalculation) && !in_array($orderLineItem['shippingType'], $includedShippingTypesForPriceBasedShippingRates)) {

				continue;
			}

			// --------------
			// amend MONEY OBJECT
			$currentLineItemTotalPriceDiscountedMoney = $this->money($orderLineItem['total_price_discounted']);
			$lineItemsTotalPriceDiscountedMoney = $lineItemsTotalPriceDiscountedMoney->add($currentLineItemTotalPriceDiscountedMoney);

		}
		$lineItemsTotalPriceDiscountedAmount = $this->getWholeMoneyAmount($lineItemsTotalPriceDiscountedMoney);
		// --------------------
		return $lineItemsTotalPriceDiscountedMoney;
	}


}
