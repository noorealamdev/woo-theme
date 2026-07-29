<?php
/**
 * The comments template — real comment count, real threaded `.box-comment`
 * list (see Ecombon\Blog\CommentTemplate), real comment form restyled to
 * the theme's own `.wg-leave-comment` markup. Same real approach as
 * woocommerce/single-product-reviews.php uses for product reviews: the
 * whole field set (author/email/comment/cookie-consent) is built as one
 * combined `comment_field` string, and `fields` is emptied out — passing a
 * `fields` array to comment_form() *replaces* WP's own default array
 * wholesale rather than merging into it, which is what suppresses the
 * default (unstyled, duplicate) author/email/url fields.
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
<?php if ( have_comments() ) : ?>
	<div class="wg-comment">
		<h4 class="title">
			<?php
			$comment_count = get_comments_number();
			printf(
				/* translators: %s: number of comments. */
				esc_html( _n( '%s Comment', '%s Comments', $comment_count, 'ecombon' ) ),
				esc_html( number_format_i18n( $comment_count ) )
			);
			?>
		</h4>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'    => 'ol',
					'callback' => array( '\Ecombon\Blog\CommentTemplate', 'render' ),
				)
			);
			?>
		</ol>

		<?php the_comments_pagination(); ?>
	</div>
<?php endif; ?>

<?php if ( ! comments_open() && get_comments_number() ) : ?>
	<p class="comments-area__closed"><?php esc_html_e( 'Comments are closed.', 'ecombon' ); ?></p>
<?php endif; ?>

<?php if ( comments_open() ) : ?>
	<?php
	$commenter            = wp_get_current_commenter();
	$name_email_required  = (bool) get_option( 'require_name_email', 1 );
	$required_attr        = $name_email_required ? ' required' : '';
	$required_mark        = $name_email_required ? ' <span class="text-primary">*</span>' : '';

	$cookie_consent_field = '';
	if ( has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) && get_option( 'show_comments_cookies_opt_in' ) ) {
		$cookie_consent_field = '<div class="checkbox-wrap">
			<input class="check" type="checkbox" id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" value="yes">
			<label for="wp-comment-cookies-consent">' . esc_html__( 'Save my name, email, and website in this browser for the next time I comment.', 'ecombon' ) . '</label>
		</div>';
	}

	$comment_field = '<div class="form-content">
			<div class="grid-layout sm-col-2">
				<fieldset class="field">
					<label for="author" class="lable fw-medium">' . esc_html__( 'Your Name', 'ecombon' ) . $required_mark . '</label>
					<input id="author" name="author" type="text" placeholder="' . esc_attr__( 'Your Name', 'ecombon' ) . '" value="' . esc_attr( $commenter['comment_author'] ) . '"' . $required_attr . '>
				</fieldset>
				<fieldset class="field">
					<label for="email" class="lable fw-medium">' . esc_html__( 'Your Email', 'ecombon' ) . $required_mark . '</label>
					<input id="email" name="email" type="email" placeholder="' . esc_attr__( 'Your email (private)', 'ecombon' ) . '" value="' . esc_attr( $commenter['comment_author_email'] ) . '"' . $required_attr . '>
				</fieldset>
			</div>
			<fieldset class="field">
				<label for="comment" class="lable fw-medium">' . esc_html__( 'Your Message', 'ecombon' ) . ' <span class="text-primary">*</span></label>
				<textarea id="comment" name="comment" placeholder="' . esc_attr__( 'Write your comment', 'ecombon' ) . '" cols="45" rows="8" required></textarea>
			</fieldset>
		</div>';

	// WP core forces its own default (unstyled) cookie-consent field back in
	// whenever `fields['cookies']` isn't already set — see the "Ensure that
	// the passed fields include cookies consent" block in
	// wp-includes/comment-template.php. Passing it here restyled, same as
	// woocommerce/single-product-reviews.php already does for reviews, is
	// what keeps that from also injecting a second, duplicate checkbox.
	$comment_fields = array();
	if ( $cookie_consent_field ) {
		$comment_fields['cookies'] = $cookie_consent_field;
	}

	comment_form(
		array(
			'title_reply'          => __( 'Leave A Comment', 'ecombon' ),
			'title_reply_before'   => '<h4 class="title">',
			'title_reply_after'    => '</h4>',
			'comment_notes_before' => '',
			'logged_in_as'         => '',
			'fields'               => $comment_fields,
			'comment_field'        => $comment_field,
			'class_container'      => 'wg-leave-comment',
			'class_form'           => 'form-leave-comment',
			'id_form'              => 'commentform',
			'label_submit'         => __( 'Post Comment', 'ecombon' ),
			'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s"><span class="btn-text">%4$s</span></button>',
			'class_submit'         => 'btn animate-btn',
		)
	);
	?>
<?php endif; ?>
