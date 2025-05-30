<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsUserInterface
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ USER INTERFACE TAB  ~~~~~~~~~~~~~~~~~~
	private function getGUITab() {

		// 		TODO
		// USER INTERFACE
		// - USE: DROPDOWN MENU ONLY/SIDE MENU ONLY/ DROPDOWN MENU AND SIDE MENU (RADIO INSTEAD OF MULTICHECKBOX!)
		// - SHOW: QUICK FILTERS ONLY/ADVANCED SEARCH ONLY/ QUICK FILTERS & ADVANCED SEARCH

		//--------------
		// for radio navigation type
		$navigationTypeRadioOptions = [
			'side_and_dropdown_menus' => $this->_("Use both side and dropdown menus"),
			'side_menu_only' => $this->_("Use side menu only"),
			'dropdown_menu_only' => $this->_("Use dropdown menu only"),
		];

		$navigationTypeValue = $this->wire('sanitizer')->fieldName($this->getGeneralSettingValue('gui_navigation_type'));
		if (empty($navigationTypeValue)) {
			// use default value: SIDE & DROPDOWN MENUS
			$navigationTypeValue = 'side_and_dropdown_menus';
		}

		// wrap attrs
		// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change
		$navigationTypeWrapAttrs = [
			['dataset' => 'data-general-settings-radio-change-type', 'value' => 'general_settings_gui_navigation_type'],
		];

		# notes (Alpine JS powered!)
		// note the X-INIT VALUE FOR ON LOAD/SAVED VALUE!
		$navigationTypeNotes =
			// wrapper
			"<span x-data='ProcessPWCommerceData' x-init='initGeneralSettingsGUI(`general_settings_gui_navigation_type`,`{$navigationTypeValue}`)' @pwcommercegeneralsettingsradiochangenotification.window='handleGeneralSettingsRadioChange'>" .
			// side_and_dropdown_menus
			"<span x-show='{$this->xstore}.general_settings_gui_navigation_type==`side_and_dropdown_menus`'>" .
			$this->_('Both the sidebar navigation and the dropdown menu will be shown. You might need to log out and log in again for this change to take effect.') .
			"</span>" .
			// side_menu_only
			"<span x-show='{$this->xstore}.general_settings_gui_navigation_type==`side_menu_only`'>" .
			$this->_('Only the sidebar navigation will be shown.') .
			"</span>" .
			// dropdown_menu_only
			"<span x-show='{$this->xstore}.general_settings_gui_navigation_type==`dropdown_menu_only`'>" .
			$this->_('Only the dropdown menu will be shown. You might need to log out and log in again for this change to take effect.') .
			"</span>" .
			// close wrapper
			"</span>";

		//--------------
		// for quick filters and advanced search
		$searchTypeRadioOptions = [
			'quick_filters_and_advanced_search' => $this->_("Use both quick filters and advanced search in the shop admin"),
			'advanced_search_only' => $this->_("Advanced search only"),
			'quick_filters_only' => $this->_("Quick filters only"),
		];

		$searchTypeValue = $this->wire('sanitizer')->fieldName($this->getGeneralSettingValue('gui_quick_filters_and_advanced_search'));
		if (empty($searchTypeValue)) {
			// use default value: QUICK FILTERS & ADVANCED SEARCH
			$searchTypeValue = 'quick_filters_and_advanced_search';
		}

		// wrap attrs
		// @note: this sets a data attribute to the parent <li>. We use this to get the 'type' of radio button change
		$searchTypeWrapAttrs = [
			['dataset' => 'data-general-settings-radio-change-type', 'value' => 'general_settings_gui_quick_filters_and_advanced_search'],
		];

		# notes (Alpine JS powered!)
		// note the X-INIT VALUE FOR ON LOAD/SAVED VALUE!
		$searchTypeNotes =
			// wrapper
			"<span x-data='ProcessPWCommerceData' x-init='initGeneralSettingsGUI(`general_settings_gui_quick_filters_and_advanced_search`,`{$searchTypeValue}`)' @pwcommercegeneralsettingsradiochangenotification.window='handleGeneralSettingsRadioChange'>" .
			// quick_filters_and_advanced_search
			"<span x-show='{$this->xstore}.general_settings_gui_quick_filters_and_advanced_search==`quick_filters_and_advanced_search`'>" .
			$this->_("Will enable you to use quick filters for some preset searching as well as advanced search for fine-grained results.") .
			"</span>" .
			// advanced_search_only
			"<span x-show='{$this->xstore}.general_settings_gui_quick_filters_and_advanced_search==`advanced_search_only`'>" .
			$this->_("Quick filters will not be shown. You will only be able to use the advanced search feature.") .
			"</span>" .
			// quick_filters_only
			"<span x-show='{$this->xstore}.general_settings_gui_quick_filters_and_advanced_search==`quick_filters_only`'>" .
			$this->_("Advanced search feature in the shop admin will not be enabled. You will only be able to use the quick filters feature.") .
			"</span>" .
			// close wrapper
			"</span>";

		//------------------
		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_general_settings_gui_tab',
				'title' => $this->_('User Interface')
			],

			'inputfields' => [
				# ***************
				// GUI DESCRIPTION
				// 'gui_info' => [
				// 	'type' => 'markup',
				// 	'label' => $this->_('User Interface'),
				// 	'collapsed' => Inputfield::collapsedNever,
				// 	// 'wrapClass' => true,
				// 	// 'wrapper_classes' => 'pwcommerce_no_outline',
				// 	'columnWidth' => 100,
				// 	'value' => $this->_('Settings for the shop user interface.'),
				// ],

				// gui navigation type
				'navigation_type' => [
					'type' => 'radio',
					'name' => 'pwcommerce_general_settings_gui_navigation_type',
					'label' => $this->_('Navigation Menus'),
					'description' => $this->_('Indicate the type of navigation to use in the shop admin.'),
					'radio_options' => $navigationTypeRadioOptions,
					'columnWidth' => 50,
					'value' => $navigationTypeValue,
					// ALPINE POWERED NOTES
					'notes' => $navigationTypeNotes,
					// -------
					'wrapAttr' => $navigationTypeWrapAttrs,
					'entityEncodeText' => false,

				],

				// gui quick filters and advanced search
				'quick_filters_and_advanced_search' => [
					'type' => 'radio',
					'name' => 'pwcommerce_general_settings_gui_quick_filters_and_advanced_search',
					'label' => $this->_('Search'),
					'description' => $this->_('Indicate the type of search to use in the shop admin.'),
					'radio_options' => $searchTypeRadioOptions,
					'columnWidth' => 50,
					'value' => $searchTypeValue,
					// ALPINE POWERED NOTES
					'notes' => $searchTypeNotes,
					// -------
					'wrapAttr' => $searchTypeWrapAttrs,
					'entityEncodeText' => false,
				],

			],

		];

		return $tabAndContents;
	}
}
