<?php

/**
 * The WC Vendors Pro shipping controller.
 *
 * Defines shipping controller functions that are external to the shipping calculator
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin
 * @author     Jamie Madden <support@wcvendors.com>
 */

class WCVendors_Pro_Shipping_Controller {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string $wcvendors_pro The ID of this plugin.
	 */
	private $wcvendors_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      bool $debug plugin is in debug mode
	 */
	private $debug;

	/**
	 * Is the plugin base directory
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string $base_dir string path for the plugin directory
	 */
	private $base_dir;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.0
	 *
	 * @param      string $wcvendors_pro The name of the plugin.
	 * @param      string $version       The version of this plugin.
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {

		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;
		$this->debug         = $debug;
		$this->base_dir      = plugin_dir_path( dirname( __FILE__ ) );
		$this->base_url      = plugin_dir_url( __FILE__ );

	}

	/**
	 *  Add the shipping tab on the front end.
	 *
	 * @since      1.1.0
	 * @version    1.7.7
	 */
	public function shipping_panel_tab( $tabs ) {

		global $post;

		$shipping_methods  = WC()->shipping->load_shipping_methods();
		$shipping_disabled = wc_string_to_bool( get_option( 'wcvendors_shipping_management_cap', 'no' ) );
		$product           = wc_get_product( $post->ID );

		if ( $product->is_type( 'variable' ) ) {
			$hide_shipping_tab    = true;
			$available_variations = $product->get_available_variations();
			foreach ( $available_variations as $variation ) {
				if ( 0 == $variation['is_virtual'] ) {
					$hide_shipping_tab = false;
				}
			}

			if ( $hide_shipping_tab ) {
				return $tabs;
			}
		}

		if ( ( array_key_exists( 'wcv_pro_vendor_shipping', $shipping_methods ) && $shipping_methods['wcv_pro_vendor_shipping']->enabled == 'yes' ) && $product->needs_shipping() && ! $shipping_disabled && $product->get_type() != 'external' ) {

			$tabs['wcv_shipping_tab'] = apply_filters(
				'wcv_shipping_tab',
				array(
					'title'    => __( 'Shipping', 'wcvendors-pro' ),
					'priority' => 60,
					'callback' => array( $this, 'shipping_panel' ),
				)
			);
		}

		return $tabs;

	} // shipping_panel_tab()

	/**
	 *
	 */

