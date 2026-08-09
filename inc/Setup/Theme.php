<?php
/**
 * Theme bootstrap class.
 *
 * @package Noorifa
 */

namespace Noorifa\Setup;

/**
 * Boots the theme's presentation-layer setup components.
 *
 * This is deliberately lightweight: it only wires up markup, assets, menus
 * and template behaviour. Anything resembling business logic (settings
 * storage, REST endpoints, data processing) belongs in the Noorifa Core
 * plugin, not here.
 */
final class Theme {

	/**
	 * Singleton instance.
	 *
	 * @var Theme|null
	 */
	private static ?Theme $instance = null;

	/**
	 * Active setup components.
	 *
	 * @var ComponentInterface[]
	 */
	private array $components = array();

	/**
	 * Retrieves the singleton instance, creating it on first call.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Registers the autoloader and the default components.
	 */
	private function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );

		$this->components = array(
			new ThemeSupport(),
			new Performance(),
			new SEO(),
			new Integrations(),
			new CustomCode(),
			new Assets(),
			new Icons(),
			new NavMenus(),
			new NavMenuAdminUi(),
			new \Noorifa\Hooks\TemplateHooks(),
			new \Noorifa\Search\LiveSearch(),
			new \Noorifa\Settings\Admin_Page(),
			new \Noorifa\Settings\Migrate_Tool(),
			new \Noorifa\Settings\Rest_Controller(),
			new \Noorifa\Settings\Frontend_Output(),
		);

		if ( class_exists( 'WooCommerce' ) ) {
			$this->components[] = new \Noorifa\WooCommerce\CatalogOrdering();
			$this->components[] = new \Noorifa\WooCommerce\CartFragments();
			$this->components[] = new \Noorifa\WooCommerce\QuantityStepper();
			$this->components[] = new \Noorifa\WooCommerce\BuyItNow();
			$this->components[] = new \Noorifa\WooCommerce\VariationPricing();
			$this->components[] = new \Noorifa\WooCommerce\CheckoutFields();
			$this->components[] = new \Noorifa\WooCommerce\ProductPageLayout();
		}
	}

	/**
	 * Hooks every registered component into WordPress.
	 */
	public function initialize(): void {
		foreach ( $this->components as $component ) {
			$component->initialize();
		}
	}

	/**
	 * Maps `Noorifa\{Namespace}\{Class}` to `inc/{Namespace}/{Class}.php`.
	 *
	 * @param string $class_name Fully qualified class name being requested.
	 */
	private function autoload( string $class_name ): bool {
		$prefix = 'Noorifa\\';

		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return false;
		}

		$relative_path = str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) );
		$file          = NOORIFA_THEME_DIR . '/inc/' . $relative_path . '.php';

		if ( ! file_exists( $file ) ) {
			return false;
		}

		require_once $file;

		return true;
	}

	/**
	 * Prevents cloning of the singleton.
	 */
	private function __clone() {}
}
