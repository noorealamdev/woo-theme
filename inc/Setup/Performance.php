<?php
/**
 * Performance component.
 *
 * @package Ecombon
 */

namespace Ecombon\Setup;

/**
 * Strips WordPress core's default <head> output down to what this theme's
 * frontend actually needs — none of this disables real functionality
 * (emoji, REST API, oEmbed all keep working), it only removes the
 * auto-discovery markup/scripts nothing here relies on.
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
