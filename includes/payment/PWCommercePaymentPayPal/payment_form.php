<?php

namespace ProcessWire;

$paymentCustomScriptSrc = $config->urls->siteModules . "ProcessWireCommerce/includes/payment/PWCommercePaymentPayPal/PWCommercePaymentPayPal.js";

?>

<!-- Set up a container element for the PayPal button -->
<div id="paypal-button-container"></div>

<?php
$out = "";
// <!-- Include the PayPal JavaScript SDK -->
$out .= "<script src='https://www.paypal.com/sdk/js?client-id={$clientID}&currency={$currency}'></script>";
// INCLUDE CUSTOM JavaScript for PayPal checkout
$out .= "<script src='{$paymentCustomScriptSrc}'></script>";
// cancel and fail urls for JS REDIRECT
$out .= "<input id='cancel_url' value='{$cancelUrl}' type='hidden'>" .
  "<input id='fail_url' value='{$failUrl}' type='hidden'>";
// ------------
echo $out;
