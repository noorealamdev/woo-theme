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

		/*
		 * WooCommerce's own remove-from-cart AJAX (wc-add-to-cart.js) only
		 * ever applies whatever fragments are registered here — without
		 * these two, removing an item on the real cart page would refresh
		 * the header badge and mini-cart drawer but leave the page's own
		 * item table and order summary showing stale data.
		 *
		 * Deliberately not gated behind `is_cart()`: that conditional tag
		 * isn't reliably true from inside a `wc-ajax` request (there's no
		 * main-query page context the way a real page load has one), so
		 * gating on it silently dropped these fragments from every AJAX
		 * remove. `$(selector).replaceWith(...)` on a page that has no
		 * `.woocommerce-cart-form`/`.cart_totals` element (i.e. every page
		 * other than the real cart page) is already a harmless no-op, so
		 * there's no real page-context check needed either way.
		 */
		if ( WC()->cart->is_empty() ) {
			// A bare selector slot, not a real <form> — there's nothing
			// left to submit. Keeping the `.woocommerce-cart-form` class
			// on it is what lets this fragment replace the real form in
			// place (see cart-table.php's own docblock).
			ob_start();
			get_template_part( 'template-parts/cart/empty-content' );
			$empty_content = ob_get_clean();

			$fragments['.woocommerce-cart-form'] = '<div class="woocommerce-cart-form">' . $empty_content . '</div>';
		} else {
			ob_start();
			get_template_part( 'template-parts/cart/cart-table' );
			$fragments['.woocommerce-cart-form'] = ob_get_clean();
		}

		ob_start();
		wc_get_template( 'cart/cart-totals.php' );
		$fragments['.cart_totals'] = ob_get_clean();

		return $fragments;
	}
}
