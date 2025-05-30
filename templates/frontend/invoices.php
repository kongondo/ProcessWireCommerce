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
 * Just copy this file into /site/templates/pwcommerce/frontend/invoices.php to modify
 *
 *
 *
 *
 *
 **/

// TODO - IS THIS FILE STILL IN USE? => yes -> the 'Print Packing Slips'!
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>
    <?= __("Invoice") ?>
  </title>
  <style>
    /* page */
    html {
      font: 16px/1 'Open Sans', sans-serif;
      overflow: auto;
      padding: 0.5in;
    }

    html {
      background: #999;
      cursor: default;
      line-height: 1.5;
    }

    .page {
      box-sizing: border-box;
      height: 11in;
      margin: 0 auto 20px;
      overflow: hidden;
      padding: 0.5in;
      width: 8.5in;
      page-break-after: always;
    }

    .page {
      background: #FFF;
      border-radius: 1px;
      box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
    }

    .block {
      display: block;
    }

    #order_invoice_thanks_header {
      font-size: larger;
    }

    #order_invoice_thanks_header,
    #order_download_links_wrapper,
    #order_customer_information_wrapper,
    #order_meta_information_wrapper,
    #pwcommerce_order_line_items_tables {
      margin-top: 20px;
    }
  </style>
  <link rel="stylesheet" href="<?= $config->urls->siteModules ?>PWCommerce/templates/styles/style.css">
  <link rel="license" href="http://www.opensource.org/licenses/mit-license/">
</head>

<body>

  <?php

  $templateFile = "invoice-content-html.php";
  $out = "";
  foreach ($orders as $orderPage) {
    $out .= "<div class='page'>";
    // @note: buildPrintOrderInvoice() will set the variables required by 'invoice-content-html.php', e.g. $orderLineItems, $orderCustomer, etc
    /** @var TemplateFile $t */
    $t = $pwcommerce->buildPrintOrderInvoice($orderPage, $templateFile);
    $out .= $t->render();
    // -----------
    $out .= "</div>";
  }
  // ---------
  echo $out;
  ?>
</body>

</html>