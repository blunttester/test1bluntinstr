<?php

/**
 * The shipping rates table
 *
 * This file is used to show the shipping rates for a product or store
 *
 * @link       http://www.wcvendors.com
 * @since      1.1.0
 * @version    1.4.4
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/forms/partials/
 */

$countries = ( WC()->countries->get_shipping_countries() ) ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();
$row_count = 0;
if ( ! isset( $countries['EWE'] ) ) {
	$countries['EWE'] = __( 'Everywhere else', 'wcvendors-pro' );
}
?>

<div id="shipping-country-rates" class="form-field wcv_shipping_rates">
	<table id="wcv_shipping_rates_table">
		<thead>
		<tr>
			<th class="sort">&nbsp;</th>
			<th class="country"><?php _e( 'Country', 'wcvendors-pro' ); ?></th>
			<th class="state"><?php _e( 'State', 'wcvendors-pro' ); ?> </th>
			<th class="postcode"><?php _e( 'Postcode', 'wcvendors-pro' ); ?> </th>
			<th class="fee"><?php _e( 'Shipping Fee', 'wcvendors-pro' ); ?></th>
			<th class="override"><?php _e( 'Override', 'wcvendors-pro' ); ?></th>
			<th class="del">&nbsp;</th>
		</tr>
		</thead>
		<tbody>

		<?php if ( $shipping_rates ) : ?>

			<?php for ( $i = 0; $i < count( $shipping_rates ); $i ++ ) : ?>

				<?php $rate = $shipping_rates[ $i ]; ?>

				<!-- required for pro 1.4 and above -->
				<?php
				if ( ! array_key_exists( 'qty_override', $rate ) ) {
					$rate['qty_override'] = '';
				}
				?>
				<?php
				if ( ! array_key_exists( 'postcode', $rate ) ) {
					$rate['postcode'] = '';
				}
				?>

				<tr>
					<td class="sort">
						<svg class="wcv-icon wcv-icon-sm">
							<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-sort"></use>
						</svg>
					</td>

					<td class="country" data-title="<?php _e( 'Country', 'wcvendors-pro' ); ?>">
						<select name="_wcv_shipping_countries[]" id="_wcv_shipping_countries[]"
								class="country_to_state country_select">
							<option value=""><?php _e( 'Select a country&hellip;', 'wcvendors-pro' ); ?></option>
							<?php
							foreach ( $countries as $ckey => $cvalue ) {
								echo '<option value="' . esc_attr( $ckey ) . '" ' . selected( esc_attr( $rate['country'] ), $ckey, false ) . '>' . $cvalue . '</option>';
							}
							?>
						</select>
					</td>
					<td class="state" data-title="<?php _e( 'State', 'wcvendors-pro' ); ?>">
						<input type="text" placeholder="<?php _e( 'State', 'wcvendors-pro' ); ?>" class="shipping_state"
							   name="_wcv_shipping_states[]" value="<?php echo esc_attr( $rate['state'] ); ?>"/>
					</td>
					<td class="postcode" data-title="<?php _e( 'Postcode', 'wcvendors-pro' ); ?>">
						<input type="text" placeholder="<?php _e( 'Postcode', 'wcvendors-pro' ); ?>"
							   name="_wcv_shipping_postcodes[]" value="<?php echo esc_attr( $rate['postcode'] ); ?>"/>
					</td>
					<td class="fee" data-title="<?php _e( 'Shipping Fee', 'wcvendors-pro' ); ?>">
						<input type="text" placeholder="<?php _e( 'Fee', 'wcvendors-pro' ); ?>"
							   name="_wcv_shipping_fees[]" value="<?php echo esc_attr( $rate['fee'] ); ?>"
							   data-parsley-price/>
					</td>
					<td class="override" data-title="<?php _e( 'Override', 'wcvendors-pro' ); ?>">
						<input type="checkbox" name="_wcv_shipping_overrides_<?php echo $i; ?>"
							   id="_wcv_shipping_overrides_<?php echo $i; ?>" <?php checked( $rate['qty_override'], 'yes' ); ?> />
						<label><?php _e( 'QTY', 'wcvendors-pro' ); ?></label>
					</td>
					<td class="del">
						<a href="#" class="delete">
							<svg class="wcv-icon wcv-icon-sm">
								<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-times"></use>
							</svg>
						</a>
					</td>
				</tr>
				<?php $row_count = $i; ?>
			<?php endfor; ?>
		<?php endif; ?>
		</tbody>

		<tfoot>
			<tr>
				<th colspan="7">
					<a href="#" class="button insert" data-row="
					<?php

					$rate = array(
						'country'      => '',
						'state'        => '',
						'postcode'     => '',
						'fee'          => '',
						'qty_override' => '',
					);

					$file_data_row = '<tr>
								<td class="sort"><svg class="wcv-icon wcv-icon-sm"><use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-sort"></use></svg></td>
								 <td class="country" data-title="' . __( 'Country', 'wcvendors-pro' ) . '">
								 <select name="_wcv_shipping_countries[]" id="_wcv_shipping_countries[]" class="country_to_state country_select">
									<option value="">' . __( 'Select a country&hellip;', 'wcvendors-pro' ) . '</option>';

					foreach ( $countries as $ckey => $cvalue ) {
						$file_data_row .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( esc_attr( $rate['country'] ), $ckey, false ) . '>' . $cvalue . '</option>';
					}

					$file_data_row .= '</select></td>
								<td class="state" data-title="' . __( 'State', 'wcvendors-pro' ) . '"><input type="text" placeholder="' . __( 'State', 'wcvendors-pro' ) . '" class="shipping_state" name="_wcv_shipping_states[]" value="' . esc_attr( $rate['state'] ) . '" /></td>
								<td class="postcode" data-title="' . __( 'Postcode', 'wcvendors-pro' ) . '"><input type="text" placeholder="' . __( 'Postcode', 'wcvendors-pro' ) . '" name="_wcv_shipping_postcodes[]" value="' . esc_attr( $rate['postcode'] ) . '" /></td>
								<td class="fee" data-title="' . __( 'Shipping Fee', 'wcvendors-pro' ) . '"><input type="text" data-parsley-price placeholder="' . __( 'Fee', 'wcvendors-pro' ) . '" name="_wcv_shipping_fees[]" value="' . esc_attr( $rate['fee'] ) . '" /></td>
								<td class="override" data-title="' . __( 'Override', 'wcvendors-pro' ) . '"><input type="checkbox" id="_wcv_shipping_overrides_" name="_wcv_shipping_overrides_" ' . checked( $rate['qty_override'], 'yes' ) . ' /><label>' . __( 'QTY', 'wcvendors-pro' ) . '</label></td>
								<td width="1%"><a href="#" class="delete"><svg class="wcv-icon wcv-icon-sm"><use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-times"></use></svg></a></td>
							</tr>';

					echo esc_attr( $file_data_row );
					?>
					"><?php _e( 'Add Rate', 'wcvendors-pro' ); ?></a>
				</th>
			</tr>
		</tfoot>
	</table>
</div>

<!-- <td class="country"><input type="text" placeholder="'. __( "Country", "wcvendors-pro" ) .'" name="_wcv_shipping_countries[]" value="' .esc_attr( $rate["country"] ). '" /></td> -->
