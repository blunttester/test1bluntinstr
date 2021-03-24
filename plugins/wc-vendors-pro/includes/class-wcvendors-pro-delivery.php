<?php
/**
 * Allow customer marks order received.
 *
 * @since 1.7.0
 * @package WCVendors_Pro
 */

/**
 * Class: WCVendors_Pro_Delivery.
 */
class WCVendors_Pro_Delivery {
	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		if ( get_queried_object_id() != wc_get_page_id( 'myaccount' ) ) {
			return;
		}

		$assets_url = plugin_dir_url( __FILE__ ) . 'assets/';

		wp_enqueue_script( 'wcv-delivery', $assets_url . 'js/delivery.min.js', array( 'jquery' ), '1.0.0', true );
		wp_localize_script(
			'wcv-delivery',
			'WCVDeliveryI18n',
			array(
				'confirm'      => __( 'Are you sure to mark this order received?', 'wcvendors-pro' ),
				'buttonText'   => __( 'Mark received', 'wcvendors-pro' ),
				'wcv_nonce'    => wp_create_nonce( 'wcv-mark-order-received' ),
				'receivedText' => __( 'Received', 'wcvendors-pro' ),
			)
		);

	}

	/**
	 * Add class to order item in My account > Order detail for sort product
	 * by vendor.
	 *
	 * @param string                $class Item class.
	 * @param WC_Order_Item_Product $item Product item instance.
	 * @param WC_Order              $order   WC_Order instance.
	 */
	public function add_class_to_order_item( $class, $item, $order ) {
		$vendor_id = WCV_Vendors::get_vendor_from_product( $item->get_product_id() );
		$received  = (array) $order->get_meta( '_wcv_order_received' );
		$class    .= ' vendor-' . $vendor_id;
		$class    .= ' order-' . $order->get_id();

		if ( in_array( (int) $vendor_id, $received, true ) ) {
			$class .= ' received';
		}

		if (
			'full' != $this->get_delivery_status( $order )
			&& $this->cant_mark_order_received( $order )
		) {
			$class .= ' cant-mark-received';
		}

		return $class;
	}

	/**
	 * Add mark received button to WooCommerce My Orders list.
	 *
	 * @param array    $actions My orders list actions.
	 * @param WC_Order $order   WC_Order instance.
	 */
	public function add_orders_list_action( $actions, $order ) {
		if ( $this->cant_mark_order_received( $order ) ) {
			return $actions;
		}

		$vendors = $this->get_all_vendors_from_order( $order );

		if ( count( $vendors ) > 1 ) {
			$url = $order->get_view_order_url();
		} else {
			$vendor_id    = array_keys( $vendors );
			$vendor_id    = $vendors[0];
			$redirect_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; //phpcs:disable
			$url          = add_query_arg(
				array(
					'order'        => $order->get_id(),
					'vendor'       => $vendor_id,
					'wcv_nonce'    => wp_create_nonce( 'wcv-mark-order-received' ),
					'redirect_url' => rawurlencode( $redirect_url ),
				),
				wc_get_account_endpoint_url( 'orders' )
			);
		}

		$actions['wcv-mark-order-received'] = array(
			'name' => __( 'Mark received', 'wcvendors-pro' ),
			'url'  => esc_url( $url ),
		);

		return $actions;
	}

	/**
	 * Mark order received by adding new order meta and add order note.
	 */
	public function mark_received() {
		if (
			! isset( $_GET['wcv_nonce'] )
			|| ! wp_verify_nonce(
				sanitize_key( $_GET['wcv_nonce'] ),
				'wcv-mark-order-received'
			)
			|| ! isset( $_GET['order'] )
			|| ! isset( $_GET['vendor'] )
		) {
			return false;
		}

		$order       = wc_get_order( sanitize_key( $_GET['order'] ) );
		$vendor_id   = sanitize_key( $_GET['vendor'] );
		$vendor_name = WCV_Vendors::get_vendor_shop_name( $vendor_id );

		if ( ! $vendor_id || ! $order || $this->cant_mark_order_received( $order ) ) {
			return false;
		}

		$received   = (array) $order->get_meta( '_wcv_order_received' );
		$received[] = (int) $vendor_id;
		$received   = array_filter( $received, 'is_int' );

		$order->update_meta_data( '_wcv_order_received', array_unique( $received ) );
		/* translators: %s is the vendor shop name */
		$order->add_order_note( sprintf( __( 'The customer received items from %s.', 'wcvendors-pro' ), $vendor_name ) );

		$vendors = $this->get_all_vendors_from_order( $order );

		if( count($vendors) == count($received)) {
			$order->set_status( 'completed' );
		}

		$order->save();

		/* translators: %d is the order id */
		wc_add_notice( sprintf( __( 'Marked the order #%d as received.', 'wcvendors-pro' ), $order->get_id() ) );

		$url = isset( $_GET['redirect_url'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_url'] ) ) : '';
		$url = $url ? urldecode( $url ) : '';

		if ( $url && wp_safe_redirect( $url ) ) {
			exit;
		}

		return true;
	}

	/**
	 * Print received text in the order list of my account page.
	 *
	 * @param WC_Order $order WC_Order instance.
	 */
	public function print_received_text( $order ) {
		$status = $this->get_delivery_status( $order );

		echo esc_html( wc_get_order_status_name( $order->get_status() ) );

		if ( 'none' == $status ) {
			return;
		}

		printf(
			'<small style="display: block" class="received">%s<small>',
			esc_html( $this->get_status_name( $status ) )
		);
	}

	/**
	 * Print received text in the order list of my account page.
	 *
	 * @param object   $new_row Order list row object.
	 * @param object   $_order  Item of array returned by get_orders2.
	 * @param WC_Order $order   WC_Order instance.
	 */
	public function print_received_text_for_vendor( $new_row, $_order, $order ) {
		$status = $this->get_delivery_status( $order );

		if ( 'none' == $status ) {
			return $new_row;
		}

		$received  = (array) $order->get_meta( '_wcv_order_received' );
		$vendor_id = get_current_user_id();

		if ( in_array( $vendor_id, $received ) && 'full' !== $status ) {
			$status = 'vendor';
		}

		$new_row->status .= sprintf(
			'<small style="display: block" class="received %1$s">%2$s<small>',
			$status,
			$this->get_status_name( $status )
		);

		return $new_row;
	}

	/**
	 * Return translated delivery status name.
	 *
	 * @param string $status Status key.
	 * @return string
	 */
	public function get_status_name( $status ) {
		$map = array(
			'full'    => __( 'Received', 'wcvendors-pro' ),
			'partial' => __( 'Partial received', 'wcvendors-pro' ),
			'vendor'  => __( 'Received & Completed', 'wcvendors-pro' ),
			'none'    => '',
		);

		return $map[ $status ];
	}

	/**
	 * Check if order can be marked as received.
	 *
	 * @param WC_Order $order WC_Order instance.
	 */
	private function cant_mark_order_received( $order ) {
		return 'full' == $this->get_delivery_status( $order )
			|| $order->get_total_refunded() > 0
			|| ! in_array( $order->get_status(), array( 'processing', 'completed' ), true )
			|| count( $order->get_items( 'shipping' ) ) === 0;
	}

	/**
	 * Get all vendor of and order including the admin.
	 *
	 * @param WC_Order $order WC_Order instance.
	 */
	private function get_all_vendors_from_order( $order ) {
		$vendors = [];
		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$vendors[]  = WCV_Vendors::get_vendor_from_product( $product_id );
		}
		return array_unique( $vendors );
	}

	/**
	 * Return delivery status for order. Including full|partial|none.
	 *
	 * @param WC_Order $order   WC_Order instance.
	 *
	 * @return string
	 */
	private function get_delivery_status( $order ) {
		$received = (array) $order->get_meta( '_wcv_order_received' );
		$received = array_filter( $received );

		if ( empty( $received ) ) {
			return 'none';
		}

		$status  = 'full';
		$vendors = $this->get_all_vendors_from_order( $order );

		foreach ( $vendors as $vendor ) {
			if ( ! in_array( (int) $vendor, $received, true ) ) {
				$status = 'partial';
			}
		}
		return $status;
	}
}
