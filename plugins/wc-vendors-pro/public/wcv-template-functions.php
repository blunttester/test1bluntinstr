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




if ( ! function_exists( 'wcv_output_vendor_ga_code' ) ) {

	/**
	 * Output the vendor tracking code
	 *
	 * @param int $vendor_id - the vendor user ID.
	 * @return string $ga_code - the google analytics code
	 */
	function wcv_output_vendor_ga_code( $vendor_id ) {

		// Not a vendor? return nothing.
		if ( ! WCV_Vendors::is_vendor( $vendor_id ) ) {
			return '';
		}

		$vendor_tracking_id = get_user_meta( $vendor_id, '_wcv_settings_ga_tracking_id', true );

		// No tracking code added, return nothing.
		if ( empty( $vendor_tracking_id ) ) {
			return '';
		}

		$ga_code = sprintf(
		' <!-- Global site tag (gtag.js) - Google Analytics added by WC Vendors Pro -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=' . $vendor_tracking_id . '"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag(\'js\', new Date());
		
		  gtag(\'config\', \' ' . $vendor_tracking_id . ' \');
		</script> '
		);

		return $ga_code;
	}
}

