<?php
/**
 * WooMultistore stock synchoronize
 */

class WOO_MSTORE_SINGLE_STOCK_SYNC_LEGACY {

	private $_options = null;

	/**
	 * Initialize the action hooks and load the plugin classes
	 **/
	public function __construct() {
		// Add init actions.
		add_action( 'init', array( $this, 'init' ), 10, 0 );
	}

	public function init() {
		add_action( 'woocommerce_thankyou', array( $this, 'reduce_stock' ), 10, 1 );

		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			add_action( 'wp_ajax_nopriv_master_receive_stock_updates', array( $this, 'master_receive_stock_change' ), 10, 0 );
		}

		if ( get_option( 'woonet_network_type' ) == 'child' ) {
			add_action( 'wp_ajax_nopriv_child_receive_stock_updates', array( $this, 'child_receive_stock_change' ), 10, 0 );
		}

		// handle manual orders
		if ( get_option( 'woonet_network_type' ) == 'child' ) {
			add_action( 'wp_insert_post', array( $this, 'update_stock_on_manual_orders' ), 10, 1 );
		}
	}

	/**
	 * Reduce stock after order is made
	 */
	public function reduce_stock( $order_id ) {
		do_action( 'WOO_MSTORE_STOCK_SYNC/stock_reduce_start', $order_id );

		$options = get_option( 'woonet_options', array() );
		// Get products from order id.
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			woomulti_log_error( 'Stock Reduce: Can not retrieve order for ID ' . $order_id );
			return;
		}

		$products         = $order->get_items();
		$stock_sync_queue = array();

		if ( ! empty( $products ) ) {
			foreach ( $products as $product ) {
				// Check if stock has alreayd been synced.
				if ( $order->get_meta( '_woomulti_stock_reduced_' . $product->get_product_id() ) == 'yes' ) {
					continue;
				}

				if ( $product->get_variation_id() ) {
					$stock_sync_queue[] = $this->get_stock_sync_data( $product->get_product_id(), $product, $order_id, $product->get_variation_id() );
				} else {
					$stock_sync_queue[] = $this->get_stock_sync_data( $product->get_product_id(), $product, $order_id, null );
				}

				update_post_meta( $order->get_id(), '_woomulti_stock_reduced_' . $product->get_product_id(), 'yes' );

				if ( ! $this->is_sync_required( $product, $options ) ) {
					continue;
				}
			}
		}

		if ( ! empty( $stock_sync_queue ) ) {
			// Send stock to sync.
			if ( get_option( 'woonet_network_type' ) == 'master' ) {
				// notify child.
				$this->master_notify_stock_change( $order_id, $stock_sync_queue );
			} else {
				// notify master site.
				$this->child_notify_stock_change( $order_id, $stock_sync_queue );
			}
		}

		do_action( 'WOO_MSTORE_STOCK_SYNC/stock_reduce_end', $order_id );
	}

	/**
	 * Retrieve stock data for a product
	 *
	 * @param integer $product_id Product ID
	 * @param integer $order_item Order Item
	 * @param integer $order_id Order ID
	 * @return array  Stock status data sent to all sites in the network.
	 **/
	public function get_stock_sync_data( $product_id, $order_item, $order_id, $variation_id = null ) {
		$product = wc_get_product( $product_id );
		$options = get_option( 'woonet_options' );

		if ( $variation_id ) {
			$variation = wc_get_product( $variation_id );
		}

		/**
		 * Sometimes associated product may be deleted. Check if the product exists.
		 */
		if ( empty( $product ) ) {
			return false;
		}

		return apply_filters(
			'WOO_MSTORE_STOCK_SYNC/get_stock_data',
			array(
				'qty'                    => $order_item->get_quantity(),
				'current_stock'          => $product->get_stock_quantity(),
				'stock_status'           => $product->get_stock_status(),
				'product_id'             => $product->get_id(),
				'parent_id'              => $product->get_meta( '_woonet_master_product_id', true ),
				'product_type'           => $product->get_type(),
				'manage_stock'           => $product->get_manage_stock(),
				'variation_id'           => isset( $variation ) && $variation->get_id() ? $variation->get_id() : '',
				'parent_variation_id'    => isset( $variation ) && $variation->get_id() ? $variation->get_meta( '_woonet_master_product_id', true ) : '',
				'variation_manage_stock' => isset( $variation ) && $variation->get_id() ? $variation->get_manage_stock() : '',
				'network_type'           => get_option( 'woonet_network_type' ),
				'global_stock_sync'      => ! empty( $options['synchronize-stock'] ) ? $options['synchronize-stock'] : 'no',
				'order_id'               => $order_id,
				'source_type'            => get_option( 'woonet_network_type' ), // Type of the originator network.
			)
		);
	}

	/**
	 * Notify child sites of a stock change from the master site
	 *
	 * @param integer $order_id Order ID
	 * @param array   $stock_sync_queue Stock sync data
	 * @param string  $exclude Site ID to exclude. When a child notify master, the site needs to be excluded so that it doesn't get sent its own updates.
	 * @return null
	 **/
	public function master_notify_stock_change( $order_id, $stock_sync_queue, $exclude = false ) {
		do_action( 'WOO_MSTORE_STOCK_SYNC/pre_child_notify', $order_id, $stock_sync_queue );

		$sites   = get_option( 'woonet_child_sites' );
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		foreach ( $sites as $key => $value ) {
			if ( $value['uuid'] != $exclude ) {
				$resp = $_engine->stock_sync( $value, $stock_sync_queue, 'master' );
				do_action( 'WOO_MSTORE_STOCK_SYNC/child_notify', $value, $stock_sync_queue, 'master' );
			}
		}

		do_action( 'WOO_MSTORE_STOCK_SYNC/after_child_notify', $order_id, $stock_sync_queue );
	}

	/**
	 * Notify master of a stock change from the child
	 *
	 * @param intger $order_id order ID
	 * @param array  $stock_sync_queue Stock data
	 **/
	public function child_notify_stock_change( $order_id, $stock_sync_queue ) {
		$master  = get_option( 'woonet_master_connect' );
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
		$resp    = $_engine->stock_sync( $master, $stock_sync_queue, 'child' );

		do_action( 'WOO_MSTORE_STOCK_SYNC/master_notify', $order_id, $stock_sync_queue );
	}


	/**
	 * Receive stock change notification from child sites
	 **/
	public function master_receive_stock_change() {
		$_engine  = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
		$headers  = getallheaders();
		$options  = get_option( 'woonet_child_sites' );
		$settings = get_option( 'woonet_options' );

		$status = array();

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			error_log( 'Stock Reduce: Authorization failed.' );
			die( 'You are not authorized to access this resource.' );
		}

		if ( ! empty( $_POST['post_data'] ) ) {
			foreach ( $_POST['post_data'] as $product_data ) {

				if ( ! empty( $product_data['parent_variation_id'] )
					&& ! empty( $product_data['variation_manage_stock'] )
					&& $product_data['variation_manage_stock'] == 'parent' ) {
						$master_product = wc_get_product( $product_data['parent_id'] );
				} elseif ( ! empty( $product_data['parent_variation_id'] ) ) {
					$master_product = wc_get_product( $product_data['parent_variation_id'] );
				} else {
					$master_product = wc_get_product( $product_data['parent_id'] );
				}

				if ( empty( $master_product ) ) {
					woomulti_log_error( 'Parent Product not found ID: ' . $product_data['parent_id'] );
					return;
				}

				if ( ! empty( $headers['Authorization'] ) ) {
					$site_uuid = $options[ $headers['Authorization'] ]['uuid'];
				}

				if ( empty( $site_uuid ) && ! empty( $_POST['Authorization'] ) ) {
					$site_uuid = $options[ $_POST['Authorization'] ]['uuid'];
				}

				$order_id = $product_data['order_id'];

				if ( $master_product->get_type() == 'variation' ) {
					$_is_per_product_sync_enabled = get_post_meta( $master_product->get_parent_id(), '_woonet_' . $site_uuid . '_child_stock_synchronize', true );
				} else {
					$_is_per_product_sync_enabled = get_post_meta( $master_product->get_id(), '_woonet_' . $site_uuid . '_child_stock_synchronize', true );
				}

				if ( $settings['synchronize-stock'] == 'yes' || $_is_per_product_sync_enabled == 'yes' ) {

					$current_stock = $master_product->get_stock_quantity();

					$master_product->set_stock_quantity( $current_stock - $product_data['qty'] );
					$master_product->save();

					$status[] = array(
						'parent_id'              => $product_data['parent_id'],
						'product_id'             => $product_data['product_id'],
						'variation_id'           => ! empty( $product_data['variation_id'] ) ? $product_data['variation_id'] : '',
						'parent_variation_id'    => ! empty( $product_data['parent_variation_id'] ) ? $product_data['parent_variation_id'] : '',
						'old_stock'              => $current_stock,
						'new_stock'              => $master_product->get_stock_quantity(),
						'variation_manage_stock' => ! empty( $product_data['variation_manage_stock'] ) ? $product_data['variation_manage_stock'] : '',
						'manage_stock'           => ! empty( $product_data['manage_stock'] ) ? $product_data['manage_stock'] : '',
					);

					do_action( 'WOO_MSTORE_STOCK_SYNC/master_stock_change', $master_product->get_id(), $current_stock, $master_product->get_stock_quantity() );
				} else {
					woomulti_log_error( 'No need to update child product. ' );
					woomulti_log_error( $product_data );
				}
			}

			/**
			 * Update stock on remaining child sites when a stock
			 * updated is initiated by a child site
			*/
			$this->update_all_child_from_master( $order_id, $_POST['post_data'], $site_uuid );
		}

		echo json_encode( $status );
		die;
	}

	/**
	 * Receive stock change from master site
	 **/
	public function child_receive_stock_change() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
		$_site   = get_option( 'woonet_master_connect' );

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			woomulti_log_error( 'Stock Reduce: Authorization failed.' );
			die( 'You are not authorized to access this resource.' );
		}

		$status = array();

		if ( ! empty( $_POST['post_data'] ) ) {
			foreach ( $_POST['post_data'] as $product_data ) {
				if ( ! empty( $product_data['variation_id'] )
					&& ! empty( $product_data['variation_manage_stock'] )
					&& $product_data['variation_manage_stock'] == 'parent'
				) {
					$child_id = $_engine->get_mapped_child_post( (int) $product_data['product_id'] );
				} elseif ( ! empty( $product_data['variation_id'] ) ) {
					$child_id = $_engine->get_mapped_child_post( (int) $product_data['variation_id'] );
				} else {
					$child_id = $_engine->get_mapped_child_post( (int) $product_data['product_id'] );
				}

				if ( empty( $child_id ) ) {
					return;
				}

				$child_product = wc_get_product( $child_id );

				if ( empty( $child_product ) ) {
					woomulti_log_error( 'Child product does not exist.' );
					return;
				}

				if ( $child_product->get_type() == 'variation' ) {
					$_is_per_product_sync_enabled = get_post_meta( $child_product->get_parent_id(), '_woonet_' . $_site['uuid'] . '_child_stock_synchronize', true );
				} else {
					$_is_per_product_sync_enabled = get_post_meta( $child_id, '_woonet_' . $_site['uuid'] . '_child_stock_synchronize', true );
				}

				if ( $product_data['global_stock_sync'] == 'yes' || $_is_per_product_sync_enabled == 'yes' ) {

					$current_stock = $child_product->get_stock_quantity();

					$child_product->set_stock_quantity( $current_stock - $product_data['qty'] );
					$child_product->save();

					$status[] = array(
						'parent_id'              => $product_data['product_id'],
						'product_id'             => $child_id,
						'variation_id'           => ! empty( $product_data['variation_id'] ) ? $product_data['variation_id'] : '',
						'parent_variation_id'    => ! empty( $product_data['parent_variation_id'] ) ? $product_data['parent_variation_id'] : '',
						'old_stock'              => $current_stock,
						'new_stock'              => $child_product->get_stock_quantity(),
						'variation_manage_stock' => ! empty( $product_data['variation_manage_stock'] ) ? $product_data['variation_manage_stock'] : '',
						'manage_stock'           => ! empty( $product_data['manage_stock'] ) ? $product_data['manage_stock'] : '',
					);

					// Fire stock updated hook.
					do_action( 'WOO_MSTORE_STOCK_SYNC/child_stock_updated', $current_stock, $child_product->get_stock_quantity() );
				} else {
					woomulti_log_error( 'No need to update child product. ' );
					woomulti_log_error( $product_data );
				}
			}
		}

		echo json_encode( $status );
		die;
	}

	/**
	 * Update other child stocks when a child stock update notification is received
	 * by the master from a child site in the network.
	 **/
	public function update_all_child_from_master( $order_id, $post_data, $site_uuid ) {
		$_post_data = array();
		$options    = get_option( 'woonet_options' );

		do_action( 'WOO_MSTORE_STOCK_SYNC/update_all_child', $order_id, $post_data, $site_uuid );

		foreach ( $post_data as $value ) {
			$_post_data[] = array_merge(
				$value,
				array(
					'global_stock_sync' => ! empty( $options['synchronize-stock'] ) ? $options['synchronize-stock'] : 'no',
					'product_id'        => $value['parent_id'],
					'variation_id'      => $value['parent_variation_id'],
				)
			);
		}

		$this->master_notify_stock_change( $order_id, $_post_data, $site_uuid );
	}

	/**
	 * When an order is created from the backend, update stock accordingly
	 */
	public function update_stock_on_manual_orders( $order_id ) {

		do_action( 'WOO_MSTORE_STOCK_SYNC/admin_stock_sync', $order_id );

		if ( wp_is_post_revision( $order_id ) ) {
			return;
		}

		if ( ! did_action( 'woocommerce_checkout_order_processed' )
			&& get_post_type( $order_id ) == 'shop_order' ) {
				do_action( 'WOO_MSTORE_STOCK_SYNC/admin_stock_before_update', $order_id );

				$this->reduce_stock( $order_id );

				do_action( 'WOO_MSTORE_STOCK_SYNC/admin_stock_after_update', $order_id );
		}
	}

	/**
	 * is_sync_required
	 *
	 * @since 4.0.0
	 */
	public function is_sync_required( $product, $options ) {
		/** Feature introduced in version 4.0.0.
		 * If below version 4.0.0, return true for backward compability.
		*/
		if ( version_compare( WOO_MSTORE_VERSION, '4.0.0', '<' ) ) {
			return true;
		}

		if ( ! apply_filters( 'WOO_MSTORE_STOCK_SYNC/is_sync_required', true, $product, $options ) ) {
			return false;
		}

		/**
		 * If stock sync is enabled globally always return true.
		 */
		if ( isset( $options['synchronize-stock'] ) && $options['synchronize-stock'] == 'yes' ) {
			return true;
		}

		$_site = get_option( 'woonet_master_connect' );

		if ( empty( $_site ) && get_option( 'woonet_network_type' ) == 'child' ) {
			// No master data found.
			return false;
		}

		if ( get_option( 'woonet_network_type' ) == 'child'
			&& get_post_meta( $product->get_product_id(), "_woonet_{$_site['uuid']}_child_stock_synchronize", true ) == 'yes' ) {
			return true;
		}

		if ( get_option( 'woonet_network_type' ) == 'master' && $this->is_child_stock_enabled( $product->get_product_id() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check to see if one of the child site has stock sync enabled.
	 *
	 * @return boolean
	 */
	private function is_child_stock_enabled( $product_id ) {
		$child_sites = get_option( 'woonet_child_sites' );

		if ( ! empty( $child_sites ) ) {
			foreach ( $child_sites as $site ) {
				if ( ! empty( $site['uuid'] )
					 && get_post_meta( $product_id, "_woonet_{$site['uuid']}_child_stock_synchronize", true ) == 'yes'
					 && get_post_meta( $product_id, "_woonet_publish_to_{$site['uuid']}", true ) == 'yes'
				 ) {
					return true;
				}
			}
		}

		return false;
	}
}

$GLOBALS['WOO_MSTORE_SINGLE_STOCK_SYNC_LEGACY'] = new WOO_MSTORE_SINGLE_STOCK_SYNC_LEGACY();
