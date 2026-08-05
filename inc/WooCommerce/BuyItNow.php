<?php
/**
 * BuyItNow component.
 *
 * @package Noorifa
 */

namespace Noorifa\WooCommerce;

use Noorifa\Setup\ComponentInterface;

/**
 * Real "Buy It Now": redirects straight to checkout after a successful
 * add-to-cart, when the request came from the buy-now button instead of
 * the regular add-to-cart button.
 *
 * The buy-now button (see the theme's `single-product/add-to-cart/*`
 * overrides) is a second real submit button inside the same `form.cart`,
 * carrying the same `.single_add_to_cart_button` class so WooCommerce's
 * own variation-selection guard already covers it — this only changes
 * *where the customer lands* after WooCommerce's real add-to-cart handler
 * runs. It works even without JavaScript; assets/js/noorifa-cart.js
 * progressively enhances it into an AJAX call that skips the page reload.
 */
class BuyItNow implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'maybe_redirect_to_checkout' ) );
	}

	/**
	 * Redirects to checkout when the add-to-cart came from the buy-now
	 * button.
	 *
	 * @param string $url Default redirect URL.
	 */
	public function maybe_redirect_to_checkout( string $url ): string {
		if ( isset( $_REQUEST['noorifa_buy_now'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return wc_get_checkout_url();
		}

		return $url;
	}
}
