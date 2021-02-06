
/* storechild.js */

(function() {

	// Add classes to homepage product sections
	jQuery( '.page-template-template-homepage .storefront-product-section' ).each( function ( i ) {
		i += 1;

		jQuery( this ).addClass( 'position-' + i );
	});

	var homepage_product_sections = jQuery( '.page-template-template-homepage .storefront-product-section' ).length;

	jQuery( '.page-template-template-homepage .storefront-product-section' ).first().addClass( 'first' );

	if ( homepage_product_sections > 4 ) {
		jQuery( '.page-template-template-homepage .storefront-product-section' ).last().addClass( 'last' );
	}

	jQuery( '.page-template-template-homepage .site-main .storefront-product-section.first.storefront-product-categories ul.products li.product' ).removeClass( 'first last' );

	// Wrap the first word of certain titles in a span
	jQuery( '.storefront-product-section .section-title, .widget-area .widget .widget-title, .widget-area .widget .widgettitle' ).each( function() {
		var html = jQuery( this ).html();
		var word = html.substr( 0, html.indexOf( ' ' ) );
		var rest = html.substr( html.indexOf( ' ' ) );

		if ( html.indexOf( ' ' ) > -1 ) {
			jQuery( this ).html( rest ).prepend( jQuery( '<span/>' ).html( word ) );
		}
	});
}).call( this );