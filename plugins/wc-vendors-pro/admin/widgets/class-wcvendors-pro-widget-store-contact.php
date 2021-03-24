<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Store Widget.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin/widgets
 * @author     WC Vendors, Lindeni Mahlalela
 * @version    1.7.5
 * @extends    WC_Widget
 */
class WCV_Widget_Store_Contact extends WC_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'wcv widget_store_contact';
		$this->widget_description = __( 'Shows the contact details of a WC Vendors Shop.', 'wcvendors-pro' );
		$this->widget_id          = 'wcv_store_contact_widget';
		$this->widget_name        = __( 'WC Vendors Pro Contact Store', 'wcvendors-pro' );
		$this->settings           = array(
			'title'                                     => array(
				'type'  => 'text',
				'std'   => __( 'Contact Store', 'wcvendors-pro' ),
				'label' => __( 'Title', 'wcvendors-pro' ),
			),
			'contact_text'                              => array(
				'type'  => 'text',
				'std'   => __( 'Use these details to contact us.', 'wcvendors-pro' ),
				'label' => __( 'Contact us text', 'wcvendors-pro' ),
			),
			'show_contact_text'                         => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show contact us text', 'wcvendors-pro' ),
			),
			'show_shop_name'                            => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show shopname', 'wcvendors-pro' ),
			),
			'show_shop_url'                             => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show shop URL', 'wcvendors-pro' ),
			),
			'show_phone_number'                         => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show phone number', 'wcvendors-pro' ),
			),
			'show_email_address'                        => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show email address', 'wcvendors-pro' ),
			),
			'show_quick_contact_form'                   => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show quick contact form', 'wcvendors-pro' ),
			),
			'required_quick_contact_form_subject'       => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Mark Subject Field as Required', 'wcvendors-pro' ),
			),
			'required_quick_contact_form_email_address' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Mark Email Address Field as Required', 'wcvendors-pro' ),
			),
			'required_quick_contact_form_message'       => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Mark Message Field as Required', 'wcvendors-pro' ),
			),
			'show_opening_hours'                        => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show opening hours', 'wcvendors-pro' ),
			),
			'show_hours_after'                          => array(
				'type'    => 'select',
				'std'     => 'title',
				'label'   => __( 'Show store hours after', 'wvendors-pro' ),
				'options' => array(
					'text' => __( 'Contact us text', 'wcvendors-pro' ),
					'info' => __( 'Contact information', 'wcvendors-pro' ),
					'form' => __( 'Contact form', 'wcvendors-pro' ),
				),
			),
			'cc_admin'                                  => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Send a copy to the admin', 'wcvendors-pro' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Output the contact widget
	 *
	 * @see WP_Widget
	 * @version    1.7.5
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $post;

		if ( ! is_woocommerce() ) {
			return;
		}

		if ( ! $post ) {
			return;
		}

		if ( ! WCV_Vendors::is_vendor_page() && ! WCV_Vendors::is_vendor_product_page( $post->post_author ) ) {
			return;
		}

		$show_phone_number                         = isset( $instance['show_phone_number'] ) ? $instance['show_phone_number'] : $this->settings['show_phone_number']['std'];
		$show_shop_name                            = isset( $instance['show_shop_name'] ) ? $instance['show_shop_name'] : $this->settings['show_shop_name']['std'];
		$show_shop_url                             = isset( $instance['show_shop_url'] ) ? $instance['show_shop_url'] : $this->settings['show_shop_url']['std'];
		$show_email_address                        = isset( $instance['show_email_address'] ) ? $instance['show_email_address'] : $this->settings['show_email_address']['std'];
		$show_contact_text                         = isset( $instance['show_contact_text'] ) ? $instance['show_contact_text'] : $this->settings['show_contact_text']['std'];
		$show_quick_contact_form                   = isset( $instance['show_quick_contact_form'] ) ? $instance['show_quick_contact_form'] : $this->settings['show_quick_contact_form']['std'];
		$required_quick_contact_form_subject       = isset( $instance['required_quick_contact_form_subject'] ) ? $instance['required_quick_contact_form_subject'] : $this->settings['required_quick_contact_form_subject']['std'];
		$required_quick_contact_form_email_address = isset( $instance['required_quick_contact_form_email_address'] ) ? $instance['required_quick_contact_form_email_address'] : $this->settings['required_quick_contact_form_email_address']['std'];
		$required_quick_contact_form_message       = isset( $instance['required_quick_contact_form_message'] ) ? $instance['required_quick_contact_form_message'] : $this->settings['required_quick_contact_form_message']['std'];
		$contact_text                              = isset( $instance['contact_text'] ) ? $instance['contact_text'] : $this->settings['contact_text']['std'];
		$cc_admin                                  = isset( $instance['cc_admin'] ) ? $instance['cc_admin'] : $this->settings['cc_admin']['std'];
		$show_opening_hours                        = isset( $instance['show_opening_hours'] ) ? $instance['show_opening_hours'] : $this->settings['show_opening_hours']['std'];
		$show_hours_after                          = isset( $instance['show_hours_after'] ) ? $instance['show_hours_after'] : $this->settings['show_hours_after']['std'];

		if ( WCV_Vendors::is_vendor_page() ) {
			$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
			$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
		} elseif ( is_singular( 'product' ) && WCV_Vendors::is_vendor_product_page( $post->post_author ) ) {
			$vendor_id = $post->post_author;
		} else {
			if ( isset( $_GET['wcv_vendor_id'] ) ) {
				$vendor_id = $_GET['wcv_vendor_id'];
			}
		}

		if ( ! isset( $vendor_id ) ) {
			return;
		}

		if ( empty( $vendor_shop ) ) {
			$vendor_shop = WCV_Vendors::get_vendor_shop_name( $vendor_id );
		}

		$user            = get_user_by( 'id', $vendor_id );
		$vendor_settings = array_map(
			function ( $a ) {
					return $a[0];
			},
			get_user_meta( $vendor_id )
		);

		$this->widget_start( $args, $instance );

		wc_get_template(
			'vendor-quick-contact.php',
			array(
				'show_phone_number'                   => $show_phone_number,
				'show_shop_name'                      => $show_shop_name,
				'show_shop_url'                       => $show_shop_url,
				'show_email_address'                  => $show_email_address,
				'show_contact_text'                   => $show_contact_text,
				'show_quick_contact_form'             => $show_quick_contact_form,
				'show_opening_hours'                  => $show_opening_hours,
				'show_hours_after'                    => $show_hours_after,
				'contact_text'                        => $contact_text,
				'cc_admin'                            => $cc_admin,
				'vendor_shop'                         => $vendor_shop,
				'vendor_id'                           => $vendor_id,
				'user'                                => $user,
				'vendor_settings'                     => $vendor_settings,
				'required_quick_contact_form_subject' => $required_quick_contact_form_subject,
				'required_quick_contact_form_email_address' => $required_quick_contact_form_email_address,
				'required_quick_contact_form_message' => $required_quick_contact_form_message,
			),
			'wc-vendors/front/',
			WCV_PRO_ABSPATH_TEMPLATES . 'front/'
		);

		$this->widget_end( $args );
	}
}
