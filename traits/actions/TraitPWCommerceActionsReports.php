<?php

namespace ProcessWire;



trait TraitPWCommerceActionsReports
{



	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ REPORTS ~~~~~~~~~~~~~~~~~~

	// ## GENERATE SALES REPORTS ACTIONS

	public function generateSalesReportAction($input) {
		$result = [
			'notice' => $this->_('Error encountered. No action was taken.'),
			'notice_type' => 'error',
		];
		$sanitizer = $this->wire('sanitizer');
		// @note: since this method is for bulk edit action, we know the name of the input in advance!
		// i.e., 'pwcommerce_bulk_edit_action'
		$action = $sanitizer->fieldName($input->pwcommerce_bulk_edit_action);

		// if no action or action context, return
		if (!$action || !$this->actionContext) {
			return $result;
		}

		// SET COMMON VARIABLES
		$reportStart = $sanitizer->text($input->pwcommerce_report_start_date);
		$reportEnd = $sanitizer->text($input->pwcommerce_report_end_date);
		$reportStartTimestamp = strtotime($reportStart);
		$reportEndTimestamp = strtotime($reportEnd);
		// ------

		// if either start or end date not specified, error out
		if (empty($reportStartTimestamp) || empty($reportEndTimestamp)) {
			$result['notice'] = $this->_('Error: A start date and an end date are both required.');
			return $result;
		}

		// if both start date greater than end date, error out
		if ($reportStartTimestamp > $reportEndTimestamp) {
			$result['notice'] = $this->_('Error: Report start date cannot be later than report end date.');
			return $result;
		}

		// ---------------
		// GOOD TO GO

		// should pending and partially paid orders be included?
		$includePendingAndPartialPayments = !empty($input->pwcommerce_report_include_pending_and_partial_payments) ? true : false;

		// assign to class properties for later use
		$this->reportStart = $reportStart;
		$this->reportEnd = $reportEnd;
		$this->reportIncludePendingAndPartialPayments = $includePendingAndPartialPayments;

		//----------
		// GENERATE THE REQUESTED REPORT
		$reportTypeString = '';
		// -----------
		if ($action === 'daily_sales') {
			// DAILY SALES REPORT
			$this->reportType = 'daily';
			$actionResult = $this->actionDailySalesReport();
			$reportTypeString = $this->_('Daily Sales');
		} elseif ($action === 'monthly_sales') {
			// MONTHLY SALES REPORT
			$this->reportType = 'month';
			$actionResult = $this->actionMonthlySalesReport();
			$reportTypeString = $this->_('Monthly Sales');
		} elseif ($action === 'sales_per_product') {
			// SALES PER PRODUCT REPORT
			$this->reportType = 'product';
			$actionResult = $this->actionSalesPerProductReport();
			$reportTypeString = $this->_('Sales Per Product');
		} elseif ($action === 'download_order_line_items_csv') {
			$this->reportType = 'download';
			$delimiter = $sanitizer->text($input->pwcommerce_report_download_csv_delimiter);
			$this->reportDownloadDelimiter = !empty($delimiter) ? $delimiter : ';';
			// DOWNLOAD ORDER LINE ITEMS AS CSV TODO not sure if possible using AJAX??
			$actionResult = $this->actionSalesAsCSVDownloadReport();
			$reportTypeString = $this->_('Rows of Order Line Items Sales');
		}

		//-------------
		// set result/response as established by action method
		if (!empty($actionResult)) {
			// --------------------
			// TODO: NO NOTICES FOR NOW SINCE IN AJAX MODE! but we send just in case changes in future
			// prepare messages
			$notice = sprintf(__("Generated report for %s."), $reportTypeString);
			$result = [
				'notice' => $notice,
				'notice_type' => 'success',
				'report_type_string' => $reportTypeString,
				'report_items' => $actionResult
			];
		}
		//-------------
		return $result;
	}

