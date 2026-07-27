<?php
/**
 * Single review: real avatar, author, date, star rating (from real comment
 * meta) and comment text — restyled with the theme's own `.box-comment`
 * markup.
 *
 * Closing </li> is left out on purpose — wp_list_comments()'s walker adds
 * it (and nests real threaded replies inside, as a real <ol class="children">,
 * before doing so; see the `.children` rule in assets/scss/elements/_section.scss
 * for how those get the same indent/border treatment as any other reply).
 *
 * @package Ecombon
 */

defined( 'ABSPATH' ) || exit;

global $comment;

$rating = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
?>
<li <?php comment_class( 'box-comment' ); ?> id="li-comment-<?php comment_ID(); ?>">
	<div id="comment-<?php comment_ID(); ?>" class="comment_container">
		<?php if ( '0' === $comment->comment_approved ) : ?>
			<p class="meta">
				<em><?php esc_html_e( 'Your review is awaiting approval', 'ecombon' ); ?></em>
			</p>
		<?php else : ?>
			<div class="comment_info">
				<div class="info_image">
					<?php echo get_avatar( $comment, apply_filters( 'woocommerce_review_gravatar_size', '60' ), '' ); ?>
				</div>
				<div class="info_author">
					<p class="h6 author__name"><?php comment_author(); ?></p>
					<p class="author_date text-caption-01 cl-text-3"><?php echo esc_html( get_comment_date( wc_date_format() ) ); ?></p>
				</div>
			</div>

			<?php if ( $rating && wc_review_ratings_enabled() ) : ?>
				<div class="star-wrap normal d-flex align-items-center mb-8">
					<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
						<i class="icon <?php echo esc_attr( $star <= $rating ? 'icon-Star' : 'icon-Star-thin' ); ?>"></i>
					<?php endfor; ?>
				</div>
			<?php endif; ?>

			<div class="comment_text text-body-1">
				<?php comment_text(); ?>
			</div>
		<?php endif; ?>
	</div>
