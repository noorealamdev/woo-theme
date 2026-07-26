<?php
/**
 * Mobile navigation offcanvas.
 *
 * The list itself is left empty on purpose: assets/js/main.js clones the
 * desktop `.box-nav-menu` into #wrapper-menu-navigation on load and wires
 * up its own accordion behaviour, so the same WordPress nav menu drives
 * both.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="offcanvas offcanvas-start canvas-mb" id="mobileMenu">
	<div class="canvas-header">
		<span class="icon-close-popup" data-bs-dismiss="offcanvas">
			<i class="icon icon-X2"></i>
		</span>
		<form class="form-search-nav" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<fieldset>
				<input type="text" name="s" placeholder="<?php esc_attr_e( 'What are you looking for?', 'ecombon' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" required>
			</fieldset>
			<button type="submit" class="btn-action">
				<i class="icon icon-MagnifyingGlass"></i>
			</button>
		</form>
	</div>
	<div class="canvas-body">
		<div class="mb-content-top">
			<ul class="nav-ul-mb" id="wrapper-menu-navigation"></ul>
		</div>
		<?php
		$phone = apply_filters( 'ecombon_contact_phone', '' );
		$email = apply_filters( 'ecombon_contact_email', get_option( 'admin_email' ) );
		$address = apply_filters( 'ecombon_contact_address', '' );
		if ( $phone || $email || $address ) :
			?>
			<div class="need-help-wrap">
				<p class="nd-title h6 fw-medium mb-16"><?php esc_html_e( 'Need Help?', 'ecombon' ); ?></p>
				<?php if ( $address ) : ?>
					<p class="lh-26 cl-text-2 mb-4"><?php echo esc_html( $address ); ?></p>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<a href="mailto:<?php echo esc_attr( $email ); ?>" class="cl-text-2 link mb-8"><?php echo esc_html( $email ); ?></a>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="cl-text-2 link"><?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
