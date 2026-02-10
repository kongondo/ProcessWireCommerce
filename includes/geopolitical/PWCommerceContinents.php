<?php

namespace ProcessWire;

/**
 * PWCommerce: Continents
 *
 * Class to deal with Continents for PWCommerce general use.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceContinents for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

class PWCommerceContinents extends WireData
{

	/**
	 * Get Continents.
	 *
	 * @return mixed
	 */
	public function getContinents() {
		$continents = [
			[
				"id" => "AFR",
				"name" => $this->_("Africa"),
			],
			[
				"id" => "ANT",
				"name" => $this->_("Antarctica"),
			],
			[
				"id" => "ASI",
				"name" => $this->_("Asia"),
			],
			[
				"id" => "EUR",
				"name" => $this->_("Europe"),
			],
			[
				"id" => "NAM",
				"name" => $this->_("North America"),
			],
			[
				"id" => "OCE",
				"name" => $this->_("Oceania"),
			],
			[
				"id" => "SAM",
				"name" => $this->_("South America"),
			],
		];
		return $continents;
	}
}
