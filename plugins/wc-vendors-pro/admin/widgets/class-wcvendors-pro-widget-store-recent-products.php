<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store Recent Products Widget.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/widgets
 * @author     Lindeni Mahlalela
 * @version    1.5.6
 * @extends    WC_Widget
 */
class WCV_Widget_Recent_Products extends WC_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'wcv widget_recent_products';
		$this->widget_description = __( "Shows the store's recent products.", 'wcvendors-pro' );
		$this->widget_id          = 'wcv_store_recent_products';
		$this->widget_name        = __( 'WC Vendors Pro Recent Products', 'wcvendors-pro' );

		$sizes = get_intermediate_image_sizes();

		$image_sizes = array();
		foreach ( $sizes as $size ) {
			$image_sizes[ $size ] = ucfirst( implode( ' ', explode( '_', $size ) ) );
		}

		$this->settings = array(
			'title'          => array(
				'type'  => 'text',
				'std'   => __( 'Recent Products', 'wcvendors-pro' ),
				'label' => __( 'Title', 'wcvendors-pro' ),
			),
			'number'         => array(
				'type'  => 'number',
				'std'   => 5,
				'min'   => 0,
				'step'  => 1,
				'max'   => 100,
				'label' => __( 'Number of products', 'wcvendors-pro' ),
			),
			'show_ratings'   => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show average rating', 'wcvendors-pro' ),
			),
			'show_image'     => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show main image', 'wcvendors-pro' ),
			),
			'image_size'     => array(
				'type'    => 'select',
				'std'     => 'thumbnail',
				'options' => $image_sizes,
				'label'   => __( 'Image Size', 'wcvendors-pro' ),
			),
			'show_excerpt'   => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show excerpt', 'wcvendors-pro' ),
			),
			'excerpt_length' => array(
				'type'  => 'number',
				'std'   => 200,
				'min'   => 0,
				'step'  => 1,
				'max'   => 10000,
				'label' => __( 'Excerpt length', 'wcvendors-pro' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Output the recent products widget.
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

		$number         = isset( $instance['number'] ) ? $instance['number'] : $this->settings['number']['std'];
		$show_excerpt   = isset( $instance['show_excerpt'] ) ? $instance['show_excerpt'] : $this->settings['show_excerpt']['std'];
		$show_ratings   = isset( $instance['show_ratings'] ) ? $instance['show_ratings'] : $this->settings['show_ratings']['std'];
		$show_image     = isset( $instance['show_image'] ) ? $instance['show_image'] : $this->settings['show_image']['std'];
		$excerpt_length = isset( $instance['excerpt_length'] ) ? $instance['excerpt_length'] : $this->settings['excerpt_length']['std'];
		$image_size     = isset( $instance['image_size'] ) ? $instance['image_size'] : $this->settings['image_size']['std'];

		$product_args = array(
			'numberposts' => $number,
			'order'       => 'desc',
			'orderby'     => 'date',
			'post_status' => 'publish',
		);

		$vendor_shop     = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id       = WCV_Vendors::get_vendor_id( $vendor_shop );
		$products        = WCVendors_Pro_Vendor_Controller::get_products_by_id( $vendor_id, $product_args );
		$vendor_shop_url = WCV_Vendors::get_vendor_shop_page( $vendor_id );

		$this->widget_start( $args, $instance );

		if ( $products ) { ?>

			<div class="wcv-recent-products-widget">
			<?php

			foreach ( $products as $product_id ) {

				$product = wc_get_product( $product_id );

				$average_rating = WCVendors_Pro_Ratings_Controller::get_product_average_rating( $product->get_id() );
				$product_link   = $product->get_permalink();
				$product_title  = $product->get_title();
				$excerpt        = $product->get_short_description();
				$image          = wp_get_attachment_image_src(
					$product->get_image_id(),
					array(
						'50',
						'50',
					),
					'',
					array( 'class' => 'img-responsive' )
				);

				// This outputs the star rating
				$stars = '';
				for ( $i = 1; $i <= stripslashes( $average_rating ); $i ++ ) {
					$stars .= '<svg class="wcv-icon wcv-icon-sm">
                                <use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-star"></use>
                            </svg>';
				}

				for ( $i = stripslashes( $average_rating ); $i < 5; $i ++ ) {
					$stars .= '<svg class="wcv-icon wcv-icon-sm">
                                <use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-star-o"></use>
                            </svg>';
				}
				?>

				<div class="wcv-widget-product">
					<?php do_action( 'wcvendors_pro_widget_product_item_start', $args ); ?>

					<a href="<?php echo esc_url( $product_link ); ?>">
						<?php echo $show_image ? $product->get_image( $image_size ) : ''; ?>
						<span class="product-title"><?php echo $product_title; ?></span>
					</a>

					<?php if ( $show_ratings && ! empty( $stars ) ) : ?>
						<br/>
						<?php echo $stars; ?>
					<?php endif; ?>
					<br/>
					<?php echo $product->get_price_html(); ?>

					<?php
					if ( $show_excerpt && ! empty( $excerpt ) ) :
						printf( '<p>%s</p>', substr( $excerpt, 0, $excerpt_length ) );
					endif;
					?>

					<?php do_action( 'wcvendors_pro_widget_product_item_end', $args ); ?>
				</div>

				<hr/>

				<?php
			}
			?>
			</div>
			<?php
		} else {
			_e( 'No products found.', 'wcvendors-pro' );
		}

		$this->widget_end( $args );
	}
}