	private function actionDailySalesReport() {
		$reportStart = $this->reportStart;
		$reportEnd = $this->reportEnd;

		// get the sales
		$sales = $this->getTimePeriodSales();

		$timePeriodSales = [];

		foreach ($sales as $sale) {
			// $day = date("Y-m-d", strtotime($sale['created']));
			$dt = new \DateTime($sale['created']);
			$day = $dt->format("Y-m-d");
			// +++++++++
			// SHIPPING CHARGES DEDUCTION
			// get totals of shipping and handling fees
			// we will deduct them from order total price to get true order (taxed) value
			$shippingFee = (float) $sale['order']['order_shipping_fee'];
			$handlingFee = (float) $sale['order']['order_handling_fee_amount'];
			$orderInclusiveShipping = (float) $sale['order']['order_total_price'];
			// order minus shipping
			$orderPrice = $orderInclusiveShipping - ($shippingFee + $handlingFee);
			// +++++++++
			// add 'this days' sales (minus shipping and handling)
			$timePeriodSales[$day][] = $orderPrice;
		}

		########################
		$dateFormat = $this->pwcommerce->getShopDateOnlyFormat();

		$start = new \DateTime($reportStart);
		$end = new \DateTime($reportEnd);
		$end->add(new \DateInterval('P1D'));

		// build a sales record for EACH day in the time period
		// this will make sure days without sales are also covered
		// these might not have been captured in the findRaw()
		$dailySales = [];
		while ($start != $end) {
			$thisDay = $start->format("Y-m-d");
			$start->add(new \DateInterval('P1D'));
			// ------
			$dailySales[$thisDay] = isset($timePeriodSales[$thisDay]) ? $timePeriodSales[$thisDay] : [];
		}

		// daily tallies
		$dailySalesTallies = [];
		foreach ($dailySales as $day => $sale) {
			$totalSales = array_sum($sale);
			$dailySalesTallies[$day] = [
				'item' => date($dateFormat, strtotime($day)),
				'total_sales' => $totalSales,
				'total_sales_as_currency' => $this->pwcommerce->getValueFormattedAsCurrencyForShop($totalSales),
				'total_sales_count' => count($sale)
			];
		}

		$grandTotalSalesForPeriod = array_sum(array_column($dailySalesTallies, 'total_sales'));
		$grandTotalSalesForPeriodAsCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($grandTotalSalesForPeriod);
		$grandTotalOrdersForPeriod = array_sum(array_column($dailySalesTallies, 'total_sales_count'));

		// ===========
		$reportItems = [
			'report_type' => 'daily',
			'total_sales_for_period' => $grandTotalSalesForPeriod,
			'total_sales_for_period_as_currency' => $grandTotalSalesForPeriodAsCurrency,
			'total_orders_for_period' => $grandTotalOrdersForPeriod,
			'grouped_period_sales' => $dailySalesTallies
		];
		// -----------
		return $reportItems;
	}

	############
	private function actionMonthlySalesReport() {
		$reportStart = $this->reportStart;
		$reportEnd = $this->reportEnd;

		// get the sales
		$sales = $this->getTimePeriodSales();

		$timePeriodSales = [];

		foreach ($sales as $sale) {
			// $day = date("Y-m-d", strtotime($sale['created']));
			$dt = new \DateTime($sale['created']);
			$month = $dt->format("Y-m");
			// +++++++++
			// SHIPPING CHARGES DEDUCTION
			// get totals of shipping and handling fees
			// we will deduct them from order total price to get true order (taxed) value
			$shippingFee = (float) $sale['order']['order_shipping_fee'];
			$handlingFee = (float) $sale['order']['order_handling_fee_amount'];
			$orderInclusiveShipping = (float) $sale['order']['order_total_price'];
			// order minus shipping
			$orderPrice = $orderInclusiveShipping - ($shippingFee + $handlingFee);
			// +++++++++
			// add 'this months' sales (minus shipping and handling)
			$timePeriodSales[$month][] = $orderPrice;
		}

		########################

		$start = new \DateTime($reportStart);
		$end = new \DateTime($reportEnd);

		// build a sales record for EACH month in the time period
		// this will make sure months without sales are also covered
		// these might not have been captured in the findRaw()
		$monthlySales = [];
		while ($start < $end) {
			$thisMonth = $start->format("Y-m");
			$start->add(new \DateInterval('P1M'));
			// ------
			$monthlySales[$thisMonth] = isset($timePeriodSales[$thisMonth]) ? $timePeriodSales[$thisMonth] : [];
		}

		// monthly tallies
		$monthlySalesTallies = [];
		foreach ($monthlySales as $month => $sale) {
			$totalSales = array_sum($sale);
			$monthlySalesTallies[$month] = [
				'item' => date(
					'F Y',
					strtotime($month)
				),
				'total_sales' => $totalSales,
				'total_sales_as_currency' => $this->pwcommerce->getValueFormattedAsCurrencyForShop($totalSales),
				'total_sales_count' => count($sale)
			];
		}

		$grandTotalSalesForPeriod = array_sum(array_column($monthlySalesTallies, 'total_sales'));
		$grandTotalSalesForPeriodAsCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($grandTotalSalesForPeriod);
		$grandTotalOrdersForPeriod = array_sum(array_column($monthlySalesTallies, 'total_sales_count'));

		// ===========
		$reportItems = [
			'report_type' => 'month',
			'total_sales_for_period' => $grandTotalSalesForPeriod,
			'total_sales_for_period_as_currency' => $grandTotalSalesForPeriodAsCurrency,
			'total_orders_for_period' => $grandTotalOrdersForPeriod,
			'grouped_period_sales' => $monthlySalesTallies
		];
		// -----------
		return $reportItems;
	}

