<?php

/**
 * Add support for the Divi Theme.
 *
 * @see https://www.elegantthemes.com/gallery/divi/
 *
 * @since 1.6.0
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCVendors_Theme_Support_Divi {


	public function __construct() {
		add_filter( 'wcv_dashboard_wrapper_class', array( $this, 'disable_smooth_scroll' ) );
		add_action( 'template_redirect', array( $this, 'remove_sidebar' ) );
		add_filter( 'body_class', array( $this, 'set_full_width' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'style_updates' ) );

	}

	/**
	 * Need to add a style to the dashboard wrapper to disable divi's smooth scroll.
	 */
	public function disable_smooth_scroll( $styles ) {
		return $styles = 'et_smooth_scroll_disabled';
	}

	 /**
	  * Remove sidebar from store and dashboard page
	  *
	  * @return void
	  */
	public function remove_sidebar() {
		if ( wcv_is_vendor_dashboard() ) {
			unregister_sidebar( 'sidebar-1' );
		}
	}

	/**
	 * Make the dashboard full width
	 */
	public function set_full_width( $classes ) {

		 if ( wcv_is_vendor_dashboard() ) {

			if ( ! in_array( 'et_full_width_page', $classes ) ) {
				$classes[] = 'et_full_width_page';
				$classes[] = 'et_no_sidebar';
			}
		}

		return $classes;
	}


	/**
	 * Add style updates to ensure theme works correctly.
	 */
	public function style_updates() {

		$style  = '#left-area ul { padding: 0 !important;}';
		$style .= '.media-button-select { font-size: 15px !important; padding-top: 0 !important}';

		wp_add_inline_style( 'woocommerce-layout', $style );

	}

}
return new WCVendors_Theme_Support_Divi();
