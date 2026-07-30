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

?>
<div class="product-media-wrap sticky-top">
	<div class="product-thumbs-slider style-row row_left">
		<div class="flat-wrap-media-product">
			<div dir="ltr" class="swiper product-media-main" id="gallery-swiper-started" data-spacing="0">
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
									<?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'image-zoom', 'data-zoom' => $full_url ) ) ); ?>
								<?php else : ?>
									<img loading="lazy" class="image-zoom" data-zoom="<?php echo esc_url( $full_url ); ?>" src="<?php echo esc_url( $full_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
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
		 * `.product-media-thumbs` whenever `.product-thumbs-slider`
		 * exists — it must always be present (even for a single image),
		 * or the missing element crashes that script.
		 */
		?>
		<?php
		/**
		 * No `stagger-wrap`/`stagger-item` here — those classes drive
		 * main.js's staggerWrap(), a scale/rotate/skew "spin in" entrance
		 * animation per thumbnail. Applied to a real product gallery, it
		 * made thumbnails visibly jump around in size for the first ~600ms
		 * after load (its mid-transition bounding box is nothing like its
		 * settled size), which read as a layout bug rather than a nice
		 * reveal.
		 */
		?>
		<?php
		/**
		 * `data-preview="auto"` (Swiper's `slidesPerView: 'auto'`) sizes
		 * each thumbnail by its own real CSS size (see the square
		 * `aspect-ratio` rule in assets/css/main.css)
		 * instead of dividing the rail's rendered height evenly by a fixed
		 * slide count, which stretched real thumbnails into tall,
		 * distorted rectangles whenever a product had fewer images than
		 * that fixed count.
		 */
		?>
		<div dir="ltr" class="swiper product-media-thumbs other-image-zoom<?php echo count( $image_ids ) <= 1 ? ' d-none' : ''; ?>" data-direction="vertical" data-preview="auto">
			<div class="swiper-wrapper">
				<?php foreach ( $image_ids as $image_id ) : ?>
					<div class="swiper-slide">
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
