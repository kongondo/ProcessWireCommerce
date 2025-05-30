const InputfieldPWCommerceTaxOverrides = {
	listenToHTMXRequests: function () {
		// after swap
		htmx.on("htmx:afterSwap", function (event) {
			// init InputfieldPageAutocomplete on newly added tax override row
			// @note: method is in PWCommerceCommonScripts.js
			// TRIGGER RELOAD: InputfieldPageAutocomplete
			const selectorString =
				".InputfieldPageAutocomplete.pwcommerce_tax_override_new";
			PWCommerceCommonScripts.reloadPageAutocomplete(selectorString);
		});
	},

	initMonitorTaxOverrideDelete: function () {
		const trashSelector =
			"div#pwcommerce_tax_overrides_wrapper span.pwcommerce_trash";
		PWCommerceCommonScripts.initMonitorItemDelete(trashSelector);
	},

	initMonitorTaxOverrideTypeChange: function () {
		document
			.getElementById("pwcommerce_tax_overrides_wrapper")
			.addEventListener("change", function (event) {
				// override type has changed
				if (
					event.target.classList.contains(
						"pwcommerce_tax_override_type"
					)
				) {
					const select = event.target;
					const selectedValue = select.value;
					const parentUL = select.closest("ul.Inputfields");
					const categoryPageAutocomplete = parentUL.querySelector(
						".pwcommerce_tax_override_category"
					);
					// if override type is 'shipping', hide category pageautocomplete
					if (selectedValue === "shipping") {
						categoryPageAutocomplete.classList.add("pwcommerce_hide");
					}
					// remove 'hide' class category pageautocomplete
					else {
						categoryPageAutocomplete.classList.remove(
							"pwcommerce_hide"
						);
					}
				}
			});
	},
};

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	// @TODO: IN FUTURE, REFACTOR TO REUSE ACROSS DIFFERENT INPUTFIELDS! WILL NEED TO BE CALLED IN SEPARATE FILE
	if (typeof htmx !== "undefined") {
		InputfieldPWCommerceTaxOverrides.listenToHTMXRequests();
	}
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (ProcessPWCommerce) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	);
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		InputfieldPWCommerceTaxOverrides.initMonitorTaxOverrideDelete();
		InputfieldPWCommerceTaxOverrides.initMonitorTaxOverrideTypeChange();
	}
	// end: if in pwcommerce shop context
});

//--------------
