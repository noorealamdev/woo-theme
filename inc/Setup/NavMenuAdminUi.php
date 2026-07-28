<?php
/**
 * NavMenuAdminUi component.
 *
 * @package Ecombon
 */

namespace Ecombon\Setup;

/**
 * Hides the "Menu Order" numeric-position dropdown in the real WordPress
 * core Appearance > Menus screen (wp-admin/includes/class-walker-nav-menu-edit.php,
 * `.field-move-combo .description-wide`, "Menu Parent" then "Menu Order").
 *
 * That field duplicates drag-and-drop reordering, which is how menu items
 * are actually positioned here — this only hides the redundant dropdown,
 * it doesn't touch the real "Menu Parent" field next to it or change how
 * menu order is stored/read.
 */
class NavMenuAdminUi implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'admin_head-nav-menus.php', array( $this, 'hide_menu_order_field' ) );
	}

	/**
	 * Prints a small inline stylesheet, only on the nav-menus.php screen.
	 *
	 * `.field-move-combo` always renders "Menu Parent" first and "Menu
	 * Order" second (real, fixed core markup) — :nth-child avoids relying
	 * on `:has()` support for something this simple.
	 */
	public function hide_menu_order_field(): void {
		echo '<style>.field-move-combo > .description-wide:nth-child(2) { display: none; }</style>';
	}
}
