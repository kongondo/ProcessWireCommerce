<?php

namespace ProcessWire;

/**
 * Trait PWCommerce: Inputfields Helpers.
 *
 * Methods to help render or enhance PWCommerce Inputfields.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceInputfieldsHelpers for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

trait TraitPWCommerceInputfieldsHelpers
{

	/**
	 * Return a blank ProcessWire InputfieldForm.
	 * @param array $options Form options.
	 * @return InputfieldForm Blank InputfieldForm.
	 */
	public function getInputfieldForm(array $options = []) {

		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldForm',
			'action' => './',
			'method' => 'post',
			'description' => '',
			// 'collapsed' => null,
			// 'columnWidth' => 100,
			// 'wrapClass' => false,
			// 'classes' => '',
			// 'wrapper_classes' => '',
			// //'icon' => null,
			// // @note: if set, will be rendered before the wrapper
			// // @see: InputfieldWrapper.php
			// 'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}

		//-----------
		$field = $this->modules->get('InputfieldForm');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		$field->action = $options['action'];
		$field->method = $options['method'];

		$field->description = $options['description'];

		//-----
		return $field;
	}

	public function getInputfieldWrapper(array $options = []) {
		// TODO: unsure which of these work and under what circumstances!
		$defaultOptions = [
			'name' => 'PWCommerceInputfieldWrapper',
			'skipLabel' => null,
			'label' => '',
			'notes' => '',
			'collapsed' => null,
			'columnWidth' => 100,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			//'icon' => null,
			// @note: if set, will be rendered before the wrapper
			// @see: InputfieldWrapper.php
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		$field = new InputfieldWrapper();

		// $field->attr('name', $options['name']);
		$field->name = $options['name'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}

		if (!empty($options['notes'])) {
			$field->notes = $options['notes'];
		}

		$field->columnWidth = $options['columnWidth'];
		$field->collapsed = $options['collapsed'];

		if (!empty($options['value'])) {
			$field->value = $options['value'];
			// $field->attr('value', $options['value']);
		}

		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		//-------
		return $field;
	}

	public function getInputfieldMarkup(array $options = []) {
		$defaultOptions = [
			'id' => null,
			'skipLabel' => null,
			'label' => '',
			'description' => '',
			'notes' => '',
			'collapsed' => null,
			'columnWidth' => 100,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		$field = $this->modules->get('InputfieldMarkup');
		//--------------
		// make sure ProcessWire renders the HTML
		// TODO: if not needed, delete
		$field->textFormat = Inputfield::textFormatNone;

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}

		$field->description = $options['description'];

		if (!empty($options['notes'])) {
			$field->notes = $options['notes'];
		}
		$field->columnWidth = $options['columnWidth'];
		$field->collapsed = $options['collapsed'];
		$field->attr('value', $options['value']);

		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		//-----
		return $field;
	}

	public function getInputfieldFieldset(array $options = []) {
		// TODO: unsure which of these work and under what circumstances!
		$defaultOptions = [
			'skipLabel' => null,
			'label' => '',
			'description' => '',
			'notes' => '',
			'collapsed' => null,
			'columnWidth' => 100,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			//'icon' => null,
			// @note: if set, will be rendered before the wrapper
			// @see: InputfieldWrapper.php
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		$field = $this->modules->get('InputfieldFieldset');

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}

		$field->description = $options['description'];

		if (!empty($options['notes'])) {
			$field->notes = $options['notes'];
		}
		$field->columnWidth = $options['columnWidth'];
		$field->collapsed = $options['collapsed'];

		if (!empty($options['value'])) {
			$field->value = $options['value'];
			// $field->attr('value', $options['value']);
		}

		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		//-------
		return $field;
	}

	public function getInputfieldText(array $options = []) {
		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldText',
			'type' => null,
			'step' => null,
			'min' => null,
			'max' => null,
			'placeholder' => null,
			'description' => '',
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'size' => 0,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		//-----------
		$field = $this->modules->get('InputfieldText');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		// special attributes
		if (!empty($options['type'])) {
			$field->attr('type', $options['type']);
		}
		if (!empty($options['step'])) {
			$field->attr('step', $options['step']);
		}
		// @note: ints!
		if (!is_null($options['min'])) {
			$field->attr('min', $options['min']);
		}
		if (!is_null($options['max'])) {
			$field->attr('max', $options['max']);
		}

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}

		if (!empty($options['placeholder'])) {
			$field->placeholder = $options['placeholder'];
		}

		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->size = $options['size'];

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	public function getInputfieldTextarea(array $options = []): InputfieldTextarea {
		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldText',
			'type' => null,
			'description' => '',
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'size' => 0,
			'rows' => 5,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		//-----------
		$field = $this->modules->get('InputfieldTextarea');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		// special attributes
		if (!empty($options['type'])) {
			$field->attr('type', $options['type']);
		}
		$field->attr('rows', $options['rows']);

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}

		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->size = $options['size'];

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	public function getInputfieldCKEditor(array $options = []): InputfieldCKEditor {
		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldCKEditor',
			'type' => null,
			'description' => '',
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'size' => 0,
			'rows' => 5,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		//-----------
		$field = $this->modules->get('InputfieldCKEditor');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		// special attributes
		if (!empty($options['type'])) {
			$field->attr('type', $options['type']);
		}
		$field->attr('rows', $options['rows']);

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}

		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->size = $options['size'];

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	// TODO ADD TINYMCE!

	public function getInputfieldEmail(array $options = []) {
		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldEmail',
			'placeholder' => null,
			'description' => '',
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			// Allow IDN emails? 1=yes for domain, 2=yes for domain+local part (default=0) 3.0.212+
			'allowIDN' => 0,
			// Specify 1 to make it include a second input for confirmation
			'confirm' => 0,
			// label to accompany second input
			// 'confirmLabel' => '',
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'size' => 0,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		//-----------
		$field = $this->modules->get('InputfieldEmail');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		// special attributes
		$field->attr('type', 'email');
		$field->attr('confirm', $options['confirm']);
		if (!empty($options['confirmLabel'])) {
			$field->attr('confirmLabel', $options['confirmLabel']);
		}

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}

		if (!empty($options['placeholder'])) {
			$field->placeholder = $options['placeholder'];
		}

		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->size = $options['size'];

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	public function getInputfieldCheckbox(array $options = []) {
		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldCheckbox',
			'description' => '',
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			'label2' => '',
			'required' => false,
			// 'checkboxLabel' => '',
			// 'checkboxOnly' => false,
			// 'labelAttrs' => [],
			'checked' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => 1,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		//-----------
		$field = $this->modules->get('InputfieldCheckbox');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		// checked state
		$field->checked($options['checked']);

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}
		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
			//    $field->skipLabel($options['skipLabel']);
		} else {
			// label
			if (!empty($options['label'])) {
				$field->label = $options['label'];
			}
			// label2
			if (!empty($options['label2'])) {
				$field->label2 = $options['label2'];
			}
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	public function getInputfieldCheckboxes(array $options = []) {
		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldCheckboxes',
			'description' => '',
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			// Whether or not to display as a table
			'table' => false,
			// Pipe "|" separated list of table headings. Do the same for the addOption() labels.
			'thead' => '',
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => [],
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		// if no checkboxes options, return TODO: ERROR? object?
		if (empty($options['checkboxes_options'])) {
			return;
		}
		//-----------
		$field = $this->modules->get('InputfieldCheckboxes');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		foreach ($options['checkboxes_options'] as $value => $label) {
			$field->addOption($value, $label);
		}

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}
		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
			//    $field->skipLabel($options['skipLabel']);
		} else {
			// label
			if (!empty($options['label'])) {
				$field->label = $options['label'];
			}
			// ====
			// TABLE DISPLAY
			// table
			if (!empty($options['table'])) {
				$field->thead = $options['thead'];
			}

		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	public function getInputfieldRadios(array $options = []) {

		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldRadios',
			'notes' => '',
			'description' => '',
			'skipLabel' => null,
			'label' => '',
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}

		// if no radio options, return TODO: ERROR? object?
		if (empty($options['radio_options'])) {
			return;
		}

		//------------
		$field = $this->modules->get('InputfieldRadios');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		$field->addOptions($options['radio_options']);

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}
		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// the saved value, if any
		//   $field->value = $options['value'];
		$field->attr('value', $options['value']);

		//-----
		return $field;
	}

	public function getInputfieldSelect(array $options = []) {

		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldSelect',
			'notes' => '',
			'description' => '',
			'skipLabel' => null,
			'label' => '',
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}

		// if no select options, return TODO: ERROR? object?
		if (empty($options['select_options'])) {
			return;
		}

		//------------
		$field = $this->modules->get('InputfieldSelect');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		$field->addOptions($options['select_options']);

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}
		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// the saved value, if any
		//   $field->value = $options['value'];
		$field->attr('value', $options['value']);

		//-----
		return $field;
	}

	public function getInputfieldButton(array $options = []) {
		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldButton',
			'type' => null,
			'description' => '',
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			'small' => false,
			'secondary' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => 1,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		//-----------
		$field = $this->modules->get('InputfieldButton');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		if (!empty($options['type'])) {
			$field->attr('type', $options['type']);
		}

		// small button
		$field->setSmall($options['small']);

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}
		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else {
			// label
			if (!empty($options['label'])) {
				$field->text = $options['label'];
			}
		}
		// secondary
		$field->secondary = $options['secondary'];

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	public function getInputfieldPageAutocomplete(array $options = []) {

		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldPageAutocomplete',
			'description' => '',
			'pagesSelector' => null,
			'maxSelectedItems' => 0,
			'parent_id' => 0,
			// whether or not to use a separate selected list. If false specified, selected item will be populated directly to the input.
			'useList' => true,
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		$field = $this->modules->get('InputfieldPageAutocomplete');

		// TODO: ADD THIS? lang_id => 'Force use of this language for results'
		//--------------
		$field->attr('name', $options['name']);
		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}
		// pages selector if sent
		if (!empty($options['pagesSelector'])) {
			$field->set('findPagesSelector', $options['pagesSelector']);
		}
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}
		$field->maxSelectedItems = $options['maxSelectedItems'];
		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['parent_id'])) {
			$field->parent_id = $options['parent_id'];
		}

		$field->useList = $options['useList'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	public function getInputfieldPageListSelect(array $options = []) {

		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldPageListSelect',
			'description' => '',
			'parent_id' => 0,
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		$field = $this->modules->get('InputfieldPageListSelect');

		//--------------
		$field->attr('name', $options['name']);
		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}

		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['parent_id'])) {
			$field->parent_id = $options['parent_id'];
		}

		// showPath if sent
		if (!empty($options['show_path'])) {
			$field->set('showPath', $options['show_path']);
		}

		// selectLabel if sent
		if (!empty($options['select_label'])) {
			$field->set('selectLabel', $options['select_label']);
		}

		// startLabel if sent
		if (!empty($options['start_label'])) {
			$field->set('startLabel', $options['start_label']);
		}

		// unselectLabel if sent
		if (!empty($options['unselect_label'])) {
			$field->set('unselectLabel', $options['unselect_label']);
		}

		// moreLabel if sent
		if (!empty($options['more_label'])) {
			$field->set('moreLabel', $options['more_label']);
		}

		// cancelLabel if sent
		if (!empty($options['cancel_label'])) {
			$field->set('cancelLabel', $options['cancel_label']);
		}

		// labelFieldName if sent
		if (!empty($options['label_field_name'])) {
			$field->set('labelFieldName', $options['label_field_name']);
		}

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	public function getInputfieldTextTags(array $options = []) {

		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldTextTags',
			'maxItems' => 0,
			'pageSelector' => null,
			'placeholder' => null,
			'useAjax' => false,
			'allowUserTags' => false,
			'closeAfterSelect' => true,
			'tagsUrl' => null,
			'delimiter' => 's',
			'description' => '',
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'set_tags_list' => [],
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		$field = $this->modules->get('InputfieldTextTags');

		// TODO: ADD THIS? lang_id => 'Force use of this language for results'
		//--------------
		$field->attr('name', $options['name']);
		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}
		// pages selector if sent
		// TODO: setPageSelector()
		if (!empty($options['pageSelector'])) {
			//    $field->set('findPagesSelector', $options['pageSelector']);
			$field->setPageSelector($options['pageSelector']);
		}
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}
		$field->maxItems = $options['maxItems'];
		$field->placeholder = $options['placeholder'];
		$field->description = $options['description'];
		$field->notes = $options['notes'];

		$field->useAjax = $options['useAjax'];
		$field->allowUserTags = $options['allowUserTags'];
		$field->closeAfterSelect = $options['closeAfterSelect'];
		if (!empty($options['tagsUrl'])) {
			$field->tagsUrl = $options['tagsUrl'];
		}
		// 's' = space; 'p' = pipe; 'c' = comma
		$field->delimiter = $options['delimiter'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		if (!empty($options['set_tags_list'])) {
			$field->setTagsList($options['set_tags_list']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	public function getInputfieldHidden(array $options = []) {

		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldHidden',
			'columnWidth' => 100,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		$field = $this->modules->get('InputfieldHidden');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		// TODO?  is useful for consistent widths
		$field->columnWidth = $options['columnWidth'];

		$field->attr('value', $options['value']);

		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		//-----
		return $field;
	}

	public function getInputfieldSelector(array $options = []) {

		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldSelector',
			'skipLabel' => null,
			'label' => '',
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'value' => null,
			// special

		];

		$inputfieldSelectorOptions = [
			'initValue' => '',
			'initTemplate' => null,
			'initSelector' => null,
			// $showFieldLabels [false] Show field labels rather than names? Or specify integer 2 to show both.
			//  'showFieldLabels' => 1, // TODO: BOOL INT - 1/0
			'showFieldLabels' => true,
			// TODO: BOOL INT - 1/0
			'addLabel' => $this->_('Add Filter'),
			'icon' => 'search-plus',
			'preview' => false,
			// bool
			'counter' => false,
			// bool
			'allowSystemCustomFields' => false,
			// bool
			'allowSystemTemplates' => false,
			// bool
			'allowSubfieldGroups' => false,
			// bool
			'allowSubselectors' => false,
			// bool
			'exclude' => '',
			// csv string
			'limitFields' => [],
			// TODO: GET DEFAULT FORMATS FROM SHOP GENERAL SETTINGS!
			// [Y-m-d] Default PHP date() format for date fields.
			'dateFormat' => 'Y-m-d',
			// Placeholder attribute text for date fields.
			'datePlaceholder' => 'yyyy-mm-dd',
			// Default PHP date() format for time component of date fields.
			'timeFormat' => 'H:i',
			// Placeholder attribute time component of date fields.
			'timePlaceholder' => 'hh:mm',
			// for toggling filters
			'toggles' => [],
		];
		//-----------------------
		if (!empty($options)) {

			// merge inputfieldselector-specific options first
			if (!empty($options['inputfield_selector'])) {
				$inputfieldSelectorOptions = array_merge($inputfieldSelectorOptions, $options['inputfield_selector']);
			}
			// then merge other usual inputfield attributes
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		$field = $this->modules->get('InputfieldSelector');
		//--------------

		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->entityEncodeLabel = false;
			$field->label = $options['label'] . "<span id='pwcommerce_inputfield_selector_spinner' class='fa fa-fw fa-spin fa-spinner ml-1 htmx-indicator'></span>";
		}

		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// special inputfieldselector attributes only
		// @note: not working! we use 'set' instead
		// $field->attr($inputfieldSelectorOptions);
		foreach ($inputfieldSelectorOptions as $key => $value) {

			if ($key == 'collapseFilters' || $key == 'disableFilters') {
				// collapse filters
				if (in_array('collapseFilters', $inputfieldSelectorOptions['toggles'])) {
					$field->collapsed = Inputfield::collapsedYes;
				}
				// disable filters
				if (in_array('disableFilters', $inputfieldSelectorOptions['toggles'])) {
					$field->attr('disabled', 'disabled');
				}
			}
			// other keys
			else {
				$field->set($key, $value);
			}
		}

		//---------------------
		// special attributes
		// $field->initValue = $options['initValue'];
		// $field->initTemplate = $options['initTemplate'];
		// $field->showFieldLabels = $options['showFieldLabels'];
		// // $field->icon = 'search-plus';// TODO delete when done
		// $field->icon = $options['icon'];
		// $field->preview = $options['preview']; // bool
		// $field->counter = $options['counter']; // bool
		// $field->allowSystemCustomFields = $options['allowSystemCustomFields']; // bool
		// $field->allowSystemTemplates = $options['allowSystemTemplates']; // bool
		// $field->allowSubfieldGroups = $options['allowSubfieldGroups']; // bool
		// $field->allowSubselectors = $options['allowSubselectors']; // bool
		// // $field->exclude = 'sort';// TODO: ARRAY?
		// $field->exclude = $options['exclude']; // TODO: ARRAY?
		// $field->limitFields = $options['limitFields'];
		// // $field->showFieldLabels = $this->useColumnLabels ? 1 : 0;
		// $field->showFieldLabels = $options['showFieldLabels']; // TODO: BOOL INT - 1/0

		//######################
		// if (in_array('collapseFilters', $this->toggles)) {
		//  $field->collapsed = Inputfield::collapsedYes;
		// }
		// if (in_array('collapseFilters', $inputfieldSelectorOptions['toggles'])) {
		//  $field->collapsed = Inputfield::collapsedYes;
		// }

		// if (in_array('disableFilters', $this->toggles)) {
		//  $field->attr('disabled', 'disabled');
		// }
		// if (in_array('disableFilters', $inputfieldSelectorOptions['toggles'])) {
		//  $field->attr('disabled', 'disabled');
		// }

		//$field->set('preview', false);
		//-----
		return $field;
	}

	public function getInputfieldPageTitle(array $options = []) {
		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldText',
			'description' => '',
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			'useLanguages' => false,
			'required' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		//-----------
		$field = $this->modules->get('InputfieldPageTitle');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		// special attributes
		if (!empty($options['useLanguages'])) {
			$field->useLanguages = true;
		}

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}

		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	public function getInputfieldDatetime(array $options = []) {
		$defaultOptions = [
			'id' => null,
			'name' => 'PWCommerceInputfieldDatetime',
			'datepicker' => InputfieldDatetime::datepickerFocus,
			'description' => '',
			'placeholder' => null,
			'notes' => '',
			'skipLabel' => null,
			'label' => '',
			'required' => false,
			'defaultToday' => false,
			'collapsed' => null,
			'columnWidth' => 100,
			'show_if' => null,
			'wrapClass' => false,
			'classes' => '',
			'wrapper_classes' => '',
			'icon' => null,
			'value' => null,
		];
		if (!empty($options)) {
			$options = array_merge($defaultOptions, $options);
		} else {
			$options = $defaultOptions;
		}
		//-----------
		$field = $this->modules->get('InputfieldDatetime');
		//--------------
		$field->attr('name', $options['name']);

		if (!empty($options['id'])) {
			$field->attr('id', $options['id']);
		}

		if (!empty($options['placeholder'])) {
			$field->placeholder = $options['placeholder'];
		}

		// special attributes
		if (!empty($options['datepicker'])) {
			// @note: passing as attr does not work!
			// $field->attr('datepicker', $options['datepicker']);
			$field->datepicker = $options['datepicker'];
		}

		$field->attr('defaultToday', $options['defaultToday']);

		//------------
		if (!empty($options['icon'])) {
			$field->icon = $options['icon'];
		}

		$field->description = $options['description'];
		$field->notes = $options['notes'];

		if (!empty($options['skipLabel'])) {
			$field->skipLabel = $options['skipLabel'];
		} else if (!empty($options['label'])) {
			$field->label = $options['label'];
		}

		$field->required = $options['required'];

		if (!empty($options['show_if'])) {
			$field->set('showIf', $options['show_if']);
		}

		$field->collapsed = $options['collapsed'];
		$field->columnWidth = $options['columnWidth'];
		// @note: $wrapClass Optional class name (CSS) to apply to the HTML element wrapping the Inputfield.
		if (!empty($options['wrapClass'])) {
			$field->addClass($options['wrapper_classes'], 'wrapClass');
		}

		$field->addClass($options['classes']);

		// the saved value, if any
		$field->value = $options['value'];

		//-----
		return $field;
	}

	// ~~~~~~~~~~~~~~~~~~~~

	/**
	 * Helper to return classes for a specified modal size.
	 *
	 * For use with a PWCommerce custom modal.
	 *
	 * @param string $size The size corresponding to the requested modal class.
	 * @return string $modalSizeClass The class corresponding to the specified modal size.
	 */
	public function getModalClassSize($size) {
		$modalSizeClass = '';

		switch ($size) {
			case 'x-small':
				$modalSizeClass = 'max-w-xs pwcommerce-modal-x-small';
				break;
			case 'small':
				$modalSizeClass = 'max-w-sm pwcommerce-modal-small';
				break;
			case 'medium':
				$modalSizeClass = 'max-w-md pwcommerce-modal-medium';
				break;
			case 'large':
				$modalSizeClass = 'max-w-lg pwcommerce-modal-large';
				break;
			case 'x-large':
				$modalSizeClass = 'max-w-xl pwcommerce-modal-x-large';
				break;
			//--------------------
			case '2x-large':
				$modalSizeClass = 'max-w-2xl pwcommerce-modal-2x-large';
				break;
			case '3x-large':
				$modalSizeClass = 'max-w-3xl pwcommerce-modal-3x-large';
				break;
			//-----------------
			case '4x-large':
				$modalSizeClass = 'max-w-4xl pwcommerce-modal-4x-large';
				break;
			case '5x-large':
				$modalSizeClass = 'max-w-5xl pwcommerce-modal-5x-large';
				break;
			case '6x-large':
				$modalSizeClass = 'max-w-6xl pwcommerce-modal-6x-large';
				break;
			case '7x-large':
				$modalSizeClass = 'max-w-7xl pwcommerce-modal-7x-large';
				break;
			default:
				// full
				// $modalSizeClass = 'max-w-full pwcommerce-modal-full'
				$modalSizeClass = 'pwcommerce-modal-full';
		}

		return $modalSizeClass;
	}

	/**
	 * Build custom modal for use in various PWCommerce inputfields.
	 *
	 * Used with alpinejs and tailwindcss.
	 *
	 * @param array $options Options to build the modal.
	 */

	public function getModalMarkup(array $options) {

		// SET VARIABLES
		// $header The modal title pane markup.
		$header = $options['header'];
		// $body The main content markup.
		$body = $options['body'];
		// $footer The footer markup.
		$footer = $options['footer'];
		// $xstore The alpinejs store with the property that will be modelled to show/hide the modal.
		$xstore = $options['xstore'];
		// $xproperty The alpinejs property that will be modelled to show/hide the modal.
		$xproperty = $options['xproperty'];
		// $size The size of the modal requested.
		$size = $options['size'];
		$handlerCloseModal = !empty($options['handler_for_close_modal']) ? $options['handler_for_close_modal'] : 'handleCloseModal';
		$handlerCloseModalValue = !empty($options['handler_for_close_modal_value']) ? $options['handler_for_close_modal_value'] : $xproperty;

		//-----------------
		$modalSizeClass = $this->getModalClassSize($size);
		$class = '{ hidden: !$store.' . $xstore . '.' . $xproperty . '}';

		// TODO: handleCloseModal below should really be calling the reset functions!
		// -------------------------------
		// modal overlay
		$out = "<div id='modal-overlay' class='modal-overlay hidden':class='{$class}'></div>" .
			// -------------------------------
			// modal outer wrapper
			"<div class='modal hidden' :class='{$class}'>" .
			// modal body wrapper
			"<div id='modal-body-wrapper' class='flex flex-col h-screen ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-resizable ui-dialog-buttons {$modalSizeClass}'>" .
			// -------------------------------
			// modal title dialog
			"<div class='ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix'>" .
			// modal title itself
			"<span class='ui-dialog-title'>{$header}</span>" .
			// modal close 'x' in title dialog
			$this->getModalMarkupTitleDialogCloseButton($handlerCloseModal, $handlerCloseModalValue) .
			// ------
			// end: modal title dialog
			"</div>" .
			// -------------------------------
			// modal body
			$this->getModalMarkupBody($body) .
			// -------------------------------
			// modal footer
			$this->getModalMarkupFooter($footer) .
			// ----------
			// end modal body wrapper
			"</div>" .
			// end: modal outer wrapper
			"</div>";
		// @note: was getting error here when editing images due to jquery trying to init these buttons becauese of the class ''ui-dialog-buttonpane'. we now flex the buttons instead.
		// <div class='ui-dialog-buttonpane ui-widget-content ui-helper-clearfix'>

		return $out;
	}

	private function getModalMarkupTitleDialogCloseButton($handleCloseModal, $xproperty) {
		// TODO add custom handler here for those that need it! e.g. some handlers need to reset before close!
		$close = $this->_('Close');
		$out = "<button type='button' class='ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close' role='button' aria-disabled='false' title='{$close}' style='padding-top: 0px' @click='{$handleCloseModal}(\"{$xproperty}\")'>" .
			"<i class='fa fa-times'></i><span class='ui-button-text'>{$close}</span>" .
			"</button>";
		return $out;
	}

	private function getModalMarkupBody($body) {
		$out = "<main class='flex-1 overflow-y-auto p-5'>{$body}</main>";
		return $out;
	}

	private function getModalMarkupFooter($footer) {
		$out = "<div class='flex justify-end pwcommerce-custom-ui-dialog-buttonpane ui-widget-content ui-helper-clearfix'> {$footer}" .
			"</div>";
		return $out;
	}

	/**
	 * Build button for the custom modal used by PWCommerce in various inputfields.
	 *
	 * For use with alpinejs.
	 *
	 * @param string $clickJSFunction The string representing the JavaScript function to call when the button is clicked.
	 * @param string $buttonType The type of button to build.
	 * @return string Rendered output of the ProcessWire InputfieldButton.
	 */
	public function getModalActionButton(array $attributes, $buttonType = 'add') {

		$secondary = false;

		if ($buttonType === 'cancel') {
			$label = $this->_('Cancel');
			$secondary = true;
		} else if ($buttonType === 'apply') {
			$label = $this->_('Apply');
		} else if ($buttonType === 'confirm') {
			$label = $this->_('Confirm');
		} else if ($buttonType === 'send') {
			$label = $this->_('Send');
		} else {
			$label = $this->_('Add');
		}
		//-------
		$options = [
			'label' => $label,
			'collapsed' => Inputfield::collapsedNever,
			// 'small' => true,
			'secondary' => $secondary,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
		];

		$field = $this->getInputfieldButton($options);
		// $field->attr('x-on:click', $clickJSFunction);
		// if adding any extra attributes
		if (!empty($attributes) && is_array($attributes)) {
			$field->attr($attributes);
		}
		return $field->render();
	}

	// htmx-enhanced search box (debounced type ahead)
	// e.g., as used by search for products to add to order
	public function getSearchBox(array $searchBoxOptions) {
		// search box options
		$options = [
			'id' => $searchBoxOptions['id'],
			'name' => $searchBoxOptions['name'],
			//    'skipLabel' => Inputfield::skipLabelHeader,
			//    'label' => $this->_('Search Products'),
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
		];

		$label = $searchBoxOptions['label'];
		// @note: trigger can be a local event or an event sent after an external event
		// e.g. a change somewhere that triggers alpine.js or htxm to fire an event that this searchbox would be listening to
		$trigger = !empty($searchBoxOptions['trigger']) ? $searchBoxOptions['trigger'] : 'keyup changed';
		$delayMS = !empty($searchBoxOptions['delay_ms']) ? $searchBoxOptions['delay_ms'] : '500ms';
		// $indicator = !empty($searchBoxOptions['indicator']) ? $searchBoxOptions['indicator'] : ".htmx-indicator";
		$indicator = !empty($searchBoxOptions['indicator']) ? $searchBoxOptions['indicator'] : "htmx-indicator";

		//--------------
		$field = $this->getInputfieldText($options);
		$field->entityEncodeLabel = false;
		$field->label = "{$label}<span class='{$indicator} fa fa-fw fa-spin fa-spinner'></span>";
		// $field->label = "{$label}<span class='htmx-indicator fa fa-fw fa-spin fa-spinner'></span>";
		// $x = "htmx-indicator";
		// $field->label = "{$label}<span class='{$x} fa fa-fw fa-spin fa-spinner'></span>";
		//   $field->label = $label;
		$field->attr([
			'hx-get' => $searchBoxOptions['get_url'],
			// 'hx-trigger' => "keyup changed delay:{$delayMS}",
			'hx-trigger' => "{$trigger} delay:{$delayMS}",
			'hx-target' => $searchBoxOptions['target'],
			// TODO ADD OPTIONS FOR SWAP!
			// TODO; NOT WORKING!SHOWING THROUGHTOUT!
			// 'hx-indicator' => ".{$indicator}",
			'hx-indicator' => ".{$indicator}",
			'placeholder' => $searchBoxOptions['placeholder'],
		]);
		// add hx-swap attribute if needed @note: default is innerHTML which will replace the target element.
		if (!empty($searchBoxOptions['swap'])) {
			$field->attr("hx-swap", $searchBoxOptions['swap']);
		}
		return $field;
	}

	//~~~~~~~~~~~~~~~~~~~~~

	// build actions panel used by bulk edit or just bulk views
	// e.g. add new products, delete tags, reports, etc
	public function getBulkEditActionsPanel($panelOptions) {

		$leftSideContent = '';
		// LEFT SIDE CONTENT
		// if context will be using add new item functionality
		if (!empty($panelOptions['add_new_item_url'])) {
			//------------------- bulk edit: add new item link + url
			$label = $panelOptions['add_new_item_label'];
			$addNewURL = $panelOptions['add_new_item_url'];
			$leftSideContent = "<a href='{$addNewURL}'><i class='fa fa-plus-circle'></i> {$label}</a>";
		} else if (!empty($panelOptions['is_extra_custom_markup'])) {
			// context used with extra supplied markup
			$leftSideContent = $panelOptions['extra_custom_markup'];
		}
		//--------
		$xstore = '$store.ProcessPWCommerceStore';

		// -------------
		// button type: can be 'button' if desired; else 'submit'
		$buttonType = !empty($panelOptions['apply_action_button_type']) ? $panelOptions['apply_action_button_type'] : 'submit';

		// NOT all views have bulk edit, in which case, hide them
		$applyActionButton = '';
		$selectAction = '';
		$hiddenMarkupIsReadyToPostAction = '';

		if (empty($panelOptions['is_hide_bulk_edit'])) {
			//------------------- bulk edit: apply  action button (getInputfieldButton)
			$options = [
				'id' => "pwcommerce_bulk_edit_apply_action",
				'name' => "pwcommerce_bulk_edit_apply_action",
				'type' => $buttonType,
				'label' => $this->_('Apply'),
				'collapsed' => Inputfield::collapsedNever,
				'small' => true,
				'wrapClass' => true,
				'wrapper_classes' => 'pwcommerce_no_outline',
			];

			// TODO: ADD ATTR FOR ALPINE TO CHECK IF READY TO APPLY, I.E. ACTION SELECTED AND AT LEAST ONE ITEM SELECTED
			// TODO: NEED TO ADD A REF OR SIMILAR TO THE SELECT AS WELL -> then can check if it has a value, and if so, + select all checkbox selected, button can submit
			// TODO: GREY OUT THE BUTTON MAYBE WHEN DISABLED?
			$field = $this->getInputfieldButton($options);
			// add attrs for alpine.js
			$field->attr([
				'x-bind:class' => "{$xstore}.is_ready_bulk_edit_action ? `` : `opacity-50`",
				'x-bind:disabled' => "!{$xstore}.is_ready_bulk_edit_action",
				'x-ref' => 'button_actions',
			]);

			// add htmx attributes if available
			$spinner = "";
			if (!empty($panelOptions['htmx_attributes'])) {
				$field->attr($panelOptions['htmx_attributes']);
				// ---------
				// add a spinner if requested
				if (!empty($panelOptions['htmx_attributes']['is_use_spinner'])) {
					$spinner = "<span id='pwcommerce_bulk_edit_view_apply_button_spinner_indicator' class='pwcommerce_spinner_indicator fa fa-fw fa-spin fa-spinner htmx-indicator mr-1'></span>";
					// tell htmx about spinner
					$field->attr('hx-indicator', '#pwcommerce_bulk_edit_view_apply_button_spinner_indicator');
				}
			}

			// -----------
			$applyActionButton = $spinner . $field->render();

			//------------------- bulk edit: actions (getInputfieldSelect)
			$selectOptions = $panelOptions['bulk_edit_actions'];
			// if only bulk view (instead of edit)
			// we don't want to disable button if no edit checkbox selected
			// only an action need to be selected
			$isViewOnly = !empty($panelOptions['is_view_only']) ? true : false;

			$options = [
				'id' => "pwcommerce_bulk_edit_action",
				'name' => 'pwcommerce_bulk_edit_action',
				// TODO: SKIP LABEL?
				'label' => $this->_('Apply Action'),
				//  'skipLabel' => Inputfield::skipLabelHeader,
				'collapsed' => Inputfield::collapsedNever,
				'wrapClass' => true,
				// TODO: delete class 'pwcommerce_bulk_edit_action' if not needed!
				'wrapper_classes' => 'pwcommerce_no_outline pwcommerce_bulk_edit_action',
				'select_options' => $selectOptions,
			];

			$field = $this->getInputfieldSelect($options);
			// add attrs for alpine.js
			$field->attr([
				'x-model' => "{$xstore}.bulk_edit_action",
				'x-on:change' => "handleBulkEditActionChange({$isViewOnly})",
				'x-ref' => 'select_actions',

			]);
			$selectAction = $field->render();

			//===============
			// DETERMINE IF CAN POST ACTION

			$options = [
				'id' => "pwcommerce_is_ready_to_save",
				'name' => 'pwcommerce_is_ready_to_save',
				// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '0'
				// 'value' => false,
				'value' => 0,
			];
			//------------------- is_ready_action (getInputfieldHidden)
			$field = $this->getInputfieldHidden($options);
			// add attrs for alpine.js
			$field->attr([
				'x-model.number' => "{$xstore}.is_ready_bulk_edit_action",
				'x-ref' => 'hidden_actions',
			]);
			$hiddenMarkupIsReadyToPostAction = $field->render();
		}
		// ----------------
		// FINAL OUTPUT
		$out =
			"<div class='grid grid-cols-2 gap-4 mb-10'>";
		// add new item
		if (!empty($leftSideContent)) {
			$out .= "<div class='col-span-full md:col-span-1'>" .
				// ADD NEW ITEM OR EXTRA CUSTOM MARKUP LINK
				$leftSideContent .
				"</div>";
		}
		//-----------
		// if bulk edit available in this view
		if (empty($panelOptions['is_hide_bulk_edit'])) {
			$classes = "col-span-full md:justify-self-end";
			// if we have 'add new item link' we share the grid in half
			if (!empty($leftSideContent)) {
				$classes .= " md:col-span-1";
			}
			// bulk edit actions markup
			$out .= "<div class='{$classes}'>" .
				// ACTION APPLY BUTTON
				$applyActionButton .
				// ACTION SELECT
				$selectAction .
				// HIDDEN INPUT FOR 'ACTION READY TO POST'
				$hiddenMarkupIsReadyToPostAction .
				"</div>";
		}
		//-----------
		// close div.grid
		$out .= "</div>";

		return $out;
	}

	// pagination for bulk edit views/pages
	public function getPagination($pages, $options) {
		$baseURL = $options['base_url'];
		$ajaxPostURL = $options['ajax_post_url'];
		//-----------
		$paginationOptions = array(
			// @note: we are posting to the ajax url! However, the display link still stays in context, e.g. 'products'
			'linkMarkup' => "<a href='{url}' hx-post='{$ajaxPostURL}' hx-target='#pwcommerce_bulk_edit_custom_lister' hx-swap='outerHTML' hx-vals='{\"pwcommerce_bulk_edit_custom_lister_pagination\": \"{url}\"}'>{out}</a>",
		);
		$pager = $this->modules->get('MarkupPagerNav');
		$pager->setBaseUrl($baseURL);
		//--------------
		$out = $pager->render($pages, $paginationOptions);
		return $out;
	}

	public function findAnythingMarkup($options) {
		// TODO DEPRECATED SINCE PWCOMMERCE 009; @SEE HOOK 'hookProcessPageSearchLive'
		// TODO DELETE IN NEXT RELEASE
		$ajaxPostURL = $options['ajax_post_url'];
		$wrapper = $this->getInputfieldWrapper();

		// $order = $this->getOrderPage();
		// TODO ADD VALIDATION TO STOP REQUEST IF LESS THAN TWO CHARACTERS @see: https: //htmx.org/docs/#validation
		// search box - for finding anything via home page
		$options = [
			'id' => "pwcommerce_find_anything_search_box",
			'name' => "pwcommerce_find_anything_search_box",
			//'label' => $this->_('Search'),// TODO?
			'label' => ' ',
			// TODO - skipping label? use skiplabel instead? but need spinner!
			'placeholder' => $this->_('Search'),
			'get_url' => $ajaxPostURL,
			'target' => "#pwcommerce_find_anything_results",
			// for hx-target
			'swap' => 'innerHTML',
			// @note: instead of the default 'keyup changed', we will issue the request programmatically via alpine.js
			// this will enable a check for minimum length before issue a request to htmx
			'trigger' => 'pwcommercefindanything',
		];

		$searchBoxField = $this->getSearchBox($options);
		$searchBoxField->headerClass('pwcommerce_find_anything_search_box_inputfield_header pt-0');
		$searchBoxField->contentClass('pwcommerce_find_anything_search_box_inputfield_content pb-0');
		$searchBoxField->attr([
			//'x-on:focusout' => 'handleFindAnythingEvent($event,true)',
			'x-on:focusin' => 'handleFindAnythingEvent',
			'x-on:keydown' => 'handleFindAnythingEvent',
			'x-on:input.debounce.300ms' => 'handleFindAnythingInput',
			//'minlength' => 2
		]);
		$wrapper->add($searchBoxField);

		// TODO: WORK ON THIS...SOMETHING FUNNY ABOUT THE FOCUS OUT?!! IS IT THE INPUT??!!! SHOULD WE MOVE FOCUS OUT TO DIV?

		// found items list
		// @note: data inserted by htmx but also accessed by alpine
		// @note: TODO: alpine bind classes and x-show not working below: we use plain javascript instead! Maybe events cancelling too fast? i.e. change between getting ready to close list then that being cancelled by focus-in on list.
		$out =
			"<div id='pwcommerce_find_anything_results_wrapper' class='relative' x-data='ProcessPWCommerceData' @pwcommercefindanythingshowresultslist.window='handleFindAnythingShowResultsList'>" .
			// TODO: do we need this tabindex? is it ok?
			"<ul id='pwcommerce_find_anything_results' tabindex='-1' class='hidden absolute z-10 list-none w-full -mt-0.5 pt-2.5 pb-5 px-0 leading-4 text-sm'></ul>" .
			"</div>";

		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->getInputfieldMarkup($options);
		$wrapper->add($field);

		// @note: wrapper is centered
		// @note: w-3/4 75%, w-2/3 66%, w-3/5 60%, w-1/2 50%, w-2/5 40%
		$out = "<div id='pwcommerce_find_anything_wrapper' class='my-0 mx-auto md:w-3/5 lg:w-1/2' @focusin='handleFindAnythingEvent' @focusout='handleFindAnythingEvent' @keydown='handleFindAnythingEvent'>" . $wrapper->render() . "</div>";
		return $out;
	}

	public function renderShopCurrencySymbolString($isUseParentheses = true) {
		$currencySymbolStr = '';
		$shopCurrencyLocale = $this->getShopCurrencyLocale();
		if (!empty($shopCurrencyLocale['currency_symbol'])) {
			$currencySymbolStr = $shopCurrencyLocale['currency_symbol'];
			if ($isUseParentheses) {
				$currencySymbolStr = "({$currencySymbolStr})";
			}
		}

		// -----
		return $currencySymbolStr;
	}
}