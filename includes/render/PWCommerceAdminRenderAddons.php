<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Addons.
 *
 * Class to render content for PWCommerce Admin Module executeAddons().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderAddons for PWCommerce
 * Copyright (C) 2022 by Francis Otieno
 * MIT License
 *
 */



class PWCommerceAdminRenderAddons extends WireData
{

	private $adminURL;
	private $ajaxPostURL;

	private $addonsPath;
	private $addonsSettings;

	// ---------
	private $addonClass;
	private $addonTitle;
	private $addonDetails;
	private $addonPageID;
	// ---------
	private $paymentAddonsNames;
	private $nonPaymentCustomAddonsNames;


	// ----------

	public function __construct($options = null) {
		parent::__construct();
		if (is_array($options)) {
			$this->adminURL = $options['admin_url'];
			$this->ajaxPostURL = $options['ajax_post_url'];
		}

		// ------------
		// set pre-defined addons path
		$this->addonsPath = $this->wire('config')->paths->templates . "pwcommerce/addons/";
		// --------
		// TODO DELETE WHEN DONE AS NO LONGER APPLICABLE
		// get and set addons settings (from addons page)
		// $this->setAddonsSettings();

		$input = $this->wire('input');
		$urlSegment3 = $input->urlSegment3;

		// -----------
		$addonViewURL = $this->wire('sanitizer')->pageName($urlSegment3);

		if (!empty($addonViewURL)) {
			$this->setAddonClass($addonViewURL);
		}

		// GET NAMES OF INSTALLED NON-CORE PAYMENT ADDONS
		$this->paymentAddonsNames = $this->pwcommerce->getNonCorePaymentProvidersNames();

		//---------------
		// GET NAMES OF INSTALLED CUSTOM ADDONS
		$this->nonPaymentCustomAddonsNames = $this->pwcommerce->getNonPaymentCustomAddonsNames();

		// *************
		// COMBINE ALL ADDONS id => name pairs
		$this->setAllAddonsNames();

	}

	private function setAllAddonsNames() {
		// @note: we need to preserve keys!
		$allAddonsNames = $this->paymentAddonsNames + $this->nonPaymentCustomAddonsNames;

		// -----------
		$this->allAddonsNames = $allAddonsNames;
	}

	private function setAddonClass($addonViewURL) {

		if (empty($this->pwcommerce->isValidAddonViewURL($addonViewURL))) {
			// $this->wire('session')->redirect();
			// $adminURL = '';
			// TODO: WE WILL ALWAYS HAVE AN ADMIN URL, BUT JUST SANITY CHECK!
			$this->session->redirect($this->getRedirectURL());
		}

		# TODO TESTING GETTING ADDON DETAILS BY 'view_url' name

		##################

		$addonsSettings = $this->pwcommerce->getAddonsSettings();
		$nonPaymentCustomAddonConfigs = array_filter($addonsSettings, fn($item) => !empty ($item['pwcommerce_addon_view_url']) && $item['pwcommerce_addon_view_url'] === $addonViewURL);

		$addonClassName = null;
		if (!empty($nonPaymentCustomAddonConfigs)) {
			$addonClassName = array_keys($nonPaymentCustomAddonConfigs)[0];
		}

		if (empty($addonClassName)) {
			// $this->session->redirect($redirectURL);
			$this->session->redirect($this->getRedirectURL());
		}
		// -------------------
		// get first item in array
		$addonDetails = reset($nonPaymentCustomAddonConfigs);

		// TODO GET DETAILS FROM addon PAGE ITSELF?, i..e using 'pwcommerce_addon_page_id'
		$genericTitle = $this->_('Unnamed Addon');
		$addonTitle = !empty($addonDetails['pwcommerce_addon_title']) ? $addonDetails['pwcommerce_addon_title'] : $genericTitle;

		// -------------
		// IF ADDON USES AJAX
		$isAddonUseAjax = !empty($addonDetails['pwcommerce_addon_is_addon_use_ajax']);

		// -------------
		// IF ADDON IS CONFIGURABLE
		$isAddonConfigurable = !empty($addonDetails['pwcommerce_addon_is_addon_configurable']);

		// =========
		// GET and SET ADDON CLASS
		$addonClass = $this->getAddonClass($addonClassName);

		// just check once more if class has render() method
		if (empty(method_exists($addonClass, 'render'))) {
			// NO RENDER METHOD IN CLASS FOR SOME REASON: REDIRECT
			$notice = sprintf(__("The addon %s is not viewable."), $addonTitle);
			$this->error($notice);
			$this->session->redirect($this->getRedirectURL());
		} elseif ($isAddonUseAjax && empty(method_exists($addonClass, 'setAddonAjaxEndpoint'))) {
			// NO SET ADDON AJAX END POINT METHOD IN CLASS FOR SOME REASON yet class uses ajax: REDIRECT
			$notice = sprintf(__("The addon %s is not viewable."), $addonTitle);
			$this->error($notice);
			$this->session->redirect($this->getRedirectURL());
		} elseif ($isAddonConfigurable && empty(method_exists($addonClass, 'setAddonPage'))) {
			// NO setAddonPage METHOD IN CLASS FOR CONFIGURABLE ADDON FOR SOME REASON: REDIRECT
			$notice = sprintf(__("The addon %s is not viewable."), $addonTitle);
			$this->error($notice);
			$this->session->redirect($this->getRedirectURL());
		} else {
			// ===============
			// GOOD TO GO
			$this->addonClass = $addonClass;
			$this->addonDetails = $addonDetails;
			$this->addonTitle = $addonTitle;
			$this->addonPageID = $addonDetails['pwcommerce_addon_page_id'];
			// ------------
			// SET AJAX ENDPOINT if needed
			if (!empty($isAddonUseAjax)) {
				$addonAjaxEndpoint = $this->ajaxPostURL;
				$this->addonClass->setAddonAjaxEndpoint($addonAjaxEndpoint);
			}
		}
	}

