<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );


if ( ! function_exists( 'crane_register_taxonomy_helper' ) ) {
	/**
	 * Get dashboard tile template
	 *
	 * @param string $type
	 * @param string $term_domain
	 *
	 */
	function crane_register_taxonomy_helper( $type, $term_domain ) {

		if ('wocommerce' == $type ) {
			register_taxonomy(
				$term_domain,
				apply_filters( 'woocommerce_taxonomy_objects_' . $term_domain, array( 'product' ) ),
				apply_filters( 'woocommerce_taxonomy_args_' . $term_domain, array(
					'hierarchical' => true,
					'show_ui'      => false,
					'query_var'    => true,
					'rewrite'      => false,
				) )
			);
		}

	}
}

if ( ! function_exists( 'crane_get_terms_by_taxonomy' ) ) {
	/**
	 * Get taxonomy items (terms)
	 *
	 * @param string $taxonomy_name
	 * @param string $term_ids
	 *
	 * @return array
	 */
	function crane_get_terms_by_taxonomy( $taxonomy_name, $term_ids = '' ) {
		static $cache = array();

		$taxonomy_name = esc_attr( $taxonomy_name );

		$terms = array();
		if ( ! $taxonomy_name ) {
			return $terms;
		}

		$term_ids_cache = $term_ids ? md5( $term_ids ) : 'none';

		if ( isset( $cache[ $taxonomy_name ][ $term_ids_cache ] ) ) {
			return $cache[ $taxonomy_name ][ $term_ids_cache ];
		}

		if ( ! empty( $term_ids ) && $all_term = crane_get_terms_by_taxonomy( $taxonomy_name ) ) {
			$term_ids = empty( $term_ids ) ? array() : explode( ',', $term_ids );
			foreach ( $all_term as $term ) {
				if ( in_array( $term['slug'], $term_ids, true ) ) {
					$terms[] = $term['id'];
				}
			}
			$term_ids = ( ! empty( $terms ) ? implode( ',', $terms ) : array() );
		}

		$args = array(
			'taxonomy'   => $taxonomy_name,
			'hide_empty' => false,
			'include'    => $term_ids
		);

		$tax_terms = get_terms( $args );

		$terms = array();
		if ( ! is_wp_error( $tax_terms ) ) {
			foreach ( $tax_terms as $term ) {
				$terms[] = array(
					'id'    => $term->term_id,
					'title' => $term->name,
					'slug'  => $term->slug
				);
			}
		}

		$cache[ $taxonomy_name ][ $term_ids_cache ] = $terms;

		return $terms;
	}

}


if ( ! function_exists( 'crane_debug_value' ) ) {
	/**
	 * Write some variable value to debug file, when it's hard to output it directly
	 *
	 * @param $value
	 * @param bool|FALSE $with_backtrace
	 * @param bool $append
	 */
	function crane_debug_value( $value, $with_backtrace = false, $append = false ) {

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			if ( file_exists( ABSPATH . '/wp-admin/includes/file.php' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
		}
		if ( empty( $wp_filesystem ) ) {
			return;
		}

		$data = '';
		static $auto_append = false;

		$data .= '[' . date( 'm/d/Y h:i:s a', time() ) . ']' . "\n";

		if ( $with_backtrace ) {
			$backtrace = debug_backtrace();
			array_shift( $backtrace );
			$data .= print_r( $backtrace, true ) . ":\n";
		}

		$upload_dir_data = wp_upload_dir();
		$basedir         = get_template_directory();
		if ( isset( $upload_dir_data['basedir'] ) ) {
			$basedir = $upload_dir_data['basedir'];
		}

		$filename = $basedir . '/crane_debug.html';

		if ( file_exists( $filename && ! is_writable( $filename ) ) ) {
			$wp_filesystem->chmod( $filename, 0666 );
		}

		ob_start();
		var_dump( $value );
		$data .= ob_get_clean() . "\n\n";
		$is_append = $append ? : $auto_append;


		if ( is_writable( $filename ) || ( ! file_exists( $filename ) && is_writable( dirname( $filename ) ) ) ) {
			if ( $is_append ) {
				$data = $wp_filesystem->get_contents( $filename ) . $data;
			}

			$wp_filesystem->put_contents( $filename, $data );

		}


		$auto_append = true;

	}

}


if ( ! function_exists( 'crane_get_thumb' ) ) {
	/**
	 * Portfolio thumb
	 *
	 * @param $post_id
	 * @param $size
	 * @param bool|false $return_array
	 * @param bool $is_attachment_id
	 *
	 * @return array|false|mixed
	 */
	function crane_get_thumb( $post_id, $size, $return_array = false, $is_attachment_id = false ) {
		$thumb = '';
		if ( ! $is_attachment_id ) {
			$thumb_id = (int) get_post_thumbnail_id( $post_id );
		} else {
			$thumb_id = (int) $post_id;
		}
		if ( $thumb_id ) {
			$attachment_src = wp_get_attachment_image_src( $thumb_id, $size );
			if ( is_array( $attachment_src ) && isset( $attachment_src[0] ) && $attachment_src[0] ) {
				$thumb = $attachment_src;
			}
		}

		if ( $thumb ) {

			return $return_array ? $thumb : $thumb[0];

		} else {

			return false;

		}

	}
}


if ( ! function_exists( 'crane_get_placeholder_html_class' ) ) {
	/**
	 * Return placeholder css classes
	 *
	 * @param mixed $show_placeholder If empty of false this function echo placeholder string class
	 *
	 * @return string
	 */
	function crane_get_placeholder_html_class( $is_image = false ) {

		$show_placeholder = $is_image ? false : true;

		global $crane_options;
		if ( ! isset( $crane_options['show_featured_placeholders'] ) || ! $crane_options['show_featured_placeholders'] ) {
			$show_placeholder = false;
		}

		return $show_placeholder ? ' crane-placeholder crane-placeholder-' . rand( 1, 10 ) : '';
	}
}


if ( ! function_exists( 'grooni_add_page_to_theme_dashboard' ) ) {
	function grooni_add_page_to_dashboard( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null ) {
		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null );
	}
}

