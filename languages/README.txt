Translating the Noorifa theme
==============================

Every user-facing string in this theme's PHP is already wrapped for
translation (text domain: "noorifa"), and WordPress core's own strings
(Add to Cart wording provided by WooCommerce, date formats, etc.) are
translated separately by WordPress's/WooCommerce's own language packs —
you don't need to do anything for those beyond picking a language under
Settings > General > Site Language.

To translate the theme's own strings (e.g. into Bengali):

1. Open languages/noorifa.pot in Poedit (or Loco Translate inside
   wp-admin) and create a new translation for your language.

2. IMPORTANT — save the compiled file as just the locale code, with NO
   "noorifa-" prefix:

       languages/bn_BD.mo     (correct — Bengali/Bangladesh)
       languages/noorifa-bn_BD.mo   (WRONG — will not load)

   This is the opposite of the usual WordPress.org plugin/theme
   convention (which prefixes with the text domain) — it only applies
   because these files live inside the theme's own folder rather than
   wp-content/languages/themes/. Getting the filename wrong fails
   completely silently: every string just quietly stays in English, with
   no error anywhere.

3. Set the site to that language under Settings > General > Site
   Language (e.g. "বাংলা"), which also prompts WordPress to download the
   matching core/WooCommerce language packs.

Common locale codes: Bengali (Bangladesh) = bn_BD.

Regenerating noorifa.pot after adding new translatable strings to the
theme requires WP-CLI (`wp i18n make-pot . languages/noorifa.pot`) — not
available in this environment when this file was generated, so it was
built with an equivalent custom extraction script instead. If WP-CLI is
available for you, it's the more thorough option to reach for next.
