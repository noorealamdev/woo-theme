<?php
/**
 * ThemeSupport component.
 *
 * @package Noorifa
 */

namespace Noorifa\Setup;

/**
 * Declares core WordPress and WooCommerce theme support.
 */
class ThemeSupport implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );
		add_action( 'after_setup_theme', array( $this, 'set_content_width' ) );
		add_filter( 'wp_theme_json_data_theme', array( $this, 'enable_line_height_setting' ) );
	}

	/**
	 * Declares the feature set the theme supports.
	 */
	public function add_theme_support(): void {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'custom-logo', array(
			'height'      => 60,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		) );
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		) );
		add_theme_support( 'customize-selective-refresh-widgets' );
		// Without this, the block editor hides the Dimensions (padding/
		// margin)/Border/Shadow controls in the Styles sidebar for EVERY
		// block — including WordPress's own core blocks, not just the
		// Noorifa Core plugin's — regardless of what each block's own
		// block.json `supports` declares. This is a classic (non-block)
		// theme with no theme.json, and WordPress only surfaces those
		// controls once a theme explicitly opts in. Zero cost when unused:
		// this only unlocks editor UI + the matching inline style output,
		// which stays empty unless a site owner actually sets a value.
		add_theme_support( 'appearance-tools' );
		// `appearance-tools` alone left Padding out of the Dimensions
		// panel (Margin/Block Spacing/Border showed correctly) — on a
		// classic theme, `enableCustomSpacing` (the flag that actually
		// gates the Padding control) falls back to this older, separate
		// `custom-spacing` support (see wp-includes/block-editor.php,
		// `get_theme_support( 'custom-spacing' )`) rather than reliably
		// inheriting from `appearance-tools`. Same zero-cost reasoning.
		add_theme_support( 'custom-spacing' );
		// Without this, the editor canvas never lets a block actually
		// reach "wide"/"full" width — blocks that declare
		// `"align": ["wide", "full"]` (e.g. the Noorifa Core plugin's Hero)
		// stay capped at the narrow default content column in the editor
		// even though their own CSS renders them correctly full-bleed on
		// the real frontend. Purely an editor-canvas concern; this theme's
		// blocks handle their own real frontend width already.
		add_theme_support( 'align-wide' );
		// Deliberately NOT declaring wc-product-gallery-zoom/lightbox/slider:
		// the single product gallery is a full custom replacement (the
		// theme's own swiper + zoom.js + photoswipe), not WooCommerce's
		// default one.
		// Those supports would make WC enqueue its own competing zoom/
		// flexslider/photoswipe scripts expecting markup that doesn't exist
		// here, which throws (see template-parts/product/gallery.php).
		add_theme_support( 'woocommerce' );

		// A translation file placed inside the theme's OWN /languages
		// folder (as opposed to the global WP_LANG_DIR) must be named just
		// `{locale}.mo` — e.g. `bn_BD.mo` for Bengali — with NO `noorifa-`
		// domain prefix. WordPress's own _load_textdomain_just_in_time()
		// only adds the domain prefix for files living outside the
		// template/stylesheet directory (see wp-includes/l10n.php);
		// getting this backwards (e.g. `noorifa-bn_BD.mo`) silently fails
		// with zero error — every __()/_e() call just falls through to a
		// NOOP_Translations instance and keeps showing English. Verified
		// this exact failure mode directly against this install's real
		// WP 7.0.2 core before landing on the correct filename. See
		// languages/README.txt for the translator-facing instructions.
		load_theme_textdomain( 'noorifa', NOORIFA_THEME_DIR . '/languages' );
	}

	/**
	 * Turns on the block editor's native Line height typography control.
	 *
	 * Font weight and letter spacing already show in the Styles > Typography
	 * panel because WordPress core defaults `typography.fontWeight` and
	 * `typography.letterSpacing` to true. Line height is the exception: core
	 * defaults `typography.lineHeight` to false, and — despite the docs —
	 * `add_theme_support( 'appearance-tools' )` does NOT flip it on for a
	 * classic (non-theme.json) theme like this one (verified: the resolved
	 * global setting stays false with appearance-tools active). Rather than
	 * add a whole theme.json just for this one flag, inject the single
	 * setting through the theme.json data filter, so the native Line height
	 * control appears alongside Font size for every block that opts into
	 * `supports.typography.lineHeight` (e.g. the Noorifa Core Feature Cards
	 * and Hero blocks). Zero output cost until a value is actually set.
	 *
	 * @param \WP_Theme_JSON_Data $theme_json The theme's theme.json data.
	 * @return \WP_Theme_JSON_Data
	 */
	public function enable_line_height_setting( $theme_json ) {
		if ( ! is_object( $theme_json ) || ! method_exists( $theme_json, 'update_with' ) ) {
			return $theme_json;
		}

		return $theme_json->update_with(
			array(
				'version'  => 2,
				'settings' => array(
					'typography' => array(
						'lineHeight' => true,
					),
				),
			)
		);
	}

	/**
	 * Sets the default content width used by embeds and oEmbed markup.
	 */
	public function set_content_width(): void {
		$GLOBALS['content_width'] = apply_filters( 'noorifa_content_width', 1200 );
	}
}
