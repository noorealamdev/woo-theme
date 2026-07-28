<?php
/**
 * The template for single blog posts.
 *
 * Full-width, no #primary/.content-area boxed wrapper and no sidebar —
 * content-single.php owns its own complete real layout (breadcrumb-nav +
 * .section-blog-single > .main-blog-single > .container), matching how
 * woocommerce/content-single-product.php already does the same for
 * products (see template-parts/product/breadcrumb-nav.php).
 *
 * @package Ecombon
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
