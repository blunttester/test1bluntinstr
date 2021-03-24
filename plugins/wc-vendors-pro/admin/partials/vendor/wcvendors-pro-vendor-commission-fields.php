<?php

/**
 * The vendor store commission information
 *
 * This file is used to display the Vendor's commission panel in the user edit screen
 *
 * @link       http://www.wcvendors.com
 * @since      1.1.0
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin/partials/store
 */
?>

<?php
if ( ! empty( $user ) ) {
	do_action( 'wcv_admin_before_store_commission', $user );
}
?>

	<tr class='form-field'>
		<th colspan="2">
			<h3><?php _e( 'Commission', 'wcvendors-pro' ); ?></h3>
			<p><?php printf( __( 'You can override the global comissions and set different commission types for each %s', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ); ?></p>
		</th>
	</tr>

	<!-- Pro Commission -->
	<?php if ( empty( $user ) ) : ?>
		<tr class='form-field'>
			<th colspan="2">
				<h3><?php _e( 'Category commission', 'wcvendors-pro' ); ?></h3>
				<p><?php _e( "\nYou must set your category display to single select for this to work. 'WC Vendors > Settings > Forms > Product > Category Display' - <strong>Single Select</strong>", 'wcvendors-pro' ); ?></p>
			</th>
		</tr>
	<?php endif; ?>

	<?php if ( ! empty( $user ) || $is_new_category ) : ?>
	<table class="form-table">
		<tbody>
	<?php endif; ?>
		<tr class='form-field _wcv_commission_type_select'>
			<th><label for='_wcv_commission_type'><?php _e( 'Commission type', 'wcvendors-pro' ); ?></label></th>
			<td><select id="_wcv_commission_type" name="_wcv_commission_type" class="wcv_field">
					<option></option>
					<?php
					if ( ! empty( $user ) ) {
						$commission_types = apply_filters( 'wcvendors_vendor_commission_types', WCVendors_Pro_Commission_Controller::commission_types() );
					} else {
						$commission_types = WCVendors_Pro_Commission_Controller::commission_types();
					}

					foreach ( $commission_types as $option => $option_name ) {
						$selected = selected( $option, $commission_type, false );
						echo '<option value="' . $option . '" ' . $selected . '>' . $option_name . '</option>';
					}
					?>
				</select></td>
		</tr>
		<tr class='form-field _wcv_commission_percent_input'>
			<th><label for="_wcv_commission_percent"><?php _e( 'Commission %', 'wcvendors-pro' ); ?></label></th>
			<td><input type="text" id="_wcv_commission_percent" name="_wcv_commission_percent" class="wcv_field" style="width: 25em"
					   value="<?php echo $commission_percent; ?>"></td>
		</tr>
		<tr class='form-field _wcv_commission_amount_input'>
			<th><label for="_wcv_commission_amount"><?php _e( 'Commission amount', 'wcvendors-pro' ); ?></label></th>
			<td><input type="text" id="_wcv_commission_amount" name="_wcv_commission_amount" class="wcv_field" style="width: 25em"
					   value="<?php echo $commission_amount; ?>"></td>
		</tr>
		<tr class='form-field _wcv_commission_fee_input'>
			<th><label for="_wcv_commission_fee"><?php _e( 'Commission fee', 'wcvendors-pro' ); ?></label></th>
			<td><input type="text" id="_wcv_commission_fee" name="_wcv_commission_fee" class="wcv_field" style="width: 25em"
					   value="<?php echo $commission_fee; ?>"></td>
		</tr>
		<?php
		if ( ! empty( $user ) ) {
			do_action( '_wcv_after_admin_store_commission_fields', $user );
		}
		?>
		<?php if ( ! empty( $user ) || $is_new_category ) : ?>
		</tbody>
	</table>
<?php endif; ?>
<?php
if ( ! empty( $user ) ) {
	do_action( 'wcv_admin_after_store_commission', $user );
}
?>
