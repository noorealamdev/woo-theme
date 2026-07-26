<?php
/**
 * Single product tabs: Description, Additional Information, Reviews,
 * Shipping & Returns.
 *
 * Description/Additional Information/Reviews reuse WooCommerce's own real
 * template callbacks (content, attributes table, and the real WP comments
 * loop + review form) — only the tab chrome around them is custom.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product, $post;

$tabs = array();

if ( $post->post_content ) {
	$tabs['description'] = __( 'Description', 'ecombon' );
}

if ( $product->has_attributes() ) {
	$tabs['additional_information'] = __( 'Additional Information', 'ecombon' );
}

if ( comments_open() ) {
	/* translators: %d: number of reviews. */
	$tabs['reviews'] = sprintf( __( 'Reviews (%d)', 'ecombon' ), $product->get_review_count() );
}

$shipping_return_note = apply_filters( 'ecombon_product_shipping_returns_content', '' );
if ( $shipping_return_note ) {
	$tabs['shipping-returns'] = __( 'Shipping & Returns', 'ecombon' );
}

if ( empty( $tabs ) ) {
	return;
}

$tab_keys   = array_keys( $tabs );
$active_tab = reset( $tab_keys );
?>
<section class="section-product-description flat-spacing flat-animate-tab">
	<div class="container">
		<ul class="tab-btn-wrap-v1" role="tablist">
			<?php foreach ( $tabs as $key => $label ) : ?>
				<li class="nav-tab-item" role="presentation">
					<a href="#<?php echo esc_attr( $key ); ?>" data-bs-toggle="tab" class="tf-btn-tab<?php echo esc_attr( $key === $active_tab ? ' active' : '' ); ?>" role="tab">
						<span class="h5 fw-medium"><?php echo esc_html( $label ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="tab-content">
			<?php if ( isset( $tabs['description'] ) ) : ?>
				<div class="tab-pane<?php echo esc_attr( 'description' === $active_tab ? ' active show' : '' ); ?>" id="description" role="tabpanel">
					<div class="tab-content_desc">
						<?php the_content(); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( isset( $tabs['additional_information'] ) ) : ?>
				<div class="tab-pane<?php echo esc_attr( 'additional_information' === $active_tab ? ' active show' : '' ); ?>" id="additional_information" role="tabpanel">
					<?php do_action( 'woocommerce_product_additional_information', $product ); ?>
				</div>
			<?php endif; ?>

			<?php if ( isset( $tabs['reviews'] ) ) : ?>
				<div class="tab-pane<?php echo esc_attr( 'reviews' === $active_tab ? ' active show' : '' ); ?>" id="reviews" role="tabpanel">
					<?php comments_template(); ?>
				</div>
			<?php endif; ?>

			<?php if ( isset( $tabs['shipping-returns'] ) ) : ?>
				<div class="tab-pane<?php echo esc_attr( 'shipping-returns' === $active_tab ? ' active show' : '' ); ?>" id="shipping-returns" role="tabpanel">
					<?php echo wp_kses_post( $shipping_return_note ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