if ( ! function_exists( 'grooni_add_page_to_theme_dashboard' ) ) {
	function grooni_add_subpage_to_dashboard( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	}
}


/**
 * @param $html
 * @param $post_id
 *
 * @return string
 */
function grooni_add_to_feature_thumb_box( $html, $post_id ) {

	$selected_option = esc_attr( get_post_meta( $post_id, '_grooni_include_image_to_widget', true ) );
	$checked         = $selected_option ? ' checked' : '';

	return $html .= '
			<label>' . esc_html__( 'Show in widget:', 'grooni-theme-addons' ) . '
				<input type="hidden" name="_grooni_include_image_to_widget" class="crane-image-widget-value" value="' . $selected_option . '">
				<input type="checkbox" class="crane-image-widget-checkbox" value="' . $selected_option . '"' . $checked . '>
				<p class="help">' . esc_html__( 'Use to display in the &laquo;Grooni images&raquo; widgets', 'grooni-theme-addons' ) . '</p>
			</label>
			';
}

add_filter( 'admin_post_thumbnail_html', 'grooni_add_to_feature_thumb_box', 10, 2 );


if ( ! function_exists( 'grooni_featured_image_meta_save' ) ) {
	/**
	 * Function and action to save the new value to the attachment meta
	 *
	 * @param $post_id
	 */
	function grooni_featured_image_meta_save( $post_id ) {
		if ( isset( $_POST['_grooni_include_image_to_widget'] ) ) {
			$img_include = esc_attr( wp_unslash( $_POST['_grooni_include_image_to_widget'] ) );
			if ( 'yes' == $img_include || '1' == $img_include ) {
				$img_include = '1';
			} else {
				$img_include = '0';
			}

			if ( $the_post = wp_is_post_revision( $post_id ) ) {
				$post_id = $the_post;
			}

			update_post_meta( $post_id, '_grooni_include_image_to_widget', $img_include );
		}
	}
}
add_action( 'save_post', 'grooni_featured_image_meta_save' );


if ( ! function_exists( 'grooni_add_user_profile_contactmethods' ) ) {
	/**
	 * Display Author’s social media links on the Profile Page
	 *
	 * @param $contactmethods
	 *
	 * @return mixed
	 */
	function grooni_add_user_profile_contactmethods( $contactmethods ) {

		$contactmethods['facebook'] = 'Facebook URL';
		$contactmethods['twitter']  = 'Twitter URL';
		$contactmethods['youtube']  = 'Youtube URL';
		$contactmethods['google+']  = 'Google+ URL';

		return $contactmethods;
	}
}
add_filter( 'user_contactmethods', 'grooni_add_user_profile_contactmethods', 10, 1 );


