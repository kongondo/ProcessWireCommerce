<?php

namespace ProcessWire;

trait TraitPWCommerceProcessOrderCustomer {
	/**
	 * Gets the session's order.
	 *
	 * @param mixed $orderPage
	 * @return mixed
	 */
	public function ___getOrderCustomer($orderPage = null) {
		// /**
  * Get Order Customer.
  *
  * @param mixed $orderPage
  * @return mixed
  */
 public function getOrderCustomer($orderPage = null) {
		// TODO MAKE HOOKABLE? SO CAN ADD EXTRA STUFF, E.G. IF CUSTOMER TO PAY DIGITAL GOODS TAX?
		// ============
		// @note: init this just to avoid errors in case no order
		$orderCustomer = new WireData();

		// if order page NOT GIVEN; get from session
		if (empty($orderPage)) {
			$orderPage = $this->getOrderPage();
		}

		// if (!empty($orderPage)) {
		if (!$orderPage instanceof NullPage) {
			/** @var WireData $orderCustomer */
			$orderCustomer = $orderPage->get(PwCommerce::ORDER_CUSTOMER_FIELD_NAME);
			// -----------
			// ADD some fields we might require
			// page ID + created date
			$orderCustomer->orderID = $orderPage->id;
			$orderCustomer->orderCreated = $orderPage->created;
		}

		return $orderCustomer;
	}

	/**
	 * Set Order Page P W Commerce Order Customer Values.
	 *
	 * @param mixed $form
	 * @param Page $orderPage
	 * @return mixed
	 */
	private function setOrderPagePWCommerceOrderCustomerValues($form, Page $orderPage) {

		// -----------
		// set values for the order page field 'pwcommerce_order_customer' [PWCommerceOrderCustomer] -> WireData
		$orderCustomer = new WireData();
		$sanitizer = $this->wire('sanitizer');
		$post = $this->input->post;
		// TODO: IF COUNTRY, WE NEED TO GET COUNTRY TITLE/NAME!
		// $countryFields = ['shipping_address_country', 'billing_address_country'];
		// ------
		$arrayInputNameToPropertyName = $this->getOrderCustomerFields();

		// @note: if $this->isCustomForm, $form will be an array
		// @note: if  we $this->isUseCustomFormInputNames, we will need to look at the equivalent input names!
		// @note: only for custom form! create different methods for either!
		foreach ($form as $input) {
			// --------------
			// TODO: THROW ERROR HERE IF NO EQUIVALENT PWCOMMERCE INPUT NAME AND USING CUSTOM FORM INPUT NAMES?
			// if used custom form
			if (!empty($this->isCustomForm)) {

				// skip payment provider ID
				if ($input['input_name'] == 'pwcommerce_order_payment_id')
					continue;
				// -------------

				// CUSTOM FORM
				$inputName = $input['input_name'];
				$value = $input['type'] == 'integer' ? (int) $post->get($inputName) : $sanitizer->text($post->get($inputName));
				$fieldName = $input['input_name'];
				// ----------
				// TODO REVISIT THIS!
				// set property
				if (isset($arrayInputNameToPropertyName[$fieldName])) {
					$property = $arrayInputNameToPropertyName[$fieldName]['property'];
					$orderCustomer->set($property, $value);
				}
			} else {

				// INTERNAL PWCOMMERCE PROCESSWIRE FORM
				// TODO HANDLE AND SET COUNTRY NAME/TITLE BASED ON SENT COUNTRY ID!
				$value = $sanitizer->text($input->value);
				// TODO CHANGE THIS! PROPERTY NEEDS TO BE FOUND IN THE ARRAY!
				$fieldName = $input->name;
				// skip payment provider ID
				if ($fieldName == 'pwcommerce_order_payment_id')
					continue;

				// ----------
				// set property
				if (isset($arrayInputNameToPropertyName[$fieldName])) {
					// e.g. 'first_name' -> firstName
					$property = $arrayInputNameToPropertyName[$fieldName]['property'];
					$orderCustomer->set($property, $value);
				}
			}
		}
		// end: foreach

		// SHIPPING ADDRESS DETAILS (IF NOT SENT)
		// if these are empty, we ASSUME identical to customer details
		// we get the from there
		$shippingAddressDetails = [
			'shippingAddressFirstName' => 'firstName',
			'shippingAddressMiddleName' => 'middleName',
			'shippingAddressLastName' => 'lastName',
		];
		// TODO IS THIS OK?
		foreach ($shippingAddressDetails as $shippingProperty => $customerProperty) {
			if (empty($orderCustomer->$shippingProperty)) {
				$orderCustomer->$shippingProperty = $orderCustomer->$customerProperty;
			}
		}
		// TODO: EXTRACT TO OWN METHOD! ALSO FOR BLLING ADDRESS!
		// SHIPPING COUNTRY NAME/TITLE
		// TODO @NOTE: temporary for convenience later; the country ID comes from the form as the country; below, we use API to get the country name
		$orderCustomer->shippingAddressCountryID = $orderCustomer->shippingAddressCountry;
		$this->session->shippingAddressCountryID = $orderCustomer->shippingAddressCountryID;
		// -----------
		// just being cautious here so we also check template!
		$shippingCountryName = $this->wire('pages')->getRaw("template=" . PwCommerce::COUNTRY_TEMPLATE_NAME . ",id={$orderCustomer->shippingAddressCountry}", 'title');
		$orderCustomer->shippingAddressCountry = $shippingCountryName;

		// ----------------
		// USE BILLING ADDRESS
		// TODO - NEED TO CLEAR OUT FORMER BILLING ADDRESS DETAILS; JUST IN CASE CUSTOMER CHANGED MIND AT CHECKOUT!
		// if (!empty($isUseBillingAddress)) {
		if (!empty((int) $orderCustomer->useBillingAddress)) {
			$orderCustomer->useBillingAddress = 1;
			// TODO: EXTRACT TO OWN METHOD! ALSO FOR BLLING ADDRESS!
			// SHIPPING COUNTRY NAME/TITLE
			// TODO @NOTE: temporary for convenience later; the country ID comes from the form as the country; below, we use API to get the country name
			$orderCustomer->billingAddressCountryID = $orderCustomer->billingAddressCountry;

			// -----------
			// just being cautious here so we also check template!
			$billingCountryName = $this->wire('pages')->getRaw("template=" . PwCommerce::COUNTRY_TEMPLATE_NAME . ",id={$orderCustomer->billingAddressCountry}", 'title');
			$orderCustomer->billingAddressCountry = $billingCountryName;
		}

		// --------------
		// USER
		// if user logged in, save their id
		if ($this->user->isLoggedIn()) {
			$orderCustomer->userID = $this->user->id;
		}

		// ------------
		// IP ADDRESS
		$orderCustomer->ipAddress = $_SERVER['REMOTE_ADDR'];
		// SET TO ORDER and save
		/** @var Page $this->orderPage */
		// $orderPage->of(false);
		$orderPage->set(PwCommerce::ORDER_CUSTOMER_FIELD_NAME, $orderCustomer);

		// ---------
		return $orderPage;
	}
}
