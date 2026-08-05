( function ( $ ) {
	'use strict';

	// Real-time AJAX search for the header search modal (#search) — see
	// Noorifa\Search\LiveSearch. Debounced, and any still-in-flight request
	// is aborted before a new one starts so a slow early response can never
	// overwrite a later, more relevant one.
	$( function () {
		var $modal   = $( '#search' );
		var $input   = $modal.find( '.form-search-nav input[name="s"]' );
		var $results = $modal.find( '.search-live-results' );

		if ( ! $input.length || ! $results.length || typeof noorifaSearchParams === 'undefined' ) {
			return;
		}

		var debounceTimer = null;
		var currentRequest = null;

		function clearResults() {
			if ( currentRequest ) {
				currentRequest.abort();
				currentRequest = null;
			}
			$results.empty();
			$modal.removeClass( 'has-live-results is-searching' );
		}

		function runSearch( term ) {
			if ( currentRequest ) {
				currentRequest.abort();
			}

			$modal.addClass( 'is-searching' );

			currentRequest = $.ajax( {
				url: noorifaSearchParams.ajaxUrl,
				method: 'GET',
				dataType: 'json',
				data: {
					action: 'noorifa_live_search',
					nonce: noorifaSearchParams.nonce,
					term: term,
				},
				success: function ( response ) {
					if ( ! response || ! response.success ) {
						return;
					}

					$results.html( response.data.html || '' );
					$modal.addClass( 'has-live-results' );
				},
			} ).always( function () {
				$modal.removeClass( 'is-searching' );
				currentRequest = null;
			} );
		}

		$input.on( 'input', function () {
			var term = $.trim( $input.val() );

			window.clearTimeout( debounceTimer );

			if ( term.length < noorifaSearchParams.minChars ) {
				clearResults();
				return;
			}

			debounceTimer = window.setTimeout( function () {
				runSearch( term );
			}, 350 );
		} );

		$modal.on( 'hidden.bs.modal', function () {
			window.clearTimeout( debounceTimer );
			clearResults();
			$input.val( '' );
		} );
	} );
} )( jQuery );
