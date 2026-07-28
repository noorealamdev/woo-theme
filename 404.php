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

get_template_part(
	'template-parts/global/page-title',
	null,
	array(
		'title'    => __( 'Page not found', 'ecombon' ),
		'subtitle' => __( 'The page you were looking for could not be found. It might have been moved or no longer exists.', 'ecombon' ),
	)
);
?>

<div id="primary" class="site-main">
	<div class="content-area content-area--single">
		<div class="content-area__main">
			<section class="error-404">
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
