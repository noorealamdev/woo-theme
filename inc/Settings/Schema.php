<?php
/**
 * Schema component.
 *
 * @package Noorifa
 */

namespace Noorifa\Settings;

/**
 * The single field registry every real theme setting is declared in —
 * one entry per field, each covering its own default value, its storage
 * path inside the nested settings array, its flat key for
 * `noorifa_settings()`, and (via `type`) how it's sanitized on save.
 *
 * Adding or removing a field here is enough to change what `Layout::all()`
 * defaults to, what `Rest_Controller::save_settings()` accepts, and what
 * `noorifa_settings()` exposes — those three call into `defaults()`,
 * `sanitize()` and `flatten()` below instead of each re-declaring the
 * field by hand. The React admin form itself is still built by hand
 * per section (assets/js/admin/settings-app.js) — this registry only
 * owns the data layer, not the UI layout.
 */
class Schema {

	/**
	 * The full field registry.
	 *
	 * Each entry: `path` (dot-path into the nested settings array),
	 * `type` (drives sanitization — text/email/textarea/html/raw_html/url/
	 * image/bool/hex_color/select/range/order/zones/subset_order/
	 * social_links), `default`, `flat` (the `noorifa_settings()` key), and, only for
	 * select/range/order/zones/subset_order/social_links, whatever extra
	 * shape that type needs (`choices`/`min`/`max`/`modules`). `order`,
	 * `zones`, `subset_order` and `social_links` fields additionally take
	 * a `read` callback so `flatten()` returns the *validated* stored
	 * value (falling back to
	 * `default` if it's ever a stale/corrupted set) instead of a raw,
	 * unvalidated read.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function fields(): array {
		$fields = array(
			array(
				'path'    => 'topbar.enabled',
				'flat'    => 'topbar_enabled',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'topbar.message',
				'flat'    => 'topbar_message',
				'type'    => 'html',
				'default' => Layout::topbar_message_default(),
			),
			array(
				'path'    => 'topbar.background_color',
				'flat'    => 'topbar_background_color',
				'type'    => 'hex_color',
				'default' => Layout::TOPBAR_BG_DEFAULT,
				'css'     => array( 'var' => '--noorifa-topbar-bg' ),
			),
			array(
				'path'    => 'topbar.font_size',
				'flat'    => 'topbar_font_size',
				'type'    => 'range',
				'default' => Layout::TOPBAR_FONT_SIZE_DEFAULT,
				'min'     => Layout::TOPBAR_FONT_SIZE_MIN,
				'max'     => Layout::TOPBAR_FONT_SIZE_MAX,
				'css'     => array( 'var' => '--noorifa-topbar-font-size', 'unit' => 'px' ),
			),
			array(
				'path'    => 'topbar.text_color',
				'flat'    => 'topbar_text_color',
				'type'    => 'hex_color',
				'default' => Layout::TOPBAR_TEXT_DEFAULT,
				'css'     => array( 'var' => '--noorifa-topbar-text' ),
			),
			array(
				'path'    => 'branding.color_primary',
				'flat'    => 'color_primary',
				'type'    => 'hex_color',
				'default' => Layout::COLOR_PRIMARY_DEFAULT,
				'css'     => array( 'var' => '--primary' ),
			),
			array(
				'path'    => 'branding.color_secondary',
				'flat'    => 'color_secondary',
				'type'    => 'hex_color',
				'default' => Layout::COLOR_SECONDARY_DEFAULT,
				'css'     => array( 'var' => '--secondary' ),
			),
			array(
				'path'    => 'branding.body_background_color',
				'flat'    => 'body_background_color',
				'type'    => 'hex_color',
				'default' => Layout::BODY_BG_DEFAULT,
				'css'     => array( 'var' => '--noorifa-body-bg' ),
			),
			array(
				'path'    => 'typography.font_body',
				'flat'    => 'font_body',
				'type'    => 'select',
				'default' => '',
				'choices' => array_merge( array( '' ), Layout::google_fonts() ),
				'css'     => array( 'var' => '--font-main', 'quote' => true ),
				'is_font' => true,
			),
			array(
				'path'    => 'typography.font_heading',
				'flat'    => 'font_heading',
				'type'    => 'select',
				'default' => '',
				'choices' => array_merge( array( '' ), Layout::google_fonts() ),
				'css'     => array( 'var' => '--font-heading', 'quote' => true ),
				'is_font' => true,
			),
			array(
				'path'    => 'typography.font_menu',
				'flat'    => 'font_menu',
				'type'    => 'select',
				'default' => '',
				'choices' => array_merge( array( '' ), Layout::google_fonts() ),
				'css'     => array( 'var' => '--font-menu', 'quote' => true ),
				'is_font' => true,
			),
			array(
				'path'    => 'typography.font_size_base',
				'flat'    => 'font_size_base',
				'type'    => 'range',
				'default' => Layout::FONT_SIZE_BASE_DEFAULT,
				'min'     => Layout::FONT_SIZE_BASE_MIN,
				'max'     => Layout::FONT_SIZE_BASE_MAX,
				'css'     => array( 'var' => '--font-size-base', 'unit' => 'px' ),
			),
			array(
				'path'    => 'layout.site_width',
				'flat'    => 'site_width',
				'type'    => 'select',
				'default' => Layout::SITE_WIDTH_DEFAULT,
				'choices' => array_keys( Layout::site_width_choices() ),
			),
			array(
				'path'    => 'shop.grid_columns',
				'flat'    => 'shop_grid_columns',
				'type'    => 'select',
				'default' => Layout::GRID_COLUMNS_DEFAULT,
				'choices' => array_keys( Layout::grid_columns_choices() ),
			),
			array(
				'path'    => 'shop.products_per_page',
				'flat'    => 'shop_products_per_page',
				'type'    => 'select',
				'default' => Layout::PRODUCTS_PER_PAGE_DEFAULT,
				'choices' => array_keys( Layout::products_per_page_choices() ),
			),
			array(
				'path'    => 'shop.title_font_size',
				'flat'    => 'shop_title_font_size',
				'type'    => 'range',
				'default' => Layout::SHOP_TITLE_FONT_SIZE_DEFAULT,
				'min'     => Layout::SHOP_TITLE_FONT_SIZE_MIN,
				'max'     => Layout::SHOP_TITLE_FONT_SIZE_MAX,
				'css'     => array( 'var' => '--noorifa-shop-title-font-size', 'unit' => 'px' ),
			),
			array(
				'path'    => 'shop.meta_font_size',
				'flat'    => 'shop_meta_font_size',
				'type'    => 'range',
				'default' => Layout::SHOP_META_FONT_SIZE_DEFAULT,
				'min'     => Layout::SHOP_META_FONT_SIZE_MIN,
				'max'     => Layout::SHOP_META_FONT_SIZE_MAX,
				'css'     => array( 'var' => '--noorifa-shop-meta-font-size', 'unit' => 'px' ),
			),
			array(
				'path'    => 'shop.title_font_weight',
				'flat'    => 'shop_title_font_weight',
				'type'    => 'select',
				'default' => Layout::SHOP_TITLE_FONT_WEIGHT_DEFAULT,
				'choices' => array_keys( Layout::font_weight_choices() ),
				'css'     => array( 'var' => '--noorifa-shop-title-font-weight' ),
			),
			array(
				'path'    => 'blog.grid_columns',
				'flat'    => 'blog_grid_columns',
				'type'    => 'select',
				'default' => Layout::BLOG_GRID_COLUMNS_DEFAULT,
				'choices' => array_keys( Layout::blog_grid_columns_choices() ),
			),
			array(
				'path'    => 'blog.excerpt_length',
				'flat'    => 'blog_excerpt_length',
				'type'    => 'range',
				'default' => Layout::BLOG_EXCERPT_LENGTH_DEFAULT,
				'min'     => Layout::BLOG_EXCERPT_LENGTH_MIN,
				'max'     => Layout::BLOG_EXCERPT_LENGTH_MAX,
			),
			array(
				'path'    => 'blog.sidebar_enabled',
				'flat'    => 'blog_sidebar_enabled',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'blog.sidebar_categories',
				'flat'    => 'blog_sidebar_categories',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'blog.sidebar_recent_posts',
				'flat'    => 'blog_sidebar_recent_posts',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'blog.sidebar_recent_posts_count',
				'flat'    => 'blog_sidebar_recent_posts_count',
				'type'    => 'range',
				'default' => Layout::BLOG_SIDEBAR_RECENT_COUNT_DEFAULT,
				'min'     => Layout::BLOG_SIDEBAR_RECENT_COUNT_MIN,
				'max'     => Layout::BLOG_SIDEBAR_RECENT_COUNT_MAX,
			),
			array(
				'path'    => 'blog.sidebar_tags',
				'flat'    => 'blog_sidebar_tags',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'blog.sidebar_tags_count',
				'flat'    => 'blog_sidebar_tags_count',
				'type'    => 'range',
				'default' => Layout::BLOG_SIDEBAR_TAGS_COUNT_DEFAULT,
				'min'     => Layout::BLOG_SIDEBAR_TAGS_COUNT_MIN,
				'max'     => Layout::BLOG_SIDEBAR_TAGS_COUNT_MAX,
			),
			array(
				'path'    => 'blog.related_posts_enabled',
				'flat'    => 'blog_related_posts_enabled',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'blog.related_posts_heading',
				'flat'    => 'blog_related_posts_heading',
				'type'    => 'text',
				'default' => Layout::blog_related_heading_default(),
			),
			array(
				'path'    => 'blog.related_posts_subtitle',
				'flat'    => 'blog_related_posts_subtitle',
				'type'    => 'text',
				'default' => Layout::blog_related_subtitle_default(),
			),
			array(
				'path'    => 'blog.related_posts_count',
				'flat'    => 'blog_related_posts_count',
				'type'    => 'range',
				'default' => Layout::BLOG_RELATED_COUNT_DEFAULT,
				'min'     => Layout::BLOG_RELATED_COUNT_MIN,
				'max'     => Layout::BLOG_RELATED_COUNT_MAX,
			),
			array(
				'path'    => 'blog.share_buttons_enabled',
				'flat'    => 'blog_share_buttons_enabled',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'blog.share_facebook',
				'flat'    => 'blog_share_facebook',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'blog.share_x',
				'flat'    => 'blog_share_x',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'blog.share_pinterest',
				'flat'    => 'blog_share_pinterest',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'buttons.style',
				'flat'    => 'buttons_style',
				'type'    => 'select',
				'default' => Layout::BUTTON_STYLE_DEFAULT,
				'choices' => array_keys( Layout::button_style_choices() ),
			),
			array(
				'path'    => 'performance.disable_xmlrpc',
				'flat'    => 'performance_disable_xmlrpc',
				'type'    => 'bool',
				'default' => false,
			),
			array(
				'path'    => 'performance.remove_version_strings',
				'flat'    => 'performance_remove_version_strings',
				'type'    => 'bool',
				'default' => false,
			),
			array(
				'path'    => 'seo.default_description',
				'flat'    => 'seo_default_description',
				'type'    => 'textarea',
				'default' => '',
			),
			array(
				'path'    => 'seo.default_image',
				'flat'    => 'seo_default_image',
				'type'    => 'image',
				'default' => '',
			),
			array(
				'path'    => 'seo.twitter_username',
				'flat'    => 'seo_twitter_username',
				'type'    => 'text',
				'default' => '',
			),
			array(
				'path'    => 'seo.facebook_app_id',
				'flat'    => 'seo_facebook_app_id',
				'type'    => 'text',
				'default' => '',
			),
			array(
				'path'    => 'seo.google_verification',
				'flat'    => 'seo_google_verification',
				'type'    => 'text',
				'default' => '',
			),
			array(
				'path'    => 'seo.bing_verification',
				'flat'    => 'seo_bing_verification',
				'type'    => 'text',
				'default' => '',
			),
			array(
				'path'    => 'integrations.google_analytics_code',
				'flat'    => 'integrations_google_analytics_code',
				'type'    => 'raw_html',
				'default' => '',
			),
			array(
				'path'    => 'integrations.facebook_pixel_code',
				'flat'    => 'integrations_facebook_pixel_code',
				'type'    => 'raw_html',
				'default' => '',
			),
			array(
				'path'    => 'page_header.breadcrumbs_enabled',
				'flat'    => 'page_header_breadcrumbs_enabled',
				'type'    => 'bool',
				'default' => true,
			),
			array(
				'path'    => 'page_header.alignment',
				'flat'    => 'page_header_alignment',
				'type'    => 'select',
				'default' => Layout::PAGE_HEADER_ALIGNMENT_DEFAULT,
				'choices' => array_keys( Layout::page_header_alignment_choices() ),
			),
			array(
				'path'    => 'page_header.background_enabled',
				'flat'    => 'page_header_background_enabled',
				'type'    => 'bool',
				'default' => false,
			),
			array(
				'path'     => 'page_header.background_color',
				'flat'     => 'page_header_background_color',
				'type'     => 'hex_color',
				'default'  => Layout::PAGE_HEADER_BG_DEFAULT,
				'requires' => 'page_header.background_enabled',
				'css'      => array( 'var' => '--noorifa-page-title-bg' ),
			),
			array(
				'path'     => 'page_header.text_color',
				'flat'     => 'page_header_text_color',
				'type'     => 'hex_color',
				'default'  => Layout::PAGE_HEADER_TEXT_DEFAULT,
				'requires' => 'page_header.background_enabled',
				'css'      => array( 'var' => '--noorifa-page-title-fg' ),
			),
			array(
				'path'    => 'layout.container_width',
				'flat'    => 'container_width',
				'type'    => 'range',
				'default' => Layout::CONTAINER_WIDTH_DEFAULT,
				'min'     => Layout::CONTAINER_WIDTH_MIN,
				'max'     => Layout::CONTAINER_WIDTH_MAX,
				'css'     => array( 'var' => '--noorifa-container-width', 'unit' => 'px' ),
			),
			array(
				'path'    => 'header.container_width',
				'flat'    => 'header_container_width',
				'type'    => 'range',
				'default' => Layout::HEADER_CONTAINER_WIDTH_DEFAULT,
				'min'     => Layout::HEADER_CONTAINER_WIDTH_MIN,
				'max'     => Layout::HEADER_CONTAINER_WIDTH_MAX,
				'css'     => array( 'var' => '--noorifa-container-full-width', 'unit' => 'px' ),
			),
			array(
				'path'    => 'header.menu_color',
				'flat'    => 'header_menu_color',
				'type'    => 'hex_color',
				'default' => Layout::MENU_COLOR_DEFAULT,
				'css'     => array( 'var' => '--noorifa-menu-color' ),
			),
			array(
				'path'    => 'header.menu_font_size',
				'flat'    => 'header_menu_font_size',
				'type'    => 'range',
				'default' => Layout::MENU_FONT_SIZE_DEFAULT,
				'min'     => Layout::MENU_FONT_SIZE_MIN,
				'max'     => Layout::MENU_FONT_SIZE_MAX,
				'css'     => array( 'var' => '--noorifa-menu-font-size', 'unit' => 'px' ),
			),
			array(
				'path'    => 'header.menu_uppercase',
				'flat'    => 'header_menu_uppercase',
				'type'    => 'bool',
				'default' => false,
				'css'     => array( 'var' => '--noorifa-menu-transform', 'true_value' => 'uppercase' ),
			),
			array(
				'path'    => 'header.menu_bold',
				'flat'    => 'header_menu_bold',
				'type'    => 'bool',
				'default' => true,
				'css'     => array( 'var' => '--noorifa-menu-weight', 'true_value' => '700', 'false_value' => '500' ),
			),
			array(
				'path'    => 'header.sticky',
				'flat'    => 'header_sticky',
				'type'    => 'bool',
				'default' => false,
			),
			array(
				'path'    => 'header.force_mobile_menu',
				'flat'    => 'header_force_mobile_menu',
				'type'    => 'bool',
				'default' => false,
			),
			array(
				'path'    => 'header.whatsapp_number',
				'flat'    => 'header_whatsapp_number',
				'type'    => 'text',
				'default' => '',
			),
			array(
				'path'    => 'header.background_color_enabled',
				'flat'    => 'header_background_color_enabled',
				'type'    => 'bool',
				'default' => false,
			),
			array(
				'path'     => 'header.background_color',
				'flat'     => 'header_background_color',
				'type'     => 'hex_color',
				'default'  => Layout::HEADER_BG_DEFAULT,
				'requires' => 'header.background_color_enabled',
				'css'      => array( 'var' => '--noorifa-header-bg' ),
			),
			array(
				'path'    => 'header.zones',
				'flat'    => 'header_zones',
				'type'    => 'zones',
				'modules' => array_keys( Layout::header_toggleable() ),
				'default' => Layout::header_zones_default(),
				'read'    => array( Layout::class, 'header_zones' ),
			),
			array(
				'path'    => 'footer.background_color_enabled',
				'flat'    => 'footer_background_color_enabled',
				'type'    => 'bool',
				'default' => false,
			),
			array(
				'path'     => 'footer.background_color',
				'flat'     => 'footer_background_color',
				'type'     => 'hex_color',
				'default'  => Layout::FOOTER_BG_DEFAULT,
				'requires' => 'footer.background_color_enabled',
				'css'      => array( 'var' => '--noorifa-footer-bg' ),
			),
			array(
				'path'     => 'footer.text_color',
				'flat'     => 'footer_text_color',
				'type'     => 'hex_color',
				'default'  => Layout::FOOTER_TEXT_DEFAULT,
				'requires' => 'footer.background_color_enabled',
				'css'      => array( 'var' => '--noorifa-footer-fg' ),
			),
			array(
				'path'    => 'footer.top',
				'flat'    => 'footer_top_order',
				'type'    => 'subset_order',
				'modules' => array_keys( Layout::footer_top_elements() ),
				'default' => Layout::footer_top_defaults(),
				'read'    => array( Layout::class, 'footer_top_items' ),
			),
			array(
				'path'    => 'footer.bottom',
				'flat'    => 'footer_bottom_order',
				'type'    => 'subset_order',
				'modules' => array_keys( Layout::footer_bottom_elements() ),
				'default' => Layout::footer_bottom_defaults(),
				'read'    => array( Layout::class, 'footer_bottom_items' ),
			),
			array(
				'path'    => 'footer.newsletter_heading',
				'flat'    => 'footer_newsletter_heading',
				'type'    => 'text',
				'default' => Layout::newsletter_heading_default(),
			),
			array(
				'path'    => 'footer.newsletter_description',
				'flat'    => 'footer_newsletter_description',
				'type'    => 'text',
				'default' => Layout::newsletter_description_default(),
			),
			array(
				'path'    => 'newsletter.provider',
				'flat'    => 'newsletter_provider',
				'type'    => 'select',
				'default' => Layout::NEWSLETTER_PROVIDER_DEFAULT,
				'choices' => array_keys( Layout::newsletter_provider_choices() ),
			),
			array(
				'path'    => 'newsletter.embed_code',
				'flat'    => 'newsletter_embed_code',
				'type'    => 'raw_html',
				'default' => '',
			),
			array(
				'path'    => 'footer.company_heading',
				'flat'    => 'footer_company_heading',
				'type'    => 'text',
				'default' => Layout::company_heading_default(),
			),
			array(
				'path'    => 'footer.info_logo',
				'flat'    => 'footer_info_logo',
				'type'    => 'image',
				'default' => Layout::info_logo_default(),
			),
			array(
				'path'    => 'footer.info_description',
				'flat'    => 'footer_info_description',
				'type'    => 'html',
				'default' => Layout::info_description_default(),
			),
			array(
				'path'    => 'footer.social_links',
				'flat'    => 'footer_social_links',
				'type'    => 'social_links',
				'choices' => array_keys( Layout::social_networks() ),
				'default' => Layout::social_links_default(),
				'read'    => array( Layout::class, 'footer_social_links_items' ),
			),
			array(
				'path'    => 'footer.customer_heading',
				'flat'    => 'footer_customer_heading',
				'type'    => 'text',
				'default' => Layout::customer_heading_default(),
			),
			array(
				'path'    => 'footer.copyright_text',
				'flat'    => 'footer_copyright_text',
				'type'    => 'text',
				'default' => Layout::copyright_text_default(),
			),
			array(
				'path'    => 'footer.payment_icons_image',
				'flat'    => 'footer_payment_icons_image',
				'type'    => 'image',
				'default' => Layout::payment_icons_image_default(),
			),
			array(
				'path'    => 'custom_code.css',
				'flat'    => 'custom_code_css',
				'type'    => 'raw_html',
				'default' => '',
			),
			array(
				'path'    => 'custom_code.js',
				'flat'    => 'custom_code_js',
				'type'    => 'raw_html',
				'default' => '',
			),
		);

		return $fields;
	}

	/**
	 * The real nested defaults array — same shape `Layout::defaults()`
	 * has always returned.
	 */
	public static function defaults(): array {
		$defaults = array();
		foreach ( self::fields() as $field ) {
			self::set( $defaults, $field['path'], $field['default'] );
		}
		return $defaults;
	}

