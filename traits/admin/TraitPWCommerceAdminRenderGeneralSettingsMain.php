<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsMain
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MAIN TAB  ~~~~~~~~~~~~~~~~~~
	private function getMainTab() {

		// get setting for 'enable_addons'
		$enableAddons = $this->getGeneralSettingValue('enable_addons');
		// check if an addons page already exists under 'settings'
		$isAddonsPageExists = $this->isExistAddonsPage();

		$addonsDescription = $this->_('Check to enable use of shop addons.');

		if (!empty($isAddonsPageExists)) {
			$addonsDescription .= " " . $this->_('Please note that disabling the addons feature after previously enabling the feature will result in related addons pages being deleted!');
		}

		//------------------
		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_general_settings_main_tab',
				'title' => $this->_('Main'),
			],
			'inputfields' => [
				// shop name
				'shop_name' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_shop_name',
					'label' => $this->_('Shop Name'),
					'columnWidth' => 33,
					'value' => $this->getGeneralSettingValue('shop_name'),
				],
				// email
				'email' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_shop_email',
					'label' => $this->_('Email'),
					'columnWidth' => 33,
					'value' => $this->getGeneralSettingValue('shop_email'),
					'required' => true,
				],
				// from email
				// @see: https://processwire.com/talk/topic/28339-should-order-confirmation-emails-also-be-received-by-store/
				'from_email' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_shop_from_email',
					'label' => $this->_('From Email'),
					'columnWidth' => 33,
					'value' => $this->getGeneralSettingValue('shop_from_email'),
					'notes' => $this->_("Use this as your 'send emails from' if your mail server is blocking sending emails from your 'shop email'. This might be the case, for instance, with Google."),
					// 'required' => true,
				],

				// legal business name
				'legal_business_name' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_legal_name_of_business',
					'label' => $this->_('Legal Name of Business'),
					'columnWidth' => 100,
					'value' => $this->getGeneralSettingValue('legal_name_of_business'),
				],

				// address
				'address' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_address_line_one',
					'label' => $this->_('Address'),
					'columnWidth' => 100,
					'value' => $this->getGeneralSettingValue('address_line_one'),
					'required' => true,
				],

				// address continued
				'address_continued' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_address_line_two',
					'label' => $this->_('Address continued (optional)'),
					'columnWidth' => 100,
					'value' => $this->getGeneralSettingValue('address_line_two'),

				],

				// city
				'city' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_city',
					'label' => $this->_('City'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('city'),
					'required' => true,
				],

				// postal / zip code
				'postal_zip_code' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_postal_code',
					'label' => $this->_('Postal / Zip Code'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('postal_code'),
					'required' => true,
				],

				// country
				// TODO: CHANGING TO TEXT TAGS!
				'country' => [
					'type' => 'tags',
					'name' => 'pwcommerce_general_settings_country',
					'label' => $this->_('Country'),
					'columnWidth' => 50,
					'maxItems' => 1,
					// TODO - SET TAGS LIST HERE ISSUE! THIS IS BECAUSE OUR SAVED VALUE AND ACTUAL VALUES ARE DIFFERENT!
					'set_tags_list' => $this->getCountries(),
					// @note: InputfieldTextTags will not allow a tag if  this condition is not met:
					// !$allowUserTags && ($isPageField || !$this->tagsUrl)
					// so we add this fake-url but don't useAjax to allow this to get through
					'tagsUrl' => 'fake-url',
					'value' => $this->getGeneralSettingValue('country'),
					'required' => true,
				],

				// phone
				'phone' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_phone',
					'label' => $this->_('Phone'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('phone'),
				],

				# ***************
				// BANK DETAILS
				// useful for invoice payments
				// shop bank name
				'bank_info' => [
					'type' => 'markup',
					// 'name' => 'pwcommerce_general_settings_bank_name',
					'label' => $this->_('Bank Details'),
					// 'description' => $this->_('Useful for invoice payments'),
					'collapsed' => Inputfield::collapsedNever,
					// 'wrapClass' => true,
					// 'wrapper_classes' => 'pwcommerce_no_outline',
					'columnWidth' => 100,
					'value' => $this->_('Bank details are useful if your shop accepts settling invoice payments via direct bank transfer.'),
				],
				// bank name
				'bank_name' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_bank_name',
					'label' => $this->_('Bank Name'),
					// 'description' => $this->_('Useful for invoice payments.'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('bank_name'),
				],
				// bank account name
				'bank_account_name' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_bank_account_name',
					'label' => $this->_('Bank Account Name'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('bank_account_name'),
				],
				// bank sort code
				'bank_sort_code' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_bank_sort_code',
					'label' => $this->_('Bank Sort Code'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('bank_sort_code'),
				],
				// bank account number
				'bank_account_number' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_bank_account_number',
					'label' => $this->_('Bank Account Number'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('bank_account_number'),
				],
				// bank IBAN
				'bank_iban' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_bank_iban',
					'label' => $this->_('Bank International Bank Account Number (IBAN)'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('bank_iban'),
				],
				// bank BIC/SWIFT
				'bank_bic' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_bank_bic',
					'label' => $this->_('Branch Identifier Code (BIC)'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('bank_bic'),
					'notes' => $this->_("Also referred to as SWIFT Code."),
				],


				# ***************
				// allow addons
				// TODO rephrase below as needed
				'allow_addons' => [
					'type' => 'checkbox',
					'name' => 'pwcommerce_general_settings_enable_addons',
					'label' => $this->_('Addons'),
					'label2' => $this->_('Enable installation of addons'),
					'description' => $addonsDescription,
					'notes' => $this->_('Addons extend the functionality of your shop.'),
					'value' => $enableAddons,
					'checked' => empty($enableAddons) ? false : true

				],

			],

		];

		// TODO @UPDATE FRIDAY 1 JULY 2022 - NO LONGER IN USE! WE NOW GIVEN WARNING DIRECTLY WHEN DISABLING IN MARKUP
		// TODO DELETE WHEN DONE

		// if addons page already exists, add checkbox to specify if to delete addons page as well if addons feature is disabled

		// if (!empty($isAddonsPageExists)) {
		// $deleteAddonsSettings = 				[
		// 	'type' => 'markup',
		// 	'name' => 'pwcommerce_general_settings_delete_addons_settings_page',
		// 	// 'label' => $this->_('Delete Addons Settings'),
		// 	// 'label2' => $this->_('Delete addons settings'),
		// 	'skipLabel' => Inputfield::skipLabelHeader,
		// 	'description' => $this->_('Check to also delete existing addons settings.'),
		// 	// 'notes' => $this->_('Only applies if addons had previously been enabled. This action cannot be undone.'),
		// 	// TODO @note: below not required as this is a one time action!
		// 	// 'value' => $enableAddons,
		// 	// 'checked' => empty($enableAddons) ? false : true
		// 	'show_if' => "pwcommerce_general_settings_enable_addons=0",
		// 	'wrapClass' => true,
		// 	'wrapper_classes' => 'pwcommerce_no_outline',

		// ];
		// $tabAndContents['inputfields'][] = $deleteAddonsSettings;
		// }

		return $tabAndContents;
	}

}
