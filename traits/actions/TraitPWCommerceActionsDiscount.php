<?php

namespace ProcessWire;

trait TraitPWCommerceActionsDiscount
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ DISCOUNT ~~~~~~~~~~~~~~~~~~

	/**
	 * Get New Discount Title.
	 *
	 * @return mixed
	 */
	private function getNewDiscountTitle() {
		// AUTO DISCOUNT TITLE
		// @note: can be edited post creation (TODO: ok?)
		// TODO: do we need this translation? yes; I think so!
		$title = sprintf(__("Discount: %d"), time());
		//---------

		return $title;
	}

	/**
	 * Action Pre Process Discount.
	 *
	 * @return mixed
	 */
	private function actionPreProcessDiscount() {

		$result = [
			'notice' => $this->_('Error encountered. No action was taken.'),
			'notice_type' => 'error',
		];

		// pre-process discount creation
		$input = $this->actionInput; // @note this is $input->post!!
		$sanitizer = $this->wire('sanitizer');

		$allowedDiscountTypesForPreProcess = ['amount_off_products', 'amount_off_order', 'free_shipping', 'buy_x_get_y'];

		$discountType = $sanitizer->option($input->pwcommerce_create_discount_type, $allowedDiscountTypesForPreProcess);

		// error: invalid discount type for some reason
		if (empty($discountType)) {
			$result['notice'] = $this->_('Invalid discount type. Please try again!');
			return $result;
		}

		// ======
		// GOOD TO GO
		// just use internal 'add item' but with special title for discount
		// will also use 'run extra operations'

		// TODO: SHOULD WE CREATE UNPUBLISHED?
		$result = $this->addNewItemAction($input);

		// 'special_redirect' => "/edit/?id={$page->id}"
		// IF DISCOUNT PAGE SAVE ERROR
		if ($result['notice_type'] === 'error') {
			return $result;
		}

		// NO ERROR
		$discountPageID = $result['new_item_id'];
		$result['special_redirect'] = "/edit/?id={$discountPageID}";

		// ---------
		return $result;

	}

}
