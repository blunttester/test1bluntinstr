function woonet_checkbox( element ) {
	jQuery( element ).prev('input[type="hidden"]').val(
		jQuery( element ).is(":checked") ? 'yes' : 'no'
	);
}

;(function ( $, window, document, undefined ) {
	$( function() {

		// Quick edit
		$('#the-list').on('click', '.editinline', function () {
			inlineEditPost.revert();

			// show all republish fields
			$('p._woonet_publish_to', '.inline-edit-row').show();
			$('p._woonet_publish_to input', '.inline-edit-row').prop('checked', false);

			const post_id         = $(this).closest('tr').attr('id').replace('post-', ''),
				  $wm_inline_data = $('#woocommerce_multistore_inline_' + post_id);

				  console.log( $wm_inline_data );

			$('div', $wm_inline_data).each(function (index, element) {
				const name = $(element).attr('class'),
					  value = $(element).text();

				if ('_is_master_product' === name) {
					$('input[name="_is_master_product"]', '.inline-edit-row').val(value);
				} else if ('master_blog_id' === name) {
					$('input[name="master_blog_id"]', '.inline-edit-row').val(value);

					// hide republish settings for master blog
					$('p[data-group-id="' + value + '"]', '.inline-edit-row').hide();

					// show categories appropriate to selected blog
					if (typeof blog_categories !== 'undefined' && blog_categories) {
						$('ul.cat-checklist.product_cat-checklist', '.inline-edit-row').html(blog_categories[value]);
					}
				} else if ('product_blog_id' === name) {
					$('input[name="product_blog_id"]', '.inline-edit-row').val(value);
				} else {
					$('#' + name, '.inline-edit-row').prop('checked', 'yes' === value);
					$('#' + name, '.inline-edit-row').change( function() {
						woonet_checkbox(this);
					} );
					woonet_checkbox( $('#' + name, '.inline-edit-row') );
				}
			});
		});

		$('.inline-edit-row #woonet_toggle_all_sites').change(function () {
			const checked = $(this).is(":checked");

			$('.inline-edit-row .woonet_sites input[type="checkbox"]._woonet_publish_to').each(function () {
				if (jQuery(this).prop('disabled') === false) {
					jQuery(this).attr('checked', checked);
					jQuery(this).trigger('change');
				}
			});
		});

		// on
		$('.ptitle').on('focus', function (e) {
			const row = $(this).closest('tr.inline-editor');

			if ('yes' === $('input[name="_is_master_product"]', row).val()) {
				$("#woonet-quick-edit-fields-slave", row).remove();
			} else {
				$("#woonet-quick-edit-fields", row).remove();
			}

			$('input[name$="_child_stock_synchronize"]', row).prop('disabled', 'yes' === woonet_options['synchronize-stock']);
		});

		// Bulk edit
		$( '#wpbody' ).on( 'click', '#doaction, #doaction2', function() {
			// get action name
			const action = $( this ).is( '#doaction' ) ? $( '#bulk-action-selector-top' ).val() : $( '#bulk-action-selector-bottom' ).val();

			// do nothing if not bulk edit
			if ( 'edit' !== action ) {
				return true;
			}

			// clone multistore fields from "quick edit" form
			$('fieldset.woocommerce-multistore-fields', '#bulk-edit').remove();
			$('#woonet-quick-edit-fields', '#inline-edit').clone().attr("id","woonet-bulk-edit-fields").insertBefore( $( "div.submit", '#bulk-edit' ) );
			$('#woonet-quick-edit-fields-slave', '#inline-edit').clone().attr("id","woonet-bulk-edit-fields-slave").insertBefore( $( "div.submit", '#bulk-edit' ) );

			// show all republish fields
			$( 'p._woonet_publish_to', '.inline-edit-row' ).show();
			$( 'p._woonet_publish_to input', '.inline-edit-row' ).prop( 'checked', false );

			$('#woonet-bulk-edit-fields-slave').remove();

			// replace "inputs" with "selects"
			$( "input[type!='hidden']", '#bulk-edit p._woonet_publish_to, #bulk-edit #woonet-bulk-edit-fields-slave' ).replaceWith( function() {
				const options = [
					{ value : '',    text: '— Use Product Settings —' },
					{ value : 'yes', text: 'Yes' },
					{ value : 'no',  text: 'No' }
				];

				let $select = $('<select/>').attr( {
					'class': $( this ).attr('class'),
					'name': $( this ).attr('name')
				} );
				$( options ).each( function( index, option ) {
					$select.append( $('<option/>').attr( 'value', option.value ).text( option.text ) );
				});

				$select.change( function( element ) {
					jQuery( this ).prev('input[type="hidden"]').val(
						jQuery( this ).val()
					);
				} );

				return $select;
			} );

			//$('select[name$="_child_stock_synchronize"]', row ).prop('disabled', 'yes' === woonet_options['synchronize-stock']);

			// show categories appropriate to selected blog
			if ( typeof blog_categories !== 'undefined' && blog_categories ) {
				$( 'ul.cat-checklist.product_cat-checklist', '.inline-edit-row' ).html( blog_categories[ master_blog_ids[0] ] );
			}

			$( '#woonet_toggle_all_sites' ).change( function() {
				$( '#bulk-edit p._woonet_publish_to select._woonet_publish_to' ).val( $( this ).is( ':checked' ) ? 'yes' : '' );
				$( '#bulk-edit p._woonet_publish_to select._woonet_publish_to' ).trigger('change');
			});
		});

	} );
})( jQuery, window, document );

;(function ( $, window, document, undefined ) {
	$( function() {

		$('#doaction, #doaction2').click(function(e){
			let n      = $( this ).attr( 'id' ).substr( 2 ),
				action = $( 'select[name="' + n + '"]' ).val();

			if ( -1 !== $.inArray( action, ['edit', 'trash', 'untrash', 'delete'] ) ) {
				// for each selected product
				$( 'tbody th.check-column input[type="checkbox"]' ).each( function() {
					if ( $(this).prop('checked') ) {
						// get product master blog id
						let id              = $(this).val(),
							product_blog_id = $('div.product_blog_id', '#woocommerce_multistore_inline_' + id).text();

						$('<input>').attr({
							type: 'hidden',
							id: 'blog_ids',
							name: 'blog_ids[]',
							value: product_blog_id
						}).appendTo('#posts-filter');
					}
				} );
			}
		});

	} );
})( jQuery, window, document );
