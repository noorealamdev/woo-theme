<?php
/**
 * MegaMenuWalker class.
 *
 * @package Ecombon
 */

namespace Ecombon\Navigation;

/**
 * Renders the primary nav menu using the theme's own header markup.
 *
 * A top-level menu item becomes a full-width mega menu (grouped into
 * columns, one per direct child, with grandchildren as the column's links)
 * when it's given the `mega-menu` CSS class in Appearance > Menus. Every
 * other item with children renders as a simple two-level dropdown. This
 * gives editors real mega-menu capability from the stock WordPress menu
 * screen, no admin UI of our own required.
 */
class MegaMenuWalker extends \Walker_Nav_Menu {

	/**
	 * Whether the top-level item currently being walked is a mega menu.
	 */
	private bool $current_top_is_mega = false;

	/**
	 * {@inheritDoc}
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= $this->current_top_is_mega
				? '<div class="sub-menu mega-menu"><div class="container-full"><div class="row">'
				: '<div class="sub-menu mega-menu-item"><ul class="sub-menu_list">';
			return;
		}

		if ( 1 === $depth ) {
			$output .= $this->current_top_is_mega
				? '<ul class="sub-menu_list">'
				: '<div class="sub-menu-lv2"><ul class="sub-menu_list">';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= $this->current_top_is_mega ? '</div></div></div>' : '</ul></div>';
			return;
		}

		if ( 1 === $depth ) {
			$output .= $this->current_top_is_mega ? '</ul>' : '</ul></div>';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes      = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_children = ! empty( $args->has_children );
		$url          = ! empty( $item->url ) ? $item->url : '#';
		$title        = apply_filters( 'the_title', $item->title, $item->ID );

		if ( 0 === $depth ) {
			$this->current_top_is_mega = in_array( 'mega-menu', $classes, true );

			$li_classes = array( 'menu-item' );
			if ( $has_children ) {
				$li_classes[] = 'position-relative';
			}
			if ( array_intersect( array( 'current-menu-item', 'current-menu-ancestor' ), $classes ) ) {
				$li_classes[] = 'current';
			}

			$output .= '<li class="' . esc_attr( implode( ' ', $li_classes ) ) . '">';
			$output .= '<a href="' . esc_url( $url ) . '" class="item-link">';
			$output .= '<span class="text cus-text">' . esc_html( $title ) . '</span>';
			if ( $has_children ) {
				$output .= '<i class="icon icon-CaretDown"></i>';
			}
			$output .= '</a>';
			return;
		}

		if ( 1 === $depth && $this->current_top_is_mega ) {
			$output .= '<div class="col-2"><div class="mega-menu-item menu-lv-2">';
			$output .= '#' === $url
				? '<p class="menu-heading">' . esc_html( $title ) . '</p>'
				: '<a href="' . esc_url( $url ) . '" class="menu-heading">' . esc_html( $title ) . '</a>';
			return;
		}

		if ( 1 === $depth && $has_children ) {
			$output .= '<li class="has-menu-lv2">';
			$output .= '<a href="#" class="menu-heading-lv2 sub-menu_link has-text">';
			$output .= '<span class="cus-text">' . esc_html( $title ) . '</span>';
			$output .= '<i class="icon icon-CaretRightThin"></i>';
			$output .= '</a>';
			return;
		}

		// Depth 1 leaf, or depth 2 (mega column link / lv2 sub-link).
		$output .= '<li><a href="' . esc_url( $url ) . '" class="sub-menu_link has-text"><span class="cus-text">' . esc_html( $title ) . '</span></a>';
	}

	/**
	 * {@inheritDoc}
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 1 === $depth && $this->current_top_is_mega ) {
			$output .= '</div></div>';
			return;
		}

		$output .= '</li>';
	}
}
