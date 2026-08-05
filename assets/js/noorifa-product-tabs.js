( function ( $ ) {
	'use strict';

	// Submitting a real review (or a comment reply) redirects back with
	// `#comment-N` (or `#respond` on a validation error) in the URL — but
	// Reviews lives inside a Bootstrap `.tab-pane` that's hidden unless its
	// tab is active, so the browser can't actually scroll to a hash target
	// sitting inside it. Switch to that tab first, then let the browser's
	// own hash-scroll (or a manual scrollIntoView, since switching tabs
	// after load won't re-trigger it) reveal the target.
	function revealHashTarget() {
		var hash = window.location.hash;

		if ( ! hash || 1 === hash.length ) {
			return;
		}

		var $target;

		try {
			$target = $( hash );
		} catch ( e ) {
			return; // Not a valid selector (e.g. a non-ID fragment).
		}

		if ( ! $target.length ) {
			return;
		}

		var $pane = $target.closest( '.tab-pane' );

		if ( ! $pane.length || $pane.hasClass( 'active' ) ) {
			return;
		}

		var $trigger = $( '[data-bs-toggle="tab"][href="#' + $pane.attr( 'id' ) + '"]' );

		if ( ! $trigger.length || typeof bootstrap === 'undefined' ) {
			return;
		}

		bootstrap.Tab.getOrCreateInstance( $trigger[ 0 ] ).show();

		window.setTimeout( function () {
			$target[ 0 ].scrollIntoView( { block: 'center' } );
		}, 50 );
	}

	$( revealHashTarget );
} )( jQuery );
