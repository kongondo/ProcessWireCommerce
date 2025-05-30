const InputfieldPWCommerceProductProperties = {
	listenToHTMXRequests: function () {
		// after swap
		htmx.on("htmx:afterSwap", function (event) {
			// init InputfieldPageAutocomplete on newly added property row
			// @note: method is in PWCommerceCommonScripts.js
			// TRIGGER RELOAD: InputfieldPageAutocomplete
			const selectorString =
				".InputfieldPageAutocomplete.pwcommerce_product_property_new"
			PWCommerceCommonScripts.reloadPageAutocomplete(selectorString)
		})
	},

	initMonitorPropertyDelete: function () {
		const trashSelector =
			"div#pwcommerce_product_properties_wrapper span.pwcommerce_trash"
		PWCommerceCommonScripts.initMonitorItemDelete(trashSelector)
	},

	initColourPicker: function () {
		const hiddenColourInput = document.getElementById(
			"pwcommerce_product_settings_colour"
		)

		/*
        Create a new Picker instance and set the parent element.
        By default, the color picker is a popup which appears when you click the parent.
    */
		const parent = document.getElementById(
			"pwcommerce_product_property_selected_colour_preview"
		)
		// const picker = new Picker(parent);
		const picker = new Picker({
			parent: parent,
			color: hiddenColourInput.value,
		})

		picker.onOpen = function (event) {
			// change picker's 'ok' button text to use our translated string
			const pickerButton = document.querySelector("div.picker_done button")
			pickerButton.innerHTML =
				'<i class="fa fa-check-square-o" aria-hidden="true"></i>'
		}

		/*
        You can do what you want with the chosen color using two callbacks: onChange and onDone.
    */
		// picker.onChange = function (color) {
		// 	console.log("initColourPicker - color - onChange", color);
		// 	parent.style.background = color.rgbaString;
		// };
		picker.onDone = function (color) {
			parent.style.background = color.rgbaString
			hiddenColourInput.value = color.hex
		}
	},
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	if (typeof htmx !== "undefined") {
		InputfieldPWCommerceProductProperties.listenToHTMXRequests()
	}
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (ProcessPWCommerce) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	)
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		InputfieldPWCommerceProductProperties.initMonitorPropertyDelete()
		InputfieldPWCommerceProductProperties.initColourPicker()
	}
	// end: if in pwcommerce shop context
})

//--------------
