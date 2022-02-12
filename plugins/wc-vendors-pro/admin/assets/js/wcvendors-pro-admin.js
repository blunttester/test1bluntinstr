(function($) {
	'use strict';

	// Product Commission fields
	$('#_wcv_commission_type').on('change', function() {
		var commission_type = $(this).val();
		var amount_row = $('._wcv_commission_amount_input');
		var percent_row = $('._wcv_commission_percent_input');
		var fee_row = $('._wcv_commission_fee_input');
		var vendor_sales = $('#wcvendors_commission_tier_vendor_sales');
		var product_sales = $('#wcvendors_commission_tier_product_sales');
		var product_price = $('#wcvendors_commission_tier_product_price');

		toggle_commission_fields(
			commission_type,
			amount_row,
			percent_row,
			fee_row,
			vendor_sales,
			product_sales,
			product_price
		);
	});
	$('#_wcv_commission_type').change();

	// Product Commission fields
	$('#wcv_commission_type').on('change', function() {
		var commission_type = $(this).val();
		var amount_row = $('.wcv_commission_amount_input');
		var percent_row = $('.wcv_commission_percent_input');
		var fee_row = $('.wcv_commission_fee_input');
		var vendor_sales = $('#wcvendors_commission_tier_vendor_sales');
		var product_sales = $('#wcvendors_commission_tier_product_sales');
		var product_price = $('#wcvendors_commission_tier_product_price');

		toggle_commission_fields(
			commission_type,
			amount_row,
			percent_row,
			fee_row,
			vendor_sales,
			product_sales,
			product_price
		);
	});
	$('#wcv_commission_type').change();

	// Global settings fields
	$('#wcvendors_commission_type').on('change', function() {
		var commission_type = $(this).val();
		var amount_row = $('#wcvendors_commission_amount').closest('tr');
		var percent_row = $('#wcvendors_vendor_commission_rate').closest('tr');
		var fee_row = $('#wcvendors_commission_fee').closest('tr');
		var vendor_sales = $('#wcvendors_commission_tier_vendor_sales');
		var product_sales = $('#wcvendors_commission_tier_product_sales');
		var product_price = $('#wcvendors_commission_tier_product_price');

		toggle_commission_fields(
			commission_type,
			amount_row,
			percent_row,
			fee_row,
			vendor_sales,
			product_sales,
			product_price
		);
	});
	$('#wcvendors_commission_type').change();

	function toggle_commission_fields(
		commission_type,
		amount_row,
		percent_row,
		fee_row,
		vendor_sales,
		product_sales,
		product_price
	) {
		switch (commission_type) {
			case 'fixed':
				amount_row.show();
				percent_row.hide();
				fee_row.hide();
				vendor_sales.hide();
				product_sales.hide();
				product_price.hide();
				break;
			case 'fixed_fee':
				amount_row.show();
				fee_row.show();
				percent_row.hide();
				vendor_sales.hide();
				product_sales.hide();
				product_price.hide();
				break;
			case 'percent':
				percent_row.show();
				amount_row.hide();
				fee_row.hide();
				vendor_sales.hide();
				product_sales.hide();
				product_price.hide();
				break;
			case 'percent_fee':
				percent_row.show();
				fee_row.show();
				amount_row.hide();
				vendor_sales.hide();
				product_sales.hide();
				product_price.hide();
				break;
			case 'vendor_sales':
				vendor_sales.show();
				percent_row.hide();
				fee_row.hide();
				amount_row.hide();
				product_sales.hide();
				product_price.hide();
				break;
			case 'product_sales':
				product_sales.show();
				percent_row.hide();
				fee_row.hide();
				amount_row.hide();
				vendor_sales.hide();
				product_price.hide();
				break;
			case 'product_price':
				product_price.show();
				percent_row.hide();
				fee_row.hide();
				amount_row.hide();
				vendor_sales.hide();
				product_sales.hide();
				break;
			default:
				amount_row.hide();
				percent_row.hide();
				fee_row.hide();
				vendor_sales.hide();
				product_sales.hide();
				product_price.hide();
		}
	}

	// Product SEO
	$('#wcvendors_hide_product_seo').on('change', function() {
		if ($(this).is(':checked')) {
			$(this)
				.parent()
				.parent()
				.parent()
				.find('.wcv_admin_checkbox')
				.not($(this))
				.attr('disabled', 'disabled')
				.attr('checked', false);
			$(this).removeAttr('disabled');
		} else {
			$(this)
				.parent()
				.parent()
				.parent()
				.find('.wcv_admin_checkbox')
				.removeAttr('disabled');
		}
	});

	if ($('.wcv-file-uploader_wcv_store_banner_id').find('img').length > 0) {
		$('#_wcv_add_wcv_store_banner_id').hide();
	} else {
		$('#_wcv_remove_wcv_store_banner_id').hide();
	}

	if ($('.wcv-file-uploader_wcv_store_icon_id').find('img').length > 0) {
		$('#_wcv_add_wcv_store_icon_id').hide();
	} else {
		$('#_wcv_remove_wcv_store_icon_id').hide();
	}

	// Handle Add banner
	$('#_wcv_add_wcv_store_banner_id').on('click', function(e) {
		e.preventDefault();
		file_uploader('_wcv_store_banner_id');
		return false;
	});

	// Handle remove banner
	$('#_wcv_remove_wcv_store_banner_id').on('click', function(e) {
		e.preventDefault();
		// reset the data so that it can be removed and saved.
		var upload_notice = $('#_wcv_store_banner_id').data('upload_notice');
		$('.wcv-file-uploader_wcv_store_banner_id').html('');
		$('.wcv-file-uploader_wcv_store_banner_id').append(upload_notice);
		$('#_wcv_store_banner_id').val('');
		$('#_wcv_add_wcv_store_banner_id').show();
		$('#_wcv_remove_wcv_store_banner_id').hide();
	});

	// Handle reset banner
	$('.wcv-reset-store-banner').on('click', function(e) {
		e.preventDefault();

		var default_image_url = $(this).data('default-url');
		var input_field_id = $(this).data('field-id');

		$('img.wcv-image-container-' + input_field_id).attr(
			'src',
			default_image_url
		);
		$('#' + input_field_id).val(default_image_url);
	});

	// Handle Add Store Icon
	$('#_wcv_add_wcv_store_icon_id').on('click', function(e) {
		e.preventDefault();
		file_uploader('_wcv_store_icon_id');
		return false;
	});

	$('#_wcv_remove_wcv_store_icon_id').on('click', function(e) {
		e.preventDefault();
		// reset the data so that it can be removed and saved.
		var upload_notice = $('#_wcv_store_icon_id').data('upload_notice');
		$('.wcv-file-uploader_wcv_store_icon_id').html('');
		$('.wcv-file-uploader_wcv_store_icon_id').append(upload_notice);
		$('#_wcv_store_icon_id').val('');
		$('#_wcv_add_wcv_store_icon_id').show();
		$('#_wcv_remove_wcv_store_icon_id').hide();
	});

	//
	// Generic Image handler for backend
	//

	$('.wcv-file-uploader-img').each(function() {
		if ($(this).find('img').length > 0) {
			$(this)
				.nextAll('.wcv_add_image_id')
				.hide();
		} else {
			$(this)
				.nextAll('.wcv_remove_image_id')
				.hide();
		}
	});

	// Handle Add Image
	$('.wcv_add_image_id').on('click', function(e) {
		e.preventDefault();
		var image_key = $(this).data('key');
		file_uploader(image_key);
		return false;
	});

	$('.wcv_remove_image_id').on('click', function(e) {
		e.preventDefault();
		var image_key = $(this).data('key');
		// reset the data so that it can be removed and saved.
		var upload_notice = $('#' + image_key).data('upload_notice');
		$('.wcv-file-uploader' + image_key).html('');
		$('.wcv-file-uploader' + image_key).append(upload_notice);
		$('#' + image_key).val('');
		$('#_wcv_add' + image_key).show();
		$('#_wcv_remove' + image_key).hide();
	});

	function file_uploader(id) {
		var media_uploader, json, attachment_image_url;

		if (undefined !== media_uploader) {
			media_uploader.open();
			return;
		}

		media_uploader = wp.media({
			title: $('#' + id).data('window_title'),
			button: {
				text: $('#' + id).data('save_button')
			},
			multiple: false // Set to true to allow multiple files to be selected
		});

		media_uploader.on('select', function() {
			json = media_uploader
				.state()
				.get('selection')
				.first()
				.toJSON();

			if (0 > $.trim(json.url.length)) {
				return;
			}

			attachment_image_url = json.sizes.thumbnail
				? json.sizes.thumbnail.url
				: json.url;

			$('.wcv-file-uploader' + id).html(
				'<img src="' +
					attachment_image_url +
					'" alt="' +
					json.caption +
					'" title="' +
					json.title +
					'" style="max-width: 100%;" />'
			);

			$('#' + id).val(json.id);

			$('#_wcv_add' + id).hide();
			$('#_wcv_remove' + id).show();
		});

		media_uploader.open();
	}

	// Show / Hide shipping types for the user edit screen
	if (window.wcv_admin.screen_id == 'user-edit') {
		// Hide both shipping rates
		$('.wcv-shipping-rates').hide();
		// Show the global current one
		$('.wcv-shipping-' + window.wcv_admin.current_shipping_type).show();

		// If there is a vendor shipping override change that here.
		var previous = window.wcv_admin.current_shipping_type;

		$('.wcv-shipping-type')
			.on('focus', function() {
				if ($(this).val() !== '') previous = $(this).val();
			})
			.change(function() {
				var shipping_type = $(this).val();
				$('.wcv-shipping-' + shipping_type).show();
				$('.wcv-shipping-' + previous).hide();
				if ($(this).val() === '')
					$('.wcv-shipping-' + window.wcv_admin.global_shipping_type).show();
				previous = shipping_type;
			});
	}

	// Show / Hide shipping types for the product edit screen
	if (window.wcv_admin.screen_id == 'product') {
		$('.wcv-shipping-' + window.wcv_admin.current_shipping_type).show();
	}

	var select2_args = { placeholderOption: 'first', width: '100%' };

	// Country Rates
	$('#shipping').on('click', '.wcv_shipping_rates a.insert', function() {
		$(this)
			.closest('.wcv_shipping_rates')
			.find('tbody')
			.append($(this).data('row'))
			.find('select')
			.select2(select2_args);
		return false;
	});

	$('#shipping').on('click', '.wcv_shipping_rates a.delete', function() {
		$(this)
			.closest('tr')
			.remove();
		return false;
	});

	// shipping rate ordering
	$('.wcv_shipping_rates tbody').sortable({
		items: 'tr',
		cursor: 'move',
		axis: 'y',
		handle: 'td.sort',
		scrollSensitivity: 40,
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65
	});

	// Global shipping settings
	$('.wcv-shipping-system').on('change', function() {
		if ($(this).val() == 'flat') {
			$('.wcv-flat-rate').each(function() {
				$(this)
					.closest('tr')
					.show();
			});

			$('.wcv_country_rate_table').hide();
		} else {
			$('.wcv-flat-rate').each(function() {
				$(this)
					.closest('tr')
					.hide();
			});

			$('.wcv_country_rate_table').show();
		}
	});

	if ($('.wcv-shipping-system').val() == 'flat') {
		$('.wcv-flat-rate').each(function() {
			$(this)
				.closest('tr')
				.show();
		});

		$('.wcv_country_rate_table').hide();
	} else {
		$('.wcv-flat-rate').each(function() {
			$(this)
				.closest('tr')
				.hide();
		});
	}

	$(window).on('load', function() {
		$('#shipping')
			.find('select')
			.each(function() {
				$(this).select2(select2_args);
			});
	});

	// Form fields required check box hiding code
	$('.wcv_admin_checkbox').on('change', function() {
		var field_id = this.id;
		var required_field_id = field_id.replace('hide', 'required');

		if (field_id.toLowerCase().indexOf('hide') >= 0) {
			if (this.checked) {
				$('#' + required_field_id).attr('disabled', true);
			} else {
				$('#' + required_field_id).removeAttr('disabled');
			}
		}
	});
	$('.wcv_admin_checkbox').change();

	$('#wcv_product_totals_chart_use_random_colors').click(function() {
		$('#wcv_product_totals_chart_use_random_in_range').attr('checked', false);
	});
})(jQuery);
