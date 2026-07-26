( function ( $ ) {
	'use strict';

	// Grid column switcher — purely presentational, no data implication.
	function initLayoutSwitch() {
		var $grid = $( '#gridLayout' );

		$( '.tf-view-layout-switch' ).on( 'click', function () {
			var layout = $( this ).data( 'value-layout' );

			if ( ! layout ) {
				return;
			}

			$( '.tf-view-layout-switch' ).removeClass( 'active' );
			$( this ).addClass( 'active' );

			$grid
				.removeClass( function ( index, className ) {
					return ( className.match( /tf-col-\d+/g ) || [] ).join( ' ' );
				} )
				.addClass( layout );
		} );
	}

	// Real price range slider: drives the two hidden `min_price`/`max_price`
	// inputs that the filter form actually submits to WooCommerce.
	function initPriceSlider() {
		var slider = document.getElementById( 'price-value-range' );

		if ( ! slider || typeof noUiSlider === 'undefined' ) {
			return;
		}

		var min = parseInt( slider.dataset.min, 10 ) || 0;
		var max = parseInt( slider.dataset.max, 10 ) || 0;
		var minInput = document.getElementById( 'price-min-input' );
		var maxInput = document.getElementById( 'price-max-input' );
		var minValue = document.getElementById( 'price-min-value' );
		var maxValue = document.getElementById( 'price-max-value' );

		noUiSlider.create( slider, {
			start: [
				minInput ? parseInt( minInput.value, 10 ) : min,
				maxInput ? parseInt( maxInput.value, 10 ) : max,
			],
			connect: true,
			step: 1,
			range: { min: min, max: max },
			format: {
				from: function ( value ) {
					return parseInt( value, 10 );
				},
				to: function ( value ) {
					return parseInt( value, 10 );
				},
			},
		} );

		slider.noUiSlider.on( 'update', function ( values ) {
			if ( minInput ) {
				minInput.value = values[ 0 ];
			}
			if ( maxInput ) {
				maxInput.value = values[ 1 ];
			}
			if ( minValue ) {
				minValue.innerText = values[ 0 ];
			}
			if ( maxValue ) {
				maxValue.innerText = values[ 1 ];
			}
		} );
	}

	$( function () {
		initLayoutSwitch();
		initPriceSlider();
	} );
} )( jQuery );
