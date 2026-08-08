<?php
/**
 * ProductPageLayout component.
 *
 * @package Noorifa
 */

namespace Noorifa\WooCommerce;

use Noorifa\Setup\ComponentInterface;

/**
 * Adds a per-product "Page Layout" meta box letting a merchant hide the
 * theme header and/or footer on a single product's page — for building
 * distraction-free landing-page / funnel products. The flags are read at
 * render time by header.php and footer.php via the static helpers below.
 */
class ProductPageLayout implements ComponentInterface {

	/**
	 * Meta key: hide the header on this product's page.
	 */
	const META_HIDE_HEADER = '_noorifa_hide_header';

	/**
	 * Meta key: hide the footer on this product's page.
	 */
	const META_HIDE_FOOTER = '_noorifa_hide_footer';

	/**
	 * Meta key: a per-product body background color override.
	 */
	const META_BODY_BG = '_noorifa_body_bg';

	/**
	 * Meta key: opacity (0–100) for the body background color override.
	 */
	const META_BODY_BG_OPACITY = '_noorifa_body_bg_opacity';

	/**
	 * Nonce action/name for the meta box save.
	 */
	const NONCE = 'noorifa_product_layout';

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_color_picker' ) );
		// Later than the global brand overrides (priority 10) so the
		// per-product value reliably wins; targeting `body` beats the
		// global `:root` value regardless, this just keeps source order tidy.
		add_action( 'wp_head', array( $this, 'print_body_background' ), 20 );
	}

	/**
	 * Loads the WordPress color picker on the product edit screen.
	 *
	 * @param string $hook The current admin page.
	 * @return void
	 */
	public function enqueue_color_picker( string $hook ): void {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_add_inline_script(
			'wp-color-picker',
			'jQuery(function($){$(".noorifa-color-field").wpColorPicker();$("#noorifa_body_bg_opacity").on("input change",function(){$(".noorifa-opacity-value").text(this.value+"%");});});'
		);
	}

	/**
	 * Registers the meta box on the product edit screen.
	 *
	 * @return void
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'noorifa-product-layout',
			__( 'Page Layout', 'noorifa' ),
			array( $this, 'render' ),
			'product',
			'side',
			'default'
		);
	}

	/**
	 * Renders the meta box controls.
	 *
	 * @param \WP_Post $post The product being edited.
	 * @return void
	 */
	public function render( \WP_Post $post ): void {
		$hide_header = '1' === get_post_meta( $post->ID, self::META_HIDE_HEADER, true );
		$hide_footer = '1' === get_post_meta( $post->ID, self::META_HIDE_FOOTER, true );
		$body_bg     = (string) get_post_meta( $post->ID, self::META_BODY_BG, true );
		$stored_op   = get_post_meta( $post->ID, self::META_BODY_BG_OPACITY, true );
		$opacity     = '' === $stored_op ? 100 : max( 0, min( 100, (int) $stored_op ) );

		wp_nonce_field( self::NONCE, self::NONCE );
		?>
		<p class="description" style="margin-top:0;">
			<?php esc_html_e( 'Hide the site header/footer on this product for a distraction-free landing page.', 'noorifa' ); ?>
		</p>
		<p>
			<label>
				<input type="checkbox" name="noorifa_hide_header" value="1" <?php checked( $hide_header ); ?> />
				<?php esc_html_e( 'Hide header on this product', 'noorifa' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="noorifa_hide_footer" value="1" <?php checked( $hide_footer ); ?> />
				<?php esc_html_e( 'Hide footer on this product', 'noorifa' ); ?>
			</label>
		</p>
		<p style="margin-bottom:4px;">
			<label for="noorifa_body_bg"><strong><?php esc_html_e( 'Body background color', 'noorifa' ); ?></strong></label>
		</p>
		<input type="text" id="noorifa_body_bg" name="noorifa_body_bg" value="<?php echo esc_attr( $body_bg ); ?>" class="noorifa-color-field" data-default-color="" />
		<p class="description">
			<?php esc_html_e( 'Overrides the theme’s body background on this product only. Leave empty to use the global color.', 'noorifa' ); ?>
		</p>
		<p style="margin:8px 0 4px;">
			<label for="noorifa_body_bg_opacity"><strong><?php esc_html_e( 'Opacity', 'noorifa' ); ?></strong></label>
		</p>
		<input type="range" id="noorifa_body_bg_opacity" name="noorifa_body_bg_opacity" min="0" max="100" step="1" value="<?php echo esc_attr( (string) $opacity ); ?>" style="width:75%;vertical-align:middle;" />
		<span class="noorifa-opacity-value"><?php echo esc_html( $opacity . '%' ); ?></span>
		<?php
	}

	/**
	 * Persists the meta box values.
	 *
	 * @param int      $post_id The product ID.
	 * @param \WP_Post $post    The product post object.
	 * @return void
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$this->save_flag( $post_id, self::META_HIDE_HEADER, isset( $_POST['noorifa_hide_header'] ) );
		$this->save_flag( $post_id, self::META_HIDE_FOOTER, isset( $_POST['noorifa_hide_footer'] ) );

		$raw_color = isset( $_POST['noorifa_body_bg'] ) ? sanitize_hex_color( wp_unslash( $_POST['noorifa_body_bg'] ) ) : '';
		$opacity   = isset( $_POST['noorifa_body_bg_opacity'] ) ? max( 0, min( 100, absint( wp_unslash( $_POST['noorifa_body_bg_opacity'] ) ) ) ) : 100;

		if ( $raw_color ) {
			update_post_meta( $post_id, self::META_BODY_BG, $raw_color );
			update_post_meta( $post_id, self::META_BODY_BG_OPACITY, $opacity );
		} else {
			delete_post_meta( $post_id, self::META_BODY_BG );
			delete_post_meta( $post_id, self::META_BODY_BG_OPACITY );
		}
	}

	/**
	 * Stores '1' when checked, or removes the meta entirely when unchecked
	 * so an unset flag never lingers in the database.
	 *
	 * @param int    $post_id The product ID.
	 * @param string $key     The meta key.
	 * @param bool   $checked Whether the box was checked.
	 * @return void
	 */
	private function save_flag( int $post_id, string $key, bool $checked ): void {
		if ( $checked ) {
			update_post_meta( $post_id, $key, '1' );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}

	/**
	 * Whether the header should be hidden on the current request.
	 *
	 * @return bool
	 */
	public static function should_hide_header(): bool {
		return self::flag_set( self::META_HIDE_HEADER );
	}

	/**
	 * Whether the footer should be hidden on the current request.
	 *
	 * @return bool
	 */
	public static function should_hide_footer(): bool {
		return self::flag_set( self::META_HIDE_FOOTER );
	}

	/**
	 * Reads a hide flag for the single product currently being viewed.
	 * Uses the queried object id so it works in header.php/footer.php,
	 * before/outside the main loop.
	 *
	 * @param string $key The meta key.
	 * @return bool
	 */
	private static function flag_set( string $key ): bool {
		if ( ! is_singular( 'product' ) ) {
			return false;
		}

		return '1' === get_post_meta( get_queried_object_id(), $key, true );
	}

	/**
	 * The per-product body background color for the product being viewed,
	 * or an empty string when none is set.
	 *
	 * @return string
	 */
	public static function body_bg_color(): string {
		if ( ! is_singular( 'product' ) ) {
			return '';
		}

		return (string) get_post_meta( get_queried_object_id(), self::META_BODY_BG, true );
	}

	/**
	 * The final CSS color value for the body background override — the hex
	 * as-is at full opacity, or an rgba() when a lower opacity is set.
	 * Empty string when no override is configured.
	 *
	 * @return string
	 */
	public static function body_bg_css(): string {
		$hex = self::body_bg_color();

		if ( '' === $hex ) {
			return '';
		}

		$stored  = get_post_meta( get_queried_object_id(), self::META_BODY_BG_OPACITY, true );
		$opacity = '' === $stored ? 100 : max( 0, min( 100, (int) $stored ) );

		if ( 100 === $opacity ) {
			return $hex;
		}

		$digits = ltrim( $hex, '#' );
		if ( 3 === strlen( $digits ) ) {
			$digits = $digits[0] . $digits[0] . $digits[1] . $digits[1] . $digits[2] . $digits[2];
		}

		$r = hexdec( substr( $digits, 0, 2 ) );
		$g = hexdec( substr( $digits, 2, 2 ) );
		$b = hexdec( substr( $digits, 4, 2 ) );
		$a = rtrim( rtrim( number_format( $opacity / 100, 2, '.', '' ), '0' ), '.' );

		return sprintf( 'rgba(%d,%d,%d,%s)', $r, $g, $b, $a );
	}

	/**
	 * Prints a per-product override of the theme's body-background CSS
	 * variable. Set on `body` (not `:root`) so it always wins over the
	 * global value for this product's page.
	 *
	 * @return void
	 */
	public function print_body_background(): void {
		$value = self::body_bg_css();

		if ( '' === $value ) {
			return;
		}

		echo '<style id="noorifa-product-body-bg">body{--noorifa-body-bg:' . esc_attr( $value ) . ';}</style>' . "\n";
	}
}
