<?php
/**
 * Different utiligy functions related to product Sync
 *
 * @class   WOO_MSTORE_SINGLE_UTILS_SYNC
 * @since   4.2.0
 */
class WOO_MSTORE_SINGLE_UTILS_SYNC {
	/**
	 * Check if stock sync is enabled and required.
	 */
	public function is_stock_sync_required( $product_id, $site_id ) {
		$options = WOO_MULTISTORE()->options_manager;

		/**
		 * Check on master site.
		 */
		if ( (WOO_MULTISTORE()->site_manager->get_type() == 'master' 
			 && $options->get( 'synchronize-stock' ) == 'yes' ) 
			 || ( WOO_MULTISTORE()->site_manager->get_type() == 'master'
			 && get_post_meta( $product_id, "_woonet_{$site_id}_child_stock_synchronize", true ) == 'yes') ) {
			return true;
		}

		/**
		 * Check on the child.
		 */
		if ( ( WOO_MULTISTORE()->site_manager->get_type() == 'child' && $options->get( 'synchronize-stock' ) == 'yes' )
		    || ( WOO_MULTISTORE()->site_manager->get_type() == 'child' && get_post_meta( $product_id, '_woonet_master_product_id', true ) >= 1 ) ) {
			return true;
		}

		return false;
	}
}

