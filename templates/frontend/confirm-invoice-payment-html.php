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
 * Just copy this file into /site/templates/pwcommerce/frontend/confirm-invoice-payment-html.php to modify
 *
 *
 *
 *
 *
 **/

$value = __("Place Invoice Order");
$submitButton = "<input type='submit' value='{$value}'/>";
?>

<form action='<?php echo $invoiceUrl; ?>'>
    <?php
    // TODO: needed?
    echo $session->CSRF->renderInput();
    echo $submitButton;
    ?>
</form>