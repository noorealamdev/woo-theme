=== Noorifa ===
Contributors: noorealam
Tags: e-commerce, custom-menu, featured-images, threaded-comments, translation-ready
Requires at least: 6.4
Tested up to: 6.6
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A conversion-focused WooCommerce theme with a visual header/footer builder, a block-based product-page layout system, and built-in A/B testing.

== Description ==

Noorifa is a WooCommerce theme built around one idea: every part of your store — layout, product pages, popups, header and footer — should be configurable visually, without editing a single file.

* **Visual header & footer builder** — drag-and-drop zones for logo, navigation, search, account, cart, and a free HTML slot; a placement-based footer builder for the info card, nav columns, newsletter and payment icons
* **Product Layouts** — build a completely custom single-product page from real WordPress blocks (Hero, Before/After comparison, Feature Cards, Video Testimonials, Trust Badges, Urgency, Tabs, Reviews and more), then assign it per product, per category, or site-wide
* **A/B testing for product layouts** — run two layouts on the same product, split traffic 50/50, and track impressions, add-to-cart rate and purchase rate for each variant from a dedicated dashboard, with a confidence indicator to help you call a winner
* **Modern WooCommerce Cart & Checkout** — built on WooCommerce's own Cart and Checkout blocks, fully restyled to match the theme, not the legacy shortcode templates
* **Popup builder** — promotional/newsletter popups with configurable trigger (immediate, delay, scroll, exit-intent, click), frequency capping, and page targeting
* **GDPR-ready cookie consent banner** — optional, configurable message, button and position
* **Inline checkout** — an on-page cash-on-delivery order form for landing pages, no cart or separate checkout step required
* **Built-in SEO** — automatic meta descriptions, Open Graph/Twitter Card tags, and Organization/WebSite/BreadcrumbList structured data, generated from real page content
* **Performance-minded by default** — strips unnecessary WordPress core head output, loads scripts conditionally per page type, and offers optional cache-busting/XML-RPC toggles
* Custom CSS/JS panel, block-level visibility (mobile/tablet/desktop) and scroll animations, import/export for settings

== Installation ==

1. Upload the theme to `/wp-content/themes/noorifa`, or install it through Appearance → Themes → Add New → Upload Theme.
2. Activate the theme through the Appearance → Themes screen.
3. Install and activate WooCommerce, then the required Noorifa Core plugin (the theme will prompt you if it's missing).
4. Go to the Noorifa menu in your dashboard sidebar to configure branding, header/footer, shop, popups and the rest of the theme's settings.
5. See the full handbook under `docs/documentation.html` in the theme folder for a complete walkthrough.

== Frequently Asked Questions ==

= Does this theme require a page builder plugin? =

No. Product pages, popups, and the header/footer are all built from the theme's own visual builders and the Noorifa Core plugin's blocks — no third-party page builder is needed or supported.

= Does the A/B testing feature slow down my site? =

No. Bucketing and stat logging happen inline in the normal WordPress request — no extra JavaScript beacon or third-party service is involved.

== Changelog ==

= 1.0.0 =
* Initial release.
