<?php
/**
 * Header search modal.
 *
 * The form is a real, working WordPress/WooCommerce search (submits on
 * Enter with no JS required). Ecombon\Search\LiveSearch adds real-time
 * results underneath as the user types — see assets/js/ecombon-search.js
 * and template-parts/header/search-results.php for the actual query/markup.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="modal modalCentered fade modal-search" id="search">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="d-flex align-items-center justify-content-between gap-10">
				<h3><?php esc_html_e( 'Search', 'ecombon' ); ?></h3>
				<span class="icon-close-popup flex-shrink-0" data-bs-dismiss="modal">
					<i class="icon-X2"></i>
				</span>
			</div>
			<form role="search" method="get" class="form-search-nav style-2" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<fieldset>
					<input type="text" name="s" placeholder="<?php esc_attr_e( 'Searching…', 'ecombon' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" required>
				</fieldset>
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>
				<button type="submit" class="btn-action">
					<i class="icon icon-MagnifyingGlass"></i>
				</button>
			</form>
			<div class="tf-search-live-results" aria-live="polite"></div>
		</div>
	</div>
</div>
