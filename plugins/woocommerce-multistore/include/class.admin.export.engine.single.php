<?php
/**
 * Single site export engine.
 *
 * @since 4.0.0
 */
class WOO_MSTORE_EXPORT_ENGINE_SINGLE {
	public $errors_log = array();

	private $export_type        = '';
	private $export_time_after  = '';
	private $export_time_before = '';
	private $site_filter        = '';
	private $order_status       = array();
	private $row_format         = '';
	private $export_fields      = array();

	public function process() {
		ini_set( 'max_execution_time', 600 );
		$orders = $this->fetch_all_orders();
		$this->output_all_orders( $orders );
	}

	public function validate_settings( $check_nonce = true ) {
		/**
		 * When requests are made via API, no need to check nounce
		 */
		if ( $check_nonce ) {
			$nonce = $_POST['woonet-orders-export-interface-nonce'];
			if ( ! wp_verify_nonce( $nonce, 'woonet-orders-export/interface-export' ) ) {
				$this->errors_log[] = 'Validation failed. Invalid nonce';
				return;
			}
		}

		if ( isset( $_POST['export_format'] ) && in_array( $_POST['export_format'], array( 'csv', 'xls' ) ) ) {
			$this->export_type = $_POST['export_format'];
		} else {
			$this->errors_log[] = 'Invalid export format';
		}

		if ( empty( $_POST['export_time_after'] ) ) {
			$this->export_time_after = 0;
		} else {
			$this->export_time_after = strtotime( $_POST['export_time_after'] );

			if ( false === $this->export_time_after ) {
				$this->errors_log[] = 'Invalid time After';
			}
		}

		if ( empty( $_POST['export_time_before'] ) ) {
			$this->export_time_before = 9999999999;
		} else {
			$this->export_time_before = strtotime( $_POST['export_time_before'] );

			if ( false === $this->export_time_before ) {
				$this->errors_log[] = 'Invalid time Before';
			}
		}

		if ( isset( $_POST['site_filter'] ) && is_array( $_POST['site_filter'] ) ) {
			$this->site_filter = array_filter( array_map( 'sanitize_key', $_POST['site_filter'] ) );

			if ( empty( $this->site_filter ) ) {
				$this->errors_log[] = 'Empty site filter';
			}
		} else {
			$this->errors_log[] = 'Empty site filter';
		}

		if ( isset( $_POST['order_status'] ) && in_array( $_POST['order_status'], array_keys( wc_get_order_statuses() ) ) ) {
			$this->order_status = array( $_POST['order_status'] );
		} else {
			$this->order_status = array_keys( wc_get_order_statuses() );
		}

		if ( isset( $_POST['row_format'] ) && in_array( $_POST['row_format'], array( 'row_per_order', 'row_per_product' ) ) ) {
			$this->row_format = $_POST['row_format'];
		} else {
			$this->errors_log[] = 'Invalid row export format';
		}

		$this->export_fields = empty( $_POST['export_fields'] ) ? array() : $_POST['export_fields'];

		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			update_option(
				'mstore_orders_export_options',
				array(
					'export_type'        => $this->export_type,
					'export_time_after'  => $this->export_time_after,
					'export_time_before' => $this->export_time_before,
					'site_filter'        => $this->site_filter,
					'order_status'       => $this->order_status,
					'row_format'         => $this->row_format,
					'export_fields'      => $this->export_fields,
				)
			);
		}

