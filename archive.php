<?php
/**
 * The template for category, tag, author and date archives.
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

				<header class="archive-header">
					<h1 class="archive-header__title"><?php the_archive_title(); ?></h1>
					<?php the_archive_description( '<div class="archive-header__description">', '</div>' ); ?>
				</header>

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
