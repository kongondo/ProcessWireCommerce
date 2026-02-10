<?php

namespace ProcessWire;

trait TraitPWCommerceProcessOrderForm
{


	/**
	 * Process Customer Form.
	 *
	 * @param mixed $form
	 * @return mixed
	 */
	public function processCustomerForm($form) {
		$form->processInput($this->input->post);

		/** @var Array $formErrors */
		$formErrors = $this->checkInbuiltOrderCustomerFormForErrors($form);

		if (empty($formErrors)) {
			// --------------------------------
			// TODO NEED TO THROW ERROR IF MISSING PAYMENT PROVIDER VALUES!
			// TODO @KONGONDO COMMENT -> PAYMENT PROVIDERS ARE NOW PAGES SO THEY HAVE IDS!
			// TODO: this is the value we expect in the post at this name!
			if ((int) $this->input->post->pwcommerce_order_payment_id) {
				$this->setPaymentProvider((int) $this->input->post->pwcommerce_order_payment_id);

			} else {
				// NO PAYMENT PROVIDER ID SENT
				// TODO: need to show better message for customers!
				throw new Wire404Exception("Sorry, payments not possible!");
			}

			// set some shared properties
			$this->isCustomForm = false;

			// SAVE THE ORDER
			// NOTE: in 'TraitPWCommerceSaveOrder'
			$this->saveOrder($form);

			return true;
		}

		// SET ERRORS TO FORM INPUTFIELDS
		foreach ($formErrors as $inputfieldName) {
			$inputfield = $form->child("name={$inputfieldName}");
			if (!empty($inputfield)) {
				// TODO: CAN WE SET ERROR CLASS HERE?
				$inputfield->error($this->_("Missing or invalid required value."));

			}
		}
		// TODO - DON'T WE NEED TO CLEAR PREVIOUS ERRORS?

		return false;
	}

	// TODO @KONGONDO ADDITION TO PROCESS A CUSTOM CHECKOUT FORM - E.G. DIFFERENT DEV HAVE DIFFERENT NEEDS AND MAY NOT WANT TO USE THE INBUILT PROCESSWIRE INPUTFIELDS TO CREATE FORMS! - HERE, WE PROCESS SUCH CUSTOM FORMS USING SPECIFIED CRITERIA FOR VALIDATION

	/**
	 * Process Custom Order Customer Form.
	 *
	 * @param array $customFormFields
	 * @param bool $isUseCustomFormInputNames
	 * @return mixed
	 */
	public function processCustomOrderCustomerForm(array $customFormFields, bool $isUseCustomFormInputNames = false) {

		/** @var Array $formErrors */
		$formErrors = $this->checkCustomOrderCustomerFormForErrors($customFormFields);

		// TODO @KONGONDO NEW METHOD
		$orderCreationResponse = new WireData();

		if (empty($formErrors)) {
			// --------------------------------
			// TODO @KONGONDO COMMENT -> PAYMENT PROVIDERS ARE NOW PAGES SO THEY HAVE IDS!
			// TODO: this is the value we expect in the post at this name!
			if ((int) $this->input->post->pwcommerce_order_payment_id) {
				$this->setPaymentProvider((int) $this->input->post->pwcommerce_order_payment_id);
			} else {
				// NO PAYMENT PROVIDER ID SENT
				// TODO: need to show better message for customers!
				throw new Wire404Exception("Sorry, payments not possible!");
			}

			// set some shared properties
			$this->isCustomForm = true;
			$this->isUseCustomFormInputNames = $isUseCustomFormInputNames;

			// SAVE THE ORDER
			// NOTE: in 'TraitPWCommerceSaveOrder'
			$this->saveOrder($customFormFields);

			// ------
			$orderCreationResponse->success = true;
			// TODO: rephrase?
			$orderCreationResponse->message = $this->_("Order processed and created successfully.");
			// -------------
			// IN CASE order getting re-confirmed, we will have previous customer values
			// we add them here
			$orderCustomer = $this->getOrderCustomer();

			if (!empty($orderCustomer->id)) {
				// TODO NEED TO CHANGE HERE OR WHERE USED SO THAT $orderCustomer PROPERTIES MATCH THOSE OF FORM NAMES! e.g. firstName instead of first_name!
				$orderCreationResponse->previousValues = $orderCustomer;
			}
		} else {
			// FORM ERRORS!
			// order not created!
			$orderCreationResponse->success = false;
			$orderCreationResponse->message = $this->_("There are errors in the form.");
			$orderCreationResponse->errors = $formErrors;
			// TODO: WE NEED TO ALSO RETURN THE ENTERED FORM VALUES! so user does not have to re-enter them!
			// TODO: CAN WE RETURN JUST THE VALUES IN A SMALLER OBJECT? OR ARRAY? - NO; it's fine!
			$orderCreationResponse->previousValues = $this->input->post;
		}
		// ------

		return $orderCreationResponse;
	}

