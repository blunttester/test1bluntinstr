<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store Short Description Widget.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/widgets
 * @author     Lindeni Mahlalela
 * @version    1.5.4
 * @extends    WC_Widget
 */
class WCV_Widget_Store_Short_Description extends WC_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'wcv widget_store_short_description';
		$this->widget_description = __( 'Shows the store\'s short description.', 'wcvendors-pro' );
		$this->widget_id          = 'wcv_store_short_description';
		$this->widget_name        = __( 'WC Vendors Pro Store Short Description', 'wcvendors-pro' );
		$this->settings           = array(
			'title'        => array(
				'type'  => 'text',
				'std'   => __( 'Store short description', 'wcvendors-pro' ),
				'label' => __( 'Title', 'wcvendors-pro' ),
			),
			'heading'      => array(
				'type'  => 'text',
				'std'   => __( 'About us', 'wcvendors-pro' ),
				'label' => __( 'Heading', 'wcvendors-pro' ),
			),
			'show_heading' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show heading', 'wcvendors-pro' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Output the address and map widget.
	 *
	 * @see   WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @since 1.5.4
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

		$show_heading = isset( $instance['show_heading'] ) ? $instance['show_heading'] : $this->settings['show_heading']['std'];
		$heading      = isset( $instance['heading'] ) ? $instance['heading'] : $this->settings['heading']['std'];

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

		$short_description = get_user_meta( $vendor_id, 'pv_shop_description', true );

		$this->widget_start( $args, $instance );

		echo $show_heading ? '<p class="wcv-widget-description-heading">' . esc_attr( $heading ) . '</p>' : '';
		echo $short_description ? '<div class="wcv-widget-shop-description">' . wp_kses_post( $short_description ) . '</div>' : '';

		$this->widget_end( $args );
	}
}
