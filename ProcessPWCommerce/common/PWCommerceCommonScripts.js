const PWCommerceCommonScripts = {
	initHTMXXRequestedWithXMLHttpRequest: function () {
		// this.debugger(
		// 	"PWCommerceCommonScripts - initHTMXXRequestedWithXMLHttpRequest"
		// )
		document.body.addEventListener("htmx:configRequest", (event) => {
			const csrf_token = PWCommerceCommonScripts.getCSRFToken()
			event.detail.headers[csrf_token.name] = csrf_token.value
			// add XMLHttpRequest to header to work with $config->ajax
			event.detail.headers["X-Requested-With"] = "XMLHttpRequest"
		})
	},

	listenToHTMXRequests: function () {
		// before send
		// @todo: revive this to use as shared resource for all htmx-powered inputfields that also use runtime markup
		htmx.on("htmx:beforeSend", function (event) {
			// PWCommerceCommonScripts.debugger(
			// 	"PWCommerceCommonScripts - listenToHTMXRequests - beforeSend - event",
			// 	"log",
			// 	event
			// )
			// check if need to run request indicators operations after swap
			if (
				PWCommerceCommonScripts.isRunRequestIndicatorsOperationsRequired(event)
			) {
				// run show indicator spinner
				PWCommerceCommonScripts.runRequestIndicatorsOperations(event)
			}
		})

		// after swap
		htmx.on("htmx:afterSwap", function (event) {
			// check if need to run request indicators operations after swap
			if (
				PWCommerceCommonScripts.isRunRequestIndicatorsOperationsRequired(event)
			) {
				// run hide indicator spinner
				PWCommerceCommonScripts.runRequestIndicatorsOperations(event, true)
			}
		})
	},

	// check if a htmx request included a special 'run-request-indicators-before-send-and-after-swap-operations' runtimemarkup class
	// used only by 'add new' requests that show/hide 'fa-plus' and 'fa-spinner'
	isRunRequestIndicatorsOperationsRequired: function (event) {
		const triggerElement = event.detail.requestConfig.elt
		return triggerElement.classList.contains(
			"pwcommerce_run_request_indicators_operations"
		)
	},

	// ~~~~~~~~~~~

	triggerReloadProcessWireInputfields: function (event) {
		// get markup (ul) of response HTML sent by server
		// @TODO: OK LIKE THIS?
		const responseMarkup = event.detail.elt.lastChild
		if (!responseMarkup) return

		// get the new FIELDSET in order to trigger reloads
		//-----------------

		// PWCommerceCommonScripts
		// @note: unfortunately, we need jQuery for this!
		const newFieldset = $(document).find(".InputfieldPWCommerceRuntimeMarkup")

		// @note: this is  jQuery object so can check using length
		if (!newFieldset.length) {
			// this.debugger(
			// 	"PWCommerceCommonScripts - triggerReloadProcessWireInputfields - newFieldset NOT FOUND!",
			// 	"error",
			// 	newFieldset
			// )
			return
		}

		const newlyCreatedItemsTimestampElement = document.getElementById(
			"pwcommerce_created_items_timestamp"
		)

		// this.debugger(
		// 	"PWCommerceCommonScripts - triggerReloadProcessWireInputfields - newlyCreatedItemsTimestampElement",
		// 	"log",
		// 	newlyCreatedItemsTimestampElement
		// )

		let selectorForNewlyCreatedItemsInputfieldElements,
			newlyCreatedItemsInputfieldElements

		if (newlyCreatedItemsTimestampElement) {
			// this.debugger(
			// 	"PWCommerceCommonScripts - triggerReloadProcessWireInputfields - newlyCreatedItemsTimestampElement.value",
			// 	"log",
			// 	newlyCreatedItemsTimestampElement.value
			// )
			selectorForNewlyCreatedItemsInputfieldElements = `.pwcommerce_created_items_timestamp_${newlyCreatedItemsTimestampElement.value}`
			newlyCreatedItemsInputfieldElements = document.querySelectorAll(
				selectorForNewlyCreatedItemsInputfieldElements
			)
		}

		// this.debugger(
		// 	"PWCommerceCommonScripts - triggerReloadProcessWireInputfields - selectorForNewlyCreatedItemsInputfieldElements",
		// 	"warn",
		// 	selectorForNewlyCreatedItemsInputfieldElements
		// )

		// this.debugger(
		// 	"PWCommerceCommonScripts - triggerReloadProcessWireInputfields - newlyCreatedItemsInputfieldElements",
		// 	"info",
		// 	newlyCreatedItemsInputfieldElements
		// )

		// #################
		// TRIGGER RELOAD:INIT  (ul.Inputfields)
		PWCommerceCommonScripts.reloadAllInputfields(newFieldset) // the ul.Inputfields
		//-----------
		// TRIGGER RELOAD: ALL INPUTFIELDS (li.Inputfield) except special ones below
		PWCommerceCommonScripts.reloadAllInputfield(newFieldset) // the li.Inputfield
		//-----------
		// TRIGGER RELOAD: InputfieldCKEditor
		PWCommerceCommonScripts.reloadInputfieldCKEditor(newFieldset)
		//-----------
		// TRIGGER RELOAD: InputfieldTextTags
		PWCommerceCommonScripts.reloadInputfieldTextTags(
			newFieldset,
			selectorForNewlyCreatedItemsInputfieldElements
		)
		//-----------
		// TRIGGER RELOAD: InputfieldPageAutocomplete
		PWCommerceCommonScripts.reloadPageAutocomplete()
	},

	// RELOAD: ALL ul.Inputfields (wrappers of the li.Inputfield)
	reloadAllInputfields: function (newFieldset) {
		// this.debugger("PWCommerceCommonScripts - reloadAllInputfields", "log")
		// the ul.Inputfields
		// @note: this is a ProcessWire method in inputfields.js
		InputfieldsInit(newFieldset.find(".Inputfields"))
	},

	// RELOAD: ALL li.Inputfield including images
	reloadAllInputfield: function (newFieldset) {
		// this.debugger("PWCommerceCommonScripts - reloadAllInputfield")
		// the li.Inputfield
		const $inputfields = newFieldset.find(
			// @note: we can only do this using jQuery, sigh!
			//-------------------------------------
			// @note: this selector doesn work for images reload. Hower, the catch all '.Inputfield' leads to many unnecessary reloads, slowing things down.
			// ".Inputfield.pwcommerce_is_new_runtime_markup_item"
			// ".Inputfield"
			// @note: this compromise works, only get 'new' for other inputfields, but for images, get 'all'
			// @TODO: DELETE THIS IF NOT NEEDED: MAKING THIS MORE GENERIC!
			//".Inputfield.pwcommerce_is_new_runtime_markup_item,.Inputfield.InputfieldImage"
			".Inputfield.pwcommerce_is_new_item,.Inputfield.InputfieldImage"
		)
		// trigger reload
		// @TODO: DO WE NEED THIS 'InputfieldImageUpload'?
		$inputfields.trigger("reloaded", ["InputfieldImageUpload"])
	},

	// RELOAD: init ProcessWire InputfieldPageAutocomplete /
	reloadPageAutocomplete: function (selectorString) {
		// this.debugger("PWCommerceCommonScripts - reloadPageAutocomplete")
		// @note: unfortunately, can only use jquery here :-)
		// if we have a selector, use it, else default to runtime markup ones
		const selector = selectorString
			? selectorString
			: ".InputfieldPWCommerceRuntimeMarkup .InputfieldPageAutocomplete"
		$(selector).each(function () {
			InputfieldPageAutocomplete.initFromInputfield($(this))
		})
	},

	// RELOAD: InputfieldCKEditor
	reloadInputfieldCKEditor: function (newFieldset) {
		// this.debugger("PWCommerceCommonScripts - reloadInputfieldCKEditor")
		newFieldset.find("textarea.InputfieldCKEditorNormal").each(function () {
			// set the 'data-configName' to read this CKEditor instance configs from the an existing Editor since ours is new and not yet saved
			$(this).attr("data-configName", "InputfieldCKEditor_pwcommerce_description")
			// trigger 'reloaded' event on the editor wrapper
			$(this).closest(".InputfieldCKEditor").trigger("reloaded")
		})
	},

	// RELOAD: selectize InputfieldTextTags
	reloadInputfieldTextTags: function (
		newFieldset,
		selectorForNewlyCreatedItemsInputfieldElements
	) {
		// this.debugger("PWCommerceCommonScripts - reloadInputfieldTextTags")
		// this.debugger(
		// 	"PWCommerceCommonScripts - reloadInputfieldTextTags - newFieldset",
		// 	"log",
		// 	newFieldset
		// )
		// this.debugger(
		// 	"PWCommerceCommonScripts - reloadInputfieldTextTags - selectorForNewlyCreatedItemsInputfieldElements",
		// 	"info",
		// 	selectorForNewlyCreatedItemsInputfieldElements
		// )

		const $selects = newFieldset.find(
			`${selectorForNewlyCreatedItemsInputfieldElements} .InputfieldTextTagsSelect:not(.selectized)`
		)
		// this.debugger(
		// 	"PWCommerceCommonScripts - reloadInputfieldTextTags -	$selects",
		// 	"info",
		// 	$selects
		// )
		// @TODO @UPDATE! IT WORKS! TUESDAY 7 SEPTEMBER 2021 13.14!!!! we use Inputfields.reload() to trigger reload for us
		// reload each selectize item individually
		$selects.each(function () {
			$item = $(this)
			// @note: method is in Inputfields.js
			Inputfields.reload($item)
		})
	},

	// ~~~~~~~~~~~

	runRequestIndicatorsOperations: function (event, isRunAfter = false) {
		// get the trigger element
		const triggerElement = event.detail.requestConfig.elt
		// found trigger element, now get the descendant 'i' element with the class 'pwcommerce_spinner_indicator'
		if (triggerElement) {
			// const indicatorSpinnerElement = triggerElement.firstElementChild;
			const indicatorSpinnerElement = htmx.find(
				triggerElement,
				"i.pwcommerce_spinner_indicator"
			)
			//----------
			if (!isRunAfter) {
				// run show before send spinner
				PWCommerceCommonScripts.runBeforeSendSpinner(
					indicatorSpinnerElement,
					true
				)
			} else {
				// run hide after swap spinner
				PWCommerceCommonScripts.runAfterSwapSpinner(indicatorSpinnerElement, true)
			}
		}
	},

	// SHOW fa-spinner/ HIDE fa-plus BEFORE SEND htmx request
	/**
	 * Toggle 'fa-plus' icon for request indicator spinner.
	 * @param {string|Element} indicatorSpinner Indicator Element or string to find indicator element
	 * @param {bool} isElement Whether indicatorSpinner is already an element
	 */
	runBeforeSendSpinner: function (indicatorSpinner, isElement = false) {
		// get the element with the fa-classes for add/spinner using selector string
		// @note: if element already sent, use it, else find it
		const indicatorSpinnerElement = isElement
			? indicatorSpinner
			: htmx.find(indicatorSpinner)
		//--------
		// remove 'fa-plus-circle' class from the indicator/spinner element
		htmx.removeClass(indicatorSpinnerElement, "fa-plus-circle")
		// add 'spinner' classes to indicator/spinner element
		// @note: can only add one class at a time
		htmx.addClass(indicatorSpinnerElement, "fa-spinner")
		htmx.addClass(indicatorSpinnerElement, "fa-spin")
	},

	// HIDE fa-spinner/ SHOW fa-plus AFTER SWAP htmx request
	/**
	 * Toggle request indicator spinner for 'fa-plus' icon.
	 * @param {string|Element} indicatorSpinner Indicator Element or string to find indicator element
	 * @param {bool} isElement Whether indicatorSpinner is already an element
	 */
	runAfterSwapSpinner: function (indicatorSpinner, isElement = false) {
		// get the element with the fa-classes for add/spinner using selector string
		// @note: if element already sent, use it, else find it
		const indicatorSpinnerElement = isElement
			? indicatorSpinner
			: htmx.find(indicatorSpinner)
		//--------
		// remove 'spinner' classes from the indicator/spinner element
		// @note: can only remove one class at a time
		htmx.removeClass(indicatorSpinnerElement, "fa-spinner")
		htmx.removeClass(indicatorSpinnerElement, "fa-spin")
		// add 'fa-plus-circle' class to indicator/spinner element
		htmx.addClass(indicatorSpinnerElement, "fa-plus-circle")
	},

	getCSRFToken: function () {
		// find hidden input with id 'csrf-token'
		const tokenInput = htmx.find("._post_token")
		return tokenInput
	},

	/**
	 * Returns the value of the PWCommerce current shop context as set by its Process module.
	 * @returns String|Null pwcommerceShopCurrentContext The current shop context.
	 */
	getPWCommerceShopCurrentContext: function () {
		let pwcommerceShopCurrentContext = null
		const pwcommerceShopCurrentContextElement = document.getElementById(
			"pwcommerce_shop_current_context"
		)
		if (pwcommerceShopCurrentContextElement) {
			pwcommerceShopCurrentContext = pwcommerceShopCurrentContextElement.value
		}
		return pwcommerceShopCurrentContext
	},

	/**
	 * Dispatch a custom event as requested.
	 * @param {string} eventName The name of the custom event to dispatch.
	 * @param {any} eventDetail The event details to attach to the event detail object.
	 * @param {Node} elem Optional element to trigger the event from, else window.
	 */
	dispatchCustomEvent: function (eventName, eventDetail, elem) {
		// this.debugger(
		// 	"PWCommerceCommonScripts - dispatchCustomEvent - eventName",
		// 	"info",
		// 	eventName
		// )
		const event = new CustomEvent(eventName, { detail: eventDetail })
		if (elem) {
			elem.dispatchEvent(event)
		} else {
			window.dispatchEvent(event)
			// this.debugger(
			// 	"PWCommerceCommonScripts - dispatchCustomEvent FROM WINDOW - eventName",
			// 	"log",
			// 	eventName
			// )
			// this.debugger(
			// 	"PWCommerceCommonScripts - dispatchCustomEvent FROM WINDOW - event",
			// 	"log",
			// 	event
			// )
		}
	},

	// ~~~~~~~~~~~

	/**
	 * Listen to 'click' and 'dblclick' on trash cans used to 'mark for deletion' some inputfield items.
	 * @param {string} trashSelector The selector to get the elements to monitor.
	 */
	initMonitorItemDelete: function (trashSelector) {
		// document.querySelectorAll(trashSelector).forEach(function (a) {
		// 	a.addEventListener(
		// 		"click",
		// 		PWCommerceCommonScripts.toggleMarkItemForDeletion,
		// 		false
		// 	)
		// })
		document
			.querySelectorAll(trashSelector)
			// add both click and double click event listeners
			.forEach((i) =>
				["click", "dblclick"].forEach(function (event) {
					i.addEventListener(
						event,
						PWCommerceCommonScripts.toggleMarkItemForDeletion,
						false
					)
				})
			)
	},

	/**
	 * Toggle mark items in some inputfields for deletion.
	 *
	 * Handles both click (single item delete) and double click (all items).
	 * @param {Event} event
	 * @returns void
	 */
	toggleMarkItemForDeletion: function (event) {
		const trashElement = event.target
		if (!trashElement) return
		//---------------
		// GOOD TO GO
		const eventType = event.type
		if (eventType === "dblclick") {
			// trash can double clicked
			// toggle mark all siblings for deletion

			const trashElementWrapper = htmx.closest(
				trashElement,
				"li.InputfieldWrapper"
			)
			const allTrashElementsInputfieldsParentElement =
				trashElementWrapper.parentNode
			//--------------

			for (const child of allTrashElementsInputfieldsParentElement.children) {
				const hiddenInputForToggleMarkForDeleteElement = child.querySelector(
					"input.pwcommerce_trash"
				)
				// toggle item as marked for deletion (0/1)
				// @note! we are inside an anonymous function so 'this' will not work! We need full path ('PWCommerceCommonScripts')
				hiddenInputForToggleMarkForDeleteElement.value =
					PWCommerceCommonScripts.toggleBooleanIntegerProperty(
						hiddenInputForToggleMarkForDeleteElement.value
					)
				// ------------------
				// toggle class for marked for trashing on the element wrapper (li.InputfieldWrapper)
				htmx.toggleClass(child, "pwcommerce_selected_for_deletion")
			}
		} else {
			// trash can clicked

			// get hidden input to mark/unmark item for deletion
			const hiddenInputForToggleMarkForDeleteElement =
				trashElement.parentNode.querySelector("input.pwcommerce_trash")
			// toggle item as marked for deletion (0/1)
			// @note! we are inside an anonymous function so 'this' will not work! We need full path ('PWCommerceCommonScripts')
			hiddenInputForToggleMarkForDeleteElement.value =
				PWCommerceCommonScripts.toggleBooleanIntegerProperty(
					hiddenInputForToggleMarkForDeleteElement.value
				)

			// ------------------
			const trashElementWrapper = htmx.closest(
				trashElement,
				"li.InputfieldWrapper"
			)
			// toggle class for marked for trashing on the element wrapper (li.InputfieldWrapper)
			htmx.toggleClass(trashElementWrapper, "pwcommerce_selected_for_deletion")
		}
	},

	// toggle bool integer property (0/1)
	// v = 1 - v
	toggleBooleanIntegerProperty: function (currentValue) {
		return 1 - parseInt(currentValue)
	},

	// ~~~~~~~~~~~~~~~~~ DEBUGGER ~~~~~~~~~~~~~~~~~

	/**
	 *
	 * @param {string} message Message to output to console.
	 * @param {string} type Console message type (error, warn, info, debug or log).
	 * @param {any} element Element to log along with the message. Can be Node or Element or Variable.
	 * @returns void
	 */
	debugger: function (message = "", type = "log", element = "") {
		// first check if ProcessWire is in debug mode
		if (!this.isProcessWireInDebugMode()) {
			// not in debug, return
			return
		}
		// if no message, return
		if (!message.length) {
			return
		}

		message = `Debugger: ${message}`

		if (type === "error") {
			// error message
			console.error(
				`%c${message}`,
				`${this.getDebugConsoleStyles(type)}`,
				element
			)
		} else if (type === "warn") {
			// warning message
			console.warn(
				`%c${message}`,
				`${this.getDebugConsoleStyles(type)}`,
				element
			)
		} else if (type === "info") {
			// information message
			console.info(
				`%c${message}`,
				`${this.getDebugConsoleStyles(type)}`,
				element
			)
		} else if (type === "debug") {
			// debug message
			// @note: requires verbose!
			console.debug(
				`%c${message}`,
				`${this.getDebugConsoleStyles(type)}`,
				element
			)
		} else {
			// log message
			console.log(
				`%c${message}`,
				`${this.getDebugConsoleStyles(type)}`,
				element
			)
		}
	},

	/**
	 *
	 * @param {string} type Type of console to return styles for.
	 * @param {string|null} customColour Custom colour for text for console message.
	 * @param {string|null} customBackgroundColour Custom background colour for background colour for console message.
	 * @returns string styles CSS styles for console type.
	 */
	getDebugConsoleStyles: function (
		type,
		customColour = null,
		customBackgroundColour = null
	) {
		let styles, colour, backgroundColour

		// -----------------
		if (type === "error") {
			// error message
			colour = customColour ? customColour : "white"
			backgroundColour = customBackgroundColour
				? customBackgroundColour
				: "#c00000"
		} else if (type === "warn") {
			// warning message
			colour = customColour ? customColour : "white"
			backgroundColour = customBackgroundColour
				? customBackgroundColour
				: "#826306"
		} else if (type === "info") {
			// information message
			colour = customColour ? customColour : "white"
			backgroundColour = customBackgroundColour
				? customBackgroundColour
				: "#0070c0"
		} else if (type === "debug") {
			// debug message
			colour = customColour ? customColour : "white"
			backgroundColour = customBackgroundColour
				? customBackgroundColour
				: "#a6a6a6"
		} else {
			// log message
			colour = customColour ? customColour : "#ffff75"
			backgroundColour = customBackgroundColour
				? customBackgroundColour
				: "#5d2884"
		}
		// --------------
		styles = `color:${colour}; background-color:${backgroundColour};`
		// add padding
		styles = `${styles} padding:2px 4px;`

		// --------
		return styles
	},

	isProcessWireInDebugMode: function () {
		let isInDebugMode = false
		if (this.isProcessWireConfigExist()) {
			isInDebugMode = ProcessWire.config.debug
		}
		return isInDebugMode
	},

	isProcessWireConfigExist: function () {
		return typeof ProcessWire !== "undefined" && "config" in ProcessWire
	},

	// ~~~~~~~~~~~~~~~~~ END: DEBUGGER ~~~~~~~~~~~~~~~~~
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	if (typeof htmx !== "undefined") {
		// @TODO: WIP! GENERIC FOR PWCOMMERCE RUNTIME ITEMS
		// init htmx with X-Requested-With
		PWCommerceCommonScripts.initHTMXXRequestedWithXMLHttpRequest()
		PWCommerceCommonScripts.listenToHTMXRequests()
	}
})

// ~~~~~~~~~~~~~
