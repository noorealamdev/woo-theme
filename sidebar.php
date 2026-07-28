<?php
/**
 * The blog sidebar — real content (search, categories, recent posts, tags),
 * see template-parts/blog/sidebar.php. The mobile hide (d-none d-lg-block,
 * matching the reference) lives on the .col-lg-4 wrapper in archive.php/
 * search.php, not here, since this template-part is just the <aside> itself.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<aside id="secondary" class="site-sidebar" aria-label="<?php esc_attr_e( 'Blog sidebar', 'ecombon' ); ?>">
	<?php get_template_part( 'template-parts/blog/sidebar' ); ?>
</aside>
