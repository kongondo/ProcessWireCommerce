const ProcessPWCommerce = {
	listenToHTMXRequests: function () {
		// before request
		// This event is triggered before an AJAX request is issued. If the event is cancelled, no request will occur.
		// @see: https://htmx.org/events/#htmx:beforeRequest
		// @see: https://htmx.org/test/1.5.0/test/manual/
		// @TODO: THIS DID NOT WORK FOR US; XHR DID NOT ABORT!
		// htmx.on("htmx:beforeRequest", function (event) {
		// 	// CHECK IF FIND ANYTHING SEARCH BOX VALID FOR SEARCH
		// 	ProcessPWCommerce.isFindAnythingSearchValid(event)
		// 	//------------
		// })
		// after settle
		htmx.on("htmx:afterSettle", function (event) {
			// RUN POST SETTLE OPS
			ProcessPWCommerce.runAfterSettleOperations(event)
			//------------
		})
	},

	/**
	 * Run afterSettle operations (after htmx swap).
	 */
	runAfterSettleOperations: function (event) {
		// @NOTE: TRIGGER ELEMENT
		const eventDetailElement = event.detail.elt
		const eventDetailElementID = eventDetailElement.id

		let eventName, eventDetail

		if (eventDetailElementID === "pwcommerce_find_anything_results") {
			// 'FIND ANYTHING' SEARCH EVENT
			// tell alpine to show list of found results @see: handleFindAnythingShowResultsList()
			eventName = "pwcommercefindanythingshowresultslist"
			eventDetail = false
			// @note: method is in PWCommerceCommonScripts.js
			PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
		} else if (
			eventDetailElement.classList.contains("pwcommerce_show_highlight")
		) {
			// LISTER FILTER EVENT
			// here we simply highlight the InputfieldMarkup that was updated with our custom lister results
			ProcessPWCommerce.showHighlight(
				"#pwcommerce_bulk_edit_context_contents_wrapper"
			)
		} else if (
			eventDetailElement.classList.contains("pwcommerce_send_window_notification")
		) {
			// DISPATCH A CUSTOM WINDOW EVENT
			// e.g, tell alpine to carry out some post-htmx-swap operations
			// @NOTE: we get this from the requestConfig element
			// this is so we get the element that specifically sent the request, not necessarily the target one
			const requestConfigElement = event.detail.requestConfig.elt

			if (requestConfigElement.dataset.sendNotificationEventName) {
				// ----------
				// get the event name from the trigger elements data-send-notification-event-name
				eventName = requestConfigElement.dataset.sendNotificationEventName
				// get the event details if supplied data-send-notification-event-details
				eventDetail = requestConfigElement.dataset.sendNotificationEventDetails

				// @note: method is in PWCommerceCommonScripts.js
				PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
			}
		} else if (
			eventDetailElementID === "pwcommerce_manual_issue_gift_card_code"
		) {
			// TELL APLPINE TO OPEN MANUAL ISSUE GIFT CARD MODAL
			// @see: handleIssueGiftCard
			// @note: method is in PWCommerceCommonScripts.js
			eventName = "pwcommercemanualissuegiftcardcodenotification"
			eventDetail = true
			PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
		}
	},

	// ~~~~~~~~~~~~~

	/**
	 * Observe changes in InputfieldSelector.
	 * We have no direct access to events there.
	 * We use a mutation observer to watch for the changes instead.
	 * We observer the changes in the preview code.
	 * That signals us to htmx.trigger() to request data based on the current selector.
	 * The selector is in the hidden inputfield: '#pwcommerce_inputfield_selector_value'.
	 * Our htmx element is ready at all executePages() for bulk edits for this module.
	 */
	initListenToPWCommerceInputfieldSelectorChanges: function () {
		const pwcommerceInputfieldSelectorValueElement = document.getElementById(
			"pwcommerce_inputfield_selector_value"
		)
		if (pwcommerceInputfieldSelectorValueElement) {
			// Observe a specific DOM element:
			// then call callback 'ProcessPWCommerce.fetchPages' ON observed mutations

			// #pwcommerce_inputfield_selector_value
			const observer = new MutationObserver(ProcessPWCommerce.fetchPages)
			observer.observe(pwcommerceInputfieldSelectorValueElement, {
				attributes: true,
				// attributeOldValue: true,
			})
		}
	},

	/**
	 * Fetch pages per inputfield selector.
	 * Trigger using htmx
	 */
	fetchPages: function () {
		// to tell Process Module to fetch new pages based on selector
		// @TODO: IF 'li#wrap_pwcommerce_inputfield_selector_value p.selector-preview' has display none: it means no selector so we should not send request OR SEND REQUEST TO SHOW ALL but paginated!
		const eventName = "pwcommercefetchpagesforcustomlister"
		const elem = document.getElementById("pwcommerce_bulk_edit_wrapper")
		htmx.trigger(elem, eventName)
	},

	focusAddNewItemTitleInput: function () {
		const addNewItemTitleElement = document.getElementById(
			"pwcommerce_add_new_item_title"
		)
		let focusElement = null
		// check if in 'basic add new item' view
		// e.g. add category, product, etc
		if (addNewItemTitleElement) {
			// focus the element if found
			focusElement = addNewItemTitleElement
		} else {
			// check if in 'add countries view'
			// focus the filter box in this case
			const addNewCountriesFilterBoxElement = document.getElementById(
				"pwcommerce_add_new_countries_countries_filter_box"
			)
			if (addNewCountriesFilterBoxElement) {
				focusElement = addNewCountriesFilterBoxElement
			}
		}
		//------------------
		if (focusElement) {
			// if we have an element, focus it
			focusElement.focus()
		}
	},

	/**
	 * Add class to restyle the processwire panel tab that opens the PWCommerce right-side menu.
	 * We have no access to the tab in the markup, hence need to target it via js.
	 */
	initRestyleProcessWirePanelTabForPWCommerceMenu: function () {
		const panelTabElement = document.querySelector(
			"[data-panel-id='pwcommerce_menu_panel'] > a.pw-panel-button"
		)
		if (panelTabElement) {
			panelTabElement.classList.add("pwcommerce_menu_panel_tab")
		}
	},

	//
	/**
	 *  Highlight an inputfield on demand.
	 * Use function in Inputfields.js.
	 * @param {*} field Can be .class, id or jQuery Object.
	 */
	showHighlight: function (field) {
		Inputfields.highlight(field)
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// CHARTS JS
	// @TODO: REPLACE THE HARDCODED DATA WITH PROCESSWIRE CONFIG ONES!
	getProcessWireChartJSConfigs: function () {
		return ProcessWire.config.PWCommerceAdminRenderShopHome.chart_js_configs
	},
	getProcessWireChartJSData: function () {
		return ProcessWire.config.PWCommerceAdminRenderShopHome.chart_js_data
	},
	getProcessWireChartJSLabels: function () {
		return ProcessWire.config.PWCommerceAdminRenderShopHome.chart_js_labels
	},
	getChartMonths: function () {
		const chartJSLabels = ProcessPWCommerce.getProcessWireChartJSLabels()
		const monthsNames = chartJSLabels.months
		//-------
		return monthsNames
	},

	// orders sales counts
	getChartSalesCounts: function () {
		const chartJSData = ProcessPWCommerce.getProcessWireChartJSData()
		const monthlyTotalSalesCounts = chartJSData.monthly_total_sales_counts
		//-------
		return monthlyTotalSalesCounts
	},
	// LINE CHART
	totalSalesChart: function () {
		if (typeof Chart !== "undefined") {
			const totalSalesChartContext = document.getElementById(
				"pwcommerce_home_dashboard_total_sales_chart"
			)
			// get bar chart configuration
			const chartOptions = ProcessPWCommerce.totalSalesChartConfiguration()
			// add the data
			chartOptions.data = ProcessPWCommerce.totalSalesChartData()
			// BUILD THE CHART
			const totalSalesChart = new Chart(totalSalesChartContext, chartOptions)
		}
	},

	totalSalesChartConfiguration: function () {
		const lineChartConfiguration = {
			// TYPE
			type: "line",
			options: {
				// hide legend
				plugins: {
					legend: {
						display: false,
					},
				},
			},
			// DATA
			// for data init (to avoid linter 'property does not exist')
			data: null,
		}
		//---------------
		return lineChartConfiguration
	},

	totalSalesChartData: function () {
		const datasetsLabel =
			ProcessPWCommerce.getProcessWireChartJSLabels().total_sales
		const monthsLabels = ProcessPWCommerce.getChartMonths()
		const monthly_total_sales_counts = ProcessPWCommerce.getChartSalesCounts()
		// ==========
		const data = {
			// @note: not really needed since 'monthly_total_sales_counts' is an object of key (month): value pairs
			labels: monthsLabels, // months names
			datasets: [
				{
					label: datasetsLabel,
					// data: Object.values(monthly_total_sales_counts),
					data: monthly_total_sales_counts,
					fill: false,
					borderColor: "rgb(75, 192, 192)", // @TODO SEND FROM CONFIGS -> MAYBE GIVE IT A PALETTE NAEM
					tension: 0.1,
				},
			],
		}

		//---------------
		return data
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	// orders revenues by country totals
	getChartOrdersRevenuesByCountry: function () {
		const chartJSData = ProcessPWCommerce.getProcessWireChartJSData()
		const ordersRevenuesByCountry = chartJSData.total_revenues_by_country
		//-------
		return ordersRevenuesByCountry
	},

	// AREA CHART
	// @note: this is just a line chart with 'fill' enabled!

	totalOrdersRevenuesChart: function () {
		if (typeof Chart !== "undefined") {
			// total orders revenues by country + 'others'
			const totalOrdersRevenuesByCountryChartContext = document.getElementById(
				"pwcommerce_home_dashboard_total_orders_chart"
			)
			// get bar chart configuration
			const chartOptions =
				ProcessPWCommerce.totalOrdersRevenuesByCountryChartConfiguration()
			// add the data
			chartOptions.data =
				ProcessPWCommerce.totalOrdersRevenuesByCountryChartData()
			// BUILD THE CHART
			const totalOrdersRevenuesByCountryChart = new Chart(
				totalOrdersRevenuesByCountryChartContext,
				chartOptions
			)
		}
	},

	totalOrdersRevenuesByCountryChartConfiguration: function () {
		const shopCurrencyConfig = ProcessPWCommerce.getShopCurrencyConfig()
		const areaChartConfiguration = {
			// TYPE
			// @note: an area chart is just a line chart with 'fill' set to true
			type: "line",
			options: {
				// hide legend
				plugins: {
					legend: {
						display: false,
					},
				},
				// LOCALE {for currency formatting per shop's country!}
				locale: shopCurrencyConfig.country_code,
			},
			// DATA
			// for data init (to avoid linter 'property does not exist')
			data: null,
		}
		//---------------
		return areaChartConfiguration
	},

	totalOrdersRevenuesByCountryChartData: function () {
		// get the object with revenues grouped by country
		const orders_total_revenues_by_country =
			ProcessPWCommerce.getChartOrdersRevenuesByCountry()

		const data = {
			labels: Object.keys(orders_total_revenues_by_country),
			// -------
			datasets: [
				{
					// @todo: don't need this???
					// label: "Series 1", // Name the series
					// data: [500, 50, 2424, 14040, 14141, 4111, 4544, 47, 5555, 6811], // Specify the data values array
					data: Object.values(orders_total_revenues_by_country), // Specify the data values array
					fill: true,
					borderColor: "#2196f3", // Add custom color border (Line)
					backgroundColor: "#2196f3", // Add custom color background (Points and Fill)
					borderWidth: 1, // Specify bar border width
				},
				// @TODO: WE DON'T NEED THIS - DELETE IF NOT IN USE!
				// {
				// 	label: "Series 2", // Name the series
				// 	data: [1288, 88942, 44545, 7588, 99, 242, 1417, 5504, 75, 457], // Specify the data values array
				// 	fill: true,
				// 	borderColor: "#4CAF50", // Add custom color border (Line)
				// 	backgroundColor: "#4CAF50", // Add custom color background (Points and Fill)
				// 	borderWidth: 1, // Specify bar border width
				// },
			],
		}

		//---------------
		return data
	},

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	// VERTICAL BAR CHART

	getAverageOrderValues: function () {
		const chartJSData = ProcessPWCommerce.getProcessWireChartJSData()
		const monthlyAverageOrderValues = chartJSData.monthly_average_order_values
		//-------
		return monthlyAverageOrderValues
	},

	averageOrderValueChart: function () {
		if (typeof Chart !== "undefined") {
			const averageOrderValueChartContext = document.getElementById(
				"pwcommerce_home_dashboard_average_order_value_chart"
			)
			// get bar chart configuration
			const chartOptions = ProcessPWCommerce.averageOrderValueChartConfiguration()
			// add the data
			chartOptions.data = ProcessPWCommerce.averageOrderValueChartData()
			// BUILD THE CHART
			const averageOrderValueChart = new Chart(
				averageOrderValueChartContext,
				chartOptions
			)
		}
	},

	averageOrderValueChartConfiguration: function () {
		const shopCurrencyConfig = ProcessPWCommerce.getShopCurrencyConfig()
		const barChartConfiguration = {
			// TYPE
			type: "bar",
			// OPTIONS
			options: {
				// hide legend
				plugins: {
					legend: {
						display: false,
					},
				},
				// SCALES
				scales: {
					y: {
						beginAtZero: true,
						ticks: {
							// forces step size to be 20 units
							stepSize: 20,
						},
					},
				},
				// LOCALE
				locale: shopCurrencyConfig.country_code,
			},
			// DATA
			// for data init (to avoid linter 'property does not exist')
			data: null,
		}
		//---------------
		return barChartConfiguration
	},

	averageOrderValueChartData: function () {
		const monthsLabels = ProcessPWCommerce.getChartMonths()
		const average_order_values = ProcessPWCommerce.getAverageOrderValues()
		// @TODO: ABSTRACT THESE BACKGROUND COLOURS TO OWN METHOD + SOME COLOUR SCHEMES/PALETTES PLUS MAYBE MOVE TO SERVER-SIDE?
		const data = {
			labels: monthsLabels,
			datasets: [
				{
					label: "",
					data: average_order_values,
					backgroundColor: [
						"rgba(255, 99, 132, 0.2)",
						"rgba(54, 162, 235, 0.2)",
						"rgba(255, 206, 86, 0.2)",
						"rgba(75, 192, 192, 0.2)",
						"rgba(153, 102, 255, 0.2)",
						"rgba(255, 159, 64, 0.2)",
					],
					borderColor: [
						"rgba(255, 99, 132, 1)",
						"rgba(54, 162, 235, 1)",
						"rgba(255, 206, 86, 1)",
						"rgba(75, 192, 192, 1)",
						"rgba(153, 102, 255, 1)",
						"rgba(255, 159, 64, 1)",
					],
					borderWidth: 1,
				},
			],
		}
		//---------------
		return data
	},

	getHomeDashboardCharts: function () {
		// CALL THE CHARTS
		// total sales (line)
		ProcessPWCommerce.totalSalesChart()
		// total orders by location (area)
		ProcessPWCommerce.totalOrdersRevenuesChart()
		// average order (bar)
		ProcessPWCommerce.averageOrderValueChart()
	},

	getShopCurrencyConfig: function () {
		return ProcessWire.config.PWCommerceAdminRenderShopHome.shop_currency_config
	},

	// ~~~~~~~~~~~~~
	// SPECIAL FOR CONFIGURE INSTALL and COMPLETE REMOVAL

	/**
	 * Listen to radio changes for selection of tree management for children of custom shop root page.
	 *
	 * We use to send window notification to Alpine.js to determine toggle show various markup.
	 * We cannot x-model PW radio inputs. We need this to toggle show markup or other actions.
	 */
	initListenToCustomShopRootPageTreeManagementRadioElements: function () {
		const pageTreeManagementRadioElements = document.querySelectorAll(
			"input.pwcommerce_custom_shop_root_page_children_page_tree_management"
		)
		if (pageTreeManagementRadioElements) {
			for (const pageTreeManagementRadioElement of pageTreeManagementRadioElements) {
				// add event listener to each radio
				pageTreeManagementRadioElement.addEventListener(
					"change",
					ProcessPWCommerce.handleCustomShopRootPageTreeManagementRadioChange,
					false
				)
			}
		}
	},

	handleCustomShopRootPageTreeManagementRadioChange: function (event) {
		const selectedRadioElement = event.target
		const selectedRadioValue = selectedRadioElement.value

		// send the window event to Alpine.JS
		const eventName =
			"pwcommercecustomshoprootpagetreemanagementradiochangenotification"
		// const eventDetail = selectedRadioValue

		const eventDetail = selectedRadioValue

		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	/**
	 * Listen to 'run pwcommerce configure install' button click.
	 * @note: using here since issue with adding alpine handler to button (only works on head button).
	 */
	initListenToPWCommerceConfigureInstallButtonClick: function () {
		const button = document.getElementById("pwcommerce_configure_install_button")
		if (button) {
			button.addEventListener(
				"click",
				ProcessPWCommerce.handleRunConfigureInstall,
				false
			)
		}
	},

	handleRunConfigureInstall: function (event) {
		// tell alpine js to open modal to confirm RUN CONFIGURE INSTALL
		const eventName = "pwcommerceconfirmrunconfigureinstall"
		const eventDetail = true
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	/**
	 * Listen to 'run pwcommerce complete removal' button click.
	 * @note: using here since issue with adding alpine handler to button (only works on head button).
	 */
	initListenToPWCommerceCompleteRemovalButtonClick: function () {
		const button = document.getElementById("pwcommerce_complete_removal_button")
		if (button) {
			button.addEventListener(
				"click",
				ProcessPWCommerce.handleRunCompleteRemoval,
				false
			)
		}
	},

	handleRunCompleteRemoval: function (event) {
		// tell alpine js to open modal to confirm RUN COMPLETE REMOVAL
		const eventName = "pwcommerceconfirmruncompleteremoval"
		const eventDetail = true
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	// ~~~~~~~~~~~~~~~~~

	// SPECIAL FOR MANUALLY ISSUE GIFT CARD

	// ~~~~~~~~~~~~~~~~~

	/**
	 * Observe changes in InputfieldTextTagsSelect and InputfieldDatetimeDatepicker.
	 * We have no direct access to events there.
	 * We use a mutation observer to watch for the changes instead.
	 */
	initListenToManualIssueGiftCardMutationObserverElements: function () {
		const giftCardManualIssueElements = document.querySelectorAll(
			"div#pwcommerce_manual_issue_gift_card_wrapper input.InputfieldTextTagsSelect,#wrap_pwcommerce_issue_gift_card_end_date"
		)
		if (giftCardManualIssueElements) {
			// Observe a specific DOM element:
			// then call callback 'ProcessPWCommerce.setIsReadyForManualIssueGiftCard' ON observed mutations

			// +++++++
			const observer = new MutationObserver(
				ProcessPWCommerce.setIsReadyForManualIssueGiftCard
			)

			// @note: NEED TO LOOP AND SET OBSERVER ON EACH ELEMENT
			for (const giftCardManualIssueElement of giftCardManualIssueElements) {
				observer.observe(giftCardManualIssueElement, {
					attributes: true,
					// attributeOldValue: true,
				})
			}
		}
	},

	// ~~~~~~~~~~~~~~~~~

	// SPECIAL FOR 'GENERAL SETTINGS'

	/**
	 * Listen to radio changes in general settings radio changes.
	 *
	 * We use to send window notification to Alpine.js to if to show extra information to user.
	 * We cannot x-model PW radio inputs.
	 */
	initListenToGeneralSettingsRadioElements: function () {
		// @note: we are not interested in all radio changes!
		// we are only interested in General Settings > Products Tab (price fields) and User Interface Tab (navigation menus and search type).
		const generalSettingsRadioElements = document.querySelectorAll(
			'input[name="pwcommerce_general_settings_product_price_fields_type"],input[name="pwcommerce_general_settings_gui_navigation_type"],input[name="pwcommerce_general_settings_gui_quick_filters_and_advanced_search"]'
		)

		if (generalSettingsRadioElements) {
			for (const generalSettingRadioElement of generalSettingsRadioElements) {
				// add event listener to each general settings radio
				generalSettingRadioElement.addEventListener(
					"change",
					ProcessPWCommerce.handleGeneralSettingsRadioChangeNotification,
					false
				)
			}
		}
	},

	handleGeneralSettingsRadioChangeNotification: function (event) {
		const selectedRadioElement = event.target
		const selectedRadioValue = selectedRadioElement.value
		const selectedRadioParentElement = selectedRadioElement.closest(
			"li.InputfieldRadios"
		)

		// note GET FROM PARENT LI!
		// send the window event to Alpine.JS
		const eventName = "pwcommercegeneralsettingsradiochangenotification"
		const radioProperty =
			selectedRadioParentElement.dataset.generalSettingsRadioChangeType

		const isNeedEventNotification = !!radioProperty

		if (!isNeedEventNotification) {
			// NOTHING TO DO! LEAVE EARLY
			return
		}

		const eventDetail = {
			property: radioProperty,
			value: selectedRadioValue,
		}
		// tell AlpineJS about the change
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	// ~~~~~~~~~~~~~~~~~

	// SPECIAL FOR 'GIFT CARDS'

	/**
	 * Listen to manually issue gift card radio changes.
	 *
	 * We use to send window notification to Alpine.js to determine status of readiness to issue gift card.
	 * We cannot x-model PW radio inputs. We need this to check if specifying custom denomination and if specifying expiration date.
	 */
	initListenToManualIssueGiftCardRadioElements: function () {
		// @note: there are two radios we are interested in
		// @note: one is for toggle specify custom denomination
		// @note: the other is for toggle specify expiry date
		const giftCardRadioElements = document.querySelectorAll(
			'input[name="pwcommerce_issue_gift_card_denomination_mode"],input[name="pwcommerce_issue_gift_card_set_expiration_date"]'
		)

		if (giftCardRadioElements) {
			for (const giftCardRadioElement of giftCardRadioElements) {
				// add event listener to each gift card radio
				giftCardRadioElement.addEventListener(
					"change",
					ProcessPWCommerce.handleManualIssueGiftCardRadioChange,
					false
				)
			}
		}
	},

	handleManualIssueGiftCardRadioChange: function (event) {
		const selectedRadioElement = event.target
		const selectedRadioValue = selectedRadioElement.value
		const selectedRadioParentElement = selectedRadioElement.closest(
			"li.InputfieldRadios"
		)

		// send the window event to Alpine.JS
		const eventName = "pwcommercemanualissuegiftcardcoderadiochangenotification"
		// const eventDetail = selectedRadioValue

		const notificationType =
			selectedRadioParentElement.dataset.giftCardRadioChangeType

		const eventDetail = {
			type: notificationType,
			value: selectedRadioValue,
		}
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	/**
	 * Set ready status of customer email or end date for manually issue gift card.
	 * Set in Alpine Store.
	 * Trigger using window event.
	 */
	setIsReadyForManualIssueGiftCard: function (mutation_record) {
		let element, elementValue, notificationType

		if (mutation_record.length) {
			// WE HAVE A MUTATION RECORD
			// -------
			// get the first item
			element = mutation_record[0].target

			// is this a parent element?
			if (element.dataset.giftCardMutationObserverElementId) {
				// this is a parent element; the ID of the input element itself is in the data attr of the parent
				// for use with hard to observe items such as jQuery UI Datepicker widget
				// -------------
				// grab the ID of the input element whose value we need
				const inputElementID = element.dataset.giftCardMutationObserverElementId
				// get the element itself
				const inputElement = document.getElementById(inputElementID)
				if (inputElement) {
					// if we found the element, make it the element to grab the value from
					element = inputElement
				}
			}

			// set the element value
			elementValue = element.value

			// grab the notification type
			// for now, we have 'email' and 'date' {end date}
			// we'll use this in Alpine to determine how we validate the value
			notificationType =
				element.dataset.giftCardMutationObserverNotificationType
		}

		// send the window event to Alpine.JS
		const eventName =
			"pwcommercemanualissuegiftcardcodemutationobservernotification"
		const eventDetail = {
			type: notificationType,
			value: elementValue,
		}
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},

	// ~~~~~~~~~~~~~~~~~

	// SPECIAL FOR 'CUSTOMERS'

	/**
	 * Observe changes in InputfieldCKEditor textarea.
	 * We have no direct access to events there.
	 * We use a mutation observer to watch for the changes instead.
	 */
	initListenToEmailCustomerBodyMutationObserverElement: function () {
		const iframe = document.querySelector("#main iframe")
		if (iframe) {
			iframe.onload = function () {
				const iframeDocument = iframe.contentWindow.document
				// iframeDocument.addEventListener('click', function(e) {
				//     if (e.target.tagName.toLowerCase() == 'span') { // Element clicked inside iframe is a span element
				//         // Do your work here
				//     }
				// })
				// console.log("iframeDocument", iframeDocument)
			}
		}

		return
		const emailCustomerBodyElement = document.getElementById(
			"pwcommerce_email_customer_email_body"
		)

		const testElement = document.querySelector("iframe")
		console.log(
			"initListenToEmailCustomerBodyMutationObserverElement - emailCustomerBodyElement",
			emailCustomerBodyElement
		)
		console.log(
			"initListenToEmailCustomerBodyMutationObserverElement - testElement",
			testElement
		)
		if (emailCustomerBodyElement) {
			// Observe a specific DOM element:
			// then call callback 'ProcessPWCommerce.setIsReadyForManualIssueGiftCard' ON observed mutations

			// +++++++
			const observer = new MutationObserver(
				ProcessPWCommerce.setIsReadyToEmailCustomer
			)

			// SET OBSERVER ON ELEMENT
			observer.observe(emailCustomerBodyElement, {
				attributes: true,
				// attributeOldValue: true,
			})
		}
	},
	/**
	 * Set ready status of customer email or end date for manually issue gift card.
	 * Set in Alpine Store.
	 * Trigger using window event.
	 */
	setIsReadyToEmailCustomer: function (mutation_record) {
		let element, elementValue, notificationType

		// console.log("setIsReadyToEmailCustomer - mutation_record", mutation_record)

		return

		if (mutation_record.length) {
			// WE HAVE A MUTATION RECORD
			// -------
			// get the first item
			element = mutation_record[0].target

			// is this a parent element?
			if (element.dataset.giftCardMutationObserverElementId) {
				// this is a parent element; the ID of the input element itself is in the data attr of the parent
				// for use with hard to observe items such as jQuery UI Datepicker widget
				// -------------
				// grab the ID of the input element whose value we need
				const inputElementID = element.dataset.giftCardMutationObserverElementId
				// get the element itself
				const inputElement = document.getElementById(inputElementID)
				if (inputElement) {
					// if we found the element, make it the element to grab the value from
					element = inputElement
				}
			}

			// set the element value
			elementValue = element.value

			// grab the notification type
			// for now, we have 'email' and 'date' {end date}
			// we'll use this in Alpine to determine how we validate the value
			notificationType =
				element.dataset.giftCardMutationObserverNotificationType
		}

		// send the window event to Alpine.JS
		const eventName =
			"pwcommercemanualissuegiftcardcodemutationobservernotification"
		const eventDetail = {
			type: notificationType,
			value: elementValue,
		}
		// @note: method is in PWCommerceCommonScripts.js
		PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
	},
}

/**
 * DOM ready
 *
 */
document.addEventListener("DOMContentLoaded", function (event) {
	ProcessPWCommerce.initRestyleProcessWirePanelTabForPWCommerceMenu()
	if (typeof htmx !== "undefined") {
		// @TODO: WORK ON THIS
		ProcessPWCommerce.listenToHTMXRequests()
		// listen to inputfield selector changes
		ProcessPWCommerce.initListenToPWCommerceInputfieldSelectorChanges()
		// @note: check if needed before initing!
		const isUsingWireTabs = document.getElementById("pwcommerce_tabs_wrapper")
		//----
		if (isUsingWireTabs) {
			$("#pwcommerce_tabs_wrapper").WireTabs({
				items: $(".WireTab"),
				rememberTabs: true,
			})
		}
		// focus title field if in a context with 'add new'
		ProcessPWCommerce.focusAddNewItemTitleInput()
		// listen to run pwcommerce configure install button click
		// @note: using here since issue with adding alpine handler to button (only works on head button)
		ProcessPWCommerce.initListenToPWCommerceConfigureInstallButtonClick()
		// -----
		// listen to run pwcommerce complete removal button click
		// @note: using here since issue with adding alpine handler to button (only works on head button)
		ProcessPWCommerce.initListenToPWCommerceCompleteRemovalButtonClick()
		// listen to manually issue gift card customer email and expiry date changes (selectize)
		ProcessPWCommerce.initListenToManualIssueGiftCardMutationObserverElements()
		// listen to inputfield ckeditor changes
		ProcessPWCommerce.initListenToEmailCustomerBodyMutationObserverElement()
		// listen to manually issue gift card radio changes for denomination and expiry date changes (selectize)
		ProcessPWCommerce.initListenToManualIssueGiftCardRadioElements()
		// listen to radio changes in tabs in general settings to determine showing more info
		ProcessPWCommerce.initListenToGeneralSettingsRadioElements()
		// listen to radio changes for setting behaviour and visibility of child pages of custom shop root page
		ProcessPWCommerce.initListenToCustomShopRootPageTreeManagementRadioElements()
	}
	// get current pwcommerce shop context
	const pwcommerceShopCurrentContext =
		PWCommerceCommonScripts.getPWCommerceShopCurrentContext()

	// get charts if on home dashboard
	// @note: if on pwcommerce home dashboard, context is empty
	if (!pwcommerceShopCurrentContext) {
		ProcessPWCommerce.getHomeDashboardCharts()
	}
})

// ~~~~~~~~~~~~~~~~~~

// ALPINE
document.addEventListener("alpine:init", () => {
	Alpine.store("ProcessPWCommerceStore", {
		// PROPERTIES
		//----------------
		// BOOLEANS TO SET IF MODALS FOR EDITING ITEMS ARE OPEN/CLOSED + first load, etc
		// is_ready_bulk_edit_action: false,
		is_ready_bulk_edit_action: 0,

		// DATA
		// SHOP SETTINGS
		general_settings_gui_navigation_type: null,
		general_settings_gui_quick_filters_and_advanced_search: null,
		general_settings_product_price_fields_type: null,
		// +++++++++++++++++++++++++++++
		// *** bulk edits ***
		// for the 'select' bulk edit action
		bulk_edit_action: null,
		// for the 'checkbox: all selected'
		// @note: if at least one item in table selected, we check this box
		bulk_edit_some_items_checked: false,
		// to model at least one item selected
		pwcommerce_bulk_edit_selected_items: [],
		// are all checkboxes checked. @todo delete if not in use!
		is_all_checkbox_checked: false,
		// *** countries/tax rates add ***
		// selected_countries: [],
		total_selected_countries: 0,
		open_continent_accordions: [],
		continents: [],
		countries: [],
		already_added_countries: [],
		flags_url: null,
		search_country_query: "",
		search_country_results_count: 0,
		no_continent_country_found_after_filter: [],
		// *** inventory inline edit ***
		inventory: {},
		inventory_items_in_inline_edit: [],
		edited_inventory_items_ids: [],
		// *** find anything search ***
		is_find_anything_list_closable: false,
		// *** quick filter search ***
		quick_filter_value: null,
		// +++++++++++++++++++++++++++++
		// ORDER ACTIONS
		// *** mark order as pending, paid, shipment delivered, etc ***
		// @note: we now add these and any future similar 'mark as actions' programatically on the fly
		// is_mark_as_pending_modal_open: false,
		// is_mark_as_paid_modal_open: false,
		// ------------------------
		// @TODO @NOTE: GETTING UPDATED TO HANDLE ALL ACTIONS!
		// for 'mark order as paid' modal, ensure a payment method is selected
		// else we disable apply button
		is_ready_apply_order_status_action: false,
		selected_order_invoice_action_type: null,
		selected_order_status_action_type: null,
		selected_order_status_action_payment_amount: null,
		selected_order_status_action_payment_method: null,
		// @note: WE MODEL ONE PROPERTY FOR ALL THREE STATUS ACTIONS SELECTS
		selected_status_action: null,
		special_payment_actions_flags: {},
		// +++++++++++++++++++++++++++++
		// GIFT CARDS
		// gift_card_code: null,
		is_copied_gift_card_code: -1,
		is_valid_gift_card_customer_email: false,
		is_valid_gift_card_end_date: false,
		is_specifying_custom_denomination: false,
		is_setting_gift_card_end_date: false,
		gift_card_customer_email: null, // @note: not in use for now
		denomination_mode: null,
		denomination_pre_defined: null,
		denomination_custom: null,
		set_expiration_date: null,
		end_date: null,
		// +++++++++++++++++++++++++++++
		// DISCOUNTS
		is_create_discount_select_type_modal_open: false,
		create_discount_type: null,
		// +++++++++++++++++++++++++++++
		// *** configure install and complete removal ***
		is_confirm_run_configure_install_modal_open: false,
		// spinner for both configure install and complete removal
		is_show_run_installer_spinner: false,
		is_confirm_run_complete_removal_modal_open: false,
		// optional features
		existing_optional_features_to_remove: [],
		new_optional_features_to_add: [],
		// other optional settings
		existing_other_optional_settings_to_remove: [],
		new_other_optional_settings_to_add: [],
		custom_shop_root_page_children_page_tree_management:
			"not_visible_in_page_tree",
		// +++++++++++++++++++++++++++++
		// CUSTOMERS
		is_valid_customer_email: false,
		is_valid_customer_confirm_email: false,
		is_matched_confirm_customer_email: false,
		customer_email: null,
		customer_confirm_email: null,
		is_open_email_customer_modal: false,
		is_ready_email_customer: false,
	})
	Alpine.data("ProcessPWCommerceData", () => ({
		//---------------
		// FUNCTIONS

		// ~~~~~~~~~~~~~~~~~

		// SPECIAL FOR GENERAL SETTINGS

		/**
		 * Init data for general settings.
		 * @return {void}.
		 */
		initGeneralSettingsGUI(property, value) {
			this.setStoreValue(property, value)
		},

		handleGeneralSettingsRadioChange: function (event) {
			const property = event.detail.property
			const value = event.detail.value
			this.setStoreValue(property, value)
		},

		// +++++++++++++

		/**
		 * Handles changes to bulk edit/view actions select.
		 * @param {bool} is_view_only Is the bulk action page view only or for editing items
		 */
		handleBulkEditActionChange(is_view_only = false) {
			this.checkIsReadyToBulkEdit(is_view_only)
		},

		handleBulkEditItemCheckboxChange(event) {
			const checkboxValue = event.target.value
			const isChecked = event.target.checked
			// get the 'check all' checkbox
			const checkAllCheckbox = document.getElementById(
				"pwcommerce_bulk_edit_checkboxall"
			)

			//------------
			// get all currently selected items
			let selectedItems = this.getStoreValue(
				"pwcommerce_bulk_edit_selected_items"
			)

			//--------------
			// ALL checkbox is current checkbox to action
			if (checkboxValue === "all") {
				const items = document.getElementsByClassName(
					"pwcommerce_bulk_edit_selected_items"
				)
				for (const item of items) {
					// skip the 'all' checkbox
					if (item.value === "all") continue
					// if 'all' is checked, add all IDs of items
					if (isChecked) {
						selectedItems.push(item.value)
					} else {
						// empty all selected
						selectedItems = []
					}
					//----------
					// @note: checkboxes are always tricky so we use vanilla JavaScript to set checked status
					// set checked state
					item.checked = isChecked
				}

				// uncheck/check all
				this.setStoreValue("is_all_checkbox_checked", isChecked)
			}
			// SINGLE checkbox is current checkbox to action
			else {
				// if checked, add the checkbox value (ID) to selected items
				if (isChecked) {
					selectedItems.push(checkboxValue)
					// since at least once SINGLE item selected, also check the 'check all' checkbox
					// @TODO: WOULD HAVE BEEN BETTER WITH 'HALF SELECTED VISUAL'
					checkAllCheckbox.checked = isChecked
				} else {
					// item is unchecked. remove it from selected items
					selectedItems = selectedItems.filter(
						(value) => value !== checkboxValue
					)
					// if all selected items removed, uncheck the 'check all' checkbox
					if (!selectedItems.length) {
						checkAllCheckbox.checked = isChecked
					}
				}
			}

			// set to store as selected/deselected
			this.setStoreValue("pwcommerce_bulk_edit_selected_items", selectedItems)

			//---------------
			// check if ready to bulk edit
			this.checkIsReadyToBulkEdit()
		},

		handleOpenModal(property) {
			this.setStoreValue(property, true)
		},

		handleCloseModal(property) {
			this.setStoreValue(property, false)
		},

		checkIsReadyToBulkEdit(is_view_only = false) {
			let selectedBulkEditAction = this.getStoreValue("bulk_edit_action")
			selectedBulkEditAction = selectedBulkEditAction
				? selectedBulkEditAction
				: ""
			// is an action selected ready to apply?
			const isReadyBulkEditAction = selectedBulkEditAction.length > 0
			// are edit items selected ready to action?
			// @note: only applicable if 'is_view_only' is false
			const isReadyBulkEditSelectedItems = is_view_only
				? true
				: this.getStoreValue("pwcommerce_bulk_edit_selected_items").length > 0

			//-----------------
			const isReadyToBulkEdit =
				isReadyBulkEditAction && isReadyBulkEditSelectedItems

			//-----------------
			const isReadyToBulkEditInt = isReadyToBulkEdit ? 1 : 0
			this.setStoreValue("is_ready_bulk_edit_action", isReadyToBulkEditInt)
		},

		// ~~~~~~~~~~~~~~~~~

		// SPECIAL FOR 'FIND ANYTHING'

		// handle focus in or out or escape
		handleFindAnythingEvent(event) {
			// get event type
			const eventType = event.type
			// get ID of event element to distinguish between input#pwcommerce_find_anything_search_box (search box)
			// and ul#pwcommerce_find_anything_results event triggers
			const eventTargetElementID = event.target.id

			// ----------------
			if (eventType === "focusin") {
				// handle focusing in
				this.handleFindAnythingFocusIn(eventTargetElementID)
			} else if (eventType === "focusout") {
				// handle focusing out
				this.handleFindAnythingFocusOut()
			} else if (eventType === "keydown" && event.which === 27) {
				// handle esc key pressed
				event.preventDefault()
				// handle escape key === focusing out
				this.handleFindAnythingFocusOut()
			}
		},

		// handle focus in
		handleFindAnythingFocusIn(eventTargetElementID) {
			if (eventTargetElementID === "pwcommerce_find_anything_results") {
				// focus-in element is ul#pwcommerce_find_anything_results (RESULTS LIST)
				// CANCEL 'is_find_anything_list_closable' set by input#pwcommerce_find_anything_search_box
				this.setStoreValue("is_find_anything_list_closable", false)
				// unhide the list
				this.toggleFindAnythingResultsListHiddenClass(false)
			} else {
				// DO NOT CANCEL 'is_find_anything_list_closable' set by input#pwcommerce_find_anything_search_box
				// CLOSE (hide) LIST AFTER A FEW
				this.setStoreValue("is_find_anything_list_closable", true)
				setTimeout(() => {
					if (this.getStoreValue("is_find_anything_list_closable")) {
						// add hidden class
						this.toggleFindAnythingResultsListHiddenClass()
					} else {
						// unlikely, but just in case...abort (remove hidden class)
						this.toggleFindAnythingResultsListHiddenClass(false)
					}
				}, 250)
			}
		},

		// handle focus out
		handleFindAnythingFocusOut() {
			this.setStoreValue("is_find_anything_list_closable", true)
			setTimeout(() => {
				if (this.getStoreValue("is_find_anything_list_closable")) {
					// add hidden class
					this.toggleFindAnythingResultsListHiddenClass()
				} else {
					// results list gained focus after search box gained focus: abort closing the list (remove hidden class)
					this.toggleFindAnythingResultsListHiddenClass(false)
				}
			}, 250)
		},

		// find anything results have come in; show them
		handleFindAnythingShowResultsList() {
			this.setStoreValue("is_find_anything_list_closable", false)
			this.toggleFindAnythingResultsListHiddenClass(false)
		},

		handleFindAnythingResultClicked() {
			const findAnythingSearchBox = document.getElementById(
				"pwcommerce_find_anything_search_box"
			)
			if (findAnythingSearchBox) {
				findAnythingSearchBox.value = ""
			}
		},

		// validate and send request for find anything to htmx
		// require at least 2 characters in search box before send
		handleFindAnythingInput(event) {
			// input#pwcommerce_find_anything_search_box
			const findAnythingSearchBox = event.target
			if (findAnythingSearchBox && findAnythingSearchBox.value.length > 1) {
				// at least 2 characters entered, fire find anything!
				this.triggerHTMXFindAnythingSearch(findAnythingSearchBox)
			} else {
				// close the results list
				this.toggleFindAnythingResultsListHiddenClass()
			}
		},

		// fire event to tell htmx to run a find anything request
		// @note: only fires if characters are > 1
		// @note: // input#pwcommerce_find_anything_search_box is listening to this event ('pwcommercefindanything')
		triggerHTMXFindAnythingSearch(triggerElement) {
			const eventName = "pwcommercefindanything"
			htmx.trigger(triggerElement, eventName)
		},

		toggleFindAnythingResultsListHiddenClass(isHide = true) {
			const findAnythingListElement = document.getElementById(
				"pwcommerce_find_anything_results"
			)
			if (isHide) {
				findAnythingListElement.classList.add("hidden")
			} else {
				findAnythingListElement.classList.remove("hidden")
			}
		},

		// @TODO: UNSURE OF THIS? LEAVE ALONE? KEEP HISTORY? CLEAR HISTORY? MAYBE FOR FUTURE!
		async emptyFindAnythingResultsList() {
			// clear the results list
			const findAnythingResultsList = document.getElementById(
				"pwcommerce_find_anything_results"
			)
			if (findAnythingResultsList) {
				findAnythingResultsList.replaceChildren()
			}
		},

		// ~~~~~~~~~~~~~~~~~

		// SPECIAL FOR QUICK FILTER
		/**
		 * Fetch pages per context quick filter.
		 * Trigger using htmx
		 */
		handleFetchPagesQuickFilter(event) {
			const quickFilterElement = event.target
			const quickFilterValue = quickFilterElement.dataset.filter
			// set quick filter value
			this.setStoreValue("quick_filter_value", quickFilterValue)
			// tell htmx to filer built edit table for context
			this.fetchPagesQuickFilter()
		},
		/**
		 * Fetch pages per context quick filter.
		 * Trigger using htmx
		 */
		fetchPagesQuickFilter() {
			// to tell Process Module to fetch new pages based on quick filter for context
			// @TODO: IF GETTING DESELECTED?!!
			const eventName = "pwcommercefetchpagesforquickfilter"
			const elem = document.getElementById("pwcommerce_quick_filter_wrapper")
			htmx.trigger(elem, eventName)
		},

		// ~~~~~~~~~~~~~~~~~

		// SPECIAL FOR ADD NEW COUNTRIES/TAX RATES

		handleContinentCheckboxChange(event) {
			const continentCheckboxElement = event.target
			const isChecked = event.target.checked // checkbox checked status
			const continentListElement = continentCheckboxElement.closest(
				"li.pwcommerce_add_new_countries_continent"
			)

			if (continentListElement) {
				// this continent's countries (children)
				const continentCountriesCheckboxesElements =
					continentListElement.querySelectorAll(
						"ul.pwcommerce_add_new_countries_continent_countries_list_wrapper input.pwcommerce_add_new_countries"
					)
				// check/uncheck the continent's countries' checkboxes
				if (continentCountriesCheckboxesElements) {
					for (const continentCountriesCheckboxesElement of continentCountriesCheckboxesElements) {
						continentCountriesCheckboxesElement.checked = isChecked
					}
					// finally, reset total selected countries
					this.setTotalSelectedCountries()
				}
			}
		},

		handleCountryCheckboxChange(event) {
			const countryCheckboxElement = event.target
			const isChecked = event.target.checked // checkbox checked status
			// the reference of this country's continent is stored in 'data-country-continent' attribute of this country's checkbox
			const countryContinentRef =
				countryCheckboxElement.dataset.countryContinent

			// the checkbox of the continent that this country belongs to
			const continentCheckboxElement = this.$refs[countryContinentRef]

			// INCREMENT/DECREMENT TOTAL COUNTRIES SELECTED
			let totalSelectedCountries = this.getStoreValue(
				"total_selected_countries"
			)
			totalSelectedCountries = isChecked
				? totalSelectedCountries + 1
				: totalSelectedCountries - 1
			// set new count of selected countries back to the store
			this.setStoreValue("total_selected_countries", totalSelectedCountries)

			// CHECK/UNCHECK CONTINENT AS WELL, if applicable
			if (continentCheckboxElement) {
				// if country is checked,we always checked the continent as well
				if (isChecked) {
					continentCheckboxElement.checked = isChecked
				} else {
					continentCheckboxElement.checked =
						this.isAtLeastOneContinentCountryChecked(continentCheckboxElement)
				}
			}
		},

		handleSearchInput(event) {
			const query = event.target.value
			let countriesFound = -1 // to denote no query/not searching
			if (query) {
				countriesFound = this.getAllCountries().length
			}
			// set countries found count
			this.setStoreValue("search_country_results_count", countriesFound)
		},

		/**
		 * Init data for continents and countries.
		 * @return {void}.
		 */
		initContinentsAndCountriesData() {
			this.setAllContinentsAndCountriesData()
		},

		isContinentAccordionOpen(continent_id) {
			const currentOpenContinentAccordions = this.getStoreValue(
				"open_continent_accordions"
			)
			return currentOpenContinentAccordions.includes(continent_id)
		},

		isAlreadyAdded(country_id) {
			const alreadyAddedCountries = this.getStoreValue(
				"already_added_countries"
			)
			return alreadyAddedCountries.includes(country_id)
		},

		isHideContinent(continent_id) {
			const noContinentCountryFound = this.getStoreValue(
				"no_continent_country_found_after_filter"
			)
			return noContinentCountryFound.includes(continent_id)
		},
		// @TODO: WORK ON IS_READY TO POST!??

		isAtLeastOneContinentCountryChecked(continentCheckboxElement) {
			// @note: here we find the first checked country that belongs to this country
			const continentListElement = continentCheckboxElement.closest(
				"li.pwcommerce_add_new_countries_continent"
			)
			//------
			if (continentListElement) {
				// find at least one of this continent's countries (children) checkbox that is checked
				const continentCountriesOneCheckedCheckboxElement =
					continentListElement.querySelector(
						"ul.pwcommerce_add_new_countries_continent_countries_list_wrapper input.pwcommerce_add_new_countries:checked"
					)
				// check/uncheck the continent's countries' checkboxes
				if (continentCountriesOneCheckedCheckboxElement) {
					return true
				}
			}
			return false
		},

		isShowFilterFoundCount() {
			const searchCountryQueryLength = this.getStoreValue(
				"search_country_query"
			).length
			const searchCountryResultsCount = this.getStoreValue(
				"search_country_results_count"
			)
			// @note: -1 means no search taking place
			return searchCountryQueryLength > 0 && searchCountryResultsCount > -1
		},

		// ~~~~~~~~~~~~~~~~~

		// SPECIAL FOR INVENTORY INLINE EDIT

		/**
		 * Init data for inventory.
		 * @return {void}.
		 */
		initInventoryData() {
			this.setAllInventoryData()
		},

		// @note: product can be a product without variant OR a variant
		// init inline-edit of a product in a row in the inventory table
		initInventoryItemInlineEdit(product_id) {
			// @note: 'this' here refers to the local x-data for the table row with this inventory item
			// @note: we might as well have used this.id instead of product_id
			product_id = parseInt(product_id)
			// process inventory item values for setting in inline edit items
			const inventoryItemForInlineEdit =
				this.processInventoryItemInlineEdit(product_id)
			// add inventory item to inline edit items @note: acts as clone if we need to revert
			this.setInventoryItemForInlineEdit(inventoryItemForInlineEdit)
		},
		// handle ACCEPT changes in an in-edit of a row in the inventory table
		handleInventoryItemInlineEditAccept(product_id) {
			product_id = parseInt(product_id)
			// @note: even if edited just once and later reverted, we will still go with the original 'it was edited at least once' - hence, save it to server! but with the new values as per the form inputs
			// --------
			// add inventory item ID to ids of edited inventory items
			// then remove inline item from pool of inventory of items in inline-edit
			// @TODO @UPDATE: NOW SAVING EDITS IMMEDIATELY TO SERVER ON ACCEPT EDIT; WE USE HTMX FOR THIS; HENCE, NO NEED TO ADD ITEM TO IDS OF EDITED INVENTORY ITEMS; INSTEAD, WE JUST DO THE REMOVAL OF INVENTORY ITEM FROM EDITED ITEMS POOL. MAYBE TRIGGER HTMX FROM HERE THOUGH
			// @TODO - DELETE WHEN DONE
			// this.setInventoryItemIDToEditedItems(product_id).then(
			// 	this.removeInventoryItemFromItemsInInlineEdit(product_id)
			// );
			this.removeInventoryItemFromItemsInInlineEdit(product_id).then(
				this.triggerHTMXSaveInventoryInlineEdit(product_id)
			)
		},
		// handle REJECT changes in an in-edit of a row in the inventory table
		handleInventoryItemInlineEditReject(product_id) {
			product_id = parseInt(product_id)
			// revert edited inventory item to last values pre-edit
			// then remove inventory item from editing pool
			this.revertEditedInventoryItemValuesToLastValuesBeforeEdit(
				product_id
			).then(this.removeInventoryItemFromItemsInInlineEdit(product_id))
		},

		// Update and refresh inline-edited inventory table row
		triggerHTMXSaveInventoryInlineEdit() {
			// for submitting then fetching edited inventory table row
			const eventName = "pwcommerceinventoryinlineeditoftablerow"
			const triggerElement = document.getElementById(
				`pwcommerce_inventory_row_${this.id}`
			)
			htmx.trigger(triggerElement, eventName)
		},

		isInventoryItemInInlineEdit(product_id) {
			const inventoryItemsInInlineEdit = this.getInventoryItemsInInlineEdit()
			//---------
			let isInventoryItemInInlineEdit = false
			if (inventoryItemsInInlineEdit.length) {
				isInventoryItemInInlineEdit = inventoryItemsInInlineEdit.some(
					(item) => parseInt(item.id) === parseInt(product_id)
				)
			}
			// -----
			return isInventoryItemInInlineEdit
		},

		processInventoryItemInlineEdit(product_id) {
			// @note: 'this' here refers to the local x-data for the table row with this inventory item
			// @note: we are using this.id instead of product_id; same thing
			const inventoryItemForInlineEdit = {
				id: parseInt(this.id), // @note: Integer!
				// text inputs
				sku: this.sku,
				quantity: this.quantity,
				// checkboxes
				oversell: this.allowBackorders ? true : false,
				enabled: this.enabled ? true : false,
			}
			// ----------
			return inventoryItemForInlineEdit
		},

		// ---------

		/**
		 *Get the ProcessWire config sent for inventory bulk edit and inline-edit view.
		 * @return object.
		 */
		getProcessWireInventoryConfig() {
			return ProcessWire.config.PWCommerceProcessRenderInventory
		},

		getInventoryItemsInInlineEdit() {
			return this.getStoreValue("inventory_items_in_inline_edit")
		},

		// ----------
		getItemFromEditedInventoryItemsByID(product_id) {
			const inventoryItemsInInlineEdit = this.getInventoryItemsInInlineEdit()
			const inventoryItem = inventoryItemsInInlineEdit.find(
				(item) => parseInt(item.id) === parseInt(product_id)
			)
			// -----------
			return inventoryItem
		},

		// ----------

		// ------
		/**
		 * Set all the inventory data.
		 *
		 * @return {void}.
		 */
		setAllInventoryData() {
			const inventoryData = this.getProcessWireInventoryConfig()
			this.setStoreValue("inventory", inventoryData)
		},
		/**
		 * Add an item to pool of copy inventory items that are in inline edit.
		 * Used to revert back to the latest values pre-edit if edit is rejected.
		 * @param {object} inventoryItemForInlineEdit Inventory item to set in pool of inline-edit items.
		 */
		setInventoryItemForInlineEdit(inventoryItemForInlineEdit) {
			const inventoryItemsInInlineEdit = this.getInventoryItemsInInlineEdit()
			// -------------
			// add inventory item in inline edit items pool if not already there
			if (!this.isInventoryItemInInlineEdit(inventoryItemForInlineEdit.id)) {
				inventoryItemsInInlineEdit.push(inventoryItemForInlineEdit)
				// -----------------
				this.setStoreValue(
					"inventory_items_in_inline_edit",
					inventoryItemsInInlineEdit
				)
			}
		},
		async setInventoryItemIDToEditedItems(product_id) {
			// @note: 'this' here refers to the local x-data for the table row with this inventory item
			// @note: we might as well have used this.id instead of product_id
			// edited_inventory_items_ids
			const editedInventoryItemsIDs = this.getStoreValue(
				"edited_inventory_items_ids"
			)
			// add this inventory ID to pool of IDs of edited inventory items if it is not already in there
			if (!editedInventoryItemsIDs.includes(parseInt(product_id))) {
				editedInventoryItemsIDs.push(parseInt(product_id))
				this.setStoreValue(
					"edited_inventory_items_ids",
					editedInventoryItemsIDs
				)
			}
		},
		async revertEditedInventoryItemValuesToLastValuesBeforeEdit(product_id) {
			// @note: 'this' here refers to the local x-data for the table row with this inventory item
			// @note: we might as well have used this.id instead of product_id
			const editedInventoryItemLastValuesBeforeEdit =
				this.getItemFromEditedInventoryItemsByID(product_id)
			//-----------------
			if (editedInventoryItemLastValuesBeforeEdit) {
				// revert values
				//-----------------
				this.sku = editedInventoryItemLastValuesBeforeEdit.sku
				this.quantity = editedInventoryItemLastValuesBeforeEdit.quantity
				this.allowBackorders = editedInventoryItemLastValuesBeforeEdit.oversell
				this.enabled = editedInventoryItemLastValuesBeforeEdit.enabled
			}
		},

		async removeInventoryItemFromItemsInInlineEdit(product_id) {
			// @note: 'this' here refers to the local x-data for the table row with this inventory item
			// @note: we might as well have used this.id instead of product_id
			// get current inventory items in inline-edit pool
			let inventoryItemsInInlineEdit = this.getInventoryItemsInInlineEdit()
			inventoryItemsInInlineEdit = inventoryItemsInInlineEdit.filter(
				(item) => parseInt(item.id) !== parseInt(product_id)
			)
			// --------------
			// set modified pool of inventory items in inline-edit
			this.setStoreValue(
				"inventory_items_in_inline_edit",
				inventoryItemsInInlineEdit
			)
		},

		// ~~~~~~~~~~~~~~~~~

		// SPECIAL FOR ACTION ORDER STATUS

		// ~~~~~~~~~~~~~~~~~
		// @TODO - THESE ARE GETTING UPDATED! WE WILL NOW HAVE ALL AVAILABLE ACTIONS INCLUDING REFUNDS! -> IT MEANS DIFFERENT MODALS PER CONTEXT!
		// SPECIAL FOR MARK ORDER AS...
		initSpecialPaymentActionsFlags(special_payment_actions_flags_data) {
			this.setStoreValue(
				"special_payment_actions_flags",
				special_payment_actions_flags_data
			)
		},

		initIsReadyApplyOrderStatusAction() {
			this.processIsReadyApplyOrderStatusAction()
		},

		processIsReadyApplyOrderStatusAction() {
			const orderStatusSelectedActionApply =
				this.$refs["pwcommerce_order_status_selected_action_apply"]

			const specialPaymentFlags = this.getStoreValue(
				"special_payment_actions_flags"
			)

			let isReadyApplyOrderStatusAction = true
			if (orderStatusSelectedActionApply) {
				const orderStatusSelectedActionApplyFlag = parseInt(
					orderStatusSelectedActionApply.value
				)

				// is the current order status action one of our special payment ones
				if (
					Object.values(specialPaymentFlags).includes(
						orderStatusSelectedActionApplyFlag
					)
				) {
					// application of status still not reay
					// changes to payment method and/or part refund/paid amount will now be checking and updating this value
					isReadyApplyOrderStatusAction = false
					// ============
					const selectedPaymentMethod = parseInt(
						this.getStoreValue("selected_order_status_action_payment_method")
					)

					// @note: amount can be partial refund or partial paid
					const selectedPaymentAmount = parseFloat(
						this.getStoreValue("selected_order_status_action_payment_amount")
					)

					// ###### DO READINESS CHECKS FOR SPECIAL PAYMENT RELATED STATUSES ###########
					if (selectedPaymentMethod) {
						// CHECKS FOR 'PAID' STATUSES ACTIONS
						if (
							specialPaymentFlags["paid"] === orderStatusSelectedActionApplyFlag
						) {
							// fully paid order payment method is seleted -> 'release' apply button
							isReadyApplyOrderStatusAction = true
						} else if (
							selectedPaymentAmount &&
							specialPaymentFlags["partially_paid"] ===
								orderStatusSelectedActionApplyFlag
						) {
							// partly paid order payment method is seleted + amount specified -> 'release' apply button
							isReadyApplyOrderStatusAction = true
						}
					} else if (
						selectedPaymentAmount &&
						specialPaymentFlags["partially_refunded"] ===
							orderStatusSelectedActionApplyFlag
					) {
						// CHECK FOR 'PARTIAL REFUND' STATUS ACTION
						isReadyApplyOrderStatusAction = true
					}
					// ========= END: CHECKS
				}
			}

			// -------
			// set readiness value to store

			this.setStoreValue(
				"is_ready_apply_order_status_action",
				isReadyApplyOrderStatusAction
			)
		},

		handleOrderStatusPaymentAmountChange(event) {
			this.processIsReadyApplyOrderStatusAction()
		},

		handleOrderStatusPaymentMethodChange(event) {
			this.processIsReadyApplyOrderStatusAction()
		},

		handleOrderStatusActionTypeChange() {
			// clear previous selected status action to handle cases where there is no action type selection made and we need to hide the link to 'edit and confirm' the action
			this.setStoreValue("selected_status_action", null)
		},

		handleEditAndConfirmOrderAction() {
			// selected_order_order_status_action: null,
			// selected_order_payment_action: null,
			// selected_order_shipment_status_action: null,
			// const selectedStatusActionType = this.getStoreValue(
			// 	"selected_order_status_action_type"
			// )
			// const selectedStatusAction = this.getStoreValue("selected_status_action")

			// open modal for for edit order status!
			this.handleOpenModal("is_order_status_modal_open")

			// ======
			// TRIGGER HTMX to fetch modal contents from server
			// the content is for the specific, selected action for one of the types 'order', 'payment' or 'shipping'
			const elem = document.getElementById("pwcommerce_order_status_action_apply")
			const eventName = "pwcommerceorderstatusfetch"
			// @TODO: SHORT DELAY?
			htmx.trigger(elem, eventName)
		},

		handleManualResetOrderStatusAction(is_order_status_modal_open) {
			if (!is_order_status_modal_open) {
			}
		},

		handleOrderStatusActionSendNotification(event) {
			// @TODO DELETE IF NOT IN USE!!!!
			// @note: custom window notification sent by htmx:afterSettle
			if (event.detail) {
				const eventDetail = JSON.parse(event.detail)
				if (eventDetail.reset_modal) {
					const delay = eventDetail.delay ? eventDetail.delay : 0
					setTimeout(() => {
						this.resetOrderStatusActionAndCloseModal()
					}, delay)
				}
			}
		},

		resetOrderStatusActionAndCloseModal() {
			this.handleCloseModal("is_order_status_modal_open")
			// @TODO: WE NEED TO DO THE SAME IF MODAL CLOSED VIA X IN TOP RIGHT! $watch?
			// also reset several properties related to order action value to null
			// @note: but we leave the action type itself as is!
			const statusActionsPropertiesToReset = [
				"selected_status_action", // reset selected action
				"selected_order_status_action_payment_amount", // reset order action payment amounts
				"selected_order_status_action_payment_method", // reset order action payment method
				"is_ready_apply_order_status_action", // reset readiness to apply action button
			]
			for (const property of statusActionsPropertiesToReset) {
				this.setStoreValue(property, null)
			}
			// clear previous messages MARKUP if present
			const previousStatusActionMarkup = document.getElementById(
				"pwcommerce_order_status_fetch_markup_response_wrapper"
			)
			if (previousStatusActionMarkup) {
				previousStatusActionMarkup.remove()
			}
		},

		isShowConfirmOrderAction() {
			const selectedOrderStatusAction = this.getStoreValue(
				"selected_order_order_status_action"
			)
			const selectedPaymentStatusAction = this.getStoreValue(
				"selected_order_payment_action"
			)
			const selectedShipmentStatusAction = this.getStoreValue(
				"selected_order_shipment_status_action"
			)
			const actions = [
				selectedOrderStatusAction,
				selectedPaymentStatusAction,
				selectedShipmentStatusAction,
			]

			const isShowLink = actions.some((item) => !!item)
			return isShowLink
		},

		// ~~~~~~~~~~~~~~~~~

		// SPECIAL FOR ISSUE GIFT CARD

		// ~~~~~~~~~~~~~~~~~

		/**
		 * Init data for gift card date error strings.
		 * @return {void}.
		 */
		initIssueGiftCardDatesErrorStringsData() {
			this.setAllIssueGiftCardDatesErrorStringsData()
		},

		// ------
		/**
		 * Set all the gift card translated error strings data.
		 *
		 * @return {void}.
		 */
		setAllIssueGiftCardDatesErrorStringsData() {
			const errorStringsData = this.getProcessWireGiftCardConfig()
			this.setStoreValue(
				"issue_gift_card_dates_error_strings",
				errorStringsData
			)
		},

		/**
		 *Get the ProcessWire config sent for gift card error strings.
		 * @return object.
		 */
		getProcessWireGiftCardConfig() {
			return ProcessWire.config.PWCommerceProcessRenderGiftCards
		},

		// @TODO DELETE IF NOT IN USE
		handleIssueGiftCard() {
			return

			// open modal for for manuall issue gift card!
			this.handleOpenModal("is_manual_issue_gift_card_modal_open")

			// ======
			// TRIGGER HTMX to fetch NEW, UNIQUEe GIFT CARD CODE from server
			// the content is for the specific, selected action for one of the types 'order', 'payment' or 'shipping'
			// const elem = document.getElementById("pwcommerce_manual_issue_gift_card")
			// @TODO: EDIT THIS EVENT NAME FOR GIFT CARDS!
			// const eventName = "pwcommercemanualissuegiftcardcodefetch"
			// @TODO: SHORT DELAY?
			// htmx.trigger(elem, eventName)
		},

		async handleCopyGiftCardCode() {
			let isSuccessfulCopy = false
			// @note: @updated: now hidden input on page
			const giftCardCodeElement = document.getElementById(
				"pwcommerce_manual_issue_gift_card_code"
			)
			if (giftCardCodeElement) {
				isSuccessfulCopy = await this.copyTextToClipboard(
					giftCardCodeElement.textContent
				)
				// isSuccessfulCopy = await this.copyTextToClipboard(
				// 	this.getStoreValue("gift_card_code")
				// )
				if (isSuccessfulCopy) {
					this.setStoreValue("is_copied_gift_card_code", 1)
				}
			} else {
				this.setStoreValue("is_copied_gift_card_code", 0)
			}
		},

		copyTextToClipboard(text) {
			const result = navigator.clipboard.writeText(text).then(
				() => {
					// console.log("Content copied to clipboard")
					/* Resolved - text copied to clipboard successfully */
					return true
				},
				() => {
					// console.error("Failed to copy")
					/* Rejected - text failed to copy to the clipboard */
					return false
				}
			)
			return result
		},

		handleIssueGiftCardMutationObserverChange(event) {
			const eventDetail = event.detail
			const validationType = eventDetail.type
			const validationValue = eventDetail.value
			if (validationType === "email") {
				// validate customer email
				const isValidCustomerEmail = this.validateEmailAddress(validationValue)
				// @NOTE: setting validity of customer email for gift card manual issuance
				this.setStoreValue(
					"is_valid_gift_card_customer_email",
					isValidCustomerEmail
				)
			} else if (validationType === "date") {
				// validate end date
				const isValidEndDate = this.validateEndDate(validationValue)
				// @NOTE: setting validity of end date  for gift card manual issuance
				this.setStoreValue("is_valid_gift_card_end_date", isValidEndDate)
			}
		},

		handleIssueGiftCardRadioChange(event) {
			const eventDetail = event.detail
			const radioType = eventDetail.type
			const radioValue = eventDetail.value

			if (radioType === "denomination") {
				// HANDLE DENOMINATION MODE STATUS
				let isSpecifyingCustomDenomination = false
				if (radioValue === "custom") {
					isSpecifyingCustomDenomination = true
				}
				this.setStoreValue(
					"is_specifying_custom_denomination",
					isSpecifyingCustomDenomination
				)
			} else if (radioType === "date") {
				// HANDLE SET EXPIRATION STATUS
				let isSettingExpirationDate = false
				if (radioValue === "set_expiration") {
					isSettingExpirationDate = true
				}
				this.setStoreValue(
					"is_setting_gift_card_end_date",
					isSettingExpirationDate
				)
			}
		},

		validateEmailAddress(email) {
			const validRegexPattern = this.getValidEmailRegexPattern()
			let isValidEmailAddress = false
			if (email.match(validRegexPattern)) {
				isValidEmailAddress = true
			}
			return isValidEmailAddress
		},

		validateEndDate(end_date) {
			// @note: reassigning just for legacy reasons
			const giftCardEndDateStr = end_date

			// @note get timestamp but if NaN then 'zero'

			const giftCardEndDate =
				this.convertStringDateToTimestamp(giftCardEndDateStr) || 0
			const giftCardNow = Math.floor(Date.now() / 1000)
			const issueGiftCardDatesErrorStrings =
				this.getIssueGiftCardDatesErrorStrings()

			/*

				~~~~ PAST DATES ~~~~
				a. end date < now/today date <-invalid
				~~~~ ONE DATE EMPTY & ONE NOT ~~~~
				b. end date EMPTY <- invalid
			*/

			// *** VALIDATION START ****

			let isValidEndDate = true
			let isSettingExpirationDate = false
			let endDateError

			// -----
			// for end date
			const endDateErrorTextProperty = "issue_gift_card_end_date_error_text"
			const endDateErrorFlagProperty = "is_error_issue_gift_card_end_date"
			// -----
			// check if setting expiration date
			// get the checked radio button for setting expiration date
			const setExpirationDateElement = document.querySelector(
				'input[name="pwcommerce_issue_gift_card_set_expiration_date"]:checked'
			)

			if (setExpirationDateElement) {
				// if we got the element and its selected value is to set expiration
				// we check if true
				isSettingExpirationDate =
					setExpirationDateElement.value === "set_expiration"
			}

			// #########
			if (isSettingExpirationDate) {
				// we are setting expriation > validate
				if (!giftCardEndDate) {
					// **INVALID**: END DATE IS EMPTY
					isValidEndDate = false
					// ~~~~~~~~~~~~
					// get end date 'EMPTY' error message
					endDateError = issueGiftCardDatesErrorStrings.no_end_date
				} else if (giftCardEndDate < giftCardNow) {
					// **INVALID**: END DATE IS IN THE PAST (LESS THAN NOW)
					isValidEndDate = false
					// ~~~~~~~~~~~~
					// get end date 'IN THE PAST' error message
					endDateError = issueGiftCardDatesErrorStrings.end_date_is_in_the_past
				}
			}

			// --------

			// ++++++ handle END DATE errors  ++++++

			if (endDateError) {
				// set END DATE error text
				this.setStoreValue(endDateErrorTextProperty, endDateError)
				// set END DATE SHOW ERROR MESSAGE 'TRUE'
				this.setStoreValue(endDateErrorFlagProperty, true)
			} else {
				// clear old error text if needed
				this.setStoreValue(endDateErrorTextProperty, null)
				// set SHOW ERROR MESSAGES FLAGS TO 'FALSE'
				this.setStoreValue(endDateErrorFlagProperty, false)
			}

			// -----
			return isValidEndDate
		},

		getIssueGiftCardDatesErrorStrings() {
			return this.getStoreValue("issue_gift_card_dates_error_strings")
		},

		checkIsReadyToSendGiftCard() {
			let isReadyToSendGiftCard = true

			// ============
			// CHECK CUSTOMER EMAIL
			const isValidCustomerEmail = this.getStoreValue(
				"is_valid_gift_card_customer_email"
			)

			if (!isValidCustomerEmail) {
				isReadyToSendGiftCard = false
			}

			// #### DENOMINATION

			const isSpecifyingCustomDenomination = this.getStoreValue(
				"is_specifying_custom_denomination"
			)

			// ============
			// CHECK IF USING PREDEFINED DENOMINATION VALUE & IF DENOMINATION SELECTED
			const preDefinedDenomination = this.getStoreValue(
				"denomination_pre_defined"
			)

			if (!isSpecifyingCustomDenomination && !preDefinedDenomination) {
				isReadyToSendGiftCard = false
			}

			// ============
			// CHECK IF USING CUSTOM DENOMINATION VALUE & IF CUSTOM DENOMINATION SELECTED
			const customDenomination = this.getStoreValue("denomination_custom")

			if (isSpecifyingCustomDenomination && !customDenomination) {
				isReadyToSendGiftCard = false
			}

			// ============
			// CHECK IF SETTING EXPIRY DATE & IF VALID END DATE SPECIFIED
			const isSettingExpirationDate = this.getStoreValue(
				"is_setting_gift_card_end_date"
			)
			const isValidEndDate = this.getStoreValue("is_valid_gift_card_end_date")

			if (isSettingExpirationDate && !isValidEndDate) {
				isReadyToSendGiftCard = false
			}

			// +++++++++++++
			return isReadyToSendGiftCard
		},

		// @TODO DELETE WHEN DONE; NOT IN USE FOR NOW
		validateIssueGiftCardDates(
			issueGiftCardStartDateElem,
			issueGiftCardEndDateElem
		) {
			// @TODO CHANGE TO USE ONLY END DATE!
			const giftCardStartDateStr = issueGiftCardStartDateElem.value
			const giftCardEndDateStr = issueGiftCardEndDateElem.value

			// @note get timestamp but if NaN then 'zero'
			const giftCardStartDate =
				this.convertStringDateToTimestamp(giftCardStartDateStr) || 0
			const giftCardEndDate =
				this.convertStringDateToTimestamp(giftCardEndDateStr) || 0
			const giftCardNow = Math.floor(Date.now() / 1000)
			const issueGiftCardDatesErrorStrings =
				this.getIssueGiftCardDatesErrorStrings()

			// @TODO SHOULD WE DO SEPARATE FOR START AND END? THIS IS BECAUSE IN SOME CASES WE NEED SEPARATE MESSAGES FOR THEM
			/*
				a. start date > end date <-invalid
				b. start date == end date <-invalid
				ab: start date >= end date <-invalid #DONE#
				~~~~ PAST DATES ~~~~
				c. start date < now/today date <-invalid
				d. end date < now/today date <-invalid
				cd: @todo check independently since both can be true and need to show errors for each
				~~~~ ONE DATE EMPTY & ONE NOT ~~~~
				e. start date NOT EMPTY & end date EMPTY <- invalid {COVERED BY ab?} #DONE#
				f. start date EMPTY & end date NOT EMPTY <- invalid
				ef: @todo although only one can be true, will need to set errors for each??? NO! WILL JUST SHOW ON THE DATE HAS THE ERROR!
			*/

			// *** VALIDATION START ****

			let isValidDates = true
			let startDateError, endDateError

			// for start date
			const startDateErrorTextProperty = "issue_gift_card_start_date_error_text"
			const startDateErrorFlagProperty = "is_error_issue_gift_card_start_date"
			// -----
			// for end date
			const endDateErrorTextProperty = "issue_gift_card_end_date_error_text"
			const endDateErrorFlagProperty = "is_error_issue_gift_card_end_date"

			// #########
			if (giftCardStartDate === 0 && giftCardEndDate === 0) {
				// **VALID**: DATES NOT IN USE
				// nothing to do
				// isValidDates = true
				// console.log("isValidDates: TRUE - DATES NOT IN USE", isValidDates)
			} else if (giftCardStartDate && !giftCardEndDate) {
				// **INVALID**: START DATE GIVEN BUT END DATE IS EMPTY
				isValidDates = false
				// ~~~~~~~~~~~~
				// get END date 'EMPTY BUT START DATE GIVEN' error message
				// @NOTE: here we show the error on "opposite's date" error message!
				endDateError =
					issueGiftCardDatesErrorStrings.start_date_given_but_no_end_date
			} else if (!giftCardStartDate && giftCardEndDate) {
				// **INVALID**:  START DATE IS EMPTY BUT END DATE GIVEN
				isValidDates = false
				// ~~~~~~~~~~~~
				// get START date 'EMPTY BUT END DATE GIVEN' error message
				// @NOTE: here we show the error on "opposite's date" error message!
				startDateError =
					issueGiftCardDatesErrorStrings.end_date_given_but_no_start_date
			} else if (
				giftCardStartDate < giftCardNow ||
				giftCardEndDate < giftCardNow
			) {
				// **INVALID**: START OR END DATE IS IN THE PAST (LESS THAN NOW)
				// @NOTE: we set error for both since mistake could be in either start or end

				isValidDates = false
				// ------
				if (giftCardStartDate < giftCardNow) {
					// **INVALID**: START DATE IS IN THE PAST (LESS THAN NOW)
					// ~~~~~~~~~~~~
					// get start date 'IN THE PAST' error message
					startDateError =
						issueGiftCardDatesErrorStrings.start_date_is_in_the_past
				}
				if (giftCardEndDate < giftCardNow) {
					// **INVALID**: END DATE IS IN THE PAST (LESS THAN NOW)
					// ~~~~~~~~~~~~
					// get end date 'IN THE PAST' error message
					// @NOTE: we set error for both since mistake could be in either start or end
					endDateError = issueGiftCardDatesErrorStrings.end_date_is_in_the_past
				}
			} else if (giftCardStartDate >= giftCardEndDate) {
				// **INVALID**: START DATE GREATER THAN OR EQUAL TO END DATE
				isValidDates = false
				// ~~~~~~~~~~~~
				// get 'GREATER/EQUAL/LESS THAN' error messages
				// @NOTE: we set error for both since mistake could be in either start or end
				startDateError =
					issueGiftCardDatesErrorStrings.start_date_greater_than_or_equal_to_end_date
				endDateError =
					issueGiftCardDatesErrorStrings.end_date_less_than_or_equal_to_start_date
			}

			// --------

			// ++++++ handle START DATE errors  ++++++

			if (startDateError) {
				// set START DATE error text
				this.setStoreValue(startDateErrorTextProperty, startDateError)
				// set START DATE SHOW ERROR MESSAGE 'TRUE'
				this.setStoreValue(startDateErrorFlagProperty, true)
			} else {
				// clear old error text if needed
				this.setStoreValue(startDateErrorTextProperty, null)
				// set SHOW ERROR MESSAGES FLAGS TO 'FALSE'
				this.setStoreValue(startDateErrorFlagProperty, false)
			}

			// ++++++ handle END DATE errors  ++++++

			if (endDateError) {
				// set END DATE error text
				this.setStoreValue(endDateErrorTextProperty, endDateError)
				// set END DATE SHOW ERROR MESSAGE 'TRUE'
				this.setStoreValue(endDateErrorFlagProperty, true)
			} else {
				// clear old error text if needed
				this.setStoreValue(endDateErrorTextProperty, null)
				// set SHOW ERROR MESSAGES FLAGS TO 'FALSE'
				this.setStoreValue(endDateErrorFlagProperty, false)
			}

			// console.log("isValidDates", isValidDates)

			// -----
			return isValidDates
		},

		// @TODO MOVE TO PWCOMMERCE COMMON SCRIPTS?
		getValidEmailRegexPattern() {
			// @note: compliant with the RFC-2822 spec for email addresses.
			// @credits: @see https://masteringjs.io/tutorials/fundamentals/email-regex
			const pattern =
				/(?:[a-z0-9+!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/i
			return pattern
		},

		convertStringDateToTimestamp(strDate) {
			const dt = Date.parse(strDate)
			return dt / 1000
		},

		// @TODO DELETE IF NOT LONGER IN USE

		handleManualResetManualIssueGiftCard(is_manual_issue_gift_card_modal_open) {
			if (!is_manual_issue_gift_card_modal_open) {
			}
		},

		resetManualIssueGiftCardAndCloseModal() {
			this.handleCloseModal("is_manual_issue_gift_card_modal_open")
			// @TODO: WE NEED TO DO THE SAME IF MODAL CLOSED VIA X IN TOP RIGHT! $watch?
			// also reset several properties related to order action value to null
			// @note: but we leave the action type itself as is!
			// const statusActionsPropertiesToReset = [
			// 	"selected_status_action", // reset selected action
			// 	"selected_order_status_action_payment_amount", // reset order action payment amounts
			// 	"selected_order_status_action_payment_method", // reset order action payment method
			// 	"is_ready_apply_order_status_action", // reset readiness to apply action button
			// ]
			// for (const property of statusActionsPropertiesToReset) {
			// 	this.setStoreValue(property, null)
			// }
			// // clear previous messages MARKUP if present
			// const previousStatusActionMarkup = document.getElementById(
			// 	"pwcommerce_order_status_fetch_markup_response_wrapper"
			// )
			// if (previousStatusActionMarkup) {
			// 	previousStatusActionMarkup.remove()
			// }
		},

		// ~~~~~~~~~~~~~~~~~

		// SPECIAL FOR DISCOUNTS

		// ~~~~~~~~~~~~~~~~~

		handleAddNewDiscount() {
			// open modal for create discount select type!
			this.handleOpenModal("is_create_discount_select_type_modal_open")
		},

		resetDiscountSelectTypeAndCloseModal() {
			this.handleCloseModal("is_create_discount_select_type_modal_open")
			// reset selected values
			this.resetDiscountSelectTypeValues()
		},

		resetDiscountSelectTypeValues() {
			this.setStoreValue("create_discount_type", null)
		},

		handleSetDiscountType(selected_discount_type) {
			this.setStoreValue("create_discount_type", selected_discount_type)
		},

		isSelectedDiscountType(discount_type) {
			const currentDiscountType = this.getStoreValue("create_discount_type")
			return discount_type === currentDiscountType
		},

		// ~~~~~~~~~~~~~~~~~

		handleHideReportLink() {
			const csvReportSummaryAndDownloadWrapperElement = document.getElementById(
				"pwcommerce_download_csv_report_wrapper"
			)
			if (csvReportSummaryAndDownloadWrapperElement) {
				csvReportSummaryAndDownloadWrapperElement.classList.add("hidden")
			}
		},

		// ~~~~~~~~~~~~~~~~~

		// SPECIAL FOR CUSTOMERS

		// ~~~~~~~~~~~~~~~~~

		initOnLoadValues() {
			const emailInputElement = document.getElementById(
				"pwcommerce_add_new_item_email"
			)
			// console.log("initOnLoadValues - emailInputElement", emailInputElement)
			if (emailInputElement) {
				// set initial values
				// for use if page reloaded due to error
				let validationValue = emailInputElement.value
				this.preProcessValidateCustomerEmail(validationValue, true)
			}
			// =========
			const emailConfirmInputElement = document.getElementById(
				"_pwcommerce_add_new_item_email_confirm"
			)
			// console.log(
			// 	"initOnLoadValues - emailConfirmInputElement",
			// 	emailConfirmInputElement
			// )
			if (emailConfirmInputElement) {
				// set initial values
				// for use if page reloaded due to error
				validationValue = emailConfirmInputElement.value
				this.preProcessValidateCustomerEmail(validationValue, false)
			}
			// ----------
			this.checkCustomerEmailsMatch()
		},

		handleCustomerEmailChange(event) {
			const emailInputElement = event.target
			let isMainEmailInput = true
			// -----
			if (emailInputElement.id == "_pwcommerce_add_new_item_email_confirm") {
				// dealing with confirm email input
				isMainEmailInput = false
			}
			const validationValue = emailInputElement.value
			this.preProcessValidateCustomerEmail(validationValue, isMainEmailInput)
			// ----------
			this.checkCustomerEmailsMatch()
		},

		preProcessValidateCustomerEmail(validationValue, isMainEmailInput) {
			// validate customer email
			const isValidEmail = this.validateEmailAddress(validationValue)
			// console.log(
			// 	"preProcessValidateCustomerEmail - isValidEmail",
			// 	isValidEmail
			// )

			const customerEmailProperty = "customer_email"
			const customerConfirmEmailProperty = "customer_confirm_email"

			let emailProperty = customerEmailProperty
			let emailValidProperty = "is_valid_customer_email"
			if (!isMainEmailInput) {
				emailProperty = customerConfirmEmailProperty
				emailValidProperty = "is_valid_customer_confirm_email"
			}

			// @NOTE: setting input value of customer email or customer confirm email for customer
			this.setStoreValue(emailProperty, validationValue)
			// @NOTE: setting validity of customer email or customer confirm email for customer
			this.setStoreValue(emailValidProperty, isValidEmail)
		},

		checkCustomerEmailsMatch() {
			const customerEmailProperty = "customer_email"
			const customerConfirmEmailProperty = "customer_confirm_email"

			// #########
			// check match
			// @note: emails must be valid though!
			const isMatchedCustomerConfirmEmailProperty =
				"is_matched_confirm_customer_email"
			const customerEmail = String(this.getStoreValue(customerEmailProperty))
			const customerConfirmEmail = String(
				this.getStoreValue(customerConfirmEmailProperty)
			)

			const isValidCustomerEmail = this.validateEmailAddress(customerEmail)
			const isValidCustomerConfirmEmail =
				this.validateEmailAddress(customerConfirmEmail)

			if (isValidCustomerEmail && isValidCustomerConfirmEmail) {
				// do the valid emails match?
				if (customerEmail === customerConfirmEmail) {
					this.setStoreValue(isMatchedCustomerConfirmEmailProperty, true)
				} else {
					this.setStoreValue(isMatchedCustomerConfirmEmailProperty, false)
				}
			} else {
				this.setStoreValue(isMatchedCustomerConfirmEmailProperty, false)
			}
		},

		handleEmailCustomer() {
			this.setStoreValue("is_open_email_customer_modal", true)
		},
		/**
		 * Set ready status of customer email or end date for manually issue gift card.
		 * Set in Alpine Store.
		 * Trigger using window event.
		 */
		setIsReadyToEmailCustomer: function (mutation_record) {
			let element, elementValue, notificationType

			// console.log(
			// 	"setIsReadyToEmailCustomer - mutation_record",
			// 	mutation_record
			// )

			if (mutation_record.length) {
				// WE HAVE A MUTATION RECORD
				// -------
				// get the first item
				element = mutation_record[0].target
				// console.log("setIsReadyToEmailCustomer - element", element)
				// is this a parent element?
				if (element.dataset.giftCardMutationObserverElementId) {
					// this is a parent element; the ID of the input element itself is in the data attr of the parent
					// for use with hard to observe items such as jQuery UI Datepicker widget
					// -------------
					// grab the ID of the input element whose value we need
					const inputElementID =
						element.dataset.giftCardMutationObserverElementId
					// get the element itself
					const inputElement = document.getElementById(inputElementID)
					if (inputElement) {
						// if we found the element, make it the element to grab the value from
						element = inputElement
					}
				}

				// set the element value
				elementValue = element.value

				// grab the notification type
				// for now, we have 'email' and 'date' {end date}
				// we'll use this in Alpine to determine how we validate the value
				notificationType =
					element.dataset.giftCardMutationObserverNotificationType
			}

			// send the window event to Alpine.JS
			const eventName =
				"pwcommercemanualissuegiftcardcodemutationobservernotification"
			const eventDetail = {
				type: notificationType,
				value: elementValue,
			}
			// @note: method is in PWCommerceCommonScripts.js
			PWCommerceCommonScripts.dispatchCustomEvent(eventName, eventDetail)
		},

		resetEmailAndCloseModal() {
			// console.log("resetEmailAndCloseModal")
			this.setStoreValue("is_open_email_customer_modal", false)
		},

		// ~~~~~~~~~~~~~~~~~

		// SPECIAL FOR CONFIGURE PWCOMMERCE INSTALLER AND COMPLETE REMOVAL
		/**
		 * Handle changes to selection of an optional feature
		 * Handles both dependencies and tracking of 'adding' or 'removing' features.
		 * For instance, product properties depend on product dimensions.
		 * @param {Event} event
		 * @return void
		 */
		handlePWCommerceOptionalFeatureChange(event) {
			const featureName = event.target.value
			const isCheckedFeature = event.target.checked
			const isInstalledFeature = parseInt(event.target.dataset.isInstalled)
				? true
				: false
			const isDependent = parseInt(event.target.dataset.isDependent)
				? true
				: false
			const isAOneWayDependency = parseInt(
				event.target.dataset.isOneWayDependency
			)
				? true
				: false

			// ADDITION/REMOVAL TRACKING
			if (isCheckedFeature) {
				if (!isInstalledFeature) {
					// checked + not installed = 'add to ADDITION pool'
					this.setNewOptionalFeatureToAdd(featureName)
				} else {
					// checked + is installed = 'remove from REMOVAL pool'
					this.setExistingOptionalFeatureToRemove(featureName, true)
				}
			} else {
				if (!isInstalledFeature) {
					// unchecked + not installed = 'remove from ADDITION pool'
					this.setNewOptionalFeatureToAdd(featureName, true)
				} else {
					// unchecked + is installed = 'add to REMOVAL pool'
					this.setExistingOptionalFeatureToRemove(featureName)
				}
			}

			// ------------------
			// DEPENDENCIES (two way)
			if (isDependent) {
				// get object with all install dependencies values
				const dependencies = this.getPWCommerceConfigureInstallDependencies()
				// get the name of the dependency for the current dependent @note: @todo: for now, only one dependency per dependent!
				const dependencyFeatureName = dependencies[featureName]
				// get the checkbox of the dependency
				const dependencyFeatureElement = this.$refs[dependencyFeatureName]
				// check/uncheck the dependency to match dependent checked status
				dependencyFeatureElement.checked = isCheckedFeature
			}
			// ONE WAY DEPENDENCIES
			if (isAOneWayDependency) {
				// get object with all install one-way dependencies values
				const oneWayDependencies =
					this.getPWCommerceConfigureInstallOneWayDependencies()
				// get the name of the dependent for the current one-way dependency @note: @todo: for now, only one dependency per dependent!
				const oneWayDependentFeatureName = oneWayDependencies[featureName]
				// get the checkbox of the one-way dependency
				const oneWayDependentFeatureElement =
					this.$refs[oneWayDependentFeatureName]

				if (isCheckedFeature) {
					// if dependency is checked
					// ------
					// 'enable' one-way dependent
					oneWayDependentFeatureElement.disabled = false
					// @note: checked status of dependency? nothing to do
				} else {
					// 'disable' one-way dependent
					oneWayDependentFeatureElement.disabled = true
					// also uncheck it
					oneWayDependentFeatureElement.checked = isCheckedFeature
				}
			}
		},

		handlePWCommerceOtherOptionalSettingsChange(event) {
			const optionalSettingName = event.target.dataset.optionalSettingName
			const otherOptionalSettingsLabels =
				this.getPWCommerceConfigureInstallOtherOptionalSettingsLabels()

			const isCheckedOptionalSetting = event.target.checked
			// TODO NEEDS CHANGIND!
			const isInstalledOptionalSetting = parseInt(
				event.target.dataset.isInstalled
			)
				? true
				: false

			// ADDITION/REMOVAL TRACKING
			if (isCheckedOptionalSetting) {
				if (!isInstalledOptionalSetting) {
					// checked + not installed = 'add to ADDITION pool'
					this.setNewOtherOptionalSettingsToAdd(optionalSettingName)
				} else {
					// checked + is installed = 'remove from REMOVAL pool'
					this.setExistingOtherOptionalSettingsToRemove(
						optionalSettingName,
						true
					)
				}
			} else {
				if (!isInstalledOptionalSetting) {
					// unchecked + not installed = 'remove from ADDITION pool'
					this.setNewOtherOptionalSettingsToAdd(optionalSettingName, true)
				} else {
					// unchecked + is installed = 'add to REMOVAL pool'
					this.setExistingOtherOptionalSettingsToRemove(optionalSettingName)
				}
			}
		},

		handlePWCommerceConfirmRunInstaller(event) {
			if (event.detail) {
				// open confirm dialog for run configure install
				this.setStoreValue("is_confirm_run_configure_install_modal_open", true)
			}
		},

		handleCustomShopRootPageTreeManagementChange(event) {
			const radioValue = event.detail
			this.setStoreValue(
				"custom_shop_root_page_children_page_tree_management",
				radioValue
			)
		},

		handlePWCommerceApplyRunInstaller(event) {
			if (event.detail) {
				// show spinner as installer/remover is running
				this.setStoreValue("is_show_run_installer_spinner", true)
			}
		},

		resetConfirmConfigureInstallAndClose() {
			this.handleCloseModal("is_confirm_run_configure_install_modal_open")
			// reset (hide) spinner on cancel run installer
			this.setStoreValue("is_show_run_installer_spinner", false)
		},

		getPWCommerceConfigureInstallDependencies() {
			// @NOTE @TODO - FOR NOW, WE DON'T SAVE THIS TO STORE; JUST FETCH IT DIRECTLY FROM THE DOM as it is a one-off variable, used only during configuring installation
			return ProcessWire.config.PWCommerceAdminRenderInstaller
				.configure_install_dependencies
		},

		getPWCommerceConfigureInstallOneWayDependencies() {
			// @NOTE @TODO - FOR NOW, WE DON'T SAVE THIS TO STORE; JUST FETCH IT DIRECTLY FROM THE DOM as it is a one-off variable, used only during configuring installation
			return ProcessWire.config.PWCommerceAdminRenderInstaller
				.configure_install_one_way_dependencies
		},

		getPWCommerceConfigureInstallOptionalFeaturesLabels() {
			// @NOTE @TODO - FOR NOW, WE DON'T SAVE THIS TO STORE; JUST FETCH IT DIRECTLY FROM THE DOM as it is a one-off variable, used only during configuring installation
			return ProcessWire.config.PWCommerceAdminRenderInstaller
				.optional_features_labels
		},

		getPWCommerceConfigureInstallOtherOptionalSettingsLabels() {
			return ProcessWire.config.PWCommerceAdminRenderInstaller
				.other_optional_setting_labels
		},

		getPWCommerceConfigureInstallNoOptionalFeatureSelectionString() {
			// @NOTE @TODO - FOR NOW, WE DON'T SAVE THIS TO STORE; JUST FETCH IT DIRECTLY FROM THE DOM as it is a one-off variable, used only during configuring installation
			return ProcessWire.config.PWCommerceAdminRenderInstaller
				.no_install_or_uninstall_text
		},

		getListOfNewFeaturesToAdd() {
			return this.getListOfOptionalFeaturesToAction(
				"new_optional_features_to_add"
			)
		},

		getListOfExistingFeaturesToRemove() {
			return this.getListOfOptionalFeaturesToAction(
				"existing_optional_features_to_remove"
			)
		},

		getListOfOptionalFeaturesToAction(action) {
			const optionalFeaturesLabels =
				this.getPWCommerceConfigureInstallOptionalFeaturesLabels()

			const optionalFeaturesToActionLabels = []
			let optionalFeaturesToActionLabelsString = ""
			const optionalFeaturesToAction = this.getStoreValue(action)
			for (const featureName of optionalFeaturesToAction) {
				optionalFeaturesToActionLabels.push(optionalFeaturesLabels[featureName])
			}
			if (optionalFeaturesToActionLabels.length) {
				optionalFeaturesToActionLabelsString =
					optionalFeaturesToActionLabels.join(", ")
			} else {
				// display text 'none' if no selections to action
				optionalFeaturesToActionLabelsString =
					this.getPWCommerceConfigureInstallNoOptionalFeatureSelectionString()
			}
			// ----------
			return optionalFeaturesToActionLabelsString
		},

		getListOfNewOtherOptionalSettingsToAdd() {
			return this.getListOfOtherOptionalSettingsToAction(
				"new_other_optional_settings_to_add"
			)
		},

		getListOfExistingOtherOptionalSettingsToRemove() {
			return this.getListOfOtherOptionalSettingsToAction(
				"existing_other_optional_settings_to_remove"
			)
		},

		getListOfOtherOptionalSettingsToAction(action) {
			const otherOptionalSettingsLabels =
				this.getPWCommerceConfigureInstallOtherOptionalSettingsLabels()

			const otherOptionalSettingsToActionLabels = []
			let otherOptionalSettingsToActionLabelsString = ""
			const otherOptionalSettingsToAction = this.getStoreValue(action)

			for (const optionalSettingName of otherOptionalSettingsToAction) {
				otherOptionalSettingsToActionLabels.push(
					otherOptionalSettingsLabels[optionalSettingName]
				)
			}
			if (otherOptionalSettingsToActionLabels.length) {
				otherOptionalSettingsToActionLabelsString =
					otherOptionalSettingsToActionLabels.join(", ")
			} else {
				// display text 'none' if no selections to action
				otherOptionalSettingsToActionLabelsString =
					this.getPWCommerceConfigureInstallNoOptionalFeatureSelectionString()
			}
			// ----------
			return otherOptionalSettingsToActionLabelsString
		},

		setNewOptionalFeatureToAdd(optionalFeature, isRemoveFromPool = false) {
			let newOptionalFeaturesToAdd = [
				...this.getStoreValue("new_optional_features_to_add"),
			]

			// check if adding or removing the NEW feature
			if (isRemoveFromPool) {
				// REMOVE OPTIONAL FEATURE FROM ADDITION POOL
				newOptionalFeaturesToAdd = newOptionalFeaturesToAdd.filter(
					(item) => item !== optionalFeature
				)
			} else {
				// ADD OPTIONAL FEATURE TO ADDITION POOL
				if (!newOptionalFeaturesToAdd.includes(optionalFeature)) {
					newOptionalFeaturesToAdd.push(optionalFeature)
				}
			}
			// -----------
			// set back to store
			this.setStoreValue(
				"new_optional_features_to_add",
				newOptionalFeaturesToAdd
			)
		},

		setExistingOptionalFeatureToRemove(
			optionalFeature,
			isRemoveFromPool = false
		) {
			let existingOptionalFeaturesToRemove = [
				...this.getStoreValue("existing_optional_features_to_remove"),
			]

			// check if adding or removing the EXISTING feature
			if (isRemoveFromPool) {
				// REMOVE OPTIONAL FEATURE FROM REMOVAL POOL
				existingOptionalFeaturesToRemove =
					existingOptionalFeaturesToRemove.filter(
						(item) => item !== optionalFeature
					)
			} else {
				// ADD OPTIONAL FEATURE TO REMOVAL POOL
				if (!existingOptionalFeaturesToRemove.includes(optionalFeature)) {
					existingOptionalFeaturesToRemove.push(optionalFeature)
				}
			}
			// -----------
			// set back to store
			this.setStoreValue(
				"existing_optional_features_to_remove",
				existingOptionalFeaturesToRemove
			)
		},

		setNewOtherOptionalSettingsToAdd(
			otherOptionalSetting,
			isRemoveFromPool = false
		) {
			let newOtherOptionalSettingsToAdd = [
				...this.getStoreValue("new_other_optional_settings_to_add"),
			]

			// check if adding or removing the NEW other optional setting
			if (isRemoveFromPool) {
				// REMOVE OTHER OPTIONAL SETTING FROM ADDITION POOL
				newOtherOptionalSettingsToAdd = newOtherOptionalSettingsToAdd.filter(
					(item) => item !== otherOptionalSetting
				)
			} else {
				// ADD OTHER OPTIONAL SETTING TO ADDITION POOL
				if (!newOtherOptionalSettingsToAdd.includes(otherOptionalSetting)) {
					newOtherOptionalSettingsToAdd.push(otherOptionalSetting)
				}
			}
			// -----------
			// set back to store
			this.setStoreValue(
				"new_other_optional_settings_to_add",
				newOtherOptionalSettingsToAdd
			)
		},

		setExistingOtherOptionalSettingsToRemove(
			otherOptionalSetting,
			isRemoveFromPool = false
		) {
			let existingOtherOptionalSettingsToRemove = [
				...this.getStoreValue("existing_other_optional_settings_to_remove"),
			]

			// check if adding or removing the EXISTING other optional setting
			if (isRemoveFromPool) {
				// REMOVE OTHER OPTIONAL SETTING FROM REMOVAL POOL
				existingOtherOptionalSettingsToRemove =
					existingOtherOptionalSettingsToRemove.filter(
						(item) => item !== otherOptionalSetting
					)
			} else {
				// ADD OTHER OPTIONAL SETTING TO REMOVAL POOL
				if (
					!existingOtherOptionalSettingsToRemove.includes(otherOptionalSetting)
				) {
					existingOtherOptionalSettingsToRemove.push(otherOptionalSetting)
				}
			}
			// -----------
			// set back to store
			this.setStoreValue(
				"existing_other_optional_settings_to_remove",
				existingOtherOptionalSettingsToRemove
			)
		},

		// ## REMOVAL ##

		handlePWCommerceConfirmCompleteRemoval(event) {
			if (event.detail) {
				// open confirm dialog for run complete uninstall
				this.setStoreValue("is_confirm_run_complete_removal_modal_open", true)
			}
		},

		resetConfirmCompleteRemovalAndClose() {
			this.handleCloseModal("is_confirm_run_complete_removal_modal_open")
			// reset (hide) spinner on cancel run remover
			this.setStoreValue("is_show_run_installer_spinner", false)
		},

		// ##############################

		// ~~~~~~~~~~~~~~~~

		/**
		 * Set a store property value.
		 * @param any value Value to set in store.
		 * @return {void}.
		 */
		setStoreValue(property, value) {
			this.$store.ProcessPWCommerceStore[property] = value
		},

		// -----------

		toggleContinentAccordionOpen(continent_id) {
			// get the current open_continent_accordions
			let openContinentAccordions = [
				...this.getStoreValue("open_continent_accordions"),
			]
			// if already open, then close
			if (openContinentAccordions.includes(continent_id)) {
				// remove from list of opened
				openContinentAccordions = openContinentAccordions.filter(
					(item) => item.toLowerCase() !== continent_id.toLowerCase()
				)
			} else {
				// else open accordion (add to list of opened)
				openContinentAccordions.push(continent_id)
			}
			// ------
			// set back opened accordion value to store
			this.setStoreValue("open_continent_accordions", openContinentAccordions)
		},

		// ------
		/**
		 * Set all the continents and countries data.
		 *
		 * @return {void}.
		 */
		setAllContinentsAndCountriesData() {
			const countriesAndContinentsData =
				this.getProcessWireContinentsAndCountriesConfig()
			this.setStoreValue("continents", countriesAndContinentsData.continents)
			this.setStoreValue("countries", countriesAndContinentsData.countries)
			this.setStoreValue(
				"already_added_countries",
				countriesAndContinentsData.already_added_countries
			)
			this.setStoreValue("flags_url", countriesAndContinentsData.flags_url)
		},

		setNoCountryFoundForContinent(continent_id, countries_found_count) {
			// no_continent_country_found_after_filter
			let noContinentCountryFound = [
				...this.getStoreValue("no_continent_country_found_after_filter"),
			]
			if (!countries_found_count) {
				noContinentCountryFound.push(continent_id)
			} else {
				noContinentCountryFound = noContinentCountryFound.filter(
					(continent) => continent !== continent_id
				)
			}
			//--------
			this.setStoreValue(
				"no_continent_country_found_after_filter",
				noContinentCountryFound
			)
		},

		setTotalSelectedCountries() {
			// @todo: could refactor select checkboxes to be setting in store instead
			const allContinentCountriesCheckedCheckboxesElements =
				document.querySelectorAll("input.pwcommerce_add_new_countries:checked")
			// set the count to the store
			this.setStoreValue(
				"total_selected_countries",
				allContinentCountriesCheckedCheckboxesElements.length
			)
		},

		// ~~~~~~~~~~~~~~~

		/**
		 * Get the the whole ProcessPWCommerceStore store.
		 * @returns {object}
		 */
		getStore() {
			return this.$store.ProcessPWCommerceStore
		},

		/**
		 * Get the value of a given store property.
		 * @param string property Property in store whose value to return
		 * @returns {any}
		 */
		getStoreValue(property) {
			return this.$store.ProcessPWCommerceStore[property]
		},

		// ----

		/**
		 *Get the ProcessWire config sent for continents and countries.
		 * @return array.
		 */
		getProcessWireContinentsAndCountriesConfig() {
			return ProcessWire.config.PWCommerceProcessRenderTaxRates.geographical_data
		},

		getAllContinents() {
			return this.getStoreValue("continents")
		},

		getContinentCountries(continent_id) {
			const allCountries = this.getAllCountries()
			const continentCountries = allCountries.filter(
				(country) =>
					country.continent.toLowerCase() === continent_id.toLowerCase()
			)
			// for checking if filtering returned 0 countries
			this.setNoCountryFoundForContinent(
				continent_id,
				continentCountries.length
			)
			//-------
			return continentCountries
		},

		getAllCountries() {
			const searchFilter = this.getStoreValue("search_country_query").trim()
			const allCountries = this.getStoreValue("countries")
			if (!searchFilter) {
				// return all countries
				return allCountries
			} else {
				// return filtered countries
				return allCountries.filter((country) =>
					country.name.toLowerCase().includes(searchFilter.toLowerCase())
				)
			}
		},

		getCountryFlag(country_id) {
			const flagsURL = this.getStoreValue("flags_url")
			const flagURL = `${flagsURL}${country_id.toLowerCase()}.svg`
			return flagURL
		},

		// ---------

		getContinentAccordionStyles(continent_id) {
			const currentOpenContinentAccordions = this.getStoreValue(
				"open_continent_accordions"
			)
			const styles = currentOpenContinentAccordions.includes(continent_id)
				? `max-height: ${this.$refs[continent_id].scrollHeight}px`
				: ``
			return styles
		},

		// get dynamic classes to apply to continent accordion <li>
		getContinentClasses(continent_id) {
			let classes = ""
			// if continent accordion is open: add this class
			if (this.isContinentAccordionOpen(continent_id)) {
				classes += " pwcommerce_continent_accordion_open"
			}
			// if no continent country found due to filtering, hide it by addind this class
			if (this.isHideContinent(continent_id)) {
				classes += " hidden"
			}
			return classes
		},

		getTotalSelectedCountries() {
			return this.getStoreValue("total_selected_countries")
		},

		getSearchCountryResultsCount() {
			return this.getStoreValue("search_country_results_count")
		},
	}))
})
