<?php
/**
 * WooMultistore single site init
 *
 * @since 3.0.0
 **/

class WOO_MSTORE_CONNECTED_SITES {

	/**
	 * Initialize action hooks and load the plugin classes
	 **/
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_woomultistore_submenu' ), PHP_INT_MAX );
	}

	/**
	 * Add a primary menu for WooMultistore
	 **/
	public function add_woomultistore_submenu() {
		// only if superadmin
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! woomulti_has_valid_license() ) {
			return;
		}

		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			// enter license key
			$hookname = add_submenu_page(
				'woonet-woocommerce',
				'Sites',
				'Sites',
				'manage_options',
				'woonet-connected-sites',
				array( $this, 'menu_callback_connected_sites' ),
				2
			);

			add_action( 'load-' . $hookname, array( $this, 'connected_sites_form_submit' ) );
		}
	}

	public function menu_callback_connected_sites() {
		woomulti_get_template_parts( 'connected-sites' );
	}

	public function connected_sites_form_submit() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( ! isset( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'woonet_delete_site' )
		) {
			wp_die( 'Nope! You are not allowed to pefrom this action.' );
		}

		$_SESSION['mstore_form_submit_messages'] = array();

		$connected_sites = get_option( 'woonet_child_sites' );
		$inactive_sites  = get_option( 'woonet_child_sites_deactivated', array() );

		if ( ! empty( $_REQUEST['submit'] ) && $_REQUEST['submit'] == 'remove' ) {
			foreach ( $connected_sites as $key => $value ) {
				if ( $key == $_POST['__key'] ) {
					unset( $connected_sites[ $key ] );
				}
			}

			foreach ( $inactive_sites as $key => $value ) {
				if ( $key == $_POST['__key'] ) {
					unset( $inactive_sites[ $key ] );
				}
			}

			$_SESSION['mstore_form_submit_messages'][] = 'Site removed succesfully.';

		} elseif ( ! empty( $_REQUEST['submit'] ) && $_REQUEST['submit'] == 'deactivate' ) {
			foreach ( $connected_sites as $key => $value ) {
				if ( $key == $_POST['__key'] ) {
					$inactive_sites[ $key ] = $connected_sites[ $key ];
					unset( $connected_sites[ $key ] );
				}
			}

			$_SESSION['mstore_form_submit_messages'][] = 'Site deactivated succesfully.';

		} elseif ( ! empty( $_REQUEST['submit'] ) && $_REQUEST['submit'] == 'activate' ) {
			foreach ( $inactive_sites as $key => $value ) {
				if ( $key == $_POST['__key'] ) {
					$connected_sites[ $key ] = $inactive_sites[ $key ];
					unset( $inactive_sites[ $key ] );
				}
			}

			$_SESSION['mstore_form_submit_messages'][] = 'Site activated succesfully.';

		}

		update_option( 'woonet_child_sites', $connected_sites );
		update_option( 'woonet_child_sites_deactivated', $inactive_sites );
	}

}

$GLOBALS['WOO_MSTORE_CONNECTED_SITES'] = new WOO_MSTORE_CONNECTED_SITES();

