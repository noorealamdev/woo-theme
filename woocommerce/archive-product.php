<?php
/**
 * The shop / product taxonomy archive template.
 *
 * Full override of WooCommerce's default archive-product.php to match the
 * Amerce shop layout (page-title banner, filter/sort control bar, grid).
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/shop/page-title' );
?>

<div class="flat-spacing">
	<div class="container">
		<?php get_template_part( 'template-parts/shop/control-bar' ); ?>

		<?php if ( have_posts() ) : ?>
			<div class="wrapper-shop tf-grid-layout tf-col-4" id="gridLayout">
				<?php
				while ( have_posts() ) :
					the_post();
					global $product;
					get_template_part( 'template-parts/product/card-product', null, array( 'product' => $product ) );
				endwhile;
				?>

				<?php get_template_part( 'template-parts/shop/pagination' ); ?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content-none' ); ?>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
