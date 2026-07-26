<?php
/**
 * VariationPricing component.
 *
 * @package Ecombon
 */

namespace Ecombon\WooCommerce;

use Ecombon\Setup\ComponentInterface;

/**
 * Always sends each variation's real price to the front end.
 *
 * By default WooCommerce blanks out `price_html` in its variation JSON
 * when a variation's price matches the others (see
 * WC_Product_Variable::get_available_variation()) — a reasonable
 * de-duplication for its own default template, but the theme's swatch
 * picker (assets/js/ecombon-product-variations.js) always shows the
 * selected variation's price on the Add To Cart button itself, so it
 * needs that real, already-formatted price string every time.
 */
class VariationPricing implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_filter( 'woocommerce_show_variation_price', '__return_true' );
	}
}
