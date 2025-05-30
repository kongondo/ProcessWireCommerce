<?php

namespace ProcessWire;

trait TraitPWCommerceAdminPagination
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PAGINATION  ~~~~~~~~~~~~~~~~~~

	private function getCurrentPaginationNumber($urlString) {
		$pageNumber = 0;
		$urlStringArray = explode("/", $urlString);
		$pageNumString = array_pop($urlStringArray);
		if (strpos($pageNumString, 'page') !== false) {
			$pageNumber = (int) str_replace('page', '', $pageNumString);
		}
		return $pageNumber;
	}


	private function setPaginationNumberCookieForContext() {
		$paginationNumberCoookieName = PwCommerce::PWCOMMERCE_PAGINATION_NUMBER_COOKIE_NAME_PREFIX . "_" . $this->wire('sanitizer')->fieldName($this->context);
		$this->wire('input')->cookie->set($paginationNumberCoookieName, (int) $this->currentPaginationNumberForContext);

	}

	private function getPaginationNumberCookieForContext() {
		$paginationNumberCoookieName = PwCommerce::PWCOMMERCE_PAGINATION_NUMBER_COOKIE_NAME_PREFIX . "_" . $this->wire('sanitizer')->fieldName($this->context);
		$currentPaginationNumber = (int) $this->wire('input')->cookie->get($paginationNumberCoookieName);
		return $currentPaginationNumber;
	}

	private function setPaginationLimitCookieForContext() {
		$paginationLimitCoookieName = PwCommerce::PWCOMMERCE_PAGINATION_LIMIT_COOKIE_NAME_PREFIX . "_" . $this->wire('sanitizer')->fieldName($this->context);
		// $limitValues = explode("=", $this->selector);
		// $limit =

		$selectorString = $this->selector;
		if (is_string($selectorString) && strpos($selectorString, 'limit=') !== false) {
			// get the user set limit
			$selectors = new Selectors($selectorString);
			$selector = $selectors->getSelectorByField('limit');
			if (!empty($selector)) {
				$limit = (int) $selector['value'];
				$this->wire('input')->cookie->set($paginationLimitCoookieName, $limit);
			}
		}
	}

	private function getPaginationLimitCookieForContext() {
		$paginationLimitCoookieName = PwCommerce::PWCOMMERCE_PAGINATION_LIMIT_COOKIE_NAME_PREFIX . "_" . $this->wire('sanitizer')->fieldName($this->context);
		$currentPaginationLimitForContext = (int) $this->wire('input')->cookie->get($paginationLimitCoookieName);
		return $currentPaginationLimitForContext;
	}

	private function setLimitForSelectorForLister() {
		// set last limit
		$currentPaginationLimitForContext = (int) $this->getPaginationLimitCookieForContext();
		if (!empty($currentPaginationLimitForContext)) {
			if (is_null($this->selector)) {
				$this->selector = "limit={$currentPaginationLimitForContext}";
			} else {
				$this->selector .= ",limit={$currentPaginationLimitForContext}";
			}
		}
	}


}