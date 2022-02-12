<?php
/**
 * The WCVendors Pro Reports Controller class
 *
 * This is the reports controller class for all front end reports
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */

/**
 * WCVendors Pro Reports Controller class
 *
 * @version 1.7.10
 * @since   1.0.0
 */
class WCVendors_Pro_Reports_Controller {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string $wcvendors_pro The ID of this plugin.
	 */
	private $wcvendors_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
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
	 * @var      string $base_dir string path for the plugin directory
	 */
	private $base_dir;

	/**
	 * Commission due
	 *
	 * @var double
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $commission_due;

	/**
	 * Shipping commission due
	 *
	 * @var double
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $commission_shipping_due;

	/**
	 * Commission paid
	 *
	 * @var double
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $commission_paid;

	/**
	 * Comission shipping paid
	 *
	 * @var double
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $commission_shiping_paid;

	/**
	 * Default start date
	 *
	 * @var string
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $default_start;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wcvendors_pro The name of the plugin.
	 * @param      string $version       The version of this plugin.
	 * @param      bool   $debug         Whether debug is enabled or not.
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {

		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;
		$this->debug         = $debug;
		$this->base_dir      = plugin_dir_path( dirname( __FILE__ ) );

		$dashboard_date_range = get_option( 'wcvendors_dashboard_date_range', 'monthly' );
		$this->default_start  = '';

		switch ( $dashboard_date_range ) {
			case 'annually':
				$this->default_start = '-1 year';
				break;
			case 'quarterly':
				$this->default_start = '-3 month';
				break;
			case 'monthly':
				$this->default_start = '-1 month';
				break;
			case 'weekly':
				$this->default_start = '-1 week';
				break;
			case 'daily':
				$this->default_start = '-1 day';
				break;
			case 'custom':
				$this->default_start = '-1 year';
				break;
			default:
				$this->default_start = '-1 month';
				break;
		}

		$this->default_start = apply_filters( 'wcv_default_dashboard_start_date', $this->default_start );
	}

	/**
	 *  Initialise the reports
	 *
	 * @since    1.0.0
	 */
	public function report_init() {

		$date_range = array(
			'before' => date( 'Y-m-d', $this->get_end_date() ),
			'after'  => date( 'Y-m-d', $this->get_start_date() ),
		);

		$this->orders = WCVendors_Pro_Vendor_Controller::get_orders2( get_current_user_id(), $date_range, true );

		// Generate the totals required for the overview.
		$this->get_totals();
		$this->get_order_chart_data();

	} // report_init

