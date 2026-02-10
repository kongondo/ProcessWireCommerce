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


	/**
	 *   construct.
	 *
	 * @param Page $page
	 * @return mixed
	 */
	public function __construct($page) {

		$this->page = $page;
		// ----------
		$this->setInputfieldOrderCustomer();
	}

	/**
	 * Get and set InputfieldPWCommerceOrderCustomer to a class property.
	 *
	 * @return mixed
	 */
	private function setInputfieldOrderCustomer() {
		$inputfieldName = "InputfieldPWCommerceOrderCustomer";
		$this->inputfieldOrderCustomer = $this->wire('modules')->get($inputfieldName);

	}

	/**
	 * Render the entire input area for order
	 *
	 * @return mixed
	 */
	public function ___render() {
		return $this->getOrderCustomerMarkup();
	}

	/**
	 * Get markup for the customer for this order.
	 *
	 * @return mixed
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

	/**
	 * Get Order Customer Required Fields.
	 *
	 * @return mixed
	 */
	public function getOrderCustomerRequiredFields() {
		$requiredFieldsIDs = $this->inputfieldOrderCustomer->getRequiredOrderCustomerInputs();

		// -----
		return $requiredFieldsIDs;
	}
}