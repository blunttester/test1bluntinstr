<?php
/**
 * Admin View: Notice - Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated wcvendors-message wc-connect wcvendors-message-success">
	<a class="wcvendors-message-close notice-dismiss"
	   href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcvendors-pro-hide-notice', 'update', remove_query_arg( 'do_update_wcvendors_pro' ) ), 'wcvendors_pro_hide_notices_nonce', '_wcvendors_pro_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'wcvendors-pro' ); ?></a>
	<p><?php _e( 'WC Vendors Pro data update complete. Thank you for updating to the latest version!', 'wcvendors-pro' ); ?></p>
</div>
