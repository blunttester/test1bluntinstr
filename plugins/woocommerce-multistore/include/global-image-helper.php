<?php

class WOO_MSTORE_Global_Image_Helper {

    /**
     * Instance.
     */
    private static $_instance = null;

    /**
     * Default image URL.
     */
    private $default_image = null;

    /**
     * Prevent instantiation
     */
    private function __construct() {
        if ( is_multisite() ) {
            $this->default_image = WOO_MSTORE_PATH . '/assets/images/woomultistore-global-image.png';
        } else {
            $this->default_image = dirname( WOO_MSTORE_PATH ) . '/assets/images/woomultistore-global-image.png';
        }
    }

    /**
     * Return Instance
     *
     * @return object WOO_MSTORE_GLOBAL_IMAGES
     */
    public static function instance() {
        if ( self::$_instance == null ) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Get default image.
     *
     * @return void
     */
    public function get_default_image_id() {
        global $wpdb;

        if ( $cached_image = $this->get_transient() ) {
            return (int) $cached_image;
        }

        $has_image = $wpdb->get_var("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key='woonet_global_image_id'");

        if ( $has_image ) {
            $this->set_transient($has_image);
            return $has_image;
        }

        return $this->set_default_image();
    }

    /**
     * Set default image.
     *
     * @return void
     */
    public function set_default_image() {
        $filetype = wp_check_filetype( basename( $this->default_image ), null );

        $attachment = array(
            //'guid'           => basename( $this->default_image ), 
            'post_mime_type' => $filetype['type'],
            'post_title'     => 'WooMultistore Global Image',
            'post_content'   => 'Do not remove this image. If you have global image sync enabled in settings, this image is copied in every child site.',
            'post_status'    => 'inherit'
        );
        
        // Insert the attachment.
        $attachment_id = wp_insert_attachment( $attachment, $this->default_image );

        if ( $attachment_id ) {
            update_post_meta($attachment_id, 'woonet_global_image_id', $attachment_id);
            return $attachment_id;
        }

        return null;
    }

    /**
     * Get transient
     */
     private function get_transient() {
         if ( is_multisite() ) {
            return get_site_transient('woonet_global_image_id');
         } else {
            return get_transient('woonet_global_image_id');
         }
     }

     /**
     * Get transient
     */
    private function set_transient( $value ) {
        if ( is_multisite() ) {
           return set_site_transient('woonet_global_image_id', $value , 5 * 60 );
        } else {
           return set_transient('woonet_global_image_id', $value , 5 * 60 );
        }
    }
}
