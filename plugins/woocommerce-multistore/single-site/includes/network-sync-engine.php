<?php

/**
 * Network Bulk Updater
 *
 * @class   WOO_MSTORE_BULK_SYNC
 * @since   2.0.20
 */
class WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE {

	/**
	 * Source of update
	 * WOO_MULTISTORE | WooCommerce
	 *
	 * If source is the WooMultistore plugin itself, do not run stock sync hook on the child as
	 * that may lead to a sync loop.
	 *
	 * @var array
	 */
	private $_source = '';

	/**
	 * Store attributes synced.
	 *
	 * @since 3.0.4
	 * @var array
	 */
	public $synced_attributes = array();

	/**
	 * Only metadata in the array will be synced by the plugin when sync
	 * all metadata is disabled.
	 *
	 * @since 3.0.4
	 * @var array
	 */
	private $whitelisted_metadata = array(
		// '_sku',
		'total_sales',
		'_tax_status',
		'_tax_class',
		// "_manage_stock",
		// S"_backorders",
		'_sold_individually',
		'_virtual',
		'_downloadable',
		'_download_limit',
		'_download_expiry',
		// "_stock",
		// "_stock_status",
		'_sale_price_dates_from',
		'_sale_price_dates_to',
		'_wc_average_rating',
		'_wc_review_count',
		'_product_version',
		'_wpcom_is_markdown',
		'_wp_old_slug',
		'_price',
		'_regular_price',
		'_sale_price',
		'_length',
		'_width',
		'_weight',
		'_height',
		'_thumbnail_id',
		'_product_attributes',
		'_edit_last',
		// "_low_stock_amount",
		'_upsell_ids',
		'_crosssell_ids',
		'_purchase_note',
		'_downloadable_files',
		'_children',
		'_product_image_gallery',
		'_variation_description',
		'attribute_pa_%',
		'attribute_%',
		'_woonet_%',
		'woonet_%',
	);

	/**
	 * Settings defined on the settings page
	 *
	 * @since 3.0.4
	 * @var array
	 */
	private $options = null;


	public function __construct( $source = 'WooCommerce' ) {
		$this->set_source( $source );
	}

	/**
	 * Run the sync operation
	 */
	public function sync( $product_id, $site_id, $background_sync = false ) {
		$product = self::product_to_json( $product_id );

		if ( $background_sync === false && ! woomulti_has_min_user_role() ) {
			return;
		}

		if ( ! WOO_MULTISTORE()->license_manager->licence_key_verify() ) {
			return;
		}

		if ( $product ) {
			$response = self::sync_child_sites( $product, $site_id );
		}
	}

	/**
	 * Set Update source
	 *
	 * @param [type] $source
	 * @return void
	 */
	public function set_source( $source ) {
		$this->_source = $source;
	}

	/**
	 * Get Update Source
	 *
	 * @return string
	 */
	public function get_source() {
		return $this->_source;
	}

	/**
	 * Convert product details and metadata to JSON, which will be sent to child sites
	 */
	public function product_to_json( $product_id ) {
		$wc_product = wc_get_product( $product_id );

		$product = array(
			'_woomulti_source'         => $this->get_source(),
			'_woomulti_version'        => defined( 'WOO_MSTORE_VERSION' ) ? WOO_MSTORE_VERSION : '',
			'_woomulti_sync_init_time' => time(),
		);

		$product['product'] = array(
			'ID'                    => $wc_product->get_id(),
			'post_author'           => null,
			'post_date'             => method_exists( $wc_product->get_date_created(), 'getTimestamp' ) ? $wc_product->get_date_created()->getTimestamp() : null,
			'date_modified'         => method_exists( $wc_product->get_date_modified(), 'getTimestamp' ) ? $wc_product->get_date_modified()->getTimestamp() : null,
			'post_date_gmt'         => null,
			'post_content'          => $wc_product->get_description(),
			'post_title'            => $wc_product->get_name(),
			'post_excerpt'          => $wc_product->get_short_description(),
			'post_status'           => $wc_product->get_status(),
			'comment_status'        => null,
			'ping_status'           => null,
			'post_password'         => $wc_product->get_post_password(),
			'post_name'             => $wc_product->get_slug(),
			'to_ping'               => null,
			'pinged'                => null,
			'post_modified'         => $wc_product->get_date_modified(),
			'post_modified_gmt'     => null,
			'post_content_filtered' => null,
			'post_parent'           => $wc_product->get_parent_id(),
			'guid'                  => null,
			'menu_order'            => $wc_product->get_menu_order(),
			'post_type'             => 'product',
			'post_mime_type'        => null,
			'comment_count'         => null,
			'filter'                => null,
			'catalog_visibility'    => $wc_product->get_catalog_visibility(),
			'product_type'          => $wc_product->get_type(),
			'is_featured'           => $wc_product->get_featured(),
			'sku'                   => $wc_product->get_sku(),
		);

		$product['product_type'] = $wc_product->get_type();
		$product['tags']         = $this->_get_product_tags( $product_id );
		$product['categories']   = $this->_get_category_tree( $product_id );

		$product['product_image'] = array(
			'image_src'  => wp_get_attachment_url( get_post_thumbnail_id( $product_id ) ),
			'attachment' => get_post( get_post_thumbnail_id( $product_id ) ),
			'metadata'   => array(
				'_wp_attachment_image_alt' => get_post_meta( get_post_thumbnail_id( $product_id ), '_wp_attachment_image_alt', true ),
			),
		);

		$product['meta'] = array();

		$_meta = $this->get_white_listed_metadata( $product_id, $wc_product );

		foreach ( $_meta as $key => $value ) {
			if ( is_array( $value ) ) {
				$product['meta'][ $key ] = maybe_unserialize( $value[0] );
			} else {
				$product['meta'][ $key ] = maybe_unserialize( $value );
			}
		}

		$product['product_gallery'] = array();

		if ( $gallery_images = $wc_product->get_gallery_image_ids() ) {
			foreach ( $gallery_images as $id ) {
				$product['product_gallery'][] = array(
					'image_src'  => wp_get_attachment_url( $id ),
					'attachment' => get_post( $id ),
				);
			}
		}

		if ( $product_attributes = $wc_product->get_attributes() ) {
			$product['product_attributes'] = $this->_get_product_attributes( $product_attributes );
		}

		if ( $wc_product->get_type() == 'variable' ) {
			$product['product_variations'] = array();

			$variations = $this->get_all_variation_ids( $product_id );

			foreach ( $variations as $variation ) {
				$wc_variation  = wc_get_product( $variation );
				$shipping_data = null;

				if ( $wc_variation->get_shipping_class() ) {
					$shipping_class = wp_get_post_terms( $variation, 'product_shipping_class' );

					if ( ! empty( $shipping_class[0]->term_id ) ) {
						$shipping_data = array(
							'id'          => $shipping_class[0]->term_id,
							'name'        => $shipping_class[0]->name,
							'slug'        => $shipping_class[0]->slug,
							'description' => $shipping_class[0]->name,
						);
					}
				}

				$thumb_id = get_post_thumbnail_id( $wc_variation->get_id() );

				if ( ! empty( $thumb_id ) ) {
					$variation_image = array(
						'image_src' => wp_get_attachment_url( $thumb_id ),
						'ID'        => $thumb_id,
					);
				} else {
					$variation_image = false;
				}

				$product['product_variations'][] = array(
					'product'         => get_post( $variation ),
					'meta'            => $this->get_white_listed_metadata( $variation, $wc_variation ),
					'shipping_class'  => isset( $shipping_data ) ? $shipping_data : array(),
					'stock_status'    => $wc_variation->get_stock_status(),
					'manage_stock'    => $wc_variation->get_manage_stock(),
					'stock_quantity'  => $wc_variation->get_stock_quantity(),
					'backorders'      => $wc_variation->get_backorders(),
					'low_stock'       => $wc_variation->get_low_stock_amount(),
					'sku'             => ! empty( $wc_product->get_sku() ) && $wc_product->get_sku() == $wc_variation->get_sku() ? '' : $wc_variation->get_sku(),
					'variation_image' => $variation_image,
				);
			}
		}

		if ( $wc_product->get_type() == 'grouped' ) {
			$product['grouped_product_ids'] = $wc_product->get_children();
		}

		if ( $upsell = $wc_product->get_upsell_ids() ) {
			$product['upsell'] = $upsell;
		} else {
			$product['upsell'] = array();
		}

		if ( $crosssell = $wc_product->get_cross_sell_ids() ) {
			$product['crosssell'] = $crosssell;
		} else {
			$product['crosssell'] = array();
		}

		if ( $wc_product->get_shipping_class() ) {
			$shipping = wp_get_post_terms( $wc_product->get_id(), 'product_shipping_class' );

			if ( ! empty( $shipping[0]->term_id ) ) {
				$product['shipping_class'] = array(
					'id'          => $shipping[0]->term_id,
					'name'        => $shipping[0]->name,
					'slug'        => $shipping[0]->slug,
					'description' => $shipping[0]->name,
				);
			}
		} else {
			$product['shipping_class'] = array();
		}

		/**
		 * Mian product stock settings.
		 */
		$product['stock_status']   = $wc_product->get_stock_status();
		$product['manage_stock']   = $wc_product->get_manage_stock();
		$product['stock_quantity'] = $wc_product->get_stock_quantity();
		$product['backorders']     = $wc_product->get_backorders();
		$product['low_stock']      = $wc_product->get_low_stock_amount();

		$product = apply_filters( 'WOO_MSTORE_SYNC/process_json/product', $product, $wc_product, $product_id );

		return wp_json_encode( $product );
	}

