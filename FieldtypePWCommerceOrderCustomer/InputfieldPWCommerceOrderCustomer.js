const InputfieldPWCommerceOrderCustomer = {
	initMonitorCopyOrderCustomerShippingNamesFromMainCustomerNames: function () {
		const copyNamesElement = document.getElementById(
			"pwcommerce_customer_copy_shipping_names_from_main_names"
		)
		if (copyNamesElement) {
			// add event listener to copy customer main names to order shipping names
			copyNamesElement.addEventListener(
				"click",
				InputfieldPWCommerceOrderCustomer.handleCopyCustomerShippingNamesFromMainCustomerNames,
				false
			)
		}
	},

	// ~~~~~~~~~~~~~~~~~

	handleCopyCustomerShippingNamesFromMainCustomerNames: function (event) {
		// 'customer_name_id' -> ID of inputs with customer names whose values to copy
		// 'shipping_name_id' -> IDs of inputs with customer shipping names to replace
		const customerNamesToCopyReplaceElementsIDs = [
			// first name
			{
				customer_name_id: "pwcommerce_order_customer_first_name",
				shipping_name_id: "pwcommerce_order_customer_shipping_address_first_name",
			},
			// middle name
			{
				customer_name_id: "pwcommerce_order_customer_middle_name",
				shipping_name_id:
					"pwcommerce_order_customer_shipping_address_middle_name",
			},
			// last name
			{
				customer_name_id: "pwcommerce_order_customer_last_name",
				shipping_name_id: "pwcommerce_order_customer_shipping_address_last_name",
			},
		]
		// ----
		for (const elementsIDs of customerNamesToCopyReplaceElementsIDs) {
			const copyElement = document.getElementById(
				elementsIDs["customer_name_id"]
			)
			if (copyElement) {
				const replaceElement = document.getElementById(
					elementsIDs["shipping_name_id"]
				)
				if (replaceElement) {
					replaceElement.value = copyElement.value
				}
			}
		}
	},
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (ProcessPWCommerce) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	)
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		InputfieldPWCommerceOrderCustomer.initMonitorCopyOrderCustomerShippingNamesFromMainCustomerNames()
		// ----------
	}
	// end: if in pwcommerce shop context
})

//--------------
