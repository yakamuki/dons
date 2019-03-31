<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );

/**
 * Class GroovyMenuAdminWalker
 */
class GroovyMenuAdminWalker extends GroovyMenuWalkerNavMenu {

	protected static $grooniColsVariants = array(
		'1'           => '100%', // 1
		'2'           => '50% + 50%', // 2
		'60-40'       => '60% + 40%',
		'40-60'       => '40% + 60%',
		'66-33'       => '66% + 33%',
		'33-66'       => '33% + 66%',
		'25-75'       => '25% + 75%',
		'75-25'       => '75% + 25%',
		'20-80'       => '20% + 80%',
		'80-20'       => '80% + 20%',
		'90-10'       => '90% + 10%',
		'10-90'       => '10% + 90%',
		'3'           => '33% + 33% + 33%', // 3
		'50-25-25'    => '50% + 25% + 25%',
		'25-25-50'    => '25% + 25% + 50%',
		'60-20-20'    => '60% + 20% + 20%',
		'20-60-20'    => '20% + 60% + 20%',
		'20-20-60'    => '20% + 20% + 60%',
		'20-30-50'    => '20% + 30% + 50%',
		'50-30-20'    => '50% + 30% + 20%',
		'4'           => '25% + 25% + 25% + 25%', // 4
		'40-20-20-20' => '40% + 20% + 20% + 20%',
		'20-20-20-40' => '20% + 20% + 20% + 40%',
		'50-20-20-10' => '50% + 20% + 20% + 10%',
		'10-20-20-50' => '10% + 20% + 20% + 50%',
		'5'           => '20% + 20% + 20% + 20% + 20%', // 5
		'10'          => '10 Columns with 10% each', // 10
	);

	/**
	 * @return array
	 */
	static function megaMenuColsVariants() {
		$cols_variants = self::$grooniColsVariants;
		if ( isset( $cols_variants['10'] ) ) {
			$cols_variants['10'] = esc_html__( '10 Columns with 10% each', 'groovy-menu' );
		}

		return $cols_variants;
	}


	/**
	 * @return array
	 */
	static function megaMenuPosts() {
		$mm_posts = array( '' => '--- ' . esc_html__( 'none', 'groovy-menu' ) . ' ---' );

		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'gm_menu_block',
			'post_status'    => 'publish',
		);

		$gm_menu_block = get_posts( $args );

		foreach ( $gm_menu_block as $mega_menu_posts ) {
			$mm_posts[ $mega_menu_posts->ID ] = $mega_menu_posts->post_title;
		}

