<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesAddons
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ADDONS ~~~~~~~~~~~~~~~~~~

	/**
	 * Get the parent page for custom addons.
	 *
	 * @return mixed
	 */
	public function getCustomAddonsParentPage() {
		// get the addons settings page
		$page = $this->wire('pages')->get("template=" . PwCommerce::PWCOMMERCE_TEMPLATE_NAME . ",name=addons");
		// ---

		return $page;
	}

	/**
	 * Get the parent page for custom addons.
	 *
	 * @return mixed
	 */
	public function getCustomAddonsSettingsPage() {
		// get the addons parent page
		/** @var Page $addonsParentPage */
		$addonsParentPage = $this->getCustomAddonsParentPage();
		// ---
		// we didn't get the page; abort
		// TODO: meaningful error? e.g. addons settings page not found?
		if (empty($addonsParentPage->id)) {
			return null;
		}
		// get the addons settings page
		// it is child of this page
		$addonsSettingsPageName = 'addons-settings';
		$addonsSettingsPage = $this->get("parent={$addonsParentPage},name={$addonsSettingsPageName}");
		// ----------------
		return $addonsSettingsPage;
	}

	// @note: updated!
	/**
	 * Get all active addons settings.
	 *
	 * @return mixed
	 */
	public function getAddonsSettings() {
		// get the addons parent page
		/** @var Page $addonsParentPage */
		$addonsParentPage = $this->getCustomAddonsParentPage();

		// we didn't get the page; abort
		// TODO: meaningful error? e.g. addons settings page not found?
		if (empty($addonsParentPage->id)) {
			return null;
		}
		// get the addons settings page
		// it is child of this page
		$addonsSettingsPageName = 'addons-settings';
		$settingsField = 'pwcommerce_settings';
		$addonsSettingsJSON = $this->getRaw("parent={$addonsParentPage},name={$addonsSettingsPageName}", $settingsField);


		// GET CURRENT ADDONS SETTINGS
		$addonsSettings = [];
		if (!empty($addonsSettingsJSON)) {
			$addonsSettings = json_decode($addonsSettingsJSON, true);
		}

		// --------

		return $addonsSettings;
	}

	/**
	 * Returns array of names of PWCommerce core payment addons.
	 *
	 * @return mixed
	 */
	public function getNamesOfCorePaymentAddons() {
		return [
			'invoice',
			'paypal',
			'stripe'
		];
	}

	/**
	 * Get Non Core Payment Providers I Ds.
	 *
	 * @return mixed
	 */
	public function getNonCorePaymentProvidersIDs() {
		/** @var array $namesOfCorePaymentAddons */
		$namesOfCorePaymentAddons = $this->getNamesOfCorePaymentAddons();
		$namesOfCorePaymentAddonsSelector = implode("|", $namesOfCorePaymentAddons);
		$nonCorePaymentAddonsIDs = $this->wire('pages')->findRaw("check_access=0,template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",name!={$namesOfCorePaymentAddonsSelector}", 'id');

		// @note: we want the integers (values are strings), so just get them easily like this
		$nonCorePaymentAddonsIDs = array_keys($nonCorePaymentAddonsIDs);
		return $nonCorePaymentAddonsIDs;
	}

	/**
	 * Get Non Core Payment Providers Names.
	 *
	 * @return mixed
	 */
	public function getNonCorePaymentProvidersNames() {
		/** @var array $namesOfCorePaymentAddons */
		$namesOfCorePaymentAddons = $this->getNamesOfCorePaymentAddons();
		$namesOfCorePaymentAddonsSelector = implode("|", $namesOfCorePaymentAddons);
		$nonCorePaymentAddonsNames = $this->wire('pages')->findRaw("check_access=0,template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",name!={$namesOfCorePaymentAddonsSelector}", 'name');

		return $nonCorePaymentAddonsNames;
	}

	/**
	 * Get Non Payment Custom Addons I Ds.
	 *
	 * @return mixed
	 */
	public function getNonPaymentCustomAddonsIDs() {
		$addonsParent = $this->getCustomAddonsParentPage();
		$nonPaymentCustomAddonsIDs = $this->wire('pages')->findRaw("check_access=0,template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",parent={$addonsParent}", 'id');

		// @note: we want the integers (values are strings), so just get them easily like this
		$nonPaymentCustomAddonsIDs = array_keys($nonPaymentCustomAddonsIDs);
		return $nonPaymentCustomAddonsIDs;
	}

	/**
	 * Get Non Payment Custom Addons Names.
	 *
	 * @return mixed
	 */
	public function getNonPaymentCustomAddonsNames() {
		$addonsParent = $this->getCustomAddonsParentPage();
		$addonsSettingsPageName = 'addons-settings';
		// -----------
		$nonPaymentCustomAddonsNames = $this->wire('pages')->findRaw("check_access=0,template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",parent={$addonsParent},name!={$addonsSettingsPageName}", 'name');

		// @note: we want the integers (values are strings), so just get them easily like this
		return $nonPaymentCustomAddonsNames;
	}

	// TODO DELETE BELOW AS NEEDED

	/**
	 * Get Non Core Payment Provider Class Name By I D.
	 *
	 * @param int $nonCorePaymentProviderID
	 * @return mixed
	 */
	public function getNonCorePaymentProviderClassNameByID($nonCorePaymentProviderID) {
		$nonCorePaymentAddonClassName = null;
		$addonsSettings = $this->getAddonsSettings();

		if (!empty($addonsSettings)) {
			// GET NON-CORE PAYMENT ADDON SETTINGS BY ID
			$nonCorePaymentAddonSettings = array_filter($addonsSettings, fn($item) => $item['pwcommerce_addon_page_id'] === $nonCorePaymentProviderID && $item['pwcommerce_addon_type'] === 'payment');
			if (!empty($nonCorePaymentAddonSettings)) {
				$nonCorePaymentAddonClassName = array_keys($nonCorePaymentAddonSettings)[0];
			}
		}
		// --------
		return $nonCorePaymentAddonClassName;
	}

	/**
	 * Get addon class by a given addon property.
	 *
	 * @param mixed $addonProperty
	 * @param mixed $settingProperty
	 * @param string $sanitizeType
	 * @return mixed
	 */
	private function getAddonClassByProperty($addonProperty, $settingProperty, $sanitizeType = 'text') {
		// --------
		$addonClass = null;
		$addonProperty = $this->sanitizeAddonProperty($addonProperty, $sanitizeType);
		if (!empty($addonProperty)) {
			$addonSettings = $this->getCustomAddonSettingsByProperty($settingProperty, $addonProperty);
			if (!empty($addonSettings['pwcommerce_addon_class_name'])) {
				$addonClass = $this->getAddonClass($addonSettings);
			}
		}
		// ------
		return $addonClass;
	}

	/**
	 * Get the Class for a given addon by its name.
	 *
	 * @param mixed $addonName
	 * @return mixed
	 */
	public function getAddonClassByName($addonName) {
		$property = 'pwcommerce_addon_view_url';
		$addonClass = $this->getAddonClassByProperty($addonName, $property, 'page_name');
		// ------
		return $addonClass;
	}

	/**
	 * Get the Class for a given addon by its title.
	 *
	 * @param mixed $addonTitle
	 * @return mixed
	 */
	public function getAddonClassByTitle($addonTitle) {
		$property = 'pwcommerce_addon_title';
		$addonClass = $this->getAddonClassByProperty($addonTitle, $property);
		// ------
		return $addonClass;
	}

	/**
	 * Get the Class for a given addon by its Class name.
	 *
	 * @param mixed $addonClassName
	 * @return mixed
	 */
	public function getAddonClassByClassName($addonClassName) {
		$property = 'pwcommerce_addon_class_name';
		$addonClass = $this->getAddonClassByProperty($addonClassName, $property, 'class_name');
		// ------
		return $addonClass;
	}

	/**
	 * Get the Class for a given addon by its page ID.
	 *
	 * @param int $addonPageID
	 * @return mixed
	 */
	public function getAddonClassByPageID($addonPageID) {
		$property = 'pwcommerce_addon_page_id';
		$addonClass = $this->getAddonClassByProperty($addonPageID, $property, 'page_id');
		// ------
		return $addonClass;
	}

	/**
	 * Get addon configurations by a given addon property.
	 *
	 * @param mixed $addonProperty
	 * @param mixed $settingProperty
	 * @param string $sanitizeType
	 * @return mixed
	 */
	private function getAddonConfigurationsByProperty($addonProperty, $settingProperty, $sanitizeType = 'text') {
		$addonSettings = [];
		$addonProperty = $this->sanitizeAddonProperty($addonProperty, $sanitizeType);
		if (!empty($addonProperty)) {
			$settings = $this->getCustomAddonSettingsByProperty($settingProperty, $addonProperty);
			if (!empty($settings['pwcommerce_addon_is_addon_configurable'])) {
				// addon must be configurable!
				$addonSettings = $settings;
			}
		}
		// ------
		return $addonSettings;
	}

	/**
	 * Get the configurations for a given addon by its name.
	 *
	 * @param mixed $addonName
	 * @return mixed
	 */
	public function getAddonConfigurationsByName($addonName) {
		$property = 'pwcommerce_addon_view_url';
		$addonConfigurations = $this->getAddonConfigurationsByProperty($addonName, $property, 'page_name');
		// ------
		return $addonConfigurations;
	}

	/**
	 * Get the configurations for a given addon by its Class name.
	 *
	 * @param mixed $addonTitle
	 * @return mixed
	 */
	public function getAddonConfigurationsByTitle($addonTitle) {
		$property = 'pwcommerce_addon_title';
		$addonConfigurations = $this->getAddonConfigurationsByProperty($addonTitle, $property);
		// ------
		return $addonConfigurations;
	}

	/**
	 * Get the configurations for a given addon by its Class name.
	 *
	 * @param mixed $addonClassName
	 * @return mixed
	 */
	public function getAddonConfigurationsByClassName($addonClassName) {
		$property = 'pwcommerce_addon_class_name';
		$addonConfigurations = $this->getAddonConfigurationsByProperty($addonClassName, $property, 'class_name');
		// ------
		return $addonConfigurations;
	}

	/**
	 * Get the configurations for a given addon by its Class name.
	 *
	 * @param int $addonPageID
	 * @return mixed
	 */
	public function getAddonConfigurationsByPageID($addonPageID) {
		$property = 'pwcommerce_addon_page_id';
		$addonConfigurations = $this->getAddonConfigurationsByProperty($addonPageID, $property, 'page_id');
		// ------
		return $addonConfigurations;
	}

	/**
	 * Get a single custom addon's settings by matching to a property.
	 *
	 * @param mixed $property
	 * @param mixed $match
	 * @return mixed
	 */
	private function getCustomAddonSettingsByProperty($property, $match) {
		$addonSettings = [];
		$allAddonsSettings = $this->getAddonsSettings();
		if (!empty($allAddonsSettings)) {
			$addonSettings = array_filter($allAddonsSettings, fn($item) => !empty ($item[$property]) && $item[$property] === $match && $item['pwcommerce_addon_type'] === 'custom');
			// we expect only one; grab it
			$addonSettings = reset($addonSettings);
		}
		// -------
		return $addonSettings;
	}

	/**
	 * Get Addon Class.
	 *
	 * @param mixed $addonSettings
	 * @return mixed
	 */
	private function getAddonClass($addonSettings) {
		$addonClassName = $addonSettings['pwcommerce_addon_class_name'];
		$files = $this->wire('files');
		// TODO NEED TO SET AS CONSTANT MAYBE? HERE OR IN UTILITIES?
		$addonClassFilePath = $this->getAddonClassFilePath($addonClassName);
		if ($files->exists($addonClassFilePath, 'readable')) {
			require_once $addonClassFilePath;
			$class = "\ProcessWire\\" . $addonClassName;
			// TODO CONSTRUCTOR TRICKY SINCE CANNOT KNOW BEFOREHAND WHAT ARGUMENTS NEEDED! SHOULD WE ASK ADDONS NOT TO INCLUDE REQUIRED PARAMS?
			// $addonClass = new $class([]);
			/** @var object $addonClass */
			$addonClass = new $class();
			$id = $addonSettings['pwcommerce_addon_page_id'];
			$addonPage = $this->get("id={$id}");
			$addonSettingsFieldName = 'pwcommerce_settings';
			$addonClass->setAddonPage($addonPage, $addonSettingsFieldName);

		}
		;
		// ------
		return $addonClass;
	}

	/**
	 * Return path to PWCommerce addons folder.
	 *
	 * @return mixed
	 */
	private function getAddonsPath() {
		$addonsPath = $this->wire('config')->paths->templates . "pwcommerce/addons/";
		return $addonsPath;
	}

	/**
	 * Return path to a given addon class file.
	 *
	 * @param mixed $addonClassName
	 * @return mixed
	 */
	private function getAddonClassFilePath($addonClassName) {
		$addonsPath = $this->getAddonsPath();
		$addonClassFilePath = $addonsPath . "{$addonClassName}/{$addonClassName}.php";
		return $addonClassFilePath;
	}

	/**
	 * Get Addon Page.
	 *
	 * @param int $titleOrNameorID
	 * @return mixed
	 */
	public function getAddonPage($titleOrNameorID) {
		// first, check if property to match with is an int or text
		if (ctype_digit($titleOrNameorID)) {
			// match by ID
			$property = 'pwcommerce_addon_page_id';
			$match = (int) $titleOrNameorID;
		} else {
			$property = 'pwcommerce_addon_view_url';
			// sanitize as page name and use that to find
			$match = $this->sanitizeAddonProperty($titleOrNameorID, 'page_name');
		}
		// +++++++++++++++
		$addonSettings = $this->getCustomAddonSettingsByProperty($property, $match);
		// +++++++++++++++
		// did we get configs?
		if (!empty($addonSettings['pwcommerce_addon_page_id'])) {
			// yes we did; grab the page
			$pageID = (int) $addonSettings['pwcommerce_addon_page_id'];
			$addonPage = $this->get("id={$pageID}");
		} else {
			// no configs, so return NullPage
			$addonPage = new NullPage();
		}

		// ------
		return $addonPage;
	}

	/**
	 * Sanitize Addon Property.
	 *
	 * @param mixed $addonProperty
	 * @param mixed $sanitizeType
	 * @return mixed
	 */
	private function sanitizeAddonProperty($addonProperty, $sanitizeType) {
		$sanitizer = $this->wire('sanitizer');
		if ($sanitizeType === 'class_name') {
			// sanitize as ClassName
			$addonProperty = $sanitizer->pascalCase($addonProperty);
		} else if ($sanitizeType === 'page_name') {
			// sanitizer as page-name
			$addonProperty = $sanitizer->pageName($addonProperty);
		} else if ($sanitizeType === 'page_id') {
			// sanitizer as int
			$addonProperty = (int) $addonProperty;
		} else {
			// sanitizer as text
			$addonProperty = $sanitizer->text($addonProperty);
		}
		// -----
		return $addonProperty;
	}

	/**
	 * Is Valid Addon View U R L.
	 *
	 * @param mixed $addonRenderViewURL
	 * @return bool
	 */
	public function isValidAddonViewURL($addonRenderViewURL) {
		// view URL must contain at least one letter
		$isValidAddonViewURL = !empty(preg_match('/[a-zA-Z]/', $addonRenderViewURL));

		return $isValidAddonViewURL;
	}

}