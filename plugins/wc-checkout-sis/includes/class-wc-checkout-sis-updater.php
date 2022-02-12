<?php

/**
 * Plugin update functions
 */
class WC_Checkout_Sis_Updater {
  /**
   * Constructor
   */
  public function __construct() {
  }

  public function run_updates() {
    if ( get_option( 'wc_checkout_sis_updated_200', 'no' ) !== 'yes' ) {
      $this->run_200();
    }
  }

  /**
   * Run 2.0.0 update
   *
   * In 2.0.0 separate settings page was merged with payment method settings
   */
  private function run_200() {
    // Get settings from separate settings page
    $commission_msg = get_option( 'wc_checkout_sis_commission_msg', null );
    $commission_user_id = get_option( 'wc_checkout_sis_commission_user_id', null );
    $shipping_user_id = get_option( 'wc_checkout_sis_shipping_user_id', null );

    if ( $commission_msg || $commission_user_id || $shipping_user_id ) {
      // Get commission merchant ID from user ID
      $commission_merchant_id = null;
      if ( $commission_user_id && get_user_by( 'ID', $commission_user_id ) ) {
        $commission_merchant_id = get_the_author_meta( 'wc_checkout_sis_merchant_id', $commission_user_id );
      }

      // Get shipping merchant ID from user ID
      $shipping_merchant_id = null;
      if ( $shipping_user_id && get_user_by( 'ID', $shipping_user_id ) ) {
        $shipping_merchant_id = get_the_author_meta( 'wc_checkout_sis_merchant_id', $shipping_user_id );
      }

      // Update settings
      $options = get_option( 'woocommerce_checkout_sis_settings', false );
      if ( $options && is_array( $options ) ) {
        $options['commission_msg'] = $commission_msg;
        $options['commission_merchant_id'] = $commission_merchant_id;
        $options['shipping_merchant_id'] = $shipping_merchant_id;

        update_option( 'woocommerce_checkout_sis_settings', $options );

        // Delete legacy options
        $fields = array(
          'wc_checkout_sis_commission_msg', 'wc_checkout_sis_commission_user_id',
          'wc_checkout_sis_shipping_user_id', 'wc_checkout_sis_api_key',
          'wc_checkout_sis_api_password'
        );
        foreach ( $fields as $field ) {
          delete_option( $field );
        }
      }
    }

    // Mark update as done
    update_option( 'wc_checkout_sis_updated_200', 'yes' );
  }
}

add_action( 'init', 'wc_checkout_sis_updater', 1000 );
function wc_checkout_sis_updater() {
  // WooCommerce not activated, abort
  if ( ! defined( 'WC_VERSION' ) ) {
    return;
  }

  $updater = new WC_Checkout_Sis_Updater();
  $updater->run_updates();
}
