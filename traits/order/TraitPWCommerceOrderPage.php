<?php

namespace ProcessWire;

trait TraitPWCommerceOrderPage
{
	/**
	 * Set Order Page.
	 *
	 * @param mixed $order
	 * @return mixed
	 */
	public function setOrderPage($order)
	{
		// ---------------------------
		if (!$order instanceof Page) {
			// ==============
			// IF $order IS NOT A PAGE, GET THE ORDER PAGE
			$order = $this->pages->get($order);
		}
		// ==============
		// SET ORDER TO THE ORDER PAGE
		$this->orderPage = $order;
	}

	/**
	 * Get New Order Page.
	 *
	 * @return mixed
	 */
	public function getNewOrderPage()
	{

		$template = $this->wire('templates')->get(PwCommerce::ORDER_TEMPLATE_NAME);
		// TODO @KONGONDO -> @NOTE THESE SET THE PARENT AND NAME OF THE NEW PWCommerceOrder page to be created
		// $this->parent = wire('pages')->get("template=admin, name=pwcommerce");
		// $this->name = uniqid();
		$pages = $this->wire('pages');
		// TODO: CHECK NAME AS WELL? THAT CAN BE CHANGED?!
		$parent = $pages->get("template=" . PwCommerce::ORDER_PARENT_TEMPLATE_NAME . ", name=orders");
		$name = uniqid();
		$title = "Order" . $name;

		// TODO @KONGONDO -> NOT SURE THIS $title IS GETTING APPLIED?
		// ADD AND SAVE NEW ORDER PAGE
		$newOrderPage = $pages->add($template, $parent, $name, [
			'title' => $title,
		]);
		// ---------
		return $newOrderPage;
	}

	/**
	 * Gets the session's order's order page.
	 *
	 * @return mixed
	 */
	public function getOrderPage()
	{


		if (!$this->session->orderId) {
			// LOST SESSION
			// NEED TO SET ORDER PAGE FIRST!



			$orderID = $this->session->get(PwCommerce::ORDER_LOST_SESSION_ORDER_ID_NAME);


			$this->setOrderPage($orderID);
		}

		return $this->orderPage;
	}

