<?php
/**
 * Syncs upsell and cross sells
 *
 * @since: 2.1.1
 **/
class WOO_MSTORE_UPSELL_CROSS_SELL_SYNC {

	/**
	 * Add action hooks on instantiation
	 **/
	public function __construct() {
		add_action( 'WOO_MSTORE_admin_product/slave_product_updated', array( $this, 'sync_upsell' ), PHP_INT_MAX, 1 );
		add_action( 'WOO_MSTORE_admin_product/slave_product_updated', array( $this, 'sync_cross_sell' ), PHP_INT_MAX, 1 );
		add_action( 'woocommerce_product_options_related', array( $this, 'show_warning_linked_products' ), 10, 0 );
	}

	public function sync_upsell( $data ) {

		if ( ! $this->is_sync_enabled($data, 'upsell') ) {
			return false;
		}

		$this->update_upsells_cross_sells( $data, 'upsell' );
	}

	public function sync_cross_sell( $data ) {

		if ( ! $this->is_sync_enabled($data, 'cross_sell') ) {
			return false;
		}

		$this->update_upsells_cross_sells( $data, 'cross_sell' );
	}

	/**
	 * Sync data for upsel land cross-sell
	 **/
	public function update_upsells_cross_sells( $data, $type ) {
		if ( $type == 'upsell' ) {
			$parent_products = $data['master_product']->get_upsell_ids();
		} else {
			$parent_products = $data['master_product']->get_cross_sell_ids();
		}

		if ( empty( $parent_products ) ) {
			if ( $type == 'upsell' ) {
				delete_post_meta( $data['slave_product']->get_id(), '_upsell_ids');
			} else {
				delete_post_meta( $data['slave_product']->get_id(), '_crosssell_ids');
			}
		}

		$mapped_parent_products = array();

		foreach ( $parent_products as $product_id ) {
			$_product = $this->get_mapped_product( $data, $product_id );

			if ( ! empty( $_product ) ) {
				$mapped_parent_products[] = $_product;
			}
		}

		if ( ! empty( $mapped_parent_products ) ) {
			if ( $type == 'upsell' ) {
				update_post_meta( $data['slave_product']->get_id(), '_upsell_ids', $mapped_parent_products );
			} else {
				update_post_meta( $data['slave_product']->get_id(), '_crosssell_ids', $mapped_parent_products );
			}
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

	/**
	* Check if sync is disabled for the current site
	**/
	public function is_sync_enabled($data, $type) {

		if ( $type == 'upsell' ) {
			$option_name = 'child_inherit_changes_fields_control__upsell';
		} else {
			$option_name = 'child_inherit_changes_fields_control__cross_sells';
		}

		//sync option enabled
		if ( !empty( $data['options'][$option_name][get_current_blog_id()] )
			 &&  $data['options'][$option_name][get_current_blog_id()] == 'yes') {
			return true;
		}

		return false;
	}

	/**
	* Print the warning message for linked products (cross-sell/upsell/grouped products)
	**/
	public function show_warning_linked_products()
	{
		echo '<p class="woomulti-quick-update-notice woomulti-linked-product-notice"> Note: A linked product (upsell, cross-sell or grouped product) needs to be synced with the child store before it can be synced as upsell, cross-sell or grouped product for a child store product.</p>';
	}
}

new WOO_MSTORE_UPSELL_CROSS_SELL_SYNC();
