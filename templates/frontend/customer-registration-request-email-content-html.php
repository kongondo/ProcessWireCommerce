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
 * Just copy this file into /site/templates/pwcommerce/frontend/customer-registration-request-email-content-html.php to modify
 *
 *
 *
 *
 *
 **/

// This is partial template for content for the email sent to customer to request they register an account with the shop.
// NOTE: THE ACTUAL IMPLEMENTATION OF THE REGISTRATION IS UP TO THE DEVELOPER!
// E.G. SEND TEMPORARY PASSWORD (NOT RECOMMENDED), ACTIVATION LINK, ETC.
/*
# PROPERTIES
$customer: WireData - This holds the customer email, first and last names, etc
$shopName: string: - The name of the shop (if completed in settings).
$tempPass: string- The temporary password set for user created for this customer
$newUser: User- The new user created for this new customer

*/
// DEVELOPER: EDIT THIS AND IMPLEMENT CUSTOMER REGISTRATION
$out = "Customer Registration Request.";

// ------------
echo $out;