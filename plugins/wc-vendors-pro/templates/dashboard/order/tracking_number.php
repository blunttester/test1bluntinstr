<?php
/**
 * The template for displaying the tracking number form this is displayed in the modal pop up.
 *
 * Override this template by copying it to yourtheme/wc-vendors/dashboard/order
 *
 * @package    WCVendors_Pro
 * @since      1.0.3
 * @version    1.7.8
 */
?>

<?php
// Change text to make UI a little cleaner
$button_text = '';

if ( isset( $tracking_details['_wcv_tracking_number'] ) && '' != $tracking_details['_wcv_tracking_number'] ) {
	$button_text = __( 'Update Tracking Details', 'wcvendors-pro' );
} else {
	$button_text = __( 'Add Tracking Details', 'wcvendors-pro' );
}

?>

<div class="wcv-shade wcv-fade">
	<div id="tracking-modal-<?php echo $order_number; ?>" class="wcv-modal wcv-fade"
		 data-trigger="#open-tracking-modal-<?php echo $order_number; ?>" data-width="80%" data-height="80%"
		 aria-labelledby="modalTitle-<?php echo $order_number; ?>" aria-hidden="true" role="dialog">

		<div class="modal-header">
			<button class="modal-close wcv-dismiss">
				<svg class="wcv-icon wcv-icon-sm">
					<use xlink:href="<?php echo WCV_PRO_PUBLIC_ASSETS_URL; ?>svg/wcv-icons.svg#wcv-icon-times"></use>
				</svg>
			</button>
			<h3 id="modal-title"><?php _e( 'Shipment tracking', 'wcvendors-pro' ); ?></h3>
		</div>

		<div class="modal-body" id="tracking-modal-<?php echo $order_number; ?>-content">

			<form method="post" class="wcv-form wcv-form-exclude" action="">

				<?php WCVendors_Pro_Tracking_Number_Form::shipping_provider( $tracking_details['_wcv_shipping_provider'], $order_id ); ?>

				<?php WCVendors_Pro_Tracking_Number_Form::tracking_number( $tracking_details['_wcv_tracking_number'], $order_id ); ?>

				<?php WCVendors_Pro_Tracking_Number_Form::date_shipped( $tracking_details['_wcv_date_shipped'], $order_id ); ?>

				<?php WCVendors_Pro_Tracking_Number_Form::form_data( $order_id, $button_text ); ?>

			</form>

		</div>
	</div>

</div>
