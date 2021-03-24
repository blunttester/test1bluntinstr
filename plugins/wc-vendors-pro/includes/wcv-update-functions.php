<?php
/**
 * WC Vendors Pro Updates
 *
 * Functions for updating data, used by the background updater.
 *
 * @package WCVendors Pro/Functions
 * @version 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map WC Vendors Pro settings to WC Vendors version two settings
 *
 * @since 1.5.0
 */
add_filter( 'wcvendors_settings_mappings', 'wcvendors_pro_get_settings_mapping' );
function wcvendors_pro_get_settings_mapping( $settings ) {
	$wcvendors_pro_settings_mappings = wcvendors_pro_settings_mapping();

	return array_merge( $settings, $wcvendors_pro_settings_mappings );
}

function wcvendors_pro_settings_mapping() {

	return $wcvendors_pro_settings_mappings = array(
		'dashboard_page_id'                    => 'wcvendors_dashboard_page_id',
		'vendor_store_header_type'             => 'wcvendors_vendor_store_header_type',
		'store_shop_headers'                   => 'wcvendors_store_shop_headers',
		'store_single_headers'                 => 'wcvendors_store_single_headers',
		'disable_wp_admin_vendors'             => 'wcvendors_disable_wp_admin_vendors',
		'vendor_dashboard_notice'              => 'wcvendors_vendor_dashboard_notice',
		'allow_form_markup'                    => 'wcvendors_allow_form_markup',
		'single_product_tools'                 => 'wcvendors_single_product_tools',
		'product_management_cap'               => 'wcvendors_product_management_cap',
		'order_management_cap'                 => 'wcvendors_order_management_cap',
		'shop_coupon_management_cap'           => 'wcvendors_shop_coupon_management_cap',
		'settings_management_cap'              => 'wcvendors_settings_management_cap',
		'ratings_management_cap'               => 'wcvendors_ratings_management_cap',
		'shipping_management_cap'              => 'wcvendors_shipping_management_cap',
		'view_store_cap'                       => 'wcvendors_view_store_cap',
		'delete_product_cap'                   => 'wcvendors_capability_product_delete',
		'duplicate_product_cap'                => 'wcvendors_capability_product_duplicate',
		'can_edit_approved_products'           => 'wcvendors_capability_products_approved',
		'dashboard_date_range'                 => 'wcvendors_dashboard_date_range',
		'orders_sales_range'                   => 'wcvendors_orders_sales_range',
		'products_per_page'                    => 'wcvendors_products_per_page',
		'coupons_per_page'                     => 'wcvendors_coupons_per_page',
		'hide_order_customer_name'             => 'wcvendors_capability_order_customer_name',
		'hide_order_customer_shipping_address' => 'wcvendors_capability_order_customer_shipping',
		'hide_order_customer_billing_address'  => 'wcvendors_capability_order_customer_billing',
		'hide_order_customer_phone'            => 'wcvendors_capability_order_customer_phone',
		'hide_order_view_details'              => 'wcvendors_hide_order_view_details',
		'hide_order_shipping_label'            => 'wcvendors_hide_order_shipping_label',
		'hide_order_tracking_number'           => 'wcvendors_hide_order_tracking_number',
		'hide_order_mark_shipped'              => 'wcvendors_hide_order_mark_shipped',
		'vendor_product_trash'                 => 'wcvendors_vendor_product_trash',
		'vendor_coupon_trash'                  => 'wcvendors_vendor_coupon_trash',
		'default_store_banner_src'             => 'wcvendors_default_store_banner_src',
		'verified_vendor_label'                => 'wcvendors_verified_vendor_label',
		'feedback_page_id'                     => 'wcvendors_feedback_page_id',
		'vendor_ratings_label'                 => 'wcvendors_vendor_ratings_label',
		'feedback_system'                      => 'wcvendors_feedback_system',
		'feedback_display'                     => 'wcvendors_feedback_display',
		'feedback_sort_order'                  => 'wcvendors_feedback_sort_order',
		'feedback_order_status'                => 'wcvendors_feedback_order_status',
		'commission_coupon_action'             => 'wcvendors_commission_coupon_action',
		'commission_type'                      => 'wcvendors_commission_type',
		'commission_percent'                   => 'wcvendors_vendor_commission_rate',
		'commission_amount'                    => 'wcvendors_commission_amount',
		'commission_fee'                       => 'wcvendors_commission_fee',
		'product_form_template'                => 'wcvendors_product_form_template',
		'hide_product_basic'                   => 'wcvendors_hide_product_basic_{field}',
		'hide_product_media'                   => 'wcvendors_hide_product_media_{field}',
		'hide_product_general'                 => 'wcvendors_hide_product_general_{field}',
		'hide_product_inventory'               => 'wcvendors_hide_product_inventory_{field}',
		'hide_product_shipping'                => 'wcvendors_hide_product_shipping_{field}',
		'hide_product_upsells'                 => 'wcvendors_hide_product_upsells_{field}',
		'hide_product_variations'              => 'wcvendors_hide_product_variations_{field}',
		'required_product_basic'               => 'wcvendors_required_product_basic_{field}',
		'required_product_media'               => 'wcvendors_required_product_media_{field}',
		'required_product_general'             => 'wcvendors_required_product_general_{field}',
		'required_product_inventory'           => 'wcvendors_required_product_inventory_{field}',
		'required_product_shipping'            => 'wcvendors_required_product_shipping_{field}',
		'required_product_upsells'             => 'wcvendors_required_product_upsells_{field}',
		'save_product_redirect'                => 'wcvendors_save_product_redirect',
		'product_form_cap'                     => 'wcvendors_product_form_cap',
		'category_display'                     => 'wcvendors_category_display',
		'hide_categories_list'                 => 'wcvendors_hide_categories_list',
		'category_limit'                       => 'wcvendors_category_limit',
		'tag_display'                          => 'wcvendors_tag_display',
		'tag_separator'                        => 'wcvendors_tag_separator',
		'file_display'                         => 'wcvendors_file_display',
		'hide_attributes_list'                 => 'wcvendors_hide_attributes_list',
		'vendor_image_prefix'                  => 'wcvendors_vendor_image_prefix',
		'product_max_gallery_count'            => 'wcvendors_product_max_gallery_count',
		'product_max_image_width'              => 'wcvendors_product_max_image_width',
		'product_max_image_height'             => 'wcvendors_product_max_image_height',
		'product_min_image_width'              => 'wcvendors_product_min_image_width',
		'product_min_image_height'             => 'wcvendors_product_min_image_height',
		'hide_settings_general'                => 'wcvendors_hide_settings_general',
		'hide_settings_store'                  => 'wcvendors_hide_settings_store',
		'hide_settings_payment'                => 'wcvendors_hide_settings_payment',
		'hide_settings_branding'               => 'wcvendors_hide_settings_branding',
		'hide_settings_shipping'               => 'wcvendors_hide_settings_shipping',
		'hide_settings_social'                 => 'wcvendors_hide_settings_social',
		'hide_signup_general'                  => 'wcvendors_hide_signup_general',
		'hide_signup_store'                    => 'wcvendors_hide_signup_store',
		'hide_signup_payment'                  => 'wcvendors_hide_signup_payment',
		'hide_signup_branding'                 => 'wcvendors_hide_signup_branding',
		'hide_signup_shipping'                 => 'wcvendors_hide_signup_shipping',
		'hide_signup_social'                   => 'wcvendors_hide_signup_social',
		'vendor_signup_notice'                 => 'wcvendors_vendor_signup_notice',
		'vendor_pending_notice'                => 'wcvendors_vendor_pending_notice',
	);

}

