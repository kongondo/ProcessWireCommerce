<?php

namespace ProcessWire;

trait TraitPWCommerceActionsEdit
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ EDIT ~~~~~~~~~~~~~~~~~~

	/**
	 * Bulk Edit Action.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	public function bulkEditAction($input) {

		$result = [
			'notice' => $this->_('Error encountered. No action was taken.'),
			'notice_type' => 'error',
		];

		$sanitizer = $this->wire('sanitizer');
		// @note: since this method is for bulk edit action, we know the name of the input in advance!
		// i.e., 'pwcommerce_bulk_edit_action'
		$action = $sanitizer->fieldName($input->pwcommerce_bulk_edit_action);
		// if no action or action context, return
		if (!$action || !$this->actionContext) {
			return $result;
		}

		// SET ITEMS TO BULK EDIT
		$this->items = $input->pwcommerce_bulk_edit_selected_items;

		//----------
		// DETERMINE THE ACTION
		$actionResult = null;
		// ADDONS PROCESSING
		// @note: we grab this first since actual pages might not be involved
		// and 'activate' and 'lock' actions need to be different
		if ($this->actionContext === 'addons') {
			$this->action = $action;
			$actionResult = $this->actionAddons($input);
		}
		// -----------
		// publish/unpublish (also: activate/deactivate respectively for some contexts)
		else if (in_array($action, ['publish', 'unpublish', 'activate', 'deactivate'])) {
			$this->action = $action;
			$actionResult = $this->actionPublishItems();
		}
		// -----------
		// lock/unlock
		else if (in_array($action, ['lock', 'unlock'])) {
			$this->action = $action;
			$actionResult = $this->actionLockItems();
		}
		// -----------
		// clone @note: Products only!
		else if ($action === 'clone') {
			$this->action = $action;
			$actionResult = $this->actionCloneItems();
		}
		// -----------
		// trash/delete
		else if (in_array($action, ['trash', 'delete'])) {
			$this->action = $action;
			$actionResult = $this->actionTrashItems();
		}

		// -----------
		// special for inventory context
		// allow/disallow overselling; enabled/disabled => STOCK
		else if (in_array($action, ['allow_overselling', 'disallow_overselling', 'enable_selling', 'disable_selling'])) {
			$this->action = $action;
			$actionResult = $this->actionInventory();
		}
		// -----------
		// special for order context -> BULK
		// print/email invoices; mark as pending/paid
		// TODO: WILL NEED TO EDIT THE 'MARK AS' SINCE IN SINGLE VIEW WE NOW EVERYTHING! BUT DON'T DO THOSE THAT REQUIRE INPUT!
		// TODO:@UPDATE: SATURDAY 2 2 APRIL 2023 -> REMOVED THESE FROM BULK EDIT SINCE WE NOW HANDLE ALL STATUSES; THE LIST IS LONG HENCE DOING THIS IN SINGLE ORDER VIEW
		// else if (in_array($action, ['invoice_print', 'invoice_email', 'payment_mark_as_pending', 'payment_mark_as_paid', 'shipment_delivered'])) {
		else if (in_array($action, ['invoice_print', 'invoice_email'])) {
			$this->action = $action;
			$actionResult = $this->actionOrder();
		}
		//-------------
		// set result/response as established by action method
		if (!empty($actionResult)) {
			$result = $actionResult;
		}
		//-------------
		return $result;
	}

	/**
	 * Single Edit Action.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	public function singleEditAction($input) {
		$result = [
			'notice' => $this->_('Error encountered. No action was taken.'),
			'notice_type' => 'error',
		];
		// if no action context, return
		if (!$this->actionContext) {
			return $result;
		}

		// TODO: HERE NEED TO DETERMINE IF SAVING TAX SETTINGS, GENERAL SETTINGS, CHECKOUT SETTINGS,  PAYMENT PROVIDER, ETC!

		//----------
		// DETERMINE THE ACTION
		$actionResult = null;
		$actionContext = $this->actionContext;
		// @note: just for convenience TODO: ok?
		$this->actionInput = $input;
		// -----------
		if ($actionContext === 'tax-settings') {
			// save: tax settings
			$actionResult = $this->actionTaxSettings();
		} else if ($actionContext === 'general-settings') {
			// save: general settings
			$actionResult = $this->actionGeneralSettings();
		} else if ($actionContext === 'checkout-settings') {
			// save: checkout settings
			$actionResult = $this->actionCheckoutSettings();
		} else if ($actionContext === 'payment-providers') {
			// save: payment providers
			$actionResult = $this->actionPaymentProviders();
		}
		// TODO:@UPDATE: SATURDAY 2 2 APRIL 2023 -> REMOVED THESE FROM BULK EDIT SINCE WE NOW HANDLE ALL STATUSES; THE LIST IS LONG HENCE DOING THIS IN SINGLE ORDER VIEW. THIS MEANT THIS WAS CHANGE AND WE NOW USE $this->manuallySetOrderStatusAction()
		else if ($actionContext === 'orders') {
			// SPECIAL save: manually add an order status: these can be for 'order', 'payment' or 'shipment'
			// $actionResult = $this->actionMarkOrderAs($isSingle = true);
			$actionResult = $this->manuallySetOrderStatusAction();
		} else if ($actionContext === 'customers') {
			// SPECIAL operation: send an email to customer
			$actionResult = $this->actionSendEmailCustomer();
		}

		//-------------
		// set result/response as established by action method
		if (!empty($actionResult)) {
			$result = $actionResult;
		}

		//-------------
		return $result;
	}

	/**
	 * Single Inline Edit Action.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	public function singleInlineEditAction($input) {
		$result = [
			'notice' => $this->_('Error encountered. No action was taken.'),
			'notice_type' => 'error',
		];
		// if no action context, return
		if (!$this->actionContext) {
			return $result;
		}

		//----------
		// DETERMINE THE ACTION
		$actionResult = null;
		$actionContext = $this->actionContext;
		// @note: just for convenience TODO: ok?
		$this->actionInput = $input;
		// -----------
		if ($actionContext === 'inventory') {
			// save: inventory single table row edits
			$actionResult = $this->actionInventoryInlineEdit();
		}
		// TODO: ADD MORE CONTEXTS IF REQUIRED!

		//-------------
		// set result/response as established by action method
		if (!empty($actionResult)) {
			$result = $actionResult;
		}
		//-------------
		return $result;
	}



	/**
	 * Action Inventory Inline Edit.
	 *
	 * @return mixed
	 */
	private function actionInventoryInlineEdit() {

		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!

		//------------------
		// GOOD TO GO to next step
		$input = $this->actionInput;
		// TODO: WIP
		$inventoryEditedItemID = (int) $input->pwcommerce_inventory_edited_item_id;
		// get the inventory item page
		// @note: can ONLY be a product without variants or a variant!
		$page = $this->wire('pages')->get($inventoryEditedItemID);

		// error: product page found
		// we didn't get the page; abort
		// TODO: meaningful error? e.g.product not found?
		if (empty($page->id)) {
			return null;
		}

		// first, check if inventory item is locked for edits
		// if it is a variant, we check the parent product, else check the product itself
		if ($this->pwcommerce->isVariant($page)) {
			// is parent product of variant locked?
			$isLocked = $page->parent->isLocked();
		} else {
			// is product without variants locked?
			$isLocked = $page->isLocked();
		}

		// error: product page locked for edits
		if ($isLocked) {
			return null;
		}

		// GOOD TO PROCEED TO FINAL STEP

		// -----------------
		// process the settings
		$sanitizer = $this->wire('sanitizer');

		$sku = $sanitizer->text($input->{"pwcommerce_inventory_item_sku_{$page->id}"});
		$quantity = (int) $input->{"pwcommerce_inventory_item_quantity_{$page->id}"};
		// -------------

		// @note: for checkboxes we don't care about the values; just whether they were sent or not

		// oversell
		if ($input->{"pwcommerce_inventory_item_oversell_{$page->id}"} === null) {
			// if CHECKBOX for 'oversell' WAS NOT SENT, ite means was not checked, hence set bool int false: 0
			$allowBackorders = 0;
		} else {
			// if CHECKBOX for 'oversell' WAS SENT, it means it was checked, hence set bool int true: 1
			$allowBackorders = 1;
		}

		// enabled
		if ($input->{"pwcommerce_inventory_item_enabled_{$page->id}"} === null) {
			// if CHECKBOX for 'enabled' WAS NOT SENT, ite means was not checked, hence set bool int false: 0
			$enabled = 0;
		} else {
			// if CHECKBOX for 'enabled' WAS SENT, it means it was checked, hence set bool int true: 1
			$enabled = 1;
		}

		// -------------------
		// @note - HERE WE NEED TO PRESERVE THE PRICES! OTHERWISE THEY GET OVERWRITTEN!
		// hence, we get current stock object
		// $stock = new WireData();
		/** @var WireData $stock */
		$stock = $page->get(PwCommerce::PRODUCT_STOCK_FIELD_NAME);
		$stock->sku = $sku;
		$stock->quantity = $quantity;
		// @note: bool ints!
		$stock->allowBackorders = $allowBackorders;
		$stock->enabled = $enabled;
		// $page->pwcommerce_product_stock = $stock;
		// @note: not needed since it $stock is referenced at its memory address RE above changes
		// $page->set(PwCommerce::PRODUCT_STOCK_FIELD_NAME, $stock);

		//-------------
		// save the page's 'pwcommerce_product_stock' field
		// $page->save('pwcommerce_product_stock');
		$page->save(PwCommerce::PRODUCT_STOCK_FIELD_NAME);

		// --------------------
		// TODO: NO NOTICES FOR NOW SINCE IN AJAX MODE! but we send just in case changes in future
		// prepare messages
		$notice = sprintf(__("Saved inventory settings for %s."), $page->title);

		$result = [
			'notice' => $notice,
			'notice_type' => 'success',
			// TODO? check if really saved first?
			// @note: needed in order to fetch edited item for htmx response
			'inline_edited_item_id' => $page->id,
		];

		//-------
		return $result;
	}

	// ~~~~~~~~~~~~

	/**
	 * Get Items To Action.
	 *
	 * @param string $selector
	 * @return mixed
	 */
	private function getItemsToAction($selector = '') {

		if (!empty($selector)) {
			$selector = ",{$selector}";
		}
		// --------
		$items = $this->items;
		$itemIDs = implode('|', $items);
		// TODO: INCLUDE ALL OK?
		$pages = $this->wire('pages')->find("id={$itemIDs}, include=all{$selector}");
		return $pages;
	}

}
