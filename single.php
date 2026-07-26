<?php
/**
 * The template for single blog posts.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div id="primary" class="site-main">
	<div class="content-area content-area--single">
		<div class="content-area__main">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/content-single' );

				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			endwhile;
			?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</div>

<?php
get_footer();
