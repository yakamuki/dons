<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

/**
 * Class GroovyMenuWalkerNavMenu
 */
class GroovyMenuWalkerNavMenu extends Walker_Nav_Menu {

	const IS_MEGAMENU_META              = 'groovy_menu_is_megamenu';
	const DO_NOT_SHOW_TITLE             = 'groovy_menu_do_not_show_title';
	const MEGAMENU_META_COLS            = 'groovy_menu_megamenu_cols';
	const MENU_BLOCK_URL                = 'groovy_menu_block_url';
	const MEGAMENU_META_POST            = 'groovy_menu_megamenu_post';
	const MEGAMENU_META_POST_NOT_MOBILE = 'groovy_menu_megamenu_post_not_mobile';
	const IS_SHOW_FEATURED_IMAGE        = 'groovy_menu_is_show_featured_image';
	const ICON_CLASS                    = 'groovy_menu_icon_class';
	const MEGAMENU_BACKGROUND           = 'groovy_menu_megamenu_background';
	const MEGAMENU_BACKGROUND_POSITION  = 'groovy_menu_megamenu_background_position';
	const MEGAMENU_BACKGROUND_REPEAT    = 'groovy_menu_megamenu_background_repeat';
	const MEGAMENU_BACKGROUND_SIZE      = 'groovy_menu_megamenu_background_size';

	protected static $backgroundPositions = array(
		'top left',
		'top center',
		'top right',
		'center left',
		'center center',
		'center right',
		'bottom left',
		'bottom center',
		'bottom right',
	);

	protected static $backgroundRepeats = array(
		'no-repeat',
		'repeat',
		'repeat-x',
		'repeat-y',
	);

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	protected function isMegaMenu( $item ) {
		global $groovyMenuSettings;

		if (
			isset( $groovyMenuSettings['header'] ) &&
			( in_array( intval( $groovyMenuSettings['header']['style'] ), array( 2, 3, 4 ), true ) )
		) {
			return false;
		}

		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return false;
		}

		return get_post_meta( $item_id, self::IS_MEGAMENU_META, true ) !== '';
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	protected function doNotShowTitle( $item ) {
		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return false;
		}

