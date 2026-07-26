<?php
/**
 * Post card used on the blog index, archives and search results.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="entry-card__thumbnail" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'medium_large' ); ?>
		</a>
	<?php endif; ?>

	<div class="entry-card__body">
		<?php
		the_title(
			sprintf( '<h2 class="entry-card__title"><a href="%s">', esc_url( get_permalink() ) ),
			'</a></h2>'
		);

		get_template_part( 'template-parts/content/entry-meta' );
		?>

		<div class="entry-card__excerpt">
			<?php the_excerpt(); ?>
		</div>

		<a href="<?php the_permalink(); ?>" class="entry-card__more">
			<?php esc_html_e( 'Read more', 'ecombon' ); ?>
		</a>
	</div>
</article>
