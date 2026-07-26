<?php
/**
 * Announcement bar shown above the header.
 *
 * Static text — no slider. The message is filterable for now; a future
 * Core Plugin module (Header Builder) will make it editable without
 * touching the theme.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message = apply_filters(
	'ecombon_topbar_message',
	__( 'Midseason Sale: 20% Off — Auto Applied at Checkout — Limited Time Only', 'ecombon' )
);

if ( empty( $message ) ) {
	return;
}
?>
<div class="tf-topbar bg-dark">
	<div class="container">
		<div class="text-center">
			<p class="text-white text-line-clamp-1"><?php echo esc_html( $message ); ?></p>
		</div>
	</div>
</div>
