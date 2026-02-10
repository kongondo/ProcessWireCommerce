<?php

namespace ProcessWire;

/**
 * PWCommerce: Order Statuses.
 *
 * Order statuses class for order, payment and fulfilment (includes shipping) statuses.
 * Interacts with the custom database table 'pwcommerce_order_status' as well as defines different order statuses.
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



class PWCommerceGiftCards extends WireData
{





	private $action;
	private $digits;


	/**
	 *   construct.
	 *
	 * @param mixed $options
	 * @return mixed
	 */
	public function __construct($options = null) {
		parent::__construct();
		if (is_array($options)) {
			$this->options = $options;
		}
		// TODO: IS THIS OK? + WE ARE SAVING AS STRING WITH '-' separation after every 4 digits; OK?
		$this->digits = !empty($this->options['gift_card_code_digits']) ? $this->options['gift_card_code_digits'] : PwCommerce::GIFT_CARD_CODE_DIGITS;
		// -----


	}

	/**
	 * Process Gift Card Action.
	 *
	 * @param mixed $input
	 * @return mixed
	 */
	public function processGiftCardAction($input) {

		$this->input = $input;


		$response = null;
		// ----------
		if (!empty($this->options['action'])) {
			if ($this->options['action'] === 'manually_issue_gift_card') {
				$response = $this->processManuallyIssueGiftCard();
			}
		} else {
			// TODO RETURN NULL AS ERROR?!
		}

		// ------
		return $response;
	}


	// ~~~~~~~~~~~~~~~~
	// PROCESS MANUALLY ISSUE GIFT CARD (in backend)

	/**
	 * xxxxx.
	 *
	 * @return mixed
	 */
	private function processManuallyIssueGiftCard() {

		# TODO @UPDATE TUESDAY 22 AUGUST 2023, 0851 - NEEDS TO CHANGE SINCE MANUALLY ISSUED GIFT CARDS NOT CONNECTED TO GIFT CARD PRODUCTS!

		###########

		$input = $this->input;
		// $input2 = $this->wire('input')->post;
		bd($input, __METHOD__ . ': $input at line #' . __LINE__);


		// TODO FOR NOW THE ONLY AJAX REQUESTED EXPECTED HERE IS FOR ISSUING GIFT CARDS!
		// TODO: IN future might change to handle more so will need $context from InputfieldPWCommerceRuntimeMarkup::processDynamicallyManageInputfieldsAjaxRequest
		// ++++++++++
		// TODO CREATE CLASS TO HANDLE ALL PROCESSING OF GIFT CARDS STUFF and pass this request to it!
		// TODO SHOULD WE DO THE COMPARE SAVED DENOM VERSUS THE ONE SENT? JUST IN CASE SOMETHING CHANGED AS THIS WAS BEING ISSUE? I.E. DUAL EDITING! IF SO, THEN SHOW THAT ERROR! YEA, ALSO GOOD FOR CHECKING IF THE VARIANT STILL EXISTS!
		// TODO -> @UPDATE: DENOM CHECK NOT APPLICABLE SINCE WE DON'T SEND IT! IT IS NOT USER EDITABLE ANYWAY! WE ONLY SEND THE GCPV PAGE ID! SO, JUST CHECK THAT -> PAGE EXISTS PLUS HAS DENOMINATION! TODO RECONSIDER IN FUTURE? WHAT IF DUAL EDIT AND DENOM CHANGED AND SENT UNWANTED AMOUNT?! UNLIKELY THOUGH!
		// -------------
		// prepare inputs
		$sanitizer = $this->wire('sanitizer');
		$giftCardProductVariantID = (int) $input->pwcommerce_issue_gift_card_denomination;

		$fields = PwCommerce::PRODUCT_STOCK_FIELD_NAME;
		$giftCardProductVariantStock = $this->wire('pages')->getRaw("id={$giftCardProductVariantID}", $fields);
		bd($giftCardProductVariantStock, __METHOD__ . ': $giftCardProductVariantStock - WE FOUND THE VARIANT TO GET DENOMINATION FROM - at line #' . __LINE__);
		$denomination = 0;
		if (!empty($giftCardProductVariantStock)) {
			if (!empty($giftCardProductVariantStock['price'])) {
				$denomination = (float) $giftCardProductVariantStock['price'];
			}
		}

		bd($denomination, __METHOD__ . ': $denomination at line #' . __LINE__);
		// TODO OK?
		// @see https://processwire.com/api/ref/sanitizer/email/
		// allowIDN (bool|int): Allow internationalized domain names? (default=false) Specify int 2 to also allow UTF-8 in local-part of email [SMTPUTF8] (i.e. bøb).
		$optionsEmail = ['allowIDN' => 2];
		$customerEmail = $sanitizer->email($input->pwcommerce_issue_gift_card_customer_email, $optionsEmail);


		// ------
		$format = "Y-m-d";
		$optionsDates = ['strict' => true];
		// TODO NEED TO CHECK FOR EMPTY BEFORE SANITIZER?
		$startDate = $sanitizer->date($input->pwcommerce_issue_gift_card_start_date, $format, $optionsDates);
		$endDate = $sanitizer->date($input->pwcommerce_issue_gift_card_end_date, $format, $optionsDates);


		bd($startDate, __METHOD__ . ': $startDate - BEFORE EMPTY CHECK -at line #' . __LINE__);
		bd($endDate, __METHOD__ . ': $endDate - BEFORE EMPTY CHECK -at line #' . __LINE__);
		if (empty($startDate) && empty($endDate)) {
			// TODO DO WE NEED TO CHECK IF VALID DATE?! YES! JUST IN CASE
			// TODO CHECK VALID DATES SENT! i.e. manual edit could have messed up
			$startDate = 'INVALID START DATE';
			$endDate = 'INVALID END DATE';
			// TODO IF ONE VALID; NEED TO ERROR OUT SINCE NEED BOTH TO BE VALID!
			bd(empty($startDate) && empty($endDate), __METHOD__ . ': empty($startDate) && empty($endDate) - BOTH DATES INVALID!! at line #' . __LINE__);
		}
		bd($startDate, __METHOD__ . ': $startDate - AFTER EMPTY CHECK -at line #' . __LINE__);
		bd($endDate, __METHOD__ . ': $endDate - AFTER EMPTY CHECK -at line #' . __LINE__);

		$code = $this->getUniqueGiftCardCode();
		bd($code, __METHOD__ . ': $code GENERATED TO ISSUE AS GIFT CARD CODE - at line #' . __LINE__);

		// -------
		// error checking + prepare ERROR response

		// ========
		// GOOD TO GO
		$balance = $denomination;
		bd($balance, __METHOD__ . ': $balance - SAME AS $denomination since first time - at line #' . __LINE__);

		// prepare SUCCESS response
		$out = "<div id='pwcommerce_issue_gift_card_response_wrapper'>" .
			"<p>Yep; we got that ajax request to issue a gift card item. Working on it!</p>" .
			"<p>Below are the details we received. We will move all this processing to a new class for gift cards, i.e. PWCommerceGiftCards.</p>" .
			"<p>" .
			"<span>Denomination page ID: {$giftCardProductVariantID} - we will compare the saved denomination versus the one sent: just a sanity check. Show errors instead of override.</span>" .
			"<span class='block'>Denomination: {$denomination}</span>" .
			"<span class='block'>Customer Email: {$customerEmail}</span>" .
			"<span class='block'>Start Date: {$startDate}</span>" .
			"<span class='block'>End Date: {$endDate}</span>" .
			"</p>" .
			"<p>" .
			"<span class='block'>Gift Card Code: {$code}</span>" .
			"</p>" .
			"</div>";
		;


		// -----
		return $out;
	}

	// ~~~~~~~~~~~~~~~~
	// PROCESS SALE OF GIFT CARD

	/**
	 * xxxxx.
	 *
	 * @return mixed
	 */
	public function xxxProcessSaleOfGiftCard() {
	}
	// ~~~~~~~~~~~~~~~~
	// PROCESS REDEEM GIFT CARD FOR ORDER

	/**
	 * xxxxx.
	 *
	 * @return mixed
	 */
	public function xxxProcessRedeemGiftCardForOrder() {
	}


	////////////
	// ~~~~~~~~~~~~~~~~
	// GIFT CARD FRONTEND

	/**
	 * xxxxx.
	 *
	 * @return mixed
	 */
	public function redeemGiftCardRender() {
		$t = $this->pwcommerce->getPWCommerceTemplate("gift-card-redeem-html.php");
		$t->set("sessionAppliedGiftCards", $this->getSessionAppliedGiftCards());
		$out = $t->render();
		// ------
		return $out;
	}

	/**
	 * Get Session Applied Gift Cards.
	 *
	 * @return mixed
	 */
	public function getSessionAppliedGiftCards() {

		$sessionGiftCards = new WireData();
		$redeemedGiftCardsIDs = $this->getSessionAppliedGiftCardsIDs();
		// bd($redeemedGiftCardsIDs, __METHOD__ . ': $redeemedGiftCardsIDs - GIFT CARD IDs - at line #' . __LINE__);
		if (!empty($redeemedGiftCardsIDs)) {
			$giftCardsInfo = $this->getGiftCardsInfo($redeemedGiftCardsIDs);
			$sessionGiftCards->set('giftCardsInfo', $giftCardsInfo);
		}

		// TODO NEED TO ADD OTHER INFO FROM SESSION? E.G. ALREADY APPLIED, ETC? COULD ALSO ADD THE INFO DIRECTLY TO EACH PAGARRAY VALUE?

		// ======
		return $sessionGiftCards;


	}

	/**
	 * Get Session Applied Gift Cards I Ds.
	 *
	 * @return mixed
	 */
	private function getSessionAppliedGiftCardsIDs() {
		$redeemedGiftCardsIDs = $this->session->get('redeemedGiftCardsIDs');
		// bd($redeemedGiftCardsIDs, __METHOD__ . ': $redeemedGiftCardsIDs - GIFT CARD IDs TO REDEEM TOTAL FROM CHECK - at line #' . __LINE__);
		// ------
		return $redeemedGiftCardsIDs;
	}

	/**
	 * Get Gift Cards Info.
	 *
	 * @param array $giftCardsIDs
	 * @return mixed
	 */
	private function getGiftCardsInfo(array $giftCardsIDs) {
		// TODO -> PAGE ARRAY FROM SELECTOR OR ARRAY?
		$giftCardsInfo = new PageArray();
		foreach ($giftCardsIDs as $giftCardsID) {
			$giftCardInfo = new NullPage();
			$giftCardInfo->id = $giftCardsID;
			$giftCardInfo->redeemable = random_int(10, 35);
			// --
			// add to fake PageArray for testing
			$giftCardsInfo->add($giftCardInfo);

		}
		return $giftCardsInfo;
	}

	/**
	 * Get Gift Card Info.
	 *
	 * @return mixed
	 */
	private function getGiftCardInfo() {

	}

	/**
	 * Process Redeem Gift Card.
	 *
	 * @param mixed $submittedGiftCardCodeString
	 * @return mixed
	 */
	public function processRedeemGiftCard($submittedGiftCardCodeString) {
		// TODO - DO WE NEED TO RETURN ANYTHING? E.G. ERRORS?
		// -------
		$submittedGiftCardCodeString = $this->wire('sanitizer')->text_entities($submittedGiftCardCodeString);
		bd($submittedGiftCardCodeString, __METHOD__ . ': $submittedGiftCardCodeString - cleaned - at line #' . __LINE__);

		$fakeGiftCardID = (int) substr($submittedGiftCardCodeString, -4);
		; // TODO HERE WE WOULD HAVE VALIDATED ALREADY,
		if (!empty($fakeGiftCardID) && strlen($fakeGiftCardID) === 4) {
			/** @var array $redeemedGiftCardsIDs */
			$redeemedGiftCardsIDs = $this->session->redeemedGiftCardsIDs;
			bd($fakeGiftCardID, __METHOD__ . ': $fakeGiftCardID - VALID - at line #' . __LINE__);
			bd($redeemedGiftCardsIDs, __METHOD__ . ': $redeemedGiftCardsIDs - EXISTING GIFT CARD IDS IN SESSION - at line #' . __LINE__);
			// CHECK IF WE HAVE ARRAY OF GIFT CARDS ALREADY SET
			if (!is_array($redeemedGiftCardsIDs)) {
				// NO REDEEMED GIFT CARDS IDS SAVED TO SESSION YET
				$redeemedGiftCardsIDs = [];
				bd($redeemedGiftCardsIDs, __METHOD__ . ': $redeemedGiftCardsIDs - NEW EMPTY ARRAY FOR REDEEMED GIFT CARD IDS TO ADD TO THIS SESSION - at line #' . __LINE__);
			}

			if (!in_array($fakeGiftCardID, $redeemedGiftCardsIDs)) {
				// NEW VALID GIFT CARD CODE TO TRACK FOR ORDER SESSION
				$redeemedGiftCardsIDs[] = $fakeGiftCardID;
				// UPDATE/START REDEEMED GIFT CARDS IDS IN SESSION
				$this->session->set('redeemedGiftCardsIDs', $redeemedGiftCardsIDs);
				bd($redeemedGiftCardsIDs, __METHOD__ . ': $redeemedGiftCardsIDs - ADD NEWLY REDEEMED GIFT CARD ID TO SESSION - at line #' . __LINE__);
			} else {
				// TODO DELETE WHEN DONE
				bd($redeemedGiftCardsIDs, __METHOD__ . ': $redeemedGiftCardsIDs - GIFT CARD ID ALREADY ADDED TO THIS SESSION - at line #' . __LINE__);
			}

			// ---

			$out = "<p>Ajax GC/D CODE out! in PWCommerceCheckout::renderForm</p>";
		} else {
			// TODO!
			// INVALID GIFT CARD CODE
			bd($fakeGiftCardID, __METHOD__ . ': $fakeGiftCardID - INVALID - at line #' . __LINE__);
			$out = "<p>INVALID GIFT CARD CODE! in PWCommerceCheckout::renderForm</p>";
		}


		return $out;
	}



	////////////
	// ~~~~~~~~~~~~~~~~
	// GIFT CARD UTILITIES

	/**
	 * Get Unique Gift Card Code.
	 *
	 * @return mixed
	 */
	public function getUniqueGiftCardCode() {
		// do-while Loop
		do {
			// generate code
			$code = $this->generateUniqueGiftCardCode();

			$giftCardFieldName = PwCommerce::GIFT_CARD;
			$selectorArray = [
				'template' => PwCommerce::GIFT_CARD_TEMPLATE_NAME,
				"{$giftCardFieldName}.code" => $code,
				'status<' => Page::statusTrash

			];

			// have we found an existing GC with the same code?
			// if YES, we generate another code and check again
			$found = $this->wire('pages')->getRaw($selectorArray, 'id');

		} while (!empty($found));

		return $code;
	}

	/**
	 * Generate Unique Gift Card Code.
	 *
	 * @return mixed
	 */
	private function generateUniqueGiftCardCode() {
		$codeRaw = \rand(pow(10, $this->digits - 1) - 1, pow(10, $this->digits) - 1);
		// TODO MIGHT NEED TO REVISIT THIS SPLIT IF MAKE CONFIGURABLE + IF NOT USER FRIENDLY FOR FRONTEND USE?!
		$code = join("-", str_split($codeRaw, 4));
		;

		// -----------
		return $code;
	}

	/**
	 * Get Last Four Digits Of Gift Card Code.
	 *
	 * @param mixed $code
	 * @return mixed
	 */
	public function getLastFourDigitsOfGiftCardCode($code) {
		$codeArray = explode("-", (string) $code);
		$codeLastFourDigits = $codeArray[3];
		//-----
		return $codeLastFourDigits;
	}
}