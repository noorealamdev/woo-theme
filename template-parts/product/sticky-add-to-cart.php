<?php
/**
 * Sticky bottom add-to-cart bar, shown once the real buy-box button has
 * scrolled out of view (assets/js/main.js's scrollBottomSticky(), already
 * shipped and initialized — it toggles `.show` based on
 * `.section-product-single .btn-action-price`'s position).
 *
 * No duplicate add-to-cart logic lives here. The button is a real
 * `<button type="submit" form="...">` natively associated with the real
 * `form.cart` rendered in template-parts/product/summary.php — clicking it
 * submits that real form (including its real hidden `add-to-cart`/
 * `variation_id`/quantity fields, whatever the shopper already set in the
 * real buy-box above) exactly as if the real button had been clicked, so
 * Noorifa\WooCommerce\BuyItNow/CartFragments and the real AJAX handler in
 * assets/js/noorifa-cart.js all keep working unmodified. For a variable
 * product, the price and variant summary text are mirrored live from the
 * real variation-matching events already fired by
 * assets/js/noorifa-product-variations.js (see
 * assets/js/noorifa-sticky-add-to-cart.js).
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product instanceof WC_Product || ! $product->is_type( array( 'simple', 'variable' ) ) ) {
	return;
}

if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
	return;
}
?>
<div class="sticky-btn-atc">
	<div class="container">
		<div class="height-observer">
			<div class="sticky-atc-product">
				<div class="prd_img">
					<?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?>
				</div>
				<div class="prd_info">
					<p class="name__prd fw-medium lh-24"><?php echo esc_html( $product->get_name() ); ?></p>
					<p class="distribute__prd text-caption-01 cl-text-3 sticky-atc-variant-desc"></p>
				</div>
			</div>
			<div class="sticky-atc-buy">
				<p class="price__prd fw-semibold sticky-atc-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
				<button
					type="submit"
					form="noorifa-add-to-cart-form"
					class="btn-add-to-cart single_add_to_cart_button"
				>
					<?php \Noorifa\Setup\Icons::render( 'Handbag' ); ?>
					<span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
				</button>
			</div>
		</div>
	</div>
</div>
