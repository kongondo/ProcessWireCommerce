<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Admin General Settings: Trait class to load all sub-classes for PWCommerce Admin General Settings.
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
	'TraitPWCommerceAdminRenderGeneralSettingsCurrency',
	'TraitPWCommerceAdminRenderGeneralSettingsDateTime',
	'TraitPWCommerceAdminRenderGeneralSettingsFiles',
	'TraitPWCommerceAdminRenderGeneralSettingsGeography',
	'TraitPWCommerceAdminRenderGeneralSettingsImages',
	'TraitPWCommerceAdminRenderGeneralSettingsMain',
	'TraitPWCommerceAdminRenderGeneralSettingsOrders',
	'TraitPWCommerceAdminRenderGeneralSettingsProducts',
	'TraitPWCommerceAdminRenderGeneralSettingsRuntimeChecks',
	'TraitPWCommerceAdminRenderGeneralSettingsShipping',
	'TraitPWCommerceAdminRenderGeneralSettingsStandards',
	'TraitPWCommerceAdminRenderGeneralSettingsUserInterface',
];

foreach ($traitsFiles as $traitFileName) {
	require_once __DIR__ . "/{$traitFileName}.php";
}


trait TraitPWCommerceAdminRenderGeneralSettings
{
	use TraitPWCommerceAdminRenderGeneralSettingsCurrency,
		TraitPWCommerceAdminRenderGeneralSettingsDateTime,
		TraitPWCommerceAdminRenderGeneralSettingsFiles,
		TraitPWCommerceAdminRenderGeneralSettingsGeography,
		TraitPWCommerceAdminRenderGeneralSettingsImages,
		TraitPWCommerceAdminRenderGeneralSettingsMain,
		TraitPWCommerceAdminRenderGeneralSettingsOrders,
		TraitPWCommerceAdminRenderGeneralSettingsProducts,
		TraitPWCommerceAdminRenderGeneralSettingsRuntimeChecks,
		TraitPWCommerceAdminRenderGeneralSettingsShipping,
		TraitPWCommerceAdminRenderGeneralSettingsStandards,
		TraitPWCommerceAdminRenderGeneralSettingsUserInterface;
	# NOTE TraitPWCommerceAdminRender WILL LOAD other sub-classes, e.g. 'TraitPWCommerceAdminRenderGeneralSettingsStandards'


	protected function getPWCommerceShopAdminPage() {
		$shop = $this->modules->getModuleID('ProcessPWCommerce');
		$shopPage = $this->pages->get("template=admin, process=$shop");
		return $shopPage;
	}
	protected function getPWCommerceShopAdminPageID() {
		$shopPage = $this->getPWCommerceShopAdminPage();
		$adminPageID = $shopPage->id;
		return $adminPageID;
	}
	protected function getPWCommerceShopadminURL() {
		$shopPage = $this->getPWCommerceShopAdminPage();
		$adminURL = $shopPage->url;
		return $adminURL;
	}


}
