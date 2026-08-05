<?php
/**
 * The search form.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$search_form_id = 'search-form-' . wp_unique_id();
?>
<form role="search" method="get" class="search-form form-search-blog" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $search_form_id ); ?>" class="screen-reader-text">
		<?php esc_html_e( 'Search for:', 'noorifa' ); ?>
	</label>
	<fieldset>
		<input
			type="search"
			id="<?php echo esc_attr( $search_form_id ); ?>"
			class="search-form__field style-stroke-bottom"
			placeholder="<?php esc_attr_e( 'Search…', 'noorifa' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			name="s"
		/>
	</fieldset>
	<button type="submit" class="search-form__submit btn-action link">
		<?php \Noorifa\Setup\Icons::render( 'MagnifyingGlass' ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'noorifa' ); ?></span>
	</button>
</form>
