const InputfieldPWCommerceGiftCardProductVariants = {
	listenToHTMXRequests: function () {
		// after request
		htmx.on("htmx:afterRequest", function (event) {
			// @note: triggered after an AJAX request has completed
			// @see: https://htmx.org/events/#htmx:afterRequest
			// RUN POST REQUEST OPS
			InputfieldPWCommerceGiftCardProductVariants.runAfterRequestOperations(event)
			//------------
		})

		// after settle
		htmx.on("htmx:afterSettle", function (event) {
			// RUN POST SETTLE OPS
			InputfieldPWCommerceGiftCardProductVariants.runAfterSettleOperations(event)
		})
	},

	runAfterRequestOperations: function (event) {
		const targetElement = event.detail.target

		// @note: SPECIAL CASE FOR variants create request has been issued, now we need to hide the preview variants view
		if (
			targetElement.id === "pwcommerce_product_generated_variants_preview_wrapper"
		) {
			InputfieldPWCommerceGiftCardProductVariants.runHidePreviewVariantsOperations()
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
			InputfieldPWCommerceGiftCardProductVariants.runProductGenerateVariantsIsFinished()
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
			InputfieldPWCommerceGiftCardProductVariants.runProductVariantsHaveBeenCreatedPostOperations(
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
		// 	"InputfieldPWCommerceGiftCardProductVariants - runProductVariantsHaveBeenCreatedPostOperations - event",
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
		// 	"InputfieldPWCommerceGiftCardProductVariants - runProductVariantsHaveBeenCreatedPostOperations - productVariantsCreationOutcomeElement - SUCCESS OR ERROR?",
		// 	"log",
		// 	productVariantsCreationOutcomeElement
		// )

		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceGiftCardProductVariants - runProductVariantsHaveBeenCreatedPostOperations - productVariantsCreationOutcomeElement.dataset.noticeType - SUCCESS OR ERROR IN NOTICE TYPE?",
		// 	"warn",
		// 	productVariantsCreationOutcomeElement.dataset.noticeType
		// )
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceGiftCardProductVariants - runProductVariantsHaveBeenCreatedPostOperations - productVariantsCreationOutcomeElement.dataset.noticeType - SUCCESS OR ERROR IN NOTICE TYPE EQUALITY?",
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
			// to tell InputfieldPWCommerceGiftCardProductVariants to display message that variants list is refreshing (via ALPINE.JS)
			// @TODO DELETE WHEN DONE
			//const elem2 = document.getElementById("pwcommerce_product_main_edit_modals")
			// @note: no htmx involvement here - just a custom Window Event
			const eventName2 = "pwcommerceproductisrefreshingvariantslist"

			// ----------------
			// to tell InputfieldPWCommerceGiftCardProductVariants to update the array of 'existing_variants' to include the newly created ones (since page not yet reloaded, so still preserving outdated values) (via ALPINE.JS)
			//const elem3 = document.getElementById("pwcommerce_product_variants_is_refreshing_list_wrapper")
			// @note: no htmx involvement here - just a custom Window Event
			const eventName3 = "pwcommerceproductupdateexistingvariantslist"

			// @note: this works! can access store externally!
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceGiftCardProductVariants - runProductVariantsHaveBeenCreatedPostOperations - TRYING TO ACCESS ALPINE STORE FROM WITHIN SCRIPT EXTERNAL TO IT! - Alpine.store('InputfieldPWCommerceGiftCardProductVariantsStore').manually_issue_gift_card_data",
			// 	"warn",
			// 	Alpine.store("InputfieldPWCommerceGiftCardProductVariantsStore").manually_issue_gift_card_data
			// )

			// @NOTE: CANNOT ACCESS Alpine.data() externally!
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceGiftCardProductVariants - runProductVariantsHaveBeenCreatedPostOperations - TRYING TO ACCESS ALPINE DATA FROM WITHIN SCRIPT EXTERNAL TO IT! - Alpine.data('InputfieldPWCommerceGiftCardProductVariantsStore').testCanAccessFromExternalScipt('HELLO SECRET MESSAGE!')",
			// 	"warn",
			// 	Alpine.data(
			// 		"InputfieldPWCommerceGiftCardProductVariantsData"
			// 	)
			// )

			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceGiftCardProductVariants - runProductVariantsHaveBeenCreatedPostOperations - SUCCESS OUTCOME! - sending this eventName using htmx.trigger(elem,eventName)",
			// 	"log",
			// 	eventName
			// )
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceGiftCardProductVariants - runProductVariantsHaveBeenCreatedPostOperations - SUCCESS OUTCOME! - TELL ALPINE TO SHOW IS REFRESHING MESSAGE - sending this eventName2 using WINDOW EVENT CUSTOM EVENT",
			// 	"info",
			// 	eventName2
			// )
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceGiftCardProductVariants - runProductVariantsHaveBeenCreatedPostOperations - SUCCESS OUTCOME! - TELL ALPINE TO UPDATE LIST OF EXISTING VARIANTS BASED ON THE NEWLY CREATED ONES! - sending this eventName3 using WINDOW EVENT CUSTOM EVENT",
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
		// then call callback 'InputfieldPWCommerceGiftCardProductVariants.checkIsReadyForVariants' ON observed mutations
		InputfieldPWCommerceGiftCardProductVariants.initObserveElementsChanges(
			selectizeInputs,
			InputfieldPWCommerceGiftCardProductVariants.checkIsReadyForVariants
		)
	},

	initObserveElementsChanges: function (elements, callback) {
		const observeDOM = InputfieldPWCommerceGiftCardProductVariants.observeDOM()
		// observe each element in elements
		for (const element of elements) {
			// @TODO - DOES NOT WORK
			// InputfieldPWCommerceGiftCardProductVariants.observeDOM(
			// Observe a specific DOM element:
			// observeDOM(element, function (mutationRecord) {
			observeDOM(element, function () {
				//---------------
				// EVERYTIME SELECTIZE CHANGES, WE CHECK IF THEIR HIDDEN TEXT INPUTS HAVE VALUES
				// @note: InputfieldPWCommerceGiftCardProductVariants.checkIsReadyForVariants();
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
	// @TODO DELETE IF NO LONGER USING HTMX!
	if (typeof htmx !== "undefined") {
		// console.log("WE GOT HTMX!")
		// listen to htmx requests
		InputfieldPWCommerceGiftCardProductVariants.listenToHTMXRequests()
		// listen to product variants created, action needed
		InputfieldPWCommerceGiftCardProductVariants.initListenToProductGenerateVariants()
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
		Alpine.store("InputfieldPWCommerceGiftCardProductVariantsStore", {
			// MANUALLY ISSUE GIFT CARD DATA
			manually_issue_gift_card_data: {},
			is_open_issue_gift_card_accordion: false,
			is_open_issue_gift_card_processing_modal: false,
			// error states
			is_error_issue_gift_card_email: false,
			is_error_issue_gift_card_start_date: false,
			is_error_issue_gift_card_end_date: false,
			// error texts
			issue_gift_card_dates_error_strings: {}, // @note: translated server-side
			issue_gift_card_start_date_error_text: null,
			issue_gift_card_end_date_error_text: null,
		})
		Alpine.data("InputfieldPWCommerceGiftCardProductVariantsData", () => ({
			//---------------
			// FUNCTIONS

			// ~~~~~~~~~~~~~~~~~

			// SPECIAL FOR INVENTORY INLINE EDIT

			/**
			 * Init data for inventory.
			 * @return {void}.
			 */
			initIssueGiftCardDatesErrorStringsData() {
				this.setAllIssueGiftCardDatesErrorStringsData()
			},

			handleOpenIssueGiftCardAccordion() {
				const isOpenGiftCardAccordion = this.getStoreValue(
					"is_open_issue_gift_card_accordion"
				)
				// toggle open/close issue gift card accordion
				this.setStoreValue(
					"is_open_issue_gift_card_accordion",
					!isOpenGiftCardAccordion
				)
			},

			handleOpenIssueGiftCardModal() {
				const issueGiftCardDenominationElem =
					this.$refs.pwcommerce_issue_gift_card_denomination
				const issueGiftCardCustomerEmailElem =
					this.$refs.pwcommerce_issue_gift_card_customer_email
				const issueGiftCardStartDateElem =
					this.$refs.pwcommerce_issue_gift_card_start_date
				const issueGiftCardEndDateElem =
					this.$refs.pwcommerce_issue_gift_card_end_date

				// ---------
				// VALIDATIONS
				// @note: this returns 'is valid' so we check for the opposite here
				// this is because the store property is about 'is_error...'
				// @TODO NEED TO FIX JS ISSUE; IF EMAIL INVALID THEN WE CHANGE VALUE, THIS BUTTON IS NOT SHOWN AGAIN!
				const isValidCustomerEmail = this.validateEmailAddress(
					issueGiftCardCustomerEmailElem
				)

				// @NOTE: setting 'invalid'!
				this.setStoreValue(
					"is_error_issue_gift_card_email",
					!isValidCustomerEmail
				)

				const isValidDates = this.validateIssueGiftCardDates(
					issueGiftCardStartDateElem,
					issueGiftCardEndDateElem
				)

				if (isValidCustomerEmail && isValidDates) {
					// VALID EMAIL & DATES: OPEN MODAL AND FIRE EVENT TO HTMX
					// @TODO htmx CUSTOM EVENT
					// -----
					// OPEN MODAL ready for htmx messages
					// @note: could result in server-error still!
					// @NOTE: htmx will fire an event for Alpine JS to listen to
					// if success, we clearn previous issue gift card form and close accordion
					// @TODO !
					this.handleIssueGiftCardStartProcessing(
						issueGiftCardDenominationElem,
						issueGiftCardStartDateElem,
						issueGiftCardEndDateElem
					)
				}
			},

			// @todo we now change this for dates validation!!! we will use vanilla js
			handleIssueGiftCardStartProcessing(
				issueGiftCardDenominationElem,
				issueGiftCardStartDateElem,
				issueGiftCardEndDateElem
			) {
				console.log(
					"handleIssueGiftCardStartProcessing",
					"OPEN MODAL + fire custom event to htmx"
				)

				// manually set some values
				const manuallyIssueGiftCardData = this.getStoreValue(
					"manually_issue_gift_card_data"
				)

				// set denomination value since their select models the ID of the selected denomination
				// i.e., the 'text' of the selected option
				manuallyIssueGiftCardData["denomination"] =
					issueGiftCardDenominationElem.options[
						issueGiftCardDenominationElem.selectedIndex
					].text
				// set jquery datetime values to Alpine store since we cannot model them directly
				// @todo translated strings if no dates?

				manuallyIssueGiftCardData["startDate"] =
					issueGiftCardStartDateElem.value
				manuallyIssueGiftCardData["endDate"] = issueGiftCardEndDateElem.value
				this.setStoreValue(
					"manually_issue_gift_card_data",
					manuallyIssueGiftCardData
				)

				// ---------
				// OPEN CONFIRM ISSUE GIFT CARD MODAL
				this.setStoreValue("is_open_issue_gift_card_processing_modal", true)
			},

			handleCloseModal(property) {
				this.setStoreValue(property, false)
			},

			handleOpenModal(property) {
				this.setStoreValue(property, true)
			},

			//~~~~~~~~~~~~~~~~~
			/**
			 *Get the ProcessWire config sent for gift card product (and variants) error strings.
			 * @return object.
			 */
			getProcessWireGiftCardProductConfig() {
				return ProcessWire.config.InputfieldPWCommerceGiftCardProductVariants
			},

			getIssueGiftCardDatesErrorStrings() {
				return this.getStoreValue("issue_gift_card_dates_error_strings")
			},
			//~~~~~~~~~~~~~~~~~

			// ------
			/**
			 * Set all the gift card product (and variants) data.
			 *
			 * @return {void}.
			 */
			setAllIssueGiftCardDatesErrorStringsData() {
				const errorStringsData = this.getProcessWireGiftCardProductConfig()
				this.setStoreValue(
					"issue_gift_card_dates_error_strings",
					errorStringsData
				)
			},
			//~~~~~~~~~~~~~~~~~

			/**
			 * Set a store property value.
			 * @param any value Value to set in store.
			 * @return {void}.
			 */
			setStoreValue(property, value) {
				this.$store.InputfieldPWCommerceGiftCardProductVariantsStore[property] =
					value
			},

			//~~~~~~~~~~~~~~~~~

			/**
			 * Get the the whole products store.
			 * @returns {object}
			 */
			getStore() {
				return this.$store.InputfieldPWCommerceGiftCardProductVariantsStore
			},

			/**
			 * Get the value of a given store property.
			 * @param string property Property in store whose value to return
			 * @returns {any}
			 */
			getStoreValue(property) {
				return this.$store.InputfieldPWCommerceGiftCardProductVariantsStore[
					property
				]
			},

			/**
			 * Get the main product data object.
			 * @return {object}..
			 */
			getMainProductData() {
				return this.$store.InputfieldPWCommerceGiftCardProductVariantsStore
					.manually_issue_gift_card_data
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

			//~~~~~~~~~~~~~~~~~

			resetIssueGiftCardAndClose(show_highlight) {
				// reset ISSUE GIFT CARD FORM AND related VALUES
				// @TODO
				// close modal
				this.handleCloseModal("is_open_issue_gift_card_processing_modal")

				if (show_highlight === "show") {
					// tell user action happened
					// @todo: change this class!
					this.showHighlight(".InputfieldPWCommerceRuntimeMarkup")
				}
				// @TODO: UNSURE IF THIS IS WORKING?
				// // empty variant items in store
				// this.setStoreValue("variants_preview_items", [])
				// // hide the 'apply button' for saving variants to the server
				// this.setStoreValue("is_show_apply_generated_variants_button", false)
				// // set variants list refreshing to false
				// this.setIsRefreshingVariantsList(false)
				// // switch active tab to first one, i.e. Edit Options
				// this.setActiveBuildVariantsTab("select_options")
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

			validateEmailAddress(input) {
				const validRegexPattern = this.getValidEmailRegexPattern()
				let isValidEmailAddress = false
				if (input.value.match(validRegexPattern)) {
					isValidEmailAddress = true
				}
				return isValidEmailAddress
			},

			validateIssueGiftCardDates(
				issueGiftCardStartDateElem,
				issueGiftCardEndDateElem
			) {
				const giftCardStartDateStr = issueGiftCardStartDateElem.value
				const giftCardEndDateStr = issueGiftCardEndDateElem.value

				// @note get timestamp but if NaN then 'zero'
				const giftCardStartDate =
					this.convertStringDateToTimestamp(giftCardStartDateStr) || 0
				const giftCardEndDate =
					this.convertStringDateToTimestamp(giftCardEndDateStr) || 0
				const giftCardNow = Math.floor(Date.now() / 1000)
				const issueGiftCardDatesErrorStrings =
					this.getIssueGiftCardDatesErrorStrings()

				// @TODO SHOULD WE DO SEPARATE FOR START AND END? THIS IS BECAUSE IN SOME CASES WE NEED SEPARATE MESSAGES FOR THEM
				/*
					a. start date > end date <-invalid
					b. start date == end date <-invalid
					ab: start date >= end date <-invalid #DONE#
					~~~~ PAST DATES ~~~~
					c. start date < now/today date <-invalid
					d. end date < now/today date <-invalid
					cd: @todo check independently since both can be true and need to show errors for each
					~~~~ ONE DATE EMPTY & ONE NOT ~~~~
					e. start date NOT EMPTY & end date EMPTY <- invalid {COVERED BY ab?} #DONE#
					f. start date EMPTY & end date NOT EMPTY <- invalid
					ef: @todo although only one can be true, will need to set errors for each??? NO! WILL JUST SHOW ON THE DATE HAS THE ERROR!
				*/

				// *** VALIDATION START ****

				let isValidDates = true
				let startDateError, endDateError

				// for start date
				const startDateErrorTextProperty =
					"issue_gift_card_start_date_error_text"
				const startDateErrorFlagProperty = "is_error_issue_gift_card_start_date"
				// -----
				// for end date
				const endDateErrorTextProperty = "issue_gift_card_end_date_error_text"
				const endDateErrorFlagProperty = "is_error_issue_gift_card_end_date"

				// #########
				if (giftCardStartDate === 0 && giftCardEndDate === 0) {
					// **VALID**: DATES NOT IN USE
					// nothing to do
					// isValidDates = true
					// console.log("isValidDates: TRUE - DATES NOT IN USE", isValidDates)
				} else if (giftCardStartDate && !giftCardEndDate) {
					// **INVALID**: START DATE GIVEN BUT END DATE IS EMPTY
					isValidDates = false
					// ~~~~~~~~~~~~
					// get END date 'EMPTY BUT START DATE GIVEN' error message
					// @NOTE: here we show the error on "opposite's date" error message!
					endDateError =
						issueGiftCardDatesErrorStrings.start_date_given_but_no_end_date
				} else if (!giftCardStartDate && giftCardEndDate) {
					// **INVALID**:  START DATE IS EMPTY BUT END DATE GIVEN
					isValidDates = false
					// ~~~~~~~~~~~~
					// get START date 'EMPTY BUT END DATE GIVEN' error message
					// @NOTE: here we show the error on "opposite's date" error message!
					startDateError =
						issueGiftCardDatesErrorStrings.end_date_given_but_no_start_date
				} else if (
					giftCardStartDate < giftCardNow ||
					giftCardEndDate < giftCardNow
				) {
					// **INVALID**: START OR END DATE IS IN THE PAST (LESS THAN NOW)
					// @NOTE: we set error for both since mistake could be in either start or end

					isValidDates = false
					// ------
					if (giftCardStartDate < giftCardNow) {
						// **INVALID**: START DATE IS IN THE PAST (LESS THAN NOW)
						// ~~~~~~~~~~~~
						// get start date 'IN THE PAST' error message
						startDateError =
							issueGiftCardDatesErrorStrings.start_date_is_in_the_past
					}
					if (giftCardEndDate < giftCardNow) {
						// **INVALID**: END DATE IS IN THE PAST (LESS THAN NOW)
						// ~~~~~~~~~~~~
						// get end date 'IN THE PAST' error message
						// @NOTE: we set error for both since mistake could be in either start or end
						endDateError =
							issueGiftCardDatesErrorStrings.end_date_is_in_the_past
					}
				} else if (giftCardStartDate >= giftCardEndDate) {
					// **INVALID**: START DATE GREATER THAN OR EQUAL TO END DATE
					isValidDates = false
					// ~~~~~~~~~~~~
					// get 'GREATER/EQUAL/LESS THAN' error messages
					// @NOTE: we set error for both since mistake could be in either start or end
					startDateError =
						issueGiftCardDatesErrorStrings.start_date_greater_than_or_equal_to_end_date
					endDateError =
						issueGiftCardDatesErrorStrings.end_date_less_than_or_equal_to_start_date
				}

				// --------

				// ++++++ handle START DATE errors  ++++++

				if (startDateError) {
					// set START DATE error text
					this.setStoreValue(startDateErrorTextProperty, startDateError)
					// set START DATE SHOW ERROR MESSAGE 'TRUE'
					this.setStoreValue(startDateErrorFlagProperty, true)
				} else {
					// clear old error text if needed
					this.setStoreValue(startDateErrorTextProperty, null)
					// set SHOW ERROR MESSAGES FLAGS TO 'FALSE'
					this.setStoreValue(startDateErrorFlagProperty, false)
				}

				// ++++++ handle END DATE errors  ++++++

				if (endDateError) {
					// set END DATE error text
					this.setStoreValue(endDateErrorTextProperty, endDateError)
					// set END DATE SHOW ERROR MESSAGE 'TRUE'
					this.setStoreValue(endDateErrorFlagProperty, true)
				} else {
					// clear old error text if needed
					this.setStoreValue(endDateErrorTextProperty, null)
					// set SHOW ERROR MESSAGES FLAGS TO 'FALSE'
					this.setStoreValue(endDateErrorFlagProperty, false)
				}

				// console.log("isValidDates", isValidDates)

				// -----
				return isValidDates
			},

			getValidEmailRegexPattern() {
				// @note: compliant with the RFC-2822 spec for email addresses.
				// @credits: @see https://masteringjs.io/tutorials/fundamentals/email-regex
				const pattern =
					/(?:[a-z0-9+!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/i
				return pattern
			},

			convertStringDateToTimestamp(strDate) {
				const dt = Date.parse(strDate)
				return dt / 1000
			},

			// ~~~~~~~~~~~~~~~~~~~~ DEBUG ~~~~~~~~~~~~~~~~~~

			// handleDebugChange(value, property) {
			// 	console.log(
			// 		"InputfieldPWCommerceGiftCardProductVariants - handleDebugChange - property => value",
			// 		`${property} => ${value}`
			// 	);
			// },
		}))
	}
	// end: if in pwcommerce shop context
})

//--------------
