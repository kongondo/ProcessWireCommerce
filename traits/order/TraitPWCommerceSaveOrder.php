<?php

namespace ProcessWire;

trait TraitPWCommerceSaveOrder
{

	/**
	 *    save Order.
	 *
	 * @param mixed $form
	 * @return mixed
	 */
	public function ___saveOrder($form = null)
	{

		// $isCustomForm = $this->isCustomForm;
		// $isUseCustomFormInputNames = $this->isUseCustomFormInputNames;



		$newOrder = false;
		if (!$this->orderPage) {
			$newOrder = true;
		}
		// There is already successful order with this id, so this is going to be new order
		// TODO @KONGONDO -> SUCCESSFUL ORDERS ARE PUBLISHED! here we check that
		// TODO @KONGONDO MAYBE ADD CHECK OF STATUS?
		else if (!$this->orderPage->is(Page::statusUnpublished)) {

			// ==============
			// REMOVE THIS SUCCESSFUL ORDER'S ID FROM THE SESSION; WE WILL CREATE A NEW ONE
			$this->session->remove('orderId');
			$newOrder = true;
		} else {
		}

		if ($newOrder) {
			// ==============
			// CREATE NEW ORDER (PARENT OF ORDER LINE ITEMS!)
			// ---------------------------
			// TODO @KONGONDO AMENDMENT
			// $this->orderPage = new PWCommerceOrder();
			// -----------
			$languages = $this->wire('languages');

			$this->orderPage = $this->getNewOrderPage();
			$this->orderPage->title = "Order" . $this->orderPage->name;

			// set order page as active in other languages
			if ($languages) {
				foreach ($languages as $language) {
					// skip default language as already set above in getNewOrderPage()
					if ($language->name == 'default') {
						continue;
					}
					$this->orderPage->set("status$language", 1);
				}
			}

			$this->orderPage->save(); // We want the order to exist right away
		} else {
			# ***************
			// EXISTING ORDER: SET SESSION FLAG TO UPDATE INSTEAD OF CREATE NEW LINE ITEMS
			// SET ORDER HAS BEEN CONFIRMED FLAG TO SESSION
			// helps track any basket/cart changes POST order creation/confirmation BUT BEFORE completion/payment
			$this->session->set('isOrderConfirmed', true);
			# ***************
		}

		// ==============
		// >>>>>>>>>>>>>>>>>>>> IMPORTANT !!! <<<<<<<<<<<<<<<<<<<<<<<<
		// TODO @KONGONDO -> COMMENT
		// *** SET SESSION ORDER TO THE ID OF THE ORDER ***
		// @note: setting the session value directly
		// @see: $this->construct()!!!
		// @see: $this->setOrderPage()
		$this->session->orderId = $this->orderPage->id;
		// TODO @KONGONDO: SINCE USING AJAX, WE ALSO NEED TO SET THE URL OR THE ID OF THE CHECKOUT PAGE AS A BACKUP SINCE WE WILL BE CHECKING OUT USING AJAX AND WILL NOT HAVE ACCESS TO $this->page!
		$this->session->checkoutPageID = $this->page->id;
		// TODO CHOOSE ONE!
		$this->session->checkoutPageURL = $this->page->url;
		$this->session->checkoutPageHttpURL = $this->page->httpUrl;
		//

		# >>>>>>>>>>>>>>>>>>>> ************************* <<<<<<<<<<<<<<<<<<<<<<<< #

		// ==============
		// TODO @KONGONDO -> COMMENT
		// OUTPUT FORMATTING NOT NEEDED
		// TODO: MAYBE NOT REQUIRED!?
		$orderPage = $this->orderPage;
		$orderPage->of(false);

		// ==============
		// TODO @KONGONDO -> COMMENT
		// INITIALLY SET ORDER AS UNPUBLISHED
		// TODO -> WE WILL ALSO SET STATUS AS 'ABANDONED CHECKOUT' AT THIS POINT
		// TODO ...SINCE THIS WILL BE AT CHECKOUT
		$orderPage->addStatus(Page::statusUnpublished);

		// ---------------
		// SAVE ORDER CUSTOMER VALUES
		// we know the customer fields to expect
		// @note: calling before parseCart() so we can set the shipping country early
		// we'll need that for calculating taxes in order line items
		// and later in setOrderPagePWCommerceOrderValues() when matching shipping rates
		// TODO WITH NEW LOGIC OF UPDATING CART, WE SHOULD ONLY DO THIS IF WE HAVE A FORM; IN THE CASE OF CHANGES DUE TO A CART UPDATE WE WILL SEND REQUEST WITHOUT A FORM -> otherwise, we still want to save changes to form in the re-confirm stage, even though in that case a post-form 'isOrderConfirmed' will still be valid
		if (!empty($form)) {
			$orderPage = $this->setOrderPagePWCommerceOrderCustomerValues($form, $orderPage);
		} else {
		}

		// ---------------
		// PROCESS ORDER DISCOUNTS
		// these are whole order and free shipping discounts that have been redeemed in this session AS WELL AS order line items PRODUCT/CATEGORIES DISCOUNTS AND BOGO
		$orderCustomer = $this->getOrderCustomer();

		$shippingAddressCountryID = $this->session->shippingAddressCountryID;
		// $this->pwcommerce->pwcommerceDiscounts->validateAndApplyDiscounts($orderCustomer->email, $shippingAddressCountryID);
		$this->validateAndApplyDiscounts($orderCustomer->email, $shippingAddressCountryID);
		# >>>>>>> CREATE/ADD ORDER LINE ITEMS! <<<<<<<<
		$this->parseCart();
		# >>>>>>> END: ADD ORDER LINE ITEMS! <<<<<<<<

		// SAVE ORDER VALUES FOR 'pwcommerce_order' FIELD
		$orderPage = $this->setOrderPagePWCommerceOrderValues($orderPage);

		// @NOTE: this sets some shipping values (handling fee values)
		// it also sets a temporary order total price that includes handling fees
		// @note: later, we will amend the shipping fee and total price based on the shipping rate selected by the customer (if more than one is matched)
		// separately, we also get matched shipping rates
		// TODO IF ONLY ONE MATCHED RATE; SAVE IT TO ORDER NOW! -> add to pwcommerce_order! -> total price + shipping fee!
		$orderPage = $this->setOrderPagePWCommerceOrderShippingValues($orderPage);



		// ==============
		// SAVE THE ORDER AGAIN!
		// after changes above
		$orderPage->of(false);
		$orderPage->save();
		$this->setOrderPage($orderPage);

		###
		// PROCESS ANY GIFT CARD OR DISCOUNT CODE
		// TODO @UPDATE - TUESDAY 10 OCTOBER 2023 - SEE NOTES! IN THIS CLASS, WE NOW DON'T CHECK INPUTS FOR DISCOUNTS/GIFT CARDS. THEIR RESPECTIVE CLASSES WILL HANDLE THEIR APPLICATION AND DEV WILL HANDLE FORM SUBMISSIONS. HERE WE ONLY CHECK SESSION FOR DISCOUNTS; ON ORDER COMPLETE, WE CALL THE DISCOUNTS CLASS TO SAVE DISCOUNT DETAILS TO THE ORDER; WE THEN CLEANUP THE SESSIONS AS USUAL.

		// TODO - WIP! SAVE ANY REDEEMED FREE SHIPPING DISCOUNTS TO ORDER'S DISCOUNTS FIELD
		// GRAB REDEEMED DISOUNTS INFO FROM THE SESSION
		$pwcommerce = $this->pwcommerce;
		// $redeemedDiscountsIDs = $pwcommerce->pwcommerceDiscounts->getSessionRedeemedDiscountsIDs();
		$redeemedDiscountsIDs = $this->getSessionRedeemedDiscountsIDs();
		$redeemedDiscounts = NULL;
		// $freeShippingDiscount = NULL;
		$freeShippingDiscounts = new WireArray();
		if (!empty($redeemedDiscountsIDs)) {
			/** @var WireArray $redeemedDiscounts */
			// $redeemedDiscounts = $pwcommerce->pwcommerceDiscounts->getSessionRedeemedDiscounts();
			$redeemedDiscounts = $this->getSessionRedeemedDiscounts();
			// -----
			// check for one discount of type 'free shipping'
			// @note: using find() so we get a WireArray back
			$freeShippingDiscounts = $redeemedDiscounts->find("discountType=free_shipping");

			// save free shipping values if we found at least one item

		}

		// save free shipping values if we found at least one item
		// OTHERWISE: delete previous values and replace with empty WireArray
		$orderPage->set(PwCommerce::ORDER_DISCOUNTS_FIELD_NAME, $freeShippingDiscounts);


		// ==========

		// SAVE ORDER PAGE
		$orderPage->save();

		#########################
		// ORDER CACHE: save order details to cache
		// /* NOTE:
		// 	- as backup for when 'sessions get lost'
		// 	- expires in 24 hours so can be used for other external post processing
		// */
		// TODO OK?
		// matches the session ID in 'padoper_cart' table
		$sessionID = session_id();
		$this->createOrderCache($orderPage, $sessionID);
		#########################

		// ==============
		// TODO @KONGONDO -> COMMENT
		// FOR HOOKING AFTER ORDER CREATION
		/** @var PageArray $orderLineItemsPages */
		$orderLineItemsPages = $this->getOrderLineItemsPages($orderPage);

		/** @var WireArray $orderLineItems */
		$orderLineItems = $this->getOrderLineItems($orderPage);

		$this->orderSavedHook($this->orderPage, $orderLineItemsPages, $orderLineItems);
	}
}
