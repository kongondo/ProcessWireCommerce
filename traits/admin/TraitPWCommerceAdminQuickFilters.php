<?php

namespace ProcessWire;

trait TraitPWCommerceAdminQuickFilters
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ QUICK FILTERS  ~~~~~~~~~~~~~~~~~~


	/**
	 *    render Context Quick Filters.
	 *
	 * @return mixed
	 */
	protected function ___renderContextQuickFilters() {
		// filters array
		/** @var array $filters */
		$filters = $this->pwcommerceRender->getQuickFiltersValues();
		// --------
		// build alpine.JS/htmx powered markup
		$out = "";

		// TODO GENERATE TAILWIND 'font-semibold' + the bg colours
		foreach ($filters as $filter => $filterLabel) {
			// $plClass = $filter === 'reset' ? ' pl-0' : '';
			//------
			$out .= "<a class='mr-3 mb-1 inline-block no-underline hover:no-underline px-3 font-semibold' data-filter='{$filter}' :class='{$this->xstore}.quick_filter_value==`{$filter}` ? `pwcommerce_selected_quick_filter` : ``' @click.prevent='handleFetchPagesQuickFilter'>{$filterLabel}</a>";
		}
		// @TODO MORE THAN ONE FILTER ALLOWED? DEPENDS ON CONTEXT!
		// hidden input to track current filter
		$out .= "<input id='pwcommerce_quick_filter_value' name='pwcommerce_quick_filter_value' type='hidden' x-model='{$this->xstore}.quick_filter_value'>" .
			// HIDDEN INPUT FOR HTMX
			// set the context for differentiation when in ajax page
			"<input type='hidden' value='{$this->context}' name='pwcommerce_quick_filter_context'>";
		//------
		// --------
		return $out;
	}


}