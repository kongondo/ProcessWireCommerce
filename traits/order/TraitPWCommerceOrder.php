<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Order: Trait class to load all sub-classes for PWCommerce Order.
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

// =========
// IMPORT TRAITS FILES
$traitsFiles = [
	'TraitPWCommerceCaptureOrder',
	'TraitPWCommerceCompleteOrder',
	'TraitPWCommerceCreateOrder',
	'TraitPWCommerceCustomerForm',
	'TraitPWCommerceMainOrder',
	'TraitPWCommerceOrderLineItem',
	'TraitPWCommerceOrderMessage',
	'TraitPWCommerceOrderPage',
	'TraitPWCommerceOrderProductVariants',
	'TraitPWCommerceOrderStatus',
	'TraitPWCommerceOrderTotals',
	'TraitPWCommerceOrderCache',
	'TraitPWCommerceParseCart',
	'TraitPWCommercePostProcessOrder',
	'TraitPWCommerceProcessOrderCustomer',
	'TraitPWCommerceProcessOrderDiscount',
	'TraitPWCommerceProcessOrderForm',
	'TraitPWCommerceProcessOrderInventory',
	'TraitPWCommerceSaveOrder',
	// TODO DELETE IF ACCESSED ELSEWHERE
	// 'TraitPWCommerceUtilitiesOrder',
	// 'TraitPWCommerceUtilitiesOrderLineItem',
	// 'TraitPWCommerceUtilitiesOrderStatus',
];

foreach ($traitsFiles as $traitFileName) {
	require_once __DIR__ . "/{$traitFileName}.php";
}


trait TraitPWCommerceOrder
{
	use TraitPWCommerceCaptureOrder, TraitPWCommerceCompleteOrder, TraitPWCommerceCreateOrder, TraitPWCommerceCustomerForm, TraitPWCommerceMainOrder, TraitPWCommerceOrderLineItem, TraitPWCommerceOrderMessage, TraitPWCommerceOrderPage, TraitPWCommerceOrderProductVariants, TraitPWCommerceOrderStatus, TraitPWCommerceOrderTotals, TraitPWCommerceOrderCache, TraitPWCommerceParseCart, TraitPWCommercePostProcessOrder, TraitPWCommerceProcessOrderCustomer, TraitPWCommerceProcessOrderDiscount, TraitPWCommerceProcessOrderForm, TraitPWCommerceProcessOrderInventory, TraitPWCommerceSaveOrder;

	// TODO DELETE UNUSED!
	protected $paymentClass;
	protected $paymentClassName;
	protected $paymentProviderID;
	protected $orderPage;
	private $isCustomForm;
	private $isUseCustomFormInputNames;
	protected $cart;

	/**
	 *  init Trait P W Commerce Order.
	 *
	 * @return mixed
	 */
	protected function _initTraitPWCommerceOrder()
	{

		if (empty($this->isInAdmin())) {
			if ($this->session->orderId) {
				// ==============
				// THIS WILL GET AND ASSIGN THE CURRENT ORDER PAGE TO $this->orderPage!
				$this->setOrderPage($this->session->orderId);
				// -------------------
				$this->orderPage->of(false);
				if (!$this->orderPage->id) {
					$this->session->remove('orderId');
				}

				// SET payment provider ID TO SESSION
				if ($this->session->paymentProviderID) {
					$this->setPaymentProvider($this->session->paymentProviderID);
				}
			}
		}
	}
}
