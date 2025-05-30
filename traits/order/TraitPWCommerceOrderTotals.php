<?php

namespace ProcessWire;


trait TraitPWCommerceOrderTotals
{

	public function getOrderGrandTotalMoney()
	{



		// TODO RENAME THIS METHOD! WE NEED TO BASE ON CART! NOT ORDER!

		$orderTotalPriceAmount = (float) $this->getOrder()->totalPrice;


		$orderGrandTotalMoney = $this->money($orderTotalPriceAmount);
		return $orderGrandTotalMoney;
	}

	public function getOrderTaxTotals(WireArray $orderLineItems)
	{

		$taxes = [];

		foreach ($orderLineItems as $orderLineItem) {

			if ($orderLineItem->taxAmountTotal) {
				$taxName = $orderLineItem->taxName;
				$taxTotalAmount = $orderLineItem->taxAmountTotal;
				if (isset($taxes[$taxName]))
					$taxes[$taxName] += $taxTotalAmount;
				else
					$taxes[$taxName] = $taxTotalAmount;
			}
		}

		return $taxes;
	}

	public function getOrderLineItemsTotalDiscountedWithTax($orderPage = null)
	{


		// ------------------
		/** @var WireArray $orderLineItems */
		$orderLineItems = $this->getOrderLineItems($orderPage);


		if ($orderLineItems->count()) {
			// create money object
			$orderTotalPriceDiscountedWithTaxMoney = $this->money(0);
			// ------------------
			foreach ($orderLineItems as $orderLineItem) {
				/** @var WireData $orderLineItem */
				// TODO: THE OK TOTAL HERE?
				$totalPriceDiscountedWithTaxAmount = $orderLineItem->totalPriceDiscountedWithTax;

				// $totalPriceDiscountedWithTax = floatval($totalPriceDiscountedWithTaxAmount);
				if (empty($totalPriceDiscountedWithTaxAmount)) {
					continue;
				}

				# ------------

				// --------
				// SUM IT UP for line item
				// add to money object
				$currentorderTotalPriceDiscountedWithTaxMoney = $this->money($totalPriceDiscountedWithTaxAmount);
				$orderTotalPriceDiscountedWithTaxMoney = $orderTotalPriceDiscountedWithTaxMoney->add($currentorderTotalPriceDiscountedWithTaxMoney);
			}
		}

		// ------------------
		$orderTotalPriceDiscountedWithTaxAmount = 0;

		if (!empty($orderTotalPriceDiscountedWithTaxMoney)) {
			$orderTotalPriceDiscountedWithTaxAmount = $this->getWholeMoneyAmount($orderTotalPriceDiscountedWithTaxMoney);
		}



		return $orderTotalPriceDiscountedWithTaxAmount;
		//
	}
}
