<?php
/**
 * ShopFilters helper.
 *
 * @package Noorifa
 */

namespace Noorifa\WooCommerce;

/**
 * Small stateless helpers for building real (non-JS-faked) shop filter URLs.
 */
class ShopFilters {

	/**
	 * Builds a URL toggling one term in/out of a `filter_{attribute}` query var.
	 *
	 * @param string   $taxonomy     Attribute taxonomy, e.g. `pa_color`.
	 * @param string   $term_slug    Term slug to toggle.
	 * @param string[] $chosen_terms Currently active term slugs for this taxonomy.
	 */
	public static function toggle_attribute_url( string $taxonomy, string $term_slug, array $chosen_terms ): string {
		$query_var = 'filter_' . str_replace( 'pa_', '', $taxonomy );

		$new_terms = in_array( $term_slug, $chosen_terms, true )
			? array_diff( $chosen_terms, array( $term_slug ) )
			: array_merge( $chosen_terms, array( $term_slug ) );

		$url = remove_query_arg( array( $query_var, 'paged' ) );

		return $new_terms ? add_query_arg( $query_var, implode( ',', $new_terms ), $url ) : $url;
	}
}
