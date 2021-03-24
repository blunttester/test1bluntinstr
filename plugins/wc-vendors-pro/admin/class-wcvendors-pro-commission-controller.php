<?php

/**
 * The WC Vendors Pro commission controller.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin
 * @author     Jamie Madden <support@wcvendors.com>
 */

class WCVendors_Pro_Commission_Controller {


	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $wcvendors_pro The ID of this plugin.
	 */
	private $wcvendors_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    bool $debug plugin is in debug mode
	 */
	private $debug;

	/**
	 * Is the plugin base directory
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $base_dir string path for the plugin directory
	 */
	private $base_dir;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $wcvendors_pro The name of the plugin.
	 * @param string $version       The version of this plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {

		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;
		$this->debug         = $debug;
		$this->base_dir      = plugin_dir_path( dirname( __FILE__ ) );

	}

	/**
	 *  Process the new commission structure
	 *
	 * @since   1.0.0
	 * @version 1.7.3
	 */
	public function process_commission( $commission, $product_id, $product_price, $order, $qty, $item = array() ) {

		// Check if this is a variation and get the parent id, this ensures that the correct vendor id is retrieved.
		if ( get_post_type( $product_id ) === 'product_variation' ) {
			$product_id = get_post_field( 'post_parent', $product_id );
		}

		// Product Commission.
		$product_commission_type = get_post_meta( $product_id, 'wcv_commission_type', true );

		// Store Commission.
		$vendor_id             = get_post_field( 'post_author', $product_id );
		$store_commission_type = get_user_meta( $vendor_id, '_wcv_commission_type', true );

		// Reset product count for vendor.
		wp_cache_delete( 'vendor_' . $vendor_id . '_total_sales', 'counts' );

		// Category commission.
		$product                  = wc_get_product( $product_id );
		$categories               = $product->get_category_ids();
		$category_commission_type = '';
		if ( count( $categories ) == 1 && 'single_select' === get_option( 'wcvendors_category_display', 'select' ) ) {
			$category_id              = reset( $categories );
			$category_commission_type = get_term_meta( $category_id, '_wcv_commission_type', true );
		}

		$commission_args = array();
		$product_sales   = $product->get_total_sales();

		if ( $product_commission_type ) {
			if ( 'product_sales' === $product_commission_type ) {
				$tier = $this->get_tier( $product_sales, 'product', 'product_sales', $product_id );

				$commission_args['type']    = $tier['type'];
				$commission_args['percent'] = (float) $tier['percent'];
				$commission_args['amount']  = (float) $tier['amount'];
				$commission_args['fee']     = (float) $tier['fee'];
			} else {
				$commission_args['type']    = $product_commission_type;
				$commission_args['percent'] = (float) get_post_meta( $product_id, 'wcv_commission_percent', true );
				$commission_args['amount']  = (float) get_post_meta( $product_id, 'wcv_commission_amount', true );
				$commission_args['fee']     = (float) get_post_meta( $product_id, 'wcv_commission_fee', true );
			}
		} elseif ( $category_commission_type ) {
			$commission_args['type']    = $category_commission_type;
			$commission_args['percent'] = (float) get_term_meta( $category_id, '_wcv_commission_percent', true );
			$commission_args['amount']  = (float) get_term_meta( $category_id, '_wcv_commission_amount', true );
			$commission_args['fee']     = (float) get_term_meta( $category_id, '_wcv_commission_fee', true );
		} elseif ( $store_commission_type ) {
			if ( in_array( $store_commission_type, array( 'product_sales', 'vendor_sales', 'product_price' ), true ) ) {
				if ( 'product_sales' === $store_commission_type ) {
					$tier = $this->get_tier( $product_sales, 'vendor', 'product_sales', $vendor_id );
				} elseif ( 'vendor_sales' === $store_commission_type ) {
					$vendor_sales = WCVendors_Pro_Vendor_Controller::get_vendor_sales_count( $vendor_id );
					$tier         = $this->get_tier( $vendor_sales, 'vendor', 'vendor_sales', $vendor_id );
				} else {
					$tier = $this->get_tier( $product_price, 'vendor', 'product_price', $vendor_id );
				}

				$commission_args['type']    = $tier['type'];
				$commission_args['percent'] = (float) $tier['percent'];
				$commission_args['amount']  = (float) $tier['amount'];
				$commission_args['fee']     = (float) $tier['fee'];
			} else {
				$commission_args['type']    = $store_commission_type;
				$commission_args['percent'] = (float) get_user_meta( $vendor_id, '_wcv_commission_percent', true );
				$commission_args['amount']  = (float) get_user_meta( $vendor_id, '_wcv_commission_amount', true );
				$commission_args['fee']     = (float) get_user_meta( $vendor_id, '_wcv_commission_fee', true );
			}
		} else { // Global Commissions.
			$global_commission_type = get_option( 'wcvendors_commission_type', 'percent' );
			if ( in_array( $global_commission_type, array( 'product_sales', 'vendor_sales', 'product_price' ), true ) ) {
				if ( 'product_sales' === $global_commission_type ) {
					$tier = $this->get_tier( $product_sales, 'global', 'product_sales' );
				} elseif ( 'vendor_sales' === $global_commission_type ) {
					$tier = $this->get_tier( $vendor_sales, 'global', 'vendor_sales' );
				} else {
					$tier = $this->get_tier( $product_price, 'global', 'product_price' );
				}

				$commission_args['type']    = $tier['type'];
				$commission_args['percent'] = (float) $tier['percent'];
				$commission_args['amount']  = (float) $tier['amount'];
				$commission_args['fee']     = (float) $tier['fee'];
			} else {
				$commission_args['type']    = $global_commission_type;
				$commission_args['percent'] = (float) get_option( 'wcvendors_vendor_commission_rate', '' );
				$commission_args['amount']  = (float) get_option( 'wcvendors_commission_amount', '' );
				$commission_args['fee']     = (float) get_option( 'wcvendors_commission_fee', '' );
			}
		}

		$commission_args = apply_filters( 'wcvendors_commission_args', $commission_args, $product_id, $vendor_id );

		// Assumption that coupon codes are unique, if created by vendors they are.
		$coupons = $order->get_items( 'coupon' );

		$discount_amount = 0;

		if ( ! empty( $coupons ) ) {

			foreach ( $coupons as $coupon ) {

				$coupon_obj    = new WC_Coupon( $coupon['name'] );
				$coupon_id     = $coupon_obj->get_id();
				$coupon_owner  = WCVendors_Pro_Vendor_Controller::get_vendor_from_object( $coupon_id );
				$product_owner = WCVendors_Pro_Vendor_Controller::get_vendor_from_object( $product_id );

				$coupon_user = get_userdata( $coupon_owner );

				// Is this coupon valid for this product?
				if ( ! $coupon_obj->is_valid_for_product( $product ) ) {
					continue;
				}

				// This checks that the coupon is created by the product owner OR the site administrator.
				if ( $coupon_owner == $product_owner || in_array( 'administrator', $coupon_user->roles ) ) {
					// Reset the product price to be the full price for calculations.
					if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
						$product_price = $item->get_subtotal();
					} elseif ( is_array( $item ) && array_key_exists( 'line_total', $item ) ) {
						$product_price = $item['line_total'];
					} else {
						$product_price = $product->get_price();
					}

					$discount_amount += $coupon_obj->get_discount_amount( $product_price );

					// Apply the coupon after the commission is taken out.
					if ( 'yes' === get_option( 'wcvendors_commission_coupon_action', 'no' ) ) {
						$product_price = $product_price - ( $discount_amount * $qty );
					}
				}
			}
		}

