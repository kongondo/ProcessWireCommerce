<?php

namespace ProcessWire;

trait TraitPWCommerceCustomerForm
{
	private function checkInbuiltOrderCustomerFormForErrors($form) {
		// TODO @see: https://processwire.com/talk/topic/4659-customizing-error-messages-for-inputfields/

		// TODO WIP THE USUAL $form->getErrors() does not seem to work with our inputfields (?)

		// 		$errors = $inputfield->getErrors(true); // true=clear the errors out
		// // $errors is a plain PHP array of error messages...
		// // ...if you want to iterate it or do anything with it
		// $inputfield->error('Your custom error message')

		//
		// expected schema ->
		// $customFormFieldsExampleArray = [
		// [
		// // the name of the input of the custom form
		// 'input_name' => 'email',
		// // the input type (for sanitization)
		// 'type' => 'email',
		// // if field/input is required
		// 'required' => true
		// ],
		// [
		// 'input_name' => 'firstName',
		// 'type' => 'text',
		// 'required' => false // can be left out
		// ]
		// ];
		//

		// -------------

		$formErrors = [];
		// $post = $this->wire('input')->post;
		foreach ($form as $inputfield) {
			// $errors = $inputfield->getErrors(true); // true=clear the errors out

			// echo "FIELD NAME: {$inputfield->name}<br>";

			$required = $inputfield->attr('required');
			// echo "FIELD REQUIRED: {$required}<br>";
			$inputType = $inputfield->attr('type');
			// TODO: NEED TO HANDLE COUNTRY ID VALUE IN SELECT as attr(type) does not return anything for select!
			// echo "FIELD TYPE: {$inputType}<br>";
			// $value = $post->get($customFormField['input_name']);
			$value = $inputfield->attr('value');
			// echo "FIELD VALUE: {$value}<br>";
			$required = !empty($required);
			$cleanValue = $this->getCleanedFormValue($value, $inputType);
			// check required is not empty (if text or email)
			// // TODO: for now, we don't handle integer_bool (e.g. checkbox)
			// TODO REVISIT THIS! MONDAY 26 JUNE 2023! WE NOW USE useBillingAddress!
			if (!empty($required) && empty($cleanValue) && !in_array($inputType, ['integer'])) {
				$formErrors[] = $inputfield->name;
			}
		}

		// -------
		return $formErrors;
	}

	public function getCustomerForm() {

		//-----------


		// ---------
		$form = $this->modules->get("InputfieldForm");
		$form->attr("id", "pwcommerce_order_customer_form");

		$requiredField = ['email', 'shippingAddressCountry'];
		// ------------
		$orderCustomerFields = $this->getOrderCustomerFields();

		$shopCheckoutSettings = $this->getShopCheckoutSettings();

		$orderCustomerBillingFieldsNames = $this->getOrderCustomerBillingFields();
		// TODO DELETE IF NOT IN USE
		// OPTIONAL SKIP FIELDS
		// $skipFields = [
		//   'company' => []
		// ];
		$orderCustomer = $this->getOrderCustomer();

		$isOrderHasSavedCustomer = !empty($orderCustomer->email);

		// 'shippingAddressCountry' => 'Andorra'
		// 'shippingAddressCountryID' => 2681

		// BUILD ORDER CUSTOMER FORM
		foreach ($orderCustomerFields as $name => $inputfield) {

			// SKIP FIELDS PER SHOP CHECKOUT SETTINGS
			$isRequired = false;

			// skip company name
			//if()

			// ----------
			// skip billing address fields
			if (in_array($name, $orderCustomerBillingFieldsNames) && empty((int) $shopCheckoutSettings->show_billing_address_fields_by_default_at_checkout)) {
				continue;
			}

			// skip shipping phone number
			if ($name === 'shipping_address_phone') {
				$shippingAddressPhoneSetting = $shopCheckoutSettings->shipping_address_phone_number_field_at_checkout;
				if ($shippingAddressPhoneSetting == 'hidden') {
					// skip shipping phone field
					continue;
				} else if ($shippingAddressPhoneSetting == 'required') {
					$isRequired = true;
				}
			}

			// ---------
			$field = $this->getInputfieldForCustomerForm($inputfield);
			// ----------
			if (in_array($name, $requiredField) || !empty($isRequired)) {

				// set required field
				$field->attr('required', true);
			}
			$field->collapsed = Inputfield::collapsedNever;
			// --------
			// ADD VALUE IF ORDER ALREADY HAS CUSTOMER
			if ($isOrderHasSavedCustomer) {
				if ($name === 'shippingAddressCountry') {
					$property = "shippingAddressCountryID|shippingAddressCountry";
				} else {
					$property = $inputfield['property'];
				}
				$value = $orderCustomer->get($property);

				$field->value = $value;
			}
			// ----------
			$form->add($field);
		}

		// ADD PAYMENT PROVIDER RADIOS selections
		$field = $this->getCustomerOrderFormPaymentProviders();
		$field->collapsed = Inputfield::collapsedNever;
		$field->attr('required', true);
		$form->add($field);
		#+++++++++++++++++++++++++++++++

		return $form;
	}

