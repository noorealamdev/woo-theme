<?php
/**
 * Related Posts — real posts sharing the current post's primary category
 * (falls back to the site's most recent other posts if none share a
 * category), rendered through the same real .article-blog card used on the
 * blog index (template-parts/content/content.php) inside a real .mk-swiper
 * (see assets/js/carousel.js, already wired globally off data-* attrs).
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = noorifa_settings();

if ( ! $settings['blog_related_posts_enabled'] ) {
	return;
}

$current_id    = get_the_ID();
$categories    = wp_get_post_categories( $current_id );
$related_count = (int) $settings['blog_related_posts_count'];

$related_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => $related_count,
		'post__not_in'        => array( $current_id ),
		'category__in'        => $categories,
		'ignore_sticky_posts' => true,
	)
);

if ( ! $related_query->have_posts() && ! empty( $categories ) ) {
	$related_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $related_count,
			'post__not_in'        => array( $current_id ),
			'ignore_sticky_posts' => true,
		)
	);
}

if ( ! $related_query->have_posts() ) {
	return;
}
?>
<section class="section-related flat-spacing">
	<div class="container">
		<div class="row">
			<?php // Same col-lg-8 content column content-single.php's article/featured image use, so this section lines up with the post above it instead of spanning the full page width. ?>
			<div class="col-lg-8 mx-auto">
				<div class="sect-heading text-center">
					<h3 class="s-title"><?php echo esc_html( $settings['blog_related_posts_heading'] ); ?></h3>
					<p class="s-desc text-body-1 cl-text-2"><?php echo esc_html( $settings['blog_related_posts_subtitle'] ); ?></p>
				</div>
				<div
					dir="ltr"
					class="swiper mk-swiper"
					data-preview="3"
					data-tablet="2"
					data-mobile-sm="1"
					data-mobile="1"
					data-space-lg="30"
					data-space-md="15"
					data-space="15"
					data-pagination="1"
					data-pagination-sm="1"
					data-pagination-md="2"
					data-pagination-lg="3"
				>
					<div class="swiper-wrapper">
						<?php
						while ( $related_query->have_posts() ) :
							$related_query->the_post();
							?>
							<div class="swiper-slide">
								<?php get_template_part( 'template-parts/content/content' ); ?>
							</div>
							<?php
						endwhile;
						?>
					</div>
					<div class="sw-dot-default sw-pagination"></div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
