<?php
/**
 * Checkout page-title banner with breadcrumb.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="section-page-title text-center flat-spacing-2 pb-0">
	<div class="container">
		<div class="main-page-title">
			<div class="breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-caption-01 cl-text-3 link"><?php esc_html_e( 'Home', 'ecombon' ); ?></a>
				<i class="icon icon-CaretRightThin cl-text-3"></i>
				<p class="text-caption-01"><?php esc_html_e( 'Check Out', 'ecombon' ); ?></p>
			</div>
			<h3><?php esc_html_e( 'Check Out', 'ecombon' ); ?></h3>
			<p class="text-body-1 cl-text-2">
				<?php esc_html_e( 'Review your order details carefully and complete your purchase securely and easily for a smooth shopping experience.', 'ecombon' ); ?>
			</p>
		</div>
	</div>
</section>
