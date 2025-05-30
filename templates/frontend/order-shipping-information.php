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
 * Just copy this file into /site/templates/pwcommerce/frontend/order-shipping-information.php to modify
 *
 *
 *
 *
 *
 **/
// ==================

/**
 * Displays an order's matched shipping rate(s) and handling fees.
 *
 *
 */

/** @var WireArray $orderMatchedShippingRates */
// @note: these have aliases in the API so examining the WireArray might reveal more than those listed here.
//  $orderMatchedShippingRates
// $shippingRateProperties = [
//     'shippingRate',
//     'shippingRateName',
//     'shippingRateID',
//     'shippingRateCriteriaType',
//     'shippingRateCriteriaMinimum',
//     'shippingRateCriteriaMaximum',
//     'shippingRateDeliveryTimeMinimumDays',
//     'shippingRateDeliveryTimeMaximumDays',
// ];

$out = '';
// --------
$out .= "<div id='order_shipping_information_and_total_wrapper'>";

// if we have matched shipping rates for customer country
if ($orderMatchedShippingRates->count) {
	// @note here will always receive one matched shipping rate (selected rate) but still this will be in a wirearray
	// @note SHOULD ALREADY HAVE BEEN ADDED AS SELECTED TO THE ORDER!
	// SO JUST GET THE FIRST (AND ONLY) RATE (WireData) IN THE WireArray
	$matchedShippingRate = $orderMatchedShippingRates->first();

	// -----------

	// if rate has delivery times
	$deliveryTimesMarkup = '';
	if (!empty($matchedShippingRate->shippingRateDeliveryTimeMinimumDays)) {
		$deliveryTimes = sprintf(__('Delivery between %1$d to %2$d days'), $matchedShippingRate->shippingRateDeliveryTimeMinimumDays, $matchedShippingRate->shippingRateDeliveryTimeMaximumDays);
		$deliveryTimesMarkup = "<span>{$deliveryTimes}</span><br>";
	}
	// ------
	// @note: in this case, we show the shipping fee itself as it might include taxes and it is final
	// $shippingFee = $pwcommerce->getValueFormattedAsCurrencyForShop($matchedShippingRate->shippingRate);
	$shippingFee = $pwcommerce->getValueFormattedAsCurrencyForShop($order->shippingFee);
	// --------
	$out .=
		"<h2>" . __("Shipping") . "</h2>" .
		// rate name
		"<span>" . __("Name") . ": {$matchedShippingRate->shippingRateName}</span>" .
		// delivery times
		$deliveryTimesMarkup .
		// rate / charge / fee
		"<span>" . __("Fee") . ":  {$shippingFee}</span>";
}

// HANDLING FEE
// add handling fee if present
if (!empty($orderHandlingFeeValues->handlingFee)) {
	$handlingFee = $pwcommerce->getValueFormattedAsCurrencyForShop($orderHandlingFeeValues->handlingFee);
	// -------
	$out .=
		"<div id='order_handling_fee'>" .
		"<h2>" . __("Handling Fee") . "</h2>" .
		"<span>" . __("Fee") . ":  {$handlingFee}</span>" .
		"</div>"; // div#order_handling_fee
}

// #############

// add total (handling fee + shipping + taxes included) if present calculated
$out .=
	"<div id='order_grand_total'>" .
	"<h2>" . __("Grand Total") . "</h2>";
// ----------
if (!empty($isOrderGrandTotalComplete)) {
	$grandTotal = $pwcommerce->getValueFormattedAsCurrencyForShop($orderGrandTotal);
	// -------
	$out .= "<span id='order_grand_total_amount' >" . __("Amount") . ":  {$grandTotal}</span>";
} else {
	$out .= "<div><span id='order_grand_total_amount'>" . __("Please select a shipping rate so that a grand total can be computed.") . "</span></div>";
}
// ------
$out .= "</div>"; // div#order_grand_total
// ---------------
$out .= "</div>"; // div#order_shipping_information_and_total_wrapper
echo $out;