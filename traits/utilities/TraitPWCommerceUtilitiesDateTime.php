<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesDateTime
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ DATE AND TIME  ~~~~~~~~~~~~~~~~~~

	/**
	 * Get Date Time Format.
	 *
	 * @return mixed
	 */
	public function getDateTimeFormat() {
		$datetimeFormat = $this->getDateFormat() . ' ' . $this->getTimeFormat();
		// --------------
		return $datetimeFormat;
	}

	/**
	 * Get Date Format.
	 *
	 * @return mixed
	 */
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

	/**
	 * Get Time Format.
	 *
	 * @return mixed
	 */
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
	 * @return mixed
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
	 * @return mixed
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
	 * @param Page $page
	 * @return mixed
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

	/**
	 * Get End Of Last Year Date Time.
	 *
	 * @return \DateTime
	 */
	public function getEndOfLastYearDateTime(): \DateTime {
		$endOfLastYear = new \DateTime('last day of December this year -1 years');
		return $endOfLastYear;
	}

	/**
	 * Get End Of Last Year Timestamp.
	 *
	 * @return int
	 */
	public function getEndOfLastYearTimestamp(): int {
		$endOfLastYear = $this->getEndOfLastYearDateTime();
		$endOfLastYearTimestamp = $endOfLastYear->getTimestamp();
		return $endOfLastYearTimestamp;
	}

	/**
	 * Get Start Of Next Year Date Time.
	 *
	 * @return \DateTime
	 */
	public function getStartOfNextYearDateTime(): \DateTime {
		$startOfNextYear = new \DateTime('first day of January this year +1 years');
		return $startOfNextYear;
	}

	/**
	 * Get Start Of Next Year Timestamp.
	 *
	 * @return int
	 */
	public function getStartOfNextYearTimestamp(): int {
		$startOfNextYear = $this->getStartOfNextYearDateTime();
		$startOfNextYearTimestamp = $startOfNextYear->getTimestamp();
		return $startOfNextYearTimestamp;
	}

	/**
	 * Get This Year.
	 *
	 * @return mixed
	 */
	public function getThisYear() {
		$now = new \DateTime();
		$year = $now->format("Y");
		return $year;
	}

	/**
	 * Get Number Of Days In Given Year.
	 *
	 * @param mixed $yearNumber
	 * @return mixed
	 */
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

	/**
	 * Get Number Of Days For Given Month In Given Year.
	 *
	 * @param mixed $monthNumber
	 * @param mixed $yearNumber
	 * @return mixed
	 */
	public function getNumberOfDaysForGivenMonthInGivenYear($monthNumber, $yearNumber) {
		$monthNumber = (int) $monthNumber;
		$yearNumber = (int) $yearNumber;
		return cal_days_in_month(CAL_GREGORIAN, $monthNumber, $yearNumber);
	}

	/**
	 * Get Number Of Days For Each Month For This Year.
	 *
	 * @return array
	 */
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

	/**
	 * Get Months Names.
	 *
	 * @return mixed
	 */
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

	/**
	 * Get This Years Start And End Months Timestamps.
	 *
	 * @return mixed
	 */
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

	/**
	 * Get Shop Date Format.
	 *
	 * @return mixed
	 */
	public function getShopDateFormat() {
		$datetimeFormat = $this->getDateTimeFormat();
		if (empty($datetimeFormat)) {
			$datetimeFormat = "Y-m-d";
		}
		// -----
		return $datetimeFormat;
	}

	/**
	 * Get Shop Date Only Format.
	 *
	 * @return mixed
	 */
	public function getShopDateOnlyFormat() {
		$dateFormat = $this->getDateFormat();
		if (empty($dateFormat)) {
			$dateFormat = "Y-m-d";
		}
		// -----
		return $dateFormat;
	}


}
