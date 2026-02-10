<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Discounts: Trait class for PWCommerce Discounts.
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



trait TraitPWCommerceDiscounts
{

	private $action;
	private $digits;
	private Page $discountPage;
	private WireData $discount;
	private WireArray $discountAppliesTo;
	private WireArray $discountEligibility;
	// ++++++++++
	private $code;
	private string $customerEmail;
	private Page $customerShippingCountry;
	// +++++
	private $buyXEligibleItemsIDs;
	private $getYAppliesToItemsIDs;
	private $cartItemsCategories;
	// -----
	private $cartItemsProductsIDsToApplyDiscountTo = [];

	// ++++++
	// APPLY DISCOUNT VALIDATION
	private $discountValidityError;




	/**
	 * Get Allowed Discount Types.
	 *
	 * @return mixed
	 */
	public function getAllowedDiscountTypes() {
		return [
			// WHOLE ORDER
			'whole_order_percentage',
			'whole_order_fixed',
			// PRODUCTS
			'products_percentage',
			'products_fixed_per_order',
			'products_fixed_per_item',
			// CATEGORIES
			'categories_percentage',
			'categories_fixed_per_order',
			'categories_fixed_per_item',
			// FREE SHIPPING
			'free_shipping',
			// BOGO
			'categories_get_y',
			'products_get_y',
		];
	}

	/**
	 * Get Allowed Minimum Requirement Types.
	 *
	 * @return mixed
	 */
	public function getAllowedMinimumRequirementTypes() {
		return [
			'none',
			'purchase',
			'quantity'
		];
	}

	/**
	 * Get Allowed Applies To Item Types.
	 *
	 * @return mixed
	 */
	public function getAllowedAppliesToItemTypes() {
		// @note: the 'discount applies to 4 items of y' in a BOGO and the 'customer gets this discount [free/%] on these items' is saved in the meta of FieldtypePWCommerceDiscount. We get the values as properties during runtime as well
		return [
			// BOGO: GET Y PORTION
			'categories_get_y',
			'products_get_y',
			// WHOLE ORDER
			'whole_order_fixed',
			'whole_order_percentage',
			// PRODUCT
			'products_fixed',
			'products_percentage',
			// CATEGORY
			'categories_fixed',
			'categories_percentage',
			// SHIPPING
			'shipping_all_countries',
			'shipping_selected_countries',
		];
	}

	/**
	 * Get Allowed Eligibility Item Types.
	 *
	 * @return mixed
	 */
	public function getAllowedEligibilityItemTypes() {
		return [
			// CUSTOMER
			'all_customers',
			'specific_customers',
			'customer_groups',
			// BOGO
			'categories_buy_x',
			'products_buy_x'
		];
	}

	// TODO DELETE IF NOT IN USE
	/**
	 * Process Discount Action.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	public function processDiscountAction($input) {
		$this->input = $input;
		$response = null;
		// ------
		return $response;
	}

	////////////
	// ~~~~~~~~~~~~~~~~
	// DISCOUNT FRONTEND

	// TODO DELETE IF NOT IN USE. SHOULD DEV IMPLEMENT THEIR OWN?
	/**
	 * xxxxx.
	 *
	 * @return mixed
	 */
	public function redeemDiscountRender() {
		$t = $this->getPWCommerceTemplate("gift-card-redeem-html.php");
		$t->set("sessionAppliedDiscounts", $this->getSessionRedeemedDiscounts());
		$out = $t->render();
		// ------
		return $out;
	}

	// TODO AMEND THIS OR DELETE?!
	/**
	 * Get Discounts Info.
	 *
	 * @param array $discountsIDs
	 * @return mixed
	 */
	private function getDiscountsInfo(array $discountsIDs) {
		// TODO -> PAGE ARRAY FROM SELECTOR OR ARRAY?
		$discountsInfo = new PageArray();
		foreach ($discountsIDs as $discountsID) {
			$discountInfo = new NullPage();
			$discountInfo->id = $discountsID;
			$discountInfo->redeemable = random_int(10, 35);
			// --
			// add to fake PageArray for testing
			$discountsInfo->add($discountInfo);

		}
		return $discountsInfo;
	}

	////////////
	// ~~~~~~~~~~~~~~~~
	// DISCOUNT VALIDATION

	/**
	 * Set Discount Checks Properties.
	 *
	 * @param mixed $code
	 * @param mixed $customerEmail
	 * @param int $customerShippingCountryID
	 * @return mixed
	 */
	private function setDiscountChecksProperties($code, $customerEmail, $customerShippingCountryID) {
		// TODO SANITIZE AND SET TO $this->code, $this->customerEmail and $this->customerShippingCountry
		// set discount code
		$this->code = $this->wire('sanitizer')->sanitize($code, "text,selectorValue");
		// set customer email
		$this->customerEmail = $this->wire('sanitizer')->email($customerEmail);
		// set customer shipping country (Page)
		$this->customerShippingCountry = $this->wire('pages')->get((int) $customerShippingCountryID);
		// TODO HERE OR SOMEWHERE, ONCE WE APPLY AT LEAST ONE DISCOUNT, WE NEED TO SET  GENERIC SESSION VARIABLE, E.G. isDiscountAppliedToOrder = true; This will help us return early if not in use so don't need to recalculate. TODO also need to remove from session if all discounts removed; can we do a partial session->get or find? like a selector?

		// =====

	}

	/**
	 * Is Valid Discount.
	 *
	 * @param string $code
	 * @param string $customerEmail
	 * @param int $customerShippingCountryID
	 * @return bool
	 */
	public function isValidDiscount(string $code, string $customerEmail, int $customerShippingCountryID) {
		// @NOTE: WILL SET $this->code, $this->customerEmail and $this->customerShippingCountry
		$this->setDiscountChecksProperties($code, $customerEmail, $customerShippingCountryID);
		// +++++++++++
		/** @var array $isValid */
		$isValid = $this->_isValidDiscount();

		return $isValid;
	}

	/**
	 * Is Valid Discount By I D.
	 *
	 * @param int $discountID
	 * @param string $customerEmail
	 * @param int $customerShippingCountryID
	 * @return bool
	 */
	public function isValidDiscountByID(int $discountID, string $customerEmail, int $customerShippingCountryID) {
		$code = $this->getDiscountCodeByDiscountID($discountID);
		$isValid = $this->isValidDiscount($code, $customerEmail, $customerShippingCountryID);

		return $isValid;
	}

	/**
	 * Validate And Set Discount.
	 *
	 * @param string $code
	 * @param string $customerEmail
	 * @param int $customerShippingCountryID
	 * @return mixed
	 */
	public function validateAndSetDiscount(string $code, string $customerEmail, int $customerShippingCountryID) {
		// =====

		// ++++++++++++
		$result = new WireData();

		# ---------------
		// FIRST CHECK IF DISCOUNT IS VALID
		// @note: this will also sanitize and set these args as class properties
		/** @var array $isValid */
		$isValid = $this->isValidDiscount($code, $customerEmail, $customerShippingCountryID);
		$discountPage = $this->getDiscountPageByCode($code);

		# ++++++++++++
		// TRACK REDEEMED DISCOUNTS IF VALID
		if (!empty($isValid['is_valid'])) {
			// SUCCESSFUL VALIDATION
			# +++++++++++
			// APPLY DISCOUNT
			// ------

			/** @var WireData $discount */
			$discount = $discountPage->get(PwCommerce::DISCOUNT_FIELD_NAME);

			$isDiscountAlreadyApplied = $this->isDiscountAlreadyApplied($discountPage->id);

			if (!empty($isDiscountAlreadyApplied)) {
				$notice = $this->_('Discount has already been applied successfully.');
			} else {
				$notice = $this->_('Discount applied successfully.');
			}
			// ------
			$resultPropertiesAndValues = [
				'isValid' => true,
				'isApplied' => true,
				'isAlreadyApplied' => $isDiscountAlreadyApplied,
				'discountID' => $discountPage->id,
				'discountCode' => $discount->discountCode,
				'discountType' => $discount->discountType,
				'discountValue' => $discount->discountValue,
				'noticeType' => 'success',
				'notice' => $notice,
			];

			// TRACK REDEEMED DISCOUNT ID
			$this->trackRedeemedDiscountsIDs($discountPage->id);

			// $result->setArray($resultPropertiesAndValues);
		} else {
			// VALIDATION FAILED
			$resultPropertiesAndValues = [
				'isValid' => $isValid['is_valid'],
				// false
				'isApplied' => $isValid['is_valid'],
				// false
				'appliedAmount' => 0,
				'noticeType' => 'error',
				'notice' => $isValid['is_valid_notice'],
			];

			// EMPTY CLASS PROP FOR TRACKING ITEMS THAT DISCOUNT COULD BE APPLIED TO
			$this->cartItemsProductsIDsToApplyDiscountTo = [];

			// REMOVED ATTEMPTED REDEEMED DISCOUNT ID
			// this is to cater for instances where the cart has been amended after initial redeem
			// since this method (i.e., validateAndSetDiscount()) will be called by validateAndApplyDiscount(), we need to ensure that only valid redeemed discount IDs are sent for processing for discount value, i.e. for redeemedDiscounts
			// also needed in cases when the basket is amended an an attempt is made to re-apply a discount.
			$this->removeTrackedRedeemedDiscountID($discountPage->id);

		}
		# **********
		$result->setArray($resultPropertiesAndValues);

		return $result;
	}

	/**
	 * Validate And Set Discount By I D.
	 *
	 * @param int $discountID
	 * @param string $customerEmail
	 * @param string $customerShippingCountryID
	 * @return mixed
	 */
	public function validateAndSetDiscountByID(int $discountID, string $customerEmail, string $customerShippingCountryID) {
		// NOTE - strval in case discount was not found and this returns null!
		$code = strval($this->getDiscountCodeByDiscountID($discountID));
		$result = $this->validateAndSetDiscount($code, $customerEmail, $customerShippingCountryID);
		return $result;
	}

	/**
	 * Get Validation Checks.
	 *
	 * @return mixed
	 */
	private function getValidationChecks() {
		return [
			'is_valid_customer_email',
			'is_valid_customer_shipping_country',
			'is_exist',
			'is_live',
			'is_not_expired',
			'is_free_shipping_already_applied',
			'is_exclude_shipping_rates_over_certain_amount_satisfied',
			'is_customer_eligible',
			'is_global_limit_reached',
			'is_customer_limit_reached',
			'is_applies_to_requirements_satisfied',
			'is_minimum_purchase_met',
			'is_bogo_requirements_met',
		];
	}

	/**
	 * Get Invalid Discount Errors.
	 *
	 * @param string $singleErrorindex
	 * @return mixed
	 */
	private function getInvalidDiscountErrors($singleErrorindex = '') {

		$errors = [
			'is_valid_discount_code' => $this->_('Discount code is not valid.'),
			'is_valid_customer_email' => $this->_('Customer email not valid.'),
			'is_valid_customer_shipping_country_not_found' => $this->_('Uknown shipping country.'),
			'is_valid_customer_shipping_country' => $this->_('Country not eligible for free shipping.'),
			'is_exist' => $this->_('Discount not found.'),
			'is_live' => $this->_('Discount not yet active.'),
			'is_not_expired' => $this->_('Discount has expired.'),
			'is_free_shipping_already_applied' => $this->_('A free shipping discount has already been applied to this order.'),
			'is_exclude_shipping_rates_over_certain_amount_satisfied' => $this->_('Matched shipping rate does not qualify for free shipping.'),
			'is_customer_eligible' => $this->_('Customer email not eligible for this discount.'),
			'is_global_limit_reached' => $this->_('Discount usage limit reached.'),
			'is_customer_limit_reached' => $this->_('Discount usage for customer reached.'),
			'is_applies_to_requirements_satisfied' => $this->_('Minimum requirements for discount not met.'),
			// =============
			// @TOO REPHRASE!
			// products
			'is_applies_to_requirements_products_purchase_items_satisfied' => $this->_('No products to apply discount to found in your cart.'),
			'is_applies_to_requirements_products_purchase_amount_satisfied' => $this->_('Minimum requirement for products amount to purchase for discount not met.'),
			'is_applies_to_requirements_products_purchase_quantity_satisfied' => $this->_('Minimum requirement for products quantity to purchase for discount not met.'),
			// categories
			'is_applies_to_requirements_categories_purchase_items_satisfied' => $this->_('No products from eligible categories to apply discount to found in your cart.'),
			'is_applies_to_requirements_categories_purchase_amount_satisfied' => $this->_('Minimum requirement for categories amount to purchase for discount not met.'),
			'is_applies_to_requirements_categories_purchase_quantity_satisfied' => $this->_('Minimum requirement for categories quantity to purchase for discount not met.'),
			// shipping
			'is_applies_to_requirements_free_shipping_countries_satisfied' => $this->_('Shipping country not eligible for free shipping.'),

			###############
			// =======
			'is_minimum_purchase_met' => $this->_('Minimum purchase requirement for discount not met.'),
			// ========
			# BOGO
			// generic
			'is_bogo_requirements_met' => $this->_('Buy X Get Y requirements not met. Please amend basket.'),
			// min requirement quantity only
			'is_bogo_requirements_buy_x_quantity_met' => $this->_('Requirement of quantity of eligible items in cart for discount not met for Buy X Get Y discount. Please amend basket.'),
			'is_bogo_requirements_get_y_quantity_met' => $this->_('Requirement of items to add to basket to apply discount to not met for Buy X Get Y discount. Please amend basket.'),
			// min requirement purchase only
			'is_bogo_requirements_buy_x_purchase_met' => $this->_('Requirement of amount to spend on eligible items for discount not met for Buy X Get Y discount. Please amend basket.'),
			// min requirement purchase or quantity generic
			'is_bogo_requirements_buy_x_met' => $this->_('Requirement of items to buy from, or amount to spend, or quantity to buy not met for Buy X Get Y discount. Please amend basket.'),
			'is_bogo_requirements_get_y_met' => $this->_('Requirement of items to add to basket, or quantity of items to add to basket to apply discount to not met for Buy X Get Y discount. Please amend basket.'),
		];

		if (!empty($errors[$singleErrorindex])) {
			return $errors[$singleErrorindex];
		}
		// ----
		return $errors;

	}

	/**
	 *  is Valid Discount.
	 *
	 * @return mixed
	 */
	public function _isValidDiscount() {
		/** @var array $isValid */
		$isValid = $this->runValidateDiscountChecks();

		return $isValid;
	}

	/**
	 * Run Validate Discount Checks.
	 *
	 * @return mixed
	 */
	private function runValidateDiscountChecks() {

		$isValid = true;
		$validationResult = [];
		/** @var array $invalidDiscountErrors */
		$invalidDiscountErrors = $this->getInvalidDiscountErrors();
		/** @var array $checks */
		$checks = $this->getValidationChecks();

		// ---------
		// loop through checks, run validation, and stop if INVALID
		########
		foreach ($checks as $check) {
			$isValid = $this->validateDiscountCheck($check);
			if (empty($isValid)) {

				// $error = $invalidDiscountErrors[$check];
				// @note: some checks can produce very specific errors, e.g. BOGO
				// in that case error is save to $this->$discountValidityError
				// otherwise we default to generic error for the discount type in $invalidDiscountErrors[$check]
				$error = !empty($this->discountValidityError) ? $this->discountValidityError : $invalidDiscountErrors[$check];
				// =======
				$validationResult = [
					'is_valid' => false,
					'is_valid_notice' => $error,
				];
				// break at first 'invalid' check
				break;
			}
		}
		// FINAL
		if (!empty($isValid)) {
			// SUCCESS
			$success = $this->_('Discount passed validation');
			$validationResult = [
				'is_valid' => true,
				'is_valid_notice' => $success,
			];
		}

		// --------
		return $validationResult;

	}

	/**
	 * Validate Discount Check.
	 *
	 * @param mixed $check
	 * @return mixed
	 */
	private function validateDiscountCheck($check) {

		// $isValid = false;/ TODO NEEDED?

		// TODO CHECKS FOR BOGO ...GET_Y!

		// TODO ALSO NEED TO ADD EXCLUDE SHIPPING RATES SPECIAL CHECK! AGAIN, THIS IS DIFFERENT FROM MINIM PURCHASE REQUIREMENS ('none', 'purchase', 'quantity')

		switch ($check) {

			# PRELIMINARY CHECKS #

			// CHECK IF SUPPLIED CUSTOMER EMAIL IS VALID
			// i.e. $sanitizer->email()
			case 'is_valid_customer_email':
				$isValid = $this->isValidCustomerEmail();
				break;

			// CHECK IF SUPPLIED SHIPPING COUNTRY IS VALID
			// i.e. COUNTRY EXISTS IN SHOP
			case 'is_valid_customer_shipping_country':
				$isValid = $this->isValidCustomerShippingCountry();
				break;

			# #################
			// CHECK IF DISCOUNT CODE IS VALID
			// i.e. DISCOUNT EXISTS
			case 'is_exist':
				$isValid = $this->isExistDiscount();
				break;

			// CHECK IF DISCOUNT IS LIVE
			// i.e. has started
			case 'is_live':
				$isValid = $this->isLiveDiscount();
				break;

			// CHECK IF DISCOUNT HAS NOT EXPIRED
			case 'is_not_expired':
				$isValid = $this->isNotExpiredDiscount();
				break;

			// CHECK IF A FREE SHIPPING DISCOUNT HAS ALREADY BEEN APPLIED TO ORDER
			case 'is_free_shipping_already_applied':
				$isValid = $this->isFreeShippingDiscountAlreadyAppliedToOrder();
				break;

			// CHECK IF EXCLUDE SHIPPING RATES OVER A CERTAIN AMOUNT REQUIREMENT IS SATISFIED
			case 'is_exclude_shipping_rates_over_certain_amount_satisfied':
				$isValid = $this->isFreeShippingExcludeRatesOverCertainAmountRequirementSatisfied();
				break;

			// CHECK IF CUSTOMER IS ELIGIBLE FOR DISCOUNT
			// i.e. if for everyone vs specific customer groups/customers
			case 'is_customer_eligible':
				$isValid = $this->isCustomerEligibleForDiscount();
				break;

			// CHECK IF DISCOUNT GLOBAL LIMIT REACHED
			// TODO: A NEGATIVE; NEEDS TO RETURN A the opposite!
			case 'is_global_limit_reached':
				$isValid = $this->isDiscountGlobalLimitReached();
				break;

			// CHECK IF DISCOUNT PER CUSTOMER LIMIT REACHED
			// TODO: A NEGATIVE; NEEDS TO RETURN A the opposite!
			case 'is_customer_limit_reached':
				$isValid = $this->isDiscountPerCustomerLimitReached();
				break;

			// CHECK IF DISCOUNT MININIUM REQUIREMENTS SATISFIED
			case 'is_applies_to_requirements_satisfied':
				$isValid = $this->isDiscountMinimumPurchaseAndAppliesToRequirementsSatisfied();
				break;

			// CHECK IF DISCOUNT MININIUM PURCHASE MET
			// @NOTE: THIS IS ALREADY DONE FOR PRODUCTS, CATEGORIES AND FREE SHIPPING IN the check for 'is_applies_to_requirements_satisfied' above, i.e. $this->isDiscountMinimumPurchaseAndAppliesToRequirementsSatisfied()
			// SO, ONLY DONE FOR 'whole_order' discounts
			// @NOTE: for BOGO, see next check/test
			case 'is_minimum_purchase_met':
				$isValid = $this->isDiscountMinimumPurchaseMet();
				break;
			# ~~~~~~~~~
			// SPECIAL
			# ~~~~~~~~~
			// CHECK IF BOGO DISCOUNT MININIUM REQUIREMENTS MET
			// @note: this is a catch all for BUY X ELIGIBILITY (both customer spends amount AND any items from) AND  GET Y APPLIES TO (both quantity of items AND any items from)
			case 'is_bogo_requirements_met':
				$isValid = $this->isBOGODiscountMinimumRequirementsMet();
				break;

		}

		return $isValid;
	}

