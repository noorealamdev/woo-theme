<?php
/**
 * Layout component.
 *
 * @package Ecombon
 */

namespace Ecombon\Settings;

/**
 * Real read/write access to the theme settings — one option
 * (`ecombon_settings`, a single associative array) holding everything
 * the admin settings page controls: the footer Info Card (logo/description/
 * social links), topbar, branding, and the header/footer builder's
 * zone/order lists.
 *
 * Two real builder mechanisms:
 *
 * - The **header** is a genuine 3-zone builder (left/center/right) — any
 *   of the 5 real modules (logo/navigation/search/account/cart) can be
 *   freely placed in any zone, in any order, including mixed zones (e.g.
 *   an icon next to the logo). This works because `.header-inner` is a
 *   plain flex row with no module-specific positioning of its own — see
 *   `header_zones()`/`header_zone_items()` and the renderer in
 *   `template-parts/header/site-header.php`, which groups consecutive
 *   icon-type modules into their own `<ul>` so mixed zones stay valid
 *   HTML without needing to rewrite the icon partials.
 *
 * - The **footer** is a placement-is-visibility list builder, like the
 *   header zones — a column/item present in `footer_top_items()` /
 *   `footer_bottom_items()` is shown (in that order, via CSS `order`),
 *   absent means hidden. Unlike the header, this is ONE ordered list per
 *   row (not 3 mixable zones): each real footer column keeps its own
 *   distinct markup/Bootstrap width class (`col-lg-4` info/newsletter vs
 *   `col-lg-2` nav-company/nav-customer), so columns can be shown, hidden
 *   and reordered relative to each other, but never swap markup/width
 *   with one another. The `info` column itself is a logo/description/
 *   social-links card (see `info_logo_default()` etc. and
 *   `template-parts/footer/info.php`), not a store contact block —
 *   `ecombon_contact_*` (phone/email/address) has no settings-page field
 *   backing it here; it's left for a future Core Plugin to fill in. Social
 *   links are their own repeatable-field list (`footer_social_links_items()`)
 *   — an empty list is itself "hidden", not a separate toggle.
 *
 * Every default below matches the theme's original hardcoded markup
 * exactly, so installing this feature changes nothing on the live site
 * until a site owner actually saves a change on the settings page.
 */
class Layout {

	const OPTION = 'ecombon_settings';

	/**
	 * The full real settings array, merged with defaults so every real
	 * key is always present even before the settings page has ever been
	 * saved. Uses `Schema::merge()` (a per-field merge), NOT a blanket
	 * `array_replace_recursive`, which corrupts variable-length lists —
	 * see that method's own docblock for the full story.
	 */
	public static function all(): array {
		$stored = get_option( self::OPTION, array() );
		return Schema::merge( is_array( $stored ) ? $stored : array() );
	}

	/**
	 * Real default settings — matches the theme's original hardcoded
	 * output exactly. Built from the single field registry in Schema.php,
	 * so a field only needs to be declared once to get a default here,
	 * a validated sanitizer in Rest_Controller, and a flat key in
	 * `ecombon_settings()`.
	 */
	public static function defaults(): array {
		return Schema::defaults();
	}

	/**
	 * The topbar's real default announcement text. A method (not a class
	 * constant) because `__()` can't run inside a constant expression —
	 * Schema.php calls this for the `topbar.message` field's default.
	 */
	public static function topbar_message_default(): string {
		return __( 'Midseason Sale: 20% Off — Auto Applied at Checkout — Limited Time Only', 'ecombon' );
	}

	const COLOR_PRIMARY_DEFAULT   = '#DC4646';
	const COLOR_SECONDARY_DEFAULT = '#70857A';
	const SITE_WIDTH_DEFAULT      = 'boxed';
	const NEWSLETTER_PROVIDER_DEFAULT = 'theme';

	/**
	 * The newsletter column's signup form source — 'theme' (the theme's
	 * own real static form, `template-parts/footer/newsletter-form.php`)
	 * or 'custom' (a site owner's own embed code, e.g. from Mailchimp or
	 * MailerLite). Defaults to 'theme' so an untouched install renders
	 * exactly the same real form it always has.
	 *
	 * @return array<string, string>
	 */
	public static function newsletter_provider_choices(): array {
		return array(
			'theme'  => __( 'Theme Default Form', 'ecombon' ),
			'custom' => __( 'Custom Embed Code (Mailchimp, MailerLite, etc.)', 'ecombon' ),
		);
	}

