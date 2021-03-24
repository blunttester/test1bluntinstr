<?php
/**
 * Admin View: Notice - Install
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated wcvendors-message wc-connect">
	<p><?php _e( '<strong>Welcome to WC Vendors Pro</strong> &#8211; You&lsquo;re almost ready to enhance your marketplace', 'wcvendors-pro' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wcvendors-pro-setup' ) ); ?>"
						 class="button-primary"><?php _e( 'Run the setup wizard', 'wcvendors-pro' ); ?></a> <a
				class="button-secondary skip"
				href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcvendors-pro-hide-notice', 'install' ), 'wcvendors_pro_hide_notices_nonce', '_wcvendors_pro_notice_nonce' ) ); ?>"><?php _e( 'Skip setup', 'wcvendors-pro' ); ?></a>
	</p>
</div>
