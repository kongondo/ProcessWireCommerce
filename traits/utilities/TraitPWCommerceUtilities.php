<?php

namespace ProcessWire;

/**
 * PWCommerce: Utilities.
 *
 * General purpose utilities class for carrying out various repetitive actions in PWCommerce, e.g. get date formats, etc.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceUtilities for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

// =========
// IMPORT TRAITS FILES
$traitsFiles = [
	'TraitPWCommerceUtilitiesAddons',
	'TraitPWCommerceUtilitiesAssets',
	'TraitPWCommerceUtilitiesCheckout',
	'TraitPWCommerceUtilitiesCountry',
	'TraitPWCommerceUtilitiesDateTime',
	'TraitPWCommerceUtilitiesDiscount',
	'TraitPWCommerceUtilitiesFindAnything',
	'TraitPWCommerceUtilitiesGeneralSettings',
	'TraitPWCommerceUtilitiesInstall',
	'TraitPWCommerceUtilitiesMessage',
	'TraitPWCommerceUtilitiesOrder',
	'TraitPWCommerceUtilitiesOrderLineItem',
	'TraitPWCommerceUtilitiesOrderStatus',
	'TraitPWCommerceUtilitiesProduct',
	'TraitPWCommerceUtilitiesRuntimeChecks',
];

foreach ($traitsFiles as $traitFileName) {
	require_once __DIR__ . "/{$traitFileName}.php";
}


trait TraitPWCommerceUtilities {

	// =============
	// TRAITS

	use TraitPWCommerceUtilitiesAddons, TraitPWCommerceUtilitiesAssets, TraitPWCommerceUtilitiesCheckout, TraitPWCommerceUtilitiesCountry, TraitPWCommerceUtilitiesDateTime, TraitPWCommerceUtilitiesDiscount, TraitPWCommerceUtilitiesFindAnything, TraitPWCommerceUtilitiesGeneralSettings, TraitPWCommerceUtilitiesInstall, TraitPWCommerceUtilitiesMessage, TraitPWCommerceUtilitiesOrder, TraitPWCommerceUtilitiesOrderLineItem, TraitPWCommerceUtilitiesOrderStatus, TraitPWCommerceUtilitiesProduct, TraitPWCommerceUtilitiesRuntimeChecks;


	private $options = [];
	// ==============
	// // tax rate at location shop is based
	// private $shopHomeTaxRate;
	// ---------------------------------
	// temporary for order calculable values usage
	private $order;
	// private $orderPage;
	private $orderLineItems;
	// private $shippingZone;
	// private $shippingZoneRates;
	// private $isForLiveShippingRateCalculation = false;
	// ---------------------------------
	// temporary for order line item calculable values usage
	private $orderLineItem;
	private $productOrVariantPage;
	// private $shippingCountry; // @note: shared with order as well! but different instance!
	private $isProcessTruePrice;
	// // --------------
	// private $isChargeTaxesManualExemption;
	// private $isCustomerTaxExempt;
	// // this takes into account above two exemptions + product taxable setting + eu digital goods vat taxes
	// private $isOrderLineItemTaxable;
	// // takes into account category tax override on line item
	// private $orderLineItemTaxRate;
	// is shipping chargable on this order?
	// private $isShippingApplicable;
	// --------
	private $allOrderStatusesDefinitions;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INIT ~~~~~~~~~~~~~~~~~~

	// /**
  *   construct.
  *
  * @param mixed $options
  * @return mixed
  */
 public function __construct($options = null) {
	/**
	 *   init Utilities.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __initUtilities($options = null) {
		// parent::__construct();
		// TODO????
		if (is_array($options)) {
			$this->options = $options;
		}

		//-----------
		// ORDER STATUSES
		// @note: we don't want this called until install is finished
		// so we check if table exists first
		$pwcommerceOrderStatusesCustomTableName = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE_NAME;
		if ($this->isExistPWCommerceCustomTable($pwcommerceOrderStatusesCustomTableName)) {
			$this->allOrderStatusesDefinitions = $this->getAllOrderStatusDefinitionsFromDatabase();
		}
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ DEBUG UTILS ~~~~~~~~~~~~~~~~~~

	/**
	 * Formatted Print R.
	 *
	 * @param mixed $printName
	 * @param mixed $printValue
	 * @return mixed
	 */
	public function formattedPrintR($printName, $printValue) {
		// FOR USE WHERE WE CAN'T GET TRACYDEBUGGER
		if ($this->config->ajax) {
			// DON'T INTERFERE WITH AJAX RESPONSES!
			return;
		}
		// =========
		echo "<pre>{$printName}: ";
		print_r($printValue);
		echo "</pre><hr>";
	}

	/**
	 * Formatted Var Dump.
	 *
	 * @param mixed $dumpName
	 * @param mixed $dumpValue
	 * @return mixed
	 */
	public function formattedVarDump($dumpName, $dumpValue) {
		// FOR USE WHERE WE CAN'T GET TRACYDEBUGGER
		if ($this->config->ajax) {
			// DON'T INTERFERE WITH AJAX RESPONSES!
			return;
		}
		// =========
		echo "<pre>{$dumpName}: ";
		var_dump($dumpValue);
		echo "</pre><hr>";
	}

	/**
	 * Pwcommerce Log.
	 *
	 * @param mixed $logName
	 * @param mixed $logValue
	 * @return mixed
	 */
	public function pwcommerceLog($logName, $logValue) {
		$log = $this->wire('log');
		$logNamePrefix = "pwcommerce_log_";
		$logName = $logNamePrefix . $logName;
		$log->save($logName, $logValue);
	}
}
