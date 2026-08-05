<?php
/**
 * Footer builder element: copyright text.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$copyright_text = str_replace(
	array( '{year}', '{site_name}' ),
	array( gmdate( 'Y' ), get_bloginfo( 'name' ) ),
	noorifa_settings()['footer_copyright_text']
);
?>
<p class="text-nocopy cl-text-2" style="order: <?php echo esc_attr( (string) ( $args['order'] ?? 0 ) ); ?>;">
	<?php echo esc_html( $copyright_text ); ?>
</p>
