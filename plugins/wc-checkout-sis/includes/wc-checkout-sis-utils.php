<?php

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get payment gateway options
 */
function wc_checkout_sis_options() {
	return (array) get_option( 'woocommerce_checkout_sis_settings', [] );
}

/**
 * Check if debug mode is enabled
 */
function wc_checkout_sis_debug_mode() {
	$options = wc_checkout_sis_options();

	$debug = isset( $options['debug_mode'] ) && $options['debug_mode'] === 'yes';

	return apply_filters( 'wc_checkout_sis_debug_mode', $debug );
}
