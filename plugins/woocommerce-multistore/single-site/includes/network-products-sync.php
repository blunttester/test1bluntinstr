<?php
/**
 * Network Bulk Updater
 *
 * @class   WOO_MSTORE_BULK_SYNC
 * @since   2.0.20
 * @package WooMultistore
 */

 /**
  * All types of syncs are managed from this class.
  * Quick Sync: When product is updated via WordPress quick-edit option,
  * $this->quick_sync() updates the product.
  *
  * AJAX Sync: When products are updated from the edit screen, AJAX sync runs and
  * updates the product.
  *
  * Real-time Sync
  * Updates product when product is updated from non-edit screen, such as API or 3rd party plugins.
  * Cancelled orders are updated by real-time sync, as when order is cancelled and product is updated,
  * process_product hook is fired.
  *
  * Note: Stock for cancelled orders, manual orders from child sites are updated via stock-sync.php
  * Note: Cancelled orders for master site is updated via stock-sync.php
  */
class WOO_MSTORE_SINGLE_NETWORK_PRODUCTS_SYNC {

	/**
	 * Product updater instance
	 */

	private $product_updater = null;

	/**
	 * Functions instance
	 */

	private $functions = null;

	/**
	 * Hook in ajax event handlers.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), -1, 0 );
	}

	/**
	 * Run all action hooks.
	 **/
	public function init() {
		$this->product_updater = new WOO_MSTORE_admin_product( false );
		$this->functions       = new WOO_MSTORE_functions( false );
		
		if ( ! WOO_MULTISTORE()->license_manager->licence_key_verify() ) {
			return;
		}

		if ( woomulti_has_min_user_role() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'admin_notices', array( $this, 'set_admin_notice' ) );
			add_action( 'wp_ajax_woomulti_cancel_sync', array( $this, 'cancel_sync' ) );
			add_action( 'wp_ajax_woomulti_process_job', array( $this, 'ajax_process_job' ) );
			add_filter( 'wp_redirect', array( $this, 'add_storage_id_to_query_string' ), PHP_INT_MAX, 2 );
			add_action( 'wp_ajax_nopriv_woomulti_child_payload', array( $this, 'receive_product_from_child' ) );
			add_action( 'wp_ajax_nopriv_woomulti_orders', array( $this, 'send_child_orders' ) );
			add_action( 'wp_ajax_nopriv_woomulti_order_status', array( $this, 'update_child_status' ) );

			// unlink duplicate products
			add_action( 'woocommerce_product_duplicate', array( $this, 'unlink_duplicated_product' ), PHP_INT_MAX, 2 );
		}


		/**
		 * Master Hook
		 */
		$this->enable_realtime_sync();

		/**
		 * Child Hook
		 */
		$this->enable_stock_sync();

		/**
		 * Hooks to programtically update products.
		 */
		add_action( 'WOO_MSTORE_admin_product/process_product', array( $this, 'process_product_hook' ), 5, 1 );
		add_action( 'WOO_MSTORE_admin_product/set_sync_options', array( $this, 'set_sync_options_hook' ), 5, 4 );
		add_filter( 'WOO_MSTORE/get_store_ids', array( $this, 'get_store_ids_filter' ), 5 );
		add_action( 'WOO_MSTORE/child/stock/sync', array( $this, 'sync_stock_hook' ), 20, 1 );

