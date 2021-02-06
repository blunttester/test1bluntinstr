jQuery(document).ready(function($) {
	/**
	 * When clicking Checkout payment method, add active class and check radio button
	 */
	$( 'form.checkout, form#order_review' ).on( 'click', '.wc-checkout-sis-method', function( e ) {
		$( '.wc-checkout-sis-method.selected').removeClass( 'selected' );
		$( this ).addClass( 'selected' );
		$( 'input[type="radio"]', this ).prop( 'checked', true ).change();
	} );
});
