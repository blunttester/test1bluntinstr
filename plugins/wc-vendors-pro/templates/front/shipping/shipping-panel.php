<?php
/**
 * The Template for displaying the product shipping details
 *
 * Override this template by copying it to yourtheme/wc-vendors/front/shipping
 *
 * @package    WCVendors_Pro
 * @version  1.7.7
 * @since    1.6.3
 */
?>
<?php if ( ! empty( $shipping_flat_rates ) ) : ?>
	<?php if ( ! empty( $shipping_flat_rates['national'] ) || ! empty( $shipping_flat_rates['international'] ) || ( array_key_exists( 'national_free', $shipping_flat_rates ) && $shipping_flat_rates['national_free'] == 'yes' ) || ( array_key_exists( 'international_free', $shipping_flat_rates ) && $shipping_flat_rates['international_free'] == 'yes' ) ) : ?>
		<?php if ( count( $shipping_costs['value'] ) > 0 ) : ?>
		<h3><?php _e( $shipping_costs['label'], 'wcvendors-pro' ); ?></h3>
		<table>
			<?php foreach ( $shipping_costs['value'] as $val ) : ?>
				<tr>
					<td width="60%">
						<strong><?php esc_attr_e( $val['label'], 'wcvendors-pro' ); ?><?php echo $val['country']; ?></strong>
					</td>
					<td width="40%"><?php echo wc_clean( $val['value'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php endif; ?>

		<?php if ( 'yes' !== $shipping_flat_rates['national_disable'] && ! empty( $national_rate_details ) ) : ?>
			<h3><?php esc_attr_e( $national_details['label'], 'wcvendors-pro' ); ?></h3>
			<table>
				<?php foreach ( $national_details['value'] as $key => $label ) : ?>
					<?php if ( isset( $shipping_flat_rates[ $key ] ) && '' !== $shipping_flat_rates[ $key ] ) : ?>
					<tr>
						<td width="60%">
							<strong><?php echo esc_attr( $label ); ?></strong>
						</td>
						<td width="40%">
							<?php echo wc_price( $shipping_flat_rates[ $key ] ); ?>
						</td>
					</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>

		<?php if ( 'yes' !== $shipping_flat_rates['international_disable'] && ! empty( $international_rate_details ) ) : ?>
			<h3><?php _e( $international_details['label'], 'wcvendors-pro' ); ?></h3>
			<table>
				<?php foreach ( $international_details['value'] as $key => $label ) : ?>
					<?php if ( isset( $shipping_flat_rates[ $key ] ) && '' !== $shipping_flat_rates[ $key ] ) : ?>
					<tr>
						<td width="60%">
							<strong><?php echo esc_attr( $label ); ?></strong>
						</td>
						<td width="40%">
							<?php echo wc_price( $shipping_flat_rates[ $key ] ); ?>
						</td>
					</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>

	<?php else : ?>

		<h5><?php esc_attr_e( 'No shipping rates are available for this product.', 'wcvendors-pro' ); ?></h5>

	<?php endif; ?>

<?php else : ?>

	<?php if ( ! empty( $shipping_table_rates['value'] ) ) : ?>
		<h3><?php _e( $shipping_table_rates['label'], 'wcvendors-pro' ); ?></h3>
		<table>
			<thead>
			<tr>
				<th><?php esc_attr_e( 'Country', 'wcvendors-pro' ); ?></th>
				<th><?php esc_attr_e( 'State', 'wcvendors-pro' ); ?></th>
				<th><?php esc_attr_e( 'Postcode', 'wcvendors-pro' ); ?></th>
				<th><?php esc_attr_e( 'Cost', 'wcvendors-pro' ); ?></th>
			</tr>
			</thead>
			<?php foreach ( $shipping_table_rates['value'] as $rate ) : ?>
				<?php if ( isset( $rate['fee'] ) && $rate['fee'] >= 0 ) : ?>
				<tr>
					<td width="30%"><?php echo ( esc_attr( $rate['country'] ) != '' ) ? esc_attr( $countries[ strtoupper( $rate['country'] ) ] ) : __( 'Any', 'wcvendors-pro' ); ?></td>
					<td width="30%"><?php echo ( esc_attr( $rate['state'] ) != '' ) ? esc_attr( WC()->countries->get_states( $rate['country'] )[ $rate['state'] ] ) : __( 'Any', 'wcvendors-pro' ); ?></td>
					<td width="20%"><?php echo ( esc_attr( $rate['postcode'] ) != '' ) ? esc_attr( $rate['postcode'] ) : __( 'Any', 'wcvendors-pro' ); ?></td>
					<td width="20%"><?php echo ( esc_attr( $rate['fee'] )> 0 ) ? wc_price( esc_attr( $rate['fee'] ) . $product->get_price_suffix() ) : __( 'Free shipping', 'wcvendors-pro' ); ?></td>
				</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</table>
	<?php else : ?>
		<h5><?php esc_attr_e( 'No shipping rates are available for this product.', 'wcvendors-pro' ); ?></h5>
	<?php endif; ?>
<?php endif; ?>
<?php if ( ! empty( $shipping_details ) && count( $shipping_details['value'] ) > 0 ) : ?>
	<h3><?php esc_attr_e( $shipping_details['label'], 'wcvendors-pro' ); ?></h3>
	<table>
		<?php foreach ( $shipping_details['value'] as $key => $val ) : ?>
			<tr>
				<td><strong> <?php esc_attr_e( $val['label'], 'wcvendors-pro' ); ?></strong></td>
				<td><?php echo $val['value']; ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>
