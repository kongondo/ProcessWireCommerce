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
   * @return mixed
   */
  public function getType();

  /**
   * Returns addon class name.
   *
   * @return mixed
   */
  public function getClassName();

  /**
   * Returns user-friendly title of this addon.
   *
   * @return mixed
   */
  public function getTitle();

  /**
   * Returns short description of this addon.
   *
   * @return mixed
   */
  public function getDescription();
}
