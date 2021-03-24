<?php
/**
 * Admin View: Step One
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<form method="post">
	<?php wp_nonce_field( 'wcvendors-pro-setup' ); ?>
	<p class="store-setup"><?php esc_html_e( sprintf( __( 'The following wizard will help you configure WC Vendors Pro and get your %s onboard quickly.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ) ); ?></p>

	<table class="wcv-setup-table">
		<thead>
		<tr>
			<td class="table-desc"><strong><?php _e( 'General', 'wcvendors-pro' ); ?></strong></td>
			<td class="table-check"></td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class="table-desc"><?php _e( 'Only administrators can access the /wp-admin/ dashboard.', 'wcvendors-pro' ); ?></td>
			<td class="table-check">
				<input
						type="checkbox"
						class="option_checkbox"
						id="wcvendors_disable_wp_admin_vendors"
						name="wcvendors_disable_wp_admin_vendors"
						value="yes"
					<?php checked( $allow_admin, 'yes' ); ?>
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php printf( __( 'Choose which page %s are redirected to after login.', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ); ?></td>
			<td class="table-select">
				<select name="wcvendors_vendor_login_redirect" id="wcvendors_vendor_login_redirect"
						class="wc-enhanced-select" style="min-width: 150px;">
					<option value="my-account" <?php selected( $wcvendors_vendor_login_redirect, 'my-account' ); ?>><?php _e( 'My Account', 'wcvendors-pro' ); ?></option>
					<option value="dashboard" <?php selected( $wcvendors_vendor_login_redirect, 'dashboard' ); ?>><?php _e( 'Dashboard', 'wcvendors-pro' ); ?></option>
				</select>


			</td>
		</tr>
		</tbody>
	</table>

	<strong><?php _e( 'Commission', 'wcvendors-pro' ); ?></strong>
	<p class="store-setup"><?php printf( __( 'Commissions are calculated per product. The commission rate can be set globally, at a %s level or at a product level.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ) ); ?></p>

	<table class="wcv-setup-table">
		<thead>
		<tr>
			<td class="table-desc"></td>
			<td class="table-check"></td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class="table-desc"><?php _e( 'Global commission type', 'wcvendors-pro' ); ?></td>
			<td class="table-select">
				<select name="wcvendors_commission_type" id="wcvendors_commission_type" style="min-width: 150px;">
					<?php foreach ( WCVendors_Pro_Commission_Controller::commission_types() as $value => $label ) : ?>
						<option value="<?php echo $value; ?>" <?php selected( $wcvendors_commission_type, $value ); ?>><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php esc_html_e( 'Global commission rate %', 'wcvendors-pro' ); ?></td>
			<td class="table-text">
				<input
						type="text"
						id="wcvendors_vendor_commission_rate"
						name="wcvendors_vendor_commission_rate"
						placeholder="%"
						class="input"
						value="<?php echo $commission_rate; ?>"
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php esc_html_e( 'Global commission amount', 'wcvendors-pro' ); ?></td>
			<td class="table-text">
				<input
						type="text"
						id="wcvendors_commission_amount"
						name="wcvendors_commission_amount"
						class="input"
						value="<?php echo $wcvendors_commission_amount; ?>"
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php esc_html_e( 'Global commission fee', 'wcvendors-pro' ); ?></td>
			<td class="table-text">
				<input
						type="text"
						id="wcvendors_commission_fee"
						name="wcvendors_commission_fee"
						class="input"
						value="<?php echo $wcvendors_commission_fee; ?>"
				/>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php _e( 'Coupon action', 'wcvendors-pro' ); ?></td>
			<td class="table-select">
				<select name="wcvendors_commission_coupon_action" id="wcvendors_commission_coupon_action"
						class="wc-enhanced-select" style="min-width: 150px;">
					<option value="yes" <?php selected( $wcvendors_commission_coupon_action, 'yes' ); ?>><?php _e( 'After', 'wcvendors-pro' ); ?></option>
					<option value="no" <?php selected( $wcvendors_commission_coupon_action, 'no' ); ?>><?php _e( 'Before', 'wcvendors-pro' ); ?></option>
				</select>
			</td>
		</tr>

		</tbody>
	</table>

	<p class="wcv-setup-actions step">
		<button type="submit" class="button button-next" value="<?php esc_attr_e( 'Next', 'wcvendors-pro' ); ?>"
				name="save_step"><?php esc_html_e( 'Next', 'wcvendors-pro' ); ?></button>
	</p>
</form>
