<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Shipping: Trait class for PWCommerce Shipping.
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



trait TraitPWCommerceShipping
{

	// is shipping chargable on this order?
	protected $isShippingApplicable;
	protected $shippingCountry; // @note: shared with order as well! but different instance!
	protected $shippingZone;
	protected $shippingZoneRates;
	protected $isForLiveShippingRateCalculation = false;

	# TODO - USE BELOW NEW METHODS

	/**
	 * Get Cart Shipping Fees.
	 *
	 * @return mixed
	 */
	public function getCartShippingFees() {
		// calculate shipping fee
		// calculate handling fee
	}

	/**
	 * Get Cart Matched Shipping Rates.
	 *
	 * @return mixed
	 */
	public function getCartMatchedShippingRates() {

	}

	/**
	 * Get Cart Shipping Fee Money.
	 *
	 * @return mixed
	 */
	public function getCartShippingFeeMoney() {
	}
	/**
	 * Get Cart Handling Fee Money.
	 *
	 * @return mixed
	 */
	public function getCartHandlingFeeMoney() {
	}

	/**
	 * Get Cart Percentage Handling Fee.
	 *
	 * @return mixed
	 */
	public function getCartPercentageHandlingFee() {
	}

	/**
	 * Is Shipping Taxable.
	 *
	 * @return bool
	 */
	public function isShippingTaxable() {
	}

	/**
	 * Get Cart Tax Amount On Shipping.
	 *
	 * @return mixed
	 */
	public function getCartTaxAmountOnShipping() {
	}


	##########################

	# ***************



	/**
	 * Add Shippable Status To Line Items.
	 *
	 * @param array $orderLineItems
	 * @return mixed
	 */
	private function addShippableStatusToLineItems(array $orderLineItems) {

		// @note: we want to get shipping type for products represented in line items
		// some line items will be variants; however, product settings leave inside parents.
		// we separate these.
		$pages = $this->wire('pages');
		// 1. PREPARE IDs OF main and variant PRODUCTS for fetching PRODUCT SETTINGS
		// i.e., settings for shippingType -> for help with shipping calculation (weight/price/quantity)
		$mainProductIDs = [];
		$variantsProductIDs = [];
		foreach ($orderLineItems as $orderLineItem) {
			// TODO DELETE WHEN DONE
			// if (empty((int)$orderLineItem['line_item']['is_variant'])) {
			if (empty((int) $orderLineItem['is_variant'])) {
				// MAIN PRODUCTS
				// TODO DELETE WHEN DONE
				// $productID =
				// 	(int)$orderLineItem['line_item']['data'];
				$productID = (int) $orderLineItem['data'];
				$mainProductIDs[$productID] = $productID;
			} else {
				// VARIANTS

				// TODO DELETE WHEN DONE
				// $variantsIDs[] = (int)$orderLineItem['id'];
				// TODO DELETE WHEN DONE
				// $variantsProductIDs[] = (int)$orderLineItem['line_item']['data'];
				$variantsProductIDs[] = (int) $orderLineItem['data'];
			}
		}

		$variantsProductIDs = implode("|", $variantsProductIDs);

		$fields = 'parent_id';
		// $fields = ['parent_id', 'id'];
		$variantsParentProductsIDs = $pages->findRaw("template=pwcommerce-product-variant,id={$variantsProductIDs},check_access=0", $fields);
		// ---------------
		// $allProductsInLineItemsIDs = array_merge($mainProductIDs, $variantsParentProductsIDs);
		// @note: using union operator '+' to preserve keys -> these are the main product and variant product IDs
		$allProductsInLineItemsIDs = $mainProductIDs + $variantsParentProductsIDs;

		// ------------

		// ------------

		// 2. GET THE SETTINGS
		$fields = PwCommerce::PRODUCT_SETTINGS_FIELD_NAME;
		$productsIDs = implode("|", $allProductsInLineItemsIDs);

		$allProductsInLineItemsSettings = $pages->findRaw("template=" . PwCommerce::PRODUCT_TEMPLATE_NAME . ",id={$productsIDs},check_access=0", $fields);
		// -------

		// ------------

		// 3. ADD THE SETTINGS TO THE RESPECTIVE LINE ITEMS

		$processedOrderLineItems = [];
		foreach ($orderLineItems as $orderLineItem) {
			// get the ID of the product with the settings
			// this is in the $allProductsInLineItemsIDs array with the $productID => $productOrParentProductID
			$productID = (int) $orderLineItem['data'];



			$idOfProductWithSettings = (int) $allProductsInLineItemsIDs[$productID];

			/** @var Array $orderLineItemProductSettings */
			$orderLineItemProductSettings = $allProductsInLineItemsSettings[$idOfProductWithSettings];



			$orderLineItem['shippingType'] = $orderLineItemProductSettings['data'];
			// for later use elsewhere, also add 'productTitle' key
			$orderLineItem['productTitle'] = $orderLineItem['product_title'];
			// ------
			$processedOrderLineItems[] = $orderLineItem;
		}

		// ------------

		// -----
		return $processedOrderLineItems;
	}



	/**
	 * Does the current order use a custom handling fee?
	 *
	 * @return bool
	 */
	public function isCustomHandlingFee() {
		return (int) $this->order->isCustomHandlingFee === 1;
	}

	/**
	 * Get current order's handling fee type.
	 *
	 * @return mixed
	 */
	public function getOrderHandlingFeeType() {

		if ($this->isCustomHandlingFee()) {
			// return the set custom handling fee type
			$handlingFeeType = $this->order->handlingFeeType;
		} elseif (empty($this->isShippingApplicable)) {
			// no shippable goods: handling fee will not be applied
			$handlingFeeType = 'inapplicable';
		} else {
			// get handling fee type set in shipping zone settings
			$handlingFeeType = $this->getZoneHandlingFeeType();
		}
		// ----------------
		return $handlingFeeType;
	}

	/**
	 * Get current order's handling fee value.
	 *
	 * @return mixed
	 */
	public function getOrderHandlingFeeValue() {

		if ($this->isCustomHandlingFee()) {
			// return the set custom handling fee value
			$handlingFeeValue = $this->order->handlingFeeValue;
		} elseif (empty($this->isShippingApplicable)) {
			// no shippable goods: handling fee will not be applied
			$handlingFeeValue = 0;
		} else {
			// get handling fee value set in shipping zone settings
			$handlingFeeValue = $this->getZoneHandlingFeeValue();
		}
		// ----------------
		return $handlingFeeValue;
	}

