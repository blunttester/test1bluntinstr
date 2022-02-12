<?php
/**
 * Bookshop Class
 *
 * @package  Bookshop
 * @author   WooThemes
 * @since    1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Bookshop' ) ) {

	/**
	 * The main Bookshop class
	 */
	class Bookshop {
		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts',                 array( $this, 'enqueue_styles' ), 40 ); // After Storefront
			add_filter( 'storefront_woocommerce_args',        array( $this, 'woocommerce_support' ) );
			add_filter( 'subcategory_archive_thumbnail_size', array( $this, 'category_image_size' ) );
			add_filter( 'body_class',                         array( $this, 'body_classes' ) );
		}

		/**
		 * Adds custom classes to the array of body classes.
		 *
		 * @param array $classes Classes for the body element.
		 * @return array
		 */
		public function body_classes( $classes ) {
			global $storefront_version;

			if ( version_compare( $storefront_version, '2.3.0', '>=' ) ) {
				$classes[] = 'storefront-2-3';
			}

			return $classes;
		}

		/**
		 * Enqueue Storefront Styles
		 *
		 * @return void
		 */
		public function enqueue_styles() {
			global $storefront_version, $bookshop_version;

			/**
			 * Styles
			 */
			wp_enqueue_style( 'storefront-style', get_template_directory_uri() . '/style.css', $storefront_version );
			wp_style_add_data( 'storefront-child-style', 'rtl', 'replace' );

			/**
			 * Javascript
			 */
			wp_enqueue_script( 'bookshop', get_stylesheet_directory_uri() . '/assets/js/bookshop.min.js', array( 'jquery' ), $bookshop_version, true );

			/**
			 * Fonts
			 */
			$fonts_url       = '';

			$font_families   = array();
			$font_families[] = 'Merriweather:400,400italic,700';
			$font_families[] = 'Lato:300,400,400italic,700,700italic,900';

			$query_args      = array(
				'family' => urlencode( implode( '|', $font_families ) ),
				'subset' => urlencode( 'latin,latin-ext' ),
			);

			$fonts_url        = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );

			wp_enqueue_style( 'bookshop-fonts', $fonts_url, array(), null );
		}

		/**
		 * Override Storefront default theme settings for WooCommerce.
		 * @return array the modified arguments
		 */
		public function woocommerce_support( $args ) {
			$args['single_image_width']              = 438;
			$args['thumbnail_image_width']           = 231;
			$args['product_grid']['default_columns'] = 4;
			$args['product_grid']['default_rows']    = 3;

			return $args;
		}

		/**
		 * Override default thumbnail size for category images
		 * @return string the new size
		 */
		public function category_image_size( $size ) {
			return 'medium_large'; // 768px
		}
	}
}

return new Bookshop();
