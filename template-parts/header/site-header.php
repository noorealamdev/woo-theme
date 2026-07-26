<?php
/**
 * Main site header: mobile menu trigger, primary nav, logo, account/cart icons.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$account_url = function_exists( 'wc_get_page_permalink' )
	? wc_get_page_permalink( 'myaccount' )
	: wp_login_url();

$cart_count = 0;
if ( function_exists( 'WC' ) && WC()->cart ) {
	$cart_count = WC()->cart->get_cart_contents_count();
}
?>
<header class="tf-header">
	<div class="br-line fake-class bottom-0"></div>
	<div class="container-full">
		<div class="header-inner">
			<div class="box-open-menu-mobile d-xl-none">
				<a href="#mobileMenu" data-bs-toggle="offcanvas" class="btn-open-menu">
					<i class="icon icon-List"></i>
				</a>
			</div>

			<div class="header-left">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-site">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<?php bloginfo( 'name' ); ?>
					<?php endif; ?>
				</a>
			</div>

			<div class="header-center d-none d-xl-block">
				<nav class="box-navigation">
					<?php
					if ( has_nav_menu( 'primary' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'primary',
								'container'      => false,
								'items_wrap'     => '<ul class="box-nav-menu">%3$s</ul>',
								'walker'         => new \Ecombon\Navigation\MegaMenuWalker(),
								'depth'          => 3,
							)
						);
					}
					?>
				</nav>
			</div>

			<div class="header-right">
				<ul class="nav-icon-list">
					<li class="d-none d-sm-block">
						<a href="#search" data-bs-toggle="modal" class="nav-icon-item link">
							<i class="icon icon-MagnifyingGlass"></i>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( $account_url ); ?>" class="nav-icon-item link">
							<i class="icon icon-User"></i>
						</a>
					</li>
					<li class="d-none d-sm-block">
						<?php /* Wishlist is a Core Plugin module — left inert until it ships. */ ?>
						<a href="#" class="nav-icon-item link">
							<i class="icon icon-HeartStraight"></i>
						</a>
					</li>
					<li>
						<a href="#shoppingCart" data-bs-toggle="offcanvas" class="nav-icon-item link shop-cart">
							<i class="icon icon-Handbag"></i>
							<span class="count"><?php echo esc_html( (string) $cart_count ); ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</header>
