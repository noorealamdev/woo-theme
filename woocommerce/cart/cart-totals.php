<?php
/**
 * Cart totals sidebar: real subtotal/coupon/shipping/tax/total (all via
 * WooCommerce's own wc_cart_totals_*_html() functions — this only changes
 * the surrounding markup to the theme's own `.box-order-summary`), plus a
 * real free-shipping threshold progress bar (same real zone-based
 * calculation as the cart drawer's, see Noorifa\WooCommerce\Shipping).
 *
 * Keeps the real `.cart_totals` wrapper class — WooCommerce's own real
 * cart.js AJAX quantity/coupon update looks for that exact class to swap
 * in fresh totals HTML after each update, so renaming it would silently
 * break that.
 *
 * @package Noorifa
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;

$free_shipping_threshold = \Noorifa\WooCommerce\Shipping::get_free_shipping_threshold();
?>
<div class="cart_totals box-order-summary <?php echo WC()->customer->has_calculated_shipping() ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<?php if ( $free_shipping_threshold ) : ?>
		<?php
		$subtotal  = (float) WC()->cart->get_subtotal();
		$remaining = max( 0, $free_shipping_threshold - $subtotal );
		$percent   = min( 100, ( $subtotal / $free_shipping_threshold ) * 100 );
		?>
		<div class="notification-progress">
			<?php if ( $remaining > 0 ) : ?>
				<p>
					<?php
					printf(
						/* translators: %s: remaining amount to reach free shipping. */
						esc_html__( 'Buy %s more to get free shipping', 'noorifa' ),
						'<span class="text-primary fw-bold">' . wp_kses_post( wc_price( $remaining ) ) . '</span>'
					);
					?>
				</p>
			<?php else : ?>
				<p><?php esc_html_e( "You've unlocked free shipping!", 'noorifa' ); ?></p>
			<?php endif; ?>
			<div class="progress-cart">
				<div class="value" style="width: <?php echo esc_attr( (string) $percent ); ?>%;" data-progress="<?php echo esc_attr( (string) $percent ); ?>">
					<span class="round"><?php \Noorifa\Setup\Icons::render( 'Truck' ); ?></span>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<h5 class="title mb-20"><?php esc_html_e( 'Order Summary', 'noorifa' ); ?></h5>

	<div class="subtotal d-flex justify-content-between align-items-center">
		<p class="fw-medium lh-24"><?php esc_html_e( 'Subtotal', 'noorifa' ); ?></p>
		<span class="total fw-medium lh-24"><?php wc_cart_totals_subtotal_html(); ?></span>
	</div>

	<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
		<div class="discount d-flex justify-content-between align-items-center coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
			<p class="fw-medium lh-24"><?php wc_cart_totals_coupon_label( $coupon ); ?></p>
			<span class="total fw-medium lh-24"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
		</div>
	<?php endforeach; ?>

	<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
		<div class="ship">
			<p class="fw-medium lh-24"><?php esc_html_e( 'Shipping', 'noorifa' ); ?></p>
			<div class="box-check-payment flex-grow-1">
				<?php
				do_action( 'woocommerce_cart_totals_before_shipping' );
				wc_cart_totals_shipping_html();
				do_action( 'woocommerce_cart_totals_after_shipping' );
				?>
			</div>
		</div>
	<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
		<div class="ship">
			<p class="fw-medium lh-24"><?php esc_html_e( 'Shipping', 'noorifa' ); ?></p>
			<?php woocommerce_shipping_calculator(); ?>
		</div>
	<?php endif; ?>

	<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
		<div class="discount d-flex justify-content-between align-items-center fee">
			<p class="fw-medium lh-24"><?php echo esc_html( $fee->name ); ?></p>
			<span class="total fw-medium lh-24"><?php wc_cart_totals_fee_html( $fee ); ?></span>
		</div>
	<?php endforeach; ?>

	<?php
	if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
		if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
			foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				?>
				<div class="discount d-flex justify-content-between align-items-center tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
					<p class="fw-medium lh-24"><?php echo esc_html( $tax->label ); ?></p>
					<span class="total fw-medium lh-24"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
				</div>
				<?php
			}
		} else {
			?>
			<div class="discount d-flex justify-content-between align-items-center tax-total">
				<p class="fw-medium lh-24"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></p>
				<span class="total fw-medium lh-24"><?php wc_cart_totals_taxes_total_html(); ?></span>
			</div>
			<?php
		}
	}
	?>

	<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

	<h5 class="total-order d-flex justify-content-between align-items-center">
		<span><?php esc_html_e( 'Total', 'noorifa' ); ?></span>
		<span class="total each-total-price"><?php wc_cart_totals_order_total_html(); ?></span>
	</h5>

	<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	<div class="list-ver text-center">
		<?php
		/**
		 * Real "Proceed to Checkout" button.
		 *
		 * @hooked woocommerce_button_proceed_to_checkout
		 */
		do_action( 'woocommerce_proceed_to_checkout' );
		?>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="link-underline link">
			<span class="fw-semibold"><?php esc_html_e( 'Or Continue Shopping', 'noorifa' ); ?></span>
		</a>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>
</div>
