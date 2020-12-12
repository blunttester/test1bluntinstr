<div class="wc-checkout-sis-methods-container">
	<input type="hidden" name="wc_checkout_sis_preselected_method" value="0" />

	<div class="wc-checkout-sis-methods">
		<?php foreach ( $payment_methods_grouped as $group_key => $methods ) { ?>
			<div class="wc-checkout-sis-method-group">
				<div class="wc-checkout-sis-method-group-title"><?php echo $groups[$group_key]; ?></div>
				
				<?php foreach ( $methods as $key => $method ) { ?>
					<div class="wc-checkout-sis-method" id="wc-checkout-sis-method-<?php echo $method->id; ?>">
						<div class="wc-checkout-sis-method-icon-container">
							<img src="<?php esc_attr_e( $method->svg ); ?>" class="wc-checkout-sis-method-icon" />
						</div>
						<input type="radio" name="wc_checkout_sis_preselected_method" value="<?php echo $method->id; ?>" id="wc-checkout-sis-method-radio-<?php echo $method->id; ?>" />
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</div>