/**
 * Migrate Pro to version 1.5.0
 */
function wcv_migrate_pro_settings() {

	$version_one = get_option( 'wc_prd_vendor_options', null );
	$mappings    = wcvendors_pro_settings_mapping();

	if ( is_null( $version_one ) ) {
		return;
	}

	foreach ( $version_one as $setting => $value ) {

		// if ( array_key_exists( $setting, $mappings ) ){
		$value = maybe_unserialize( $value );

		if ( $setting == 'hide_product_basic' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_product_basic_description', $value['description'] );
			update_option( 'wcvendors_hide_product_basic_short_description', $value['short_description'] );
			update_option( 'wcvendors_hide_product_basic_categories', $value['categories'] );
			update_option( 'wcvendors_hide_product_basic_tags', $value['tags'] );
			update_option( 'wcvendors_hide_product_basic_attributes', $value['attributes'] );

		} elseif ( $setting == 'hide_product_media' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_product_media_featured', $value['featured'] );
			update_option( 'wcvendors_hide_product_media_gallery', $value['gallery'] );

		} elseif ( $setting == 'hide_product_general' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_product_general_sku', $value['sku'] );
			update_option( 'wcvendors_hide_product_general_private_listing', $value['private_listing'] );
			update_option( 'wcvendors_hide_product_general_external_url', $value['external_url'] );
			update_option( 'wcvendors_hide_product_general_button_text', $value['button_text'] );
			update_option( 'wcvendors_hide_product_general_price', $value['price'] );
			update_option( 'wcvendors_hide_product_general_sale_price', $value['sale_price'] );
			update_option( 'wcvendors_hide_product_general_tax', $value['tax'] );
			update_option( 'wcvendors_hide_product_general_download_files', $value['download_files'] );
			update_option( 'wcvendors_hide_product_general_download_file_url', $value['download_file_url'] );
			update_option( 'wcvendors_hide_product_general_download_limit', $value['download_limit'] );
			update_option( 'wcvendors_hide_product_general_download_expiry', $value['download_expiry'] );
			update_option( 'wcvendors_hide_product_general_download_type', $value['download_type'] );

		} elseif ( $setting == 'hide_product_inventory' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_product_inventory_manage_inventory', $value['manage_inventory'] );
			update_option( 'wcvendors_hide_product_inventory_stock_qty', $value['stock_qty'] );
			update_option( 'wcvendors_hide_product_inventory_backorders', $value['backorders'] );
			update_option( 'wcvendors_hide_product_inventory_stock_status', $value['stock_status'] );
			update_option( 'wcvendors_hide_product_inventory_sold_individually', $value['sold_individually'] );

		} elseif ( $setting == 'hide_product_shipping' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_product_shipping_weight', $value['weight'] );
			update_option( 'wcvendors_hide_product_shipping_handling_fee', $value['handling_fee'] );
			update_option( 'wcvendors_hide_product_shipping_max_charge', $value['max_charge'] );
			update_option( 'wcvendors_hide_product_shipping_free_shipping_order', $value['free_shipping_order'] );
			update_option( 'wcvendors_hide_product_shipping_free_shipping_product', $value['free_shipping_product'] );
			update_option( 'wcvendors_hide_product_shipping_dimensions', $value['dimensions'] );
			update_option( 'wcvendors_hide_product_shipping_shipping_class', $value['shipping_class'] );

		} elseif ( $setting == 'hide_product_upsells' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_product_upsells_up_sells', $value['up_sells'] );
			update_option( 'wcvendors_hide_product_upsells_crosssells', $value['crosssells'] );
			update_option( 'wcvendors_hide_product_upsells_grouped_products', $value['grouped_products'] );

		} elseif ( $setting == 'hide_product_variations' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_product_variations_featured', $value['featured'] );
			update_option( 'wcvendors_hide_product_variations_sku', $value['sku'] );
			update_option( 'wcvendors_hide_product_variations_enabled', $value['enabled'] );
			update_option( 'wcvendors_hide_product_variations_downloadable', $value['downloadable'] );
			update_option( 'wcvendors_hide_product_variations_virtual', $value['virtual'] );
			update_option( 'wcvendors_hide_product_variations_manage_stock', $value['manage_stock'] );
			update_option( 'wcvendors_hide_product_variations_sale_price', $value['sale_price'] );
			update_option( 'wcvendors_hide_product_variations_stock_qty', $value['stock_qty'] );
			update_option( 'wcvendors_hide_product_variations_allow_backorders', $value['allow_backorders'] );
			update_option( 'wcvendors_hide_product_variations_stock_status', $value['stock_status'] );
			update_option( 'wcvendors_hide_product_variations_weight', $value['weight'] );
			update_option( 'wcvendors_hide_product_variations_dimensions', $value['dimensions'] );
			update_option( 'wcvendors_hide_product_variations_shipping_class', $value['shipping_class'] );
			update_option( 'wcvendors_hide_product_variations_tax_class', $value['tax_class'] );
			update_option( 'wcvendors_hide_product_variations_description', $value['description'] );
			update_option( 'wcvendors_hide_product_variations_download_files', $value['download_files'] );
			update_option( 'wcvendors_hide_product_variations_download_limit', $value['download_limit'] );
			update_option( 'wcvendors_hide_product_variations_download_expiry', $value['download_expiry'] );

		} elseif ( $setting == 'hide_settings_general' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_settings_tab_payment', $value['payment'] );
			update_option( 'wcvendors_hide_settings_tab_branding', $value['branding'] );
			update_option( 'wcvendors_hide_settings_tab_shipping', $value['shipping'] );
			update_option( 'wcvendors_hide_settings_tab_social', $value['social'] );

		} elseif ( $setting == 'hide_settings_store' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_settings_store_description', $value['pv_shop_description'] );
			update_option( 'wcvendors_hide_settings_store_seller_info', $value['pv_seller_info'] );
			update_option( 'wcvendors_hide_settings_store_company_url', $value['_wcv_company_url'] );
			update_option( 'wcvendors_hide_settings_store_phone', $value['_wcv_store_phone'] );
			update_option( 'wcvendors_hide_settings_store_address', $value['store_address'] );
			update_option( 'wcvendors_hide_settings_store_vacation_mode', $value['vacation_mode'] );

		} elseif ( $setting == 'hide_settings_payment' && ! empty( $value ) ) {
			update_option( 'wcvendors_hide_settings_payment_paypal', $value['paypal'] );

		} elseif ( $setting == 'hide_settings_branding' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_settings_branding_store_banner', $value['store_banner'] );
			update_option( 'wcvendors_hide_settings_branding_store_icon', $value['store_icon'] );

		} elseif ( $setting == 'hide_settings_shipping' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_settings_shipping_handling_fee', $value['handling_fee'] );
			update_option( 'wcvendors_hide_settings_shipping_min_charge', $value['min_charge'] );
			update_option( 'wcvendors_hide_settings_shipping_max_charge', $value['max_charge'] );
			update_option( 'wcvendors_hide_settings_shipping_max_charge_product', $value['max_charge_product'] );
			update_option( 'wcvendors_hide_settings_shipping_free_shipping_order', $value['free_shipping_order'] );
			update_option( 'wcvendors_hide_settings_shipping_free_shipping_product', $value['free_shipping_product'] );
			update_option( 'wcvendors_hide_settings_shipping_shipping_policy', $value['shipping_policy'] );
			update_option( 'wcvendors_hide_settings_shipping_return_policy', $value['return_policy'] );

		} elseif ( $setting == 'hide_settings_social' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_settings_social_twitter', $value['twitter'] );
			update_option( 'wcvendors_hide_settings_social_instagram', $value['instagram'] );
			update_option( 'wcvendors_hide_settings_social_facebook', $value['facebook'] );
			update_option( 'wcvendors_hide_settings_social_linkedin', $value['linkedin'] );
			update_option( 'wcvendors_hide_settings_social_youtube', $value['youtube'] );
			update_option( 'wcvendors_hide_settings_social_pinterest', $value['pinterest'] );
			update_option( 'wcvendors_hide_settings_social_snapchat', $value['snapchat'] );

		} elseif ( $setting == 'hide_signup_general' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_signup_tab_payment', $value['payment'] );
			update_option( 'wcvendors_hide_signup_tab_branding', $value['branding'] );
			update_option( 'wcvendors_hide_signup_tab_shipping', $value['shipping'] );
			update_option( 'wcvendors_hide_signup_tab_social', $value['social'] );

		} elseif ( $setting == 'hide_signup_store' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_signup_store_description', $value['pv_shop_description'] );
			update_option( 'wcvendors_hide_signup_store_seller_info', $value['pv_seller_info'] );
			update_option( 'wcvendors_hide_signup_store_company_url', $value['_wcv_company_url'] );
			update_option( 'wcvendors_hide_signup_store_phone', $value['_wcv_store_phone'] );
			update_option( 'wcvendors_hide_signup_store_address', $value['store_address'] );
			update_option( 'wcvendors_hide_signup_store_vacation_mode', $value['vacation_mode'] );

		} elseif ( $setting == 'hide_signup_payment' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_signup_payment_paypal', $value['paypal'] );

		} elseif ( $setting == 'hide_signup_shipping' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_signup_shipping_handling_fee', $value['handling_fee'] );
			update_option( 'wcvendors_hide_signup_shipping_min_charge', $value['min_charge'] );
			update_option( 'wcvendors_hide_signup_shipping_max_charge', $value['max_charge'] );
			update_option( 'wcvendors_hide_signup_shipping_max_charge_product', $value['max_charge_product'] );
			update_option( 'wcvendors_hide_signup_shipping_free_shipping_order', $value['free_shipping_order'] );
			update_option( 'wcvendors_hide_signup_shipping_free_shipping_product', $value['free_shipping_product'] );
			update_option( 'wcvendors_hide_signup_shipping_shipping_policy', $value['shipping_policy'] );
			update_option( 'wcvendors_hide_signup_shipping_return_policy', $value['return_policy'] );

		} elseif ( $setting == 'hide_signup_social' && ! empty( $value ) ) {

			update_option( 'wcvendors_hide_signup_social_twitter', $value['twitter'] );
			update_option( 'wcvendors_hide_signup_social_instagram', $value['instagram'] );
			update_option( 'wcvendors_hide_signup_social_facebook', $value['facebook'] );
			update_option( 'wcvendors_hide_signup_social_linkedin', $value['linkedin'] );
			update_option( 'wcvendors_hide_signup_social_youtube', $value['youtube'] );
			update_option( 'wcvendors_hide_signup_social_pinterest', $value['pinterest'] );
			update_option( 'wcvendors_hide_signup_social_snapchat', $value['snapchat'] );

		} else {

			if ( $value == 1 ) {
				$value = 'yes';
			}

			if ( array_key_exists( $settings, $mappings ) ) {
				update_option( $mappings[ $setting ], $value );
			}
		}
	}

}

