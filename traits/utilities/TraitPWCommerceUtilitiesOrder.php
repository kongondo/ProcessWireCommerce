<?php

namespace ProcessWire;



trait TraitPWCommerceUtilitiesOrder
{



	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ORDERS ~~~~~~~~~~~~~~~~~~



	/**
	 * Get order ID with prefix and suffix if applicable.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	public function getOrderNumberWithPrefixAndSuffix(Page $page) {
		// TODO: CHECK IF ADD PREFIX/SUFFIX TO ORDER TITLE/NAME!
		$generalSettings = $this->getShopGeneralSettings();
		$orderPrefix = "";
		$orderSuffix = "";
		// add order prefix if required by general settings
		if (!empty($generalSettings->order_prefix)) {
			$orderPrefix = $generalSettings->order_prefix;
		}
		// add order suffix if required by general settings
		if (!empty($generalSettings->order_suffix)) {
			$orderSuffix = $generalSettings->order_suffix;
		}
		$orderNumberWithPrefixAndSuffix = "{$orderPrefix}{$page->id}{$orderSuffix}";
		return $orderNumberWithPrefixAndSuffix;
	}

	// TODO: MOVE TO PWCOMMERCE CLASS!!!
	/**
	 * Get This Years Orders.
	 *
	 * @param bool $isRaw
	 * @param array $options
	 * @return mixed
	 */
	public function getThisYearsOrders(bool $isRaw = true, array $options = []) {

		// TODO IF WE DON'T SPECIFY FIELDS HERE, WE WILL GET AN ERROR SINCE PROCESSWIRE WILL ATTEMPT TO SEARCH INPUTFIELDPARLOPERRUNTIMEMARKUP!!! WE NEED A WAY TO TELL IT TO EXCLUDE THAT OR TO AVOID THE DATABASE???!!

		// TODO WILL NEED TO ADD STATUS COMPLETE TO SELECTOR!

		$endOfLastYearTimestamp = $this->getEndOfLastYearTimestamp();
		$startOfNextYearTimestamp = $this->getStartOfNextYearTimestamp();

		// TODO: WHY NOT ORDER TEMPLATE HERE OR WE NEED BOTH? FOR THE STATS, MOST LIKELY NEED BOTH?
		// $selector = "template=" . PwCommerce::ORDER_LINE_ITEMS_TEMPLATE_NAME . ",created>$endOfLastYearTimestamp,created<$startOfNextYearTimestamp";
		// TODO: ALTERNATIVELY, GET THE ORDERS IDS FIRST, THEN GET THEIR CHILDREN?
		// TODO WHAT ABOUT LIMITS?
		$selector = "include=all,check_access=0,template=" . PwCommerce::ORDER_TEMPLATE_NAME . ",created>$endOfLastYearTimestamp,created<$startOfNextYearTimestamp,status<" . Page::statusTrash;
		if ($isRaw) {
			// findRaw
			$thisYearsOrders = $this->wire('pages')->findRaw($selector, $options);
		} else {
			// usual find
			$thisYearsOrders = $this->wire('pages')->find($selector);
		}
		// ---------------
		return $thisYearsOrders;
	}


	/**
	 * Get Open Orders Count.
	 *
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return mixed
	 */
	public function getOpenOrdersCount($startDate = null, $endDate = null) {
		$fields = 'id';
		if (empty($startDate) && empty($endDate)) {
			// if both $startDate and $endDate not given
			// we get this year's abandoned checkouts
			$startDateTimestamp = $this->getEndOfLastYearTimestamp();
			$endDateTimestamp = $this->getStartOfNextYearTimestamp();
		} else {
			// TODO REVISIT + SANITY CHECKS FOR DATES!
			// get abandoned checkouts count for given period
			$startDateTimestamp = strtotime($startDate);
			$endDateTimestamp = strtotime($endDate);
		}
		//------------------
		$selector = "include=all,check_access=0,template=" . PwCommerce::ORDER_TEMPLATE_NAME . ",created>$startDateTimestamp,created<$endDateTimestamp,pwcommerce_order.status=" . PwCommerce::ORDER_STATUS_OPEN . "status<" . Page::statusTrash;
		// --------------
		$openOrders = $this->wire('pages')->findRaw($selector, $fields);
		$openOrdersCount = count($openOrders);
		// ----------
		return $openOrdersCount;
	}

	/**
	 * Get Cancelled Orders Count.
	 *
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return mixed
	 */
	public function getCancelledOrdersCount($startDate = null, $endDate = null) {
		$fields = 'id';
		if (empty($startDate) && empty($endDate)) {
			// if both $startDate and $endDate not given
			// we get this year's abandoned checkouts
			$startDateTimestamp = $this->getEndOfLastYearTimestamp();
			$endDateTimestamp = $this->getStartOfNextYearTimestamp();
		} else {
			// TODO REVISIT + SANITY CHECKS FOR DATES!
			// get abandoned checkouts count for given period
			$startDateTimestamp = strtotime($startDate);
			$endDateTimestamp = strtotime($endDate);
		}
		// --------------
		$selector = "include=all,check_access=0,template=" . PwCommerce::ORDER_TEMPLATE_NAME . ",created>$startDateTimestamp,created<$endDateTimestamp,pwcommerce_order.status=" . PwCommerce::ORDER_STATUS_CANCELLED . "status<" . Page::statusTrash;
		// --------------
		$cancelledOrders = $this->wire('pages')->findRaw($selector, $fields);
		$cancelledOrdersCount = count($cancelledOrders);
		// ----------
		return $cancelledOrdersCount;
	}

