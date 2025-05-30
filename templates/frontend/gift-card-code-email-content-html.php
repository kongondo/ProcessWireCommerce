<?php

namespace ProcessWire;

/*
 *
 *
 *
 *
 *
 *
 * Want to customize this template? Please do not edit directly!
 *
 * Just copy this file into /site/templates/pwcommerce/frontend/invoice-content-html.php to modify
 *
 *
 *
 *
 *
 **/

// This is partial template for content for the email sent to customer for their manually issued gift card.
/*
# PROPERTIES
$denomination/value: float
$shopName: string
$giftCardCode: string (DO NOT EDIT THIS! DO NOT EXCLUDE THIS!)
$endDate: string (DO NOT EDIT THIS! DO NOT EXCLUDE THIS IF NOT EMPTY!)

*/
$out = "";
$valueAsCurrency = $pwcommerce->getValueFormattedAsCurrencyForShop($denomination);

$message1 = sprintf(__('Your %1$s for %2$s is active. Keep this email or write down your gift card number.'), $valueAsCurrency, $shopName);
$message2 = sprintf(__("Your Gift Card Number is: %s."), $giftCardCode);
$message3 = null;
if (!empty($endDate)) {
  $message3 = sprintf(__("Please note that your Gift Card expires on %s. You will not be able to use this Gift Card after this date."), $endDate);
}

$out .= "<p>{$message1}</p>";
$out .= "<p>{$message2}</p>";
if (!empty($message3)) {
  $out .= "<p>{$message3}</p>";
}

// ------------
echo $out;