	/**
	 * Get current order's handling fee amount.
	 *
	 * @return mixed
	 */
	public function getOrderHandlingFeeMoney() {
		// TODO CHANGE TO RETURN MONEY

		$handlingFeeType = $this->getOrderHandlingFeeType();
		$handlingFeeValue = $this->getOrderHandlingFeeValue();
		// if handling fee type === 'none' or 'inapplicable'
		$handlingFee = 0;
		// default empty money object
		// $handlingFeeMoney = $this->money($handlingFee);
		$isFixedHandlingFee = true;
		// -----------
		if ($handlingFeeType === 'fixed') {
			// fixed handling fee: nothing to calculate
			$handlingFee = $handlingFeeValue;
			// $handlingFeeMoney = $this->money($handlingFee);
		} elseif ($handlingFeeType === 'percentage') {
			// percentage handling fee: calculate it!
			// @note: percentage handling fee calculated in order NET PRICE before tax (BUT WITH DISCOUNTS!) applied to order line items as well as whole order discount applied.
			$isFixedHandlingFee = false;

		}

		if (empty($isFixedHandlingFee)) {
			// PERCENTAGE HANDLING FEE
			$handlingFeeMoney = $this->calculatePercentageHandlingFee($handlingFeeValue, $this->getOrderSubTotalMoney($isForShippingRateCalculation = true));
		} else {
			// FIXED HANDLING FEE
			$handlingFeeMoney = $this->money($handlingFee);
		}

		// ----------------
		return $handlingFeeMoney;
	}

	/**
	 * Get the order's matched shipping zone's handling fee type.
	 *
	 * @return mixed
	 */
	public function getZoneHandlingFeeType() {
		return $this->shippingZone->get(PwCommerce::SHIPPING_FEE_SETTINGS_FIELD_NAME)->handlingFeeType;
	}

	/**
	 * Get the order's matched shipping zone's handling fee value.
	 *
	 * @return mixed
	 */
	public function getZoneHandlingFeeValue() {
		return $this->shippingZone->get(PwCommerce::SHIPPING_FEE_SETTINGS_FIELD_NAME)->handlingFeeValue;
	}

	/**
	 * Calculate the current order's percentage handling fee amount.
	 *
	 * @param mixed $handlingFeeValue
	 * @param mixed $orderDiscountedNetPriceMoney
	 * @return mixed
	 */
	public function calculatePercentageHandlingFee($handlingFeeValue, $orderDiscountedNetPriceMoney) {
		// TODO CONFIRM MONEY
		// TODO DELETE WHEN DONE
		// $handlingFeeRate = $handlingFeeValue / 100;
		$handlingFeeRate = $this->getPercentageAsDecimal($handlingFeeValue);
		// $handlingFeeRate = $handlingFeeValue / PwCommerce::HUNDRED;

		$handlingFeeMoney = $orderDiscountedNetPriceMoney->multiply(strval($handlingFeeRate));
		// -------------
		return $handlingFeeMoney;
	}

	// ============

	/**
	 * Does the current order use a custom shipping fee?
	 *
	 * @return bool
	 */
	public function isCustomShippingFee() {
		return (int) $this->order->isCustomShippingFee === 1;
	}

