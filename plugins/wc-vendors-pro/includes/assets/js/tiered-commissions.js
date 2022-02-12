/**
 * Tiered Commission
 * https://www.wcvendors.com/
 */

window.TieredCommissions = window.TieredCommissions || {};

(function(window, document, $, plugin) {
	/* global commission_data */
	var $c = {};

	plugin.init = function() {
		plugin.cache();
		plugin.bindEvents();
		$(document).ready(function() {
			plugin.ready();
		});
	};

	plugin.ready = function() {
		$c.mainForm.parsley({
			excluded:
				'input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled], :hidden, #_wc_booking_qty'
		});
		plugin.checkCommissionTypes();
	};

	plugin.bindEvents = function() {
		$c.addRowButton.on('click', plugin.addRowHandler);
		$c.tableData.on('click', '.delete-tier', plugin.removeRowHandler);
		$c.tableData.on('change', plugin.checkCommissionTypes);
		$c.tableData.on('change', 'input, select', plugin.highlightError);
		$c.selector.on('change', plugin.cache);
		$c.mainForm.on('submit', plugin.checkIfTableIsEmpty);
	};

	plugin.cache = function() {
		$c.mainForm = $('.wcv_field');
		$c.tableData = $('.commission-tiers');
		$c.addRowButton = $('.insert-tier');
		$c.selector = $(plugin.getSelectorVar());
		if ($c.selector.length === 0) {
			$c.selector = $('#wcvendors_commission_type');
		}
		$c.tableId = '#wcvendors_commission_tier_' + $c.selector.val() + '_table';
	};

	plugin.randomColor = function() {
		return (
			'#' +
			Math.random()
				.toString(16)
				.slice(2, 8)
				.toUpperCase()
		);
	};

	plugin.checkCommissionTypesValues = function($ancestor, value) {
		switch (value) {
			case 'fixed':
				$ancestor
					.find('.percent, .fee')
					.attr('disabled', 'disabled')
					.attr('value', 0);
				$ancestor.find('.amount').removeAttr('disabled');
				break;
			case 'fixed_fee':
				$ancestor
					.find('.percent')
					.attr('disabled', 'disabled')
					.attr('value', 0);
				$ancestor.find('.amount, .fee').removeAttr('disabled');
				break;
			case 'percent':
				$ancestor
					.find('.amount, .fee')
					.attr('disabled', 'disabled')
					.attr('value', 0);
				$ancestor.find('.percent').removeAttr('disabled');
				break;
			case 'percent_fee':
				$ancestor
					.find('.amount')
					.attr('disabled', 'disabled')
					.attr('value', 0);
				$ancestor.find('.percent, .fee').removeAttr('disabled');
				break;
			default:
				$ancestor
					.find('.amount, .fee, .percent')
					.attr('disabled', 'disabled')
					.attr('value', 0);
				break;
		}
	};

	plugin.addRowHandler = function(e) {
		e.preventDefault();
		var commissions_row = $(this).data('row');
		$($c.tableId)
			.find('tbody')
			.append(commissions_row);
	};

	plugin.removeRowHandler = function(e) {
		e.preventDefault();
		if (confirm(commission_data.confirm_remove)) {
			$(this)
				.closest('tr')
				.remove();
		}
	};

	plugin.checkCommissionTypes = function() {
		$('.commission-types').each(function() {
			var $ancestor = $(this).closest('.tier-row');
			var value = $(this).val();

			plugin.checkCommissionTypesValues($ancestor, value);
		});
	};

	plugin.highlightError = function() {
		var $current_row = $(this).closest('.tier-row');
		var current_name = $current_row.find('.name').val();
		var current_rule = $current_row.find('.rule').val();
		var current_value = $current_row.find('.value').val();
		var current_amount = $current_row.find('.amount').val();
		var current_percent = $current_row.find('.percent').val();
		var current_fee = $current_row.find('.fee').val();

		var colors = [
			'#e96868',
			'#e74f4e',
			'#d9321f',
			'#c34242',
			'#b23d3c',
			'#a13736'
		];

		$('.tier-row')
			.not($(this))
			.each(function() {
				var this_name = $(this)
					.find('.name')
					.val();
				var this_rule = $(this)
					.find('.rule')
					.val();
				var this_value = $(this)
					.find('.value')
					.val();
				var this_amount = $(this)
					.find('.amount')
					.val();
				var this_percent = $(this)
					.find('.percent')
					.val();
				var this_fee = $(this)
					.find('.fee')
					.val();

				if ($current_row.index() != $(this).index()) {
					if (current_name == this_name) {
						$current_row
							.find('.name')
							.css('border', '1px solid ' + colors[0])
							.addClass('parsley-error');
						$(this)
							.find('.name')
							.css('border', '1px solid ' + colors[0]);
					} else {
						$current_row
							.find('.name')
							.css('border', 'none')
							.removeClass('parsley-error');
						$(this)
							.find('.name')
							.css('border', 'none')
							.removeClass('parsley-error');
					}

					if (current_value == this_value && current_rule == this_rule) {
						$current_row
							.find('.value, .rule')
							.css('border', '1px solid ' + colors[1])
							.addClass('parsley-error');
						$(this)
							.find('.value, .rule')
							.css('border', '1px solid ' + colors[1]);
						$(this)
							.find('.value, .rule')
							.data(
								'parsley-error-message',
								commission_data.possible_duplicate
							);
					} else {
						$current_row
							.find('.value, .rule')
							.css('border', 'none')
							.removeClass('parsley-error');
						$(this)
							.find('.value, .rule')
							.css('border', 'none')
							.removeClass('parsley-error');
					}

					if (
						this_amount == 0 &&
						this_fee == 0 &&
						this_percent == 0 &&
						current_amount == 0 &&
						current_fee == 0 &&
						current_percent == 0
					) {
						$current_row
							.find('.amount, .percent, .fee')
							.css('border', '1px solid ' + colors[2]);
						$(this)
							.find('.amount, .percent, .fee')
							.css('border', '1px solid ' + colors[2]);
						$(this)
							.find('.amount, .percent, .fee')
							.data('parsley-error-message', commission_data.cant_be_zero);
					} else {
						$current_row
							.find('.amount, .percent, .fee')
							.css('border', 'none')
							.removeClass('parsley-error');
						$(this)
							.find('.amount, .percent, .fee')
							.css('border', 'none')
							.removeClass('parsley-error');
					}

					if (
						current_amount == this_amount &&
						current_fee == this_fee &&
						current_percent == this_percent
					) {
						$current_row
							.find('.amount, .percent, .fee')
							.css('border', '1px solid ' + colors[3]);
						$(this)
							.find('.amount, .percent, .fee')
							.css('border', '1px solid ' + colors[3]);
						$(this)
							.find('.amount, .percent, .fee')
							.data('parsley-error-message', commission_data.conflicting_rows);
					} else {
						$current_row
							.find('.amount, .percent, .fee')
							.css('border', 'none')
							.removeClass('parsley-error');
						$(this)
							.find('.amount, .percent, .fee')
							.css('border', 'none')
							.removeClass('parsley-error');
					}
				}
			});
	};

	plugin.checkIfTableIsEmpty = function(e) {
		var rowCount = $($c.tableId + ' >tbody >tr').length;
		var selectedType = $c.selector.val();
		var commissionTypes = ['vendor_sales', 'product_sales', 'product_price'];

		if (!selectedType) {
			return;
		}

		if (-1 === commissionTypes.indexOf(selectedType)) {
			return;
		}

		if (rowCount == 0) {
			e.preventDefault();
			alert(commission_data.cant_be_empty);
		} else {
			$($c.tableId)
				.find('input')
				.removeAttr('disabled');
			plugin.removeOtherTiers();
		}
	};

	plugin.getSelectorVar = function() {
		// Global & product screen
		if ($('#wcv_commission_type').length) {
			return '#wcv_commission_type';
		}
		// user screen
		if ($('#_wcv_commission_type').length) {
			return '#_wcv_commission_type';
		}
	};

	plugin.removeOtherTiers = function() {
		var commissionTypes = ['vendor_sales', 'product_sales', 'product_price'];
		var selectedType = $c.selector.val();

		commissionTypes.forEach(function(item) {
			if (item == selectedType) {
				return;
			}
			$('#wcvendors_commission_tier_' + item)
				.find('.commission-tiers')
				.empty();
		});
	};

	$(plugin.init);
})(window, document, jQuery, window.TieredCommissions);
