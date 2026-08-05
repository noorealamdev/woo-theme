<?php
/**
 * Real-time search results panel, rendered inside the header search modal.
 * Only ever called from Noorifa\Search\LiveSearch's AJAX handler, which
 * `ob_start()`s this same way Noorifa\WooCommerce\CartFragments does for
 * the mini-cart drawer. $args keys must be read explicitly (this WP
 * install doesn't extract() get_template_part()'s $args): 'term',
 * 'product_query' (WP_Query|null), 'product_total' (int),
 * 'content_query' (WP_Query).
 *
 * @package Noorifa
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
<div class="search-results">
	<?php if ( ! $has_products && ! $has_content ) : ?>
		<div class="search-results_empty">
			<?php
			printf(
				/* translators: %s: search term. */
				esc_html__( 'No results found for "%s".', 'noorifa' ),
				esc_html( $term )
			);
			?>
		</div>
	<?php else : ?>
		<?php if ( $has_products ) : ?>
			<div class="search-results_group">
				<p class="search-results_label"><?php esc_html_e( 'Products', 'noorifa' ); ?></p>
				<div class="search-results_list">
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
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="search-result-item">
							<span class="search-result-image">
								<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?>
							</span>
							<span class="search-result-info">
								<span class="search-result-name text-line-clamp-1"><?php echo esc_html( get_the_title() ); ?></span>
								<span class="search-result-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
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
			<div class="search-results_group">
				<p class="search-results_label"><?php esc_html_e( 'Pages & Articles', 'noorifa' ); ?></p>
				<div class="search-results_list search-results_list--compact">
					<?php
					while ( $content_query->have_posts() ) :
						$content_query->the_post();
						?>
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="search-result-item search-result-item--compact">
							<span class="search-result-name text-line-clamp-1"><?php echo esc_html( get_the_title() ); ?></span>
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
				class="search-results_view-all link"
			>
				<?php
				printf(
					/* translators: 1: number of matching products, 2: search term. */
					esc_html( _n( 'View %1$d result for "%2$s"', 'View all %1$d results for "%2$s"', $product_total, 'noorifa' ) ),
					(int) $product_total,
					esc_html( $term )
				);
				?>
			</a>
		<?php endif; ?>
	<?php endif; ?>
</div>
