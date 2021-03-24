<?php
/**
 * Adds the top level primary menu for the plugin
 * 
 * @since 3.0.0
 * @package WooMultistore
 */

class WOO_MSTORE_SINGLE_MENU {

	/**
	 * Initialize the action hooks and load the plugin classes
	 **/
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_woomultistore_menu' ), PHP_INT_MAX - 1 );
		add_action( 'admin_head', array( $this, 'remove_default_submenu_page' ), PHP_INT_MAX - 1 );
	}

	/**
	 * Add a primary menu for WooMultistore
	 **/
	public function add_woomultistore_menu() {
		// only if superadmin
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_menu_page(
			'Multistore',
			'Multistore',
			'manage_options',
			'woonet-woocommerce',
			array( $this, 'top_level_menu_callback' ),
			null,
			57
		);
	}

	public function top_level_menu_callback() {
		wp_redirect( admin_url( 'admin.php?page=woonet-setup-wizard', 'relative' ) );
		die;
	}

	/**
	 * Hide the default submenu created for the top menu page.
	 **/
	public function remove_default_submenu_page() {
		remove_submenu_page( 'woonet-woocommerce', 'woonet-woocommerce' );
	}
}

$GLOBALS['WOO_MSTORE_SINGLE_MENU'] = new WOO_MSTORE_SINGLE_MENU();

