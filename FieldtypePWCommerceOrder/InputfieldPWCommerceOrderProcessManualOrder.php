<?php

namespace ProcessWire;

/**
 * PWCommerce: InputfieldPWCommerceOrder -> InputfieldPWCommerceOrderProcessManualOrder
 *
 * Helper process inputs class for InputfieldPWCommerceOrder.
 * For processing manual order values for live calculations and saving.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceOrderProcessManualOrder for PWCommerce
 * Copyright (C) 2022 by Francis Otieno
 * MIT License
 *
 */



class InputfieldPWCommerceOrderProcessManualOrder extends WireData
{



	// =============
	protected $page;
	protected $field;
	private $input; // just for $input convenience

	private $order; // WireData
	// ----------
	private $shippingCountry;
	private $isCustomerTaxExempt;
	private $isChargeTaxesManualExemption;
	private $isProcessTruePrice;
	private $isLiveCalculateOnly;
	private $isCustomerValuesForProcessCalculableValuesChanged;
	private $isChargeTaxesManualExemptionChanged;
	// ----------
	private $existingOrderLineItemsIDs;
	private $inEditOrderLineItemsIDs;
	private $inEditOrderLineItemsProductsIDs;
	private $newOrderLineItemsIDs;
	private $deletedOrderLineItemsIDs;
	private $keptOrderLineItemsIDs;



	// =============

	public function __construct($page) {

		$this->page = $page;
		// ----------
		// GET UTILITIES CLASS


	}

	/**
	 * Process order customer for saving via InputfieldPWCommerceOrderCustomer.
	 *
	 * @access public
	 * @param WireInputData $input Input to get customer details from.
	 * @return void
	 */
	public function processOrderCustomerForSaving(WireInputData $input) {
		$inputfieldName = "InputfieldPWCommerceOrderCustomer";
		$inputfield = $this->wire('modules')->get($inputfieldName);
		$inputfield->setPage($this->page);

		$inputfield->setField($this->wire('fields')->get('name=' . PwCommerce::ORDER_CUSTOMER_FIELD_NAME));
		$inputfield->attr('value', $this->page->get(PwCommerce::ORDER_CUSTOMER_FIELD_NAME));
		$orderCustomer = $inputfield->processOrderCustomerForSaving($input);

		// --------
		// check for missing required values
		if (!empty($orderCustomer->errors)) {

			$this->error(sprintf(__("There were errors.  Please fill these missing values: %s."), implode(', ', $orderCustomer->errors)));
		} elseif (!empty($orderCustomer->isNeedSaving)) {
			// not really needed but all the same
			$this->page->of(false);
			$this->page->set(PwCommerce::ORDER_CUSTOMER_FIELD_NAME, $orderCustomer);
			// $this->page->save(PwCommerce::ORDER_CUSTOMER_FIELD_NAME);

		}
	}

	/**
	 * Process order for saving.
	 *
	 * Process line items, shipping values, etc.
	 * Process new,deleted and existing/kept items.
	 *
	 * @access public
	 * @param WireInputData $input Input to get order line items details from.
	 * @param bool $isCustomerValuesForProcessCalculableValuesChanged Whether customer values that require the processing of order line items calculable values have happened..
	 * @return void
	 */
	public function processOrderForSaving(WireInputData $input, bool $isCustomerValuesForProcessCalculableValuesChanged) {
		// @note: called by InputfieldPWCommerceOrder::___processInput

		$this->input = $input;
		// @note: checked in InputfieldPWCommerceOrder::___processInput for customer shipping country or tax exemption status changed
		$this->isCustomerValuesForProcessCalculableValuesChanged = $isCustomerValuesForProcessCalculableValuesChanged;

		// =======
		// ----------

		// TODO @debug #works#
		$this->order = $this->page->get(PwCommerce::ORDER_FIELD_NAME);
		// ==============
		// @note: not needed but just here forcompleteness
		$isLiveCalculateOnly = false;
		$this->order->isLiveCalculateOnly = $isLiveCalculateOnly;
		$this->isLiveCalculateOnly = $isLiveCalculateOnly;

		// ==============

		$this->preProcessOrderForLiveCalculationsORSaving();

		// @note: also checked on client but leaving this here as well
		if (!empty($this->order->isOrderError)) {

			// order processing error! don't save! show error!
			$this->error($this->order->isOrderErrorMessage);

			// return early
			return;
		}

		// =>>>>>>
		// TODO: HERE NEED TO CALL processInputPostProcessAfterSave() ??? OR SEE IF CAN HAVE COMMON PRE-CHECKS AND CALL $this->processSaveNonLiveOrder() INSTEAD?
		// TODO: NEED TO REFACTOR TO CHECK EARLY FOR SHIPPING RATE! OTHERWISE LINE ITEMS ARE SAVED BUT ORDER ERROR CAUGHT LATER! CREATE A DEDICATED REUSABLE METHOD FOR THIS!
		$this->order->isShippingFeeNeedsCalculating = false; // default
		// $order = $this->setOrderShippingValues();
		$this->setOrderShippingValues();

		// ########################################
		// ERROR CHECKING: shipping errors (ONLY IF NOT IN 'CALCULATE-ONLY' MODE)
		// WE CHECK EARLIER TO ABORT PROCESSING OF LINE ITEMS BELOW!
		if (!empty($this->order->isOrderError)) {

			// order processing error! don't save! show error!
			$this->error($this->order->isOrderErrorMessage);

			// return early
			return;
		}
		// =========== GOOD TO GO ================

		// ######################################## SAVING/COMMITING VALUES ###########
		$this->processSaveNonLiveOrder();
	}

	private function preProcessOrderForLiveCalculationsORSaving() {

		// ------
		// get IDs of existing order line items (current children of this order)
		$this->existingOrderLineItemsIDs = $this->getIDsForExistingOrderLineItemsForOrder();
		// --------
		// get IDs of all order line items IN EDIT that were sent via $input
		// @note: this might include new items. In that case, their ID will be the ID of the product they will be created from
		$this->inEditOrderLineItemsIDs = $this->getInEditOrderLineItemsIDs();
		// TODO: confirm this and above usage!
		$this->inEditOrderLineItemsProductsIDs = $this->getInEditProductsIDs();
		// --------
		// get new items
		$this->newOrderLineItemsIDs = $this->getIDsForAddedOrderLineItemsForOrder();
		// get deleted items
		$this->deletedOrderLineItemsIDs = $this->getIDsForDeletedOrderLineItemsForOrder();
		// get kept items
		$this->keptOrderLineItemsIDs = $this->getIDsForKeptOrderLineItemsForOrder();
		// ---------- @debug

		// +++++++++++++++
		/*
																								$this->setOrderOrderLineItemsSharedProperties()
																								SET CLASS PROPERTIES FOR LATER USE FOR THE FOLLOWING:
																								1. Set order shipping country. If no shipping country, return early with error
																								- $input->pwcommerce_order_customer_shipping_address_country_id[0]
																								- $this->shippingCountry
																								2. Set if customer is tax exempt:
																								- $input->pwcommerce_order_customer_is_tax_exempt
																								- $this->isCustomerTaxExempt
																								3. Set if order has manual tax exemption:
																								- $input->pwcommerce_order_apply_manual_tax_exemption
																								- $this->isChargeTaxesManualExemption
																								*/
		// TODO @debug #works#
		$this->setOrderOrderLineItemsSharedProperties();

	}

	private function getIDsForExistingOrderLineItemsForOrder() {
		$fields = 'id';
		$existingChildrenIDs = $this->wire('pages')->findRaw("template=" . PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME . ", parent={$this->page},check_access=0", $fields);

		return $existingChildrenIDs;
	}

	private function getInEditOrderLineItemsIDs() {
		// TODO: make sure getting from correct input!!!
		// TODO: delete if not in use: pwcommerce_order_live_order_line_items_products_ids
		// pwcommerce_order_live_order_line_items_ids TODO MAKE SURE IT IS NOT ABOVE ONE
		$inEditOrderLineItemsIDs = $this->wire('sanitizer')->intArray($this->input->pwcommerce_order_live_order_line_items_ids);

		return $inEditOrderLineItemsIDs;
	}

