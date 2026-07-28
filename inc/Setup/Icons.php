<?php
/**
 * Icons component.
 *
 * @package Ecombon
 */

namespace Ecombon\Setup;

/**
 * Real inline SVG icons — replaces the theme's old icomoon icon font.
 *
 * The sprite (assets/icon/svg/sprite.svg) is inlined once into the page via
 * the real `wp_body_open` hook, and every icon is a real `<svg><use></svg>`
 * referencing a `<symbol>` in that sprite — no icon font, no extra HTTP
 * request per icon. Sizing/color both come from ordinary CSS on the calling
 * element (font-size/color), same as the icon font did, since the base
 * `.icon` rule sizes to `1em` and fills with `currentColor`.
 */
class Icons implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'wp_body_open', array( $this, 'render_sprite' ) );
	}

	/**
	 * Inlines the real SVG sprite once, right after <body> opens.
	 */
	public function render_sprite(): void {
		$sprite_path = ECOMBON_THEME_DIR . '/assets/icon/svg/sprite.svg';

		if ( ! file_exists( $sprite_path ) ) {
			return;
		}

		echo file_get_contents( $sprite_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Builds the markup for a single real icon: `<svg class="icon icon-{$name} ..."><use ...></svg>`.
	 *
	 * @param string $name        Icon name, matching a real `<symbol id="icon-{$name}">` in the sprite.
	 * @param string $extra_class Additional classes to add (e.g. 'fs-20 cl-text-yellow').
	 */
	public static function html( string $name, string $extra_class = '' ): string {
		$class = trim( 'icon icon-' . $name . ( $extra_class ? ' ' . $extra_class : '' ) );
		return sprintf(
			'<svg class="%1$s" aria-hidden="true"><use href="#icon-%2$s"></use></svg>',
			esc_attr( $class ),
			esc_attr( $name )
		);
	}

	/**
	 * Echoes a single real icon — see self::html().
	 *
	 * @param string $name        Icon name.
	 * @param string $extra_class Additional classes.
	 */
	public static function render( string $name, string $extra_class = '' ): void {
		echo self::html( $name, $extra_class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
