<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated wcvendors-message wc-connect">
	<p><strong><?php _e( 'WC Vendors Pro data update', 'wcvendors-pro' ); ?></strong>
		&#8211; <?php _e( 'Your database is being updated in the background. ', 'wcvendors-pro' ); ?> <a
				href="<?php echo esc_url( add_query_arg( 'force_update_wcvendors_pro', 'true', admin_url( 'admin.php?page=wcv-settings' ) ) ); ?>"><?php _e( 'Taking a while? Click here to run it now.', 'wcvendors-pro' ); ?></a>
	</p>
</div>