/**
 * Finish Settings update
 *
 * @since 1.5.0
 */
function wcv_update_150_db_version() {
	WCVendors_Pro_Activator::update_db_version();
}


/**
 *
 *
 * @since 1.5.3
 */
function wcv_fix_product_form() {
	$product_form = get_option( 'wcvendors_product_form_template', 'standard' );

	if ( 'select' === $product_form ) {
		update_option( 'wcvendors_product_form_template', 'edit' );
	}
}

/**
 * Finish Settings update
 *
 * @since 1.5.3
 */
function wcv_update_153_db_version() {
	WCVendors_Pro_Activator::update_db_version();
}

/**
 *
 *
 * @since 1.5.4
 */
function wcv_fix_product_standard_form() {
	$product_form = get_option( 'wcvendors_product_form_template', 'standard' );
	if ( 'edit' === $product_form ) {
		update_option( 'wcvendors_product_form_template', 'standard' );
	}
}

/**
 * Finish Settings update
 *
 * @since 1.5.4
 */
function wcv_update_154_db_version() {
	WCVendors_Pro_Activator::update_db_version();
}

/**
 * Update inconsistent option name
 *
 * @since 1.5.5
 */
function wcv_update_inconsistent_option_names() {
	$seller_info_required = get_option( 'wcvendors_required_settings_seller_info', 'no' );

	update_option( 'wcvendors_required_settings_store_seller_info', $seller_info_required );
	delete_option( 'wcvendors_required_settings_seller_info' );
}

