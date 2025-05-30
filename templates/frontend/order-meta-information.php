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
 * Just copy this file into /site/templates/pwcommerce/frontend/order-meta-information.php to modify
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
        <?php echo __("Invoice #") ?>
      </span></th>
    <td><span>
        <?php echo $order->id ?>
      </span></td>
  </tr>
  <tr>
    <th><span>
        <?php echo __("Date") ?>
      </span></th>
    <td><span>
        <?php echo date($pwcommerce->getShopDateFormat(), $order->created) ?>
      </span></td>
  </tr>
</table>