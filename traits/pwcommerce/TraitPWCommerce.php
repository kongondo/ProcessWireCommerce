<?php

namespace ProcessWire;

/**
 * Trait PWCommerce: Trait class to load first class citizen classes for PWCommerce Module.
 *
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerce Class for PWCommerce
 * Copyright (C) 2024 by Francis Otieno
 * MIT License
 *
 */

// =========
// IMPORT TRAITS FILES
$traitsFiles = [
	'loader/TraitPWCommerceLoader',
	// +++++++++++
	'actions/TraitPWCommerceActions',
	'api/TraitPWCommerceAPI',
	'cart/TraitPWCommerceCart',
	'checkout/TraitPWCommerceCheckout',
	'constants/TraitPWCommerceConstants',
	'database/TraitPWCommerceDatabase',
	'discounts/TraitPWCommerceDiscounts',
	'downloads/TraitPWCommerceDownloads',
	'hooks/TraitPWCommerceHooks',
	'maths/TraitPWCommerceMaths',
	'money/TraitPWCommerceMoney',
	'order/TraitPWCommerceOrder',
	'payment/TraitPWCommercePayment',
	'shipping/TraitPWCommerceShipping',
	'render/TraitPWCommerceRender',
	'tax/TraitPWCommerceTax',
	'utilities/TraitPWCommerceUtilities',
	'order/TraitPWCommerceWebhooks',
	'inputfield/TraitPWCommerceInputfieldsHelpers',

];

foreach ($traitsFiles as $traitFileName) {
	require_once __DIR__ . "/../{$traitFileName}.php";
}

trait TraitPWCommerce
{

	use TraitPWCommerceLoader;
	use TraitPWCommerceAPI;
	use TraitPWCommerceActions;
	use TraitPWCommerceCart;
	use TraitPWCommerceConstants;
	use TraitPWCommerceCheckout;
	use TraitPWCommerceDatabase;
	use TraitPWCommerceDiscounts;
	use TraitPWCommerceDownloads;
	use TraitPWCommerceHooks;
	use TraitPWCommerceInputfieldsHelpers;
	use TraitPWCommerceMoney;
	use TraitPWCommerceMaths;
	use TraitPWCommerceOrder;
	use TraitPWCommercePayment;
	use TraitPWCommerceRender;
	use TraitPWCommerceShipping;
	use TraitPWCommerceTax;
	use TraitPWCommerceUtilities;
	use TraitPWCommerceWebhooks;


	/**
	 * Init P W Commerce Traits.
	 *
	 * @return mixed
	 */
	private function initPWCommerceTraits()
	{
		// INIT TRAITS 'INIT' METHODS IF APPLICABLE
		$traitsNames = $this->getTraitsNames();
		foreach ($traitsNames as $traitName) {

			// cleanup $traitName if needed
			// remove subfolder prefix
			if (str_contains($traitName, "/")) {
				$traitNameParts = explode("/", $traitName);
				$traitName = $traitNameParts[1];
			}

			# ++++++++++
			$traitInitMethodName = "_init{$traitName}";


			if (method_exists($this, $traitInitMethodName)) {
				// $this->{"$traitInitMethodName()"};
				// The callback syntax is a little odd in PHP. What you need to do is make an array. The 1st element is the object, and the 2nd is the method.
				call_user_func([$this, $traitInitMethodName]);
				// You can also do it without call_user_func:
				// $this->{"$traitInitMethodName"}();
				// $this->{$traitInitMethodName}();
				// Or:
				// $method = "$traitInitMethodName";
				// $method = $traitInitMethodName;
				// $this->$method();
			}

		}
	}

	/**
	 * Get Traits Names.
	 *
	 * @return mixed
	 */
	private function getTraitsNames()
	{
		$traitsFiles = [
			'TraitPWCommerceActions',
			'TraitPWCommerceAdmin',
			// 'api/TraitPWCommerceAPI',
			// 'cart/TraitPWCommerceCart',
			'checkout/TraitPWCommerceCheckout',
			'TraitPWCommerceConstants',
			// 'database/TraitPWCommerceDatabase',
			'discounts/TraitPWCommerceDiscounts',
			'downloads/TraitPWCommerceDownloads',
			// 'hooks/TraitPWCommerceHooks',
			// 'maths/TraitPWCommerceMaths',
			// 'money/TraitPWCommerceMoney',
			'order/TraitPWCommerceOrder',
			'payment/TraitPWCommercePayment',
			'shipping/TraitPWCommerceShipping',
			'TraitPWCommerceRender',
			'tax/TraitPWCommerceTax',
			'TraitPWCommerceUtilities',
			'order/TraitPWCommerceWebhooks',
		];
		return $traitsFiles;
	}


}