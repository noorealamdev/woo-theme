<?php
/**
 * Proceed to checkout button, restyled to match the theme's own real
 * buttons (same classes as the cart drawer's "Check Out" button).
 *
 * @package Ecombon
 */

defined( 'ABSPATH' ) || exit;
?>
<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="action-checkout btn w-100 animate-btn">
	<span class="fw-semibold"><?php esc_html_e( 'Proceed To Checkout', 'ecombon' ); ?></span>
</a>
