<?php
/**
 * GDPR-style cookie consent notice — a single site-wide banner (not a
 * repeatable list like the popup builder; see Noorifa\Settings\Layout's
 * `privacy.*` fields). Informational only: accepting it just remembers the
 * choice for `cookie_notice_duration_days` and hides the banner — it
 * doesn't gate any other script (Google Analytics/Facebook Pixel keep
 * loading from Integrations regardless, same as before this existed).
 *
 * Rendered from footer.php's always-output area, same as the floating
 * button and popups, so it still appears on distraction-free products that
 * hide the header/footer.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Noorifa\Settings\Layout;

$privacy = Layout::all()['privacy'] ?? array();

if ( empty( $privacy['cookie_notice_enabled'] ) ) {
	return;
}

$heading     = trim( (string) ( $privacy['cookie_notice_heading'] ?? '' ) );
$message     = trim( (string) ( $privacy['cookie_notice_message'] ?? '' ) );
$button      = trim( (string) ( $privacy['cookie_notice_button_label'] ?? '' ) );
$policy_url  = trim( (string) ( $privacy['cookie_notice_policy_url'] ?? '' ) );
$policy_text = trim( (string) ( $privacy['cookie_notice_policy_link_text'] ?? '' ) );
$position    = $privacy['cookie_notice_position'] ?? Layout::COOKIE_NOTICE_POSITION_DEFAULT;
$duration    = (int) ( $privacy['cookie_notice_duration_days'] ?? Layout::COOKIE_NOTICE_DURATION_DEFAULT );

$style = sprintf(
	'--noorifa-cookie-bg:%s;--noorifa-cookie-text:%s;',
	esc_attr( $privacy['cookie_notice_background_color'] ?? Layout::COOKIE_NOTICE_BG_DEFAULT ),
	esc_attr( $privacy['cookie_notice_text_color'] ?? Layout::COOKIE_NOTICE_TEXT_DEFAULT )
);
?>
<div
	id="noorifa-cookie-notice"
	class="noorifa-cookie-notice noorifa-cookie-notice--<?php echo esc_attr( $position ); ?>"
	data-duration-days="<?php echo esc_attr( $duration ); ?>"
	style="<?php echo esc_attr( $style ); ?>"
	role="region"
	aria-label="<?php esc_attr_e( 'Cookie notice', 'noorifa' ); ?>"
	hidden
>
	<div class="noorifa-cookie-notice__box">
		<div class="noorifa-cookie-notice__content">
			<?php if ( $heading ) : ?>
				<p class="noorifa-cookie-notice__heading"><?php echo esc_html( $heading ); ?></p>
			<?php endif; ?>
			<?php if ( $message ) : ?>
				<p class="noorifa-cookie-notice__message">
					<?php echo wp_kses_post( $message ); ?>
					<?php if ( $policy_url && $policy_text ) : ?>
						<a href="<?php echo esc_url( $policy_url ); ?>" class="noorifa-cookie-notice__link"><?php echo esc_html( $policy_text ); ?></a>.
					<?php endif; ?>
				</p>
			<?php endif; ?>
		</div>
		<?php if ( $button ) : ?>
			<button type="button" class="noorifa-cookie-notice__accept" data-cookie-accept>
				<?php echo esc_html( $button ); ?>
			</button>
		<?php endif; ?>
	</div>
</div>
