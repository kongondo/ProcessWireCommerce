<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Shop Home
 *
 * Class to render content for PWCommerce Process Module execute() (Shop Home Dashboard).
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderShopHome for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */




class PWCommerceAdminRenderShopHome extends WireData
{


	private $adminURL;
	// TODO: delete if not in use
	private $dateFormat;


	private $allOrderStatusesDefinitions;
	// for currency data for shop for formatting values, etc
	// includes: currency, code, country, etc
	// we use this to store it once for 'home dashboard' visits
	private $shopCurrencyData;
	//------------------


	public function __construct($options = null) {
		if (is_array($options)) {
			$this->adminURL = $options['admin_url'];
		}
		//-----------

		$this->shopCurrencyData = $this->pwcommerce->getShopCurrencyData();
		// ORDER STATUSES
		$this->allOrderStatusesDefinitions = $this->pwcommerce->getAllOrderStatusDefinitionsFromDatabase();

	}

	protected function renderResults($selector = null) {
		// DETERMINE HOW TO RENDER SHOP HOME DASH
		// +++++
		$customPartialTemplate = $this->pwcommerce->getBackendPartialTemplate(PwCommerce::PROCESS_RENDER_SHOP_HOME_PARTIAL_TEMPLATE_NAME);
		if (!empty($customPartialTemplate)) {
			// CUSTOM PWCOMMERCE PROCESS RENDER PROCESS RENDER SHOP HOME BACKEND MARKUP
			# +++++++++++
			// GET MARKUP
			$out = $customPartialTemplate->render();
		} else {
			// DEFAULT PWCOMMERCE PROCESS RENDER SHOP HOME MARKUP
			$out = $this->buildViewShopHome();
		}
		// ------------
		return $out;
	}

	private function buildViewShopHome() {

		########### GET MARKUP ########

		// TODO: ADD JS CONFIGS TO PROCESS MODULE TO GRAB DATA FOR CHARTS! LOOK AT THE 3 CHARTS WE NEED AND THE DATA THEY WILL NEED
		// ###################

		$out =
			// TODO DEPRECATED SINCE PWCOMMERCE 009; @SEE HOOK 'hookProcessPageSearchLive'
			// TODO DELETE IN NEXT RELEASE
			// FIND ANYTHING
			// TODO IN FUTURE USE EVERYWHERE? i.e in all contexts, not just home!
			// "<div>" . $this->findAnythingMarkup() . "<hr></div>" .

			//  TOP SUMMARY STATS GRID 4 * 1 GRID
			"<div class='grid grid-cols-12 gap-4 mt-10' id='pwcommerce_home_dashboard_summary_stats'>" .
			$this->getSummaryStatsCards() .
			//  end grid
			"</div>" .

			//  MIDDLE SUMMARY GRAPHS GRID 3 * 1 GRID - CHART JS
			"<div class='grid grid-cols-12 gap-4 mt-10' id='pwcommerce_home_dashboard_summary_graphs'>" .
			// ********************
			$this->getChartsCards() .
			// ********************
			//  end grid
			"</div>" .

			// BOTTOM LATEST ORDERS
			// @note: in future, will display 2 * 1 grid here with messages on left and latest orders on right
			"<div class='mt-10' id='pwcommerce_home_dashboard_latest_orders_table'>" .
			"<h3 class='mb-0 mt-7'>" . $this->_('Latest Orders') . "</h3>" .
			$this->getLatestOrdersTable() .
			"</div>";

		// ------------
		return $out;
	}

	private function getSummaryStatsCards() {
		// TODO revisit this breakpoint if we revisit chart's one! might need to drop earlier than md!
		$summaryStatsCardOptions = $this->getSummaryStatsCardsOptions();
		$out = "";
		foreach ($summaryStatsCardOptions as $options) {
			$out .= "<div class='col-span-full md:col-span-3 p-3 rounded-sm border'>" .
				"<div class='grid grid-cols-2 gap-4 text-lg md:text-xl'>" .
				"<div class='col-span-1'><i class='fa fa-{$options['icon']}' aria-hidden='true'></i></div>" .
				"<div class='col-span-1 justify-self-end'>{$options['value']}</div>" .
				"</div>" .
				"<hr>" .
				"<span class='uppercase'>{$options['label']}</span>" .
				"</div>";
		}
		// -------
		return $out;
	}

	private function getSummaryStatsCardsOptions() {
		$averageItemsPerOrderCount = $this->pwcommerce->getAverageItemsPerOrderCount();
		$abandondedCheckoutsCount = $this->pwcommerce->getAbandonedCheckoutsCount();
		$cancelledOrdersCount = $this->pwcommerce->getCancelledOrdersCount();
		$openOrdersCount = $this->pwcommerce->getOpenOrdersCount();
		// ----------------
		$summaryStatsCardOptions = [
			// TODO - GET THESE HARCODED VALUES USING THE API!
			//  open orders
			['icon' => 'folder-open-o', 'value' => $openOrdersCount, 'label' => $this->_('Open Orders')],
			//  cancelled orders
			['icon' => 'bell-slash-o', 'value' => $cancelledOrdersCount, 'label' => $this->_('Cancelled Orders')],
			//  abandoned checkouts
			['icon' => 'shopping-cart', 'value' => $abandondedCheckoutsCount, 'label' => $this->_('Abandoned Checkouts')],
			//  average items per order
			['icon' => 'anchor', 'value' => $averageItemsPerOrderCount, 'label' => $this->_('Average Items Per Order')],
		];

		return $summaryStatsCardOptions;
	}

