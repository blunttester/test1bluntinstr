<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOO_MSTORE_functions {
	public static $instance;

    /**
     * WOO_MSTORE_functions constructor.
     * @param bool $init_hooks
     */
	function __construct($init_hooks = true) {
		self::$instance = $this;

		if ( $init_hooks === true )
        {
            //add specific classes for list table within the admin
            add_filter( 'post_class', array( $this, 'post_class' ), 10, 3 );
        }
	}

	/**
	 * Check if a plugin is active
	 *
	 * @param mixed $plugin
     * @return boolean
	 */
	static public function is_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || self::is_plugin_active_for_network( $plugin );
	}

	static public function is_plugin_active_for_network( $plugin ) {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[ $plugin ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns plugin options
	 *
	 * @return array
	 */
	public static function get_options() {
		$mstore_options = get_site_option( 'mstore_options' );

		$defaults = array(
			'version'                                                 => '',
			'db_version'                                              => '1.0',
			/**********************************************************************/
			'synchronize-stock'                                       => 'no',
			'synchronize-trash'                                       => 'no',
			'sequential-order-numbers'                                => 'no',
			'publish-capability'                                      => 'administrator',
			'network-user-info'                                       => 'yes',
			/**********************************************************************/
			'child_inherit_changes_fields_control__title'             => array(),
			'child_inherit_changes_fields_control__description'       => array(),
			'child_inherit_changes_fields_control__short_description' => array(),
			'child_inherit_changes_fields_control__price'             => array(),
			'child_inherit_changes_fields_control__product_cat'       => array(),
			'child_inherit_changes_fields_control__product_tag'       => array(),
			'child_inherit_changes_fields_control__variations'        => array(),
			'child_inherit_changes_fields_control__attributes'        => array(),
			'child_inherit_changes_fields_control__category_changes'  => array(),
			'child_inherit_changes_fields_control__reviews'           => array(),
			'child_inherit_changes_fields_control__slug'              => array(),
			'child_inherit_changes_fields_control__purchase_note'     => array(),
		);

		// Parse incoming $args into an array and merge it with $defaults
		$options = wp_parse_args( $mstore_options, $defaults );

		//ensure the child_inherit_changes_fields_control__title is available for all sites
		$blog_ids = self::get_active_woocommerce_blog_ids();
		foreach ( $blog_ids as $blog_id ) {
			if ( ! isset( $options['child_inherit_changes_fields_control__title'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__title'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__description'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__description'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__short_description'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__short_description'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__price'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__price'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__product_cat'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__product_cat'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__product_tag'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__product_tag'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__variations'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__variations'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__attributes'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__attributes'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__category_changes'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__category_changes'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__reviews'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__reviews'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__slug'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__slug'][ $blog_id ] = 'yes';
			}
			if ( ! isset( $options['child_inherit_changes_fields_control__purchase_note'][ $blog_id ] ) ) {
				$options['child_inherit_changes_fields_control__purchase_note'][ $blog_id ] = 'yes';
			}
		}

		return $options;
	}

	/**
	 * Update plugin options
	 *
	 * @param array $options
	 */
	function update_options( $options ) {
		update_site_option( 'mstore_options', $options );
	}

	function post_class( $classes, $class, $post_ID ) {
		if ( ! is_admin() ) {
			return $classes;
		}

		$post_data = get_post( $post_ID );

		if ( $post_data->post_type != 'product' ) {
			return $classes;
		}

		//check if it's child product
		$_woonet_network_is_child_product_id = get_post_meta( $post_ID, '_woonet_network_is_child_product_id', true );

		if ( ! empty( $_woonet_network_is_child_product_id ) ) {
			$classes[] = 'ms-child-product';
		}

		return $classes;
	}

	/**
	 * Check if current user can use plugin Publish functionality
	 */
	function publish_capability_user_can() {
		$options = $this->get_options();

		switch ( $options['publish-capability'] ) {
			case 'super-admin':
				if ( ! is_super_admin() ) {
					return false;
				}
				break;
			case 'administrator':
				if ( ! current_user_can( 'administrator' ) ) {
					return false;
				}
				break;
			case 'shop_manager':
				if ( ! current_user_can( 'shop_manager' ) && ! current_user_can( 'administrator' ) ) {
					return false;
				}
				break;
		}

		return true;
	}

	public static function get_active_woocommerce_blog_ids() {
		static $blog_ids = null;

		if ( ! is_null( $blog_ids ) ) {
			return $blog_ids;
		}

		// Makes sure the plugin is defined before trying to use it
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		$blog_ids = array();

		$current_blog_id = get_current_blog_id();

		$get_sites_args = array(
			'number'   => 999,
			'fields'   => 'ids',
			'archived' => 0,
			'spam'     => 0,
			'deleted'  => 0,
		);
		$sites = get_sites( $get_sites_args );
		foreach ( $sites as $blog_id ) {
			if ( $current_blog_id != $blog_id ) {
				switch_to_blog( $blog_id );
			}

			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				$blog_ids[] = $blog_id;
			}

			if ( $current_blog_id != $blog_id ) {
				restore_current_blog();
			}
		}

		return $blog_ids;
	}

	/**
	 * Get a list of network site IDs
	 * @since 3.0.6
	 * @return Array array of site IDs 
	 */
	public function get_sites() {
		$_args = array(
			'number'   => 999,
			'fields'   => 'ids',
			'archived' => 0,
			'spam'     => 0,
			'deleted'  => 0,
		);

		return  get_sites( $_args );
	}
}
