<?php
/**
 * Announcement bar shown above the header.
 *
 * Static text — no slider. Real on/off toggle + message text, both
 * configurable via Appearance > Theme Settings > Topbar.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$topbar = \Noorifa\Settings\Layout::all()['topbar'];

if ( empty( $topbar['enabled'] ) ) {
	return;
}

$message = apply_filters( 'noorifa_topbar_message', $topbar['message'] );

if ( empty( $message ) ) {
	return;
}
?>
<div class="topbar bg-dark">
	<div class="container">
		<div class="text-center">
			<p class="text-line-clamp-1"><?php echo wp_kses_post( $message ); ?></p>
		</div>
	</div>
</div>
