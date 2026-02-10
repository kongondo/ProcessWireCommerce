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
		// TODO: needed?

	}

	# ********* CUSTOMERS ***********

	/**
	 * Add Customer.
	 *
	 * @return mixed
	 */
	public function addCustomer() {
		// TODO
	}

	/**
	 * Update Customer.
	 *
	 * @return mixed
	 */
	public function updateCustomer() {
		// TODO
	}

	/**
	 * Delete Customer.
	 *
	 * @return mixed
	 */
	public function deleteCustomer() {
		// TODO
	}

	# ~~~~~~~~~~~~~~~

	/**
	 * Is Valid Customer.
	 *
	 * @return bool
	 */
	public function isValidCustomer() {
		// TODO
		// TODO CHECK UNIQUE EMAIL + VALID EMAIL + ALL REQUIRED INFO (firstName, lastName, email, firstLineOfAddress, city, postCode, country)
	}

	# ********* CUSTOMER GROUPS ***********

	/**
	 * Add Customer Group.
	 *
	 * @return mixed
	 */
	public function addCustomerGroup() {
		// TODO
	}

	/**
	 * Update Customer Group.
	 *
	 * @return mixed
	 */
	public function updateCustomerGroup() {
		// TODO
	}

	/**
	 * Delete Customer Group.
	 *
	 * @return mixed
	 */
	public function deleteCustomerGroup() {
		// TODO
	}

}