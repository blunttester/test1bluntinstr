<?php

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Gateway_Checkout_Sis extends WC_Payment_Gateway {
	public function __construct() {
		$this->id = 'checkout_sis';
		#$this->icon = WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) . '/logo.png';
		$this->has_fields = TRUE;
		$this->method_title = 'Checkout Shop in Shop';
		$this->method_description = '';

		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->merchant_id = $this->get_option( 'merchant_id' );
		$this->merchant_key = $this->get_option( 'merchant_key' );
		$this->checkout_language = $this->get_option( 'language' );
		$this->mode = $this->get_option( 'mode' );

		$this->commission_merchant_id = $this->get_option( 'commission_merchant_id' );
		$this->shipping_merchant_id = $this->get_option( 'shipping_merchant_id' );

		$this->checkout_api_url = 'https://api.checkout.fi'; 

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		add_action( 'woocommerce_api_wc_gateway_checkout_sis', array( $this, 'complete_payment' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
	}

	/**
	 * Settings form fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Enable Checkout', 'wc-checkout-sis' ),
				'type' => 'checkbox',
				'label' => __( 'Enable Checkout', 'wc-checkout-sis' ),
				'default' => 'yes'
			),
			'mode' => array(
				'title' => __( 'Payment mode', 'wc-checkout-sis' ),
				'type' => 'select',
				'default' => 'default',
				'options' => array(
					'default' => __( 'Payment page', 'wc-checkout-sis' ),
					'bypass' => __( 'Payment page bypass', 'wc-checkout-sis' ),
				)
			),
			'merchant_id' => array(
				'title' => __( 'Merchant ID', 'wc-checkout-sis' ),
				'type' => 'text',
				'default' => '',
			),
			'merchant_key' => array(
				'title' => __( 'Merchant key', 'wc-checkout-sis' ),
				'type' => 'text',
				'default' => '',
			),
			'language' => array(
				'title' => __( 'Language', 'wc-checkout-sis' ),
				'type' => 'select',
				'default' => 'FI',
				'options' => array(
					'FI' => __( 'Finnish', 'wc-checkout-sis' ),
					'SV' => __( 'Swedish', 'wc-checkout-sis' ),
					'EN' => __( 'English', 'wc-checkout-sis' )
				)
			),
			'title' => array(
				'title' => __( 'Title', 'wc-checkout-sis' ),
				'type' => 'text',
				'default' => __( 'Checkout', 'wc-checkout-sis' ),
				'description' => __( 'This controls the title which the user sees during checkout.', 'wc-checkout-sis' ),
				'desc_tip' => true,
			),
			'description' => array(
				'title' => __( 'Description', 'wc-checkout-sis' ),
				'type' => 'textarea',
				'default' => '',
				'description' => __( 'This controls the description which the user sees during checkout.', 'wc-checkout-sis' ),
				'desc_tip' => true,
			),
			'commission_merchant_id' => array(
				'title' => __( 'Commission merchant ID', 'wc-checkout-sis' ),
				'description' => __( 'Valid Checkout Finland merchant ID for commissions', 'wc-checkout-sis' ),
				'type' => 'text',
				'default' => '',
				'desc_tip' => true,
			),
			'shipping_merchant_id' => array(
				'title' => __( 'Shipping merchant ID', 'wc-checkout-sis' ),
				'description' => __( 'Valid Checkout Finland merchant ID for shipping costs', 'wc-checkout-sis' ),
				'type' => 'text',
				'default' => '',
				'desc_tip' => true,
			),
		);
	}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		return (bool) ($this->merchant_ids_set() && parent::is_available());
	}

	/**
	 * Checks if all merchants have Checkout merchant ID set
	 */
	protected function merchant_ids_set() {
		if ( ! WC()->cart ) {
			return TRUE;
		}

		$cart_contents = WC()->cart->get_cart();

		// Check that all merchants have merchant ID set
		$missing = array();
		foreach ($cart_contents as $key => $item) {
			$product = $item['data'];

			if ( $product ) {
				$merchant_id = $this->get_product_merchant_id( $product );

				if ( ! $merchant_id ) {
					$missing[] = $product;
				}
			}
		}

		if ( ! empty( $missing ) ) {
			foreach ( $missing as $product ) {
				$error_msg = sprintf( __( 'Vendor missing for %s', 'wc-checkout-sis' ), $product->get_title() );
				
				$author_id = $this->get_product_author_id( $product );
				if ( $author_id ) {
					$author = get_user_by( 'ID', $author_id );

					if ( $author ) {
						$error_msg = sprintf( __( 'Checkout merchant ID missing for user %s (fetched from product %s)', 'wc-checkout-sis' ), $author->user_login, $product->get_title() );
					}
				}

				$this->log( $error_msg );
			}
		}

		return empty( $missing );
	}

	/**
	 * Fields for preselecting payment method on checkout.
	 */
	public function payment_fields() {
		if ( 'bypass' === $this->mode && is_checkout() ) {
			$cart_total = intval( round( WC()->cart->get_total( 'edit' ) * 100 ) );

			$payment_methods = $this->get_payment_methods( $cart_total );
			$payment_methods = apply_filters( 'wc_checkout_sis_bypass_methods', $payment_methods );

			// Group payment methods by type
			$payment_methods_grouped = array();
			foreach ( $payment_methods as $method ) {
				if ( ! isset( $payment_methods_grouped[$method->group] ) ) {
					$payment_methods_grouped[$method->group] = array();
				}

				$payment_methods_grouped[$method->group][] = $method;
			}

			$groups = $this->payment_method_groups();

			ob_start();

			include 'views/bypass-payment-methods.html.php';

			$output = ob_get_clean();

			echo $output;
		} else {
			if ( $description = $this->get_description() ) {
				echo wpautop( wptexturize( $description ) );
			}
		}
	}

	/**
	 * Payment method groups
	 */
	private function payment_method_groups() {
		return array(
			'bank' => __( 'Online Banking', 'wc-checkout-sis' ),
			'mobile' => __( 'Mobile', 'wc-checkout-sis' ),
			'creditcard' => __( 'Credit / Debit Card', 'wc-checkout-sis' ),
			'credit' => __( 'Invoice / Part Payment', 'wc-checkout-sis' ),
			'other' => __( 'Other', 'wc-checkout-sis' ),
		);
	}

	/**
	 * Get payment methods for bypass
	 */
	private function get_payment_methods( $amount ) {
		// Make request
		$datetime = new \DateTime();
		$args = array(
			'body' => '',
			'headers' => array(
				'checkout-account' => $this->merchant_id,
				'checkout-algorithm' => 'sha256',
				'checkout-method' => 'GET',
				'checkout-nonce' => uniqid( true ),
				'checkout-timestamp' => $datetime->format('Y-m-d\TH:i:s.u\Z'),
				'cof-plugin-version' => sprintf( 'markup-fi-wc-checkout-shop-in-shop-%s', WC_CHECKOUT_SIS_VERSION ),
				'content-type' => 'application/json; charset=utf-8',
			),
			'timeout' => 3,
		);

		$args['headers']['signature'] = $this->calculate_hmac( $args['headers'], $args['body'] );

		$url = add_query_arg( array(
			'amount' => $amount,
		), $this->checkout_api_url . '/merchants/payment-providers' );

		$response = wp_remote_get( $url, $args );

		if ( ! is_wp_error( $response ) ) {
			$response_code = (string) wp_remote_retrieve_response_code( $response );

			if ( $response_code === '200' ) {
				// Validate that request originated from Checkout.fi (signature is valid)
				$headers_obj = wp_remote_retrieve_headers( $response );
				$headers = $headers_obj->getAll();
				$body = wp_remote_retrieve_body( $response );
				$body_obj = json_decode( $body );

				$response_hmac = $this->calculate_hmac( $headers, $body );
				if ( $response_hmac === $headers['signature'] ) {
					return $body_obj;
				}
			}
		}

		return array();
	}

	/**
	 * Process payment by redirecting to payment page
	 *
	 * @param int @order_id
	 * @return array|void
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( 'bypass' === $this->mode ) {
			$this->save_preselected_payment_method( $order_id );
			$bypass_method = get_post_meta( $order_id, '_wc_checkout_sis_preselected_method', true );

			if ( ! empty( $bypass_method ) ) {
				return array(
					'result' => 'success',
					'redirect' => $order->get_checkout_payment_url( true )
				);
			}
		}

		try {
			$payment = $this->create_payment( $order );
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
			return;
		}

		return array(
			'result' => 'success',
			'redirect' => $payment->href,
		);
	}

	/**
	 * Create payment via Checkout API
	 */
	private function create_payment( $order ) {
		$body = $this->payment_args( $order );

		// Store request to order meta
		update_post_meta( $order->get_id(), '_wc_checkout_sis_payment_request', $body );

		// Validate that totals match
		if ( ! $this->validate_totals( $body, $order ) ) {
			$order->add_order_note( sprintf( __( 'Error processing payment: totals do not match (%d vs %d cents)', 'wc-checkout-sis' ), $this->get_line_item_total( $body ), intval( round( $order->get_total() * 100 ) ) ) );

			throw new Exception( __( 'Unable to complete the payment. Please try again or choose another payment method. (1)', 'wc-checkout-sis' ) );
		}

		// Validate that all items include merchant ID
		foreach ( $body['items'] as $key => $item ) {
			if ( empty( $item['merchant'] ) ) {
				$order->add_order_note( sprintf( __( "Error processing payment: %s doesn't include merchant ID", 'wc-checkout-sis' ), $item['description'] ) );

				throw new Exception( __( 'Unable to complete the payment. Please try again or choose another payment method. (1)', 'wc-checkout-sis' ) );
			}
		}

		$datetime = new \DateTime();

		$args = array(
			'body' => json_encode( $body ),
			'headers' => array(
				'checkout-account' => $this->merchant_id,
				'checkout-algorithm' => 'sha256',
				'checkout-method' => 'POST',
				'checkout-nonce' => uniqid( true ),
				'checkout-timestamp' => $datetime->format('Y-m-d\TH:i:s.u\Z'),
				'cof-plugin-version' => sprintf( 'markup-fi-wc-checkout-shop-in-shop-%s', WC_CHECKOUT_SIS_VERSION ),
				'content-type' => 'application/json; charset=utf-8',
			),
			'timeout' => 15
		);

		$args['headers']['signature'] = $this->calculate_hmac( $args['headers'], $args['body'] );

		$url = $this->checkout_api_url . '/payments';

		$response = wp_remote_post( $url, $args );

		// Check response
		if ( ! is_wp_error( $response ) ) {
			$response_code = (string) wp_remote_retrieve_response_code( $response );

			if ( $response_code === '201' ) {
				// Validate that response originated from Checkout.fi (signature is valid)
				$headers_obj = wp_remote_retrieve_headers( $response );
				$headers = $headers_obj->getAll();
				$body = wp_remote_retrieve_body( $response );
				$body_obj = json_decode( $body );

				$response_hmac = $this->calculate_hmac( $headers, $body );
				if ( $response_hmac === $headers['signature'] ) {
					return $body_obj;
				}
			} else if ( $response_code === '400' || $response_code === '500' ) {
				$body = wp_remote_retrieve_body( $response );
				$body_obj = json_decode( $body );

				if ( is_object( $body_obj ) && isset( $body_obj->status ) && $body_obj->status === 'error' && isset( $body_obj->message ) ) {
					$order->add_order_note( sprintf( __( 'Error processing payment: %s', 'wc-checkout-sis' ), $body_obj->message ) );
				} else {
					$order->add_order_note( sprintf( __( 'Error processing payment: %s', 'wc-checkout-sis' ), 'Unknown error' ) );
				}
			}
		}

		throw new Exception( __( 'Unable to complete the payment. Please try again or choose another payment method. (2)', 'wc-checkout-sis' ) );
	}

	/**
	 * Save preselected payment method if bypass is enabled.
	 */
	public function save_preselected_payment_method( $order_id ) {
		$method = false;

		if ( isset( $_REQUEST['wc_checkout_sis_preselected_method'] ) ) {
			$method = $_REQUEST['wc_checkout_sis_preselected_method'];
		}

		update_post_meta( $order_id, '_wc_checkout_sis_preselected_method', $method );
	}

	/**
	 * Calculate hmac for request
	 */
	private function calculate_hmac( $params, $body = '' ) {
		// Keep only checkout- params, more relevant for response validation. Filter query
		// string parameters the same way - the signature includes only checkout- values.
		$included_keys = array_filter( array_keys( $params ), function ( $key ) {
			return preg_match( '/^checkout-/', $key );
		});
	
		// Keys must be sorted alphabetically
		sort( $included_keys, SORT_STRING );
	
		$hmac_payload = array_map(
			function ( $key ) use ( $params ) {
				return join( ':', array( $key, $params[$key] ) );
			},
			$included_keys
		);

		array_push( $hmac_payload, $body );
	
		return hash_hmac( 'sha256', join( "\n", $hmac_payload ), $this->merchant_key );
	}

	/**
	 * Payment request body
	 */
	private function payment_args( $order ) {
		$body = array();

		$body['stamp'] = apply_filters( 'wc_checkout_sis_stamp', $order->get_id() . '-' . time(), $order );
		$body['reference'] = strval( $order->get_id() );
		$body['amount'] = intval( round( $order->get_total() * 100 ) );
		$body['currency'] = 'EUR';
		$body['language'] = $this->get_language();
		$body['orderId'] = strval( $order->get_id() );
		$body['items'] = $this->get_item_args( $order );
		$body['customer'] = array(
			'email' => $order->get_billing_email(),
			'firstName' => $order->get_billing_first_name(),
			'lastName' => $order->get_billing_last_name(),
			'phone' => $order->get_billing_phone(),
			'vatId' => '',
		);
		$body['deliveryAddress'] = array(
			'streetAddress' => trim( sprintf( '%s %s', $order->get_shipping_address_1(), $order->get_shipping_address_2() ) ),
			'postalCode' => $order->get_shipping_postcode(),
			'city' => $order->get_shipping_city(),
			'county' => $order->get_shipping_state(),
			'country' => $order->get_shipping_country(),
		);
		$body['invoicingAddress'] = array(
			'streetAddress' => trim( sprintf( '%s %s', $order->get_billing_address_1(), $order->get_billing_address_2() ) ),
			'postalCode' => $order->get_billing_postcode(),
			'city' => $order->get_billing_city(),
			'county' => $order->get_billing_state(),
			'country' => $order->get_billing_country(),
		);
		$body['redirectUrls'] = array(
			'success' => $this->get_api_url( $order ),
			'cancel' => $this->get_api_url( $order ),
		);
		$body['callbackUrls'] = array(
			'success' => $this->get_api_url( $order ),
			'cancel' => $this->get_api_url( $order ),
		);
		$body['callbackDelay'] = 10; // Avoid simultaneous requests from both customer and Checkout Finland server to the thank you page which would lead to duplicate payment confirmations

		// Allow other plugins to alter this data
		$body = apply_filters( 'wc_checkout_sis_payment_args', $body, $order );

		return $body;
	}

	/**
	 * Payment return URL
	 */
	public function get_api_url( $order ) {
		$url = WC()->api_request_url( 'WC_Gateway_Checkout_Sis' );
		$url = add_query_arg( 'order_id', $order->get_id(), $url );

		return $url;
	}

	/**
	 * Get Checkout.fi payment page language
	 */
	private function get_language() {
		if ( 'auto_wpml' === $this->checkout_language && function_exists( 'icl_object_id' ) && defined( 'ICL_LANGUAGE_CODE' ) ) {
			switch (ICL_LANGUAGE_CODE) {
				case 'fi':
					return 'FI';
				case 'sv':
					return 'SV';
				case 'en':
					return 'EN';
				default:
					return 'FI';
			}
		}

		return $this->checkout_language;
	}

	/**
	 * Get order items
	 */
	private function get_item_args( $order ) {
		$line_items = array();
		$round = false;

		// Add product line items
		foreach ( $order->get_items() as $item ) {
			$product = $order->get_product_from_item( $item );
			$merchant_id = $this->get_product_merchant_id( $product );

			$total_without_tax = $order->get_item_total( $item, false, false );
			$tax_percent = $total_without_tax ? ( $order->get_item_tax( $item, false ) / $total_without_tax ) * 100 : 0;

			$line_items[] = array(
				'unitPrice' => intval( round( $order->get_line_total( $item, true, $round ) * 100 ) ),
				'units' => 1,
				'vatPercentage' => round( floatval( $tax_percent ), 0 ),
				'productCode' => ( false !== $product ) ? $product->get_sku() : '',
				'deliveryDate' => apply_filters( 'wc_checkout_sis_item_delivery_date', date( 'Y-m-d' ) ),
				'description' => sprintf( '%d x %s', $item->get_quantity(), substr( $item->get_name(), 0, 995 ) ),
				'stamp' => sprintf( '%d-%s', $item->get_id(), uniqid() ),
				'reference' => sprintf( '%d', $item->get_id() ),
				'merchant' => strval( $merchant_id ),
				'commission' => array(
					'merchant' => $this->commission_merchant_id,
					'amount' => $this->get_commission( $order, $product, $item ),
				),
			);
		}

		// Add shipping line items
		foreach ( $order->get_items( array( 'shipping' ) ) as $key => $item ) {
			// Dokan Pro shipping costs handling
			if ( ! $this->shipping_merchant_id && $this->dokan_shipping_enabled( $order ) ) {
				$seller_id = FALSE;
				foreach ( $item->get_meta_data() as $meta ) {
					if ( $meta->key == 'seller_id' ) {
						$seller_id = $meta->value;
						break;
					}
				}

				$title = $item->get_name() . " - " . $this->get_shop_name_by_user_id( $seller_id, 'dokan' );

				$total_without_tax = $order->get_item_total( $item, false, false );
				$tax_percent = $total_without_tax ? ( $order->get_item_tax( $item, false ) / $total_without_tax ) * 100 : 0;

				$line_items[] = array(
					'unitPrice' => intval( round( $order->get_line_total( $item, true, $round ) * 100 ) ),
					'units' => 1,
					'vatPercentage' => round( floatval( $tax_percent ), 0 ),
					'productCode' => '',
					'deliveryDate' => apply_filters( 'wc_checkout_sis_item_delivery_date', date( 'Y-m-d' ) ),
					'description' => $title,
					'stamp' => sprintf( '%d-%s', $item->get_id(), uniqid() ),
					'reference' => sprintf( '%d', $item->get_id() ),
					'merchant' => strval( $this->get_merchant_id_by_user_id( $seller_id ) ),
					'commission' => array(
						'merchant' => $this->commission_merchant_id,
						'amount' => 0,
					),
				);
			}
			// WC Vendors Pro shipping costs handling
			else if ( ! $this->shipping_merchant_id && isset( $item['method_id'] ) && 'wcv_pro_vendor_shipping' === $item['method_id'] && class_exists( 'WCV_Vendors' ) ) {
				// Find vendor ID
				$seller_id = $this->get_wc_vendors_shipping_seller_id( $item );

				$total_without_tax = $order->get_item_total( $item, false, false );
				$tax_percent = $total_without_tax ? ( $order->get_item_tax( $item, false ) / $total_without_tax ) * 100 : 0;

				$line_items[] = array(
					'unitPrice' => intval( round( $order->get_line_total( $item, true, $round ) * 100 ) ),
					'units' => 1,
					'vatPercentage' => round( floatval( $tax_percent ), 0 ),
					'productCode' => '',
					'deliveryDate' => apply_filters( 'wc_checkout_sis_item_delivery_date', date( 'Y-m-d' ) ),
					'description' => $item->get_name(),
					'stamp' => sprintf( '%d-%s', $item->get_id(), uniqid() ),
					'reference' => sprintf( '%d', $item->get_id() ),
					'merchant' => strval( $this->get_merchant_id_by_user_id( $seller_id ) ),
					'commission' => array(
						'merchant' => $this->commission_merchant_id,
						'amount' => 0,
					),
				);
			}
			// Regular shipping item(s)
			else {
				$total_with_tax = $order->get_item_total( $item, false, false );
				$tax_percent = $total_with_tax ? ( $order->get_item_tax( $item, false ) / $total_with_tax ) * 100 : 0;
	
				$line_items[] = array(
					'unitPrice' => intval( round( $order->get_line_total( $item, true, $round ) * 100 ) ),
					'units' => 1,
					'vatPercentage' => round( floatval( $tax_percent ), 0 ),
					'productCode' => '',
					'deliveryDate' => apply_filters( 'wc_checkout_sis_item_delivery_date', date( 'Y-m-d' ) ),
					'description' => $item->get_name(),
					'stamp' => sprintf( '%d-%s', $item->get_id(), uniqid() ),
					'reference' => sprintf( '%d', $item->get_id() ),
					'merchant' => strval( $this->shipping_merchant_id ),
					'commission' => array(
						'merchant' => $this->commission_merchant_id,
						'amount' => 0,
					),
				);
			}
		}

		// Ensure units and unitPrice are integers
		foreach ( $line_items as $key => $item ) {
			$line_items[$key]['unitPrice'] = intval( $item['unitPrice'] );
			$line_items[$key]['units'] = intval( $item['units'] );
		}

		// Remove commission data if commission merchant ID is not set
		foreach ( $line_items as $key => $item ) {
			if ( isset( $item['commission'] ) && empty( $item['commission']['merchant'] ) ) {
				unset( $line_items[$key]['commission'] );
			}
		}

		// Allow other plugins to modify line items
		$line_items = apply_filters( 'wc_checkout_sis_line_item_args', $line_items, $order, $round );

		return $line_items;
	}

	/**
	 * Find vendor ID for WC Vendors shipping item
	 * 
	 * WC Vendors doesn't provide this easy way so this will take some
	 * work
	 */
	private function get_wc_vendors_shipping_seller_id( $item ) {
		foreach ( $item->get_meta_data() as $meta ) {
			if ( $meta->key == 'vendor_costs' && is_array( $meta->value ) && isset( $meta->value['items'] ) && ! empty( $meta->value['items'] ) ) {
				foreach ( $meta->value['items'] as $vendor_item ) {
					if ( is_array( $vendor_item ) && isset( $vendor_item['product_id'] ) && ! empty( $vendor_item['product_id'] ) ) {
						$product = wc_get_product( $vendor_item['product_id'] );

						if ( $product ) {
							return $this->get_product_author_id( $product );
						}
					}
				}
			}
		}

		return FALSE;
	}

	/**
	 * Calculate line item total
	 */
	private function get_line_item_total( $body ) {
		$total = 0;
		foreach ( $body['items'] as $line_item ) {
			$total += $line_item['unitPrice'] * $line_item['units'];
		}

		return $total;
	}

	/**
	 * Returns difference between line item totals and order total
	 */
	private function totals_diff( $body, $order ) {
		$line_item_total = $this->get_line_item_total( $body );
		$order_total = intval( round( $order->get_total() * 100 ) );

		return $line_item_total - $order_total;
	}

	/**
	 * Validate that line items total and order total matches
	 */
	private function validate_totals( $body, $order ) {
		$diff = $this->totals_diff( $body, $order );

		// Allow rounding error of 10 cents
		// Rounding errors more than this are very likely caused by misconfigured gift card
		// plugins or some other plugins altering line items
		$epsilon = 10;

		return ! ( abs( $diff ) > $epsilon );
	}

	/**
	 * Receipt page.
	 *
	 * This page shows payment method forms.
	 *
	 * @param int $order_id
	 */
	public function receipt_page( $order_id ) {
		$order = wc_get_order( $order_id );

		try {
			$payment = $this->create_payment( $order );
		} catch ( Exception $e ) {
			wc_print_notice( $e->getMessage(), 'error' );

			return;
		}

		// Find preselected payment method
		$bypass_method = get_post_meta( $order_id, '_wc_checkout_sis_preselected_method', true );
		$bypass_provider = false;
		foreach ( $payment->providers as $key => $provider ) {
			if ( $provider->id === $bypass_method ) {
				$bypass_provider = $provider;
				break;
			}
		}

		ob_start();

		include 'views/redirect-form.html.php';

		$output = ob_get_clean();

		echo $output;
	}

	/**
	 * Complete payment after returning from the offsite payment provider's website.
	 *
	 * After visitor has completed or cancelled the payment, he will
	 * be redirected to this page from Checkout. The request will be validated
	 * and visitor redirected to the thank you page.
	 *
	 * @return void
	 */
	public function complete_payment() {
		@ob_clean();
		header( 'HTTP/1.1 200 OK' );

		$signature = filter_input( INPUT_GET, 'signature' );

		$calculated_signature = $this->calculate_hmac( $_GET, '' );

		if ( $signature !== $calculated_signature ) {
			wp_die( __( 'Signature validation failed.', 'wc-checkout-sis' ) );
			exit;
		}

		$order_id = filter_input( INPUT_GET, 'checkout-reference' );
		$order = false;

		if ( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( $order ) {
				$status = filter_input( INPUT_GET, 'checkout-status' );

				if ( $status === 'ok' ) {
					if ( ! $order->has_status( array( 'processing', 'completed' ) ) ) {
						$txn_id = filter_input( INPUT_GET, 'checkout-transaction-id' );

						$order->payment_complete( $txn_id );
						$order->add_order_note( sprintf( __( 'Checkout.fi payment completed. Transaction ID: %s', 'wc-checkout-sis' ), $txn_id ) );
					}

					WC()->cart->empty_cart();
				} else if ( $status === 'pending' || $status === 'delayed' ) {
					$order->update_status( 'on-hold', __( 'Awaiting payment', 'wc-checkout-sis' ) );
					$order->add_order_note( sprintf( __( 'Checkout.fi payment PENDING. Transaction ID: %s', 'wc-checkout-sis' ), $txn_id ) );

					wc_maybe_reduce_stock_levels( $order_id );

					WC()->cart->empty_cart();
				} else {
					$order->update_status( 'failed' );
				}
			}
		}

		// Dont provide order object for return URL if the request originated from Checkout Finland server.
		// Google Analytics plugin uses Javascript for conversion tracking which Checkout Finland server doesn't
		// support so we want to redirect Checkout Finland server to anonymous (non-order) return page. Otherwise
		// conversion tracking wont work correctly.
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && $_SERVER['HTTP_USER_AGENT'] === 'Checkout Finland payment registration' ) {
			$url = $this->get_return_url();
		} else {
			$url = $this->get_return_url( $order );
		}

		wp_redirect( $url );
		exit;
	}

	/**
	 * Get Checkout merchant ID for product
	 */
	private function get_product_merchant_id( $product ) {
		if ( $product ) {
			$author_id = $this->get_product_author_id( $product );

			if ( $author_id ) {
				return get_the_author_meta( 'wc_checkout_sis_merchant_id', $author_id );
			}
		}

		return FALSE;
	}

	/**
	 * Get author ID for product
	 */
	private function get_product_author_id( $product ) {
		if ( $product ) {
			// If product is variation, the author must be checked from the parent product
			if ( 'variation' === $product->get_type() ) {
				$parent_product_id = $product->get_parent_id();

				if ( ! empty( $parent_product_id ) ) {
					return get_post_field( 'post_author', $parent_product_id, 'raw' );
				}
			} else {
				return get_post_field( 'post_author', $product->get_id(), 'raw' );
			}
		}

		return FALSE;
	}

	/**
	 * Get Checkout merchant ID by user ID
	 */
	private function get_merchant_id_by_user_id( $user_id ) {
		if ( $user_id ) {
			return get_the_author_meta( 'wc_checkout_sis_merchant_id', $user_id );
		}

		return NULL;
	}

	/**
	 * Get shop name by user ID
	 */
	private function get_shop_name_by_user_id( $user_id, $plugin = 'wc_vendors' ) {
		if ( 'wc_vendors' == $plugin ) {
			return get_user_meta( $user_id, 'pv_shop_name', true );
		} else if ( 'dokan' == $plugin ) {
			return get_user_meta( $user_id, 'dokan_store_name', true );
		}

		return '';
	}

	/**
	 * Calculate commission
	 */
	private function get_commission( $order, $product, $item ) {
		if ( $product ) {
			$author_id = $this->get_product_author_id( $product );

			if ( $author_id ) {
				$type = get_the_author_meta( 'wc_checkout_sis_merchant_commission_type', $author_id );
				$value = get_the_author_meta( 'wc_checkout_sis_merchant_commission_value', $author_id );
				$value = floatval( trim( str_replace( ',', '.', $value ) ) );

				if ( $type == 'percentage' ) {
					$base = round( $order->get_line_total( $item, true ) * 100 );
					$commission = $base * ( $value / 100 );
					$commission = intval( round( $commission ) );
					return $commission;
				} else if ( $type == 'fixed' ) {
					$commission = intval( round( $value * 100 ) );
					return $commission;
				} else if ( $type == 'fixed_product' ) {
					$commission = intval( round( $value * 100 ) * $item->get_quantity() );
					return $commission;
				}
			}
		}

		return 0;
	}

	/**
	 * Checks if Dokan shipping is enabled
	 */
	private function dokan_shipping_enabled( $order ) {
		$shipping_items = $order->get_items( array( 'shipping' ) );

		if ( empty( $shipping_items ) ) {
			return FALSE;
		}

		foreach ( $shipping_items as $shipping_item ) {
			if ( $shipping_item['method_id'] !== 'dokan_product_shipping' && $shipping_item['method_id'] !== 'dokan_vendor_shipping' ) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Logger
	 */
	private function log( $msg, $level = 'debug' ) {
		$logger = wc_get_logger();
		$context = array( 'source' => 'wc-checkout-sis' );

		call_user_func( array( $logger, $level ), $msg, $context );
	}
}
