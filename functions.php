<?php
/**
 * Ecombon theme bootstrap.
 *
 * This file wires up the autoloader and boots the theme's setup
 * components. It must never contain business logic — that lives in the
 * Ecombon Core plugin. This file only prepares the presentation layer.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ECOMBON_VERSION', '0.1.1' );
define( 'ECOMBON_MIN_PHP', '8.0' );
define( 'ECOMBON_THEME_DIR', get_template_directory() );
define( 'ECOMBON_THEME_URI', get_template_directory_uri() );

if ( version_compare( PHP_VERSION, ECOMBON_MIN_PHP, '<' ) ) {
	require ECOMBON_THEME_DIR . '/inc/back-compat.php';
	return;
}

require ECOMBON_THEME_DIR . '/inc/Setup/ComponentInterface.php';
require ECOMBON_THEME_DIR . '/inc/Setup/Theme.php';
require ECOMBON_THEME_DIR . '/inc/Settings/helpers.php';

Ecombon\Setup\Theme::instance()->initialize();