	private function getInEditProductsIDs() {
		$inEditOrderLineItemsProductsIDs = $this->wire('sanitizer')->intArray($this->input->pwcommerce_order_live_order_line_items_products_ids);

		return $inEditOrderLineItemsProductsIDs;
	}

	private function getIDsForAddedOrderLineItemsForOrder() {
		// get new items
		$newOrderLineItemsIDs = array_diff($this->inEditOrderLineItemsIDs, $this->existingOrderLineItemsIDs);

		return $newOrderLineItemsIDs;
	}

	private function getIDsForDeletedOrderLineItemsForOrder() {
		// get deleted items
		$deletedOrderLineItemsIDs = array_diff($this->existingOrderLineItemsIDs, $this->inEditOrderLineItemsIDs);

		return $deletedOrderLineItemsIDs;
	}

	private function getIDsForKeptOrderLineItemsForOrder() {
		// get kept/preserved items
		$keptOrderLineItemsIDs = array_intersect($this->existingOrderLineItemsIDs, $this->inEditOrderLineItemsIDs);

		return $keptOrderLineItemsIDs;
	}

	public function processNonSaveLiveOrder(WireInputData $input, Page $orderPage) {

		// TODO @debug #works#
		$this->input = $input;
		// TODO: is this ok?
		// TODO @debug #works#
		$this->page = $orderPage;
		// TODO @debug #works#
		$this->order = $this->page->get(PwCommerce::ORDER_FIELD_NAME);
		// ==============
		$isLiveCalculateOnly = true;
		$this->order->isLiveCalculateOnly = $isLiveCalculateOnly;
		$this->isLiveCalculateOnly = $isLiveCalculateOnly;
		// ==============
		$this->preProcessOrderForLiveCalculationsORSaving();

		// @note: also checked on client but leaving this here as well
		if (!empty($this->order->isOrderError)) {

			// order processing error! don't save! show error!
			$this->error($this->order->isOrderErrorMessage);

			// return early
			return;
		}

		// TODO @debug #works#

		// TODO WE ALSO NEED TO CREATE A HIDDEN INPUT FOR LIVE LINE ITEMS THAT EXIST IN ORDER SINCE THEIR QUANTITY AND DISCOUNT INPUTS WILL BE SUFFIXED WITH THEIR IDS! AND NOT PRODUCT IDS!
		// --------------------
		// get the product IDs as an array for the LIVE order line items
		// $inEditOrderLineItemsProductsIDs = $this->getCurrentLiveOrderLineItemsProductsIDs($input->pwcommerce_order_live_order_line_items_products_ids);
		// $this->order->liveOrderLineItemsProductsIDs = $inEditOrderLineItemsProductsIDs;
		// TODO CONFIRM THIS IS THE RIGHT ONE! AND NOT -> $this->inEditOrderLineItemsIDs
		$this->order->liveOrderLineItemsProductsIDs = $this->inEditOrderLineItemsProductsIDs;
		// -------------------
		// GET LINE ITEMS IDS of both existing and unsaved new order line items
		// TODO DELETE IF NOT IN USE
		// $inEditOrderLineItemsIDs = $this->getCurrentLiveOrderLineItemsIDs($input->pwcommerce_order_live_order_line_items_ids);
		// GET AND SET the quantity, discount type and value for each LIVE ORDER LINE ITEM as well as price and taxable setting for their product
		// TODO @NOTE: THIS IS NOW A WIREARRAY!!! OK??? - NO FOR NOW, CONTINUE WITH ARRAY FOR CONSISTENCY IN PWCommerceUtilities::getLineItemsForOrder
		// TODO DELETE IF NOT IN USE
		// $this->order->liveOrderLineItems = $this->setLiveOrderLineItemsValues($inEditOrderLineItemsProductsIDs, $inEditOrderLineItemsIDs);
		$this->order->liveOrderLineItems = $this->setLiveOrderLineItemsValues();
		$this->processOrderCalculableValues();

		return $this->order;
	}

	private function processSaveNonLiveOrder() {

		// 1. FIRST, CREATE NEW ORDER LINE ITEMS
		// TODO DELETE IF NOT IN USE; USING NEW APPROACH BELOW
		// $newOrderLineItemsProductsOrVariantsIDsArray = $this->getNewOrderLineItemsProductsOrVariantsIDsArray($input->pwcommerce_order_new_line_items);
		if (!empty($this->newOrderLineItemsIDs)) {
			$this->processInputCreateNewItems();
		}

		// ########################################

		// 2. SECOND, PROCESS DELETED EXISTING ORDER LINE ITEMS
		if (!empty($this->deletedOrderLineItemsIDs)) {
			$this->processInputDeletedRemovedExistingItems();
		}

		// ########################################

		// 3. THIRD, PROCESS EXISTING (AND KEPT) ORDER LINE ITEMS
		// TODO @see race condition issue in temp notes for 'Saturday 2 October 2021 6pm.'
		if (!empty($this->keptOrderLineItemsIDs)) {
			$this->processInputEditExistingItems();
		}

		// ########################################

		// 4. FINALLY, PROCESS CALCULABLE (DEPENDENT) VALUES FOR WHOLE ORDER
		// e.g. tax, discounts, shipping and handling fee
		// TODO: MAYBE NO NEED TO RETURN THIS ORDER? WE'LL NEED TO SAVE IT THOUGH ALONG WITH OTHER INPUTS?
		// TODO: MIGHT MEAN WE HAVE NO NEED FOR processInput()??? OR CAN USE IT BUT NOT FOR PROCESSING $input?!!!
		// TODO @UPDATE! WE DON'T NEED PROCESSINPUT AND WE HAVE this->page!!! WE CAN SAVE!!
		$this->processOrderCalculableValues();

		// TODO IS THIS OK?
		// ########################################
		// IF ONLY NEED CALCULATIONS, RETURN PROCESSED ORDER
		// TODO: HOW TO HANDLE ERRORS IN THIS CASE? I.E. IN AJAX MODE!!!
		// @note: MOVED UP: DELETE WHEN DONE!
		// if ($order->isLiveCalculateOnly) return $order;

		// ########################################

		// ########################################
		// ERROR CHECKING
		if (!empty($this->order->isOrderError)) {

			// order processing error! don't save! IF NOT A WARNING!
			// else show error!
			if ($this->order->noticeType === 'warning') {
				$this->warning($this->order->isOrderErrorMessage);
			} else {
				$this->error($this->order->isOrderErrorMessage);
				// return early
				return;
			}
		}

		// ########################################
		// SAVE ORDER WITH CALCULATED VALUES!
		// TODO: NEED TO WORK ON API SET VALUES SUCH AS STATUS! OR DO WE SET INITIAL ORDER STATUS HERE BEFORE IT IS FINALISED?


		$this->page->set(PwCommerce::ORDER_FIELD_NAME, $this->order);
		//$this->wire('pages')->save($this->page);

		$this->page->save(PwCommerce::ORDER_FIELD_NAME);

		// TODO NOW SAVE THIS! SIMILAR TO PROCESSINPUT! OR EVEN A DIRECT SAVE BUT WOULD HAVE BEEN GOOD TO TRACK?
		// TODO @UPDATE! WE DON'T NEED PROCESSINPUT AND WE HAVE this->page!!! WE CAN SAVE!!
	}

