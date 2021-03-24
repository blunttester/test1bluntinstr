<?php
/**
 * Activation Hook
 **/
function woo_mstore_activation_hook() {
	require_once dirname( __FILE__ ) . '/includes/options-manager.php';
	require_once dirname( __FILE__ ) . '/includes/activation-hook.php';
}

/**
 * Deactivation Hook
 **/
function woo_mstore_deactivation_hook() {
	require_once dirname( __FILE__ ) . '/includes/options-manager.php';
	require_once dirname( __FILE__ ) . '/includes/deactivation-hook.php';
}

/**
 * Regiser activation and deactivation hooks
 */
register_activation_hook( dirname( dirname( __FILE__ ) ) . '/woocommerce-multistore.php', 'woo_mstore_activation_hook' );
register_deactivation_hook( dirname( dirname( __FILE__ ) ) . '/woocommerce-multistore.php', 'woo_mstore_deactivation_hook' );

/**
 * WooMultistore single site init
 **/
final class WOO_MSTORE_SINGLE_MAIN {

	/**
	 * Site Manager Instance
	 **/
	public $site_manager = null;

	/**
	 * Asset Manager Instance
	 **/
	public $asset_manager = null;

	/**
	 * License Manager Instance
	 **/
	public $license_manager = null;

	/**
	 * Options Manager Instance
	 **/
	public $options_manager = null;

	/**
	 * Sync Engine
	 **/
	public $sync_engine = null;

	/**
	 * Sync Utils
	 **/
	public $sync_utils = null;

	/**
	 * Network sync product interface
	 */
	public $product_sync_interface = null;

	/**
	 * Instance
	 **/
	public static $_instance = null;

	/**
	 * initiate the action hooks and load the plugin classes
	 **/
	private function __construct() {
		/**
		 * If WooCommerce is inactive, don't run the plugin
		 */
		if ( ! $this->is_woocomemrce_active() ) {
			return false;
		}

		$this->include_required_classes();
		$this->setup_action_hooks();

		// Property and global variable for backward compatibility.
		$this->license_manager = new WOO_MSTORE_licence();

		// Site Manager
		$this->site_manager = new WOO_MSTORE_SINGLE_SITE_MANAGER();

		// Asset Manager
		$this->asset_manager = new WOO_MSTORE_SINGLE_ASSETS_MANAGER();

		// Options Manager
		$this->options_manager = new WOO_MSTORE_OPTIONS_MANAGER();

		// Sync Engine
		$this->sync_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		// Sync Utility
		$this->sync_utils = new WOO_MSTORE_SINGLE_UTILS_SYNC();

		// Network product sync interface
		$this->product_sync_interface = new WOO_MSTORE_SINGLE_NETWORK_PRODUCTS_SYNC();
	}

