<?php
/**
 * Content for single blog posts.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>
	<header class="entry-header">
		<?php
		if ( has_category() ) :
			?>
			<div class="entry-header__categories"><?php the_category( ', ' ); ?></div>
		<?php endif; ?>

		<?php the_title( '<h1 class="entry-header__title">', '</h1>' ); ?>

		<?php get_template_part( 'template-parts/content/entry-meta' ); ?>
	</header>

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
				'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'ecombon' ),
				'after'  => '</nav>',
			)
		);
		?>
	</div>

	<?php if ( has_tag() ) : ?>
		<footer class="entry-footer">
			<div class="entry-footer__tags">
				<?php the_tags( '', ', ' ); ?>
			</div>
		</footer>
	<?php endif; ?>

	<nav class="post-navigation" aria-label="<?php esc_attr_e( 'More posts', 'ecombon' ); ?>">
		<div class="post-navigation__prev"><?php previous_post_link( '%link', '&larr; %title' ); ?></div>
		<div class="post-navigation__next"><?php next_post_link( '%link', '%title &rarr;' ); ?></div>
	</nav>
</article>
