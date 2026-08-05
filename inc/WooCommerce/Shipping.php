<?php
/**
 * Shipping helper.
 *
 * @package Noorifa
 */

namespace Noorifa\WooCommerce;

/**
 * Real shipping-zone data lookups shared by the cart drawer and the full
 * cart page.
 */
class Shipping {

	/**
	 * The lowest configured "Free shipping" minimum order amount across all
	 * enabled shipping zones, or null if no zone has one configured.
	 */
	public static function get_free_shipping_threshold(): ?float {
		$threshold = null;

		foreach ( \WC_Shipping_Zones::get_zones() as $zone_data ) {
			$zone = new \WC_Shipping_Zone( $zone_data['zone_id'] );

			foreach ( $zone->get_shipping_methods( true ) as $method ) {
				if ( 'free_shipping' !== $method->id || 'no' === $method->enabled ) {
					continue;
				}

				$min_amount = (float) ( $method->min_amount ?? 0 );

				if ( $min_amount > 0 && in_array( $method->requires, array( 'min_amount', 'either', 'both' ), true ) ) {
					$threshold = null === $threshold ? $min_amount : min( $threshold, $min_amount );
				}
			}
		}

		return $threshold;
	}
}