if ( ! function_exists( 'grooni_is_lazyload_enabled' ) ) {
	/**
	 * Get a value of the Lazy Load setting
	 *
	 * @return bool
	 */
	function grooni_is_lazyload_enabled() {

		$theme_slug           = GROONI_THEME_ADDONS_CURRENT_THEME_SLUG;
		$theme_options_helper = ucfirst( $theme_slug ) . '_Options_Helper';
		$theme_options_var    = $theme_slug . '_options';
		$lazyload_enabled     = false;

		if ( class_exists( $theme_options_helper ) ) {
			$options_helper   = new $theme_options_helper();
			$lazyload_enabled = $options_helper::is_lazyload_enabled();
		} else {
			if ( isset( $GLOBALS[ $theme_options_var ] ) ) {
				$options          = $GLOBALS[ $theme_options_var ];
				$lazyload_enabled = isset( $options['lazyload'] ) && $options['lazyload'];
			}
		}

		return $lazyload_enabled;

	}

}


add_action( 'admin_bar_menu', 'grooni_woo_admin_bar_menu', 100 );
if ( ! function_exists( 'grooni_woo_admin_bar_menu' ) ) {

	/**
	 * Show "edit page" button for shop main page
	 *
	 * @param $wp_admin_bar
	 */
	function grooni_woo_admin_bar_menu( $wp_admin_bar ) {
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$main_shop_id = wc_get_page_id( 'shop' );
			$wp_admin_bar->add_menu( array(
				'id'    => 'grooni_shop_edit',
				'title' => '<span class="admin-bar-edit-icon ab-item"></span>' . esc_html__( 'Edit Shop Page', 'grooni-theme-addons' ),
				'href'  => get_site_url() . '/wp-admin/post.php?post=' . $main_shop_id . '&action=edit',
				'meta'  => [ 'class' => 'ab-item' ]
			) );
		}
	}
}


if ( ! function_exists( 'grooni_filter_action' ) ) {
	function grooni_filter_action( $action, $filter_name, $function_name, $order = 10, $params = 0 ) {

		if ( 'image_srcset' == $filter_name ) {
			$filter_name = 'wp_calculate_image_srcset';
		}

		switch ( $action ) {
			case 'add':
				add_filter( $filter_name, $function_name, $order, $params );
				break;

			case 'remove':
				remove_filter( $filter_name, $function_name, $order );
				break;
		}

	}
}


/**
 * Check menu name for posts meta, to,taxonomy meta
 *
 * @param $term_id
 * @param $taxonomy
 */
function grooni_before_menu_name_change( $term_id = 0, $taxonomy = '' ) {

	if ( ! $term_id || 'nav_menu' !== $taxonomy ) {
		return;
	}

	$term = get_term( $term_id, 'nav_menu', 'ARRAY_A' );

	set_transient( 'grooni_edit_nav_menu__' . $term_id, $term, 600 );

}

add_action( 'edit_terms', 'grooni_before_menu_name_change', 50, 2 );


/**
 * Update menu name for posts meta, to,taxonomy meta
 *
 * @param $menu_id
 * @param $menu_data
 */
