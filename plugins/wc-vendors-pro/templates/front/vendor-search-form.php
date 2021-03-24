<?php
/**
 * The template for displaying vendor search form
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/front/vendor-search-form.php.
 *
 * @package    WCVendors_Pro
 * @since      1.5.6
 * @version    1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vendors_page_id = get_option( 'wcvendors_vendors_page_id', null );

if ( is_numeric( $vendors_page_id ) ) {
	$page = esc_url( get_permalink( $vendors_page_id ) );
} else {
	$page = esc_url( home_url( '/vendors' ) );
}

?>
<form role="search" method="get" class="wcv-vendor-search" action="<?php echo $page; ?>">
	<label class="screen-reader-text"
		   for="wcv-vendor-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>"><?php _e( 'Search for:', 'wcvendors-pro' ); ?></label>
	<input type="search" id="wcv-vendor-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>"
		   class="search-field"
		   placeholder="<?php echo esc_attr( sprintf( __( 'Search %s&hellip;', 'wcvendors-pro' ), wcv_get_vendor_name() ) ); ?>"
		   value="<?php echo( isset( $_GET['vendor_search_term'] ) ? esc_attr( $_GET['vendor_search_term'] ) : '' ); ?>"
		   name="vendor_search_term"/>
	<input type="submit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'wcvendors-pro' ); ?>"/>
</form>
