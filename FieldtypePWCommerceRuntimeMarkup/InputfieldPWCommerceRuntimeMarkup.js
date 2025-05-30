const InputfieldPWCommerceRuntimeMarkup = {
	listenToHTMXRequests: function () {
		// before request
		htmx.on("htmx:beforeRequest", function (event) {
			// check if to cancel product variant dynamic loading
			// const eventName = "pwcommerceproductvariantdynamicloading"
			// const eventDetail = false
			// @note: method is in PWCommerceCommonScripts.js
			// PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceRuntimeMarkup -htmx:beforeRequest -  event",
			// 	"info",
			// 	event.target
			// )
			// htmx.trigger("#request-button", "htmx:abort")
			// htmx.trigger(`#${event.target.id}`, "htmx:abort")
			//
			const triggerElement = event.target
			if (triggerElement.classList.contains("InputfieldFieldset")) {
				// ONLY ON INPUTFIELDSET! i.e. ignore <a> triggers such as add shipping rate.
				if (!triggerElement.classList.contains("pwcommerce_dynamic_loading")) {
					// PWCommerceCommonScripts.debugger(
					// 	"InputfieldPWCommerceRuntimeMarkup -htmx:beforeRequest -  triggerElement NOT FOR AJAX DYNAMIC LOADING - ABORT HTMX",
					// 	"info",
					// 	event.target.id
					// )
					// @TODO @NOTE DOES NOT WORK
					// htmx.trigger(`#${triggerElement.id}`, "htmx:abort")
					// htmx.trigger(event.target, "htmx:abort")
					event.preventDefault()
				}
			}
		})

		// after settle
		htmx.on("htmx:afterSettle", function (event) {
			// @TODO TESTING DYNAMIC LOADING FOR PRODUCT VARIANTS!
			// @TODO - BELOW WORKS -> IN FUTURE, REFACTOR TO MAKE CALLER CLEANER!
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceRuntimeMarkup - htmx:afterSettle -  event",
			// 	"info",
			// 	event
			// )

			// get the fieldset that has been dynamically populated
			// we need to remove the class 'pwcommerce_dynamic_loading' so that it will not trigger dynamic loading via htmx again
			const productVariantFieldsetNowOpenedElement = event.target.closest(
				"li.pwcommerce_dynamic_loading"
			)

			// console.log(
			// 	"InputfieldPWCommerceRuntimeMarkup - htmx:afterSettle - productVariantFieldsetNowOpenedElement",
			// 	productVariantFieldsetNowOpenedElement
			// )
			if (productVariantFieldsetNowOpenedElement) {
				// console.log(
				// 	"InputfieldPWCommerceRuntimeMarkup - htmx:afterSettle - productVariantFieldsetNowOpenedElement - REMOVE THE DYNAMIC LOADING CLASS",
				// 	productVariantFieldsetNowOpenedElement.classList.contains(
				// 		"pwcommerce_dynamic_loading"
				// 	)
				// )
				productVariantFieldsetNowOpenedElement.classList.remove(
					"pwcommerce_dynamic_loading"
				)

				// @TODO TRYING TO FIX INPUTFIELDTEXTTAGS NOT RELOADING! - for variants downloads!
				// @TODO - BELOW WORKS -> IN FUTURE, REFACTOR TO MAKE CALLER CLEANER!
				// @TODO -> IDEALLY MOVE BELOW TO THE AUTO RELOAD VIA PWCommerceCommonScripts. Maybe pass an argument for that?
				// console.log(
				// 	"InputfieldPWCommerceRuntimeMarkup - htmx:afterSettle - productVariantFieldsetNowOpenedElement.id",
				// 	productVariantFieldsetNowOpenedElement.id
				// )
				// PWCommerceCommonScripts
				// @note: unfortunately, we need jQuery for this!
				const dynamicLoadedContentMainFieldset = $(document).find(
					".InputfieldPWCommerceRuntimeMarkup"
				)
				// console.log(
				// 	"InputfieldPWCommerceRuntimeMarkup - htmx:afterSettle - dynamicLoadedContentMainFieldset",
				// 	dynamicLoadedContentMainFieldset
				// )
				const selectorForNewlyDynamicLoadedContentInputfieldTextTagsElements = `#${productVariantFieldsetNowOpenedElement.id} .InputfieldPage`
				// console.log(
				// 	"InputfieldPWCommerceRuntimeMarkup - htmx:afterSettle - selectorForNewlyDynamicLoadedContentInputfieldTextTagsElements",
				// 	selectorForNewlyDynamicLoadedContentInputfieldTextTagsElements
				// )
				// TRIGGER RELOAD: InputfieldTextTags
				PWCommerceCommonScripts.reloadInputfieldTextTags(
					dynamicLoadedContentMainFieldset,
					selectorForNewlyDynamicLoadedContentInputfieldTextTagsElements
				)
			}

			// >>>>>>>>>
			// RUN POST SETTLE OPS
			InputfieldPWCommerceRuntimeMarkup.runAfterSettleOperations(event)

			//------------
			// TRIGGER RELOAD OF  PROCESSWIRE INPUTFIELDS  IF REQUIRED BY THE TRIGGERING HTMX EVENT
			// also re-parent newly inserted items in this case since will be li.Inputfield and these need to live under their ul.Inputfields
			if (InputfieldPWCommerceRuntimeMarkup.isInputfieldsReloadRequired(event)) {
				//  RE-PARENT NEWLY INSERTED ITEM (li)
				// it needs to live under a <ul class='Inputfields'></ul>
				// @todo: need to cater for multiple inserts as well!
				InputfieldPWCommerceRuntimeMarkup.reparentNewInputfields(event)

				//-----------
				// TRIGGER RELOADS
				// @note: method is in PWCommerceCommonScripts.js
				// @TODO @UPDATE: THE ISSUE WITH RELOADING INPUTFIELD TEXT TAGS SEEMS TO HAVE BEEN INTRODUCED IN PROCESSWIRE 3.0.184. DOWGRADING TO 3.0.181 MAKES THE ISSUE DISAPPEAR
				// @TODO @UPDATE: TUESDAY 7 SEPTEMBER 2021: THE ISSUE WITH TEXT TAGS => IT SEEMS, WHEN WE CREATE VARIANTS, THE FIRST TIME, THERE IS NO ERROR. HOWEVER, IF WE ADD MORE VARIANTS (WITHOUT THE PAGE BEING RELOADED), WE GET THE ERROR, i.e. 'Unexpected token u in JSON at position 0' MEANING, RELOADING THE SECOND TIME ROUND. ONLY THING I CAN THINK OF IS TO TRY AND RELOAD ONLY THE LAST ADDED; NOT ALL WITH THE CLASS 'pwcommerce_is_new_item'. Can we remove the class after reparenting? or, should we be sending with some latest timestamp and only target those? With the remove class though, we need it in some cases, when we focus inputs! so, instead of remove the class, we can add a class 'pwcommerce_has_been_reloaded' and ignore those but we might have a race condition between reload and reparenting! However, we don't use focus input if in products context! so, can get away with that? either way, we will have to either remove or add a class! can do so after last reload? as  post op?
				PWCommerceCommonScripts.triggerReloadProcessWireInputfields(event)
			}
		})
	},

	/**
	 * Run afterSettle operations (after htmx swap).
	 * These depend on the htmx request context.
	 * Use this so that alpine js can work on 'settled' dom contents.
	 * @param {object} event Object containing the event that triggered the request or custom object with post-op details.
	 */
	runAfterSettleOperations: function (event) {
		// get markup (ul) of response HTML sent by server
		const responseMarkup = event.detail.elt.lastChild

		if (!responseMarkup) return
		// -------------
		// CHECK IF AFTER SETTLE SPECIFIC OPERATIONS REQUIRED
		// e.g. open newly inserted fieldset, focus an element, etc
		if (
			InputfieldPWCommerceRuntimeMarkup.isRunAfterSettleOperationsRequired(event)
		) {
			InputfieldPWCommerceRuntimeMarkup.runSpecificAfterSettleOperation(event)
		}
	},

	runSpecificAfterSettleOperation: function (event) {
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceRuntimeMarkup - runSpecificAfterSettleOperation -  DETERMINE WHICH POST SETTLE OPERATION TO RUN"
		// );
		const triggerElement = event.detail.requestConfig.elt

		// get the new FIELDSET/INPUTFIELD
		const newFieldset = document.querySelector(
			//".pwcommerce_is_new_runtime_markup_item:last-child"
			// @TODO MAKING MORE GENERIC CLASS NAME!
			".pwcommerce_is_new_item:last-child"
		)

		// @note/@todo: for now, we only do 'open fieldset' POST-OP

		// RUN OPEN NEW FIELDSET
		// used for newly added/created fieldsets
		if (
			triggerElement.classList.contains(
				"pwcommerce_open_newly_created_inputfieldset"
			)
		) {
			//----------------
			// open the new fieldset (single, last newly inserted fieldset)
			// Inputfields.open(newFieldset)
			// @note: we need jQuery (sigh!!) since required by Inputfields below
			Inputfields.open($(newFieldset))
			// Inputfields.toggle(newFieldset)
		} // END: IF OPEN NEW FIELDSET

		//----------------
		// If in shipping zone edit, re-init shipping rate criteria type change ON NEWLY ADDED RATES (htmx)
		// @TODO REFACTOR IN FUTURE TO ONLY TARGET NEWLY ADDED ELEMENT
		InputfieldPWCommerceRuntimeMarkup.initListenToShippingRateCriteriaType()

		// ---------------

		// FOCUS AN INPUT IN THE NEW FIELDSET/INPUTFIELD
		if (
			triggerElement.classList.contains(
				"pwcommerce_focus_input_in_newly_created_inputfield"
			)
		) {
			this.focusInputInNewInputfield(event)
		}
	},

	// check if a htmx request included a  'refresh' runtimemarkup class
	// if true, will trigger reloading of ProcessWire inputfields after settle insert new markup items to DOM by htmx
	isInputfieldsReloadRequired: function (event) {
		const triggerElement = event.detail.requestConfig.elt
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceRuntimeMarkup -isInputfieldsReloadRequired -  triggerElement",
		// 	"log",
		// 	triggerElement
		// )
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceRuntimeMarkup -isInputfieldsReloadRequired -  triggerElement.classList.Contains('pwcommerce_reload_inputfield_runtimemarkup_list')",
		// 	"log",
		// 	triggerElement.classList.contains(
		// 		"pwcommerce_reload_inputfield_runtimemarkup_list"
		// 	)
		// )
		return triggerElement.classList.contains(
			"pwcommerce_reload_inputfield_runtimemarkup_list"
		)
	},

	// check if a htmx request included a special 'run-after-settle-post-operations' runtimemarkup class
	// if true, will trigger checking of predefined operation to run, e.g. 'open newly created fieldset'
	// e.g., for use by InputfieldPWCommerceShippingRate or InputfieldPWCommerceAttributeOptions (a virtual field)
	isRunAfterSettleOperationsRequired: function (event) {
		const triggerElement = event.detail.requestConfig.elt
		return triggerElement.classList.contains(
			"pwcommerce_run_after_settle_operations"
		)
	},

	// ~~~~~~~~~~~

	// used by contexts that add items to runtimemarkup list - e.g add attribute options, add shipping rate and generate product variants
	// these come in under their own (incorrect) ul parents
	// we reparent them here, adding them to the existing ul (correct/new parent) then discard the 'incorrect' and now empty parent.
	reparentNewInputfields: function (event) {
		// the variants parent: ul.Inputfields
		const newParentElement = event.detail.elt
		// the ul.Inputfields that came with the new li.InputfieldFieldset.InputfieldPWCommerceRuntimeMarkupItem.pwcommerce_is_new_runtime_markup_item
		// @note: this is the 'incorrect' parent as we end up with <li>existing variant</li><ul><li>new variant</li>...</ul>
		// @note: this old (incorrect) parent is the last child of the correct/new parent above
		const oldParentElement = event.detail.elt.lastElementChild
		// the li.InputfieldFieldset.InputfieldPWCommerceRuntimeMarkupItem.pwcommerce_is_new_runtime_markup_item
		//const children = event.detail.elt.lastElementChild.children
		const children = oldParentElement.children

		if (children) {
			// append them!
			// @note: we were having problems with looping (only 1 child getting appended) but spread syntax works. maybe it was a race condition?
			newParentElement.append(...oldParentElement.childNodes)
			// remove their 'incorrect/old parent'
			oldParentElement.remove()
		}
	},

	// ----------------

	focusInputInNewInputfield: function (event) {
		// FOCUS THE PAGE TITLE INPUT OR FIRST INPUT FOR NAME IN TAX RATE CONTEXT FOR COUNTRY TERRITORY TAX ABBREVIATION/NAME, ATTRIBUTE OPTION OR SHIPPING RATE
		// also remove default title for new item if Attribute Options or Shipping Rate

		// get current pwcommerce shop context
		const pwcommerceShopCurrentContext =
			PWCommerceCommonScripts.getPWCommerceShopCurrentContext()
		// PWCommerceCommonScripts.debugger(
		// 	"InputfieldPWCommerceRuntimeMarkup - pwcommerceShopCurrentContext",
		// 	"log",
		// 	pwcommerceShopCurrentContext
		// )

		let focusElementSelector
		let isAddPlaceholder = false
		// determine if focusing an InputfieldPageTitle (most cases) or .pwcommerce_tax_rate_name  in the case of tax-rates context
		if (pwcommerceShopCurrentContext === "tax-rates") {
			// @note: also works
			// focusElementSelector = "input.pwcommerce_tax_rate_name";
			focusElementSelector = "li.pwcommerce_is_new_item input:first-child"
		} else {
			focusElementSelector = ".InputfieldPageTitle input"
			isAddPlaceholder = true
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceRuntimeMarkup - focusInputInNewInputfield - focusElementSelector - FOCUSING TITLE",
			// 	"log",
			// 	focusElementSelector
			// )
		}

		//--------
		// grap the focus input
		const focusElement =
			event.detail.elt.lastElementChild.querySelector(focusElementSelector)

		//------------------
		if (focusElement) {
			// @TODO: WE NEED TO REMOVE THE TITLE FROM VALUE AND ADD THAT TO ITS PLACEHOLDER
			// PWCommerceCommonScripts.debugger(
			// 	"InputfieldPWCommerceRuntimeMarkup - focusInputInNewInputfield - focusElement.value",
			// 	"log",
			// 	focusElement.value
			// )
			if (isAddPlaceholder) {
				// if setting placeholder for new items
				// remove the 'generic value' from server
				// then add a generic placeholder
				// -------------
				const translatedStrings =
					this.getInputfieldPWCommerceRuntimeMarkupTranslatedStrings()
				const placeholder = translatedStrings.enter_title
				// PWCommerceCommonScripts.debugger(
				// 	"InputfieldPWCommerceRuntimeMarkup - focusInputInNewInputfield - placeholder",
				// 	"log",
				// 	placeholder
				// )
				focusElement.setAttribute("value", "")
				// @todo: get translated value from
				focusElement.setAttribute("placeholder", placeholder)
			}

			// if we have an element, focus it after a few ms to allow for settling
			setTimeout(() => {
				focusElement.focus()
			}, 300)
		}
	},

	/**
	 * Get the ProcessWire config sent for this Inputfield.
	 * @returns object.
	 */
	getProcessWireInputfieldPWCommerceRuntimeMarkupConfig: function () {
		return ProcessWire.config.InputfieldPWCommerceRuntimeMarkup
	},

	/**
	 * Get the ProcessWire config translated strings sent for this Inputfield.
	 * @returns object.
	 */
	getInputfieldPWCommerceRuntimeMarkupTranslatedStrings: function () {
		const inputfieldRuntimeMarkupConfigs =
			this.getProcessWireInputfieldPWCommerceRuntimeMarkupConfig()
		return inputfieldRuntimeMarkupConfigs.translations
	},

	// ~~~~~~~~~~~~~~~~~

	/**
	 * Listen to shipping handling fee type changes.
	 *
	 * We use to toggle '%' sign if handling fee type is 'percentage' or currency symbol if available if type is 'fixed'.
	 */
	initListenToShippingHandlingFeeType: function () {
		const handlingFeeTypeElement = document.getElementById(
			"pwcommerce_shipping_fee_settings_handling_fee_type"
		)
		if (handlingFeeTypeElement) {
			// add event listener to handling fee type change
			handlingFeeTypeElement.addEventListener(
				"change",
				InputfieldPWCommerceRuntimeMarkup.handleShippingHandlingFeeTypeChange,
				false
			)
		}
	},
	handleShippingHandlingFeeTypeChange: function (event) {
		// shipping handling fee type change: need to show or hide % or symbol in description of 'handling fee value'
		const handlingFeeValueElement = document.getElementById(
			"pwcommerce_shipping_fee_settings_handling_fee_value"
		)
		const selectedHandlingFeeType = event.target.value
		if (handlingFeeValueElement) {
			// get the two spans with the percentage and currency symbols
			const handlingFeeValuePercentageSymbolElement = document.getElementById(
				"pwcommerce_shipping_fee_settings_handling_fee_value_percent_symbol"
			)
			const handlingFeeValueCurrencySymbolElement = document.getElementById(
				"pwcommerce_shipping_fee_settings_handling_fee_value_currency_symbol"
			)
			// toggle show/hide symbols markup
			if (selectedHandlingFeeType === "fixed") {
				// if handling fee type is fixed - show currency symbol + hide percentage symbol
				handlingFeeValueCurrencySymbolElement.classList.remove("pwcommerce_hide")
				handlingFeeValuePercentageSymbolElement.classList.add("pwcommerce_hide")
			} else if (selectedHandlingFeeType === "percentage") {
				// if handling fee type is percentage - show percentage symbol + hide currency symbol
				handlingFeeValuePercentageSymbolElement.classList.remove(
					"pwcommerce_hide"
				)
				handlingFeeValueCurrencySymbolElement.classList.add("pwcommerce_hide")
			} else {
				// handling fee type is none - hide both percentage and currency symbols
				handlingFeeValueCurrencySymbolElement.classList.add("pwcommerce_hide")
				handlingFeeValuePercentageSymbolElement.classList.add("pwcommerce_hide")
			}
		}
	},

	// ~~~~~~~~~~~~~~~~~~~

	/**
	 * Listen to shipping rate criteria type changes.
	 *
	 * We use to toggle 'kg' sign if  weight-based rate, or currency symbol if available if type is 'price'.
	 * If quantity-based or none, we hide symbols.
	 */
	initListenToShippingRateCriteriaType: function () {
		// @note: can have multiple shipping rates
		// @note: we also cater for rates added on the fly via htmx. These need re-initing
		const rateCriteriaTypeElements = document.querySelectorAll(
			"li.InputfieldWrapper select.pwcommerce_shipping_rate_criteria_type"
		)

		if (rateCriteriaTypeElements) {
			for (const rateCriteriaTypeElement of rateCriteriaTypeElements) {
				// add event listener to each shipping rate criteria type
				rateCriteriaTypeElement.addEventListener(
					"change",
					InputfieldPWCommerceRuntimeMarkup.handleShippingCriteriaTypeChange,
					false
				)
			}
		}
	},
	handleShippingCriteriaTypeChange: function (event) {
		const selectedCriteriaTypeElement = event.target
		const selectedCriteriaType = selectedCriteriaTypeElement.value
		// get the top parent ul Inputfields
		// we'll need it to get the markup header 'Conditions'
		const rateCriteriaTypeTopParentElement =
			selectedCriteriaTypeElement.closest("ul.Inputfields")

		// ==========
		// toggle show/hide symbols markup
		InputfieldPWCommerceRuntimeMarkup.toggleShippingCriteriaTypeSymbol(
			rateCriteriaTypeTopParentElement,
			selectedCriteriaType
		)

		// ==========
		// toggle minum and maximum criteria inputs 'step' attribute
		InputfieldPWCommerceRuntimeMarkup.toggleShippingCriteriaMinMaxStep(
			rateCriteriaTypeTopParentElement,
			selectedCriteriaType
		)
	},

	toggleShippingCriteriaTypeSymbol: function (
		rateCriteriaTypeTopParentElement,
		selectedCriteriaType
	) {
		// get the span inside the li child of rateCriteriaTypeTopParentElement
		// that has the symbol for weight (kg)
		const rateCriteriaTypeWeightSymbolElement =
			rateCriteriaTypeTopParentElement.querySelector(
				"span.pwcommerce_shipping_rate_criteria_type_weight"
			)
		// get the span inside the li child of rateCriteriaTypeTopParentElement
		// that has the symbol for price (currency symbol)
		const rateCriteriaTypePriceSymbolElement =
			rateCriteriaTypeTopParentElement.querySelector(
				"span.pwcommerce_shipping_rate_criteria_type_price"
			)
		// +++++++++++
		// toggle show/hide symbols markup
		if (selectedCriteriaType === "weight") {
			// if rate criteria type is weight - show weight(kg) symbol + hide price(currency) symbol
			rateCriteriaTypeWeightSymbolElement.classList.remove("pwcommerce_hide")
			rateCriteriaTypePriceSymbolElement.classList.add("pwcommerce_hide")
		} else if (selectedCriteriaType === "price") {
			// if rate criteria type is price - show price (currency) symbol + hide weight(kg) symbol
			rateCriteriaTypePriceSymbolElement.classList.remove("pwcommerce_hide")
			rateCriteriaTypeWeightSymbolElement.classList.add("pwcommerce_hide")
		} else {
			// handling fee type is none - hide both percentage and currency symbols
			rateCriteriaTypeWeightSymbolElement.classList.add("pwcommerce_hide")
			rateCriteriaTypePriceSymbolElement.classList.add("pwcommerce_hide")
		}
	},

	toggleShippingCriteriaMinMaxStep: function (
		rateCriteriaTypeTopParentElement,
		selectedCriteriaType
	) {
		// get the minimum and maximum input(type=number)
		// we adjust their 'step'
		// quantity -> '1' else '0.01'
		const rateCriteriaTypeMinMaxElements =
			rateCriteriaTypeTopParentElement.querySelectorAll(
				".pwcommerce_shipping_rate_criteria_min_max"
			)

		// +++++++++++
		// toggle minum and maximum criteria inputs 'step' attribute
		// if quantity -> '1', else -> '0.01'
		const minMaxCriteriaStep = selectedCriteriaType === "quantity" ? 1 : 0.01
		for (const rateCriteriaTypeMinMaxElement of rateCriteriaTypeMinMaxElements) {
			rateCriteriaTypeMinMaxElement.step = minMaxCriteriaStep
		}
	},

	// ~~~~~~~~~~~~~~~~~~~

	/**
	 * Listen to product variant fieldset open.
	 *
	 * We use to fetch inputfields of the variant via htmx.
	 */
	initListenToProductVariantOpen: function () {
		// @TODO!!!
		// @note: we also cater for product variants added on the fly via htmx. These need re-initing
		const productVariantsFieldsetsElements = document.querySelectorAll(
			"li.InputfieldPWCommerceRuntimeMarkupItem"
		)
		// console.log(
		// 	"InputfieldPWCommerceRuntimeMarkup - initListenToProductVariantOpen - productVariantsFieldsetsElements",
		// 	productVariantsFieldsetsElements
		// )

		if (productVariantsFieldsetsElements) {
			for (const productVariantFieldsetElement of productVariantsFieldsetsElements) {
				// add event listener to each product variant fieldset
				productVariantFieldsetElement.addEventListener(
					"click",
					InputfieldPWCommerceRuntimeMarkup.handleProductVariantOpen,
					false
				)
			}
		}
	},
	handleProductVariantOpen: function (event) {
		// get the desired trigger element from the event
		// @note; we need the closest li.InputfieldFieldset
		// const triggerElement = event.target.closest(
		// 	"li.InputfieldPWCommerceRuntimeMarkupItem"
		// )
		// event.stopPropagation()
		const triggerElement = event.target
		// @TODO DELETE WHEN DONE
		// console.log(
		// 	"InputfieldPWCommerceRuntimeMarkup - handleProductVariantOpen - event",
		// 	event
		// )
		// console.log(
		// 	"InputfieldPWCommerceRuntimeMarkup - handleProductVariantOpen - triggerElement",
		// 	triggerElement
		// )

		// @TODO - DELETE IF NOT IN USE
		// const variantID = triggerElement.dataset.page
		// console.log(
		// 	"InputfieldPWCommerceRuntimeMarkup - handleProductVariantOpen - variantID",
		// 	variantID
		// )
		// const triggerElementID =
		// 	triggerElement.id
		// console.log(
		// 	"InputfieldPWCommerceRuntimeMarkup - handleProductVariantOpen - triggerElementID",
		// 	triggerElementID
		// )
		// ----------

		//
		// InputfieldHeader
		// if (triggerElement.classList.contains("pwcommerce_dynamic_loading")) {
		if (
			InputfieldPWCommerceRuntimeMarkup.checkTriggerDynamicLoading(triggerElement)
		) {
			PWCommerceCommonScripts.debugger(
				"InputfieldPWCommerceRuntimeMarkup - handleProductVariantOpen -  triggerElement FOR AJAX DYNAMIC LOADING",
				"info",
				triggerElement.id
			)
			// dispatch custom event for htmx to trigger product variant dynamic loading
			const eventName = "pwcommerceproductvariantdynamicloading"
			htmx.trigger(triggerElement, eventName)
		} else {
			PWCommerceCommonScripts.debugger(
				"InputfieldPWCommerceRuntimeMarkup - handleProductVariantOpen -  triggerElement NO MORE AJAX DYNAMIC LOADING",
				"warn",
				triggerElement.id
			)
		}
	},

	checkTriggerDynamicLoading: function (triggerElement) {
		PWCommerceCommonScripts.debugger(
			"InputfieldPWCommerceRuntimeMarkup - checkTriggerDynamicLoading -  triggerElement TO CHECK FOR DYNAMIC AJAX LOADING",
			"log",
			triggerElement
		)
		return (
			triggerElement.classList.contains("InputfieldHeader") &&
			triggerElement.parentNode.classList.contains("pwcommerce_dynamic_loading")
		)
	},

	// ~~~~~~~~~~~~~~~~~~~
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	if (typeof htmx !== "undefined") {
		// @TODO: WIP! GENERIC FOR PWCOMMERCE RUNTIME ITEMS
		InputfieldPWCommerceRuntimeMarkup.listenToHTMXRequests()
	}
	// const pwcommerceShopCurrentContext =
	// 	PWCommerceCommonScripts.getPWCommerceShopCurrentContext()
	// PWCommerceCommonScripts.debugger(
	// 	"InputfieldPWCommerceRuntimeMarkup - pwcommerceShopCurrentContext",
	// 	"log",
	// 	pwcommerceShopCurrentContext
	// )
	// handle changes to shipping handling fee type change
	InputfieldPWCommerceRuntimeMarkup.initListenToShippingHandlingFeeType()
	// handle changes to shipping rate criteria type change
	InputfieldPWCommerceRuntimeMarkup.initListenToShippingRateCriteriaType()
	// @TODO! handle product variant fieldset open (once)
	// @TODO - DELETE IF NO LONGER IN USE! -> @TODO MIGHT USE THIS SINCE HTMX ABORT AND HT-DISINHERIT ARE NOT WORKING!
	// InputfieldPWCommerceRuntimeMarkup.initListenToProductVariantOpen()
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
		Alpine.store("InputfieldPWCommerceRuntimeMarkupStore", {
			// collection of IDs of items marked for deletion
			items_to_delete: [],
		})

		Alpine.data("InputfieldPWCommerceRuntimeMarkupData", () => ({
			//---------------
			// FUNCTIONS

			/**
			 * Set a store property value.
			 * @param any value Value to set in store.
			 * @return {void}.
			 */
			setStoreValue(property, value) {
				this.$store.InputfieldPWCommerceRuntimeMarkupStore[property] = value
			},

			// ~~~~~~~~~~~~~~~~

			/**
			 * Get the value of a given store property.
			 * @param string property Property in store whose value to return
			 * @returns {any}
			 */
			getStoreValue(property) {
				return this.$store.InputfieldPWCommerceRuntimeMarkupStore[property]
			},

			// ~~~~~~~~~~~~~~~~

			toggleMarkItemForDeletion(event = {}, itemID, trash_element = null) {
				let showDeleteIntent = false
				// clone items to delete
				let itemsToDeleteClone = [...this.getStoreValue("items_to_delete")]

				// IF ITEM ALREADY SELECTED, remove it
				if (itemsToDeleteClone.includes(itemID)) {
					itemsToDeleteClone = itemsToDeleteClone.filter(
						(item_id) => item_id !== itemID
					)
				} else {
					// else ADD IT
					itemsToDeleteClone.push(itemID)
					showDeleteIntent = true
				}

				//---------------
				this.setStoreValue("items_to_delete", itemsToDeleteClone)

				//------------------
				// TOGGLE DELETE INTENT 'classes'
				// trashElement IS: i.fa-trash.pwcommerce_runtime_item_delete
				// ancestor parent of
				// const trashElement = event.target;
				const trashElement = trash_element ? trash_element : event.target
				// ancestor parent of
				// trashElement IS: i.fa-trash.pwcommerce_runtime_item_delete
				// if trash element sent, use it, else get from event
				const parentInputfieldHeaderLabelElement = trashElement.closest(
					"label.InputfieldHeader"
				)
				// toggle class for in 'pending-delete-bin'
				this.toggleDeletePending(
					parentInputfieldHeaderLabelElement,
					showDeleteIntent
				)
				// toggle class to show 'ui-state-error'
				this.toggleUIStateError(
					parentInputfieldHeaderLabelElement,
					showDeleteIntent
				)
			},

			handleDoubleClickToggleMarkItemsForDeletion() {
				// GET ALL TRASH ELEMENTS
				const trashElements = document.querySelectorAll(
					"i.pwcommerce_runtime_item_delete"
				)
				if (trashElements) {
					// PASS EACH TO TOGGLE DELETE METHOD
					for (const trashElement of trashElements) {
						const itemID = parseInt(trashElement.dataset.itemId)
						// mark for deletion
						this.toggleMarkItemForDeletion({}, itemID, trashElement)
					}
				}
			},

			// hovering the trash of a runtime item gives a preview of what clicking it would do
			handleDeleteIntent(event) {
				const showDeleteIntent = event.type === "mouseenter"
				//------------------
				const trashElement = event.target // i.fa-trash.pwcommerce_runtime_item_delete
				// ancestor parent of
				const parentInputfieldHeaderLabelElement = trashElement.closest(
					"label.InputfieldHeader"
				)
				// if we found the parent label and it is NOT ALREADY marked for deletion
				// toggle class 'ui-state-error'
				if (
					parentInputfieldHeaderLabelElement &&
					!parentInputfieldHeaderLabelElement.classList.contains(
						"pwcommerce_runtime_item_delete_pending"
					)
				) {
					this.toggleUIStateError(
						parentInputfieldHeaderLabelElement,
						showDeleteIntent
					)
				}
			},

			// add/remove class 'ui-state-error' for showing intention to delete an item (page)
			toggleUIStateError(element, isAddErrorState = true) {
				// element.classList.toggle("ui-state-error") // @note: clashes with with toggleDeletePending()!
				if (isAddErrorState) {
					element.classList.add("ui-state-error")
				} else {
					element.classList.remove("ui-state-error")
				}
			},

			// add/remove class 'pwcommerce_runtime_item_delete_pending' to show item ID in 'pending-delete-bin'
			// @note: needed to ignore handleDeleteIntent() since already marked for deletion
			toggleDeletePending(element, isAddDeletePending = true) {
				if (isAddDeletePending) {
					element.classList.add("pwcommerce_runtime_item_delete_pending")
				} else {
					element.classList.remove("pwcommerce_runtime_item_delete_pending")
				}
			},
		}))
	}
	// end: if in pwcommerce shop context
})
