<?php

namespace ProcessWire;

trait TraitPWCommerceActionsOrder
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ORDER ~~~~~~~~~~~~~~~~~~

	/**
	 * Action Order.
	 *
	 * @return mixed
	 */
	private function actionOrder() {
		$items = $this->items;

		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!
		if (empty($items)) {
			return null;
		}

		//------------------
		// good to go
		$selector = "sort=-created";
		$pages = $this->getItemsToAction($selector);

		// action each item
		// ====================
		if ($this->action === 'invoice_print') {

			// prepare orders invoices for printing
			return $this->actionPrepareOrdersInvoicesForPrinting($pages);
		} elseif ($this->action === 'invoice_email') {
			// prepare orders invoices for emailing
			return $this->actionPrepareOrdersInvoicesForEmailing($pages);
		}
		// TODO:@UPDATE: SATURDAY 2 2 APRIL 2023 -> REMOVED THESE FROM BULK EDIT SINCE WE NOW HANDLE ALL STATUSES; THE LIST IS LONG HENCE DOING THIS IN SINGLE ORDER VIEW
		/*elseif (in_array($this->action, ['payment_mark_as_pending', 'payment_mark_as_paid', 'shipment_delivered'])) {
			// TODO! -> criteria? mark all statuses? or mark shipping status? what about payment pending? ignore? skip?
			return $this->actionMarkOrderAs();
			}*/
	}

	/**
	 * Get New Order Title.
	 *
	 * @param mixed $inputTitle
	 * @return mixed
	 */
	private function getNewOrderTitle($inputTitle) {
		$title = "";
		if (!empty($inputTitle)) {
			// CUSTOM ORDER TITLE
			$title = $this->wire('sanitizer')->text($inputTitle);
		} else {
			// AUTO ORDER TITLE
			// TODO: is this ok or need microtime?
			$title = sprintf(__("Order: %d"), time());
		}
		//---------
		return $title;
	}

	/**
	 * Get Order Action Notice.
	 *
	 * @return mixed
	 */
	private function getOrderActionNotice() {
		$orderActionNotices = [
			// TODO:@UPDATE: SATURDAY 22 APRIL 2023 -> REMOVED THESE FROM BULK EDIT SINCE WE NOW HANDLE ALL STATUSES; THE LIST IS LONG HENCE DOING THIS IN SINGLE ORDER VIEW
			// 'payment_mark_as_pending' => $this->_('pending'),
			// 'payment_mark_as_paid' => $this->_('paid'),
			// 'shipment_delivered' => $this->_('delivered'),
			// ------
			// unknown OK?
			'unknown' => $this->_('unknown action')
		];
		if (!empty($orderActionNotices[$this->action])) {
			$orderNoticeAction = $orderActionNotices[$this->action];
		} else {
			$orderNoticeAction = $orderActionNotices['unknown'];
		}
		// -----
		return $orderNoticeAction;
	}

}
