<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesCheckout
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CHECKOUT ~~~~~~~~~~~~~~~~~~

	/**
	 * Get the shop's checkout settings.
	 *
	 * @return WireData $generalSettings The general settings.
	 */
	public function getShopCheckoutSettings() {
		$shopCheckoutSettingsArray = [];
		$checkoutSettingsJSON = $this->wire('pages')->getRaw("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",name=checkout", 'pwcommerce_settings');
		if (!empty($checkoutSettingsJSON)) {
			$shopCheckoutSettingsArray = json_decode($checkoutSettingsJSON, true);
		}
		$shopCheckoutSettings = new WireData();
		$shopCheckoutSettings->setArray($shopCheckoutSettingsArray);
		// --------
		return $shopCheckoutSettings;
	}



}
