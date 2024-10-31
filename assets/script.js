jQuery( function( $ ) {
	'use strict';

	$( document.body ).on( 'added_to_cart', function( e, fragments, cart_hash, $button ) {
		//for ajax add-to-cart, sessionstorage has been updated by the added_to_cart's handler in cart-fragment.js till this time b/c this js file is enqueued with a priority 100 while WC frontend JS is enqueued with prioity 10.
		//for non-ajax add-to-cart, fragment is refreshed by the cart-fragment.js b/c cart_hash differs b/w cookie and sessionstorage.
		if ( $button.hasClass( 'ajax_add_to_cart' ) && $button.data( 'mwca-url' ) ) {
			window.location = $button.data( 'mwca-url' );
		}
	} );
} );