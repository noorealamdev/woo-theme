<?php
/**
 * CartFragments component.
 *
 * @package Ecombon
 */

namespace Ecombon\WooCommerce;

use Ecombon\Setup\ComponentInterface;

/**
 * Registers real WooCommerce cart fragments for the theme's own markup.
 *
 * WooCommerce's `wc-add-to-cart.js` already does a real AJAX add-to-cart
 * and swaps in whatever's returned from `woocommerce_add_to_cart_fragments`
 * — that mechanism normally only fires for the core Cart widget's markup.
 * Since the header cart badge and mini-cart drawer are theme-authored, we
 * need to hand it fresh HTML for those too, or they'd only update on the
 * next full page load.
 */
class CartFragments implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'add_fragments' ) );
	}

	/**
	 * Adds the header cart badge and mini-cart drawer as refreshable fragments.
	 *
	 * @param array $fragments Existing fragments, keyed by CSS selector.
	 * @return array Filtered fragments.
	 */
	public function add_fragments( array $fragments ): array {
		if ( ! WC()->cart ) {
			return $fragments;
		}

		$fragments['.shop-cart .count'] = '<span class="count">' . esc_html( (string) WC()->cart->get_cart_contents_count() ) . '</span>';

		ob_start();
		get_template_part( 'template-parts/header/cart-drawer' );
		$fragments['#shoppingCart'] = ob_get_clean();

		return $fragments;
	}
}
