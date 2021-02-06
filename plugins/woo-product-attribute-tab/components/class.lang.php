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
 * Dependencies
 */
require_once('trait.singleton.php');

/**
 * Language class of this plugin that initializes the plugin for internationalization.
 * Currently supported are Polylang and WPML (via String Translations).
 * Note that term translation is already correctly handled, this class only adds support for attribute meta translation.
 */
class Lang {

    /**
     * The attribute translation name template.
     * 
     * @var string
     */
    private $attribute_translation_name_template = 'Attribute tab title for %s';


    /**
     * The attribute translation domain.
     * 
     * @var string
     */
    private $attribute_translation_domain = 'woo-product-attribute-tab';

    /**
     * Use Singleton trait to disallow multiple instances of this class.
     * You may also fetch the instance of this class to remove registered filter and action hooks.
     */
    use Singleton;

    /**
     * Constructs a new instance of this class and registers the required actions and filters.
     */
    protected function __construct() {
        add_action('init', array($this, 'i18n'));
        add_filter('woocommerce_product_attribute_tab_meta', array($this, 'translate'), 10, 2);
    }

    /**
     * Initializes the translation for this plugin.
     */
    public function i18n() {
        if (function_exists('wc_get_attribute_taxonomies')) {
            $attributes = wc_get_attribute_taxonomies();
            foreach ($attributes as $attribute) {
                $tab_title = $attribute->attribute_tab_title;
                if ($tab_title) {
                    $domain = $this->get_translation_domain();
                    $name = $this->get_translation_name($attribute->attribute_name);
                    $this->register_string($domain, $name, $tab_title);
                }
            }
        }
    }

    /**
     * Register a string for translation with Polylang and/or WPML.
     * 
     * @param string $context The name of the plugin, in a human readable format
     * @param string $name The name of the string which helps the user (or translator) understand what’s being translated.
     * @param string $value The string that needs to be translated.
     */
    public function register_string($domain, $name, $value) {
        if (function_exists('pll_register_string')) {
            pll_register_string($name, $value, $domain);
        } else if (function_exists('icl_register_string')) {
            icl_register_string($domain, $name, $value);
        }
    }

    /**
     * Translate the given meta using Polylang/WPML.
     */
    public function translate($meta, $attribute_id) {
        if (function_exists('pll__')) {
            $meta['attribute_tab_title'] = pll__($meta['attribute_tab_title']);
        } else if (function_exists('icl_t')) {
            $domain = $this->get_translation_domain();
            $name = $this->get_translation_name($meta['attribute_name']);
            $meta['attribute_tab_title'] = icl_t($domain, $name, $meta['attribute_tab_title']);
        }
        return $meta;
    }

    /**
     * Get the attribute translation name.
     * 
     * @param  string $attribute_name The attribute name.
     * @return string The attribute translation name.
     */
    private function get_translation_name($attribute_name) {
        return sprintf($this->attribute_translation_name_template, $attribute_name);
    }

    /**
     * Get the attribute translation domain.
     * 
     * @return string The attribute translation domain.
     */
    private function get_translation_domain() {
        return $this->attribute_translation_domain;
    }
}

?>