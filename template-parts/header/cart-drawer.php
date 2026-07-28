<?php
/**
 * Mini cart offcanvas, triggered by the header cart icon.
 *
 * Only rendered when WooCommerce is active. The free-shipping bar reads
 * an actual configured shipping-zone threshold (hidden if none is set).
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
	return;
}

$cart = WC()->cart;

$free_shipping_threshold = \Ecombon\WooCommerce\Shipping::get_free_shipping_threshold();
?>
<div class="offcanvas offcanvas-end popup-shopping-cart" id="shoppingCart">
	<div class="canvas-wrapper">
		<div class="popup-header">
			<div class="d-flex align-items-center justify-content-between mb-12">
				<h5 class="title"><?php esc_html_e( 'Shopping Cart', 'ecombon' ); ?></h5>
				<span class="icon-close-popup" data-bs-dismiss="offcanvas"><?php \Ecombon\Setup\Icons::render( 'X2' ); ?></span>
			</div>

			<?php if ( $free_shipping_threshold && ! $cart->is_empty() ) : ?>
				<?php
				$subtotal  = (float) $cart->get_subtotal();
				$remaining = max( 0, $free_shipping_threshold - $subtotal );
				$percent   = min( 100, ( $subtotal / $free_shipping_threshold ) * 100 );
				?>
				<div class="cart-threshold">
					<?php if ( $remaining > 0 ) : ?>
						<p class="text">
							<?php
							printf(
								/* translators: %s: remaining amount to reach free shipping. */
								esc_html__( 'Buy %s more to get free shipping', 'ecombon' ),
								wp_kses_post( wc_price( $remaining ) )
							);
							?>
						</p>
					<?php else : ?>
						<p class="text"><?php esc_html_e( "You've unlocked free shipping!", 'ecombon' ); ?></p>
					<?php endif; ?>
					<div class="tf-progress-bar tf-progress-ship">
						<div class="value" style="width: <?php echo esc_attr( (string) $percent ); ?>%">
							<?php \Ecombon\Setup\Icons::render( 'Truck' ); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<div class="wrap">
			<div class="tf-mini-cart-wrap list-file-delete">
				<div class="tf-mini-cart-main">
					<div class="tf-mini-cart-sroll">
						<div class="tf-mini-cart-items">
							<?php if ( $cart->is_empty() ) : ?>
								<div class="box-text_empty type-shop_cart">
									<div class="shop-empty_top">
										<?php \Ecombon\Setup\Icons::render( 'Handbag' ); ?>
										<h4 class="text-emp"><?php esc_html_e( 'Your cart is empty', 'ecombon' ); ?></h4>
										<p class="cl-text-2"><?php esc_html_e( 'Your cart is currently empty. Let us help you find the right product.', 'ecombon' ); ?></p>
									</div>
									<div class="shop-empty_bot">
										<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
											<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="tf-btn animate-btn"><?php esc_html_e( 'Shopping', 'ecombon' ); ?></a>
										<?php endif; ?>
										<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tf-btn btn-stroke"><?php esc_html_e( 'Back to home', 'ecombon' ); ?></a>
									</div>
								</div>
							<?php else : ?>
								<?php foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) :
									$product = $cart_item['data'];
									if ( ! $product || ! $product->exists() || $cart_item['quantity'] <= 0 ) {
										continue;
									}
									$thumbnail  = $product->get_image( array( 100, 133 ) );
									$remove_url = wc_get_cart_remove_url( $cart_item_key );
									?>
									<div class="tf-mini-cart-item">
										<div class="tf-mini-cart-image">
											<a href="<?php echo esc_url( $product->get_permalink( $cart_item ) ); ?>">
												<?php echo wp_kses_post( $thumbnail ); ?>
											</a>
										</div>
										<div class="tf-mini-cart-info">
											<a href="<?php echo esc_url( $product->get_permalink( $cart_item ) ); ?>" class="name fw-medium link text-line-clamp-1">
												<?php echo wp_kses_post( \Ecombon\WooCommerce\CartItemVariations::get_title( $cart_item ) ); ?>
											</a>
											<?php foreach ( \Ecombon\WooCommerce\CartItemVariations::get_rows( $cart_item ) as $variation_row ) : ?>
												<div class="tf-prd-select text-caption-01 mb-4">
													<span class="type-text cl-text-3"><?php echo esc_html( $variation_row['label'] ); ?>:&nbsp;</span>
													<span class="cl-text-2"><?php echo wp_kses_post( $variation_row['value'] ); ?></span>
												</div>
											<?php endforeach; ?>
										</div>
										<div class="tf-mini-cart-price">
											<a
												href="<?php echo esc_url( $remove_url ); ?>"
												class="tf-btn-line-3 type-primary cs-pointer remove_from_cart_button"
												aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'ecombon' ), wp_strip_all_tags( \Ecombon\WooCommerce\CartItemVariations::get_title( $cart_item ) ) ) ); ?>"
												data-product_id="<?php echo esc_attr( (string) $product->get_id() ); ?>"
												data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>"
												data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
											>
												<span class="text-caption-01 fw-semibold"><?php esc_html_e( 'Remove', 'ecombon' ); ?></span>
											</a>
											<div class="fw-semibold d-flex align-items-center justify-content-between gap-4">
												<span class="number"><?php echo esc_html( (string) $cart_item['quantity'] ); ?></span>
												<span>x</span>
												<span class="price tf-mini-card-price"><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<?php if ( ! $cart->is_empty() ) : ?>
					<div class="tf-mini-cart-bottom-wrap">
						<div class="tf-mini-cart-total">
							<h5 class="text-total d-flex align-content-center justify-content-between">
								<span class="subtotal"><?php esc_html_e( 'Subtotal', 'ecombon' ); ?></span>
								<span class="total-price tf-totals-total-value"><?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?></span>
							</h5>
						</div>

						<div class="tf-mini-cart-view-checkout">
							<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="tf-btn btn-stroke">
								<?php esc_html_e( 'View cart', 'ecombon' ); ?>
							</a>
							<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="tf-btn animate-btn">
								<?php esc_html_e( 'Check Out', 'ecombon' ); ?>
							</a>
						</div>

						<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="d-flex justify-content-center fw-semibold text-center link" data-bs-dismiss="offcanvas">
							<?php esc_html_e( 'Or Continue Shopping', 'ecombon' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
