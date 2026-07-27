<?php
/**
 * Reusable WooCommerce product card ("card-product" markup).
 *
 * Expects a `$product` (WC_Product) passed via get_template_part()'s
 * $args. Wishlist / compare / quick view stay inert links — those are
 * Core Plugin modules, not theme business logic.
 *
 * @package Ecombon
 *
 * @var WC_Product $product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = $args['product'] ?? null;

if ( empty( $product ) || ! $product instanceof \WC_Product ) {
	return;
}

$gallery_ids = $product->get_gallery_image_ids();
$hover_id    = ! empty( $gallery_ids ) ? $gallery_ids[0] : 0;

$is_new  = ( time() - get_post_time( 'U', true, $product->get_id() ) ) < 14 * DAY_IN_SECONDS;
$is_sale = $product->is_on_sale();

$rating_count = $product->get_rating_count();
$average      = $product->get_average_rating();

$color_variations = array();
if ( $product->is_type( 'variable' ) ) {
	$color_attribute = null;
	foreach ( array_keys( $product->get_attributes() ) as $attribute_key ) {
		if ( false !== strpos( $attribute_key, 'color' ) || false !== strpos( $attribute_key, 'colour' ) ) {
			$color_attribute = $attribute_key;
			break;
		}
	}

	if ( $color_attribute ) {
		$seen = array();
		foreach ( $product->get_available_variations() as $variation_data ) {
			$term_slug = $variation_data['attributes'][ 'attribute_' . $color_attribute ] ?? '';
			if ( ! $term_slug || isset( $seen[ $term_slug ] ) ) {
				continue;
			}
			$seen[ $term_slug ] = true;

			$term  = taxonomy_exists( $color_attribute ) ? get_term_by( 'slug', $term_slug, $color_attribute ) : null;
			$label = $term ? $term->name : $term_slug;
			$image = $variation_data['image']['thumb_src'] ?? wc_placeholder_img_src();

			$color_variations[] = array(
				'label' => $label,
				'image' => $image,
			);
		}
	}
}
?>
<div class="card-product">
	<div class="card-product_wrapper">
		<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="product-img">
			<?php echo wp_kses_post( $product->get_image( 'medium_large', array( 'class' => 'img-product' ) ) ); ?>
			<?php if ( $hover_id ) : ?>
				<?php echo wp_kses_post( wp_get_attachment_image( $hover_id, 'medium_large', false, array( 'class' => 'img-hover' ) ) ); ?>
			<?php endif; ?>
		</a>

		<ul class="product-action_list">
			<li class="wishlist">
				<a href="#" class="hover-tooltip tooltip-left box-icon">
					<span class="icon icon-heart"></span>
					<span class="tooltip"><?php esc_html_e( 'Add to Wishlist', 'ecombon' ); ?></span>
				</a>
			</li>
		</ul>

		<?php if ( $is_new || $is_sale ) : ?>
			<ul class="product-badge_list">
				<?php if ( $is_sale && $product->get_regular_price() ) : ?>
					<?php
					$percent = round( ( ( (float) $product->get_regular_price() - (float) $product->get_sale_price() ) / (float) $product->get_regular_price() ) * 100 );
					?>
					<li class="product-badge_item text-caption-01 sale">
						<?php echo esc_html( sprintf( '-%d%%', $percent ) ); ?>
					</li>
				<?php elseif ( $is_new ) : ?>
					<li class="product-badge_item text-caption-01 new"><?php esc_html_e( 'NEW', 'ecombon' ); ?></li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>

		<div class="product-action_bot">
			<a
				href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
				data-quantity="1"
				data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
				class="tf-btn btn-white small w-100 <?php echo esc_attr( $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button ajax_add_to_cart' : '' ); ?>"
			>
				<?php echo esc_html( $product->add_to_cart_text() ); ?>
			</a>
		</div>
	</div>

	<div class="card-product_info">
		<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="name-product lh-24 fw-medium link-underline-text">
			<?php echo wp_kses_post( $product->get_name() ); ?>
		</a>

		<?php if ( $rating_count > 0 ) : ?>
			<div class="star-wrap d-flex align-items-center">
				<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
					<i class="icon <?php echo esc_attr( $star <= round( $average ) ? 'icon-Star' : 'icon-Star-thin' ); ?>"></i>
				<?php endfor; ?>
			</div>
		<?php endif; ?>

		<div class="price-wrap">
			<?php if ( $is_sale ) : ?>
				<span class="price-new text-primary fw-semibold"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_sale_price() ) ) ) ); ?></span>
				<span class="price-old text-caption-01 cl-text-3"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) ) ); ?></span>
			<?php else : ?>
				<span class="price-new text-primary fw-semibold"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product ) ) ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</div>
