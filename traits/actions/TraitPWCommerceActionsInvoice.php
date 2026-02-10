<?php

namespace ProcessWire;

trait TraitPWCommerceActionsInvoice
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ INVOICE ~~~~~~~~~~~~~~~~~~

	/**
	 * Action Prepare Orders Invoices For Printing.
	 *
	 * @param PageArray $orders
	 * @return mixed
	 */
	private function actionPrepareOrdersInvoicesForPrinting(PageArray $orders) {
		$t = $this->pwcommerce->getPWCommerceTemplate("invoices.php");
		$t->set("orders", $orders);
		echo $t->render();
		exit();
	}

	/**
	 * Action Prepare Orders Invoices For Emailing.
	 *
	 * @param PageArray $orders
	 * @return mixed
	 */
	private function actionPrepareOrdersInvoicesForEmailing(PageArray $orders) {
		$pwcommerceProcessOrder = $this->pwcommerce->pwcommerceProcessOrder;

		// TODO: ACCESS CHECKS HERE - FOR FUTURE RELEASE!
		if (empty($orders->count())) {
			return null;
		}

		//------------------
		// GOOD TO GO
		$i = 0;
		// action each item
		foreach ($orders as $orderPage) {
			// TODO - NOT NEEDED, OK?
			// skip if page is locked
			// if ($orderPage->isLocked()) {
			// 	continue;
			// }
			//------------------------------
			$pwcommerceProcessOrder->sendConfirmation($orderPage);
			//-------------
			$i++;
		}

		// --------------------
		// prepare messages
		// emails sent
		$notice = sprintf(_n("Emailed %d item.", "Emailed %d items.", $i), $i);

		$result = [
			'notice' => $notice,
			'notice_type' => 'success', // TODO: WILL DETERMINE BASED ON HOW MANY ITEMS WE COULD ACTION
		];

		//-------
		return $result;
	}

}