/**
 * Add default Google Maps options
 *
 * @since   1.5.5
 * @version 1.5.7
 */
function add_defaults_google_maps_options() {
	if ( get_option( 'wcvendors_pro_google_maps_zoom_level', false ) === false ) {
		update_option( 'wcvendors_pro_google_maps_zoom_level', 18 );
	}

	if ( get_option( 'wcvendors_pro_location_picker_default_visibility', false ) === false ) {
		update_option( 'wcvendors_pro_location_picker_default_visibility', 'visible' );
	}
}

/**
 *  Add new options for color filters
 * Add product variation dropdown type option
 *
 * @since 1.5.5
 */
function wcv_add_variation_dropdown_type_option() {
	if ( ! get_option( 'wcvendors_hide_product_variations_download_expiry', 'no' ) ) {
		update_option( 'wcvendors_hide_product_variations_download_expiry', 'single' );
	}
}

/**
 * Add new options for color filters
 *
 * @since 1.5.5
 */
function wcv_add_color_filter_options() {

	update_option( 'wcv_product_totals_chart_use_random_colors', 'yes' );

	update_option( 'wcv_product_totals_chart_base_fill_color', '#005580' );
	update_option( 'wcv_product_totals_chart_base_hover_color', '#5897b6' );

	update_option( 'wcv_order_totals_chart_use_random_colors', 'no' );
	update_option( 'wcv_order_totals_chart_fill_color', '#005580' );
	update_option( 'wcv_order_totals_chart_fill_opacity', 1 );
	update_option( 'wcv_order_totals_chart_stroke_color', '#ffffff' );
	update_option( 'wcv_order_totals_chart_stroke_opacity', '0.5' );
	update_option( 'wcv_order_totals_chart_hover_fill_color', '#5897b6' );
	update_option( 'wcv_order_totals_chart_hover_fill_opacity', '0.5' );
	update_option( 'wcv_order_totals_chart_hover_stroke_color', '#ffffff' );
	update_option( 'wcv_order_totals_chart_fill_color_opacity', '0.5' );
}

