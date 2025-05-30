<?php

namespace ProcessWire;

/**
 * PWCommerce: Actions
 *
 * General purpose class for carrying out various actions in PWCommerce, e.g. create or delete pages, variants, etc.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceActions for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

// =========
// IMPORT TRAITS FILES
$traitsFiles = ["TraitPWCommerceActions"];

foreach ($traitsFiles as $traitFileName) {
	require_once __DIR__ . "/../../traits/actions/{$traitFileName}.php";
}

class PWCommerceActions extends WireData
{

	// =============
	// TRAITS

	use TraitPWCommerceActions;

	private $options;
	private $action;
	private $actionContext;
	private $items; // items to action for bulk edit
	private $actionInput; // just to pass Input around conveniently

	private $reportStart;
	private $reportEnd;
	private $reportIncludePendingAndPartialPayments;
	private $reportType;
	private $reportDownloadDelimiter;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INIT ~~~~~~~~~~~~~~~~~~

	public function __construct($options = null) {
		parent::__construct();
		if (is_array($options)) {
			$this->options = $options;
		}

		// set action and action context if applicable
		// e.g. products, categories, etc
		if (!empty($options['action_context'])) {
			$this->actionContext = $options['action_context'];
		}
		// e.g. publish, unpublish, trash, etc
		// if (!empty($options['action'])) {
		//     $this->action = $options['action'];
		// }

	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PRE-PROCESS ~~~~~~~~~~~~~~~~~~

	public function preProcessAction($input) {
		$result = [
			'notice' => $this->_('Error encountered. No action was taken.'),
			'notice_type' => 'error',
		];

		// if no action context, return
		if (!$this->actionContext) {

			return $result;
		}

		// TODO: HERE NEED TO DETERMINE IF SAVING TAX SETTINGS, GENERAL SETTINGS, CHECKOUT SETTINGS,  PAYMENT PROVIDER, ETC!

		//----------
		// DETERMINE THE ACTION
		$actionResult = null;
		$actionContext = $this->actionContext;
		// @note: just for convenience
		$this->actionInput = $input;
		// -----------
		if ($actionContext === 'discounts') {
			// create new discount of this type
			$actionResult = $this->actionPreProcessDiscount();
		}

		//-------------
		// set result/response as established by action method
		if (!empty($actionResult)) {
			$result = $actionResult;
		}

		//-------------
		return $result;
	}

}