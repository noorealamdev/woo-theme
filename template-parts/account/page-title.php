<?php
/**
 * My Account page-title banner.
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
		'title'    => __( 'My Account', 'ecombon' ),
		'subtitle' => __( 'Manage your profile, track orders, and easily update your personal details anytime, all in one convenient place.', 'ecombon' ),
	)
);