	/**
	 * Render single order view headline to append to the Process headline in PWCommerce.
	 *
	 * @return string $out Headline string to append to the main Process headline.
	 */
	public function renderViewItemHeadline() {
		$headline = $this->getCustomAddonRender(true);
		return $headline;
	}
	public function renderViewItem() {

		// get whole render for custom addon
		// @note: will redirect if errors were found
		// e.g. if no render method or empty $urlSegment3, etc

		$out = $this->getCustomAddonRender();

		# +++++++++++++

		$wrapper = $this->pwcommerce->getInputfieldWrapper();

		//------------------
		// generate final markup
		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			// TODO: DELETE IF NOT IN USE
			'classes' => 'pwcommerce_addon_view',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $out,
		];

		$field = $this->pwcommerce->getInputfieldMarkup($options);
		$wrapper->add($field);

		// ------------
		// ADD REQUIRED HIDDEN INPUT
		// lets ProcessPwCommerce::pagesHandler know that we are ready to save
		$options = [
			'id' => "pwcommerce_is_ready_to_save",
			'name' => 'pwcommerce_is_ready_to_save',
			// TODO @NOTE CHANGE POST-PROCESSWIRE 3.0.203 - this is not typecasting to '1'
			// 'value' => true,
			'value' => 1,
		];
		//------------------- is_ready_to_save (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		return $wrapper->render();
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~

	protected function renderResults($selector = null) {

		if ($this->wire('config')->ajax) {
			$out = $this->handleAddonAjaxRequest($selector);
			return $out;
		}

		// enforce to string for strpos for PHP 8+
		$selector = strval($selector);

		//-----------------
		// FORCE DEFAULT LIMIT IF NO USER LIMIT SET
		if (strpos($selector, 'limit=') === false) {
			$limit = 10;
			$selector = rtrim("limit={$limit}," . $selector, ",");
		}

		//-----------------
		// TODO IS THIS APPLICABLE HERE?
		// BUILD FINAL MARKUP TO RETURN TO ProcessPwCommerce::pagesHandler()
		// @note: important: don't remove the class 'pwcommerce_inputfield_selector'! we need it for htmx (hx-include)
		$out =
			"<div id='pwcommerce_bulk_edit_custom_lister' class='pwcommerce_inputfield_selector pwcommerce_show_highlight mt-5'>" .
			// BULK EDIT ACTIONS
			$this->getBulkEditActionsPanel() .
			// TODO INCLUDE THIS IN VIRTUAL PAGE ARRAY?
			// PAGINATION STRING (e.g. 1 of 25)
			// "<h3 id='pwcommerce_bulk_edit_custom_lister_pagination_string'>" . $pages->getPaginationString('') . "</h3>" .
			// TABULATED RESULTS (if pages found, else 'none found' message is rendered)
			$this->getResultsTable() .
			// HIDDEN INPUT FOR HTMX
			// set the context for differentiation when in ajax page
			"<input type='hidden' value='addons' name='pwcommerce_inputfield_selector_context'>" .
			// TODO INCLUDE THIS IN VIRTUAL PAGE ARRAY?
			// PAGINATION (render the pagination navigation)
			// $this->pwcommerce->getPagination($pages, $this->paginationOptions()) .
			//---------------
			"</div>";

		return $out;
	}


