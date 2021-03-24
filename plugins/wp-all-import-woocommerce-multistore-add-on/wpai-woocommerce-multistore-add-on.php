<?php
/*
* Plugin Name: WP All Import - WooCommerce Multistore Add-On
* Plugin URI: http://www.lykkemedia.no
* Description: Import to WooCommerce. Adds integration with WooCommerce Multistore. Requires WP All Import.
* Version: 2.0.5
* Author: Lykke Media AS
* WC tested up to: 4.1.1
*/

final class WPAI_WM_Add_On {

	/**
	 * WPAI options for the current import
	 */
	private $_options = null;

	/**
	 * Single Site Product Interface
	 *
	 * @var null
	 */
	private $product_interface = null;

	public function __construct() {

		if ( false === $this->check_if_plugins_active() ) {
			return;
		}

		add_action( 'pmwi_tab_header', array( $this, 'pmwi_tab_header' ) );
		add_action( 'pmwi_tab_content', array( $this, 'pmwi_tab_content' ) );
		add_filter( 'WOO_MSTORE_admin_product\define_fields\product_fields', array( $this, 'product_fields' ) );
		add_filter( 'pmxi_options_options', array( $this, 'pmxi_options_options' ) );

		add_action( 'pmxi_saved_post', array( $this, 'pmxi_saved_post' ), PHP_INT_MAX );
		add_action( 'wp_all_import_make_product_simple', array( $this, 'wp_all_import_make_product_simple' ), PHP_INT_MAX );

		if ( is_multisite() ) {
			add_filter( 'woocommerce_product_type_query', array( $this, 'woocommerce_product_type_query' ), 10, 2 );

			add_filter( 'WOO_MSTORE_admin_product/master_product_meta_to_update', array( $this, 'master_product_meta_to_update' ) );
			add_filter( 'WOO_MSTORE_admin_product/is_product_inherit_updates', array( $this, 'is_product_inherit_updates' ) );
			add_filter( 'WOO_MSTORE_admin_product/is_product_stock_synchronize', array( $this, 'is_product_stock_synchronize' ) );
		}

		// for non-multisite
		if ( ! is_multisite() && isset( $GLOBALS['WOO_MSTORE_SINGLE_EDITOR_INTEGRATION'] ) ) {
			$this->product_interface = $GLOBALS['WOO_MSTORE_SINGLE_EDITOR_INTEGRATION'];
		}
	}

	public function pmwi_tab_header() {
		if ( is_multisite() ) {
			global $WOO_MSTORE;
			$WOO_MSTORE->product_interface->add_multistore_tab();
		} else {
			$this->product_interface->add_multistore_tab();
		}
	}

	public function pmwi_tab_content() {
		if ( is_multisite() ) {
			global $WOO_MSTORE;
			$WOO_MSTORE->product_interface->add_multistore_panel();
		} else {
			$this->product_interface->add_multistore_panel();
		}
	}

	public function product_fields( $product_fields ) {
		if ( ! doing_action( 'pmwi_tab_content' ) ) {
			return $product_fields;
		}

		$options = $this->get_options();

		foreach ( $product_fields as $index => $product_field ) {
			if ( isset( $product_field['id'], $options[ $product_field['id'] ] ) ) {
				$value = $options[ $product_field['id'] ];

				$product_fields[ $index ]['value']   = $value;
				$product_fields[ $index ]['checked'] = ( 'yes' == $value );
			}
		}

		return $product_fields;
	}

