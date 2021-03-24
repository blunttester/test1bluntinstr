/* global WCVDeliveryI18n */
(function($) {
	function prepareItemsTable() {
		var $table = $('.woocommerce-table--order-details');
		var $tableContent = $table.find('tbody');

		if ($tableContent.find('tr').hasClass('cant-mark-received')) return;

		if ($tableContent.length == 0) return;

		var $rows = $tableContent.find('> tr');
		var items = {};
		var orderIdMatch = $rows
			.first()
			.attr('class')
			.match(/order-\d+/g);
		var orderId = orderIdMatch[0];

		orderId = orderId.replace('order-', '');

		if ($rows.length == 0) return;

		$rows.each(function() {
			var $this = $(this);
			var itemClass = $this.attr('class');
			var vendorIdMatch = itemClass.match(/vendor-\d+/g);
			var vendorId = vendorIdMatch[0];
			vendorId = vendorId.replace('vendor-', '');
			if (items[vendorId]) {
				items[vendorId].push($this);
			} else {
				items[vendorId] = [$this];
			}
		});

		if (items.length == 0) return;
		$table.find('thead tr').append('<th></th>');
		$tableContent.empty();
		$.each(items, function(vendorId, subItems) {
			for (var i = 0; i < subItems.length; i++) {
				var $item = subItems[i];
				if (i == 0 && !$item.hasClass('received')) {
					var currentURL = document.location.href;
					var args =
						'order=' +
						orderId +
						'&vendor=' +
						vendorId +
						'&redirect_url=' +
						encodeURIComponent(currentURL) +
						'&wcv_nonce=' +
						WCVDeliveryI18n.wcv_nonce;
					if (currentURL.indexOf('?') > 0) {
						currentURL += '&' + args;
					} else {
						currentURL += '?' + args;
					}
					$item.append(
						'<td rowspan="' +
							subItems.length +
							'">' +
							'<a class="woocommerce-button button wcv-mark-order-received" href="' +
							currentURL +
							'">' +
							WCVDeliveryI18n.buttonText +
							'</td>'
					);
				}
				if ($item.hasClass('received')) {
					$item.append('<td>' + WCVDeliveryI18n.receivedText + '</td>');
				}
				$tableContent.append($item);
			}
		});
	}

	prepareItemsTable();

	$('.wcv-mark-order-received').click(function(e) {
		if (e.target.href.indexOf('wcv_nonce') < 0) return;
		e.preventDefault();
		if (confirm(WCVDeliveryI18n.confirm)) {
			window.location.href = e.target.href;
		}
	});
})(jQuery);
