<?php

/**
 * Settings page for the plugin
 *
 * @since 3.0.0
 * @package WooMultistore
 */
class WOO_MSTORE_options_interface {

	function __construct() {
		add_action( 'init', array( $this, 'init' ), 10, PHP_INT_MAX );
	}

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_submenu' ), PHP_INT_MAX );
	}

	public function add_settings_submenu() {
		if ( ! woomulti_has_valid_license() ) {
			return;
		}

			$hookID = add_submenu_page(
				'woonet-woocommerce',
				'Settings',
				'Settings',
				'manage_options',
				'woonet-woocommerce-settings',
				array(
					$this,
					'options_interface',
				),
				6
			);

			add_action( 'load-' . $hookID, array( $this, 'options_update' ) );
	}

	public function options_interface() {
		woomulti_get_template_parts( 'settings-page' );
	}

	public function options_update() {
		if ( isset( $_POST['mstore_form_submit'] ) ) {
				// check nonce
			if ( ! wp_verify_nonce( $_POST['mstore_form_nonce'], 'mstore_form_submit' ) ) {
				return;
			}

			$options = get_option( 'woonet_options', array() );

			global $mstore_form_submit_messages;

			if ( get_option( 'woonet_network_type' ) != 'child' ) {
				$options['synchronize-stock']  = isset( $_POST['synchronize-stock'] ) ? $_POST['synchronize-stock'] : 'no';
				$options['synchronize-trash']  = isset( $_POST['synchronize-trash'] ) && in_array( $_POST['synchronize-trash'], array( 'yes', 'no' ) ) ? $_POST['synchronize-trash'] : 'no';
				$options['publish-capability'] = isset( $_POST['publish-capability'] ) ? $_POST['publish-capability'] : 'administrator';
				$options['sync-all-metadata']  = isset( $_POST['sync-all-metadata'] ) ? $_POST['sync-all-metadata'] : 'no';
			}

			$options['child_inherit_changes_fields_control__status']            = $_POST['child_inherit_changes_fields_control__status'];
			$options['child_inherit_changes_fields_control__title']             = $_POST['child_inherit_changes_fields_control__title'];
			$options['child_inherit_changes_fields_control__description']       = $_POST['child_inherit_changes_fields_control__description'];
			$options['child_inherit_changes_fields_control__short_description'] = $_POST['child_inherit_changes_fields_control__short_description'];
			$options['child_inherit_changes_fields_control__price']             = $_POST['child_inherit_changes_fields_control__price'];
			$options['child_inherit_changes_fields_control__product_cat']       = $_POST['child_inherit_changes_fields_control__product_cat'];
			$options['child_inherit_changes_fields_control__product_tag']       = $_POST['child_inherit_changes_fields_control__product_tag'];
			$options['child_inherit_changes_fields_control__variations']        = $_POST['child_inherit_changes_fields_control__variations'];
			$options['child_inherit_changes_fields_control__attributes']        = $_POST['child_inherit_changes_fields_control__attributes'];
			$options['child_inherit_changes_fields_control__category_changes']  = $_POST['child_inherit_changes_fields_control__category_changes'];
			$options['child_inherit_changes_fields_control__reviews']           = $_POST['child_inherit_changes_fields_control__reviews'];
			$options['child_inherit_changes_fields_control__slug']              = $_POST['child_inherit_changes_fields_control__slug'];
			$options['child_inherit_changes_fields_control__purchase_note']     = $_POST['child_inherit_changes_fields_control__purchase_note'];
			$options['child_inherit_changes_fields_control__upsell']            = $_POST['child_inherit_changes_fields_control__upsell'];
			$options['child_inherit_changes_fields_control__cross_sells']       = $_POST['child_inherit_changes_fields_control__cross_sells'];
			$options['child_inherit_changes_fields_control__product_image']     = $_POST['child_inherit_changes_fields_control__product_image'];
			$options['child_inherit_changes_fields_control__product_gallery']   = $_POST['child_inherit_changes_fields_control__product_gallery'];

			$options = apply_filters( 'woo_mstore/options/options_save', $options );

			foreach ( $options as $key => $value ) {
				if ( is_array( $value ) ) {
					$options[ $key ] = array_map( 'strip_tags', $value );
				} else {
					$options[ $key ] = strip_tags( $value );
				}
			}

			update_option( 'woonet_options', $options );
			$mstore_form_submit_messages[] = __( 'Settings Saved', 'woonet' );
		}
	}
}



$GLOBALS['WOO_MSTORE_options_interface'] = new WOO_MSTORE_options_interface();
