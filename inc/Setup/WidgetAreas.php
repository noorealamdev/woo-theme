<?php
/**
 * WidgetAreas component.
 *
 * @package Ecombon
 */

namespace Ecombon\Setup;

/**
 * Registers the theme's sidebar and footer widget areas.
 */
class WidgetAreas implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
	}

	/**
	 * Registers all widget areas.
	 */
	public function register_sidebars(): void {
		register_sidebar(
			array(
				'name'          => __( 'Blog Sidebar', 'ecombon' ),
				'id'            => 'sidebar-blog',
				'description'   => __( 'Displayed alongside blog posts and archives.', 'ecombon' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);
	}
}
