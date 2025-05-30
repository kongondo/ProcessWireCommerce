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



// TODO HERE $order->id or $order->orderID? The former is the processwire page; the latter an auto increment field
echo "<h1 id='order_invoice_number_header'>" . __("Order Invoice:#") . " {$order->id}</h1>";
// TODO REVISIT!
echo "<h2 id='order_invoice_thanks_header'>" . __("Thank you for your custom.") . "</h2>";

// ORDER DOWNLOADS
/** @var array $downloads */
if (!empty($downloads)) {
  /** @var TemplateFile $t */
  $t = $pwcommerce->getPWCommerceTemplate("order-downloadlinks.php");
  $t->set("downloads", $downloads);
  echo $t->render();
}

// ORDER CUSTOMER
/** @var TemplateFile $t */
$t = $pwcommerce->getPWCommerceTemplate("order-customer-information.php");
/** @var WireData $orderCustomer */
$t->set("orderCustomer", $orderCustomer);
echo $t->render();

// ORDER META
/** @var TemplateFile $t */
$t = $pwcommerce->getPWCommerceTemplate("order-meta-information.php");
/** @var WireData $order */
$t->set("order", $order);
echo $t->render();

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
echo $t->render();