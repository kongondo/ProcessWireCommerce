<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRuntimeChecks
{



	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ RUNTIME CHECKS  ~~~~~~~~~~~~~~~~~~


	protected function isSuperUser() {

		return $this->wire('user')->isSuperuser();
	}



	protected function checkPWCommerceConfiguration() {
		// DON'T RUN CHECK DURING MODULE UNINSTALL!

		// if (strpos($this->adminURL, 'module') !== false) {
		// NOTE: WE NOW DO THIS IN ProcessPwCommerce::init but leaving this here, just in case
		if (str_contains($this->wire('input')->url(), 'module')) {
			return;
		}

		// ----------------
		// IF PWCOMMERCE IS NOT FULLY CONFIGURED
		if (empty($this->isConfigurePWCommerceComplete)) {

			// IF SUPER USER
			$configurePWCommerceContext = 'configure-pwcommerce';
			if ($this->isSuperUser()) {
				// IF CONTEXT IS NOT CONFIGURE INSTALL && NOT AN AJAX CALL REDIRECT TO CONFIGURE INSTALL
				// TODO @note: not using ajax for installer at the moment but we leave here for fututure if needed
				// TODO: should this be an || condition?
				if ($this->context !== $configurePWCommerceContext && empty($this->wire('config')->ajax)) {
					// HEAD TO PWCOMMERCE CONFIG PAGE
					$this->session->redirect($this->adminURL . $configurePWCommerceContext . "/");
				}
			} else {
				// SEND NON SUPER USERS TO ADMIN PAGE
				$this->warning($this->_('Shop needs to be fully configured before you can use it.'));
				// $this->session->redirect($this->wire('config')->urls->admin);
			}
		}
	}

	/**
	 * Check if an optional feature's execute page is viewable.
	 *
	 * If the optional feature is installed, it is viewable.
	 * Otherwise, we redirect to shop's home.
	 * @return void
	 */
	private function checkOptionalFeaturePageViewable() {
		if (empty($this->isOptionalFeatureInstalledAdminRuntimeCheck($this->context))) {
			// IF OPTIONAL FEATURE NOT INSTALLED AND ITS PAGE (executeOptionalFeature) is accessed manually
			// redirect to shop landing page
			$this->session->redirect($this->adminURL);
		}
	}

	/**
	 * Check if a given optional feature for a given context is installed.
	 *
	 * @access private
	 * @param string $context Optional feature context to check if installed.
	 * @return bool $isOptionalFeatureInstalled True if installed else false.
	 */
	private function isOptionalFeatureInstalledAdminRuntimeCheck($context) {
		$isOptionalFeatureInstalled = false;
		// get list of key => value pairs of ALL optional features => context
		$optionalFeatures = $this->pwcommerce->getPWCommerceOptionalFeatures();
		// get name of optional feature for the current optional feature context
		// it will be a KEY in the array $optionalFeatures
		$optionalFeatureNameForContext = array_search($context, $optionalFeatures);

		// check if optional feature is installed
		if (in_array($optionalFeatureNameForContext, $this->installedOptionalFeatures)) {
			$isOptionalFeatureInstalled = true;
		}
		// ---------------
		return $isOptionalFeatureInstalled;
	}

	/**
	 * Is current context an optional feature.
	 *
	 * @access protected
	 * @return bool Whether current context is an optional feature or not
	 */
	protected function isCurrentContextAnOptionalFeature() {
		return $this->isGivenContextAnOptionalFeature($this->context);
	}

	/**
	 * Is given context an optional feature.
	 *
	 * @access private
	 * @param string $context Context to check if is an optional feature.
	 * @return bool Whether given context is an optional feature or not.
	 */
	private function isGivenContextAnOptionalFeature($context) {
		return in_array($context, $this->pwcommerce->getPWCommerceOptionalFeatures());
	}

	/**
	 * Check if all required general shop settings have been completed.
	 *
	 * Check is done only after second install is done.
	 * Settings required include shop currency, email, address and image and file allowed extensions.
	 *
	 * @access protected
	 * @return void
	 */
	protected function checkShopRequiredGeneralSettingsIsComplete() {
		if ($this->getConfigurePWCommerceStatus() === PwCommerce::PWCOMMERCE_SECOND_STAGE_INSTALL_CONFIGURATION_STATUS) {
			if (empty($this->pwcommerce->isAllRequiredGeneralSettingsSetUp())) {
				$this->warning($this->_('Required Shop Settings have not yet been completed. You will not be able to operate the shop until this is done. Please head over to General Settings to complete all required fields including setting a shop currency, email address and allowed image and file extensions.'));
			}
		}
	}

}