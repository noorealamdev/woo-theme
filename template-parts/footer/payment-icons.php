<?php
/**
 * Accepted payment method icons — either a single site-owner-uploaded
 * image (a combined icon strip), or the theme's own real bundled icon set.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$custom_image = apply_filters( 'noorifa_payment_icons_image', '' );
$icons        = array( 'visa', 'master-card', 'amex', 'paypal', 'water', 'discover' );

// `footer_context` is only ever passed by footer.php's own bottom-bar loop
// (see Layout::footer_bottom_items(), which already excludes this partial
// entirely when 'payment-icons' isn't in the stored bottom-bar list — no
// separate visibility check needed here). This same real template is also
// reused, unrelated, on the single product page's "Guaranteed Safe
// Checkout" trust badge, which must never be affected by the footer
// builder's own order settings.
$is_footer_context = ! empty( $args['footer_context'] );

$list_class = $args['list_class'] ?? 'list payment-list';
$item_class = $args['item_class'] ?? '';
$order      = $is_footer_context ? (string) ( $args['order'] ?? 0 ) : null;
?>
<ul class="<?php echo esc_attr( $list_class ); ?>"<?php echo null !== $order ? ' style="order: ' . esc_attr( $order ) . ';"' : ''; ?>>
	<?php if ( $custom_image ) : ?>
		<li class="<?php echo esc_attr( $item_class ); ?> payment-list-image">
			<img loading="lazy" src="<?php echo esc_url( $custom_image ); ?>" alt="<?php esc_attr_e( 'Accepted payment methods', 'noorifa' ); ?>">
		</li>
	<?php else : ?>
		<?php foreach ( $icons as $icon ) : ?>
			<li class="<?php echo esc_attr( $item_class ); ?>">
				<img loading="lazy" width="38" height="24" src="<?php echo esc_url( NOORIFA_THEME_URI . '/assets/images/payment/' . $icon . '.svg' ); ?>" alt="<?php echo esc_attr( ucwords( str_replace( '-', ' ', $icon ) ) ); ?>">
			</li>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>
