<?php
/**
 * Function describe for webstore 
 * 
 * @package webstore
 */

add_action( 'wp_enqueue_scripts', 'webstore_enqueue_styles', 999 );
function webstore_enqueue_styles() {
  $parent_style = 'webstore-parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'webstore-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style )
    );
}


function webstore_theme_setup() {
    
    load_child_theme_textdomain( 'webstore', get_stylesheet_directory() . '/languages' );
    
    add_image_size( 'maxstore-slider', 1140, 488, true );
    		    
}
add_action( 'after_setup_theme', 'webstore_theme_setup' );

add_action( 'customize_register', 'webstore_custom_remove', 100);

if ( is_admin() ) {
	include_once(trailingslashit( get_template_directory() ) . 'lib/welcome/welcome-screen.php');
} 



