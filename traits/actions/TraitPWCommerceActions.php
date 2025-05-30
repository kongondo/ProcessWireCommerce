<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Actions: Trait class to load all sub-classes for PWCommerce Actions.
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
	'TraitPWCommerceActionsAddons',
	'TraitPWCommerceActionsCountry',
	'TraitPWCommerceActionsCustomer',
	'TraitPWCommerceActionsDiscount',
	'TraitPWCommerceActionsEdit',
	'TraitPWCommerceActionsGiftCard',
	'TraitPWCommerceActionsInventory',
	'TraitPWCommerceActionsInvoice',
	'TraitPWCommerceActionsNewItem',
	'TraitPWCommerceActionsNewItemExtra',
	'TraitPWCommerceActionsOrder',
	'TraitPWCommerceActionsOrderStatus',
	'TraitPWCommerceActionsPageManipulation',
	'TraitPWCommerceActionsPayment',
	'TraitPWCommerceActionsReports',
	'TraitPWCommerceActionsSettings',
	'TraitPWCommerceActionsVariants',
];

foreach ($traitsFiles as $traitFileName) {
	require_once __DIR__ . "/{$traitFileName}.php";
}



trait TraitPWCommerceActions
{

	use TraitPWCommerceActionsAddons, TraitPWCommerceActionsCountry, TraitPWCommerceActionsCustomer, TraitPWCommerceActionsDiscount, TraitPWCommerceActionsEdit, TraitPWCommerceActionsGiftCard, TraitPWCommerceActionsInventory, TraitPWCommerceActionsInvoice, TraitPWCommerceActionsNewItem, TraitPWCommerceActionsNewItemExtra, TraitPWCommerceActionsOrder, TraitPWCommerceActionsOrderStatus, TraitPWCommerceActionsPageManipulation, TraitPWCommerceActionsPayment, TraitPWCommerceActionsReports, TraitPWCommerceActionsSettings, TraitPWCommerceActionsVariants;

}