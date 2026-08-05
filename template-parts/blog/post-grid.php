<?php
/**
 * Shared post grid + sidebar — used identically by index.php (blog/posts
 * page), archive.php (category/tag/date/author archives) and search.php,
 * since all three render the exact same real loop of post cards.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings         = noorifa_settings();
$sidebar_enabled  = $settings['blog_sidebar_enabled'];
$content_col_class = $sidebar_enabled ? 'col-lg-8' : 'col-lg-12';
?>
<section class="section-blog flat-spacing">
	<div class="container">
		<div class="row">
			<div class="<?php echo esc_attr( $content_col_class ); ?>">
				<?php if ( have_posts() ) : ?>

					<div class="grid-layout sm-col-<?php echo esc_attr( $settings['blog_grid_columns'] ); ?>">
						<?php
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/content/content' );
						endwhile;
						?>

						<div class="wd-full">
							<?php the_posts_pagination( array( 'class' => 'page-pagination' ) ); ?>
						</div>
					</div>

				<?php else : ?>
					<?php get_template_part( 'template-parts/content/content-none' ); ?>
				<?php endif; ?>
			</div>

			<?php if ( $sidebar_enabled ) : ?>
				<div class="col-lg-4 d-none d-lg-block">
					<?php get_sidebar(); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
