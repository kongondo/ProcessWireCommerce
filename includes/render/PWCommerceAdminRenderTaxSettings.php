<?php

namespace ProcessWire;

/**
 * PWCommerce: Admin Render Tax Settings
 *
 * Class to render content for PWCommerce Admin Module executeTaxSettings().
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceAdminRenderTaxSettings for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

class PWCommerceAdminRenderTaxSettings extends WireData
{


	protected function renderResults($selector = null) {

		$pwcommerce = $this->pwcommerce;

		$wrapper = $pwcommerce->getInputfieldWrapper();
		//-------------

		// shop's list prices include taxes
		$pricesIncludeTaxes = $pwcommerce->isPricesIncludeTaxes();
		// -----------------
		// charge EU digital goods vat taxes
		$shopCountryTaxRate = $pwcommerce->getShopCountryTaxRate();
		// ----------------------------
		// charge taxes on shipping rates
		$chargeTaxesOnShippingRates = $pwcommerce->isShopChargeTaxesOnShippingRates();
		// -----------------
		// charge EU digital goods vat taxes
		$chargeEUDigitalGoodsVATTaxes = $pwcommerce->isShopChargingEUDigitalGoodsVATTaxes();

		//------------------- prices include taxes (getInputfieldCheckbox)

		// checkbox description
		$description = $this->_("If selected all taxes will be calculated using the formula below.");

		$options = [
			'id' => "pwcommerce_tax_settings_prices_include_taxes",
			'name' => "pwcommerce_tax_settings_prices_include_taxes",
			'label' => $this->_('Prices Include Taxes'),
			'label2' => $this->_('All taxes are included in stated prices'),
			// @note: will not work since our checkbox is rendered outside an inputfield
			'description' => $description,
			'collapsed' => Inputfield::collapsedNever,
			'classes' => 'pwcommerce_checkbox_outside_inputfield',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'checked' => empty($pricesIncludeTaxes) ? false : true,
		];

		$field = $pwcommerce->getInputfieldCheckbox($options);
		$wrapper->add($field);

		//------------------- prices include taxes formula info (getInputfieldMarkup)
		// TODO CURRENCY SYMBOLS HERE DYNAMIC? OR REMOVE?
		// TODO ALPINE IT!!! IT SHOULD READ FROM THE STATED RATE? IF CHECKBOX CHECKED AS WELL!
		$taxesFormulaInfo =
			"<strong class='mb-5 block'>tax = (tax rate * price) / (1 + tax rate)</strong>" .
			"<p>" . $this->_('For example: £1.00 at 20% tax will be £0.17 (rounded)') . "</p>";

		$options = [
			'skipLabel' => Inputfield::skipLabelHeader,
			'collapsed' => Inputfield::collapsedNever,
			'wrapClass' => true,
			'classes' => 'pwcommerce_note_add_new',
			'wrapper_classes' => 'pwcommerce_no_outline',
			'value' => $taxesFormulaInfo,
		];
		$field = $pwcommerce->getInputfieldMarkup($options);
		$wrapper->add($field);

		// ---------------------

		//------------------- shop/home country standard/base tax (for use to calculate tax portion when prices are inclusive of tax) (getInputfieldCheckbox)
		// TODO TESTING ENTER SHOP COUNTRY TAX!
		$options = [
			'id' => 'pwcommerce_tax_settings_shop_country_tax_rate',
			'name' => 'pwcommerce_tax_settings_shop_country_tax_rate',
			'type' => 'number',
			// 'label' => $this->_('Shop Country Tax Rate'), // TODO 'home country'?
			'label' => $this->_('Home Country Standard Tax Rate'), // TODO 'shop country'?
			'min' => 0,
			'step' => 0.1,
			'collapsed' => Inputfield::collapsedNever,
			'size' => 30, // TODO OK? SMALL SCREENS? USE TW?
			'description' => $this->_("You have stated that your products already have taxes included. Therefore, you will need to specify here your shop/home country's standard tax rate. This will be used to work out the tax and product portions of the list price. If you sell to a region or country with a different tax rate, their tax rate will be applied to the product portion of your list price."),
			'notes' => $this->_("Only the standard/base rate should be specified here. Tax reductions, overrides or territorial/regional taxes can be specified when editing a country's tax rates."),
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'show_if' => "pwcommerce_tax_settings_prices_include_taxes=1",
			// TODO HANDLE SAVE THIS
			'value' => $shopCountryTaxRate,
			'required' => true,
		];

		$field = $pwcommerce->getInputfieldText($options);
		$field->set('requiredIf', "pwcommerce_tax_settings_prices_include_taxes=1");
		$wrapper->add($field);

		//------------------- charge taxes on shipping  (getInputfieldCheckbox)

		$options = [
			'id' => "pwcommerce_tax_settings_charge_taxes_on_shipping_rates",
			'name' => "pwcommerce_tax_settings_charge_taxes_on_shipping_rates",
			'label' => 'Shipping Rates Taxes', // @note: empty string just to hide label but keeping label2
			'label2' => $this->_('Charge taxes on shipping rates'),
			'collapsed' => Inputfield::collapsedNever,
			// 'classes' => 'pwcommerce_checkbox_outside_inputfield',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'checked' => empty($chargeTaxesOnShippingRates) ? false : true,
		];

		$field = $pwcommerce->getInputfieldCheckbox($options);
		$wrapper->add($field);

		// TODO PREPEND <hr>???

		//-------------------  charge eu digital goods vat taxes  (getInputfieldCheckbox)

		// add own notes for checkbox since our checkbox is rendered outside an
		// TODO: NEED TO ADD THIS CATEGORY!!!! OR WORK IT OUT SOMEHOW BASED ON PRODUCT TYPE AND CUSTOMER SHIPPING ADDRESS
		$notes = $this->_("If selected, products in the Digital Goods VAT Tax categories will have VAT applied on Checkout.");

		$options = [
			'id' => "pwcommerce_tax_settings_charge_eu_digital_goods_vat_taxes",
			'name' => "pwcommerce_tax_settings_charge_eu_digital_goods_vat_taxes",
			'label' => $this->_('Digital Goods Taxes'),
			'label2' => $this->_('Charge EU Digital Goods VAT Taxes'),
			'collapsed' => Inputfield::collapsedNever,
			'notes' => $notes,
			//'classes' => 'pwcommerce_checkbox_outside_inputfield',
			'wrapClass' => true,
			'wrapper_classes' => 'pwcommerce_no_outline',
			'checked' => empty($chargeEUDigitalGoodsVATTaxes) ? false : true,
		];

		$field = $pwcommerce->getInputfieldCheckbox($options);
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
		$field = $pwcommerce->getInputfieldHidden($options);
		$wrapper->add($field);

		//------------------- save button (getInputfieldButton)
		// @note: not needed here. It is added in  ProcessPwCommerce::pagesHandler so that it is output 'below' the InputfieldWrapper, similar to processwire pages

		//-------
		return $wrapper->render();
	}
}
