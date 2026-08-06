<?php
/**
 * CustomCode component.
 *
 * @package Noorifa
 */

namespace Noorifa\Setup;

/**
 * Outputs the real site owner-authored CSS/JS from Appearance/Noorifa >
 * Custom CSS/JS, verbatim (see Schema::sanitize_value()'s 'raw_html' case
 * for why that's deliberately unsanitized — this REST route already
 * requires manage_options). Plain code, not full snippets — this class
 * supplies the wrapping `<style>`/`<script>` tags itself, same as WP
 * core's own Customizer "Additional CSS" panel. Neither prints anything
 * at all until a site owner has actually written real code — an
 * untouched install's markup is unaffected.
 */
class CustomCode implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		// Late priority so this can override the theme's own CSS.
		add_action( 'wp_head', array( $this, 'render_css' ), 100 );
		// Footer, not head: same non-blocking placement as every other
		// theme script (see Assets::enqueue_scripts()'s `in_footer`).
		add_action( 'wp_footer', array( $this, 'render_js' ), 100 );
	}

	/**
	 * The real custom CSS a site owner wrote, wrapped in its own
	 * `<style>` tag.
	 */
	public function render_css(): void {
		$css = \Noorifa\Settings\Layout::all()['custom_code']['css'] ?? '';

		if ( ! $css ) {
			return;
		}

		echo '<style id="noorifa-custom-css">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * The real custom JS a site owner wrote, wrapped in its own
	 * `<script>` tag.
	 */
	public function render_js(): void {
		$js = \Noorifa\Settings\Layout::all()['custom_code']['js'] ?? '';

		if ( ! $js ) {
			return;
		}

		echo '<script id="noorifa-custom-js">' . $js . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