	/**
	 * Send JSON payload to remote sites
	 *
	 * @param string $product Product JSON
	 * @param string $site_id Site ID
	 * @return array Array containing reponse from child site
	 **/
	public function sync_child_sites( $product, $site_id ) {
		$sites    = get_option( 'woonet_child_sites' );
		$response = array();

		foreach ( $sites as $site ) {
			if ( $site['uuid'] == $site_id ) {
				$data = array(
					'action'        => 'woomulti_child_payload',
					'post_data'     => $product,
					'Authorization' => $site['site_key'],
				);

				$url = $site['site_url'] . '/wp-admin/admin-ajax.php';

				$headers = array(
					'Authorization' => $site['site_key'],
				);

				$result = wp_remote_post(
					$url,
					array(
						'headers' => $headers,
						'body'    => $data,
					)
				);

				if ( is_wp_error( $result ) ) {
					$error_message = $result->get_error_message();
					// response received from child site.
					$response = array(
						'site_url' => $site['site_url'],
						'status'   => 'request_error',
						'error'    => $error_message,
					);

					woomulti_log_error( 'sync_child_sites: Failed.' );
					woomulti_log_error( $response );
				} else {
					// response received from child site.
					$response = array(
						'site_url'    => $site['site_url'],
						'status'      => 'request_success',
						'status_code' => $result['response']['code'],
						'headers'     => $result['headers']->getAll(),
						'response'    => $result['body'],
					);
				}
			}
		}

		return $response;
	}

	/**
	 * Run on the child site. Updates/creates product from JSON received
	 *
	 * @return void
	 */
	public function sync_child() {
		if ( ! $this->is_request_authenticated( $_POST ) ) {
			wp_send_json(
				array(
					'error'   => 1,
					'message' => 'Authentication failed',
				)
			);
		}

		do_action( 'WOO_MSTORE/sync/stock/disable' );

		if ( ! empty( $_POST['post_data'] ) ) {
			$product = json_decode( stripslashes( $_POST['post_data'] ), JSON_OBJECT_AS_ARRAY );

			if ( is_null( $product ) ) {
				$product = json_decode( $_POST['post_data'], JSON_OBJECT_AS_ARRAY );
			}

			$parent_id  = $product['product']['ID'];
			$wc_product = $this->sync_product_attributes( $product );

			if ( ! empty( $wc_product->get_id() ) ) {
				$this->sync_attributes_meta( $wc_product, $product );
				$this->sync_product_meta( $wc_product->get_id(), $product );
				$this->sync_product_image( $wc_product->get_id(), $product );
				$this->sync_product_gallery( $wc_product->get_id(), $product );
				$this->sync_product_tags( $wc_product->get_id(), $product );
				$this->sync_product_categories( $wc_product->get_id(), $product );

				if ( $wc_product->get_type() == 'variable' ) {
					$this->sync_product_variations( $wc_product->get_id(), $product );
				}

				$this->sync_upsell_cross_sell( $wc_product->get_id(), $product );
				$this->sync_grouped_products( $wc_product->get_id(), $product );
				$this->sync_shipping_class( $wc_product->get_id(), $product );

				// @todo: refactor
				$this->stock_status_sync_fix( $wc_product->get_id(), $product );

				do_action( 'WOO_MSTORE_SYNC/sync_child/complete', $wc_product->get_id(), $parent_id, $product );
			}
		}

		do_action( 'WOO_MSTORE/sync/stock/enable' );
	}

