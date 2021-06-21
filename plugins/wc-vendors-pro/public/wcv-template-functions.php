<?php
/**
 *  WC Vendors Template
 *
 *  Functions for the WC Vendors template system
 *
 * @package WC_Vendors_Pro\Functions
 * @since 1.7.9
 * @version 1.7.9
 */


if ( ! function_exists( 'wcv_get_default_store_banner_src' ) ) {
	/**
	 * Get defualt vendor banner src
	 *
	 * @return array
	 * @version 1.7.9
	 * @since   1.7.9
	 */
	function wcv_get_default_store_banner_src() {

		$defaul_banner_src = plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/images/wcvendors_default_banner.jpg';

		return apply_filters( 'wcv_get_default_store_banner_src', $defaul_banner_src );
	}
}