	/**
	 * Is Valid Customer Email.
	 *
	 * @return bool
	 */
	private function isValidCustomerEmail() {
		$sanitizedEmail = $this->wire('sanitizer')->email($this->customerEmail);
		$isValid = !empty($sanitizedEmail);
		return $isValid;
	}

	/**
	 * Is Valid Customer Shipping Country.
	 *
	 * @return bool
	 */
	private function isValidCustomerShippingCountry() {
		// @note: $this->customerShippingCountry was already set in $his->setDiscountChecksProperties() via $this->isValidDiscount()
		// it is a Page
		$isValid = !$this->customerShippingCountry instanceof NullPage;
		if (empty($isValid)) {
			$this->discountValidityError = $this->getInvalidDiscountErrors('is_valid_customer_shipping_country_not_found');
		}

		return $isValid;
	}

	/**
	 * Is Exist Discount.
	 *
	 * @return bool
	 */
	private function isExistDiscount() {
		// @note: $this->code was already set in $his->setDiscountChecksProperties() via $this->isValidDiscount()
		// it is a string
		$selector = "template=discount, discount.code={$this->code}, status<" . Page::statusUnpublished;
		$discountPage = $this->get($selector);
		$isValid = !$discountPage instanceof NullPage;

		if (!empty($isValid)) {
			// SET DISCOUNT PAGE TO CLASS PROPERTY!
			/** @var Page $this->discountPage */
			$this->discountPage = $discountPage;
			// +++++++++++
			// SET DISCOUNT FIELD VALUE TO CLASS PROPERTY
			/** @var WireData $this->discount */
			$this->discount = $discountPage->get(PwCommerce::DISCOUNT_FIELD_NAME);
			// +++++++++++
			// SET DISCOUNT FIELD APPLIES TO TO CLASS PROPERTY
			/** @var WireArray $this->discountAppliesTo */
			$this->discountAppliesTo = $discountPage->get(PwCommerce::DISCOUNT_APPLIES_TO_FIELD_NAME);
			// +++++++++++
			// SET DISCOUNT FIELD ELIGIBILITY TO CLASS PROPERTY
			/** @var WireArray $this->discountEligibility */
			$this->discountEligibility = $discountPage->get(PwCommerce::DISCOUNT_ELIGIBILITY_FIELD_NAME);
			# ~~~~~~~~~~

		}
		return $isValid;
	}

	/**
	 * Is Live Discount.
	 *
	 * @return bool
	 */
	private function isLiveDiscount() {
		$today = time();
		// @NOTE TODO: CURRENTLY THIS IS TIMESTAMP IN WAKEUPVALUE! BUT MIGHT CHANGE IN FUTURE; SO WE CHECK HERE!
		$discountStartDate = $this->discount->discountStartDate;
		$discountStartDateTimestamp = is_int($discountStartDate) ? $discountStartDate : strtotime($discountStartDate);
		$isValid = $today > $discountStartDateTimestamp;

		return $isValid;
	}

	/**
	 * Is Not Expired Discount.
	 *
	 * @return bool
	 */
	private function isNotExpiredDiscount() {
		$today = time();
		// @NOTE TODO: CURRENTLY THIS IS TIMESTAMP IN WAKEUPVALUE! BUT MIGHT CHANGE IN FUTURE; SO WE CHECK HERE!
		$discountEndDate = $this->discount->discountEndDate;
		$discountEndDateTimestamp = is_int($discountEndDate) ? $discountEndDate : strtotime($discountEndDate);
		$isValid = true;
		// ONLY VALIDATE END DATE IF ONE HAS BEEN SET!
		if ($discountEndDateTimestamp > 0) {
			// WE HAVE AN END DATE:: CHECK VALIDITY!
			$isValid = $discountEndDateTimestamp > $today;

		}

		return $isValid;
	}

	/**
	 * Is Free Shipping Discount Already Applied To Order.
	 *
	 * @return bool
	 */
	private function isFreeShippingDiscountAlreadyAppliedToOrder() {
		// @note - WE ONLY ALLOW ONE FREE SHIPPING PER ORDER
		//  it doesn't make sense to have multiple free shipping in one order!
		// -------------

		$isValid = true;

		$discountType = $this->discount->discountType;

		if ($this->isFreeShippingDiscount($discountType)) {

			$redeemedDiscountsIDs = $this->getSessionRedeemedDiscountsIDs();
			if (!empty($redeemedDiscountsIDs)) {
				// @NOTE DON'T CHECK THIS'FREE SHIPPING DISCOUNT' ITSELF; WE ONLY CHECK OTHER 'FREE SHIPPING DISCOUNTS'
				$incomingFreeShippingDiscountID = $this->discountPage->id;
				unset($redeemedDiscountsIDs[$incomingFreeShippingDiscountID]);

				$redeemedDiscountsIDsSelector = implode("|", $redeemedDiscountsIDs);
				$selector = "template=discount,id={$redeemedDiscountsIDsSelector},discount.discount_type=free_shipping";
				$fields = 'id';

				$freeShippingDiscountID = (int) $this->getRaw($selector, $fields);

				$isValid = empty($freeShippingDiscountID);

				if (empty($isValid)) {
					// PREPARE ERROR: FREE SHIPPING ALREADY APPLIED TO ORDER!
					$errorType = 'is_free_shipping_already_applied';
					$this->discountValidityError = $this->getInvalidDiscountErrors($errorType);
				}

			}

		}

		//--------
		return $isValid;
	}

	/**
	 * Is Free Shipping Exclude Rates Over Certain Amount Requirement Satisfied.
	 *
	 * @return bool
	 */
	private function isFreeShippingExcludeRatesOverCertainAmountRequirementSatisfied() {
		// TODO: HOW TO HANDLE MULTIPLE MATCHED SHIPPING RATES? WHICH ONE DO WE CHECK FOR EXCLUDE SHIPPING RATES OVER A CERTAIN AMOUNT? THE HIGHEST OR THE LOWEST AND WHY? HOW DO WE THEN CHOOSE WHICH DELIVERY TIME TO USE? PWCOMMERCE? DEVS?
		// @note - WE ONLY ALLOW ONE FREE SHIPPING PER ORDER
		//  it doesn't make sense to have multiple free shipping in one order!
		// -------------

		$isValid = true;

		$discountType = $this->discount->discountType;
		// get exclude shipping rate limit
		$excludeShippingAmountOver = $this->discount->excludeShippingAmountOver;

		if ($this->isFreeShippingDiscount($discountType) && !empty($excludeShippingAmountOver)) {

			// ----------
			$orderPage = $this->getOrderPage();
			// if order page not yet created
			// we send a dummy NullPage
			// @note: order page could be available if customer had made it to checkout
			// but decided to continue shopping
			if (empty($orderPage)) {
				$orderPage = new NullPage();
				$order = new WireData();
			} else {
				$order = $orderPage->get('pwcommerce_order');
			}

			// ---------
			// GET MATCHED SHIPPING RATE(S) and HANDLING FEE AMOUNT FOR THIS ORDER
			// -------

			// ------
			$orderCalculatedShippingValuesOptions = [
				/** @var Page|NullPage $orderPage */
				'order_page' => $orderPage,
				/** @var WireData $order */
				'order' => $order,
				/** @var Page $this->customerShippingCountry */
				'shipping_country' => $this->customerShippingCountry,
				// we need to tell getOrderCalculatedValues to calculate values from
				// corresponding products of items in cart
				// i.e., this is for fetching shipping rates via ajax
				'is_for_live_shipping_rate_calculation' => true
			];

			/** @var WireData $orderCalculatedShippingValues */
			$orderCalculatedShippingValues = $this->getOrderCalculatedValues($orderCalculatedShippingValuesOptions);
			/** @var WireArray $matchedShippingRates */
			$matchedShippingRates = $orderCalculatedShippingValues->matchedShippingRates;

			// TODO WHAT IF DIGITAL ORDERS?! THIS WILL FAIL! need to say shipping not applicable?!
			if ($matchedShippingRates->count()) {
				// TODO COMPUTE TOTAL SHIPPING FEE (EXCLUDING TAX)
				// i.e. lowest shipping rate + handling fee amount
				// -----
				$excludeShippingAmountOverMoney = $this->money($excludeShippingAmountOver);

				// get lowest shipping rate
				$lowestShippingRate = $matchedShippingRates->get("sort=shippingRate");
				// get its shipping fee/charge
				$shippingFee = $lowestShippingRate->shippingRate;
				$shippingFeeMoney = $this->money($shippingFee);
				// get handling fee amount
				$handlingFee = $orderCalculatedShippingValues->handlingFee;
				$handlingFeeMoney = $this->money($handlingFee);
				// compute total shipping fee
				$totalShippingFeeMoney = $shippingFeeMoney->add($handlingFeeMoney);

				// ~~~~~~~~~
				// validate
				// $isValid = $excludeShippingAmountOver > $totalShippingFee;
				$isValid = $excludeShippingAmountOverMoney->greaterThan($totalShippingFeeMoney);

				// -------

				if (empty($isValid)) {
					// PREPARE ERROR: FREE SHIPPING DOES NOT APPLY TO THIS SHIPPING RATE
					$errorType = 'is_exclude_shipping_rates_over_certain_amount_satisfied';
					$this->discountValidityError = $this->getInvalidDiscountErrors($errorType);
				}
			}

			// ---------

		}

		//--------
		return $isValid;
	}
	/**
	 * Is Customer Eligible For Discount.
	 *
	 * @return bool
	 */
	private function isCustomerEligibleForDiscount() {
		// IF CUSTOMER ELIGIBILITY TYPE IS NOT 'all_customers;
		// we then check if eligibility is 'specific_customers' OR 'customer_groups'
		$isValid = true;
		// =======
		/** @var WireArray $discountEligibility */
		$discountEligibility = $this->discountEligibility;

		if ($discountEligibility->get("itemType=all_customers")) {
			// CUSTOMER ELIGIBILITY: ALL CUSTOMERS
			// NOTHING TO DO
			// ALL customer eligible

		} else {
			// only customer groups or specific customers eligible
			// ++++++++
			// get this customer's page to check if their customer group(s) is one of the eligible customer groups
			// OR if their customer ID is one of the elibigle 'specific customers'
			// NOTE: since we need a record to check against for a supplied email address...
			// we cannot compare someone@email.com == someone@email.com without having that record!
			// that record is saved customers (both guest and registered)
			// this means if shop admin wants to give specific people, including potential customers, discount codes
			// they'll also need to be added to the shop 'customers'
			$customer = $this->get("template=customer,customer.email={$this->customerEmail}");


			#########
			// DETERMINE IF DEALING WITH GROUPS OR SPECIFIC CUSTOMERS

			# +++++++
			if ($discountEligibility->get("itemType=" . PwCommerce::DISCOUNT_ELIGIBILITY_CUSTOMER_GROUPS)) {
				// CUSTOMER ELIGIBILITY: CUSTOMER GROUPS
				$checkType = PwCommerce::DISCOUNT_ELIGIBILITY_CUSTOMER_GROUPS;
				// ======
				// get all the eligible customer groups IDs
				/** @var WireArray $eligibleGroups */
				$eligibleGroups = $discountEligibility->find("itemType=" . PwCommerce::DISCOUNT_ELIGIBILITY_CUSTOMER_GROUPS);
				/** @var array $eligibleGroupsIDs */
				$eligibleIDs = $eligibleGroups->explode('itemID');

			} else if ($discountEligibility->get("itemType=" . PwCommerce::DISCOUNT_ELIGIBILITY_SPECIFIC_CUSTOMERS)) {
				// CUSTOMER ELIGIBILITY: SPECIFIC CUSTOMERS
				$checkType = PwCommerce::DISCOUNT_ELIGIBILITY_SPECIFIC_CUSTOMERS;
				// ======
				// get all the eligible specific customers IDs
				/** @var WireArray $eligibleCustomers */
				$eligibleCustomers = $discountEligibility->find("itemType=" . PwCommerce::DISCOUNT_ELIGIBILITY_SPECIFIC_CUSTOMERS);
				/** @var array $eligibleCustomersIDs */
				$eligibleIDs = $eligibleCustomers->explode('itemID');

			}

			// DO THE CHECK!
			if ($checkType === PwCommerce::DISCOUNT_ELIGIBILITY_SPECIFIC_CUSTOMERS) {
				// is this customer ID part of eligible 'specific_customers'
				$isValid = in_array($customer->id, $eligibleIDs);

			} else {
				// is this customer's groups IDs part of eligible 'customer_groups'
				// $isValid = in_array($customer->id, $eligibleIDs);
				/** @var PageArray $customerGroups */
				$customerGroups = $customer->get(PwCommerce::CUSTOMER_GROUPS_FIELD_NAME);
				// TODO CONFIRM THIS WORKS EVEN WHEN THE PAGE FIELD IS EMPTY!
				if (empty($customerGroups)) {
					$isValid = false;
				} else {
					$customerGroupsIDs = $customerGroups->explode('id');
					$interSection = array_intersect($customerGroupsIDs, $eligibleIDs);
					// -------
					$isValid = !empty($interSection);
				}

			}

		}

		// ------
		return $isValid;
	}

	/**
	 * Is Discount Global Limit Reached.
	 *
	 * @return bool
	 */
	private function isDiscountGlobalLimitReached() {
		// count current usage and see if that >= limit
		// TODO findRaw vs $pages->count()? findRaw for now!
		// @OTODO TEST! + ADD/UPDATE A FEW TO HAVE USED SOME OF THE DISCOUNTS!!!! BOTH SINGLE AND MULTI!
		$fields = 'id';
		/** @var array $discountOrdersPagesIDs */
		$discountOrdersPagesIDs = $this->findRaw("template=order,order_discounts.code={$this->code}", $fields);
		$discountUsage = count($discountOrdersPagesIDs);
		$isValid = true;
		// ONLY CHECK IF LIMIT REACHED IF WE HAVE A DISCOUNT LIMIT TOTAL
		if (!empty($this->discount->discountLimitTotal)) {
			// WE HAVE A DISCOUNT LIMIT TOTAL: CHECK VALIDITY!
			$isValid = $this->discount->discountLimitTotal > $discountUsage;
		}

		return $isValid;
	}

	/**
	 * Is Discount Per Customer Limit Reached.
	 *
	 * @return bool
	 */
	private function isDiscountPerCustomerLimitReached() {
		// count current usage OF THIS CUSTOMER and see if that >= per customer limit
		// TODO findRaw vs $pages->count()? findRaw for now!
		// @OTODO TEST! + ADD/UPDATE A FEW TO HAVE USED SOME OF THE DISCOUNTS!!!! BOTH SINGLE AND MULTI!
		$fields = 'id';
		/** @var array $customerDiscountOrdersPagesIDs */
		$customerDiscountOrdersPagesIDs = $this->findRaw("template=order,order_discounts.code={$this->code},order_customer.email={$this->customerEmail}", $fields);
		$customerDiscountUsage = count($customerDiscountOrdersPagesIDs);
		$isValid = true;
		// ONLY CHECK IF PER CUSTOMER LIMIT REACHED IF WE HAVE A DISCOUNT LIMIT PER CUSTOMER
		if (!empty($this->discount->discountLimitPerCustomer)) {
			// WE HAVE A DISCOUNT LIMIT PER CUSTOMER: CHECK VALIDITY!
			$isValid = $this->discount->discountLimitPerCustomer > $customerDiscountUsage;
		}

		return $isValid;
	}