	public static function getInstance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Include required classes
	 **/
	public function include_required_classes() {

		require_once dirname( __FILE__ ) . '/constants.php';

		// Shared Files
		require_once dirname( WOO_MSTORE_PATH ) . '/include/licence.php';
		require_once dirname( WOO_MSTORE_PATH ) . '/include/class.updater.php';
		require_once dirname( WOO_MSTORE_PATH ) . '/include/class.admin.product.php';
		require_once dirname( WOO_MSTORE_PATH ) . '/include/class.functions.php';
		require_once dirname( WOO_MSTORE_PATH ) . '/include/global-image-helper.php';
		require_once dirname( WOO_MSTORE_PATH ) . '/include/global-image.php';
		require_once dirname( WOO_MSTORE_PATH ) . '/include/options-custom-taxonomies.php';

		// Single site files
		require_once dirname( __FILE__ ) . '/includes/assets-manager.php';
		require_once dirname( __FILE__ ) . '/includes/options-manager.php';
		require_once dirname( __FILE__ ) . '/includes/site-manager.php';
		require_once dirname( __FILE__ ) . '/includes/utils-sync.php';
		require_once dirname( __FILE__ ) . '/includes/functions.php';
		require_once dirname( __FILE__ ) . '/includes/menu.php';
		require_once dirname( __FILE__ ) . '/includes/setup-wizard.php';
		require_once dirname( __FILE__ ) . '/includes/connected-sites.php';
		require_once dirname( __FILE__ ) . '/includes/editor-integration.php';
		require_once dirname( __FILE__ ) . '/includes/network-products.php';
		require_once dirname( __FILE__ ) . '/includes/network-orders.php';
		require_once dirname( __FILE__ ) . '/includes/network-products-sync.php';
		require_once dirname( __FILE__ ) . '/includes/network-sync-engine.php';
		require_once dirname( __FILE__ ) . '/includes/options.php';
		require_once dirname( __FILE__ ) . '/includes/trash-products.php';
		require_once dirname( __FILE__ ) . '/includes/version.php';
		require_once dirname( __FILE__ ) . '/includes/ajax.php';
		require_once dirname( __FILE__ ) . '/includes/sequential-order-number.php';
		require_once dirname( __FILE__ ) . '/includes/order-meta.php';
		// require_once dirname( __FILE__ ) . '/includes/stock-sync-legacy.php';

		// Load 3rd party integration support
		require_once dirname( WOO_MSTORE_PATH ) . '/include/class.integration-loader.php';

		/**
		 * If dev constant is defined, disable some security features.
		 */
		if ( defined( 'WOO_MOSTORE_DEV_ENV' ) && WOO_MOSTORE_DEV_ENV == true ) {
			require_once dirname( __FILE__ ) . '/includes/dev-env.php';
		}

		// Load export functionality from the core multistore plugin.
		require_once dirname( WOO_MSTORE_PATH ) . '/include/class.admin.export.php';
	}

	/**
	 * Sets up common action hooks
	 **/
	public function setup_action_hooks() {
		if ( get_option( 'woonet_setup_wizard_complete' ) != 'yes' && ! $this->check_if_plugin_page() ) {
			add_action( 'admin_notices', array( $this, 'show_setup_instructions' ) );
		}

		// Show additional information in plugin row.
		add_filter( 'plugin_row_meta', array( $this, 'action_links' ), 10, 2 );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 4.2.0
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'woocommerce' ), '4.2.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 4.2.0
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'woocommerce' ), '4.2.0' );
	}

	/**
	 * Show set up instructions
	 **/
	public function show_setup_instructions() {
		woomulti_get_template_parts( 'admin-notice-setup-wizard' );
	}

	/**
	 * Hide the setup wizard warning from plugin page
	 **/
	public function check_if_plugin_page() {
		if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'woonet' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Modify plugin row action links
	 */
	public function action_links( $links, $file ) {
		if ( strpos( $file, 'woocommerce-multistore.php' ) !== false ) {
			unset( $links[2] );

			if ( get_option( 'woonet_network_type' ) == 'child' ) {
				/**
				 * Child site settings panel has been moved to the master site. Link to it.
				 */

				$master_data = get_option( 'woonet_master_connect' );

				if ( ! empty( $master_data['master_url'] ) ) {
					$links[] = '<a target="_blank" href="' . esc_url( $master_data['master_url'] . '/wp-admin/admin.php?page=woonet-woocommerce-settings' ) . '">Settings</a>';
				}
			} else {
				$links[] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=woonet-woocommerce-settings' ) ) . '">Settings</a>';
			}

			$links[] = '<a href="https://woomultistore.com/documentation/">Docs</a>';
			$links[] = '<a href="https://woomultistore.com/plugin-api-filters-actions/">API docs</a>';
		}

		return $links;
	}

	public function is_woocomemrce_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_inactive_admin_notice' ), 10, 0 );
			return false;
		}

		return true;
	}

	public function woocommerce_inactive_admin_notice() {
		$class   = 'notice notice-error';
		$message = __( 'Multistore requires WooCommerce to be activated', 'woonet' );

		printf(
			'<div class="%1$s"><p>%2$s</p></div>',
			esc_attr( $class ),
			esc_html( $message )
		);
	}
}

function WOO_MULTISTORE() {
	return WOO_MSTORE_SINGLE_MAIN::getInstance();
}

$GLOBALS['WOO_MULTISTORE'] = WOO_MULTISTORE();