	/**
	 * Process Customer Shipping Confirmation.
	 *
	 * @param bool $isCustomForm
	 * @return mixed
	 */
	public function processCustomerShippingConfirmation(bool $isCustomForm = false) {

		$response = new WireData();
		## SANITY CHECKS ##

		$orderSelectedShippingRateID = (int) $this->input->post->order_selected_shipping_rate;

		// 1. NO SHIPPING RATE ID
		if (empty($orderSelectedShippingRateID)) {
			// TODO RETURN ERROR HERE

			$error = $this->_('Shipping choice not selected.');
			$response->message = $error;
			$response->success = false;
			return $response;
		}

		// 2. INVALID SHIPPING RATE ID
		// check if selected shipping rate ID is one of the matched ones
		/** @var array $matchedShippingZoneRatesIDs */
		// $matchedShippingZoneRatesIDs = $this->session->matchedShippingZoneRatesIDs;

		// if (!in_array($orderSelectedShippingRateID, $matchedShippingZoneRatesIDs)) {
		if (!$this->isValidShippingRateIDForOrder($orderSelectedShippingRateID)) {
			// TODO CONFIRM THIS WORKS! SPOOF IT!
			// TODO RETURN ERROR INVALID SHIPPING RATE ID!

			$error = $this->_('Shipping ID and Session Matched Shipping Rates IDs mismatch.');
			$response->message = $error;
			$response->success = false;
			return $response;
		}

		// GET THE SHIPPING RATE
		/** @var WireData $rate */
		$rate = $this->pwcommerce->getShippingRateByID($orderSelectedShippingRateID);

		if (is_null($rate)) {
			// we didn't get shipping rate!
			// TODO RETURN ERROR SHIPPING RATE NOT FOUND (I.E. THE PAGE ITSELF NOT FOUND)

			$error = $this->_('Shipping Rate not found.');
			$response->message = $error;
			$response->success = false;
			return $response;
		}

		// -----
		// GOOD TO GO.
		// we need to update order
		$orderPage = $this->getOrderPage();
		$order = $this->getOrder();

		// TODO DO WE STILL NEED THESE TO BE SET HERE? WE SEEM TO BE OVERWRITING $order later!
		// TODO THIS HAS TO BE SET AGAIN AND CALCULATED SO THAT TAXES CAN BE INCLUDED! IF APPLICABLE
		$order->isShippingFeeNeedsCalculating = true;
		$order->selectedMatchedShippingRate = $rate->shippingRate;
		// ###########
		// @note: new for PWCommerce === 004
		$order->shippingRateName = $rate->shippingRateName;
		$order->shippingRateDeliveryTimeMinimumDays = $rate->shippingRateDeliveryTimeMinimumDays;
		$order->shippingRateDeliveryTimeMaximumDays = $rate->shippingRateDeliveryTimeMaximumDays;


		// ###########
		// -----------
		// NOTE: This will add the shipping fee for selected rate to the order
		// it will also recompute order total price
		// handling fee values will not be affected
		/** @var WireData $order */
		// $order = $this->getOrderCalculatedShippingValues($orderPage);
		$order = $this->reprocessOrderValuesAfterShippingConfirmation($orderPage);
		// SET TO ORDER
		$orderPage->set(PwCommerce::ORDER_FIELD_NAME, $order);

		// SAVE THE ORDER AGAIN!
		// after changes above
		$orderPage->of(false);
		$orderPage->save();
		$this->setOrderPage($orderPage);

		// change session tracker of multiple shipping rates
		// tell it single rate matched since one has now been selected
		$this->session->set('isMatchedMultipleShippingRates', false);
		// set the ID of the matched shipping rate
		$this->session->set('selectedMatchedShippingRateID', $rate->shippingRateID);

		// TODO DELETE IF NOT IN USE
		// -------------
		// if (!empty($isCustomForm)) {
		// 	// CUSTOM FORM
		// } else {
		// 	// USUAL FORM
		// }

		$response->success = true;
		// ---------
		return $response;
	}

	/**
	 *    check Custom Order Customer Form For Errors.
	 *
	 * @param mixed $customFormFields
	 * @return mixed
	 */
	protected function ___checkCustomOrderCustomerFormForErrors($customFormFields) {
		// TODO MAKE THIS HOOKABLE SO DEVS CAN CHECK FOR ERRORS FOR THEIR CUSTOM INPUTS AS WELL! E.G. VALID VAT NUMBER!????
		//
		// expected schema ->
		// @note: input names (input_name) MUST MATCH FieldtypePWCommerceOrderCustomer properties
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
		$post = $this->wire('input')->post;
		foreach ($customFormFields as $customFormField) {
			$inputType = $customFormField['type'];
			$value = $post->get($customFormField['input_name']);
			$required = isset($customFormField['required']) ? $customFormField['required'] : false;
			$cleanValue = $this->getCleanedFormValue($value, $inputType);
			// check required is not empty (if text or email)
			// TODO: for now, we don't handle integer_bool (e.g. checkbox)
			// if (!empty($required) && empty($cleanValue) && !in_array($inputType, ['integer', 'float'])) {
			if (!empty($required) && empty($cleanValue) && !in_array($inputType, ['integer_bool'])) {
				$formErrors[] = $customFormField['input_name'];
			}
		}

		// -------
		return $formErrors;
	}

	/**
	 * Get Cleaned Form Value.
	 *
	 * @param mixed $value
	 * @param mixed $inputType
	 * @return mixed
	 */
	private function getCleanedFormValue($value, $inputType) {
		$cleanedValue = null;
		$sanitizer = $this->wire('sanitizer');
		// TODO: FOR NOW; WE ONLY HANDLE EMAIL, TEXT, INTEGER or FLOAT
		// TODO DO WE NEED TO CONSIDER MULTIPLE CHECKBOXES? MONDAY 26 JUNE 2023, 18:18
		if ($inputType == 'email') {
			// clean email
			$cleanedValue = $sanitizer->email($value);
		} else if ($inputType == 'integer') {
			// clean integer
			$cleanedValue = (int) $value;
		} else if ($inputType == 'float') {
			// clean float
			$cleanedValue = (float) $value;
		} else {
			// clean text, textarea
			// ------
			// just being extra careful
			$cleanedValue = $sanitizer->purify($value);
			$cleanedValue = $this->sanitizer->text($value);
		}
		// ------
		return $cleanedValue;
	}
}
