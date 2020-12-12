<?php

/*
Plugin Name: WooCommerce Checkout Shop in Shop
Plugin URI:  http://markup.fi
Description: Checkout.fi Shop in Shop payment gateway integration for WooCommerce.
Version:     2.0.4
Author:      Lauri Karisola / markup.fi
Author URI:  http://markup.fi
Text Domain: wc-checkout-sis
Domain Path: /languages
WC requires at least: 3.0.0
WC tested up to: 4.4.1
*/

/**
 * Prevent direct access to the script.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version
 */
if ( ! defined( 'WC_CHECKOUT_SIS_VERSION' ) ) {
	define( 'WC_CHECKOUT_SIS_VERSION', '2.0.4' );
}

/**
 * Plugin update checker
 */
require_once 'plugin-update-checker/plugin-update-checker.php';
$update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://markup.fi/products/35/metadata.json?mac=06C84802CD737F1B55CD4858375EE8F9',
	__FILE__,
	'wc-checkout-sis'
);

/**
 * Add Checkout to WooCommerce payment gateways.
 *
 * @param array $methods
 * @return array $methods
 */
add_filter( 'woocommerce_payment_gateways', 'add_wc_checkout_sis' );
function add_wc_checkout_sis( $methods ) {
	$methods[] = 'WC_Gateway_Checkout_Sis';
	return $methods;
}

/**
 * Load plugin textdomain
 *
 * @return void
 */
add_action( 'plugins_loaded', 'wc_checkout_sis_load_textdomain' );
function wc_checkout_sis_load_textdomain() {
  load_plugin_textdomain( 'wc-checkout-sis', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * Load payment gateway class.
 */
add_action( 'plugins_loaded', 'init_wc_gateway_checkout_sis' );
function init_wc_gateway_checkout_sis() {
	if ( defined( 'WC_VERSION' ) ) {
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wc-gateway-checkout-sis.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wc-checkout-sis-updater.php' );
	}
}

/**
 * Load stylesheets and scripts
 */
add_action( 'wp_enqueue_scripts', 'wc_checkout_sis_enqueue_scripts' );
function wc_checkout_sis_enqueue_scripts() {
	wp_enqueue_style( 'wc-checkout-sis-css', plugin_dir_url( __FILE__ ) . 'assets/css/frontend/wc-checkout-sis.css', array(), WC_CHECKOUT_SIS_VERSION );
	wp_enqueue_script( 'wc-checkout-sis-js', plugin_dir_url( __FILE__ ) . 'assets/js/frontend/wc-checkout-sis.js', array( 'jquery' ), WC_CHECKOUT_SIS_VERSION );
}

/**
 * Add settings link to the plugins page.
 */
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wc_checkout_sis_add_settings_link' );
function wc_checkout_sis_add_settings_link( $links ) {
	$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=checkout_sis' );
	$link = '<a href="' . $url . '">' . __( 'Settings' ) . '</a>';

  return array_merge( array($link), $links );
}

function wc_checkout_sis_extra_user_profile_fields( $user ) {
	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}

	?>
	  <h3><?php _e( 'Checkout Shop in Shop', 'wc-checkout-sis' ); ?></h3>
	  <table class="form-table">
	    <tr>
	      <th><label for="wc_checkout_sis_merchant_id"><?php _e( 'Merchant ID', 'wc-checkout-sis' ); ?></label></th>
	      <td>
	        <input type="text" name="wc_checkout_sis_merchant_id" id="wc_checkout_sis_merchant_id" class="regular-text"
	            value="<?php echo esc_attr( get_the_author_meta( 'wc_checkout_sis_merchant_id', $user->ID ) ); ?>" disabled="disabled" />
					<a href="#" id="wc-checkout-sis-edit-merchant-id">Muokkaa</a>
	    	</td>
	    </tr>
			<tr>
	      <th><label for="wc_checkout_sis_merchant_commission_type"><?php _e( 'Commission type', 'wc-checkout-sis' ); ?></label></th>
	      <td>
					<select name="wc_checkout_sis_merchant_commission_type">
						<option value="percentage" <?php selected( get_the_author_meta( 'wc_checkout_sis_merchant_commission_type', $user->ID ), 'percentage' ); ?>>
							<?php _e( 'Percentage', 'wc-checkout-sis' ) ?>
						</option>
						<option value="fixed" <?php selected( get_the_author_meta( 'wc_checkout_sis_merchant_commission_type', $user->ID ), 'fixed' ); ?>>
							<?php _e( 'Fixed, per line item', 'wc-checkout-sis' ) ?>
						</option>
						<option value="fixed_product" <?php selected( get_the_author_meta( 'wc_checkout_sis_merchant_commission_type', $user->ID ), 'fixed_product' ); ?>>
							<?php _e( 'Fixed, per product', 'wc-checkout-sis' ) ?>
						</option>
					</select>
	    	</td>
	    </tr>
			<tr>
	      <th><label for="wc_checkout_sis_merchant_commission_value"><?php _e( 'Commission', 'wc-checkout-sis' ); ?></label></th>
	      <td>
	        <input type="text" name="wc_checkout_sis_merchant_commission_value" id="wc_checkout_sis_merchant_commission_value" class="regular-text"
	            value="<?php echo esc_attr( get_the_author_meta( 'wc_checkout_sis_merchant_commission_value', $user->ID ) ); ?>" />
	    	</td>
			</tr>
	  </table>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#wc-checkout-sis-edit-merchant-id').click(function(e) {
					e.preventDefault();
					$('input[name="wc_checkout_sis_merchant_id"]').prop('disabled', false);
					$(this).remove();
				});
			});
		</script>
	<?php
}
add_action( 'show_user_profile', 'wc_checkout_sis_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'wc_checkout_sis_extra_user_profile_fields' );

function wc_checkout_sis_save_extra_user_profile_fields( $user_id ) {
	if ( ! current_user_can( 'administrator' ) ) {
		return true;
	}

  $saved = false;

  if ( current_user_can( 'edit_user', $user_id ) ) {
    update_user_meta( $user_id, 'wc_checkout_sis_merchant_commission_type', $_POST['wc_checkout_sis_merchant_commission_type'] );
    update_user_meta( $user_id, 'wc_checkout_sis_merchant_commission_value', $_POST['wc_checkout_sis_merchant_commission_value'] );

		if ( isset( $_POST['wc_checkout_sis_merchant_id'] ) ) {
			update_user_meta( $user_id, 'wc_checkout_sis_merchant_id', $_POST['wc_checkout_sis_merchant_id'] );
		}

    $saved = true;
  }

  return true;
}
add_action( 'personal_options_update', 'wc_checkout_sis_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'wc_checkout_sis_save_extra_user_profile_fields' );