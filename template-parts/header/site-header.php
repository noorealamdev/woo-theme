<?php
/**
 * Main site header: mobile menu trigger, primary nav, logo, account/cart icons.
 *
 * A real 3-zone (left/center/right) builder — any of the 5 header modules
 * (logo/navigation/search/account/cart) can be freely placed in any zone,
 * in any order, including mixed zones — see Ecombon\Settings\Layout and
 * the class docblock there for why this is safe (a plain flex row with no
 * module-specific positioning). Every default here matches the theme's
 * original hardcoded layout exactly.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Ecombon\Settings\Layout;

$module_partials = array(
	'logo'       => 'template-parts/header/logo',
	'navigation' => 'template-parts/header/navigation',
	'search'     => 'template-parts/header/search',
	'account'    => 'template-parts/header/account',
	'cart'       => 'template-parts/header/cart',
);

$icon_modules = array( 'search', 'account', 'cart' );

// Real, default-off setting — when on, the hamburger trigger stays visible
// (and the inline desktop nav stays hidden) at every width, not just below
// the `xl` breakpoint. See Layout::force_mobile_menu().
$force_mobile_menu   = Layout::force_mobile_menu();
$mobile_trigger_class = $force_mobile_menu ? '' : 'd-xl-none';
$desktop_nav_class    = $force_mobile_menu ? 'd-none' : 'd-none d-xl-block';
?>
<header class="header">
	<div class="container-full">
		<div class="header-inner">
			<div class="box-open-menu-mobile <?php echo esc_attr( $mobile_trigger_class ); ?>">
				<a href="#mobileMenu" data-bs-toggle="offcanvas" class="btn-open-menu">
					<?php \Ecombon\Setup\Icons::render( 'ListBold' ); ?>
				</a>
			</div>

			<?php
			foreach ( array( 'left', 'center', 'right' ) as $zone ) :
				$items = Layout::header_zone_items( $zone );
				if ( empty( $items ) ) {
					continue;
				}
				?>
				<div class="header-zone header-zone--<?php echo esc_attr( $zone ); ?>">
					<?php
					$pending_icons = array();
					foreach ( $items as $module ) {
						if ( ! isset( $module_partials[ $module ] ) ) {
							continue;
						}

						// Consecutive icon-type modules share one <ul>, matching
						// the real markup their partials already expect (each
						// one renders a bare <li>) — icons aren't valid outside
						// a list, and this keeps the existing partials untouched.
						if ( in_array( $module, $icon_modules, true ) ) {
							$pending_icons[] = $module;
							continue;
						}

						if ( $pending_icons ) {
							echo '<ul class="nav-icon-list">';
							foreach ( $pending_icons as $icon_module ) {
								get_template_part( $module_partials[ $icon_module ] );
							}
							echo '</ul>';
							$pending_icons = array();
						}

						if ( 'navigation' === $module ) {
							echo '<div class="' . esc_attr( $desktop_nav_class ) . '">';
							get_template_part( $module_partials['navigation'] );
							echo '</div>';
						} else {
							get_template_part( $module_partials[ $module ] );
						}
					}

					if ( $pending_icons ) {
						echo '<ul class="nav-icon-list">';
						foreach ( $pending_icons as $icon_module ) {
							get_template_part( $module_partials[ $icon_module ] );
						}
						echo '</ul>';
					}
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</header>
