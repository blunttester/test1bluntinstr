<?php

/**
 * Network Bulk Updater
 *
 * @class   WOO_MSTORE_BULK_SYNC
 * @since   2.0.20
 */

class WOO_MSTORE_BULK_SYNC {

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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		if ( function_exists( 'is_network_admin' ) && is_network_admin() ) {
			add_action( 'network_admin_notices', array( $this, 'set_admin_notice' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'set_admin_notice' ) );
		}

		add_action( 'wp_ajax_woomulti_cancel_sync', array( $this, 'cancel_sync' ) );
		add_action( 'wp_ajax_woomulti_process_job', array( $this, 'ajax_process_job' ) );
		add_filter( 'wp_redirect', array( $this, 'add_storage_id_to_query_string' ), PHP_INT_MAX, 2 );

		if ( is_admin() ) {
			add_action( 'woocommerce_update_product', array( $this, 'process_product' ), PHP_INT_MAX, 1 );
		}

		$this->product_updater = new WOO_MSTORE_admin_product( false );
		$this->functions       = new WOO_MSTORE_functions( false );
	}

	/**
	 * Enqueue assets for the the updater
	 */
	public function enqueue_assets() {
		if ( is_admin() ) {
			wp_register_style( 'woomulti-speed-css', plugins_url( '/assets/css/speed-updater.css', dirname( __FILE__ ) ), array(), WOO_MSTORE_VERSION );
			wp_enqueue_style( 'woomulti-speed-css' );

			wp_register_script( 'woomulti-speed-js', plugins_url( '/assets/js/speed-updater.js', dirname( __FILE__ ) ), array(), WOO_MSTORE_VERSION );
			wp_enqueue_script( 'woomulti-speed-js' );

			wp_enqueue_script( 'jquery-ui-progressbar' );
		}
	}

	/**
	 * Save submitted options for products in the database from the bulk editor
	 */
	public function process_product( $post_id ) {
		/**
		 * If user do not have permission to access multisite features,
		 * prevent update of product across the network.
		 */
		if ( is_user_logged_in() && ! $this->functions->publish_capability_user_can() ) {
			return false;
		}

		/**
		 * 1. If WOO_MSTORE_admin_product/process_product or WOO_MSTORE_admin_product/process_slave_product
		 * hook is fired, do not use this method to update product.
		 *
		 * 2. Do not run the hook when products are being updated via AJAX.
		 *
		 * 3. Do not run the hook when variations are beins saved.
		 *
		 * 4. Do not run the hook when product revision are being saved.
		 *
		 * 5. When products are updated using bulk editor this hooks
		 * fires for each product. We set the required parameters for AJAX update,
		 * the first time the hook is fired, there is no need to process it a second time.
		 */
		if ( doing_action( 'WOO_MSTORE_admin_product/process_product' )
			|| doing_action( 'WOO_MSTORE_admin_product/process_slave_product' )
			|| $this->is_ajax_sync()
			|| ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'woocommerce_save_variations' )
			|| wp_is_post_revision( $post_id )
			|| ! empty( $_REQUEST['woomulti_request_processed'] ) ) {
			return false;
		}

		/**
		 * We are removing the update hook. So that it doesn't
		 * fire again for the child product is saved by this function.
		 */
		$this->remove_product_update_hook();

		/**
		 * Use the legacy updater on quick edit
		 */
		if ( $this->is_quick_edit() ) {
			$this->product_updater->process_product( $post_id );
		} elseif ( ! $this->is_edit_screen() ) {
			$this->product_updater->process_product( $post_id );
		}

		/**
		 * If the product being updated is a child product
		 * Update its metadata.
		 */

		if ( $this->is_child_product( $post_id ) ) {
			$this->update_child_product_metadata( $post_id );
		}

		if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'editpost' ) {
			$_REQUEST['post'] = (array) $_REQUEST['post_ID'];
		}

		if ( ! empty( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) >= 1 ) {
			$_REQUEST['total_products'] = count( $_REQUEST['post'] );

			$selected_stores = $this->get_selected_stores( $_REQUEST );

			if ( ! empty( $selected_stores ) && $this->store_update_options( $_REQUEST, $selected_stores ) ) {
				$_REQUEST['woomulti_request_processed'] = true; // request processed once. Don't process for every product in the array.
			}
		}

		/**
		 * Product saved, child products synced.
		 * Now, we set the hook again for the next product.
		 */
		$this->add_product_update_hook();
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
		$data['site_id']         = get_current_blog_id();
		$data['selected_stores'] = $selected_stores;
		$data['post_to_update']  = $this->generate_post_array_to_update( $data, $selected_stores );

		if ( $this->is_bulk_edit() ) {
			$data['is_bulk_edit'] = 'yes';
		}

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

			if ( ( isset( $transient['site_id'] ) && $transient['site_id'] == get_current_blog_id() ) || is_network_admin() ) {
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
		define( 'WOOMULTI_AJAX_PROCESSING_JOB', true );

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

			foreach ( $next_post as $p ) {
				$this->process_master_meta( $update_config, $p['post_id'], $p['store_id'] );
				$this->product_updater->process_ajax_product( $p['post_id'], $p['store_id'], WOOMULTI_MAX_SITE_PER_REQUEST );
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
		if ( ! empty( $data['is_bulk_edit'] ) && empty( $data[ '_woonet_publish_to_' . $store_id ] ) ) {
			/**
			 * Use Product Settings selected in bulk edit mode. No need to update master meta.
			 */
			return;
		}

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

		$active_blog_ids = $this->functions->get_active_woocommerce_blog_ids();

		foreach ( $active_blog_ids as $blog_id ) {
			if ( $this->is_bulk_edit() ) {
				$selected_stores[] = $blog_id;
			} elseif ( isset( $data[ '_woonet_publish_to_' . $blog_id ] ) && $data[ '_woonet_publish_to_' . $blog_id ] == 'yes' ) {
				$selected_stores[] = $blog_id;
			} elseif ( isset( $data[ '_woonet_publish_to_' . $blog_id ] ) && $data[ '_woonet_publish_to_' . $blog_id ] == 'no' ) {
				if ( $this->is_sync_required( $data, $blog_id ) && ! in_array( $blog_id, $selected_stores ) ) {
					$selected_stores[] = $blog_id;
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
	 */
	private function delete_transient_from_all_blogs() {
		$get_site_ids    = get_sites();
		$current_blog_id = get_current_blog_id();

		// loop through the blog IDs and delete transient from each
		foreach ( $get_site_ids as $id ) {
			switch_to_blog( $id->blog_id );
			delete_transient( 'woomulti_product_sync_queue' );
		}

		// switch to t he original blog ID
		switch_to_blog( $current_blog_id );
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
	public function is_child_product( $post_id ) {
		$parent_id = get_post_meta( $post_id, '_woonet_network_is_child_product_id', true );

		if ( ! empty( $parent_id ) ) {
			return true;
		}

		return false;
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
		/**
		 * Network edit page
		 */
		if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woonet-woocommerce-products' && ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
			return true;
		}

		/**
		 * edit product page or bulk editor
		 */
		if ( ! empty( $_REQUEST['action'] )
			 && ( $_REQUEST['action'] == 'editpost' || $_REQUEST['action'] == 'edit' )
			 && ! empty( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'product' ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if product sync is running
	 */
	public function is_ajax_sync() {

		if ( defined( 'WOOMULTI_AJAX_PROCESSING_JOB' ) ) {
			return true;
		}

		if ( ! isset( $_REQUEST['action'] ) ) {
			return false;
		}

		if ( $_REQUEST['action'] == 'woomulti_process_job' || $_REQUEST['action'] == 'woomulti_cancel_sync' ) {
			return true;
		}

		return false;
	}

	/**
	 * If its quick edit invoke the legacy update function
	 */
	public function is_quick_edit() {
		if ( ! empty( $_REQUEST['woocommerce_quick_edit'] ) && $_REQUEST['woocommerce_quick_edit'] == 1
			 && ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'inline-save' ) {
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

	/**
	 * Check if current action is bulk edit
	 *
	 * @return boolean
	 */
	public function is_bulk_edit() {
		if ( ! empty( $_REQUEST['bulk_edit'] ) && $_REQUEST['bulk_edit'] == 'Update'
			&& ! empty( $_REQUEST['screen'] ) && $_REQUEST['screen'] == 'edit-product'
			&& ! empty( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'product' ) {
				return true;
		}

		/**
		 * Handle bulk edit from network product screen.
		 */
		if ( ! empty( $_REQUEST['woocommerce_bulk_edit'] ) && $_REQUEST['woocommerce_bulk_edit'] == 1
			&& ! empty( $_REQUEST['screen'] ) && $_REQUEST['screen'] == 'woocommerce_page_woonet-woocommerce-products'
			&& ! empty( $_REQUEST['bulk_edit'] ) && $_REQUEST['bulk_edit'] == 'Update' ) {
				return true;
		}

		return false;
	}

	/**
	 * Check if an AJAX sync has triggerd or started
	 */
	public function is_sync_running() {
		if ( ! empty( $_REQUEST['woomulti_storage_id'] ) ) {
			return;
		}
	}

	/**
	 * Add update hook
	 *
	 * Add the hook back after product update is done.
	 */
	public function add_product_update_hook() {
		if ( is_admin() ) {
			add_action( 'woocommerce_update_product', array( $this, 'process_product' ), PHP_INT_MAX, 1 );
		}
	}

	/**
	 * Remove update hook.
	 *
	 * Child product are updated by the plugin,
	 * prevent firing of woocommerce_update_product for child products.
	 */
	public function remove_product_update_hook() {
		if ( is_admin() ) {
			remove_action( 'woocommerce_update_product', array( $this, 'process_product' ), PHP_INT_MAX );
		}
	}
}

$GLOBALS['WOO_MSTORE_BULK_SYNC'] = new WOO_MSTORE_BULK_SYNC();