	/**
	 * Calculate the shipping to charge for the current order.
	 *
	 * @return mixed
	 */
	public function getOrderShippingFeeMoney() {

		$order = $this->order;


		// TODO IF SHIPPING DID NOT NEED TO RECALCULATE BETWEEN ORDER EDITS, THEN NEED TO JUST RETURN EXISTING SHIPPING FEE HERE!!!
		// TODO - WE NOW ALWAYS UPDATE SHIPPING; FROM THE CART! THIS TO AVOID CONFUSION IF PASSING THROUGH CHECKOUT SEVERAL TIMES!

		$isShippingTaxableOnOrder = $this->isShippingTaxableOnOrder();

		// -----------------
		// $isCustomShippingFee = $this->order->isCustomShippingFee;
		$isCustomShippingFee = $this->isCustomShippingFee();

		// TODO -> NEED TO IGNORE BASKETS THAT DO NOT HAVE SHIPPABLE GOODS! E.G. DIGITAL, EVENTS/SERVICES AND PHYSICAL NOT REQUIRING SHIPPING. SO NEED TO CHECK FIRST! SO, RETURN SHIPPING FEE = 0(?)

		// $isShopChargeTaxesOnShippingRates = $this->isShopChargeTaxesOnShippingRates();

		// TODO THIS SHOULD ONLY RETURN IF WE ARE NOT CHARGING TAXES ON SHIPPING! OR IF CUSTOMER OR ORDER IS TAX EXEMPT BUT DO THAT LATER BELOW? yes since this is mainly about if shipping fee needs calculating -> alternatively create method that checks!
		// TODO GETTING AN ERROR HERE (13 JUNE 2024) SINCE THIS IS RETURNING NULL; DISABLEING FOR NOW
		// TODO - DELETE IF NOT IN USE
		// if (empty($this->order->isShippingFeeNeedsCalculating)) {
		// if (empty($this->order->isShippingFeeNeedsCalculating) && empty($isShopChargeTaxesOnShippingRates)) {

		// return $this->order->shippingFee;
		// }
		// #############################

		// TODO: WHAT IF COUNTRY HAS NO SHIPPING RATES SET? ERROR AT ORDER LEVEL!
		// $isShopChargeTaxesOnShippingRates = $this->isShopChargeTaxesOnShippingRates();
		$zoneMaximumShippingFeeAmount = $this->getZoneMaximumShippingFee();

		$zoneMaximumShippingFeeMoney = $this->money($zoneMaximumShippingFeeAmount);
		// TODO - DELETE WHEN DONE IF NOT IN USE!
		// TODO THIS NEEDS TO BE SET TO CUSTOM VALUE IF AVAILABLE!
		// TODO WHY THIS ZERO?
		// $shippingFee = 0;
		// TODO WHY THIS ZERO? it is cancelling our shipping fee amount!!! @see original
		// IT IS FOR USE WITH MANUAL ORDER; BUT LOGIC STILL NOT RIGHT; REVISIT!
		// $shippingFeeAmount = $isCustomShippingFee ? $this->order->shippingFee : 0;
		// TODO THIS NEEDS TO BE THE 'LIVE' OR 'CART' SHIPPING FEE; THIS IS BECAUSE WE NEED TO ACCOUNT FOR SITUATIONS WHEREBY CUSTOMER CONTINUED SHOPPING AFTER STARTING CHECKOUT; IN THAT CASE, SHIPPING VALUES WOULD HAVE BEEN SAVED; HENCE, IF WE USE SAVED VALUES, ONE, THEY COULD BE INCORRECT AND TWO, HERE, WE WOULD APPLY TAX ON SHIPPING AGAIN!!!
		$shippingFeeAmount = $order->shippingFee;


		########################
		// IF WE HAVE 'SHIPPING RATE NAME' SET TO ORDER
		// it means order has passed checkout at least once
		// hence, we have a selected shipping rate
		// we use its 'raw' pre-tax amount
		// this is because $this->order->shippingFee will include taxes (if applicable)
		// and we don't want to use that value and add more tax to it by mistake!

		########################

		// check for null, really!
		if (empty($shippingFeeAmount)) {
			$shippingFeeAmount = 0;
		}

		// default to current shipping amount TODO OK? => PROBLEM IS THAT SHIPPING IS THEN TAXED AGAIN WITH EACH SAVE!!!
		// $shippingFee = $this->order->shippingFee;

		// ---------------
		// TODO CHECK CUSTOM FIRST!
		// if(!empty)
		// IF WE HAVE A SELECTED SHIPPING RATE FOR 'LIVE SHIPPING RATE', WE USE IT
		// TODO CONFIRM THIS WON'T INTEFERE WITH CUSTOM SHIPPING!
		if (!is_null($order->selectedMatchedShippingRate)) {
			$shippingFeeAmount = $order->selectedMatchedShippingRate;
		}

		// ---------------

		// ---------------
		// CHECK IF CHARGING TAXES ON SHIPPING
		// @note: the $isShippingTaxableOnOrder has also checked for manual and customer tax exemptions
		// if (!empty($shippingFee) && $isShopChargeTaxesOnShippingRates) {
		# IF WE HAVE A SHIPPING AMOUNT
		# && NO SHIPPING RATE NAME ALREADY SAVED WITH ORDER
		# && SHIPPING IS TAXABLE...
		# APPLY TAX
		# NOTE: PRESENCE OF 'shippingRateName' MEANS TAX WAS ALREADY APPLIED TO SAVED SHIPPING FEE!



		// $matchedShippingRates = $order->matchedShippingRates;
		$shippingFeeMoney = $this->money($shippingFeeAmount);



		// CHECK IF SHIPPING RATE IS GREATER THAN MAXIMUM SHIPPING FEE
		// @note: we check after applying taxes above if applicable! TODO OK?
		// if (!empty($zoneMaximumShippingFee) && $shippingFeeAmount > $zoneMaximumShippingFee) {
		if (!empty($zoneMaximumShippingFeeAmount) && $shippingFeeMoney->greaterThan($zoneMaximumShippingFeeMoney)) {
			$shippingFeeMoney = $zoneMaximumShippingFeeMoney;
		}
		return $shippingFeeMoney;
	}


	# ==============================


	/**
	 * Is Shop Have At Least One Shipping Zone.
	 *
	 * @return bool
	 */
	public function isShopHaveAtLeastOneShippingZone() {
		// get at least one PUBLISHED shipping zone
		$oneShippingZoneID = (int) $this->wire('pages')->getRaw("template=" . PwCommerce::SHIPPING_ZONE_TEMPLATE_NAME . ",status!=unpublished", 'id');

		return !empty($oneShippingZoneID);
	}



	/**
	 * Is Shipping Applicable On Order.
	 *
	 * @return bool
	 */
	private function isShippingApplicableOnOrder() {
		$lineItemsProductsIDs = $this->getProductsIDsInLineItemsForOrder();
		// ------
		$selectorIDs = implode("|", $lineItemsProductsIDs);

		// TODO: THESE NEEDS TO ACCOUNT FOR VARIANTS! I.E., THEIR PRODUCT SETTINGS WILL BE ON THEIR PARENTS, SO USE OR:groups!
		/** @var int $productID */
		// $productID = (int) $this->wire('pages')->getRaw("id={$selectorIDs},pwcommerce_product_settings.shipping_type=physical,check_access=0", 'id');
		$productID = (int) $this->wire('pages')->getRaw("id={$selectorIDs},check_access=0,(pwcommerce_product_settings.shipping_type=physical),(parent.pwcommerce_product_settings.shipping_type=physical)", 'id');

		return !empty($productID);
	}



	/**
	 * Is Shippable Physical Product.
	 *
	 * @param Page $product
	 * @return bool
	 */
	public function isShippablePhysicalProduct(Page $product) {
		// determine if product is a variant or main product (without variants)
		$isVariant = $this->isVariant($product);
		// get the product settings
		$productSettings = $isVariant ? $product->parent->get(PwCommerce::PRODUCT_SETTINGS_FIELD_NAME) : $product->get(PwCommerce::PRODUCT_SETTINGS_FIELD_NAME);
		// ----------
		return $productSettings->shippingType === 'physical';
	}

	/**
	 * Is Order Customer Shipping Address In The E U.
	 *
	 * @return bool
	 */
	public function isOrderCustomerShippingAddressInTheEU() {
		// GET COUNTRIES CLASS
		$pwcommerceCountries = $this->getPWCommerceClassByName('PWCommerceCountries');
		// shippingCountry
		$shippingCountryCode = $this->getOrderCountryTaxLocationCode();
		// CHECK IF SHIPPING COUNTRY IS IN THE EU
		$isShippingCountryInTheEU = $pwcommerceCountries->isEUCountry($shippingCountryCode);

		return $isShippingCountryInTheEU;
	}
	/**
	 * Is Order Customer Shipping Address In Shop Country.
	 *
	 * @return bool
	 */
	public function isOrderCustomerShippingAddressInShopCountry() {
		// GET COUNTRIES CLASS
		$pwcommerceCountries = $this->getPWCommerceClassByName('PWCommerceCountries');
		// shippingCountry
		$shippingCountryCode = $this->getOrderCountryTaxLocationCode();
		// GET SHOP GENERAL SETTINGS TO GET SHOP COUNTRY
		$shopGeneralSettings = $this->getShopGeneralSettings();
		$shopCountryCode = $shopGeneralSettings->country;

		// CHECK IF SHIPPING COUNTRY IS IN THE SHOP COUNTRY
		$isShippingCountryInShopCountry = strtoupper($shippingCountryCode) === strtoupper($shopCountryCode);

		return $isShippingCountryInShopCountry;
	}

