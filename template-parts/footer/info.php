<?php
/**
 * Footer builder element: a logo/description info card, plus social links.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings    = noorifa_settings();
$logo        = $settings['footer_info_logo'];
$description = $settings['footer_info_description'];
$socials     = apply_filters( 'noorifa_social_links', array() );

$social_icons = array(
	'facebook'  => 'FacebookLogo',
	'x'         => 'XLogo',
	'instagram' => 'InstagramLogo',
	'tiktok'    => 'TiktokLogo',
	'snapchat'  => 'SnapchatLogo',
	'pinterest' => 'PinterestLogo',
	'youtube'   => 'YoutubeLogo',
);
?>
<div class="col-md-6 col-lg-4" style="order: <?php echo esc_attr( (string) ( $args['order'] ?? 0 ) ); ?>;">
<div class="footer-infor d-flex flex-column align-items-start mb-lg-0">
	<?php if ( $logo ) : ?>
		<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="footer-logo-custom mb-16" />
	<?php elseif ( has_custom_logo() ) : ?>
		<div class="logo-site mb-16">
			<?php the_custom_logo(); ?>
		</div>
	<?php else : ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-site mb-16">
			<?php bloginfo( 'name' ); ?>
		</a>
	<?php endif; ?>
	<?php if ( $description ) : ?>
		<p class="footer-infor-desc lh-26 cl-text-2"><?php echo wp_kses_post( $description ); ?></p>
	<?php endif; ?>
	<?php if ( ! empty( $socials ) ) : ?>
		<ul class="social-icon-2">
			<?php foreach ( $socials as $network => $url ) : ?>
				<?php if ( empty( $social_icons[ $network ] ) ) { continue; } ?>
				<li>
					<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
						<?php \Noorifa\Setup\Icons::render( $social_icons[ $network ] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
</div>
