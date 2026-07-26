<?php
/**
 * Single product image gallery: main swiper synced to a thumbnail rail.
 *
 * Uses the real featured image + product gallery images. Lightbox
 * (photoswipe) and zoom (drift) are wired by assets/js/zoom.js, enqueued
 * only on single product pages.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$image_ids = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );

if ( empty( $image_ids ) ) {
	$image_ids = array( 0 );
}

/*
 * zoom.js sizes each thumbnail slot by dividing the rail's fixed height by
 * `data-preview` (Swiper's `slidesPerView`) — the Amerce demo hardcodes 7
 * assuming a 7-image gallery, but real products here usually have far
 * fewer, which left each real thumbnail tiny (sized as if 7 were present).
 * Size it to how many images this product actually has instead, capped at
 * 5 — the same breakpoint value zoom.js itself already falls back to below
 * desktop widths — so a short gallery gets proportionally larger thumbs
 * and a long one still scrolls instead of shrinking further.
 */
$thumb_preview = max( 1, min( 5, count( $image_ids ) ) );
?>
<div class="tf-product-media-wrap sticky-top">
	<div class="product-thumbs-slider style-row row_left">
		<div class="flat-wrap-media-product">
			<div dir="ltr" class="swiper tf-product-media-main" id="gallery-swiper-started" data-spacing="0">
				<div class="swiper-wrapper">
					<?php foreach ( $image_ids as $image_id ) : ?>
						<?php
						$full_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : wc_placeholder_img_src( 'full' );
						$full_meta = $image_id ? wp_get_attachment_metadata( $image_id ) : null;
						$width    = $full_meta['width'] ?? 800;
						$height   = $full_meta['height'] ?? 800;
						?>
						<div class="swiper-slide">
							<a href="<?php echo esc_url( $full_url ); ?>" target="_blank" class="item" data-pswp-width="<?php echo esc_attr( (string) $width ); ?>" data-pswp-height="<?php echo esc_attr( (string) $height ); ?>">
								<?php if ( $image_id ) : ?>
									<?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'tf-image-zoom', 'data-zoom' => $full_url ) ) ); ?>
								<?php else : ?>
									<img loading="lazy" class="tf-image-zoom" data-zoom="<?php echo esc_url( $full_url ); ?>" src="<?php echo esc_url( $full_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
								<?php endif; ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<?php
		/**
		 * assets/js/zoom.js unconditionally initializes a Swiper on
		 * `.tf-product-media-thumbs` whenever `.product-thumbs-slider`
		 * exists — it must always be present (even for a single image),
		 * or the missing element crashes that script.
		 */
		?>
		<div dir="ltr" class="swiper tf-product-media-thumbs other-image-zoom<?php echo count( $image_ids ) <= 1 ? ' d-none' : ''; ?>" data-direction="vertical" data-preview="<?php echo esc_attr( $thumb_preview ); ?>">
			<div class="swiper-wrapper stagger-wrap">
				<?php foreach ( $image_ids as $image_id ) : ?>
					<div class="swiper-slide stagger-item">
						<div class="item">
							<?php if ( $image_id ) : ?>
								<?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'thumbnail' ) ); ?>
							<?php else : ?>
								<img loading="lazy" width="82" height="110" src="<?php echo esc_url( wc_placeholder_img_src( 'thumbnail' ) ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
