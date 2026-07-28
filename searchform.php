<?php
/**
 * The search form.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$search_form_id = 'search-form-' . wp_unique_id();
?>
<form role="search" method="get" class="search-form form-search-blog" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $search_form_id ); ?>" class="screen-reader-text">
		<?php esc_html_e( 'Search for:', 'ecombon' ); ?>
	</label>
	<fieldset>
		<input
			type="search"
			id="<?php echo esc_attr( $search_form_id ); ?>"
			class="search-form__field style-stroke-bottom"
			placeholder="<?php esc_attr_e( 'Search…', 'ecombon' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			name="s"
		/>
	</fieldset>
	<button type="submit" class="search-form__submit btn-action link">
		<i class="icon icon-MagnifyingGlass" aria-hidden="true"></i>
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'ecombon' ); ?></span>
	</button>
</form>
