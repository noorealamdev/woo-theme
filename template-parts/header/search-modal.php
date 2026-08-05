<?php
/**
 * Header search modal.
 *
 * The form is a real, working WordPress/WooCommerce search (submits on
 * Enter with no JS required). Noorifa\Search\LiveSearch adds real-time
 * results underneath as the user types — see assets/js/noorifa-search.js
 * and template-parts/header/search-results.php for the actual query/markup.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="modal modalCentered fade modal-search" id="search">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="d-flex align-items-center justify-content-between gap-10">
				<h3><?php esc_html_e( 'Search', 'noorifa' ); ?></h3>
				<span class="icon-close-popup flex-shrink-0" data-bs-dismiss="modal">
					<?php \Noorifa\Setup\Icons::render( 'X2' ); ?>
				</span>
			</div>
			<form role="search" method="get" class="form-search-nav style-2" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<fieldset>
					<input type="text" name="s" placeholder="<?php esc_attr_e( 'Searching…', 'noorifa' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" required>
				</fieldset>
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>
				<button type="submit" class="btn-action">
					<?php \Noorifa\Setup\Icons::render( 'MagnifyingGlass' ); ?>
				</button>
			</form>
			<div class="search-live-results" aria-live="polite"></div>
		</div>
	</div>
</div>
