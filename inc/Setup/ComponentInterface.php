<?php
/**
 * Component_Interface.
 *
 * @package Ecombon
 */

namespace Ecombon\Setup;

/**
 * Contract every theme setup component must implement.
 *
 * Keeps `Theme` decoupled from what each component actually does — it only
 * ever calls `initialize()`.
 */
interface ComponentInterface {

	/**
	 * Hooks the component into WordPress.
	 */
	public function initialize(): void;
}
