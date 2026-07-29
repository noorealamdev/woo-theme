<?php
/**
 * Real empty-cart message + real return-to-shop links — the content-only
 * half of woocommerce/cart/cart-empty.php (no page-title banner), shared
 * with Ecombon\WooCommerce\CartFragments so an AJAX remove-to-empty updates
 * the real page in place instead of just the header/drawer.
 *
 * @package Ecombon
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="box-text_empty type-shop_cart text-center mx-auto" style="max-width: 420px;">
	<div class="shop-empty_top">
		<?php \Ecombon\Setup\Icons::render( 'Handbag' ); ?>
		<h4 class="text-emp"><?php esc_html_e( 'Your cart is empty', 'ecombon' ); ?></h4>
		<div class="cl-text-2">
			<?php
			/**
			 * Hooked: wc_empty_cart_message — keeps this a real WooCommerce
			 * message (respects any filters/plugins on it), not fixed text.
			 * It renders its own real notice wrapper, so this stays a <div>
			 * rather than a <p>.
			 */
			do_action( 'woocommerce_cart_is_empty' );
			?>
		</div>
	</div>
	<div class="shop-empty_bot d-flex justify-content-center gap-12">
		<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
			<a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="btn animate-btn">
				<?php echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'ecombon' ) ) ); ?>
			</a>
		<?php endif; ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-stroke"><?php esc_html_e( 'Back to home', 'ecombon' ); ?></a>
	</div>
</div>
