<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Reports
 *
 * Class to render content for PWCommerce Admin Module executeDownloads().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderReports for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

class PWCommerceAdminRenderReports extends WireData
{

	private $adminURL;
	private $ajaxPostURL;


	/**
	 *   construct.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __construct($options = null) {
		if (is_array($options)) {
			$this->adminURL = $options['admin_url'];
			$this->ajaxPostURL = $options['ajax_post_url'];
		}
	}

	/**
	 * Render Results.
	 *
	 * @param mixed $selector
	 * @return string|mixed
	 */
	protected function renderResults($selector = null) {

		$message = "";
		$csvReportName = $this->wire('sanitizer')->fieldName($this->wire('input')->get->report_id);
		if (!empty($csvReportName)) {
			// WE HAVE A REPORT DOWNLOAD REQUEST
			// check if report still available
			$csvReportCache = $this->getCSVReportFromCache($csvReportName);
			if (!empty($csvReportCache)) {
				// force download of csv report
				// @note: this will also delete it from cache
				$this->downloadCSVReport($csvReportCache, $csvReportName);
			} else {
				$this->warning(('Downloadable CSV report has expired. Please generate a new one.'));
				$url = $this->adminURL . 'reports/';
				// redirect!
				$this->session->redirect($url);
			}
		} else {
			$message = $this->_('Report will be displayed here.');
		}

		// ---------
		$reportsArea = "<div id='pwcommerce_reports_wrapper' class='pwcommerce_show_highlight'><p>" .
			$message .
			"</p></div>";
		$out =
			"<div id='pwcommerce_bulk_view_reports' class='mt-5'>" .
			// ACTIONS
			$this->getBulkEditActionsPanel() .
			// TARGET AREA FOR REPORTS
			$reportsArea .
			// HIDDEN INPUT FOR HTMX
			// set the context for differentiation when in ajax page
			// TODO DELETE IF NOT IN USE; ELSE @SEE ORIGINAL IMPLEMENTATION
			"<input type='hidden' value='reports' name='pwcommerce_reports_context'>" .
			//---------------
			"</div>";
		// ---------
		return $out;
	}