	/**
	 * Process creation of new order line items for this order.
	 *
	 * @note: we do this here instead of inside InputfieldPWCommerceOrderLineItem::processInput in order to avoid race condition.
	 * We need line items, new and existing, to be processed first in order to then process the dependable values in whole order.
	 *
	 * @access private
	 * @param array $newOrderLineItemsProductsOrVariantsIDsArray IDs of products or variants to add as new line items.
	 * @return void
	 */
	private function processInputCreateNewItems() {
		$input = $this->input;
		// TODO DELETE WHEN DONE -> WE NOW HAVE THIS AT $this->page!!!
		// @note: need to get this way since not in $this context since $this->processInputPostProcessAfterSave() was called externally by InputfieldPWCommerceRuntimeMarkup::processInputContextPostProcessAfterSave
		// $newOrderLineItemParentID = (int) $input->pwcommerce_order_parent_page_id;
		// $parent = $this->wire('pages')->get("id=${newOrderLineItemParentID}");
		// we found parent page (order) + IDs of products to create new order line items from
		// if (!empty($parent)) {
		$parent = $this->page;

		if (!empty($parent->id)) {
			// @note: findRaw values!
			$productOrVariantPages = $this->getProductOrVariantPagesForCreatingNewOrderLineItems();
			if (empty($productOrVariantPages)) {
				// TODO RETURN ERROR???
			}

			// GOOD TO GO

			// =================
			$sanitizer = $this->wire('sanitizer');
			/** @var Template $template */
			$template = $this->wire('templates')->get(PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME);
			/** @var Template $variantsTemplate */
			$variantsTemplate = $this->wire('templates')->get(PwCommerce::PRODUCT_VARIANT_TEMPLATE_NAME);

			// ===========================================

			// ERROR CATCHING VARIABLES
			$notAddedLineItemsProductsTitles = [];
			$addedLineItemsProductsTitles = [];

			// ================== PROCESS PRODUCTS AS NEW ORDER ITEMS ============
			// new order line item will require processing of the 'TRUE' price that takes into account if prices include taxes or not
			$this->isProcessTruePrice = true;
			foreach ($productOrVariantPages as $productOrVariantPage) {

				$productID = (int) $productOrVariantPage['product_id'];

				// ###################
				// prepare titles and name + will help check if identical exists
				$title = $sanitizer->text($productOrVariantPage['title']);
				$name = $sanitizer->pageName($title, true); // TODO: SHOULD THIS MATCH THE FRONTEND/API CREATED LINE ITEMS NAMES?

				// first check if page already exists (under this parent)
				$pageIDExists = $this->wire('pages')->getRaw("parent_id={$parent->id},name=$name", 'id');
				if (!empty($pageIDExists)) {
					// CHILD PAGE (ORDER LINE ITEM) ALREADY EXISTS!
					// prepare for error: product cannot be added: a duplicate exists
					$notAddedLineItemsProductsTitles[] = $title;
					continue;
				}

				// TODO: IF VARIANT HAS NO PRICE, WE GET FROM PARENT! TODO: ALTERNATIVELY, WE NEED TO BE SETTING THIS WHEN SAVING THE VARIANT! THEN WE HAVE IT STORED ALREADY! IF AFTER THAT IS ZERO? WE LEAVE IT! IF SO, ALSO CHANGE IN RENDER SEARCH PRODUCTS TO SHOW THE REAL PRICE OF THE VARIANT!

				// ==================
				// GOOD TO GO

				// CREATE NEW ORDER LINE ITEM FOR THIS ORDER
				//------------
				$p = new Page();
				$p->template = $template;
				$p->parent = $parent;
				$p->title = $title;
				$p->name = $name;
				//--------------
				// process order line item field values
				//----------
				$fieldName = PwCommerce::ORDER_LINE_ITEM_FIELD_NAME;
				$newOrderLineItem = new WireData();
				// ========= HANDLE VARIANTS =============
				/** @var bool $isVariant */
				$isVariant = (int) $variantsTemplate->id === (int) $productOrVariantPage['templates_id'];

				// =================
				// @note: we only allow these discount types
				$allowedDiscountTypeValues = $this->getOrderLineItemAllowedDiscountTypes();
				$discountType = $sanitizer->option($input->{"pwcommerce_order_line_item_discount_type{$productID}"}, $allowedDiscountTypeValues);
				// default to 'none' if sanitizer returns null
				if (empty($discountType)) {
					$discountType = 'none';
				}
				// -------------
				// SET: IMMUTABLE VALUES from product itself
				$newOrderLineItem->productID = $productID;
				$newOrderLineItem->productTitle = $title;
				// @note: 'pwcommerce_order_line_item' was fetched as 'stock'
				// @note: 'sku' lives at 'data'. Since this is a findRaw() result, we get the real column name in the results.
				$newOrderLineItem->productSKU = $sanitizer->text($productOrVariantPage['stock']['data']);
				$newOrderLineItem->isVariant = (int) $isVariant;
				// @note: @see: $this->isProcessTruePrice  below: this price will be processed for 'TRUENESS' when calculable values are processed IF THIS IS A NEW LINE ITEM  (which it is). the TRUE price of the product/variant considers if the price is inclusive or exclusive of tax
				$newOrderLineItem->unitPrice = (float) $productOrVariantPage['stock']['price'];

				// ==================
				// SET: EDITABLE VALUES -> grab via $input
				// @note: inputs names for new items are suffixed with the product or variant ID; hence this works
				// @note: for existing items (as processed in InputfieldPWCommerceOrderLineItem::processInput()), they are suffixed with the existing order line item page ID.
				// TODO: SHOULD WE HANDLE OUT OF STOCK? E.G. MORE QUANTITIES ADDED THAN STOCK ALLOWS?! => FOR FUTURE RELEASE!

				$newOrderLineItem = $this->processOrderLineItem($newOrderLineItem, $productID);

				// ==================
				// TODO - I THINK TAXES ARE NOT GETTING APPLIED CORRECTLY IN CASES WHERE TAXES ARE ALREADY INCLUDED IN PRICES BUT THE CUSTOMER IS IN A DIFFERENT (EVEN SAME?) COUNTRY. WE ARE GETTING THE LIST PRICE PORTION CORRECTLY BUT THEN TAX IS NOT SUBSEQUENTLY APPLIED! I THINK IT IS SOME LOGIC IN UTILITIES! MAYBE IT IS CHECKING IF TAX HAS ALREADY BEEN APPLIED! HOWEVER, IT NEEDS TO WORK WITH THE TRUE PRICE AND ALWAYS APPLY TAX UNLESS THE PRODUCT, OR THE CUSTOMER OR THE ORDER IS TAX EXEMPTA
				// SET: CALCULABLE VALUES
				// TODO - HERE MAYBE RETURN ERROR IF MISSING REQUIRED VALUES, E.G. MISSING SHIPPING COUNTRY! IN WIREDATA OR OTHER? MAYBE TEMP ERROR PROPERTY (ARRAY? ) WITH EXACT ERROR!

				// =================
				// @note: we set placeholder delivered date ourselves UNTIL order line item status will be upated via the API
				// TODO: REVISIT THIS!
				$newOrderLineItem->deliveredDate = PwCommerce::ORDER_LINE_ITEM_PLACEHOLDER_DELIVERED_DATE;
				// set value to order line item field
				$p->set($fieldName, $newOrderLineItem);
				//------------------
				// SAVE the new order line item page

				$p->save();

				// ------------
				// prepare for success message
				if (!empty($p->id)) {
					// prepare for success message
					$addedLineItemsProductsTitles[] = $p->title;
				} else {
					// prepare for error: for some reason, page was not created
					$notAddedLineItemsProductsTitles[] = $p->title;
				}
			}

			// end loop
			// ------
			// NOTICES
			// success
			if (!empty($addedLineItemsProductsTitles)) {
				$this->message(sprintf(__("Added these products to order: %s."), implode(', ', $addedLineItemsProductsTitles)));
			}
			// error
			if (!empty($notAddedLineItemsProductsTitles)) {
				$this->warning(sprintf(__("Could not add these products to order: %s."), implode(', ', $notAddedLineItemsProductsTitles)));
			}
		}
	}

	// process a single order line item for either saving or live calculate values
	private function processOrderLineItem($orderLineItem, $id) {

		$productOrVariantPage = $this->getProductOrVariantPagesForOrderLineItem($orderLineItem->productID, $orderLineItem->isVariant);

		$shippingType = $productOrVariantPage['settings']['data'];

		// if in LIVE CALCULATE VALUES MODE, we need price to be set to order line item
		if (!empty($this->isLiveCalculateOnly)) {
			$orderLineItem->unitPrice = (float) $productOrVariantPage['stock']['price'];
			// we also set title for debug convenience
			$orderLineItem->title = $productOrVariantPage['title'];
		}

		// -------
		### SET LINE ITEM PROPERTIES ###
		// ==========================
		// SET QUANTITY
		$orderLineItem->quantity = (int) $this->input->{"pwcommerce_order_line_item_quantity{$id}"};
		// -------
		# DISCOUNTS #
		// SET DISCOUNT TYPE AND VALUE
		$orderLineItem = $this->setOrderLineItemDiscountTypeAndValue($orderLineItem, $id);

		# SET PRODUCT SHIPPING TYPE #
		// e.g.for use in PWCommerceUtilities to calculate shipping rates based on price, etc
		$orderLineItem->shippingType = $shippingType;

		// ==================
		// SET: CALCULABLE VALUES (recalculate due to changes)
		$orderLineItem = $this->processOrderLineItemCalculableValues($orderLineItem, $productOrVariantPage);

		// ##########
		return $orderLineItem;
	}

