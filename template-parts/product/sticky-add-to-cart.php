<?php
/**
 * Sticky bottom add-to-cart bar, shown once the real buy-box button has
 * scrolled out of view (assets/js/main.js's scrollBottomSticky(), already
 * shipped and initialized — it toggles `.show` based on
 * `.section-product-single .btn-action-price`'s position).
 *
 * No duplicate add-to-cart logic lives here. The quantity input is kept in
 * two-way sync with the real form's own `.qty` input (see
 * assets/js/ecombon-sticky-add-to-cart.js), and the button is a real
 * `<button type="submit" form="...">` natively associated with the real
 * `form.cart` rendered in template-parts/product/summary.php — clicking it
 * submits that real form (including its real hidden `add-to-cart`/
 * `variation_id` fields) exactly as if the real button had been clicked,
 * so Ecombon\WooCommerce\BuyItNow/CartFragments and the real AJAX handler
 * in assets/js/ecombon-cart.js all keep working unmodified. For a variable
 * product, the price and variant summary text are mirrored live from the
 * real variation-matching events already fired by
 * assets/js/ecombon-product-variations.js.
 *
 * @package Ecombon
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
<div class="tf-sticky-btn-atc">
	<div class="container">
		<div class="tf-height-observer w-100 d-flex align-items-center">
			<div class="tf-sticky-atc-product d-flex align-items-center">
				<div class="atc-product-side">
					<div class="prd_img">
						<?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?>
					</div>
					<div class="prd_info d-none d-lg-grid">
						<p class="name__prd fw-medium lh-24"><?php echo esc_html( $product->get_name() ); ?></p>
						<p class="distribute__prd text-caption-01 cl-text-3 tf-sticky-atc-variant-desc"></p>
						<p class="price__prd fw-semibold tf-sticky-atc-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
					</div>
				</div>
			</div>
			<div class="tf-sticky-atc-infos">
				<form class="tf-sticky-atc-form">
					<div class="tf-product-info-quantity">
						<p class="title"><?php esc_html_e( 'Quantity:', 'ecombon' ); ?></p>
						<div class="wg-quantity style-2 quantity">
							<button type="button" class="btn-quantity btn-decrease" aria-label="<?php esc_attr_e( 'Decrease quantity', 'ecombon' ); ?>">
								<i class="icon icon-minus"></i>
							</button>
							<input class="quantity-product qty" type="number" name="quantity" min="<?php echo esc_attr( (string) $product->get_min_purchase_quantity() ); ?>" value="<?php echo esc_attr( (string) $product->get_min_purchase_quantity() ); ?>">
							<button type="button" class="btn-quantity btn-increase" aria-label="<?php esc_attr_e( 'Increase quantity', 'ecombon' ); ?>">
								<i class="icon icon-plus"></i>
							</button>
						</div>
					</div>
					<button
						type="submit"
						form="ecombon-add-to-cart-form"
						class="tf-btn animate-btn btn-add-to-cart single_add_to_cart_button"
					>
						<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
