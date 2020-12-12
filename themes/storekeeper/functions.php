<?php
/*This file is part of storekeeper child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/

function storekeeper_enqueue_child_styles() {
    $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    $parent_style = 'storecommerce-style';

    $fonts_url = 'https://fonts.googleapis.com/css?family=Cabin:400,400italic,500,600,700';
    wp_enqueue_style('storekeeper-google-fonts', $fonts_url, array(), null);
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/bootstrap/css/bootstrap' . $min . '.css');
    wp_enqueue_style('owl-carousel', get_template_directory_uri() . '/assets/owl-carousel-v2/assets/owl.carousel' . $min . '.css');
    wp_enqueue_style('owl-theme-default', get_template_directory_uri() . '/assets/owl-carousel-v2/assets/owl.theme.default.css');
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style(
        'storekeeper-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'bootstrap', $parent_style ),
        wp_get_theme()->get('Version') );


}
add_action( 'wp_enqueue_scripts', 'storekeeper_enqueue_child_styles' );
