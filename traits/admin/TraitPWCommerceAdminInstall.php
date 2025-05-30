<?php

namespace ProcessWire;

trait TraitPWCommerceAdminInstall
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INSTALL ~~~~~~~~~~~~~~~~~~

	private function installerOurModulePageAlreadyExists() {
		$isPageExist = false;
		// check if our process module's page already exists in Admin
		$parent = $this->wire('pages')->get($this->wire('config')->adminRootPageID);
		$page = $this->wire('pages')->get("parent=$parent, template=admin, include=all, name=" . PwCommerce::PAGE_NAME);
		if ($page->id && $page->id > 0) {
			$isPageExist = true;
		}

		return $isPageExist;
	}


	private function getConfigurePWCommerceStatus() {
		return $this->wire('sanitizer')->fieldName($this->wire('modules')->getConfig($this, 'pwcommerce_install_configuration_status'));
	}

	private function getConfigurePWCommerceHeadline() {
		$headline = $this->_('Configure PWCommerce');
		if ($this->getConfigurePWCommerceStatus() === PwCommerce::PWCOMMERCE_SECOND_STAGE_INSTALL_CONFIGURATION_STATUS) {
			$headline = $this->_('Modify PWCommerce Configuration');
		}
		// -----
		return $headline;
	}

	private function isConfigurePWCommerceComplete() {
		return $this->getConfigurePWCommerceStatus() !== PwCommerce::PWCOMMERCE_FIRST_STAGE_INSTALL_CONFIGURATION_STATUS;
	}

	private function setConfigurePWCommerceStatus($configurationStatus) {
		$data = ['pwcommerce_install_configuration_status' => $configurationStatus];
		$this->wire('modules')->saveConfig($this, $data);
	}


}