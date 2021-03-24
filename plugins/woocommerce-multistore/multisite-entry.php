<?php

final class WOO_MSTORE_MULTI_INIT {

	public function __construct() {

		/**
		 * If WooCommerce is inactive, don't run the plugin
		 */
		if ( ! $this->is_woocomemrce_active() ) {
			return;
		}

		if ( is_admin() ) {
			// load classes required on the backend.
			$this->define_required_constants();
			$this->include_required_classes();
		} else {
			// load classes required on the frontend.
			$this->define_required_constants();
			$this->include_required_frontend_classes();
		}

		if ( is_admin() ) {
			add_action( 'init', array( $this, 'setup_wizard_init' ), PHP_INT_MAX, 0 );
			add_action( 'network_admin_notices', array( $this, 'network_admin_notices' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_filter( 'plugin_row_meta', array( $this, 'woo_mstore_plugin_action_links' ), 10, 2 );
		} else {
			// frontend hooks.
			add_action( 'init', array( $this, 'frontend_order_interface' ), PHP_INT_MAX, 0 );
		}

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		// add_action( 'rest_api_init', array($this, 'do_something_only_if_api_request') );
	}

	public function define_required_constants() {
		define( 'WOO_MSTORE_PATH', plugin_dir_path( __FILE__ ) );
		define( 'WOO_MSTORE_URL', plugins_url( '', __FILE__ ) );
		define( 'WOO_MSTORE_APP_API_URL', 'https://woomultistore.com/index.php' );

		define( 'WOO_MSTORE_VERSION', '4.1.5' );
		define( 'WOO_MSTORE_DB_VERSION', '1.0' );

		define( 'WOO_MSTORE_PRODUCT_ID', 'WCMSTORE' );
		define( 'WOO_MSTORE_INSTANCE', str_replace( array( 'https://', 'http://' ), '', network_site_url() ) );
		define( 'WOO_MSTORE_ASSET_URL', plugins_url( '', __FILE__ ) );
	}

	public function include_required_classes() {
		require_once WOO_MSTORE_PATH . '/include/check-requirements.php';
		require_once WOO_MSTORE_PATH . '/include/class.functions.php';
		require_once WOO_MSTORE_PATH . '/include/licence.php';
		require_once WOO_MSTORE_PATH . '/include/class.updater.php';

		if ( ! function_exists( 'woothemes_queue_update' ) || ! function_exists( 'is_woocommerce_active' ) ) {
			require_once WOO_MSTORE_PATH . '/woo-includes/woo-functions.php';
		}

		// check for other dependencies
		$options = WOO_MSTORE_functions::get_options();

		if ( $options['sequential-order-numbers'] == 'yes' ) {
			require_once WOO_MSTORE_PATH . '/include/class.sequential-order-numbers.php';
			new WOO_SON();
		}

		if ( defined( 'DOING_AJAX' ) ) {
			include WOO_MSTORE_PATH . '/include/class.ajax.php';
			new WOO_MSTORE_ajax();
		}

		require_once WOO_MSTORE_PATH . '/include/class.admin.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.network-orders.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.network-products.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.product.php';
		require_once WOO_MSTORE_PATH . '/include/class-admin-product-category.php';
		require_once WOO_MSTORE_PATH . '/include/class.options.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.speed-updater.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.grouped-products.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.upsell-cross-sell.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.coupons.php';
		require_once WOO_MSTORE_PATH . '/include/global-image-helper.php';
		require_once WOO_MSTORE_PATH . '/include/global-image.php';
		require_once WOO_MSTORE_PATH . '/include/class.compatibility.php';
		require_once WOO_MSTORE_PATH . '/include/options-custom-taxonomies.php';

		global $WOO_MSTORE;
		$WOO_MSTORE = new WOO_MSTORE_admin();
		$WOO_MSTORE->init();

		$WOO_MSTORE_options_interface = new WOO_MSTORE_options_interface();

		// export functionality
		require_once WOO_MSTORE_PATH . '/include/class.admin.export.php';

		// 3rd party plugin integration
		require_once WOO_MSTORE_PATH . '/include/class.integration-loader.php';
	}

	public function include_required_frontend_classes() {
		require_once WOO_MSTORE_PATH . '/include/check-requirements.php';
		require_once WOO_MSTORE_PATH . '/include/class.functions.php';
		require_once WOO_MSTORE_PATH . '/include/class.options.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.product.php';
		require_once WOO_MSTORE_PATH . '/include/class.stock-sync.php';

		global $WOO_MSTORE_FUNCTIONS;
		$WOO_MSTORE_FUNCTIONS = new WOO_MSTORE_functions();

		$options = WOO_MSTORE_functions::get_options();
		if ( $options['sequential-order-numbers'] == 'yes' ) {
			require_once WOO_MSTORE_PATH . '/include/class.sequential-order-numbers.php';
			new WOO_SON();
		}
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'woonet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}

	public function network_admin_notices() {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$this->setup_wizard_notice();
		}

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$this->update_wizard_notice();
		}
	}

	public function admin_notices() {
		// if ( ! is_multisite() ) {
		// echo '<div class="updated"><p>' . __( 'WooMultistore requires WordPress MultiSite environment', 'woonet' ) . '</p></div>';
		// }

		// if ( ! is_woocommerce_active() || version_compare( get_option( 'woocommerce_db_version' ), '2.1', '<' ) ) {
		// $wc_url = 'https://www.woothemes.com/woocommerce/';
		// printf( '<div class="updated"><p>' . __( 'WooMultistore requires', 'woonet' ) . ' <a href="%s">WooCommerce</a> ' . __( 'to be installed', 'woonet' ) . '</p></div>', $wc_url );
		// }

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$this->setup_wizard_notice();
		}
	}

