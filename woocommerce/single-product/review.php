<?php
/**
 * Single review: real avatar, author, date, star rating (from real comment
 * meta) and comment text — restyled with the theme's own `.box-comment`
 * markup.
 *
 * Closing </li> is left out on purpose — wp_list_comments()'s walker adds
 * it (and nests real threaded replies inside, as a real <ol class="children">,
 * before doing so; see the `.children` rule in assets/css/main.css
 * for how those get the same indent/border treatment as any other reply).
 *
 * Kept in sync with WC core's own review hooks (see Noorifa\Hooks\TemplateHooks
 * for the default-callback removals that keep this file's own avatar/
 * rating/meta/text markup from being duplicated by them).
 *
 * @package Noorifa
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

global $comment;

$rating = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
?>
<li <?php comment_class( 'box-comment' ); ?> id="li-comment-<?php comment_ID(); ?>">
	<div id="comment-<?php comment_ID(); ?>" class="comment_container">
		<?php
		/**
		 * Real hook — no default callback (the theme's own avatar/author
		 * markup below isn't hook-driven), kept open for extensions.
		 */
		do_action( 'woocommerce_review_before', $comment );
		?>
		<?php if ( '0' === $comment->comment_approved ) : ?>
			<p class="meta">
				<em><?php esc_html_e( 'Your review is awaiting approval', 'noorifa' ); ?></em>
			</p>
		<?php else : ?>
			<?php do_action( 'woocommerce_review_before_comment_meta', $comment ); ?>
			<div class="comment_info">
				<div class="info_image">
					<?php echo get_avatar( $comment, apply_filters( 'woocommerce_review_gravatar_size', '60' ), '' ); ?>
				</div>
				<div class="info_author">
					<p class="h6 author__name"><?php comment_author(); ?></p>
					<p class="author_date text-caption-01 cl-text-3"><?php echo esc_html( get_comment_date( wc_date_format() ) ); ?></p>
				</div>
			</div>
			<?php
			/**
			 * Real hook — no default callback, kept open for extensions
			 * (e.g. a "verified purchase" badge plugin).
			 */
			do_action( 'woocommerce_review_meta', $comment );
			?>

			<?php if ( $rating && wc_review_ratings_enabled() ) : ?>
				<div class="star-wrap normal d-flex align-items-center mb-8">
					<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
						<?php \Noorifa\Setup\Icons::render( $star <= $rating ? 'Star' : 'Star-thin' ); ?>
					<?php endfor; ?>
				</div>
			<?php endif; ?>

			<?php do_action( 'woocommerce_review_before_comment_text', $comment ); ?>
			<div class="comment_text">
				<?php
				/**
				 * Real hook — no default callback (comment_text() below
				 * isn't hook-driven), kept open for extensions (e.g. a
				 * review-photo-upload plugin).
				 */
				do_action( 'woocommerce_review_comment_text', $comment );
				comment_text();
				?>
			</div>
			<?php do_action( 'woocommerce_review_after_comment_text', $comment ); ?>
		<?php endif; ?>
	</div>
