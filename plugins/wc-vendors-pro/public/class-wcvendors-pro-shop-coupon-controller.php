<?php

/**
 * The WCVendors Pro Coupon Controller class
 *
 * This is the coupon controller class
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCVendors_Pro_Shop_Coupon_Controller {

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
	private $debug;

	/**
	 * Is the plugin base directory
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $base_dir string path for the plugin directory
	 */
	private $base_dir;

	/**
	 * Max number of pages for pagination
	 *
	 * @since    1.2.4
	 * @access   public
	 * @var      int $max_num_pages interger for max number of pages for the query
	 */
	public $max_num_pages;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wcvendors_pro The name of the plugin.
	 * @param      string $version       The version of this plugin.
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {

		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;
		$this->debug         = $debug;
		$this->base_dir      = plugin_dir_path( dirname( __FILE__ ) );

		// Add Author to shop coupon
		add_post_type_support( 'shop_coupon', 'author' );

	}

	/**
	 *  Process the form submission from the front end.
	 *
	 * @since    1.0.0
	 */
	public function process_submit() {

		if ( ! isset( $_POST['wcv_save_coupon'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['wcv_save_coupon'], 'wcv-save-coupon' ) ) {
			return;
		}

		// Requires a Coupon Code
		if ( ! isset( $_POST['_wcv_coupon_post_title'] ) || '' === $_POST['_wcv_coupon_post_title'] ) {
			wc_add_notice( __( 'Please enter a coupon code', 'wcvendors-pro' ), 'error' );

			return null;
		}

		$coupon_id = (int) ( $_POST['_wcv_coupon_post_id'] );

		if ( $this->coupon_exists( $_POST['_wcv_coupon_post_title'] ) && ! $coupon_id ) {
			wc_add_notice( __( 'This coupon code exists. ', 'wcvendors-pro' ), 'error' );

			return null;
		}

		$coupon_args = array(
			'post_title'   => $_POST['_wcv_coupon_post_title'],
			'post_excerpt' => $_POST['_wcv_coupon_post_excerpt'],
			'post_author'  => get_current_user_id(),
			'post_type'    => 'shop_coupon',
			'post_status'  => 'publish',
		);

		// Create the coupon post type or update it
		if ( 0 !== $coupon_id ) {
			// Update the coupon
			$coupon_args['ID'] = $coupon_id;
			$coupon            = wp_update_post( $coupon_args, true );

		} else {
			// Attempts to create the new product
			$coupon = wp_insert_post( $coupon_args, true );
		}

		$all_vendor_product_ids = array_filter( array_map( 'intval', WCVendors_Pro_Vendor_Controller::get_products_by_id( get_current_user_id() ) ) );

		// If the user doesn't select apply to all products and hasn't selected any product ids then, auto apply it to all products.
		if (
			isset( $_POST['_wcv_coupon_post_meta_product_ids'] )
			 && $_POST['_wcv_coupon_post_meta_product_ids']
		) {
			if ( ( isset( $_POST['_wcv_coupon_post_meta_apply_to_all_products'] ) && $_POST['_wcv_coupon_post_meta_apply_to_all_products'] ) ) {
				$product_ids = $all_vendor_product_ids;
				update_post_meta( $coupon, 'apply_to_all_products', 'yes' );
			} else {
				$product_ids = isset( $_POST['_wcv_coupon_post_meta_product_ids'] ) ? $_POST['_wcv_coupon_post_meta_product_ids'] : array();
				update_post_meta( $coupon, 'apply_to_all_products', 'no' );
			}
		} else {
			$product_ids = $all_vendor_product_ids;
			update_post_meta( $coupon, 'apply_to_all_products', 'yes' );
		}

		// Free shipping
		if ( isset( $_POST['_wcv_coupon_post_meta_free_shipping'] ) && 'yes' == $_POST['_wcv_coupon_post_meta_free_shipping'] ) {
			update_post_meta( $coupon, 'vendor_free_shipping', 'yes' );
		} else {
			delete_post_meta( $coupon, 'vendor_free_shipping' );
		}

		$coupon_object = new WC_Coupon( $coupon );
		$coupon_object->set_props(
			apply_filters(
				'wcv_coupon_props',
				array(
					'code'                   => $coupon_args['post_title'],
					'discount_type'          => wc_clean( $_POST['_wcv_coupon_post_meta_discount_type'] ),
					'amount'                 => wc_format_decimal( $_POST['_wcv_coupon_post_meta_coupon_amount'] ),
					'date_expires'           => wc_clean( $_POST['_wcv_coupon_post_meta_expiry_date'] ),
					'individual_use'         => isset( $_POST['_wcv_coupon_post_meta_individual_use'] ),
					'product_ids'            => $product_ids,
					'excluded_product_ids'   => isset( $_POST['_wcv_coupon_post_meta_exclude_product_ids'] ) ? $_POST['_wcv_coupon_post_meta_exclude_product_ids'] : array(),
					'usage_limit'            => absint( $_POST['_wcv_coupon_post_meta_usage_limit'] ),
					'usage_limit_per_user'   => absint( $_POST['_wcv_coupon_post_meta_usage_limit_per_user'] ),
					'limit_usage_to_x_items' => absint( $_POST['_wcv_coupon_post_meta_limit_usage_to_x_items'] ),
					'exclude_sale_items'     => isset( $_POST['_wcv_coupon_post_meta_exclude_sale_items'] ),
					'minimum_amount'         => wc_format_decimal( $_POST['_wcv_coupon_post_meta_minimum_amount'] ),
					'maximum_amount'         => wc_format_decimal( $_POST['_wcv_coupon_post_meta_maximum_amount'] ),
					'email_restrictions'     => array_filter( array_map( 'trim', explode( ',', wc_clean( $_POST['_wcv_coupon_post_meta_email_addresses'] ) ) ) ),
				),
				$coupon_object,
				$_POST
			)
		);

		$coupon_object->save();

		if ( $coupon ) {
			if ( isset( $_POST['_wcv_coupon_post_id'] ) && is_numeric( $_POST['_wcv_coupon_post_id'] ) ) {
				$text = __( 'Coupon Updated.', 'wcvendors-pro' );
			} else {
				$text = __( 'Coupon Added.', 'wcvendors-pro' );
			}

			do_action( 'wcv_after_save_coupon', $coupon, $_POST );

		} else {
			if ( isset( $_POST['_wcv_coupon_post_id'] ) && is_numeric( $_POST['_wcv_coupon_post_id'] ) ) {
				$text = __( 'There was a problem updating the coupon.', 'wcvendors-pro' );
			} else {
				$text = __( 'There was a problem adding the coupon.', 'wcvendors-pro' );
			}
		}

		wc_add_notice( $text );

		$coupon_redirect = get_option( 'wcvendors_save_coupon_redirect', 'addnew' );

		$coupon_url = WCVendors_Pro_Dashboard::get_dashboard_page_url( 'shop_coupon' );

		switch ( $coupon_redirect ) {
			case 'edit':
				// code...
				$url = $coupon_url . '/edit/' . $coupon;
				break;
			case 'list':
				$url = $coupon_url;
				break;
			case 'addnew':
			default:
				$url = $coupon_url . '/edit/';
				break;
		}

		wp_safe_redirect( $url );

		exit;

	} // process_submit()

	/**
	 *  Process the delete action
	 *
	 * @since    1.0.0
	 */
	public function process_delete() {

		global $wp;

		if ( isset( $wp->query_vars['object'] ) ) {

			$object = get_query_var( 'object' );
			$action = get_query_var( 'action' );
			$id     = get_query_var( 'object_id' );

			if ( $object == 'shop_coupon' && $action == 'delete' && is_numeric( $id ) ) {

				if ( $id != null ) {
					if ( WCVendors_Pro_Dashboard::check_object_permission( 'shop_coupon', $id ) == false ) {
						return false;
					}
				}

				if ( 'yes' !== get_option( 'wcvendors_vendor_coupon_trash', 'no' ) ) {
					$update = wp_update_post(
						array(
							'ID'          => $id,
							'post_status' => 'trash',
						)
					);
				} else {
					$update = wp_delete_post( $id );
				}

				if ( is_object( $update ) || is_numeric( $update ) ) {
					$text = __( 'Coupon Deleted.', 'wcvendors-pro' );
				} else {
					$text = __( 'There was a problem deleting the coupon.', 'wcvendors-pro' );
				}

				wc_add_notice( $text );

				wp_safe_redirect( WCVendors_Pro_Dashboard::get_dashboard_page_url( 'shop_coupon' ) );

				exit;
			}
		}

	} // process_delete()

	/**
	 *  Update Table Headers for display
	 *
	 * @since    1.0.0
	 *
	 * @param     array $headers array passed via filter
	 */
	public function table_columns() {

		$columns = array(
			'coupon'        => __( 'Coupon', 'wcvendors-pro' ),
			'coupon_type'   => __( 'Coupon Type', 'wcvendors-pro' ),
			'coupon_amount' => __( 'Coupon Amount', 'wcvendors-pro' ),
			'description'   => __( 'Description', 'wcvendors-pro' ),
			'product_ids'   => __( 'Product ID\'s', 'wcvendors-pro' ),
			'usage_limits'  => __( 'Usage / Limits', 'wcvendors-pro' ),
			'expiry_date'   => __( 'Expiry', 'wcvendors-pro' ),
		);

		return apply_filters( 'wcv_shop_coupon_table_columns', $columns );

	} // table_columns()

	/**
	 *  Manipulate the table data
	 *
	 * @since    1.0.0
	 *
	 * @param     array $rows          array of wp_post objects passed by the filter
	 * @param     mixed $result_object the wp_query object
	 *
	 * @return   array  $new_rows   array of stdClass objects passed back to the filter
	 */
	public function table_rows( $rows, $result_object ) {

		$this->max_num_pages = $result_object->max_num_pages;

		$new_rows = array();

		foreach ( $rows as $row ) {

			$new_row     = new stdClass();
			$the_coupon  = new WC_Coupon( $row->post_title );
			$coupon_meta = get_post_meta( $row->ID );

			$coupon_amount     = $the_coupon->get_amount();
			$discount_type     = $the_coupon->get_discount_type();
			$usage_count       = $the_coupon->get_usage_count();
			$usage_limit       = $the_coupon->get_usage_limit() ? $the_coupon->get_usage_limit() : '&infin;';
			$expiry_date       = $the_coupon->get_date_expires();
			$usage_display     = sprintf( '%s / %s', $usage_count, $usage_limit );
			$product_ids       = $the_coupon->get_product_ids();
			$products_text     = '';
			$product_id_string = is_array( $product_ids ) ? implode( ',', $product_ids ) : '*';
			$expiry            = empty( $expiry_date ) ? __( '-', 'wcvendors-pro' ) : date_i18n( get_option( 'date_format' ), strtotime( $expiry_date ) );

			if ( sizeof( $product_ids ) > 2 ) {
				$products_text = '<span class="wcv-tooltip" data-tip-text="' . $product_id_string . '">' . $product_ids[0] . ',' . $product_ids[1] . '...</span>';
			} else {
				$products_text = $product_id_string;
			}

			$coupon_text = sprintf(
				 '
				<div class="coupon_code">%s</div>
				<div class="amount wcv_mobile">%s</div>
				<div class="expires wcv_mobile">%s</div>
				<div class="description wcv_mobile">%s</div>
				',
				$row->post_title,
				sprintf( __( 'Expires : %s ', 'wcvendors-pro' ), $expiry ),
				sprintf( __( 'Amount : %s ', 'wcvendors-pro' ), $coupon_amount ),
				sprintf( __( 'Description : %s ', 'wcvendors-pro' ), $row->post_excerpt )
			);

			$new_row->ID            = $row->ID;
			$new_row->coupon        = $coupon_text;
			$new_row->coupon_type   = $this->coupon_types( $discount_type );
			$new_row->coupon_amount = $coupon_amount;
			$new_row->description   = $row->post_excerpt;
			$new_row->product_ids   = $products_text;
			$new_row->usage_limits  = $usage_display;
			$new_row->expiry_date   = empty( $expiry_date ) ? __( '-', 'wcvendors-pro' ) : date_i18n( get_option( 'date_format' ), strtotime( $expiry_date ) );
			$new_row->coupon_meta   = $coupon_meta;

			$new_rows[] = $new_row;

		}

		return apply_filters( 'wcv_shop_coupon_table_rows', $new_rows );

	} // table_rows()

	/**
	 *  Change the column that actions are displayed in
	 *
	 * @since    1.0.0
	 *
	 * @param     string $column column passed from filter
	 *
	 * @return   string $new_column    new column passed back to filter
	 */
	public function table_action_column( $column ) {

		$new_column = 'coupon';

		return apply_filters( 'wcv_shop_coupon_table_action_column', $new_column );

	}

	/**
	 *  Change the column that actions are displayed in
	 *
	 * @since    1.0.0
	 *
	 * @param     string $column column passed from filter
	 *
	 * @return   string $new_column    new column passed back to filter
	 */
	public function table_row_actions( $actions ) {

		unset( $actions['view'] );

		return $actions;

	}

	/**
	 *  Change the column that actions are displayed in
	 *
	 * @since    1.0.0
	 *
	 * @param     string $column column passed from filter
	 *
	 * @return   string $new_column    new column passed back to filter
	 */
	public function table_no_data_notice( $notice ) {

		$product_ids = WCVendors_Pro_Vendor_Controller::get_products_by_id( get_current_user_id() );

		if ( ! empty( $product_ids ) ) {
			$notice = __( 'No coupons found.', 'wcvendors-pro' );
		} else {
			$notice = __( "You cannot add coupons until you've added a product. ", 'wcvendors-pro' );
		}

		return apply_filters( 'wcv_shop_coupon_table_no_data_notice', $notice );

	}

	/**
	 *  Add actions before and after the table
	 *
	 * @since    1.0.0
	 */
	public function table_actions( $id = 'shop_coupon', $position = 'before' ) {

		$product_ids = WCVendors_Pro_Vendor_Controller::get_products_by_id( get_current_user_id() );

		$pagination_wrapper = apply_filters(
			'wcv_shop_coupon_paginate_wrapper',
			array(
				'wrapper_start' => '<nav class="woocommerce-pagination">',
				'wrapper_end'   => '</nav>',
			)
		);

		if ( ! empty( $product_ids ) ) {

			$add_url = 'edit';

			include apply_filters( 'wcvendors_pro_shop_coupon_table_actions_path', 'partials/shop_coupon/wcvendors-pro-table-shop-coupon-table-actions.php' );
		}

	} //table_actions()

	/**
	 *  Return pretty coupon type
	 *
	 * @since    1.0.0
	 *
	 * @param     string $index key to look up
	 *
	 * @return   string $name        nice name
	 */
	public function coupon_types( $index ) {

		$coupon_types = apply_filters(
			'wcv_coupon_types',
			array(
				'fixed_product' => __( 'Fixed Discount', 'wcvendors-pro' ),
				'percent'       => __( 'Percentage Discount', 'wcvendors-pro' ),
			)
		);

		return $coupon_types[ $index ];
	}

	/**
	 *  Return pretty coupon type
	 *
	 * @since    1.0.0
	 * @return   string $name        nice name
	 */
	public function coupon_meta_defs() {

		$coupon_meta = array(
			'discount_type',
			'apply_to_all_products',
			'vendor_free_shipping',
			'coupon_amount',
			'expiry_date',
			'minimum_amount',
			'maximum_amount',
			'individual_use',
			'exclude_sale_items',
			'product_ids',
			'exclude_product_ids',
			'email_addresses',
			'usage_limit',
			'usage_limit_per_user',
		);

		return apply_filters( 'wcv_coupon_meta', $coupon_meta );
	}

	/**
	 *  Check if the coupon exists in the system
	 *
	 * @since    1.0.0
	 * @return   string $coupon_title    coupon title to search for
	 */
	public function coupon_exists( $coupon_title ) {

		global $wpdb;

		// Check for dupe coupons
		$query = $wpdb->prepare(
			"
			SELECT $wpdb->posts.ID
			FROM $wpdb->posts
			WHERE $wpdb->posts.post_type = 'shop_coupon'
			AND $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.post_title = '%s'
		 	",
			$coupon_title
		);

		$wpdb->query( $query );

		if ( $wpdb->num_rows ) {
			return true;
		} else {
			return false;
		}

	} // coupon_exists()

	/**
	 *  Add a vendor store column data to coupons in the WP_LIST_TABLE
	 *
	 * @since    1.0.0
	 *
	 * @param     string $column  the column
	 * @param     int    $post_id the post id this relates to
	 */
	public function display_vendor_store_custom_column( $column, $post_id ) {

		$vendor_id = get_post_field( 'post_author', $post_id );

		if ( ! $vendor_id ) {
			return;
		}

		$vendor_store_name = WCVendors_Pro_Vendor_Controller::get_vendor_detail( $vendor_id, 'pv_shop_name' );
		$vendor_store_link = WCV_Vendors::get_vendor_shop_page( $vendor_id );

		switch ( $column ) {

			case 'vendor_store':
				include apply_filters( 'wcvendors_pro_shop_coupon_admin_column_path', 'partials/shop_coupon/wcvendors-pro-shop-coupon-admin-column.php' );
				break;

			default:
				break;
		}

	} // display_vendor_store_custom_column()

	/**
	 *  Add a vendor store column to coupons in the WP_LIST_TABLE
	 *
	 * @since    1.0.0
	 *
	 * @param     array $columns wp-admin columns
	 */
	public function display_vendor_store_column( $posts_columns ) {

		$posts_columns['vendor_store'] = sprintf( __( '%s Store', 'wcvendors-pro' ), wcv_get_vendor_name() );

		return $posts_columns;

	} // display_vendor_store_column()

	/**
	 *  Posts per page
	 *
	 * @since    1.2.4
	 *
	 * @param     int $post_num number of posts to display from the admin options.
	 */
	public function table_posts_per_page( $per_page ) {

		return get_option( 'wcvendors_coupons_per_page', 20 );

	} //table_posts_per_page()


	/**
	 * Validate the min max values of the coupon amount for the vendor products.
	 *
	 * @param bool        $valid - Current validity of the coupon.
	 * @param WC_Coupon   $coupon - The coupon applied.
	 * @param WC_Discount $discount - The WooCommerce discount object.
	 * @return bool $valid - If the vendor coupon is valid for min/max amount.
	 *
	 * @since 1.7.7
	 */
	public function validate_vendor_coupon_min_max( $valid, $coupon, $discount ) {

		// Check if this is a vendor coupon.
		$coupon_vendor_id = get_post_field( 'post_author', $coupon->get_id() );

		// Check coupon is admin coupon or not.
		$user = get_userdata( $coupon_vendor_id );
		if ( in_array( 'administrator', $user->roles ) ) {
			return $valid;
		}

		if ( ! $coupon_vendor_id && ! WCV_Vendors::is_vendor( $coupon_vendor_id ) ) {
			return $valid;
		}

		$subtotal  = 0;
		$total_tax = 0;

		// Loop through the items
		foreach ( $discount->get_items_to_validate() as $item ) {

			if ( $item->product ) {
				$product_vendor_id = get_post_field( 'post_author', $item->product->get_id() );

				if ( $coupon_vendor_id === $product_vendor_id ) {
					$subtotal += $item->object['line_subtotal'];
				}
			}
		}

		$vendor_store_name      = WCVendors_Pro_Vendor_Controller::get_vendor_detail( $coupon_vendor_id, 'pv_shop_name' );
		$vendor_store_link      = WCV_Vendors::get_vendor_shop_page( $coupon_vendor_id );
		$vendor_store_name_link = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $vendor_store_link, $vendor_store_name );

		// validate minimum amount.
		if ( $coupon->get_minimum_amount() > 0 && apply_filters( 'wcv_coupon_validate_minimum_amount', $coupon->get_minimum_amount() > $subtotal, $coupon, $subtotal ) ) {
			/* translators: %s: coupon minimum amount, %2$s: the link to vendor store page */
			throw new Exception(
				sprintf(
					__( 'The minimum spend for this coupon is %1$s and the coupon applies to products purchased from %2$s.', 'wcvendors-pro' ),
					wc_price( $coupon->get_minimum_amount() ),
					$vendor_store_name_link
				),
				108
			);
		}

		// validate maximum amount.
		if ( $coupon->get_maximum_amount() > 0 && apply_filters( 'wcv_coupon_validate_maximum_amount', $coupon->get_maximum_amount() < $subtotal, $coupon ) ) {
			/* translators: %1$s: coupon maximum amount, %2$s: the link to vendor store page */
			throw new Exception(
				sprintf(
					__( 'The maximum spend for this coupon is %1$s and the coupon applies to products purchased from %2$s.', 'wcvendors-pro' ),
					wc_price( $coupon->get_minimum_amount() ),
					$vendor_store_name_link
				),
				108
			);
		}

		return $valid;

	} // validate_vendor_coupon_min_max()

}
