<?php

namespace ProcessWire;

/**
 * PWCommerce: Order Statuses.
 *
 * Order statuses class for order, payment and fulfilment (includes shipping) statuses.
 * Interacts with the custom database table 'pwcommerce_order_status' as well as defines different order statuses.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceActions for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

trait TraitPWCommerceOrderStatus
{
	/**
	 * Set Order Statuses After Order Completion.
	 *
	 * @param mixed $orderStatus
	 * @param mixed $paymentStatus
	 * @param mixed $fulfilmentStatus
	 * @return mixed
	 */
	private function setOrderStatusesAfterOrderCompletion($orderStatus, $paymentStatus, $fulfilmentStatus)
	{
		// get the order page
		// @note: could have been set in TraitPWCommercePayment::postProcessPayment in case 'session was lost'
		$orderPage = $this->getOrderPage();

		$orderPage->of(false);
		$order = $orderPage->get(PwCommerce::ORDER_FIELD_NAME);
		// ------
		// ORDER STATUS
		// @note: we are here since order has been paid OR invoice raised
		// but since we don't know if some items are yet to be delivered
		// we set status as OPEN
		$order->orderStatus = $orderStatus;

		// PAYMENT STATUS
		// @note: we are here since order has been paid OR invoice raised
		$order->paymentStatus = $paymentStatus;

		// FULFILMENT STATUS
		// @note: we are here since order has been paid OR invoice raised
		// but since we don't know if some items are yet to be delivered
		// we set status as AWAITING FULFILMENT
		$order->fulfilmentStatus = $fulfilmentStatus;
		// set order as paid (as per ) $this->captureOrder()
		$orderPage->set(PwCommerce::ORDER_FIELD_NAME, $order);
		$orderPage->save(PwCommerce::ORDER_FIELD_NAME);
	}

	//
	// @note:
	// - stasuses have four digits
	// - lowest possible is 1000
	// - highest possible is 9999
	// - however, technically, can increase in future since we are using a SMALLINT!
	// - we have three status groups: 'ORDER', 'PAYMENT' and 'FULFILMENT'
	// - FULFILMENT includes SHIPPING status
	// - some statuses can be applied to order line items as well, e.g. 'damaged', 'refunded', etc
	// - NUMBERING {just for convenience}
	// 1000 - 2999 => ORDER STATUSES: {1XXX, 2XXX}
	// 3000 - 4999 => PAYMENT STATUSES: {3XXX, 4XXX}
	// 5000 - 6999 => FULFILMENT STATUSES: {5XXX, 6XXX}
	// // FUTURE USE IF NEEDED
	// 7000 - 9999 => OTHER {7XXX, 8XXX, 9XXX}
	//

	/**
	 * Get Statuses.
	 *
	 * @return mixed
	 */
	public function getStatuses()
	{
		// TODO NEED TO MAKE USE OF THESE IN INPUTFIELDSELECTOR IN DASHBOARDS TO SHOW FRIENDLY FIELDS AND MAKE QUERYABLE INSTEAD OF USING STATUS CODES -> FUTURE RELEASE!
		// @note: for convenience: final values stored in Database when PWCommerce is installed!

		$statuses = [
			#### ORDER STATUS ###
			# -> 1000 - 2999 => ORDER {1XXX, 2XXX} <-
			1000 => $this->_('Draft Order'),
			// draft (manual) order
			1001 => $this->_('Abandoned'),
			// @note: customer did not place order.
			// @note: same as 'open'!
			1002 => $this->_('Pending'),
			// @note: can still use text 'open' in stats cards
			1003 => $this->_('Manual verification required'),
			// @note: not very sure but stays for now
			1004 => $this->_('Declined'),
			// @note: TODO: unsure (since we have 'refused' (although this is a customer induced action). we also have 'failed' for payments) - but stays for now
			1005 => $this->_('Disputed'),

			//
			// ^
			// | //// in betweens if needed ////
			// v
			//

			2000 => $this->_('Cancelled'),
			2999 => $this->_('Completed'),

			///////////////////////////////////

			#### PAYMENT STATUS ###
			# -> 3000 - 4999 => ORDER {3XXX, 4XXX} <-
			3000 => $this->_('Awaiting payment'),
			3001 => $this->_('Authentication required'),
			// TODO keep it?
			3002 => $this->_('Failed'),
			3003 => $this->_('Authorised'),
			3004 => $this->_('Overdue'),
			3005 => $this->_('Unpaid'),

			//
			// ^
			// |
			// Voided #UNSURE??# CAN ALWAYS ADD IN FUTURE
			// | //// in betweens if needed ////
			// v
			//

			3999 => $this->_('Partially paid'),
			4000 => $this->_('Paid'),
			4998 => $this->_('Partially refunded'),
			4999 => $this->_('Refunded'),

			///////////////////////////////////

			#### SHIPPING/fulfilment STATUS ###
			#  -> 5000 - 6999 => ORDER {5XXX, 6XXX} <-
			// @note: use this as a companion for a comparable orders status,  'abandoned'
			5000 => $this->_('Void fulfilment'),
			// @note: use this as a companion for a comparable orders status, e.g. 'pending'
			5001 => $this->_('Awaiting fulfilment'),
			5002 => $this->_('On hold'),
			5003 => $this->_('Scheduled'),
			// @note: e.g. for subscriptions
			5004 => $this->_('Awaiting shipment'),
			5005 => $this->_('Shipment delayed'),

			//
			// ^
			// | //// in betweens if needed ////
			// v
			//

			6000 => $this->_('Partially shipped'),
			// @note: TODO: unsure if to use this or partially fulfilled or that could mean something else? yes keep both since can ship and deliver after which partially fulfilled kicks in!
			6001 => $this->_('Shipped'),
			6002 => $this->_('Awaiting pickup'),
			6003 => $this->_('Partially fulfilled'),
			6004 => $this->_('Fulfilled'),
			// delivered
			6005 => $this->_('Shipment damaged'),
			6006 => $this->_('Shipment lost - Customer'),
			6007 => $this->_('Shipment lost - Courier'),
			6008 => $this->_('Delivery refused') // @see declined!
		];
		//---
		return $statuses;
	}

	/**
	 * Get Order Status By Status Code.
	 *
	 * @param mixed $code
	 * @return mixed
	 */
	public function getOrderStatusByStatusCode($code)
	{
		$status = null;
		if (!empty((int) $code)) {
			$statuses = $this->getStatuses();
			$status = !empty($statuses[$code]) ? $statuses[$code] : null;
		}
		// -----
		return $status;
	}

	/**
	 * Get Order Only Statuses.
	 *
	 * @return mixed
	 */
	public function getOrderOnlyStatuses()
	{
		# -> 1000 - 2999 => ORDER {1XXX, 2XXX} <-
		// requires PHP 7.4 -> it introduced arrow functions
		$statuses = $this->getStatuses();
		$orderOnlyStatuses = array_filter(
			$statuses,
			fn($key) => (int) $key >= PwCommerce::ORDER_STATUS_MIMINUM_FLAG && (int) $key <= PwCommerce::ORDER_STATUS_MAXIMUM_FLAG,
			ARRAY_FILTER_USE_KEY
		); //------
		return $orderOnlyStatuses;
	}
	/**
	 * Get Payment Only Statuses.
	 *
	 * @return mixed
	 */
	public function getPaymentOnlyStatuses()
	{
		# -> 3000 - 4999 => ORDER {3XXX, 4XXX} <-
		// requires PHP 7.4 -> it introduced arrow functions
		$statuses = $this->getStatuses();
		$paymentOnlyStatuses = array_filter(
			$statuses,
			fn($key) => (int) $key >= PwCommerce::PAYMENT_STATUS_MIMINUM_FLAG && (int) $key <= PwCommerce::PAYMENT_STATUS_MAXIMUM_FLAG,
			ARRAY_FILTER_USE_KEY
		); //------
		return $paymentOnlyStatuses;
	}
	/**
	 * Get Fulfilment Only Statuses.
	 *
	 * @return mixed
	 */
	public function getFulfilmentOnlyStatuses()
	{
		#  -> 5000 - 6999 => ORDER {5XXX, 6XXX} <-
		// requires PHP 7.4 -> it introduced arrow functions
		$statuses = $this->getStatuses();
		$fulfilmentOnlyStatuses = array_filter(
			$statuses,
			fn($key) => (int) $key >= PwCommerce::FULFILMENT_STATUS_MIMINUM_FLAG && (int) $key <= PwCommerce::FULFILMENT_STATUS_MAXIMUM_FLAG,
			ARRAY_FILTER_USE_KEY
		); //------
		return $fulfilmentOnlyStatuses;
	}
	/**
	 * Get Statuses Descriptions.
	 *
	 * @return mixed
	 */
	public function getStatusesDescriptions()
	{
		// @note: for TRANSLATION & convenience: final values stored in Database when PWCommerce is installed!
		// TODO REVISIT THESE DESCRIPTIONS!

		$statuses = [
			#### ORDER STATUS ###
			# -> 1000 - 2999 => ORDER {1XXX, 2XXX} <-
			// draft order
			1000 => $this->_('Manual draft order.'),
			// abandoned
			1001 => $this->_('Customer did not complete checkout.'),
			// @note: customer did not place order.
			//---------------
			// pending
			1002 => $this->_('Payment received (paid) and stock has been reduced; order is awaiting fulfilment.'),
			// @note: can still use text 'open' in stats cards
			//---------------
			// Manual verification required
			1003 => $this->_('Order on hold while some aspect, such as tax-exempt documentation, is manually confirmed. Orders with this status must be updated manually. Capturing funds or other order actions will not automatically update the status of an order marked Manual Verification Required.'),
			// @note: not very sure but stays for now
			//---------------
			// Declined
			1004 => $this->_('Seller has marked the order as declined.'),
			// @note: TODO: unsure (since we have 'refused' (although this is a customer induced action). we also have 'failed' for payments) - but stays for now
			//---------------
			// Disputed
			1005 => $this->_('Customer has initiated a dispute resolution process for the transaction that paid for the order or the seller has marked the order as a fraudulent order.'),

			//
			// ^
			// | //// in betweens if needed ////
			// v
			//
			//---------------
			// Cancelled
			2000 => $this->_('Seller or customer has cancelled an order, due to a stock inconsistency or other reasons. Stock levels will automatically update depending on your Inventory Settings. Canceling an order will not refund the order.'),
			//---------------
			// Completed
			2999 => $this->_('Order has been shipped/picked up, and receipt is confirmed; client has paid for their digital product, and their file(s) are available for download.'),
			// DRAFT ORDER

			///////////////////////////////////

			#### PAYMENT STATUS ###
			# -> 3000 - 4999 => ORDER {3XXX, 4XXX} <-
			//---------------
			// Awaiting payment
			3000 => $this->_('Customer has completed the checkout process, but payment has yet to be confirmed. Authorise-only transactions that are not yet captured have this status.'),
			//---------------
			// Authentication required
			3001 => $this->_('Awaiting action by the customer to authenticate the transaction and/or complete SCA requirements.'),
			// TODO keep it?
			//---------------
			// Failed
			3002 => $this->_('Payment failed or was declined (unpaid) or requires authentication (SCA).'),
			//---------------
			// Authorised
			3003 => $this->_('Depending on your checkout settings, payments are either captured manually or automatically. If your store is set up for manual capture, then new credit card payments have a status of Authorised.'),
			//---------------
			// Overdue
			3004 => $this->_('An order has not yet been paid by the due date set in the payment terms.'),
			//---------------
			// Unpaid
			3005 => $this->_('Payment has not yet been captured.'),

			//
			// ^
			// |
			// Voided #UNSURE??# CAN ALWAYS ADD IN FUTURE
			// | //// in betweens if needed ////
			// v
			//

			//---------------
			// Partially paid
			3999 => $this->_('A credit card payment has been captured, or a payment using an offline or custom payment method has been marked as received but the capture is less than the full amount of the order.'),
			//---------------
			// Paid
			4000 => $this->_('A credit card payment has been captured, or a payment using an offline or custom payment method has been marked as received.'),
			//---------------
			// Partially refunded
			4998 => $this->_('Seller has partially refunded the order.'),
			//---------------
			// Refunded
			4999 => $this->_('Seller has used the Refund action to refund the whole order.'),

			///////////////////////////////////

			#### SHIPPING/fulfilment STATUS ###
			#  -> 5000 - 6999 => ORDER {5XXX, 6XXX} <-
			//---------------
			// TODO NEED TO ADD A VOID STATUS HERE! I.E., TO ACCOMPANY ABANDONED CHECKOUT!
			// Void fulfilment (due to abandoned checkout)
			5000 => $this->_('Customer did not complete checkout. Fulfilment is void.'),
			// Awaiting fulfilment
			5001 => $this->_('Customer has completed the checkout process and payment has been confirmed.'),
			// @note: use this as a companion for a comparable orders status, e.g. 'pending'
			//---------------
			// On hold @see also manual verification required
			5002 => $this->_('Awaiting payment - stock is reduced, but you need to confirm payment.'),
			//---------------
			// Scheduled
			5003 => $this->_('Order is marked as scheduled for future fulfilment. For instance, for a pre-paid subscription order.'),
			// @note: e.g. for subscriptions
			//---------------
			// Awaiting shipment
			5004 => $this->_('Order has been pulled and packaged and is awaiting collection from a shipping provider.'),
			//---------------
			// Shipment delayed
			5005 => $this->_('Shipment of the order has been delayed.'),

			//
			// ^
			// | //// in betweens if needed ////
			// v
			//

			//---------------
			// Partially shipped
			6000 => $this->_('Part of the order has been shipped, but receipt has not been confirmed.'),
			// @note: TODO: unsure if to use this or partially fulfilled or that could mean something else? yes keep both since can ship and deliver after which partially fulfilled kicks in!
			//---------------
			// Shipped
			6001 => $this->_('Order has been shipped, but receipt has not been confirmed.'),
			//---------------
			//  Awaiting pickup
			6002 => $this->_('Order has been packaged and is awaiting customer pickup from a seller-specified location.'),
			//---------------
			// Partially fulfilled
			6003 => $this->_('Part of the order has been shipped and receipt has been confirmed.'),
			//---------------
			// Fulfilled
			6004 => $this->_('Order has been shipped and receipt has been confirmed.'),
			//---------------
			// Shipment damaged
			6005 => $this->_('Customer reports shipment is damaged.'),
			//---------------
			// Shipment lost - Customer
			6006 => $this->_('Customer claims shipment was never delivered.'),
			// Shipment lost - Courier
			6007 => $this->_('Courier reports that shipment is lost.'),
			//---------------
			// Delivery refused
			6008 => $this->_('Customer refused to accept the delivery.') // @see declined!
		];
		//---
		return $statuses;
	}

	/**
	 * Get Order Description By Status Code.
	 *
	 * @param mixed $code
	 * @return mixed
	 */
	public function getOrderDescriptionByStatusCode($code)
	{
		$statusDescription = null;
		if (!empty((int) $code)) {
			$statusesDescriptions = $this->getStatusesDescriptions();
			$statusDescription = !empty($statusesDescriptions[$code]) ? $statusesDescriptions[$code] : null;
		}
		// -----
		return $statusDescription;
	}

	/**
	 * Get Order Status Type String By Status Code.
	 *
	 * @param mixed $code
	 * @return mixed
	 */
	public function getOrderStatusTypeStringByStatusCode($code)
	{
		// here we get the translated string for status type
		$statusTypeString = "";
		$statusTypes = $this->orderStatusTypes();
		if (!empty((int) $code)) {
			$orderOnlyStatuses = $this->getOrderOnlyStatuses();
			$paymentOnlyStatuses = $this->getPaymentOnlyStatuses();
			$fulfilmentOnlyStatuses = $this->getFulfilmentOnlyStatuses();

			if (!empty($orderOnlyStatuses[$code])) {
				$statusTypeString = $statusTypes['order_status'];
			} else if (!empty($paymentOnlyStatuses[$code])) {
				$statusTypeString = $statusTypes['payment_status'];
			} else if (!empty($fulfilmentOnlyStatuses[$code])) {
				$statusTypeString = $statusTypes['fulfilment_status'];
			}
		}
		// -----
		return $statusTypeString;
	}

	/**
	 * Get Order Status Type By Status Code.
	 *
	 * @param mixed $code
	 * @return mixed
	 */
	public function getOrderStatusTypeByStatusCode($code)
	{
		// here we get the key fo the status type
		// for use in processing actioning the status -> for comparison/determining the status type
		$statusType = null;
		if (!empty((int) $code)) {
			$orderOnlyStatuses = $this->getOrderOnlyStatuses();
			$paymentOnlyStatuses = $this->getPaymentOnlyStatuses();
			$fulfilmentOnlyStatuses = $this->getFulfilmentOnlyStatuses();

			if (!empty($orderOnlyStatuses[$code])) {
				$statusType = 'order_status';
			} else if (!empty($paymentOnlyStatuses[$code])) {
				$statusType = 'payment_status';
			} else if (!empty($fulfilmentOnlyStatuses[$code])) {
				$statusType = 'fulfilment_status';
			}
		}
		// -----
		return $statusType;
	}

	/**
	 * Order Status Types.
	 *
	 * @return mixed
	 */
	public function orderStatusTypes()
	{
		return [
			'order_status' => $this->_('Order Status'),
			'payment_status' => $this->_('Payment Status'),
			'fulfilment_status' => $this->_('Shipping Status'),
		];
	}

	// ~~~~~~~~~~~~~~~~~~~
	// SELECT FROM DATABASE
	// TODO - SELECT * WHERE ID=XXXX LIMIT 1 -> NEED ONLY ONE ROW

	/**
	 * Get Order Status Abandoned Definition.
	 *
	 * @return mixed
	 */
	public function getOrderStatusAbandonedDefinition()
	{
		return $this->getOrderStatusDefinitionFromDatabaseByStatusCode(PwCommerce::ORDER_STATUS_ABANDONED);
	}

	/**
	 * Get Order Status Open Definition.
	 *
	 * @return mixed
	 */
	public function getOrderStatusOpenDefinition()
	{
		// @NOTE: SAME AS 'PENDING'!
		return $this->getOrderStatusDefinitionFromDatabaseByStatusCode(PwCommerce::ORDER_STATUS_OPEN);
	}

	/**
	 * Get Order Status Cancelled Definition.
	 *
	 * @return mixed
	 */
	public function getOrderStatusCancelledDefinition()
	{
		return $this->getOrderStatusDefinitionFromDatabaseByStatusCode(PwCommerce::ORDER_STATUS_CANCELLED);
	}

	/**
	 * Get Order Status Completed Definition.
	 *
	 * @return mixed
	 */
	public function getOrderStatusCompletedDefinition()
	{
		return $this->getOrderStatusDefinitionFromDatabaseByStatusCode(PwCommerce::ORDER_STATUS_COMPLETED);
	}

	/**
	 * Get all order statuses definitions from the database.
	 *
	 * @return mixed
	 */
	public function getAllOrderStatusDefinitionsFromDatabase()
	{
		// TODO HOW TO MAKE THESE VALUES TRANSLATABLE? GET FROM ALL STATUS ARRAY IN $this->getStatuses()?
		$database = $this->wire('database');
		$table = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE;
		$sql = "SELECT * FROM `$table`";
		// prepare query
		$query = $database->prepare($sql);
		// execute
		$query->execute();
		// fetch ALL  the rows
		$statusesDefinition = $query->fetchAll(\PDO::FETCH_ASSOC);
		return $statusesDefinition;
	}

	/**
	 * Get the order status definition of a given order status code from the database.
	 *
	 * @param mixed $code
	 * @return mixed
	 */
	public function getOrderStatusDefinitionFromDatabaseByStatusCode($code)
	{
		// TODO HOW TO MAKE THESE VALUES TRANSLATABLE? GET FROM ALL STATUS ARRAY IN $this->getStatuses()?
		$code = (int) $code;
		$database = $this->wire('database');
		$table = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE;
		$sql = "SELECT * FROM `$table` WHERE status_code=:status_code";
		// prepare query
		$query = $database->prepare($sql);
		// bind our named parameter
		$query->bindValue(":status_code", $code, \PDO::PARAM_INT);
		// execute
		$query->execute();
		// fetch the row
		$statusDefinition = $query->fetch(\PDO::FETCH_OBJ);
		return $statusDefinition;
	}

	/**
	 * Is Valid Status Code.
	 *
	 * @param mixed $code
	 * @return bool
	 */
	public function isValidStatusCode($code)
	{
		$code = (int) $code;
		return $code >= PwCommerce::STATUS_MIMINUM_FLAG && $code <= PwCommerce::STATUS_MAXIMUM_FLAG;
	}

	// ~~~~~~~~~~~~~~~~~~~
	// INSTALL ONLY
	//  - CREATE AND POPULATE STATUSES TABLE -

	/**
	 * Create the a custom database table for storing order statuses definitions.
	 *
	 * @return mixed
	 */
	public function createOrderStatusDatabaseTable()
	{

		$database = $this->wire('database');
		$table = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE;
		// utf8mb3 || utf8
		// try to create our custom database table: 'pwcommerce_order_status'
		try {
			$sql = "CREATE TABLE `$table` (
				`status_code` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
				`name` VARCHAR(255) CHARACTER SET ascii NOT NULL,
				`description` TEXT CHARACTER SET ascii NOT NULL,
				PRIMARY KEY (`status_code`),
				UNIQUE KEY `name` (`name`),
				FULLTEXT KEY `description` (`description`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
			";
			// -------------
			$query = $database->prepare($sql);
			$query->execute();
		} catch (Exception $e) {
			$this->error($e->getMessage());
			// throw wire exception if could not create database table
			throw new WireException(sprintf(__("PWCommerce: Installation aborted. Could not create custom table: %s."), $table));
		}
	}

	/**
	 * Check if an order status already already exists in this database.
	 *
	 * @return bool
	 */
	public function isExistOrderStatusDatabaseTable()
	{
		$table = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE;
		$database = $this->wire('database');
		return $database->tableExists($table);
	}

	/**
	 * Insert Order Statuses On Install.
	 *
	 * @param mixed $statusCode
	 * @param mixed $statusName
	 * @param mixed $statusDescription
	 * @return mixed
	 */
	public function insertOrderStatusesOnInstall($statusCode, $statusName, $statusDescription)
	{

		$sanitizer = $this->wire('sanitizer');
		$statusCode = (int) $statusCode;
		$isValidStatusCode = $this->isValidStatusCode($statusCode);
		$statusName = $sanitizer->text($statusName);
		$statusDescription = $sanitizer->text($statusDescription, ['maxLength' => 0]);

		//-----------------
		if (empty($statusCode) || empty($statusName) || empty($statusDescription) || empty($isValidStatusCode)) {
			// abort if any of the values are empty

			return;
		}

		// GOOD TO GO

		// ------------
		$table = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE;
		// $database is ProcessWire PDO
		$database = $this->wire('database');
		$table = $database->escapeTable($table); // @see /wire/core/Fieldtype.php. returns
		// @note:  need backticks around $table
		$sql = "INSERT INTO `$table` (status_code, name, description) VALUES (:status_code, :status_name, :status_description)";
		// prepare statement
		$query = $database->prepare($sql);
		// bind our named parameters
		$query->bindValue(":status_code", $statusCode, \PDO::PARAM_INT);
		$query->bindValue(":status_name", $statusName, \PDO::PARAM_STR);
		$query->bindValue(":status_description", $statusDescription, \PDO::PARAM_STR);
		// let's write some data
		$query->execute();
	}

	// ~~~~~~~~~~~~~~~~
	// UNINSTALL

	/**
	 * Drop custom PWCommerce order status Database Table.
	 *
	 * @return mixed
	 */
	public function dropOrderStatusDatabaseTable()
	{

		$database = $this->wire('database');
		$table = PwCommerce::PWCOMMERCE_ORDER_STATUS_TABLE;
		$success = false;

		// try to DROP our custom database table: 'pwcommerce_order_status'
		try {
			$sql = "DROP TABLE `$table`";
			$query = $database->prepare($sql);
			$query->execute();
			$success = true;
		} catch (Exception $e) {
			$this->error($e->getMessage());
		}
		// ------------
		// TODO REVISIT!
		if ($success) {
			$this->message(sprintf(__("PWCommerce: Deleted Custom Database Table: %s."), $table));
		}
	}
}