	/**
	 * Builds the real, full settings array — one field at a time, taking
	 * each field's *entire* value from the raw stored option if present
	 * at all, else its own default. This is `Layout::all()`'s real
	 * implementation, deliberately NOT a blanket `array_replace_recursive`
	 * merge: that merges numeric-indexed lists by index rather than
	 * replacing them wholesale, so a shorter stored list (e.g. a header
	 * module dragged out, a footer column hidden) gets silently padded
	 * back out with stale trailing default items — see
	 * array_replace_recursive_shorter_list_bug for the full story. Taking
	 * a field's value as a whole, not merging into it, avoids this for
	 * every field, not just the ones with their own dedicated validated
	 * accessor.
	 *
	 * @param array $stored Raw stored option value (e.g. from `get_option()`).
	 */
	public static function merge( array $stored ): array {
		$result = array();
		foreach ( self::fields() as $field ) {
			$value = self::get( $stored, $field['path'], null );
			self::set( $result, $field['path'], null === $value ? $field['default'] : $value );
		}
		return $result;
	}

	/**
	 * Sanitizes a posted (REST request) array into the real nested
	 * settings array, one field at a time, using the sanitization rule
	 * for that field's `type`. Any field missing from the posted data,
	 * or that fails validation, falls back to its own real default —
	 * matching every sanitizer this replaced.
	 *
	 * @param array $posted Raw posted data (e.g. from a JSON REST body).
	 */
	public static function sanitize( array $posted ): array {
		$settings = array();
		foreach ( self::fields() as $field ) {
			$raw = self::get( $posted, $field['path'], null );
			self::set( $settings, $field['path'], self::sanitize_value( $raw, $field ) );
		}
		return $settings;
	}

