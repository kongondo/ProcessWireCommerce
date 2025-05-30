# PWCommerce Addons

>This code in this folder is for managing PWCommerce addons. Do not add your addons to this folder. Instead, add them to `/site/templates/pwcommerce/addons/`

## Introduction

Addons allow you to extend the functionality of PWCommerce. Currently, only backend addons are supported. The addons feature enable you to add third-party payment gateways, create GUIs to display, edit or compute shop related elements. For instance, you can add an addon to show and display products images. You could edit the images in a dedicated page. The possibilities are endless.

## Enabling Addons

In order to use the addons, you have to enable the feature in the `main` Tab in your shop general settings (`/shop/general-settings/`). In addition you will need to create a folder called *addons* under `/site/templates/pwcommerce/`.

Once enabled, a menu item will appear as `settings > addons`.

> Currently, only Superusers can enable, view and manage addons.

## Adding Addons

The location for PWCommerce addons is  `/site/templates/pwcommerce/addons/`.

Addons files should be placed in  folder whose name is identical to its addon class. For instance, the folder *MyAddon* will contain files including *MyAddon.php*. In this case, `MyAddon.php` must `implement` `PWCommerceAddons`.

## Addons Class

All addons must implement the `PWCommerceAddons Interface`. Addons classes can extend any class as needed. Some addons will need to extend specific classes. For instance, addons that are payment gateways must extend the `PWCommercePayment` abstract class.

### Interface Methods

PWCommerceAddons Interface requires the following `public methods` to be implemented.
``` php

  /**
   * Returns addon type.
   *
   * Must be one of PWCommerce pre-defined types.
   * @see documentation.
   * @return string $type
   */
  public function getType();

	/**
	 * Returns fields schema for building GUI for editing fields/inputs for this addon.
	 *
	 * @see documentation.
	 * @return array $schema.
	 */
	public function getFieldsSchema() {
	}

	/**
	 * Returns user-friendly title of this addon.
	 *
	 * @return string $title.
	 */
	public function getTitle() {
	}

	/**
	 * Returns short description of this addon.
	 *
	 * @return string $description.
	 */
	public function getDescription() {
	}

```

## Activating Addons

PWCommerce will parse the contents of  `/site/templates/pwcommerce/addons/` to discover and display available addons. These can be viewed in the shop dashboard at `/shop/addons/`.