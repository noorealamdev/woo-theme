<?php
/**
 * The template for the blog index (Posts page) — and, per WP's own
 * template-fallback order, anything not matched by a more specific
 * template (archive.php covers category/tag/date/author archives,
 * search.php covers search results).
 *
 * Real page-title banner + grid, same real markup archive.php already
 * uses, so /blog/ matches every other archive instead of the bare
 * WP-starter-theme markup (`.content-area`/`.archive-header`/`.entry-list`)
 * this file used before — those classes had no real styling of their own.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( is_home() && ! is_front_page() ) {
	get_template_part(
		'template-parts/global/page-title',
		null,
		array( 'title' => wp_strip_all_tags( single_post_title( '', false ) ) )
	);
}
?>

<?php get_template_part( 'template-parts/blog/post-grid' ); ?>

<?php
get_footer();