function grooni_update_menu_name_change( $term_id = 0, $menu_data = '' ) {

	if ( ! $term_id ) {
		return;
	}

	$term_saved = get_transient( 'grooni_edit_nav_menu__' . $term_id );

	if ( false === $term_saved ) {
		return;
	}

	$term = get_term( $term_id, 'nav_menu', 'ARRAY_A' );

	if ( ( ! isset( $term_saved['slug'] ) || ! isset( $term['slug'] ) ) && $term_saved['slug'] === $term['slug'] ) {
		return;
	}

	$theme_options_name = GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '_options';
	$_redux_otions      = maybe_unserialize( get_option( $theme_options_name ) );

	if ( class_exists( 'Redux' ) && is_array( $_redux_otions ) ) {

		$options_for_check = array(
			'regular-page-nav_menu',
			'portfolio-archive-nav_menu',
			'portfolio-single-nav_menu',
			'blog-nav_menu',
			'blog-single-nav_menu',
			'shop-nav_menu',
			'shop-single-nav_menu',
			'search-nav_menu',
			'404-nav_menu'
		);

		foreach ( $options_for_check as $opt_name ) {
			if ( ! isset( $_redux_otions[ $opt_name ] ) || $_redux_otions[ $opt_name ] !== $term_saved['slug'] ) {
				continue;
			}

			Redux::setOption( $theme_options_name, $opt_name, $term['slug'] );

		}

	}

	delete_transient( 'grooni_edit_nav_menu__' . $term_id );

	global $wpdb;

	// TAXONOMY META
	$term_meta_key_name = GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '_term_additional_meta';
	$query_values       = "SELECT * FROM " . $wpdb->termmeta . " WHERE meta_key LIKE '" . $term_meta_key_name . "';";
	$all_term_meta      = $wpdb->get_results( $query_values, OBJECT );

	if ( ! $all_term_meta ) {
		//$wpdb->print_error(); // do nothing ...
	} else {

		foreach ( $all_term_meta as $one_term_meta ) {
			if ( empty( $one_term_meta->meta_value ) ) {
				continue;
			}

			$term_meta = maybe_unserialize( $one_term_meta->meta_value );

			if ( empty( $term_meta ) ) {
				continue;
			}

			if ( ! isset( $term_meta['nav_menu'] ) || $term_meta['nav_menu'] !== $term_saved['slug'] ) {
				continue;
			}

			$term_meta['nav_menu'] = $term['slug'];
			$term_meta             = serialize( $term_meta );

			$wpdb->update( $wpdb->termmeta, array( 'meta_value' => $term_meta ), array( 'meta_id' => $one_term_meta->meta_id ) );

		}

	}


	// POST META
	$query_values    = "SELECT * FROM " . $wpdb->postmeta . " WHERE meta_key LIKE 'grooni_meta';";
	$all_grooni_meta = $wpdb->get_results( $query_values, OBJECT );

	if ( ! $all_grooni_meta ) {
		//$wpdb->print_error(); // do nothing ...
	} else {

		foreach ( $all_grooni_meta as $one_grooni_meta ) {
			if ( empty( $one_grooni_meta->meta_value ) ) {
				continue;
			}

			$grooni_meta = json_decode( $one_grooni_meta->meta_value, true );

			if ( empty( $grooni_meta ) ) {
				continue;
			}

			if ( ! isset( $grooni_meta['main_menu'] ) || $grooni_meta['main_menu'] !== $term_saved['slug'] ) {
				continue;
			}

			$grooni_meta['main_menu'] = $term['slug'];
			$grooni_meta              = json_encode( $grooni_meta, JSON_UNESCAPED_UNICODE );

			$wpdb->update( $wpdb->postmeta, array( 'meta_value' => $grooni_meta ), array( 'meta_id' => $one_grooni_meta->meta_id ) );

		}

	}

}

add_action( 'wp_update_nav_menu', 'grooni_update_menu_name_change', 50, 2 );


if ( ! function_exists( 'grooni_maintenance_admin_bar' ) ) {
	function grooni_maintenance_admin_bar() {
		add_action( 'admin_bar_menu', 'grooni_maintenance_admin_bar_notice', 9999 );
	}
}


if ( ! function_exists( 'grooni_maintenance_admin_bar_notice' ) ) {
	function grooni_maintenance_admin_bar_notice() {

		global $wp_admin_bar;

		if ( empty( $wp_admin_bar ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '-maintenance-notice',
				'title' => __( 'Maintenance Mode', 'grooni-theme-addons' ),
				'href'  => admin_url() . 'admin.php?page=' . GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '-theme-options',
				'meta'  => array(
					'class' => GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '-maintenance',
					'html'  => '<style>.' . GROONI_THEME_ADDONS_CURRENT_THEME_SLUG . '-maintenance a{background-color:rgba(245,0,0,0.3)!important;color:#f74200!important;font-weight:900!important;}</style>',
				),
			)
		);

	}
}


function grooni_activate_plugin( $plugin_info ) {
	// Don't try to activate on upgrade of active plugin as WP will do this already.
	if ( ! is_plugin_active( $plugin_info ) ) {
		$activate = activate_plugin( $plugin_info );
	}
}

function grooni_add_meta_action() {
	add_action( 'add_meta_boxes', array( 'Crane_Meta_Data', 'show_post_metaboxes' ) );
}


/**
 * @param $group
 * @param $postType
 */
function grooni_add_metabox_by_post_type( $group, $postType ) {
	add_meta_box(
		$group->getName(),
		$group->getTitle(),
		array( $group, 'show' ),
		$postType,
		$group->getContext(),
		$group->getPriority()
	);
}

function grooni_get_users_array() {

	static $users = array();

	if ( ! empty( $users ) ) {
		return $users;
	}

	foreach ( get_users() as $user ) {
		/**
		 * @var $user WP_User
		 */
		$users[ $user->ID ] = $user->display_name;
	}

	return $users;
}
