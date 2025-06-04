<?php

namespace ProcessWireCommerce;

// money
require_once __DIR__ . '/composer/autoload_real.php';
// spl_autoload_register('autoload_money');
spl_autoload_register(__NAMESPACE__ . '\autoload_money');
// paypal and stripe
// spl_autoload_register('autoload_paypal_stripe');
spl_autoload_register(__NAMESPACE__ . '\autoload_paypal_stripe');

# +++++++++++++++++++

# ~~~~~~~~~ MONEY ~~~~~~~~~ #

function autoload_money() {
	return \ComposerAutoloaderInitd16620ad09c3b4c3ab149557149a22712::getLoader();
}

# ~~~~~~~~~ PAYPAL & STRIPE  ~~~~~~~~~ #

function autoload_paypal_stripe($originalClassName) {

	// TODO NOT IN USE FOR NOW!
	// var_dump($originalClassName);

	// TODO - @note: for now, only Stripe and PayPal to autoload!

	// if (strripos($originalClassName, 'paypal') === false && stripos($originalClassName, 'stripe') === false) {
	if (empty(isValidForPayPalAndStripeAutoload($originalClassName))) {
		// var_dump($originalClassName);
		return false;
	}

	// exit($originalClassName);
	// ----------
	$className = ltrim($originalClassName, '\\');
	// var_dump($className);
	$fileName = '';
	$namespace = '';
	if ($lastNsPos = strripos($className, '\\')) {
		$namespace = substr($className, 0, $lastNsPos);
		$className = substr($className, $lastNsPos + 1);
		$fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	}
	$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

	require $fileName;
}

function isValidForPayPalAndStripeAutoload($className) {
	return strripos($className, 'paypal') === false && stripos($className, 'stripe') === false ? false : true;
}
