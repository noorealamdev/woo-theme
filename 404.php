<?php
/**
 * The template for 404 (not found) responses.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div id="primary" class="site-main">
	<div class="content-area content-area--single">
		<div class="content-area__main">
			<section class="error-404">
				<h1 class="error-404__title"><?php esc_html_e( 'Page not found', 'ecombon' ); ?></h1>
				<p class="error-404__description">
					<?php esc_html_e( 'The page you were looking for could not be found. It might have been moved or no longer exists.', 'ecombon' ); ?>
				</p>

				<?php get_search_form(); ?>

				<p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="error-404__home-link">
						<?php esc_html_e( 'Back to homepage', 'ecombon' ); ?>
					</a>
				</p>
			</section>
		</div>
	</div>
</div>

<?php
get_footer();
