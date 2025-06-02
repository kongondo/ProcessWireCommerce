<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Constants: Trait class for PWCommerce Constants.
 *
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerce Class for PWCommerce
 * Copyright (C) 2024 by Francis Otieno
 * MIT License
 *
 */

trait TraitPWCommerceConstants {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CONSTANTS  ~~~~~~~~~~~~~~~~~~

	/**
	 * Constants used in various PWCommerce classes.
	 * We save them in one TRAIT FILE for ease of maintenance
	 * Arranged alphabetically.
	 *
	 */

	const ATTRIBUTE_OPTION_TEMPLATE_NAME = 'pwcommerce-attribute-option';
	const ATTRIBUTE_TEMPLATE_NAME = 'pwcommerce-attribute';
	const ATTRIBUTES_TEMPLATE_NAME = 'pwcommerce-attributes';
	const BRAND_TEMPLATE_NAME = 'pwcommerce-brand';
	const CATEGORY_TEMPLATE_NAME = 'pwcommerce-category';
	const CHECK_ACCESS_ZERO = 'check_access=0';
	const CHILD_PAGE_NAME = 'pwcommerce'; // for 'shop' single child page with template 'pwcommerce'
	const COUNTRY_TEMPLATE_NAME = 'pwcommerce-country';
	const CUSTOMER_ADDRESSES_FIELD_NAME = 'pwcommerce_customer_addresses';
	const CUSTOMER_BILLING_PRIMARY_ADDRESS = 'billing_primary';
	const CUSTOMER_FIELD_NAME = 'pwcommerce_customer';
	const CUSTOMER_GROUP_DESCRIPTION_FIELD_NAME = 'pwcommerce_description';
	const CUSTOMER_GROUP_TEMPLATE_NAME = 'pwcommerce-customer-group';
	const CUSTOMER_GROUPS_FIELD_NAME = 'pwcommerce_customer_groups'; // @note: page ref field
	const CUSTOMER_GROUPS_TEMPLATE_NAME = 'pwcommerce-customer-groups';
	const CUSTOMER_SHIPPING_PRIMARY_ADDRESS = 'shipping_primary';
	const CUSTOMER_TEMPLATE_NAME = 'pwcommerce-customer';
	const CUSTOMERS_TEMPLATE_NAME = 'pwcommerce-customers';
	const CUSTOMER_ROLE_NAME = 'pwcommerce-customer';
	const DESCRIPTION_FIELD_NAME = 'pwcommerce_description';
	const DIMENSION_TEMPLATE_NAME = 'pwcommerce-dimension';
	const DISCOUNT_APPLIES_TO_ALL_COUNTRIES = 'shipping_all_countries';
	const DISCOUNT_APPLIES_TO_FIELD_NAME = 'pwcommerce_discounts_apply_to';
	const DISCOUNT_APPLIES_TO_SELECTED_COUNTRIES = 'shipping_selected_countries';
	const DISCOUNT_BOGO_CATEGORIES_BUY_X = 'categories_buy_x';
	const DISCOUNT_BOGO_CATEGORIES_GET_Y = 'categories_get_y';
	const DISCOUNT_BOGO_PRODUCTS_BUY_X = 'products_buy_x';
	const DISCOUNT_BOGO_PRODUCTS_GET_Y = 'products_get_y';
	const DISCOUNT_ELIGIBILITY_CUSTOMER_GROUPS = 'customer_groups';
	const DISCOUNT_ELIGIBILITY_FIELD_NAME = 'pwcommerce_discounts_eligibility';
	const DISCOUNT_ELIGIBILITY_SPECIFIC_CUSTOMERS = 'specific_customers';
	const DISCOUNT_FIELD_NAME = 'pwcommerce_discount';
	const DISCOUNT_REDEEMED_DISCOUNTS = 'redeemedDiscounts';
	const DISCOUNT_REDEEMED_DISCOUNTS_IDS = 'redeemedDiscountsIDs';
	const DISCOUNT_TEMPLATE_NAME = 'pwcommerce-discount';
	const DOWNLOAD_FILE_FIELD_NAME = 'pwcommerce_file';
	const DOWNLOAD_SETTINGS_FIELD_NAME = 'pwcommerce_download_settings';
	const DOWNLOAD_TEMPLATE_NAME = 'pwcommerce-download';
	const FIND_ANYTHING_TEMPLATES_CACHE_NAME = 'pwcommerce_find_anything_templates';
	const FULFILMENT_STATUS_AWAITING_FULFILMENT = 5001;
	const FULFILMENT_STATUS_FULFILLED = 6004; // @note: same as 'delivered'
	const FULFILMENT_STATUS_MAXIMUM_FLAG = 6999;
	const FULFILMENT_STATUS_MIMINUM_FLAG = 5000;
	const FULFILMENT_STATUS_VOID_FULFILMENT = 5000;
	const GIFT_CARD = 'pwcommerce_gift_card';
	const GIFT_CARD_ACTIVITIES_FIELD_NAME = 'pwcommerce_gift_card_activities';
	const GIFT_CARD_CODE_DIGITS = 16;
	const GIFT_CARD_FIELD_NAME = 'pwcommerce_gift_card';
	const GIFT_CARD_PRODUCT_TEMPLATE_NAME = 'pwcommerce-gift-card-product';
	const GIFT_CARD_PRODUCT_VARIANT_TEMPLATE_NAME = 'pwcommerce-gift-card-product-variant';
	const GIFT_CARD_TEMPLATE_NAME = 'pwcommerce-gift-card';
	const GIFT_CARDS_TEMPLATE_NAME = 'pwcommerce-gift-cards';
	const HUNDRED = 100;
	const LEGAL_PAGE_TEMPLATE_NAME = 'pwcommerce-legal-page';
	const ORDER_CUSTOMER_FIELD_NAME = 'pwcommerce_order_customer';
	const ORDER_DISCOUNTS_FIELD_NAME = 'pwcommerce_order_discounts';
	const ORDER_FIELD_NAME = 'pwcommerce_order';
	const ORDER_LINE_ITEM_FIELD_NAME = 'pwcommerce_order_line_item';
	const ORDER_LINE_ITEM_PLACEHOLDER_DELIVERED_DATE = '2016-04-08 05:10:00';
	const ORDER_LINE_ITEMS_TEMPLATE_NAME = 'pwcommerce-order-line-item';
	const ORDER_NOTES_FIELD_NAME = 'pwcommerce_notes';
	const ORDER_PARENT_TEMPLATE_NAME = 'pwcommerce-orders';
	const ORDER_PLACEHOLDER_PAID_DATE = '2016-04-08 05:10:00';
	const ORDER_LOST_SESSION_ORDER_ID_NAME = 'lostSessionOrderID';
	const PROCESS_RENDER_ORDERS_PARTIAL_TEMPLATE_NAME = 'pwcommerce-process-render-orders.php';
	const PROCESS_RENDER_SINGLE_ORDER_VIEW_PARTIAL_TEMPLATE_NAME = 'pwcommerce-process-render-single-order-view.php';
	const ORDER_STATUS_ABANDONED = 1001;
	const ORDER_STATUS_CANCELLED = 2000;
	const ORDER_STATUS_COMPLETED = 2999;
	const ORDER_STATUS_DRAFT = 1000; // @note: same as 'pending'
	const ORDER_STATUS_MAXIMUM_FLAG = 2999;
	const ORDER_STATUS_MIMINUM_FLAG = 1000;
	const ORDER_STATUS_OPEN = 1002; // @note: same as 'pending'
	const ORDER_TEMPLATE_NAME = 'pwcommerce-order';
	const ORDER_CACHE_NAME_PREFIX = 'pwcommerce_order_cache';
	const PWCOMMERCE_BACKEND_TEMPLATES_PATH = "pwcommerce/backend/";
	const PWCOMMERCE_CART_TABLE_NAME = 'pwcommerce_cart';
	const PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_PAGE_TREE_MANAGEMENT_SETTING_NAME = 'pwcommerce_custom_shop_root_page_children_page_tree_management';
	const PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_CHILDREN_SETTING_NAME = 'pwcommerce_custom_shop_root_page_children';
	const PWCOMMERCE_CUSTOM_SHOP_ROOT_PAGE_ID_SETTING_NAME = 'pwcommerce_custom_shop_root_page_id';
	const PWCOMMERCE_DISCOUNT_NEARLY_USED_UP_THRESHOLD = 10;
	const PWCOMMERCE_DOWNLOAD_CODES = 'pwcommerce_download_codes';
	const PWCOMMERCE_DOWNLOAD_CODES_TABLE_NAME = 'pwcommerce_download_codes';
	const PWCOMMERCE_FIRST_STAGE_INSTALL_CONFIGURATION_STATUS = 'first_stage_install';
	const PWCOMMERCE_GIFT_CARD_PRODUCT_VARIANT_PARENT_PAGE_ID_INPUT = 'pwcommerce_gift_card_product_variant_parent_page_id';
	const PWCOMMERCE_GIFT_CARD_PRODUCT_VARIANT_PARENT_PAGE_TITLE_INPUT = 'pwcommerce_gift_card_product_variant_parent_page_title';
	const PWCOMMERCE_HIGH_DISCOUNT_USAGE_THRESHOLD = 10;
	const PWCOMMERCE_MOST_SALES_THRESHOLD = 10;
	const PWCOMMERCE_IS_CATEGORY_A_COLLECTION_SETTING_NAME = 'pwcommerce_is_category_collection';
	const PWCOMMERCE_IS_USE_CUSTOM_SHOP_ROOT_PAGE_SETTING_NAME = 'pwcommerce_is_use_custom_shop_root_page';
	const PWCOMMERCE_LISTER_SELECTOR_STRING_COOKIE_NAME_PREFIX = 'pwcommerce_lister_selector_string_cookie';
	const PWCOMMERCE_LOW_DISCOUNT_USAGE_THRESHOLD = 10;
	const PWCOMMERCE_LEAST_SALES_THRESHOLD = 10;
	const PWCOMMERCE_LOW_STOCK_THRESHOLD = 5;
	const PWCOMMERCE_ORDER_CART_TABLE_NAME = 'pwcommerce_cart';
	const PWCOMMERCE_ORDER_STATUS_TABLE = 'pwcommerce_order_status';
	const PWCOMMERCE_ORDER_STATUS_TABLE_NAME = 'pwcommerce_order_status';
	const PWCOMMERCE_PAGINATION_LIMIT_COOKIE_NAME_PREFIX = 'pwcommerce_pagination_limit_cookie';
	const PWCOMMERCE_PAGINATION_NUMBER_COOKIE_NAME_PREFIX = 'pwcommerce_pagination_number_cookie';
	const PWCOMMERCE_PROCESS_MODULE = 'ProcessPWCommerce';
	const PWCOMMERCE_SECOND_STAGE_INSTALL_CONFIGURATION_STATUS = 'second_stage_install';
	const PWCOMMERCE_SHOP_PAGE_IN_ADMIN_NAME = 'shop';
	const PWCOMMERCE_TEMPLATE_NAME = 'pwcommerce';
	const PAGE_NAME = 'shop'; // for this process module's 'admin' page
	const PAYMENT_PROVIDER_TEMPLATE_NAME = 'pwcommerce-payment-provider';
	const PAYMENT_PROVIDERS_TEMPLATE_NAME = 'pwcommerce-payment-providers';
	const PAYMENT_STATUS_AWAITING_PAYMENT = 3000;
	const PAYMENT_STATUS_MAXIMUM_FLAG = 4999;
	const PAYMENT_STATUS_MIMINUM_FLAG = 3000;
	const PAYMENT_STATUS_PAID = 4000;
	const PAYMENT_STATUS_PARTIALLY_PAID = 3999;
	const PAYMENT_STATUS_PARTIALLY_REFUNDED = 4998;
	const PAYMENT_STATUS_REFUNDED = 4999;
	const PROCESS_RENDER_SHOP_HOME_PARTIAL_TEMPLATE_NAME = 'pwcommerce-process-render-shop-home.php';
	const PRODUCT_ATTRIBUTES_FIELD_NAME = 'pwcommerce_product_attributes';
	const PRODUCT_ATTRIBUTES_OPTIONS_FIELD_NAME = 'pwcommerce_product_attributes_options';
	const PRODUCT_BRAND_FIELD_NAME = 'pwcommerce_brand';
	const PRODUCT_CATEGORIES_FIELD_NAME = 'pwcommerce_categories';
	const PRODUCT_DOWNLOADS_FIELD_NAME = 'pwcommerce_downloads';
	const PRODUCT_PROPERTIES_FIELD_NAME = 'pwcommerce_product_properties';
	const PRODUCT_SETTINGS_FIELD_NAME = 'pwcommerce_product_settings';
	const PRODUCT_STOCK_FIELD_NAME = 'pwcommerce_product_stock';
	const PRODUCT_TAGS_FIELD_NAME = 'pwcommerce_tags';
	const PRODUCT_TEMPLATE_NAME = 'pwcommerce-product';
	const PRODUCT_TYPE_FIELD_NAME = 'pwcommerce_type';
	const PRODUCT_VARIANT_TEMPLATE_NAME = 'pwcommerce-product-variant';
	const PROPERTY_TEMPLATE_NAME = 'pwcommerce-property';
	const REPEATER_SUFFIX = '_repeater';
	const ROUND_PRECISION = 2;
	const SETTINGS_FIELD_NAME = 'pwcommerce_settings';
	const SETTINGS_TEMPLATE_NAME = 'pwcommerce-settings';
	const SHIPPING_FEE_SETTINGS_FIELD_NAME = 'pwcommerce_shipping_fee_settings';
	const SHIPPING_RATE_FIELD_NAME = 'pwcommerce_shipping_rate';
	const SHIPPING_RATE_TEMPLATE_NAME = 'pwcommerce-shipping-rate';
	const SHIPPING_ZONE_COUNTRIES_FIELD_NAME = 'pwcommerce_shipping_zone_countries';
	const SHIPPING_ZONE_TEMPLATE_NAME = 'pwcommerce-shipping-zone';
	const STATUS_MAXIMUM_FLAG = 9999;
	const STATUS_MIMINUM_FLAG = 1000;
	const TAG_TEMPLATE_NAME = 'pwcommerce-tag';
	const TAX_OVERRIDES_FIELD_NAME = 'pwcommerce_tax_overrides';
	const TAX_RATES_FIELD_NAME = 'pwcommerce_tax_rates';
	const TYPE_TEMPLATE_NAME = 'pwcommerce-type';
	const MYSQL_DATE_FUNCTIONS_DEFAULT_INTERVAL = 1;
	const MYSQL_DATE_FUNCTIONS_DEFAULT_INTERVAL_TYPE = 'DAY';
}
