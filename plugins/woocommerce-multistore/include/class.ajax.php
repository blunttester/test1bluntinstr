<?php

class WOO_MSTORE_ajax {
	function __construct() {
		add_action( 'wp_ajax_woosl_setup_get_process_list', array( $this, 'woosl_setup_get_process_list' ) );
		add_action( 'wp_ajax_woosl_setup_process_batch', array( $this, 'woosl_setup_process_batch' ) );

		add_action( 'wp_ajax_inline-save', array( $this, 'network_products_inline_save' ), - PHP_INT_MAX );
	}

	/**
	 * Ajax handler for Quick Edit saving a post from a list table.
	 * wp-admin/includes/ajax-actions.php:wp_ajax_inline_save
	 */
	public function network_products_inline_save() {
		if (
			empty( $_REQUEST["screen"] )
			||
			! in_array( $_REQUEST["screen"], array(
				'woocommerce_page_woonet-woocommerce-products',
				'woocommerce_page_woonet-woocommerce-products-network',
			) )
		) {
			return;
		}

		global $mode;

		if ( isset( $_REQUEST['master_blog_id'] ) ) {
			$blog_id = $_REQUEST['master_blog_id'];
		} elseif ( isset( $_REQUEST['product_blog_id'] ) ) {
			$blog_id = $_REQUEST['product_blog_id'];
		} else {
			die();
		}

		switch_to_blog( intval( $blog_id ) );

		check_ajax_referer( 'inlineeditnonce', '_inline_edit' );

		if ( ! isset($_POST['post_ID']) || ! ( $post_ID = (int) $_POST['post_ID'] ) )
			wp_die();

		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_ID ) )
				wp_die( __( 'Sorry, you are not allowed to edit this page.' ) );
		} else {
			if ( ! current_user_can( 'edit_post', $post_ID ) )
				wp_die( __( 'Sorry, you are not allowed to edit this post.' ) );
		}

		if ( $last = wp_check_post_lock( $post_ID ) ) {
			$last_user      = get_userdata( $last );
			$last_user_name = $last_user ? $last_user->display_name : __( 'Someone' );
			printf( $_POST['post_type'] == 'page' ? __( 'Saving is disabled: %s is currently editing this page.' ) : __( 'Saving is disabled: %s is currently editing this post.' ), esc_html( $last_user_name ) );
			wp_die();
		}

		$data = &$_POST;

		$post = get_post( $post_ID, ARRAY_A );

		// Since it's coming from the database.
		$post = wp_slash( $post );

		$data['content'] = $post['post_content'];
		$data['excerpt'] = $post['post_excerpt'];

		// Rename.
		$data['user_ID'] = get_current_user_id();

		if ( isset($data['post_parent']) )
			$data['parent_id'] = $data['post_parent'];

		// Status.
		if ( isset( $data['keep_private'] ) && 'private' == $data['keep_private'] ) {
			$data['visibility']  = 'private';
			$data['post_status'] = 'private';
		} else {
			$data['post_status'] = $data['_status'];
		}

		if ( empty($data['comment_status']) )
			$data['comment_status'] = 'closed';
		if ( empty($data['ping_status']) )
			$data['ping_status'] = 'closed';

		// Exclude terms from taxonomies that are not supposed to appear in Quick Edit.
		if ( ! empty( $data['tax_input'] ) ) {
			foreach ( $data['tax_input'] as $taxonomy => $terms ) {
				$tax_object = get_taxonomy( $taxonomy );
				/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
				if ( ! apply_filters( 'quick_edit_show_taxonomy', $tax_object->show_in_quick_edit, $taxonomy, $post['post_type'] ) ) {
					unset( $data['tax_input'][ $taxonomy ] );
				}
			}
		}

		// Hack: wp_unique_post_slug() doesn't work for drafts, so we will fake that our post is published.
		if ( ! empty( $data['post_name'] ) && in_array( $post['post_status'], array( 'draft', 'pending' ) ) ) {
			$post['post_status'] = 'publish';
			$data['post_name']   = wp_unique_post_slug( $data['post_name'], $post['ID'], $post['post_status'], $post['post_type'], $post['post_parent'] );
		}

		// Update the post.
		edit_post();

//		restore_current_blog();

		require_once( WOO_MSTORE_PATH . '/include/class.admin.network-products-list-table.php' );
		$wp_list_table = new Class_Admin_Network_Products_List_Table();

		$mode = $_POST['post_view'] === 'excerpt' ? 'excerpt' : 'list';

		$item = array(
			'id'         => intval( $_REQUEST["post_ID"] ),
			'post_title' => $_REQUEST["post_title"],
			'date'       => null,
			'blog_id'    => intval( $blog_id ),
		);
		$wp_list_table->display_rows( array( (object) $item ) );

		wp_die();
	}

	function woosl_setup_get_process_list() {
		$site_id = intval( $_POST['site_id'] );

		switch_to_blog( $site_id );

		//get all products
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => '-1',
			'fields'         => 'ids',
		);

		$custom_query = new WP_Query( $args );

		$post_list = $custom_query->get_posts();

		restore_current_blog();

		$response           = array();
		$response['status'] = 'completed';
		$response['data']   = $post_list;

		echo json_encode( $response );
		die();
	}

	function woosl_setup_process_batch() {
		$site_id = intval( $_POST['site_id'] );
		$batch   = (array) $_POST['batch'];

		switch_to_blog( $site_id );

		foreach ( $batch as $post_id ) {
			//check if the product include the required meta fields
			$is_main_product  = get_post_meta( $post_id, '_woonet_network_main_product', true );
			$is_child_product = get_post_meta( $post_id, '_woonet_network_is_child_product_id', true );

			if ( ! empty( $is_child_product ) || ! empty( $is_main_product ) ) {
				continue;
			}

			//add as main product
			update_post_meta( $post_id, '_woonet_network_main_product', 'true' );
		}

		restore_current_blog();

		$response           = array();
		$response['status'] = 'completed';

		echo json_encode( $response );
		die();
	}

	private function log( $message, $line_number = 0, $level = 'notice' ) {
		static $logger = null;

		if ( empty( $logger ) && function_exists( 'wc_get_logger' ) ) {
			$logger = wc_get_logger();
		}

		if ( empty( $logger ) ) {
			return;
		}

		if ( ! is_scalar( $message ) ) {
			$message = wc_print_r( $message, true );
		}
		$message = __CLASS__ . ':'  . $line_number . '=>' . $message;

		switch ( $level ) {
			case 'debug':     $level = WC_Log_Levels::DEBUG;     break;
			case 'info':      $level = WC_Log_Levels::INFO;      break;
			case 'emergency': $level = WC_Log_Levels::EMERGENCY; break;
			case 'alert':     $level = WC_Log_Levels::ALERT;     break;
			case 'critical':  $level = WC_Log_Levels::CRITICAL;  break;
			case 'error':     $level = WC_Log_Levels::ERROR;     break;
			case 'warning':   $level = WC_Log_Levels::WARNING;   break;
			default:          $level = WC_Log_Levels::NOTICE;    break;
		}

		$logger->log( $level, $message, array( 'source' => 'WOO_MSTORE' ) );
	}
}