	/**
	 * Get Abandoned Checkouts Count.
	 *
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return mixed
	 */
	public function getAbandonedCheckoutsCount($startDate = null, $endDate = null) {
		$fields = 'id';
		if (empty($startDate) && empty($endDate)) {
			// if both $startDate and $endDate not given
			// we get this year's abandoned checkouts
			$startDateTimestamp = $this->getEndOfLastYearTimestamp();
			$endDateTimestamp = $this->getStartOfNextYearTimestamp();
		} else {
			// get abandoned checkouts count for given period
			$startDateTimestamp = strtotime($startDate);
			$endDateTimestamp = strtotime($endDate);
		}
		// --------------
		$selector = "include=all,check_access=0,template=" . PwCommerce::ORDER_TEMPLATE_NAME . ",created>$startDateTimestamp,created<$endDateTimestamp,pwcommerce_order.status=" . PwCommerce::ORDER_STATUS_ABANDONED . "status<" . Page::statusTrash;
		// --------------
		$abandondedCheckouts = $this->wire('pages')->findRaw($selector, $fields);
		$abandondedCheckoutsCount = count($abandondedCheckouts);
		// ----------
		return $abandondedCheckoutsCount;
	}

	/**
	 * Get Average Items Per Order Count.
	 *
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return mixed
	 */
	public function getAverageItemsPerOrderCount($startDate = null, $endDate = null) {
		$fields = ['pwcommerce_order_line_item' => 'order_line_item', 'parent_id' => 'order_id'];
		if (empty($startDate) && empty($endDate)) {
			// if both $startDate and $endDate not given
			// we get this year's average items per order
			// TODO WILL NEED TO ADD STATUS COMPLETE!
			$orderLineItems = $this->getThisYearsOrderLineItems(true, $fields);
		} else {
			// get average items per order for given period
		}

		// total orders found
		//
		// GET UNIQUE ORDER IDS FROM ORDER LINE ITEMS
		// @note: the latter technique is faster
		// $orderIDs = array_unique(array_column($orderLineItems, 'order_id'));
		$orderIDs = array_keys(array_flip(array_column($orderLineItems, 'order_id')));
		$totalNumberOfOrdersFound = count($orderIDs);

		// -----------
		$totalQuantityInLineItems = 0;
		foreach ($orderLineItems as $orderLineItem) {
			$lineItemQuantity = (int) $orderLineItem['order_line_item']['quantity'];
			$totalQuantityInLineItems += $lineItemQuantity;
		}
		// ----------------

		// ------------
		// TODO abs OK?? ceil? floor? int? round?
		$averageItemsPerOrder = 0; // TODO OK TO DEFAULT TO ZERO LIKE THIS?
		if ($totalNumberOfOrdersFound) {
			$averageItemsPerOrder = (int) round($totalQuantityInLineItems / $totalNumberOfOrdersFound);
		}

		// --------------
		return $averageItemsPerOrder;
	}

	/**
	 * Get Year Total Sales Count.
	 *
	 * @return mixed
	 */
	public function getYearTotalSalesCount() {
		/** @var array $monthsSalesCount */
		$monthsSalesCount = $this->getMonthsTotalSalesCounts();
		$yearSalesCount = array_sum($monthsSalesCount);
		// --------------
		return $yearSalesCount;
	}

	/**
	 * Get Months Total Sales Counts.
	 *
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return mixed
	 */
	public function getMonthsTotalSalesCounts($startDate = null, $endDate = null) {
		// get orders/sales (completed orders) for year grouped by month
		$monthlySales = $this->getThisYearsSalesGroupedByMonth($startDate, $endDate);
		$monthlySalesNumbers = [];
		// ---------------
		// get all months
		$months = $this->getMonthsNames();
		// loop through sales to compute total sales (i.e. NUMBE OF SALES; NOT REVENUE!) per month
		foreach ($months as $monthName) {
			$monthSalesCount = isset($monthlySales[$monthName]) ? count($monthlySales[$monthName]) : 0;
			// --------------
			$monthlySalesNumbers[$monthName] = $monthSalesCount;
		}
		//----------------
		return $monthlySalesNumbers;
	}

	/**
	 * Get Year Orders Revenue.
	 *
	 * @return mixed
	 */
	public function getYearOrdersRevenue() {
		/** @var array $monthsOrdersRevenues */
		$monthsOrdersRevenues = $this->getMonthsOrdersRevenues();
		$yearsOrdersRevenueMoney = $this->money(0);
		foreach ($monthsOrdersRevenues as $monthOrderRevenueMoney) {
			$yearsOrdersRevenueMoney = $yearsOrdersRevenueMoney->add($monthOrderRevenueMoney);
		}
		// --------------
		return $yearsOrdersRevenueMoney;
	}

