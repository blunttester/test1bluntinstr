<?php

// Namespace declaration
namespace MJJ\WooProductAttributeTab;

// Exit if accessed directly
defined('ABSPATH') or die();

/**
 * A generic reusable singleton trait.
 */
trait Singleton {

    /**
     * The static singleton instance of the class.
     */
    protected static $instance;

    /**
     * Function to retrieve the singleton instance of this class.
     * The instance is lazily created if it does not already exist.
     *
     * @return Singleton The existing or newly created instance of the invoked class.
     */
    public static function instance() {
        $class = get_called_class();
        if (!isset(static::$instance)) {
            static::$instance = new $class();
        }
        return static::$instance;
    }

    /**
     * Protected empty constructor. Override as needed.
     */
    protected function __construct() {}
}

?>