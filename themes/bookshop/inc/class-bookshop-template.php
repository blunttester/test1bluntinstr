<?php
/**
 * Bookshop_Template Class
 *
 * @package  Bookshop
 * @author   WooThemes
 * @since    1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Bookshop_Template' ) ) {

	/**
	 * The Bookshop template class
	 */
	class Bookshop_Template {

		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			add_action( 'storefront_header',                        array( $this, 'primary_navigation_wrapper' ),       45 );
			add_action( 'storefront_header',                        array( $this, 'primary_navigation_wrapper_close' ), 65 );
			add_action( 'init',                                     array( $this, 'adjust_layout' ) );
			add_filter( 'storefront_related_products_args',         array( $this, 'related_products_args' ) );
			add_filter( 'storefront_product_categories_args',       array( $this, 'homepage_categories' ) );
			add_filter( 'storefront_recent_products_args',          array( $this, 'homepage_products' ), 5 );
			add_filter( 'storefront_featured_products_args',        array( $this, 'homepage_products' ), 5 );
			add_filter( 'storefront_popular_products_args',         array( $this, 'homepage_products' ), 5 );
			add_filter( 'storefront_on_sale_products_args',         array( $this, 'homepage_products' ), 5 );
			add_filter( 'storefront_best_selling_products_args',    array( $this, 'homepage_products' ), 5 );
			add_action( 'woocommerce_before_shop_loop_item_title',  array( $this, 'display_author' ), 11 );
			add_action( 'woocommerce_after_shop_loop_item_title',   array( $this, 'display_format' ), 1 );
			add_action( 'woocommerce_single_product_summary',       array( $this, 'display_author' ), 1 );
			add_action( 'woocommerce_single_product_summary',       array( $this, 'display_format' ), 7 );
			remove_action( 'woocommerce_single_product_summary',    'woocommerce_template_single_price', 10 );
			add_action( 'woocommerce_single_product_summary',       'woocommerce_template_single_price', 25 );
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'output_upsells' ), 15 );

			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.3', '<' ) ) {
				add_filter( 'storefront_loop_columns',              array( $this, 'return_4' ) );
			}
		}

		/**
		 * Display the author (pa_writer) product attribute.
		 *
		 * @return void
		 */
		public function display_author() {
			global $product;
			$author = $product->get_attribute( apply_filters( 'bookshop_author_attribute', $attribute = 'pa_writer' ) );

			if ( $author ) {
				echo '<span class="author">' . esc_attr( $author ) . '</span>';
			}
		}

		/**
		 * Display the format (pa_format) product attribute.
		 *
		 * @return void
		 */
		public function display_format() {
			global $product;
			$format = $product->get_attribute( apply_filters( 'bookshop_format_attribute', $attribute = 'pa_format' ) );

			if ( $format ) {
				echo '<span class="format">' . esc_attr( $format ) . '</span>';
			}
		}

		/**
		 * Primary navigation wrapper
		 *
		 * @return void
		 */
		public function primary_navigation_wrapper() {
			echo '<section class="bookshop-primary-navigation">';
		}

		/**
		 * Primary navigation wrapper close
		 *
		 * @return void
		 */
		public function primary_navigation_wrapper_close() {
			echo '</section>';
		}

		/**
		 * Adjust the default storefront layout to suit Book Shop
		 *
		 * @return void
		 */
		public function adjust_layout() {
			global $storefront_version;

			// Secondary nav.
			remove_action( 'storefront_header', 'storefront_secondary_navigation', 30 );
			add_action( 'storefront_header',    'storefront_secondary_navigation', 10 );

			if ( storefront_is_woocommerce_activated() ) {

				// Cart.
				remove_action( 'storefront_header', 'storefront_header_cart', 60 );
				add_action( 'storefront_header',    'storefront_header_cart', 41 );

				// Upsells.
				remove_action( 'woocommerce_after_single_product_summary', 'storefront_upsell_display', 15 );

				if ( version_compare( $storefront_version, '2.3.0', '>=' ) ) {
					remove_action( 'storefront_header', 'storefront_header_container_close', 41 );
					remove_action( 'storefront_header', 'storefront_primary_navigation_wrapper', 42 );
					remove_action( 'storefront_header', 'storefront_primary_navigation_wrapper_close', 68 );
					add_action( 'storefront_header', 'storefront_header_container_close', 100 );
				}
			}
		}

		/**
		 * Set the default column layout to 4 products
		 *
		 * @param int $cols number of columns.
		 */
		public function return_4( $cols ) {
			$cols = 4;

			return $cols;
		}

		/**
		 * Set the number of related products to match the product archive columns
		 *
		 * @param  array $args related products args.
		 */
		public function related_products_args( $args ) {
			$args['posts_per_page'] = 4;
			$args['columns']        = 4;

			return $args;
		}

		/**
		 * Display all upsells in a 4 column layout.
		 *
		 * @return void
		 */
		public function output_upsells() {
			woocommerce_upsell_display( -1, apply_filters( 'bookshop_upsells_columns', 4 ) );
		}

		/**
		 * Specified how many categories to display on the homepage
		 *
		 * @param array $args The arguments used to control the layout of the homepage category section.
		 */
		public function homepage_categories( $args ) {
			$args['limit']   = 7;
			$args['columns'] = 6;

			return $args;
		}

		/**
		 * Specified how many products to display on the homepage
		 *
		 * @param array $args The arguments used to control the layout of the homepage product sections.
		 */
		public function homepage_products( $args ) {
			$args['limit']   = 5;
			$args['columns'] = 5;

			return $args;
		}
	}
}

return new Bookshop_Template();
