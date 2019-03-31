<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

/**
 * Class GroovyMenuFrontendWalker
 */
class GroovyMenuFrontendWalker extends GroovyMenuWalkerNavMenu {

	protected $currentLvl            = 0;
	protected $isMegaMenu            = false;
	protected $megaMenuCnt           = 0;
	protected $megaMenuColStarted    = false;
	protected $megaMenuCols          = 5;
	protected $megaMenuPost          = null;
	protected $megaMenuPostNotMobile = null;
	protected $currentItem;


	/**
	 * @param string $output
	 * @param int    $depth
	 * @param array  $args
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$this->currentLvl ++;
		$classes = '';
		$styles  = '';

		if ( ! $this->isMegaMenu || ( $this->isMegaMenu && 2 !== $this->currentLvl ) ) {
			$classes = "gm-dropdown-menu gm-dropdown-menu--lvl-{$this->currentLvl}";

			if ( $this->getBackgroundId( $this->currentItem ) ) {
				$size     = $this->getBackgroundSize( $this->currentItem );
				$styles  .= 'background-image: url(' . $this->getBackgroundUrl( $this->currentItem, $size ) . ');';
				$styles  .= 'background-repeat: ' . $this->getBackgroundRepeat( $this->currentItem ) . ';';
				$styles  .= 'background-position: ' . $this->getBackgroundPosition( $this->currentItem ) . ';';
				$classes .= " gm-dropdown-menu--background";
			}
		}

		$output .= "\n$indent<ul class='{$classes}' style='{$styles}'>\n";
	}


	/**
	 * @param string $output
	 * @param int    $depth
	 * @param array  $args
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {

		$show_in_mobile = ( isset( $args->gm_navigation_mobile ) && $args->gm_navigation_mobile );

		if ( 1 === $this->currentLvl && $this->isMegaMenu && ! $show_in_mobile ) {
			$this->megamenuWrapperEnd( $output );
			$this->megaMenuCnt = 0;
		}
		$indent  = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
		$this->currentLvl --;

	}


	/**
	 * Begin of element
	 *
	 * @param string  $output
	 * @param WP_Post $item
	 * @param int     $depth
	 * @param array   $args
	 * @param int     $id
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $groovyMenuSettings;
		$item_output = '';

		$this->currentItem = $item;
		$indent            = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$show_in_mobile = ( isset( $args->gm_navigation_mobile ) && $args->gm_navigation_mobile );

		if ( ! $show_in_mobile ) {
			$this->megamenuWrapperStart( $output, $item );
		}

		$postContent                 = '';
		$this->megaMenuPost          = $this->megaMenuPost( $item );
		$this->megaMenuPostNotMobile = $this->megaMenuPostNotMobile( $item ) && $show_in_mobile;
		if ( $this->megaMenuPost && ! $this->megaMenuPostNotMobile ) {
			$postContent = $this->getMenuBlockPostContent( $this->megaMenuPost );
			if ( function_exists( 'groovy_menu_add_custom_styles' ) ) {
				groovy_menu_add_custom_styles( $this->megaMenuPost );
			}
		}

		$gm_menu_block = false;
		if ( isset( $item->object ) && 'gm_menu_block' === $item->object ) {
			$gm_menu_block = true;
		}

		if ( 1 === $depth && $this->isMegaMenu && ! $show_in_mobile ) {

			global $groovyMenuSettings;
			$styles      = new GroovyMenuStyle();
			$headerStyle = intval( $groovyMenuSettings['header']['style'] );

			if ( $headerStyle && in_array( $headerStyle, array( 2, 3 ), true ) ) {

				$gridClass = 'mobile-grid-100 grid-100';

			} else {

				if ( is_numeric( $this->megaMenuCols ) ) {
					if ( intval( $this->megaMenuCols ) > 0 ) {
						$colNumder = ( (int) ( 100 / intval( $this->megaMenuCols ) ) );
					}
				} else {
					$_colsElements  = explode( '-', $this->megaMenuCols );
					$_colsElemCount = count( $_colsElements );
					$_counter       = $this->megaMenuCnt;
					$maximus        = 100;

					if ( is_array( $_colsElements ) && ! empty( $_colsElements ) ) {

						while ( empty( $colNumder ) && $maximus > 0 ) {

							if ( $_counter > $_colsElemCount ) {
								$_counter = $_counter - $_colsElemCount;
							}

							if ( ! empty( $_colsElements[ ( $_counter - 1 ) ] ) ) {
								$colNumder = $_colsElements[ ( $_counter - 1 ) ];
							}

							$maximus --;
						}
					}
				}

				if ( empty( $colNumder ) ) {
					$colNumder = '20'; // 20 by default.
				}

				$gridClass = 'mobile-grid-100 grid-' . $colNumder;
			}

			$output .= '<div class="gm-mega-menu__item ' . $gridClass . '">';

			if ( ! $this->doNotShowTitle( $item ) ) {
				$output .= '<div class="gm-mega-menu__item__title">' . apply_filters( 'the_title', $item->title, $item->ID ) . '</div>';
			}

			if ( $postContent ) {
				$output .= $postContent;
			}

		} else {
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			$thumb   = null;
			if ( $depth > 0 && $this->isShowFeaturedImage( $item ) ) {
				$previewWidth  = $groovyMenuSettings['previewWidth'];
				$previewHeight = $groovyMenuSettings['previewHeight'];

				if ( get_post_thumbnail_id( $item->object_id ) ) {
					$thumb = wp_get_attachment_image( get_post_thumbnail_id( $item->object_id ), array(
						$previewWidth,
						$previewHeight,
					), false, array( 'class' => 'attachment-menu-thumb size-menu-thumb' ) );
					if ( $thumb ) {
						$classes[] = 'has-attachment-thumbnail';
					}
				}
			}

			$classes[] = 'gm-menu-item';
			if ( $this->hasChildren( $classes ) ) {
				$classes[] = 'gm-dropdown';
			}
			if ( $this->hasParents() && $this->hasChildren( $classes ) ) {
				$classes[] = 'gm-dropdown-submenu';
			}

			if ( 0 === $depth && $this->isMegaMenu( $item ) && ! $show_in_mobile ) {
				$this->megaMenuCols = $this->megaMenuCols( $item );
				$classes[]          = 'mega-gm-dropdown';
				$this->isMegaMenu   = true;
			}

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
			$class_names = trim( $class_names ) ? ' class="' . esc_attr( $class_names ) . '"' : '';

			$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

			$output        .= $indent . '<li' . $id . $class_names . '>';
			$atts           = array();
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target ) ? $item->target : '';
			$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
			$atts['href']   = ! empty( $item->url ) ? $item->url : '';
			$atts['class']  = 'gm-anchor';
			if ( $this->hasChildren( $classes ) ) {
				$atts['class'] .= ' gm-dropdown-toggle';
			}
			if ( $this->hasParents() ) {
				$atts['class'] .= ' gm-menu-item__link';
			}

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					if ( 'href' === $attr ) {
						$value = esc_url( $value );
						if ( $gm_menu_block ) {
							$value = $this->menuBlockURL( $item, $value );
						}
					} else {
						$value = esc_attr( $value );
					}
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			$item_output = $args->before;
			if ( ! $this->doNotShowTitle( $item ) ) {
				$item_output .= '<a' . $attributes . '>';
				if ( $this->getIcon( $item ) ) {
					$item_output .= '<span class="gm-menu-item__icon ' . $this->getIcon( $item ) . '"></span>';
				}
				$item_output .= '<span class="gm-menu-item__txt">';
				$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
				$item_output .= '</span>';
				if ( $this->hasParents() && $this->hasChildren( $classes ) ) {
					$item_output .= '<span class="gm-caret"><i class="fa fa-fw fa-angle-right"></i></span>';
				} elseif ( $this->hasChildren( $classes ) ) {
					$item_output .= '<span class="gm-caret"><i class="fa fa-fw fa-angle-down"></i></span>';
				}
				$item_output .= $thumb;
				$item_output .= '</a>';
			} else {
				if ( $this->hasParents() && $this->hasChildren( $classes ) ) {
					$item_output .= '<span class="gm-caret ' . $atts['class'] . '"><i class="fa fa-fw fa-angle-right"></i></span>';
				} elseif ( $this->hasChildren( $classes ) ) {
					$item_output .= '<span class="gm-caret ' . $atts['class'] . '"><i class="fa fa-fw fa-angle-down"></i></span>';
				}
				$item_output .= $thumb;
			}
			$item_output .= $postContent;
			$item_output .= $args->after;
		}
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}


	/**
	 * @param string  $output
	 * @param WP_Post $item
	 * @param int     $depth
	 * @param array   $args
	 */
	public function end_el( &$output, $item, $depth = 0, $args = array() ) {

		$show_in_mobile = ( isset( $args->gm_navigation_mobile ) && $args->gm_navigation_mobile );

		if ( 1 === $depth && $this->isMegaMenu && ! $show_in_mobile ) {
			$output .= '</div>';
		} else {
			parent::end_el( $output, $item, $depth, $args );
		}

		$this->megaMenuPost = '';

	}


	/**
	 * @param $classes
	 *
	 * @return bool
	 */
	protected function hasChildren( $classes ) {
		return in_array( 'menu-item-has-children', $classes, true );
	}


	/**
	 * @return bool
	 */
	protected function hasParents() {
		return $this->currentLvl > 0;
	}


	/**
	 * @param $output
	 * @param $item
	 */
	protected function megamenuWrapperStart( &$output, $item ) {

		if ( $this->isMegaMenu ) {
			if ( 1 === $this->currentLvl ) {
				$this->megaMenuCnt ++;

				if ( 1 === $this->megaMenuCnt ) {
					$styles = '';
					$class  = 'gm-mega-menu-wrapper';

					$output .= '<li><div style="' . $styles . '" class="' . $class . '"><div class="gm-grid-container"><div class="gm-grid-row">';
				}
			}
		}
	}


	/**
	 * @param $output
	 */
	protected function megamenuWrapperEnd( &$output ) {

		$output .= '</div></div></div></li>';

		$this->isMegaMenu   = false;
		$this->megaMenuPost = '';

	}


}