	private function processInputDeletedRemovedExistingItems() {

		// TODO TEST THIS!
		$deletedOrderLineItemsIDsSelector = implode("|", $this->deletedOrderLineItemsIDs);

		/** @var PageArray $deletedOrderLineItemsPages */
		$deletedOrderLineItemsPages = $this->wire('pages')->find("id={$deletedOrderLineItemsIDsSelector}");

		foreach ($deletedOrderLineItemsPages as $page) {
			// skip if page is locked TODO IS THIS POSSIBLE IN THIS CASE?
			if ($page->isLocked()) {
				continue;
			}
			// delete
			$page->delete();
		}
	}

	private function processInputEditExistingItems() {

		// TODO DELETE IF NOT IN USE - USING NEW APPROACH BELOW
		// $existingOrderLineItemsIDsToProcess = $this->getExistingOrderLineItemsIDsToProcess();
		// -----------------------
		// GOOD TO GO
		if (!empty($this->keptOrderLineItemsIDs)) {

			// existing order line items WILL NOT require processing of the 'TRUE' price that takes into account if prices include taxes or not. that was taken care of when they were created as a new order items
			$this->isProcessTruePrice = false;

			// check if charge manual taxes on order status has changed
			$this->isChargeTaxesManualExemptionChanged = $this->isChargeTaxesManualExemptionChanged();

			// -----------
			// GET INSTANCE OF INPUTFIELD PWCOMMERCE ORDER LINE ITEM
			// we will use its methods to check if inputfield has changed and to process calculable values
			// get the line items pages for processing
			$orderLineItemsPages = $this->getExistingOrderLineItemsPagesToProcess();
			// loop through line item pages and procss order line item field
			foreach ($orderLineItemsPages as $orderLineItemPage) {
				$pageID = $orderLineItemPage->id;
				/** @var WireData $orderLineItem */
				$orderLineItem = $orderLineItemPage->get(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME);

				// #########################

				// if the string value of the processed order line item is different from the previous,
				// then flag this Inputfield as changed
				// so that it will be automatically saved with the page
				// @note: we compare using an in-house toString() private method as we don't implement toString() in the field.
				// TODO IF either customer or order tax exemption has happened AS WELL; WE NEED TO RECALCULATE TAX!
				// TODO HOW TO CHECK IF CUSTOMER TAX EXEMPTION CHANGED? this is because the saving process there is different from this one! session? hidden markup on client? pass variable here?
				if ($this->isCustomerValuesForProcessCalculableValuesChanged || $this->isChargeTaxesManualExemptionChanged || $this->isChangedOrderLineItem($orderLineItem, $pageID)) {

					// AT LEAST ONE OF EDITABLE OR DEPENDENT VALUES HAS CHANGED
					// ==================
					// SET: CALCULABLE VALUES (recalculate due to changes)
					// ##############################
					// PROCESS EDITABLE VALUES + CALCULABLE ONES
					$orderLineItem = $this->processOrderLineItem($orderLineItem, $pageID);
					// -----------------
					// ##################### SAVE ORDER LINE ITEM FIELD FOR PAGE ###

					$orderLineItemPage->save(PwCommerce::ORDER_LINE_ITEM_FIELD_NAME);
				} else {
					// TODO - DELETE WHEN DONE

				}
			}
		}
	}

	// TODO MOVING THIS HERE (AND MAYBE TO UTILITIES?) FROM InputfieldPWCommerceOrderLineItem IN ORDER TO REUSE SOME VALUES, E.G. FETCH SHIPPING COUNTRY ONLY ONCE! FOR NOW, WE DO IT FOR EACH ITERATATION IN THE LOOP IN processInputEditExistingItems()!

	/**
	 * Process order line item calculable values.
	 *
	 * These include discount amount and taxes.
	 *
	 * @access private
	 * @param WireData $orderLineItem Order line item to process.
	 * @param array $productOrVariantPage Array with properties from the product/product variant page, i.e. price, etc.
	 * @return WireData $orderLineItem The processed order line item.
	 */
	private function processOrderLineItemCalculableValues(WireData $orderLineItem, $productOrVariantPage): WireData {
		// ==========  RECALCULATE CALCULABE VALUES ====

		// ==================
		// SET: CALCULABLE VALUES
		$orderLineItemCalculatedValuesOptions = [
			/** @var WireData $orderLineItem */
			'order_line_item' => $orderLineItem,
			'product_or_variant_page' => $productOrVariantPage,
			/** @var Page $shippingCountry */
			'shipping_country' => $this->shippingCountry,
			'is_charge_taxes_manual_exemption' => $this->isChargeTaxesManualExemption,
			'is_customer_tax_exempt' => $this->isCustomerTaxExempt,
			'is_process_order_line_item_product_true_price' => $this->isProcessTruePrice
		];
		$orderLineItem = $this->pwcommerce->getOrderLineItemCalculatedValues($orderLineItemCalculatedValuesOptions);

		// ----------------------
		// @note: we set placeholder delivered date ourselves UNTIL order line item status will be upated via the API

		return $orderLineItem;
	}

	/**
	 * Process order calculable values.
	 *
	 * These include discount amount, handling, shipping and final/total price.
	 *
	 * @access private
	 * @return void
	 */
	private function processOrderCalculableValues() {

		# ERROR HANDLING # (runtime)
		$this->order->isOrderError = false;
		$this->order->isOrderErrorMessage = null;

		// ==================

		// @note: we set placeholder paid date ourselves UNTIL order status will be upated via the API
		// TODO: REVISIT THIS!
		$this->order->paidDate = PwCommerce::ORDER_PLACEHOLDER_PAID_DATE;
		// ========================

		// SET INITIAL VALUES TO $this->order BEFORE LATER DETERMINING FINAL VALUES IN
		// -----------------
		# DISCOUNTS #
		$this->setOrderDiscountTypeAndValue();

		// ========
		# SHIPPING #
		// SET SHIPPING NEEDS CALCULATING PROPERTY FOR LATER CHECKING
		// TODO: delete when done. we now do this earlier in this->processInputPostProcessAfterSave() to avoid processing and saving of line items!
		// $order->isShippingFeeNeedsCalculating = false; // default
		// $order = $this->setOrderShippingValues($order, $input);
		// // ERROR CHECKING: shipping errors (ONLY IF NOT IN 'CALCULATE-ONLY' MODE)
		// if (!empty($order->isOrderError) && empty($order->isLiveCalculateOnly)) {


		//   // order processing error! don't save! return the order with the set error flag and error message!
		//   // return early
		//   return $order;
		// } else {


		// }
		// ========
		# HANDLING #
		$this->setOrderHandlingFeeValues();

		// ========
		# TAXES #
		$this->setOrderTaxesValues();

		// =================
		// ********************

		# LINE ITEMS NUMBER # (runtime) TODO AMEND THIS FOR LIVE CALCULATIONS SINCE PAGE CHILDREN WILL NOT HAVE BEEN CREATED YET IF NEWLY ADDED AND JUST CHECKING CALCULATIONS! EITHER ALWAYS CHECK THAT OR HAVE CONDITION FOR LIVE VERSUS SAVE!
		// @note: just number of all children! we don't count individual line item quantitties!!!
		// @note: mainly used for discount purposes that use 'fixed_per_line_item'
		// @note: this is fine here and includes newly created line items if any since we already invoked  $this->processInputCreateNewItems() in $this->processSaveNonLiveOrder()
		// $this->order->numberOfLineItems = $this->page->numChildren;
		$numberOfLineItems = $this->isLiveCalculateOnly ? count($this->order->liveOrderLineItems) :
			$this->page->numChildren;
		$this->order->numberOfLineItems = $numberOfLineItems;

		// ============== GET SHIPPING COUNTRY TAX + OVERRIDES DATA ======

		// @note: this is an array so need only first value!
		// TODO DELETE WHEN DONE - MOVED!
		// $shippingCountryID = (int) $this->input->pwcommerce_order_customer_shipping_address_country_id[0];

		// // TODO @NOTE: @UPPDATE: NOW JUST USING THE COUNTRY PAGE! makes it easier to search category overrides!
		// // $countryTaxData = $this->pwcommerce->getTaxCountryByID($shippingCountryID);
		//
		// $shippingCountry = $this->wire('pages')->get("id=$shippingCountryID");

		// TODO THIS WILL RETURN ERROR IF NO SHIPPING COUNTRY! NEED TO CAPTURE THAT HERE THE WAY WE DO IN LIVE ORDER?
		// $this->setOrderShippingCountry();
		// SET SHIPPING COUNTRY IN THE ORDER
		$isSetOrderShippingCountry = $this->setOrderShippingCountry();

		if (empty($isSetOrderShippingCountry)) {
			// return early
			$error = $this->_('A shipping country needs to be specified');
			// $isError = true;
			$this->order->isOrderError = true;
			$this->order->isOrderErrorMessage = $error;
		}

		// TODO TEST THIS NEW WAY!!
		if (!empty($this->order->isOrderError)) {

			// order processing error! don't save! show error!
			$this->error($this->order->isOrderErrorMessage);
			// return early
			return;
		}

		// ###########################

		// ========== WORK OUT CALCULABE VALUES ====

		// TODO!
		$orderCalculatedValuesOptions = [
			/** @var WireData $order */
			'order' => $this->order,
			/** @var Page $this->page */
			// TODO IS THIS OK? PASSING LIKE THIS TO AVOID GETTING AGAIN SINCE WE WILL NEED IT TO GET NUMBER OF LINE ITEMS (CHILDREN)
			'order_page' => $this->page,
			/** @var Page $this->shippingCountry */
			'shipping_country' => $this->shippingCountry,
		];
		$this->order = $this->pwcommerce->getOrderCalculatedValues($orderCalculatedValuesOptions);
		// -------
		// TODO DELETE IF NOT IN USE
		// return $this->order;

	}

