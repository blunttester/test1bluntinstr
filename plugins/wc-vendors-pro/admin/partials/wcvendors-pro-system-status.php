<?php
/**
 * Provide a meta box view for the settings page
 *
 * @link       http://www.wcvendors.com
 * @since      1.2.3
 * @version    1.5.2
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin/partials
 */

?>

<table class="wc_status_table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3"
			data-export-label="WC Vendors Pro"><?php _e( 'WC Vendors Pro', 'wcvendors-pro' ); ?><?php echo ' <a href="#" class="help_tip" data-tip="' . esc_attr__( 'This section shows information about WC Vendors Pro requirements.', 'wcvendors-pro' ) . '"></a>'; ?></th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td data-export-label="Theme compatability"><?php _e( 'Theme compatability', 'wcvendors-pro' ); ?>:</td>
		<td class="help">&nbsp;</td>
		<td>
			<?php
			if ( '' !== $woocommerce_override ) {
				echo '<mark class="error">&#10005; ' . sprintf( __( 'Your theme is not 100%% WooCommerce compatible and will not display %1$s stores properly. Please show this page ( <a href="%2$s">https://docs.woothemes.com/document/third-party-custom-theme-compatibility/</a> ) to your theme author and instruct them to provide full WooCommerce compatibility, not the limited WooCommerce compatibility they currently provide by using woocommerce.php instead of full templates.', 'wcvendors-pro' ), wcv_get_vendor_name( true, false ), 'https://docs.woothemes.com/document/third-party-custom-theme-compatibility/' ) . '</mark>';
			} else {
				echo '-';
			}
			?>
		</td>
	</tr>
	<tr>
		<td data-export-label="Pro Dashboard Page"><a
					href="admin.php?page=wcv-settings&tab=display"><?php _e( 'Pro dasboard page', 'wcvendors-pro' ); ?>
				:</a></td>
		<td class="help">&nbsp;</td>
		<td>
			<?php
			if ( empty( $pro_dashboard_pages ) ) {
				echo '<mark class="error">&#10005; -' . sprintf( __( 'WC Vendors Pro WILL NOT FUNCTION without this set. <a href="%s">Click here to set the page</a>.', 'wcvendors-pro' ), 'admin.php?page=wcv-settings&tab=display' ) . '</mark>';
			} else {
				if ( in_array( $free_dashboard_page, $pro_dashboard_pages ) ) {
					echo '<mark class="error">&#10005; -' . sprintf( __( 'Your pro dashboard page cannot be set to your free dashboard page. <a href="%s">Click here to change</a>.', 'wcvendors-pro' ), 'admin.php?page=wcv-settings&tab=display' ) . '</mark>';

				} else {
					echo '<mark class="yes">&#10004; - #' . implode( ', ', $pro_dashboard_pages ) . '</mark>';
				}
			}
			?>
		</td>
	</tr>
	<tr>
		<td data-export-label="Feedback form page"><a
					href="admin.php?page=wcv-settings&tab=display"><?php _e( 'Feedback form page', 'wcvendors-pro' ); ?>
				:</a></td>
		<td class="help">&nbsp;</td>
		<td>
		<?php
		if ( ! $feedback_form_page ) {
			echo '<mark class="error">&#10005;  ' . sprintf( __( '%1$s ratings will not work without this page set. <a href="%2$s">Click here to set the page</a>.', 'wcvendors-pro' ), wcv_get_vendor_name( true, true ), 'admin.php?page=wcv-settings&tab=display' ) . '</mark>';
		} else {
			echo '<mark class="yes">&#10004; - #' . $feedback_form_page . '</mark>';
		}
		?>
			</td>
	</tr>
	<tr>
		<td data-export-label="<?php echo wcv_get_vendor_name(); ?> Shop Permalink"><a
					href="admin.php?page=wcv-settings&tab=display"><?php echo sprintf( __( '%s Shop Permalink', 'wcvendors-pro' ), wcv_get_vendor_name() ); ?>
				:</a></td>
		<td class="help">&nbsp;</td>
		<td>
		<?php
		if ( $vendor_shop_permalink == '' ) {
			echo '<mark class="error">&#10005; -' . sprintf( __( 'You need to set a %1$s store permalink. <a href="%2$s">Click here to set the slug</a>.', 'wcvendors-pro' ), wcv_get_vendor_name(), 'admin.php?page=wcv-settings&tab=display' ) . '</mark>';
		} else {
			echo '<mark class="yes">&#10004; - ' . $vendor_shop_permalink . '</mark>';
		}
		?>
			</td>
	</tr>
	</tbody>
</table>
