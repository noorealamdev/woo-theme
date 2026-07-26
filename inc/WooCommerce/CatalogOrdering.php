<?php
/**
 * CatalogOrdering component.
 *
 * @package Ecombon
 */

namespace Ecombon\WooCommerce;

use Ecombon\Setup\ComponentInterface;

/**
 * Adds alphabetical sort options to WooCommerce's catalog ordering, so the
 * shop control bar's A-Z / Z-A links are backed by a real query, not a
 * client-side re-sort.
 */
class CatalogOrdering implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'add_alphabetical_args' ) );
	}

	/**
	 * Maps our `a-z` / `z-a` orderby values to real WP_Query args.
	 *
	 * @param array $args Existing ordering args.
	 * @return array Filtered ordering args.
	 */
	public function add_alphabetical_args( array $args ): array {
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'a-z' === $orderby ) {
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
		} elseif ( 'z-a' === $orderby ) {
			$args['orderby'] = 'title';
			$args['order']   = 'DESC';
		}

		return $args;
	}
}
