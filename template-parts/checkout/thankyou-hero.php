<?php
/**
 * Success hero banner shown at the top of the order-received / thank-you page.
 *
 * The heading and subtitle are translatable and filterable so a site can
 * customise the copy (e.g. to Bengali) without editing the template:
 *   - noorifa/thankyou_hero_title
 *   - noorifa/thankyou_hero_subtitle
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$checkout_settings = \Noorifa\Settings\Layout::all()['checkout'] ?? array();

/**
 * Filters the thank-you hero heading. Defaults to the admin Settings value
 * (Theme Settings → Cart & Checkout), which itself defaults to "Thank you".
 *
 * @param string $title Heading from settings.
 */
$title = apply_filters( 'noorifa/thankyou_hero_title', $checkout_settings['thankyou_hero_title'] ?? '' );

/**
 * Filters the thank-you hero subtitle. Defaults to the admin Settings value.
 *
 * @param string $subtitle Subtitle from settings.
 */
$subtitle = apply_filters( 'noorifa/thankyou_hero_subtitle', $checkout_settings['thankyou_hero_subtitle'] ?? '' );
?>
<section class="noorifa-thankyou-hero">
	<span class="noorifa-thankyou-hero__icon" aria-hidden="true">
		<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
	</span>
	<?php if ( $title ) : ?>
		<h1 class="noorifa-thankyou-hero__title"><?php echo esc_html( $title ); ?></h1>
	<?php endif; ?>
	<?php if ( $subtitle ) : ?>
		<p class="noorifa-thankyou-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
	<?php endif; ?>
</section>
