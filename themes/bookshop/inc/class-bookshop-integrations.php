<?php
/**
 * Bookshop_Integrations Class
 * Provides integrations with Storefront extensions by removing/changing incompatible controls/settings. Also adjusts default values
 * if they need to differ from the original setting.
 *
 * @package  Bookshop
 * @author   WooThemes
 * @since    2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Bookshop_Integrations' ) ) {

	/**
	 * Bookshop integrations class
	 */
	class Bookshop_Integrations {

		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			add_action( 'after_switch_theme', array( $this, 'edit_theme_mods' ) );
			add_action( 'customize_register', array( $this, 'edit_controls' ), 99 );

			/**
			 * Storefront WooCommerce Customiser
			 */
			if ( class_exists( 'Storefront_WooCommerce_Customiser' ) ) {
				add_filter( 'swc_setting_defaults', array( $this, 'bookshop_swc_settings' ), 1000 );
			}

			/**
			 * Storefront Checkout Customiser
			 */
			if ( class_exists( 'Storefront_Checkout_Customiser' ) ) {
				add_action( 'wp', array( $this, 'storefront_checkout_customiser' ) );
			}
		}

		/**
		 * Define the desired Storefront WooCommerce Customiser setting defaults that will provide compatibility with this themes design.
		 *
		 * @return array The desired defaults.
		 */
		public function bookshop_swc_defaults() {
			return array(
				'swc_product_columns'                     => '4',
				'swc_homepage_recent_products_columns'    => '5',
				'swc_homepage_recent_products_limit'      => '5',
				'swc_homepage_featured_products_columns'  => '5',
				'swc_homepage_featured_products_limit'    => '5',
				'swc_homepage_top_rated_products_columns' => '5',
				'swc_homepage_top_rated_products_limit'   => '5',
				'swc_homepage_on_sale_products_columns'   => '5',
				'swc_homepage_on_sale_products_limit'     => '5',
				'swc_shop_alignment'                      => 'left',
			);
		}

		/**
		 * This function filters swc_setting_defaults.
		 * swc_setting_defaults is an array of default Storefront WooCommerce Customiser settings, defined in the extension.
		 * That array is checked, and the values are set for each setting, if a value doesn't already exist (IE on fresh install).
		 *
		 * This function filters that array to include the desired defaults for this theme.
		 *
		 * @param array $args The Storefront WooCommerce Customiser default args.
		 * @return array
		 */
		public function bookshop_swc_settings( $args ) {
			$new_args = Bookshop_Integrations::bookshop_swc_defaults();

			return array_merge( $args, $new_args );
		}

		/**
		 * Remove unused/incompatible controls from the Customizer to avoid confusion
		 *
		 * @param array $wp_customize the Customizer object.
		 * @return void
		 */
		public function edit_controls( $wp_customize ) {
			/**
			 * Storefront Designer
			 */
			$wp_customize->remove_control( 'sd_header_layout' );
			$wp_customize->remove_control( 'sd_button_flat' );
			$wp_customize->remove_control( 'sd_button_shadows' );
			$wp_customize->remove_control( 'sd_button_background_style' );
			$wp_customize->remove_control( 'sd_button_rounded' );
			$wp_customize->remove_control( 'sd_button_size' );
			$wp_customize->remove_control( 'sd_header_layout_divider_after' );
			$wp_customize->remove_control( 'sd_button_divider_1' );
			$wp_customize->remove_control( 'sd_button_divider_2' );

			/**
			 * Storefront WooCommerce Customiser
			 */
			$wp_customize->remove_control( 'swc_homepage_category_columns' );
			$wp_customize->remove_control( 'swc_homepage_category_limit' );

			/**
			 * Specify Storefront WooCommerce Customiser defaults based on bookshop_swc_defaults()
			 */
			if ( class_exists( 'Storefront_WooCommerce_Customiser' ) ) {
				foreach ( Bookshop_Integrations::bookshop_swc_defaults() as $mod => $val ) {
					$wp_customize->get_setting( $mod )->default = $val;
				}
			}

			/**
			 * Storefront Powerpack
			 */
			$wp_customize->remove_control( 'sp_homepage_category_columns' );
			$wp_customize->remove_control( 'sp_homepage_recent_products_columns' );
			$wp_customize->remove_control( 'sp_homepage_featured_products_columns' );
			$wp_customize->remove_control( 'sp_homepage_top_rated_products_columns' );
			$wp_customize->remove_control( 'sp_homepage_on_sale_products_limit' );
			$wp_customize->remove_control( 'sp_homepage_best_sellers_products_columns' );
		}

		/**
		 * Remove any pre-existing theme mods for settings that are incompatible with Bookshop.
		 *
		 * @return void
		 */
		public function edit_theme_mods() {
			/**
			 * Storefront Designer
			 */
			remove_theme_mod( 'sd_header_layout' );
			remove_theme_mod( 'sd_button_flat' );
			remove_theme_mod( 'sd_button_shadows' );
			remove_theme_mod( 'sd_button_background_style' );
			remove_theme_mod( 'sd_button_rounded' );
			remove_theme_mod( 'sd_button_size' );
			remove_theme_mod( 'sd_header_layout_divider_after' );
			remove_theme_mod( 'sd_button_divider_1' );
			remove_theme_mod( 'sd_button_divider_2' );

			/**
			 * Storefront Powerpack
			 */
			remove_theme_mod( 'sp_homepage_category_columns' );
			remove_theme_mod( 'sp_homepage_recent_products_columns' );
			remove_theme_mod( 'sp_homepage_featured_products_columns' );
			remove_theme_mod( 'sp_homepage_top_rated_products_columns' );
			remove_theme_mod( 'sp_homepage_on_sale_products_limit' );
			remove_theme_mod( 'sp_homepage_best_sellers_products_columns' );
		}

		/**
		 * Compatibility with the checkout customiser extension
		 */
		public function storefront_checkout_customiser() {
			$distraction_free 	= get_theme_mod( 'scc_distraction_free_checkout' );

			if ( true == $distraction_free && is_checkout() ) {
				remove_action( 'storefront_header', 'storefront_header_cart',					41 );
				remove_action( 'storefront_header', 'storefront_secondary_navigation',			10 );
			}
		}
	}
}

return new Bookshop_Integrations();
