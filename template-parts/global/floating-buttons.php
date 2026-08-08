<?php
/**
 * Floating chat button(s) — currently a WhatsApp click-to-chat button fixed
 * to a corner of every page. Rendered from footer.php's always-output area so
 * it still appears on distraction-free products that hide the footer.
 *
 * Uses the https://wa.me/ link (never the whatsapp:// app scheme) so it opens
 * WhatsApp Web/app without triggering the browser's "open external app"
 * permission prompt.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$floating = \Noorifa\Settings\Layout::all()['floating'] ?? array();

if ( empty( $floating['whatsapp_enabled'] ) ) {
	return;
}

$number = \Noorifa\Settings\Layout::floating_whatsapp_number();

if ( '' === $number ) {
	return;
}

$message  = trim( (string) ( $floating['whatsapp_message'] ?? '' ) );
$tooltip  = trim( (string) ( $floating['whatsapp_tooltip'] ?? '' ) );
$position = ( 'left' === ( $floating['position'] ?? 'right' ) ) ? 'left' : 'right';

$url = 'https://wa.me/' . $number;
if ( '' !== $message ) {
	// rawurlencode (not add_query_arg) so spaces become %20 — wa.me renders
	// "+" from form-encoding as literal plus signs.
	$url .= '?text=' . rawurlencode( $message );
}
?>
<div class="noorifa-floating noorifa-floating--<?php echo esc_attr( $position ); ?>">
	<a
		class="noorifa-floating__whatsapp"
		href="<?php echo esc_url( $url ); ?>"
		target="_blank"
		rel="noopener noreferrer"
		aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'noorifa' ); ?>"
	>
		<?php if ( '' !== $tooltip ) : ?>
			<span class="noorifa-floating__tooltip"><?php echo esc_html( $tooltip ); ?></span>
		<?php endif; ?>
		<?php \Noorifa\Setup\Icons::render( 'WhatsappLogo' ); ?>
	</a>
</div>