	# ***************

	/**
	 * Get Lines Items For Live Shipping Rate Calculation.
	 *
	 * @return array
	 */
	private function getLinesItemsForLiveShippingRateCalculation(): array {
		// TODO: NEED TO REVISIT THIS! THIS IS BECAUSE WE CANNOT DETERMINE DISCOUNTED PRICES AT THIS POINT IN TIME; HENCE WE ASSUME 'total_price_discounted_with_tax' IS EQUAL TO 'total_price'! it is also difficult since no taxes have been applied yet! this issue only affects price-based live rates; these depend on 'getOrderLineItemsSubTotalNetDiscountedPriceAmount()'
		$orderLineItems = [];
		// get the cart
		// @note: array of stdClass items
		$cartItems = $this->getCart();
		// ----------
		if (!empty($cartItems)) {
			// -------------
			// get the products
			// $fields = 'parent_id';
			// --------
			foreach ($cartItems as $cartItem) {
				$orderLineItem = [
					'data' => $cartItem->product_id,
					'product_title' => $cartItem->pwcommerce_title,
					'quantity' => $cartItem->quantity,
					// ADD 'is_variant' flag to variants
					'is_variant' => (int) $cartItem->pwcommerce_is_variant,
					# +++++++
					// empty/default fields just to avoid errors at this stage
					// they will be populated properly in order confirmation
					// here we are only interested in live shipping rates
					'total_price' => $cartItem->pwcommerce_price_total,
					// 'total_price_with_tax' => 0,
					// 'total_price_discounted' => 0,
					// 'total_price_discounted_with_tax' => 0,
					'total_price_with_tax' => $cartItem->pwcommerce_price_total,
					'total_price_discounted' => $cartItem->pwcommerce_price_total,
					'total_price_discounted_with_tax' => $cartItem->pwcommerce_price_total,

				];

				// -------
				$orderLineItems[] = $orderLineItem;
			}
		}

		// --
		return $orderLineItems;
	}
	# ==============================

	/**
	 * Get Shipping Countries.
	 *
	 * @return mixed
	 */
	public function getShippingCountries() {
		$allShippingZoneCountries = [];
		// TODO: FINDRAW OR FIND?
		// @note: if we have a rest of the world (ROW) shipping zone, we display all countries; else, only existing shipping zone countries!
		if ($this->isShopHaveRestOfTheWorldShippingZone()) {
			// TODO: NEED TO RETHINK THE ROW! ALTHOUGH WE HAVE THE COUNTRIES, WE WILL STILL NEED TO KNOW THEIR TAX RATES! HENCE, THEY WILL STILL NEED TO BE ADDED AS COUNTRIES
			// we have a ROW: get all countries
			// $allShippingZoneCountries = $this->getAllCountries();

			// @note: if shop has rest of the world, we just get all available countries in the shop!
			// this means, they countries do not need to be in a zone
			// TODO FOR SHIPPING RATE MATCH, MAKE SURE ROW COUNTRIES ARE MATCHED TO ROW ZONE if they don't match another zone!
			$fields = ['title', 'id', 'pwcommerce_tax_rates'];
			$allShopCountries = $this->wire('pages')->findRaw("template=" . PwCommerce::COUNTRY_TEMPLATE_NAME . ",check_access=0", $fields);

			foreach ($allShopCountries as $countryID => $countryValues) {
				$code = '';
				if (!empty($countryValues['pwcommerce_tax_rates'][0]['tax_location_code'])) {
					$code = $countryValues['pwcommerce_tax_rates'][0]['tax_location_code'];
				}

				// ----------
				$allShippingZoneCountries[] = [
					'id' => $countryValues['id'],
					'code' => $code,
					'name' => $countryValues['title'],
				];
			}
		} else {
			// no ROW: get limited countries
			// TODO @note: this simpler ProcessWire syntax does not work: returns zero results
			// // You can also use this format below to get multiple subfields from one field:
			// $a = $pages->findRaw("template=blog", [ "title", "categories" => [ "id", "title" ] ]);
			// ---------------------
			$fields = ['pwcommerce_shipping_zone_countries.title', 'pwcommerce_shipping_zone_countries.id', 'pwcommerce_shipping_zone_countries.pwcommerce_tax_rates'];
			$allShippingZoneCountriesRaw = $this->wire('pages')->findRaw("template=" . PwCommerce::SHIPPING_ZONE_TEMPLATE_NAME . ",check_access=0", $fields);

			// TODO NEED TO SORT COUNTRIES IN FINAL OUPTPUT ALPHABETICALLY

			// @note: above returns a nested array.
			// FOR FRONTEND FORM: We need to make sure we have 'tax_location_code' which is at pwcommerce_shipping_zone_countries => tax_location_code and 'title'.
			// TODO: WE THEN LOOP THROUGH AND COMPARE TO GEOGRAPHICAL LIST? NOT REALLY NECESSARY AS LONG AS WE HAVE the country code

			if (!empty($allShippingZoneCountriesRaw)) {
				foreach ($allShippingZoneCountriesRaw as $shippingZoneID => $shippingZoneValues) {
					$shippingZoneCountriesValues = $shippingZoneValues['pwcommerce_shipping_zone_countries'];
					foreach ($shippingZoneCountriesValues as $countryID => $countryValues) {

						$code = '';
						if (!empty($countryValues['pwcommerce_tax_rates'][0]['tax_location_code'])) {
							$code = $countryValues['pwcommerce_tax_rates'][0]['tax_location_code'];
						}
						// ----------
						$allShippingZoneCountries[] = [
							'id' => $countryValues['id'],
							'code' => $code,
							'name' => $countryValues['title'],
						];
					}
				}
			}
		}

		// -------------

		// @note: here we sort the countries alphabetically (case insensitive), based on the value of the key 'name'
		array_multisort(array_column($allShippingZoneCountries, 'name'), SORT_NATURAL | SORT_FLAG_CASE, $allShippingZoneCountries);

		return $allShippingZoneCountries;
	}

