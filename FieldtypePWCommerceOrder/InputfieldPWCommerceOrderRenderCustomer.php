<?php

namespace ProcessWire;

/**
 * PWCommerce: InputfieldPWCommerceOrder -> InputfieldPWCommerceOrderRenderCustomer
 *
 * Helper render class for InputfieldPWCommerceOrder.
 * For displaying order customer.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * InputfieldPWCommerceOrderRenderCustomer for PWCommerce
 * Copyright (C) 2022 by Francis Otieno
 * MIT License
 *
 */


class InputfieldPWCommerceOrderRenderCustomer extends WireData
{



	protected $page;
	private $inputfieldOrderCustomer;


	public function __construct($page) {

		$this->page = $page;
		// ----------
		$this->setInputfieldOrderCustomer();
	}

	/**
	 * Get and set InputfieldPWCommerceOrderCustomer to a class property.
	 *
	 * For convenience / reuse.
	 *
	 * @access private
	 * @return void
	 */
	private function setInputfieldOrderCustomer() {
		$inputfieldName = "InputfieldPWCommerceOrderCustomer";
		$this->inputfieldOrderCustomer = $this->wire('modules')->get($inputfieldName);

	}

	/**
	 * Render the entire input area for order
	 *
	 */
	public function ___render() {
		return $this->getOrderCustomerMarkup();
	}

	/**
	 * Get markup for the customer for this order.
	 *
	 * @note: calls the render() method of InputfieldPWCommerceOrderCustomer.
	 *
	 * @access private
	 * @return void
	 */
	private function getOrderCustomerMarkup() {
		// set values to InputfieldPWCommerceOrderCustomer
		$this->inputfieldOrderCustomer->setPage($this->page);
		// @note: we don't need this setField
		// $this->inputfieldOrderCustomer->setField($this->wire('fields')->get('name=pwcommerce_order_customer'));
		$this->inputfieldOrderCustomer->attr('value', $this->page->get(PwCommerce::ORDER_CUSTOMER_FIELD_NAME));
		// get the render() output from the inputfield
		$out = $this->inputfieldOrderCustomer->render();
		// ----------
		return $out;
	}

	public function getOrderCustomerRequiredFields() {
		$requiredFieldsIDs = $this->inputfieldOrderCustomer->getRequiredOrderCustomerInputs();

		// -----
		return $requiredFieldsIDs;
	}
}