	private function actionSalesPerProductReport() {

		// get the sales
		$sales = $this->getTimePeriodSales();

		// for order IDs for found product sales
		// @note: we do differently because order line items can be more than the orders
		// i.e., an order can have multiple line items
		// so, we need to calculate based on order numbers.
		// GET UNIQUE ORDER IDs
		$orderIDs = [];

		// product sales prep
		$productSales = [];
		foreach ($sales as $sale) {
			// get order IDs for later use
			// we need them to be unique as we want the real count
			$orderIDs[$sale['order_id']] = $sale['order_id'];
			// prepare order line items
			// --------
			$lineItem = $sale['line_item'];
			$productID = $lineItem['data'];
			$productSales[$productID][] = [
				'product_title' => $lineItem['product_title'],
				'quantity' => $lineItem['quantity'],
				'total_price_discounted_with_tax' => $lineItem['total_price_discounted_with_tax'],
				// TODO: delete if not in use
				// 'order_id' => $sale['parent_id']
			];
		}

		// product sales tallies
		$productSalesTallies = [];
		foreach ($productSales as $productID => $sale) {
			$productTitle = $sale[0]['product_title'];
			$totalSales = array_sum(array_column($sale, 'total_price_discounted_with_tax'));
			$totalSalesCount = array_sum(array_column($sale, 'quantity'));
			$productSalesTallies[$productID] = [
				'item' => $productTitle,
				'total_sales' => $totalSales,
				'total_sales_as_currency' => $this->pwcommerce->getValueFormattedAsCurrencyForShop($totalSales),
				'total_sales_count' => $totalSalesCount,
			];
		}

		$grandTotalSalesForPeriod = array_sum(array_column($productSalesTallies, 'total_sales'));
		$grandTotalSalesForPeriodAsCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($grandTotalSalesForPeriod);
		$grandTotalOrderLineItemsForPeriod = array_sum(array_column($productSalesTallies, 'total_sales_count'));
		$grandTotalOrdersForPeriod = count($orderIDs);

		// ===========
		$reportItems = [
			'report_type' => 'product',
			'total_sales_for_period' => $grandTotalSalesForPeriod,
			'total_sales_for_period_as_currency' => $grandTotalSalesForPeriodAsCurrency,
			'total_order_line_items_for_period' => $grandTotalOrderLineItemsForPeriod,
			'total_orders_for_period' => $grandTotalOrdersForPeriod,
			'grouped_period_sales' => $productSalesTallies
		];
		// -----------
		return $reportItems;
	}

