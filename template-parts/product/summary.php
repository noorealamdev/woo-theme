<?php
/**
 * Single product buy-box: heading, price, variations/add-to-cart,
 * extra links, delivery info, and trust seal.
 *
 * The variations + add-to-cart form is WooCommerce's own real template
 * (via woocommerce_template_single_add_to_cart()) — real stock/price/
 * variation matching, just restyled to match the theme.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$category_list = wc_get_product_category_list( $product->get_id() );
$rating_count  = $product->get_rating_count();
$average       = $product->get_average_rating();
$review_count  = $product->get_review_count();
$contact_url   = get_permalink( get_page_by_path( 'contact-us' ) ?: 0 );
?>
<div class="tf-product-info-wrap position-relative mt-md-0">
	<?php /* assets/js/zoom.js renders the hover-zoom pane into this element. */ ?>
	<div class="tf-zoom-main sticky-top"></div>
	<div class="tf-product-info-list">
		<div class="tf-product-info-heading">
			<?php if ( $category_list ) : ?>
				<p class="product-infor-cate text-caption-01 mb-4"><?php echo wp_kses_post( $category_list ); ?></p>
			<?php endif; ?>

			<h1 class="product-infor-name mb-12"><?php echo wp_kses_post( $product->get_name() ); ?></h1>

			<div class="product-infor-meta mb-20">
				<?php if ( $rating_count > 0 ) : ?>
					<div class="meta_rate">
						<div class="star-wrap normal d-flex align-items-center">
							<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
								<i class="icon <?php echo esc_attr( $star <= round( $average ) ? 'icon-Star' : 'icon-Star-thin' ); ?>"></i>
							<?php endfor; ?>
						</div>
						<span class="text-caption-01 cl-text-2">
							<?php
							printf(
								/* translators: %d: number of reviews. */
								esc_html( _n( '(%d review)', '(%d reviews)', $review_count, 'ecombon' ) ),
								(int) $review_count
							);
							?>
						</span>
					</div>
					<div class="br-line type-vertical"></div>
				<?php endif; ?>
				<?php if ( $product->get_sku() ) : ?>
					<div class="meta_prd_code text-caption-01">
						<span class="cl-text-2"><?php esc_html_e( 'SKU:', 'ecombon' ); ?></span>
						<span><?php echo esc_html( $product->get_sku() ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<div class="product-infor-price mb-12">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>

			<?php if ( $product->get_short_description() ) : ?>
				<div class="product-infor-desc cl-text-2 mb-12">
					<?php echo wp_kses_post( $product->get_short_description() ); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="br-line"></div>

		<div class="tf-product-variant">
			<?php woocommerce_template_single_add_to_cart(); ?>
		</div>

		<div class="tf-product-extra-link">
			<?php /* Compare is a Core Plugin module — left inert until it ships. */ ?>
			<a href="#" class="product-extra-icon link">
				<i class="icon icon-ArrowsLeftRight"></i>
				<?php esc_html_e( 'Compare', 'ecombon' ); ?>
			</a>
			<?php if ( $contact_url ) : ?>
				<a href="<?php echo esc_url( $contact_url ); ?>" class="product-extra-icon link">
					<i class="icon icon-Question"></i>
					<?php esc_html_e( 'Ask A Question', 'ecombon' ); ?>
				</a>
			<?php endif; ?>
			<a href="mailto:?subject=<?php echo esc_attr( rawurlencode( $product->get_name() ) ); ?>&body=<?php echo esc_attr( rawurlencode( get_permalink( $product->get_id() ) ) ); ?>" class="product-extra-icon link">
				<i class="icon icon-ShareNetwork"></i>
				<?php esc_html_e( 'Share', 'ecombon' ); ?>
			</a>
		</div>

		<div class="br-line"></div>

		<?php
		$delivery_note = apply_filters( 'ecombon_product_delivery_note', '' );
		$return_note   = apply_filters( 'ecombon_product_return_note', '' );
		if ( $delivery_note || $return_note ) :
			?>
			<div class="tf-product-delivery-return">
				<?php if ( $delivery_note ) : ?>
					<div class="product-delivery">
						<i class="icon icon-Timer"></i>
						<p><?php echo wp_kses_post( $delivery_note ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( $return_note ) : ?>
					<div class="product-delivery return">
						<i class="icon icon-ArrowClockwise"></i>
						<p><?php echo wp_kses_post( $return_note ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<div class="br-line"></div>
		<?php endif; ?>

		<div class="tf-product-trust-seal">
			<p class="h6 text-seal"><?php esc_html_e( 'Guaranteed Safe Checkout:', 'ecombon' ); ?></p>
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