		return true;
	}

	/**
	 *
	 * When exporting from the child sites via AJAX set the export parameters first
	 */
	public function set_options( $data ) {
		$this->export_type        = $data['export_type'];
		$this->export_time_after  = $data['export_time_after'];
		$this->export_time_before = $data['export_time_before'];
		$this->site_filter        = $data['site_filter'];
		$this->order_status       = $data['order_status'];
		$this->row_format         = $data['row_format'];
		$this->export_fields      = $data['export_fields'];
	}

	/**
	 * Retrieve the orders
	 */
	public function fetch_orders() {
		global $wpdb;

		$status = array_map( 'esc_sql', $this->order_status );
		$status = "'" . implode( "', '", $status ) . "'";

		$query = $wpdb->prepare(
			"
			SELECT *
			FROM {$wpdb->prefix}posts
			WHERE post_type = 'shop_order'
				AND post_status IN ({$status})
				AND post_date BETWEEN %s AND %s
				ORDER BY ID ASC
			",
			array(
				date( 'Y-m-d', $this->export_time_after ),
				date( 'Y-m-d', $this->export_time_before ),
			)
		);

		$orders = $wpdb->get_results( $query, ARRAY_A );

		return $orders;
	}

	public function fetch_all_orders() {
		// All orders.
		$all_orders = array();

		// Get master orders if selected.
		if ( in_array( 'master', $this->site_filter ) ) {
			$master_orders = $this->output();
		}

		// Get child site orders.
		$engine      = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
		$site_orders = $engine->get_order_exports(
			array(
				'export_type'        => $this->export_type,
				'export_time_after'  => $this->export_time_after,
				'export_time_before' => $this->export_time_before,
				'site_filter'        => $this->site_filter,
				'order_status'       => $this->order_status,
				'row_format'         => $this->row_format,
				'export_fields'      => $this->export_fields,
			),
			$this->site_filter
		);

		if ( ! empty( $master_orders ) ) {
			$all_orders = array_merge(
				$all_orders,
				$master_orders
			);
		}

		if ( ! empty( $site_orders ) ) {
			foreach ( $site_orders as $site_order ) {
				if ( ! empty( $site_order['status'] )
					&& ! empty( $site_order['result'] )
					&& $site_order['status'] == 'success'
					&& is_array( $site_order['result'] )
					&& count( $site_order['result'] ) >= 1 ) {
					$all_orders = array_merge(
						$all_orders,
						$site_order['result']
					);
				}
			}
		}

		return $all_orders;
	}

	public function output() {
		$orders   = $this->fetch_orders();
		$all_rows = array();
		$row      = array();

		foreach ( $orders as $order_data ) {
			$order       = wc_get_order( $order_data['ID'] );
			$order_items = $order->get_items();
			$items       = array();

			/**
			 * Check if synced product export filter is set
			 */
			if ( apply_filters( "WOO_MSTORE/export_synced_products", false ) && get_post_meta( $order_data['ID'], '_woonet_has_synced_product', true ) != 'yes' ) {
                continue;
			}

			if ( $order_items ) {
				foreach ( $order_items as $order_item ) {
					$row = $this->get_item_fields( $order, $order_item );

					if ( 'row_per_order' == $this->row_format ) {
						if ( array_key_exists( 'order__order_items', $row ) ) {
							$items[] = $row['order__order_items'];
						}
					}
				}
			} else {
				$row = $this->get_item_fields( $order );

				if ( 'row_per_order' == $this->row_format ) {
					if ( array_key_exists( 'order__order_items', $row ) ) {
						$items[] = $row['order__order_items'];
					}
				}
			}

			if ( 'row_per_order' == $this->row_format ) {
				if ( array_key_exists( 'order__order_items', $row ) ) {
					$row['order__order_items'] = json_encode( $items );
				}
			}

			$all_rows[] = $row;
		}

		return $all_rows;
	}

	public function output_all_orders( $orders ) {
		// output the column headings
		$header = $this->get_header();
		$this->output_row( $header );

		foreach ( $orders as $order ) {
			$this->output_row( $order );
		}

		$this->save();
	}

	private function get_item_fields( $order, $order_item = null ) {
		if ( $order_item ) {
			$order_item_product  = new WC_Order_Item_Product( $order_item->get_id() );
			$order_item_product  = $order_item_product->get_product();
			$order_item_coupon   = new WC_Order_Item_Coupon( $order_item->get_id() );
			$order_item_fee      = new WC_Order_Item_Fee( $order_item->get_id() );
			$order_item_shipping = new WC_Order_Item_Shipping( $order_item->get_id() );
			$order_item_tax      = new WC_Order_Item_Tax( $order_item->get_id() );
		}

		$row  = array();
		$item = array();
		foreach ( $this->export_fields as $export_field => $export_field_column_name ) {
			list( $class_name, $field_name ) = explode( '__', $export_field );
			$get_field_value_function_name   = 'get_' . $field_name;

			if ( isset( ${ $class_name } ) && method_exists( ${$class_name}, $get_field_value_function_name ) ) {
				$value = $this->maybe_jsonify( ${$class_name}->$get_field_value_function_name() );
			} elseif ( method_exists( $this, $get_field_value_function_name ) ) {
				$value = $this->maybe_jsonify( $this->$get_field_value_function_name() );
			} else {
				$value = null;
			}

			if ( 0 === strpos( $class_name, 'order_item' ) ) {
				$item[ $export_field_column_name ] = $value;
			}

			if ( 'row_per_order' != $this->row_format || 0 !== strpos( $export_field, 'order_item_' ) ) {
				$row[ $export_field ] = $value;
			}
		}

		if ( array_key_exists( 'order__order_items', $this->export_fields ) ) {
			$row['order__order_items'] = 'row_per_order' == $this->row_format ? $item : json_encode( $item );
		}

		return $row;
	}

	private function output_row( $row = null ) {
		static $spreadsheet, $current_row = 0;

		if ( empty( $spreadsheet ) ) {
			$this->register_loader();

			$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		}

		if ( empty( $spreadsheet ) ) {
			return null;
		}

		if ( empty( $row ) ) {
			try {
				$this->set_html_headers();

				if ( $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadsheet, ucfirst( $this->export_type ) ) ) {
					$writer->save( 'php://output' );
				}

				die();
			} catch ( PhpOffice\PhpSpreadsheet\Writer\Exception $exception ) {
				return null;
			}
		} else {
			try {
				$current_row++;
				$spreadsheet->getActiveSheet()->fromArray( $row, null, 'A' . $current_row );
			} catch ( Exception $exception ) {
				return null;
			}
		}

		return $current_row;
	}

	private function save() {
		$this->output_row();
	}

	private function register_loader() {
		require_once dirname( WOO_MSTORE_PATH ) . '/include/dependencies/W8_Loader.php';

		$root = dirname( WOO_MSTORE_PATH ) . '/include/dependencies/PhpSpreadsheet';

		if ( $loader = new W8_Loader() ) {
			$loader->register();

			$loader->addPrefix( 'Psr\SimpleCache', dirname( WOO_MSTORE_PATH ) . '/include/dependencies/simple-cache' );

			$loader->addPrefix( 'PhpOffice\PhpSpreadsheet', dirname( WOO_MSTORE_PATH ) . '/include/dependencies/PhpSpreadsheet' );

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $root, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::SELF_FIRST,
				RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
			);
			foreach ( $iterator as $path => $dir ) {
				if ( $dir->isDir() ) {
					$prefix = 'PhpOffice\PhpSpreadsheet' . str_replace( DIRECTORY_SEPARATOR, '\\', str_replace( $root, '', $path ) );
					$loader->addPrefix( $prefix, $path );
				}
			}
		}
	}

	private function set_html_headers() {
		$filename  = 'network_orders_export';
		$filename .= empty( $this->export_time_after ) ? '' : '_from_' . date( 'Ymd', $this->export_time_after );
		$filename .= empty( $this->export_time_before ) ? '' : '_to_' . date( 'Ymd', $this->export_time_before );

		if ( 'csv' == $this->export_type ) {
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . $filename . '.csv' );
		} elseif ( 'xls' == $this->export_type ) {
			header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
			header( 'Content-Disposition: attachment;filename="' . $filename . '.xls"' );
		}
		header( 'Content-Encoding: UTF-8' );
		header( 'Cache-Control: max-age=0' );
		header( 'Cache-Control: max-age=1' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' ); // Date in the past
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); // always modified
		header( 'Cache-Control: cache, must-revalidate' ); // HTTP/1.1
		header( 'Pragma: public' ); // HTTP/1.0
	}

	/*
	================================================================================================================*/
	/*
	 Callback fields                                                                                                */
	/*================================================================================================================*/
	private function get_header() {
		$header = array();

		foreach ( $this->export_fields as $key => $value ) {
			if ( 'row_per_order' != $this->row_format || 0 !== strpos( $value, 'order_item_' ) ) {
				$header[ $key ] = $value;
			}
		}

		return $header;
	}

	private function maybe_jsonify( $value ) {
		if ( is_array( $value ) ) {
			$value = json_encode( $value );
		}

		return $value;
	}

	private function get_site_id() {
		return get_bloginfo( 'name' );
	}
}
