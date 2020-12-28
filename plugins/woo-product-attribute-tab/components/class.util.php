<?php

/**
 * Namespace declaration
 */
namespace MJJ\WooProductAttributeTab;

/**
 * Exit if accessed directly
 */
defined('ABSPATH') or die();

/**
 * Utility class that provides convenience functions in a static manner.
 */
class Util {

    /**
     * Loads the given template if it exists. Use `locate_template` first to check if any templates exists within the current theme,
     * Loads the default plugin template if none was found.
     *
     * @param  string $template_name The template to load.
     * @param  array  $args Arguments to pass to the template, the array is extracted before the template is included.
     */
    public static function load_template($template_name, $args = array()) {
        $template = locate_template("templates/woo-product-attribute-tab/{$template_name}") ?: dirname(plugin_dir_path(__FILE__)) . "/templates/{$template_name}";
        if (file_exists($template)) {
            extract($args);
            include ($template);
        }
    }

    /**
     * Checks if WooCommerce is active with respect to site-wide plugins.
     *
     * @return bool true if WooCommerce is active, false otherwise.
     */
    public static function is_woocommerce_active() {
        return self::is_plugin_active('woocommerce');
    }

    /**
     * Checks if the given plugin is active with respect to site-wide plugins.
     * This function does not use the standard WordPress function `is_plugin_active` since it is loaded too late.
     * With the below approach we can find active plugins early on in the loading process.
     *
     * @param  string $slug The name/slug of the plugin to test.
     * @return bool true if A plugin with the given slug is active, false otherwise.
     */
    public static function is_plugin_active($slug) {
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        $plugin = "{$slug}/{$slug}.php";
        return in_array($plugin, $active_plugins) || array_key_exists($plugin, $active_plugins);
    }


    /**
     * Gets the current product.
     *
     * @return \WC_Product The current product or `null`;
     */
    public static function get_product() {
        $product = null;
        if (function_exists('wc_get_product')) {
            $product = wc_get_product();
            if (!$product) {
                $product = wc_get_product(get_the_ID());
            }
        }
        if (!$product && function_exists('get_product')) {
            $product = get_product();
        }
        if (!$product && class_exists('WC_Product')) {
            $product = new \WC_Product(get_the_ID());
        }
        return $product;
    }

    /**
     * Private constructor to avoid instances of this utility class.
     */
    private function __construct() {}
}

?>