<?php defined( 'ABSPATH' ) || exit;

global $wpdb;

echo '<p>' . __( 'Applying update 2.0.17.', 'woonet' ) . '</p>';

$network_site_ids = WOO_MSTORE_functions::get_active_woocommerce_blog_ids();
foreach ( $network_site_ids as $network_site_id ) {
	switch_to_blog( $network_site_id );

	$query = "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE term_id IN (%s)";
	$terms_mapping = get_option( 'terms_mapping', array() );
		foreach ( $terms_mapping as $master_product_blog_id => $blog_terms_mapping ) {
			if ( is_array( $blog_terms_mapping ) ) {
				$term_ids = $wpdb->get_col( sprintf(
					$query,
					implode( ',', $blog_terms_mapping )
				) );
				$terms_mapping[ $master_product_blog_id ] = array_intersect( $blog_terms_mapping, $term_ids );
			} else {
				// unused old terms mapping
				unset( $terms_mapping[ $master_product_blog_id ] );
			}
		}
	update_option( 'terms_mapping', $terms_mapping, false );

	$query = "SELECT ID FROM {$wpdb->posts} WHERE ID IN (%s)";
	$images_mapping = get_option( 'images_mapping', array() );
		foreach ( $images_mapping as $master_product_blog_id => $blog_images_mapping ) {
			if ( is_array( $blog_images_mapping ) ) {
				$image_ids = $wpdb->get_col( sprintf(
					$query,
					implode( ',', $blog_images_mapping )
				) );
				$images_mapping[ $master_product_blog_id ] = array_intersect( $blog_images_mapping, $image_ids );
			} else {
				// unused old images mapping
				unset( $images_mapping[ $master_product_blog_id ] );
			}
		}
	update_option( 'images_mapping', $images_mapping, false );

	restore_current_blog();
}
