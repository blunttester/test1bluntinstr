<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store Social Media Widget.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/widgets
 * @author     Lindeni Mahlalela
 * @version    1.5.6
 * @extends    WC_Widget
 */
class WCV_Widget_Store_Social_Media extends WC_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'wcv widget_store_store_social_media';
		$this->widget_description = __( 'Shows social media icons.', 'wcvendors-pro' );
		$this->widget_id          = 'wcv_store_store_social_media';
		$this->widget_name        = __( 'WC Vendors Pro Store Social Media', 'wcvendors-pro' );
		$this->settings           = array_merge(
			array(
				'title'        => array(
					'type'  => 'text',
					'std'   => __( 'Store Social Media', 'wcvendors-pro' ),
					'label' => __( 'Title', 'wcvendors-pro' ),
				),
				'icon_size'    => array(
					'type'    => 'select',
					'std'     => 'sm',
					'label'   => __( 'Icon Size', 'wcvendors-pro' ),
					'options' => apply_filters(
						'wcv_social_widget_icon_sizes',
						array(
							'sm' => 'Small',
							'md' => 'Medium',
							'lg' => 'Large',
						)
					),
				),
				'heading'      => array(
					'type'  => 'text',
					'std'   => __( 'Like us on social media.', 'wcvendors-pro' ),
					'label' => __( 'Heading', 'wcvendors-pro' ),
				),
				'show_heading' => array(
					'type'  => 'checkbox',
					'std'   => 0,
					'label' => __( 'Show Heading', 'wcvendors-pro' ),
				),
			),
			$this->get_visible_settings()
		);

		parent::__construct();
	}

	/**
	 * Render visible social setting programatically.
	 *
	 * @return array.
	 */
	public function get_visible_settings() {
		$visible_settings = array();
		foreach ( wcv_get_social_media_settings() as $key => $setting ) {
			$visible_settings[ 'show_' . $key ] = array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => sprintf( __( 'Show %s', 'wcvendors-pro' ), $setting['label'] ),
			);
		}
		return $visible_settings;
	}

	/**
	 * Output the social media icons widget.
	 *
	 * @see   WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @since 1.5.6
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


		$icon_size = isset( $instance['icon_size'] ) ? $instance['icon_size'] : $this->settings['icon_size']['std'];

		$show_heading = isset( $instance['show_heading'] ) ? $instance['show_heading'] : $this->settings['show_heading']['std'];
		$heading      = isset( $instance['heading'] ) ? $instance['heading'] : $this->settings['heading']['std'];

		$hidden = array();

		foreach ( wcv_get_social_media_settings() as $key => $setting ) {
			$option_key = 'show_' . $key;
			$show       = isset( $instance[ $option_key ] ) ? $instance[ $option_key ] : $this->settings[ $option_key ]['std'];
			if ( ! $show ) {
				$hidden[] = $key;
			}
		}

		$this->widget_start( $args, $instance );

		echo $show_heading ? '<p class="wcv-widget-description-heading">' . esc_attr( $heading ) . '</p>' : '';

		echo wcv_format_store_social_icons( $vendor_id, $icon_size, $hidden );

		$this->widget_end( $args );
	}
}
