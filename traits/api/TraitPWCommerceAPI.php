<?php

namespace ProcessWire;

/**
 * Trait PWCommerce API: Trait class to load all sub-classes for PWCommerce API.
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
	'TraitPWCommerceFinder',
];

foreach ($traitsFiles as $traitFileName) {
	require_once __DIR__ . "/{$traitFileName}.php";
}



trait TraitPWCommerceAPI
{

	use TraitPWCommerceFinder;

}