<?php

/**
 * The WCVendors Pro Abstract Controller class
 *
 * This is the abstract controller class for all front end actions
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCVendors_Pro_Shipping_Method extends WC_Shipping_Method {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $wcvendors_pro The ID of this plugin.
	 */
	private $wcvendors_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool $debug plugin is in debug mode
	 */
	private static $debug;

	/**
	 * Is the plugin base directory
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $base_dir string path for the plugin directory
	 */
	private $base_dir;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wcvendors_pro The name of the plugin.
	 * @param      string $version       The version of this plugin.
	 */
	public function __construct() {

		$this->wcvendors_pro      = 'wcvendors-pro';
		$this->version            = '1.5.8';
		self::$debug              = false;
		$this->base_dir           = plugin_dir_path( dirname( __FILE__ ) );
		$this->id                 = 'wcv_pro_vendor_shipping';
		$this->method_title       = sprintf( __( '%s Shipping', 'wcvendors-pro' ), wcv_get_vendor_name( true, true ) );
		$this->method_description = sprintf( __( 'This shipping module is for your  %1$s to input their own shipping prices on their Pro Dashboard.  <p>The prices you enter for Product Cost and Handling Fees will only be used if a %2$s has not entered their own prices on their Pro Vendor Dashboard.  <p>The simplest shipping system is Flat Rate, where %3$s can input a cost within their country, and outside of their country.  Country Table Rate will require %4$s to enter country codes that they ship to and set prices for each country.  <p><strong><a href="https://docs.wcvendors.com/knowledge-base/wc-vendors-pro-shipping-system/" target="prodocs">WC Vendors Pro Shipping Documentation</a></strong>', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ), wcv_get_vendor_name( true, false ), wcv_get_vendor_name( false, false ), wcv_get_vendor_name( false, false ) );

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled                           = $this->get_option( 'enabled' );
		$this->title                             = $this->get_option( 'title' );
		$this->availability                      = $this->get_option( 'availability' );
		$this->countries                         = $this->get_option( 'countries' );
		$this->tax_status                        = $this->get_option( 'tax_status' );
		$this->product_tax                       = $this->get_option( 'product_tax' );
		$this->shipping_system                   = $this->get_option( 'shipping_system' );
		$this->national_cost                     = $this->get_option( 'national_cost' );
		$this->national_min_charge               = $this->get_option( 'national_min_charge' );
		$this->national_max_charge               = $this->get_option( 'national_max_charge' );
		$this->national_free_shipping_order      = $this->get_option( 'national_free_shipping_order' );
		$this->national_free                     = $this->get_option( 'national_free' );
		$this->national_disable                  = $this->get_option( 'national_disable' );
		$this->national_qty_override             = $this->get_option( 'national_qty_override' );
		$this->international_cost                = $this->get_option( 'international_cost' );
		$this->international_min_charge          = $this->get_option( 'international_min_charge' );
		$this->international_max_charge          = $this->get_option( 'international_max_charge' );
		$this->international_free_shipping_order = $this->get_option( 'international_free_shipping_order' );
		$this->international_free                = $this->get_option( 'international_free' );
		$this->international_disable             = $this->get_option( 'international_disable' );
		$this->international_qty_override        = $this->get_option( 'international_qty_override' );
		$this->country_rate                      = $this->get_option( 'country_rate' );
		$this->product_fee                       = $this->get_option( 'product_fee' );
		$this->min_charge                        = $this->get_option( 'min_charge' );
		$this->max_charge                        = $this->get_option( 'max_charge' );
		$this->max_charge_product                = $this->get_option( 'max_charge_product' );
		$this->free_shipping_order               = $this->get_option( 'free_shipping_order' );
		$this->free_shipping_product             = $this->get_option( 'free_shipping_product' );
		$this->shipping_policy                   = $this->get_option( 'shipping_policy' );
		$this->return_policy                     = $this->get_option( 'return_policy' );
		$this->product_shipping                  = array();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'                           => array(
				'title'   => __( 'Standalone Method', 'wcvendors-pro' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable WC Vendors Pro Shipping as a standalone shipping method', 'wcvendors-pro' ),
				'default' => 'yes',
			),
			'title'                             => array(
				'title'       => __( 'Method Title', 'wcvendors-pro' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wcvendors-pro' ),
				'default'     => sprintf( __( '%s Shipping', 'wc_shipping_per_product' ), wcv_get_vendor_name() ),
			),
			'tax_status'                        => array(
				'title'       => __( 'Tax Status', 'wcvendors-pro' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'description' => '',
				'default'     => 'none',
				'options'     => array(
					'taxable' => __( 'Taxable', 'wcvendors-pro' ),
					'none'    => __( 'None', 'wcvendors-pro' ),
				),
			),
			'product_tax'                       => array(
				'title'       => __( 'Include product tax', 'wcvendors-pro' ),
				'label'       => __( 'Uses products taxes in prices when calculating shipping costs or shipping thresholds.', 'wcvendors-pro' ),
				'type'        => 'checkbox',
				'description' => __( 'Tax Settings must be configured for prices to be exclusive of tax for this setting to apply. ', 'wcvendors-pro' ),
				'default'     => 'no',
			),
			'shipping_system'                   => array(
				'title'       => __( 'Shipping system', 'wcvendors-pro' ),
				'type'        => 'select',
				'default'     => 'flat',
				'class'       => 'wc-enhanced-select wcv-shipping-system',
				'description' => sprintf( __( 'Your %1$s have a simple flat rate for national and international shipping or a per country rate table. This can be overridden on a per %2$s basis.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ), wcv_get_vendor_name( true, false ) ),
				'options'     => WCVendors_Pro_Shipping_Controller::shipping_types(),
			),
			'national_cost'                     => array(
				'title'       => __( 'Product cost nationally', 'wcvendors-pro' ),
				'type'        => 'text',
				'class'       => 'wcv-flat-rate',
				'description' => sprintf( __( 'Default per product cost excluding tax for products on a per %s level. e.g. 5.50.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'default'     => '',
			),
			'national_min_charge'               => array(
				'title'       => __( 'Minimum shipping fee per order nationally', 'wcvendors-pro' ),
				'type'        => 'text',
				'class'       => 'wcv-flat-rate',
				'description' => sprintf( __( 'The minimum shipping fee excluding tax for order on a per %s level.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'default'     => '',
			),
			'national_max_charge'               => array(
				'title'       => __( 'Maximum shipping fee per order nationally', 'wcvendors-pro' ),
				'type'        => 'text',
				'class'       => 'wcv-flat-rate',
				'description' => sprintf( __( 'The maximum shipping fee excluding tax for order on a per %s level.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'default'     => '',
			),
			'national_free_shipping_order'      => array(
				'title'       => __( 'Free shipping order nationally', 'wcvendors-pro' ),
				'type'        => 'text',
				'class'       => 'wcv-flat-rate',
				'description' => sprintf( __( 'Free shipping for order spends over this amount for national shipping on a per %s level. This will override the max shipping charge above.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'default'     => '',
			),
			'national_free'                     => array(
				'title'       => __( 'Free national shipping', 'wcvendors-pro' ),
				'label'       => __( 'Enable store wide free national shipping', 'wcvendors-pro' ),
				'type'        => 'checkbox',
				'class'       => 'wcv-flat-rate',
				'description' => __( 'Check this to enable free national shipping', 'wcvendors-pro' ),
				'default'     => '',
			),
			'national_disable'                  => array(
				'title'       => __( 'Disable national shipping', 'wcvendors-pro' ),
				'label'       => __( 'Disable national shipping', 'wcvendors-pro' ),
				'type'        => 'checkbox',
				'class'       => 'wcv-flat-rate',
				'description' => __( 'Check this to disable national shipping', 'wcvendors-pro' ),
				'default'     => '',
			),
			'national_qty_override'             => array(
				'title'       => __( 'Product qty override national', 'wcvendors-pro' ),
				'label'       => __( 'Charge once for national shipping, even if more than one is purchased.', 'wcvendors-pro' ),
				'type'        => 'checkbox',
				'class'       => 'wcv-flat-rate',
				'description' => __( 'Disable the product qty in shipping calculations on a per product basis.', 'wcvendors-pro' ),
				'default'     => '',
			),
			'international_cost'                => array(
				'title'       => __( 'Product cost internationally', 'wcvendors-pro' ),
				'type'        => 'text',
				'class'       => 'wcv-flat-rate',
				'description' => sprintf( __( 'Default per product cost excluding tax for products on a per %s level. e.g. 5.50.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'default'     => '',
			),
			'international_min_charge'          => array(
				'title'       => __( 'Minimum shipping fee per order internationally', 'wcvendors-pro' ),
				'type'        => 'text',
				'class'       => 'wcv-flat-rate',
				'description' => sprintf( __( 'The minimum shipping fee excluding tax for order on a per %s level.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'default'     => '',
			),
			'international_max_charge'          => array(
				'title'       => __( 'Maximum shipping fee per order internationally', 'wcvendors-pro' ),
				'type'        => 'text',
				'class'       => 'wcv-flat-rate',
				'description' => sprintf( __( 'The maximum shipping fee excluding tax for order on a per %s level.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'default'     => '',
			),
			'international_free_shipping_order' => array(
				'title'       => __( 'Free shipping order internationally', 'wcvendors-pro' ),
				'type'        => 'text',
				'class'       => 'wcv-flat-rate',
				'description' => sprintf( __( 'Free shipping for order spends over this amount for international shipping on a per %s level. This will override the max shipping charge above.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'default'     => '',
			),
			'international_free'                => array(
				'title'       => __( 'Free international shipping', 'wcvendors-pro' ),
				'label'       => __( 'Enable store wide free international shipping', 'wcvendors-pro' ),
				'type'        => 'checkbox',
				'class'       => 'wcv-flat-rate',
				'description' => __( 'Check this to enable free international shipping', 'wcvendors-pro' ),
				'default'     => '',
			),
			'international_disable'             => array(
				'title'       => __( 'Disable international shipping', 'wcvendors-pro' ),
				'label'       => __( 'Disable store wide international shipping', 'wcvendors-pro' ),
				'type'        => 'checkbox',
				'class'       => 'wcv-flat-rate',
				'description' => __( 'Check this to disable international shipping', 'wcvendors-pro' ),
				'default'     => '',
			),
			'international_qty_override'        => array(
				'title'       => __( 'Product qty override international', 'wcvendors-pro' ),
				'label'       => __( 'Charge once for international shipping, even if more than one is purchased.', 'wcvendors-pro' ),
				'type'        => 'checkbox',
				'class'       => 'wcv-flat-rate',
				'description' => __( 'Disable the product qty in shipping calculations on a per product basis.', 'wcvendors-pro' ),
				'default'     => '',
			),
			'country_rate'                      => array(
				'title'   => __( 'Country table rate', 'wcvendors-pro' ),
				'label'   => __( 'Charge once for international shipping, even if more than one is purchased.', 'wcvendors-pro' ),
				'type'    => 'country_table',
				'class'   => 'wcv-country-rate',
				'default' => '',
			),
			'product_fee'                       => array(
				'title'       => sprintf( __( 'Default product handling fee (per %s)', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'type'        => 'text',
				'description' => __( 'Product handling fee excluding tax. Fixed amount (5.00) or add a percentage sign for a percentage (5%). Leave blank to disable.', 'wcvendors-pro' ),
				'default'     => '',
			),
			'min_charge'                        => array(
				'title'       => sprintf( __( 'Minimum shipping charged (per %s)', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'type'        => 'text',
				'description' => __( 'Notice: These settings will soon be removed. Please use respective settings for national/international.', 'wcvendors-pro' ),
				'default'     => '',
			),
			'max_charge'                        => array(
				'title'       => sprintf( __( 'Maximum shipping charged (per %s)', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'type'        => 'text',
				'description' => __( 'Notice: These settings will soon be removed. Please use respective settings for national/international.', 'wcvendors-pro' ),
				'default'     => '',
			),
			'free_shipping_order'               => array(
				'title'       => sprintf( __( 'Free shipping order (per %s)', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'type'        => 'text',
				'description' => __( 'Notice: These settings will soon be removed. Please use respective settings for national/international.', 'wcvendors-pro' ),
				'default'     => '',
			),
			'max_charge_product'                => array(
				'title'       => __( 'Maximum product shipping', 'wcvendors-pro' ),
				'type'        => 'text',
				'description' => __( 'The maximum shipping charged per product no matter the quantity. Leave blank to disable.', 'wcvendors-pro' ),
				'default'     => '',
			),
			'free_shipping_product'             => array(
				'title'       => __( 'Free shipping product (per product)', 'wcvendors-pro' ),
				'type'        => 'text',
				'description' => __( 'Free shipping if the spend per product is over this amount. Leave blank to disable.', 'wcvendors-pro' ),
				'default'     => '',
			),
			'shipping_policy'                   => array(
				'title'       => __( 'Default shipping policy', 'wcvendors-pro' ),
				'type'        => 'textarea',
				'description' => sprintf( __( 'Default shipping policy, displayed if a %s has not set a shipping policy at store level.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'default'     => '',
			),
			'return_policy'                     => array(
				'title'       => __( 'Default return policy', 'wcvendors-pro' ),
				'type'        => 'textarea',
				'description' => sprintf( __( 'Default return policy, displayed if a %s has not set a return policy at store level.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ),
				'default'     => '',
			),
			'availability'                      => array(
				'title'   => __( 'Method availability', 'wcvendors-pro' ),
				'type'    => 'select',
				'default' => 'all',
				'class'   => 'availability wc-enhanced-select',
				'options' => array(
					'all'      => __( 'All allowed countries', 'wcvendors-pro' ),
					'specific' => __( 'Specific Countries', 'wcvendors-pro' ),
				),
			),
			'countries'                         => array(
				'title'   => __( 'Specific Countries', 'wcvendors-pro' ),
				'type'    => 'multiselect',
				'class'   => 'chosen_select',
				'css'     => 'width: 450px;',
				'default' => '',
				'options' => WC()->countries->get_allowed_countries(),
			),
		);
	}

	/**
	 *  Calculate the shipping
	 *
	 * @since    1.1.0
	 *
	 * @param     mixed $package the shipping package data
	 */
	public function calculate_shipping( $package = array() ) {

		$_tax                 = new WC_Tax();
		$taxes                = array();
		$shipping_cost        = 0;
		$vendor_cost          = array();
		$vendor_shipping_cost = array(
			'total_shipping' => 0,
			'total_cost'     => 0,
		);

		$settings = array(
			'product_tax'                       => $this->product_tax,
			'national_cost'                     => $this->national_cost,
			'national_min_charge'               => $this->national_min_charge,
			'national_max_charge'               => $this->national_max_charge,
			'national_free_shipping_order'      => $this->national_free_shipping_order,
			'national_free'                     => $this->national_free,
			'national_disable'                  => $this->national_disable,
			'international_cost'                => $this->international_cost,
			'international_min_charge'          => $this->international_min_charge,
			'international_max_charge'          => $this->international_max_charge,
			'international_free_shipping_order' => $this->international_free_shipping_order,
			'international_free'                => $this->international_free,
			'international_disable'             => $this->international_disable,
			'product_fee'                       => $this->product_fee,
			'min_charge'                        => $this->min_charge,
			'max_charge'                        => $this->max_charge,
			'max_charge_product'                => $this->max_charge_product,
			'free_shipping_order'               => $this->free_shipping_order,
			'free_shipping_product'             => $this->free_shipping_product,
			'shipping_system'                   => $this->shipping_system,
			'national_qty_override'             => $this->national_qty_override,
			'international_qty_override'        => $this->international_qty_override,
			'country_rate'                      => $this->country_rate,
		);

		$_total_cost = 0;
		// This shipping method loops through products.
		if ( sizeof( $package['contents'] ) > 0 ) {

			foreach ( $package['contents'] as $item_id => $cart_item ) {

				if ( $cart_item['quantity'] > 0 ) {

					if ( $cart_item['data']->needs_shipping() ) {

						$post               = get_post( $cart_item['product_id'] );
						$vendor_id          = $post->post_author;
						$item_shipping_cost = 0;
						$rate               = false;
						$product_id         = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];

						// Currently uses the parent's shipping costs for now.
						// Eventually allow to set variation shipping costs by changing the product_id to the variation_id
						if ( $cart_item['variation_id'] ) {
							$rate = self::get_shipping_rate( $cart_item['product_id'], $vendor_id, $package, $settings );
						}

						if ( ! $rate ) {
							$rate = self::get_shipping_rate( $cart_item['product_id'], $vendor_id, $package, $settings );
						}

						if ( self::$debug ) {
							WCVendors_Pro::log( $rate, 'RATE' );
						}

						if ( $rate ) {

							$qty = ( $rate->qty_override === 'yes' && empty( $rate->max_charge_product ) ) ? 1 : (int) $cart_item['quantity'];

							if ( self::$debug ) {
								WCVendors_Pro::log( $qty, 'QTY' );
							}
							if ( self::$debug ) {
								WCVendors_Pro::log( $cart_item['data'], 'Cart Item Data' );
							}

							$product_price = $cart_item['data']->get_price();

							$_total_cost += $product_price * $cart_item['quantity'];

							// If include product tax is enabled
							if ( wc_string_to_bool( $this->product_tax ) ) {
								if ( 'no' === get_option( 'woocommerce_prices_include_tax', 'no' ) ) {
									$product_price = wc_get_price_including_tax( $cart_item['data'] );
								}
							}

							if ( self::$debug ) {
								WCVendors_Pro::log( $product_price, 'Product Price' );
							}

							$item_shipping_cost += $rate->fee * $qty;
							$product_cost        = $product_price * $qty;

							if ( $rate->max_charge_product > 0 && $item_shipping_cost > $rate->max_charge_product ) {
								$item_shipping_cost = $rate->max_charge_product;
							}

							// Product handling fee.
							if ( stripos( $rate->product_fee, '%' ) > 0 ) {

								$product_fee_percent = substr( $rate->product_fee, 0, stripos( $rate->product_fee, '%' ) );
								$product_fee         = ( $product_fee_percent / 100 ) * $product_cost;

							} else {

								$product_fee = $this->get_fee( (float) $rate->product_fee, (float) $item_shipping_cost ) * (int) $qty;

							}

							$item_shipping_cost += $product_fee;

							$item_taxes = 0;

							if ( empty( $vendor_shipping_cost ) ) {

								$vendor_shipping_cost = array(
									'total_shipping' => $item_shipping_cost,
									'total_cost'     => $product_cost,
									'items'          => array(
										$item_id =>
											array(
												'product_id'    => $product_id,
												'shipping_cost' => $item_shipping_cost,
											),
									),
								);

							} else {

								$vendor_shipping_cost['total_shipping']   += $item_shipping_cost;
								$vendor_shipping_cost['total_cost']       += $product_cost;
								$vendor_shipping_cost['items'][ $item_id ] = array(
									'product_id'    => $product_id,
									'shipping_cost' => $item_shipping_cost,
								);

							}

							// Check free shipping over for this product
							if ( $rate->free_shipping_product > 0 && $product_cost > $rate->free_shipping_product ) {
								$item_shipping_cost = 0;
							}

							// Get shipping taxes
							$taxes = $this->get_shipping_taxes( $cart_item, $item_shipping_cost );

						} else {
							// No fees found for this product.
							return;
						}
					}
				}
			}
		}

		$shipping_costs         = $vendor_shipping_cost['total_shipping'];
		$order_spend_total      = $vendor_shipping_cost['total_cost'];
		$store_shipping_details = get_user_meta( $vendor_id, '_wcv_shipping', true );
		$store_shipping_details = wp_parse_args( $store_shipping_details, WCVendors_Pro_Shipping_Controller::get_shipping_defaults() );

		$customer_country = strtolower( $package['destination']['country'] );
		$store_country    = ( $store_shipping_details && $store_shipping_details['shipping_from'] == 'other' ) ? strtolower( $store_shipping_details['shipping_address']['country'] ) : strtolower( get_user_meta( $vendor_id, '_wcv_store_country', true ) );
		$prefix           = $customer_country == $store_country ? 'national_' : 'international_';

		$min_charge = array_filter(
			 array(
				 $store_shipping_details[ $prefix . 'min_charge' ],
				 $store_shipping_details['min_charge'],
				 $settings[ $prefix . 'min_charge' ],
				 $settings['min_charge'],
			 )
			);
		$min_charge = (float) reset( $min_charge );

		$max_charge = array_filter(
			 array(
				 $store_shipping_details[ $prefix . 'max_charge' ],
				 $store_shipping_details['max_charge'],
				 $settings[ $prefix . 'max_charge' ],
				 $settings['max_charge'],
			 )
			);
		$max_charge = (float) reset( $max_charge );

		$free_shipping_order = array_filter(
			 array(
				 $store_shipping_details[ $prefix . 'free_shipping_order' ],
				 $store_shipping_details['free_shipping_order'],
				 $settings[ $prefix . 'free_shipping_order' ],
				 $settings['free_shipping_order'],
			 )
			);
		$free_shipping_order = (float) reset( $free_shipping_order );

		$qty_override = array_filter(
			array(
				$store_shipping_details[ $prefix . 'qty_override' ],
				$settings[ $prefix . 'qty_override' ],
			)
		   );
	   $qty_override  = wc_string_to_bool( reset( $qty_override ) );

		if ( self::$debug ) {
			WCVendors_Pro::log( $settings, 'SETTINGS' );
		}
		if ( self::$debug ) {
			WCVendors_Pro::log( $order_spend_total, 'Order Spend' );
		}

		// Minimum charge for the order
		if ( $min_charge > 0 && $shipping_costs <= $min_charge ) {

			$vendor_shipping_cost['total_shipping'] = $min_charge;
			// This triggers a product cost split
			$vendor_shipping_cost['items'] = $this->split_shipping( $vendor_shipping_cost['items'], $min_charge );

			// Calculate shipping taxes
			$taxes = $this->get_shipping_taxes( $cart_item, $min_charge );
		}

		// Maximum charge for the order
		if ( $max_charge > 0 && $shipping_costs >= $max_charge ) {
			$vendor_shipping_cost['total_shipping'] = $max_charge;
			// This triggers a product cost split
			$vendor_shipping_cost['items'] = $this->split_shipping( $vendor_shipping_cost['items'], $max_charge );

			// Calculate shipping taxes
			$taxes = $this->get_shipping_taxes( $cart_item, $max_charge );
		}

		// free shipping for the order
		if ( $free_shipping_order > 0 && $order_spend_total >= $free_shipping_order ) {
			// Check if free shipping is valid for total order cost
			$vendor_shipping_cost['total_shipping'] = 0;
			$vendor_shipping_cost['items']          = $this->split_shipping( $vendor_shipping_cost['items'], 0 );
			$taxes                                  = array();
		}

		if ( $free_shipping_order > 0 && $_total_cost >= $free_shipping_order && $qty_override ) {
			// Check if free shipping is valid for total order cost and orverride qty
			$vendor_shipping_cost['total_shipping'] = 0;
			$vendor_shipping_cost['items']          = $this->split_shipping( $vendor_shipping_cost['items'], 0 );
			$taxes                                  = array();
		}

		// Check if there is a coupon with free shipping and apply free shipping for this vendor only
		foreach ( $package['applied_coupons'] as $coupon_code ) {

			$coupon       = new WC_Coupon( $coupon_code );
			$coupon_owner = WCVendors_Pro_Vendor_Controller::get_vendor_from_object( $coupon->get_id() );
			$coupon_user  = get_userdata( $coupon_owner );

			if ( $coupon_owner == $vendor_id || in_array( 'administrator', $coupon_user->roles ) ) {

				$free_shipping = $coupon->get_meta( 'vendor_free_shipping' );

				// Set the total shipping to 0
				if ( 'yes' === $free_shipping ) {
					$vendor_shipping_cost['total_shipping'] = 0;
					$vendor_shipping_cost['items']          = $this->split_shipping( $vendor_shipping_cost['items'], 0 );
				}
			}
		}

		// $vendor_totals 	= wp_list_pluck( $vendor_cost, 'total_shipping' );
		// $total 			= ( float ) array_sum( $vendor_totals );
		$shipping_meta = array( 'vendor_costs' => $vendor_shipping_cost );

		if ( self::$debug ) {
			WCVendors_Pro::log( $taxes, 'Taxes' );
		}

		// Add rate
		$this->add_rate(
			array(
				'id'        => $this->id,
				'label'     => apply_filters( 'wcvendors_pro_shipping_label', sprintf( __( '%s Shipping', 'wcvendors-pro' ), WCV_Vendors::get_vendor_sold_by( $vendor_id ) ), $vendor_id ),
				'cost'      => $vendor_shipping_cost['total_shipping'],
				'meta_data' => $shipping_meta,
				'taxes'     => $taxes,  // We calc tax in the method
				'package'   => $package,
			)
		);

	} // calculate_shipping()

	/**
	 * Calculate the shipping taxes
	 *
	 * @param array $cart_item          - the item whose taxes are to be calculated
	 * @param float $item_shipping_cost - the cost of the item
	 *
	 * @return void
	 *
	 * @since 1.5.5
	 */
	public function get_shipping_taxes( $cart_item, $item_shipping_cost ) {
		$_tax  = new WC_Tax();
		$taxes = array();

		if ( $this->tax_status === 'taxable' && wc_tax_enabled() && $item_shipping_cost > 0 ) {

			$tax_rates  = $_tax->get_shipping_tax_rates( $cart_item['data']->get_tax_class() );
			$item_taxes = $_tax->calc_shipping_tax( $item_shipping_cost, $tax_rates );

			if ( self::$debug ) {
				WCVendors_Pro::log( $tax_rates );
			}

			// Add up the item taxes
			foreach ( array_keys( $taxes + $item_taxes ) as $key ) {
				$taxes[ $key ] = ( isset( $item_taxes[ $key ] ) ? $item_taxes[ $key ] : 0 ) + ( isset( $taxes[ $key ] ) ? $taxes[ $key ] : 0 );
			}
		}

		return $taxes;

	} //get_shipping_taxes()

	/**
	 *  Get the shipping rate
	 *
	 * @since   1.1.0
	 * @version 1.7.7
	 *
	 * @param     object $product the product to get the rate for
	 * @param     mixed  $package the shipping package data
	 */
	public static function get_shipping_rate( $product_id, $vendor_id, $package, $settings ) {

		$customer_country    = strtolower( $package['destination']['country'] );
		$customer_state      = strtolower( $package['destination']['state'] );
		$customer_postcode   = strtolower( $package['destination']['postcode'] );
		$store_shipping_type = get_user_meta( $vendor_id, '_wcv_shipping_type', true );
		$store_rates         = (array) get_user_meta( $vendor_id, '_wcv_shipping', true );
		$store_country       = ( $store_rates && array_key_exists( 'shipping_from', $store_rates ) && $store_rates['shipping_from'] == 'other' ) ? strtolower( $store_rates['shipping_address']['country'] ) : strtolower( get_user_meta( $vendor_id, '_wcv_store_country', true ) );
		$store_state         = ( $store_rates && array_key_exists( 'shipping_from', $store_rates ) && $store_rates['shipping_from'] == 'other' ) ? strtolower( $store_rates['shipping_address']['state'] ) : strtolower( get_user_meta( $vendor_id, '_wcv_store_state', true ) );
		$shipping_rate       = new stdClass();
		$product_rates       = (array) get_post_meta( $product_id, '_wcv_shipping_details', true );

		// fill out the settings correctly
		$store_rates   = wp_parse_args( $store_rates, WCVendors_Pro_Shipping_Controller::get_shipping_defaults() );
		$product_rates = wp_parse_args( $product_rates, WCVendors_Pro_Shipping_Controller::get_shipping_defaults() );

		array_walk( $store_rates, 'wcv_format_shipping_data' );
		array_walk( $product_rates, 'wcv_format_shipping_data' );

		if ( self::$debug ) {
			WCVendors_Pro::log( $product_rates, 'PRODUCT RATES' );
		}
		if ( self::$debug ) {
			WCVendors_Pro::log( $store_rates, 'STORE RATES' );
		}

		$shipping_rate->product_id = $product_id;

		// Check if the store has a shipping type override.
		$shipping_type = ( $store_shipping_type != '' ) ? $store_shipping_type : $settings['shipping_system'];

		// Get default country for admin.
		if ( ! WCV_Vendors::is_vendor( $vendor_id ) ) {
			$store_country = WC()->countries->get_base_country();
		}

		if ( $shipping_type == 'flat' ) {

			// National Shipping
			if ( $customer_country == $store_country ) {

				if ( ( is_array( $product_rates ) && array_key_exists( 'national_disable', $product_rates ) && 'yes' === $product_rates['national_disable'] ) ) {

					if ( self::$debug ) {
						WCVendors_Pro::log( 'product national disabled' );
					}

					return $shipping_rate = false;

				} elseif ( is_array( $product_rates ) && ( strlen( $product_rates['national_disable'] ) === 0 && ( strlen( trim( $product_rates['national'] ) ) > 0 || strlen( trim( $product_rates['national_free'] ) ) > 0 ) ) ) {

					if ( self::$debug ) {
						WCVendors_Pro::log( 'product national triggered' );
					}

					// Is free shipping enabled ?
					if ( 'yes' === $product_rates['national_free'] ) {
						$shipping_rate->fee = 0;
					} else {
						$shipping_rate->fee = $product_rates['national'];
					}

					$shipping_rate->max_charge_product    = $product_rates['max_charge_product'];
					$shipping_rate->free_shipping_product = $product_rates['free_shipping_product'];
					$shipping_rate->qty_override          = $product_rates['national_qty_override'];
					$shipping_rate->product_fee           = wcv_percentage_to_price( $product_rates['handling_fee'], $product_id );

					if ( ( is_array( $product_rates ) && array_key_exists( 'national_disable', $product_rates ) && 'yes' === $product_rates['national_disable'] ) ) {
						return $shipping_rate = false;
					}
					} elseif ( ( is_array( $store_rates ) && array_key_exists( 'national_disable', $store_rates ) && 'yes' === $store_rates['national_disable'] ) ) {

					if ( self::$debug ) {
						WCVendors_Pro::log( 'store rate national disabled' );
					}

					return $shipping_rate = false;

				} elseif ( is_array( $store_rates ) && ( strlen( $store_rates['national_disable'] ) === 0 && ( strlen( trim( $store_rates['national'] ) ) > 0 || strlen( $store_rates['national_free'] ) > 0 ) ) ) {

					if ( self::$debug ) {
						WCVendors_Pro::log( 'store rate international triggered' );
					}

					// Is free shipping enabled at store level?
					if ( 'yes' === $store_rates['national_free'] ) {
						$shipping_rate->fee = 0;
					} else {
						$shipping_rate->fee = $store_rates['national'];
					}
					$shipping_rate->product_fee           = wcv_percentage_to_price( $store_rates['product_handling_fee'], $product_id );
					$shipping_rate->max_charge_product    = $store_rates['max_charge_product'];
					$shipping_rate->free_shipping_product = $store_rates['free_shipping_product'];
					$shipping_rate->qty_override          = $store_rates['national_qty_override'];

				} elseif ( (float) trim( $settings['national_cost'] ) > 0 || ( 'yes' === $settings['national_free'] ) || $settings['min_charge'] > 0 || $settings['max_charge'] > 0 ) {

					if ( self::$debug ) {
						WCVendors_Pro::log( 'global settings triggered' );
					}

					if ( 'yes' === $settings['national_free'] ) {
						$shipping_rate->fee = 0;
					} else {
						$shipping_rate->fee = $settings['national_cost'];
					}

					// Set the fees correctly for the min and max charges
					if ( $shipping_rate->fee == 0 && $settings['min_charge'] > 0 ) {
						$shipping_rate->fee = $settings['min_charge'];
					} elseif ( $shipping_rate->fee == 0 && $settings['max_charge'] > 0 ) {
						$shipping_rate->fee = $settings['max_charge'];
					}

					$shipping_rate->product_fee           = wcv_percentage_to_price( $settings['product_fee'], $product_id );
					$shipping_rate->qty_override          = $settings['national_qty_override'];
					$shipping_rate->max_charge_product    = $settings['max_charge_product'];
					$shipping_rate->min_charge            = $settings['min_charge'];
					$shipping_rate->max_charge            = $settings['max_charge'];
					$shipping_rate->free_shipping_product = $settings['free_shipping_product'];

				} else {

					if ( self::$debug ) {
						WCVendors_Pro::log( 'no national flat rate found' );
					}

					$shipping_rate = false;
				}

				// Return the national rate found
				return $shipping_rate;

			} else {

				// International shipping
				if ( ( is_array( $product_rates ) && array_key_exists( 'international_disable', $product_rates ) && 'yes' === $product_rates['international_disable'] ) ) {

					if ( self::$debug ) {
						WCVendors_Pro::log( 'international disabled' );
					}

					return $shipping_rate = false;

				} elseif ( is_array( $product_rates ) && ( strlen( $product_rates['international_disable'] ) === 0 && ( strlen( trim( $product_rates['international'] ) ) > 0 || strlen( $product_rates['international_free'] ) > 0 ) ) ) {

					if ( self::$debug ) {
						WCVendors_Pro::log( 'international product rate' );
					}

					// Is free shipping enabled ?
					if ( 'yes' === $product_rates['international_free'] ) {
						$shipping_rate->fee = 0;
					} else {
						$shipping_rate->fee = $product_rates['international'];
					}

					$shipping_rate->product_fee           = wcv_percentage_to_price( $product_rates['handling_fee'], $product_id );
					$shipping_rate->max_charge_product    = $product_rates['max_charge_product'];
					$shipping_rate->qty_override          = $product_rates['international_qty_override'];
					$shipping_rate->free_shipping_product = $product_rates['free_shipping_product'];

				} elseif ( is_array( $store_rates ) && array_key_exists( 'international_disable', $store_rates ) && 'yes' === $store_rates['international_disable'] ) {

					if ( self::$debug ) {
						WCVendors_Pro::log( 'store rate international disabled' );
					}

					return $shipping_rate = false;

				} elseif ( is_array( $store_rates ) && ( strlen( $store_rates['international_disable'] ) === 0 && ( strlen( trim( $store_rates['international'] ) ) > 0 || strlen( $store_rates['international_free'] ) > 0 ) ) ) {

					if ( self::$debug ) {
						WCVendors_Pro::log( 'international store rate' );
					}

					if ( 'yes' === $store_rates['international_free'] ) {
						$shipping_rate->fee = 0;
					} else {
						$shipping_rate->fee = $store_rates['international'];

					}

					$shipping_rate->product_fee           = wcv_percentage_to_price( $store_rates['product_handling_fee'], $product_id );
					$shipping_rate->max_charge_product    = $store_rates['max_charge_product'];
					$shipping_rate->qty_override          = $store_rates['international_qty_override'];
					$shipping_rate->free_shipping_product = $store_rates['free_shipping_product'];

				} elseif ( (float) trim( $settings['international_cost'] ) > 0 || ( 'yes' === $settings['international_free'] ) || $settings['min_charge'] > 0 || $settings['max_charge'] > 0 ) {

					if ( 'yes' === $settings['international_free'] ) {
						$shipping_rate->fee = 0;
					} else {
						$shipping_rate->fee = $settings['international_cost'];
					}

					// Set the fees correctly for the min and max charges
					if ( $shipping_rate->fee == 0 && $settings['min_charge'] > 0 ) {
						$shipping_rate->fee = $settings['min_charge'];
					} elseif ( $shipping_rate->fee == 0 && $settings['max_charge'] > 0 ) {
						$shipping_rate->fee = $settings['max_charge'];
					}

					$shipping_rate->product_fee           = wcv_percentage_to_price( $settings['product_fee'], $product_id );
					$shipping_rate->qty_override          = $settings['international_qty_override'];
					$shipping_rate->max_charge_product    = $settings['max_charge_product'];
					$shipping_rate->min_charge            = $settings['min_charge'];
					$shipping_rate->max_charge            = $settings['max_charge'];
					$shipping_rate->free_shipping_product = $settings['free_shipping_product'];

				} else {
					return $shipping_rate = false;
				}
			}

			// Return the international rates found
			return $shipping_rate;

		} else {

			$product_shipping_table = get_post_meta( $product_id, '_wcv_shipping_rates', true );
			$store_shipping_table   = get_user_meta( $vendor_id, '_wcv_shipping_rates', true );
			$global_shipping_table  = $settings['country_rate'];

			// Check to see if the product has any rates set.
			if ( is_array( $product_shipping_table ) ) {

				$shipping_rate->product_fee           = ( is_array( $product_rates ) && array_key_exists( 'handling_fee', $product_rates ) ) ? $product_rates['handling_fee'] : 0;
				$shipping_rate->max_charge_product    = $product_rates['max_charge_product'];
				$shipping_rate->free_shipping_product = $product_rates['free_shipping_product'];

				foreach ( $product_shipping_table as $rate ) {

					// Country matches and state matches and postcode matches
					// Required for version differences.
					if ( array_key_exists( 'postcode', $rate ) ) {

						$shipping_rate->qty_override = $rate['qty_override'];

						if ( strtolower( $customer_country ) === strtolower( $rate['country'] ) && strtolower( $customer_state ) === strtolower( $rate['state'] ) && self::check_postcode( $customer_postcode, $rate['postcode'] ) ) {
							$shipping_rate->fee = $rate['fee'];

							return $shipping_rate;
						}
					}

					// Country and state match
					if ( strtolower( $customer_country ) === strtolower( $rate['country'] ) && strtolower( $customer_state ) === strtolower( $rate['state'] ) && empty( $rate['postcode'] ) ) {
						$shipping_rate->fee = $rate['fee'];

						return $shipping_rate;
					}

					// Country matches and state is any
					if ( strtolower( $customer_country ) === strtolower( $rate['country'] ) && empty( $rate['state'] ) && empty( $rate['postcode'] ) ) {
						$shipping_rate->fee = $rate['fee'];

						return $shipping_rate;
					}

					// Country and state is any
					if ( $rate['country'] === '' && $rate['state'] === '' && $rate['postcode'] == '' ) {
						$shipping_rate->fee = $rate['fee'];

						return $shipping_rate;
					}
				}
			}

			// Check to see if the store has any rates set.
			if ( is_array( $store_shipping_table ) ) {

				$shipping_rate->product_fee           = ( is_array( $store_rates ) && array_key_exists( 'product_handling_fee', $store_rates ) ) ? $store_rates['product_handling_fee'] : 0;
				$shipping_rate->max_charge_product    = $store_rates['max_charge_product'];
				$shipping_rate->free_shipping_product = $store_rates['free_shipping_product'];

				foreach ( $store_shipping_table as $rate ) {

					if ( array_key_exists( 'postcode', $rate ) ) {

						// Grab qty override from rate line
						$shipping_rate->qty_override = $rate['qty_override'];

						$postcode_found = self::check_postcode( $customer_postcode, $rate['postcode'] );

						if ( strtolower( $customer_country ) === strtolower( $rate['country'] ) && strtolower( $customer_state ) === strtolower( $rate['state'] ) && $postcode_found ) {
							$shipping_rate->fee = $rate['fee'];

							return $shipping_rate;
						}

						// Country and state
						if ( strtolower( $customer_country ) == strtolower( $rate['country'] ) && strtolower( $customer_state ) == strtolower( $rate['state'] ) && empty( $rate['postcode'] ) ) {
							$shipping_rate->fee = $rate['fee'];

							return $shipping_rate;
						}

						// Country and state is any
						if ( strtolower( $customer_country ) == strtolower( $rate['country'] ) && empty( $rate['state'] ) && empty( $rate['postcode'] ) ) {
							$shipping_rate->fee = $rate['fee'];

							return $shipping_rate;
						}

						// Country is any and state is any
						if ( $rate['country'] == '' && $rate['state'] == '' && $rate['postcode'] == '' ) {
							$shipping_rate->fee = $rate['fee'];

							return $shipping_rate;
						}
					}
				}
			}

			// Check if there is any global rates set
			if ( is_array( $global_shipping_table ) ) {

				$shipping_rate->product_fee           = $settings['product_fee'];
				$shipping_rate->max_charge_product    = $settings['max_charge_product'];
				$shipping_rate->free_shipping_product = $settings['free_shipping_product'];

				foreach ( $global_shipping_table as $rate ) {

					if ( array_key_exists( 'postcode', $rate ) ) {

						// Grab qty override from rate line
						$shipping_rate->qty_override = $rate['qty_override'];

						$postcode_found = self::check_postcode( $customer_postcode, $rate['postcode'] );

						if ( strtolower( $customer_country ) === strtolower( $rate['country'] ) && strtolower( $customer_state ) === strtolower( $rate['state'] ) && $postcode_found ) {
							$shipping_rate->fee = $rate['fee'];

							return $shipping_rate;
						}

						// Country and state
						if ( strtolower( $customer_country ) == strtolower( $rate['country'] ) && strtolower( $customer_state ) == strtolower( $rate['state'] ) && empty( $rate['postcode'] ) ) {
							$shipping_rate->fee = $rate['fee'];

							return $shipping_rate;
						}

						// Country and state is any
						if ( strtolower( $customer_country ) == strtolower( $rate['country'] ) && empty( $rate['state'] ) && empty( $rate['postcode'] ) ) {
							$shipping_rate->fee = $rate['fee'];

							return $shipping_rate;
						}

						// Country is any and state is any
						if ( $rate['country'] == '' && $rate['state'] == '' && $rate['postcode'] == '' ) {
							$shipping_rate->fee = $rate['fee'];

							return $shipping_rate;
						}
					}
				}
			}

			return false;

		}

		return $shipping_rate;

	} // get_shipping_rate()

	/**
	 * Check if this post code is valid
	 *
	 * @param mixed $customer_postcode - the postcode to check
	 * @param mixed $rate_postcode     - the postcode(s) to check against
	 */
	public static function check_postcode( $customer_postcode, $rate_postcode ) {

		// clean both codes before doing anything to them
		$customer_postcode = strtolower( str_replace( ' ', '', $customer_postcode ) );
		$rate_postcode     = strtolower( str_replace( ' ', '', $rate_postcode ) );

		$postcode_length = strlen( $customer_postcode );

		// single post code to check?
		if ( $customer_postcode == $rate_postcode ) {
			return true;
		}

		// wildcard postcode ?
		$wildcard_position = strpos( $rate_postcode, '*' );

		// The rate has a wildcard match only the beginning of both
		if ( $wildcard_position > 0 ) {

			$customer_postcode_start = substr( $customer_postcode, 0, $wildcard_position );
			$rate_postcode_start     = substr( $rate_postcode, 0, $wildcard_position );

			return ( $customer_postcode_start === $rate_postcode_start ) ? true : false;

		}

		// check if the rate contains a range
		$range_position = strpos( $rate_postcode, '-' );

		// postcode range set
		if ( $range_position > 0 ) {

			$range = array_map( 'trim', explode( apply_filters( 'wcv_shipping_postcode_range_separator', '-' ), $rate_postcode ) );

			$min_postcode = ( is_numeric( $range[0] ) ) ? $range[0] : self::make_numeric_postcode( $range[0] );
			$max_postcode = ( is_numeric( $range[1] ) ) ? $range[1] : self::make_numeric_postcode( $range[1] );

			if ( self::$debug ) {
				WCVendors_Pro::log( $min_postcode, 'Min Post Code' );
			}
			if ( self::$debug ) {
				WCVendors_Pro::log( $max_postcode, 'Max Post Code' );
			}

			$customer_postcode = ( is_numeric( $customer_postcode ) ) ? $customer_postcode : self::make_numeric_postcode( $customer_postcode );

			if ( self::$debug ) {
				WCVendors_Pro::log( $customer_postcode, 'Customer Post Code' );
			}

			// check if the ranges are the same size as the customer postcode
			// if ( $postcode_length != strlen( $min_postcode ) ) return false;
			// Check to see if the postcode is in the range using filter_var
			$in_range = filter_var(
				(int) $customer_postcode,
				FILTER_VALIDATE_INT,
				array(
					'options' => array(
						'min_range' => (int) $min_postcode,
						'max_range' => (int) $max_postcode,
					),
				)
			);

			if ( self::$debug ) {
				WCVendors_Pro::log( $in_range, 'In Range' );
			}

			// if there is a number in the range return what it finds
			return is_int( $in_range );

		}

		return false;

	} // check_postcode()

	/**
	 * Make Numeric postcode
	 *
	 * Converts letters to numbers so we can do a simple range check on postcodes.
	 *
	 * E.g. PE30 becomes 16050300 (P = 16, E = 05, 3 = 03, 0 = 00)
	 *
	 * @access public
	 *
	 * @param mixed $postcode
	 *
	 * @return int $numberic_postcode
	 */
	public static function make_numeric_postcode( $postcode ) {
		$postcode_length    = strlen( $postcode );
		$letters_to_numbers = array_merge( array( 0 ), range( 'A', 'Z' ) );
		$letters_to_numbers = array_flip( $letters_to_numbers );
		$numeric_postcode   = '';

		for ( $i = 0; $i < $postcode_length; $i ++ ) {
			if ( is_numeric( $postcode[ $i ] ) ) {
				$numeric_postcode .= str_pad( $postcode[ $i ], 2, '0', STR_PAD_LEFT );
			} elseif ( isset( $letters_to_numbers[ $postcode[ $i ] ] ) ) {
				$numeric_postcode .= str_pad( $letters_to_numbers[ $postcode[ $i ] ], 2, '0', STR_PAD_LEFT );
			} else {
				$numeric_postcode .= '00';
			}
		}

		return $numeric_postcode;

	} //make_numeric_postcode()

	/**
	 * Split the shipping amount the products
	 *
	 * @since  1.4.0
	 * @access public
	 *
	 * @param array - $items the shipping items array
	 * @param float - $total the total shipping costs
	 *
	 * @return array -
	 */
	public function split_shipping( $items, $total ) {

		$last_item_id    = '';
		$total_remaining = 0;

		$new_shipping_cost = ( $total == 0 ) ? 0 : $total / count( $items );

		foreach ( $items as $item_id => $details ) {
			$items[ $item_id ]['shipping_cost'] = number_format( $new_shipping_cost, 2 );
			$last_item_id                       = $item_id;
			$total                             -= number_format( $new_shipping_cost, 2 );
		}

		// Make sure any uneven splits are still stored correctly for commissions
		$items[ $last_item_id ]['shipping_cost'] += number_format( $total, 2 );
		$items[ $last_item_id ]['shipping_cost']  = number_format( $items[ $last_item_id ]['shipping_cost'], 2 );

		return apply_filters( 'wcv_split_shipping_items', $items );

	} // split_shipping()

	/**
	 * Class logger so that we can keep our debug and logging information cleaner
	 *
	 * @since  1.3.4
	 * @access public
	 *
	 * @param mixed - $data the data to go to the error log could be string, array or object
	 */
	public static function log( $data ) {

		if ( is_array( $data ) || is_object( $data ) ) {
			error_log( print_r( $data, true ) );
		} else {
			error_log( $data );
		}

	} // log()

	/**
	 * Custom country rate field for the settings api
	 *
	 * @since  1.4.0
	 * @access public
	 */
	public function generate_country_table_html( $key, $data ) {

		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data           = wp_parse_args( $data, $defaults );
		$field_key      = $this->get_field_key( $key );
		$shipping_rates = $this->get_option( $key );
		$screen         = get_current_screen();

		ob_start();
		?>
		<tr valign="top" class="wcv_country_rate_table">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">

				<div class="wcv-country_rate_shipping wcv-shipping-rates wcv-shipping-country">

					<?php include apply_filters( 'wcv_partial_path_pro_user_country_rate', 'partials/vendor/wcvendors-pro-user-meta-shipping-country-rate.php' ); ?>

				</div>

			</td>
		</tr>
		<?php

		return ob_get_clean();

	} // generate_country_table_html()

	/**
	 * Custom field validator for the country rate table
	 */
	public function validate_country_rate_field( $key, $value ) {

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
		}

		return $shipping_rates;

	} // validate_country_rate_field()

}
