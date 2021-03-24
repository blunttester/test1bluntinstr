<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The ratings settings class
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Settings
 * @package     WCVendors/Admin/Settings
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCVendors_Pro_Settings_Ratings', false ) ) :

	/**
	 * WC_Admin_Settings_General.
	 */
	class WCVendors_Pro_Settings_Ratings extends WCVendors_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'ratings';
			$this->label = sprintf( __( '%s Ratings', 'wcvendors-pro' ), wcv_get_vendor_name() );

			parent::__construct();
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'' => __( 'General', 'wcvendors-pro' ),
			);

			return apply_filters( 'wcvendors_get_sections_' . $this->id, $sections );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {

			if ( '' === $current_section ) {

				$settings = apply_filters(
					'wcvendors_pro_settings_ratings_general',
					array(

						// General Options
						array(
							'title' => sprintf( __( '%s Ratings System', 'wcvendors-pro' ), wcv_get_vendor_name() ),
							'type'  => 'title',
							'desc'  => sprintf( __( 'Options for the %s rating system', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ),
							'id'    => 'general_options',
						),

						array(
							'title'   => __( 'Feedback System', 'wcvendors-pro' ),
							'desc'    => sprintf( __( 'Start all %s at a 5 star rating until they receive their first feedback score', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
							'tip'     => __( 'Reverse the feedback system.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_feedback_system',
							'type'    => 'checkbox',
							'default' => true,
						),

						array(
							'title'   => __( 'Sync with WooCommerce Reviews', 'wcvendors-pro' ),
							'desc'    => sprintf( __( 'Copy %s Ratings to WooCommerce Reviews.', 'wcvendors-pro' ), wcv_get_vendor_name( true, true ) ),
							'tip'     => sprintf( __( 'Copy %s Ratings to WooCommerce Reviews.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_feedback_sync_reviews',
							'type'    => 'checkbox',
							'default' => false,
						),

						array(
							'title'   => __( 'Feedback Display', 'wcvendors-pro' ),
							'desc'    => __( 'Disable feedback on the single product pages.', 'wcvendors-pro' ),
							'tip'     => __( 'Only show feedback at the store level.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_feedback_display',
							'type'    => 'checkbox',
							'default' => false,
						),

						array(
							'title'   => __( 'Feedback Sort', 'wcvendors-pro' ),
							'desc'    => __( 'What order to display the feedback in.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_feedback_sort_order',
							'type'    => 'select',
							'options' => array(
								'desc' => __( 'Newest', 'wcvendors-pro' ),
								'asc'  => __( 'Oldest', 'wcvendors-pro' ),
							),
							'default' => 'desc',
						),

						array(
							'title'   => __( 'Order Status', 'wcvendors-pro' ),
							'desc'    => __( 'The order status required before feedback can be left.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_feedback_order_status',
							'type'    => 'select',
							'options' => array(
								'processing' => __( 'Processing', 'wcvendors-pro' ),
								'completed'  => __( 'Completed', 'wcvendors-pro' ),
							),
							'default' => 'processing',
						),

						array(
							'title'   => __( 'Delete ratings', 'wcvendors-pro' ),
							'desc'    => __( 'Delete ratings when product is deleted.', 'wcvendors-pro' ),
							'tip'     => __( 'Delete ratings when product is deleted.', 'wcvendors-pro' ),
							'id'      => 'wcvendors_delete_ratings_on_deleting_product',
							'type'    => 'checkbox',
							'default' => false,
						),

						array(
							'type' => 'sectionend',
							'id'   => 'general_options',
						),

					)
				);

			}

			return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );

		}

	}

endif;

return new WCVendors_Pro_Settings_Ratings();
