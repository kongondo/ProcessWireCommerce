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
 * Just copy this file into /site/templates/pwcommerce/frontend/order-downloadlinks.php to modify
 *
 *
 *
 *
 *
 **/
// ORDER DOWNLOADS
/** @var array $downloads */
if (!empty($downloads)) {
  echo "<h2>" . __("There are downloads in your order") . "</h2>";
  foreach ($downloads as $href => $title) {
    echo "<a target='_blank' href='$href'>$title</a><br>";
  }
  echo "<br>";
}