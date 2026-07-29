( function ( $ ) {
	'use strict';

	/**
	 * Progressive-enhancement swatch/pill picker over WooCommerce's own
	 * real `table.variations` selects. The selects are never removed or
	 * replaced — they stay the source of truth that
	 * assets/js/frontend/add-to-cart-variation.js (WooCommerce core) reads
	 * from and writes to, so real stock/price/availability matching keeps
	 * working unmodified. This only builds a nicer UI on top and mirrors
	 * clicks back onto the real select via `.val().trigger('change')`.
	 *
	 * If this script fails to run for any reason, the real table (with
	 * real native <select> dropdowns) stays visible and fully usable —
	 * it's only hidden once the swatches built from it are in the DOM.
	 */

	/**
	 * Whether an attribute should render as image swatches (color) rather
	 * than text pills (size and everything else).
	 */
	function isColorAttribute( attributeName, label ) {
		var needle = ( ( attributeName || '' ) + ' ' + ( label || '' ) ).toLowerCase();
		return /colou?r/.test( needle );
	}

	/**
	 * Finds a representative variation image for a given attribute value,
	 * e.g. the photo of the shirt in that particular color. Falls back to
	 * the product's own main image when no single variation is a clean
	 * match (e.g. ajax-loaded variation sets, where `variationData` is
	 * `false` and no per-option image is available at all).
	 */
	function findVariationImage( variationData, attributeName, value ) {
		if ( ! Array.isArray( variationData ) ) {
			return '';
		}

		for ( var i = 0; i < variationData.length; i++ ) {
			var attrs = variationData[ i ].attributes || {};

			if ( attrs[ attributeName ] === value && variationData[ i ].image && variationData[ i ].image.src ) {
				return variationData[ i ].image.src;
			}
		}

		return '';
	}

	/**
	 * Builds the swatch/pill picker for one variations form, reading
	 * entirely from the real <select> elements already in the DOM.
	 */
	function buildSwatches( $form ) {
		var $table = $form.find( 'table.variations' );

		if ( ! $table.length ) {
			return;
		}

		var variationData = $form.data( 'product_variations' );
		var fallbackImage = $( '.product-media-main .swiper-slide' ).first().find( 'img' ).attr( 'src' ) || '';

		// Inserted as direct siblings of `table.variations` (not wrapped in
		// a container div) so `.product-variant`'s own `gap` (see
		// assets/scss/elements/_product.scss) spaces them the same way it
		// spaces every other real child of that element.
		var $items = [];

		$table.find( '> tbody > tr' ).each( function () {
			var $row = $( this );
			var $select = $row.find( 'select' );

			if ( ! $select.length ) {
				return;
			}

			var attributeName = $select.data( 'attribute_name' ) || $select.attr( 'name' );
			var label = $.trim( $row.find( 'th.label label' ).text() );
			var isColor = isColorAttribute( attributeName, label );
			var $item = $( '<div class="variant-picker-item"></div>' ).addClass( isColor ? 'variant-color' : 'variant-size' );
			var $labelValue = $( '<span class="variant-picker-label-value text-capitalize fw-medium"></span>' );
			var $labelWrap = $( '<div class="variant-picker-label"><div></div></div>' );

			$labelWrap.find( 'div' ).append( document.createTextNode( label + ': ' ) ).append( $labelValue );

			var $values = $( '<div class="variant-picker-values"></div>' );

			$select.find( 'option' ).each( function () {
				var $option = $( this );
				var value = $option.attr( 'value' );

				if ( ! value ) {
					return;
				}

				var text = $.trim( $option.text() );
				var $btn;

				if ( isColor ) {
					var imgSrc = findVariationImage( variationData, attributeName, value ) || fallbackImage;
					$btn = $(
						'<div class="hover-tooltip tooltip-bot color-btn style-image">' +
							'<div class="img"><img loading="lazy" width="60" height="60"></div>' +
							'<span class="tooltip"></span>' +
						'</div>'
					);
					$btn.find( 'img' ).attr( { src: imgSrc, alt: text } );
					$btn.find( '.tooltip' ).text( text );
				} else {
					// `.style-nor` (see assets/scss/elements/_product.scss)
					// auto-sizes to its padding + text instead of the base
					// `.size-btn`'s fixed 44x44 box, which only really fits
					// single-letter sizes (S/M/L) — real WC attribute values
					// here are full words ("Large", "Medium"), which would
					// otherwise overflow a fixed-size box.
					$btn = $( '<span class="size-btn style-nor"></span>' ).text( text );
				}

				$btn.attr( 'data-value', value );

				if ( $option.is( ':selected' ) ) {
					$btn.addClass( 'active' );
					$labelValue.text( text );
				}

				if ( $option.is( ':disabled' ) ) {
					$btn.addClass( 'disabled' );
				}

				$btn.on( 'click', function () {
					if ( $btn.hasClass( 'disabled' ) || $btn.hasClass( 'active' ) ) {
						return;
					}

					$values.children().removeClass( 'active' );
					$btn.addClass( 'active' );
					$labelValue.text( text );
					$select.val( value ).trigger( 'change' );
				} );

				$values.append( $btn );
			} );

			$item.append( $labelWrap ).append( $values );
			$items.push( $item );
		} );

		$table.addClass( 'd-none' );

		var $insertAfter = $table;

		$items.forEach( function ( $item ) {
			$insertAfter.after( $item );
			$insertAfter = $item;
		} );

		// WooCommerce's own onUpdateAttributes (add-to-cart-variation.js)
		// enables/disables real <option> elements based on the other
		// selected attributes every time a selection changes — mirror
		// that onto the matching swatch/pill after each such pass.
		$form.on( 'woocommerce_update_variation_values', function () {
			$table.find( '> tbody > tr' ).each( function ( index ) {
				var $optionSelect = $( this ).find( 'select' );
				var $item = $items[ index ];

				$optionSelect.find( 'option' ).each( function () {
					var value = $( this ).attr( 'value' );

					if ( ! value ) {
						return;
					}

					$item
						.find( '[data-value="' + value.replace( /"/g, '\\"' ) + '"]' )
						.toggleClass( 'disabled', $( this ).is( ':disabled' ) )
						.toggleClass( 'active', $( this ).is( ':selected' ) );
				} );
			} );
		} );
	}

	/**
	 * Appends "- $price" to the add-to-cart button once WooCommerce finds
	 * a matching variation, removing it again when the selection becomes
	 * incomplete/invalid. `variation.price_html` is WooCommerce's own
	 * already-formatted (currency symbol, decimals, position) price
	 * string, so this never needs to format a number itself.
	 *
	 * Deliberately named `.add-price*`, not the theme's own `.price-add`:
	 * main.js's fake demo variant-price calculator (`totalPriceVariant()`)
	 * finds any real `.price-add` element unconditionally on page load and
	 * overwrites it based on a `data-price` attribute this real markup
	 * doesn't have, producing "$NaN".
	 */
	function bindPriceDisplay( $form ) {
		var $button = $form.find( '.single_add_to_cart_button' ).not( '.buy-it-now-button' );

		function clearPrice() {
			$button.find( '.add-price-sep, .add-price' ).remove();
		}

		$form.on( 'found_variation', function ( event, variation ) {
			// `price_html` for an on-sale variation includes WooCommerce's
			// own accessibility-only `.screen-reader-text` spans ("Original
			// price was: $X. Current price is: $Y.") alongside the visible
			// <del>/<ins> prices — invisible via CSS, but `.text()` reads
			// them anyway. Strip them, then prefer the <ins> (current/sale)
			// price over the struck-through original when both are present.
			var $priceHtml = $( '<div>' ).html( variation.price_html );

			$priceHtml.find( '.screen-reader-text' ).remove();

			var $currentPrice = $priceHtml.find( 'ins' );
			var priceText = ( $currentPrice.length ? $currentPrice : $priceHtml ).text().replace( /\s+/g, ' ' ).trim();

			clearPrice();

			if ( ! priceText ) {
				return;
			}

			$button
				.append( '<span class="add-price-sep d-none d-sm-block d-md-none d-lg-block">&nbsp;-&nbsp;</span>' )
				.append( $( '<span class="add-price d-none d-sm-block d-md-none d-lg-block"></span>' ).text( priceText ) );
		} );

		$form.on( 'reset_data hide_variation', clearPrice );
	}

	// `wc_variation_form` is WooCommerce's own signal (add-to-cart-variation.js)
	// that a form's VariationForm has finished constructing *and* run its
	// first `check_variations` pass — so option `disabled`/`selected`
	// state already reflects any default attribute selection before this
	// ever reads it, with no arbitrary delay to guess at. That first pass
	// already fired (and was missed by) `found_variation` synchronously,
	// before this listener existed — re-trigger `check_variations` once
	// `bindPriceDisplay` is wired up so a product with default attributes
	// selected shows its price immediately instead of only after the next
	// manual selection change.
	$( document ).on( 'wc_variation_form', '.variations_form', function () {
		var $form = $( this );

		buildSwatches( $form );
		bindPriceDisplay( $form );
		$form.trigger( 'check_variations' );
	} );
} )( jQuery );
