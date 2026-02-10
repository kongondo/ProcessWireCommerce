<?php

namespace ProcessWire;

/**
 * PWCommerce: Process Render Gift Cards
 *
 * Class to render content for PWCommerce Process Module executePaymentProviders().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceProcessRenderGiftCards for PWCommerce
 * Copyright (C) 2023 by Francis Otieno
 * MIT License
 *
 */

// =========
// IMPORT TRAITS FILES
$traitsFiles = [
	'TraitPWCommerceProcessQuickFilters'
];

foreach ($traitsFiles as $traitFileName) {
	require_once __DIR__ . "/../../traits/{$traitFileName}.php";
}

class PWCommerceProcessRenderGiftCards extends WireData
{



	use TraitPWCommerceProcessQuickFilters;


	private $options = [];

	private $shopGeneralSettings;
	# ----------
	// the ALPINE JS store used by this Class
	private $xstoreRenderGiftCards;
	// the full prefix to the ALPINE JS store used by this Class
	private $xstore;
	private $ajaxPostURL;
	private $issueGiftCardLink;
	private $shopCurrencySymbolString = "";
	// current gift card page
	public $giftCardPage;


	/**
	 *   construct.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __construct($options = null) {
		parent::__construct();
		// TODO????
		if (is_array($options)) {
			$this->options = $options;
		}



		//-----------


		// TODO - WIP - FROM FORMER VIEW FOR ISSUE GCs IN edit single GCP
		// ==================
		// -------
		// ==================
		$this->xstoreRenderGiftCards = 'ProcessPWCommerceStore';
		// i.e., '$store.ProcessPWCommerceStore'
		$this->xstore = "\$store.{$this->xstoreRenderGiftCards}";
		$this->ajaxPostURL = $this->wire('config')->urls->admin . PwCommerce::PWCOMMERCE_SHOP_PAGE_IN_ADMIN_NAME . '/ajax/';

		// ----------
		$this->issueGiftCardLink = $this->_('Issue gift card');


		// ---------
		$this->shopGeneralSettings = $this->pwcommerce->getshopGeneralSettings();

		$shopCurrencySymbolString = $this->pwcommerce->renderShopCurrencySymbolString();
		if (strlen($shopCurrencySymbolString)) {
			$this->shopCurrencySymbolString = " " . $shopCurrencySymbolString;
		}
	}

	/**
	 * Render Results.
	 *
	 * @param mixed $selector
	 * @return string|mixed
	 */
	public function renderResults($selector = null) {

		$input = $this->wire('input');
		if ($this->wire('config')->ajax && (int) $input->pwcommerce_manual_issue_gift_card_fetch_code) {
			// GET REQUEST FOR NEW, UNIQUE CODE TO MANUALLY ISSUE A GIFT CARD
			$out = $this->handleAjaxManualIssugeGiftCardCode();
			return $out;
		}

		# ++++++++++++++++++++++++

		// enforce to string for strpos for PHP 8+
		$selector = strval($selector);

		//-----------------
		// FORCE DEFAULT LIMIT IF NO USER LIMIT SET
		if (strpos($selector, 'limit=') === false) {
			$limit = 10;
			$selector = rtrim("limit={$limit}," . $selector, ",");
		}

		//------------
		// FORCE TEMPLATE TO MATCH PWCOMMERCE GIFT CARDS ONLY + INCLUDE ALL + EXLUDE TRASH
		$selector .= ",template=" . PwCommerce::GIFT_CARD_TEMPLATE_NAME . ",include=all,status<" . Page::statusTrash;
		//------------
		// ADD START IF APPLICABLE (ajax pagination)
		$classOptions = $this->options;
		if (!empty($classOptions['selector_start'])) {
			$start = (int) $classOptions['selector_start'];

			$selector .= ",start={$start}";
		}

		//-----------------------

		// TODO: work on this! e.g. inlude all???

		// TODO: for future: need to add variants! i.e. their child pages, if applicable - same for orders - need order items!

		$pages = $this->wire('pages')->find($selector);

		//-----------------

		// BUILD FINAL MARKUP TO RETURN TO ProcessPwCommerce::pagesHandler()
		// @note: important: don't remove the class 'pwcommerce_inputfield_selector'! we need it for htmx (hx-include)
		$out =
			"<div id='pwcommerce_bulk_edit_custom_lister' class='pwcommerce_inputfield_selector pwcommerce_show_highlight mt-5'>" .
			# TODO DELETE SINCE NOT POSSIBLE TO ADD GIFT CARD!
			// BULK EDIT ACTIONS
			$this->getBulkEditActionsPanel() .
			// PAGINATION STRING (e.g. 1 of 25)
			"<h3 id='pwcommerce_bulk_edit_custom_lister_pagination_string'>" . $pages->getPaginationString('') . "</h3>" .
			// TABULATED RESULTS (if pages found, else 'none found' message is rendered)
			$this->getResultsTable($pages) .
			// HIDDEN INPUT FOR HTMX
			// set the context for differentiation when in ajax page
			"<input type='hidden' value='gift-cards' name='pwcommerce_inputfield_selector_context'>" .
			// PAGINATION (render the pagination navigation)
			$this->pwcommerce->getPagination($pages, $this->paginationOptions()) .
			//---------------
			"</div>";

		return $out;
	}


	/**
	 * Get the options for building the form to manually issue a Gift Card for use in ProcessPWCommerce.
	 *
	 * @return mixed
	 */
	public function getAddNewItemOptions() {
		return [
			'label' => $this->_('Gift Card Title'),
			'headline' => $this->_('Manually Issue Gift Card'),
		];
	}

	/**
	 * Pagination Options.
	 *
	 * @return mixed
	 */
	public function paginationOptions() {
		$adminURL = '';
		// TODO: WE WILL ALWAYS HAVE AN ADMIN URL, BUT JUST SANITY CHECK!
		$classOptions = $this->options;
		if (!empty($classOptions['admin_url'])) {
			$adminURL = $classOptions['admin_url'];
		}
		//------------
		$paginationOptions = ['base_url' => $adminURL . 'gift-cards/', 'ajax_post_url' => $adminURL . 'ajax/'];
		return $paginationOptions;
	}

	/**
	 * Get Custom Lister Settings.
	 *
	 * @return mixed
	 */
	public function getCustomListerSettings() {
		return [
			'label' => $this->_('Filter Gift Cards'),
			'inputfield_selector' => [
				'initValue' => "template=" . PwCommerce::GIFT_CARD_TEMPLATE_NAME,
				'initTemplate' => PwCommerce::GIFT_CARD_TEMPLATE_NAME,
				'showFieldLabels' => true,
			],

			// TODO; add columns!!!!
		];
	}

	/**
	 * Get Results Table Headers.
	 *
	 * @return mixed
	 */
	private function getResultsTableHeaders() {
		// TODO: DO WE USE TW CLASSES HERE?
		$selectAllCheckboxName = "pwcommerce_bulk_edit_selected_items_all";
		$xref = 'pwcommerce_bulk_edit_selected_items_all';
		return [
			// SELECT ALL CHECKBOX
			$this->getBulkEditCheckbox('all', $selectAllCheckboxName, $xref),
			// CODE
			[$this->_('Code'), 'pwcommerce_gift_cards_table_code'],
			// CUSTOMER
			// TODO: make these classes generic? e.g. for th percent width?
			[$this->_('Customer'), 'pwcommerce_gift_cards_table_customer'],
			// END DATE/EXPIRE
			[$this->_('Expire'), 'pwcommerce_gift_cards_table_expire'],
			// BALANCE (BALANCE/DENOMINATION)
			[$this->_('Balance'), 'pwcommerce_gift_cards_table_balance'],
			// USAGE
			// [$this->_('Usage'), 'pwcommerce_gift_cards_table_usage'],
			// LAST USED DATE? TODO?
			// [$this->_('Usage'), 'pwcommerce_gift_cards_table_usage'],
		];
	}

