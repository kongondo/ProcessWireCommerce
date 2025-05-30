<?php

namespace ProcessWire;

/*
 * Template for customer to confirm shipping/delivery.
 *
 * This is used in case more than one shipping rate has been matched.
 * For instance, a free slower delivery and an express, faster, paid delivery/shipping.
 *
 *
 * Want to customize this template? Please do not edit directly!
 *
 * Just copy this file into /site/templates/pwcommerce/frontend/checkout-shipping-confirmation-html.php to modify
 *
 *
 *
 *
 *
 **/

$out = "";

// -------------
$info = "<p>" . __("Please select your preferred shipping.") . "</p>";
foreach ($orderMatchedShippingRates as $matchedShippingRate) {
    // ----------
    // @note: in this case we show the rate itself. It doesn't yet include any taxes (if applicable). The final shipping fee will be shown once a rate is selected
    $shippingFee = $pwcommerce->getValueFormattedAsCurrencyForShop($matchedShippingRate->shippingRate);
    // --------
    $shippingRateID = $matchedShippingRate->shippingRateID;
    $out .=
        "<input type='radio' name='order_selected_shipping_rate' value='{$shippingRateID}' id='order_selected_shipping_rate_{$shippingRateID}' required>" .
        "<label for='order_selected_shipping_rate_{$shippingRateID}'>{$matchedShippingRate->shippingRateName} ({$shippingFee})</label>";
}
// TODO WHERE TO POST THIS FORM? i think pwcommerce/shipping??

?>
<?php
// SHIPPING SELECTION INFO
echo $info;
?>
<form method="post" class="pwcommerce-checkout-shipping-confirmation" action="./">
    <?php
    // RADIO INPUTS
    echo $out;
    ?>
    <button type='submit' name='shippingConfirmationForm' value='1'>
        <?php echo __("Confirm Shipping"); ?>
    </button>
</form>