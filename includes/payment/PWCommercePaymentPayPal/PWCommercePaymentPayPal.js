const PWCommercePaymentPayPal = {
	/**
	 * Init PayPal Smart button for checkout.
	 *
	 */
	init: function () {
		const paypalButtonID = "#paypal-button-container"
		PWCommercePaymentPayPal.renderPayPalMarkup(paypalButtonID)
	},

	renderPayPalMarkup: function (paypal_button_id) {
		// Render the PayPal button into #paypal-button-container
		paypal
			.Buttons({
				// Call your server to set up the transaction
				createOrder: function (data, actions) {
					return fetch("/payment/create/", {
						method: "post",
						headers: {
							"X-Requested-With": "XMLHttpRequest",
						},
					})
						.then(function (res) {
							return res.json()
						})
						.then(function (orderData) {
							return orderData.id
						})
				},

				// Call your server to finalize the transaction
				onApprove: function (data, actions) {
					return fetch("/payment/capture/?orderID=" + data.orderID, {
						method: "post",
						headers: {
							"X-Requested-With": "XMLHttpRequest",
						},
					})
						.then(function (res) {
							return res.json()
						})
						.then(function (orderData) {
							// Three cases to handle:
							//   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
							//   (2) Other non-recoverable errors -> Show a failure message
							//   (3) Successful transaction -> Show confirmation or thank you

							const errorDetail =
								Array.isArray(orderData.details) && orderData.details[0]

							if (errorDetail && errorDetail.issue === "INSTRUMENT_DECLINED") {
								return actions.restart() // Recoverable state, per:
								// https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
							}

							if (errorDetail) {
								const msg = "Sorry, your transaction could not be processed."
								if (errorDetail.description)
									msg += "\n\n" + errorDetail.description
								if (orderData.debug_id) msg += " (" + orderData.debug_id + ")"
								//  redirect to /checkout/confirmation/?failure=
								// get element with fail url
								const failPaymentRedirectURLElement =
									document.getElementById("fail_url")
								if (failPaymentRedirectURLElement) {
									const failURL = failPaymentRedirectURLElement.value + msg
									// redirect to fail page
									actions.redirect(failURL)
								}
							}

							// Successful capture! For demo purposes:
							// REDIRECT TO SUCCESS PAGE:
							actions.redirect(orderData.redirectURL)
						})
				},
				onCancel: function (data, actions) {
					// get element with cancel url
					const cancelPaymentRedirectURLElement =
						document.getElementById("cancel_url")
					if (cancelPaymentRedirectURLElement) {
						// redirect to cancel page
						actions.redirect(cancelPaymentRedirectURLElement.value)
					}
				},
			})
			.render(paypal_button_id)
	},
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	PWCommercePaymentPayPal.init()
})
