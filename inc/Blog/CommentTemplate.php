<?php
/**
 * CommentTemplate component.
 *
 * @package Noorifa
 */

namespace Noorifa\Blog;

/**
 * Real `wp_list_comments()` callback rendering the theme's own `.box-comment`
 * markup — same real pattern as woocommerce/single-product/review.php (real
 * avatar, author, date, comment text; real threaded replies via WP's own
 * recursive `.children` list, not a hand-authored single-level demo).
 */
class CommentTemplate {

	/**
	 * Renders a single real comment.
	 *
	 * @param \WP_Comment $comment Comment being rendered.
	 * @param array       $args    wp_list_comments() args.
	 * @param int         $depth   Current nesting depth.
	 */
	public static function render( \WP_Comment $comment, array $args, int $depth ): void {
		?>
		<li <?php comment_class( 'box-comment' ); ?> id="li-comment-<?php comment_ID(); ?>">
			<div id="comment-<?php comment_ID(); ?>" class="comment_container">
				<?php if ( '0' === $comment->comment_approved ) : ?>
					<p class="meta">
						<em><?php esc_html_e( 'Your comment is awaiting moderation.', 'noorifa' ); ?></em>
					</p>
				<?php else : ?>
					<div class="comment_info">
						<div class="info_image">
							<?php echo get_avatar( $comment, 60 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<div class="info_author">
							<p class="h6 author__name"><?php comment_author(); ?></p>
							<p class="author_date text-caption-01 cl-text-3"><?php echo esc_html( get_comment_date( get_option( 'date_format' ) ) ); ?></p>
						</div>
					</div>
					<div class="comment_text text-body-1">
						<?php comment_text(); ?>
					</div>
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'add_below' => 'div-comment',
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],
								'before'    => '<div class="comment_reply_link">',
								'after'     => '</div>',
							)
						)
					);
					?>
				<?php endif; ?>
			</div>
		<?php
	}
}
