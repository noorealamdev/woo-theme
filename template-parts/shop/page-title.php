<?php
/**
 * Shop / product archive page-title banner with breadcrumb.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_product_taxonomy() ) {
	$term        = get_queried_object();
	$title       = $term->name;
	$description = $term->description;
} else {
	$title       = wc_get_page_permalink( 'shop' ) ? get_the_title( wc_get_page_id( 'shop' ) ) : __( 'Shop', 'ecombon' );
	$description = '';
}
?>
<section class="section-page-title text-center flat-spacing-2 pb-0">
	<div class="container">
		<div class="main-page-title">
			<div class="breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-caption-01 cl-text-3 link"><?php esc_html_e( 'Home', 'ecombon' ); ?></a>
				<i class="icon icon-CaretRightThin cl-text-3"></i>
				<p class="text-caption-01"><?php echo esc_html( $title ); ?></p>
			</div>
			<h3><?php echo esc_html( $title ); ?></h3>
			<?php if ( $description ) : ?>
				<p class="text-body-1 cl-text-2"><?php echo wp_kses_post( $description ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
