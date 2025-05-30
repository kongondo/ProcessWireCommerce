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
 * Just copy this file into /site/templates/pwcommerce/frontend/invoice-content-text.php to modify
 *
 *
 *
 *
 *
 **/

// This is text presentation of invoice information. Can be used in text emails.

// ==================
// TODO WIP!!

// TODO -> WILL NEED TO DO THIS DIFFERENTLY IN CASE EMAIL IS NOT GETTING SENT IMMEDIATELY! WILL NEED TO GET FROM REAL ORDER! SO, CREATE API FOR THAT, FROM pwcommerce itself! AND NOT PROCESSORDER!

// TODO REFACTOR BELOW!
?>

<?= __("Invoice") ?>

=========

<?= __("Invoice #") ?>
<?= $order->id ?>

<?= __("Date") ?>:
<?= date("Y-m-d", $order->created) ?>

<?= $orderCustomer->firstName . " " . $orderCustomer->lastName ?>

<?= $orderCustomer->shippingAddressLineOne ?>

<?php
if ($orderCustomer->shippingAddressLineTwo)
  echo $orderCustomer->shippingAddressLineTwo . "\n";
if ($orderCustomer->shippingAddressPhone)
  echo $orderCustomer->shippingAddressPhone . "\n";
?>

<?= __("Recipient") ?>

=========

<?= $orderCustomer->shippingAddressFirstName . " " . $orderCustomer->shippingAddressLastName ?>

<?php
// TODO: ADD REGION?
if ($orderCustomer->shippingAddressLineOne) {
  echo $orderCustomer->shippingAddressLineOne . "\n";
}
if ($orderCustomer->shippingAddressLineTwo) {
  echo $orderCustomer->shippingAddressLineTwo . "\n";
}
if ($orderCustomer->shippingAddressCity || $orderCustomer->shippingAddressPostalCode) {
  if ($orderCustomer->shippingAddressPostalCode) {
    echo $orderCustomer->shippingAddressPostalCode . " ";
    echo $orderCustomer->shippingAddressCity . "\n";
    echo $orderCustomer->shippingAddressCountry . "\n";
  }
}
if ($orderCustomer->shippingAddressPhone) {
  echo $orderCustomer->shippingAddressPhone . "\n";
}
?>

<?= __("Products") ?>

=========

<?php

// ---------------------------
// TODO WIP

// TODO LINE ITEM PRICES INC OR EX TAXES?
/** @var WireArray $orderLineItems */
foreach ($orderLineItems as $orderLineItem) {

  echo $orderLineItem->quantity . " x " . $orderLineItem->productTitle . " " . $pwcommerce->renderCartPriceAndCurrency($orderLineItem->unitPrice) . " " . $pwcommerce->renderCartPriceAndCurrency($orderLineItem->totalPrice) . "\n";
  if ($orderLineItem->taxAmountTotal) {
    $taxTotalAmount = $orderLineItem->taxAmountTotal;
    echo $orderLineItem->taxName . " " . $pwcommerce->renderCartPriceAndCurrency($taxTotalAmount) . "\n";
  }
  echo "\n";
}
?>

<?= __("Total (incl. tax)") ?>:
<?= $pwcommerce->renderCartPriceAndCurrency($order->totalPrice) . "\n" ?>
<?php
// TODO WIP CHANGE TO LINE ITEMS?
foreach ($pwcommerce->getOrderTaxTotals($orderLineItems) as $key => $value) {
  echo $key . ": " . $pwcommerce->renderCartPriceAndCurrency($value) . "\n";
}
?>