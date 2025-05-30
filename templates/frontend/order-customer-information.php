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
 * Just copy this file into /site/templates/pwcommerce/frontend/order-customer-information.php to modify
 *
 *
 *
 *
 *
 **/

// @NOTE: @see documenation (WIP) for all potentially available Order Customer Details/Information

?>
<div class='customer'>
  <?php echo $orderCustomer->firstName . " " . $orderCustomer->lastName ?><br />
  <?php
  $out = "";
  // --------
  // build customer details markup
  if ($orderCustomer->shippingAddressLineOne)
    $out .= $orderCustomer->shippingAddressLineOne . "<br />";
  if ($orderCustomer->shippingAddressLineTwo)
    $out .= $orderCustomer->shippingAddressLineTwo . "<br />";
  if ($orderCustomer->shippingAddressCity || $orderCustomer->shippingAddressPostalCode) {
    if ($orderCustomer->shippingAddressPostalCode)
      $out .= $orderCustomer->shippingAddressPostalCode . " ";
    $out .= $orderCustomer->shippingAddressCity;
    $out .= "<br />";
  }
  if ($orderCustomer->shippingAddressPhone)
    $out .= $orderCustomer->shippingAddressPhone . "<br />";

  // ------
  echo $out;
  ?>
</div>