	public function pmxi_options_options( $options ) {
		if ( is_multisite() ) {
			foreach ( $this->get_default_import_options() as $option_name => $default_value ) {
				if ( isset( $_POST['is_submitted'] ) ) {
					$value = ( isset( $_POST[ $option_name ] ) && in_array( $_POST[ $option_name ], array( 'yes', 'no' ) ) )
						? $_POST[ $option_name ]
						: $default_value;
				} else {
					$value = isset( $options[ $option_name ] ) ? $options[ $option_name ] : $default_value;
				}
				$options[ $option_name ] = $value;
			}
		} else {
			// single site.
			$sites = get_option( 'woonet_child_sites' );

			foreach ( $sites as $site ) {
				if ( isset( $_REQUEST[ '_woonet_publish_to_' . $site['uuid'] ] ) && $_REQUEST[ '_woonet_publish_to_' . $site['uuid'] ] == 'yes' ) {
					$options[ '_woonet_publish_to_' . $site['uuid'] ] = 'yes';
				}

				if ( isset( $_REQUEST[ '_woonet_publish_to_' . $site['uuid'] . '_child_inheir' ] ) && $_REQUEST[ '_woonet_publish_to_' . $site['uuid'] . '_child_inheir' ] == 'yes' ) {
					$options[ '_woonet_publish_to_' . $site['uuid'] . '_child_inheir' ] = 'yes';
				}

				if ( isset( $_REQUEST[ '_woonet_' . $site['uuid'] . '_child_stock_synchronize' ] ) && $_REQUEST[ '_woonet_' . $site['uuid'] . '_child_stock_synchronize' ] == 'yes' ) {
					$options[ '_woonet_' . $site['uuid'] . '_child_stock_synchronize' ] = 'yes';
				}
			}
		}

		return $options;
	}

	public function wp_all_import_make_product_simple( $product_id ) {
		if ( is_multisite() ) {
			do_action( 'WOO_MSTORE_admin_product/process_product', $product_id );
		} else {
			// Single site product sync.
			$_REQUEST['post_ID'] = $product_id;
			WOO_MULTISTORE()->product_sync_interface->disable_realtime_sync();
			WOO_MULTISTORE()->product_sync_interface->quick_sync();
			WOO_MULTISTORE()->product_sync_interface->enable_realtime_sync();
		}
	}

	public function pmxi_saved_post( $product_id ) {
		/**
		 * Remove Multistore default product update hook
		 */
		if ( isset( $GLOBALS['WOO_MSTORE_BULK_SYNC'] )
			 && defined( 'WOO_MSTORE_VERSION' )
			 && version_compare( WOO_MSTORE_VERSION, '4.0.0', '>=' ) ) {
			remove_action( 'woocommerce_update_product', array( $GLOBALS['WOO_MSTORE_BULK_SYNC'], 'process_product' ), PHP_INT_MAX );
		}

		// Remove hook for the single site version.
		if ( ! is_multisite()
			&& isset( $GLOBALS['WOO_MSTORE_SINGLE_NETWORK_PRODUCTS_SYNC'] )
			&& defined( 'WOO_MSTORE_VERSION' )
			&& version_compare( WOO_MSTORE_VERSION, '4.0.0', '>=' ) ) {
				remove_action( 'woocommerce_update_product', array( $GLOBALS['WOO_MSTORE_SINGLE_NETWORK_PRODUCTS_SYNC'], 'process_product' ), PHP_INT_MAX );
		}

		if ( $product = wc_get_product( $product_id ) ) {
			if ( $product_parent_id = $product->get_parent_id() ) {
				$product_id = $product->get_parent_id();
			}

			if ( is_multisite() ) {
				do_action( 'WOO_MSTORE_admin_product/process_product', $product_id );
			} else {
				// single site.
				$sites   = get_option( 'woonet_child_sites' );
				$options = $this->get_options();

				foreach ( $sites as $site ) {
					if ( isset( $options[ '_woonet_publish_to_' . $site['uuid'] ] ) && $options[ '_woonet_publish_to_' . $site['uuid'] ] == 'yes' ) {
						$_REQUEST[ '_woonet_publish_to_' . $site['uuid'] ] = 'yes';
					}

					if ( isset( $options[ '_woonet_publish_to_' . $site['uuid'] . '_child_inheir' ] ) && $options[ '_woonet_publish_to_' . $site['uuid'] . '_child_inheir' ] == 'yes' ) {
						$_REQUEST[ '_woonet_publish_to_' . $site['uuid'] . '_child_inheir' ] = 'yes';
					}

					if ( isset( $options[ '_woonet_' . $site['uuid'] . '_child_stock_synchronize' ] ) && $options[ '_woonet_' . $site['uuid'] . '_child_stock_synchronize' ] == 'yes' ) {
						$_REQUEST[ '_woonet_' . $site['uuid'] . '_child_stock_synchronize' ] = 'yes';
					}
				}
				// Single site product sync.
				$_REQUEST['post_ID'] = $product_id;
                WOO_MULTISTORE()->product_sync_interface->disable_realtime_sync();
				WOO_MULTISTORE()->product_sync_interface->quick_sync();
				WOO_MULTISTORE()->product_sync_interface->enable_realtime_sync();
			}
		}
	}

