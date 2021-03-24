<?php

/**
 * Dashboard wrapper container
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://www.wcvendors.com
 * @since      1.0.0
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/partials
 */
$vertical_menu = wc_string_to_bool( get_option( 'wcvendors_use_vertical_menu', 'no' ) );

?>

<?php if ( $vertical_menu ) : ?>
	</div>
<?php endif; ?>
</div>
</div>
<?php do_action( 'wcv_after_pro_dashboard_wrapper' ); ?>
