<?php

namespace ProcessWire;


trait TraitPWCommerceActionsCustomer
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CUSTOMER ~~~~~~~~~~~~~~~~~~

	/**
	 * Add New Customer Action.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	private function addNewCustomerAction($input) {

		// TODO CONFIRM STILL NEEDED?
		// $this->actionInput = $input;

		$result = [
			'notice' => $this->_('Error encountered. Could not add new customer.'),
			'notice_type' => 'error',
		];

		// for errors
		$errors = [];

		# ++++++++++++++
		// ERROR CHECKING

		$sanitizer = $this->wire('sanitizer');

		// customer email
		$customerEmail = $input->get('pwcommerce_add_new_item_email');
		// @note: prefix '_' in input name!
		$customerConfirmEmail = $input->get('_pwcommerce_add_new_item_email_confirm');

		// customer names
		$customerFirstName = $sanitizer->text($input->get('pwcommerce_add_new_item_first_name'));
		$customerMiddleName = $sanitizer->text($input->get('pwcommerce_add_new_item_middle_name'));
		$customerLastName = $sanitizer->text($input->get('pwcommerce_add_new_item_last_name'));

		//---------

		// ERROR: INVALID EMAIL ADDRESS(ES)
		$isValidCustomerEmail = $this->isValidEmailAddress($customerEmail);
		$isValidCustomerConfirmEmail = $this->isValidEmailAddress($customerConfirmEmail);

		if (empty($isValidCustomerEmail) || empty($isValidCustomerConfirmEmail)) {
			// @note: also handled in JS so unlikely to hit this
			$error = sprintf(__('An invalid email address was specified. Please check %1$s and %2$s.'), $customerEmail, $customerConfirmEmail);
			$errors[] = $error;
		}

		// ERROR: UNMATCHED EMAIL ADDRESSES
		$isMatchedEmailAddresses = $this->isMatchedEmailAddresses($customerEmail, $customerConfirmEmail);
		if (empty($isMatchedEmailAddresses)) {
			// @note: also handled in JS so unlikely to hit this
			$error = sprintf(__('Email address %1$s does not match %2$s.'), $customerEmail, $customerConfirmEmail);
			$errors[] = $error;
		}

		// ERROR: DUPLICATE EMAIL ADDRESS
		/** @var bool $isDuplicateCustomerEmailAddress */
		$isDuplicateCustomerEmailAddress = $this->isDuplicateCustomerEmailAddress($customerEmail);

		if (!empty($isDuplicateCustomerEmailAddress)) {
			$error = sprintf(__("A customer with the email %s already exists!"), $customerEmail);
			$errors[] = $error;
		}

		$singleErrorMessage = $this->_('Could not create customer due to the following error');
		$multipleErrorsMessage = $this->_('Could not create customer due to the following errors');

		// ERROR: EMPTY FIRST NAME
		if (empty($customerFirstName)) {
			$error = $this->_('Customer First Name needs to be specified');
			$errors[] = $error;
		}

		// ERROR: EMPTY LAST NAME
		if (empty($customerLastName)) {
			$error = $this->_('Customer Last Name needs to be specified');
			$errors[] = $error;
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

		// ---------

		$sanitizer = $this->wire('sanitizer');
		$template = $this->getContextAddNewItemTemplate(); // Template|Null
		$parent = $this->getContextAddNewItemParent(); // Page|Null

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

		// ----------------------
		// GOOD TO GO: CREATE NEW CUSTOMER

		// temporary page title for customer
		// @note: later in InputfieldCustomer::processInput we will give it a friendlier page title and name
		$title = sprintf(__("Customer: %d"), time());
		// $name = $sanitizer->pageName($title);
		// @SEE NOTES ABOUT $beautify!
		$name = $sanitizer->pageName($title, true);

		$page = new Page();
		$page->template = $template;
		$page->parent = $parent;
		$page->title = $title;
		$page->name = $name;

		// unpublish page on save (i.e., NO Save + Publish button)
		if (empty((int) $input->pwcommerce_save_and_publish_new_button)) {
			$page->addStatus(Page::statusUnpublished);
		}

		// run extra operations
		// save new customer email, first and last name to customer field
		// also see below if registering an account for them
		$customer = new WireData();
		$customer->set('email', $customerEmail);
		$customer->set('firstName', $customerFirstName);
		$customer->set('middleName', $customerMiddleName);
		$customer->set('lastName', $customerLastName);
		$page->set(PwCommerce::CUSTOMER_FIELD_NAME, $customer);

		//------------------
		// SAVE the new page
		$page->save();

		// error: could not save page for some reason
		if (!$page->id) {
			$result['notice'] = $this->_('An error prevented the page from being created!');
			return $result;
		}

		// update page title (to 'Customer First Name Last Name')
		$this->updateCustomerTitle($customer, $page);

		// IF CREATING AN ACCOUNT (REGISTERING) CUSTOMER
		// NOTE we SET 'pwcommerce_email_customer_customer_id' FOR reuse and convenience in $this->getCustomerPage() which will be called by $this->emailCustomerRegistrationRequest()

		// are we creating a new customer account as well?
		$isCreateCustomerAccount = $this->isCreateCustomerAccount();

		$extraNotice = '';
		if (!empty($isCreateCustomerAccount)) {
			$this->actionInput->set('pwcommerce_email_customer_customer_id', $page->id);

			// SEND REGISTRATION EMAIL
			// NOTE this will also a ProcessWire user for this new customer as well as send them an email
			// in addition, will set userID to customer field in the customer page
			$registrationResult = $this->emailCustomerRegistrationRequest();

			// NOTE DEVS STILL HANDLES REGISTRATION! WE PASS A RANDOM PASS TO 'customer-registration-request-email-content-html.php', the new user, etc. DEV CAN THEN DECIDE WHETHER TO SEND the TEMP PASS TO NEW CUSTOMER OR REVOKE, ETC. I.E. SEND REGISTRATION PAGE INSTEAD, etc

			// HANDLE EMAIL SENT NOTICES!
			// NOTE can be success or error
			$extraNotice = $registrationResult['notice'];

			// note: $registrationResult has info about registration email and user creation outcomes
			if ($registrationResult['notice_type'] === 'error') {
				$error = $this->_('Could not send email registration request to new customer.');
				$error .= " " . $extraNotice;
				$this->error($error);
				// empty the 'extra notice' so it doesn't appear in success message
				$extraNotice = '';
			}

			// note if success,

		}

		// --------------------
		// prepare messages

		$notice = sprintf(__("Created a customer with email %s."), $customerEmail);

		if (!empty($extraNotice)) {
			$notice .= " " . $extraNotice;
		}

		$result = [
			'notice' => $notice,
			'notice_type' => 'success',
			// @note: needed for redirection to edit new customer
			'new_item_id' => $page->id,
		];

		//-------
		return $result;
	}

	/**
	 * Is Valid Email Address.
	 *
	 * @param mixed $emailAddress
	 * @return bool
	 */
	private function isValidEmailAddress($emailAddress) {
		$sanitizedEmailAddress = $this->wire('sanitizer')->email($emailAddress);
		return !empty($sanitizedEmailAddress);
	}

	/**
	 * Is Matched Email Addresses.
	 *
	 * @param mixed $emailAddress
	 * @param mixed $confirmEmailAddress
	 * @return bool
	 */
	private function isMatchedEmailAddresses($emailAddress, $confirmEmailAddress) {
		return $emailAddress === $confirmEmailAddress;
	}

	/**
	 * Is Duplicate Customer Email Address.
	 *
	 * @param mixed $validatedEmailAddress
	 * @return bool
	 */
	private function isDuplicateCustomerEmailAddress($validatedEmailAddress) {
		$isDuplicateCustomerEmailAddress = false;
		$template = $this->getContextAddNewItemTemplate(); // Template|Null
		$parent = $this->getContextAddNewItemParent(); // Page|Null
		$pageIDExists = $this->wire('pages')->getRaw("parent_id={$parent->id},template=$template,pwcommerce_customer.email={$validatedEmailAddress}", 'id');
		// error: child page under this parent already exists
		if (!empty($pageIDExists)) {
			// CHILD PAGE ALREADY EXISTS!
			$isDuplicateCustomerEmailAddress = true;
		}
		return $isDuplicateCustomerEmailAddress;
	}

	/**
	 * Is Create Customer Account.
	 *
	 * @return bool
	 */
	private function isCreateCustomerAccount() {
		$isCreateCustomerAccount = (int) $this->actionInput->get('pwcommerce_add_new_item_customer_create_account');
		return !empty($isCreateCustomerAccount);
	}

	/**
	 * Create New User For Customer.
	 *
	 * @param mixed $customerEmail
	 * @param mixed $tempPass
	 * @return mixed
	 */
	private function createNewUserForCustomer($customerEmail, $tempPass) {

		$result = [
			'notice' => $this->_('Error encountered. No action was taken.'),
			'notice_type' => 'error',
		];

		$u = new User();
		$u->email = $customerEmail;
		$u->pass = $tempPass;
		$u->save();

		if ($u instanceof NullPage) {
			$result['notice'] = $this->_('Could not create a user account for customer. Please retry.');
			return $result;
		}

		// give user 'pwcommerce-customer' role
		$u->addRole(PwCommerce::CUSTOMER_ROLE_NAME);
		$u->save();

		$result = [
			'notice' => $this->_('Created user account for new customer.'),
			'notice_type' => 'success',
			'user' => $u
		];

		return $result;

	}

	/**
	 * Set New User To Customer.
	 *
	 * @param Page $page
	 * @param User $newUser
	 * @return void
	 */
	private function setNewUserToCustomer(Page $page, User $newUser): void {
		$customer = $page->get(PwCommerce::CUSTOMER_FIELD_NAME);
		$customer->set('userID', $newUser->id);
		$page->set(PwCommerce::CUSTOMER_FIELD_NAME, $customer);
		$page->save(PwCommerce::CUSTOMER_FIELD_NAME);
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ EMAIL CUSTOMER  ~~~~~~~~~~~~~~~~~~

	/**
	 * Action Send Email Customer.
	 *
	 * @return mixed
	 */
	private function actionSendEmailCustomer() {
		$input = $this->actionInput;
		$customerRegistrationRequest = (int) $input->pwcommerce_customer_registration_request_button;
		if (!empty($customerRegistrationRequest)) {
			// HANDLE CUSTOMER REGISTRATION REQUEST
			return $this->emailCustomerRegistrationRequest();
		} else {
			// HANDLE EMAIL CUSTOMER REQUEST
			return $this->emailCustomer();
		}

	}

	/**
	 * Email Customer.
	 *
	 * @return mixed
	 */
	private function emailCustomer() {

		$result = [
			'notice' => $this->_('Error encountered. No action was taken.'),
			'notice_type' => 'error',
		];

		$sanitizer = $this->wire('sanitizer');
		$pages = $this->wire('pages');
		$input = $this->actionInput;

		$customerPage = $this->getCustomerPage();

		// -------------------
		// ERROR CHECKING

		// ERROR: customer page not found for some reason
		if ($customerPage instanceof NullPage) {
			$result['notice'] = $this->_('Could not find customer page. No action was taken.');
			return $result;
		}

		// SET REDIRECT FOR LATER USE
		$result['special_redirect'] = $this->getSpecialRedirectParamString($customerPage);

		$customer = $customerPage->get(PwCommerce::CUSTOMER_FIELD_NAME);
		$customerEmail = $customer->email;

		// use shop's 'FROM EMAIL ADDRESS' if available
		$shopEmail = $this->pwcommerce->getShopFromEmail();
		if (empty($shopEmail)) {
			$shopEmail = $this->pwcommerce->getShopEmail();
		}

		$emailSubject = $sanitizer->text($input->pwcommerce_email_customer_email_subject);
		$emailBody = $sanitizer->purify($input->pwcommerce_email_customer_email_body);
		$errors = [];
		// ERROR: email subject not specified
		if (empty($emailSubject)) {
			$errors[] = $this->_('Missing email subject');

		}
		// ERROR: email body not specified
		if (empty($emailBody)) {
			$errors[] = $this->_('Missing email content');
		}

		if (!empty($errors)) {
			$notice = sprintf(__("There were errors.  Please fill these missing values: %s."), implode(', ', $errors));
			$result['notice'] = $notice;
			return $result;
		}

		// ==========
		// GOOD TO GO

		######## prepare email variables ######

		$emailOptions = [];

		// $mail->from($shopEmail);
		// TODO: CHANGE THIS TO USE SPECIAL 'FROM' EMAIL ADDRESS; SEE ISSUE IN FORUMS ABOUT SENDING EMAIL TO SELF?!
		// @see: https://processwire.com/talk/topic/28339-should-order-confirmation-emails-also-be-received-by-store/
		$emailOptions['from'] = $shopEmail;
		$emailOptions['to'] = $customerEmail;
		$emailOptions['subject'] = $emailSubject;
		$emailOptions['bodyHTML'] = $emailBody;
		$emailOptions['body'] = $emailBody; // @note: for now, we just send HTML

		// ====================
		// TODO HANDLE ERRORS HERE?
		/** @var array $result */
		$emailResult = $this->pwcommerce->sendEmail($emailOptions);

		$isSentEmail = $emailResult['notice_type'] === 'success';
		// --------------------
		// NOTICES
		if ($isSentEmail) {
			// single edit notice
			$notice = sprintf(__("Sent email to %s."), $customerEmail);
			$noticeType = 'success';
		} else {
			// prepare messages for bulk edit
			$notice = sprintf(__("Could not send email to %s."), $customerEmail);
			$noticeType = 'error';
		}

		// PREPARE RESULTS
		$result['notice'] = $notice;
		$result['notice_type'] = $noticeType;

		// --------
		return $result;
	}

	/**
	 *    email Customer Registration Request.
	 *
	 * @return mixed
	 */
	protected function ___emailCustomerRegistrationRequest() {

		$result = [
			'notice' => $this->_('Error encountered. No action was taken.'),
			'notice_type' => 'error',
		];
		// TODO

		$customerPage = $this->getCustomerPage();
		// -------------------
		// ERROR CHECKING

		// ERROR: customer page not found for some reason
		if ($customerPage instanceof NullPage) {
			$result['notice'] = $this->_('Could not find customer page. No action was taken.');
			return $result;
		}

		// SET REDIRECT FOR LATER USE
		$result['special_redirect'] = $this->getSpecialRedirectParamString($customerPage);

		/** @var WireData $customer */
		$customer = $customerPage->get(PwCommerce::CUSTOMER_FIELD_NAME);
		$customerEmail = $customer->email;


		$shopGeneralSettings = $this->pwcommerce->getshopGeneralSettings();
		$shopName = $shopGeneralSettings->shop_name;
		// TODO CREATE NEW PW USER AS WELL! DO WE NEED TO SEND USER/USER ID TO TEMPLATE? MAYBE!
		$password = new Password();
		$tempPass = $password->randomPass();
		$newUserResult = $this->createNewUserForCustomer($customerEmail, $tempPass);
		// TODO HANDLE ERRORS! E.G. USER NOT CREATED FOR SOME REASON!
		if ($newUserResult['notice_type'] === 'error') {
			// TODO RETURN EARLY!!!
			$result['notice'] = $newUserResult['notice'];
			return $result;
		}

		$newUser = $newUserResult['user'];

		// TODO BUILD EMAIL OPTIONS; EMAIL BODY COMES FROM TEMPLATE RENDER
		$t = $this->pwcommerce->getPWCommerceTemplate("customer-registration-request-email-content-html.php");
		$t->set("customer", $customer);
		$t->set("shopName", $shopName);
		$t->set("tempPass", $tempPass);
		$t->set("newUser", $newUser);
		$emailBody = $t->render();

		// use shop's 'FROM EMAIL ADDRESS' if available
		$shopEmail = $this->pwcommerce->getShopFromEmail();
		if (empty($shopEmail)) {
			$shopEmail = $this->pwcommerce->getShopEmail();
		}

		$errors = [];

		// ERROR: email body not specified
		if (empty(trim($emailBody))) {
			$errors[] = $this->_('Missing email content');
		}

		if (!empty($errors)) {
			$notice = sprintf(__("There were errors.  Please fill these missing values: %s."), implode(', ', $errors));
			$result['notice'] = $notice;
			// DELETE THE USER!
			// TODO OK?
			$this->wire('users')->delete($newUser);
			return $result;
		}

		// ==========
		// GOOD TO GO

		######## prepare email variables ######

		$emailOptions = [];

		// EMAIL SUBJECT
		$emailSubject = $this->emailCustomerRegistrationRequestSubject($shopName);

		// $mail->from($shopEmail);
		// TODO: CHANGE THIS TO USE SPECIAL 'FROM' EMAIL ADDRESS; SEE ISSUE IN FORUMS ABOUT SENDING EMAIL TO SELF?!
		// @see: https://processwire.com/talk/topic/28339-should-order-confirmation-emails-also-be-received-by-store/
		$emailOptions['from'] = $shopEmail;
		$emailOptions['to'] = $customerEmail;
		$emailOptions['subject'] = $emailSubject;
		$emailOptions['bodyHTML'] = $emailBody;
		$emailOptions['body'] = $emailBody; // @note: for now, we just send HTML

		// ====================
		/** @var array $result */
		$emailResult = $this->pwcommerce->sendEmail($emailOptions);

		$isSentEmail = $emailResult['notice_type'] === 'success';
		// --------------------
		// NOTICES
		if ($isSentEmail) {
			// SUCCESS
			// registration email sent successfully notice
			$notice = sprintf(__("Sent customer registration request email to %s."), $customerEmail);
			$noticeType = 'success';
			// -----
			// set user_id to customer and save
			$this->setNewUserToCustomer($customerPage, $newUser);
			// -------
			// we grab saved 'customer' since we need its userID
			$customer = $customerPage->get(PwCommerce::CUSTOMER_FIELD_NAME);
			// update user 'name' (to 'customer-first-name-last-name')
			$this->updateCustomerUserName($customer, $customerPage);
		} else {
			// prepare messages for bulk edit
			$notice = sprintf(__("Could not send customer registration request email to %s."), $customerEmail);
			$noticeType = 'error';
			// DELETE THE USER!
			// TODO OK?
			$this->wire('users')->delete($newUser);
		}

		// PREPARE RESULTS
		$result['notice'] = $notice;
		$result['notice_type'] = $noticeType;
		$result['user'] = $newUser;

		// --------
		return $result;

	}

	/**
	 *    email Customer Registration Request Subject.
	 *
	 * @param mixed $shopName
	 * @return mixed
	 */
	protected function ___emailCustomerRegistrationRequestSubject($shopName) {
		// TODO MAKE CONFIGURABLE! or hookable!
		if (!empty($shopName)) {
			$emailSubject = sprintf(__("%s Customer Account Registration"), $shopName);
		} else {
			$emailSubject = $this->_('Customer Account Registration');
		}
		return $emailSubject;
	}

	/**
	 * Get Customer Page.
	 *
	 * @return mixed
	 */
	private function getCustomerPage() {
		$input = $this->actionInput;
		$customerPageID = (int) $input->pwcommerce_email_customer_customer_id;
		$customerPage = $this->wire('pages')->get("template=" . PwCommerce::CUSTOMER_TEMPLATE_NAME . ",id={$customerPageID}");
		// -------
		return $customerPage;
	}

	/**
	 * Get Special Redirect Param String.
	 *
	 * @param mixed $customerPage
	 * @return mixed
	 */
	private function getSpecialRedirectParamString($customerPage) {
		$specialRedirect = "/view/?id={$customerPage->id}";
		// -------
		return $specialRedirect;
	}

	// ALSO USED BY InputfieldPWCommerceCustomer

	/**
	 * Update Customer Title.
	 *
	 * @param mixed $customer
	 * @param Page $page
	 * @return void
	 */
	protected function updateCustomerTitle($customer, $page): void {
		$languages = $this->wire('languages');
		$sanitizer = $this->wire('sanitizer');

		// --------
		$customerPageTitle = "{$customer->firstName} {$customer->lastName}";
		$customerPageTitle = $sanitizer->text($customerPageTitle);
		$customerPageName = $sanitizer->pageName($customerPageTitle, true);

		// get title to compare if changes
		if ($languages) {
			// note: for customer, all language titles are identical. This is because we build from customer names
			$currentTitle = $page->title->getLanguageValue($languages->getDefault());
		} else {
			$currentTitle = $page->title;
		}

		if ($currentTitle !== $customerPageTitle) {
			// title has changed
			// +++
			// determine if multilingual title or single
			if ($languages) {
				// modify language names
				foreach ($languages as $language) {
					$page->title->setLanguageValue($language, $customerPageTitle);
					// ------
					// set name
					if ($language->name == 'default') {
						// $page->set('name', $customerPageName);
						$page->setName($customerPageName);
					} else {
						// $page->set("name$language", $customerPageName);
						$page->setName($customerPageName, $language);
					}

				}
				$page->save();
			} else {
				// modify single title and name
				$page->setAndSave(
					[
						'title' => $customerPageTitle,
						'name' => $customerPageName
					],
				);
			}

		}
	}

	/**
	 * Update Customer User Name.
	 *
	 * @param WireData $customer
	 * @param Page $page
	 * @return void
	 */
	private function updateCustomerUserName(WireData $customer, Page $page): void {
		if (empty($customer->userID)) {
			return;
		}
		// -------
		$languages = $this->wire('languages');
		$sanitizer = $this->wire('sanitizer');

		// --------
		$customerPageTitle = "{$customer->firstName} {$customer->lastName}";
		$customerPageTitle = $sanitizer->text($customerPageTitle);
		$customerPageName = $sanitizer->pageName($customerPageTitle, true);

		// get title to compare if changes
		if ($languages) {
			// note: for customer, all language titles are identical. This is because we build from customer names
			$currentTitle = $page->title->getLanguageValue($languages->getDefault());
		} else {
			$currentTitle = $page->title;
		}

		// get the user associated with this customer
		$user = $this->wire('users')->get($customer->userID);

		$isDefaultGenericUnserName = str_contains($user->name, 'untitled-');

		if (($currentTitle !== $customerPageTitle) || $isDefaultGenericUnserName) {
			// title has changed
			// +++

			if ($user instanceof NullPage) {
				return;
			}

			// GOOD TO GO
			$user->name = $customerPageName;
			$user->save();
		}

	}

}
