<?php
/**
 * The plugin lets the customer manage multiple inventory per product. 
 * This integration syncs multi-inventory data when the product is synced
 * across the network. 
 * 
 * URL: https://www.stockmanagementlabs.com/
 * Plugin URL: https://www.stockmanagementlabs.com/addons/atum-multi-inventory/
 * 
 * @since 4.0.0
 */

class WOO_MSTORE_INTEGRATION_ATUM_MULTI_INVENTORY {	
	/**
	 * Initialize the action hooks and load the plugin classes
	 **/
	public function __construct() {
		if ( is_multisite() ) {
			// add_filter('WOO_MSTORE_admin_product/slave_product_meta_to_update', array($this, 'multisite_sync_data'), 10, 2);
		}
	}

	/**
	 * Sync data for multsite version
	 *
	 * @param mixed $data
	 * @return void
	 */
	public function multisite_sync_data( $meta_data, $data ) {
		$current_blog_id = get_current_blog_id(); 
		switch_to_blog( $data['master_product_blog_id'] );

		$meta_data['_multi_inventory'] = get_post_meta( $data['master_product']->get_id(), '_multi_inventory', true );
		$meta_data['_inventory_sorting_mode'] = get_post_meta( $data['master_product']->get_id(), '_inventory_sorting_mode', true );
		$meta_data['_inventory_iteration'] = get_post_meta( $data['master_product']->get_id(), '_inventory_iteration', true );
		$meta_data['_expirable_inventories'] = get_post_meta( $data['master_product']->get_id(), '_expirable_inventories', true );
		$meta_data['_price_per_inventory'] = get_post_meta( $data['master_product']->get_id(), '_price_per_inventory', true );

		switch_to_blog( $current_blog_id  );

		return $meta_data; 
	}
}

new WOO_MSTORE_INTEGRATION_ATUM_MULTI_INVENTORY();