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
 * Just copy this file into /site/templates/pwcommerce/frontend/cart-add-product.php to modify
 *
 *
 *
 *
 *
 **/

?>
<form method="post" class="pwcommerce-cart-add-product" action="<?= $config->urls->root ?>pwcommerce/add/">
    <?php
    if ($askQty)
        echo "<input type='number' name='pwcommerce_cart_add_product_quantity' value='1'/>";
    if ($redirectUrl)
        echo "<input type='hidden' name='pwcommerce_cart_redirect' value='$redirectUrl'/>";
    // TODO WIP - NEED TO CHANGE THIS AS NO SPECIAL VARIATION ID; THEY ARE JUST PRODUCTS THEMSELVES
    if ($variationId)
        echo "<input type='hidden' name='pwcommerce_cart_add_product_variation_id' value='$variationId'/>";
    echo "<input type='hidden' name='product_id' value='{$product->id}'/>";
    ?>
    <input type='submit' name='pwcommerce_submit' value='<?= __("Add to cart") ?>' />
</form>