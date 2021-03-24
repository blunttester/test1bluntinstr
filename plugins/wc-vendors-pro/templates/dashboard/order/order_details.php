<?php
/**
 * The template for displaying the order details
 *
 * Override this template by copying it to yourtheme/wc-vendors/dashboard/order
 *
 * @package    WCVendors_Pro
 * @version    1.7.6
 */

$total_colspan = wc_tax_enabled() ? count( $order->get_taxes() ) : 1;
$label_colspan = wc_tax_enabled() && $order->get_total_tax() > 0 ? 6 : 5;

?>

<div class="wcv-shade wcv-fade">

	<div id="order-details-modal-<?php echo $order_id; ?>" class="wcv-modal wcv-fade"
		 data-trigger="#open-order-details-modal-<?php echo $order_id; ?>" data-width="80%" data-height="85%"
		 data-reveal aria-labelledby="modalTitle-<?php echo $order_id; ?>" aria-hidden="true" role="dialog">

		<div class="modal-header">
			<button class="modal-close wcv-dismiss">
				<svg class="wcv-icon wcv-icon-sm">
					<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-times"></use>
				</svg>
			</button>
			<h3 id="modal-title"><?php echo sprintf( __( 'Order #%s Details', 'wcvendors-pro' ), $order->get_order_number() ); ?>
				- <?php echo date_i18n( get_option( 'date_format', 'F j, Y' ), ( $order_date->getOffsetTimestamp() ) ); ?></h3>
		</div>

		<div class="modal-body wcv-order-details" id="modalContent">

			<?php do_action( 'wcvendors_order_before_customer_detail' ); ?>

			<div class="wcv-order-customer-details wcv-cols-group wcv-horizontal-gutters">

				<div class="all-50 small-100 billing-details">
					<h4><?php _e( 'Billing details', 'wcvendors-pro' ); ?></h4>
					<?php
					// Display values
					echo '<div class="wcv-order-address">';

					if ( $order->get_formatted_billing_address() ) {
						echo '<p><strong>' . __( 'Address', 'wcvendors-pro' ) . ':</strong>' . wp_kses( $order->get_formatted_billing_address(), array( 'br' => array() ) ) . '</p>';
					} else {
						echo '<p class="none_set"><strong>' . __( 'Address', 'wcvendors-pro' ) . ':</strong> ' . __( 'No billing address set.', 'wcvendors-pro' ) . '</p>';
					}

					echo '</div>';
					?>
				</div>  <!-- // billing details  -->

				<div class="all-50 small-100 shipping-details">
					<h4><?php _e( 'Shipping details', 'wcvendors-pro' ); ?></h4>
					<?php
					// Display values
					echo '<div class="wcv-order-address">';

					if ( $order->get_formatted_shipping_address() ) {
						echo '<p><strong>' . __( 'Address', 'wcvendors-pro' ) . ':</strong>' . wp_kses( $order->get_formatted_shipping_address(), array( 'br' => array() ) ) . '</p>';
					} else {
						echo '<p class="none_set"><strong>' . __( 'Address', 'wcvendors-pro' ) . ':</strong> ' . __( 'No shipping address set.', 'wcvendors-pro' ) . '</p>';
					}

					echo '</div>';
					?>
				</div> <!-- //shipping details  -->

			</div>

			<hr/>

			<?php do_action( 'wcvendors_order_before_items_detail' ); ?>

			<div class="wcv-order-items-details wcv-cols-group wcv-horizontal-gutters">

				<div class="all-100">

					<h4><?php _e( 'Order items', 'wcvendors-pro' ); ?></h4>

					<table cellpadding="0" cellspacing="0" class="wcv-table wcv-order-table">
						<thead>
						<tr>
							<th class="order_item" colspan="2"><?php _e( 'Item', 'wcvendors-pro' ); ?></th>
							<th class="commission"><?php _e( 'Commission', 'wcvendors-pro' ); ?></th>
							<th class="cost"><?php _e( 'Cost', 'wcvendors-pro' ); ?></th>
							<th class="qty"><?php _e( 'Qty', 'wcvendors-pro' ); ?></th>
							<th class="total"><?php _e( 'Total', 'wcvendors-pro' ); ?></th>

							<?php
							if ( ! empty( $order_taxes ) ) :
								foreach ( $order_taxes as $tax_id => $tax_item ) :
									$column_label = ! empty( $tax_item['label'] ) ? $tax_item['label'] : __( 'Tax', 'wcvendors-pro' );
									?>
									<th class="line_tax tips"><?php echo esc_attr( $column_label ); ?></th>
									<?php
								endforeach;
							endif;
							?>
						</tr>
						</thead>
						<!-- Desktop line items -->
						<tbody id="order_line_items">
						<?php foreach ( $order_item_details as $item_id => $order_item_detail ) : ?>

							<tr class="order-item item-id-<?php echo $item_id; ?>">
								<td class="wcv-order-thumb"><?php echo $order_item_detail['thumbnail']; ?></td>
								<td class="name"><?php echo $order_item_detail['product_name']; ?><?php echo $order_item_detail['product_meta']; ?></td>
								<td class="item_cost" width="1%"><?php echo $order_item_detail['commission']; ?></td>
								<td class="item_cost" width="1%"><?php echo $order_item_detail['cost']; ?></td>
								<td class="quantity" width="1%"><?php echo $order_item_detail['qty']; ?></td>
								<td class="line_cost" width="1%"><?php echo $order_item_detail['total']; ?></td>

								<?php if ( wc_tax_enabled() ) : ?>

								<?php foreach ( $order_item_detail['tax_items'] as $tax_item ) : ?>
										<td class="line_tax" width="1%">
											<div class="view">
												<?php echo $tax_item; ?>
											</div>
										</td>
										<?php endforeach; ?>
								<?php endif; ?>
							</tr>

						<?php endforeach; ?>
						</tbody>

						<!-- Desktop Totals -->
						<tbody class="wcv-order-totals">
						<tr class="shipping">
							<td class="wcv-order-totals-label" colspan="<?php echo $label_colspan; ?>"><?php _e( 'Shipping', 'wcvendors-pro' ); ?>
								:
							</td>
							<td class="total" colspan="<?php echo $total_colspan; ?>"><?php echo wc_price( $_order->total_shipping, array( 'currency' => $order_currency ) ); ?></td>
						</tr>

						<?php if ( wc_tax_enabled() ) : ?>
							<?php foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
								<tr>
									<td class="wcv-order-totals-label" colspan="<?php echo $label_colspan; ?>"><?php echo $tax->label; ?>:</td>
									<td class="total" colspan="<?php echo $total_colspan; ?>"><?php echo wc_price( $_order->total_tax, array( 'currency' => $order_currency ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>

						<tr>
							<td class="wcv-order-totals-label"
								colspan="<?php echo $label_colspan; ?>"><?php _e( 'Commission total', 'wcvendors-pro' ); ?>:
							</td>
							<td class="total" colspan="<?php echo $total_colspan; ?>">
								<div class="view"><?php echo wc_price( $_order->commission_total, array( 'currency' => $order_currency ) ); ?></div>
							</td>
						</tr>
						<tr>
							<td class="wcv-order-totals-label"
								colspan="<?php echo $label_colspan; ?>"><?php _e( 'Order total', 'wcvendors-pro' ); ?>:
							</td>
							<td class="total" colspan="<?php echo $total_colspan; ?>">
								<div class="view"><?php echo wc_price( $_order->total, array( 'currency' => $order_currency ) ); ?></div>
							</td>
						</tr>

						</tbody>
					</table>

				</div>

			</div>

			<div class="wcv-cols-group wcv-horizontal-gutters wcv_mobile wcv-mobile-order-items">
				<div class="all-100">
					<table>
						<thead>
							<tr>
								<th width="65%">Product</th>
								<th width="35%">QTY</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $order_item_details as $item_id => $order_item_detail ) : ?>
							<tr>
								<td>
									<?php echo $order_item_detail['product_name']; ?>
									<?php echo $order_item_detail['product_meta']; ?>
								</td>
								<td><?php echo $order_item_detail['qty']; ?>x <?php echo $order_item_detail['total']; ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>

						<tbody class="mobile-wcv-order-totals">
						<tr>
							<td colspan="2"><h4><?php _e( 'Totals', 'wcvendors-pro' ); ?></h4></td>
						</tr>
						<tr class="shipping">
							<td class="wcv-order-totals-label"><?php _e( 'Shipping', 'wcvendors-pro' ); ?>:</td>
							<td><?php echo wc_price( $_order->total_shipping, array( 'currency' => $order_currency ) ); ?></td>
						</tr>

						<?php if ( wc_tax_enabled() ) : ?>
							<?php foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
								<tr>
									<td class="wcv-order-totals-label"><?php echo $tax->label; ?>:</td>
									<td><?php echo wc_price( $_order->total_tax, array( 'currency' => $order_currency ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>

						<tr>
							<td class="wcv-order-totals-label"><?php _e( 'Commission total', 'wcvendors-pro' ); ?>:</td>
							<td><div class="view"><?php echo wc_price( $_order->commission_total, array( 'currency' => $order_currency ) ); ?></div></td>
						</tr>
						<tr>
							<td class="wcv-order-totals-label"><?php _e( 'Order total', 'wcvendors-pro' ); ?>:</td>
							<td><div class="view"><?php echo wc_price( $_order->total, array( 'currency' => $order_currency ) ); ?></div></td>
						</tr>

						</tbody>
					</table>
				</div>

			</div>


			<hr/>

			<?php do_action( 'wcvendors_order_after_items_detail' ); ?>

			<?php
			$allow_read_order_note = wc_string_to_bool( get_option( 'wcvendors_capability_order_read_notes', 'no' ) );
			?>

			<?php if ( $allow_read_order_note ) : ?>
			<div class="wcv-cols-group wcv-horizontal-gutters">

				<div class="all-100">
					<h4><?php _e( 'Customer note', 'wcvendors-pro' ); ?></h4>
					<?php echo $customer_note; ?>
				</div>

			</div>
			<?php endif; ?>

		</div>

	</div>

</div>
