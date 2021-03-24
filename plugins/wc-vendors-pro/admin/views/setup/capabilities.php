<?php
/**
 * Admin View: Step Two
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<form method="post">
	<?php wp_nonce_field( 'wcvendors-pro-setup' ); ?>
	<p class="store-setup"><?php printf( __( 'Select which features to disable for the %s pro dashboard', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name() ) ); ?></p>

	<table class="wcv-setup-table">
		<thead>
		<tr>
			<td class="table-desc"><strong><?php _e( 'WC Vendors Pro Features', 'wcvendors-pro' ); ?></strong></td>
			<td class="table-check"></td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class="table-desc"><?php _e( 'Product management', 'wcvendors-pro' ); ?></td>
			<td class="table-check">
				<input
						type="checkbox"
						style="float: right; font-size: 4em;"
						id="wcvendors_product_management_cap"
						name="wcvendors_product_management_cap"
						value="yes"
					<?php checked( $wcvendors_product_management_cap, 'yes' ); ?>
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php _e( 'Order management', 'wcvendors-pro' ); ?></td>
			<td class="table-check">
				<input
						type="checkbox"
						style="float: right; font-size: 4em;"
						id="wcvendors_order_management_cap"
						name="wcvendors_order_management_cap"
						value="yes"
					<?php checked( $wcvendors_order_management_cap, 'yes' ); ?>
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php _e( 'Coupon management', 'wcvendors-pro' ); ?></td>
			<td class="table-check">
				<input
						type="checkbox"
						style="float: right; font-size: 4em;"
						id="wcvendors_shop_coupon_management_cap"
						name="wcvendors_shop_coupon_management_cap"
						value="yes"
					<?php checked( $wcvendors_shop_coupon_management_cap, 'yes' ); ?>
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php _e( 'Settings management', 'wcvendors-pro' ); ?></td>
			<td class="table-check">
				<input
						type="checkbox"
						style="float: right; font-size: 4em;"
						id="wcvendors_settings_management_cap"
						name="wcvendors_settings_management_cap"
						value="yes"
					<?php checked( $wcvendors_settings_management_cap, 'yes' ); ?>
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php _e( 'Ratings', 'wcvendors-pro' ); ?></td>
			<td class="table-check">
				<input
						type="checkbox"
						style="float: right; font-size: 4em;"
						id="wcvendors_ratings_management_cap"
						name="wcvendors_ratings_management_cap"
						value="yes"
					<?php checked( $wcvendors_ratings_management_cap, 'yes' ); ?>
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php printf( __( '%s shipping', 'wcvendors-pro' ), wcv_get_vendor_name() ); ?></td>
			<td class="table-check">
				<input
						type="checkbox"
						style="float: right; font-size: 4em;"
						id="wcvendors_shipping_management_cap"
						name="wcvendors_shipping_management_cap"
						value="yes"
					<?php checked( $wcvendors_shipping_management_cap, 'yes' ); ?>
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php _e( 'View store', 'wcvendors-pro' ); ?></td>
			<td class="table-check">
				<input
						type="checkbox"
						style="float: right; font-size: 4em;"
						id="wcvendors_view_store_cap"
						name="wcvendors_view_store_cap"
						value="yes"
					<?php checked( $wcvendors_view_store_cap, 'yes' ); ?>
				/>
			</td>
		</tr>
		</tbody>
	</table>


	<p class="wcv-setup-actions step">
		<button type="submit" class="button button-next" value="<?php esc_attr_e( 'Next', 'wcvendors-pro' ); ?>"
				name="save_step"><?php esc_html_e( 'Next', 'wcvendors-pro' ); ?></button>
	</p>
</form>
