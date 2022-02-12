<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store Ratings Widget.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/widgets
 * @author     Lindeni Mahlalela
 * @version    1.5.4
 * @extends    WC_Widget
 */
class WCV_Widget_Store_Ratings extends WC_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'wcv widget_store_ratingsn';
		$this->widget_description = __( 'Shows the store\'s ratings.', 'wcvendors-pro' );
		$this->widget_id          = 'wcv_store_ratings';
		$this->widget_name        = __( 'WC Vendors Pro Store Ratings', 'wcvendors-pro' );
		$this->settings           = array(
			'title'         => array(
				'type'  => 'text',
				'std'   => __( 'Store Ratings', 'wcvendors-pro' ),
				'label' => __( 'Title', 'wcvendors-pro' ),
			),
			'number'        => array(
				'type'  => 'number',
				'std'   => 5,
				'min'   => 0,
				'step'  => 1,
				'max'   => 100,
				'label' => __( 'Number of ratings', 'wcvendors-pro' ),
			),
			'show_title'    => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show title', 'wcvendors-pro' ),
			),
			'show_customer' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show customer name', 'wcvendors-pro' ),
			),
			'show_date'     => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show date', 'wcvendors-pro' ),
			),
			'show_product'  => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show product', 'wcvendors-pro' ),
			),
			'show_comment'  => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show comment', 'wcvendors-pro' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Output the ratings widget.
	 *
	 * @see   WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @since 1.5.5
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

		$number        = isset( $instance['number'] ) ? $instance['number'] : $this->settings['number']['std'];
		$show_title    = isset( $instance['show_title'] ) ? $instance['show_title'] : $this->settings['show_title']['std'];
		$show_customer = isset( $instance['show_customer'] ) ? $instance['show_customer'] : $this->settings['show_customer']['std'];
		$show_date     = isset( $instance['show_date'] ) ? $instance['show_date'] : $this->settings['show_date']['std'];
		$show_product  = isset( $instance['show_product'] ) ? $instance['show_product'] : $this->settings['show_product']['std'];
		$show_comment  = isset( $instance['show_comment'] ) ? $instance['show_comment'] : $this->settings['show_comment']['std'];

		$vendor_shop     = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id       = WCV_Vendors::get_vendor_id( $vendor_shop );
		$vendor_feedback = WCVendors_Pro_Ratings_Controller::get_vendor_feedback( $vendor_id, $number );
		$vendor_shop_url = WCV_Vendors::get_vendor_shop_page( $vendor_id );

		$this->widget_start( $args, $instance );

		if ( $vendor_feedback ) {

			foreach ( $vendor_feedback as $vf ) {

				$customer      = get_userdata( $vf->customer_id );
				$rating        = $vf->rating;
				$rating_title  = $vf->rating_title;
				$comment       = $vf->comments;
				$post_date     = date_i18n( get_option( 'date_format' ), strtotime( $vf->postdate ) );
				$customer_name = ucfirst( $customer->display_name );
				$product_link  = get_permalink( $vf->product_id );
				$product_title = get_the_title( $vf->product_id );

				// This outputs the star rating
				$stars = '';
				for ( $i = 1; $i <= stripslashes( $rating ); $i ++ ) {
					$stars .= '<svg class="wcv-icon wcv-icon-sm">
                                <use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-star"></use>
                            </svg>';
				}

				for ( $i = stripslashes( $rating ); $i < 5; $i ++ ) {
					$stars .= '<svg class="wcv-icon wcv-icon-sm">
                                <use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-star-o"></use>
                            </svg>';
				}
				?>

				<h3>
				<?php
				if ( ! empty( $rating_title ) && $show_title ) {
						echo $rating_title . ' :: ';
				}
				?>
					<?php echo $stars; ?></h3>

				<?php if ( $show_product ) : ?>
					<p><?php _e( 'Product : ', 'wcvendors-pro' ); ?><a href="<?php echo $product_link; ?>"
																	   target="_blank"><?php echo $product_title; ?></a>
					</p>
				<?php endif; ?>

				<?php if ( $show_date ) : ?>
					<span><?php __( 'Posted on', 'wcvendors-pro' ); ?><?php echo $post_date; ?></span>&nbsp;
				<?php endif; ?>

				<?php
				if ( $show_customer ) :
					printf( __( 'by %s', 'wcvendors-pro' ), $customer_name );
					?>
					<br/>
				<?php endif; ?>

				<?php if ( $show_comment ) : ?>
					<p><?php echo $comment; ?></p>
				<?php endif; ?>
				<hr/>

				<?php
			}
		} else {
			printf( __( 'No ratings have been submitted for this %s yet.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) );
		}

		$this->widget_end( $args );
	}
}
