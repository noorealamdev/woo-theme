<?php
/**
 * The footer for the theme.
 *
 * Column order (top row) and the copyright/payment-icons order (bottom
 * bar) are real and configurable via the Footer Builder (Customize >
 * Footer) — see Noorifa\Settings\Layout. Every default here matches
 * the theme's original hardcoded layout exactly.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Noorifa\Settings\Layout;

$top_partials = array(
	'info'         => 'template-parts/footer/info',
	'nav-company'  => 'template-parts/footer/nav-company',
	'nav-customer' => 'template-parts/footer/nav-customer',
	'newsletter'   => 'template-parts/footer/newsletter',
);
?>
	<?php
	// A product can opt out of the footer for a distraction-free landing
	// page (Product edit screen > Page Layout).
	if ( ! ( class_exists( '\Noorifa\WooCommerce\ProductPageLayout' ) && \Noorifa\WooCommerce\ProductPageLayout::should_hide_footer() ) ) :
	?>
	<footer class="footer">
		<div class="footer-inner flat-spacing position-relative">
			<div class="container">
				<div class="row">
					<?php
					foreach ( Layout::footer_top_items() as $index => $column ) {
						if ( isset( $top_partials[ $column ] ) ) {
							get_template_part( $top_partials[ $column ], null, array( 'order' => $index ) );
						}
					}
					?>
				</div>
			</div>
		</div>
		<div class="footer-bottom">
			<div class="container">
				<div class="br-line sm-d-none"></div>
				<div class="inner-bottom">
					<?php
					foreach ( Layout::footer_bottom_items() as $index => $item ) {
						if ( 'copyright' === $item ) {
							get_template_part( 'template-parts/footer/copyright', null, array( 'order' => $index ) );
						} elseif ( 'payment-icons' === $item ) {
							get_template_part(
								'template-parts/footer/payment-icons',
								null,
								array(
									'order'          => $index,
									'footer_context' => true,
								)
							);
						}
					}
					?>
				</div>
			</div>
		</div>
	</footer>
	<?php endif; ?>
</main>

<?php
get_template_part( 'template-parts/header/mobile-menu' );
get_template_part( 'template-parts/header/search-modal' );
get_template_part( 'template-parts/header/cart-drawer' );
get_template_part( 'template-parts/global/floating-buttons' );
get_template_part( 'template-parts/global/popups' );
get_template_part( 'template-parts/global/cookie-notice' );

if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
	get_template_part( 'template-parts/shop/filter-panel' );
}

wp_footer();
?>
</body>
</html>
