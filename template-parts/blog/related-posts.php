<?php
/**
 * Related Posts — real posts sharing the current post's primary category
 * (falls back to the site's most recent other posts if none share a
 * category), rendered through the same real .article-blog card used on the
 * blog index (template-parts/content/content.php) inside a real .tf-swiper
 * (see assets/js/carousel.js, already wired globally off data-* attrs).
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_id = get_the_ID();
$categories = wp_get_post_categories( $current_id );

$related_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 6,
		'post__not_in'        => array( $current_id ),
		'category__in'        => $categories,
		'ignore_sticky_posts' => true,
	)
);

if ( ! $related_query->have_posts() && ! empty( $categories ) ) {
	$related_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => 6,
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
		<div class="sect-heading text-center">
			<h3 class="s-title"><?php esc_html_e( 'Related Posts', 'ecombon' ); ?></h3>
			<p class="s-desc text-body-1 cl-text-2"><?php esc_html_e( 'Discover more stories worth reading.', 'ecombon' ); ?></p>
		</div>
		<div
			dir="ltr"
			class="swiper tf-swiper"
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
			<div class="sw-dot-default tf-sw-pagination"></div>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
