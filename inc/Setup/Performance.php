<?php
/**
 * Performance component.
 *
 * @package Noorifa
 */

namespace Noorifa\Setup;

/**
 * Strips WordPress core's default <head>/enqueue output down to what this
 * theme's frontend actually needs. The unconditional part (always active,
 * no setting) disables no real functionality — emoji, REST API, oEmbed all
 * keep working, it only removes auto-discovery markup/scripts nothing here
 * relies on. Two further, genuinely optional trade-offs (disabling XML-RPC
 * entirely, stripping `?ver=` cache-busting strings) are real settings
 * fields (Appearance/Noorifa > Performance) — default off, since each has
 * a real downside for some sites, not an unambiguous win for every site.
 */
class Performance implements ComponentInterface {

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		// Emoji-detection script + inline styles: the theme's own icon
		// system (Icons::render) handles all real iconography, and every
		// browser this site needs to support renders emoji natively.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		add_filter( 'tiny_mce_plugins', array( $this, 'remove_emoji_tinymce_plugin' ) );
		add_filter( 'wp_resource_hints', array( $this, 'remove_emoji_dns_prefetch' ), 10, 2 );

		// Discovery links no real visitor uses: RSD (external blog-editor
		// clients like the old Windows Live Writer), its manifest, and the
		// shortlink.
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		// REST API / oEmbed auto-discovery <link> tags — the REST API and
		// oEmbed processing both keep working normally; this only drops
		// the discovery hints, which nothing on this frontend consumes.
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// WordPress version number — no functional purpose on the frontend,
		// only makes the install's version trivially fingerprintable.
		remove_action( 'wp_head', 'wp_generator' );

		// oEmbed's client-side responsive-iframe-resize script — the
		// discovery <link> tags are already removed above, and nothing on
		// this frontend needs the JS that resizes *other* sites' embeds
		// of *this* site's content either.
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_embed_script' ), 100 );

		// Real, admin-configurable settings (Appearance/Noorifa > Performance)
		// — both default off, since each is a genuine trade-off a site owner
		// should choose, not an unambiguous win to force on everyone.
		$settings = \Noorifa\Settings\Layout::all()['performance'] ?? array();

		if ( ! empty( $settings['disable_xmlrpc'] ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}

		if ( ! empty( $settings['remove_version_strings'] ) && ! is_admin() ) {
			add_filter( 'script_loader_src', array( $this, 'remove_version_query_string' ) );
			add_filter( 'style_loader_src', array( $this, 'remove_version_query_string' ) );
		}
	}

	/**
	 * Drops the `?ver=` cache-busting query string WP core appends to every
	 * enqueued script/style URL — some CDN/proxy caching layers cache a
	 * URL with query string less effectively, at the cost of needing a
	 * real cache purge (not just a version bump) after a theme update.
	 *
	 * @param string $src Real enqueued script/style URL.
	 */
	public function remove_version_query_string( string $src ): string {
		return $src ? remove_query_arg( 'ver', $src ) : $src;
	}

	/**
	 * Deregisters WP core's own `wp-embed` script.
	 */
	public function dequeue_embed_script(): void {
		wp_deregister_script( 'wp-embed' );
	}

	/**
	 * Drops the emoji TinyMCE plugin from the list Core would otherwise load.
	 *
	 * @param string[] $plugins Active TinyMCE plugins.
	 * @return string[]
	 */
	public function remove_emoji_tinymce_plugin( array $plugins ): array {
		return array_diff( $plugins, array( 'wpemoji' ) );
	}

	/**
	 * Drops the emoji CDN from the dns-prefetch resource hints.
	 *
	 * @param string[] $urls          Resource hint URLs for this $relation_type.
	 * @param string   $relation_type The relation type being filtered.
	 * @return string[]
	 */
	public function remove_emoji_dns_prefetch( array $urls, string $relation_type ): array {
		if ( 'dns-prefetch' === $relation_type ) {
			$urls = array_diff( $urls, array( 'https://s.w.org' ) );
		}

		return $urls;
	}
}
