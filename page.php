<?php
/**
 * The template for standard WordPress pages.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/*
 * Cart, checkout and my account are real WP Pages too (routed through this
 * same template). Account already renders its own complete, self-boxed
 * `.woocommerce` wrapper (styled in main.css) — wrapping it in the
 * plain-page `.content-area` boxed container below would nest one
 * boxed-width container inside another. Same reasoning for the page-title
 * banner: it must render as a true top-level section, a direct sibling of
 * the header (matching the reference), not nested inside `.content-area`.
 *
 * Cart and Checkout stay on WooCommerce's modern Cart/Checkout BLOCKS
 * (`<!-- wp:woocommerce/cart -->` / `<!-- wp:woocommerce/checkout -->` in
 * each page's real content — this theme deliberately doesn't use the
 * legacy `[woocommerce_cart]`/`[woocommerce_checkout]` shortcodes or their
 * classic PHP templates at all), matching how a real, currently created
 * WooCommerce store sets these pages up by default. Neither block gets a
 * page-title banner or container width from WooCommerce itself, so both
 * are added here instead. `is_order_received_page()` excludes the
 * checkout's own thank-you step — that already has its own real hero via
 * woocommerce/checkout/thankyou.php, so this must not double up on it.
 */
$is_wc_page            = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() );
$is_account_page       = function_exists( 'is_account_page' ) && is_account_page();
$is_checkout_form_page = function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page();
$is_boxed_wc_page      = $is_wc_page && ( ! function_exists( 'is_checkout' ) || ! is_checkout() || $is_checkout_form_page );

if ( $is_account_page ) {
	get_template_part( 'template-parts/account/page-title' );
} elseif ( function_exists( 'is_cart' ) && is_cart() ) {
	get_template_part( 'template-parts/cart/page-title' );
} elseif ( $is_checkout_form_page ) {
	get_template_part( 'template-parts/checkout/page-title' );
} elseif ( ! $is_wc_page ) {
	get_template_part(
		'template-parts/global/page-title',
		null,
		array( 'title' => get_the_title( get_queried_object_id() ) )
	);
}

if ( $is_wc_page || $is_account_page ) {
	?>
	<?php if ( $is_boxed_wc_page ) : ?>
		<div class="container flat-spacing-2">
	<?php endif; ?>
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
	<?php if ( $is_boxed_wc_page ) : ?>
		</div>
	<?php endif; ?>
	<?php
} else {
	?>
	<div id="primary" class="site-main">
		<div class="content-area content-area--single">
			<div class="content-area__main">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/content-page' );

					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
				endwhile;
				?>
			</div>
		</div>
	</div>
	<?php
}

get_footer();
