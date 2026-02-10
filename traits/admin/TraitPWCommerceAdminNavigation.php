<?php

namespace ProcessWire;

trait TraitPWCommerceAdminNavigation
{

	private $shopPage;
	private $installedOptionalFeatures;
	private $optionalFeatures;
	// show addons menu item
	protected $isShowAddonsMenuItem;
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ BREADCRUMB ~~~~~~~~~~~~~~~~~~

	/**
	 * Hook to modify breadcrumb in certain contexts and sub-contexts.
	 *
	 * @param HookEvent $event
	 * @return mixed
	 */
	public function modifyBreadcrumb(HookEvent $event) {

		// hook to modify breadcrumb if we need to
		// @note:  so that we can either go to 'shop' or 'context' pages
		// @note: this is because our structure of executes and physical pages (only shop page) PLUS  use of embedded edit don't match breadcrumbs
		//--------------------,
		// only modify breadcrumb for specific add/edit/view views
		if (!$this->isContextModifyBreadcrumb()) {
			return;
		}
		//------------------------
		// good to go
		// @note: this is one we added for the context in, e.g., getEmbeddedEdit()
		// we take it out temporarily as we want to insert 'shop' breadcrumb in between
		$lastBreadcrumb = $this->wire('breadcrumbs')->pop();
		// @note: ADD OUR NEW 'shop' BREADCRUMB
		$shop = $this->_('Shop');
		$this->wire('breadcrumbs')->add(new Breadcrumb($this->adminURL, $shop));
		// finally, re-add our removed sub-context breadcrumb
		$this->wire('breadcrumbs')->add($lastBreadcrumb);
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MENUS ~~~~~~~~~~~~~~~~~~

	/**
	 * Get Menu.
	 *
	 * @return mixed
	 */
	private function getMenu() {

		$out = "";
		# +++++++++++
		// prep variables to help skip unrequired menu items
		// PWCOMMERCE OPTIONAL FEATURES
		// these are key=>value pairs, e.g. 'product_inventory' => 'inventory', 'product_categories' => 'categories', 'customers' => 'customers', etc.
		$optionalFeatures = $this->pwcommerce->getPWCommerceOptionalFeatures();
		$this->optionalFeatures = array_keys($optionalFeatures);
		// we want the 'keys' since they MATCH the 'values' of 'installedOptionalFeatures'
		// SHOP INSTALLED PWCOMMERCE OPTIONAL FEATURES
		$this->installedOptionalFeatures = $this->pwcommerce->getPWCommerceInstalledOptionalFeatures(PwCommerce::PWCOMMERCE_PROCESS_MODULE);

		$adminURL = $this->adminURL;
		// get the menu items to build the menu
		$menuItems = $this->getMenuItems();

		// for use with alpinejs to open menu item on load for context
		$selected = $this->getSelectedXrefForMenu();
		// open the wrappers
		// @note: local x-data different from the ProcessPWCommerceStore one above!
		// @credit: alpine accordion: @see: https://codepen.io/QJan84/pen/zYvRMMw
		$out .= "<div id='pwcommerce_panel_menu_wrapper' class='pt-3.5'>" .
			"<div id='pwcommerce_panel_menu' x-data='{selected:`{$selected}`}'>" .
			"<ul class='list-none m-0 p-0'>";
		//----------------
		// loop through menu items and build the menu
		foreach ($menuItems as $menu) {
			// skip non-installed optional features
			if (!empty($menu['optional_feature_name']) && $this->isSkipMenuItem($menu['optional_feature_name'])) {
				continue;
			}

			// ----------------------
			$icon = $menu['icon'];
			$menuURL = $adminURL . $menu['url'];
			// append final forward slash
			if (!empty($menu['url'])) {
				$menuURL .= "/";
			}
			$menuLabel = $menu['label'];

			$menuActiveClass = $this->context === $menu['url'] ? " pwcommerce_menu_active" : '';

			// items without submenus
			if (empty($menu['sub_menu'])) {
				// TODO: unsure we need this 'relative' class???
				$out .=
					"<li class='py-1.5 px-0 relative'>" .
					"<a href='{$menuURL}' class='no-underline hover:no-underline cursor-pointer block px-5 py-1.5{$menuActiveClass}'>" .
					// @note the space before the label!
					"<i class='fa fa-fw fa-{$icon}'></i> {$menuLabel}" .
					"</a>" .
					"</li>";
			}
			// parent items
			// TODO IS THIS AFFECTED IF NO OPTIONAL FEATURES FOR PRODUCTS? NO, SINCE ALWAYS HAVE 'products' there
			else {

				$xref = $menu['xref'];
				// TODO: unsure we need this 'relative' class???
				// @note: we are using JavaScript's short-circuit evaluation && here!
				$out .=
					"<li class='py-1.5 px-0 relative pwcommerce_parent_menu_item' :class='selected == `{$xref}` && `pwcommerce_parent_menu_item_open`'>" .
					"<a @click='selected !== `{$xref}` ? selected = `{$xref}` : selected = null' class='no-underline hover:no-underline cursor-pointer block px-5 py-1.5{$menuActiveClass}'>" .
					// @note the space before the label!
					"<i class='fa fa-fw fa-{$icon}'></i> {$menuLabel}" .
					"</a>";
				// @note: 'li' closed after submenu loop below

				// children items wrapper
				$out .= "
				<ul
				class='list-none text-sm px-0 m-0 relative overflow-hidden transition-all max-h-0 duration-700' x-ref='{$xref}'
				x-bind:style='selected == `{$xref}` ? `max-height: \${\$refs.{$xref}.scrollHeight}px` : `` '
				aria-hidden='false'>
		";
				// submenu items
				foreach ($menu['sub_menu'] as $submenu) {
					// skip non-installed optional features in submenu
					if (!empty($submenu['optional_feature_name']) && $this->isSkipMenuItem($submenu['optional_feature_name'])) {
						continue;
					}



					// --------------------
					$submenuURL = $adminURL . $submenu['url'] . "/";
					// TODO: OK LIKE THIS?
					$submenuActiveClass = $this->context == $submenu['url'] ? " pwcommerce_menu_active" : '';
					$out .=
						"<li class='text-sm py-1 px-0'>" .
						"<a href='{$submenuURL}' class='cursor-pointer no-underline hover:no-underline block px-5 py-1.5{$submenuActiveClass}'>{$submenu['label']}</a>" .
						"</li>";
				}
				// end: submenu loop

				// close the submenu and the parent item
				$out .=
					"</ul>" .
					"</li>";
				//-------------
			}
			// end: else menu item with submenu

		}
		// end: main menu loop

		// #####################
		// @update TODO @note: we moved this to be loaded BEFORE getMenuPanel() @see the reason there
		// add aplinejs
		//$out .= $this->getInlineScripts();

		return $out;
	}

	/**
	 * Get Menu Items.
	 *
	 * @return mixed
	 */
	private function getMenuItems() {

		// SPECIAL FOR CATEGORIES
		// 'categories' can be 'collections'

		if (!empty($this->isCategoryACollection) && !empty($this->isInstalledProductCategoriesFeature)) {
			$categoriesMenuLabel = $this->_('Collections');
			$categoriesContext = 'collections';
			$categoriesURL = 'collections';
		} else {
			$categoriesMenuLabel = $this->_('Categories');
			$categoriesContext = 'categories';
			$categoriesURL = 'categories';
		}

		// ----------

		$menuItems = [
			'home' => [
				'label' => $this->_('Home'),
				'url' => '',
				'icon' => 'home',
				'context' => NULL,
			],
			'orders' => [
				'label' => $this->_('Orders'),
				'url' => 'orders',
				'icon' => 'shopping-bag',
				'context' => 'orders'
			],
			// PRODUCTS + RELATED
			'products' => [
				'label' => $this->_('Products'),
				'url' => NULL,
				'xref' => 'products',
				'icon' => 'gift',
				'context' => NULL,
				'sub_menu' => [
					[
						'label' => $this->_('All Products'),
						'url' => 'products',
						'context' => 'products'
					],
					[
						'label' => $this->_('Inventory'),
						'url' => 'inventory',
						'context' => 'inventory',
						'optional_feature_name' => 'product_inventory',
					],
					[
						'label' => $categoriesMenuLabel,
						'url' => $categoriesURL,
						'context' => $categoriesContext,
						'optional_feature_name' => 'product_categories',
					],
					[
						'label' => $this->_('Tags'),
						'url' => 'tags',
						'context' => 'tags',
						'optional_feature_name' => 'product_tags',
					],
					[
						'label' => $this->_('Attributes'),
						'url' => 'attributes',
						'context' => 'attributes',
						'optional_feature_name' => 'product_attributes',
					],
					[
						'label' => $this->_('Types'),
						'url' => 'types',
						'context' => 'types',
						'optional_feature_name' => 'product_types',
					],
					[
						'label' => $this->_('Brands'),
						'url' => 'brands',
						'context' => 'brands',
						'optional_feature_name' => 'product_brands',
					],
					[
						'label' => $this->_('Dimensions'),
						'url' => 'dimensions',
						'context' => 'dimensions',
						'optional_feature_name' => 'product_dimensions',
					],
					[
						'label' => $this->_('Properties'),
						'url' => 'properties',
						'context' => 'properties',
						'optional_feature_name' => 'product_properties',
					],
					// TODO @NOTE - NOT IN MENU FOR NOW -> we now get there via GCs Dashboard
					// ['label' => $this->_('Gift Card Products'), 'url' => 'gift-card-products'],
					[
						'label' => $this->_('Gift Cards'),
						'url' => 'gift-cards',
						'context' => 'gift-cards',
						'optional_feature_name' => 'gift_cards',
					],
					// ['label' => $this->_('Import'), 'url' => 'Import'],
				]
			],
			// ========
			'downloads' => [
				'label' => $this->_('Downloads'),
				'url' => 'downloads',
				'icon' => 'download',
				'context' => 'downloads',
				'optional_feature_name' => 'downloads',
			],
			'shipping' => [
				'label' => $this->_('Shipping'),
				'url' => 'shipping',
				'icon' => 'truck',
				'context' => 'shipping'
			],
			'discounts' => [
				'label' => $this->_('Discounts'),
				'url' => 'discounts',
				'icon' => 'money',
				'context' => 'discounts'
			],
			// CUSTOMERS + RELATED
			'customers' => [
				'label' => $this->_('Customers'),
				'url' => NULL,
				'xref' => 'customers',
				'icon' => 'users',
				'context' => 'customers',
				'optional_feature_name' => 'customers',
				'sub_menu' => [
					[
						'label' => $this->_('All Customers'),
						'url' => 'customers',
						'context' => 'customers',
						'optional_feature_name' => 'customers',
					],
					[
						'label' => $this->_('Customer Groups'),
						'url' => 'customer-groups',
						'context' => 'customer-groups',
						'optional_feature_name' => 'customer_groups',
					],
				]
			],
			// TAXES
			'taxes' => [
				'label' => $this->_('Taxes'),
				'url' => NULL,
				'xref' => 'taxes',
				'icon' => 'percent',
				'context' => 'taxes',
				'sub_menu' => [
					[
						'label' => $this->_('Tax Settings'),
						'url' => 'tax-settings',
						'context' => 'tax-settings'
					],
					[
						'label' => $this->_('Tax Rates'),
						'url' => 'tax-rates',
						'context' => 'tax-rates'
					],
				]
			],
			// REPORTS
			'reports' => [
				'label' => $this->_('Reports'),
				'url' => 'reports',
				'icon' => 'bar-chart',
				'context' => 'reports'
			],
			// SETTINGS
			'settings' => [
				'label' => $this->_('Settings'),
				'url' => NULL,
				'xref' => 'settings',
				'icon' => 'gears',
				'context' => NULL,
				'sub_menu' => [
					// @note: TODO: these two, makes sense to append the 'settings', no?
					[
						'label' => $this->_('General'),
						'url' => 'general-settings',
						'context' => 'general-settings'
					],
					[
						'label' => $this->_('Checkout'),
						'url' => 'checkout-settings',
						'context' => 'checkout-settings'
					],
					[
						'label' => $this->_('Payment Providers'),
						'url' => 'payment-providers',
						'context' => 'payment-providers',
						'optional_feature_name' => 'payment_providers',
					],
					[
						'label' => $this->_('Legal Pages'),
						'url' => 'legal-pages',
						'context' => 'legal-pages',
						'optional_feature_name' => 'legal_pages',
					],
					// ['label' => $this->_('Addons'), 'url' => 'addons'],
				]
			],
		];
		// add addons to menu if activated and user is superuser
		if (!empty($this->isShowAddonsMenuItem)) {
			$menuItems['settings']['sub_menu'][] = ['label' => $this->_('Addons'), 'url' => 'addons', 'context' => 'addons'];
		}

		// ------
		return $menuItems;
	}

	/**
	 * Is Skip Menu Item.
	 *
	 * @param mixed $featureName
	 * @return bool
	 */
	private function isSkipMenuItem($featureName) {
		$isSkipMenuItem = true;
		// check if menu item is for an optional feature and if that feature is installed
		// @note: context will be at KEY 'url'
		if (!empty($featureName)) {
			// DO NOT SKIP IT IN MENU!
			if (in_array($featureName, $this->optionalFeatures) && in_array($featureName, $this->installedOptionalFeatures)) {
				$isSkipMenuItem = false;
			}
		}
		// --------------
		return $isSkipMenuItem;
	}

	/**
	 * Get value for selected property for alpine js for our panel menu.
	 *
	 * @return mixed
	 */
	private function getSelectedXrefForMenu() {
		$selected = null;
		// grab menu items that have children/submenu items only
		$parentMenuItems = array_filter($this->getMenuItems(), function ($item) {
			return !empty($item['sub_menu']);
		});
		//-----------------
		// loop through menu items with children
		foreach ($parentMenuItems as $parentMenuItem) {
			// loop through the sub-menu items to match the parent context to the child
			foreach ($parentMenuItem['sub_menu'] as $subMenuItem) {
				if ($subMenuItem['url'] == $this->context) {
					$selected = $parentMenuItem['xref'];
					break;
				}
			}
		}
		return $selected;
	}

	/**
	 * Get Menu Panel.
	 *
	 * @return mixed
	 */
	private function getMenuPanel() {

		$out = "";

		// ------------
		// ensure first save after install has been done before running the check
		if (!empty($this->pwcommerce->getShopGeneralSettings()->data())) {
			// return early if side menu not enabled!
			if (empty($this->pwcommerce->isUseSideMenu())) {
				return $out;
			}
		}
		####################

		// @note: IMPORTANT! We add this before the panel script otherwise it will get pulled inside the panel markup and lead to the warning: 'Synchronous XMLHttpRequest on the main thread is deprecated because of its detrimental effects to the end user's experience.'  (a jQuery issue?)
		// add aplinejs
		$out .= $this->getInlineScripts();

		// ===================

		// @note PW-PANEL! + ACCORDION MENU
		$pwcommerceMenuPanelLabel = $this->_('Shop Menu');
		$width = "35%";
		// @note: we hide this link but need it so that processwire can build the panel!
		$out .=
			"<a class='hidden pw-panel pw-panel-right pw-panel-tab'
								data-panel-id='pwcommerce_menu_panel'
								data-panel-width='{$width}'
								data-tab-text='$pwcommerceMenuPanelLabel'
								data-tab-icon='cart-plus'
								data-tab-offset='200'
								id='pwcommerce_menu_toggle'
								href='#debug'>" .
			"<i class='fa fa-cart-plus'></i>" .
			$pwcommerceMenuPanelLabel .
			"</a>";
		// @note: inline style = display:none to deal with initial load fouc
		$out .= "<div id='pwcommerce_menu_panel' class='min-h-full' style='display: none;'>" . $this->getMenu() . "</div>";

		return $out;
	}

	/**
	 * Set Is Show Addons Menu Item.
	 *
	 * @return mixed
	 */
	private function setIsShowAddonsMenuItem() {
		$this->isShowAddonsMenuItem = $this->isSuperUser() && $this->pwcommerce->isShopAllowAddons();
	}

	/**
	 * Output JSON list of navigation items for this (intended to for ajax use)
	 *
	 * @param array $options
	 * @return mixed
	 */
	public function ___executeNavJSON(array $options = []) {
		$this->shopPage = $this->pages->get('process=ProcessPWCommerce');

		$this->installedOptionalFeatures = $this->pwcommerce->getPWCommerceInstalledOptionalFeatures(PwCommerce::PWCOMMERCE_PROCESS_MODULE, $isUseRawSQL = true);

		# SUB-MENUS

		$data = ['list' => []];
		switch ($this->input->get->submenu) {

			// PRODUCTS + RELATED
			case 'products':
				$data['list'] = $this->getProductsDataListForNavJSON();
				break;

			// CUSTOMERS + RELATED
			// TODO WHAT IF NOT INSTALLED? REMOVE PROGRAMMATICALLY! SO MOVE TO A METHOD THAT RETURNS THIS AND UNSET!
			case 'customers':
				$data['list'] = $this->getCustomersDataListForNavJSON();
				break;

			// TAXES
			case 'taxes':
				$data['list'] = $this->getTaxesDataListForNavJSON();
				break;

			// SETTINGS
			case 'settings':
				$data['list'] = $this->getSettingsDataListForNavJSON();
				break;
		}

		//ksort($data['list']);
		$data['list'] = array_values($data['list']);

		if ($this->wire()->config->ajax) {
			header("Content-Type: application/json");
		}

		return json_encode($data);
	}
	/**
	 * Get Products Data List For Nav J S O N.
	 *
	 * @return mixed
	 */
	private function getProductsDataListForNavJSON() {
		$shopPage = $this->shopPage;
		// SPECIAL FOR CATEGORIES
		// 'categories' can be 'collections'

		if (!empty($this->isCategoryACollection) && !empty($this->isInstalledProductCategoriesFeature)) {
			$categoriesMenuLabel = $this->_('Collections');
			$categoriesURL = 'collections';
		} else {
			$categoriesMenuLabel = $this->_('Categories');
			$categoriesURL = 'categories';
		}

		// ========
		$dataList = [
			'products' => [
				'url' => $shopPage->url . 'products/',
				'label' => $this->_('All Products'),
				'is_optional_feature' => false,
			],
			'inventory' => [
				'url' => $shopPage->url . 'inventory/',
				'label' => $this->_('Inventory'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'product_inventory',
			],
			'categories' => [
				// 'url' => $shopPage->url . 'categories/',
				'url' => "{$shopPage->url}{$categoriesURL}/",
				// 'label' => $this->_('Categories')
				'label' => $categoriesMenuLabel,
				'is_optional_feature' => true,
				'optional_feature_name' => 'product_categories',
			],
			'tags' => [
				'url' => $shopPage->url . 'tags/',
				'label' => $this->_('Tags'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'product_tags',
			],
			'attributes' => [
				'url' => $shopPage->url . 'attributes/',
				'label' => $this->_('Attributes'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'product_attributes',
			],
			'types' => [
				'url' => $shopPage->url . 'types/',
				'label' => $this->_('Types'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'product_types',
			],
			'brands' => [
				'url' => $shopPage->url . 'brands/',
				'label' => $this->_('Brands'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'product_brands',
			],
			'dimensions' => [
				'url' => $shopPage->url . 'dimensions/',
				'label' => $this->_('Dimensions'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'product_dimensions',
			],
			'properties' => [
				'url' => $shopPage->url . 'properties/',
				'label' => $this->_('Properties'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'product_properties',
			]
			// TODO GIFT CARDS
		];

		// remove non-installed optional features
		foreach ($dataList as $itemName => $itemValues) {
			// process items
			if (empty($itemValues['is_optional_feature'])) {
				continue;
			} else {
				// process: optional feature
				$optionalFeatureName = $itemValues['optional_feature_name'];
				if (!in_array($optionalFeatureName, $this->installedOptionalFeatures)) {
					// optional feature not installed: remove it
					unset($dataList[$itemName]);
				}
			}
		}

		// ----------
		return $dataList;
	}

	/**
	 * Get Customers Data List For Nav J S O N.
	 *
	 * @return mixed
	 */
	private function getCustomersDataListForNavJSON() {
		$shopPage = $this->shopPage;
		// @NOTE: this will not be accessed if features not installed
		// will have been removed from main/parent nav items in 'TraitPWCommerceProcessNavigation::getNavItemsForDropdown'
		$dataList = [
			'customers' => [
				'url' => $shopPage->url . 'customers/',
				'label' => $this->_('All Customers'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'customers',
			],
			'customer_groups' => [
				'url' => $shopPage->url . 'customer-groups/',
				'label' => $this->_('Customer Groups'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'customer_groups',
			]
		];

		// remove non-installed optional features
		foreach ($dataList as $itemName => $itemValues) {
			// process items
			if (empty($itemValues['is_optional_feature'])) {
				continue;
			} else {
				// process: optional feature
				$optionalFeatureName = $itemValues['optional_feature_name'];
				if (!in_array($optionalFeatureName, $this->installedOptionalFeatures)) {
					// optional feature not installed: remove it
					unset($dataList[$itemName]);
				}
			}
		}

		// ----------
		return $dataList;
	}
	/**
	 * Get Taxes Data List For Nav J S O N.
	 *
	 * @return mixed
	 */
	private function getTaxesDataListForNavJSON() {
		$shopPage = $this->shopPage;
		return [
			'tax_settings' => [
				'url' => $shopPage->url . 'tax-settings/',
				'label' => $this->_('Tax Settings')
			],
			'tax_rates' => [
				'url' => $shopPage->url . 'tax-rates/',
				'label' => $this->_('Tax Rates')
			]
		];
	}

	/**
	 * Get Settings Data List For Nav J S O N.
	 *
	 * @return mixed
	 */
	private function getSettingsDataListForNavJSON() {
		$shopPage = $this->shopPage;
		$dataList = [
			'general' => [
				'url' => $shopPage->url . 'general-settings/',
				'label' => $this->_('General'),
				'is_optional_feature' => false,
			],
			'checkout' => [
				'url' => $shopPage->url . 'checkout-settings/',
				'label' => $this->_('Checkout'),
				'is_optional_feature' => false,
			],
			'payment_providers' => [
				'url' => $shopPage->url . 'payment-providers/',
				'label' => $this->_('Payment Providers'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'payment_providers',
			],
			'legal_pages' => [
				'url' => $shopPage->url . 'legal-pages/',
				'label' => $this->_('Legal Pages'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'legal_pages',
			],
			// ++++++++++++++
			// SPECIAL CASE
			// TODO WHAT IF NOT INSTALLED? REMOVE PROGRAMMATICALLY! SO MOVE TO A METHOD THAT RETURNS THIS AND UNSET!
			'addons' => [
				'url' => $shopPage->url . 'addons/',
				'label' => $this->_('Addons'),
				'is_optional_feature' => true,
				'optional_feature_name' => 'addons',
			]
		];

		// remove non-installed optional features
		foreach ($dataList as $itemName => $itemValues) {
			// process items
			if (empty($itemValues['is_optional_feature'])) {
				continue;
			} else {
				// process: optional feature
				$optionalFeatureName = $itemValues['optional_feature_name'];

				// SPECIAL PROCESSING: ADDONS
				if ($optionalFeatureName === 'addons') {
					if (empty($this->isShowAddonsMenuItem)) {
						// addons (optional) feature not installed: remove it
						unset($dataList[$itemName]);
					}
				} else {
					if (!in_array($optionalFeatureName, $this->installedOptionalFeatures)) {
						// optional feature not installed: remove it
						unset($dataList[$itemName]);
					}
				}
			}
		}
		// ----------
		return $dataList;
	}

	# ~~~~~~~~~~~~

	/**
	 * Get Nav Items For Dropdown.
	 *
	 * @return mixed
	 */
	public function getNavItemsForDropdown() {
		// @NOTE: NEEDS THE PAGE '/shop/pwcommerce/' TO BE 'HIDDEN'!!!
		// @NOTE: @see ___executeNavJSON() for 'navJSON' values
		$navItemsForDropdown = [
			'home' => [
				'url' => '',
				'label' => __('Home'),
				'icon' => 'home',
				'is_optional_feature' => false,
			],
			'orders' => [
				'url' => 'orders/',
				'label' => __('Orders'),
				'icon' => 'shopping-bag',
				'is_optional_feature' => false,
			],
			'products' => [
				'url' => 'products/',
				'label' => __('Products'),
				'icon' => 'gift',
				'navJSON' => 'navJSON/?submenu=products',
				'is_optional_feature' => false,
			],
			'downloads' => [
				'url' => 'downloads/',
				'label' => __('Downloads'),
				'icon' => 'download',
				'is_optional_feature' => true,
				'optional_feature_name' => 'downloads',
			],
			'shipping' => [
				'url' => 'shipping/',
				'label' => __('Shipping'),
				'icon' => 'truck',
				'is_optional_feature' => false,
			],
			'discounts' => [
				'url' => 'discounts/',
				'label' => __('Discounts'),
				'icon' => 'money',
				'is_optional_feature' => true,
				'optional_feature_name' => 'discounts',
			],
			'customers' => [
				'url' => 'customers/',
				'label' => __('Customers'),
				'icon' => 'users',
				'navJSON' => 'navJSON/?submenu=customers',
				'is_optional_feature' => true,
				'optional_feature_name' => 'customers',
			],
			'taxes' => [
				'url' => 'tax-settings/',// note: default to 'tax settings' in case main menu item clicked
				'label' => __('Taxes'),
				'icon' => 'percent',
				'navJSON' => 'navJSON/?submenu=taxes',
				'is_optional_feature' => false,
			],
			'reports' => [
				'url' => 'reports/',
				'label' => __('Reports'),
				'icon' => 'bar-chart',
				'is_optional_feature' => false,
			],
			'settings' => [
				'url' => 'general-settings/',// note: default to 'general settings' in case main menu item clicked
				'label' => __('Settings'),
				'icon' => 'gears',
				'navJSON' => 'navJSON/?submenu=settings',
				'is_optional_feature' => false,
			],

		];

		// remove nav items for optional features that are not installed
		// @NOTE: this is only for top level items
		// for submenus we post-process in ___executeNavJSON()
		$navItemsForDropdown = $this->postProcessNavItemsForDropdown($navItemsForDropdown);

		// ------
		return $navItemsForDropdown;
	}

	/**
	 * Post Process Nav Items For Dropdown.
	 *
	 * @param mixed $navItemsForDropdown
	 * @return mixed
	 */
	private function postProcessNavItemsForDropdown($navItemsForDropdown) {

		$installedOptionalFeatures = $this->pwcommerce->getPWCommerceInstalledOptionalFeatures(PwCommerce::PWCOMMERCE_PROCESS_MODULE, $isUseRawSQL = true);

		$finaNavItemsForDropdown = [];
		foreach ($navItemsForDropdown as $itemName => $itemValues) {
			// process items
			if (empty($itemValues['is_optional_feature'])) {
				// process: non-optional feature
				$finaNavItemsForDropdown[$itemName] = $itemValues;
			} else {
				// process: optional feature
				$optionalFeatureName = $itemValues['optional_feature_name'];
				if (in_array($optionalFeatureName, $installedOptionalFeatures)) {
					// optional feature installed: retain it
					$finaNavItemsForDropdown[$itemName] = $itemValues;
				}
			}
		}

		// ---------
		return $finaNavItemsForDropdown;
	}

}