<?php
/**
 * Content for pages. Only ever called for plain pages — page.php routes
 * cart/checkout/my-account (and their own page-title banners) around this
 * template entirely, since each already renders its own complete, self-
 * boxed structure.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--page' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry-thumbnail">
			<?php the_post_thumbnail( 'large' ); ?>
		</div>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'noorifa' ),
				'after'  => '</nav>',
			)
		);
		?>
	</div>
</article>
