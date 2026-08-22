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
		add_filter( 'mce_css', array( $this, 'exclude_frontend_stylesheet_from_classic_editor' ) );
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
		// Without this, the block editor canvas never loads the theme's own
		// CSS at all — every Noorifa Core block (Button, Hero, Advanced
		// Heading, …) falls back to its own bare, unstyled/placeholder look
		// while editing (e.g. Button's own style.scss sets a plain blue
		// `#2c6ecb` background purely as a "something is visibly a button"
		// fallback; the real black/pill look lives entirely in this file,
		// which only ever ran on the real frontend before this). Since
		// WP 5.9 the block canvas renders inside its own `<iframe>`, so
		// loading the full frontend stylesheet here can't leak out and
		// clash with the surrounding editor UI chrome — that isolation is
		// the whole reason `add_editor_style()` is safe to point at a
		// theme's real stylesheet instead of a hand-maintained editor-only
		// subset. One real gap remains: rules gated behind a `body` class
		// this theme only ever adds on the real frontend (e.g.
		// `body.noorifa-btn-style-2` for a non-default Button Style choice
		// under Noorifa → Buttons) still can't match inside the editor's
		// own iframe body, so the canvas shows the theme's default look for
		// those, not whichever style a site owner picked — everything else
		// (colors, typography, the base button/heading/hero look) matches
		// the real frontend exactly.
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/main.css' );
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
	 * Keeps `add_editor_style()`'s frontend stylesheet out of the classic/
	 * TinyMCE editor specifically, while leaving it in place for the block
	 * editor (both consume the exact same `add_editor_style()` registration
	 * via `get_editor_stylesheets()` — there's no separate opt-in for one
	 * without the other, only this filter to subtract it back out again).
	 *
	 * The block editor genuinely needs it: every Noorifa Core block
	 * (Button, Hero, Advanced Heading, …) previews its real colors/
	 * typography/layout there instead of a bare placeholder look — see
	 * the comment above `add_editor_style()` in `add_theme_support()`.
	 *
	 * The classic editor gets none of that benefit (it renders plain rich
	 * text, no blocks) and only inherits collateral damage from a
	 * 2900+ line frontend stylesheet full of resets and component styling
	 * never written with a generic content field in mind — confirmed
	 * directly: the sitewide `ul, li { list-style-type: none }` reset and
	 * the `body { margin: 0; padding: 0; … }` reset both leaked into
	 * WooCommerce's product description field this way, stripping list
	 * markers and all breathing room from ordinary editor content.
	 *
	 * @param string $mce_css Comma-delimited list of stylesheet URLs TinyMCE loads.
	 * @return string
	 */
	public function exclude_frontend_stylesheet_from_classic_editor( $mce_css ) {
		if ( '' === (string) $mce_css ) {
			return $mce_css;
		}

		$frontend_stylesheet = NOORIFA_THEME_URI . '/assets/css/main.css';

		$stylesheets = array_filter(
			array_map( 'trim', explode( ',', $mce_css ) ),
			static function ( $url ) use ( $frontend_stylesheet ) {
				return 0 !== strpos( $url, $frontend_stylesheet );
			}
		);

		return implode( ',', $stylesheets );
	}

	/**
	 * Turns on the block editor's native Line height typography control,
	 * and sets the site's default block spacing (the gap WordPress core
	 * inserts between two stacked blocks with no gap of their own — the
	 * `:root :where(.is-layout-flow) > *` / `.is-layout-constrained` /
	 * `.is-layout-flex`/`.is-layout-grid` rules core's global styles engine
	 * prints on every page).
	 *
	 * Font weight and letter spacing already show in the Styles > Typography
	 * panel because WordPress core defaults `typography.fontWeight` and
	 * `typography.letterSpacing` to true. Line height is the exception: core
	 * defaults `typography.lineHeight` to false, and — despite the docs —
	 * `add_theme_support( 'appearance-tools' )` does NOT flip it on for a
	 * classic (non-theme.json) theme like this one (verified: the resolved
	 * global setting stays false with appearance-tools active).
	 *
	 * Block spacing is a separate, unrelated fix riding the same filter:
	 * core's own default is 24px (`styles.spacing.blockGap` unset falls
	 * back to that), which is *not* a value in this codebase to search for
	 * and edit directly — it only ever exists as this filtered-in override
	 * or core's own hardcoded fallback, never as a static rule in any
	 * stylesheet here.
	 *
	 * Rather than add a whole theme.json for either of these two flags,
	 * inject both through the same theme.json data filter. Zero output
	 * cost for the line-height half until a site owner actually sets a
	 * value; the block-gap half always prints (it's a real default, not an
	 * opt-in control).
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
				'styles'   => array(
					'spacing' => array(
						'blockGap' => '13px',
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
