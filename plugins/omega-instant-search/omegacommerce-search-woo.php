<?php
/*
Plugin Name: Omega Instant Search For WooCommerce
Description: Omega Instant Search delivers the right search results for your customers, thus, driving more sales!
Version: 2.0.9
Author: Omega Search
Author URI: https://omegacommerce.com
License: MIT

The MIT License (MIT)

Copyright (c) 2017 Omega Commerce LLC https://omegacommerce.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
//3rd party plugins
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

spl_autoload_register('omagecommerce_autoloader');
function omagecommerce_autoloader($class_name)
{
    if (false !== strpos($class_name, 'OmegaCommerce')) {
        $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $class_name = str_replace('OmegaCommerce\\', '', $class_name);
        $class_file = str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
        require_once $classes_dir . $class_file;
    }
}


define('WP_OMEGA_COMMERCE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WP_OMEGA_COMMERCE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OMEGA_COMMERCE_SEARCH_FILE', __FILE__);

$omegaManager = new \OmegaCommerce\Manager("2.0.9");

//flush_rewrite_rules();
