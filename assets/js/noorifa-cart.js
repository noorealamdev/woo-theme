( function ( $ ) {
	'use strict';

	// WooCommerce's own add-to-cart.js also listens for these events and
	// replaces #shoppingCart with fresh markup (see
	// Noorifa\WooCommerce\CartFragments) — but jQuery doesn't guarantee
	// that handler runs before this one just because its script loads
	// first. Deferring to the next tick lets every same-event handler
	// finish first, so we always grab the fragment-replaced node.
	//
	// `added_to_cart` opens the drawer; `removed_from_cart` (real AJAX
	// removal via `.remove_from_cart_button`, same script) re-shows it
	// after the fragment swap instead, since it was already open.
	//
	// Not on the real cart page itself, though (WordPress's own real
	// `body_class()` marks it with `.woocommerce-cart`) — popping a
	// mini-cart preview open on top of the full cart page you're already
	// looking at is redundant, and the page's own table/totals already
	// refresh in place via the fragments CartFragments adds there.
	$( document.body ).on( 'added_to_cart removed_from_cart', function () {
		if ( document.body.classList.contains( 'woocommerce-cart' ) ) {
			return;
		}

		setTimeout( function () {
			var el = document.getElementById( 'shoppingCart' );

			if ( ! el || typeof bootstrap === 'undefined' ) {
				return;
			}

			// WooCommerce's fragment swap replaces #shoppingCart's whole
			// DOM node (see Noorifa\WooCommerce\CartFragments), so the
			// Offcanvas instance that was open on the OLD node gets
			// orphaned rather than closed: the old node is simply gone,
			// with nothing left to call .hide() on. That leaves two
			// things stuck behind, since only the instance that applied a
			// lock ever releases it, and this one never gets the chance:
			// its backdrop, and the body scroll-lock (Bootstrap sees the
			// lock is already applied when the fresh instance below
			// shows, so that new instance never takes responsibility for
			// releasing it either — it would otherwise sit locked forever
			// after this drawer is later closed). getOrCreateInstance()
			// below only ever creates a fresh instance for the NEW node,
			// so anything already in the DOM at this point is guaranteed
			// to be one of these orphans.
			document.querySelectorAll( '.offcanvas-backdrop' ).forEach( function ( backdrop ) {
				backdrop.remove();
			} );
			document.body.style.removeProperty( 'overflow' );
			document.body.style.removeProperty( 'padding-right' );

			bootstrap.Offcanvas.getOrCreateInstance( el ).show();
		}, 0 );
	} );

	// +/- buttons around WooCommerce's own real `.qty` input (see
	// Noorifa\WooCommerce\QuantityStepper) — only ever touches that one
	// real input's value, respecting its actual min/max/step attributes.
	$( document ).on( 'click', '.btn-quantity', function () {
		var $qty = $( this ).closest( '.quantity' ).find( '.qty' );

		if ( ! $qty.length ) {
			return;
		}

		var step = parseFloat( $qty.attr( 'step' ) ) || 1;
		var min  = parseFloat( $qty.attr( 'min' ) );
		var max  = parseFloat( $qty.attr( 'max' ) );
		var value = parseFloat( $qty.val() ) || 0;

		value += $( this ).hasClass( 'btn-increase' ) ? step : -step;

		if ( ! isNaN( min ) ) {
			value = Math.max( min, value );
		}
		if ( ! isNaN( max ) ) {
			value = Math.min( max, value );
		}

		$qty.val( value ).trigger( 'change' );

		// On the real cart page, also trigger WooCommerce's own real
		// AJAX quantity update (wc-cart.js) — its `change` handler only
		// *enables* the (hidden) "Update cart" button rather than
		// auto-submitting, since it's built for an explicit click; a
		// stepper click is that explicit action here, so finish the job.
		var $cartForm = $qty.closest( 'form.woocommerce-cart-form' );

		if ( $cartForm.length ) {
			$cartForm.find( ':input[name="update_cart"]' ).prop( 'disabled', false ).trigger( 'click' );
		}
	} );

	// AJAX-ify the single product page's real add-to-cart form (simple and
	// variable), so it behaves like the shop grid: no page reload, drawer
	// opens via the same `added_to_cart` event WooCommerce's own
	// add-to-cart.js already listens for. WooCommerce doesn't ajaxify this
	// form itself — only archive/loop "Add to cart" buttons.
	//
	// The form also carries a second real submit button — "Buy It Now"
	// (name="noorifa_buy_now") — for a real add-to-cart-then-redirect flow
	// that works with no JS at all via Noorifa\WooCommerce\BuyItNow's
	// `woocommerce_add_to_cart_redirect` filter. `event.originalEvent.submitter`
	// (standard, evergreen-browser API) tells us which button actually
	// triggered this submit, since both share `.single_add_to_cart_button`.
	$( document ).on( 'submit', 'form.cart', function ( e ) {
		var $form = $( this );
		var submitter = e.originalEvent && e.originalEvent.submitter;
		var isBuyNow = !! submitter && 'noorifa_buy_now' === submitter.name;
		var $button = submitter ? $( submitter ) : $form.find( '.single_add_to_cart_button' ).first();

		// Grouped-product forms have one quantity input per child product;
		// this endpoint only supports a single product/variation at a time.
		var $quantity = $form.find( 'input[name="quantity"]' );

		if (
			typeof wc_add_to_cart_params === 'undefined' ||
			1 !== $quantity.length ||
			$button.is( ':disabled' ) ||
			$button.hasClass( 'disabled' ) ||
			$button.hasClass( 'loading' )
		) {
			return; // Let it submit normally.
		}

		var $variationIdField = $form.find( 'input[name="variation_id"]' );
		var variationId = parseInt( $variationIdField.val(), 10 ) || 0;

		// A variable-product form with no variation chosen yet: let
		// WooCommerce's own click handler run its "please make a
		// selection" alert and stop there, rather than round-tripping an
		// AJAX call we already know the server will reject.
		if ( $variationIdField.length && ! variationId ) {
			return;
		}

		var productId = variationId || parseInt( $form.find( '[name="add-to-cart"]' ).val(), 10 );

		if ( ! productId ) {
			return;
		}

		e.preventDefault();
		$button.removeClass( 'added' ).addClass( 'loading' );

		// `$form[0].submit()` (the fallback below) doesn't carry a clicked
		// button's name/value — restore it manually so the no-JS
		// `noorifa_buy_now` redirect still fires after a fallback submit.
		function fallbackSubmit() {
			if ( isBuyNow && ! $form.find( 'input[name="noorifa_buy_now"]' ).length ) {
				$form.append( '<input type="hidden" name="noorifa_buy_now" value="1">' );
			}
			$form[ 0 ].submit();
		}

		$.ajax( {
			type: 'POST',
			url: wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ),
			data: {
				product_id: productId,
				quantity: $quantity.val(),
			},
			dataType: 'json',
			success: function ( response ) {
				$button.removeClass( 'loading' );

				if ( ! response || response.error || ! response.fragments ) {
					// Real validation/stock error — fall back to a normal
					// submit so WooCommerce's own notice/redirect shows.
					fallbackSubmit();
					return;
				}

				if ( isBuyNow ) {
					window.location.href = noorifaCartParams.checkoutUrl;
					return;
				}

				$button.addClass( 'added' );
				$( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, $button ] );
			},
			error: function () {
				$button.removeClass( 'loading' );
				fallbackSubmit();
			},
		} );
	} );
} )( jQuery );