	/**
	 * Make a string value to represent the order values that can be used for comparison purposes.
	 *
	 * @note: this is only for internal use since we don't have a __toString() method.
	 * @return string
	 *
	 */
	private function toStringInhouse($item) {
		// TODO: ADD OTHER CALCULABE E.G. SHIPMENT STATUS? what about totalprice? hmm, that is always calculated separately anyway (line item post-processing!)
		$string = (string) "$item->discountType: $item->discountValue: $item->handlingFeeType: $item->handlingFeeValue: $item->shippingFee: $item->isCustomHandlingFee: $item->isCustomShippingFee: $item->isChargeTaxesManualExemption: $item->isPricesIncludeTaxes";
		return $string;
	}

	// ~~~~~~~~~~~~~~

	private function getProductOrVariantPagesForCreatingNewOrderLineItems() {
		$productsOrVariantsIDsSelector = implode("|", $this->newOrderLineItemsIDs);
		$selector = "id={$productsOrVariantsIDsSelector}";
		// @note: 'pwcommerce_product_settings' only for main products!
		$fields = ['id' => 'product_id', 'title', 'pwcommerce_product_stock' => 'stock', 'pwcommerce_product_settings' => 'settings', 'pwcommerce_categories' => 'categories', 'parent_id', 'templates_id'];
		//-------------
		$productsOrVariantPages = $this->wire('pages')->findRaw($selector, $fields);
		return $productsOrVariantPages;
	}

	private function getExistingOrderLineItemsPagesToProcess() {
		$existingOrderLineItemsIDsToProcessSelector = implode("|", $this->keptOrderLineItemsIDs);
		/** @var PageArray $orderLineItemsPages */
		$orderLineItemsPages = $this->wire('pages')->find("id={$existingOrderLineItemsIDsToProcessSelector}");
		// --------------
		return $orderLineItemsPages;
	}

	/**
	 * Get the product or product variant for an order line item.
	 *
	 * We need properties including title, product_id, stock values (prices, etc), etc.
	 *
	 * @access private
	 * @param integer $productsOrVariantsID The ID of the product or product variant whose properties to find.
	 * @param boolean $isVariant
	 * @return array $productOrVariantPage Array with needed values from product or product variant page.
	 */
	private function getProductOrVariantPagesForOrderLineItem($productsOrVariantsID, $isVariant = false): array {

		$selector = "id={$productsOrVariantsID}";
		// @note: 'pwcommerce_product_settings' only for main products!
		$fields = ['id' => 'product_id', 'title', 'pwcommerce_product_stock' => 'stock', 'pwcommerce_product_settings' => 'settings', 'pwcommerce_categories' => 'categories', 'parent_id', 'templates_id'];
		//-------------
		$productOrVariantPage = $this->wire('pages')->getRaw($selector, $fields);

		if ($isVariant) {
			// variant's parent settings and categories need fetching from parent product
			$parentProductSettingsAndCategories = $this->getVariantParentProductSettingsAndCategories($productOrVariantPage['parent_id']);
			// add variant parent product settings and categories to variant
			$productOrVariantPage = array_merge($productOrVariantPage, $parentProductSettingsAndCategories);
		}

		// ------------------
		return $productOrVariantPage;
	}

	private function getVariantParentProductSettingsAndCategories($parentID) {
		$parentProductSettingsAndCategories = $this->wire('pages')->getRaw("id={$parentID}", ['pwcommerce_product_settings' => 'settings', 'pwcommerce_categories' => 'categories']);
		return $parentProductSettingsAndCategories;
	}

	private function getOrderLineItemAllowedDiscountTypes() {
		$allowedDiscountTypeValues = ['none', 'percentage', 'fixed_applied_once', 'fixed_applied_per_item'];
		return $allowedDiscountTypeValues;
	}

	private function getSelectedMatchedShippingRate($shippingRateID) {

		$rate = null;
		$shippingRateID = (int) $shippingRateID;
		$fields = ['pwcommerce_shipping_rate.data'];
		$rateValues = $this->wire('pages')->getRaw("id={$shippingRateID}", $fields);
		if (!empty($rateValues)) {
			// get the first array
			$rate = (float) $rateValues['pwcommerce_shipping_rate']['data'];
		}
		// -----------
		return $rate;
	}

	// ~~~~~~~~~~~~~~~

	private function setOrderOrderLineItemsSharedProperties() {
		// $isError = false;
		// $error = null;
		### SETUP CLASS PROPERTIES ###

		// -------------------------
		// SET SHIPPING COUNTRY IN THE ORDER
		$isSetOrderShippingCountry = $this->setOrderShippingCountry();
		if (empty($isSetOrderShippingCountry)) {
			// return early
			$error = $this->_('A shipping country needs to be specified');
			// $isError = true;
			$this->order->isOrderError = true;
			$this->order->isOrderErrorMessage = $error;
		}

		// -------------------------
		// SET CUSTOMER TAX EXEMPTION STATUS IN THE ORDER
		$this->setIsCustomerTaxExempt();

		// -------------------------
		// SET WHOLE ORDER TAX EXEMPTION STATUS
		$this->setIsChargeTaxesManualExemption();
	}
	// TODO TESTING ALTERNATIVE PROCESSING SINLGE LINE ITEM

