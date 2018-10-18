<?php
/**
 * WooCommerce Plugin Compatibility
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to http://www.skyverge.com
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2013, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'SV_WC_Gateway_Checkout_Fi_Plugin_Compatibility' ) ) :

/**
 * WooCommerce Compatibility Utility Class
 *
 * The unfortunate purpose of this class is to provide a single point of
 * compatibility functions for dealing with supporting multiple versions
 * of WooCommerce.
 *
 * The recommended procedure is to rename this file/class, replacing "my plugin"
 * with the particular plugin name, so as to avoid clashes between plugins.
 * Over time we expect to remove methods from this class, using the current
 * ones directly, as support for older versions of WooCommerce is dropped.
 *
 * Current Compatibility: 2.0.x - 2.1
 *
 * @version 1.0
 */
class SV_WC_Gateway_Checkout_Fi_Plugin_Compatibility {


	/**
	 * Get the WC Order instance for a given order ID or order post
	 *
	 * Introduced in WC 2.2 as part of the Order Factory so the 2.1 version is
	 * not an exact replacement.
	 *
	 * If no param is passed, it will use the global post. Otherwise pass an
	 * the order post ID or post object.
	 *
	 * @since 2.2.0-1
	 * @param bool|int|string|\WP_Post $the_order
	 * @return bool|\WC_Order
	 */
	public static function wc_get_order( $the_order = false ) {

		if ( self::is_wc_version_gte_2_2() ) {

			return wc_get_order( $the_order );

		} else {

			global $post;

			if ( false === $the_order ) {

				$order_id = $post->ID;

			} elseif ( $the_order instanceof WP_Post ) {

				$order_id = $the_order->ID;

			} elseif ( is_numeric( $the_order ) ) {

				$order_id = $the_order;
			}

			return new WC_Order( $order_id );
		}
	}


	/**
	 * Helper method to get the version of the currently installed WooCommerce
	 *
	 * @since 2.2.0-1
	 * @return string woocommerce version number or null
	 */
	private static function get_wc_version() {

		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}


	/**
	 * Returns true if the installed version of WooCommerce is 2.2 or greater
	 *
	 * @since 2.2.0
	 * @return boolean true if the installed version of WooCommerce is 2.2 or greater
	 */
	public static function is_wc_version_gte_2_2() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.2', '>=' );
	}

}

endif; // Class exists check
