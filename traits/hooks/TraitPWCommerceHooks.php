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

	public function ___orderSavedHook(Page $orderPage, PageArray $orderLineItemsPages, WireArray $orderLineItems) {
		// This is here only for hooking

	}

	// TODO DEPRECATED!
	public function ___orderSaved(Page $orderPage, PageArray $orderLineItemsPages, WireArray $orderLineItems) {
		// This is here only for hooking
	}

	#### ORDER STATUSES ####

	public function ___manuallySetOrderStatusActionHook($orderPage, $statusName, $statusCode) {
	}

	#### PAYMENT GATEWAYS  ####

	public function ___isSuccessfulPaymentCaptureHook(object $response, array $options) {
		// This is here only for hooking
	}

	public function ___orderCompletedHook(Page $orderPage, WireArray $orderLineItems) {
		// This is here only for hooking
	}

}
