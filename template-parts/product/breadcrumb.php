<?php
/**
 * Single product breadcrumb trail.
 *
 * Single products own their own <h1> (template-parts/product/summary.php)
 * and so don't render the shared page-title banner — this renders just the
 * breadcrumb trail (Home > Shop > Category > Product) above the product,
 * reusing the exact `.breadcrumbs` markup/icon styling as
 * template-parts/global/page-title.php.
 *
 * Gated by the same Page Header "breadcrumbs" setting used everywhere else,
 * and skipped on distraction-free products that hide the site header.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$noorifa_settings = \Noorifa\Settings\Layout::all()['page_header'] ?? array();
if ( empty( $noorifa_settings['breadcrumbs_enabled'] ?? true ) ) {
	return;
}

if ( \Noorifa\WooCommerce\ProductPageLayout::should_hide_header() ) {
	return;
}

global $product;
$noorifa_product = ( $product instanceof \WC_Product ) ? $product : wc_get_product( get_the_ID() );
if ( ! $noorifa_product ) {
	return;
}

$noorifa_crumbs = array();

// Shop page.
$noorifa_shop_id = wc_get_page_id( 'shop' );
if ( $noorifa_shop_id > 0 ) {
	$noorifa_shop_url = get_permalink( $noorifa_shop_id );
	if ( $noorifa_shop_url ) {
		$noorifa_crumbs[] = array(
			'label' => get_the_title( $noorifa_shop_id ),
			'url'   => $noorifa_shop_url,
		);
	}
}

// Primary product category, with its ancestors listed top-down before it.
$noorifa_terms = get_the_terms( $noorifa_product->get_id(), 'product_cat' );
if ( $noorifa_terms && ! is_wp_error( $noorifa_terms ) ) {
	$noorifa_term      = $noorifa_terms[0];
	$noorifa_ancestors = array_reverse( get_ancestors( $noorifa_term->term_id, 'product_cat' ) );

	foreach ( $noorifa_ancestors as $noorifa_anc_id ) {
		$noorifa_anc = get_term( $noorifa_anc_id, 'product_cat' );
		if ( $noorifa_anc && ! is_wp_error( $noorifa_anc ) ) {
			$noorifa_anc_link = get_term_link( $noorifa_anc );
			if ( ! is_wp_error( $noorifa_anc_link ) ) {
				$noorifa_crumbs[] = array(
					'label' => $noorifa_anc->name,
					'url'   => $noorifa_anc_link,
				);
			}
		}
	}

	$noorifa_term_link = get_term_link( $noorifa_term );
	if ( ! is_wp_error( $noorifa_term_link ) ) {
		$noorifa_crumbs[] = array(
			'label' => $noorifa_term->name,
			'url'   => $noorifa_term_link,
		);
	}
}
?>
<div class="section-product-breadcrumb">
	<div class="container">
		<div class="breadcrumbs">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-caption-01 cl-text-3 link"><?php esc_html_e( 'Home', 'noorifa' ); ?></a>
			<?php foreach ( $noorifa_crumbs as $noorifa_crumb ) : ?>
				<?php \Noorifa\Setup\Icons::render( 'CaretRightThin', 'cl-text-3' ); ?>
				<a href="<?php echo esc_url( $noorifa_crumb['url'] ); ?>" class="text-caption-01 cl-text-3 link"><?php echo esc_html( $noorifa_crumb['label'] ); ?></a>
			<?php endforeach; ?>
			<?php \Noorifa\Setup\Icons::render( 'CaretRightThin', 'cl-text-3' ); ?>
			<p class="text-caption-01"><?php echo esc_html( $noorifa_product->get_name() ); ?></p>
		</div>
	</div>
</div>
