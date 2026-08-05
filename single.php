<?php
/**
 * The template for single blog posts.
 *
 * Full-width, no #primary/.content-area boxed wrapper and no sidebar —
 * content-single.php owns its own complete real layout (breadcrumb-nav +
 * .section-blog-single > .main-blog-single > .container).
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content/content-single' );
endwhile;

get_footer();
