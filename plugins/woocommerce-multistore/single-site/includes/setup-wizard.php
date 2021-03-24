<?php
/**
 * WooMultistore single site init
 *
 * @package WooMultistore
 * @since 3.0.0
 */

class WOO_MSTORE_SINGLE_SETUP_WIZARD {

	/**
	 * Initialize the action hooks and load the plugin classes
	 **/
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_woomultistore_submenu' ), PHP_INT_MAX );
		add_action( 'admin_head', array( $this, 'remove_setup_wizard_from_menu' ), PHP_INT_MAX );

		// Runs on master site, when adding a site
		add_action( 'wp_ajax_woonet_child_submit', array( $this, 'connect_child_ajax_submit' ) );

		// Runs on child site to very master site that is submitted
		add_action( 'wp_ajax_woonet_verify', array( $this, 'verify_master_ajax_submit' ) );
		add_action( 'wp_ajax_nopriv_woonet_verify_child', array( $this, 'verify_child_ajax_submit' ) );

		// runs on child when master site is disconnected
		add_action( 'wp_ajax_woonet_delete_master', array( $this, 'woonet_delete_master' ) );
	}

	/**
	 * Add a primary menu for WooMultistore
	 **/
	public function add_woomultistore_submenu() {
		// only if superadmin
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_submenu_page(
			'woonet-woocommerce',
			'Setup Wizard',
			'Setup Wizard',
			'manage_options',
			'woonet-setup-wizard',
			array( $this, 'sub_menu_callback' ),
			1
		);

		// Enter license key.
		$hookname = add_submenu_page(
			'woonet-woocommerce',
			'Select Network Type',
			'Select Network Type',
			'manage_options',
			'woonet-network-type',
			array( $this, 'menu_callback_select_network_type' )
		);

		add_action( 'load-' . $hookname, array( $this, 'select_network_type_form_submit' ) );

		// Enter license key.
		$hookname = add_submenu_page(
			'woonet-woocommerce',
			'Enter License Key',
			'Enter License Key',
			'manage_options',
			'woonet-license-key',
			array( $this, 'menu_callback_enter_license_key' )
		);

		add_action( 'load-' . $hookname, array( $this, 'enter_license_key_form_submit' ) );

		// Connect child site.
		$hookname = add_submenu_page(
			'woonet-woocommerce',
			'Add a Site',
			'Add a Site',
			'manage_options',
			'woonet-connect-child',
			array( $this, 'menu_callback_connect_child' )
		);

		// Connect child site.
		$hookname = add_submenu_page(
			'woonet-woocommerce',
			'Connect to Master',
			'Connect to Master',
			'manage_options',
			'woonet-connect-master',
			array( $this, 'menu_callback_connect_master' )
		);

	}

	public function remove_setup_wizard_from_menu() {
		// remove_submenu_page( 'woonet-woocommerce', 'woonet-setup-wizard' );
		remove_submenu_page( 'woonet-woocommerce', 'woonet-license-key' );
		remove_submenu_page( 'woonet-woocommerce', 'woonet-network-type' );
		remove_submenu_page( 'woonet-woocommerce', 'woonet-connect-child' );
		remove_submenu_page( 'woonet-woocommerce', 'woonet-connect-master' );
	}

	/**
	 * Submenu is hidden. This hook is unused.
	 **/
	public function sub_menu_callback() {
		woomulti_get_template_parts( 'admin-setup-wizard' );
	}

	public function menu_callback_enter_license_key() {
		if ( ! empty( $_REQUEST['woonet_license_key_remove'] ) ) {
			$this->deactivate_license();
			wp_redirect( admin_url( 'admin.php?page=woonet-license-key', 'relative' ) );
			die;
		}

		woomulti_get_template_parts( 'setup-wizard-license-key' );
	}

	public function enter_license_key_form_submit() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( ! isset( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'woonet_license_verify_submit' )
		) {
			wp_die( 'Nope! You are not allowed to pefrom this action.' );
		}

		$_SESSION['mstore_form_submit_messages'] = array();

		if ( isset( $_POST['woonet_license_key'] ) ) {

				$license_key = isset( $_POST['woonet_license_key'] ) ? sanitize_key( trim( $_POST['woonet_license_key'] ) ) : '';

			if ( $license_key == '' ) {
					$_SESSION['mstore_form_submit_messages'][] = __( "Licence key can't be empty", 'woonet' );
					return;
			}

			// build the request query
			$args = array(
				'woo_sl_action'     => 'activate',
				'licence_key'       => $license_key,
				'product_unique_id' => WOO_MSTORE_PRODUCT_ID,
				'domain'            => WOO_MSTORE_INSTANCE,
			);

			$request_uri = WOO_MSTORE_APP_API_URL . '?' . http_build_query( $args, '', '&' );
			$data        = wp_remote_get( $request_uri );

			if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
					woomulti_log_error( 'There was a problem connecting to the license verifiation server.' );
					woomulti_log_error( $data );

					$_SESSION['mstore_form_submit_messages'][] .= __( 'There was a problem connecting to the license verifiation server.', 'woonet' );
					return;
			}

			$response_block = woomulti_json_decode( $data['body'] );
			// Retrieve the last message within the $response_block.
			$response_block = $response_block[ count( $response_block ) - 1 ];
			$response       = $response_block->message;

			if ( isset( $response_block->status ) ) {
				if ( $response_block->status == 'success' && in_array( $response_block->status_code, array( 's100', 's101' ) ) ) {
						// the license is active and the software is active
						$_SESSION['mstore_form_submit_messages'][] = $response_block->message;

						$license_data = get_option( 'mstore_license' );

						// save the license
						$license_data['key']        = $license_key;
						$license_data['last_check'] = time();

						update_site_option( 'mstore_license', $license_data );

				} else {
					woomulti_log_error( 'There was a problem activating the license:' );
					woomulti_log_error( $response_block );

					$_SESSION['mstore_form_submit_messages'][] = __( 'There was a problem activating the license: ', 'woonet' ) . $response_block->message;
					return;
				}
			} else {
					woomulti_log_error( 'There was a problem with the data block received from the server.' );
					woomulti_log_error( $response_block );

					$_SESSION['mstore_form_submit_messages'][] = __( 'There was a problem with the data block received from the server.', 'woonet' );
					return;
			}

			wp_redirect( admin_url( 'admin.php?page=woonet-setup-wizard', 'relative' ) );
			die();
		}
	}

	// Render page contents.
	public function menu_callback_select_network_type() {
		woomulti_get_template_parts( 'setup-wizard-select-network-type' );
	}

	// $_POST
	public function select_network_type_form_submit() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( ! isset( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'woonet_select_network_type' )
		) {
			wp_die( 'Nope! You are not allowed to pefrom this action.' );
		}

		$_SESSION['mstore_form_submit_messages'] = array();

		if ( ! empty( $_POST['woonet_network_type'] ) ) {
			update_option( 'woonet_network_type', strip_tags( $_POST['woonet_network_type'] ) );
			// redirect
			wp_redirect( admin_url( 'admin.php?page=woonet-setup-wizard', 'relative' ) );
			die();
		} else {
			$_SESSION['mstore_form_submit_messages'][] = __( 'Please select a network type.', 'woonet' );
		}
	}

	public function menu_callback_connect_child() {
		woomulti_get_template_parts( 'connect-child-sites' );
	}

	/**
	 * Runs on master site, when adding a site.
	 *
	 * @return void
	 */
	public function connect_child_ajax_submit() {
		if ( ! empty( $_POST['url'] ) ) {
			if ( filter_var( $_POST['url'], FILTER_VALIDATE_URL ) === false ) {
				wp_send_json(
					array(
						'error'   => 1,
						'message' => 'Please enter a valid URL.',
					)
				);
			}

			$url         = esc_url_raw( $_POST['url'] );
			$child_sites = get_option( 'woonet_child_sites', array() );
			$site_key    = sha1( uniqid() );
			$uuid        = md5( $url );

			$child_sites[ $site_key ] = array(
				'site_url'   => $url,
				'date_added' => time(),
				'site_key'   => $site_key,
				'uuid'       => $uuid,
			);

			if ( update_option( 'woonet_child_sites', $child_sites ) ) {
				// now hide the wizard alert
				update_option( 'woonet_setup_wizard_complete', 'yes' );

				wp_send_json(
					array(
						'success'  => 1,
						'message'  => 'Site succesfully added',
						'copy_url' => admin_url( 'admin-ajax.php?action=woonet_verify&k=' . $site_key . '&id=' . $uuid ),
					)
				);
			} else {
				wp_send_json(
					array(
						'error'   => 1,
						'message' => 'Can not save site.',
					)
				);
			}
		}

		wp_send_json(
			array(
				'error' => 1,
				'msg'   => 'No valid URL provided.',
			)
		);
	}

	public function menu_callback_connect_master() {
		woomulti_get_template_parts( 'connect-master-site' );
	}

	// runs on child site
	public function verify_master_ajax_submit() {
		if ( ! empty( $_POST['url'] ) ) {
			$site_link = str_replace( 'woonet_verify', 'woonet_verify_child', $_POST['url'] );
			$data      = wp_remote_get( $site_link );

			if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
				woomulti_log_error( 'There was a problem connecting to the server (' . $data->get_error_message() . ').' );
				woomulti_log_error( $data );

				echo json_encode(
					array(
						'error'   => 1,
						'message' => 'There was a problem connecting to the server (' . $data->get_error_message() . ').',
					)
				);
				die;
			}

			$response_block = woomulti_json_decode( $data['body'] );

			if ( isset( $response_block->status ) ) {
				if ( $response_block->status == 'success' ) {
						$parts = parse_url( $site_link );
						parse_str( $parts['query'], $query );

						update_option(
							'woonet_master_connect',
							array(
								'key'        => $query['k'],
								'uuid'       => $query['id'],
								'master_url' => $parts['scheme'] . '://' . $parts['host'] . preg_replace( '/\/wp-admin\/admin-ajax.php/', '', $parts['path'] ),
							)
						);

						// now hide the wizard alert
						update_option( 'woonet_setup_wizard_complete', 'yes' );

						echo json_encode(
							array(
								'success' => 1,
								'message' => 'Site added to the network.',
							)
						);
						die;
				} else {
					woomulti_log_error( 'Remote failed to verify the site.' );
					woomulti_log_error( $response_block );

					echo json_encode(
						array(
							'error'   => 1,
							'message' => 'Remote failed to verify the site.',
						)
					);
					die;
				}
			} else {
				woomulti_log_error( 'No response received from the master site.' );
				woomulti_log_error( $data );
				echo json_encode(
					array(
						'error'   => 1,
						'message' => 'No response received from the master site.',
					)
				);
				die;
			}
		}

		echo json_encode(
			array(
				'error'   => 1,
				'message' => 'No valid URL provided.',
			)
		);
		die;
	}

	public function verify_child_ajax_submit() {
		echo json_encode(
			array(
				'status'  => 'success',
				'message' => 'Site succesfully authenticated',
			)
		);
		die;
	}

	public function woonet_delete_master() {
		if ( delete_option( 'woonet_master_connect' ) ) {
			echo json_encode(
				array(
					'success' => 1,
					'message' => 'Succesfully disconnected from master.',
				)
			);
		} else {
			echo json_encode(
				array(
					'error'   => 1,
					'message' => 'Failed to disconnect master.',
				)
			);
		}
		die;
	}

	private function deactivate_license() {
		$license_data = get_option( 'mstore_license' );
		$license_key  = $license_data['key'];

		// build the request query
		$args = array(
			'woo_sl_action'     => 'deactivate',
			'licence_key'       => $license_key,
			'product_unique_id' => WOO_MSTORE_PRODUCT_ID,
			'domain'            => WOO_MSTORE_INSTANCE,
		);

		$request_uri = WOO_MSTORE_APP_API_URL . '?' . http_build_query( $args, '', '&' );
		$data        = wp_remote_get( $request_uri );

		if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
			$_SESSION['mstore_form_submit_messages'] = __( 'There was a problem connecting to ', 'woonet' ) . WOO_MSTORE_APP_API_URL;
			return;
		}

		$response_block = json_decode( $data['body'] );
		// retrieve the last message within the $response_block
		$response_block = $response_block[ count( $response_block ) - 1 ];
		$response       = $response_block->message;

		if ( isset( $response_block->status ) ) {
			if ( $response_block->status == 'success' && $response_block->status_code == 's201' ) {
				// the license is active and the software is active
				$mstore_form_submit_messages = $response_block->message;
				delete_option( 'mstore_license' );
			} else { // if message code is e104  force de-activation
				if ( $response_block->status_code == 'e002' || $response_block->status_code == 'e104' ) {
					delete_option( 'mstore_license' );
				} else {
					$_SESSION['mstore_form_submit_messages'] = __( 'There was a problem deactivating the licence: ', 'woonet' ) . $response_block->message;
					delete_option( 'mstore_license' );
					return;
				}
			}
		} else {
				$_SESSION['mstore_form_submit_messages'] = __( 'There was a problem with the data block received from ', 'woonet' ) . WOO_MSTORE_APP_API_URL;
				return;
		}
	}
}

$GLOBALS['WOO_MSTORE_SINGLE_SETUP_WIZARD'] = new WOO_MSTORE_SINGLE_SETUP_WIZARD();

