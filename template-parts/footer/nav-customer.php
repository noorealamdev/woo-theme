<?php
/**
 * Footer builder element: "Customer Care" nav menu column.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="col-sm-6 col-md-6 col-lg-2" style="order: <?php echo esc_attr( (string) ( $args['order'] ?? 0 ) ); ?>;">
	<div class="footer-col-block footer-wrap-2 mx-xl-auto">
		<p class="footer-heading footer-heading-mobile"><?php echo esc_html( noorifa_settings()['footer_customer_heading'] ); ?></p>
		<div class="collapse-content">
			<?php
			if ( has_nav_menu( 'footer_customer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer_customer',
						'container'      => false,
						'items_wrap'     => '<ul class="footer-menu-list">%3$s</ul>',
						'depth'          => 1,
					)
				);
			}
			?>
		</div>
	</div>
</div>