	/**
	 *  Process the date range form submission from the front end.
	 *
	 * @since    1.0.0
	 */
	public function process_submit() {

		if ( ! isset( $_POST['wcv_dashboard_date_update'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['wcv_dashboard_date_update'], 'wcv-dashboard-date-update' ) ) {
			return;
		}

		// Start Date.
		if ( isset( $_POST['_wcv_dashboard_start_date_input'] ) || '' === $_POST['_wcv_dashboard_start_date_input'] ) {
			WC()->session->set( 'wcv_dashboard_start_date', strtotime( $_POST['_wcv_dashboard_start_date_input'] ) );
		}

		// End Date
		if ( isset( $_POST['_wcv_dashboard_end_date_input'] ) || '' === $_POST['_wcv_dashboard_end_date_input'] ) {
			WC()->session->set( 'wcv_dashboard_end_date', strtotime( $_POST['_wcv_dashboard_end_date_input'] ) );
		}

	} // process_submit

	/**
	 *  Display the dashboard template
	 *
	 * @since    1.0.0
	 */
	public function display() {

		wc_get_template(
			'dashboard.php',
			array(
				'store_report'      => $this,
				'products_disabled' => get_option( 'wcvendors_product_management_cap', 'no' ),
				'orders_disabled'   => get_option( 'wcvendors_order_management_cap', 'no' ),
			),
			'wc-vendors/dashboard/',
			$this->base_dir . 'templates/dashboard/'
		);

	} // display

	/**
	 *  Get the total sales amount
	 *
	 * @since    1.0.0
	 */
	public function get_filtered_orders() {

		// This filters the array based on the dates provided. This allows date based filtering without re-quering the database.
		return $filtered_orders = array_filter(
			$this->orders,
			function ( $order ) {
				return strtotime( $order->recorded_time ) >= $this->get_start_date() && strtotime( $order->recorded_time ) <= $this->get_end_date();
			}
		);

	} //get_filtered_orders

	/**
	 *  Calculate the totals for the reports overview
	 *
	 * @since    1.0.0
	 */
	public function get_totals() {

		$this->commission_due           = 0;
		$this->commission_paid          = 0;
		$this->commission_shipping_due  = 0;
		$this->commission_shipping_paid = 0;
		$this->commission_tax_due       = 0;
		$this->commission_tax_paid      = 0;
		$this->total_products_sold      = 0;

		$wcv_orders = $this->get_filtered_orders();

		// Count all orders.
		$this->total_orders = count( $wcv_orders );

		// Create the cumulative totals for commissions and products.
		foreach ( $wcv_orders as $wcv_order ) {

			if ( $wcv_order->status == 'due' ) {
				$this->commission_due          += $wcv_order->total_due;
				$this->commission_shipping_due += $wcv_order->total_shipping;
				$this->commission_tax_due      += $wcv_order->total_tax;
			} elseif ( 'paid' == $wcv_order->status ) {
				$this->commission_paid          += $wcv_order->total_due;
				$this->commission_shipping_paid += $wcv_order->total_shipping;
				$this->commission_tax_paid      += $wcv_order->total_tax;
			}

			$this->total_products_sold += $wcv_order->qty;
		}

	} // get_totals

	/**
	 *  Get the order chart data required for output
	 *
	 * @since    1.0.0
	 * @return   array  $order_chart_data   array of order chart data
	 */
	public function get_order_chart_data() {

		$grouped_orders = array();
		$wcv_orders     = $this->get_filtered_orders();

		// Group the orders by date and get total orders for that date.
		foreach ( $wcv_orders as $order ) {

			if ( ! array_key_exists( $order->recorded_time, $grouped_orders ) ) {
				$grouped_orders[ $order->recorded_time ] = array();
			}

			if ( is_array( $grouped_orders[ $order->recorded_time ] ) && ! array_key_exists( 'total', $grouped_orders[ $order->recorded_time ] ) ) {
				$grouped_orders[ $order->recorded_time ] = array( 'total' => 0 );
			}

			$grouped_orders[ $order->recorded_time ]['total'] += 1;
		}

		if ( empty( $grouped_orders ) ) {
			return null;
		}

		// Extract the date labels.
		$labels = json_encode( array_keys( $grouped_orders ) );
		// Extract the totals for each day.
		$data = json_encode( array_values( wp_list_pluck( $grouped_orders, 'total' ) ) );

		$chart_data = array(
			'labels' => $labels,
			'data'   => $data,
		);

		return $chart_data;

	} //get_order_chart_data

	/**
	 *  Get the order chart data required for output
	 *
	 * @since    1.0.0
	 * @return   array  $order_chart_data   array of order chart data
	 */
	public function get_product_chart_data() {

		$grouped_products = array();
		$chart_data       = array();
		$wcv_orders       = $this->get_filtered_orders();

		if ( ! empty( $wcv_orders ) ) {

			// Group the orders by date and get total orders for that date.
			foreach ( $wcv_orders as $order ) {

				// Make sure the order exists before attempting to loop over it.
				if ( is_object( $order->order ) ) {
					if ( is_array( $order->order_items ) ) {

						foreach ( $order->order_items as $item ) {
							if ( ! array_key_exists( $item['name'], $grouped_products ) ) {
								$grouped_products[ $item['name'] ] = array();
							}

							if ( is_array( $grouped_products[ $item['name'] ] ) && ! array_key_exists( 'total', $grouped_products[ $item['name'] ] ) ) {
								$grouped_products[ $item['name'] ] = array( 'total' => 0 );
							}

							$grouped_products[ $item['name'] ]['total']     += $item['qty'];
							$grouped_products[ $item['name'] ]['product_id'] = $item['product_id'];

						}
					}
				}
			}

			// create the pie chart data, color and hover are currently randomly generated.
			foreach ( $grouped_products as $label => $product_data ) {
				$random_colors = apply_filters( 'wcv_product_totals_chart_use_random_colors', wc_string_to_bool( get_option( 'wcv_product_totals_chart_use_random_colors', 'no' ) ) );

				if ( $random_colors ) {
					$fill_min  = apply_filters( 'wcv_product_totals_chart_fill_color_min', 0 );
					$fill_max  = apply_filters( 'wcv_product_totals_chart_fill_color_max', 0xFFFFFF );
					$hover_min = apply_filters( 'wcv_product_totals_chart_hover_color_min', 0 );
					$hover_max = apply_filters( 'wcv_product_totals_chart_hover_color_max', 0xFFFFFF );
				} else {
					$base_fill_color_number = apply_filters( 'wcv_product_totals_chart_fill_color_base_number', hexdec( str_replace( '#', '', get_option( 'wcv_product_totals_chart_base_fill_color' ) ) ) );

					$base_hover_color_number = apply_filters( 'wcv_product_totals_chart_hover_color_base_number', hexdec( str_replace( '#', '', get_option( 'wcv_product_totals_chart_base_hover_color' ) ) ) );

					$fill_max = apply_filters( 'wcv_product_totals_chart_fill_color_min', $base_fill_color_number + apply_filters( 'wcv_product_totals_chart_base_fill_color_plus', 500 ) );
					$fill_min = apply_filters( 'wcv_product_totals_chart_fill_color_max', $base_fill_color_number - apply_filters( 'wcv_product_totals_chart_base_fill_color_minus', 500 ) );

					$hover_max = apply_filters( 'wcv_product_totals_chart_hover_color_min', $base_hover_color_number + apply_filters( 'wcv_product_totals_chart_base_hover_color_plus', 500 ) );
					$hover_min = apply_filters( 'wcv_product_totals_chart_hover_color_max', $base_hover_color_number - apply_filters( 'wcv_product_totals_chart_base_hover_color_minus', 500 ) );
				}

				$chart_data[] = array(
					'value' => $product_data['total'],
					'color' => apply_filters(
						'wcv_product_totals_chart_chart_color_' . $product_data['product_id'],
						'#' . str_pad( dechex( mt_rand( $fill_min, $fill_max ) ), 6, '0', STR_PAD_LEFT )
					),
					'hover' => apply_filters( 'wcv_product_totals_chart_chart_hover_' . $product_data['product_id'], '#' . str_pad( dechex( mt_rand( $hover_min, $hover_max ) ), 6, '0', STR_PAD_LEFT ) ),
					'label' => $label,
				);
			}

			if ( empty( $chart_data ) ) {
				return false;
			}
		}

		return json_encode( $chart_data );

	} // get_product_chart_data

	/**
	 *  Output the recent orders mini table
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 * @return   array  $recent_orders   array of recent orders
	 */
	public function recent_orders_table() {

		$shipping_disabled = wc_string_to_bool( get_option( 'wcvendors_shipping_management_cap', 'no' ) );

		// Get the last 10 recent orders.
		$max_orders = apply_filters( 'wcv_recent_orders_max', 9 );

		$recent_orders = array_splice( $this->orders, 0, $max_orders );

		// Create recent orders table.
		$recent_order_table = new WCVendors_Pro_Table_Helper( $this->wcvendors_pro, $this->version, 'recent_order', null, get_current_user_id() );

		$recent_order_table->container_wrap = false;

		// Set the columns.
		$columns = array(
			'ID'           => __( 'ID', 'wcvendors-pro' ),
			'order_number' => __( 'Order', 'wcvendors-pro' ),
			'product'      => __( 'Products', 'wcvendors-pro' ),
			'totals'       => __( 'Totals', 'wcvendors-pro' ),
			'commission'   => __( 'Commission', 'wcvendors-pro' ),
		);
		$recent_order_table->set_columns( $columns );

		// Set the rows.
		$rows = array();

		if ( ! empty( $recent_orders ) ) {

			foreach ( $recent_orders as $order ) {

				$products_html   = '';
				$totals_html     = '';
				$commission_html = '';
				$total_products  = 0;

				// Make sure the order exists before attempting to loop over it.
				if ( is_object( $order->order ) ) {
					if ( is_array( $order->order_items ) ) {
						$total_products = count( $order->order_items );

						// Get products to output
						foreach ( $order->order_items as $key => $item ) {
							$where            = array(
								'vendor_id'  => get_current_user_id(),
								'order_id'   => $order->order_id,
								'product_id' => $item->get_product_id(),
							);
							$count_paid       = WCV_Commission::check_commission_status( $where, 'paid' );
							$commission_html .= ( 0 == $count_paid ? '<strong>' . __( 'Due', 'wcvendors-pro' ) . '</strong>' : '<strong>' . __( 'Paid', 'wcvendors-pro' ) . '</strong>' );
							// May need to fix for variations.
							$products_html  .= '<strong>' . $item['qty'] . ' x ' . $item['name'] . '</strong>';
							$item_product_id = $item->get_product_id();

							$totals_html .= wc_price( $order->product_commissions[ $item_product_id ] );
							if ( $total_products > 1 ) {
								$products_html   .= '<br />';
								$commission_html .= '<br />';
								$totals_html     .= '<br />';
							}
						}
					}
				}

				if ( ! $shipping_disabled ) {

					$products_html .= ( $total_products == 1 ) ? '<br /><strong>' . __( 'Shipping', 'wcvendors-pro' ) . '</strong>' : '<strong>' . __( 'Shipping', 'wcvendors-pro' ) . '</strong>';

					$totals_html .= ( $total_products == 1 ) ? '<br />' . wc_price( $order->total_shipping ) : wc_price( $order->total_shipping );
				}

				$new_row = new stdClass();

				$new_row->ID           = $order->order_id;
				$new_row->order_number = $order->order->get_order_number() . '<br />' . date_i18n( get_option( 'date_format', 'F j, Y' ), ( $order->order->get_date_created()->getOffsetTimestamp() ) );
				$new_row->product      = $products_html;
				$new_row->totals       = $totals_html;
				$new_row->commission   = $commission_html;

				$rows[] = $new_row;

			}
		}

		$recent_order_table->set_rows( $rows );

		// Disable row actions.
		$recent_order_table->set_actions( array() );

		// display the table.
		$recent_order_table->display();

		return $recent_orders;

	} // recent_orders_table

	/**
	 *  Change the order text output when there are no rows
	 *
	 * @since    1.0.0
	 *
	 * @param     string $notice Notice output.
	 *
	 * @return   string $notice    filtered text
	 */
	public function order_table_no_data_notice( $notice ) {

		$notice = __( 'No Orders found.', 'wcvendors-pro' );

		return $notice;

	} // order_table_no_data_notice

	/**
	 *  Output the recent products mini table
	 *
	 * @since    1.0.0
	 */
	public function recent_products_table() {

		$args              = array( 'numberposts' => apply_filters( 'wcv_recent_products_max', 5 ) );
		$recent_products   = WCVendors_Pro_Vendor_Controller::get_products_by_id( get_current_user_id(), $args );
		$products_disabled = get_option( 'wcvendors_product_management_cap', 'no' );
		$can_edit          = get_option( 'wcvendors_can_edit_published_products', '' );

		// Create the recent products table.
		$recent_product_table                 = new WCVendors_Pro_Table_Helper( $this->wcvendors_pro, $this->version, 'recent_product', null, get_current_user_id() );
		$recent_product_table->container_wrap = false;

		// Set the columns.
		$columns = array(
			'ID'      => __( 'ID', 'wcvendors-pro' ),
			'tn'      => __( '<i class="wcv-icon wcv-icon-picture-o"></i>', 'wcvendors-pro' ),
			'details' => __( 'Details', 'wcvendors-pro' ),
			'status'  => __( 'Status', 'wcvendors-pro' ),
		);
		$recent_product_table->set_columns( $columns );

		// Set the rows.
		$rows = array();
		$link = '';

		foreach ( $recent_products as $product_id ) {

			$new_row         = new stdClass();
			$product         = wc_get_product( $product_id );
			$vendor_disabled = ( 'yes' === $product->get_meta( '_disable_vendor_edit' ) );

			// Fix link based on template.
			$link          = ( $can_edit ) ? WCVendors_Pro_Product_Controller::get_product_edit_link( $product->get_id() ) : get_permalink( $product->get_id() );
			$link          = ( ! $products_disabled ) ? WCVendors_Pro_Product_Controller::get_product_edit_link( $product->get_id() ) : get_permalink( $product->get_id() );
			$link          = $vendor_disabled ? get_permalink( $product->get_id() ) : $link;
			$post          = get_post( $product->get_id() );
			$product_price = wc_get_price_to_display( $product );

			$target = '_self';
			if ( $products_disabled && wc_string_to_bool( get_option( 'wcvendors_dashboard_view_product_new_window', 'no' ) ) ) {
				$target = '_blank';
			}

			$new_row->ID      = $product->get_id();
			$new_row->tn      = get_the_post_thumbnail( $product->get_id(), array( 50, 50 ) );
			$new_row->details = sprintf( '<a href="%s" target="%s">%s<br />%s%s</a>', $link, $target, $product->get_title(), wc_price( $product_price ), $product->get_price_suffix() );
			$new_row->status  = sprintf( '%s <br /> %s', WCVendors_Pro_Product_Controller::product_status( $post->post_status ), date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $post->post_date ) ) );

			$rows[] = $new_row;

		}

