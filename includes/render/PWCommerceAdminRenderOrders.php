<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Orders
 *
 * Class to render content for PWCommerce Admin Module executeOrders().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderOrders for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceAdminRenderOrders extends WireData {

	private $adminURL;
	private $ajaxPostURL;
	private $selectorStart;

	// @note: these are not translated; they are from the database
	private $allOrderStatusesDefinitions;
	// @note: these are translated strings
	private $allOrderStatuses;
	// current order page
	public $orderPage;

	private $shopCurrencySymbolString;
	private $applyStatusCode;
	private $applyStatusCodeOrderID;
	private $applyStatusCodeOrderTotalPrice;
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
			$this->ajaxPostURL = $options['ajax_post_url'];
			$this->xstoreProcessPWCommerce = $options['xstoreProcessPWCommerce'];
			// i.e., '$store.ProcessPWCommerceStore'
			$this->xstore = $options['xstore'];
			if (!empty($options['selector_start'])) {
				$this->selectorStart = $options['selector_start'];
			}
		}

		// ORDER STATUSES
		$this->allOrderStatusesDefinitions = $this->pwcommerce->getAllOrderStatusDefinitionsFromDatabase();
	}

	/**
	 * Render Results.
	 *
	 * @param mixed $selector
	 * @return string|mixed
	 */
	protected function renderResults($selector = null) {

		// enforce to string for strpos for PHP 8+
		$selector = strval($selector);

		// DETERMINE HOW TO RENDER ALLS ORDERS VIEW/DASH
		// +++++
		$customPartialTemplate = $this->pwcommerce->getBackendPartialTemplate(PwCommerce::PROCESS_RENDER_ORDERS_PARTIAL_TEMPLATE_NAME);
		if (!empty($customPartialTemplate)) {
			// CUSTOM PWCOMMERCE PROCESS RENDER ORDERS BACKEND MARKUP
			// set selector
			$customPartialTemplate->set('selector', $selector);
			# +++++++++++
			// GET MARKUP
			$out = $customPartialTemplate->render();
			return $out;
		}


		$input = $this->wire('input');
		$isQuickFilter = false;

		// pwcommerce_quick_filter_value
		if ($this->wire('config')->ajax) {
			if ($input->pwcommerce_quick_filter_value) {
				// BULK VIEW QUICK FILTER
				$isQuickFilter = true;
			} else if ((int) $input->pwcommerce_order_status_selected_action_fetch_markup) {
				// SINGLE VIEW GET REQUEST FOR MARKUP FOR APPLY ORDER STATUS ACTION
				$out = $this->handleAjaxOrderStatusAction();
				return $out;
			}
		}


		//-----------------
		// FORCE DEFAULT LIMIT IF NO USER LIMIT SET
		if (strpos($selector, 'limit=') === false) {
			$limit = 10;
			$selector = rtrim("limit={$limit}," . $selector, ",");
		}
		//------------
		// FORCE TEMPLATE TO MATCH PWCOMMERCE ORDERS ONLY + INCLUDE ALL + EXLUDE TRASH
		// TODO: for orders, for now, we also sort -created - users can always override it
		$selector .= ",template=" . PwCommerce::ORDER_TEMPLATE_NAME . ",include=all,sort=-created,status<" . Page::statusTrash;

		// ----------
		// ADD SELECTOR FOR QUICK FILTER
		if (!empty($isQuickFilter)) {
			$selector .= $this->getSelectorForQuickFilter();
		}

		//------------
		// ADD START IF APPLICABLE (ajax pagination)
		if (!empty($this->selectorStart)) {
			$start = (int) $this->selectorStart;
			$selector .= ",start={$start}";
		}

		//-----------------------

		// TODO: for future: need to add variants! i.e. their child pages, if applicable - same for orders - need order items!

		$pages = $this->wire('pages')->find($selector);

		//-----------------

		// BUILD FINAL MARKUP TO RETURN TO ProcessPwCommerce::pagesHandler()
		// @note: important: don't remove the class 'pwcommerce_inputfield_selector'! we need it for htmx (hx-include)
		$out =
			"<div id='pwcommerce_bulk_edit_custom_lister' class='pwcommerce_inputfield_selector pwcommerce_show_highlight mt-5'>" .
			// BULK EDIT ACTIONS
			$this->getBulkEditActionsPanel() .
			// PAGINATION STRING (e.g. 1 of 25)
			"<h3 id='pwcommerce_bulk_edit_custom_lister_pagination_string'>" . $pages->getPaginationString('') . "</h3>" .
			// TABULATED RESULTS (if pages found, else 'none found' message is rendered)
			$this->getTable($pages) .
			// HIDDEN INPUT FOR HTMX
			// set the context for differentiation when in ajax page
			"<input type='hidden' value='orders' name='pwcommerce_inputfield_selector_context'>" .
			// PAGINATION (render the pagination navigation)
			$this->pwcommerce->getPagination($pages, $this->paginationOptions()) .
			//---------------
			"</div>";

		return $out;
	}


	/**
	 * Get the options for building the form to add a new Order for use in ProcessPWCommerce.
	 *
	 * @return array
	 */
	protected function getAddNewItemOptions(): array {
		$exampleOrderTitle = $this->_("Special Children's Books Order");
		$description = sprintf(__("Optionally enter a descriptive title for this manual order for easier identification. For instance, '%s'. If left blank, an order title will be generated automatically."), $exampleOrderTitle);

		return [
			'label' => $this->_('Order Title'),
			// TODO USE NAME INSTEAD?
			'headline' => $this->_('Create Manual Order'),
			'description' => $description,
			// @note: NOT required as will generate automatically if empty
			'required' => false,
		];
	}

	/**
	 * Pagination Options.
	 *
	 * @return mixed
	 */
	private function paginationOptions() {
		//------------
		$paginationOptions = ['base_url' => $this->adminURL . 'orders/', 'ajax_post_url' => $this->adminURL . 'ajax/'];
		return $paginationOptions;
	}

	/**
	 * Get Results Table Headers.
	 *
	 * @return mixed
	 */
	private function getResultsTableHeaders() {
		// TODO: DO WE USE TW CLASSES HERE?
		$selectAllCheckboxName = "pwcommerce_bulk_edit_selected_items_all";
		$xref = 'pwcommerce_bulk_edit_selected_items_all';
		return [
			// SELECT ALL CHECKBOX
			$this->getBulkEditCheckbox('all', $selectAllCheckboxName, $xref),
			// ORDER NUMBER TODO: ok?
			[$this->_('Order ID'), 'pwcommerce_orders_table_order'],
			// ITEMS (ORDER LINE ITEMS)
			[$this->_('Items'), 'pwcommerce_orders_table_items'],
			// DATE
			[$this->_('Date'), 'pwcommerce_orders_table_date'],
			// EMAIL TODO?
			[$this->_('Email'), 'pwcommerce_orders_table_email'],
			// COUNTRY
			[$this->_('Country'), 'pwcommerce_orders_table_country'],
			// PAYMENT
			[$this->_('Payment'), 'pwcommerce_orders_table_payment'],
			// PAYMENT & FULFILMENT STATUSES
			[$this->_('Payment/Fulfilment Status'), 'pwcommerce_orders_table_payment_and_fulfilment_status'],
			// TOTAL
			[$this->_('Total'), 'pwcommerce_orders_table_total'],
		];
	}

	/**
	 * Get Single View Table Headers.
	 *
	 * @return mixed
	 */
	private function getSingleViewTableHeaders() {
		// TODO: DO WE USE TW CLASSES HERE?
		return [
			// PRODUCT
			[$this->_('Product'), 'pwcommerce_orders_table_order_line_item_unit_product'],
			// QUANTITY
			[$this->_('Quantity'), 'pwcommerce_orders_table_order_line_item_quantity'],
			// UNIT PRICE
			[$this->_('Price'), 'pwcommerce_orders_table_order_line_item_unit_price'],
			// TOTAL PRICE
			[$this->_('Total'), 'pwcommerce_orders_table_order_line_item_total_price'],
		];
	}

	/**
	 * Get Results Table Row.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getResultsTableRow(Page $page) {
		$checkBoxesName = "pwcommerce_bulk_edit_selected_items[]";
		$order = $page->get(PwCommerce::ORDER_FIELD_NAME);
		$statusesArray = $this->getOrderCombinedStatusesArray($order, $excludeStatuses = ['order']);
		return [
			// CHECKBOX
			$this->getBulkEditCheckbox($page->id, $checkBoxesName),
			// ORDER NUMBER TODO: ok?
			// TODO; LINK TO VIEW: $this->getViewOrder($page),
			// $page->id, // TODO: GET WITH PREFIX/SUFFIX AS APPLICABLE
			$this->getViewOrder($page),
			// ITEMS (ORDER LINE ITEMS)
			// @note:these are  just children of the order page
			// @note: we exclude line items that are in 'abandoned state' @see the notes in buildViewOrder() for more details
			$page->numChildren("status!=hidden"),
			// DATE
			$this->getCreatedDate($page),
			// TODO: OK WITH CREATED? show also modified?
			// EMAIL TODO?
			$order->email,
			// COUNTRY
			$order->shippingAddressCountry,
			// PAYMENT
			// 'Order Payment Method Here, e.g. PayPal - TODO later: invoice?',
			// TODO: for draft orders, need to show this as draft as well similar to below!
			// TODO IS THIS PAYMENT METHOD OR STATUS???? MAYBE BOTH? WITH SMALL FOR METHOD??
			$order->paymentMethod,
			// ORDER, PAYMENT & FULFILLMENT STATUSES
			// TODO: @NOTE: deduced from line items IN THE API! e.g. if all complete, then order complete!
			// @note: if draft order, we also show this as draft
			// TODO: SHOW DRAFT ORDER TEXT IN DIFFERENT COLOUR (?) -> FUTURE!
			$this->getOrderCombinedStatusesText($statusesArray),
			// TOTAL
			$this->pwcommerce->getValueFormattedAsCurrencyForShop($order->totalPrice),

		];
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
	 * Get View Order.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getViewOrder($page) {

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
		$out = "<a href='{$this->adminURL}orders/view/?id={$page->id}'>{$page->id}</a>";
		return $out;
	}

	/**
	 * Get Markup For Order Status Action Types.
	 *
	 * @return mixed
	 */
	private function getMarkupForOrderStatusActionTypes() {
		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		// TODO 3 FIELDS? ONE EACH FOR ORDER, PAYMENT AND FULFILLMENT STATUSES?
		$selectOptions = [
			'order_statuses' => $this->_('Order'),
			'payment_statuses' => $this->_('Payment'),
			'shipping_statuses' => $this->_('Shipping'),
			// TODO FOR INVOICES, MAYBE NOW DO SEPARATELY IN OWN SELECT NOT SELECTIZE AND NOT HERE!
			// 'invoices' => $this->_('Invoices'),
		];

		// TODO: add dynamic notes with respect to above
		$options = [
			// 'id' => "pwcommerce_order_line_item_discount_type",
			// TODO: not really needed! @UPDATE: NOW NEEDED! WE NEED IT FOR THIS LINE ITEM!
			// 'name' => 'pwcommerce_order_line_item_discount_type',
			// TODO: SKIP LABEL?
			'label' => $this->_('Action Type'),
			//  'skipLabel' => Inputfield::skipLabelHeader,
			'description' => $this->_('Select an action type and an action to apply.'),
			'collapsed' => Inputfield::collapsedNever,
			// 'classes' => 'pwcommerce_product_generate_variants_attribute_options',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_header_padding_left pwcommerce_override_processwire_inputfield_content_padding_left',
			'classes' => 'pwcommerce_order_line_item_discount_type',
			'columnWidth' => 50,
			'select_options' => $selectOptions,
			'notes' => $this->_("Once you have selected an action type, its specific actions will be displayed. After you pick a specific action, click on the action button. This will open a modal to edit and confirm the action before it gets applied to the order."),
		];

		$field = $this->pwcommerce->getInputfieldSelect($options);
		$xstore = $this->xstore;
		$field->attr([
			// 'x-model' => "{$xstore}.discountType",
			'x-model' => "{$xstore}.selected_order_status_action_type",
			'x-on:change' => "handleOrderStatusActionTypeChange",
			// 'x-bind:id' => '`pwcommerce_order_line_item_discount_type${product.id}`',
			// 'x-bind:name' => '`pwcommerce_order_line_item_discount_type${product.id}`',

		]);
		$wrapper->add($field);
		$out = $wrapper->render();
		// -------
		return $out;
	}

	/**
	 * Get Markup For Order Status Action Select.
	 *
	 * @param string $actionType
	 * @return mixed
	 */
	private function getMarkupForOrderStatusActionSelect(string $actionType) {
		// TODO THIS SHOULD FILTER TO ONLY GRAB THE RELEVANT ACTIONS FOR THE TYPE
		$actionTypesOptions = [
			// order status
			'order_statuses' => [
				'label' => $this->_('Order Status'),
				'description' => $this->_('Set an order status for this order.'),
				'notes' => $this->_('E.g., draft, pending, declined, cancelled, etc.'),
				// 'x_model' => 'selected_order_order_status_action'
			],
			// payment status
			'payment_statuses' => [
				'label' => $this->_('Payment Status'),
				'description' => $this->_('Set a payment status for this order.'),
				'notes' => $this->_('E.g., failed, overdue, unpaid, refunded, etc.'),
				// 'x_model' => 'selected_order_payment_action'
			],
			// shipment/fulfilment status
			'shipping_statuses' => [
				'label' => $this->_('Shipping Status'),
				'description' => $this->_('Set a shipping/fulfilment status for this order.'),
				'notes' => $this->_('E.g., on hold, scheduled, partially shipped, delivered, etc.'),
				// 'x_model' => 'selected_order_shipment_status_action'
			],

		];

		$actionTypeOptions = $actionTypesOptions[$actionType];
		$label = $actionTypeOptions['label'];
		$description = $actionTypeOptions['description'];
		$notes = $actionTypeOptions['notes'];
		// $xmodel = $actionTypeOptions['x_model'];
		$xmodel = 'selected_status_action';

		// TODO FOR INVOICES, MAYBE NOW DO SEPARATELY IN OWN SELECT NOT SELECTIZE AND NOT HERE!

		####################
		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		// TODO 3 FIELDS? ONE EACH FOR ORDER, PAYMENT AND FULFILLMENT STATUSES?

		// TODO: add dynamic notes with respect to above
		$options = [
			'label' => $label,
			'description' => $description,
			'notes' => $notes,
			//  'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			// 'classes' => 'pwcommerce_product_generate_variants_attribute_options',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top pwcommerce_override_processwire_inputfield_header_padding_left pwcommerce_override_processwire_inputfield_content_padding_left',
			'classes' => 'pwcommerce_order_line_item_discount_type',
			'columnWidth' => 50,
			'select_options' => $this->getOptionsForSelectsForOrderActions($actionType),
		];

		$field = $this->pwcommerce->getInputfieldSelect($options);
		$xstore = $this->xstore;
		$field->attr([
			// 'x-model' => "{$xstore}.discountType",
			'x-model' => "{$xstore}.{$xmodel}",
			// 'x-bind:id' => '`pwcommerce_order_line_item_discount_type${product.id}`',
			// 'x-bind:name' => '`pwcommerce_order_line_item_discount_type${product.id}`',

		]);
		$wrapper->add($field);
		$out = $wrapper->render();
		// $out = $field->render();
		// -------
		return $out;
	}

	/**
	 * Get Options For Selects For Order Actions.
	 *
	 * @param mixed $actionType
	 * @return mixed
	 */
	private function getOptionsForSelectsForOrderActions($actionType) {
		# TODO @UPDATE: RETURNING THE TRANSLATED STRINGS INSTEAD!
		// $allOrderStatuses = $this->allOrderStatuses;

		$statuses = [];
		if ($actionType === 'order_statuses') {
			$statuses = $this->pwcommerce->getOrderOnlyStatuses();
		} else if ($actionType === 'payment_statuses') {
			$statuses = $this->pwcommerce->getPaymentOnlyStatuses();
		} else if ($actionType === 'shipping_statuses') {
			$statuses = $this->pwcommerce->getFulfilmentOnlyStatuses();
		}
		asort($statuses);
		return $statuses;
	}

	/**
	 * Modal for mark order actions.
	 *
	 * @return string $out Modal markup.
	 */
	// /**
  * Get Modal Markup For Confirm Mark Order As.
  *
  * @param string $mode
  * @return mixed
  */
 private function getModalMarkupForConfirmMarkOrderAs($mode = 'payment_mark_as_paid') {
	/**
	 * Get Modal Markup For Order Status Actions.
	 *
	 * @return mixed
	 */
	private function getModalMarkupForOrderStatusActions() {
		// ## ORDER MARK AS MODALs MARKUP  ##
		$xstore = $this->xstore;
		// $header = $this->_("Action TODO MODEL ACTION TYPE HERE X-TEXT - Status");
		// @UPDATE TODO FOR NOW WE SET IN THE HTMX RESPONSE IN THE BODY OF THE MODAL
		$header = $this->_("Action Status");

		$orderStatusActionProperty = "is_order_status_modal_open";
		// =======
		// HTMX
		// @note: these are for fetching the markup for requested order status action! not for posting
		// for posting, see the htmx attributes pass to the apply button in $this->renderModalMarkupForOrderStatusActionsApplyButton()
		$ajaxgGetURL = $this->ajaxPostURL;
		$hxVals = json_encode(['pwcommerce_order_status_action_context' => 'orders']);

		// TODO: ADD TOTAL DUE TO markAsInfo USING ALPINE JS -> order total pice -> next release!
		// @NOTE: WE NEED THE WATCH SO AS TO CLEAR SELECTED STATUS ACTION (draft, cancelled, abandoned, overdue, etc) IF MODAL IS CLOSED VIA THE 'x' RATHER THAN CANCEL
		$specialPaymentActionsflags = [
			'partially_paid' => PwCommerce::PAYMENT_STATUS_PARTIALLY_PAID,
			'paid' => PwCommerce::PAYMENT_STATUS_PAID,
			"partially_refunded" => PwCommerce::PAYMENT_STATUS_PARTIALLY_REFUNDED,
		];
		$specialPaymentActionsflagsJSON = json_encode($specialPaymentActionsflags);
		$xInitForOrderStatusActionsArray = [
			'watch_order_status_modal' => "\$watch(`{$xstore}.is_order_status_modal_open`, value => handleManualResetOrderStatusAction(value))",
			'order_status_special_payment_actions' => "initSpecialPaymentActionsFlags($specialPaymentActionsflagsJSON)"
		];
		$xInitForOrderStatusActionsString = implode(",", $xInitForOrderStatusActionsArray);
		$body =
			"<div x-init='{$xInitForOrderStatusActionsString}'>" .
			// ++++++++
			// HTMX
			// @note: the hx-vals is to tell ProcessPwCommerce::pageHandler to call the renderResults() here
			// i.e., in this context, i.e. 'prders'
			// @note: we will swap inside this div (default innerHTML)
			//
			// @note:
			// - 'pwcommerce_send_window_notification' will tell htmx:afterSettle look at the request config trigger element, grab details of a custom window event and send them to window.
			// - Alpine will be listening to that window event.
			// - In this case, it will use that to disabled the 'apply' button then close the modal shortly after.
			// - this approach is versatile and doesn't need the server to know about the events that need to be sent
			// - the event.detail.requestConfig.elt is our element with the event details; in this case it is the 'apply button'
			// - @see: $this->renderModalMarkupForOrderStatusActionsApplyButton()
			//
			// TODO DELETE pwcommerceorderstatusactionsendnotification IF NOT IN USE!!!!
			"<div id='pwcommerce_order_status_action_apply' hx-get='{$ajaxgGetURL}' hx-indicator='#pwcommerce_order_status_action_apply_spinner_indicator' hx-trigger='pwcommerceorderstatusfetch' hx-include='.pwcommerce_order_status_action_fetch_markup' hx-vals='{$hxVals}' @pwcommerceorderstatusactionsendnotification.window='handleOrderStatusActionSendNotification'>" .
			"</div>" .
			// ++++++++
			// spinner
			"<div id='pwcommerce_order_status_action_apply_spinner_indicator' class='htmx-indicator'>" .
			"<i class='fa fa-fw fa-spin fa-spinner'></i>" .
			$this->_("Please wait") .
			"&#8230;" .
			"</div>" .
			// ++++++++
			"</div>"; // end div with x-init
		// ==================================
		// apply button
		$applyButton = $this->renderModalMarkupForOrderStatusActionsApplyButton();
		// cancel button
		$cancelButton = $this->renderModalMarkupForOrderStatusActionsCancelButton();
		$footer = "<div class='ui-dialog-buttonset'>{$applyButton}{$cancelButton}</div>";
		$xproperty = $orderStatusActionProperty;
		$size = '4x-large';

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
	 * Render Modal Markup For Order Status Actions Apply Button.
	 *
	 * @return string|mixed
	 */
	private function renderModalMarkupForOrderStatusActionsApplyButton() {
		# ALPINE JS #
		$xstore = $this->xstore;
		$applyButtonOptions = [
			'type' => 'submit',
			// 'type' => 'button',
			# ALPINE JS #
			//
			// alpine js: disable apply button and apply opacity if applicable to some three use cases
			// i. payment status action: partly paid [3999]: if no payment method is selected OR part payment amount is empty
			// ii. payment status action: paid [4000]: if no payment method is selected
			// iii. payment status action: partly refunded [4998]: if refund amount is empty
			//
			'x-bind:disabled' => "!{$xstore}.is_ready_apply_order_status_action",
			'x-bind:class' => "{$xstore}.is_ready_apply_order_status_action ? `` : `opacity-50`",
			#################
			// @NOTE WE NEED THE PAGE TO RELOADED AFTER POSTING STATUSES (AS BEFORE) SO EDITOR/SHOP ADMIN CAN SEE CHANGES IN NOTES; EASIER THAN USING HTMX IN THIS CASE
		];

		// -----------
		$applyButton = $this->pwcommerce->getModalActionButton($applyButtonOptions, 'apply');

		// ===========
		return $applyButton;
	}
	/**
	 * Get rendered button for the modal for actioning a selected order status.
	 *
	 * @return string
	 */
	private function renderModalMarkupForOrderStatusActionsCancelButton(): string {
		$cancelButton = $this->pwcommerce->getModalActionButton(['x-on:click' => 'resetOrderStatusActionAndCloseModal'], 'cancel');
		return $cancelButton;
	}

	/**
	 * Get hidden markup to set order id of the order that will have its status actioned.
	 *
	 * @return mixed
	 */
	private function getOrderActionHiddenMarkupForOrderID() {
		//------------------- order_action_mark_order_as_order_id (getInputfieldHidden)
		$options = [
			'id' => "pwcommerce_order_actions_order_id",
			'name' => 'pwcommerce_order_actions_order_id',
			'value' => $this->orderPage->id
		];
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$field->addClass('pwcommerce_order_status_action_fetch_markup');
		// return $field;
		return $field->render();
	}

	/**
	 * Get hidden markup to model selected order status action.
	 *
	 * @return mixed
	 */
	private function getOrderSelectedStatusFlagForOrder() {
		// TODO
		//------------------- order_status_selected_action_fetch_markup (getInputfieldHidden)
		$options = [
			'id' => "pwcommerce_order_status_selected_action_fetch_markup",
			'name' => 'pwcommerce_order_status_selected_action_fetch_markup',
		];
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$field->addClass('pwcommerce_order_status_action_fetch_markup');
		$field->attr([
			'x-bind:value' => "{$this->xstore}.selected_status_action"
		]);
		return $field->render();
	}

	/**
	 * Get Edit Item U R L.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getEditItemURL($page) {
		// if page is locked, don't show edit URL
		if ($page->isLocked()) {
			$out = "<span>{$page->title}</span>";
		} else {
			$out = "<a href='{$this->adminURL}orders/edit/?id={$page->id}'>{$page->title}</a>";
		}
		return $out;
	}

	/**
	 * Get Bulk Edit Actions Panel.
	 *
	 * @return mixed
	 */
	private function getBulkEditActionsPanel() {
		$actions = [
			// TODO: NEED TO DISABLE THESE IF ORDER COMPLETE?
			'publish' => $this->_('Publish'),
			'unpublish' => $this->_('Unpublish'),
			// ----------------
			'lock' => $this->_('Lock'),
			'unlock' => $this->_('Unlock'),
			// ----------------
			'invoice_print' => $this->_('Print Invoice'),
			'invoice_email' => $this->_('Email Invoice'),
			//----------------
			'trash' => $this->_('Trash'),
			// TODO: DO WE SUPPORT THIS?
			'delete' => $this->_('Delete'),
		];
		$options = [
			// add new link
			'add_new_item_label' => $this->_('Create manual order'),
			// add new url
			'add_new_item_url' => "{$this->adminURL}orders/add/",
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
	 * @param mixed $xref
	 * @return mixed
	 */
	private function getBulkEditCheckbox($id, $name, $xref = null) {
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

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

	/**
	 * Build View Order.
	 *
	 * @return mixed
	 */
	private function buildViewOrder() {

		########### GET MARKUP ########

		$out =

			// order created date
			$this->getSingleViewOrderCreatedDate() .

			# 2-COLUMN GRID #

			"<div class='grid grid-cols-10 gap-4'>" .
			# -------------
			// ***** LARGE LEFT COLUMN [7] *****
			$this->renderLeftGridColumn() .
			// "<div class='col-span-full md:col-span-7 md:mr-7'>" .
			// "</div>" .
			// END: COL-SPAN-7
			# -------------
			// ***** RIGHT NARROWER COLUMN [3] *****
			$this->renderRightGridColumn() .
			// "<div class='col-span-full md:col-span-3 order-first md:order-last md:mt-5'>" .
			// "</div>" .
			// END: COL-SPAN-3
			# -------------
			// END GRID
			"</div>";
		return $out;
	}

	/**
	 * Render single order view headline to append to the Process headline in PWCommerce.
	 *
	 * @param Page $orderPage
	 * @return string|mixed
	 */
	public function renderViewItemHeadline(Page $orderPage) {
		$headline = $this->_('View order');
		if ($orderPage->id) {
			// TODO: need to make sure this TITLE is formatted correctly! e.g. add prefix or suffixes as per  general settings! TODO: if suffix or prefix, add to order id, else show title!
			// $headline .= ": {$orderPage->title}";
			$headline .= ": #{$orderPage->id}";
		}
		return $headline;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~

	/**
	 * Render Print Item.
	 *
	 * @param Page $orderPage
	 * @return string|mixed
	 */
	public function renderPrintItem(Page $orderPage) {

		if (!$orderPage->id) {
			// TODO: return in markup for consistency!
			return "<p>" . $this->_('Order was not found!') . "</p>";
		} else {
			return $this->renderPrintOrderInvoice($orderPage);
		}
	}

	/**
	 * Render Print Order Invoice.
	 *
	 * @param Page $orderPage
	 * @return string|mixed
	 */
	private function renderPrintOrderInvoice(Page $orderPage) {
		$templateFile = "invoice.php";
		/** @var TemplateFile $t */
		$t = $this->pwcommerce->buildPrintOrderInvoice($orderPage, $templateFile);
		echo $t->render();
		exit();
	}

	/**
	 * Render Email Item.
	 *
	 * @param Page $orderPage
	 * @return string|mixed
	 */
	public function renderEmailItem(Page $orderPage) {
		// TODO: nothing to render really! rename!??? leave it for now
		if (!$orderPage->id) {
			// TODO: return in markup for consistency!
			return "<p>" . $this->_('Order was not found!') . "</p>";
		} else {
			return $this->emailOrderInvoice($orderPage);
		}
	}

	/**
	 * Email Order Invoice.
	 *
	 * @param Page $orderPage
	 * @return mixed
	 */
	private function emailOrderInvoice(Page $orderPage) {
		$this->pwcommerce->sendConfirmation($orderPage);
		$notice = sprintf(__("Emailed invoice for order number %d."), $orderPage->id);
		$result = [
			'notice_type' => 'success',
			'notice' => $notice,
			'special_redirect' => "/view/?id={$orderPage->id}"
		];
		// -----------
		return $result;
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

	// ~~~~~~~~~~~~~
	/**
	 * Is Order No Longer Editable.
	 *
	 * @param WireData $order
	 * @return bool
	 */
	private function isOrderNoLongerEditable(WireData $order) {

		return (int) $order->orderStatus > PwCommerce::ORDER_STATUS_DRAFT;
	}

	/**
	 * Is Order Has Customer Email.
	 *
	 * @return bool
	 */
	private function isOrderHasCustomerEmail() {
		$orderPage = $this->orderPage;
		$orderCustomer = $orderPage->get(PwCommerce::ORDER_CUSTOMER_FIELD_NAME);
		return !empty($orderCustomer->email);
	}

	/**
	 * Get Order Customer Page I D.
	 *
	 * @param WireData $customer
	 * @return mixed
	 */
	private function getOrderCustomerPageID(WireData $customer) {
		$customerPageID = 0;
		// if customers feature not installed in shop, nothing to do!
		$customersFeature = 'customers';
		if (!empty($this->pwcommerce->isOptionalFeatureInstalled($customersFeature))) {
			$customerPageID = $this->pwcommerce->getRaw("template=customer,customer.email={$customer->email}", 'id');
		}
		// ---------
		return $customerPageID;
	}

	/**
	 * Get Order Mark As Payments To Show.
	 *
	 * @param WireData $order
	 * @return mixed
	 */
	private function getOrderMarkAsPaymentsToShow(WireData $order) {
		$markAsPaymentsToShow = [];
		$paymentStatus = $order->paymentStatus;
		if ($paymentStatus < PwCommerce::PAYMENT_STATUS_AWAITING_PAYMENT) {
			// payment not marked as pending yet
			$markAsPaymentsToShow = ['payment_mark_as_pending', 'payment_mark_as_paid'];
		} else if ($paymentStatus >= PwCommerce::PAYMENT_STATUS_AWAITING_PAYMENT && $paymentStatus < PwCommerce::PAYMENT_STATUS_PAID) {
			// payment already marked as pending; can only be marked as paid now
			$markAsPaymentsToShow = ['payment_mark_as_paid'];
		} else {
			// nothing, i.e. $paymentStatus >= 4000 -> already marked as paid
		}
		// -------
		return $markAsPaymentsToShow;
	}

	# >>>>>>>>>>>>>>>>>>> AJAX <<<<<<<<<<<<<<<<<<<

	/**
	 * Handle Ajax Order Status Action.
	 *
	 * @return mixed
	 */
	private function handleAjaxOrderStatusAction() {

		// $out = "";
		$out = $this->processAjaxGetRequestForOrderStatusAction();
		// $sanitizer = $this->wire('sanitizer');

		// $requestType = $sanitizer->text($selector);

		// if ($requestType === 'GET') {
		// 	// GET REQUEST
		// 	// for GET requests we ARE ONLY returning MARKUP
		// 	$out = $this->processAjaxGetRequestForOrderStatusAction();

		// } else if ($requestType === 'POST') {
		// 	// POST REQUEST
		// 	// TODO DELETE IF NOT IN USE
		// 	// CURRENT THIS GOES TO PWCommerceActions::manuallySetOrderStatus

		// }

		// =======
		return $out;
	}

	/**
	 * Process Ajax Get Request For Order Status Action.
	 *
	 * @return mixed
	 */
	private function processAjaxGetRequestForOrderStatusAction() {

		$input = $this->wire('input');
		$pages = $this->wire('pages');
		$out = "";
		$isError = false;
		$error = "";

		// ---------
		// *********** GET INPUTS *********
		$statusActionOrderPageID = (int) $input->get('pwcommerce_order_actions_order_id');
		$statusCode = (int) $input->get('pwcommerce_order_status_selected_action_fetch_markup');

		// *********** ERROR HANDLING *********
		$orderPage = $pages->getRaw("id={$statusActionOrderPageID}", ['id', 'pwcommerce_order']);
		$orderPageID = $orderPage['id'];
		$isValidStatusCode = $this->pwcommerce->isValidStatusCode($statusCode);

		if (empty($orderPageID)) {
			// unexpected ORDER (page) ID
			$isError = true;
			$error = $this->_("Invalid order ID");
		} else if (empty($isValidStatusCode)) {
			// invalid order status code/flag
			$isError = true;
			$error = $this->_("Invalid order status action");
		}
		if (!empty($isError)) {
			$out = "<p class='text-red-500'>" . $error . "</p>";
		} else {
			// GOOD TO GO:
			$this->applyStatusCode = $statusCode;
			$this->applyStatusCodeOrderID = $orderPageID;
			$this->applyStatusCodeOrderTotalPrice = $orderPage['pwcommerce_order']['order_total_price'];

			// *********** GET MARKUP *********
			$out = $this->buildMarkupForAjaxGetRequestForOrderStatusAction();
		}

		// *********** SEND RESULT BACK TO HTMX *********

		return $out;
	}

	/**
	 * Build Markup For Ajax Get Request For Order Status Action.
	 *
	 * @return string
	 */
	private function buildMarkupForAjaxGetRequestForOrderStatusAction(): string {
		$out = "";
		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
		];

		# ++++++++++++++

		// get status type
		$statusType = $this->pwcommerce->getOrderStatusTypeStringByStatusCode($this->applyStatusCode);
		// get status title/name
		$name = $this->pwcommerce->getOrderStatusByStatusCode($this->applyStatusCode);
		// get status description
		$description = $this->pwcommerce->getOrderDescriptionByStatusCode($this->applyStatusCode);
		// prepare markup for status type, name and description
		$valueForMarkupForStatus =
			// status name
			"<h4>{$statusType}: {$name}</h4>" .
			// status description
			"<p class='description'>{$description}</p>" .
			// divider
			"<hr class='my-1'>";
		// -------------
		$options['value'] = $valueForMarkupForStatus;
		$field = $this->pwcommerce->getInputfieldMarkup($options);
		$wrapper->add($field);
		# ++++++++++++++++

		// get extra markup if status needs it
		// e.g. some payment statuses need payment methods
		if (in_array($this->applyStatusCode, $this->orderStatusActionsRequiringExtraMarkup())) {
			// @note: we pass $wrapper since some methods might get more than one $field
			// so, we add them to the $wrapper there instead
			$wrapper = $this->orderStatusActionsExtraMarkup($wrapper);
		}
		// get textarea for notes for status
		$field = $this->getOrderStatusApplicationNoteTextareaField();
		$wrapper->add($field);

		# ++++++++++++++++
		// TODO NOT IN USE FOR NOW! BETTER TO SEND A COMPLETE MESSAGE TO CUSTOMER INSTEAD OF A VERY SHORT ONE SUCH AS 'your order has been marked as pendind' without giving further details!
		// TODO SHOULD WE ADD RICH TEXT AREA FOR CUSTOMER NOTE? PULL FROM A TEMPLATE? IF YES, HOW ABOUT EDITING THE TEMPLATE? IN FUTURE, WE WILL USE NOTIFY FEATURE! -> SO, IMPLEMENTATION THEN
		// get checkbox for notify customer about status change/update
		// $field = $this->getOrderStatusApplicationNotifyCustomerCheckbox();
		// $wrapper->add($field);
		# ++++++++++++++++
		$wrapper = $this->getHiddenInputsForOrderStatusApplication($wrapper);

		// =========
		$out =
			// TODO THIS DIV NEEDS HTMX ATTRIBUTES THAT WILL BE TRIGGERED WHEN USER CLICKS APPLY BUTTON ON THE MODAL -> WHEN HANDLING WILL CONSIDER PAGE RELOAD (REDIRECT) DEPENDING ON CONTEXT
			# @UPDATE: SATURDAY 22 APRIL 2023 - USING NORMAL FORM SUBMISSION AND NOT AJAX/HTMX
			// "<div id='pwcommerce_order_status_fetch_markup_response_wrapper' class='pwcommerce_send_window_notification' x-init='initIsReadyApplyOrderStatusAction'>" .
			"<div id='pwcommerce_order_status_fetch_markup_response_wrapper' x-init='initIsReadyApplyOrderStatusAction'>" .
			$wrapper->render() .
			// ######
			"</div>";

		// --------
		return $out;
	}

	/**
	 * Get Order Status Application Note Textarea Field.
	 *
	 * @return InputfieldTextarea
	 */
	private function getOrderStatusApplicationNoteTextareaField(): InputfieldTextarea {

		//------------------- note text/content/value (getInputfieldTextarea/getInputfieldMarkup)

		$options = [
			'id' => "pwcommerce_order_status_note_for_selected_action_apply",
			'name' => "pwcommerce_order_status_note_for_selected_action_apply",
			'label' => $this->_('Admin Note'),
			// 'description' => $this->_('Action note.'),
			'notes' => $this->_('Optionally add a note about this action. This will only be seen by shop admins.'),
			'collapsed' => Inputfield::collapsedNever,
			'rows' => 2,
			'classes' => 'pwcommerce_note_text',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
			'value' => "",
			// @note: to match payment select field
			// @see: renderOrderStatusApplicationCapturePaymentMethodSelectField()
			'columnWidth' => 99,
		];
		/** @var InputfieldTextarea $field */
		$field = $this->pwcommerce->getInputfieldTextarea($options);
		$field->addClass('pwcommerce_order_status_action_apply');
		return $field;
	}

	/**
	 * Get Order Status Application Notify Customer Checkbox.
	 *
	 * @return mixed
	 */
	private function getOrderStatusApplicationNotifyCustomerCheckbox() {

		// TODO NOT IN USE FOR NOW! BETTER TO SEND A COMPLETE MESSAGE TO CUSTOMER INSTEAD OF A VERY SHORT ONE SUCH AS 'your order has been marked as pendind' without giving further details!

		//------------------- order_status_notify_customer_for_selected_action_apply (getInputfieldCheckbox)

		// $label = "<span class='ml-3'>" . $this->_('Notify Customer') . "</span>";
		$label2 = "<span class='ml-3'>" . $this->_('Send customer an email about the status update') . "</span>";

		$options = [
			'id' => "pwcommerce_order_status_notify_customer_for_selected_action_apply",
			'name' => "pwcommerce_order_status_notify_customer_for_selected_action_apply",
			'label' => $this->_('Notify Customer'),
			// @note: skipping label
			// 'label2' => $this->_('Send customer an email about the status update'),
			'label2' => $label2,
			'description' => $this->_('Optionally email customer about status update.'),
			'notes' => $this->_('Customer will receive a generic email about the update.'),
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_bulk_edit_selected_items',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => 1,

		];

		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		// @note: disable entity encode of label so we can render own markup for the checkbox label
		$field->entityEncodeLabel = false;

		return $field;
	}

	/**
	 * Get Hidden Inputs For Order Status Application.
	 *
	 * @param mixed $wrapper
	 * @return InputfieldWrapper
	 */
	private function getHiddenInputsForOrderStatusApplication($wrapper): InputfieldWrapper {
		// hidden input to track status code/flag if status is confirmed and applied
		$options = [
			'id' => "pwcommerce_order_status_selected_action_apply",
			'name' => 'pwcommerce_order_status_selected_action_apply',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => $this->applyStatusCode,
		];
		//------------------- order_status_selected_action_apply (getInputfieldHidden)
		/** @var InputfieldHidden $field */
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$field->attr([
			'x-ref' => 'pwcommerce_order_status_selected_action_apply',

		]);
		$field->addClass('pwcommerce_order_status_action_apply');
		$wrapper->add($field);

		// hidden input to track order ID if status is confirmed and applied
		$options = [
			'id' => "pwcommerce_order_status_order_id_for_selected_action_apply",
			'name' => 'pwcommerce_order_status_order_id_for_selected_action_apply',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => $this->applyStatusCodeOrderID,
		];
		//------------------- order_status_order_id_for_selected_action_apply (getInputfieldHidden)
		/** @var InputfieldHidden $field */
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$field->addClass('pwcommerce_order_status_action_apply');
		$wrapper->add($field);

		// --------
		return $wrapper;
	}

	/**
	 * Order Status Actions Requiring Extra Markup.
	 *
	 * @return mixed
	 */
	private function orderStatusActionsRequiringExtraMarkup() {
		return [
			'partially_paid' => PwCommerce::PAYMENT_STATUS_PARTIALLY_PAID,
			'paid' => PwCommerce::PAYMENT_STATUS_PAID,
			"partially_refunded" => PwCommerce::PAYMENT_STATUS_PARTIALLY_REFUNDED,
			'refunded' => PwCommerce::PAYMENT_STATUS_REFUNDED
		];
	}

	/**
	 * Order Refund Status Actions Requiring Extra Markup.
	 *
	 * @return mixed
	 */
	private function orderRefundStatusActionsRequiringExtraMarkup() {
		return [

			"partially_refunded" => PwCommerce::PAYMENT_STATUS_PARTIALLY_REFUNDED,
			'refunded' => PwCommerce::PAYMENT_STATUS_REFUNDED
		];
	}

	/**
	 * Order Payment Capture Status Actions Requiring Extra Markup.
	 *
	 * @return mixed
	 */
	private function orderPaymentCaptureStatusActionsRequiringExtraMarkup() {
		return [
			'partially_paid' => PwCommerce::PAYMENT_STATUS_PARTIALLY_PAID,
			'paid' => PwCommerce::PAYMENT_STATUS_PAID
		];
	}

	/**
	 * Order Status Actions Extra Markup.
	 *
	 * @param mixed $wrapper
	 * @return InputfieldWrapper
	 */
	private function orderStatusActionsExtraMarkup($wrapper): InputfieldWrapper {
		// @note: currently, all the statuses that require extra markup have to do with payments; 2 for taking payment and 2 for refunding money
		// -------

		// ---------
		$refundsActions = $this->orderRefundStatusActionsRequiringExtraMarkup();
		$paymentCapturesActions = $this->orderPaymentCaptureStatusActionsRequiringExtraMarkup();
		# DETERMINE EXTRA MARKUP TO GET
		if (in_array($this->applyStatusCode, $refundsActions)) {
			// REFUNDS MARKUP
			$field = $this->orderStatusActionsExtraMarkupForRefunds();
			$wrapper->add($field);
		} else if (in_array($this->applyStatusCode, $paymentCapturesActions)) {
			// PAYMENT CAPTURES MARKUP
			$wrapper = $this->orderStatusActionsExtraMarkupForCapturedPayment($wrapper);
		}
		// --------
		return $wrapper;
	}
	/**
	 * Order Status Actions Extra Markup For Refunds.
	 *
	 * @return mixed
	 */
	private function orderStatusActionsExtraMarkupForRefunds() {
		// @note: extra markup to capture amount that was refunded IF PARTIAL
		// else markup that shows (for confirmation) THE ORDER TOTAL
		$field = $this->renderOrderStatusApplicationRefundAmountMarkup();
		return $field;
	}

	/**
	 * Order Status Actions Extra Markup For Captured Payment.
	 *
	 * @param mixed $wrapper
	 * @return InputfieldWrapper
	 */
	private function orderStatusActionsExtraMarkupForCapturedPayment($wrapper): InputfieldWrapper {
		// @note: extra markup to capture amount that was captured and the payment gateway details

		$key = $this->applyStatusCode === PwCommerce::PAYMENT_STATUS_PARTIALLY_PAID ? 'partially_paid' : 'paid';

		$capturedPayentsOptions = [
			// 3999
			'partially_paid' => [
				'info' => $this->_('The payment status of the order will be changed to partially paid. You will not be able to change the payment status to pending after this action. However, you will still be able to add paymenet status in the future. This could be partial or full payments statuses. Please select a payment method below to continue.'),
			],
			// 4000
			'paid' => [
				'info' => $this->_('The payment status of the order will be changed to paid. You will not be able to change the payment status to pending after this action. Please select a payment method below to continue.'),
			],
		];

		$notes = $capturedPayentsOptions[$key]['info'];

		// if partial payment, capture the amount of the part payment
		/** @var InputfieldText $field */
		if ($this->applyStatusCode === PwCommerce::PAYMENT_STATUS_PARTIALLY_PAID) {
			$field = $this->getOrderStatusApplicationPartialPaymentAmountTextField();
			$wrapper->add($field);
		}

		// =======
		// select field for payment method used
		/** @var InputfieldSelect $field */
		$field = $this->renderOrderStatusApplicationCapturePaymentMethodSelectField($notes);
		$wrapper->add($field);

		// --------
		return $wrapper;
	}

	/**
	 * Render Order Status Application Capture Payment Method Select Field.
	 *
	 * @param mixed $notes
	 * @return string|mixed
	 */
	private function renderOrderStatusApplicationCapturePaymentMethodSelectField($notes) {

		// TODO NEED TO ACCOUNT FOR STORES THAT HAVEN'T INSTALLED PAYMENT PROVIDER FEATURE!
		// TODO FOR NOW, WE STORE AS CUSTOM WITH ID 0?
		$xstore = $this->xstore;

		$activePaymentProvidersSelectOptions = [];
		/** @var array $paymentProviders */
		// get all active payment providers/gateways for this shop
		$paymentProviders = $this->pwcommerce->getActivePaymentProviders();

		// TODO!
		// if no payment providers installed, just use a generic 'custom' for now!
		if (empty($paymentProviders)) {
			$activePaymentProvidersSelectOptions[] = '';
			$activePaymentProvidersSelectOptions[0] = $this->_('Custom Payment');
		} else {
			// build payment providers radio options
			foreach ($paymentProviders as $paymentGateway) {
				$activePaymentProvidersSelectOptions[$paymentGateway['id']] = $paymentGateway['title'];
			}
		}

		// --------
		$options = [
			'id' => "pwcommerce_order_status_payment_method_for_selected_action_apply",
			'name' => 'pwcommerce_order_status_payment_method_for_selected_action_apply',
			'label' => $this->_('Payment Method'),
			'required' => true,
			// TODO - GOING FUNKY HERE IF DON'T SET WIDTH!!!
			'columnWidth' => 99,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
			'select_options' => $activePaymentProvidersSelectOptions,
			'notes' => $notes
		];
		$field = $this->pwcommerce->getInputfieldSelect($options);
		$field->addClass('pwcommerce_order_status_action_apply');

		// -----
		$field->attr([
			'x-model' => "{$xstore}.selected_order_status_action_payment_method",
			'x-on:change' => "handleOrderStatusPaymentMethodChange",
		]);
		return $field;
	}

	/**
	 * Render Order Status Application Refund Amount Markup.
	 *
	 * @return string|mixed
	 */
	private function renderOrderStatusApplicationRefundAmountMarkup() {
		if ($this->applyStatusCode === PwCommerce::PAYMENT_STATUS_PARTIALLY_REFUNDED) {
			$field = $this->getOrderStatusApplicationPartialRefundAmountTextField();
		} else {
			$field = $this->getOrderStatusApplicationFullRefundAmountText();
		}
		return $field;
	}

	/**
	 * Get Order Status Application Partial Refund Amount Text Field.
	 *
	 * @return mixed
	 */
	private function getOrderStatusApplicationPartialRefundAmountTextField() {

		//------------------- order_status_refunded_amount_for_selected_action_apply (getInputfieldText)
		// append currency symbol string if available

		$shopCurrencySymbolString = $this->pwcommerce->renderShopCurrencySymbolString();
		if (strlen($shopCurrencySymbolString)) {
			$this->shopCurrencySymbolString = " " . $shopCurrencySymbolString;
		}
		$description = $this->_('Specify partially refunded amount for this order');
		// append currency symbol string if available
		$description .= $this->shopCurrencySymbolString . '.';

		$options = [
			'id' => "pwcommerce_order_status_refunded_amount_for_selected_action_apply",
			'name' => "pwcommerce_order_status_refunded_amount_for_selected_action_apply",
			'type' => 'number',
			'step' => '0.01',
			'min' => 0,
			'label' => $this->_('Partially Refunded Amount'),
			'description' => $description,
			'required' => true,
			// @note: to match payment select field
			// @see: renderOrderStatusApplicationCapturePaymentMethodSelectField()
			'columnWidth' => 99,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
		];
		$field = $this->getOrderStatusApplicationPaymentAmountsTextField($options);
		// --------
		return $field;
	}

	/**
	 * Get Order Status Application Partial Payment Amount Text Field.
	 *
	 * @return mixed
	 */
	private function getOrderStatusApplicationPartialPaymentAmountTextField() {

		//-------------------  order_status_paid_amount_for_selected_action_apply (getInputfieldText)
		// append currency symbol string if available
		$shopCurrencySymbolString = $this->pwcommerce->renderShopCurrencySymbolString();
		if (strlen($shopCurrencySymbolString)) {
			$this->shopCurrencySymbolString = " " . $shopCurrencySymbolString;
		}
		$description = $this->_('Specify partially paid amount for this order');
		// append currency symbol string if available
		$description .= $this->shopCurrencySymbolString . '.';

		$options = [
			'id' => "pwcommerce_order_status_paid_amount_for_selected_action_apply",
			'name' => "pwcommerce_order_status_paid_amount_for_selected_action_apply",
			'type' => 'number',
			'step' => '0.01',
			'min' => 0,
			'label' => $this->_('Partially Paid Amount'),
			'description' => $description,
			'required' => true,
			// @note: to match payment select field
			// @see: renderOrderStatusApplicationCapturePaymentMethodSelectField()
			'columnWidth' => 99,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
		];
		$field = $this->getOrderStatusApplicationPaymentAmountsTextField($options);
		// --------
		return $field;
	}

	/**
	 * Get Order Status Application Payment Amounts Text Field.
	 *
	 * @param array $options
	 * @return mixed
	 */
	private function getOrderStatusApplicationPaymentAmountsTextField($options) {
		$xstore = $this->xstore;
		$field = $this->pwcommerce->getInputfieldText($options);
		$field->attr([
			'x-model.number' => "{$xstore}.selected_order_status_action_payment_amount",
			'x-on:input' => 'handleOrderStatusPaymentAmountChange',
		]);
		$field->addClass('pwcommerce_order_status_action_apply');
		// --------
		return $field;
	}

	/**
	 * Get Order Status Application Full Refund Amount Text.
	 *
	 * @return mixed
	 */
	private function getOrderStatusApplicationFullRefundAmountText() {
		$orderTotalAmount = $this->pwcommerce->getValueFormattedAsCurrencyForShop($this->applyStatusCodeOrderTotalPrice);
		$fullRefundAmount = "<span class='font-bold'>" . sprintf(__("Refunded amount: %s"), $orderTotalAmount) . "</span>";
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $fullRefundAmount,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		return $field;
	}

	# ~~~~~~~~~~~~~~ HOOKABLE ~~~~~~~~~~~~~~

	# >>> hookable:layout <<<

	/**
	 * Render the markup for a single order view.
	 *
	 * @param Page $orderPage
	 * @return mixed
	 */
	public function ___renderViewItem(Page $orderPage) {

		$this->orderPage = $orderPage;
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		$out = "";
		// get the order by its ID
		//   $orderPage = $this->wire('pages')->get("id={$id}");
		if (!$orderPage->id) {
			// TODO: return in markup for consistency!
			$out = "<p>" . $this->_('Order was not found!') . "</p>";
		} else {
			// $out = $this->buildViewOrder();
			// DETERMINE HOW TO RENDER SINGLE ORDER PAGE VIEW
			// +++++
			$customPartialTemplate = $this->pwcommerce->getBackendPartialTemplate(PwCommerce::PROCESS_RENDER_SINGLE_ORDER_VIEW_PARTIAL_TEMPLATE_NAME);
			if (!empty($customPartialTemplate)) {
				// CUSTOM PWCOMMERCE PROCESS RENDER SINGLE ORDER VIEW BACKEND MARKUP
				// set order PAGE
				$customPartialTemplate->set('orderPage', $this->orderPage);
				// set ORDER itself
				$customPartialTemplate->set('order', $this->orderPage->get(PwCommerce::ORDER_FIELD_NAME));
				// set order LINE ITEMS
				$customPartialTemplate->set('orderLineItems', $this->orderPage->children());
				// set order CUSTOMER
				$customPartialTemplate->set('orderCustomer', $this->orderPage->get(PwCommerce::ORDER_CUSTOMER_FIELD_NAME));
				// set order DISCOUNTS
				$customPartialTemplate->set('orderDiscounts', $this->orderPage->get(PwCommerce::ORDER_DISCOUNTS_FIELD_NAME));
				// set order NOTES
				$customPartialTemplate->set('orderNotes', $this->orderPage->get(PwCommerce::ORDER_NOTES_FIELD_NAME));
				# +++++++++++
				// GET MARKUP
				$out = $customPartialTemplate->render();
			} else {
				// DEFAULT PWCOMMERCE PROCESS RENDER ORDERS BACKEND MARKUP
				$out = $this->buildViewOrder();
			}
		}

		//------------------
		// generate final markup
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			'classes' => 'pwcommerce_order_view',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		$wrapper->add($field);

		return $wrapper->render();
	}

	/**
	 * Render markup for the left column of the single order view grid.
	 *
	 * @return mixed
	 */
	protected function ___renderLeftGridColumn() {
		$out =
			"<div class='col-span-full md:col-span-7 md:mr-7'>" .
			# -------------
			// line items
			$this->renderOrderLineItemsBlock() .
			// order discounts total block
			$this->renderOrderDiscountsTotalsBlock() .
			// shipping details (rate, delivery and handling)
			$this->renderOrderShippingBlock() .
			// totals
			$this->renderOrderTotalsBlock() .
			// notes
			$this->renderOrderNotesBlock() .
			# -------------
			// END: COL-SPAN-7
			"</div>";
		return $out;
	}

	/**
	 * Render markup for current order line items block in the grid.
	 *
	 * @return mixed
	 */
	protected function ___renderOrderLineItemsBlock() {
		$out =
			// line items heading
			$this->getSingleViewOrderLineItemsHeading() .
			// -------
			// line items main content
			$this->getSingleViewOrderLineItemsMainContent();
		return $out;
	}

	/**
	 * Render markup for current order discounts total block in the grid.
	 *
	 * @return mixed
	 */
	protected function ___renderOrderDiscountsTotalsBlock() {
		$orderPage = $this->orderPage;
		$order = $orderPage->get(PwCommerce::ORDER_FIELD_NAME);
		//--------------
		// order discounts total formatted as currency
		$discountsTotal = $this->pwcommerce->getValueFormattedAsCurrencyForShop($order->orderLineItemsTotalDiscount);
		// =============
		$out = "";
		// if discounts applied to order, render total details
		if (!empty($order->orderLineItemsTotalDiscount)) {
			// order total discounts amount applied
			$out =
				// order discounts total heading
				$this->getSingleViewOrderDiscountsTotalHeading() .
				$this->getSingleViewOrderDiscountsTotal($discountsTotal);
		}

		return $out;
	}

	/**
	 * Render markup for current order shipping details block in the grid.
	 *
	 * @return mixed
	 */
	protected function ___renderOrderShippingBlock() {
		// TODO ALSO SHOW FREE SHIPPING IF APPLICABLE PLUS HOW MUCH SHIPPING WOULD HAVE COST OTHERWISE
		$orderPage = $this->orderPage;
		$order = $orderPage->get(PwCommerce::ORDER_FIELD_NAME);
		//--------------
		// order shipping + handling total
		$orderShippingFeePlusHandlingFeeTotal = $order->orderShippingFeePlusHandlingFeeTotal;

		$freeShippingDiscount = NULL;
		if (!empty($order->freeShippingDiscount)) {
			// free shipping discount was applied to order
			// show shipping and handling fee totals as NEGATIVE
			$orderShippingFeePlusHandlingFeeTotal = $orderShippingFeePlusHandlingFeeTotal * -1;
			// -----
			$freeShippingDiscount = $order->freeShippingDiscount;
		}

		// ---------
		$shippingAndHandlingTotal = $this->pwcommerce->getValueFormattedAsCurrencyForShop($orderShippingFeePlusHandlingFeeTotal);
		// -------------



		// =============
		$out =
			// shipping details heading
			$this->getSingleViewOrderShippingDetailsHeading() .
			// order shipping rate name + estimated delivery time details
			$this->getSingleViewOrderShippingRateDetails($order->shippingRateName, $order->shippingRateDeliveryTimeMinimumDays, $order->shippingRateDeliveryTimeMaximumDays) .
			// order shipping + handling total details
			$this->getSingleViewOrderShippingAndHandlingTotal($shippingAndHandlingTotal, $freeShippingDiscount);
		return $out;
	}

	/**
	 * Render markup for current order totals block in the grid.
	 *
	 * @return mixed
	 */
	protected function ___renderOrderTotalsBlock() {
		$orderPage = $this->orderPage;
		$order = $orderPage->get(PwCommerce::ORDER_FIELD_NAME);
		// -------------
		// grand total
		$grandTotal = $this->pwcommerce->getValueFormattedAsCurrencyForShop($order->totalPrice);
		// =============
		$out =
			// -------
			// grand total details
			// GRAND TOTAL INCLUDING SHIPPING + HANDLING + TAXES
			// order grand total
			// TODO RETHINK MARKUP!
			// TODO:  + PAYMENT TYPE + PAYMENT STATUS???, etc? own block?
			$this->getSingleViewOrderGrandTotal($grandTotal) .
			// TODO HR OK HERE?
			"<hr>";
		return $out;
	}

	/**
	 * Render markup for current order line items block in the grid.
	 *
	 * @return mixed
	 */
	protected function ___renderOrderNotesBlock() {
		$out =

			// order notes heading
			$this->getSingleViewOrderNotesHeading() .
			// -------
			// order notes main content
			$this->getSingleViewOrderNotesMainContent();
		return $out;
	}

	/**
	 * Render markup for the right column of the single order view grid.
	 *
	 * @return mixed
	 */
	protected function ___renderRightGridColumn() {
		$out =
			"<div class='col-span-full md:col-span-3 order-first md:order-last'>" .
			# -------------
			// status
			$this->renderOrderStatusBlock() .
			// edit
			$this->renderOrderEditBlock() .
			// customer
			$this->renderOrderCustomerBlock() .
			// actions
			$this->renderOrderActionsBlock() .
			# -------------
			// END: COL-SPAN-3
			"</div>";
		return $out;
	}

	/**
	 * Render markup for current order status block in the grid.
	 *
	 * @return mixed
	 */
	protected function ___renderOrderStatusBlock() {
		$out =
			// order status heading
			$this->getSingleViewOrderStatusHeading() .
			// -------
			// order status main content
			$this->getSingleViewOrderStatusMainContent() .
			// -------
			// divider for smaller screens use
			$this->renderRightGridColumnBlocksDivider();
		return $out;
	}

	/**
	 * Render markup for current order edit block in the grid.
	 *
	 * @return mixed
	 */
	protected function ___renderOrderEditBlock() {
		$out =
			// order edit heading
			$this->getSingleViewOrderEditHeading() .
			// -------
			// order edit main content
			$this->getSingleViewOrderEditMainContent() .
			// -------
			// divider for smaller screens use
			$this->renderRightGridColumnBlocksDivider();
		return $out;
	}

	/**
	 * Render markup for current order customer block in the grid.
	 *
	 * @return mixed
	 */
	protected function ___renderOrderCustomerBlock() {
		$out =
			// order customer heading
			$this->getSingleViewOrderCustomerHeading() .
			// -------
			// order customer main content
			$this->getSingleViewOrderCustomerMainContent() .
			// -------
			// divider for smaller screens use
			// @note: hidden on mid screen sizes updward
			// we use it to show 'divider' in smaller screens to separate it and orders items (THEN) below it
			$this->renderRightGridColumnBlocksDivider();
		return $out;
	}

	/**
	 * Render markup for current order actions block in the grid.
	 *
	 * @return mixed
	 */
	protected function ___renderOrderActionsBlock() {
		$out =
			// order actions heading
			$this->getSingleViewOrderActionsHeading() .
			// -------
			// order actions main content
			$this->getSingleViewOrderActionsMainContent() .
			// -------
			// divider for smaller screens use
			$this->renderRightGridColumnBlocksDivider();
		return $out;
	}

	# >>> hookable:headings <<<

	/**
	 * Get markup of current order line items block heading.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderLineItemsHeading() {
		// TODO H3 OK?
		// $out = "<h2 class='mt-5'>" . $this->_('Order Items') . "</h2>";
		$out = "<h3 class='mb-1'>" . $this->_('Order Items') . "</h3>";
		return $out;
	}

	/**
	 * Get markup of current order shipping details for details block heading.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderDiscountsTotalHeading() {
		// TODO H3 OK?
		// $out = "<h2 class='mt-5'>" . $this->_('Order Discounts Total') . "</h2>";
		$out = "<h3 class='mb-1'>" . $this->_('Discounts Total') . "</h3>";
		return $out;
	}

	/**
	 * Get markup of current order shipping details for details block heading.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderShippingDetailsHeading() {
		// TODO H3 OK?
		// $out = "<h2 class='mt-5'>" . $this->_('Order Items') . "</h2>";
		$out = "<h3 class='mb-1'>" . $this->_('Shipping') . "</h3>";
		return $out;
	}

	/**
	 * Get markup of current order notes block heading.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderNotesHeading() {
		// TODO H3 OK?
		// $out = "<h2>" . $this->_('Order Notes') . "</h2>";
		$out = "<h3 class='mt-5 mb-1'>" . $this->_('Order Notes') . "</h3>";
		return $out;
	}

	/**
	 * Get markup of heading of current order status block.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderStatusHeading() {
		$out = "<h3 class='mb-1'>" . $this->_('Status') . "</h3>";
		return $out;
	}

	/**
	 * Get markup of heading of current order edit block.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderEditHeading() {
		$out = "<h3 class='mt-5 mb-1'>" . $this->_('Edit') . "</h3>";
		return $out;
	}

	/**
	 * Get markup of heading of current order customer block.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderCustomerHeading() {
		$out = "<h3 class='mt-5 mb-1'>" . $this->_('Customer') . "</h3>";
		return $out;
	}

	/**
	 * Get markup of heading of current order actions block.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderActionsHeading() {
		$out = "<h3 class='mt-5 mb-1'>" . $this->_('Actions') . "</h3>";
		return $out;
	}

	/**
	 * Get markup of current order invoice actions sub-heading for actions block.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderInvoiceActionsSubHeading() {
		$out = "<h4 class='pwcommerce_override_processwire_heading_margin_top'>" . $this->_('Invoices') . "</h4>";
		return $out;
	}
	/**
	 * Get markup of current order status actions sub-heading for actions block.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderStatusActionsSubHeading() {
		// TODO
		$out = "<h4 class='pwcommerce_override_processwire_heading_margin_top'>" . $this->_('Status') . "</h4>";
		return $out;
	}

	# >>> hookable:blocks main contents <<<

	/**
	 * Get markup of current order line items block main content.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderLineItemsMainContent() {
		$orderPage = $this->orderPage;
		// @note: order line items are children of the order itself
		// @note: we exclude hidden pages since it means they are to be deleted when order is confirmed
		// they represent 'abandoned' line items -> i.e., basket was edited after order confirmed then order re-confirmed.
		// $orderLineItems = $orderPage->children('include=all,check_access=0');
		/** @var PageArray $orderLineItems */
		$orderLineItems = $orderPage->children('include=all,check_access=0,status!=hidden');
		// -----------------------
		$out = $this->getTable($orderLineItems, 'orders_single_view');
		// --
		return $out;
	}

	/**
	 * Get markup of current order notes block main content.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderNotesMainContent() {
		$out = $this->getSingleViewOrderNotes();
		// --
		return $out;
	}

	/**
	 * Get markup of current order status block main content.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderStatusMainContent() {
		$orderPage = $this->orderPage;
		$order = $orderPage->get(PwCommerce::ORDER_FIELD_NAME);
		$statusesArray = $this->getOrderCombinedStatusesArray($order);


		// -------
		$orderStatusTextLabel = $this->_('Order');
		$paymentStatusLabel = $this->_('Payment');
		$fulfilmentStatusLabel = $this->_('Fulfilment');
		// -------------
		$orderStatusText = $statusesArray['order'];
		$paymentStatusText = $statusesArray['payment'];
		$fulfilmentStatusText = $statusesArray['fulfilment'];
		$out =
			// order status
			"<div class='mt-1'>" .
			"<span class='opacity-70'>" . $orderStatusTextLabel . "</span>" .
			"<span>: {$orderStatusText}</span>" .
			"</div>" .
			// fulfilment status
			"<div>" .
			"<span class='opacity-70'>" . $paymentStatusLabel . "</span>" .
			"<span>: {$paymentStatusText}</span>" .
			"</div>" .
			// payment status
			"<div>" .
			"<span class='opacity-70'>" . $fulfilmentStatusLabel . "</span>" .
			"<span>: {$fulfilmentStatusText}</span>" .
			"</div>";
		// --
		return $out;
	}

	/**
	 * Get markup of current order edit block main content.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderEditMainContent() {

		$out =
			// order edit
			"<div class='mt-1'>" .
			$this->getEditOrderMarkup() .
			"</div>";
		// --
		return $out;
	}

	/**
	 * Get markup of current order customer block main content.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderCustomerMainContent() {
		$out =
			// customer shipping/primary address
			$this->getSingleViewOrderCustomerShippingAddressContent() .
			// customer billing address
			$this->getSingleViewOrderCustomerBillingAddressContent() .
			// customer email
			$this->getSingleViewOrderCustomerEmailContent();
		return $out;
	}

	/**
	 * Get markup of current order customer block shipping address content.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderCustomerShippingAddressContent() {
		$orderPage = $this->orderPage;
		$customer = $orderPage->get(PwCommerce::ORDER_CUSTOMER_FIELD_NAME);

		$customerNames = $customer->firstName;
		if (!empty($customer->middleName)) {
			$customerNames .= " {$customer->middleName}";
		}
		$customerNames .= " {$customer->lastName}";

		// if customer is in shop customers records, link to it.
		if ($this->isOrderHasCustomerEmail()) {
			$customerPageID = $this->getOrderCustomerPageID($customer);
			if (!empty($customerPageID)) {
				// we got the page: create the view link
				$customerNames = "<a href='{$this->adminURL}customers/view/?id={$customerPageID}'>{$customerNames}</a>";
			}
		}

		$country = $customer->shippingAddressCountry ? $customer->shippingAddressCountry : $this->_('shipping country not found!');
		$shippingAddressLineTwo = !empty($customer->shippingAddressLineTwo) ? "<span class='block'>{$customer->shippingAddressLineTwo}</span>" : '';
		$shippingAddressRegion = !empty($customer->shippingAddressRegion) ? "<span class='block'>{$customer->shippingAddressRegion}</span>" : '';

		$out =
			// customer shipping/primary address
			"<div class='mt-1'>" .
			"<h4 class='xpwcommerce_override_processwire_heading_margin_top mb-1'>" . $this->_('Shipping Address') . "</h4>" .
			"<span class='block'>{$customerNames}</span>" .
			"<span class='block'>{$customer->shippingAddressLineOne}</span>" .
			$shippingAddressLineTwo .
			"<span class='block'>{$customer->shippingAddressCity}</span>" .
			$shippingAddressRegion .
			"<span class='block'>{$country}</span>" .
			"</div>";
		return $out;
	}

	/**
	 * Get markup of current order customer block primary address content.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderCustomerBillingAddressContent() {
		$orderPage = $this->orderPage;
		$customer = $orderPage->get(PwCommerce::ORDER_CUSTOMER_FIELD_NAME);

		$customerNames = $customer->billingAddressFirstName;
		if (!empty($customer->middleName)) {
			$customerNames .= " {$customer->billingAddressMiddleName}";
		}
		$customerNames .= " {$customer->billingAddressLastName}";

		// $country = $customer->billingAddressCountry ? $customer->billingAddressCountry : $this->_('billing country not found!');
		$country = $customer->billingAddressCountry;

		// +++++++++++++

		$out =
			// customer billing address
			"<div class='mt-3'>" .
			"<h4 class='mb-1'>" . $this->_('Billing Address') . "</h4>";

		// --------
		// if either billing customer names or country are missing
		// it means we don't have full details for billing!
		if (empty($customerNames) || empty($country)) {
			$out .= $this->_('No separate billing details.');
		} else {
			// WE HAVE BILLING ADDRESS DETAILS

			if ($this->isBillingAddressSameAsShippingAddress()) {
				// SHIPPING AND BILLING ADDRESSES ARE IDENTICAL
				$out .= $this->_('Same as shipping address.');
			} else {
				// SHIPPING AND BILLING ADDRESSES ARE DIFFERENT
				$billingAddressLineTwo = !empty($customer->billingAddressLineTwo) ? "<span class='block'>{$customer->billingAddressLineTwo}</span>" : '';
				$billingAddressRegion = !empty($customer->billingAddressRegion) ? "<span class='block'>{$customer->billingAddressRegion}</span>" : '';
				// -------
				$out .=
					"<span class='block'>{$customerNames}</span>" .
					"<span class='block'>{$customer->billingAddressLineOne}</span>" .
					$billingAddressLineTwo .
					"<span class='block'>{$customer->billingAddressCity}</span>" .
					$billingAddressRegion .
					"<span class='block'>{$country}</span>";
			}
		}
		// ----------
		$out .= "</div>";
		return $out;
	}

	/**
	 * Check if billing address is identical to shipping address.
	 *
	 * @return bool
	 */
	private function isBillingAddressSameAsShippingAddress() {
		$orderPage = $this->orderPage;
		$customer = $orderPage->get(PwCommerce::ORDER_CUSTOMER_FIELD_NAME);

		// ----------
		$isBillingAddressSameAsShippingAddress = true;
		$shippingVersusBillingAddressProperties = [
			'shippingAddressFirstName' => 'billingAddressFirstName',
			'shippingAddressMiddleName' => 'billingAddressMiddleName',
			'shippingAddressLastName' => 'billingAddressLastName',
			'shippingAddressPhone' => 'billingAddressPhone',
			'shippingAddressCompany' => 'billingAddressCompany',
			'shippingAddressLineOne' => 'billingAddressLineOne',
			'shippingAddressLineTwo' => 'billingAddressLineTwo',
			'shippingAddressCity' => 'billingAddressCity',
			'shippingAddressRegion' => 'billingAddressRegion',
			'shippingAddressCountry' => 'billingAddressCountry',
			'shippingAddressCountryID' => 'billingAddressCountryID',
			'shippingAddressPostalCode' => 'billingAddressPostalCode'
		];

		foreach ($shippingVersusBillingAddressProperties as $shippingProperty => $billingProperty) {
			if (ucwords($customer->get($shippingProperty)) !== ucwords($customer->get($billingProperty))) {
				$isBillingAddressSameAsShippingAddress = false;
				break;
			}
		}

		return $isBillingAddressSameAsShippingAddress;
	}

	/**
	 * Get markup of current order customer block email address content.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderCustomerEmailContent() {
		$orderPage = $this->orderPage;
		$customer = $orderPage->get(PwCommerce::ORDER_CUSTOMER_FIELD_NAME);
		$email = $customer->email ? $customer->email : $this->_('email not found!');
		$out =
			// customer primary address
			"<div class='mt-3'>" .
			// "<span class='opacity-70'>" . $this->_('Email') . "</span>" .
			"<span>{$email}</span>" .
			"</div>";
		return $out;
	}

	/**
	 * Get markup of current order actions block main content.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderActionsMainContent() {
		$out = $this->getOrderActions();
		return $out;
	}

	# >>> hookable:other <<<

	/**
	 * Get markup of current order created date.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderCreatedDate() {
		$orderPage = $this->orderPage;
		$createdDate = $this->getCreatedDate($orderPage);
		$out = "<h4 class='mt-3'>" . $createdDate . "</h4>";
		return $out;
	}

	/**
	 * Render divider markup for use by the blocks of the  right column of the single order view grid.
	 *
	 * @return mixed
	 */
	protected function ___renderRightGridColumnBlocksDivider() {
		$out = "<hr class='md:hidden mt-3 mb-1'>";
		// -------
		return $out;
	}

	/**
	 *    get Markup For Order Invoice Actions.
	 *
	 * @return mixed
	 */
	protected function ___getMarkupForOrderInvoiceActions() {

		$orderPage = $this->orderPage;
		// $order = $orderPage->get(PwCommerce::ORDER_FIELD_NAME);
		$actions = [
			// 'payment_mark_as_pending' => $this->_('Mark as payment pending'),
			// 'payment_mark_as_paid' => $this->_('Mark as paid'),
			// 'shipment_delivered' => $this->_('Mark as delivered'),
			'invoice_print' => $this->_('Print invoice'),
			'invoice_email' => $this->_('Email invoice'),
		];
		$out = "<ul>";

		// ==========
		// TODO - AMEND, DELETE AND SPLIT BELOW AS APPLICABLE; 'MARK AS' ACTIONS ARE NOW SEPARATE AND IN SELECTS; ONLY INVOICES REMAIN HERE!
		// TODO UNSET INVOICE IF ORDER IS INCOMPLETE?
		// check which of payment_mark_as_paid' and 'payment_mark_as_pending' to unset, if applicable
		// $markAsPaymentsToShow = $this->getOrderMarkAsPaymentsToShow($order);
		// if (!in_array('payment_mark_as_pending', $markAsPaymentsToShow)) {
		// 	unset($actions['payment_mark_as_pending']);
		// }
		// if (!in_array('payment_mark_as_paid', $markAsPaymentsToShow)) {
		// 	unset($actions['payment_mark_as_paid']);
		// }
		// unset email invoice if no order customer email
		if (empty($this->isOrderHasCustomerEmail())) {
			unset($actions['invoice_email']);
		}

		// --------------
		// build actions

		// build actions list
		foreach ($actions as $action => $actionText) {
			// --------
			// for 'mark as' we add alpine js click handlers
			// $clickHandler = in_array($action, ['payment_mark_as_paid', 'payment_mark_as_pending', 'shipment_delivered']) ? " @click='handleMarkOrderAs(\$event,`{$action}`)'" : "";
			// ----
			// build action link as needed
			$link = "";
			if ($action === 'invoice_print') {
				$link = "href='{$this->adminURL}orders/print-invoice/?id={$orderPage->id}' target='_blank'";
			} elseif ($action === 'invoice_email') {
				$link = "href='{$this->adminURL}orders/email-invoice/?id={$orderPage->id}'";
			}
			// ---------
			$out .= "<li>" .
				// "<a {$link}{$clickHandler}>{$actionText}</a>" .
				"<a {$link}>{$actionText}</a>" .
				"</li>";
		}
		$out .= "</ul>";

		// -------
		return $out;
	}

	/**
	 * Get markup of applicable actions for current order.
	 *
	 * @return mixed
	 */
	protected function ___getOrderActions() {

		//--------------
		$out = "<div id='pwcommerce_order_single_view_actions_wrapper' x-data='ProcessPWCommerceData'>";
		// TODO: NEED TO ADD HTMX ATTRIBUTES HERE PLUS HOW TO HANDLE THEM AND RESPONSE!

		// ---------

		// TODO REFACTOR TO NOT EXCLUDE MARK AS DELIVERED!
		// IF ORDER IS STILL EDITABLE
		// if (!$isNotEditable) {
		// --------
		// APPEND MARK AS MODALS for 'pending', 'paid' and 'shipment'
		// TODO: ADD MORE IN FUTURE!
		// $skipMarkOrderAsIfNotEditable = ['payment_mark_as_pending', 'payment_mark_as_paid'];
		// $markOrderAsOptions = $this->getOptionsForModalMarkOrderAs();
		// foreach ($markOrderAsOptions as $action => $markOrderAsOption) {

		// 	// if ($isNotEditable && in_array($action, $skipMarkOrderAsIfNotEditable)) {
		// 	//
		// 	// 	continue;
		// 	// }
		// 	// -------
		// 	$out .= $this->getModalMarkupForConfirmMarkOrderAs($markOrderAsOption);
		// }

		// ===================
		// ADD HIDDEN MARKUPS for 'order status actions'
		// 1. for ORDER ID (for the current order being viewed)
		// 2. for the selected ORDER/PAYMENT/SHIPMENT STATUS ACTION/FLAG
		// for orderID
		// $out .= $this->getOrderActionHiddenMarkupForOrderIDForMarkOrderAs();
		$out .= $this->getOrderActionHiddenMarkupForOrderID();
		$out .= $this->getOrderSelectedStatusFlagForOrder();
		// for 'mark order as' action value (pending, paid, shipment delivered, etc.)
		// $this->getOrderActionHiddenMarkupForOrderActionValueForMarkOrderAs();
		// ------------
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPwCommerce::renderViewItem know that we are ready to save
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];
		//------------------- is_ready_to_save (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$out .= $field->render();
		// }

		// BUILD ACTION TYPES (getInputfieldTextTags ) for each order action type
		$xstore = $this->xstore;
		$actionTypes = [
			'order_statuses',
			'payment_statuses',
			'shipping_statuses',
			// TODO FOR INVOICES, MAYBE NOW DO SEPARATELY IN OWN SELECT NOT SELECTIZE AND NOT HERE!
			// 'invoices'
		];

		// INVOICE ACTIONS MARKUP
		$out .= $this->getSingleViewOrderInvoiceActionsSubHeading();
		// TODO @update: going back to links <a> since only two choices for now
		$out .= $this->getMarkupForOrderInvoiceActions();
		$out .= "<hr>";
		// ORDER STATUS ACTIONS MARKUP
		// $label = $this->_('Confirm Action');
		$label = $this->_('Confirm');
		$options = [
			'id' => "pwcommerce_edit_order_link_button",
			'name' => "pwcommerce_edit_order_link_button",
			'label' => $label,
			'small' => true,
		];

		$out .= $this->getSingleViewOrderStatusActionsSubHeading();
		$out .= $this->getMarkupForOrderStatusActionTypes();
		$out .= "<div>";
		foreach ($actionTypes as $actionType) {
			$out .=
				"<template x-if='{$xstore}.selected_order_status_action_type==`{$actionType}`'>" .

				$this->getMarkupForOrderStatusActionSelect($actionType) .
				"</template>";
		}
		$out .=
			"<template x-if='{$xstore}.selected_order_status_action_type'>" .
			// TODO @update - maybe cleaner with a link instead?
			"<a href='#' @click.prevent='handleEditAndConfirmOrderAction' class='mt-1 block' x-show='{$xstore}.selected_status_action'>" . $this->_('Add details and apply action') . "</a>" .
			"</template>";
		$out .= "</div>";
		$out .= $this->getModalMarkupForOrderStatusActions();

		//-----
		// close the order actions wrapper
		$out .= "</div>";

		return $out;
	}

	/**
	 * Get markup for editing current order.
	 *
	 * @return mixed
	 */
	protected function ___getEditOrderMarkup() {
		$orderPage = $this->orderPage;
		if ($orderPage->isLocked()) {
			// TODO: ITALICS? COLOR?
			// $out = "<small>" . $this->_('Order locked for edits') . "</small>";
			$out = "<span>" . $this->_('Order locked for edits.') . "</span>";
		} else if ($this->isOrderNoLongerEditable($orderPage->get(PwCommerce::ORDER_FIELD_NAME))) {
			// $out = "<small>" . $this->_('Order is no longer editable.') . "</small>";
			$out = "<span>" . $this->_('Order is no longer editable.') . "</span>";
		} else {
			//------------------- edit LINK button (getInputfieldButton)
			$label = $this->_('Edit Order');
			$options = [
				'id' => "pwcommerce_edit_order_link_button",
				'name' => "pwcommerce_edit_order_link_button",
				'label' => $label,
				'small' => true,
			];
			$field = $this->pwcommerce->getInputfieldButton($options);
			// on click navigate to edit this order
			$field->href = "{$this->adminURL}orders/edit/?id={$orderPage->id}";
			//-------
			$out = $field->render();
		}

		return $out;
	}

	/**
	 * Get a single row for an order line item for the order line items table.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	protected function ___getSingleViewTableRow($page) {
		$orderLineItem = $page->get(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME);
		$totalPriceAndDiscountsInfo = $this->___getSingleViewTableRowTotalAndDiscounts($orderLineItem);
		return [
			// PRODUCT
			$orderLineItem->productTitle,
			// QUANTITY
			$orderLineItem->quantity,
			// UNIT PRICE
			$this->pwcommerce->getValueFormattedAsCurrencyForShop($orderLineItem->unitPrice),
			// TOTAL DISCOUNTED PRICE WITH TAX + DISCOUNTS INFO
			$totalPriceAndDiscountsInfo,
		];
	}

	/**
	 * Get a single row for an order line item for the order line items table.
	 *
	 * @param WireData $orderLineItem
	 * @return mixed
	 */
	protected function ___getSingleViewTableRowTotalAndDiscounts(WireData $orderLineItem) {
		$out = "";
		$totalPriceDiscountedWithTax = $this->pwcommerce->getValueFormattedAsCurrencyForShop($orderLineItem->totalPriceDiscountedWithTax);
		$out .= "<span class='block'>" . $totalPriceDiscountedWithTax . "</span>";
		if (!empty($discounts) && !empty($orderLineItem->discounts->count())) {
			$out .= "<div class='mt-1'>";
			// build markup of applied discounts
			// TODO REVISIT THIS GUI PRESENTATION/MARKUP!
			foreach ($orderLineItem->discounts as $discount) {
				// SHOW DISCOUINT AMOUNT AS NEGATIVE
				$discountAmount = $discount->amount * -1;
				$discountAmountAsCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($discountAmount);
				$out .= "<small class='block italic'>{$discountAmountAsCurrency} ({$discount->code})</small>";
			}
			$out .= "</div>";
		}
		// -------
		return $out;
	}

	/**
	 * Get markup for current order discounts total.
	 *
	 * @param mixed $discountsTotal
	 * @return mixed
	 */
	protected function ___getSingleViewOrderDiscountsTotal($discountsTotal) {
		$out =
			"<div>" .
			"<p>" . $this->_('Order Discounts Total') . ": {$discountsTotal}</p>" .
			"</div>";
		// -----
		return $out;
	}

	/**
	 * Get markup for current order shipping and handling total.
	 *
	 * @param mixed $shippingAndHandlingTotal
	 * @param mixed $freeShippingDiscount
	 * @return mixed
	 */
	protected function ___getSingleViewOrderShippingAndHandlingTotal($shippingAndHandlingTotal, $freeShippingDiscount = NULL) {
		$freeShippingDiscountInfo = "";
		if (!empty($freeShippingDiscount)) {
			$freeShippingDiscountInfo = $this->getSingleViewOrderFreeShippingDiscountInfo($freeShippingDiscount);
		}
		$out =
			"<div>" .
			"<p>" .
			"<span class='block'>" .
			$this->_('Shipping and Handling Fee') . ": {$shippingAndHandlingTotal}</span>" .
			// end: order shipping + handling total
			$freeShippingDiscountInfo .
			"</p>" .
			"</div>";
		// -----
		return $out;
	}

	/**
	 * Get markup for current order free shipping discount info.
	 *
	 * @param WireData $freeShippingDiscount
	 * @return mixed
	 */
	protected function ___getSingleViewOrderFreeShippingDiscountInfo(WireData $freeShippingDiscount) {
		$out =
			"<small class='italic'>" .
			$this->_('Free Shipping Discount Code') . ": {$freeShippingDiscount->code}</small>";
		// -----
		return $out;
	}

	/**
	 * Get markup for current order shipping delivery name and estimated days.
	 *
	 * @param mixed $shippingRateName
	 * @param mixed $shippingMininumDays
	 * @param mixed $shippingMaximumDays
	 * @return mixed
	 */
	protected function ___getSingleViewOrderShippingRateDetails($shippingRateName, $shippingMininumDays, $shippingMaximumDays) {

		if (!empty($shippingRateName)) {
			$shippingRateNameText = $shippingRateName . ".";
		} else {
			// no shipping rate name specified
			$shippingRateNameText = $this->_('No shipping rate specified.');
		}
		// ---------
		if (!empty($shippingMaximumDays)) {
			// we have a mininimum delivery estimate
			$shippingRateDeliveryText =
				sprintf(__('%1$d - %2$d days.'), $shippingMininumDays, $shippingMaximumDays);
		} else {
			// no delivery estimate
			$shippingRateDeliveryText = $this->_('No delivery estimate specified.');
		}
		// ##########
		$out =
			"<div>" .
			// TODO MOVE TO OWN METHOD LIKE OTHERS
			// header (including for shipping fee and handling amount)
			// "<h4 class='mb-0'>" . $this->_("Shipping") . "</h4>" .
			"<p>" .
			// rate name
			"<span class='block'>" .
			sprintf(__("Rate Name: %s"), $shippingRateNameText) .
			"</span>" .
			// delivery estimate
			"<span class='block'>" .
			sprintf(__("Delivery Estimate: %s"), $shippingRateDeliveryText) .
			"</span>" .
			//
			"</p>" .
			"</div>";
		// -----
		return $out;
	}

	/**
	 * Get markup for current order grand total.
	 *
	 * @param mixed $grandTotal
	 * @return mixed
	 */
	protected function ___getSingleViewOrderGrandTotal($grandTotal) {
		$out = "<div>" .
			"<p class='text-lg font-bold'>" . $this->_('Grand Total') . ": {$grandTotal}</p>" .
			// end: order grand total
			"</div>";
		return $out;
	}

	/**
	 * Get markup of current order notes.
	 *
	 * @return mixed
	 */
	protected function ___getSingleViewOrderNotes() {
		$orderPage = $this->orderPage;
		$inputfield = $this->modules->get('InputfieldPWCommerceNotes');
		$notes = $orderPage->get(PwCommerce::ORDER_NOTES_FIELD_NAME);
		// sort the orders by created date, ASCENDING
		$notes->sort("-created");
		// TODO: THIS IS THROWING ERROR IN PHP 8 ENVIRONMENTS DUE TO THE OVERLOADING OF renderValue() with $notes!
		// $orderNotesMarkup = $field->renderValue($notes);
		// -------
		$inputfield->setPage($orderPage);
		$inputfield->setNotes($notes);
		$orderNotesMarkup = $inputfield->renderValue();

		return $orderNotesMarkup;
	}

	/**
	 * Render table with order line items for single view or several orders for bulk edit view
	 *
	 * @param mixed $pages
	 * @param string $usage
	 * @return mixed
	 */
	protected function ___getTable($pages, $usage = 'orders_bulk_edit_view') {

		$notFoundMessage = $this->_('No orders found.');
		if ($usage === 'orders_single_view') {
			$notFoundMessage = $this->_('No orders items found.');
		}

		$out = "";
		if (!$pages->count()) {
			$out = "<div  class='mt-5'><p>" . $notFoundMessage . "</p></div>";
		} else {
			$field = $this->modules->get('MarkupAdminDataTable');
			$field->setEncodeEntities(false);
			// set headers (th)
			$tableHeaders = $usage === 'orders_single_view' ? $this->getSingleViewTableHeaders() : $this->getResultsTableHeaders();
			//    $field->headerRow($this->getResultsTableHeaders());
			$field->headerRow($tableHeaders);
			// set each row
			// TODO: THIS IS NOT WORKING FOR ORDER LINE ITEMS! WE SEE ONLY 1 ROW AND THE REST ARE EMPTY!
			// @UPDATE: IT WORKS! IT'S JUST THAT WE HAVE NO SAVED VALUES IN $orderLineItem->pwcommerce_order_line_item FOR THEM!
			foreach ($pages as $page) {
				$row = $usage === 'orders_single_view' ? $this->getSingleViewTableRow($page) : $this->getResultsTableRow($page);
				$field->row($row);
			}

			// ---------
			// show note in footer
			if ($usage === 'orders_single_view') {
				$footerLabels = [
					// @note: forcing empty for columns 1-3
					'',
					'',
					'',
					// using for note for totals
					"<span class='italic'>" . $this->_('Totals include applicable tax') . "</span>"
				];
				$field->footerRow($footerLabels);
			}
			// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
			$out = $field->render();
		}
		return $out;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ QUICK FILTERS  ~~~~~~~~~~~~~~~~~~

	/**
	 *    get Quick Filters Values.
	 *
	 * @return mixed
	 */
	protected function ___getQuickFiltersValues() {
		// TODO DRAFT ORDERS?
		$filters = [
			// reset/all
			'reset' => $this->_('All'),
			// orders
			'open' => $this->_('Open'),
			'completed' => $this->_('Completed'),
			'cancelled' => $this->_('Cancelled'),
			'abandoned' => $this->_('Abandoned'),
			// payment
			'awaiting_payment' => $this->_('Awaiting Payment'),

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
		return [
			// ALL
			'reset' => 0,
			// orders
			'open' => PwCommerce::ORDER_STATUS_OPEN,
			'completed' => PwCommerce::ORDER_STATUS_COMPLETED,
			'cancelled' => PwCommerce::ORDER_STATUS_CANCELLED,
			'abandoned' => PwCommerce::ORDER_STATUS_ABANDONED,
			// payment
			'awaiting_payment' => PwCommerce::PAYMENT_STATUS_AWAITING_PAYMENT,
		];
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
		$allowedQuickFilterValuesArray = $this->getAllowedQuickFilterValues();
		$allowedQuickFilterValues = array_keys($allowedQuickFilterValuesArray);
		$quickFilterValue = $this->wire('sanitizer')->option($input->pwcommerce_quick_filter_value, $allowedQuickFilterValues);

		if (!empty($allowedQuickFilterValuesArray[$quickFilterValue])) {
			$quickFilterValueStatus = $allowedQuickFilterValuesArray[$quickFilterValue];

			// order_status; order_payment_status
			$selector = "," . PwCommerce::ORDER_FIELD_NAME . ".order_status|" . PwCommerce::ORDER_FIELD_NAME . ".order_payment_status={$quickFilterValueStatus}";
		}

		return $selector;
	}
}
