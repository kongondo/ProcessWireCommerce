<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Render: Trait class for PWCommerce Render.
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

trait TraitPWCommerceRender
{



	#####################
	/**
	 *
	 * Returns requested FRONTEND TemplateFile.
	 * Either customized version from /site/templates/pwcommerce/frontend/
	 * Or the default one from /site/modules/ProcessWireCommerce/templates/frontend/
	 *
	 * @param string filename of required template
	 * @return TemplateFile requested
	 *
	 */
	protected function getPWCommerceTemplate($filename)
	{
		$configPaths = $this->config->paths;
		// $templatePath = __DIR__ . "/templates/" . $filename;
		// $templatePath = __DIR__ . "/../../templates/" . $filename;
		// DEFAULT PARTIAL FRONTEND TEMPLATE
		$templatePath = $configPaths->siteModules . "ProcessWireCommerce/templates/frontend/" . $filename;

		if (file_exists($configPaths->templates . "pwcommerce/frontend/" . $filename)) {
			// CUSTOM PARTIAL FRONTEND TEMPLATE
			$templatePath = $configPaths->templates . "pwcommerce/frontend/" . $filename;
		} elseif (file_exists($configPaths->templates . "pwcommerce/" . $filename)) {
			// CUSTOM PARTIAL FRONTEND TEMPLATE - TEMPORARY OLDER PWCOMMERCE VERSIONS
			$templatePath = $configPaths->templates . "pwcommerce/" . $filename;
		}

		return new TemplateFile($templatePath);
	}


	/**
	 * Returns string with price and currency in friendly format
	 *
	 * @param float $price
	 * @return string
	 */
	protected function ___renderPriceAndCurrency($price)
	{
		if ($price == "")
			$price = 0;
		$priceAndCurrency = $this->getValueFormattedAsCurrencyForShop($price);
		return $priceAndCurrency;
	}

	public function renderCartPriceAndCurrency($price)
	{
		// FOR BACKWARDS COMPATIBILITY
		return $this->renderPriceAndCurrency($price);
	}

	/**
	 * Returns price in friendly format
	 *
	 * @param float $price
	 * @return string
	 */
	protected function ___renderPrice($price)
	{
		// TODO: DELETE; NO LONGER IN USE!
		$decimals = 2;
		if (!$this->dec_point)
			$decimals = 0;
		$thousands_sep = ($this->thousands_sep == "space") ? " " : $this->thousands_sep;
		return number_format($price, $decimals, $this->dec_point, $thousands_sep);
	}

	/**
	 * Renders the current cart in view mode
	 *
	 * @return string html markup
	 *
	 */
	public function viewCart()
	{
		$t = $this->getPWCommerceTemplate("cart-view.php");
		return $t->render();
	}

	/**
	 * Renders the current cart in edit mode
	 *
	 * @return string html markup
	 *
	 */
	public function editCart()
	{
		// $cartItems = $this->getCart();
		$t = $this->getPWCommerceTemplate("cart-edit.php");
		return $t->render();
	}

	# >>>>>>>>>>>>>>>>>>>>>>>>>> DEPRECATED <<<<<<<<<<<<<<<<<<<<<<<

	public function getPadTemplate($file)
	{
		return $this->getPWCommerceTemplate($file);
	}
}
