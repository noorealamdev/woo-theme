<?php
/**
 * Header builder element: account icon (real My Account / login link).
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$account_url = function_exists( 'wc_get_page_permalink' )
	? wc_get_page_permalink( 'myaccount' )
	: wp_login_url();
?>
<li>
	<a href="<?php echo esc_url( $account_url ); ?>" class="nav-icon-item link">
		<?php \Ecombon\Setup\Icons::render( 'User' ); ?>
	</a>
</li>
