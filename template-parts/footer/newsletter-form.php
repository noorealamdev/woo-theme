<?php
/**
 * Footer newsletter signup form.
 *
 * Markup only for now — actually subscribing people is a Core Plugin
 * Marketing module concern (needs a provider integration), not theme logic.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form class="form-sub" action="<?php echo esc_url( apply_filters( 'ecombon_newsletter_action', '' ) ); ?>" method="post">
	<fieldset>
		<input type="email" name="email" placeholder="<?php esc_attr_e( 'Enter your e-mail', 'ecombon' ); ?>" required>
	</fieldset>
	<button type="submit" class="btn-action">
		<?php \Ecombon\Setup\Icons::render( 'ArrowUpRight' ); ?>
	</button>
</form>
<p class="text-remember cl-text-2">
	<?php
	printf(
		/* translators: 1: Terms of Service link, 2: Privacy Policy link. */
		esc_html__( 'By clicking subscribe, you agree to the %1$s and %2$s.', 'ecombon' ),
		'<a href="' . esc_url( home_url( '/terms-and-conditions/' ) ) . '" class="text-main link link-underline">' . esc_html__( 'Terms of Service', 'ecombon' ) . '</a>',
		'<a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '" class="text-main link link-underline">' . esc_html__( 'Privacy Policy', 'ecombon' ) . '</a>'
	);
	?>
</p>
