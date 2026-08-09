<?php
/**
 * Header builder element: a call-to-action button.
 *
 * Placed via the Header Builder (Theme Settings → Header) and configured
 * with its text/link there. Renders nothing until a button text is set, so
 * an unconfigured element placed in a zone stays invisible on the front end.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$noorifa_header = \Noorifa\Settings\Layout::all()['header'] ?? array();
$noorifa_btn_text = trim( (string) ( $noorifa_header['button_text'] ?? '' ) );

if ( '' === $noorifa_btn_text ) {
	return;
}

$noorifa_btn_url     = trim( (string) ( $noorifa_header['button_url'] ?? '' ) );
$noorifa_btn_url     = '' !== $noorifa_btn_url ? $noorifa_btn_url : '#';
$noorifa_btn_new_tab = ! empty( $noorifa_header['button_new_tab'] );
?>
<div class="header-button">
	<a
		href="<?php echo esc_url( $noorifa_btn_url ); ?>"
		class="btn animate-btn"
		<?php echo $noorifa_btn_new_tab ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
	>
		<?php echo esc_html( $noorifa_btn_text ); ?>
	</a>
</div>
