<?php

namespace ProcessWire;

trait TraitPWCommerceActionsPayment
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PAYMENT ~~~~~~~~~~~~~~~~~~

	/**
	 * Action Payment Providers.
	 *
	 * @return mixed
	 */
	private function actionPaymentProviders() {

		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!

		// TODO @note -> errors not getting caught for now in our non-tabs forms! e.g. tax settings or payment provider, so, will need to check missing required values ourselves. for tab-based forms, e.g. general settings, $form->getErrors() works fine.
		//------------------
		// good to go
		$input = $this->actionInput;
		// TODO: MAKE SURE TO ADD THIS INPUT+NAME!
		// $paymentProviderName = $input->pwcommerce_payment_provider_settings_name;
		$paymentProviderID = (int) $input->pwcommerce_payment_provider_page_id;
		// get the payment provider page
		$page = $this->wire('pages')->get($paymentProviderID);

		// we didn't get the page; abort
		// TODO: meaningful error? e.g. payment provider page not found?
		if (empty($page->id)) {
			return null;
		}

		// process the settings
		$sanitizer = $this->wire('sanitizer');

		// expected payment provider settings inputs and types
		// TODO NEED TO ADD THIS FOR CUSTOM PAYMENT PROVIDERS!
		// @note: these are unique per payment provider, so we get the info from a hidden field
		$paymentProviderSchemaInputs = explode(",", $input->pwcommerce_payment_provider_schema_inputs_names_and_types);

		// we didn't get the schema; abort
		// TODO: meaningful error? e.g. payment provider inputs not found?
		if (empty($paymentProviderSchemaInputs)) {
			return null;
		}

		// process inputs
		// @note: TODO: for now only accept text, email and checkboxes
		$paymentProviderSettings = [];
		foreach ($paymentProviderSchemaInputs as $inputNameAndType) {
			// @note: input name and type are coming in as pipe-separated pairs
			// TODO - @SEE ABOVE GETERRORS ISSUE; MIGHT NEED TO ADD required value AS PART OF THE | here
			list($inputName, $inputType) = explode("|", $inputNameAndType);
			// @note: $inputName === $property!
			// email TODO: show error invalid email?
			if ($inputType === 'email') {
				$value = $sanitizer->email($input->$inputName);
			} else if ($inputType === 'checkbox') {
				// checkboxes saved as bool int 0/1
				$value = (int) $input->$inputName;
			} else {
				// text sanitise all else
				$value = $sanitizer->text($input->$inputName);
			}
			//---------
			$paymentProviderSettings[$inputName] = $value;
		}

		// ---------------

		// prepare the JSON string to save as checkout settings
		$paymentProviderSettingsJSON = json_encode($paymentProviderSettings);
		// assign to settings field
		$page->pwcommerce_settings = $paymentProviderSettingsJSON;
		//-------------
		// save the page's 'pwcommerce_settings' field
		$page->save('pwcommerce_settings');

		// --------------------
		// prepare messages
		$notice = sprintf(__("Saved settings for %s."), $page->title);

		$result = [
			'notice' => $notice,
			'notice_type' => 'success',
			// TODO? check if really saved first?
			'special_redirect' => "/edit/?id={$page->id}"
		];

		//-------
		return $result;
	}

}
