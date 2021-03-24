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
	<p class="store-setup"><?php printf( __( 'Select the pages for relevant frontend features for %s', 'wcvendors-pro' ), wcv_get_vendor_name( false, false ) ); ?></p>

	<table class="wcv-setup-table-pages">
		<thead>
		<tr>
			<td class="table-desc"><strong><?php _e( 'Pages', 'wcvendors-pro' ); ?></strong></td>
			<td class="table-check"></td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class="table-desc"><?php printf( __( '%s Pro Dashboard', 'wcvendors-pro' ), wcv_get_vendor_name() ); ?>

			</td>
			<td class="table-check">
				<?php wcv_single_select_page( 'wcvendors_dashboard_page_id', $wcvendors_dashboard_page_id, 'wc-enhanced-select' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="3" class="tool-tip">
				<?php printf( __( '<br />The page to display the WC Vendors Pro dashboard. This page requires the <code>[wcv_pro_dashboard]</code> shortcode. <strong>This page should be separate to your %1$s dashboard page above. Do not delete your %1$s dashboard page.</strong>', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ); ?>
			</td>
		</tr>
		<tr>
			<td class="table-desc"><?php printf( __( '%s Ratings', 'wcvendors-pro' ), wcv_get_vendor_name( false ) ); ?></td>
			<td class="table-check">
				<?php wcv_single_select_page( 'wcvendors_feedback_page_id', $wcvendors_feedback_page_id, 'wc-enhanced-select' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="3" class="tool-tip">
				<?php printf( __( '<br />The page to display the feedback from this will have the <code>[wcv_feedback_form]</code> shortcode.', 'wcvendors-pro' ), lcfirst( wcv_get_vendor_name( false ) ) ); ?>
			</td>
		</tr>
		</tbody>
	</table>
	<p class="wcv-setup-actions step">
		<button type="submit" class="button button-next" value="<?php esc_attr_e( 'Next', 'wcvendors-pro' ); ?>"
				name="save_step"><?php esc_html_e( 'Next', 'wcvendors-pro' ); ?></button>
	</p>
</form>
