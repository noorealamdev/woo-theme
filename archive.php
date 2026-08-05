<?php
/**
 * The template for category, tag, author and date archives.
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
		'title'    => wp_strip_all_tags( get_the_archive_title() ),
		'subtitle' => get_the_archive_description(),
	)
);
?>

<?php get_template_part( 'template-parts/blog/post-grid' ); ?>

<?php
get_footer();
