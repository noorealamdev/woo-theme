<?php
/**
 * The comments template.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-area__title">
			<?php
			$comment_count = get_comments_number();
			if ( 1 === (int) $comment_count ) {
				esc_html_e( '1 Comment', 'ecombon' );
			} else {
				printf(
					/* translators: %s: number of comments. */
					esc_html( _n( '%s Comment', '%s Comments', $comment_count, 'ecombon' ) ),
					esc_html( number_format_i18n( $comment_count ) )
				);
			}
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>

		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="comments-area__closed"><?php esc_html_e( 'Comments are closed.', 'ecombon' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply' => __( 'Leave a comment', 'ecombon' ),
		)
	);
	?>
</div>