	/**
	 * Is Discount Minimum Purchase And Applies To Requirements Satisfied.
	 *
	 * @return bool
	 */
	private function isDiscountMinimumPurchaseAndAppliesToRequirementsSatisfied() {

		// TODO NOT APPLICABLE FOR ORDER!
		// TODO ONLY APPLIES TO 'free_shipping', 'categories' and 'products'
		// TODO ALSO, DO SEPARATE FOR BOGO?
		// TODO FOR PRODUCTS AND CATEGORIES, WE NEED TO CHECK MINIMUM APPLIES TO AND MINIMUM PURCHASE AMOUNT IN TANDEM IN CASE OF QUANTITY! E.G. if discount says customer must buy 10 items as min qty, this must come from the applies to categoreis! if the categories include men's and women's categories, it means, the number of items from men's or from women's or from both MUST TOTAL >= 10 items!!!
		// @UPDATE: SATURDAY, 14 OCTOBER 2023, 1952: ABOVE LOGIC IS WRONG! YOU ARE TREATING THIS LIKE BOGO! FOR CATEGORIES AND PRODUCTS, WE DON'T CARE IF THE MIN PURCHASE REQS COME FROM THOSE CATEGORIES/PRODUCTS! THEY CAN BE FROM ANY CATEGORY OR PRODUCT. THE ONLY THING THAT NEEDS TO BE FULFILLED IS THAT: 'IS THERE AT LEAST ONE ITEM, CATEGORY OR PRODUCT, ON WHICH WE CAN APLY THIS DISCOUNT?'. THAT'S IT! THESE CATEGORIES/PRODUCTS, BEYOND THIS, DO NOT NEED TO ALL COUNT TOWARDS THE MIN PURCHASE AMOUNT OR THE MIN QUANTITY! FOR EXAMPLE, IF A CATEGORY DISCOUNT APPLIES TO SUMMER SHIRTS AND SUMMER DRESSES AND THE MIN QUANTITY REQ IS 3...IF THE CUSTOMER HAS 3 ITEMS IN THEIR BASKET, THAT MAKES IT ELIGIBLE FOR THE FIRST PART OF THE CHECK. NEXT, WE CHECK, IS AT LEAST ONE OF THOSE ITEMS FROM EITHER 'SUMMER SHIRTS' OR 'SUMMER DRESSES'? IF YES, BOOM! DISCOUNT WILL BE APPLIED, BUT ONLY TO THE ITEM(S) THAT ARE SUMMER SHIRTS OR SUMMER DRESSES. HOWEVER, IF THEY HAD ONLY 2 ITEMS IN THEIR CART, WE WOULDN'T EVEN BOTHER STARTING THE CHECK! HENCE, MIN REQ AMOUNT STILL HAS TO BE CHECKED IN TANDEM WITH THE 'APPLIES TO CATEGORY/PRODUCT' PART. EXAMPLE 2, IF A PRODUCT DISCOUNT APPLIES TO SAY, A 'RED SHIRT', 'RED SHOES' AND 'YELLOW SOCKS' AND THE MIN PURCHASE AMOUNT IS $5... IF THE CART AMOUNT (PRICE) IS AT LEAST >= $5, THIS TRIGGERS A CHECK IF AT LEAST OF THE ITEMS IS A 'RED SHIRT' OR 'RED SHOES' OR 'YELLOW SOCKS'. IF NO, WE RESPOND WITH A MESSAGE ABOUT NOT HAVING AT LEAST ONE REQUIRED PRODUCT TO APPLY THE DISCOUNT TO. SIMILAR MESSAGE FOR CATEGORIES; DONE!
		// ++++++++++++
		// GET THE DISCOUNT TYPE
		$discountType = $this->discount->discountType;
		# ++++++++++++++
		// @NOTE: THIS CHECK DOES NOT APPLY TO BOGO AND WHOLE ORDER! THAT IS DONE SEPARATELY AS SPECIAL CHECKS IN $this->isBOGODiscountMinimumRequirementsMet() AND $this->isDiscountMinimumPurchaseMet()
		// hence, return true here to pass checks if discount type is BOGO, just so we can move to the next check(s)
		if ($this->isBogoDiscount($discountType) || $this->isWholeOrderDiscount($discountType)) {
			$isValid = true;
			return $isValid;
		}
		# ++++++++++++
		// $isValid = false;
		// ++++++++++++

		if ($this->isCategoriesDiscount($discountType)) {
			// DISCOUNT TYPE: CATEGORIES
			$isValid = $this->isDiscountMinimumPurchaseAndAppliesToCategoriesRequirementsSatisfied();

		} else if ($this->isProductsDiscount($discountType)) {
			// DISCOUNT TYPE: PRODUCTS
			$isValid = $this->isDiscountMinimumPurchaseAndAppliesToProductsRequirementsSatisfied();

		} else if ($this->isFreeShippingDiscount($discountType)) {
			// DISCOUNT TYPE: FREE SHIPPING
			$isValid = $this->isDiscountMinimumPurchaseAndAppliesToFreeShippingRequirementsSatisfied();

		}

		// ------
		return $isValid;
	}

	/**
	 * Is Discount Minimum Purchase And Applies To Categories Requirements Satisfied.
	 *
	 * @return bool
	 */
	private function isDiscountMinimumPurchaseAndAppliesToCategoriesRequirementsSatisfied() {
		// TODO FOR PRODUCTS AND CATEGORIES, WE NEED TO CHECK MINIMUM APPLIES TO AND MINIMUM PURCHASE AMOUNT IN TANDEM IN CASE OF QUANTITY! E.G. if discount says customer must buy 10 items as min qty, this must come from the applies to categoreis! if the categories include men's and women's categories, it means, the number of items from men's or from women's or from both MUST TOTAL >= 10 items!!!
		// @UPDATE: SATURDAY, 14 OCTOBER 2023, 1952: ABOVE LOGIC IS WRONG! @SEE isDiscountMinimumPurchaseAndAppliesToRequirementsSatisfied FOR EXPLANATION!
		// $isValid = true;
		$isValid = false; // @note: default to false so that we don't return true in case part 2 of the checks does not run
		// GET CART IN SESSION
		// @NOTE: WE CANNOT USE getOrder(). This is because that requires the order to have been created during checkout form, after clicking 'proceed to confirmation'
		// however, discount can be applied via AJAX in the same form but BEFORE this button has been clicked!
		/** @var array $cart */
		$cart = $this->getOrderCart();
		// =======
		/** @var WireData $discount */
		$discount = $this->discount;

		// =======
		/** @var WireArray $discountAppliesTo */
		$discountAppliesTo = $this->discountAppliesTo;
		$discountAppliesToCategoriesIDsSaved = $discountAppliesTo->explode('itemID');

		// GET PRODUCTS (including VARIANTS) IDS for items in cart
		// $cartProductsIDs = array_column($cart, 'product_id');
		// @note: we oop through cart so that we can get 'pwcommerce_variant_parent_id' IF CART ITEM IS VARIANT!
		// this is because the CATEGORIES live in the product itself and not the variant!
		$cartProductsIDs = [];
		foreach ($cart as $item) {
			if (!empty($item->pwcommerce_is_variant)) {
				$cartProductsIDs[] = $item->pwcommerce_variant_parent_id;

			} else {
				$cartProductsIDs[] = $item->product_id;
			}
		}
		# +++++++++++
		// GET CATEGORIES IDs FOR THE PRODUCTS IN THE CART
		// this is because we are dealing with a CATEGORIES discount here
		$cartProductsIDsSelector = implode("|", $cartProductsIDs);
		// $fields = ["pwcommerce_categories.id","pwcommerce_categories.title"];
		$fields = ["pwcommerce_categories" => ["id", "title"]];
		// ===========
		$discountAppliesToCategoriesIDsSaved = $discountAppliesTo->explode('itemID');
		$discountAppliesToCategoriesIDsSavedSelector = implode("|", $discountAppliesToCategoriesIDsSaved);
		// FIND PRODUCTS IN THE CART THAT HAVE THE SAVED DISCOUNT APPLIES TO CATEGORIES!
		$cartCategories = $this->findRaw("template=product,id={$cartProductsIDsSelector},pwcommerce_categories={$discountAppliesToCategoriesIDsSavedSelector}", $fields);
		#######

		// lOOP THROUGH TO FIND CATEGORIES FOR THE PRODUCTS
		// @note: some might be empty!
		$cartCategoriesIDs = [];
		// @note: we will use this to calculate 'applies to items' total price or total quantity for MIN REQ when MIN REQ is not 'none'!
		$cartCategoriesProductsIDs = [];
		foreach ($cartCategories as $productID => $categories) {
			// @note: $categories is nested; the 'id' we want is the index in level 2
			// before reset, this looks something like this:
			//  'pwcommerce_categories' => array
			// 1827 => array
			// 'id' => 1827
			// 'title' => 'Accessories'

			// get the inner items in $categories
			$categories = reset($categories);
			// after reset we get something like this:
			//   array
			// 1827 => array
			// 'id' => 1827
			// 'title' => 'Accessories'
			// 1813 => array
			// 'id' => 1813
			// 'title' => 'Kitchen'

			if (empty($categories)) {
				// no categories; skip!

				continue;
			}

			// $cartCategoriesIDs = array_merge($cartCategoriesIDs, array_column($outer, 'id'));
			// grab the category IDs of the product from the key and merge to array of categories IDs
			$cartCategoriesIDs = array_merge($cartCategoriesIDs, array_keys($categories));
			$cartCategoriesProductsIDs[] = $productID;
		}
		# ++++++++++
		// @UPDATE: SUNDAY 15 OCTOBER 2023 12PM. REVERT TO THE ORIGINAL (AND CORRECT) LOGIC!
		// ONLY APPLIES TO ITEMS SHOULD CONTRIBUTE TO THIS! NOT THE WHOLE CART! HENCE, USE $interSection BELOW INSTEAD! SO DO THE PART 2 LOGIC FIRST IN ORDER TO GET $interSection
		// $cartProductsQuantity = array_column($cart, 'quantity');
		// GET TOTAL QUANTITY OF ITEMS IN CART
		// $cartProductsTotalQuantity = array_sum(array_column($cart, 'quantity'));
		// // GET TOTAL PRICE PURCHASE OF ITEMS IN CART
		// $cartProductsPriceTotal = array_sum(array_column($cart, 'pwcommerce_price_total'));

		# ++++++++++
		// @UPDATE: SUNDAY 15 OCTOBER 2023 12PM. REVERT TO THE ORIGINAL (AND CORRECT) LOGIC!
		// ONLY APPLIES TO ITEMS SHOULD CONTRIBUTE TO THIS! NOT THE WHOLE CART! HENCE, USE $interSection BELOW INSTEAD! SO DO THE PART 2 LOGIC FIRST IN ORDER TO GET $interSection

		// ~~~~~~~~~
		// @NOTE: THERE ARE TWO PARTS TO PRODUCTS VALIDITY
		// PART 1 is also affected by PART 2 in case MIN PURCHASE REQ IS NOT 'none'!!!!
		// 1a. is MIN REQ is 'none'?: ONLY CHECK FOR AT LEAST ONE 'APPLIES TO' PRODUCT IN THE CART. This is so that we have an item to discount!
		// 1b. is MIN REQ  'quantity' or 'purchase'?: FILTER THE CART TO GRAB ONLY THE APPLIES TO ITEMS THEN SUM THEIR TOTAL QTY (MIN REQ QTY) OR THEIR TOTAL PRICE (MIN REQ AMOUNT £). THEN, CHECK IF TOTAL QTY >= DISCOUNTMINREQAMOUNT! || TOTAL PRICE >= DISCOUNTMINREQAMOUNT!
		//PART 2 ONLY kicks in if 1b IS TRUE: SO CAN RUN IT IN THAT ELSE CONDITIONAL!

		# PART 1a.
		// CHECK IF AT LEAST ONE PRODUCT ITEM IN THE CART IS IN THE LIST OF APPLIES TO CATEGORIES FOR THIS DISCOUNT
		// @note: $interSection is array of CATEGORIES IDs! NOT PRODUCTS! For products, it is $cartCategoriesProductsIDs
		$interSection = array_intersect($cartCategoriesIDs, $discountAppliesToCategoriesIDsSaved);

		// track product IDs of cart items that this discount could be applied to
		// @note: we will empty this if final validation returns invalid discount conditions
		// @note: for categories discount, for product variants, we track product parent ID!
		$this->cartItemsProductsIDsToApplyDiscountTo = $cartCategoriesProductsIDs;

		// TODO FOR MIN PURCHASE CHECKS WE NEED THE PRODUCTS AGAIN! BUT WE NEED ONLY THE APPLIES TO ONES! THIS IS BECAUSE WE CANNOT USE CATEGORIES FOR INTERSECTION CHECKS AS THEY HAVE NO PRICE OR QTY IN THE CART!
		// $cartProductsIDs = array_column($cart, 'product_id');

		// +++++++++++++++++
		// COMPUTE VALIDITY
		$isValid = !empty($interSection);
		if ($isValid) {
			$isCategoryMode = true;
			// --------
			# PART 1b. CHECK
			if ($discount->discountMinimumRequirementType !== 'none') {
				if ($discount->discountMinimumRequirementType === 'purchase') {
					// MIN REQ: PURCHASE AMOUNT (£)

					$checkMinimumAmount = $this->getCartAppliesToProductsTotalForMinimumPurchaseRequirement($isCategoryMode, $cartCategoriesProductsIDs);
					$errorType = 'purchase';

				} else {
					// MIN REQ: PURCHASE QUANTITY OF ITEMS
					$checkMinimumAmount = $this->getCartAppliesToProductsTotalForMinimumPurchaseRequirement($isCategoryMode, $cartCategoriesProductsIDs, 'quantity');
					$errorType = 'quantity';

				}
				// -----
				# PART 2 CHECK
				// CHECK IF minimum amount reached
				// $isValid = $discount->discountMinimumRequirementAmount <= $checkMinimumAmount;
				$isValid = $checkMinimumAmount >= $discount->discountMinimumRequirementAmount;

				if (empty($isValid)) {
					// PREPARE ERROR: EITHER MIN REQ QTY or MIN REQ PURCHASE AMOUNT NOT MET!
					if ($errorType === 'purchase') {
						// PURCHASE AMOUNT (£)
						$this->discountValidityError = $this->getInvalidDiscountErrors('is_applies_to_requirements_categories_purchase_amount_satisfied');
					} else {
						// PURCHASE QUANTITY
						$this->discountValidityError = $this->getInvalidDiscountErrors('is_applies_to_requirements_categories_purchase_quantity_satisfied');
					}
				}
			} else {
				// NOTHING TO DO; MIN  PURCHASE REQ IS 'none'
				// $isValid = true;
			}
		} else {
			// ERROR: PART 1a. DID NOT PASS
			// no need for further checks
			// prepare error about 'no item eligible for discount found in cart'
			$this->discountValidityError = $this->getInvalidDiscountErrors('is_applies_to_requirements_categories_purchase_items_satisfied');
		}

		// +++++++++

		// PART 1 CHECK: at least one APPLIES TO ITEM in the cart

		# ********

		// -------
		return $isValid;
	}

