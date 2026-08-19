/**
 * Popup builder — front-end trigger engine. Vanilla JS, no dependencies
 * (the popup markup itself is server-rendered by
 * template-parts/global/popups.php; this file only decides *when* to
 * reveal it and handles closing/frequency-capping).
 *
 * Only one non-bar popup (center/corner/fullscreen) can be open at a time —
 * whichever trigger fires first wins, so a slow scroll-trigger popup can't
 * shove a delay-trigger popup off screen mid-visit. Bars (top-bar/
 * bottom-bar) are page chrome, not modals, so they're exempt from that
 * single-active rule and from the body scroll lock.
 */
( function () {
	'use strict';

	var COOKIE_PREFIX = 'noorifa_popup_';
	var MOBILE_BREAKPOINT = 782; // Matches WP core's own mobile admin-bar breakpoint.
	var activeModalOpen = false;

	function isBar( popup ) {
		return popup.classList.contains( 'noorifa-popup--top-bar' ) || popup.classList.contains( 'noorifa-popup--bottom-bar' );
	}

	function isMobileViewport() {
		return window.matchMedia( '(max-width: ' + MOBILE_BREAKPOINT + 'px)' ).matches;
	}

	function getCookie( name ) {
		var match = document.cookie.match( new RegExp( '(?:^|; )' + name.replace( /[.$?*|{}()[\]\\/+^]/g, '\\$&' ) + '=([^;]*)' ) );
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

	function frequencyBlocked( popup ) {
		var frequency = popup.dataset.frequency;
		var id = popup.dataset.popupId;

		if ( 'every_visit' === frequency ) {
			return false;
		}

		if ( 'once_per_session' === frequency ) {
			try {
				return '1' === sessionStorage.getItem( COOKIE_PREFIX + id );
			} catch ( e ) {
				return false; // Storage unavailable (e.g. private mode) — never block.
			}
		}

		return '1' === getCookie( COOKIE_PREFIX + id );
	}

	function markShown( popup ) {
		var frequency = popup.dataset.frequency;
		var id = popup.dataset.popupId;

		if ( 'every_visit' === frequency ) {
			return;
		}

		if ( 'once_per_session' === frequency ) {
			try {
				sessionStorage.setItem( COOKIE_PREFIX + id, '1' );
			} catch ( e ) {} // eslint-disable-line no-empty
			return;
		}

		var days = 7;
		if ( 'once_per_day' === frequency ) {
			days = 1;
		} else if ( 'once_ever' === frequency ) {
			days = 3650;
		} else if ( 'once_per_days' === frequency ) {
			days = parseInt( popup.dataset.frequencyDays, 10 ) || 7;
		}

		setCookie( COOKIE_PREFIX + id, '1', days );
	}

	function openPopup( popup ) {
		if ( false === popup.hidden ) {
			return;
		}

		var bar = isBar( popup );

		if ( ! bar ) {
			if ( activeModalOpen ) {
				return;
			}
			activeModalOpen = true;
			document.body.classList.add( 'noorifa-popup-lock' );
		}

		popup.hidden = false;
		popup.setAttribute( 'aria-hidden', 'false' );

		window.requestAnimationFrame( function () {
			popup.classList.add( 'is-open' );
		} );

		markShown( popup );

		var closeButton = popup.querySelector( '.noorifa-popup__close' );
		if ( closeButton ) {
			closeButton.focus();
		}
	}

	function closePopup( popup ) {
		popup.classList.remove( 'is-open' );
		popup.setAttribute( 'aria-hidden', 'true' );

		if ( ! isBar( popup ) ) {
			activeModalOpen = false;
			document.body.classList.remove( 'noorifa-popup-lock' );
		}

		window.setTimeout( function () {
			popup.hidden = true;
		}, 320 );
	}

	function bindClose( popup ) {
		var closers = popup.querySelectorAll( '[data-popup-close]' );
		for ( var i = 0; i < closers.length; i++ ) {
			closers[ i ].addEventListener( 'click', function () {
				closePopup( popup );
			} );
		}

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! popup.hidden ) {
				closePopup( popup );
			}
		} );
	}

	function bindTrigger( popup ) {
		var device = popup.dataset.device;
		if ( 'desktop' === device && isMobileViewport() ) {
			return;
		}
		if ( 'mobile' === device && ! isMobileViewport() ) {
			return;
		}

		if ( frequencyBlocked( popup ) ) {
			return;
		}

		var type = popup.dataset.trigger;

		if ( 'immediate' === type ) {
			openPopup( popup );
			return;
		}

		if ( 'delay' === type ) {
			var seconds = parseInt( popup.dataset.delay, 10 );
			window.setTimeout( function () {
				openPopup( popup );
			}, ( isNaN( seconds ) ? 0 : seconds ) * 1000 );
			return;
		}

		if ( 'scroll' === type ) {
			var threshold = parseInt( popup.dataset.scroll, 10 ) || 50;
			var onScroll = function () {
				var scrollable = document.documentElement.scrollHeight - window.innerHeight;
				var percent = scrollable > 0 ? ( window.scrollY / scrollable ) * 100 : 100;
				if ( percent >= threshold ) {
					window.removeEventListener( 'scroll', onScroll );
					openPopup( popup );
				}
			};
			window.addEventListener( 'scroll', onScroll, { passive: true } );
			return;
		}

		if ( 'exit_intent' === type ) {
			var onMouseOut = function ( event ) {
				if ( event.clientY <= 0 && ! event.relatedTarget ) {
					document.removeEventListener( 'mouseout', onMouseOut );
					openPopup( popup );
				}
			};
			document.addEventListener( 'mouseout', onMouseOut );
			return;
		}

		if ( 'click' === type ) {
			var selector = popup.dataset.selector;
			if ( ! selector ) {
				return;
			}
			document.addEventListener( 'click', function ( event ) {
				if ( event.target.closest( selector ) ) {
					event.preventDefault();
					openPopup( popup );
				}
			} );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var popups = document.querySelectorAll( '.noorifa-popup' );
		for ( var i = 0; i < popups.length; i++ ) {
			bindClose( popups[ i ] );
			bindTrigger( popups[ i ] );
		}
	} );
} )();
