const InputfieldPWCommerceProductStock = {
	listenToHTMXRequests: function () {
		// after request
		htmx.on("htmx:afterRequest", function (event) {
			// @note: triggered after an AJAX request has completed
			// @see: https://htmx.org/events/#htmx:afterRequest
			// RUN POST REQUEST OPS
			InputfieldPWCommerceProductStock.runAfterRequestOperations(event)
			//------------
		})

		// after settle
		htmx.on("htmx:afterSettle", function (event) {
			// RUN POST SETTLE OPS
			InputfieldPWCommerceProductStock.runAfterSettleOperations(event)
		})
	},

	runAfterRequestOperations: function (event) {
		const targetElement = event.detail.target

		// @note: SPECIAL CASE FOR variants create request has been issued, now we need to hide the preview variants view
		if (
			targetElement.id === "pwcommerce_product_generated_variants_preview_wrapper"
		) {
			InputfieldPWCommerceProductStock.runHidePreviewVariantsOperations()
		}
	},

	/**
	 * Run afterSettle operations (after htmx swap).
	 * These depend on the htmx request context.
	 * Use this so that alpine js can work on 'settled' dom contents.
	 * @param {object} event Object containing the event that triggered the request or custom object with post-op details.
	 */
	runAfterSettleOperations: function (event) {
		const requestElement = event.detail.elt
		const requestElementID = requestElement.id
		// ----------------------
		const requestConfigElement = event.detail.requestConfig.elt
		const requestConfigElementID = requestConfigElement.id
		// ----------------
		// if either IDs not present, return
		if (!requestElementID && !requestConfigElementID) return

		// --------------

		// FIRST CHECK IF THIS IS AFTER SETTLE OPERATIONS FOR REFRESH VARIANTS LIST AFTER CREATING NEW VARIANTS
		// @note: in this case, the element at event.detail.elt is a ProcessWire ul; it has no ID! instead we check the ID of the element in requestConfig -> event.detail.requestConfig.elt => div#pwcommerce_product_main_edit_modals
		// in that case, the hx-trigger was an event ''pwcommercerefreshinputfieldruntimemarkuplist'
		if (requestConfigElementID === "pwcommerce_product_main_edit_modals") {
			// this was a refresh RUNTIMEMARKUP operation
			// send window event to alpine.js to close modal
			InputfieldPWCommerceProductStock.runProductGenerateVariantsIsFinished()
		}
		//---------
		// SECOND CHECK IF THIS IS A CREATE VARIANTS OPERATION
		// fire event to notify htmx that variants have been updated and the list needs refreshing.
		// htmx will send a GET request to InputfieldRuntimeMarkup
		// @update @note! we are now using htmx 'out of bands' in the response markup. it seems in that case, the TARGET div with id 'pwcommerce_product_generated_variants_preview_wrapper' is no longer the trigger element! the oob element is! in our case p#pwcommerce_product_variants_creation_outcome
		else if (
			requestElementID === "pwcommerce_product_variants_creation_outcome"
		) {
			// product new variants created via API - post-op (will trigget GET htmx request to refresh runtimemarkup list IF CREATION WAS SUCCESSFUL)
			InputfieldPWCommerceProductStock.runProductVariantsHaveBeenCreatedPostOperations(
				event
			)
		}
	},

	// ~~~~~~~~~~~~~

	runHidePreviewVariantsOperations: function () {
		//-----------------------
		// TELL ALPINE TO HIDE THE PREVIEW VARIANTS MARKUP AND ACTION BUTTONS
		// @see: handleHideProductGenerateVariantsPreview()
		// @todo: change event name?
		const eventName = "pwcommerceproductgeneratevariantshidepreview"
		const eventDetail = true
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	// Run post-product-variants-created operations.
	// @note: another HTMX trigger is listening to the custom event here.
	runProductVariantsHaveBeenCreatedPostOperations: function (event) {
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceProductStock - runProductVariantsHaveBeenCreatedPostOperations - event",
		// 	"info",
		// 	event
		// )
		/* @TODO @UDPDATE!!!

		1. NEED TO FIRST CHECK IF AT LEAST ONE VARIANT WAS CREATED! WE CHECK the dataset of the p#pwcommerce_product_variants_creation_outcome => data-notice-type => if 'error', don't fire below event!
		2. NO NEED TO DO ANYTHING AFTER THAT; ERROR MESSAGE WILL BE DISPLAYED; USE CAN CLOSE MODAL AND RETRY

		*/
		// @note: p#pwcommerce_product_variants_creation_outcome
		const productVariantsCreationOutcomeElement = event.detail.elt
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceProductStock - runProductVariantsHaveBeenCreatedPostOperations - productVariantsCreationOutcomeElement - SUCCESS OR ERROR?",
		// 	"log",
		// 	productVariantsCreationOutcomeElement
		// )

		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceProductStock - runProductVariantsHaveBeenCreatedPostOperations - productVariantsCreationOutcomeElement.dataset.noticeType - SUCCESS OR ERROR IN NOTICE TYPE?",
		// 	"warn",
		// 	productVariantsCreationOutcomeElement.dataset.noticeType
		// )
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceProductStock - runProductVariantsHaveBeenCreatedPostOperations - productVariantsCreationOutcomeElement.dataset.noticeType - SUCCESS OR ERROR IN NOTICE TYPE EQUALITY?",
		// 	"log",
		// 	productVariantsCreationOutcomeElement.dataset.noticeType === "success"
		// )
		if (
			productVariantsCreationOutcomeElement.dataset.noticeType === "success"
		) {
			// =================
			// to tell InputfieldPWCommerceRuntimeMarkup to refresh its markup list (because new items have been added via API as a result of another htmx trigger) BY TRIGGERING AN ADDITIONAL HTMX REQUEST
			const elem = document.getElementById("pwcommerce_product_main_edit_modals")
			const eventName = "pwcommercerefreshinputfieldruntimemarkuplist"
			// ----------------
			// to tell InputfieldPWCommerceProductStock to display message that variants list is refreshing (via ALPINE.JS)
			// @TODO DELETE WHEN DONE
			//const elem2 = document.getElementById("pwcommerce_product_main_edit_modals")
			// @note: no htmx involvement here - just a custom Window Event
			const eventName2 = "pwcommerceproductisrefreshingvariantslist"

			// ----------------
			// to tell InputfieldPWCommerceProductStock to update the array of 'existing_variants' to include the newly created ones (since page not yet reloaded, so still preserving outdated values) (via ALPINE.JS)
			//const elem3 = document.getElementById("pwcommerce_product_variants_is_refreshing_list_wrapper")
			// @note: no htmx involvement here - just a custom Window Event
			const eventName3 = "pwcommerceproductupdateexistingvariantslist"

			// @note: this works! can access store externally!
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceProductStock - runProductVariantsHaveBeenCreatedPostOperations - TRYING TO ACCESS ALPINE STORE FROM WITHIN SCRIPT EXTERNAL TO IT! - Alpine.store('InputfieldPWCommerceProductStockStore').main_product",
			// 	"warn",
			// 	Alpine.store("InputfieldPWCommerceProductStockStore").main_product
			// )

			// @NOTE: CANNOT ACCESS Alpine.data() externally!
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceProductStock - runProductVariantsHaveBeenCreatedPostOperations - TRYING TO ACCESS ALPINE DATA FROM WITHIN SCRIPT EXTERNAL TO IT! - Alpine.data('InputfieldPWCommerceProductStockStore').testCanAccessFromExternalScipt('HELLO SECRET MESSAGE!')",
			// 	"warn",
			// 	Alpine.data(
			// 		"InputfieldPWCommerceProductStockData"
			// 	)
			// )

			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceProductStock - runProductVariantsHaveBeenCreatedPostOperations - SUCCESS OUTCOME! - sending this eventName using htmx.trigger(elem,eventName)",
			// 	"log",
			// 	eventName
			// )
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceProductStock - runProductVariantsHaveBeenCreatedPostOperations - SUCCESS OUTCOME! - TELL ALPINE TO SHOW IS REFRESHING MESSAGE - sending this eventName2 using WINDOW EVENT CUSTOM EVENT",
			// 	"info",
			// 	eventName2
			// )
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceProductStock - runProductVariantsHaveBeenCreatedPostOperations - SUCCESS OUTCOME! - TELL ALPINE TO UPDATE LIST OF EXISTING VARIANTS BASED ON THE NEWLY CREATED ONES! - sending this eventName3 using WINDOW EVENT CUSTOM EVENT",
			// 	"info",
			// 	eventName3
			// )
			// htmx.trigger(elem, eventName, eventDetail);
			// need htmx to trigger another htmx request
			htmx.trigger(elem, eventName)
			// @todo delete when done; no need for htmx as we don't need it to trigger any htmx request
			//htmx.trigger(elem2, eventName2)
			// @note: window events for alpine.js to listen to; no htmx requests here
			PWCommerceCommonScripts.dispatchCustomEvent(eventName2, true)
			PWCommerceCommonScripts.dispatchCustomEvent(eventName3, true)
			// @TODO: NEED TO HIGHLIGHT WRAPPER OF VARIANTS LIST
			// @TODO: NEED TO SHOW INDICATOR WHEN VARIANTS BEING CREATED PLUS LOOK THAT SCREEN
			// @TODO: NEED TO CLOSE THE MODAL ON SUCCESS OR SHOW ERROR ON FAIL
			// @TODO: maybe send event to alpine during the creation to hide the previews and show view with spinner or loading or get progress from htmx? @see this example: https://htmx.org/examples/progress-bar/
		}
	},

	// Run post-product-variants-created operations.
	// @note: another HTMX trigger is listening to the custom event here.
	// @note: this means at least one product variant was created successfully and the runtimemarkup list has been refreshed.
	// we now fire an event to tell alpine.js that the product variant generation is finished
	runProductGenerateVariantsIsFinished: function () {
		// ---------------
		// @TODO: SETTIMEOUT FOR A FEW SECONDS?
		// TELL ALPINE NEW VARIANTS ADDED SO CAN NOW CLOSE MODAL FOR GENERATING VARIANTS
		const eventName = "pwcommerceproductgeneratevariantsisfinished"
		const eventDetail = true
		setTimeout(() => {
			// @note: method is in PWCommerceCommonScripts.js
			PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
		}, 500)
	},

	// ~~~~~~~~~~~~~

	/**
	 * Mutation observer for selected elements.
	 * @credit https://stackoverflow.com/a/14570614
	 */
	observeDOM: function () {
		var MutationObserver =
			window.MutationObserver || window.WebKitMutationObserver

		return function (obj, callback) {
			if (!obj || obj.nodeType !== 1) return

			if (MutationObserver) {
				// define a new observer
				var mutationObserver = new MutationObserver(callback)

				mutationObserver.observe(obj, {
					// have the observer observe foo for changes in children
					// childList: true,
					// subtree: true,
					// @note: we now observe the attributes of the elements instead, i.e. the 'value'
					attributes: true,
					// attributeOldValue: true,
				})
				return mutationObserver
			}

			// browser support fallback
			// else if (window.addEventListener) {
			// 	obj.addEventListener("DOMNodeInserted", callback, false);
			// 	obj.addEventListener("DOMNodeRemoved", callback, false);
			// }
		}
	},

	/**
	 * Listen to selectize changes with respect to product variants previews
	 * We have no access to selectize input change events.
	 * We use a mutation observer to watch for the changes instead.
	 * Selectize will modify the value of a 'display-none' input[type='text'] when items are added/removed from the selectized input.
	 * Mainly used in modal for generating product variants to detect changes in options.
	 */
	initListenToProductGenerateVariants: function () {
		const selectizeInputs = document.querySelectorAll(
			"div#pwcommerce_product_generate_variants_select_options_wrapper input.InputfieldTextTagsSelect"
		)
		// observe each attribute's selectize
		// Observe a specific DOM element:
		// then call callback 'InputfieldPWCommerceProductStock.checkIsReadyForVariants' ON observed mutations
		InputfieldPWCommerceProductStock.initObserveElementsChanges(
			selectizeInputs,
			InputfieldPWCommerceProductStock.checkIsReadyForVariants
		)
	},

	initObserveElementsChanges: function (elements, callback) {
		const observeDOM = InputfieldPWCommerceProductStock.observeDOM()
		// observe each element in elements
		for (const element of elements) {
			// @TODO - DOES NOT WORK
			// InputfieldPWCommerceProductStock.observeDOM(
			// Observe a specific DOM element:
			// observeDOM(element, function (mutationRecord) {
			observeDOM(element, function () {
				//---------------
				// EVERYTIME SELECTIZE CHANGES, WE CHECK IF THEIR HIDDEN TEXT INPUTS HAVE VALUES
				// @note: InputfieldPWCommerceProductStock.checkIsReadyForVariants();
				callback()
			})
		}
	},

	/**
	 * Check if all product attributes with respect to generating variants have options selected.
	 */
	checkIsReadyForVariants: function () {
		// all selectized elements for each product attribute that wants to generate variants.
		const selector =
			"div#pwcommerce_product_generate_variants_select_options_wrapper input.InputfieldTextTagsSelect.selectized"
		const allAttributeOptions = document.querySelectorAll(selector)
		let isReadyForGenerateVariants = true
		for (const attributeOption of allAttributeOptions) {
			// @note: in our case saved as space-separated IDs of option attributes
			const selectedOptions = attributeOption.value
			if (!selectedOptions) {
				isReadyForGenerateVariants = false
				break
			}
		}

		//-----------------------
		// TELL ALPINE IF READY OR NOT FOR GENERATING VARIANTS
		const eventName = "pwcommerceproductgeneratevariantsselectizechange"
		const eventDetail = isReadyForGenerateVariants
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function () {
	if (typeof htmx !== "undefined") {
		// listen to htmx requests
		InputfieldPWCommerceProductStock.listenToHTMXRequests()
		// listen to product variants created, action needed
		InputfieldPWCommerceProductStock.initListenToProductGenerateVariants()
	}
})

// ~~~~~~~~~~~~~

// ALPINE
document.addEventListener("alpine:init", () => {
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (ProcessPWCommerce) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	)
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		Alpine.store("InputfieldPWCommerceProductStockStore", {
			// @todo: delete if not in use
			results: [],

			// @TODO: TESTING TABS
			build_variants_tab: "select_options",

			// VARIANTS PREVIEW
			variants_preview_items: [],

			// MAIN/PARENT PRODUCT DATA
			main_product: {},

			// MODAL
			// @TODO

			// FOR GENERATING/EDITING PRODUCT VARIANTS IN A MODAL
			is_edit_product_variants_modal_open: false,
			// for checking that a product has attributes
			// CHECK IF READY TO GENERATE VARIANTS - all attributes must have at least one option selected for them
			// @note: modified via listening to a custom event @see handleIsReadyForGenerateVariants()
			is_ready_for_generate_variants: false,
			// CHECK IF TO HIDE THE VARIANTS PREVIEW ONLY
			// @note: modified via listening to a custom window event @see handleHideProductGenerateVariantsPreview()
			// @note: here, the 'generate preview' button and 'view instructions' are still visible
			// for use when is not ready to generate variants preview
			is_hide_generate_variants_preview: false,
			//------------------
			// CHECK IF TO HIDE EVERYTHING IN GENERATE VARIANTS PREVIEW
			// @note: here request has been submitted to server to create variants so we hide above buttons and links and modal buttons
			is_creating_variants: false,
			//------------------
			// CHECK IF VARIANTS LIST IS BEING REFRESHED AFTER SUCCESSFUL CREATE IN SERVER
			is_refreshing_variants_list: false,
			//------------------
			// whether to show the generate variants modal instructions or no
			is_show_information_for_generate_variants: false,
			// whether or not to show 'apply' button to save generated variants
			// only want to show when 'generate preview' is clicked
			is_show_apply_generated_variants_button: false,

			// CLIENT ONLY PROPERTIES/VALUES (not saved on server!)
			client: {},
		})
		Alpine.data("InputfieldPWCommerceProductStockData", () => ({
			//---------------
			// FUNCTIONS

			initMainProductData(data) {
				this.setMainProductData(data)
			},

			handleOpenGenerateProductVariantsModal() {
				// first unhide whole generate variants preview and the generate variants preview as they would have been hidden on generate variants + is creating variants
				// @TODO: REFACTOR THIS TO USE ONLY ONE! E.G. IF CREATING, THEN PARENT OF THE PREVIEW IS HIDDEN ANYWAY! I.E. #pwcommerce_product_variants_build_tab_wrapper IS HIDDEN WHEN IS CREATING
				this.setStoreValue("is_hide_generate_variants_preview", false)
				this.setStoreValue("is_creating_variants", false)
				this.handleOpenModal("is_edit_product_variants_modal_open")
			},

			handleCloseModal(property) {
				this.$store.InputfieldPWCommerceProductStockStore[property] = false
			},

			handleOpenModal(property) {
				this.$store.InputfieldPWCommerceProductStockStore[property] = true
			},

			/**
			 * Handle custom event that notifies if selectize inputs for product attributes options selections are all populated.
			 * If yes, we are ready to generate variants preview.
			 * @param {event} event
			 */
			handleIsReadyForGenerateVariants(event) {
				const isReady = event.detail
				this.setStoreValue("is_ready_for_generate_variants", isReady)
				// if not ready, just in case, empty any previously generated values
				if (!isReady) {
					this.emptyVariantsPreviewItems()
				}
			},

			// triggered after the AJAX request to add new variants has completed
			handleHideProductGenerateVariantsPreview(event) {
				// @see runHidePreviewVariantsOperations()
				const isHide = event.detail
				// @TODO: MAY NOT NEED THIS FIRST ONE - DELETE IF NOT IN USE!
				this.setStoreValue("is_hide_generate_variants_preview", isHide)
				this.setStoreValue("is_creating_variants", isHide)
				this.emptyVariantsPreviewItems()
			},

			handleGenerateProductVariantsPreview() {
				// @TODO: NEED TO STOP ABILITY TO GENERATE RIGHT AFTER MODAL IS OPEN! NEED TO CLEAR PREVIOUS VALUES SOMEHOW!
				// if not ready to generate variants (i.e., not all attributes have at least one option selected), return

				// @TODO:CREATE METHOD FOR THIS!
				if (
					!this.$store.InputfieldPWCommerceProductStockStore
						.is_ready_for_generate_variants
				) {
					return
				}

				//--------------
				// @note: we are being rather verbose with out selector! just to be sure
				const selector =
					"div#pwcommerce_product_generate_variants_select_options_wrapper input.InputfieldTextTagsSelect.selectized"
				const allAttributeOptions = document.querySelectorAll(selector)

				// if we didn't find attribute options, abort
				if (!allAttributeOptions) {
					return
				}

				//--------------------
				// good to go, first round. process selected options to create array of arrays
				// we will pass this to cartesianProduct
				const allAttributeOptionsArray =
					this.processAllAttributeOptionsForCartesianProduct(
						allAttributeOptions
					)

				// @TODO: MESSAGE HERE ABOUT NO OPTIONS SELECTED!
				if (!allAttributeOptionsArray) {
					return
				}

				//-------------------
				// GET CARTESIAN PRODUCT OF ALL ATTRIBUTE OPTIONS!
				// @TODO: NEED TO CHECK IF ARRAY EMPTY, I.E. NOT JUST WITH EMPTY ARRAYS AND ABORT!
				const cartesianProductOfAllAttributeOptions = this.cartesianProduct(
					allAttributeOptionsArray
				)

				// @todo: delete when done; we now post-process the items,flattening the object!
				// this.setProductVariantsPreview(cartesianProductOfAllAttributeOptions)

				// POST PROCESS CARTESIAN PRODUCT TO 'FLATTEN' ARRAY
				// we want to end up with one object for each variant
				const productVariantsPreviewItems = this.buildVariantObjects(
					cartesianProductOfAllAttributeOptions
				)

				// add the preview items to the store!
				// this will trigger the x-for loop to display the variants
				this.setProductVariantsPreview(productVariantsPreviewItems)

				// finally, show the 'apply button' for saving variants to the server
				this.setStoreValue("is_show_apply_generated_variants_button", true)
			},

			handleRemoveProductVariantInPreview(index) {
				this.removeVariantPreviewItem(index)
				// check if we have more variants left, if not, hide 'apply button'
				if (!this.getProductVariantsPreview().length) {
					this.setStoreValue("is_show_apply_generated_variants_button", false)
				}
			},

			updateExistingVariantsList() {
				const newlyCreatedVariantsOptionsIDsElement = document.getElementById(
					"pwcommerce_product_created_variants_options_ids"
				)

				if (newlyCreatedVariantsOptionsIDsElement) {
					// got the new values to update existing variants with in main_product
					// get current values (array) at main_product.existing_variants
					// @note: clone, just in case
					const existingVariantsOptionsIDs = [
						...this.getMainProductValue("existing_variants"),
					]

					// get the new values (create array) - @note: pipe-separate; each value is in format 1234,2234,3456, where these are IDs of each attribute option that the variant was built from
					const newVariantsOptionsIDs =
						newlyCreatedVariantsOptionsIDsElement.value.split("|")

					// add to existing values; just loop through
					for (const newVariantOptionIDs of newVariantsOptionsIDs) {
						existingVariantsOptionsIDs.push(newVariantOptionIDs)
					}

					// set back to main_product in store
					this.setMainProductValue(
						"existing_variants",
						existingVariantsOptionsIDs
					)
				}
			},

			toggleShowInformationForGenerateVariants() {
				this.setStoreValue(
					"is_show_information_for_generate_variants",
					!this.getStoreValue("is_show_information_for_generate_variants")
				)
			},

			/**
			 * Empty the variants preview items property.
			 * @returns void
			 */
			emptyVariantsPreviewItems() {
				this.setStoreValue("variants_preview_items", [])
			},

			//~~~~~~~~~~~~~~~~~

			setActiveBuildVariantsTab(current_tab) {
				this.setStoreValue("build_variants_tab", current_tab)
				//---------
			},

			/**
			 * Set a store property value.
			 * @param any value Value to set in store.
			 * @return {void}.
			 */
			setStoreValue(property, value) {
				this.$store.InputfieldPWCommerceProductStockStore[property] = value
			},

			/**
			 * Set the main product object data to given value.
			 * @param any value Value to set as main product data.
			 * @return {void}.
			 */
			setMainProductData(data) {
				this.$store.InputfieldPWCommerceProductStockStore.main_product = data
			},

			/**
			 *Set the value of a given property to the main product object in the products store.
			 * @param string property Main Product's property whose value to set.
			 * @param any value Value to set to property.
			 * @return {void}.
			 */
			setMainProductValue(property, value) {
				this.$store.InputfieldPWCommerceProductStockStore.main_product[property] =
					value
			},

			setProductVariantsPreview(cartesianProductOfAllAttributeOptions) {
				this.$store.InputfieldPWCommerceProductStockStore.variants_preview_items =
					cartesianProductOfAllAttributeOptions
			},

			setIsRefreshingVariantsList(value) {
				this.setStoreValue("is_refreshing_variants_list", value)
			},

			//~~~~~~~~~~~~~~~~~

			/**
			 * Get the the whole products store.
			 * @returns {object}
			 */
			getStore() {
				return this.$store.InputfieldPWCommerceProductStockStore
			},

			/**
			 * Get the value of a given store property.
			 * @param string property Property in store whose value to return
			 * @returns {any}
			 */
			getStoreValue(property) {
				return this.$store.InputfieldPWCommerceProductStockStore[property]
			},

			/**
			 * Get the main product data object.
			 * @return {object}..
			 */
			getMainProductData() {
				return this.$store.InputfieldPWCommerceProductStockStore.main_product
			},

			/**
			 * Get the value of a given main product data property
			 * @param string property Main product property whose value to return
			 * @returns {any}
			 */
			getMainProductValue(property) {
				const mainProductData = this.getMainProductData()
				return mainProductData[property]
			},

			/**
			 * Get the whole preview variants array.
			 * @return {array}..
			 */
			getProductVariantsPreview() {
				return this.$store.InputfieldPWCommerceProductStockStore
					.variants_preview_items
			},

			//~~~~~~~~~~~~~~~~~

			resetProductVariantsAndClose(show_highlight) {
				// @TODO: EMPTY VARIANTS PREVIEW ARRAY? - YES!
				// reset modal collections
				// @TODO
				// close modal
				this.handleCloseModal("is_edit_product_variants_modal_open")

				if (show_highlight === "show") {
					// tell user action happened
					// @todo: change this class!
					this.showHighlight(".InputfieldPWCommerceRuntimeMarkup")
				}
				// @TODO: UNSURE IF THIS IS WORKING?
				// empty variant items in store
				this.setStoreValue("variants_preview_items", [])
				// hide the 'apply button' for saving variants to the server
				this.setStoreValue("is_show_apply_generated_variants_button", false)
				// set variants list refreshing to false
				this.setIsRefreshingVariantsList(false)
				// switch active tab to first one, i.e. Edit Options
				this.setActiveBuildVariantsTab("select_options")
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

			//~~~~~~~~~~~~~~~~~ UTILITIES

			/**
			 * Cartesian product of multiple arrays.
			 * @credit based on https://stackoverflow.com/a/59555849
			 * @param {array} array Array of arrays or of objects to use as input.
			 * @returns {array} Array of arrays or of objects comprising the cartesian product.
			 */
			cartesianProduct(array) {
				if (!array || !Array.isArray(array) || !array.length) {
					return
				}
				/*

      example input
      ----------------
        const array1 = [
          [1, 2],
          [10, 20],
          [100, 200, 300,400],
        ];

        const array2 = [
          ['Red', 'Black'],
          ['Cotton', 'Silk'],
          ['X-Small', 'Medium', 'Small', 'Large'],
        ];

        const array3 = [
          [
            { id: 1, label: "Red" },
            { id: 2, label: "Black" },
          ],
          [
            { id: 10, label: "Cotton" },
            { id: 20, label: "Silk" },
          ],
          [
            { id: 100, label: "X-Small" },
            { id: 200, label: "Medium" },
            { id: 300, label: "Small" },
            { id: 400, label: "Large" },
          ],
        ];

        example output
        ----------------
        const outputArray1 = [
        [
          1,
          10,
          100
        ],
        [
          1,
          10,
          200
        ],
        // .....
      ]

      const outputArray3 = [
      [
        {
          id: 1,
          label: "Red",
        },
        {
          id: 10,
          label: "Cotton",
        },
        {
          id: 100,
          label: "X-Small",
        },
      ],
      [
        {
          id: 1,
          label: "Red",
        },
        {
          id: 10,
          label: "Cotton",
        },
        {
          id: 200,
          label: "Medium",
        },
        ],
        // ...
      ];


      */

				//---------
				return array.reduce(
					(a, b) => a.flatMap((x) => b.map((y) => [...x, y])),
					[[]]
				)
			},

			// @TODO
			processAllAttributeOptionsForCartesianProduct(allAttributeOptions) {
				const allAttributeOptionsArray = []

				for (const attributeOptions of allAttributeOptions) {
					const selectedOptionsIDs = attributeOptions.value.split(" ")

					// console.log(
					// 	"InputfieldPWCommerceProductStock - setActiveBuildVariantsTab - selectedOptionsIDs for this attribute",
					// 	selectedOptionsIDs
					// );

					// skip empty options
					if (!selectedOptionsIDs[0].length) {
						continue
					}

					//--------------------
					// proceed to inner loop and build id and label pairs for each
					const optionLabelsAndIDs = []
					for (const selectedOptionID of selectedOptionsIDs) {
						const labelElement = document.querySelector(
							`[data-value='${selectedOptionID}']`
						)
						if (!labelElement) continue
						//-------------
						// found the element with the label
						// remove the 'x' appended by selectize from the <a></a> for removing elements
						// we also trim the \n before the 'x'
						const label = labelElement.innerText.slice(0, -1).trim()
						// create object and insert in array for this attribute
						// @TODO: WILL EVENTUALLY GET ARRAY OF ARRAYS IN OUTER LOOP!
						optionLabelsAndIDs.push({
							id: selectedOptionID,
							label: label,
						})
					}

					// add all of this attributes options array in main array
					// we'll use it for cartesianProduct as array of arrays of objects
					allAttributeOptionsArray.push(optionLabelsAndIDs)
				}
				//------------
				return allAttributeOptionsArray
			},

			getVariantPreviewNumbering(index) {
				return `#${parseInt(index) + 1}. `
			},

			/**
			 * Join values of properties of an object into one string.
			 * @param {object} object Object to implode.
			 * @param {string} property The object property to join.
			 * @param {string} separator The string to separate the properties.
			 * @param {string} remove Any strings that need to be removed in the final output.
			 * @returns
			 */
			implodeObjectProperties(object, property, separator, remove) {
				if (!object) return
				let propertyValues = []
				for (const item of object) {
					// @TODO: NEED MULTILINGUAL HERE!??
					propertyValues.push(item[property])
				}
				if (remove) {
					return this.stringReplace(
						propertyValues.join(separator),
						remove,
						"" // replace with empty string
					)
				} else return propertyValues.join(separator)
			},

			/**
			 * Return a new String, where the specified value(s) have been replaced by the new value.
			 * Performs a global replacement.
			 * @param {string} string The original string.
			 * @param {string} searchValue The string to search for and replace.
			 * @param {string} newValue The replacement string.
			 */
			stringReplace(string, searchValue, newValue) {
				// @note: string replace would trip on variables inside regex -> would need new RegExp()
				return string.split(searchValue).join(newValue)
			},

			/**
			 * Given a cartesian product of all attribute options that make up a variant, build an array of 'flat' objects representing a variant.
			 * @param {array} cartesianProductOfAllAttributeOptions
			 * @return {array} Array with flat objects, each representing a variant.
			 */
			buildVariantObjects(cartesianProductOfAllAttributeOptions) {
				// @todo: better check here?
				if (!cartesianProductOfAllAttributeOptions) return
				const variantItems = []
				for (const arrayOfOptionsObjects of cartesianProductOfAllAttributeOptions) {
					const variantItem = {
						options_ids: this.implodeObjectProperties(
							arrayOfOptionsObjects,
							"id",
							",",
							"_" // replace underscores added by selectize to IDs of preselected tags
						),
						label: this.implodeObjectProperties(
							arrayOfOptionsObjects,
							"label",
							" / "
						),
						enabled: true,
						price: this.getMainProductValue("product_price"), // set initial value to the main product's price
						sku: "",
						// for use instead of index for bind:names, e.g. pwcommerce_product_variant_preview_title123456788976
						options_ids_for_name_suffix: this.implodeObjectProperties(
							arrayOfOptionsObjects,
							"id",
							"", // nothing to replace
							"_" // replace underscores added by selectize to IDs of preselected tags
						),
					}
					//------------
					variantItems.push(variantItem)
				}

				return variantItems
			},

			removeVariantPreviewItem(variant_index) {
				this.$store.InputfieldPWCommerceProductStockStore.variants_preview_items =
					this.$store.InputfieldPWCommerceProductStockStore.variants_preview_items.filter(
						(_, index) => {
							return index !== variant_index
						}
					)
				// this.$store.InputfieldPWCommerceProductStockStore.variants_preview_items.splice(variant_index, 1)
			},

			// check if all attributes have at least one option selected
			// before we can generate variants for them
			isAllAttributesWithOptions(selectedOptionsArrayLength) {
				const expectedAttributesOptionsLength =
					this.getMainProductValue("attributes_count")
				return selectedOptionsArrayLength === expectedAttributesOptionsLength
			},

			checkVariantAlreadyExists(variant) {
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceProductStock - checkVariantAlreadyExists - variant",
				// 	"info",
				// 	variant
				// )
				const existingVariantsOptionsIDs =
					this.getMainProductValue("existing_variants")
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceProductStock - checkVariantAlreadyExists - existingVariantsOptionsIDs",
				// 	"log",
				// 	existingVariantsOptionsIDs
				// )
				return existingVariantsOptionsIDs.some(
					(optionsIDs) => optionsIDs == variant.options_ids
				)
			},

			// ~~~~~~~~~~~~~~~~~~~~ DEBUG ~~~~~~~~~~~~~~~~~~

			// handleDebugChange(value, property) {
			// 	console.log(
			// 		"InputfieldPWCommerceProductStock - handleDebugChange - property => value",
			// 		`${property} => ${value}`
			// 	);
			// },
		}))
	}
	// end: if in pwcommerce shop context
})

//--------------
