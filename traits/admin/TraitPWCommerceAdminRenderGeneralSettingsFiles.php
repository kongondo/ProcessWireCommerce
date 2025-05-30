<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsFiles
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ FILES TAB  ~~~~~~~~~~~~~~~~~~

	private function getFilesTab() {
		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_general_settings_files_tab',
				'title' => $this->_('Files'),
			],
			'inputfields' => [
				// files minimum filesize
				'downloads_minimum_filesize' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_downloads_minimum_filesize',
					'min' => 0,
					'step' => 1,
					'label' => $this->_('Digital Products Minimum File Size'),
					'description' => $this->_('Enter the value in kilobytes or leave blank for no limit.'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('downloads_minimum_filesize'),
				],

				// files maximum filesize
				'downloads_maximum_filesize' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_downloads_maximum_filesize',
					'min' => 0,
					'step' => 1,
					'label' => $this->_('Digital Products Maximum File Size'),
					'description' => $this->_('Enter the value in kilobytes or leave blank for no limit.'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('downloads_maximum_filesize'),
				],

				// allowed digital products file extensions TODO: TEXT AREA!
				'allowed_downloads_file_extensions' => [
					'type' => 'textarea',
					'name' => 'pwcommerce_general_settings_allowed_downloads_file_extensions',
					'label' => $this->_('Allowed Digital Products File Extensions'),
					'description' => $this->_('Enter all file extensions allowed for digital/downloadable products. Separate each extension by a space. No periods or commas. This field is not case sensitive.'),
					'columnWidth' => 100,
					'rows' => 3,
					'value' => $this->getGeneralSettingValue('allowed_downloads_file_extensions'),
					'required' => true,
				],
			],

		];

		return $tabAndContents;
	}

}
