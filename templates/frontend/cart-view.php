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
 * Just copy this file into /site/templates/pwcommerce/frontend/cart-view.php to modify
 *
 *
 *
 *
 *
 **/

// ==================
// TODO WIP

// ==================
$total = 0;
$cartItems = $pwcommerce->getCart();
if (empty($cartItems)) {
	return __("Your cart is empty.");
}
?>

<table class='pwcommerce-cart pwcommerce-viewcart'>
	<tbody>

		<?php
		foreach ($cartItems as $p):
			?>

			<tr>
				<td>
					<?= $p->pwcommerce_title ?>
				</td>
				<td class='pwcommerce-cart-quantity-and-price'>
					<?= $p->quantity ?> <span class='pwcommerce-cart-x'>x</span>
					<?= $pwcommerce->renderCartPriceAndCurrency($p->pwcommerce_price) ?>
					<?php if ($p->quantity > 1): ?>
						<br /><span class='pwcommerce-cart-subprice'>
							<?= $pwcommerce->renderCartPriceAndCurrency($p->pwcommerce_price_total) ?>
						</span>
					<?php endif; ?>
				</td>
			</tr>

			<?php
			$total = $total + ($p->pwcommerce_price_total);
		endforeach;
		?>

		<tr class='pwcommerce-cart-totalrow'>
			<td></td>
			<td>
				<?= $pwcommerce->renderCartPriceAndCurrency($total) ?>
			</td>
		</tr>
	</tbody>
</table>