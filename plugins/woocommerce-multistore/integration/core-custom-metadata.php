<?php
/**
 * Sync custom metadata
 *
 * @since 4.1.0
 */
class WOO_MSTORE_INTEGRATION_Sync_Custom_Meta {

	/**
	 * The array of whitelisted metadata
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $whitelist    The array of whitelisted metadata for automatic syncing.
	 */
	protected $whitelist;


	/**
	 * $options_manager instance
	 *
	 * @var object
	 */
	private $options_manager = null;


	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->set_whitelist();

		if ( is_multisite() ) {
			add_filter( 'WOO_MSTORE_admin_product/slave_product_meta_to_update', array( $this, 'sync_whitelist' ), PHP_INT_MAX, 2 );
		} else {
			add_filter( 'WOO_MSTORE_SYNC/process_json/meta', array( $this, 'sync_whitelist_standalone' ), PHP_INT_MAX, 3 );
		}

		if ( ! is_multisite() ) {
			$this->options_manager = new WOO_MSTORE_OPTIONS_MANAGER();
		}
	}

	/**
	 * Syncs the whitelisted metadata for multisite
	 *
	 * @since    1.0.0
	 * @return  array array of metada key and meta value.
	 */
	public function sync_whitelist( $meta_data, $data ) {
		foreach ( $this->whitelist as $whitelisted_metakey ) {
			$meta_value                        = $data['master_product']->get_meta( trim( $whitelisted_metakey ), true );
			$meta_data[ $whitelisted_metakey ] = $meta_value;
		}

		return $meta_data;
	}


	/**
	 * Sync metadata on regular WordPress site.
	 *
	 * @since 1.0.1
	 *
	 * @param mixed $_whitelisted_meta
	 * @param mixed $product_id
	 * @param mixed $wc_product
	 * @return void
	 */
	public function sync_whitelist_standalone( $_whitelisted_meta, $product_id, $wc_product ) {

		if ( $this->options_manager->get( 'sync-custom-metadata' ) != 'yes' ) {
			return $_whitelisted_meta;
		}

		foreach ( $this->whitelist as $whitelisted_metakey ) {
			$meta_value                                = get_post_meta( $product_id, trim( $whitelisted_metakey ), true );
			$_whitelisted_meta[ $whitelisted_metakey ] = $meta_value;
		}

		return $_whitelisted_meta;
	}

	private function set_whitelist() {
		if ( is_multisite() ) {
			$options = get_site_option( 'woonet_settings_custom_metadata', '' );
		} else {
			$options = get_option( 'woonet_settings_custom_metadata', '' );
		}

		$options = trim( $options );

		if ( ! empty( $options ) ) {
			$options = explode( "\n", $options );
			$options = array_map( 'sanitize_key', $options );

			if ( is_array( $options ) ) {
				$this->whitelist = $options;
			} else {
				$this->whitelist = array();
			}
		} else {
			$this->whitelist = array();
		}
	}
}

new WOO_MSTORE_INTEGRATION_Sync_Custom_Meta();
