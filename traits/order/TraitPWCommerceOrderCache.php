<?php

namespace ProcessWire;

trait TraitPWCommerceOrderCache {

	/**
	 * Create Order Cache.
	 *
	 * @param Page $orderPage
	 * @param int $sessionID
	 * @return bool
	 */
	protected function createOrderCache(Page $orderPage, $sessionID): bool {
		// TODO create an order cache for given order ID + set it to auto expire in 24 hours
		// TODO will need to first check if it exists or not!

		$order = $orderPage->get(self::ORDER_FIELD_NAME);
		$orderCustomer = $orderPage->get(self::ORDER_CUSTOMER_FIELD_NAME);
		$orderCacheName = self::ORDER_CACHE_NAME_PREFIX . "_{$orderPage->id}";
		$expectedAmountMoney = $this->getOrderGrandTotalMoney();
		$expectedAmountValueInCents = (int) $expectedAmountMoney->getAmount();

		$orderCache = [
			'cache_name' => $orderCacheName,
			'order_id' => (int) $orderPage->id,
			// 'cart_order_id' => $order->orderID,// TODO - NOT WORKING FOR SOME REASON!
			'order_status' => $order->orderStatus,
			// TODO WE AMEND THIS IF WEBHOOK COMES IN
			// TODO the class webhook handler does the amendment! e.g. it could be that the webhook is about payment intent created; in that case, the handler will not change 'payment_status'.
			// TODO we only check 'payment_status' if 'is_received_webhook' IS TRUE!
			// 'payment_status' => $order->paymentStatus,
			'order_session_id' => $sessionID,
			'order_customer_email' => $orderCustomer->email,
			'expected_amount' => $order->totalPrice,
			'expected_amount_in_cents' => $expectedAmountValueInCents,
			'expected_currency' => $this->getShopCurrency(),
			'is_received_webhook' => false,
			'checkout_page_http_url' => $this->session->checkoutPageHttpURL,
			// ################
			// PAYMENT CLASS CAN MUTATE BELOW VALUES AT INDEX 'payment'
			'payment' => [
				// 'provider_name' => 'stripe', // name; not title!
				'provider_name' => $order->paymentMethod, // TITLE
				// TODO NEEDED?
				'id' => $this->session->paymentProviderID,
				// -------
				// TODO - LET STRIPE ADD THESE KEYS! DURING PAYMENT CREATE ORDER - to use setOrderCacheValue()
				// 'payment_intent' => 'pi_3Q86N0DJJW03hvkU03hQjIhr',
				// 'payment_intent_client_secret' => 'pi_3Q86N0DJJW03hvkU03hQjIhr_secret_2uvCSCkbWjHvijiWRAkgNt4OO',
				// -------
				// TODO we only check 'payment_status' if 'is_received_webhook' IS TRUE!
				'payment_status' => $order->paymentStatus,
				# ++++++++
				# WEBHOOKS #
				// TODO POPULATE SOON AFTER RECEIVE FIRST WEBHOOK
				// TODO SHOULD IT BE A 200 OR EVEN 400 ONES?
				// TODO SHOULD IT BE PWCOMMERCE (TRAITWEBHOOK) OR HANDLER CLASS?
				// 'is_received_webhook' => false,
				// TODO - ALSO USE TO PREVENT REPLAY ATTACK; I.E. PROCESS WEBHOOK ONLY ONCE
				// TODO - IN THIS ARRAY OF ARRAYS, WE COULD USE THE WEBHOOK EVENT ID AS THE INDEX. HENCE, WE CAN CHECK IF THAT INDEX EXISTS AND IF IT DOES, IT MEANS THE WEBHOOK HAS ALREADY BEEN PROCESSED, SO RETURN 400 RESPONSE (?)
				// TODO - HERE WE MEAN THE HANDLER CLASS DOES THE HEAVY LIFTING! I.E. THEY (IN OUR CASE STRIPE) CHECK IF ALREADY PROCESSED, ETC. DNA CAN CHOOSE TO IMPLEMENT THEIRS DIFFERENTLY - HENCE ADD TO DOCS!
				'webhooks' => [] // TODO ARRAYS OF JSON? UP TO THEM!
			]
		];

		// SAVE THE CACHE
		$isCacheSaved = $this->wire('cache')->save($orderCacheName, $orderCache);

		return $isCacheSaved;
	}

