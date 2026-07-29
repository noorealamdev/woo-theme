<?php
/**
 * Accepted payment method icons.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icons = apply_filters(
	'ecombon_payment_icons',
	array( 'visa', 'master-card', 'amex', 'paypal', 'water', 'discover' )
);

if ( empty( $icons ) ) {
	return;
}

$list_class = $args['list_class'] ?? 'list payment-list';
$item_class = $args['item_class'] ?? '';
?>
<ul class="<?php echo esc_attr( $list_class ); ?>">
	<?php foreach ( $icons as $icon ) : ?>
		<li class="<?php echo esc_attr( $item_class ); ?>">
			<img loading="lazy" width="38" height="24" src="<?php echo esc_url( ECOMBON_THEME_URI . '/assets/images/payment/' . $icon . '.svg' ); ?>" alt="<?php echo esc_attr( ucwords( str_replace( '-', ' ', $icon ) ) ); ?>">
		</li>
	<?php endforeach; ?>
</ul>