	/**
	 * Get Months Orders Revenues.
	 *
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return mixed
	 */
	public function getMonthsOrdersRevenues($startDate = null, $endDate = null) {
		// TODO: EITHER HERE OR LATER WE NEED TO COMPUTE AVERAGE FOR YEAR! REMEMBER TO AV BY 12 MONTHS IN THAT CASE
		// get orders/sales (completed orders) for year grouped by month
		$monthlySales = $this->getThisYearsSalesGroupedByMonth($startDate, $endDate);
		$ordersRevenuesPerMonthMonies = [];
		// ---------------
		// get all months
		$months = $this->getMonthsNames();
		// loop through sales to compute average revenue for month
		// i.e. total revenue for month / total sales for month

		// DEFAULT
		$monthRevenueMoney = $this->money(0);

		foreach ($months as $monthName) {
			$isInitialMonthRevenue = true;
			// get revenue for each order in this month as MONEY
			if (!empty($monthlySales[$monthName])) {


				foreach ($monthlySales[$monthName] as $monthSales) {
					// order_total_price
					if (!empty($monthSales['order']['order_total_price'])) {

						$orderRevenue = $monthSales['order']['order_total_price'];

						########################
						if (!empty($isInitialMonthRevenue)) {
							// FIRST/INITIAL MONTH AMOUNT TO START WITH
							// create money object
							$monthRevenueMoney = $this->money($orderRevenue);
						} else {
							// NOT INITIAL MONTH AMOUNT TO ADD
							// amend money object > add to money object
							$orderRevenueMoney = $this->money($orderRevenue);
							$monthRevenueMoney = $monthRevenueMoney->add($orderRevenueMoney);
						}
					}

					// ---------
					$isInitialMonthRevenue = false;
				}
			}
			// --------------
			$ordersRevenuesPerMonthMonies[$monthName] = $monthRevenueMoney;
		}

		return $ordersRevenuesPerMonthMonies;
	}

	/**
	 * Get Year Orders Revenue Grouped By Country.
	 *
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return mixed
	 */
	public function getYearOrdersRevenueGroupedByCountry($startDate = null, $endDate = null) {
		// for fields, we only need the 'order_total_price' from 'pwcommerce_order' and 'shipping_address_country' from 'pwcommerce_order_customer'
		$fields = ['pwcommerce_order.order_total_price', 'pwcommerce_order_customer.shipping_address_country'];
		if (empty($startDate) && empty($endDate)) {
			// if both $startDate and $endDate not given
			// we get this year's orders sales
			// TODO WILL NEED TO ADD STATUS COMPLETE!
			// @note: the 'false' makes this return a PageArray!
			//	/** @var PageArray $orders */
			//$orders = $this->getThisYearsOrders(false, $fields);
			// @note: now using array as can be lots of orders and will slow things down!
			/** @var array $orders */
			$orders = $this->getThisYearsOrders(true, $fields);
		} else {
			// TODO!?
			// get order sales for given period
		}

		// GROUP COUNTRIES
		$limit = 10; // we will show top 10 countries + 1 'others' group if countries > $limit
		$yearOrdersRevenueGroupedByCountry = [];


		foreach ($orders as $order) {
			$country = !empty($order['pwcommerce_order_customer']['shipping_address_country']) ? $order['pwcommerce_order_customer']['shipping_address_country'] : null;
			if (!empty($country)) {
				// get the revenue for the current order in loop
				$revenueRawAmount = (float) $order['pwcommerce_order']['order_total_price'];
				$revenueRawMoney = $this->money($revenueRawAmount);


				// --------------
				if (isset($yearOrdersRevenueGroupedByCountry[$country])) {
					// if we already set country, we add to it
					$currentCountryRevenueTotalAmount = $yearOrdersRevenueGroupedByCountry[$country];
				} else {
					// start country revenue totals
					$currentCountryRevenueTotalAmount = 0;
				}
				$currentCountryRevenueTotalMoney = $this->money($currentCountryRevenueTotalAmount);
				// SUM revenue then convert back to whole currency
				$totalRevenueMoney = $currentCountryRevenueTotalMoney->add($revenueRawMoney);

				// TODO DELETE WHEN DONE if not in use
				$totalRevenueAmount = $this->getWholeMoneyAmount($totalRevenueMoney);
				$yearOrdersRevenueGroupedByCountry[$country] = $totalRevenueAmount;
			}
		}

		// we only show top $limit countries
		// if we $countries > $limit, we need to group leftovers into 'others' group
		// so we sort them by order total price then group the remainders in an 'others' country group
		if (count($yearOrdersRevenueGroupedByCountry) > $limit) {
			// need to sort plus add an 'others' group

			// ------
			$yearOrdersRevenueGroupedByCountry = $this->getSortOrdersRevenuesByValueAndGroupedByLimitedCountries($yearOrdersRevenueGroupedByCountry, $limit);

		} else {
			// no need for grouping
			// TODO?

		}

		// ------
		return $yearOrdersRevenueGroupedByCountry;
	}

