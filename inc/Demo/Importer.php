<?php
/**
 * One-click demo content importer.
 *
 * @package Noorifa
 */

namespace Noorifa\Demo;

use Noorifa\Setup\ComponentInterface;

/**
 * A lightweight, self-contained demo importer (no third-party plugin):
 * one click populates a fresh site with sample products, a designed home
 * page built from Noorifa Core blocks, simple About/Contact pages, menus,
 * and the static front page — so a buyer can see a working store and edit
 * from there.
 *
 * Placeholder images are generated on the fly with GD (a grey box labelled
 * with its own size, e.g. "800 x 800"), so nothing heavy is bundled in the
 * theme and the demo works offline. Designed to run on a fresh install.
 */
class Importer implements ComponentInterface {

	const OPTION   = 'noorifa_demo_import';
	const ACTION   = 'noorifa_demo_import';
	const PAGE     = 'noorifa-demo-import';

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		if ( ! is_admin() ) {
			return;
		}
		add_action( 'admin_menu', array( $this, 'add_page' ), 30 );
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_import' ) );
	}

	/**
	 * Adds the Demo Import page under the shared Noorifa menu (or as its own
	 * top-level item if the menu constant isn't defined).
	 */
	public function add_page(): void {
		$parent = defined( 'NOORIFA_ADMIN_MENU_SLUG' ) ? NOORIFA_ADMIN_MENU_SLUG : null;

		if ( $parent ) {
			add_submenu_page( $parent, __( 'Demo Import', 'noorifa' ), __( 'Demo Import', 'noorifa' ), 'manage_options', self::PAGE, array( $this, 'render_page' ) );
		} else {
			add_menu_page( __( 'Demo Import', 'noorifa' ), __( 'Demo Import', 'noorifa' ), 'manage_options', self::PAGE, array( $this, 'render_page' ), 'dashicons-download' );
		}
	}

	/**
	 * Renders the import screen.
	 */
	public function render_page(): void {
		$done    = isset( $_GET['imported'] ) ? sanitize_key( wp_unslash( $_GET['imported'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flash.
		$has_wc  = class_exists( 'WooCommerce' );
		$already = (array) get_option( self::OPTION, array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Noorifa — Demo Import', 'noorifa' ); ?></h1>

			<?php if ( 'yes' === $done ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Demo content imported. Visit your site to see it.', 'noorifa' ); ?></p></div>
			<?php elseif ( 'error' === $done ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( get_transient( 'noorifa_demo_error_' . get_current_user_id() ) ?: __( 'Import failed.', 'noorifa' ) ); ?></p></div>
				<?php delete_transient( 'noorifa_demo_error_' . get_current_user_id() ); ?>
			<?php endif; ?>

			<p style="max-width:640px">
				<?php esc_html_e( 'This installs sample content so you can see a complete store and edit from there: a few products, a designed home page, About and Contact pages, menus, and your front page. It is meant for a fresh site — on an existing store it adds demo items alongside your own.', 'noorifa' ); ?>
			</p>

			<?php if ( ! $has_wc ) : ?>
				<div class="notice notice-warning inline"><p><?php esc_html_e( 'WooCommerce must be installed and active before importing demo content.', 'noorifa' ); ?></p></div>
			<?php endif; ?>

			<?php if ( $already ) : ?>
				<div class="notice notice-info inline"><p><?php esc_html_e( 'Demo content has already been imported once. Running it again will create duplicate items.', 'noorifa' ); ?></p></div>
			<?php endif; ?>

			<p>
				<a
					class="button button-primary button-hero <?php echo $has_wc ? '' : 'disabled'; ?>"
					href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=' . self::ACTION ), self::ACTION ) ); ?>"
					<?php echo $has_wc ? '' : 'aria-disabled="true"'; ?>
					onclick="return confirm('<?php echo esc_js( __( 'Import demo content now?', 'noorifa' ) ); ?>');"
				>
					<?php esc_html_e( 'Import Demo Content', 'noorifa' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Runs the import.
	 */
	public function handle_import(): void {
		check_admin_referer( self::ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'noorifa' ) );
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->fail( __( 'WooCommerce must be active first.', 'noorifa' ) );
		}

		if ( ! function_exists( 'imagepng' ) ) {
			$this->fail( __( 'The GD image library is required to generate demo images.', 'noorifa' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';

		$record = array( 'products' => array(), 'pages' => array(), 'menus' => array(), 'attachments' => array(), 'terms' => array(), 'time' => time() );

		// 1. Product categories.
		$cats = array();
		foreach ( array( 'Apparel', 'Accessories' ) as $cat_name ) {
			$term = term_exists( $cat_name, 'product_cat' );
			if ( ! $term ) {
				$term = wp_insert_term( $cat_name, 'product_cat' );
			}
			if ( ! is_wp_error( $term ) ) {
				$cats[] = (int) $term['term_id'];
				$record['terms'][] = (int) $term['term_id'];
			}
		}

		// 2. Sample products with placeholder images.
		$product_names = array(
			'Classic Tee', 'Everyday Hoodie', 'Canvas Tote', 'Leather Belt', 'Wool Beanie', 'Sport Socks',
		);
		$prices = array( '24', '49', '19', '35', '15', '9' );
		$product_ids = array();

		foreach ( $product_names as $i => $name ) {
			$image_id = $this->placeholder_attachment( 800, 800 );
			if ( $image_id ) {
				$record['attachments'][] = $image_id;
			}

			$product = new \WC_Product_Simple();
			$product->set_name( $name );
			$product->set_status( 'publish' );
			$product->set_regular_price( $prices[ $i ] );
			if ( 0 === $i % 2 ) {
				$product->set_sale_price( (string) max( 5, (int) $prices[ $i ] - 6 ) );
			}
			$product->set_short_description( 'Demo product — replace with your own description.' );
			$product->set_category_ids( array( $cats[ $i % max( 1, count( $cats ) ) ] ) );
			if ( $image_id ) {
				$product->set_image_id( $image_id );
			}
			$pid = $product->save();
			if ( $pid ) {
				$product_ids[] = $pid;
				$record['products'][] = $pid;
			}
		}

		// 3. Placeholder banners for the home page.
		$banner   = $this->placeholder_attachment( 1200, 600 );
		$promo_a  = $this->placeholder_attachment( 600, 460 );
		$promo_b  = $this->placeholder_attachment( 600, 460 );
		$cta_img  = $this->placeholder_attachment( 1200, 420 );
		foreach ( array( $banner, $promo_a, $promo_b, $cta_img ) as $att ) {
			if ( $att ) {
				$record['attachments'][] = $att;
			}
		}
		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

		// 4. Home page (blank-canvas front page → self-boxing Noorifa blocks only).
		$home_id = wp_insert_post( array(
			'post_title'   => 'Home',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => $this->home_content( $banner, $promo_a, $promo_b, $cta_img, $shop_url ),
		) );
		if ( $home_id ) {
			$record['pages'][] = $home_id;
		}

		// 5. About & Contact (regular pages use the theme's constrained content area).
		$about_id = wp_insert_post( array(
			'post_title'   => 'About Us',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => "<!-- wp:paragraph --><p>This is a demo About page. Share your brand's story here — who you are, what you sell, and why customers should trust you.</p><!-- /wp:paragraph -->",
		) );
		$contact_id = wp_insert_post( array(
			'post_title'   => 'Contact',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => "<!-- wp:paragraph --><p>This is a demo Contact page. Add your address, phone, email or a contact form here.</p><!-- /wp:paragraph -->",
		) );
		foreach ( array( $about_id, $contact_id ) as $pid ) {
			if ( $pid ) {
				$record['pages'][] = $pid;
			}
		}

		// 6. Menus.
		$this->build_menus( $home_id, $about_id, $contact_id, $shop_url, $record );

		// 7. Static front page.
		if ( $home_id ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_id );
		}

		// Record what we created (append if run more than once).
		$prev = (array) get_option( self::OPTION, array() );
		update_option( self::OPTION, array_merge_recursive( $prev, $record ) );

		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE . '&imported=yes' ) );
		exit;
	}

	/**
	 * Builds the home page block markup from dynamic Noorifa Core blocks
	 * (all self-closing, so the saved markup is always valid).
	 *
	 * @param int    $banner   Banner attachment id.
	 * @param int    $promo_a  Promo A attachment id.
	 * @param int    $promo_b  Promo B attachment id.
	 * @param int    $cta      CTA attachment id.
	 * @param string $shop_url Shop URL.
	 * @return string
	 */
	private function home_content( $banner, $promo_a, $promo_b, $cta, $shop_url ): string {
		$blocks = array();

		$blocks[] = '<!-- wp:noorifa-core/image-card ' . wp_json_encode( array(
			'columns'   => 1,
			'minHeight' => 460,
			'items'     => array( array(
				'imageId'    => (int) $banner,
				'imageUrl'   => $banner ? wp_get_attachment_url( $banner ) : '',
				'heading'    => 'Your store, beautifully presented',
				'subheading' => 'This is demo content — swap in your own products, images and copy to make it yours.',
				'linkText'   => 'Shop Now',
				'linkUrl'    => $shop_url,
				'textColor'  => '#ffffff',
				'overlay'    => 45,
			) ),
		) ) . ' /-->';

		$blocks[] = '<!-- wp:noorifa-core/feature-cards ' . wp_json_encode( array(
			'columns' => 3,
			'items'   => array(
				array( 'heading' => 'Free & fast delivery', 'text' => 'Reliable shipping your customers can count on.' ),
				array( 'heading' => 'Secure checkout', 'text' => 'Trusted payments and a smooth buying experience.' ),
				array( 'heading' => 'Easy returns', 'text' => 'A hassle-free policy that builds confidence.' ),
			),
		) ) . ' /-->';

		$blocks[] = '<!-- wp:noorifa-core/product-grid ' . wp_json_encode( array(
			'relation'      => 'latest',
			'productsToShow' => 8,
			'columns'       => 4,
		) ) . ' /-->';

		$blocks[] = '<!-- wp:noorifa-core/image-card ' . wp_json_encode( array(
			'columns' => 2,
			'items'   => array(
				array( 'imageId' => (int) $promo_a, 'imageUrl' => $promo_a ? wp_get_attachment_url( $promo_a ) : '', 'heading' => 'New arrivals', 'subheading' => 'Fresh picks for the season.', 'linkText' => 'Explore', 'linkUrl' => $shop_url, 'textColor' => '#ffffff', 'overlay' => 40 ),
				array( 'imageId' => (int) $promo_b, 'imageUrl' => $promo_b ? wp_get_attachment_url( $promo_b ) : '', 'heading' => 'Best sellers', 'subheading' => 'The favourites everyone loves.', 'linkText' => 'Shop now', 'linkUrl' => $shop_url, 'textColor' => '#ffffff', 'overlay' => 40 ),
			),
		) ) . ' /-->';

		$blocks[] = '<!-- wp:noorifa-core/image-card ' . wp_json_encode( array(
			'columns'   => 1,
			'minHeight' => 320,
			'items'     => array( array(
				'imageId'    => (int) $cta,
				'imageUrl'   => $cta ? wp_get_attachment_url( $cta ) : '',
				'heading'    => 'Ready to start selling?',
				'subheading' => 'Replace this demo with your real offer and launch.',
				'linkText'   => 'Visit the shop',
				'linkUrl'    => $shop_url,
				'textColor'  => '#ffffff',
				'overlay'    => 50,
			) ),
		) ) . ' /-->';

		return implode( "\n\n", $blocks );
	}

	/**
	 * Creates the primary and footer menus and assigns them to the theme's
	 * menu locations.
	 *
	 * @param int    $home_id    Home page id.
	 * @param int    $about_id   About page id.
	 * @param int    $contact_id Contact page id.
	 * @param string $shop_url   Shop URL.
	 * @param array  $record     Import record (passed by reference).
	 */
	private function build_menus( $home_id, $about_id, $contact_id, $shop_url, array &$record ): void {
		$primary = $this->create_menu( 'Primary Menu' );
		if ( $primary ) {
			$record['menus'][] = $primary;
			$this->add_page_item( $primary, $home_id, 'Home' );
			$this->add_link_item( $primary, $shop_url, 'Shop' );
			$this->add_page_item( $primary, $about_id, 'About Us' );
			$this->add_page_item( $primary, $contact_id, 'Contact' );
		}

		$company = $this->create_menu( 'Footer Company' );
		if ( $company ) {
			$record['menus'][] = $company;
			$this->add_page_item( $company, $about_id, 'About Us' );
			$this->add_page_item( $company, $contact_id, 'Contact' );
		}

		$customer = $this->create_menu( 'Footer Customer' );
		if ( $customer ) {
			$record['menus'][] = $customer;
			$this->add_link_item( $customer, $shop_url, 'Shop' );
			$myaccount = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
			$this->add_link_item( $customer, $myaccount, 'My Account' );
		}

		$locations = get_theme_mod( 'nav_menu_locations', array() );
		if ( $primary ) {
			$locations['primary'] = $primary;
		}
		if ( $company ) {
			$locations['footer_company'] = $company;
		}
		if ( $customer ) {
			$locations['footer_customer'] = $customer;
		}
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Creates (or reuses) a nav menu and returns its id.
	 *
	 * @param string $name Menu name.
	 * @return int
	 */
	private function create_menu( $name ): int {
		$existing = wp_get_nav_menu_object( $name );
		if ( $existing ) {
			return (int) $existing->term_id;
		}
		$id = wp_create_nav_menu( $name );
		return is_wp_error( $id ) ? 0 : (int) $id;
	}

	/**
	 * Adds a page link to a menu.
	 *
	 * @param int    $menu_id Menu id.
	 * @param int    $page_id Page id.
	 * @param string $title   Menu label.
	 */
	private function add_page_item( $menu_id, $page_id, $title ): void {
		if ( ! $menu_id || ! $page_id ) {
			return;
		}
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => $title,
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
	}

	/**
	 * Adds a custom-URL link to a menu.
	 *
	 * @param int    $menu_id Menu id.
	 * @param string $url     Link URL.
	 * @param string $title   Menu label.
	 */
	private function add_link_item( $menu_id, $url, $title ): void {
		if ( ! $menu_id || ! $url ) {
			return;
		}
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'  => $title,
			'menu-item-url'    => $url,
			'menu-item-type'   => 'custom',
			'menu-item-status' => 'publish',
		) );
	}

	/**
	 * Generates a grey placeholder PNG labelled with its size and stores it
	 * as a media attachment. Returns the attachment id (0 on failure).
	 *
	 * @param int $w Width in px.
	 * @param int $h Height in px.
	 * @return int
	 */
	private function placeholder_attachment( int $w, int $h ): int {
		$image = imagecreatetruecolor( $w, $h );
		$bg    = imagecolorallocate( $image, 228, 224, 214 );
		$edge  = imagecolorallocate( $image, 205, 199, 185 );
		$ink   = imagecolorallocate( $image, 122, 114, 98 );
		imagefilledrectangle( $image, 0, 0, $w, $h, $bg );
		imagerectangle( $image, 0, 0, $w - 1, $h - 1, $edge );

		// Draw the size label large by rendering it small then scaling up.
		$label = $w . ' x ' . $h;
		$font  = 5;
		$tw    = imagefontwidth( $font ) * strlen( $label );
		$th    = imagefontheight( $font );
		$strip = imagecreatetruecolor( $tw, $th );
		imagefilledrectangle( $strip, 0, 0, $tw, $th, $bg );
		imagestring( $strip, $font, 0, 0, $label, $ink );
		$scale = ( $w * 0.32 ) / $tw;
		$dw    = max( 1, (int) ( $tw * $scale ) );
		$dh    = max( 1, (int) ( $th * $scale ) );
		imagecopyresampled( $image, $strip, (int) ( ( $w - $dw ) / 2 ), (int) ( ( $h - $dh ) / 2 ), 0, 0, $dw, $dh, $tw, $th );
		imagedestroy( $strip );

		ob_start();
		imagepng( $image );
		$binary = ob_get_clean();
		imagedestroy( $image );

		$upload = wp_upload_bits( 'noorifa-demo-' . $w . 'x' . $h . '-' . uniqid() . '.png', null, $binary );
		if ( ! empty( $upload['error'] ) ) {
			return 0;
		}

		$attachment_id = wp_insert_attachment( array(
			'post_mime_type' => 'image/png',
			'post_title'     => sprintf( 'Demo placeholder %d x %d', $w, $h ),
			'post_status'    => 'inherit',
		), $upload['file'] );

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return 0;
		}

		$meta = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $meta );

		return (int) $attachment_id;
	}

	/**
	 * Stores an error and redirects back to the import screen.
	 *
	 * @param string $message Error message.
	 */
	private function fail( string $message ): void {
		set_transient( 'noorifa_demo_error_' . get_current_user_id(), $message, MINUTE_IN_SECONDS );
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE . '&imported=error' ) );
		exit;
	}
}
