<?php
/**
 * Theme bootstrap class.
 *
 * @package Ecombon
 */

namespace Ecombon\Setup;

/**
 * Boots the theme's presentation-layer setup components.
 *
 * This is deliberately lightweight: it only wires up markup, assets, menus
 * and template behaviour. Anything resembling business logic (settings
 * storage, REST endpoints, data processing) belongs in the Ecombon Core
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
			new Assets(),
			new NavMenus(),
			new WidgetAreas(),
			new \Ecombon\Hooks\TemplateHooks(),
		);

		if ( class_exists( 'WooCommerce' ) ) {
			$this->components[] = new \Ecombon\WooCommerce\CatalogOrdering();
			$this->components[] = new \Ecombon\WooCommerce\CartFragments();
			$this->components[] = new \Ecombon\WooCommerce\QuantityStepper();
			$this->components[] = new \Ecombon\WooCommerce\BuyItNow();
			$this->components[] = new \Ecombon\WooCommerce\VariationPricing();
			$this->components[] = new \Ecombon\WooCommerce\CheckoutFields();
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
	 * Maps `Ecombon\{Namespace}\{Class}` to `inc/{Namespace}/{Class}.php`.
	 *
	 * @param string $class_name Fully qualified class name being requested.
	 */
	private function autoload( string $class_name ): bool {
		$prefix = 'Ecombon\\';

		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return false;
		}

		$relative_path = str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) );
		$file          = ECOMBON_THEME_DIR . '/inc/' . $relative_path . '.php';

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
