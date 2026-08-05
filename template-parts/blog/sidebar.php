<?php
/**
 * Real blog sidebar — search, categories (with real post counts), recent
 * posts (with real thumbnails), and popular tags. Direct WP function calls
 * rather than a widget area, matching the rest of the theme's pattern of
 * real template-parts over widget abstraction (e.g. template-parts/product/
 * related.php) — every section here reflects the site's actual real content,
 * never placeholder data.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = noorifa_settings();

$categories = $settings['blog_sidebar_categories'] ? get_categories( array( 'hide_empty' => true ) ) : array();

$recent_posts = $settings['blog_sidebar_recent_posts'] ? get_posts(
	array(
		'numberposts'         => (int) $settings['blog_sidebar_recent_posts_count'],
		'post__not_in'        => is_singular( 'post' ) ? array( get_the_ID() ) : array(),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
) : array();

$tags = $settings['blog_sidebar_tags'] ? get_tags(
	array(
		'number'  => (int) $settings['blog_sidebar_tags_count'],
		'orderby' => 'count',
		'order'   => 'DESC',
	)
) : array();
?>
<div class="blog-sidebar sidebar-content-wrap sticky-top">
	<div class="sidebar-item">
		<div class="sb-search">
			<?php get_search_form(); ?>
		</div>
	</div>

	<?php if ( $categories ) : ?>
		<div class="sidebar-item">
			<h5 class="sb-title"><?php esc_html_e( 'Categories', 'noorifa' ); ?></h5>
			<ul class="sb-category">
				<?php foreach ( $categories as $category ) : ?>
					<li>
						<a href="<?php echo esc_url( get_category_link( $category ) ); ?>">
							<?php echo esc_html( $category->name ); ?>
							<span class="count">(<?php echo esc_html( $category->count ); ?>)</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $recent_posts ) : ?>
		<div class="sidebar-item">
			<h5 class="sb-title"><?php esc_html_e( 'Recent Posts', 'noorifa' ); ?></h5>
			<ul class="sb-recent">
				<?php foreach ( $recent_posts as $recent_post ) : ?>
					<li class="recent-item">
						<?php if ( has_post_thumbnail( $recent_post ) ) : ?>
							<a href="<?php echo esc_url( get_permalink( $recent_post ) ); ?>" class="image">
								<?php echo get_the_post_thumbnail( $recent_post, array( 90, 90 ) ); ?>
							</a>
						<?php endif; ?>
						<div class="meta">
							<p class="meta-date text-caption-01 cl-text-2"><?php echo esc_html( get_the_date( 'j F', $recent_post ) ); ?></p>
							<a href="<?php echo esc_url( get_permalink( $recent_post ) ); ?>" class="meta-name link-underline link fw-medium">
								<?php echo esc_html( get_the_title( $recent_post ) ); ?>
							</a>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $tags ) : ?>
		<div class="sidebar-item">
			<h5 class="sb-title"><?php esc_html_e( 'Popular Tag', 'noorifa' ); ?></h5>
			<ul class="sb-tag">
				<?php foreach ( $tags as $tag ) : ?>
					<li>
						<a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" class="text-caption-01"><?php echo esc_html( $tag->name ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>
