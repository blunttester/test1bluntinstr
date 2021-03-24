<?php
/**
 * Trash all linked products when a network product is trashed from the master site.
 */

class WOO_MSTORE_SINGLE_TRASH_PRODUCTS {

	/**
	 * Initialize the action hooks and load the plugin classes
	 **/
	public function __construct() {
		add_action( 'init', array($this, 'init'), 10, 0 );
	}

	public function init() {
		$_options = get_option( 'woonet_options', array() );

		// run on master site
		if ( get_option('woonet_network_type') == 'master' 
		     && isset($_options['synchronize-trash']) 
		 	 && $_options['synchronize-trash'] == 'yes' ) {
			add_action('trashed_post', array($this, 'trash_post'), 10, 1);
			add_action('untrashed_post', array($this, 'untrash_post'), 10, 1); 
			add_action('deleted_post', array($this, 'delete_post'), 10, 1);
		}

		//run on child site. Look for trash post request
		add_action( 'wp_ajax_nopriv_woomulti_trash_untrash', array( $this, 'delete_post_on_child' ), 10, 0 );
	}


	/**
	 * Runs on the master. Send trash post request to the child.
	**/
	public function trash_post( $post_id ) {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
		$response = $_engine->trash_untrash_delete_post($post_id, 'trash');
	}

	/**
	 * Runs on the master. Send untrash post request to the child.
	**/
	public function untrash_post( $post_id ) {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
		$response = $_engine->trash_untrash_delete_post($post_id, 'untrash');
	}

	/**
	 * Runs on the master. Send delete post request to the child.
	**/
	public function delete_post( $post_id ) {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
		$response = $_engine->trash_untrash_delete_post($post_id, 'delete');
	}

	/**
	 * Runs on the child sites. Listens to the request to delete, trash or untrash a product. 
	 */
	public function delete_post_on_child() {
		$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();

		if ( ! $_engine->is_request_authenticated($_POST) ) {
			echo json_encode(array(
				'status'  => 'failed',
				'message' => 'Authorization failed for ' . site_url(),
			));
			die;
		}

		if ( !empty($_POST['parent_post_id']) && !empty($_POST['parent_post_status']) ) {
			$child_post_id = $_engine->get_mapped_child_post( (int) $_POST['parent_post_id'] );

			if ( !empty($child_post_id) ) {

				$product = wc_get_product( $child_post_id );

				if ( $_POST['parent_post_status'] == 'delete' ) {
					$product->delete(true);

				} else if ( $_POST['parent_post_status'] == 'trash' ) {
					$product->delete();

				} else if ( $_POST['parent_post_status'] == 'untrash' ) {
					wp_untrash_post( $product->get_id() );
				}

				// @todo: check for return value and send appropriate response.
				echo json_encode(array(
					'status'  => 'success',
					'message' => "Product successfully deleted/trashed/untrashed.",
				));
				die;
			}
		}
	}
} 

$GLOBALS['WOO_MSTORE_SINGLE_TRASH_PRODUCTS'] = new WOO_MSTORE_SINGLE_TRASH_PRODUCTS();
