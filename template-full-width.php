<?php
/**
 * Template Name: Full Width
 *
 * Selectable from any Page's Page Attributes panel. Same real page-title
 * banner and content/comments as the default page template — the only
 * difference is the content wrapper below has no max-width or sidebar
 * grid, so it stretches to fill the actual browser viewport instead of
 * boxing to the Layout > Container Width setting. Intended for landing
 * pages / custom block layouts that need to go edge-to-edge.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part(
	'template-parts/global/page-title',
	null,
	array( 'title' => get_the_title( get_queried_object_id() ) )
);
?>

<div id="primary" class="site-main">
	<div class="content-area content-area--full-width">
		<div class="content-area__main">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/content-page' );

				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			endwhile;
			?>
		</div>
	</div>
</div>

<?php
get_footer();
