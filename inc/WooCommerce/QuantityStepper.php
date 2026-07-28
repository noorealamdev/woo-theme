<?php
/**
 * QuantityStepper component.
 *
 * @package Ecombon
 */

namespace Ecombon\WooCommerce;

use Ecombon\Setup\ComponentInterface;

/**
 * Wraps WooCommerce's real quantity `<input class="qty">` with +/- buttons.
 *
 * The input itself, its min/max/step and WooCommerce's own validation are
 * untouched — this only adds a nicer control around it (see
 * assets/js/ecombon-cart.js for the click behaviour).
 */
class QuantityStepper implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'woocommerce_before_quantity_input_field', array( $this, 'render_decrease_button' ) );
		add_action( 'woocommerce_after_quantity_input_field', array( $this, 'render_increase_button' ) );
	}

	/**
	 * Outputs the decrease ("-") button before the real quantity input.
	 */
	public function render_decrease_button(): void {
		echo '<button type="button" class="btn-quantity btn-decrease" aria-label="' . esc_attr__( 'Decrease quantity', 'ecombon' ) . '">' . \Ecombon\Setup\Icons::html( 'minus' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Outputs the increase ("+") button after the real quantity input.
	 */
	public function render_increase_button(): void {
		echo '<button type="button" class="btn-quantity btn-increase" aria-label="' . esc_attr__( 'Increase quantity', 'ecombon' ) . '">' . \Ecombon\Setup\Icons::html( 'plus' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
