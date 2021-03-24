<?php
/**
 * Ask customer to mark order received (plain text).
 *
 * @author         Jamie Madden, WC Vendors
 * @package        WCVendors/Templates/Emails/Plain
 * @version        2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '= ' . $email_heading . " =\n\n";

printf( __( 'It has been a while since the %1$s shipped your order #%2$s. If you received the order, please mark it received in My account > Orders. The order is as follows.', 'wcvendors-pro' ), $vendor_name, $order->get_id() );
echo __( 'Please ignore this email if you have already done it.', 'wcvendors-pro' );

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
