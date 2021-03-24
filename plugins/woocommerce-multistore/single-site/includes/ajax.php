<?php
/**
 * AJAX hooks that are not handled by any specific files.
 *
 * @class   WOO_MSTORE_SINGLE_AJAX_HOOKS
 * @since   4.0.0
 */
class WOO_MSTORE_SINGLE_AJAX_HOOKS {


	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 20, 0 );
	}

	/**
	 * init
	 *
	 * @return void
	 */
	public function init() {
		// Run on master to send the data to child necessary for version update.
		add_action( 'wp_ajax_nopriv_send_update_data', array( $this, 'send_update_data' ) );
		add_action( 'wp_ajax_nopriv_woomulti_custom_payload', array( $this, 'woomulti_custom_payload' ) );

		add_action( 'wp_ajax_nopriv_woomulti_child_options_get', array( $this, 'get_options' ) );
		add_action( 'wp_ajax_nopriv_woomulti_child_options_update', array( $this, 'update_options' ) );

		add_action( 'wp_ajax_nopriv_woomulti_get_blognames', array( $this, 'get_blognames' ) );
		add_action( 'wp_ajax_nopriv_woomulti_get_order_exports', array( $this, 'child_order_exports' ) );
		add_action( 'wp_ajax_nopriv_woomulti_check_site_connection', array( $this, 'send_site_connection_status' ) );
		add_action( 'wp_ajax_nopriv_master_receive_updates', array( $this, 'master_receive_updates' ), 10, 0 );
	}

	public function send_update_data() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( $_engine->is_request_authenticated( $_POST ) ) {
			echo $_engine->send_data_for_update();
		} else {
			echo json_encode(
				array(
					'status'  => 'error',
					'message' => 'Request is not authenticated.',
				)
			);
			woomulti_log_error( 'Child site update: Request is not authenticated.' );
		}
		die;
	}

	public function woomulti_custom_payload() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( $_engine->is_request_authenticated( $_POST ) ) {
			if ( ! empty( $_POST['data']['payload_type'] ) && ! empty( $_POST['data']['payload_contents'] ) ) {

				// Fire the hook.
				do_action( 'WOO_MSTORE_SYNC/CUSTOM/' . sanitize_text_field( $_POST['data']['payload_type'] ), $_POST['data'] );

				wp_send_json(
					array(
						'status'  => 'succes',
						'message' => 'Data Received.',
					)
				);
			} else {
				woomulti_log_error( 'Custom payload empty.' );

				wp_send_json(
					array(
						'status'  => 'error',
						'message' => 'Custom payload empty.',
					)
				);
			}
		} else {
			woomulti_log_error( 'Child site recieved custom payload. But request was not authenticated.' );

			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'Request is not authenticated.',
				)
			);
		}
	}

	public function get_options() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			woomulti_log_error( 'Update options requested. But request was not authenticated.' );

			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'Request is not authenticated.',
				)
			);
		}

		$option = get_option( 'woonet_options' );

		/**
		 * These options are managed by master.
		 * There's no need to send them back to the master site.
		 * Master has these settings and sends to the child sites only for updates.
		 */
		unset( $option['synchronize-stock'] );
		unset( $option['sync-all-metadata'] );
		unset( $option['synchronize-trash'] );
		unset( $option['publish-capability'] );
		unset( $option['sequential-order-numbers'] );
		unset( $option['network-user-info'] );
		unset( $option['use-global-image'] );
		unset( $option['sync-by-sku'] );

		if ( ! empty( $option ) ) {
			$option['blog_name'] = get_bloginfo( 'name' ); // To display on each tab of the settings panel.
		}

		wp_send_json(
			array(
				'status'  => 'success',
				'result'  => $option,
				'message' => '',
			),
			200
		);
	}

	public function update_options() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			woomulti_log_error( 'Update options requested. But request was not authenticated.' );

			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'Request is not authenticated for ' . get_bloginfo( 'name' ),
				)
			);
		}

		if ( ! empty( $_POST['data'] ) ) {
			$manager = new WOO_MSTORE_OPTIONS_MANAGER();

			$connect_details = get_option( 'woonet_master_connect' );

			if ( ! empty( $connect_details['uuid'] )
				&& ! empty( $_POST['data'][ $connect_details['uuid'] ] )
				&& ! empty( $_POST['data']['master'] ) ) {
				$updated_options        = $_POST['data'][ $connect_details['uuid'] ];
				$updated_master_options = $_POST['data']['master'];
				$updated_options        = array_merge(
					array(
						'synchronize-stock'        => isset( $updated_master_options['synchronize-stock'] ) ? $updated_master_options['synchronize-stock'] : 'no',
						'sync-all-metadata'        => isset( $updated_master_options['sync-all-metadata'] ) ? $updated_master_options['sync-all-metadata'] : 'no',
						'synchronize-trash'        => isset( $updated_master_options['synchronize-trash'] ) ? $updated_master_options['synchronize-trash'] : 'no',
						'publish-capability'       => isset( $updated_master_options['publish-capability'] ) ? $updated_master_options['publish-capability'] : 'no',
						'network-user-info'        => isset( $updated_master_options['network-user-info'] ) ? $updated_master_options['network-user-info'] : 'no',
						'sync-custom-taxonomy'     => isset( $updated_master_options['sync-custom-taxonomy'] ) ? $updated_master_options['sync-custom-taxonomy'] : 'no',
						'sync-custom-metadata'     => isset( $updated_master_options['sync-custom-metadata'] ) ? $updated_master_options['sync-custom-metadata'] : 'no',
						'sequential-order-numbers' => isset( $updated_master_options['sequential-order-numbers'] ) ? $updated_master_options['sequential-order-numbers'] : 'no',
						'use-global-image'         => isset( $updated_master_options['use-global-image'] ) ? $updated_master_options['use-global-image'] : 'no',
						'sync-by-sku'              => isset( $updated_master_options['sync-by-sku'] ) ? $updated_master_options['sync-by-sku'] : 'no',
					),
					$updated_options
				);

				$manager->update( $updated_options );

				wp_send_json(
					array(
						'status'  => 'success',
						'result'  => get_option( 'woonet_master_connect' ),
						'message' => 'Settings updated on ' . get_bloginfo( 'name' ),
					)
				);
			}
		}

		woomulti_log_error( 'Update options request failed.' );

		wp_send_json(
			array(
				'status'  => 'error',
				'result'  => '',
				'message' => 'Can not update options for ' . get_bloginfo( 'name' ),
			)
		);
	}

	/**
	 * Send blogname
	 *
	 * @return void
	 */
	public function get_blognames() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			woomulti_log_error( 'Update options requested. But request was not authenticated.' );

			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'Request is not authenticated for ' . get_bloginfo( 'name' ),
					'result'  => '',
				)
			);
		}

		wp_send_json(
			array(
				'status'  => 'success',
				'result'  => get_bloginfo( 'name' ),
				'message' => 'Successfully retrieved blogname',
			)
		);
	}

	/**
	 * Send child order data to master site for exports function.
	 */
	public function child_order_exports() {
		include_once dirname( WOO_MSTORE_PATH ) . '/include/class.admin.export.engine.single.php';

		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			woomulti_log_error( 'Update options requested. But request was not authenticated.' );

			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'Request is not authenticated for ' . get_bloginfo( 'name' ),
				)
			);
		}

		if ( ! empty( $_POST['data'] ) ) {
			$export = new WOO_MSTORE_EXPORT_ENGINE_SINGLE();
			$export->set_options( $_POST['data'] );
			$orders = $export->output();

			wp_send_json(
				array(
					'status'  => 'success',
					'message' => 'Orders succesfully retrieved.',
					'result'  => $orders,
				)
			);
		} else {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'Missing parameters for ' . get_bloginfo( 'name' ),
				)
			);
		}
	}

	/**
	 * send_site_connection_status
	 *
	 * @return void
	 */
	public function send_site_connection_status() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			woomulti_log_error( 'Connection status check requested, but not authenticated.' );

			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'Request is not authenticated for ' . get_bloginfo( 'name' ),
					'result'  => '',
				)
			);
		}

		wp_send_json(
			array(
				'status'  => 'Success',
				'message' => 'Success',
				'result'  => array(
					'version'    => defined( 'WOO_MSTORE_VERSION' ) ? WOO_MSTORE_VERSION : '',
					'connection' => 'success',
				),
			)
		);

	}

	/**
	 * Receive sync request from the child site
	 */
	public function master_receive_updates() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			woomulti_log_error( 'Stock sync requested by the child. Authentication failed.' );

			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'Stock sync requested by child. Authentication failed.',
				)
			);
		}

		if ( ! empty( $_POST['data']['parent_id'] ) ) {
			$wc_product = wc_get_product( (int) $_POST['data']['parent_id'] );
			$uuid       = WOO_MULTISTORE()->site_manager->get_uuid_by_key( $_POST['Authorization'] );

			if ( ! WOO_MULTISTORE()->sync_utils->is_stock_sync_required( $wc_product->get_id(), $uuid ) ) {
				return;
			}

			if ( $wc_product && $wc_product->get_id() ) {
                do_action('WOO_MSTORE/sync/realtime/disable');

				$wc_product->set_stock_quantity( $_POST['data']['current_stock'] );
				$wc_product->set_stock_status( $_POST['data']['stock_status'] );
				$wc_product->set_manage_stock( $_POST['data']['manage_stock'] );

				if ( ! empty( $_POST['data']['variations'] ) ) {
					foreach ( $_POST['data']['variations'] as $variation_data ) {
						$variation = wc_get_product( $variation_data['parent_id'] );
						$variation->set_stock_quantity( $variation_data['current_stock'] );
						$variation->set_stock_status( $variation_data['stock_status'] );
						$variation->set_manage_stock( $variation_data['manage_stock'] );
						$variation->save();
					}
				}

				$wc_product->save();

				WOO_MULTISTORE()->product_sync_interface->realtime_sync( (int) $_POST['data']['parent_id'], $uuid, true );

				do_action('WOO_MSTORE/sync/realtime/enable');
			}
		}
	}
}

$GLOBALS['WOO_MSTORE_SINGLE_AJAX_HOOKS'] = new WOO_MSTORE_SINGLE_AJAX_HOOKS();
