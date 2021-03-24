<?php

/**
 * Network Bulk Updater
 *
 * @class   WOO_MSTORE_BULK_SYNC
 * @since   2.0.20
 */
class WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE {

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
		"_sku",
		"total_sales",
		"_tax_status",
		"_tax_class",
		//"_manage_stock",
		//S"_backorders",
		"_sold_individually",
		"_virtual",
		"_downloadable",
		"_download_limit",
		"_download_expiry",
		//"_stock",
		//"_stock_status",
		"_wc_average_rating",
		"_wc_review_count",
		"_product_version",
		"_wpcom_is_markdown",
		"_wp_old_slug",
		"_price",
		"_regular_price",
		"_sale_price",
		"_length",
		"_width",
		"_weight",
		"_height",
		"_thumbnail_id",
		"_product_attributes",
		"_edit_last",
		//"_low_stock_amount",
		"_upsell_ids",
		"_crosssell_ids",
		"_purchase_note",
		"_downloadable_files",
		"_children",
		"_product_image_gallery",
		"_variation_description",
		"attribute_pa_%",
		"_woonet_%",
		"woonet_%",
	);

	/**
	 * Settings defined on the settings page
	 *
	 * @since 3.0.4
	 * @var array
	 */
	public $options = array();

	/**
	 * Run the sync operation
	 */
	public function sync( $product_id, $site_id ) {
		$product = self::product_to_json( $product_id );

		if ( ! woomulti_has_min_user_role() ) {
			return;
		}

		if ( ! woomulti_has_valid_license() ) {
			return;
		}

		if ( $product ) {
			$response = self::sync_child_sites( $product, $site_id );
		}
	}

	/**
	 * Convert product details and metadata to JSON, which will be sent to child sites
	 */
	public function product_to_json( $product_id ) {

		$wc_product = wc_get_product( $product_id );

		$product                 = array();
		$product['product']      = get_post( $product_id );
		$product['product_type'] = $wc_product->get_type();
		$product['tags']         = get_the_terms( $product_id, 'product_tag' );
		$product['categories']   = $this->get_category_tree( $product_id );

		$product['product_image'] = array(
			'image_src'  => wp_get_attachment_url( get_post_thumbnail_id( $product_id ) ),
			'attachment' => get_post( get_post_thumbnail_id( $product_id ) ),
			// 'metadata'   => wp_get_attachment_metadata( get_post_thumbnail_id( $product_id ) ),
		);

		$product['meta'] = array();

		$_meta = $this->get_white_listed_metadata( $product_id );

		foreach ( $_meta as $key => $value ) {
			$product['meta'][ $key ] = maybe_unserialize( $value[0] );
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
			$product['product_attributes'] = array();

			foreach ( $product_attributes as $pa ) {
				$product['product_attributes'][] = array(
					'id'       => $pa->get_id(),
					'name'     => $pa->get_name(),
					'slug'     => $pa->get_name(), //name is slug
					'options'  => $pa->get_options(),
					'terms'    => $pa->get_terms(),
					'taxonomy' => $pa->get_taxonomy_object(),
				);
			}
		}

		if ( $wc_product->get_type() == 'variable' ) {
			$product['product_variations'] = array();

			$variations = $this->get_all_variation_ids( $product_id );

			foreach ( $variations as $variation ) {

				$wc_variation = wc_get_product( $variation );
				$shipping_data = null;

				if ( $wc_variation->get_shipping_class() ) {
					$shipping_class = wp_get_post_terms( $variation, 'product_shipping_class');

					if ( !empty( $shipping_class[0]->term_id ) ) {
						$shipping_data = array(
							'id'   => $shipping_class[0]->term_id,
							'name' => $shipping_class[0]->name,
							'slug' => $shipping_class[0]->slug,
							'description' => $shipping_class[0]->name,
						);
					}
				}

				$product['product_variations'][] = array(
					'product' => get_post( $variation ),
					'meta'    => $this->get_white_listed_metadata( $variation ),
					'shipping_class' => isset( $shipping_data ) ? $shipping_data : array(),
					'stock_status' => $wc_variation->get_stock_status(),
					'manage_stock' => $wc_variation->get_manage_stock(),
					'stock_quantity' => $wc_variation->get_stock_quantity(),
					'backorders' => $wc_variation->get_backorders(),
					'low_stock' => $wc_variation->get_low_stock_amount(),
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
			$shipping = wp_get_post_terms( $wc_product->get_id(), 'product_shipping_class');

			if ( !empty( $shipping[0]->term_id ) ) {
				$product['shipping_class'] = array(
					'id'   => $shipping[0]->term_id,
					'name' => $shipping[0]->name,
					'slug' => $shipping[0]->slug,
					'description' => $shipping[0]->name,
				);
			}
		} else {
			$product['shipping_class'] = array();
		}

		if ( $wc_product->get_stock_status() ) {
			$product['stock_status'] = $wc_product->get_stock_status();
			$product['manage_stock'] = $wc_product->get_manage_stock();
			$product['stock_quantity'] = $wc_product->get_stock_quantity();
			$product['backorders'] = $wc_product->get_backorders();
			$product['low_stock'] = $wc_product->get_low_stock_amount();
		}

		return json_encode( $product );
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
					// response received from child site
					$response = array(
						'site_url' => $site['site_url'],
						'status'   => 'request_error',
						'error'    => $error_message,
					);

					woomulti_log_error( "sync_child_sites: Failed." );
					woomulti_log_error( $response );
				} else {
					// response received from child site
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

	public function sync_child() {

		if ( ! $this->is_request_authenticated( $_POST ) ) {
			echo json_encode(
				array(
					'error'   => 1,
					'message' => 'Authentication failed',
				)
			);
			die;
		}

		if ( ! empty( $_POST['post_data'] ) ) {
			$product   = json_decode( stripslashes( $_POST['post_data'] ), JSON_OBJECT_AS_ARRAY );

			if ( is_null($product) ) {
				$product   = json_decode( $_POST['post_data'], JSON_OBJECT_AS_ARRAY );
			}

			$parent_id = $product['product']['ID'];
			$id        = $this->sync_product_attributes( $product );

			if ( is_int( $id ) ) {
				$this->sync_attributes_meta( $id, $product );
				$this->sync_product_meta( $id, $product );
				$this->sync_product_image( $id, $product );
				$this->sync_product_gallery( $id, $product );
				$this->sync_product_tags( $id, $product );
				$this->sync_product_categories( $id, $product );
				$this->sync_product_variations( $id, $product );
				$this->sync_upsell_cross_sell( $id, $product );
				$this->sync_grouped_products( $id, $product );
				$this->sync_shipping_class( $id, $product );

				// @todo: refactor 
				$this->stock_status_sync_fix($id, $product);
			}
		}
	}

	public function sync_product_attributes( $product ) {
		$_syncable_attributes = $product['product'];
		$parent_id            = $product['product']['ID'];
		$_options             = get_option( 'woonet_options' );

		unset( $_syncable_attributes['ID'] );
		unset( $_syncable_attributes['guid'] );
		unset( $_syncable_attributes['post_author'] );

		if ( $_options['child_inherit_changes_fields_control__title'] != 'yes' ) {
			unset( $_syncable_attributes['post_title'] );
		}

		if ( $_options['child_inherit_changes_fields_control__description'] != 'yes' ) {
			unset( $_syncable_attributes['post_content'] );
		}

		if ( $_options['child_inherit_changes_fields_control__short_description'] != 'yes' ) {
			unset( $_syncable_attributes['post_excerpt'] );
		}

		if ( $_options['child_inherit_changes_fields_control__slug'] != 'yes' ) {
			unset( $_syncable_attributes['post_name'] );
		}

		if ( $child_id = $this->get_mapped_child_post( $parent_id ) ) {
			$_syncable_attributes['ID'] = $child_id;
			$resp                       = wp_update_post( $_syncable_attributes );
		} else {
			$resp = wp_insert_post( $_syncable_attributes );
			update_post_meta( $resp, '_woonet_master_product_id', $parent_id );
		}

		// add/update post type
		if ( $product['product_type'] && ! is_wp_error( $resp ) ) {
			wp_remove_object_terms( $resp, array( 'simple', 'grouped', 'variable', 'external' ), 'product_type' );
			wp_set_object_terms( $resp, $product['product_type'], 'product_type' );
		}

		if ( is_wp_error( $resp ) ) {
			// @todo better error handling. Maybe log it on child/master.
			return false;
		} else {
			return $resp; // POST ID
		}
	}

	public function get_mapped_child_post( $parent_post_id ) {
		global $wpdb;
		$meta = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key="_woonet_master_product_id" AND meta_value=' . $parent_post_id );

		if ( ! empty( $meta->post_id ) ) {
			return $meta->post_id;
		}
		return false;
	}

	public function get_mapped_child_attachment( $parent_post_id ) {
		global $wpdb;
		$meta = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key="_woonet_master_attachment_id" AND meta_value=' . $parent_post_id );

		if ( ! empty( $meta->post_id ) ) {
			return $meta->post_id;
		}

		return false;
	}

	public function get_mapped_child_term( $parent_term_id ) {
		global $wpdb;
		$meta = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'termmeta WHERE meta_key="_woonet_master_term_id" AND meta_value=' . $parent_term_id );

		if ( ! empty( $meta->term_id ) ) {
			return $meta->term_id;
		}

		return false;
	}

	public function sync_product_meta( $child_product_id, $product ) {
		$_options       = get_option( 'woonet_options' );
		$_syncable_meta = $product['meta'];

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
		
		if ( $_options['child_inherit_changes_fields_control__price'] == 'no' ) {
			unset( $_syncable_meta['_price'] );
			unset( $_syncable_meta['_sale_price'] );
			unset( $_syncable_meta['_regular_price'] );
		}

		foreach ( $_syncable_meta as $key => $value ) {
			update_post_meta( $child_product_id, $key, $value );
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

		if ( $attachment_id = $this->get_mapped_child_attachment( $product_image['attachment']['ID'] ) ) {
			// check for update
			set_post_thumbnail( $child_product_id, $attachment_id );

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
				if ( $child_term_id = $this->get_mapped_child_term( $tag['term_id'] ) ) {
					// term exists update it
					unset( $tag['term_id'] );
					unset( $tag['term_taxonomy_id'] );
					unset( $tag['taxonomy'] );
					$terms_to_add[] = (int) $child_term_id;
					wp_update_term( $child_term_id, 'product_tag', $tag );
				} else {
					/**
					 * Check if a tag with the same name exists.
					 */
					$matching_tag = get_term_by('name', $tag['name'], 'product_tag');

					if ( !empty( $matching_tag->term_id) ) {
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

					$category['parent'] = $this->get_mapped_child_term( $category['parent'] );
					wp_update_term( $child_term_id, 'product_cat', $category );
				} else {
					/**
					 * Check if a tag with the same name exists.
					 */
					$matching_cat = get_term_by('name', $category['name'], 'product_cat');

					if ( !empty( $matching_cat->term_id) ) {
						$terms_to_add[] = (int) $matching_cat->term_id;
						add_term_meta( (int) $matching_cat->term_id, '_woonet_master_term_id', $category['term_id'] );
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
						} else {
							woomulti_log_error( 'Term (product_cat) can not be added. ' . $id->get_error_message() );
							continue;
						}
					}
				}

				$terms_to_add[] = $this->get_mapped_child_term( $key );

				// add or update category thumbnail
				if ( ! empty( $category_thumb ) ) {
					$this->sync_category_thumbnail( $child_term_id, $category_thumb );
				} else {
					delete_term_meta( $child_term_id, 'thumbnail_id' );
				}
			}
		}

		wp_set_post_terms( $child_product_id, $terms_to_add, 'product_cat' );

	}

	public function sync_product_variations( $child_product_id, $product ) {

		$_options = get_option( 'woonet_options' );
		$_existing_variations = $this->get_all_variation_ids( $child_product_id ); //get all current variations before the sync.
		$_synced_variations = array();

		if ( $_options['child_inherit_changes_fields_control__variations'] != 'yes' ) {
			return;
		}

		if ( empty( $product['product_variations'] ) ) {
			woomulti_log_error("No product variation found for product type variable.");
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
				$resp = wp_update_post( $_syncable_attributes, true );

				if ( is_wp_error( $resp ) ) {
					woomulti_log_error('Failed to update variation: ' . $resp->get_error_message() );
				} else {
					$this->sync_variation_meta( $child_id, $variation['meta'], $product );
					$this->stock_status_sync_fix(  $child_id, $variation );
					$this->sync_shipping_class( $child_id, $variation );
					$_synced_variations[] = $child_id;
				}
			} else {
				$resp = wp_insert_post( $_syncable_attributes, true );

				if ( is_wp_error( $resp ) ) {
					woomulti_log_error('Failed to insert variation: ' . $resp->get_error_message() );
				} else {
					//$va_product = new WC_Product_Variation( $resp );
					$this->sync_variation_meta( $resp, $variation['meta'], $product );
					$this->stock_status_sync_fix(  $resp, $variation );
					update_post_meta( $resp, '_woonet_master_product_id', $parent_id );

					$this->sync_shipping_class( $resp, $variation );
					$_synced_variations[] = $resp;
				}
			}
		}

		if ( !empty( $_existing_variations ) ) {
			foreach ( $_existing_variations as $variation_id ) {
				if ( ! in_array($variation_id, $_synced_variations) ) {
					wp_delete_post( $variation_id, true);
				}
			}
		}

		//set product children
		//update_post_meta( $child_product_id, '_children', $_synced_variations);
	}

	/**
	 * Sync shipping class for products and variations.
	 */
	public function sync_shipping_class( $id, $product ) {
		if ( empty( $product['shipping_class'] ) ) {
			return wp_set_post_terms( $id, '', 'product_shipping_class' ); //delete post terms if exists
		}

		if ( !empty( $product['shipping_class']['id'] ) ) {
			$term_id = $this->get_mapped_child_term( (int) $product['shipping_class']['id'] );

			if ( !empty($term_id) ) {
				wp_update_term( $term_id, 'product_shipping_class', array(
					'name' 		  => $product['shipping_class']['name'],
					'slug' 		  => $product['shipping_class']['slug'],
					'description' =>  $product['shipping_class']['description'],
				));
			} else {
				$term = wp_insert_term( $product['shipping_class']['name'], 'product_shipping_class', array(
					'slug' 		  => $product['shipping_class']['slug'],
					'description' =>  $product['shipping_class']['description'],
				));

				if ( !empty($term['term_id']) ) {
					$term_id = $term['term_id'];
				} else {
					woomulti_log_error('Failed to create term for shipping_class.');
					woomulti_log_error( $product['shipping_class'] );
					return;
				}

				update_term_meta( $term_id, '_woonet_master_term_id', $product['shipping_class']['id'] );
			}
		}

		if ( ! empty( $term_id ) ) {
			wp_set_post_terms( $id, $product['shipping_class']['slug'], 'product_shipping_class' );
		}
	}

	/*
	 * Stock status is not being synced correctly
	 * This makes a temporary fix.
	 * @todo refactor
	 */
	public function stock_status_sync_fix($id, $product) {

		$wp = wc_get_product( $id );

		if ( !empty($product['stock_status']) ) {
			$wp->set_stock_status( $product['stock_status'] );
		} else {
			$wp->set_stock_status( $product['stock_status'] );
		}

		if ( !empty($product['manage_stock']) ) {
			$wp->set_manage_stock( $product['manage_stock'] );
		}

		if ( !empty($product['stock_quantity']) ) {
			$wp->set_stock_quantity( $product['stock_quantity'] );
		}

		if ( !empty($product['backorders']) ) {
			$wp->set_backorders( $product['backorders'] );
		}

		if ( !empty($product['low_stock']) ) {
			$wp->set_low_stock_amount( $product['low_stock'] );
		}
	}

	public function sync_attributes_meta( $child_product_id, $product ) {

		$_options = get_option( 'woonet_options' );

		if ( $_options['child_inherit_changes_fields_control__attributes'] != 'yes' ) {
			return;
		}

		if ( empty( $product['product_attributes'] ) ) {
			return;
		}

		$attributes               = $product['product_attributes'];
		$product_attributes_array = array();

		foreach ( $attributes as $attr ) {
			// process taxonomy
			if ( ! empty( $attr['taxonomy'] ) ) {
				// check if taxonomy eixsts
				$id = wc_attribute_taxonomy_id_by_name( $attr['name'] ); //in effect its similar to by_slug

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

				/**
				 * If taxonomy slug on the child is different from the master,
				 * call to term_exists will fail and terms will not be added correctly.
				 * So, we get the taxonomy name on the child by the taxonomy ID.
				 */
				//$_tax_name = wc_attribute_taxonomy_name_by_id( $id );
				$_tax_name = $attr['name'];

				// If taxonomy doesn't exists we create it
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
							$term_id = wp_insert_term( $term['name'], $_tax_name, array(
								//'slug' => $term['slug'],
							));
						} 

						if ( ! array_key_exists( $term['slug'], $this->synced_attributes) ) {
							// fetch the term again to get its slug
							$_trm = get_term_by('name', $term['name'], $_tax_name);

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
					}

					wp_set_post_terms( $child_product_id, $post_terms_to_add, $_tax_name, false );
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
		if ( $_options['child_inherit_changes_fields_control__price'] == 'no' ) {
			unset( $_syncable_meta['_price'] );
			unset( $_syncable_meta['_sale_price'] );
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
			// 	// compare the terms for the matched taxonomy.
			// 	$term_name   = str_replace( 'attribute_', '', $key );
			// 	$child_terms = $this->get_product_terms( $term_name );

			// 	// 1. check if the term slug exists in  
			// }

			if ( strpos($key, "attribute_pa_") === 0 
				 && isset( $this->synced_attributes[$value] )
				 &&  $this->synced_attributes[$value] != $value ) {
				$value = $this->synced_attributes[$value];
			}

			update_post_meta( $child_variation_id, $key, $value );
		}
	}

	/**
	 * Sorts the products attributes into an array, which is sent
	 * by master to the child when sync is run.
	 */
	// private function get_master_attributes_array( $product ) {

	// 	if ( empty( $product['product_attributes'] ) ) {
	// 		return array();
	// 	}

	// 	$attributes_array = array();

	// 	foreach( $product['product_attributes'] as $attr ) {
	// 		$name = 'attribute_' . $attr['name'];
	// 		if ( ! isset( $attributes_array[$name] ) ) {
	// 			$attributes_array[$name] = array();

	// 			if ( !empty( $attr['terms'] ) ) {
	// 				foreach( $attr['terms'] as $term ) {
	// 					$term_slug = $term['slug'];
	// 					$attributes_array[$name][$term_slug] = (array) $term;
	// 				}
	// 			}
	// 		}
	// 	}

	// 	return $attributes_array;
	// }

	/**
	 * Get all terms for a particular attributes and sorts them into an array
	 */
	// private function get_product_terms( $attr ) {
	// 	$attributes = array();
	// 	$child_terms = get_terms( $attr );

	// 	if ( !empty( $child_terms ) ) {
	// 		foreach ($child_terms as $term ) {
	// 			$attributes[] = (array) $term;
	// 		}
	// 	}

	// 	return $attributes;
	// }

	public function get_category_tree( $product_id ) {
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

					$cats_tree[ $cat->term_id ][] = array_merge( (array) get_term( $ancestor ), (array) $thumbnail );
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

			$cats_tree[ $cat->term_id ][] = array_merge( (array) $cat, (array) $thumbnail );
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
			return;
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

	public function fetch_child_orders( $site, $page = 1, $per_page = 10 ) {
		if ( ! is_array( $site ) ) {
			return array();
		}

		$data = array(
			'action'        => 'woomulti_orders',
			'page'          => $page,
			'per_page'      => $per_page,
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

		if ( ! is_wp_error( $result ) ) {
			$resp = json_decode( stripslashes( $result['body'] ), JSON_OBJECT_AS_ARRAY );

			/**
			 * Sometimes they string may not require un-quoting, in which case the above will return null.
			 * If null, then run json_decode without stripslashes.
			 */
			if ( is_null( $resp ) ) {
				$resp = json_decode( $result['body'], JSON_OBJECT_AS_ARRAY );
			}

			return $resp;
		} else {
			woomulti_log_error( 'HTTP ERROR: Can not retrieve orders from child site.' );
			woomulti_log_error( $resp );
		}

		return array();
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

		$types = array( 'upsell', 'crosssell' );
		$_options = get_option( 'woonet_options' );

		foreach ( $types as $type ) {
			if ( ! isset( $product[ $type ] ) ) {
				continue;
			}

			if ( $_options['child_inherit_changes_fields_control__upsell'] == 'no' && $type == 'upsell' ) {
				continue;
			}

			if ( $_options['child_inherit_changes_fields_control__cross_sells'] == 'no' && $type == 'crosssell' ) {
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
		$sites = get_option('woonet_child_sites');
		$site_response = array();

		if ( !empty($sites) ) {
			foreach ( $sites as $site_data) {

				if ( empty($posts_data[ $site_data['uuid'] ]) ) {
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


					if ( !empty($resp['status']) && !empty($resp['message']) ) {
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

		foreach( $this->whitelisted_metadata as $_meta ) {
			if ( substr( $_meta, 0 ) == '%' || substr( $_meta, -1 ) == '%') {
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
	public function get_white_listed_metadata( $product_id ) {
		$_meta = get_post_meta( $product_id );
		$_whitelisted_meta = array();

		if ( empty($_meta) ) {
			woomulti_log_error('Failed to retrieve metadata.');
			return array();
		}

		if ( $this->get_option('sync-all-metadata') == 'yes' ) {
			return $_meta;
		}

		foreach ( $_meta as $key => $value ) {
			if ( $this->is_meta_white_listed( $key) ) {
				$_whitelisted_meta[ $key ] = $value;
			}
		}

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
		if ( empty($this->options ) ) {
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
	 * @param string $status The changed status of the post
	 * @since 3.0.3
	 */
	public function trash_untrash_delete_post( $parent_post_id, $status ) {
		$sites = get_option('woonet_child_sites');
		$site_response = array();

		if ( !empty($sites) ) {
			foreach ( $sites as $site_data) {

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


					if ( !empty($resp['status']) && !empty($resp['message']) ) {
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
}