	/**
	 * Get Shop Rest Of The World Shipping Zone I D.
	 *
	 * @return mixed
	 */
	public function getShopRestOfTheWorldShippingZoneID() {
		$restOfTheWorldShippingZoneID = 0;
		if ($this->isShopHaveRestOfTheWorldShippingZone()) {
			$generalSettings = $this->getShopGeneralSettings();
			$restOfTheWorldShippingZoneID = (int) $generalSettings->rest_of_the_world_shipping_zone;
		}
		return $restOfTheWorldShippingZoneID;
	}

	// check if shop uses a rest of the world shipping zone
	// @see notes in shop general settings
	/**
	 * Is Shop Have Rest Of The World Shipping Zone.
	 *
	 * @return bool
	 */
	public function isShopHaveRestOfTheWorldShippingZone() {
		$generalSettings = $this->getShopGeneralSettings();
		return !empty($generalSettings->rest_of_the_world_shipping_zone);
	}

	/**
	 * Get Rest Of The World Shipping Zone.
	 *
	 * @return mixed
	 */
	public function getRestOfTheWorldShippingZone() {
		$restOfTheWorldShippingZone = [];
		$restOfTheWorldShippingZoneID = $this->getShopRestOfTheWorldShippingZoneID();
		if (!empty($restOfTheWorldShippingZoneID)) {
			$restOfTheWorldShippingZone = $this->wire('pages')->getRaw("id={$restOfTheWorldShippingZoneID}");
		}
		// -------------
		return $restOfTheWorldShippingZone;
	}


	# *************

	/**
	 * Get the shipping zone for a given country.
	 *
	 * @param mixed $country
	 * @return mixed
	 */
	public function getOrderCustomerCountryShippingZone($country) {
		// TODO CHANGE THIS! IF NO SHIPPING ZONE FOUND, NEXT CHECK IF ROW IN USE AND IF SO, GET THAT ZONE!
		// @note: 'pwcommerce_shipping_zone_countries' is a multi page field
		$shippingZone = $this->wire('pages')->get("template=" . PwCommerce::SHIPPING_ZONE_TEMPLATE_NAME . ",pwcommerce_shipping_zone_countries={$country}");
		if (empty($shippingZone->id)) {
			// TODO CHANGE THIS! IF NO SHIPPING ZONE FOUND, NEXT CHECK IF ROW IN USE AND IF SO, GET THAT ZONE!
			if (!empty($this->getShopRestOfTheWorldShippingZoneID())) {
				// @note: using template just in case id is of a different page type!
				$id = $this->getShopRestOfTheWorldShippingZoneID();

				$shippingZone = $this->wire('pages')->get("template=" . PwCommerce::SHIPPING_ZONE_TEMPLATE_NAME . ",id={$id}");
			} else {
				// if null page, just return null
				$shippingZone = null;
			}
		}

		//----------------
		return $shippingZone;
	}

	// ================
	# ************



	/**
	 * Get the order's matched shipping zone's maximum shipping fee value.
	 *
	 * @return mixed
	 */
	public function getZoneMaximumShippingFee() {
		return $this->shippingZone->get(PwCommerce::SHIPPING_FEE_SETTINGS_FIELD_NAME)->maximumShippingFee;
	}

	/**
	 * Get shipping rates for order's matched shipping zone.
	 *
	 * @return mixed
	 */
	public function getZoneShippingRates() {
		$rates = null;
		$ratesPages = $this->getZoneShippingRatesPages();
		if (!empty($ratesPages)) {
			/** @var WireArray $rates */
			$rates = new WireArray();
			foreach ($ratesPages as $ratePage) {
				$rates->add($ratePage->get(PwCommerce::SHIPPING_RATE_FIELD_NAME));
			}
		}

		return $rates;
	}

	/**
	 * Get shipping rates pages for order's matched shipping zone.
	 *
	 * @return mixed
	 */
	public function getZoneShippingRatesPages() {
		$ratesPages = null;
		$count = 0;
		if (!empty($this->shippingZone)) {
			$ratesPages = $this->shippingZone->children("template=" . PwCommerce::SHIPPING_RATE_TEMPLATE_NAME . ",include=all,check_access=0");
			$count = $ratesPages->count();
		}

		// return !empty($ratesPages->count()) ? $ratesPages : null;
		return !empty($count) ? $ratesPages : null;
	}

	/**
	 * Get a shipping rate by its page ID.
	 *
	 * @param int $ratePageID
	 * @return mixed
	 */
	public function getShippingRateByID($ratePageID) {
		$ratePage = $this->wire('pages')->get("template=" . PwCommerce::SHIPPING_RATE_TEMPLATE_NAME . ",id={$ratePageID}");

		return !empty($ratePage->id) ? $ratePage->get(PwCommerce::SHIPPING_RATE_FIELD_NAME) : null;
	}

