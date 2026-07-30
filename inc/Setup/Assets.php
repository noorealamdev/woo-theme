<?php
/**
 * Assets component.
 *
 * @package Ecombon
 */

namespace Ecombon\Setup;

/**
 * Registers and conditionally enqueues the theme's CSS and JS.
 *
 * The design system (fonts, icon font, vendor libraries, main.css, and the
 * vendor carousel/menu/offcanvas scripts) is the theme's own; see
 * assets/css/main.css and assets/js for the source.
 */
class Assets implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Every WooCommerce page is fully restyled with the theme's own CSS
		// (see template-parts/product, /shop, /header/cart-drawer.php) —
		// WC's default stylesheets would just fight it for specificity
		// (e.g. its brand-purple `.button.alt` winning over ours).
		add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
	}

	/**
	 * Enqueues the theme stylesheets.
	 */
	public function enqueue_styles(): void {
		// Custom Bootstrap build (grid, dropdown, offcanvas, modal,
		// nav/tabs, progress, and utilities only, not the full bundle) —
		// plain hand-maintained CSS now, no build step.
		wp_enqueue_style( 'ecombon-bootstrap', ECOMBON_THEME_URI . '/assets/css/bootstrap-custom.css', array(), '5.3.3' );
		wp_enqueue_style( 'ecombon-swiper', ECOMBON_THEME_URI . '/assets/css/swiper-bundle.min.css', array(), '11' );

		wp_enqueue_style( 'ecombon-style', get_stylesheet_uri(), array(), ECOMBON_VERSION );
		wp_enqueue_style(
			'ecombon-main',
			ECOMBON_THEME_URI . '/assets/css/main.css',
			array( 'ecombon-bootstrap', 'ecombon-swiper', 'ecombon-style' ),
			ECOMBON_VERSION
		);

		if ( $this->is_product_gallery_context() ) {
			wp_enqueue_style( 'ecombon-drift', ECOMBON_THEME_URI . '/assets/css/drift-basic.min.css', array(), '1.5' );
			wp_enqueue_style( 'ecombon-photoswipe', ECOMBON_THEME_URI . '/assets/css/photoswipe.css', array(), '5' );
		}
	}

	/**
	 * Enqueues the theme scripts.
	 */
	public function enqueue_scripts(): void {
		$plugin_uri = ECOMBON_THEME_URI . '/assets/js/plugin';
		$footer     = array(
			'in_footer' => true,
			'strategy'  => 'defer',
		);

		// WordPress's core jQuery always calls noConflict(), so the bare
		// global `$` several vendor scripts below expect (carousel.js,
		// zoom.js) doesn't exist unless we restore it.
		wp_add_inline_script( 'jquery', 'window.$ = window.jQuery;' );

		wp_enqueue_script( 'ecombon-bootstrap-js', "{$plugin_uri}/bootstrap.min.js", array(), '5.3.3', $footer );
		wp_enqueue_script( 'ecombon-swiper-js', "{$plugin_uri}/swiper-bundle.min.js", array(), '11', $footer );

		wp_enqueue_script(
			'ecombon-carousel',
			ECOMBON_THEME_URI . '/assets/js/carousel.js',
			array( 'jquery', 'ecombon-swiper-js' ),
			ECOMBON_VERSION,
			$footer
		);
		wp_enqueue_script(
			'ecombon-main-js',
			ECOMBON_THEME_URI . '/assets/js/main.js',
			array( 'jquery', 'ecombon-bootstrap-js', 'ecombon-swiper-js' ),
			ECOMBON_VERSION,
			$footer
		);

		// Real-time results in the header search modal — see
		// Ecombon\Search\LiveSearch. The modal (template-parts/header/
		// search-modal.php) is rendered on every page via footer.php, so
		// this loads unconditionally too.
		wp_enqueue_script(
			'ecombon-search',
			ECOMBON_THEME_URI . '/assets/js/ecombon-search.js',
			array( 'jquery', 'ecombon-bootstrap-js' ),
			ECOMBON_VERSION,
			$footer
		);
		wp_localize_script(
			'ecombon-search',
			'ecombonSearchParams',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ecombon_live_search' ),
				'minChars' => \Ecombon\Search\LiveSearch::MIN_CHARS,
			)
		);

		if ( $this->is_shop_context() ) {
			wp_enqueue_script( 'ecombon-nouislider', "{$plugin_uri}/nouislider.min.js", array(), '15', $footer );
			wp_enqueue_script( 'ecombon-shop', ECOMBON_THEME_URI . '/assets/js/ecombon-shop.js', array( 'jquery', 'ecombon-nouislider' ), ECOMBON_VERSION, $footer );
		}

		if ( $this->is_product_gallery_context() ) {
			wp_enqueue_script( 'ecombon-drift-js', "{$plugin_uri}/drift.min.js", array(), '1.5', $footer );
			wp_enqueue_script( 'ecombon-photoswipe-js', "{$plugin_uri}/photoswipe.umd.min.js", array(), '5', $footer );
			wp_enqueue_script( 'ecombon-photoswipe-lightbox-js', "{$plugin_uri}/photoswipe-lightbox.umd.min.js", array( 'ecombon-photoswipe-js' ), '5', $footer );
			wp_enqueue_script( 'ecombon-zoom-js', ECOMBON_THEME_URI . '/assets/js/zoom.js', array( 'jquery', 'ecombon-drift-js' ), ECOMBON_VERSION, $footer );

			// Only depends on jQuery, not WooCommerce's own
			// 'wc-add-to-cart-variation' handle: that script is enqueued
			// later, from inside the variable-product add-to-cart template
			// itself, so it isn't reliably enqueued yet at this point for
			// dependency resolution. This script listens for its
			// `wc_variation_form` event via document-level delegation
			// instead, which works regardless of load order — and simply
			// never fires on a simple-product page where that event never
			// happens.
			wp_enqueue_script( 'ecombon-product-variations', ECOMBON_THEME_URI . '/assets/js/ecombon-product-variations.js', array( 'jquery' ), ECOMBON_VERSION, $footer );
			wp_enqueue_script( 'ecombon-product-tabs', ECOMBON_THEME_URI . '/assets/js/ecombon-product-tabs.js', array( 'jquery', 'ecombon-bootstrap-js' ), ECOMBON_VERSION, $footer );
			wp_enqueue_script( 'ecombon-sticky-add-to-cart', ECOMBON_THEME_URI . '/assets/js/ecombon-sticky-add-to-cart.js', array( 'jquery', 'ecombon-product-variations' ), ECOMBON_VERSION, $footer );
		}

		if ( class_exists( 'WooCommerce' ) ) {
			// Deliberately NOT enqueuing WooCommerce's own 'wc-cart' script
			// here: its global `added_to_cart` handler assumes it's running
			// on the actual Cart page and calls `window.location.reload()`
			// when `.woocommerce-cart-form` isn't found — which is every
			// other page, including this drawer. The shipping-calculator
			// and coupon panels get their own toggle in ecombon-cart.js, and
			// both forms are plain POSTs that WooCommerce's global
			// `wp_loaded` cart-action handler processes either way.
			$cart_deps = array( 'jquery', 'ecombon-bootstrap-js' );
			if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
				$cart_deps[] = 'wc-add-to-cart';
			}
			wp_enqueue_script( 'ecombon-cart', ECOMBON_THEME_URI . '/assets/js/ecombon-cart.js', $cart_deps, ECOMBON_VERSION, $footer );
			wp_localize_script(
				'ecombon-cart',
				'ecombonCartParams',
				array( 'checkoutUrl' => wc_get_checkout_url() )
			);

			// Safe here specifically: the reload landmine documented above
			// only fires when `.woocommerce-cart-form` is missing from the
			// page — on the real Cart page (woocommerce/cart/cart.php) it's
			// always present, so this gives real AJAX quantity/coupon/
			// shipping-calculator updates instead of a full form POST.
			if ( function_exists( 'is_cart' ) && is_cart() ) {
				wp_enqueue_script( 'wc-cart' );
			}
		}

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	 * Whether the current request is a single product page, where the zoom
	 * and lightbox gallery scripts are needed.
	 */
	private function is_product_gallery_context(): bool {
		return function_exists( 'is_product' ) && is_product();
	}

	/**
	 * Whether the current request is the shop page or a product taxonomy
	 * archive (category, tag, or attribute), where the filter panel lives.
	 */
	private function is_shop_context(): bool {
		return function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() );
	}
}
