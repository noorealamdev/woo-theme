<?php
/**
 * The default fallback template: blog index and any query not matched by a
 * more specific template.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div id="primary" class="site-main">
	<div class="content-area">
		<div class="content-area__main">
			<?php if ( have_posts() ) : ?>

				<?php if ( is_home() && ! is_front_page() ) : ?>
					<header class="archive-header">
						<h1 class="archive-header__title"><?php single_post_title(); ?></h1>
					</header>
				<?php endif; ?>

				<div class="entry-list">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/content' );
					endwhile;
					?>
				</div>

				<?php the_posts_pagination(); ?>

			<?php else : ?>
				<?php get_template_part( 'template-parts/content/content-none' ); ?>
			<?php endif; ?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</div>

<?php
get_footer();
