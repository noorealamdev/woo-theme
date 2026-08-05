<?php
/**
 * My Account page-title banner.
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
		'title'    => __( 'My Account', 'noorifa' ),
		'subtitle' => __( 'Manage your profile, track orders, and easily update your personal details anytime, all in one convenient place.', 'noorifa' ),
	)
);
