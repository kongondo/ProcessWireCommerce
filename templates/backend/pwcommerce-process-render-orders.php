<?php

namespace ProcessWire;

/**
 * BACKEND Template for PWCOMMERCE 'PWCommerceProcessRenderOrders.php'.
 * PATH: 'includes\render\PWCommerceProcessRenderOrders.php'.
 * RENDERS VIEW FOR A SINGLE ORDER PAGE IF USED AS A CUSTOM PARTIAL TEMPLATE.
 * For the method PWCommerceProcessRenderOrders::renderViewItem()
 *
 * Want to customize this template? Please do not edit directly!
 *
 * Just copy this file into /site/templates/pwcommerce/backend/pwcommerce-process-render-orders.php to modify
 *
 * @NOTE TODO EXPERIMENTAL!
 *
 * +++++++++++
 * @property Page $orderPage The ORDER PAGE.
 * @property WireData $order The ORDER itself (pwcommerce_order).
 * @property WireArray $orderLineItems The ORDER LINE ITEMS ($orderPage->children()).
 * @property WireData $orderCustomer The ORDER CUSTOMER (pwcommerce_order_customer).
 * @property WireArray $orderDiscounts The ORDER CUSTOMER (pwcommerce_order_discounts).
 * @property WireArray $orderNotes The ORDER NOTES (pwcommerce_notes).
 *
 *
 **/

?>

<div id='pwcommerce_process_render_order_view_wrapper'>
	<div>
		<h3>
			<?php echo __("Custom Shop Home Dashboard") ?>
		</h3>
		<p>
			<?php echo __("Hello PWCommerce!") ?>
		</p>
	</div>
</div>