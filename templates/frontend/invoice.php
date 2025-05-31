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
 * Just copy this file into /site/templates/pwcommerce/frontend/invoice.php to modify
 *
 *
 *
 *
 *
 **/

// TODO: WAS USED IN PadProcess::___executePrintInvoice()
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

		body {
			box-sizing: border-box;
			height: 11in;
			margin: 0 auto;
			overflow: hidden;
			padding: 0.5in;
			width: 8.5in;
		}

		body {
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
	<link rel="stylesheet" href="<?= $config->urls->siteModules ?>ProcessWireCommerce/templates/styles/style.css">
	<link rel="license" href="http://www.opensource.org/licenses/mit-license/">
</head>

<body>
	<?php

	// ORDER
	/** @var TemplateFile $t */
	$t = $pwcommerce->getPWCommerceTemplate("invoice-content-html.php");
	/** @var WireData $order */
	$t->set("order", $order);
	// ORDER LINE ITEMS
	/** @var WireArray $orderLineItems */
	$t->set("orderLineItems", $orderLineItems);
	// ORDER CUSTOMER
	/** @var WireData $orderCustomer */
	$t->set("orderCustomer", $orderCustomer);
	// ORDER DOWNLOADS
	/** @var array $downloads */
	if (!empty($downloads)) {
		$t->set("downloads", $downloads);
	}
	// ORDER OTHER
	/** @var float $orderSubtotal */
	$t->set("orderSubtotal", $orderSubtotal);
	/** @var bool $isOrderGrandTotalComplete */
	$t->set("isOrderGrandTotalComplete", $isOrderGrandTotalComplete);
	/** @var bool $isOrderConfirmed */
	$t->set("isOrderConfirmed", $isOrderConfirmed);

	// ----
	echo $t->render();
	?>
</body>

</html>