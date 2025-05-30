<?php

namespace ProcessWire;

trait TraitPWCommerceActionsGiftCard
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ GIFT CARD ~~~~~~~~~~~~~~~~~~

	public function manuallyIssueGiftCard($input) {
		$result = [
			'notice' => $this->_('Error encountered. No action was taken.'),
			'notice_type' => 'error',
		];
		// if no action context, return
		if (!$this->actionContext) {
			return $result;
		}

		// @note: we hand over to the gift cards class
		// it will determine the type of acion needed @NOTE: MIGHT CHANGE IN FUTURE IN CASE OF SALE AND REDEEMING OF GIFT CARDS!:
		$giftcard = $this->pwcommerce->getPWCommerceClassByName('PWCommerceGiftCards', $this->options);
		$response = $giftcard->processGiftCardAction($input);

		//-------------
		// set result/response as established by action method
		if (!empty($response)) {
			// --------------------
			// TODO: NO NOTICES FOR NOW SINCE IN AJAX MODE! but we send just in case changes in future
			// prepare messages
			$result = [
				'notice' => $response,
				'notice_type' => 'success',
			];
		}
		//-------------
		return $result;
	}

	private function sendGiftCardCodeToCustomer(array $options) {

		$shopGeneralSettings = $this->pwcommerce->getshopGeneralSettings();
		$shopName = $shopGeneralSettings->shop_name;
		// $shopEmail = $shopGeneralSettings->shop_email();
		// $shopEmail = $this->pwcommerce->getShopEmail();
		// use shop's 'FROM EMAIL ADDRESS' if available
		$shopEmail = $this->pwcommerce->getShopFromEmail();
		if (empty($shopEmail)) {
			$shopEmail = $this->pwcommerce->getShopEmail();
		}

		if (!empty($shopName)) {
			$emailSubject = sprintf(__("Your Gift Card from %s"), $shopName);
		} else {
			$emailSubject = $this->_('Your Gift Card');
		}

		// TODO CHANGE THIS
		$t = $this->pwcommerce->getPWCommerceTemplate("gift-card-code-email-content-html.php");
		$t->set("denomination", $options['denomination']);
		$t->set("shopName", $shopName);
		$t->set("giftCardCode", $options['code']);
		$t->set("endDate", $options['end_date']);
		$emailBody = $t->render();

		######## prepare email variables ######

		$emailOptions = [];

		// $mail->from($shopEmail);
		// TODO: CHANGE THIS TO USE SPECIAL 'FROM' EMAIL ADDRESS; SEE ISSUE IN FORUMS ABOUT SENDING EMAIL TO SELF?!
		// @see: https://processwire.com/talk/topic/28339-should-order-confirmation-emails-also-be-received-by-store/
		$emailOptions['from'] = $shopEmail;
		$emailOptions['to'] = $options['customer_email'];
		$emailOptions['subject'] = $emailSubject;
		$emailOptions['bodyHTML'] = $emailBody;
		$emailOptions['body'] = $emailBody; // @note: for now, we just send HTML

		// ====================
		// TODO HANDLE ERRORS HERE?
		/** @var array $result */
		$result = $this->pwcommerce->sendEmail($emailOptions);

		// --------
		return $result;
	}


	/**
	 * Create and manually issue a new gift card.
	 *
	 *
	 * @access private
	 * @param WireInputData $input
	 * @return array $result Outcome of the creation action.
	 */
	private function addNewManualIssueGiftCardAction($input) {

		$result = [
			'notice' => $this->_('Error encountered. Could not issue gift card.'),
			'notice_type' => 'error',
		];

		$sanitizer = $this->wire('sanitizer');

		$template = $this->getContextAddNewItemTemplate(); // Template|Null
		$parent = $this->getContextAddNewItemParent(); // Page|Null

		// for errors
		$errors = [];
		$singleErrorMessage = $this->_('Could not send Gift Card due to the following error');
		$multipleErrorsMessage = $this->_('Could not send Gift Card due to the following errors');

		/*	PRE-CHECKS+ SUITABLE ERRORS
										1. CHECK IF WE HAVE CODE
										2. CHECK THAT NO GIFT CARD WIH IDENTICAL CODE > CAN USE GETRAW FOR THIS
										3. CHECK EMAIL NOT EMPTY
										4. CHECK DENOMINATION SPECIFIED > IF PREDEFINED (RADIO), CHECK SELECT; IF CUSTOM(RADIO), CHECK TEXT INPUT
										5. CHECK END DATE (TEXT) IF WE GOT RADIO 'SET EXPIRATION DATE'
										TODO IF ERROR CAN WE RETURN ABOVE POST VALUES?
												-------
											GOOD TO GO
											1. CREATE GC PAGE + PUBLISHED?
										 > IF ISSUE, SHOW ERROR?
											2. POPULATE ITS FIELDS: GC FIELD AND GC ACTIVITIES FIELD
										IF ADMIN NOTE SENT, POPULATE ADMIN (SANITIZE TEXTAREA OR ENTITIES? @SEE PREVIOUS)
										3. SEND EMAIL
										 USE UTILITIES FOR THIS
										- GRAB BODY/CONTENT FROM TEMPLATE FILE PARITAL
										- GET THE GENERIC SUBJECT > USE SHOPNAME IF AVAILABLE
										- IF ISSUE???
										########
										RETURN RESULTS
										*/

		// error: template not found
		if (!$template) {
			$result['notice'] = $this->_('Required template not found!');
			return $result;
		}

		// error: parent not found
		if (!$parent) {
			$result['notice'] = $this->_('Parent page not found!');
			return $result;
		}

		$code = $sanitizer->text($input->pwcommerce_issue_gift_card_code);
		// error: code not sent
		if (empty($code)) {
			$errors[] = $this->_('Gift Card code is not valid.');
		}

		// error: identical code found
		$pageIDExists = $this->wire('pages')->getRaw("parent_id={$parent->id},pwcommerce_gift_card.code=$code", 'id');
		if (!empty($pageIDExists)) {
			// CHILD PAGE ALREADY EXISTS!
			$errors[] = $this->_('A Gift Card with an identical code already exists.');
		}

		$customerEmail = $sanitizer->email($input->pwcommerce_issue_gift_card_customer_email);
		// error: customer email empty
		if (empty($customerEmail)) {
			$errors[] = $this->_('Customer email is not valid.');
		}

		$denomination = 0;
		// error: no denomination (pre-defined or custom)
		if ($input->pwcommerce_issue_gift_card_denomination_mode === 'pre_defined') {
			// pre-defined denomination
			$denomination = (float) $input->pwcommerce_issue_gift_card_denomination_pre_defined;
		} else {
			// custom denomination
			$denomination = (float) $input->pwcommerce_issue_gift_card_denomination_custom;
		}
		if (empty($denomination)) {
			$errors[] = $this->_('Gift Card denomination is not valid.');
		}

		$endDate = NULL;
		// error: end date in use but invalid end date (empty OR in the past)
		if ($input->pwcommerce_issue_gift_card_set_expiration_date === 'set_expiration') {
			$endDate = $input->pwcommerce_issue_gift_card_end_date;
			// end date in use
			$endDateTimestamp = strtotime($endDate);
			if (empty($endDateTimestamp)) {
				$errors[] = $this->_('Gift Card expiration date is empty.');
			} elseif ($endDateTimestamp < time()) {
				$errors[] = $this->_('Gift Card expiration date is in the past.');
			}
		}

		// +++++++
		// IF ERRORS: IMPLODE AND SEND BACK
		if (!empty($errors)) {
			// build errors string
			$errorsString = implode(', ', $errors);
			$errorsCount = count($errors);

			$errorMessage = $errorsCount > 1 ? $multipleErrorsMessage : $singleErrorMessage;
			$notice = ": " . sprintf(__('%1$s: %2$s.'), $errorMessage, $errorsString);

			$result['notice'] = $notice;

			return $result;
		}

		// ----------------------
		// ~~~ PROCEED ~~~

		// AUTO GIFT CARD PAGE TITLE
		// TODO DO WE ALSO ADD LAST FOUR DIGITS? DISCLOSIVE?
		// TODO: is this ok or need microtime?
		$title = sprintf(__("Gift Card Code: %d"), time());
		// first check if page already exists (under this parent)
		// $name = $sanitizer->pageName($title);
		// @SEE NOTES ABOUT $beautify!
		$name = $sanitizer->pageName($title, true);
		$pageIDExists = $this->wire('pages')->getRaw("parent_id={$parent->id},name=$name", 'id');
		// TODO: TEST THIS!
		// error: child page under this parent already exists
		if (!empty($pageIDExists)) {
			// CHILD PAGE ALREADY EXISTS!
			$notice = sprintf(__("A page with the title %s already exists!"), $title);
			$result['notice'] = $notice;
			return $result;
		}

		//---------
		// GOOD TO GO!
		$page = new Page();
		$page->template = $template;
		$page->parent = $parent;
		$page->title = $title;
		$page->name = $name;

		// run extra operations
		$adminNote = $sanitizer->sanitize($input->pwcommerce_issue_gift_card_admin_note, 'textarea,entities');
		// ---------
		$options = [
			'customer_email' => $customerEmail,
			'denomination' => $denomination,
			'code' => $code,
			'end_date' => $endDate,
			// ----------
			'admin_note' => $adminNote
		];
		// ----
		$page = $this->runGiftCardExtraAddNewItemOperations($page, $options);

		//------------------
		// SAVE the new page
		$page->save();

		// error: could not save page for some reason
		if (!$page->id) {
			$result['notice'] = $this->_('An error prevented the page from being created!');
			return $result;
		}

		# SEND EMAIL TO CUSTOMER! #
		// TODO BETTER HANDLER? DO WE DELETE THE PAGE?
		$sent = $this->sendGiftCardCodeToCustomer($options);

		if ($sent['notice_type'] === 'error') {

			// DELETE GIFT CARD PAGE SINCE CODE WAS NOT SENT
			// new code needs to be generated and a new page created
			$page->delete();

			$errors = $sent['errors'];
			// build errors string
			$errorsString = implode(', ', $errors);
			$errorsCount = count($errors);

			$errorMessage = $errorsCount > 1 ? $multipleErrorsMessage : $singleErrorMessage;
			$notice = ": " . sprintf(__('%1$s: %2$s'), $errorMessage, $errorsString);

			$result['notice'] = $notice;
			return $result;
		}

		// --------------------
		// prepare messages

		// TODO: rephrase: item or page?
		$codeArray = explode("-", $code);
		$codeLastFourDigits = $codeArray[3];
		$notice = sprintf(__('Created a Gift Card ending in %1$s. Gift Card sent to customer with email %2$s.'), $codeLastFourDigits, $customerEmail);

		$result = [
			'notice' => $notice,
			'notice_type' => 'success',
			// TODO NOT NEEDED! CANNOT BE EDITED; ONLY VIEWED; SINCE NEW, JUST RELOAD THE BULK GC ITEMS PAGE!
			// 'new_item_id' => $page->id, // @note: needed for redirection to edit it
		];

		//-------
		return $result;
	}

}