	private function getResultsTableHeaders() {
		// TODO: DO WE USE TW CLASSES HERE?
		$selectAllCheckboxName = "pwcommerce_bulk_edit_selected_items_all";
		$xref = 'pwcommerce_bulk_edit_selected_items_all';
		return [
			// SELECT ALL CHECKBOX
			$this->getBulkEditCheckbox('all', $selectAllCheckboxName, $xref),
			// TODO: make these classes generic? e.g. for th percent width?
			// NAME
			[$this->_('Name'), 'pwcommerce_addons_table_name'],
			// TYPE
			[$this->_('Type'), 'pwcommerce_addons_table_type'],
			// DESCRIPTION TODO make required in schema!
			[$this->_('Description'), 'pwcommerce_addons_table_description'],
			// INSTALLED/ACTIVE TODO?
			[$this->_('Active'), 'pwcommerce_addons_table_active'],
		];
	}

	private function getResultsTable() {
		$out = "";
		if (is_dir($this->addonsPath)) {
			$addons = $this->getAddonsList();

			$extraFormInputsForAddonsProcessing = "";
			if (empty($addons)) {
				$out = "<p>" . $this->_('No addons found.') . "</p>";
			} else {

				$active = $this->_('Activated');
				$inactive = $this->_('Not activated');

				// ----------------
				$field = $this->modules->get('MarkupAdminDataTable');
				$field->setEncodeEntities(false);
				// set headers (th)
				$field->headerRow($this->getResultsTableHeaders());
				$checkBoxesName = "pwcommerce_bulk_edit_selected_items[]";
				// set each row
				// TODO -> ALSO NEED TO CHECK THAT PPS HAVE CONFIGS IN THEM!!
				foreach ($addons as $addonClassName => $addonValues) {
					$activeAddonString = !empty($addonValues['is_addon_active']) ? $active : $inactive;
					// ----------------
					$row = [
						// CHECKBOX
						$this->getBulkEditCheckbox($addonClassName, $checkBoxesName),
						// NAME
						// $addonValues['title'],
						$this->getAddonTitle($addonClassName, $addonValues),
						// TYPE
						$this->getAddonType($addonValues['type']),
						// DESCRIPTION
						$this->getAddonDescription($addonValues['description']),
						// ACTIVE TODO: ACTIVE ADDON (i.e. installed and page created?)
						// TODO -> FIND WAY TO INDICATE INSTALLED; maybe like ProcessWire 'install.php'???
						$activeAddonString,
					];
					$field->row($row);
					// ------
					// APPEND EXTRA ADDON INPUTS FOR processing in PWCommerceActions::actionAddons
					$extraFormInputsForAddonsProcessing .= $this->buildExtraAddonInputsMarkup($addonClassName, $addonValues);
				}
				// @note: render like this instead of inside an InputfieldMarkup is fine since in ProcessPwCommerce::pagesHandler() we add the output here to an InputfieldMarkup which is then added to an InputfieldWrapper that we then render.
				$out = $field->render() . $extraFormInputsForAddonsProcessing;
			}
		} else {
			$out = "<p>" . $this->_('Addons directory not found.') . "</p>";
		}

		return $out;
	}