	/**
	 * Get Sort Orders Revenues By Value And Grouped By Limited Countries.
	 *
	 * @param mixed $ordersRevenueGroupedByCountry
	 * @param int $limit
	 * @return mixed
	 */
	private function getSortOrdersRevenuesByValueAndGroupedByLimitedCountries($ordersRevenueGroupedByCountry, $limit = 10) {
		// @note: limit is total we need EXLCLUDING an 'OTHERS' country grouping if limit is surpassed by count of $ordersRevenueGroupedByCountry
		// @note: for now we sort DESCENDING only
		arsort($ordersRevenueGroupedByCountry, SORT_NUMERIC);
		// ---------
		$ordersRevenueSortedByValueAndGroupedByLimitedCountries = [];
		$ordersRevenueForOtherCountriesMoney = null;
		// limit the country groups TOP $limit COUNTRIES
		// @note: could have added this to final array already but doing it this way for readability
		$topCountriesByRevenueTotal = array_slice($ordersRevenueGroupedByCountry, 0, $limit);
		$otherCountries = array_slice($ordersRevenueGroupedByCountry, $limit);

		// if we have 'other countries' we sum their revenues into one 'other' group
		if (!empty($otherCountries)) {
			$otherCountriesRevenueMoney = $this->money(0);
			foreach ($otherCountries as $countryRevenue) {
				$otherCountriesRevenueMoney = $otherCountriesRevenueMoney->add($this->money($countryRevenue));
			}

			$ordersRevenueForOtherCountriesMoney = $otherCountriesRevenueMoney;
			$oordersRevenueForOtherCountriesAmount = $this->getWholeMoneyAmount($ordersRevenueForOtherCountriesMoney);
		}
		// -------
		// add top countries
		$ordersRevenueSortedByValueAndGroupedByLimitedCountries = $topCountriesByRevenueTotal;
		// add other countries
		if (!empty($oordersRevenueForOtherCountriesAmount)) {
			$others = $this->_('Others');
			$ordersRevenueSortedByValueAndGroupedByLimitedCountries[$others] = $oordersRevenueForOtherCountriesAmount;
		}
		//---------
		return $ordersRevenueSortedByValueAndGroupedByLimitedCountries;
	}

	/**
	 * Get Average Order Values.
	 *
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return mixed
	 */
	public function getAverageOrderValues($startDate = null, $endDate = null) {
		//
		// NOTE: AOV
		// What is AOV metric?
		// Average Order Value is the average amount of revenue your business earns from all the checkout orders.
		// How is AOV calculated?
		// Average Order Value is calculated as the total revenue divided by the total number of checkouts on the eCommerce store in defined time period.
		// Average order value(AOV) = total revenue/number of Checkouts {in a given time period}
		// So, it represents the average amount of money customers spend after a visit to your store. To calculate AOV, defne a time period and then divide total revenue by the number of orders during that time.
		//

		// ======================
		// revenue for each month in given period
		$ordersRevenuesPerMonth = $this->getMonthsOrdersRevenues();
		// for getting total checkouts/sales counts per month during this year (period)
		$monthlySales = $this->getMonthsTotalSalesCounts();
		// --------------
		// for storing each month's AOV
		$monthlyAverageOrderValues = [];
		// ~~~~~~~~~~~~~~~~~~
		foreach ($ordersRevenuesPerMonth as $monthName => $monthRevenueMoney) {
			$monthSalesCount = 0;
			$monthAverageOrderValueAmount = 0;
			if (!empty($monthRevenueMoney)) {
				$monthSalesCount = (int) $monthlySales[$monthName];
				if (!empty($monthSalesCount)) {
					// sales this month; AOV is zero
					// -------------
					$monthAverageOrderValueMoney = $monthRevenueMoney->divide($monthSalesCount);
					$monthAverageOrderValueAmount = $this->getWholeMoneyAmount($monthAverageOrderValueMoney);
				}
			}
			// --------------
			// store the AOV for the month
			$monthlyAverageOrderValues[$monthName] = $monthAverageOrderValueAmount;
		}



		// -------
		// compute and store the year AOV
		$yearRevenueMoney = $this->getYearOrdersRevenue();

		$yearTotalSalesCount = $this->getYearTotalSalesCount();
		$yearAverageOrderValue = 0;

		if ($yearTotalSalesCount) {
			$yearAverageOrderValueMoney = $yearRevenueMoney->divide($yearTotalSalesCount);
			$yearAverageOrderValue = $this->getWholeMoneyAmount($yearAverageOrderValueMoney);
		}
		// -----------


		// --------------
		// store the monthly and year AOVs
		$averageOrderValues = [
			'year_average_order_value' => $yearAverageOrderValue,
			'monthly_average_order_values' => $monthlyAverageOrderValues
		];


		// ----------------
		return $averageOrderValues;
	}

	/**
	 * Get This Years Sales Grouped By Month.
	 *
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return mixed
	 */
	public function getThisYearsSalesGroupedByMonth($startDate = null, $endDate = null) {
		$fields = ['pwcommerce_order' => 'order', 'created'];
		if (empty($startDate) && empty($endDate)) {
			// if both $startDate and $endDate not given
			// we get this year's orders sales
			// TODO WILL NEED TO ADD STATUS COMPLETE!
			// @note: the 'false' makes this return a PageArray!
			//	/** @var PageArray $orders */
			//$orders = $this->getThisYearsOrders(false, $fields);
			// @note: now using array as can be lots of orders and will slow things down!
			/** @var array $orders */
			$orders = $this->getThisYearsOrders(true, $fields);
		} else {
			// get order sales for given period
		}

		// ==========
		$monthlySales = [];
		// get this years months timestamps for start_after and end_before
		// 'start_after' is the last date of the previous month and 'end_before' is the first date of the next month
		// any order created date greater than 'start_after' or less than 'end_before' is THAT month's order
		$monthsTimestamps = $this->getThisYearsStartAndEndMonthsTimestamps();
		// loop through and group order sales by month
		foreach ($orders as $id => $order) {
			$createdDateTimestamp = strtotime($order['created']);

			// add order to its month
			foreach ($monthsTimestamps as $monthName => $monthsTimestamp) {
				if ($createdDateTimestamp > $monthsTimestamp['start_after'] && $createdDateTimestamp < $monthsTimestamp['end_before']) {
					// we found matching month
					// $monthlySales[$month][] = $order['order'];
					$monthlySales[$monthName][] = $order; // TODO: FOR DEBUG: USE ABOVE WHEN DONE!
					break;
				}
			}
		}

		// -------------

		// -----------------
		return $monthlySales;
	}

