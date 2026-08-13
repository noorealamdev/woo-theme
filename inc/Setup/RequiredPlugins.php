<?php
/**
 * Required plugins component.
 *
 * @package Noorifa
 */

namespace Noorifa\Setup;

/**
 * A lightweight, self-contained alternative to TGMPA: prompts the site
 * owner to install/activate the plugins the Noorifa theme needs, and can
 * install Noorifa Core straight from the copy bundled inside the theme —
 * no marketplace round-trip.
 *
 *  - Noorifa Core — installed from the bundled zip at
 *    inc/required-plugins/noorifa-core.zip (this theme's own free, GPL
 *    companion plugin). Detected by its NOORIFA_CORE_VERSION constant so
 *    it's recognised no matter which folder it lives in.
 *  - WooCommerce  — installed from the wordpress.org repository (never
 *    bundled; it's large and repo-hosted).
 *
 * Shows a one-click Install / Activate / Update admin notice until both
 * are active and up to date, then stays silent. Admin-only.
 */
class RequiredPlugins implements ComponentInterface {

	/**
	 * Version of Noorifa Core bundled in this theme. Bump this whenever
	 * inc/required-plugins/noorifa-core.zip is refreshed (see that folder's
	 * README) so the "update available" notice fires for anyone running an
	 * older copy of the plugin.
	 */
	const BUNDLED_CORE_VERSION = '1.0.0';

