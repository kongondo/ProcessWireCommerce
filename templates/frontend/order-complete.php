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
 * Just copy this file into /site/templates/pwcommerce/frontend/order-complete.php to modify
 *
 *
 *
 *
 *
 **/

$out = "";
// --------
// IF ORDER SESSION IS LOST, SHOW LIMITED INFORMATION
if (!empty($isLostOrderSession)) {
  // ORDER SESSION LOST! LIMIT INFO!
  $out .= "<div  id='order_complete_thank_you_wrapper' class='container mx-auto px-6 my-4'>" .
    "<p class='text-xl'>" .
    sprintf(__('Thanks for order #%d. We are processing your order and you will receive an email confirmation shortly.'), $orderID) .
    "</p>" .
    "</div>";
} else {
  // ORDER SESSION NOT LOST! SHOW MORE INFO!
  $out .= "<div  id='order_complete_thank_you_wrapper' class=''>" .
    "<p class=''>" . __("Thank you. Your order is complete.") . "</p>" .
    "</div>";

  // --------------
  // ORDER DOWNLOADS
  /** @var array $downloads */
  if (!empty($downloads)) {
    /** @var TemplateFile $t */
    $t = $pwcommerce->getPWCommerceTemplate("order-downloadlinks.php");
    $t->set("downloads", $downloads);
    $out .= $t->render();
  }

  // --------------
  // ORDER CUSTOMER INFORMATION
  /** @var TemplateFile $t */
  $t = $pwcommerce->getPWCommerceTemplate("order-customer-information.php");
  /** @var WireData $orderCustomer */
  $t->set("orderCustomer", $orderCustomer);
  $out .= $t->render();

  // --------------
  // ORDER META INFORMATION
  /** @var TemplateFile $t */
  $t = $pwcommerce->getPWCommerceTemplate("order-meta-information.php");
  /** @var WireData $order */
  $t->set("order", $order);
  $out .= $t->render();

  // TODO WIP
  // --------------
  // ORDER LINE ITEMS
  /** @var TemplateFile $t */
  $t = $pwcommerce->getPWCommerceTemplate("order-products-table.php");
  /** @var WireData $order */
  $t->set("order", $order);
  /** @var WireArray $orderLineItems */
  $t->set("orderLineItems", $orderLineItems);
  /** @var float $orderSubtotal */
  $t->set("orderSubtotal", $orderSubtotal);
  /** @var bool $isOrderGrandTotalComplete */
  $t->set("isOrderGrandTotalComplete", $isOrderGrandTotalComplete);
  /** @var bool $isOrderConfirmed */
  $t->set("isOrderConfirmed", $isOrderConfirmed);

  $out .= $t->render();
}



// -------
echo $out;
