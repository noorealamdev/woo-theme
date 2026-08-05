<?php
/**
 * Single variation cart button, restyled with the theme's own real
 * quantity/action markup, plus a real "Buy It Now" button.
 *
 * WooCommerce's own variation-matching JS (add-to-cart-variation.js) finds
 * this button purely by class (`.single_add_to_cart_button`) and toggles
 * `disabled`/`wc-variation-*` classes on every element that matches — the
 * buy-it-now button carries that same class so it gets the exact same
 * "no selection yet" / "unavailable" guard for free, no extra JS needed.
 *
 * @package Noorifa
 * @version 10.5.2
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>
<div class="woocommerce-variation-add-to-cart variations_button product-total-quantity">
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<p class="mb-0"><?php esc_html_e( 'Quantity:', 'noorifa' ); ?></p>

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
		</button>
	</div>

	<button type="submit" name="noorifa_buy_now" value="1" class="single_add_to_cart_button btn type-xl btn-primary animate-btn w-100 buy-it-now-button">
		<?php esc_html_e( 'Buy It Now', 'noorifa' ); ?>
	</button>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>
