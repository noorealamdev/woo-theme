<?php
/**
 * Shared page-title banner (breadcrumb + heading + optional subtitle) used
 * by every page type — static pages, single posts, archives, search, 404,
 * shop, cart, checkout, my account.
 *
 * Args (read explicitly, not extracted — see get_template_part_args_quirk
 * memory: this WP version does not extract() get_template_part()'s $args):
 *   'title'       string        Required. The page heading.
 *   'subtitle'    string        Optional. Shown under the heading.
 *   'breadcrumbs' array         Optional. Extra crumbs between Home and the
 *                                current title, each ['label' => '', 'url' => ''].
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title       = $args['title'] ?? '';
$subtitle    = $args['subtitle'] ?? '';
$breadcrumbs = $args['breadcrumbs'] ?? array();

if ( '' === $title ) {
	return;
}

$settings            = \Noorifa\Settings\Layout::all()['page_header'] ?? array();
$breadcrumbs_enabled = $settings['breadcrumbs_enabled'] ?? true;
$align_class         = 'left' === ( $settings['alignment'] ?? \Noorifa\Settings\Layout::PAGE_HEADER_ALIGNMENT_DEFAULT ) ? 'text-start' : 'text-center';
?>
<section class="section-page-title <?php echo esc_attr( $align_class ); ?> flat-spacing-3">
	<div class="container">
		<div class="main-page-title">
			<?php if ( $breadcrumbs_enabled ) : ?>
			<div class="breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-caption-01 cl-text-3 link"><?php esc_html_e( 'Home', 'noorifa' ); ?></a>
				<?php foreach ( $breadcrumbs as $crumb ) : ?>
					<?php \Noorifa\Setup\Icons::render( 'CaretRightThin', 'cl-text-3' ); ?>
					<a href="<?php echo esc_url( $crumb['url'] ); ?>" class="text-caption-01 cl-text-3 link"><?php echo esc_html( $crumb['label'] ); ?></a>
				<?php endforeach; ?>
				<?php \Noorifa\Setup\Icons::render( 'CaretRightThin', 'cl-text-3' ); ?>
				<p class="text-caption-01"><?php echo esc_html( $title ); ?></p>
			</div>
			<?php endif; ?>
			<?php
			/*
			 * Real <h1> — this banner is the main heading on every page
			 * type that uses it (shop, archives, cart, checkout, account,
			 * static pages, search, 404); single posts/products own their
			 * own separate <h1> instead (see single.php/content-single.php,
			 * content-single-product.php) and never render this template
			 * part, so there's no risk of two <h1>s on the same page.
			 * `.h3` keeps the existing visual size — see main.css.
			 */
			?>
			<h1 class="h3"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $subtitle ) : ?>
				<p class="text-body-1 cl-text-2"><?php echo wp_kses_post( $subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