	/**
	 * Set Order Cache Value.
	 *
	 * @param int $orderID
	 * @param mixed $key
	 * @param mixed $value
	 * @return mixed
	 */
	protected function setOrderCacheValue(int $orderID, $key, $value) {
		// TODO ONLY ALLOW VALUES FOR KEY 'payment'
		// PAYMENT CLASS CAN MUTATE BELOW VALUES AT INDEX 'payment'
		if (!empty($key) && !empty($value)) {
			// get the order cache to amend
			$orderCache = $this->getOrderCache($orderID);
			if (!empty($orderCache)) {
				// SET/AMEND CACHE VALUE
				$orderCache['payment'][$key] = $value;
				$isAmendedCache = true;
				// SAVE CACHE
				$orderCacheName = self::ORDER_CACHE_NAME_PREFIX . "_{$orderID}";
				$isAmendedCache = $this->wire('cache')->save($orderCacheName, $orderCache);
			}
		}

		return $isAmendedCache;
	}
	/**
	 * Track Webhook In Order Cache.
	 *
	 * @param int $orderID
	 * @param mixed $key
	 * @param array $value
	 * @return mixed
	 */
	protected function trackWebhookInOrderCache(int $orderID, $key, array $value) {
		// TODO ONLY ALLOW VALUES FOR KEY ['payment']['webhook']
		// TODO OVERWRITE EXISTING OR SKIP?
		// PAYMENT CLASS CAN MUTATE BELOW VALUES AT INDEX 'payment'
		// TODO HERE EXPECT ARRAY?
		$isAmendedCache = false;
		if (!empty($key) && !empty($value)) {
			// get the order cache to amend
			$orderCache = $this->getOrderCache($orderID);
			if (!empty($orderCache)) {
				// NOTE: WE SKIP KEY ALREADY EXISTS
				if (!isset($orderCache['payment']['webhooks'][$key])) {
					// NEW WEBHOOK EVENT: CAN SET!
					$orderCache['payment']['webhooks'][$key] = $value;
					// IF FIRST TIME WEBHOOK DETAILS ADDED TO CACHE, ALSO SET 'is_received_webhook' TO TRUE
					$orderCache = $this->updateIsReceivedWebhook($orderCache);
					// SAVE CACHE
					$orderCacheName = self::ORDER_CACHE_NAME_PREFIX . "_{$orderID}";
					$isAmendedCache = $this->wire('cache')->save($orderCacheName, $orderCache);
				}
			}
		}

		return $isAmendedCache;
	}

	/**
	 * Update Is Received Webhook.
	 *
	 * @param array $orderCache
	 * @return array
	 */
	private function updateIsReceivedWebhook(array $orderCache): array {
		if (empty($orderCache['is_received_webhook'])) {
			$orderCache['is_received_webhook'] = true;
		}
		return $orderCache;
	}

