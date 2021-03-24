<?php
/**
 * Add support for the generatepress theme.
 *
 * @see https://woocommerce.com/storefront/
 *
 * @since 1.7.6
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 */

class WCVendors_Theme_Support_Generatepress {
	/**
	 * Add theme support for the generatepress theme
	 *
	 * @version 1.7.6
	 * @since   1.7.6
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'dashboard_style_updates' ), 20 );
		add_action( 'wcv_after_pro_dashboard_wrapper', array( $this, 'load_generatepress_dashboard' ) );
	}

	/**
	 * Add style updates to the dashboard ensure theme works correctly.
	 *
	 * @version 1.7.6
	 * @since   1.7.6
	 */
	public function dashboard_style_updates() {
		$style  = '.wcv-generatepress-theme-support nav.wcv-navigation ul.menu.horizontal li { margin: 0; }';
		$style .= '.wcv-generatepress-theme-support nav.wcv-navigation ul.menu.horizontal li a { padding: 8px 18px; }';
		$style .= '.wcv-generatepress-theme-support a.button, .wcv-generatepress-theme-support form input[type="submit"], .wcv-generatepress-theme-support form .control-group button.wcv-button.wcv-search-product, .wcv-generatepress-theme-support form input[type="reset"] { background-color: #000000; color: #FFFFFF; font-size: 16px; }';
		$style .= '.wcv-generatepress-theme-support a.button:hover, .wcv-generatepress-theme-support a.button:focus, .wcv-generatepress-theme-support form input[type="submit"]:hover, .wcv-generatepress-theme-support form input[type="submit"]:focus, .wcv-generatepress-theme-support form .control-group button.wcv-button.wcv-search-product:hover, .wcv-generatepress-theme-support form .control-group button.wcv-button.wcv-search-product:focus, .wcv-generatepress-theme-support form input[type="reset"]:hover, .wcv-generatepress-theme-support form input[type="reset"]:focus { background-color: #333333; color: #FFFFFF; text-decoration: none; outline: none; box-shadow: none; } .wcv-generatepress-theme-support .wcv-cols-group.wcv-horizontal-gutters .all-40 { width: auto; }';
		$style .= '.wcv-generatepress-theme-support form.wcv-form.wcv-form-exclude .all-33 .control-group input#update_button { padding: 8px 15px; width: 70%; display: block; margin: 0 auto; }';
		$style .= '.wcv-generatepress-theme-support table, table.wcv-table tr td, table.wcv-table tr th { border-bottom: 1px solid rgba(0, 0, 0, .1); }';
		$style .= '.wcv-generatepress-theme-support h3 { font-size: 20px !important; }';
		$style .= '.wcv-generatepress-theme-support h4 { font-size: 18px !important; }';
		$style .= '.wcv-generatepress-theme-support .all-50 nav.woocommerce-pagination ul.page-numbers, .wcv-generatepress-theme-support .all-50 nav.woocommerce-pagination ul.page-numbers { display: inline-flex; float: right; }';
		$style .= '.wcv-generatepress-theme-support .all-50 nav.woocommerce-pagination ul.page-numbers li:last-child, .wcv-generatepress-theme-support .all-50 nav.woocommerce-pagination ul.page-numbers li:last-child { padding-right: 0; }';
		$style .= '.wcv-generatepress-theme-support .all-50 nav.woocommerce-pagination ul.page-numbers li .page-numbers.current { color: #FFFFFF; background-color: #000000; }';
		$style .= '.wcv-generatepress-theme-support .all-50 nav.woocommerce-pagination ul.page-numbers li .page-numbers { color: #000000; padding: 4px 10px; margin-left: 2px; border: 1px solid #000000; font-size: 16px; }';
		$style .= '.wcv-generatepress-theme-support .all-50 nav.woocommerce-pagination ul.page-numbers li .page-numbers:hover, .wcv-generatepress-theme-support .all-50 nav.woocommerce-pagination ul.page-numbers li .page-numbers:focus { text-decoration: none; box-shadow: none; outline: none; }';
		$style .= '.wcv-generatepress-theme-support .wcv-order-header form.wcv-form.wcv-form-exclude .wcv-cols-group.wcv-horizontal-gutters { margin-left: 0; }';
		$style .= '.wcv-generatepress-theme-support .wcv-order-header .export-orders { padding-right: 2em; }';
		$style .= '.wcv-generatepress-theme-support .wcv-order-header .export-orders a { padding: 12px 20px; }';
		$style .= '@media only screen and (max-width: 480px) { ';
		$style .= '.wcv-generatepress-theme-support .wcv-cols-group.wcv-horizontal-gutters .all-40 { width: 100%; }';
		$style .= '.wcv-generatepress-theme-support form.wcv-form.wcv-form-exclude .all-66 { width: 100%; }';
		$style .= '.wcv-generatepress-theme-support form.wcv-form.wcv-form-exclude .wcv-cols-group.wcv-horizontal-gutters .all-50.tiny-100 { width: 50%; display: inline-flex; }';
		$style .= '.wcv-generatepress-theme-support form.wcv-form.wcv-form-exclude .all-33 { width: 100%; display: block; float: none; }';
		$style .= '}';
		wp_add_inline_style( 'wcv-pro-dashboard', $style );
	}
	/**
	 * Add script updates to the dashboard ensure theme works correctly.
	 *
	 * @version 1.7.6
	 * @since   1.7.6
	 */
	public function load_generatepress_dashboard() {
	?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('body').addClass('wcv-generatepress-theme-support');
		});
	</script>
	<?php
	}
}

return new WCVendors_Theme_Support_Generatepress();
