<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors Pro Customer Mark Received Email class.
 *
 * @since       1.5.4
 * @author      WC Vendors, Lindeni Mahlalela
 * @package     WCVendors_Pro
 * @subpackage  WCVendors_Pro/emails/
 */
class WC_Vendors_Pro_Email_Customer_Mark_Received extends WC_Email {

	/**
	 * Constructor
	 */
	function __construct() {
		$this->id          = 'customer-mark-received';
		$this->title       = __( 'Customer Mark Received', 'wcvendors-pro' );
		$this->description = __( 'Ask customer to mark order received.', 'wcvendors-pro' );

		$this->heading = __( 'Please mark your order received', 'wcvendors-pro' );
		$this->subject = __( 'Please mark order #{order_number} received', 'wcvendors-pro' );

		$this->template_base  = WCV_PRO_ABSPATH_TEMPLATES . '/emails/';
		$this->template_html  = 'customer-mark-received.php';
		$this->template_plain = 'plain/customer-mark-received.php';

		// Call parent constuctor
		parent::__construct();

		add_action( 'wcvendors_vendor_ship', array( $this, 'trigger' ), 10, 2 );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int $order_id  The order ID.
	 * @param int $vendor_id The vendor ID.
	 */
	public function trigger( $order_id, $vendor_id ) {

		if ( ! $order_id || ! $this->is_enabled() ) {
			return;
		}

		$this->setup_locale();

		$order = wc_get_order( $order_id );

		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object                         = $order;
			$this->recipient                      = $this->object->get_billing_email();
			$this->vendor_id                      = $vendor_id;
			$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
			$this->placeholders['{order_number}'] = $this->object->get_order_number();
		}

		if ( $this->get_recipient() ) {
			$this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}

		$this->restore_locale();
	}

	/**
	 * Get html content.
	 *
	 * @access public
	 * @return string Email html content.
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => false,
				'email'         => $this,
				'vendor_name'   => WCV_Vendors::get_vendor_shop_name( $this->vendor_id ),
			),
			'woocommerce',
			$this->template_base
		);
	}

	/**
	 * Get email plain content.
	 *
	 * @access public
	 * @return string email plain content
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => true,
				'email'         => $this,
				'vendor_name'   => WCV_Vendors::get_vendor_shop_name( $this->vendor_id ),
			),
			'woocommerce',
			$this->template_base
		);
	}

	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'wcvendors-pro' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wcvendors-pro' ),
				'default' => 'yes',
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'wcvendors-pro' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'wcvendors-pro' ), $this->subject ),
				'placeholder' => '',
				'default'     => '',
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'wcvendors-pro' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'wcvendors-pro' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'wcvendors-pro' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wcvendors-pro' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'     => __( 'Plain text', 'wcvendors-pro' ),
					'html'      => __( 'HTML', 'wcvendors-pro' ),
					'multipart' => __( 'Multipart', 'wcvendors-pro' ),
				),
			),
			'period'    => array(
				'title'       => __( 'Sending period', 'wcvendors-pro' ),
				'type'        => 'number',
				'description' => __( 'This controls how many days this email is sent after vendor mark an order shipped.', 'wcvendors-pro' ),
				'placeholder' => '',
				'default'     => 7,
				'custom_attributes' => array(
					'min' => 1,
					'step' => 1,
				),
			),
		);
	}
}