	/**
	 * Get compute matched shipping rates for given order.
	 *
	 * @return mixed
	 */
	protected function getOrderComputedMatchedShippingRates() {

		//
		// @note:
		// - It is possible to match several shipping rates
		// - However, we prioritise matching as follows: TODO: ok? revisit? confer?
		// a. CONDITIONAL RATES
		// (i) weight-based rates
		// (ii) price-based rates
		// (iii) quantity-based rates
		// b. NON-CONDITIONAL RATES (flat rates)
		// - if we get a match in a conditional rate, we DO NOT search the other conditions
		// - we always match (show/offer) flat rates if they are present (@note: if present, it means always matches!)
		//

		/** @var WireArray $matchedRates */
		$matchedRates = new WireArray();
		// ---------
		// IF NO PHYSICAL PRODUCT IN LINE ITEMS, RETURN EARLY
		if (empty($this->isShippingApplicable)) {
			return $matchedRates;
		}
		// =============

		// ATTEMPT TO MATCH CONDITIONAL RATES FIRST (as per above priority)

		// ---------
		// ** CHECK WEIGHT-BASED RATES FIRST **
		// TODO DELETE WHEN DONE
		//$matches = $this->getWeightBasedRatesMatches();
		$context = 'weight';
		$matches = $this->getConditionBasedRatesMatches($context);
		$foundMatches = false;

		if (!empty($matches)) {
			$foundMatches = true;

		}

		// ---------
		// ** GET PRICE-BASED RATE MATCHES IF WEIGHT-BASED MATCHES NOT FOUND **
		if (empty($foundMatches)) {

			$context = 'price';
			$matches = $this->getConditionBasedRatesMatches($context);
			if (!empty($matches)) {
				$foundMatches = true;

			}
		}

		// ---------
		// ** GET QUANTITY-BASED RATE MATCHES IF PRICE-BASED MATCHES NOT FOUND **
		if (empty($foundMatches)) {

			$context = 'quantity';
			$matches = $this->getConditionBasedRatesMatches($context);
			if (!empty($matches)) {
				$foundMatches = true;

			}
		}

		// =============

		if ($foundMatches) {
			// import into wirearray
			$matchedRates->import($matches);

		} else {
			// TODO: DELETE WHEN DONE!

		}

		// GET FLAT RATES (ALWAYS)
		// @note: these always match!
		// TODO: WILL NEED TO SHOW AS FLAT!
		$flatRatematches = $this->getZoneFlatRates();

		if (!empty($flatRatematches->count())) {
			$matchedRates->import($flatRatematches);
		}
		$isShippingTaxableOnOrder = $this->isShippingTaxableOnOrder();
		// ADD TAX TO EACH RATE IF APPLICABLE
		if (!empty($isShippingTaxableOnOrder)) {
			$shippingTaxRateAsPercentage = $this->getShippingTaxRatePercentage();
			$orderCountryRateAsPercentage = $this->getOrderCountryTaxPercentage();
			$isShippingTaxOverride = $shippingTaxRateAsPercentage !== $orderCountryRateAsPercentage;
			foreach ($matchedRates as $match) {
				$shippingRateWithoutTax = $match->shippingRate;
				$shippingRateWithTax = $this->getShippingFeeWithTax($shippingRateWithoutTax);
				$shippingRateTaxMoney = $this->getTaxAmountFromPriceExclusiveTax($shippingTaxRateAsPercentage, $shippingRateWithoutTax);
				$shippingRateTaxAmount = $this->getWholeMoneyAmount($shippingRateTaxMoney);

				$match->set('shippingRateWithoutTax', $shippingRateWithoutTax);
				$match->set('shippingRate', $shippingRateWithTax);
				$match->set('shippingRateTaxPercentage', $shippingTaxRateAsPercentage);
				$match->set('shippingRateTaxAmount', $shippingRateTaxAmount);
				$match->set('isShippingTaxableOnOrder', $isShippingTaxableOnOrder);
				$match->set('isShippingTaxOverride', $isShippingTaxOverride);
			}
		}

		// ============
		return $matchedRates;
	}

	// TODO PASS $order of get from $this->orderPage as this is only useful during a session?

	/**
	 * Get matched shipping rates for given order.
	 *
	 * @param Page $orderPage
	 * @return mixed
	 */
	protected function getOrderMatchedShippingRates(Page $orderPage) {
		$orderCalculatedShippingValues = $this->getOrderShippingValues($orderPage);
		// ------------
		return $orderCalculatedShippingValues->matchedShippingRates;
	}

	/**
	 * Match a condition-based rate for the shipping zone for this order.
	 *
	 * @param mixed $context
	 * @return mixed
	 */
	public function getConditionBasedRatesMatches($context) {
		$matches = null;
		$conditionCriteriaAmount = null;
		if ($context === 'weight') {
			$conditionCriteriaAmount = $this->getOrderWeight();
			$rates = $this->getZoneWeightBasedRates();
		} elseif ($context === 'price') {
			$conditionCriteriaAmountMoney = $this->getOrderSubTotalMoney($isForShippingRateCalculation = true);
			$conditionCriteriaAmount = $this->getWholeMoneyAmount($conditionCriteriaAmountMoney);
			$rates = $this->getZonePriceBasedRates();

		} elseif ($context === 'quantity') {
			$conditionCriteriaAmount = $this->getOrderTotalQuantity($isForShippingRateCalculation = true);
			$rates = $this->getZoneQuantityBasedRates();
		}

		// TODO OK?
		if (empty($conditionCriteriaAmount)) {
			return $matches; // TODO!
		}
		if (!empty($rates->count())) {

			// PREPARE RATES WITH NO shippingRateCriteriaMaximum. They have a zero (0) value. This means no upper bound
			// however, for find below, we need an integer/float. So, we convert these here
			// @note: we also append the conditionCriteriaAmount for reference (not saved to order for now! TODO?)
			foreach ($rates as $rate) {
				// append condition that was met
				$rate->conditionCriteriaAmount = $conditionCriteriaAmount;
				// -------
				if (!empty($rate->shippingRateCriteriaMaximum)) {
					continue;
				}
				// ------------

				$rate->shippingRateCriteriaMaximum = mt_getrandmax();

			}

			// ============
			// GET MATCHES
			$matches = $rates->find("shippingRateCriteriaMinimum<={$conditionCriteriaAmount},shippingRateCriteriaMaximum>={$conditionCriteriaAmount}");

			if (empty($matches->count())) {
				$matches = null;
			}

		}
		//------------
		return $matches;
	}
	// ================

	/**
	 * Return a WireArray with weight-based rates for the current order shipping zone.
	 *
	 * @return mixed
	 */
	public function getZoneWeightBasedRates() {
		/** @var WireArray $allRates */
		$allRates = $this->shippingZoneRates;
		$weightBasedRates = new WireArray();
		// $weightBasedRates = $allRates->find("shippingRateCriteriaType=weight");
		foreach ($allRates as $rate) {
			if ($rate->shippingRateCriteriaType !== 'weight') {
				continue;
			}
			/** @var WireData $rate */
			// add to WireArray
			$weightBasedRates->add($rate);
		}
		// ---------------
		return $weightBasedRates;
	}

