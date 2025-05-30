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
 * Just copy this file into /site/templates/pwcommerce/frontend/cart-edit.php to modify
 *
 *
 *
 *
 *
 **/

// ==================
// TODO WIP
// ==================
// TODO rename here
if (!isset($currency)) {
	$currency = $pwcommerce->getShopCurrency();
}

$total = 0;
$cartItems = $pwcommerce->getCart();
if (empty($cartItems)) {
	return __("Your cart is empty.");
}

// TODO: ADD SHIPPING!? OR SEPARATE
?>

<form class='pwcommerce-cart pwcommerce-editcart' action='<?= $config->urls->root ?>pwcommerce/updateCart/' method='post'>
	<input type='hidden' name='pwcommerce_cart_redirect' value='<?= $page->httpUrl ?>' />
	<table class='pwcommerce-cart'>
		<tbody>
			<?php
			foreach ($cartItems as $p):
				?>
				<tr>
					<td class='pwcommerce-cart-product'>
						<?= $p->pwcommerce_title ?>
					</td>
					<td class='pwcommerce-cart-remove'><input type='checkbox' name='pwcommerce_cart_remove_product[<?= $p->id ?>]'
							id='removeproduct-<?= $p->id ?>' value='1' /><label for='removeproduct-<?= $p->id ?>'>
							<?= __("remove") ?>
						</label></td>
					<td class='pwcommerce-cart-quantity-and-price'><input size='2' name='pwcommerce_cart_products[<?= $p->id ?>]'
							value='<?= $p->quantity ?>' />
						<span class='pwcommerce-cart-x'>x</span>
						<?= $pwcommerce->renderCartPriceAndCurrency($p->pwcommerce_price) ?>
						<?php if ($p->quantity > 1): ?>
							<div class='pwcommerce-cart-subprice'>
								<?= $pwcommerce->renderCartPriceAndCurrency($p->pwcommerce_price_total) ?>
							</div>
						<?php endif; ?>
					</td>
				</tr>

				<?php

				$total = $total + ($p->pwcommerce_price_total);
			endforeach;
			?>
			<tr class='pwcommerce-cart-totalrow'>
				<td></td>
				<td></td>
				<td>
					<?= $pwcommerce->renderCartPriceAndCurrency($total) ?>
				</td>
			</tr>
		</tbody>
	</table>
	<input class='pwcommerce-submit' type='submit' value='<?= __("Update Cart") ?>'>
</form>