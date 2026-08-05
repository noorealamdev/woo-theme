<?php
/**
 * TemplateHooks component.
 *
 * @package Noorifa
 */

namespace Noorifa\Hooks;

use Noorifa\Setup\ComponentInterface;

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

		// WooCommerce's own `woocommerce_breadcrumb()` (hooked to
		// `woocommerce_before_main_content` at priority 20) renders a second,
		// unstyled breadcrumb on top of the theme's own real one (the
		// page-title template part on shop/category archives) — remove the
		// default. Single product pages intentionally have no breadcrumb at
		// all — their own breadcrumb-nav.php was removed outright.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

		// The shop archive (woocommerce/archive-product.php) still fires
		// every one of these real hooks — so third-party extensions that
		// hook into them keep working — but removes WC's own default
		// callback on each where the theme already has its own equivalent
		// UI, to avoid rendering it twice. See archive-product.php's own
		// comments at each call site for which default pairs with which.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header' );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
		remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );

		// woocommerce_sidebar's default callback falls back to loading the
		// theme's own real sidebar.php (the blog sidebar — search/
		// categories/recent posts) whenever no dedicated sidebar-shop.php
		// exists, on *every* real WooCommerce page that fires this hook —
		// including WC core's own unmodified single-product.php, which
		// this theme doesn't override. This theme has no WC sidebar
		// widget-area concept on shop/product pages at all, so remove the
		// default outright; the hook itself is left firing for extensions.
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		// The single product page (woocommerce/content-single-product.php)
		// keeps every one of these real hooks firing too, for the same
		// reason — but strips WC's own default gallery/title/price/
		// add-to-cart/meta/tabs/related-products callbacks, since
		// template-parts/product/gallery.php, summary.php, tabs.php, and
		// related.php are full custom replacements of all of them.
		// WC_Structured_Data::generate_product_data() is deliberately left
		// on woocommerce_single_product_summary — it only outputs SEO
		// JSON-LD, no visible markup.
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

		// Individual reviews (woocommerce/single-product/review.php) keep
		// every review hook firing too, but strip WC's own default
		// gravatar/rating/meta/comment-text callbacks, since the theme's
		// own avatar/star/author/comment markup there already renders all
		// of it directly rather than through these hooks.
		remove_action( 'woocommerce_review_before', 'woocommerce_review_display_gravatar', 10 );
		remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10 );
		remove_action( 'woocommerce_review_meta', 'woocommerce_review_display_meta', 10 );
		remove_action( 'woocommerce_review_comment_text', 'woocommerce_review_display_comment_text', 10 );

		// Checkout uses WooCommerce's own default checkout/form-checkout.php
		// template (not overridden) so the real field/order-review/payment
		// structure and hooks stay exactly as WooCommerce ships them — the
		// theme only adds its own page-title banner via this real hook,
		// same as every other page-title in the theme.
		add_action( 'woocommerce_before_checkout_form', array( $this, 'checkout_page_title' ), 5 );
	}

	/**
	 * Renders the theme's page-title banner above the default checkout form.
	 */
	public function checkout_page_title(): void {
		get_template_part( 'template-parts/checkout/page-title' );
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
		// Single posts are full-width in the reference (no sidebar column) —
		// only the blog listing (home/archive/search) really uses one.
		$has_sidebar = is_home() || is_archive() || is_search();

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
	 * Adds the theme's footer link styling to the footer nav menus.
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
