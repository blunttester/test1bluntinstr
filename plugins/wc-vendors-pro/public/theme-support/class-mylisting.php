<?php

/**
 * Add support for the MyListing theme.
 *
 * @see https://themeforest.net/item/mylisting-directory-listing-wordpress-theme/20593226
 *
 * @since 1.6.0
 * @version 1.6.3
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCVendors_Theme_Support_Mylisting {

	public function __construct() {
		add_action( 'wcv_before_pro_dashboard_wrapper', array( $this, 'mylisting_wrapper_open' ) );
		add_action( 'wcv_after_pro_dashboard_wrapper', array( $this, 'mylisting_wrapper_close' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'dashboard_style_updates' ), 20 );
	}

	/**
	 * Open the mylisting wrapper containers
	 */
	public function mylisting_wrapper_open() {
		echo '<section class="i-section">
				<div class="container c1 wcc">
					<div class="content-area row the-page-content">
						<div class="col-md-12">';
	}

	/**
	 * Close the MyListing wrapper containers
	 */
	public function mylisting_wrapper_close() {
		echo '</div></div></div></section>';
	}


	/**
	 * Add style updates to the dashboard ensure theme works correctly.
	 */
	public function dashboard_style_updates() {

		$style  = '.wcv-grid .woocommerce-pagination { text-align: right; }';
		$style .= '.wcv-pro-dashboard .wcv-navigation ul.menu.black li.active a:hover { color: #fff!important; }';

		wp_add_inline_style( 'wcv-pro-dashboard', $style );
	}

}
return new WCVendors_Theme_Support_Mylisting();
