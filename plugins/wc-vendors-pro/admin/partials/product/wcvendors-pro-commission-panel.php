<?php

/**
 * The vendor store commission information
 *
 * This file is used to display the Vendor's commission panel in the product edit screen
 *
 * @link       http://www.wcvendors.com
 * @since      1.1.0
 * @version    1.7.7
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin/partials/store
 */
?>

<div id="commission" class="panel woocommerce_options_panel">
	<fieldset>
		<p class='form-field wcv_commission_type_select'>
			<label for='wcv_commission_type'><?php _e( 'Commission type', 'wcvendors-pro' ); ?></label>
			<select id="wcv_commission_type" class="wcv_field" name="wcv_commission_type">
				<option></option>
				<?php
				$commission_types = apply_filters( 'wcv_product_panel_commission_types', WCVendors_Pro_Commission_Controller::commission_types() );
				foreach ( $commission_types as $option => $option_name ) {
					$selected = selected( $option, $commission_type, false );
					echo '<option value="' . $option . '" ' . $selected . '>' . $option_name . '</option>';
				}
				?>
			</select>
		</p>
		<p class='form-field wcv_commission_percent_input'>
			<label for="wcv_commission_percent"><?php _e( 'Commission %', 'wcvendors-pro' ); ?></label>
			<input type="text" id="wcv_commission_percent" name="wcv_commission_percent" class="wc_input_price wcv_field"
				   value="<?php echo $commission_percent; ?>">

		</p>
		<p class='form-field wcv_commission_amount_input'>
			<label for="wcv_commission_amount"><?php _e( 'Commission amount', 'wcvendors-pro' ); ?></label>
			<input type="text" id="wcv_commission_amount" name="wcv_commission_amount" class="wc_input_price wcv_field"
				   value="<?php echo $commission_amount; ?>">
		</p>
		<p class='form-field wcv_commission_fee_input'>
			<label for="wcv_commission_fee"><?php _e( 'Commission fee', 'wcvendors-pro' ); ?></label>
			<input type="text" id="wcv_commission_fee" name="wcv_commission_fee" class="wc_input_price wcv_field"
				   value="<?php echo $commission_fee; ?>">
		</p>
		<?php
			do_action_deprecated( 'wcv_commission_panel_after', array(), '1.7.10', 'wcvendors_commission_panel_after' );
			do_action( 'wcvendors_commission_panel_after' );
		?>
	</fieldset>
</div>
