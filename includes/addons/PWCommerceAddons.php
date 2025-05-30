<?php

namespace ProcessWire;

/**
 * PWCommerce: Addons Interface.
 *
 * Provides the base interfaces required by PWCommerce third-party addons.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAddons for PWCommerce
 * Copyright (C) 2022 by Francis Otieno
 * MIT License
 *
 */

interface PWCommerceAddons
{

  /**
   * Returns addon type.
   *
   * Must be one of PWCommerce pre-defined types.
   * @see documentation.
   * @return string $type
   */
  public function getType();

  /**
   * Returns addon class name.
   *
   * @return string $className.
   */
  public function getClassName();

  /**
   * Returns user-friendly title of this addon.
   *
   * @return string $title.
   */
  public function getTitle();

  /**
   * Returns short description of this addon.
   *
   * @return string $description.
   */
  public function getDescription();
}
