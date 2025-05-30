<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Admin: Trait class to load all sub-classes for PWCommerce Admin.
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
	'TraitPWCommerceAdminAjax',
	'TraitPWCommerceAdminContext',
	'TraitPWCommerceAdminExecute',
	'TraitPWCommerceAdminForm',
	'TraitPWCommerceAdminInstall',
	'TraitPWCommerceAdminLister',
	'TraitPWCommerceAdminNavigation',
	'TraitPWCommerceAdminPagination',
	'TraitPWCommerceAdminQuickFilters',
	'TraitPWCommerceAdminRender',
	'TraitPWCommerceAdminRuntimeChecks'
];

foreach ($traitsFiles as $traitFileName) {
	require_once __DIR__ . "/{$traitFileName}.php";
}


trait TraitPWCommerceAdmin
{
	use TraitPWCommerceAdminAjax, TraitPWCommerceAdminContext, TraitPWCommerceAdminExecute, TraitPWCommerceAdminForm, TraitPWCommerceAdminInstall, TraitPWCommerceAdminLister, TraitPWCommerceAdminNavigation, TraitPWCommerceAdminPagination, TraitPWCommerceAdminQuickFilters, TraitPWCommerceAdminRender, TraitPWCommerceAdminRuntimeChecks;
	# NOTE TraitPWCommerceAdminRender WILL LOAD other sub-classes, e.g. 'TraitPWCommerceAdminRenderGeneralSettingsStandards'

	public $adminURL;
	public $adminPageID;

	protected function getPWCommerceShopAdminPage() {
		$processPWCommerceModuleID = $this->modules->getModuleID('ProcessPWCommerce');
		$shopPage = $this->pages->get("template=admin, process=$processPWCommerceModuleID");
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

	protected function _initTraitPWCommerceAdmin() {
		$adminPage = $this->getPWCommerceShopAdminPage();
		$this->adminURL = $adminPage->url;
		$this->adminPageID = $adminPage->id;
	}
}