		add_action( 'WOO_MSTORE/sync/realtime/enable', array( $this, 'enable_realtime_sync' ), 10, 0 );
		add_action( 'WOO_MSTORE/sync/realtime/disable', array( $this, 'disable_realtime_sync' ), 10, 0 );
		add_action( 'WOO_MSTORE/sync/stock/enable', array( $this, 'enable_stock_sync' ), 10, 0 );
		add_action( 'WOO_MSTORE/sync/stock/disable', array( $this, 'disable_stock_sync' ), 10, 0 );
	}

	/**
	 * Enqueue assets for the the updater
	 */
	public function enqueue_assets() {
		if ( is_admin() ) {
			wp_register_style( 'woomulti-speed-css', plugins_url( '/assets/css/speed-updater.css', dirname( dirname( __FILE__ ) ) ), array(), WOO_MSTORE_VERSION );
			wp_enqueue_style( 'woomulti-speed-css' );

			wp_register_script( 'woomulti-speed-js', plugins_url( '/assets/js/speed-updater.js', dirname( dirname( __FILE__ ) ) ), array(), WOO_MSTORE_VERSION );
			wp_enqueue_script( 'woomulti-speed-js' );

			wp_enqueue_script( 'jquery-ui-progressbar' );
		}
	}

	/**
	 * Save submitted options for products in the database from the bulk editor
	 */
	public function process_product( $post_id ) {

		if ( ! empty( $_REQUEST['action'] )
			&& $_REQUEST['action'] == 'woocommerce_save_variations' ) {
			return;
		}

		if ( ! empty( $_REQUEST['action'] )
			&& $_REQUEST['action'] == 'woocommerce_save_attributes' ) {
			return;
		}

		if ( ! empty( $_REQUEST['action'] )
			&& $_REQUEST['action'] == 'woocommerce_link_all_variations' ) {
			return;
		}

		if ( ! empty( $_REQUEST['action'] )
			&& $_REQUEST['action'] == 'woocommerce_bulk_edit_variations' ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( did_action( 'woocommerce_product_duplicate' ) ) {
			return;
		}

		/**
		 * If the product being updated is a child product
		 * Update its metadata.
		 */

		if ( $this->is_child_product() ) {
			$this->update_child_product_metadata( $post_id );
		} else {
			$this->update_parent_product_metadata( $post_id );
		}

		/**
		 * AJAX Sync can be disabled by retuning false.
		 * If AJAX sync is disabled, realtime sync will be invoked.
		 * Realtime sync updates the product in the same request.
		 */
		/**
		 * Return false to disable realtime sync.
		 */
		if ( ! apply_filters( 'WOO_MSTORE/sync/ajax', true ) ) {
			return $this->realtime_sync( $post_id );
		}

		if ( ! empty( $_REQUEST['woomulti_request_processed'] ) ) {
			/**
			 * The hook is called once for each product. Request processed once for all products in the array.
			 */
			return;
		}

		/**
		 * User is not on edit screen hook legacy product updater function for backward compatibility
		 */

		if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'inline-save' ) {
			return $this->quick_sync();
		}

		/**
		 * When user is on edit screen, the AJAX sync runs,
		 * but if user is NOT on edit screen, and product details are updated, eg.
		 * via API or other means, quick sync runs the sync instantly.
		 */
		if ( ! $this->is_edit_screen() ) {
			return $this->realtime_sync( $post_id );
		}

		/**
		 * User is on edit screen. Set the data for AJAX updater.
		 */
		if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'editpost' ) {
			$_REQUEST['post'] = (array) $_REQUEST['post_ID'];
		}

		if ( count( $_REQUEST['post'] ) >= 1 ) {
			$_REQUEST['total_products'] = count( $_REQUEST['post'] );

		} else {
			return; // no post to update
		}

		$selected_stores = $this->get_selected_stores( $_REQUEST );

		if ( empty( $selected_stores ) ) {
			return;
		}

		if ( $this->store_update_options( $_REQUEST, $selected_stores ) ) {
			$_REQUEST['woomulti_request_processed'] = true; // request processed once. Don't process for every product in the array.
		}
	}

	/**
	 * Store product update options using transient APIs
	 *
	 * @param array request array
	 * @return boolean
	 */
	public function store_update_options( array $data, $selected_stores ) {
		$storage_id              = uniqid();
		$data['storage_id']      = $storage_id;
		$data['selected_stores'] = $selected_stores;
		$data['post_to_update']  = $this->generate_post_array_to_update( $data, $selected_stores );

		if ( set_transient( 'woomulti_product_sync_queue', $data, 4 * HOUR_IN_SECONDS ) ) {
			$_REQUEST['woomulti_storage_id'] = $storage_id;
			return true;
		}
	}

	/**
	 * Create a new multi-dimentional array with post to be updated,
	 * one array with post ID and one with store ID.
	 **/
	public function generate_post_array_to_update( $data, $selected_stores ) {
		$post_to_update = array();

		if ( ! empty( $data['post'] ) && ! empty( $selected_stores ) ) {
			foreach ( $data['post'] as $p ) {
				foreach ( $selected_stores as $s ) {
					$post_to_update[ $p ][] = array(
						'post_id'  => $p,
						'store_id' => $s,
					);
				}
			}
		}

		return $post_to_update;
	}

	/**
	 * Enqueue JavaScripts to process product update requests
	 */
	public function set_product_updater_js( $storage_id ) {
		?>
		<div class="wrap woomulti-panel">
			<div class="welcome-panel">
				<div class="welcome-panel-content">
					<h2><?php _e( 'WooMultistore Product Sync' ); ?></h2>
					<p class="about-description"><?php _e( 'Processing products in the queue. Please do not quit the browser while the sync is in progress.' ); ?></p>
					<div class="welcome-panel-column-container">
						<div class="welcome-panel-column">
								<div>
									<p style='display: none;' class="woomultistore_sync_completed"</p>
									<p style='display: none;' class="woomultistore_sync_failed"</p>
								</div>
								<div class="woomultistire_sync_container">
									<h3 class="woo-sync-message"><?php _e( 'Preparing to sync' ); ?></h3>
									<p class="woo-sync-product-count"><?php _e( 'Calculating products to be synchronized.' ); ?></p>
									<div class="progress-bar-container"> <div id="woo-product-update-progress-bar"></div> </div>
									<input type="submit" name="submit" id="submit" class="button button-primary woomulti-cancel-sync" value="Cancel Sync">
								</div>
								<div class="close-sync-screen" style="display: none;">
									<a data-attr='3' href="#"> Close (3) </a>
								</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check for transient data and enqueue JavaScript data if present
	 */
	public function set_admin_notice() {
		if ( $transient = get_transient( 'woomulti_product_sync_queue' ) ) {

			if ( ( isset( $transient['site_id'] ) && $transient['site_id'] == get_current_blog_id() ) || is_admin() ) {
				if ( ! empty( $_REQUEST['woomulti_storage_id'] ) ) {
					$this->set_product_updater_js( 123 );
				}
			}
		}
	}

	/**
	 * Cancel Sync that is already running.
	 */
	public function cancel_sync() {
		/**
		 * Page reloaded after transient is deleted to cancel the sync
		 */
		$this->delete_transient_from_all_blogs();
	}

	/**
	 * Process the job request from ajax request
	 */
	public function ajax_process_job() {
		define( 'WOOMULTI_MAX_SITE_PER_REQUEST', 3 );

		$update_config = get_transient( 'woomulti_product_sync_queue' );

		if ( count( $update_config['post_to_update'] ) ) {
			/**
			 * Request data is used by slave product update functions
			 * Lets restore request variable from transient data
			 * so that we can run product update hook without modifying those functions
			 */
			$_REQUEST = $update_config;

			$next_post = array_shift( $update_config['post_to_update'] );

			if ( count( $next_post ) > WOOMULTI_MAX_SITE_PER_REQUEST ) {
				array_unshift( $update_config['post_to_update'], array_slice( $next_post, WOOMULTI_MAX_SITE_PER_REQUEST - 1 ) );
				$next_post = array_slice( $next_post, 0, WOOMULTI_MAX_SITE_PER_REQUEST );
			}

			woomulti_log_error( 'AJAX Sync Running....' );

			foreach ( $next_post as $p ) {
				// $this->process_master_meta($update_config, $p['post_id'], $p['store_id']);
				// $this->product_updater->process_ajax_product($p['post_id'], $p['store_id'], WOOMULTI_MAX_SITE_PER_REQUEST);
				$sync = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
				$sync->sync( $p['post_id'], $p['store_id'] );
				woomulti_log_error( "AJAX Sync updated product #{$p['post_id']} on store {$p['store_id']}" );
			}

			/**
			 * We update the data after process_ajax_product() calls wp_cache_flush()
			*/
			set_transient( 'woomulti_product_sync_queue', $update_config, 4 * HOUR_IN_SECONDS );

			echo json_encode(
				array(
					'progress_percentage' => 100 - ( count( $update_config['post_to_update'] ) / $update_config['total_products'] ) * 100,
					'product_count'       => ( $update_config['total_products'] - count( $update_config['post_to_update'] ) ) . ' out of ' . $update_config['total_products'],
					'status'              => 'in-progress',
				)
			);

		} else {
			$this->delete_transient_from_all_blogs();

			echo json_encode(
				array(
					'progress_percentage' => 100,
					'product_count'       => $update_config['total_products'] . ' out of ' . $update_config['total_products'],
					'status'              => 'completed',
				)
			);

		}

		die;
	}

	/**
	 * Add slave publish to settings to master product
	 *
	 * @param $data
	 * @param $post_id
	 */
	private function process_master_meta( $data, $post_id, $store_id ) {
		if ( isset( $data[ '_woonet_publish_to_' . $store_id ] ) && $data[ '_woonet_publish_to_' . $store_id ] == 'yes' ) {
			update_post_meta( $post_id, '_woonet_publish_to_' . $store_id, 'yes' );
		} else {
			update_post_meta( $post_id, '_woonet_publish_to_' . $store_id, 'no' );
		}
	}

	/**
	 * Return the ID of the stores selected by the user for update
	 *
	 * @return array
	 */
	private function get_selected_stores( $data ) {
		$selected_stores = array();

		$sites = get_option( 'woonet_child_sites' );

		if ( empty( $sites ) ) {
			return;
		}

		foreach ( $sites as $site ) {
			if ( isset( $data[ '_woonet_publish_to_' . $site['uuid'] ] ) && $data[ '_woonet_publish_to_' . $site['uuid'] ] == 'yes' ) {
				$selected_stores[] = $site['uuid'];
			} elseif ( isset( $data[ '_woonet_publish_to_' . $site['uuid'] ] ) && $data[ '_woonet_publish_to_' . $site['uuid'] ] == 'no' ) {
				if ( $this->is_sync_required( $data, $site['uuid'] ) && ! in_array( $blog_id, $selected_stores ) ) {
					$selected_stores[] = $site['uuid'];
				}
			}
		}

		return $selected_stores;
	}

	/**
	 * When deleting transient data it's not being deleted for all blogs
	 * As a temporary solution, this method loops through all blogs and remove transient from each of them
	 *
	 * @todo: find a better solution
	 * @note the function got its name from multisite version
	 */
	private function delete_transient_from_all_blogs() {
		delete_transient( 'woomulti_product_sync_queue' );
	}

	/**
	 * When _woonet_publish_to_<blog_id> set to No, check if the product has previously been synced.
	 * If it was synced, unsync is required and we need to queue the blog for update.
	 *
	 * If it has never been synced, skip updating.
	 */
	public function is_sync_required( $data, $blog_id ) {
		if ( isset( $data['post_ID'] ) ) {
			$data['post'] = (array) $data['post_ID'];
		}

		if ( ! empty( $data['post'] ) ) {
			foreach ( $data['post'] as $pid ) {
				$post = get_post_meta( $pid, '_woonet_publish_to_' . $blog_id, true );

				if ( ! empty( $post ) && strtolower( $post ) == 'yes' ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if the product being updated is a child product
	 */
	public function is_child_product() {
		$network_type = get_option( 'woonet_network_type' );

		if ( $network_type == 'master' ) {
			return false;
		}

		return true;
	}

	/**
	 * Update child product metadata
	 */
	public function update_child_product_metadata( $post_id ) {
		if ( isset( $_REQUEST['_woonet_child_inherit_updates'] ) ) {
			update_post_meta( $post_id, '_woonet_child_inherit_updates', strip_tags( $_REQUEST['_woonet_child_inherit_updates'] ) );
		}

		if ( isset( $_REQUEST['_woonet_child_stock_synchronize'] ) ) {
			update_post_meta( $post_id, '_woonet_child_stock_synchronize', strip_tags( $_REQUEST['_woonet_child_stock_synchronize'] ) );
		}
	}

	/**
	 * Check if the user is on edit screen
	 */
	public function is_edit_screen() {
		if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woonet-woocommerce-products' && ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
			return true;
		}

		if ( ! empty( $_REQUEST['action'] )
			 && ( $_REQUEST['action'] == 'editpost' || $_REQUEST['action'] == 'edit' )
			 && ! empty( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'product' ) {
			return true;
		}

		return false;
	}


	/**
	 * Append the query string that is used by Sync function to determine whether to show sync dialogue
	 */
	public function add_storage_id_to_query_string( $url, $status_code ) {

		if ( ! empty( $_REQUEST['woomulti_storage_id'] ) ) {
			return add_query_arg( 'woomulti_storage_id', $_REQUEST['woomulti_storage_id'], $url );
		} else {
			return $url;
		}
	}

	public function update_parent_product_metadata( $post_id ) {
		$sites = get_option( 'woonet_child_sites' );

		foreach ( $sites as $site ) {
			$key = '_woonet_publish_to_' . $site['uuid'];

			if ( isset( $_REQUEST[ $key ] ) ) {
				update_post_meta( $post_id, $key, $_REQUEST[ $key ] );
			}

			$key = '_woonet_publish_to_' . $site['uuid'] . '_child_inheir';

			if ( isset( $_REQUEST[ $key ] ) ) {
				update_post_meta( $post_id, $key, $_REQUEST[ $key ] );
			}

			$key = '_woonet_' . $site['uuid'] . '_child_stock_synchronize';

			if ( isset( $_REQUEST[ $key ] ) ) {
				update_post_meta( $post_id, $key, $_REQUEST[ $key ] );
			}
		}
	}

	public function receive_product_from_child() {
		if ( get_option( 'woonet_network_type' ) != 'child' ) {
			return;
		}

		$sync = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
		$sync->sync_child();
	}

	public function quick_sync() {
		/**
		 * Return false to disable quick sync.
		 */
		if ( ! apply_filters( 'WOO_MSTORE/sync/quick', true ) ) {
			return;
		}

		$this->disable_realtime_sync();

		woomulti_log_error( 'Quick Sync Fired.' );

		$stores = $this->get_selected_stores( $_REQUEST );

		if ( $this->is_child_product() ) {
			$this->update_child_product_metadata( (int) $_REQUEST['post_ID'] );
		} else {
			$this->update_parent_product_metadata( (int) $_REQUEST['post_ID'] );
		}

		foreach ( $stores as $store ) {
			$sync = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
			$sync->sync( (int) $_REQUEST['post_ID'], $store );
		}

		$this->enable_realtime_sync();
	}

	/**
	 * When users are not on the edit screen and a product is updated via eg. API,
	 * or other means, product details are instantly synced via this function.
	 *
	 * When stock is updated, or order cancelled the stock will be synced via
	 * real-time sync.
	 *
	 * Stock update and cancelled order updates from the child sites are synced via
	 * stock-sync.php
	 */
	public function realtime_sync( $post_id, $ignore_uuid = null, $background_sync = true ) {
		/**
		 * Return false to disable realtime sync.
		 */
		if ( ! apply_filters( 'WOO_MSTORE/sync/realtime', true ) ) {
			return;
		}

		$this->disable_realtime_sync();

		/**
		 * Real-time sync syncs the data from the master to the child
		 */
		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			$stores = WOO_MULTISTORE()->site_manager->get_sites();
			$sync   = WOO_MULTISTORE()->sync_engine;

			woomulti_log_error( 'Real-time Sync Hook Fired (master)' );

			foreach ( $stores as $k => $store ) {
				if ( $ignore_uuid == $store['uuid'] ) {
					continue;
				}

				/**
				 * Check if sync is enabled for the particular site
				 */
				if ( WOO_MULTISTORE()->site_manager->get_type() == 'master' && $this->is_sync_enabled_for_site( $post_id, $store['uuid'] ) ) {
					$sync->sync( (int) $post_id, $store['uuid'], $background_sync );
					woomulti_log_error( "Realtime Sync Executed for product #{$post_id}, store {$store['uuid']}." );
				} else {
					woomulti_log_error( "(Realtime Sync) Product {$post_id} not marked to sync with {$store['uuid']}. Skipped sync." );
				}
			}
		} elseif ( get_option( 'woonet_network_type' ) == 'child' ) {
			woomulti_log_error( 'Sync stock with master. Request fired.' );
			$this->sync_master( $post_id );
		}

		$this->enable_realtime_sync();
	}

	/**
	 * Send child site orders to master
	 *
	 * Send child site orders to the master site to be displayed on
	 * the network order interface.
	 *
	 * @since 3.0.0
	 */
	public function send_child_orders() {
		global $wpdb;

		$per_page = 10;
		$page     = 1;

		if ( ! empty( $_REQUEST['data']['per_page'] ) ) {
			$per_page = (int) $_REQUEST['data']['per_page'];
		}

		if ( ! empty( $_REQUEST['data']['page'] ) ) {
			$page = (int) $_REQUEST['data']['page'];
		}

		if ( ! empty( $_REQUEST['data']['post_status'] ) ) {
			$post_status = $_REQUEST['data']['post_status'];
		} else {
			$post_status = '';
		}

		if ( ! empty( $_REQUEST['data']['search'] ) ) {
			$search = $_REQUEST['data']['search'];
		} else {
			$search = '';
		}

		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			woomulti_log_error( 'ORDER LIST: Authentication failed.' );

			wp_send_json(
				array(
					'status'  => 'error',
					'message' => 'You are not allowed to access this resource.',
					'result'  => '',
				)
			);
		}

		$orders = $this->get_current_site_orders( $per_page, $page, $post_status, $search );
		wp_send_json( $orders );
	}

	/**
	 * Get child orders from the current site.
	 *
	 * Get current site orders.
	 *
	 * @since 4.1.0
	 */
	public function get_current_site_orders( $per_page = 10, $page = 1, $post_status = '', $search = '' ) {
		global $wpdb;

		if ( empty( $post_status ) ) {
			$post_status = array_keys( wc_get_order_statuses() );
		}

		/**
		 * array(
		 *    'meta_key'     => '_woonet_has_synced_product',
		 *    'meta_value'   => 'yes'
		 * );
		 */

		if ( ! empty( $search ) ) {
			$post_ids = wc_order_search( wc_clean( wp_unslash( $search ) ) );

			$query = new WC_Order_Query(
				apply_filters(
					'WOO_MSTORE/network_order_query',
					array(
						'limit'    => $per_page,
						'page'     => $page,
						'order'    => 'DESC',
						'orderby'  => 'date',
						'status'   => $post_status,
						'post__in' => $post_ids,
					)
				)
			);
		} else {
			$query = new WC_Order_Query(
				apply_filters(
					'WOO_MSTORE/network_order_query',
					array(
						'limit'   => $per_page,
						'page'    => $page,
						'order'   => 'DESC',
						'orderby' => 'date',
						'status'  => $post_status,
					)
				)
			);
		}

		if ( ! empty( $search ) && empty( $post_ids ) ) {
			$orders = array();
		} else {
			$orders = $query->get_orders();
		}

		$orders_array = array();
		$site_data    = get_option( 'woonet_master_connect' );

		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			$site_data = array( 'uuid' => 'master' );
		}

		if ( ! empty( $orders ) ) {
			foreach ( $orders as $order ) {
				$order_data = $order->get_data();

				$order_data['__custom_order_id'] = apply_filters( 'woocommerce_order_number', $order->get_id(), $order );

				$items = array();

				foreach ( $order->get_items() as $item ) {
					$items[] = array_merge(
						$item->get_data(),
						array(
							'meta_data' => get_post_meta( $item->get_id() ),
						)
					);
				}

				$order_meta = array();

				foreach ( get_post_meta( $order->get_id() ) as $key => $value ) {
					$order_meta[ $key ] = isset( $value[0] ) ? $value[0] : '';
				}

				$order_data = array_merge(
					$order_data,
					array(
						'date_created'   => ! empty( $order_data['date_created'] ) ? $order_data['date_created']->date( 'Y/m/d H:i:s' ) : '',
						'date_modified'  => ! empty( $order_data['date_modified'] ) ? $order_data['date_modified']->date( 'Y/m/d H:i:s' ) : '',
						'meta_data'      => $order_meta,
						'line_items'     => $items,
						'shipping_lines' => array(), // not needed
						'shipping_method_title'  => $order->get_shipping_method(),
					),
					array(
						'uuid'       => $site_data['uuid'],
						'store_url'  => site_url(),
						'store_name' => get_bloginfo( 'name' ),
					)
				);

				$orders_array[] = apply_filters( 'WOO_MSTORE_ORDER/woocommerce_add_order_to_results', $order_data, $order );
			}
		}

		$total = $wpdb->get_var( 'SELECT count(*) as total FROM ' . $wpdb->prefix . "posts WHERE post_type IN('shop_order_refund', 'shop_order')" );

		$orders_array = apply_filters( 'WOO_MSTORE_ORDER/woocommerce_order_results', $orders_array );

		$count_by_status = (array) wp_count_posts( 'shop_order' );

		/**
		 * Single status passed from request variable.
		 */
		if ( ! is_array( $post_status ) && isset( $count_by_status[ $post_status ] ) ) {
			$selected_total = (int) $count_by_status[ $post_status ];
		} else {
			$selected_total = $total;
		}

		return array(
			'status'  => 'success',
			'message' => 'Success',
			'result'  => array(
				'page'            => $page,
				'per_page'        => $per_page,
				'count_by_status' => (array) $count_by_status,
				'total'           => $total,
				'selected_total'  => $selected_total,
				'orders'          => $orders_array,
			),
		);
	}

	/**
	 * Update order status on the child site
	 *
	 * When master initiates a request to update child status
	 * this hook runs on the child site to update the status
	 *
	 * @since 3.0.3
	 */
	public function update_child_status() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( ! $_engine->is_request_authenticated( $_POST ) ) {
			echo json_encode(
				array(
					'status'  => 'failed',
					'message' => 'Authentication failed for ' . site_url(),
				)
			);
			die;
		}

		if ( ! empty( $_POST['post_data'] ) ) {

			$post_data      = (array) $_POST['post_data'];
			$status_message = '';
			$failed         = array();
			$success        = array();

			$wc_status = wc_get_order_statuses();

			// Initialize payment gateways in case order has hooked status transition actions.
			WC()->payment_gateways();

			do_action( 'WOO_MSTORE_ORDER/handle_bulk_actions-edit-shop_order_start', $post_data );

			if ( ! empty( $post_data ) ) {
				foreach ( $post_data as $post ) {
					if ( $post['status'] == 'delete' ) {
						wp_delete_post( $post['post'], true );
						$success[] = '#' . $post['post'];
					} elseif ( $post['status'] == 'untrash' ) {
						wp_untrash_post( $post['post'] );
						$success[] = '#' . $post['post'];
					} elseif ( $post['status'] == 'trash' ) {
						wp_trash_post( $post['post'] );
						$success[] = '#' . $post['post'];
					} elseif ( array_key_exists( $post['status'], $wc_status ) ) {
						$order = wc_get_order( (int) $post['post'] );

						if ( $order && $order->update_status( $post['status'], __( 'Order status changed by WooMultistore API', 'woonet' ), true ) ) {
							$success[] = '#' . $post['post'];
							do_action( 'woocommerce_order_edit_status', $post['post'], $post['status'] );
						} else {
							$failed[] = '#' . $post['post'];
						}
					} else {
						// Custom bulk actions.
						do_action( 'WOO_MSTORE_ORDER/handle_bulk_actions-edit-shop_order', $post['status'], $post['post'] );
					}
				}
			}

			do_action( 'WOO_MSTORE_ORDER/handle_bulk_actions-edit-shop_order_end' );

			if ( ! empty( $success ) ) {
				$status_message .= 'Status for order(s) ' . implode( ',', $success ) . ' were succesfully updated on ' . site_url() . '.';
			}

			if ( ! empty( $failed ) ) {
				$status_message .= 'Status for order(s) ' . implode( ',', $failed ) . ' failed to update on ' . site_url() . '.';
			}

			echo json_encode(
				array(
					'status'  => 'success',
					'message' => $status_message,
				)
			);
		} else {
			echo json_encode(
				array(
					'status'  => 'failed',
					'message' => 'Child site (' . site_url() . ') received no data.',
				)
			);
		}

		die;
	}

	/**
	 * When a product is duplicated using WooCommerce, delete the metadata related to the plugin
	 * so that the new product is no longer linked to the old child products.
	 *
	 * @since 3.0.6
	 */
	public function unlink_duplicated_product( $duplicate, $product ) {

		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE post_id={$duplicate->get_id()} AND meta_key LIKE '%_woonet_%'" );

		// If product is variable, remove the metadata from variations as well
		if ( $duplicate->get_type() == 'variable' && ! empty( $duplicate->get_children() ) ) {
			foreach ( $duplicate->get_children() as $variation_id ) {
				$wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE post_id={$variation_id} AND meta_key LIKE '%_woonet_%'" );
			}
		}

		// if ( get_option('woonet_network_type') == 'master' ) {
		// master site and main product
		// $sites = get_option('woonet_child_sites');

		// if ( !empty($sites) ) {
		// foreach ($sites as $site ) {
		// delete_post_meta($duplicate->get_id(), '_woonet_publish_to_' . $site['uuid'] );
		// delete_post_meta($duplicate->get_id(), '_woonet_publish_to_' . $site['uuid'] . '_child_inheir' );
		// delete_post_meta($duplicate->get_id(), '_woonet_publish_to_' . $site['uuid'] . '_child_stock_synchronize' );
		// }
		// }
		// } else {
		// child product
		// $child_connect = get_option('woonet_master_connect'); // data used by the child site to identify itself in the network.

		// delete_post_meta($duplicate->get_id(), '_woonet_master_product_id');
		// delete_post_meta($duplicate->get_id(), '_woonet_network_is_child_product_id');
		// delete_post_meta($duplicate->get_id(), '_woonet_child_inherit_updates');

		// If product is variable, remove the metadata from variations as well
		// if ( $duplicate->get_type() == 'variable' && !empty($duplicate->get_children()) ) {
		// foreach ( $duplicate->get_children() as $variation_id ) {
		// child product
		// delete_post_meta( $variation_id, '_woonet_master_product_id' );
		// delete_post_meta( $variation_id, '_woonet_network_is_child_product_id' );
		// delete_post_meta( $variation_id, '_woonet_child_inherit_updates' );
		// }
		// }
		// }
	}

	/**
	 * Run the sync across stores
	 *
	 * @param mixed $product_id
	 * @return void
	 */
	public function process_product_hook( $product_id ) {
		$this->realtime_sync( $product_id, null, true );
	}

	/**
	 *
	 * Mark a new product to sync with a store and then call process_product hook to run the sync.
	 *
	 * @param integer $product_id WooCommerce product ID
	 * @param array   $stores Store IDs
	 * @param string  $child_inherit Set child inherit product change option. Valid value is either yes or no.
	 * @param string  $stock_sync Set stock sync option. Valid value is either yes or no.
	 */
	public function set_sync_options_hook( $product_id, $stores, $child_inherit = 'yes', $stock_sync = 'no' ) {
		$sites = get_option( 'woonet_child_sites' );

		foreach ( $sites as $site ) {
			if ( in_array( $site['uuid'], $stores ) ) {
				$_REQUEST[ '_woonet_publish_to_' . $site['uuid'] ]                   = 'yes';
				$_REQUEST[ '_woonet_publish_to_' . $site['uuid'] . '_child_inheir' ] = 'yes';
				$_REQUEST[ '_woonet_' . $site['uuid'] . '_child_stock_synchronize' ] = 'yes';
			} else {
				unset( $_REQUEST[ '_woonet_publish_to_' . $site['uuid'] ] );
				unset( $_REQUEST[ '_woonet_publish_to_' . $site['uuid'] . '_child_inheir' ] );
				unset( $_REQUEST[ '_woonet_' . $site['uuid'] . '_child_stock_synchronize' ] );
			}
		}
	}

	/**
	 * Get store IDs
	 */
	public function get_store_ids_filter( $ids = array() ) {
		$ids   = array();
		$sites = get_option( 'woonet_child_sites' );

		foreach ( $sites as $site ) {
			$ids[] = array(
				'id'  => $site['uuid'],
				'url' => $site['site_url'],
			);
		}

		return $ids;
	}

	public function sync_stock_hook( $product_id ) {
		$this->realtime_sync( $product_id, null, true );
	}

	/**
	 * When product is updated on the child,
	 * sync stock data only.
	 */
	public function sync_stock( $product_id ) {

		/**
		 * Override stock sync for particular site.
		 */
		if ( WOO_MULTISTORE()->options_manager->get( 'override__synchronize-stock', 'no' ) == 'yes' ) {
            return;
		}
		
		/**
		 * Do not fire sync, when user is updating various data from the sync screen.
		 */
		if ( ! empty( $_REQUEST['action'] )
			&& $_REQUEST['action'] == 'woocommerce_save_variations' ) {
			return;
		}

		if ( ! empty( $_REQUEST['action'] )
			&& $_REQUEST['action'] == 'woocommerce_save_attributes' ) {
			return;
		}

		if ( ! empty( $_REQUEST['action'] )
			&& $_REQUEST['action'] == 'woocommerce_link_all_variations' ) {
			return;
		}

		if ( ! empty( $_REQUEST['action'] )
			&& $_REQUEST['action'] == 'woocommerce_bulk_edit_variations' ) {
			return;
		}

		if ( wp_is_post_revision( $product_id ) ) {
			return;
		}

		if ( did_action( 'woocommerce_product_duplicate' ) ) {
			return;
		}

		//check if the product is a synced product
		$is_child_product = get_post_meta( $product_id, '_woonet_master_product_id', true );
		
		if ( empty( $is_child_product ) ) {
			woomulti_log_error( "(Stock Sync) Sync skipped for product {$product_id}. Not a child product." );
            return;
		}

		if ( $this->is_edit_screen() ) {
			woomulti_log_error( "(Stock Sync) Sync skipped for product {$product_id}. User editing product." );
            return;
		}

		do_action( 'WOO_MSTORE/child/stock/sync', $product_id );
	}

	public function sync_master( $product_id ) {
		$wc_product             = wc_get_product( $product_id );
		$options                = WOO_MULTISTORE()->options_manager;
		$sites                  = WOO_MULTISTORE()->site_manager;
		$utils                  = WOO_MULTISTORE()->sync_utils;
		$sync                   = WOO_MULTISTORE()->sync_engine;
		$master_connect_details = $sites->get_master_site();
		
		if (  ! $utils->is_stock_sync_required( $product_id, '' ) ) {
			return;
		}

		if ( $wc_product->get_type() == 'variable' ) {
			$variation_ids = $sync->get_all_variation_ids( $product_id );
		}

		$data = array(
			'current_stock' => $wc_product->get_stock_quantity(),
			'stock_status'  => $wc_product->get_stock_status(),
			'product_id'    => $wc_product->get_id(),
			'parent_id'     => $wc_product->get_meta( '_woonet_master_product_id', true ),
			'product_type'  => $wc_product->get_type(),
			'manage_stock'  => $wc_product->get_manage_stock(),
			'network_type'  => 'child',
		);

		if ( ! empty( $variation_ids ) ) {
			$data['variations'] = array();

			foreach ( $variation_ids as $id ) {
				$variation = wc_get_product( $id );

				$data['variations'][] = array(
					'current_stock' => $variation->get_stock_quantity(),
					'stock_status'  => $variation->get_stock_status(),
					'product_id'    => $variation->get_id(),
					'parent_id'     => $variation->get_meta( '_woonet_master_product_id', true ),
					'product_type'  => $variation->get_type(),
					'manage_stock'  => $variation->get_manage_stock(),
				);
			}
		}

		$sync->request_master( 'master_receive_updates', $data );
	}

	public function disable_realtime_sync() {
		if ( WOO_MULTISTORE()->site_manager->get_type() == 'master' ) {
			remove_action( 'woocommerce_update_product', array( $this, 'process_product' ), PHP_INT_MAX );
		}
	}

	public function enable_realtime_sync() {
		if ( WOO_MULTISTORE()->site_manager->get_type() == 'master' ) {
			add_action( 'woocommerce_update_product', array( $this, 'process_product' ), PHP_INT_MAX, 1 );
		}
	}

	public function enable_stock_sync() {
		/**
		 * Updates from master. Don't run resync.
		 */
		if ( ! empty( $_POST['action'] ) && $_POST['action'] == 'woomulti_child_payload' ) {
            woomulti_log_error( 'Updates from master. Skipped sync from child.' );
            return;
		}

		if ( WOO_MULTISTORE()->site_manager->get_type() == 'child' ) {
			add_action( 'woocommerce_update_product', array( $this, 'sync_stock' ), 99, 1 );
		}
	}

	public function disable_stock_sync() {
		if ( WOO_MULTISTORE()->site_manager->get_type() == 'child' ) {
            remove_all_actions( 'woocommerce_update_product', 99 );
		}	 
	}

	public function is_sync_enabled_for_site( $post_id, $uuid ) {
		if ( get_post_meta( $post_id, "_woonet_publish_to_{$uuid}", true ) == 'yes' ) {
            return true;
		}

        return false;
	}
}