	/**
	 * Return a WireArray with price-based rates for the current order shipping zone.
	 *
	 * @return mixed
	 */
	public function getZonePriceBasedRates() {
		/** @var WireArray $allRates */
		$allRates = $this->shippingZoneRates;

		$priceBasedRates = new WireArray();
		// $priceBasedRates = $allRates->find("shippingRateCriteriaType=price");
		foreach ($allRates as $rate) {
			if ($rate->shippingRateCriteriaType !== 'price') {
				continue;
			}
			// add to WireArray
			/** @var WireData $rate */
			$priceBasedRates->add($rate);
		}
		// ---------------
		return $priceBasedRates;
	}

	/**
	 * Return a WireArray with quantity-based rates for the current order shipping zone.
	 *
	 * @return mixed
	 */
	public function getZoneQuantityBasedRates() {
		/** @var WireArray $allRates */
		$allRates = $this->shippingZoneRates;
		$quantityBasedRates = new WireArray();
		// $quantityBasedRates = $allRates->find("shippingRateCriteriaType=quantity");
		foreach ($allRates as $rate) {
			if ($rate->shippingRateCriteriaType !== 'quantity') {
				continue;
			}
			// add to WireArray
			/** @var WireData $rate */
			$quantityBasedRates->add($rate);
		}
		// ---------------
		return $quantityBasedRates;
	}

	/**
	 * Return a WireArray with flat-based rates for the current order shipping zone.
	 *
	 * @return mixed
	 */
	public function getZoneFlatRates() {
		/** @var WireArray $allRates */
		$allRates = $this->shippingZoneRates;
		$flatRates = new WireArray();
		// $flatRates = $allRates->find("shippingRateCriteriaType=none");
		foreach ($allRates as $rate) {
			if ($rate->shippingRateCriteriaType !== 'none') {
				continue;
			}
			// add to WireArray
			/** @var WireData $rate */
			$flatRates->add($rate);
		}
		// ---------------
		return $flatRates;
	}

	#######################################

	/**
	 * Is Valid Shipping Rate I D For Order.
	 *
	 * @param int $orderSelectedShippingRateID
	 * @return bool
	 */
	private function isValidShippingRateIDForOrder($orderSelectedShippingRateID) {
		/** @var array $matchedShippingZoneRatesIDs */
		$matchedShippingZoneRatesIDs = $this->session->matchedShippingZoneRatesIDs;
		return in_array($orderSelectedShippingRateID, $matchedShippingZoneRatesIDs);
	}

	/**
	 * Set Order Page P W Commerce Order Shipping Values.
	 *
	 * @param Page $orderPage
	 * @return mixed
	 */
	private function setOrderPagePWCommerceOrderShippingValues(Page $orderPage) {
		// GET CALCULATED SHIPPING VALUES
		/** @var WireData $order */
		$order = $this->getOrderShippingValues($orderPage);

		// -----------
		// SET TO ORDER
		// @note: will be saved in saveOrder()
		// NOTE: in 'TraitPWCommerceSaveOrder'
		$orderPage->set(PwCommerce::ORDER_FIELD_NAME, $order);

		// ----
		$orderMatchedShippingRatesCount = $order->matchedShippingRates->count();

		// +++++++
		// TODO: IF NO MATCHED SHIPPING ZONE RATE, THROW ERROR! WHAT ABOUT DIGITAL GOODS?!!! - next release
		/** @var WireArray $order->matchedShippingRates */
		$matchedShippingZoneRatesIDs = $order->matchedShippingRates->explode('shippingRateID');

		// +++++++
		// SET SESSION VALUES FOR MATCHED SHIPPING ZONE & MATCHED SHIPPING ZONE RATES
		// TODO: would need to be reset if order details change before payment!
		$this->session->set('matchedShippingZoneID', $order->matchedShippingZoneID);
		// TODO WE SHOULD SET THIS EARLER BEFORE COUNT === 1! TO ALLOW FOR AJAX SETTING OF SELECTED SHIPPING RATE CHECK!, I.E. FOR the check by 'isValidShippingRateIDForOrder'!
		$this->session->set('matchedShippingZoneRatesIDs', $matchedShippingZoneRatesIDs);

		$this->session->set('isMatchedMultipleShippingRates', false);
		if (!empty($matchedShippingZoneRatesIDs)) {
			if ($orderMatchedShippingRatesCount > 1) {
				// MULTIPLE SHIPPING RATES WERE MATCHED
				$this->session->set('isMatchedMultipleShippingRates', true);
			}
		}

		// ++++++++++++
		// IN CASE A MATCHED SHIPPING RATE SELECTED 'EARLIER' VIA AJAX
		// WE CHECK & VALIDATE THAT HERE
		$preSelectedMatchedShippingRateID = $this->getValidatedPreSelectedMatchedShippingRate();

		// +++++++++++

		// ----------------------
		// TODO REFACTOR THIS LATER IF POSSIBLE!
		// IF ONLY ONE SHIPPING RATE MATCHED OR PRESELECTED; SET IT AS SELECTED AND RECALCULATE $order->totalPrice
		if ($orderMatchedShippingRatesCount === 1 || !empty($preSelectedMatchedShippingRateID)) {
			// $rate = $order->matchedShippingRates->first();
			$rate = $this->getSingleMatchedShippingRate($order->matchedShippingRates, $preSelectedMatchedShippingRateID);
			$order->isShippingFeeNeedsCalculating = true;
			$order->selectedMatchedShippingRate = $rate->shippingRate;
			$order->selectedMatchedShippingRateID = $rate->shippingRateID;
			// ###########
			// @note: new for PWCommerce === 004
			$order->shippingRateName = $rate->shippingRateName;
			$order->shippingRateDeliveryTimeMinimumDays = $rate->shippingRateDeliveryTimeMinimumDays;
			$order->shippingRateDeliveryTimeMaximumDays = $rate->shippingRateDeliveryTimeMaximumDays;
			// ###########
			// SET TO ORDER
			// TODO - SHOULD WE SET LATER?
			// @note: will be saved in saveOrder()
			// NOTE: in 'TraitPWCommerceSaveOrder'
			$orderPage->set(PwCommerce::ORDER_FIELD_NAME, $order);
			// -----------
			/** @var WireData $order */
			$order = $this->getOrderShippingValues($orderPage);
			$this->session->set('selectedMatchedShippingRateID', $rate->shippingRateID);

			// TODO here we now need to remove 'is matched multiple session flag' OR check order is has a preselected rate set.
		}

		// // -------------------
		// // SET SESSION VALUES FOR MATCHED SHIPPING ZONE & MATCHED SHIPPING ZONE RATES
		// // TODO: would need to be reset if order details change before payment!
		// $this->session->set('matchedShippingZoneID', $order->matchedShippingZoneID);
		// // TODO WE SHOULD SET THIS EARLER BEFORE COUNT === 1! TO ALLOW FOR AJAX SETTING OF SELECTED SHIPPING RATE CHECK!, I.E. FOR the check by 'isValidShippingRateIDForOrder'!
		// $this->session->set('matchedShippingZoneRatesIDs', $matchedShippingZoneRatesIDs);
		return $orderPage;
	}


