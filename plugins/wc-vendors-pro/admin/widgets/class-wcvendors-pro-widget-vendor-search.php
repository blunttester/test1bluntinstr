<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vendor Search Widget.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/widgets
 * @author     Lindeni Mahlalela <WC Vendors>
 * @version    1.5.6
 * @extends    WC_Widget
 */
class WCV_Widget_Vendor_Search extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->widget_cssclass    = 'wcv wcv_vendor_search';
		$this->widget_description = sprintf( __( 'A Search box for searching for %s by username or store name.', 'wcvendors-pro' ), wcv_get_vendor_name( false, true ) );
		$this->widget_id          = 'wcv_vendor_search';
		$this->widget_name        = sprintf( __( 'WC Vendors Pro %s Search', 'wcvendors-pro' ), wcv_get_vendor_name() );
		$this->settings           = array(
			'notice' => array(
				'type'  => 'show_notice',
				'std'   => '',
				'label' => '',
			),
			'title'  => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Title', 'wcvendors-pro' ),
			),
		);

		add_action( 'woocommerce_widget_field_show_notice', array( $this, 'show_notice_field' ), 10, 4 );

		parent::__construct();
	}

	/**
	 * Output widget.
	 *
	 * @see   WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @since 1.5.6
	 */
	public function widget( $args, $instance ) {

		$vendors_page_id = get_option( 'wcvendors_vendors_page_id', 0 );

		if ( ! $vendors_page_id ) {
			return;
		}

		$this->widget_start( $args, $instance );

		global $vendor_search_form_index;

		$vendor_id = 0;

		ob_start();

		if ( empty( $vendor_search_form_index ) ) {
			$vendor_search_form_index = 0;
		}

		if ( WCV_Vendors::is_vendor_page() ) {
			$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
			$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
		} else {

			if ( isset( $_GET['wcv_vendor_id'] ) ) {
				$vendor_id = $_GET['wcv_vendor_id'];
			}
		}

		do_action( 'pre_get_wcv_vendor_search_form' );

		wc_get_template(
			'vendor-search-form.php',
			array(
				'index'     => $vendor_search_form_index ++,
				'vendor_id' => $vendor_id,
			),
			'wc-vendors/front/',
			plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . '/templates/front/'
		);

		$form = apply_filters( 'get_wcv_vendor_search_form', ob_get_clean() );

		echo $form;

		$this->widget_end( $args );

	}

	/**
	 * Custom google maps field so that it can be formatted correctly.
	 */
	public function show_notice_field( $key, $value, $setting, $instance ) { ?>
		<p><?php printf( __( 'Please note that this widget relies on the %s list page and the <code>[wcv_pro_vendorslist]</code>  short code. Please make sure the page is set and the shortcode exists on the page.', 'wcvendors-pro' ), wcv_get_vendor_name() ); ?>
		<a href="<?php echo admin_url( 'admin.php?page=wcv-settings&tab=display' ); ?>"><?php _e( 'Choose page here.', 'wcvendors-pro' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Widget settings form
	 *
	 * @return void
	 * @since 1.5.6
	 */
	public function form( $instance ) {
		parent::form( $instance );
	}
}
