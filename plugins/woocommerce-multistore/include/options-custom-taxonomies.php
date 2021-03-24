<?php

class WOO_MSTORE_SINGLE_OPTIONS_CUSTOM_TAXONOMIES {

	public function __construct() {
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'add_submenu_multisite' ), PHP_INT_MAX );
			add_action( 'admin_head', array( $this, 'remove_set_taxonomy_from_menu' ), PHP_INT_MAX );
		} else {
			add_action( 'admin_menu', array( $this, 'add_submenu_single' ), PHP_INT_MAX );
			add_action( 'admin_head', array( $this, 'remove_set_taxonomy_from_menu' ), PHP_INT_MAX );
		}
	}


	/**
	 * Add submenu to single site.
	 * @single-site
	 */
	public function add_submenu_single() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! woomulti_has_valid_license() ) {
			return;
		}

		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			$hook_id = add_submenu_page(
				'woonet-woocommerce',
				'Custom Taxonomy & Metadata Settings',
				'Custom Taxonomy & Metadata Settings',
				'manage_options',
				'woonet-set-taxonomy',
				array( $this, 'custom_taxonomies_setting' )
			);
			add_action( 'load-' . $hook_id, array( $this, 'options_update_single' ) );
		}
	}

	/**
	 * Add submenu to multisite.
	 * @multi-site
	 */
	public function add_submenu_multisite() {
		// if ( ! current_user_can( 'manage_options' ) ) {
		// 	return;
		// }

		//@todo: check license.

		$hook_id = add_submenu_page(
			'woonet-woocommerce',
			'Custom Taxonomy & Metadata Settings',
			'Custom Taxonomy & Metadata Settings',
			'manage_options',
			'woonet-set-taxonomy',
			array( $this, 'custom_taxonomies_setting' )
		);

		add_action( 'load-' . $hook_id, array( $this, 'options_update_multisite' ) );
	}

	/**
	 * Remove_set_taxonomy_from_menu
	 * @shared-sites
	 * @return void
	 */
	public function remove_set_taxonomy_from_menu() {
		remove_submenu_page( 'woonet-woocommerce', 'woonet-set-taxonomy' );
	}

	/**
	 * Custom_taxonomies_setting
	 *
	 * @return void
	 */
	public function custom_taxonomies_setting() {
		if ( is_multisite() ) {
			$woo_mstore_custom_taxonomies = $this->get_taxonomies();
            require_once WOO_MSTORE_PATH . '/templates/ms-settings-custom-taxonomy-metadata.php';
		} else {
			$GLOBALS['WOO_MSTORE_CUSTOM_TAXONOMIES'] = $this->get_taxonomies();
			woomulti_get_template_parts( 'admin-taxonomy-settings' );
		}
	}

	/**
	 * Custom_taxonomies_setting
	 * @for single-site
	 * @return void
	 */
	public function options_update_single() {
		if ( empty( $_REQUEST['Submit'] ) ) {
			return false;
		}

		if ( empty( $_REQUEST['_mstore_form_submit_taxonomies_nonce'] )
			|| ! wp_verify_nonce( $_REQUEST['_mstore_form_submit_taxonomies_nonce'], 'mstore_form_submit_taxonomies' ) ) {
			wp_die( 'You are not allowed to access this page.' );
		}

		$options = new WOO_MSTORE_OPTIONS_MANAGER();

		if ( $options->get( 'sync-custom-taxonomy' ) == 'yes' ) {
			if ( ! empty( $_REQUEST['__woonet_tax_settings'] ) ) {
				update_option( 'woonet_settings_custom_taxonomy', $_REQUEST['__woonet_tax_settings'] );
			} else {
				update_option( 'woonet_settings_custom_taxonomy', array() );
			}
		}

		if ( $options->get( 'sync-custom-metadata' ) == 'yes' ) {
			if ( ! empty( $_REQUEST['__woonet_settings_custom_metadata'] ) ) {
				update_option( 'woonet_settings_custom_metadata', $_REQUEST['__woonet_settings_custom_metadata'] );
			} else {
				update_option( 'woonet_settings_custom_metadata', '' );
			}
		}
	}

	/**
	 * Custom_taxonomies_setting
	 * @for multisite
	 * @return void
	 */
	public function options_update_multisite() {
		global $WOO_MSTORE;

		if ( empty( $_REQUEST['Submit'] ) ) {
			return false;
		}

		if ( empty( $_REQUEST['_mstore_form_submit_taxonomies_nonce'] )
			|| ! wp_verify_nonce( $_REQUEST['_mstore_form_submit_taxonomies_nonce'], 'mstore_form_submit_taxonomies' ) ) {
			wp_die( 'You are not allowed to access this page.' );
		}

		
		$options = $WOO_MSTORE->functions->get_options(); 

		if ( isset( $options['sync-custom-taxonomy'] ) && $options['sync-custom-taxonomy'] == 'yes' ) {
			if ( ! empty( $_REQUEST['__woonet_settings_custom_taxonomies'] ) ) {
				update_site_option( 'woonet_settings_custom_taxonomy', $_REQUEST['__woonet_settings_custom_taxonomies'] );
			} else {
				update_site_option( 'woonet_settings_custom_taxonomy', '' );
			}
		}

		if (  isset($options['sync-custom-metadata']) && $options['sync-custom-metadata'] == 'yes' ) {
			if ( ! empty( $_REQUEST['__woonet_settings_custom_metadata'] ) ) {
				update_site_option( 'woonet_settings_custom_metadata', $_REQUEST['__woonet_settings_custom_metadata'] );
			} else {
				update_site_option( 'woonet_settings_custom_metadata', '' );
			}
		}
	}

	/**
	 * Get_taxonomies
	 *
	 * @return array
	 */
	public function get_taxonomies() {
		$taxonomies        = get_taxonomies();
		$system_taxonomies = array(
			'category',
			'post_tag',
			'nav_menu',
			'link_category',
			'post_format',
			'product_type',
			'product_visibility',
			'product_cat',
			'product_tag',
			'product_shipping_class',
		);

		$_filterd_taxonomies = array();

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $tax ) {
				if ( in_array( $tax, $system_taxonomies ) || substr( $tax, 0, 3 ) == 'pa_' ) {
					continue;
				}

				$_filterd_taxonomies[] = $tax;
			}
		}

		return $_filterd_taxonomies;
	}
}

$GLOBALS['WOO_MSTORE_SINGLE_OPTIONS_CUSTOM_TAXONOMIES'] = new WOO_MSTORE_SINGLE_OPTIONS_CUSTOM_TAXONOMIES();