	/**
	 * The site's real original body background color — matches the
	 * theme's own `--white` custom property (`#ffffff`). Deliberately
	 * NOT stored as an override of `--white` itself, which is a pervasive
	 * general-purpose token used in ~110 other places across main.css
	 * (buttons, cards, borders, text-on-dark, …) — this gets its own
	 * scoped `--ecombon-body-bg` variable instead.
	 */
	const BODY_BG_DEFAULT = '#ffffff';

	/**
	 * The header nav menu's real original text color — it has no `color`
	 * rule of its own today (`.item-link` only sets one on hover, to
	 * `--primary`), so it just inherits the theme's `--text` (`#101010`).
	 */
	const MENU_COLOR_DEFAULT = '#101010';

	/**
	 * The header nav menu's real original font size — like the color, it
	 * has no `font-size` rule of its own, so it just inherits the body
	 * default (16px).
	 */
	const MENU_FONT_SIZE_DEFAULT = 18;
	const MENU_FONT_SIZE_MIN     = 12;
	const MENU_FONT_SIZE_MAX     = 24;

	/**
	 * The topbar's real original background color — matches the theme's
	 * own `--text` custom property (`#101010`), which is what `.bg-dark`
	 * actually resolves to here (main.css overrides Bootstrap's own
	 * `.bg-dark` to use it), not Bootstrap's generic default.
	 */
	const TOPBAR_BG_DEFAULT = '#101010';

	/**
	 * The topbar message's real original text color — the theme's own
	 * markup applies this via a plain `text-white` class (no CSS rule of
	 * its own, so it never adapted to a custom Topbar Background Color).
	 * `template-parts/header/topbar.php` no longer uses that class — this
	 * default (and its matching `#fff` CSS fallback) keeps the exact same
	 * rendered color, just through a real, overridable setting instead.
	 */
	const TOPBAR_TEXT_DEFAULT = '#ffffff';

	/**
	 * Header/footer background color overrides — both start disabled, so
	 * an untouched install keeps its real original look (header/footer
	 * have no background of their own, they just show the body background
	 * — `--ecombon-body-bg` — through). These defaults are only what the
	 * color picker opens on before a site owner ever turns the toggle on;
	 * they never apply on their own.
	 */
	const HEADER_BG_DEFAULT = '#ffffff';
	const FOOTER_BG_DEFAULT = '#ffffff';

	/**
	 * The footer's text/icon/border color override — gated by the SAME
	 * `footer.background_color_enabled` toggle as `FOOTER_BG_DEFAULT`
	 * (see the `requires` field in `Schema::fields()`), since the whole
	 * point of this field is to keep footer content readable once a site
	 * owner picks a custom (e.g. dark) footer background.
	 */
	const FOOTER_TEXT_DEFAULT = '#ffffff';

	const CONTAINER_WIDTH_DEFAULT = 1440;
	const CONTAINER_WIDTH_MIN     = 1140;
	const CONTAINER_WIDTH_MAX     = 1600;

	/**
	 * The theme's real original base body font size (`body { font-size: 16px; }`
	 * in main.css) — headings and other elements with their own explicit
	 * sizes are unaffected by this, same as any "body font size" control.
	 */
	const FONT_SIZE_BASE_DEFAULT = 16;
	const FONT_SIZE_BASE_MIN     = 14;
	const FONT_SIZE_BASE_MAX     = 20;

	/**
	 * The topbar message's real original font size — it has no font-size
	 * rule of its own today, so it just inherits the body default (16px).
	 */
	const TOPBAR_FONT_SIZE_DEFAULT = 16;
	const TOPBAR_FONT_SIZE_MIN     = 12;
	const TOPBAR_FONT_SIZE_MAX     = 22;

	/**
	 * The header/mega-menu container's (`.container-full`) real original
	 * width — independently adjustable from the main content container.
	 */
	const HEADER_CONTAINER_WIDTH_DEFAULT = 1800;
	const HEADER_CONTAINER_WIDTH_MIN     = 1200;
	const HEADER_CONTAINER_WIDTH_MAX     = 2000;