	/**
	 * Is Discount Minimum Purchase And Applies To Products Requirements Satisfied.
	 *
	 * @return bool
	 */
	private function isDiscountMinimumPurchaseAndAppliesToProductsRequirementsSatisfied() {
		// TODO FOR PRODUCTS AND CATEGORIES, WE NEED TO CHECK MINIMUM APPLIES TO AND MINIMUM PURCHASE AMOUNT IN TANDEM IN CASE OF QUANTITY! E.G. if discount says customer must buy 10 items as min qty, this must come from the applies to categoreis! if the categories include men's and women's categories, it means, the number of items from men's or from women's or from both MUST TOTAL >= 10 items!!!
		// @UPDATE: SATURDAY, 14 OCTOBER 2023, 1952: ABOVE LOGIC IS WRONG! @SEE isDiscountMinimumPurchaseAndAppliesToRequirementsSatisfied FOR EXPLANATION!
		// $isValid = true;
		$isValid = false; // @note: default to false so that we don't return true in case part 2 of the checks does not run
		// GET CART IN SESSION
		// @NOTE: WE CANNOT USE getOrder(). This is because that requires the order to have been created during checkout form, after clicking 'proceed to confirmation'
		// however, discount can be applied via AJAX in the same form but BEFORE this button has been clicked!
		/** @var array $cart */
		$cart = $this->getOrderCart();
		// GET PRODUCTS (including VARIANTS) IDS for items in cart
		$cartProductsIDs = array_column($cart, 'product_id');
		# ++++++++++
		// @UPDATE: SUNDAY 15 OCTOBER 2023 12PM. REVERT TO THE ORIGINAL (AND CORRECT) LOGIC!
		// ONLY APPLIES TO ITEMS SHOULD CONTRIBUTE TO THIS! NOT THE WHOLE CART! HENCE, USE $interSection BELOW INSTEAD! SO DO THE PART 2 LOGIC FIRST IN ORDER TO GET $interSection
		// $cartProductsQuantity = array_column($cart, 'quantity');
		// GET TOTAL QUANTITY OF ITEMS IN CART
		// $cartProductsTotalQuantity = array_sum(array_column($cart, 'quantity'));
		// // GET TOTAL PRICE PURCHASE OF ITEMS IN CART
		// $cartProductsPriceTotal = array_sum(array_column($cart, 'pwcommerce_price_total'));
		// =======
		/** @var WireData $discount */
		$discount = $this->discount;

		// =======
		/** @var WireArray $discountAppliesTo */
		$discountAppliesTo = $this->discountAppliesTo;

		// @note: we also need to account for product variants!
		// this is because in the discount edit GUI, we allow admins to only specify the PARENT PRODUCT for it to apply to its variants as well
		// if only variants are specified, it means the discount is only for those variants
		// hence here, for all specified product IDs, we get their children 'variants'
		// if found, it means the product is a parent product; if not found it means the product either has no children (has no variants) or is a variant itself (no children), meaning, only apply to the variant
		$discountAppliesToProductsIDsSaved = $discountAppliesTo->explode('itemID');
		// TODO HERE GRAB THE CHILDREN [variants] (if applicable)
		$idsSelector = implode("|", $discountAppliesToProductsIDsSaved);
		$variantsSelector = "template=variant,parent.id={$idsSelector}";
		$fields = "id,parent_id";
		$discountAppliesToProductsIDs = $this->findRaw($variantsSelector, $fields);
		// TODO PROCESS ABOVE SO WE REMOVE DUPLICATES;? MORE IMPORTANTLY NEED TO MERGE THE RAW ONES THAT WERE VARIANTS THEMSELVES OR WERE WITHOUT VARIANTS AS THEY WOULD NOT BE RETURNED IN THE FIND RAW! GET THEM FROM $discountAppliesToProductsIDsSaved!!!
		// ^^^^^^^^^

		// MERGE DISCOUNT SAVED PRODUCT IDS to FIND VARIANTS IDS
		$foundVariantsIDsForDiscountAppliesToProducts = array_column($discountAppliesToProductsIDs, 'id');

		$discountAppliesToProductsIDs = array_merge($discountAppliesToProductsIDsSaved, $foundVariantsIDsForDiscountAppliesToProducts);

		# ++++++++++
		// @UPDATE: SUNDAY 15 OCTOBER 2023 12PM. REVERT TO THE ORIGINAL (AND CORRECT) LOGIC!
		// ONLY APPLIES TO ITEMS SHOULD CONTRIBUTE TO THIS! NOT THE WHOLE CART! HENCE, USE $interSection BELOW INSTEAD! SO DO THE PART 2 LOGIC FIRST IN ORDER TO GET $interSection

		// ~~~~~~~~~
		// @NOTE: THERE ARE TWO PARTS TO PRODUCTS VALIDITY
		// PART 1 is also affected by PART 2 in case MIN PURCHASE REQ IS NOT 'none'!!!!
		// 1a. is MIN REQ is 'none'?: ONLY CHECK FOR AT LEAST ONE 'APPLIES TO' PRODUCT IN THE CART. This is so that we have an item to discount!
		// 1b. is MIN REQ  'quantity' or 'purchase'?: FILTER THE CART TO GRAB ONLY THE APPLIES TO ITEMS THEN SUM THEIR TOTAL QTY (MIN REQ QTY) OR THEIR TOTAL PRICE (MIN REQ AMOUNT £). THEN, CHECK IF TOTAL QTY >= DISCOUNTMINREQAMOUNT! || TOTAL PRICE >= DISCOUNTMINREQAMOUNT!
		//PART 2 ONLY kicks in if 1b IS TRUE: SO CAN RUN IT IN THAT ELSE CONDITIONAL!

		# ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

		# PART 1a.
		// CHECK IF AT LEAST ONE PRODUCT ITEM IN THE CART IS IN THE LIST OF APPLIES TO PRODUCTS FOR THIS DISCOUNT
		// $interSection = array_intersect($cartProductsIDs, $discountAppliesToProductsIDsSaved);
		// @see NOTES ABOVE: $discountAppliesToProductsIDs is merged to $discountAppliesToProductsIDsSaved (in order to get IDs of variants IDs where applicable)
		$interSection = array_intersect($cartProductsIDs, $discountAppliesToProductsIDs);
		// track product IDs of cart items that this discount could be applied to
		// @note: we will empty this if final validation returns invalid discount conditions
		$this->cartItemsProductsIDsToApplyDiscountTo = $interSection;

		// +++++++++++++++++
		// COMPUTE VALIDITY
		$isValid = !empty($interSection);

		if ($isValid) {
			$isCategoryMode = false;
			// --------
			# PART 1b. CHECK
			if ($discount->discountMinimumRequirementType !== 'none') {
				if ($discount->discountMinimumRequirementType === 'purchase') {
					// MIN REQ: PURCHASE AMOUNT (£)

					$checkMinimumAmount = $this->getCartAppliesToProductsTotalForMinimumPurchaseRequirement($isCategoryMode, $interSection);

					$errorType = 'is_applies_to_requirements_products_purchase_amount_satisfied';
				} else {
					// MIN REQ: PURCHASE QUANTITY OF ITEMS
					$checkMinimumAmount = $this->getCartAppliesToProductsTotalForMinimumPurchaseRequirement($isCategoryMode, $interSection, 'quantity');

					$errorType = 'is_applies_to_requirements_products_purchase_quantity_satisfied';
				}
				// -----
				# PART 2 CHECK
				// CHECK IF minimim amount reached
				// $isValid = $discount->discountMinimumRequirementAmount <= $checkMinimumAmount;
				$isValid = $checkMinimumAmount >= $discount->discountMinimumRequirementAmount;

				if (empty($isValid)) {
					// PREPARE ERROR: EITHER MIN REQ QTY or MIN REQ PURCHASE AMOUNT NOT MET!
					$this->discountValidityError = $this->getInvalidDiscountErrors($errorType);
				}
			} else {
				// NOTHING TO DO; MIN  PURCHASE REQ IS 'none'
				// $isValid = true;
			}
		} else {
			// ERROR: PART 1a. DID NOT PASS
			// no need for further checks
			// prepare error about 'no item eligible for discount found in cart'
			$this->discountValidityError = $this->getInvalidDiscountErrors('is_applies_to_requirements_products_purchase_items_satisfied');
		}

		// +++++++++
		# ********

		// -------
		return $isValid;
	}

	/**
	 * Get Cart Applies To Products Total For Minimum Purchase Requirement.
	 *
	 * @param mixed $isCategoryMode
	 * @param array $appliesToProductIDs
	 * @param string $minimumRequirementType
	 * @return mixed
	 */
	private function getCartAppliesToProductsTotalForMinimumPurchaseRequirement($isCategoryMode, array $appliesToProductIDs, $minimumRequirementType = 'purchase') {

		/** @var array $cart */
		$cart = $this->getOrderCart();
		$cartAppliesToProductsTotalAmount = 0;
		// --------
		# IF DEALING WITH CATEGORIES (discount type of category or BUY X category eligibility)
		# WE NEED TO USE PARENT_ID of cart item TO SKIP items that are not related to the dicount!
		# ELSE, we need to use the product ID itself to skip such items
		# THESE RELATED IDS are in $appliesToProductIDS
		# For the former, this onyly incudes PRODUCT and VARIANT PARENT PRODUCT IDs
		# FOR THE LATTER, this only includes PRODUCT AND VARIANT IDs
		// $isCategoryMode = $this->isCategoriesDiscount($this->discount->discountType) || $this->isBogoBuyXCategoriesDiscount($this->discountEligibility);
		// $isCategoryMode = $this->isCategoryModeForProductsTotalForMinimumPurchaseRequirement();

		// -------
		foreach ($cart as $item) {
			if (!empty($isCategoryMode)) {
				$itemProductID = $item->pwcommerce_is_variant ? $item->pwcommerce_variant_parent_id : $item->product_id;

			} else {
				$itemProductID = $item->product_id;

			}
			// =========
			if (!in_array($itemProductID, $appliesToProductIDs)) {

				// SKIP: NONE 'APPLIES TO' PRODUCT
				continue;
			}
			if ($minimumRequirementType === 'quantity') {
				// SUM THE QUANTITY!
				$cartAppliesToProductsTotalAmount += $item->quantity;

			} else {
				// SUM THE PURCHASE!
				$cartAppliesToProductsTotalAmount += $item->pwcommerce_price_total;

			}

		}

		return $cartAppliesToProductsTotalAmount;
	}

	/**
	 * Is Category Mode For Products Total For Minimum Purchase Requirement.
	 *
	 * @return bool
	 */
	private function isCategoryModeForProductsTotalForMinimumPurchaseRequirement() {
		$isCategoryMode = $this->isCategoriesDiscount($this->discount->discountType) || $this->isBogoBuyXCategoriesDiscount() || $this->isBogGetYCategoriesDiscount();

		// ------
		return $isCategoryMode;
	}

	/**
	 * Is Discount Minimum Purchase And Applies To Free Shipping Requirements Satisfied.
	 *
	 * @return bool
	 */
	private function isDiscountMinimumPurchaseAndAppliesToFreeShippingRequirementsSatisfied() {
		// ********
		// TODO IMPLEMENT 'EXCLUDE SHIPPING RATES OVER CERTAIN AMOUNT' FROM FREE SHIPPING
		// TODO HOW DO WE CHECK EXCLUDE RATES OVER AMOUNT? WE WILL NEED TO CHECK SHIPPING COSTS ON THE FLY THEN! LIKE IN LIVE SHIPPING RATES!!!!
		// ~~~~~~~~~
		// @NOTE: THERE ARE TWO PARTS TO FREE SHIPPING VALIDITY
		// PART 1 is NOT affected by PART 2 per se: but we need it to be valid in order to check PART 2.
		// is SHIPPING COUNTRY MIN REQ 'satisfied'?: If free shipping to 'shipping_all_countries', the $isValid is true; If free sipping to 'shipping_selected_countries' then $isValid ONLY TRUE if customer $this->customerShippingCountry->id is one of APPIES TO COUNTRIES; So, if FALSE, we don't bother checking PART 2.

		//PART 2 ONLY kicks in if 1 IS TRUE: SO CAN RUN IT IN THAT ELSE CONDITIONAL!
		// HERE WE CHECK IF THERE IS A PURCHASE MINIMUM QTY OR AMOUNT  (£)
		// 2a. is MIN REQ is 'none'?: NOTHING TO DO; $isValid will carry ON  PER PART 1
		// 2b. is MIN REQ  'quantity' or 'purchase'?: FILTER THE CART TO GRAB ONLY THE APPLIES TO ITEMS THEN SUM THEIR TOTAL QTY (MIN REQ QTY) OR THEIR TOTAL PRICE (MIN REQ AMOUNT £). THEN, CHECK IF TOTAL QTY >= DISCOUNTMINREQAMOUNT! || TOTAL PRICE >= DISCOUNTMINREQAMOUNT!
		// =========
		# PART 1.
		// CHECK IF CUSTOMER SHIPPING COUNTRY IS IN THE LIST OF APPLIES TO COUNTRIES in case APPLIES TO IS 'shipping_selected_countries' FOR THE DISCOUNT
		// IF FREE SHIPPING APPLIES TO NOT 'shipping_all_countries;
		// we check if CUSTOMER SHIPING COUNTRY IS ONE OF SELECTED COUNTRIES FOR THIS DISCOUNT
		$isValid = true;
		// =======
		/** @var WireData $discount */
		$discount = $this->discount;

		// =======
		/** @var WireArray $discountAppliesTo */
		$discountAppliesTo = $this->discountAppliesTo;

		if ($discountAppliesTo->get("itemType=" . PwCommerce::DISCOUNT_APPLIES_TO_SELECTED_COUNTRIES)) {
			// SHIPPING APPLIES TO: SELECTED COUNTRIES

			// TODO WIP
			// GET THE SELECTED COUNTRIES
			/** @var WireArray $appliesToCountries */
			$appliesToCountries = $discountAppliesTo->find("itemType=" . PwCommerce::DISCOUNT_APPLIES_TO_SELECTED_COUNTRIES);
			/** @var array $appliesToCountriesIDs */
			$appliesToCountriesIDs = $appliesToCountries->explode('itemID');

			// NEXT, DO THE CHECK
			// is this customer's shipping country ID part of applies to 'shipping_selected_countries'

			$isValid = in_array($this->customerShippingCountry->id, $appliesToCountriesIDs);

			// -----
			if (empty($isValid)) {
				// TODO: MOVED BELOW!

				// // ERROR:
				// // prepare error about 'customer shipping country not eligible for free shipping'
				// $this->discountValidityError = $this->getInvalidDiscountErrors('is_applies_to_requirements_free_shipping_countries_satisfied');

			}
		}

		// +++++++++++++++++

		// PART 2: CHECK MINIMUM PURCHASE REQUIREMENT
		if ($isValid) {
			// --------
			# PART 2b. CHECK
			if ($discount->discountMinimumRequirementType !== 'none') {
				// GET CART IN SESSION
				// @NOTE: WE CANNOT USE getOrder(). This is because that requires the order to have been created during checkout form, after clicking 'proceed to confirmation'
				// however, discount can be applied via AJAX in the same form but BEFORE this button has been clicked!
				/** @var array $cart */
				$cart = $this->getOrderCart();
				// GET PRODUCTS (including VARIANTS) IDS for items in cart
				$cartProductsIDs = array_column($cart, 'product_id');

				# ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
				$isCategoryMode = false;
				if ($discount->discountMinimumRequirementType === 'purchase') {
					// MIN REQ: PURCHASE AMOUNT (£)
					$checkMinimumAmount = $this->getCartAppliesToProductsTotalForMinimumPurchaseRequirement($isCategoryMode, $cartProductsIDs);

					$errorType = 'is_applies_to_requirements_products_purchase_amount_satisfied';
				} else {
					// MIN REQ: PURCHASE QUANTITY OF ITEMS
					$checkMinimumAmount = $this->getCartAppliesToProductsTotalForMinimumPurchaseRequirement($isCategoryMode, $cartProductsIDs, 'quantity');

					$errorType = 'is_applies_to_requirements_products_purchase_quantity_satisfied';
				}
				// -----
				# PART 2 CHECK IF minimim amount reached
				// $isValid = $discount->discountMinimumRequirementAmount <= $checkMinimumAmount;
				$isValid = $checkMinimumAmount >= $discount->discountMinimumRequirementAmount;

				if (empty($isValid)) {
					// PREPARE ERROR: EITHER MIN REQ QTY or MIN REQ PURCHASE AMOUNT NOT MET!
					$this->discountValidityError = $this->getInvalidDiscountErrors($errorType);
				}
			} else {
				// NOTHING TO DO; MIN  PURCHASE REQ IS 'none'
				// $isValid = true;
			}
		} else {
			// ERROR:
			// no need for further checks
			// prepare error about 'customer shipping country not eligible for free shipping'
			$this->discountValidityError = $this->getInvalidDiscountErrors('is_applies_to_requirements_free_shipping_countries_satisfied');

		}

		// -----------
		return $isValid;
	}

	/**
	 * Is Discount Exclude Shipping Rates Over A Certain Amount Free Shipping Requirement Satisfied.
	 *
	 * @return bool
	 */
	private function isDiscountExcludeShippingRatesOverACertainAmountFreeShippingRequirementSatisfied() {
		// TODO USE LIVE SHIPPING RATES TO CHECK THIS
	}

	/**
	 * Is Discount Minimum Purchase Met.
	 *
	 * @return bool
	 */
	private function isDiscountMinimumPurchaseMet() {
		// @NOTE: THIS IS ALREADY DONE FOR PRODUCTS, CATEGORIES AND FREE SHIPPING IN the check for 'is_applies_to_requirements_satisfied' above, i.e. $this->isDiscountMinimumPurchaseAndAppliesToRequirementsSatisfied()
		// HERE WE ONLY DO IT FOR ORDER. WE WILL DO THIS SEPARATELY FOR BOGO!S
		$isValid = true;
		// =======
		/** @var WireData $discount */
		$discount = $this->discount;

		if ($this->isWholeOrderDiscount($discount->discountType)) {
			// CHECK IF minimim amount reached
			$isValid = $this->checkDiscountMinimumAmountValidity();

		}

		return $isValid;
	}

	/**
	 * Check Discount Minimum Amount Validity.
	 *
	 * @return mixed
	 */
	private function checkDiscountMinimumAmountValidity() {
		// =======
		/** @var WireData $discount */
		$discount = $this->discount;
		// =======
		// GET CART IN SESSION
		// @NOTE: WE CANNOT USE getOrder(). This is because that requires the order to have been created during checkout form, after clicking 'proceed to confirmation'
		// however, discount can be applied via AJAX in the same form but BEFORE this button has been clicked!
		/** @var array $cart */
		$cart = $this->getOrderCart();
		# ++++++++++
		// $cartProductsQuantity = array_column($cart, 'quantity');
		$cartProductsTotalQuantity = array_sum(array_column($cart, 'quantity'));
		$cartProductsPriceTotal = array_sum(array_column($cart, 'pwcommerce_price_total'));
		// =======
		$checkMinimumAmount = $discount->discountMinimumRequirementType === 'purchase' ? $cartProductsPriceTotal : $cartProductsTotalQuantity;
		// -----
		// CHECK IF minimim amount reached
		$checkMinimumAmountValidity = $discount->discountMinimumRequirementAmount <= $checkMinimumAmount;
		// -----
		return $checkMinimumAmountValidity;
	}

	/**
	 * Is B O G O Discount Minimum Requirements Met.
	 *
	 * @return bool
	 */
	private function isBOGODiscountMinimumRequirementsMet() {
		// ++++++++++++
		// GET THE DISCOUNT TYPE
		$discountType = $this->discount->discountType;
		# ++++++++++++++
		// @NOTE: THIS CHECK ONLY APPLIES TO BOGO! PRODUCTS AND CATEGORIES ALREADY DONE IN $this-.isDiscountMinimumPurchaseAndAppliesToRequirementsSatisfied() AND WHOLE ORDER IN $this->isDiscountMinimumPurchaseMet()
		// hence, return true here to pass checks if discount type IS NOT BOGO, just so we can move to the next check(s)
		if (!$this->isBogoDiscount($discountType)) {

			$isValid = true;
			return $isValid;
		}
		####################
		$isValid = false;
		// ----------
		// CHECK IF BOGO DISCOUNT MININIUM REQUIREMENTS MET
		// @note: this is a catch all for BUY X ELIGIBILITY (both customer spends amount AND any items from) AND  GET Y APPLIES TO (both quantity of items AND any items from)
		// we check each in turn
		// +++++++++++++
		$isBuyXEligibilityValid = $this->isBOGOCustomerBuysXFromAndSpendRequirementMet();

		if (empty($isBuyXEligibilityValid)) {
			// BUY X NOT VALID
			// set error and return early
			// @NOTE: ERROR HAS ALREADY BEEN SET IN $this->isBOGOCustomerBuysXFromAndSpendRequirementMet()
			// $this->discountValidityError = $this->getInvalidDiscountErrors('is_bogo_requirements_buy_x_met');
			return $isValid;
		}
		// GOOD TO GO: BUY X REQS SATISFIED
		$isGetYAppliesToValid = $this->isBOGOCustomerGetsYFromAndQuantityOfItemsRequirementMet();
		if (empty($isGetYAppliesToValid)) {
			// GET Y NOT VALID
			// set error and return early
			// @NOTE: ERROR HAS ALREADY BEEN SET IN $this->isBOGOCustomerGetsYFromAndQuantityOfItemsRequirementMet()
			// $this->discountValidityError = $this->getInvalidDiscountErrors('is_bogo_requirements_get_y_met');
			return $isValid;
		}
		// ------
		// ALL GOOD AND VALID
		$isValid = true;

		// ---------
		return $isValid;
	}

