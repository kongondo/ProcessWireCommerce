<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Hooks: Trait class for PWCommerce Hooks.
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

trait TraitPWCommerceHooks
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ HOOKS  ~~~~~~~~~~~~~~~~~~

	/**
	 * Hookable methods used in various PWCommerce classes.
	 * These are methods that are purely for hooking.
	 * They are not the usual hookable methods that actually do something.
	 * Typically we call these 'purely for hooking' methods AFTER operations.
	 * We save them in one TRAIT FILE for ease of maintenance.
	 * We name them consistently!
	 * Arranged alphabetically.
	 *
	 */

	#### ORDERS ####

	/**
	 *    order Saved Hook.
	 *
	 * @param Page $orderPage
	 * @param PageArray $orderLineItemsPages
	 * @param WireArray $orderLineItems
	 * @return mixed
	 */
	public function ___orderSavedHook(Page $orderPage, PageArray $orderLineItemsPages, WireArray $orderLineItems) {
		// This is here only for hooking

	}

	// TODO DEPRECATED!
	/**
	 *    order Saved.
	 *
	 * @param Page $orderPage
	 * @param PageArray $orderLineItemsPages
	 * @param WireArray $orderLineItems
	 * @return mixed
	 */
	public function ___orderSaved(Page $orderPage, PageArray $orderLineItemsPages, WireArray $orderLineItems) {
		// This is here only for hooking
	}

	#### ORDER STATUSES ####

	/**
	 *    manually Set Order Status Action Hook.
	 *
	 * @param mixed $orderPage
	 * @param mixed $statusName
	 * @param mixed $statusCode
	 * @return mixed
	 */
	public function ___manuallySetOrderStatusActionHook($orderPage, $statusName, $statusCode) {
	}

	#### PAYMENT GATEWAYS  ####

	/**
	 *    is Successful Payment Capture Hook.
	 *
	 * @param object $response
	 * @param array $options
	 * @return mixed
	 */
	public function ___isSuccessfulPaymentCaptureHook(object $response, array $options) {
		// This is here only for hooking
	}

	/**
	 *    order Completed Hook.
	 *
	 * @param Page $orderPage
	 * @param WireArray $orderLineItems
	 * @return mixed
	 */
	public function ___orderCompletedHook(Page $orderPage, WireArray $orderLineItems) {
		// This is here only for hooking
	}

}
