<?php

/**
 * The WC Vendors Pro settings
 *
 * Defines the WC Vendors Pro settings that hook into WC Vendors
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin
 * @author     Jamie Madden <support@wcvendors.com>
 */

class WCVendors_Pro_Admin_Settings {


	public function __construct() {

		add_filter( 'wcvendors_get_settings_pages', array( $this, 'forms_page' ) );
		add_filter( 'wcvendors_settings_general', array( $this, 'general_settings' ) );

		// Hook into existing sections
		add_filter( 'wcvendors_get_settings_general', array( $this, 'general' ) );
		add_filter( 'wcvendors_get_settings_display', array( $this, 'display' ) );
		add_filter( 'wcvendors_get_settings_commission', array( $this, 'commission' ) );
		add_filter( 'wcvendors_global_commission_types', array( $this, 'add_commission_types' ) );
		add_filter( 'wcvendors_vendor_commission_types', array( $this, 'add_commission_types' ) );
		add_filter( 'wcv_product_panel_commission_types', array( $this, 'add_product_commission_types' ) );
		add_action( 'wcvendors_admin_field_include', array( $this, 'include_commission_tiers' ) );
		add_filter( 'wcvendors_get_settings_capabilities', array( $this, 'capabilities' ) );

		// Get Sections
		add_filter( 'wcvendors_get_sections_display', array( $this, 'get_display_sections' ) );
		add_filter( 'wcvendors_get_sections_capabilities', array( $this, 'get_capabilities_sections' ) );

		// Section definitions
		add_filter( 'wcvendors_get_settings_display', array( $this, 'display_sections' ), 10, 2 );
		add_filter( 'wcvendors_get_settings_capabilities', array( $this, 'capabilities_sections' ), 10, 2 );

		add_action( 'wcvendors_image_buttons', array( $this, 'add_banner_reset_button' ) );

	}

	/**
	 * Hook the forms page into the settings
	 *
	 * @since 2.0.0
	 */
	public function forms_page( $settings ) {

		$settings[] = include WCV_PRO_ABSPATH_ADMIN . 'settings/class-wcvendors-pro-settings-forms.php';
		$settings[] = include WCV_PRO_ABSPATH_ADMIN . 'settings/class-wcvendors-pro-settings-ratings.php';

		return $settings;
	}

	/**
	 *    General settings
	 */
	public function general_settings( $settings ) {

		$general_settings = apply_filters( 'wcvendors_pro_general_settings', array() );

		// $settings = array_merge( $general_settings, $settings );
		return apply_filters( 'wcvendors_pro_get_settings_general', $settings );
	}

	/**
	 *    Display sections settings
	 */
	public function get_display_sections( $sections ) {
		$sections['pro_dashboard'] = __( 'Pro dashboard', 'wcvendors-pro' );
		$sections['branding']      = __( 'Branding', 'wcvendors-pro' );
		$sections['colors']        = __( 'Colors', 'wcvendors-pro' );
		$sections['notices']       = __( 'Notices', 'wcvendors-pro' );

		return apply_filters( 'wcvendors_pro_get_sections_display', $sections );
	}

	/**
	 *    Capabilities sections settings
	 */
	public function get_capabilities_sections( $sections ) {
		$sections['trash'] = __( 'Trash', 'wcvendors-pro' );

		return apply_filters( 'wcvendors_pro_get_sections_capabilities', $sections );
	}

