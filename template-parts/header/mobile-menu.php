<?php
/**
 * Mobile navigation offcanvas.
 *
 * The list itself is left empty on purpose: assets/js/main.js clones the
 * desktop `.box-nav-menu` into #wrapper-menu-navigation on load and wires
 * up its own accordion behaviour, so the same WordPress nav menu drives
 * both.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="offcanvas offcanvas-start canvas-mb" id="mobileMenu">
	<div class="canvas-header">
		<span class="icon-close-popup" data-bs-dismiss="offcanvas">
			<?php \Noorifa\Setup\Icons::render( 'X2' ); ?>
		</span>
	</div>
	<div class="canvas-body">
		<div class="mb-content-top">
			<ul class="nav-ul-mb" id="wrapper-menu-navigation"></ul>
		</div>
		<?php
		$phone           = apply_filters( 'noorifa_contact_phone', '' );
		$email           = apply_filters( 'noorifa_contact_email', get_option( 'admin_email' ) );
		$address         = apply_filters( 'noorifa_contact_address', '' );
		$whatsapp_raw    = \Noorifa\Settings\Layout::all()['header']['whatsapp_number'] ?? '';
		$whatsapp_digits = preg_replace( '/[^0-9]/', '', $whatsapp_raw );
		if ( $whatsapp_digits || $phone || $email || $address ) :
			?>
			<div class="need-help-wrap">
				<p class="nd-title h6 fw-medium mb-16"><?php esc_html_e( 'Need Help?', 'noorifa' ); ?></p>
				<?php if ( $address ) : ?>
					<p class="lh-26 cl-text-2 mb-4"><?php echo esc_html( $address ); ?></p>
				<?php endif; ?>
				<?php if ( $whatsapp_digits ) : ?>
					<a href="<?php echo esc_url( 'https://wa.me/' . $whatsapp_digits ); ?>" target="_blank" rel="noopener noreferrer" class="cl-text-2 link fw-bold mb-8"><?php echo esc_html( $whatsapp_raw ); ?></a>
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
