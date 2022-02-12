<?php
/**
 * Vendor Contact Widget
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/vendor-contact-widget.php.
 *
 * @author  WC Vendors
 * @package WCVendors/Templates/Emails
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '= ' . $email_heading . " =\n\n";

do_action( 'woocommerce_email_header', $email_heading );

echo printf( __( 'Hi there. A customer has left a message on %s.', 'wcvendors-pro' ), $shop_name ) . "\n\n";
echo printf( __( 'Email Address: %s', 'wcvendors-pro' ), $email ) . "\n\n";
echo printf( __( 'Message: %s', 'wcvendors-pro' ), $message ) . "\n\n";

do_action( 'wcv_quick_contact_email_extras' );

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