/**
 * Add SEO Facebook Image option based on the description option as they were using the same name
 *
 * @since 1.5.5
 */
function update_facebook_image_option() {
	$current_fb_image_option = get_option( 'wcvendors_hide_settings_seo_fb_description', 'no' );
	add_option( 'wcvendors_hide_settings_seo_fb_image', $current_fb_image_option );
}

/**
 * Add default option for disabling vendor cart globally
 *
 * @return void
 * @since 1.5.8
 */
function add_vacation_disable_cart_defaults() {
	$show_total_sales    = get_option( '_wcv_show_product_total_sales' );
	$hide_totals_fields  = get_option( 'wcvendors_hide_settings_store_product_total_sales', false );
	$store_sales_label   = get_option( 'wcvendors_store_total_sales_label', false );
	$product_sales_label = get_option( 'wcvendors_product_total_sales_label', false );
	$override_vendor     = get_option( 'wcvendors_override_vendor_total_sales_label', false );

	if ( false === $show_total_sales ) {
		add_option( '_wcv_show_product_total_sales', 'yes' );
	}

	if ( false === $hide_totals_fields ) {
		add_option( 'wcvendors_hide_settings_store_product_total_sales', 1 );
	}

	if ( false === $store_sales_label ) {
		add_option( 'wcvendors_store_total_sales_label', __( 'Total Sales:', 'wcvendors-pro' ) );
	}

	if ( false === $product_sales_label ) {
		add_option( 'wcvendors_product_total_sales_label', __( 'Units Sold:', 'wcvendors-pro' ) );
	}

	if ( false === $override_vendor ) {
		add_option( 'wcvendors_override_vendor_total_sales_label', 'no' );
	}
}

