<?php
/**
 * Header builder element: cart icon + real cart contents count.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart_count = 0;
if ( function_exists( 'WC' ) && WC()->cart ) {
	$cart_count = WC()->cart->get_cart_contents_count();
}
?>
<li>
	<a href="#shoppingCart" data-bs-toggle="offcanvas" class="nav-icon-item link shop-cart">
		<?php \Noorifa\Setup\Icons::render( 'Handbag' ); ?>
		<span class="count"><?php echo esc_html( (string) $cart_count ); ?></span>
	</a>
</li>
