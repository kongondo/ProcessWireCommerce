<?php

namespace ProcessWire;



trait TraitPWCommerceProcessOrderDiscount
{



	private function updateGlobalUsageOfRedeemedDiscounts() {
		// TODO DELETE IF NO LONGER IN USE
		$pwcommerce = $this->pwcommerce;
		// $redeemedDiscountsIDs = $pwcommerce->pwcommerceDiscounts->getSessionRedeemedDiscountsIDs();
		$redeemedDiscountsIDs = $this->getSessionRedeemedDiscountsIDs();
		if (empty($redeemedDiscountsIDs)) {
			// NOTHING TO DO; NO DISCOUNTS WERE APPLIED
			return;
		}

		$discountIDsSelector = implode("|", $redeemedDiscountsIDs);
		$discountsPages = $pwcommerce->find("id={$discountIDsSelector}, template=discount");
		if (!empty($discountsPages->count())) {
			foreach ($discountsPages as $page) {
				$discount = $page->get(PwCommerce::DISCOUNT_FIELD_NAME);
				$currentGlobalUsageCount = (int) $discount->discountGlobalUsage;
				// --------
				$updatedGlobalUsageCount = $currentGlobalUsageCount + 1;
				// ----------
				$discount->discountGlobalUsage = $updatedGlobalUsageCount;
				// set and save
				$page->setAndSave(PwCommerce::DISCOUNT_FIELD_NAME, $discount);

			}
		}

	}

}