	/**
	 * Set order line item discount type and value based on form values.
	 *
	 * Note that discount value is not the discount amount!
	 * The amount will be determined later, based on discount type and value!
	 *
	 * @access private
	 * @param WireData $orderLineItem The Order Line Item WireData object to populate with discount type and value.
	 * @return WireData $orderLineItem The populated order line item.
	 */
	private function setOrderLineItemDiscountTypeAndValue(WireData $orderLineItem, $id) {
		// @note: we only allow these discount types
		$allowedDiscountTypeValues = $this->getOrderLineItemAllowedDiscountTypes();
		$discountType = $this->wire('sanitizer')->option($this->input->{"pwcommerce_order_line_item_discount_type{$id}"}, $allowedDiscountTypeValues);
		// default to 'none' if sanitizer returns null for discount type
		if (empty($discountType)) {
			$discountType = 'none';
		}
		$orderLineItem->discountType = $discountType;
		$orderLineItem->discountValue = (float) $this->input->{"pwcommerce_order_line_item_discount_value{$id}"};
		// @note: $order->discountAmount here is a placeholder value!
		// The final value will be calculated in this->pwcommerce->getOrderCalculatedValues()
		$orderLineItem->discountAmount = 0;
		// --------------
		return $orderLineItem;
	}

	/**
	 * Set manual order discount type and value based on form values.
	 *
	 * Note that discount value is not the discount amount!
	 * The amount will be determined later, based on discount type and value!
	 *
	 * @access private
	 * @return void
	 */
	private function setOrderDiscountTypeAndValue() {
		// @note: we only allow these discount types
		$allowedDiscountTypeValues = $this->getOrderLineItemAllowedDiscountTypes();
		$discountType = $this->wire('sanitizer')->option($this->input->pwcommerce_order_discount_type, $allowedDiscountTypeValues);
		// default to 'none' if sanitizer returns null for discount type
		if (empty($discountType)) {
			$discountType = 'none';
		}
		$this->order->discountType = $discountType;
		$this->order->discountValue = (float) $this->input->pwcommerce_order_discount_value;
		// @note: $order->discountAmount here is a placeholder value!
		// The final value will be calculated in this->pwcommerce->getOrderCalculatedValues()
		$this->order->discountAmount = 0;
		// --------------
		// return $order;
	}

	private function setOrderShippingCountry() {
		// ============== GET SHIPPING COUNTRY TAX + OVERRIDES DATA ======

		// @note: this is an array so need only first value!
		$shippingCountryID = (int) $this->input->pwcommerce_order_customer_shipping_address_country_id[0];
		$shippingCountry = $this->wire('pages')->get("id=$shippingCountryID");

		// TODO NEED TO LOAD THIS ERROR IN A TEMP 'error' PROPERTY ON THE WIREDATA! VALUE COULD BE AN ARRAY WITH TYPE AND TEXT OF ERROR MESSAGE
		// TODO: if no shipping country, return error now??? earlier? or in shipping inputfield?
		if (empty($shippingCountry->id)) {
			$isSetOrderShippingCountry = false;
		} else {
			// shipping country found: set it!
			/** @var Page $this->shippingCountry */
			$this->shippingCountry = $shippingCountry;
			$isSetOrderShippingCountry = true;

		}

		return $isSetOrderShippingCountry;
	}

	private function setIsCustomerTaxExempt() {
		// ===========================================
		// CUSTOMER TAX EXEMPTION STATUS
		// TODO GET FROM INPUT OR SAVED VALUE? I THINK INPUT AS IT IS THE LATEST!
		// @note: if value is zero (0), exemption IS NOT being applied, if one (1), customer is tax exempt
		$this->isCustomerTaxExempt = (int) $this->input->pwcommerce_order_customer_is_tax_exempt === 1;

	}

	private function setIsChargeTaxesManualExemption() {
		// MANUAL ORDER TAX EXEMPTION STATUS
		// @note: if value is zero (0), exemption being applied, if one (1), charge taxes normaly
		$this->isChargeTaxesManualExemption = (int) $this->input->pwcommerce_order_apply_manual_tax_exemption === 1;

	}