/**
 * Add settings to show store and product total sales
 *
 * @return    void
 * @since      1.5.9
 * @version    1.5.9
 */
function add_store_total_sales_options() {

	$show_store_totals = get_option( 'wcvendors_show_store_total_sales', false );
	if ( false === $show_store_totals ) {
		add_option( 'wcvendors_show_store_total_sales', 'yes' );
	}

	$show_product_totals = get_option( 'wcvendors_show_product_total_sales', false );
	if ( false === $show_product_totals ) {
		add_option( 'wcvendors_show_product_total_sales', 'yes' );
	}

	delete_option( 'wcvendors_hide_settings_store_product_total_sales' );
	delete_option( 'wcvendors_override_vendor_total_sales_label' );
	delete_option( '_wcv_show_product_total_sales' );
}


/**
 * Add SEO Facebook Image option based on the description option as they were using the same name
 *
 * @since 1.5.5
 */
function wcv_add_enable_media_option() {
	$enable_media = get_option( 'wcvendors_allow_editor_media', 'no' );
	add_option( 'wcvendors_allow_editor_media', $enable_media );
}


/**
 * Add option to hide/show vendor store notice field
 *
 * @return void
 * @since    1.5.9
 * @version  1.5.9
 */
function add_hide_vendor_store_notice_option() {
	if ( false === get_option( 'wcvendors_hide_settings_vendor_store_notice', false ) ) {
		add_option( 'wcvendors_hide_settings_vendor_store_notice', 'no' );
	}

	if ( false === get_option( 'wcvendors_hide_settings_store_store_enable_notice', false ) ) {
		add_option( 'wcvendors_hide_settings_store_enable_notice', 'no' );
	}
}

/**
 * Add option to hide/show vendor shipping type field
 *
 * @return  void
 * @since   1.6.0
 * @version 1.6.0
 */
function add_options_for_vendor_shipping_type() {
	if ( false === get_option( 'wcvendors_hide_settings_store_shipping_type', false ) ) {
		add_option( 'wcvendors_hide_settings_store_shipping_type', 'yes' );
		add_option( 'wcvendors_required_settings_store_shipping_type', 'no' );
	}

	if ( false === get_option( 'wcvendors_hide_signup_store_shipping_type', false ) ) {
		add_option( 'wcvendors_hide_signup_store_shipping_type', 'yes' );
		add_option( 'wcvendors_required_signup_store_shipping_type', 'no' );
	}
}

/**
 * Add option to sync reviews with woocommerce reviews
 *
 * @return  void
 * @since   1.6.0
 * @version 1.6.0
 */
function add_sync_reviews_option() {
	if ( false == get_option( 'wcvendors_feedback_sync_reviews', false ) ) {
		add_option( 'wcvendors_feedback_sync_reviews', 'no' );
	}
}

/**
 * Add media upload limits
 *
 * @return  void
 * @since   1.6.0
 * @version 1.6.0
 */
function add_upload_limits() {
	if ( false === get_option( 'wcvendors_global_disk_usage_limit', false ) ) {
		add_option( 'wcvendors_global_disk_usage_limit', 0 );
		add_option( 'wcvendors_global_files_count_limit', 0 );
		add_option( 'wcvendors_upload_limits_include_thumbnails', 'yes' );
	}
}

/**
 * Add options to hide individual seo fields in product form
 *
 * @return  void
 * @since   1.6.0
 * @version 1.6.0
 */
