<?php
/**
 * Automatically suggest addon based on installed plugins
 *
 * @since 4.1.5
 */
class WOO_MSTORE_INTEGRATION_AUTO_SUGGEST_ADDON {
	/**
	 * Addons to install
	 */
	private $install_addons = array();

	/**
	 * Addons to update.
	 */
	private $update_addons = array();

	/**
	 * Supported plugins.
	 *
	 * @var array
	 */
	private $addons = array(
		'wp-all-import-pro/wp-all-import-pro.php'       => array(
			'name'            => 'WP All Import – WooCommerce Multistore Add-on',
			'slug'            => 'wp-all-import-woocommerce-multistore-add-on/wpai-woocommerce-multistore-add-on.php',
			'required'        => true,
			'version'         => '2.0.4',
			'require_license' => false,
			'external_url'    => 'https://woomultistore.com/product/wp-all-import-woocommerce-woomultistore-add-on/',
			'support_for'     => 'WP All Import Pro',
		),

		'wp-all-import/wp-all-import.php'               => array(
			'name'            => 'WP All Import – WooCommerce Multistore Add-on',
			'slug'            => 'wp-all-import-woocommerce-multistore-add-on/wpai-woocommerce-multistore-add-on.php',
			'required'        => true,
			'version'         => '2.0.4',
			'require_license' => false,
			'external_url'    => 'https://woomultistore.com/product/wp-all-import-woocommerce-woomultistore-add-on/',
			'support_for'     => 'WP All Import',
		),

		'woocommerce-multilingual/wpml-woocommerce.php' => array(
			'name'            => 'WPML WooCommerce Multistore Add-on',
			'slug'            => 'wpml-woocommerce-multistore-add-on/wpml-woocommerce-multistore-add-on.php',
			'required'        => true,
			'version'         => '',
			'external_url'    => '',
			'require_license' => false,
			'external_url'    => 'https://woomultistore.com/product/wpml-woocommerce-multistore-addon/',
			'support_for'     => 'WPML',

		),

		'advanced-custom-fields/acf.php'                => array(
			'name'            => 'WooMultistore Woocommerce ACF Add-on',
			'slug'            => 'acf-woocommerce-multistore-add-on/acf-woocommerce-multistore-add-on.php',
			'required'        => true,
			'version'         => '',
			'require_license' => false,
			'external_url'    => 'https://woomultistore.com/product/acf-advanced-custom-fields-woomultistore-add-on/',
			'support_for'     => 'Advanced Custom Fields (ACF)',
		),

		// 'advanced-custom-fields'                  => array(
		// 'name'            => 'Woocommerce Wholesale Price WooMultistore Add-On',
		// 'slug'            => 'wp-all-import-woocommerce-multistore-add-on3',
		// 'required'        => true,
		// 'version'         => '',
		// 'require_license' => false,
		// 'external_url'    => 'https://woomultistore.com/product/woocommerce-wholesale-price-woomultistore-add-on/',
		// 'support_for'     => "Woocommerce Wholesale Price",
		// ),

		// 'advanced-custom-fields'                  => array(
		// 'name'            => 'WooCommerce Bookings WooMultistore Add-On',
		// 'slug'            => 'wp-all-import-woocommerce-multistore-add-on4',
		// 'required'        => true,
		// 'version'         => '',
		// 'require_license' => false,
		// 'external_url'    => 'https://woomultistore.com/product/woocommerce-bookings-woomultistore-add-on/',
		// ),

		// 'advanced-custom-fields'                  => array(
		// 'name'         => 'PPOM for WooCommerce WooMultistore Add-On',
		// 'slug'         => 'wp-all-import-woocommerce-multistore-add-on5',
		// 'required'     => true,
		// 'version'      => '',
		// 'external_url' => 'https://woomultistore.com/product/ppom-for-woocommerce-add-on/',
		// ),

		// 'multistore'                              => array(
		// 'name'            => 'Bulk sync products Add-On for WooMultistore',
		// 'slug'            => 'wp-all-import-woocommerce-multistore-add-on6',
		// 'required'        => false,
		// 'version'         => '',
		// 'require_license' => false,
		// 'external_url'    => 'https://woomultistore.com/product/bulk-sync-products-add-on-for-woomultistore/',
		// 'dependency'      => array(),
		// ),

		// 'multistore'                              => array(
		// 'name'            => 'Change sequential order number Add-On for WooMultistore',
		// 'slug'            => 'wp-all-import-woocommerce-multistore-add-on7',
		// 'required'        => false,
		// 'version'         => '',
		// 'require_license' => false,
		// 'external_url'    => 'https://woomultistore.com/product/change-sequential-order-number-add-on-for-woomultistore/',
		// ),
	);


	/**
	 * Initialize
	 */
	public function __construct() {
		if ( is_admin() ) {
			$this->check_addon_to_update_install();

			add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );

			if ( is_multisite() && is_network_admin() ) {
				add_action( 'network_admin_notices', array( $this, 'show_admin_notices' ) );
			}
		}
	}

	/**
	 * Check installed plugin and show notice if addon is missing.
	 *
	 * @return void
	 */
	public function check_addon_to_update_install() {
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

		if ( ! empty( $this->addons ) ) {
			foreach ( $this->addons as $key => $addon ) {
				if ( in_array( $key, $active_plugins ) ) {
					// supported plugin is installed.
					// Check if addon is installed.
					if ( ! in_array( $addon['slug'], $active_plugins ) ) {
						$this->install_addons[] = $key;
					} else {
						// Free addon installed. Check if the user is running correct version.
						$plugin_data = get_plugin_data( $addon['slug'], false, false );

						if ( ! empty( $addon['version'] ) && ! empty( $plugin_data['Version'] ) && version_compare( $plugin_data['Version'], $addon['version'] ) === -1 ) {
							$this->update_addons[] = $key;
						}
					}
				}
			}
		}
	}

	/**
	 * Show admin notices.
	 *
	 * @return void
	 */
	public function show_admin_notices() {
		if ( ! empty( $this->install_addons ) ) {
			foreach ( $this->install_addons as $key ) {
				$class   = 'notice notice-error';
				$message = __( '<strong>' . esc_html( $this->addons[ $key ]['name'] ) . '</strong> must be installed and active for ' . '<strong>' . esc_html( $this->addons[ $key ]['support_for'] ) . '</strong>' . ' to work properly. ' . "Please <a href='" . esc_attr( $this->addons[ $key ]['external_url'] ) . "' target='_blank'>download</a> from our website if you don't have it installed.", 'woonet' );
				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
			}
		}

		if ( ! empty( $this->update_addons ) ) {
			foreach ( $this->update_addons as $key ) {
				$class   = 'notice notice-error';
				$message = __( '<strong>' . esc_html( $this->addons[ $key ]['name'] ) . "</strong> must be updated to version {$this->addons[$key]['version']} for " . '<strong>' . esc_html( $this->addons[ $key ]['support_for'] ) . '</strong>' . ' to work properly. ' . "Please <a href='" . esc_attr( $this->addons[ $key ]['external_url'] ) . "' target='_blank'>download</a> from our website.", 'woonet' );
				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
			}
		}
	}
}

new WOO_MSTORE_INTEGRATION_AUTO_SUGGEST_ADDON();
