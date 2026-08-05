<?php
/**
 * Global helper for reading theme settings as a single flat array.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'noorifa_settings' ) ) {
	/**
	 * Real, flat, request-cached view of the theme's settings.
	 *
	 * $settings = noorifa_settings();
	 * $description = $settings['footer_info_description'];
	 *
	 * Built from the single field registry in Schema.php — every key
	 * this returns, and how it's read, is declared once there. The header
	 * zone map (header_zones — `{left, center, right}`) and the footer
	 * order lists (footer_top_order, footer_bottom_order) go through the
	 * same validated accessors the builder's own renderer uses, so a
	 * corrupted or stale stored value still falls back to the real
	 * default layout here too.
	 *
	 * @return array<string, mixed>
	 */
	function noorifa_settings(): array {
		static $flat = null;

		if ( null === $flat ) {
			$flat = \Noorifa\Settings\Schema::flatten( \Noorifa\Settings\Layout::all() );
		}

		return $flat;
	}
}
