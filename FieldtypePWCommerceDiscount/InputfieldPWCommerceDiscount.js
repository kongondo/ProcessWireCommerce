const InputfieldPWCommerceDiscount = {
	listenToHTMXRequests: function () {
		// after settle
		// @note: aftersettle is fired AFTER  afterswap
		htmx.on("htmx:afterSettle", function (event) {
			// RUN POST SETTLE OPS
			InputfieldPWCommerceDiscount.runAfterSettleOperations(event)
		})
	},

	/**
	 * Run afterSettle operations (after htmx swap).
	 * These depend on the htmx request context.
	 * Use this so that alpine js can work on 'settled' dom contents.
	 * @param {object} event Object containing the event that triggered the request or custom object with post-op details.
	 */
	runAfterSettleOperations: function (event) {
		// @note: currently in use only with generate automatic discount code
		InputfieldPWCommerceDiscount.showHighlight(
			"#wrap_pwcommerce_discount_method_code"
		)
	},

	/**
	 * Listen to discounts radio changes.
	 *
	 * We use to send window notification to Alpine.js to determine toggle show various markup.
	 * We cannot x-model PW radio inputs. We need this to toggle show markup or other actions.
	 */
	initListenDiscountRadioElements: function () {
		// @note: this is a catch all for various radios
		const discountRadioElements = document.querySelectorAll(
			"li.pwcommerce_discounts_radios_wrapper input"
		)

		if (discountRadioElements) {
			for (const discountsRadioElement of discountRadioElements) {
				// add event listener to each discount radio
				discountsRadioElement.addEventListener(
					"change",
					InputfieldPWCommerceDiscount.handleDiscountsRadioChange,
					false
				)
			}
		}
	},

	handleDiscountsRadioChange: function (event) {
		const selectedRadioElement = event.target
		const selectedRadioValue = selectedRadioElement.value
		const selectedRadioParentElement = selectedRadioElement.closest(
			"li.InputfieldRadios"
		)

		// send the window event to Alpine.JS
		const eventName = "pwcommercediscountradiochangenotification"
		// const eventDetail = selectedRadioValue

		const notificationType =
			selectedRadioParentElement.dataset.discountRadioChangeType

		const eventDetail = {
			type: notificationType,
			value: selectedRadioValue,
		}

		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	// +++++++++++++++
	/**
	 *  Highlight an inputfield on demand.
	 * Use function in Inputfields.js.
	 * @param {*} field Can be .class, id or jQuery Object.
	 */
	showHighlight: function (field) {
		Inputfields.highlight(field)
	},
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	if (typeof htmx !== "undefined") {
		InputfieldPWCommerceDiscount.listenToHTMXRequests()
	}
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (InputfieldPWCommerceDiscount) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	)
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		// listen to discounts radio changes
		InputfieldPWCommerceDiscount.initListenDiscountRadioElements()
	}
	// end: if in pwcommerce shop context
})

