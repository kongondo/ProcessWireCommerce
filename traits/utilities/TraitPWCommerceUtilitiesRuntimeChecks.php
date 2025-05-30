<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesRuntimeChecks
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ RUNTIME CHECKS  ~~~~~~~~~~~~~~~~~~

	public function isOptionalFeatureInstalled($feature) {
		// ---------
		// get list of installed features
		/** @var array $installedOptionalFeatures */
		$installedOptionalFeatures = $this->getPWCommerceInstalledOptionalFeatures(PwCommerce::PWCOMMERCE_PROCESS_MODULE);
		// -----
		return in_array($feature, $installedOptionalFeatures);
	}

	public function isOtherOptionalSettingInstalled($otherOptionalSetting) {
		// ---------
		// get list of OTHER SETTINGS/installed features
		/** @var array $installedOptionalFeatures */
		$otherOptionalSettings = $this->getPWCommerceInstalledOtherOptionalSettings(PwCommerce::PWCOMMERCE_PROCESS_MODULE);
		// -----
		return !empty($otherOptionalSettings[$otherOptionalSetting]);
	}

	/**
	 * Checks if given string contains name of the generic no image found image.
	 *
	 * @param string $string String to check.
	 * @return boolean Whether the string contains the generic no image found name or not.
	 */
	public function isGenericNoImageFound($string) {
		$string = strval($string);
		return strpos($string, 'no-image-found.svg') !== false;
	}

	public function isTitleLanguageField() {
		$isTitleLanguageField = false;
		$titleField = $this->wire('fields')->get('title');
		if (!empty($titleField)) {
			$isTitleLanguageField = $titleField->type instanceof FieldtypePageTitleLanguage;
		}
		return $isTitleLanguageField;
	}

	public function isDescriptionLanguageField() {
		// TODO?
	}

	# ~~~~~~~~~~~~~~~~~

	/**
	 * Check if we are in ProcessWire backend or not.
	 *
	 * @access private
	 * @return boolean If in admin or not.
	 */
	private function isInAdmin() {
		return strpos($_SERVER['REQUEST_URI'], wire('pages')->get(wire('config')->adminRootPageID)->url) !== false;
	}
}