	private function getAddonsList() {
		$path = $this->addonsPath;

		// TODO - throw into own function?

		$addonsClassesList = [];
		$addonsList = [];
		$files = $this->wire('files');
		// $iterator = new \DirectoryIterator($path);

		try {
			//code...
			$iterator = new \DirectoryIterator($path);
		} catch (\Throwable $th) {
			//throw $th;
			// No 'addons' directory found ('/site/templates/pwcommerce/addons/') OR other error
			return $addonsList;
		}

		foreach ($iterator as $fileinfo) {
			if ($fileinfo->isDir() && !$fileinfo->isDot()) {
				// echo $fileinfo->getFilename() . "\n";
				$addonDirectoryName = $fileinfo->getFilename();
				$addonPath = $fileinfo->getPath();

				if ($files->exists("{$addonPath}/{$addonDirectoryName}/{$addonDirectoryName}.php", 'readable')) {

					$addonsClassesList[] = $fileinfo->getFilename();
				}
				;
			}
		}

		// ---------
		// BUILD ADDONS LIST
		// if we have addons classes, get their values (title, description, type, viewable link, etc)
		if (!empty($addonsClassesList)) {
			$addonsList = $this->buildAddonsList($addonsClassesList);
		}

		return $addonsList;
	}

	private function buildAddonsList($addonsClassesList) {
		$addonsList = [];
		// we only allow these addon types
		/** @var array $allowedAddonTypes */
		$allowedAddonTypes = $this->getAllowedAddonTypes();

		$sanitizer = $this->wire('sanitizer');
		// ----------
		foreach ($addonsClassesList as $addonClassName) {
			$addonClass = $this->getAddonClass($addonClassName);

			// --------
			$addonType = $sanitizer->option($addonClass->getType(), $allowedAddonTypes);

			// NO ALLOWED ADDON TYPE: SKIP
			if (empty($addonType)) {

				continue;
			}

			// NO TITLE: SKIP
			$title = $sanitizer->text($addonClass->getTitle());
			if (empty($title)) {

				continue;
			}

			// CHECK IF ADDON OF TYPE 'custom' HAS REQUIRED property 'renderViewURL' and method 'render'
			// -----------
			if ($addonType === 'custom') {
				if (!property_exists($addonClass, 'renderViewURL') || !method_exists($addonClass, 'render')) {
					// custom type requires property renderViewURL and method render()
					// this class does not have both or either; skip

					continue;
				}
				$isAddonUseAjax = false;
				// CHECK IF ADDON OF TYPE 'custom' IS USING AJAX
				// this can be for get or post
				// -----------
				if (!empty($addonClass->isAddonUseAjax)) {

					$isAddonUseAjax = true;
				}

				// TODO MIGHT DELETE THIS? SINCE ALL CONFIGURABLE NOW!
				$isAddonConfigurable = false;
				// CHECK IF ADDON OF TYPE 'custom' IS CONFIGURABLE
				// this will signal that a config page needs to be created for it
				// -----------
				if (!empty($addonClass->isConfigurable)) {

					$isAddonConfigurable = true;
				}
			}

			// ==============
			// GOOD TO GO
			// -----------
			$isAddonActive = $this->isAddonActive($title);

			$addonsList[$addonClassName] = [
				'addon_class_name' => $addonClassName,
				'type' => $addonType,
				'title' => $title,
				'description' => $addonClass->getDescription(),
				'is_addon_active' => $isAddonActive,
				'id' => $this->getAddonID($title),

			];
			if ($addonType === 'custom') {
				$addonViewURL = $sanitizer->pageName($addonClass->renderViewURL);
				// view URL must contain at least one letter
				if (empty($this->pwcommerce->isValidAddonViewURL($addonViewURL))) {
					// if (empty(preg_match('/[a-zA-Z]/', $addonViewURL))) {
					// ADDON VIEW URL NOT VALID

					// NO ADDON VIEW URL: SKIP
					unset($addonsList[$addonClassName]);
					continue;
				}

				$addonsList[$addonClassName]['view_url'] = $addonViewURL;
				// --------------
				// addon will use ajax
				if (!empty($isAddonUseAjax)) {
					$addonsList[$addonClassName]['is_addon_use_ajax'] = 1;
				}
				// addon is configurable (needs config page to be created)
				if (!empty($isAddonConfigurable)) {
					$addonsList[$addonClassName]['is_addon_configurable'] = 1;
				}
			}
			// ---------
			// UNSET CLASS AS NO LONGER NEEDED TODO ok?
			unset($addonClass);
		}

		// ----------
		return $addonsList;
	}

