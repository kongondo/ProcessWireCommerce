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
 * Just copy this file into /site/templates/pwcommerce/frontend/order-products-table.php to modify
 *
 *
 *
 *
 *
 **/

if (!empty($isOrderConfirmed)) {
  // ORDER COMPLETE: GRAND TOTAL (total price + taxes + handling fee + shipping - discounts)
  // TODO:?
  $totalText = __("Total");
  $totalPrice = $pwcommerce->renderCartPriceAndCurrency($order->totalPrice);
} else {
  // ORDER INCOMPLETE: SUBTOTAL (total price + taxes - discounts)
  // in this case, handling shown separately + choice to select shipping rate is shown
  $totalText = __("Subtotal");
  $totalPrice = $pwcommerce->renderCartPriceAndCurrency($orderSubtotal);
}

?>
<!-- ORDER LINE ITEMS TABLE -->

<table class="inventory">
  <thead>
    <tr>
      <?php
      // thead > tr > th

      $out = "";

      $theadTrThItems = [
        'item' => __("Item"),
        'unit_price' => __("Unit Price"),
        'quantity' => __("Quantity"),
        'total_price' => __("Total Price"),
      ];

      // --------
      foreach ($theadTrThItems as $th) {
        $out .= "<th>{$th}</th>";
      }
      // ----
      echo $out;
      ?>

    </tr>
  </thead>
  <tbody>
    <?php
    // ---------------------------
    $out = "";
    if (!empty($orderLineItems)) {
      /** @var WireArray $orderLineItems */
      foreach ($orderLineItems as $orderLineItem) {
        /** @var WireData $orderLineItem */

        // -----------
        $out .=
          "<tr>" .
          // line item TITLE + tax info
          "<td><span>$orderLineItem->productTitle</span>";
        // TODO do we need this? IF YES, NOT YET IMPLEMENTED
        // $orderLineItem->pwcommerce_product_notes;
        # ++++++++++++++++++++++++++
        if ($orderLineItem->taxAmountTotal) {
          $taxTotalAmount = $orderLineItem->taxAmountTotal;
          $out .= "<small>" .
            $orderLineItem->taxName . " " . $pwcommerce->renderCartPriceAndCurrency($taxTotalAmount) . "</small>";
        }
        # ++++++++++++++++++++++++++
        // -----------
        $out .= "</td>";
        // end: title
        // ---------
        // PRICE
        // TODO: DO WE SHOW PRICE INC OR EX TAX? or does it depend where in checkout we are?
        $out .= "<td><span>" . $pwcommerce->renderCartPriceAndCurrency($orderLineItem->unitPrice) .
          "</span></td>" .
          // QUANTITY
          "<td><span>$orderLineItem->quantity</span></td>" .
          // TOTAL PRICE (of line item)
          "<td><span>" . $pwcommerce->renderCartPriceAndCurrency($orderLineItem->totalPrice) . "</span></td>" .
          "</tr>";
        // ----------
      }
    }
    // ----------
    // line items table rows
    echo $out;
    ?>
  </tbody>
</table>

<!-- ORDER SUBTOTAL/TOTAL TABLE -->
<table class="balance">
  <tbody>
    <tr>
      <td>
        <span>
          <?php echo $totalText; ?>:
        </span><span>
          <?php echo $totalPrice; ?>
        </span>
      </td>
    </tr>
    <?php
    // TODO WIP
    // taxes markup
    $out = "";
    foreach ($pwcommerce->getOrderTaxTotals($orderLineItems) as $taxShortName => $value) {
      $out .= "<tr>" .
        "<td><span>{$taxShortName}:</span><span>" . $pwcommerce->renderCartPriceAndCurrency($value) . "</span></td>" .
        "</tr>";
    }
    // -----
    // echo out subtotal/total table
    echo $out;
    ?>
  </tbody>
</table>