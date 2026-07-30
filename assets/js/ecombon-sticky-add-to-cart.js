( function ( $ ) {
	'use strict';

	// Real sticky add-to-cart bar (template-parts/product/sticky-add-to-cart.php).
	// No add-to-cart logic lives here — the sticky button is a real
	// `<button form="ecombon-add-to-cart-form">`, natively associated with
	// the real `form.cart` below regardless of where it sits in the DOM, so
	// clicking it submits that real form (with whatever quantity the
	// shopper already set in the real buy-box above) exactly like the real
	// button would. This script only: (1) makes sure the real form
	// actually has that id, and (2) for a variable product, mirrors the
	// real live price/variant selection into the sticky bar.
	$( function () {
		var $stickyBar = $( '.sticky-btn-atc' );

		if ( ! $stickyBar.length ) {
			return;
		}

		var $realForm = $( 'form.cart' ).first();

		if ( $realForm.length && ! $realForm.attr( 'id' ) ) {
			$realForm.attr( 'id', 'ecombon-add-to-cart-form' );
		}

		// Variable products only: mirror the real, already-matched variation
		// (see assets/js/ecombon-product-variations.js, which fires this
		// same real WooCommerce event) into the sticky price and a
		// "Color, Size" style summary line built from the real swatch/pill
		// labels it builds — never a second, separate lookup.
		var $variationsForm  = $( '.variations_form' );
		var $stickyPrice     = $stickyBar.find( '.sticky-atc-price' );
		var $stickyVariantDesc = $stickyBar.find( '.sticky-atc-variant-desc' );
		var originalPriceHtml = $stickyPrice.html();

		if ( $variationsForm.length && $stickyPrice.length ) {
			function updateVariantDesc() {
				var parts = [];

				$( '.variant-picker-label-value' ).each( function () {
					var text = $.trim( $( this ).text() );

					if ( text ) {
						parts.push( text );
					}
				} );

				$stickyVariantDesc.text( parts.join( ', ' ) );
			}

			$variationsForm.on( 'found_variation', function ( event, variation ) {
				$stickyPrice.html( variation.price_html );
				updateVariantDesc();
			} );

			$variationsForm.on( 'reset_data hide_variation', function () {
				$stickyPrice.html( originalPriceHtml );
				updateVariantDesc();
			} );

			$variationsForm.on( 'woocommerce_update_variation_values', updateVariantDesc );
		}
	} );
} )( jQuery );
