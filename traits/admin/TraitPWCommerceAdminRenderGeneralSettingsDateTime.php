<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsDateTime
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ DATE AND TIME  ~~~~~~~~~~~~~~~~~~

	// @credits: adapted from FieldtypeDatetime::___getConfigInputfields
	/**
	 * Get Date Formats.
	 *
	 * @return mixed
	 */
	private function getDateFormats() {

		$generalSettings = $this->generalSettings;
		$dateFormats = [];
		//-------------
		$wdt = $this->wire('datetime');
		//--------------
		$date = strlen(date('jn')) < 4 ? time() : strtotime('2016-04-08 5:10:02 PM');
		$dateOutputFormat = !empty($generalSettings['date_format']) ? $generalSettings['date_format'] : '';

		//-------------
		foreach ($wdt->getDateFormats() as $format) {
			$dateFormatted = $wdt->formatDate($date, $format);
			if ($format == 'U') {
				$dateFormatted .= " " . $this->_('(unix timestamp)');
			}
			$dateFormats[$format] = "$dateFormatted [$format]";
		}
		//--------------

		return $dateFormats;
	}

	// @credits: FieldtypeDatetime::___getConfigInputfields
	/**
	 * Get Time Formats.
	 *
	 * @return mixed
	 */
	private function getTimeFormats() {
		$generalSettings = $this->generalSettings;
		$timeFormats = [];
		//----------
		$wdt = $this->wire('datetime');
		//--------------
		$date = strtotime('5:10:02 PM');
		$timeOutputFormat = !empty($generalSettings['time_format']) ? $generalSettings['time_format'] : '';

		//----------
		foreach ($wdt->getTimeFormats() as $format) {
			$timeFormatted = $wdt->formatDate($date, $format);
			$timeFormats[$format] = "$timeFormatted [$format]";
		}

		//---------

		return $timeFormats;
	}

	/**
	 * Get Time Zone Identifiers.
	 *
	 * @return mixed
	 */
	private function getTimeZoneIdentifiers() {
		$timezones = [];
		// @note: we need the 'key' otherwise if only value, selectize will send the input as a prefixed index in the array, e.g. _29
		foreach (\DateTimeZone::listIdentifiers() as $tz) {
			$timezones[$tz] = $tz;
		}
		//------------

		return $timezones;
	}

}
