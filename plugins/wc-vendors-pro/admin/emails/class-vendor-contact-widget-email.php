<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors Pro Contact Widget Emails Class
 *
 * @since       1.5.4
 * @author      WC Vendors, Lindeni Mahlalela
 * @package     WCVendors_Pro
 * @subpackage  WCVendors_Pro/emails/
 */
class WC_Vendors_Pro_Email_Vendor_Contact_Widget extends WC_Email {

	/**
	 * Constructor
	 */
	function __construct() {
		$this->id          = 'store_contact';
		$this->title       = sprintf( __( '%s Store Contact Widget', 'wc-vendors' ), wcv_get_vendor_name() );
		$this->description = sprintf( __( '%s contact widget submitted', 'wc-vendors' ), wcv_get_vendor_name() );

		$this->heading = __( 'Customer Enquiry [{storename}]', 'wc-vendors' );
		$this->subject = __( '[{storename} - Customer Enquiry] {subject}', 'wc-vendors' );

		$this->template_base  = WCV_PRO_ABSPATH_TEMPLATES . '/emails/';
		$this->template_html  = 'vendor-contact-widget.php';
		$this->template_plain = 'plain/vendor-contact-widget.php';

		// Call parent constuctor
		parent::__construct();

		// Other
		$this->sender_email   = '';
		$this->sender_subject = '';
		$this->sender_message = '';
		$this->shop_name      = '';
		$this->recipient      = '';
	}

	/**
	 * Send the email tot the vendor
	 *
	 * @access public
	 * @return void
	 */
	public function send_email() {

		if ( wp_verify_nonce( $_REQUEST['nonce'], 'wcv_quick_contact' ) || ! isset( $_REQUEST['vendor'] ) ) {
			$vendor_id = esc_attr( $_REQUEST['vendor'] );

			if ( ! WCV_Vendors::is_vendor( $vendor_id ) ) {
				return;
			}

			$vendor_settings = array_map(
				function ( $a ) {
						return $a[0];
				},
				get_user_meta( $vendor_id )
			);
			$shop_name       = isset( $vendor_settings['pv_shop_name'] ) ? $vendor_settings['pv_shop_name'] : '';
			$vendor_email    = apply_filters( 'wcv_quick_contact_vendor_email', get_userdata( $vendor_id )->user_email );

			$this->sender_email   = esc_attr( $_REQUEST['wcv_quick_email_address'] );
			$this->sender_subject = apply_filters( 'wcv_quick_contact_subject', esc_attr( $_REQUEST['wcv_quick_email_subject'] ), $_POST );
			$this->sender_message = apply_filters( 'wcv_quick_contact_message', esc_attr( $_REQUEST['wcv_quick_email_message'] ), $_POST );
			$this->shop_name      = apply_filters( 'wcv_quick_contact_shop_name', esc_attr( $shop_name ) );
			$this->recipient      = apply_filters( 'wcv_quick_contact_recipient', esc_attr( $vendor_email ) );

			$headers[] = "Content-Type: text/html; charset=UTF-8\r\n";
			$headers[] = "Reply-To: {$this->sender_email}\r\n";

			$cc_admin = esc_attr( $_POST['cc_admin'] );
			if ( $cc_admin == 1 ) {
				$headers[] = 'Cc: ' . apply_filters( 'wcv_vendor_contact_cc_admin_email', get_option( 'admin_email' ) ) . "\r\n";
			}
			$headers = apply_filters( 'wcv_quick_contact_headers', $headers, $_POST );

			$subject     = apply_filters( 'wcv_quick_contact_final_subject', $this->get_subject(), $_POST );
			$content     = apply_filters( 'wcv_quick_contact_message_content', $this->get_content(), $_POST );
			$attachments = apply_filters( 'wcv_quick_contact_attachments', $this->get_attachments(), $_POST );

			/**
			 * Do something before sending the email.
			 * Make the whole $_POST object available to the action hook
			 * Make the email components available to the user
			 */
			do_action( 'wcv_quick_contact_before_send_email', $_POST, $this->recipient, $subject, $content, $headers, $attachments );

			// send the email through WordPress
			if ( $this->send( $this->recipient, $subject, $content, $headers, $attachments ) ) {
				wp_send_json_success(
					array(
						'message'     => __( 'Your message was sent.', 'wcvendors-pro' ),
						'status_code' => 1,
					)
				);
			} else {
				wp_send_json_error(
					array(
						'message'     => __( 'Failed to send the message, please try again later.', 'wcvendors-pro' ),
						'status_code' => 0,
					)
				);
			}
		}
	}

	/**
	 * Get the email template heading
	 *
	 * @return string
	 * @since  1.5.4
	 * @access public
	 */
	function get_heading() {
		$heading = parent::get_heading();
		$heading = str_replace( '{storename}', $this->shop_name, $heading );

		return $heading;
	}

	/**
	 * Get the email subject
	 *
	 * @return string the email subject
	 * @since 1.5.4
	 */
	public function get_subject() {
		$subject = parent::get_subject();
		$subject = str_replace( '{storename}', $this->shop_name, $subject );
		$subject = str_replace( '{subject}', $this->sender_subject, $subject );

		return $subject;
	}

	/**
	 * Get html content.
	 *
	 * @access public
	 * @return string email html content
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'message'       => $this->sender_message,
				'subject'       => $this->sender_subject,
				'email'         => $this->sender_email,
				'shop_name'     => $this->shop_name,
				'email_heading' => $this->get_heading(),
			),
			'woocommerce',
			$this->template_base
		);

		return ob_get_clean();
	}

	/**
	 * Get email plain content.
	 *
	 * @access public
	 * @return string email plain content
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'message'       => $this->sender_message,
				'subject'       => $this->sender_subject,
				'email'         => $this->sender_email,
				'shop_name'     => $this->shop_name,
				'email_heading' => $this->get_heading(),
			),
			'woocommerce',
			$this->template_base
		);

		return ob_get_clean();
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
				'title'   => __( 'Enable/Disable', 'wc-vendors' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wc-vendors' ),
				'default' => 'yes',
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'wc-vendors' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'wc-vendors' ), $this->subject ),
				'placeholder' => '',
				'default'     => '',
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'wc-vendors' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'wc-vendors' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'wc-vendors' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wc-vendors' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'     => __( 'Plain text', 'wc-vendors' ),
					'html'      => __( 'HTML', 'wc-vendors' ),
					'multipart' => __( 'Multipart', 'wc-vendors' ),
				),
			),
		);
	}
}
