<?php
/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin
 * @author     Jamie Madden <support@wcvendors.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for admin functionality
 *
 * @version 1.7.3
 * @since   1.0.0
 */
class WCVendors_Pro_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access  private
	 * @var  string $wcvendors_pro The ID of this plugin.
	 */
	private $wcvendors_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Script suffix for debugging
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $suffix script suffix for including minified file versions
	 */
	private $suffix;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool $debug plugin is in debug mode
	 */
	private $debug;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param    string $wcvendors_pro The name of this plugin.
	 * @param    string $version       The version of this plugin.
	 * @param    bool   $debug         Is the plugin in debug mode.
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {

		$this->wcvendors_pro   = $wcvendors_pro;
		$this->version         = $version;
		$this->debug           = $debug;
		$this->base_dir        = plugin_dir_url( __FILE__ );
		$this->plugin_base_dir = plugin_dir_path( dirname( __FILE__ ) );
		$this->suffix          = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || $this->debug ? '' : '.min';
	}

	/**
	 * Initialize the process of submit.
	 */
	public function process_submit() {

		if ( isset( $_GET['wcv_export_commissions'] ) ) {
			$this->export_csv();
		}
	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$screen    = get_current_screen();
		$vendor_id = get_current_user_id();
		$screen_id = $screen->id;
		$product   = 0;

		if ( 'user-edit' == $screen->id || 'woocommerce_page_wc-settings' == $screen->id ) {
			global $user_id;
			$vendor_id = $user_id;
			// SVG Icon Styles.
			wp_enqueue_style(
				'wcv-icons',
				WCV_PRO_PUBLIC_ASSETS_URL . 'css/wcv-icons' . $this->suffix . '.css',
				array(),
				$this->version,
				'all'
			);
		} elseif ( 'product' == $screen->id ) {
			global $post;
			$product = $post;
		}

		wp_enqueue_script( 'postbox' );
		wp_enqueue_media();

		$shipping_settings   = get_option( 'woocommerce_wcv_pro_vendor_shipping_settings', wcv_get_default_vendor_shipping() );
		$store_shipping_type = get_user_meta( $vendor_id, '_wcv_shipping_type', true );
		$shipping_type       = ( '' != $store_shipping_type ) ? $store_shipping_type : $shipping_settings['shipping_system'];

		// Variables to pass to javascript in admin.
		$admin_args = array(
			'screen_id'             => $screen_id,
			'product'               => $product,
			'vendor_shipping_type'  => $store_shipping_type,
			'global_shipping_type'  => $shipping_settings['shipping_system'],
			'current_shipping_type' => $shipping_type,
		);

		wp_register_script(
			'wcv-admin-js',
			$this->base_dir . 'assets/js/wcvendors-pro-admin' . $this->suffix . '.js',
			array(
				'jquery',
				'wp-color-picker',
				'selectWoo',
			),
			WCV_PRO_VERSION,
			true
		);
		wp_localize_script( 'wcv-admin-js', 'wcv_admin', $admin_args );
		wp_enqueue_script( 'wcv-admin-js' );

		// Admin style.
		wp_enqueue_style( 'wcv-admin-css', $this->base_dir . 'assets/css/wcvendors-pro-admin' . $this->suffix . '.css', array(), WCV_PRO_VERSION, 'all' );
		wp_enqueue_style( 'wp-color-picker' );

		if ( 'user-edit' == $screen->id || 'product' == $screen->id || 'woocommerce_page_wc-settings' == $screen->id ) {
			// Country select.
			$country_select_args = array(
				'countries'                 => json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
				'i18n_select_state_text'    => esc_attr__( 'Select an option&hellip;', 'wcvendors-pro' ),
				'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'wcvendors-pro' ),
				'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'wcvendors-pro' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'wcvendors-pro' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'wcvendors-pro' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'wcvendors-pro' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'wcvendors-pro' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'wcvendors-pro' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'wcvendors-pro' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'wcvendors-pro' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'wcvendors-pro' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'wcvendors-pro' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'wcvendors-pro' ),
			);

			wp_register_script( 'wcv-country-select', $this->base_dir . '../includes/assets/js/country-select' . $this->suffix . '.js', array( 'jquery', 'selectWoo' ), WCV_PRO_VERSION, true );
			wp_localize_script( 'wcv-country-select', 'wcv_country_select_params', $country_select_args );
			wp_enqueue_script( 'wcv-country-select' );
		}

		if ( 'wc-vendors_page_wcv-settings' == $screen->id || 'product' == $screen->id || 'user-edit' == $screen->id ) {
			wp_enqueue_style( 'parsley-stype', WCV_PRO_PUBLIC_ASSETS_URL . 'lib/parsley/parsley' . $this->suffix . '.css', array(), WCV_PRO_VERSION );
			wp_enqueue_script( 'parsley-js', WCV_PRO_PUBLIC_ASSETS_URL . 'lib/parsley/parsley' . $this->suffix . '.js', array(), WCV_PRO_VERSION );

			wp_register_script( 'wcv-tiered-commissions', $this->base_dir . '../includes/assets/js/tiered-commissions' . $this->suffix . '.js', array( 'jquery' ), WCV_PRO_VERSION, true );
			wp_enqueue_script( 'wcv-tiered-commissions' );

			$types_options = '';
			$rules_options = '';
			foreach ( WCVendors_Pro_Commission_Controller::commission_types() as $option => $option_name ) {
				$types_options .= '<option value="' . $option . '">' . $option_name . '</option>';
			}

			foreach ( WCVendors_Pro_Commission_Controller::commission_rules() as $rule => $label ) {
				$rules_options .= '<option value="' . $rule . '">' . $label . '</option>';
			}

			wp_localize_script(
				'wcv-tiered-commissions',
				'commission_data',
				array(
					'types_options'       => $types_options,
					'rules_options'       => $rules_options,
					'name_placeholder'    => __( 'Name/Description', 'wcvendors-pro' ),
					'value_placeholder'   => __( 'Value', 'wcvendors-pro' ),
					'percent_placeholder' => __( 'Percent', 'wcvendors-pro' ),
					'amount_placeholder'  => __( 'Amount', 'wcvendors-pro' ),
					'fee_placeholder'     => __( 'Fee', 'wcvendors-pro' ),
					'assets_url'          => WCV_PRO_PUBLIC_ASSETS_URL,
					'confirm_remove'      => __( 'Are you sure you want to remove this row?', 'wcvendors-pro' ),
					'possible_duplicate'  => __( 'The values and rules of this rows should not be the same.', 'wcvendors-pro' ),
					'cant_be_zero'        => __( 'Should not be zero', 'wcvendors-pro' ),
					'conflicting_row'     => __( 'There is a conflict. These values cannot be equal.', 'wcvendors-pro' ),
					'cant_be_empty'       => __( 'Commission table can not be empty', 'wcvendors-pro' ),
				)
			);
		}
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 * @return   array  Action links
	 * @param    string $links link.
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=wcv-settings' ) . '">' . __( 'Settings', 'wcvendors-pro' ) . '</a>',
			),
			$links
		);

	} // add_action_links()

	/**
	 * Lock a vendor out of the wp-admin
	 *
	 * @since    1.0.0
	 * @version  1.4.0
	 */
	public function admin_lockout() {

		if ( 'yes' == get_option( 'wcvendors_disable_wp_admin_vendors', 'no' ) ) {

			// Need to make this filterable somehow.
			$capabilities = array( 'vendor', 'pending_vendor' );

			foreach ( $capabilities as $capability ) {

				if ( current_user_can( $capability ) && ! defined( 'DOING_AJAX' ) ) {
					add_action( 'admin_init', array( $this, 'admin_redirect' ) );
				} else {
					return;
				}
			}
		}

	} // admin_lockout()

	/**
	 * Redirect to pro dashboard if attempting to access WordPress dashboard
	 *
	 * @since    1.0.0
	 */
	public function admin_redirect() {

		$redirect_page = apply_filters( 'wcv_admin_lockout_redirect_url', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
		wp_redirect( $redirect_page );

	} //admin_redirect()

	/**
	 * Output system status information for pro
	 *
	 * @since    1.0.3
	 */
	public function wcvendors_pro_system_status() {

		$free_dashboard_page   = get_option( 'wcvendors_vendor_dashboard_page_id', null );
		$pro_dashboard_pages   = (array) get_option( 'wcvendors_dashboard_page_id', array() );
		$feedback_form_page    = get_option( 'wcvendors_feedback_page_id', null );
		$vendor_shop_permalink = get_option( 'wcvendors_vendor_shop_permalink', '' );

		$woocommerce_override = locate_template( 'woocommerce.php' );

		include_once apply_filters( 'wcv_wcvendors_pro_system_status_path', 'partials/wcvendors-pro-system-status.php' );

	} // wcvendors_pro_system_status()

	/**
	 * Template for system status information for pro
	 *
	 * @since    1.0.3
	 */
	public function wcvendors_pro_template_status() {

		include_once apply_filters( 'wcvendors_pro_template_status', 'partials/wcvendors-pro-template-status.php' );

	} // wcvendors_pro_template_status()

	/**
	 * Load the new wc vendors shipping module
	 *
	 * @since    1.1.0
	 */
	public function wcvendors_pro_shipping_init() {

		if ( ! class_exists( 'WCVendors_Pro_Shipping_Method' ) ) {
			include 'class-wcvendors-pro-shipping.php';
		}

	} // wcvendors_pro_shipping_init()

	/**
	 * Add the new wc vendors shipping module
	 *
	 * @since    1.1.0
	 *
	 * @param    array $methods The shipping methods array.
	 *
	 * @return   array    $methods        The updated shipping methods array.
	 */
	public function wcvendors_pro_shipping_method( $methods ) {

		$methods['wcv_pro_vendor_shipping'] = 'WCVendors_Pro_Shipping_Method';

		return $methods;

	}

	/**
	 * WooCommerce Tools for Pro this will allow admins to import commission overrides from free.
	 *
	 * @since  1.3.6
	 * @access public
	 * @param  array $tools WooCommerce Tools for Pro this will allow admins to import commission overrides from free.
	 */
	public function wc_pro_tools( $tools ) {

		$tools['import_vendor_commissions'] = array(
			/* translators: %s: vendor name */
			'name'     => sprintf( __( 'Import %s Commission Overrides', 'wcvendors-pro' ), wcv_get_vendor_name( true, true ) ),
			/* translators: %s: vendor name */
			'button'   => sprintf( __( 'Import %s commission overrides', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
			/* translators: %s: vendor name */
			'desc'     => sprintf( __( 'This will import all the commission overrides for %s.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
			'callback' => array( 'WCVendors_Pro_Commission_Controller', 'import_vendor_commission_overrides' ),
		);

		$tools['import_product_commissions'] = array(
			'name'     => __( 'Import Product Commission Overrides', 'wcvendors-pro' ),
			'button'   => __( 'Import product commission overrides', 'wcvendors-pro' ),
			'desc'     => __( 'This will import all the commission overrides for products.', 'wcvendors-pro' ),
			'callback' => array( 'WCVendors_Pro_Commission_Controller', 'import_product_commission_overrides' ),
		);

		$tools['check_vendor_limits'] = array(
			/* translators: %s: vendor name */
			'name'     => sprintf( __( 'Check %s Upload Limits', 'wcvendors-pro' ), wcv_get_vendor_name( true, true ) ),
			/* translators: %s: vendor name */
			'button'   => sprintf( __( 'Check %s Upload Limits', 'wcvendors-pro' ), wcv_get_vendor_name( true, true ) ),
			/* translators: %1$ss: vendor name, %2$ss: vendor name */
			'desc'     => sprintf( __( 'This will check if the %1$ss have reached their upload limits. A notice will be displayed to %2$ss that have reached the limits.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ), wcv_get_vendor_name( false, false ) ),
			'callback' => array( __CLASS__, 'check_vendor_upload_limits' ),
		);

		return $tools;

	} // wc_pro_tools()

	/**
	 * Check vendor upload limits
	 *
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public static function check_vendor_upload_limits() {
		$vendors = get_users(
			array(
				'role'   => 'vendor',
				'fields' => array( 'ID' ),
			)
		);

		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}

		foreach ( $vendors as $vendor ) {
			$limits_controller = WCVendors_Pro_Upload_Limits::get_instance( $vendor->ID );

			$files_limited = $limits_controller->files_limit_reached() ? 1 : 0;
			$disk_limited  = $limits_controller->disk_limit_reached() ? 1 : 0;

			update_user_meta( $limits_controller->get_user_id(), '_wcv_vendor_file_count_limit_reached', $files_limited );
			update_user_meta( $limits_controller->get_user_id(), '_wcv_vendor_disk_usage_limit_reached', $disk_limited );
		}
	}

	/**
	 * Register front end widgets
	 *
	 * @since 1.4.4
	 */
	public function register_widgets() {

		include_once dirname( __FILE__ ) . '/widgets/class-wcvendors-pro-widget-store-search.php';
		include_once dirname( __FILE__ ) . '/widgets/class-wcvendors-pro-widget-store-categories.php';
		include_once dirname( __FILE__ ) . '/widgets/class-wcvendors-pro-widget-store-short-description.php';
		include_once dirname( __FILE__ ) . '/widgets/class-wcvendors-pro-widget-store-address-and-map.php';
		include_once dirname( __FILE__ ) . '/widgets/class-wcvendors-pro-widget-store-social-media.php';
		include_once dirname( __FILE__ ) . '/widgets/class-wcvendors-pro-widget-store-contact.php';
		include_once dirname( __FILE__ ) . '/widgets/class-wcvendors-pro-widget-store-ratings.php';
		include_once dirname( __FILE__ ) . '/widgets/class-wcvendors-pro-widget-vendor-search.php';
		include_once dirname( __FILE__ ) . '/widgets/class-wcvendors-pro-widget-store-recent-products.php';
		include_once dirname( __FILE__ ) . '/widgets/class-wcvendors-pro-widget-store-total-sales.php';

		register_widget( 'WCV_Widget_Store_Search' );
		register_widget( 'WCV_Widget_Store_Categories' );
		register_widget( 'WCV_Widget_Store_Address_And_Map' );
		register_widget( 'WCV_Widget_Store_Short_Description' );
		register_widget( 'WCV_Widget_Store_Contact' );
		register_widget( 'WCV_Widget_Store_Social_Media' );
		register_widget( 'WCV_Widget_Store_Ratings' );
		register_widget( 'WCV_Widget_Vendor_Search' );
		register_widget( 'WCV_Widget_Recent_Products' );
		register_widget( 'WCV_Widget_Store_Total_Sales' );

	} // register_widgets

	/**
	 * Add a product edit template meta box to the product admin edit screen
	 *
	 * @since  1.4.4
	 * @access public
	 * @param  string $post_type post type.
	 */
	public function add_template_meta_box( $post_type ) {

		add_meta_box(
			'wcv-wpsls-template-meta-box',
			__( 'Product Form Template', 'wc-vendors-wpsls' ),
			array(
				$this,
				'load_template_metabox',
			),
			'product',
			'side',
			'default',
			null
		);

	} // add_template_meta_box

	/**
	 * Add the content for the product template metabox on product admin edit screen
	 *
	 * @since  1.4.4
	 * @access public
	 */
	public function load_template_metabox() {

		wp_nonce_field( basename( __FILE__ ), 'product-template-mb-nonce' );

		woocommerce_wp_select(
			array(
				'id'      => '_wcv_product_form_template',
				'label'   => __( 'Template name', 'wpsls' ),
				'options' => wcv_get_product_templates(),
			)
		);

	} // load_template_metabox()

	/**
	 * Save template product mata data
	 *
	 * @since  1.4.4
	 * @access public
	 * @param  int $post_id post id.
	 */
	public function save_template_product_meta( $post_id ) {

		if ( ! empty( $_POST['_wcv_product_form_template'] ) ) {
			update_post_meta( $post_id, '_wcv_product_form_template', esc_attr( $_POST['_wcv_product_form_template'] ) );
		} else {
			update_post_meta( $post_id, '_wcv_product_form_template', '' );
		}

	} // save_template_product_meta()

	/**
	 * Hook into the query args for the drop down on the coupon screen.
	 *
	 * @version 1.1.18
	 * @since   1.4.5
	 * @access public
	 */
	public function vendor_dropdown_users( $output ) {
		global $post;
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( 'shop_coupon' !== $screen_id ) {
			return $output;
		}

		$vendor_args = array(
			'who'         => '',
			'role__in'    => array( 'vendor', 'administrator' ),
			'placeholder' => __( 'None', 'wcvendors-pro' ),
			'selected'    => $post->post_author,
			'id'          => 'post_author',
		);

		if ( version_compare( WCV_VERSION, '2.1.17', '>' ) ) {
			return WCV_Product_Meta::vendor_selectbox( $vendor_args, false );
		} else {
			return ( new WCV_Product_Meta() )->vendor_selectbox( $vendor_args, false );
		}
	}

	/**
	 * Product category form fields
	 *
	 * @param   WP_Term $category category object.
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function product_cat_edit_form_fields( $category ) {

		if ( is_object( $category ) ) {
			$category_id        = $category->term_id;
			$commission_type    = get_term_meta( $category_id, '_wcv_commission_type', true );
			$commission_amount  = get_term_meta( $category_id, '_wcv_commission_amount', true );
			$commission_fee     = get_term_meta( $category_id, '_wcv_commission_fee', true );
			$commission_percent = get_term_meta( $category_id, '_wcv_commission_percent', true );

			$is_new_category = false;
		} else {
			$commission_type    = '';
			$commission_percent = '';
			$commission_amount  = '';
			$commission_fee     = '';

			$is_new_category = true;
		}

		require_once WCV_PRO_ABSPATH_ADMIN . 'partials/vendor/wcvendors-pro-vendor-commission-fields.php';
	} // product_cat_edit_form_fields()

	/**
	 * Save category commissions
	 *
	 * @param   int $category_id category id.
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function save_category_commissions( $category_id ) {

		if ( ! is_numeric( $category_id ) ) {
			return;
		}

		$commission_type    = update_term_meta( $category_id, '_wcv_commission_type', wp_unslash( $_POST['_wcv_commission_type'] ) );
		$commission_amount  = update_term_meta( $category_id, '_wcv_commission_amount', wp_unslash( $_POST['_wcv_commission_amount'] ) );
		$commission_fee     = update_term_meta( $category_id, '_wcv_commission_fee', wp_unslash( $_POST['_wcv_commission_fee'] ) );
		$commission_percent = update_term_meta( $category_id, '_wcv_commission_percent', wp_unslash( $_POST['_wcv_commission_percent'] ) );
	} // save_category_commissions()

	/**
	 * Add product SEO data tab
	 *
	 * @param   array $tabs Tabs array.
	 * @return  array
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function add_product_data_tabs( $tabs ) {
		return array_merge(
			$tabs,
			array(
				'seo' => __( 'Product SEO', 'wcvendors-pro' ),
			)
		);
	} // add_product_data_tabs()

	/**
	 * Add option to disable edit product capability of vendor.
	 *
	 * @since 1.7.0
	 */
	public function disable_vendor_edit_product() {
	?>
	<div class="options_group disable-vendor-edit-product">
		<?php
		woocommerce_wp_checkbox(
			array(
				'id'          => '_disable_vendor_edit',
				'label'       => __( 'Disable edit', 'wcvendors-pro' ),
				'description' => sprintf(
					/* translators: %s customized vendor string */
					__( 'Disable product edit capability of %s for this product.', 'wcvendors-pro' ),
					wcv_get_vendor_name()
				),
			)
			);
			?>
	</div>
	<?php
	}

	/**
	 * Handle saving disable_vendor_edit meta key.
	 *
	 * @param int $post_id Product ID.
	 *
	 * @since 1.7.0
	 */
	public function save_disable_vendor_edit_product( $post_id ) {
		if (
			! isset( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'update-post_' . $post_id )
		) {
			return false;
		}

		if (
			! isset( $_POST['_disable_vendor_edit'] )
			|| 'yes' !== sanitize_key( wp_unslash( $_POST['_disable_vendor_edit'] ) )
		) {
			delete_post_meta( $post_id, '_disable_vendor_edit' );
			return false;
		}

		update_post_meta( $post_id, '_disable_vendor_edit', 'yes' );
	}

	/**
	 * Add nav menu meta boxes.
	 *
	 * @version 1.7.5
	 * @since 1.7.5
	 */
	public function add_nav_menu_meta_boxes( $object ) {
		add_meta_box( 'wcvendor-links-metabox', __( 'WC Vendors', 'wcvendors-pro' ), array( $this, 'nav_menu_meta_box_links' ), 'nav-menus', 'side', 'default' );
		return $object;
	}

	/**
	 * Add nav menu meta boxes.
	 *
	 * @version 1.7.6
	 * @since 1.7.5
	 */
	public function nav_menu_meta_box_links() {
		global $nav_menu_selected_id;
		$vendor_dashboard_url = WCVendors_Pro_Dashboard::get_dashboard_page_url();
		$pages                = array(
			'Dashboard'  => esc_html( $vendor_dashboard_url ),
			'Products'   => esc_html( $vendor_dashboard_url . 'product' ),
			'Orders'     => esc_html( $vendor_dashboard_url . 'order' ),
			'Settings'   => esc_html( $vendor_dashboard_url . 'settings' ),
			'Coupons'    => esc_html( $vendor_dashboard_url . 'shop_coupon' ),
			'Ratings'    => esc_html( $vendor_dashboard_url . 'rating' ),
			'View Store' => 'javascript:;',
		);
		foreach ( $pages as $key => $value ) {
			$my_items['item'][] =
				(object) array(
					'ID'               => '',
					'object_id'        => 1,
					'type_label'       => __( $key, 'wcvendors-pro' ),
					'title'            => __( $key, 'wcvendors-pro' ),
					'url'              => $value,
					'type'             => 'custom',
					'object'           => 'wcvendor_pro_menu_item',
					'db_id'            => 0,
					'menu_item_parent' => 0,
					'post_parent'      => 0,
					'target'           => '',
					'attr_title'       => '',
					'description'      => '',
					'classes'          => array( 'wcvendor_pro_menu_item' ),
					'xfn'              => '',
				);
		}

		$removed_args = array( 'action', 'customlink-tab', 'edit-menu-item', 'menu-item', 'page-tab', '_wpnonce' );
		$walker       = new Walker_Nav_Menu_Checklist();
		include apply_filters( 'wcv_partial_path_pro_nav_menu_meta_box', 'partials/vendor/wcvendors-pro-nav-menu-meta-box.php' );
	}
}
