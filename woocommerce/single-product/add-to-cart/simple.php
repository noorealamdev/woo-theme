<?php
/**
 * Simple product add to cart: same quantity/action markup as the variable
 * template, plus a real "Buy It Now" button.
 *
 * @package Ecombon
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

if ( $product->is_in_stock() ) :
	do_action( 'woocommerce_before_add_to_cart_form' );
	?>

	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data">
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<div class="product-total-quantity">
			<p class="mb-0"><?php esc_html_e( 'Quantity:', 'ecombon' ); ?></p>

			<div class="group-action">
				<?php
				do_action( 'woocommerce_before_add_to_cart_quantity' );

				woocommerce_quantity_input(
					array(
						'min_value'   => $product->get_min_purchase_quantity(),
						'max_value'   => $product->get_max_purchase_quantity(),
						'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // phpcs:ignore WordPress.Security.NonceVerification.Missing
					)
				);

				do_action( 'woocommerce_after_add_to_cart_quantity' );
				?>

				<button type="submit" class="single_add_to_cart_button btn-action-price btn type-xl animate-btn w-100">
					<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
					<?php /* "" prefix deliberately avoids main.js's fake demo `.price-add` calculator (see assets/js/main.js totalPriceVariant()), which clobbers any real element with that exact class on every page load. */ ?>
					<span class="add-price-sep d-none d-sm-block d-md-none d-lg-block">&nbsp;-&nbsp;</span>
					<span class="add-price d-none d-sm-block d-md-none d-lg-block"><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
				</button>
			</div>

			<button type="submit" name="ecombon_buy_now" value="1" class="single_add_to_cart_button btn type-xl btn-primary animate-btn w-100 buy-it-now-button">
				<?php esc_html_e( 'Buy It Now', 'ecombon' ); ?>
			</button>
		</div>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

		<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
