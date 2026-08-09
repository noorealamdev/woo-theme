<?php
/**
 * The template for 404 (not found) responses.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$shop_url = ( function_exists( 'wc_get_page_permalink' ) ) ? wc_get_page_permalink( 'shop' ) : '';
?>

<div id="primary" class="site-main">
	<section class="error-404 flat-spacing">
		<div class="container">
			<div class="error-404__inner">
				<div class="error-404__code" aria-hidden="true">404</div>
				<h1 class="error-404__title"><?php esc_html_e( 'Oops! Page not found', 'noorifa' ); ?></h1>
				<p class="error-404__description">
					<?php esc_html_e( 'The page you’re looking for doesn’t exist or may have been moved. Try searching, or head back to safe ground.', 'noorifa' ); ?>
				</p>

				<div class="error-404__search">
					<?php get_search_form(); ?>
				</div>

				<div class="error-404__actions">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="error-404__btn">
						<?php esc_html_e( 'Back to Home', 'noorifa' ); ?>
					</a>
					<?php if ( $shop_url ) : ?>
						<a href="<?php echo esc_url( $shop_url ); ?>" class="error-404__btn error-404__btn--outline">
							<?php esc_html_e( 'Continue Shopping', 'noorifa' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
</div>

<?php
get_footer();