function add_product_seo_options() {
	if ( false == get_option( 'wcvendors_hide_product_seo_title', false ) ) {
		add_option( 'wcvendors_hide_product_seo_title', 'no' );
		add_option( 'wcvendors_hide_product_seo_description', 'no' );
		add_option( 'wcvendors_hide_product_seo_keywords', 'no' );
		add_option( 'wcvendors_hide_product_seo_opengraph', 'no' );
		add_option( 'wcvendors_hide_product_seo_twitter', 'no' );
	}
}

/**
 * Add option to remove product review tab
 *
 * @return  void
 * @since   1.6.0
 * @version 1.6.0
 */
function add_remove_product_tab_option() {
	if ( false === get_option( 'wcvendors_feedback_remove_product_vendor_ratings_tab', false ) ) {
		add_option( 'wcvendors_feedback_remove_product_vendor_ratings_tab', 'no' );
	}
}

/**
 * Remove google plus options.
 *
 * @return void
 * @version 1.6.5
 * @since   1.6.5
 */
function delete_google_plus_options() {
	delete_option( 'wcvendors_hide_settings_social_google_plus' );
	delete_option( 'wcvendors_hide_signup_social_google_plus' );
}

/**
 * Add options for variations required.
 *
 * @return  void
 * @version 1.6.5
 * @since   1.6.5
 */
function add_variation_required_settings() {

	$options_suffices = array(
		'featured',
		'sku',
		'enabled',
		'downloadable',
		'virtual',
		'manage_stock',
		'price',
		'sale_price',
		'stock_qty',
		'allow_backorders',
		'stock_status',
		'weight',
		'dimensions',
		'shipping_class',
		'tax_class',
		'description',
		'download_files',
		'download_limit',
		'download_expiry',
	);

	foreach ( $options_suffices as $option_suffix ) {
		if ( false === get_option( 'wcvendors_required_product_variations_' . $option_suffix, false ) ) {
			add_option( 'wcvendors_required_product_variations_' . $option_suffix, 'no' );
		}
	}
}

/**
 * Update the option to hide order notes.
 *
 * If Pro has set the option to hide order notes, update the WC Vendors Marketplace to also disallow vendors to read or add order notes.
 * Then use the marketplace settings in Pro instead of adding new ones.
 *
 * @version 1.7.3
 * @since   1.7.3
 */
function update_order_note_settings_option() {
	$hide_order_note = wc_string_to_bool( get_option( 'wcvendors_hide_order_order_note', 'no' ) );

	if ( $hide_order_note ) {
		update_option( 'wcvendors_capability_order_read_notes', 'no' );
		update_option( 'wcvendors_capability_order_update_notes', 'no' );
		delete_option( 'wcvendors_hide_order_order_note' );
	}
}

/**
 * Update the option to hide signup and settings shipping option.
 *
 * @version 1.7.4
 * @since   1.7.4
 */
function update_hide_signup_and_settings_shipping_option() {
	// Settings Minimum Order Charge.
	if ( 'yes' === get_option( 'wcvendors_hide_settings_shipping_min_charge', 'no' ) ) {
		add_option( 'wcvendors_hide_settings_shipping_national_min_charge', 'yes' );
		add_option( 'wcvendors_hide_settings_shipping_international_min_charge', 'yes' );
	}
	delete_option( 'wcvendors_hide_settings_shipping_min_charge' );

	// Settings Maximum Order Charge.
	if ( 'yes' === get_option( 'wcvendors_hide_settings_shipping_max_charge', 'no' ) ) {
		add_option( 'wcvendors_hide_settings_shipping_national_max_charge', 'yes' );
		add_option( 'wcvendors_hide_settings_shipping_international_max_charge', 'yes' );
	}
	delete_option( 'wcvendors_hide_settings_shipping_max_charge' );

	// Settings Max Charge Product.
	delete_option( 'wcvendors_hide_settings_shipping_max_charge_product' );

	// Settings Free Shipping Order.
	if ( 'yes' === get_option( 'wcvendors_hide_settings_shipping_free_shipping_order', 'no' ) ) {
		add_option( 'wcvendors_hide_settings_shipping_national_free_shipping_order', 'yes' );
		add_option( 'wcvendors_hide_settings_shipping_international_free_shipping_order', 'yes' );
	}
	delete_option( 'wcvendors_hide_settings_shipping_free_shipping_order' );

	// Settings Free Shipping Product.
	delete_option( 'wcvendors_hide_settings_shipping_free_shipping_product' );

	// Signup Minimum Order Charge.
	if ( 'yes' === get_option( 'wcvendors_hide_signup_shipping_min_charge', 'no' ) ) {
		add_option( 'wcvendors_hide_signup_shipping_national_min_charge', 'yes' );
		add_option( 'wcvendors_hide_signup_shipping_international_min_charge', 'yes' );
	}
	delete_option( 'wcvendors_hide_signup_shipping_min_charge' );

	// Signup Maximum Order Charge.
	if ( 'yes' === get_option( 'wcvendors_hide_signup_shipping_max_charge', 'no' ) ) {
		add_option( 'wcvendors_hide_signup_shipping_national_max_charge', 'yes' );
		add_option( 'wcvendors_hide_signup_shipping_international_max_charge', 'yes' );
	}
	delete_option( 'wcvendors_hide_signup_shipping_max_charge' );

	// Signup Max Charge Product.
	delete_option( 'wcvendors_hide_signup_shipping_max_charge_product' );

	// Signup Free Shipping Order.
	if ( 'yes' === get_option( 'wcvendors_hide_signup_shipping_free_shipping_order', 'no' ) ) {
		add_option( 'wcvendors_hide_signup_shipping_national_free_shipping_order', 'yes' );
		add_option( 'wcvendors_hide_signup_shipping_international_free_shipping_order', 'yes' );
	}
	delete_option( 'wcvendors_hide_signup_shipping_free_shipping_order' );

	// Signup Free Shipping Product.
	delete_option( 'wcvendors_hide_signup_shipping_free_shipping_product' );
}

