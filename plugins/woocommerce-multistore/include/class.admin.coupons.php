<?php
/**
 * Syncs upsell and cross sells
 *
 * @since: 2.1.1
 **/
class WOO_MSTORE_COUPONS_SYNC {

	private $functions = null;

	private $meta_keys = array(
        'discount_type'              => 'discount_type',
        'coupon_amount'              => 'coupon_amount',
        'individual_use'             => 'individual_use',
        'product_ids'                => 'product_ids',
        'exclude_product_ids'        => 'exclude_product_ids',
        'usage_limit'                => 'usage_limit',
        'usage_limit_per_user'       => 'usage_limit_per_user',
        'limit_usage_to_x_items'     => 'limit_usage_to_x_items',
        'usage_count'                => 'usage_count',
        'date_expires'               => 'date_expires',
        'free_shipping'              => 'free_shipping',
        'product_categories'         => 'product_categories',
        'exclude_product_categories' => 'exclude_product_categories',
        'exclude_sale_items'         => 'exclude_sale_items',
        'minimum_amount'             => 'minimum_amount',
        'maximum_amount'             => 'maximum_amount',
        'customer_email'             => 'customer_email',
    );

	/**
	 * Add action hooks on instantiation
	 **/
	public function __construct() {
		$this->functions = new WOO_MSTORE_functions();

		add_action( 'woocommerce_coupon_object_updated_props', array( $this, 'sync_created_coupons' ), 10, 2 );
		add_action( 'delete_post',   array( $this, 'delete_coupons' ), 10, 1 );
		add_action( 'wp_trash_post',   array( $this, 'trash_coupons' ), 10, 1 );
	}

	/**
	**
	** Sync created coupons across stores
	**/
	public function sync_created_coupons($coupon, $updated_props ) {

		if ( ! $this->is_sync_enabled() ) {
			return;
		}

		// User not on edit screen. Hook fired by something else
		if ( ! $this->is_edit_screen() ) {
			return; 
		}

		$this->sync_coupons_with_child_stores( $coupon->get_id(), $coupon, $updated_props );
	}

	/**
	** Check if user is editing the post
	**/
	public function is_edit_screen() {
		if ( !empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
			return true;
		}

		if ( !empty($_REQUEST['action']) && $_REQUEST['action'] == 'editpost') {
			return true;
		}

		return false;
	}

	/**
	** Check if coupon sync is enabled in global settings
	**/
	public function is_sync_enabled() {		
		$options = $this->functions->get_options();

		if ( !empty($options['sync-coupons']) && $options['sync-coupons'] == 'yes' ) {
			return true;
		}

		return false;
	}

