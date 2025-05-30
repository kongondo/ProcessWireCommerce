const InputfieldPWCommerceTaxRates = {
	initMonitorTaxRateDelete: function () {
		const trashSelector =
			"div.pwcommerce_tax_rates_wrapper span.pwcommerce_trash";
		PWCommerceCommonScripts.initMonitorItemDelete(trashSelector);
	},
};

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (ProcessPWCommerce) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	);
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		InputfieldPWCommerceTaxRates.initMonitorTaxRateDelete();
	}
	// end: if in pwcommerce shop context
});

//--------------