	private function buildExtraAddonInputsMarkup($addonClassName, $addonValues) {
		$out = "";
		// --------

		// addon ClassName input
		$options = [
			'id' => "pwcommerce_addon_class_name_{$addonClassName}",
			'name' => "pwcommerce_addon_class_name_{$addonClassName}",
			'value' => $addonValues['addon_class_name'],
		];
		//------------------- pwcommerce_addon_class_name (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$out .= $field->render();

		// addon type input
		$options = [
			'id' => "pwcommerce_addon_type_{$addonClassName}",
			'name' => "pwcommerce_addon_type_{$addonClassName}",
			'value' => $addonValues['type'],
		];
		//------------------- pwcommerce_addon_type (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$out .= $field->render();

		// addon title input
		$options = [
			'id' => "pwcommerce_addon_title_{$addonClassName}",
			'name' => "pwcommerce_addon_title_{$addonClassName}",
			'value' => $addonValues['title'],
		];
		//------------------- pwcommerce_addon_title (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$out .= $field->render();

		// addon id
		// @note: if activate, will have a page->id, else 0
		$options = [
			'id' => "pwcommerce_addon_id_{$addonClassName}",
			'name' => "pwcommerce_addon_id_{$addonClassName}",
			'value' => $addonValues['id'],
		];
		//------------------- pwcommerce_addon_id (getInputfieldHidden)
		$field = $this->pwcommerce->getInputfieldHidden($options);
		$out .= $field->render();

		// TODO SEE IF WE STILL NEED IS CONFIGUARABLE BELOW?
		// CUSTOM ADDON TYPE
		if ($addonValues['type'] === 'custom') {
			// ++++++++++++
			// needs a 'view_url' value
			// addon view url input
			$options = [
				'id' => "pwcommerce_addon_view_url_{$addonClassName}",
				'name' => "pwcommerce_addon_view_url_{$addonClassName}",
				'value' => $addonValues['view_url'],
			];
			//------------------- pwcommerce_addon_view_url (getInputfieldHidden)
			$field = $this->pwcommerce->getInputfieldHidden($options);
			$out .= $field->render();
			// ================
			// OTHER CUSTOM ADDON CHECKS
			// ++++++++++++
			// does addon use ajax?
			if (!empty($addonValues['is_addon_use_ajax'])) {
				// needs a 'is_addon_use_ajax' value
				// addon is use ajax input
				$options = [
					'id' => "pwcommerce_addon_is_addon_use_ajax_{$addonClassName}",
					'name' => "pwcommerce_addon_is_addon_use_ajax_{$addonClassName}",
					'value' => $addonValues['is_addon_use_ajax'],
				];
				//------------------- pwcommerce_addon_is_addon_use_ajax (getInputfieldHidden)
				$field = $this->pwcommerce->getInputfieldHidden($options);
				$out .= $field->render();
			}
			// ++++++++++++
			// is addon configurable?
			if (!empty($addonValues['is_addon_configurable'])) {
				// needs a 'is_addon_configurable' value
				// addon is configurable input
				$options = [
					'id' => "pwcommerce_addon_is_addon_configurable_{$addonClassName}",
					'name' => "pwcommerce_addon_is_addon_configurable_{$addonClassName}",
					'value' => $addonValues['is_addon_configurable'],
				];
				//------------------- pwcommerce_addon_is_addon_configurable (getInputfieldHidden)
				$field = $this->pwcommerce->getInputfieldHidden($options);
				$out .= $field->render();
			}
		}

		// ------
		return $out;
	}

	private function getAddonClass($addonClassName) {
		$addonClassFilePath = $this->addonsPath . "{$addonClassName}/{$addonClassName}.php";

		require_once $addonClassFilePath;
		// TODO is this efficient? What if lots of addons? YAML? JSON CONFIGS?
		// instantiate class
		$class = "\ProcessWire\\" . $addonClassName;
		// TODO CONSTRUCTOR TRICKY SINCE CANNOT KNOW BEFOREHAND WHAT ARGUMENTS NEEDED! SHOULD WE ASK ADDONS NOT TO INCLUDE REQUIRED PARAMS?
		// $addonClass = new $class([]);
		/** @var object $addonClass */
		$addonClass = new $class();

		// ------
		return $addonClass;
	}

	private function getAllowedAddonTypes() {
		// TODO ADD MORE AS NEEDED!
		return [
			'custom',
			'payment'
		];
	}

