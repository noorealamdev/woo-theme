<?php
/**
 * CheckoutFields component.
 *
 * @package Ecombon
 */

namespace Ecombon\WooCommerce;

use Ecombon\Setup\ComponentInterface;

/**
 * Restyles WooCommerce's own real checkout fields (still rendered by the
 * real `woocommerce_form_field()` — real validation, real required-field
 * logic, real per-country state lists, all untouched) into clean,
 * placeholder-only inputs instead of a visible label above every field.
 *
 * The real label is kept (screen-reader only) rather than removed, so
 * screen readers and browser autofill still get a real accessible name.
 */
class CheckoutFields implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_filter( 'woocommerce_form_field_args', array( $this, 'placeholder_instead_of_label' ), 10, 2 );
	}

	/**
	 * Moves a field's real label into its placeholder, visually hiding the
	 * label itself.
	 *
	 * @param array  $args Field arguments.
	 * @param string $key  Field key.
	 * @return array Filtered field arguments.
	 */
	public function placeholder_instead_of_label( array $args, string $key ): array {
		// Only real checkout address/contact/order-note fields — leave
		// payment-gateway fields, quantity inputs, etc. exactly as each
		// defines itself.
		$checkout_field_prefixes = array( 'billing_', 'shipping_', 'account_' );
		$is_checkout_field       = 'order_comments' === $key;

		foreach ( $checkout_field_prefixes as $prefix ) {
			if ( 0 === strpos( $key, $prefix ) ) {
				$is_checkout_field = true;
				break;
			}
		}

		if ( ! $is_checkout_field || in_array( $args['type'], array( 'checkbox', 'radio' ), true ) ) {
			return $args;
		}

		if ( empty( $args['placeholder'] ) && ! empty( $args['label'] ) ) {
			$args['placeholder'] = wp_strip_all_tags( $args['label'] ) . ( $args['required'] ? ' *' : '' );
		}

		$args['label_class']   = array_merge( (array) ( $args['label_class'] ?? array() ), array( 'screen-reader-text' ) );
		$args['label']         = wp_strip_all_tags( $args['label'] ?? '' );

		return $args;
	}
}
