<?php
/**
 * Migrate URLs tool.
 *
 * @package Noorifa
 */

namespace Noorifa\Settings;

use Noorifa\Setup\ComponentInterface;

/**
 * A "Migrate URLs" admin page under the Noorifa menu: a serialization-safe
 * database search & replace, for fixing old-site URLs (e.g. localhost) after
 * cloning a site. Always previews (dry run) first; applying is a separate,
 * explicit step. Admin-only and nonce-protected.
 */
class Migrate_Tool implements ComponentInterface {

	/**
	 * Submenu page slug.
	 */
	const SLUG = 'noorifa-migrate-urls';

	/**
	 * Nonce action.
	 */
	const NONCE = 'noorifa_migrate_urls';

	/**
	 * Text-ish MySQL column types worth scanning.
	 *
	 * @var string[]
	 */
	private $text_types = array( 'char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext' );

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		// Priority 11 so the parent "Noorifa" menu (Admin_Page, priority 10)
		// is registered first.
		add_action( 'admin_menu', array( $this, 'register_page' ), 11 );
	}

	/**
	 * Adds the submenu under the shared Noorifa menu.
	 *
	 * @return void
	 */
	public function register_page(): void {
		$parent = defined( 'NOORIFA_ADMIN_MENU_SLUG' ) ? NOORIFA_ADMIN_MENU_SLUG : 'noorifa-settings';

		add_submenu_page(
			$parent,
			__( 'Migrate URLs', 'noorifa' ),
			__( 'Migrate URLs', 'noorifa' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render' )
		);
	}

	/**
	 * Renders the tool and processes a dry-run / apply submission.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$from    = '';
		$to      = '';
		$results = null;
		$applied = false;

		if ( isset( $_POST['noorifa_migrate_submit'] ) ) {
			check_admin_referer( self::NONCE );

			$from    = isset( $_POST['noorifa_from'] ) ? esc_url_raw( wp_unslash( $_POST['noorifa_from'] ) ) : '';
			$to      = isset( $_POST['noorifa_to'] ) ? esc_url_raw( wp_unslash( $_POST['noorifa_to'] ) ) : '';
			$apply   = 'apply' === sanitize_key( wp_unslash( $_POST['noorifa_migrate_submit'] ) );
			$confirm = ! empty( $_POST['noorifa_confirm'] );

			if ( '' === $from || '' === $to ) {
				add_settings_error( 'noorifa-migrate', 'missing', __( 'Please enter both the old and new URL.', 'noorifa' ), 'error' );
			} elseif ( $from === $to ) {
				add_settings_error( 'noorifa-migrate', 'same', __( 'The old and new URL are identical.', 'noorifa' ), 'error' );
			} elseif ( $apply && ! $confirm ) {
				add_settings_error( 'noorifa-migrate', 'confirm', __( 'Tick the confirmation box before applying.', 'noorifa' ), 'error' );
			} else {
				$results = $this->run( $from, $to, ! $apply );
				$applied = $apply;
			}
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Migrate URLs', 'noorifa' ); ?></h1>
			<p class="description" style="max-width:720px;">
				<?php esc_html_e( 'Search & replace a URL across the whole database (posts, options, meta, widgets, etc.), safely handling serialized data. Use this after cloning a site to fix old links/images — e.g. replace http://localhost/site with https://yourdomain.com. Always run a Dry run first, and back up your database before applying.', 'noorifa' ); ?>
			</p>

			<?php settings_errors( 'noorifa-migrate' ); ?>

			<?php if ( is_array( $results ) ) : ?>
				<div class="notice notice-<?php echo $applied ? 'success' : 'info'; ?>">
					<p>
						<strong>
							<?php
							echo $applied
								? esc_html( sprintf( /* translators: %d: number of fields. */ __( 'Done — changed %d field(s).', 'noorifa' ), $results['total'] ) )
								: esc_html( sprintf( /* translators: %d: number of fields. */ __( 'Dry run — %d field(s) would change.', 'noorifa' ), $results['total'] ) );
							?>
						</strong>
					</p>
					<?php if ( ! empty( $results['tables'] ) ) : ?>
						<table class="widefat striped" style="max-width:520px;margin-bottom:12px;">
							<thead><tr><th><?php esc_html_e( 'Table', 'noorifa' ); ?></th><th><?php esc_html_e( 'Fields', 'noorifa' ); ?></th></tr></thead>
							<tbody>
								<?php foreach ( $results['tables'] as $table => $count ) : ?>
									<tr><td><?php echo esc_html( $table ); ?></td><td><?php echo esc_html( (string) $count ); ?></td></tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
					<?php if ( ! $applied && $results['total'] > 0 ) : ?>
						<p><?php esc_html_e( 'Review the counts above, then tick the box and click “Apply changes”.', 'noorifa' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( self::NONCE ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="noorifa_from"><?php esc_html_e( 'Old URL', 'noorifa' ); ?></label></th>
						<td><input name="noorifa_from" id="noorifa_from" type="url" class="regular-text" value="<?php echo esc_attr( $from ); ?>" placeholder="http://localhost/ecombon" required /></td>
					</tr>
					<tr>
						<th scope="row"><label for="noorifa_to"><?php esc_html_e( 'New URL', 'noorifa' ); ?></label></th>
						<td><input name="noorifa_to" id="noorifa_to" type="url" class="regular-text" value="<?php echo esc_attr( $to ); ?>" placeholder="https://yourdomain.com" required /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Confirm', 'noorifa' ); ?></th>
						<td><label><input type="checkbox" name="noorifa_confirm" value="1" /> <?php esc_html_e( 'I have a database backup and want to apply the changes.', 'noorifa' ); ?></label></td>
					</tr>
				</table>
				<p>
					<button type="submit" name="noorifa_migrate_submit" value="dry" class="button button-secondary"><?php esc_html_e( 'Dry run', 'noorifa' ); ?></button>
					<button type="submit" name="noorifa_migrate_submit" value="apply" class="button button-primary"><?php esc_html_e( 'Apply changes', 'noorifa' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Runs the search & replace across every table.
	 *
	 * @param string $from    Old URL.
	 * @param string $to      New URL.
	 * @param bool   $dry_run When true, only counts; makes no changes.
	 * @return array{total:int,tables:array<string,int>}
	 */
	private function run( string $from, string $to, bool $dry_run ): array {
		global $wpdb;

		$from = untrailingslashit( $from );
		$to   = untrailingslashit( $to );

		// Plain + escaped-slash variant (block markup / JSON store "\/").
		$pairs = array(
			$from                             => $to,
			str_replace( '/', '\/', $from )   => str_replace( '/', '\/', $to ),
		);

		$total  = 0;
		$tables = array();

		foreach ( (array) $wpdb->get_col( 'SHOW TABLES' ) as $table ) {
			$keys = $wpdb->get_results( "SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'" ); // phpcs:ignore WordPress.DB
			if ( ! $keys ) {
				continue;
			}
			$pk = wp_list_pluck( $keys, 'Column_name' );

			$text_cols = array();
			foreach ( (array) $wpdb->get_results( "SHOW COLUMNS FROM `$table`" ) as $col ) { // phpcs:ignore WordPress.DB
				$type = strtolower( preg_replace( '/\(.*$/', '', $col->Type ) );
				if ( in_array( $type, $this->text_types, true ) ) {
					$text_cols[] = $col->Field;
				}
			}
			if ( ! $text_cols ) {
				continue;
			}

			$col_list = '`' . implode( '`,`', array_unique( array_merge( $pk, $text_cols ) ) ) . '`';
			$rows     = $wpdb->get_results( "SELECT $col_list FROM `$table`", ARRAY_A ); // phpcs:ignore WordPress.DB
			$hits     = 0;

			foreach ( (array) $rows as $row ) {
				$changes = array();
				foreach ( $text_cols as $col ) {
					if ( null === $row[ $col ] || '' === $row[ $col ] ) {
						continue;
					}
					$new = $this->replace_deep( $row[ $col ], $pairs );
					if ( $new !== $row[ $col ] ) {
						$changes[ $col ] = $new;
					}
				}
				if ( ! $changes ) {
					continue;
				}
				$hits += count( $changes );

				if ( ! $dry_run ) {
					$where = array();
					foreach ( $pk as $k ) {
						$where[ $k ] = $row[ $k ];
					}
					$wpdb->update( $table, $changes, $where ); // phpcs:ignore WordPress.DB
				}
			}

			if ( $hits ) {
				$total          += $hits;
				$tables[ $table ] = $hits;
			}
		}

		if ( ! $dry_run && $total > 0 ) {
			wp_cache_flush();
			flush_rewrite_rules();
		}

		return array(
			'total'  => $total,
			'tables' => $tables,
		);
	}

	/**
	 * Recursively replaces inside strings, arrays and objects, re-serializing
	 * any serialized string so its length prefixes stay valid.
	 *
	 * @param mixed $data  Value to process.
	 * @param array $pairs from => to map.
	 * @return mixed
	 */
	private function replace_deep( $data, array $pairs ) {
		if ( is_string( $data ) ) {
			if ( is_serialized( $data ) ) {
				$un = @unserialize( $data ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.PHP.DiscouragedPHPFunctions
				if ( false !== $un || 'b:0;' === $data ) {
					return serialize( $this->replace_deep( $un, $pairs ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
				}
			}
			return str_replace( array_keys( $pairs ), array_values( $pairs ), $data );
		}
		if ( is_array( $data ) ) {
			$out = array();
			foreach ( $data as $k => $v ) {
				$out[ $k ] = $this->replace_deep( $v, $pairs );
			}
			return $out;
		}
		if ( is_object( $data ) ) {
			$out = clone $data;
			foreach ( get_object_vars( $data ) as $k => $v ) {
				$out->$k = $this->replace_deep( $v, $pairs );
			}
			return $out;
		}
		return $data;
	}
}
