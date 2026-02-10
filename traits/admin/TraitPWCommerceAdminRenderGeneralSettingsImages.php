<?php

namespace ProcessWire;

trait TraitPWCommerceAdminRenderGeneralSettingsImages
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ IMAGES TAB  ~~~~~~~~~~~~~~~~~~

	/**
	 * Get Images Tab.
	 *
	 * @return mixed
	 */
	private function getImagesTab() {
		$tabAndContents = [
			'details' => [
				'id' => 'pwcommerce_general_settings_images_tab',
				'title' => $this->_('Images'),
			],
			'inputfields' => [
				// images minimum width
				'images_minimum_width' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_images_minimum_width',
					'min' => 0,
					'step' => 1,
					'label' => $this->_('Images Minimum Width'),
					'description' => $this->_('Enter the value in number of pixels or leave blank for no limit.'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('images_minimum_width'),
				],
				// images minimum height
				'images_minimum_height' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_images_minimum_height',
					'min' => 0,
					'step' => 1,
					'label' => $this->_('Images Minimum Height'),
					'description' => $this->_('Enter the value in number of pixels or leave blank for no limit.'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('images_minimum_height'),
				],

				// images maximum width
				'images_maximum_width' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_images_maximum_width',
					'min' => 0,
					'step' => 1,
					'label' => $this->_('Images Maximum Width'),
					'description' => $this->_('Enter the value in number of pixels or leave blank for no limit.'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('images_maximum_width'),
				],

				// images maximum height
				'images_maximum_height' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_images_maximum_height',
					'min' => 0,
					'step' => 1,
					'label' => $this->_('Images Maximum Height'),
					'description' => $this->_('Enter the value in number of pixels or leave blank for no limit.'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('images_maximum_height'),

				],

				// images minimum filesize
				'images_minimum_filesize' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_images_minimum_filesize',
					'min' => 0,
					'step' => 1,
					'label' => $this->_('Images Minimum File Size'),
					'description' => $this->_('Enter the value in kilobytes or leave blank for no limit.'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('images_minimum_filesize'),
				],

				// images maximum filesize
				'images_maximum_filesize' => [
					'type' => 'number',
					'name' => 'pwcommerce_general_settings_images_maximum_filesize',
					'min' => 0,
					'step' => 1,
					'label' => $this->_('Images Maximum File Size'),
					'description' => $this->_('Enter the value in kilobytes or leave blank for no limit.'),
					'columnWidth' => 50,
					'value' => $this->getGeneralSettingValue('images_maximum_filesize'),
				],

				// allowed image file extensions TODO: TEXT AREA!
				'images_allowed_file_extensions' => [
					'type' => 'textarea',
					'name' => 'pwcommerce_general_settings_images_allowed_file_extensions',
					'label' => $this->_('Allowed Images File Extensions'),
					'description' => $this->_('Enter all file extensions allowed for product images. Separate each extension by a space. No periods or commas. This field is not case sensitive.'),
					'columnWidth' => 100,
					'rows' => 3,
					'value' => $this->getGeneralSettingValue('images_allowed_file_extensions'),
					'required' => true,
				],

			],

		];

		return $tabAndContents;
	}

}
