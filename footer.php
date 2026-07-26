<?php
/**
 * The footer for the theme.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$phone   = apply_filters( 'ecombon_contact_phone', '' );
$email   = apply_filters( 'ecombon_contact_email', get_option( 'admin_email' ) );
$address = apply_filters( 'ecombon_contact_address', '' );
$socials = apply_filters(
	'ecombon_social_links',
	array(
		// 'facebook'  => 'https://www.facebook.com/',
		// 'x'         => 'https://x.com/',
		// 'instagram' => 'https://www.instagram.com/',
	)
);
$social_icons = array(
	'facebook'  => 'FacebookLogo',
	'x'         => 'XLogo',
	'instagram' => 'InstagramLogo',
	'tiktok'    => 'TiktokLogo',
	'snapchat'  => 'SnapchatLogo',
	'pinterest' => 'PinterestLogo',
	'youtube'   => 'YoutubeLogo',
);
?>
	<footer class="tf-footer">
		<div class="footer-inner flat-spacing position-relative">
			<div class="br-line top-0"></div>
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-lg-4">
						<div class="footer-infor d-flex flex-column align-items-start mb-lg-0">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-site mb-16">
								<?php if ( has_custom_logo() ) : ?>
									<?php the_custom_logo(); ?>
								<?php else : ?>
									<?php bloginfo( 'name' ); ?>
								<?php endif; ?>
							</a>
							<?php if ( $address ) : ?>
								<p class="lh-26 cl-text-2"><?php echo esc_html( $address ); ?></p>
							<?php endif; ?>
							<?php if ( $email ) : ?>
								<a href="mailto:<?php echo esc_attr( $email ); ?>" class="cl-text-2 link mb-8"><?php echo esc_html( $email ); ?></a>
							<?php endif; ?>
							<?php if ( $phone ) : ?>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="cl-text-2 link mb-16"><?php echo esc_html( $phone ); ?></a>
							<?php endif; ?>
							<?php if ( ! empty( $socials ) ) : ?>
								<ul class="tf-social-icon-2">
									<?php foreach ( $socials as $network => $url ) : ?>
										<?php if ( empty( $social_icons[ $network ] ) ) { continue; } ?>
										<li>
											<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
												<i class="icon icon-<?php echo esc_attr( $social_icons[ $network ] ); ?>"></i>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-2">
						<div class="footer-col-block footer-wrap-1 mx-xl-auto">
							<p class="footer-heading footer-heading-mobile"><?php esc_html_e( 'Company', 'ecombon' ); ?></p>
							<div class="tf-collapse-content">
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
					<div class="col-sm-6 col-md-6 col-lg-2">
						<div class="footer-col-block footer-wrap-2 mx-xl-auto">
							<p class="footer-heading footer-heading-mobile"><?php esc_html_e( 'Customer Care', 'ecombon' ); ?></p>
							<div class="tf-collapse-content">
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
					<div class="col-md-6 col-lg-4">
						<div class="footer-col-block footer-wrap-3 mb-0">
							<p class="footer-heading footer-heading-mobile"><?php esc_html_e( 'Newsletter', 'ecombon' ); ?></p>
							<div class="tf-collapse-content">
								<p class="footer-desc cl-text-2"><?php esc_html_e( 'Subscribe for store updates and discounts.', 'ecombon' ); ?></p>
								<?php get_template_part( 'template-parts/footer/newsletter-form' ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="footer-bottom">
			<div class="container">
				<div class="br-line sm-d-none"></div>
				<div class="inner-bottom">
					<p class="text-nocopy cl-text-2">
						<?php
						printf(
							/* translators: 1: current year, 2: site name. */
							esc_html__( '©%1$s %2$s. All Rights Reserved.', 'ecombon' ),
							esc_html( gmdate( 'Y' ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
						?>
					</p>
					<?php get_template_part( 'template-parts/footer/payment-icons' ); ?>
				</div>
			</div>
		</div>
	</footer>
</main>

<?php
get_template_part( 'template-parts/header/mobile-menu' );
get_template_part( 'template-parts/header/search-modal' );
get_template_part( 'template-parts/header/cart-drawer' );

if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
	get_template_part( 'template-parts/shop/filter-panel' );
}

wp_footer();
?>
</body>
</html>
