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

	/**
	 * Math Add.
	 *
	 * @param mixed $leftOperand
	 * @param mixed $rightOperand
	 * @param int $scale
	 * @return mixed
	 */
	public function mathAdd($leftOperand, $rightOperand, $scale = 2) {
		// BCMATH EXPECTS STRING VALUES
		$leftOperand = strval($leftOperand);
		$rightOperand = strval($rightOperand);
		$sum = bcadd($leftOperand, $rightOperand, $scale = 2);
		// return $sum;
		return (float) $sum;
	}

	/**
	 * Math Subtract.
	 *
	 * @param mixed $leftOperand
	 * @param mixed $rightOperand
	 * @param int $scale
	 * @return mixed
	 */
	public function mathSubtract($leftOperand, $rightOperand, $scale = 2) {
		// BCMATH EXPECTS STRING VALUES
		$leftOperand = strval($leftOperand);
		$rightOperand = strval($rightOperand);
		$difference = bcsub($leftOperand, $rightOperand, $scale);
		// return $difference;
		return (float) $difference;
	}

	/**
	 * Math Multiply.
	 *
	 * @param mixed $leftOperand
	 * @param mixed $rightOperand
	 * @param int $scale
	 * @return mixed
	 */
	public function mathMultiply($leftOperand, $rightOperand, $scale = 2) {
		// BCMATH EXPECTS STRING VALUES
		$leftOperand = strval($leftOperand);
		$rightOperand = strval($rightOperand);
		$product = bcmul($leftOperand, $rightOperand, $scale);
		// return $product;
		return (float) $product;
	}

	/**
	 * Math Divide.
	 *
	 * @param mixed $dividend
	 * @param mixed $divisor
	 * @param int $scale
	 * @return mixed
	 */
	public function mathDivide($dividend, $divisor, $scale = 2) {
		// BCMATH EXPECTS STRING VALUES
		$dividend = strval($dividend);
		$divisor = strval($divisor);
		$quotient = bcdiv($dividend, $divisor, $scale);
		// return $quotient;
		return (float) $quotient;
	}

	/**
	 * Math Compare.
	 *
	 * @param mixed $leftOperand
	 * @param mixed $rightOperand
	 * @param int $scale
	 * @return mixed
	 */
	public function mathCompare($leftOperand, $rightOperand, $scale = 2) {
		// BCMATH EXPECTS STRING VALUES
		$leftOperand = strval($leftOperand);
		$rightOperand = strval($rightOperand);
		$compareResult = bccomp($leftOperand, $rightOperand, $scale);
		return $compareResult;
	}

}