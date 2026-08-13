# Bundled required plugins

The Noorifa theme ships **Noorifa Core** inside itself and installs it for
the user on activation (see `inc/Setup/RequiredPlugins.php`). WooCommerce is
**not** bundled — it is installed from wordpress.org on demand.

## Files

- `noorifa-core.zip` — the built, distributable Noorifa Core plugin. This is
  what gets installed when the user clicks *Install &amp; Activate* in the
  admin notice.

## Refreshing the bundled plugin (do this on every Noorifa Core release)

1. Build the plugin zip from the plugin repo:

   ```bash
   cd wp-content/plugins/norofia-core
   npm run release          # runs build + plugin-zip → noorifa-core.zip
   ```

2. Copy the fresh zip over the bundled copy:

   ```bash
   cp wp-content/plugins/norofia-core/noorifa-core.zip \
      wp-content/themes/ecombon/inc/required-plugins/noorifa-core.zip
   rm wp-content/plugins/norofia-core/noorifa-core.zip
   ```

3. Bump `RequiredPlugins::BUNDLED_CORE_VERSION` in
   `inc/Setup/RequiredPlugins.php` to match the plugin's new version. This is
   what triggers the *Update Noorifa Core* notice for sites running an older
   copy — the theme install detects it by the plugin's `NOORIFA_CORE_VERSION`
   constant, so it works regardless of which folder the plugin lives in.

Keep the version constant and the zip in sync; that pair is the whole
update mechanism.