	/**
	 * Set Order Page P W Commerce Order Values.
	 *
	 * @param Page $orderPage
	 * @return mixed
	 */
	private function setOrderPagePWCommerceOrderValues(Page $orderPage)
	{
		/** @var Page $orderPage */
		// $orderPage = $this->orderPage;
		// set values for the order page field 'pwcommerce_order' [PWCommerceOrder] -> WireData
		$order = new WireData();
		$sanitizer = $this->wire('sanitizer');
		// 1. MAIN

		// ** order id (read-only) **
		// @note: not needed since AUTO_INCREMENT!
		// $order->orderID = (int) $value['data'];

		// ** order paid date **
		// TODO: UNSURE
		$order->paidDate = time();

		// ** payment method  **
		$order->paymentMethod = $this->session->paymentProviderTitle;

		// 2. DISCOUNTS
		// TODO: NOT SUPPORTED IN API ORDER FOR NOW

		// ** type **
		// $discountValues = [];
		// $order->discountType = $sanitizer->pageName($discountValues['order_discount_type']);
		// // ** discountValues **
		// $order->discountValue = (float) $discountValues['order_discount_value'];
		// // ** amount **
		// $discountAmount = (float) $discountValues['order_discount_amount'];
		// $order->discountAmount = $discountAmount;

		// 3. SHIPPING

		// ** handling fee type **
		// @NOTE: JUST SETTING SOME DEFAULT VALUES HERE
		// will be changed later in $this->setOrderPagePWCommerceOrderShippingValues()
		$value['order_handling_fee_type'] = 'none';
		$value['order_handling_fee_value'] = 0;
		$value['order_handling_fee_amount'] = 0;
		$order->handlingFeeType = $sanitizer->pageName($value['order_handling_fee_type']);
		// ** handling fee value **
		$handlingFeeValue = (float) $value['order_handling_fee_value'];
		$order->handlingFeeValue = $handlingFeeValue;
		// ** handling fee amount **
		$handlingFee = (float) $value['order_handling_fee_amount'];
		$order->handlingFee = $handlingFee;

		// ** shipping amount **
		// TODO: GET FROM MATCHED SHIPPING!
		$value['order_shipping_fee'] = 0;
		$shippingFee = (float) $value['order_shipping_fee'];
		$order->shippingFee = $shippingFee;

		// ** is custom handling fee **
		// @note: we still model the value at handling fee amount!
		// @note: to identify whether the shipping handling fee was custom or per shop's settings
		// TODO @NOTE: ONLY APPLICABLE TO MANUAL ORDERS
		// $order->isCustomHandlingFee = (int) $value['is_custom_handling_fee'];
		// ** is custom shipping fee **
		// @note: to identify whether the shipping fee was custom or per shop's settings
		// @note: we still model the value at shipping fee amount!
		// $order->isCustomShippingFee = (int) $value['is_custom_shipping_fee'];

		// 4. TOTALS

		// @note: calculated from total net prices of line items  MINUS any order discount amount
		// TODO TO BE COMPLETED SOON
		// TODO @UPDATE! 24 NOVEMBER 2023 0102: NO! WE CANNOT TAX DISCOUNT! WE NEED TO DEDUCT DISCOUNTS FIRST THEN APPLY TAX!! - FOR NOW; WE INCLUDE (PROPORTIONALLY) ORDER DISCOUNTS IN LINE ITEMS DISCOUNTS!
		$totalPrice = $this->getOrderLineItemsTotalDiscountedWithTax();

		// TODO @UPDATE - TUESDAY 10 OCTOBER 2023 - SEE NOTES! IN THIS CLASS, WE NOW DON'T CHECK INPUTS FOR DISCOUNTS/GIFT CARDS. THEIR RESPECTIVE CLASSES WILL HANDLE THEIR APPLICATION AND DEV WILL HANDLE FORM SUBMISSIONS. HERE WE ONLY CHECK SESSION FOR DISCOUNTS; ON ORDER COMPLETE, WE CALL THE DISCOUNTS CLASS TO SAVE DISCOUNT DETAILS TO THE ORDER; WE THEN CLEANUP THE SESSIONS AS USUAL.
		// TODO - ABOVE MEANS WE CHECK DIFFERENTLY; WE WILL NEED TO CHECK ORDER IN SESSION INSTEAD? WE DON'T HAVE TO DEAL WITH IDS HERE; JUST THE AMOUNTS, BUT YES, IDS IMPORTANT
		// TODO @NOTE: WE WILL APPLY ANY GIFT CARD OR DISCOUNT CODES IN SESSION in getOrderGrandTotal(). HENCE, HERE AND IN ORDER ITSELF, WE KEEP THIS TOTAL! IN OTHER WORDS, THE GC/DC IS ABOUT 'TOPAY'!
		$order->totalPrice = $totalPrice;

		// 5. STATUSES
		// TODO @NOTE: WE START WITH LOWEST UNTIL ORDER COMPLETED!
		// ORDER STATUS
		// set as abandoned order until payment complete
		$order->orderStatus = PwCommerce::ORDER_STATUS_ABANDONED;
		// SHIPMENT STATUS TODO: TRICKY AS SOME ITEMS COULD BE DIGITAL!
		// @note: use this VOID STATUS as a companion for a comparable orders status,  'abandoned'
		$order->fulfilmentStatus = PwCommerce::FULFILMENT_STATUS_VOID_FULFILMENT;
		// PAYMENT STATUS
		$order->paymentStatus = PwCommerce::PAYMENT_STATUS_AWAITING_PAYMENT;

		// 6. TAXES
		// ** is prices include taxes **
		// TODO: TO BE ADDED SOON
		$value['is_prices_include_taxes'] = 1;
		// @note: to identify whether at the time the order was placed, prices included taxes
		$order->isPricesIncludeTaxes = (int) $value['is_prices_include_taxes'];

		// -----------
		// SET TO ORDER
		// @note: will be saved in saveOrder()
		// NOTE: in 'TraitPWCommerceSaveOrder'
		/** @var Page $this->orderPage */
		// $order->of(false);
		$orderPage->set(PwCommerce::ORDER_FIELD_NAME, $order);


		// TODO: not sure we need to save here since we will save in TraitPWCommerceSaveOrder::saveOrder()?
		// $order->save();
		return $orderPage;
	}
}
