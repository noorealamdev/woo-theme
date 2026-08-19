<?php
/**
 * Popup builder output — every enabled popup whose targeting rules match
 * the current request (see `Layout::popups_for_display()`). Markup is
 * always rendered server-side (content included) but stays `hidden` until
 * `assets/js/popups.js` decides its trigger condition has fired, so there's
 * no flash of unstyled/positioned content and no JSON round-trip of
 * user-authored HTML.
 *
 * Rendered from footer.php's always-output area, same as the floating
 * button, so popups still appear on distraction-free products that hide
 * the header/footer.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Noorifa\Settings\Layout;

$popups = Layout::popups_for_display();

if ( ! $popups ) {
	return;
}
?>
<div id="noorifa-popups">
	<?php foreach ( $popups as $popup ) : ?>
		<?php
		$position = $popup['position'];
		$is_bar   = in_array( $position, array( 'top-bar', 'bottom-bar' ), true );

		$classes = array(
			'noorifa-popup',
			'noorifa-popup--' . $position,
			'noorifa-popup--size-' . $popup['size'],
			'noorifa-popup--anim-' . $popup['animation'],
		);

		$style = sprintf(
			'--noorifa-popup-bg:%s;--noorifa-popup-text:%s;--noorifa-popup-radius:%dpx;--noorifa-popup-overlay:%s;',
			esc_attr( $popup['background_color'] ),
			esc_attr( $popup['text_color'] ),
			(int) $popup['border_radius'],
			esc_attr( $popup['overlay_color'] )
		);
		?>
		<div
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			id="noorifa-popup-<?php echo esc_attr( $popup['id'] ); ?>"
			data-popup-id="<?php echo esc_attr( $popup['id'] ); ?>"
			data-trigger="<?php echo esc_attr( $popup['trigger_type'] ); ?>"
			data-delay="<?php echo esc_attr( $popup['trigger_delay_seconds'] ); ?>"
			data-scroll="<?php echo esc_attr( $popup['trigger_scroll_percent'] ); ?>"
			data-selector="<?php echo esc_attr( $popup['trigger_click_selector'] ); ?>"
			data-frequency="<?php echo esc_attr( $popup['frequency'] ); ?>"
			data-frequency-days="<?php echo esc_attr( $popup['frequency_days'] ); ?>"
			data-device="<?php echo esc_attr( $popup['device'] ); ?>"
			style="<?php echo esc_attr( $style ); ?>"
			role="dialog"
			aria-modal="true"
			aria-hidden="true"
			hidden
		>
			<?php if ( $popup['overlay_enabled'] && ! $is_bar ) : ?>
				<div class="noorifa-popup__overlay" data-popup-close></div>
			<?php endif; ?>
			<div class="noorifa-popup__box">
				<?php if ( 'none' !== $popup['close_style'] ) : ?>
					<button
						type="button"
						class="noorifa-popup__close noorifa-popup__close--<?php echo esc_attr( $popup['close_style'] ); ?>"
						data-popup-close
						aria-label="<?php esc_attr_e( 'Close', 'noorifa' ); ?>"
					>
						<?php echo 'text' === $popup['close_style'] ? esc_html__( 'Close', 'noorifa' ) : '&times;'; ?>
					</button>
				<?php endif; ?>
				<div class="noorifa-popup__content">
					<?php if ( 'html' === $popup['content_mode'] ) : ?>
						<?php
						// Deliberately unsanitized on output — already stored
						// unsanitized on purpose (see `custom_html`'s field
						// declaration in Schema.php) under the same trust
						// model as Custom Code CSS/JS.
						echo $popup['custom_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					<?php else : ?>
						<?php if ( $popup['image'] ) : ?>
							<img class="noorifa-popup__image" src="<?php echo esc_url( $popup['image'] ); ?>" alt="" />
						<?php endif; ?>
						<?php if ( $popup['heading'] ) : ?>
							<h3 class="noorifa-popup__heading"><?php echo esc_html( $popup['heading'] ); ?></h3>
						<?php endif; ?>
						<?php if ( $popup['subheading'] ) : ?>
							<p class="noorifa-popup__subheading"><?php echo esc_html( $popup['subheading'] ); ?></p>
						<?php endif; ?>
						<?php if ( $popup['body'] ) : ?>
							<div class="noorifa-popup__body"><?php echo wp_kses_post( $popup['body'] ); ?></div>
						<?php endif; ?>
						<?php if ( $popup['button_label'] && $popup['button_url'] ) : ?>
							<a
								class="noorifa-popup__button"
								href="<?php echo esc_url( $popup['button_url'] ); ?>"
								<?php echo $popup['button_new_tab'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
							>
								<?php echo esc_html( $popup['button_label'] ); ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
