<?php
/**
 * Frontend_Output component.
 *
 * @package Ecombon
 */

namespace Ecombon\Settings;

use Ecombon\Setup\ComponentInterface;

/**
 * Wires the real stored settings into the theme's frontend.
 *
 * For social links, this hooks the *same* `ecombon_social_links` filter
 * `template-parts/footer/info.php` already calls — that file needs zero
 * changes, it just starts receiving real stored values instead of always
 * falling back to its hardcoded default (empty array).
 */
class Frontend_Output implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_filter( 'ecombon_social_links', array( $this, 'social_links' ) );
		add_filter( 'ecombon_payment_icons_image', array( $this, 'payment_icons_image' ) );

		add_action( 'wp_head', array( $this, 'print_brand_overrides' ) );
		add_filter( 'body_class', array( $this, 'site_width_body_class' ) );
		add_filter( 'loop_shop_per_page', array( $this, 'products_per_page' ), 20 );
	}

	/**
	 * Adds a real body class only when the site owner has switched away
	 * from the theme's original 'boxed' layout — an untouched install
	 * gets no extra class and no extra CSS rule ever applies.
	 *
	 * @param string[] $classes Real classes WordPress is about to output.
	 * @return string[]
	 */
	public function site_width_body_class( array $classes ): array {
		if ( 'full-width' === Layout::site_width() ) {
			$classes[] = 'ecombon-full-width';
		}
		if ( ! Layout::header_sticky() ) {
			$classes[] = 'ecombon-no-sticky-header';
		}
		if ( Layout::force_mobile_menu() ) {
			$classes[] = 'ecombon-force-mobile-menu';
		}
		return $classes;
	}

	/**
	 * Real stored social links, converted from the settings page's ordered
	 * `{id, url}` repeater rows into the plain `network => url` map this
	 * filter has always returned — `template-parts/footer/info.php`'s own
	 * rendering loop needs zero changes for that.
	 *
	 * @param array $default_value The filter's own default value.
	 */
	public function social_links( array $default_value ): array {
		$items = Layout::footer_social_links_items();
		if ( ! $items ) {
			return $default_value;
		}

		$links = array();
		foreach ( $items as $item ) {
			$links[ $item['id'] ] = $item['url'];
		}
		return $links;
	}

	/**
	 * Real stored payment icons image (a single uploaded image replacing
	 * the theme's own bundled icon set entirely) — this is the same filter
	 * `template-parts/footer/payment-icons.php` already calls (both for
	 * the real footer bottom bar and, unrelated, the single product page's
	 * trust badge), so that file needs zero changes.
	 *
	 * @param string $default_value The filter's own default value.
	 */
	public function payment_icons_image( string $default_value ): string {
		$value = Layout::all()['footer']['payment_icons_image'] ?? '';
		return '' !== $value ? $value : $default_value;
	}

	/**
	 * Real stored shop products-per-page — this is WooCommerce's own
	 * core `loop_shop_per_page` filter (already the sole thing controlling
	 * this, since the theme never declares `product_grid` theme support),
	 * so no query/template changes are needed anywhere else for this.
	 *
	 * @param int $default_value The filter's own default value (WC core's own 16).
	 */
	public function products_per_page( int $default_value ): int {
		$value = (int) ( Layout::all()['shop']['products_per_page'] ?? 0 );
		return $value > 0 ? $value : $default_value;
	}

	/**
	 * Prints a `:root` override only for values a site owner has actually
	 * changed from the theme's real defaults — an untouched install prints
	 * nothing extra at all. Every field that needs this (brand colors,
	 * typography, topbar background/font size, container width, …)
	 * declares its own CSS output in `Schema::fields()`; this method is
	 * just the generic printer, not a per-field one.
	 */
	public function print_brand_overrides(): void {
		$result = Schema::css_declarations( Layout::all() );

		if ( ! $result['declarations'] ) {
			return;
		}

		if ( $result['font_families'] ) {
			$query = array();
			foreach ( $result['font_families'] as $family ) {
				$query[] = 'family=' . rawurlencode( $family ) . ':wght@400;500;600;700';
			}
			$url = 'https://fonts.googleapis.com/css2?' . implode( '&', $query ) . '&display=swap';
			wp_enqueue_style( 'ecombon-google-font', $url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}

		echo '<style id="ecombon-brand-overrides">:root{' . implode( '', $result['declarations'] ) . '}</style>' . "\n";
	}
}
