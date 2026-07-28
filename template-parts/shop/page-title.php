<?php
/**
 * Shop / product archive page-title banner.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_product_taxonomy() ) {
	$term        = get_queried_object();
	$title       = $term->name;
	$description = $term->description;
} else {
	$title       = wc_get_page_permalink( 'shop' ) ? get_the_title( wc_get_page_id( 'shop' ) ) : __( 'Shop', 'ecombon' );
	$description = '';
}

get_template_part(
	'template-parts/global/page-title',
	null,
	array(
		'title'    => $title,
		'subtitle' => $description,
	)
);