		return $mm_posts;
	}

	public static function registerWalker() {

		$admin_walker_priority = 10;
		$styles_class          = new GroovyMenuStyle( null );

		if ( $styles_class->getGlobal( 'tools', 'admin_walker_priority' ) ) {
			$admin_walker_priority = 999999;
		}

		add_filter( 'wp_edit_nav_menu_walker', 'GroovyMenuAdminWalker::get_edit_walker', $admin_walker_priority, 2 );
		add_filter( 'wp_setup_nav_menu_item', 'GroovyMenuAdminWalker::setup_fields' );

		add_action( 'wp_update_nav_menu_item', 'GroovyMenuAdminWalker::update_fields', 10, 2 );
	}

	/**
	 * @param string $output
	 * @param int    $depth
	 * @param array  $args
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
	}

	/**
	 * @param string $output
	 * @param int    $depth
	 * @param array  $args
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
	}

	/**
	 * @return string
	 */
	public static function get_edit_walker() {
		return 'GroovyMenuAdminWalker';
	}

	/**
	 * Update post meta fields
	 *
	 * @param string     $menu_id menu id.
	 * @param string     $item_id item id.
	 * @param null|array $args    arguments.
	 */
	public static function update_fields( $menu_id, $item_id, $args = null ) {
		if ( isset( $_POST['wp_customize'] ) ) {
			return;
		}
		if ( ! isset( $_REQUEST['groovymenu-megamenu'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-megamenu'][ $item_id ] = '';
		}
		if ( ! isset( $_REQUEST['groovymenu-do-not-show-title'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-do-not-show-title'][ $item_id ] = '';
		}
		if ( ! isset( $_REQUEST['groovymenu-icon-class'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-icon-class'][ $item_id ] = '';
		}
		if ( ! isset( $_REQUEST['groovymenu-is-show-featured'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-is-show-featured'][ $item_id ] = '';
		}
		if ( ! isset( $_REQUEST['groovymenu-megamenu-bg'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-megamenu-bg'][ $item_id ] = '';
		}
		if ( ! isset( $_REQUEST['groovymenu-megamenu-bg-position'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-megamenu-bg-position'][ $item_id ] = '';
		}
		if ( ! isset( $_REQUEST['groovymenu-megamenu-bg-repeat'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-megamenu-bg-repeat'][ $item_id ] = '';
		}
		if ( ! isset( $_REQUEST['groovymenu-megamenu-bg-size'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-megamenu-bg-size'][ $item_id ] = '';
		}
		if ( ! isset( $_REQUEST['groovymenu-megamenu-cols'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-megamenu-cols'][ $item_id ] = '5';
		}
		if ( ! isset( $_REQUEST['groovymenu-block-url'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-block-url'][ $item_id ] = '';
		}
		if ( ! isset( $_REQUEST['groovymenu-megamenu-post-not-mobile'][ $item_id ] ) ) {
			$_REQUEST['groovymenu-megamenu-post-not-mobile'][ $item_id ] = '';
		}
		update_post_meta( $item_id, self::IS_MEGAMENU_META, $_REQUEST['groovymenu-megamenu'][ $item_id ] );
		update_post_meta( $item_id, self::DO_NOT_SHOW_TITLE, $_REQUEST['groovymenu-do-not-show-title'][ $item_id ] );
		update_post_meta( $item_id, self::MEGAMENU_META_COLS, $_REQUEST['groovymenu-megamenu-cols'][ $item_id ] );
		update_post_meta( $item_id, self::MENU_BLOCK_URL, $_REQUEST['groovymenu-block-url'][ $item_id ] );
		//update_post_meta( $item_id, self::MEGAMENU_META_POST, $_REQUEST['groovymenu-megamenu-post'][ $item_id ] );
		update_post_meta( $item_id, self::MEGAMENU_META_POST_NOT_MOBILE, $_REQUEST['groovymenu-megamenu-post-not-mobile'][ $item_id ] );
		update_post_meta( $item_id, self::IS_SHOW_FEATURED_IMAGE, $_REQUEST['groovymenu-is-show-featured'][ $item_id ] );
		update_post_meta( $item_id, self::ICON_CLASS, $_REQUEST['groovymenu-icon-class'][ $item_id ] );
		update_post_meta( $item_id, self::MEGAMENU_BACKGROUND, $_REQUEST['groovymenu-megamenu-bg'][ $item_id ] );
		update_post_meta( $item_id, self::MEGAMENU_BACKGROUND_POSITION, $_REQUEST['groovymenu-megamenu-bg-position'][ $item_id ] );
		update_post_meta( $item_id, self::MEGAMENU_BACKGROUND_REPEAT, $_REQUEST['groovymenu-megamenu-bg-repeat'][ $item_id ] );
		update_post_meta( $item_id, self::MEGAMENU_BACKGROUND_SIZE, $_REQUEST['groovymenu-megamenu-bg-size'][ $item_id ] );
	}

	/**
	 * Get params from meta
	 *
	 * @param object $menu_item menu item.
	 *
	 * @return mixed
	 */
	public static function setup_fields( $menu_item ) {
		$menu_item->is_megamenu            = get_post_meta( $menu_item->ID, self::IS_MEGAMENU_META, true );
		$menu_item->is_show_featured_image = get_post_meta( $menu_item->ID, self::IS_SHOW_FEATURED_IMAGE, true );

		return $menu_item;
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

		global $_wp_nav_menu_max_depth;
		$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

		ob_start();
		$item_id      = strval( esc_attr( $item->ID ) );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$original_title = '';
		if ( 'taxonomy' === $item->type ) {
			$original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
			if ( is_wp_error( $original_title ) ) {
				$original_title = false;
			}
		} elseif ( 'post_type' === $item->type ) {
			$original_object = get_post( $item->object_id );
			$original_title  = get_the_title( $original_object->ID );
		}

		$classes = array(
			'menu-item menu-item-depth-' . $depth,
			'menu-item-' . esc_attr( $item->object ),
			'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && strval( $item_id ) === $_GET['edit-menu-item'] ) ? 'active' : 'inactive' ),
		);

		$title = $item->title;

		if ( ! empty( $item->_invalid ) ) {
			$classes[] = 'menu-item-invalid';
			/* translators: %s: title of menu item which is invalid */
			$title = sprintf( esc_html__( '%s (Invalid)', 'groovy-menu' ), $item->title );
		} elseif ( isset( $item->post_status ) && 'draft' === $item->post_status ) {
			$classes[] = 'pending';
			/* translators: %s: title of menu item in draft status */
			$title = sprintf( esc_html__( '%s (Pending)', 'groovy-menu' ), $item->title );
		}

		$title = ( ! isset( $item->label ) || '' === $item->label ) ? $title : $item->label;

		$submenu_text_escaped = '';
		if ( 0 === $depth ) {
			$submenu_text_escaped = 'style="display: none;"';
		}

		$item_classes = array();
		if ( isset( $item->classes ) && ! empty( $item->classes ) && is_array( $item->classes ) ) {
			foreach ( $item->classes as $one_class ) {
				$elem = maybe_unserialize( $one_class );
				if ( is_array( $elem ) ) {
					foreach ( $elem as $el ) {
						if ( ! empty( $el ) ) {
							$item_classes[] = $el;
						}
					}
				} else {
					$item_classes[] = $one_class;
				}
			}
		}

		$item_classes = implode( ' ', $item_classes );

		$itemTypeLabel = $item->type_label;
		if ( $this->isMegaMenu( $item ) ) {
			$itemTypeLabel .= ' [' . esc_html__( 'Mega Menu', 'groovy-menu' ) . ']';
		}

		$gm_menu_block = false;
		if ( isset( $item->object ) && 'gm_menu_block' === $item->object ) {
			$gm_menu_block = true;
		}

		?>
	<li id="menu-item-<?php echo esc_attr( $item_id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<dl class="menu-item-bar">
			<dt class="menu-item-handle">
				<span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ); ?></span> <span
						class="is-submenu" <?php echo $submenu_text_escaped; ?>><?php esc_html_e( 'sub item', 'groovy-menu' ); ?></span></span>
				<span class="item-controls">
						<span class="item-type"><?php echo esc_html( $itemTypeLabel ); ?></span>
						<span class="item-order hide-if-js">
							<a href="<?php
							echo wp_nonce_url(
								add_query_arg(
									array(
										'action'    => 'move-up-menu-item',
										'menu-item' => $item_id,
									),
									remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
								),
								'move-menu_item'
							);
							?>" class="item-move-up"><abbr title="<?php esc_attr_e( 'Move up', 'groovy-menu' ); ?>">&#8593;</abbr></a>
							|
							<a href="<?php
							echo wp_nonce_url(
								add_query_arg(
									array(
										'action'    => 'move-down-menu-item',
										'menu-item' => $item_id,
									),
									remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
								),
								'move-menu_item'
							);
							?>" class="item-move-down"><abbr title="<?php esc_attr_e( 'Move down', 'groovy-menu' ); ?>">
									&#8595;</abbr></a>
						</span>
						<a class="item-edit" id="edit-<?php echo esc_attr( $item_id ); ?>"
							title="<?php esc_attr_e( 'Edit Menu Item', 'groovy-menu' ); ?>" href="<?php
						echo ( isset( $_GET['edit-menu-item'] ) && strval( $item_id ) === $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
						?>"><?php esc_html_e( 'Edit Menu Item', 'groovy-menu' ); ?></a>
					</span>
			</dt>
		</dl>

		<div class="menu-item-settings" id="menu-item-settings-<?php echo esc_attr( $item_id ); ?>">
			<?php if ( 'custom' === $item->type ) : ?>
				<p class="field-url description description-wide">
					<label for="edit-menu-item-url-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'URL', 'groovy-menu' ); ?><br/>
						<input type="text" id="edit-menu-item-url-<?php echo esc_attr( $item_id ); ?>"
							class="widefat code edit-menu-item-url"
							name="menu-item-url[<?php echo esc_attr( $item_id ); ?>]"
							value="<?php echo esc_attr( $item->url ); ?>"/>
					</label>
				</p>
			<?php endif; ?>
			<p class="description description-thin">
				<label for="edit-menu-item-title-<?php echo esc_attr( $item_id ); ?>">
					<?php if ( $depth === 1 ) { ?>
						<?php esc_html_e( 'Navigation Label ("-" to hide)', 'groovy-menu' ); ?>
					<?php } else { ?>
						<?php esc_html_e( 'Navigation Label', 'groovy-menu' ); ?>
					<?php } ?>
					<br/>
					<input type="text" id="edit-menu-item-title-<?php echo esc_attr( $item_id ); ?>"
						class="widefat edit-menu-item-title" name="menu-item-title[<?php echo esc_attr( $item_id ); ?>]"
						value="<?php echo esc_attr( $item->title ); ?>"/>
				</label>
			</p>

			<p class="description description-thin">
				<label for="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Title Attribute', 'groovy-menu' ); ?><br/>
					<input type="text" id="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>"
						class="widefat edit-menu-item-attr-title"
						name="menu-item-attr-title[<?php echo esc_attr( $item_id ); ?>]"
						value="<?php echo esc_attr( $item->post_excerpt ); ?>"/>
				</label>
			</p>

			<?php if ( $gm_menu_block ) : ?>
				<p class="description description-wide groovymenu-block-url">
					<?php
					$value = $this->menuBlockURL( $item );
					if ( ! $value ) {
						$value = '';
					}
					?>
					<label for="groovymenu-block-url-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Menu block URL', 'groovy-menu' ); ?><br/>
						<input type="text" id="groovymenu-block-url-<?php echo esc_attr( $item_id ); ?>"
							class="widefat code groovymenu-block-url"
							name="groovymenu-block-url[<?php echo esc_attr( $item_id ); ?>]"
							value="<?php echo esc_attr( $value ); ?>"/>
					</label>
				</p>
			<?php endif; ?>

			<p class="field-link-target description">
				<label for="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>">
					<input type="checkbox" id="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>" value="_blank"
						name="menu-item-target[<?php echo esc_attr( $item_id ); ?>]"<?php checked( $item->target, '_blank' ); ?> />
					<?php esc_html_e( 'Open link in a new window/tab', 'groovy-menu' ); ?>
				</label>
			</p>

			<p class="field-css-classes description description-thin">
				<label for="edit-menu-item-classes-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'CSS Classes (optional)', 'groovy-menu' ); ?><br/>
					<input type="text" id="edit-menu-item-classes-<?php echo esc_attr( $item_id ); ?>"
						class="widefat code edit-menu-item-classes"
						name="menu-item-classes[<?php echo esc_attr( $item_id ); ?>]"
						value="<?php echo esc_attr( $item_classes ); ?>"/>
				</label>
			</p>

			<p class="field-xfn description description-thin">
				<label for="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Link Relationship (XFN)', 'groovy-menu' ); ?><br/>
					<input type="text" id="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>"
						class="widefat code edit-menu-item-xfn"
						name="menu-item-xfn[<?php echo esc_attr( $item_id ); ?>]"
						value="<?php echo esc_attr( $item->xfn ); ?>"/>
				</label>
			</p>

			<p class="field-description description description-wide">
				<label for="edit-menu-item-description-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Description', 'groovy-menu' ); ?><br/>
					<textarea id="edit-menu-item-description-<?php echo esc_attr( $item_id ); ?>"
						class="widefat edit-menu-item-description" rows="3" cols="20"
						name="menu-item-description[<?php echo esc_attr( $item_id ); ?>]"><?php echo esc_html( $item->description ); // textarea_escaped ?></textarea>
					<span class="description"><?php esc_html_e( 'The description will be displayed in the menu if the current theme supports it.', 'groovy-menu' ); ?></span>
				</label>
			</p>

			<p class="description description-wide">
				<?php
				$value = $this->getIcon( $item );
				?>
				<label for="edit-menu-item-icon-class-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Icon', 'groovy-menu' ); ?>
					<br>
					<span class="gm-icon-preview">
							<span class="<?php echo esc_attr( $value ); ?>"></span>
						</span>
					<input
							type="text"
							value="<?php echo esc_attr( $value ); ?>"
							class="groovymenu-icon-class"
							id="groovymenu-icon-class-<?php echo esc_attr( $item_id ); ?>"
							name="groovymenu-icon-class[<?php echo esc_attr( $item_id ); ?>]" <?php echo esc_attr( $value ); ?>
					/>
					<button
							type="button"
							class="gm-select-icon"
							data-toggle="modal"
							data-target="#gm-icon-settings-modal"
					>
						<?php esc_html_e( 'Select icon', 'groovy-menu' ); ?>
					</button>
				</label>
			</p>
			<p class="description description-wide">
				<?php
				$value = '';
				if ( $this->doNotShowTitle( $item ) ) {
					$value = "checked='checked'";
				}
				?>
				<label for="edit-menu-item-do-not-show-title-<?php echo esc_attr( $item_id ); ?>">
					<input
						type="checkbox"
						value="enabled"
						class="groovymenu-do-not-show-title"
						id="groovymenu-do-not-show-title-<?php echo esc_attr( $item_id ); ?>"
						name="groovymenu-do-not-show-title[<?php echo esc_attr( $item_id ); ?>]"
						<?php echo esc_attr( $value ); ?> />
					<?php esc_html_e( 'Do not show menu item title and link', 'groovy-menu' ); ?>
				</label>
			</p>

			<?php if ( $gm_menu_block ) : ?>
			<p class="description description-wide">
				<?php
				$value = '';
				if ( $this->megaMenuPostNotMobile( $item ) ) {
					$value = "checked='checked'";
				}
				?>
				<label for="edit-menu-item-megamenu-post-<?php echo esc_attr( $item_id ); ?>">
					<input
						type="checkbox"
						value="enabled"
						class="groovymenu-megamenu-post-not-mobile"
						id="groovymenu-megamenu-post-not-mobile-<?php echo esc_attr( $item_id ); ?>"
						name="groovymenu-megamenu-post-not-mobile[<?php echo esc_attr( $item_id ); ?>]"
						<?php echo esc_attr( $value ); ?> />
					<?php esc_html_e( 'Do not show Menu block content on mobile', 'groovy-menu' ); ?>
				</label>
			</p>
			<?php endif; ?>

			<?php if ( $depth === 0 ) { ?>
				<p class="description description-wide">
					<?php
					$value = '';
					if ( $this->isMegaMenu( $item ) ) {
						$value = "checked='checked'";
					}
					?>
					<label for="edit-menu-item-megamenu-<?php echo esc_attr( $item_id ); ?>">
						<input
							type="checkbox"
							value="enabled"
							class="groovymenu-megamenu"
							id="groovymenu-megamenu-<?php echo esc_attr( $item_id ); ?>"
							name="groovymenu-megamenu[<?php echo esc_attr( $item_id ); ?>]"
							<?php echo esc_attr( $value ); ?> />
						<?php esc_html_e( 'Mega menu', 'groovy-menu' ); ?>
					</label>
				</p>
				<p class="description description-wide megamenu-cols megamenu-options-depend">
					<?php
					$value = $this->megaMenuCols( $item );
					if ( ! $value ) {
						$value = '5';
					}
					?>
					<label for="edit-menu-item-megamenu-cols-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Mega menu columns', 'groovy-menu' ); ?><br/>
						<select class="groovymenu-megamenu-cols"
							id="groovymenu-megamenu-cols-<?php echo esc_attr( $item_id ); ?>"
							name="groovymenu-megamenu-cols[<?php echo esc_attr( $item_id ); ?>]">
							<?php
							foreach ( GroovyMenuAdminWalker::megaMenuColsVariants() as $cols => $cols_name ) {
								?>
								<option value="<?php echo esc_attr( $cols ); ?>"<?php echo ( strval( $cols ) === strval( $value ) ) ? ' selected' : '' ?>><?php echo esc_attr( $cols_name ); ?></option>
								<?php
							}
							?>
						</select>

					</label>
				</p>
				<div class="groovymenu-megamenu-bg">
					<div>
						<input type="hidden" class="groovymenu-megamenu-bg-input"
							data-url="<?php echo esc_attr( $this->getBackgroundUrl( $item ) ); ?>"
							data-thumbnail="<?php echo esc_attr( $this->getBackgroundUrlThumbnail( $item ) ); ?>"
							value="<?php echo esc_attr( $this->getBackgroundId( $item ) ); ?>"
							name="groovymenu-megamenu-bg[<?php echo esc_attr( $item_id ); ?>]">
						<button type="button"
							class="button button-primary groovymenu-megamenu-bg-select"><?php esc_html_e( 'Set background image', 'groovy-menu' ); ?>
						</button>
						<button type="button"
							class="button groovymenu-megamenu-bg-remove"><?php esc_html_e( 'Remove background image', 'groovy-menu' ); ?></button>
						<div class="groovymenu-megamenu-bg-preview"></div>

					</div>
					<div>
						<p class="description description-thin">
							<label>
								<?php esc_html_e( 'Background position', 'groovy-menu' ); ?><br/>
								<select class="widefat"
									name="groovymenu-megamenu-bg-position[<?php echo esc_attr( $item_id ); ?>]">
									<?php foreach ( self::$backgroundPositions as $position ) { ?>
										<option value="<?php echo esc_attr( $position ); ?>" <?php echo( $position === $this->getBackgroundPosition( $item ) ? 'selected' : '' ) ?>><?php echo esc_attr( $position ); ?></option>
									<?php } ?>
								</select>
							</label>
						</p>
						<p class="description description-thin">
							<label>
								<?php esc_html_e( 'Background repeat', 'groovy-menu' ); ?><br/>
								<select class="widefat"
									name="groovymenu-megamenu-bg-repeat[<?php echo esc_attr( $item_id ); ?>]">
									<?php foreach ( self::$backgroundRepeats as $repeat ) { ?>
										<option
											value="<?php echo esc_attr( $repeat ); ?>" <?php echo( $repeat === $this->getBackgroundRepeat( $item ) ? 'selected' : '' ) ?>><?php echo esc_attr( $repeat ); ?></option>
									<?php } ?>
								</select>
							</label>
						</p>
						<p class="description description-thin">
							<label>
								<?php esc_html_e( 'Background image size', 'groovy-menu' ); ?><br/>
								<select class="widefat"
									name="groovymenu-megamenu-bg-size[<?php echo esc_attr( $item_id ); ?>]">
									<?php foreach ( GroovyMenuUtils::get_all_image_sizes() as $size => $size_data ) { ?>
										<option
											value="<?php echo esc_attr( $size ); ?>" <?php echo( $size === $this->getBackgroundSize( $item ) ? 'selected' : '' ) ?>><?php echo esc_attr( $size ); ?></option>
									<?php } ?>
								</select>
							</label>
						</p>
					</div>
				</div>
			<?php } ?>

			<?php if ( 'post_type' === $item->type && ! $gm_menu_block ) : ?>
				<p class="gm-show-featured-image-wrapper description-wide">
					<?php
					$value = '';
					if ( $this->isShowFeaturedImage( $item ) ) {
						$value = "checked='checked'";
					}
					?>
					<label for="edit-menu-item-show-featured-image-<?php echo esc_attr( $item_id ); ?>">
						<input type="checkbox" value="enabled" class="groovymenu-show-featured-image"
							id="groovymenu-is-show-featured-<?php echo esc_attr( $item_id ); ?>"
							name="groovymenu-is-show-featured[<?php echo esc_attr( $item_id ); ?>]" <?php echo esc_attr( $value ); ?> />
						<?php esc_html_e( 'Show featured image on hover', 'groovy-menu' ); ?>
					</label>
				</p>
			<?php endif; ?>

			<p class="field-move hide-if-no-js description description-wide">
				<label>
					<span><?php esc_html_e( 'Move', 'groovy-menu' ); ?></span>
					<a href="#" class="menus-move menus-move-up"
						data-dir="up"><?php esc_html_e( 'Up one', 'groovy-menu' ); ?></a>
					<a href="#" class="menus-move menus-move-down"
						data-dir="down"><?php esc_html_e( 'Down one', 'groovy-menu' ); ?></a>
					<a href="#" class="menus-move menus-move-left" data-dir="left"></a>
					<a href="#" class="menus-move menus-move-right" data-dir="right"></a>
					<a href="#" class="menus-move menus-move-top"
						data-dir="top"><?php esc_html_e( 'To the top', 'groovy-menu' ); ?></a>
				</label>
			</p>

			<div class="menu-item-actions description-wide submitbox">
				<?php if ( 'custom' != $item->type && $original_title !== false ) : ?>
					<p class="link-to-original">
						<?php printf( esc_html__( 'Original: %s', 'groovy-menu' ), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
					</p>
				<?php endif; ?>
				<a class="item-delete submitdelete deletion" id="delete-<?php echo esc_attr( $item_id ); ?>" href="<?php
				echo wp_nonce_url(
					add_query_arg(
						array(
							'action'    => 'delete-menu-item',
							'menu-item' => $item_id,
						),
						admin_url( 'nav-menus.php' )
					),
					'delete-menu_item_' . $item_id
				); ?>"><?php esc_html_e( 'Remove', 'groovy-menu' ); ?></a> <span
					class="meta-sep hide-if-no-js"> | </span> <a
					class="item-cancel submitcancel hide-if-no-js" id="cancel-<?php echo esc_attr( $item_id ); ?>"
					href="<?php echo esc_url( add_query_arg( array(
						'edit-menu-item' => $item_id,
						'cancel'         => time()
					), admin_url( 'nav-menus.php' ) ) );
					?>#menu-item-settings-<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Cancel', 'groovy-menu' ); ?></a>
			</div>

			<input class="menu-item-data-db-id" type="hidden"
				name="menu-item-db-id[<?php echo esc_attr( $item_id ); ?>]"
				value="<?php echo esc_attr( $item_id ); ?>"/>
			<input class="menu-item-data-object-id" type="hidden"
				name="menu-item-object-id[<?php echo esc_attr( $item_id ); ?>]"
				value="<?php echo esc_attr( $item->object_id ); ?>"/>
			<input class="menu-item-data-object" type="hidden"
				name="menu-item-object[<?php echo esc_attr( $item_id ); ?>]"
				value="<?php echo esc_attr( $item->object ); ?>"/>
			<input class="menu-item-data-parent-id" type="hidden"
				name="menu-item-parent-id[<?php echo esc_attr( $item_id ); ?>]"
				value="<?php echo esc_attr( $item->menu_item_parent ); ?>"/>
			<input class="menu-item-data-position" type="hidden"
				name="menu-item-position[<?php echo esc_attr( $item_id ); ?>]"
				value="<?php echo esc_attr( $item->menu_order ); ?>"/>
			<input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo esc_attr( $item_id ); ?>]"
				value="<?php echo esc_attr( $item->type ); ?>"/>
		</div>
		<!-- .menu-item-settings-->
		<ul class="menu-item-transport"></ul>
		<?php
		$output .= ob_get_clean();
	}

}