	/**
	 *  Add the shipping panel information for this product to the front end.
	 *
	 * @version    1.7.7
	 * @since      1.1.0
	 */
	public function shipping_panel() {

		global $product;

		$product_id           = $product->get_id();
		$settings             = get_option( 'woocommerce_wcv_pro_vendor_shipping_settings', wcv_get_default_vendor_shipping() );
		$vendor_id            = WCV_Vendors::get_vendor_from_product( $product_id );
		$store_rates          = (array) get_user_meta( $vendor_id, '_wcv_shipping', true );
		$store_country        = ( $store_rates && array_key_exists( 'shipping_from', $store_rates ) && $store_rates['shipping_from'] == 'other' ) ? strtolower( $store_rates['shipping_address']['country'] ) : strtolower( get_user_meta( $vendor_id, '_wcv_store_country', true ) );
		$store_state          = ( $store_rates && array_key_exists( 'shipping_from', $store_rates ) && $store_rates['shipping_from'] == 'other' ) ? strtolower( $store_rates['shipping_address']['state'] ) : strtolower( get_user_meta( $vendor_id, '_wcv_store_state', true ) );
		$product_rates        = (array) get_post_meta( $product_id, '_wcv_shipping_details', true );
		$countries            = WCVendors_Pro_Form_Helper::countries();
		$shipping_flat_rates  = array();
		$shipping_table_rates = array();
		$store_shipping_type  = get_user_meta( $vendor_id, '_wcv_shipping_type', true );
		$shipping_system      = ( ! empty( $store_shipping_type ) ) ? $store_shipping_type : $settings['shipping_system'];
		$store_check          = true;

		array_walk( $store_rates, 'wcv_format_shipping_data' );
		array_walk( $product_rates, 'wcv_format_shipping_data' );

		if ( ! $store_country ) {
			$store_country = WC()->countries->get_base_country();
		}

		// Product rates is empty so set to null.
		if ( is_array( $product_rates ) && ! array_filter( $product_rates ) ) {
			$product_rates = null;
		}

		// Store rates is empty so set to null.
		if ( is_array( $store_rates ) && ( array_key_exists( 'national', $store_rates ) && strlen( trim( $store_rates['national'] ) ) === 0 ) && ( array_key_exists( 'international', $store_rates ) && strlen( trim( $store_rates['international'] ) ) === 0 ) && ( array_key_exists( 'national_free', $store_rates ) && strlen( trim( $store_rates['national_free'] ) ) === 0 ) && ( array_key_exists( 'national_free', $store_rates ) && strlen( trim( $store_rates['international_free'] ) ) === 0 ) ) {
			$store_check = false;
		}

		// Get default country for admin.
		if ( ! WCV_Vendors::is_vendor( $vendor_id ) ) {
			$store_country = WC()->countries->get_base_country();
		}

		if ( $shipping_system == 'flat' ) {

			if ( is_array( $product_rates ) && ! empty( $product_rates['national'] ) || ! empty( $product_rates['international'] ) || ! empty( $product_rates['national_free'] ) || ! empty( $product_rates['international_free'] ) ) {

				$shipping_flat_rates = $product_rates;

			} elseif ( is_array( $store_rates ) && ! empty( $store_rates['national'] ) || ! empty( $store_rates['international'] ) || ! empty( $store_rates['national_free'] ) || ! empty( $store_rates['international_free'] ) ) {

				$shipping_flat_rates = $store_rates;

			} elseif ( $settings['national_cost'] >= 0 && $settings['international_cost'] >= 0 ) {

				// National rates and details
				$shipping_flat_rates['national']                     = $settings['national_cost'];
				$shipping_flat_rates['national_min_charge']          = $settings['national_min_charge'];
				$shipping_flat_rates['national_max_charge']          = $settings['national_max_charge'];
				$shipping_flat_rates['national_free_shipping_order'] = $settings['national_free_shipping_order'];
				$shipping_flat_rates['national_disable']             = $settings['national_disable'];
				$shipping_flat_rates['national_free']                = $settings['national_free'];
				// International rates and details
				$shipping_flat_rates['international']                     = $settings['international_cost'];
				$shipping_flat_rates['international_min_charge']          = $settings['international_min_charge'];
				$shipping_flat_rates['international_max_charge']          = $settings['international_max_charge'];
				$shipping_flat_rates['international_free_shipping_order'] = $settings['international_free_shipping_order'];
				$shipping_flat_rates['international_disable']             = $settings['international_disable'];
				$shipping_flat_rates['international_free']                = $settings['international_free'];
			}
		} else {

			$product_shipping_table = get_post_meta( $product_id, '_wcv_shipping_rates', true );
			$store_shipping_table   = get_user_meta( $vendor_id, '_wcv_shipping_rates', true );
			$global_shipping_table  = $settings['country_rate'];

			// Check to see if the product has any rates set.
			if ( is_array( $product_shipping_table ) && ! empty( $product_shipping_table ) ) {
				$shipping_table_rates = $product_shipping_table;
			} elseif ( is_array( $store_shipping_table ) && ! empty( $store_shipping_table ) ) {
				$shipping_table_rates = $store_shipping_table;
			} else {

				$shipping_table_rates = $global_shipping_table;
			}
		}

		// Order level shipping
		$min_charge          = ! empty( $store_rates['min_charge'] ) ? $store_rates['min_charge'] : '';
		$free_shipping_order = ! empty( $store_rates['free_shipping_order'] ) ? wc_price( $store_rates['free_shipping_order'] ) : '';
		$max_charge          = ! empty( $store_rates['max_charge'] ) ? wc_price( $store_rates['max_charge'] ) : '';
		$min_tax             = WCV_Shipping::calculate_shipping_tax( $min_charge, '', $product->get_shipping_class() );
		$min_charge          = ! empty( $min_charge ) ? wc_price( $min_charge + $min_tax ) : '';

		// Product Level shipping
		// Free Shipping per product
		if ( ! empty( $product_rates['free_shipping_product'] ) ) {
			$free_shipping_product = wc_price( $product_rates['free_shipping_product'] );
		} elseif ( empty( $product_rates['free_shipping_product'] ) && ! empty( $store_rates['free_shipping_product'] ) ) {
			$free_shipping_product = wc_price( $store_rates['free_shipping_product'] );
		} else {
			$free_shipping_product = '';
		}

		// Maximum shipping charged per product
		if ( ! empty( $product_rates['max_charge_product'] ) ) {
			$max_charge_product = wc_price( $product_rates['max_charge_product'] );
		} elseif ( empty( $product_rates['max_charge_product'] ) && ! empty( $store_rates['max_charge_product'] ) ) {
			$max_charge_product = wc_price( $store_rates['max_charge_product'] );
		} else {
			$max_charge_product = '';
		}

		// Product handling fee
		if ( ! empty( $product_rates['handling_fee'] ) ) {
			$product_handling_fee = wcv_percentage_to_price( $product_rates['handling_fee'], $product_id );
		} elseif ( empty( $product_rates['handling_fee'] ) && ! empty( $store_rates['product_handling_fee'] ) ) {
			$product_handling_fee = wcv_percentage_to_price( $store_rates['product_handling_fee'], $product_id );
		} elseif ( empty( $store_rates['product_handling_fee'] ) && ! empty( $settings['product_fee'] ) ) {
			$product_handling_fee = wcv_percentage_to_price( $settings['product_fee'], $product_id );
		} else {
			$product_handling_fee = '';
		}

		// Shipping costs.
		$shipping_costs = array();
		if ( '' !== $countries[ strtoupper( $store_country ) ] ) {
			$shipping_costs['value'][] = array(
				'label'   => __( 'Shipping from', 'wcvendors-pro' ),
				'value'   => $countries[ strtoupper( $store_country ) ],
				'country' => '',
			);
		}
		// National costs.
		if ( ! empty( $shipping_flat_rates ) && 'yes' !== $shipping_flat_rates['national_disable'] ) {
			$free  = ( array_key_exists( 'national_free', $shipping_flat_rates ) ) && wc_string_to_bool( $shipping_flat_rates['national_free'] );
			$price = $free ? __( 'Free', 'wcvendors-pro' ) : wc_price( $shipping_flat_rates['national'] . $product->get_price_suffix() );
			if ( $shipping_flat_rates['national'] > 0 || $free ) {
				$shipping_costs['value'][] = array(
					'label'   => __( 'Within ', 'wcvendors-pro' ),
					'value'   => wc_clean( $price ),
					'country' => $countries[ strtoupper( $store_country ) ],
				);
			}
		}

		// International costs.
		if ( ! empty( $shipping_flat_rates ) && 'yes' !== $shipping_flat_rates['international_disable'] ) {
			$free  = ( array_key_exists( 'international_free', $shipping_flat_rates ) ) && wc_string_to_bool( $shipping_flat_rates['international_free'] );
			$price = $free ? __( 'Free', 'wcvendors-pro' ) : wc_price( $shipping_flat_rates['international'] . $product->get_price_suffix() );
			if ( $shipping_flat_rates['international'] > 0 || $free ) {
				$shipping_costs['value'][] = array(
					'label'   => __( 'Outside ', 'wcvendors-pro' ),
					'value'   => wc_clean( $price ),
					'country' => $countries[ strtoupper( $store_country ) ],
				);
			}
		}

		if ( count( $shipping_costs['value'] ) > 0 ) {
			$shipping_costs['label'] = __( 'Shipping costs', 'wcvendors-pro' );
		}

		// Shipping Details.
		$shipping_details = array();
		if ( $product_handling_fee ) {
			$shipping_details['value'][] = array(
				'label' => __( 'Product handling fee', 'wcvendors-pro' ),
				'value' => wc_price( $product_handling_fee ),
			);
		}

		if ( isset( $shipping_details['value'] ) && count( $shipping_details['value'] ) > 0 ) {
			$shipping_details['label'] = __( 'Shipping Details', 'wcvendors-pro' );
		}

		// Extract national & international rates from the flat rates to use in the extra details section.
		$national_rate_details      = array_filter(
			$shipping_flat_rates,
			function( $key ) {
				return strpos( $key, 'national_' ) === 0;
			},
			ARRAY_FILTER_USE_KEY
		);
		$international_rate_details = array_filter(
			$shipping_flat_rates,
			function( $key ) {
				return strpos( $key, 'international_' ) === 0;
			},
			ARRAY_FILTER_USE_KEY
		);

		// Clean arrays for the national international details.
		unset( $national_rate_details['national_disable'] );
		unset( $national_rate_details['national_free'] );
		unset( $international_rate_details['international_disable'] );
		unset( $international_rate_details['international_free'] );

		wc_get_template(
			'shipping-panel.php',
			array(
				'shipping_costs'             => $shipping_costs,
				'national_details'           => array(
					'label' => __( 'National Shipping Details', 'wcvendors-pro' ),
					'value' => array(
						'national_minimum_shipping_fee'  => __( 'Minimum shipping charge', 'wc-vendors-pro' ),
						'national_maximum_shipping_fee'  => __( 'Maximum shipping charge', 'wc-vendors-pro' ),
						'national_free_shipping_product' => __( 'Free shipping charge if spend is over', 'wc-vendors-pro' ),
						'national_min_charge'            => __( 'Minimum shipping charge per order', 'wc-vendors-pro' ),
						'national_max_charge'            => __( 'Maximum shipping charge per order', 'wc-vendors-pro' ),
						'national_free_shipping_order'   => __( 'Free shipping if the order spend is over', 'wc-vendors-pro' ),
					),
				),
				'national_rate_details'      => array_filter( $national_rate_details ),
				'international_details'      => array(
					'label' => __( 'International Shipping Details', 'wcvendors-pro' ),
					'value' => array(
						'international_minimum_shipping_fee' => __( 'Minimum shipping charge per order', 'wc-vendors-pro' ),
						'international_maximum_shipping_fee' => __( 'Maximum shipping charge per order', 'wc-vendors-pro' ),
						'international_free_shipping_product' => __( 'Free shipping charge per order spend over', 'wc-vendors-pro' ),
						'international_min_charge' => __( 'Minimum shipping charge per order', 'wc-vendors-pro' ),
						'international_max_charge' => __( 'Maximum shipping charge per order', 'wc-vendors-pro' ),
						'international_free_shipping_order' => __( 'Free shipping if the order spend is over', 'wc-vendors-pro' ),
					),
				),
				'international_rate_details' => array_filter( $international_rate_details ),
				'shipping_flat_rates'        => $shipping_flat_rates,
				'shipping_table_rates'       => array(
					'label' => __( 'Shipping costs', 'wcvendors-pro' ),
					'value' => $shipping_table_rates,
				),
				$shipping_table_rates,
				'shipping_details'           => $shipping_details,
				'countries'                  => $countries,
				'product'                    => $product,
			),
			'wc-vendors/front/shipping/',
			$this->base_dir . 'templates/front/shipping/'
		);

	} // shipping_panel()

