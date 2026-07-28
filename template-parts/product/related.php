<?php
/**
 * "You May Also Like" — real related products (same category/tags),
 * falling back to WooCommerce's own relatedness algorithm.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$related_ids = wc_get_related_products( $product->get_id(), 8 );

if ( empty( $related_ids ) ) {
	return;
}
?>
<section class="flat-spacing-25">
	<div class="container">
		<div class="sect-heading type-2 text-center">
			<h3 class="s-title"><?php esc_html_e( 'You May Also Like', 'ecombon' ); ?></h3>
		</div>
		<div class="wrapper-shop tf-grid-layout tf-col-4">
			<?php foreach ( $related_ids as $related_id ) : ?>
				<?php
				$related_product = wc_get_product( $related_id );
				if ( ! $related_product ) {
					continue;
				}
				get_template_part( 'template-parts/product/card-product', null, array( 'product' => $related_product ) );
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
