<?php
/**
 * Safe SVG upload component.
 *
 * @package Noorifa
 */

namespace Noorifa\Setup;

/**
 * Allows `.svg` media uploads, but sanitizes every file first.
 *
 * WordPress blocks SVG uploads by default because an SVG is XML and can carry
 * executable content (a `<script>`, an `on*` handler, a `javascript:` href),
 * which would be a stored-XSS vector. Rather than blindly whitelisting the
 * MIME type, this component:
 *
 *  1. registers `image/svg+xml` so the media library will accept it,
 *  2. corrects WordPress's real file-content check (`wp_check_filetype_and_ext`),
 *     which otherwise still reports the file as an unrecognised type — the
 *     source of the "this file cannot be processed by the web server" error,
 *  3. sanitizes the file on upload, stripping scripts, event handlers,
 *     external/`javascript:` references and any DOCTYPE/entity payload
 *     (XXE / billion-laughs), rejecting anything that isn't a valid SVG,
 *  4. forces SVG thumbnails to a sane size in wp-admin (SVGs report no
 *     intrinsic dimensions, so they'd otherwise render at 0×0).
 */
class SvgUpload implements ComponentInterface {

	/**
	 * SVG elements that are allowed to survive sanitization.
	 *
	 * Compared case-insensitively (see sanitize_node()), so a disguised
	 * `<Script>` collapses to `script` and is removed. Covers presentational
	 * SVG (icons/illustrations); `script`, `foreignObject` and `image` are
	 * deliberately absent.
	 *
	 * @var string[]
	 */
	private const ALLOWED_ELEMENTS = array(
		'svg',
		'g',
		'defs',
		'symbol',
		'use',
		'title',
		'desc',
		'metadata',
		'path',
		'rect',
		'circle',
		'ellipse',
		'line',
		'polyline',
		'polygon',
		'text',
		'tspan',
		'textpath',
		'lineargradient',
		'radialgradient',
		'stop',
		'pattern',
		'clippath',
		'mask',
		'marker',
		'style',
		'filter',
		'fegaussianblur',
		'feoffset',
		'feblend',
		'fecolormatrix',
		'fecomposite',
		'feflood',
		'femerge',
		'femergenode',
		'femorphology',
		'fetile',
		'feturbulence',
		'fedropshadow',
		'fecomponenttransfer',
		'fefuncr',
		'fefuncg',
		'fefuncb',
		'fefunca',
		'fedisplacementmap',
		'feconvolvematrix',
		'fediffuselighting',
		'fespecularlighting',
		'fepointlight',
		'fespotlight',
		'fedistantlight',
	);

