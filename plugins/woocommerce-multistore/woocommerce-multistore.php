<?php
/**
 * WooMultistore (formerly WooCommerce Multistore)
 *
 * @package     WooMultistore
 * @author      Lykke Media AS
 * @copyright   2020 Lykke Media AS
 *
 * @wordpress-plugin
 * Plugin Name: WooMultistore
 * Description: The WooMultistore (formerly WooCommerce Multistore) plugin can be used to manage features on unlimited WooCommerce stores from one single WordPress admin.
 * Author: Lykke Media AS
 * Author URI: https://woomultistore.com/
 * Version: 4.1.5
 * Requires at least: 5.3.0
 * Tested up to: 5.5.1
 *
 * WC requires at least: 3.6.0
 * WC tested up to: 4.6.1
 * Network: true
 **/

defined( 'ABSPATH' ) || exit;

if ( is_multisite() ) {
	/**
	 * Entry script for the multisite version
	 */
	require_once __DIR__ . '/multisite-entry.php';
} else {
	/**
	 * Entry script for the single site version
	 */
	require_once __DIR__ . '/single-site/single-site-entry.php';
}
