<?php
/**
 * The WCVendors Export Helper Class
 *
 * This is the this is the helper class to help exporting data for vendors
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * WCVendors Pro Order Export class.
 *
 * @version 1.7.4
 * @since   1.0.0
 */
class WCVendors_Pro_Export_Helper {

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
	 * Initialize the class and set its properties.
	 *
	 * @version 1.7.4
	 * @since   1.0.0
	 *
	 * @param string $wcvendors_pro The name of the plugin.
	 * @param string $version       The version of this plugin.
	 * @param string $debug         Whether debug is turned on or not.
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {

		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;
		$this->debug         = $debug;
		$this->base_dir      = plugin_dir_path( dirname( __FILE__ ) );

		$this->can_view_orders        = wc_string_to_bool( get_option( 'wcvendors_capability_orders_enabled', 'no' ) );
		$this->can_export_csv         = wc_string_to_bool( get_option( 'wcvendors_capability_orders_export', 'no' ) );
		$this->can_view_emails        = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_email', 'no' ) );
		$this->can_view_name          = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_name', 'no' ) );
		$this->can_view_shipping_name = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_shipping_name', 'no' ) );
		$this->can_view_address       = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_shipping', 'no' ) );
		$this->can_view_phone         = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_phone', 'no' ) );
	}

	/**
	 * Send the CSV to the browser for download
	 *
	 * @version 1.7.4
	 * @since   1.0.0
	 *
	 * @param array $all_orders All the orders to export.
	 *
	 * @return array  $orders  Formatted orders.
	 */
	public function format_orders_export( $all_orders ) {

		$rows = array();

		if ( ! empty( $all_orders ) ) {

			foreach ( $all_orders as $_order ) {

				$order          = $_order->order;
				$products       = '';
				$needs_shipping = false;
				$needs_to_ship  = false;
				$downloadable   = false;

				foreach ( $_order->order_items as $key => $item ) {

					$product_id = $item->get_product_id();
					$_product   = new WC_Product( $product_id );

					$needs_shipping = $_product->is_virtual();
					if ( ! $needs_shipping ) {
						$needs_shipping = 0;
					}

					$downloadable = ( $_product->is_downloadable( 'yes' ) ) ? true : false;
					if ( is_null( $downloadable ) ) {
						$downloadable = 0;
					}
					$item_qty      = $item['qty'];
					$item_name     = $item['name'];
					$products     .= "$item_qty x $item_name \r";
					$needs_to_ship = ( $needs_shipping || ! $downloadable ) ? true : false;
				}

				$order_id    = $order->get_id();
				$shippers    = (array) get_post_meta( $order_id, 'wc_pv_shipped', true );
				$has_shipped = in_array( get_current_user_id(), $shippers ) ? __( 'Yes', 'wcvendors-pro' ) : __( 'No', 'wcvendors-pro' );
				$shipped     = ( $needs_to_ship ) ? $has_shipped : __( 'NA', 'wcvendors-pro' );
				$order_date  = $order->get_date_created();

				$use_shipping_address = apply_filters( 'wcv_export_orders_use_shipping_address', true );
				$shipping_name        = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
				$billing_name         = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
				$customer_details     = $use_shipping_address && '' != trim( $shipping_name ) ? $shipping_name : $billing_name;

				$new_row = array();

				$new_row['order_number'] = $order->get_order_number();
				$new_row['products']     = $products;
				if ( $this->can_view_name ) {
					$new_row['customer'] = $customer_details;
				}
				if ( $this->can_view_address ) {
					$new_row['address']  = $use_shipping_address && '' != $order->get_shipping_address_1() ? $order->get_shipping_address_1() : $order->get_billing_address_1();
					$new_row['address2'] = $use_shipping_address && '' != $order->get_shipping_address_2() ? $order->get_shipping_address_2() : $order->get_billing_address_2();
					$new_row['city']     = $use_shipping_address && '' != $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
					$new_row['state']    = $use_shipping_address && '' != $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state();
					$new_row['zip']      = $use_shipping_address && '' != $order->get_shipping_postcode() ? $order->get_shipping_postcode() : $order->get_billing_postcode();
				}
				if ( $this->can_view_emails ) {
					$new_row['email'] = $order->get_billing_email();
				}
				if ( $this->can_view_phone ) {
					$new_row['phone'] = $order->get_billing_phone();
				}
				$new_row['total']      = $_order->total;
				$new_row['status']     = $shipped;
				$new_row['order_date'] = date_i18n( 'Y-m-d', strtotime( $order_date ) );

				$rows[] = $new_row;

			}
		} // check for orders

		return $rows;

	} // prepare_orders_export

	/**
	 * Send the CSV to the browser for download
	 *
	 * @version 1.7.4
	 * @since   1.0.0
	 *
	 * @param    array  $headers  The CSV column headers.
	 * @param    array  $body     The CSV body.
	 * @param    string $filename The CSV filename.
	 */
	public function download_csv( $headers, $body, $filename ) {

		// Clear browser output before this point.
		if ( ob_get_contents() ) {
			ob_end_clean();
		}

		if ( ! $body ) {
			return false;
		}

		// Output headers so that the file is downloaded rather than displayed.
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename . '.csv' );

		// Create a file pointer connected to the output stream.
		$csv_output = fopen( 'php://output', 'w' );

		// Output the column headings.
		fputcsv( $csv_output, $headers );

		// Body.
		foreach ( $body as $data ) {
			fputcsv( $csv_output, $data );
		}

		die();

	} // download_csv

	/**
	 * Headers for the orders export CSV
	 *
	 * @version 1.7.4
	 * @since   1.7.4
	 *
	 * @return array
	 */
	public function get_export_headers() {
		$headers = array(
			'order'    => __( 'Order', 'wcvendors-pro' ),
			'product'  => __( 'Product Title', 'wcvendors-pro' ),
			'name'     => __( 'Full name', 'wcvendors-pro' ),
			'address'  => __( 'Address', 'wcvendors-pro' ),
			'address2' => __( 'Address 2', 'wcvendors-pro' ),
			'city'     => __( 'City', 'wcvendors-pro' ),
			'state'    => __( 'State', 'wcvendors-pro' ),
			'zip'      => __( 'Zip', 'wcvendors-pro' ),
			'email'    => __( 'Email address', 'wcvendors-pro' ),
			'phone'    => __( 'Phone', 'wcvendors-pro' ),
			'total'    => __( 'Order Total', 'wcvendors-pro' ),
			'status'   => __( 'Order Status', 'wcvendors-pro' ),
			'date'     => __( 'Date', 'wcvendors-pro' ),
		);

		if ( ! $this->can_view_emails ) {
			unset( $headers['email'] );
		}

		if ( ! $this->can_view_phone ) {
			unset( $headers['phone'] );
		}

		if ( ! $this->can_view_name ) {
			unset( $headers['name'] );
		}

		if ( ! $this->can_view_address ) {
			unset( $headers['address'] );
			unset( $headers['address2'] );
			unset( $headers['city'] );
			unset( $headers['state'] );
			unset( $headers['zip'] );
		}

		return apply_filters( 'wcv_export_headers', $headers );
	}
}
