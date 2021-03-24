<?php
/**
 * Sync role based pricing created by the Price by User Role for WooCommerce
 * crated by Tyche Software
 * URL: https://www.tychesoftwares.com/
 * Plugin URL: https://www.tychesoftwares.com/store/premium-plugins/price-user-role-woocommerce/
 * 
 * @since 4.0.0
 */

class WOO_MSTORE_INTEGRATION_TYCHE_PRICE_BY_USER_ROLE {	
	/**
	 * Initialize the action hooks and load the plugin classes
	 **/
	public function __construct() {
		if ( is_multisite() ) {
			add_filter('WOO_MSTORE_admin_product/slave_product_meta_to_update', array($this, 'multisite_sync_data'), 10, 2);
		}
	}

	/**
	 * Sync data for multsite version
	 *
	 * @param mixed $data
	 * @return void
	 */
	public function multisite_sync_data( $meta_data, $data ) {
		if ( ! function_exists('alg_get_user_roles') ) {
			return $meta_data;
		}

		//check if sync option is enabled or disabled
		$meta_data['_alg_wc_price_by_user_role_per_product_settings_enabled'] = $data['master_product']->get_meta('_alg_wc_price_by_user_role_per_product_settings_enabled');

		/**
		 * Metatadata for variable product are added to each variation, not the main product. 
		 * We will skip adding the metadata to the variable product. But this same hook gets fired for variations as well. 
		 * There's no additional code needed to sync variable pricing. 
		 */
		if ( $data['master_product']->get_type() == 'variable' ) {
			return $meta_data;
		}

		$roles = alg_get_user_roles();

		/**
		 * Run for product (simple, grouped, variable, etc ) and variations. 
		 */
		if ( !empty($roles) ) {
			foreach( $roles as $role_key => $role ) {
				$meta_data[ '_alg_wc_price_by_user_role_regular_price_' . $role_key ] = $data['master_product']->get_meta( '_alg_wc_price_by_user_role_regular_price_' . $role_key );
				$meta_data[ '_alg_wc_price_by_user_role_sale_price_' . $role_key ] = $data['master_product']->get_meta( '_alg_wc_price_by_user_role_sale_price_' . $role_key );
				$meta_data[ '_alg_wc_price_by_user_role_empty_price_' . $role_key ] = $data['master_product']->get_meta( '_alg_wc_price_by_user_role_empty_price_' . $role_key );
			}
		}

		return $meta_data; 
	}
}

new WOO_MSTORE_INTEGRATION_TYCHE_PRICE_BY_USER_ROLE();