	/**
	 *    General settings
	 */
	public function general( $settings ) {

		$new_settings = array();

		foreach ( $settings as $setting ) {

			// general
			if ( isset( $setting['id'] ) && 'general_options' == $setting['id'] && isset( $setting['type'] ) && 'sectionend' == $setting['type'] ) {

				// WordPress Dashboard access.
				$new_settings[] = array(
					'title'   => __( 'WordPress Dashboard', 'wcvendors-pro' ),
					/* translators: %s: vendor name */
					'desc'    => sprintf( __( 'Lock %1$s and pending %1$s out of the /wp-admin/ area.', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					/* translators: %s: vendor name */
					'tip'     => sprintf( __( 'Lock %s out of the /wp-admin/ area.', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_disable_wp_admin_vendors',
					'type'    => 'checkbox',
					'default' => false,
				);

				// Vendor Redirect.
				$new_settings[] = array(
					/* translators: %s: vendor name */
					'title'   => sprintf( __( '%s login redirect', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'desc'    => sprintf( __( 'Choose which page %s are redirected to after login. ', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_vendor_login_redirect',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select-nostd',
					'css'     => 'min-width:300px;',
					'options' => apply_filters(
						'wcvendors_vendor_login_redirect_args',
						array(
							'my-account' => __( 'My account', 'wcvendors-pro' ),
							'dashboard'  => __( 'Dashboard', 'wcvendors-pro' ),
						)
					),
					'default' => 'my-account',
				);

				// Registration Form Redirect.
				$new_settings[] = array(
					/* translators: %s: vendor name */
					'title'   => sprintf( __( '%s Registration Form', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					/* translators: %s: vendor name */
					'desc'    => sprintf( __( 'Redirect %s applications to the registration form', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_vendor_registration_form_redirect',
					'type'    => 'checkbox',
					'default' => 'no',
				);
			}

			$new_settings[] = $setting;

		}

		return apply_filters( 'wcvendors_pro_get_settings_general', $new_settings );
	}

	/**
	 * Add pro features to general capabilities tab
	 *
	 * @since 2.0.0
	 */
	public function capabilities( $settings ) {

		$new_settings = array();

		foreach ( $settings as $setting ) {

			// Pro Features
			if ( isset( $setting['id'] ) && 'permissions_orders_options' == $setting['id'] && isset( $setting['type'] ) && 'sectionend' == $setting['type'] ) {

				$new_settings[] = $setting;

				// Pro features
				$pro_features = apply_filters(
					'wcvendors_pro_settings_capabilities_general_features',
					array(
						// Shop Display Options
						array(
							'title' => __( 'Pro Features', 'wcvendors-pro' ),
							'type'  => 'title',
							'desc'  => sprintf( __( 'Enable and disable the pro features for the %s dashboard', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name() ) ),
							'id'    => 'pro_features_options',
						),

						array(
							'title'   => __( 'Product management', 'wcvendors-pro' ),
							'desc'    => __( 'Disable product management in pro dashboard. ', 'wcvendors-pro' ),
							'tip'     => __( 'Check to remove the product management from the pro dashboard.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_product_management_cap',
							'type'    => 'checkbox',
							'default' => 'no',
						),
						array(
							'title'   => __( 'Order management', 'wcvendors-pro' ),
							'desc'    => __( 'Disable order management in pro dashboard. ', 'wcvendors-pro' ),
							'tip'     => __( 'Check to remove the order management from the pro dashboard.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_order_management_cap',
							'type'    => 'checkbox',
							'default' => 'no',
						),
						array(
							'title'   => __( 'Coupon management', 'wcvendors-pro' ),
							'desc'    => __( 'Disable coupon management in pro dashboard. ', 'wcvendors-pro' ),
							'tip'     => __( 'Check to remove the coupon management from the pro dashboard.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_shop_coupon_management_cap',
							'type'    => 'checkbox',
							'default' => 'no',
						),
						array(
							'title'   => __( 'Settings management', 'wcvendors-pro' ),
							'desc'    => __( 'Disable store settings management in pro dashboard. ', 'wcvendors-pro' ),
							'tip'     => __( 'Check to remove the store settings management from the pro dashboard.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_settings_management_cap',
							'type'    => 'checkbox',
							'default' => 'no',
						),
						array(
							'title'   => __( 'Ratings', 'wcvendors-pro' ),
							'desc'    => __( 'Disable the ratings system completely. ', 'wcvendors-pro' ),
							'tip'     => __( 'Check to remove the ratings system from the front end completely.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_ratings_management_cap',
							'type'    => 'checkbox',
							'default' => 'no',
						),
						array(
							'title'   => sprintf( __( '%s shipping', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							'desc'    => sprintf( __( 'Disable the %s shipping system completely. ', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							'tip'     => sprintf( __( 'Check to remove the %s shipping system from the front end completely.', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							'id'      => 'wcvendors_shipping_management_cap',
							'type'    => 'checkbox',
							'default' => 'no',
						),

						array(
							'title'   => __( 'View store', 'wcvendors-pro' ),
							'desc'    => __( 'Disable the view store button on the pro dashboard. ', 'wcvendors-pro' ),
							'tip'     => __( 'Check to remove the view store button from the navigation.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_view_store_cap',
							'type'    => 'checkbox',
							'default' => 'no',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'pro_features_options',
						),
					)
				);

				$new_settings = array_merge( $new_settings, $pro_features );
			}

			// Pro Features
			if ( isset( $setting['id'] ) && 'order_view_options' == $setting['id'] && isset( $setting['type'] ) && 'sectionend' == $setting['type'] ) {

				$new_settings[] = $setting;

				// Pro features
				$pro_features = apply_filters(
					'wcvendors_pro_settings_capabilities_general_features',
					array(
						// Shop Display Options
						array(
							'title' => __( 'Order table', 'wcvendors-pro' ),
							'type'  => 'title',
							'desc'  => sprintf( __( 'Configure which actions to disable on the orders table for %s', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
							'id'    => 'order_table_options',
						),

						array(
							'title'   => __( 'Customer address', 'wcvendors-pro' ),
							'desc'    => __( 'Choose which customer address to show in the orders table ', 'wcvendors-pro' ),
							'id'      => 'wcvendors_order_customer_address',
							'type'    => 'select',
							'class'   => 'wc-enhanced-select-nostd',
							'css'     => 'min-width:300px;',
							'options' => apply_filters(
								'wcvendors_order_customer_address_args',
								array(
									'shipping' => __( 'Shipping address', 'wcvendors-pro' ),
									'billing'  => __( 'Billing address', 'wcvendors-pro' ),
								)
							),
							'default' => 'shipping',
						),

						array(
							'title'   => sprintf( __( '%s orders', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							'desc'    => __( 'Override shipping address with billing address if empty. Use customer billing address if customer shipping address is empty.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_orders_override_empty_shipping',
							'type'    => 'checkbox',
							'default' => 'no',
						),

						array(
							'title'   => __( 'Orders table actions', 'wcvendors-pro' ),
							'desc'    => __( 'View order details', 'wcvendors-pro' ),
							'tip'     => __( 'Hide the view details action from the orders table.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_hide_order_view_details',
							'type'    => 'checkbox',
							'default' => 'no',
						),

						array(
							'desc'    => __( 'Shipping label', 'wcvendors-pro' ),
							'tip'     => __( 'Hide the shipping label action from the orders table.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_hide_order_shipping_label',
							'type'    => 'checkbox',
							'default' => 'no',
						),

						array(
							'desc'    => __( 'Tracking number', 'wcvendors-pro' ),
							'tip'     => __( 'Hide the tracking number action from the orders table.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_hide_order_tracking_number',
							'type'    => 'checkbox',
							'default' => 'no',
						),

						array(
							'desc'    => __( 'Mark shipped', 'wcvendors-pro' ),
							'tip'     => __( 'Hide the mark shipped action from the orders table.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_hide_order_mark_shipped',
							'type'    => 'checkbox',
							'default' => 'no',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'order_table_options',
						),
					)
				);

				$new_settings = array_merge( $new_settings, $pro_features );
			}

			// Edit approved products
			if ( isset( $setting['id'] ) && 'permissions_products_options' == $setting['id'] && isset( $setting['type'] ) && 'sectionend' == $setting['type'] ) {

				$new_settings[] = array(
					'title'   => __( 'Edit approved products', 'wcvendors-pro' ),
					'desc'    => __( 'Publish edits to approved products. ( New products will still have to be approved )', 'wcvendors-pro' ),
					'tip'     => sprintf( __( 'Allow %s to edit products that have already been approved.', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_capability_products_approved',
					'type'    => 'checkbox',
					'default' => 'no',
				);

			}

			// Delete product
			if ( isset( $setting['id'] ) && 'wcvendors_capability_product_duplicate' == $setting['id'] ) {

				$new_settings[] = array(
					'title'   => __( 'Delete product', 'wcvendors-pro' ),
					'desc'    => __( 'Disable the delete option on the product form. ', 'wcvendors-pro' ),
					'tip'     => __( 'Check to remove the delete button from the product table.', 'wcvendors-pro' ),
					'id'      => 'wcvendors_capability_product_delete',
					'type'    => 'checkbox',
					'default' => 'no',
				);

			}

			$new_settings[] = $setting;
		}

		return $new_settings;
	}

	/**
	 *    Display settings
	 */
	public function display( $settings ) {

		$new_settings = array();

		foreach ( $settings as $setting ) {

			// Set the page type based on the version of WC Vendors currently running
			$page_type = version_compare( WCV_VERSION, '2.0.7', '<' ) ? 'single_select_page' : 'multi_select_page';

			// Pages
			if ( isset( $setting['id'] ) && 'page_options' == $setting['id'] && isset( $setting['type'] ) && 'sectionend' == $setting['type'] ) {

				$new_settings[] = array(
					'title'   => __( 'Pro Dashboard', 'wcvendors-pro' ),
					'id'      => 'wcvendors_dashboard_page_id',
					'type'    => $page_type,
					'default' => '',
					'class'   => 'wc-enhanced-select',
					'css'     => 'min-width:300px;',
					'desc'    => sprintf( __( '<br />The page to display the WC Vendors Pro dashboard. This page requires the <code>[wcv_pro_dashboard]</code> shortcode. <strong>This page should be separate to your %1$s dashboard page above. Do not delete your %1$s dashboard page.</strong>', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
				);
				$new_settings[] = array(
					'title'   => sprintf( __( '%s ratings', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'id'      => 'wcvendors_feedback_page_id',
					'type'    => 'single_select_page',
					'default' => '',
					'class'   => 'wc-enhanced-select-nostd',
					'css'     => 'min-width:300px;',
					'desc'    => sprintf( __( '<br />The page to display the feedback from this will have the <code>[wcv_feedback_form]</code> shortcode.', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
				);

			}

			// Shop Settings
			if ( isset( $setting['id'] ) && 'wcvendors_display_shop_description_html' == $setting['id'] ) {

				$new_settings[] = array(
					'title'   => __( 'Shop header', 'wcvendors-pro' ),
					'desc'    => __( 'Which shop header to use. Shop headers need to be enabled for this option to work.', 'wcvendors-pro' ),
					'id'      => 'wcvendors_vendor_store_header_type',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select-nostd',
					'css'     => 'min-width:300px;',
					'options' => array(
						'free'       => __( 'Free', 'wcvendors-pro' ),
						'pro'        => __( 'Pro', 'wcvendors-pro' ),
						'pro-modern' => __( 'Pro Modern', 'wcvendors-pro' ),
					),
					'default' => 'pro-modern',
				);

			}

			// Labels
			if ( isset( $setting['id'] ) && 'label_options' == $setting['id'] && isset( $setting['type'] ) && 'sectionend' == $setting['type'] ) {

				$new_settings[] = array(
					'title'   => sprintf( __( 'Verified %s Label', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'desc'    => sprintf( __( 'Text to output on the verified %s badge.', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'id'      => 'wcvendors_verified_vendor_label',
					'type'    => 'text',
					'default' => sprintf( __( 'Verified %s', 'wcvendors-pro' ), wcv_get_vendor_name() ),
				);

				$new_settings[] = array(
					'title'   => sprintf( __( '%s ratings label', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'desc'    => sprintf( __( 'The %s ratings tab title on the single product page.', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'id'      => 'wcvendors_vendor_ratings_label',
					'type'    => 'text',
					'default' => __( 'Product Ratings', 'wcvendors-pro' ),
				);

				$new_settings[] = array(
					'title'   => __( 'Show store total sales', 'wcvendors-pro' ),
					'desc'    => __( 'This will show total sales on store headers if enabled.', 'wcvendors-pro' ),
					'id'      => 'wcvendors_show_store_total_sales',
					'type'    => 'checkbox',
					'default' => 'yes',
				);

				$new_settings[] = array(
					'title'   => __( 'Show product total sales', 'wcvendors-pro' ),
					'desc'    => __( 'This will show number of items sold on product pages if enabled.', 'wcvendors-pro' ),
					'id'      => 'wcvendors_show_product_total_sales',
					'type'    => 'checkbox',
					'default' => 'yes',
				);

				$new_settings[] = array(
					'title'   => __( 'Store total sales label', 'wcvendors-pro' ),
					'desc'    => sprintf( __( 'The default label for %s store total sales display.', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'id'      => 'wcvendors_store_total_sales_label',
					'type'    => 'text',
					'default' => __( 'Total Sales:', 'wcvendors-pro' ),
				);

				$new_settings[] = array(
					'title'   => sprintf( __( 'Product total sales label', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'desc'    => sprintf( __( 'The default label for %s total sales display.', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'id'      => 'wcvendors_product_total_sales_label',
					'type'    => 'text',
					'default' => __( 'Units Sold:', 'wcvendors-pro' ),
				);

			}

			// advanced
			if ( isset( $setting['id'] ) && 'advanced_options' == $setting['id'] && isset( $setting['type'] ) && 'sectionend' == $setting['type'] ) {

				$new_settings[] = array(
					'title'   => __( 'Single Product Tools', 'wcvendors-pro' ),
					'desc'    => sprintf( __( 'Display product actions on the single product page for %s.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
					'tip'     => sprintf( __( 'Diplay the enabled actions for edit/duplicate/delete on the single product page to the %s.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
					'id'      => 'wcvendors_single_product_tools',
					'type'    => 'checkbox',
					'default' => 'no',
				);

				$new_settings[] = array(
					'title'   => __( 'Theme Support', 'wcvendors-pro' ),
					'desc'    => __( 'Load theme support to enhance certain themes.', 'wcvendors-pro' ),
					'id'      => 'wcvendors_load_theme_support',
					'type'    => 'checkbox',
					'default' => 'no',
				);

				$new_settings[] = array(
					'title'   => __( 'Vertical Navigation', 'wcvendors-pro' ),
					'desc'    => __( 'Use a vertical navigation', 'wcvendors-pro' ),
					'id'      => 'wcvendors_use_vertical_menu',
					'type'    => 'checkbox',
					'default' => 'no',
				);
				$new_settings[] = array(
					'title'   => sprintf( __( 'Header prority', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'desc'    => __( 'You can use this to change when the vendor store header loads. If you require the store header to be full width, make this number lower.', 'wcvendors-pro' ),
					'id'      => 'wcvendors_store_header_priority',
					'type'    => 'text',
					'default' => 30,
				);
				$new_settings[] = array(
					'title'   => sprintf( __( 'Single product header prority', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'desc'    => __( 'You can use this to change when the vendor store header load on the single product page. If you require the store header to be full width, make this number lower.', 'wcvendors-pro' ),
					'id'      => 'wcvendors_single_product_store_header_priority',
					'type'    => 'text',
					'default' => 30,
				);

			}

			$new_settings[] = $setting;

		}

		return apply_filters( 'wcvendors_pro_get_settings_display', $new_settings );
	}

	/**
	 *    Display extra sections
	 */
	public function display_sections( $settings, $current_section ) {

		if ( 'pro_dashboard' === $current_section ) {

			$settings = apply_filters(
				'wcvendors_pro_settings_display_prodashboard',
				array(
					// Branding display options
					array(
						'title' => __( '', 'wcvendors-pro' ),
						'type'  => 'title',
						'desc'  => sprintf( __( 'Display options for the pro dashboard', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name() ) ),
						'id'    => 'pro_dashboard_options',
					),

					array(
						'title'   => __( 'Dashboard Date Range', 'wcvendors-pro' ),
						'id'      => 'wcvendors_dashboard_date_range',
						'tip'     => __( 'Define the dashboard default date range.', 'wcvendors-pro' ),
						'options' => array(
							'annually'  => __( 'Annually', 'wcvendors-pro' ),
							'quarterly' => __( 'Quarterly', 'wcvendors-pro' ),
							'monthly'   => __( 'Monthly', 'wcvendors-pro' ),
							'weekly'    => __( 'Weekly', 'wcvendors-pro' ),
							'daily'     => __( 'Daily', 'wcvendors-pro' ),
						),
						'type'    => 'radio',
						'default' => 'monthly',
					),
					array(
						'title'   => __( 'Orders Page Ranges', 'wcvendors-pro' ),
						'id'      => 'wcvendors_orders_sales_range',
						'tip'     => __( 'Define the orders sales page date range.', 'wcvendors-pro' ),
						'options' => array(
							'annually'  => __( 'Annually', 'wcvendors-pro' ),
							'quarterly' => __( 'Quarterly', 'wcvendors-pro' ),
							'monthly'   => __( 'Monthly', 'wcvendors-pro' ),
							'weekly'    => __( 'Weekly', 'wcvendors-pro' ),
							'daily'     => __( 'Daily', 'wcvendors-pro' ),
						),
						'type'    => 'radio',
						'default' => 'monthly',
					),
					array(
						'title'   => __( 'View store link', 'wcvendors-pro' ),
						'desc'    => __( 'Open the view store link in a new window.', 'wcvendors-pro' ),
						'id'      => 'wcvendors_dashboard_view_store_new_window',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					array(
						'title'   => __( 'Show logout link', 'wcvendors-pro' ),
						'desc'    => __( 'Show a logout link on the pro dashboard navigation.', 'wcvendors-pro' ),
						'id'      => 'wcvendors_dashboard_show_logout',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					array(
						'title'   => __( 'View product link', 'wcvendors-pro' ),
						'desc'    => __( 'Open the view product link in a new window.', 'wcvendors-pro' ),
						'id'      => 'wcvendors_dashboard_view_product_new_window',
						'type'    => 'checkbox',
						'default' => 'yes',
					),
					array(
						'title'   => __( 'Show disk usage limit', 'wcvendors-pro' ),
						'desc'    => __( 'Show a disk usage count on the dashboard', 'wcvendors-pro' ),
						'id'      => 'wcvendors_dashboard_show_disk_usage',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					array(
						'title'   => __( 'Show file usage limit', 'wcvendors-pro' ),
						'desc'    => __( 'Show a file usage count on the dashboard', 'wcvendors-pro' ),
						'id'      => 'wcvendors_dashboard_show_files_usage',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					array(
						'title'   => __( 'Products per page', 'wcvendors-pro' ),
						'desc'    => __( 'How many products to display per page', 'wcvendors-pro' ),
						'id'      => 'wcvendors_products_per_page',
						'type'    => 'number',
						'default' => 20,
					),
					array(
						'title'   => __( 'Coupons per page', 'wcvendors-pro' ),
						'desc'    => __( 'How many coupons to display per page', 'wcvendors-pro' ),
						'id'      => 'wcvendors_coupons_per_page',
						'type'    => 'number',
						'default' => 20,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'pro_dashboard_options',
					),
				)
			);

			return $settings;

		} elseif ( 'branding' === $current_section ) {

			$settings = apply_filters(
				'wcvendors_pro_settings_display_branding',
				array(
					// Branding display options
					array(
						'title' => __( 'Default Branding', 'wcvendors-pro' ),
						'type'  => 'title',
						'desc'  => sprintf( __( 'Default branding for the %s shop', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name() ) ),
						'id'    => 'branding_options',
					),
					array(
						'title'   => __( 'Default Store Banner', 'wcvendors-pro' ),
						'desc'    => __( 'Select an image for the default shop header banner', 'wcvendors-pro' ),
						'id'      => 'wcvendors_default_store_banner_src',
						'type'    => 'image',
						'css'     => 'wcv-img-id button',
						'default' => plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/images/wcvendors_default_banner.jpg',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'branding_options',
					),
				)
			);

			return $settings;

		} elseif ( 'colors' === $current_section ) {

			$opacity_numbers = array(
				'0.0' => '0.0',
				'0.1' => '0.1',
				'0.2' => '0.2',
				'0.3' => '0.3',
				'0.4' => '0.4',
				'0.5' => '0.5',
				'0.6' => '0.6',
				'0.7' => '0.7',
				'0.8' => '0.8',
				'0.9' => '0.9',
				'1'   => '1',
			);

			$settings = apply_filters(
				'wcvendors_pro_settings_chart_colors',
				array(
					// chart colors
					array(
						'title' => __( 'Order Totals Chart Colors', 'wcvendors-pro' ),
						'type'  => 'title',
						'desc'  => __( 'Customize the colors used for the charts', 'wcvendors-pro' ),
						'id'    => 'bar_chart_color_options',
					),
					array(
						'title'   => __( 'Use Random Colors', 'wcvendors-pro' ),
						'id'      => 'wcv_order_totals_chart_use_random_colors',
						'type'    => 'checkbox',
						'default' => 'no',
					),
					array(
						'title'   => __( 'Fill Color', 'wcvendors-pro' ),
						'id'      => 'wcv_order_totals_chart_fill_color',
						'type'    => 'color',
						'default' => '#005580',
					),
					array(
						'title'   => __( 'Fill Opacity', 'wcvendors-pro' ),
						'desc'    => __( 'How opaque you want this color to be. O is the most opaque 1 is solid.', 'wcvendors-pro' ),
						'id'      => 'wcv_order_totals_chart_fill_opacity',
						'type'    => 'select',
						'class'   => 'wc-enhanced-select-nostd',
						'css'     => 'min-width: 200px;',
						'options' => $opacity_numbers,
						'default' => '1',
					),
					array(
						'title'   => __( 'Stroke Color', 'wcvendors-pro' ),
						'id'      => 'wcv_order_totals_chart_stroke_color',
						'type'    => 'color',
						'default' => '#ffffff',
					),
					array(
						'title'   => __( 'Stroke Opacity', 'wcvendors-pro' ),
						'desc'    => __( 'How opaque you want this color to be. O is the most opaque 1 is solid.', 'wcvendors-pro' ),
						'id'      => 'wcv_order_totals_chart_stroke_opacity',
						'type'    => 'select',
						'css'     => 'min-width: 200px;',
						'class'   => 'wc-enhanced-select-nostd',
						'options' => $opacity_numbers,
						'default' => '0.5',
					),
					array(
						'title'   => __( 'Hover Fill Color', 'wcvendors-pro' ),
						'id'      => 'wcv_order_totals_chart_hover_fill_color',
						'type'    => 'color',
						'default' => '#5897b6',
					),
					array(
						'title'   => __( 'Hover Fill Opacity', 'wcvendors-pro' ),
						'desc'    => __( 'How opaque you want this color to be. O is the most opaque 1 is solid.', 'wcvendors-pro' ),
						'id'      => 'wcv_order_totals_chart_hover_fill_opacity',
						'type'    => 'select',
						'css'     => 'min-width: 200px;',
						'class'   => 'wc-enhanced-select-nostd',
						'options' => $opacity_numbers,
						'default' => '0.5',
					),
					array(
						'title'   => __( 'Hover Stroke Color', 'wcvendors-pro' ),
						'id'      => 'wcv_order_totals_chart_hover_stroke_color',
						'type'    => 'color',
						'default' => '#ffffff',
					),
					array(
						'title'   => __( 'Hover Stroke Opacity', 'wcvendors-pro' ),
						'desc'    => __( 'How opaque you want this color to be. O is the most opaque 1 is solid.', 'wcvendors-pro' ),
						'id'      => 'wcv_order_totals_chart_fill_color_opacity',
						'type'    => 'select',
						'css'     => 'min-width: 200px;',
						'class'   => 'wc-enhanced-select-nostd',
						'options' => $opacity_numbers,
						'default' => '0.5',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'pie_chart_colors_options',
					),

					array(
						'title' => __( 'Product Totals Chart Colors', 'wcvendors-pro' ),
						'type'  => 'title',
						'desc'  => __( 'Choose how the colors for the product totals pie chart are generated. You may choose a single color that will show random shades of this color or all colors can be completely random.', 'wcvendors-pro' ),
						'id'    => 'product_totals_chart_color_options',
					),

					array(
						'title'   => __( 'Random Colors', 'wcvendors-pro' ),
						'id'      => 'wcv_product_totals_chart_use_random_colors',
						'desc'    => __( 'Completely random colors.', 'wcvendors-pro' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),

					array(
						'title'   => __( 'Shade Fill Color', 'wcvendors-pro' ),
						'id'      => 'wcv_product_totals_chart_base_fill_color',
						'desc'    => __( '<br>The chart will be various shades of this color', 'wcvendors-pro' ),
						'type'    => 'color',
						'default' => '#005580',
					),
					array(
						'title'   => __( 'Shade Hover Color', 'wcvendors-pro' ),
						'id'      => 'wcv_product_totals_chart_base_hover_color',
						'desc'    => __( '<br>The chart hover will be various shades of this color', 'wcvendors-pro' ),
						'type'    => 'color',
						'default' => '#5897b6',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'product_totals_chart_colors_options',
					),

				)
			);

			return $settings;

		} elseif ( 'notices' === $current_section ) {

			$settings = apply_filters(
				'wcvendors_pro_settings_display_branding',
				array(
					// vendor notices
					array(
						'title' => __( 'Notices', 'wcvendors-pro' ),
						'type'  => 'title',
						'desc'  => sprintf( __( 'Display notices to the %s', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name() ) ),
						'id'    => 'notices_options',
					),
					array(
						'title' => sprintf( __( '%s Dashboard Notice', 'wcvendors-pro' ), wcv_get_vendor_name() ),
						'desc'  => sprintf( __( 'Display a message to %s on all dashboard pages below the dashboard menu.', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name() ) ),
						'id'    => 'wcvendors_vendor_dashboard_notice',
						'css'   => 'width: 700px;min-height:100px',
						'type'  => 'textarea',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'notices_options',
					),

					// Signup notices
					array(
						'title' => __( 'Signup Notices', 'wcvendors-pro' ),
						'type'  => 'title',
						'desc'  => sprintf( __( 'These options allow you to provide messages to %s signing up to your market place.', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name() ) ),
						'id'    => 'signup_notices_options',
					),
					array(
						'title' => sprintf( __( '%s signup notice', 'wcvendors-pro' ), wcv_get_vendor_name() ),
						'desc'  => sprintf( __( 'Display a message to %s on signup page, this could include store specific instructions.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
						'id'    => 'wcvendors_vendor_signup_notice',
						'type'  => 'wysiwyg',
					),
					array(
						'title'   => sprintf( __( 'Pending %s message', 'wcvendors-pro' ), wcv_get_vendor_name() ),
						'desc'    => sprintf( __( 'Display a message to pending %s after they have applied.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
						'id'      => 'wcvendors_vendor_pending_notice',
						'css'     => 'width: 700px;min-height:100px',
						'type'    => 'textarea',
						'default' => self::get_default_pending_vendor_notice(),
					),
					array(
						'title'   => sprintf( __( 'Approved %s message', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
						'desc'    => sprintf( __( 'Display a message on the dashboard for approved %s.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
						'id'      => 'wcvendors_vendor_approved_notice',
						'css'     => 'width: 700px;min-height:100px',
						'type'    => 'textarea',
						'default' => sprintf( __( 'Congratulations! You are now a %s. Be sure to configure your store settings before adding products.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
					),

					array(
						'type' => 'sectionend',
						'id'   => 'signup_notices_options',
					),

				)
			);

			return $settings;

		} else {
			return $settings;
		}

	}

	/**
	 *    Capabilities trash settings
	 */
	public function capabilities_sections( $settings, $current_section ) {

		if ( 'trash' === $current_section ) {

			$settings = apply_filters(
				'wcvendors_pro_settings_capabilities_trash',
				array(
					// Trash options
					array(
						'title' => __( '', 'wcvendors-pro' ),
						'type'  => 'title',
						'desc'  => sprintf( __( 'Default behaviour when a %s deletes something', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name() ) ),
						'id'    => 'trash_options',
					),
					array(
						'title'   => __( 'Product Delete', 'wcvendors-pro' ),
						'desc'    => sprintf( __( 'Delete %s products permanently. ', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
						'tip'     => sprintf( __( 'Bypass the trash when a %s deletes a product and delete permanently.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
						'id'      => 'wcvendors_vendor_product_trash',
						'type'    => 'checkbox',
						'default' => 'no',
					),

					array(
						'title'   => __( 'Coupon Delete', 'wcvendors-pro' ),
						'desc'    => sprintf( __( 'Delete %s coupons permanently. ', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
						'tip'     => sprintf( __( 'Bypass the trash when a %s deletes a coupon and delete permanently.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
						'id'      => 'wcvendors_vendor_coupon_trash',
						'type'    => 'checkbox',
						'default' => 'no',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'trash_options',
					),

				)
			);

			return $settings;

		} else {
			return $settings;
		}

	}

	/**
	 *    Commission settings
	 */
	public function commission( $settings ) {

		$new_settings = array();

		foreach ( $settings as $setting ) {

			if ( isset( $setting['id'] ) && 'wcvendors_vendor_commission_rate' == $setting['id'] ) {

				$new_settings[] = array(
					'title'   => __( 'Global Commission Type', 'wcvendors-pro' ),
					'desc'    => sprintf( __( 'This is the default commission type for all %s.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
					'id'      => 'wcvendors_commission_type',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select-nostd',
					'css'     => 'min-width:300px;',
					'options' => apply_filters( 'wcvendors_global_commission_types', WCVendors_Pro_Commission_Controller::commission_types() ),
					'default' => 'percent',
				);

			}

			// commission
			if ( isset( $setting['id'] ) && 'commission_options' == $setting['id'] && isset( $setting['type'] ) && 'sectionend' == $setting['type'] ) {

				$new_settings[] = array(
					'title'   => __( 'Coupon Action', 'wcvendors-pro' ),
					'desc'    => __( 'Process the commission before or after the coupon has been applied to the price.', 'wcvendors-pro' ),
					'id'      => 'wcvendors_commission_coupon_action',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select-nostd',
					'css'     => 'min-width:300px;',
					'options' => array(
						'yes' => __( 'After', 'wcvendors-pro' ),
						'no'  => __( 'Before', 'wcvendors-pro' ),
					),
					'default' => 'yes',
				);

				$new_settings[] = array(
					'title' => __( 'Commission amount', 'wcvendors-pro' ),
					'desc'  => sprintf( __( 'The fixed amount of commission you give the %s.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
					'id'    => 'wcvendors_commission_amount',
					'type'  => 'number',
				);

				$new_settings[] = array(
					'title' => __( 'Commission fee', 'wcvendors-pro' ),
					'desc'  => __( 'This is the fee deducted from the commission amount.', 'wcvendors-pro' ),
					'id'    => 'wcvendors_commission_fee',
					'type'  => 'text',
				);

				$new_settings[] = array(
					'title'       => sprintf( __( '%s Sales', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'desc'        => sprintf( __( 'Commissions based on %s sales', 'wcvendors-pro' ), wcv_get_vendor_name() ),
					'id'          => 'wcvendors_commission_tier_vendor_sales',
					'key'         => 'vendor_sales',
					'value_label' => __( 'Sales', 'wcvendors-pro' ),
					'type'        => 'include',
					'file'        => WCV_PRO_ABSPATH_ADMIN . 'settings/partials/tiered-commissions.php',
				);

				$new_settings[] = array(
					'title'       => __( 'Product Sales', 'wcvendors-pro' ),
					'desc'        => __( 'Commissions will be applied based on product sales.' ),
					'id'          => 'wcvendors_commission_tier_product_sales',
					'type'        => 'include',
					'key'         => 'product_sales',
					'value_label' => __( 'Sales', 'wcvemdors-pro' ),
					'file'        => WCV_PRO_ABSPATH_ADMIN . 'settings/partials/tiered-commissions.php',
				);

				$new_settings[] = array(
					'title'       => __( 'Product Price', 'wcvendors-pro' ),
					'desc'        => __( 'Define the commission tiers based on the product price.', 'wcvendors-pro' ),
					'id'          => 'wcvendors_commission_tier_product_price',
					'type'        => 'include',
					'key'         => 'product_price',
					'value_label' => __( 'Price', 'wcvendors-pro' ),
					'file'        => WCV_PRO_ABSPATH_ADMIN . 'settings/partials/tiered-commissions.php',
				);

			}

			$new_settings[] = $setting;

		}

		return apply_filters( 'wcvendors_pro_get_settings_commission', $new_settings );
	}

	/**
	 * Get the default pending vendor notice
	 *
	 * @return void
	 * @version 1.7.5
	 * @since   1.7.5
	 */
	public static function get_default_pending_vendor_notice() {
		return __( 'Your application has been received. You will be notified by email the results of your application.', 'wcvendors-pro' );
	}

	/**
	 * Add new commission types
	 *
	 * @param   array $types
	 * @return  array $types
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function add_commission_types( $types ) {
		$new_types = array(
			'vendor_sales'  => sprintf( __( 'Sales by %s', 'wcvendors-pro' ), wcv_get_vendor_name() ),
			'product_sales' => __( 'Sales by Product', 'wcvendors-pro' ),
			'product_price' => __( 'Product Price', 'wcvendors-pro' ),
		);

		return array_merge( $types, $new_types );
	}

	/**
	 * @param $types
	 *
	 * @return array
	 */
	public function add_product_commission_types( $types ) {
		return array_merge( $types, array( 'product_sales' => __( 'Total sales', 'wcvendors-pro' ) ) );
  }

	/**
	 * Add global comission tiers
	 *
	 * @param   array $field_details
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function include_commission_tiers( $field_details ) {

		$commission_tiers = WCVendors_Pro_Commission_Controller::get_commission_tiers( 'global', $field_details['key'] );

		?>
	 <tr valign="top" class="wcv_form_fields_table" id="<?php echo $field_details['id']; ?>">
	  <td colspan="2">
				<?php include $field_details['file']; ?>
	  </td>
	 </tr>
		<?php
	}

	/**
	 * Add banner reset button.
	 *
	 * @param  array $field The properties of the field to be rendered.
	 * @return void
	 * @version 1.7.3
	 * @since   1.7.3
	 */
	public function add_banner_reset_button( $field ) {
		$default_banner_url = plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/images/wcvendors_default_banner.jpg';
		?>
		<input
			type="button"
			id="wcv-banner-reset-<?php echo esc_attr( $field['id'] ); ?>"
			value="<?php esc_attr_e( 'Reset to default', 'wcvendors-pro' ); ?>"
			class="wcv-reset-store-banner button"
			data-default-url="<?php echo esc_attr( $default_banner_url ); ?>"
			data-field-id="<?php echo esc_attr( $field['id'] ); ?>"
			/>
		<?php
	}
}

return new WCVendors_Pro_Admin_Settings();