	/**
	 * The flat, single-level array `noorifa_settings()` returns.
	 *
	 * @param array $settings A real, already-merged settings array (i.e. `Layout::all()`).
	 */
	public static function flatten( array $settings ): array {
		$flat = array();
		foreach ( self::fields() as $field ) {
			$flat[ $field['flat'] ] = isset( $field['read'] )
				? call_user_func( $field['read'] )
				: self::get( $settings, $field['path'], $field['default'] );
		}
		return $flat;
	}

	/**
	 * Every real `--css-var:value;` declaration a site owner's changes
	 * actually call for, plus the Google Font family names to enqueue —
	 * built generically from each field's own `css`/`is_font` declaration,
	 * so `Frontend_Output` never hand-writes a per-field `printf()` again.
	 * A field with no `css` key (order lists, choices with no direct
	 * visual output) is simply skipped. A `bool` field's `css` output
	 * needs a `true_value` (e.g. 'uppercase', '700') since the stored
	 * true/false isn't itself a usable CSS value. A field with a
	 * `requires` key (a companion boolean field's path) only ever prints
	 * while that companion is true, e.g. a "Background Color" gated by
	 * its own "Enable" toggle.
	 *
	 * @param array $settings A real, already-merged settings array (i.e. `Layout::all()`).
	 * @return array{declarations: string[], font_families: string[]}
	 */
	public static function css_declarations( array $settings ): array {
		$declarations   = array();
		$font_families  = array();

		foreach ( self::fields() as $field ) {
			if ( empty( $field['css'] ) ) {
				continue;
			}

			// A `requires` field is gated by a companion boolean (e.g. a
			// "Background Color" field only applies while its own
			// "Enable" toggle is on) — while that toggle is off, the
			// override never prints, even if the color differs from its
			// own default. While it's on, the override always prints,
			// even if the color happens to equal its own default.
			if ( ! empty( $field['requires'] ) ) {
				if ( ! self::get( $settings, $field['requires'], false ) ) {
					continue;
				}
			} elseif ( self::get( $settings, $field['path'], $field['default'] ) === $field['default'] ) {
				continue; // Unchanged — nothing to override.
			}

			$value = self::get( $settings, $field['path'], $field['default'] );

			if ( ! empty( $field['is_font'] ) && ! in_array( $value, Layout::google_fonts(), true ) ) {
				continue; // Guards against a corrupted stored value slipping through.
			}
			if ( ! empty( $field['is_font'] ) ) {
				$font_families[] = $value;
			}

			// A field's `css` is either one output target or a list of them
			// (e.g. container_width drives both the content and header widths).
			$outputs = isset( $field['css']['var'] ) ? array( $field['css'] ) : $field['css'];

			foreach ( $outputs as $output ) {
				if ( 'bool' === $field['type'] && isset( $output['true_value'] ) ) {
					// A bool field's stored value (true/false) isn't itself a
					// usable CSS value — print the literal string this output
					// stands for instead (e.g. "uppercase", "700"). Fields
					// whose *default* is false never reach here with $value
					// false (the gating check above already skipped them),
					// so `true_value` alone is a safe fallback for them —
					// only a field whose default is true (e.g. menu_bold)
					// needs `false_value` too, for when it's switched off.
					$final = $value ? $output['true_value'] : ( $output['false_value'] ?? $output['true_value'] );
				} elseif ( isset( $output['transform'] ) ) {
					$final = call_user_func( $output['transform'], $value );
				} else {
					$final = $value;
				}
				$unit = $output['unit'] ?? '';

				$css_value = ! empty( $output['quote'] )
					? "'" . str_replace( "'", '', (string) $final ) . "'"
					: esc_html( (string) $final ) . $unit;

				$declarations[] = esc_html( $output['var'] ) . ':' . $css_value . ';';
			}
		}

		return array(
			'declarations'  => $declarations,
			'font_families' => array_unique( $font_families ),
		);
	}

