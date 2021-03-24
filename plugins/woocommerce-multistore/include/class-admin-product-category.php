<?php

final class WOO_MSTORE_admin_product_category {
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ), 1 );
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

	public function init() {
		$licence = new WOO_MSTORE_licence();
		if ( ! $licence->licence_key_verify() ) {
			return;
		}

		add_action( 'created_product_cat', array( $this, 'add_term_creation_timestamp' ) );
		add_action( 'edited_product_cat', array( $this, 'republish_category_changes' ) );
	}

	/**
	 * @param int $term_id Term ID.
	 */
	public function add_term_creation_timestamp( $term_id ) {
		update_term_meta( $term_id, '_timestamp', current_time( 'timestamp' ) );
		update_term_meta( $term_id, '_timestamp_gmt', current_time( 'timestamp', true ) );
	}

	/**
	 * @param int $master_term_id Term ID.
	 */
	public function republish_category_changes( $master_term_id, $blog_ids = null ) {
		global $wpdb, $WOO_MSTORE;

		if ( doing_action( 'wp_ajax_inline-save-tax' ) ) {
			return;
		}

		$master_term = $this->get_term_data( $master_term_id );
		if ( empty( $master_term ) ) {
			return;
		}

		$master_term_image = $this->get_term_image( $master_term['thumbnail_id'] );

		$master_blog_id = get_current_blog_id();

		if ( is_array( $blog_ids ) ) {
			$blog_ids = array_filter( $blog_ids, 'intval');
		} else {
			$blog_ids = $WOO_MSTORE->functions->get_active_woocommerce_blog_ids();
		}
		foreach ( $blog_ids as $slave_blog_id ) {
			if ( $master_blog_id == $slave_blog_id ) {
				continue;
			}

			switch_to_blog( $slave_blog_id );

			if ( $slave_term_id = $this->get_mapped_term_id( $master_blog_id, $master_term ) ) {
				$slave_term = $this->get_term_data( $slave_term_id );

				// if ( isset( $slave_term['timestamp'] ) && $master_term['timestamp'] < $slave_term['timestamp'] ) {
				if ( isset( $slave_term['timestamp'] ) ) {
					// update slave term data
					$wpdb->update(
						$wpdb->term_taxonomy,
						array( 'description' => $master_term['description'] ),
						array( 'term_id' => $slave_term_id, 'taxonomy' => 'product_cat' )
					);

					if ( $slave_term_image_id = $this->get_mapped_term_image_id( $master_blog_id, $master_term_image ) ) {
						if ( $slave_term_image = $this->get_term_image( $slave_term_image_id ) ) {
							if ( date_create( $master_term_image['post_date'] ) < date_create( $slave_term_image['post_date'] ) ) {
								$wpdb->update(
									$wpdb->posts,
									array( 'post_content' => $master_term_image['post_content'], 'post_excerpt' => $master_term_image['post_excerpt'] ),
									array( 'ID' => $slave_term_image_id ),
									array( '%s', '%s' ),
									array( '%d' )
								);
								update_post_meta( $slave_term_image_id, '_wp_attachment_image_alt', $master_term_image['image_alt'] );
							}
						}
					}

					update_term_meta( $slave_term_id, 'thumbnail_id', $slave_term_image_id );
				}
			}

			restore_current_blog();
		}
	}

	private function get_term_data( $term_id ) {
		$term_data = get_term( $term_id, 'product_cat', ARRAY_A );

		$term_data['thumbnail_id'] = get_term_meta( $term_id, 'thumbnail_id', true );
		$term_data['timestamp'] = get_term_meta( $term_id, '_timestamp', true );

		return $term_data;
	}

	private function get_mapped_term_id( $master_blog_id, $master_term ) {
		// get mapped terms
		$terms_mapping = get_option( 'terms_mapping', array() );

		// if term id is mapped
		if ( isset( $terms_mapping[ $master_blog_id ][ $master_term['term_id'] ] ) ) {
			$mapped_term_id = intval( $terms_mapping[ $master_blog_id ][ $master_term['term_id'] ] );
		} else {
			$mapped_term_id = null;
		}

		return $mapped_term_id;
	}

	private function get_term_image( $thumbnail_id ) {
		$thumbnail_id = intval( $thumbnail_id );

		if ( $term_image = get_post( $thumbnail_id, ARRAY_A ) ) {
			$term_image['image_alt'] = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
			$term_image['attached_file'] = get_post_meta( $thumbnail_id, '_wp_attached_file', true );
			$term_image['master_upload_dir'] = wp_upload_dir();
		}

		return $term_image;
	}

	private function get_mapped_term_image_id( $master_blog_id, $master_term_image ) {
		// get mapped images
		$images_mapping = get_option( 'images_mapping', array() );

		if ( isset( $master_term_image['ID'] ) && isset( $images_mapping[ $master_blog_id ][ $master_term_image['ID'] ] ) ) {
			$slave_term_image_id = $images_mapping[ $master_blog_id ][ $master_term_image['ID'] ];
		} else {
			if ( empty( $master_term_image['attached_file'] ) ) {
				return null;
			}

			// get master image full name
			$master_attached_file = $master_term_image['master_upload_dir']['basedir'] . DIRECTORY_SEPARATOR . $master_term_image['attached_file'];
			if ( ! is_readable( $master_attached_file ) ) {
				return null;
			}

			// copy master image to slave image
			$file_name = basename( $master_attached_file );
			$upload    = wp_upload_bits( $file_name, '', file_get_contents( $master_attached_file ) );
			if ( $upload['error'] ) {
				return null;
			}

			$slave_term_image_id = wc_rest_set_uploaded_image_as_attachment( $upload );

			$images_mapping[ $master_blog_id ][ $master_term_image['ID'] ] = $slave_term_image_id;
			update_option( 'images_mapping', $images_mapping, false );
		}

		return $slave_term_image_id;
	}
}
