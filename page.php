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
 * same template), but each already renders its own complete, self-boxed
 * structure (cart.php/checkout.php's own real .container sections, the
 * account .woocommerce wrapper styled in main.css) — wrapping any of
 * them in the plain-page .content-area boxed container below would nest one
 * boxed-width container inside another. Same reasoning for the page-title
 * banner: it must render as a true top-level section, a direct sibling of
 * the header (matching the reference), not nested inside .content-area.
 */
$is_wc_page      = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() );
$is_account_page = function_exists( 'is_account_page' ) && is_account_page();

if ( $is_account_page ) {
	get_template_part( 'template-parts/account/page-title' );
} elseif ( ! $is_wc_page ) {
	get_template_part(
		'template-parts/global/page-title',
		null,
		array( 'title' => get_the_title( get_queried_object_id() ) )
	);
}

if ( $is_wc_page || $is_account_page ) {
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
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
