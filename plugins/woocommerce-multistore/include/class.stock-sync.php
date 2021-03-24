<?php

class WOO_MSTORE_stock_sync extends WOO_MSTORE_admin_product {
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ), 1 );

		$this->functions = new WOO_MSTORE_functions();
	}

	public function init() {
		add_action( 'woocommerce_update_product', array( $this, 'process_product' ), PHP_INT_MAX, 1 );
		add_action( 'WOO_MSTORE_admin_product/process_slave_product', array( $this, 'process_slave_product' ), PHP_INT_MAX );
	}

	/**
	 * Process any actions for a product New/Update
	 *
	 * @param integer $post_id Post ID.
	 */
	public function process_product( $post_id ) {
		if ( doing_action( 'wp_ajax_woocommerce_save_variations' ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		wp_cache_flush();
		$master_product = wc_get_product( $post_id );
		$master_product->get_children();

		if ( $this->is_slave_product( $master_product ) ) {
			do_action( 'WOO_MSTORE_admin_product/process_slave_product', $master_product );

			return;
		}

		remove_action( 'woocommerce_update_product', array( $this, 'process_product' ), PHP_INT_MAX );
		remove_action( 'WOO_MSTORE_admin_product/process_product', array( $this, 'process_product' ), PHP_INT_MAX );

		// set master product meta
		$master_product_meta_to_exclude = $this->get_master_product_meta_to_exclude( $master_product );
		foreach ( $master_product_meta_to_exclude as $meta_key ) {
			$master_product->delete_meta_data( $meta_key );
		}
		$master_product_meta_to_update = $this->get_master_product_meta_to_update( $master_product );
		foreach ( $master_product_meta_to_update as $meta_key => $meta_value ) {
			$master_product->add_meta_data( $meta_key, $meta_value, true );
		}

		if ( count( $master_product_meta_to_exclude ) || count( $master_product_meta_to_update ) ) {
			$master_product->save();
		}

		$master_product_data = array(
			'options'                   => $this->functions->get_options(),
			'master_product'            => $master_product,
			'master_product_blog_id'    => get_current_blog_id(),
			'master_product_attributes' => wc_get_attribute_taxonomies(),
			'master_product_terms'      => $this->get_product_terms( $master_product->get_id() ),
			'master_product_upload_dir' => wp_upload_dir(),
		);

		$blog_ids = $this->functions->get_active_woocommerce_blog_ids();
		foreach ( $blog_ids as $slave_product_blog_id ) {
			if (
				get_current_blog_id() == $slave_product_blog_id
				||
				'yes' !== $master_product->get_meta( '_woonet_publish_to_' . $slave_product_blog_id )
			) {
				continue;
			}

			switch_to_blog( $slave_product_blog_id );
				$this->synchronize_master_slave_products(
					$master_product_data + array(
						'slave_product' => $this->get_slave_product( $master_product_data['master_product_blog_id'], $master_product ),
					)
				);
			restore_current_blog();
		}

		add_action( 'woocommerce_update_product', array( $this, 'process_product' ), PHP_INT_MAX );
		add_action( 'WOO_MSTORE_admin_product/process_product', array( $this, 'process_product' ), PHP_INT_MAX );
	}
}

new WOO_MSTORE_stock_sync();
