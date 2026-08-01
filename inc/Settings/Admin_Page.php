<?php
/**
 * Admin_Page component.
 *
 * @package Ecombon
 */

namespace Ecombon\Settings;

use Ecombon\Setup\ComponentInterface;

/**
 * Real standalone "Ecombon" top-level dashboard menu item (its own icon
 * in the admin sidebar, not nested under Appearance) — a React app (built
 * with WordPress core's own bundled `wp-element`/`wp-components`, no
 * npm/build step) that reads and saves through the real REST endpoint
 * registered by Rest_Controller.
 */
class Admin_Page implements ComponentInterface {

	const PAGE_HOOK = 'toplevel_page_ecombon-settings';

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Registers the real standalone top-level menu item.
	 */
	public function register_page(): void {
		add_menu_page(
			__( 'Ecombon Theme Settings', 'ecombon' ),
			__( 'Ecombon', 'ecombon' ),
			'manage_options',
			'ecombon-settings',
			array( $this, 'render' ),
			'dashicons-layout',
			59
		);
	}

	/**
	 * Only enqueues on the theme settings screen itself.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( self::PAGE_HOOK !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'ecombon-admin-settings', ECOMBON_THEME_URI . '/assets/css/admin-settings.css', array( 'wp-components' ), ECOMBON_VERSION );

		wp_enqueue_media(); // Backs the footer Info Card's avatar picker (ImageField in settings-app.js).

		wp_enqueue_script(
			'ecombon-admin-settings',
			ECOMBON_THEME_URI . '/assets/js/admin/settings-app.js',
			array( 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n', 'media-editor' ),
			ECOMBON_VERSION,
			true
		);

		wp_localize_script(
			'ecombon-admin-settings',
			'ecombonSettingsData',
			array(
				'restUrl'  => esc_url_raw( rest_url( 'ecombon/v1/settings' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'settings'    => Layout::all(),
				'fieldBounds' => Schema::range_bounds(),
				'choices'     => array(
					'social'      => Layout::social_networks(),
					'googleFonts' => Layout::google_fonts(),
					'layout'      => array(
						'siteWidth' => Layout::site_width_choices(),
					),
					'header'      => array(
						'toggleable' => Layout::header_toggleable(),
					),
					'footer'      => array(
						'top'    => Layout::footer_top_elements(),
						'bottom' => Layout::footer_bottom_elements(),
					),
					'newsletter'  => array(
						'provider' => Layout::newsletter_provider_choices(),
					),
					'shop'        => array(
						'gridColumns'     => Layout::grid_columns_choices(),
						'productsPerPage' => Layout::products_per_page_choices(),
						'fontWeight'      => Layout::font_weight_choices(),
					),
				),
				'defaults'    => array(
					'colorPrimary'   => Layout::COLOR_PRIMARY_DEFAULT,
					'colorSecondary' => Layout::COLOR_SECONDARY_DEFAULT,
				),
			)
		);
	}

	/**
	 * Renders the real mount point the React app attaches to.
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div id="ecombon-settings-app" class="ecombon-settings-app"></div>';
	}
}
