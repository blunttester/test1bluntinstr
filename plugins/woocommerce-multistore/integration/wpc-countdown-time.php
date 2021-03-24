<?php
/**
 * Integrate WPC Countdown Timer
 * URL: https://wordpress.org/plugins/wpc-countdown-timer/
 * Plugin URL: https://wordpress.org/plugins/wpc-countdown-timer/
 *
 * @since 4.1.5
 */

class WOO_MSTORE_INTEGRATION_WPC_COUNTDOWN_TIMER {

	/**
	 * The array of whitelisted metadata
	 *
	 * @access   protected
	 * @var      array    $whitelist    The array of whitelisted metadata for automatic syncing.
	 */
	protected $whitelist;


	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 */
	public function __construct() {
		$this->whitelist = array(
            'wooct_active',
            'wooct_style',
            'wooct_time_start',
            'wooct_time_end',
            'wooct_text_above',
            'wooct_text_under',
            'wooct_text_ended',
        );

		if ( is_multisite() ) {
			add_filter( 'WOO_MSTORE_admin_product/slave_product_meta_to_update', array( $this, 'sync_whitelist' ), PHP_INT_MAX, 2 );
		} else {
			add_filter( 'WOO_MSTORE_SYNC/process_json/meta', array( $this, 'sync_whitelist_standalone' ), PHP_INT_MAX, 3 );
		}
	}

	/**
	 * Syncs the whitelisted metadata for multisite
	 *
	 * @return  array array of metada key and meta value.
	 */
	public function sync_whitelist( $meta_data, $data ) {
		foreach ( $this->whitelist as $whitelisted_metakey ) {
			$meta_value                        = $data['master_product']->get_meta( $whitelisted_metakey, true );
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
		foreach ( $this->whitelist as $whitelisted_metakey ) {
			$meta_value                                = get_post_meta( $product_id, $whitelisted_metakey, true );
			$_whitelisted_meta[ $whitelisted_metakey ] = $meta_value;
		}

		return $_whitelisted_meta;
	}
}

new WOO_MSTORE_INTEGRATION_WPC_COUNTDOWN_TIMER();