	private function getChartsCards() {
		// TODO: ADD JS CONFIGS TO PROCESS MODULE TO GRAB DATA FOR CHARTS! LOOK AT THE 3 CHARTS WE NEED AND THE DATA THEY WILL NEED
		$chartsCardOptions = $this->getChartsCardsOptions();
		$out = "";
		foreach ($chartsCardOptions as $options) {
			$out .= "<div class='col-span-full md:col-span-4 p-3 rounded-sm border'>" .
				//  chart heading
				"<div class='mb-5 text-lg md:text-xl'>" .
				"<span class='block mb-2'>{$options['label']}</span>" .
				"<span class='mr-3'>" .
				"<i class='fa fa-{$options['icon']}' aria-hidden='true'></i>" .
				"</span>" .
				"<span>{$options['value']}</span>" .
				"</div>" .
				//  chart
				"<div id='pwcommerce_home_dashboard_{$options['chart_id']}_chart_wrapper'>" .
				"<canvas id='pwcommerce_home_dashboard_{$options['chart_id']}_chart'></canvas>" .
				"</div>" .
				"</div>";
		}

		// add script for chart js
		$out .= $this->getChartJSScript();

		// -------
		return $out;
	}

	private function getChartsCardsOptions() {

		$averageOrderValues = $this->pwcommerce->getAverageOrderValues();
		$yearAverageOrderValueRaw = $averageOrderValues['year_average_order_value'];


		$yearAverageOrderValue = $this->pwcommerce->getValueFormattedAsCurrencyForShop($yearAverageOrderValueRaw);
		// -------
		$yearSalesCount = $this->pwcommerce->getYearTotalSalesCount();

		// -----------
		$yearOrdersRevenueRawMoney = $this->pwcommerce->getYearOrdersRevenue();
		$yearOrdersRevenueRawAmount = $this->pwcommerce->getWholeMoneyAmount($yearOrdersRevenueRawMoney);
		$yearOrdersRevenue = $this->pwcommerce->getValueFormattedAsCurrencyForShop($yearOrdersRevenueRawAmount);


		// --------------
		$chartsCardOptions = [
			// TODO - GET THESE HARCODED VALUES USING THE API!
			// TODO THEY ARE AVERAGES! e.g average sales over 20 months, average orders over 12 months, average order value over 12 months
			//  1. total sales: line chart
			['icon' => 'paper-plane-o', 'value' => $yearSalesCount, 'label' => $this->_('Total Sales'), 'chart_id' => 'total_sales'],
			//  2. total Orders: area chart TODO: MAYBE TOTAL REVENUE INSTEAD OF ORDERS?
			// ['icon' => 'bell-o', 'value' => 8, 'label' => $this->_('Total Orders'), 'chart_id' => 'total_orders'],
			// TODO FORMAT REVENUE NUMBER PER CURRENCY!
			// TODO IF SHOW COUNTRIES HERE, MAYBE GET TOP TEN COUNTRIES AND GROUP THE REST IN TO OTHER?
			// TODO: SHOULD WE REMOVE COUNTRIES OR SWAP THEM, PUT THEM IN THE LINE CHART TO SHOW NUMBER OF SALES PER COUNTRY INSTEAD? THEN WE CAN SHOW REVENUE PER MONTH! IN REVENUE THE ICON NUMBER IS THE TOTAL REVENUE FOR THE YEAR
			['icon' => 'bell-o', 'value' => $yearOrdersRevenue, 'label' => $this->_('Total Revenue'), 'chart_id' => 'total_orders'],
			//  3. average order value: vertical bar chart
			['icon' => 'briefcase', 'value' => $yearAverageOrderValue, 'label' => $this->_('Average Order Value'), 'chart_id' => 'average_order_value'],
		];
		return $chartsCardOptions;
	}

	private function getChartJSScript() {
		$data = [
			'chart_js_configs' => $this->getChartJSConfigs(),
			'chart_js_data' => $this->getChartJSData(),
			'chart_js_labels' => $this->getChartJSLabels(),
			'shop_currency_config' => $this->shopCurrencyData
		];
		$script = "<script>ProcessWire.config.PWCommerceAdminRenderShopHome = " . json_encode($data) . ';</script>';
		return $script;
	}

	private function getChartJSConfigs() {
		$chartJSConfigs = [];
		return $chartJSConfigs;
	}

