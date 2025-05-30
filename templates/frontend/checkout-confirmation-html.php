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
 * Just copy this file into /site/templates/pwcommerce/frontend/checkout-confirmation-html.php to modify
 *
 *
 *
 *
 *
 **/


// -------

?>
<div>
	<h2>
		<?php echo __("Please verify your order"); ?>
	</h2>
</div>

<?php
/** @var TemplateFile $t */
// TODO: ONLY RENDER IF CUSTOMER INFORMATION WAS ENTERED!
$t = $pwcommerce->getPWCommerceTemplate("order-customer-information.php");
// ==============
// @note
// here we set the property order to the value of $order so that it can be used in the newly created virtual TemplateFile $t which uses the file "order-customer-information.php" AS ITS TEMPLATE FILE
// @note: TemplateFile extends WireData, hence here this is what happens: parent::set($property, $value);
// @note: $order itself was alreay set to this file, as a TemplateFile property in PWCommerceCheckout.php
// @see: PWCommerceCheckout::renderConfirmation
/** @var WireData $order */
$t->set("order", $order);
/** @var WireData $orderCustomer */
$t->set("orderCustomer", $orderCustomer);
echo $t->render();
?>

<?php
// TODO @note: NOT IN USE FOR NOW
// if ($order->pwcommerce_discount_code) {
//     $code = $sanitizer->selectorValue($order->pwcommerce_discount_code);
//     $dc = $pages->get("template=paddiscount, title=$code");
//     if ($dc->id) echo "<p>" . sprintf(__('Discount code %1$s is applied, and your order gets %2$s%% discount.'), $code, $dc->pwcommerce_percentage) . "</p>";
// }
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

/** @var TemplateFile $t */
$t = $pwcommerce->getPWCommerceTemplate("order-shipping-information.php");
/** @var WireData $order */
$t->set("order", $order);
/** @var WireArray $orderMatchedShippingRates */
$t->set("orderMatchedShippingRates", $orderMatchedShippingRates);
/** @var WireData $orderHandlingFeeValues */
$t->set("orderHandlingFeeValues", $orderHandlingFeeValues);
/** @var bool $isOrderGrandTotalComplete */
$t->set("isOrderGrandTotalComplete", $isOrderGrandTotalComplete);
/** @var float $orderGrandTotal */
$t->set("orderGrandTotal", $orderGrandTotal);
echo $t->render();