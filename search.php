<?php
/**
 * The template for search results.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part(
	'template-parts/global/page-title',
	null,
	array(
		/* translators: %s: search query. */
		'title' => sprintf( __( 'Search results for: %s', 'ecombon' ), get_search_query() ),
	)
);
?>

<section class="section-blog flat-spacing">
	<div class="container">
		<div class="row">
			<div class="col-lg-8">
				<?php if ( have_posts() ) : ?>

					<div class="grid-layout sm-col-2">
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

			<div class="col-lg-4 d-none d-lg-block">
				<?php get_sidebar(); ?>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
