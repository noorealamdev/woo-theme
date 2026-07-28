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
<article id="post-<?php the_ID(); ?>" <?php post_class( 'article-blog hover-img' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="blog-image img-style">
			<?php the_post_thumbnail( 'medium_large' ); ?>
		</a>
	<?php endif; ?>

	<div class="blog-content">
		<p class="entry-date text-caption-01 fw-semibold cl-text-3"><?php echo esc_html( get_the_date( 'j F' ) ); ?></p>
		<?php
		the_title(
			sprintf( '<h5 class="entry-title"><a href="%s" class="link-underline link">', esc_url( get_permalink() ) ),
			'</a></h5>'
		);
		?>
		<p class="entry-desc cl-text-2"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
	</div>
</article>