	private function getAddonsUserFriendlyTypes() {
		// TODO ADD MORE AS NEEDED
		return [
			'custom' => $this->_('Custom'),
			'payment' => $this->_('Payment Gateway')
		];
	}

	private function getAddonTitle($addonClassName, $addonValues) {
		// TODO - ALSO ADD CHECK FOR 'is_addon_locked'? not easy since only active addons are in addons settings/
		$out = $addonValues['title'];
		// if addon is inacive, no edit/view url for it
		$addonType = $addonValues['type'];
		if (!empty($addonValues['is_addon_active'])) {
			// ADDON IS ACTIVE
			// -------------
			if ($addonType === 'payment') {
				// BUILD EDIT PAYMENT ADDON EDIT LINK
				$out = $this->getPaymentAddonEditURL($addonValues['title']);
			} else if ($addonType === 'custom') {
				// BUILD VIEW CUSTOM ADDON LINK
				$out = $this->getCustomAddonViewURL($addonValues);
			}
		}
		// ----------
		return $out;
	}

	private function getCustomAddonViewURL($addonValues) {
		// TODO FOR NOW 'LOCKED' STATUS NOT IN USE; NOT SURE ABOUT ITS NEED
		// TODO: CHECK IF UNLOCKED FIRST!??

		$addonTitle = $addonValues['title'];
		$addonViewURL = $addonValues['view_url'];

		$out = "<a href='{$this->adminURL}addons/view/{$addonViewURL}/'>{$addonTitle}</a>";
		return $out;
	}

	private function getPaymentAddonEditURL($addonTitle) {
		$addonID = $this->getAddonID($addonTitle);

		// if payment addon page not found, don't show edit URL
		if (empty($addonID)) {
			$out = "<span>{$addonTitle}</span>";
		} else {
			$out = "<a href='{$this->adminURL}payment-providers/edit/?id={$addonID}'>{$addonTitle}</a>";
		}
		return $out;
	}

	private function getAddonID($addonTitle) {
		// non-core payment addons
		$addonsNames = $this->allAddonsNames;
		$addonName = $this->wire('sanitizer')->pageName($addonTitle);

		$addonID = 0;
		foreach ($addonsNames as $id => $name) {
			if ($name === $addonName) {
				$addonID = $id;
				break;
			}
		}

		// -------------
		return $addonID;
	}

	private function getAddonType($addonType) {
		$allAddonTypes = $this->getAddonsUserFriendlyTypes();
		// @note: we can assume user friendly name exists since we only allowed pre-defined addon types
		// TODO OK
		$out = $allAddonTypes[$addonType];
		// ------
		return $out;
	}

	private function getAddonDescription($description) {
		$out = $this->wire('sanitizer')->purify($description);
		if (empty(preg_match('/\S/', $out))) {
			$out = $this->_('Addon has no description.');
		}
		// ------
		return $out;
	}

	private function isAddonActive($addonTitle) {
		$addonsNames = $this->allAddonsNames;
		// -------------
		$addonName = $this->wire('sanitizer')->pageName($addonTitle);

		return in_array($addonName, $addonsNames);
	}

	private function getBulkEditActionsPanel() {
		$actions = [
			// @note: means published
			'activate' => $this->_('Active'),
			// @note: means unpublished
			'deactivate' => $this->_('Inactive'),
			// TODO HOW TO DO THESE? needed?
			// 'lock' => $this->_('Lock'),
			// 'unlock' => $this->_('Unlock'),
			// TODO: UNSURE ABOUT DELETION? OR IF NOT PRESENT, JUST HIDE?
			// 'trash' => $this->_('Trash'),
			// 'delete' => $this->_('Delete'),
		];
		$options = [
			// bulk edit select action
			'bulk_edit_actions' => $actions,
		];
		// TODO: NEED TO HIDE ADD NEW!
		$out = $this->pwcommerce->getBulkEditActionsPanel($options);

		return $out;
	}

	private function getBulkEditCheckbox($id, $name, $xref = null) {
		$options = [
			'id' => "pwcommerce_bulk_edit_checkbox{$id}",
			'name' => $name,
			'label' => ' ',
			// @note: skipping label
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_bulk_edit_selected_items',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $id,

		];
		$field = $this->pwcommerce->getInputfieldCheckbox($options);
		// TODO: ADD THIS ATTR AND MAYBE EVEN A x-ref? so we can selectall using alpinejs
		$field->attr([
			'x-on:change' => 'handleBulkEditItemCheckboxChange',
		]);

		return $field->render();
	}

