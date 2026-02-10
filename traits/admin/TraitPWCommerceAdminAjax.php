<?php

namespace ProcessWire;

trait TraitPWCommerceAdminAjax
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ AJAX  ~~~~~~~~~~~~~~~~~~

	//----------------------
	/**
	 * Outputs javascript configuration values for this module.
	 *
	 * @return mixed
	 */
	protected function ajaxConfigs() {
		$adminURL = $this->adminURL;
		$ajaxURL = "{$adminURL}ajax/";
		// $session = $this->wire('session');
		// $tokenName = $session->CSRF->getTokenName();
		// $tokenValue = $session->CSRF->getTokenValue();
		// options for ajax calls
		$options = [
			'config' => [
				'ajaxURL' => $ajaxURL,
				// 'formData' => array($tokenName => $tokenValue),
			],
		];
		$scripts = $this->wire('config')->js($this->className(), $options);
		return $scripts;
	}

}