	private function actionSalesAsCSVDownloadReport() {

		// @note: this does not include line items!
		// get the sales
		$sales = $this->getTimePeriodSales();

		// GET LINE ITEMS AND MERGE TO ORDER

		// first, prepare order IDs. Can bet from keys or or array_column($sales,'id')
		$orderIDs = array_keys($sales);
		$orderIDsString = implode("|", $orderIDs);

		// get the matching line items
		$fields = ['id', 'pwcommerce_order_line_item' => 'line_item', 'parent_id' => 'order_id'];
		$salesLineItems = $this->pwcommerce->findRaw("template=line-item,parent_id={$orderIDsString},sort=order_line_item.product_title", $fields);

		$datetimeFormat = $this->pwcommerce->getShopDateFormat();
		// getShopDateFormat

		$headers = [
			// ** ORDER **
			$this->_('Order Number'),
			$this->_('Order ID'),
			$this->_('Order Date'),
			// ----------------
			// ** LINE ITEM **
			// BASICS
			// product title
			$this->_('Product'),
			// sku
			$this->_('SKU'),
			// quantity
			$this->_('Quantity'),
			// is line item a variant?
			$this->_('Variant'),

			// DISCOUNTS
			$this->_('Discount Type'),
			$this->_('Discount Value'),
			$this->_('Discount Amount'),

			// TAXES
			$this->_('Tax Name'),
			$this->_('Tax Percentage'),
			$this->_('Tax Total'),
			$this->_('Tax Override'),

			// UNIT COSTS
			$this->_('Unit Price'),
			$this->_('Unit Price With Tax'),
			$this->_('Unit Discounted'),
			$this->_('Unit Price Discounted With Tax'),

			// TOTALS
			$this->_('Total Price'),
			$this->_('Total Price With Tax'),
			$this->_('Total Price Discounted'),
			$this->_('Total Price Discounted With Tax'),
			$this->_('Total Discounts'),

			// CUSTOMER
			// customer email
			$this->_('Email'),
			// customer country
			$this->_('Country'),

			// DATES
			// order paid date
			$this->_('Paid Date'),
			// line item delivered date
			$this->_('Delivered Date'),
		];

		$rows = [];
		$yes = $this->_('Yes');
		$no = $this->_('No');

		// prepare report items for CSV
		foreach ($salesLineItems as $lineItem) {
			$orderID = (int) $lineItem['order_id'];
			// -----
			$wholeOrder = $sales[$orderID];
			$order = $wholeOrder['order'];
			$orderLineItem = $lineItem['line_item'];
			$orderCustomer = $wholeOrder['customer'];

			// ---------
			$row = [
				// ** ORDER **
				// order number {the autoincrement value in order table}
				'order_number' => (int) $order['data'],
				// order ID {the processwire page ID for this order}
				'order_id' => $orderID,
				// order date
				'created' => date($datetimeFormat, strtotime($wholeOrder['created'])),
				// ----------------
				// ** LINE ITEM **
				// BASICS
				// product title
				'product_title' => $orderLineItem['product_title'],
				// sku
				'sku' => $orderLineItem['sku'],
				// quantity
				'quantity' => (int) $orderLineItem['quantity'],
				// is line item a variant?
				'is_variant' => empty((int) $orderLineItem['is_variant']) ? $no : $yes,

				// DISCOUNTS
				'discount_type' => $orderLineItem['discount_type'],
				'discount_value' => (float) $orderLineItem['discount_value'],
				'discount_amount' => (float) $orderLineItem['discount_amount'],

				// TAXES
				'tax_name' => $orderLineItem['tax_name'],
				'tax_percentage' => (float) $orderLineItem['tax_percentage'],
				'tax_amount_total' => (float) $orderLineItem['tax_amount_total'],
				'is_tax_override' => empty((int) $orderLineItem['is_tax_override']) ? $no : $yes,

				// UNIT COSTS
				'unit_price' => (float) $orderLineItem['unit_price'],
				'unit_price_with_tax' => (float) $orderLineItem['unit_price_with_tax'],
				'unit_price_discounted' => (float) $orderLineItem['unit_price_discounted'],
				'unit_price_discounted_with_tax' => (float) $orderLineItem['unit_price_discounted_with_tax'],

				// TOTALS
				'total_price' => $orderLineItem['total_price'],
				'total_price_with_tax' => $orderLineItem['total_price_with_tax'],
				'total_price_discounted' => $orderLineItem['total_price_discounted'],
				'total_price_discounted_with_tax' => $orderLineItem['total_price_discounted_with_tax'],
				'total_discounts' => $orderLineItem['total_discounts'],

				// CUSTOMER
				// customer email
				'email' => $orderCustomer['email'],
				// customer country
				'country' => $orderCustomer['shipping_address_country'],

				// DATES
				// order paid date
				'paid_date' => date($datetimeFormat, strtotime($order['order_paid_date'])),
				// line item delivered date
				'delivered_date' => date($datetimeFormat, strtotime($orderLineItem['delivered_date'])),
			];
			// ------
			// add to rows
			$rows[] = $row;
		}

		$delimiter = $this->reportDownloadDelimiter;

		$grandTotalSalesForPeriod = array_sum(array_column($rows, 'total_price_discounted_with_tax'));
		$grandTotalSalesForPeriodAsCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($grandTotalSalesForPeriod);
		$grandTotalOrderLineItemsForPeriod = count($rows);
		$grandTotalOrdersForPeriod = count($orderIDs);

		// save report rows to cache ready for download
		$cacheName = uniqid("pwcommerce_report");
		$cacheItems = ['headers' => $headers, 'rows' => $rows, 'delimiter' => $delimiter];
		$this->actionSaveCSVSalesReportToCache($cacheName, $cacheItems);

		// ===========
		$reportItems = [
			'report_type' => 'download',
			'report_cache_name' => $cacheName,
			'total_sales_for_period' => $grandTotalSalesForPeriod,
			'total_sales_for_period_as_currency' => $grandTotalSalesForPeriodAsCurrency,
			'total_order_line_items_for_period' => $grandTotalOrderLineItemsForPeriod,
			'total_orders_for_period' => $grandTotalOrdersForPeriod,
		];

		// -----------
		return $reportItems;
	}

