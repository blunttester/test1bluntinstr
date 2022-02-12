<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC Vendors Pro Emails Class
 *
 * @author     WC Vendors, Lindeni Mahlalela
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/emails/
 */
class WCV_Pro_Emails {
	/**
	 * Construct, add ajax hooks
	 *
	 * @package
	 * @since
	 *
	 * @param
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_classes', array( $this, 'email_classes' ) );
		add_action( 'wp_ajax_wcv_quick_contact', array( $this, 'process_quick_contact' ) );

		add_action( 'wp_ajax_nopriv_wcv_quick_contact', array( $this, 'process_quick_contact' ) );
		add_action( 'wcvendors_vendor_ship', array( $this, 'schedule_customer_mark_received_email' ), 10, 2 );

		add_action( 'wcv_scheduled_customer_mark_received_email', array( $this, 'process_mark_received' ), 10, 2 );
	}

	/**
	 * Add email class to woocomerce emails
	 *
	 * @param array $emails
	 *
	 * @return array $emails
	 * @since 1.5.4
	 */
	public function email_classes( $emails ) {

		require_once 'class-vendor-contact-widget-email.php';
		require_once 'class-customer-mark-received-email.php';

		$emails['WC_Vendors_Pro_Email_Vendor_Contact_Widget']  = new WC_Vendors_Pro_Email_Vendor_Contact_Widget();
		$emails['WC_Vendors_Pro_Email_Customer_Mark_Received'] = new WC_Vendors_Pro_Email_Customer_Mark_Received();

		return $emails;
	}

	/**
	 * Process the ajax request to send the email
	 *
	 * @return void
	 * @since 1.5.4
	 */
	public function process_quick_contact() {
		global $woocommerce;

		$emails = $woocommerce->mailer()->get_emails();

		if ( isset( $_REQUEST['vendor'] ) ) {
			$emails['WC_Vendors_Pro_Email_Vendor_Contact_Widget']->send_email();
		}

	}

	public function process_mark_received( $order_id, $vendor_id ) {
		global $woocommerce;

		$emails = $woocommerce->mailer()->get_emails();

		$emails['WC_Vendors_Pro_Email_Customer_Mark_Received']->trigger( $order_id, $vendor_id );
	}

	/**
	 * Schedule sending email to customer asking them mark order received.
	 *
	 * @param int $order_id  The order ID.
	 * @param int $vendor_id The vendor ID.
	 */
	public function schedule_customer_mark_received_email( $order_id, $vendor_id ) {
		global $woocommerce;

		$received = (array) get_post_meta( $order_id, '_wcv_order_received', true );
		$received = array_filter( $received );

		$emails = $woocommerce->mailer()->get_emails();

		$email = $emails['WC_Vendors_Pro_Email_Customer_Mark_Received'];

		if ( ! $email->is_enabled() || ! in_array( $vendor_id, $received ) ) {
			return;
		}

		WC()->queue()->schedule_single(
			time() + DAY_IN_SECONDS * $email->get_option( 'period' ),
			'wcv_scheduled_customer_mark_received_email',
			array(
				'order_id'  => $order_id,
				'vendor_id' => $vendor_id,
			)
		);
	}
}
