<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesDateTime
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ DATE AND TIME  ~~~~~~~~~~~~~~~~~~

	public function getDateTimeFormat() {
		$datetimeFormat = $this->getDateFormat() . ' ' . $this->getTimeFormat();
		// --------------
		return $datetimeFormat;
	}

	public function getDateFormat() {
		$dateFormat = '';
		// check if general settings has date and time formats set
		// if yes, use those, else fallback is config
		$generalSettings = $this->getShopGeneralSettings();

		//------------
		// if general settings DATE FORMAT available
		if (!empty($generalSettings->date_format)) {
			$dateFormat .= $generalSettings->date_format;
		} else {
			// get date format from ProcessWire config
			$dateFormat .= " " . $this->getDateFormatFromConfigDateFormat();
		}

		// --------------
		return $dateFormat;
	}

	public function getTimeFormat() {
		$timeFormat = '';
		// check if general settings has date and time formats set
		// if yes, use those, else fallback is config
		$generalSettings = $this->getShopGeneralSettings();

		//------------
		// if general settings TIME FORMAT available
		if (!empty($generalSettings->time_format)) {
			// @note: we skip relative times (TODO??? for later?)
			if (\strpos($generalSettings->time_format, "!r") === false) {
				$timeFormat .= " {$generalSettings->time_format}";
			} else {
				// skip relative time: get time from ProcessWire config
				$timeFormat .= " " . $this->getTimeFormatFromConfigDateFormat();
			}
		} else {
			// get time format from ProcessWire config
			$timeFormat .= " " . $this->getTimeFormatFromConfigDateFormat();
		}

		// --------------
		return $timeFormat;
	}

	/**
	 * Return the 'time portion' only in the ProcessWire dateFormat config setting.
	 *
	 * Used if we need to replace relative time in some instances or only date was specified in general settings.
	 *
	 * @return string $timeFormat
	 */
	private function getTimeFormatFromConfigDateFormat() {
		$wdt = $this->wire('datetime');
		$timeFormat = str_replace(
			$wdt->getDateFormats(),
			"",
			$this->config->dateFormat
		);
		$timeFormat = trim($timeFormat);
		return $timeFormat;
	}

	/**
	 * Return the 'date portion' only in the ProcessWire dateFormat config setting.
	 *
	 * Used if only time format was specified in general settings.
	 *
	 * @return string $timeFormat
	 */
	private function getDateFormatFromConfigDateFormat() {
		$wdt = $this->wire('datetime');
		$dateFormat = str_replace(
			$wdt->getTimeFormats(),
			"",
			$this->config->dateFormat
		);
		$dateFormat = trim($dateFormat);
		return $dateFormat;
	}

	/**
	 * Build the string for the last created date of of a given page.
	 *
	 * @credits: ProcessPageEdit::buildFormInfo().
	 * @param Page $page The page whose created date we are building.
	 * @return String $createdDate The last created date string.
	 */
	public function getCreatedDate($page) {
		$unknown = '[?]';
		$dateFormat = $this->getDateTimeFormat();
		$lowestDate = strtotime('1974-10-10');
		$createdDate = $page->created > $lowestDate ? date($dateFormat, $page->created) . " " .
			"<span class='detail'>(" . wireRelativeTimeStr($page->created) . ")</span>" : $unknown;
		//--------------
		return $createdDate;
	}

	public function getEndOfLastYearDateTime(): \DateTime {
		$endOfLastYear = new \DateTime('last day of December this year -1 years');
		return $endOfLastYear;
	}

	public function getEndOfLastYearTimestamp(): int {
		$endOfLastYear = $this->getEndOfLastYearDateTime();
		$endOfLastYearTimestamp = $endOfLastYear->getTimestamp();
		return $endOfLastYearTimestamp;
	}

	public function getStartOfNextYearDateTime(): \DateTime {
		$startOfNextYear = new \DateTime('first day of January this year +1 years');
		return $startOfNextYear;
	}

	public function getStartOfNextYearTimestamp(): int {
		$startOfNextYear = $this->getStartOfNextYearDateTime();
		$startOfNextYearTimestamp = $startOfNextYear->getTimestamp();
		return $startOfNextYearTimestamp;
	}

	public function getThisYear() {
		$now = new \DateTime();
		$year = $now->format("Y");
		return $year;
	}

	public function getNumberOfDaysInGivenYear($yearNumber = null) {
		// if not give a year number (e.g. 1980), use this year as default
		$yearNumber = !empty((int) $yearNumber) ? (int) $yearNumber : (int) $this->getThisYear();
		// @note: z	The day of the year (starting from 0);
		// as per Format accepted by DateTimeInterface::format().
		// @note: here we get the day of the year on 31 December
		// @note: we add 1 since the day of the year is zero-based!
		$totalDaysInYear = date('z', strtotime($yearNumber . '-12-31')) + 1;

		// --------
		return $totalDaysInYear;
	}

	public function getNumberOfDaysForGivenMonthInGivenYear($monthNumber, $yearNumber) {
		$monthNumber = (int) $monthNumber;
		$yearNumber = (int) $yearNumber;
		return cal_days_in_month(CAL_GREGORIAN, $monthNumber, $yearNumber);
	}

	public function getNumberOfDaysForEachMonthForThisYear(): array {
		$thisYear = (int) $this->getThisYear();
		$numberOfDaysForEachMonthThisYear = [];
		$monthsNames = $this->getMonthsNames();
		// -----------
		$monthNumber = 1; // @note: 'January'
		foreach ($monthsNames as $monthName) {
			$numberOfDaysForEachMonthThisYear[$monthName] = $this->getNumberOfDaysForGivenMonthInGivenYear($monthNumber, $thisYear);
			// ------
			// increment month
			$monthNumber++;
		}
		// -------------

		return $numberOfDaysForEachMonthThisYear;
	}

	public function getMonthsNames() {
		return [
			$this->_('January'),
			$this->_('February'),
			$this->_('March'),
			$this->_('April'),
			$this->_('May'),
			$this->_('June'),
			$this->_('July'),
			$this->_('August'),
			$this->_('September'),
			$this->_('October'),
			$this->_('November'),
			$this->_('December'),
		];
	}

	public function getThisYearsStartAndEndMonthsTimestamps() {
		$months = $this->getMonthsNames();
		$monthsTimestamps = [];
		//-----------------
		foreach ($months as $month) {
			$endOfLastMonth = new \DateTime("last day of {$month} this year -1 months");
			// ----------
			$startOfNextMonth = new \DateTime("first day of {$month} this year +1 months");
			// -------------
			$monthsTimestamps[$month] = [
				'start_after' => $endOfLastMonth->getTimestamp(),
				'end_before' => $startOfNextMonth->getTimestamp()
			];
		}
		//------------
		return $monthsTimestamps;
	}

	public function getShopDateFormat() {
		$datetimeFormat = $this->getDateTimeFormat();
		if (empty($datetimeFormat)) {
			$datetimeFormat = "Y-m-d";
		}
		// -----
		return $datetimeFormat;
	}

	public function getShopDateOnlyFormat() {
		$dateFormat = $this->getDateFormat();
		if (empty($dateFormat)) {
			$dateFormat = "Y-m-d";
		}
		// -----
		return $dateFormat;
	}


}
