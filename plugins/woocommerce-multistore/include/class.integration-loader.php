<?php
/**
 * Load 3rd party plugin integrations based on whether the plugins are installed and active
 */
final class WOO_MSTORE_INTEGRATION_LOADER {

	/**
	 *  Supported plugin list
	 *
	 * @var $supported_plugins
	 */
	private $supported_plugins = array(
		'woocommerce/woocommerce.php'                   => array( 'core-custom-taxonomies.php', 'core-custom-metadata.php', 'core-auto-suggest-addon.php' ),
		'price-by-user-role-for-woocommerce-pro/price-by-user-role-for-woocommerce-pro.php' => 'tyche-price-by-user-role.php',
		'atum-multi-inventory/atum-multi-inventory.php' => 'atum-multi-inventory.php',
		'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' => 'woocommerce-pdf-invoices-packingslips.php',
		'product-gtin-ean-upc-isbn-for-woocommerce/product-gtin-ean-upc-isbn-for-woocommerce.php' => 'product-gtin-ean-upc-isbn-for-woocommerce.php',
		'wpc-countdown-timer/wpc-countdown-timer.php' => 'wpc-countdown-time.php',
		'yikes-inc-easy-custom-woocommerce-product-tabs/yikes-inc-easy-custom-woocommerce-product-tabs.php' => 'custom-product-tabs-wp-all-import-add-on.php',
		'elementor/elementor.php' => 'elementor-elementor-pro.php',
	);

	/**
	 *  Path to the integration folder
	 *
	 * @var $integration_path
	 */
	private $integration_path = null;

	/**
	 * Initialize the action hooks and load the plugin classes
	 **/
	public function __construct() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( is_multisite() ) {
			$this->integration_path = WOO_MSTORE_PATH . 'integration/';
		} else {
			$this->integration_path = dirname( WOO_MSTORE_PATH ) . '/integration/';
		}

		add_action( 'init', array( $this, 'init' ), PHP_INT_MAX, 0 );
	}

	public function init() {
		$this->add_supported_plugins();
		$this->load_support_for_active_plugins();
	}


	/**
	 * Run filter to add supported plugins to the list
	 *
	 * @return void
	 */
	private function add_supported_plugins() {
		$this->supported_plugins = apply_filters(
			'WOO_MSTORE_Integration/add_supported_plugins',
			$this->supported_plugins
		);
	}

	/**
	 * Load integration support for all 3rd party plugins
	 *
	 * @return void
	 */
	private function load_support_for_active_plugins() {
		if ( ! empty( $this->supported_plugins ) ) {
			foreach ( $this->supported_plugins as $plugin_name => $plugin_support_file ) {
				if ( is_array( $plugin_support_file ) ) {
					foreach ( $plugin_support_file as $component ) {
						if ( is_plugin_active( $plugin_name ) ) {
							$this->_load_supported_plugin( $component );
						}
					}
				} else {
					if ( is_plugin_active( $plugin_name ) ) {
						$this->_load_supported_plugin( $plugin_support_file );
					}
				}
			}
		}

		do_action( 'WOO_MSTORE_Integration/supported_plugins_loaded', 100, 200 );
	}

	/**
	 * Load supported plugin file
	 *
	 * @param mixed $file_to_load string integration file to load
	 * @return void
	 */
	private function _load_supported_plugin( $file_to_load ) {
		if ( file_exists( $this->integration_path . $file_to_load ) ) {
			include_once $this->integration_path . $file_to_load;
		}
	}
}

$GLOBALS['WOO_MSTORE_INTEGRATION_LOADER'] = new WOO_MSTORE_INTEGRATION_LOADER();