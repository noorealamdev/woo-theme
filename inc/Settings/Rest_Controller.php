<?php
/**
 * Rest_Controller component.
 *
 * @package Ecombon
 */

namespace Ecombon\Settings;

use Ecombon\Setup\ComponentInterface;

/**
 * Real `ecombon/v1/settings` REST route the React settings app reads
 * from and saves to via `wp.apiFetch` — same real, modern persistence
 * mechanism Gutenberg/Site Editor itself uses, not an admin-post form.
 */
class Rest_Controller implements ComponentInterface {

	const NAMESPACE_URI = 'ecombon/v1';
	const ROUTE          = '/settings';

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the GET/POST route.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE_URI,
			self::ROUTE,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'reset_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Only real site administrators may read or write theme settings.
	 */
	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @return \WP_REST_Response
	 */
	public function get_settings(): \WP_REST_Response {
		return new \WP_REST_Response( Layout::all() );
	}

	/**
	 * Sanitizes and saves the posted settings, then returns the real
	 * saved value (so the app can confirm what actually persisted).
	 *
	 * @param \WP_REST_Request $request Real REST request.
	 * @return \WP_REST_Response
	 */
	public function save_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$posted = $request->get_json_params();
		if ( ! is_array( $posted ) ) {
			$posted = array();
		}

		update_option( Layout::OPTION, Schema::sanitize( $posted ) );

		return new \WP_REST_Response( Layout::all() );
	}

	/**
	 * Deletes the real stored option outright — `Layout::all()` then just
	 * falls back to `Schema::defaults()` for every field, same as a fresh
	 * install that has never saved anything.
	 *
	 * @return \WP_REST_Response
	 */
	public function reset_settings(): \WP_REST_Response {
		delete_option( Layout::OPTION );

		return new \WP_REST_Response( Layout::all() );
	}
}
