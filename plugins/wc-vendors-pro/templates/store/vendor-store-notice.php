<?php
/**
 * Display the vendor store notice
 *
 * Override this template by copying it to yourtheme/wc-vendors/store
 *
 * @package WCVendors_Pro
 * @version 1.5.9
 * @since   1.6.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( $vendor_store_notice ) : ?>
	<div class="wcv-store-notice-container wcv-store-msg">
		<?php echo wp_kses_post( $vendor_store_notice ); ?>
	</div>
<?php endif; ?>
