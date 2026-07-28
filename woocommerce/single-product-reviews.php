<?php
/**
 * Single product reviews: real rating summary/breakdown, real WP comment
 * list (including real threaded replies), and the real WP/WC review form —
 * all restyled with the theme's own `.product-desc_review` markup instead
 * of WooCommerce's default review template.
 *
 * @package Ecombon
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

$review_count  = $product->get_review_count();
$average       = $product->get_average_rating();
$rating_counts = $product->get_rating_counts();
?>
<?php
/*
 * No `id="reviews"` here (unlike WooCommerce's own default template) —
 * template-parts/product/tabs.php's `.tab-pane` wrapper around this whole
 * template already carries that ID, and a duplicate would make hash
 * navigation (`#reviews`, and `#comment-N` after a real review submit)
 * unreliable, since `document.getElementById()` picks whichever comes first.
 */
?>
<div class="woocommerce-Reviews">
	<div class="product-desc_review write-cancel-review-wrap">
		<div class="box-rating mb-0">
			<div class="rating-ratio">
				<p class="text-display fw-medium"><?php echo esc_html( number_format( (float) $average, 1 ) ); ?></p>
				<div class="star-wrap normal d-flex align-items-center">
					<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
						<?php \Ecombon\Setup\Icons::render( $star <= round( $average ) ? 'Star' : 'Star-thin', 'fs-24' ); ?>
					<?php endfor; ?>
				</div>
				<p class="rate-number">
					<?php
					printf(
						/* translators: %d: number of ratings. */
						esc_html( _n( '(%d Rating)', '(%d Ratings)', $review_count, 'ecombon' ) ),
						(int) $review_count
					);
					?>
				</p>
			</div>

			<?php if ( $review_count > 0 ) : ?>
				<div class="rating-progress-list">
					<?php for ( $star = 5; $star >= 1; $star-- ) : ?>
						<?php
						$count   = isset( $rating_counts[ $star ] ) ? (int) $rating_counts[ $star ] : 0;
						$percent = $review_count ? round( ( $count / $review_count ) * 100 ) : 0;
						?>
						<div class="rate-progress-star fw-medium">
							<span class="number-star"><?php echo (int) $star; ?></span>
							<?php \Ecombon\Setup\Icons::render( 'Star', 'fs-20 cl-text-yellow' ); ?>
							<div class="progress" role="progressbar" aria-label="<?php echo esc_attr( $star ); ?> star ratings" aria-valuenow="<?php echo esc_attr( $percent ); ?>" aria-valuemin="0" aria-valuemax="100">
								<div class="progress-bar" style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
							</div>
							<span class="number-percent"><?php echo esc_html( $percent ); ?>%</span>
						</div>
					<?php endfor; ?>
				</div>
			<?php endif; ?>

			<div>
				<button type="button" class="action btn-comment-review btn-cancel-review tf-btn animate-btn">
					<?php esc_html_e( 'Cancel Review', 'ecombon' ); ?>
				</button>
				<button type="button" class="action btn-comment-review btn-write-review tf-btn animate-btn">
					<?php esc_html_e( 'Write a review', 'ecombon' ); ?>
				</button>
			</div>
		</div>

		<div class="box-comment cancel-review-wrap">
			<div class="head">
				<h4>
					<?php
					printf(
						/* translators: %d: number of reviews. */
						esc_html( _n( '%d Review', '%d Reviews', $review_count, 'ecombon' ) ),
						(int) $review_count
					);
					?>
				</h4>
			</div>

			<div class="wg-comment">
				<?php if ( have_comments() ) : ?>
					<ol class="comment-list">
						<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
					</ol>

					<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
						<nav class="woocommerce-pagination">
							<?php
							paginate_comments_links(
								apply_filters(
									'woocommerce_comment_pagination_args',
									array(
										'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
										'next_text' => is_rtl() ? '&larr;' : '&rarr;',
										'type'      => 'list',
									)
								)
							);
							?>
						</nav>
					<?php endif; ?>
				<?php else : ?>
					<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'ecombon' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
			<?php
			$commenter            = wp_get_current_commenter();
			$name_email_required  = (bool) get_option( 'require_name_email', 1 );
			$required_attr        = $name_email_required ? ' required' : '';
			$required_mark        = $name_email_required ? ' <span class="text-primary">*</span>' : '';
			$rating_enabled       = wc_review_ratings_enabled();
			$rating_required_attr = wc_review_ratings_required() ? ' required' : '';

			$rating_field = '';
			if ( $rating_enabled ) {
				$rating_field = '<div class="comment-form-rating">
					<label for="rating" id="comment-form-rating-label" class="screen-reader-text">' . esc_html__( 'Your rating', 'ecombon' ) . '</label>
					<select name="rating" id="rating"' . $rating_required_attr . '>
						<option value="">' . esc_html__( 'Rate&hellip;', 'ecombon' ) . '</option>
						<option value="5">' . esc_html__( 'Perfect', 'ecombon' ) . '</option>
						<option value="4">' . esc_html__( 'Good', 'ecombon' ) . '</option>
						<option value="3">' . esc_html__( 'Average', 'ecombon' ) . '</option>
						<option value="2">' . esc_html__( 'Not that bad', 'ecombon' ) . '</option>
						<option value="1">' . esc_html__( 'Very poor', 'ecombon' ) . '</option>
					</select>
				</div>';
			}

			$cookie_consent_field = '';
			if ( has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) && get_option( 'show_comments_cookies_opt_in' ) ) {
				$cookie_consent_field = '<div class="checkbox-wrap">
					<input class="tf-check" type="checkbox" id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" value="yes">
					<label for="wp-comment-cookies-consent">' . esc_html__( 'Save my name, email, and website in this browser for the next time I comment.', 'ecombon' ) . '</label>
				</div>';
			}

			$comment_field = '<div class="head">
					<h5>' . esc_html__( 'Write a review:', 'ecombon' ) . '</h5>
					' . $rating_field . '
				</div>
				<div class="form-content mb-24">
					<div class="tf-grid-layout md-col-2">
						<div class="tf-grid-layout">
							<fieldset class="tf-field comment-form-author">
								<label for="author" class="tf-lable fw-medium">' . esc_html__( 'Your Name', 'ecombon' ) . $required_mark . '</label>
								<input type="text" id="author" name="author" autocomplete="name" value="' . esc_attr( $commenter['comment_author'] ) . '"' . $required_attr . '>
							</fieldset>
							<fieldset class="tf-field comment-form-email">
								<label for="email" class="tf-lable fw-medium">' . esc_html__( 'Your Email', 'ecombon' ) . $required_mark . '</label>
								<input type="email" id="email" name="email" autocomplete="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '"' . $required_attr . '>
							</fieldset>
						</div>
						<fieldset class="tf-field d-flex flex-column comment-form-comment">
							<label for="comment" class="tf-lable fw-medium">' . esc_html__( 'Review', 'ecombon' ) . ' <span class="text-primary">*</span></label>
							<textarea name="comment" id="comment" cols="45" rows="8" class="h-md-100" required></textarea>
						</fieldset>
					</div>
				</div>';

			// WP core forces its own default cookie-consent field back in
			// whenever `fields['cookies']` isn't already set (see
			// wp-includes/comment-template.php, "Ensure that the passed
			// fields include cookies consent") — passing it here, restyled,
			// is what keeps that from also adding its own unstyled copy.
			$review_fields = array();
			if ( $cookie_consent_field ) {
				$review_fields['cookies'] = $cookie_consent_field;
			}

			comment_form(
				apply_filters(
					'woocommerce_product_review_comment_form_args',
					array(
						'fields'               => $review_fields,
						'comment_field'        => $comment_field,
						'comment_notes_before' => '',
						'logged_in_as'         => '',
						'title_reply'          => '',
						'title_reply_before'   => '',
						'title_reply_after'    => '',
						'class_container'      => 'write-review-wrap box-write-comment',
						'class_form'           => 'form-rating',
						'class_submit'         => 'tf-btn animate-btn',
						'label_submit'         => esc_html__( 'Submit Review', 'ecombon' ),
						// A real <button> instead of WP core's default
						// `<input type="submit" value="...">` — `.tf-btn`
						// (the same class every other real button in this
						// theme uses) is a flex container meant to lay out
						// child content/icons; browsers don't apply that to
						// a submit input's own native button chrome, which
						// left its "Submit Review" value invisible (white
						// text with no background actually being painted).
						'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
					)
				)
			);
			?>
		<?php else : ?>
			<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'ecombon' ); ?></p>
		<?php endif; ?>
	</div>
</div>