	/**
	 * @return array<string, string>
	 */
	public static function site_width_choices(): array {
		return array(
			'boxed'      => __( 'Boxed', 'ecombon' ),
			'full-width' => __( 'Full Width', 'ecombon' ),
		);
	}

	/**
	 * The shop grid's real original column count — matches the hardcoded
	 * `tf-col-4` class `woocommerce/archive-product.php` always rendered
	 * before this setting existed. Every other choice here (`tf-col-2`
	 * through `tf-col-6`) is a real, already-shipped CSS class — no new
	 * CSS needed for this field at all.
	 */
	const GRID_COLUMNS_DEFAULT = '4';

	/**
	 * @return array<string, string>
	 */
	public static function grid_columns_choices(): array {
		return array(
			'2' => __( '2 Columns', 'ecombon' ),
			'3' => __( '3 Columns', 'ecombon' ),
			'4' => __( '4 Columns', 'ecombon' ),
			'5' => __( '5 Columns', 'ecombon' ),
			'6' => __( '6 Columns', 'ecombon' ),
		);
	}

	/**
	 * The shop's real original products-per-page count — WooCommerce's own
	 * core default (`wc_get_default_products_per_row()` × `wc_get_default_
	 * product_rows_per_page()` = 4×4), since the theme never declared
	 * `add_theme_support('woocommerce', ['product_grid' => …])` to change
	 * it. Overridden via the real `loop_shop_per_page` filter — see
	 * `Frontend_Output::products_per_page()`.
	 */
	const PRODUCTS_PER_PAGE_DEFAULT = '16';

	/**
	 * @return array<string, string>
	 */
	public static function products_per_page_choices(): array {
		return array(
			'8'  => '8',
			'12' => '12',
			'16' => '16',
			'20' => '20',
			'24' => '24',
			'32' => '32',
		);
	}

	/**
	 * The product card title's real original font size — it has no
	 * `font-size` rule of its own today (`.name-product` only sets
	 * `font-family`), so it just inherits the body default (16px). Shared
	 * by every real `card-product.php` instance (shop grid, related
	 * products, …), not just the /shop/ page — one product card component,
	 * one typography setting.
	 */
	const SHOP_TITLE_FONT_SIZE_DEFAULT = 16;
	const SHOP_TITLE_FONT_SIZE_MIN     = 12;
	const SHOP_TITLE_FONT_SIZE_MAX     = 24;

	/**
	 * The product card's current-price text (`.price-new`) real original
	 * font size — like the title, it has no `font-size` rule of its own,
	 * so it just inherits the body default (16px). The crossed-out
	 * original price (`.price-old`) is deliberately NOT included — it
	 * uses the shared `.text-caption-01` utility class (14px) elsewhere
	 * on the site and stays untouched.
	 */
	const SHOP_META_FONT_SIZE_DEFAULT = 16;
	const SHOP_META_FONT_SIZE_MIN     = 12;
	const SHOP_META_FONT_SIZE_MAX     = 22;

	/**
	 * The product card title's real original font weight — the theme's own
	 * markup applied this via Bootstrap's real `.fw-medium` utility class
	 * (`!important`, so it never adapted to anything); removed from
	 * `template-parts/product/card-product.php` in favor of this real,
	 * overridable setting, which still falls back to the exact same 500.
	 */
	const SHOP_TITLE_FONT_WEIGHT_DEFAULT = '500';

	/**
	 * @return array<string, string>
	 */
	public static function font_weight_choices(): array {
		return array(
			'400' => __( 'Normal', 'ecombon' ),
			'500' => __( 'Medium', 'ecombon' ),
			'600' => __( 'Semibold', 'ecombon' ),
			'700' => __( 'Bold', 'ecombon' ),
		);
	}

	/**
	 * Real stored site width — 'boxed' (the theme's original, unchanged
	 * fixed-max-width containers) or 'full-width' (containers stretch to
	 * fill the actual browser viewport). Falls back to 'boxed' for any
	 * missing/invalid stored value.
	 */
	public static function site_width(): string {
		$value = self::all()['layout']['site_width'] ?? self::SITE_WIDTH_DEFAULT;
		return isset( self::site_width_choices()[ $value ] ) ? $value : self::SITE_WIDTH_DEFAULT;
	}

