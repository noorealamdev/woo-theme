<?php
/**
 * The header for the theme.
 *
 * @package Noorifa
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

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'noorifa' ); ?></a>

<div class="preload preload-container" id="preload">
	<div class="preload-logo">
		<div class="spinner"></div>
	</div>
</div>

<main id="wrapper">
	<?php
	// A product can opt out of the header for a distraction-free landing
	// page (Product edit screen > Page Layout).
	if ( ! ( class_exists( '\Noorifa\WooCommerce\ProductPageLayout' ) && \Noorifa\WooCommerce\ProductPageLayout::should_hide_header() ) ) :
		get_template_part( 'template-parts/header/topbar' );
		get_template_part( 'template-parts/header/site-header' );
	endif;
	?>
