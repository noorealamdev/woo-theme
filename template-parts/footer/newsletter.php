<?php
/**
 * Footer builder element: newsletter signup column. Only ever called by
 * footer.php's own loop over Layout::footer_top_items() — visibility is
 * simply whether 'newsletter' is present in that list, so there's no
 * separate visibility check needed here.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings   = ecombon_settings();
$provider   = $settings['newsletter_provider'];
$embed_code = $settings['newsletter_embed_code'];
?>
<div class="col-md-6 col-lg-4" style="order: <?php echo esc_attr( (string) ( $args['order'] ?? 0 ) ); ?>;">
	<div class="footer-col-block footer-wrap-3 mb-0">
		<p class="footer-heading footer-heading-mobile"><?php echo esc_html( $settings['footer_newsletter_heading'] ); ?></p>
		<div class="collapse-content">
			<p class="footer-desc cl-text-2"><?php echo esc_html( $settings['footer_newsletter_description'] ); ?></p>
			<?php if ( 'custom' === $provider && $embed_code ) : ?>
				<?php
				// Deliberately unfiltered — see Schema::sanitize_value()'s
				// 'raw_html' case for why (manage_options-gated third-party
				// embed markup, e.g. Mailchimp/MailerLite).
				echo $embed_code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/footer/newsletter-form' ); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
