<?php
/**
 * Admin setup wziard
 *
 * @author      WooCommerce, Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors_Pro/Admin
 * @version     1.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors_Admin_Setup_Wizard class.
 */
class WCVendors_Pro_Admin_Setup_Wizard {

	/**
	 * Current step
	 *
	 * @var string
	 */
	private $step = '';

	/**
	 * Steps for the setup wizard
	 *
	 * @var array
	 */
	private $steps = array();

	/**
	 * Actions to be executed after the HTTP response has completed
	 *
	 * @var array
	 */
	private $deferred_actions = array();

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		if ( apply_filters( 'wcvendors_pro_enable_setup_wizard', true ) && current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'wcvendors-pro-setup', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {

		if ( empty( $_GET['page'] ) || 'wcvendors-pro-setup' !== $_GET['page'] ) {
			return;
		}
		$default_steps = array(
			'store_setup'  => array(
				'name'    => __( 'Start', 'wcvendors-pro' ),
				'view'    => array( $this, 'wcv_setup_general' ),
				'handler' => array( $this, 'wcv_setup_general_save' ),
			),
			'capabilities' => array(
				'name'    => __( 'Capabilities', 'wcvendors-pro' ),
				'view'    => array( $this, 'wcv_setup_capabilities' ),
				'handler' => array( $this, 'wcv_setup_capabilities_save' ),
			),
			'pages'        => array(
				'name'    => __( 'Pages', 'wcvendors-pro' ),
				'view'    => array( $this, 'wcv_setup_pages' ),
				'handler' => array( $this, 'wcv_setup_pages_save' ),
			),
			'ready'        => array(
				'name'    => __( 'Ready!', 'wcvendors-pro' ),
				'view'    => array( $this, 'wcv_setup_ready' ),
				'handler' => '',
			),
		);

		$this->steps = apply_filters( 'wcvendors_pro_setup_wizard_steps', $default_steps );
		$this->step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
		$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.0' );
		wp_register_script(
			'wc-enhanced-select',
			WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js',
			array(
				'jquery',
				'selectWoo',
			),
			WC_VERSION
		);
		wp_localize_script(
			'wc-enhanced-select',
			'wc_enhanced_select_params',
			array(
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
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'     => wp_create_nonce( 'search-products' ),
				'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
			)
		);
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );

		wp_enqueue_style(
			'wcvendors-pro-setup',
			plugin_dir_url( __FILE__ ) . 'assets/css/wcvendors-pro-setup' . $suffix . '.css',
			array(
				'dashicons',
				'install',
			),
			WCV_PRO_VERSION
		);
		wp_register_script(
			'wcvendors-pro-setup',
			plugin_dir_url( __FILE__ ) . 'assets/js/wcvendors-pro-setup' . $suffix . '.js',
			array(
				'jquery',
				'wc-enhanced-select',
				'jquery-blockui',
				'wp-util',
			),
			WCV_PRO_VERSION
		);

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
		}

		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @param string $step slug (default: current step).
	 *
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since 2.0.0
	 */
	public function get_next_step_link( $step = '' ) {
		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );
		if ( end( $keys ) === $step ) {
			return admin_url();
		}

