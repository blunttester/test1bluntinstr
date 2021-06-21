<?php

/**
 * The main WCVendors Pro Product Controller class
 *
 * This is the main controller class for products, all actions are defined in this class.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCVendors_Pro_Product_Controller {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $wcvendors_pro The ID of this plugin.
	 */
	private $wcvendors_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool $debug plugin is in debug mode
	 */
	private $debug;

	/**
	 * Is the plugin base directory
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $base_dir string path for the plugin directory
	 */
	private $base_dir;

	/**
	 * Max number of pages for pagination
	 *
	 * @since    1.2.4
	 * @access   public
	 * @var      int $max_num_pages interger for max number of pages for the query
	 */
	public $max_num_pages;

	/**
	 * Allow HTML markup in forms
	 *
	 * @since    1.3.3
	 * @access   private
	 * @var      string $allow_markup boolean option to allow mark up in forms
	 */
	private $allow_markup;

	/**
	 * Is the ratings table name
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string $table_name name of the ratings table
	 */
	public $table_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wcvendors_pro The name of the plugin.
	 * @param      string $version       The version of this plugin.
	 * @param      bool   $debug         If the plugin is currently in debug mode.
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {
		global $wpdb;
		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;
		$this->debug         = $debug;
		$this->base_dir      = plugin_dir_path( dirname( __FILE__ ) );
		$this->table_name    = $wpdb->prefix . 'wcv_feedback';

	}

	/**
	 *  Process the form submission from the front end.
	 *
	 * @since    1.0.0
	 */
	public function process_submit() {

		if ( ! isset( $_POST['_wcv-save_product'] ) || ! wp_verify_nonce( $_POST['_wcv-save_product'], 'wcv-save_product' ) || ! is_user_logged_in() ) {
			return;
		}

		$can_submit_live     = wc_string_to_bool( get_option( 'wcvendors_capability_products_live', 'no' ) );
		$current_post_status = isset( $_POST['post_status'] ) ? $_POST['post_status'] : '';
		$can_edit_approved   = wc_string_to_bool( get_option( 'wcvendors_capability_products_approved', 'no' ) );
		$trusted_vendor      = ( get_user_meta( get_current_user_id(), '_wcv_trusted_vendor', true ) == 'yes' ) ? true : false;
		$untrusted_vendor    = ( get_user_meta( get_current_user_id(), '_wcv_untrusted_vendor', true ) == 'yes' ) ? true : false;
		$product_redirect    = get_option( 'wcvendors_save_product_redirect', 'empty' );

		if ( $trusted_vendor ) {
			$can_submit_live = true;
		}
		if ( $untrusted_vendor ) {
			$can_submit_live = false;
		}

		$text = array(
			'notice' => '',
			'type'   => 'success',
		);

		if ( isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {

			$post_id = $this->save_product( (int) ( $_POST['post_id'] ) );

			if ( $post_id ) {

				$view = get_permalink( $post_id );

				if ( isset( $_POST['draft_button'] ) ) {

					if ( $can_submit_live ) {
						$text['notice'] = sprintf( apply_filters( 'wcv_product_draft_msg', __( 'Product draft saved.', 'wcvendors-pro' ) ), $view );
					} else {
						$text['notice'] = sprintf( apply_filters( 'wcv_product_draft_saved_msg', __( 'Product draft saved, pending review.', 'wcvendors-pro' ) ), $view );
					}
				} else {
					$target = wc_string_to_bool( get_option( 'wcvendors_dashboard_view_product_new_window', 'no' ) ) ? '_blank' : '_self';
					if ( $can_submit_live ) {
						$text['notice'] = sprintf( apply_filters( 'wcv_product_updated_msg', __( 'Product Updated. <a href="%1$s" target="%2$s">View product.</a>', 'wcvendors-pro' ) ), $view, $target );
					} elseif ( $can_edit_approved && 'pending' !== $current_post_status && 'draft' !== $current_post_status ) {
						$text['notice'] = sprintf( apply_filters( 'wcv_product_updated_msg', __( 'Product Updated. <a href="%1$s" target="%2$s">View product.</a>', 'wcvendors-pro' ) ), $view, $target );
					} else {
						$text['notice'] = sprintf( apply_filters( 'wcv_product_review_msg', __( 'Product submitted for review. <a href="%1$s" target="%2$s">Preview product.</a>', 'wcvendors-pro' ) ), $view, $target );
					}
				}
			} else {
				$text['notice'] = apply_filters( 'wcv_product_edit_problem_msg', __( 'There was a problem editing the product.', 'wcvendors-pro' ) );
				$text['type']   = 'error';
			}
		} else {

			$post_id = $this->save_product();
			$view    = get_permalink( $post_id );

			if ( $post_id ) {
				if ( isset( $_POST['draft_button'] ) ) {
					if ( $can_submit_live ) {
						$text['notice'] = sprintf( apply_filters( 'wcv_product_draft_msg', __( 'Product draft saved.', 'wcvendors-pro' ) ), $view );
					} else {
						$text['notice'] = sprintf( apply_filters( 'wcv_product_draft_saved_msg', __( 'Product draft saved, pending review.', 'wcvendors-pro' ) ), $view );
					}
				} else {
					if ( $can_submit_live ) {
						$text['notice'] = sprintf( apply_filters( 'wcv_product_added_msg', __( 'Product Added. <a href="%s">View product.</a>', 'wcvendors-pro' ) ), $view );
					} else {
						$text['notice'] = sprintf( apply_filters( 'wcv_product_review_msg', __( 'Product submitted for review. <a href="%s">Preview product.</a>', 'wcvendors-pro' ) ), $view );
					}
				}
			} else {
				$text['notice'] = apply_filters( 'wcv_product_add_problem_msg', __( 'There was a problem adding the product.', 'wcvendors-pro' ) );
				$text['type']   = 'error';
			}
		}

		wc_add_notice( $text['notice'], $text['type'] );

		switch ( $product_redirect ) {
			case 'edit':
				// code...
				$url = self::get_product_edit_link( $post_id );
				break;
			case 'list':
				$current_page = ( isset( $_POST['page_number'] ) && 1 < $_POST['page_number'] ) ? '/page/' . $_POST['page_number'] : '';
				$url          = WCVendors_Pro_Dashboard::get_dashboard_page_url( 'product' ) . $current_page;
				break;
			case 'view':
				// code...
				$url = $view;
				break;
			case 'empty':
			default:
				// code...
				$url = self::get_product_edit_link();
				break;
		}
		wp_safe_redirect( apply_filters( 'wcv_save_product_redirect', $url, $product_redirect, $post_id ) );
		exit;

	} // process_submit()

	/**
	 *  Process the delete action
	 *
	 * @since    1.0.0
	 */
	public function process_delete() {

		global $wp;

		if ( isset( $wp->query_vars['object'] ) ) {

			$object = get_query_var( 'object' );
			$action = get_query_var( 'action' );
			$id     = get_query_var( 'object_id' );

			if ( $object == 'product' && $action == 'delete' && is_numeric( $id ) ) {

				if ( $id != null ) {
					if ( WCVendors_Pro_Dashboard::check_object_permission( 'products', $id ) == false ) {
						return false;
					}
				}

				if ( 'yes' !== get_option( 'wcvendors_vendor_product_trash', 'no' ) ) {
					$update = wp_update_post(
						array(
							'ID'          => $id,
							'post_status' => 'trash',
						)
					);
				} else {
					$update = wp_delete_post( $id );
					if ( is_object( $update ) || is_numeric( $update ) ) {
						do_action( 'wcv_delete_post', $id );
					}
				}

				if ( is_object( $update ) || is_numeric( $update ) ) {
					$text = __( 'Product Deleted.', 'wcvendors-pro' );
				} else {
					$text = __( 'There was a problem deleting the product.', 'wcvendors-pro' );
				}

				wc_add_notice( $text );

				wp_safe_redirect( WCVendors_Pro_Dashboard::get_dashboard_page_url( 'product' ) );
				exit;
			}
		}
	} // process_delete()

	/**
	 *  Process the duplicate action
	 *
	 * @since    1.0.0
	 */
	public function process_duplicate() {

		global $wp;

		if ( isset( $wp->query_vars['object'] ) ) {

			$object = get_query_var( 'object' );
			$action = get_query_var( 'action' );
			$id     = get_query_var( 'object_id' );

			if ( $object == 'product' && $action == 'duplicate' && is_numeric( $id ) ) {

				if ( $id != null ) {
					if ( WCVendors_Pro_Dashboard::check_object_permission( 'products', $id ) == false ) {
						return false;
					}
				}

				$new_product_id = $this->duplicate_product( $id );

				wc_add_notice( apply_filters( 'wcv_product_duplicated_msg', __( 'Product Duplicated.', 'wcvendors-pro' ) ) );

				wp_safe_redirect( WCVendors_Pro_Dashboard::get_dashboard_page_url( 'product/edit/' . $new_product_id ) );

				exit;
			}
		}
	} // process_duplicate()

	/**
	 *  Process the duplicate action
	 *
	 * @since    1.3.4
	 * @access   private
	 *
	 * @return int $product_id the new product id
	 */
	private function duplicate_product( $object_id ) {

		// create the WC Admin duplicate product object
		$wcdpa       = new WC_Admin_Duplicate_Product();
		$product     = wc_get_product( $object_id );
		$new_product = $wcdpa->product_duplicate( $product );

		return $new_product->get_id();

	} // duplicate_product()

	/**
	 *  Save a new product
	 *
	 * @since       1.0.0
	 * @version     1.5.9
	 *
	 * @param     int $post_id
	 *
	 * @return   mixed    $post_id or WP_Error
	 */
	public function save_product( $post_id = 0 ) {

		// WC_Vendors::log( $_POST );
		$this->allow_markup = wc_string_to_bool( get_option( 'wcvendors_allow_form_markup', 'no' ) );

		// Work on adding filters and option checks to publish to draft instead of straight to live
		$can_submit_live   = wc_string_to_bool( get_option( 'wcvendors_capability_products_live', 'no' ) );
		$can_edit_live     = wc_string_to_bool( get_option( 'wcvendors_capability_products_edit', 'no' ) );
		$can_edit_approved = wc_string_to_bool( get_option( 'wcvendors_capability_products_approved', 'no' ) );

		$post_status         = '';
		$current_post_status = isset( $_POST['post_status'] ) ? $_POST['post_status'] : '';

		if ( isset( $_POST['draft_button'] ) ) {
			$post_status = 'draft';
		} else {

			$post_status = $can_submit_live ? 'publish' : 'pending';

			if ( 0 !== $post_id ) {

				$post_status = ( $can_edit_live && $can_submit_live || $can_edit_approved ) ? 'publish' : 'pending';

				if ( 'draft' == $current_post_status && ! $can_submit_live ) {
					$post_status = 'pending';
				}

				if ( 'pending' == $current_post_status && ! $can_submit_live ) {
					$post_status = 'pending';
				}
			}
		}

		// Bypass globals for live product submissions
		$trusted_vendor   = ( get_user_meta( get_current_user_id(), '_wcv_trusted_vendor', true ) == 'yes' ) ? true : false;
		$untrusted_vendor = ( get_user_meta( get_current_user_id(), '_wcv_untrusted_vendor', true ) == 'yes' ) ? true : false;

		if ( $trusted_vendor && ! isset( $_POST['draft_button'] ) ) {
			$post_status = 'publish';
		}
		if ( $untrusted_vendor ) {
			$post_status = 'pending';
		}

		$product_type = empty( $_POST['product-type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['product-type'] ) );

		$_product = array(
			'post_title'   => $this->allow_markup ? wc_clean( $_POST['post_title'] ) : wp_strip_all_tags( wc_clean( $_POST['post_title'] ) ),
			'post_status'  => $post_status,
			'post_type'    => 'product',
			'post_excerpt' => ( isset( $_POST['post_excerpt'] ) ? ( wc_string_to_bool( get_option( 'wcvendors_allow_product_html', 'no' ) ) ? wp_kses_post( $_POST['post_excerpt'] ) : wp_strip_all_tags( $_POST['post_excerpt'] ) ) : '' ),
			'post_content' => ( isset( $_POST['post_content'] ) ? ( wc_string_to_bool( get_option( 'wcvendors_allow_product_html', 'no' ) ) ? wp_kses_post( $_POST['post_content'] ) : wp_strip_all_tags( $_POST['post_content'] ) ) : '' ),
			'post_author'  => get_current_user_id(),
		);

		if ( 0 !== $post_id ) {
			$_product['ID'] = $post_id;
			if ( $this->allow_markup ) {
				kses_remove_filters();
			}
			$product_id = wp_update_post( $_product, true );
			if ( $this->allow_markup ) {
				kses_init_filters();
			}
		} else {
			// Attempts to create the new product
			if ( $this->allow_markup ) {
				kses_remove_filters();
			}
			$product_id = wp_insert_post( $_product, true );
			if ( $this->allow_markup ) {
				kses_init_filters();
			}
		}

		// Checks for an error in the product creation
		if ( is_wp_error( $product_id ) ) {
			return null;
		}

		// Featured Image
		if ( isset( $_POST['_featured_image_id'] ) && '' !== $_POST['_featured_image_id'] ) {
			set_post_thumbnail( $product_id, (int) $_POST['_featured_image_id'] );
		} else {
			delete_post_thumbnail( $product_id );
		}

		// // Gallery Images
		if ( isset( $_POST['product_image_gallery'] ) && '' !== $_POST['product_image_gallery'] ) {
			update_post_meta( $product_id, '_product_image_gallery', $_POST['product_image_gallery'] );
		} else {
			update_post_meta( $product_id, '_product_image_gallery', '' );
		}

		// Categories
		if ( isset( $_POST['product_cat'] ) && is_array( $_POST['product_cat'] ) ) {
			$categories = array_map( 'intval', $_POST['product_cat'] );
			$categories = array_unique( $categories );

			// Need to store product categories into single user meta to track all. Sync this also.
			wp_set_post_terms( $product_id, $categories, 'product_cat' );
		} else {
			// No categories selected so reset them
			wp_set_post_terms( $product_id, null, 'product_cat' );
		}

		// Tags
		if ( isset( $_POST['product_tags'] ) ) {

			$post_tags = array();

			if ( is_array( $_POST['product_tags'] ) ) {
				$post_tags = $_POST['product_tags'];
			}

			$tags = array();

			foreach ( $post_tags as $post_tag ) {
				$existing_tag = get_term( $post_tag, 'product_tag' );

				if ( $existing_tag != null ) {
					$tags[] = $existing_tag->slug;
				} else {
					$tags[] = $post_tag;
				}
			}

			$tags = array_unique( $tags );
			$tags = implode( ',', $tags );

			wp_set_post_terms( $product_id, $tags, 'product_tag' );
		} else {
			// No tags selected so reset them
			wp_set_post_terms( $product_id, null, 'product_tag' );
		}

		// Custom taxonomies
		$wcv_custom_hidden_tax = array_intersect_key( $_POST, array_flip( preg_grep( '/^_wcv_custom_taxonomy_/', array_keys( $_POST ) ) ) );
		if ( ! empty( $wcv_custom_hidden_tax ) ) {

			foreach ( $wcv_custom_hidden_tax as $taxonomy => $data ) {

				$taxonomy = str_replace( '_wcv_custom_taxonomy_', '', $taxonomy );
				$tax_data = array_unique( $data );
				$tax_data = array_map( 'intval', $tax_data );

				wp_set_object_terms( $product_id, $tax_data, $taxonomy );

			}
		}

		$wcv_custom_tax = array_intersect_key( $_POST, array_flip( preg_grep( '/^wcv_custom_taxonomy_/', array_keys( $_POST ) ) ) );
		if ( ! empty( $wcv_custom_tax ) ) {

			foreach ( $wcv_custom_tax as $taxonomy => $data ) {

				$taxonomy = str_replace( 'wcv_custom_taxonomy_', '', $taxonomy );
				$tax_data = array_unique( $data );
				$tax_data = array_map( 'intval', $tax_data );

				wp_set_object_terms( $product_id, $tax_data, $taxonomy );

			}
		}

		// Clean up taxonomies that have no values
		$track_tax = array_intersect_key( $_POST, array_flip( preg_grep( '/^track_/', array_keys( $_POST ) ) ) );
		if ( ! empty( $track_tax ) ) {

			foreach ( $track_tax as $taxonomy => $value ) {

				$taxonomy = str_replace( 'track_', '', $taxonomy );

				// Hidden
				if ( ! array_key_exists( $taxonomy, $wcv_custom_hidden_tax ) ) {
					$taxonomy = str_replace( '_wcv_custom_taxonomy_', '', $taxonomy );
					wp_set_object_terms( $product_id, null, $taxonomy );
				}

				// Not hidden
				if ( ! array_key_exists( $taxonomy, $wcv_custom_tax ) ) {
					$taxonomy = str_replace( 'wcv_custom_taxonomy_', '', $taxonomy );
					wp_set_object_terms( $product_id, null, $taxonomy );
				}
			}
		}

		// Base product saved now save all meta fields
		$this->save_meta( $product_id );

		switch ( $product_type ) {
			case apply_filters( 'wcv_save_product_variation_type', '' ):
				$this->save_variations( $product_id );
				break;

			default:
				$this->save_variations( $product_id );
				break;
		}

		$this->check_coupons();

		do_action( 'wcv_save_product', $product_id );

		wc_delete_product_transients( $product_id );

		return $product_id;
	} // save_product()

	/**
	 *  Save product meta
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 *
	 * @param     int $post_id
	 */
	public function save_meta( $post_id ) {

		global $wpdb;

		if ( isset( $_POST['product-type'] ) ) {

			$classname = WC_Product_Factory::get_classname_from_product_type( $_POST['product-type'] );

			if ( ! class_exists( $classname ) ) {
				$classname = 'WC_Product_Simple';
			}

			$product = new $classname( $post_id );

			// Featured Product.
			if ( isset( $_POST['featured'] ) ) {
				$product->set_featured( $_POST['featured'] );
			}

			// Catalog visibility
			if ( isset( $_POST['_private_listing'] ) ) {
				$product->set_catalog_visibility( 'hidden' );
				update_post_meta( $post_id, '_private_listing', $_POST['_private_listing'] );
			} else {
				$product->set_catalog_visibility( 'visible' );
				delete_post_meta( $post_id, '_private_listing' );
			}

			if ( isset( $_POST['_stock_status'] ) ) {
				$stock_status = true === $_POST['_stock_status'] ? 'instock' : 'outofstock';
			} else {
				$stock_status = $product->get_stock_status();
			}

			// Stock data.
			if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {

				// Manage stock.
				if ( isset( $_POST['_manage_stock'] ) ) {
					$product->set_manage_stock( $_POST['_manage_stock'] );
				}

				// Backorders.
				if ( isset( $_POST['_backorders'] ) ) {
					$product->set_backorders( $_POST['_backorders'] );
				}

				if ( $product->is_type( 'grouped' ) ) {
					$product->set_manage_stock( 'no' );
					$product->set_backorders( 'no' );
					$product->set_stock_quantity( '' );
					$product->set_stock_status( $stock_status );
				} elseif ( $product->is_type( 'external' ) ) {
					$product->set_manage_stock( 'no' );
					$product->set_backorders( 'no' );
					$product->set_stock_quantity( '' );
					$product->set_stock_status( 'instock' );
				} elseif ( $product->get_manage_stock() ) {

					if ( ! $product->is_type( 'variable' ) ) {
						$product->set_stock_status( $stock_status );
					}

					// Stock quantity.
					if ( isset( $_POST['_stock'] ) ) {
						$product->set_stock_quantity( (int) wc_stock_amount( $_POST['_stock'] ) );
					}
				} else {
					// Don't manage stock.
					$product->set_manage_stock( 'no' );
					$product->set_stock_quantity( '' );
					$product->set_stock_status( $stock_status );
				}
			} elseif ( ! $product->is_type( 'variable' ) ) {

				if ( ! $product->is_type( 'external' ) ) {
					$product->set_stock_status( $stock_status );
				}
			} // stock data
		}

		$product->save();

		$this->allow_markup = wc_string_to_bool( get_option( 'wcvendors_allow_form_markup', 'no' ) );

		// Add any default post meta
		add_post_meta( $post_id, 'total_sales', '0', true );

		// Set catalog visibility
		if ( isset( $_POST['_private_listing'] ) ) {
			update_post_meta( $post_id, '_visibility', 'hidden' );
			update_post_meta( $post_id, '_private_listing', $_POST['_private_listing'] );
		} else {
			update_post_meta( $post_id, '_visibility', 'visible' );
			delete_post_meta( $post_id, '_private_listing' );
		}

		// Get types
		$product_type    = empty( $_POST['product-type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['product-type'] ) );
		$is_downloadable = isset( $_POST['_downloadable'] ) ? 'yes' : 'no';
		$is_virtual      = isset( $_POST['_virtual'] ) ? 'yes' : 'no';

		// Product type + Downloadable/Virtual
		wp_set_object_terms( $post_id, $product_type, 'product_type' );
		update_post_meta( $post_id, '_downloadable', $is_downloadable );
		update_post_meta( $post_id, '_virtual', $is_virtual );

		// Update post meta
		if ( isset( $_POST['_regular_price'] ) ) {
			update_post_meta( $post_id, '_regular_price', ( $_POST['_regular_price'] === '' ) ? '' : wc_format_decimal( $_POST['_regular_price'] ) );
		}

		if ( isset( $_POST['_sale_price'] ) ) {

			$sale_price = ( $_POST['_sale_price'] === '' ) ? '' : wc_format_decimal( $_POST['_sale_price'] );
			update_post_meta( $post_id, '_sale_price', ( $_POST['_sale_price'] === '' ? '' : wc_format_decimal( $_POST['_sale_price'] ) ) );
			update_post_meta( $post_id, '_price', $sale_price );
		}

		if ( isset( $_POST['_tax_status'] ) ) {
			update_post_meta( $post_id, '_tax_status', wc_clean( $_POST['_tax_status'] ) );
		}

		if ( isset( $_POST['_tax_class'] ) ) {
			update_post_meta( $post_id, '_tax_class', wc_clean( $_POST['_tax_class'] ) );
		}

		// Featured
		// if ( update_post_meta( $post_id, '_featured', isset( $_POST[ '_featured' ] ) ? 'yes' : 'no' ) ) {
		// delete_transient( 'wc_featured_products' );
		// }
		// Dimensions
		if ( 'no' == $is_virtual ) {

			$shipping_details = array();

			if ( isset( $_POST['_shipping_fee_national'] ) && '' != $_POST['_shipping_fee_national'] ) {
				$shipping_details['national'] = wc_format_decimal( $_POST['_shipping_fee_national'] );
			} else {
				$shipping_details['national'] = '';
			}

			if ( isset( $_POST['_national_minimum_shipping_fee'] ) && '' != $_POST['_national_minimum_shipping_fee'] ) {
				$shipping_details['national_minimum_shipping_fee'] = wc_format_decimal( $_POST['_national_minimum_shipping_fee'] );
			} else {
				$shipping_details['national_minimum_shipping_fee'] = '';
			}

			if ( isset( $_POST['_national_maximum_shipping_fee'] ) && '' != $_POST['_national_maximum_shipping_fee'] ) {
				$shipping_details['national_maximum_shipping_fee'] = wc_format_decimal( $_POST['_national_maximum_shipping_fee'] );
			} else {
				$shipping_details['national_maximum_shipping_fee'] = '';
			}

			if ( isset( $_POST['_national_free_shipping_product'] ) && '' != $_POST['_national_free_shipping_product'] ) {
				$shipping_details['national_free_shipping_product'] = wc_format_decimal( $_POST['_national_free_shipping_product'] );
			} else {
				$shipping_details['national_free_shipping_product'] = '';
			}

			if ( isset( $_POST['_shipping_fee_international'] ) && '' != $_POST['_shipping_fee_international'] ) {
				$shipping_details['international'] = wc_format_decimal( $_POST['_shipping_fee_international'] );
			} else {
				$shipping_details['international'] = '';
			}

			if ( isset( $_POST['_international_minimum_shipping_fee'] ) && '' != $_POST['_international_minimum_shipping_fee'] ) {
				$shipping_details['international_minimum_shipping_fee'] = wc_format_decimal( $_POST['_international_minimum_shipping_fee'] );
			} else {
				$shipping_details['international_minimum_shipping_fee'] = '';
			}

			if ( isset( $_POST['_international_maximum_shipping_fee'] ) && '' != $_POST['_international_maximum_shipping_fee'] ) {
				$shipping_details['international_maximum_shipping_fee'] = wc_format_decimal( $_POST['_international_maximum_shipping_fee'] );
			} else {
				$shipping_details['international_maximum_shipping_fee'] = '';
			}

			if ( isset( $_POST['_international_free_shipping_product'] ) && '' != $_POST['_international_free_shipping_product'] ) {
				$shipping_details['international_free_shipping_product'] = wc_format_decimal( $_POST['_international_free_shipping_product'] );
			} else {
				$shipping_details['international_free_shipping_product'] = '';
			}

			if ( isset( $_POST['_handling_fee'] ) && '' != $_POST['_handling_fee'] ) {
				$shipping_details['handling_fee'] = sanitize_text_field( $_POST['_handling_fee'] );
			} else {
				$shipping_details['handling_fee'] = '';
			}

			if ( isset( $_POST['_max_charge_product'] ) && '' != $_POST['_max_charge_product'] ) {
				$shipping_details['max_charge_product'] = wc_format_decimal( $_POST['_max_charge_product'] );
			} else {
				$shipping_details['max_charge_product'] = '';
			}

			if ( isset( $_POST['_free_shipping_product'] ) && '' != $_POST['_free_shipping_product'] ) {
				$shipping_details['free_shipping_product'] = wc_format_decimal( $_POST['_free_shipping_product'] );
			} else {
				$shipping_details['free_shipping_product'] = '';
			}

			if ( isset( $_POST['_shipping_fee_national_qty'] ) && '' != $_POST['_shipping_fee_national_qty'] ) {
				$shipping_details['national_qty_override'] = 'yes';
			} else {
				$shipping_details['national_qty_override'] = '';
			}

			if ( isset( $_POST['_shipping_fee_national_disable'] ) && '' != $_POST['_shipping_fee_national_disable'] ) {
				$shipping_details['national_disable'] = 'yes';
			} else {
				$shipping_details['national_disable'] = '';
			}

			if ( isset( $_POST['_shipping_fee_national_free'] ) && '' != $_POST['_shipping_fee_national_free'] ) {
				$shipping_details['national_free'] = 'yes';
			} else {
				$shipping_details['national_free'] = '';
			}

			if ( isset( $_POST['_shipping_fee_international_qty'] ) && '' != $_POST['_shipping_fee_international_qty'] ) {
				$shipping_details['international_qty_override'] = 'yes';
			} else {
				$shipping_details['international_qty_override'] = '';
			}

			if ( isset( $_POST['_shipping_fee_international_disable'] ) && '' != $_POST['_shipping_fee_international_disable'] ) {
				$shipping_details['international_disable'] = 'yes';
			} else {
				$shipping_details['international_disable'] = '';
			}

			if ( isset( $_POST['_shipping_fee_international_free'] ) && '' != $_POST['_shipping_fee_international_free'] ) {
				$shipping_details['international_free'] = 'yes';
			} else {
				$shipping_details['international_free'] = '';
			}

			if ( ! empty( $shipping_details ) ) {
				update_post_meta( $post_id, '_wcv_shipping_details', $shipping_details );
			} else {
				delete_post_meta( $post_id, '_wcv_shipping_details' );
			}

			if ( isset( $_POST['_weight'] ) ) {
				update_post_meta( $post_id, '_weight', ( '' === $_POST['_weight'] ) ? '' : wc_format_decimal( $_POST['_weight'] ) );
			}

			if ( isset( $_POST['_length'] ) ) {
				update_post_meta( $post_id, '_length', ( '' === $_POST['_length'] ) ? '' : wc_format_decimal( $_POST['_length'] ) );
			}

			if ( isset( $_POST['_width'] ) ) {
				update_post_meta( $post_id, '_width', ( '' === $_POST['_width'] ) ? '' : wc_format_decimal( $_POST['_width'] ) );
			}

			if ( isset( $_POST['_height'] ) ) {
				update_post_meta( $post_id, '_height', ( '' === $_POST['_height'] ) ? '' : wc_format_decimal( $_POST['_height'] ) );
			}

			// shipping rates
			$shipping_rates = array();

			if ( isset( $_POST['_wcv_shipping_fees'] ) ) {

				$shipping_countries = isset( $_POST['_wcv_shipping_countries'] ) ? $_POST['_wcv_shipping_countries'] : array();
				$shipping_states    = isset( $_POST['_wcv_shipping_states'] ) ? $_POST['_wcv_shipping_states'] : array();
				$shipping_postcodes = isset( $_POST['_wcv_shipping_postcodes'] ) ? $_POST['_wcv_shipping_postcodes'] : array();
				// $shipping_qty_overrides = isset( $_POST[ '_wcv_shipping_overrides'] ) 	? $_POST[ '_wcv_shipping_overrides'] : array();
				$shipping_fees      = isset( $_POST['_wcv_shipping_fees'] ) ? $_POST['_wcv_shipping_fees'] : array();
				$shipping_fee_count = sizeof( $shipping_fees );

				for ( $i = 0; $i < $shipping_fee_count; $i ++ ) {

					if ( $shipping_fees[ $i ] != '' ) {

						$country      = wc_clean( $shipping_countries[ $i ] );
						$state        = wc_clean( $shipping_states[ $i ] );
						$postcode     = wc_clean( $shipping_postcodes[ $i ] );
						$qty_override = isset( $_POST[ '_wcv_shipping_overrides_' . $i ] ) ? 'yes' : 'no';

						$fee                  = wc_format_decimal( $shipping_fees[ $i ] );
						$shipping_rates[ $i ] = array(
							'country'      => $country,
							'state'        => $state,
							'postcode'     => $postcode,
							'fee'          => $fee,
							'qty_override' => $qty_override,
						);
					}
				}
				update_post_meta( $post_id, '_wcv_shipping_rates', $shipping_rates );
			} else {

				delete_post_meta( $post_id, '_wcv_shipping_rates' );
			}

			// Invalidate the shipping cache
			WC_Cache_Helper::get_transient_version( 'shipping', true );

		} else {
			update_post_meta( $post_id, '_weight', '' );
			update_post_meta( $post_id, '_length', '' );
			update_post_meta( $post_id, '_width', '' );
			update_post_meta( $post_id, '_height', '' );
		}

		// Save shipping class
		if ( isset( $_POST['product_shipping_class'] ) ) {
			$product_shipping_class = $_POST['product_shipping_class'] > 0 && $product_type != 'external' ? absint( $_POST['product_shipping_class'] ) : '';
			wp_set_object_terms( $post_id, $product_shipping_class, 'product_shipping_class' );
		}

		// Unique SKU
		if ( isset( $_POST['_sku'] ) ) {

			$sku     = get_post_meta( $post_id, '_sku', true );
			$new_sku = wc_clean( stripslashes( $_POST['_sku'] ) );

			if ( '' == $new_sku ) {
				update_post_meta( $post_id, '_sku', '' );
			} elseif ( $new_sku !== $sku ) {

				if ( ! empty( $new_sku ) ) {

					$unique_sku = wc_product_has_unique_sku( $post_id, $new_sku );

					if ( ! $unique_sku ) {
						// TODO: make this send error to the front end
						WC_Admin_Meta_Boxes::add_error( __( 'Product SKU must be unique.', 'wcvendors-pro' ) );
					} else {
						update_post_meta( $post_id, '_sku', $new_sku );
					}
				} else {
					update_post_meta( $post_id, '_sku', '' );
				}
			}
		}

		// Save Attributes
		$attributes = array();

		if ( isset( $_POST['attribute_names'] ) && isset( $_POST['attribute_values'] ) ) {

			$attribute_names  = $_POST['attribute_names'];
			$attribute_values = $_POST['attribute_values'];

			$attribute_names_count = sizeof( $attribute_names );

			if ( isset( $_POST['attribute_visibility'] ) ) {
				$attribute_visibility = $_POST['attribute_visibility'];
			}

			if ( isset( $_POST['attribute_variation'] ) ) {
				$attribute_variation = $_POST['attribute_variation'];
			}

			$attribute_is_taxonomy   = $_POST['attribute_is_taxonomy'];
			$attribute_position      = $_POST['attribute_position'];
			$attribute_names_max_key = max( array_keys( $attribute_names ) );

			$pos = 0;

			// for ( $i = 0; $i < $attribute_names_count; $i++ ) {
			for ( $i = 0; $i <= $attribute_names_max_key; $i ++ ) {

				if ( ! $attribute_names[ $i ] ) {
					continue;
				}

				$is_visible   = isset( $attribute_visibility[ $i ] ) ? 1 : 0;
				$is_variation = isset( $attribute_variation[ $i ] ) ? 1 : 0;
				$is_taxonomy  = $attribute_is_taxonomy[ $i ] ? 1 : 0;

				if ( $is_taxonomy ) {

					$values_are_slugs = false;

					if ( isset( $attribute_values[ $i ] ) ) {

						// Select based attributes - Format values (posted values are slugs)
						if ( is_array( $attribute_values[ $i ] ) ) {
							$values           = array_map( 'sanitize_title', $attribute_values[ $i ] );
							$values_are_slugs = true;

							// Text based attributes - Posted values are term names - don't change to slugs
						} else {
							$values = array_map( 'stripslashes', array_map( 'strip_tags', explode( WC_DELIMITER, $attribute_values[ $i ] ) ) );
						}

						// Remove empty items in the array
						$values = array_filter( $values, 'strlen' );

					} else {
						$values = array();
					}

					// Update post terms
					if ( taxonomy_exists( $attribute_names[ $i ] ) ) {

						foreach ( $values as $key => $value ) {
							$term = get_term_by( $values_are_slugs ? 'slug' : 'name', trim( $value ), $attribute_names[ $i ] );

							if ( $term ) {
								$values[ $key ] = intval( $term->term_id );
							} else {
								$term = wp_insert_term( trim( $value ), $attribute_names[ $i ] );
								if ( isset( $term->term_id ) ) {
									$values[ $key ] = intval( $term->term_id );
								}
							}
						}

						wp_set_object_terms( $post_id, $values, $attribute_names[ $i ] );
					}

					if ( ! empty( $values ) ) {
						// Add attribute to array, but don't set values
						$attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
							'name'         => wc_clean( $attribute_names[ $i ] ),
							'value'        => '',
							'position'     => $attribute_position[ $i ],
							'is_visible'   => $is_visible,
							'is_variation' => $is_variation,
							'is_taxonomy'  => $is_taxonomy,
						);
					}

					$pos ++;

				} elseif ( isset( $attribute_values[ $i ] ) ) {

					// Text based, possibly separated by pipes (WC_DELIMITER). Preserve line breaks in non-variation attributes.
					$values = $is_variation ? wc_clean( $attribute_values[ $i ] ) : implode( "\n", array_map( 'wc_clean', explode( "\n", $attribute_values[ $i ] ) ) );
					$values = implode( ' ' . WC_DELIMITER . ' ', wc_get_text_attributes( $values ) );

					// Custom attribute - Add attribute to array and set the values
					$attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
						'name'         => wc_clean( $attribute_names[ $i ] ),
						'value'        => $values,
						'position'     => $attribute_position[ $i ],
						'is_visible'   => $is_visible,
						'is_variation' => $is_variation,
						'is_taxonomy'  => $is_taxonomy,
					);
				}
			} // end forloop
		}

		if ( ! function_exists( 'attributes_cmp' ) ) {
			function attributes_cmp( $a, $b ) {
				if ( $a['position'] == $b['position'] ) {
					return 0;
				}

				return ( $a['position'] < $b['position'] ) ? - 1 : 1;
			}
		}
		uasort( $attributes, 'attributes_cmp' );

		update_post_meta( $post_id, '_product_attributes', $attributes );

		// Sales and prices
		if ( in_array( $product_type, apply_filters( 'wcv_product_meta_types', array( 'variable', 'grouped' ) ) ) ) {

			// Variable and grouped products have no prices
			update_post_meta( $post_id, '_regular_price', '' );
			update_post_meta( $post_id, '_sale_price', '' );
			update_post_meta( $post_id, '_sale_price_dates_from', '' );
			update_post_meta( $post_id, '_sale_price_dates_to', '' );
			update_post_meta( $post_id, '_price', '' );

		} else {

			$date_from = isset( $_POST['_sale_price_dates_from'] ) ? wc_clean( $_POST['_sale_price_dates_from'] ) : '';
			$date_to   = isset( $_POST['_sale_price_dates_to'] ) ? wc_clean( $_POST['_sale_price_dates_to'] ) : '';

			if ( wc_clean( $date_from ) == wc_clean( $date_to ) ) {
				$date_to   = '';
				$date_from = '';
			}

			// Dates
			if ( $date_from ) {
				update_post_meta( $post_id, '_sale_price_dates_from', strtotime( $date_from ) );
			} else {
				update_post_meta( $post_id, '_sale_price_dates_from', '' );
			}

			if ( $date_to ) {
				update_post_meta( $post_id, '_sale_price_dates_to', strtotime( $date_to ) );
			} else {
				update_post_meta( $post_id, '_sale_price_dates_to', '' );
			}

			if ( $date_to && ! $date_from ) {
				update_post_meta( $post_id, '_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );
			}

			// Update price if on sale
			if ( isset( $_POST['_sale_price'] ) && '' !== $_POST['_sale_price'] && '' == $date_to && '' == $date_from ) {
				update_post_meta( $post_id, '_price', wc_format_localized_price( $_POST['_sale_price'] ) );
			} else {
				update_post_meta( $post_id, '_price', ( $_POST['_regular_price'] === '' ) ? '' : wc_format_localized_price( $_POST['_regular_price'] ) );
			}

			if ( isset( $_POST['_sale_price'] ) && '' !== $_POST['_sale_price'] && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $post_id, '_price', wc_format_localized_price( $_POST['_sale_price'] ) );
			}

			if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $post_id, '_price', ( $_POST['_regular_price'] === '' ) ? '' : wc_format_localized_price( $_POST['_regular_price'] ) );
				update_post_meta( $post_id, '_sale_price_dates_from', '' );
				update_post_meta( $post_id, '_sale_price_dates_to', '' );
			}
		}

		// Product parent ID for groups
		if ( isset( $_POST['parent_id'] ) ) {
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_parent' => absint( $_POST['parent_id'] ),
				)
			);

		}

		if ( $product->is_type( 'grouped' ) && isset( $_POST['grouped_products'] ) && $_POST['grouped_products'] ) {
			$product->set_children( $_POST['grouped_products'] );
		}

		// Sold Individually
		if ( ! empty( $_POST['_sold_individually'] ) ) {
			update_post_meta( $post_id, '_sold_individually', 'yes' );
		} else {
			update_post_meta( $post_id, '_sold_individually', '' );
		}

		// Stock Data
		if ( isset( $_POST['_stock_status'] ) ) {

			if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {

				$manage_stock = 'no';
				$backorders   = 'no';
				$stock_status = wc_clean( $_POST['_stock_status'] );

				if ( 'external' === $product_type ) {

					$stock_status = 'instock';

				} elseif ( 'variable' === $product_type ) {

					// Stock status is always determined by children so sync later
					$stock_status = '';

					if ( ! empty( $_POST['_manage_stock'] ) ) {
						$manage_stock = 'yes';
						$backorders   = wc_clean( $_POST['_backorders'] );
					}
				} elseif ( 'grouped' !== $product_type && ! empty( $_POST['_manage_stock'] ) ) {
					$manage_stock = 'yes';
					$backorders   = wc_clean( $_POST['_backorders'] );
				}

				update_post_meta( $post_id, '_manage_stock', $manage_stock );
				update_post_meta( $post_id, '_backorders', $backorders );

				if ( $stock_status ) {
					wc_update_product_stock_status( $post_id, $stock_status );
				}

				if ( ! empty( $_POST['_manage_stock'] ) ) {
					wc_update_product_stock( $post_id, (int) wc_stock_amount( $_POST['_stock'] ) );
				} else {
					update_post_meta( $post_id, '_stock', '' );
				}
			} else {
				wc_update_product_stock_status( $post_id, wc_clean( $_POST['_stock_status'] ) );
			}
		} else {
			// Set default to be instock if not managed at all.
			wc_update_product_stock_status( $post_id, wc_clean( 'instock' ) );
		}

		// Downloadable options
		if ( 'yes' == $is_downloadable ) {

			$_download_limit = absint( $_POST['_download_limit'] );
			if ( ! $_download_limit ) {
				$_download_limit = - 1; // 0 or blank = unlimited
			}

			$_download_expiry = absint( $_POST['_download_expiry'] );
			if ( ! $_download_expiry ) {
				$_download_expiry = - 1; // 0 or blank = unlimited
			}

			$product->set_props(
				array(
					'download_limit'  => $_download_limit,
					'download_expiry' => $_download_expiry,
					'downloads'       => self::prepare_downloads(
						isset( $_POST['_wc_file_names'] ) ? $_POST['_wc_file_names'] : array(),
						isset( $_POST['_wc_file_urls'] ) ? $_POST['_wc_file_urls'] : array(),
						isset( $_POST['_wc_file_hashes'] ) ? $_POST['_wc_file_hashes'] : array()
					),
				)
			);

			if ( isset( $_POST['_download_type'] ) ) {
				update_post_meta( $post_id, '_download_type', wc_clean( $_POST['_download_type'] ) );
			}
		}

		// Product url
		if ( 'external' == $product_type ) {

			if ( isset( $_POST['_product_url'] ) ) {
				update_post_meta( $post_id, '_product_url', esc_url_raw( $_POST['_product_url'] ) );
			}

			if ( isset( $_POST['_button_text'] ) ) {
				update_post_meta( $post_id, '_button_text', wc_clean( $_POST['_button_text'] ) );
			}
		}

		// Upsells
		if ( isset( $_POST['upsell_ids'] ) && ! empty( $_POST['upsell_ids'] ) ) {
			update_post_meta( $post_id, '_upsell_ids', $_POST['upsell_ids'] );
		} else {
			update_post_meta( $post_id, '_upsell_ids', '' );
		}

		// Cross sells
		if ( isset( $_POST['crosssell_ids'] ) && ! empty( $_POST['crosssell_ids'] ) ) {
			update_post_meta( $post_id, '_crosssell_ids', $_POST['crosssell_ids'] );
		} else {
			update_post_meta( $post_id, '_crosssell_ids', '' );
		}

		// Product template page
		if ( ! empty( $_POST['_wcv_product_form_template'] ) ) {
			update_post_meta( $post_id, '_wcv_product_form_template', $_POST['_wcv_product_form_template'] );
		} else {
			update_post_meta( $post_id, '_wcv_product_form_template', '' );
		}

		// To be used to allow custom hidden meta keys
		$wcv_custom_hidden_metas = array_intersect_key( $_POST, array_flip( preg_grep( '/^_wcv_custom_product_/', array_keys( $_POST ) ) ) );

		if ( ! empty( $wcv_custom_hidden_metas ) ) {

			foreach ( $wcv_custom_hidden_metas as $key => $value ) {
				$value = $this->allow_markup ? $value : wp_strip_all_tags( $value );
				update_post_meta( $post_id, $key, $value );
			}
		}

		// To be used to allow custom meta keys
		$wcv_custom_metas = array_intersect_key( $_POST, array_flip( preg_grep( '/^wcv_custom_product_/', array_keys( $_POST ) ) ) );

		if ( ! empty( $wcv_custom_metas ) ) {

			foreach ( $wcv_custom_metas as $key => $value ) {
				$value = $this->allow_markup ? $value : wp_strip_all_tags( $value );
				update_post_meta( $post_id, $key, $value );
			}
		}

		// Save variations
		if ( 'variable' == $product_type ) {
			// Update parent if variable so price sorting works and stays in sync with the cheapest child
			WC_Product_Variable::sync( $post_id );
			WC_Product_Variable::sync_stock_status( $post_id );
		}

		$hide_seo = wc_string_to_bool( get_option( 'wcvendors_hide_product_seo', 'no' ) );

		if ( ! $hide_seo ) {

			$seo_title = ! empty( $_POST['wcv_product_seo_title'] ) ? wcv_strip_html( $_POST['wcv_product_seo_title'] ) : '';
			update_post_meta( $post_id, 'wcv_product_seo_title', $seo_title );

			$seo_description = ! empty( $_POST['wcv_product_seo_description'] ) ? wcv_strip_html( $_POST['wcv_product_seo_description'] ) : '';
			update_post_meta( $post_id, 'wcv_product_seo_description', $seo_description );

			$seo_keywords = ! empty( $_POST['wcv_product_seo_keywords'] ) ? wcv_strip_html( $_POST['wcv_product_seo_keywords'] ) : '';
			update_post_meta( $post_id, 'wcv_product_seo_keywords', $seo_keywords );

			$seo_opengraph = isset( $_POST['wcv_product_seo_opengraph'] ) ? esc_attr( $_POST['wcv_product_seo_opengraph'] ) : 'no';
			update_post_meta( $post_id, 'wcv_product_seo_opengraph', $seo_opengraph );

			$seo_twitter_card = isset( $_POST['wcv_product_seo_twitter_card'] ) ? esc_attr( $_POST['wcv_product_seo_twitter_card'] ) : 'no';
			update_post_meta( $post_id, 'wcv_product_seo_twitter_card', $seo_twitter_card );

		}

		$product->set_props(
			array(
				'low_stock_amount' => isset( $_POST['_low_stock_amount'] ) && '' !== $_POST['_low_stock_amount'] ? wc_stock_amount( wp_unslash( $_POST['_low_stock_amount'] ) ) : '',
			)
		);

		$product->save();

		do_action( 'wcv_save_product_meta', $post_id, $product );

	} // save_meta()

	/**
	 *  Save product variations
	 *
	 * @since    1.3.0
	 *
	 * @param     int   $post_id     the parent post id
	 * @param     array $parent_data parent data
	 */
	public function save_variations( $post_id ) {

		global $wpdb;

		$this->allow_markup = wc_string_to_bool( get_option( 'wcvendors_allow_form_markup', 'no' ) );
		$attributes         = (array) maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );
		$deleted_variations = isset( $_POST['wcv_deleted_variations'] ) ? json_decode( stripslashes( $_POST['wcv_deleted_variations'] ) ) : array();
		$variation_indexes  = array();

		// Store variations deleted from the UI and remove them if there is any
		if ( ! empty( $deleted_variations ) ) {
			foreach ( $deleted_variations as $variation ) {
				wp_delete_post( $variation->id );
				$variation_indexes[] = $variation->loop;
			}
		}

		if ( ! isset( $_POST['variable_post_id'] ) ) {
			return;
		}

		$variable_post_id               = $_POST['variable_post_id'];
		$variable_sku                   = $_POST['variable_sku'];
		$variable_regular_price         = $_POST['variable_regular_price'];
		$variable_sale_price            = $_POST['variable_sale_price'];
		$upload_image_id                = $_POST['upload_image_id'];
		$variable_download_limit        = $_POST['variable_download_limit'];
		$variable_download_expiry       = $_POST['variable_download_expiry'];
		$variable_shipping_class        = $_POST['variable_shipping_class'];
		$variable_tax_class             = isset( $_POST['variable_tax_class'] ) ? $_POST['variable_tax_class'] : array();
		$variable_menu_order            = $_POST['variation_menu_order'];
		$variable_sale_price_dates_from = $_POST['variable_sale_price_dates_from'];
		$variable_sale_price_dates_to   = $_POST['variable_sale_price_dates_to'];

		$variable_weight          = isset( $_POST['variable_weight'] ) ? $_POST['variable_weight'] : array();
		$variable_length          = isset( $_POST['variable_length'] ) ? $_POST['variable_length'] : array();
		$variable_width           = isset( $_POST['variable_width'] ) ? $_POST['variable_width'] : array();
		$variable_height          = isset( $_POST['variable_height'] ) ? $_POST['variable_height'] : array();
		$variable_enabled         = isset( $_POST['variable_enabled'] ) ? $_POST['variable_enabled'] : array();
		$variable_is_virtual      = isset( $_POST['variable_is_virtual'] ) ? $_POST['variable_is_virtual'] : array();
		$variable_is_downloadable = isset( $_POST['variable_is_downloadable'] ) ? $_POST['variable_is_downloadable'] : array();

		$variable_manage_stock = isset( $_POST['variable_manage_stock'] ) ? $_POST['variable_manage_stock'] : array();
		$variable_stock        = isset( $_POST['variable_stock'] ) ? $_POST['variable_stock'] : array();
		$variable_backorders   = isset( $_POST['variable_backorders'] ) ? $_POST['variable_backorders'] : array();
		$variable_stock_status = isset( $_POST['variable_stock_status'] ) ? $_POST['variable_stock_status'] : array();

		$variable_description = isset( $_POST['variable_description'] ) ? $_POST['variable_description'] : array();

		$max_loop = max( array_keys( $_POST['variable_post_id'] ) );

		for ( $i = 0; $i <= $max_loop; $i ++ ) {

			if ( ! isset( $variable_post_id[ $i ] ) ) {
				continue;
			}

			if ( in_array( $i, $variation_indexes ) ) {
				continue;
			}

			$variation_id = absint( $variable_post_id[ $i ] );

			// Checkboxes
			$is_virtual      = isset( $variable_is_virtual[ $i ] ) ? 'yes' : 'no';
			$is_downloadable = isset( $variable_is_downloadable[ $i ] ) ? 'yes' : 'no';
			$post_status     = isset( $variable_enabled[ $i ] ) ? 'publish' : 'private';
			$manage_stock    = isset( $variable_manage_stock[ $i ] ) ? 'yes' : 'no';

			// Generate a useful post title
			$variation_post_title = sprintf( __( 'Variation #%1$s of %2$s', 'wcvendors-pro' ), absint( $variation_id ), esc_html( get_the_title( $post_id ) ) );

			// Update or Add post
			if ( ! $variation_id ) {

				$variation = array(
					'post_title'   => $variation_post_title,
					'post_content' => '',
					'post_status'  => $post_status,
					'post_author'  => get_current_user_id(),
					'post_parent'  => $post_id,
					'post_type'    => 'product_variation',
					'menu_order'   => $variable_menu_order[ $i ],
				);

				$variation_id = wp_insert_post( $variation );

				do_action( 'wcv_create_product_variation', $variation_id );

			} else {

				$modified_date = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

				$wpdb->update(
					$wpdb->posts,
					array(
						'post_status'       => $post_status,
						'post_title'        => $variation_post_title,
						'menu_order'        => $variable_menu_order[ $i ],
						'post_modified'     => $modified_date,
						'post_modified_gmt' => get_gmt_from_date( $modified_date ),
					),
					array( 'ID' => $variation_id )
				);

				clean_post_cache( $variation_id );

				do_action( 'wcv_update_product_variation', $variation_id );

			}

			// Only continue if we have a variation ID
			if ( ! $variation_id ) {
				continue;
			}

			// Unique SKU
			$sku     = get_post_meta( $variation_id, '_sku', true );
			$new_sku = wc_clean( $variable_sku[ $i ] );

			if ( '' == $new_sku ) {
				update_post_meta( $variation_id, '_sku', '' );
			} elseif ( $new_sku !== $sku ) {
				if ( ! empty( $new_sku ) ) {
					$unique_sku = wc_product_has_unique_sku( $variation_id, $new_sku );

					if ( ! $unique_sku ) {
						WC_Admin_Meta_Boxes::add_error( sprintf( __( '#%s &ndash; Variation SKU must be unique.', 'wcvendors-pro' ), $variation_id ) );
					} else {
						update_post_meta( $variation_id, '_sku', $new_sku );
					}
				} else {
					update_post_meta( $variation_id, '_sku', '' );
				}
			}

			// Update post meta
			update_post_meta( $variation_id, '_thumbnail_id', absint( $upload_image_id[ $i ] ) );
			update_post_meta( $variation_id, '_virtual', wc_clean( $is_virtual ) );
			update_post_meta( $variation_id, '_downloadable', wc_clean( $is_downloadable ) );

			if ( isset( $variable_weight[ $i ] ) ) {
				update_post_meta( $variation_id, '_weight', ( '' === $variable_weight[ $i ] ) ? '' : wc_format_decimal( $variable_weight[ $i ] ) );
			}

			if ( isset( $variable_length[ $i ] ) ) {
				update_post_meta( $variation_id, '_length', ( '' === $variable_length[ $i ] ) ? '' : wc_format_decimal( $variable_length[ $i ] ) );
			}

			if ( isset( $variable_width[ $i ] ) ) {
				update_post_meta( $variation_id, '_width', ( '' === $variable_width[ $i ] ) ? '' : wc_format_decimal( $variable_width[ $i ] ) );
			}

			if ( isset( $variable_height[ $i ] ) ) {
				update_post_meta( $variation_id, '_height', ( '' === $variable_height[ $i ] ) ? '' : wc_format_decimal( $variable_height[ $i ] ) );
			}

			// Stock handling
			update_post_meta( $variation_id, '_manage_stock', $manage_stock );

			// Only update stock status to user setting if changed by the user, but do so before looking at stock levels at variation level
			if ( ! empty( $variable_stock_status[ $i ] ) ) {
				wc_update_product_stock_status( $variation_id, $variable_stock_status[ $i ] );
			}

			if ( 'yes' === $manage_stock ) {
				update_post_meta( $variation_id, '_backorders', wc_clean( $variable_backorders[ $i ] ) );
				wc_update_product_stock( $variation_id, wc_stock_amount( $variable_stock[ $i ] ) );
			} else {
				delete_post_meta( $variation_id, '_backorders' );
				delete_post_meta( $variation_id, '_stock' );
			}

			// Price handling
			$regular_price = wc_format_decimal( $variable_regular_price[ $i ] );
			$sale_price    = $variable_sale_price[ $i ] === '' ? '' : wc_format_decimal( $variable_sale_price[ $i ] );
			$date_from     = wc_clean( $variable_sale_price_dates_from[ $i ] );
			$date_to       = wc_clean( $variable_sale_price_dates_to[ $i ] );

			if ( wc_clean( $date_from ) == wc_clean( $date_to ) ) {
				$date_to   = '';
				$date_from = '';
			}

			update_post_meta( $variation_id, '_regular_price', $regular_price );
			update_post_meta( $variation_id, '_sale_price', $sale_price );

			// Save Dates
			update_post_meta( $variation_id, '_sale_price_dates_from', $date_from ? strtotime( $date_from ) : '' );
			update_post_meta( $variation_id, '_sale_price_dates_to', $date_to ? strtotime( $date_to ) : '' );

			if ( $date_to && ! $date_from ) {
				update_post_meta( $variation_id, '_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );
			}

			// Update price if on sale
			if ( '' !== $sale_price && '' === $date_to && '' === $date_from ) {
				update_post_meta( $variation_id, '_price', $sale_price );
			} else {
				update_post_meta( $variation_id, '_price', $regular_price );
			}

			if ( '' !== $sale_price && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $variation_id, '_price', $sale_price );
			}

			if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $variation_id, '_price', $regular_price );
				update_post_meta( $variation_id, '_sale_price_dates_from', '' );
				update_post_meta( $variation_id, '_sale_price_dates_to', '' );
			}

			if ( isset( $variable_tax_class[ $i ] ) && $variable_tax_class[ $i ] !== 'parent' ) {
				update_post_meta( $variation_id, '_tax_class', wc_clean( $variable_tax_class[ $i ] ) );
			} else {
				delete_post_meta( $variation_id, '_tax_class' );
			}

			if ( 'yes' == $is_downloadable ) {
				update_post_meta( $variation_id, '_download_limit', wc_clean( $variable_download_limit[ $i ] ) );
				update_post_meta( $variation_id, '_download_expiry', wc_clean( $variable_download_expiry[ $i ] ) );

				$files              = array();
				$file_names         = isset( $_POST['_wc_variation_file_names'][ $variation_id ] ) ? array_map( 'wc_clean', $_POST['_wc_variation_file_names'][ $variation_id ] ) : array();
				$file_urls          = isset( $_POST['_wc_variation_file_urls'][ $variation_id ] ) ? array_map( 'wc_clean', $_POST['_wc_variation_file_urls'][ $variation_id ] ) : array();
				$file_ids           = isset( $_POST['_wc_variation_file_ids'][ $variation_id ] ) ? array_map( 'wc_clean', $_POST['_wc_variation_file_ids'][ $variation_id ] ) : array();
				$file_display       = isset( $_POST['_wc_variation_file_display'][ $variation_id ] ) ? array_map( 'wc_clean', $_POST['_wc_variation_file_display'][ $variation_id ] ) : array();
				$file_url_size      = sizeof( $file_urls );
				$allowed_file_types = get_allowed_mime_types();

				for ( $ii = 0; $ii < $file_url_size; $ii ++ ) {
					if ( ! empty( $file_urls[ $ii ] ) ) {
						// Find type and file URL
						if ( 0 === strpos( $file_urls[ $ii ], 'http' ) ) {
							$file_is  = 'absolute';
							$file_url = esc_url_raw( $file_urls[ $ii ] );
						} elseif ( '[ ' === substr( $file_urls[ $ii ], 0, 1 ) && ' ]' === substr( $file_urls[ $ii ], - 1 ) ) {
							$file_is  = 'shortcode';
							$file_url = wc_clean( $file_urls[ $ii ] );
						} else {
							$file_is  = 'relative';
							$file_url = wc_clean( $file_urls[ $ii ] );
						}

						$file_name = wc_clean( $file_names[ $ii ] );
						$file_hash = md5( $file_url );
						$file_id   = $file_ids[ $ii ];

						// Validate the file extension
						if ( in_array( $file_is, array( 'absolute', 'relative' ) ) ) {
							$file_type  = wp_check_filetype( strtok( $file_url, '?' ), $allowed_file_types );
							$parsed_url = parse_url( $file_url, PHP_URL_PATH );
							$extension  = pathinfo( $parsed_url, PATHINFO_EXTENSION );

							// TODO: Make this on the front end instead of backend
							if ( ! empty( $extension ) && ! in_array( $file_type['type'], $allowed_file_types ) ) {
								WC_Admin_Meta_Boxes::add_error( sprintf( __( '#%1$s &ndash; The downloadable file %2$s cannot be used as it does not have an allowed file type. Allowed types include: %3$s', 'wcvendors-pro' ), $variation_id, '<code>' . basename( $file_url ) . '</code>', '<code>' . implode( ', ', array_keys( $allowed_file_types ) ) . '</code>' ) );
								continue;
							}
						}

						// Validate the file exists
						if ( 'relative' === $file_is && ! apply_filters( 'woocommerce_downloadable_file_exists', file_exists( $file_url ), $file_url ) ) {
							WC_Admin_Meta_Boxes::add_error( sprintf( __( '#%1$s &ndash; The downloadable file %2$s cannot be used as it does not exist on the server.', 'wcvendors-pro' ), $variation_id, '<code>' . $file_url . '</code>' ) );
							continue;
						}

						// Has the file selected
						WCVendors_Pro::md5_attachment_url( $file_id );

						$files[ $file_hash ] = array(
							'name' => $file_name,
							'file' => $file_url,
						);
					}
				}

				// grant permission to any newly added files on any existing orders for this product prior to saving
				do_action( 'wcv_process_product_file_download_paths', $post_id, $variation_id, $files );

				update_post_meta( $variation_id, '_downloadable_files', $files );
			} else {
				update_post_meta( $variation_id, '_download_limit', '' );
				update_post_meta( $variation_id, '_download_expiry', '' );
				update_post_meta( $variation_id, '_downloadable_files', '' );
			}

			$variable_description[ $i ] = $this->allow_markup ? $variable_description[ $i ] : wp_strip_all_tags( $variable_description[ $i ] );

			update_post_meta( $variation_id, '_variation_description', wp_kses_post( $variable_description[ $i ] ) );

			// Save shipping class
			$variable_shipping_class[ $i ] = ! empty( $variable_shipping_class[ $i ] ) ? (int) $variable_shipping_class[ $i ] : '';
			wp_set_object_terms( $variation_id, $variable_shipping_class[ $i ], 'product_shipping_class' );

			// Update Attributes
			$updated_attribute_keys = array();
			foreach ( $attributes as $attribute ) {
				if ( $attribute['is_variation'] ) {
					$attribute_key            = 'attribute_' . sanitize_title( $attribute['name'] );
					$updated_attribute_keys[] = $attribute_key;

					if ( $attribute['is_taxonomy'] ) {
						// Don't use wc_clean as it destroys sanitized characters
						$value = isset( $_POST[ $attribute_key ][ $i ] ) ? sanitize_title( stripslashes( $_POST[ $attribute_key ][ $i ] ) ) : '';
					} else {
						$value = isset( $_POST[ $attribute_key ][ $i ] ) ? wc_clean( stripslashes( $_POST[ $attribute_key ][ $i ] ) ) : '';
					}

					update_post_meta( $variation_id, $attribute_key, $value );
				}
			}

			// Remove old taxonomies attributes so data is kept up to date - first get attribute key names
			$delete_attribute_keys = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE 'attribute_%%' AND meta_key NOT IN ( '" . implode( "','", $updated_attribute_keys ) . "' ) AND post_id = %d;", $variation_id ) );

			foreach ( $delete_attribute_keys as $key ) {
				delete_post_meta( $variation_id, $key );
			}

			do_action( 'wcv_save_product_variation', $variation_id, $i );
		}

		// Update parent if variable so price sorting works and stays in sync with the cheapest child
		WC_Product_Variable::sync( $post_id );

		// Update default attribute options setting
		$default_attributes = array();

		foreach ( $attributes as $attribute ) {

			if ( $attribute['is_variation'] ) {
				$value = '';

				if ( isset( $_POST[ 'default_attribute_' . sanitize_title( $attribute['name'] ) ] ) ) {

					if ( $attribute['is_taxonomy'] ) {
						// Don't use wc_clean as it destroys sanitized characters
						$value = sanitize_title( trim( stripslashes( $_POST[ 'default_attribute_' . sanitize_title( $attribute['name'] ) ] ) ) );
					} else {
						$value = wc_clean( trim( stripslashes( $_POST[ 'default_attribute_' . sanitize_title( $attribute['name'] ) ] ) ) );
					}
				}

				if ( $value ) {
					$default_attributes[ sanitize_title( $attribute['name'] ) ] = $value;
				}
			}
		}

		update_post_meta( $post_id, '_default_attributes', $default_attributes );

	} // save_variations()

	/**
	 * Search for products and echo json
	 *
	 * @since 1.0.0
	 *
	 * @param string $x          (default: '')
	 * @param string $post_types (default: array('product'))
	 */
	public static function json_search_products( $x = '', $post_types = array( 'product' ) ) {

		ob_start();

		check_ajax_referer( 'wcv-search-products', 'security' );

		$term = (string) wc_clean( stripslashes( $_GET['term'] ) );

		if ( empty( $term ) ) {
			die();
		}

		$args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'post_author'    => get_current_user_id(),
			's'              => $term,
			'fields'         => 'ids',
		);

		if ( is_numeric( $term ) ) {

			$args2 = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'post__in'       => array( 0, $term ),
				'fields'         => 'ids',
			);

			$args3 = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'post_parent'    => $term,
				'fields'         => 'ids',
			);

			$args4 = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => '_sku',
						'value'   => $term,
						'compare' => 'LIKE',
					),
				),
				'fields'         => 'ids',
			);

			$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ), get_posts( $args3 ), get_posts( $args4 ) ) );

		} else {

			$args2 = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => '_sku',
						'value'   => $term,
						'compare' => 'LIKE',
					),
				),
				'fields'         => 'ids',
			);

			$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ) ) );

		}

		$found_products = array();

		if ( $posts ) {
			foreach ( $posts as $post ) {
				$product                 = wc_get_product( $post );
				$found_products[ $post ] = rawurldecode( $product->get_formatted_name() );
			}
		}

		$found_products = apply_filters( 'woocommerce_json_search_found_products', $found_products );

		wp_send_json( $found_products );

	}

	/**
	 * Search for product tags and echo json
	 *
	 * @since 1.0.0
	 *
	 * @param string $x          (default: '')
	 * @param string $post_types (default: array('product'))
	 */
	public static function json_search_product_tags() {

		$tag_taxonomy = 'product_tag';

		ob_start();

		check_ajax_referer( 'wcv-search-product-tags', 'security' );

		$term = (string) wc_clean( stripslashes( $_GET['term'] ) );

		if ( empty( $term ) ) {
			die();
		}

		$args = apply_filters(
			'wcv_json_search_tags_args',
			array(
				'orderby'    => 'name',
				'hide_empty' => false,
				'fields'     => 'all',
				'search'     => $term,
				'fields'     => 'ids',
			)
		);

		$tags = get_terms( $tag_taxonomy, $args );

		$found_tags = array();

		if ( $tags ) {

			foreach ( $tags as $tag ) {
				$product_tag        = get_term( $tag, $tag_taxonomy );
				$found_tags[ $tag ] = rawurldecode( $product_tag->name );
			}
		}

		$found_tags = apply_filters( 'wcv_json_search_found_tags', $found_tags );

		wp_send_json( $found_tags );
	}

	/**
	 * Product status text for output on front end.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $status product post status
	 */
	public static function product_status( $status ) {

		$product_status = apply_filters(
			'wcv_product_status',
			array(
				'publish' => __( 'Online', 'wcvendors-pro' ),
				'future'  => __( 'Scheduled', 'wcvendors-pro' ),
				'draft'   => __( 'Draft', 'wcvendors-pro' ),
				'pending' => __( 'Pending Approval', 'wcvendors-pro' ),
				'private' => __( 'Admin Only', 'wcvendors-pro' ),
				'trash'   => __( 'Trash', 'wcvendors-pro' ),
			)
		);

		return $product_status[ $status ];

	} // product_status()

	/**
	 *  Update Table Headers for display of product post type
	 *
	 * @since    1.0.0
	 *
	 * @param     array $headers array passed via filter
	 */
	public function table_columns( $columns ) {

		$columns = array(
			'ID'      => __( 'ID', 'wcvendors-pro' ),
			'tn'      => __( '<i class="wcv-icon wcv-icon-image"></i>', 'wcvendors-pro' ),
			'details' => __( 'Details', 'wcvendors-pro' ),
			'price'   => __( '<i class="wcv-icon wcv-icon-shopping-cart"></i>', 'wcvendors-pro' ),
			'status'  => __( 'Status', 'wcvendors-pro' ),
		);

		return apply_filters( 'wcv_product_table_columns', $columns );

	} // table_columns()

	/**
	 *  Manipulate the table data
	 *
	 * @since    1.0.0
	 * @version  1.7.5
	 *
	 * @param     array $rows          array of wp_post objects passed by the filter
	 * @param     mixed $result_object the wp_query object
	 *
	 * @return   array  $new_rows    array of stdClass objects passed back to the filter
	 */
	public function table_rows( $rows, $result_object ) {

		$new_rows = array();

		$this->max_num_pages = $result_object->max_num_pages;

		$categories_hide    = wc_string_to_bool( get_option( 'wcvendors_hide_product_basic_categories', 'no' ) );
		$tags_hide          = wc_string_to_bool( get_option( 'wcvendors_hide_product_basic_tags', 'no' ) );
		$can_edit           = wc_string_to_bool( get_option( 'wcvendors_capability_products_edit', 'no' ) );
		$can_edit_approved  = wc_string_to_bool( get_option( 'wcvendors_capability_products_approved', 'no' ) );
		$disable_delete     = wc_string_to_bool( get_option( 'wcvendors_capability_product_delete', 'no' ) );
		$allow_duplicate    = wc_string_to_bool( get_option( 'wcvendors_capability_product_duplicate', 'no' ) );
		$trusted_vendor     = ( get_user_meta( get_current_user_id(), '_wcv_trusted_vendor', true ) == 'yes' ) ? true : false;
		$untrusted_vendor   = ( get_user_meta( get_current_user_id(), '_wcv_untrusted_vendor', true ) == 'yes' ) ? true : false;
		$lock_edit_products = ( get_user_meta( get_current_user_id(), '_wcv_lock_edit_products_vendor', true ) == 'yes' ) ? true : false;

		if ( $trusted_vendor ) {
			$can_edit = true;
		}
		if ( $untrusted_vendor ) {
			$can_edit = false;
		}

		// get the page product listing page number and pass that value with edit link.
		$page_no = ( 1 < $result_object->query['paged'] && 'list' == get_option( 'wcvendors_save_product_redirect', 'empty' ) ) ? '?wcv_paged_id=' . $result_object->query['paged'] : '';

		foreach ( $rows as $row ) {

			$product = wc_get_product( $row->ID );
			$new_row = new stdClass();

			$row_actions = apply_filters(
				'wcv_product_table_row_actions',
				array(
					'edit'      => apply_filters(
						'wcv_product_table_row_actions_edit',
						array(
							'label' => __( 'Edit', 'wcvendors-pro' ),
							'class' => '',
							'url'   => self::get_product_edit_link( $product->get_id() ) . $page_no,
						)
					),
					'duplicate' => apply_filters(
						'wcv_product_table_row_actions_duplicate',
						array(
							'label' => __( 'Duplicate', 'wcvendors-pro' ),
							'class' => '',
							'url'   => WCVendors_Pro_Dashboard::get_dashboard_page_url( 'product/duplicate/' . $product->get_id() ),
						)
					),
					'delete'    => apply_filters(
						'wcv_product_table_row_actions_delete',
						array(
							'label'  => __( 'Delete', 'wcvendors-pro' ),
							'class'  => 'confirm_delete',
							'custom' => array( 'data-confirm_text' => __( 'Delete product?', 'wcvendors-pro' ) ),
							'url'    => WCVendors_Pro_Dashboard::get_dashboard_page_url( 'product/delete/' . $product->get_id() ),
						)
					),
					'view'      => apply_filters(
						'wcv_product_table_row_actions_view',
						array(
							'label'  => __( 'View', 'wcvendors-pro' ),
							'class'  => '',
							'url'    => get_permalink( $product->get_id() ),
							'target' => wc_string_to_bool( get_option( 'wcvendors_dashboard_view_product_new_window', 'yes' ) ) ? '_blank' : '_self',
						)
					),
				),
				$product
			);

			// Check if you can edit published products or the product is variable
			// if (  !$can_edit && $row->post_status == 'publish' || $product->product_type == 'variable' ) {
			if ( ! $can_edit && $row->post_status == 'publish' || $lock_edit_products ) {
				unset( $row_actions['edit'] );
			}

			// Check if you can delete the product
			if ( $disable_delete ) {
				unset( $row_actions['delete'] );
			}

			// Check if you can duplicate the product
			if ( ! $allow_duplicate ) {
				unset( $row_actions['duplicate'] );
			}

			$product_types = wcv_get_product_types();
			$product_type  = $product_types[ $product->get_type() ];

			$categories_label   = $categories_hide ? '' : apply_filters( 'wcv_product_row_category_label', __( 'Categories:', 'wcvendors-pro' ), $product, $product->get_id() );
			$tags_label         = $tags_hide ? '' : apply_filters( 'wcv_product_row_tags_label', __( 'Tags:', 'wcvendors-pro' ), $product, $product->get_id() );
			$stock_status       = $product->get_manage_stock() ? ( $product->is_in_stock() ) ? __( 'In stock (' . $product->get_stock_quantity() . ')', 'wcvendors-pro' ) : __( 'Out of Stock', 'wcvendors-pro' ) : '';
			$stock_status_label = $product->get_manage_stock() ? apply_filters( 'wcv_stock_status_label', __( 'Stock status: ', 'wcvendors-pro' ) ) : '';
			$product_price      = wc_get_price_to_display( $product );
			$product_categories = $categories_hide ? '' : wc_get_product_category_list( $product->get_id() );
			$product_tags       = $tags_hide ? '' : wc_get_product_tag_list( $product->get_id() );

			$new_row->ID          = $row->ID;
			$new_row->tn          = get_the_post_thumbnail( $row->ID, array( 120, 120 ) );
			$new_row->details     = apply_filters(
				'wcv_product_row_details',
				sprintf(
					'<h4>%s</h4>
					<div class="wcv_mobile_status wcv_mobile">%s</div>
					<div class="wcv_mobile_price wcv_mobile">%s</div>
					<div class="cat_tags">%s %s <br />%s %s</div>',
					$product->get_title(),
				self::product_status( $row->post_status ),
				wc_price( $product_price . $product->get_price_suffix() ),
				$categories_label,
				$product_categories,
				$tags_label,
				$product_tags
				),
				$product,
				$product->get_id()
				);
			$new_row->price       = wc_price( $product_price . $product->get_price_suffix() );
			$new_row->status      = apply_filters(
				'wcv_product_row_status',
				sprintf(
					'<span class="status %s">%s</span><br />
					<span class="product_type %s">%s</span><br />
					<span class="product_date">%s</span><br />
					<span class="stock_status">%s</span>',
					lcfirst( self::product_status( $row->post_status ) ),
					self::product_status( $row->post_status ),
					sanitize_title_with_dashes( lcfirst( $product_type ) ),
					$product_type,
					date_i18n( get_option( 'date_format' ), strtotime( $row->post_date ) ),
					$stock_status_label . $stock_status
				),
				self::product_status( $row->post_status ),
				$product_type,
				date_i18n( get_option( 'date_format' ), strtotime( $row->post_date ) ),
				$stock_status_label . $stock_status,
				$product
			);
			$new_row->row_actions = $row_actions;
			$new_row->product     = $product;

			$new_rows[] = $new_row;
		}

		return apply_filters( 'wcv_product_table_rows', $new_rows );

	} // table_rows()

	/**
	 *  Change the column that actions are displayed in
	 *
	 * @since    1.0.0
	 *
	 * @param     string $column column passed from filter
	 *
	 * @return   string $new_column    new column passed back to filter
	 */
	public function table_action_column( $column ) {

		$new_column = 'details';

		return apply_filters( 'wcv_product_table_action_column', $new_column );

	}

	/**
	 *  Add actions before and after the table
	 *
	 * @since    1.0.0
	 */
	public function table_actions( $id = 'product', $position = 'before' ) {

		$lock_new_products   = apply_filters( 'wcv_product_table_lock_new_products', ( get_user_meta( get_current_user_id(), '_wcv_lock_new_products_vendor', true ) == 'yes' ) ? true : false );
		$can_submit_products = get_option( 'wcvendors_can_submit_products', '' );
		$search              = isset( $_GET['wcv-search'] ) ? $_GET['wcv-search'] : '';

		$pagination_wrapper = apply_filters(
			'wcv_product_paginate_wrapper',
			array(
				'wrapper_start' => '<nav class="woocommerce-pagination">',
				'wrapper_end'   => '</nav>',
			)
		);

		$template_overrides = $this->get_product_templates();
		include apply_filters( 'wcv_product_table_actions_path', 'partials/product/wcvendors-pro-table-actions.php' );

	}

	/**
	 * Prepare template overrides option.
	 *
	 * @param $templates
	 *
	 * @return array
	 */
	public function template_overrides_options( $templates ) {
		$template_path     = 'wc-vendors/dashboard/';
		$product_templates = array();

		if ( file_exists( STYLESHEETPATH . '/' . $template_path ) ) {
			$product_templates = glob( STYLESHEETPATH . '/' . $template_path . 'product-*.php' );
		}

		if ( ! empty( $product_templates ) ) {

			foreach ( $product_templates as $file_path ) {

				$prefix = STYLESHEETPATH . '/' . $template_path;

				// Find the file name from he path, remove the extension then generate the dashboard link
				if ( substr( $file_path, 0, strlen( $prefix ) ) == $prefix ) {
					$filename        = substr( $file_path, strlen( $prefix ) );
					$filename_no_ext = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $filename );
					$template_part   = substr( $filename_no_ext, strlen( 'product-' ) );
					$template_info   = implode( '', file( $file_path ) );
					$label           = ( preg_match( '|Label: (.*)$|mi', $template_info, $label_name ) ) ? $label_name[1] : ucfirst( $template_part );

					if ( $filename == self::get_default_product_template()['filename'] ) {
continue;
					}

					$templates[ $template_part ] = $label;
				}
			}
		}

		return $templates;
	}

	/**
	 *  Change the column that actions are displayed in
	 *
	 * @since    1.0.0
	 *
	 * @param     string $column column passed from filter
	 *
	 * @return   string $new_column    new column passed back to filter
	 */
	public function table_no_data_notice( $notice ) {

		$notice = __( 'No products found.', 'wcvendors-pro' );

		return apply_filters( 'wcv_product_table_no_data_notice', $notice );

	}

	/**
	 *  Posts per page
	 *
	 * @since    1.2.4
	 *
	 * @param     int $post_num number of posts to display from the admin options.
	 */
	public function table_posts_per_page( $per_page ) {

		return get_option( 'wcvendors_products_per_page', 20 );

	} //table_posts_per_page()

	/**
	 *  Add Atribute ajax call
	 *
	 * @since    1.3.0
	 */
	public static function json_add_attribute() {

		ob_start();

		check_ajax_referer( 'wcv-add-attribute', 'security' );

		if ( ! current_user_can( 'edit_products' ) ) {
			die( - 1 );
		}

		global $wc_product_attributes;

		$post_id       = 0;
		$taxonomy      = sanitize_text_field( $_POST['taxonomy'] );
		$i             = absint( $_POST['i'] );
		$position      = 0;
		$metabox_class = array();
		$attribute     = array(
			'name'         => $taxonomy,
			'value'        => '',
			'is_visible'   => apply_filters( 'woocommerce_attribute_default_visibility', 1 ),
			'is_variation' => 0,
			'is_taxonomy'  => $taxonomy ? 1 : 0,
		);

		if ( $taxonomy ) {
			$attribute_taxonomy = $wc_product_attributes[ $taxonomy ];
			$metabox_class[]    = 'taxonomy';
			$metabox_class[]    = $taxonomy;
			$attribute_label    = wc_attribute_label( $taxonomy );
		} else {
			$attribute_label = '';
		}

		$attribute_types = wc_get_attribute_types();

		if ( ! array_key_exists( $attribute_taxonomy->attribute_type, $attribute_types ) || ( ! $attribute_taxonomy->attribute_type == 'text' ) || ! ( $attribute_taxonomy->attribute_type == 'select' ) ) {
			$attribute_taxonomy->attribute_type = 'select';
		}

		$attribute_terms_allowed = wc_string_to_bool( get_option( 'wcvendors_allow_vendor_attribute_terms', 'no' ) );

		include apply_filters( 'wcvendors_pro_product_attribute_path', 'forms/partials/wcvendors-pro-product-attribute.php' );

		wp_die();
	}

	/**
	 *  Add a new atribute ajax call
	 *
	 * @since    1.2.5
	 */
	public static function json_add_new_attribute() {

		$attribute_terms_allowed = wc_string_to_bool( get_option( 'wcvendors_allow_vendor_attribute_terms', 'no' ) );

		ob_start();

		check_ajax_referer( 'wcv-add-attribute', 'security' );

		if ( ! $attribute_terms_allowed ) {

			wp_send_json(
				array(
					'error' => __( 'No permission to add attributes. ', 'wcvendors-pro' ),
				)
			);
			die();
		}

		$taxonomy = esc_attr( $_POST['taxonomy'] );
		$term     = wc_clean( $_POST['term'] );

		if ( taxonomy_exists( $taxonomy ) ) {

			$result = wp_insert_term( $term, $taxonomy );

			if ( is_wp_error( $result ) ) {
				wp_send_json(
					array(
						'error' => $result->get_error_message(),
					)
				);
			} else {
				$term = get_term_by( 'id', $result['term_id'], $taxonomy );
				wp_send_json(
					array(
						'term_id' => $term->term_id,
						'name'    => $term->name,
						'slug'    => $term->slug,
					)
				);
			}
		}

		die();

	} // json_add_new_attribute()

	/**
	 *  Load existing variations
	 *
	 * @since    1.3.0
	 */
	public static function load_variations( $product_id ) {

		// Get attributes for this product
		$attributes = maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );

		// Get tax classes
		$tax_classes                   = WC_Tax::get_tax_classes();
		$tax_class_options             = array();
		$tax_class_options['standard'] = __( 'Standard', 'wcvendors-pro' );

		if ( ! empty( $tax_classes ) ) {
			foreach ( $tax_classes as $class ) {
				$tax_class_options[ sanitize_title( $class ) ] = esc_attr( $class );
			}
		}

		// Set backorder options
		$backorder_options = array(
			'no'     => __( 'Do not allow', 'wcvendors-pro' ),
			'notify' => __( 'Allow, but notify customer', 'wcvendors-pro' ),
			'yes'    => __( 'Allow', 'wcvendors-pro' ),
		);

		// set stock status options
		$stock_status_options = array(
			'instock'    => __( 'In stock', 'wcvendors-pro' ),
			'outofstock' => __( 'Out of stock', 'wcvendors-pro' ),
		);

		$parent_data = array(
			'id'                   => $product_id,
			'attributes'           => $attributes,
			'tax_class_options'    => $tax_class_options,
			'sku'                  => get_post_meta( $product_id, '_sku', true ),
			'weight'               => wc_format_localized_decimal( get_post_meta( $product_id, '_weight', true ) ),
			'length'               => wc_format_localized_decimal( get_post_meta( $product_id, '_length', true ) ),
			'width'                => wc_format_localized_decimal( get_post_meta( $product_id, '_width', true ) ),
			'height'               => wc_format_localized_decimal( get_post_meta( $product_id, '_height', true ) ),
			'tax_class'            => get_post_meta( $product_id, '_tax_class', true ),
			'backorder_options'    => $backorder_options,
			'stock_status_options' => $stock_status_options,
		);

		if ( ! $parent_data['weight'] ) {
			$parent_data['weight'] = wc_format_localized_decimal( 0 );
		}

		if ( ! $parent_data['length'] ) {
			$parent_data['length'] = wc_format_localized_decimal( 0 );
		}

		if ( ! $parent_data['width'] ) {
			$parent_data['width'] = wc_format_localized_decimal( 0 );
		}

		if ( ! $parent_data['height'] ) {
			$parent_data['height'] = wc_format_localized_decimal( 0 );
		}

		// Get variations
		$args = array(
			'post_type'      => 'product_variation',
			'post_status'    => array( 'private', 'publish' ),
			'posts_per_page' => - 1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'ID'         => 'DESC',
			),
			'post_parent'    => $product_id,
		);

		$variations = get_posts( $args );

		$loop = 0;

		if ( $variations ) {

			foreach ( $variations as $variation ) {
				$variation_id     = absint( $variation->ID );
				$variation_meta   = get_post_meta( $variation_id );
				$variation_data   = array();
				$shipping_classes = get_the_terms( $variation_id, 'product_shipping_class' );
				$variation_fields = array(
					'_sku'                   => '',
					'_stock'                 => '',
					'_regular_price'         => '',
					'_sale_price'            => '',
					'_weight'                => '',
					'_length'                => '',
					'_width'                 => '',
					'_height'                => '',
					'_download_limit'        => '',
					'_download_expiry'       => '',
					'_downloadable_files'    => '',
					'_downloadable'          => '',
					'_virtual'               => '',
					'_thumbnail_id'          => '',
					'_sale_price_dates_from' => '',
					'_sale_price_dates_to'   => '',
					'_manage_stock'          => '',
					'_stock_status'          => '',
					'_backorders'            => null,
					'_tax_class'             => null,
					'_variation_description' => '',
				);

				foreach ( $variation_fields as $field => $value ) {
					$variation_data[ $field ] = isset( $variation_meta[ $field ][0] ) ? maybe_unserialize( $variation_meta[ $field ][0] ) : $value;
				}

				// Add the variation attributes
				$variation_data = array_merge( $variation_data, wc_get_product_variation_attributes( $variation_id ) );

				// Formatting
				$variation_data['_regular_price'] = wc_format_localized_price( $variation_data['_regular_price'] );
				$variation_data['_sale_price']    = wc_format_localized_price( $variation_data['_sale_price'] );
				$variation_data['_weight']        = wc_format_localized_decimal( $variation_data['_weight'] );
				$variation_data['_length']        = wc_format_localized_decimal( $variation_data['_length'] );
				$variation_data['_width']         = wc_format_localized_decimal( $variation_data['_width'] );
				$variation_data['_height']        = wc_format_localized_decimal( $variation_data['_height'] );
				$variation_data['_thumbnail_id']  = absint( $variation_data['_thumbnail_id'] );
				$variation_data['image']          = $variation_data['_thumbnail_id'] ? wp_get_attachment_thumb_url( $variation_data['_thumbnail_id'] ) : '';
				$variation_data['shipping_class'] = $shipping_classes && ! is_wp_error( $shipping_classes ) ? current( $shipping_classes )->term_id : '';
				$variation_data['menu_order']     = $variation->menu_order;
				$variation_data['_stock']         = '' === $variation_data['_stock'] ? '' : (int) wc_stock_amount( $variation_data['_stock'] );
				$variation_data['_enabled']       = ( $variation->post_status == 'publish' ) ? true : false;
				$variation_data['id']             = $variation_id;

				include apply_filters( 'wcvendors_pro_product_variation_path', 'forms/partials/wcvendors-pro-product-variation.php' );

				$loop ++;
			}
		}

	} //load_variations()

	/**
	 * Add variation
	 *
	 * @since    1.3.0
	 */
	public static function json_add_variation() {

		check_ajax_referer( 'wcv-add-variation', 'security' );

		if ( ! current_user_can( 'edit_products' ) ) {
			die( - 1 );
		}

		$attributes                = $_POST['attributes'];
		$loop                      = intval( $_POST['loop'] );
		$parent_data               = $_POST['parent_data'];
		$parent_data['attributes'] = $attributes;

		$variation_id = 0;

		if ( $parent_data['attributes'] ) {
			$variation_data   = array();
			$shipping_classes = get_the_terms( $variation_id, 'product_shipping_class' );
			$variation_fields = array(
				'_sku'                   => '',
				'_stock'                 => '',
				'_regular_price'         => '',
				'_sale_price'            => '',
				'_weight'                => '',
				'_length'                => '',
				'_width'                 => '',
				'_height'                => '',
				'_download_limit'        => '',
				'_download_expiry'       => '',
				'_downloadable_files'    => '',
				'_downloadable'          => '',
				'_virtual'               => '',
				'_thumbnail_id'          => '',
				'_sale_price_dates_from' => '',
				'_sale_price_dates_to'   => '',
				'_manage_stock'          => '',
				'_stock_status'          => '',
				'_backorders'            => null,
				'_tax_class'             => null,
				'_variation_description' => '',
			);

			foreach ( $variation_fields as $field => $value ) {
				$variation_data[ $field ] = isset( $variation_meta[ $field ][0] ) ? maybe_unserialize( $variation_meta[ $field ][0] ) : $value;
			}

			$variation_data['_enabled'] = true;

			// Formatting
			$variation_data['_regular_price'] = wc_format_localized_price( $variation_data['_regular_price'] );
			$variation_data['_sale_price']    = wc_format_localized_price( $variation_data['_sale_price'] );
			$variation_data['_weight']        = wc_format_localized_decimal( $variation_data['_weight'] );
			$variation_data['_length']        = wc_format_localized_decimal( $variation_data['_length'] );
			$variation_data['_width']         = wc_format_localized_decimal( $variation_data['_width'] );
			$variation_data['_height']        = wc_format_localized_decimal( $variation_data['_height'] );
			$variation_data['_thumbnail_id']  = absint( $variation_data['_thumbnail_id'] );
			$variation_data['image']          = $variation_data['_thumbnail_id'] ? wp_get_attachment_thumb_url( $variation_data['_thumbnail_id'] ) : '';
			$variation_data['shipping_class'] = $shipping_classes && ! is_wp_error( $shipping_classes ) ? current( $shipping_classes )->term_id : '';
			$variation_data['menu_order']     = - 1;
			$variation_data['_stock']         = (int) wc_stock_amount( $variation_data['_stock'] );
			$variation_data['id']             = $loop;

			// Get tax classes
			$tax_classes                   = WC_Tax::get_tax_classes();
			$tax_class_options             = array();
			$tax_class_options['standard'] = __( 'Standard', 'wcvendors-pro' );

			if ( ! empty( $tax_classes ) ) {
				foreach ( $tax_classes as $class ) {
					$tax_class_options[ sanitize_title( $class ) ] = esc_attr( $class );
				}
			}

			// Set backorder options
			$backorder_options = array(
				'no'     => __( 'Do not allow', 'wcvendors-pro' ),
				'notify' => __( 'Allow, but notify customer', 'wcvendors-pro' ),
				'yes'    => __( 'Allow', 'wcvendors-pro' ),
			);

			// set stock status options
			$stock_status_options = array(
				'instock'    => __( 'In stock', 'wcvendors-pro' ),
				'outofstock' => __( 'Out of stock', 'wcvendors-pro' ),
			);

			$parent_data['tax_class_options']    = $tax_class_options;
			$parent_data['backorder_options']    = $backorder_options;
			$parent_data['stock_status_options'] = $stock_status_options;

			if ( ! $parent_data['weight'] ) {
				$parent_data[' weight'] = wc_format_localized_decimal( 0 );
			}

			if ( ! $parent_data['length'] ) {
				$parent_data['length'] = wc_format_localized_decimal( 0 );
			}

			if ( ! $parent_data['width'] ) {
				$parent_data['width'] = wc_format_localized_decimal( 0 );
			}

			if ( ! $parent_data['height'] ) {
				$parent_data['height'] = wc_format_localized_decimal( 0 );
			}

			include apply_filters( 'wcvendors_pro_product_variation_path', 'forms/partials/wcvendors-pro-product-variation.php' );
		}

		die();

	} //json_add_variation()

	/**
	 * link all variations
	 *
	 * @since    1.3.0
	 */
	public static function json_link_all_variations() {

		if ( ! defined( 'WC_MAX_LINKED_VARIATIONS' ) ) {
			define( 'WC_MAX_LINKED_VARIATIONS', 50 );
		}

		check_ajax_referer( 'wcv-link-all-variations', 'security' );

		if ( ! current_user_can( 'edit_products' ) ) {
			die( - 1 );
		}

		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 0 );
		}

		$attributes           = $_POST['attributes'];
		$available_variations = isset( $_POST['available_variations'] ) ? $_POST['available_variations'] : array();
		$parent_data          = $_POST['parent_data'];
		$loop                 = $_POST['loop'];
		$variations           = array();
		$added                = 1;

		// No attributes? return
		if ( ! $attributes ) {
			die();
		}

		foreach ( $attributes as $key => $attribute ) {

			$attribute_field_name = 'attribute_' . sanitize_title( $attribute['name'] );

			$options = array();

			if ( array_key_exists( 'values', $attribute ) ) {
				foreach ( $attribute['values'] as $key => $value ) {
					$options[] = $key;
				}
			} elseif ( array_key_exists( 'value', $attribute ) ) {
				$attribute_options = explode( '|', $attribute['value'] );
				if ( is_array( $attribute_options ) ) {
					foreach ( $attribute_options as $option_value ) {
						$options[] = trim( $option_value );
					}
				} else {
					$options[] = trim( $attribute['value'] );
				}
			}

			$variations[ $attribute_field_name ] = $options;

		}

		// No variations? return
		if ( sizeof( $variations ) == 0 ) {
			die();
		}

		$possible_variations = wc_array_cartesian( $variations );

		foreach ( $possible_variations as $variation ) {

			// Check if variation already exists
			if ( in_array( $variation, $available_variations ) ) {
				continue;
			}

			$variation_id = 0;

			if ( $variation ) {
				$variation_data   = array();
				$shipping_classes = get_the_terms( $variation_id, 'product_shipping_class' );
				$variation_fields = array(
					'_sku'                   => '',
					'_stock'                 => '',
					'_regular_price'         => '',
					'_sale_price'            => '',
					'_weight'                => '',
					'_length'                => '',
					'_width'                 => '',
					'_height'                => '',
					'_download_limit'        => '',
					'_download_expiry'       => '',
					'_downloadable_files'    => '',
					'_downloadable'          => '',
					'_virtual'               => '',
					'_thumbnail_id'          => '',
					'_sale_price_dates_from' => '',
					'_sale_price_dates_to'   => '',
					'_manage_stock'          => '',
					'_stock_status'          => '',
					'_backorders'            => null,
					'_tax_class'             => null,
					'_variation_description' => '',
				);

				foreach ( $variation_fields as $field => $value ) {
					$variation_data[ $field ] = isset( $variation_meta[ $field ][0] ) ? maybe_unserialize( $variation_meta[ $field ][0] ) : $value;
				}

				$variation_data['_enabled'] = true;

				// Formatting
				$variation_data['_regular_price'] = wc_format_localized_price( $variation_data['_regular_price'] );
				$variation_data['_sale_price']    = wc_format_localized_price( $variation_data['_sale_price'] );
				$variation_data['_weight']        = wc_format_localized_decimal( $variation_data['_weight'] );
				$variation_data['_length']        = wc_format_localized_decimal( $variation_data['_length'] );
				$variation_data['_width']         = wc_format_localized_decimal( $variation_data['_width'] );
				$variation_data['_height']        = wc_format_localized_decimal( $variation_data['_height'] );
				$variation_data['_thumbnail_id']  = absint( $variation_data['_thumbnail_id'] );
				$variation_data['image']          = $variation_data['_thumbnail_id'] ? wp_get_attachment_thumb_url( $variation_data['_thumbnail_id'] ) : '';
				$variation_data['shipping_class'] = $shipping_classes && ! is_wp_error( $shipping_classes ) ? current( $shipping_classes )->term_id : '';
				$variation_data['menu_order']     = - 1;
				$variation_data['_stock']         = (int) wc_stock_amount( $variation_data['_stock'] );
				$variation_data['id']             = $loop ++;

				foreach ( $variation as $key => $value ) {
					$variation_data[ $key ] = $value;
				}

				// Get tax classes
				$tax_classes                   = WC_Tax::get_tax_classes();
				$tax_class_options             = array();
				$tax_class_options['standard'] = __( 'Standard', 'wcvendors-pro' );

				if ( ! empty( $tax_classes ) ) {
					foreach ( $tax_classes as $class ) {
						$tax_class_options[ sanitize_title( $class ) ] = esc_attr( $class );
					}
				}

				// Set backorder options
				$backorder_options = array(
					'no'     => __( 'Do not allow', 'wcvendors-pro' ),
					'notify' => __( 'Allow, but notify customer', 'wcvendors-pro' ),
					'yes'    => __( 'Allow', 'wcvendors-pro' ),
				);

				// set stock status options
				$stock_status_options = array(
					'instock'    => __( 'In stock', 'wcvendors-pro' ),
					'outofstock' => __( 'Out of stock', 'wcvendors-pro' ),
				);

				$parent_data['tax_class_options']    = $tax_class_options;
				$parent_data['backorder_options']    = $backorder_options;
				$parent_data['stock_status_options'] = $stock_status_options;

				if ( ! $parent_data['weight'] ) {
					$parent_data[' weight'] = wc_format_localized_decimal( 0 );
				}

				if ( ! $parent_data['length'] ) {
					$parent_data['length'] = wc_format_localized_decimal( 0 );
				}

				if ( ! $parent_data['width'] ) {
					$parent_data['width'] = wc_format_localized_decimal( 0 );
				}

				if ( ! $parent_data['height'] ) {
					$parent_data['height'] = wc_format_localized_decimal( 0 );
				}

				include apply_filters( 'wcvendors_pro_product_variation_path', 'forms/partials/wcvendors-pro-product-variation.php' );

			}

			$added ++;

			if ( $added > WC_MAX_LINKED_VARIATIONS ) {
				break;
			}
		}

		die();
	}

	/**
	 * Default attributes toolbar
	 *
	 * @since    1.3.0
	 */
	public static function json_default_variation_attributes() {

		ob_start();

		check_ajax_referer( 'wcv-add-variation', 'security' );

		$attributes = $_POST['attributes'];

		include apply_filters( 'wcvendors_pro_product_variation_default_path', 'forms/partials/wcvendors-pro-product-variations-default-attribute.php' );

		die();

	} //json_default_variation_attributes()

	/**
	 * Get the product edit link depending on several different variables
	 *
	 * @since  1.4.0
	 * @version 1.6.4
	 * @access public
	 * @return array $product_edit_link
	 */
	public static function get_product_edit_link( $product_id = null, $default = false ) {

		$default_template = get_option( 'wcvendors_product_form_template', 'standard' );
		$default_template = 'edit' === $default_template ? 'standard' : $default_template;
		$default_link     = ( 'standard' === $default_template ) ? 'product/edit/' : 'product/' . $default_template . '/edit/';

		// Only load a custom template if the product has one
		if ( $product_id ) {
			$template = get_post_meta( $product_id, '_wcv_product_form_template', true );
			if ( empty( $template ) || $template === 'edit' || $template === 'standard' ) {
				$template     = '';
				$default_link = 'product/edit/';
			} else {
				$template = $template . '/';
			}
		}

		if ( $default ) {
			$template = $default_link;
		}

		$template_url = ( empty( $template ) || $template === '' ) ? $default_link : 'product/' . $template . 'edit/';

		return apply_filters( 'wcv_product_edit_link', WCVendors_Pro_Dashboard::get_dashboard_page_url( $template_url . $product_id ) );

	}

	/**
	 * Get the global default product template for the store.
	 *
	 * @since  1.4.0
	 * @access public
	 * @return array $default_product_template - details for the default product edit template
	 */
	public static function get_default_product_template() {

		$template  = get_option( 'wcvendors_product_form_template', 'standard' );
		$template  = ( $template === 'standard' ) ? $template : $template . '/';
		$file_name = ( 'standard' === $template ) ? 'product-edit.php' : 'product-' . $template . '.php';
		$url_path  = ( 'standard' === $template ) ? 'product/edit' : 'product/' . $template . 'edit/';

		$default_product_template = apply_filters(
			'wcv_default_product_template',
			array(
				'filename' => $file_name,
				'url_path' => $url_path,
				'label'    => __( 'Product', 'wcvendors-pro' ),
				'url'      => WCVendors_Pro_Dashboard::get_dashboard_page_url( $url_path ),
			)
		);

		return $default_product_template;

	} // get_default_product_template()

	/**
	 * Get the product template overrides from the theme directory
	 * Required file format to work product-mytemplatenamehere.
	 *
	 * @since    1.4.0
	 * @version  1.6.2
	 * @access   public
	 */
	public function get_product_templates() {

		$template_path              = 'wc-vendors/dashboard/';
		$files                      = array();
		$product_templates          = array();
		$default_template           = get_option( 'wcvendors_product_form_template', 'standard' );
		$files[ $default_template ] = self::get_default_product_template();

		if ( file_exists( STYLESHEETPATH . '/' . $template_path ) ) {
			$product_templates = glob( STYLESHEETPATH . '/' . $template_path . 'product-*.php' );
		}

		if ( ! empty( $product_templates ) ) {

			foreach ( $product_templates as $file_path ) {

				$prefix = STYLESHEETPATH . '/' . $template_path;

				// Find the file name from he path, remove the extension then generate the dashboard link
				if ( substr( $file_path, 0, strlen( $prefix ) ) == $prefix ) {
					$filename        = substr( $file_path, strlen( $prefix ) );
					$filename_no_ext = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $filename );
					$template_part   = substr( $filename_no_ext, strlen( 'product-' ) );

					// Check if the file override is the default template
					if ( $filename == self::get_default_product_template()['filename'] ) {
continue;
					}

					$template_info           = implode( '', file( $file_path ) );
					$url_path                = str_replace( '-', '/', $filename_no_ext );
					$label                   = ( preg_match( '|Label: (.*)$|mi', $template_info, $label_name ) ) ? $label_name[1] : ucfirst( $template_part );
					$files[ $template_part ] = array(
						'filename' => $filename,
						'url_path' => $url_path,
						'label'    => $label,
						'url'      => WCVendors_Pro_Dashboard::get_dashboard_page_url( $url_path . '/edit' ),
					);
				}
			}
		}

		return apply_filters( 'wcv_product_template_files', $files );

	} // get_product_templates()

	/**
	 *    Check the coupons to add the product id if not already added.
	 */
	public function check_coupons() {

		$all_vendor_product_ids = implode( ',', WCVendors_Pro_Vendor_Controller::get_products_by_id( get_current_user_id() ) );

		$coupon_args = array(
			'posts_per_page' => - 1,
			'orderby'        => 'title',
			'author'         => get_current_user_id(),
			'order'          => 'asc',
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
		);

		$coupons = get_posts( $coupon_args );

		foreach ( $coupons as $coupon ) {

			$apply_to_all = get_post_meta( $coupon->ID, 'apply_to_all_products', true );

			if ( 'yes' === $apply_to_all ) {
				update_post_meta( $coupon->ID, 'product_ids', $all_vendor_product_ids );
			}
		}

	} // check_coupons()

	/**
	 * Filter file uploads to check image dimensions and provide a prefix for iamages
	 *
	 * @since 1.5.0
	 */
	public function filter_upload( $file ) {

		// Run the prefix code on the dashboard for all vendor uploaded images
		if ( false === strpos( wp_get_referer(), WCVendors_Pro_Dashboard::get_dashboard_page_url() ) ) {
			return $file;
		}

		$user         = get_userdata( get_current_user_id() );
		$image_prefix = get_option( 'wcvendors_vendor_image_prefix', '' );
		$image        = getimagesize( $file['tmp_name'] );

		// Only run this code on images
		if ( false === strpos( $image['mime'], 'image' ) ) {
			return $file;
		}

		switch ( $image_prefix ) {
			case 'vendor_id':
				$prefix = $user->ID;
				break;
			case 'vendor_username':
				$prefix = $user->user_login;
				break;
			default:
				$prefix = '';
				break;
		}

		if ( ! empty( $prefix ) ) {
			// Prefix all files with the vendors user_id
			$file['name'] = $prefix . '-' . $file['name'];
		}

		// Only run this on the Pro dashboard page product page
		if ( false === strpos( wp_get_referer(), WCVendors_Pro_Dashboard::get_dashboard_page_url( 'product' ) ) ) {
			return $file;
		}

		$max_image_width    = get_option( 'wcvendors_product_max_image_width', '' );
		$max_image_height   = get_option( 'wcvendors_product_max_image_height', '' );
		$min_image_width    = get_option( 'wcvendors_product_min_image_width', '' );
		$min_image_height   = get_option( 'wcvendors_product_min_image_height', '' );
		$max_width_message  = apply_filters( 'wcv_product_product_max_width_msg', sprintf( __( 'The image file uploaded exceeds the maximum width allowed. Maximium width is %spx', 'wcvendors-pro' ), $max_image_width ) );
		$max_height_message = apply_filters( 'wcv_product_product_max_height_msg', sprintf( __( 'The image file uploaded exceeds the maximum height allowed. Maximum height is %spx', 'wcvendors-pro' ), $max_image_height ) );
		$min_wide_message   = apply_filters( 'wcv_product_product_min_width_msg', sprintf( __( 'The image file uploaded does not meet the minimum width required. Minimum image width is %spx', 'wcvendors-pro' ), $min_image_width ) );
		$min_height_message = apply_filters( 'wcv_product_product_min_height_msg', sprintf( __( 'The image file uploaded does not meet the minimum height required. Minimum image height is %spx.', 'wcvendors-pro' ), $min_image_height ) );

		$image_width  = $image[0];
		$image_height = $image[1];

		// Maximum width
		if ( isset( $max_image_width ) && $max_image_width > 0 ) {
			if ( $image_width > $max_image_width ) {
				$file['error'] = $max_width_message;

				return $file;
			}
		}

		// Maximum Height
		if ( isset( $max_image_height ) && $max_image_height > 0 ) {
			if ( $image_height > $max_image_height ) {
				$file['error'] = $max_height_message;

				return $file;
			}
		}

		// Minimum width
		if ( isset( $min_image_width ) && $min_image_width > 0 ) {
			if ( $image_width < $min_image_width ) {
				$file['error'] = $min_wide_message;

				return $file;
			}
		}

		// Minimum Height
		if ( isset( $min_image_height ) && $min_image_height > 0 ) {
			if ( $image_height < $min_image_height ) {
				$file['error'] = $min_height_message;

				return $file;
			}
		}

		return $file;

	} // filter_upload()

	/**
	 * Hook into the product search and search by product meta.
	 *
	 * @since 1.5.0
	 */
	public function product_search_args( $args ) {

		if ( $args['post_type'] === 'product' ) {

			$search = $args['s'];
			// If there is no search return the args
			if ( empty( $search ) ) {
				return $args;
			}

			$meta_query = $this->get_product_search_meta_keys( $search );
			// Reset the simple query
			unset( $args['s'] );

			if ( count( $meta_query ) > 1 ) {
				$meta_query['relation'] = 'OR';
			}

			$args['_wcv_product_search'] = $search;
			$args['meta_query']          = $meta_query;

		}

		return $args;

	} // product_search_args()

	/**
	 * Add product meta to the product search defaul sku
	 *
	 * @since 1.5.0
	 */
	public function get_product_search_meta_keys( $search ) {

		$meta_query = array();

		$meta_query[] = array(
			'key'     => '_sku',
			'value'   => $search,
			'compare' => 'LIKE',
		);

		return apply_filters( 'wcvendors_pro_table_product_search_meta_keys', $meta_query );

	} // get_product_search_meta_keys()

	/**
	 * Prepare downloads for save.
	 *
	 * @param array $file_names
	 * @param array $file_urls
	 * @param array $file_hashes
	 *
	 * @return array
	 */
	private static function prepare_downloads( $file_names, $file_urls, $file_hashes ) {
		$downloads = array();

		if ( ! empty( $file_urls ) ) {
			$file_url_size = sizeof( $file_urls );

			for ( $i = 0; $i < $file_url_size; $i ++ ) {
				if ( ! empty( $file_urls[ $i ] ) ) {
					$downloads[] = array(
						'name'        => wc_clean( $file_names[ $i ] ),
						'file'        => wp_unslash( trim( $file_urls[ $i ] ) ),
						'download_id' => wc_clean( $file_hashes[ $i ] ),
					);
				}
			}
		}

		return $downloads;
	}

	/**
	 * @param array      $actions Row actions.
	 * @param WC_Product $product Product object.
	 *
	 * @since 1.7.0
	 */
	public function maybe_disable_actions( $actions, $product ) {
		if ( 'yes' === $product->get_meta( '_disable_vendor_edit' ) ) {
			unset( $actions['edit'] );
		}
		return $actions;
	}

	/**
	 * Redirect vendor to the products list if edit is disabled.
	 *
	 * @param bool   $disabled     Is page disabled.
	 * @param string $current_page Current page id.
	 */
	public function maybe_disable_page( $disabled, $current_page ) {
		if ( 'product' !== $current_page ) {
			return $disabled;
		}

		$product_id = get_query_var( 'object_id' );
		$product    = wc_get_product( $product_id );

		if ( ( $product instanceof WC_Product ) && 'yes' === $product->get_meta( '_disable_vendor_edit' ) ) {
			return true;
		}

		return $disabled;
	}

	/**
	 * Delete rating data .
	 *
	 * @param int $id     Product id.
	 */
	public function delete_rating_data( $id ) {
		global $wpdb;
		if ( 'yes' == get_option( 'wcvendors_delete_ratings_on_deleting_product', 'no' ) ) {
			$wpdb->delete( $this->table_name, array( 'product_id' => $id ) );
		}
	} // delete_rating_data

	/**
	 * Filter the allowed html tags for products.
	 *
	 * @param array $allowed_html_tags List of currently allowed html tags.
	 * @param array $context The context in which the html tags are used, usually a post type.
	 * @return void
	 * @version 1.7.5
	 * @since   1.7.5
	 */
	public function allowed_html_tags( $allowed_html_tags, $context ) {
		if ( 'post' === $context ) {
			$allowed_html_tags['iframe'] = array(
				'src'             => true,
				'height'          => true,
				'width'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
				'name'            => true,
				'srcdoc'          => true,
				'importance'      => true,
				'loading'         => true,
				'referrerpolicy'  => array(
					'no-referrer'                     => true,
					'no-referrer-when-upgrade'        => true,
					'origin'                          => true,
					'same-origin'                     => true,
					'strict-origin'                   => true,
					'strict-origin-when-cross-origin' => true,
					'unsafe-url'                      => false,
				),
			);
		}

		return apply_filters( 'wcv_allowed_html_tags', $allowed_html_tags, $context );
	}
}
