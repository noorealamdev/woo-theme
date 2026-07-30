<?php
/**
 * The real cart form + item table — a standalone template-part (not inlined
 * into woocommerce/cart/cart.php) so `Ecombon\WooCommerce\CartFragments` can
 * render the exact same markup as a real AJAX fragment for the
 * `.woocommerce-cart-form` selector WooCommerce's own cart.js swaps in
 * after a quantity/coupon update — without it, those real AJAX updates
 * would refresh the mini-cart/badge but leave the full cart page's own
 * table showing stale data.
 *
 * Variation attributes are shown as plain text (via CartItemVariations),
 * not editable per-row — WooCommerce has no built-in "swap variation in an
 * existing cart line" action, so an editable-looking control there would be
 * fake.
 *
 * @package Ecombon
 */

defined( 'ABSPATH' ) || exit;
?>
<form class="form-shop-cart woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<div class="overflow-auto">
		<?php /* `.woocommerce-cart-form__contents` is required — WooCommerce's own real cart.js (AJAX quantity/coupon updates) checks for that exact class before doing anything. */ ?>
		<table class="table-page-cart woocommerce-cart-form__contents">
			<thead>
				<tr>
					<th><p class="h6 fw-medium"><?php esc_html_e( 'Products', 'ecombon' ); ?></p></th>
					<th><p class="h6 fw-medium"><?php esc_html_e( 'Price', 'ecombon' ); ?></p></th>
					<th><p class="h6 fw-medium"><?php esc_html_e( 'Quantity', 'ecombon' ); ?></p></th>
					<th class="text-end"><p class="h6 fw-medium"><?php esc_html_e( 'Total Price', 'ecombon' ); ?></p></th>
				</tr>
			</thead>
			<tbody>
				<?php do_action( 'woocommerce_before_cart_contents' ); ?>

				<?php
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
					$visible    = apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key );

					if ( ! ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible ) ) {
						continue;
					}

					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					$product_name       = \Ecombon\WooCommerce\CartItemVariations::get_title( $cart_item );
					$thumbnail           = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( array( 100, 133 ) ), $cart_item, $cart_item_key );
					?>
					<tr class="cart_item each-prd file-delete <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
						<td class="cart_product">
							<?php if ( $product_permalink ) : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>" class="img-prd"><?php echo wp_kses_post( $thumbnail ); ?></a>
							<?php else : ?>
								<span class="img-prd"><?php echo wp_kses_post( $thumbnail ); ?></span>
							<?php endif; ?>
							<div class="infor-prd">
								<?php if ( $product_permalink ) : ?>
									<a href="<?php echo esc_url( $product_permalink ); ?>" class="prd_name fw-medium link lh-24"><?php echo esc_html( $product_name ); ?></a>
								<?php else : ?>
									<p class="prd_name fw-medium lh-24"><?php echo esc_html( $product_name ); ?></p>
								<?php endif; ?>

								<?php foreach ( \Ecombon\WooCommerce\CartItemVariations::get_rows( $cart_item ) as $variation_row ) : ?>
									<div class="prd_select text-caption-01">
										<span class="type-text cl-text-3"><?php echo esc_html( $variation_row['label'] ); ?>:&nbsp;</span>
										<span class="cl-text-2"><?php echo wp_kses_post( $variation_row['value'] ); ?></span>
									</div>
								<?php endforeach; ?>

								<?php
								/**
								 * Real hook — no default callback (the theme
								 * shows variation data via CartItemVariations
								 * above, not this hook), kept open for
								 * extensions (e.g. a subscription-details or
								 * backorder-notice plugin).
								 */
								do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
								?>

								<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a role="button" href="%s" class="cart_remove btn-line-3 type-primary remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><span class="text-caption-01 fw-semibold">%s</span></a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										esc_attr( sprintf( __( 'Remove %s from cart', 'ecombon' ), wp_strip_all_tags( $product_name ) ) ),
										esc_attr( $product_id ),
										esc_attr( $cart_item_key ),
										esc_attr( $_product->get_sku() ),
										esc_html__( 'Remove', 'ecombon' )
									),
									$cart_item_key
								);
								?>
							</div>
						</td>

						<td class="cart_price each-price fw-semibold text-primary" data-cart-title="<?php esc_attr_e( 'Price', 'ecombon' ); ?>">
							<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</td>

						<td class="cart_quantity" data-cart-title="<?php esc_attr_e( 'Quantity', 'ecombon' ); ?>">
							<?php
							if ( $_product->is_sold_individually() ) {
								$min_quantity = 1;
								$max_quantity = 1;
							} else {
								$min_quantity = 0;
								$max_quantity = $_product->get_max_purchase_quantity();
							}

							$product_quantity = woocommerce_quantity_input(
								array(
									'input_name'   => "cart[{$cart_item_key}][qty]",
									'input_value'  => $cart_item['quantity'],
									'max_value'    => $max_quantity,
									'min_value'    => $min_quantity,
									'product_name' => $product_name,
								),
								$_product,
								false
							);

							echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</td>

						<td>
							<div class="cart_total fw-semibold text-primary each-subtotal-price">
								<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</td>
					</tr>
					<?php
				}
				?>

				<?php do_action( 'woocommerce_cart_contents' ); ?>
			</tbody>
		</table>
	</div>

	<?php if ( wc_coupons_enabled() ) : ?>
		<?php /* Real class `coupon` (alongside the theme's own) is required — WooCommerce's real cart.js looks for a `.coupon` ancestor to show a real invalid-coupon error message; without it, the error is silently dropped. */ ?>
		<div class="ip-discount-code coupon">
			<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'ecombon' ); ?></label>
			<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Add voucher discount', 'ecombon' ); ?>" />
			<button type="submit" class="btn animate-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply Code', 'ecombon' ); ?>"><?php esc_html_e( 'Apply Code', 'ecombon' ); ?></button>
			<?php do_action( 'woocommerce_cart_coupon' ); ?>
		</div>
	<?php endif; ?>

	<button type="submit" class="d-none" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'ecombon' ); ?>"><?php esc_html_e( 'Update cart', 'ecombon' ); ?></button>

	<?php do_action( 'woocommerce_cart_actions' ); ?>
	<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
	<?php do_action( 'woocommerce_after_cart_contents' ); ?>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>
