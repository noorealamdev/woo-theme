<?php
/**
 * NavMenus component.
 *
 * @package Ecombon
 */

namespace Ecombon\Setup;

/**
 * Registers the theme's navigation menu locations.
 */
class NavMenus implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'after_setup_theme', array( $this, 'register_menus' ) );
	}

	/**
	 * Registers the available nav menu locations.
	 */
	public function register_menus(): void {
		register_nav_menus(
			array(
				'primary'        => __( 'Primary Menu', 'ecombon' ),
				'footer_company' => __( 'Footer — Company', 'ecombon' ),
				'footer_customer' => __( 'Footer — Customer Care', 'ecombon' ),
			)
		);
	}
}