	/**
	 * {@inheritDoc}
	 */
	public function initialize(): void {
		add_filter( 'upload_mimes', array( $this, 'allow_svg_mime' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'fix_svg_filetype_check' ), 10, 4 );
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'sanitize_upload' ) );
		add_action( 'admin_head', array( $this, 'admin_thumbnail_css' ) );
	}

	/**
	 * Registers the SVG MIME type so the uploader will accept `.svg`.
	 *
	 * @param array $mimes Allowed MIME types keyed by extension.
	 * @return array
	 */
	public function allow_svg_mime( array $mimes ): array {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
		return $mimes;
	}

	/**
	 * Fixes WordPress's real (content-sniffing) filetype check for SVGs.
	 *
	 * `wp_check_filetype_and_ext()` verifies the file's actual bytes against
	 * its extension. finfo has no reliable SVG signature, so it returns an
	 * empty type and the upload is rejected before our sanitizer ever runs.
	 * When the filename is a `.svg`, restore the expected type/ext explicitly.
	 *
	 * @param array  $data     Values for the extension, mime type, and corrected filename.
	 * @param string $file     Full path to the file.
	 * @param string $filename The name of the file.
	 * @param array  $mimes    Allowed MIME types.
	 * @return array
	 */
	public function fix_svg_filetype_check( array $data, string $file, string $filename, $mimes ): array {
		unset( $file, $mimes );

		if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
			return $data;
		}

		if ( preg_match( '/\.svgz?$/i', $filename ) ) {
			$data['ext']  = 'svg';
			$data['type'] = 'image/svg+xml';
		}

		return $data;
	}

	/**
	 * Sanitizes an SVG the moment it's uploaded, before it's stored.
	 *
	 * @param array $upload Upload data ({name, type, tmp_name, error, size}).
	 * @return array The (possibly error-flagged) upload data.
	 */
	public function sanitize_upload( array $upload ): array {
		$is_svg = ( isset( $upload['type'] ) && 'image/svg+xml' === $upload['type'] )
			|| ( isset( $upload['name'] ) && preg_match( '/\.svgz?$/i', $upload['name'] ) );

		if ( ! $is_svg ) {
			return $upload;
		}

		$path = $upload['tmp_name'] ?? '';

		if ( ! $path || ! is_readable( $path ) ) {
			$upload['error'] = __( 'The SVG file could not be read for sanitizing.', 'noorifa' );
			return $upload;
		}

		$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local temp upload, not a remote request.

		// Gzip-compressed .svgz — inflate before we can inspect the markup.
		if ( isset( $upload['name'] ) && preg_match( '/\.svgz$/i', $upload['name'] ) && function_exists( 'gzdecode' ) ) {
			$decoded = @gzdecode( $raw ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- invalid gzip just falls through to the reject below.
			if ( false !== $decoded ) {
				$raw = $decoded;
			}
		}

		$clean = $this->sanitize_markup( (string) $raw );

		if ( null === $clean ) {
			$upload['error'] = __( 'This SVG could not be sanitized safely and was not uploaded.', 'noorifa' );
			return $upload;
		}

		// .svgz must stay gzip-compressed on disk to match its declared type.
		if ( isset( $upload['name'] ) && preg_match( '/\.svgz$/i', $upload['name'] ) && function_exists( 'gzencode' ) ) {
			$clean = gzencode( $clean );
		}

		file_put_contents( $path, $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_put_contents_file_put_contents -- local temp upload, not a remote request.

		return $upload;
	}

	/**
	 * Returns a sanitized copy of the SVG markup, or null if it can't be trusted.
	 *
	 * @param string $svg Raw SVG markup.
	 * @return string|null
	 */
	private function sanitize_markup( string $svg ): ?string {
		$svg = trim( $svg );

		if ( '' === $svg ) {
			return null;
		}

		// Reject only entity *definitions* — the actual XXE / billion-laughs
		// vector. A plain `<!DOCTYPE svg PUBLIC ...>` (as SVG editors and
		// svgrepo emit) carries no entity subset and is harmless: its external
		// DTD is never fetched (entity loading is off by default under libxml
		// 2.9+, and LIBXML_NONET blocks network), and the DOCTYPE itself is
		// dropped from the output since we serialize the root element only.
		if ( preg_match( '/<!ENTITY/i', $svg ) ) {
			return null;
		}

		$dom = new \DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput       = false;

		// LIBXML_NONET blocks network access during parsing; external entity
		// loading is already off by default under libxml 2.9+ (PHP 8.x here).
		$previous = libxml_use_internal_errors( true );
		$loaded   = $dom->loadXML( $svg, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		if ( ! $loaded || ! $dom->documentElement ) {
			return null;
		}

		if ( 'svg' !== strtolower( $dom->documentElement->nodeName ) ) {
			return null;
		}

		// Clean the root element's own attributes (e.g. an `onload` on the
		// `<svg>` itself) before descending — sanitize_node() only handles
		// each node's children, not the node it's called on.
		$this->sanitize_attributes( $dom->documentElement );
		$this->sanitize_node( $dom->documentElement );

		$out = $dom->saveXML( $dom->documentElement );

		return is_string( $out ) && '' !== $out ? $out : null;
	}

	/**
	 * Recursively strips disallowed elements and attributes from a node.
	 *
	 * @param \DOMNode $node Node to clean (its children are cleaned in place).
	 */
	private function sanitize_node( \DOMNode $node ): void {
		// Walk children on a static snapshot — removing nodes mutates the
		// live DOMNodeList mid-iteration otherwise.
		$children = array();
		foreach ( $node->childNodes as $child ) {
			$children[] = $child;
		}

		foreach ( $children as $child ) {
			if ( XML_COMMENT_NODE === $child->nodeType || XML_PI_NODE === $child->nodeType ) {
				$node->removeChild( $child );
				continue;
			}

			if ( XML_ELEMENT_NODE !== $child->nodeType ) {
				continue;
			}

			$name = strtolower( $child->localName );

			if ( ! in_array( $name, self::ALLOWED_ELEMENTS, true ) ) {
				$node->removeChild( $child );
				continue;
			}

			$this->sanitize_attributes( $child );
			$this->sanitize_node( $child );
		}
	}

	/**
	 * Removes dangerous attributes from a single element.
	 *
	 * @param \DOMElement $el Element to clean.
	 */
	private function sanitize_attributes( \DOMElement $el ): void {
		if ( ! $el->hasAttributes() ) {
			return;
		}

		$attributes = array();
		foreach ( $el->attributes as $attr ) {
			$attributes[] = $attr;
		}

		foreach ( $attributes as $attr ) {
			$attr_name = strtolower( $attr->nodeName );
			$value     = trim( $attr->nodeValue );
			$stripped  = strtolower( preg_replace( '/\s+/', '', $value ) );

			// Event handlers (onload, onclick, …).
			if ( 0 === strpos( $attr_name, 'on' ) ) {
				$el->removeAttributeNode( $attr );
				continue;
			}

			// href / xlink:href: only internal fragment refs (#id) survive —
			// this blocks javascript:, data: and external URLs while keeping
			// the `<use href="#icon">` pattern working.
			if ( 'href' === $attr_name || 'xlink:href' === $attr_name ) {
				if ( '' !== $stripped && 0 !== strpos( $stripped, '#' ) ) {
					$el->removeAttributeNode( $attr );
				}
				continue;
			}

			// Inline styles carrying a URL, script or CSS expression.
			if ( 'style' === $attr_name ) {
				if ( preg_match( '/(javascript:|expression\s*\(|url\s*\(|@import)/i', $value ) ) {
					$el->removeAttributeNode( $attr );
				}
				continue;
			}

			// Any remaining attribute value that still smuggles a script URI.
			if ( false !== strpos( $stripped, 'javascript:' ) ) {
				$el->removeAttributeNode( $attr );
			}
		}
	}

	/**
	 * Forces SVG thumbnails to a visible size in wp-admin.
	 *
	 * SVGs report no intrinsic width/height, so the media grid and attachment
	 * previews render them at 0×0 without this.
	 */
	public function admin_thumbnail_css(): void {
		echo '<style>
.attachment .thumbnail img[src$=".svg"],
.attachment-preview .thumbnail img[src$=".svg"],
.media-icon img[src$=".svg"],
img.attachment-thumbnail[src$=".svg"] { width: 100% !important; height: auto !important; }
</style>';
	}
}