		switch ( $commission_args['type'] ) {
			case 'fixed':
				$commission = $commission_args['amount'] * $qty;
				$commission = round( $commission, 2 );
				break;
			case 'fixed_fee':
				$commission = round( $commission_args['amount'] - $commission_args['fee'], 2 );
				break;
			case 'percent':
				$commission = $product_price * ( $commission_args['percent'] / 100 );
				$commission = round( $commission, 2 );
				break;
			case 'percent_fee':
				$commission = $product_price * ( $commission_args['percent'] / 100 );
				$commission = round( $commission - $commission_args['fee'], 2 );
				break;
			default:
				$commission = round( $commission_args['amount'], 2 );
				break;
		}

		// If the coupon amount is higher than the commission amount then set it to 0.
		if ( $commission < 0 ) {
			$commission = 0;
		}

		return apply_filters( 'wcv_process_commission', $commission, $product_id, $product_price, $order, $qty, $item );

	} // process_commission()


	/**
	 * Get a commission tier based on the condition value
	 *
	 * @param   float   $value The value yo calculate commission from.
	 * @param   string  $from  Where to get the tiers from, this is where the data is saved.
	 *                         Possible values 'global', 'vendor' and product.
	 * @param   string  $key   The key of the tiers to return.
	 * @param   integer $id The user_id or post.
	 * @return  array
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function get_tier( $value, $from = 'global', $key = 'vendor_sales', $id = 0 ) {
		$tiers = $this->get_commission_tiers( $from, $key, $id );
		if ( empty( $tiers ) ) {
			return array();
		};
		uasort(
			$tiers,
			function ( $a, $b ) {
				return $a['value'] > $b['value'];
			}
		);
		foreach ( $tiers as $tier ) {
			if ( $value < $tier['value'] && 'less_than' === $tier['rule'] ) {
				return $tier;
			} elseif ( $tier['value'] >= $value && 'up_to' === $tier['rule'] ) {
				return $tier;
			} elseif ( $value > $tier['value'] && 'more_than' === $tier['rule'] ) {
				return $tier;
			}
		}
		return array();
	}

	/**
	 * Search an array using multiple values
	 *
	 * @param array $array The array to search
	 * @param array $pairs Array of values to look for
	 *
	 * @return  array
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public static function search_array( $array, $pairs ) {
		$found = array();
		foreach ( $array as $array_key => $array_value ) {
			$occurances = 0;
			foreach ( $pairs as $pair_key => $pair_val ) {
				if ( array_key_exists( $pair_key, $array_value ) && $array_value[ $pair_key ] == $pair_val ) {
					$occurances ++;
				}
			}

			if ( $occurances == count( $pairs ) ) {
				$found[ $array_key ] = $array_value;
			}
		}

		return $found;
	}

	/**
	 *  Save the commission detail for the post object (Product | Store)
	 *
	 * @param int $post_id post_id being saved
	 *
	 * @since 1.0.0
	 */
	public function save_commission_details( $post_id ) {

		if ( isset( $_POST['wcv_commission_type'] ) && '' !== $_POST['wcv_commission_type'] && 'product_sales' !== $_POST['wcv_commission_type'] ) {
			update_post_meta( $post_id, 'wcv_commission_type', $_POST['wcv_commission_type'] );

			// wcv_commission_percent
			if ( isset( $_POST['wcv_commission_percent'] ) && '' !== $_POST['wcv_commission_percent'] ) {
				update_post_meta( $post_id, 'wcv_commission_percent', (float) $_POST['wcv_commission_percent'] );
			} else {
				delete_post_meta( $post_id, 'wcv_commission_percent' );
			}

			// wcv_commission_amount
			if ( isset( $_POST['wcv_commission_amount'] ) && '' !== $_POST['wcv_commission_amount'] ) {
				update_post_meta( $post_id, 'wcv_commission_amount', (float) $_POST['wcv_commission_amount'] );
			} else {
				delete_post_meta( $post_id, 'wcv_commission_amount' );
			}

			// wcv_commission_fee
			if ( isset( $_POST['wcv_commission_fee'] ) && '' !== $_POST['wcv_commission_fee'] ) {
				update_post_meta( $post_id, 'wcv_commission_fee', (float) $_POST['wcv_commission_fee'] );
			} else {
				delete_post_meta( $post_id, 'wcv_commission_fee' );
			}
		} else {
			delete_post_meta( $post_id, 'wcv_commission_type' );
			delete_post_meta( $post_id, 'wcv_commission_percent' );
			delete_post_meta( $post_id, 'wcv_commission_amount' );
			delete_post_meta( $post_id, 'wcv_commission_fee' );
		}

		$this->save_commission_tiers( 'product', $post_id );

	} //save_commission_details()

	/**
	 * Save commission tiers for product, vendor or global settings
	 *
	 * @param   string  $where
	 * @param   integer $id
	 *
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function save_commission_tiers( $save_where = 'global', $id = 0 ) {

		switch ( $save_where ) {
			case 'global':
				$commission_type_index = 'wcvendors_commission_type';
				break;
			case 'product':
				$commission_type_index = 'wcv_commission_type';
				break;
			case 'vendor':
				$commission_type_index = '_wcv_commission_type';
				break;
			default:
				$commission_type_index = 'wcvendors_commission_type';
				break;
		}

		if ( isset( $_POST['wcv_commission_tiers_names'] ) ) {
			$commission_tiers_names    = isset( $_POST['wcv_commission_tiers_names'] ) ? $_POST['wcv_commission_tiers_names'] : array();
			$commission_tiers_rules    = isset( $_POST['wcv_commission_tiers_rules'] ) ? $_POST['wcv_commission_tiers_rules'] : array();
			$commission_tiers_values   = isset( $_POST['wcv_commission_tiers_values'] ) ? $_POST['wcv_commission_tiers_values'] : array();
			$commission_tiers_types    = isset( $_POST['wcv_commission_tiers_types'] ) ? $_POST['wcv_commission_tiers_types'] : array();
			$commission_tiers_amounts  = isset( $_POST['wcv_commission_tiers_amounts'] ) ? $_POST['wcv_commission_tiers_amounts'] : array();
			$commission_tiers_percents = isset( $_POST['wcv_commission_tiers_percents'] ) ? $_POST['wcv_commission_tiers_percents'] : array();
			$commission_tiers_fees     = isset( $_POST['wcv_commission_tiers_fees'] ) ? $_POST['wcv_commission_tiers_fees'] : array();
			$tier_count                = count( $commission_tiers_names );
			$commission_type           = $_POST[ $commission_type_index ];
			$tier_rows                 = array();
			$tiers                     = array();

			for ( $i = 0; $i < $tier_count; $i ++ ) {
				if ( $commission_tiers_names[ $i ] != '' ) {
					$name    = array_key_exists( $i, $commission_tiers_names ) ? wc_clean( $commission_tiers_names[ $i ] ) : '';
					$rule    = array_key_exists( $i, $commission_tiers_rules ) ? wc_clean( $commission_tiers_rules[ $i ] ) : '';
					$value   = array_key_exists( $i, $commission_tiers_values ) ? wc_format_decimal( $commission_tiers_values[ $i ] ) : 0;
					$type    = array_key_exists( $i, $commission_tiers_types ) ? wc_clean( $commission_tiers_types[ $i ] ) : '';
					$amount  = array_key_exists( $i, $commission_tiers_amounts ) ? wc_format_decimal( $commission_tiers_amounts[ $i ] ) : 0;
					$percent = array_key_exists( $i, $commission_tiers_percents ) ? wc_format_decimal( $commission_tiers_percents[ $i ] ) : 0;
					$fee     = array_key_exists( $i, $commission_tiers_fees ) ? wc_format_decimal( $commission_tiers_fees[ $i ] ) : 0;

					$tier_rows[ $i ] = array(
						'name'    => $name,
						'rule'    => $rule,
						'type'    => $type,
						'value'   => $value,
						'amount'  => $amount,
						'percent' => $percent,
						'fee'     => $fee,
					);
				}
			}

			$tier_rows = apply_filters( "wcv_{$save_where}_commission_tiers", $tier_rows );

			if ( empty( $tier_rows ) ) {
				return;
			}

			$tiers[ $commission_type ] = $tier_rows;

			if ( 'global' === $save_where && $id == 0 ) {
				update_option( 'wcv_global_commission_tiers', $tiers );
			} elseif ( 'vendor' === $save_where && $id > 0 ) {
				update_user_meta( $id, 'wcv_vendor_commission_tiers', $tiers );
			} elseif ( 'product' === $save_where && $id > 0 ) {
				update_post_meta( $id, 'wcv_commission_type', $_POST['wcv_commission_type'] );
				update_post_meta( $id, 'wcv_product_commission_tiers', $tiers );
			}

			do_action( "after_save_{$save_where}_commission_tiers", $id, $tiers );
		}
	}

	/**
	 * Save commission tiers for global commission settings
	 *
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function save_commission_tiers_settings() {
		$this->save_commission_tiers( 'global' );
	}

	/**
	 *  Disable the product commission tab enabled by free
	 *
	 * @since    1.0.0
	 */
	public function update_product_meta() {
		return false;
	} // update_product_meta()

	/**
	 *  Add the product commission tab
	 *
	 * @since    1.0.0
	 */
	public function add_commission_tab() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		include apply_filters( 'wcvendors_pro_add_commission_tab_path', 'partials/product/wcvendors-pro-product-meta-tab.php' );

	} //add_commission_tab()

	/**
	 * Add the panel to the product commission tab
	 *
	 * @since    1.0.0
	 */
	public function add_commission_panel() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		global $post;

		$commission_type    = get_post_meta( $post->ID, 'wcv_commission_type', true );
		$commission_percent = get_post_meta( $post->ID, 'wcv_commission_percent', true );
		$commission_amount  = get_post_meta( $post->ID, 'wcv_commission_amount', true );
		$commission_fee     = get_post_meta( $post->ID, 'wcv_commission_fee', true );

		include apply_filters( 'wcvendors_pro_add_commission_panel_path', 'partials/product/wcvendors-pro-commission-panel.php' );

	} //add_commission_panel()

	/**
	 * Add commission tiers to product commission panel
	 *
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function add_vendor_commission_tiers_panel( $user ) {

		$fields = array(
			array(
				'title'       => sprintf( __( '%s Sales', 'wcvendors-pro' ), wcv_get_vendor_name() ),
				'desc'        => sprintf( __( 'Commissions based on %s sales', 'wcvendors-pro' ), wcv_get_vendor_name() ),
				'id'          => 'wcvendors_commission_tier_vendor_sales',
				'key'         => 'vendor_sales',
				'value_label' => __( 'Sales', 'wcvendors-pro' ),
				'type'        => 'include',
				'file'        => WCV_PRO_ABSPATH_ADMIN . 'settings/partials/tiered-commissions.php',
			),
			array(
				'title'       => __( 'Product Price', 'wcvendors-pro' ),
				'desc'        => __( 'Define the commission tiers based on the product price.', 'wcvendors-pro' ),
				'id'          => 'wcvendors_commission_tier_product_price',
				'type'        => 'include',
				'key'         => 'product_price',
				'value_label' => __( 'Price', 'wcvendors-pro' ),
				'file'        => WCV_PRO_ABSPATH_ADMIN . 'settings/partials/tiered-commissions.php',
			),
			array(
				'title'       => __( 'Product Sales', 'wcvendors-pro' ),
				'desc'        => __( 'Commissions will be applied based on product sales.' ),
				'id'          => 'wcvendors_commission_tier_product_sales',
				'type'        => 'include',
				'key'         => 'product_sales',
				'value_label' => __( 'Sales', 'wcvendors-pro' ),
				'file'        => WCV_PRO_ABSPATH_ADMIN . 'settings/partials/tiered-commissions.php',
			),
		);

		$fields = apply_filters( 'wcv_commission_tiers_panel_fields', $fields );

		foreach ( $fields as $field_details ) {
			$commission_tiers = self::get_commission_tiers( 'vendor', $field_details['key'], $user->ID );
			?>
			<tr class="wcv_form_fields_table" id="<?php echo $field_details['id']; ?>">
				<td colspan="2">
					<?php include WCV_PRO_ABSPATH_ADMIN . 'settings/partials/tiered-commissions.php'; ?>
				</td>
			</tr>
			<?php
		}
	}

	public function add_product_commission_tiers_panel() {
		global $post;

		$field_details    = array(
			'title'       => '',
			'desc'        => __( 'Commissions will be applied based on product sales.', 'wcvendors-pro' ),
			'id'          => 'wcvendors_commission_tier_product_sales',
			'type'        => 'include',
			'key'         => 'product_sales',
			'value_label' => __( 'Sales', 'wcvendors-pro' ),
			'file'        => WCV_PRO_ABSPATH_ADMIN . 'settings/partials/tiered-commissions.php',
		);
		$commission_tiers = self::get_commission_tiers( 'product', $field_details['key'], $post->ID );
		?>
		<div class="wcv_form_fields_table" id="<?php echo esc_attr( $field_details['id'] ); ?>">
			<?php include WCV_PRO_ABSPATH_ADMIN . 'settings/partials/tiered-commissions.php'; ?>
		</div>
		<?php
	}

	/**
	 * Save the data for the product
	 *
	 * @param    int $post_id post_id being saved
	 *
	 * @since    1.0.0
	 */
	public function save_commission_panel( $post_id ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$this->save_commission_details( $post_id );

	} // save_commission_panel()

	/**
	 *  Add new commission interface to user edit screen
	 *
	 * @since    1.0.0
	 */
	public function store_commission_meta_fields( $user ) {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! WCV_Vendors::is_vendor( $user->ID ) ) {
			return;
		}

		// Get the default commission rate
		$free_override_commission = get_user_meta( $user->ID, 'pv_custom_commission_rate', true );

		$commission_type    = get_user_meta( $user->ID, '_wcv_commission_type', true );
		$commission_percent = get_user_meta( $user->ID, '_wcv_commission_percent', true );
		$commission_amount  = get_user_meta( $user->ID, '_wcv_commission_amount', true );
		$commission_fee     = get_user_meta( $user->ID, '_wcv_commission_fee', true );

		include apply_filters( 'wcvendors_pro_store_commission_meta_fields_path', 'partials/vendor/wcvendors-pro-vendor-commission-fields.php' );

	} // store_commission_meta_fields()

	/**
	 *  Save the store commission fields on the user edit screen.
	 *
	 * @param    int $post_id post_id being saved
	 *
	 * @since    1.0.0
	 */
	public function store_commission_meta_fields_save( $vendor_id ) {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! WCV_Vendors::is_vendor( $vendor_id ) ) {
			return;
		}

		if ( isset( $_POST['_wcv_commission_type'] ) && '' !== $_POST['_wcv_commission_type'] ) {

			update_user_meta( $vendor_id, '_wcv_commission_type', wc_clean( $_POST['_wcv_commission_type'] ) );

			// _wcv_commission_percent
			if ( isset( $_POST['_wcv_commission_percent'] ) && '' !== $_POST['_wcv_commission_percent'] ) {
				update_user_meta( $vendor_id, '_wcv_commission_percent', (float) $_POST['_wcv_commission_percent'] );
			} else {
				delete_user_meta( $vendor_id, '_wcv_commission_percent' );
			}

			// _wcv_commission_fee
			if ( isset( $_POST['_wcv_commission_fee'] ) && '' !== $_POST['_wcv_commission_fee'] ) {
				update_user_meta( $vendor_id, '_wcv_commission_fee', (float) $_POST['_wcv_commission_fee'] );
			} else {
				delete_user_meta( $vendor_id, '_wcv_commission_fee' );
			}

			// _wcv_commission_amount
			if ( isset( $_POST['_wcv_commission_amount'] ) && '' !== $_POST['_wcv_commission_amount'] ) {
				update_user_meta( $vendor_id, '_wcv_commission_amount', (float) $_POST['_wcv_commission_amount'] );
			} else {
				delete_user_meta( $vendor_id, '_wcv_commission_amount' );
			}
		} else {
			delete_user_meta( $vendor_id, '_wcv_commission_type' );
			delete_user_meta( $vendor_id, '_wcv_commission_percent' );
			delete_user_meta( $vendor_id, '_wcv_commission_amount' );
			delete_user_meta( $vendor_id, '_wcv_commission_fee' );
		}

		$this->save_commission_tiers( 'vendor', $vendor_id );

	} //store_commission_meta_fields_save()

	/**
	 *  Commission types
	 *
	 * @param    int $post_id post_id being saved
	 *
	 * @since    1.0.0
	 */
	public static function commission_types() {

		return apply_filters(
			'wcv_commission_types',
			array(
				'fixed'       => __( 'Fixed', 'wcvendors-pro' ),
				'fixed_fee'   => __( 'Fixed + fee', 'wcvendors-pro' ),
				'percent'     => __( 'Percentage', 'wcvendors-pro' ),
				'percent_fee' => __( 'Percentage + fee', 'wcvendors-pro' ),
			)
		);

	} // commission_types()

	/**
	 * Get the commission rules
	 *
	 * @return  array
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public static function commission_rules() {
		$rules = apply_filters(
			'wcv_commission_rules',
			array(
				'less_than' => __( 'Less than', 'wcvendors-pro' ),
				'up_to'     => __( 'Up to', 'wcvendors-pro' ),
				'more_than' => __( 'More than', 'wcvendors-pro' ),
			)
		);

		return $rules;
	}

	/**
	 * Get commission tiers
	 *
	 * @param   string  $key       Return tiers for this commission type if given.
	 *                             Expected vendor_sales|product_sales|product_price
	 * @param   string  $from
	 * @param   integer $id
	 *
	 * @return  array   $tiers
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public static function get_commission_tiers( $from = 'global', $key = '', $id = 0 ) {

		$_tiers = array();
		$tiers  = array();
		if ( 'global' === $from ) {
			$_tiers = get_option( 'wcv_global_commission_tiers', array() );
		} elseif ( 'vendor' === $from && $id > 0 ) {
			$_tiers = get_user_meta( $id, 'wcv_vendor_commission_tiers', true );
		} elseif ( 'product' === $from && $id > 0 ) {
			$_tiers = get_post_meta( $id, 'wcv_product_commission_tiers', true );
		}

		if ( in_array(
			 $key,
			array(
				'vendor_sales',
				'product_sales',
				'product_price',
			)
			) && ! empty( $_tiers[ $key ] ) ) {
			$tiers = $_tiers[ $key ];
		}

		return apply_filters( "wcv_get_{$from}_commission_tiers", $tiers, $key, $id );
	}

	/**
	 * Get shipping due for vendor shipping this uses the new package stored
	 * vendor_costs in the order. No need for secondary calculations
	 *
	 * @param   int $shipping_costs
	 *
	 * @since   1.4.0
	 * @version 1.5.0
	 */
	public function get_shipping_due_from_order( $shipping_costs, $order_id, $order_item, $vendor_id, $product_id ) {

		$item_shipping_cost = 0;
		$tax                = 0;
		$settings           = get_option( 'woocommerce_wcv_pro_vendor_shipping_settings', wcv_get_default_vendor_shipping() );
		$taxable            = $settings['tax_status'];
		$shipping_costs     = array(
			'amount' => 0,
			'tax'    => 0,
		);
		$order              = new WC_Order( $order_id );
		$shipping_items     = $order->get_items( 'shipping' );
		$_product           = wc_get_product( $product_id );
		$tax_class          = $order_item->get_tax_class();

		if ( $_product && $_product->needs_shipping() && ! $_product->is_virtual() ) {

			if ( ! empty( $shipping_items ) ) {

				foreach ( $shipping_items as $shipping_item ) {

					$shipping_meta_data = $shipping_item->get_meta_data();

					foreach ( $shipping_meta_data as $meta ) {

						// Vendor Shipping
						if ( 'vendor_costs' === $meta->key ) {

							$package_cost = $meta->value;

							foreach ( $package_cost['items'] as $key => $shipping_details ) {

								if ( $product_id == $shipping_details['product_id'] ) {
									$item_shipping_cost = $shipping_details['shipping_cost'];
									$tax                = ( 'taxable' === $taxable ) ? WCV_Shipping::calculate_shipping_tax( $item_shipping_cost, $order, $tax_class ) : 0;
								}
							}
						}

						// Shipping Zones
						// if ( '_vendor_id' === $meta->key && $vendor_id == $meta->value ){

						// $item_shipping_cost = $shipping_item->get_total();
						// $tax = $shipping_item->get_total_tax();

						// }
					}
				}

				$shipping_costs['amount'] = $item_shipping_cost;
				$shipping_costs['tax']    = $tax;

			}
		}

		return $shipping_costs;

	} // get_shipping_due_from_order()

	/**
	 * get_fee function from woocommerce
	 *
	 * @param mixed $fee
	 * @param mixed $total
	 *
	 * @return float
	 */
	public function get_fee( $fee, $total ) {

		if ( strstr( $fee, '%' ) ) {
			$fee = ( $total / 100 ) * str_replace( '%', '', $fee );
		}

		return $fee;
	} // get_fee()

	/**
	 * Import the commission overrides for vendors
	 *
	 * @since  1.3.6
	 * @access public
	 * @todo   delete the free meta keys
	 */
	public static function import_vendor_commission_overrides() {

		$all_vendor_ids = get_users(
			array(
				'role'   => 'vendor',
				'fields' => 'ID',
			)
		);

		if ( isset( $all_vendor_ids ) ) {

			foreach ( $all_vendor_ids as $vendor_id ) {

				$store_free_commission = get_user_meta( $vendor_id, 'pv_custom_commission_rate', true );

				// There is a free commission override. Import it into pro
				if ( isset( $store_free_commission ) ) {
					update_user_meta( $vendor_id, '_wcv_commission_type', 'percent' );
					update_user_meta( $vendor_id, '_wcv_commission_percent', $store_free_commission );
				}
			}

			echo '<div class="updated inline"><p>' . sprintf( __( '%s commission overrides successfully imported.', 'wcvendors-pro' ), wcv_get_vendor_name( true, true ) ) . '</p></div>';

		}

	} // import_vendor_commission_overrides()

	/**
	 * Import the commission overrides for products
	 *
	 * @since  1.3.6
	 * @access public
	 * @todo   delete the free meta keys
	 */
	public static function import_product_commission_overrides() {

		$all_products = get_posts( array( 'post_type' => 'product' ) );

		if ( isset( $all_products ) ) {

			foreach ( $all_products as $product ) {

				$free_product_override_commission = get_post_meta( $product->ID, 'pv_commission_rate', true );

				if ( isset( $free_product_override_commission ) ) {
					update_post_meta( $product->ID, 'wcv_commission_type', 'percent' );
					update_post_meta( $product->ID, 'wcv_commission_percent', $free_product_override_commission );
				}
			}

			echo '<div class="updated inline"><p>' . __( 'Product commission overrides successfully imported.', 'wcvendors-pro' ) . '</p></div>';

		}

	} // import_product_commission_overrides()

	/**
	 * Add action to bulk calculate commissions on order list
	 *
	 * @param   array $actions
	 *
	 * @return  array $actions Modified bulk actions
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	function add_bulk_order_commissions_action( $actions ) {

		$actions['wcv_bulk_order_commissions'] = __( 'Recalculate Commissions', 'wcvendors-pro' );

		return $actions;
	} // add_bulk_order_commissions_action()

	/**
	 * Add action to calculate commissions on single order edit screen
	 *
	 * @param [type] $actions
	 *
	 * @return void
	 * @since
	 * @version
	 */
	public function add_order_commissions_action( $actions ) {

		$actions['wcv_order_commissions'] = __( 'Recalculate Commissions', 'wcvendors-pro' );

		return $actions;
	} // add_order_commissions_action()

	/**
	 * Calculate order commissions
	 *
	 * @return  void
	 * @since   1.6.0
	 * @version 1.7.3
	 */
	public function calculate_bulk_order_commissions() {

		if ( empty( $_REQUEST['post'] ) ) {
			return;
		}

		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}

		$recalculate_order_statuses = apply_filters( 'wcv_recalculate_commission_complete_order_statuses', array( 'completed', 'processing' ) );

		foreach ( $_REQUEST['post'] as $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! in_array( $order->get_status(), $recalculate_order_statuses ) ) {
				continue;
			}

			$this->calculate_order_commissions( $order_id );

			do_action( 'wcv_order_commissions_calculated', $order );
		}

		do_action( 'wcv_bulk_order_commissions_calculated', $_REQUEST['post'] );

		// of course using add_query_arg() is not required, you can build your URL inline
		$location = add_query_arg(
			array(
				'post_type'              => 'shop_order',
				'commissions_calculated' => 1,
				'changed'                => count( $_REQUEST['post'] ),
				'ids'                    => join( $_REQUEST['post'], ',' ),
				'post_status'            => 'all',
			),
			'edit.php'
		);

		wp_redirect( admin_url( $location ) );
		exit;
	} // calculate_bulk_order_commissions()

	/**
	 * Calculate single order commissions
	 *
	 * @param   mixed $order
	 *
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function calculate_order_commissions( $order ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		} elseif ( is_array( $order ) ) {
			$order = wc_get_order( $order['order_id'] );
		}

		$give_tax      = 'yes' == get_option( 'wcvendors_vendor_give_taxes', 'no' ) ? true : false;
		$give_shipping = 'yes' == get_option( 'wcvendors_vendor_give_shipping', 'no' ) ? true : false;

		foreach ( $order->get_items() as $item ) {

			$total_shipping = 0;
			$total_tax      = 0;
			$shipping_costs = array();

			$product    = wc_get_product( $item->get_product_id() );
			$order_id   = $item->get_order_id();
			$product_id = ( $item->get_variation_id() ) ? $item->get_variation_id() : $item->get_product_id();
			$qty        = $item->get_quantity();

			$product_price = $item->get_total();

			$vendor_id = get_post_field( 'post_author', $product->get_id() );
			$is_vendor = WCV_Vendors::is_vendor( $vendor_id );

			$commission = WCV_Commission::calculate_commission( $product_price, $product_id, $order, $qty, $item );
			$commission = is_numeric( $commission ) ? $this->process_commission( $commission, $product_id, $product_price, $order, $qty, $item ) : 0;
			$line_tax   = ! empty( $item['line_tax'] ) ? (float) $item['line_tax'] : 0;

			if ( 'no' == get_option( 'woocommerce_calc_shipping' ) ) {
				$shipping_amount = 0;
				$shipping_tax    = 0;
			} else {
				$shipping_costs  = $this->get_shipping_due_from_order( $shipping_costs, $order_id, $item, $vendor_id, $product_id );
				$shipping_amount = $shipping_costs['amount'];
				$shipping_tax    = $shipping_costs['tax'];
			}

			$total_tax = ( $product->is_taxable() ) ? (float) $line_tax + (float) $shipping_tax : 0;

			if ( $is_vendor ) {

				$give_tax_override      = get_user_meta( $vendor_id, 'wcv_give_vendor_tax', true );
				$give_shipping_override = get_user_meta( $vendor_id, 'wcv_give_vendor_shipping', true );

				if ( $give_tax_override ) {
					$give_tax = true;
				}
				if ( $give_shipping_override ) {
					$give_shipping = true;
				}

				$total_shipping = $give_shipping ? $shipping_amount : 0;
				$total_tax      = $give_tax ? $total_tax : 0;
			}

			$data = array(
				'vendor_id'      => $vendor_id,
				'order_id'       => $item->get_order_id(),
				'product_id'     => $product_id,
				'total_due'      => $commission,
				'qty'            => $qty,
				'total_shipping' => $total_shipping,
				'tax'            => $total_tax,
				'time'           => date_i18n( 'Y-m-d H:i:s', strtotime( $order->get_date_created() ) ),
			);

			$this->update_commission( $data );

			do_action( 'wcv_order_commissions_calculated', $order );
		}
	} // calculate_order_commissions()

	/**
	 * Update/Insert commission
	 *
	 * @param array $data
	 *
	 * @return  void
	 * @since   1.6.0
	 * @version 1.7.0
	 */
	public function update_commission( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'pv_commission';

		$where = array(
			'vendor_id'  => $data['vendor_id'],
			'order_id'   => $data['order_id'],
			'product_id' => $data['product_id'],
		);

		// Is the commission already paid?
		$count_paid = WCV_Commission::check_commission_status( $where, 'paid' );
		$count_due  = WCV_Commission::check_commission_status( $where, 'due' );

		if ( 0 == $count_paid ) {
			$format = array( '%d', '%d', '%d', '%f', '%d', '%f', '%f', '%s', '%s' );
			$update = $wpdb->update( $table, $data, $where, $format );
			if ( ! $update && $count_due < 1 ) {
				$insert = $wpdb->insert( $table, $data, $format );
			}
		}
	} // update_commission

	/**
	 * Add admin notice after calculating order commissions
	 *
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function commissions_calculated_notice() {

		global $pagenow, $typenow;

		if ( $typenow == 'shop_order'
			 && $pagenow == 'edit.php'
			 && isset( $_REQUEST['commissions_calculated'] )
			 && $_REQUEST['commissions_calculated'] == 1
			 && isset( $_REQUEST['changed'] ) ) {

			$message = sprintf( _n( 'Order commissions recalculated.', 'Commissions calculated for %s orders.', $_REQUEST['changed'] ), number_format_i18n( $_REQUEST['changed'] ) );
			echo "<div class=\"updated\"><p>{$message}</p></div>";
		}
	} // commissions_calculated_notice()

	/**
	 * Disable the free commission input on the users edit screen.
	 *
	 * @return bool
	 */
	public function disable_free_commission_user() {
		return false;
	} // disable_free_commission_user()
}
