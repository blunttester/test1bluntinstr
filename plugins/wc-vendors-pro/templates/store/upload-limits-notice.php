<?php
/**
 * Display the vendor upload limits notice
 *
 * Override this template by copying it to yourtheme/wc-vendors/store
 *
 * @package    WCVendors_Pro
 * @version    1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php if ( $upload_limits_notice ) : ?>
	<div class="wcv-store-msg woocommerce-error">
		<p><?php echo $upload_limits_notice; ?></p>
	</div>
<?php endif; ?>