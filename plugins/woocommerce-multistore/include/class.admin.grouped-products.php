<?php

/**
 * Syncs grouped products with child sites
 *
 * @since: 2.1.1
 **/
class WOO_MSTORE_GROUPED_PRODUCTS_SYNC {

	/**
	 * Add action hooks on instantiation
	 **/
	public function __construct() {
		 add_action( 'WOO_MSTORE_admin_product/slave_product_updated', array( $this, 'sync_grouped_products' ), PHP_INT_MAX, 1 );
	}

	public function sync_grouped_products( $data ) {
		if ( $data['master_product']->get_type() != 'grouped' ) {
			return;
		}

		$this->update_grouped_products( $data );
	}

	public function update_grouped_products( $data ) {
		$grouped_products        = $data['master_product']->get_children();
		$mapped_grouped_products = array();

		if ( ! empty( $grouped_products ) ) {
			foreach ( $grouped_products as $product_id ) {
				$_product = $this->get_mapped_product( $data, $product_id );

				if ( ! empty( $_product ) ) {
					$mapped_grouped_products[] = $_product;
				}
			}
		}

		if ( ! empty( $mapped_grouped_products ) ) {
			update_post_meta( $data['slave_product']->get_id(), '_children', $mapped_grouped_products );
		} else {
			delete_post_meta( $data['slave_product']->get_id(), '_children' );
		}
	}

	/**
	 * Map the parent product ID to its child product ID for child store
	 **/
	public function get_mapped_product( $data, $product_id ) {
		global $wpdb;

		$_product = $wpdb->get_row("SELECT * from {$wpdb->prefix}postmeta WHERE 
							meta_key='_woonet_network_is_child_sid_{$data['master_product_blog_id']}_pid_{$product_id}'");
		
		if ( !empty($_product->post_id) ) {
			return $_product->post_id;
		}
		
		/**
		 * Requires full table scanning. Kept for backward compatibility. 
		 */
		$_product = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . "postmeta WHERE meta_key='_woonet_network_is_child_product_id' AND meta_value=" . $product_id );

		if ( ! empty( $_product->post_id ) ) {
			return $_product->post_id;
		}

		return false;
	}
}

new WOO_MSTORE_GROUPED_PRODUCTS_SYNC();
