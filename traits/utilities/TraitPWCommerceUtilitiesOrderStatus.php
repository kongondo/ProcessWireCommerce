<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesOrderStatus
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ORDER STATUS ~~~~~~~~~~~~~~~~~~


	public function getOrderLineItemFulfilmentStatus() {
		// TODO: UNSURE OF THIS ONE? GET? SET?
		// 7. STATUSES
		// 'fulfilment_status' => (int) $value->fulfilmentStatus, // +++
	}

	public function getOrderLineItemPaymentStatus() {
		// TODO: UNSURE OF THIS ONE? GET? SET?
		// 7. STATUSES
		// 'payment_status' => (int) $value->paymentStatus, // +++
	}


	/**
	 * Return associated array with order, payment and fulfilment statuses names.
	 *
	 * @param WireData $order The $order whose combined statuses to return.
	 * @return array $statuses Associated array with statuses.
	 */
	public function getOrderCombinedStatuses(WireData $order) {

		// ========
		// this combines order status, payment status and fulfilment status in one array
		// can be used to create combined status text
		// e.g. paid / awaiting fulfilment, etc
		$statuses = [];
		// ----------
		// get and add the order status
		$orderStatusName = $this->getOrderStatusName($order);
		$statuses['order'] = $orderStatusName;
		// ----------
		// get and add the payment status
		$orderPaymentStatusName = $this->getOrderPaymentStatusName($order);
		$statuses['payment'] = $orderPaymentStatusName;
		// ------
		// get and add the fulfilment status
		$orderFulfilmentStatusName = $this->getOrderFulfilmentStatusName($order);
		$statuses['fulfilment'] = $orderFulfilmentStatusName;
		// ----------

		return $statuses;
	}

	public function getOrderStatusName(WireData $order) {
		// @note: if order is draft then just return draft!
		if ((int) $order->orderStatus === PwCommerce::ORDER_STATUS_DRAFT) {
			$orderStatusName = $this->_('Draft order');
		} else {
			$orderStatusName = $this->_('Unknown');
			foreach ($this->getAllOrderStatusDefinitionsFromDatabase() as $statusDefinition) {
				$statusCode = (int) $statusDefinition['status_code'];
				if ((int) $order->orderStatus === $statusCode) {
					$orderStatusName = $statusDefinition['name'];
					break;
				}
			}
			// TODO HOW TO TRANSLATE THIS? CHECK WITH FILE ONES?
			//---------
			return $orderStatusName;
		}
	}

	public function getOrderFulfilmentStatusName(WireData $order) {
		// @note: if order is draft then just return draft!
		if ((int) $order->orderStatus === PwCommerce::ORDER_STATUS_DRAFT) {
			$orderFulfilmentStatusName = $this->_('Draft order');
		} else {
			$orderFulfilmentStatusName = $this->_('Unknown');
			// ------------
			foreach ($this->getAllOrderStatusDefinitionsFromDatabase() as $statusDefinition) {
				$statusCode = (int) $statusDefinition['status_code'];
				if ((int) $order->fulfilmentStatus === $statusCode) {
					$orderFulfilmentStatusName = $statusDefinition['name'];
					break;
				}
			}
			// TODO HOW TO TRANSLATE THIS? CHECK WITH FILE ONES?
		}
		//---------
		return $orderFulfilmentStatusName;
	}

	public function getOrderPaymentStatusName(WireData $order) {
		// @note: if order is draft then just return draft!
		if ((int) $order->orderStatus === PwCommerce::ORDER_STATUS_DRAFT) {
			$orderPaymentStatusName = $this->_('Draft order');
		} else {
			$orderPaymentStatusName = $this->_('Unknown');
			foreach ($this->getAllOrderStatusDefinitionsFromDatabase() as $statusDefinition) {
				$statusCode = (int) $statusDefinition['status_code'];
				if ((int) $order->paymentStatus === $statusCode) {
					$orderPaymentStatusName = $statusDefinition['name'];
					break;
				}
			}
			// TODO HOW TO TRANSLATE THIS? CHECK WITH FILE ONES?
			//---------
			return $orderPaymentStatusName;
		}
	}




}