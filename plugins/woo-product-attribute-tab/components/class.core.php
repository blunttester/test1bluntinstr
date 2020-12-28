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
require_once('class.util.php');
require_once('class.meta.php');
require_once('class.tabs.php');
require_once('class.lang.php');

/**
 * Core class of this plugin that initializes the plugin framework and loads dependencies.
 */
class Core {

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
        $this->load();
    }

    /**
     * Loads the translation domain for this plugin.
     */
    public function i18n() {
        load_plugin_textdomain('woo-product-attribute-tab', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Loads the plugin components if necessary.
     */
    private function load() {
        if (Util::is_woocommerce_active()) {
            Meta::instance();
            Tabs::instance();
            Lang::instance();
        }
    }
}

?>