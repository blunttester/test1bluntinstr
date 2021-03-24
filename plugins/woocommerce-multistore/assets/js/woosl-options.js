;(function ( $, window, document, undefined ) {

	$( function() {
		jQuery( '.tips' ).tipTip( {
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200
		} );

		var tabs = $( "#fields-control" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
		$( "#fields-control li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
		tabs.find( ".ui-tabs-nav" ).sortable({
			axis: "y",
			stop: function() {
				tabs.tabs( "refresh" );
			}
		});
	} );
})( $ms, window, document );


(function($) {
	$('.woomulti_option_with_warning').live('change', function() {
		if ( this.value == 'yes' ) {
			$('.woomulti_options_warning', $(this).parent().parent()).show();
		} else {
			$('.woomulti_options_warning', $(this).parent().parent()).hide();
		}
	});
})( jQuery );
