<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store Total Sales.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/widgets
 * @author     Lindeni Mahlalela, WC Vendors
 * @version    1.5.8
 * @extends    WC_Widget
 */
class WCV_Widget_Store_Total_Sales extends WC_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'wcv widget_store_total_sales';
		$this->widget_description = __( 'Shows the store\'s number of sales.', 'wcvendors-pro' );
		$this->widget_id          = 'wcv_store_total_sales';
		$this->widget_name        = __( 'WC Vendors Pro Store Total Sales', 'wcvendors-pro' );
		$this->settings           = array(
			'title'       => array(
				'type'  => 'text',
				'std'   => __( 'Store Total Sales', 'wcvendors-pro' ),
				'label' => __( 'Title', 'wcvendors-pro' ),
			),
			'label'       => array(
				'type'  => 'text',
				'std'   => __( 'Total sales: ', 'wcvendors-pro' ),
				'label' => __( 'Label', 'wcvendors-pro' ),
			),
			'show_label'  => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show Label', 'wcvendors-pro' ),
			),
			'label_after' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show label after number', 'wcvendors-pro' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Output the store total sales count.
	 *
	 * @see     WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @since   1.5.8
	 * @version 1.5.8
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

		$label       = isset( $instance['label'] ) ? $instance['label'] : $this->settings['label']['std'];
		$show_label  = isset( $instance['show_label'] ) ? $instance['show_label'] : $this->settings['show_label']['std'];
		$label_after = isset( $instance['label_after'] ) ? $instance['label_after'] : $this->settings['label_after']['std'];

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

		$vendor_shop = ! empty( $vendor_shop ) ? $vendor_shop : '';

		if ( ! isset( $vendor_id ) ) {
			return;
		}

		$total_sales = apply_filters( 'wcv_store_total_sales_count', WCVendors_Pro_Vendor_Controller::get_vendor_sales_count( $vendor_id ), $vendor_id );

		$this->widget_start( $args, $instance );

		$label            = $show_label == 1 ? esc_attr( $label ) : '';
		$total_sales_text = ( $label_after == 1 ) ? ( $total_sales . ' ' . $label ) : ( $label . ' ' . $total_sales );

		$vendors_page_id = get_option( 'wcvendors_vendors_page_id', null );
		if ( is_numeric( $vendors_page_id ) ) {
			$page = get_permalink( $vendors_page_id );
		} else {
			$page = home_url( '/vendors' );
		}

		$url = add_query_arg( array( 'orderby' => 'popularity' ), esc_url( $page . $vendor_shop ) );

		echo apply_filters(
			'wcv_store_total_sales_html',
			sprintf(
				__( '<p class="wcv-widget-total-sales"><a href="%1$s">%2$s</a></p>', 'wcvendors-pro' ),
				$url,
				$total_sales_text
			),
			$total_sales
		);

		$this->widget_end( $args );
	}
}
