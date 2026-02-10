<?php

namespace ProcessWire;

trait TraitPWCommerceParseCart
{


	// called by TraitPWCommerceSaveOrder::saveOrder

	/**
	 *    parse Cart.
	 *
	 * @return mixed
	 */
	public function ___parseCart() {

		// =============


		$isOrderAlreadyConfirmed = $this->session->isOrderConfirmed;

		if ($isOrderAlreadyConfirmed) {
			// IF ORDER ALREADY CONFIRMED, CHECK FOR ANY ORDER LINE ITEMS THAT HAVE BEEN 'DETACHED' FROM THE CART AND DELETE THEM
			// REMOVE ANY 'ORPHANED' ORDER LINE ITEMS
			// all line items must also be present in the cart!
			$this->processOrderLineItemsDetachedFromCart();
		}

		// ==========
		// TODO @KONGONDO -> COMMENT
		// TODO: we could just use $this->orderPage as well??
		// TODO @note: this is currently PWCommerceOrder which extends Page
		/** @var Page $orderPage */
		$orderPage = $this->getOrderPage();

		// ==============
		// TODO @KONGONDO -> COMMENT
		/** @var stdClass $cartItems */
		$cartItems = $this->getCart();


		// ==============
		// TODO @KONGONDO -> COMMENT
		# >>>>>>> CREATE AND ADD ORDER LINE ITEMS! <<<<<<<<
		// ==============
		// -------------
		// GET SHIPPING COUNTRY FOR LATER USE IN $this->setOrderLineItemCalculatedValues()
		/** @var Page $shippingCountryPage */
		$shippingCountryPage = $this->wire('pages')->get((int) $this->session->shippingAddressCountryID);

		// GRAB REDEEMED DISOUNTS INFO FROM THE SESSION
		$pwcommerce = $this->pwcommerce;
		$redeemedDiscountsIDs = $pwcommerce->getSessionRedeemedDiscountsIDs();
		$redeemedDiscounts = new WireArray();
		if (!empty($redeemedDiscountsIDs)) {
			/** @var WireArray $redeemedDiscounts */
			// $redeemedDiscounts = $pwcommerce->getSessionRedeemedDiscounts();
			$redeemedDiscounts = $this->getSessionRedeemedDiscounts();
		}

		$pages = $this->wire('pages');
		$languages = $this->wire('languages');

		$fieldtype = $this->wire('fields')->get("title")->type;
		foreach ($cartItems as $product) {

			/** @var stdClass $product */
			// We don't have to calculate any total amounts or taxes here, since WE DO THAT IN setOrderLineItemCalculatedValues()
			// TODO @KONGONDO -> Add a new page using the given template and parent -> $pages->add()
			// $p = wire('pages')->add('padorder_product', $this);
			// ==============
			// TODO @KONGONDO -> COMMENT
			// TODO: this creates page whose template is 'padorder_product' and parent is $order ($this->orderPage)
			// TODO @KONGONDO AMENDMENT

			$orderLineItemPageTitle = $product->pwcommerce_title;
			$name = "Order Line Item " . $orderLineItemPageTitle;
			$orderLineItemPageName = $this->wire('sanitizer')->pageName($name, true);

			$isNewItem = false;
			// ---------------
			if ($isOrderAlreadyConfirmed) {
				// IF ORDER ALREADY CONFIRMED
				if (!empty($this->isOrderLineItemAlreadyExists($orderLineItemPageName, $orderPage))) {
					// IF EXISTING ITEM

					// TODO
					// process quantity
					// if gone up change quantity
					// if gone up BUT ITEM HIDDEN; change quantity + remove hidden status
					// if gone down but not zero, change quantity @note if zero, will come in via $removedProductIDs
					// # for above, summary is that quantity is either up or unchanged. it cannot be zero unless it is in $removedProductIDs
					// this means that if status is hidden here, we need to remove it; alternative check is that
					// if gone down AND ZERO -> add hidden status / hide the line item
					// TODO: if zero AND existing, need to find from session or passed in! via $this->processExistingLineItemsRemovedFromCart	> so, that method should call saveOrder and add these to memory?
					// NOTE: in 'TraitPWCommerceSaveOrder'
					// => SINCE REMOVED WILL NOT BE IN CART, WE NEED TO PROCESS THEM SEPARATELY!
					// =========
					// GRAND SUMMARY:
					// 1. IF HERE, IT MEANS QUANTITY HAS GONE UP OR UNCHANGED OR DECREASED BUT NOT ZERO! SO, WE HIDE STATUS ANYWAY, TO COVER WAS REMOVE BUT NOW REINSTATED
					// 2. IF REMOVED, IT WON'T BE HERE BUT IN $removedProductIDs; WE PROCESS THOSE SEPARATELY JUST BY ADDING HIDDEN STATUS
					//

					# *** FETCH EXISTING ORDER LINE ITEM PAGE ***
					// TODO BUT NEED TO CHECK FOR UPDATE!!! E.G. BASKET UPDATED POST-CONFIRMATION AND RE-CONFIRMED!
					// PREVENT DUPLICATE ORDER LINE ITEMS
					// continue;
					// TODO FOR NOW, WE UPDATE IT INSTEAD!!!
					// TODO BETTER WAY TO GET IT? EXISTING ID SOMEHOW? OR CHECK USING PRODUCT ID?

					// =================
					// GET THE PAGE
					$orderLineItemPage = $pages->get("template=" . PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME . ", parent={$orderPage}, name={$orderLineItemPageName}");
					// REMOVE HIDDEN STATUS
					$orderLineItemPage->removeStatus(Page::statusHidden);
					// GET THE PRODUCT PAGE ASSOCIATED WITH THIS LINE ITEM
					$existingOrderLineItem = $orderLineItemPage->get(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME);
					$existingOrderLineItemProductID = $existingOrderLineItem->productID;
					$productPage = $this->wire('pages')->get($existingOrderLineItemProductID);

				} else {

					// ELSE NEW ITEM
					// TODO
					// process quantity
					// if gone up OR down change quantity
					// @note: if not zero, will not be here
					// ==============
					// GRAND SUMMARY:
					// 1. NOTHING TO DO SINCE VALUES WILL BE ADJUSTED BELOW
					// 2. ITS STATUS DOES NOT NEED TOUCHING ; ITS BASICALLY A NEW ITEM
					//
					$isNewItem = true;
				}
			} else {
				// ELSE ORDER NOT ALREADY CONFIRMED -> everything should be BRAND new
				//
				// GRAND SUMMARY:
				// 1. NOTHING TO DO SINCE NEW AND TAKE CARE OF BELOW
				//
				$isNewItem = true;

			}

			// ===========
			if ($isNewItem) {
				# *** CREATE NEW ORDER LINE ITEM PAGE ***
				// --------
				// ADD AND SAVE NEW ORDER LINE ITEM (CREATE)
				$orderLineItemPage = $pages->add(PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME, $orderPage);
				// ==============
				// TODO @KONGONDO -> COMMENT
				// @note: pwcommerce_title and other properties are added on the fly in PWCommerceCart::getCart() to the stdClass object $product
				$orderLineItemPage->title = $orderLineItemPageTitle;
				$orderLineItemPage->name = $orderLineItemPageName;

				// set order line item page as active in other languages
				// TODO REFACTOR AND ADD TO BELOW!
				if ($languages) {
					foreach ($languages as $language) {
						// skip default language as already set above
						if ($language->name == 'default') {
							continue;
						}
						$orderLineItemPage->set("status$language", 1);
					}
				}
				// ----------
				$productPage = $this->wire('pages')->get($product->product_id);
				if ($fieldtype instanceof FieldtypePageTitleLanguage) {
					foreach ($this->wire('languages') as $lang) {
						// TODO: SHOULDN'T THIS TITLE BE CONSISTENT WITH OUR $orderLineItemPageTitle above?
						$title = $this->getProductTitle($productPage, $lang);
						$orderLineItemPage->title->setLanguageValue($lang, $title);
						// TODO @KONGONDO -> WORK SETTING LANGUAGE NAME AS WELL?
					}
				}

			}

			$orderLineItemPage->of(false);

			// -------------
			// GOOD TO GO

			# PROCESS ORDERLINEITEM VALUES

			// If we have multilang title field, we update the title for each field
			// We cannot use original lang titles from the product here, since pwcommerce_title is often modified by hooks
			// $fieldtype = wire('fields')->get("title")->type;
			// TODO @KONGONDO AMENDMENT -> no need to have this $fieldtype  inside loop!
			// TODO: WHEN WE FINISH PORTING, DELETE THIS AS WE WILL KNOW WHAT OUR FILEDS ARE IN ADVANCE!
			// $fieldtype = $this->wire('fields')->get("title")->type;
			// TODO @KONGONDO -> CHANGE BELOW pages->get to getRaw() + let it return an object?

			$stock = $productPage->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);

			// TODO SPLIT TO OWN METHOD!!!!
			$orderLineItem = new WireData();
			// --------
			// 1. PRODUCT
			$orderLineItem->productID = $product->product_id;
			$orderLineItem->productTitle = $product->pwcommerce_title;
			$orderLineItem->productSKU = $stock->sku;
			$orderLineItem->quantity = $product->quantity;
			$orderLineItem->isVariant = !empty($this->isVariant($productPage)) ? 1 : 0;

			// TODO THIS AND OTHERS BELOW, SENT ONCE IN THE CALCULATED METHOD!

			# ******* THESE VALUES WILL BE RECALCULATED LATER IN $this->setOrderLineItemCalculatedValues ******* #

			// TODO HERE DECIDE IF ADDING ALL THESE AGAIN OR JUST UPDATING QUANTITY!

			// 2. DISCOUNTS
			// TODO NOT SUPPORTED FOR NOW
			// TODO NOVEMBER 2023. CHANGE THIS! ONE, NO LONGER NEEDED IN SCHEMA FOR PWCOMMERCE 009+ AND TWO, WE NEED TO ADD A PROPERTY $orderLineItem->discounts WHOSE VALUE IS A WIREARRAY. THE WIREARRAY WILL CONTAIN WIREDATA ITEMS EACH OF WHICH IS AN APPLIED DISCOUNT; EACH WILL HAVE ITS OWN DISCOUNT VALUES; EVENTUALLY WILL BE SAVED TO THE FIELD PWCOMMERCEORDERDISCOUNTS; WE PASS THIS TO PWCommerceUtilities::getOrderLineItemDiscountsAmount() TO COMPUTE DISCOUNT AMOUNT! ALSO SEE NOTES ON 'FIXED PER ORDER'
			/** @var WireArray $discounts */
			// $discounts = 'GET FROM SESSION reedemedDiscounts BUT FILTERED FOR THIS LINE ITEM USING PRODUCTID=PRODUCT_ID';
			$discounts = $redeemedDiscounts->find("product_id={$orderLineItem->productID}");

			// $discounts = new WireArray();
			$orderLineItem->discounts = $discounts;
			$orderLineItem->discountType = 'none';
			$orderLineItem->discountValue = 0;
			// @note: calculated based on discount type and value, unit price and quantity
			$orderLineItem->discountAmount = 0;

			// 3. TAXES
			$orderLineItem->taxName = '';
			$orderLineItem->taxPercentage = 0;
			$orderLineItem->taxAmountTotal = 0;
			$orderLineItem->isTaxOverride = 0;

			// 4. UNITS
			// TODO: GET FROM $stock? OR THE 'FROZEN' ONE IN CART? -> former for now
			// TODO @update! NEED TO GET 'FROZEN' ONE IN CART TO ACCOUNT FOR HOOKS!
			// $orderLineItem->unitPrice = $stock->price;
			$orderLineItem->unitPrice = $product->pwcommerce_price;
			$orderLineItem->unitPriceWithTax = 0;
			// TODO: DISCOUNTS IN FUTURE RELEASE
			$orderLineItem->unitPriceDiscounted = 0;
			// TODO: DISCOUNTS IN FUTURE RELEASE
			$orderLineItem->unitPriceDiscountedWithTax = 0;

			// 5. TOTALS
			// TODO - CONFIRM VALUES!
			$orderLineItem->totalPrice = $product->pwcommerce_price_total;

			$orderLineItem->totalPriceWithTax = 0;
			// TODO: DISCOUNTS IN FUTURE RELEASE
			$orderLineItem->totalPriceDiscounted = 0;
			// TODO: DISCOUNTS IN FUTURE RELEASE
			$orderLineItem->totalPriceDiscountedWithTax = 0;
			// TODO: DISCOUNTS IN FUTURE RELEASE
			$orderLineItem->totalDiscounts = 0;

			# ******* END VALUES TO BE RECALCULATED BELOW IN  $this->setOrderLineItemCalculatedValues() ******* #

			// @note: this will calculate price values (base, with tax, discounts, etc) for above default values
			$orderLineItem = $this->setOrderLineItemCalculatedValues($orderLineItem, $shippingCountryPage, $productPage);

			// 6. SHIPMENT
			// TODO! - FOR NOW SET AS CURRENT TIME; HOWEVER, IN FUTURE, CHECK WHOLE ORDER STATUS + IF ORDER LINE ITEM IS A DOWNLOAD, ETC
			$orderLineItem->deliveredDate = time();

			// 7. STATUSES
			// TODO - REDO IN $this->completeOrder()
			$orderLineItem->status = PwCommerce::ORDER_STATUS_ABANDONED;
			$orderLineItem->fulfilmentStatus = PwCommerce::FULFILMENT_STATUS_VOID_FULFILMENT;
			$orderLineItem->paymentStatus = PwCommerce::PAYMENT_STATUS_AWAITING_PAYMENT;
			###########

			// -------------
			// SET THE POPULATED $orderLineItem as order line item field value on the order line item page
			$orderLineItemPage->set(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME, $orderLineItem);
			// TODO WE ALSO NEED TO SAVE ORDER DISCOUNTS FOR LINE ITEM!
			$orderLineItemPage->set(PwCommerce::ORDER_DISCOUNTS_FIELD_NAME, $orderLineItem->discounts);

			// ==============
			// save the line item (including title fields, etc)
			$orderLineItemPage->save();


		}
		// end:     foreach ($cartItems as $product)
	}
}
