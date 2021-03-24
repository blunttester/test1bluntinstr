<?php
/**
 * WooMultistore single site init
 */

class WOO_MSTORE_SINGLE_NETWORK_PRODUCTS {

	/**
	 * initiate the action hooks and load the plugin classes
	 **/
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_woomultistore_menu' ), PHP_INT_MAX );

		add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ), PHP_INT_MAX );

		add_filter( 'set-screen-option', array( $this, 'set_screen_options' ), 10, 3 );

		add_filter( 'manage_woocommerce_page_woonet-woocommerce-products-network_columns', array( $this, 'manage_screen_columns' ) );

		// allow woocommerce to run on this screen
		add_filter( 'woocommerce_screen_ids', array( $this, 'woocommerce_screen_ids' ) );

		add_action( 'current_screen', array( $this, 'current_screen' ), 1 );
	}

	/**
	 * Add a primary menu for WooMultistore
	 **/
	public function add_woomultistore_menu() {
		// only if superadmin
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! woomulti_has_valid_license() ) {
			return;
		}

		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			add_submenu_page(
				'woonet-woocommerce',
				'Network Products',
				'Network Products',
				'manage_options',
				'network-products',
				array( $this, 'network_products_interface' ),
				3
			);
		}
	}

	function current_screen( $_current_screen ) {
		// bulk distribution
		if ( is_object( $_current_screen ) && $_current_screen->id == 'woocommerce_page_woonet-woocommerce-products-network' ) {
			global $current_screen, $typenow;

			$typenow = $current_screen->post_type = 'product';
		}
	}

	public function woocommerce_screen_ids( $screen_ids ) {
		$screen_ids[] = 'woocommerce_page_woonet-woocommerce-products-network';
		$screen_ids[] = 'edit-woocommerce_page_woonet-woocommerce-products-network'; // @todo w8 Do we need this?

		return $screen_ids;
	}

	public function manage_screen_columns( $existing_columns ) {
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}

		unset( $existing_columns['name'], $existing_columns['comments'], $existing_columns['date'] );

		$columns = array(
			'network_sites' => __( 'Network Sites', 'woonet' ),
			'thumb'         => __( 'Image', 'woonet' ),
			'in_stock'      => __( 'Stock', 'woonet' ),
			'price'         => __( 'Price', 'woonet' ),
			'categories'    => __( 'Categories', 'woonet' ),
			'product_type'  => __( 'Type', 'woonet' ),
			'date'          => __( 'Date', 'woonet' ),
		);

		return array_merge( $columns, $existing_columns );
	}

	public function network_admin_menu() {
		$menu_hook = add_submenu_page(
			'woonet-woocommerce',
			__( 'Products', 'woonet' ),
			__( 'Products', 'woonet' ),
			'manage_product_terms',
			'woonet-woocommerce-products',
			array(
				$this,
				'network_products_interface',
			)
		);

		add_action( 'load-' . $menu_hook, array( $this, 'admin_notices' ) );
		add_action( 'load-' . $menu_hook, array( $this, 'screen_options' ) );

		add_action( 'admin_print_styles-' . $menu_hook, array( $this, 'admin_print_styles' ) );
		add_action( 'admin_print_scripts-' . $menu_hook, array( $this, 'admin_print_scripts' ) );
	}

	public function admin_notices() {
		global $WOO_SL_messages;

		if ( ! is_array( $WOO_SL_messages ) || count( $WOO_SL_messages ) < 1 ) {
			return;
		}

		foreach ( $WOO_SL_messages as $message_data ) {
			echo "<div id='notice' class='" . $message_data['status'] . " fade'><p>" . $message_data['message'] . '</p></div>';
		}
	}

	function admin_print_styles() {
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array() );
	}

	public function admin_print_scripts() {
		wp_enqueue_script( 'jquery-tiptip' );
		wp_enqueue_script( 'inline-edit-post' );

		wp_enqueue_script( 'woocommerce_quick-edit', WC()->plugin_url() . '/assets/js/admin/quick-edit.min.js', array( 'jquery', 'woocommerce_admin' ), WC_VERSION );
		wp_localize_script(
			'woocommerce_quick-edit',
			'woocommerce_quick_edit',
			array(
				'strings' => array(
					'allow_reviews' => esc_js( __( 'Enable reviews', 'woocommerce' ) ),
				),
			)
		);
	}

	public function screen_options() {
		$screen = get_current_screen();

		if ( is_object( $screen ) && $screen->id == 'woocommerce_page_woonet-woocommerce-products-network' ) {
			$args = array(
				'label'   => __( 'Products per Page', 'woonet' ),
				'default' => 10,
				'option'  => 'products_per_page',
			);
			add_screen_option( 'per_page', $args );
		}
	}

	public function set_screen_options( $status, $option, $value ) {
		if ( 'products_per_page' == $option ) {
			$status = absint( $value );
		}

		return $status;
	}

	public function network_products_interface() {
		// require_once( WOO_MSTORE_PATH . 'templates/html-network-products-interface.php' );
		wp_redirect( admin_url( 'edit.php?post_type=product' ) );
		die;
	}
}

$GLOBALS['WOO_MSTORE_SINGLE_NETWORK_PRODUCTS'] = new WOO_MSTORE_SINGLE_NETWORK_PRODUCTS();

