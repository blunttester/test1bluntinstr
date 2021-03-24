<?php
/**
 * The template for displaying store search form
 *
 * NOTE: DO NOT REMOVE THE HIDDEN FIELD 'wcv_vendor_id' this will break the search
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/front/vendor-searchform.php.
 *
 * @package    WCVendors_Pro
 * @version    1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<form role="search" method="get" class="wcv-store-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text"
		   for="wcv-store-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>"><?php _e( 'Search for:', 'wcvendors-pro' ); ?></label>
	<input type="search" id="wcv-store-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>"
		   class="search-field" placeholder="<?php echo esc_attr__( 'Search store&hellip;', 'wcvendors-pro' ); ?>"
		   value="<?php echo get_search_query(); ?>" name="s"/>
	<input type="submit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'wcvendors-pro' ); ?>"/>
	<input type="hidden" name="post_type" value="product"/>
	<input type="hidden" name="wcv_vendor_id" value="<?php echo $vendor_id; ?>"/>
</form>