	private function getChartJSData() {
		$averageOrderValues = $this->pwcommerce->getAverageOrderValues();
		// --------
		$chartJSData = [
			// ------------------
			// TOTAL SALES CHART
			'monthly_total_sales_counts' => $this->pwcommerce->getMonthsTotalSalesCounts(),
			// ---------------
			// TOTAL REVENUE CHART
			'total_revenues_by_country' => $this->pwcommerce->getYearOrdersRevenueGroupedByCountry(),
			// ----------------
			// AVERAGE ORDER VALUE CHART
			'monthly_average_order_values' => $averageOrderValues['monthly_average_order_values']
		];
		return $chartJSData;
	}

	private function getChartJSLabels() {
		// --------
		$chartJSLabels = [
			// REUSABLE
			// reusable months names
			'months' => $this->pwcommerce->getMonthsNames(),
			// SPECIFIC
			'total_sales' => $this->_('Total Sales')
		];
		return $chartJSLabels;
	}

	private function getLatestOrdersTable() {

		$orders = $this->getLatestOrders();

		$out = "";

		$notFoundMessage = $this->_('No orders found.');

		$out = "";
		if (!$orders->count()) {
			$out = "<div  class='mt-5'><p>" . $notFoundMessage . "</p></div>";
		} else {

			// ==========
			$field = $this->modules->get('MarkupAdminDataTable');
			$field->setEncodeEntities(false);
			// set headers (th)
			$tableHeaders = $this->getLatestOrdersTableHeaders();
			$field->headerRow($tableHeaders);
			// set each row
			// @note: if order line items were not saved at all (i.e., $orderLineItem->pwcommerce_order_line_item), we will end up with empty table rows!
			foreach ($orders as $order) {
				$row = $this->getLatestOrdersTableRow($order);
				$field->row($row);
			}
			// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
			$out = $field->render();
		}
		//----------------
		return $out;
	}

	private function getLatestOrdersTableHeaders() {
		// TODO: DO WE USE TW CLASSES HERE?
		return [
			// ORDER NUMBER TODO: ok?
			[$this->_('Order ID'), 'pwcommerce_orders_table_order'],
			// DATE
			[$this->_('Date'), 'pwcommerce_orders_table_date'],
			// ORDER STATUS
			[$this->_('Order Status'), 'pwcommerce_orders_table_order_status'],
			// PAYMENT & FULFILMENT STATUSES
			[$this->_('Payment/Fulfilment Status'), 'pwcommerce_orders_table_payment_and_fulfilment_status'],
			// TOTAL
			[$this->_('Total'), 'pwcommerce_orders_table_total'],
		];
	}

	private function getLatestOrdersTableRow(Page $page) {
		$order = $page->pwcommerce_order;
		$orderTotalPriceFormattedAsShopCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($order->totalPrice);
		//------------
		return [
			// ORDER NUMBER/TITLE
			$this->getOrderViewURL($page),
			// DATE
			$this->getCreatedDate($page),
			// STATUS
			$this->pwcommerce->getOrderStatusName($order),
			// PAYMENT & FULFILMENT STATUSES
			$this->getOrderCombinedStatusesText($order, $excludeStatuses = ['order']),
			// TOTAL
			// $order->totalPrice,
			$orderTotalPriceFormattedAsShopCurrency,
		];
	}

	private function getOrderViewURL($page) {
		// ORDER NUMBER/TITLE
		$orderTitle = $this->pwcommerce->getOrderNumberWithPrefixAndSuffix($page);
		$out = "<a href='{$this->adminURL}orders/view/?id={$page->id}'>{$orderTitle}</a>";
		return $out;
	}

	private function getOrderCombinedStatusesText($order, $excludeStatuses = []) {
		$statuses = $this->pwcommerce->getOrderCombinedStatuses($order);

		if (!empty($excludeStatuses)) {
			foreach ($excludeStatuses as $excludeStatus) {
				if (!empty($statuses[$excludeStatus])) {
					unset($statuses[$excludeStatus]);
				}
			}
		}

		// ----------
		// prepare text for statuses
		// $statusesText = "<small>" . implode("<br>", $statuses) . "</small>";
		$statusesText = "<small>" . implode("/", $statuses) . "</small>";
		return $statusesText;
	}

	// get 10 latest orders, newest first
	private function getLatestOrders() {
		// FORCE TEMPLATE TO MATCH PWCOMMERCE ORDERS ONLY + INCLUDE ALL + EXLUDE TRASH
		$selector = "template=" . PwCommerce::ORDER_TEMPLATE_NAME . ",include=all,sort=-created,limit=10,status<" . Page::statusTrash;
		$orders = $this->wire('pages')->find($selector);
		return $orders;
	}

	/**
	 * Build the string for the last created date of this order page.
	 *
	 * @param Page $page The order page whose created date we are building.
	 * @return string The last created date string.
	 */
	private function getCreatedDate($page) {
		return $this->pwcommerce->getCreatedDate($page);
	}


}