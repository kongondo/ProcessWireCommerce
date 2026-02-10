<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsOrders
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ORDERS TAB  ~~~~~~~~~~~~~~~~~~

	/**
	 * Get Orders Tab.
	 *
	 * @return mixed
	 */
	private function getOrdersTab() {

		// quick filters thresholds
		// LEAST SALES THRESHOLD (DEFAULT 10)
		$leastSalesThresholdValue = (int) $this->getGeneralSettingValue('order_least_sales_threshold');
		if (empty($leastSalesThresholdValue)) {
			// use default value: 10
			$leastSalesThresholdValue = 10;
		}
		// MOST SALES THRESHOLD (DEFAULT 10)
		$mostSalesThresholdValue = (int) $this->getGeneralSettingValue('order_most_sales_threshold');
		if (empty($mostSalesThresholdValue)) {
			// use default value: 10
			$mostSalesThresholdValue = 10;
		}

		//------------------
		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_general_settings_orders_tab',
				'title' => $this->_('Orders'),
			],

			'inputfields' => [
				# ***************
				// ORDER PRE/SUFFIX
				// 'order_prefix_suffix_info' => [
				// 	'type' => 'markup',
				// 	'label' => $this->_('Order Number Prefix/Suffix'),
				// 	'collapsed' => Inputfield::collapsedNever,
				// 	// 'wrapClass' => true,
				// 	// 'wrapper_classes' => 'pwcommerce_no_outline',
				// 	'columnWidth' => 100,
				// 	'value' => $this->_('Prefix and/or suffix for customer order number.'),
				// ],

				// order prefix
				'order_prefix' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_order_prefix',
					'label' => $this->_('Order ID Prefix (optional) '),
					'notes' => $this->_("While you cannot change order numbers, you can add a prefix to create IDs such as 'IB2002'."),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('order_prefix'),
				],

				// order suffix
				'order_suffix' => [
					'type' => 'text',
					'name' => 'pwcommerce_general_settings_order_suffix',
					'label' => $this->_('Order ID Suffix (optional)'),
					'notes' => $this->_("While you cannot change order numbers, you can add a suffix to create IDs such as '1234-C'."),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('order_suffix'),
				],
				# ***************
				// QUICKFILTERS: ORDER THRESHOLDS
				// 'order_quick_filters_thresholds' => [
				// 	'type' => 'markup',
				// 	'label' => $this->_('Order Quick Filters'),
				// 	'collapsed' => Inputfield::collapsedNever,
				// 	// 'wrapClass' => true,
				// 	// 'wrapper_classes' => 'pwcommerce_no_outline',
				// 	'columnWidth' => 100,
				// 	'value' => $this->_('Values for order quick filters.'),
				// ],
				// lows sales threshold (quantity)
				'quick_filters_least_sales_threshold' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_order_least_sales_threshold',
					'min' => 0,
					'step' => 1,
					'label' => $this->_('Least Sales Threshold'),
					'description' => $this->_("Enter the quantity value below which to show as least sold items for use by quick filters."),
					'notes' => $this->_("Default is bottom 10 quantity of items sold."),
					'columnWidth' => 50,
					'value' => $leastSalesThresholdValue,
				],
				// most sales threshold (quantity)
				'quick_filters_most_sales_threshold' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_order_most_sales_threshold',
					'min' => 1,// @note!
					'step' => 1,
					'label' => $this->_('Most Sales Threshold'),
					'description' => $this->_("Enter the quantity value above which to show as most sold items for use by quick filters."),
					'notes' => $this->_("Default is top 10 quantity of items sold."),
					'columnWidth' => 50,
					'value' => $mostSalesThresholdValue,
				],

			],

		];

		return $tabAndContents;
	}

}
