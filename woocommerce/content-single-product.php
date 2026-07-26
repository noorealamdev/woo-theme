<?php
/**
 * Single product content override, matching product-detail.html.
 *
 * The gallery and buy-box layout is custom (Amerce markup); the actual
 * add-to-cart form, attributes table, and reviews reuse WooCommerce's own
 * real templates/functions — see template-parts/product/*.php.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

do_action( 'woocommerce_before_single_product' );

get_template_part( 'template-parts/product/breadcrumb-nav' );
?>

<section class="section-product-single tf-main-product section-image-zoom">
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<?php get_template_part( 'template-parts/product/gallery' ); ?>
			</div>
			<div class="col-md-6">
				<?php get_template_part( 'template-parts/product/summary' ); ?>
			</div>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/product/tabs' );
get_template_part( 'template-parts/product/related' );

do_action( 'woocommerce_after_single_product' );