		return get_post_meta( $item_id, self::DO_NOT_SHOW_TITLE, true ) !== '';
	}

	/**
	 * @param $item
	 *
	 * @return int
	 */
	protected function megaMenuCols( $item ) {
		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return 5;
		}

		$val = get_post_meta( $item_id, self::MEGAMENU_META_COLS, true );
		if ( ! $val ) {
			$val = 5;
		}

		return $val;
	}

	/**
	 * @param $item
	 *
	 * @return int|null
	 */
	protected function megaMenuPost( $item ) {

		if ( isset( $item->object ) && 'gm_menu_block' === $item->object && ! empty( $item->object_id ) ) {
			$item_id = intval( $item->object_id );
			if ( $item_id ) {
				return $item_id;
			}
		}

		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return null;
		}

		$val = get_post_meta( $item_id, self::MEGAMENU_META_POST, true );
		$val = $val ? intval( $val ) : null;

		return $val;
	}

	/**
	 * @param        $item
	 * @param string $reserveUrl
	 *
	 * @return int|null
	 */
	protected function menuBlockURL( $item, $reserveUrl = '' ) {

		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return null;
		}

		$val = get_post_meta( $item_id, self::MENU_BLOCK_URL, true );
		$val = $val ? esc_url( $val ) : $reserveUrl;

		return $val;
	}

	/**
	 * Get post content
	 *
	 * @param integer $post_id post id.
	 *
	 * @return string
	 */
	protected function getMenuBlockPostContent( $post_id ) {
		global $post;

		$mm_content = '';

		if ( $post_id ) {

			$post_id = intval( $post_id );

			$wpml_gm_menu_block_id = apply_filters( 'wpml_object_id', $post_id, 'gm_menu_block', true );

			// Copy global $post exemplar.
			$_post = $post;
			$post  = get_post( $wpml_gm_menu_block_id );

			if ( empty( $post->ID ) ) {
				return $mm_content;
			}


			if ( class_exists( 'FLBuilder' ) &&
				class_exists( 'FLBuilderModel' ) &&
				method_exists( 'FLBuilderModel', 'is_builder_enabled' ) &&
				method_exists( 'FLBuilder', 'enqueue_layout_styles_scripts_by_id' ) &&
				method_exists( 'FLBuilder', 'render_content_by_id' ) &&
				FLBuilderModel::is_builder_enabled( $post->ID )
			) {

				ob_start();

				// Enqueue styles and scripts for this post.
				FLBuilder::enqueue_layout_styles_scripts_by_id( $post->ID );

				// Render the builder content.
				FLBuilder::render_content_by_id( $post->ID );

				$mm_content = ob_get_clean();

			} else {

				$mm_content = apply_filters( 'the_content', $post->post_content );

			}


			// Recovery global $post exemplar.
			$post = $_post;

		}

		return $mm_content;
	}

	/**
	 * @param $item
	 *
	 * @return int|mixed
	 */
	protected function megaMenuPostNotMobile( $item ) {
		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return false;
		}

		return get_post_meta( $item_id, self::MEGAMENU_META_POST_NOT_MOBILE, true ) !== '';
	}

	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	protected function getIcon( $item ) {
		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return '';
		}

		return get_post_meta( $item_id, self::ICON_CLASS, true );
	}

	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	protected function getBackgroundRepeat( $item ) {
		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return '';
		}

		return get_post_meta( $item_id, self::MEGAMENU_BACKGROUND_REPEAT, true );
	}

	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	protected function getBackgroundPosition( $item ) {
		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return '';
		}

		return get_post_meta( $item_id, self::MEGAMENU_BACKGROUND_POSITION, true );
	}

	/**
	 * @param $item
	 *
	 * @return mixed|string
	 */
	protected function getBackgroundSize( $item ) {
		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return 'full';
		}

		$size = get_post_meta( $item_id, self::MEGAMENU_BACKGROUND_SIZE, true );
		if ( empty( $size ) ) {
			$size = 'full';
		}

		return $size;
	}

	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	protected function getBackgroundId( $item ) {
		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return null;
		}

		return get_post_meta( $item_id, self::MEGAMENU_BACKGROUND, true );
	}

	/**
	 * @param        $item
	 * @param string $size
	 *
	 * @return false|mixed|string
	 */
	protected function getBackgroundUrl( $item, $size = 'full' ) {
		static $cache = array();

		$id = $this->getBackgroundId( $item );

		if ( empty( $id ) ) {
			return '';
		}

		if ( isset( $cache[ $id ][ $size ] ) ) {
			return $cache[ $id ][ $size ];
		}

		if ( 'full' === $size ) {
			$attach_url = wp_get_attachment_url( $id );
		} else {
			$attach_url = $this->getBackgroundUrlThumbnail( $item, $size );
		}

		$cache[ $id ][ $size ] = $attach_url;

		return $attach_url;
	}

	/**
	 * @param        $item
	 * @param string $size
	 *
	 * @return false|mixed|string
	 */
	protected function getBackgroundUrlThumbnail( $item, $size = 'thumbnail' ) {
		$id = $this->getBackgroundId( $item );

		if ( empty( $id ) ) {
			return '';
		}

		$thumb_url_array = wp_get_attachment_image_src( $id, $size );

		$thumb_url = empty( $thumb_url_array[0] ) ? $this->getBackgroundUrl( $item ) : $thumb_url_array[0];

		return $thumb_url;
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	protected function isShowFeaturedImage( $item ) {
		$item_id = $this->getId( $item );
		if ( empty( $item_id ) ) {
			return false;
		}

		return get_post_meta( $item_id, self::IS_SHOW_FEATURED_IMAGE, true ) !== '';
	}

	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	protected function getId( $item ) {
		if ( is_object( $item ) ) {

			if ( isset( $item->object ) && 'wpml_ls_menu_item' === $item->object ) {
				return null;
			}

			if ( isset( $item->db_id ) ) {
				$item_id = $item->db_id;
			} else {
				$item_id = intval( $item->ID );
			}

			return $item_id;
		}

		return $item;
	}
}
