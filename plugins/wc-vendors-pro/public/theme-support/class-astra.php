<?php

/**
 * Add support for the astra theme.
 *
 * @see https://woocommerce.com/storefront/
 *
 * @since 1.7.4
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 */
class WCVendors_Theme_Support_Astra {


	/**
	 * Add theme support for the astra theme
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'dashboard_style_updates' ), 20 );
		add_action( 'wcv_after_pro_dashboard_wrapper', array( $this, 'load_astra_dashboard' ) );
	}


	/**
	 * Add style updates to the dashboard ensure theme works correctly.
	 */
	public function dashboard_style_updates() {
		$style  = '.wcv-astra-theme-support .wcv-grid input[type=submit], .wcv-grid input[type=reset] { color: #FFFFFF;}';
		$style .= '.wcv-astra-theme-support .wcv-form .control-group.all-33 { padding-left: 1em;}';
		$style .= '.wcv-astra-theme-support .wcv-form .control-group .control.append-button .wcv-button.wcv-search-product, .wcv-astra-theme-support #update_button { margin-left: 1em;}';
		$style .= '.wcv-astra-theme-support .wcvendors-table.wcvendors-table-product.wcv-table { margin-top: 1em;margin-bottom: 1em; }';
		$style .= '.wcv-astra-theme-support table.wcv-table tr td { border-bottom: 1px solid rgba(0,0,0,.1) !important; }';
		$style .= '.wcv-astra-theme-support nav.woocommerce-pagination ul.page-numbers li { float: left !important; padding: 4px 10px !important; border: 1px solid grey !important; margin-left: 2px !important; font-weight: 600 !important; border-radius: 2px !important; }';
		$style .= '.wcv-astra-theme-support nav.woocommerce-pagination ul.page-numbers { padding: 0 !important; }';
		$style .= '.wcv-astra-theme-support nav.woocommerce-pagination { float: right !important; }';
		$style .= '.wcv-astra-theme-support nav.woocommerce-pagination ul.page-numbers li.wcv-current { background-color: #0274be; color: #fff; }';
		wp_add_inline_style( 'wcv-pro-dashboard', $style );
	}

	/**
	 * Add script updates to the dashboard ensure theme works correctly.
	 */
	public function load_astra_dashboard() { ?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('body').addClass('wcv-astra-theme-support');
				jQuery('.wcv-astra-theme-support nav.woocommerce-pagination ul.page-numbers li span.page-numbers.current').parent('li').addClass('wcv-current');
			});
		</script>
	<?php
	}
}
return new WCVendors_Theme_Support_Astra();