	/**
	 * Get the order totals for a specified country.
	 *
	 * @param mixed $country
	 * @return mixed
	 */
	public function getCountryAllOrdersPriceTotal($country) {
		// TODO - IN FUTURE, ADD LIMIT NUMBER OF ORDERS, LIMIT TO TIME PERIOD, ETC.
		$fields = ['pwcommerce_order.order_total_price', 'pwcommerce_order_customer.shipping_address_country'];
		$countryOrders = $this->wire('pages')->findRaw("template=pwcommerce-order,pwcommerce_order_customer.shipping_address_country={$country},check_access=0", $fields);
		$ordersOnly = array_column($countryOrders, 'pwcommerce_order');
		$ordersPriceOnly = array_column($ordersOnly, 'order_total_price');
		$countryAllOrdersPriceTotal = array_sum($ordersPriceOnly);
		return $countryAllOrdersPriceTotal;
	}


	# *******************


	/**
	 * Build Print Order Invoice.
	 *
	 * @param Page $orderPage
	 * @param mixed $templateFile
	 * @return mixed
	 */
	public function buildPrintOrderInvoice(Page $orderPage, $templateFile) {
		// TODO CONFIRM!
		// TODO: DELETE WHEN DONE; ALREADY AUTOLOADED IN PwCommerce::ready
		// make sure PWCommercePayment Class is autoloaded so that payment classes can extend it
		// require_once __DIR__ . '/../payment/PWCommercePayment/PWCommercePayment.php';
		################

		// ------

		$order = $orderPage->get(PwCommerce::ORDER_FIELD_NAME);

		/** @var WireArray $orderLineItems */
		$orderLineItems = $this->getOrderLineItems($orderPage);

		// --------
		/** @var WireData $orderCustomer */
		$orderCustomer = $this->getOrderCustomer($orderPage);

		// -----
		/** @var float $orderSubtotal */
		$orderSubtotal = $this->getOrderLineItemsTotalDiscountedWithTax($orderPage);

		// is order grand total computation complete? since we are in the backend printing the order, it is complete
		$isOrderGrandTotalComplete = true;
		// is order confirmed? since we are in backend printing the order, it is complete
		$isOrderConfirmed = true;
		// ---------
		$orderPaymentStatus = $order->paymentStatus;

		// --------
		/** @var WireData $shopBankDetails */
		$shopBankDetails = $this->getShopBankDetails();

		// ==========
		/** @var TemplateFile $t */
		$t = $this->getPWCommerceTemplate($templateFile);

		// ORDER
		$t->set("order", $order);
		// ORDER LINE ITEMS
		$t->set(
			"orderLineItems",
			$orderLineItems
		);
		// ORDER CUSTOMER
		$t->set("orderCustomer", $orderCustomer);
		// ORDER OTHER
		$t->set("orderSubtotal", $orderSubtotal);
		$t->set("isOrderGrandTotalComplete", $isOrderGrandTotalComplete);
		$t->set("isOrderConfirmed", $isOrderConfirmed);

		// SHOP BANK DETAILS
		// just in case shop allows invoice settlement via direct bank transfer
		$t->set("shopBankDetails", $shopBankDetails);

		// -----

		// TODO - DO WE NEED THIS FOR A PRINT JOB?
		// ORDER DOWNLOADS?
		if ((int) $orderPaymentStatus === PwCommerce::PAYMENT_STATUS_PAID) {
			// IF DOWNLOADS FEATURE WAS ENABLED ON INSTALL
			if ($this->isOptionalFeatureInstalled('downloads')) {
				/** @var array $downloads */
				$downloads = $this->getDownloadCodesByOrderID($order->id);
				if (!empty($downloads)) {
					$t->set("downloads", $downloads);
				}
			}
		}

		// ------
		return $t;
	}


