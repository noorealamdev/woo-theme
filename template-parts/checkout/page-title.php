<?php
/**
 * Checkout page-title banner.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part(
	'template-parts/global/page-title',
	null,
	array(
		'title'    => __( 'Check Out', 'ecombon' ),
		'subtitle' => __( 'Review your order details carefully and complete your purchase securely and easily for a smooth shopping experience.', 'ecombon' ),
	)
);
