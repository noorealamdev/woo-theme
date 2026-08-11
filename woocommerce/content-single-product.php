<?php
/**
 * Single product content override.
 *
 * The gallery and buy-box layout is custom; the actual add-to-cart form,
 * attributes table, and reviews reuse WooCommerce's own real templates/
 * functions — see template-parts/product/*.php.
 *
 * Kept in sync with WC core's own hooks (see Noorifa\Hooks\TemplateHooks
 * for the default-callback removals that keep gallery.php/summary.php/
 * tabs.php/related.php from being duplicated by them).
 *
 * @package Noorifa
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

do_action( 'woocommerce_before_single_product' );

get_template_part( 'template-parts/product/breadcrumb' );
?>

<section class="section-product-single main-product section-image-zoom">
	<div class="container">
		<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'row', $product ); ?>>
			<div class="col-md-6">
				<?php
				/**
				 * Real hook — its default sale-flash/gallery callbacks are
				 * removed in Noorifa\Hooks\TemplateHooks since gallery.php
				 * below is a full custom replacement of WC's own product
				 * gallery (see ThemeSupport's deliberate non-support of
				 * wc-product-gallery-*); kept open for extensions (e.g. a
				 * "new"/"pre-order" ribbon plugin).
				 */
				do_action( 'woocommerce_before_single_product_summary' );

				get_template_part( 'template-parts/product/gallery' );
				?>
			</div>
			<div class="col-md-6">
				<?php
				get_template_part( 'template-parts/product/summary' );

				/**
				 * Real hook — every default callback that would render
				 * title/rating/price/excerpt/add-to-cart/meta/sharing is
				 * removed in Noorifa\Hooks\TemplateHooks, since
				 * summary.php above is a full custom replacement of all
				 * of it — except WC_Structured_Data::generate_product_data()
				 * (priority 60), which is real SEO JSON-LD output with no
				 * visible markup, so it's deliberately left in place. This
				 * still fires for any extension (product add-ons, bundles,
				 * subscriptions, deposits, warranty upsells, etc.) that
				 * hooks into the real buy-box position.
				 */
				do_action( 'woocommerce_single_product_summary' );
				?>
			</div>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/product/sticky-add-to-cart' );

get_template_part( 'template-parts/product/tabs' );
get_template_part( 'template-parts/product/related' );

/**
 * Real hook — its default tabs/upsell/related-products callbacks are
 * removed in Noorifa\Hooks\TemplateHooks since tabs.php/related.php above
 * are full custom replacements of all three; kept open for extensions
 * (e.g. "recently viewed", "trust badges", "size guide" plugins commonly
 * hook in here).
 */
do_action( 'woocommerce_after_single_product_summary' );

do_action( 'woocommerce_after_single_product' );
