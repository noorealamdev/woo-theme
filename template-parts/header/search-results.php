<?php
/**
 * Real-time search results panel, rendered inside the header search modal.
 * Only ever called from Ecombon\Search\LiveSearch's AJAX handler, which
 * `ob_start()`s this same way Ecombon\WooCommerce\CartFragments does for
 * the mini-cart drawer. $args keys must be read explicitly (this WP
 * install doesn't extract() get_template_part()'s $args): 'term',
 * 'product_query' (WP_Query|null), 'product_total' (int),
 * 'content_query' (WP_Query).
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term           = isset( $args['term'] ) ? $args['term'] : '';
$product_query  = isset( $args['product_query'] ) ? $args['product_query'] : null;
$product_total  = isset( $args['product_total'] ) ? (int) $args['product_total'] : 0;
$content_query  = isset( $args['content_query'] ) ? $args['content_query'] : null;

$has_products = $product_query instanceof WP_Query && $product_query->have_posts();
$has_content  = $content_query instanceof WP_Query && $content_query->have_posts();
?>
<div class="tf-search-results">
	<?php if ( ! $has_products && ! $has_content ) : ?>
		<div class="tf-search-results_empty">
			<?php
			printf(
				/* translators: %s: search term. */
				esc_html__( 'No results found for "%s".', 'ecombon' ),
				esc_html( $term )
			);
			?>
		</div>
	<?php else : ?>
		<?php if ( $has_products ) : ?>
			<div class="tf-search-results_group">
				<p class="tf-search-results_label"><?php esc_html_e( 'Products', 'ecombon' ); ?></p>
				<div class="tf-search-results_list">
					<?php
					while ( $product_query->have_posts() ) :
						$product_query->the_post();
						global $product;
						if ( ! $product instanceof WC_Product ) {
							$product = wc_get_product( get_the_ID() );
						}
						if ( ! $product ) {
							continue;
						}
						?>
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="tf-search-result-item">
							<span class="tf-search-result-image">
								<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?>
							</span>
							<span class="tf-search-result-info">
								<span class="tf-search-result-name text-line-clamp-1"><?php echo esc_html( get_the_title() ); ?></span>
								<span class="tf-search-result-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
							</span>
						</a>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $has_content ) : ?>
			<div class="tf-search-results_group">
				<p class="tf-search-results_label"><?php esc_html_e( 'Pages & Articles', 'ecombon' ); ?></p>
				<div class="tf-search-results_list tf-search-results_list--compact">
					<?php
					while ( $content_query->have_posts() ) :
						$content_query->the_post();
						?>
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="tf-search-result-item tf-search-result-item--compact">
							<span class="tf-search-result-name text-line-clamp-1"><?php echo esc_html( get_the_title() ); ?></span>
						</a>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $has_products ) : ?>
			<a
				href="<?php echo esc_url( add_query_arg( array( 's' => $term, 'post_type' => 'product' ), home_url( '/' ) ) ); ?>"
				class="tf-search-results_view-all link"
			>
				<?php
				printf(
					/* translators: 1: number of matching products, 2: search term. */
					esc_html( _n( 'View %1$d result for "%2$s"', 'View all %1$d results for "%2$s"', $product_total, 'ecombon' ) ),
					(int) $product_total,
					esc_html( $term )
				);
				?>
			</a>
		<?php endif; ?>
	<?php endif; ?>
</div>
