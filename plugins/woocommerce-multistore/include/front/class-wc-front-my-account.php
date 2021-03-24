<?php
/**
 * My Account Class
 *
 * Allow a user to see the same info at any site he logs in to.
 *
 * @author      Tonny
 * @category    Admin
 * @package     Multistore/Admin
 * @version     2.4.0
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Front_My_Account class.
 */
class WC_Front_My_Account {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// hook into "My account" menu items
		add_filter( 'woocommerce_account_menu_items', array( $this, 'get_account_menu_items' ), PHP_INT_MAX );
	}

	public function enqueue_scripts() {
		wp_register_style(
			'jquery-ui-accordion',
			WOO_MSTORE_URL . '/include/front/assets/css/jquery-ui-accordion.min.css'
		);
		wp_register_script(
			'wc_front_my_account',
			WOO_MSTORE_URL . '/include/front/assets/js/wc_front_my_account.js',
			array( 'jquery-ui-accordion' ),
			'1.11.4',
			true
		);
	}

	public function get_account_menu_items( $items ) {
		global $WOO_MSTORE_FUNCTIONS;

		static $current_user_id = null;
		static $processed_sites = array();
		static $processed_items = array();

		$current_user_id   = get_current_user_id();
		$processed_sites[] = get_current_blog_id();
		$processed_items   = array_merge( $processed_items, $items );

		// get "My account" menu items for all blogs
		if ( $blog_ids = $WOO_MSTORE_FUNCTIONS->get_active_woocommerce_blog_ids() ) {
			foreach( $blog_ids as $blog_id ) {
				if ( in_array( $blog_id, $processed_sites ) ) {
					continue;
				}

				if ( ! is_user_member_of_blog( $current_user_id, $blog_id ) ) {
					continue;
				}

				switch_to_blog( $blog_id );

				wc_get_account_menu_items();

				restore_current_blog();
			}
		}

		if ( empty( $GLOBALS['switched'] ) ) {
			$items = $processed_items;

			$common_enpoints = array(
				'customer-logout',
				'dashboard',
				'edit-account',
				'edit-address',
			);
			foreach ( array_keys( $items ) as $endpoint ) {
				if ( ! in_array( $endpoint, $common_enpoints) ) {
					add_action( 'woocommerce_account_' . $endpoint . '_endpoint', array( $this, 'account_endpoint_start' ), -PHP_INT_MAX );
					add_action( 'woocommerce_account_' . $endpoint . '_endpoint', array( $this, 'account_endpoint' ), PHP_INT_MAX );
				}
			}
		}

		return $items;
	}

	public function account_endpoint_start() {
		ob_start();
	}

	public function account_endpoint() {
		remove_action( 'woocommerce_account_content', 'woocommerce_output_all_notices', 5 );

		global $WOO_MSTORE_FUNCTIONS;

		static $current_user_id = null;
		static $processed_sites = array();
		static $content         = '';

		$current_user_id   = get_current_user_id();
		$processed_sites[] = get_current_blog_id();

		$content .= sprintf(
			'<h3>%s</h3><div>%s</div>',
			get_bloginfo('name'),
			ob_get_clean()
		);

		// get "My account" menu item content for all blogs
		if ( $blog_ids = $WOO_MSTORE_FUNCTIONS->get_active_woocommerce_blog_ids() ) {
			foreach( $blog_ids as $blog_id ) {
				if ( in_array( $blog_id, $processed_sites ) ) {
					continue;
				}

				if ( ! is_user_member_of_blog( $current_user_id, $blog_id ) ) {
					continue;
				}

				switch_to_blog( $blog_id );

				do_action( 'woocommerce_account_content' );

				restore_current_blog();
			}
		}

		if ( empty( $GLOBALS['switched'] ) ) {
			echo '<div id="woo_mstore_accordion">' . $content . '</div>';
			wp_enqueue_style( 'jquery-ui-accordion' );
			wp_enqueue_script( 'wc_front_my_account' );
		}
	}
}
