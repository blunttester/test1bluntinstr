<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Network Bulk Updater
 *
 * @class   WOO_MSTORE_SINGLE_SEQUENTIAL_ORDER_NUMBER
 * @since   4.1.3
 */
class WOO_MSTORE_SINGLE_SEQUENTIAL_ORDER_NUMBER {

	/**
	 * Options
	 */
	private $options = array();

	/**
	 * __construct
	 *
	 * @return void
	 */
	function __construct() {
		$this->options = new WOO_MSTORE_OPTIONS_MANAGER();

		// action hooks
		add_action( 'init', array( $this, 'init' ), 10 );
	}

	/**
	 * init
	 *
	 * @return void
	 */
	function init() {
		/**
		 * Do not hook actions of sequential order is disabled.
		 */
		if ( $this->options->get( 'sequential-order-numbers' ) != 'yes' ) {
			return;
		}

		/**
		 * When order is being inserted, add the order number.
		 */
		add_action( 'wp_insert_post', array( $this, 'wp_insert_post' ), 10, 2 );

		/**
		 * Rewrite the WooCommerce Order Number on the child site.
		 */
		add_filter( 'woocommerce_order_number', array( $this, 'get_order_number' ), 10, 2 );

		/**
		 * Rewrite the tracking order number
		 */
		add_filter( 'woocommerce_shortcode_order_tracking_order_id', array( $this, 'woocommerce_shortcode_order_tracking_order_id' ), 10, 1 );

		/**
		 * Add sequential order to search field.
		 */
		add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'add_sequential_shop_order_search_fields' ) );

		/**
		 * Send order number to the child sites
		 *
		 * @return void
		 */
		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			add_action( 'wp_ajax_nopriv_master_send_sequential_order', array( $this, 'master_send_sequential_order' ), 10, 0 );
		}
	}


	/**
	 * network_update_order_numbers
	 *
	 * @return void
	 */
	public function network_update_order_numbers( $number ) {
		return update_option( 'woonet_sequential_order_number', $number );
	}


	/**
	 * Retirve next order_number from the master site.
	 */
	public function get_next_network_order_number() {
		if ( $this->options->get( 'sequential-order-numbers' ) != 'yes' ) {
			return null;
		}

		$next_number = null;

		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			$next_number = get_option( 'woonet_sequential_order_number' );

			if ( $next_number >= 1 ) {
				$this->network_update_order_numbers( $next_number + 1 );
				return $next_number;
			}

			$next_number = $this->get_highest_order_number_from_master();
			$this->network_update_order_numbers( $next_number + 1 );

		} else {
			$_engine      = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
			$order_number = $_engine->request_master( 'master_send_sequential_order' );

			if ( ! empty( $order_number['result'] ) && $order_number['result'] >= 1 ) {
				$next_number = (int) $order_number['result'];
			}
		}

		return $next_number;
	}

	/**
	 * Get the highest order number from master site.
	 */
	private function get_highest_order_number_from_master() {
		global $wpdb;
		$high = $wpdb->get_var( "SELECT MAX(`id`) from {$wpdb->prefix}posts" );
		return $high + 1;
	}


	/**
	 * add_order_number
	 *
	 * @param mixed $post_id
	 * @return void
	 */
	public function add_order_number( $post_id ) {
		// check if there's already an order_number
		$order_number = get_post_meta( $post_id, '_order_number', true );

		if ( $order_number > 0 ) {
			return $order_number;
		}

		$network_order_number = $this->get_next_network_order_number();

		update_post_meta( $post_id, '_order_number', $network_order_number );

		return $network_order_number;
	}


	/**
	 * woocommerce_process_shop_order_meta
	 *
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public function woocommerce_process_shop_order_meta( $post_id, $post ) {
		if ( $post->post_type != 'shop_order' ) {
			return;
		}

		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$this->add_order_number( $post_id );

	}

	/**
	 * wp_insert_post
	 *
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public function wp_insert_post( $post_id, $post ) {
		if ( $post->post_type != 'shop_order' ) {
			return;
		}

		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( $this->options->get( 'sequential-order-numbers' ) != 'yes' ) {
			return;
		}

		$this->add_order_number( $post_id );
	}


	/**
	 * Get the order number for the current order.
	 */
	public function get_order_number( $order_number, $order ) {
		if ( $this->options->get( 'sequential-order-numbers' ) != 'yes' ) {
			return $order_number;
		}

		$_order_number = get_post_meta( $order_number, '_order_number', true );

		if ( $_order_number > 0 ) {
			return $_order_number;
		}

		remove_filter( 'woocommerce_order_number', array( $this, 'get_order_number' ), 10, 2 );

		$_order_nubmer = $order->get_order_number();

		add_filter( 'woocommerce_order_number', array( $this, 'get_order_number' ), 10, 2 );

		/*
		 * If sequential order number is set, return the number.
		 */
		if ( ! empty( $_order_nubmer ) ) {
			return $this->format_order_number( $_order_nubmer );
		}

		return $order_number;
	}

	/**
	 * Formats the order number, including adding prefix if necessary.
	 */
	public function format_order_number( $_order_nubmer ) {
		return $_order_nubmer;
	}

	/**
	 * add_sequential_shop_order_search_fields
	 *
	 * @param mixed $search_fields
	 * @return void
	 */
	public function add_sequential_shop_order_search_fields( $search_fields ) {
		$search_fields[] = '_order_number';
		return $search_fields;
	}

	/**
	 * woocommerce_shortcode_order_tracking_order_id
	 *
	 * @param mixed $order_id
	 * @return void
	 */
	public function woocommerce_shortcode_order_tracking_order_id( $order_id ) {
		if ( $this->options->get( 'sequential-order-numbers' ) != 'yes' ) {
			return $order_number;
		}

		global $wpdb;

		$order_number = $wpdb->get_var( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key='_order_number' AND meta_value='{$order_id}'" );

		if ( ! empty( $order_number ) ) {
			return $order_number;
		}

		return $order_id;
	}

	/**
	 * Hook to sned sequential order number to the master.
	 */
	public function master_send_sequential_order() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( $_engine->is_request_authenticated( $_POST ) === false ) {
			wp_send_json(
				array(
					'status' => 'failed',
					'msg'    => 'Authentication failed.',
					'result' => 0,
				)
			);
			die;
		}

		if ( $this->options->get( 'sequential-order-numbers' ) != 'yes' ) {
			wp_send_json(
				array(
					'status' => 'success',
					'msg'    => 'Sequential order is disabled.',
					'result' => null,
				)
			);
		}

		wp_send_json(
			array(
				'status' => 'success',
				'msg'    => '',
				'result' => $this->get_next_network_order_number(),
			)
		);

	}
}

$GLOBALS['WOO_MSTORE_SINGLE_SEQUENTIAL_ORDER_NUMBER'] = new WOO_MSTORE_SINGLE_SEQUENTIAL_ORDER_NUMBER();