	##############################

	// BUY X

	/**
	 * Is B O G O Customer Buys X From And Spend Requirement Met.
	 *
	 * @return bool
	 */
	private function isBOGOCustomerBuysXFromAndSpendRequirementMet() {
		$isValid = false; // @note: default to false so that we don't return true in case part 2 of the checks does not run
		# ++++++++++
		// =======
		/** @var WireData $discount */
		$discount = $this->discount;

		// =======
		/** @var WireArray $discountEligibility */
		$discountEligibility = $this->discountEligibility;

		$isCategoriesBuyX = $this->isBogoBuyXCategoriesDiscount();
		if ($isCategoriesBuyX) {
			// CATEGORY BUY X BOGO
			$checkItems = $this->getCartItemsCategoriesIDs();
			$checkSavedItems = $this->getSavedBuyXEligibilityCategoriesIDs();

		} else {
			// PRODUCT BUY X BOGO

			//
			$checkItems = $this->getCartItemsProductsIDs();

			// @NOTE: includes 'unsaved' VARIANT IDs in cases where the eligibility itemID is a parent product!
			$checkSavedItems = $this->getSavedBuyXEligibilityProductsIDs();

		}

		$eligibleItems = array_intersect($checkItems, $checkSavedItems);

		################
		# PART 1 CHECK: BUY X ELIGIBILITY ITEM(S) IN CART
		$isBuyXEligibilityItemsInCart = !empty($eligibleItems);

		if (empty($isBuyXEligibilityItemsInCart)) {
			// INVALID: RETURN EARLY
			// no buy x items in cart
			$errorType = 'is_bogo_requirements_met';

			$this->discountValidityError = $this->getInvalidDiscountErrors($errorType);
			;
			// ----
			return $isValid;
		}

		################
		# PART 2 CHECK: MIN REQUIREMENT COMPUTE (quantity OR spend (£))
		$this->buyXEligibleItemsIDs = $eligibleItems;

		if ($isCategoriesBuyX) {
			// CATEGORY BUY X BOGO: GET PRODUCT IDs from ELIGIBLE CATEGORIES for SUMMING
			// @note: this will first get the products IDs for eligible products
			// then get the corresponding cart item product or variant product IDs!
			$checkItemsForSum = $this->getBuyXEligibleCategoriesProductsIDs();
		} else {
			// PRODUCT BUY X BOGO: ALREADY HAVEPRODUCT IDs from ELIGIBLE CATEGORIES for SUMMING
			// @note: this will check both products and products variants parent_ids!
			$checkItemsForSum = $this->getBuyXEligibleProductsIDs();
		}

		$checkItemsSum = $this->getBuyXEligibleItemsSum($checkItemsForSum);

		# PART 3 CHECK
		// CHECK IF minimum amount reached
		$isValid = $checkItemsSum >= $discount->discountMinimumRequirementAmount;

		if (empty($isValid)) {
			// PREPARE ERROR: EITHER MIN REQ QTY or MIN REQ PURCHASE AMOUNT FOR BUY X NOT MET!
			$errorType = $discount->discountMinimumRequirementType === 'purchase' ? 'is_bogo_requirements_buy_x_purchase_met' : 'is_bogo_requirements_buy_x_quantity_met';
			$this->discountValidityError = $this->getInvalidDiscountErrors($errorType);
		}
		// +++++++++
		# ********

		// -----

		return $isValid;
	}

	/**
	 * Get Cart Items Categories I Ds.
	 *
	 * @return array
	 */
	private function getCartItemsCategoriesIDs(): array {
		$isIncludeProductIDs = false;
		$cartCategoriesIDs = $this->getCartItemsCategoriesIDsWithAssociatedProductsIDs($isIncludeProductIDs);

		// -------
		return $cartCategoriesIDs;
	}

	/**
	 * Get Cart Items Categories I Ds With Associated Products I Ds.
	 *
	 * @param bool $isIncludeProductIDs
	 * @return mixed
	 */
	private function getCartItemsCategoriesIDsWithAssociatedProductsIDs(bool $isIncludeProductIDs = true) {

		$cartTopLevelProductsIDs = $this->getOrderCartTopLevelProductsIDs();

		$cartCategoriesIDs = [];
		// GET CATEGORIES IDs FOR THE PRODUCTS IN THE CART
		// @note: since we have top level products IDs here, we don't need to consider variants; they have no categories
		$cartProductsIDsSelector = implode("|", $cartTopLevelProductsIDs);
		// $fields = ["pwcommerce_categories.id","pwcommerce_categories.title"];
		$fields = "pwcommerce_categories.id";
		$cartItemsCategories = $this->findRaw("template=product,id={$cartProductsIDsSelector}", $fields);

		$this->cartItemsCategories = $cartItemsCategories;

		if (!empty($isIncludeProductIDs)) {

			// IF WE WANT THE PRODUCT IDS ASSOCIATED WITH THESE CATEGORIES
			// RETURN THE NESTED ARRAY WITH productID => categoriesIDsArray
			return $cartItemsCategories;
		}

		## =======
		if (!empty($cartItemsCategories)) {
			foreach ($cartItemsCategories as $productID => $cartCategoriesItemValues) {
				if (empty($cartCategoriesItemValues)) {
					// SKIP PRODUCTS WITHOUT CATEGORIES
					continue;
				}
				$cartCategoriesIDs = array_merge($cartCategoriesIDs, array_values($cartCategoriesItemValues));
			}
			// remove duplicates
			$cartCategoriesIDs = array_unique($cartCategoriesIDs);
		}

		// ----
		return $cartCategoriesIDs;
	}

	/**
	 * Get Cart Categories I Ds With Associated Products I Ds.
	 *
	 * @param bool $isIncludeProductIDs
	 * @return array
	 */
	private function getCartCategoriesIDsWithAssociatedProductsIDs(bool $isIncludeProductIDs = false): array {

		// TODO DELETE IF NO LONGER IN USE! SPLITTING THIS UP FOR CATEGORIES ONLY!
		$cartTopLevelProductsIDs = $this->getOrderCartTopLevelProductsIDs();
		$cartCategoriesIDs = [];
		// GET CATEGORIES IDs FOR THE PRODUCTS IN THE CART
		// @note: since we have top level products IDs here, we don't need to consider variants; they have no categories
		$cartProductsIDsSelector = implode("|", $cartTopLevelProductsIDs);
		// $fields = ["pwcommerce_categories.id","pwcommerce_categories.title"];
		$fields = "pwcommerce_categories.id";
		$cartCategoriesIDsRaw = $this->findRaw("template=product,id={$cartProductsIDsSelector}", $fields);

		if (!empty($isIncludeProductIDs)) {

			// IF WE WANT THE PRODUCT IDS ASSOCIATED WITH THESE CATEGORIES
			// RETURN THE NESTED ARRAY WITH productID => categoriesIDsArray
			return $cartCategoriesIDsRaw;
		}

		## =======
		if (!empty($cartCategoriesIDsRaw)) {
			foreach ($cartCategoriesIDsRaw as $productID => $categoriesIDs) {
				$cartCategoriesIDs = array_merge($cartCategoriesIDs, array_values($categoriesIDs));
			}
			// remove duplicates
			$cartCategoriesIDs = array_unique($cartCategoriesIDs);
		}

		// ----
		return $cartCategoriesIDs;
	}

	/**
	 * Get Cart Items Products I Ds.
	 *
	 * @return array
	 */
	private function getCartItemsProductsIDs(): array {
		$cart = $this->getOrderCart();
		$cartProductsIDs = array_column($cart, 'product_id');

		// -------
		return $cartProductsIDs;
	}

	/**
	 * Get Saved Buy X Eligibility Categories I Ds.
	 *
	 * @return mixed
	 */
	private function getSavedBuyXEligibilityCategoriesIDs() {
		// =======
		/** @var WireArray $discountEligibility */
		$discountEligibility = $this->discountEligibility;
		$discountEligibilityCategoriesIDsItems = $discountEligibility->find("itemType=" . PwCommerce::DISCOUNT_BOGO_CATEGORIES_BUY_X);

		$discountEligibilityCategoriesIDs = $discountEligibilityCategoriesIDsItems->explode("itemID");

		// -------
		return $discountEligibilityCategoriesIDs;
	}

	/**
	 * Get Saved Buy X Eligibility Products I Ds.
	 *
	 * @return mixed
	 */
	private function getSavedBuyXEligibilityProductsIDs() {
		// TODO COULD REFACTOR THIS! NEAR IDENTICAL TO 	$this->getSavedBuyXEligibilityCategoriesIDs()
		// =======
		/** @var WireArray $discountEligibility */
		$discountEligibility = $this->discountEligibility;
		$discountEligibilityProductsIDsItems = $discountEligibility->find("itemType=" . PwCommerce::DISCOUNT_BOGO_PRODUCTS_BUY_X);

		$discountEligibilityProductsIDs = $discountEligibilityProductsIDsItems->explode("itemID");

		// +++++++++++++++++++++

		// ADD VARIANT IDs TO CATER FOR SITUATIONS WHERE ELIGIBILITY IS A 'CATCH ALL' PRODUCT PARENT ID!

		// HERE GRAB THE CHILDREN [variants] (if applicable)
		$idsSelector = implode("|", $discountEligibilityProductsIDs);
		$variantsSelector = "template=variant,parent.id={$idsSelector}";
		$fields = "id,parent_id";
		$productsVariantsItems = $this->findRaw($variantsSelector, $fields);
		$productsVariantsIDs = array_column($productsVariantsItems, 'id');

		$discountEligibilityProductsIDs = array_merge($discountEligibilityProductsIDs, $productsVariantsIDs);

		# ----------------------

		// -------
		return $discountEligibilityProductsIDs;
	}

	/**
	 * Get Buy X Eligible Categories Products I Ds.
	 *
	 * @return mixed
	 */
	private function getBuyXEligibleCategoriesProductsIDs() {

		// @note: this will first get the products IDs for eligible products
		// then get the corresponding cart item product or variant product IDs!

		// $this->buyXEligibleItemsIDs
		// $this->$this->cartItemsCategories

		$cart = $this->getOrderCart();

		$eligibleItemsProductIDs = [];
		foreach ($this->cartItemsCategories as $productID => $cartItemCategories) {

			if (empty($cartItemCategories)) {
				continue;
			}
			// --------

			if (!empty(array_intersect($cartItemCategories, $this->buyXEligibleItemsIDs))) {
				$eligibleItemsProductIDs[] = $productID;
			}
		}

		$eligibleItemsProductIDsForSum = [];

		if (!empty($eligibleItemsProductIDs)) {
			foreach ($cart as $cartItem) {
				if (!empty($cartItem->pwcommerce_is_variant)) {
					// we use the variant parent id to check since categories belong to it
					$checkProductID = $cartItem->pwcommerce_variant_parent_id;
				} else {
					$checkProductID = $cartItem->product_id;
				}
				// --------
				if (in_array($checkProductID, $eligibleItemsProductIDs)) {
					// check passed: we get the cart item product_id itself
					// this is so we include variants
					// we will use this for summming!
					$eligibleItemsProductIDsForSum[] = $cartItem->product_id;
				}

			}
		}

		// --------
		return $eligibleItemsProductIDsForSum;
	}

	/**
	 * Get Buy X Eligible Products I Ds.
	 *
	 * @return mixed
	 */
	private function getBuyXEligibleProductsIDs() {
		// @note: this will check both products and products variants parent_ids!
		// $this->buyXEligibleItemsIDs

		$cart = $this->getOrderCart();
		$eligibleItemsProductIDsForSum = [];
		foreach ($cart as $cartItem) {
			// @NOTE: WE CAN DO CHECK LIKE THIS SINCE IN $this->getSavedBuyXEligibilityProductsIDs(), we got eligible product variants IDs using their product parent ID
			// --------
			if (in_array($cartItem->product_id, $this->buyXEligibleItemsIDs)) {
				// check passed: we get the cart item product_id itself
				// this is so we include variants
				// we will use this for summming!
				$eligibleItemsProductIDsForSum[] = $cartItem->product_id;
			}

		}

		// --------
		return $eligibleItemsProductIDsForSum;
	}

	/**
	 * Get Buy X Eligible Items Sum.
	 *
	 * @param mixed $checkItemsForSum
	 * @return mixed
	 */
	private function getBuyXEligibleItemsSum($checkItemsForSum) {
		// @note: $checkItemsForSum are already filtered to be eligible product/variant IDs only!
		$minimumRequirementType = $this->discount->discountMinimumRequirementType;

		$cart = $this->getOrderCart();
		$buyXEligibleItemsSum = 0;

		// TODO: WE NEED TO ALWAYS TRACK QUANTITY SO THAT WE CAN SEE IF CART HAS RIGHT NUMBER OF 'BUY X' ITEMS IN CASES WHERE MIN REQ IS QUANTITY; WE ALSO NEED TO TRACK THE ID OF THE X-ITEMS IN ORDER TO SEE IF CAN SPLIT TO ANOTHER DISCOUNT APPLY. FOR INSTANCE, FOR A 2:1 SPLIT, WE ALWAYS NEED 1 GET Y. FOR EACH 2 BUY X. SECONDLY, WE NEED TO CATER FOR CASES WHEREBY A BUY X ITEM CAN ALSO BE A GET Y ITEM! E.G. BUY 3 PAIRS OF SOCKS, GET THE THIRD PAIR FREE.SO, APART FROM COUNTING, WE NEED TO BE ABLE TO KEEP TRACK OF WHICH ITEMS HAVE BEEN SPLIT ACROSS BUY X AND GET Y. HOW??? MAYBE CREATE GROUPS OF ITEMS THAT CAN ONLY BE IN BUY X, ITEMS THAT CAN ONLY BE IN GET Y AND ITEMS THAT CAN BE IN BOTH. THIS THIRD GROUP IS THE ISSUE! BACK TO THE SOCKS, THESE WOULD BE IN THE THIRD GROUP; WE WOULD NEED TO PROGRAMMATICALLY DETERMINE WHEN AND WHERE TO DO THE SPLIT (IN THIS CASE AT 2) BUT ALSO KEEP TRACK OF REMAINING ITEMS AFTER SPLIT AND PAIRING. SAY,WE HAD FOUR PAIRS OF SOCKS, WE WOULD NEED ENSURE THAT 2 WERE ADDED TO BUY X, 1 WAS ADDED TO GET Y AND 1 REMAINED THAT COULD NOT BE PAIRED. @SEE SEPARATE NOTES FOR STRATEGY

		foreach ($cart as $cartItem) {

			// SKIP NON-ELIGIBLE
			if (!in_array($cartItem->product_id, $checkItemsForSum)) {

				continue;
			}

			# +++++++++
			if ($minimumRequirementType === 'quantity') {
				// SUM THE QUANTITY!
				$buyXEligibleItemsSum += $cartItem->quantity;

			} else {
				// SUM THE SPEND (£)!
				$buyXEligibleItemsSum += $cartItem->pwcommerce_price_total;

			}

		}

		// --------
		return $buyXEligibleItemsSum;

	}

	/**
	 * Get Buy X Eligible Products And Variants I Ds.
	 *
	 * @return mixed
	 */
	private function getBuyXEligibleProductsAndVariantsIDs() {
		$discountSavedEligibilityProductsAndVariantsIDs = $this->getSavedDiscountEligibilityProductsORCategoriesIDs();
		// --------
		// GET IDs OF PRODUCTS IN THE CART
		// @note: for variants, in this case, we use their ID as is, since they are product IDs
		// however, for the RIGHT SIDE OF the array_intersect WE WILL NEED TO GET ADD THE IDs OF ALL VARIANTS OF A PRODUCT WITH VARIANTS
		// @note: we also need to account from product variants!
		// this is because in the discount edit GUI, we allow admins to only specify the PARENT PRODUCT for it to apply to its variants as well
		// if only variants are specified, it means the discount is only for those variants
		// hence here, for all specified product IDs, we get their children 'variants'
		// if found, it means the product is a parent product; if not found it means the product either has no children (has no variants) or is a variant itself (no children), meaning, only apply to the variant
		// --------

		############
		// @note: we also need to account for product variants!
		// this is because in the discount edit GUI, we allow admins to only specify the PARENT PRODUCT for it to apply to its variants as well
		// if only variants are specified, it means the discount is only for those variants
		// hence here, for all specified product IDs, we get their children 'variants'
		// if found, it means the product is a parent product; if not found it means the product either has no children (has no variants) or is a variant itself (no children), meaning, only apply to the variant
		// TODO HERE GRAB THE CHILDREN [variants] (if applicable)
		$idsSelector = implode("|", $discountSavedEligibilityProductsAndVariantsIDs);
		$variantsSelector = "template=variant,parent.id={$idsSelector}";
		$fields = "id,parent_id";
		$discountBuyXEligibleVariantsIDs = $this->findRaw($variantsSelector, $fields);
		// TODO PROCESS ABOVE SO WE REMOVE DUPLICATES;? MORE IMPORTANTLY NEED TO MERGE THE RAW ONES THAT WERE VARIANTS THEMSELVES OR WERE WITHOUT VARIANTS AS THEY WOULD NOT BE RETURNED IN THE FIND RAW! GET THEM FROM $discountAppliesToProductsIDsSaved!!!
		// ^^^^^^^^^

		// MERGE DISCOUNT SAVED PRODUCT IDS to FIND VARIANTS IDS
		$foundVariantsIDsForBuyXEligibleProducts = array_column($discountBuyXEligibleVariantsIDs, 'id');

		// $cartItemsIDs = array_merge($cartItemsIDs, $foundVariantsIDsForBuyXEligibleProducts);

		## +++++++++++++++++++++
		// $discountBuyXEligibleVariantsIDs = $this->findRaw($variantsSelector, $fields);
		// $foundVariantsIDsForDiscountAppliesToProducts = array_column($discountAppliesToProductsIDs, 'id');
		// $foundVariantsIDsForBuyXEligibleProducts = array_column($discountBuyXEligibleVariantsIDs, 'id');

		$discountSavedEligibilityProductsAndVariantsIDs = array_merge($discountSavedEligibilityProductsAndVariantsIDs, $foundVariantsIDsForBuyXEligibleProducts);

		// -------
		return $discountSavedEligibilityProductsAndVariantsIDs;
	}

