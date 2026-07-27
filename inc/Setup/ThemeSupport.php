<?php
/**
 * ThemeSupport component.
 *
 * @package Ecombon
 */

namespace Ecombon\Setup;

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
		// Deliberately NOT declaring wc-product-gallery-zoom/lightbox/slider:
		// the single product gallery is a full custom replacement (the
		// theme's own swiper + zoom.js + photoswipe), not WooCommerce's
		// default one.
		// Those supports would make WC enqueue its own competing zoom/
		// flexslider/photoswipe scripts expecting markup that doesn't exist
		// here, which throws (see template-parts/product/gallery.php).
		add_theme_support( 'woocommerce' );

		load_theme_textdomain( 'ecombon', ECOMBON_THEME_DIR . '/languages' );
	}

	/**
	 * Sets the default content width used by embeds and oEmbed markup.
	 */
	public function set_content_width(): void {
		$GLOBALS['content_width'] = apply_filters( 'ecombon_content_width', 1200 );
	}
}
