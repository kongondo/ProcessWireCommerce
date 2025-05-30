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
 * Just copy this file into /site/templates/pwcommerce/frontend/gift-card-redeem-html.php to modify
 *
 *
 *
 *
 *
 **/

// This is partial template for markup for redeeming a gift card in the frontend.


// ==================
// TODO WIP

// -------
bdb($sessionAppliedGiftCards, __METHOD__ . ': $sessionAppliedGiftCards - APPLIED GIFT CARDS IN SESSION - at line #' . __LINE__);
$appliedGiftCardsInfo = "";
if (!empty($sessionAppliedGiftCards)) {
	// $giftCardsInfo = $sessionAppliedGiftCards->giftCardsInfo;
	bdb($sessionAppliedGiftCards->giftCardsInfo, __METHOD__ . ': $sessionAppliedgiftCardsInfo->giftCards - at line #' . __LINE__);

	$giftCardsInfo = $sessionAppliedGiftCards->giftCardsInfo;

	if (!empty($giftCardsInfo)) {
		$appliedGiftCardsInfo .= "<p class='block'>Gift Card amount(s) to be applied to the order:</p>";
		$appliedGiftCardsInfo .= "<ul>";

		foreach ($giftCardsInfo as $giftCard) {
			$redeemable = $pwcommerce->getValueFormattedAsCurrencyForShop($giftCard->redeemable);
			$appliedGiftCardsInfo .= "<li>{$redeemable} - ****{$giftCard->id}</li>";
		}

		$appliedGiftCardsInfo .= "</ul>";
	}


}
?>



<?php
/** @var TemplateFile $t */
// TODO: ONLY RENDER IF CUSTOMER INFORMATION WAS ENTERED!
// $t = $pwcommerce->getPWCommerceTemplate("order-customer-information.php");
// ==============
// @note
// here we set the property order to the value of $order so that it can be used in the newly created virtual TemplateFile $t which uses the file "order-customer-information.php" AS ITS TEMPLATE FILE
// @note: TemplateFile extends WireData, hence here this is what happens: parent::set($property, $value);
// @note: $order itself was alreay set to this file, as a TemplateFile property in PWCommerceCheckout.php
// @see: PWCommerceCheckout::renderConfirmation
/** @var WireData $order */
// $t->set("order", $order);
// /** @var WireData $orderCustomer */
// $t->set("orderCustomer", $orderCustomer);
// echo $t->render();

// TODO CHANGE BELOW TO GENERIC; REMOVE TAILWIND!
?>


<div id='checkout_form_order_gift_card_or_discount_codes_wrapper' classXX="px-8 flex justify-between py-8 text-gray-600"
	class='grid grid-cols-10 gap-4 mb-10'>
	<input type="text" name="pwcommerce_order_gift_card_or_discount_code" placeholder="Gift card or discount code"
		class='col-span-6 mt-1 focus:ring-indigo-500 focus:border-indigo-500 XXXw-full shadow-sm sm:text-sm border-gray-300 bg-indigo-100 px-5 py-1'>
	<button id="pwcommerce_order_apply_gift_card_or_discount_code"
		class='font-semibold col-span-3 text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded'
		type="button" hx-post='./' hx-target='#checkout_form_order_gift_card_or_discount_codes_wrapper'>
		<?php echo __("Apply"); ?>
	</button>
	<!-- applied gift cards info -->
	<?php echo $appliedGiftCardsInfo; ?>
</div>