<?php
/**
 * The template for search results.
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
	array(
		/* translators: %s: search query. */
		'title' => sprintf( __( 'Search results for: %s', 'noorifa' ), get_search_query() ),
	)
);
?>

<?php get_template_part( 'template-parts/blog/post-grid' ); ?>

<?php
get_footer();
