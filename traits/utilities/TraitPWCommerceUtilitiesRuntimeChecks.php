<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesRuntimeChecks
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ RUNTIME CHECKS  ~~~~~~~~~~~~~~~~~~

	/**
	 * Is Optional Feature Installed.
	 *
	 * @param mixed $feature
	 * @return bool
	 */
	public function isOptionalFeatureInstalled($feature) {
		// ---------
		// get list of installed features
		/** @var array $installedOptionalFeatures */
		$installedOptionalFeatures = $this->getPWCommerceInstalledOptionalFeatures(PwCommerce::PWCOMMERCE_PROCESS_MODULE);
		// -----
		return in_array($feature, $installedOptionalFeatures);
	}

	/**
	 * Is Other Optional Setting Installed.
	 *
	 * @param mixed $otherOptionalSetting
	 * @return bool
	 */
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
	 * @param mixed $string
	 * @return bool
	 */
	public function isGenericNoImageFound($string) {
		$string = strval($string);
		return strpos($string, 'no-image-found.svg') !== false;
	}

	/**
	 * Is Title Language Field.
	 *
	 * @return bool
	 */
	public function isTitleLanguageField() {
		$isTitleLanguageField = false;
		$titleField = $this->wire('fields')->get('title');
		if (!empty($titleField)) {
			$isTitleLanguageField = $titleField->type instanceof FieldtypePageTitleLanguage;
		}
		return $isTitleLanguageField;
	}

	/**
	 * Is Description Language Field.
	 *
	 * @return bool
	 */
	public function isDescriptionLanguageField() {
		// TODO?
	}

	# ~~~~~~~~~~~~~~~~~

	/**
	 * Check if we are in ProcessWire backend or not.
	 *
	 * @return bool
	 */
	private function isInAdmin() {
		return strpos($_SERVER['REQUEST_URI'], wire('pages')->get(wire('config')->adminRootPageID)->url) !== false;
	}
}