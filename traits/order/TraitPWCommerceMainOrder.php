<?php

namespace ProcessWire;

trait TraitPWCommerceMainOrder
{
	/**
	 * Gets the session's order.
	 *
	 * @return mixed
	 */
	public function getOrder()
	{

		// TODO NEED TO ACCOUNT FOR LOST SESSIONS!
		// @note: init this just to avoid errors in case no order
		$order = new WireData();
		$orderPage = $this->getOrderPage();
		// if (!empty($orderPage)) {
		if (!$orderPage instanceof NullPage) {
			/** @var WireData $order */
			$order = $orderPage->get(PwCommerce::ORDER_FIELD_NAME);
			// -----------
			// ADD some fields we might require
			// order page ID + created date
			$order->id = $orderPage->id;
			$order->created = $orderPage->created;
		}
		return $order;
	}
}