	/**
	 * DEPRECIATED:: Add shipping override to user edit screen
	 *
	 * @since    1.2.0
	 */
	public function store_shipping_override( $user ) {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! WCV_Vendors::is_vendor( $user->ID ) ) {
			return;
		}

		include apply_filters( 'wcv_partial_path_pro_store_meta_shipping_panel', 'partials/vendor/wcvendors-pro-store-meta-shipping-panel.php' );

	} // store_shipping_override()

	/**
	 * Get all shipping fields for user panel
	 *
	 * @since    1.3.0
	 */
	public function get_user_meta_fields( $user ) {

		$vendor_meta     = array_map(
			function ( $a ) {
					return $a[0];
			},
			get_user_meta( $user->ID )
		);
		$vendor_shipping = array_key_exists( '_wcv_shipping', $vendor_meta ) ? unserialize( $vendor_meta['_wcv_shipping'] ) : self::get_shipping_defaults();

		$vendor_shipping = wp_parse_args( $vendor_shipping, self::get_shipping_defaults() );

		return $fields = apply_filters(
			'wcv_custom_user_shiping_fields',
			array(
				'shipping_address'   => array(
					'title'  => __( 'Store Shipping Address', 'wcvendors-pro' ),
					'fields' => array(
						'_wcv_shipping_address1' => array(
							'label'       => __( 'Address 1', 'wcvendors-pro' ),
							'description' => '',
							'value'       => $vendor_shipping['shipping_address']['address1'],
							'field_type'  => 'shipping_address',
						),
						'_wcv_shipping_address2' => array(
							'label'       => __( 'Address 2', 'wcvendors-pro' ),
							'description' => '',
							'value'       => $vendor_shipping['shipping_address']['address2'],
							'field_type'  => 'shipping_address',
						),
						'_wcv_shipping_city'     => array(
							'label'       => __( 'City', 'wcvendors-pro' ),
							'description' => '',
							'value'       => $vendor_shipping['shipping_address']['city'],
							'field_type'  => 'shipping_address',
						),
						'_wcv_shipping_postcode' => array(
							'label'       => __( 'Postcode', 'wcvendors-pro' ),
							'description' => '',
							'value'       => $vendor_shipping['shipping_address']['postcode'],
							'field_type'  => 'shipping_address',
						),
						'_wcv_shipping_country'  => array(
							'label'       => __( 'Country', 'wcvendors-pro' ),
							'description' => '',
							'field_type'  => 'shipping_address',
							'class'       => 'js_field-country',
							'type'        => 'select',
							'value'       => $vendor_shipping['shipping_address']['country'],
							'options'     => array( '' => __( 'Select a country&hellip;', 'wcvendors-pro' ) ) + WC()->countries->get_allowed_countries(),
						),
						'_wcv_shipping_state'    => array(
							'label'       => __( 'State/County', 'wcvendors-pro' ),
							'description' => __( 'State/County or state code', 'wcvendors-pro' ),
							'class'       => 'js_field-state',
							'value'       => $vendor_shipping['shipping_address']['state'],
							'field_type'  => 'shipping_address',
						),
					),
				),
				'shipping_general'   => array(
					'title'  => __( 'Store Shipping General', 'wcvendors-pro' ),
					'fields' => array(
						'_wcv_vendor_product_handling_fee' => array(
							'label'       => __( 'Product handling fee', 'wcvendors-pro' ),
							'description' => __( 'The product handling fee, this can be overridden on a per product basis. Amount (5.00) or Percentage (5%).', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['product_handling_fee'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_min_charge'         => array(
							'label'       => __( 'Minimum shipping charged per order', 'wcvendors-pro' ),
							'description' => __( 'Notice: These settings will soon be removed. Please use respective settings for national/international.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['min_charge'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_max_charge'         => array(
							'label'       => __( 'Maxium shipping charged per order', 'wcvendors-pro' ),
							'description' => __( 'Notice: These settings will soon be removed. Please use respective settings for national/international.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['max_charge'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_free_shipping_order' => array(
							'label'       => __( 'Free shipping order', 'wcvendors-pro' ),
							'description' => __( 'Notice: These settings will soon be removed. Please use respective settings for national/international.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['free_shipping_order'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_max_charge_product' => array(
							'label'       => __( 'Maximum product charge', 'wcvendors-pro' ),
							'description' => __( 'The maximum shipping charged per product no matter the quantity.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['max_charge_product'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_free_shipping_product' => array(
							'label'       => __( 'Free shipping product', 'wcvendors-pro' ),
							'description' => __( 'Free shipping for order spends over this amount. This will override the max shipping charge above.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['free_shipping_product'],
							'field_type'  => 'shipping',
						),
						'_wcv_vendor_shipping_from'        => array(
							'label'       => __( 'Shipping from', 'wcvendors-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'store_address' => __( 'Store Address', 'wcvendors-pro' ),
								'other'         => __( 'Other', 'wcvendors-pro' ),
							),
							'value'       => $vendor_shipping['shipping_from'],
						),
						'_wcv_shipping_type'               => array(
							'label'       => __( 'Shipping type', 'wcvendors-pro' ),
							'description' => sprintf( __( 'You can override the global setting for shipping type for this %s.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
							'field_type'  => 'shipping',
							'type'        => 'select',
							'class'       => 'wcv-shipping-type',
							'options'     => array_merge( array( '' => '' ), self::shipping_types() ),
						),
					),
				),
				'shipping_flat_rate' => array(
					'title'       => __( 'Store Flat Rate Shipping', 'wcvendors-pro' ),
					'field_class' => 'wcv-shipping-rates wcv-shipping-flat',
					'fields'      => array(
						'_wcv_vendor_national'           => array(
							'label'       => __( 'National shipping fee', 'wcvendors-pro' ),
							'description' => __( 'The default shipping fee within your country, this can be overridden on a per product basis.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['national'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_national_min_charge' => array(
							'label'       => __( 'Minimum shipping charged per order for national shipping', 'wcvendors-pro' ),
							'description' => __( 'The minimum national shipping fee charged for an order.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['national_min_charge'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_national_max_charge' => array(
							'label'       => __( 'Maximum shipping charged per order for national shipping', 'wcvendors-pro' ),
							'description' => __( 'The maximum national shipping fee charged for an order.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['national_max_charge'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_national_free_shipping_order' => array(
							'label'       => __( 'Free shipping order for national shipping', 'wcvendors-pro' ),
							'description' => __( 'The minimum shipping fee charged for an order. This will override the max shipping charge above.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['national_free_shipping_order'],
							'field_type'  => 'shipping',
						),
						'_wcv_vendor_national_free'      => array(
							'label'       => __( 'Free national shipping', 'wcvendors-pro' ),
							'description' => __( 'Free national shipping', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['national_free'],
							'type'        => 'checkbox',
							'field_type'  => 'shipping',
						),
						'_wcv_vendor_national_qty_override' => array(
							'label'       => __( 'Charge once', 'wcvendors-pro' ),
							'description' => __( 'Charge once per product for national shipping, even if more than one is purchased.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['national_qty_override'],
							'type'        => 'checkbox',
							'field_type'  => 'shipping',
						),
						'_wcv_vendor_national_disable'   => array(
							'label'       => __( 'Disable national shipping', 'wcvendors-pro' ),
							'description' => __( 'Disable national shipping', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['national_disable'],
							'type'        => 'checkbox',
							'field_type'  => 'shipping',
						),
						'_wcv_vendor_international'      => array(
							'label'       => __( 'International shipping fee', 'wcvendors-pro' ),
							'description' => __( 'The default shipping fee within your country, this can be overridden on a per product basis.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['international'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_international_min_charge' => array(
							'label'       => __( 'Minimum shipping charged per order for international shipping', 'wcvendors-pro' ),
							'description' => __( 'The minimum international shipping fee charged for an order.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['international_min_charge'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_international_max_charge' => array(
							'label'       => __( 'Maximum shipping charged per order', 'wcvendors-pro' ),
							'description' => __( 'The maximum international shipping fee charged for an order.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['international_max_charge'],
							'field_type'  => 'shipping',
						),
						'_wcv_shipping_international_free_shipping_order' => array(
							'label'       => __( 'Free shipping order for international shipping', 'wcvendors-pro' ),
							'description' => __( 'The minimum shipping fee charged for an order. This will override the max shipping charge above.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['international_free_shipping_order'],
							'field_type'  => 'shipping',
						),
						'_wcv_vendor_international_free' => array(
							'label'       => __( 'Free international shipping', 'wcvendors-pro' ),
							'description' => __( 'Free international shipping', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['international_free'],
							'type'        => 'checkbox',
							'field_type'  => 'shipping',
						),
						'_wcv_vendor_international_qty_override' => array(
							'label'       => __( 'Charge once', 'wcvendors-pro' ),
							'description' => __( 'Charge once per product for international shipping, even if more than one is purchased.', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['international_qty_override'],
							'type'        => 'checkbox',
							'field_type'  => 'shipping',
						),
						'_wcv_vendor_international_disable' => array(
							'label'       => __( 'Disable international shipping', 'wcvendors-pro' ),
							'description' => __( 'Disable international shipping', 'wcvendors-pro' ),
							'value'       => $vendor_shipping['international_disable'],
							'type'        => 'checkbox',
							'field_type'  => 'shipping',
						),
					),
				),
			)
		);

	}

	/**
	 * Show the Pro vendor store shipping fields
	 *
	 * @since    1.3.3
	 *
	 * @param WP_User $user
	 */
	public function add_pro_vendor_meta_fields( $user ) {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! WCV_Vendors::is_vendor( $user->ID ) && ! WCV_Vendors::is_pending( $user->ID ) ) {
			return;
		}

		$fields = $this->get_user_meta_fields( $user );

		include apply_filters( 'wcv_partial_path_pro_user_meta', 'partials/vendor/wcvendors-pro-user-meta.php' );

	}

	/**
	 * Show the Pro vendor store shipping country rate fields
	 *
	 * @since    1.3.3
	 *
	 * @param WP_User $user
	 */
	public function add_pro_vendor_country_rate_fields( $user ) {

		$screen         = get_current_screen();
		$helper_text    = apply_filters( 'wcv_shipping_rate_table_msg', __( 'Countries must use the international standard for two letter country codes. eg. AU for Australia.', 'wcvendors-pro' ) );
		$shipping_rates = get_user_meta( $user->ID, '_wcv_shipping_rates', true );

		include apply_filters( 'wcv_partial_path_pro_user_country_rate', 'partials/vendor/wcvendors-pro-user-meta-shipping-country-rate.php' );

	}

	/**
	 *  Save vendor shipping overrides for the user edit screen
	 *
	 * @param    int $post_id post_id being saved
	 *
	 * @since    1.1.0
	 */
	public function save_vendor_shipping_user( $vendor_id ) {

		$this->allow_markup = 'yes' === get_option( 'wcvendors_allow_form_markup', 'no' ) ? true : false;

		// Shipping type
		if ( isset( $_POST['_wcv_shipping_type'] ) && '' !== $_POST['_wcv_shipping_type'] ) {
			update_user_meta( $vendor_id, '_wcv_shipping_type', $_POST['_wcv_shipping_type'] );
		} else {
			delete_user_meta( $vendor_id, '_wcv_shipping_type' );
		}

		// Shipping Address
		$shipping_address1 = ( isset( $_POST['_wcv_shipping_address1'] ) ) ? trim( $_POST['_wcv_shipping_address1'] ) : '';
		$shipping_address2 = ( isset( $_POST['_wcv_shipping_address2'] ) ) ? trim( $_POST['_wcv_shipping_address2'] ) : '';
		$shipping_city     = ( isset( $_POST['_wcv_shipping_city'] ) ) ? trim( $_POST['_wcv_shipping_city'] ) : '';
		$shipping_state    = ( isset( $_POST['_wcv_shipping_state'] ) ) ? trim( $_POST['_wcv_shipping_state'] ) : '';
		$shipping_country  = ( isset( $_POST['_wcv_shipping_country'] ) ) ? trim( $_POST['_wcv_shipping_country'] ) : '';
		$shipping_postcode = ( isset( $_POST['_wcv_shipping_postcode'] ) ) ? trim( $_POST['_wcv_shipping_postcode'] ) : '';

		// Flat Rate
		$shipping_fee_national                          = ( isset( $_POST['_wcv_vendor_national'] ) ) ? trim( $_POST['_wcv_vendor_national'] ) : '';
		$shipping_fee_national_min_charge               = ( isset( $_POST['_wcv_shipping_national_min_charge'] ) ) ? trim( $_POST['_wcv_shipping_national_min_charge'] ) : '';
		$shipping_fee_national_max_charge               = ( isset( $_POST['_wcv_shipping_national_max_charge'] ) ) ? trim( $_POST['_wcv_shipping_national_max_charge'] ) : '';
		$shipping_fee_national_free_shipping_order      = ( isset( $_POST['_wcv_shipping_national_free_shipping_order'] ) ) ? trim( $_POST['_wcv_shipping_national_free_shipping_order'] ) : '';
		$shipping_fee_international                     = ( isset( $_POST['_wcv_vendor_international'] ) ) ? trim( $_POST['_wcv_vendor_international'] ) : '';
		$shipping_fee_international_min_charge          = ( isset( $_POST['_wcv_shipping_international_min_charge'] ) ) ? trim( $_POST['_wcv_shipping_international_min_charge'] ) : '';
		$shipping_fee_international_max_charge          = ( isset( $_POST['_wcv_shipping_international_max_charge'] ) ) ? trim( $_POST['_wcv_shipping_international_max_charge'] ) : '';
		$shipping_fee_international_free_shipping_order = ( isset( $_POST['_wcv_shipping_international_free_shipping_order'] ) ) ? trim( $_POST['_wcv_shipping_international_free_shipping_order'] ) : '';
		$shipping_fee_national_qty                      = ( isset( $_POST['_wcv_vendor_national_qty_override'] ) ) ? 'yes' : '';
		$shipping_fee_international_qty                 = ( isset( $_POST['_wcv_vendor_international_qty_override'] ) ) ? 'yes' : '';
		$shipping_fee_national_free                     = ( isset( $_POST['_wcv_vendor_national_free'] ) ) ? 'yes' : '';
		$shipping_fee_international_free                = ( isset( $_POST['_wcv_vendor_international_free'] ) ) ? 'yes' : '';
		$shipping_fee_national_disable                  = ( isset( $_POST['_wcv_vendor_national_disable'] ) ) ? 'yes' : '';
		$shipping_fee_international_disable             = ( isset( $_POST['_wcv_vendor_international_disable'] ) ) ? 'yes' : '';

		// Shipping General
		$product_handling_fee  = ( isset( $_POST['_wcv_vendor_product_handling_fee'] ) ) ? trim( $_POST['_wcv_vendor_product_handling_fee'] ) : '';
		$max_charge            = ( isset( $_POST['_wcv_shipping_max_charge'] ) ) ? wc_format_decimal( $_POST['_wcv_shipping_max_charge'] ) : '';
		$min_charge            = ( isset( $_POST['_wcv_shipping_min_charge'] ) ) ? wc_format_decimal( $_POST['_wcv_shipping_min_charge'] ) : '';
		$free_shipping_order   = ( isset( $_POST['_wcv_shipping_free_shipping_order'] ) ) ? wc_format_decimal( $_POST['_wcv_shipping_free_shipping_order'] ) : '';
		$free_shipping_product = ( isset( $_POST['_wcv_shipping_free_shipping_product'] ) ) ? wc_format_decimal( $_POST['_wcv_shipping_free_shipping_product'] ) : '';
		$max_charge_product    = ( isset( $_POST['_wcv_shipping_max_charge_product'] ) ) ? wc_format_decimal( $_POST['_wcv_shipping_max_charge_product'] ) : '';
		$shipping_policy       = ( isset( $_POST['_wcv_vendor_shipping_policy'] ) ) ? trim( $_POST['_wcv_vendor_shipping_policy'] ) : '';
		$return_policy         = ( isset( $_POST['_wcv_vendor_return_policy'] ) ) ? trim( $_POST['_wcv_vendor_return_policy'] ) : '';
		$shipping_from         = ( isset( $_POST['_wcv_vendor_shipping_from'] ) ) ? trim( $_POST['_wcv_vendor_shipping_from'] ) : '';

		$wcvendors_shipping = array(
			'national'                          => $shipping_fee_national,
			'national_max_charge'               => $shipping_fee_national_max_charge,
			'national_min_charge'               => $shipping_fee_national_min_charge,
			'national_free_shipping_order'      => $shipping_fee_national_free_shipping_order,
			'national_qty_override'             => $shipping_fee_national_qty,
			'national_free'                     => $shipping_fee_national_free,
			'national_disable'                  => $shipping_fee_national_disable,
			'international'                     => $shipping_fee_international,
			'international_max_charge'          => $shipping_fee_international_max_charge,
			'international_min_charge'          => $shipping_fee_international_min_charge,
			'international_free_shipping_order' => $shipping_fee_international_free_shipping_order,
			'international_qty_override'        => $shipping_fee_international_qty,
			'international_free'                => $shipping_fee_international_free,
			'international_disable'             => $shipping_fee_international_disable,
			'product_handling_fee'              => $product_handling_fee,
			'max_charge'                        => $max_charge,
			'min_charge'                        => $min_charge,
			'free_shipping_order'               => $free_shipping_order,
			'free_shipping_product'             => $free_shipping_product,
			'max_charge_product'                => $max_charge_product,
			'shipping_policy'                   => $shipping_policy,
			'return_policy'                     => $return_policy,
			'shipping_from'                     => $shipping_from,
			'shipping_address'                  => '',
		);

		$shipping_address = array(
			'address1' => $shipping_address1,
			'address2' => $shipping_address2,
			'city'     => $shipping_city,
			'state'    => $shipping_state,
			'country'  => $shipping_country,
			'postcode' => $shipping_postcode,
		);

		$wcvendors_shipping['shipping_address'] = $shipping_address;

		update_user_meta( $vendor_id, '_wcv_shipping', $wcvendors_shipping );

		// shipping rates
		$shipping_rates = array();

		if ( isset( $_POST['_wcv_shipping_fees'] ) ) {
			$shipping_countries     = isset( $_POST['_wcv_shipping_countries'] ) ? $_POST['_wcv_shipping_countries'] : array();
			$shipping_states        = isset( $_POST['_wcv_shipping_states'] ) ? $_POST['_wcv_shipping_states'] : array();
			$shipping_postcodes     = isset( $_POST['_wcv_shipping_postcodes'] ) ? $_POST['_wcv_shipping_postcodes'] : array();
			$shipping_qty_overrides = isset( $_POST['_wcv_shipping_overrides'] ) ? $_POST['_wcv_shipping_overrides'] : array();
			$shipping_fees          = isset( $_POST['_wcv_shipping_fees'] ) ? $_POST['_wcv_shipping_fees'] : array();
			$shipping_fee_count     = sizeof( $shipping_fees );

			for ( $i = 0; $i < $shipping_fee_count; $i ++ ) {

				if ( $shipping_fees[ $i ] != '' ) {
					$country              = wc_clean( $shipping_countries[ $i ] );
					$state                = wc_clean( $shipping_states[ $i ] );
					$postcode             = wc_clean( $shipping_postcodes[ $i ] );
					$qty_override         = ( isset( $shipping_qty_overrides[ $i ] ) && '' != $shipping_qty_overrides[ $i ] ) ? 'yes' : '';
					$fee                  = wc_format_localized_price( $shipping_fees[ $i ] );
					$shipping_rates[ $i ] = array(
						'country'      => $country,
						'state'        => $state,
						'postcode'     => $postcode,
						'fee'          => $fee,
						'qty_override' => $qty_override,
					);
				}
			}
			update_user_meta( $vendor_id, '_wcv_shipping_rates', $shipping_rates );
		} else {
			delete_user_meta( $vendor_id, '_wcv_shipping_rates' );
		}

		// loop through new fields to save into correct array
	} //save_vendor_shipping_user()

	/**
	 *  Shipping types
	 *
	 * @since    1.1.0
	 */
	public static function shipping_types() {

		return apply_filters(
			'wcv_shipping_types',
			array(
				'flat'    => __( 'Flat Rate', 'wcvendors-pro' ),
				'country' => __( 'Country Table Rate', 'wcvendors-pro' ),
			)
		);

	} // shipping_types()

	/**
	 *  Shipping tab on product edit screen
	 *
	 * @since    1.3.3
	 * @version  1.7.4
	 */
	public function product_vendor_shipping_panel() {

		global $post;

		$user                = get_user_by( 'id', $post->post_author );
		$screen              = get_current_screen();
		$shipping_settings   = get_option( 'woocommerce_wcv_pro_vendor_shipping_settings', wcv_get_default_vendor_shipping() );
		$store_shipping_type = get_user_meta( $post->post_author, '_wcv_shipping_type', true );
		$shipping_type       = ( $store_shipping_type != '' ) ? $store_shipping_type : $shipping_settings['shipping_system'];

		$shipping_rates   = get_post_meta( $post->ID, '_wcv_shipping_rates', true );
		$shipping_details = get_post_meta( $post->ID, '_wcv_shipping_details', true );

		if ( ! empty( $shipping_details ) ) {
			array_walk( $shipping_details, 'wcv_format_shipping_data' );
		}

		$handling_fee          = ( $shipping_type == 'flat' ) ? ( ! empty( $shipping_details ) ? $shipping_details['handling_fee'] : '' ) : ( ! empty( $shipping_rates ) ? $shipping_details['handling_fee'] : '' );
		$max_charge_product    = ! empty( $shipping_details ) && array_key_exists( 'max_charge_product', $shipping_details ) ? $shipping_details['max_charge_product'] : '';
		$free_shipping_product = ! empty( $shipping_details ) && array_key_exists( 'free_shipping_product', $shipping_details ) ? $shipping_details['free_shipping_product'] : '';

		include apply_filters( 'wcv_partial_path_pro_product_vendor_shipping_panel', 'partials/product/wcvendors-pro-vendor-shipping-panel.php' );

	} //vendor_shipping_tab

	/**
	 * Save the shipping data for the product
	 *
	 * @param    int $post_id post_id being saved
	 *
	 * @since    1.3.3
	 */
	public function save_vendor_shipping_product( $post_id ) {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$shipping_details = array();

		if ( isset( $_POST['_shipping_fee_national'] ) && '' != $_POST['_shipping_fee_national'] ) {
			$shipping_details['national'] = wc_format_decimal( $_POST['_shipping_fee_national'] );
		} else {
			$shipping_details['national'] = '';
		}

		if ( isset( $_POST['_shipping_fee_international'] ) && '' != $_POST['_shipping_fee_international'] ) {
			$shipping_details['international'] = wc_format_decimal( $_POST['_shipping_fee_international'] );
		} else {
			$shipping_details['international'] = '';
		}

		if ( isset( $_POST['_handling_fee'] ) && '' != $_POST['_handling_fee'] ) {
			$shipping_details['handling_fee'] = sanitize_text_field( $_POST['_handling_fee'] );
		} else {
			$shipping_details['handling_fee'] = '';
		}

		if ( isset( $_POST['_max_charge_product'] ) && '' != $_POST['_max_charge_product'] ) {
			$shipping_details['max_charge_product'] = wc_format_decimal( $_POST['_max_charge_product'] );
		} else {
			$shipping_details['max_charge_product'] = '';
		}

		if ( isset( $_POST['_free_shipping_product'] ) && '' != $_POST['_free_shipping_product'] ) {
			$shipping_details['free_shipping_product'] = wc_format_decimal( $_POST['_free_shipping_product'] );
		} else {
			$shipping_details['free_shipping_product'] = '';
		}

		if ( isset( $_POST['_shipping_fee_national_qty'] ) && '' != $_POST['_shipping_fee_national_qty'] ) {
			$shipping_details['national_qty_override'] = 'yes';
		} else {
			$shipping_details['national_qty_override'] = '';
		}

		if ( isset( $_POST['_shipping_fee_national_disable'] ) && '' != $_POST['_shipping_fee_national_disable'] ) {
			$shipping_details['national_disable'] = 'yes';
		} else {
			$shipping_details['national_disable'] = '';
		}

		if ( isset( $_POST['_shipping_fee_national_free'] ) && '' != $_POST['_shipping_fee_national_free'] ) {
			$shipping_details['national_free'] = 'yes';
		} else {
			$shipping_details['national_free'] = '';
		}

		if ( isset( $_POST['_shipping_fee_international_qty'] ) && '' != $_POST['_shipping_fee_international_qty'] ) {
			$shipping_details['international_qty_override'] = 'yes';
		} else {
			$shipping_details['international_qty_override'] = '';
		}

		if ( isset( $_POST['_shipping_fee_international_disable'] ) && '' != $_POST['_shipping_fee_international_disable'] ) {
			$shipping_details['international_disable'] = 'yes';
		} else {
			$shipping_details['international_disable'] = '';
		}

		if ( isset( $_POST['_shipping_fee_international_free'] ) && '' != $_POST['_shipping_fee_international_free'] ) {
			$shipping_details['international_free'] = 'yes';
		} else {
			$shipping_details['international_free'] = '';
		}

		if ( ! empty( $shipping_details ) ) {
			update_post_meta( $post_id, '_wcv_shipping_details', $shipping_details );
		} else {
			delete_post_meta( $post_id, '_wcv_shipping_details' );
		}

		// shipping rates
		$shipping_rates = array();

		if ( isset( $_POST['_wcv_shipping_fees'] ) ) {

			$shipping_countries     = isset( $_POST['_wcv_shipping_countries'] ) ? $_POST['_wcv_shipping_countries'] : array();
			$shipping_states        = isset( $_POST['_wcv_shipping_states'] ) ? $_POST['_wcv_shipping_states'] : array();
			$shipping_postcodes     = isset( $_POST['_wcv_shipping_postcodes'] ) ? $_POST['_wcv_shipping_postcodes'] : array();
			$shipping_qty_overrides = isset( $_POST['_wcv_shipping_overrides'] ) ? $_POST['_wcv_shipping_overrides'] : array();
			$shipping_fees          = isset( $_POST['_wcv_shipping_fees'] ) ? $_POST['_wcv_shipping_fees'] : array();
			$shipping_fee_count     = sizeof( $shipping_fees );

			for ( $i = 0; $i < $shipping_fee_count; $i ++ ) {
				if ( $shipping_fees[ $i ] != '' ) {
					$country              = wc_clean( $shipping_countries[ $i ] );
					$state                = wc_clean( $shipping_states[ $i ] );
					$postcode             = wc_clean( $shipping_postcodes[ $i ] );
					$qty_override         = ( isset( $shipping_qty_overrides[ $i ] ) && '' != $shipping_qty_overrides[ $i ] ) ? 'yes' : '';
					$fee                  = wc_format_decimal( $shipping_fees[ $i ] );
					$shipping_rates[ $i ] = array(
						'country'      => $country,
						'state'        => $state,
						'postcode'     => $postcode,
						'fee'          => $fee,
						'qty_override' => $qty_override,
					);
				}
			}
			update_post_meta( $post_id, '_wcv_shipping_rates', $shipping_rates );
		} else {

			delete_post_meta( $post_id, '_wcv_shipping_rates' );
		}

		// Invalidate the shipping cache
		WC_Cache_Helper::get_transient_version( 'shipping', true );

	} // save_vendor_shipping()

	/**
	 * Return an empty array of the shipping defaults
	 *
	 * @since  1.3.6
	 * @access public
	 */
	public static function get_shipping_defaults() {

		return apply_filters(
			'wcv_shipping_default_options',
			array(
				'product_handling_fee'              => '',
				'max_charge'                        => '',
				'min_charge'                        => '',
				'free_shipping_order'               => '',
				'free_shipping_product'             => '',
				'max_charge_product'                => '',
				'shipping_policy'                   => '',
				'return_policy'                     => '',
				'shipping_from'                     => '',
				'national'                          => '',
				'national_min_charge'               => '',
				'national_max_charge'               => '',
				'national_free_shipping_order'      => '',
				'national_free'                     => '',
				'national_qty_override'             => '',
				'national_disable'                  => '',
				'international'                     => '',
				'international_min_charge'          => '',
				'international_max_charge'          => '',
				'international_free_shipping_order' => '',
				'international_free'                => '',
				'international_qty_override'        => '',
				'international_disable'             => '',
				'shipping_address'                  => array(
					'address1' => '',
					'address2' => '',
					'city'     => '',
					'postcode' => '',
					'country'  => '',
					'state'    => '',
				),
			)
		);

	} // get_shipping_defaults()

	/**
	 *  Split vendor shipping on the cart
	 *
	 * @since    1.4.0
	 *
	 * @param     array $packages the shipping packages from the cart
	 *
	 * @return   array    $packages the modified shipping packages from the cart
	 */
	public function vendor_split_woocommerce_cart_shipping_packages( $packages ) {

		$new_packages = array();

		foreach ( $packages as $package ) {
			$vendor_items = array();
			foreach ( $package['contents'] as $item_key => $item ) {

				$post = get_post( $item['product_id'] );

				if ( $item['data']->needs_shipping() ) {
					$vendor_items[ $post->post_author ][ $item_key ] = $item;
				}
			}

			foreach ( $vendor_items as $vendor_id => $items ) {

				$contents_cost = array_sum( wp_list_pluck( $items, 'line_total' ) );

				$new_packages[] = array(
					'contents'        => $items,
					'contents_cost'   => $contents_cost,
					'applied_coupons' => WC()->cart->applied_coupons,
					'vendor_id'       => $vendor_id,
					'user'            => array(
						'ID' => get_current_user_id(),
					),
					'destination'     => array(
						'country'   => WC()->customer->get_shipping_country(),
						'state'     => WC()->customer->get_shipping_state(),
						'postcode'  => WC()->customer->get_shipping_postcode(),
						'city'      => WC()->customer->get_shipping_city(),
						'address'   => WC()->customer->get_shipping_address(),
						'address_2' => WC()->customer->get_shipping_address_2(),
					),
					'cart_subtotal'   => WC()->cart->get_displayed_subtotal(),
				);

			}
		}

		return apply_filters( 'wcv_vendor_split_shipping_packages', $new_packages );

	} // vendor_split_woocommerce_cart_shipping_packages()

	/**
	 *  Rename the shipping packages based on the vendor sold by
	 *
	 * @since    1.4.0
	 *
	 * @param     string $title    the shipping package title
	 * @param     int    $count    the shipping package position
	 * @param     array  $packages the shipping packages from the cart
	 *
	 * @return     string $title the modified shipping package title
	 */
	public function rename_vendor_shipping_package( $title, $count, $package ) {

		$vendor_sold_by = WCV_Vendors::get_vendor_sold_by( $package['vendor_id'] );
		$title          = sprintf( __( '%s Shipping', 'wcvendors-pro' ), $vendor_sold_by );

		return apply_filters( 'wcv_vendor_shipping_package_title', $title, $count, $package, $vendor_sold_by );

	} // rename_vendor_shipping_package()

	/**
	 *  Rename the shipping packages method id if the shipping is free
	 *
	 * @since    1.4.0
	 *
	 * @param     string $label  the shipping package title
	 * @param     array  $method the shipping method
	 *
	 * @return     string $label the modified shipping method title
	 */
	public function rename_vendor_shipping_method_label( $label, $method ) {

		if ( 'wcv_pro_vendor_shipping' === $method->method_id && $method->cost <= 0 ) {
			$label = __( 'Free shipping', 'wcvendors-pro' );
		}

		return $label;

	}

	/**
	 * Add vendor to the shipping line meta key for shipping zones
	 *
	 * @since 1.6.3
	 */
	public function add_shipping_line_vendor( $item, $package_key, $package, $order ) {

			if ( ! isset( $package['vendor_id'] ) ) {
return;
			}

			$vendor_id = $package['vendor_id'];

			$item->add_meta_data( '_vendor_id', $vendor_id, true );

	}


} // WCVendors_Pro_Shipping_Controller
