<?php
/**
 * Sync custom taxonomies
 *
 * @since 4.1.0
 */
class WOO_MSTORE_INTEGRATION_CUSTOM_TAXONOMIES {
	private $taxonomies       = array();
	private $taxonomy_options = array();
	private $options_manager  = null;

	public function __construct() {
		if ( is_multisite() ) {
			add_action( 'WOO_MSTORE_admin_product/slave_product_updated', array( $this, 'sync_custom_taxonomies' ), 10, 1 );
		} else {
			// Regular WordPress support
			add_filter( 'WOO_MSTORE_SYNC/process_json/product', array( $this, 'add_taxonomy_terms' ), 10, 3 );
			add_action( 'WOO_MSTORE_SYNC/sync_child/complete', array( $this, 'sync_taxonomy_terms' ), 10, 3 );
		}

		if ( is_multisite() ) {
			$_tax = get_site_option('woonet_settings_custom_taxonomy');
			
			if ( ! empty($_tax) ) {
				$this->taxonomies = explode( "\n", trim($_tax) );
			}
		} else {
			$this->taxonomy_options = get_option( 'woonet_settings_custom_taxonomy' );
		}

		if ( ! empty( $this->taxonomy_options ) && is_array( $this->taxonomy_options ) ) {
			$this->taxonomies = array_keys( $this->taxonomy_options );
		}

		if ( is_multisite() ) {
			global $WOO_MSTORE;
			$this->options_manager = $WOO_MSTORE->functions->get_options(); 
		} else {
			$this->options_manager = new WOO_MSTORE_OPTIONS_MANAGER();
		}
	}

	public function add_taxonomy_terms( $product, $wc_product, $product_id ) {
		$custom_tax = array();

		if ( $this->options_manager->get( 'sync-custom-taxonomy' ) != 'yes' ) {
			return $product;
		}

		$product['_custom_taxonomies'] = array();

		foreach ( $this->taxonomies as $tax ) {
			$_terms = get_the_terms( $product_id, $tax );

			if ( ! empty( $_terms ) ) {
				$custom_tax [ $tax ] = array();

				foreach ( $_terms as $trm ) {
					$custom_tax [ $tax ][] = $trm->name;
				}
			}
		}

		if ( ! empty( $this->taxonomy_options ) ) {
			foreach ( $this->taxonomy_options  as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $k => $v ) {
						if ( isset( $custom_tax [ $key ] ) ) {
							$tax_terms = $custom_tax [ $key ];
						} else {
							$tax_terms = array();
						}

						if ( isset( $product['_custom_taxonomies'][ $k ] ) ) {
							$product['_custom_taxonomies'][ $k ][ $key ] = $tax_terms;
						} else {
							$product['_custom_taxonomies'][ $k ]         = array();
							$product['_custom_taxonomies'][ $k ][ $key ] = $tax_terms;
						}
					}
				}
			}
		}

		return $product;

	}

	public function sync_taxonomy_terms( $wc_product_id, $parent_id, $product ) {
		if ( empty( $product['_custom_taxonomies'] ) ) {
			return;
		}

		$site_option = get_option( 'woonet_master_connect' );

		if ( ! isset( $site_option['uuid'] ) && ! isset( $product['_custom_taxonomies'][ $site_option['uuid'] ] ) ) {
			return;
		}

		if ( ! empty( $product['_custom_taxonomies'][ $site_option['uuid'] ] ) ) {
			foreach ( $product['_custom_taxonomies'][ $site_option['uuid'] ] as $tax => $terms ) {
				wp_set_object_terms( $wc_product_id, $terms, $tax );
			}
		}
	}

	/**
	 * Sync custom taxonomies on the multisite version.
	 */
	public function sync_custom_taxonomies( $data ) {
		if ( isset( $this->options_manager['sync-custom-taxonomy'] ) && $this->options_manager['sync-custom-taxonomy'] != 'yes' ) {
            return;
		}

		foreach ( $this->taxonomies as $tax ) {
			$terms_to_sync = array();
            $tax = trim( $tax );

			$slave_blog_id = get_current_blog_id();
			restore_current_blog();

			// get the terms from parent
			$_terms = get_the_terms( $data['master_product']->get_id(), $tax );

			if ( ! empty( $_terms ) ) {
				foreach ( $_terms as $trm ) {
					if ( isset( $trm->name ) ) {
						$terms_to_sync[] = $trm->name;
					}
				}
			}

			switch_to_blog( $slave_blog_id );

			wp_set_object_terms( $data['slave_product']->get_id(), $terms_to_sync, $tax );
		}
	}
}


new WOO_MSTORE_INTEGRATION_CUSTOM_TAXONOMIES();
