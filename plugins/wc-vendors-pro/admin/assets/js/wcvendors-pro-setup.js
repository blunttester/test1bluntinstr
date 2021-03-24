/* */
jQuery(document).ready(function() {
	console.log(jQuery('#wcvendors_commission_type').val());

	// Global settings fields
	jQuery('#wcvendors_commission_type').on('change', function() {
		var commission_type = jQuery(this).val();
		var amount_row = jQuery('#wcvendors_commission_amount').closest('tr');
		var percent_row = jQuery('#wcvendors_vendor_commission_rate').closest('tr');
		var fee_row = jQuery('#wcvendors_commission_fee').closest('tr');

		toggle_commission_fields(commission_type, amount_row, percent_row, fee_row);
	});
	jQuery('#wcvendors_commission_type').change();

	function toggle_commission_fields(
		commission_type,
		amount_row,
		percent_row,
		fee_row
	) {
		switch (commission_type) {
			case 'fixed':
				amount_row.show();
				percent_row.hide();
				fee_row.hide();
				break;
			case 'fixed_fee':
				amount_row.show();
				fee_row.show();
				percent_row.hide();
				break;
			case 'percent':
				percent_row.show();
				amount_row.hide();
				fee_row.hide();
				break;
			case 'percent_fee':
				percent_row.show();
				fee_row.show();
				amount_row.hide();
				break;
			default:
				amount_row.hide();
				percent_row.hide();
				fee_row.hide();
		}
	}
});
