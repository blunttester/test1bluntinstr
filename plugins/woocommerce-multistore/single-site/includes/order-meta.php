<?php
/**
 * Add metadata to the order to assist WooMultistore Query
 *
 * @class   WOO_MSTORE_SINGLE_ADD_ORDER_META
 * @since   4.2.0
 */
class WOO_MSTORE_SINGLE_ADD_ORDER_META {
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 10, 0 );
	}

	/**
	 * init
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'woocommerce_thankyou', array( $this, 'wp_insert_meta' ), 10, 1 );
	}

	/**
	 * Insert metadata
	 */
	public function wp_insert_meta( $order_id ) {
		$order = wc_get_order($order_id);
        if ( $items = $order->get_items() ) {
			foreach( $items as $item ) {
				if ( $id = $item->get_product_id() ) {
					$wc_product = wc_get_product( $id );
					if ( $wc_product &&  $wc_product->get_meta('_woonet_master_product_id', true) >= 1 ) {
                        update_post_meta( $order_id, '_woonet_has_synced_product', 'yes');
					}
				}
			}
		}
	}
}

$GLOBALS['WOO_MSTORE_SINGLE_ADD_ORDER_META'] = new WOO_MSTORE_SINGLE_ADD_ORDER_META();
