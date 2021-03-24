<?php

/**
 * Global images hooks and common functions.
 */
class WOO_MSTORE_Global_Image {
	public function __construct() {
		add_action( 'wp_get_attachment_image_src', array( $this, 'get_image' ), PHP_INT_MAX, 4 );
	}

	public function get_image( $image, $attachment_id, $size, $icon ) {
		// error_log( var_export($GLOBALS, true ));
		$parent_image_url = get_post_meta( $attachment_id, '__woonet_parent_image_url', true );

		if ( $parent_image_url ) {
			$image[0] = $parent_image_url;
		}

		return $image;
	}
}

$GLOBALS['WOO_MSTORE_Global_Images'] = new WOO_MSTORE_Global_Image();