	/**
	 * Process 'calculated' values for given order.
	 *
	 * @param array $options
	 * @return mixed
	 */
	public function getOrderCalculatedValues(array $options) {

		// -------
		/** @var WireData $this->order */
		$this->order = $options['order'];

		/** @var Page $this->page */
		$this->orderPage = $options['order_page'];

		if (isset($options['is_for_live_shipping_rate_calculation'])) {
			/** @var bool $this->isForLiveShippingRateCalculation */
			$this->isForLiveShippingRateCalculation = $options['is_for_live_shipping_rate_calculation'];
		}

		/** @var array $this->orderLineItems (2D) */
		// grab order line items for order once for reusability
		// TODO - THIS SHOULD BE GETTING ITEMS THAT ARE CURRENTLY IN ORDER EDIT VIEW! NOT NECESSARILY SAVED ONES! THAT MEANS CHECKING IDS IN INPUT! OR BEING PASSED LINE ITEMS HERE!
		$this->orderLineItems = $this->getLineItemsForOrder();

		// TODO -> CHECK IF TO APPLY SHIPPING TO ORDER -> NEED AT LEAST ONE SHIPPABLE ITEM PLUS IT SHOULD ONLY INCLUDE THOSE ITEMS!
		// TODO @note: need to call after $this->orderLineItems has been set!
		$this->isShippingApplicable = $this->isShippingApplicableOnOrder();

		// ----------------
		// SET ORDER SHIPPING COUNTRY
		/** @var Page $this->shippingCountry */
		$this->shippingCountry = $options['shipping_country'];
		// determine and set this order's shipping zone
		// TODO! make sure country is always set before order save then!

		// SET ORDER MATCHED SHIPPING ZONE
		/** @var Page $this->shippingZone */
		// TODO: IF THIS IS NULL, IT WILL THROW ERRORS LATER! FIX THAT CHECK?!
		$this->shippingZone = $this->getOrderCustomerCountryShippingZone($this->shippingCountry);

		// CHECK FOR ERRORS OR NOT APPLICABLE
		$notice = '';
		$isOrderError = false;
		$noticeTypeText = "";
		$noticeType = null;
		if (empty($this->isShippingApplicable)) {
			// shipping not applicable (technically not an error; just a notice)
			$notice = $this->_("Shipping is not applicable to this order!");
			$isOrderError = true;
			$noticeTypeText = "shipping_not_applicable";
			$noticeType = 'warning';
		} else if (empty($this->shippingZone)) {
			// country not in any shipping zone
			$notice = sprintf(__("The selected order customer shipping country %s has not yet been added to any shipping zone. This has to be done before you can continue editing this order!"), $this->shippingCountry->title);
			$isOrderError = true;
			$noticeTypeText = "country_not_in_shipping_zone";
			$noticeType = 'error';
		}

		$this->order->isOrderError = $isOrderError;

		// CATCH ERRORS
		if (!empty($this->order->isOrderError)) {
			$this->order->isOrderErrorMessage = $notice;
			$this->order->noticeTypeText = $noticeTypeText;
			$this->order->noticeType = $noticeType;
			// ------
			// if notice type is 'error' we return early
			// @note: this meanings we don't stop on warnings
			if ($noticeType === 'error') {
				return $this->order;
			}
		}

		// --------
		// GOOD TO GO

		############

		// SET ORDER MATCHED SHIPPING ZONE SHIPPING RATES
		/** @var WireArray $this->shippingZoneRates */
		$this->shippingZoneRates = $this->getZoneShippingRates();
		// TODO THIS SHOULD NOT RETURN IF BASKET CONTAINS ONLY NON-SHIPPABLE ITEMS! EVEN PHYSICAL PRODUCTS NOT REQUIRING SHIPPING!
		// if (empty($this->shippingZoneRates)) {
		// ERROR: IF SHIPPING IS APPLICABLE BUT NO SHIPPING ZONE RATES for zone
		if (!empty($this->isShippingApplicable) && empty($this->shippingZoneRates)) {
			$notice = sprintf(__("The matched shipping zone for this order %s has not yet added any shipping rates. This has to be done before you can continue editing this order!"), $this->shippingZone->title);
			$this->order->isOrderError = true;
			$this->order->isOrderErrorMessage = $notice;
			return $this->order;
		}

		// ------------
		// GOOD TO CONTINUE

		// ##################
		// ----------------
		$order = $this->order;

		## ********** SET CALCULABLE VALUES FOR ORDER  ********** ##

		// +++++++++++++
		// 2. DISCOUNTS
		// NOTE ORDER DISCOUNTS ARE PROPORTIONATELLY APPLIED ACROSS ALL LINE ITEMS!
		// nothing to do here
		// +++++++++++++
		// 3. SHIPPING
		// @NOTE
		// THESE SHIPPING VALUES HAVE TO TAKE INTO ACCOUNT FREE SHIPPING! WHILST WE MAINTAIN THEIR VALUES BEFORE DISCOUNT FOR REPORTING, WE NEED TO ENSURE THAT IN $order->totalPrice WE HAVE MADE THE VALUES NEGATIVE OR ZERO FOR THEM TO BE SUBTRACTED FROM TOTAL
		// --------
		// handling
		// TODO NEED TO APPLY TO SHIPPABLE GOODS ONLY! NO HANDLING FOR OTHERS!
		// @note: if handlingFeeType == 'percentage', WE BASE PERCENTAGE ON THE SHIPPABLE ITEMS TOTAL PRICE ONLY!
		// @note: if no shippable goods, handling fee type is 'inapplicable', amount and value here will be 0
		$order->handlingFeeType = $this->getOrderHandlingFeeType();
		$order->handlingFeeValue = $this->getOrderHandlingFeeValue();
		$handlingFeeMoney = $this->getOrderHandlingFeeMoney();
		$order->handlingFee = $this->getWholeMoneyAmount($handlingFeeMoney);

		// --------
		// shipping
		// TODO THIS WILL NEED TO COME FROM SELECTED MATCHED SHIPPING RATE! SELECTION WILL NEED TO OCCUR ON CLIENT FORM (BACKEND)
		// TODO IN THE METHOD, ONLY CALCULATE IF SHIPPING NEEDS (RE)CALCULATING!
		$shippingFeeMoney = $this->getOrderShippingFeeMoney();
		$order->shippingFee = $this->getWholeMoneyAmount($shippingFeeMoney);

		// @note: overloading! TODO OK????
		/** @var WireArray $order->matchedShippingRates */
		$order->matchedShippingRates = $this->getOrderComputedMatchedShippingRates();
		$order->maximumShippingFee = $this->getZoneMaximumShippingFee();
		// +++++++++++++
		// 4. TOTALS
		// TODO DELETE WHEN DONE
		// @note: this is ORDER SUB-TOTAL PRICE/COST (order and line items discounts applied) + SHIPPING + HANDLING + TAX
		// @UPDATE: THIS NOW CHANGES -> IT IS PRICE/COST OF LINE ITEMS WITHOUT TAX AND OTHER FEES (INCLUDING SHIPPING) BUT MINUS DISCOUNTS; ALSO NOTE, WHOLE ORDER DISCOUNTS NOW ALSO INCLUDED IN LINE ITEMS (PROPORTIONATELY DIVIDED between them)
		$orderTotalPriceMoney = $this->getOrderTotalPriceMoney();
		$order->totalPrice = $this->getWholeMoneyAmount($orderTotalPriceMoney);
		//-------------------------

		// 5. RUNTIMES
		// runtime, e.g. for TraitPWCommerceOrder
		$order->matchedShippingZoneID = $this->shippingZone->id;
		$order->matchedShippingZoneName = $this->shippingZone->title;
		// @note: excludes shipping and handling BUT includes taxes + discounts
		// runtime, e.g. for TraitPWCommerceOrder
		$orderSubtotalPriceMoney = $this->getOrderSubTotalMoney();
		$order->subtotalPrice = $this->getWholeMoneyAmount($orderSubtotalPriceMoney);

		// NOTE: DOES NOT INCLUDE TAX ON SHIPPING! THAT IS PART OF THE SHIPPING FEE ALREADY IF APPLICABLE

		$orderTaxMoney = $this->getOrderTaxMoney();
		$order->orderTaxAmountTotal = $this->getWholeMoneyAmount($orderTaxMoney);
		// ----------

		// return the order with calculated values now processed
		return $order;
	}


