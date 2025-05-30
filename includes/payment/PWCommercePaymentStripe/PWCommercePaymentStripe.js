const PWCommercePaymentStripe = {
	/**
	/**
	 * Init Stripe Elements.
	 *
	 */
	init: function () {
		PWCommercePaymentStripe.renderStripeMarkup()
	},

	getStripe: function () {
		// get the publishable API key
		const publishableKey = PWCommercePaymentStripe.getPublishableKey()
		const stripe = Stripe(publishableKey)
		return stripe
	},

	getStripeElements: function (stripe) {
		const paymentIntentsClientSecret =
			PWCommercePaymentStripe.getPaymentIntentsClientSecret()
		// -----
		let elements
		const appearance = PWCommercePaymentStripe.getStripeElementsAppearance()
		// build options for Stripe element
		if (paymentIntentsClientSecret) {
			const options = {
				clientSecret: paymentIntentsClientSecret,
			}
			// if we have appearance, add it to options
			if (appearance.length) {
				// Fully customizable with appearance API.
				options["appearance"] = appearance
			}
			// ------
			elements = stripe.elements(options)
		}

		// ------
		return elements
	},

	getStripeElementsAppearance: function () {
		let appearance = {}
		const stripeElementsAppearance = PWCommercePaymentStripeElementsAppearance
		if (stripeElementsAppearance) {
			appearance = stripeElementsAppearance
		}
		// -----
		return appearance
	},

	getPaymentElement: function (elements, mount_id) {
		const paymentElement = elements.create("payment")
		paymentElement.mount(`#${mount_id}`)
		//-------
		return paymentElement
	},

	getPublishableKey: function () {
		let publishableKey
		const publishableKeyElement = document.getElementById(
			"stripe_publishable_key"
		)
		if (publishableKeyElement) {
			publishableKey = publishableKeyElement.value
		}
		// -------
		return publishableKey
	},

	getPaymentIntentsClientSecret: function () {
		let paymentIntentsClientSecret
		const stripeFormElement = PWCommercePaymentStripe.getPaymentForm()
		if (stripeFormElement) {
			paymentIntentsClientSecret = stripeFormElement.dataset.secret
		}
		// -----
		return paymentIntentsClientSecret
	},

	getPaymentForm: function () {
		return document.getElementById("payment_form")
	},

	getReturnURL: function () {
		const returnURLElement = document.getElementById("return_url")
		return returnURLElement.value
	},

	processPaymentFormSubmit: function (stripe, elements) {
		const stripeFormElement = PWCommercePaymentStripe.getPaymentForm()
		if (stripeFormElement) {
			stripeFormElement.addEventListener(
				"submit",
				PWCommercePaymentStripe.handleSubmit.bind(null, stripe, elements),
				false
			)
		}
	},

	handleSubmit: async function (stripe, elements, event) {
		event.preventDefault()
		PWCommercePaymentStripe.setLoading(true)

		/////////////////////////
		// @see: https://stripe.com/docs/payments/quickstart AND https://stripe.com/docs/payments/accept-a-payment?platform=web&ui=elements#enable-payment-methods
		const { error } = await stripe.confirmPayment({
			elements,
			confirmParams: {
				// payment completion page
				return_url: PWCommercePaymentStripe.getReturnURL(),
			},
		})
		// console.log("PWCommercePaymentStripe: handleSubmit - error", error)

		// @TODO CHOOSE ONE OF ERROR HANDLING BELOW ++ DECIDE IF TO SHOW ERROR VIA SERVER OR CLIENT
		// This point will only be reached if there is an immediate error when
		// confirming the payment. Otherwise, your customer will be redirected to
		// your `return_url`. For some payment methods like iDEAL, your customer will
		// be redirected to an intermediate site first to authorize the payment, then
		// redirected to the `return_url`.
		// @TODO MAKE TRANSLATABLE ???
		/////////////////////////
		if (error.type === "card_error" || error.type === "validation_error") {
			PWCommercePaymentStripe.showMessage(error.message)
		} else {
			PWCommercePaymentStripe.showMessage("An unexpected error occured.")
		}
		// @TODO -> ALTERNATIVE
		// if (error) {
		// 	// This point will only be reached if there is an immediate error when
		// 	// confirming the payment. Show error to your customer (for example, payment
		// 	// details incomplete)
		// 	const messageContainer = document.querySelector("#error-message")
		// 	messageContainer.textContent = error.message
		// } else {
		// 	// Your customer will be redirected to your `return_url`. For some payment
		// 	// methods like iDEAL, your customer will be redirected to an intermediate
		// 	// site first to authorize the payment, then redirected to the `return_url`.
		// }
		/////////////////////////
		PWCommercePaymentStripe.setLoading(false)
	},

	// Fetches the payment intent status after payment submission
	checkStatus: async function (stripe) {
		const clientSecret = new URLSearchParams(window.location.search).get(
			"payment_intent_client_secret"
		)
		if (!clientSecret) {
			return
		}

		const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret)

		// @TODO MAKE TRANSLATABLE
		switch (paymentIntent.status) {
			case "succeeded":
				PWCommercePaymentStripe.showMessage("Payment succeeded!")
				break
			case "processing":
				PWCommercePaymentStripe.showMessage("Your payment is processing.")
				break
			case "requires_payment_method":
				PWCommercePaymentStripe.showMessage(
					"Your payment was not successful, please try again."
				)
				break
			default:
				PWCommercePaymentStripe.showMessage("Something went wrong.")
				break
		}
	},

	// ~~~~~~~~~~~~~~~~~

	renderStripeMarkup: function () {
		// Initialise Stripe.js Stripe.js with Publishable Key
		const stripe = PWCommercePaymentStripe.getStripe()
		// Initialise Stripe Elements UI Library with Payment Intents Client Secret
		const elements = PWCommercePaymentStripe.getStripeElements(stripe)
		if (elements) {
			// create the PaymentElement and mount it to our form
			const mountID = "payment_element"
			PWCommercePaymentStripe.getPaymentElement(elements, mountID)
			// handle payment form submission
			PWCommercePaymentStripe.processPaymentFormSubmit(stripe, elements)
			// checkStatus
			PWCommercePaymentStripe.checkStatus(stripe)
		} else {
			// @TODO - debug messages? + translate!
			PWCommercePaymentStripe.showMessage("Error: Payment not possible!")
		}
	},

	// ------- UI helpers -------

	showMessage: function (messageText) {
		const messageContainer = document.querySelector("#payment_message")

		messageContainer.classList.remove("hidden")
		messageContainer.textContent = messageText

		setTimeout(function () {
			messageContainer.classList.add("hidden")
			messageText.textContent = ""
		}, 4000)
	},
	// Show a spinner on payment submission
	setLoading: function (isLoading) {
		const stripePayButtonElement = document.getElementById("stripe_pay_button")
		const spinnerElement = document.getElementById("spinner")
		const stripePayButtonTextElement = document.getElementById("button-text")
		if (isLoading) {
			// Disable the stripe button and its text and show a spinner
			stripePayButtonElement.disabled = true
			stripePayButtonElement.classList.add("fade")
			// stripePayButtonTextElement.classList.add("hidden")
			// show spinner
			spinnerElement.classList.remove("hidden")
		} else {
			// enable stripe pay button and show it but hide spinner
			stripePayButtonElement.disabled = false
			stripePayButtonElement.classList.remove("fade")
			spinnerElement.classList.add("hidden")
			// stripePayButtonTextElement.classList.remove("hidden")
		}
	},
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	PWCommercePaymentStripe.init()
})
