<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Customers
 *
 * Class to render content for PWCommerce Admin Module executeCustomers().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderCustomers for PWCommerce
 * Copyright (C) 2024 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceAdminRenderCustomers extends WireData
{

	private $adminURL;

	private $isInstalledCustomerGroupsFeature;
	// current customer page
	public $customerPage;
	# ----------
	// the ALPINE JS store used by this Class
	private $xstoreProcessPWCommerce;
	// the full prefix to the ALPINE JS store used by this Class
	private $xstore;


	/**
	 *   construct.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __construct($options = null) {
		if (is_array($options)) {
			$this->adminURL = $options['admin_url'];
			$this->xstoreProcessPWCommerce = $options['xstoreProcessPWCommerce'];
			// i.e., '$store.ProcessPWCommerceStore'
			$this->xstore = $options['xstore'];
		}
		// set if customer groups feature is installed
		$customerGroupsFeature = 'customer_groups';
		$this->isInstalledCustomerGroupsFeature = !empty($this->pwcommerce->isOptionalFeatureInstalled($customerGroupsFeature));
	}

	/**
	 * Render single customer view headline to append to the Process headline in PWCommerce.
	 *
	 * @param Page $customerPage
	 * @return string|mixed
	 */
	public function renderViewItemHeadline(Page $customerPage) {
		$headline = $this->_('View customer');
		// TODO MORE? TITLE?!
		$customer = $customerPage->get(PwCommerce::CUSTOMER_FIELD_NAME);
		$customerNamesString = $this->getConcatCustomerNames($customer);
		$headline .= ": {$customerNamesString}";
		return $headline;
	}

	/**
	 * Render the markup for a single customer view.
	 *
	 * @param Page $customerPage
	 * @return string|mixed
	 */
	public function renderViewItem(Page $customerPage) {

		$this->customerPage = $customerPage;
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		$out = "";
		// get the customer by its ID
		if (!$customerPage->id) {
			// TODO: return in markup for consistency!
			$out = "<p>" . $this->_('Customer was not found!') . "</p>";
		} else {
			$out = $this->buildViewCustomer();
		}

		//----------------Customer- final markup
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			// 'classes' => 'pwcommerce_order_view',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		$wrapper->add($field);

		return $wrapper->render();
	}

	/**
	 * Build View Customer.
	 *
	 * @return mixed
	 */
	private function buildViewCustomer() {

		// TODO ADD AOV IN A FUTURE RELEASE
		$out = "";

		// NAME
		$out .= $this->getMarkupForCustomerName();
		// EMAIL
		$out .= $this->getMarkupForCustomerEmail();
		// REGISTERED/GUEST CUSTOMER
		$out .= $this->getMarkupForCustomerRegisteredStatus();
		// ACTIONS
		$out .= $this->getMarkupForCustomerActions();
		// ADDRESSES
		// TODO MOVE TO A FIELDSET!???
		$out .= $this->getMarkupForCustomerAddresses();
		// CUSTOMER ORDERS
		// TODO MOVE TO A FIELDSET!???
		// TODO IN FUTURE RELEASE, MAKE TABLE PAGINATED
		$out .= $this->getMarkupForCustomerOrders();

		// -------
		return $out;
	}

	/**
	 * Get markup of a single address for a customer.
	 *
	 * @param mixed $customerAddress
	 * @return mixed
	 */
	private function getMarkupForSingleAddressOfCustomer($customerAddress) {

		$customerAddressTypesLabels = $this->getCustomerAddressTypesLabels();
		$addressLabel = $customerAddressTypesLabels[$customerAddress->addressType];

		$customerNamesString = $this->getConcatCustomerNames($customerAddress);

		$country = $customerAddress->country ? $customerAddress->country : $this->_('country not found');
		$addressLineTwo = !empty($customerAddress->addressLineTwo) ? "<span class='block'>{$customerAddress->shippingAddressLineTwo}</span>" : '';
		$shippingAddressRegion = !empty($customerAddress->region) ? "<span class='block'>{$customerAddress->region}</span>" : '';

		$out =
			// customer shipping/primary address
			"<div class='mt-1'>" .
			"<h4 class='xpwcommerce_override_processwire_heading_margin_top mb-1'>" . $addressLabel . "</h4>" .
			"<span class='block'>{$customerNamesString}</span>" .
			"<span class='block'>{$customerAddress->addressLineOne}</span>" .
			$addressLineTwo .
			"<span class='block'>{$customerAddress->city}</span>" .
			$shippingAddressRegion .
			"<span class='block'>{$country}</span>" .
			"</div>";
		// -----
		return $out;

	}

	/**
	 * Get Customer Address Types Labels.
	 *
	 * @return mixed
	 */
	private function getCustomerAddressTypesLabels() {
		return [
			// shipping
			'shipping_primary' => $this->_('Primary Shipping Address'),
			'shipping' => $this->_('Shipping Address'),
			// billing
			'billing_primary' => $this->_('Primary Billing Address'),
			'billing' => $this->_('Billing Address')
		];
	}

	/**
	 * Get Markup For Customer Name.
	 *
	 * @return mixed
	 */
	private function getMarkupForCustomerName() {
		$customerPage = $this->customerPage;
		$customer = $customerPage->get(PwCommerce::CUSTOMER_FIELD_NAME);


		$out = "";
		$customerNamesString = $this->getConcatCustomerNames($customer);
		// edit link
		// TODO OK LIKE THIS? BUTTON like for editable order? ICON?

		if ($customerPage->isLocked()) {
			// CUSTOMER PAGE LOCKED FOR EDITS
			// TODO: ITALICS? COLOR?
			// $out = "<small>" . $this->_('Order locked for edits') . "</small>";
			// $out = "<span>" . $this->_('Customer page is locked for edits.') . "</span>";
			$customerNamesString = "{$customerNamesString}<i class='ml-1 fa fa-lock'></i>";
			$out .= "<span class='block mt-5'>{$customerNamesString}</span>";
		} else {
			$customerNamesString = "<a href='{$this->adminURL}customers/edit/?id={$customerPage->id}'>{$customerNamesString}<i class='ml-1 fa fa-pencil-square-o'></i></a>";
			$out .= "<span class='block mt-5'>{$customerNamesString}</span>";
		}

		// -------
		return $out;
	}

	/**
	 * Get Markup For Customer Email.
	 *
	 * @return mixed
	 */
	private function getMarkupForCustomerEmail() {
		$customer = $this->customerPage->get(PwCommerce::CUSTOMER_FIELD_NAME);
		$out = "<span class='block'>{$customer->email}</span>";
		// ------
		return $out;
	}

	/**
	 * Get Markup For Customer Registered Status.
	 *
	 * @return mixed
	 */
	private function getMarkupForCustomerRegisteredStatus() {
		$customerStatusTexts = $this->getCustomerStatusTexts();
		$registeredCustomer = $customerStatusTexts['customer_with_account'];
		$guestCustomer = $customerStatusTexts['guest_customer'];
		$customerType = empty($this->isRegisteredCustomer($this->customerPage)) ? $guestCustomer : $registeredCustomer;
		$out = "<span class='block mt-3 mb-5 italic'>{$customerType}</span>";
		// ------
		return $out;
	}

	/**
	 * Get Customer Status Texts.
	 *
	 * @return mixed
	 */
	private function getCustomerStatusTexts() {
		return [
			'customer_with_account' => $this->_('Customer with account.'),
			'guest_customer' => $this->_('Customer does not have an account.')
		];
	}

	/**
	 * Is Registered Customer.
	 *
	 * @param mixed $customerPage
	 * @return bool
	 */
	private function isRegisteredCustomer($customerPage) {
		$customer = $customerPage->get(PwCommerce::CUSTOMER_FIELD_NAME);
		$user = $this->wire('users')->get($customer->userID);
		// check if user actually exists!
		// return !empty($customer->userID);
		return !$user instanceof NullPage;
	}

	/**
	 * Get Markup For Customer Actions.
	 *
	 * @return mixed
	 */
	private function getMarkupForCustomerActions() {
		$out = "<div class='flex items-center mt-10 mb-5'>";
		// ACTION: EDIT CUSTOMER
		//-------
		$out .= $this->getMarkupForCustomerActionsEdit();
		// ACTION: SEND CUSTOMER REGISTRATION REQUEST
		$out .= $this->getMarkupForCustomerActionsSendRegistrationRequest();
		// ACTION: SEND CUSTOMER EMAIL
		$out .= $this->getMarkupForCustomerActionsSendEmail();

		$out .= "</div>";

		// ACTION: NOTES
		$out .= $this->getMarkupForCustomerActionsNotes();
		// ACTION: MODAL for SEND CUSTOMER EMAIL
		// append email customer modal markup
		$out .= $this->getModalMarkupForEmailCustomer();

		// ------------
		// ADD REQUIRED HIDDEN INPUT
		//------------------- is_ready_to_save (getInputfieldHidden)
		$field = $this->getHiddenMarkupForRequiredField();
		$out .= $field->render();

		// wrap it
		$out = "<div x-data='ProcessPWCommerceData'>" . $out . "</div>";

		// ------
		return $out;
	}

	/**
	 * Get Markup For Customer Actions Edit.
	 *
	 * @return mixed
	 */
	private function getMarkupForCustomerActionsEdit() {

		$out = "";
		$customerPage = $this->customerPage;
		if ($customerPage->isLocked()) {
			// TODO: ITALICS? COLOR?
			// $out = "<small>" . $this->_('Customer page locked for edits') . "</small>";
			$out = "<span class='mr-3'>" . $this->_('Customer page locked for edits.') . "</span>";
		} else {
			//------------------- edit LINK button (getInputfieldButton)
			$label = $this->_('Edit Customer');
			$options = [
				'id' => "pwcommerce_edit_customer_link_button",
				'name' => "pwcommerce_edit_customer_link_button",
				'label' => $label,
				'small' => true,
			];
			$field = $this->pwcommerce->getInputfieldButton($options);
			// on click navigate to edit this customer
			$field->href = "{$this->adminURL}customers/edit/?id={$customerPage->id}";
			//-------
			$out .= $field->render();
		}

		// ------
		return $out;

	}


	/**
	 * Get Markup For Customer Actions Send Registration Request.
	 *
	 * @return mixed
	 */
	private function getMarkupForCustomerActionsSendRegistrationRequest() {

		$out = "";
		if (empty($this->isRegisteredCustomer($this->customerPage))) {
			$customerPage = $this->customerPage;
			$customerRegistrationURL = "{$this->adminURL}customers/email-registration-request/?id={$customerPage->id}";

			$options = [
				'label' => $this->_('Customer Registration Request'),
				'name' => 'pwcommerce_customer_registration_request_button',
				'type' => 'submit',
				'collapsed' => Inputfield::collapsedNever,
				'small' => true,
				'wrapClass' => true,
				'wrapper_classes' => 'pwcommerce_no_outline',
				'secondary' => true,
				'icon' => 'user-plus'
			];

			$field = $this->pwcommerce->getInputfieldButton($options);

			$field->href = $customerRegistrationURL;
			$out = $field->render();
		}


		// ------
		return $out;

	}

	/**
	 * Get Markup For Customer Actions Send Email.
	 *
	 * @return mixed
	 */
	private function getMarkupForCustomerActionsSendEmail() {

		$options = [
			'label' => $this->_('Email Customer'),
			'collapsed' => Inputfield::collapsedNever,
			'small' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'secondary' => true,
			'icon' => 'paper-plane'
		];

		$field = $this->pwcommerce->getInputfieldButton($options);

		$field->attr([
			'x-on:click' => 'handleEmailCustomer',
		]);

		$out = $field->render();

		// ------
		return $out;
	}

	/**
	 * Get Markup For Customer Actions Notes.
	 *
	 * @return mixed
	 */
	private function getMarkupForCustomerActionsNotes() {
		$out = "";
		if (empty($this->isRegisteredCustomer($this->customerPage))) {
			// NOTE devs should handle own registration implementation, e.g. 'send temp pass' or 'registration link', etc.
			$registrationNotes = $this->_('For registration, an activation email will be sent to the customer requesting them to register an account with the shop.');
			$out = "<p class='notes'>" . $registrationNotes . "</p>";
		}

		// -----
		return $out;
	}


	/**
	 * Get Markup For Customer Addresses.
	 *
	 * @return mixed
	 */
	private function getMarkupForCustomerAddresses() {
		$customerAddresses = $this->customerPage->get(PwCommerce::CUSTOMER_ADDRESSES_FIELD_NAME);
		$out = "<hr><h3 class='pwcommerce_override_processwire_heading_margin_top'>" . $this->_('Addresses') . "</h3>";
		foreach ($customerAddresses as $customerAddress) {
			$out .= "<div>" .
				$this->getMarkupForSingleAddressOfCustomer($customerAddress) .
				"<hr></div>";
		}

		// ------
		return $out;
	}

	/**
	 * Get Markup For Customer Orders.
	 *
	 * @return mixed
	 */
	private function getMarkupForCustomerOrders() {
		$out = "<h3 class='pwcommerce_override_processwire_heading_margin_top'>" . $this->_('Latest Orders') . "</h3>";
		/** @var PageArray $customerOrders */
		$customerOrders = $this->getCustomerOrders($this->customerPage);

		/** @var array $customerOrdersTotals */
		$customerOrdersTotals = $this->getCustomerOrdersTotals($this->customerPage);
		$customerOrdersQuantity = $customerOrdersTotals['orders_quantity'];
		$customerOrdersTotal = $customerOrdersTotals['orders_total_spend'];

		$totalCustomerOrdersStr = sprintf(__('%1$s (%2$s)'), $customerOrdersQuantity, $customerOrdersTotal);
		$out .= "<p>" . $totalCustomerOrdersStr . "</p>";

		$out .= $this->getTable($customerOrders, 'customers_single_view');
		// -------
		return $out;
	}

	/**
	 * Get Customer Orders.
	 *
	 * @param mixed $customerPage
	 * @param bool $isRaw
	 * @return mixed
	 */
	private function getCustomerOrders($customerPage, bool $isRaw = false) {
		$customer = $customerPage->get(PwCommerce::CUSTOMER_FIELD_NAME);
		if (!empty($isRaw)) {
			// FIND RAW
			$fields = ['id', 'pwcommerce_order.order_total_price'];
			$customerOrders = $this->pwcommerce->findRaw("template=order,order_customer.email={$customer->email},sort=-created", $fields);
		} else {
			// FIND
			$customerOrders = $this->pwcommerce->find("template=order,order_customer.email={$customer->email},sort=-created,limit=10");
		}
		// -----
		return $customerOrders;
	}

	/**
	 * Get Markup For Single Order Of Customer.
	 *
	 * @param mixed $customerOrder
	 * @return mixed
	 */
	private function getMarkupForSingleOrderOfCustomer($customerOrder) {
		$shopDateFormat = $this->pwcommerce->getShopDateFormat();

		$orderCreatedDate = $customerOrder->created;
		$order = $customerOrder->get(PwCommerce::ORDER_FIELD_NAME);
		$orderCreatedTimestamp = strtotime($orderCreatedDate);

		$orderCreatedDateFormatted = date($shopDateFormat, $orderCreatedTimestamp);

		$orderTotalPrice = $this->pwcommerce->getValueFormattedAsCurrencyForShop($order->totalPrice);

		$out = "<div class='mb-2'>" .
			// ORDER ID (inc link)
			"<span class='mr-1'><a href='{$this->adminURL}orders/view/?id={$customerOrder->id}'>{$customerOrder->id}</a></span>" .
			// DATE
			"<span>{$orderCreatedDateFormatted}</span> / " .
			// TOTAL PRICE
			"<span>{$orderTotalPrice}</span> / " .
			// PAYMENT
			"<span>{$order->paymentMethod}</span>" .
			"</div>";

		return $out;
	}

	// ~~~~~~~~~~~~~
	/**
	 * Builds a custom add new page/item for adding a new customer.
	 *
	 * @return mixed
	 */
	public function getCustomAddNewItemForm() {
		/** @var InputfieldForm $form */
		$form = $this->pwcommerce->getInputfieldForm();
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		$form->attr('x-data', 'ProcessPWCommerceData');

		// ++++++++++++++++
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		//------------------- new customer description (getInputfieldMarkup)
		$field = $this->getCustomAddNewItemFormDescription();
		$wrapper->add($field);

		//------------------- new customer first name (getInputfieldText)
		$field = $this->getCustomAddNewItemFormFirstName();
		$wrapper->add($field);

		//------------------- new customer middle name (getInputfieldText)
		$field = $this->getCustomAddNewItemFormMiddleName();
		$wrapper->add($field);

		//------------------- new customer last name (getInputfieldText)
		$field = $this->getCustomAddNewItemFormLastName();
		$wrapper->add($field);

		//------------------- new customer email (getInputfieldEmail)
		$field = $this->getCustomAddNewItemFormEmail();
		$wrapper->add($field);


		//------------------- create customer account for this new customer (getInputfieldCheckbox)
		$field = $this->getCustomAddNewItemFormCreateAccountCheckbox();
		// add checkbox
		$wrapper->add($field);

		//------------------- is_ready_to_save (getInputfieldHidden)
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPwCommerce::renderAddItem() know that we are ready to save
		$field = $this->getCustomAddNewItemFormRequiredHiddenInput();
		$wrapper->add($field);
		# -----------

		//------------------- save button (getInputfieldButton)
		$field = $this->getCustomAddNewItemFormSaveButton();
		// add submit button for add new customer add  SAVE process views
		$wrapper->add($field);

		//------------------- save + publish button (getInputfieldButton)
		$field = $this->getCustomAddNewItemFormSaveAndPublishButton();
		// add submit button for single item add  SAVE + PUBLISH process views
		$wrapper->add($field);

		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//----------
		return $form;
	}

	/**
	 * Get Custom Add New Item Form Description.
	 *
	 * @return mixed
	 */
	private function getCustomAddNewItemFormDescription() {
		$description = $this->_('Please specify some required details for the new customer. Once created, you will be able to add more details including shipping and billing addresses.');

		$options = [
			'description' => $description,
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			// note: blank
			'value' => '',
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// @note: add custom margin to the div.InputfieldContent.uk-form-controls
		$field->contentClass('mt-5');
		//----------
		return $field;
	}

	/**
	 * Get Custom Add New Item Form Email.
	 *
	 * @return mixed
	 */
	private function getCustomAddNewItemFormEmail() {

		$options = [
			'id' => "pwcommerce_add_new_item_email",
			'name' => "pwcommerce_add_new_item_email",
			'label' => $this->_('Customer Email'),
			'confirmLabel' => $this->_('Confirm Customer Email'),
			'placeholder' => $this->_('Enter Customer Email'),
			// will include a second input for confirmation
			// its id+name will be '_pwcommerce_add_new_item_email_confirm'
			'confirm' => 1,
			'required' => true,
			// TODO: needed?
			'collapsed' => Inputfield::collapsedNever,
			// 'wrapClass' => true,
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			//'classes' => 'pwcommerce_add_new_item',
			// 'columnWidth' => 100,
			'columnWidth' => 50,
			'size' => 50,
		];
		$field = $this->pwcommerce->getInputfieldEmail($options);
		$field->attr([
			// @note: wont' work since applied to both 'email' and 'confirm email' inputs!
			// 'x-model' => "{$this->xstore}.customer_email",
			// in case of error and reload, hence old values present
			'x-init' => "initOnLoadValues",
			// ==========
			'x-on:change.debounce' => 'handleCustomerEmailChange',
		]);

		// customer email
		$errorHandlingMarkup1 = "<small class='pwcommerce_error mb-1 block' x-show='!{$this->xstore}.is_valid_customer_email'>" . $this->_('Invalid email address') . "</small>";
		$errorHandlingMarkup1 .= "<small class='pwcommerce_error mb-1 block' x-show='!{$this->xstore}.is_matched_confirm_customer_email'>" . $this->_('Email addresses do not match') . "</small>";
		$field->prependMarkup($errorHandlingMarkup1);
		// customer confirm email
		$errorHandlingMarkup2 = "<small class='pwcommerce_error mt-1 block' x-show='!{$this->xstore}.is_valid_customer_confirm_email'>" . $this->_('Confirm email address is invalid') . "</small>";
		// $field->prependMarkup($errorHandlingMarkup2);
		$field->appendMarkup($errorHandlingMarkup2);

		//----------
		return $field;
	}

	/**
	 * Get Custom Add New Item Form First Name.
	 *
	 * @return mixed
	 */
	private function getCustomAddNewItemFormFirstName() {
		$options = [
			'id' => "pwcommerce_add_new_item_first_name",
			'name' => "pwcommerce_add_new_item_first_name",
			'label' => $this->_('First Name'),
			'placeholder' => $this->_('Customer First Name'),
			'required' => true,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			// 'wrapClass' => true,
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			//'classes' => 'pwcommerce_add_new_item',
		];
		$field = $this->pwcommerce->getInputfieldText($options);
		//----------
		return $field;
	}

	/**
	 * Get Custom Add New Item Form Middle Name.
	 *
	 * @return mixed
	 */
	private function getCustomAddNewItemFormMiddleName() {
		$options = [
			'id' => "pwcommerce_add_new_item_middle_name",
			'name' => "pwcommerce_add_new_item_middle_name",
			'label' => $this->_('Middle Name(s)'),
			'notes' => $this->_('Optional'),
			'placeholder' => $this->_('Customer Middle Name(s)'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			// 'wrapClass' => true,
			// 'wrapper_classes' => 'pwcommerce_no_outline',
		];
		$field = $this->pwcommerce->getInputfieldText($options);
		//----------
		return $field;
	}

	/**
	 * Get Custom Add New Item Form Last Name.
	 *
	 * @return mixed
	 */
	private function getCustomAddNewItemFormLastName() {
		$options = [
			'id' => "pwcommerce_add_new_item_last_name",
			'name' => "pwcommerce_add_new_item_last_name",
			'label' => $this->_('Last Name'),
			'placeholder' => $this->_('Customer Last Name'),
			'required' => true,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			// 'wrapClass' => true,
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			'classes' => 'pwcommerce_add_new_item',
		];
		$field = $this->pwcommerce->getInputfieldText($options);
		//----------
		return $field;
	}

	/**
	 * Get Custom Add New Item Form Create Account Checkbox.
	 *
	 * @return mixed
	 */
	private function getCustomAddNewItemFormCreateAccountCheckbox() {
		//------------------- create customer account for this new customer (getInputfieldCheckbox)
		$options = [
			'id' => "pwcommerce_add_new_item_customer_create_account",
			'name' => "pwcommerce_add_new_item_customer_create_account",
			// 'label' => $this->_('Allow duplicate title'),
			'label' => ' ', // @note: skipping label
			'label2' => $this->_('Create user account for customer'),
			'description' => $this->_("Tick to create a customer user account for this customer."),
			// TODO REPHRASE?
			'notes' => $this->_("If ticked, this will also create a user account for this new customer. An activation email will be sent to the customer requesting them to register an account with the shop. If unticked, you can later create an account for this customer when editing the customer's page."),
			'collapsed' => Inputfield::collapsedNever,
		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		return $field;
	}

	/**
	 * Get Custom Add New Item Form Required Hidden Input.
	 *
	 * @return mixed
	 */
	private function getCustomAddNewItemFormRequiredHiddenInput() {
		//------------------- is_ready_to_save (getInputfieldHidden)
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPwCommerce::renderAddItem() know that we are ready to save
		$field = $this->getHiddenMarkupForRequiredField();
		//----------
		return $field;
	}

	/**
	 * Get Custom Add New Item Form Save Button.
	 *
	 * @return mixed
	 */
	private function getCustomAddNewItemFormSaveButton() {
		$disabled = "!{$this->xstore}.is_matched_confirm_customer_email";
		$opacityClass = "!{$this->xstore}.is_matched_confirm_customer_email ? 'opacity-50' : ''";
		//------------------- save button (getInputfieldButton)
		$options = [
			'id' => "submit_save",
			'name' => "pwcommerce_save_new_button",
			'type' => 'submit',
			'label' => $this->_('Save'),
		];
		$field = $this->pwcommerce->getInputfieldButton($options);
		$field->showInHeader();
		$field->attr([
			'x-bind:disabled' => $disabled,
			'x-bind:class' => $opacityClass,
		]);
		return $field;
	}


	/**
	 * Get Custom Add New Item Form Save And Publish Button.
	 *
	 * @return mixed
	 */
	private function getCustomAddNewItemFormSaveAndPublishButton() {
		$disabled = "!{$this->xstore}.is_matched_confirm_customer_email";
		$opacityClass = "!{$this->xstore}.is_matched_confirm_customer_email ? 'opacity-50' : ''";
		//------------------- save + publish button (getInputfieldButton)
		$options = [
			'id' => "submit_save_and_publish",
			'name' => "pwcommerce_save_and_publish_new_button",
			'type' => 'submit',
			'label' => $this->_('Save + Publish'),
			'secondary' => true,
		];
		$field = $this->pwcommerce->getInputfieldButton($options);
		$field->attr([
			'x-bind:disabled' => $disabled,
			'x-bind:class' => $opacityClass,
		]);
		// add submit button for single item add  SAVE + PUBLISH process views
		return $field;
	}

	// ~~~~~~~~~~

	/**
	 * Get Single View Table Headers.
	 *
	 * @return mixed
	 */
	private function getSingleViewTableHeaders() {
		// TODO: DO WE USE TW CLASSES HERE?
		return [
			// ORDER NUMBER TODO: ok?
			[$this->_('Order ID'), 'pwcommerce_customer_orders_table_order'],
			// DATE
			[$this->_('Date'), 'pwcommerce_customer_orders_table_date'],
			// ORDER STATUS
			[$this->_('Order Status'), 'pwcommerce_customer_orders_table_order_status'],
			// PAYMENT & FULFILMENT STATUSES
			[$this->_('Payment/Fulfilment Status'), 'pwcommerce_customer_orders_table_payment_and_fulfilment_status'],
			// TOTAL
			[$this->_('Total'), 'pwcommerce_customer_orders_table_total'],
		];
	}

	// ~~~~~~~~~~

	/**
	 * Get Results Table Headers.
	 *
	 * @return mixed
	 */
	private function getResultsTableHeaders() {
		// TODO: DO WE USE TW CLASSES HERE?
		$selectAllCheckboxName = "pwcommerce_bulk_edit_selected_items_all";
		$xref = 'pwcommerce_bulk_edit_selected_items_all';
		$headers = [
			// SELECT ALL CHECKBOX
			$this->getBulkEditCheckbox('all', $selectAllCheckboxName, $xref),
			// CUSTOMER NAMES - concat + email below
			// TODO: make these classes generic? e.g. for th percent width?
			[$this->_('Customer'), 'pwcommerce_customers_table_names'],
			// [$this->_('Customer Name'), 'pwcommerce_customers_table_names'],
			// EMAIL
			// [$this->_('Email'), 'pwcommerce_customers_table_email'],
			// --------------
			// STATUS (guest, registered)
			[$this->_('Status'), 'pwcommerce_customers_table_status'],
			// --------------
			// PRIMARY/SHIPPING ADDRESS LINE ONE + TWO (if applicable) - concat
			// [$this->_('Address'), 'pwcommerce_customers_table_address'],
			// // POSTCODE
			// [$this->_('Postcode'), 'pwcommerce_customers_table_postcode'],
			// // COUNTRY
			// [$this->_('Country'), 'pwcommerce_customers_table_country'],
			// LOCATION (concat) - City, Country
			[$this->_('Location'), 'pwcommerce_customers_table_location'],
			// ORDERS (count)
			[$this->_('Orders'), 'pwcommerce_customers_table_orders'],
			// AMOUNT SPENT (currency)
			[$this->_('Amount Spent'), 'pwcommerce_customers_table_amount_spent'],

		];
		// CUSTOMER GROUPS
		if (!empty($this->isInstalledCustomerGroupsFeature)) {
			// add header (column) for customer groups
			$headers[] = [$this->_('Customer Groups'), 'pwcommerce_customers_table_customer_groups'];
		}

		// ----
		return $headers;
	}

	/**
	 * Get Results Table.
	 *
	 * @param mixed $pages
	 * @return mixed
	 */
	protected function getResultsTable($pages) {
		return $this->getTable($pages);
	}

	/**
	 * Render table with order line items for single view or several orders for bulk edit view
	 *
	 * @param mixed $pages
	 * @param string $usage
	 * @return mixed
	 */
	protected function ___getTable($pages, $usage = 'customers_bulk_edit_view') {
		$notFoundMessage = $this->_('No customers found.');
		if ($usage === 'customers_single_view') {
			$notFoundMessage = $this->_('Customer does not have any orders.');
		}

		$out = "";
		if (!$pages->count()) {
			$out = "<div  class='mt-5'><p>" . $notFoundMessage . "</p></div>";
		} else {
			$field = $this->modules->get('MarkupAdminDataTable');
			$field->setEncodeEntities(false);
			// set headers (th)
			$tableHeaders = $usage === 'customers_single_view' ? $this->getSingleViewTableHeaders() : $this->getResultsTableHeaders();
			//    $field->headerRow($this->getResultsTableHeaders());
			$field->headerRow($tableHeaders);
			// set each row
			// TODO: THIS IS NOT WORKING FOR ORDER LINE ITEMS! WE SEE ONLY 1 ROW AND THE REST ARE EMPTY!
			// @UPDATE: IT WORKS! IT'S JUST THAT WE HAVE NO SAVED VALUES IN $orderLineItem->pwcommerce_order_line_item FOR THEM!
			foreach ($pages as $page) {
				$row = $usage === 'customers_single_view' ? $this->getSingleViewTableRow($page) : $this->getResultsTableRow($page);
				$field->row($row);
			}

			// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
			$out = $field->render();
		}
		return $out;
	}

	// /**
  * Get Latest Orders Table Row.
  *
  * @param Page $page
  * @return mixed
  */
 private function getLatestOrdersTableRow(Page $page) {
	/**
	 *    get Single View Table Row.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	protected function ___getSingleViewTableRow(Page $page) {
		$order = $page->pwcommerce_order;
		$orderTotalPriceFormattedAsShopCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($order->totalPrice);
		$order = $page->get(PwCommerce::ORDER_FIELD_NAME);
		$statusesArray = $this->getOrderCombinedStatusesArray($order, $excludeStatuses = ['order']);

		//------------
		return [
			// ORDER NUMBER/TITLE
			$this->getEditItemURL($page),
			// DATE
			$this->getCreatedDate($page),
			// STATUS
			$this->pwcommerce->getOrderStatusName($order),
			// PAYMENT & FULFILMENT STATUSES
			$this->getOrderCombinedStatusesText($statusesArray),
			// TOTAL
			// $order->totalPrice,
			$orderTotalPriceFormattedAsShopCurrency,
		];
	}
	/**
	 * Get Order Combined Statuses Array.
	 *
	 * @param WireData $order
	 * @param array $excludeStatuses
	 * @return array
	 */
	private function getOrderCombinedStatusesArray(WireData $order, array $excludeStatuses = []): array {
		$statuses = $this->pwcommerce->getOrderCombinedStatuses($order);
		// --------
		// here we fetch order, payment and and fulfilment statuses
		// ----------
		if (!empty($excludeStatuses)) {
			foreach ($excludeStatuses as $excludeStatus) {
				if (!empty($statuses[$excludeStatus])) {
					unset($statuses[$excludeStatus]);
				}
			}
		}
		// ----------
		return $statuses;
	}

	/**
	 * Get Results Table Row.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getResultsTableRow($page) {

		$checkBoxesName = "pwcommerce_bulk_edit_selected_items[]";

		$customerAddresses = $page->get(PwCommerce::CUSTOMER_ADDRESSES_FIELD_NAME);
		$customerPrimaryAddress = $customerAddresses->get('addressType=shipping_primary');

		// for customer address concact
		$customerPrimaryAddressText = $this->getCustomerPrimaryShippingAddress($customerPrimaryAddress);

		// for customer status
		$customerTypeText = $this->getCustomerAccountStatusText($page);

		// for customer order quantity + customer orders totals
		/** @var array $customerOrdersTotals */
		$customerOrdersTotals = $this->getCustomerOrdersTotals($page);
		$customerOrdersQuantity = $customerOrdersTotals['orders_quantity'];
		$customerOrdersQuantity = "<small>{$customerOrdersQuantity}</small>";
		$customerOrdersTotal = $customerOrdersTotals['orders_total_spend'];

		$row = [
			// CHECKBOX
			$this->getBulkEditCheckbox($page->id, $checkBoxesName),
			// CUSTOMER NAMES + EMAIL - concat
			$this->getViewCustomer($page),
			// ACCOUNT REGISTRATION STATUS
			$customerTypeText,
			// FULL PRIMARY/SHIPPING ADDRESS  - concat
			$customerPrimaryAddressText,
			// ORDERS TOTAL QUANTITY
			$customerOrdersQuantity,
			// ORDERS TOTAL AMOUNT SPENT
			$customerOrdersTotal,

		];
		// -------
		// CUSTOMER GROUPS
		if (!empty($this->isInstalledCustomerGroupsFeature)) {
			// add row for customer groups
			$row[] = $this->getCustomerCustomerGroups($page);
		}

		// -------
		return $row;
	}

	/**
	 * Get View Customer.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getViewCustomer($page) {

		// ++++++
		// TODO EXPERIMENT WITH CUSTOM BACKEND PARTIAL TEMPLATE SAVED AT /site/templates/pwcommerce/backend/my_process_order.php <- would also enable dynamic custom views; e.g. if this perm or that filter, use this partial, else that one. they would use the logic in their partials. partials only need the order Page! TODO - add breaking change note: /site/templates/pwcommerce/frontend/order-complete-php! Could also test with home dash!

		// +++++++++
		// get the view URL if item is unlocked
		$out = $this->getViewItemURL($page);
		// add published and locked status if applicable
		$status = [];
		if ($page->isLocked()) {
			$status[] = $this->_('locked');
		}

		// TODO: DO WE REALLY NEED THIS STATUS FOR ORDERS???
		if ($page->isUnpublished()) {
			$status[] = $this->_('unpublished');
		}
		$statusString = implode(', ', $status);
		if ($statusString) {
			$out .= "<small class='block italic mt-1'>{$statusString}</small>";
		}
		// $out = "<a href='{$adminURL}orders/edit/?id={$page->id}'>{$page->title}</a>";
		return $out;
	}

	/**
	 *    get View Item U R L.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	public function ___getViewItemURL($page) {

		$customer = $page->get(PwCommerce::CUSTOMER_FIELD_NAME);
		$customerNamesString = $this->getConcatCustomerNames($customer);
		if (empty(trim($customerNamesString))) {
			$customerNamesString = $this->_('Missing Names');
		}
		// --------
		// append customer email
		$customerEmailString = "<small class='block mb-2'>{$customer->email}</small>";

		// -------
		$out = "<a href='{$this->adminURL}customers/view/?id={$page->id}'>{$customerNamesString}</a>{$customerEmailString}";
		// -----
		return $out;

	}

	/**
	 * Get Edit Item Title.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getEditItemTitle($page) {
		// get the edit URL if item is unlocked
		$out = $this->getEditItemURL($page);
		// add published and locked status if applicable
		$status = [];
		if ($page->isLocked()) {
			$status[] = $this->_('locked');
		}

		if ($page->isUnpublished()) {
			$status[] = $this->_('unpublished');
		}

		$statusString = implode(', ', $status);
		if ($statusString) {
			$out .= "<small class='block italic mt-1'>{$statusString}</small>";
		}
		// $out = "<a href='{$adminURL}customers/edit/?id={$page->id}'>{$page->title}</a>";
		return $out;
	}

	/**
	 * Get Edit Item U R L.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	protected function getEditItemURL($page) {
		// if page is locked, don't show edit URL
		if ($page->isLocked()) {
			$out = "<span>{$page->title}</span>";
		} else {
			$orderTitle = $this->pwcommerce->getOrderNumberWithPrefixAndSuffix($page);
			$out = "<a href='{$this->adminURL}orders/view/?id={$page->id}'>{$orderTitle}</a>";
		}
		return $out;
	}

	/**
	 * Build the string for the last created date of this order page.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getCreatedDate($page) {
		return $this->pwcommerce->getCreatedDate($page);
	}

	/**
	 * Get Order Combined Statuses Text.
	 *
	 * @param array $statusesArray
	 * @return string
	 */
	private function getOrderCombinedStatusesText(array $statusesArray): string {
		// --------
		// here we combine order payment status and fulfilment status
		// e.g. paid / awaiting fulfilment, etc
		// ----------
		// prepare text for statuses
		// $statusesText = "<small>" . implode("<br>", $statuses) . "</small>";
		$statusesText = "<small class='block'>" . implode("/", $statusesArray) . "</small>";
		return $statusesText;
	}

	/**
	 * Get Concat Customer Names.
	 *
	 * @param mixed $customer
	 * @return mixed
	 */
	private function getConcatCustomerNames($customer) {
		$customerNamesString = '';
		if (!empty($customer)) {
			$customerNames = [
				'first_name' => $customer->firstName,
				'middle_name' => $customer->middleName,
				'last_name' => $customer->lastName,
			];
			$customerNamesString = ucfirst(implode(' ', $customerNames));
		}
		return $customerNamesString;
	}

	/**
	 * Get Customer Account Status Text.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	protected function getCustomerAccountStatusText(Page $page) {
		$registeredCustomer = $this->_('Registered account');
		$guestCustomer = $this->_('No account');
		$customerType = empty($this->isRegisteredCustomer($page)) ? $guestCustomer : $registeredCustomer;
		// ----
		$out = "<small>{$customerType}</small>";
		// -----
		return $out;
	}

	/**
	 * Get Customer Orders Totals.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	protected function getCustomerOrdersTotals($page) {
		/** @var array $customerOrders */
		$customerOrders = $this->getCustomerOrders($page, $isRaw = true);

		// orders quantity
		$customerOrdersQuantity = count($customerOrders);
		$totalCustomerOrdersStr = sprintf(_n("%d order", "%d orders", $customerOrdersQuantity), $customerOrdersQuantity);

		// orders total price/spend
		$orders = array_column($customerOrders, 'pwcommerce_order');
		$ordersTotalPriceValues = array_column($orders, 'order_total_price');

		$ordersTotalPrice = array_sum($ordersTotalPriceValues);
		$totalCustomerOrdersSpend = $this->pwcommerce->getValueFormattedAsCurrencyForShop($ordersTotalPrice);

		// -----
		$customerOrdersTotals = [
			'orders_quantity' => $totalCustomerOrdersStr,
			'orders_total_spend' => $totalCustomerOrdersSpend,
		];

		// -----
		return $customerOrdersTotals;
	}

	/**
	 * Get Customer Primary Shipping Address.
	 *
	 * @return mixed
	 */
	private function getCustomerPrimaryShippingAddress(WireData|null $customerAddress) {

		if (empty($customerAddress)) {
			$customerPrimaryAddressText = $this->_('Missing primary shipping address');
			$out = "<small class='block'>{$customerPrimaryAddressText}</small>";
		} else {
			$out = "<span>";
			// -----
			$addressProperties = $this->getCustomerPrimaryShippingAddressProperties();
			foreach ($addressProperties as $property) {
				$addressValue = $customerAddress->get($property);
				if (!empty(trim($addressValue))) {
					$out .= "<small class='block'>{$addressValue}</small>";
				}
			}
			$out .= "</span>";

		}
		return $out;

	}

	/**
	 * Get Customer Primary Shipping Address Properties.
	 *
	 * @return mixed
	 */
	private function getCustomerPrimaryShippingAddressProperties() {
		return [
			'addressLineOne',
			'addressLineTwo',
			'city',
			'region',
			'postalCode',
			'country',
		];
	}

	/**
	 * Get Customer Customer Groups.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getCustomerCustomerGroups(Page $page) {
		$customerGroups = $page->get(PwCommerce::CUSTOMER_GROUPS_FIELD_NAME);

		if ($customerGroups && !empty($customerGroups->count())) {
			// TODO: MAYBE MAKE THIS LINKS?
			$customerGroupsStatusString = $customerGroups->implode(', ', 'title');
			$out = "<small class='italic mt-1'>{$customerGroupsStatusString}</small>";
		} else {
			$customerGroupsStatusString = $this->_('Customer not in any customer group');
			$out = "<small class='mt-1'>{$customerGroupsStatusString}</small>";
		}

		return $out;
	}

	/**
	 * Get Bulk Edit Actions Panel.
	 *
	 * @param mixed $adminURL
	 * @return mixed
	 */
	protected function getBulkEditActionsPanel($adminURL) {
		$actions = [
			'publish' => $this->_('Publish'),
			'unpublish' => $this->_('Unpublish'),
			'lock' => $this->_('Lock'),
			'unlock' => $this->_('Unlock'),
			'trash' => $this->_('Trash'),
			'delete' => $this->_('Delete'),
		];
		$options = [
			// add new link
			'add_new_item_label' => $this->_('Add new customer'),
			// add new url
			'add_new_item_url' => "{$adminURL}customers/add/",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	/**
	 * Get Bulk Edit Checkbox.
	 *
	 * @param int $id
	 * @param mixed $name
	 * @return mixed
	 */
	private function getBulkEditCheckbox($id, $name) {
		$options = [
			'id' => "pwcommerce_bulk_edit_checkbox{$id}",
			'name' => $name,
			'label' => ' ',
			// @note: skipping label
			// 'label2' => $this->_('Use custom handling fee'),
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_bulk_edit_selected_items',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $id,

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		// TODO: ADD THIS ATTR AND MAYBE EVEN A x-ref? so we can selectall using alpinejs
		$field->attr([
			'x-on:change' => 'handleBulkEditItemCheckboxChange',
		]);

		return $field->render();
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ EMAIL CUSTOMER  ~~~~~~~~~~~~~~~~~~

	/**
	 * Modal for email customer
	 *
	 * @return mixed
	 */
	private function getModalMarkupForEmailCustomer() {

		$xstore = $this->xstore;

		$header = $this->_("Email Customer");
		$emailCustomerModalProperty = "is_open_email_customer_modal";

		$body =
			"<div>" .
			"<div id='pwcommerce_email_customer'>" .
			$this->getEmailCustomerMarkup() .
			"</div>" .

			// ++++++++
			// spinner
			"<div id='pwcommerce_email_customer_spinner_indicator' class='htmx-indicator'>" .
			"<i class='fa fa-fw fa-spin fa-spinner'></i>" .
			$this->_("Please wait") .
			"&#8230;" .
			"</div>" .
			// ++++++++
			"</div>"; // end div with x-init
		// ==================================
		// apply button
		$applyButton = $this->renderModalMarkupForEmailCustomerSendButton();
		// cancel button
		$cancelButton = $this->renderModalMarkupForEmailCustomerCancelButton();
		$footer = "<div class='ui-dialog-buttonset'>{$applyButton}{$cancelButton}</div>";
		$xproperty = $emailCustomerModalProperty;
		$size = '5x-large';
		// $size = '4x-large';

		// wrap content in modal for activating/deactivating
		// modal options
		$options = [
			// $header The modal title pane markup.
			'header' => $header,
			// $body The main content markup.
			'body' => $body,
			// $footer The footer markup.
			'footer' => $footer,
			// $xstore The alpinejs store with the property that will be modelled to show/hide the modal.
			'xstore' => $this->xstoreProcessPWCommerce,
			// $xproperty The alpinejs property that will be modelled to show/hide the modal.
			'xproperty' => $xproperty,
			// $size The size of the modal requested.
			'size' => $size,
		];
		$out = $this->pwcommerce->getModalMarkup($options);

		return $out;
	}

	/**
	 * Get Email Customer Markup.
	 *
	 * @return mixed
	 */
	private function getEmailCustomerMarkup() {

		$emailCustomerMarkup = $this->getMarkupForEmailCustomerParts();
		$out =
			"<div id='pwcommerce_send_customer_email_wrapper'>" .
			"<div>" .
			########
			// FORM INPUTS
			$emailCustomerMarkup .
			// --------
			"</div>" .
			"</div>";

		// -----------

		return $out;
	}

	/**
	 * Get Markup For Email Customer Parts.
	 *
	 * @return mixed
	 */
	private function getMarkupForEmailCustomerParts() {

		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		// ==================

		//------------------- email customer info (getInputfieldText)
		$field = $this->getMarkupEmailCustomerToAndFromEmailsMarkup();
		$wrapper->add($field);

		//------------------- email customer subject (getInputfieldText)
		$field = $this->getMarkupForEmailSubjectTextField();
		$wrapper->add($field);

		//------------------- email customer body (getInputfieldRichText)
		$field = $this->getMarkupForEmailBodyRTEField();
		$wrapper->add($field);

		//------------------- email customer customer id (getInputfieldHidden)
		$field = $this->getHiddenMarkupForCustomerID();
		$wrapper->add($field);

		//------------------- email customer email type (getInputfieldHidden)
		$field = $this->getHiddenMarkupForEmailCustomerType();
		$wrapper->add($field);

		//------------------- email customer processing modal
		$out = $wrapper->render();

		// ------
		return $out;

	}


	########################

	/**
	 * Get Markup Email Customer To And From Emails Markup.
	 *
	 * @return mixed
	 */
	private function getMarkupEmailCustomerToAndFromEmailsMarkup() {
		$shopEmail = $this->pwcommerce->getShopFromEmail();
		if (empty($shopEmail)) {
			$shopEmail = $this->pwcommerce->getShopEmail();
		}
		$customer = $this->customerPage->get(PwCommerce::CUSTOMER_FIELD_NAME);
		$customerEmail = $customer->email;
		$description = sprintf(__('Sending email from %1$s to %2$s.'), $shopEmail, $customerEmail);
		$out = "<span class='description'>" . $description . "</span>";
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			// 'description' => $description,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			// 'classes' => 'pwcommerce_order_view',
			'wrapper_classes' => 'pwcommerce_no_outline',
			// 'value' => '', if using $description
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// -----
		return $field;

	}

	/**
	 * Get Markup For Email Subject Text Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForEmailSubjectTextField() {

		$options = [
			'id' => "pwcommerce_email_customer_email_subject",
			'name' => "pwcommerce_email_customer_email_subject",
			'type' => 'text',
			'label' => $this->_('Subject'),
			'value' => '',
			'collapsed' => Inputfield::collapsedNever,
			'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
		];

		$field = $this->pwcommerce->getInputfieldText($options);

		return $field;
	}

	/**
	 * Get Markup For Email Body R T E Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForEmailBodyRTEField() {


		$value = "";

		$options = [
			'id' => "pwcommerce_email_customer_email_body",
			// TODO: not really needed!
			'name' => "pwcommerce_email_customer_email_body",
			'type' => 'email',
			'label' => $this->_('Content'),
			'notes' => $this->_('Email contents'),
			// 'description' => $this->_('Email contents'),
			// 'placeholder' => $this->_('Email contents'),
			'value' => $value,
			// 'description' => $description . $extraDescriptionMarkup,
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 50,
			'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
		];

		$field = $this->pwcommerce->getInputfieldCKEditor($options);



		# ------------------
		// TODO MOVE TO INPUTFIELD HELPERS!


		// -----
		return $field;
	}

	/**
	 * Get hidden markup to set customer id of the customer to send an email to.
	 *
	 * @return mixed
	 */
	private function getHiddenMarkupForCustomerID() {
		//------------------- email_customer_customer_order_id (getInputfieldHidden)
		$options = [
			'id' => "pwcommerce_email_customer_customer_id",
			'name' => 'pwcommerce_email_customer_customer_id',
			'value' => $this->customerPage->id
		];
		$field = $this->pwcommerce->getInputfieldHidden($options);
		// return $field;
		return $field;
	}

	/**
	 * Get hidden markup to track type of email to send.
	 *
	 * @param string $emailActionType
	 * @return mixed
	 */
	private function getHiddenMarkupForEmailCustomerType($emailActionType = 'send_customer_email') {
		//------------------- email_customer_customer_order_id (getInputfieldHidden)
		$options = [
			'id' => "pwcommerce_email_customer_email_type",
			'name' => 'pwcommerce_email_customer_email_type',
			'value' => $emailActionType
		];
		$field = $this->pwcommerce->getInputfieldHidden($options);
		return $field;
	}

	/**
	 * Get hidden markup to track type of email to send.
	 *
	 * @return mixed
	 */
	private function getHiddenMarkupForRequiredField() {
		//------------------- pwcommerce_is_ready_to_save (getInputfieldHidden)
		// lets ProcessPwCommerce::renderViewItem know that we are ready to save
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];
		$field = $this->pwcommerce->getInputfieldHidden($options);
		return $field;
	}


	/**
	 * Render Modal Markup For Email Customer Send Button.
	 *
	 * @return string|mixed
	 */
	private function renderModalMarkupForEmailCustomerSendButton() {
		$emailCustomerURL = "{$this->adminURL}customers/email-message/";
		$applyButtonOptions = [
			'type' => 'submit',
			'href' => $emailCustomerURL,
		];
		// -----------
		$applyButton = $this->pwcommerce->getModalActionButton($applyButtonOptions, 'send');
		// ===========
		return $applyButton;
	}
	/**
	 * Get rendered button for the modal for actioning a selected order status.
	 *
	 * @return string
	 */
	private function renderModalMarkupForEmailCustomerCancelButton(): string {
		$cancelButton = $this->pwcommerce->getModalActionButton(['x-on:click' => 'resetEmailAndCloseModal'], 'cancel');
		return $cancelButton;
	}


	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ QUICK FILTERS  ~~~~~~~~~~~~~~~~~~

	/**
	 *    get Quick Filters Values.
	 *
	 * @return mixed
	 */
	protected function ___getQuickFiltersValues() {
		$filters = [
			// reset/all
			'reset' => $this->_('All'),
			// active
			'active' => $this->_('Active'),// published
			'draft' => $this->_('Draft'),// unpublished
			// registered
			'registered' => $this->_('Registered'),
			'guest' => $this->_('Guest'),
			// not in customer group
			'not_in_customer_group' => $this->_('Not in Customer Group'),
			// no orders
			'no_orders' => $this->_('No Orders'),
		];

		// ------
		return $filters;
	}

	/**
	 * Get Allowed Quick Filter Values.
	 *
	 * @return mixed
	 */
	private function getAllowedQuickFilterValues() {
		// filters array
		/** @var array $filters */
		$filters = $this->getQuickFiltersValues();
		$allowedQuickFilterValues = array_keys($filters);
		return $allowedQuickFilterValues;
	}

	/**
	 * Get Selector For Quick Filter.
	 *
	 * @return mixed
	 */
	protected function getSelectorForQuickFilter() {
		$input = $this->wire('input');
		$selector = '';
		// NOTE: KEYS -> filter values; VALUEs -> STATUS CONSTANTS
		$allowedQuickFilterValues = $this->getAllowedQuickFilterValues();
		$quickFilterValue = $this->wire('sanitizer')->option($input->pwcommerce_quick_filter_value, $allowedQuickFilterValues);
		if (!empty($quickFilterValue)) {
			// quick filter checks
			// ++++++++++
			if (in_array($quickFilterValue, ['active', 'draft'])) {
				// ACTIVE (PUBLISHED) OR DRAFT (UNPUBLISHED)
				$selector = $this->getSelectorForQuickFilterActive($quickFilterValue);
			} elseif (in_array($quickFilterValue, ['registered', 'guest'])) {
				// REGISTERED (PW USER [ID]) OR GUEST (user_id=0)
				$selector = $this->getSelectorForQuickFilterAccount($quickFilterValue);
			} else if ($quickFilterValue === 'not_in_customer_group') {
				// NOT IN CUSTOMER GROUP
				$selector = $this->getSelectorForQuickFilterNotInCustomerGroup();
			} else if ($quickFilterValue === 'no_orders') {
				// NO ORDERS
				$selector = $this->getSelectorForQuickFilterNoOrder();
			}
		}
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter Active.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterActive($quickFilterValue) {
		$selector = '';
		if ($quickFilterValue === 'active') {
			// PUBLISHED
			$selector = ",status<" . Page::statusUnpublished;
		} else if ($quickFilterValue === 'draft') {
			// UNPUBLISHED
			$selector = ",status>=" . Page::statusUnpublished;
		}
		// ----
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter Account.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterAccount($quickFilterValue) {
		$selector = '';
		if ($quickFilterValue === 'registered') {
			// REGISTERED CUSTOMER/CUSTOMER WITH ACCOUNT (ProcessWire userID)
			$selector = "," . PwCommerce::CUSTOMER_FIELD_NAME . ".user_id>0";
		} else if ($quickFilterValue === 'guest') {
			// GUEST
			$selector = "," . PwCommerce::CUSTOMER_FIELD_NAME . ".user_id<1";
		}
		// ----
		return $selector;

	}

	/**
	 * Get Selector For Quick Filter Not In Customer Group.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterNotInCustomerGroup() {
		$selector = "," . PwCommerce::CUSTOMER_GROUPS_FIELD_NAME . "=''";
		// ----
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter No Order.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterNoOrder() {
		// e.g.
		// SELECT email
		// FROM field_pwcommerce_order_customer
		// GROUP BY email

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::ORDER_CUSTOMER_FIELD_NAME,
			'select_columns' => ['email'],
			'group_by_columns' => ['email']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);


		if (!empty($results)) {
			// get emails BUT REMOVE EMPTIES (just in case)
			$emails = array_filter(array_column($results, 'email'));
			$emailsSelector = implode("|", $emails);
			// NOTE: we want emails of customers without orders!
			$selector = "," . PwCommerce::CUSTOMER_FIELD_NAME . ".email!={$emailsSelector}";
		}


		// ----
		return $selector;

	}

}