<?php

namespace ProcessWire;

$paymentCustomScriptSrc = $config->urls->siteModules . "PWCommerce/includes/payment/PWCommercePaymentStripe/PWCommercePaymentStripe.js";
// TODO @KONGONDO - WIP

// TODO: PREFILL BELOW WITH CUSTOMER FULL NAME(?) + email so they don't need to enter again

// TODO -> HANDLE CASES WHERE PAYMENT IS ZERO, HENCE NO STRIPE BUTTON needed! OR FORM!

$cardHolderNameStr = __("Card Holder Name");
$payStr = __("Pay");
$payStr .= " {$amountAsCurrency}";

?>

<style>
	div#spripe_pay_button_wrapper {
		display: flex;
		margin-top: 20px;
	}

	#stripe_pay_button {
		margin-right: 5px;
	}

	#stripe_pay_button.fade {
		cursor: not-allowed;
	}

	.fade {
		/* display: none; */
		opacity: 0.5;
	}

	.spinner {
		border: 16px solid #f3f3f3;
		border-radius: 50%;
		border: 4px dotted gray;
		border-top: transparent;
		width: 30px;
		height: 30px;
		-webkit-animation: spin 2s linear infinite;
		animation: spin 2s linear infinite;
	}

	@-webkit-keyframes spin {
		0% {
			-webkit-transform: rotate(0deg);
		}

		100% {
			-webkit-transform: rotate(360deg);
		}
	}

	@keyframes spin {
		0% {
			transform: rotate(0deg);
		}

		100% {
			transform: rotate(360deg);
		}
	}
</style>

<!-- Display payment form -->
<form id="payment_form" data-secret="<?= $intentClientSecret ?>">
	<!-- <div id="stripe_fullname_wrapper">
		<label for="stripe_fullname" id="stripe_fullname_label"><?= $cardHolderNameStr ?></label>
		<input type="text" id="stripe_fullname" name="stripe_fullname" required>
	</div> -->
	<div id="payment_element">
		<!--Stripe.js injects the Payment Element here-->
	</div>

	<!-- submit button -->
	<div id="spripe_pay_button_wrapper">
		<button id="stripe_pay_button">
			<!-- <div class="spinner hidden" id="spinner"></div> -->
			<span id="button-text">
				<?= $payStr ?>
			</span>
		</button>
		<div id="spinner" class="spinner hidden"></div>
	</div>
	<!-- We'll put the error messages in this element -->
	<div id="payment_message" class="hidden"></div>
</form>

<?php
$out = "";
// <!-- Include the Stripe JavaScript SDK -->
$out .= "<script src='https://js.stripe.com/v3/'></script>";
// INCLUDE CUSTOM JavaScript for Stripe Elements checkout
$out .= "<script src='{$paymentCustomScriptSrc}'></script>";

$out .=
	// cancel url for JS REDIRECT
	"<input id='cancel_url' value='{$cancelUrl}' type='hidden'>" .
	// fail url for JS REDIRECT
	"<input id='fail_url' value='{$failUrl}' type='hidden'>" .
	// success url for Stripe return_url
	"<input id='return_url' value='{$returnUrl}' type='hidden'>" .
	// client ID (publishable key) for Stripe Elements
	"<input id='stripe_publishable_key' value='{$clientID}' type='hidden'>";
// ------------
echo $out;
?>

<script>
	<?php
	// $script = "<script>PWCommercePaymentStripeElementsAppearance = " . json_encode($stripeElementsAppearance) . ';</script>';
	$script = "PWCommercePaymentStripeElementsAppearance = " . json_encode($stripeElementsAppearance) . ';';
	echo $script;

	?>
</script>