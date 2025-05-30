<?php

namespace ProcessWire;

/**
 * PWCommerce: Customers.
 *
 * Customers class.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceCustomers for PWCommerce
 * Copyright (C) 2023 by Francis Otieno
 * MIT License
 *
 */

class PWCommerceCustomers extends WireData
{

	// TODO WIP

	private $options;



	public function __construct($options = null) {
		parent::__construct();
		if (is_array($options)) {
			$this->options = $options;
		}
		// TODO: needed?

	}

	# ********* CUSTOMERS ***********

	public function addCustomer() {
		// TODO
	}

	public function updateCustomer() {
		// TODO
	}

	public function deleteCustomer() {
		// TODO
	}

	# ~~~~~~~~~~~~~~~

	public function isValidCustomer() {
		// TODO
		// TODO CHECK UNIQUE EMAIL + VALID EMAIL + ALL REQUIRED INFO (firstName, lastName, email, firstLineOfAddress, city, postCode, country)
	}

	# ********* CUSTOMER GROUPS ***********

	public function addCustomerGroup() {
		// TODO
	}

	public function updateCustomerGroup() {
		// TODO
	}

	public function deleteCustomerGroup() {
		// TODO
	}

}