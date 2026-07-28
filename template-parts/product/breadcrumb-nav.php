<?php
/**
 * Single product breadcrumb + prev/next/all navigation.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$first_terms = get_the_terms( $product->get_id(), 'product_cat' );
$first_term  = ( $first_terms && ! is_wp_error( $first_terms ) ) ? reset( $first_terms ) : null;

$previous_product = \Ecombon\WooCommerce\ProductNavigation::get_adjacent( true );
$next_product     = \Ecombon\WooCommerce\ProductNavigation::get_adjacent( false );
?>
<div class="section-page-title-single flat-spacing-3">
	<div class="container">
		<div class="main-page-title">
			<div class="breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-caption-01 cl-text-3 link"><?php esc_html_e( 'Home', 'ecombon' ); ?></a>
				<?php \Ecombon\Setup\Icons::render( 'CaretRightThin', 'cl-text-3' ); ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="text-caption-01 cl-text-3 link"><?php esc_html_e( 'Shop', 'ecombon' ); ?></a>
				<?php if ( $first_term ) : ?>
					<?php \Ecombon\Setup\Icons::render( 'CaretRightThin', 'cl-text-3' ); ?>
					<a href="<?php echo esc_url( get_term_link( $first_term ) ); ?>" class="text-caption-01 cl-text-3 link"><?php echo esc_html( $first_term->name ); ?></a>
				<?php endif; ?>
				<?php \Ecombon\Setup\Icons::render( 'CaretRightThin', 'cl-text-3' ); ?>
				<p class="text-caption-01"><?php echo esc_html( get_the_title( $product->get_id() ) ); ?></p>
			</div>
			<div class="nav-post-list">
				<?php if ( $previous_product ) : ?>
					<a href="<?php echo esc_url( get_permalink( $previous_product->get_id() ) ); ?>" class="link nav-post-item nav-post-prev" aria-label="<?php esc_attr_e( 'Previous product', 'ecombon' ); ?>">
						<?php \Ecombon\Setup\Icons::render( 'CaretLeft' ); ?>
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="link nav-all-post nav-post-link" aria-label="<?php esc_attr_e( 'All products', 'ecombon' ); ?>">
					<?php \Ecombon\Setup\Icons::render( 'SquaresFour' ); ?>
				</a>
				<?php if ( $next_product ) : ?>
					<a href="<?php echo esc_url( get_permalink( $next_product->get_id() ) ); ?>" class="link nav-post-item nav-post-next" aria-label="<?php esc_attr_e( 'Next product', 'ecombon' ); ?>">
						<?php \Ecombon\Setup\Icons::render( 'CaretRightThin' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