	public function woocommerce_product_type_query( $product_type, $product_id ) {
		if ( doing_action( 'WOO_MSTORE_admin_product/process_product' ) ) {
			global $wpdb;

			$query  = "SELECT p.post_type AS post_type, t.name AS product_type
			FROM {$wpdb->posts} AS p
			JOIN {$wpdb->term_relationships} AS tr ON tr.object_id = p.ID
			JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
			WHERE p.ID={$product_id} AND tt.taxonomy='product_type'";
			$result = $wpdb->get_row( $query );

			if ( empty( $result->post_type ) ) {
				$product_type = false;
			} elseif ( 'product_variation' == $result->post_type ) {
				$product_type = 'variation';
			} else {
				$product_type = $result->product_type;
			}
		}

		return $product_type;
	}

	public function master_product_meta_to_update( $meta_data ) {
		if ( doing_filter( 'WOO_MSTORE_admin_product/process_product' ) ) {
			$options = $this->get_options(); // for the admin interface.

			foreach ( $options as $key => $value ) {
				if ( preg_match( '/^_woonet_publish_to_\d+$/', $key ) ) {
					$meta_data[ $key ] = $value;
				}
			}
		}

		return $meta_data;
	}

	public function is_product_inherit_updates( $result ) {
		if ( doing_filter( 'WOO_MSTORE_admin_product/process_product' ) ) {
			$options = $this->get_options();

			if ( isset( $options[ '_woonet_publish_to_' . get_current_blog_id() . '_child_inheir' ] ) ) {
				$result = $options[ '_woonet_publish_to_' . get_current_blog_id() . '_child_inheir' ];
			} elseif ( isset( $options['_woonet_child_inherit_updates'] ) ) {
				$result = $options['_woonet_child_inherit_updates'];
			}
		}

		return $result;
	}

	public function is_product_stock_synchronize( $result ) {
		if ( doing_filter( 'WOO_MSTORE_admin_product/process_product' ) ) {
			$options = $this->get_options();

			if ( isset( $options[ '_woonet_' . get_current_blog_id() . '_child_stock_synchronize' ] ) ) {
				$result = $options[ '_woonet_' . get_current_blog_id() . '_child_stock_synchronize' ];
			} elseif ( isset( $options['_woonet_child_stock_synchronize'] ) ) {
				$result = $options['_woonet_child_stock_synchronize'];
			}
		}

		return $result;
	}

	private function get_default_import_options() {
		static $default_import_options = array();

		if ( ! empty( $default_import_options ) ) {
			return $default_import_options;
		}

		$option_names = array( '_woonet_publish_to_%d', '_woonet_publish_to_%d_child_inheir', '_woonet_%d_child_stock_synchronize' );

		$blog_ids = WOO_MSTORE_functions::get_active_woocommerce_blog_ids();
		foreach ( $blog_ids as $blog_id ) {
			foreach ( $option_names as $option_name ) {
				$default_import_options[ sprintf( $option_name, $blog_id ) ] = 'no';
			}
		}

		$default_import_options['woonet_toggle_all_sites']                     = 'no';
		$default_import_options['woonet_toggle_child_product_inherit_updates'] = 'no';

		return $default_import_options;
	}

	/**
	 * Get import options from session
	 */
	private function get_options() {
		if ( $this->_options !== null ) {
			return $this->_options;
		}

		if ( ! empty( PMXI_Plugin::$session ) ) {
			$this->_options = PMXI_Plugin::$session->options; // not available when running via cron.
		} else {
			$import         = new PMXI_Import_Record();
			$options        = $import->getById( $_GET['import_id'] );
			$this->_options = $options->options;
		}

		return $this->_options;
	}

	/**
	 * Check if required plugins are active.
	 *
	 * @return void
	 */
	public function check_if_plugins_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( 'wp-all-import/plugin.php' )
			|| ! is_plugin_active( 'woocommerce-multistore/woocommerce-multistore.php' ) ) {
			return;
		}

		return true;
	}
}

new WPAI_WM_Add_On();