	/**
	 * `{min, max, default}` for every real `range`-type field, keyed by
	 * its flat name — localized once, generically, instead of each range
	 * field needing its own hand-added entry in `Admin_Page.php`.
	 *
	 * @return array<string, array{min: int, max: int, default: int}>
	 */
	public static function range_bounds(): array {
		$bounds = array();
		foreach ( self::fields() as $field ) {
			if ( 'range' === $field['type'] ) {
				$bounds[ $field['flat'] ] = array(
					'min'     => $field['min'],
					'max'     => $field['max'],
					'default' => $field['default'],
				);
			}
		}
		return $bounds;
	}

	/**
	 * @param mixed $raw Raw, unsanitized value posted for this field.
	 */
	private static function sanitize_value( $raw, array $field ) {
		switch ( $field['type'] ) {
			case 'text':
				return sanitize_text_field( (string) ( $raw ?? '' ) );

			case 'email':
				return sanitize_email( (string) ( $raw ?? '' ) );

			case 'textarea':
				return sanitize_textarea_field( (string) ( $raw ?? '' ) );

			case 'html':
				// A safe subset of HTML (links, bold/italic, line breaks, …) —
				// same tags `wp_kses_post()` allows in a normal post body.
				// No `<script>`/`<style>` — see 'raw_html' for that.
				return wp_kses_post( (string) ( $raw ?? '' ) );

			case 'raw_html':
				// Deliberately UNsanitized — this REST route already requires
				// `manage_options` (see Rest_Controller::check_permission()),
				// and this type exists specifically to hold third-party embed
				// markup (Mailchimp/MailerLite `<script>`/`<style>` snippets)
				// that `wp_kses_post()` would strip and break. Same trust
				// model as WP core's own Additional CSS / Custom HTML widget.
				return (string) ( $raw ?? '' );

			case 'url':
			case 'image':
				return esc_url_raw( (string) ( $raw ?? '' ) );

			case 'bool':
				return ! empty( $raw );

			case 'hex_color':
				return sanitize_hex_color( (string) ( $raw ?? '' ) ) ?: $field['default'];

			case 'select':
				$value = is_string( $raw ) ? sanitize_text_field( $raw ) : '';
				// array_map( 'strval', … ) guards against PHP's own array-key
				// auto-casting: a choices array declared with numeric-string
				// keys (e.g. '2' => '2 Columns') silently becomes int keys
				// the moment the array is built, so array_keys( … ) here
				// would otherwise hand back ints and fail this strict
				// in_array() check against the always-string posted value.
				return in_array( $value, array_map( 'strval', $field['choices'] ), true ) ? $value : $field['default'];

			case 'range':
				if ( ! is_numeric( $raw ) ) {
					return $field['default'];
				}
				$value = (int) $raw;
				return ( $value >= $field['min'] && $value <= $field['max'] ) ? $value : $field['default'];

			case 'order':
				if ( ! is_array( $raw ) ) {
					return $field['default'];
				}
				$ids      = array_values( array_filter( array_map( 'sanitize_key', $raw ) ) );
				$given    = $ids;
				$expected = $field['default'];
				sort( $given );
				sort( $expected );
				return $given === $expected ? $ids : $field['default'];

			case 'zones':
				return self::sanitize_zones( $raw, $field );

			case 'subset_order':
				return self::sanitize_subset_order( $raw, $field );

			case 'social_links':
				return self::sanitize_social_links( $raw, $field );

			default:
				return $field['default'];
		}
	}

