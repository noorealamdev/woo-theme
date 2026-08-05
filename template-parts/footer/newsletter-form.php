<?php
/**
 * Footer newsletter signup form.
 *
 * Markup only for now — actually subscribing people is a Core Plugin
 * Marketing module concern (needs a provider integration), not theme logic.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form class="form-sub" action="<?php echo esc_url( apply_filters( 'noorifa_newsletter_action', '' ) ); ?>" method="post">
	<fieldset>
		<input type="email" name="email" placeholder="<?php esc_attr_e( 'Enter your e-mail', 'noorifa' ); ?>" required>
	</fieldset>
	<button type="submit" class="btn-action">
		<?php \Noorifa\Setup\Icons::render( 'ArrowUpRight' ); ?>
	</button>
</form>
