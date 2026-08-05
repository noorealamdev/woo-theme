<?php
/**
 * Integrations component.
 *
 * @package Noorifa
 */

namespace Noorifa\Setup;

/**
 * Outputs real third-party tracking snippets — Google Analytics and the
 * Meta/Facebook Pixel — exactly as a site owner pastes them (Appearance/
 * Noorifa > Integrations), not reconstructed from just an ID. Google/Meta
 * both hand site owners the *full* install snippet to copy, and asking for
 * just the ID instead was its own source of confusion — this takes
 * whatever real code is pasted, verbatim (see Schema::sanitize_value()'s
 * 'raw_html' case for why that's deliberately unsanitized). Neither prints
 * anything at all until a site owner has actually pasted real code — an
 * untouched install's <head> is unaffected.
 */
class Integrations implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'wp_head', array( $this, 'render_google_analytics' ) );
		add_action( 'wp_head', array( $this, 'render_facebook_pixel' ) );
	}

	/**
	 * Google Analytics — the real snippet pasted from Google's own
	 * dashboard, printed as-is.
	 */
	public function render_google_analytics(): void {
		$code = \Noorifa\Settings\Layout::all()['integrations']['google_analytics_code'] ?? '';

		if ( ! $code ) {
			return;
		}

		echo $code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Meta/Facebook Pixel — the real snippet pasted from Meta's own Events
	 * Manager, printed as-is.
	 */
	public function render_facebook_pixel(): void {
		$code = \Noorifa\Settings\Layout::all()['integrations']['facebook_pixel_code'] ?? '';

		if ( ! $code ) {
			return;
		}

		echo $code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