		$step_index = array_search( $step, $keys );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ], remove_query_arg( 'activate_error' ) );
	}

	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {

		include WCV_PRO_ABSPATH_ADMIN . 'views/setup/header.php';
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {
		include WCV_PRO_ABSPATH_ADMIN . 'views/setup/footer.php';
	}

	/**
	 * Output the steps.
	 */
	public function setup_wizard_steps() {
		$output_steps = $this->steps;
		include WCV_PRO_ABSPATH_ADMIN . 'views/setup/steps.php';
	}

	/**
	 * Output the content for the current step.
	 */
	public function setup_wizard_content() {
		echo '<div class="wcv-setup-content">';
		if ( ! empty( $this->steps[ $this->step ]['view'] ) ) {
			call_user_func( $this->steps[ $this->step ]['view'], $this );
		}
		echo '</div>';
	}

	/**
	 * Helper method to retrieve the current user's email address.
	 *
	 * @return string Email address
	 */
	protected function get_current_user_email() {
		$current_user = wp_get_current_user();
		$user_email   = $current_user->user_email;

		return $user_email;
	}

	/**
	 * Initial "marketplace setup" step.
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_general() {

		$allow_admin                        = get_option( 'wcvendors_disable_wp_admin_vendors', 'no' );
		$wcvendors_vendor_login_redirect    = get_option( 'wcvendors_vendor_login_redirect', 'my-account' );
		$commission_rate                    = get_option( 'wcvendors_vendor_commission_rate', '' );
		$wcvendors_commission_type          = get_option( 'wcvendors_commission_type', 'percent' );
		$wcvendors_commission_amount        = get_option( 'wcvendors_commission_amount', '' );
		$wcvendors_commission_fee           = get_option( 'wcvendors_commission_fee', '' );
		$wcvendors_commission_coupon_action = get_option( 'wcvendors_commission_coupon_action', 'yes' );

		include WCV_PRO_ABSPATH_ADMIN . 'views/setup/general.php';
	}

	/**
	 * Save initial marketplace settings.
	 *
	 * @version 1.7.3
	 * @version 1.0.0
	 */
	public function wcv_setup_general_save() {

		check_admin_referer( 'wcvendors-pro-setup' );

		$allow_admin                        = isset( $_POST['wcvendors_disable_wp_admin_vendors'] ) ? sanitize_text_field( $_POST['wcvendors_disable_wp_admin_vendors'] ) : '';
		$wcvendors_vendor_login_redirect    = isset( $_POST['wcvendors_vendor_login_redirect'] ) ? sanitize_text_field( $_POST['wcvendors_vendor_login_redirect'] ) : '';
		$wcvendors_commission_type          = sanitize_text_field( $_POST['wcvendors_commission_type'] );
		$commission_rate                    = sanitize_text_field( $_POST['wcvendors_vendor_commission_rate'] );
		$wcvendors_commission_amount        = sanitize_text_field( $_POST['wcvendors_commission_amount'] );
		$wcvendors_commission_fee           = sanitize_text_field( $_POST['wcvendors_commission_fee'] );
		$wcvendors_commission_coupon_action = sanitize_text_field( $_POST['wcvendors_commission_coupon_action'] );

		update_option( 'wcvendors_disable_wp_admin_vendors', $allow_admin );
		update_option( 'wcvendors_vendor_login_redirect', $wcvendors_vendor_login_redirect );
		update_option( 'wcvendors_vendor_commission_rate', $commission_rate );
		update_option( 'wcvendors_commission_type', $wcvendors_commission_type );
		update_option( 'wcvendors_commission_amount', $wcvendors_commission_amount );
		update_option( 'wcvendors_commission_fee', $wcvendors_commission_fee );
		update_option( 'wcvendors_commission_coupon_action', $wcvendors_commission_coupon_action );

		WCVendors_Pro_Activator::create_pages();
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * General setup
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_capabilities() {

		$wcvendors_product_management_cap     = get_option( 'wcvendors_product_management_cap', 'no' );
		$wcvendors_order_management_cap       = get_option( 'wcvendors_order_management_cap', 'no' );
		$wcvendors_shop_coupon_management_cap = get_option( 'wcvendors_shop_coupon_management_cap', 'no' );
		$wcvendors_settings_management_cap    = get_option( 'wcvendors_settings_management_cap', 'no' );
		$wcvendors_ratings_management_cap     = get_option( 'wcvendors_ratings_management_cap', 'no' );
		$wcvendors_shipping_management_cap    = get_option( 'wcvendors_shipping_management_cap', 'no' );
		$wcvendors_view_store_cap             = get_option( 'wcvendors_view_store_cap', 'no' );

		include WCV_PRO_ABSPATH_ADMIN . 'views/setup/capabilities.php';
	}

	/**
	 * Save capabilities settings.
	 */
	public function wcv_setup_capabilities_save() {

		check_admin_referer( 'wcvendors-pro-setup' );

		$wcvendors_product_management_cap     = isset( $_POST['wcvendors_product_management_cap'] ) ? sanitize_text_field( $_POST['wcvendors_product_management_cap'] ) : '';
		$wcvendors_order_management_cap       = isset( $_POST['wcvendors_order_management_cap'] ) ? sanitize_text_field( $_POST['wcvendors_order_management_cap'] ) : '';
		$wcvendors_shop_coupon_management_cap = isset( $_POST['wcvendors_shop_coupon_management_cap'] ) ? sanitize_text_field( $_POST['wcvendors_shop_coupon_management_cap'] ) : '';
		$wcvendors_settings_management_cap    = isset( $_POST['wcvendors_settings_management_cap'] ) ? sanitize_text_field( $_POST['wcvendors_settings_management_cap'] ) : '';
		$wcvendors_ratings_management_cap     = isset( $_POST['wcvendors_ratings_management_cap'] ) ? sanitize_text_field( $_POST['wcvendors_ratings_management_cap'] ) : '';
		$wcvendors_shipping_management_cap    = isset( $_POST['wcvendors_shipping_management_cap'] ) ? sanitize_text_field( $_POST['wcvendors_shipping_management_cap'] ) : '';
		$wcvendors_view_store_cap             = isset( $_POST['wcvendors_view_store_cap'] ) ? sanitize_text_field( $_POST['wcvendors_view_store_cap'] ) : '';

		update_option( 'wcvendors_product_management_cap', $wcvendors_product_management_cap );
		update_option( 'wcvendors_order_management_cap', $wcvendors_order_management_cap );
		update_option( 'wcvendors_shop_coupon_management_cap', $wcvendors_shop_coupon_management_cap );
		update_option( 'wcvendors_settings_management_cap', $wcvendors_settings_management_cap );
		update_option( 'wcvendors_ratings_management_cap', $wcvendors_ratings_management_cap );
		update_option( 'wcvendors_shipping_management_cap', $wcvendors_shipping_management_cap );
		update_option( 'wcvendors_view_store_cap', $wcvendors_view_store_cap );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Initial "marketplace setup" step.
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_pages() {

		$wcvendors_dashboard_page_ids = (array) get_option( 'wcvendors_dashboard_page_id', array() );
		$wcvendors_dashboard_page_id  = reset( $wcvendors_dashboard_page_ids );
		$wcvendors_feedback_page_id   = get_option( 'wcvendors_feedback_page_id', '' );

		include WCV_PRO_ABSPATH_ADMIN . 'views/setup/pages.php';
	}

	/**
	 * Initial "marketplace setup" step.
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_pages_save() {

		$wcvendors_dashboard_page_id = sanitize_text_field( $_POST['wcvendors_dashboard_page_id'] );
		$wcvendors_feedback_page_id  = sanitize_text_field( $_POST['wcvendors_feedback_page_id'] );

		update_option( 'wcvendors_dashboard_page_id', $wcvendors_dashboard_page_id );
		update_option( 'wcvendors_feedback_page_id', $wcvendors_feedback_page_id );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;

	}

	/**
	 * Final step.
	 */
	public function wcv_setup_ready() {

		WCVendors_Pro_Admin_Notices::remove_notice( 'install' );
		WCVendors_Pro_Activator::update_db_version();
		flush_rewrite_rules();

		$user_email = $this->get_current_user_email();
		$forums     = 'https://wordpress.org/support/plugin/wc-vendors';
		$docs_url   = 'https://docs.wcvendors.com/?utm_source=setup_wizard&utm_medium=plugin&utm_campaign=setup_complete';
		$help_text  = sprintf(
			/* translators: %1$s: link to videos, %2$s: link to docs */
			__( 'Don\'t forget to check our <a href="%1$s" target="_blank">documentation</a> to learn more about setting up WC Vendors and if you need help, be sure to visit our <a href="%2$s" target="_blank">free support forums</a>.', 'wcvendors-pro' ),
			$docs_url,
			$forums
		);

		include WCV_PRO_ABSPATH_ADMIN . 'views/setup/ready.php';
	}
}

new WCVendors_Pro_Admin_Setup_Wizard();
