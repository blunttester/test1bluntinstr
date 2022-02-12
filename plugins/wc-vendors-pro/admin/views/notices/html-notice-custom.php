<?php
/**
 * Admin View: Custom Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated wcvendors-message">
	<a class="wcvendors-message-close notice-dismiss"
	   href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcvendors-pro-hide-notice', $notice ), 'wcvendors_pro_hide_notices_nonce', '_wcvendors_pro_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'wcvendors-pro' ); ?></a>
	<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>
