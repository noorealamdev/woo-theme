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
 * @package Ecombon
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
?>
<section class="section-page-title text-center flat-spacing-3">
	<div class="container">
		<div class="main-page-title">
			<div class="breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-caption-01 cl-text-3 link"><?php esc_html_e( 'Home', 'ecombon' ); ?></a>
				<?php foreach ( $breadcrumbs as $crumb ) : ?>
					<i class="icon icon-CaretRightThin cl-text-3"></i>
					<a href="<?php echo esc_url( $crumb['url'] ); ?>" class="text-caption-01 cl-text-3 link"><?php echo esc_html( $crumb['label'] ); ?></a>
				<?php endforeach; ?>
				<i class="icon icon-CaretRightThin cl-text-3"></i>
				<p class="text-caption-01"><?php echo esc_html( $title ); ?></p>
			</div>
			<h3><?php echo esc_html( $title ); ?></h3>
			<?php if ( $subtitle ) : ?>
				<p class="text-body-1 cl-text-2"><?php echo wp_kses_post( $subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
