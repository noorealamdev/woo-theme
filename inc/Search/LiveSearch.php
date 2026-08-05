<?php
/**
 * LiveSearch component.
 *
 * @package Noorifa
 */

namespace Noorifa\Search;

use Noorifa\Setup\ComponentInterface;
use WP_Query;

/**
 * Real-time AJAX search for the header search modal.
 *
 * Runs a real `WP_Query` against actual published products (when
 * WooCommerce is active) and pages/posts as the user types, and renders
 * the result through a normal template part — the same
 * ob_start()/get_template_part()/ob_get_clean() approach already used by
 * Noorifa\WooCommerce\CartFragments — so there is exactly one place
 * (template-parts/header/search-results.php) that owns the markup.
 */
class LiveSearch implements ComponentInterface {

	/**
	 * Minimum query length before a search is actually run.
	 */
	const MIN_CHARS = 2;

	/**
	 * Maximum number of products/content results returned per request.
	 */
	const PRODUCTS_LIMIT = 5;
	const CONTENT_LIMIT  = 3;

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'wp_ajax_noorifa_live_search', array( $this, 'handle_request' ) );
		add_action( 'wp_ajax_nopriv_noorifa_live_search', array( $this, 'handle_request' ) );
	}

	/**
	 * Handles the `noorifa_live_search` AJAX request.
	 */
	public function handle_request(): void {
		check_ajax_referer( 'noorifa_live_search', 'nonce' );

		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

		if ( strlen( $term ) < self::MIN_CHARS ) {
			wp_send_json_success( array( 'html' => '' ) );
		}

		$product_query = null;
		$product_total = 0;

		if ( class_exists( 'WooCommerce' ) ) {
			$product_query = new WP_Query(
				array(
					'post_type'           => 'product',
					'post_status'         => 'publish',
					's'                   => $term,
					'posts_per_page'      => self::PRODUCTS_LIMIT,
					'ignore_sticky_posts' => true,
					'no_found_rows'       => false,
				)
			);
			$product_total = (int) $product_query->found_posts;
		}

		$content_query = new WP_Query(
			array(
				'post_type'           => array( 'post', 'page' ),
				'post_status'         => 'publish',
				's'                   => $term,
				'posts_per_page'      => self::CONTENT_LIMIT,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		ob_start();
		get_template_part(
			'template-parts/header/search-results',
			null,
			array(
				'term'           => $term,
				'product_query'  => $product_query,
				'product_total'  => $product_total,
				'content_query'  => $content_query,
			)
		);
		$html = ob_get_clean();

		wp_reset_postdata();

		wp_send_json_success( array( 'html' => $html ) );
	}
}
