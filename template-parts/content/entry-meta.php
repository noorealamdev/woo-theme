<?php
/**
 * Post meta: date, author, categories.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="entry-meta">
	<span class="entry-meta__date">
		<time class="entry-meta__published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
			<?php echo esc_html( get_the_date() ); ?>
		</time>
	</span>

	<span class="entry-meta__author">
		<?php
		printf(
			/* translators: %s: post author display name. */
			esc_html__( 'by %s', 'ecombon' ),
			'<a class="entry-meta__author-link" href="' . esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a>'
		);
		?>
	</span>

	<?php if ( has_category() ) : ?>
		<span class="entry-meta__categories">
			<?php the_category( ', ' ); ?>
		</span>
	<?php endif; ?>
</div>
