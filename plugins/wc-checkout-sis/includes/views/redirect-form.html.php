<?php if ( $bypass_provider ) { ?>
	<form id="wc-checkout-sis-payment-form" method="POST" action="<?php echo $bypass_provider->url; ?>">
		<?php foreach ( $bypass_provider->parameters as $key => $field ) { ?>
			<input type="hidden" name="<?php esc_html_e( $field->name ); ?>" value="<?php esc_html_e( $field->value ); ?>" />
		<?php } ?>

		<p><?php esc_html_e( 'Redirecting...', 'wc-checkout-sis' ); ?></p>
		<p><?php esc_html_e( 'If nothing happens in a few seconds, please click the button below.', 'wc-checkout-sis' ); ?></p>

		<p><input type="submit" value="<?php esc_html_e( 'Submit', 'wc-checkout-sis' ); ?>" /></p>
	</form>
	<script>document.getElementById( "wc-checkout-sis-payment-form" ).submit();</script>
<?php } else { ?>
	<p><?php _e( 'Error occurred. Please try again shortly.', 'wc-checkout-sis' ); ?></p>
<?php } ?>
