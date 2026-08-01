<?php
/**
 * Header builder element: site logo.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-site">
	<?php if ( has_custom_logo() ) : ?>
		<?php the_custom_logo(); ?>
	<?php else : ?>
		<?php bloginfo( 'name' ); ?>
	<?php endif; ?>
</a>
