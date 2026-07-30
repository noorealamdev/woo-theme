<?php
/**
 * The shop / product taxonomy archive template.
 *
 * Full override of WooCommerce's default archive-product.php to match the
 * theme's own shop layout (page-title banner, filter/sort control bar, grid).
 *
 * Kept in sync with WC core's own hooks (see Ecombon\Hooks\TemplateHooks
 * for the default-callback removals that keep this override's own UI from
 * duplicating what those hooks would otherwise render).
 *
 * @package Ecombon
 * @version 8.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/**
 * Real hook — WC_Structured_Data::generate_website_data() (SEO JSON-LD) is
 * the only default callback left on it (the wrapper-div and breadcrumb
 * callbacks are removed in Ecombon\Hooks\TemplateHooks, since this page has
 * its own wrapper markup and breadcrumb already) — kept so extensions that
 * hook here for anything else (banners, notices) still work.
 */
do_action( 'woocommerce_before_main_content' );

get_template_part( 'template-parts/shop/page-title' );

/**
 * Real hook — its only default callback (the taxonomy archive description)
 * is removed in Ecombon\Hooks\TemplateHooks since page-title.php above
 * already shows it; kept open for extensions.
 */
do_action( 'woocommerce_shop_loop_header' );
?>

<div class="flat-spacing">
	<div class="container">
		<?php get_template_part( 'template-parts/shop/control-bar' ); ?>

		<?php
		/**
		 * Real hook — its result-count/ordering-dropdown default callbacks
		 * are removed in Ecombon\Hooks\TemplateHooks since control-bar.php
		 * above already provides that UI; woocommerce_output_all_notices
		 * is kept, since nothing else on this page shows WC notices.
		 */
		do_action( 'woocommerce_before_shop_loop' );
		?>

		<?php if ( have_posts() ) : ?>
			<div class="wrapper-shop grid-layout tf-col-4" id="gridLayout">
				<?php
				while ( have_posts() ) :
					the_post();
					global $product;
					do_action( 'woocommerce_shop_loop' );
					get_template_part( 'template-parts/product/card-product', null, array( 'product' => $product ) );
				endwhile;
				?>

				<?php get_template_part( 'template-parts/shop/pagination' ); ?>
			</div>
		<?php else : ?>
			<?php
			get_template_part( 'template-parts/content/content-none' );

			/**
			 * Real hook — its default "no products found" message callback
			 * is removed in Ecombon\Hooks\TemplateHooks since content-none.php
			 * above already shows one; kept open for extensions.
			 */
			do_action( 'woocommerce_no_products_found' );
			?>
		<?php endif; ?>

		<?php
		/**
		 * Real hook — its default pagination callback is removed in
		 * Ecombon\Hooks\TemplateHooks since pagination.php above already
		 * provides that UI; kept open for extensions.
		 */
		do_action( 'woocommerce_after_shop_loop' );
		?>
	</div>
</div>

<?php
do_action( 'woocommerce_after_main_content' );
do_action( 'woocommerce_sidebar' );

get_footer();