	private function getInputfieldForCustomerForm($options) {
		$type = $options['type'];

		$inputfieldsHelpers = $this->inputfieldsHelpers;

		if (in_array($type, ['text', 'number', 'email'])) {
			$field = $inputfieldsHelpers->getInputfieldText($options);
		} else if ($type === 'textarea') {
			$field = $inputfieldsHelpers->getInputfieldTextarea($options);
		} else if ($type === 'radio') {
			$field = $inputfieldsHelpers->getInputfieldRadios($options);
		} else if ($type === 'checkbox' || $type === 'integer') {
			// TODO FOR NOW 'integer' is synonymous with checkbox! Monday 26 June 2023)
			$field = $inputfieldsHelpers->getInputfieldCheckbox($options);
		} else if ($type === 'select') {
			$field = $inputfieldsHelpers->getInputfieldSelect($options);
		}

		return $field;
	}

	private function getCustomerOrderFormShippingCountries() {
		$shippingCountries = $this->pwcommerce->getShippingCountries();
		// -----------
		$selectOptions = [];
		// prepare selection options
		foreach ($shippingCountries as $shippingCountry) {
			$selectOptions[$shippingCountry['id']] = $shippingCountry['name'];
		}
		// -------
		return $selectOptions;
	}


	private function getOrderCustomerFields() {
		$countrySelectOptions = $this->getCustomerOrderFormShippingCountries();
		return [
			'firstName' => [
				'property' => 'firstName',
				'type' => 'text',
				'name' => 'firstName',
				'label' => $this->_('First Name')
			],
			'middleName' => [
				'property' => 'middleName',
				'type' => 'text',
				'name' => 'middleName',
				'label' => $this->_('Middle Name(s)')
			],
			'lastName' => [
				'property' => 'lastName',
				'type' => 'text',
				'name' => 'lastName',
				'label' => $this->_('Last Name')
			],
			'email' => [
				'property' => 'email',
				'type' => 'email',
				'name' => 'email',
				'label' => $this->_('Email')
			],
			// @note: just for info: for manual orders use only
			// 'is_tax_exempt' => ['property'=>'isTaxExempt','type'=>'text', 'name'=>// 'is_tax_exempt' ,'label'=>'label'],
			// TODO: MAKE OPTIONAL BY PASSING OPTIONS! + custom labels here?
			'shippingAddressFirstName' => [
				'property' => 'shippingAddressFirstName',
				'type' => 'text',
				'name' => 'shippingAddressFirstName',
				'label' => $this->_('Shipping First Name')
			],
			'shippingAddressMiddleName' => [
				'property' => 'shippingAddressMiddleName',
				'type' => 'text',
				'name' => 'shippingAddressMiddleName',
				'label' => $this->_('Shipping Middle Name(s)')
			],
			'shippingAddressLastName' => [
				'property' => 'shippingAddressLastName',
				'type' => 'text',
				'name' => 'shippingAddressLastName',
				'label' => $this->_('Shipping Last Name')
			],
			'shippingAddressPhone' => [
				'property' => 'shippingAddressPhone',
				'type' => 'text',
				'name' => 'shippingAddressPhone',
				'label' => $this->_('Phone')
			],
			'shippingAddressCompany' => [
				'property' => 'shippingAddressCompany',
				'type' => 'text',
				'name' => 'shippingAddressCompany',
				'label' => $this->_('Shipping Company')
			],
			'shippingAddressLineOne' => [
				'property' => 'shippingAddressLineOne',
				'type' => 'text',
				'name' => 'shippingAddressLineOne',
				'label' => $this->_('Address')
			],
			'shippingAddressLineTwo' => [
				'property' => 'shippingAddressLineTwo',
				'type' => 'text',
				'name' => 'shippingAddressLineTwo',
				'label' => $this->_('Address Line Two')
			],
			'shippingAddressCity' => [
				'property' => 'shippingAddressCity',
				'type' => 'text',
				'name' => 'shippingAddressCity',
				'label' => $this->_('City')
			],
			'shippingAddressRegion' => [
				'property' => 'shippingAddressRegion',
				'type' => 'text',
				'name' => 'shippingAddressRegion',
				'label' => $this->_('Region')
			],
			'shippingAddressCountry' => [
				'property' => 'shippingAddressCountry',
				'type' => 'select',
				'name' => 'shippingAddressCountry',
				'select_options' => $countrySelectOptions,
				'label' => $this->_('Country')
			],
			'shippingAddressPostalCode' => [
				'property' => 'shippingAddressPostalCode',
				'type' => 'text',
				'name' => 'shippingAddressPostalCode',
				'label' => $this->_('Postal / Zip Code')
			],
			// @note: just for info: for manual orders use only + set manually below if applicable
			// 'use_billing_address' => ['property'=>'useBillingAddress','type'=>'text', 'name'=> 'use_billing_address' ,'label'=>'label'],
			// @UPDATE: Thursday 15 June 2023, we now allow use of 'useBillingAddress' in the frontend
			'useBillingAddress' => [
				'property' => 'useBillingAddress',
				'type' => 'integer',
				'name' => 'useBillingAddress',
				'label' => $this->_('Use Billing Address')
			],
			'billingAddressFirstName' => [
				'property' => 'billingAddressFirstName',
				'type' => 'text',
				'name' => 'billingAddressFirstName',
				'label' => $this->_('Billing First Name')
			],
			'billingAddressMiddleName' => [
				'property' => 'billingAddressMiddleName',
				'type' => 'text',
				'name' => 'billingAddressMiddleName',
				'label' => $this->_('Billing Middle Name')
			],
			'billingAddressLastName' => [
				'property' => 'billingAddressLastName',
				'type' => 'text',
				'name' => 'billingAddressLastName',
				'label' => $this->_('Billing Last Name')
			],
			'billingAddressPhone' => [
				'property' => 'billingAddressPhone',
				'type' => 'text',
				'name' => 'billingAddressPhone',
				'label' => $this->_('Billing Phone')
			],
			'billingAddressCompany' => [
				'property' => 'billingAddressCompany',
				'type' => 'text',
				'name' => 'billingAddressCompany',
				'label' => $this->_('Billing Company')
			],
			'billingAddressLineOne' => [
				'property' => 'billingAddressLineOne',
				'type' => 'text',
				'name' => 'billingAddressLineOne',
				'label' => $this->_('Billing Address')
			],
			'billingAddressLineTwo' => [
				'property' => 'billingAddressLineTwo',
				'type' => 'text',
				'name' => 'billingAddressLineTwo',
				'label' => $this->_('Billing Address Line Two')
			],
			'billingAddressCity' => [
				'property' => 'billingAddressCity',
				'type' => 'text',
				'name' => 'billingAddressCity',
				'label' => $this->_('Billing Address City')
			],
			'billingAddressRegion' => [
				'property' => 'billingAddressRegion',
				'type' => 'text',
				'name' => 'billingAddressRegion',
				'label' => $this->_('Billing Address Region')
			],
			'billingAddressCountry' => [
				'property' => 'billingAddressCountry',
				'type' => 'select',
				'name' => 'billingAddressCountry',
				'select_options' => $countrySelectOptions,
				'label' => $this->_('Billing Address Country')
			],
			'billingAddressPostalCode' => [
				'property' => 'billingAddressPostalCode',
				'type' => 'text',
				'name' => 'billingAddressPostalCode',
				'label' => $this->_('Billing Address Postal / Zip Code')
			],
		];
	}

	private function getOrderCustomerBillingFields() {
		$orderCustomerFields = $this->getOrderCustomerFields();
		$orderCustomerBillingFieldsNames = [];
		foreach ($orderCustomerFields as $name => $orderCustomerField) {
			// if (strpos($name, 'billing') === false) {
			// @note @update: making this case-insensitive! @MONDAY 26 JUNE 2023 6.05PM; TEST!
			if (stripos($name, 'billing') === false) {
				continue;
			}
			// add billing field name
			$orderCustomerBillingFieldsNames[] = $name;
		}
		// -------
		return $orderCustomerBillingFieldsNames;
	}

}
