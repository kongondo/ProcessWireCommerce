<?php

namespace ProcessWire;

trait TraitPWCommerceAdminLister
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ LISTER  ~~~~~~~~~~~~~~~~~~

	/**
	 * Get the InputfieldSelector instance for this Lister
	 *
	 * @return mixed
	 */
	public function getInputfieldSelector() {

		// TODO: REMOVE THE 'show' in the matches 1 page; or just remove the whole code there including the matches!

		//------------------- selector (getInputfieldSelector)

		// $out = "";
		// TODO - WILL CHANGE TO MARKUP FOR INPUTFIELDSELECTOR
		// TODO: PASS OPTIONS, E.G. OF INITVALUE, ETC!
		// TODO: MAKE CONTEXTUAL IN ready()!

		// get context inputfieldselector settings
		$options = $this->contextCustomListerSettings;
		$defaultOptions = [
			'id' => 'pwcommerce_inputfield_selector_value',
			'name' => 'pwcommerce_inputfield_selector_value',
			// @note: does not work!
			'classes' => 'pwcommerce_inputfield_selector_value',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_inputfield_selector',
			'label' => $this->_('Filter Items'),
			//---------------
			'inputfield_selector' => [
				'initValue' => "",
				'initTemplate' => null,
			],
		];
		//----------------
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}

		if (!empty($options['inputfield_selector']['initTemplate'])) {
			$templateName = $options['inputfield_selector']['initTemplate'];
			$template = $this->wire('templates')->get($templateName);
			$options['inputfield_selector']['initTemplate'] = $template;
		}

		//--------------

		$field = $this->pwcommerce->getInputfieldSelector($options);

		return $field;

		#################################################
		// TODO - DELETE BELOW IF NOT IN USE

		// ================= TODO: BORROWED FROM ProcessPageLister.module. ADAPT FOR OUR NEED!

		/* TODO: ALSO SEE THIS CODE IN ProcessPageLister::processInput <= ADAPT AND USE HERE?? YES!
																																																																																																																																																																																																																																																																																																																																												$is = $this->getInputfieldSelector();
																																																																																																																																																																																																																																																																																																																																												$is->processInput($this->input->post);
																																																																																																																																																																																																																																																																																																																																												*/

		if ($this->inputfieldSelector) {
			return $this->inputfieldSelector;
		}

		/** @var InputfieldSelector $s */
		$s = $this->modules->get('InputfieldSelector');
		$s->attr('name', 'filters');
		$s->attr('id', 'ProcessListerFilters');
		$s->initValue = $this->initSelector;
		if ($this->template) {
			$s->initTemplate = $this->template;
		}

		$s->label = $this->_('Filters');
		$s->addLabel = $this->_('Add Filter');
		$s->icon = 'search-plus';
		$s->preview = $this->preview;
		$s->counter = false;
		$s->allowSystemCustomFields = $this->allowSystem;
		$s->allowSystemTemplates = $this->allowSystem;
		$s->allowSubfieldGroups = false; // we only support in ListerPro
		$s->allowSubselectors = false; // we only support in ListerPro
		$s->exclude = 'sort';
		$s->limitFields = $this->limitFields;
		$s->showFieldLabels = $this->useColumnLabels ? 1 : 0;
		if (in_array('collapseFilters', $this->toggles)) {
			$s->collapsed = Inputfield::collapsedYes;
		}

		if (in_array('disableFilters', $this->toggles)) {
			$s->attr('disabled', 'disabled');
		}
		$selector = $this->sessionGet('selector');
		if ($this->initSelector) {
			if (strpos($selector, $this->initSelector) !== false) {
				$selector = str_replace($this->initSelector, '', $selector); // ensure that $selector does not contain initSelector
			}
		}

		if (!strlen($selector)) {
			$selector = $this->defaultSelector;
		} else if ($this->defaultSelector && strpos($selector, $this->defaultSelector) === false) {
			$selector = $this->combineSelector($this->defaultSelector, $selector);
		}
		$s->attr('value', $selector);
		$this->inputfieldSelector = $s;
		return $s;
	}

	/**
	 * Get Selector Start.
	 *
	 * @return mixed
	 */
	private function getSelectorStart() {
		$selectorString = $this->selector;

		$currentPaginationLimitForContext = $this->getPaginationLimitCookieForContext();

		$currentListerSelectorStringForContext = $this->getLastSelectorForListerCookieForContext();

		// $limit = 10;
		$limit = !empty((int) $currentPaginationLimitForContext) ? (int) $currentPaginationLimitForContext : 10;
		if (is_string($selectorString) && strpos($selectorString, 'limit=') !== false) {

			// get the user set limit
			$selectors = new Selectors($selectorString);
			$selector = $selectors->getSelectorByField('limit');
			if (!empty($selector)) {
				$userLimit = (int) $selector['value'];

				$limit = $userLimit;
				// -------
				// if limit has changed (i.e. cookie limit and new user limit are different)
				if ($userLimit !== $currentPaginationLimitForContext) {

					// clear/reset $this->currentPaginationNumberForContext
					// TODO: STILL NOT WORKING AS NEEDS TO BE CHECKED EARLIER AND AMENDED BEFORE $this->getPWCommerceContextRender() PASSES $options TO CLASSES!
					$this->currentPaginationNumberForContext = 1;
				}
			}
		}
		//-----------------------
		$start = ($this->currentPaginationNumberForContext - 1) * $limit;

		//-----------------
		return $start;
	}


	/**
	 * Set Last Selector For Lister Cookie For Context.
	 *
	 * @return mixed
	 */
	private function setLastSelectorForListerCookieForContext() {
		$selectorString = $this->selector;
		$listerSelectorStringCoookieName = PwCommerce::PWCOMMERCE_LISTER_SELECTOR_STRING_COOKIE_NAME_PREFIX . "_" . $this->wire('sanitizer')->fieldName($this->context);
		$this->wire('input')->cookie->set($listerSelectorStringCoookieName, $selectorString);
	}

	/**
	 * Get Last Selector For Lister Cookie For Context.
	 *
	 * @return mixed
	 */
	private function getLastSelectorForListerCookieForContext() {
		$listerSelectorStringCoookieName = PwCommerce::PWCOMMERCE_LISTER_SELECTOR_STRING_COOKIE_NAME_PREFIX . "_" . $this->wire('sanitizer')->fieldName($this->context);
		$currentListerSelectorStringForContext = $this->wire('input')->cookie->get($listerSelectorStringCoookieName);
		return $currentListerSelectorStringForContext;
	}

	// TODO: DELETE IF NOT IN USE!

	/**
	 * Get a Lister session variable
	 *
	 * @param mixed $key
	 * @param mixed $fallback
	 * @return mixed
	 */
	public function sessionGet($key, $fallback = null) {
		$key = $this->page->name . '_lister_' . $key;
		$value = $this->session->get($key);
		if ($value === null && $fallback !== null) {
			$value = $fallback;
		}

		return $value;
	}



}