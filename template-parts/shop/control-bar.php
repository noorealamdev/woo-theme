<?php
/**
 * Shop control bar: filter trigger, real sort links, result count and
 * active-filter chips.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

global $wp_query;

$sort_options = array(
	'menu_order' => __( 'Default sorting', 'noorifa' ),
	'popularity' => __( 'Best Selling', 'noorifa' ),
	'rating'     => __( 'Average Rating', 'noorifa' ),
	'date'       => __( 'Latest', 'noorifa' ),
	'price'      => __( 'Price, low to high', 'noorifa' ),
	'price-desc' => __( 'Price, high to low', 'noorifa' ),
	'a-z'        => __( 'Alphabetically, A-Z', 'noorifa' ),
	'z-a'        => __( 'Alphabetically, Z-A', 'noorifa' ),
);

$current_orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! isset( $sort_options[ $current_orderby ] ) ) {
	$current_orderby = 'menu_order';
}

$active_chips = array();
if ( isset( $_GET['min_price'] ) || isset( $_GET['max_price'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_chips['price'] = array(
		/* translators: 1: min price, 2: max price. */
		'label' => sprintf( __( 'Price: $%1$s–$%2$s', 'noorifa' ), sanitize_text_field( wp_unslash( $_GET['min_price'] ?? '' ) ), sanitize_text_field( wp_unslash( $_GET['max_price'] ?? '' ) ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		'url'   => remove_query_arg( array( 'min_price', 'max_price', 'paged' ) ),
	);
}
foreach ( WC_Query::get_layered_nav_chosen_attributes() as $taxonomy => $data ) {
	if ( empty( $data['terms'] ) ) {
		continue;
	}
	$query_var                 = 'filter_' . str_replace( 'pa_', '', $taxonomy );
	$active_chips[ $query_var ] = array(
		'label' => wc_attribute_label( $taxonomy ) . ': ' . implode( ', ', $data['terms'] ),
		'url'   => remove_query_arg( array( $query_var, 'paged' ) ),
	);
}

$clear_all_url = remove_query_arg( array( 'min_price', 'max_price', 'paged', 'orderby' ) );
foreach ( array_keys( $active_chips ) as $query_var ) {
	if ( 'price' !== $query_var ) {
		$clear_all_url = remove_query_arg( $query_var, $clear_all_url );
	}
}
?>
<div class="shop-control sticky-top no-offset">
	<a href="#filterShop" data-bs-toggle="offcanvas" class="btn-filter">
		<?php \Noorifa\Setup\Icons::render( 'filter' ); ?>
		<span class="text"><?php esc_html_e( 'Show Filters', 'noorifa' ); ?></span>
	</a>
	<div class="control-sorting">
		<div class="dropdown-sort">
			<div class="btn-select" data-bs-toggle="dropdown" aria-expanded="false">
				<span class="text-sort-value"><?php echo esc_html( $sort_options[ $current_orderby ] ); ?></span>
				<?php \Noorifa\Setup\Icons::render( 'CaretDown' ); ?>
			</div>
			<div class="dropdown-menu">
				<?php foreach ( $sort_options as $value => $label ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'orderby', $value, remove_query_arg( 'paged' ) ) ); ?>" class="select-item <?php echo $current_orderby === $value ? 'active' : ''; ?>">
						<span class="text-value-item"><?php echo esc_html( $label ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
<div class="wrapper-control-shop gridLayout-wrapper">
	<div class="meta-filter-shop">
		<div class="count-text text-caption-01">
			<?php
			printf(
				/* translators: %d: number of products. */
				esc_html( _n( '%d Product', '%d Products', (int) $wp_query->found_posts, 'noorifa' ) ),
				(int) $wp_query->found_posts
			);
			?>
		</div>
		<?php if ( ! empty( $active_chips ) ) : ?>
			<div class="br-line type-vertical"></div>
			<div id="applied-filters" class="d-flex flex-wrap gap-8">
				<?php foreach ( $active_chips as $chip ) : ?>
					<a href="<?php echo esc_url( $chip['url'] ); ?>" class="filter-tag">
						<?php echo esc_html( $chip['label'] ); ?>
						<?php \Noorifa\Setup\Icons::render( 'X2' ); ?>
					</a>
				<?php endforeach; ?>
			</div>
			<button type="button" onclick="window.location.href='<?php echo esc_url( $clear_all_url ); ?>'" id="remove-all" class="remove-all-filters">
				<?php \Noorifa\Setup\Icons::render( 'X2' ); ?>
				<?php esc_html_e( 'Clear all', 'noorifa' ); ?>
			</button>
		<?php endif; ?>
	</div>
</div>