	/**
	 * Get Saved Discount Eligibility Products O R Categories I Ds.
	 *
	 * @return mixed
	 */
	private function getSavedDiscountEligibilityProductsORCategoriesIDs() {
		// =======
		/** @var WireArray $discountEligibility */
		$discountEligibility = $this->discountEligibility;
		// REMOVE CUSTOMER ELIGIBILITY ITEMS; ONLY GET 'products_buy_x' OR 'categories_buy_x' ONLY
		// @note technically not necessary since we are dealing with page IDs and these are unique
		$discountEligibilityItemsSelectorArray = [
			PwCommerce::DISCOUNT_BOGO_PRODUCTS_BUY_X,
			PwCommerce::DISCOUNT_BOGO_CATEGORIES_BUY_X
		];
		$discountEligibilityItemsSelector = implode("|", $discountEligibilityItemsSelectorArray);
		$discountEligibilityProductsORCategoriesItemsSaved = $discountEligibility->find("itemType={$discountEligibilityItemsSelector}");
		$discountEligibilityProductsORCategoriesIDsSaved = $discountEligibilityProductsORCategoriesItemsSaved->explode('itemID');
		// -----
		return $discountEligibilityProductsORCategoriesIDsSaved;
	}

	# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// GET Y

	/**
	 * Is B O G O Customer Gets Y From And Quantity Of Items Requirement Met.
	 *
	 * @return bool
	 */
	private function isBOGOCustomerGetsYFromAndQuantityOfItemsRequirementMet() {
		$isValid = false; // @note: default to false so that we don't return true in case part 2 of the checks does not run
		# ++++++++++
		// =======
		/** @var WireData $discount */
		$discount = $this->discount;

		// =======
		/** @var WireArray $discountAppliesTo */
		$discountAppliesTo = $this->discountAppliesTo;

		$isCategoriesGetY = $this->isBogGetYCategoriesDiscount();
		if ($isCategoriesGetY) {
			// CATEGORY GET Y BOGO
			$checkItems = $this->getCartItemsCategoriesIDs();
			$checkSavedItems = $this->getSavedGetYAppliesToCategoriesIDs();

		} else {
			// PRODUCT GET Y BOGO

			//
			$checkItems = $this->getCartItemsProductsIDs();

			// @NOTE: includes 'unsaved' VARIANT IDs in cases where the applies to itemID is a parent product!
			$checkSavedItems = $this->getSavedGetYAppliesToProductsIDs();

		}

		$eligibleItems = array_intersect($checkItems, $checkSavedItems);

		################
		# PART 1 CHECK: GET Y ELIGIBILITY ITEM(S) IN CART
		$isGetYAppliesToItemsInCart = !empty($eligibleItems);

		if (empty($isGetYAppliesToItemsInCart)) {
			// INVALID: RETURN EARLY
			// no get y items in cart
			$errorType = 'is_bogo_requirements_met';

			$this->discountValidityError = $this->getInvalidDiscountErrors($errorType);
			;
			// ----
			return $isValid;
		}

		################
		# PART 2 CHECK: MIN REQUIREMENT COMPUTE (quantity)
		$this->getYAppliesToItemsIDs = $eligibleItems;

		if ($isCategoriesGetY) {
			// CATEGORY GET Y BOGO: GET PRODUCT IDs from APPLIES TO CATEGORIES for SUMMING
			// @note: this will first get the products IDs for eligible products
			// then get the corresponding cart item product or variant product IDs!
			$checkItemsForSum = $this->getGetYAppliesToCategoriesProductsIDs();
		} else {
			// PRODUCT GET Y BOGO: ALREADY HAVEPRODUCT IDs from APPLIES TO CATEGORIES for SUMMING
			// @note: this will check both products and products variants parent_ids!
			$checkItemsForSum = $this->getGetYAppliesToProductsIDs();
		}

		$checkItemsSum = $this->getGetYAppliesToItemsSum($checkItemsForSum);

		# PART 3 CHECK IF minimim amount reached
		// @NOTE: FOR GET Y, WE CHECK 'getYDiscountedItemsAmount', i.e. the quantity of applies to items that needs to be added to the basket!
		$isValid = $checkItemsSum >= $discount->getYDiscountedItemsAmount;

		if (empty($isValid)) {
			// PREPARE ERROR: MIN REQ QTY FOR GET Y NOT MET!
			$errorType = 'is_bogo_requirements_get_y_quantity_met';
			$this->discountValidityError = $this->getInvalidDiscountErrors($errorType);
		}

		// +++++++++
		# ********

		// -----
		return $isValid;
	}

	/**
	 * Get Saved Get Y Applies To Categories I Ds.
	 *
	 * @return mixed
	 */
	private function getSavedGetYAppliesToCategoriesIDs() {
		// =======
		/** @var WireArray $discountAppliesTo */
		$discountAppliesTo = $this->discountAppliesTo;
		$discountAppliesToCategoriesIDsItems = $discountAppliesTo->find("itemType=" . PwCommerce::DISCOUNT_BOGO_CATEGORIES_GET_Y);

		$discountAppliesToCategoriesIDs = $discountAppliesToCategoriesIDsItems->explode("itemID");

		// -------
		return $discountAppliesToCategoriesIDs;
	}

	/**
	 * Get Get Y Applies To Items Sum.
	 *
	 * @param mixed $checkItemsForSum
	 * @return mixed
	 */
	private function getGetYAppliesToItemsSum($checkItemsForSum) {
		// @note: $checkItemsForSum are already filtered to be eligible product/variant IDs only!

		$cart = $this->getOrderCart();
		$getYAppliesToItemsSum = 0;
		// TODO: WE NEED TO ALWAYS TRACK QUANTITY SO THAT WE CAN SEE IF CART HAS RIGHT NUMBER OF 'GET Y' ITEMS; WE ALSO NEED TO TRACK THE ID OF THE Y-ITEMS IN ORDER TO SEE IF CAN SPLIT TO ANOTHER DISCOUNT APPLY. FOR INSTANCE, FOR A 2:1 SPLIT, WE ALWAYS NEED 1 GET Y. FOR EACH 2 BUY X. SECONDLY, WE NEED TO CATER FOR CASES WHEREBY A BUY X ITEM CAN ALSO BE A GET Y ITEM! E.G. BUY 3 PAIRS OF SOCKS, GET THE THIRD PAIR FREE.SO, APART FROM COUNTING, WE NEED TO BE ABLE TO KEEP TRACK OF WHICH ITEMS HAVE BEEN SPLIT ACROSS BUY X AND GET Y. HOW??? MAYBE CREATE GROUPS OF ITEMS THAT CAN ONLY BE IN BUY X, ITEMS THAT CAN ONLY BE IN GET Y AND ITEMS THAT CAN BE IN BOTH. THIS THIRD GROUP IS THE ISSUE! BACK TO THE SOCKS, THESE WOULD BE IN THE THIRD GROUP; WE WOULD NEED TO PROGRAMMATICALLY DETERMINE WHEN AND WHERE TO DO THE SPLIT (IN THIS CASE AT 2) BUT ALSO KEEP TRACK OF REMAINING ITEMS AFTER SPLIT AND PAIRING. SAY,WE HAD FOUR PAIRS OF SOCKS, WE WOULD NEED ENSURE THAT 2 WERE ADDED TO BUY X, 1 WAS ADDED TO GET Y AND 1 REMAINED THAT COULD NOT BE PAIRED. @SEE SEPARATE NOTES FOR STRATEGY

		foreach ($cart as $cartItem) {

			// SKIP NON-ELIGIBLE
			if (!in_array($cartItem->product_id, $checkItemsForSum)) {

				continue;
			}

			# +++++++++

			// SUM THE QUANTITY!
			$getYAppliesToItemsSum += $cartItem->quantity;

		}

		// -----
		return $getYAppliesToItemsSum;

	}

	/**
	 * Get Saved Get Y Applies To Products I Ds.
	 *
	 * @return mixed
	 */
	private function getSavedGetYAppliesToProductsIDs() {
		// TODO COULD REFACTOR THIS! NEAR IDENTICAL TO 	$this->getSavedGetYAppliesToCategoriesIDs()
		// =======
		/** @var WireArray $discountAppliesTo */
		$discountAppliesTo = $this->discountAppliesTo;
		$discountAppliesToProductsIDsItems = $discountAppliesTo->find("itemType=" . PwCommerce::DISCOUNT_BOGO_PRODUCTS_GET_Y);

		$discountAppliesToProductsIDs = $discountAppliesToProductsIDsItems->explode("itemID");

		// +++++++++++++++++++++

		// ADD VARIANT IDs TO CATER FOR SITUATIONS WHERE ELIGIBILITY IS A 'CATCH ALL' PRODUCT PARENT ID!

		// HERE GRAB THE CHILDREN [variants] (if applicable)
		$idsSelector = implode("|", $discountAppliesToProductsIDs);
		$variantsSelector = "template=variant,parent.id={$idsSelector}";
		$fields = "id,parent_id";
		$productsVariantsItems = $this->findRaw($variantsSelector, $fields);
		$productsVariantsIDs = array_column($productsVariantsItems, 'id');

		$discountAppliesToProductsIDs = array_merge($discountAppliesToProductsIDs, $productsVariantsIDs);

		# ----------------------

		// -------
		return $discountAppliesToProductsIDs;
	}

	/**
	 * Get Get Y Applies To Products I Ds.
	 *
	 * @return mixed
	 */
	private function getGetYAppliesToProductsIDs() {
		// @note: this will check both products and products variants parent_ids!
		// $this->getYAppliesToItemsIDs

		$cart = $this->getOrderCart();
		$appliesToItemsProductIDsForSum = [];
		foreach ($cart as $cartItem) {
			// @NOTE: WE CAN DO CHECK LIKE THIS SINCE IN $this->getSavedBuyXEligibilityProductsIDs(), we got eligible product variants IDs using their product parent ID
			// --------
			if (in_array($cartItem->product_id, $this->getYAppliesToItemsIDs)) {
				// check passed: we get the cart item product_id itself
				// this is so we include variants
				// we will use this for summming!
				$appliesToItemsProductIDsForSum[] = $cartItem->product_id;
			}

		}

		// --------
		return $appliesToItemsProductIDsForSum;
	}

	/**
	 * Get Get Y Applies To Categories Products I Ds.
	 *
	 * @return mixed
	 */
	private function getGetYAppliesToCategoriesProductsIDs() {

		// @note: this will first get the products IDs for eligible products
		// then get the corresponding cart item product or variant product IDs!

		// $this->buyXEligibleItemsIDs
		// $this->$this->cartItemsCategories

		$cart = $this->getOrderCart();

		$eligibleItemsProductIDs = [];
		foreach ($this->cartItemsCategories as $productID => $cartItemCategories) {

			if (empty($cartItemCategories)) {
				continue;
			}
			// --------

			if (!empty(array_intersect($cartItemCategories, $this->getYAppliesToItemsIDs))) {
				$eligibleItemsProductIDs[] = $productID;
			}
		}

		$eligibleItemsProductIDsForSum = [];

		if (!empty($eligibleItemsProductIDs)) {
			foreach ($cart as $cartItem) {
				if (!empty($cartItem->pwcommerce_is_variant)) {
					// we use the variant parent id to check since categories belong to it
					$checkProductID = $cartItem->pwcommerce_variant_parent_id;
				} else {
					$checkProductID = $cartItem->product_id;
				}
				// --------
				if (in_array($checkProductID, $eligibleItemsProductIDs)) {
					// check passed: we get the cart item product_id itself
					// this is so we include variants
					// we will use this for summming!
					$eligibleItemsProductIDsForSum[] = $cartItem->product_id;
				}

			}
		}

		// --------
		return $eligibleItemsProductIDsForSum;
	}

	/**
	 * Get Get Y Applies To Products And Variants I Ds.
	 *
	 * @return mixed
	 */
	private function getGetYAppliesToProductsAndVariantsIDs() {
		$discountSavedAppliesToProductsAndVariantsIDs = $this->getSavedDiscountAppliesToProductsORCategoriesIDs();
		// --------
		// GET IDs OF PRODUCTS IN THE CART
		// @note: for variants, in this case, we use their ID as is, since they are product IDs
		// however, for the RIGHT SIDE OF the array_intersect WE WILL NEED TO GET ADD THE IDs OF ALL VARIANTS OF A PRODUCT WITH VARIANTS
		// @note: we also need to account from product variants!
		// this is because in the discount edit GUI, we allow admins to only specify the PARENT PRODUCT for it to apply to its variants as well
		// if only variants are specified, it means the discount is only for those variants
		// hence here, for all specified product IDs, we get their children 'variants'
		// if found, it means the product is a parent product; if not found it means the product either has no children (has no variants) or is a variant itself (no children), meaning, only apply to the variant
		// --------

		############
		// @note: we also need to account for product variants!
		// this is because in the discount edit GUI, we allow admins to only specify the PARENT PRODUCT for it to apply to its variants as well
		// if only variants are specified, it means the discount is only for those variants
		// hence here, for all specified product IDs, we get their children 'variants'
		// if found, it means the product is a parent product; if not found it means the product either has no children (has no variants) or is a variant itself (no children), meaning, only apply to the variant
		// TODO HERE GRAB THE CHILDREN [variants] (if applicable)
		$idsSelector = implode("|", $discountSavedAppliesToProductsAndVariantsIDs);
		$variantsSelector = "template=variant,parent.id={$idsSelector}";
		$fields = "id,parent_id";
		$discountGetYAppliesToVariantsIDs = $this->findRaw($variantsSelector, $fields);
		// TODO PROCESS ABOVE SO WE REMOVE DUPLICATES;? MORE IMPORTANTLY NEED TO MERGE THE RAW ONES THAT WERE VARIANTS THEMSELVES OR WERE WITHOUT VARIANTS AS THEY WOULD NOT BE RETURNED IN THE FIND RAW! GET THEM FROM $discountAppliesToProductsIDsSaved!!!
		// ^^^^^^^^^

		// MERGE DISCOUNT SAVED PRODUCT IDS to FIND VARIANTS IDS
		$foundVariantsIDsForGetYAppliesToProducts = array_column($discountGetYAppliesToVariantsIDs, 'id');

		// $cartItemsIDs = array_merge($cartItemsIDs, $foundVariantsIDsForGetYAppliesToProducts);

		## +++++++++++++++++++++
		// $discountGetYAppliesToVariantsIDs = $this->findRaw($variantsSelector, $fields);
		// $foundVariantsIDsForDiscountAppliesToProducts = array_column($discountAppliesToProductsIDs, 'id');
		// $foundVariantsIDsForGetYAppliesToProducts = array_column($discountGetYAppliesToVariantsIDs, 'id');

		$discountSavedAppliesToProductsAndVariantsIDs = array_merge($discountSavedAppliesToProductsAndVariantsIDs, $foundVariantsIDsForGetYAppliesToProducts);

		// -------
		return $discountSavedAppliesToProductsAndVariantsIDs;
	}

	/**
	 * Get Saved Discount Applies To Products O R Categories I Ds.
	 *
	 * @return mixed
	 */
	private function getSavedDiscountAppliesToProductsORCategoriesIDs() {
		// =======
		/** @var WireArray $discountAppliesTo */
		$discountAppliesTo = $this->discountAppliesTo;
		// REMOVE CUSTOMER ELIGIBILITY ITEMS; ONLY GET 'products_get_y' OR 'categories_get_y' ONLY
		// @note technically not necessary since we are dealing with page IDs and these are unique
		$discountAppliesToItemsSelectorArray = [
			PwCommerce::DISCOUNT_BOGO_PRODUCTS_GET_Y,
			PwCommerce::DISCOUNT_BOGO_CATEGORIES_GET_Y
		];
		$discountAppliesToItemsSelector = implode("|", $discountAppliesToItemsSelectorArray);
		$discountAppliesToProductsORCategoriesItemsSaved = $discountAppliesTo->find("itemType={$discountAppliesToItemsSelector}");
		$discountAppliesToProductsORCategoriesIDsSaved = $discountAppliesToProductsORCategoriesItemsSaved->explode('itemID');
		// -----
		return $discountAppliesToProductsORCategoriesIDsSaved;
	}

