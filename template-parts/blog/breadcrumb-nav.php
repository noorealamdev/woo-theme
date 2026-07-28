<?php
/**
 * Single post breadcrumb + prev/next/all navigation — same real pattern as
 * template-parts/product/breadcrumb-nav.php, using WP core's own real
 * get_previous_post()/get_next_post() (real adjacent-post lookup, not a
 * custom query) for the prev/next links.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$first_categories = get_the_category();
$first_category    = ! empty( $first_categories ) ? $first_categories[0] : null;

// No dedicated "Blog" archive page exists on this site (no
// woocommerce-style shop equivalent for posts) — the post's own category
// archive is the closest real "view all" target.
$all_posts_url = $first_category ? get_category_link( $first_category ) : home_url( '/' );

$previous_post = get_previous_post();
$next_post     = get_next_post();
?>
<div class="section-page-title-single flat-spacing-3">
	<div class="container">
		<div class="main-page-title">
			<div class="breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-caption-01 cl-text-3 link"><?php esc_html_e( 'Home', 'ecombon' ); ?></a>
				<?php if ( $first_category ) : ?>
					<?php \Ecombon\Setup\Icons::render( 'CaretRightThin', 'cl-text-3' ); ?>
					<a href="<?php echo esc_url( get_category_link( $first_category ) ); ?>" class="text-caption-01 cl-text-3 link"><?php echo esc_html( $first_category->name ); ?></a>
				<?php endif; ?>
				<?php \Ecombon\Setup\Icons::render( 'CaretRightThin', 'cl-text-3' ); ?>
				<p class="text-caption-01"><?php the_title(); ?></p>
			</div>
			<div class="nav-post-list">
				<?php if ( $previous_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $previous_post ) ); ?>" class="link nav-post-item nav-post-prev" aria-label="<?php esc_attr_e( 'Previous post', 'ecombon' ); ?>">
						<?php \Ecombon\Setup\Icons::render( 'CaretLeft' ); ?>
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $all_posts_url ); ?>" class="link nav-all-post nav-post-link" aria-label="<?php esc_attr_e( 'All posts', 'ecombon' ); ?>">
					<?php \Ecombon\Setup\Icons::render( 'SquaresFour' ); ?>
				</a>
				<?php if ( $next_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>" class="link nav-post-item nav-post-next" aria-label="<?php esc_attr_e( 'Next post', 'ecombon' ); ?>">
						<?php \Ecombon\Setup\Icons::render( 'CaretRightThin' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
