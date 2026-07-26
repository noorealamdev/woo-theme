<?php
/**
 * Shown when a query returns no results.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="no-results">
	<h1 class="no-results__title"><?php esc_html_e( 'Nothing found', 'ecombon' ); ?></h1>

	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'Sorry, nothing matched your search. Try again with a different term.', 'ecombon' ); ?></p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'It looks like there is nothing here yet.', 'ecombon' ); ?></p>
	<?php endif; ?>
</section>
