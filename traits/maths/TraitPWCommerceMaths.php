<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Maths: Trait class for PWCommerce Maths.
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

trait TraitPWCommerceMaths
{
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MATHS ~~~~~~~~~~~~~~~~~~

	public function mathAdd($leftOperand, $rightOperand, $scale = 2) {
		// BCMATH EXPECTS STRING VALUES
		$leftOperand = strval($leftOperand);
		$rightOperand = strval($rightOperand);
		$sum = bcadd($leftOperand, $rightOperand, $scale = 2);
		// return $sum;
		return (float) $sum;
	}

	public function mathSubtract($leftOperand, $rightOperand, $scale = 2) {
		// BCMATH EXPECTS STRING VALUES
		$leftOperand = strval($leftOperand);
		$rightOperand = strval($rightOperand);
		$difference = bcsub($leftOperand, $rightOperand, $scale);
		// return $difference;
		return (float) $difference;
	}

	public function mathMultiply($leftOperand, $rightOperand, $scale = 2) {
		// BCMATH EXPECTS STRING VALUES
		$leftOperand = strval($leftOperand);
		$rightOperand = strval($rightOperand);
		$product = bcmul($leftOperand, $rightOperand, $scale);
		// return $product;
		return (float) $product;
	}

	public function mathDivide($dividend, $divisor, $scale = 2) {
		// BCMATH EXPECTS STRING VALUES
		$dividend = strval($dividend);
		$divisor = strval($divisor);
		$quotient = bcdiv($dividend, $divisor, $scale);
		// return $quotient;
		return (float) $quotient;
	}

	public function mathCompare($leftOperand, $rightOperand, $scale = 2) {
		// BCMATH EXPECTS STRING VALUES
		$leftOperand = strval($leftOperand);
		$rightOperand = strval($rightOperand);
		$compareResult = bccomp($leftOperand, $rightOperand, $scale);
		return $compareResult;
	}

}