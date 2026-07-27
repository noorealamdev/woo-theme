<?php
/**
 * Cart page: real cart contents, real quantity/remove/coupon actions,
 * restyled with the theme's own `.tf-table-page-cart` markup.
 *
 * The form/table itself lives in template-parts/cart/cart-table.php (a
 * separate template-part, not inlined here) so Ecombon\WooCommerce\CartFragments
 * can render the exact same markup as a real AJAX fragment.
 *
 * @package Ecombon
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

get_template_part( 'template-parts/cart/page-title' );
?>
<section class="section-shoping-cart each-list-prd flat-spacing-2 pb-0">
	<div class="container">
		<div class="row">
			<div class="col-lg-8">
				<?php get_template_part( 'template-parts/cart/cart-table' ); ?>
			</div>

			<div class="col-lg-4">
				<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
				<div class="cart-collaterals">
					<?php
					/**
					 * Cart collaterals hook.
					 *
					 * @hooked woocommerce_cross_sell_display
					 * @hooked woocommerce_cart_totals - 10
					 */
					do_action( 'woocommerce_cart_collaterals' );
					?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php do_action( 'woocommerce_after_cart' ); ?>
