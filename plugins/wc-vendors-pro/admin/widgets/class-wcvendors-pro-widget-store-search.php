<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store Search Widget.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/widgets
 * @author     Jamie Madden <support@wcvendors.com>
 * @version    1.4.4
 * @extends    WC_Widget
 */
class WCV_Widget_Store_Search extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'wcv wcv_store_search';
		$this->widget_description = sprintf( __( 'A Search box for %1$s stores only. Will not display if the page is not the main %2$s store page.', 'wcvendors-pro' ), wcv_get_vendor_name(), wcv_get_vendor_name() );
		$this->widget_id          = 'wcv_store_search';
		$this->widget_name        = __( 'WC Vendors Pro Store Search', 'wcvendors-pro' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Title', 'wcvendors-pro' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$this->widget_start( $args, $instance );

		global $store_search_form_index;

		$vendor_id = 0;

		ob_start();

		if ( empty( $store_search_form_index ) ) {
			$store_search_form_index = 0;
		}

		if ( WCV_Vendors::is_vendor_page() ) {
			$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
			$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
		} else {

			if ( isset( $_GET['wcv_vendor_id'] ) ) {
				$vendor_id = $_GET['wcv_vendor_id'];
			}
		}

		do_action( 'pre_get_wcv_store_search_form' );

		wc_get_template(
			'vendor-searchform.php',
			array(
				'index'     => $store_search_form_index ++,
				'vendor_id' => $vendor_id,
			),
			'wc-vendors/front/',
			plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . '/templates/front/'
		);

		$form = apply_filters( 'get_wcv_store_search_form', ob_get_clean() );

		echo $form;

		$this->widget_end( $args );

	}
}