	/**
	 * Get Order Cache.
	 *
	 * @param int $orderID
	 * @return mixed
	 */
	protected function getOrderCache(int $orderID) {
		// TODO get order cache for given order ID
		$input = $this->wire('input');

		$exampleWirecache = [
			'cache_name' => 'pwcommerce_order_cache_1234',
			'order_id' => (int) $input->get('order_id'),
			'cart_order_id' => (int) $input->get('cart_order_id'),
			'order_status' => '1234 - abandoned, etc',
			// TODO WE AMEND THIS IF WEBHOOK COMES IN
			// TODO the class webhook handler does the amendment! e.g. it could be that the webhook is about payment intent created; in that case, the handler will not change 'payment_status'.
			// TODO we only check 'payment_status' if 'is_received_webhook' IS TRUE!
			'payment_status' => '2234 - paid, etc',
			'order_session_id' => '0s7jj2mrkub45up3370qbequum',
			'order_customer_email' => 'someone@somewhere.com',
			'expected_amount' => '10',
			'expected_amount_in_cents' => '1000',
			'expected_currency' => 'GBP',
			'payment' => [
				'name' => 'stripe', // name; not title!
				'id' => '7859',
				'payment_intent' => 'pi_3Q86N0DJJW03hvkU03hQjIhr',
				'payment_intent_client_secret' => 'pi_3Q86N0DJJW03hvkU03hQjIhr_secret_2uvCSCkbWjHvijiWRAkgNt4OO',
				# WEBHOOKS #
				// TODO POPULATE SOON AFTER RECEIVE FIRST WEBHOOK
				// TODO SHOULD IT BE A 200 OR EVEN 400 ONES?
				// TODO SHOULD IT BE PWCOMMERCE (TRAITWEBHOOK) OR HANDLER CLASS?
				'is_received_webhook' => false,
				// TODO - ALSO USE TO PREVENT REPLAY ATTACK; I.E. PROCESS WEBHOOK ONLY ONCE
				// TODO - IN THIS ARRAY OF ARRAYS, WE COULD USE THE WEBHOOK EVENT ID AS THE INDEX. HENCE, WE CAN CHECK IF THAT INDEX EXISTS AND IF IT DOES, IT MEANS THE WEBHOOK HAS ALREADY BEEN PROCESSED, SO RETURN 400 RESPONSE (?)
				// TODO - HERE WE MEAN THE HANDLER CLASS DOES THE HEAVY LIFTING! I.E. THEY (IN OUR CASE STRIPE) CHECK IF ALREADY PROCESSED, ETC. DNA CAN CHOOSE TO IMPLEMENT THEIRS DIFFERENTLY - HENCE ADD TO DOCS!
				'webhooks' => [] // TODO ARRAYS OF JSON? UP TO THEM!
			]
		];
		// return $exampleWirecache;
		$orderCacheName = self::ORDER_CACHE_NAME_PREFIX . "_{$orderID}";
		// RETRIEVE THE CACHE
		$orderCache = $this->wire('cache')->get($orderCacheName);

		return $orderCache;
	}

	/**
	 * Renew Order Cache.
	 *
	 * @param int $orderID
	 * @param int $time
	 * @return mixed
	 */
	protected function renewOrderCache(int $orderID, $time = 24) {
		// TODO renew the expiry of order cache for given order ID
		// TODO need to check that it exists first!

	}

	/**
	 * Update Order Cache.
	 *
	 * @param int $orderID
	 * @param mixed $value
	 * @return mixed
	 */
	protected function updateOrderCache(int $orderID, $value) {
		// TODO update an existing order cache.
		// TODO not sure what $value could be! string or assoc array!
	}

	/**
	 * Delete Order Cache.
	 *
	 * @param int $orderID
	 * @return mixed
	 */
	protected function deleteOrderCache(int $orderID) {
		// TODO delete the order cache for given order
		$orderCacheName = self::ORDER_CACHE_NAME_PREFIX . "_{$orderID}";
		// DELETE THE CACHE
		$isCacheDeleted = $this->wire('cache')->delete($orderCacheName);

		return $isCacheDeleted;
	}

	/**
	 * Is Exist Order Cache.
	 *
	 * @param int $orderID
	 * @return bool
	 */
	protected function isExistOrderCache(int $orderID): bool {
		// TODO NOT SURE WE NEED THIS METHOD? CAN JUST USE getOrderCache and see if it returns an array?
		$isValidOrderCache = false;
		// TODO check if we have an order cache for the given order ID

		return $isValidOrderCache;
	}

	/**
	 * Is Alive Order Cache.
	 *
	 * @param int $orderID
	 * @return bool
	 */
	protected function isAliveOrderCache(int $orderID) {
		// TODO - NOT SURE HOW THIS METHOD IS HELPFUL?
		$isAliveOrderCache = false;
		// TODO check if cache available but order paid/complated!

		return $isAliveOrderCache;
	}

	/**
	 * Is Already Processed Webhook.
	 *
	 * @param int $orderID
	 * @param int $webhookEventID
	 * @return bool
	 */
	protected function isAlreadyProcessedWebhook(int $orderID, $webhookEventID) {
		// TODO NOTE - PREVENT WEBHOOK REPLAY ATTACK!

		$isAlreadyProcessedWebhook = false;
		// TODO check cache if webhook event with given ID for given order ID has already been processed
		// GET ORDER CACHE FIRST
		$orderCache = $this->getOrderCache($orderID);
		if (!empty($orderCache)) {
			// we found the cache. Now check under webhooks
			$isAlreadyProcessedWebhook = isset($orderCache['payment']['webhooks'][$webhookEventID]);
		}

		return $isAlreadyProcessedWebhook;
	}
}
