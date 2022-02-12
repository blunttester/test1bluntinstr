<?php
/**
 * The forms settings class
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Settings
 * @package     WCVendors/Admin/Settings
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCVendors_Pro_Settings_Forms', false ) ) :

	/**
	 * WC_Admin_Settings_General.
	 */
	class WCVendors_Pro_Settings_Forms extends WCVendors_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'forms';
			$this->label = __( 'Forms', 'wcvendors-pro' );

			add_action(
				'wcvendors_admin_field_form_fields_required',
				array(
					$this,
					'generate_form_fields_required_html',
				)
			);

			parent::__construct();
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				''         => __( 'General', 'wcvendors-pro' ),
				'product'  => __( 'Product', 'wcvendors-pro' ),
				'coupon'   => __( 'Coupon', 'wcvendors-pro' ),
				'settings' => __( 'Settings', 'wcvendors-pro' ),
				'signup'   => __( 'Signup', 'wcvendors-pro' ),
			);

			return apply_filters( 'wcvendors_get_sections_' . $this->id, $sections );
		}

		/**
		 * Save the fields including the required fields tables.
		 */
		public function save() {

			global $current_section;

			$options  = array();
			$settings = $this->get_settings( $current_section );

			foreach ( $settings as $setting ) {

				if ( 'form_fields_required' === $setting['type'] ) {

					if ( isset( $setting['fields'] ) && ! empty( $setting['fields'] ) ) {
						foreach ( $setting['fields'] as $field ) {
							$options[] = $field;
							// create the second field for saving.
							if ( array_key_exists( 'required_id', $field ) ) {
								$required_field       = $field;
								$required_field['id'] = $field['required_id'];
								$options[]            = $required_field;
							}
						}
					}
				}
			}

			WCVendors_Admin_Settings::save_fields( $options );

			parent::save();

		} // save

		/**
		 * Get settings array.
		 *
		 * @return  array
		 * @version 1.7.7
		 *
		 * @param string $current_section The current settings section.
		 */
		public function get_settings( $current_section = '' ) {

			if ( 'signup' === $current_section ) {
				$social_signup_array = array();
				foreach ( wcv_get_social_media_settings() as $k => $v ) {
					$social_signup_array[] = $v['admin_signup_form'];
				}

				$settings = apply_filters(
					'wcvendors_pro_settings_forms_signup',
					array(
						// Shop Display Options.
						array(
							'title' => __( 'Signup Form', 'wcvendors-pro' ),
							'type'  => 'title',
							// translators: Name used to refer to vendor.
							'desc'  => sprintf( __( 'Configure which fields the %s signup form should show or be required', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'    => 'settings_options',
						),

						array(
							'title'  => __( 'Tabs', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'Payment', 'wcvendors-pro' ),

									'id'      => 'wcvendors_hide_signup_tab_payment',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Branding', 'wcvendors-pro' ),

									'id'      => 'wcvendors_hide_signup_tab_branding',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Shipping', 'wcvendors-pro' ),

									'id'      => 'wcvendors_hide_signup_tab_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Social', 'wcvendors-pro' ),

									'id'      => 'wcvendors_hide_signup_tab_social',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Policies', 'wcvendors-pro' ),

									'id'      => 'wcvendors_hide_signup_tab_policies',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'SEO', 'wcvendors-pro' ),

									'id'      => 'wcvendors_hide_signup_tab_seo',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'   => __( 'Store', 'wcvendors-pro' ),

							'require' => true,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'       => __( 'Store description', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_signup_store_description',
									'required_id' => 'wcvendors_required_signup_store_description',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Seller info', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_signup_store_seller_info',
									'required_id' => 'wcvendors_required_signup_store_seller_info',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Company / blog URL', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_signup_store_company_url',
									'required_id' => 'wcvendors_required_signup_store_company_url',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Store phone', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_signup_store_phone',
									'required_id' => 'wcvendors_required_signup_store_phone',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									// translators: Name used to refer to vendors.
									'title'       => sprintf( __( 'Store address ( If you disable this, %s shipping will not work. )', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_signup_store_address',
									'required_id' => 'wcvendors_required_signup_store_address',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => sprintf( __( 'Store address map', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_signup_store_address_chooser',
									'required_id' => 'wcvendors_required_signup_store_address_chooser',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => sprintf( __( 'Store opening hours', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_signup_opening_hours',
									'required_id' => 'wcvendors_required_signup_opening_hours',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									// translators: 1. Name used to refer to vendor, 2. Name used to refer to vendors.
									'title'       => sprintf( __( '%1$s Shipping Type (Allow %2$s to choose shipping type.)', 'wcvendors-pro' ), wcv_get_vendor_name(), wcv_get_vendor_name( false, false ) ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_signup_store_shipping_type',
									'type'        => 'checkbox',
									'required_id' => 'wcvendors_required_signup_store_shipping_type',
									'default'     => false,
								),
							),
						),

						array(
							'title'  => __( 'Payment', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'Paypal email', 'wcvendors-pro' ),

									'id'      => 'wcvendors_hide_signup_payment_paypal',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Bank Account Name', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_payment_bank_account_name',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Bank Account Number', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_payment_bank_account_number',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Bank Name', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_payment_bank_name',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Routing number', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_payment_routing_number',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'IBAN', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_payment_iban',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'BIC/SWIF', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_payment_bic_swift',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'  => __( 'Branding', 'wcvendors-pro' ),
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'Store banner', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_branding_store_banner',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Store icon', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_branding_store_icon',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'  => __( 'Shipping', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'Product Handling Fee', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_handling_fee',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Default Shipping Fee', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_national_default_shipping_fee',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Minimum Order Charge', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_national_min_charge',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Maximum Order Charge', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_national_max_charge',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Free Shipping Order', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_national_free_shipping_order',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Free Shipping', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_national_free_national_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Charge Once Per Product', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_national_charge_once_per_product',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Shipping Disable', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_national_disable_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Default Shipping Fee', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_international_default_shipping_fee',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Minimum Order Charge', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_international_min_charge',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Maximum Order Charge', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_international_max_charge',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Free Shipping Order', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_international_free_shipping_order',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Free Shipping', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_international_free_international_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Charge Once Per Product', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_international_charge_once_per_product',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Shipping Disable', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_international_disable_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'  => __( 'Policies', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'Privacy policy', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_store_policy_privacy',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Terms and conditions', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_store_policy_terms',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Shipping policy', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_shipping_policy',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Return policy', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_shipping_return_policy',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'  => __( 'Social', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => $social_signup_array,
						),

						array(
							'title'  => __( 'SEO', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'SEO Title', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_seo_title',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Meta Description', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_seo_meta_description',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Meta Keywords', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_seo_meta_keywords',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Facebook Title', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_seo_fb_title',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Facebook Description', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_seo_fb_description',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Facebook Image', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_seo_fb_description',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Twitter Title', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_seo_twitter_title',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Twitter Description', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_seo_twitter_description',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Twitter Image', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_signup_seo_twitter_image',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),
						array(
							'title'   => __( 'Use HTML for policy', 'wcvendors-pro' ),
							'desc'    => __( 'Use WP Editor for store policies on signup page.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_allow_signup_policy_html',
							'type'    => 'checkbox',
							'default' => false,
						),
						array(
							'type' => 'sectionend',
							'id'   => 'settings_options',
						),

					)
				);

			} elseif ( 'settings' === $current_section ) {
				$social_settings_array = array();
				foreach ( wcv_get_social_media_settings() as $k => $v ) {
					$social_settings_array[] = $v['admin_settings_form'];
				}
				$settings = apply_filters(
					'wcvendors_pro_settings_forms_settings',
					array(

						// Vendor store settings.
						array(
							'title' => __( 'Settings Form', 'wcvendors-pro' ),
							'type'  => 'title',
							'desc'  => sprintf( __( 'Configure which fields for the store settings form should show', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
							'id'    => 'settings_options',
						),

						array(
							'title'  => __( 'Tabs', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'Payment', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_tab_payment',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Branding', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_tab_branding',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Shipping', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_tab_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Social', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_tab_social',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Policies', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_tab_policies',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'SEO', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_tab_seo',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'   => __( 'Store', 'wcvendors-pro' ),
							'require' => true,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'       => __( 'Store description', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_settings_store_description',
									'required_id' => 'wcvendors_required_settings_store_description',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Seller info', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_settings_store_seller_info',
									'required_id' => 'wcvendors_required_settings_store_seller_info',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Company / blog URL', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_settings_store_company_url',
									'required_id' => 'wcvendors_required_settings_store_company_url',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Store phone', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_settings_store_phone',
									'required_id' => 'wcvendors_required_settings_store_phone',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									// translators: Name used to refer to vendor.
									'title'       => sprintf( __( 'Store address ( If you disable this, %s shipping will not work.  )', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_settings_store_address',
									'required_id' => 'wcvendors_required_settings_store_address',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => sprintf( __( 'Store address map', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_settings_store_address_chooser',
									'required_id' => 'wcvendors_required_settings_store_address_chooser',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => sprintf( __( 'Store opening hours', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_settings_opening_hours',
									'required_id' => 'wcvendors_required_settings_opening_hours',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									// translators: Name used to refer to vendors.
									'title'       => sprintf( __( 'Vacation Mode ( allow %s to create a message to show on their stores. )', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_settings_store_vacation_mode',
									'required_id' => 'wcvendors_required_settings_store_vacation_mode',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									// translators: Name used to refer to vendors.
									'title'       => sprintf( __( 'Product Total Sales ( allow %s to show product total sales. )', 'wcvendors-pro' ), wcv_get_vendor_name() ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_settings_store_product_total_sales',
									'required_id' => 'wcvendors_required_settings_store_product_total_sales',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									// translators: Name used to refer to vendor.
									'title'   => sprintf( __( '%s Store Notice', 'wcvendors-pro' ), wcv_get_vendor_name() ),
									// translators: Name used to refer to vendors.
									'desc'    => sprintf( __( 'Allow %s to enable and show a store notice.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
									'id'      => 'wcvendors_hide_settings_store_enable_notice',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									// translators: Name used to refer to vendor.
									'title'   => sprintf( __( '%s Store Google Analytics', 'wcvendors-pro' ), wcv_get_vendor_name() ),
									// translators: Name used to refer to vendors.
									'desc'    => sprintf( __( 'Allow %s to enable and show a add Google Analytics ID.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
									'id'      => 'wcvendors_hide_settings_ga',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'  => __( 'Payment', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'Paypal email', 'wcvendors-pro' ),

									'id'      => 'wcvendors_hide_settings_payment_paypal',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Bank Account Name', 'wcvendors-pro' ),

									'id'      => 'wcvendors_hide_settings_payment_bank_account_name',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Bank Account Number', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_payment_bank_account_number',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Bank Name', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_payment_bank_name',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Routing number', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_payment_routing_number',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'IBAN', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_payment_iban',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'BIC/SWIF', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_payment_bic_swift',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'   => __( 'Branding', 'wcvendors-pro' ),
							'require' => true,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'       => __( 'Store banner', 'wcvendors-pro' ),
									'id'          => 'wcvendors_hide_settings_branding_store_banner',
									'required_id' => 'wcvendors_required_settings_branding_store_banner',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Store icon', 'wcvendors-pro' ),
									'id'          => 'wcvendors_hide_settings_branding_store_icon',
									'required_id' => 'wcvendors_required_settings_branding_store_icon',
									'type'        => 'checkbox',
									'default'     => false,
								),
							),
						),

						array(
							'title'  => __( 'Shipping', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'Product handling fee', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_handling_fee',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Default Shipping Fee', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_national_default_shipping_fee',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Minimum Order Charge', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_national_min_charge',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Maximum Order Charge', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_national_max_charge',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Free Shipping Order', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_national_free_shipping_order',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Free Shipping', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_national_free_national_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Charge Once Per Product', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_national_charge_once_per_product',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National Shipping Disable', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_national_disable_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Default Shipping Fee', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_international_default_shipping_fee',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Minimum Order Charge', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_international_min_charge',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Maximum Order Charge', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_international_max_charge',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Free Shipping Order', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_international_free_shipping_order',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Free Shipping', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_international_free_international_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Charge Once Per Product', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_international_charge_once_per_product',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International Shipping Disable', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_international_disable_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'  => __( 'Social', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => $social_settings_array,
						),

						array(
							'title'  => __( 'Policies', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'Privacy policy', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_store_policy_privacy',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Terms and conditions', 'wcvendors-pro' ),
									'type'    => 'checkbox',
									'id'      => 'wcvendors_hide_settings_store_policy_terms',
									'default' => false,
								),
								array(
									'title'   => __( 'Shipping policy', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_shipping_policy',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Return policy', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_shipping_return_policy',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'  => __( 'SEO', 'wcvendors-pro' ),
							'desc'   => '',
							'type'   => 'form_fields_required',
							'fields' => array(
								array(
									'title'   => __( 'SEO Title', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_seo_title',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Meta Description', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_seo_meta_description',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Meta Keywords', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_seo_meta_keywords',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Facebook Title', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_seo_fb_title',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Facebook Description', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_seo_fb_description',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Facebook Image', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_seo_fb_image',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Twitter Title', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_seo_twitter_title',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Twitter Description', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_seo_twitter_description',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'Twitter Image', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_settings_seo_twitter_image',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),
						array(
							'title'   => __( 'Use HTML for policy', 'wcvendors-pro' ),
							'desc'    => __( 'Use WP Editor for store policies setting.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_allow_settings_policy_html',
							'type'    => 'checkbox',
							'default' => false,
						),
						array(
							'title'   => __( 'Use HTML for store notice', 'wcvendors-pro' ),
							'desc'    => __( 'Use the WP Editor for the store notice.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_allow_settings_store_notice',
							'type'    => 'checkbox',
							'default' => 'yes',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'settings_options',
						),
					)
				);

			} elseif ( 'product' === $current_section ) {

				$settings = apply_filters(
					'wcvendors_pro_settings_forms_product',
					array(
						// Product Form fields.
						array(
							'title' => __( 'Form Fields', 'wcvendors-pro' ),
							'type'  => 'title',
							'desc'  => sprintf( __( 'Configure which fields for the product edit form should show and which are required', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
							'id'    => 'product_form_options',
						),

						array(
							'title'   => __( 'Basic', 'wcvendors-pro' ),
							'require' => true,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'       => __( 'Description', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_basic_description',
									'required_id' => 'wcvendors_required_product_basic_description',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Short Description', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_basic_short_description',
									'required_id' => 'wcvendors_required_product_basic_short_description',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Categories', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_basic_categories',
									'required_id' => 'wcvendors_required_product_basic_categories',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Tags', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_basic_tags',
									'required_id' => 'wcvendors_required_product_basic_tags',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Attributes', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_basic_attributes',
									'required_id' => 'wcvendors_required_product_basic_attributes',
									'type'        => 'checkbox',
									'default'     => false,
								),
							),
						),

						array(
							'title'   => __( 'Media', 'wcvendors-pro' ),
							'require' => true,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'       => __( 'Featured Image (also disables the gallery)', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_media_featured',
									'required_id' => 'wcvendors_required_product_media_featured',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Gallery', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_media_gallery',
									'required_id' => 'wcvendors_required_product_media_gallery',
									'type'        => 'checkbox',
									'default'     => false,
								),
							),
						),

						array(
							'title'   => __( 'General', 'wcvendors-pro' ),
							'require' => true,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'       => __( 'SKU', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_sku',
									'required_id' => 'wcvendors_required_product_general_sku',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Private listing', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_private_listing',
									'required_id' => 'wcvendors_required_product_general_private_listing',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'External URL', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_external_url',
									'required_id' => 'wcvendors_required_product_general_external_url',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Button text for external url', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_button_text',
									'required_id' => 'wcvendors_required_product_general_button_text',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Price (disables sale price)', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_price',
									'required_id' => 'wcvendors_required_product_general_price',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Sale price', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_sale_price',
									'required_id' => 'wcvendors_required_product_general_sale_price',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Tax', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_tax',
									'required_id' => 'wcvendors_required_product_general_tax',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Download files (also disables all download fields)', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_download_files',
									'required_id' => 'wcvendors_required_product_general_download_files',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									// translators: Name used to refer to vendor.
									'title'       => sprintf( __( 'Disable the %s ability to change file URL to prevent remote file URLs', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_download_file_url',
									'required_id' => 'wcvendors_required_product_general_download_file_url',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Download limit', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_download_limit',
									'required_id' => 'wcvendors_required_product_general_download_limit',
									'type'        => 'checkbox',
									'default'     => false,
								),

								array(
									'title'       => __( 'Download expiry', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_download_expiry',
									'required_id' => 'wcvendors_required_product_general_download_expiry',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Download type', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_general_download_type',
									'required_id' => 'wcvendors_required_product_general_download_type',
									'type'        => 'checkbox',
									'default'     => false,
								),
							),
						),

						array(
							'title'   => __( 'Inventory', 'wcvendors-pro' ),
							'require' => true,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'       => __( 'Manage Inventory (also disables all inventory fields)', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_inventory_manage_inventory',
									'required_id' => 'wcvendors_required_product_inventory_manage_inventory',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Stock qty', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_inventory_stock_qty',
									'required_id' => 'wcvendors_required_product_inventory_stock_qty',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Backorders', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_inventory_backorders',
									'required_id' => 'wcvendors_required_product_inventory_backorders',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Stock status', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_inventory_stock_status',
									'required_id' => 'wcvendors_required_product_inventory_stock_status',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Low stock threshold', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_inventory_low_stock_threshold',
									'required_id' => 'wcvendors_required_product_inventory_low_stock_threshold',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Sold individually', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_inventory_sold_individually',
									'required_id' => 'wcvendors_required_product_inventory_sold_individually',
									'type'        => 'checkbox',
									'default'     => false,
								),

							),
						),

						array(
							'title'   => __( 'Shipping', 'wcvendors-pro' ),

							'require' => true,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'       => __( 'Product handling fee', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_shipping_handling_fee',
									'required_id' => 'wcvendors_required_product_shipping_handling_fee',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'National shipping fee', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_national_shipping_fee',
									'required_id' => 'wcvendors_required_product_national_shipping_fee',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'National minimum shipping fee', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_national_minimum_shipping_fee',
									'required_id' => 'wcvendors_required_product_national_minimum_shipping_fee',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'National maximum shipping fee', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_national_maximum_shipping_fee',
									'required_id' => 'wcvendors_required_product_national_maximum_shipping_fee',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'National free shipping product', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_national_free_shipping_product',
									'required_id' => 'wcvendors_required_product_national_free_shipping_product',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'   => __( 'National free shipping', 'wcvendors-pro' ),
									'desc'    => '',
									'id'      => 'wcvendors_hide_product_national_free_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National charge once per product', 'wcvendors-pro' ),
									'desc'    => '',
									'id'      => 'wcvendors_hide_product_national_charge_once_per_product',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'National shipping disable', 'wcvendors-pro' ),
									'desc'    => '',
									'id'      => 'wcvendors_hide_product_national_shipping_disable',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'       => __( 'International shipping fee', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_international_shipping_fee',
									'required_id' => 'wcvendors_required_product_international_shipping_fee',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'International minimum shipping fee', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_international_minimum_shipping_fee',
									'required_id' => 'wcvendors_required_product_international_minimum_shipping_fee',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'International maximum shipping fee', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_international_maximum_shipping_fee',
									'required_id' => 'wcvendors_required_product_international_maximum_shipping_fee',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'International free shipping product', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_international_free_shipping_product',
									'required_id' => 'wcvendors_required_product_international_free_shipping_product',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'   => __( 'International free shipping', 'wcvendors-pro' ),
									'desc'    => '',
									'id'      => 'wcvendors_hide_product_international_free_shipping',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International charge once per product', 'wcvendors-pro' ),
									'desc'    => '',
									'id'      => 'wcvendors_hide_product_international_charge_once_per_product',
									'type'    => 'checkbox',
									'default' => false,
								),
								array(
									'title'   => __( 'International shipping disable', 'wcvendors-pro' ),
									'desc'    => '',
									'id'      => 'wcvendors_hide_product_international_shipping_disable',
									'type'    => 'checkbox',
									'default' => false,
								),
							),
						),

						array(
							'title'   => __( 'Upsells / Cross sells', 'wcvendors-pro' ),
							'require' => true,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'       => __( 'Up sells', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_upsells_up_sells',
									'required_id' => 'wcvendors_required_product_upsells_up_sells',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Cross sells', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_upsells_crosssells',
									'required_id' => 'wcvendors_required_product_upsells_crosssells',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Grouped Products', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_upsells_grouped_products',
									'required_id' => 'wcvendors_required_product_upsells_grouped_products',
									'type'        => 'checkbox',
									'default'     => false,
								),

							),
						),

						array(
							'title'   => __( 'Variations', 'wcvendors-pro' ),
							'require' => true,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'       => __( 'Featured Image', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_featured',
									'required_id' => 'wcvendors_required_product_variations_featured',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'SKU', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_sku',
									'required_id' => 'wcvendors_required_product_variations_sku',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Enabled', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_enabled',
									'required_id' => 'wcvendors_required_product_variations_enabled',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Downloadable', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_downloadable',
									'required_id' => 'wcvendors_required_product_variations_downloadable',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Virtual', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_virtual',
									'required_id' => 'wcvendors_required_product_variations_virtual',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Manage Stock', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_manage_stock',
									'required_id' => 'wcvendors_required_product_variations_manage_stock',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Price', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_price',
									'required_id' => 'wcvendors_required_product_variations_price',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Sale Price', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_sale_price',
									'required_id' => 'wcvendors_required_product_variations_sale_price',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Stock QTY', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_stock_qty',
									'required_id' => 'wcvendors_required_product_variations_stock_qty',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Allow backorders', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_allow_backorders',
									'required_id' => 'wcvendors_required_product_variations_allow_backorders',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Stock Status', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_stock_status',
									'required_id' => 'wcvendors_required_product_variations_stock_status',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Weight', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_weight',
									'required_id' => 'wcvendors_required_product_variations_weight',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Dimensions', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_dimensions',
									'required_id' => 'wcvendors_required_product_variations_dimensions',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Shipping Class', 'wcvendors-pro' ),
									'desc'        => 'shipping_class',
									'id'          => 'wcvendors_hide_product_variations_shipping_class',
									'required_id' => 'wcvendors_required_product_variations_shipping_class',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Tax Class', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_tax_class',
									'required_id' => 'wcvendors_required_product_variations_tax_class',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Description', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_description',
									'required_id' => 'wcvendors_required_product_variations_description',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Download files ( disables all download fields on variations )', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_download_files',
									'required_id' => 'wcvendors_required_product_variations_download_files',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Download limit', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_download_limit',
									'required_id' => 'wcvendors_required_product_variations_download_limit',
									'type'        => 'checkbox',
									'default'     => false,
								),
								array(
									'title'       => __( 'Download expiry', 'wcvendors-pro' ),
									'desc'        => '',
									'id'          => 'wcvendors_hide_product_variations_download_expiry',
									'required_id' => 'wcvendors_required_product_variations_download_expiry',
									'type'        => 'checkbox',
									'default'     => false,
								),
							),
						),

						array(
							'title'   => __( 'Product SEO', 'wcvendors-pro' ),

							'require' => false,
							'type'    => 'form_fields_required',
							'fields'  => array(
								array(
									'title'   => __( 'Product SEO (Disables all product SEO fields)', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_product_seo',
									'type'    => 'checkbox',
									'default' => 'no',
								),
								array(
									'title'   => __( 'Title', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_product_seo_title',
									'type'    => 'checkbox',
									'default' => 'no',
								),
								array(
									'title'   => __( 'Description', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_product_seo_description',
									'type'    => 'checkbox',
									'class'   => 'product-seo-field',
									'default' => 'no',
								),
								array(
									'title'   => __( 'Keywords', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_product_seo_keywords',
									'type'    => 'checkbox',
									'class'   => 'product-seo-field',
									'default' => 'no',
								),
								array(
									'title'   => __( 'OpenGraph', 'wcvendors-pro' ),
									'id'      => 'wcvendors_hide_product_seo_opengraph',
									'type'    => 'checkbox',
									'class'   => 'product-seo-field',
									'default' => 'no',
								),
								array(
									'title'   => __( 'Twitter', 'wcvendors-pro' ),
									'desc'    => 'hide option to enable Twitter cards display',
									'id'      => 'wcvendors_hide_product_seo_twitter',
									'type'    => 'checkbox',
									'class'   => 'product-seo-field',
									'default' => 'no',
								),
							),
						),

						array(
							'title'   => __( 'Variation Actions Type', 'wcvendors-pro' ),
							'desc'    => __( 'Which variations drop down type to use.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_variation_actions_dropdown_type',
							'type'    => 'select',
							'class'   => 'wc-enhanced-select-nostd',
							'css'     => 'min-width:300px;',
							'options' => array(
								'single'  => __( 'Single Dropdown', 'wcvendors-pro' ),
								'grouped' => __( 'Grouped Dropdowns', 'wcvendors-pro' ),
							),
							'default' => 'single',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'product_form_options',
						),

						// Product Form Options.
						array(
							'title' => __( 'Product Form', 'wcvendors-pro' ),
							'type'  => 'title',
							// translators: Name used to refer to vendors.
							'desc'  => sprintf( __( 'Configure the product edit form options for %s', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
							'id'    => 'product_options',
						),

						array(
							'title'   => __( 'Default Product Form', 'wcvendors-pro' ),
							'desc'    => __( 'Which product form to use.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_product_form_template',
							'type'    => 'select',
							'class'   => 'wc-enhanced-select-nostd',
							'css'     => 'min-width:300px;',
							'options' => wcv_get_product_templates(),
							'default' => 'edit',
						),

						array(
							'title'   => __( 'Save Product Redirect', 'wcvendors-pro' ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'After the %s saves the product redirect to.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_save_product_redirect',
							'type'    => 'select',
							'class'   => 'wc-enhanced-select-nostd',
							'css'     => 'min-width:300px;',
							'class'   => 'wc-enhanced-select-nostd',
							'options' => array(
								'edit'  => __( 'Edit product form', 'wcvendors-pro' ),
								'list'  => __( 'Product list', 'wcvendors-pro' ),
								'view'  => __( 'Published/Preview Product', 'wcvendors-pro' ),
								'empty' => __( 'Add new product', 'wcvendors-pro' ),
							),
							'default' => 'empty',
						),

						array(
							'title'   => __( 'Use HTML', 'wcvendors-pro' ),
							'desc'    => __( 'Use WP Editor for product description and product short description.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_allow_product_html',
							'type'    => 'checkbox',
							'default' => false,
						),

						array(
							'title'   => __( 'Allow media', 'wcvendors-pro' ),
							'desc'    => __( 'Allow uploading media to product description and product short description.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_allow_product_description_media',
							'type'    => 'checkbox',
							'default' => false,
						),

						array(
							'title'   => __( 'Product Shipping', 'wcvendors-pro' ),
							// translators: %s - the name used to refer to a Vendor.
							'desc'    => sprintf( __( 'Show %s level shipping rates on product shipping tab if no shipping rates were defined for the product.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcv_use_vendor_level_shipping_on_products',
							'type'    => 'checkbox',
							'default' => false,
						),

						array(
							'title'   => __( 'Category Display', 'wcvendors-pro' ),
							'desc'    => __( 'What kind of category selection.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_category_display',
							'class'   => 'wc-enhanced-select-nostd',
							'css'     => 'min-width:300px;',
							'type'    => 'select',
							'options' => array(
								'select'        => __( 'Multi select', 'wcvendors-pro' ),
								'single_select' => __( 'Single select', 'wcvendors-pro' ),
								'checklist'     => __( 'Check list', 'wcvendors-pro' ),
							),
							'default' => 'select',
						),

						array(
							'title' => __( 'Hide Categories', 'wcvendors-pro' ),
							// translators: Name used to refer to vendor.
							'desc'  => sprintf( __( 'A comma separated list of category IDs to hide from the %s product form. ', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'    => 'wcvendors_hide_categories_list',
							'type'  => 'text',
						),

						array(
							'title'   => __( 'Categories limit', 'wcvendors-pro' ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'Limit the number of categories a %s can select. ', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_category_limit',
							'type'    => 'text',
							'default' => '',
						),

						array(
							'title'   => __( 'Tag Display', 'wcvendors-pro' ),
							'desc'    => __( 'What kind of tag selection.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_tag_display',
							'class'   => 'wc-enhanced-select-nostd',
							'css'     => 'min-width:300px;',
							'type'    => 'select',
							'options' => array(
								'select'         => __( 'Multi select', 'wcvendors-pro' ),
								'select_limited' => __( 'Multi select limited', 'wcvendors-pro' ),
							),
							'default' => 'select',
						),

						array(
							'title'   => __( 'Tag Separator', 'wcvendors-pro' ),
							'desc'    => __( 'What kind of tag separator.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_tag_separator',
							'class'   => 'wc-enhanced-select-nostd',
							'css'     => 'min-width:300px;',
							'type'    => 'select',
							'options' => array(
								'both'  => __( 'Comma (,) and space ( )', 'wcvendors-pro' ),
								'space' => __( 'Space only ( )', 'wcvendors-pro' ),
								'comma' => __( 'Comma only (,)', 'wcvendors-pro' ),
							),
							'default' => 'select',
						),

						array(
							'title'   => __( 'Tags limit', 'wcvendors-pro' ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'Limit the number of tags a %s can select. ', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_tag_limit',
							'type'    => 'text',
							'default' => '',
						),

						array(
							'title'   => __( 'File Display', 'wcvendors-pro' ),
							'desc'    => __( 'The format to display on the file uploader.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_file_display',
							'class'   => 'wc-enhanced-select-nostd',
							'css'     => 'min-width:300px;',
							'type'    => 'select',
							'options' => array(
								'file_url'  => __( 'File URL', 'wcvendors-pro' ),
								'file_name' => __( 'File name', 'wcvendors-pro' ),
							),
							'default' => 'file_url',
						),

						array(
							'title' => __( 'Hide Attributes', 'wcvendors-pro' ),
							'desc'  => sprintf( __( 'A comma separated list of attributes IDs to hide from the %s product form. ', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'    => 'wcvendors_hide_attributes_list',
							'type'  => 'text',
						),

						array(
							'title'   => __( 'Attribute Terms', 'wcvendors-pro' ),
							// translators: 1. Name used to refer to vendor.
							'desc'    => sprintf( __( 'Allow %1$s to add attribute terms. This does not allow %1$s to add attributes only terms for already defined attributes.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
							'tip'     => sprintf( __( '', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_allow_vendor_attribute_terms',
							'type'    => 'checkbox',
							'default' => false,
						),

						array(
							// translators: Name used to refer to vendor.
							'title'   => sprintf( __( '%s filename prefix', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							// translators: Name used to refer to vendors.
							'desc'    => sprintf( __( 'Prefix all %s file name uploads', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_vendor_image_prefix',
							'class'   => 'wc-enhanced-select-nostd',
							'css'     => 'min-width:300px;',
							'type'    => 'select',
							'options' => array(
								'none'            => __( 'No file prefix', 'wcvendors-pro' ),
								// translators: Name used to refer to vendor.
								'vendor_id'       => sprintf( __( '%s ID', 'wcvendors-pro' ), wcv_get_vendor_name() ),
								// translators: Name used to refer to vendor.
								'vendor_username' => sprintf( __( '%s Username', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							),
							'default' => 'none',
						),

						array(
							// translators: Name used to refer to vendor.
							'title'   => sprintf( __( '%s Disk usage limit', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'Limit the total disk usage for a %s.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_vendor_disk_usage_limit',
							'type'    => 'number',
							'default' => __( '100', 'wcvendors-pro' ),
						),

						array(
							// translators: Name used to refer to vendor.
							'title'   => sprintf( __( '%s File upload limit', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'Limit the total file size %ss can upload.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_vendor_file_count_limit',
							'type'    => 'number',
							'default' => __( '100', 'wcvendors-pro' ),
						),

						array(
							'title'   => __( 'Max Gallery Images', 'wcvendors-pro' ),
							'desc'    => __( 'The maximum number of images that can be uploaded to the gallery. ', 'wcvendors-pro' ),
							'id'      => 'wcvendors_product_max_gallery_count',
							'type'    => 'number',
							'default' => 4,
						),

						array(
							'title' => __( 'Max image width', 'wcvendors-pro' ),
							'desc'  => __( 'The maximum allowed width (px) for an uploaded image', 'wcvendors-pro' ),
							'id'    => 'wcvendors_product_max_image_width',
							'type'  => 'number',
						),

						array(
							'title' => __( 'Max image height', 'wcvendors-pro' ),
							'desc'  => __( 'The maximum allowed height (px) for an uploaded image', 'wcvendors-pro' ),
							'id'    => 'wcvendors_product_max_image_height',
							'type'  => 'number',
						),

						array(
							'title' => __( 'Min image width', 'wcvendors-pro' ),
							'desc'  => __( 'The minimum allowed width (px) for an uploaded image', 'wcvendors-pro' ),
							'id'    => 'wcvendors_product_min_image_width',
							'type'  => 'number',
						),

						array(
							'title' => __( 'Min image height', 'wcvendors-pro' ),
							'desc'  => __( 'The minimum allowed height (px) for an uploaded image', 'wcvendors-pro' ),
							'id'    => 'wcvendors_product_min_image_height',
							'type'  => 'number',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'product_options',
						),

					)
				);

			} elseif ( 'coupon' === $current_section ) {
				$settings = apply_filters(
					'wcvendors_pro_settings_forms_coupon',
					array(

						// Coupon Options.
						array(
							'title' => __( 'Coupon', 'wcvendors-pro' ),
							'type'  => 'title',
							// translators: Name used to refer to vendor.
							'desc'  => sprintf( __( 'Configure what happens after the %s saves a coupon.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'    => 'coupon_options',
						),
						array(
							'title'   => __( 'Save Coupon Redirect', 'wcvendors-pro' ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'After the %s saves the coupon redirect to.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_save_coupon_redirect',
							'type'    => 'select',
							'class'   => 'wc-enhanced-select-nostd',
							'css'     => 'min-width:300px;',
							'class'   => 'wc-enhanced-select-nostd',
							'options' => array(
								'edit'   => __( 'Edit coupon form', 'wcvendors-pro' ),
								'list'   => __( 'Coupon list', 'wcvendors-pro' ),
								'addnew' => __( 'Add new coupon', 'wcvendors-pro' ),
							),
							'default' => 'addnew',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'coupon_options',
						),
					)
				);

			} else {

				$settings = apply_filters(
					'wcvendors_pro_settings_forms_general',
					array(

						// General Options.
						array(
							'title' => __( 'General', 'wcvendors-pro' ),
							'type'  => 'title',
							'desc'  => __( 'General options applicable to all forms.', 'wcvendors-pro' ),
							'id'    => 'general_options',
						),
						array(
							'title'   => __( 'Allow HTML in inputs', 'wcvendors-pro' ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'Allow %s to add html source to the inputs and text areas on forms.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							// translators: Name used to refer to vendor.
							'tip'     => sprintf( __( 'This will allow %s the ability to add html source code to their inputs and text areas.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_allow_form_markup',
							'type'    => 'checkbox',
							'default' => false,
						),
						array(
							'title'   => __( 'Allow media uploads', 'wcvendors-pro' ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'Allow %s to upload media on the wp_editor on forms.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							// translators: Name used to refer to vendor.
							'tip'     => sprintf( __( 'This will allow %s the ability to upload media to their shop description and seller info', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_allow_editor_media',
							'type'    => 'checkbox',
							'default' => false,
						),
						array(
							// translators: Name used to refer to vendor.
							'title'   => sprintf( __( '%s filename prefix', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'Prefix all %s file name uploads', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_vendor_image_prefix',
							'type'    => 'select',
							'options' => array(
								'none'            => __( 'No file prefix', 'wcvendors-pro' ),
								// translators: Name used to refer to vendor.
								'vendor_id'       => sprintf( __( '%s ID', 'wcvendors-pro' ), wcv_get_vendor_name() ),
								// translators: Name used to refer to vendor.
								'vendor_username' => sprintf( __( '%s Username', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							),
							'default' => 'none',
						),

						array(
							// translators: Name used to refer to vendor.
							'title'   => sprintf( __( '%s disk usage limit', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'Limit the total disk space %ss can use. Specify size in Mb. 0 = Unlimited disk space.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_global_disk_usage_limit',
							'type'    => 'number',
							'default' => __( '0', 'wcvendors-pro' ),
						),

						array(
							// translators: Name used to refer to vendor.
							'title'   => sprintf( __( '%s file count limit', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'Limit the number of files %ss can upload. 0 = Unlimited number of files.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_global_files_count_limit',
							'type'    => 'number',
							'default' => __( '0', 'wcvendors-pro' ),
						),

						array(
							'title'   => __( 'Limits include thumbnails', 'wcvendors-pro' ),
							'desc'    => __( 'Check if you want media thumbnails to contribute to limits.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_upload_limits_include_thumbnails',
							'type'    => 'checkbox',
							'default' => 'yes',
						),

						array(
							'title'   => __( 'Shipping type', 'wcvendors-pro' ),
							// translators: Name used to refer to vendor.
							'desc'    => sprintf( __( 'Allow vendors to select the shipping type they want to apply to their store or products.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'id'      => 'wcvendors_vendor_select_shipping',
							'type'    => 'checkbox',
							'default' => false,
						),

						array(
							'type' => 'sectionend',
							'id'   => 'general_options',
						),

						array(
							'title' => __( 'Google Maps API', 'wcvendors-pro' ),
							'type'  => 'title',
							'desc'  => __( 'Google Maps API Key and other settings.', 'wcvendors-pro' ),
							'id'    => 'google_maps_options',
						),

						array(
							'title' => __( 'Google Maps API key', 'wcvendors-pro' ),
							'id'    => 'wcvendors_pro_google_maps_api_key',
							'css'   => 'min-width:400px;',
							// translators: Text of button to Get Google API Key.
							'desc'  => sprintf( '<br/><a class="button" target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key">%s</a></p>', __( 'Get Google MAPS API Key', 'wcvendors-pro' ) ),
							'type'  => 'text',
						),

						array(
							'title'   => __( 'Map Zoom Level', 'wcvendors-pro' ),
							'id'      => 'wcvendors_pro_google_maps_zoom_level',
							'css'     => 'min-width:400px;',
							'type'    => 'number',
							'default' => 18,
						),
						array(
							'title'   => __( 'Location Picker Visibility', 'wcvendors-pro' ),
							'id'      => 'wcvendors_pro_location_picker_default_visibility',
							'css'     => 'min-width:400px;',
							'type'    => 'select',
							'options' => array(
								'hidden'  => __( 'Hidden', 'wcvendors-pro' ),
								'visible' => __( 'Visible', 'wcvendors-pro' ),
							),
							'defaul'  => 'hidden',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'general_options',
						),

					)
				);

			}

			return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );

		}

		/**
		 * Output the form fields table
		 *
		 * @param string $value The attributes fo the form field to be output.
		 */
		public function generate_form_fields_required_html( $value ) {
			$require           = isset( $value['require'] ) ? $value['require'] : false;
			$field_description = WCVendors_Admin_Settings::get_field_description( $value );
			extract( $field_description );

			?>
			<tr valign="top" class="wcv_form_fields_table">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo wp_kses_post( $value['title'] ); ?></label>

				</th>
				<td class="forminp">
					<?php echo ( $description ) ? wp_kses_post( $description ) : ''; ?>
					<div class="wcv-form_fields_required">
						<?php include apply_filters( 'wcv_partial_path_pro_form_fields_required', 'partials/html-form-fields-table.php' ); ?>
					</div>

				</td>
			</tr>
			<?php

		}

	}

endif;

return new WCVendors_Pro_Settings_Forms();
