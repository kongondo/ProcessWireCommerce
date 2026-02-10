<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesFindAnything
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ FIND ANYTHING ~~~~~~~~~~~~~~~~~~
	/**
	 * Get array of templates that are searchable for use in 'find anything' feature.
	 *
	 * @return mixed
	 */
	public function getSearcheableFindAnythingTemplates() {
		// @note: these are in $template->id => $template->name key=>value pairs
		$searchableFindAnythingTemplates = $this->getFindAnythingCachedTemplates();
		// if we didn't find the templates in the cache
		if (empty($searchableFindAnythingTemplates)) {
			// create cache of the templates for next time
			$this->cacheFindAnythingTemplates();
			// get 'raw' templates array
			$searchableFindAnythingTemplates = $this->getFindAnythingTemplatesArray();
		}
		// --------
		return $searchableFindAnythingTemplates;
	}

	/**
	 * Get the names of the pwcommerce templates that can be used in 'find anything' feature.
	 *
	 * @return mixed
	 */
	private function getFindAnythingTemplatesNames() {
		$findAnythingTemplatesNames = [
			"pwcommerce-attribute",
			"pwcommerce-attribute-option",
			// @note: special: editing via parent
			"pwcommerce-brand",
			"pwcommerce-category",
			"pwcommerce-country",
			"pwcommerce-country-territory",
			// @note: special: editing via parent
			"pwcommerce-dimension",
			"pwcommerce-download",
			"pwcommerce-legal-page",
			"pwcommerce-order",
			"pwcommerce-order-line-item",
			// @note: special: editing via parent
			// pwcommerce-payment-provider
			"pwcommerce-product",
			"pwcommerce-product-variant",
			// @note: special: editing via parent
			"pwcommerce-property",
			"pwcommerce-shipping-rate",
			// @note: special: editing via parent
			"pwcommerce-shipping-zone",
			"pwcommerce-tag",
			"pwcommerce-type",
		];
		return $findAnythingTemplatesNames;
	}

	/**
	 * Build the selector to use to for the ProcessWire $templates->find() to fetch templates for laer use in 'find anything'.
	 *
	 * @return mixed
	 */
	public function getFindAnythingTemplatesSelector() {
		return implode("|", $this->getFindAnythingTemplatesNames());
	}

	/**
	 * Get templates for later use as templates for 'find anything' feature.
	 *
	 * @return mixed
	 */
	private function getFindAnythingTemplates() {
		$templatesSelector = $this->getFindAnythingTemplatesSelector();
		$templates = $this->wire('templates')->find("name={$templatesSelector}");
		return $templates;
	}

	/**
	 * Get 'find anything' templates array.
	 *
	 * @return mixed
	 */
	private function getFindAnythingTemplatesArray() {
		/** @var TemplatesArray $value */
		$templates = $this->getFindAnythingTemplates();
		// ------
		$templatesArray = [];
		// get only the value we need
		foreach ($templates as $template) {
			$templatesArray[$template->id] = $template->name;
		}
		// ---------
		return $templatesArray;
	}

	/**
	 * Get the cached values for 'find anything' templates.
	 *
	 * @return mixed
	 */
	private function getFindAnythingCachedTemplates() {
		return $this->wire('cache')->get(PwCommerce::FIND_ANYTHING_TEMPLATES_CACHE_NAME);
	}

	/**
	 * Save 'find anything' templates to cache.
	 *
	 * @return mixed
	 */
	private function cacheFindAnythingTemplates() {
		$templatesArray = $this->getFindAnythingTemplatesArray();
		$this->wire('cache')->save(PwCommerce::FIND_ANYTHING_TEMPLATES_CACHE_NAME, $templatesArray, WireCache::expireNever);
	}

}
