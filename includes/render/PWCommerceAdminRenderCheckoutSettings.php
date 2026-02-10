<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Checkout Settings
 *
 * Class to render content for PWCommerce Admin Module executeCheckoutSettings().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderCheckoutSettings for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceAdminRenderCheckoutSettings extends WireData
{



	private $checkoutSettings;


	/**
	 * Get Tabs.
	 *
	 * @param mixed $wrapper
	 * @return mixed
	 */
	protected function getTabs($wrapper) {

		// GET CHECKOUT SETTINGS PAGE
		// TODO: name ok? or search by title?
		$checkoutSettingsJSON = $this->wire('pages')->getRaw("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",name=checkout", 'pwcommerce_settings');
		$checkoutSettings = [];
		if (!empty($checkoutSettingsJSON)) {
			$checkoutSettings = json_decode($checkoutSettingsJSON, true);
		}
		$this->checkoutSettings = $checkoutSettings;

		//-------------------

		$tabsNames = ['main', 'order_processing', 'abandoned_checkouts'];

		foreach ($tabsNames as $tabName) {
			$tabContents = $this->getCheckoutSettingsTabs($tabName);

			$tab = $this->pwcommerce->getInputfieldWrapper();
			$tabDetails = $tabContents['details'];
			$tab->attr([
				'id' => $tabDetails['id'],
				'class' => 'WireTab',
				'title' => $tabDetails['title'],
			]);
			//------------
			$tabInputfields = $tabContents['inputfields'];
			// loop through inputfields details and build inputfields
			foreach ($tabInputfields as $inputfield) {
				$field = $this->getInputfieldForTab($inputfield);
				$tab->add($field);
			}
			// add tab to tabs wrapper
			$wrapper->add($tab);
		}

		// ------------
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPwCommerce::pagesHandler know that we are ready to save
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];
		//------------------- is_ready_to_save (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		//--------------
		return $wrapper;
	}

	/**
	 * Get Checkout Settings Tabs.
	 *
	 * @param mixed $tabName
	 * @return mixed
	 */
	private function getCheckoutSettingsTabs($tabName) {
		if ($tabName === 'main') {
			$tab = $this->getMainTab();
		} else if ($tabName === 'order_processing') {
			$tab = $this->getOrderProcessingTab();
		} else if ($tabName === 'abandoned_checkouts') {
			$tab = $this->getAbandonedCheckoutsTab();
		}

		return $tab;
	}

	// MAIN TAB
	/**
	 * Get Main Tab.
	 *
	 * @return mixed
	 */
	private function getMainTab() {

		//--------------
		// for accounts for checkouts
		$accountRequirementAtCheckoutRadioOptions = [
			'guest_customers' => $this->_('Customers will only be able to check out as guests'),
			'registered_or_guest_customers' => $this->_('Customers will be able to check out
				with a customer account or as a guest'),
			'registered_customers' => $this->_('Customers will only be able to check out if they
				have a customer account'),
		];
		//--------------
		// for company name + for shipping address phone number
		$hiddenOptionalRequiredRadioOptions = [
			'hidden' => $this->_('Hidden'),
			'optional' => $this->_('Optional'),
			'required' => $this->_('Required'),
		];

		//--------------
		// for show billing address fields by default in checkout
		$showBillingAddressFieldsRadioOptions = [
			// TODO: rephrase?
			true => $this->_("Show billing address by default at checkout"),
			false => $this->_("Do not show billing address by default at checkout")
		];

		//------------------
		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_checkout_settings_main_tab',
				'title' => $this->_('Main'),
			],
			'inputfields' => [
				// accounts
				[
					'type' => 'radio',
					'name' => 'pwcommerce_checkout_settings_account_requirement_at_checkout',
					'label' => $this->_('Accounts'),
					'description' => $this->_('Indicate if an account is needed for customers to check out.'),
					'radio_options' => $accountRequirementAtCheckoutRadioOptions,
					'value' => $this->getCheckoutSettingValue('account_requirement_at_checkout'),

				],
				// company name
				[
					'type' => 'radio',
					'name' => 'pwcommerce_checkout_settings_company_name_field_at_checkout',
					'label' => $this->_('Company Name'),
					'description' => $this->_('Indicate if a company name field should be shown in the checkout form.'),
					'radio_options' => $hiddenOptionalRequiredRadioOptions,
					'value' => $this->getCheckoutSettingValue('company_name_field_at_checkout'),

				],
				// shipping address phone number
				[
					'type' => 'radio',
					'name' => 'pwcommerce_checkout_settings_shipping_address_phone_number_field_at_checkout',
					'label' => $this->_('Shipping Address Phone Number'),
					'description' => $this->_('Indicate if a shipping address phone number field should be shown in the checkout form.'),
					'radio_options' => $hiddenOptionalRequiredRadioOptions,
					'value' => $this->getCheckoutSettingValue('shipping_address_phone_number_field_at_checkout'),

				],

				// show billing address by default
				[
					'type' => 'radio',
					'name' => 'pwcommerce_checkout_settings_show_billing_address_fields_by_default_at_checkout',
					'label' => $this->_('Show Billing Address by Default'),
					'description' => $this->_('Indicate if billing address fields should be shown in the checkout form by default.'),
					'radio_options' => $showBillingAddressFieldsRadioOptions,
					'value' => $this->getCheckoutSettingValue('show_billing_address_fields_by_default_at_checkout'),
				],

			],

		];

		return $tabAndContents;
	}

	// ORDER PROCESSING TAB
	/**
	 * Get Order Processing Tab.
	 *
	 * @return mixed
	 */
	private function getOrderProcessingTab() {

		//--------------
		// for after order has been paid
		$afterOrderHasBeenPaidRadioOptions = [
			true => $this->_("Automatically fulfill the order's line items"),
			false => $this->_("Do not automatically fulfill any of the order's line items")
		];

		// use the shipping address as the billing address by default
		$useShippingAddressAsBillingAddressByDefault = $this->getCheckoutSettingValue('use_shipping_address_as_the_billing_address_by_default');

		// require a confirmation step
		$requireConfirmationStepBeforePurchase = $this->getCheckoutSettingValue('require_a_confirmation_step_before_purchase');

		// for // enable address autocompletion
		$enableAddressAutocompletion = $this->getCheckoutSettingValue('enable_address_autocompletion');

		//------------------
		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_checkout_settings_order_processing_tab',
				'title' => $this->_('Order Processing'),
			],
			'inputfields' => [
				// use the shipping address as the billing address by default
				[
					'type' => 'checkbox',
					'name' => 'pwcommerce_checkout_settings_use_shipping_address_as_the_billing_address_by_default',
					'label' => ' ', // @note: empty string just to hide label but keeping label2
					'label2' => $this->_('Use the shipping address as the billing address by default'),
					'description' => $this->_('Change how your shop handles addresses during checkout and order events.'),
					'notes' => $this->_('The billing address can still be edited.'),
					'value' => $useShippingAddressAsBillingAddressByDefault,
					'checked' => empty($useShippingAddressAsBillingAddressByDefault) ? false : true

				],
				// require a confirmation step
				[
					'type' => 'checkbox',
					'name' => 'pwcommerce_checkout_settings_require_a_confirmation_step_before_purchase',
					'label' => ' ', // @note: empty string just to hide label but keeping label2
					'label2' => $this->_('Require a confirmation step'),
					'description' => $this->_('Check if customers must review their order details before purchasing.'),
					'notes' => $this->_('The billing address can still be edited.'),
					'value' => $requireConfirmationStepBeforePurchase,
					'checked' => empty($requireConfirmationStepBeforePurchase) ? false : true

				],
				// enable address autocompletion
				[
					'type' => 'checkbox',
					'name' => 'pwcommerce_checkout_settings_enable_address_autocompletion',
					'label' => ' ', // @note: empty string just to hide label but keeping label2
					'label2' => $this->_('Enable address autocompletion'),
					'notes' => $this->_('Gives customers address suggestions when they enter their shipping and billing address.'),
					'value' => $enableAddressAutocompletion,
					'checked' => empty($enableAddressAutocompletion) ? false : true

				],
				// automatically fulfill order after an order has been paid
				[
					'type' => 'radio',
					'name' => 'pwcommerce_checkout_settings_enable_automatic_fulfillment_after_an_order_has_been_paid',
					'label' => $this->_('After an Order has been Paid'),
					'radio_options' => $afterOrderHasBeenPaidRadioOptions,
					'value' => $this->getCheckoutSettingValue('enable_automatic_fulfillment_after_an_order_has_been_paid'),

				],

			],

		];

		return $tabAndContents;
	}

	// ABANDONED CHECKOUTS
	/**
	 * Get Abandoned Checkouts Tab.
	 *
	 * @return mixed
	 */
	private function getAbandonedCheckoutsTab() {

		//--------------
		// for send automatic emails TO on abandoned checkout
		$sendAbandonedCheckoutsAutomaticEmailsToRadioOptions = [
			'anyone' => $this->_("Anyone who abandons checkout"),
			'registered_customers_only' => $this->_("Registered customers who abandon checkout")
		];

		// for send automatic emails AFTER on abandoned checkout
		$sendAbandonedCheckoutsAutomaticEmailsAfterRadioOptions = [
			1 => $this->_("1 hour"),
			6 => $this->_("6 hours"),
			10 => $this->_("10 hours (recommended)"),
			24 => $this->_("24 hours"),
		];

		// for automatically send abandoned checkout emails
		$automaticallySendAbandonedCheckoutEmails = $this->getCheckoutSettingValue('automatically_send_abandoned_checkout_emails');

		//------------------
		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_checkout_settings_abandoned_checkouts_tab',
				'title' => $this->_('Abandoned Checkouts'),
			],
			'inputfields' => [
				// automatically send abandoned checkout emails
				[
					'type' => 'checkbox',
					'name' => 'pwcommerce_checkout_settings_automatically_send_abandoned_checkout_emails',
					'label' => ' ', // @note: empty string just to hide label but keeping label2
					'label2' => $this->_('Automatically send abandoned checkout emails'),
					'description' => $this->_("Send an email to customers who left products in their cart but didn't complete their order."),
					'value' => $automaticallySendAbandonedCheckoutEmails,
					'checked' => empty($automaticallySendAbandonedCheckoutEmails) ? false : true

				],

				// send abandoned checkout automatic emails to
				[
					'type' => 'radio',
					'name' => 'pwcommerce_checkout_settings_send_abandoned_checkout_emails_to',
					'label' => $this->_('Send Automatic Emails To'),
					'description' => $this->_("Choose who to send abandoned checkout emails to."),
					'radio_options' => $sendAbandonedCheckoutsAutomaticEmailsToRadioOptions,
					'value' => $this->getCheckoutSettingValue('send_abandoned_checkout_emails_to'),

				],
				// send abandoned checkout automatic emails after
				[
					'type' => 'radio',
					'name' => 'pwcommerce_checkout_settings_send_abandoned_checkout_emails_after',
					'label' => $this->_('Send Automatic Emails After'),
					'description' => $this->_("Choose when to send abandoned checkout emails."),
					'radio_options' => $sendAbandonedCheckoutsAutomaticEmailsAfterRadioOptions,
					'value' => $this->getCheckoutSettingValue('send_abandoned_checkout_emails_after'),

				],

			],

		];

		return $tabAndContents;
	}

	/**
	 * Get Inputfield For Tab.
	 *
	 * @param array $options
	 * @return mixed
	 */
	private function getInputfieldForTab($options) {
		$type = $options['type'];
		if (in_array($type, ['text', 'number'])) {
			$field = $this->pwcommerce->getInputfieldText($options);
		} else if ($type === 'textarea') {
			$field = $this->pwcommerce->getInputfieldTextarea($options);
		} else if ($type === 'radio') {
			$field = $this->pwcommerce->getInputfieldRadios($options);
		} else if ($type === 'select') {
			$field = $this->pwcommerce->getInputfieldSelect($options);
		} else if ($type === 'tags') {
			$field = $this->pwcommerce->getInputfieldTextTags($options);
		} else if ($type === 'checkbox') {
			$field = $this->pwcommerce->getInputfieldCheckbox($options);
		}

		return $field;
	}

	/**
	 * Get Checkout Setting Value.
	 *
	 * @param mixed $setting
	 * @return mixed
	 */
	private function getCheckoutSettingValue($setting) {
		$checkoutSettings = $this->checkoutSettings;
		// TODO: SHOULD WE SAVE ZEROS OR LEAVE BLANK OR NULL? WHAT IF ZERO WAS ACTUALLY INPUT?
		// TODO: OR SHOULD WE LEAVE THAT FOR DISPLAY TO HANDLE? LEAVE IT TO DISPLAY!
		$value = isset($checkoutSettings[$setting]) ? $checkoutSettings[$setting] : null;
		return $value;
	}
}
