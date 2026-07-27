<?php
/**
 * Content for pages.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
/*
 * Core WooCommerce pages (cart, checkout, my account) render their own
 * complete page-title banner + layout via their real template overrides
 * (e.g. woocommerce/cart/cart.php) — the generic title/thumbnail wrapper
 * below would just duplicate it.
 */
$is_wc_page = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--page' ); ?>>
	<?php if ( ! $is_wc_page ) : ?>
		<header class="entry-header">
			<?php the_title( '<h1 class="entry-header__title">', '</h1>' ); ?>
		</header>
	<?php endif; ?>

	<?php if ( ! $is_wc_page && has_post_thumbnail() ) : ?>
		<div class="entry-thumbnail">
			<?php the_post_thumbnail( 'large' ); ?>
		</div>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'ecombon' ),
				'after'  => '</nav>',
			)
		);
		?>
	</div>
</article>