	/**
	 * Process B O G O Apply Discount.
	 *
	 * @return mixed
	 */
	private function processBOGOApplyDiscount() {

		// TODO WIP! TEST FOR BOGO GROUPING FOR APPPLY DISCOUNT!

		// $buyXEligibleItems = new WireArray();
		// $getYAppliesToItems = new WireArray();
		// $shareBuyXAndGetYItems = new WireArray();

		$buyXEligibleItems = [
			["product_id" => 5489, "quantity" => 3, "item_type" => "buy_x_item"],
			// ["product_id" => 7745, "quantity" => 1, "item_type" => "buy_x_item"],
			["product_id" => 7745, "quantity" => 2, "item_type" => "buy_x_item"],
			["product_id" => 3358, "quantity" => 2, "item_type" => "buy_x_item"],
			// ["product_id" => 4455, "quantity" => 1, "item_type" => "buy_x_item"],
			["product_id" => 4455, "quantity" => 3, "item_type" => "buy_x_item"],
			["product_id" => 5002, "quantity" => 1, "item_type" => "buy_x_item"],
			["product_id" => 9874, "quantity" => 2, "item_type" => "buy_x_item"],
		];
		$getYAppliesToItems = [
			["product_id" => 5489, "quantity" => 3, "item_type" => "get_y_item"],
			["product_id" => 1058, "quantity" => 2, "item_type" => "get_y_item"],
			["product_id" => 3358, "quantity" => 2, "item_type" => "get_y_item"],
			["product_id" => 8885, "quantity" => 1, "item_type" => "get_y_item"],
		];
		$sharedBuyXAndGetYItems = [];
		$getYAppliesToItemsIDsForUnshared = [];
		$getYAppliesToItemsUnshared = [];

		// build shared items
		$buyXEligibleItemsProductIDs = array_column($buyXEligibleItems, "product_id");
		$getYAppliesToItemsProductIDs = array_column($getYAppliesToItems, "product_id");
		foreach ($buyXEligibleItemsProductIDs as $buyXEligibleItemProductID) {
			if (in_array($buyXEligibleItemProductID, $getYAppliesToItemsProductIDs)) {
				$sharedGetYAppliesToItem = array_filter($getYAppliesToItems, fn($item) => $item['product_id'] === $buyXEligibleItemProductID);
				$sharedBuyXAndGetYItems = array_merge($sharedBuyXAndGetYItems, $sharedGetYAppliesToItem);
				$getYAppliesToItemsIDsForUnshared[] = $buyXEligibleItemProductID;
			}
		}

		// REMOVE THE SHARED 'GET Y' ITEMS TEMPORARILY
		// we first let them only operate in '$buyXEligibleItems'
		// we will only pick them if the are leftovers in '$buyXEligibleItems' AND GET Y SIDE NEEDS ITEMS!
		$getYAppliesToItemsUnshared = array_filter($getYAppliesToItems, fn($item) => !in_array($item['product_id'], $getYAppliesToItemsIDsForUnshared));

		// @note: if this is TRUE, we will use this for fulfilling GET Y items
		// the other SHARED GET Y items have 'moved' to BUY X
		// if any remain AND BUY X (last item)  fulfilled
		// we will get all or some of these back to fulfill GET Y
		// @note: we will have to grab 'quantity' to know how many left!
		$isUseUnsharedGetY = !empty($getYAppliesToItemsUnshared);

		// TWO SCENARIOS
		// A. spend £xxx amount on BUY X ITEMS TO GET YYY ITEM(S) FREE/%
		// B. BUY XXX item(s)  TO GET YYY ITEM(S) FREE/%

		// +++++++++++
		// above means we only check both BUY X AN GET Y if in scenario B

		// ++++++++++++
		// SCENARIO A
		// if in scenario 'A', we only need to match GET Ys. this means dividing like so:
		// $discount->getYDiscountedItemsAmount;
		$totalNumberOfGetYItems = array_sum(array_column($getYAppliesToItems, 'quantity'));
		$getYDiscountedItemsAmount = 1;
		if (!empty($isUseUnsharedGetY)) {
			$totalNumberOfGetYUnsharedItems = array_sum(array_column($getYAppliesToItemsUnshared, 'quantity'));

			// TODO  NEW MATHS CLASS!
			// $numberOfRequiredGetYForGrouping = floor($totalNumberOfGetYUnsharedItems / $getYDiscountedItemsAmount);
			// $numberOfRequiredGetYForGrouping = $this->mathDivide($totalNumberOfGetYUnsharedItems, $getYDiscountedItemsAmount);


		} else {
			// TODO  NEW MATHS CLASS!
			// $numberOfRequiredGetYForGrouping = floor($totalNumberOfGetYItems / $getYDiscountedItemsAmount);
			// $numberOfRequiredGetYForGrouping = $this->mathDivide($totalNumberOfGetYItems, $getYDiscountedItemsAmount);
		}

		######## EXPANSIONS ##########
		# @NOTE: THESE EXPANSIONS HELP US KEEP TRACK OF 'USED' AND 'LEFTOVOERS'!

		# +++++++++++++++
		// 'EXPAND' THE BUY X BY THEIR QUANTITITES
		$buyXEligibleItemsExpanded = [];
		foreach ($buyXEligibleItems as $buyXEligibleItem) {
			// $num = $buyXEligibleItem['quantity'] - 1;
			$num = $buyXEligibleItem['quantity'];
			// if(empty($num)){
			//   continue;
			// }
			$arr = array_fill(0, $num, $buyXEligibleItem);
			$buyXEligibleItemsExpanded = array_merge($buyXEligibleItemsExpanded, $arr);
		}

		// 'EXPAND' THE UNSARED GET Y BY THEIR QUANTITITES
		$getYAppliesToItemsExpanded = [];
		if (!empty($isUseUnsharedGetY)) {
			$getYItemsForExpand = $getYAppliesToItemsUnshared;
		} else {
			$getYItemsForExpand = $getYAppliesToItems;
		}
		foreach ($getYItemsForExpand as $getYAppliesToItem) {
			$num = $getYAppliesToItem['quantity'];
			$arr = array_fill(0, $num, $getYAppliesToItem);
			$getYAppliesToItemsExpanded = array_merge($getYAppliesToItemsExpanded, $arr);
		}

		// ++++++++++++
		// SCENARIO B
		// $discount->discountMinimumRequirementAmount
		$totalNumberOfBuyXItems = array_sum(array_column($buyXEligibleItems, 'quantity'));
		$discountMinimumRequirementAmount = 2;

		// TODO CONVERT TO MONEY!!
		// TODO  NEW MATHS CLASS!
		// $numberOfRequiredBuyXForGrouping = floor($totalNumberOfBuyXItems / $discountMinimumRequirementAmount);
		$numberOfRequiredBuyXForGrouping = $this->mathDivide($totalNumberOfBuyXItems, $discountMinimumRequirementAmount);
		// EXAMPLE 1: BUY 2 GET 1 DISCOUNTED

		#######

		######## GROUP BUY_X/GET_Y - SLICE ##########
		// @note: we need to slice the buy_x and get_y arrays IN ORDER TO DO THE GROUPINGS
		// WE NEED TO SLICE THE 'EXPANDED' ARRAYS!, I.E. $buyXEligibleItemsExpanded AND $getYAppliesToItemsExpanded
		// in this example, for each 2 BUY X, we match them to 1 GET Y
		// WE WILL USE ARRAY SLICE WITH INCREMENTING OFFSET
		// THE LENGTH STAYS CONSTANT AND IS THE VALUE OF $discountMinimumRequirementAmount
		// IN THIS EXAMPLE, IT IS '2'
		// the offset starts at 0, we then increment that by $offset += $discountMinimumRequirementAmount (or?)
		// ABOUT WHERE TO BREAK?? WE COULD EITHER COUNT REMAINDERS AFTER EACH SLICE OR BREAK WITH WE REACH $numberOfRequiredBuyXForGrouping??

		$bogoGroups = [];

		$bogoGroupNumber = 1;
		$buyXOffset = 0;
		$buyXLength = $discountMinimumRequirementAmount;
		$getYOffset = 0;
		$getYLength = $getYDiscountedItemsAmount;
		foreach ($buyXEligibleItemsExpanded as $buyXEligibleItem) {
			// PROCESS BUY X

			$buyXGroup = array_slice($buyXEligibleItemsExpanded, $buyXOffset, $buyXLength);
			$bogoGroupIndex = "bogo_group_{$bogoGroupNumber}";

			$bogoGroups[$bogoGroupIndex]['buy_x'] = $buyXGroup;
			$buyXOffset += $discountMinimumRequirementAmount;
			# +++++++++++++++++++++
			// PROCESS GET Y

			$getYGroup = array_slice($getYAppliesToItemsExpanded, $getYOffset, $getYLength);

			$bogoGroups[$bogoGroupIndex]['get_y'] = $getYGroup;
			$getYOffset += $getYDiscountedItemsAmount;
			// --------

			$bogoGroupNumber++;
		}

	}

	/**
	 * Return the discount code of a given discount ID.
	 *
	 * @param int $discountID
	 * @return mixed
	 */
	public function getDiscountCodeByDiscountID(int $discountID) {
		$discountID = (int) $discountID;
		$selector = "id={$discountID},status<" . Page::statusUnpublished;
		$discountPage = $this->wire('pages')->get($selector);
		$discount = $discountPage->get(PwCommerce::DISCOUNT_FIELD_NAME);
		$code = !empty($discount) ? $discount->code : NULL;

		// -----
		return $code;
	}

	/**
	 * Return the discount page for a given discount code.
	 *
	 * @param mixed $code
	 * @return mixed
	 */
	public function getDiscountPageByCode($code) {
		$code = $this->wire('sanitizer')->text($code);
		$selector = PwCommerce::DISCOUNT_FIELD_NAME . ".code={$code},status<" . Page::statusUnpublished;
		$discountPage = $this->wire('pages')->get($selector);

		// -----
		return $discountPage;
	}

	////////////
	// ~~~~~~~~~~~~~~~~
	// DISCOUNTS REFRESH/RECALCULATION

	// TODO DELETE IF NO LONGER IN USE
	/**
	 * Recalculate Discounts In Session.
	 *
	 * @param mixed $mode
	 * @return mixed
	 */
	public function recalculateDiscountsInSession($mode) {
		/** @var array $cart */
		$cart = $this->getOrderCart();

		$name = "orderAppliedDiscountsUpdatedTime_" . microtime();
		$this->wire('session')->set($name, "{$mode}: " . date("Y-m-d"));
	}

	////////////
	// ~~~~~~~~~~~~~~~~
	// DISCOUNT APPLICATION

	/**
	 * Get redeemed discount IDs in session and apply them.
	 *
	 * @param string $customerEmail
	 * @param string $customerShippingCountryID
	 * @return mixed
	 */
	public function validateAndApplyDiscounts(string $customerEmail, string $customerShippingCountryID) {

		$redeemedDiscountsIDs = $this->getSessionRedeemedDiscountsIDs();
		if (empty($redeemedDiscountsIDs)) {
			// NOTHING TO DO!
			// but cleanup first!
			// REMOVE PREVIOUS DISCOUNTS TRACKED IN SESSION!
			$this->removeRedeemedDiscountsFromSession();
			// -----
			return;
		}

		$productsIDsToApplyDiscountTo = [];
		$cart = $this->getOrderCart();

		foreach ($redeemedDiscountsIDs as $discountID) {
			/** @var WireData $result */
			$result = $this->validateAndSetDiscountByID($discountID, $customerEmail, $customerShippingCountryID);

			if (!empty($result->isValid)) {
				// SET DISCOUNT VALUES TO SESSION

				// +++++++++++++

				/** @var WireData $discount */
				$discount = $this->discount;

				// =======
				/** @var WireArray $discountAppliesTo */
				$discountAppliesTo = $this->discountAppliesTo;

				// -------
				// @note: this might include parent IDs if item is a variant
				/** @var array $discountItemsIDs */
				$discountItemsIDs = $this->cartItemsProductsIDsToApplyDiscountTo;

				// ------
				// *****************

				$discountType = $discount->discountType;

				// ++++++++++++++++++++

				if ($this->isWholeOrderDiscount($discountType)) {
					// WHOLE ORDER DISCOUNT
					// applies to ID is zero
					$discountAppliesToID = 0;

					// $cartProductsPriceTotal = array_sum(array_column($cart, 'pwcommerce_price_total'));
					$totalCartAmountMoney = NULL;

					if ($discountType === 'whole_order_fixed') {
						$isFirstCartItem = true;
						foreach ($cart as $cartItem) {
							$unitPrice = $cartItem->pwcommerce_price;
							$quantity = $cartItem->quantity;

							// create money object for the unit for the cart
							$unitAmountMoney = $this->money($unitPrice);
							// create money object for total amount for cart
							$totalAmountMoney = $unitAmountMoney->multiply((int) $quantity);

							if (!empty($isFirstCartItem)) {
								// create money object for total amount for the whole cart
								$totalCartAmountMoney = $totalAmountMoney;
							} else {
								// update the money object for the whole cart
								$totalCartAmountMoney = $totalCartAmountMoney->add($totalAmountMoney);
							}

							// SET MONEY PROP FOR USE BELOW!
							$cartItem->unitAmountMoney = $unitAmountMoney;
							$cartItem->totalAmountMoney = $totalAmountMoney;

							// -------
							$isFirstCartItem = false;
						}
					}

					$discountValueAmountMoney = $this->money($discount->discountValue);
					$discountValueAmount = (int) $discountValueAmountMoney->getAmount();

					// TODO REFACTOR THIS! MOVE TO OWM METHOD?
					foreach ($cart as $item) {

						// $productID = $item->product_id;

						if ($discountType === 'whole_order_fixed') {
							// WHOLE ORDER FIXED
							// -----
							// need to split the discount  proportionately across all items in the cart
							// e.g. 10 * (12.5/107.50) = 10 * 0.1162790697674419 = 1.162790697674419 = £1.16
							// where 12.5 is the TOTAL PRICE OF THE LINE ITEM (i.e. quantity * price), 107.5 is the total price of the cart (i.e. $cartProductsPriceTotak) and 10 is the whole order discount value (i.e. £10)
							// delegate proportional whole order discount value to line item


							$totalAmountMoney = $item->totalAmountMoney;
							$totalAmount = (int) $totalAmountMoney->getAmount();
							$totalCartAmount = (int) $totalCartAmountMoney->getAmount();
							// 10 * (12.5/107.50)

							$ratio = $discountValueAmount * ($totalAmount / $totalCartAmount);
							$divisor = ($totalAmount / $totalCartAmount);
							$ratioMoney = $discountValueAmountMoney->multiply(strval($divisor));

							$discountValue = (float) $this->getWholeMoneyAmount($ratioMoney);

						} else {
							// WHOLE ORDER PERCENTAGE
							// @note: just assigning percentage to each order line item!
							// i.e. 'delegating' to line items as the math will still add up
							$discountValue = $discount->discountValue;

						}

						$discountItem = [
							'cart_id' => $item->id,
							'product_id' => $item->product_id, // TODO?
							'productTitle' => $item->pwcommerce_title,
							// ----------
							'discountID' => $discountID,
							'code' => $discount->discountCode,
							'discountType' => $discountType,
							'discountAppliesTo' => $discountAppliesToID, // i.e. product/country/etc ID of item applied to,  i.e., 'whole order', etc
							'discountValue' => $discountValue, // i.e. 10% or £10
							'discountAmount' => 0, // @note: default amount: real amount to be calculated in PWCommerceUtilities

						];

						$productsIDsToApplyDiscountTo[] = $discountItem;

					}
				} else if ($this->isFreeShippingDiscount($discountType)) {
					// FREE SHIPPING DISCOUNT
					if ($discountAppliesTo->get("itemType=" . PwCommerce::DISCOUNT_APPLIES_TO_SELECTED_COUNTRIES)) {
						$discountAppliesToID = $this->customerShippingCountry->id;
					} else {
						// applies to ID is zero => 'shipping_all_countries'
						$discountAppliesToID = 0;
					}
					// no need to loop

					$discountItem = [
						'cart_id' => 0,
						'product_id' => 0, // TODO?
						'productTitle' => NULL,
						// ----------
						'discountID' => $discountID,
						'code' => $discount->discountCode,
						'discountType' => $discountType,
						'discountAppliesTo' => $discountAppliesToID, // i.e. product/country/etc ID of item applied to,  i.e., 'whole order', etc
						'discountValue' => $discount->discountValue, // i.e. 100% FREE SHIPPING
						'discountAmount' => 100, // @note: default amount: real amount to be calculated in PWCommerceUtilities
					];

					$productsIDsToApplyDiscountTo[] = $discountItem;
				} else if ($this->isBogoDiscount($discountType)) {
					// BOGO DISCOUNT
					// TODO!
				} else {
					// PRODUCTS/CATEGORIES DISCOUNT

					$appliesToCartItems = array_filter($cart, fn($item) => (in_array($item->product_id, $discountItemsIDs)) || (in_array($item->pwcommerce_variant_parent_id, $discountItemsIDs)));

					$appliesToCartProductsPriceTotal = array_sum(array_column($appliesToCartItems, 'pwcommerce_price_total'));

					$allocationRatios = [];
					$discountValueToAllocate = $discount->discountValue;
					$discountValueToAllocateMoney = $this->money($discountValueToAllocate);

					foreach ($appliesToCartItems as $item) {


						$discountAppliesToID = $item->product_id;

						if (in_array($discountType, ['categories_fixed_per_order', 'products_fixed_per_order'])) {

							// CATEGORIES OR PRODUCTS FIXED PER ORDER
							// -----
							// need to split the discount  proportionally across all items in the cart
							// e.g. 10 * (12.5/107.50) = 10 * 0.1162790697674419 = 1.162790697674419 = £1.16
							// where 12.5 is the TOTAL PRICE OF THE LINE ITEM (i.e. quantity * price), 107.5 is the total price of the cart (i.e. $cartProductsPriceTotak) and 10 is the whole order discount value (i.e. £10)
							// delegate proportional applies to line items discount value to line item
							// TODO CONVERT TO MONEY!!
							// TODO  NEW MATHS CLASS!
							// $discountValue = $discount->discountValue * ($item->pwcommerce_price_total / $appliesToCartProductsPriceTotal);
							$discountValue = $this->mathMultiply(
								$discount->discountValue,
								$this->mathDivide($item->pwcommerce_price_total, $appliesToCartProductsPriceTotal)
							);

							// $allocationRatios[$item->product_id] = $this->mathDivide($item->pwcommerce_price_total, $appliesToCartProductsPriceTotal);
							$allocationRatios[] = $item->pwcommerce_price_total / $appliesToCartProductsPriceTotal;


						} else {
							// CATEGORIES OR PRODUCTS FIXED PER ITEM OR PERCENTAGE
							$discountValue = $discount->discountValue;

						}

						$discountItem = [
							'cart_id' => $item->id,
							'product_id' => $item->product_id, // TODO?
							'productTitle' => $item->pwcommerce_title,
							// ----------
							'discountID' => $discountID,
							'code' => $discount->discountCode,
							'discountType' => $discountType,
							'discountAppliesTo' => $discountAppliesToID, // i.e. product/country/etc ID of item applied to,  i.e., 'whole order', etc
							'discountValue' => $discountValue, // i.e. 10%
							'discountAmount' => 0, // @note: default amount: real amount to be calculated in PWCommerceUtilities

						];

						$productsIDsToApplyDiscountTo[] = $discountItem;

					}
					// END LOOP

					// NOTE FIXED ONLY!!
					if (!empty($allocationRatios)) {
						$allocationMoneyList = $discountValueToAllocateMoney->allocate($allocationRatios);

						foreach ($productsIDsToApplyDiscountTo as $key => $values) {
							$discountValueMoney = $allocationMoneyList[$key];
							$discountValue = $this->getWholeMoneyAmount($discountValueMoney);
							$values['discountValue'] = $discountValue;
							$productsIDsToApplyDiscountTo[$key] = $values;
						}
					}


				}

			}
		}

		if (!empty($productsIDsToApplyDiscountTo)) {
			// SAVE TO SESSION!
			$this->session->set(PwCommerce::DISCOUNT_REDEEMED_DISCOUNTS, $productsIDsToApplyDiscountTo);
		} else {
			# EMPTY!
			// REMOVE PREVIOUS DISCOUNTS TRACKED IN SESSION!
			$this->removeRedeemedDiscountsFromSession();
		}

	}

