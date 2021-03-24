<?php
$rules = WCVendors_Pro_Commission_Controller::commission_rules();

$types_options = '';
$rules_options = '';
foreach ( WCVendors_Pro_Commission_Controller::commission_types() as $option => $option_name ) {
  $types_options .= '<option value="' . $option . '">' . $option_name . '</option>';
}

foreach ( WCVendors_Pro_Commission_Controller::commission_rules() as $rule => $label ) {
  $rules_options .= '<option value="' . $rule . '">' . $label . '</option>';
}

?>
<div class="options_group commission-tier-wrapper">
	<?php if ( isset( $field_details['title'] ) && $field_details['title'] ) : ?>
		<h3><?php echo esc_attr( $field_details['title'] ); ?></h3>
	<?php endif; ?>
	<p><?php echo esc_attr( $field_details['desc'] ); ?></p>
	<table class="commission-tiers-table widefat" id="<?php echo esc_attr( $field_details['id'] ); ?>_table">
		<thead>
		<tr>
			<th><?php _e( 'Name', 'wcvendors-pro' ); ?></th>
			<th><?php _e( 'Rule', 'wcvendors-pro' ); ?></th>
			<th><?php echo esc_attr( $field_details['value_label'] ); ?></th>
			<th><?php _e( 'Type', 'wcvendors-pro' ); ?></th>
			<th><?php _e( 'Amount', 'wcvendors-pro' ); ?></th>
			<th><?php _e( 'Percent', 'wcvendors-pro' ); ?></th>
			<th><?php _e( 'Fee', 'wcvendors-pro' ); ?></th>
			<th class="actions"></th>
		</tr>
		</thead>
		<tbody class="commission-tiers">
		<?php if ( $commission_tiers ) : ?>
			<?php foreach ( $commission_tiers as $index => $tier ) : ?>
				<?php
				$tier = wp_parse_args(
					 $tier,
					array(
						'amount'  => '',
						'fee'     => '',
						'percent' => '',
					)
					);
				?>
				<tr class="tier-row">
					<td class="input-text commission-field">
						<input type="text"
							   value="<?php echo esc_attr( $tier['name'] ); ?>"
							   name="wcv_commission_tiers_names[]"
							   placeholder="<?php _e( 'Name/Description', 'wcvendors-pro' ); ?>"
							   class="name"
						/>
					</td>
					<td class="input-text wc_input_decimal commission-field">
						<select name="wcv_commission_tiers_rules[]" class="rule" required>
							<?php
							foreach ( $rules as $rule => $label ) {
								$selected = selected( $rule, $tier['rule'], false );
								?>
								<option value="<?php echo esc_attr( $rule ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $label ); ?></option>
								<?php
							}
							?>
						</select>
					</td>
					<td class="input-text commission-field">
						<input type="number"
							   value="<?php echo esc_attr( $tier['value'] ); ?>"
							   name="wcv_commission_tiers_values[]"
							   placeholder="<?php _e( 'Value', 'wcvendors-pro' ); ?>"
							   class="value"
							   min="0"
							   step="0.01"
							   required/>
					</td>
					<td class="form-field commission-field">
						<select name="wcv_commission_tiers_types[]" class="commission-types" required>
							<option></option>
							<?php
							foreach ( WCVendors_Pro_Commission_Controller::commission_types() as $option => $option_name ) {
								$selected = selected( $option, $tier['type'], false );
								echo '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_attr( $option_name ) . '</option>';
							}
							?>
						</select>
					</td>
					<td class="input-text wc_input_decimal commission-field">
						<input type="number"
							   value="<?php echo $tier['amount']; ?>"
							   name="wcv_commission_tiers_amounts[]"
							   placeholder="<?php _e( 'Amount', 'wcvendors-pro' ); ?>"
							   class="amount"
							   step="0.01"
							   required/>
					</td>
					<td class="input-text wc_input_decimal commission-field">
						<input type="number"
							   value="<?php echo esc_attr( $tier['percent'] ); ?>"
							   name="wcv_commission_tiers_percents[]"
							   placeholder="<?php _e( 'Percent', 'wcvendors-pro' ); ?>"
							   class="percent"
							   min="0"
							   max="100"
							   step="0.01"
							   required/>
					</td>
					<td class="input-text wc_input_decimal commission-field">
						<input type="number"
							   value="<?php echo esc_attr( $tier['fee'] ); ?>"
							   name="wcv_commission_tiers_fees[]"
							   placeholder="<?php _e( 'Fee', 'wcvendors-pro' ); ?>"
							   class="fee"
							   step="0.01"
							   required/>
					</td>
					<td class="actions form-field">
						<a href="#" class="delete-tier">
							<svg class="wcv-icon wcv-icon-sm">
								<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-times"></use>
							</svg>
						</a>
					</td>
				</tr>
			<?php
			endforeach;
		endif;
		?>
		</tbody>
		<tfoot>
		<tr>
			<td>
				<a href="#" class="button insert-tier" data-row="
				<?php
				$tier =
				  '<tr class="tier-row">' .
					  '<td class="commission-field"><input type="text" name="wcv_commission_tiers_names[]" placeholder="" class="name" /></td>' .
					  '<td class="commission-field"><select name="wcv_commission_tiers_rules[]" class="rule" required><option></option>' . $rules_options . '</select></td>' .
					  '<td class="commission-field"><input type="number" name="wcv_commission_tiers_values[]" placeholder="" class="value" required step="0.01" /></td>' .
					  '<td class="commission-field"><select name="wcv_commission_tiers_types[]" class="commission-types" required><option></option>' . $types_options . '</select></td>' .
					  '<td class="commission-field"><input type="number" name="wcv_commission_tiers_amounts[]" placeholder="" class="amount" step="0.01" required/></td>' .
					  '<td class="commission-field"><input type="number" name="wcv_commission_tiers_percents[]" placeholder="" class="percent" min="0" max="100" step="0.01" required /></td>' .
					  '<td class="commission-field"><input type="number" name="wcv_commission_tiers_fees[]" placeholder="" class="fee" step="0.01" required /></td>' .
					  '<td class="actions"><a href="#" class="delete-tier" data-key=""><svg class="wcv-icon wcv-icon-sm"><use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-times"></use></svg></a></td>' .
					'</tr>';
					echo esc_attr( $tier );
				?>
				">
					<?php echo esc_attr( __( 'Add row', 'wcvendors-pro' ) ); ?>
				</a>
			</td>
			<td colspan="7"></td>
		</tr>
		</tfoot>
	</table>
</div>
