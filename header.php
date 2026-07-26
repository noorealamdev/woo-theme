<?php
/**
 * The header for the theme.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'ecombon' ); ?></a>

<button id="goTop">
	<span class="border-progress"></span>
	<span class="ic-wrap">
		<span class="icon icon-CaretTopThin"></span>
	</span>
</button>

<div class="preload preload-container" id="preload">
	<div class="preload-logo">
		<div class="spinner"></div>
	</div>
</div>

<main id="wrapper">
	<?php get_template_part( 'template-parts/header/topbar' ); ?>
	<?php get_template_part( 'template-parts/header/site-header' ); ?>
