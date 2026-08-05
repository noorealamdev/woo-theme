<?php
/**
 * SEO component.
 *
 * @package Noorifa
 */

namespace Noorifa\Setup;

/**
 * Outputs the meta description, Open Graph / Twitter Card tags, and
 * Organization/WebSite + BreadcrumbList structured data — all derived from
 * real, already-existing data (post/product content, the real custom logo,
 * the real social links footer.php already renders). Product structured
 * data itself is WooCommerce's own real `WC_Structured_Data::generate_product_data()`
 * (see Noorifa\Hooks\TemplateHooks, which keeps that default callback on
 * `woocommerce_single_product_summary`), not duplicated here.
 *
 * A handful of real, site-wide settings (Appearance/Noorifa > SEO) — a
 * fallback description/image for pages with no real per-page content to
 * derive one from (e.g. the Shop archive, which has none of its own),
 * `twitter:site`/`fb:app_id`, and search-engine verification meta tags —
 * all default empty/unset, so an untouched install prints exactly what it
 * always has. Per-page overrides (a specific product's own meta
 * description, say) are still out of scope here — that's real per-content
 * business data, which belongs in the future Noorifa Core plugin.
 */
class SEO implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'wp_head', array( $this, 'render_meta_tags' ), 1 );
		add_action( 'wp_footer', array( $this, 'render_structured_data' ) );
	}

	/**
	 * Outputs the meta description and Open Graph / Twitter Card tags.
	 */
	public function render_meta_tags(): void {
		$title       = wp_strip_all_tags( wp_get_document_title() );
		$description = $this->get_description();
		$image       = $this->get_image();
		// wp_get_canonical_url() only ever resolves for singular content
		// (WP core's own limitation) — real request URI for everything
		// else (archives, search, shop), same as what's actually in the
		// browser's address bar.
		$canonical = wp_get_canonical_url();
		$url       = $canonical ? $canonical : home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Recommended
		$type        = $this->get_og_type();

		if ( $description ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
		}

		printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );

		if ( $description ) {
			printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
		}

		if ( $image ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		}

		printf( '<meta name="twitter:card" content="%s">' . "\n", esc_attr( $image ? 'summary_large_image' : 'summary' ) );
		printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );

		if ( $description ) {
			printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
		}

		if ( $image ) {
			printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
		}

		$settings = \Noorifa\Settings\Layout::all()['seo'] ?? array();

		if ( ! empty( $settings['twitter_username'] ) ) {
			$handle = ltrim( trim( (string) $settings['twitter_username'] ), '@' );
			printf( '<meta name="twitter:site" content="@%s">' . "\n", esc_attr( $handle ) );
		}

		if ( ! empty( $settings['facebook_app_id'] ) ) {
			printf( '<meta property="fb:app_id" content="%s">' . "\n", esc_attr( $settings['facebook_app_id'] ) );
		}

		if ( ! empty( $settings['google_verification'] ) ) {
			printf( '<meta name="google-site-verification" content="%s">' . "\n", esc_attr( $settings['google_verification'] ) );
		}

		if ( ! empty( $settings['bing_verification'] ) ) {
			printf( '<meta name="msvalidate.01" content="%s">' . "\n", esc_attr( $settings['bing_verification'] ) );
		}
	}

	/**
	 * Outputs Organization/WebSite (sitewide) and BreadcrumbList (contextual)
	 * JSON-LD, each only when there's real data to describe.
	 */
	public function render_structured_data(): void {
		$graphs = array_filter(
			array(
				$this->get_organization_data(),
				$this->get_website_data(),
				$this->get_breadcrumb_data(),
			)
		);

		foreach ( $graphs as $graph ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $graph ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Real, content-derived meta description for the current request. Falls
	 * back to the real stored `seo.default_description` setting (e.g. for
	 * the Shop archive, which has no excerpt/content/term-description of
	 * its own) when there's no real per-page text to summarize, rather
	 * than fabricating one — returns an empty string (no tag printed) only
	 * if that fallback is itself unset.
	 */
	private function get_description(): string {
		if ( function_exists( 'is_product' ) && is_product() ) {
			global $product;
			$text = $product instanceof \WC_Product ? $product->get_short_description() : '';
			$text = $text ? $text : get_the_excerpt();
		} elseif ( is_singular() ) {
			$text = has_excerpt() ? get_the_excerpt() : get_the_content();
		} elseif ( is_category() || is_tag() || is_tax() || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) {
			$text = term_description();
		} elseif ( is_home() || is_front_page() ) {
			$text = get_bloginfo( 'description' );
		} else {
			$text = '';
		}

		$text = wp_strip_all_tags( strip_shortcodes( (string) $text ) );

		if ( ! $text ) {
			$text = \Noorifa\Settings\Layout::all()['seo']['default_description'] ?? '';
		}

		return $text ? wp_trim_words( $text, 30, '…' ) : '';
	}

	/**
	 * Real representative image for the current request — product image,
	 * post featured image, the real custom logo, or (last resort) the real
	 * stored `seo.default_image` setting.
	 */
	private function get_image(): string {
		if ( function_exists( 'is_product' ) && is_product() ) {
			global $product;
			if ( $product instanceof \WC_Product ) {
				$image_id = $product->get_image_id();
				if ( $image_id ) {
					$url = wp_get_attachment_image_url( $image_id, 'large' );
					if ( $url ) {
						return $url;
					}
				}
			}
		}

		if ( is_singular() && has_post_thumbnail() ) {
			$url = get_the_post_thumbnail_url( null, 'large' );
			if ( $url ) {
				return $url;
			}
		}

		$logo_id = get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$url = wp_get_attachment_image_url( (int) $logo_id, 'full' );
			if ( $url ) {
				return $url;
			}
		}

		$default_image = \Noorifa\Settings\Layout::all()['seo']['default_image'] ?? '';
		if ( $default_image ) {
			return $default_image;
		}

		return '';
	}

	/**
	 * Open Graph type for the current request.
	 */
	private function get_og_type(): string {
		if ( function_exists( 'is_product' ) && is_product() ) {
			return 'product';
		}

		if ( is_singular( 'post' ) ) {
			return 'article';
		}

		return 'website';
	}

	/**
	 * Real Organization data — name, url, and the real custom logo/social
	 * links if they're actually set (never fabricated placeholders).
	 */
	private function get_organization_data(): array {
		$data = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Organization',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url( '/' ),
		);

		$logo_id = get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$logo_url = wp_get_attachment_image_url( (int) $logo_id, 'full' );
			if ( $logo_url ) {
				$data['logo'] = $logo_url;
			}
		}

		// Same real, filterable social links footer.php already renders —
		// see footer.php's own `noorifa_social_links` filter. Empty by
		// default until a site owner actually configures real accounts.
		$socials = apply_filters( 'noorifa_social_links', array() );
		$same_as = array_values( array_filter( array_map( 'esc_url_raw', $socials ) ) );
		if ( $same_as ) {
			$data['sameAs'] = $same_as;
		}

		return $data;
	}

	/**
	 * Real WebSite data (name/url) for the site.
	 */
	private function get_website_data(): array {
		return array(
			'@context' => 'https://schema.org',
			'@type'    => 'WebSite',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url( '/' ),
		);
	}

	/**
	 * Real BreadcrumbList data, built from the same real WP/WC context
	 * (product categories, post categories, queried terms/pages) the
	 * theme's own visible breadcrumb templates use — see
	 * blog/breadcrumb-nav.php and global/page-title.php for the matching
	 * visible markup (single product pages have no visible breadcrumb, but
	 * still get this structured data for search engines). Returns an empty
	 * array (no tag printed) on the front page, where no breadcrumb is
	 * shown at all.
	 */
	private function get_breadcrumb_data(): array {
		if ( is_front_page() ) {
			return array();
		}

		$crumbs   = array();
		$crumbs[] = array( __( 'Home', 'noorifa' ), home_url( '/' ) );

		if ( function_exists( 'is_product' ) && is_product() ) {
			$crumbs[] = array( __( 'Shop', 'noorifa' ), wc_get_page_permalink( 'shop' ) );

			$terms = get_the_terms( get_the_ID(), 'product_cat' );
			$term  = ( $terms && ! is_wp_error( $terms ) ) ? reset( $terms ) : null;
			if ( $term ) {
				$crumbs[] = array( $term->name, get_term_link( $term ) );
			}

			$crumbs[] = array( get_the_title(), get_permalink() );
		} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
			$crumbs[] = array( get_the_title( wc_get_page_id( 'shop' ) ), wc_get_page_permalink( 'shop' ) );
		} elseif ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
			$crumbs[] = array( single_term_title( '', false ), get_term_link( get_queried_object() ) );
		} elseif ( is_singular( 'post' ) ) {
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				$crumbs[] = array( $categories[0]->name, get_category_link( $categories[0] ) );
			}
			$crumbs[] = array( get_the_title(), get_permalink() );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$crumbs[] = array( single_term_title( '', false ), get_term_link( get_queried_object() ) );
		} elseif ( is_search() ) {
			/* translators: %s: search query. */
			$crumbs[] = array( sprintf( __( 'Search results for: %s', 'noorifa' ), get_search_query() ), '' );
		} elseif ( is_404() ) {
			$crumbs[] = array( __( 'Page not found', 'noorifa' ), '' );
		} elseif ( is_singular() ) {
			$crumbs[] = array( get_the_title(), get_permalink() );
		} else {
			return array();
		}

		$items = array();
		foreach ( $crumbs as $index => $crumb ) {
			list( $name, $item_url ) = $crumb;
			$item                    = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => wp_strip_all_tags( $name ),
			);
			if ( $item_url ) {
				$item['item'] = $item_url;
			}
			$items[] = $item;
		}

		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	}
}
