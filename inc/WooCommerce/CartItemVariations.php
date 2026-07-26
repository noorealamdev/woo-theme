<?php
/**
 * CartItemVariations helper.
 *
 * @package Ecombon
 */

namespace Ecombon\WooCommerce;

/**
 * Resolves a cart item's chosen variation attributes into display-ready
 * label/value pairs (e.g. "Color" / "Light Gray").
 *
 * Deliberately doesn't use WooCommerce's own `wc_get_formatted_cart_item_data()`
 * as-is: that function hides an attribute row whenever the value already
 * appears in the variation's product title (WC's default auto-generated
 * variation names always include it), which would hide every row on this
 * catalog. The label/value resolution here mirrors WooCommerce's own logic,
 * just without that suppression.
 */
class CartItemVariations {

	/**
	 * Gets the display rows for a cart item's variation attributes.
	 *
	 * @param array $cart_item A WC_Cart cart item.
	 * @return array<int, array{label: string, value: string}>
	 */
	public static function get_rows( array $cart_item ): array {
		if ( empty( $cart_item['variation'] ) || ! is_array( $cart_item['variation'] ) ) {
			return array();
		}

		$product = $cart_item['data'];
		$rows    = array();

		foreach ( $cart_item['variation'] as $attribute_key => $attribute_value ) {
			$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $attribute_key ) ) );

			if ( taxonomy_exists( $taxonomy ) ) {
				$term  = get_term_by( 'slug', $attribute_value, $taxonomy );
				$value = ( $term && ! is_wp_error( $term ) ) ? $term->name : $attribute_value;
				$label = wc_attribute_label( $taxonomy );
			} else {
				$value = apply_filters( 'woocommerce_variation_option_name', $attribute_value, null, $taxonomy, $product );
				$label = wc_attribute_label( str_replace( 'attribute_', '', $attribute_key ), $product );
			}

			if ( '' === $value ) {
				continue;
			}

			$rows[] = array(
				'label' => $label,
				'value' => $value,
			);
		}

		return $rows;
	}

	/**
	 * Gets the base product title for a cart item, without WooCommerce's
	 * auto-appended variation attributes (e.g. "Simplified Crop-top", not
	 * "Simplified Crop-top - Large, Blue") — those are shown separately
	 * via {@see self::get_rows()}.
	 *
	 * @param array $cart_item A WC_Cart cart item.
	 */
	public static function get_title( array $cart_item ): string {
		$product = $cart_item['data'];

		if ( $product->is_type( 'variation' ) ) {
			$parent = wc_get_product( $product->get_parent_id() );
			if ( $parent ) {
				return $parent->get_name();
			}
		}

		return $product->get_name();
	}
}
