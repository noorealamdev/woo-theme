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

if ( version_compare( PHP_VERSION, NOORIFA_MIN_PHP, '<' ) ) {
	require NOORIFA_THEME_DIR . '/inc/back-compat.php';
	return;
}

require NOORIFA_THEME_DIR . '/inc/Setup/ComponentInterface.php';
require NOORIFA_THEME_DIR . '/inc/Setup/Theme.php';
require NOORIFA_THEME_DIR . '/inc/Settings/helpers.php';

Noorifa\Setup\Theme::instance()->initialize();
