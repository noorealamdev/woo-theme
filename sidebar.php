<?php
/**
 * The blog sidebar.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-blog' ) ) {
	return;
}
?>
<aside id="secondary" class="site-sidebar" aria-label="<?php esc_attr_e( 'Blog sidebar', 'ecombon' ); ?>">
	<?php dynamic_sidebar( 'sidebar-blog' ); ?>
</aside>