	//  /**
   * Get Results Table.
   *
   * @param array $items
   * @param array $headerRow
   * @param array $rows
   * @param array $options
   * @return mixed
   */
  public function getResultsTable($items, array $headerRow, array $rows, array $options = []) {
	/**
	 * Get Results Table.
	 *
	 * @param mixed $pages
	 * @return mixed
	 */
	private function getResultsTable($pages) {

		$out = "";
		// TODO @UPDATE - NO LONGER IN USE SINCE JULY 2023; MANUALLY ISSUED GIFT CARDS ARE NOT LINKED TO GIFT CARD PRODUCTS!
		// if (empty($this->isReadyToIssueGiftCards())) {
		// 	$warning = $this->_('You need to create at least one Gift Card Product before Gift Cards can be issued.');
		// 	$this->warning($warning);
		// 	$out = "<p>" . $warning . "</p>";
		// }
		// elseif (!$pages->count()) {
		if (!$pages->count()) {
			$out = "<p>" . $this->_('No gift cards found.') . "</p>";
		} else {
			$field = $this->modules->get('MarkupAdminDataTable');
			$field->setEncodeEntities(false);
			// set headers (th)
			$field->headerRow($this->getResultsTableHeaders());
			$checkBoxesName = "pwcommerce_bulk_edit_selected_items[]";
			// set each row
			foreach ($pages as $page) {
				$giftCard = $page->get(PwCommerce::GIFT_CARD_FIELD_NAME);
				// TODO - CHANGE THIS TO FIND ORDERS THAT HAVE HAD A GC FROM THIS GCP/GCPV REDEEMED? - WOULD NEED TO GET USING RAW! , BUT GETTING COUNT!
				// @note: true -> 'include=all'
				// TODO delete if not in use
				// get the count of orders whose related GC was used in the payment
				// $reedemedInOrderCount = $page->references(true)->count;
				// $reedemedInOrderCountString = !empty($reedemedInOrderCount) ? $reedemedInOrderCount : $this->_('Gift card not used in any order purchase');
				$row = [
					// CHECKBOX
					$this->getBulkEditCheckbox($page->id, $checkBoxesName),
					// TITLE > CODE (last four digits)
					$this->getEditItemTitle($page),
					// CUSTOMER
					$giftCard->customerEmail,
					// EXPIRE/END DATE
					$this->getGiftCardEndDate($giftCard->endDate),
					// BALANCE
					$this->getGiftCardBalance($giftCard),
					// USAGE TODO: ORDRERS WHOSE PURCHASE WAS PAID (part or fully) USING THIS GCP's GCPV's Gift Card
					// $reedemedInOrderCountString,

				];
				$field->row($row);
			}
			// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
			$out = $field->render();
		}
		return $out;
	}



	/**
	 * Get Edit Item Title.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getEditItemTitle($page) {
		// get the edit URL if item is unlocked
		$out = $this->getEditItemURL($page);
		// add published and locked status if applicable
		$status = [];
		if ($page->isLocked()) {
			$status[] = $this->_('locked');
		}

		if ($page->isUnpublished()) {
			$status[] = $this->_('unpublished');
		}

		$statusString = implode(', ', $status);
		if ($statusString) {
			$out .= "<small class='block italic mt-1'>{$statusString}</small>";
		}
		// $out = "<a href='{$adminURL}categories/edit/?id={$page->id}'>{$page->title}</a>";
		return $out;
	}

	/**
	 * Get Edit Item U R L.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	private function getEditItemURL($page) {
		// TODO: CHECK IF UNLOCKED FIRST!
		$adminURL = $this->options['admin_url'];
		// -----------
		$giftCard = $page->get(PwCommerce::GIFT_CARD_FIELD_NAME);
		$codeLastFourDigits = $this->pwcommerce->pwcommerceGiftCards->getLastFourDigitsOfGiftCardCode($giftCard->code);
		$codeLastFourDigitsString = sprintf(__("Code ending %s"), $codeLastFourDigits);

		// if page is locked, don't show edit URL
		if ($page->isLocked()) {
			$out = "<span>{$codeLastFourDigitsString}</span>";
		} else {
			$out = "<a href='{$adminURL}gift-cards/view/?id={$page->id}'>{$codeLastFourDigitsString}</a>";
		}
		return $out;
	}


	/**
	 * Get Gift Card End Date.
	 *
	 * @return mixed
	 */
	private function getGiftCardEndDate(string|null $endDate) {
		$endDateTimestamp = (int) $endDate;
		$dateFormat = $this->pwcommerce->getShopDateOnlyFormat();
		if ($endDateTimestamp < 1) {
			// non-expiring gift card
			$out = "-";
		} elseif ($endDateTimestamp < time()) {
			// gift card has expired
			$out = sprintf(__("Gift Card expired on %s"), date($dateFormat, $endDateTimestamp));
		} else {
			// future expiry date
			$out = date($dateFormat, $endDateTimestamp);
		}
		//---------------
		return $out;
	}

	/**
	 * Get Gift Card Balance.
	 *
	 * @param WireData $giftCard
	 * @return mixed
	 */
	private function getGiftCardBalance(WireData $giftCard) {
		$balanceAsCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($giftCard->balance);
		$denominationAsCurrency = $this->pwcommerce->getValueFormattedAsCurrencyForShop($giftCard->denomination);
		//---------------
		// TODO: DO WE NEED A DIV HERE?
		$out = "{$balanceAsCurrency} / {$denominationAsCurrency}";
		return $out;
	}

	// TODO delete since not possible!
	/**
	 * Get Bulk Edit Actions Panel.
	 *
	 * @return mixed
	 */
	private function getBulkEditActionsPanel() {
		// TODO: wip!
		// TODO: NOT IN USE FOR NOW; WILL ADD IN LATER RELEASES
		// $adminURL = $this->options['admin_url'];
		// $label = $this->_('Gift Card Products');
		// $viewGiftCardProductsURL = "{$adminURL}gift-card-products/";
		// $viewGiftCardProductsURLMarkup = "<a href='{$viewGiftCardProductsURL}' class='mr-3'><i class='fa fa-credit-card-alt'></i> {$label}</a>";

		// $issueGiftCardButton = $this->getIssueGiftCardButtonMarkup();
		// append issue gift card button
		// $viewGiftCardProductsURLMarkup .= $issueGiftCardButton;
		# EXTRA MARKUP FOR BULK ACTIONS PANEL
		// append 'issue gift card' button markup
		$extraCustomMarkup = $this->getIssueGiftCardButtonMarkup();
		// append issue gift card modal markup
		// $extraCustomMarkup .= $this->getModalMarkupForManualIssueGiftCard();


		//////////////////////

		$actions = [
			'publish' => $this->_('Publish'),
			'unpublish' => $this->_('Unpublish'),
			'lock' => $this->_('Lock'),
			'unlock' => $this->_('Unlock'),
			'trash' => $this->_('Trash'),
			'delete' => $this->_('Delete'),
		];
		# TODO CREATE NEW OPTION FOR DASHBOARD OR VIEW, ETC SO WE GET DIFFERENT ICON AND NO NEED TO USE 'ADD NEW ITEM LABEL'
		$options = [
			// @NOTE: CANNOT CREATE A GIFT CARD!
			// however, can create a gift card product!
			// this is a link to view, create and edit gift card products
			// left side content extra custom markup
			// 'extra_custom_markup' => $viewGiftCardProductsURLMarkup,
			'extra_custom_markup' => $extraCustomMarkup,
			// extra custom markup will be used
			'is_extra_custom_markup' => true,
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	/**
	 * Get Bulk Edit Checkbox.
	 *
	 * @param int $id
	 * @param mixed $name
	 * @param mixed $xref
	 * @return mixed
	 */
	private function getBulkEditCheckbox($id, $name, $xref = null) {
		$options = [
			'id' => "pwcommerce_bulk_edit_checkbox{$id}",
			'name' => $name,
			'label' => ' ',
			// @note: skipping label
			// 'label2' => $this->_('Use custom handling fee'),
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_bulk_edit_selected_items',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $id,

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		// TODO: ADD THIS ATTR AND MAYBE EVEN A x-ref? so we can selectall using alpinejs
		$field->attr([
			'x-on:change' => 'handleBulkEditItemCheckboxChange',
		]);

		return $field->render();
	}
	/**
	 * Get Issue Gift Card Button Markup.
	 *
	 * @return mixed
	 */
	private function getIssueGiftCardButtonMarkup() {

		// TODO -> THIS NEEDS TO BE A LINK to the page to manually issue gift card
		// TODO for now we are using with this link just to test
		// TODO: DELETE IF NOT IN USE; WE NOW OPEN A MODAL INSTEAD!
		$adminURL = $this->options['admin_url'];
		$issueGiftCardURL = "{$adminURL}gift-cards/issue/";

		$options = [
			'label' => $this->_('Issue Gift Card'),
			// 'type' => 'submit', // TODO IN THIS CASE WE NEED EVERYTHING HERE WRAPPED IN OWN FORM!
			'collapsed' => Inputfield::collapsedNever,
			'small' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'secondary' => true,
			'icon' => 'paper-plane'
		];

		$field = $this->pwcommerce->getInputfieldButton($options);

		$field->href = $issueGiftCardURL;

		// TODO - DUE TO DATEPICKER ISSUES (@see getMarkupForManuallyIssueGiftCardDateField), we no longer use the modal; we now use the 'issue' single page
		// ++++++++
		// $ajaxgGetURL = $this->options['ajax_post_url'];
		// $hxVals = json_encode(['pwcommerce_manual_issue_gift_card_fetch_code' => 1]);
		// $field->attr([

		// 'x-on:click' => 'handleIssueGiftCard'
		// HTMX
		// 	'hx-get' => $ajaxgGetURL,
		// 	'hx-indicator' => '#pwcommerce_manual_issue_gift_card_spinner_indicator',
		// 	'hx-target' => '#pwcommerce_manual_issue_gift_card_code',
		// 	'hx-swap' => 'innerHTML',
		// 	'hx-vals' => $hxVals
		// ]);
		$out = $field->render();
		return $out;
	}

	/**
	 * Render single gift card view headline to append to the Process headline in PWCommerce.
	 *
	 * @param Page $giftCardPage
	 * @return string|mixed
	 */
	public function renderViewItemHeadline(Page $giftCardPage) {
		$headline = $this->_('View gift card');
		if ($giftCardPage->id) {
			$giftCard = $giftCardPage->get(PwCommerce::GIFT_CARD_FIELD_NAME);
			//
			$codeLastFourDigits = $this->pwcommerce->pwcommerceGiftCards->getLastFourDigitsOfGiftCardCode($giftCard->code);
			$headline .= ": #{$codeLastFourDigits}";
		}
		return $headline;
	}

	/**
	 * Render the markup for a single gift card view.
	 *
	 * @param Page $giftCardPage
	 * @return string|mixed
	 */
	public function renderViewItem(Page $giftCardPage) {

		$this->giftCardPage = $giftCardPage;
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		$out = "";
		// get the gift card by its ID
		//   $orderPage = $this->wire('pages')->get("id={$id}");
		if (!$giftCardPage->id) {
			// TODO: return in markup for consistency!
			$out = "<p>" . $this->_('Gift Card was not found!') . "</p>";
		} else {
			$out = $this->buildViewGiftCard();
		}

		//------------------
		// generate final markup
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			// 'classes' => 'pwcommerce_order_view',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		$wrapper->add($field);

		return $wrapper->render();
	}

	/**
	 * Build View Gift Card.
	 *
	 * @return mixed
	 */
	private function buildViewGiftCard() {
		$out =
			// gift card (main)
			$this->renderGiftCardMain() .
			"<hr>" .
			// gift card activities
			$this->renderGiftCardActivities() .
			// "<hr>" .
			// gift card notes
			$this->renderGiftCardNotes();

		// -------
		return $out;
	}
	/**
	 * Render Gift Card Main.
	 *
	 * @return string|mixed
	 */
	private function renderGiftCardMain() {

		// TODO THINK ABOUT ADDING FUNCTION TO TOP-UP GIFT CARD + SEND EMAIL NOTIFICATION
		// TODO THINK ABOUT ADDING FUNCTION TO ALERT CUSTOMER OF IMPENDING EXPIRY
		// TODO THINK ABOUT ADDING FUNCTION TO ADD MORE ADMIN NOTES
		$giftCardPage = $this->giftCardPage;
		$giftCard = $giftCardPage->get(PwCommerce::GIFT_CARD_FIELD_NAME);
		// -------
		$codeLabel = $this->_('Code');
		$customerEmailLabel = $this->_('Customer Email');
		$balanceLabel = $this->_('Balance');
		$expiryLabel = $this->_('Expiry');
		// -------------
		$codeLastFourDigits = $this->pwcommerce->pwcommerceGiftCards->getLastFourDigitsOfGiftCardCode($giftCard->code);
		$codeLastFourDigitsString = sprintf(__("Code ending %s"), $codeLastFourDigits);
		$balanceText = $this->getGiftCardBalance($giftCard);
		$expiryText = $this->getGiftCardEndDate($giftCard->endDate);
		// TODO - ALSO SHOW PRODUCT ID AND VARIANT ID IF APPLICABLE?
		$out =

			// gift card code
			"<div class='mt-1'>" .
			"<span class='opacity-70'>" . $codeLabel . "</span>" .
			"<span>: {$codeLastFourDigitsString}</span>" .
			"</div>" .
			// gift card customer email
			"<div>" .
			"<span class='opacity-70'>" . $customerEmailLabel . "</span>" .
			"<span>: {$giftCard->customerEmail}</span>" .
			"</div>" .
			// gift card balance/denom
			"<div>" .
			"<span class='opacity-70'>" . $balanceLabel . "</span>" .
			"<span>: {$balanceText}</span>" .
			"</div>" .
			// gift expiry
			"<div>" .
			"<span class='opacity-70'>" . $expiryLabel . "</span>" .
			"<span>: {$expiryText}</span>" .
			"</div>";

		// -----
		return $out;
	}

	/**
	 * Render Gift Card Activities.
	 *
	 * @return string|mixed
	 */
	private function renderGiftCardActivities() {
		$giftCardPage = $this->giftCardPage;
		/** @var WireArray $giftCardActitivities */
		$giftCardActitivities = $giftCardPage->get(PwCommerce::GIFT_CARD_ACTIVITIES_FIELD_NAME);

		$out = "<div><p>HERE RENDER GIFT CARD ACTIVITIES STUFF. IT IS TABLE WITH ACTIVITIES, I.E. ORDER ID USED ON, ACTIVITY DATE, AMOUNT USED</p></div>";

		if ($giftCardActitivities->count) {
			$dateFormat = $this->pwcommerce->getShopDateOnlyFormat();
			$headers = [
				// ORDER ID
				[$this->_('Order ID'), 'pwcommerce_gift_cards_activities_table_order_id'],
				// ACTIVITY DATE
				[$this->_('Activity Date'), 'pwcommerce_gift_cards_activities_table_activity_date'],
				// AMOUNT USED
				[$this->_('Amount'), 'pwcommerce_gift_cards_activities_table_amount'],
			];

			$field = $this->modules->get('MarkupAdminDataTable');
			$field->setEncodeEntities(false);
			// set headers (th)
			$field->headerRow($headers);
			// set each row
			foreach ($giftCardActitivities as $giftCardActitivity) {
				$activityDate = date($dateFormat, $giftCardActitivity->activityDate);
				$row = [
					// ORDER ID
					$giftCardActitivity->orderID,
					// ACTIVITY DATE
					$activityDate,
					// AMOUNT USED
					$giftCardActitivity->amount,

				];
				$field->row($row);
			}
			// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
			$out = $field->render();
		} else {
			$out = "<p>" . $this->_('This gift card has not yet been used.') . "</p>";
		}

		// -----
		return $out;
	}

	/**
	 * Render Gift Card Notes.
	 *
	 * @return string|mixed
	 */
	private function renderGiftCardNotes() {
		// TODO - TO ADD IN FUTURE!
		$out = "";
		return $out;
	}



	/**
	 * Modal for MANUAL issue of Gift Cards.
	 *
	 * @return mixed
	 */
	private function getModalMarkupForManualIssueGiftCard() {
		// ## ORDER MARK AS MODALs MARKUP  ##
		$xstore = $this->xstore;

		// $header = $this->_("Action TODO MODEL ACTION TYPE HERE X-TEXT - Status");
		// @UPDATE TODO FOR NOW WE SET IN THE HTMX RESPONSE IN THE BODY OF THE MODAL
		$header = $this->_("Action Status");

		$issueGiftCardModalProperty = "is_manual_issue_gift_card_modal_open";
		// =======
		// HTMX
		// @note: these are for fetching the markup for requested order status action! not for posting
		// for posting, see the htmx attributes pass to the apply button in $this->renderModalMarkupForManualIssueGiftCardSendButton()
		// $ajaxgGetURL = $this->options['ajax_post_url'];
		// $hxVals = json_encode(['pwcommerce_manual_issue_gift_card_fetch_code' => 1]);

		// TODO? @NOTE: WE NEED THE WATCH SO AS TO CLEAR INPUTS/ELEMENTS IF MODAL IS CLOSED VIA THE 'x' RATHER THAN CANCEL

		$xInitForManualIssueGiftCardArray = [
			'watch_order_status_modal' => "\$watch(`{$xstore}.is_manual_issue_gift_card_modal_open`, value => handleManualResetManualIssueGiftCard(value))"
		];
		$xInitForManualIssueGiftCardString = implode(",", $xInitForManualIssueGiftCardArray);
		$body =
			"<div x-init='{$xInitForManualIssueGiftCardString}'>" .
			// ++++++++
			// HTMX
			// @note: the hx-vals is to tell ProcessPwCommerce::pageHandler to call the renderResults() here
			// i.e., in this context, i.e. 'prders'
			// @note: we will swap inside this div (default innerHTML)
			/*
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																					@note:
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																					- 'pwcommerce_send_window_notification' will tell htmx:afterSettle look at the request config trigger element, grab details of a custom window event and send them to window.
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																					- Alpine will be listening to that window event.
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																					- In this case, it will use that to disabled the 'apply' button then close the modal shortly after.
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																					- this approach is versatile and doesn't need the server to know about the events that need to be sent
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																					- the event.detail.requestConfig.elt is our element with the event details; in this case it is the 'apply button'
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																					- @see: $this->renderModalMarkupForManualIssueGiftCardSendButton()
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																					*/
			// TODO EDIT/DELETE HTXM ATTRIBUTES BELOW AS NEEDED
			// "<div id='pwcommerce_manual_issue_gift_card' hx-get='{$ajaxgGetURL}' hx-indicator='#pwcommerce_manual_issue_gift_card_spinner_indicator' hx-trigger='pwcommercemanualissuegiftcardcodefetch' hx-target='#pwcommerce_manual_issue_gift_card_code' hx-swap='innerHTML' hx-vals='{$hxVals}' @pwcommercemanualissuegiftcardcodenotification.window='handleIssueGiftCard'>" .
			"<div id='pwcommerce_manual_issue_gift_card' @pwcommercemanualissuegiftcardcodenotification.window='handleIssueGiftCard>" .
			"<p id='pwcommerce_manual_issue_gift_card_code'>GC CODE INSERTED HERE</p>" .
			"<small>ADD NOTE HERE ABOUT COPY THIS, WINDOW CLOSE + ADD JS TO COPY TO CLIPBOARD AND MESSAGE CONFIRMING THAT (SHOW/HIDE)</small>" .
			"<hr>" .
			$this->getManuallyIssueGiftCardMarkup() .
			// "<p>FORM SHOWN ONLY AFTER HTMX RETURNS GC CODE: SO, AFTER:SETTLE. CAN ALSO USE ALPINE TO OPEN MODAL AFTER HTMX HAS SETTLED</p>" .
			"</div>" .

			// ++++++++
			// spinner
			"<div id='pwcommerce_manual_issue_gift_card_spinner_indicator' class='htmx-indicator'>" .
			"<i class='fa fa-fw fa-spin fa-spinner'></i>" .
			$this->_("Please wait") .
			"&#8230;" .
			"</div>" .
			// ++++++++
			"</div>"; // end div with x-init
		// ==================================
		// apply button
		$applyButton = $this->renderModalMarkupForManualIssueGiftCardSendButton();
		// cancel button
		$cancelButton = $this->renderModalMarkupForManualIssueGiftCardCancelButton();
		$footer = "<div class='ui-dialog-buttonset'>{$applyButton}{$cancelButton}</div>";
		$xproperty = $issueGiftCardModalProperty;
		$size = '4x-large';

		// wrap content in modal for activating/deactivating
		// modal options
		$options = [
			// $header The modal title pane markup.
			'header' => $header,
			// $body The main content markup.
			'body' => $body,
			// $footer The footer markup.
			'footer' => $footer,
			// $xstore The alpinejs store with the property that will be modelled to show/hide the modal.
			'xstore' => $this->xstoreRenderGiftCards,
			// $xproperty The alpinejs property that will be modelled to show/hide the modal.
			'xproperty' => $xproperty,
			// $size The size of the modal requested.
			'size' => $size,
		];
		$out = $this->pwcommerce->getModalMarkup($options);

		return $out;
	}

	/**
	 * Render Modal Markup For Manual Issue Gift Card Send Button.
	 *
	 * @return string|mixed
	 */
	private function renderModalMarkupForManualIssueGiftCardSendButton() {
		# ALPINE JS #
		$xstore = $this->xstore;
		// $eventDetailsArray = [
		// 	'reset_modal' => true,
		// 	'delay' => 2000,
		// 	// TODO adjust as needed; also if we have an error, it would be good to delay this further! maybe send from server???
		// 	'nullify_reset_property' => ['is_ready_apply_order_status_action']
		// ];
		// $eventDetailsJSON = json_encode($eventDetailsArray);
		# HTMX #
		// $ajaxPostURL = $this->options['ajax_post_url'];
		// $hxVals = json_encode(['pwcommerce_order_status_action_context' => 'orders']);
		$applyButtonOptions = [
			'type' => 'submit',
			// 'type' => 'button',
			# ALPINE JS #
			/*
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																													alpine js: disable apply button and apply opacity if applicable to some three use cases
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																													i. payment status action: partly paid [3999]: if no payment method is selected OR part payment amount is empty
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																													ii. payment status action: paid [4000]: if no payment method is selected
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																													iii. payment status action: partly refunded [4998]: if refund amount is empty
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																													*/
			'x-bind:disabled' => "!{$xstore}.is_ready_apply_order_status_action",
			'x-bind:class' => "{$xstore}.is_ready_apply_order_status_action ? `` : `opacity-50`",
			#################
			// @UPDATE SATURDAY 22 APRIL 2023 - NO LONGER USING HTMX FOR SUBMISSION; WE NEED THE PAGE TO RELOADED AFTER POSTING STATUSES (AS BEFORE) SO EDITOR/SHOP ADMIN CAN SEE CHANGES IN NOTES; EASIER THAN USING HTMX IN THIS CASE
			# HTMX #
			// 'hx-post' => $ajaxPostURL,
			// 'hx-vals' => $hxVals,
			// swap the element (div) that matches this CSS selector
			// @note: it is the same div that got swapped with the hx-get in $this->getModalMarkupForOrderStatusActions() when we fetched markup
			// for the order status action
			// 'hx-target' => '#pwcommerce_order_status_action_apply',
			// 'hx-target' => '#pwcommerce_order_status_fetch_markup_response_wrapper',
			// 'hx-indicator' => '#pwcommerce_order_status_action_apply_spinner_indicator',
			// 'data-send-notification-event-name' => 'pwcommerceorderstatusactionsendnotification',
			// 'data-send-notification-event-details' => "{$eventDetailsJSON}",

		];


		// -----------
		$applyButton = $this->pwcommerce->getModalActionButton($applyButtonOptions, 'send');

		// ===========
		return $applyButton;
	}
	/**
	 * Get rendered button for the modal for actioning a selected order status.
	 *
	 * @return string
	 */
	private function renderModalMarkupForManualIssueGiftCardCancelButton(): string {
		$cancelButton = $this->pwcommerce->getModalActionButton(['x-on:click' => 'resetManualIssueGiftCardAndCloseModal'], 'cancel');
		return $cancelButton;
	}

	// ~~~~~~~~~~~~~
	/**
	 * XXXXXXX
	 *
	 * @return mixed
	 */
	public function getCustomAddNewItemForm() {
		$form = $this->pwcommerce->getInputfieldForm();
		$wrapper = $this->pwcommerce->getInputfieldWrapper();
		$out = "";

		// ++++++++++++++++

		//-------------------TODOxxxx (getInputfieldMarkup)

		// TODO DELETE WHEN DONE
		// $out .= $this->renderCountriesList();
		// TODO @UPDATE: MONDAY 21 AUGUST 2023 1747: EDIT/DELETE BELOW! NO LONGER NEEDED AS NO CONNECTION BETWEEN MANUALLY ISSUED GIFT CARD AND GIFT CARD PRODUCTS!
/*
		if (empty($this->isIssuingGiftCardsPossible())) {
			//------------------- show message that needs to add at least one GCPV
			$out .= $this->getMarkupForNotPossibleToIssueGiftCards();
			# @NOTE - TO REMOVE EXTRA PADDING IF 'issue GFC button' not shown
			$classes = 'pwcommerce_gift_card_product_variant_no_child';
		} else {
			//------------------- add gift card product variant(s) to GCP (getInputfieldButton)
			// $out = $this->renderIssueGiftCardButton();
			$out .= $this->getManuallyIssueGiftCardMarkup();
		}

*/
		$out = $this->getManuallyIssueGiftCardMarkup();

		$description = $this->_('Gift Card Code');

		$options = [
			'description' => $description,
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// @note: we need this outline to show
			// 'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		// @note: add custom margin to the div.InputfieldContent.uk-form-controls
		$field->contentClass('mt-5');
		$wrapper->add($field);

		//------------------- is_ready_to_save (getInputfieldHidden)
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPwCommerce::renderAddItem() know that we are ready to save
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];

		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		//------------------- save button (getInputfieldButton)
		// $options = [
		// 	'id' => "submit_save",
		// 	'name' => "pwcommerce_save_new_button",
		// 	'type' => 'submit',
		// 	'label' => $this->_('Save'),
		// ];
		// $field = $this->pwcommerce->getInputfieldButton($options);
		// $field->showInHeader();
		// // add submit button for add new country add  SAVE process views
		// $wrapper->add($field);

		//------------------- save + publish button (getInputfieldButton)
		// $options = [
		// 	'id' => "submit_save_and_publish",
		// 	'name' => "pwcommerce_save_and_publish_new_button",
		// 	'type' => 'submit',
		// 	'label' => $this->_('Save + Publish'),
		// 	'secondary' => true,
		// ];
		// $field = $this->pwcommerce->getInputfieldButton($options);
		// // add submit button for single item add  SAVE + PUBLISH process views
		// $wrapper->add($field);

		//------------------
		// ADD WRAPPER TO FORM
		$form->add($wrapper);

		//----------
		return $form;
	}


	/**
	 * Get Manually Issue Gift Card Markup.
	 *
	 * @return mixed
	 */
	private function getManuallyIssueGiftCardMarkup() {



		# TODO HERE NEED TO maybe add a wrapper ID to pass to htmx to get forms for htmx. e.g. do we need separate one for parent id or add hidden input for that here?

		// TODO DELETE UNUSED PLUS RENAME AS NEEDED


		$issueGiftCardMarkup = $this->getMarkupForManuallyIssueGiftCardParts();
		// $xstore = $this->xstore;
		$code = $this->pwcommerce->pwcommerceGiftCards->getUniqueGiftCardCode();


		// --------
		# TODO NEED TO SORT OUT THIS CONTAINER SCROLL HEIGHT! NOT WORKING OK!
		// $issueGiftCardAccordion =
		// 	// "<div x-data='{selected:null}'>" .// @NOTE: MOVED TO STORE; DELETE WHEN DONE
		// 	"<div>" .
		// 	"<a class='block mb-5' @click='handleOpenIssueGiftCardAccordion'><i class='fa fa-paper-plane' aria-hidden='true'></i> <span>{$this->issueGiftCardLink}</span></a>" .
		// 	"<div class='text-sm px-0 m-0 relative overflow-hidden transition-all max-h-0 duration-700'
		// 	x-ref='container' x-bind:style='{$xstore}.is_open_issue_gift_card_accordion ? `max-height: ` + \$refs.container.scrollHeight + `px` : ``'
		// 	aria-hidden='false'>" .
		// 	$issueGiftCardMarkup .
		// 	"</div>" .
		// 	"</div>";

		//----------------
		$out =
			"<div id='pwcommerce_manual_issue_gift_card_wrapper' x-data='ProcessPWCommerceData' @pwcommercemanualissuegiftcardcodemutationobservernotification.window='handleIssueGiftCardMutationObserverChange' @pwcommercemanualissuegiftcardcoderadiochangenotification.window='handleIssueGiftCardRadioChange'>" .
			"<div>" .
			// ---------
			// TODO DELETE ABOVE; ACCORDION NO LONGER NEEDED!
			// $issueGiftCardAccordion .
			# **********
			// gift card code
			"<span id='pwcommerce_manual_issue_gift_card_code' class='font-bold mt-1 mb-2 block'>{$code}</span>" .
			"<input type='hidden' name='pwcommerce_issue_gift_card_code' value='{$code}'>" .
			// gift card code warning
			"<small class='block mb-5'>" . $this->_('Once you click the button to send the gift card you will not be able to see this code again.') . "</small>" .
			// TODO USE X-CLOAK OR X-IF
			$this->getCopyGiftCardCodeButtonMarkup() .
			"<small class='block mt-1' x-show='{$this->xstore}.is_copied_gift_card_code==1'>" . $this->_('Gift card code successfully copied.') . "</small>" .
			"<small class='block mt-1 pwcommerce_error' x-show='{$this->xstore}.is_copied_gift_card_code==0'>" . $this->_('Not able to copy gift card code. Please copy manually.') . "</small>" .
			"<hr>" .
			########
			// FORM INPUTS

			$issueGiftCardMarkup .
			// --------
			"</div>" .
			// end issue gift card accordion box
			"</div>";

		// -----------


		return $out;
	}

	// markup for whole issue gift card
	/**
	 * Get Markup For Manually Issue Gift Card Parts.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardParts() {

		// GET WRAPPER FOR ALL INPUTFIELDS HERE
		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		// TODO SHOULD WE ADD SUBJECT AND MESSAGE INPUTS?! SHOULD MESSAGE/BODY TEXTAREA BE NON-RTE? SHOULD IT CONTAIN [REPLACE WITH NAME] AND {gift card code will be placed here} {etc}???
		// @UPDATE TODO: WE NOW USE AN EMAIL TEMPLATE INSTEAD; MIGHT REVERT TO RTE IN FUTURE!

		// ==================

		//------------------- issue gift card email (getInputfieldText)
		$field = $this->getMarkupForManuallyIssueGiftCardEmailTextField();
		$wrapper->add($field);

		//------------------- issue gift card email subject (getInputfieldText)
		// $field = $this->getMarkupForManuallyIssueGiftCardEmailSubjectTextField();
		// $wrapper->add($field);

		//------------------- issue gift card email body (getInputfieldRichText)
		// $field = $this->getMarkupForManuallyIssueGiftCardEmailBodyRTEField();
		// $wrapper->add($field);

		//------------------- issue gift card denomination input mode (getInputfieldRadios)
		$field = $this->getMarkupForManuallyIssueGiftCardDenominationModeRadioField();
		$wrapper->add($field);

		//------------------- issue gift card pre-defined denomination (getInputfieldSelect)
		$field = $this->getMarkupForManuallyIssueGiftCardDenominationsSelectField();
		$wrapper->add($field);

		//------------------- issue gift card custom denomination (getInputfieldText)
		$field = $this->getMarkupForManuallyIssueGiftCardDenominationTextField();
		$wrapper->add($field);


		// TODO DELETE WHEN DONE; NO LONGER USING START DATE!
		//------------------- issue gift card start date (getInputfieldDate)
		// TODO ADD START DATE FIELD
		// $field = $this->getMarkupForManuallyIssueGiftCardDateField();
		// $wrapper->add($field);

		//------------------- issue gift card set expiration date (getInputfieldRadios)
		$field = $this->getMarkupForManuallyIssueGiftCardSetExpirationDateRadioField();
		$wrapper->add($field);

		//------------------- issue gift card end date (getInputfieldDate)
		// TODO ADD END DATE FIELD
		$field = $this->getMarkupForManuallyIssueGiftCardDateField();
		$wrapper->add($field);

		//------------------- issue gift card admin note (getInputfieldTextarea)
		$field = $this->getMarkupForManuallyIssueGiftCardAdminNoteTextareaField();
		$wrapper->add($field);

		//------------------- issue gift card GCP ID (getInputfieldHidden)
		// TODO ADD GCP ID HIDDEN FIELD??
		// @update: no longer needed - we send the ID via the select denomination
		// we then get the value on the server-side
		// @UPDATE 2: MANUALLY ISSUED GIFT CARDS NO LONGER RELATED TO GCPs! WE ONLY NEED THE AMOUNT

		//------------------- issue gift card button (getInputfieldButton)
		$field = $this->getMarkupForManuallyIssueGiftCardButtonField();
		$wrapper->add($field);

		// TODO DELETE WHEN DONE - WE NOW SEND A GET REQUEST VIA HTMX USING hx-vals
		//------------------- issue gift card GCP: Ajax Request signal (getInputfieldHidden)
		// @note: for ProcessPwCommerce::executeAjax
		// $options = [
		// 	'id' => "pwcommerce_manually_issue_gift_card",
		// 	'name' => 'pwcommerce_manually_issue_gift_card',
		// 	'value' => 1
		// ];

		// $field = $this->pwcommerce->getInputfieldHidden($options);
		// $wrapper->add($field);

		// TODO: DELETE WHEN DONE; GCP IS NOW NOT RELATED TO MANUALLY ISSUED GIFT CARD
		//------------------- issue gift card GCP: Ajax Request set context (getInputfieldHidden)
		// @note: for ProcessPwCommerce::executeAjax
		// $options = [
		// 	'id' => "pwcommerce_manually_issue_gift_card_context",
		// 	'name' => 'pwcommerce_manually_issue_gift_card_context',
		// 	'value' => 'gift-card-products'
		// ];

		// $field = $this->pwcommerce->getInputfieldHidden($options);
		// $wrapper->add($field);


		//------------------- issue gift card processing JavaScript configs
		// @note: current only for dates validation errors (translated strings)
		$issueGiftCardDatesErrorStringsMarkup = $this->getMarkupForManuallyIssueGiftCardDatesErrorStrings();

		//------------------- issue gift card processing modal
		// $modalMarkup = $this->getMarkupForManuallyIssueGiftCardModal();


		// ------
		// $out = $wrapper->render() . $issueGiftCardDatesErrorStringsMarkup . $modalMarkup;
		$out = $wrapper->render() . $issueGiftCardDatesErrorStringsMarkup;
		// $out = $wrapper->render() . $modalMarkup;
		// $out = $wrapper->render();

		return $out;
	}

	/**
	 * Get Markup For Manually Issue Gift Card Email Text Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardEmailTextField() {
		// TODO RENAME NAME, ETC BELOW


		// TODO confirm $xstore!
		//------------------- issue gift card customer email (getInputfieldText)
		// $xstore = "{$this->xstore}.manually_issue_gift_card_data";
		// TODO DELETE WHEN DONE; WE MODEL DIRECT OBJECT!
		// $xstoreIssueGiftCardData = "{$this->xstore}.manually_issue_gift_card_data";

		// /** @var Array $alpineJSValues */
		// $alpineJSValues = $this->getIssueGiftCardDependentInputsAlpineAttributes();
		// $hiddenInverseClass = $alpineJSValues['class_hidden_inverse'];
		// $opacityClass = $alpineJSValues['class_opacity'];
		// $disabled = $alpineJSValues['input_disabled'];

		// ----------

		// $selectGiftCardDenominationNote = $this->_('please select a gift card denomination first');
		// $extraDescriptionMarkup = "<span :class=\"{$hiddenInverseClass}\"> ($selectGiftCardDenominationNote)</span>.";

		$description = $this->_('Customer email to send gift card to');
		$customHookURL = "/find-pwcommerce_gift_card_manual_issue_customers_emails/";
		$tagsURL = "{$customHookURL}?q={q}";

		// TODO: IS THIS OK? ALLOW MORE?
		$placeholder = $this->_("Type at least 3 characters to search for customers.");

		$options = [
			'id' => "pwcommerce_issue_gift_card_customer_email",
			// TODO: not really needed!
			'name' => "pwcommerce_issue_gift_card_customer_email",
			// 'type' => 'email',
			'label' => $this->_('Customer Email'),
			// 'description' => $description . $extraDescriptionMarkup,
			'description' => $description,
			'useAjax' => true,
			// to allow sending emails to potential customers (not just existing)
			'allowUserTags' => true,
			'closeAfterSelect' => false,
			'tagsUrl' => $tagsURL,
			'placeholder' => $placeholder,
			'maxItems' => 1,
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 50,
			'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
		];

		// $field = $this->pwcommerce->getInputfieldText($options);
		$field = $this->pwcommerce->getInputfieldTextTags($options);
		// allow HTML in description
		// $field->entityEncodeText = false;
		$field->attr([
			'x-model' => "{$this->xstore}.gift_card_customer_email",
			'data-gift-card-mutation-observer-notification-type' => 'email',
			// ==========
			// if no denonimation selected; opacity at a quarter
			// 'x-bind:class' => $opacityClass,
			// // if no denonimation selected; input is disabled
			// 'x-bind:disabled' => $disabled,
			// 'x-ref' => "pwcommerce_issue_gift_card_customer_email",
		]);

		$errorHandlingMarkup = "<small class='pwcommerce_error' x-show='!{$this->xstore}.is_valid_gift_card_customer_email'>" . $this->_('Invalid email address') . "</small>";
		// $field->prependMarkup($errorHandlingMarkup);
		$field->appendMarkup($errorHandlingMarkup);


		return $field;
	}

	/**
	 * Get Markup For Manually Issue Gift Card Denomination Mode Radio Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardDenominationModeRadioField() {

		$radioOptionsDenominationMode = [
			'pre_defined' => __('Select value from pre-defined values'),
			'custom' => __('Specify custom value'),
		];

		$options = [
			'id' => "pwcommerce_issue_gift_card_denomination_mode",
			'name' => 'pwcommerce_issue_gift_card_denomination_mode',
			'label' => $this->_('Specify Value'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 33,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
			'radio_options' => $radioOptionsDenominationMode,
			'value' => 'pre_defined',
		];

		$field = $this->pwcommerce->getInputfieldRadios($options);

		// note => ALPINE DOES NOT WORK WITH PROCESSWIRE RADIOS

		$field->attr([
			'x-model' => "{$this->xstore}.denomination_mode",
			'x-ref' => "pwcommerce_issue_gift_card_denomination_mode",
		]);
		// +++++++++
		// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change (specify custom denomination vs set expiry date)
		$field->wrapAttr('data-gift-card-radio-change-type', 'denomination');



		// -------
		return $field;
	}

	/**
	 * Get Markup For Manually Issue Gift Card Denominations Select Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardDenominationsSelectField() {
		// TODO RENAME NAME, ETC BELOW
		// TODO confirm $xstore!
		// @note: models value for modal edit only!

		$xstoreIssueGiftCardData = "{$this->xstore}.manually_issue_gift_card_data";

		//------------------- issue gift card denominations (getInputfieldSelect)
		// TODO - WIP! SHOULD ALSO BE ABLE TO ENTER MANUALLY!
		$selectOptions = [
			5 => 5,
			10 => 10,
			15 => 15,
			20 => 20,
			25 => 25,
			30 => 30,
			35 => 35,
			40 => 40,
			45 => 45,
			50 => 50,
			60 => 60,
			70 => 70,
			80 => 80,
			90 => 90,
			100 => 100,
		];

		// TODO DELETE WHEN DONE; NO LONGER LINKING GCPs TO GCs!
		// @NOTE: THE ISSUE GIFT CARD MARKUP WILL NOT SHOW IF NO VARIANTS WERE FOUND SO OK HERE TO ASSUME VARIANTS FOUND.TODO OK?

		// TODO DELETE WHEN DONE; NO LONGER LINKING GCPs TO GCs!
		// $giftCardProductVariants = $this->getGiftCardProductVariants();

		// TODO DELETE WHEN DONE; NO LONGER LINKING GCPs TO GCs!
		// foreach ($giftCardProductVariants as $variantID => $stock) {

		// 	if (empty($stock))
		// 		continue;
		// 	$selectOptions[$variantID] = $stock['price'];
		// }

		// TODO NEED TO SORT OUT BELOW IF STARTING AND NO STOCK YET, SELECT BELOW NEEDS NOT TO BE SHOWN; INSTEAD SHOW MARKUP ABOUT NEED TO CREATE AND ENABLE AT LEAST ONE GIFT CARD PRODUCT VARIANT


		$description = sprintf(__("Value for the gift card to issue%s."), $this->shopCurrencySymbolString);

		$options = [
			'id' => "pwcommerce_issue_gift_card_denomination_pre_defined",
			// TODO: not really needed!
			'name' => 'pwcommerce_issue_gift_card_denomination_pre_defined',
			// TODO: SKIP LABEL?
			'label' => $this->_('Denomination'),
			//  'skipLabel' => Inputfield::skipLabelHeader,
			'description' => $description,
			'collapsed' => Inputfield::collapsedNever,
			'show_if' => 'pwcommerce_issue_gift_card_denomination_mode=pre_defined',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
			'columnWidth' => 35,
			'required' => true,
			'select_options' => $selectOptions,
		];

		$field = $this->pwcommerce->getInputfieldSelect($options);

		$field->attr([
			'x-model' => "{$this->xstore}.denomination_pre_defined",
			'x-ref' => "pwcommerce_issue_gift_card_denomination_pre_defined",
		]);

		$errorHandlingMarkup = "<small class='pwcommerce_error block mt-1' x-show='!{$this->xstore}.denomination_pre_defined'>" . $this->_('Value must be selected') . "</small>";
		// $field->prependMarkup($errorHandlingMarkup);
		$field->appendMarkup($errorHandlingMarkup);

		return $field;
	}

	/**
	 * Get Markup For Manually Issue Gift Card Denomination Text Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardDenominationTextField() {

		$description = sprintf(__("Custom value for the gift card to issue%s."), $this->shopCurrencySymbolString);

		$options = [
			'id' => "pwcommerce_issue_gift_card_denomination_custom",
			// TODO: not really needed!
			'name' => "pwcommerce_issue_gift_card_denomination_custom",
			'type' => 'number',
			'min' => '0.1',
			'step' => '0.01',
			'label' => $this->_('Denomination'),
			'value' => '',
			'description' => $description,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 33,
			'size' => 30,
			'show_if' => 'pwcommerce_issue_gift_card_denomination_mode=custom',
			'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		// allow HTML in description
		$field->entityEncodeText = false;
		$field->attr([
			'x-model.number' => "{$this->xstore}.denomination_custom",
			// ==========
			// // if no denonimation selected; opacity at a quarter
			// 'x-bind:class' => $opacityClass,
			// // if no denonimation selected; input is disabled
			// 'x-bind:disabled' => $disabled,
			'x-ref' => "pwcommerce_issue_gift_card_denomination_custom",
		]);

		$errorHandlingMarkup = "<small class='pwcommerce_error block mt-1' x-show='!{$this->xstore}.denomination_custom'>" . $this->_('Custom value must be specified') . "</small>";
		// $field->prependMarkup($errorHandlingMarkup);
		$field->appendMarkup($errorHandlingMarkup);


		return $field;
	}

	/**
	 * Get Markup For Manually Issue Gift Card Set Expiration Date Radio Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardSetExpirationDateRadioField() {

		$radioOptionsSetExpirationDate = [
			'no_expiration' => __('No expiration date'),
			'set_expiration' => __('Set expiration date'),
		];

		$options = [
			'id' => "pwcommerce_issue_gift_card_set_expiration_date",
			'name' => 'pwcommerce_issue_gift_card_set_expiration_date',
			'label' => $this->_('Set Expiration Date'),
			'notes' => $this->_('Check the laws for your country before setting an expiration date. Some countries do not allow this.'),
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 33,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'radio_options' => $radioOptionsSetExpirationDate,
			'value' => 'no_expiration',
		];

		$field = $this->pwcommerce->getInputfieldRadios($options);

		// note => ALPINE DOES NOT WORK WITH PROCESSWIRE RADIOS

		$field->attr([
			'x-model' => "{$this->xstore}.set_expiration_date",
			// @NOTE: DOESN'T WORK ON RADIOS PER SE; THIS WILL BE APPLIED TO ITS PARENT <li>
			// 'x-ref' => "pwcommerce_issue_gift_card_set_expiration_date",

		]);

		// +++++++++
		// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change (specify custom denomination vs set expiry date)
		$field->wrapAttr('data-gift-card-radio-change-type', 'date');


		// -------
		return $field;
	}

	// TODO DELETE WHEN DONE; NO LONGER USING START DATE!
	// /**
  * Get Markup For Manually Issue Gift Card Date Field.
  *
  * @param string $mode
  * @return mixed
  */
 private function getMarkupForManuallyIssueGiftCardDateField($mode = 'start') {
	/**
	 * Get Markup For Manually Issue Gift Card Date Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardDateField() {
		// TODO RENAME NAME, ETC BELOW


		// TODO confirm $xstore!
		//------------------- issue gift card customer email (getInputfieldText)
		// $xstore = "{$this->xstore}.manually_issue_gift_card_data";
		// TODO DELETE WHEN DONE; WE MODEL DIRECT OBJECT!

		$xstore = $this->xstore;
		$xstoreIssueGiftCardData = "{$xstore}.manually_issue_gift_card_data";

		// TODO REPHRASE?!
		$notes = $this->_('This field is optional. Check the laws for your country first to confirm that they allow Gift Cards to expire.');


		// TODO DELETE WHEN DONE; NO LONGER USING START DATE!
		// if ($mode == 'end') {
		// 	$label = $this->_('End Date');
		// 	$description = $this->_('Gift card end date');
		// } else {
		// 	$label = $this->_('Start Date');
		// 	$description = $this->_('Gift card start date');
		// }

		$mode = 'end';

		// TODO ADD SHOW IF FOR THIS INPUT!
		$label = $this->_('Expiration Date');
		$description = $this->_('Gift card end date.');

		/** @var Array $alpineJSValues */
		// $alpineJSValues = $this->getIssueGiftCardDependentInputsAlpineAttributes();
		// $hiddenInverseClass = $alpineJSValues['class_hidden_inverse'];
		// $opacityClass = $alpineJSValues['class_opacity'];
		// $disabled = $alpineJSValues['input_disabled'];

		// ----------

		// $selectGiftCardDenominationNote = $this->_('please select a gift card denomination first');
		// $extraDescriptionMarkup = "<span :class=\"{$hiddenInverseClass}\"> ($selectGiftCardDenominationNote)</span>.";

		// ++++++++++++

		// TODO @NOTE: CHANGE FROM MODAL TO NON MODAL FOR MANUALLY ISSUE GC. THIS IS BECAUSE OF ISSUE WITH DATE PICKER STAYING BEHIND THE MODAL!

		$options = [
			'id' => "pwcommerce_issue_gift_card_{$mode}_date",
			// TODO: not really needed!
			'name' => "pwcommerce_issue_gift_card_{$mode}_date",
			'type' => 'text',
			'label' => $label,
			// 'description' => $description . $extraDescriptionMarkup,
			'description' => $description,
			'notes' => $notes,
			'collapsed' => Inputfield::collapsedNever,
			'columnWidth' => 50,
			'show_if' => 'pwcommerce_issue_gift_card_set_expiration_date=set_expiration',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',


		];

		// TODO FOR NOW ONLY DOING DATE, NOT TIME. OK?
		$field = $this->pwcommerce->getInputfieldDatetime($options);

		// allow HTML in description
		// $field->entityEncodeText = false;
		$field->attr([
			'x-model' => "{$this->xstore}.{$mode}_date",
			// ==========
			// // if no denonimation selected; opacity at a quarter
			// 'x-bind:class' => $opacityClass,
			// // if no denonimation selected; input is disabled
			// 'x-bind:disabled' => $disabled,
			// 'x-ref' => "pwcommerce_issue_gift_card_{$mode}_date",
			'x-ref' => "pwcommerce_issue_gift_card_{$mode}_date",
			// @note: doesn't work; use mutation observer instead
			// 'x-on:change' => 'handleGiftCardEndDateChange',
			'data-gift-card-mutation-observer-notification-type' => 'date',
		]);

		// $field->set('dateInputFormat', "Y-m-d");
		$errorHandlingMarkup = "<small class='pwcommerce_error' x-show='{$xstore}.is_error_issue_gift_card_{$mode}_date' x-text='{$xstore}.issue_gift_card_{$mode}_date_error_text'></small>";
		// // $field->prependMarkup($errorHandlingMarkup);
		$field->appendMarkup($errorHandlingMarkup);
		// +++++++++
		// @note: this sets a data attribute to the parent <li>. We will use mutation observer to listen to changes on this parent element since listening to changes to the jQuery UI text input is not working. We will then get the value of the input (the date, if any) using the id in this data attribute
		$field->wrapAttr('data-gift-card-mutation-observer-element-id', 'pwcommerce_issue_gift_card_end_date');


		return $field;
	}

	/**
	 * Get Copy Gift Card Code Button Markup.
	 *
	 * @return mixed
	 */
	private function getCopyGiftCardCodeButtonMarkup() {
		$options = [
			'label' => $this->_('Copy Gift Card Code'),
			// 'type' => 'submit', // TODO IN THIS CASE WE NEED EVERYTHING HERE WRAPPED IN OWN FORM!
			'collapsed' => Inputfield::collapsedNever,
			'small' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'secondary' => true,
			'icon' => 'clone'
		];

		$field = $this->pwcommerce->getInputfieldButton($options);
		$field->attr([
			// 'x-data' => 'ProcessPWCommerceData',
			'x-on:click' => 'handleCopyGiftCardCode'
		]);
		$out = $field->render();
		return $out;
	}

	/**
	 * Get Markup For Manually Issue Gift Card Button Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardButtonField() {


		$xstore = $this->xstore;

		$label = $this->_('Send Gift Card');
		$options = [
			'label' => $label,
			'type' => 'submit',
			'collapsed' => Inputfield::collapsedNever,
			// 'small' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'secondary' => true,
			// 'columnWidth' => 100,
		];

		$field = $this->pwcommerce->getInputfieldButton($options);
		// $field->attr('x-on:click', 'handleIssueGiftCard');

		$field->attr([
			// 'x-data' => 'ProcessPWCommerceData',
			// 'x-on:click' => 'handleIssueGiftCard()',
			// 'x-bind:disabled' => "!{$xstore}.is_ready_to_send_gift_card",
			// 'x-bind:class' => "{$xstore}.is_ready_to_send_gift_card ? `` : `opacity-50`",
			'x-bind:disabled' => "!checkIsReadyToSendGiftCard()",
			'x-bind:class' => "checkIsReadyToSendGiftCard() ? `` : `opacity-50`",

		]);

		// $errorHandlingMarkup = "<small class='pwcommerce_error block mt-1' x-show='!{$xstore}.is_ready_to_send_gift_card'>" . $this->_('Please complete all required fields') . "</small>";
		$errorHandlingMarkup = "<small class='pwcommerce_error block mt-1' x-show='!checkIsReadyToSendGiftCard()'>" . $this->_('Please complete all required fields') . "</small>";
		// $field->prependMarkup($errorHandlingMarkup);
		$field->appendMarkup($errorHandlingMarkup);

		# -----
		return $field;
	}
	/**
	 * Get Markup For Manually Issue Gift Card Admin Note Textarea Field.
	 *
	 * @return InputfieldTextarea
	 */
	private function getMarkupForManuallyIssueGiftCardAdminNoteTextareaField(): InputfieldTextarea {

		//------------------- note text/content/value (getInputfieldTextarea)

		$options = [
			'id' => "pwcommerce_issue_gift_card_admin_note",
			'name' => "pwcommerce_issue_gift_card_admin_note",
			'label' => $this->_('Admin Note'),
			// 'description' => $this->_('Action note.'),
			'notes' => $this->_('Optionally add a note about this Gift Card. This will only be seen by shop admins.'),
			'collapsed' => Inputfield::collapsedNever,
			'rows' => 2,
			'classes' => 'pwcommerce_note_text',
			'wrapClass' => true,
			// 'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
			'value' => "",
			// @note: to match payment select field
			// @see: renderOrderStatusApplicationCapturePaymentMethodSelectField()
			// 'columnWidth' => 99,
		];
		/** @var InputfieldTextarea $field */
		$field = $this->pwcommerce->getInputfieldTextarea($options);
		$field->addClass('pwcommerce_order_status_action_apply');
		return $field;

	}



	########################

	/**
	 * Get Markup For Manually Issue Gift Card Email Subject Text Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardEmailSubjectTextField() {
		// TODO RENAME NAME, ETC BELOW


		// TODO confirm $xstore!
		//------------------- issue gift card customer email (getInputfieldText)
		// $xstore = "{$this->xstore}.manually_issue_gift_card_data";
		// TODO DELETE WHEN DONE; WE MODEL DIRECT OBJECT!
		$xstoreIssueGiftCardData = "{$this->xstore}.manually_issue_gift_card_data";

		/** @var Array $alpineJSValues */
		$alpineJSValues = $this->getIssueGiftCardDependentInputsAlpineAttributes();
		$hiddenInverseClass = $alpineJSValues['class_hidden_inverse'];
		$opacityClass = $alpineJSValues['class_opacity'];
		$disabled = $alpineJSValues['input_disabled'];

		// ----------

		$selectGiftCardDenominationNote = $this->_('please select a gift card denomination first');
		$extraDescriptionMarkup = "<span :class=\"{$hiddenInverseClass}\"> ($selectGiftCardDenominationNote)</span>.";

		// TODO: FROM TEMPLATE OR EDITABLE? e.g. 'Your shopName £30 Gift Card' in a partial template?

		$shopName = $this->shopGeneralSettings->shop_name;
		if (!empty($shopName)) {
			$defaultValue = sprintf(__("Your Gift Card from %s"), $shopName);
		} else {
			$defaultValue = $this->_('Your Gift Card');
		}

		$description = $this->_('Customer to send gift card to');

		$options = [
			'id' => "pwcommerce_issue_gift_card_customer_email_subject",
			// TODO: not really needed!
			'name' => "pwcommerce_issue_gift_card_customer_email_subject",
			'type' => 'email',
			'label' => $this->_('Subject'),
			'value' => $defaultValue,
			// 'description' => $description . $extraDescriptionMarkup,
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 50,
			'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
		];

		$field = $this->pwcommerce->getInputfieldText($options);
		// allow HTML in description
		$field->entityEncodeText = false;
		$field->attr([
			'x-model' => "{$this->xstore}.customerEmailSubject",
			// ==========
			// if no denonimation selected; opacity at a quarter
			'x-bind:class' => $opacityClass,
			// if no denonimation selected; input is disabled
			'x-bind:disabled' => $disabled,
			'x-ref' => "pwcommerce_issue_gift_card_customer_email_subject",
		]);

		$errorHandlingMarkup = "<small class='pwcommerce_error' x-show='{$this->xstore}.is_error_issue_gift_card_email_subject'>" . $this->_('Invalid email subject') . "</small>";
		// $field->prependMarkup($errorHandlingMarkup);
		$field->appendMarkup($errorHandlingMarkup);


		return $field;
	}

	/**
	 * Get Markup For Manually Issue Gift Card Email Body R T E Field.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardEmailBodyRTEField() {


		$code = $this->pwcommerce->pwcommerceGiftCards->getUniqueGiftCardCode();

		// TODO: FROM TEMPLATE OR EDITABLE? e.g. 'Your £30 gift card for shopName is active. Keep this email or write down your gift card number. <br> 1234-4567-9876-3456' in a partial template?

		$denomination = "£10"; // TODO JUST TESTING; WON'T WORK LIKE THIS SINCE DON'T KNOW VALUE!
		$shopGeneralSettings = $this->pwcommerce->getshopGeneralSettings();
		$shopName = $shopGeneralSettings->shop_name;
		$body = sprintf(__('Your %1$s from %2$s. Your code is %3$s.'), $denomination, $shopName, $code);

		$value = "<p class='gift_card_code'>TODO: TRY AND ADD THE CODE HERE; SO IN SESSION MAYBE? {$body}</p>";


		$options = [
			'id' => "pwcommerce_issue_gift_card_customer_email_body",
			// TODO: not really needed!
			'name' => "pwcommerce_issue_gift_card_customer_email_body",
			'type' => 'email',
			'label' => $this->_('Subject'),
			'notes' => $this->_('Email contents'),
			// 'description' => $this->_('Email contents'),
			'value' => $value,
			// 'description' => $description . $extraDescriptionMarkup,
			'collapsed' => Inputfield::collapsedNever,
			// 'columnWidth' => 50,
			'required' => true,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_override_processwire_inputfield_header_padding_top',
		];

		$field = $this->pwcommerce->getInputfieldCKEditor($options);


		# ------------------
		// TODO MOVE TO INPUTFIELD HELPERS!


		// -----
		return $field;
	}

	/**
	 * Get Markup For Manually Issue Gift Card Dates Error Strings.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardDatesErrorStrings() {
		// SCRIPT for Issue Gift Card Error Strings Configs for ALPINE JS
		// TODO: important that this is INIT'd on time!
		$script = $this->getManuallyIssueGiftCardDatesErrorStringsScript();
		$out = "<div x-init='initIssueGiftCardDatesErrorStringsData'>" . $script . "</div>";
		// --------
		return $out;
	}
	/**
	 * Get Manually Issue Gift Card Dates Error Strings Script.
	 *
	 * @return mixed
	 */
	private function getManuallyIssueGiftCardDatesErrorStringsScript() {
		// TODO REPHRASE?
		$errorStringsArray = [
			// @NOTE: NOT USING START DATE FOR NOW!
			// 'start too late' or 'end too early' errors
			// 'start_date_greater_than_or_equal_to_end_date' => $this->_('Start date cannot be equal to or greater than end date.'),
			// 'end_date_less_than_or_equal_to_start_date' => $this->_('End date cannot be equal to or less than start date.'),
			// 'past' errors
			// 'start_date_is_in_the_past' => $this->_('Start date is in the past.'),
			'end_date_is_in_the_past' => $this->_('End date is in the past.'),
			// 'one NOT empty BUT one is' errors
			// 'start_date_given_but_no_end_date' => $this->_('End date is empty for the given start date.'),
			'no_end_date' => $this->_('End date is empty.'),
			// 'end_date_given_but_no_start_date' => $this->_('Start date is empty for the given end date.'),

		];


		$script = "<script>ProcessWire.config." . $this . "=" . json_encode($errorStringsArray) . ';</script>';
		// --------
		return $script;
	}


	// TODO DELETE THIS AS NO LONGER IN USE OR RETAIN?
	/**
	 * Get Markup For Manually Issue Gift Card Modal.
	 *
	 * @return mixed
	 */
	private function getMarkupForManuallyIssueGiftCardModal() {


		//------------------- process issue gift card modal (getInputfieldMarkup)
		// TODO NEEDED?
		$pageID = $this->page->id;
		$xstore = $this->xstore;
		$ajaxPostURL = $this->ajaxPostURL;
		//
		// TODOe: the pageautocomplete must limit parent_id to its attribute parent!";
		// TODO: UNSURE IF THIS BUTTON SHOULD BE GENERATE OR NOT HAVE IT AND USE A LINK INSTEAD?
		// TODO: BEST ALSO TO HIDE IT IF NOT READY TO GENERATE VARIANTS!
		// TODO: hide or disable if not ready?
		// $applyButtonAttributes = ['x-on:click' => 'handleSaveGeneratedProductVariants', 'x-show' => "{$xstore}.is_ready_for_generate_variants"];
		// TODO; testing use htxm instead
		// $adminEditURL = $this->wire('config')->urls->admin . "page/edit/";
		// $adminEdit = "{$adminEditURL}?id={$pageID}&field={$this->name}&context=dynamically_manage_inputfields";
		// $sessionTokenName = $this->wire('session')->CSRF->getTokenName();



		// TODO LIMIT SENT FIELDS TO ONLY THOSE FROM ISSUE GIFT CARD FORM ELEMENTS!!!
		$applyButtonAttributes = [
			// HTMX
			'hx-post' => $ajaxPostURL,
			// TODO: delete when done: not needed except for file uploads
			//  'hx-encoding' => 'multipart/form-data',
			// @note: we are using htmx out of band in the response!
			// @see: https: //htmx.org/attributes/hx-swap-oob/
			// The hx-swap-oob attribute allows you to specify that some content in a response should be swapped into the DOM somewhere other than the target, that is "Out of Band". This allows you to piggy back updates to other element updates on a response.
			'hx-target' => '#pwcommerce_issue_gift_card_confirm_wrapper',
			// TODO @UPDATE we now target the 'is creating variants please wait markup'
			// TODO: HAVING ISSUES WITH BELOW! NOT GETTING UPDATED. MAYBE IT IS OUT OF BAND? ALSO, CAUSING PREVIEW NOT TO BE HIDDEN! TODO - TRY USING OOB TRUE IN THE RESPONSE P
			// @note: this is a 'p'
			//'hx-target' => '#pwcommerce_product_variants_is_creating_info',
			//    'hx-swap' => 'beforeend',
			// 'hx-swap' => 'innerHTML',
			// @note: we only need these inputs for issue gift card
			// TODO NEED TO ADD HIDDEN INPUTFIELDS FOR HX-VALS BELOW SINCE GETTING OVERRIDEN BY HX PARAMS!
			'hx-params' => "pwcommerce_issue_gift_card_denomination,pwcommerce_issue_gift_card_customer_email,pwcommerce_issue_gift_card_start_date,pwcommerce_issue_gift_card_end_date,pwcommerce_manually_issue_gift_card, pwcommerce_manually_issue_gift_card_context",
			// 'hx-vals' => json_encode(['pwcommerce_manually_issue_gift_card' => true, 'pwcommerce_manually_issue_gift_card_context' => 'gift-card-products']),
			// +++++++++++ ALPINE +++++++++++
			// @note: 'apply button' to generate variants has own bool property being checked whether it is visible.
			// don't show appy button before variants preview have been generated and also when in 'is creating variants' phase (i.e., server request to create variants has been sent)
			// TODO NEEDED?
			'x-show' => "{$xstore}.is_open_issue_gift_card_processing_modal",
			// @note: on click, htmx above will kick in. In alpine.js, we also set a property to swap the display when variants are being generated
			// TODO @update: not needed for now. We use handleHideProductGenerateVariantsPreview() instead
			//'x-on:click' => 'handleIsGeneratingProductVariants',
		];
		// ----
		// TODO NOT NEEDED?
		$cancelButtonAttributes = [
			'x-on:click' => 'resetIssueGiftCardAndClose',
			// don't show cancel button when in 'is creating variants' phase (i.e., server request to create variants has been sent)
			// 'x-show' => "",
		];

		// hx-encoding
		$applyButton = $this->pwcommerce->getModalActionButton($applyButtonAttributes, 'confirm');
		$cancelButton = $this->pwcommerce->getModalActionButton($cancelButtonAttributes, 'cancel');
		// TODO: make header string dynamic? i.e. generate vs edit?
		$header = $this->_("Issue gift card");
		$body = $this->renderBuildIssueGiftCardBody();
		$footer = "<div class='ui-dialog-buttonset'>{$applyButton}{$cancelButton}</div>";
		// TODO NEEDED?
		$xproperty = 'is_open_issue_gift_card_processing_modal';
		$size = '3x-large';
		// wrap content in modal for adding/editing product variants
		// modal options
		$options = [
			// $header The modal title pane markup.
			'header' => $header,
			// $body The main content markup.
			'body' => $body,
			// $footer The footer markup.
			'footer' => $footer,
			// $xstore The alpinejs store with the property that will be modelled to show/hide the modal.
			'xstore' => 'InputfieldPWCommerceGiftCardProductVariantsStore',
			// $xproperty The alpinejs property that will be modelled to show/hide the modal.
			'xproperty' => $xproperty,
			// $size The size of the modal requested.
			'size' => $size,
			// custom handler for x (close) in modal title dialog [instead of the usual ]
			'handler_for_close_modal' => 'resetIssueGiftCardAndClose',
			'handler_for_close_modal_value' => '',
		];
		$out = $this->pwcommerce->getModalMarkup($options);
		return $out;
	}

	/**
	 * Render Build Issue Gift Card Body.
	 *
	 * @return string|mixed
	 */
	private function renderBuildIssueGiftCardBody() {
		// TODO ADD GC VALUE AND CURRENCY SYMBOL TO GC TO ISSUE!
		$currencySymbol = "";
		// if currency locale set..
		// grab symbol; we use on price fields description
		$shopCurrencySymbolString = $this->pwcommerce->renderShopCurrencySymbolString();
		if (strlen($shopCurrencySymbolString)) {
			$currencySymbol = " " . $shopCurrencySymbolString;
		}

		$xstoreIssueGiftCardData = "{$this->xstore}.manually_issue_gift_card_data";
		$out =

			"<div id='pwcommerce_issue_gift_card_confirm_wrapper'>" .
			"<p>" . $this->_("Please check the details below and click 'Confirm' to issue a gift card to the specified email.") . "</p>" .
			# DETAILS #
			// gift card denomination/value/amount
			"<p>" .
			"<span class='opacity-70'>" . $this->_('Denomination') . $currencySymbol . ": </span>" .
			"<span x-text='{$xstoreIssueGiftCardData}.denomination'></span>" .
			"</p>" .
			// gift card recipient email
			"<p>" .
			"<span class='opacity-70'>" . $this->_('Customer Email') . ": </span>" .
			"<span x-text='{$xstoreIssueGiftCardData}.customerEmail'></span>" .
			"</p>" .
			// TODO COMBINE DATES into one markup?
			// gift card start date
			"<p>" .
			"<span class='opacity-70'>" . $this->_('Start Date') . ": " . "</span>" .
			"<span x-text='{$xstoreIssueGiftCardData}.startDate'></span>" .
			"</p>" .
			// gift end start date
			"<p>" .
			"<span class='opacity-70'>" . $this->_('End Date') . ": " . "</span>" .
			"<span x-text='{$xstoreIssueGiftCardData}.endDate'></span>" .
			"</p>" .
			"</div>";
		return $out;
	}

	/**
	 * Get Issue Gift Card Dependent Inputs Alpine Attributes.
	 *
	 * @return mixed
	 */
	private function getIssueGiftCardDependentInputsAlpineAttributes() {
		// @note: here since shared by three inputs + button
		$xstoreIssueGiftCardData = "{$this->xstore}.manually_issue_gift_card_data";
		$invisibleClass = "{ invisible: !{$xstoreIssueGiftCardData}.denominationID || !{$xstoreIssueGiftCardData}.customerEmail}";
		return [
			'class_hidden_inverse' => "!{$xstoreIssueGiftCardData}.denominationID || 'hidden'",
			// 'class_invisible_send_button' => "!{$xstoreIssueGiftCardData}.denominationID && 'invisible'",
			'class_invisible_send_button' => $invisibleClass,
			'class_opacity' => "!{$xstoreIssueGiftCardData}.denominationID ? 'opacity-25' : ''",
			'input_disabled' => "Boolean(!{$xstoreIssueGiftCardData}.denominationID)",
		];
	}

	/**
	 * Is Issuing Gift Cards Possible.
	 *
	 * @return bool
	 */
	private function isIssuingGiftCardsPossible() {
		$singleIssuableGiftCardProductVariant = $this->getGiftCardProductVariants(true);

		return !empty($singleIssuableGiftCardProductVariant);
	}

	/**
	 * Get Markup For Not Possible To Issue Gift Cards.
	 *
	 * @return mixed
	 */
	private function getMarkupForNotPossibleToIssueGiftCards() {
		$label = ucwords($this->issueGiftCardLink);
		$warning1 = $this->_('It is currently not possible to issue a Gift Card from this Gift Card Product.');
		$warning2 = $this->_('You need to add and enable at least one Gift Card Product Variant with a denomination to this Gift Card Product.');
		$out =
			"<div class='pt-2.5'>" .
			"<p>" . $label . "</p>" .
			"<p>" . $warning1 . " " . $warning2 . "</p>" .
			"</div>";
		$this->warning($warning2);
		// ------
		return $out;
	}

	/**
	 * Get Gift Card Product Variants.
	 *
	 * @param bool $isGetSingle
	 * @return mixed
	 */
	private function getGiftCardProductVariants(bool $isGetSingle = false) {
		# --------
		// $selector = "template=" . PwCommerce::GIFT_CARD_PRODUCT_VARIANT_TEMPLATE_NAME . ",parent_id={$this->page->id},". PwCommerce::PRODUCT_STOCK_FIELD_NAME . ".price>0,status<" . Page::statusTrash;

		$stockFieldName = PwCommerce::PRODUCT_STOCK_FIELD_NAME;
		$selectorArray = [
			'template' => PwCommerce::GIFT_CARD_PRODUCT_VARIANT_TEMPLATE_NAME,
			'parent_id' => $this->page->id,
			"{$stockFieldName}.price>" => 0,
			"{$stockFieldName}.enabled" => 1,
			'status<' => Page::statusTrash

		];

		$fields = PwCommerce::PRODUCT_STOCK_FIELD_NAME;

		if ($isGetSingle) {
			// get a single gift card product variant (for checking if 'issue gift card' can be shown)
			$giftCardProductVariants = $this->wire('pages')->getRaw($selectorArray, $fields);
		} else {
			// get all eligible gift card product variants (for denomination selection in 'issue gift card')
			$giftCardProductVariants = $this->wire('pages')->findRaw($selectorArray, $fields);
		}
		// ================








		// ----
		return $giftCardProductVariants;
	}

	// TODO @UPDATE - NO LONGER IN USE SINCE JULY 2023; MANUALLY ISSUED GIFT CARDS ARE NOT LINKED TO GIFT CARD PRODUCTS!
	// TODO DELETE IF NOT IN USE
	/**
	 * Is Ready To Issue Gift Cards.
	 *
	 * @return bool
	 */
	private function isReadyToIssueGiftCards() {
		// CHECK IF WE HAVE AT LEAST ONE GIFT CARD PRODUCT AVAILABLE
		// @NOTE: WE FIND AT LEAST ONE PUBLISHED GIFT CARD VARIANT with a denomination!

		$stockFieldName = PwCommerce::PRODUCT_STOCK_FIELD_NAME;
		$selectorArray = [
			'parent.template' => PwCommerce::GIFT_CARD_PRODUCT_TEMPLATE_NAME,
			'template' => PwCommerce::GIFT_CARD_PRODUCT_VARIANT_TEMPLATE_NAME,
			"{$stockFieldName}.price>" => 0,
			"{$stockFieldName}.enabled" => 1,
			// "status!" => "unpublished", // @note: getting an error here!
			// @note TODO: theoretically, not needed since cannot use GCP GUI to unpublish variants
			'status<' => Page::statusUnpublished,
			// @note: conflicting with unpublished above? for now just ensuring not in trash using parent.template
			// 'status<' => Page::statusTrash


		];

		// get a single gift card product variant (for checking if 'issue gift card' can be shown)
		$oneGiftCardProductVariantID = (int) $this->wire('pages')->getRaw($selectorArray, 'id');


		// ---------
		return !empty($oneGiftCardProductVariantID);
	}

	# >>>>>>>>>>>>>>>>>>> AJAX <<<<<<<<<<<<<<<<<<<

	/**
	 * Handle Ajax Manual Issuge Gift Card Code.
	 *
	 * @return mixed
	 */
	private function handleAjaxManualIssugeGiftCardCode() {
		// TODO: DO WE REALLY NEED A SEPARATE METHOD? LET BE FOR NOW (FOR CONSISTENCY WITH ProcessRenderOrders)
		$out = $this->processAjaxGetRequestForManualIssugeGiftCardCode();

		// =======
		return $out;

	}

	/**
	 * Process Ajax Get Request For Manual Issuge Gift Card Code.
	 *
	 * @return mixed
	 */
	private function processAjaxGetRequestForManualIssugeGiftCardCode() {
		$code = $this->pwcommerce->pwcommerceGiftCards->getUniqueGiftCardCode();
		// $out = "<p>{$code}</p>"
		// *********** SEND RESULT BACK TO HTMX *********
		// return $out;
		return $code;
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ QUICK FILTERS  ~~~~~~~~~~~~~~~~~~

	/**
	 *    get Quick Filters Values.
	 *
	 * @return mixed
	 */
	protected function ___getQuickFiltersValues() {
		$filters = [
			// reset/all
			'reset' => $this->_('All'),
			// active
			'active' => $this->_('Active'),// published
			'draft' => $this->_('Draft'),// unpublished
			// no orders
			'unused' => $this->_('Unused'),
		];

		// ------
		return $filters;
	}

	/**
	 * Get Allowed Quick Filter Values.
	 *
	 * @return mixed
	 */
	private function getAllowedQuickFilterValues() {
		// filters array
		/** @var array $filters */
		$filters = $this->getQuickFiltersValues();
		$allowedQuickFilterValues = array_keys($filters);
		return $allowedQuickFilterValues;
	}

	/**
	 * Get Selector For Quick Filter.
	 *
	 * @return mixed
	 */
	protected function getSelectorForQuickFilter() {
		$input = $this->wire('input');
		$selector = '';
		// NOTE: KEYS -> filter values; VALUEs -> STATUS CONSTANTS
		$allowedQuickFilterValues = $this->getAllowedQuickFilterValues();
		$quickFilterValue = $this->wire('sanitizer')->option($input->pwcommerce_quick_filter_value, $allowedQuickFilterValues);
		if (!empty($quickFilterValue)) {
			// quick filter checks
			// ++++++++++
			if (in_array($quickFilterValue, ['active', 'draft'])) {
				// ACTIVE (PUBLISHED) OR DRAFT (UNPUBLISHED)
				$selector = $this->getSelectorForQuickFilterActive($quickFilterValue);
			} elseif (in_array($quickFilterValue, ['registered', 'guest'])) {
				// REGISTERED (PW USER [ID]) OR GUEST (user_id=0)
				$selector = $this->getSelectorForQuickFilterAccount($quickFilterValue);
			} else if ($quickFilterValue === 'not_in_customer_group') {
				// NOT IN CUSTOMER GROUP
				$selector = $this->getSelectorForQuickFilterNotInCustomerGroup();
			} else if ($quickFilterValue === 'no_orders') {
				// NO ORDERS
				$selector = $this->getSelectorForQuickFilterNoOrder();
			}
		}
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter Active.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterActive($quickFilterValue) {
		$selector = '';
		if ($quickFilterValue === 'active') {
			// PUBLISHED
			$selector = ",status<" . Page::statusUnpublished;
		} else if ($quickFilterValue === 'draft') {
			// UNPUBLISHED
			$selector = ",status>=" . Page::statusUnpublished;
		}
		// ----
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter Account.
	 *
	 * @param mixed $quickFilterValue
	 * @return mixed
	 */
	private function getSelectorForQuickFilterAccount($quickFilterValue) {
		$selector = '';
		if ($quickFilterValue === 'registered') {
			// REGISTERED CUSTOMER/CUSTOMER WITH ACCOUNT (ProcessWire userID)
			$selector = "," . PwCommerce::CUSTOMER_FIELD_NAME . ".user_id>0";
		} else if ($quickFilterValue === 'guest') {
			// GUEST
			$selector = "," . PwCommerce::CUSTOMER_FIELD_NAME . ".user_id<1";
		}
		// ----
		return $selector;

	}

	/**
	 * Get Selector For Quick Filter Not In Customer Group.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterNotInCustomerGroup() {
		$selector = "," . PwCommerce::CUSTOMER_GROUPS_FIELD_NAME . "=''";
		// ----
		return $selector;
	}

	/**
	 * Get Selector For Quick Filter No Order.
	 *
	 * @return mixed
	 */
	private function getSelectorForQuickFilterNoOrder() {
		// e.g.
		// SELECT email
		// FROM field_pwcommerce_order_customer
		// GROUP BY email

		$selector = '';

		$queryOptions = [
			'table' => PwCommerce::ORDER_CUSTOMER_FIELD_NAME,
			'select_columns' => ['email'],
			'group_by_columns' => ['email']
		];

		$results = $this->pwcommerce->processQueryGroupBy($queryOptions);

		if (!empty($results)) {
			$emails = array_column($results, 'email');
			$emailsSelector = implode("|", $emails);
			// NOTE: we want emails of customers without orders!
			$selector = "," . PwCommerce::CUSTOMER_FIELD_NAME . ".email!={$emailsSelector}";
		}

		// ----
		return $selector;

	}
}