	private function actionSaveCSVSalesReportToCache($cacheName, $cacheItems) {
		$expiration = 300; // expire after five minutes
		$this->wire('cache')->save($cacheName, $cacheItems, $expiration);
	}

	private function getTimePeriodSales() {

		$startdate = $this->reportStart;
		$enddate = $this->reportEnd;
		$includePendingAndPartialPayments = $this->reportIncludePendingAndPartialPayments;
		$reportType = $this->reportType;
		// -----------

		// $enddate = $enddate + 86400; // 23:59:59 instead of 00:00:00
		$startdate = strtotime($startdate);
		$enddate = strtotime($enddate);
		$enddate = $enddate + 86400; // 23:59:59 instead of 00:00:00

		// DETERMINE TEMPLATE NAME, SORTING & FIELDS
		if ($reportType === 'product') {
			// fetching order line items for product sales report
			$templateName = 'line-item';
			$sort = "order_line_item.product_title";
			$paymentStatusField = "parent.order.order_payment_status";
			$fields = [
				'id',
				'created',
				'pwcommerce_order_line_item' => 'line_item',
				'parent_id' => 'order_id'
			];
		} else if ($reportType == 'download') {
			// fetching orders for download report
			$templateName = 'order';
			$sort = "created";
			$paymentStatusField = "order.order_payment_status";
			$fields = ['id', 'created', 'pwcommerce_order' => 'order', 'pwcommerce_order_customer' => 'customer'];
		} else {
			// fetching orders for daily or monthly report
			$templateName = 'order';
			$sort = "created";
			$paymentStatusField = "order.order_payment_status";
			$fields = ['id', 'created', 'pwcommerce_order' => 'order'];
		}

		$selector = "template={$templateName}, created>={$startdate}, created<{$enddate}, sort={$sort}";
		$paidStatuses = [
			'fully_paid' => PwCommerce::PAYMENT_STATUS_PAID,
			'awaiting_payment' => PwCommerce::PAYMENT_STATUS_AWAITING_PAYMENT,
			'partially_paid' => PwCommerce::PAYMENT_STATUS_PARTIALLY_PAID,
		];

		if (empty($includePendingAndPartialPayments)) {
			// include only fully paid orders
			// $selector .= ",order.order_payment_status=" . $paidStatuses['fully_paid'];
			$selector .= ",{$paymentStatusField}=" . $paidStatuses['fully_paid'];
		} else {
			// include paid, awaiting payment and partially paid
			$paidStatusesString = implode("|", $paidStatuses);
			$selector .= $selector . ",{$paymentStatusField}={$paidStatusesString}";
		}

		// get the sales whose created date match the given time period
		$sales = $this->pwcommerce->findRaw($selector, $fields);
		// -----
		return $sales;
	}

}