		$rows = apply_filters( 'wcv_recent_product_table_rows', $rows );

		$recent_product_table->set_rows( $rows );

		// Disable row actions.
		$recent_product_table->set_actions( array() );

		// display the table.
		$recent_product_table->display();

		return $recent_products;

	} // recent_products_table

	/**
	 *  Change the product text output when there are no rows
	 *
	 * @since    1.0.0
	 *
	 * @param     string $notice Notice output.
	 *
	 * @return   string $notice    filtered text
	 */
	public function product_table_no_data_notice( $notice ) {

		$notice = __( 'No Products found.', 'wcvendors-pro' );

		return $notice;

	} // product_table_no_data_notice

	/**
	 *  Output the date range form to filter the reports
	 *
	 * @since   1.0.0
	 * @version 1.7.10
	 */
	public function date_range_form() {

		// Start Date.
		WCVendors_Pro_Form_Helper::input(
			apply_filters(
				'wcv_dashboard_start_date_input',
				array(
					'id'                => '_wcv_dashboard_start_date_input',
					'label'             => __( 'Start date', 'wcvendors-pro' ),
					'class'             => 'wcv-datepicker-dashboard-filter wcv-datepicker wcv-init-picker',
					'value'             => date( 'Y-m-d', $this->get_start_date() ),
					'placeholder'       => 'YYYY-MM-DD',
					'wrapper_start'     => '<div class="all-66 tiny-50"><div class="wcv-cols-group wcv-horizontal-gutters"><div class="all-50 tiny-100">',
					'wrapper_end'       => '</div>',
					'custom_attributes' => array(
						'maxlenth' => '10',
						'pattern'  => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])',
					),
				)
			)
		);

		// End Date.
		WCVendors_Pro_Form_Helper::input(
			apply_filters(
				'wcv_dashboard_end_date_input',
				array(
					'id'                => '_wcv_dashboard_end_date_input',
					'label'             => __( 'End date', 'wcvendors-pro' ),
					'class'             => 'wcv-datepicker-dashboard-filter wcv-datepicker wcv-init-picker',
					'value'             => date( 'Y-m-d', $this->get_end_date() ),
					'placeholder'       => 'YYYY-MM-DD',
					'wrapper_start'     => '<div class="all-50 tiny-100">',
					'wrapper_end'       => '</div></div></div>',
					'custom_attributes' => array(
						'maxlenth' => '10',
						'pattern'  => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])',
					),
				)
			)
		);

		// Update Button.
		WCVendors_Pro_Form_helper::submit(
			apply_filters(
				'wcv_dashboard_update_button',
				array(
					'id'            => 'update_button',
					'value'         => __( 'Update', 'wcvendors-pro' ),
					'class'         => 'expand',
					'wrapper_start' => '<div class="all-33"><div class="control-group"><div class="control"><label>&nbsp;&nbsp;</label>',
					'wrapper_end'   => '</div></div></div>',
				)
			)
		);

		wp_nonce_field( 'wcv-dashboard-date-update', 'wcv_dashboard_date_update' );

	}

	/**
	 * Get dashboard start date.
	 *
	 * @return array|string
	 */
	public function get_start_date() {
		return WC()->session->get( 'wcv_dashboard_start_date', strtotime( apply_filters( 'wcv_dashboard_start_date', $this->default_start ) ) );
	}

	/**
	 * Get dashboard end date.
	 *
	 * @return array|string
	 */
	public function get_end_date() {
		return WC()->session->get( 'wcv_dashboard_end_date', strtotime( apply_filters( 'wcv_dashboard_end_date', 'now' ) ) );
	}

}
