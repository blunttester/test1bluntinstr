<?php
/**
 * Bookshop_Customizer Class
 * Makes adjustments to Storefront cores Customizer implementation.
 *
 * @author   WooThemes
 * @package  Bookshop
 * @since    1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Bookshop_Customizer' ) ) {

	/**
	 * The Bookshop Customizer class
	 */
	class Bookshop_Customizer {

		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			$theme              = wp_get_theme( 'storefront' );
			$storefront_version = $theme['Version'];

			add_action( 'wp_enqueue_scripts',                array( $this, 'add_customizer_css' ),               999 );
			add_action( 'customize_register',                array( $this, 'edit_default_controls' ),            99 );
			add_filter( 'storefront_setting_default_values', array( $this, 'get_bookshop_defaults' ) );
			add_filter( 'storefront_custom_background_args', array( $this, 'set_background' ) );

			/**
			 * The following can be removed when Storefront 2.1 lands
			 */
			add_action( 'customize_register',                array( $this, 'edit_default_customizer_settings' ), 99 );
			add_action( 'init',                              array( $this, 'default_theme_mod_values' ) );
			if ( version_compare( $storefront_version, '2.0.0', '<' ) ) {
				add_action( 'init',                          array( $this, 'default_theme_settings' ) );
			}
		}

		/**
		 * Returns an array of the desired default Storefront options
		 *
		 * @return array
		 */
		public function get_bookshop_defaults( $defaults = array() ) {
			$defaults['background_color']                       = 'F7F6F2';
			$defaults['storefront_header_background_color']     = '#ffffff';
			$defaults['storefront_header_text_color']           = '#444444';
			$defaults['storefront_header_link_color']           = '#657E21';
			$defaults['storefront_heading_color']               = '#444444';
			$defaults['storefront_accent_color']                = '#657E21';
			$defaults['storefront_button_alt_background_color'] = '#922627';
			$defaults['storefront_button_alt_text_color']       = '#ffffff';
			$defaults['storefront_button_background_color']     = '#657E21';
			$defaults['storefront_button_text_color']           = '#ffffff';
			$defaults['storefront_text_color']                  = '#777777';
			$defaults['storefront_footer_background_color']     = '#ffffff';
			$defaults['storefront_footer_link_color']           = '#657E21';
			$defaults['storefront_footer_text_color']           = '#777777';
			$defaults['storefront_footer_heading_color']        = '#444444';
			$defaults['storefront_layout']                      = 'left';

			return apply_filters( 'bookshop_default_settings', $defaults );
		}

		/**
		 * Set default Customizer settings based on Bookshop design.
		 *
		 * @param array $wp_customize the Customizer object.
		 * @uses get_bookshop_defaults()
		 * @return void
		 */
		public function edit_default_customizer_settings( $wp_customize ) {
			foreach ( Bookshop_Customizer::get_bookshop_defaults() as $mod => $val ) {
				$setting = $wp_customize->get_setting( $mod );

				if ( is_object( $setting ) ) {
					$setting->default = $val;
				}
			}
		}

		/**
		 * Returns a default theme_mod value if there is none set.
		 *
		 * @uses get_bookshop_defaults()
		 * @return void
		 */
		public function default_theme_mod_values() {
			foreach ( Bookshop_Customizer::get_bookshop_defaults() as $mod => $val ) {
				add_filter( 'theme_mod_' . $mod, function( $setting ) use ( $val ) {
					return $setting ? $setting : $val;
				});
			}
		}

		/**
		 * Sets default theme color filters for storefront color values.
		 * This function is required for Storefront < 2.0.0
		 *
		 * @uses get_bookshop_defaults()
		 * @return void
		 */
		public function default_theme_settings() {
			$prefix_regex = '/^storefront_/';
			foreach ( self::get_bookshop_defaults() as $mod => $val ) {
				if ( preg_match( $prefix_regex, $mod ) ) {
					$filter = preg_replace( $prefix_regex, 'storefront_default_', $mod );
					add_filter( $filter, function( $setting ) use ( $val ) {
						return $val;
					}, 99 );
				}
			}
		}

		/**
		 * Modify the default controls
		 *
		 * @param array $wp_customize the Customizer object.
		 * @return void
		 */
		public function edit_default_controls( $wp_customize ) {
			$wp_customize->get_setting( 'storefront_header_text_color' )->transport = 'refresh';
		}

		/**
		 * Add CSS using settings obtained from the theme options.
		 *
		 * @return void
		 */
		public function add_customizer_css() {
			$background 			= storefront_get_content_background_color();
			$header_background 		= get_theme_mod( 'storefront_header_background_color' );
			$header_link_color 		= get_theme_mod( 'storefront_header_link_color' );
			$header_text_color 		= get_theme_mod( 'storefront_header_text_color' );
			$button_alt_background 	= get_theme_mod( 'storefront_button_alt_background_color' );
			$button_alt_text 		= get_theme_mod( 'storefront_button_alt_text_color' );
			$accent_color 			= get_theme_mod( 'storefront_accent_color' );
			$text_color 			= get_theme_mod( 'storefront_text_color' );
			$header_color 			= get_theme_mod( 'storefront_heading_color' );
			$button_background 		= get_theme_mod( 'storefront_button_background_color' );
			$button_text 			= get_theme_mod( 'storefront_button_text_color' );

			$style = '
				.secondary-navigation ul.menu li a,
				.woocommerce-breadcrumb a,
				ul.products li.product .price,
				.star-rating span:before {
					color: ' . $button_alt_background . ';
				}

				.site-header-cart .widget_shopping_cart, .main-navigation ul.menu ul.sub-menu, .main-navigation ul.nav-menu ul.children {
					background-color: ' . $header_background . ';
				}

				.star-rating:before {
					color: ' . $text_color . ';
				}

				.single-product div.product .summary .price,
				#reviews .commentlist li .review-meta strong,
				.main-navigation ul li.smm-active .widget h3.widget-title {
					color: ' . $header_color . ';
				}

				ul.products li.product h3,
				ul.products li.product .button,
				ul.products li.product .added_to_cart,
				.widget-area .widget a:not(.button) {
					color: ' . $accent_color . ';
				}

				.main-navigation ul li .smm-mega-menu ul.products li.product a.button.add_to_cart_button,
				.main-navigation ul li .smm-mega-menu ul.products li.product a.added_to_cart,
				.main-navigation ul li .smm-mega-menu ul.products li.product a.button.add_to_cart_button:hover,
				.main-navigation ul li .smm-mega-menu ul.products li.product a.added_to_cart:hover,
				.main-navigation ul li .smm-mega-menu ul.products li.product a.button.product_type_grouped,
				.main-navigation ul li .smm-mega-menu ul.products li.product a.button.product_type_grouped:hover {
					color: ' . $accent_color . ' !important;
					background-color: transparent !important;
				}

				.widget-area .widget a:hover,
				.main-navigation ul li a:hover,
				.main-navigation ul li:hover > a,
				.site-title a:hover, a.cart-contents:hover,
				.site-header-cart .widget_shopping_cart a:hover,
				.site-header-cart:hover > li > a,
				ul.menu li.current-menu-item > a {
					color: ' . storefront_adjust_color_brightness( $accent_color, -40 ) . ';
				}

				ul.products li.product .format,
				ul.products li.product .author,
				ul.products li.product .button:before,
				mark {
					color: ' . $text_color . ';
				}

				.onsale {
					background-color: ' . $accent_color . ';
					color: ' . $background . ';
				}

				.secondary-navigation ul.menu li a:hover {
					color: ' . storefront_adjust_color_brightness( $button_alt_background, 40 ) . ';
				}

				.site-header:before {
					background-color: ' . $accent_color . ';
				}

				.button,
				button:not(.pswp__button),
				input[type=button],
				input[type=reset],
				input[type=submit],
				.site-header-cart .cart-contents {
					background: ' . $button_background . '; /* Old browsers */
					background: -moz-linear-gradient(top, ' . storefront_adjust_color_brightness( $button_background, 20 ) . ' 0%, ' . $button_background . ' 100%); /* FF3.6-15 */
					background: -webkit-linear-gradient(top, ' . storefront_adjust_color_brightness( $button_background, 20 ) . ' 0%,' . $button_background . ' 100%); /* Chrome10-25,Safari5.1-6 */
					background: linear-gradient(to bottom, ' . storefront_adjust_color_brightness( $button_background, 20 ) . ' 0%,' . $button_background . ' 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="' . storefront_adjust_color_brightness( $button_background, 20 ) . '", endColorstr="' . $button_background . '",GradientType=0 ); /* IE6-9 */
					color: ' . $button_text . '
				}

				.button:hover,
				button:not(.pswp__button):hover,
				input[type=button]:hover,
				input[type=reset]:hover,
				input[type=submit]:hover,
				.site-header-cart .cart-contents:hover {
					background: ' . $button_background . '; /* Old browsers */
					background: -moz-linear-gradient(top, ' . $button_background . ' 0%, ' . storefront_adjust_color_brightness( $button_background, -35 ) . ' 100%); /* FF3.6-15 */
					background: -webkit-linear-gradient(top, ' . $button_background . ' 0%,' . storefront_adjust_color_brightness( $button_background, -35 ) . ' 100%); /* Chrome10-25,Safari5.1-6 */
					background: linear-gradient(to bottom, ' . $button_background . ' 0%,' . storefront_adjust_color_brightness( $button_background, -35 ) . ' 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="' . $button_background . '", endColorstr="' . storefront_adjust_color_brightness( $button_background, -35 ) . '",GradientType=0 ); /* IE6-9 */
				}

				.button.alt,
				button.alt,
				input[type=button].alt,
				input[type=reset].alt,
				input[type=submit].alt {
					background: ' . $button_alt_background . '; /* Old browsers */
					background: -moz-linear-gradient(top, ' . storefront_adjust_color_brightness( $button_alt_background, 20 ) . ' 0%, ' . $button_alt_background . ' 100%); /* FF3.6-15 */
					background: -webkit-linear-gradient(top, ' . storefront_adjust_color_brightness( $button_alt_background, 20 ) . ' 0%,' . $button_alt_background . ' 100%); /* Chrome10-25,Safari5.1-6 */
					background: linear-gradient(to bottom, ' . storefront_adjust_color_brightness( $button_alt_background, 20 ) . ' 0%,' . $button_alt_background . ' 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="' . storefront_adjust_color_brightness( $button_alt_background, 20 ) . '", endColorstr="' . $button_alt_background . '",GradientType=0 ); /* IE6-9 */
					color: ' . $button_alt_text . '
				}

				.button.alt:hover,
				button.alt:hover,
				input[type=button].alt:hover,
				input[type=reset].alt:hover,
				input[type=submit].alt:hover {
					background: ' . $button_alt_background . '; /* Old browsers */
					background: -moz-linear-gradient(top, ' . $button_alt_background . ' 0%, ' . storefront_adjust_color_brightness( $button_alt_background, -35 ) . ' 100%); /* FF3.6-15 */
					background: -webkit-linear-gradient(top, ' . $button_alt_background . ' 0%,' . storefront_adjust_color_brightness( $button_alt_background, -35 ) . ' 100%); /* Chrome10-25,Safari5.1-6 */
					background: linear-gradient(to bottom, ' . $button_alt_background . ' 0%,' . storefront_adjust_color_brightness( $button_alt_background, -35 ) . ' 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="' . $button_alt_background . '", endColorstr="' . storefront_adjust_color_brightness( $button_alt_background, -35 ) . '",GradientType=0 ); /* IE6-9 */
				}

				.site-header-cart .cart-contents:hover,
				.site-header-cart:hover .cart-contents,
				ul.menu li.current-menu-item > a.cart-contents {
					color: ' . $button_text . '
				}

				ul.menu li.current-menu-item > a,
				table th,
				#payment .payment_methods li label {
					color: ' . $header_text_color . ';
				}

				.main-navigation ul.nav-menu > li:hover > a,
				ul.menu li.current-menu-item > a:not(.cart-contents),
				.main-navigation ul.menu ul,
				.site-header-cart .widget_shopping_cart,
				.smm-mega-menu {
					background: ' . $header_background . ';
				}

				.main-navigation ul li.smm-active li:hover a {
					color: ' . $header_link_color . ' !important;
				}

				.widget-area .widget,
				table,
				.woocommerce-tabs ul.tabs,
				.storefront-sorting,
				#order_review_heading,
				#order_review {
					background: ' . storefront_adjust_color_brightness( $background, 7 ) . ';
				}

				.widget-area .widget.widget_shopping_cart {
					background: ' . storefront_adjust_color_brightness( $background, 10 ) . ';
				}

				.widget-area .widget .widget-title, .widget-area .widget .widgettitle,
				.site-main .storefront-product-section .section-title {
					border-bottom-color: ' . storefront_adjust_color_brightness( $background, -15 ) . ';
				}

				.widget-area .widget .widget-title:after, .widget-area .widget .widgettitle:after {
					background-color: ' . storefront_adjust_color_brightness( $background, -15 ) . ';
					border: 2px solid ' . storefront_adjust_color_brightness( $background, 7 ) . ';
					box-shadow: 0 0 0 2px ' . storefront_adjust_color_brightness( $background, -15 ) . ';
				}

				.site-main .storefront-product-section .section-title:after {
					background-color: ' . storefront_adjust_color_brightness( $background, -15 ) . ';
					border: 2px solid ' . $background . ';
					box-shadow: 0 0 0 2px ' . storefront_adjust_color_brightness( $background, -15 ) . ';
				}';

			wp_add_inline_style( 'storefront-child-style', $style );
		}

		/**
		 * Bookshop background settings
		 *
		 * @param  array $args the background args.
		 * @return array $args the modified args.
		 */
		public function set_background( $args ) {
			$args['default-image']      = get_stylesheet_directory_uri() . '/assets/images/paper.png';
			$args['default-attachment'] = 'fixed';

			return $args;
		}
	}
}

return new Bookshop_Customizer();