	/**
	 * Remove Redeemed Discounts From Session.
	 *
	 * @return mixed
	 */
	private function removeRedeemedDiscountsFromSession() {
		$this->session->remove(PwCommerce::DISCOUNT_REDEEMED_DISCOUNTS);
	}

	// Public method to remove redeemed discounts
	/**
	 * Remove Discounts.
	 *
	 * @return mixed
	 */
	public function removeDiscounts() {
		return $this->removeRedeemedDiscountsFromSession();
	}

	////////////
	// ~~~~~~~~~~~~~~~~
	// DISCOUNT UTILITIES

	// WHOLE ORDER DISCOUNTS

	/**
	 * Is Whole Order Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isWholeOrderDiscount($discountType) {
		$isWholeOrderDiscount = in_array($discountType, ['whole_order_percentage', 'whole_order_fixed']);
		return $isWholeOrderDiscount;
	}

	/**
	 * Is Whole Order Fixed Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isWholeOrderFixedDiscount($discountType) {
		$isWholeOrderFixedDiscount = $discountType === 'whole_order_fixed';
		return $isWholeOrderFixedDiscount;
	}

	/**
	 * Is Whole Order Percentage Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isWholeOrderPercentageDiscount($discountType) {
		$isWholeOrderPercentageDiscount = $discountType === 'whole_order_percentage';
		return $isWholeOrderPercentageDiscount;
	}

	// CATEGORIES DISCOUNTS

	/**
	 * Is Categories Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isCategoriesDiscount($discountType) {
		$isCategoriesDiscount = in_array($discountType, [
			'categories_percentage',
			'categories_fixed_per_order',
			'categories_fixed_per_item',
		]);
		return $isCategoriesDiscount;
	}

	/**
	 * Is Categories Percentage Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isCategoriesPercentageDiscount($discountType) {
		$isCategoriesPercentageDiscount = $discountType === 'categories_percentage';
		return $isCategoriesPercentageDiscount;
	}

	/**
	 * Is Categories Fixed Per Order Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isCategoriesFixedPerOrderDiscount($discountType) {
		$isCategoriesFixedPerOrderDiscount = $discountType === 'categories_fixed_per_order';
		return $isCategoriesFixedPerOrderDiscount;
	}

	/**
	 * Is Categories Fixed Per Item Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isCategoriesFixedPerItemDiscount($discountType) {
		$isCategoriesFixedPerItemDiscount = $discountType === 'categories_fixed_per_item';
		return $isCategoriesFixedPerItemDiscount;
	}

	// PRODUCTS DISCOUNTS

	/**
	 * Is Products Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isProductsDiscount($discountType) {
		$isProductsDiscount = in_array($discountType, [
			'products_percentage',
			'products_fixed_per_order',
			'products_fixed_per_item',
		]);
		return $isProductsDiscount;
	}

	/**
	 * Is Products Percentage Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isProductsPercentageDiscount($discountType) {
		$isProductsPercentageDiscount = $discountType === 'products_percentage';
		return $isProductsPercentageDiscount;
	}

	/**
	 * Is Products Fixed Per Order Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isProductsFixedPerOrderDiscount($discountType) {
		$isProductsFixedPerOrderDiscount = $discountType === 'products_fixed_per_order';
		return $isProductsFixedPerOrderDiscount;
	}

	/**
	 * Is Products Fixed Per Item Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isProductsFixedPerItemDiscount($discountType) {
		$isProductsFixedPerItemDiscount = $discountType === 'products_fixed_per_item';
		return $isProductsFixedPerItemDiscount;
	}

	// FREE SHIPPING DISCOUNT

	/**
	 * Is Free Shipping Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isFreeShippingDiscount($discountType) {
		$isFreeShippingDiscount = $discountType === 'free_shipping';
		return $isFreeShippingDiscount;
	}

	/**
	 * Is Free Shipping Discount Applied To Order.
	 *
	 * @return bool
	 */
	public function isFreeShippingDiscountAppliedToOrder() {
		// GRAB REDEEMED DISOUNTS INFO FROM THE SESSION
		$redeemedDiscountsIDs = $this->getSessionRedeemedDiscountsIDs();
		$redeemedDiscounts = NULL;
		$freeShippingDiscount = NULL;

		if (!empty($redeemedDiscountsIDs)) {
			/** @var WireArray $redeemedDiscounts */
			$redeemedDiscounts = $this->getSessionRedeemedDiscounts();
			// -----
			// check for one discount of type 'free shipping'
			$freeShippingDiscount = $redeemedDiscounts->get("discountType=free_shipping");
		}

		$isFreeShippingDiscountAppliedToOrder = !empty($freeShippingDiscount);
		// -------
		return $isFreeShippingDiscountAppliedToOrder;
	}

	// BOGO DISCOUNT

	/**
	 * Is Bogo Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isBogoDiscount($discountType) {
		$isBogoDicount = in_array($discountType, ['categories_get_y', 'products_get_y']);
		return $isBogoDicount;
	}

	// TODO MAKING THIS PRIVATE FOR NOW SINCE THEY RELY ON IN MEMORY CHECKS
	// TODO MAYBE PASS OPTIONAL PARAMETER TO ALLOW FOR CHECK?

	/**
	 * Is Bogo Buy X Categories Discount.
	 *
	 * @return bool
	 */
	private function isBogoBuyXCategoriesDiscount() {
		$isCategoriesBuyX = !empty($this->discountEligibility->get("itemType=" . PwCommerce::DISCOUNT_BOGO_CATEGORIES_BUY_X));
		return $isCategoriesBuyX;
	}

	/**
	 * Is Bog Get Y Categories Discount.
	 *
	 * @return bool
	 */
	private function isBogGetYCategoriesDiscount() {
		$isCategoriesGetY = !empty($this->discountAppliesTo->get("itemType=" . PwCommerce::DISCOUNT_BOGO_CATEGORIES_GET_Y));
		return $isCategoriesGetY;
	}

	/**
	 * Is Bogo Buy X Products Discount.
	 *
	 * @return bool
	 */
	private function isBogoBuyXProductsDiscount() {
		$isProductsBuyX = !empty($this->discountEligibility->get("itemType=" . PwCommerce::DISCOUNT_BOGO_PRODUCTS_BUY_X));
		return $isProductsBuyX;
	}

	/**
	 * Is Bog Get Y Products Discount.
	 *
	 * @return bool
	 */
	private function isBogGetYProductsDiscount() {
		$isProductsGetY = !empty($this->discountAppliesTo->get("itemType=" . PwCommerce::DISCOUNT_BOGO_PRODUCTS_GET_Y));
		return $isProductsGetY;
	}

	// FIXED DISCOUNTS

	/**
	 * Is Fixed Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isFixedDiscount($discountType) {
		$isFixedDiscount = in_array($discountType, [
			// whole order
			'whole_order_fixed',
			// categories
			'categories_fixed_per_order',
			'categories_fixed_per_item',
			// products
			'products_fixed_per_order',
			'products_fixed_per_item',
		]);
		return $isFixedDiscount;
	}

	/**
	 * Is Fixed Per Order Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isFixedPerOrderDiscount($discountType) {
		$isFixedPerOrderDiscount = in_array($discountType, [
			// whole order
			'whole_order_fixed',
			// categories
			'categories_fixed_per_order',
			// products
			'products_fixed_per_order',
		]);
		return $isFixedPerOrderDiscount;
	}

	/**
	 * Is Fixed Per Item Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isFixedPerItemDiscount($discountType) {
		$isFixedPerItemDiscount = in_array($discountType, [
			// categories
			'categories_fixed_per_item',
			// products
			'products_fixed_per_item',
		]);
		return $isFixedPerItemDiscount;
	}

	// PERCENTAGE DISCOUNTS

	/**
	 * Is Percentage Discount.
	 *
	 * @param mixed $discountType
	 * @return bool
	 */
	public function isPercentageDiscount($discountType) {
		$isProductsDiscount = in_array($discountType, [
			// whole order
			'whole_order_percentage',
			// categories
			'categories_percentage',
			// products
			'products_percentage',
		]);
		return $isProductsDiscount;
	}

	/**
	 * Get Unique Automatic Discount Code.
	 *
	 * @return mixed
	 */
	public function getUniqueAutomaticDiscountCode() {
		// do-while Loop
		do {
			// generate code
			$code = $this->generateUniqueAutomaticDiscountCode();

			$discountFieldName = PwCommerce::DISCOUNT_FIELD_NAME;
			$selectorArray = [
				'template' => PwCommerce::DISCOUNT_TEMPLATE_NAME,
				"{$discountFieldName}.code" => $code,
				'status<' => Page::statusTrash
			];

			// have we found an existing DISCOUNT with the same code?
			// if YES, we generate another code and check again
			$found = $this->wire('pages')->getRaw($selectorArray, 'id');

		} while (!empty($found));

		return $code;
	}

	/**
	 * Generate Unique Automatic Discount Code.
	 *
	 * @return mixed
	 */
	private function generateUniqueAutomaticDiscountCode() {
		$bytes = random_bytes(6);
		// $code = strtoupper(bin2hex($bytes)); // 12 digit code
		$code = bin2hex($bytes); // 12 digit code

		// -----------
		return $code;
	}

	/**
	 * Get order cart in session.
	 *
	 * @return mixed
	 */
	private function getOrderCart() {
		/** @var array $cart */
		$cart = $this->getCart();
		// ----
		return $cart;
	}

	/**
	 * Get Order Cart Products And Variants I Ds.
	 *
	 * @return mixed
	 */
	private function getOrderCartProductsAndVariantsIDs() {
		$cart = $this->getOrderCart();
		$cartProductsAndVariantsIDs = array_column($cart, 'product_id');
		// ----
		return $cartProductsAndVariantsIDs;
	}

	/**
	 * Get Order Cart Top Level Products I Ds.
	 *
	 * @return mixed
	 */
	private function getOrderCartTopLevelProductsIDs() {
		$cart = $this->getOrderCart();
		$cartTopLevelProductsIDs = [];
		foreach ($cart as $item) {
			$cartTopLevelProductsIDs[] = $item->pwcommerce_is_variant ? $item->pwcommerce_variant_parent_id : $item->product_id;
		}
		// ----
		return $cartTopLevelProductsIDs;
	}

	/**
	 * For items in the cart, find their categories IDs.
	 *
	 * @param bool $isIncludeProductIDs
	 * @return mixed
	 */
	private function getOrderCartCategoriesIDs(bool $isIncludeProductIDs = false) {

		// TODO DELETE IF NO LONGER IN USE! SPLITTING THIS UP FOR CATEGORIES ONLY!
		$cartTopLevelProductsIDs = $this->getOrderCartTopLevelProductsIDs();
		$cartCategoriesIDs = [];
		// GET CATEGORIES IDs FOR THE PRODUCTS IN THE CART
		// @note: since we have top level products IDs here, we don't need to consider variants; they have no categories
		$cartProductsIDsSelector = implode("|", $cartTopLevelProductsIDs);
		// $fields = ["pwcommerce_categories.id","pwcommerce_categories.title"];
		$fields = "pwcommerce_categories.id";
		$cartCategoriesIDsRaw = $this->findRaw("template=product,id={$cartProductsIDsSelector}", $fields);

		if (!empty($isIncludeProductIDs)) {

			// IF WE WANT THE PRODUCT IDS ASSOCIATED WITH THESE CATEGORIES
			// RETURN THE NESTED ARRAY WITH productID => categoriesIDsArray
			return $cartCategoriesIDsRaw;
		}

		## =======
		if (!empty($cartCategoriesIDsRaw)) {
			foreach ($cartCategoriesIDsRaw as $productID => $categoriesIDs) {
				$cartCategoriesIDs = array_merge($cartCategoriesIDs, array_values($categoriesIDs));
			}
			// remove duplicates
			$cartCategoriesIDs = array_unique($cartCategoriesIDs);
		}

		// ----
		return $cartCategoriesIDs;
	}

	/**
	 * For BOGO BUY X Categories (categories_buy_x).
	 *
	 * @param mixed $cartItemsProductsORCategoriesIDs
	 * @return mixed
	 */
	private function getOrderCartEligibleProductsAndVariantsIDsForAssociatedCategories($cartItemsProductsORCategoriesIDs) {
		$cart = $this->getOrderCart();
		$cartEligibleProductsAndVariantsIDsForCategoriesBuyX = [];
		$cartCategoriesProductsIDs = array_keys($cartItemsProductsORCategoriesIDs);
		foreach ($cart as $item) {
			if (in_array($item->pwcommerce_variant_parent_id, $cartCategoriesProductsIDs)) {
				// variant parent product matched with this category ID
				$cartEligibleProductsAndVariantsIDsForCategoriesBuyX[] = $item->product_id;
			} else if (in_array($item->product_id, $cartCategoriesProductsIDs)) {
				// product matched with this category ID
				$cartEligibleProductsAndVariantsIDsForCategoriesBuyX[] = $item->product_id;
			}
		}
		// --------
		return $cartEligibleProductsAndVariantsIDsForCategoriesBuyX;
	}

	/**
	 * Get Session Redeemed Discounts.
	 *
	 * @return mixed
	 */
	public function getSessionRedeemedDiscounts() {

		/** @var array $redeemedDiscounts */
		$redeemedDiscounts = $this->session->get(PwCommerce::DISCOUNT_REDEEMED_DISCOUNTS);

		// $discounts = NULL;
		$discounts = new WireArray();

		if (!empty($redeemedDiscounts)) {
			// $discounts = new WireArray();
			foreach ($redeemedDiscounts as $redeemedDiscount) {
				// $d = new NullPage();
				$discount = new WireData();
				$discount->setArray($redeemedDiscount);

				$discounts->add($discount);
			}

		}

		// ======
		return $discounts;

	}

	/**
	 * Get Session Redeemed Discounts I Ds.
	 *
	 * @return mixed
	 */
	public function getSessionRedeemedDiscountsIDs() {
		/** @var array $redeemedDiscountsIDs */
		$redeemedDiscountsIDs = $this->session->get(PwCommerce::DISCOUNT_REDEEMED_DISCOUNTS_IDS);
		// ------
		return $redeemedDiscountsIDs;
	}

	/**
	 * Track Redeemed Discounts I Ds.
	 *
	 * @param int $discountID
	 * @return mixed
	 */
	private function trackRedeemedDiscountsIDs($discountID) {
		// GET ALREADY SAVED DISCOUNT IDS FROM SESSION
		$redeemedDiscountsIDs = $this->getSessionRedeemedDiscountsIDs();
		// -
		if (empty($redeemedDiscountsIDs)) {
			// SETTING BRAND SESSION FOR REDEEMED DISCOUNT IDs
			$redeemedDiscountsIDs = [];
		}
		$redeemedDiscountsIDs[$discountID] = $discountID;
		// SAVE TO SESSION
		$this->session->set(PwCommerce::DISCOUNT_REDEEMED_DISCOUNTS_IDS, $redeemedDiscountsIDs);
	}

	/**
	 * Is Discount Already Applied.
	 *
	 * @param int $discountID
	 * @return bool
	 */
	private function isDiscountAlreadyApplied($discountID) {
		$redeemedDiscountsIDs = $this->getSessionRedeemedDiscountsIDs();

		$isDiscountAlreadyApplied = is_array($redeemedDiscountsIDs) && in_array($discountID, $redeemedDiscountsIDs);

		// ------
		return $isDiscountAlreadyApplied;
	}

	/**
	 * Remove Tracked Redeemed Discount I D.
	 *
	 * @param int $discountID
	 * @return mixed
	 */
	public function removeTrackedRedeemedDiscountID($discountID) {
		// first check if the discount ID to remove is being tracked
		$isDiscountAlreadyApplied = $this->isDiscountAlreadyApplied($discountID);

		if (!empty($isDiscountAlreadyApplied)) {
			// GET ALREADY SAVED DISCOUNT IDS FROM SESSION
			$redeemedDiscountsIDs = $this->getSessionRedeemedDiscountsIDs();
			// REMOVE IT FROM REDEEMED DISCOUNTS IDS LIST
			unset($redeemedDiscountsIDs[$discountID]);
			// SAVE TO SESSION
			$this->session->set(PwCommerce::DISCOUNT_REDEEMED_DISCOUNTS_IDS, $redeemedDiscountsIDs);
		}
	}

}