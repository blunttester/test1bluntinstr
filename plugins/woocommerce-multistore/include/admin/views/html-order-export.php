<?php
/**
 * Admin View: Order Export
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

extract( get_site_option( 'mstore_orders_export_options', array() ) );

?>

<div id="evcoe" class="wrap">
	<div id="icon-settings" class="icon32"></div>
	<h2><?php _e( 'WooCommerce Orders Export', 'woonet' ); ?></h2>

	<form id="form_data" name="form" method="post" action="admin.php?page=woonet-woocommerce-orders-export">

		<?php wp_nonce_field( 'woonet-orders-export/interface-export', 'woonet-orders-export-interface-nonce' ); ?>

		<p>&nbsp;</p>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row" class="label">
						<label>Format</label>
					</th>
					<td>
						<label>
							<input type="radio" value="csv" name="export_format" <?php checked( ( empty( $export_type ) ? 'csv' : $export_type ), 'csv' ); ?> />
							<span class="date-time-text format-i18n">CSV</span>
						</label>
						<br/>
						<label>
							<input type="radio" value="xls" name="export_format" <?php checked( ( empty( $export_type ) ? 'csv' : $export_type ), 'xls' ); ?> />
							<span class="date-time-text format-i18n">XLS</span>
						</label>
						<p class="description">Export file type format.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="label">
						<label>Date Interval</label>
					</th>
					<td>
						<p>
							<label>
								<input type="text" value="<?php echo empty( $export_time_after ) ? '' : date( 'm/d/Y', $export_time_after ); ?>" id="export_time_after" name="export_time_after" />
								<span class="dashicons dashicons-calendar-alt"></span>
								<span class="date-time-text format-i18n">After</span>
							</label>
						</p>
						<p>
							<label>
								<input type="text" value="<?php echo empty( $export_time_before ) || 9999999999 == $export_time_before ? '' : date( 'm/d/Y', $export_time_before ); ?>" id="export_time_before" name="export_time_before" />
								<span class="dashicons dashicons-calendar-alt"></span>
								<span class="date-time-text format-i18n">Before</span>
							</label>
						</p>
						<p class="description">Timeframe for export. Any option or both can be used</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="label">
						<label for="order_status">Order Status</label>
					</th>
					<td>
						<p>
							<select id="order_status" name="order_status">
								<option value="">All</option>
								<?php
								foreach ( wc_get_order_statuses() as $key => $order_status ) {
									printf(
										'<option value="%s" %s>%s</option>',
										$key,
										selected( isset( $order_status, $order_status[ $key ] ) && 1 == count( $order_status ), true, false ),
										$order_status
									);
								}
								?>
							</select>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="label">
						<label for="site_filter">Site Filter</label>
					</th>
					<td>
						<p>
							<select id="site_filter" name="site_filter[]" multiple="multiple">
								<?php
								if ( is_multisite() ) {
									global $WOO_MSTORE;
									$blog_ids = $WOO_MSTORE->functions->get_active_woocommerce_blog_ids();
								} else {
									$_sites   = array_merge(
										array(
											'master' => array(
												'uuid' => 'master',
											),
										),
										get_option( 'woonet_child_sites' )
									);
									$blog_ids = array();
									foreach ( $_sites as $site ) {
										$blog_ids[] = $site['uuid'];
									}
								}


								if ( empty( $site_filter ) ) {
									$site_filter = $blog_ids;
								}

								if ( is_multisite() ) {
									foreach ( $blog_ids as $blog_id ) {
										switch_to_blog( $blog_id );

										printf(
											'<option value="%s" %s>%s</option>',
											$blog_id,
											selected( isset( $site_filter ) && in_array( $blog_id, $site_filter ), true, false ),
											get_bloginfo( 'name' )
										);

										restore_current_blog();
									}
								} else {
									foreach ( $blog_ids as $blog_id ) {
										if ( isset( $blog_names_array[ $blog_id ] ) ) {
											printf(
												'<option value="%s" %s>%s</option>',
												$blog_id,
												selected( isset( $site_filter ) && in_array( $blog_id, $site_filter ), true, false ),
												$blog_names_array[ $blog_id ]
											);
										}
									}
								}
								?>
							</select>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="label">
						<label for="row_format">Row Format</label>
					</th>
					<td>
						<p>
							<select id="row_format" name="row_format">
								<option value="row_per_order" <?php selected( isset( $row_format ) && 'row_per_order' == $row_format, true, false ); ?>>row per order</option>
								<option value="row_per_product" <?php selected( isset( $row_format ) && 'row_per_product' == $row_format, true, false ); ?>>row per product</option>
							</select>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="label">
						<label>Export Fields</label>
					</th>
					<td>
						<table id="export-fields-table" style="width:100%;">
							<tr>
								<td id="export_fields">
									<h3>Network Fields</h3>
									<div>
										<div id="network_fields" class="export_fields">
											<?php
											foreach ( $this->network_fields as $key => $value ) {
												if ( empty( $value ) ) {
													$value = ucwords( str_replace( '_', ' ', $key ) );
												}
												printf(
													'<label>%s<input type="checkbox" data-name="%s" %s/></label>',
													$value,
													'network__' . $key,
													checked( isset( $export_fields, $export_fields[ 'network__' . $key ] ), true, false )
												);
											}
											?>
										</div>
									</div>
									<h3>Order Fields</h3>
									<div>
										<div id="order_fields" class="export_fields">
											<?php
											foreach ( $this->order_fields as $key => $value ) {
												if ( empty( $value ) ) {
													$value = ucwords( str_replace( '_', ' ', $key ) );
												}
												printf(
													'<label>%s<input type="checkbox" data-name="%s" %s /></label>',
													$value,
													'order__' . $key,
													checked( isset( $export_fields, $export_fields[ 'order__' . $key ] ), true, false )
												);
											}
											?>
										</div>
									</div>
									<h3>Order Item Fields</h3>
									<div>
										<div id="order_item_fields" class="export_fields">
											<?php
											foreach ( $this->order_item_fields as $key => $value ) {
												if ( empty( $value ) ) {
													$value = ucwords( str_replace( '_', ' ', $key ) );
												}
												printf(
													'<label>%s<input type="checkbox" data-name="%s" %s /></label>',
													$value,
													'order_item__' . $key,
													checked( isset( $export_fields, $export_fields[ 'order_item__' . $key ] ), true, false )
												);
											}
											?>
										</div>
									</div>
									<h3>Order Item Product Fields</h3>
									<div>
										<div id="order_item_product_fields" class="export_fields">
											<?php
											foreach ( $this->order_item_product_fields as $key => $value ) {
												if ( empty( $value ) ) {
													$value = ucwords( str_replace( '_', ' ', $key ) );
												}
												printf(
													'<label>%s<input type="checkbox" data-name="%s" %s /></label>',
													$value,
													'order_item_product__' . $key,
													checked( isset( $export_fields, $export_fields[ 'order_item_product__' . $key ] ), true, false )
												);
											}
											?>
										</div>
									</div>
									<h3>Order Item Shipping Fields</h3>
									<div>
										<div id="order_item_shipping_fields" class="export_fields">
											<?php
											foreach ( $this->order_item_shipping_fields as $key => $value ) {
												if ( empty( $value ) ) {
													$value = ucwords( str_replace( '_', ' ', $key ) );
												}
												printf(
													'<label>%s<input type="checkbox" data-name="%s" %s /></label>',
													$value,
													'order_item_shipping__' . $key,
													checked( isset( $export_fields, $export_fields[ 'order_item_shipping__' . $key ] ), true, false )
												);
											}
											?>
										</div>
									</div>
									<h3>Order Item Tax Fields</h3>
									<div>
										<div id="order_item_tax_fields" class="export_fields">
											<?php
											foreach ( $this->order_item_tax_fields as $key => $value ) {
												if ( empty( $value ) ) {
													$value = ucwords( str_replace( '_', ' ', $key ) );
												}
												printf(
													'<label>%s<input type="checkbox" data-name="%s" %s /></label>',
													$value,
													'order_item_tax__' . $key,
													checked( isset( $export_fields, $export_fields[ 'order_item_tax__' . $key ] ), true, false )
												);
											}
											?>
										</div>
									</div>
									<h3>Order Item Coupon Fields</h3>
									<div>
										<div id="order_item_coupon_fields" class="export_fields">
											<?php
											foreach ( $this->order_item_coupon_fields as $key => $value ) {
												if ( empty( $value ) ) {
													$value = ucwords( str_replace( '_', ' ', $key ) );
												}
												printf(
													'<label>%s<input type="checkbox" data-name="%s" %s /></label>',
													$value,
													'order_item_coupon__' . $key,
													checked( isset( $export_fields, $export_fields[ 'order_item_coupon__' . $key ] ), true, false )
												);
											}
											?>
										</div>
									</div>
									<h3>Order Item Fee Fields</h3>
									<div>
										<div id="order_item_fee_fields" class="export_fields">
											<?php
											foreach ( $this->order_item_fee_fields as $key => $value ) {
												if ( empty( $value ) ) {
													$value = ucwords( str_replace( '_', ' ', $key ) );
												}
												printf(
													'<label>%s<input type="checkbox" data-name="%s" %s /></label>',
													$value,
													'order_item_fee__' . $key,
													checked( isset( $export_fields, $export_fields[ 'order_item_fee__' . $key ] ), true, false )
												);
											}
											?>
										</div>
									</div>
								</td>
								<td>
									<p>Drag and drop to reorder</p>
									<div id="order_fields_selected">
										<?php
										if ( ! empty( $export_fields ) ) {
											foreach ( $export_fields as $export_field_id => $export_field_name ) {
												list( $class_name, $field_name ) = explode( '__', $export_field_id );

												$export_field_class_name    = $class_name . '_fields';
												$export_field_section_title = ucwords( str_replace( '_', ' ', $export_field_class_name ) );
												$export_field_title         = $this->{ $export_field_class_name }[ $field_name ];
												if ( empty( $export_field_title ) ) {
													$export_field_title = ucwords( str_replace( '_', ' ', $field_name ) );
												}

												printf(
													'<div id="%1$s" class="%2$s">%3$s<input type="text" name="%4$s" value="%5$s" /><span>%6$s</span><span class="dashicons dashicons-no-alt"></span></div>',
													$export_field_id,
													'ui-state-highlight',
													'Column name:',
													"export_fields[{$export_field_id}]",
													$export_field_name,
													"({$export_field_section_title} -&gt; {$export_field_title})"
												);
											}
										}
										?>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="hidden" name="evcoe_form_submit" value="export" />
			<input type="submit" name="Submit" class="button-primary" value="Export" />
		</p>
	</form>
</div>