	/**
	 * Get Validated Pre Selected Matched Shipping Rate.
	 *
	 * @return mixed
	 */
	private function getValidatedPreSelectedMatchedShippingRate() {
		$validPreselectedMatchedShippingRateID = 0;
		$preSelectedMatchedShippingRateID = (int) $this->wire('input')->post->order_selected_shipping_rate;
		if (!empty($preSelectedMatchedShippingRateID)) {
			$isValidPreSelectedMatchedShippingRateID = $this->isValidShippingRateIDForOrder($preSelectedMatchedShippingRateID);
			if (!empty($isValidPreSelectedMatchedShippingRateID)) {
				$validPreselectedMatchedShippingRateID = $preSelectedMatchedShippingRateID;
				// -----
				// ALSO SET TO SESSION SO THAT PWCommerceCheckout::isNeedToSelectFromMultipleMatchedShippingRates can pick it up when checking if to redirect to /checkout/shipping/
				// @UPDATE TODO? WE NOW USE THE SINGLE $this->session->isMatchedMultipleShippingRates
				// $this->session->set('validPreselectedMatchedShippingRateID', $validPreselectedMatchedShippingRateID);
				$this->session->set('isMatchedMultipleShippingRates', false);
			}
		}

		// ------
		return $validPreselectedMatchedShippingRateID;
	}

	/**
	 * Get Single Matched Shipping Rate.
	 *
	 * @param WireArray $matchedShippingRates
	 * @param int $preSelectedMatchedShippingRateID
	 * @return WireData
	 */
	private function getSingleMatchedShippingRate(WireArray $matchedShippingRates, int $preSelectedMatchedShippingRateID): WireData {
		// first check if we have a preselected (e.g. via ajax) shipping rate
		if (!empty($preSelectedMatchedShippingRateID)) {
			$rate = $matchedShippingRates->get("shippingRateID={$preSelectedMatchedShippingRateID}");
		} else {
			// otherwise get the single matched shipping rate in the WireArray
			$rate = $matchedShippingRates->first();
		}
		// ----
		return $rate;
	}



	// TODO CONFIRM STILL NEEDED
	/**
	 * Get Order Handling Fee Values.
	 *
	 * @param Page $orderPage
	 * @return mixed
	 */
	public function getOrderHandlingFeeValues(Page $orderPage) {
		$handlingFeeValues = new WireData();
		$handlingFeeProperties = ['handlingFeeType', 'handlingFeeValue', 'handlingFee'];
		$orderCalculatedShippingValues = $this->getOrderShippingValues($orderPage);
		// --------
		foreach ($handlingFeeProperties as $handlingFeeProperty) {
			$handlingFeeValues->set($handlingFeeProperty, $orderCalculatedShippingValues->$handlingFeeProperty);
		}
		// ------------
		return $handlingFeeValues;
	}

	/**
	 * Get Order Shipping Values.
	 *
	 * @param Page $orderPage
	 * @return mixed
	 */
	private function getOrderShippingValues(Page $orderPage) {
		// TODO: WE NEED THIS AGAIN IN parseCart(); should we save to memory? or just call again? latter for now
		$shippingCountry = $this->wire('pages')->get((int) $this->session->shippingAddressCountryID);
		$order = $orderPage->get(PwCommerce::ORDER_FIELD_NAME);
		// prepare options for getting order calculated shipping values
		// @note: at this point, there is no shipping fee since a matched shipping rate has not yet been selected!
		$orderCalculatedShippingValuesOptions = [
			/** @var Page $orderPage */
			'order_page' => $orderPage,
			/** @var WireData $order */
			'order' => $order,
			/** @var Page $shippingCountry */
			'shipping_country' => $shippingCountry,
		];

		// GET THE CALCULATED SHIPPING VALUES
		// includes: handlingFeeType; handlingFeeValue; handlingFee; totalPrice and matchedShippingRates
		// also does order discount!
		/** @var WireData $orderCalculatedShippingValues */
		$orderCalculatedShippingValues = $this->getOrderCalculatedValues($orderCalculatedShippingValuesOptions);
		// ------------
		return $orderCalculatedShippingValues;
	}

	# reprocessOrderValuesAfterShippingConfirmation

	# ~~~~~~~~~~~~~~
	/**
	 * Reprocess Order Values After Shipping Confirmation.
	 *
	 * @param Page $orderPage
	 * @return mixed
	 */
	private function reprocessOrderValuesAfterShippingConfirmation(Page $orderPage) {
		// @TODO: WE NEED THIS AGAIN IN parseCart(); should we save to memory? or just call again? latter for now
		$shippingCountry = $this->wire('pages')->get((int) $this->session->shippingAddressCountryID);
		$order = $orderPage->get(self::ORDER_FIELD_NAME);

		// prepare options for getting order calculated shipping values
		// @note: at this point, there is no shipping fee since a matched shipping rate has not yet been selected!
		$orderCalculatedShippingValuesOptions = [
			/** @var Page $orderPage */
			'order_page' => $orderPage,
			/** @var WireData $order */
			'order' => $order,
			/** @var Page $shippingCountry */
			'shipping_country' => $shippingCountry,
		];

		// GET THE CALCULATED SHIPPING VALUES
		// includes: handlingFeeType; handlingFeeValue; handlingFeeAmount; totalPrice and matchedShippingRates
		/** @var WireData $orderCalculatedShippingValues */
		// $orderCalculatedShippingValues = $this->pwcommerce->getOrderCalculatedShippingValues($orderCalculatedShippingValuesOptions);
		$orderCalculatedShippingValues = $this->getOrderCalculatedValues($orderCalculatedShippingValuesOptions);

		// ------------
		return $orderCalculatedShippingValues;
	}
}