/**
 * Update the option to hide product shipping option for national/international.
 *
 * @version 1.7.5
 * @since   1.7.5
 */
function update_hide_product_shipping_option() {

	delete_option( 'wcvendors_hide_product_shipping_weight' );
	delete_option( 'wcvendors_required_product_shipping_weight' );

	if ( 'yes' === get_option( 'wcvendors_hide_product_shipping_max_charge', 'no' ) ) {
		add_option( 'wcvendors_hide_product_national_maximum_shipping_fee', 'yes' );
		add_option( 'wcvendors_hide_product_international_maximum_shipping_fee', 'yes' );
	}
	delete_option( 'wcvendors_hide_product_shipping_max_charge' );

	if ( 'yes' === get_option( 'wcvendors_required_product_shipping_max_charge', 'no' ) ) {
		add_option( 'wcvendors_required_product_national_maximum_shipping_fee', 'yes' );
		add_option( 'wcvendors_required_product_international_maximum_shipping_fee', 'yes' );
	}
	delete_option( 'wcvendors_required_product_shipping_max_charge' );

	delete_option( 'wcvendors_hide_product_shipping_free_shipping_order' );
	delete_option( 'wcvendors_required_product_shipping_free_shipping_order' );

	if ( 'yes' === get_option( 'wcvendors_hide_product_shipping_free_shipping_product', 'no' ) ) {
		add_option( 'wcvendors_hide_product_national_free_shipping_product', 'yes' );
		add_option( 'wcvendors_hide_product_international_free_shipping_product', 'yes' );
	}
	delete_option( 'wcvendors_hide_product_shipping_free_shipping_product' );

	if ( 'yes' === get_option( 'wcvendors_required_product_shipping_free_shipping_product', 'no' ) ) {
		add_option( 'wcvendors_required_product_national_free_shipping_product', 'yes' );
		add_option( 'wcvendors_required_product_international_free_shipping_product', 'yes' );
	}
	delete_option( 'wcvendors_required_product_shipping_free_shipping_product' );

	delete_option( 'wcvendors_hide_product_shipping_dimensions' );
	delete_option( 'wcvendors_required_product_shipping_dimensions' );

	delete_option( 'wcvendors_hide_product_shipping_shipping_class' );
	delete_option( 'wcvendors_required_product_shipping_shipping_class' );
}

if ( ! function_exists( 'default_vendor_shipping' ) ) {
	/**
	 * Save Default Vendor Shipping Setting Values
	 *
	 * @version 1.7.6
	 * @since   1.7.6
	 */
	function default_vendor_shipping() {
		$default_vendor_shipping = wcv_get_default_vendor_shipping();
		add_option( 'woocommerce_wcv_pro_vendor_shipping_settings', $default_vendor_shipping );
	}
}


if ( ! function_exists( 'default_veupdate_default_store_notice_html_optionndor_shipping' ) ) {
	/**
	 * Save Default Option for HTML Store Notice.
	 *
	 * @version 1.7.7
	 * @since   1.7.7
	 */
	function update_default_store_notice_html_option() {
		add_option( 'wcvendors_allow_settings_store_notice', 'yes' );
	}
}
