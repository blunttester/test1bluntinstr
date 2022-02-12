<?php
/**
 * Admin View: Notice - Install
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated wcvendors-message wc-connect is-dismissible">
	<p><?php _e( '<strong>WC Vendors Pro Update Required.</strong> &#8211; We need to upgrade your configuration to the latest version.', 'wcvendors-pro' ); ?></p>
	<p class="submit"><a class="wcvendors-pro-update-now button-primary"
						 href="<?php echo esc_url( add_query_arg( 'do_update_wcvendors_pro', 'true', admin_url( 'admin.php?page=wcv-settings' ) ) ); ?>"
						 class="button-primary"><?php _e( 'Run the update', 'wcvendors-pro' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery('.wcvendors-pro-update-now').click('click', function () {
		return window.confirm('<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'wcvendors-pro' ) ); ?>'); // jshint ignore:line
	});
</script>
