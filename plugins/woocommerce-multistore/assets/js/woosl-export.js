/* global $ms, woonet_woocommerce_orders_export */
;(function ( $, window, document, undefined ) {

	$( function() {
		$( "#export_time_after" ).datepicker();
		$( "#export_time_before" ).datepicker();

		jQuery( "#site_filter" ).select2( {
			placeholder: woonet_woocommerce_orders_export.site_filter_placeholder
		} );

		$( "#export_fields" ).accordion({
			collapsible: true,
			heightStyle: "content"
		});

		$( '.export_fields' ).controlgroup( {
			direction: "vertical"
		} );
		$( 'input', '.export_fields' ).checkboxradio({
			icon: false
		});

		$( 'span.dashicons', '#order_fields_selected' ).on('click', remove_sortable_export_field);

		$( 'input', '.export_fields' ).on( 'change', function() {
			const element_id = $( this ).data('name');

			if ( this.checked ) {
				add_sortable_export_field( this );
			} else {
				$( '#'+element_id ).remove();
			}

			$('#order_fields_selected').sortable( "refresh" );
		} );

		function add_sortable_export_field( element ) {
			const element_id = $( element ).data('name');

			var input = $('<input/>', {
				type: 'text',
				name: 'export_fields[' + element_id + ']',
				value: element_id
			});
			var div = $('<div/>', {
				id: element_id,
				class: 'ui-state-highlight'
			});

			div.append( 'Column name:' )
				.append( input )
				.append('<span>(' + $('.ui-accordion-header-active').text() + ' -> ' + $( element ).parent().text() + ')</span>')
				.append('<span class="dashicons dashicons-no-alt"></span>')
				.appendTo('#order_fields_selected');

			$('span.dashicons', '#'+element_id).on('click', remove_sortable_export_field);
		}

		function remove_sortable_export_field() {
			let sortable_export_field = $( this ).parent( 'div.ui-state-highlight' ),
				sortable_export_field_id = $( sortable_export_field ).attr( 'id' );

			$( 'input[data-name="' + sortable_export_field_id + '"]', '#export_fields' ).prop( "checked", false );
			$( 'input[data-name="' + sortable_export_field_id + '"]', '#export_fields' ).checkboxradio( "refresh" );

			$( sortable_export_field ).remove();

			$('#order_fields_selected').sortable( "refresh" );
		}

		$( '#order_fields_selected' ).sortable();
		$( '#order_fields_selected' ).disableSelection();

		$( '#form_data' ).submit( function( event) {
			if ( $( '.ui-sortable-handle', '#order_fields_selected' ).length === 0 ) {
				event.preventDefault();
				alert( 'Please select at least one field to export.' );
			}
		} );
	} );

})( $ms, window, document );