	/**
	 * Get Order Total Quantity.
	 *
	 * @param bool $isForShippingRateCalculation
	 * @return mixed
	 */
	public function getOrderTotalQuantity(bool $isForShippingRateCalculation = false) {

		$orderTotalQuantity = 0;
		// -------------------
		// SKIP IF FOR QUANTITY-BASED SHIPPING RATE CALCULATION
		// for quantity-based shipping, only include shippable goods
		$includedShippingTypesForPriceBasedShippingRates = ['physical'];
		// ------------------
		// loop through to calculate quantity
		$orderTotalQuantity = 0;
		foreach ($this->orderLineItems as $orderLineItem) {

			// -----------------
			// skip non-shippable if computation is for calculating shipping rates
			if (!empty($isForShippingRateCalculation) && !in_array($orderLineItem['shippingType'], $includedShippingTypesForPriceBasedShippingRates)) {

				continue;
			}
			// ----------
			$orderTotalQuantity += (int) $orderLineItem['quantity'];
		}

		// ----------
		return $orderTotalQuantity;
	}

	## ==============

	/**
	 * Get current orders SUBTOTAL.
	 *
	 * @param bool $isForShippingRateCalculation
	 * @return mixed
	 */
	public function getOrderSubTotalMoney(bool $isForShippingRateCalculation = false) {
		// TODO NOTE IF FOR $isForShippingRateCalculation = false IT WILL IGNORE NON-SHIPPABLES
		$orderSubTotalMoney = $this->getOrderDiscountedSubTotal($isForShippingRateCalculation);
		// ----------
		return $orderSubTotalMoney;

	}