// ALPINE
document.addEventListener("alpine:init", () => {
	// @note: hidden input to detect if a pwcommerce page is being edited/viewed inside the pwcommerce shop (InputfieldPWCommerceDiscount) or in usual ProcessWire page edit. If the latter, don't init Aline.js!
	const pwcommerceIsInShopContext = document.getElementById(
		"pwcommerce_is_in_shop_context"
	)
	// ARE WE IN PWCOMMERCE SHOP CONTEXT?
	if (pwcommerceIsInShopContext) {
		// YES: GOOD TO GO!
		Alpine.store("InputfieldPWCommerceDiscountStore", {
			// discount method (automatic|code)
			discount_method_type_selected: null,
			is_auto_checked_discount_minimum_requirement_type: false,
			// value
			discount_value_type_selected: null,
			discount_value: null,
			// minimum purchase requirement
			discount_minimum_requirement_selected: null,
			// --
			// discount types
			discount_percentage_types: [
				// GENERIC
				"percentage",
				// WHOLE ORDER
				"whole_order_percentage",
				// PRODUCTS
				"products_percentage",
				// CATEGORIES
				"categories_percentage",
			],

			// --
			// dates
			active_from: null,
			active_from: null,
			is_error_discount_active_from_date: false,
			is_error_discount_active_to_date: false,
			discount_active_from_date_error_text: null,
			discount_active_to_date_error_text: null,
			// -------

			// OTHER BOOLEANS
			// first page load, no initial data set
			is_first_load: true,

			// ----------
		})
		Alpine.data("InputfieldPWCommerceDiscountData", () => ({
			//---------------
			// FUNCTIONS
			// #######
			// INIT
			// init() {
			// 	console.log("InputfieldPWCommerceDiscount - init")
			// },

			initDiscountRadioElements(radio_values) {
				for (const [prop, value] of Object.entries(radio_values)) {
					// set on load values for various discount radio inputs
					// so that we can affect some Alpine.JS attributes
					this.setStoreValue(prop, value)
				}

				// --------
				// on load: if currently selected discount method is automatic
				// we need to hide the radio input 'no minimum requirements'
				const selectedDiscountMethodType = this.getStoreValue(
					"discount_method_type_selected"
				)

				if (selectedDiscountMethodType === "automatic_discount") {
					const radioNoMinReqElement = document.getElementById(
						"pwcommerce_discount_minimum_requirement_type_none"
					)
					let radioNoMinReqElementParent
					if (radioNoMinReqElement) {
						radioNoMinReqElementParent = radioNoMinReqElement.closest("li")
						radioNoMinReqElementParent.classList.add("pwcommerce_hide")
					}
				}
			},
			// ********

			handleDiscountRadioChange(event) {
				const eventDetail = event.detail
				const radioType = eventDetail.type
				const radioValue = eventDetail.value
				// -----
				if (radioType === "discount_value_type") {
					// HANDLE DISCOUNT VALUE RADIO CHANGE
					this.setStoreValue("discount_value_type_selected", radioValue)
					// ------
					// also see if to set max percentage value to '100'
					this.handleDiscountValueChange()
				} else if (radioType === "discount_minimum_requirement_type") {
					// HANDLE DISCOUNT MINIMUM PURCHASE REQUIREMENTS RADIO CHANGE
					this.setStoreValue(
						"discount_minimum_requirement_selected",
						radioValue
					)
					// also clear previoulsy set 'auto checked'
					this.setStoreValue(
						"is_auto_checked_discount_minimum_requirement_type",
						false
					)
				} else if (radioType === "discount_method_type") {
					// HANDLE DISCOUNT METHOD RADIO CHANGE
					// this.setStoreValue("discount_method_type_selected", radioValue)
					// get the radio 'no minimum requirements'
					// we hide/show && check/uncheck it depending on value of discount method
					const radioNoMinReqElement = document.getElementById(
						"pwcommerce_discount_minimum_requirement_type_none"
					)
					const textMinPurchaseAmntWrapperElement = document.getElementById(
						"wrap_pwcommerce_discount_minimum_requirement"
					)
					let radioNoMinReqElementParent
					if (radioNoMinReqElement) {
						radioNoMinReqElementParent = radioNoMinReqElement.closest("li")
					}

					// #####
					if (radioValue === "automatic_discount") {
						// AUTOMATIC DISCOUNT
						// +++++++

						// hide radio 'no minimum requirements'
						if (radioNoMinReqElementParent) {
							radioNoMinReqElementParent.classList.add("pwcommerce_hide")
						}
						// -----
						// also if this radio is currently checked, change checked radio to 'purchase amount as checked'
						// plus override pw show-if in order to show the purchase amount text field
						// plus set 'discount_minimum_requirement_type' to 'purchase' so as to show description in amount text field
						// then set a flag to state it was auto checked
						if (radioNoMinReqElement && radioNoMinReqElement.checked) {
							// find and check radio 'minimum purchase amount'
							const radioMinPurchaseAmntElement = document.getElementById(
								"pwcommerce_discount_minimum_requirement_type_purchase"
							)
							if (radioMinPurchaseAmntElement) {
								radioMinPurchaseAmntElement.checked = true

								if (textMinPurchaseAmntWrapperElement) {
									textMinPurchaseAmntWrapperElement.classList.remove(
										"uk-hidden",
										"InputfieldStateHidden"
									)
									this.setStoreValue(
										"discount_minimum_requirement_selected",
										"purchase"
									)
								}
							}
							// -----
							// set auto checked flag as true
							this.setStoreValue(
								"is_auto_checked_discount_minimum_requirement_type",
								true
							)
						}
					} else {
						// DISCOUNT CODE
						// +++++++
						// if auto checked is true, find radio 'no minimum requirements' and check it
						// plus reset pw show-if in order to hide the purchase amount text field
						// plus reset 'discount_minimum_requirement_type' to 'none'
						const isAutoChecked = this.getStoreValue(
							"is_auto_checked_discount_minimum_requirement_type"
						)
						if (isAutoChecked) {
							// set radio 'no minimum requirements' as checked
							if (radioNoMinReqElement) {
								radioNoMinReqElement.checked = true
							}
							// also clear previoulsy set 'auto checked'
							this.setStoreValue(
								"is_auto_checked_discount_minimum_requirement_type",
								false
							)
							// ---
							if (textMinPurchaseAmntWrapperElement) {
								textMinPurchaseAmntWrapperElement.classList.add(
									"uk-hidden",
									"InputfieldStateHidden"
								)
								this.setStoreValue(
									"discount_minimum_requirement_selected",
									"none"
								)
							}
						}
						// unhide radio element 'no minimum requirements'
						if (radioNoMinReqElementParent) {
							radioNoMinReqElementParent.classList.remove("pwcommerce_hide")
						}
					}
				} else if (radioType === "discount_customer_buys_minimum_type") {
					// HANDLE DISCOUNT CUSTOMER BUYS MINIMUM RADIO CHANGE
					// FOR BOGO
					this.setStoreValue(
						"discount_customer_buys_minimum_type_selected",
						radioValue
					)
				}
			},

			handleDiscountValueChange() {
				// ensure discount value for discount of type percentage does not exceed 100%
				const selectedDiscountType = this.getStoreValue(
					"discount_value_type_selected"
				)
				let discountValue = this.getStoreValue("discount_value")
				const discountPercentageTypes = this.getStoreValue(
					"discount_percentage_types"
				)

				if (discountPercentageTypes.includes(selectedDiscountType)) {
					if (discountValue > 100) {
						discountValue = 100
						this.setStoreValue("discount_value", 100)
					}
				}
			},

			//~~~~~~~~~~~~~~~~~
			/**
			 * Get the value of a given store property.
			 * @param string property Property in store whose value to return
			 * @returns {any}
			 */
			getStoreValue(property) {
				return this.$store.InputfieldPWCommerceDiscountStore[property]
			},
			//~~~~~~~~~~~~~~~~~

			/**
			 * Set a store property value.
			 * @param any value Value to set in store.
			 * @return {void}.
			 */
			setStoreValue(property, value) {
				this.$store.InputfieldPWCommerceDiscountStore[property] = value
			},
		}))
	}
	// end: if in pwcommerce shop context
})

//--------------