	/**
	** Sync coupons with child stores
	**/
	public function sync_coupons_with_child_stores($post_id, $post, $updated_props) {

		if ( $this->is_child_coupon( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );
		$post = (array) $post; 
		unset($post['ID']);
		unset($post['guid']);
		unset($post['post_author']);

		$meta = get_post_meta( $post_id );

		$functions = new WOO_MSTORE_functions();
		$stores = $functions->get_active_woocommerce_blog_ids();
		$current_blog_id = get_current_blog_id();

		if ( !empty($stores) ) {
			foreach( $stores as $store_id ) {

				if ( $store_id == $current_blog_id ) {
					continue;
				}

				switch_to_blog( $store_id );

				$coupon_post_id = $this->get_child_coupon_id( $post_id );

				if ( empty($coupon_post_id) ) {
					$coupon_post_id = wp_insert_post( $post );
				} else {
					wp_update_post( array_merge($post, array(
						'ID' => $coupon_post_id,
					)));
				}

				foreach( $meta as $k => $v ) {

					if ( $k == 'product_ids' || $k == 'exclude_product_ids' ) {

						if ( !empty($v[0]) ) {
							$v[0] = $this->__translate_product_ids( $v[0] );
						} else {
							$v[0] = '';
						}
					}

					if ( $k == 'product_categories' || $k == 'exclude_product_categories' ) {

						if ( !empty($v[0]) ) {
							$v[0] = $this->__translate_product_terms( $current_blog_id, $v[0] );
						} else {
							$v[0] = array();
						}

					}

					if ( $k == 'customer_email' ) {

						if ( !empty($v[0]) ) {
							$v[0] = maybe_unserialize( $v[0] );
						} else {
							$v[0] = array();
						}

					}

					update_post_meta($coupon_post_id, $k, $v[0]);
				}

				foreach( $this->meta_keys as $mkey ) {

					if ( ! isset( $meta[$mkey] ) ) {
						delete_post_meta($coupon_post_id, $mkey);
					}
				}

				update_post_meta($coupon_post_id, 'woonet_coupon_parent_id', $post_id);
				update_post_meta($coupon_post_id, 'woonet_coupon_site_id',   $current_blog_id);
			}
		}

		switch_to_blog( $current_blog_id );
	}

	/**
	** Check if coupons exists on chind store and if so return the ID
	**/
	public function get_child_coupon_id( $parent_post_id ) {
		global $wpdb;

		$coupon_id = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='woonet_coupon_parent_id' AND meta_value='{$parent_post_id}'");

		if ( !empty( $coupon_id ) && !empty( $coupon_id->post_id ) ) {
			return $coupon_id->post_id;
		}

		return false;
	}

	/**
	** Check if Child coupon
	**/
	public function is_child_coupon( $post_id ) {

		$meta = get_post_meta( $post_id, 'woonet_coupon_parent_id', true );

		if ( !empty($meta) ) {
			return true;
		}

		return false;
	}

	/**
	** Delete child coupons when parent coupon is deleted
	**/
	public function delete_coupons( $post_id ) {
		if ( ! $this->is_sync_enabled() ) {
			return;
		}

		return $this->__trash_delete_coupons( $post_id, 'delete' );
	}

	/**
	** Trash child coupons when parent coupon is trashed
	**/
	public function trash_coupons( $post_id ) {

		if ( ! $this->is_sync_enabled() ) {
			return;
		}

		return $this->__trash_delete_coupons( $post_id, 'trash' );
	}

	/**
	** Trash or delete coupons
	**/
	public function __trash_delete_coupons($post_id, $status) {

		if ( $this->is_child_coupon( $post_id ) ) {
			return;
		}

		if ( ! $this->is_post_type_coupons( $post_id ) ) {
			return;
		}

		$functions = new WOO_MSTORE_functions();
		$stores = $functions->get_active_woocommerce_blog_ids();
		$current_blog_id = get_current_blog_id();

		if ( !empty($stores) ) {
			foreach( $stores as $store_id ) {

				if ( $store_id == $current_blog_id ) {
					continue;
				}

				switch_to_blog( $store_id );

				$coupon_post_id = $this->get_child_coupon_id( $post_id );

				if ( empty( $coupon_post_id ) ) {
					continue;
				}

				if ( $status == 'trash' ) {
					wp_trash_post( $coupon_post_id );
				} else {
					wp_delete_post( $coupon_post_id, true ); 
				}
			}
		}

		switch_to_blog( $current_blog_id );
	}

	/**
	** Check if post type is coupons 
	**/
	public function is_post_type_coupons( $post_id ) {
		$_type = get_post_type( $post_id );

		if ( $_type == 'shop_coupon' ) {
			return true;
		}

		return false;
	}

	/**
	** Translate product IDs
	**/
	public function __translate_product_ids( $parent_product_ids ) {
		$parent_product_ids = explode(',', $parent_product_ids);
		$child_product_ids = '';

		if ( is_array( $parent_product_ids ) && count( $parent_product_ids ) >= 1 ) {
			foreach( $parent_product_ids as $id ) {
				$child_product_id = $this->__get_child_product_id( $id );

				if ( !empty($child_product_id) ) {
					$child_product_ids = $child_product_ids . $child_product_id . ',';
				}
			}

			$child_product_ids = trim( $child_product_ids, ',' );
		}

		return $child_product_ids;
	}

	/**
	** Get the child product ID of a parent product
	**/
	public function __get_child_product_id( $parent_product_id ) {
		global $wpdb;

		//@todo:optimize 
		$product_id = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='_woonet_network_is_child_product_id' AND meta_value='{$parent_product_id}'");

		if ( !empty( $product_id ) && !empty( $product_id->post_id ) ) {
			return $product_id->post_id;
		}

		return;
	}

	/**
	** Translate term IDs
	**/
	public function __translate_product_terms( $parent_site_id, $parent_term_ids ) {

		$parent_term_ids = maybe_unserialize( $parent_term_ids );
		
		$terms_mapping = get_option( 'terms_mapping', array() );

		$child_term_ids = array();

		if ( count( $parent_term_ids ) >= 1 ) {
			foreach( $parent_term_ids as $id ) {
				if ( isset( $terms_mapping[$parent_site_id][$id] ) ) {
					$child_term_ids[] = $terms_mapping[$parent_site_id][$id];
				}
			}
		}

		return $child_term_ids;
	}
}

new WOO_MSTORE_COUPONS_SYNC();
