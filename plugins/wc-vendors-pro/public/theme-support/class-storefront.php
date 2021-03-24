<?php

/**
 * Add support for the storefront theme.
 *
 * @see https://woocommerce.com/storefront/
 *
 * @since 1.6.0
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCVendors_Theme_Support_Storefront {


	public function __construct() {
		add_filter( 'body_class', array( $this, 'set_full_width' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'dashboard_style_updates' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'store_style_updates' ) );

	}

	/**
	 * Make the dashboard full width
	 */
	public function set_full_width( $classes ) {

		 if ( wcv_is_vendor_dashboard() ) {

		   if ( ! in_array( 'page-template-template-fullwidth-php', $classes ) ) {
				$classes[] = 'page-template-template-fullwidth-php';
			}
		}

		return $classes;
	}


	/**
	 * Add style updates to the dashboard ensure theme works correctly.
	 */
	public function dashboard_style_updates() {

		$style  = '.wcv-form .control-group input[type=checkbox] { position: static !important; }';
		$style .= '.wcv-form .control-group input[type=checkbox] + label { padding-left: 0 !important;}';
		$style .= '.hentry .wp-post-image { margin-bottom: 0.5em!important;}';

		wp_add_inline_style( 'wcv-pro-dashboard', $style );
	}

	/**
	 * Add style updates to the store ensure theme works correctly.
	 */
	public function store_style_updates() {
		$style = '.woocommerce-products-header { padding: 0 !important;}';
		wp_add_inline_style( 'wcv-pro-store-style', $style );
	}
}
return new WCVendors_Theme_Support_Storefront();
