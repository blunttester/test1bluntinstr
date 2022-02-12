jQuery(
	function($) {
	$( document ).ready(
		function() {
		var days    =
			'<select name="days[]">' +
			'<option value="sunday">' +
			wcv_days.sunday +
			'</option>' +
			'<option value="monday">' +
			wcv_days.monday +
			'</option>' +
			'<option value="tuesday">' +
			wcv_days.tuesday +
			'</option>' +
			'<option value="wednesday">' +
			wcv_days.wednesday +
			'</option>' +
			'<option value="thursday">' +
			wcv_days.thursday +
			'</option>' +
			'<option value="friday">' +
			wcv_days.friday +
			'</option>' +
			'<option value="saturday">' +
			wcv_days.saturday +
			'</option>' +
			'<option disabled></option>' +
			'<option value="weekdays">' +
			wcv_days.weekdays +
			'</option>' +
			'<option value="weekend">' +
			wcv_days.weekend +
			'</option>' +
			'<option value="holidays">' +
			wcv_days.holidays +
			'</option>' +
			'</select>';
		var opening = '<select name="open[]">' + wcv_days.times + '</select>';
		var closing = '<select name="close[]">' + wcv_days.times + '</select>';

		var newrow =
			'<tr class="hours-row">' +
			'<td><input type="checkbox" name="status[]" class="status" value="1" checked/></td>' +
			'<td class="select-days"><label class="days-label"></label><input type="hidden" name="days[]" class="days-hidden" value="" /><span class="edit-days">' +
			days +
			'</span></td>' +
			'<td class="select-opening"><label class="open-label"></label><input type="hidden" name="open[]" class="open-hidden" value="" data-list="newday" /><span class="edit-opening">' +
			opening +
			'</span></td>' +
			'<td class="select-closing"><label class="close-label"></label><input type="hidden" name="close[]" class="close-hidden" value="" /><span class="edit-closing">' +
			closing +
			'</span></td>' +
			'<td><a href="#" data-action="edit" class="edit hidden">' +
			'<svg class="wcv-icon wcv-icon-md">' +
			'<use xlink:href="' +
			wcv_days.assets_url +
			'svg/wcv-icons.svg#wcv-icon-pen-square"></use>' +
			'</svg></a><a href="#" data-action="done" class="done">' +
			'<svg class="wcv-icon wcv-icon-md">' +
			'<use xlink:href="' +
			wcv_days.assets_url +
			'svg/wcv-icons.svg#wcv-icon-check-square"></use>' +
			'</svg></a><a href="#" class="remove-row">' +
			'<svg class="wcv-icon wcv-icon-md">' +
			'<use xlink:href="' +
			wcv_days.assets_url +
			'svg/wcv-icons.svg#wcv-icon-times"></use>' +
			'</svg></a></td>' +
			'</tr>';

		$( '#add-work-hours' ).click(
			function(event) {
			event.preventDefault();
			$( '#opening-hours' ).append( newrow );
		}
			);

		$( '#opening-hours' ).on(
			'click',
			'.remove-row',
			function(event) {
			if (confirm( wcv_days.confirm_remove )) {
					event.preventDefault();
					$( this )
					.parent()
					.parent()
					.remove();
			}
		}
			);

		$( '#opening-hours' ).on(
			'click',
			'.status',
			function(event) {
			if ($( this ).is( ':checked' )) {
					$( this ).val( 1 );
			} else {
					$( this ).val( 0 );
			}
		}
			);

		$( '#opening-hours' ).on(
			'click',
			'.done',
			function(event) {
			var $ancestor = $( this )
				.parent()
				.parent();

			var opening = $ancestor.find( '.edit-opening select' ).val();
			var closing = $ancestor.find( '.edit-closing select' ).val();

			opening =
				opening == 'open' || opening == 'closed'
					? ucFirst( wcv_days.open )
					: opening;
			closing =
				closing == 'open' || closing == 'closed'
					? ucFirst( wcv_days.closed )
					: closing;

			$ancestor
				.find( '.days-label' )
				.html( ucFirst( $ancestor.find( '.edit-days select' ).val() ) )
				.removeClass( 'hidden' );

			$ancestor
				.find( '.open-label' )
				.html( opening )
				.removeClass( 'hidden' );

			$ancestor
				.find( '.close-label' )
				.html( closing )
				.removeClass( 'hidden' );

			$ancestor
				.find( '.days-hidden' )
				.val( $ancestor.find( '.edit-days select' ).val() );

			$ancestor
				.find( '.open-hidden' )
				.val( $ancestor.find( '.edit-opening select' ).val() );

			$ancestor
				.find( '.close-hidden' )
				.val( $ancestor.find( '.edit-closing select' ).val() );

			$ancestor.find( '.done' ).addClass( 'hidden' );
			$ancestor.find( '.edit' ).removeClass( 'hidden' );
			$ancestor.find( '.remove-row' ).removeClass( 'hidden' );

			$ancestor.find( 'select' ).each(
				function() {
				$( this ).remove();
			}
				);

			event.preventDefault();
		}
			);

		$( '#opening-hours' ).on(
			'click',
			'.edit',
			function(event) {
			var $ancestor = $( this )
				.parent()
				.parent();

			$ancestor.find( '.done' ).removeClass( 'hidden' );
			$ancestor.find( '.edit' ).addClass( 'hidden' );
			$ancestor.find( '.remove-row' ).addClass( 'hidden' );
			$ancestor
				.find( '.edit-days' )
				.html( '' )
				.append( days );

			$ancestor
				.find( '.edit-opening' )
				.html( '' )
				.append( opening );

			$ancestor
				.find( '.edit-closing' )
				.html( '' )
				.append( closing );

			$ancestor.find( '.days-label' ).addClass( 'hidden' );
			$ancestor.find( '.open-label' ).addClass( 'hidden' );
			$ancestor.find( '.close-label' ).addClass( 'hidden' );

			$ancestor
				.find( '.edit-days select' )
				.val( $ancestor.find( '.days-hidden' ).val() );

			$ancestor
				.find( '.edit-opening select' )
				.val( $ancestor.find( '.open-hidden' ).val() );

			$ancestor
				.find( '.edit-closing select' )
				.val( $ancestor.find( '.close-hidden' ).val() );

			event.preventDefault();
		}
			);

		$( '#opening-hours' ).on(
			'change',
			'select',
			function(e) {
			var $ancestor = $( this )
				.parent()
				.parent()
				.parent();
			var value     = $( this ).val();

			var translatedValue = '';
			if (value == 'open') {
					translatedValue = wcv_days.open;
			}
			if (value == 'closed') {
					translatedValue = wcv_days.closed;
			}
			if (value == 'open' || value == 'closed') {
					$ancestor.find( '.close-hidden' ).val( value );
					$ancestor.find( '.close-label' ).html( ucFirst( translatedValue ) );
					$ancestor
					.find( '.edit-closing select' )
					.hide()
					.val( value );
			}
			e.preventDefault();
		}
			);

		var ucFirst = function(str) {
				var splitStr = str.toLowerCase().split( ' ' );
				for (var i = 0; i < splitStr.length; i++) {
					splitStr[i] =
					splitStr[i].charAt( 0 ).toUpperCase() + splitStr[i].substring( 1 );
					}

				return splitStr.join( ' ' );
		};
	}
		);
}
	);
