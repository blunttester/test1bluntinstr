/*global wcv_product_export_params */
(function($, window) {
	/**
	 * productExportForm handles the export process.
	 */
	var productExportForm = function($form) {
		this.$form = $form;
		this.xhr = false;

		// Initial state.
		this.$form.find('.wcv-exporter-progress').val(0);

		// Methods.
		this.processStep = this.processStep.bind(this);

		// Events.
		$form.on('submit', { productExportForm: this }, this.onSubmit);
		$form
			.find('.wcv-exporter-types')
			.on('change', { productExportForm: this }, this.exportTypeFields);
	};

	/**
	 * Handle export form submission.
	 */
	productExportForm.prototype.onSubmit = function(event) {
		event.preventDefault();

		var currentDate = new Date(),
			day = currentDate.getDate(),
			month = currentDate.getMonth() + 1,
			year = currentDate.getFullYear(),
			timestamp = currentDate.getTime(),
			filename =
				'product-export-' +
				day +
				'-' +
				month +
				'-' +
				year +
				'-' +
				timestamp +
				'.csv';

		event.data.productExportForm.$form.addClass('wcv-exporter_exporting');
		event.data.productExportForm.$form.find('.wcv-exporter-progress').val(0);
		event.data.productExportForm.$form
			.find('.wcv-exporter-button')
			.prop('disabled', true);
		event.data.productExportForm.processStep(
			1,
			$(this).serialize(),
			'',
			filename
		);
	};

	/**
	 * Process the current export step.
	 */
	productExportForm.prototype.processStep = function(
		step,
		data,
		columns,
		filename
	) {
		var $this = this,
			selected_columns = $('.wcv-exporter-columns').val(),
			export_meta = $('#wcv-exporter-meta:checked').length ? 1 : 0,
			export_types = $('.wcv-exporter-types').val(),
			export_category = $('.wcv-exporter-category').val();

		$.ajax({
			type: 'POST',
			url: wcv_product_export_params.ajax_url,
			data: {
				form: data,
				action: 'wcv_do_ajax_product_export',
				step: step,
				columns: columns,
				selected_columns: selected_columns,
				export_meta: export_meta,
				export_types: export_types,
				export_category: export_category,
				filename: filename,
				security: wcv_product_export_params.export_nonce
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					if ('done' === response.data.step) {
						$this.$form
							.find('.wcv-exporter-progress')
							.val(response.data.percentage);
						window.location = response.data.url;
						setTimeout(function() {
							$this.$form.removeClass('wcv-exporter__exporting');
							$this.$form.find('.wcv-exporter-button').prop('disabled', false);
						}, 2000);
					} else {
						$this.$form
							.find('.wcv-exporter-progress')
							.val(response.data.percentage);
						$this.processStep(
							parseInt(response.data.step, 10),
							data,
							response.data.columns,
							filename
						);
					}
				}
			}
		}).fail(function(response) {
			window.console.log(response);
		});
	};

	/**
	 * Handle fields per export type.
	 */
	productExportForm.prototype.exportTypeFields = function() {
		var exportCategory = $('.wcv-exporter-category');

		if (-1 !== $.inArray('variation', $(this).val())) {
			exportCategory.closest('tr').hide();
		} else {
			exportCategory.closest('tr').show();
		}
	};

	/**
	 * Function to call productExportForm on jquery selector.
	 */
	$.fn.wcv_product_export_form = function() {
		new productExportForm(this);
		return this;
	};

	$('.wcv-exporter').wcv_product_export_form();
})(jQuery, window);