	/**
	 * First time usage require a setip
	 */
	public function setup_wizard_notice() {
		$setup_wizard_completed = get_site_option( 'mstore_setup_wizard_completed' );
		if ( is_multisite() && is_woocommerce_active() && empty( $setup_wizard_completed ) ) {
				include WOO_MSTORE_PATH . '/include/admin/views/html-notice-setup.php';
		}

	}

	/**
	 * Updates routines
	 */
	public function update_wizard_notice() {
		global $WOO_MSTORE;

		$current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';

		if ( $WOO_MSTORE->upgrade_require === true && $current_page != 'woonet-upgrade' ) {
			include WOO_MSTORE_PATH . '/include/admin/views/html-notice-update.php';
		}

	}

	public function do_something_only_if_api_request( $wp_rest_server ) {
		$this->include_required_classes();

		require_once WOO_MSTORE_PATH . '/include/class.admin.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.network-orders.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.network-products.php';
		require_once WOO_MSTORE_PATH . '/include/class.admin.product.php';
		require_once WOO_MSTORE_PATH . '/include/class-admin-product-category.php';
		require_once WOO_MSTORE_PATH . '/include/class.options.php';

		global $WOO_MSTORE;

		if ( ! is_object( $WOO_MSTORE ) ) {
			$WOO_MSTORE = new WOO_MSTORE_admin();
			$WOO_MSTORE->init();
		}
	}

	public function woo_mstore_plugin_action_links( $links, $file ) {
		if ( strpos( $file, 'woocommerce-multistore.php' ) !== false ) {
			unset( $links[2] );
			$links[] = '<a href="' . esc_url( network_admin_url( 'settings.php?page=woo-ms-options' ) ) . '">Settings</a>';
			$links[] = '<a href="https://woomultistore.com/documentation/">Docs</a>';
			$links[] = '<a href="https://woomultistore.com/plugin-api-filters-actions/">API docs</a>';
			$links[] = '<a href="https://woomultistore.com/addons/">Addons</a>';
		}

		return $links;
	}

	public function setup_wizard_init() {
		// Setup wizard
		if ( ! empty( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case 'woonet-setup':
					require_once WOO_MSTORE_PATH . '/include/admin/class-wc-admin-setup-wizard.php';
					new WC_Admin_Setup_Wizard();
					break;
			}
		}
	}

	public function frontend_order_interface() {
		$options = WOO_MSTORE_functions::get_options();

		if ( 'no' != $options['network-user-info'] ) {
			require_once WOO_MSTORE_PATH . '/include/front/class-wc-front-my-account.php';
			new WC_Front_My_Account();
		}
	}

	public function is_woocomemrce_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_inactive_admin_notice' ), 10, 0 );
			add_action( 'network_admin_notices', array( $this, 'woocommerce_inactive_admin_notice' ), 10, 0 );
			return false;
		}

		return true;
	}

	public function woocommerce_inactive_admin_notice() {
		$class   = 'notice notice-error';
		$message = __( 'Multistore requires WooCommerce to be network activated', 'woonet' );

		printf(
			'<div class="%1$s"><p>%2$s</p></div>',
			esc_attr( $class ),
			esc_html( $message )
		);

	}
}

new WOO_MSTORE_MULTI_INIT();