	/**
	 * Real stored main-content container width in px (only visually
	 * applies while `site_width()` is 'boxed'). Falls back to the real
	 * default for any missing/out-of-range stored value.
	 */
	public static function container_width(): int {
		$value = (int) ( self::all()['layout']['container_width'] ?? self::CONTAINER_WIDTH_DEFAULT );
		if ( $value < self::CONTAINER_WIDTH_MIN || $value > self::CONTAINER_WIDTH_MAX ) {
			return self::CONTAINER_WIDTH_DEFAULT;
		}
		return $value;
	}

	/**
	 * Real stored header/mega-menu container width in px — independent
	 * from `container_width()`. Falls back to the real default for any
	 * missing/out-of-range stored value.
	 */
	public static function header_container_width(): int {
		$value = (int) ( self::all()['header']['container_width'] ?? self::HEADER_CONTAINER_WIDTH_DEFAULT );
		if ( $value < self::HEADER_CONTAINER_WIDTH_MIN || $value > self::HEADER_CONTAINER_WIDTH_MAX ) {
			return self::HEADER_CONTAINER_WIDTH_DEFAULT;
		}
		return $value;
	}

	/**
	 * @return array<string, string>
	 */
	public static function social_networks(): array {
		return array(
			'facebook'  => __( 'Facebook', 'ecombon' ),
			'x'         => __( 'X (Twitter)', 'ecombon' ),
			'instagram' => __( 'Instagram', 'ecombon' ),
			'tiktok'    => __( 'TikTok', 'ecombon' ),
			'snapchat'  => __( 'Snapchat', 'ecombon' ),
			'pinterest' => __( 'Pinterest', 'ecombon' ),
			'youtube'   => __( 'YouTube', 'ecombon' ),
		);
	}

	/**
	 * Every real Google Fonts family (1,942 of them), generated from
	 * Google's own public metadata endpoint — see google-fonts-list.php's
	 * own header for how to regenerate it.
	 *
	 * @return string[]
	 */
	public static function google_fonts(): array {
		static $fonts = null;
		if ( null === $fonts ) {
			$fonts = require __DIR__ . '/google-fonts-list.php';
		}
		return $fonts;
	}

	/**
	 * Every real header module the 3-zone builder can place, id => label.
	 *
	 * @return array<string, string>
	 */
	public static function header_toggleable(): array {
		return array(
			'logo'       => __( 'Logo', 'ecombon' ),
			'navigation' => __( 'Navigation Menu', 'ecombon' ),
			'search'     => __( 'Search Icon', 'ecombon' ),
			'account'    => __( 'Account Icon', 'ecombon' ),
			'cart'       => __( 'Cart Icon', 'ecombon' ),
		);
	}

	/**
	 * The header's real default zone → module assignment — logo left,
	 * navigation centered, icons right, exactly matching the theme's
	 * original hardcoded layout.
	 *
	 * @return array{left: string[], center: string[], right: string[]}
	 */
	public static function header_zones_default(): array {
		return array(
			'left'   => array( 'logo' ),
			'center' => array( 'navigation' ),
			'right'  => array( 'search', 'account', 'cart' ),
		);
	}

	/**
	 * @return array<string, string>
	 */
	public static function footer_top_elements(): array {
		return array(
			'info'         => __( 'Info Card & Social', 'ecombon' ),
			'nav-company'  => __( 'Company Menu', 'ecombon' ),
			'nav-customer' => __( 'Customer Care Menu', 'ecombon' ),
			'newsletter'   => __( 'Newsletter Signup', 'ecombon' ),
		);
	}

	/**
	 * @return string[]
	 */
	public static function footer_top_defaults(): array {
		return array( 'info', 'nav-company', 'nav-customer', 'newsletter' );
	}

	/**
	 * @return array<string, string>
	 */
	public static function footer_bottom_elements(): array {
		return array(
			'copyright'     => __( 'Copyright Text', 'ecombon' ),
			'payment-icons' => __( 'Payment Icons', 'ecombon' ),
		);
	}

	/**
	 * @return string[]
	 */
	public static function footer_bottom_defaults(): array {
		return array( 'copyright', 'payment-icons' );
	}

