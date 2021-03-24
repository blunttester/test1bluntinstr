<?php
/**
 * Admin View: Step One
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<h1><?php esc_html_e( 'WC Vendors Pro Setup Completed!', 'wcvendors-pro' ); ?></h1>

<ul class="wcv-wizard-next-steps">
	<li class="wcv-wizard-next-step-item">
		<div class="wcv-wizard-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'Next step', 'wcvendors-pro' ); ?></p>
			<h3 class="next-step-description"><?php esc_html_e( 'Customize your forms', 'wcvendors-pro' ); ?></h3>
			<p class="next-step-extra-info"><?php printf( __( 'Pro provides options to customize your forms for the following ', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ); ?></p>
			<p class="next-step-heading"><?php esc_html_e( 'Forms', 'wcvendors-pro' ); ?></p>
			<ul>
				<li><?php _e( 'Products', 'wcvendors-pro' ); ?></li>
				<li><?php _e( 'Settings', 'wcvendors-pro' ); ?></li>
				<li><?php _e( 'Signup', 'wcvendors-pro' ); ?></li>
			</ul>
		</div>
		<div class="wcv-wizard-next-step-action">
			<p class="wcv-setup-actions step">
				<a class="button button-large"
				   href="<?php echo esc_url( admin_url( 'admin.php?page=wcv-settings&tab=forms' ) ); ?>">
					<?php _e( 'Form Settings', 'wcvendors-pro' ); ?>
				</a>
			</p>
		</div>
	</li>
	<li class="wcv-wizard-next-step-item">
		<div class="wcv-wizard-next-step-description">
			<p class="next-step-heading"><?php _e( 'Extend your marketplace', 'wcvendors-pro' ); ?></p>
			<h3 class="next-step-description"><?php _e( 'Extensions', 'wcvendors-pro' ); ?></h3>
			<p class="next-step-extra-info"><?php _e( 'Extend your marketplace today with a variety of extensions from us and 3rd party developers.', 'wcvendors-pro' ); ?></p>
		</div>
		<div class="wcv-wizard-next-step-action">
			<p class="wcv-setup-actions step">
				<a class="button button-large"
				   href="https://www.wcvendors.com/extensions/?utm_source=setup_wizard&utm_medium=proplugin&utm_campaign=setup_complete"
				   target="_blank">
					<?php _e( 'View Extensions', 'wcvendors-pro' ); ?>
				</a>
			</p>
		</div>
	</li>
	<li class="wcv-wizard-additional-steps">
		<div class="wcv-wizard-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'You can also:', 'wcvendors-pro' ); ?></p>
		</div>
		<div class="wc-wizard-next-step-action">
			<p class="wcv-setup-actions step">
				<a class="button button-large" href="<?php echo esc_url( admin_url() ); ?>">
					<?php esc_html_e( 'Visit dashboard', 'wcvendors-pro' ); ?>
				</a>
				<a class="button button-large"
				   href="<?php echo esc_url( admin_url( 'admin.php?page=wcv-settings' ) ); ?>">
					<?php esc_html_e( 'Review settings', 'wcvendors-pro' ); ?>
				</a>

			</p>
		</div>
	</li>
</ul>
<h4 class="help-title"><?php _e( 'Need help?', 'wcvendors-pro' ); ?></h4>
<p class="next-steps-help-text"><?php echo wp_kses_post( $help_text ); ?></p>
