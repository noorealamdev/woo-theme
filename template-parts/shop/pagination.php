<?php
/**
 * Shop pagination, styled to match the theme's own `.page-pagination` markup.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_query;

$total = $wp_query->max_num_pages;

if ( $total <= 1 ) {
	return;
}

$current = max( 1, get_query_var( 'paged' ) ?: get_query_var( 'page' ) );

$links = paginate_links(
	array(
		'total'     => $total,
		'current'   => $current,
		'prev_next' => true,
		'prev_text' => \Ecombon\Setup\Icons::html( 'CaretLeft' ),
		'next_text' => \Ecombon\Setup\Icons::html( 'CaretRightThin' ),
		'type'      => 'array',
	)
);

if ( empty( $links ) ) {
	return;
}
?>
<div class="wd-full justify-content-center">
	<div class="page-pagination">
		<?php foreach ( $links as $link ) : ?>
			<?php
			$is_current = false !== strpos( $link, 'current' );

			// Pull the inner text/markup and href out of WP's <a>/<span> and
			// re-render with the theme's own `.pag-item` markup instead.
			preg_match( '/href="([^"]*)"/', $link, $href_match );
			preg_match( '/>(.*)<\/(?:a|span)>/s', $link, $content_match );

			$href    = $href_match[1] ?? '#';
			$content = $content_match[1] ?? '';
			$class   = $is_current ? 'pag-item active' : 'pag-item';
			?>
			<?php if ( $is_current ) : ?>
				<p class="<?php echo esc_attr( $class ); ?>"><?php echo wp_kses_post( $content ); ?></p>
			<?php else : ?>
				<a href="<?php echo esc_url( $href ); ?>" class="<?php echo esc_attr( $class ); ?>"><?php echo wp_kses_post( $content ); ?></a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>
