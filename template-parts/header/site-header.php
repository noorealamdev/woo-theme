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
<header class="header">
	<div class="br-line fake-class bottom-0"></div>
	<div class="container-full">
		<div class="header-inner">
			<div class="box-open-menu-mobile d-xl-none">
				<a href="#mobileMenu" data-bs-toggle="offcanvas" class="btn-open-menu">
					<?php \Ecombon\Setup\Icons::render( 'List' ); ?>
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
							<?php \Ecombon\Setup\Icons::render( 'MagnifyingGlass' ); ?>
						</a>
					</li>
					<li>
						<a href="<?php echo esc_url( $account_url ); ?>" class="nav-icon-item link">
							<?php \Ecombon\Setup\Icons::render( 'User' ); ?>
						</a>
					</li>
					<li>
						<a href="#shoppingCart" data-bs-toggle="offcanvas" class="nav-icon-item link shop-cart">
							<?php \Ecombon\Setup\Icons::render( 'Handbag' ); ?>
							<span class="count"><?php echo esc_html( (string) $cart_count ); ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</header>
