<?php
/**
 * ProductNavigation helper.
 *
 * @package Ecombon
 */

namespace Ecombon\WooCommerce;

/**
 * Resolves the previous/next product for the single-product page's
 * prev/next navigation, scoped to the current product's first category
 * when it has one (falls back to the whole catalog otherwise).
 */
class ProductNavigation {

	/**
	 * Gets the adjacent (previous or next) published product, if any.
	 *
	 * @param bool $previous True for the previous product, false for next.
	 */
	public static function get_adjacent( bool $previous ): ?\WC_Product {
		$post = get_adjacent_post( true, '', $previous, 'product_cat' );

		if ( ! $post ) {
			return null;
		}

		$product = wc_get_product( $post );

		return $product instanceof \WC_Product ? $product : null;
	}
}