	/**
	 * Creates or updates main product details.
	 *
	 * @param mixed $product
	 * @return void
	 */
	public function sync_product_attributes( $product ) {
		$_syncable_attributes = $product['product'];
		$parent_id            = $product['product']['ID'];

		if ( $id = $this->get_mapped_child_post( $parent_id ) ) {
			$wc_product = wc_get_product( $id );

			/**
			 * Fixes the variation bug that was introduced in 4.1.1
			 */
			$this->__fix_variation_sku_duplication_bug( $wc_product );

			/**
			 * If the master product type was been changed,
			 * force change the child product type
			 */
			if ( ! empty( $product['product']['product_type'] )
				&& $product['product']['product_type'] != $wc_product->get_type() ) {
					$this->force_product_type_convertion( $wc_product, $product['product']['product_type'] );
					wp_cache_flush();
					// fetch the product again with updated details.
					$wc_product = wc_get_product( $id );
			}
		} else {
			switch ( $product['product_type'] ) {
				case 'simple':
					$wc_product = new WC_Product_Simple();
					break;

				case 'variable':
					$wc_product = new WC_Product_Variable();
					break;

				case 'grouped':
					$wc_product = new WC_Product_Grouped();
					break;

				case 'external':
					$wc_product = new WC_Product_External();
					break;

				default:
					$wc_product = new WC_Product();
					break;
			}
		}

		if ( $this->get_option( 'child_inherit_changes_fields_control__status', 'yes' ) == 'yes' ) {
			$wc_product->set_status( $_syncable_attributes['post_status'] );
		} elseif ( $wc_product->get_id() === 0 && $this->get_option( 'child_inherit_changes_fields_control__status', 'yes' ) == 'no' ) {
			$wc_product->set_status( 'draft' );
		}

		if ( $this->get_option( 'child_inherit_changes_fields_control__title', 'yes' ) == 'yes' || $wc_product->get_id() === 0 ) {
			$wc_product->set_name( $_syncable_attributes['post_title'] );
		}

		if ( $this->get_option( 'child_inherit_changes_fields_control__description', 'yes' ) == 'yes' ) {
			$wc_product->set_description( $_syncable_attributes['post_content'] );
		}

		if ( $this->get_option( 'child_inherit_changes_fields_control__short_description', 'yes' ) == 'yes' ) {
			$wc_product->set_short_description( $_syncable_attributes['post_excerpt'] );
		}

		if ( $this->get_option( 'child_inherit_changes_fields_control__slug', 'yes' ) == 'yes' || $wc_product->get_id() === 0 ) {
			$wc_product->set_slug( $_syncable_attributes['post_name'] );
		}

		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_post_date', true ) ) {
			$wc_product->set_date_created( $_syncable_attributes['post_date'] );
		}

		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_date_modified', true ) ) {
			$wc_product->set_date_modified( $_syncable_attributes['date_modified'] );
		}

		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_catalog_visibility', true ) ) {
			$wc_product->set_catalog_visibility( $_syncable_attributes['catalog_visibility'] );
		}

		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_menu_order', true ) ) {
			$wc_product->set_menu_order( $_syncable_attributes['menu_order'] );
		}

		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_post_password', true ) ) {
			$wc_product->set_post_password( $_syncable_attributes['post_password'] );
		}

		if ( $this->get_option( 'child_inherit_changes_fields_control__sku', 'yes' ) == 'yes' ) {
			if ( empty( $_syncable_attributes['sku'] ) ) {
				$wc_product->set_sku( '' );
			} elseif ( ! empty( $_syncable_attributes['sku'] ) && $wc_product->get_sku() != $_syncable_attributes['sku'] ) {
				$wc_product->set_sku( $_syncable_attributes['sku'] );
			}
		}

		if ( $this->get_option( 'child_inherit_changes_fields_control__featured', 'yes' ) == 'yes' ) {
			if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_is_featured', true ) ) {
				$wc_product->set_featured( $_syncable_attributes['is_featured'] );
			}
		}

		// link with the parent product.
		$wc_product->update_meta_data( '_woonet_master_product_id', $parent_id );
		$wc_product->update_meta_data( '_woonet_master_product_id_' . $parent_id, $parent_id );

		if ( $wc_product->save() ) {
			return $wc_product;
		}

		return false;
	}

	/**
	 * Get mapped child post on the child site.
	 *
	 * @param integer $parent_post_id Master post ID.
	 * @return mixed
	 */
	public function get_mapped_child_post( $parent_post_id ) {
		global $wpdb;

		/**
		 * New metadata bypass search by meta_value to prevent full table scanning.
		 */
		$meta = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='_woonet_master_product_id_%d'",
				$parent_post_id
			)
		);

		if ( ! empty( $meta->post_id ) ) {
			return $meta->post_id;
		}

		/**
		 * Fallback to old metadata.
		 */
		$meta = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='_woonet_master_product_id' AND meta_value=%d",
				$parent_post_id
			)
		);

		if ( ! empty( $meta->post_id ) ) {
			return $meta->post_id;
		}
		return false;
	}

	public function get_mapped_child_attachment( $parent_post_id ) {
		global $wpdb;

		$meta = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='_woonet_master_attachment_id' AND meta_value=%d",
				$parent_post_id
			)
		);

		if ( ! empty( $meta->post_id ) ) {
			return $meta->post_id;
		}

		return false;
	}

	public function get_mapped_child_term( $parent_term_id ) {
		global $wpdb;
		$meta = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}termmeta WHERE meta_key='_woonet_master_term_id' AND meta_value=%d",
				$parent_term_id
			)
		);

		if ( ! empty( $meta->term_id ) ) {
			return $meta->term_id;
		}

		return false;
	}

	public function sync_product_meta( $child_product_id, $product ) {
		$_options       = get_option( 'woonet_options' );
		$_syncable_meta = $product['meta'];
		$wc_product     = wc_get_product( $child_product_id );

		unset( $_syncable_meta['_product_image_gallery'] );
		// unset( $_syncable_meta['_product_url'] );
		unset( $_syncable_meta['_thumbnail_id'] );
		unset( $_syncable_meta['_edit_lock'] );

		/**
		 * If users migrated the product from a child store and then imported
		 * it back into a parent store, there could be some meta we use to identify
		 * a child product. So here we unset the meta.
		 */
		unset( $_syncable_meta['_woonet_master_product_id'] );

		/**
		 * Hook for the child site to add additional metadata to the sync
		 */
		$_syncable_meta = apply_filters( 'WOO_MSTORE_SYNC/sync_child/meta', $_syncable_meta, $child_product_id, $product );

		if ( WOO_MULTISTORE()->options_manager->get( 'child_inherit_changes_fields_control__price' ) == 'no' ) {
			unset( $_syncable_meta['_price'] );
			unset( $_syncable_meta['_regular_price'] );
		}

		if ( WOO_MULTISTORE()->options_manager->get( 'child_inherit_changes_fields_control__sale_price' ) == 'no' ) {
			unset( $_syncable_meta['_sale_price'] );
		}

		/**
		 * Individual price filters
		 */
		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_product_price', true ) === false ) {
			unset( $_syncable_meta['_price'] );
		}

		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_product_sale_price', true ) === false ) {
			unset( $_syncable_meta['_sale_price'] );
		}

		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_product_regular_price', true ) === false ) {
			unset( $_syncable_meta['_regular_price'] );
		}

		if ( WOO_MULTISTORE()->options_manager->get( 'child_inherit_changes_fields_control__reviews' ) == 'no' ) {
			$_syncable_meta['_wc_review_count'] = get_comments_number( $child_product_id );
			unset( $_syncable_meta['_wc_average_rating'] );
		}

		foreach ( $_syncable_meta as $key => $value ) {
			if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_meta_' . sanitize_key( $key ), true ) ) {
				update_post_meta( $child_product_id, $key, $value );
			}
		}
	}

	public function sync_product_image( $child_product_id, $product ) {
		$_options = get_option( 'woonet_options' );

		/**
		 * If image sync is explicitly disabled, skip syncing
		 */
		if ( isset( $_options['child_inherit_changes_fields_control__product_image'] )
			 && $_options['child_inherit_changes_fields_control__product_image'] == 'no' ) {
			return;
		}

		$product_image = $product['product_image'];

		if ( empty( $product_image['image_src'] ) ) {
			delete_post_meta( $child_product_id, '_thumbnail_id' );
			return;
		}

		/**
		 * Check if global image is enabled.
		 * If global image is enabled, there's no need to download images on the child.
		 */
		// if ( ! empty( $_options['use-global-image'] ) && $_options['use-global-image'] == 'yes' ) {
		// $ghs = WOO_MSTORE_Global_Image_Helper::instance();

		// if ( $id = $ghs->get_default_image_id() ) {
		// set_post_thumbnail( $child_product_id, $id );
		// update_post_meta( $child_product_id, '__woonet_parent_image_url', $product_image['image_src'] );
		// }

		// return true;
		// }

		if ( $id = $this->get_mapped_child_attachment( $product_image['attachment']['ID'] ) ) {
			// check for update
			set_post_thumbnail( $child_product_id, $id );
		} else {
			// create new image and set it as prodouct thumbnail
			$id = media_sideload_image( trim( $product_image['image_src'] ), $child_product_id, null, 'id' );

			if ( ! empty( $id ) && ! is_wp_error( $id ) ) {
				set_post_thumbnail( $child_product_id, $id );
				update_post_meta( $id, '_woonet_master_attachment_id', $product_image['attachment']['ID'] );
			} else {
				error_log( $id->get_error_message() . ' Supplied URL: ' . $product_image['image_src'] );
			}
		}

		/**
		 * By default don't sync image data.
		 */
		if ( ! apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_attachment_meta_data', false ) ) {
			return;
		}

		if ( ! empty( $id ) && ! is_wp_error( $id ) ) {
			$post_array = $product_image['attachment'];

			$post_array['ID'] = $id;
			unset( $post_array['post_author'] );
			unset( $post_array['guid'] );
			unset( $post_array['post_type'] );
			unset( $post_array['post_mime_type'] );

			wp_update_post( $post_array );

			if ( isset( $product_image['metadata']['_wp_attachment_image_alt'] ) ) {
				update_post_meta( $id, '_wp_attachment_image_alt', $product_image['metadata']['_wp_attachment_image_alt'] );
			}
		}
	}

	public function sync_product_gallery( $child_product_id, $product ) {
		$_options = get_option( 'woonet_options' );

		/**
		 * If image sync is explicitly disabled, skip syncing
		 */
		if ( isset( $_options['child_inherit_changes_fields_control__product_gallery'] )
			 && $_options['child_inherit_changes_fields_control__product_gallery'] == 'no' ) {
			return;
		}

		$product_image = $product['product_gallery'];
		$media_ids     = array();

		foreach ( $product_image as $key => $value ) {
			if ( $attachment_id = $this->get_mapped_child_attachment( $value['attachment']['ID'] ) ) {
				// check for update
				$media_ids[] = $attachment_id;
			} else {
				// create new image and set it as prodouct thumbnail
				$id = media_sideload_image( trim( $value['image_src'] ), $child_product_id, null, 'id' );

				if ( ! empty( $id ) && ! is_wp_error( $id ) ) {
					$media_ids[] = $id;
					update_post_meta( $id, '_woonet_master_attachment_id', $value['attachment']['ID'] );
				} else {
					error_log( $id->get_error_message() . ' Supplied URL: ' . $value['image_src'] );
				}
			}
		}

		update_post_meta( $child_product_id, '_product_image_gallery', implode( ',', $media_ids ) );
	}

	/**
	 * Sync variation image for variable product.
	 *
	 * @param mixed $child_variation_id
	 * @param mixed $variation
	 * @return void
	 */
	public function sync_variation_image( $child_variation_id, $variation ) {
		$variation_image = $variation['variation_image'];

		if ( empty( $variation_image['image_src'] ) ) {
			delete_post_meta( $child_variation_id, '_thumbnail_id' );
			return;
		}

		if ( $attachment_id = $this->get_mapped_child_attachment( $variation_image['ID'] ) ) {
			// check for update
			set_post_thumbnail( $child_variation_id, $attachment_id );
		} else {
			// create new image and set it as prodouct thumbnail
			$id = media_sideload_image( trim( $variation_image['image_src'] ), $child_variation_id, null, 'id' );

			if ( ! empty( $id ) && ! is_wp_error( $id ) ) {
				set_post_thumbnail( $child_variation_id, $id );
				update_post_meta( $id, '_woonet_master_attachment_id', $variation_image['ID'] );
			} else {
				error_log( $id->get_error_message() . ' Supplied URL for variation image: ' . $variation_image['image_src'] );
			}
		}
	}

	public function sync_category_thumbnail( $term_id, $data ) {
		$media_id = null;

		if ( $attachment_id = $this->get_mapped_child_attachment( $data['id'] ) ) {
			// check for update
			$media_id = $attachment_id;
		} else {
			// create new image and set it as prodouct thumbnail
			$id = media_sideload_image( trim( $data['url'] ), null, null, 'id' );

			if ( ! empty( $id ) && ! is_wp_error( $id ) ) {
				$media_id = $id;
				update_post_meta( $id, '_woonet_master_attachment_id', $data['id'] );
			} else {
				error_log( $id->get_error_message() . ' Supplied URL: ' . $data['url'] );
			}
		}

		update_term_meta( $term_id, 'thumbnail_id', $media_id );
	}

	public function sync_product_tags( $child_product_id, $product ) {
		$_options = get_option( 'woonet_options' );

		if ( $_options['child_inherit_changes_fields_control__product_tag'] != 'yes' ) {
			return;
		}

		$tags         = $product['tags'];
		$terms_to_add = array();

		if ( ! empty( $tags ) ) {
			foreach ( $tags as $tag ) {
				if ( $id = $this->get_mapped_child_term( $tag['term_id'] ) ) {
					// term exists update it
					unset( $tag['term_id'] );
					unset( $tag['term_taxonomy_id'] );
					unset( $tag['taxonomy'] );
					$terms_to_add[] = (int) $id;
					wp_update_term( $id, 'product_tag', $tag );

					do_action( 'WOO_MSTORE_SYNC/sync_child/tag', $id, $tag );
				} else {
					/**
					 * Check if a tag with the same name exists.
					 */
					$matching_tag = get_term_by( 'name', $tag['name'], 'product_tag' );

					if ( ! empty( $matching_tag->term_id ) ) {
						$terms_to_add[] = (int) $matching_tag->term_id;
						add_term_meta( $matching_tag->term_id, '_woonet_master_term_id', $tag['term_id'] );
					} else {
						// add new term
						$parent_term_id = $tag['term_id'];
						unset( $tag['term_id'] );
						unset( $tag['term_taxonomy_id'] );
						unset( $tag['taxonomy'] );
						$id = wp_insert_term( $tag['name'], 'product_tag', $tag );

						if ( ! is_wp_error( $id ) ) {
							add_term_meta( $id['term_id'], '_woonet_master_term_id', $parent_term_id );
							$terms_to_add[] = (int) $id['term_id'];
							do_action( 'WOO_MSTORE_SYNC/sync_child/tag', $id['term_id'], $tag );
						} else {
							woomulti_log_error( 'Term (product_tag) can not be added. ' . $id->get_error_message() );
						}
					}
				}
			}
		}

		wp_set_post_terms( $child_product_id, $terms_to_add, 'product_tag' );
	}

	public function sync_product_categories( $child_product_id, $product ) {
		$_options = get_option( 'woonet_options' );

		if ( $_options['child_inherit_changes_fields_control__product_cat'] != 'yes' ) {
			return;
		}

		$cat_tree     = $product['categories'];
		$terms_to_add = array();

		foreach ( $cat_tree as $key => $categories ) {
			foreach ( $categories as $category ) {
				if ( ! empty( $category['__thumbnail'] ) ) {
					$category_thumb = $category['__thumbnail'];
					unset( $category['__thumbnail'] );
				}

				if ( $child_term_id = $this->get_mapped_child_term( $category['term_id'] ) ) {
					// term exists update it
					unset( $category['term_id'] );
					unset( $category['term_taxonomy_id'] );
					unset( $category['taxonomy'] );

					if ( $_options['child_inherit_changes_fields_control__category_changes'] != 'yes' ) {
						unset( $category['description'] );
					}

					$category['parent'] = $this->get_mapped_child_term( $category['parent'] );
					wp_update_term( $child_term_id, 'product_cat', $category );

					if ( ! empty( $category['order'] ) ) {
						update_term_meta( $child_term_id, 'parent_order', $category['order'] );
					}

					do_action( 'WOO_MSTORE_SYNC/sync_child/cat', $child_term_id, $category );
				} else {
					/**
					 * Check if a tag with the same name exists.
					 */
					$matching_cat = get_term_by( 'name', $category['name'], 'product_cat' );

					if ( ! empty( $matching_cat->term_id ) ) {
						$terms_to_add[] = (int) $matching_cat->term_id;
						add_term_meta( (int) $matching_cat->term_id, '_woonet_master_term_id', $category['term_id'] );

						if ( ! empty( $category['order'] ) ) {
							update_term_meta( $matching_cat->term_id, 'parent_order', $category['order'] );
						}

						do_action( 'WOO_MSTORE_SYNC/sync_child/cat', $matching_cat->term_id, $category );
					} else {
						// add new term
						$parent_term_id = $category['term_id'];
						unset( $category['term_id'] );
						unset( $category['term_taxonomy_id'] );
						unset( $category['taxonomy'] );

						$category['parent'] = $this->get_mapped_child_term( $category['parent'] );
						$id                 = wp_insert_term( $category['name'], 'product_cat', $category );

						if ( ! is_wp_error( $id ) ) {
							add_term_meta( $id['term_id'], '_woonet_master_term_id', $parent_term_id );
							$child_term_id = $id['term_id'];

							if ( ! empty( $category['order'] ) ) {
								update_term_meta( $id['term_id'], 'parent_order', $category['order'] );
							}

							do_action( 'WOO_MSTORE_SYNC/sync_child/cat', $id['term_id'], $category );
						} else {
							woomulti_log_error( 'Term (product_cat) can not be added. ' . $id->get_error_message() );
							continue;
						}
					}
				}

				$terms_to_add[] = $this->get_mapped_child_term( $key );

				// add or update category thumbnail.
				if ( ! empty( $category_thumb ) ) {
					if ( $_options['child_inherit_changes_fields_control__category_changes'] != 'no' ) {
						$this->sync_category_thumbnail( $child_term_id, $category_thumb );
					}
				} else {
					delete_term_meta( $child_term_id, 'thumbnail_id' );
				}
			}
		}

		wp_set_post_terms( $child_product_id, $terms_to_add, 'product_cat' );
	}

	public function sync_product_variations( $child_product_id, $product ) {
		$_options             = get_option( 'woonet_options' );
		$_existing_variations = $this->get_all_variation_ids( $child_product_id ); // get all current variations before the sync.
		$_synced_variations   = array();

		if ( $_options['child_inherit_changes_fields_control__variations'] != 'yes' ) {
			return;
		}

		if ( empty( $product['product_variations'] ) ) {
			woomulti_log_error( 'No product variation found for product type variable.' );
			return;
		} else {
			$variations = $product['product_variations'];
		}

		// set the created product as a variable product
		// wp_set_object_terms ($child_product_id, 'variable', 'product_type');

		// loop through variations and create them
		foreach ( $variations as $variation ) {
			$_syncable_attributes = $variation['product'];
			$parent_id            = $variation['product']['ID'];

			unset( $_syncable_attributes['ID'] );
			unset( $_syncable_attributes['guid'] );
			unset( $_syncable_attributes['post_author'] );

			$_syncable_attributes['post_parent'] = $child_product_id;

			if ( $child_id = $this->get_mapped_child_post( $parent_id ) ) {
				$_syncable_attributes['ID'] = $child_id;
				$resp                       = wp_update_post( $_syncable_attributes, true );

				if ( is_wp_error( $resp ) ) {
					woomulti_log_error( 'Failed to update variation: ' . $resp->get_error_message() );
				} else {
					$this->sync_variation_meta( $child_id, $variation['meta'], $product );
					$this->stock_status_sync_fix( $child_id, $variation );
					$this->sync_shipping_class( $child_id, $variation );

					if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_variation_image', true ) ) {
						$this->sync_variation_image( $child_id, $variation );
					}

					$_synced_variations[] = $child_id;

					// @todo: implement WooComemrce API for data update.
					if ( $_options['child_inherit_changes_fields_control__variations_sku'] != 'no' ) {
						update_post_meta( $child_id, '_sku', $variation['sku'] );
					}
				}
			} else {
				$resp = wp_insert_post( $_syncable_attributes, true );

				if ( is_wp_error( $resp ) ) {
					woomulti_log_error( 'Failed to insert variation: ' . $resp->get_error_message() );
				} else {
					// $va_product = new WC_Product_Variation( $resp );
					if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_variation_meta', true ) ) {
						$this->sync_variation_meta( $resp, $variation['meta'], $product );
					}

					$this->stock_status_sync_fix( $resp, $variation );
					update_post_meta( $resp, '_woonet_master_product_id', $parent_id );

					$this->sync_shipping_class( $resp, $variation );

					if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_variation_image', true ) ) {
						$this->sync_variation_image( $resp, $variation );
					}

					$_synced_variations[] = $resp;

					// @todo: implement WooComemrce API for data update.
					if ( $_options['child_inherit_changes_fields_control__variations_sku'] != 'no' ) {
						update_post_meta( $resp, '_sku', $variation['sku'] );
					}
				}
			}
		}

		if ( ! empty( $_existing_variations ) ) {
			foreach ( $_existing_variations as $variation_id ) {
				if ( ! in_array( $variation_id, $_synced_variations ) ) {
					wp_delete_post( $variation_id, true );
				}
			}
		}
	}

	/**
	 * Sync shipping class for products and variations.
	 */
	public function sync_shipping_class( $id, $product ) {
		if ( $this->get_option( 'child_inherit_changes_fields_control__shipping_class', 'no' ) != 'yes' ) {
			return;
		}

		if ( empty( $product['shipping_class'] ) ) {
			return wp_set_post_terms( $id, '', 'product_shipping_class' ); // delete post terms if exists
		}

		if ( ! empty( $product['shipping_class']['id'] ) ) {
			$term_id = $this->get_mapped_child_term( (int) $product['shipping_class']['id'] );

			if ( ! empty( $term_id ) ) {
				wp_update_term(
					$term_id,
					'product_shipping_class',
					array(
						'name'        => $product['shipping_class']['name'],
						'slug'        => $product['shipping_class']['slug'],
						'description' => $product['shipping_class']['description'],
					)
				);
			} else {
				$term = wp_insert_term(
					$product['shipping_class']['name'],
					'product_shipping_class',
					array(
						'slug'        => $product['shipping_class']['slug'],
						'description' => $product['shipping_class']['description'],
					)
				);

				if ( is_wp_error( $term ) ) {
					woomulti_log_error( 'Failed to insert shipping class.' );
					woomulti_log_error( $term->get_error_message() );
					return false;
				}

				if ( ! empty( $term['term_id'] ) ) {
					$term_id = $term['term_id'];
					update_term_meta( $term_id, '_woonet_master_term_id', $product['shipping_class']['id'] );
				} else {
					woomulti_log_error( 'Failed to create term for shipping_class.' );
					woomulti_log_error( $product['shipping_class'] );
					return;
				}
			}
		}

		if ( ! empty( $term_id ) ) {
			$term = get_term_by( 'id', $term_id, 'product_shipping_class' );

			if ( is_wp_error( $term ) ) {
				woomulti_log_error( 'get_term failed with error. Can not set shipping class.' );
				woomulti_log_error( $term->get_error_message() );
				return false;
			} elseif ( ! empty( $term->term_id ) ) {
				wp_set_post_terms( $id, $term->slug, 'product_shipping_class' );
			}
		}
	}

	/*
	 * Stock meta is commented out and synced from this function since 4.1.5.
	 * @todo refactor
	 */
	public function stock_status_sync_fix( $id, $product ) {
		$wp = wc_get_product( $id );

		/**
		 * Set false to stop syncing stock status
		 */
		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_stock_status', true ) ) {
			if ( ! empty( $product['stock_status'] ) ) {
				$wp->set_stock_status( $product['stock_status'] );
			} else {
				$wp->set_stock_status();
			}
		}

		/**
		 * Set false to stop syncing manage stock status.
		 */
		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_is_manage_stock', true ) ) {
			if ( ! empty( $product['manage_stock'] ) ) {
				$wp->set_manage_stock( $product['manage_stock'] );
			} else {
				$wp->set_manage_stock( null );
			}
		}

		/**
		 * Set false to stop syncing stock_quantity with the child
		 */
		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_is_stock_quantity', true ) ) {
			if ( ! empty( $product['stock_quantity'] ) ) {
				$wp->set_stock_quantity( $product['stock_quantity'] );
			} else {
				$wp->set_stock_quantity( null );
			}
		}

		/**
		 * Set false to stop syncing backorders with the child
		 */
		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_is_backorders', true ) ) {
			if ( ! empty( $product['backorders'] ) ) {
				$wp->set_backorders( $product['backorders'] );
			} else {
				$wp->set_backorders( false );
			}
		}

		/**
		 * Set false to stop syncing low stock amount status with the child
		 */
		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_low_stock', true ) ) {
			if ( ! empty( $product['low_stock'] ) ) {
				$wp->set_low_stock_amount( $product['low_stock'] );
			} else {
				$wp->set_low_stock_amount( null );
			}
		}

		$wp->save();
	}

	/**
	 * Sync product attributes
	 *
	 * @param mixed $child_product_id
	 * @param mixed $product
	 * @return void
	 */
	public function sync_attributes_meta( $wc_product, $product ) {
		if ( $this->get_option( 'child_inherit_changes_fields_control__attributes', 'yes' ) == 'no' ) {
			return;
		}

		if ( empty( $product['product_attributes'] ) ) {
			return;
		}

		$attributes               = $product['product_attributes'];
		$product_attributes_array = array();

		foreach ( $attributes as $attr ) {
			// process taxonomy.
			if ( ! empty( $attr['taxonomy'] ) ) {
				// check if taxonomy eixsts.
				$id = wc_attribute_taxonomy_id_by_name( $attr['name'] ); // in effect its similar to by_slug

				if ( ! empty( $id ) ) {
					wc_update_attribute(
						$id,
						array(
							'label' => $attr['taxonomy']['attribute_label'],
							'name'  => $attr['taxonomy']['attribute_label'],
							'slug'  => $attr['name'],
						)
					);
				}

				if ( ! $id ) {
					$id = wc_create_attribute(
						array(
							'name'  => $attr['taxonomy']['attribute_label'],
							'label' => $attr['taxonomy']['attribute_label'],
							'slug'  => $attr['name'],
							'type'  => 'select',
						)
					);
				}

				do_action( 'WOO_MSTORE_SYNC/sync_child/attribute', $id, $attr );

				/**
				 * If taxonomy slug on the child is different from the master,
				 * call to term_exists will fail and terms will not be added correctly.
				 * So, we get the taxonomy name on the child by the taxonomy ID.
				 */
				// $_tax_name = wc_attribute_taxonomy_name_by_id( $id );
				$_tax_name = $attr['name'];

				// If taxonomy doesn't exists we create it.
				if ( ! taxonomy_exists( $_tax_name ) ) {
					register_taxonomy(
						$_tax_name,
						'product_variation',
						array(
							'hierarchical' => false,
							'label'        => ucfirst( $attr['taxonomy']['attribute_label'] ),
							'query_var'    => true,
							'rewrite'      => array( 'slug' => sanitize_title( $attr['name'] ) ), // The base slug
						)
					);
				}

				if ( ! is_wp_error( $id ) ) {
					$post_terms_to_add = array();

					foreach ( $attr['terms'] as $term ) {
						if ( ! term_exists( $term['name'], $_tax_name ) ) {
							$_trm = wp_insert_term(
								$term['name'],
								$_tax_name,
								array(
								// 'slug' => $term['slug'],
								)
							);
						}

						if ( ! array_key_exists( $term['slug'], $this->synced_attributes ) ) {
							// fetch the term again to get its slug
							$_trm = get_term_by( 'name', $term['name'], $_tax_name );

							if ( $_trm->slug ) {
								$this->synced_attributes[ $term['slug'] ] = $_trm->slug;
							}
						}

						// $post_term_names =  wp_get_post_terms( $child_product_id, $term['taxonomy'], array('fields' => 'names') );
						// Check if the post term exist and if not we set it in the parent variable product.
						// if( ! in_array( $term['name'], (array) $post_term_names ) ) {
						// $post_terms_to_add[] = $term['name'];
						// }
						$post_terms_to_add[] = $term['name'];

						do_action( 'WOO_MSTORE_SYNC/sync_child/product_term', $_trm->term_id, $term );
					}

					wp_set_post_terms( $wc_product->get_id(), $post_terms_to_add, $_tax_name, false );
				}
			}
		}
	}

	public function sync_variation_meta( $child_variation_id, $variation, $product = null ) {
		$_options       = get_option( 'woonet_options' );
		$_syncable_meta = $variation;

		/**
		 * Don't sync price if price sync is disabled in settings
		 */
		if ( WOO_MULTISTORE()->options_manager->get( 'child_inherit_changes_fields_control__price' ) == 'no' ) {
			unset( $_syncable_meta['_price'] );
			unset( $_syncable_meta['_regular_price'] );
		}

		if ( WOO_MULTISTORE()->options_manager->get( 'child_inherit_changes_fields_control__sale_price' ) == 'no' ) {
			unset( $_syncable_meta['_sale_price'] );
		}

		unset( $_syncable_meta['_thumbnail_id'] );

		// Individual price control
		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_variation_price', true ) === false ) {
			unset( $_syncable_meta['_price'] );
		}

		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_variation_sale_price', true ) === false ) {
			unset( $_syncable_meta['_sale_price'] );
		}

		if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_variation_regular_price', true ) === false ) {
			unset( $_syncable_meta['_regular_price'] );
		}

		/**
		 * If users migrated the product from a child store and then imported
		 * it back into a parent store, there could be some meta we use to identify
		 * a child product. So here we unset the meta.
		 */
		unset( $_syncable_meta['_woonet_master_product_id'] );

		foreach ( $_syncable_meta as $key => $value ) {
			if ( is_array( $value ) && count( $value ) == 1 ) {
				$value = $value[0];
			}

			/**
			 * Sometimes child and master site may have different attributes slug.
			 * If variation meta contains a slug for attributes term that doesn't match
			 * the child term, linking will fail.
			 * This checks the term slugs and replace them if necessary.
			 */

			// $master_attributes = $this->get_master_attributes_array( $product );
			// if ( array_key_exists($key, $master_attributes) ) {
			// compare the terms for the matched taxonomy.
			// $term_name   = str_replace( 'attribute_', '', $key );
			// $child_terms = $this->get_product_terms( $term_name );

			// 1. check if the term slug exists in
			// }

			if ( strpos( $key, 'attribute_pa_' ) === 0
				 && isset( $this->synced_attributes[ $value ] )
				 && $this->synced_attributes[ $value ] != $value ) {
				$value = $this->synced_attributes[ $value ];
			}

			if ( apply_filters( 'WOO_MSTORE_SYNC/sync_child/sync_variation_meta_' . sanitize_key( $key ), true ) ) {
				update_post_meta( $child_variation_id, $key, $value );
			}
		}
	}

	public function _get_category_tree( $product_id ) {
		$cats      = get_the_terms( $product_id, 'product_cat' );
		$cats_tree = array();

		foreach ( $cats as $cat ) {
			$ancestors = get_ancestors( $cat->term_id, 'product_cat' );
			$ancestors = array_reverse( $ancestors );

			if ( ! empty( $ancestors ) ) {
				foreach ( $ancestors as $ancestor ) {
					$thumbnail = array(
						'__thumbnail' => array(),
					);

					if ( ! empty( $thumbnail_id = get_term_meta( $ancestor, 'thumbnail_id', true ) ) ) {
						$thumbnail['__thumbnail'] = array(
							'id'  => $thumbnail_id,
							'url' => wp_get_attachment_url( $thumbnail_id ),
						);
					}

					$cats_tree[ $cat->term_id ][] = array_merge(
						(array) get_term( $ancestor ),
						(array) $thumbnail,
						array(
							'order' => get_term_meta( $ancestor, 'order', true ),
						)
					);
				}
			}

			$thumbnail = array(
				'__thumbnail' => array(),
			);

			if ( $thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true ) ) {
				$thumbnail['__thumbnail'] = array(
					'id'  => $thumbnail_id,
					'url' => wp_get_attachment_url( $thumbnail_id ),
				);
			}

			$cat_data = array_merge(
				(array) $cat,
				(array) $thumbnail,
				array(
					'order' => get_term_meta( $cat->term_id, 'order', true ),
				)
			);

			$cats_tree[ $cat->term_id ][] = apply_filters(
				'WOO_MSTORE_SYNC/process_json/cat',
				$cat_data,
				$cat->term_id
			);
		}

		return $cats_tree;
	}

	/**
	 * Check if the request to master or child site is authenticated
	 */
	public function is_request_authenticated( $auth_in_post_body = false ) {
		$headers = getallheaders();

		if ( ! empty( $auth_in_post_body['Authorization'] ) ) {
			$headers['Authorization'] = $auth_in_post_body['Authorization'];
		}

		if ( empty( $headers['Authorization'] ) ) {
			woomulti_log_error( 'Authentication Error: Authorization header does not exists.' );
			return false;
		}

		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			$data = get_option( 'woonet_child_sites' );

			foreach ( $data as $value ) {
				if ( $value['site_key'] == $headers['Authorization'] ) {
					return true;
				}
			}
		} else {
			$data = get_option( 'woonet_master_connect' );

			if ( $data['key'] == $headers['Authorization'] ) {
				return true;
			}
		}

		return false;
	}

	public function fetch_child_orders( $page = 1, $per_page = 10, $post_status = '', $search = '', $site_id = null ) {
		$data = array(
			'page'        => $page,
			'per_page'    => $per_page,
			'post_status' => $post_status,
			'search'      => $search,
		);

		return $this->request_child( 'woomulti_orders', $data, $site_id );
	}

	public function stock_sync( $site, $payload, $network_type = 'master' ) {
		if ( $network_type == 'master' ) {
			$data = array(
				'action'        => 'child_receive_stock_updates',
				'post_data'     => $payload,
				'Authorization' => $site['site_key'],
			);

			$url = trim( $site['site_url'] ) . '/wp-admin/admin-ajax.php';

			$headers = array(
				'Authorization' => $site['site_key'],
			);

			$result = wp_remote_post(
				$url,
				array(
					'headers' => $headers,
					'body'    => $data,
				)
			);
		} else {
			$data = array(
				'action'        => 'master_receive_stock_updates',
				'post_data'     => $payload,
				'Authorization' => $site['key'], // the index is key on the child.
			);

			$url = trim( $site['master_url'] ) . '/wp-admin/admin-ajax.php';

			$headers = array(
				'Authorization' => $site['key'], // the index is key on the child.
			);

			$result = wp_remote_post(
				$url,
				array(
					'headers' => $headers,
					'body'    => $data,
				)
			);
		}

		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();
			woomulti_log_error( 'Stock Reduce: HTTP ERROR ' . $error_message );
			woomulti_log_error( $result );
			return;
		} else {
			// response received from child site
			if ( isset( $result['response']['code'] ) && $result['response']['code'] != 200 ) {
				woomulti_log_error( 'Stock Reduce: HTTP ERROR' );
				woomulti_log_error( $result['body'] );
				return $result['body'];
			}
		}
	}

	/**
	 * Sync upsell and crossell
	 *
	 * Translate the upsell and crosssell product IDs received from the parent sites
	 * to corresponding child site IDs and update the cross-sell and upsells.
	 *
	 * @param integer $id Mapped product ID on the child site
	 * @param array   $product Product JSON (array) received from parent
	 * @return null;
	 * @since 3.0.1
	 */
	public function sync_upsell_cross_sell( $id, $product ) {
		$types    = array( 'upsell', 'crosssell' );
		$_options = get_option( 'woonet_options' );

		foreach ( $types as $type ) {
			if ( ! isset( $product[ $type ] ) ) {
				continue;
			}

			if ( $this->get_option( 'child_inherit_changes_fields_control__upsell', 'no' ) == 'no' && $type == 'upsell' ) {
				continue;
			}

			if ( $this->get_option( 'child_inherit_changes_fields_control__cross_sells', 'no' ) == 'no' && $type == 'crosssell' ) {
				continue;
			}

			$product_ids             = $product[ $type ];
			$_translated_product_ids = array();

			if ( ! empty( $product_ids ) ) {
				foreach ( $product_ids as $product_id ) {
					if ( $mapped_id = $this->get_mapped_child_post( $product_id ) ) {
						$_translated_product_ids[] = $mapped_id;
					}
				}
			}

			if ( ! empty( $_translated_product_ids ) ) {
				update_post_meta( $id, '_' . $type . '_ids', $_translated_product_ids );
			} else {
				delete_post_meta( $id, '_' . $type . '_ids' );
			}
		}
	}

	/**
	 * Sync grouped products
	 *
	 * Translate the grouped product IDs received from the master site
	 * and sync them with the child site
	 *
	 * @param integer $id Mapped product ID on the child site
	 * @param array   $product Product JSON (array) received from parent
	 * @return null;
	 * @since 3.0.1
	 */
	public function sync_grouped_products( $id, $product ) {
		if ( $product['product_type'] != 'grouped' ) {
			// This is not a grouped product.
			return;
		}

		$product_ids             = $product['grouped_product_ids'];
		$_translated_product_ids = array();

		if ( ! empty( $product_ids ) ) {
			foreach ( $product_ids as $product_id ) {
				if ( $mapped_id = $this->get_mapped_child_post( $product_id ) ) {
					$_translated_product_ids[] = $mapped_id;
				}
			}
		}

		if ( ! empty( $_translated_product_ids ) ) {
			update_post_meta( $id, '_children', $_translated_product_ids );
		} else {
			delete_post_meta( $id, '_children' );
		}
	}

	/**
	 * Sync order status
	 *
	 * Send the updated order status on the child site from the master.
	 *
	 * @param array $posts_data A multidimentional array with post data grouped into site ID
	 * @since 3.0.3
	 */
	public function sync_order_status( $posts_data ) {
		$sites         = get_option( 'woonet_child_sites' );
		$site_response = array();

		if ( ! empty( $sites ) ) {
			foreach ( $sites as $site_data ) {
				if ( empty( $posts_data[ $site_data['uuid'] ] ) ) {
					continue;
				}

				$data = array(
					'action'        => 'woomulti_order_status',
					'Authorization' => $site_data['site_key'],
					'post_data'     => $posts_data[ $site_data['uuid'] ],
				);

				$url = $site_data['site_url'] . '/wp-admin/admin-ajax.php';

				$headers = array(
					'Authorization' => $site_data['site_key'],
				);

				$result = wp_remote_post(
					$url,
					array(
						'headers' => $headers,
						'body'    => $data,
					)
				);

				if ( ! is_wp_error( $result ) ) {
					$resp = json_decode( stripslashes( $result['body'] ), JSON_OBJECT_AS_ARRAY );

					/**
					 * Sometimes the string may not require un-quoting, in which case the above will return null.
					 * If null, then run json_decode without stripslashes.
					 */
					if ( is_null( $resp ) ) {
						$resp = json_decode( $result['body'], JSON_OBJECT_AS_ARRAY );
					}

					if ( ! empty( $resp['status'] ) && ! empty( $resp['message'] ) ) {
						$site_response[ $site_data['uuid'] ] = array(
							'status'  => $resp['status'],
							'message' => $resp['message'],
						);
					} else {
						$site_response[ $site_data['uuid'] ] = array(
							'status'  => 'failed',
							'message' => 'Child site (' . esc_url( $site_data['site_url'] ) . ') did not send a response. Please check that you are running version 3.0.3 or greater on the child site. You may need to manually update the plugin on the child site.',
						);
					}
				} else {
					$site_response[ $site_data['uuid'] ] = array(
						'status'  => 'failed',
						'message' => $result->get_error_message(),
					);

					woomulti_log_error( 'HTTP ERROR: Order status update failed.' );
					woomulti_log_error( $result );
				}
			}
		}

		return $site_response;
	}

	/**
	 * Get all variation IDs of a product
	 */
	public function get_all_variation_ids( $product_id ) {
		$all_args = array(
			'post_parent' => $product_id,
			'post_type'   => 'product_variation',
			'orderby'     => array(
				'menu_order' => 'ASC',
				'ID'         => 'ASC',
			),
			'fields'      => 'ids',
			'post_status' => array( 'publish', 'private' ),
			'numberposts' => -1, // phpcs:ignore WordPress.VIP.PostsPerPage.posts_per_page_numberposts
		);

		$ids = get_posts( $all_args );

		if ( $ids ) {
			return wp_parse_id_list( (array) $ids );
		}

		return null;
	}

	/**
	 * Check if meta key is whitelisted
	 *
	 * @since 3.0.4
	 *
	 * @param string $meta_key The meta key
	 * @return bool
	 */
	public function is_meta_white_listed( $meta_key ) {
		if ( in_array( $meta_key, $this->whitelisted_metadata ) ) {
			return true;
		}

		foreach ( $this->whitelisted_metadata as $_meta ) {
			if ( substr( $_meta, 0 ) == '%' || substr( $_meta, -1 ) == '%' ) {
				$_match = str_replace( '%', '', $_meta );
				if ( strpos( $meta_key, $_match ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Return all whitelisted metadata
	 *
	 * @since 3.0.4
	 *
	 * @param string $product_id Product ID
	 * @return array
	 */
	public function get_white_listed_metadata( $product_id, $wc_product ) {
		$_meta             = get_post_meta( $product_id );
		$_whitelisted_meta = array();

		if ( empty( $_meta ) ) {
			woomulti_log_error( 'Failed to retrieve metadata.' );
			return array();
		}

		/**
		 * All metadata sync setting is ignored as it's causing problems with 3rd party plugins.
		 */
		// if ( $this->get_option( 'sync-all-metadata' ) == 'yes' ) {
		// return $_meta;
		// }

		foreach ( $_meta as $key => $value ) {
			if ( $this->is_meta_white_listed( $key ) ) {
				$_whitelisted_meta[ $key ] = $value;
			}
		}

		$_whitelisted_meta = apply_filters( 'WOO_MSTORE_SYNC/process_json/meta', $_whitelisted_meta, $product_id, $wc_product );

		return $_whitelisted_meta;
	}

	/**
	 * Get the value of a setting
	 *
	 * @since 3.0.4
	 *
	 * @param string $option_name The key of the option defined on the settings page
	 * @param string $default Default value to return, if key does not exist.
	 * @return string Either the value defined in settings or the default
	 */
	public function get_option( $option_name, $default = 'no' ) {
		if ( empty( $this->options ) ) {
			$this->options = get_option( 'woonet_options' );
		}

		if ( isset( $this->options[ $option_name ] ) ) {
			return $this->options[ $option_name ];
		}

		return $default;
	}

	/**
	 * Sync order status
	 *
	 * Send the updated order status on the child site from the master.
	 *
	 * @param integer $parent_post_id The post id of the parent post
	 * @param string  $status The changed status of the post
	 * @since 3.0.3
	 */
	public function trash_untrash_delete_post( $parent_post_id, $status ) {
		$sites         = get_option( 'woonet_child_sites' );
		$site_response = array();

		if ( ! empty( $sites ) ) {
			foreach ( $sites as $site_data ) {
				$data = array(
					'action'             => 'woomulti_trash_untrash',
					'Authorization'      => $site_data['site_key'],
					'parent_post_id'     => $parent_post_id,
					'parent_post_status' => $status,
				);

				$url = $site_data['site_url'] . '/wp-admin/admin-ajax.php';

				$headers = array(
					'Authorization' => $site_data['site_key'],
				);

				$result = wp_remote_post(
					$url,
					array(
						'headers' => $headers,
						'body'    => $data,
					)
				);

				if ( ! is_wp_error( $result ) ) {
					$resp = json_decode( stripslashes( $result['body'] ), JSON_OBJECT_AS_ARRAY );

					/**
					 * Sometimes the string may not require un-quoting, in which case the above will return null.
					 * If null, then run json_decode without stripslashes.
					 */
					if ( is_null( $resp ) ) {
						$resp = json_decode( $result['body'], JSON_OBJECT_AS_ARRAY );
					}

					if ( ! empty( $resp['status'] ) && ! empty( $resp['message'] ) ) {
						$site_response[ $site_data['uuid'] ] = array(
							'status'  => $resp['status'],
							'message' => $resp['message'],
						);
					} else {
						$site_response[ $site_data['uuid'] ] = array(
							'status'  => 'failed',
							'message' => 'Child site (' . esc_url( $site_data['site_url'] ) . ') did not send a response. Please check that you are running version 3.0.5 or greater on the child site. You may need to manually update the plugin on the child site.',
						);
					}
				} else {
					$site_response[ $site_data['uuid'] ] = array(
						'status'  => 'failed',
						'message' => $result->get_error_message(),
					);

					woomulti_log_error( 'HTTP ERROR: Trash product failed.' );
					woomulti_log_error( $result );
				}
			}
		}

		return $site_response;
	}

	/**
	 * Run on the master network. Send the site URL and the license key to check for updates.
	 *
	 * @return string
	 */
	public function send_data_for_update() {
		$license_data = get_option( 'mstore_license' );

		return json_encode(
			array(
				'key'    => $license_data['key'],
				'domain' => WOO_MSTORE_INSTANCE,
			)
		);
	}

	public function get_data_for_update() {
		$site = get_option( 'woonet_master_connect' );

		$data = array(
			'action'        => 'send_update_data',
			'Authorization' => $site['key'],
		);

		$url = trim( $site['master_url'] ) . '/wp-admin/admin-ajax.php';

		$headers = array(
			'Authorization' => $site['key'],
		);

		$result = wp_remote_post(
			$url,
			array(
				'headers' => $headers,
				'body'    => $data,
			)
		);

		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();
			woomulti_log_error( 'Child site Update: HTTP ERROR ' . $error_message );
			woomulti_log_error( $result );
			return;
		} else {
			// response received from child site.
			if ( isset( $result['response']['code'] ) && $result['response']['code'] != 200 ) {
				woomulti_log_error( 'Child site Update: HTTP ERROR' );
				woomulti_log_error( $result['body'] );
				return;
			} else {
				return $result['body'];
			}
		}
	}

	private function _get_product_tags( $product_id ) {
		$terms       = get_the_terms( $product_id, 'product_tag' );
		$terms_array = array();

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$terms_array[] = (array) apply_filters(
					'WOO_MSTORE_SYNC/process_json/tag',
					(array) $term,
					$product_id
				);
			}
		}

		return $terms_array;
	}

	public function _get_product_attributes( $product_attributes ) {
		$product_attributes_array = array();

		foreach ( $product_attributes as $pa ) {
			$terms       = $pa->get_terms();
			$terms_array = array();

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$terms_array[] = apply_filters( 'WOO_MSTORE_SYNC/process_json/product_term', (array) $term, $term->term_id );
				}
			}

			$attr = array(
				'id'       => $pa->get_id(),
				'name'     => $pa->get_name(),
				'slug'     => $pa->get_name(), // name is slug
				'options'  => $pa->get_options(),
				'terms'    => $terms_array,
				'taxonomy' => $pa->get_taxonomy_object(),
			);

			$product_attributes_array[] = apply_filters( 'WOO_MSTORE_SYNC/process_json/attribute', $attr, $pa->get_id() );
		}

		return $product_attributes_array;
	}

	/**
	 * Send data payload to a connected site with custom action.
	 *
	 * @param mixed $data
	 * @param mixed $site_id
	 * @return void
	 */
	public function send_payload( $payload_data, $site_id = null ) {
		return $this->request_child( 'woomulti_custom_payload', $payload_data, $site_id );
	}


	/**
	 * Send a request to the child site or the master site.
	 *
	 * @param string $action AJAX action name
	 * @param string $payload_data data to send with POST request
	 * @param mixed  $site_id If the request is for a particular site ID, set site ID
	 * @param string $request_type HTTP Request type - GET or POST.
	 * @return array Array with respose from each site requested
	 *
	 * @since 4.0.0
	 */
	public function request_child( $action, $payload_data = null, $site_id = null, $request_type = 'post' ) {
		$sites         = get_option( 'woonet_child_sites' );
		$site_response = array();

		if ( ! empty( $sites ) ) {
			foreach ( $sites as $site_data ) {
				if ( $site_id !== null && ! is_array( $site_id ) ) {
					$site_id = array( $site_id );
				}

				if ( ! empty( $site_id ) && ! in_array( $site_data['uuid'], $site_id ) ) {
					continue;
				}

				$data = array(
					'action'        => $action,
					'Authorization' => $site_data['site_key'],
					'data'          => $payload_data,
				);

				$url = $site_data['site_url'] . '/wp-admin/admin-ajax.php';

				$headers = array(
					'Authorization' => $site_data['site_key'],
				);

				if ( 'post' === $request_type ) {
					$result = wp_remote_post(
						$url,
						array(
							'headers' => $headers,
							'body'    => $data,
						)
					);
				} else {
					// @todo: implement get request and other requst methods.
				}

				if ( ! is_wp_error( $result ) ) {
					$resp = json_decode( stripslashes( $result['body'] ), JSON_OBJECT_AS_ARRAY );

					/**
					 * Sometimes the string may not require un-quoting, in which case the above will return null.
					 * If null, then run json_decode without stripslashes.
					 */
					if ( is_null( $resp ) ) {
						$resp = json_decode( $result['body'], JSON_OBJECT_AS_ARRAY );
					}

					if ( ! empty( $resp['status'] ) && ! empty( $resp['result'] ) ) {
						$site_response[ $site_data['uuid'] ] = array(
							'status'  => 'success',
							'result'  => $resp['result'],
							'message' => ! empty( $resp['message'] ) ? $resp['message'] : '',
						);
					} else {
						$site_response[ $site_data['uuid'] ] = array(
							'status'  => 'failed',
							'result'  => null,
							'message' => 'Child site (' . esc_url( $site_data['site_url'] ) . ') did not send a response. Please check if all your child sites are up and you are running the same version on all of them. You may need to manually update the plugin if you are running a version older than 4.0.0',
						);
					}
				} else {
					$site_response[ $site_data['uuid'] ] = array(
						'status'  => 'failed',
						'result'  => null,
						'message' => $result->get_error_message(),
					);

					woomulti_log_error( "HTTP ERROR: action:{$action}, site_id:{$site_id}, request_type:{$request_type}" );
					woomulti_log_error( $result );
				}
			}
		}

		return $site_response;
	}

	/**
	 * Send a request to the master site.
	 *
	 * @param string $action AJAX action name
	 * @param string $payload_data data to send with POST request
	 * @param string $request_type HTTP Request type - GET or POST.
	 * @return array Array with respose from each site requested
	 *
	 * @since 4.1.2
	 */
	public function request_master( $action, $payload_data = null, $request_type = 'post' ) {
		$master_site   = get_option( 'woonet_master_connect' );
		$site_response = array();

		if ( ! empty( $master_site ) ) {
			$data = array(
				'action'        => $action,
				'Authorization' => $master_site['key'],
				'data'          => $payload_data,
			);

			$url = $master_site['master_url'] . '/wp-admin/admin-ajax.php';

			$headers = array(
				'Authorization' => $master_site['key'],
			);

			if ( 'post' === $request_type ) {
				$result = wp_remote_post(
					$url,
					array(
						'headers' => $headers,
						'body'    => $data,
					)
				);
			} else {
				// @todo: implement get request and other requst methods.
			}

			if ( ! is_wp_error( $result ) ) {
				$resp = json_decode( stripslashes( $result['body'] ), JSON_OBJECT_AS_ARRAY );

				/**
				 * Sometimes the string may not require un-quoting, in which case the above will return null.
				 * If null, then run json_decode without stripslashes.
				 */
				if ( is_null( $resp ) ) {
					$resp = json_decode( $result['body'], JSON_OBJECT_AS_ARRAY );
				}

				if ( ! empty( $resp['status'] ) && ! empty( $resp['result'] ) ) {
					$site_response = array(
						'status'  => 'success',
						'result'  => $resp['result'],
						'message' => ! empty( $resp['message'] ) ? $resp['message'] : '',
					);
				} else {
					$site_response = array(
						'status'  => 'failed',
						'result'  => null,
						'message' => 'Child site (' . esc_url( $master_site['master_url'] ) . ') did not send a response. Please check if all your child sites are up and you are running the same version on all of them. You may need to manually update the plugin if you are running a version older than 4.0.0',
					);
				}
			} else {
				$site_response = array(
					'status'  => 'failed',
					'result'  => null,
					'message' => $result->get_error_message(),
				);

				woomulti_log_error( "HTTP ERROR: action:{$action}, request_type:{$request_type}" );
				woomulti_log_error( $result );
			}
		}

		return $site_response;
	}

	/**
	 * Get options/settings from each site.
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->request_child( 'woomulti_child_options_get' );
	}

	/**
	 * Update options/settings from each site.
	 *
	 * @param $data options for all sites
	 * @return array
	 */
	public function update_options( $data ) {
		return $this->request_child( 'woomulti_child_options_update', $data );
	}

	/**
	 * Get version from the child sites
	 *
	 * @param $data options for all sites
	 * @return array
	 */
	public function get_versions() {
		return $this->request_child( 'woomulti_version' );
	}

	/**
	 * Get blog names.
	 *
	 * @return array
	 */
	public function get_blogname() {
		return $this->request_child( 'woomulti_get_blognames' );
	}

	/**
	 * Get order data for export function.
	 *
	 * @return array
	 */
	public function get_order_exports( $data, $site_id ) {
		return $this->request_child( 'woomulti_get_order_exports', $data, $site_id );
	}

	/**
	 * check_connection_details
	 *
	 * @param mixed $data
	 * @return void
	 */
	public function check_connection_details() {
		return $this->request_child( 'woomulti_check_site_connection' );
	}

	/**
	 * Fix duplicate SKU bug
	 *
	 * Version 4.0.0 switched to WooCommerce's get_sku() function to get
	 * variable SKU instead of directly updating the metadata. This introduced
	 * a duplicate SKU bug on the child.
	 *
	 * Check if a product is a variable product. And if variations have the same SKU as the main product,
	 * remove the SKU from variations.
	 */
	private function __fix_variation_sku_duplication_bug( $wc_product ) {
		if ( empty( $wc_product->get_sku() ) || $wc_product->get_type() != 'variable' ) {
			return;
		}

		$_existing_variations = $this->get_all_variation_ids( $wc_product->get_id() );

		if ( ! empty( $_existing_variations ) ) {
			foreach ( $_existing_variations as $vid ) {
				$_sku = get_post_meta( $vid, '_sku', true );

				if ( ! empty( $_sku ) && $_sku == $wc_product->get_sku() ) {
					delete_post_meta( $vid, '_sku' );
				}
			}
		}
	}

	private function force_product_type_convertion( $wc_product, $master_type ) {
		wp_remove_object_terms( $wc_product->get_id(), $wc_product->get_type(), 'product_type' );
		wp_set_object_terms( $wc_product->get_id(), $master_type, 'product_type' );
	}
}
