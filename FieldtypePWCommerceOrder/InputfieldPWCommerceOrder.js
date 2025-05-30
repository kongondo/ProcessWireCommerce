const InputfieldPWCommerceOrder = {
	listenToHTMXRequests: function () {
		// before request
		htmx.on("htmx:beforeSend", function (event) {
			// set found addable products to false before sending htmx request for search products
			const eventName = "pwcommercefoundaddableproducts"
			const eventDetail = false
			// @note: method is in PWCommerceCommonScripts.js
			PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
		})

		// after swap
		htmx.on("htmx:afterSwap", function (event) {
			// RUN POST SWAP OPS
			InputfieldPWCommerceOrder.runAfterSwapOperations(event)
		})

		// after settle
		// @note: aftersettle is fired AFTER  afterswap
		// we use it to tell alpine js our checkboxes are ready
		// @todo: maybe even use css to transition in so user doesn't 'perceive' a delay?
		htmx.on("htmx:afterSettle", function (event) {
			// RUN POST SETTLE OPS
			InputfieldPWCommerceOrder.runAfterSettleOperations(event)
		})
	},

	/**
	 * Run afterSwap operations (post htmx swap).
	 * These depend on the htmx request context.
	 * @param {object} event Object containing the event that triggered the request or custom object with post-op details.
	 */
	runAfterSwapOperations: function (event) {
		// @todo: need to ensure we always have this even for manually triggered htmx.ajax()!
		const requestElementID = event.detail.elt.id
		if (!requestElementID) return
		//---------

		if (requestElementID === "pwcommerce_order_calculated_taxes_amount") {
			// calculated taxes and shipping post-op
			InputfieldPWCommerceOrder.runCalculatedTaxesPostOperations()
		} else if (
			requestElementID === "pwcommerce_order_customer_shipping_address_country_id"
		) {
			// customer shipping country change post-op
			// @note: event not linked to HTMX but event object here 'mimicks' htmx's event structure for syntax consistency
			const shippingCountryDetails = {
				shipping_address_country_id:
					event.detail.elt.shipping_address_country_id,
				shipping_address_country: event.detail.elt.shipping_address_country,
			}
			InputfieldPWCommerceOrder.runShippingAddressCountryChangePostOperations(
				shippingCountryDetails
			)
		}
	},

	/**
	 * Run afterSettle operations (after htmx swap).
	 * These depend on the htmx request context.
	 * Use this so that alpine js can work on 'settled' dom contents.
	 * @param {object} event Object containing the event that triggered the request or custom object with post-op details.
	 */
	runAfterSettleOperations: function (event) {
		// @todo: need to ensure we always have this even for manually triggered htmx.ajax()!
		const requestElementID = event.detail.elt.id
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceOrder - runAfterSettleOperations - event",
		// 	"info",
		// 	event
		// )
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceOrder - runAfterSettleOperations - requestElementID",
		// 	"log",
		// 	requestElementID
		// )
		if (!requestElementID) return
		//---------
		if (requestElementID === "search-results") {
			// product search post-op
			InputfieldPWCommerceOrder.runProductsSearchPostOperations()
		} else if (requestElementID === "pwcommerce_notes_wrapper") {
			// a new order note added
			InputfieldPWCommerceOrder.runNewOrderNoteAddedPostOperations(event)
		} else if (requestElementID === "pwcommerce_order_matched_shipping_rates") {
			// calculate shipping post-op
			InputfieldPWCommerceOrder.runCalculateShippingPostOperations()
		}
	},

	// ~~~~~~~~~~~~~

	// Run post-product-search operations.
	// @note: Alpine listening to the custom event here.
	runProductsSearchPostOperations: function () {
		// @TODO TRYING TO FIX A BUG THAT SOMETIMES SHOWS WHEN WE SELECT PRODUCTS with variants or the variants checkboxes after a htmx swap
		// @note/@update: now using afterSettle RE ABOVE BUG @see above
		const eventName = "pwcommercefoundaddableproducts"
		const eventDetail = true
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	// Run post-order-note-added operations.
	runNewOrderNoteAddedPostOperations: function (event) {
		const triggerElement = event.detail.elt
		// get the last inserted note's textarea
		const newlyAddedNoteTextareaElement =
			triggerElement.firstElementChild.querySelector(".pwcommerce_note_text")

		if (newlyAddedNoteTextareaElement) {
			// focus the newly added note textarea
			newlyAddedNoteTextareaElement.focus()
		}
	},

	// Run post-calculate-shipping operations.
	// @note: Alpine listening to the custom event here.
	runCalculateShippingPostOperations: function () {
		const eventName = "pwcommercecalculatedordershipping"
		const eventDetail = true
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceOrder - runCalculateShippingPostOperations - eventName",
		// 	"log",
		// 	eventName
		// )
	},

	runCalculatedTaxesPostOperations: function () {
		// @TODO: SEEMS THIS HIGHLIGHT NO LONGER WORKING?
		// highlight the order taxes inputfield markup
		Inputfields.highlight("#pwcommerce_order_calculated_taxes")
	},

	// if customer shipping address country has changed.
	// run this post-op to dispatch event to Alpine JS.
	// @note: we dispatch a custom event since could not add custom attrs to InputfieldPageAutocomplete.
	// @note: Alpine listening to the custom event here.
	runShippingAddressCountryChangePostOperations: function (
		shippingCountryDetails
	) {
		// @TODO: SAVE TITLE TOO OR JUST ID? REMEMBER, ONLY FOR DISPLAY; WE CHECK AGAIN IN BACKEND!
		const eventName = "pwcommerceordercustomercountrychange"
		const eventDetail = shippingCountryDetails // @note: object!
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	initMonitorOrderCustomerShippingAddressCountry: function () {
		const shippingAddressCountryIDElement = document.getElementById(
			"pwcommerce_order_customer_shipping_address_country_id_input"
		)
		if (shippingAddressCountryIDElement) {
			// add event listener to autocomplete shipping country
			shippingAddressCountryIDElement.addEventListener(
				"change",
				InputfieldPWCommerceOrder.handleShippingAddressCountryChange,
				false
			)
		}

		const removeShippingAddressCountryElement = document.querySelector(
			"#wrap_pwcommerce_order_customer_shipping_address_country_id .InputfieldPageAutocompleteRemove"
		)
		if (removeShippingAddressCountryElement) {
			// add event listening to 'remove autocomplete shipping country'
			// #wrap_pwcommerce_order_customer_shipping_address_country InputfieldPageAutocompleteRemove
			removeShippingAddressCountryElement.addEventListener(
				"click",
				InputfieldPWCommerceOrder.handleShippingAddressCountryChange,
				false
			)
		}
	},

	// @NOTE: MOVED TO InputfieldPWCommerceOrderCustomer.js
	// initMonitorCopyOrderCustomerShippingNamesFromMainCustomerNames: function () {
	// 	const copyNamesElement = document.getElementById(
	// 		"pwcommerce_customer_copy_shipping_names_from_main_names"
	// 	)
	// 	if (copyNamesElement) {
	// 		// add event listener to copy customer main names to order shipping names
	// 		copyNamesElement.addEventListener(
	// 			"click",
	// 			InputfieldPWCommerceOrder.handleCopyCustomerShippingNamesFromMainCustomerNames,
	// 			false
	// 		)
	// 	}
	// },

	// ~~~~~~~~~~~~~~~~~

	/**
	 * Listen to whole order discount type changes.
	 *
	 * We use to toggle '%' sign if handling fee type is 'percentage' or currency symbol if available if type is of 'fixed' types.
	 */
	initListenToWholeOrderDiscountType: function () {
		const wholeOrderDiscountTypeElement = document.getElementById(
			"pwcommerce_order_discount_type"
		)
		if (wholeOrderDiscountTypeElement) {
			// add event listener to whole order discount type change
			wholeOrderDiscountTypeElement.addEventListener(
				"change",
				InputfieldPWCommerceOrder.handleWholeOrderDiscountTypeChange,
				false
			)
		}
	},

	initMonitorIsDraftOrderSaveable: function () {
		for (const publishButtonElementID of [
			"submit_save_unpublished_copy",
			"submit_save_unpublished",
			// these two not necessary since published orders are not editable but just in case
			"submit_save_copy",
			"submit_save",
		]) {
			const publishElement = document.getElementById(publishButtonElementID)
			if (publishElement) {
				publishElement.addEventListener(
					"click",
					InputfieldPWCommerceOrder.handleIsDraftOrderSaveable,
					false
				)
			}
		}
	},

	initMonitorIsDraftOrderPublishable: function () {
		for (const publishButtonElementID of [
			"submit_publish_copy",
			"submit_publish",
		]) {
			const publishElement = document.getElementById(publishButtonElementID)
			if (publishElement) {
				publishElement.addEventListener(
					"click",
					InputfieldPWCommerceOrder.handleIsDraftOrderPublishable,
					false
				)
			}
		}
	},

	handleShippingAddressCountryChange: function (event) {
		// shipping country change: added or removed: check value
		const shippingCountryIDElement = document.getElementById(
			"pwcommerce_order_customer_shipping_address_country_id"
		)

		const shippingCountryElement = document.getElementById(
			"pwcommerce_order_customer_shipping_address_country_id_input"
		)
		if (shippingCountryIDElement) {
			// #pwcommerce_order_customer_shipping_address_country_id_input
			// data-selectedlabel
			const shippingCountryID = shippingCountryIDElement.value
			const shippingCountry = shippingCountryElement.dataset.selectedlabel
			const mockEvent = {
				detail: {
					elt: {
						id: "pwcommerce_order_customer_shipping_address_country_id",
						shipping_address_country_id: shippingCountryID,
						shipping_address_country: shippingCountry,
					},
				},
			}
			InputfieldPWCommerceOrder.runAfterSwapOperations(mockEvent)
		}
	},

	// @NOTE: MOVED TO InputfieldPWCommerceOrderCustomer.js
	// handleCopyCustomerShippingNamesFromMainCustomerNames: function (event) {
	// 	// 'customer_name_id' -> ID of inputs with customer names whose values to copy
	// 	// 'shipping_name_id' -> IDs of inputs with customer shipping names to replace
	// 	const customerNamesToCopyReplaceElementsIDs = [
	// 		// first name
	// 		{
	// 			customer_name_id: "pwcommerce_order_customer_first_name",
	// 			shipping_name_id: "pwcommerce_order_customer_shipping_address_first_name",
	// 		},
	// 		// middle name
	// 		{
	// 			customer_name_id: "pwcommerce_order_customer_middle_name",
	// 			shipping_name_id:
	// 				"pwcommerce_order_customer_shipping_address_middle_name",
	// 		},
	// 		// last name
	// 		{
	// 			customer_name_id: "pwcommerce_order_customer_last_name",
	// 			shipping_name_id: "pwcommerce_order_customer_shipping_address_last_name",
	// 		},
	// 	]
	// 	// ----
	// 	for (const elementsIDs of customerNamesToCopyReplaceElementsIDs) {
	// 		const copyElement = document.getElementById(
	// 			elementsIDs["customer_name_id"]
	// 		)
	// 		if (copyElement) {
	// 			const replaceElement = document.getElementById(
	// 				elementsIDs["shipping_name_id"]
	// 			)
	// 			if (replaceElement) {
	// 				replaceElement.value = copyElement.value
	// 			}
	// 		}
	// 	}
	// },

	handleWholeOrderDiscountTypeChange: function (event) {
		// whole order discount fee type change: need to show or hide % or symbol in description of 'whole order discount fee value'
		const wholeOrderDiscountValueElement = document.getElementById(
			"pwcommerce_order_discount_value"
		)
		const selectedWholeOrderDiscountType = event.target.value
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceOrder - handleWholeOrderDiscountTypeChange - selectedWholeOrderDiscountType",
		// 	"log",
		// 	selectedWholeOrderDiscountType
		// )

		if (wholeOrderDiscountValueElement) {
			// get the two spans with the percentage and currency symbols
			const wholeOrderDiscountValuePercentageSymbolElement =
				document.getElementById("pwcommerce_order_discount_value_percent_symbol")
			const wholeOrderDiscountValueCurrencySymbolElement =
				document.getElementById("pwcommerce_order_discount_value_currency_symbol")
			// toggle show/hide symbols markup
			if (
				["fixed_applied_once", "fixed_applied_per_item"].includes(
					selectedWholeOrderDiscountType
				)
			) {
				// if whole order discount type is one of the fixed types - show currency symbol + hide percentage symbol
				wholeOrderDiscountValueCurrencySymbolElement.classList.remove(
					"pwcommerce_hide"
				)
				wholeOrderDiscountValuePercentageSymbolElement.classList.add(
					"pwcommerce_hide"
				)
			} else if (selectedWholeOrderDiscountType === "percentage") {
				// if whole order discount type is percentage - show percentage symbol + hide currency symbol
				wholeOrderDiscountValuePercentageSymbolElement.classList.remove(
					"pwcommerce_hide"
				)
				wholeOrderDiscountValueCurrencySymbolElement.classList.add(
					"pwcommerce_hide"
				)
			} else {
				// order whole discount type is none - hide both percentage and currency symbols
				wholeOrderDiscountValueCurrencySymbolElement.classList.add(
					"pwcommerce_hide"
				)
				wholeOrderDiscountValuePercentageSymbolElement.classList.add(
					"pwcommerce_hide"
				)
			}
		}
	},

	// @TODO REFACTOR IN LATER RELEASES TO COMBINE WITH HANDLER FOR PUBLISHABLE

	handleIsDraftOrderSaveable: function (event) {
		// prevent form submission until we've determined if order is saveable
		const isDraftOrderSaveable =
			InputfieldPWCommerceOrder.checkIsOKToSubmitOrderForm()
		// if order is saveable, let form submit -> return early
		if (isDraftOrderSaveable) return
		// -------
		event.preventDefault()
		// send to AlpineJS to carry out 'is saveable' checks
		const eventName = "pwcommerceisordersaveable"
		const eventDetail = true
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	handleIsDraftOrderPublishable: function (event) {
		// prevent form submission until we've determined if order is publishable
		const isDraftOrderSaveable =
			InputfieldPWCommerceOrder.checkIsOKToSubmitOrderForm()
		// if order is saveable, let form submit -> return early
		if (isDraftOrderSaveable) return
		// -------
		event.preventDefault()
		// send to AlpineJS to carry out 'is publishable' checks
		const eventName = "pwcommerceisorderpublishable"
		const eventDetail = true
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	toggleOrderLineItemDiscountTypeSymbol: function (
		rateCriteriaTypeTopParentElement,
		selectedOrderLineItemDiscountType
	) {
		// get the span inside the li child of xxxxxxxxx
		// that has the symbol for percentage (%)
		const orderLineItemDiscountTypePercentageSymbolElement =
			rateCriteriaTypeTopParentElement.querySelector(
				"span.pwcommerce_shipping_rate_criteria_type_weight"
			)
		// get the span inside the li child of rateCriteriaTypeTopParentElement
		// that has the symbol for fixed type discounts (currency symbol)
		const orderLineItemDiscountTypeCurrencySymbolElement =
			rateCriteriaTypeTopParentElement.querySelector(
				"span.pwcommerce_shipping_rate_criteria_type_price"
			)
		// +++++++++++
		// toggle show/hide symbols markup
		if (selectedOrderLineItemDiscountType === "weight") {
			// if rate criteria type is weight - show weight(kg) symbol + hide price(currency) symbol
			orderLineItemDiscountTypePercentageSymbolElement.classList.remove(
				"pwcommerce_hide"
			)
			orderLineItemDiscountTypeCurrencySymbolElement.classList.add(
				"pwcommerce_hide"
			)
		} else if (selectedOrderLineItemDiscountType === "price") {
			// if rate criteria type is price - show price (currency) symbol + hide weight(kg) symbol
			orderLineItemDiscountTypeCurrencySymbolElement.classList.remove(
				"pwcommerce_hide"
			)
			orderLineItemDiscountTypePercentageSymbolElement.classList.add(
				"pwcommerce_hide"
			)
		} else {
			// handling fee type is none - hide both percentage and currency symbols
			orderLineItemDiscountTypePercentageSymbolElement.classList.add(
				"pwcommerce_hide"
			)
			orderLineItemDiscountTypeCurrencySymbolElement.classList.add(
				"pwcommerce_hide"
			)
		}
	},

	checkIsOKToSubmitOrderForm() {
		const pwcommerceOrderAlpineJSStore =
			InputfieldPWCommerceOrder.getPWCommerceOrderAlpineJSStore()
		const isNeedToRecalculateShippingAndTaxesForOrder =
			pwcommerceOrderAlpineJSStore.is_need_to_recalculate_shipping_and_taxes
		// @note: if recalculation needed, it means order is not saveable, hence negation
		return !isNeedToRecalculateShippingAndTaxesForOrder
	},
	getPWCommerceOrderAlpineJSStore() {
		const pwcommerceOrderAlpineJSStore = Alpine.store(
			"InputfieldPWCommerceOrderStore"
		)
		return pwcommerceOrderAlpineJSStore
	},
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	if (typeof htmx !== "undefined") {
		InputfieldPWCommerceOrder.listenToHTMXRequests()
	}
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (ProcessPWCommerce) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	)
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		InputfieldPWCommerceOrder.initMonitorOrderCustomerShippingAddressCountry()
		// @NOTE: MOVED TO InputfieldPWCommerceOrderCustomer.js
		// InputfieldPWCommerceOrder.initMonitorCopyOrderCustomerShippingNamesFromMainCustomerNames()
		// ----------

		// monitor order save button click to prevent saving non-ready draft orders
		// @note: this is mainly for save + keep unpublished as after publishing, editing will not be possible
		InputfieldPWCommerceOrder.initMonitorIsDraftOrderSaveable()
		// monitor order publish button click to prevent publishing non-ready draft orders
		InputfieldPWCommerceOrder.initMonitorIsDraftOrderPublishable()
		// handle changes to whole order discount type change
		InputfieldPWCommerceOrder.initListenToWholeOrderDiscountType()
	}
	// end: if in pwcommerce shop context
})

// ALPINE
document.addEventListener("alpine:init", () => {
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (ProcessPWCommerce) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	)
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		Alpine.store("InputfieldPWCommerceOrderStore", {
			// MODAL CHECKED ITEMS
			modal_checked_variants: [],
			// @todo @note: since products with variants are never really added to the order themselves, no need to track their modal checked status
			// modal_checked_products_with_variants: [],
			modal_checked_products_without_variants: [],
			//-------------
			// CONFIGS
			shop_currency_config: {},
			//-------------
			// DATA
			order_whole_data: {},
			// -------
			// immutable once set on load and only contains IDs of current existing order line items on the server
			// we populate it on load once, via $refs
			order_existing_line_items_ids: [],
			// -------
			// contains both existing and incoming order line items
			order_line_items: [],
			order_new_line_items: [],
			// @todo: work on this plus only add if item existed before! i.e. not new! are we tracking is new? NO -> BUT EASY TO KNOW: IF item.id === item.productID then it is new!
			order_deleted_line_items: [],
			order_products_search_results: [],

			// @note: temporary for display one for totals modelling
			// this prevents infinite loop caused by watching wholse order_whole_data and we change totlaPrice and subtotalPrice
			// after trigger recalculate
			temporary_subtotal_price: 0,
			temporary_total_price: 0,
			temporary_handling_fee_amount: 0,
			temporary_shipping_fee_amount: 0,

			//----------------
			// @TODO DELETE WHEN DONE NOT IN USE
			// FOR EDITING ITEMS IN MODALS (cloned or temporary values)
			edit_whole_order_discount: {},
			edit_whole_order_shipping: {},
			edit_whole_order_taxes: {},
			edit_current_order_line_item_discount: {
				// @todo: null instead?
				discountType: "none",
				discountValue: 0,
				totalPriceDiscounted: 0, // @todo: make reactive or be updating
			},
			// for IN EDIT selected matched shipping rate IN modal
			// @note: this value not yet commited
			// @NOTE: '-1' to signify not yet selected @todo null?
			edit_selected_matched_shipping_rate: -1,
			// for selected (and 'applied') matched shipping rate FROM modal
			// @todo: might not need this?
			selected_matched_shipping_rate: null,
			// -------
			// ORDER ERRORS
			error_current_order_error: null,
			//----------------
			// BOOLEANS TO SET IF MODALS FOR EDITING ITEMS ARE OPEN/CLOSED + first load, etc
			is_add_products_modal_open: false,
			is_order_saveable_modal_open: false,
			is_order_publishable_modal_open: false,
			// --------------
			is_current_edit_order_line_item_discount_modal_open: false,
			is_edit_whole_order_discount_modal_open: false,
			is_edit_whole_order_shipping_modal_open: false,
			is_edit_whole_order_taxes_modal_open: false,

			// OTHER BOOLEANS
			// first page load, no initial data set
			is_first_load: true,
			// if to show custom shipping fee input (in modal)
			is_show_custom_shipping_fee: false,
			// if to show custom handling fee inputs(in modal)
			is_show_custom_handling_fee: false,
			// ---------------
			//  for CHECKBOXES toggling custom shipping or handling fees (in modal)
			// @note:keeping it separate from the whole 'order_whole_data.isCustomShippingFee' and 'order_whole_data.isCustomHandlingFe' since those prefer bool int!
			is_custom_shipping_fee: false,
			is_custom_handling_fee: false,
			// ---------------
			is_show_recalculate_shipping_and_taxes_error: false,
			is_need_to_recalculate_shipping_and_taxes: false,
			// @TODO DELETE IF NOT IN USE; DOING A BIT DIFFERENT NOW
			// if shipping fee needs calculating, we change this in order to show the link (htmx)
			is_show_calculate_shipping_link: false,
			// -------------------
			// if to show custom handling fee value select input
			is_show_custom_handling_fee_value: false,
			// @TODO: TRYING TO RESOLVE INTERMITTENT BUG WHEN PRODUCTS WITH VARIANTS CHECKBOXES ARE CLICKED
			// are products (fetched by HTMX) ready to interact with?
			is_ready_for_products_checkboxes: false,
			// ----------
		})
		Alpine.data("InputfieldPWCommerceOrderData", () => ({
			//---------------
			// FUNCTIONS

			/**
			 * Init whole order data sent from server.
			 * as well as other configs.
			 *
			 * @return {void}.
			 */
			initOrderData() {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - initOrderData - 	ProcessWire.config.InputfieldPWCommerceOrder",
				// 	"error",
				// 	ProcessWire.config.InputfieldPWCommerceOrder
				// )
				// init shop currency
				const shopCurrencyConfig = this.getProcessWireOrderShopCurrencyConfig()
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - initOrderData - shopCurrencyConfig",
				// 	"log",
				// 	shopCurrencyConfig
				// )
				this.setShopCurrencyConfig(shopCurrencyConfig)

				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - initOrderData - this.getShopCurrencyConfig()",
				// 	"log",
				// 	this.getShopCurrencyConfig()
				// )

				// init whole order data
				const wholeOrderData = this.getProcessWireOrderConfig()
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - initOrderData - wholeOrderData",
				// 	"info",
				// 	wholeOrderData
				// )
				this.setWholeOrderData(wholeOrderData)

				// init immutable existing order line items
				this.setExistingOrderLineItemsIDs()

				// init data for products already saved on server
				this.initOrderLineItemsProductsData()

				//##########

				// init subtotals and totals
				// @TODO!
				this.initTotals()

				// INIT WATCHERS
				this.initWatchers()
			},

			/**
			 * Init app watchers.
			 * Multiple $watch for various contexts.
			 * @returns {void}.
			 */
			initWatchers() {
				/*
					- INIT WATCHES FOR SEVERAL events that will need shipping to be recalculated.
					1. Changes to order_line_items: add, remove, quantity, discount changes
					2. Change to customer shipping country
					3. Change to customer tax exemption
					4. Change to manual order tax exemption -> not in watch but monitored
					5. change to whole order discount
					6. Change to custom handling fee -> not in watch but monitored
					7. Change to custom shipping fee -> not in watch but monitored

				*/
				// 1. WATCH: order ORDER LINE ITEMS changes
				this.$watch(
					"$store.InputfieldPWCommerceOrderStore.order_whole_data.order_line_items",
					(value) =>
						this.triggerShippingRecalculationAlert(
							value,
							"order_line_items_change"
						)
				)
				// *****
				// 2. WATCH: order CUSTOMER COUNTRY changes
				this.$watch(
					"$store.InputfieldPWCommerceOrderStore.order_whole_data.shippingAddressCountryID",
					(value) =>
						this.triggerShippingRecalculationAlert(value, "country_change")
				)
				// *****
				// 3. WATCH: order CUSTOMER TAX EXEMPTION status changes
				this.$watch(
					"$store.InputfieldPWCommerceOrderStore.order_whole_data.isTaxExempt",
					(value) =>
						this.triggerShippingRecalculationAlert(
							value,
							"tax_exemption_change"
						)
				)
				// *****
				// 4. WATCH: order MANUAL TAX EXEMPTION changes
				// @see this.handleIsChargeTaxesManualExemptionChange()
				// *****
				// 5. WATCH: order DISCOUNT CHANGE changes
				this.$watch(
					"$store.InputfieldPWCommerceOrderStore.order_whole_data.isTaxExempt",
					(value) =>
						this.triggerShippingRecalculationAlert(
							value,
							"whole_order_discount_change"
						)
				)
				// *****
				// 6. WATCH: order CUSTOM  HANDLING  FEE changes
				// @see this.handleCustomHandlingFeeChange()

				// *****
				// 7. WATCH: order CUSTOM SHIPPING FEE changes
				// @see this.handleCustomShippingFeeChange()

				// @TODO DELETE IF NOT IN USE
				// #########################
				// 1. WATCH: order customer country changes
				// this.$watch(
				// 	"$store.InputfieldPWCommerceOrderStore.order_whole_data.shippingAddressCountryID",
				// 	(value) =>
				// 		this.triggerOrderCalculateTaxesAndShipping(value, "country_change")
				// )
				//------------
				// 2. WATCH: order customer tax exemption changes
				// this.$watch(
				// 	// "$store.InputfieldPWCommerceOrderStore.client.is_customer_tax_exempt",// @TODO: DELETE WHEN
				// 	"$store.InputfieldPWCommerceOrderStore.order_whole_data.isTaxExempt",
				// 	(value) =>
				// 		this.triggerOrderCalculateTaxesAndShipping(
				// 			value,
				// 			"tax_exemption_change"
				// 		)
				// )
				//------------
				// @TODO DELETE IF NO LONGER IN USE
				// 3. WATCH: order subtotal changes
				// @note: without taxes or shipping costs! we use this to trigger server calculation of shipping and/or taxes!
				// this.$watch(
				// 	"$store.InputfieldPWCommerceOrderStore.order_whole_data.client.client_subtotal_after_discounts_applied_without_shipping_and_taxes_amount",
				// 	(value) =>
				// 		this.triggerOrderCalculateTaxesAndShipping(
				// 			value,
				// 			"order_subtotal_change"
				// 		)
				// )
				//------------
				// 4. WATCH: charge taxes on order
				// this.$watch(
				// 	"$store.InputfieldPWCommerceOrderStore.order_whole_data.client.client_charge_taxes",
				// 	(value) =>
				// 		this.triggerOrderCalculateTaxesAndShipping(
				// 			value,
				// 			"order_charge_taxes_change"
				// 		)
				// )
				//------------
			},

			triggerShippingRecalculationAlert(newValue, change_type) {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - triggerShippingRecalculationAlert - newValue",
				// 	"info",
				// 	newValue
				// )
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - triggerShippingRecalculationAlert - change_type",
				// 	"warn",
				// 	change_type
				// )

				this.setStoreValue("is_need_to_recalculate_shipping_and_taxes", true)
				// also need to nullify selected matched shipping rate and totals
				this.resetShippingTaxesAndTotalsValues()

				// -----

				// >>>>>>>>>> @DEBUG <<<<<<<

				// @TODO DELETE WHEN DONE

				let changed
				if (change_type === "order_line_items_change") {
					changed = this.getAllOrderLineItems()
				} else if (change_type === "country_change") {
					changed = this.getWholeOrderDataValue("shippingAddressCountry")
					// changed = this.getWholeOrderData()
				} else if (change_type === "tax_exemption_change") {
					changed = this.getWholeOrderDataValue("isTaxExempt")
				} else if (change_type === "manual_tax_exemption_change") {
					changed = this.getWholeOrderDataValue("isChargeTaxesManualExemption")
				} else if (change_type === "whole_order_discount_change") {
					changed = this.getWholeOrderDataValue("discountAmount")
				} else if (change_type === "custom_handling_fee_change") {
					changed = this.getWholeOrderDataValue("handlingFeeAmount")
				} else if (change_type === "custom_shipping_fee_change") {
					changed = this.getWholeOrderDataValue("shippingFee")
				}

				// ------------
				if (changed) {
					// PWCommerceCommonScripts.debugger(
					// 	"InputfieldPWCommerceOrder - triggerShippingRecalculationAlert - changed",
					// 	"log",
					// 	changed
					// )
				}
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - triggerShippingRecalculationAlert - this.getWholeOrderData()",
				// 	"error",
				// 	this.getWholeOrderData()
				// )
			},

			// called after trigger shipping recalculation is affected
			// to nullify selected shipping rate, subtotalPrice and totalPrice
			resetShippingTaxesAndTotalsValues() {
				// nullify handling fee ONLY IF NOT CUSTOM!
				if (!this.checkIsUseCustomHandlingFee()) {
					this.setStoreValue("temporary_handling_fee_amount", 0)
				}

				// nullify shipping fee ONLY IF NOT CUSTOM!
				if (!this.checkIsUseCustomShippingFee()) {
					this.setStoreValue("temporary_shipping_fee_amount", 0)
				}

				// nullify sub-total price
				this.setStoreValue("temporary_subtotal_price", 0)
				// nullify total price
				this.setStoreValue("temporary_total_price", 0)
			},

			initTotals() {
				// @TODO: WE currently save these in temporary properties due to infinite loop issues re $watch. @see separate notes
				const wholeOrderData = this.getWholeOrderData()
				const totalPrice = wholeOrderData.totalPrice
				const shippingAndHandlingTotal =
					wholeOrderData.orderShippingFeePlusHandlingFeeAmountTotal
				const subtotalPrice = totalPrice - shippingAndHandlingTotal
				// handling fee
				this.setStoreValue(
					"temporary_handling_fee_amount",
					wholeOrderData.handlingFeeAmount
				)
				// shipping fee
				this.setStoreValue(
					"temporary_shipping_fee_amount",
					wholeOrderData.shippingFee
				)
				// subtotal
				this.setStoreValue("temporary_subtotal_price", subtotalPrice)
				// total
				this.setStoreValue("temporary_total_price", totalPrice)
			},

			/**
			 * Init data for products already added (saved on server) as order line items.
			 * @return {void}.
			 */
			async initOrderLineItemsProductsData() {
				this.setAllOrderLineItems(this.getProcessWireOrderLineItemsConfig())

				// ### INIT ON LOAD VALUES ###
				// set on-load  OVERALL TOTALS (discounts, costs, etc)
				await this.setOverallTotals().then(() => {
					// @note: need a bit of a delay otherwise API call is fired by triggerOrderCalculateTaxesAndShipping() almost immediately
					// this.setIsFirstLoad(false);
					// @TODO: TIME OK? TOO SOON?
					// indicate app is ready to monitor changes that will trigger taxes and/or shipping recalculations
					setTimeout(() => {
						this.setIsFirstLoad(false)
					}, 150)
				})
			},

			/**
			 * Init data for found products from search query.
			 * Search is via htmx.
			 * @param {array} data Data to set for found products.
			 * @return {void}.
			 */
			initProductSearchResultsData(data) {
				this.setOrderProductsSearchResults(data.order_products_search_results)
			},

			//~~~~~~~~~~~~~~~~~

			/**
			 * Set a store property value.
			 * @param any value Value to set in store.
			 * @return {void}.
			 */
			setStoreValue(property, value) {
				this.$store.InputfieldPWCommerceOrderStore[property] = value
			},

			/**
			 * Set shop currency config to given value.
			 *
			 * @param object value The value to set for shop currency config.
			 * @return {void}.
			 */
			setShopCurrencyConfig(value) {
				this.setStoreValue("shop_currency_config", value)
			},

			/**
			 * Set whole order data to given value.
			 *
			 * @param object value The value to set for whole order data.
			 * @return {void}.
			 */
			setWholeOrderData(value) {
				// add the client only object
				value.client = this.getClientOnlyOrderObject()
				// then set to store
				this.setStoreValue("order_whole_data", value)
			},

			/**
			 *Set the value of a given whole-order-only property.
			 * @param string property Whole-order property whose value to set.
			 * @param any value Value to set to property.
			 * @return {void}.
			 */
			setWholeOrderValue(property, value) {
				// clone current wholeOrderData object
				const wholeOrderData = {
					...this.getStoreValue("order_whole_data"),
				}
				// update given property
				wholeOrderData[property] = value
				// set back to store
				this.setStoreValue("order_whole_data", wholeOrderData)
			},

			// order_existing_line_items_ids

			/**
			 * Set the IDs of existing order line items currently saved on the server.
			 *
			 * We get the values via $refs.
			 *
			 * @return {void}.
			 */
			setExistingOrderLineItemsIDs() {
				const existingOrderLineItemsIDsCSV =
					this.$refs.pwcommerce_order_existing_line_items
				if (existingOrderLineItemsIDsCSV) {
					// @note: for consistent use down the line, we cast the strings to integers
					const existingOrderLineItemsIDs = existingOrderLineItemsIDsCSV.value
						.split(",")
						.map((id) => parseInt(id))
					// ------------
					this.setStoreValue(
						"order_existing_line_items_ids",
						existingOrderLineItemsIDs
					)
				}
			},

			/**
			 * Set all the order items for this order to given value.
			 *
			 * @param array value The value to set for all order line items.
			 * @return {void}.
			 */
			setAllOrderLineItems(value) {
				this.setStoreValue("order_line_items", value)
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - setAllOrderLineItems - value",
				// 	"info",
				// 	value
				// )
			},

			/**
			 * Set results from products search resutls to order results.
			 *
			 * @param array value The value to set for all order line items.
			 * @return {void} void.
			 */
			setOrderProductsSearchResults(value) {
				this.setStoreValue("order_products_search_results", value)
			},

			/**
			 * Set the currently selected product with variants in the modal for adding products as order line items to given value.
			 *
			 * @param array value The value to set for all checked products with variants.
			 * @return {void}.
			 */
			setModalCheckedProductsWithVariants(value) {
				this.setStoreValue("modal_checked_products_with_variants", value)
			},

			/**
			 * Set the currently selected product variants in the modal for adding products as order line items to given value.
			 *
			 * @param array value The value to set for all checked product variants.
			 * @return void
			 */
			setModalCheckedProductVariants(value) {
				this.setStoreValue("modal_checked_variants", value)
			},

			/**
			 * Set the currently selected product without variants in the modal for adding products as order line items to given value.
			 *
			 * @param array value The value to set for all checked products without variants.
			 * @return {void}.
			 */
			setModalCheckedProductsWithoutVariants(value) {
				this.setStoreValue("modal_checked_products_without_variants", value)
			},

			/**
			 *Set the value of a given client-side-only property.
			 * @param string property Client property whose value to set.
			 * @param any value Value to set to property.
			 * @return {void}.
			 */
			setClientOnlyOrderValue(property, value) {
				// clone current wholeOrderData object
				const wholeOrderData = {
					...this.getStoreValue("order_whole_data"),
				}
				// update given client property
				wholeOrderData.client[property] = value
				// set back to store
				this.setStoreValue("order_whole_data", wholeOrderData)
			},

			/**
			 * Set the in-edit whole-order discount object.
			 * @param object value The value to set for whole order discount.
			 * @return {void} void.
			 */
			setEditWholeOrderDiscount(value) {
				// @TODO IF STILL USING THIS, IT HAS TO CHANGE AS WE NOW MODEL DIRECTLY AND NOT IN A MODAL!
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - setEditWholeOrderDiscount - value",
				// 	"log",
				// 	value
				// )
				this.setStoreValue("edit_whole_order_discount", value)
			},

			/**
			 *Set the order line item whose discount will be edited.
			 * @param object value The value to set for the current edit order line item discount.
			 * @return {void}.
			 */
			setCurrentEditOrderLineItemDiscount(value) {
				this.setStoreValue("edit_current_order_line_item_discount", value)
			},

			/**
			 * Set the in-edit order taxes to given value.
			 *
			 * @param object value The value to set for order taxes.
			 * @return {void} void.
			 */
			setEditOrderTaxes(value) {
				this.setStoreValue("edit_whole_order_taxes", value)
			},

			/**
			 * Set the in-edit value of a given order taxes property.
			 * @param string property Order taxes property whose value to set.
			 * @param any value Value to set to property.
			 * @return {void}.
			 */
			setEditOrderTaxesValue(property, value) {
				// clone current wholeOrderTaxes object
				const wholeOrderTaxes = {
					...this.getStoreValue("edit_whole_order_taxes"),
				}
				// update given property
				wholeOrderTaxes[property] = value
				// set back to store
				this.setStoreValue("edit_whole_order_taxes", wholeOrderTaxes)
			},

			/**
			 * Set the in-edit order shipping to given value.
			 *
			 * @param object value The value to set for order shipping.
			 * @return {void}.
			 */
			setEditOrderShipping(value) {
				this.setStoreValue("edit_whole_order_shipping", value)
			},

			/**
			 * Set the in-edit value of a given order shipping property.
			 * @param string property Order shipping property whose value to set.
			 * @param any value Value to set to property.
			 * @return {void}.
			 */
			setEditOrderShippingValue(property, value) {
				// clone current wholeOrderShipping object
				const wholeOrderShipping = {
					...this.getStoreValue("edit_whole_order_shipping"),
				}
				// update given property
				wholeOrderShipping[property] = value
				// set back to store
				this.setStoreValue("edit_whole_order_shipping", wholeOrderShipping)
			},

			/**
			 * Set the checkbox state of the checkboxes in edit shipping.
			 * These are for either custom shipping or custom handling fee.
			 * @param {string} context Custom Shipping Fee or Handling Fee.
			 * @return {void} void..
			 */
			setEditShippingCustomFeeCheckboxes(context) {
				const isCustomFeeValue = this.getWholeOrderDataValue(context)
				// @note: in the modal, this $refs is not working; let's use vanilla js instead!
				// const id =
				// 	context == "isCustomShippingFee"
				// 		? "pwcommerce_order_is_custom_shipping_fee_checkbox"
				// 		: "pwcommerce_order_is_custom_handling_fee_checkbox"
				let id, isCustomFeeProp
				if (context == "isCustomShippingFee") {
					// custom shipping fee
					id = "pwcommerce_order_is_custom_shipping_fee_checkbox"
					isCustomFeeProp = "is_custom_shipping_fee"
				} else {
					// custom handling fee
					id = "pwcommerce_order_is_custom_handling_fee_checkbox"
					isCustomFeeProp = "is_custom_handling_fee"
				}

				const isCustomFeeCheckbox = document.getElementById(id)
				const isCustomFeeBoolValue = isCustomFeeValue ? true : false

				if (isCustomFeeCheckbox) {
					isCustomFeeCheckbox.checked = isCustomFeeBoolValue
				}
				// also set custom fee property for modelling of above checkbox
				this.setStoreValue(isCustomFeeProp, isCustomFeeBoolValue)
			},

			setEditShippingIsShowCustomFee() {
				// set initial show/hide value for custom shipping fee input
				const isShowCustomShippingFee = this.getWholeOrderDataValue(
					"isCustomShippingFee"
				)
					? true
					: false
				//---------
				this.setStoreValue(
					"is_show_custom_shipping_fee",
					isShowCustomShippingFee
				)
			},

			setEditShippingIsShowCustomHandlingFee() {
				// set initial show/hide value for custom handling fee inputs
				const isShowCustomHandlingFee = this.getWholeOrderDataValue(
					"isCustomHandlingFee"
				)
					? true
					: false
				//---------
				this.setStoreValue(
					"is_show_custom_handling_fee",
					isShowCustomHandlingFee
				)

				// in addition, set initial show/hide value for custom handling fee value (it depends on the custom handling fee type saved/selected)
				const handlingFeeType = this.getWholeOrderDataValue("handlingFeeType")

				const isShowCustomHandlingFeeValue =
					handlingFeeType === "none" ? false : true
				this.setStoreValue(
					"is_show_custom_handling_fee_value",
					isShowCustomHandlingFeeValue
				)
			},

			// set selected and APPLIED matched shipping rate for saving with order
			setSelectedMatchedShippingRate() {
				const editSelectedMatchedShippingRate = this.getStoreValue(
					"edit_selected_matched_shipping_rate"
				)
				if (editSelectedMatchedShippingRate == -1) {
					return
				}

				this.setStoreValue(
					"selected_matched_shipping_rate",
					editSelectedMatchedShippingRate
				)
			},

			// FOR MODAL SHIPPING EDIT, set curenty selected matched shipping rate to the matching RADIO BUTTON
			setEditSelectedMatchedShippingRate() {
				// @TODO HERE NEED TO SELECT LAST SAVED ADD SET AS CURRENTLY SELECTED VALUE IF RADIOS ARE PRESENT! HELPFUL IN CASE AN EDIT WAS CANCELLED!
				// NEED TO GET THE RADIO WITH THE ID 'pwcommerce_order_matched_shipping_rates_choices_1234' WHERE 1234 IS THE ID OF THE SELECTED SHIPPING RATE FOUND AT: 'selected_matched_shipping_rate'
				// ------
				const selectedMatchedShippingRate = this.getStoreValue(
					"selected_matched_shipping_rate"
				)
				const id = `pwcommerce_order_matched_shipping_rates_choices_${selectedMatchedShippingRate}`
				const editSelectedMatchedShippingRateElement =
					document.getElementById(id)
				if (editSelectedMatchedShippingRateElement) {
					editSelectedMatchedShippingRateElement.checked = true
				} else {
					// no currently selected AND APPLIED matched shipping rate
					// deselect all
					const editSelectedMatchedShippingRateElements =
						document.querySelectorAll(
							".pwcommerce_order_matched_shipping_rates_choices"
						)
					// PWCommerceCommonScripts.debugger(
					// 	"InputfieldPWCommerceOrder - setEditSelectedMatchedShippingRate - editSelectedMatchedShippingRateElements",
					// 	"info",
					// 	editSelectedMatchedShippingRateElements
					// )
					if (editSelectedMatchedShippingRateElements) {
						for (const editSelectedMatchedShippingRateElement of editSelectedMatchedShippingRateElements) {
							editSelectedMatchedShippingRateElement.checked = false
						}
					}
				}
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - setEditSelectedMatchedShippingRate - editSelectedMatchedShippingRateElement",
				// 	"info",
				// 	editSelectedMatchedShippingRateElement
				// )
			},

			/**
			 * Set the checkbox state of the manual charge taxes in edit whole order taxes modal.
			 * @return {void} void..
			 */
			setEditWholeOrderTaxCheckbox() {
				// @note: always have problems with checkboxes! binding is erratic! so we set ourselves here
				const editOrderChargeTaxesCheckboxElement = document.getElementById(
					"pwcommerce_order_is_charge_taxes_manual_exemption_checkbox"
				)
				if (editOrderChargeTaxesCheckboxElement) {
					const isChargeTaxesManualExemption = this.getWholeOrderDataValue(
						"isChargeTaxesManualExemption"
					)
					editOrderChargeTaxesCheckboxElement.checked =
						parseInt(isChargeTaxesManualExemption) === 1 ? false : true
				}
			},

			/**
			 * Set the first load property to the given value.
			 *
			 * @param bool value The value to set for first loadtaxes.
			 * @returns bool
			 */
			setIsFirstLoad(value) {
				this.setStoreValue("is_first_load", value)
			},

			//++++++++++++++++++

			// this sets/inits overall order totals when changes occur to the order
			// totals are set for discounts, shipping, order cost, etc
			async setOverallTotals() {
				//**********
				// set ORDER TOTAL LINE ITEMS QUANTITY in order
				this.setClientOnlyOrderValue(
					"client_total_line_items_quantity",
					this.getOrderQuantity()
				)

				//**********
				// set ORDER TOTAL COSTS WITHOUT DISCOUNTS, SHIPPING or TAXES

				const totalOrderCostWithoutDiscountsShippingOrTaxes =
					this.formatValueAsCurrencyWithoutSymbol(
						this.getTotalOrderCostWithoutDiscountsShippingOrTaxes()
					)

				//-------
				this.setClientOnlyOrderValue(
					"client_order_total_cost_without_discounts_shipping_or_taxes",
					totalOrderCostWithoutDiscountsShippingOrTaxes
				)

				//**********
				// set ORDER LINE ITEMS ONLY DISCOUNTS TOTALS

				const totalOrderLineItemsDiscountAmount =
					this.getTotalOrderLineItemsDiscountAmount()
				//------
				this.setClientOnlyOrderValue(
					"client_total_line_items_discount_amount",
					totalOrderLineItemsDiscountAmount
				)

				//**********
				// set OVERALL ORDER DISCOUNTS TOTALS (whole order + line items discounts amount)

				//@note: get fresh/updated values of whole-order-discount-amount
				const totalWholeOrderOnlyDiscountAmount =
					this.getWholeOrderOnlyDiscountAmount(this.getWholeOrderData())

				//---------
				const overallOrderPlusLineItemsDiscountsAmount =
					this.formatValueAsCurrencyWithoutSymbol(
						totalOrderLineItemsDiscountAmount +
							totalWholeOrderOnlyDiscountAmount
					)
				this.setClientOnlyOrderValue(
					"client_order_plus_line_items_discount_amount",
					overallOrderPlusLineItemsDiscountsAmount
				)

				//**********

				// set ORDER TOTAL NET COSTS WITH DISCOUNTS APPLIED (but still without shipping or taxes)
				// @TODO
				const orderSubtotalWithoutShippingOrTaxes =
					this.formatValueAsCurrencyWithoutSymbol(
						totalOrderCostWithoutDiscountsShippingOrTaxes -
							overallOrderPlusLineItemsDiscountsAmount
					)
				this.setClientOnlyOrderValue(
					"client_subtotal_after_discounts_applied_without_shipping_and_taxes_amount",
					orderSubtotalWithoutShippingOrTaxes
				)
			},

			//~~~~~~~~~~~~~~~~~

			addSelectedProductsToOrder() {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - addSelectedProductsToOrder - this.getOrderProductsSearchResults()",
				// 	"info",
				// 	this.getOrderProductsSearchResults()
				// )
				const productsToAddToOrder = []
				//----------
				for (const product_item of this.getOrderProductsSearchResults()) {
					// if item already added skip it
					let is_ok_to_add = false
					if (
						this.getAllOrderLineItems().some(
							(alreadySelectedProduct) =>
								alreadySelectedProduct.productID === product_item.productID
						)
					)
						continue

					//---------------
					// we don't add products that have variants (parents) themselves; skip
					if (product_item.has_variants) {
						continue
					}
					// variant
					else if (parseInt(product_item.isVariant)) {
						// check if variant is addable/currently checked
						if (
							this.getModalCheckedProductVariants().includes(
								product_item.productID
							)
						) {
							// @todo: do we need to clone it?
							productsToAddToOrder.push(product_item)
							is_ok_to_add = true
						}
					}
					// product without variants
					else {
						// check if product without variants is addable/currently checked
						if (
							this.getModalCheckedProductsWithoutVariants().includes(
								product_item.productID
							)
						) {
							// @todo: do we need to clone it?
							productsToAddToOrder.push(product_item)
							is_ok_to_add = true
						}
					}

					// if we got here, add product to pool of new products as well
					if (is_ok_to_add) {
						this.addProductIDToOrderNewLineItems(product_item.productID)
						// PWCommerceCommonScripts.debugger(
						// 	"InputfieldPWCommerceOrder - addSelectedProductsToOrder - product_item",
						// 	"info",
						// 	product_item
						// )
					}
				} // end for of loop

				// FINAL PRODUCTS TO ADD TO ORDER order_line_items
				const finalProductsToAddToOrder =
					this.getAllOrderLineItems().concat(productsToAddToOrder)
				this.setAllOrderLineItems(finalProductsToAddToOrder)
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - addSelectedProductsToOrder - finalProductsToAddToOrder",
				// 	"log",
				// 	finalProductsToAddToOrder
				// )
				// ALSO CHANGE OVERALL ORDER TOTALS THAT DEPEND ON ABOVE
				this.setOverallTotals()

				// @TODO DELETE WHEN DONE - NOW WATCHING
				// FINALLY, SIGNAL THAT SHIPPING WILL NEED (RE) CALCULATING
				// a 'shipping-needs-calculating' event has occured
				// this.setShippingNeedsCalculating(true)

				//--------------------
				const showHighlight = productsToAddToOrder.length > 0 ? "show" : null
				// @todo: make sure no race condition here!
				this.resetSelectedProductsAndClose(showHighlight)
			},

			/**
			 * Add a product ID to the collection of order new line items.
			 * @param {integer} value The product id to add.
			 * @return {void}.
			 */
			addProductIDToOrderNewLineItems(value) {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - addProductIDToOrderNewLineItems - value",
				// 	"error",
				// 	value
				// )
				// clone existing
				const orderNewLineItems = [
					...this.getStoreValue("order_new_line_items"),
				]
				orderNewLineItems.push(value)
				// add to store
				this.setStoreValue("order_new_line_items", orderNewLineItems)
			},

			/**
			 * Add a line item ID to the collection of deleted order new line items.
			 *
			 * @note Only applies if this is a currently existing order line item on the server!.
			 *
			 * @param {integer} order_line_item_id The line item id to add to collection of deleted items.
			 * @return {void}.
			 */
			addExistingOrderLineItemToDeletedLineItems(order_line_item_id) {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - addExistingOrderLineItemToDeletedLineItems - order_line_item_id",
				// 	"info",
				// 	order_line_item_id
				// )

				const orderLineItemID = parseInt(order_line_item_id)
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - addExistingOrderLineItemToDeletedLineItems - orderLineItemID",
				// 	"log",
				// 	orderLineItemID
				// )

				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - addExistingOrderLineItemToDeletedLineItems - this.getStoreValue('order_existing_line_items_ids')",
				// 	"error",
				// 	this.getStoreValue("order_existing_line_items_ids")
				// )

				// first check if the deleted order line item is an existing item on the server
				if (this.isExistingOrderLineItem(orderLineItemID)) {
					// get and clone current collection of deleted line items
					const orderDeletedLineItems = [
						...this.getStoreValue("order_deleted_line_items"),
					]
					// if this order line item is not yet added, add it to the collection
					if (!orderDeletedLineItems.includes(orderLineItemID)) {
						orderDeletedLineItems.push(orderLineItemID)
						// update store
						this.setStoreValue(
							"order_deleted_line_items",
							orderDeletedLineItems
						)
						// PWCommerceCommonScripts.debugger(
						// 	"InputfieldPWCommerceOrder - addExistingOrderLineItemToDeletedLineItems - orderDeletedLineItems - ADDED TO DELETED ITEMS!",
						// 	"info",
						// 	orderDeletedLineItems
						// )
					} else {
						// PWCommerceCommonScripts.debugger(
						// 	"InputfieldPWCommerceOrder - addExistingOrderLineItemToDeletedLineItems - orderLineItemID - ALREADY ADDED TO DELETE POOL!: SKIP IT",
						// 	"error",
						// 	orderLineItemID
						// )
					}
				} else {
					// PWCommerceCommonScripts.debugger(
					// 	"InputfieldPWCommerceOrder - addExistingOrderLineItemToDeletedLineItems - orderLineItemID - DOES NOT CURRENTLY EXIST ON THE SERVER: SKIP IT",
					// 	"error",
					// 	orderLineItemID
					// )
				}

				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - addExistingOrderLineItemToDeletedLineItems - this.getStoreValue('order_deleted_line_items')",
				// 	"warn",
				// 	this.getStoreValue("order_deleted_line_items")
				// )
			},

			/**
			 * Checks if the  given order line item id a currently existing order line item on the server.
			 * @param {integer} order_line_item_id
			 * @return bool Whether the item currently exists on the server or not.
			 */
			isExistingOrderLineItem(order_line_item_id) {
				return this.getStoreValue("order_existing_line_items_ids").includes(
					parseInt(order_line_item_id)
				)
			},

			/**
			 * Remove a line item ID from the collection of order new line items.
			 * @note: new order line item's IDs equal their productID as still unsaved!
			 * @param {integer} value The line item id to remove.
			 * @return {void}.
			 */
			removeProductIDFromOrderNewLineItems(order_line_item_id) {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - removeProductIDFromOrderNewLineItems - order_line_item_id",
				// 	"info",
				// 	order_line_item_id
				// )
				// clone existing new order line items
				let orderNewLineItems = [...this.getStoreValue("order_new_line_items")]
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - setAllOrderLineItems - orderNewLineItems",
				// 	"log",
				// 	orderNewLineItems
				// )

				// if no new items added yet, nothing to do; return early
				if (!orderNewLineItems.length) {
					// PWCommerceCommonScripts.debugger(
					// 	"InputfieldPWCommerceOrder - setAllOrderLineItems - NO NEW ITEMS ADDED YET! GO BACK! - orderNewLineItems.length",
					// 	"error",
					// 	orderNewLineItems.length
					// )
					return
				}

				// remove the DELETED new order line items from the collection of new order line items
				orderNewLineItems = orderNewLineItems.filter(
					(id) => parseInt(id) !== parseInt(order_line_item_id)
				)
				// set back to store the updated orderNewLineItems
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - setAllOrderLineItems - orderNewLineItems - NOW UPDATED",
				// 	"error",
				// 	orderNewLineItems
				// )
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - setAllOrderLineItems - this.getStoreValue('order_new_line_items') - IN STORE BEFORE WE UPDATE STORE",
				// 	"info",
				// 	this.getStoreValue("order_new_line_items")
				// )

				this.setStoreValue("order_new_line_items", orderNewLineItems)

				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - setAllOrderLineItems - this.getStoreValue('order_new_line_items') - IN STORE AFTER WE UPDATE STORE",
				// 	"info",
				// 	this.getStoreValue("order_new_line_items")
				// )
			},

			//~~~~~~~~~~~~~~~~~

			/**
			 * Update the whole-order discount after an edit in the discounts edit modal.
			 * @return {void} void.
			 */
			updateWholeOrderDiscount() {
				const wholeOrderEditedDiscount = this.getEditWholeOrderDiscount()
				//---------
				// @TODO: CONFIRM!
				for (const property of [
					"discountType",
					"discountValue",
					"discountAmount",
				]) {
					const value = wholeOrderEditedDiscount[property]
					this.setWholeOrderValue(property, value)
				}

				//-----------------
				// ALSO CHANGE OVERALL ORDER TOTALS THAT DEPEND ON ABOVE
				this.setOverallTotals()

				// FINALLY, SIGNAL THAT SHIPPING WILL NEED (RE) CALCULATING
				// a 'shipping-needs-calculating' event has occured
				this.setShippingNeedsCalculating(true)

				// THEN RESET VALUES AND CLOSE MODAL
				// finally, reset values and close modal
				this.resetEditWholeOrderDiscountAndClose("show")
			},

			updateSingleLineItemDiscount() {
				// set applied discount VALUES to line item
				// ** @note: we simply replace the 'older' line item **

				const currentEditOrderLineItem =
					this.getCurrentEditOrderLineItemDiscount()
				// first, find its index in order_line_items
				const orderLineItemID = currentEditOrderLineItem.id
				const orderLineItemToReplaceIndex =
					this.findOrderLineItemIndex(orderLineItemID)
				// then replace it
				// @TODO: RACE CONDITION HERE?
				const insertOrderLineItem = {
					...currentEditOrderLineItem,
				}
				this.replaceOrderLineItem(
					orderLineItemToReplaceIndex,
					insertOrderLineItem
				)

				//-----------------
				// ALSO CHANGE OVERALL ORDER TOTALS THAT DEPEND ON ABOVE
				this.setOverallTotals()

				// FINALLY, SIGNAL THAT SHIPPING WILL NEED (RE) CALCULATING
				// a 'shipping-needs-calculating' event has occured
				this.setShippingNeedsCalculating(true)

				// THEN RESET VALUES AND CLOSE MODAL
				// finally, reset values and close modal
				this.resetEditSingleLineItemDiscountAndClose("show")
			},

			updateWholeOrderTaxes() {
				// @TODO MOVE TO OWN METHOD???
				const editOrderChargeTaxesCheckboxElement = document.getElementById(
					"pwcommerce_order_is_charge_taxes_manual_exemption_checkbox"
				)

				if (editOrderChargeTaxesCheckboxElement) {
					// @note: using bool int here to match server bool int
					const isChargeTaxesManualExemption =
						editOrderChargeTaxesCheckboxElement.checked ? 0 : 1
					this.setWholeOrderValue(
						"isChargeTaxesManualExemption",
						isChargeTaxesManualExemption
					)
					//###############
					// @todo: with above edits, not sure we still need this client-only value?
					//this.setClientOnlyOrderValue("client_charge_taxes", value)
					// THEN RESET VALUES AND CLOSE  MODAL
					this.resetEditWholeOrderTaxesAndClose("show")
				}
				// @TODO ELSE???

				//##########
				// @TODO DELETE WHEN DONE
				// @todo: now changes! we want this value for a hidden input; instead, here add 'edit_charge_taxes' or 'edit_modal_charge_taxes' or 'temporary_in_edit_charge_taxes'
				//const value = this.getEditedOrderTaxes().charge_taxes
				// @TODO WE THEN HAVE TO UPDATE THE REAL VALUE TO SAVE IN HIDDEN INPUT 'charge_taxes'
				// @todo alternatively, don't model checkbox; just add ref and find ischecked status on apply!
			},

			updateOrderShipping() {
				const orderEditedShipping = this.getEditOrderShipping()
				//---------
				// @TODO: CONFIRM!
				for (const property of [
					"handlingFeeType",
					"handlingFeeValue",
					"handlingFeeAmount",
					"shippingFee",
					"isCustomHandlingFee",
					"isCustomShippingFee",
				]) {
					// let value = orderEditedShipping[property]
					let value
					// ** @note: if the property is for custom shipping or handling fee, we values from the store property that their related checkboxes model **
					if (property === "isCustomShippingFee") {
						// custom shipping bool
						value = this.getStoreValue("is_custom_shipping_fee") ? 1 : 0
					} else if (property === "isCustomHandlingFee") {
						// custom handling bool
						value = this.getStoreValue("is_custom_handling_fee") ? 1 : 0
					} else {
						// other shipping property
						value = orderEditedShipping[property]
					}
					//------------------
					this.setWholeOrderValue(property, value)
				}

				// ----------
				// SIGNAL THAT SHIPPING MIGHT NEED (RE) CALCULATING
				// a 'shipping-needs-calculating' event has occured
				// if no longer using a custom shipping fee, will need to calculate shipping!
				// @note: we want the 'opposite' of is_custom_shipping_fee!!
				const isCustomShippingFee = !this.getStoreValue(
					"is_custom_shipping_fee"
				)
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - updateOrderShipping - isCustomShippingFee",
				// 	"info",
				// 	isCustomShippingFee
				// )
				// @TODO NEEDS TO BE SPECIFIC TO CUSTOM SHIPPING; E.G. IF RATE APPLIED, NEED TO GRAY OUT LINK!
				this.setShippingNeedsCalculating(isCustomShippingFee)

				// --------
				// SET SELECTED MATCHED SHIPPING RATE from 'edit_selected_matched_shipping_rate'
				this.setSelectedMatchedShippingRate()
				// @TODO: THEN RESET VALUES AND CLOSE  MODAL
				this.resetEditOrderShippingAndClose("show")
			},

			//~~~~~~~~~~~~~~~~~

			resetSelectedProductsAndClose(show_highlight) {
				// reset modal collections
				// @todo: do we need to reset checked status as well?
				this.setModalCheckedProductVariants([])
				this.setModalCheckedProductsWithVariants([])
				this.setModalCheckedProductsWithoutVariants([])
				// ----------
				// reset calculate shipping and taxes link
				//---------
				this.setOrderProductsSearchResults([])
				// close modal
				this.handleCloseModal("is_add_products_modal_open")
				// remove found items markup
				const searchResultsContainer = document.getElementById("search-results")
				if (searchResultsContainer) searchResultsContainer.innerHTML = ""
				// clear search box
				const searchBox = document.getElementById(
					"pwcommerce_order_search_products"
				)
				if (searchBox) searchBox.value = ""

				if (show_highlight === "show") {
					// tell user action happened
					this.showHighlight(".pwcommerce_order_line_item")
				}
			},

			// reset after whole order discount edit
			resetEditWholeOrderDiscountAndClose(show_highlight) {
				// empty 'edit_whole_order_discount'
				this.setEditWholeOrderDiscount({})
				// then close modal
				this.handleCloseModal("is_edit_whole_order_discount_modal_open")
				if (show_highlight === "show") {
					// tell user action happened
					this.showHighlight(".InputfieldPWCommerceOrder")
				}
			},

			// reset after single line item discount edit
			resetEditSingleLineItemDiscountAndClose(show_highlight) {
				this.setCurrentEditOrderLineItemDiscount({})
				// then close modal
				this.handleCloseModal(
					"is_current_edit_order_line_item_discount_modal_open"
				)
				if (show_highlight === "show") {
					// tell user action happened
					this.showHighlight(".pwcommerce_order_line_item")
				}
			},

			resetEditWholeOrderTaxesAndClose(show_highlight) {
				// empty 'edit_whole_order_taxes'
				this.setEditOrderTaxes({})
				// then close modal
				this.handleCloseModal("is_edit_whole_order_taxes_modal_open")
				if (show_highlight === "show") {
					// tell user action happened
					this.showHighlight(".InputfieldPWCommerceOrder")
				}
			},

			resetEditOrderShippingAndClose(show_highlight) {
				// empty 'edit_whole_order_shipping'
				this.setEditOrderShipping({})
				// then close modal
				this.handleCloseModal("is_edit_whole_order_shipping_modal_open")
				if (show_highlight === "show") {
					// tell user action happened
					this.showHighlight(".InputfieldPWCommerceOrder")
				}
			},

			// @TODO REFACTOR IN LATER RELEASES TO COMBINE WITH HANDLER FOR PUBLISHABLE
			resetIsOrderSaveableAndClose() {
				// close modal
				this.handleCloseModal("is_order_saveable_modal_open")
				// scroll 'calculate shipping and taxes' button into view
				const calculateShippingAndTaxesElementID =
					"pwcommerce_order_calculate_shipping_button"
				this.scrollElementToView(calculateShippingAndTaxesElementID)
			},

			resetIsOrderPublishableAndClose() {
				// close modal
				this.handleCloseModal("is_order_publishable_modal_open")
				// scroll 'calculate shipping and taxes' button into view
				const calculateShippingAndTaxesElementID =
					"pwcommerce_order_calculate_shipping_button"
				this.scrollElementToView(calculateShippingAndTaxesElementID)
			},

			scrollElementToView(element_id, custom_scroll_options = null) {
				const scrollElement = document.getElementById(element_id)
				// return early if we didn't find the element to scroll into view
				if (!scrollElement) return
				// -------------
				const defaultScrollOptions = {
					behavior: "smooth",
					block: "center",
					inline: "nearest",
				}
				const scrollOptions = custom_scroll_options
					? custom_scroll_options
					: defaultScrollOptions
				scrollElement.scrollIntoView(scrollOptions)
			},

			//~~~~~~~~~~~~~~~~~

			toggleProductWithVariantSelected(event) {
				const isReadyForProductsCheckboxes = this.getStoreValue(
					"is_ready_for_products_checkboxes"
				)
				// @TODO: TRYING TO SORT OUT BUG BELOW
				if (!isReadyForProductsCheckboxes) {
					return
				}
				//--------------
				// @TODO: MONITOR THIS! SOMETIMES GETTING ERRORS
				const selectedProductWithVariantsID = parseInt(event.target.value)
				const checked = event.target.checked
				const temporaryVariantsArray = []
				for (const product_item of this.getOrderProductsSearchResults()) {
					// we only want the children of this selected product with variants
					if (product_item.parent_product !== selectedProductWithVariantsID) {
						continue
					}
					// for later tracking if to add to order (final selected)
					temporaryVariantsArray.push(product_item.productID)
					// match variants checkboxes checked status to parent product
					this.$refs[`variant_${product_item.productID}`].checked = checked
				}
				//------
				// process IDs of checked variants
				if (checked) {
					// add IDs of variants of checked parent product to collection
					const value = this.getModalCheckedProductVariants().concat(
						temporaryVariantsArray
					)
					this.setModalCheckedProductVariants(value)
				} else {
					// remove IDs of variants of checked parent product from collection
					const value = this.getModalCheckedProductVariants().filter(
						(variantID) => !temporaryVariantsArray.includes(variantID)
					)
					this.setModalCheckedProductVariants(value)
				}
			},

			toggleVariantSelected(event) {
				const isReadyForProductsCheckboxes = this.getStoreValue(
					"is_ready_for_products_checkboxes"
				)
				// @TODO: TRYING TO SORT OUT BUG BELOW
				if (!isReadyForProductsCheckboxes) {
					return
				}
				// @TODO: MONITOR THIS! SOMETIMES GETTING ERRORS
				const selectedVariantID = parseInt(event.target.value)
				const checked = event.target.checked

				const variant = this.getOrderProductsSearchResults().find(
					(product_item) => product_item.productID === selectedVariantID
				)
				const variantParentProductID = variant.parent_product
				//---------
				// add to selected variants
				if (checked) {
					// check the parent as well (even if already checked by another variant sibling)
					this.$refs[`parent_product_${variantParentProductID}`].checked = true
					// add selected variant to selected variants array
					const value = this.getModalCheckedProductVariants().concat([
						selectedVariantID,
					])
					this.setModalCheckedProductVariants(value)
				}
				// remove from selected variants
				else {
					// first, remove the variant from selected variants array
					const value = this.getModalCheckedProductVariants().filter(
						(variantID) => variantID !== selectedVariantID
					)
					this.setModalCheckedProductVariants(value)
					// if variant unselected, we also need to uncheck the parent product unless it  a sibling variant is still selected, in which case we do nothing

					// first, find sibling variants, if any
					let isSiblingVariantSelected = false
					for (const product_item of this.getOrderProductsSearchResults()) {
						// we only want siblings
						if (product_item.parent_product !== variantParentProductID) continue
						// we don't want the current variant itself
						if (product_item.productID === selectedVariantID) continue
						// check if sibling variant still selected
						if (
							this.getModalCheckedProductVariants().includes(
								product_item.productID
							)
						) {
							isSiblingVariantSelected = true
							// at least one sibling still selected; stop checking
							break
						}
					}
					// if no sibling selected, remove/uncheck parent product
					if (!isSiblingVariantSelected) {
						// parent product needs to be unchecked since none of the variants siblings are checked
						this.$refs[
							`parent_product_${variantParentProductID}`
						].checked = false
						// @todo @note: since products with variants are never really added to the order themselves, no need to track their modal checked status
					}
				}
			},

			toggleProductWithoutVariantSelected(event) {
				const selectedProductWithoutVariantsID = parseInt(event.target.value)
				const checked = event.target.checked
				// add to selected products without variants
				if (checked) {
					const value = this.getModalCheckedProductsWithoutVariants().concat([
						selectedProductWithoutVariantsID,
					])
					this.setModalCheckedProductsWithoutVariants(value)
				}
				// remove from selected products without variants
				else {
					const value = this.getModalCheckedProductsWithoutVariants().filter(
						(productID) => productID !== selectedProductWithoutVariantsID
					)
					this.setModalCheckedProductsWithoutVariants(value)
				}
			},

			//~~~~~~~~~~~~~~~~~

			handleOpenOrderAddProductsModal() {
				this.handleOpenModal("is_add_products_modal_open")
				// focus the search box
				const searchBox = document.getElementById(
					"pwcommerce_order_search_products"
				)
				if (searchBox) {
					// @note: need to wait for it to render first
					setTimeout(() => {
						searchBox.focus()
					}, 100)
				}
			},

			// handler for custom event dispatched by HTMX that products (line items) have been found that can be added to order as line items
			// @see @event: pwcommercefoundaddableproducts
			handleFoundAddableProducts(event) {
				const value = event.detail

				// @TODO: TRYING TO SORT OUT THE BUG THAT SOMETIMES SHOWS UP WHEN WE SELECT CHECKBOX OF A PRODUCT WITH VARIANTS
				this.setStoreValue("is_ready_for_products_checkboxes", value)
			},

			// handler for custom event dispatched when order customer country changes
			handleOrderCustomerCountryChange(event) {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - handleOrderCustomerCountryChange - event",
				// 	"error",
				// 	event
				// )
				const customerShippingCountryID = event.detail
					.shipping_address_country_id
					? parseInt(event.detail.shipping_address_country_id)
					: 0
				//-------------
				// set country ID
				this.setWholeOrderValue(
					"shippingAddressCountryID",
					customerShippingCountryID
				)
				// set country Name -> @todo: needed? delete if not!
				this.setWholeOrderValue(
					"shippingAddressCountry",
					event.detail.shipping_address_country
				)

				// @TODO DELETE WHEN DONE - NO LONGER IN USE
				// FINALLY, SIGNAL THAT SHIPPING WILL NEED (RE) CALCULATING
				// a 'shipping-needs-calculating' event has occured
				// this.setShippingNeedsCalculating(true)
			},

			handleCustomHandlingFeeChange(event) {
				const value = event.target.value
				// just to force shipping recalculation notice
				this.triggerShippingRecalculationAlert(
					value,
					"custom_handling_fee_change"
				)
			},

			handleUseCustomHandlingFeeChange(event) {
				this.setWholeOrderValue("isCustomHandlingFee", event.target.checked)
			},

			handleUseCustomShippingFeeChange(event) {
				this.setWholeOrderValue("isCustomShippingFee", event.target.checked)
			},

			handleCustomShippingFeeChange(event) {
				const value = event.target.value
				// just to force shipping recalculation notice
				this.triggerShippingRecalculationAlert(
					value,
					"custom_shipping_fee_change"
				)
			},

			handleIsChargeTaxesManualExemptionChange(event) {
				const isChecked = event.target.checked
				// check boxes usually problematic; we manually set the value in store
				const value = isChecked ? 1 : 0
				// this.setWholeOrderValue(
				// 	"isChargeTaxesManualExemption",
				// 	event.target.checked
				// )
				this.setWholeOrderValue("isChargeTaxesManualExemption", value)

				// force shipping recalculation notice
				this.triggerShippingRecalculationAlert(
					isChecked,
					"manual_tax_exemption_change"
				)
			},

			handleOrderCustomerTaxExemptChange(event) {
				this.setWholeOrderValue("isTaxExempt", event.target.checked)
			},

			handleOrderLineItemsQuantityChange(order_line_item) {
				// @note: QUANTITY has changed: adjust the TOTAL PRICE (TP) OF THIS LINE ITEM
				// @todo: since referencing the object directly, it will change in store as well
				order_line_item.totalPrice =
					order_line_item.quantity * order_line_item.unitPrice

				// ALSO CHANGE DISCOUNT AMOUNT OF THE LINE ITEM if applicable
				if (order_line_item.discountType !== "none") {
					order_line_item.discountAmount =
						this.formatValueAsCurrencyWithoutSymbol(
							this.getOrderLineItemDiscountAmount(order_line_item)
						)
				} else {
					order_line_item.discountValue = 0
				}

				// CHANGE THE TOTAL DISCOUNTED PRICE (NET PRICE) FOR THIS LINE ITEM
				const totalPriceDiscounted =
					order_line_item.totalPrice - order_line_item.discountAmount

				order_line_item.totalPriceDiscounted =
					totalPriceDiscounted < 0
						? 0
						: this.formatValueAsCurrencyWithoutSymbol(totalPriceDiscounted)

				//----------------

				//------------------ TOTALS --------
				// ALSO CHANGE OVERALL ORDER TOTALS THAT DEPEND ON ABOVE
				this.setOverallTotals()
				// @TODO DELETE WHEN DONE - NO LONGER IN USE
				// FINALLY, SIGNAL THAT SHIPPING WILL NEED (RE) CALCULATING
				// a 'shipping-needs-calculating' event has occured
				// this.setShippingNeedsCalculating(true)
			},

			handleRemoveOrderLineItem(order_line_item_id) {
				const removeProductIndex =
					this.findOrderLineItemIndex(order_line_item_id)
				this.removeOrderLineItem(removeProductIndex)
				// ALSO CHANGE OVERALL ORDER TOTALS THAT DEPEND ON ABOVE
				this.setOverallTotals()
				// ALSO REMOVE FROM 'order_new_line_items'!!!
				this.removeProductIDFromOrderNewLineItems(order_line_item_id)
				// ALSO ADD TO DELETED ITEMS IF APPLICABLE
				// @note: only if it was an existing item!
				this.addExistingOrderLineItemToDeletedLineItems(order_line_item_id)
				// FINALLY, SIGNAL THAT SHIPPING WILL NEED (RE) CALCULATING (unless all items have been removed)
				// a 'shipping-needs-calculating' event has occured
				// if we have at least one order line item still in the order, show 'calculate shipping' link
				const isShippingFeeNeedsCalculating = this.getAllOrderLineItems().length
					? true
					: false
				// @TODO DELETE WHEN DONE - NO LONGER IN USE
				// this.setShippingNeedsCalculating(isShippingFeeNeedsCalculating)
			},

			handleCloseModal(property) {
				const propertiesNeedingExtraHandling =
					this.getNamesOfModalPropertiesNeedingExtraHandling()
				if (propertiesNeedingExtraHandling.includes(property)) {
					this.handlePostCloseModalExtras(property)
				}
				this.setStoreValue(property, false)
			},

			handleOpenModal(property) {
				this.setStoreValue(property, true)
			},

			handlePostCloseModalExtras(property) {
				if (
					[
						"is_order_saveable_modal_open",
						"is_order_publishable_modal_open",
					].includes(property)
				) {
					this.setStoreValue("error_current_order_error", null)
					// scroll 'calculate shipping and taxes' button into view
					const calculateShippingAndTaxesElementID =
						"pwcommerce_order_calculate_shipping_button"
					this.scrollElementToView(calculateShippingAndTaxesElementID)
				}
			},

			// @TODO CHECK IF STILL NEEDED?
			handleEditWholeOrderDiscount() {
				// @note: we just clone the whole order data object but we will only work with discount properties.
				const wholeOrderClone = {
					...this.getWholeOrderData(),
				}

				if (wholeOrderClone) {
					this.setEditWholeOrderDiscount(wholeOrderClone)
					//-------------------
					// open modal
					this.handleOpenModal("is_edit_whole_order_discount_modal_open")
				}
			},

			handleEditOrderLineItemDiscount(order_line_item) {
				const itemClone = { ...order_line_item }
				if (itemClone) {
					this.setCurrentEditOrderLineItemDiscount(itemClone)
					this.handleOpenModal(
						"is_current_edit_order_line_item_discount_modal_open"
					)
				}
			},

			// handle WHOLE ORDER discount change
			handleEditWholeOrderDiscountChange() {
				// const editWholeOrderDiscount = this.getEditWholeOrderDiscount()
				const editWholeOrderDiscount = this.getWholeOrderData()

				// no discount selected or 'none' selected or discount value is zero
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - handleEditWholeOrderDiscountChange - editWholeOrderDiscount",
				// 	"log",
				// 	editWholeOrderDiscount
				// )
				// return
				if (
					!editWholeOrderDiscount.discountType ||
					editWholeOrderDiscount.discountType === "none" ||
					!parseFloat(editWholeOrderDiscount.discountValue)
				) {
					editWholeOrderDiscount.discountAmount = 0
					editWholeOrderDiscount.discountValue = 0
				} else {
					editWholeOrderDiscount.discountAmount =
						this.formatValueAsCurrencyWithoutSymbol(
							this.getWholeOrderOnlyDiscountAmount(editWholeOrderDiscount)
						)
				}
				// ------------
				// ALSO CHANGE OVERALL ORDER TOTALS THAT DEPEND ON ABOVE
				this.setOverallTotals()
			},

			// @TODO DELETE WHEN DONE
			// handle WHOLE ORDER discount change
			handleEditWholeOrderDiscountChangeOLD() {
				const editWholeOrderDiscount = this.getEditWholeOrderDiscount()

				if (
					editWholeOrderDiscount.discountType === "none" ||
					!parseFloat(editWholeOrderDiscount.discountValue)
				) {
					editWholeOrderDiscount.discountAmount = 0
					editWholeOrderDiscount.discountValue = 0
				} else {
					editWholeOrderDiscount.discountAmount =
						this.formatValueAsCurrencyWithoutSymbol(
							this.getWholeOrderOnlyDiscountAmount(editWholeOrderDiscount)
						)
				}
			},

			// @TODO THIS NOW CHANGES; WE HANDLE FOR EACH LINE ITEM INLINE USING NEW GUI
			// @TODO DELETE WHEN DONE!

			// handle a SINGLE ORDER LINE ITEM discount change
			handleOrderLineItemDiscountChange(product) {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - handleOrderLineItemDiscountChange - product",
				// 	"info",
				// 	product
				// )

				// @AMEND BELOW TO DEAL WITH ONE LINE ITEM
				// const currentEditOrderLineItem =
				// this.getCurrentEditOrderLineItemDiscount()

				//-----------
				// no discount selected or 'none' selected or discount value is zero
				if (
					!product.discountType ||
					product.discountType === "none" ||
					!parseFloat(product.discountValue)
				) {
					product.discountAmount = 0
					product.discountValue = 0
				} else {
					product.discountAmount = this.formatValueAsCurrencyWithoutSymbol(
						this.getOrderLineItemDiscountAmount(product)
					)
				}
				//============

				const totalPriceDiscounted = product.totalPrice - product.discountAmount

				product.totalPriceDiscounted =
					totalPriceDiscounted < 0
						? 0
						: this.formatValueAsCurrencyWithoutSymbol(totalPriceDiscounted)

				// ------------
				// ALSO CHANGE OVERALL ORDER TOTALS THAT DEPEND ON ABOVE
				this.setOverallTotals()
			},

			handleOrderLineItemDiscountChangeOLD() {
				const currentEditOrderLineItem =
					this.getCurrentEditOrderLineItemDiscount()
				//-----------
				if (
					currentEditOrderLineItem.discountType === "none" ||
					!parseFloat(currentEditOrderLineItem.discountValue)
				) {
					currentEditOrderLineItem.discountAmount = 0
					currentEditOrderLineItem.discountValue = 0
				} else {
					currentEditOrderLineItem.discountAmount =
						this.formatValueAsCurrencyWithoutSymbol(
							this.getOrderLineItemDiscountAmount(currentEditOrderLineItem)
						)
				}
				//============

				const totalPriceDiscounted =
					currentEditOrderLineItem.totalPrice -
					currentEditOrderLineItem.discountAmount

				currentEditOrderLineItem.totalPriceDiscounted =
					totalPriceDiscounted < 0
						? 0
						: this.formatValueAsCurrencyWithoutSymbol(totalPriceDiscounted)
			},

			handleEditWholeOrderTaxes() {
				// set values then open modal
				const isChargeTaxesManualExemption = this.getWholeOrderDataValue(
					"isChargeTaxesManualExemption"
				)
				this.setEditOrderTaxesValue(
					"charge_taxes",
					isChargeTaxesManualExemption
				)
				// --------
				// set checkbox 'checked state' for 'charge taxes' edit in modal
				this.setEditWholeOrderTaxCheckbox()

				//---------
				// finally open the modal to edit 'charge taxes'
				this.handleOpenModal("is_edit_whole_order_taxes_modal_open")
			},

			// @TODO DELETE WHEN DONE
			// handle edit order shipping

			handleCalculateShippingAndTaxes() {
				// @TODO WHEN TO CLEAR THE ERROR?
				const isCalculateShippingAndTaxesPossible =
					this.checkIsCalculateShippingAndTaxesPossible()

				if (!isCalculateShippingAndTaxesPossible) {
					// shipping and taxes calculation not possible: show error
					this.setStoreValue(
						"is_show_recalculate_shipping_and_taxes_error",
						!isCalculateShippingAndTaxesPossible
					)
					// PWCommerceCommonScripts.debugger(
					// 	"InputfieldPWCommerceOrder - handleCalculateShippingAndTaxes - isCalculateShippingAndTaxesPossible - NOT POSSIBLE",
					// 	"info",
					// 	isCalculateShippingAndTaxesPossible
					// )
				} else {
					// remove shipping and taxes calculation not possible error
					this.setStoreValue(
						"is_show_recalculate_shipping_and_taxes_error",
						false
					)

					// PWCommerceCommonScripts.debugger(
					// 	"InputfieldPWCommerceOrder - handleCalculateShippingAndTaxes - isCalculateShippingAndTaxesPossible - YES POSSIBLE - FIRE HTXM",
					// 	"info",
					// 	isCalculateShippingAndTaxesPossible
					// )
					// trigger htmx to fire shipping and taxes calculation
					// htmx.trigger(elem, eventName);
					const eventName = "pwcommercecalculateshippingandtaxes"
					const elem = document.getElementById(
						"pwcommerce_order_calculate_shipping_button"
					)
					PWCommerceCommonScripts.dispatchCustomEvent(eventName, true, elem)
				}
			},

			handleEditOrderShippingAfterCalculateShippingAndTaxes() {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - handleEditOrderShippingAfterCalculateShippingAndTaxes - NEED TO HANDLE DIFFERENTLY",
				// 	"info",
				// 	"NEED TO HANDLE DIFFERENTLY"
				// )

				const elementWithCalculatedValues = document.getElementById(
					"pwcommerce_order_live_calculated_values_other_values"
				)

				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - handleEditOrderShippingAfterCalculateShippingAndTaxes - elementWithCalculatedValues",
				// 	"warn",
				// 	elementWithCalculatedValues
				// )

				if (elementWithCalculatedValues) {
					const calculatedValues = JSON.parse(elementWithCalculatedValues.value)
					// -------------
					// SET VALUES

					// check if we have an error
					// @note: now moved to htmx
					// const isOrderError = calculatedValues.isOrderError
					// if (isOrderError) {
					// 	const elementForShippingCalculationError = document.getElementById(
					// 		"pwcommerce_order_shipping_rates_calculation_error"
					// 	)
					// 	if (elementForShippingCalculationError) {
					// 		const isOrderErrorMessage = calculatedValues.isOrderErrorMessage
					// 		elementForShippingCalculationError.innerHTML = isOrderErrorMessage
					// 	}
					// }

					// set handling fee amount ONLY IF NOT CUSTOM!
					if (!this.checkIsUseCustomHandlingFee()) {
						const orderHandlingFeeAmount = calculatedValues.handlingFeeAmount
						// this.setWholeOrderValue("handlingFeeAmount", orderHandlingFeeAmount)
						this.setStoreValue(
							"temporary_handling_fee_amount",
							orderHandlingFeeAmount
						)
					}

					// set shipping fee amount ONLY IF NOT CUSTOM!
					if (!this.checkIsUseCustomShippingFee()) {
						const orderShippingFeeAmount = calculatedValues.shippingFee
						this.setStoreValue(
							"temporary_shipping_fee_amount",
							orderShippingFeeAmount
						)
					}

					// set sub-total price
					const orderSubtotalPrice = calculatedValues.subtotalPrice
					// this.setWholeOrderValue("subtotalPrice", orderSubtotalPrice)
					this.setStoreValue("temporary_subtotal_price", orderSubtotalPrice)
					// set total price
					const orderTotal = calculatedValues.totalPrice
					// this.setWholeOrderValue("totalPrice", orderTotal)
					this.setStoreValue("temporary_total_price", orderTotal)

					// ---------
					// also hide message about need to calculate shipping and taxes
					this.setStoreValue("is_need_to_recalculate_shipping_and_taxes", false)
				}
			},

			handleIsCustomShippingFeeChange(event) {
				// @TODO: UPDATE ORDER DATA MODEL: isCustomShippingFee so can toggle show custom shipping fee
				const customShippingFeeChangeElement = event.target
				// ==========
				// toggle show input for custom shipping fee (@see: isCustomShippingFee)
				this.setStoreValue(
					"is_show_custom_shipping_fee",
					customShippingFeeChangeElement.checked
				)
			},

			handleIsCustomHandlingFeeChange(event) {
				// @TODO: UPDATE ORDER DATA MODEL: isCustomHandlingFee so can toggle show custom handling fee type and value
				const customHandlingFeeChangeElement = event.target
				// ==========

				// toggle show inputs for custom handling fee (@see: isCustomHandlingFee)
				this.setStoreValue(
					"is_show_custom_handling_fee",
					customHandlingFeeChangeElement.checked
				)
			},

			handleIsCustomHandlingFeeTypeChange(event) {
				const customHandlingFeeTypeChangeElement = event.target
				// ==========
				const isShowCustomHandlingFeeValue =
					customHandlingFeeTypeChangeElement.value !== "none"
				// toggle show inputs for custom handling fee (@see: isCustomHandlingFee)
				this.setStoreValue(
					"is_show_custom_handling_fee_value",
					isShowCustomHandlingFeeValue
				)
			},

			handleConfirmIsDraftOrderSaveable(event) {
				/*
					@note:
						- @see InputfieldPWCommerceOrder.handleIsDraftOrderSaveable()
						- That reads store value here for 'is_need_to_recalculate_shipping_and_taxes'
						- If recalculation needed, it will prevent the order form from being submitted/saved
						- Else, form will be submitted as usual
				*/
				const isOrderDraftOrderSaveable =
					this.checkIsDraftOrderPublishableORSaveable()
				if (!isOrderDraftOrderSaveable) {
					// order not publishable: tell user in modal
					this.setStoreValue("is_order_saveable_modal_open", true)
				}
			},

			handleConfirmIsDraftOrderPublishable(event) {
				/*
					@note:
						- @see InputfieldPWCommerceOrder.handleIsDraftOrderPublishable()
						- That reads store value here for 'is_need_to_recalculate_shipping_and_taxes'
						- If recalculation needed, it will prevent the order form from being submitted/saved
						- Else, form will be submitted as usual
				*/
				const isOrderDraftOrderPublishable =
					this.checkIsDraftOrderPublishableORSaveable()
				if (!isOrderDraftOrderPublishable) {
					// order not publishable: tell user in modal
					this.setStoreValue("is_order_publishable_modal_open", true)
				}
			},

			handleSelectedMatchedShippingRateChange(event) {
				// @note: this is via the label surrounding the radio input
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - handleMatchedShippingRateChange - event",
				// 	"info",
				// 	event
				// )
				// get the shipping fee
				// @note: the dataset is on the label not the radio
				// hence use parentNode to reach it
				const shippingFee = event.target.parentNode.dataset.shippingFee
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - handleMatchedShippingRateChange - shippingFee",
				// 	"log",
				// 	shippingFee
				// )
				this.setStoreValue("temporary_shipping_fee_amount", shippingFee)
			},

			// @TODO DELETE IF NOT IN USE

			// submitProcessPageEditForm() {
			// 	return
			// 	const processPageEditFormElement =
			// 		document.getElementById("ProcessPageEdit")
			// 	if (processPageEditFormElement) {
			// 		processPageEditFormElement.submit()
			// 	}
			// },

			// ############# @TODO: DELETE IF NOT IN USE!!! ##############

			// carry out checks to determine if draft order is publishable
			checkIsDraftOrderPublishableORSaveable() {
				let isDraftOrderPublishable = true
				let draftOrderError
				const orderErrors = this.getOrderErrorsStrings()
				// -------
				const orderLineItems = this.getAllOrderLineItems()
				if (!orderLineItems.length) {
					// 1. check line items
					isDraftOrderPublishable = false
					draftOrderError = orderErrors["error_no_line_items_added"]
				} else if (
					this.getStoreValue("is_need_to_recalculate_shipping_and_taxes")
				) {
					// 2. check shipping needs recalculating (due to a recent change)
					isDraftOrderPublishable = false
					draftOrderError =
						orderErrors["error_need_to_recalculate_shipping_and_taxes"]
				} else if (!this.checkIsOrderCustomerDetailsComplete().is_error) {
					// 3. check customer details
					isDraftOrderPublishable = false
					draftOrderError = this.getDraftOrderCustomerDetailsError(
						this.checkIsOrderCustomerDetailsComplete().empty_detail
					)
				}
				// @TODO CARRY OUT SHIPPING CHECKS HERE IF APPLICABLE -> MEANS, WE HAVE MULTIPLE RATES AND ONE NEEDS SELECTING

				// ----------
				if (draftOrderError) {
					this.setStoreValue("error_current_order_error", draftOrderError)
				}

				// ------
				return isDraftOrderPublishable
			},

			// check if order required customer details are filled
			checkIsOrderCustomerDetailsComplete() {
				let isOrderCustomerDetailsComplete = true
				let emptyDetail
				// -----------
				// we check all required customer fields including names, email and address details. If billing address is in use, we also check that

				let orderCustomerDetailsElementsIDs =
					this.getOrderCustomerRequiredInputs()

				// check if to remove billing fields if not in use
				const useBillingAddressElementID =
					"pwcommerce_order_customer_use_billing_address"
				const useBillingAddressElement = document.getElementById(
					useBillingAddressElementID
				)
				if (useBillingAddressElement && !useBillingAddressElement.checked) {
					// filter out billing address inputs from being required
					orderCustomerDetailsElementsIDs =
						orderCustomerDetailsElementsIDs.filter(
							(item) => !item.includes("billing")
						)
				}

				// ------------
				// GOOD TO GO

				for (const orderCustomerDetailElementID of orderCustomerDetailsElementsIDs) {
					const orderCustomerDetailElement = document.getElementById(
						orderCustomerDetailElementID
					)
					// ------

					// if found element, check its value
					if (orderCustomerDetailElement) {
						if (
							// if checking xxx_country_id (primary or billing), we need a non-zero country ID
							orderCustomerDetailElementID.includes("country_id") &&
							!parseInt(orderCustomerDetailElement.value)
						) {
							// PWCommerceCommonScripts.debugger(
							// 	"InputfieldPWCommerceOrder - checkIsOrderCustomerDetailsComplete - EMPTY REQUIRED COUNTRY FIELD - orderCustomerDetailElementID",
							// 	"error",
							// 	orderCustomerDetailElementID
							// )
							isOrderCustomerDetailsComplete = false
							emptyDetail = orderCustomerDetailElementID
							break
						} else if (!orderCustomerDetailElement.value) {
							// for other inputs, we just need a value
							// PWCommerceCommonScripts.debugger(
							// 	"InputfieldPWCommerceOrder - checkIsOrderCustomerDetailsComplete - EMPTY REQUIRED FIELD - orderCustomerDetailElementID",
							// 	"error",
							// 	orderCustomerDetailElementID
							// )
							isOrderCustomerDetailsComplete = false
							emptyDetail = orderCustomerDetailElementID
							break
						}
					}
				}
				const customerCheckResult = {
					is_error: isOrderCustomerDetailsComplete,
					empty_detail: emptyDetail,
				}
				// ------
				return customerCheckResult
			},

			checkIsCalculateShippingAndTaxesPossible() {
				let isCalculateShippingAndTaxesPossible = true
				const wholeOrderData = this.getWholeOrderData()
				const orderShippingCountryID = parseInt(
					wholeOrderData.shippingAddressCountryID
				)

				const orderLineItems = this.getAllOrderLineItems()
				if (!orderLineItems.length || !orderShippingCountryID) {
					isCalculateShippingAndTaxesPossible = false
				}
				return isCalculateShippingAndTaxesPossible
			},

			checkIsUseCustomHandlingFee() {
				return this.getWholeOrderDataValue("isCustomHandlingFee")
				// let isUseCustomHandlingFee
				// const useCustomHandlingFeeElement = document.getElementById(
				// 	"pwcommerce_order_use_custom_handling_fee"
				// )
				// if (useCustomHandlingFeeElement) {
				// 	isUseCustomHandlingFee = useCustomHandlingFeeElement.checked
				// }
				// return isUseCustomHandlingFee
			},

			checkIsUseCustomShippingFee() {
				return this.getWholeOrderDataValue("isCustomShippingFee")
			},

			// ##################### @TODO: DELETE handleOrderShippingChange + triggerOrderCalculateTaxesAndShipping, etc if not in use!!! #######

			//~~~~~~~~~~~~~~~~~

			// ORDER CUSTOMER CHANGES TRIGGER

			// @note: changes 'live' in InputfieldPWCommerceOrderCustomer but we need some for calculation of taxes and shipping. we will calculate on server but we need to know when to trigger those request
			// @TODO, ALTERNATIVELY, TRIGGER REQUESTS USING HTMX AND SWAP THE MARKUP ON THE ORDER

			/**
			 * Handler for order changes being watched.
			 * Triggers API call based on changes.
			 *
			 * @param {any} value New value for property being watched
			 * @param {*} context The context of the watch that sent the value.
			 * @returns {void} void.
			 */
			async triggerOrderCalculateTaxesAndShipping(value, context) {
				// IF ON FIRST LOAD, RETURN
				// @note: prevents unnecessary API call since nothing has changed
				// @todo: ok like this?
				if (this.getIsFirstLoad()) {
					return
				}
				//-----------
			},

			//~~~~~~~~~~~~~~~~~

			//
			/**
			 *  Highlight an inputfield on demand.
			 * Use function in Inputfields.js.
			 * @param {*} field Can be .class, id or jQuery Object.
			 */
			showHighlight(field) {
				Inputfields.highlight(field)
			},

			//~~~~~~~~~~~~~~~~~

			// UTILITIES

			totalsForAnyProperty(items, property, isFloat = false) {
				let total
				if (isFloat) {
					total = items.reduce(
						(prev, cur) => prev + parseFloat(cur[property]),
						0
					)
				} else {
					total = items.reduce((prev, cur) => prev + parseInt(cur[property]), 0)
				}
				return total
			},

			findOrderLineItemIndex(order_line_item_id) {
				return this.getAllOrderLineItems().findIndex(
					(product) => product.id === order_line_item_id
				)
			},

			findOrderLineItem(order_line_item_id) {
				return this.getAllOrderLineItems().filter(
					(product) => product.id === order_line_item_id
				)[0]
			},

			removeOrderLineItem(removeProductIndex) {
				if (removeProductIndex > -1) {
					this.getAllOrderLineItems().splice(removeProductIndex, 1)
				}
			},

			replaceOrderLineItem(replaceAtIndex, insertOrderLineItem) {
				if (replaceAtIndex > -1) {
					this.getAllOrderLineItems().splice(
						replaceAtIndex,
						1,
						insertOrderLineItem
					)
				}
			},

			formatValueAsCurrency(number) {
				const shopCurrencyConfig = this.getShopCurrencyConfig()
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - formatValueAsCurrency - shopCurrencyConfig",
				// 	"warn",
				// 	shopCurrencyConfig
				// )
				//---------------
				// example: format de to EUR. Could also be format jp to euro!
				// @note: all these work: 'de-DE', 'de' or 'DE'
				// const currencyFormatTest = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(number)
				const formattedCurrency = new Intl.NumberFormat(
					shopCurrencyConfig.country_code,
					{
						style: "currency",
						currency: shopCurrencyConfig.alphabetic_code,
					}
				).format(number)
				// --------------
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - formatValueAsCurrency - formattedCurrency",
				// 	"warn",
				// 	formattedCurrency
				// )
				// ---------------
				return formattedCurrency
			},

			formatValueAsCurrencyWithoutSymbol(value, decimalPlaces = 2) {
				const parsedValue = parseFloat(value)
				// return parseFloat(value.toFixed(decimalPlaces))// @note: will sometimes throw errors
				return parsedValue.toFixed(decimalPlaces)
			},

			// @credit: https://stackoverflow.com/a/56592365
			extractObjectSubset(originalObject, subsetOfPropertiesToExtract) {
				const subset = subsetOfPropertiesToExtract.reduce(function (obj2, key) {
					if (key in originalObject)
						// line can be removed to make it inclusive
						obj2[key] = originalObject[key]
					return obj2
				}, {})
				return subset
			},

			/**
			 * Converts an object or value to a JSON string, optionally replacing values if a replacer function or array is specified.
			 * @param {any} value
			 * @param {string} replacer A function that alters the behavior of the stringification process, or an array of String and Number that serve as an allowlist for selecting/filtering the properties of the value object to be included in the JSON string.
			 * @returns {string} A JSON string representing the given value, or undefined.
			 */
			stringifyArrayOrObject(value, replacer) {
				return JSON.stringify(value, replacer)
			},

			// creates from data from an object of values
			prepareFormData(params) {
				const formData = new FormData()
				for (const [prop, value] of Object.entries(params)) {
					formData.append(prop, value)
				}

				return formData
			},

			// @TODO: SORT OUT PROPS IN BELOW FUNCS!

			// ~~~~~~~~~~~~~~ ORDER LINE ITEMS ~~~~~~~~~~~~~~~~~~~

			// get the discount amount (i.e. from discount type and value) FOR A SINGLE LINE ITEM
			getOrderLineItemDiscountAmount(order_line_item) {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - getOrderLineItemDiscountAmount - order_line_item",
				// 	"log",
				// 	order_line_item
				// )
				let totalDiscount = 0
				if (order_line_item.discountValue) {
					// percentage or fixed discount
					totalDiscount =
						order_line_item.discountType === "percentage"
							? this.calculateOrderItemPercentageDiscountTotal(order_line_item)
							: this.calculateOrderItemFixedDiscountTotal(order_line_item)
				}
				return totalDiscount
			},

			//-------------------
			calculateOrderItemFixedDiscountTotal(order_line_item) {
				return order_line_item.discountType === "fixed_applied_once"
					? order_line_item.discountValue
					: order_line_item.discountValue * order_line_item.quantity
			},
			/**
			 * Calculate the discount portion of a line item.
			 * Takes into account quantity of items.
			 * @returns Integer Discounted amount
			 */
			calculateOrderItemPercentageDiscountTotal(order_line_item) {
				// Discount = Original Price x Discount %/100
				return (
					order_line_item.totalPrice * (order_line_item.discountValue / 100)
				)
			},

			// ~~~~~~~~~~~~~~ END ORDER LINE ITEMS ~~~~~~~~~~~~~~~~~~~

			// ~~~~~~~~~~~~~~ ORDER ONLY/WHOLE ORDER ~~~~~~~~~~~~~~~~~~~

			/**
			 * Get the whole-order-only discount amount.
			 * @param {object} wholeOrderOnlyDiscount Object with order discount values.
			 * @returns {float}wholeOrderOnlyTotalDiscount The whole-order-only discount amount.
			 */
			getWholeOrderOnlyDiscountAmount(wholeOrderOnlyDiscount) {
				let wholeOrderOnlyTotalDiscount = 0
				if (wholeOrderOnlyDiscount.discountValue) {
					// percentage or fixed discount
					wholeOrderOnlyTotalDiscount =
						wholeOrderOnlyDiscount.discountType === "percentage"
							? this.calculateOrderOnlyPercentageDiscountTotal(
									wholeOrderOnlyDiscount
							  )
							: this.calculateOrderOnlyFixedDiscountTotal(
									wholeOrderOnlyDiscount
							  )
				}
				return wholeOrderOnlyTotalDiscount
			},

			//-------------------
			calculateOrderOnlyFixedDiscountTotal(wholeOrderOnlyDiscount) {
				// @note for whole order fixed discount applied per line item we get the number of items/products in order! Not their quantities!
				const numberOfLineItemsInOrder = this.getOrderNumberOfLineItems()
				//-------
				// discount applied once vs apply fixed discount per item in the order
				return wholeOrderOnlyDiscount.discountType === "fixed_applied_once"
					? wholeOrderOnlyDiscount.discountValue
					: wholeOrderOnlyDiscount.discountValue * numberOfLineItemsInOrder
			},
			/**
			 * Calculate order only percentage discount.
			 * Takes into account quantity of items.
			 * @returns Integer Discounted amount
			 */
			calculateOrderOnlyPercentageDiscountTotal(wholeOrderOnlyDiscount) {
				const orderGrossPrice = this.getClientOnlyOrderValue(
					"client_order_total_cost_without_discounts_shipping_or_taxes"
				)
				// Discount = Original Price x Discount %/100
				return orderGrossPrice * (wholeOrderOnlyDiscount.discountValue / 100)
			},

			//-------------------------------

			/**
			 * Get the handling fee amount of the order.
			 * Can be based on a percentage or a fixed fee.
			 * @param {object} orderShipping Object with order shipping values.
			 * @returns {float}orderHandlingFeeTotal The order handling fee amount.
			 */
			getOrderHandlingFeeAmount(orderShipping) {
				let orderHandlingFeeTotal = 0

				if (orderShipping.handlingFeeValue) {
					// percentage or fixed handling fee type
					orderHandlingFeeTotal =
						orderShipping.handlingFeeType === "percentage"
							? this.calculateOrderHandlingFeePercentageTotal(orderShipping)
							: this.calculateOrderHandlingFeeFixedTotal(orderShipping)
				}
				return orderHandlingFeeTotal
			},

			//-------------------
			calculateOrderHandlingFeeFixedTotal(orderShipping) {
				// handling fee applied once (fixed)
				return orderShipping.handlingFeeValue
			},
			/**
			 * Calculate order handling fee percentage amount.
			 * Takes into account shipping total
			 * @returns {float} Handling Fee  amount
			 */
			calculateOrderHandlingFeePercentageTotal(orderShipping) {
				// @TODO: CREATE THIS! + @TODO: NEED TO GET FROM SERVER!!!
				// const orderShippingFee = this.getClientOnlyOrderValue(
				// 	"client_order_total_cost_without_discounts_shipping_or_taxes"
				// );
				// @todo: client_order_shipping_amount_plus_handling_fee_amount
				const orderShippingFee = 3
				// Handling Fee = Shipping Fee x Handling Fee Value %/100
				return orderShippingFee * (orderShipping.handlingFeeValue / 100)
			},

			//~~~~~~~~~~~~~~~~~~~~~

			/**
			 *Get the ProcessWire config sent for this order.
			 * @returns object.
			 */
			getProcessWireOrderConfig() {
				return ProcessWire.config.InputfieldPWCommerceOrder.order_whole_data
			},
			/**
			 *Get the ProcessWire config extra data sent for use this order.
			 * @returns object.
			 */
			getProcessWireOrderExrasConfig() {
				return ProcessWire.config.InputfieldPWCommerceOrder.extras
			},

			/**
			 *Get the ProcessWire config shop currency data sent for use this order.
			 * @returns object.
			 */
			getProcessWireOrderShopCurrencyConfig() {
				return ProcessWire.config.InputfieldPWCommerceOrder.shop_currency_data
			},

			/**
			 *Get the ProcessWire config sent for order items in this order.
			 * @return array.
			 */
			getProcessWireOrderLineItemsConfig() {
				return ProcessWire.config.InputfieldPWCommerceOrder.order_line_items
			},

			/**
			 * Get the value of a given store property.
			 * @param string property Property in store whose value to return
			 * @returns {any}
			 */
			getStoreValue(property) {
				return this.$store.InputfieldPWCommerceOrderStore[property]
			},

			/**
			 *Get the shop currency config object.
			 * @return {object}..
			 */
			getShopCurrencyConfig() {
				return this.getStoreValue("shop_currency_config")
			},

			/**
			 *Get the whole-order data object.
			 * @return {object}..
			 */
			getWholeOrderData() {
				return this.getStoreValue("order_whole_data")
			},

			/**
			 *Get the value of a given whole-order data property
			 * @param str property Whole-order property whose value to return
			 * @returns any
			 */
			getWholeOrderDataValue(property) {
				const wholeOrderData = this.getWholeOrderData()
				return wholeOrderData[property]
			},

			getOrderCustomerRequiredInputs() {
				const getOrderExtrasConfigs = this.getProcessWireOrderExrasConfig()
				return getOrderExtrasConfigs.order_required_customer_fields
			},
			/**
			 * Get order error string
			 * @returns obj Object with translated order errors strings.
			 */
			getOrderErrorsStrings() {
				const getOrderExtrasConfigs = this.getProcessWireOrderExrasConfig()
				return getOrderExtrasConfigs.order_errors_strings
			},

			getDraftOrderCustomerDetailsError(order_customer_detail_id) {
				const orderErrorStrings = this.getOrderErrorsStrings()
				// @todo maybe first check if key exists?
				return orderErrorStrings[order_customer_detail_id]
			},

			getNamesOfModalPropertiesNeedingExtraHandling() {
				const propertiesNeedingExtraHandling = [
					"is_order_publishable_modal_open",
					"is_order_saveable_modal_open",
				]
				return propertiesNeedingExtraHandling
			},

			/**
			 * Get the current order with a a subset of its properties
			 * @return {object} orderWithSubsetOfProperties Object with subset of order properties.
			 */
			async getMainOrderWithLimitedProperties(subsetproperties) {
				const wholeOrderData = this.getWholeOrderData()
				const orderWithSubsetOfProperties = this.extractObjectSubset(
					wholeOrderData,
					subsetproperties
				)
				//-----------
				return orderWithSubsetOfProperties
			},

			/**
			 * Get all the order items in this order
			 * @return array.
			 */
			getAllOrderLineItems() {
				return this.getStoreValue("order_line_items")
			},

			// @TODO DELETE IF NOT IN USE!
			/**
			 * Get all the order items in this order BUT limit properties
			 * @return {array} allOrderLineItemsWithSubsetOfProperties Array of order line items with subset of their properties.
			 */
			async getAllOrderLineItemsWithLimitedProperties(subsetproperties) {
				const allOrderLineItems = this.getAllOrderLineItems()
				// --------------
				const allOrderLineItemsWithSubsetOfProperties = []
				for (const order_line_item of allOrderLineItems) {
					const orderLineItemWithSubsetOfProperties = this.extractObjectSubset(
						order_line_item,
						subsetproperties
					)
					//-----------
					// add subset order line item
					allOrderLineItemsWithSubsetOfProperties.push(
						orderLineItemWithSubsetOfProperties
					)
				}
				return allOrderLineItemsWithSubsetOfProperties
			},

			getCurrentLiveOrderLineItemsProductsIDsCSV() {
				const allOrderLineItems = this.getAllOrderLineItems()
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - getCurrentLiveOrderLineItemsProductsIDsCSV - allOrderLineItems",
				// 	"info",
				// 	allOrderLineItems
				// )
				// @note: for 'live' items, we need the product ID instead of order line item ID! This will cater for both saved and non-saved as well as removed line items since we will need to fetch product values such as price and taxable
				const allLiveOrderLineItemsProductsIDsCSV = allOrderLineItems
					.map((item) => item.productID)
					.join()

				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - getCurrentLiveOrderLineItemsProductsIDsCSV - allLiveOrderLineItemsProductsIDsCSV",
				// 	"log",
				// 	allLiveOrderLineItemsProductsIDsCSV
				// )
				// --------------
				return allLiveOrderLineItemsProductsIDsCSV
			},

			getCurrentLiveOrderLineItemsIDsCSV() {
				const allOrderLineItems = this.getAllOrderLineItems()
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - getCurrentLiveOrderLineItemsIDsCSV - allOrderLineItems",
				// 	"info",
				// 	allOrderLineItems
				// )
				// @note: for 'live' items, we ALSO need the IDs of LIVE EXISTING ORDER LINE ITEMS! We need this in order to get the values for their quantity and discount inputs! Their IDs are the suffixes for these inputs
				const allLiveOrderLineItemsIDsCSV = allOrderLineItems
					.map((item) => item.id)
					.join()

				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - getCurrentLiveOrderLineItemsIDsCSV - allLiveOrderLineItemsIDsCSV",
				// 	"log",
				// 	allLiveOrderLineItemsIDsCSV
				// )
				// --------------
				return allLiveOrderLineItemsIDsCSV
			},

			/**
			 *Get the current order's products results.
			 * @return array.
			 */
			getOrderProductsSearchResults() {
				return this.getStoreValue("order_products_search_results")
			},

			/**
			 * Get the currently selected product variants in the modal for adding products as order line items.
			 * @return array.
			 */
			getModalCheckedProductVariants() {
				return this.getStoreValue("modal_checked_variants")
			},

			/**
			 * Get the currently selected product without variants in the modal for adding products as order line items.
			 * @return array.
			 */
			getModalCheckedProductsWithoutVariants() {
				return this.getStoreValue("modal_checked_products_without_variants")
			},

			/**
			 * Get the client-side-only object.
			 * @note this will lives inside order_whole_data
			 * @param string property Client property whose value to return
			 * @returns any
			 */
			getClientOnlyOrderObject() {
				// CLIENT ONLY PROPERTIES/VALUES (NOT saved on server!)

				const clientOnlyObject = {
					// charge taxes on this order?
					client_charge_taxes: true,
					// is customer tax exempt? // @TODO: DELETE IF NOT IN USE! MOVING TO WHOLE ORDER DATA!
					// is_customer_tax_exempt: false,
					// total calculated taxes
					// @todo: dynamically set: htmx???
					client_order_total_taxes_amount: 0,
					// total quantity items in the order
					// @note: this is the sum of quantities of all line items AND NOT the number of line items / product titles in the order!
					client_total_line_items_quantity: 0,
					// all line items discounts amount/combined
					client_total_line_items_discount_amount: 0,
					// overall discount amount/total on order
					client_order_plus_line_items_discount_amount: 0,
					// order gross / total cost without discounts, taxes or shipping (gross)
					client_order_total_cost_without_discounts_shipping_or_taxes: 0,
					// overall subtotal with discounts applied BUT without shipping and taxes added
					client_subtotal_after_discounts_applied_without_shipping_and_taxes_amount: 0,
					// use custom shipping fee (instead of calculated one)
					// @note: we still model shippingFee!
					client_is_use_custom_shipping_fee: false,
					// use custom shipping HANDLING fee (instead of the one from the matched zone)
					// @note: we still model handlingFeeValue!
					client_is_use_custom_handling_fee: false,
					// overall shipping amount on order (shipping + handling fee)
					// @TODO!!!!!!!!!!!!!!!! NEED TO IMPLEMENT THIS!
					client_order_shipping_amount_plus_handling_fee_amount: 0,
					// ORDER FINAL COST after DEDUCTIONS and ADDITIONS
					// @TODO!!!!!!!!!!!!!!!! NEED TO IMPLEMENT THIS!
					client_total_after_discounts_and_shipping_and_taxes_added_amount: 0,
				}
				return clientOnlyObject
			},

			/**
			 * Get the value of a given client-side-only property.
			 * @note these also live under order_whole_data.
			 * @param string property Client property whose value to return.
			 * @returns any
			 */
			getClientOnlyOrderValue(property) {
				return this.getStoreValue("order_whole_data").client[property]
			},

			/**
			 * Get the in-edit whole-order discount object.
			 * @return {object} The IN-EDIT discount object.
			 */
			getEditWholeOrderDiscount() {
				return this.getWholeOrderData()
				// @TODO CHANGE THIS! WE NOW EDIT DIRECTLY!
				return this.getStoreValue("edit_whole_order_discount")
			},

			/**
			 *Get the IN-EDIT order taxes object.
			 * @return {object}
			 */
			getEditedOrderTaxes() {
				return this.getStoreValue("edit_whole_order_taxes")
			},

			/**
			 * Get the value of whether taxes are being charged on order.
			 * @return {boolean} Whether to charge taxes on order.
			 */
			getChargeTaxesOnOrder() {
				return this.getClientOnlyOrderValue("client_charge_taxes")
			},

			// @TODO DELETE IF NOT IN USE ANYMORE

			/**
			 * Get the IN-EDIT order shipping object.
			 * @return {object}.
			 */
			getEditOrderShipping() {
				return this.getStoreValue("edit_whole_order_shipping")
			},

			// @TODO! + RETURN GENERIC INFO IF EMPTY?!
			getShippingZoneCalculatedHandlingFee() {
				// @todo @note: for now, due to some $watch and infinite loop, we get from temporary display value
				// const handlingFeeAmount = this.getWholeOrderDataValue("handlingFeeAmount")
				const handlingFeeAmount = this.getStoreValue(
					"temporary_handling_fee_amount"
				)
				return this.formatValueAsCurrency(handlingFeeAmount)
			},

			// @TODO! + RETURN GENERIC INFO IF EMPTY?!
			getShippingZoneCalculatedShippingFee() {
				// @todo @note: for now, due to some $watch and infinite loop, we get from temporary display value
				// const handlingFeeAmount = this.getWholeOrderDataValue("shippingFee")
				const shippingFee = this.getStoreValue("temporary_shipping_fee_amount")
				return this.formatValueAsCurrency(shippingFee)
			},

			/**
			 *Get the total number of order items quantities in this order.
			 * @note: this is the sum of quantities of all line items AND NOT the number of line items / product titles in the order! for that @see getOrderNumberOfLineItems()
			 * @return {number}.
			 */
			getOrderQuantity() {
				return this.totalsForAnyProperty(
					this.getAllOrderLineItems(),
					"quantity"
				)
			},

			/**
			 *Get the total number of order items in this order.
			 * @note: this is the the number of line items / product titles in the order!
			 * and not their quantities! for that @see getOrderQuantity()
			 * @return {number}.
			 */
			getOrderNumberOfLineItems() {
				const allOrderLineItems = this.getAllOrderLineItems()
				return allOrderLineItems.length
			},

			/**
			 *Get the order line item whose discount is currently being edited.
			 * @return {object}.
			 */
			getCurrentEditOrderLineItemDiscount() {
				return this.getStoreValue("edit_current_order_line_item_discount")
			},

			/**
			 * Check if the order item page has just been loaded
			 * @returns bool
			 */
			getIsFirstLoad() {
				return this.getStoreValue("is_first_load")
			},

			// GETTERS FOR SETTING TOTALS

			//

			/**
			 * Get the total of ALL order line items discount amounts.
			 * @returns {float} The total discount amount for line items.
			 */
			getTotalOrderLineItemsDiscountAmount() {
				return this.totalsForAnyProperty(
					this.getAllOrderLineItems(),
					"discountAmount",
					true
				)
			},

			/**
			 * Get the total order cost without discounts, shipping or taxes applied.
			 * @returns {float} The total price.
			 */
			getTotalOrderCostWithoutDiscountsShippingOrTaxes() {
				return this.totalsForAnyProperty(
					this.getAllOrderLineItems(),
					"totalPrice",
					true
				)
			},

			// @TODO: WIP!
			// get the subtotal of the order AFTER all discounts applied (order and line items ones) WITHOUT shpping or taxes
			getOrderSubtotalWithoutShippingOrTaxes() {
				// total order cost without discounts, shipping or taxes
				const totalOrderCostWithoutDiscountsShippingOrTaxes =
					this.getTotalOrderCostWithoutDiscountsShippingOrTaxes()
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - getOrderSubtotalWithoutShippingOrTaxes - totalOrderCostWithoutDiscountsShippingOrTaxes",
				// 	"error",
				// 	totalOrderCostWithoutDiscountsShippingOrTaxes
				// )
				//----------
				// total line items discounts amount
				const totalOrderLineItemsDiscountAmount =
					this.getTotalOrderLineItemsDiscountAmount()
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - getOrderSubtotalWithoutShippingOrTaxes - totalOrderLineItemsDiscountAmount",
				// 	"log",
				// 	totalOrderLineItemsDiscountAmount
				// )
				//------------
				// total whole-order-only discount amount
				// @note: THIS IS THE FRESH VALUE IN-EDIT! - this.getEditWholeOrderDiscount() as opposed to this.getWholeOrderData()! which at this point hasn't yet been updated @see: this.updateWholeOrderDiscount()
				// @TODO: REVISIT THIS ORDER DISCOUNT SOURCE? WHERE DO WE NEED THIS? IF NEEDED IN MODAL TO DISPLAY VALUES IF DISCOUNT APPLIED, THEN FINE AS BELOW!
				const totalWholeOrderOnlyDiscountAmount =
					this.getWholeOrderOnlyDiscountAmount(this.getEditWholeOrderDiscount())
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceOrder - getOrderSubtotalWithoutShippingOrTaxes - totalWholeOrderOnlyDiscountAmount",
				// 	"info",
				// 	totalWholeOrderOnlyDiscountAmount
				// )
				//------------
				// total of whole-order-only PLUS all-line-items discounts amount
				const overallOrderPlusLineItemsDiscountsAmount =
					this.formatValueAsCurrencyWithoutSymbol(
						totalOrderLineItemsDiscountAmount +
							totalWholeOrderOnlyDiscountAmount
					)
				//--------------
				const orderSubtotalWithoutShippingOrTaxes =
					this.formatValueAsCurrencyWithoutSymbol(
						totalOrderCostWithoutDiscountsShippingOrTaxes -
							overallOrderPlusLineItemsDiscountsAmount
					)
				//--------------
				return orderSubtotalWithoutShippingOrTaxes
			},

			//+++++++++++++++++++++++++++++++++

			// @TODO: DELETE THOSE NOT IN USE!

			// PRICES
			// gross price/amount minus any discounts applied
			// @todo: including taxes?
			getOrderGrossPrice() {
				// @TODO: CHANGE FIXED DECIMAL PLACES VALUE TO COUNTRYCURRENCY MINOR UNIT!
				// @TODO
				const lineItems = this.currentEditOrder().line_items
				const grossPrice = this.totalsForAnyProperty(
					lineItems,
					"gross_price",
					true
				)
				return grossPrice
			},

			// TOTALS

			// @TODO: AMEND BELOW FOR OUR USE!!! WE DON'T HAVE THESE PROPS!

			getOrderTotal() {
				const orderTotalTaxes = this.charge_taxes ? this.taxes : 0
				const orderTotalShippingPrice = this.shipping
					? this.shipping.shipping_price_total
					: 0
				return (
					this.getOrderNetPriceAfterDiscount +
					orderTotalTaxes +
					orderTotalShippingPrice
				)
			},

			// ~~~~~~~~~~~~~~ END ORDER ONLY/WHOLE ORDER ~~~~~~~~~~~~~~~~~~~

			//~~~~~~~~~~~~~~~~~
			/**
			 * Process order details to be sent to server in order to calculate taxes and/or shipping.
			 * @returns {object} values Processed values ready for posting.
			 */
			async processOrderTaxesAndShippingCalculationsData() {
				// @TODO: THIS NOW CHANGES! SINGLE SOURCE OF TRUTH! ORDER WHOLE DATA AND ORDER LINE ITEMS ONLY!!!!
				// we are only interested in these properties in an order line item object
				// we use this array as a 'replacer' in the JSON.stringify() function
				const orderLineItemsAllowProperties = [
					"productID",
					"discountType",
					"discountValue",
					"quantity",
					"isVariant",
				]

				//-----------------
				// we are only interested in these properties in an order object
				// we use this array as a 'replacer' in the JSON.stringify() function
				const orderAllowProperties = ["discountType", "discountValue"]
				// const mainOrderWithSubsetOfProperties =
				// 	await this.getMainOrderWithLimitedProperties(
				// 		orderAllowProperties
				// 	);

				//-----------
				const orderShipping = this.getEditOrderShipping()
				const shipping = {
					handling_fee_type: orderShipping.handlingFeeType,
					handling_fee_value: orderShipping.handlingFeeValue,
				}

				const customer = {
					country_id: this.getWholeOrderDataValue("shippingAddressCountryID"),
					is_tax_exempt: this.getWholeOrderDataValue("isTaxExempt"),
				}

				//#################
				// @todo: stringify or form objects?
				const values = {
					// main_order: mainOrderWithSubsetOfProperties,
					main_order: this.stringifyArrayOrObject(
						this.getWholeOrderData(),
						orderAllowProperties
					),
					order_line_items: this.stringifyArrayOrObject(
						this.getAllOrderLineItems(),
						orderLineItemsAllowProperties
					),
					// order_line_items: allOrderLineItemsWithSubsetOfProperties,
					// customer: {},
					customer: this.stringifyArrayOrObject(customer),
					charge_taxes: this.getChargeTaxesOnOrder(),
					// taxes: taxes,
					shipping: this.stringifyArrayOrObject(shipping),
					// shipping: shipping,
					// calculate_shipping_and_taxes_other: 1,
				}
				// const formValues = this.prepareFormData(values)

				return values
				// return formValues
			},

			// ~~~~~~~~~~~~~~~~~~~~ DEBUG ~~~~~~~~~~~~~~~~~~

			handleDebugChange(value, property) {
				// PWCommerceCommonScripts.debugger(
				// 	`InputfieldPWCommerceOrder - handleDebugChange - ${property}`,
				// 	"info",
				// 	value
				// )
			},
		}))
	}
	// end: if in pwcommerce shop context
})

//--------------
