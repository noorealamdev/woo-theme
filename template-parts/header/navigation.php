<?php
/**
 * Header builder element: primary navigation menu.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! has_nav_menu( 'primary' ) ) {
	return;
}
?>
<nav class="box-navigation">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'items_wrap'     => '<ul class="box-nav-menu">%3$s</ul>',
			'walker'         => new \Ecombon\Navigation\MegaMenuWalker(),
			'depth'          => 3,
		)
	);
	?>
</nav>
