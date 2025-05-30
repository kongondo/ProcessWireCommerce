<?php

namespace ProcessWire;

trait TraitPWCommerceActionsAddons
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ADDONS ~~~~~~~~~~~~~~~~~~

	private function actionAddons($input) {

		$sanitizer = $this->wire('sanitizer');
		$items = $this->items;

		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!
		if (empty($items)) {
			return null;
		}

		// TODO CHECK IF PAYMENT -> ACTIVATE -> CREATE PAYMENT GATEWAY PAGE; INACTIVE -> DELTE PAYMENT GATEWAY PAGE + SAVE THE ID OF NEW PAGE TO ADDONS SETTINGS
		// TODO - ABOVE MEANS TO ADD AND CHECK OTHER $input for addon type (to determine if creating payment gateway page) AND TITLE to give to created page. Other?
		// TODO FOR ADDONS SETTINGS OBJECTS SHOULD BE Structured as below
		// TODO CONSIDER ADDON PAGES, VIA THEIR SCHEMA? e.g. is_create_page? In that case, they would have to live under settings > addons > my_addon, etc + use the template 'pwcommerce' maybe? so that can use 'pwcommerce_settings' textfield for their settings
		//
		// addons settings 'addon object' structure
		// 'AddonClassName => [
		// 'id' => xxx, // only applicable to type 'payment' for now
		// 'type' => 'xxx',
		// // TODO FOR NOW 'LOCKED' STATUS NOT IN USE; NOT SURE ABOUT ITS NEED
		// 'is_locked' => true/false
		// ]
		// @note:
		// - don't need title to be saved, ok?
		// - the key 'AddonClassName' ok? or store in 'class_name'??
		// $array = [
		// 'AddonClassName1 =>  [], // addon 1
		// 'AddonClassName2 => [], // addon 2
		// 'AddonClassName3 => [], // addon 3, etc
		// ]
		//

		// TODO - AMEND FOR ADDONS AS NO PAGES!
		//------------------
		// GOOD TO GO


		// we only allow these addon types
		/** @var array $allowedAddonTypes */
		$allowedAddonTypes = $this->getAllowedAddonTypes();

		// for adding/updating ACITIVATED ADDONS IN ADDONS SETTINGS
		$updateAddons = [];
		// for remoal of INACTIVATED ADDONS FROM ADDONS SETTINGS
		$removeAddons = [];

		$i = 0;
		// action each item
		foreach ($items as $addonClassName) {
			// TODO NOT IN USE FOR NOW
			// skip if item is locked
			// if ($page->isLocked()) {
			// 	continue;
			// }
			$addonType = $sanitizer->fieldName($input->{"pwcommerce_addon_type_{$addonClassName}"});

			// --------
			$addonType = $sanitizer->option($addonType, $allowedAddonTypes);

			// NO ALLOWED ADDON TYPE: SKIP
			if (empty($addonType)) {
				continue;
			}

			// NO TITLE: SKIP
			$addonTitle = $sanitizer->text($input->{"pwcommerce_addon_title_{$addonClassName}"});
			if (empty($addonTitle)) {

				continue;
			}

			// CUSTOM ADDON view, ajax and configurable
			$addonViewURL = null;
			if ($addonType === 'custom') {
				// NO VIEW URL FOR ADDON OF TYPE 'custom'
				$addonViewURL = $sanitizer->pageName($input->{"pwcommerce_addon_view_url_{$addonClassName}"});
				if (empty($this->pwcommerce->isValidAddonViewURL($addonViewURL))) {
					continue;
				}
			}

			##########
			// ++++++++++++++
			if ($addonType === 'payment') {
				// CREATE OR DELETE PAYMENT GATEWAY
				// dependent on activate vs inactivate
				$addonValues = $this->processPaymentProviderAddonPage($addonClassName, $input);
			} else {
				// CREATE OR DELETE CUSTOM ADDON PAGE
				// dependent on activate vs inactivate
				$addonValues = $this->processNonPaymentCustomAddonPage($addonClassName, $input);
			}

			// +++++++++++++++
			// process addon values for saving OR removing from settings
			if ($this->action === 'activate') {
				// GATHER ADDON VALUES FOR SAVING/UPDATING
				$updateAddons[$addonClassName] = $addonValues;
			} else {
				// REMOVE THESE ADDONS FROM SETTINGS
				$removeAddons[] = $addonClassName;
			}

			//-------------
			$i++;
		}

		// ============

		# UPDATE ADDONS SETTINGS
		if ($this->action === 'activate') {
			$this->processAddonsSettings($updateAddons);
		} else {
			$this->processAddonsSettings($removeAddons);
		}

		# ***************

		// --------------------
		// prepare messages
		if ($this->action === 'activate') {
			// activated
			$notice = sprintf(_n("Activated %d item.", "Activated %d items.", $i), $i);
		} elseif ($this->action === 'deactivate') {
			// deactivated
			$notice = sprintf(_n("Deactivated %d item.", "Deactivated %d items.", $i), $i);
		}

		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO: WILL DETERMINE BASED ON HOW MANY ITEMS WE COULD ACTION
		];

		//-------
		return $result;
	}

	private function getAllowedAddonTypes() {
		// TODO ADD MORE AS NEEDED!
		return [
			'custom',
			'payment'
		];
	}

	private function processPaymentProviderAddonPage($addonClassName, $input) {

		$pages = $this->wire('pages');
		$sanitizer = $this->wire('sanitizer');
		// ----------
		$addonValues = [];
		$addonID = (int) $input->{"pwcommerce_addon_id_{$addonClassName}"};
		// ----------

		if ($this->action === 'activate') {
			// ACTIVATE
			$addonTitle = $sanitizer->text($input->{"pwcommerce_addon_title_{$addonClassName}"});
			$name = $this->wire('sanitizer')->pageName($addonTitle, true);

			// fist check if addons page exists
			// get the addons settings page
			$page = $pages->get("template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",name={$name}");

			#########################################
			// CREATE ADDON PAGE IF IT DOESN'T EXIST
			if (empty($page->id)) {
				// ACTION: ACTIVATE
				# ++++++++++
				$languages = $this->wire('languages');
				// PAGE DOES NO EXIST: CREATE IT
				$parent = $pages->get("template=" . PwCommerce::PAYMENT_PROVIDERS_TEMPLATE_NAME);

				$page = new Page();
				$page->parent = $parent;
				$page->template = PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME;
				$page->title = $addonTitle;
				$page->name = $name;

				// set payment addon page as active in other languages
				if ($languages) {
					foreach ($languages as $language) {
						// skip default language as already set above
						if ($language->name == 'default') {
							continue;
						}
						$page->set("status$language", 1);
					}
				}
				// -------------
				// save
				$page->save();
			}
			// for saving page ID if addon getting activated
			$addonID = $page->id;
			// ========
			// PREPARE ADDON SETTINGS FOR SAVING TO ADDONS SETTINGS PAGE
			// @NOTE: WE ARE NOT SAVING WITH THE ADDON PAGE ITSELF!
			// so that the settings are not accidentally deleted by addon-level custom settings
			// or so that we don't accidentally mess up with those custom/user addons settings!
			// ----------
			// we need to save the custom addon ClassNamee on first save!

			############
			$addonType = $sanitizer->fieldName($input->{"pwcommerce_addon_type_{$addonClassName}"});

			// --------
			$addonType = $sanitizer->option($addonType, $this->getAllowedAddonTypes());
			###############

			// =============
			// PREPARE INCOMING SETTINGS
			// @note: we prefix with 'pwcommerce'!
			$addonValues = [
				'pwcommerce_addon_class_name' => $addonClassName,
				'pwcommerce_addon_page_id' => $addonID,
				'pwcommerce_addon_type' => $addonType,
				'pwcommerce_addon_title' => $addonTitle,
				// TODO FOR NOW 'LOCKED' STATUS NOT IN USE; NOT SURE ABOUT ITS NEED
				// 'is_locked' => true / false
			];
			// -----

		} else {
			// ACTION: INACTIVATE
			// @note: if here, it means we have AN ADDON ID!
			// this is because a page was created when addon was activated (theoretically)
			// GET THE PAGE to delete
			// @note: using template name just to be doubly sure
			$page = $pages->get("template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",id={$addonID}");

			# ++++++++++
			// DELETE ADDON PAGE IF IT DOES EXIST
			if (!empty($page->id)) {
				$pages->delete($page);
			}
		}

		// ------------
		return $addonValues;
	}

	private function actionDeleteAllNoneCorePaymentAddons() {
		// TODO THIS NOW CHANGES -> SINCE ADDONS MAIN/PARENT PAGE DOES NOT NOW HAVE OWN SETTINGS, AND SINCE NON-PAYMENT ADDONS NOW HAVE OWN PAGES, IT MEANS WE HAVE NOWHERE CENTRAL TO STORE ALL ADDONS SETTINGS. HENCE, SINCE WE KNOW PWCOMMERCES CORE PAYMENT ADDONS, AND SINCE THIS MIGHT NOT CHANGE FOR LONG OR FOREVER, WE JUST EXCLUDE THEM BY NAME! I.E. 'invoice', 'stripe', and 'paypal'
		/** @var array $namesOfCorePaymentAddons */
		$namesOfCorePaymentAddons = $this->pwcommerce->getNamesOfCorePaymentAddons();
		$namesOfCorePaymentAddonsSelector = implode("|", $namesOfCorePaymentAddons);
		$paymentAddonsPages = $this->wire('pages')->find("template=" . PwCommerce::PAYMENT_PROVIDER_TEMPLATE_NAME . ",name!={$namesOfCorePaymentAddonsSelector}");

		// ----------
		if ($paymentAddonsPages->count()) {
			// FOUND PAYMENT ADDONS PAGES
			foreach ($paymentAddonsPages as $page) {
				// DELETE  EACH NON-CORE PAYMENT ADDON PAGE
				$page->delete();
			}
		}
		// #####################
		return;
	}

	private function processNonPaymentCustomAddonPage($addonClassName, $input) {

		$pages = $this->wire('pages');
		$sanitizer = $this->wire('sanitizer');
		// ----------
		$addonValues = [];
		$addonID = (int) $input->{"pwcommerce_addon_id_{$addonClassName}"};

		if ($this->action === 'activate') {
			// ACTIVATE
			$addonTitle = $sanitizer->text($input->{"pwcommerce_addon_title_{$addonClassName}"});
			$name = $this->wire('sanitizer')->pageName($addonTitle, true);
			$addonViewURL = $sanitizer->pageName($input->{"pwcommerce_addon_view_url_{$addonClassName}"});

			// fist check if addons page exists
			// get the addons settings page
			$page = $pages->get("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",name={$name}");

			#########################################
			// CREATE ADDON PAGE IF IT DOESN'T EXIST
			if (empty($page->id)) {
				// ACTION: ACTIVATE
				# ++++++++++
				$languages = $this->wire('languages');
				// PAGE DOES NO EXIST: CREATE IT
				$parent = $this->pwcommerce->getCustomAddonsParentPage();

				$page = new Page();
				$page->parent = $parent;
				$page->template = PwCommerce::SETTINGS_TEMPLATE_NAME;
				$page->title = $addonTitle;
				$page->name = $name;

				// set addon page as active in other languages
				if ($languages) {
					foreach ($languages as $language) {
						// skip default language as already set above
						if ($language->name == 'default') {
							continue;
						}
						$page->set("status$language", 1);
					}
				}
				// -------------
				// save
				$page->save();
			}
			// for saving page ID if addon getting activated
			$addonID = $page->id;
			// ========
			// PREPARE ADDON SETTINGS FOR SAVING TO ADDONS SETTINGS PAGE
			// @NOTE: WE ARE NOT SAVING WITH THE ADDON PAGE ITSELF!
			// so that the settings are not accidentally deleted by addon-level custom settings
			// or so that we don't accidentally mess up with those custom/user addons settings!
			// ----------
			// we need to save the custom addon ClassNamee on first save!

			############
			$addonType = $sanitizer->fieldName($input->{"pwcommerce_addon_type_{$addonClassName}"});

			// --------
			$addonType = $sanitizer->option($addonType, $this->getAllowedAddonTypes());
			###############
			// addon uses ajax
			$addonIsUseAjax = $sanitizer->pageName($input->{"pwcommerce_addon_is_addon_use_ajax_{$addonClassName}"});
			// ----------
			// addon is configurable
			$addonIsConfigurable = $sanitizer->pageName($input->{"pwcommerce_addon_is_addon_configurable_{$addonClassName}"});

			// =============
			// PREPARE INCOMING SETTINGS
			// @note: we prefix with 'pwcommerce'!
			$addonValues = [
				'pwcommerce_addon_class_name' => $addonClassName,
				'pwcommerce_addon_page_id' => $addonID,
				'pwcommerce_addon_type' => $addonType,
				'pwcommerce_addon_title' => $addonTitle,
				// TODO FOR NOW 'LOCKED' STATUS NOT IN USE; NOT SURE ABOUT ITS NEED
				// 'is_locked' => true / false
			];
			// ------
			// ADD 'view url' for addon of type 'custom'
			if (!empty($addonViewURL)) {
				$addonValues['pwcommerce_addon_view_url'] = $addonViewURL;
			}
			// ------
			// ADD 'is use ajax' for addon of type 'custom'
			if (!empty($addonIsUseAjax)) {
				$addonValues['pwcommerce_addon_is_addon_use_ajax'] = $addonIsUseAjax;
			}
			// ------
			// TODO DELETE THIS SINCE NOW ALWAYS HAVE A PAGE? OR USE IT IN CASE OF ISSUES WITH DELETING 'ClassName'?
			// ADD 'is configurable' for addon of type 'custom'
			if (!empty($addonIsConfigurable)) {
				$addonValues['pwcommerce_addon_is_addon_configurable'] = $addonIsConfigurable;
			}
		} else {
			// ACTION: INACTIVATE
			# DELETE ADDON PAGE #
			// @note: if here, it means we have AN ADDON ID!
			// this is because a page was created when addon was activated (theoretically)
			// GET THE PAGE to delete
			// @note: using template name just to be doubly sure
			$page = $pages->get("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",id={$addonID}");

			# ++++++++++
			// DELETE ADDON PAGE IF IT DOES EXIST
			if (!empty($page->id)) {
				$pages->delete($page);
			}
		}

		// ------------
		return $addonValues;
	}

	private function processAddonsSettings(array $setttings) {
		$currentAddonsSettings = $this->pwcommerce->getAddonsSettings();

		$updatedAddonsSettings = [];
		// ------------
		if ($this->action === 'activate') {
			// UPDATE SETTINGS
			// @note: we need to merge so that we don't delete existing non-updated settings!
			$updatedAddonsSettings = array_merge($currentAddonsSettings, $setttings);
		} else {
			// REMOVE ALL OR SOME SETTINGS
			foreach ($setttings as $removeSetting) {
				if (!empty($currentAddonsSettings[$removeSetting])) {
					unset($currentAddonsSettings[$removeSetting]);
				}
			}
			// --------
			// make the process $currentAddonsSettings the updated settings
			$updatedAddonsSettings = $currentAddonsSettings;
		}
		// ----------

		// *****************
		// UPDATE ADDONS SETTINGS
		$page = $this->pwcommerce->getCustomAddonsSettingsPage();
		if (!empty($updatedAddonsSettings)) {
			// prepare the JSON string to save as addons settings
			$updatedAddonsSettingsJSON = json_encode($updatedAddonsSettings);
			// assign to settings field
		} else {
			$updatedAddonsSettingsJSON = '';
		}

		$page->pwcommerce_settings = $updatedAddonsSettingsJSON;
		//-------------
		// save the page's 'pwcommerce_settings' field
		$page->save('pwcommerce_settings');
	}

	private function processAddonsPage() {
		// process the settings
		$input = $this->actionInput; // @note this is $input->post!!
		// SPECIAL HANDLING OF 'enable_addons'
		// if creating afresh, we need to create an 'addons' page
		// ELSE if inactive, will need to delete the page

		$enableAddons = (int) $input->pwcommerce_general_settings_enable_addons;
		// --------

		if ($enableAddons) {
			// ENABLE ADDONS
			// check if addons page exists; if not create one
			$action = "create";
		} else {
			// ADDONS NOT ENABLED + DELETE ADDONS PAGE
			// check if addons page exists; if yes, delete it
			$action = "delete";
		}

		$notice = $this->actionAddonsPage($action);
		return $notice;
	}

	private function actionAddonsPage($action) {
		// TODO NEED TO RETURN SOMETHING FOR NOTICES!
		$pages = $this->wire('pages');
		// fist check if addons page exists
		// get the addons parent page
		// TODO DELETE WHEN DONE! THIS NOW CHANGES; IT IS A CHILD OF PWCOMMERCE PAGE AND USES TEMPLATE 'pwcommerce'
		// $page = $pages->get("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",name=addons");
		// $page = $pages->get("template=" . PwCommerce::PWCOMMERCE_TEMPLATE_NAME . ",name=addons");
		$page = $this->pwcommerce->getCustomAddonsParentPage();

		// we didn't get the page; abort

		$notice = "";
		// PAGE EXISTS
		if (!empty($page->id)) {
			if ($action === 'delete') {
				// TODO DO WE DELETE OR TRASH? DEVS MIGHT WANT TO RESTORE, ESPECIALLY DURING TESTING!
				// DELETE
				// TODO HERE WE ALSO NEED TO DELETE NON-CORE PAYMENT PROVIDERS SINCE THEIR ADDONS CREATE PAGES! SO, EXTRA PROCESSING
				// ========
				// FIRST DELETE all ADDONS OF TYPE 'payment'
				$this->actionDeleteAllNoneCorePaymentAddons();

				// then addons settings page itself + any configurable addons pages
				$pages->delete($page, true);

				// ##########
				// TODO CONFIRM DELETED?
				$notice = $this->_('Also disabled addons feature and deleted existing settings.');
			}
			// else: nothing else to do since page exists already
			// ---------
		} elseif ($action === 'create') {
			// PAGE DOES NO EXIST: CREATE IT

			$languages = $this->wire('languages');
			// ----------
			$parent = $pages->get("template=" . PwCommerce::PWCOMMERCE_TEMPLATE_NAME . ",name=pwcommerce");

			$page = new Page();
			$page->parent = $parent;
			// $page->template = PwCommerce::SETTINGS_TEMPLATE_NAME;
			// TODO @see above: this now changes; no settings here but each activated addon creates own page!
			$page->template = PwCommerce::PWCOMMERCE_TEMPLATE_NAME;
			$page->title = 'Addons';
			$page->name = 'addons';

			// set page as active in other languages
			if ($languages) {
				foreach ($this->wire('languages') as $language) {
					// skip default language as already set above
					if ($language->name == 'default') {
						continue;
					}
					$page->set("status$language", 1);
				}
			}

			$page->save();

			// ----------
			// CREATE CHILD PAGE - 'Addons Settings'
			$childPage = new Page();
			$childPage->template = PwCommerce::SETTINGS_TEMPLATE_NAME;
			$childPage->parent = $page;
			$childPage->title = 'Addons Settings';
			$childPage->name = 'addons-settings';

			// set child page as active in other languages
			if ($languages) {
				foreach ($this->wire('languages') as $language) {
					// skip default language as already set above
					if ($language->name == 'default') {
						continue;
					}
					$childPage->set("status$language", 1);
				}
			}

			$childPage->save();

			//  -----------
			$notice = $this->_('Also enabled addons feature.');
		}

		return $notice;
	}

}