	/**
	 * Set to $this->order the manual order shipping values based on form values.
	 *
	 * Values set here are whether shipping is custom and in that case, the custom shipping fee.
	 *
	 * @access private
	 * @return void
	 */
	private function setOrderShippingValues() {
		$input = $this->input;
		$isCustomShippingFee = (int) $input->pwcommerce_order_use_custom_shipping_fee;
		// DEFAULT TO 'FREEZING' LAST SAVED SHIPPING FEE
		// TODO: rename variable?
		$shippingFee = $this->order->shippingFee;

		########
		if (!empty($isCustomShippingFee)) {
			// MANUAL/CUSTOM SHIPPING FEE
			// ---------------------

			// manually entered custom shipping fee
			$shippingFee = (float) $input->pwcommerce_order_custom_shipping_fee;
			// FOR CUSTOM SHIPPING isShippingFeeNeedsCalculating is determined differently
			// we check if tax exemptions or shipping country changed OR oldShippingValue does not match incomingShippingFee
			$isShippingFeeNeedsCalculating = $this->isCustomShippingFeeNeedsCalculating();

		} else {
			// CALCULATED SHIPPING FEE
			// ---------------------
			// TODO NEED TO CHANGE THIS! THERE ARE TIMES NO RECALCULATION IS NEEDED! E.G. WHEN CUSTOMER NAME OR EMAIL CHANGES! IN SUCH CASES WE LOOK AT pwcommerce_order_is_shipping_needs_calculating!

			// TODO ADD THIS PROPERTY!! => $order->isNeedShippingFeeCalculation

			// CHECK IF AN EVENT THAT TRIGGERS SHIPPING (RE) CALCULATION HAS OCCURED
			// if yes, calculate shipping. if no, freeze the last calculated shipping fee.
			// @note: this is ok since at least one triggering event will occur even for newly created orders! e.g. products will be added!
			$selectedMatchedShippingRateID = (int) $input->pwcommerce_order_selected_matched_shipping_rate;

			// --------------

			// if shipping needs recalculating BUT no matched shipping rate selected yet, throw error
			// if ($isShippingFeeNeedsCalculating && is_null($selectedMatchedShippingRateID)) {
			// if ($isShippingFeeNeedsCalculating) {
			if (!empty($selectedMatchedShippingRateID)) {
				// // SET SHIPPING NEEDS CALCULATING PROPERTY FOR LATER CHECKING
				$isShippingFeeNeedsCalculating = true;

				// -------------
				// RECALCULATE SHIPPING
				//---------------------
				//
				// @note:
				// - since we don't save the selected shipping rate ID, we check this by the presence of the Input with ID 'pwcommerce_order_selected_matched_shipping_rate'
				// - On the client, that input will be present after recalculation of shipping and taxes has occured on the server
				// - It will be inserted by htmx
				// - If it is not there and changes that require recalculation have been made on the order, client will prevent saving
				// - This means we always have this input when we need it
				//
				####################
				// TODO @NOTE: AT THIS POINT THE SHIPPING HAS ALREADY BEEN CALCULATED IN utilities AND WE HAVE THE ID TO THE SELECTED RATE! SO, JUST GRAB ITS VALUE
				// if (!empty($selectedMatchedShippingRateID)) {
				// get the RATE for the matched shipping rate. It is a page
				$rate = $this->getSelectedMatchedShippingRate($selectedMatchedShippingRateID);
				if (!is_null($rate)) {
					// TODO ALLOW NULL?
					// TODO NOT SURE STILL NEEDED? YES! @see PWCommerceUtilities::getOrderShippingFee!
					$this->order->selectedMatchedShippingRate = $rate;
					// ---------------
				} else {
					// given matched shipping rate ID did not match any shipping rate page
					//  shipping needs recalculating BUT no matched shipping rate selected yet, throw error
					$notice = $this->_('Selected matched shipping rate was not found! Please try again.');
					$this->order->isOrderError = true;
					$this->order->isOrderErrorMessage = $notice;
					return;
				}
			} else {
				// CALCULATED SHIPPING DOES NOT NEED RE-CALCULATING
				$isShippingFeeNeedsCalculating = false;
				// TODO DELETE WHEN DONE

			}

			// --------------
			// GOOD TO GO


			###############
			// we will calculate shipping automatically
			// @note: $shippingFee here is a placeholder value!
			// The final value will be calculated in this->pwcommerce->getOrderCalculatedValues()
			// $shippingFee = 0;
			$isCustomShippingFee = 0;

		} // end: NOT CUSTOM SHIPPING FEE

		// TODO: we will check in utilities: if custom shipping, we don't calculate! if not, we do automatic calculation!
		$this->order->isCustomShippingFee = $isCustomShippingFee;
		$this->order->shippingFee = $shippingFee;
		// SET SHIPPING NEEDS CALCULATING PROPERTY FOR LATER CHECKING
		$this->order->isShippingFeeNeedsCalculating = $isShippingFeeNeedsCalculating;

		// --------------
		// return $order;
	}
	private function setOrderShippingValuesOLDDelete() {
		// TODO DELETE THIS ONE AS WE NO LONGER CHECK RATE ID IN THIS WAY -> INSTEAD WE ARE PREVENTING SAVING ON THE CLIENT UNTIL RECALCULATION REQUIREMENTS ARE FULFILLED
		// +++++++++++++++++++++
		$input = $this->input;
		$isCustomShippingFee = (int) $input->pwcommerce_order_use_custom_shipping_fee;
		if (!empty($isCustomShippingFee)) {
			// MANUAL/CUSTOM SHIPPING FEE
			// ---------------------

			// manually entered custom shipping fee
			$shippingFee = (float) $input->pwcommerce_order_custom_shipping_fee;
		} else {
			// CALCULATED SHIPPING FEE
			// ---------------------
			// TODO NEED TO CHANGE THIS! THERE ARE TIMES NO RECALCULATION IS NEEDED! E.G. WHEN CUSTOMER NAME OR EMAIL CHANGES! IN SUCH CASES WE LOOK AT pwcommerce_order_is_shipping_needs_calculating!

			// TODO ADD THIS PROPERTY!! => $order->isNeedShippingFeeCalculation

			// DEFAULT TO 'FREEZING' LAST SAVED SHIPPING FEE
			$shippingFee = $this->order->shippingFee;

			// CHECK IF AN EVENT THAT TRIGGERS SHIPPING (RE) CALCULATION HAS OCCURED
			// if yes, calculate shipping. if no, freeze the last calculated shipping fee.
			// @note: this is ok since at least one triggering event will occur even for newly created orders! e.g. products will be added!
			// TODO:still need to check if a matched shipping rate has been selected!
			// TODO REVISIT THIS SINCE NOW HAVE NEW APROACH! + we don't use this input!
			$isShippingFeeNeedsCalculating = (int) $input->pwcommerce_order_is_shipping_needs_calculating;
			$selectedMatchedShippingRateID = $input->pwcommerce_order_selected_matched_shipping_rate;

			// TODO TEMPORARY FOR TESTING
			$isShippingFeeNeedsCalculating = true;
			// if shipping needs recalculating BUT no matched shipping rate selected yet, throw error
			// if ($isShippingFeeNeedsCalculating && is_null($selectedMatchedShippingRateID)) {
			if ($isShippingFeeNeedsCalculating) {
				// SET SHIPPING NEEDS CALCULATING PROPERTY FOR LATER CHECKING
				$this->order->isShippingFeeNeedsCalculating = true;
				// -------------
				// RECALCULATE SHIPPING
				//---------------------
				// if (is_null($selectedMatchedShippingRateID)) {
				if (empty($selectedMatchedShippingRateID)) {
					// if no matched shipping rate selected, throw error
					$notice = $this->_('You need to calculate and select a matched shipping rate. This has to be done before you can continue editing this order!');
					$this->order->isOrderError = true;
					$this->order->isOrderErrorMessage = $notice;
					return;
				} else {
					## =========
					// TODO: I THINK WE NEED TO SAVE THIS SAVED RATE ID OR SIMILAR SO THAT CAN SHOW AGAIN? HOWEVER, THIS IS NOT ENTIRELY CORRECT APPROACH! IF PRODUCTS OR THEIR VARIABLES CHANGE, WE WILL NEED TO RECALCULATE SHIPPING! WE NEED TO SHOW THIS IN THE JS THEN! I.E., ALERT TO RECALCULATE! WOULD HAVE PREFERED A 'BANNER ALERT!'
					// CHECK IF A 'MATCHED' SHIPPING RATE HAS BEEN SELECTED
					// TODO! NEED TO CHECK NULL NOW!
					####################
					// TODO @NOTE: AT THIS POINT THE SHIPPING HAS ALREADY BEEN CALCULATED IN utilities AND WE HAVE THE ID TO THE SELECTED RATE! SO, JUST GRAB ITS VALUE
					$selectedMatchedShippingRateID = (int) $selectedMatchedShippingRateID;
					if (!empty($selectedMatchedShippingRateID)) {
						// get the RATE for the matched shipping rate. It is a page
						$rate = $this->getSelectedMatchedShippingRate($selectedMatchedShippingRateID);
						if (!is_null($rate)) {
							// TODO ALLOW NULL?
							// TODO NOT SURE STILL NEEDED? YES! @see PWCommerceUtilities::getOrderShippingFee!
							$this->order->selectedMatchedShippingRate = $rate;
							// ---------------
						} else {
							// given matched shipping rate ID did not match any shipping rate page
							$notice = $this->_('Selected matched shipping rate was not found! Please try again.');
							$this->order->isOrderError = true;
							$this->order->isOrderErrorMessage = $notice;
							return;
						}
					} else {
						// selected matched shipping rate ID is empty! (for some reason, just in case)
						// TODO: BETTER ERROR MESSAGE HERE!
						$notice = $this->_('Selected matched shipping rate was not found! Please try again.');
						$this->order->isOrderError = true;
						$this->order->isOrderErrorMessage = $notice;
						return;
					}
				}
			}

			// --------------
			// GOOD TO GO


			###############
			// we will calculate shipping automatically
			// @note: $shippingFee here is a placeholder value!
			// The final value will be calculated in this->pwcommerce->getOrderCalculatedValues()
			// $shippingFee = 0;
			$isCustomShippingFee = 0;

		}
		// end: NOT CUSTOM SHIPPING FEE

		// TODO: we will check in utilities: if custom shipping, we don't calculate! if not, we do automatic calculation!
		$this->order->isCustomShippingFee = $isCustomShippingFee;
		$this->order->shippingFee = $shippingFee;

		// --------------
		// return $order;
	}

	private function isCustomShippingFeeNeedsCalculating() {
		// custom shipping fee neededing calculating is only affected by customer country or tax exemption change OR manual tax exemption change
		// @note: above will not apply if taxes not charged on shipping -> that will be sorted out in PWCommerceUtilities::getOrderShippingFee()
		// OR
		// oldShippingFee does not match incomingShippingFee
		$oldShippingFee = $this->order->shippingFee;
		$incomingShippingFee = (float) $this->input->pwcommerce_order_custom_shipping_fee;
		$isNotMatchOldShippingFeeIncomingShippingFee = $oldShippingFee !== $incomingShippingFee;

		// -----------
		$isCustomShippingFeeNeedsCalculating = $this->isCustomerValuesForProcessCalculableValuesChanged || $this->isChargeTaxesManualExemptionChanged() || $isNotMatchOldShippingFeeIncomingShippingFee;
		return $isCustomShippingFeeNeedsCalculating;
	}

	/**
	 * Set manual order handling fee values based on form values.
	 *
	 * Values set here are whether handling fee is custom and in that case, the custom handling fee type and value.
	 * Note that handling fee value is not the handling fee amount!
	 * The amount will be determined later, based on handling fee type and value!
	 *
	 * @access private
	 * @return void
	 */
	private function setOrderHandlingFeeValues() {
		$input = $this->input;
		$isCustomHandlingFee = (int) $input->pwcommerce_order_use_custom_handling_fee;
		if (!empty($isCustomHandlingFee)) {

			// manually entered custom handling fee type and value
			// -----------
			// TODO: FOR CUSTOM HANDLING FEE, BELOW NOT IN USE FOR NOW! EDITOR JUST ENTERS A FIXED VALUE
			// $allowedHandlingFeeTypeValues = ['none', 'percentage', 'fixed'];
			// $handlingFeeType = $this->wire('sanitizer')->option($input->pwcommerce_order_handling_fee_type, $allowedHandlingFeeTypeValues);
			// TODO @NOTE: REVERT THIS TO ABOVE IF WE MAKE CUSTOM HANDLING FEE HAVE TYPES TO CHOOSE FROM
			$handlingFeeType = 'fixed';
			// default to 'none' if sanitizer returns null for handling fee type
			if (empty($handlingFeeType)) {
				$handlingFeeType = 'none';
			}
			// ----------
			// TODO: DELETE WHEN DONE! WE NOW HAVE DEDICATED INPUT FOR CUSTOM HANDLING FEE!
			// $handlingFeeValue = (float) $input->pwcommerce_order_handling_fee_value;
			$handlingFeeValue = (float) $input->pwcommerce_order_custom_handling_fee;
		} else {
			// calculate handling automatically
			// TODO! UTILITIES?
			$isCustomHandlingFee = 0;
			// @note: $handlingFeeType and $handlingFeeValue here are placeholder values!
			$handlingFeeType = 'none';
			$handlingFeeValue = 0;

		}

		// init handling fee properties.
		// The final values will be determined and set in $this->pwcommerce->getOrderCalculatedValues()!
		$this->order->isCustomHandlingFee = $isCustomHandlingFee;
		$this->order->handlingFeeType = $handlingFeeType;
		$this->order->handlingFeeValue = $handlingFeeValue;
		// @note: placeholder value! Will be calculated in this->pwcommerce->getOrderCalculatedValues()
		$this->order->handlingFeeAmount = 0;

		// --------------
		// return $order;
	}

