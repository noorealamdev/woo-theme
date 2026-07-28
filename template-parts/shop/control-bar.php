<?php
/**
 * Shop control bar: filter trigger, grid column switch, real sort links,
 * result count and active-filter chips.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

global $wp_query;

$sort_options = array(
	'menu_order' => __( 'Default sorting', 'ecombon' ),
	'popularity' => __( 'Best Selling', 'ecombon' ),
	'rating'     => __( 'Average Rating', 'ecombon' ),
	'date'       => __( 'Latest', 'ecombon' ),
	'price'      => __( 'Price, low to high', 'ecombon' ),
	'price-desc' => __( 'Price, high to low', 'ecombon' ),
	'a-z'        => __( 'Alphabetically, A-Z', 'ecombon' ),
	'z-a'        => __( 'Alphabetically, Z-A', 'ecombon' ),
);

$current_orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! isset( $sort_options[ $current_orderby ] ) ) {
	$current_orderby = 'menu_order';
}

$active_chips = array();
if ( isset( $_GET['min_price'] ) || isset( $_GET['max_price'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_chips['price'] = array(
		/* translators: 1: min price, 2: max price. */
		'label' => sprintf( __( 'Price: $%1$s–$%2$s', 'ecombon' ), sanitize_text_field( wp_unslash( $_GET['min_price'] ?? '' ) ), sanitize_text_field( wp_unslash( $_GET['max_price'] ?? '' ) ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
<div class="tf-shop-control sticky-top no-offset">
	<a href="#filterShop" data-bs-toggle="offcanvas" class="tf-btn-filter">
		<?php \Ecombon\Setup\Icons::render( 'filter' ); ?>
		<span class="text"><?php esc_html_e( 'Show Filters', 'ecombon' ); ?></span>
	</a>
	<ul class="tf-control-layout">
		<li class="tf-view-layout-switch active" data-value-layout="tf-col-4">
			<?php \Ecombon\Setup\Icons::render( 'grid-4' ); ?>
		</li>
		<li class="tf-view-layout-switch" data-value-layout="tf-col-3">
			<?php \Ecombon\Setup\Icons::render( 'grid-3' ); ?>
		</li>
		<li class="tf-view-layout-switch" data-value-layout="tf-col-2">
			<?php \Ecombon\Setup\Icons::render( 'grid-2' ); ?>
		</li>
	</ul>
	<div class="tf-control-sorting">
		<div class="tf-dropdown-sort">
			<div class="btn-select" data-bs-toggle="dropdown" aria-expanded="false">
				<span class="text-sort-value"><?php echo esc_html( $sort_options[ $current_orderby ] ); ?></span>
				<?php \Ecombon\Setup\Icons::render( 'CaretDown' ); ?>
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
				esc_html( _n( '%d Product', '%d Products', (int) $wp_query->found_posts, 'ecombon' ) ),
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
						<?php \Ecombon\Setup\Icons::render( 'X2' ); ?>
					</a>
				<?php endforeach; ?>
			</div>
			<button type="button" onclick="window.location.href='<?php echo esc_url( $clear_all_url ); ?>'" id="remove-all" class="remove-all-filters">
				<?php \Ecombon\Setup\Icons::render( 'X2' ); ?>
				<?php esc_html_e( 'Clear all', 'ecombon' ); ?>
			</button>
		<?php endif; ?>
	</div>
</div>
