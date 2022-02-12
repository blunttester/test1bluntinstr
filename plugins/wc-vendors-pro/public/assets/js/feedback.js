(function($) {
	$(window).on('load', function() {
		$.each($('.star-rating-input'), function() {
			var star_rating = $(this).val();
			var fn = $(this).data('fn');
			starClosed(fn, star_rating);
		});

		$('.wcv-form').on('submit', function(event) {
			var valid_stars = true;
			$.each($('.star-rating-input'), function() {
				if ($(this).val() == '') {
					valid_stars = false;
					alert(window.wcv_frontend_feedback.select_stars_message);
					event.preventDefault();
					return false;
				}
			});

			return valid_stars;
		});

		$('.star-icon').on('click', function(e) {
			e.preventDefault();
			var star_rating = $(this).data('index');
			var fn = $(this).data('fn');
			$('#wcv-star-rating-' + fn + '-input').val(star_rating);

			starOpen(fn);
			starClosed(fn, star_rating);
		});

		$('.star-icon').on('mouseover', function(e) {
			e.preventDefault();
			var star_rating = $(this).data('index');
			var fn = $(this).data('fn');

			starOpen(fn);
			starClosed(fn, star_rating);
		});

		$('.wcv_star-rating-container').on('mouseleave', function(e) {
			e.preventDefault();
			var fn = $(this).data('fn');
			var star_rating = $('#wcv-star-rating-' + fn + '-input').val();

			starOpen(fn);

			if (star_rating != undefined) {
				starClosed(fn, star_rating);
			}
		});

		function starClosed(fn, star_rating) {
			for (var i = 1; i <= star_rating; i++) {
				$('#star-' + fn + '-' + i)
					.find('use')
					.attr(
						'xlink:href',
						$('#wcv-star-rating-' + fn + '-label').data('star-closed')
					);
			}
		}

		function starOpen(fn) {
			$('#wcv-star-rating-' + fn + '-label')
				.find('use')
				.attr(
					'xlink:href',
					$('#wcv-star-rating-' + fn + '-label').data('star-open')
				);
		}
	});
})(jQuery);
