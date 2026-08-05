<?php
/**
 * Assets component.
 *
 * @package Noorifa
 */

namespace Noorifa\Setup;

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
		wp_enqueue_style( 'noorifa-bootstrap', NOORIFA_THEME_URI . '/assets/css/bootstrap-custom.css', array(), '5.3.3' );
		wp_enqueue_style( 'noorifa-swiper', NOORIFA_THEME_URI . '/assets/css/swiper-bundle.min.css', array(), '11' );

		wp_enqueue_style( 'noorifa-style', get_stylesheet_uri(), array(), NOORIFA_VERSION );
		wp_enqueue_style(
			'noorifa-main',
			NOORIFA_THEME_URI . '/assets/css/main.css',
			array( 'noorifa-bootstrap', 'noorifa-swiper', 'noorifa-style' ),
			NOORIFA_VERSION
		);

		if ( $this->is_product_gallery_context() ) {
			wp_enqueue_style( 'noorifa-drift', NOORIFA_THEME_URI . '/assets/css/drift-basic.min.css', array(), '1.5' );
			wp_enqueue_style( 'noorifa-photoswipe', NOORIFA_THEME_URI . '/assets/css/photoswipe.css', array(), '5' );
		}
	}

	/**
	 * Enqueues the theme scripts.
	 */
	public function enqueue_scripts(): void {
		$plugin_uri = NOORIFA_THEME_URI . '/assets/js/plugin';
		$footer     = array(
			'in_footer' => true,
			'strategy'  => 'defer',
		);

		// WordPress's core jQuery always calls noConflict(), so the bare
		// global `$` several vendor scripts below expect (carousel.js,
		// zoom.js) doesn't exist unless we restore it.
		wp_add_inline_script( 'jquery', 'window.$ = window.jQuery;' );

		wp_enqueue_script( 'noorifa-bootstrap-js', "{$plugin_uri}/bootstrap.min.js", array(), '5.3.3', $footer );
		wp_enqueue_script( 'noorifa-swiper-js', "{$plugin_uri}/swiper-bundle.min.js", array(), '11', $footer );

		wp_enqueue_script(
			'noorifa-carousel',
			NOORIFA_THEME_URI . '/assets/js/carousel.js',
			array( 'jquery', 'noorifa-swiper-js' ),
			NOORIFA_VERSION,
			$footer
		);
		wp_enqueue_script(
			'noorifa-main-js',
			NOORIFA_THEME_URI . '/assets/js/main.js',
			array( 'jquery', 'noorifa-bootstrap-js', 'noorifa-swiper-js' ),
			NOORIFA_VERSION,
			$footer
		);

		// Real-time results in the header search modal — see
		// Noorifa\Search\LiveSearch. The modal (template-parts/header/
		// search-modal.php) is rendered on every page via footer.php, so
		// this loads unconditionally too.
		wp_enqueue_script(
			'noorifa-search',
			NOORIFA_THEME_URI . '/assets/js/noorifa-search.js',
			array( 'jquery', 'noorifa-bootstrap-js' ),
			NOORIFA_VERSION,
			$footer
		);
		wp_localize_script(
			'noorifa-search',
			'noorifaSearchParams',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'noorifa_live_search' ),
				'minChars' => \Noorifa\Search\LiveSearch::MIN_CHARS,
			)
		);

		if ( $this->is_shop_context() ) {
			wp_enqueue_script( 'noorifa-nouislider', "{$plugin_uri}/nouislider.min.js", array(), '15', $footer );
			wp_enqueue_script( 'noorifa-shop', NOORIFA_THEME_URI . '/assets/js/noorifa-shop.js', array( 'jquery', 'noorifa-nouislider' ), NOORIFA_VERSION, $footer );
		}

		if ( $this->is_product_gallery_context() ) {
			wp_enqueue_script( 'noorifa-drift-js', "{$plugin_uri}/drift.min.js", array(), '1.5', $footer );
			wp_enqueue_script( 'noorifa-photoswipe-js', "{$plugin_uri}/photoswipe.umd.min.js", array(), '5', $footer );
			wp_enqueue_script( 'noorifa-photoswipe-lightbox-js', "{$plugin_uri}/photoswipe-lightbox.umd.min.js", array( 'noorifa-photoswipe-js' ), '5', $footer );
			wp_enqueue_script( 'noorifa-zoom-js', NOORIFA_THEME_URI . '/assets/js/zoom.js', array( 'jquery', 'noorifa-drift-js' ), NOORIFA_VERSION, $footer );

			// Only depends on jQuery, not WooCommerce's own
			// 'wc-add-to-cart-variation' handle: that script is enqueued
			// later, from inside the variable-product add-to-cart template
			// itself, so it isn't reliably enqueued yet at this point for
			// dependency resolution. This script listens for its
			// `wc_variation_form` event via document-level delegation
			// instead, which works regardless of load order — and simply
			// never fires on a simple-product page where that event never
			// happens.
			wp_enqueue_script( 'noorifa-product-variations', NOORIFA_THEME_URI . '/assets/js/noorifa-product-variations.js', array( 'jquery' ), NOORIFA_VERSION, $footer );
			wp_enqueue_script( 'noorifa-product-tabs', NOORIFA_THEME_URI . '/assets/js/noorifa-product-tabs.js', array( 'jquery', 'noorifa-bootstrap-js' ), NOORIFA_VERSION, $footer );
			wp_enqueue_script( 'noorifa-sticky-add-to-cart', NOORIFA_THEME_URI . '/assets/js/noorifa-sticky-add-to-cart.js', array( 'jquery', 'noorifa-product-variations' ), NOORIFA_VERSION, $footer );
		}

		if ( class_exists( 'WooCommerce' ) ) {
			// Deliberately NOT enqueuing WooCommerce's own 'wc-cart' script
			// here: its global `added_to_cart` handler assumes it's running
			// on the actual Cart page and calls `window.location.reload()`
			// when `.woocommerce-cart-form` isn't found — which is every
			// other page, including this drawer. The shipping-calculator
			// and coupon panels get their own toggle in noorifa-cart.js, and
			// both forms are plain POSTs that WooCommerce's global
			// `wp_loaded` cart-action handler processes either way.
			$cart_deps = array( 'jquery', 'noorifa-bootstrap-js' );
			if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
				$cart_deps[] = 'wc-add-to-cart';
			}
			wp_enqueue_script( 'noorifa-cart', NOORIFA_THEME_URI . '/assets/js/noorifa-cart.js', $cart_deps, NOORIFA_VERSION, $footer );
			wp_localize_script(
				'noorifa-cart',
				'noorifaCartParams',
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
