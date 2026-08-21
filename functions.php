<?php
/**
 * Noorifa theme bootstrap.
 *
 * This file wires up the autoloader and boots the theme's setup
 * components. It must never contain business logic — that lives in the
 * Noorifa Core plugin. This file only prepares the presentation layer.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NOORIFA_VERSION', '1.0.0' );
define( 'NOORIFA_MIN_PHP', '8.0' );
define( 'NOORIFA_THEME_DIR', get_template_directory() );
define( 'NOORIFA_THEME_URI', get_template_directory_uri() );

// Slug of the theme's top-level "Noorifa" admin menu. Defined this early
// (theme load) so both the Noorifa Core plugin's admin_menu and its Product
// Layouts CPT (registered on `init`) can detect an active Noorifa theme and
// attach their own submenus under this one shared menu.
define( 'NOORIFA_ADMIN_MENU_SLUG', 'noorifa-settings' );

/**
 * Freemius SDK integration (licensing, updates, checkout).
 *
 * !! REPLACE `id` and `public_key` below with the real values from your
 * Freemius dashboard: Products → Noorifa → SDK (or "Integration") tab —
 * these two are unique to your product and can't be guessed/reused.
 * Everything else here follows Freemius's own documented starter snippet
 * (see freemius/README.md). Initialized before the PHP-version gate below
 * so Freemius can still show its own requirements/update UI even on an
 * incompatible PHP version.
 *
 * The SDK throws a hard fatal error if `id` isn't a real numeric product
 * ID (confirmed locally: it doesn't degrade gracefully, it crashes the
 * whole site) — so this stays inert, with an admin-only reminder notice
 * instead of a broken storefront, until the two placeholders below are
 * actually replaced.
 */
define( 'NOORIFA_FS_PRODUCT_ID', '37602' );
define( 'NOORIFA_FS_PUBLIC_KEY', 'pk_d2673537d292d0b85dbad80902deb' );

if ( ! function_exists( 'noorifa_fs' ) ) {
	function noorifa_fs() {
		global $noorifa_fs;

		if ( ! isset( $noorifa_fs ) ) {
			require_once NOORIFA_THEME_DIR . '/freemius/start.php';

			$noorifa_fs = fs_dynamic_init( array(
				'id'                  => NOORIFA_FS_PRODUCT_ID,
				'slug'                => 'noorifa',
				'type'                => 'theme',
				'public_key'          => NOORIFA_FS_PUBLIC_KEY,
				'is_premium'          => true,
				'has_premium_version' => true,
				'has_paid_plans'      => true,
				'menu'                => array(
					'slug' => NOORIFA_ADMIN_MENU_SLUG,
				),
			) );
		}

		return $noorifa_fs;
	}

	if ( 'REPLACE_WITH_YOUR_PRODUCT_ID' !== NOORIFA_FS_PRODUCT_ID && 'REPLACE_WITH_YOUR_PUBLIC_KEY' !== NOORIFA_FS_PUBLIC_KEY ) {
		noorifa_fs();
		do_action( 'noorifa_fs_loaded' );
	} elseif ( is_admin() ) {
		add_action( 'admin_notices', function () {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			?>
			<div class="notice notice-warning">
				<p>
					<?php esc_html_e( 'Freemius isn\'t active yet: the theme\'s functions.php still has placeholder values for NOORIFA_FS_PRODUCT_ID and NOORIFA_FS_PUBLIC_KEY. Replace them with the real values from your Freemius dashboard (Products → Noorifa → SDK) to enable licensing, updates and checkout.', 'noorifa' ); ?>
				</p>
			</div>
			<?php
		} );
	}
}

if ( version_compare( PHP_VERSION, NOORIFA_MIN_PHP, '<' ) ) {
	require NOORIFA_THEME_DIR . '/inc/back-compat.php';
	return;
}

require NOORIFA_THEME_DIR . '/inc/Setup/ComponentInterface.php';
require NOORIFA_THEME_DIR . '/inc/Setup/Theme.php';
require NOORIFA_THEME_DIR . '/inc/Settings/helpers.php';

Noorifa\Setup\Theme::instance()->initialize();