	/**
	 * admin-post action name (also used as the nonce action).
	 */
	const ACTION = 'noorifa_required_plugin';

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_notices', array( $this, 'render_notice' ) );
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_action' ) );
	}

	/**
	 * The plugins this theme requires, keyed by a short id.
	 *
	 * @return array[]
	 */
	private function plugins(): array {
		return array(
			'noorifa-core' => array(
				'name'   => 'Noorifa Core',
				'file'   => 'noorifa-core/noorifa-core.php',
				'slug'   => 'noorifa-core',
				'source' => 'bundled',
			),
			'woocommerce'  => array(
				'name'   => 'WooCommerce',
				'file'   => 'woocommerce/woocommerce.php',
				'slug'   => 'woocommerce',
				'source' => 'wporg',
			),
		);
	}

	/**
	 * Absolute path to the bundled Noorifa Core zip.
	 */
	private function bundled_zip(): string {
		return get_template_directory() . '/inc/required-plugins/noorifa-core.zip';
	}

	/**
	 * Whether a required plugin is active. Noorifa Core is detected by its
	 * own constant (folder-name independent); WooCommerce by its main class.
	 *
	 * @param string $id Plugin id.
	 */
	private function is_active( string $id ): bool {
		if ( 'noorifa-core' === $id ) {
			return defined( 'NOORIFA_CORE_VERSION' );
		}
		if ( 'woocommerce' === $id ) {
			return class_exists( 'WooCommerce' );
		}
		return false;
	}

	/**
	 * Whether a required plugin's files are present (installed, maybe
	 * inactive).
	 *
	 * @param array $plugin Plugin definition.
	 */
	private function is_installed( array $plugin ): bool {
		return file_exists( WP_PLUGIN_DIR . '/' . $plugin['file'] );
	}

	/**
	 * Resolves the action each required plugin needs: 'install', 'activate',
	 * 'update', or absent when nothing is needed.
	 *
	 * @return array<string,string> id => action.
	 */
	private function pending(): array {
		$pending = array();

		foreach ( $this->plugins() as $id => $plugin ) {
			if ( $this->is_active( $id ) ) {
				if ( 'noorifa-core' === $id
					&& defined( 'NOORIFA_CORE_VERSION' )
					&& version_compare( NOORIFA_CORE_VERSION, self::BUNDLED_CORE_VERSION, '<' )
					&& file_exists( $this->bundled_zip() ) ) {
					$pending[ $id ] = 'update';
				}
				continue;
			}

			$pending[ $id ] = $this->is_installed( $plugin ) ? 'activate' : 'install';
		}

		return $pending;
	}

	/**
	 * Renders the admin notice with a one-click action per pending plugin.
	 */
	public function render_notice(): void {
		if ( ! current_user_can( 'install_plugins' ) && ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$this->render_flash();

		$pending = $this->pending();
		if ( ! $pending ) {
			return;
		}

		$plugins = $this->plugins();
		$labels  = array(
			'install'  => __( 'Install & Activate %s', 'noorifa' ),
			'activate' => __( 'Activate %s', 'noorifa' ),
			'update'   => __( 'Update %s', 'noorifa' ),
		);
		?>
		<div class="notice notice-warning">
			<p><strong><?php esc_html_e( 'Noorifa theme — required plugins', 'noorifa' ); ?></strong></p>
			<p><?php esc_html_e( 'The Noorifa theme needs the following plugins to work correctly:', 'noorifa' ); ?></p>
			<p>
				<?php foreach ( $pending as $id => $action ) : ?>
					<?php
					$plugin = $plugins[ $id ];
					$url    = wp_nonce_url(
						add_query_arg(
							array(
								'action' => self::ACTION,
								'plugin' => $id,
								'do'     => $action,
							),
							admin_url( 'admin-post.php' )
						),
						self::ACTION
					);
					?>
					<a href="<?php echo esc_url( $url ); ?>" class="button button-primary" style="margin:0 8px 4px 0;">
						<?php echo esc_html( sprintf( wp_strip_all_tags( $labels[ $action ] ), $plugin['name'] ) ); ?>
					</a>
				<?php endforeach; ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Shows the success/error message set after an install/activate/update.
	 */
	private function render_flash(): void {
		$flag = isset( $_GET['noorifa_rp'] ) ? sanitize_key( wp_unslash( $_GET['noorifa_rp'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flash flag, the action itself is nonce-checked.

		if ( 'done' === $flag ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Plugin action completed.', 'noorifa' ) . '</p></div>';
			return;
		}

		if ( 'error' === $flag ) {
			$message = get_transient( 'noorifa_rp_error_' . get_current_user_id() );
			delete_transient( 'noorifa_rp_error_' . get_current_user_id() );
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $message ? $message : __( 'The plugin action failed. Please try again or install manually.', 'noorifa' ) ) . '</p></div>';
		}
	}

	/**
	 * Handles the install/activate/update request from the notice buttons.
	 */
	public function handle_action(): void {
		check_admin_referer( self::ACTION );

		$id = isset( $_GET['plugin'] ) ? sanitize_key( wp_unslash( $_GET['plugin'] ) ) : '';
		$do = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( $_GET['do'] ) ) : '';

		$plugins = $this->plugins();
		if ( ! isset( $plugins[ $id ] ) || ! in_array( $do, array( 'install', 'activate', 'update' ), true ) ) {
			$this->redirect_error( __( 'Invalid request.', 'noorifa' ) );
		}

		$needs_install = ( 'install' === $do || 'update' === $do );
		if ( $needs_install && ! current_user_can( 'install_plugins' ) ) {
			$this->redirect_error( __( 'You are not allowed to install plugins.', 'noorifa' ) );
		}
		if ( ! current_user_can( 'activate_plugins' ) ) {
			$this->redirect_error( __( 'You are not allowed to activate plugins.', 'noorifa' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$plugin = $plugins[ $id ];

		if ( 'activate' === $do ) {
			$this->finish_activate( $plugin );
		}

		// install or update.
		$overwrite = ( 'update' === $do );
		$installed = $this->install_plugin( $plugin, $overwrite );

		if ( is_wp_error( $installed ) ) {
			$this->redirect_error( $installed->get_error_message() );
		}

		$this->finish_activate( $plugin );
	}

	/**
	 * Activates the plugin (no-op if already active) and redirects back.
	 *
	 * @param array $plugin Plugin definition.
	 */
	private function finish_activate( array $plugin ): void {
		if ( ! is_plugin_active( $plugin['file'] ) ) {
			$result = activate_plugin( $plugin['file'] );
			if ( is_wp_error( $result ) ) {
				$this->redirect_error( $result->get_error_message() );
			}
		}
		$this->redirect_done();
	}

	/**
	 * Installs (or overwrites, for updates) a required plugin from its
	 * source — the bundled zip for Noorifa Core, wordpress.org for others.
	 *
	 * @param array $plugin    Plugin definition.
	 * @param bool  $overwrite Whether to overwrite an existing copy (update).
	 * @return true|\WP_Error
	 */
	private function install_plugin( array $plugin, bool $overwrite ) {
		WP_Filesystem();

		if ( 'bundled' === $plugin['source'] ) {
			$package = $this->bundled_zip();
			if ( ! file_exists( $package ) ) {
				return new \WP_Error( 'noorifa_missing_zip', __( 'The bundled plugin file is missing from the theme.', 'noorifa' ) );
			}
		} else {
			$info = plugins_api( 'plugin_information', array( 'slug' => $plugin['slug'], 'fields' => array( 'sections' => false ) ) );
			if ( is_wp_error( $info ) || empty( $info->download_link ) ) {
				return new \WP_Error( 'noorifa_wporg', __( 'Could not reach wordpress.org to download the plugin.', 'noorifa' ) );
			}
			$package = $info->download_link;
		}

		$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );

		if ( $overwrite ) {
			add_filter( 'upgrader_package_options', array( $this, 'overwrite_package_options' ) );
		}

		$result = $upgrader->install( $package );

		if ( $overwrite ) {
			remove_filter( 'upgrader_package_options', array( $this, 'overwrite_package_options' ) );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( true !== $result ) {
			return new \WP_Error( 'noorifa_install_failed', __( 'The plugin could not be installed.', 'noorifa' ) );
		}

		return true;
	}

	/**
	 * Lets install() overwrite an existing plugin directory (used only for
	 * the bundled-plugin update path).
	 *
	 * @param array $options Upgrader package options.
	 * @return array
	 */
	public function overwrite_package_options( array $options ): array {
		$options['clear_destination']           = true;
		$options['abort_if_destination_exists'] = false;
		return $options;
	}

	/**
	 * Redirects back to the referring admin page with a success flag.
	 */
	private function redirect_done(): void {
		wp_safe_redirect( add_query_arg( 'noorifa_rp', 'done', $this->back_url() ) );
		exit;
	}

	/**
	 * Stores an error message and redirects back with an error flag.
	 *
	 * @param string $message Human-readable error.
	 */
	private function redirect_error( string $message ): void {
		set_transient( 'noorifa_rp_error_' . get_current_user_id(), $message, MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( 'noorifa_rp', 'error', $this->back_url() ) );
		exit;
	}

	/**
	 * The admin page to return to after an action.
	 */
	private function back_url(): string {
		$referer = wp_get_referer();
		return $referer ? $referer : admin_url();
	}
}