	/**
	 * Get Bulk Edit Actions Panel.
	 *
	 * @return mixed
	 */
	private function getBulkEditActionsPanel() {
		// TODO: wip!
		$ajaxPostURL = $this->ajaxPostURL;
		$reportOptionsMarkup = $this->getReportOptionsMarkup();
		$actions = [
			'daily_sales' => $this->_('Daily sales'),
			'monthly_sales' => $this->_('Monthly sales'),
			'sales_per_product' => $this->_('Sales Per Product'),
			'download_order_line_items_csv' => $this->_('Download CSV Sales Rows'),
		];
		$options = [
			// add new url: empty since not needed
			'add_new_item_url' => "",
			// bulk edit select action
			'bulk_edit_actions' => $actions,
			// extra custom markup will be used
			'is_extra_custom_markup' => true,
			// left side content extra custom markup
			'extra_custom_markup' => $reportOptionsMarkup,
			// bulk actions page is for view only
			'is_view_only' => true,
			// htmx attributes
			'htmx_attributes' => [
				'hx-post' => $ajaxPostURL,
				'hx-target' => '#pwcommerce_reports_wrapper',
				'hx-swap' => 'innerHTML',
				'hx-vals' => json_encode(['pwcommerce_generate_sales_report' => true, 'pwcommerce_generate_sales_report_context' => 'reports']),
				'is_use_spinner' => true
			]
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	/**
	 * Get Report Options Markup.
	 *
	 * @return mixed
	 */
	private function getReportOptionsMarkup() {

		$curYear = date("Y");
		$curMonth = date("m");
		// $curDay = date("d");

		// --------
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		// ---------

		//------------------- start date incompletely paid orders? (getInputfieldDatetime)

		$options = [
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'columnWidth' => 50,
			'required' => true
		];
		// --------
		$startOptions = [
			'id' => "pwcommerce_report_start_date",
			'name' => 'pwcommerce_report_start_date',
			'label' => $this->_('Start Date'),
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_no_padding_top pwcommerce_report_start_date pwcommerce_report_options',
			// start from the first of the month
			'value' => strtotime("1.{$curMonth}.{$curYear}")
		];
		$startOptions = array_merge($options, $startOptions);
		$field = $this->pwcommerce->getInputfieldDatetime($startOptions);
		// add start date datepicker
		$wrapper->add($field);

		//------------------- end date incompletely paid orders? (getInputfieldDatetime)

		$endOptions = [
			'id' => "pwcommerce_report_end_date",
			'name' => 'pwcommerce_report_end_date',
			'label' => $this->_('End Date'),
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_no_padding_top pwcommerce_report_options',
			// start from today's date
			'value' => strtotime("today")
		];
		$endOptions = array_merge($options, $endOptions);
		$field = $this->pwcommerce->getInputfieldDatetime($endOptions);
		// add end date datepicker
		$wrapper->add($field);

		//------------------- include incompletely paid orders? (getInputfieldCheckbox)

		$options = [
			'id' => "pwcommerce_report_include_pending_and_partial_payments",
			'name' => "pwcommerce_report_include_pending_and_partial_payments",
			// 'label' => ' ', // @note: skipping label
			'label' => $this->_('Include Incomplete Orders'),
			'label2' => $this->_('Include pending and partially paid orders'),
			'description' => $this->_("Tick to include orders whose payments are incomplete."),
			// 'notes' => $this->_("Tick to include orders whose payments are incomplete."),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_report_options',
		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		// add checkbox
		$wrapper->add($field);

		//------------------- CSV delimiter for downloads (getInputfieldText)

		$options = [
			'id' => "pwcommerce_report_download_csv_delimiter",
			'name' => "pwcommerce_report_download_csv_delimiter",
			// 'label' => ' ', // @note: skipping label
			'label' => $this->_('CSV Delimiter'),
			'description' => $this->_("Delimiter for downloaded reports. This is usually a comma (',') or a semi-colon (';')."),
			'size' => 30,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_report_options',
			'value' => ",",
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		$wrapper->add($field);

		// --------
		$out = $wrapper->render();
		// --------
		return $out;
	}

	/**
	 * Get Results Table Headers.
	 *
	 * @param mixed $reportType
	 * @return mixed
	 */
	private function getResultsTableHeaders($reportType) {

		$numberOfOrders = $this->_('Number of Orders');
		$sales = $this->_('Sales');

		$tableHeaders = [
			'daily' => [
				// DATE
				// TODO: make these classes generic? e.g. for th percent width?
				[$this->_('Date'), 'pwcommerce_reports_table_date'],
				// NUMBER OF ORDERS
				[$numberOfOrders, 'pwcommerce_reports_table_number_of_orders'],
				// SALES
				[$sales, 'pwcommerce_reports_table_sales'],
			],
			'month' => [
				// MONTH
				// TODO: make these classes generic? e.g. for th percent width?
				[$this->_('Month'), 'pwcommerce_reports_table_month'],
				// NUMBER OF ORDERS
				[$numberOfOrders, 'pwcommerce_reports_table_number_of_orders'],
				// SALES
				[$sales, 'pwcommerce_reports_table_sales'],
			],
			'product' => [
				// PRODUCT
				// TODO: make these classes generic? e.g. for th percent width?
				[$this->_('Product'), 'pwcommerce_reports_table_product'],
				// NUMBER OF ITEMS SOLD
				[$this->_(' Items Sold'), 'pwcommerce_reports_table_product_items_sold'],
				// TOTAL SALES
				[$this->_('Total Sales'), 'pwcommerce_reports_table_product_total_sales'],
			],
		];

		// -----------
		$reportTableHeaders = $tableHeaders[$reportType];
		return $reportTableHeaders;
	}

	/**
	 * Get Results Table.
	 *
	 * @param mixed $reportItems
	 * @param mixed $caption
	 * @return mixed
	 */
	private function getResultsTable($reportItems, $caption = null) {

		$field = $this->modules->get('MarkupAdminDataTable');
		$field->id = 'pwcommerce_reports_table';
		$field->setEncodeEntities(false);
		// -------------
		// set caption if available
		if (!empty($caption)) {
			$field->setCaption($caption);
		}
		// -----------
		// set headers (th)
		$field->headerRow($this->getResultsTableHeaders($reportItems['report_type']));

		// ----------
		// set rows (tr)
		$reportRows = $reportItems['grouped_period_sales'];
		// set each row
		foreach ($reportRows as $reportRow) {
			$row = [
				// column 1: date/month/product
				$reportRow['item'],
				// column 2: count
				$reportRow['total_sales_count'],
				// column 3: sales
				$reportRow['total_sales_as_currency'],
			];
			$field->row($row);
		}

		// ----------
		// build footer
		$footer = $this->getResultsTableFooter($reportItems);
		$field->footerRow($footer);

		// -----------
		// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
		$out = $field->render();
		// -------
		return $out;
	}

	/**
	 * Get Results Table Footer.
	 *
	 * @param array $reportItems
	 * @return mixed
	 */
	private function getResultsTableFooter(array $reportItems) {
		$grandTotalSalesForPeriodAsCurrency = $reportItems['total_sales_for_period_as_currency'];
		// $footerTotal = sprintf(__("Total: %s"), $grandTotalSalesForPeriodAsCurrency);
		$footer = [
			'',
			$this->_('Total'),
			$grandTotalSalesForPeriodAsCurrency,
		];
		// --------
		return $footer;
	}

	/**
	 * Render Report.
	 *
	 * @param array $reportItems
	 * @return string|mixed
	 */
	protected function renderReport(array $reportItems) {
		$out = "";
		if (!empty($reportItems['grouped_period_sales'])) {
			// WE GOT RESULTS to display report
			// create caption
			$grandTotalSalesForPeriodAsCurrency = $reportItems['total_sales_for_period_as_currency'];
			$grandTotalOrdersForPeriod = $reportItems['total_orders_for_period'];
			$caption = sprintf(__('Total Sales: %1$s (for %2$s orders)'), $grandTotalSalesForPeriodAsCurrency, $grandTotalOrdersForPeriod);
			// get the table
			$out .= $this->getResultsTable($reportItems, $caption);
		} else if ($reportItems['report_type'] === 'download') {
			// we got a download: generate report summary + download report link
			$out .=
				"<div id='pwcommerce_download_csv_report_wrapper'>" .
				$this->renderDownloadCSVReport($reportItems);
			"</div>";
		} else {
			$out .= "<p>" . $this->_('There are no orders matching your reporting critetia.') . "</p>";
		}

		// -----------
		return $out;
	}

	/**
	 * Render Download C S V Report.
	 *
	 * @param mixed $reportItems
	 * @return string|mixed
	 */
	private function renderDownloadCSVReport($reportItems) {
		$out = "";

		// -----
		// report summary
		$grandTotalOrderLineItemsForPeriod = $reportItems['total_order_line_items_for_period'];
		$grandTotalOrdersForPeriod = $reportItems['total_orders_for_period'];
		$grandTotalSalesForPeriodAsCurrency = $reportItems['total_sales_for_period_as_currency'];
		$summary = sprintf(__('The report is ready to download. It contains %1$d rows for %2$d orders worth %3$s.'), $grandTotalOrderLineItemsForPeriod, $grandTotalOrdersForPeriod, $grandTotalSalesForPeriodAsCurrency);
		// download link for csv report
		$csvReportID = $reportItems['report_cache_name'];
		$downloadLink = "<a id='pwcommerce_download_csv_report' href='./?report_id={$csvReportID}' @click='handleHideReportLink'>" . $this->_('Download report.') . "</a>";

		// ----
		$out .=
			"<p>" . $summary . ' ' . $downloadLink . "</p>" .
			"<small>" . $this->_('Please note that the download link will expire in 5 minutes.') . "</small>";

		// -----
		return $out;
	}

	/**
	 * Download C S V Report.
	 *
	 * @param mixed $csvReport
	 * @param mixed $csvReportName
	 * @return mixed
	 */
	private function downloadCSVReport($csvReport, $csvReportName) {

		// get the report from cache
		// $csvReport = $this->getCSVReportFromCache($csvReportName);
		$headers = $csvReport['headers'];
		$rows = $csvReport['rows'];
		$delimiter = $csvReport['delimiter'];

		// --------------------
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data.csv');

		// create a file pointer connected to the output stream
		$fp = fopen('php://output', 'w');
		fputcsv($fp, $headers, $delimiter);
		foreach ($rows as $row) {
			fputcsv($fp, $row, $delimiter);
		}

		// remove temporary downloadable csv report
		$this->deleteCSVReportFromCache($csvReportName);
		// ------
		exit();
	}

	/**
	 * Get C S V Report From Cache.
	 *
	 * @param mixed $csvReportName
	 * @return mixed
	 */
	private function getCSVReportFromCache($csvReportName) {
		// get cached csv download value
		$csvReportCache = $this->wire('cache')->get($csvReportName);
		return $csvReportCache;
	}

	/**
	 * Delete C S V Report From Cache.
	 *
	 * @param mixed $csvReportName
	 * @return mixed
	 */
	private function deleteCSVReportFromCache($csvReportName) {
		$this->wire('cache')->delete($csvReportName);
	}
}