	/**
	 * Sanitizes a `zones`-type field: a `{left, center, right}` map of
	 * ordered module-id lists. Unlike `order`, this does NOT require an
	 * exact-set match — a module can legitimately be placed in one zone,
	 * left out of all zones (hidden), or reordered within/between zones
	 * freely. Only real module ids (from `$field['modules']`) are kept,
	 * each at most once across all three zones (first zone it's seen in
	 * wins, matching drag-and-drop's own "a module lives in one place"
	 * rule). Falls back to the real default layout only if nothing valid
	 * survives at all (never-saved, corrupted, or a fully empty payload).
	 *
	 * @param mixed $raw Raw posted value, expected shape `{left:[], center:[], right:[]}`.
	 */
	private static function sanitize_zones( $raw, array $field ): array {
		if ( ! is_array( $raw ) ) {
			return $field['default'];
		}

		$seen      = array();
		$result    = array(
			'left'   => array(),
			'center' => array(),
			'right'  => array(),
		);
		$any_valid = false;

		foreach ( array_keys( $result ) as $zone ) {
			$items = is_array( $raw[ $zone ] ?? null ) ? $raw[ $zone ] : array();
			foreach ( $items as $id ) {
				$id = sanitize_key( $id );
				if ( in_array( $id, $field['modules'], true ) && ! isset( $seen[ $id ] ) ) {
					$result[ $zone ][] = $id;
					$seen[ $id ]       = true;
					$any_valid         = true;
				}
			}
		}

		return $any_valid ? $result : $field['default'];
	}

