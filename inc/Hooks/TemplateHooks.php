<?php
/**
 * TemplateHooks component.
 *
 * @package Ecombon
 */

namespace Ecombon\Hooks;

use Ecombon\Setup\ComponentInterface;

/**
 * Small, presentation-only filters that shape how core WordPress markup and
 * text is rendered. Nothing here stores or processes data.
 */
class TemplateHooks implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'wp_head', array( $this, 'pingback_header' ) );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
		add_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );
		add_filter( 'nav_menu_link_attributes', array( $this, 'footer_menu_link_attributes' ), 10, 3 );
	}

	/**
	 * Adds the pingback link to <head> on singular posts with pings open.
	 */
	public function pingback_header(): void {
		if ( is_singular() && pings_open() ) {
			printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
		}
	}

	/**
	 * Adds structural classes used by the theme's CSS.
	 *
	 * @param string[] $classes Existing body classes.
	 * @return string[] Filtered body classes.
	 */
	public function body_classes( array $classes ): array {
		$blog_context = is_singular( 'post' ) || is_home() || is_archive();
		$has_sidebar  = $blog_context && is_active_sidebar( 'sidebar-blog' );

		if ( ! $has_sidebar ) {
			$classes[] = 'no-sidebar';
		}

		return $classes;
	}

	/**
	 * Shortens the default excerpt length.
	 */
	public function excerpt_length(): int {
		return 30;
	}

	/**
	 * Replaces the default excerpt ellipsis with a "Read more" link.
	 */
	public function excerpt_more(): string {
		return '&hellip;';
	}

	/**
	 * Adds the Amerce footer link styling to the footer nav menus.
	 *
	 * @param string[]  $atts Link attributes.
	 * @param \WP_Post  $item Menu item.
	 * @param \stdClass $args wp_nav_menu() args, as an object.
	 * @return string[] Filtered attributes.
	 */
	public function footer_menu_link_attributes( array $atts, \WP_Post $item, \stdClass $args ): array {
		$footer_locations = array( 'footer_company', 'footer_customer' );

		if ( in_array( $args->theme_location ?? '', $footer_locations, true ) ) {
			$atts['class'] = trim( ( $atts['class'] ?? '' ) . ' cl-text-2 link' );
		}

		return $atts;
	}
}
