<?php
/**
 * Plugin Name: Bulk Sync for WooMultistore
 * Description: A WooMultistore extension to sync all products in bulk.
 * Author: Lykke Media AS
 * Author URI: https://woomultistore.com/
 * Version: 1.0.7
 * WC tested up to: 5.5
 **/

define( 'ADDON_WOOCOMMERCE_MULTISTORE_BULK_SYNC', '1.0.6' );

class ADDON_WOOCOMMERCE_MULTISTORE_BULK_SYNC {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ), PHP_INT_MAX );
	}

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_run_woomulti_bulk_sync', array( $this, 'sync' ) );
		add_action( 'wp_ajax_cancel_woomulti_bulk_sync', array( $this, 'cancel_sync' ) );

		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'add_submenu' ), PHP_INT_MAX );
			add_action( 'admin_menu', array( $this, 'add_submenu' ), PHP_INT_MAX );
		} elseif ( get_option( 'woonet_network_type' ) == 'master' ) {
			add_action( 'admin_menu', array( $this, 'add_submenu_non_multisite' ), PHP_INT_MAX );
		}

		if ( defined( 'WOO_MSTORE_BULK_SYNC_VERSION' ) ) {
			add_action( 'admin_notices', array( $this, 'show_admin_notice_to_remove_old_version' ) );
			add_action( 'network_admin_notices', array( $this, 'show_admin_notice_to_remove_old_version' ) );
		}
	}

	public function enqueue_assets() {
		if ( is_admin() ) {
			wp_register_style( 'woomulti-bulk-sync-css', plugins_url( '/assets/main.css', __FILE__ ), array(), ADDON_WOOCOMMERCE_MULTISTORE_BULK_SYNC );
			wp_enqueue_style( 'woomulti-bulk-sync-css' );

			wp_register_script( 'woomulti-bulk-sync-js', plugins_url( '/assets/main.js', __FILE__ ), array(), ADDON_WOOCOMMERCE_MULTISTORE_BULK_SYNC );
			wp_enqueue_script( 'woomulti-bulk-sync-js' );

			wp_enqueue_script( 'jquery-ui-progressbar' );
		}
	}

	public function show_admin_notice_to_remove_old_version() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Please, remove the older version of the WooCommerce Multistore Bulk Sync Addon.', 'woonet' ); ?></p>
		</div> 
		<?php
	}

	public function add_submenu() {
		// only if superadmin
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( is_network_admin() ) {
			$hookname = add_submenu_page(
				'woonet-woocommerce',
				'Bulk Sync',
				'Bulk Sync',
				'manage_options',
				'woonet-bulk-sync-products',
				array( $this, 'menu_callback_bulk_sync_all_menu' )
			);
		} else {
			$hookname = add_submenu_page(
				'woocommerce',
				'Bulk Sync',
				'Bulk Sync',
				'manage_options',
				'woonet-bulk-sync-products',
				array( $this, 'menu_callback_bulk_sync_all_menu' )
			);

		}
	}

	public function add_submenu_non_multisite() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$hookname = add_submenu_page(
			'woonet-woocommerce',
			'Bulk Sync',
			'Bulk Sync',
			'manage_options',
			'woonet-bulk-sync-products',
			array( $this, 'menu_callback_bulk_sync_all_menu' )
		);
	}

	public function menu_callback_bulk_sync_all_menu() {
		require_once dirname( __FILE__ ) . '/views/network-bulk-sync-page.php';
	}

	public function get_ids() {
		$product_ids = get_posts(
			array(
				'post_type'   => 'product',
				'numberposts' => -1,
				'post_status' => 'publish',
				'fields'      => 'ids',
			)
		);

		return $product_ids;
	}

	/**
	 * sync for multisite
	 *
	 * @return void
	 */
	public function sync() {

		if ( ! is_multisite() ) {
			return $this->sync_non_multisite();
		}

		if ( ! empty( $_POST['data'] ) ) {
			$params = array();
			parse_str( $_POST['data'], $params );
			$queue_id = $params['queue_id'] = uniqid();

			$query = new WP_Query();

			if ( is_array( $params ) && count( $params ) >= 1 ) {
				// switch to parent blog
				switch_to_blog( (int) $params['select-parent-site'] );
				// we can proceed with sync
				if ( ! empty( $params['select-all-products'] ) ) {
					$products = $query->query(
						array(
							'fields'         => 'ids',
							'posts_per_page' => -1,
							'post_type'      => 'product',
						)
					);
				} else {
					// category selected
					$products = $query->query(
						array(
							'fields'         => 'ids',
							'posts_per_page' => -1,
							'post_type'      => 'product',
							'tax_query'      => array(
								array(
									'taxonomy' => 'product_cat',
									'field'    => 'id',
									'terms'    => $params['select_categories'],
								),
							),
						)
					);
				}

				$this->delete_transient_from_all_blogs();
				set_transient( 'woomulti_bulk_sync_product_data', $products, 60 * 60 * 24 );
				set_transient( 'woomulti_bulk_sync_params', $params, 60 * 60 * 24 );

				echo json_encode(
					array(
						'message'  => 'Sync Settings Saved.',
						'status'   => 'in-progress',
						'queue_id' => $queue_id,
					)
				);
				die;
			}
		} else {
			$params   = get_transient( 'woomulti_bulk_sync_params' );
			$products = get_transient( 'woomulti_bulk_sync_product_data' );

			$current_product = array_shift( $products );

			// update transient
			set_transient( 'woomulti_bulk_sync_product_data', $products, 60 * 60 * 24 );
			set_transient( 'woomulti_bulk_sync_params', $params, 60 * 60 * 24 );

			// switch to the target blog
			switch_to_blog( (int) $params['select-parent-site'] );

			if ( ! empty( $current_product ) ) {
				// set metadata
				update_post_meta( $current_product, '_woonet_network_main_product', 'yes' );

				foreach ( $params['select_child_sites'] as $cs ) {
					update_post_meta( $current_product, '_woonet_publish_to_' . $cs, 'yes' );

					// set the variable that child sites will read to set parent sync status
					$_REQUEST[ '_woonet_publish_to_' . $cs ]                   = 'yes';
					$_REQUEST[ '_woonet_publish_to_' . $cs . '_child_inheir' ] = ! empty( $params['child-sync'] ) ? $params['child-sync'] : 'no';
					$_REQUEST[ '_woonet_' . $cs . '_child_stock_synchronize' ] = ! empty( $params['stock-sync'] ) ? $params['stock-sync'] : 'no';
				}

				do_action( 'WOO_MSTORE_admin_product/process_product', $current_product );

				// update transient
				// set_transient('woomulti_bulk_sync_product_data', $products, 60*60*24);
				// set_transient('woomulti_bulk_sync_params', $params, 60*60*24);

				// send response
				echo json_encode(
					array(
						'message' => (int) count( $products ) . ' products remain to be synced.',
						'status'  => 'in-progress',
					)
				);
				die;
			}

			if ( count( $products ) == 0 ) {
				// send response
				echo json_encode(
					array(
						'message' => 'Sync completed.',
						'status'  => 'completed',
					)
				);
			}
			die;
		}
	}

	/**
	 * Sync for single site
	 *
	 * @return void
	 */
	public function sync_non_multisite() {
		if ( ! empty( $_POST['data'] ) ) {
			$params = array();
			parse_str( $_POST['data'], $params );
			$queue_id = $params['queue_id'] = uniqid();

			$query = new WP_Query();

			if ( is_array( $params ) && count( $params ) >= 1 ) {
				if ( ! empty( $params['select-all-products'] ) ) {
					$products = $query->query(
						array(
							'fields'         => 'ids',
							'posts_per_page' => -1,
							'post_type'      => 'product',
						)
					);
				} else {
					// category selected.
					$products = $query->query(
						array(
							'fields'         => 'ids',
							'posts_per_page' => -1,
							'post_type'      => 'product',
							'tax_query'      => array(
								array(
									'taxonomy' => 'product_cat',
									'field'    => 'id',
									'terms'    => $params['select_categories'],
								),
							),
						)
					);
				}

				$this->delete_transient_from_all_blogs();
				set_transient( 'woomulti_bulk_sync_product_data', $products, 60 * 60 * 24 );
				set_transient( 'woomulti_bulk_sync_params', $params, 60 * 60 * 24 );

				echo json_encode(
					array(
						'message'  => 'Sync Settings Saved.',
						'status'   => 'in-progress',
						'queue_id' => $queue_id,
					)
				);
				die;
			}
		} else {
			$params   = get_transient( 'woomulti_bulk_sync_params' );
			$products = get_transient( 'woomulti_bulk_sync_product_data' );

			$current_product = array_shift( $products );

			// update transient.
			set_transient( 'woomulti_bulk_sync_product_data', $products, 60 * 60 * 24 );
			set_transient( 'woomulti_bulk_sync_params', $params, 60 * 60 * 24 );

			if ( ! empty( $current_product ) ) {
				foreach ( $params['select_child_sites'] as $cs ) {
					$_REQUEST[ '_woonet_publish_to_' . $cs ]                   = 'yes';
					$_REQUEST[ '_woonet_publish_to_' . $cs . '_child_inheir' ] = ! empty( $params['child-sync'] ) ? $params['child-sync'] : 'no';
					$_REQUEST[ '_woonet_' . $cs . '_child_stock_synchronize' ] = ! empty( $params['stock-sync'] ) ? $params['stock-sync'] : 'no';
				}

				// Single site product sync.
				$_REQUEST['post_ID'] = $current_product;
                WOO_MULTISTORE()->product_sync_interface->disable_realtime_sync();
				WOO_MULTISTORE()->product_sync_interface->quick_sync();
				WOO_MULTISTORE()->product_sync_interface->enable_realtime_sync();

				// send response.
				echo json_encode(
					array(
						'message' => 'Synced #' . absint( $current_product ) . '. Next: #' . (int) array_shift( $products ) . '. Total remaining: ' . (int) count( $products ),
						'status'  => 'in-progress',
					)
                );
				die;
			}

			if ( count( $products ) == 0 ) {
				// send response
				echo json_encode(
					array(
						'message' => 'Sync completed.',
						'status'  => 'completed',
					)
				);
			}
			die;
		}
	}

	private function delete_transient_from_all_blogs() {

		if ( is_multisite() ) {
			$get_site_ids    = get_sites();
			$current_blog_id = get_current_blog_id();

			// loop through the blog IDs and delete transient from each
			foreach ( $get_site_ids as $id ) {
				switch_to_blog( $id->blog_id );
				delete_transient( 'woomulti_bulk_sync_product_data' );
				delete_transient( 'woomulti_bulk_sync_params' );
			}

			// switch to the original blog ID
			switch_to_blog( $current_blog_id );

		} else {
			delete_transient( 'woomulti_bulk_sync_product_data' );
			delete_transient( 'woomulti_bulk_sync_params' );
		}
	}

	public function cancel_sync() {
		$this->delete_transient_from_all_blogs();
	}
}

new ADDON_WOOCOMMERCE_MULTISTORE_BULK_SYNC();
