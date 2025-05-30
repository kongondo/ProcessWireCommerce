<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesFindAnything
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ FIND ANYTHING ~~~~~~~~~~~~~~~~~~
	/**
	 * Get array of templates that are searchable for use in 'find anything' feature.
	 *
	 * Get values from cache if available. Else, from 'raw' but also save to cache for next time.
	 *
	 * @return array $searchableFindAnythingTemplates Array of the searchable templates.
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
	 * @access private
	 * @return array $findAnythingTemplatesNames The names of  templates for 'find anything'.
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
	 * @access public
	 * @return string Selector string of pipe-separated names of 'find anything' templates for $templates->find().
	 */
	public function getFindAnythingTemplatesSelector() {
		return implode("|", $this->getFindAnythingTemplatesNames());
	}

	/**
	 * Get templates for later use as templates for 'find anything' feature.
	 *
	 * @note: This is the result of a $templates->find().
	 *
	 * @access private
	 * @return TemplatesArray $templates The result of the templates find.
	 */
	private function getFindAnythingTemplates() {
		$templatesSelector = $this->getFindAnythingTemplatesSelector();
		$templates = $this->wire('templates')->find("name={$templatesSelector}");
		return $templates;
	}

	/**
	 * Get 'find anything' templates array.
	 *
	 * These are build from the TemplatesArray of the 'find anything' templates.
	 * For use to either build the cache for 'find anything' templates or...
	 * Initial use if no cache was found for the templates.
	 *
	 * @access private
	 * @return array $templatesArray Array of 'find anything' templates.
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
	 * @access private
	 * @return array|null Array or null if no cache found.
	 */
	private function getFindAnythingCachedTemplates() {
		return $this->wire('cache')->get(PwCommerce::FIND_ANYTHING_TEMPLATES_CACHE_NAME);
	}

	/**
	 * Save 'find anything' templates to cache.
	 *
	 * @note: The cache never expires.
	 *
	 * @access private
	 * @return void
	 */
	private function cacheFindAnythingTemplates() {
		$templatesArray = $this->getFindAnythingTemplatesArray();
		$this->wire('cache')->save(PwCommerce::FIND_ANYTHING_TEMPLATES_CACHE_NAME, $templatesArray, WireCache::expireNever);
	}

}