	private function getRedirectURL() {
		if (!empty($this->adminURL)) {
			// redirect to /shop/addons/
			$redirectURL = $this->adminURL . "addons/";
		} else {
			// redirect to /shop/
			$redirectURL = $this->wire('page')->url;
		}
		// -------
		return $redirectURL;
	}

	private function getCustomAddonRender($isOnlyTitle = false) {

		// TODO PASS OPTIONS to render()? SUCH AS?
		// TODO @update we pass to the class's setAddonPage
		if (!empty($this->addonDetails['pwcommerce_addon_is_addon_configurable'])) {
			// @note: just being doubly sure hence passing expected template name as well
			$addonPage = $this->pwcommerce->get("template=" . PwCommerce::SETTINGS_TEMPLATE_NAME . ",id={$this->addonPageID}");

			if (empty($addonPage->id)) {
				// NO ADDON PAGE FOUND FOR SOME REASON: REDIRECT
				$notice = sprintf(__("The addon %s is not viewable."), $this->addonTitle);
				$this->error($notice);
				$this->session->redirect($this->getRedirectURL());
			}
			// GOOD TO GO
			$addonSettingsFieldName = PwCommerce::SETTINGS_FIELD_NAME;
			$this->addonClass->setAddonPage($addonPage, $addonSettingsFieldName);
		}
		$out = !empty($isOnlyTitle) ? $this->addonTitle : $this->addonClass->render();
		// ------
		return $out;
	}

	################# PROCESS FORMS ######################
	// @NOTE: NOT ALL ADDONS NEED TO PROCESS FORMS

	public function processForm(InputfieldForm $form, WireInputData $input) {

		// #####################
		// ERROR HANDLING
		$form->processInput($input);
		$errors = $form->getErrors();
		// TODO @note -> errors not getting caught for now in our non-tabs forms! e.g. tax settings or payment provider, so, will need to check missing required values ourselves. for tab-based forms, e.g. general settings, getErrors() works fine.
		// TODO: BETTER ERROR MESSAGE HERE?

		if (count($errors)) {

			$this->error($this->_('There were errors. No action taken'));
			return;
		}

		if (empty(method_exists($this->addonClass, 'processForm'))) {
			// NO PROCESSFORM CLASS FOR SOME REASON: REDIRECT
			$notice = sprintf(__("The addon %s cannot be processed."), $this->addonTitle);
			$this->error($notice);
			$this->session->redirect($this->getRedirectURL());
			return;
		}

		// #####################
		// GOOD TO GO
		$this->addonClass->processForm($form, $input);
	}

	private function handleAddonAjaxRequest($selector) {
		$out = "";
		$sanitizer = $this->wire('sanitizer');
		$input = $this->wire('input');
		$requestType = $sanitizer->text($selector);
		// ----------

		if ($requestType === 'GET') {
			// GET REQUEST
			// for GET requests we expect the 'view name' of the addon to have been sent!
			$addonViewURL = $sanitizer->pageName($input->pwcommerce_addon_view_url);

			if (!empty($addonViewURL)) {

				// we attempt to set the addon class using the $addonViewURL
				// if it succeeds, we will have $this->addonClass populated
				$this->setAddonClass($addonViewURL);
			}
		} else if ($requestType === 'POST') {
			// POST REQUEST

		}

		############

		// ----------

		# *************************
		// CHECK IF WE GOT A CLASS & HAS NEEDED METHOD TO PROCESS AJAX REQUEST

		$isError = false;
		if (empty($this->addonClass)) {
			// ERROR: addon class was not found!
			$isError = true;
		} else {
			// OK: addon class found
			if (!method_exists($this->addonClass, 'processAjaxForm')) {
				// ERROR: addon class does not have the required method to process ajax requests
				$isError = true;
			}
		}

		// gather either response from addon class OR error message from this class
		$out = empty($isError) ? $this->addonClass->processAjaxForm($input) : $this->getAjaxErrorMessage();

		// ---------
		return $out;
	}

	private function getAjaxErrorMessage() {
		$out = $this->_("Sorry we could not process your request.");
		$out = "<div><p>{$out}</p></div>";
		// -----------
		return $out;
	}
}