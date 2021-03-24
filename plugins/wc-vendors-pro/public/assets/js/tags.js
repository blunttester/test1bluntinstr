/*global wcv_tag_search_params */
jQuery(
	function($) {
	function getEnhancedSelectFormatString() {
			var formatString = {
				formatMatches: function(matches) {
					if (1 === matches) {
						return wcv_tag_search_params.i18n_matches_1;
					}

					return wcv_tag_search_params.i18n_matches_n.replace( '%qty%', matches );
				},
			formatNoMatches: function() {
					return wcv_tag_search_params.i18n_no_matches;
				},
			formatAjaxError: function() {
					return wcv_tag_search_params.i18n_ajax_error;
				},
			formatInputTooShort: function(input, min) {
					var number = min - input.length;

					if (1 === number) {
						return wcv_tag_search_params.i18n_input_too_short_1;
						}

					return wcv_tag_search_params.i18n_input_too_short_n.replace(
					'%qty%',
					number
					);
				},
			formatInputTooLong: function(input, max) {
					var number = input.length - max;

					if (1 === number) {
						return wcv_tag_search_params.i18n_input_too_long_1;
						}

					return wcv_tag_search_params.i18n_input_too_long_n.replace(
					'%qty%',
					number
					);
				},
			formatSelectionTooBig: function(limit) {
					if (1 === limit) {
						return wcv_tag_search_params.i18n_selection_too_long_1;
						}

					return wcv_tag_search_params.i18n_selection_too_long_n.replace(
					'%qty%',
					limit
					);
				},
			maximumSelected: function(input) {
					if (1 === input.maximum) {
						return wcv_tag_search_params.i18n_selection_too_long_1;
						}

					return wcv_tag_search_params.i18n_selection_too_long_n.replace(
					'%qty%',
					input.maximum
					);
				},
			formatLoadMore: function(pageNumber) {
					return wcv_tag_search_params.i18n_load_more;
				},
			formatSearching: function() {
					return wcv_tag_search_params.i18n_searching;
				}
				};

			return {
				language: formatString
				};
	}

	$( document.body ).on(
		'wcv-search-tag-init',
		function() {
		// Ajax product tag search box
		$( ':input.wcv-tag-search' )
			.filter( ':not(.enhanced)' )
			.each(
				function() {
				var select2_args = {
						allowClear: ! ! $( this ).data( 'allow_clear' ),
						placeholder: $( this ).data( 'placeholder' ),
						tags: $( this ).data( 'tags' ),
						tokenSeparators: wcv_tag_search_params.separator,
						minimumInputLength: $( this ).data( 'minimum_input_length' )
						? $( this ).data( 'minimum_input_length' )
						: '2',
						maximumSelectionLength: wcv_tag_search_params.tag_limit,
						escapeMarkup: function(m) {
							return m;
							},
					ajax: {
						url: wcv_tag_search_params.ajax_url,
						dataType: 'json',
						delay: 250,
						data: function(params) {
							return {
								term: params.term,
								action: $( this ).data( 'action' ) || 'wcv_json_search_tags',
								security: wcv_tag_search_params.nonce
							};
						},
						processResults: function(data) {
							var terms = [];
							if (data) {
								$.each(
									data,
									function(id, text) {
									terms.push( { id: id, text: text } );
								}
									);
							}
							return { results: terms };
						},
						cache: true
						}
				};

				select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

				$( this )
					.select2( select2_args )
					.addClass( 'enhanced' );
			}
				);
	}
		);

	$( document ).ready(
		function() {
		$( document.body ).trigger( 'wcv-search-tag-init' );
	}
		);
}
	);
