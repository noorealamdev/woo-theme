/**
 * GDPR-style cookie notice — vanilla JS, no dependencies. Shows the banner
 * (server-rendered by template-parts/global/cookie-notice.php, hidden by
 * default) unless a prior "accepted" cookie is still within its configured
 * duration; clicking Accept sets that cookie and hides the banner.
 */
( function () {
	'use strict';

	var COOKIE_NAME = 'noorifa_cookie_notice_accepted';

	function getCookie( name ) {
		var match = document.cookie.match( new RegExp( '(?:^|; )' + name + '=([^;]*)' ) );
		return match ? decodeURIComponent( match[ 1 ] ) : null;
	}

	function setCookie( name, value, days ) {
		var expires = '';
		if ( days > 0 ) {
			var date = new Date();
			date.setTime( date.getTime() + days * 24 * 60 * 60 * 1000 );
			expires = '; expires=' + date.toUTCString();
		}
		document.cookie = name + '=' + encodeURIComponent( value ) + expires + '; path=/; SameSite=Lax';
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var notice = document.getElementById( 'noorifa-cookie-notice' );
		if ( ! notice ) {
			return;
		}

		if ( '1' === getCookie( COOKIE_NAME ) ) {
			return;
		}

		notice.hidden = false;
		window.requestAnimationFrame( function () {
			notice.classList.add( 'is-visible' );
		} );

		var acceptButton = notice.querySelector( '[data-cookie-accept]' );
		if ( acceptButton ) {
			acceptButton.addEventListener( 'click', function () {
				var days = parseInt( notice.dataset.durationDays, 10 ) || 180;
				setCookie( COOKIE_NAME, '1', days );
				notice.classList.remove( 'is-visible' );
				window.setTimeout( function () {
					notice.hidden = true;
				}, 320 );
			} );
		}
	} );
} )();
