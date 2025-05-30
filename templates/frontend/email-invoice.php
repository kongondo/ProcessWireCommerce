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
 * Just copy this file into /site/templates/pwcommerce/frontend/email-invoice.php to modify
 *
 *
 *
 *
 *
 **/


?>

<table class="meta">
  <tr>
    <th><span>
        <?= __("Invoice #") ?>
      </span></th>
    <td><span>
        <?= $order->id ?>
      </span></td>
  </tr>
  <tr>
    <th><span>
        <?= __("Date") ?>
      </span></th>
    <td><span>
        <?= date("Y-m-d", $order->created) ?>
      </span></td>
  </tr>
</table>
<h1>
  <?= __("Recipient") ?>
</h1>
<?php
/** @var TemplateFile $t */
$t = $pwcommerce->getPWCommerceTemplate("order-customer-information.php");
/** @var WireData $orderCustomer */
$t->set("orderCustomer", $orderCustomer);
echo $t->render();
?>

<h3>
  <?= __("Products") ?>
</h3>
<?php
/** @var TemplateFile $t */
$t = $pwcommerce->getPWCommerceTemplate("order-products-table.php");
/** @var WireData $order */
$t->set("order", $order);
/** @var WireArray $orderLineItems */
$t->set("orderLineItems", $orderLineItems);
/** @var bool $isOrderGrandTotalComplete */
$t->set("isOrderGrandTotalComplete", $isOrderGrandTotalComplete);
/** @var float $orderSubtotal */
$t->set("orderSubtotal", $orderSubtotal);
/** @var float $orderGrandTotal */
$t->set("orderGrandTotal", $orderGrandTotal);
echo $t->render();

// ORDER DOWNLOADS
/** @var array $downloads */
if (!empty($downloads)) {
  /** @var TemplateFile $t */
  $t = $pwcommerce->getPWCommerceTemplate("order-downloadlinks.php");
  $t->set("downloads", $downloads);
  echo $t->render();
}