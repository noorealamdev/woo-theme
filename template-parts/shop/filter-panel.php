<?php
/**
 * Shop filter offcanvas: categories, price range, and product attributes.
 *
 * Every filter here is real: category links go to the real term archive,
 * price submits WooCommerce's own `min_price`/`max_price` query vars, and
 * attribute checkboxes toggle WooCommerce's own `filter_{attribute}` query
 * vars — no client-side fake filtering.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$product_categories = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
	)
);

$queried_object     = get_queried_object();
$current_category   = ( $queried_object instanceof WP_Term && 'product_cat' === $queried_object->taxonomy ) ? $queried_object->slug : '';

global $wpdb;
$price_min = (int) floor( (float) $wpdb->get_var( "SELECT MIN(CAST(meta_value AS DECIMAL)) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE meta_key = '_price' AND p.post_status = 'publish'" ) );
$price_max = (int) ceil( (float) $wpdb->get_var( "SELECT MAX(CAST(meta_value AS DECIMAL)) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE meta_key = '_price' AND p.post_status = 'publish'" ) );
$current_min_price  = isset( $_GET['min_price'] ) ? (int) $_GET['min_price'] : $price_min; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_max_price  = isset( $_GET['max_price'] ) ? (int) $_GET['max_price'] : $price_max; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$preserved_query_args = array();
foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( in_array( $key, array( 'min_price', 'max_price', 'paged' ), true ) ) {
		continue;
	}
	$preserved_query_args[ $key ] = $value;
}

$chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
$attribute_taxonomies = function_exists( 'wc_get_attribute_taxonomies' ) ? wc_get_attribute_taxonomies() : array();
?>
<div class="offcanvas offcanvas-start canvas-filter" id="filterShop">
	<div class="canvas-wrapper">
		<div class="canvas-header">
			<div class="h5 title"><?php esc_html_e( 'Filters', 'ecombon' ); ?></div>
			<span class="icon-close-popup" data-bs-dismiss="offcanvas"><?php \Ecombon\Setup\Icons::render( 'X2', 'fs-24 link' ); ?></span>
		</div>
		<div class="canvas-body">

			<?php if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) : ?>
				<div class="widget-facet">
					<div class="facet-title" data-bs-target="#facet-category" role="button" data-bs-toggle="collapse" aria-expanded="true" aria-controls="facet-category">
						<h6><?php esc_html_e( 'Product Categories', 'ecombon' ); ?></h6>
						<?php \Ecombon\Setup\Icons::render( 'CaretDown' ); ?>
					</div>
					<div id="facet-category" class="collapse show">
						<ul class="collapse-body filter-group-check group-category">
							<?php foreach ( $product_categories as $category ) : ?>
								<li class="list-item">
									<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="label link <?php echo $current_category === $category->slug ? 'active' : ''; ?>">
										<span class="cate-text"><?php echo esc_html( $category->name ); ?></span>
										<span class="count">(<?php echo esc_html( (string) $category->count ); ?>)</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
				<div class="br-line"></div>
			<?php endif; ?>

			<?php if ( $price_max > $price_min ) : ?>
				<div class="widget-facet">
					<div class="facet-title" data-bs-target="#facet-price" role="button" data-bs-toggle="collapse" aria-expanded="true" aria-controls="facet-price">
						<h6><?php esc_html_e( 'Filter By Price', 'ecombon' ); ?></h6>
						<?php \Ecombon\Setup\Icons::render( 'CaretDown' ); ?>
					</div>
					<div id="facet-price" class="collapse show">
						<form class="collapse-body widget-price filter-price" method="get" action="<?php echo esc_url( strtok( wp_unslash( $_SERVER['REQUEST_URI'] ), '?' ) ); ?>">
							<?php foreach ( $preserved_query_args as $key => $value ) : ?>
								<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>">
							<?php endforeach; ?>
							<div class="price-val-range" id="price-value-range" data-min="<?php echo esc_attr( (string) $price_min ); ?>" data-max="<?php echo esc_attr( (string) $price_max ); ?>"></div>
							<input type="hidden" id="price-min-input" name="min_price" value="<?php echo esc_attr( (string) $current_min_price ); ?>">
							<input type="hidden" id="price-max-input" name="max_price" value="<?php echo esc_attr( (string) $current_max_price ); ?>">
							<div class="price-box tf-grid-layout tf-col-2">
								<div class="box-wrap">
									<div class="price-val_wrap">
										<span class="cl-text-2 text-body-1">$</span>
										<div class="price-val" id="price-min-value"><?php echo esc_html( (string) $current_min_price ); ?></div>
									</div>
								</div>
								<div class="box-wrap">
									<div class="price-val_wrap">
										<span class="cl-text-2 text-body-1">$</span>
										<div class="price-val" id="price-max-value"><?php echo esc_html( (string) $current_max_price ); ?></div>
									</div>
								</div>
							</div>
							<button type="submit" class="tf-btn btn-fill w-100 justify-content-center mt-16">
								<?php esc_html_e( 'Apply', 'ecombon' ); ?>
							</button>
						</form>
					</div>
				</div>
			<?php endif; ?>

			<?php foreach ( $attribute_taxonomies as $attribute ) : ?>
				<?php
				$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
				if ( ! taxonomy_exists( $taxonomy ) ) {
					continue;
				}
				$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
				if ( empty( $terms ) || is_wp_error( $terms ) ) {
					continue;
				}
				$chosen_terms = $chosen_attributes[ $taxonomy ]['terms'] ?? array();
				?>
				<div class="br-line"></div>
				<div class="widget-facet">
					<div class="facet-title" data-bs-target="#facet-<?php echo esc_attr( $attribute->attribute_name ); ?>" role="button" data-bs-toggle="collapse" aria-expanded="true" aria-controls="facet-<?php echo esc_attr( $attribute->attribute_name ); ?>">
						<h6><?php echo esc_html( $attribute->attribute_label ); ?></h6>
						<?php \Ecombon\Setup\Icons::render( 'CaretDown' ); ?>
					</div>
					<div id="facet-<?php echo esc_attr( $attribute->attribute_name ); ?>" class="collapse show">
						<ul class="collapse-body filter-group-check">
							<?php foreach ( $terms as $term ) : ?>
								<li class="list-item">
									<a href="<?php echo esc_url( \Ecombon\WooCommerce\ShopFilters::toggle_attribute_url( $taxonomy, $term->slug, $chosen_terms ) ); ?>" class="label link <?php echo in_array( $term->slug, $chosen_terms, true ) ? 'active' : ''; ?>">
										<span class="cate-text"><?php echo esc_html( $term->name ); ?></span>
										<span class="count">(<?php echo esc_html( (string) $term->count ); ?>)</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endforeach; ?>

		</div>
	</div>
</div>