	/**
	 * Set manual order taxes values based on form values.
	 *
	 * Values set here are whether a manual tax exemption is set by user and 'prices include taxes' value set when the order was created.
	 *
	 * @access private
	 * @return void
	 */
	private function setOrderTaxesValues() {
		$incomingIsChargeTaxesManualExemption = (int) $this->input->pwcommerce_order_apply_manual_tax_exemption;
		// --------
		//  set incoming order manual tax exemption
		$this->order->isChargeTaxesManualExemption = $incomingIsChargeTaxesManualExemption;
		// also set if customer is tax exempt -> need this to also check if shipping is taxable
		$this->order->isCustomerTaxExempt = (int) $this->isCustomerTaxExempt; // as int just to match manual exemption above
		// ++++++++++++++++
		// @note: this is an immutable value set when a new order is created
		// so, we retrieve directly from the order page
		// $isPricesIncludeTaxes = $this->page->pwcommerce_order->isPricesIncludeTaxes;
		$isPricesIncludeTaxes = $this->page->get(PwCommerce::ORDER_FIELD_NAME)->isPricesIncludeTaxes;
		$this->order->isPricesIncludeTaxes = $isPricesIncludeTaxes;
		// --------------
		// return $order;
	}

	// TODO - REFACTOR THIS! IT IS NEAR IDENTICAL TO PROCESSINPUT AND PROCESSINPUTEXISTING! NEED TO MAKE ONE AGNOSTIC METHOD THAT RETURNS A WIREARRAY OF WIREDATA OF LINE ITEMS! WE CAN THEN PASS THAT WIREARRAY TO WHICHEVER PROCESS THAT NEEDS IT! E.G. FOR SAVING LINE ITEMS, FOR CREATING LINE ITEMS OR FOR LIVE CALCULATIONS IN UTILITIES!!!

	// private function setLiveOrderLineItemsValues(array $inEditOrderLineItemsProductsIDs, array $inEditOrderLineItemsIDs): array {
	private function setLiveOrderLineItemsValues(): array {
		// TODO THOROUGHLY TEST THIS GIVEN NEW CHANGES IN MARCH 2022!
		// TODO: LOOP THROUGH liveOrderLineItemsProductsIDs AND GET - change this! we need lineitems ids for exsiting ones' inputs!
		$inEditOrderLineItems = [];
		// TODO CONFIRM THIS NEW APPROACH WORKS!
		$inEditOrderLineItemsIDs = $this->inEditOrderLineItemsIDs;
		$inEditOrderLineItemsProductsIDs = $this->inEditOrderLineItemsProductsIDs;


		// LIVE ITEMS ARE TREATED AS NEW ITEMS HENCE WILL REQUIRE PROCESSING OF THEIR TRUE PRICE!!!
		//TODO - DO WE NEED TO SET THE UNIT PRICE BELOW THEN?
		$this->isProcessTruePrice = true;

		// ----------
		// LOOP THROUGH AND SET VALUES
		foreach ($inEditOrderLineItemsIDs as $index => $id) {

			if (empty($id))
				continue;

			$orderLineItem = new WireData();

			$orderLineItem->id = $id;
			// -----------
			// TODO confirm this! product_id and id, although in different arrays, will be at same index!
			$productID = $inEditOrderLineItemsProductsIDs[$index];
			$orderLineItem->productID = $productID;
			// @note: just for live items, we check directly. This is ok since values in live items will not be saved! We are also in the backend! TODO OK?
			$orderLineItem->isVariant = (int) $this->input->{"pwcommerce_order_line_item_is_variant{$id}"};
			// ##############################
			// PROCESS EDITABLE VALUES + CALCULABLE ONES
			$orderLineItem = $this->processOrderLineItem($orderLineItem, $id);

			// TODO: CONFIRM THIS IS OK! ESPECIALLY THAT WE NEED 'data' KEY!!
			$orderLineItemArray = $orderLineItem->getArray();
			// @note: productID saved at 'data' column in the schema! We match that here to make this agnostic
			// TODO confirm this! product id and id, although in different arrays, will be at same index!
			// we also do the same for 'total_price_discounted' and 'total_price_discounted_with_tax' as these will be required by ORDER in PWCommerceUtilities::getOrderLineItemsSubTotalNetDiscountedPriceAmount() and PWCommerceUtilities::getOrderLineItemsSubTotalNetDiscountedPriceWithTaxInCents()
			$orderLineItemArray['data'] = $inEditOrderLineItemsProductsIDs[$index];
			$orderLineItemArray['total_price_discounted'] = $orderLineItem->totalPriceDiscounted;
			$orderLineItemArray['total_price_discounted_with_tax'] = $orderLineItem->totalPriceDiscountedWithTax;
			$inEditOrderLineItems[] = $orderLineItemArray;
		}

		// ------------
		return $inEditOrderLineItems;
	}

	// ~~~~~~~~~~~

	/**
	 * Make a string value to represent these values that can be used for comparison purposes.
	 *
	 * @note We only compare three mutable values with respect to editing an order line item.
	 * @note: this is only for internal use since we don't have a __toString() method.
	 *
	 * @access private
	 * @param integer $editedQuantity The quantity of this line item.
	 * @param string $editedDiscountType One of 'none|percentage|fixed_applied_once|fixed_applied_per_item' representing discount type.
	 * @param float $editedDiscountValue Discount value, e.g. 1.5% or €4.99
	 * @return bool Whether above values are different from their counterpart saved values
	 *
	 */
	private function isChangedOrderLineItem($orderLineItem, $id) {
		$editedQuantity = (int) $this->input->{"pwcommerce_order_line_item_quantity{$id}"};
		// ----
		// @note: we only allow these discount types
		$allowedDiscountTypeValues = $this->getOrderLineItemAllowedDiscountTypes();
		$editedDiscountType = $this->wire('sanitizer')->option($this->input->{"pwcommerce_order_line_item_discount_type{$id}"}, $allowedDiscountTypeValues);
		// default to 'none' if sanitizer returns null for discount type
		if (empty($editedDiscountType)) {
			$editedDiscountType = 'none';
		}
		// ----
		$editedDiscountValue = (float) $this->input->{"pwcommerce_order_line_item_discount_value{$id}"};
		// ------------
		//$orderLineItem = $this->attr('value');
		$editedString = (string) "$editedQuantity: $editedDiscountType: $editedDiscountValue";
		$savedString = (string) "$orderLineItem->quantity: $orderLineItem->discountType: $orderLineItem->discountValue";

		return $editedString !== $savedString;
	}

	private function isChargeTaxesManualExemptionChanged() {
		// @note: by this point, the value of $this->order->isChargeTaxesManualExemption IS THE SAVED ONE
		// it hasn't been changed yet at setOrderTaxesValues()
		$isChargeTaxesManualExemptionChanged = (int) $this->input->pwcommerce_order_apply_manual_tax_exemption !==
			(int) $this->order->isChargeTaxesManualExemption;

		return $isChargeTaxesManualExemptionChanged;
	}
}