	/**
	 * Real default footer content text — methods (not class constants)
	 * because `__()` can't run inside a constant expression.
	 */
	public static function newsletter_heading_default(): string {
		return __( 'Newsletter', 'ecombon' );
	}

	public static function newsletter_description_default(): string {
		return __( 'Subscribe for store updates and discounts.', 'ecombon' );
	}

	public static function company_heading_default(): string {
		return __( 'Company', 'ecombon' );
	}

	public static function customer_heading_default(): string {
		return __( 'Customer Care', 'ecombon' );
	}

	/**
	 * The footer Info Card's real default content — no natural default for
	 * either (an untouched install falls back to the real WP custom logo,
	 * or the site title as plain text, at render time — see
	 * `template-parts/footer/info.php` — not to a stored value here).
	 */
	public static function info_logo_default(): string {
		return '';
	}

	public static function info_description_default(): string {
		return '';
	}

	/**
	 * The footer Info Card's social links repeater — no rows by default,
	 * matching the theme's original "no social links configured" output.
	 *
	 * @return array<int, array{id: string, url: string}>
	 */
	public static function social_links_default(): array {
		return array();
	}

	/**
	 * `{year}` and `{site_name}` are replaced at render time — see
	 * `template-parts/footer/copyright.php`.
	 */
	public static function copyright_text_default(): string {
		return __( '©{year} {site_name}. All Rights Reserved.', 'ecombon' );
	}

	/**
	 * The footer's payment icons block is a single uploaded image (e.g. a
	 * combined icon strip) — no natural default, so an untouched install
	 * falls back to the theme's own real bundled icon set (see the
	 * hardcoded default array in `template-parts/footer/payment-icons.php`,
	 * unchanged from before this setting existed).
	 */
	public static function payment_icons_image_default(): string {
		return '';
	}

