<?php
/**
 * Header builder element: site logo.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php if ( has_custom_logo() ) : ?>
	<?php // the_custom_logo() renders its own complete <a class="custom-logo-link"> wrapper — an outer <a> here would nest anchors, which is invalid HTML and gets silently split by the browser. ?>
	<?php the_custom_logo(); ?>
<?php else : ?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-site">
		<?php bloginfo( 'name' ); ?>
	</a>
<?php endif; ?>
