/* global wcv_frontend_general */
/* global Cookies */
jQuery(function($) {
	window.Parsley.addValidator('price', {
		validateString: function(value) {
			var valid = true;
			var thousand_separator = wcv_frontend_general.thousand_separator;
			var decimal_separator = wcv_frontend_general.decimal_separator;
			var decimals = wcv_frontend_general.digits_after_decimal;
			var value_arr = value.split(decimal_separator);

			// Disallow thousand separator
			if (value.indexOf(thousand_separator) > -1) {
				return false;
			}

			// Disallow multiple decimal separators
			if (charCount(value, decimal_separator) > 1) {
				return false;
			}

			$.each(value_arr, function(index, val) {
				if (isNaN(val)) {
					valid = false;
					return false;
				}

				if (value_arr.length > 1 && index === value_arr.length - 1) {
					if (val.length === 0 || val.length > decimals) {
						valid = false;
						return false;
					}
				}
			});

			return valid;
		},
		messages: {
			en: wcv_frontend_general.invalid_price_format
		}
	});

	window.Parsley.addValidator('decimal', {
		validateString: function(value) {
			var valid = true;
			var decimal_separator = '.';
			var value_arr = value.split(decimal_separator);

			// Disallow multiple decimal separators
			if (charCount(value, decimal_separator) > 1) {
				return false;
			}

			$.each(value_arr, function(index, val) {
				if (isNaN(val)) {
					valid = false;
					return false;
				}

				if (
					value_arr.length > 1 &&
					index === value_arr.length - 1 &&
					val.length === 0
				) {
					valid = false;
					return false;
				}
			});

			return valid;
		},
		messages: {
			en: wcv_frontend_general.invalid_number_format
		}
	});

	var charCount = function(string, char) {
		var char_ount = 0;
		for (var position = 0; position < string.length; position++) {
			if (string.charAt(position) == char) {
				char_ount += 1;
			}
		}

		return char_ount;
	};

	// Iterate over all instances of the uploader on the page
	$('.wcv-img-id').each(function() {
		var id = this.id;

		// Handle Add banner
		$('.wcv-file-uploader-add' + id).on('click', function(e) {
			e.preventDefault();
			file_uploader(id);
			return false;
		});

		$('.wcv-file-uploader-delete' + id).on('click', function(e) {
			e.preventDefault();
			// reset the data so that it can be removed and saved.
			$('.wcv-file-uploader' + id).html('');
			$('input[id=' + id + ']').val('');
			$('.wcv-file-uploader-delete' + id).addClass('hidden');
			$('.wcv-file-uploader-reset' + id).addClass('hidden');
			$('.wcv-file-uploader-add' + id).removeClass('hidden');
		});
	});

	function file_uploader(id) {
		var $id = $('#' + id); // Cache this.
		var file_type = $id.data('type');
		var media_uploader, json;
		var library_type = '';

		if (undefined !== media_uploader) {
			media_uploader.open();
			return;
		}

		if (file_type === 'document') {
			library_type = 'application';
		} else if (file_type !== 'input') {
			library_type = file_type;
		}
		media_uploader = wp.media({
			title: $id.data('window_title'),
			button: {
				text: $id.data('save_button')
			},
			library: {
				type: library_type
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

			$id.val(json.id).change();
			$('.wcv-file-uploader-add' + id).addClass('hidden');
			$('.wcv-file-uploader-delete' + id).removeClass('hidden');

			if (file_type === 'image') {
				var attachment_image_url = json.sizes.thumbnail
					? json.sizes.thumbnail.url
					: json.url;

				$('.wcv-file-uploader' + id).append(
					'<img src="' +
						attachment_image_url +
						'" alt="' +
						json.caption +
						'" title="' +
						json.title +
						'" style="max-width: 100%;" />'
				);
				return;
			}

			if (
				file_type === 'video' ||
				file_type === 'audio' ||
				file_type === 'document'
			) {
				var data = {
					action: 'wcv_file_uploader_preview',
					file_url: json.url
				};
				// We can also pass the url value separately from ajaxurl for front end AJAX implementations
				$.post(wcv_frontend_general.ajax_url, data, function(res) {
					$('.wcv-file-uploader' + id).append(res);
				});
				return;
			}

			$('.wcv-file-uploader' + id).append(
				'<div class="control-group"><div class="control"><input type="text" value="' +
					json.url +
					'" readonly/></div></div>'
			);
		});

		media_uploader.open();
	}

	function shipping_address_other() {
		var shipping_from = $('#_wcv_shipping_from').val();

		if ('other' === shipping_from) {
			$('.shipping_other').show();
		} else {
			$('.shipping_other').hide();
		}
	}

	// Shipping from other address trigger
	$('select#_wcv_shipping_from')
		.change(function() {
			// Get value
			var select_val = $(this).val();

			if ('other' === select_val) {
				$('.shipping_other').show();
			} else {
				$('.shipping_other').hide();
			}
		})
		.change();

	// Flat Rates
	// National
	function enable_disable(disable_input, toggle_inputs) {
		if ($(disable_input).is(':checked')) {
			toggle_inputs.prop('disabled', true);

			toggle_inputs.each(function() {
				if ($(this).is(':checkbox')) {
					$(this).removeAttr('checked');
				} else {
					$(this).val('');
				}
			});
		} else {
			toggle_inputs.prop('disabled', false);
		}
	}

	// Disable national shipping
	// Toggle Free shipping
	$('#_wcv_shipping_fee_national_free').change(function() {
		enable_disable($(this), $('#_wcv_shipping_fee_national'));
	});
	$('#_wcv_shipping_fee_national_disable').change(function() {
		enable_disable($(this), $('.wcv-disable-national-input'));
	});

	// International
	// Free shipping
	$('#_wcv_shipping_fee_international_free').change(function() {
		enable_disable($(this), $('#_wcv_shipping_fee_international'));
	});

	// Disable international shipping
	$('#_wcv_shipping_fee_international_disable').change(function() {
		enable_disable($(this), $('.wcv-disable-international-input'));
	});

	shipping_address_other();

	// Vacation Mode
	$('.wcv-vacaction-mode').change(function() {
		$('.wcv-vacation-mode-msg-wrapper').slideToggle();
	});

	if ($('.wcv-vacaction-mode').is(':checked')) {
		$('.wcv-vacation-mode-msg-wrapper').show();
	}

	//Enable Google Analytics

	$('.wcv-ga-enable').change(function() {
		$('.wcv-ga-id-value-wrapper').slideToggle();
	});

	if ($('.wcv-ga-enable').is(':checked')) {
		$('.wcv-ga-id-value-wrapper').slideToggle();
	} else {
		$('.wcv-ga-id-value-wrapper').hide();
	}

	// Enable Opening hours
	$('.wcv-enable-opening-hours').change(function() {
		$('.wcv-opening-hours-wrapper').slideToggle();
	});

	if ($('.wcv-enable-opening-hours').is(':checked')) {
		$('.wcv-opening-hours-wrapper').show();
	} else {
		$('.wcv-opening-hours-wrapper').hide();
	}

	// Unique store name settings form
	$('#store_save_button').on('click', function(e) {
		e.preventDefault();

		var store_name = $('#_wcv_store_name').val();

		var data = {
			action: 'wcv_json_unique_store_name',
			store_name: store_name,
			security: wcv_frontend_general.wcv_json_unique_store_name_nonce
		};

		$.post(wcv_frontend_general.ajax_url, data, function(response) {
			if (response.error) {
				window.alert(response.error);
				return false;
			} else {
				$('#wcv-store-settings').submit();
			}
		});
	});

	// Enable new validation system
	$('.wcv-form').parsley({
		trigger: 'input',
		excluded:
			'.is_hidden, input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled]',
		classHandler: function(e) {
			if (e.$element.hasClass('wcv_category_check')) {
				return e.$element.closest('.wcv_terms_checklist_container');
			}
			if (e.$element.hasClass('select2-hidden-accessible')) {
				return e.$element.parent().find('.select2-selection');
			}
			return e;
		},
		errorsContainer: function(e) {
			if (e.$element.hasClass('wcv_category_check')) {
				return e.$element.closest('.wcv_terms_checklist_container').parent();
			}
			if (e.$element.hasClass('select2-hidden-accessible')) {
				return e.$element.parent();
			}
			return e;
		}
	});

	window.Parsley.on('field:error', function() {
		var $parent_tab_id = this.$element.closest('.tabs-content').attr('id');
		$('.wcv-tabs')
			.find('.' + $parent_tab_id)
			.addClass('tab-has-error');
	});

	window.Parsley.on('field:success', function() {
		var $parent_tab = this.$element.closest('.tabs-content');

		if ($parent_tab.find('.parsley-error').length > 0) {
			return;
		}

		var $parent_tab_id = $parent_tab.attr('id');
		$('.wcv-tabs')
			.find('.' + $parent_tab_id)
			.removeClass('tab-has-error');
	});

	// Check the value of that cookie and show/hide the notice accordingly
	if ('hidden' === Cookies.get('vendor_store_notice')) {
		$('.woocommerce-store-notice').hide();
	} else {
		$('.woocommerce-store-notice').show();
	}

	// Order form date picker clear button
	$('#clear_button').click(function(e) {
		e.preventDefault();

		var $startDateInput = $('#_wcv_order_start_date_input'),
			$endDateInput = $('#_wcv_order_end_date_input');

		$startDateInput.val($startDateInput.data('default'));
		$endDateInput.val($endDateInput.data('default'));

		$('.wcv-order-header .wcv-form').submit();
	});
});