	/**
	 * Sanitizes a `subset_order`-type field: an ordered list that's a
	 * *subset* of `$field['modules']` (an id missing from the list is
	 * simply hidden — unlike `order`, this does NOT require every real id
	 * to be present). Each id is kept only once, in the order given.
	 * Falls back to the real default order only if nothing valid survives
	 * at all (never-saved, corrupted, or a fully empty payload).
	 *
	 * @param mixed $raw Raw posted value, an array of ids.
	 */
	private static function sanitize_subset_order( $raw, array $field ): array {
		if ( ! is_array( $raw ) ) {
			return $field['default'];
		}

		$seen   = array();
		$result = array();

		foreach ( $raw as $id ) {
			$id = sanitize_key( $id );
			if ( in_array( $id, $field['modules'], true ) && ! isset( $seen[ $id ] ) ) {
				$result[] = $id;
				$seen[ $id ] = true;
			}
		}

		return $result ? $result : $field['default'];
	}

	/**
	 * Sanitizes a `social_links`-type field: a real repeatable-field list,
	 * each row `{id, url}` — `id` must be one of `$field['choices']` (the
	 * theme's real supported networks/icons) and `url` a non-empty URL.
	 * Invalid or empty-URL rows are dropped rather than falling back to a
	 * default; duplicate `id`s keep only their first occurrence. An empty
	 * result (including "never saved") is itself the real default state —
	 * matches the theme's original "no social links configured" output.
	 *
	 * @param mixed $raw Raw posted value, an array of `{id, url}` rows.
	 */
	private static function sanitize_social_links( $raw, array $field ): array {
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$seen   = array();
		$result = array();

		foreach ( $raw as $row ) {
			$id  = is_array( $row ) ? sanitize_key( $row['id'] ?? '' ) : '';
			$url = is_array( $row ) ? esc_url_raw( (string) ( $row['url'] ?? '' ) ) : '';

			if ( '' === $url || ! in_array( $id, $field['choices'], true ) || isset( $seen[ $id ] ) ) {
				continue;
			}

			$seen[ $id ] = true;
			$result[]    = array(
				'id'  => $id,
				'url' => $url,
			);
		}

		return $result;
	}

	/**
	 * Reads a dot-path (e.g. `footer.copyright_text`) out of a nested array.
	 *
	 * @param mixed $default_value Returned when any segment of the path is missing.
	 */
	private static function get( array $source, string $path, $default_value ) {
		$cursor = $source;
		foreach ( explode( '.', $path ) as $key ) {
			if ( ! is_array( $cursor ) || ! array_key_exists( $key, $cursor ) ) {
				return $default_value;
			}
			$cursor = $cursor[ $key ];
		}
		return $cursor;
	}

	/**
	 * Writes a value at a dot-path (e.g. `footer.copyright_text`) inside a
	 * nested array, creating intermediate arrays as needed.
	 *
	 * @param mixed $value Value to assign at the path's final key.
	 */
	private static function set( array &$target, string $path, $value ): void {
		$keys   = explode( '.', $path );
		$last   = array_pop( $keys );
		$cursor = &$target;
		foreach ( $keys as $key ) {
			if ( ! isset( $cursor[ $key ] ) || ! is_array( $cursor[ $key ] ) ) {
				$cursor[ $key ] = array();
			}
			$cursor = &$cursor[ $key ];
		}
		$cursor[ $last ] = $value;
	}
}
