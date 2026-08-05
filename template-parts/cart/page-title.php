<?php
/**
 * Cart page-title banner.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part(
	'template-parts/global/page-title',
	null,
	array(
		'title'    => __( 'Shopping Cart', 'noorifa' ),
		'subtitle' => __( 'Review your selected items, update quantities, and get ready for a smooth and easy checkout experience.', 'noorifa' ),
	)
);
