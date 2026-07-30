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
	<?php
	/*
	 * Real <h2>, not <h1> — every real usage site (archive.php, search.php,
	 * woocommerce/archive-product.php) already renders its own page <h1>
	 * via template-parts/global/page-title.php above this; a second <h1>
	 * here would be a duplicate. `.h1` keeps the existing visual size (see
	 * main.css).
	 */
	?>
	<h2 class="no-results__title h1"><?php esc_html_e( 'Nothing found', 'ecombon' ); ?></h2>

	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'Sorry, nothing matched your search. Try again with a different term.', 'ecombon' ); ?></p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'It looks like there is nothing here yet.', 'ecombon' ); ?></p>
	<?php endif; ?>
</section>
