jQuery(
	function($) {
	jQuery( 'a.wcv_order_note' ).on(
		'click',
		function(e) {
		e.preventDefault();
		$( '.add_note_' + $( this ).data( 'order_id' ) ).slideToggle();
	}
		);

	jQuery( 'a.mark-order-shipped' ).on(
		'click',
		function(e) {
		if ( ! confirm( window.wcv_pro_order.confirm_shipped )) {
				e.preventDefault();
		}
	}
		);
}
	);