	/**
	 * Convert a given percentage value to a decimal.
	 *
	 * @param float $percentage
	 * @return float Given percentage converted to a decimal.
	 */
	// /**
  * Get Percentage As Decimal.
  *
  * @param mixed $percentage
  * @param int $scale
  * @return mixed
  */
 public function getPercentageAsDecimal($percentage, $scale = 6) {
	/**
	 * Get Percentage As Decimal.
	 *
	 * @param mixed $percentage
	 * @return mixed
	 */
	public function getPercentageAsDecimal($percentage) {
		$percentageAsDecimal = $percentage / PwCommerce::HUNDRED;
		return $percentageAsDecimal;
	}

	# **************



	/**
	 * Get Order Total Price Money.
	 *
	 * @return mixed
	 */
	public function getOrderTotalPriceMoney() {
		// TODO CHANGE TO MONEY!

		// NOTE
		// orderSubTotalWithDiscounts = SUM(LINE ITEMS 'total_price_discounted')
		// orderSubTotalWithDiscountsWithTax = SUM(LINE ITEMS 'total_price_discounted_with_tax')
		// orderDiscountsTotal = SUM(LINE ITEMS 'discount_amount')
		// orderTax = orderTaxAmountTotal = SUM(LINE ITEMS 'tax_amount_total')
		// orderTotal = orderSubTotalWithDiscounts + orderTax + shipping + handling

		// INIT EMPTY MONEY OBJECT
		$orderTotalPriceMoney = $this->money(0);


		// WITHOUT TAX!
		$orderSubTotalMoney = $this->getOrderSubTotalMoney();
		// WITH TAX
		// TODO DELETE IF NOT IN USE
		// $orderSubTotalWithTax = $this->getOrderSubTotalWithTax();
		// TAX ONLY
		$orderTaxMoney = $this->getOrderTaxMoney();


		// ~~~~~
		// TODO CHECK IF AT LEAST ONE 'FREE SHIPPING' DISCOUNT IS IN THE SESSION
		// IF YES, THEN CHANGE THESE VALUES TO ZERO!
		// SHIPPING
		// if ($this->isFreeShippingDiscountAppliedToOrder()) {
		if ($this->isFreeShippingDiscountAppliedToOrder()) {
			// free shipping discount applied to order
			$shippingFeeMoney = $this->money(0);
			$handlingFeeMoney = $this->money(0);
		} else {
			// shipping charges applied as usual
			$shippingFeeMoney = $this->getOrderShippingFeeMoney();
			$handlingFeeMoney = $this->getOrderHandlingFeeMoney();
		}


		// SUM ORDER PARTS: subtotal (including tax and discounts) + shipping fee (might include tax) + handling fee
		$orderTotalPriceMoney = $orderTotalPriceMoney->add($orderSubTotalMoney, $orderTaxMoney, $shippingFeeMoney, $handlingFeeMoney);

		return $orderTotalPriceMoney;
	}

	/**
	 * Get Order Tax Money.
	 *
	 * @return mixed
	 */
	public function getOrderTaxMoney() {
		// +++++++++
		// loop through to get values
		// INIT MONEY OBJECT
		$orderTaxAmountMoney = $this->money(0);
		foreach ($this->orderLineItems as $orderLineItem) {
			// NOTE: FOR LIVE SHIPIPING RATE, TAXES NOT YET DONE!
			if (empty($orderLineItem['tax_amount_total'])) {
				continue;
			}

			// --------------
			// amend MONEY OBJECT
			$currentLineItemTaxMoney = $this->money($orderLineItem['tax_amount_total']);
			$orderTaxAmountMoney = $orderTaxAmountMoney->add($currentLineItemTaxMoney);
		}
		// --------------------
		return $orderTaxAmountMoney;
	}

	/**
	 * Compute whole order weight.
	 *
	 * @return mixed
	 */
	public function ___getOrderWeight() {
		// TODO: get all order product ids; then get the products; then get their weights; will need to get parents if variants!; then loop through each and multiply by quantity!
		$orderWeight = 0;
		// get the ID of the 'property' being used to signify product weight
		$weightID = $this->getShopProductWeightPropertyID(); // TODO: THROW ERROR IF NO WEIGHT ID?

		// ================
		// compute total weight if we have a weight ID to pick weight properties from
		if (!empty($weightID)) {
			/** @var PageArray $productsInLineItemsForOrder */
			$productsInLineItemsForOrder = $this->getProductsPagesInLineItemsForOrder();
			// loop through and grab weights
			foreach ($productsInLineItemsForOrder as $product) {

				// -------------
				// check if this is a product requiring shipping
				$isShippablePhysicalProduct = $this->isShippablePhysicalProduct($product);

				// -------------
				// SKIP NON-SHIPPABLE PRODUCTS
				if (empty($isShippablePhysicalProduct))
					continue;

				// TODO DELETE WHEN DONE: MOVED TO OWN FUNCTION FOR EASE OF HOOKING INTO PER PRODUCT
				// determine if product is a variant or main product (without variants)
				// $isVariant =  $this->isVariant($product->template->name);
				// -------------
				// get the product properties
				// if product is a variant we get its properties from the parent product page
				// $properties = $isVariant ? $product->parent->get(PwCommerce::PRODUCT_PROPERTIES_FIELD_NAME) : $product->get(PwCommerce::PRODUCT_PROPERTIES_FIELD_NAME);

				// get the first weight property that matches the shop's weight property ID, just in case there are several for some reason
				// $firstWeightProperty = $properties->get("propertyID={$weightID}");

				// if we have a weight property
				// if (!empty($firstWeightProperty)) {
				// 	// get unit weight of product
				// 	$unitProductWeight = (float) $firstWeightProperty->value;
				// 	// get total quantity of product in line item
				// 	$quantity = $this->getSingleOrderLineItemQuantityInOrder($product->id);
				// 	// compute total weight at this line item
				// 	$totalProductWeight = $unitProductWeight * $quantity;
				// 	// ----------------
				//
				//
				//
				// 	// increment order weight
				// 	$orderWeight += $totalProductWeight;
				// }
				$unitProductWeight = $this->getProductWeight($product, $weightID);

				if (!empty($unitProductWeight)) {
					// get total quantity of product in line item
					$quantity = $this->getSingleOrderLineItemQuantityInOrder($product->id);
					// compute total weight at this line item
					$totalProductWeight = $unitProductWeight * $quantity;
					// ----------------

					// increment order weight
					$orderWeight += $totalProductWeight;
				}
			}
			// ------------
		}
		// ------------

		// ---------------
		return $orderWeight;
	}


}