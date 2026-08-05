<?php
/**
 * Single product buy-box: heading, price, variations/add-to-cart,
 * extra links, delivery info, and trust seal.
 *
 * The variations + add-to-cart form is WooCommerce's own real template
 * (via woocommerce_template_single_add_to_cart()) — real stock/price/
 * variation matching, just restyled to match the theme.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$category_list = wc_get_product_category_list( $product->get_id() );
$rating_count  = $product->get_rating_count();
$average       = $product->get_average_rating();
$review_count  = $product->get_review_count();
?>
<div class="product-info-wrap position-relative mt-md-0">
	<?php /* assets/js/zoom.js renders the hover-zoom pane into this element. */ ?>
	<div class="zoom-main sticky-top"></div>
	<div class="product-info-list">
		<div class="product-info-heading">
			<?php if ( $category_list ) : ?>
				<p class="product-infor-cate text-caption-01 mb-4"><?php echo wp_kses_post( $category_list ); ?></p>
			<?php endif; ?>

			<?php // Real <h1> — the product's main heading; its own font-size is set directly in main.css (26px, not the shared .h3 clamp). ?>
			<h1 class="product-infor-name mb-12"><?php echo wp_kses_post( $product->get_name() ); ?></h1>

			<div class="product-infor-meta mb-20">
				<?php if ( $rating_count > 0 ) : ?>
					<div class="meta_rate">
						<div class="star-wrap normal d-flex align-items-center">
							<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
								<?php \Noorifa\Setup\Icons::render( $star <= round( $average ) ? 'Star' : 'Star-thin' ); ?>
							<?php endfor; ?>
						</div>
						<span class="text-caption-01 cl-text-2">
							<?php
							printf(
								/* translators: %d: number of reviews. */
								esc_html( _n( '(%d review)', '(%d reviews)', $review_count, 'noorifa' ) ),
								(int) $review_count
							);
							?>
						</span>
					</div>
					<div class="br-line type-vertical"></div>
				<?php endif; ?>
				<?php if ( $product->get_sku() ) : ?>
					<div class="meta_prd_code text-caption-01">
						<span class="cl-text-2"><?php esc_html_e( 'SKU:', 'noorifa' ); ?></span>
						<span><?php echo esc_html( $product->get_sku() ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<?php
			// A clean before/after/percentage-off display needs single,
			// unambiguous regular and sale prices — true for a simple
			// product, not for a variable product's own parent-level
			// price fields (which are empty; each variation has its own).
			// Those fall back to WooCommerce's own real get_price_html()
			// output below, already restyled to match in
			// assets/css/main.css.
			$regular_price = $product->get_regular_price();
			$sale_price    = $product->get_sale_price();
			$show_badge    = $product->is_on_sale() && '' !== $regular_price && '' !== $sale_price && (float) $regular_price > 0;
			?>
			<div class="product-infor-price mb-12">
				<?php if ( $show_badge ) : ?>
					<h4 class="price-on-sale"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product ) ) ); ?></h4>
					<div class="br-line type-vertical"></div>
					<p class="cl-text-3 text-decoration-line-through"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product, array( 'price' => $regular_price ) ) ) ); ?></p>
					<span class="badge-sale text-white fw-semibold text-caption-02">
						<?php
						$percent_off = round( ( ( (float) $regular_price - (float) $sale_price ) / (float) $regular_price ) * 100 );
						printf( '-%s%%', esc_html( $percent_off ) );
						?>
					</span>
				<?php else : ?>
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
				<?php endif; ?>
			</div>

			<?php if ( $product->get_short_description() ) : ?>
				<div class="product-infor-desc cl-text-2 mb-12">
					<?php echo wp_kses_post( $product->get_short_description() ); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="br-line"></div>

		<div class="product-variant">
			<?php woocommerce_template_single_add_to_cart(); ?>
		</div>

		<?php
		$delivery_note = apply_filters( 'noorifa_product_delivery_note', '' );
		$return_note   = apply_filters( 'noorifa_product_return_note', '' );
		if ( $delivery_note || $return_note ) :
			?>
			<div class="product-delivery-return">
				<?php if ( $delivery_note ) : ?>
					<div class="product-delivery">
						<?php \Noorifa\Setup\Icons::render( 'Timer' ); ?>
						<p><?php echo wp_kses_post( $delivery_note ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( $return_note ) : ?>
					<div class="product-delivery return">
						<?php \Noorifa\Setup\Icons::render( 'ArrowClockwise' ); ?>
						<p><?php echo wp_kses_post( $return_note ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<div class="br-line"></div>
		<?php endif; ?>

		<div class="product-trust-seal">
			<p class="h6 text-seal"><?php esc_html_e( 'Guaranteed Safe Checkout:', 'noorifa' ); ?></p>
			<?php
			get_template_part(
				'template-parts/footer/payment-icons',
				null,
				array(
					'list_class' => 'list-card',
					'item_class' => 'card-item',
				)
			);
			?>
		</div>
	</div>
</div>
