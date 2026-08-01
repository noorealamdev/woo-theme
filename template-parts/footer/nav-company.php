<?php
/**
 * Footer builder element: "Company" nav menu column.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="col-sm-6 col-md-6 col-lg-2" style="order: <?php echo esc_attr( (string) ( $args['order'] ?? 0 ) ); ?>;">
	<div class="footer-col-block footer-wrap-1 mx-xl-auto">
		<p class="footer-heading footer-heading-mobile"><?php echo esc_html( ecombon_settings()['footer_company_heading'] ); ?></p>
		<div class="collapse-content">
			<?php
			if ( has_nav_menu( 'footer_company' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer_company',
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
