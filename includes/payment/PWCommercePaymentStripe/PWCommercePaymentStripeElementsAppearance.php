<?php

namespace ProcessWire;

/*

	PWCommercePaymentStripeElementsAppearance.php
	-----------------------------

	Elements Appearance API
	Customise the look and feel of Elements to match the design of your site.
	Stripe Elements supports extensive visual customisation, allowing you to seamlessly match the design of your site with the appearance option.
	The layout of each Element stays consistent, but you can modify colours, fonts, borders, padding and more.
	@see : https://stripe.com/docs/stripe-js/appearance-api for possible values and more details.

	1. Start by picking a theme
	Quickly get running by picking the pre-built theme that most closely resembles your website.

	Currently, the following themes are available:
		stripe
		night
		flat
		none

	2. Customise the theme using variables
	Set variables like fontFamily and colorPrimary to broadly customise components appearing throughout each Element.

	3. If needed, fine-tune individual components and states using rules

	=============

	Want to customize this Stripe Elements template? Please do not edit directly!
	Just copy this file into /site/templates/pwcommerce/frontend/frontend/frontend/frontend/frontend/PWCommercePaymentStripeElementsAppearance.php to modify.
	@note: you don't need to modify all the values.
	@NOTE: EMPTY VALUES WILL BE DISCARDED.

*/

$stripeElementsAppearance = [
	//
	'theme' => '',
	'variables' => [
		// ****  COMMONLY USED VARIABLES ****
		// The font family used throughout Elements. Custom fonts are supported by passing the fonts option to the Elements group. E.g. 'Ideal Sans, system-ui, sans-serif'
		"fontFamily" => "",
		// The font size that will be set on the root of the Element. By default, other font size variables such as fontSizeXs or fontSizeSm will be scaled from this value using rem units.
		"fontSizeBase" => "",
		// The base spacing unit that all other spacing is derived from. Increase or decrease this value to make your layout more or less spacious.
		"spacingUnit" => "",
		// The border radius used for tabs, inputs, and other components in the Element.
		"borderRadius" => "",
		// A primary colour used throughout the Element. Set this to your primary brand colour. E.g. '#0570de'
		"colorPrimary" => "",
		// The colour used for the background of inputs, tabs, and other components in the Element.
		"colorBackground" => "",
		// The default text colour used in the Element.
		"colorText" => "",
		// A colour used to indicate errors or destructive actions in the Element.
		"colorDanger" => "",
		// **** LESS COMMONLY USED VARIABLES ****
		// What text antialiasing settings to use in the Element. Can either be always, auto, or never.
		"fontSmooth" => '',
		// The font-variant-ligatures setting of text in the Element.
		"fontVariantLigatures" => '',
		// The font-variation-settings setting of text in the Element.
		"fontVariationSettings" => '',
		// The font weight used for light text.
		"fontWeightLight" => '',
		// The font weight used for normal text.
		"fontWeightNormal" => '',
		// The font weight used for medium text.
		"fontWeightMedium" => '',
		// The font weight used for bold text.
		"fontWeightBold" => '',
		// The line-height setting of text in the Element.
		"fontLineHeight" => '',
		// The font size of extra-large text in the Element. By default this is scaled from var(--fontSizeBase) using rem units.
		"fontSizeXl" => '',
		// The font size of large text in the Element. By default this is scaled from var(--fontSizeBase) using rem units.
		"fontSizeLg" => '',
		// The font size of small text in the Element. By default this is scaled from var(--fontSizeBase) using rem units.
		"fontSizeSm" => '',
		// The font size of extra-small text in the Element. By default this is scaled from var(--fontSizeBase) using rem units.
		"fontSizeXs" => '',
		// The font size of double-extra small text in the Element. By default this is scaled from var(--fontSizeBase) using rem units.
		"fontSize2Xs" => '',
		// The font size of triple-extra small text in the Element. By default this is scaled from var(--fontSizeBase) using rem units.
		"fontSize3Xs" => '',
		// A preference for which logo variations to display; either light or dark.
		"colorLogo" => '',
		// The logo variation to display inside .Tab components; either light or dark.
		"colorLogoTab" => '',
		// The logo variation to display inside the .Tab--selected component; either light or dark.
		"colorLogoTabSelected" => '',
		// The logo variation to display inside .Block components; either light or dark.
		"colorLogoBlock" => '',
		// A colour used to indicate positive actions or successful results in the Element.
		"colorSuccess" => '',
		// A colour used to indicate potentially destructive actions in the Element.
		"colorWarning" => '',
		// The colour of text appearing on top of any a var(--colorPrimary) background.
		"colorPrimaryText" => '',
		// The colour of text appearing on top of any a var(--colorBackground) background.
		"colorBackgroundText" => '',
		// The colour of text appearing on top of any a var(--colorSuccess) background.
		"colorSuccessText" => '',
		// The colour of text appearing on top of any a var(--colorDanger) background.
		"colorDangerText" => '',
		// The colour of text appearing on top of any a var(--colorWarning) background.
		"colorWarningText" => '',
		// The colour used for text of secondary importance. For example, this colour is used for the label of a tab that is not currently selected.
		"colorTextSecondary" => '',
		// The colour used for input placeholder text in the Element.
		"colorTextPlaceholder" => '',
		// The colour of text appearing in any legal terms displayed in the Element.
		"colorTextTerms" => '',
		// The spacing between tabs in the Element (for example, 4px).
		"spacingTab" => '',
		// The colour used for icons in the Element, such as the icon appearing in the card tab.
		"colorIcon" => '',
		// The colour of icons when hovered.
		"colorIconHover" => '',
		// The colour of the card icon when it is in an error state.
		"colorIconCardError" => '',
		// The colour of the CVC variant of the card icon.
		"colorIconCardCvc" => '',
		// The colour of the CVC variant of the card icon when the CVC field has invalid input.
		"colorIconCardCvcError" => '',
		// The colour of the redirect icon that appears for redirect-based payment methods.
		"colorIconRedirect" => '',
		// The colour of the arrow appearing over select inputs.
		"colorIconSelectArrow" => '',
		// The colour of icons appearing in a tab.
		"colorIconTab" => '',
		// The colour of icons appearing in a tab when the tab is hovered.
		"colorIconTabHover" => '',
		// The colour of icons appearing in a tab when the tab is selected.
		"colorIconTabSelected" => '',
		// The colour of the icon that appears in the trigger for the additional payment methods menu.
		"colorIconTabMore" => '',
		// The colour of the icon that appears in the trigger for the additional payment methods menu when the trigger is hovered.
		"colorIconTabMoreHover" => '',
		// A preference for which logo should be rendered inside of blocks; either light or dark.
		"colorLogoBlock" => '',
		// A preference for which logo should be rendered inside of tabs; either light or dark.
		"colorLogoTab" => '',
		// A preference for which logo should be rendered inside of selected tabs; either light or dark.
		"colorLogoTabSelected" => '',
		// The spacing between rows in the grid used for the Element layout.
		"spacingGridRow" => '',
		// The spacing between columns in the grid used for the Element layout.
		"spacingGridColumn" => '',
		// The horizontal spacing between .Tab components.
		"spacingTab" => '',

	]
];