<?php

namespace ProcessWire;

trait TraitPWCommerceActionsNewItemExtra
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ NEW ITEM EXTRA ~~~~~~~~~~~~~~~~~~

	/**
	 * For add new page for a given context, check if extra operations need to be run.
	 *
	 * @return bool
	 */
	private function isContextRunExtraAddNewItemOperations() {
		// $parent = null;
		$contextsRunningExtraAddNewItemOperations = [
			'products',
			'orders',
			'gift-cards',
			'discounts',
		];

		//-------------
		return in_array($this->actionContext, $contextsRunningExtraAddNewItemOperations);
	}

	/**
	 * For add new page for a given context, that runs xtra operations before create new page/item.
	 *
	 * @param Page $page
	 * @param array $options
	 * @return mixed
	 */
	private function runContextExtraAddNewItemOperations(Page $page, array $options = []) {
		if ($this->actionContext === 'products') {
			$page = $this->runProductExtraAddNewItemOperations($page);
		} else if ($this->actionContext === 'orders') {
			$page = $this->runOrderExtraAddNewItemOperations($page);
		} else if ($this->actionContext === 'discounts') {
			$page = $this->runDiscountExtraAddNewItemOperations($page);
		}
		//-------------
		return $page;
	}

	/**
	 * Run extra operations before create new product page.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function runProductExtraAddNewItemOperations(Page $page) {

		// get shop's general settings and check if we need to add default properties to new pages
		$generalSettings = $this->pwcommerce->getShopGeneralSettings();

		// we have default product properties to apply to this new product
		if (!empty($generalSettings['default_product_properties'])) {
			//-----------
			$propertyIDs = $generalSettings['default_product_properties'];
			//----------
			foreach ($propertyIDs as $propertyID) {
				// @note: we have no access to the field's getBlankRecord() here, so just use WireData()
				$property = new WireData();
				$property->propertyID = (int) $propertyID;
				//------------
				$page->pwcommerce_product_properties->add($property);
			}
		}

		// by default, enable the product
		$stock = new WireData();
		$stock->enabled = 1;
		// $page->pwcommerce_product_stock = $stock;
		$page->set(PwCommerce::PRODUCT_STOCK_FIELD_NAME, $stock);

		// by default, charge taxes on the product
		$settings = new WireData();
		$settings->taxable = 1;
		$page->pwcommerce_product_settings = $settings;

		//-------------
		return $page;
	}

	/**
	 * Run Order Extra Add New Item Operations.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function runOrderExtraAddNewItemOperations(Page $page) {
		// blank order (FieldtypePWCommerceOrder)
		$order = new WireData();
		// @note! default to some past date just so we know this is an incomplete order
		$order->paidDate = strtotime('2016-04-08 5:10:02 PM');
		$order->isPricesIncludeTaxes = (int) $this->pwcommerce->isPricesIncludeTaxes();
		// TODO: MORE?
		$page->pwcommerce_order = $order;
		return $page;
	}

	/**
	 * Run Gift Card Extra Add New Item Operations.
	 *
	 * @param Page $page
	 * @param array $options
	 * @return mixed
	 */
	private function runGiftCardExtraAddNewItemOperations(Page $page, array $options) {

		# ============
		/*	---
										1. populate pwcommerce_gift_card
										2. populate pwcommerce_notes if needed
										@note: we don't need to populate 'populate pwcommerce_gift_card_activities' since no order saved at this point
										--- */

		// GC fields
		$customerEmail = $options['customer_email'];
		$denomination = $options['denomination'];
		$code = $options['code'];
		$endDate = $options['end_date']; // @note: null if no end date
		// ------
		// OTHER GC fields
		// full balance
		$balance = $denomination;

		// blank gift card (FieldtypePWCommerceGiftCard)
		$giftCard = new WireData();
		$giftCard->customerEmail = $customerEmail;
		$giftCard->code = $code;
		$giftCard->denomination = $denomination;
		$giftCard->balance = $balance;
		$giftCard->endDate = $endDate;
		// -----------
		$page->pwcommerce_gift_card = $giftCard;
		// -----
		// ADMIN NOTE
		$adminNote = $options['admin_note'];
		// TODO CHECK IF NOT EMPTY AND ADD NOTE
		if (!empty($adminNote)) {
			# 2. ADD ADMIN NOTE (IF SENT/if applicable)
			// =========
			// ADD ADMIN NOTE ABOUT ORDER STATUS CHANGE
			$noteText = $adminNote;
			/** @var WireData $note */
			$noteType = 'admin';
			$userID = $this->wire('user')->id;
			$note = $this->pwcommerce->buildNote($noteText, $noteType, $userID);
			// -----
			$page->pwcommerce_notes->add($note);
		}

		// ----------
		return $page;
	}
	/**
	 * Run Discount Extra Add New Item Operations.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function runDiscountExtraAddNewItemOperations(Page $page) {

		$input = $this->actionInput; // @note this is $input->post!!

		// @note: if we in this method/here, this has already been sanitized. @see:
		$seletedDiscountType = $input->pwcommerce_create_discount_type;

		// DISCOUNT TYPE
		// we default to percentage for order or products discounts
		// if (in_array($seletedDiscountType, ['amount_off_products', 'amount_off_order'])) {
		if ($seletedDiscountType === 'amount_off_order') {
			$discountType = 'whole_order_percentage';
		} else if ($seletedDiscountType === 'amount_off_products') {
			$discountType = 'products_percentage';
		} else if ($seletedDiscountType === 'buy_x_get_y') {
			$discountType = 'products_get_y';
		} else {
			// 'free shipping'
			$discountType = $seletedDiscountType;
		}

		// TODO? @NOTE: NO LONGER IN USE; WE NOW GET FROM 'DISCOUNT TYPE'
		// DISCOUNT APPLIES TO TYPE
		// we default to 'order' for 'order' and 'shipping' discounts
		// $discountAppliesToType = 'order';
		// if (in_array($seletedDiscountType, ['amount_off_products', 'buy_x_get_y'])) {
		// 	$discountAppliesToType = 'products';
		// }

		// blank order (FieldtypePWCommerceDiscount)
		$discount = new WireData();

		# @NOTE: BELOW ARE MOSTLY DEFAULTS FOR A NEW DISCOUNT!

		// $discount->discountID = xxxx;// TODO CONFIRM AUTOINCREMENT WORKS!
		$discount->isAutomaticDiscount = 0; // default to false
		$discount->discountType = $discountType;
		// TODO? @NOTE: NO LONGER IN USE; WE NOW GET FROM 'DISCOUNT TYPE'
		// $discount->discountAppliesToType = $discountAppliesToType;
		// TODO: MORE?
		$page->pwcommerce_discount = $discount;

		return $page;
	}

	/**
	 * Run extra operations before create new country page.
	 *
	 * @param Page $page
	 * @param mixed $countryCode
	 * @return mixed
	 */
	private function runCountryExtraAddNewItemOperations(Page $page, $countryCode) {
		// @note: we have no access to the field's getBlankRecord() here, so just use WireData()
		$countryTaxRate = new WireData();
		$countryTaxRate->taxLocationCode = $countryCode;
		// @note: this is an array if not empty!
		$countryBaseTaxRate = $this->getCountryBaseTaxRate($countryCode);
		if (!empty($countryBaseTaxRate)) {
			// short name
			$taxAbbreviation = $countryBaseTaxRate['name'];
			// the rate
			$taxRate = (float) $countryBaseTaxRate['rate'];
			$countryTaxRate->taxName = $taxAbbreviation;
			$countryTaxRate->taxRate = $taxRate;
		}
		//------------
		$page->pwcommerce_tax_rates->add($countryTaxRate);
		//-------------
		return $page;
	}

}