	/**
	 * Real stored social links repeater rows — reads the raw stored value
	 * directly (same reasoning as every other list accessor above) and
	 * keeps only rows whose `id` is still a real network and whose `url`
	 * is a non-empty string; each `id` kept only once (first occurrence).
	 * An empty result is itself the real default state, not a fallback.
	 *
	 * @return array<int, array{id: string, url: string}>
	 */
	public static function footer_social_links_items(): array {
		$stored     = self::raw_stored( 'footer', 'social_links' );
		$valid_ids  = array_keys( self::social_networks() );

		if ( ! is_array( $stored ) ) {
			return self::social_links_default();
		}

		$seen   = array();
		$result = array();

		foreach ( $stored as $row ) {
			$id  = is_array( $row ) ? (string) ( $row['id'] ?? '' ) : '';
			$url = is_array( $row ) ? (string) ( $row['url'] ?? '' ) : '';

			if ( '' === $url || ! in_array( $id, $valid_ids, true ) || isset( $seen[ $id ] ) ) {
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
	 * Whether a real header module is currently placed in any of the 3
	 * zones — a module not placed anywhere is simply hidden.
	 */
	public static function is_header_element_visible( string $id ): bool {
		foreach ( self::header_zones() as $items ) {
			if ( in_array( $id, $items, true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Whether the header should stick to the top of the viewport on
	 * scroll — real, default-on JS behavior (see `headerSticky()` in
	 * assets/js/main.js) that this setting can turn off.
	 */
	public static function header_sticky(): bool {
		return (bool) ( self::all()['header']['sticky'] ?? true );
	}

	/**
	 * Whether the mobile hamburger menu (offcanvas nav) should be used at
	 * every viewport width, including desktop — real, default-off — an
	 * untouched install keeps the theme's original responsive behavior
	 * (inline nav on desktop, hamburger below the `xl` breakpoint).
	 */
	public static function force_mobile_menu(): bool {
		return (bool) ( self::all()['header']['force_mobile_menu'] ?? false );
	}

	/**
	 * Real stored header zone → module assignment — validated the same
	 * way `valid_order()` protects the footer's order lists: only ids
	 * that are still real header modules are kept, deduplicated across
	 * zones, and the whole thing falls back to the real default layout
	 * if nothing valid survives (never-saved, corrupted, or a stale set
	 * from before a module was added/removed).
	 *
	 * @return array{left: string[], center: string[], right: string[]}
	 */
	public static function header_zones(): array {
		// Reads the RAW stored option directly rather than self::all() —
		// array_replace_recursive (which all() uses to merge defaults
		// over stored data) merges numeric-indexed lists by index, not by
		// value, so a *shorter* stored zone (e.g. a module dragged out)
		// would otherwise get padded back out with leftover default
		// items at its trailing indices.
		$stored_option = get_option( self::OPTION, array() );
		$stored        = is_array( $stored_option ) ? ( $stored_option['header']['zones'] ?? array() ) : array();
		$defaults      = self::header_zones_default();
		$valid_ids     = array_keys( self::header_toggleable() );

		if ( ! is_array( $stored ) ) {
			return $defaults;
		}

		$seen      = array();
		$result    = array(
			'left'   => array(),
			'center' => array(),
			'right'  => array(),
		);
		$any_valid = false;

		foreach ( array_keys( $result ) as $zone ) {
			$items = is_array( $stored[ $zone ] ?? null ) ? $stored[ $zone ] : array();
			foreach ( $items as $id ) {
				if ( in_array( $id, $valid_ids, true ) && ! isset( $seen[ $id ] ) ) {
					$result[ $zone ][] = $id;
					$seen[ $id ]       = true;
					$any_valid         = true;
				}
			}
		}

		return $any_valid ? $result : $defaults;
	}

	/**
	 * The real stored module order for a single zone ('left'/'center'/'right').
	 *
	 * @return string[]
	 */
	public static function header_zone_items( string $zone ): array {
		return self::header_zones()[ $zone ] ?? array();
	}

	/**
	 * Real stored footer top-row order — like the header zones, this is a
	 * *subset* of the 4 real columns (a column not present is hidden), not
	 * a fixed-size permutation, so it's validated and defaulted the same
	 * way: read the raw stored option directly (not through the
	 * `array_replace_recursive`-merged `self::all()`, which would pad a
	 * shorter stored list back out with stale trailing default columns —
	 * see the header zones' own docblock for the full explanation) and
	 * keep only real, deduplicated column ids.
	 *
	 * @return string[]
	 */
	public static function footer_top_items(): array {
		return self::valid_subset(
			self::raw_stored( 'footer', 'top' ),
			self::footer_top_defaults(),
			array_keys( self::footer_top_elements() )
		);
	}

	/**
	 * Real stored footer bottom-bar order — same subset validation as
	 * `footer_top_items()`.
	 *
	 * @return string[]
	 */
	public static function footer_bottom_items(): array {
		return self::valid_subset(
			self::raw_stored( 'footer', 'bottom' ),
			self::footer_bottom_defaults(),
			array_keys( self::footer_bottom_elements() )
		);
	}

	/**
	 * Reads a raw, unmerged value straight out of the stored option —
	 * bypassing `self::all()`'s `array_replace_recursive` merge, which
	 * corrupts variable-length lists (see `footer_top_items()`'s docblock).
	 *
	 * @return mixed
	 */
	private static function raw_stored( string $section, string $key ) {
		$stored_option = get_option( self::OPTION, array() );
		return is_array( $stored_option ) ? ( $stored_option[ $section ][ $key ] ?? array() ) : array();
	}

	/**
	 * A stored list is only used if it's an array — each entry is kept
	 * only when it's still a real, valid id (deduplicated); anything else
	 * (never saved, corrupted, or referencing a since-removed id) is
	 * simply dropped rather than invalidating the whole list. Falls back
	 * to the real default order only if *nothing* valid survives at all.
	 *
	 * @param mixed    $stored    Raw stored value.
	 * @param string[] $defaults  Real default order for this list.
	 * @param string[] $valid_ids Every real id this list may contain.
	 * @return string[]
	 */
	private static function valid_subset( $stored, array $defaults, array $valid_ids ): array {
		if ( ! is_array( $stored ) ) {
			return $defaults;
		}

		$seen   = array();
		$result = array();

		foreach ( $stored as $id ) {
			if ( in_array( $id, $valid_ids, true ) && ! isset( $seen[ $id ] ) ) {
				$result[] = $id;
				$seen[ $id ] = true;
			}
		}

		return $result ? $result : $defaults;
	}
}
