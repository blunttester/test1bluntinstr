<?php

/**
 * Dashboard wrapper container
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://www.wcvendors.com
 * @since      1.6.0
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/partials
 */
?>

<?php $dashboard_wrapper_class = apply_filters( 'wcv_dashboard_wrapper_class', '' ); ?>

<?php do_action( 'wcv_before_pro_dashboard_wrapper' ); ?>

<div class="wcvendors-pro-dashboard-wrapper <?php echo $dashboard_wrapper_class; ?>">

	<div class="wcv-grid">
