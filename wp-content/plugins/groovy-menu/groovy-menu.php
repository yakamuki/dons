<?php defined( 'ABSPATH' ) || die( 'This script cannot be accessed directly.' );
/*
Plugin Name: Groovy Menu
Version: 1.6.3
Description: Groovy menu is a modern adjustable and flexible menu designed for creating mobile-friendly menus with a lot of options.
Plugin URI: http://grooni.com/docs/groovy-menu/
Author: Grooni
Author URI: http://grooni.com
Domain Path: /languages/
*/

define( 'GROOVY_MENU_VERSION', '1.6.3' );
define( 'GROOVY_MENU_DB_VER_OPTION', 'groovy_menu_db_version' );
define( 'GROOVY_MENU_PREFIX_WIM', 'groovy-menu-wim' );
define( 'GROOVY_MENU_DIR', plugin_dir_path( __FILE__ ) );
define( 'GROOVY_MENU_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'AUTH_COOKIE' ) && function_exists( 'is_multisite' ) && is_multisite() ) {
	if ( function_exists( 'wp_cookie_constants' ) ) {
		wp_cookie_constants();
	}
}

$db_version = get_option( GROOVY_MENU_DB_VER_OPTION );
if ( ! $db_version ) {
	update_option( GROOVY_MENU_DB_VER_OPTION, GROOVY_MENU_VERSION );
	$db_version = GROOVY_MENU_VERSION;
}

global $gm_supported_module;
$gm_supported_module = array(
	'theme'      => wp_get_theme()->get_template(),
	'post_types' => array(),
	'activate'   => array(),
	'deactivate' => array(),
	'db_version' => $db_version,
);


require_once GROOVY_MENU_DIR . 'includes/theme_support/crane.php';

require_once GROOVY_MENU_DIR . 'includes/GroovyMenuWalkerNavMenu.php';
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuAdminWalker.php';
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuFrontendWalker.php';
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuStyleStorage.php';
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuPreStorage.php';
if ( version_compare( $db_version, '1.4.4', '>' ) ) {
	require_once GROOVY_MENU_DIR . 'includes/GroovyMenuSettings15.php';
	require_once GROOVY_MENU_DIR . 'includes/GroovyMenuStyle15.php';
	require_once GROOVY_MENU_DIR . 'includes/GroovyMenuPreset15.php';
} else {
	require_once GROOVY_MENU_DIR . 'includes/GroovyMenuSettings.php';
	require_once GROOVY_MENU_DIR . 'includes/GroovyMenuStyle.php';
	require_once GROOVY_MENU_DIR . 'includes/GroovyMenuPreset.php';
}
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuMenuBlockPostType.php';
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuUtils.php';
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuSingleMetaPreset.php';
add_action( 'init', array( 'GroovyMenuUtils', 'add_groovy_menu_preset_post_type' ), 3 );
add_filter( 'plugin_row_meta', array( 'GroovyMenuUtils', 'gm_plugin_meta_links' ), 10, 2 );
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuIcons.php';
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuCategoryPreset.php';
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuPreviewModal.php';
require_once GROOVY_MENU_DIR . 'includes/GroovyMenuGFonts.php';

require_once GROOVY_MENU_DIR . 'includes/fields/Field.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Checkbox.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Colorpicker.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Select.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Slider.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Group.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Header.php';
require_once GROOVY_MENU_DIR . 'includes/fields/HiddenInput.php';
require_once GROOVY_MENU_DIR . 'includes/fields/HoverStyle.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Media.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Import.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Export.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Text.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Icons.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Icon.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Textarea.php';
require_once GROOVY_MENU_DIR . 'includes/fields/Number.php';
require_once GROOVY_MENU_DIR . 'includes/fields/PostTypes.php';
require_once GROOVY_MENU_DIR . 'includes/fields/TaxonomyPreset.php';
require_once GROOVY_MENU_DIR . 'includes/fields/InlineStart.php';
require_once GROOVY_MENU_DIR . 'includes/fields/InlineEnd.php';
require_once GROOVY_MENU_DIR . 'includes/fields/LogoType.php';

require_once GROOVY_MENU_DIR . 'includes/migration/migrate.php';

require_once GROOVY_MENU_DIR . 'template/Header.php';

require_once GROOVY_MENU_DIR . 'includes/GroovyMenuWidgetInMenu.php';

require_once GROOVY_MENU_DIR . 'includes/virtual_pages/VirtualPages.php';

register_activation_hook( __FILE__, 'groovy_menu_activation' );
register_deactivation_hook( __FILE__, 'groovy_menu_deactivation' );

// Initialize Groovy Menu.
if ( class_exists( 'GroovyMenuPreset' ) ) {
	new GroovyMenuPreset( null, true );
}

if ( class_exists( 'GroovyMenuSettings' ) ) {
	new GroovyMenuSettings();
}

if ( class_exists( 'GroovyMenuMenuBlockPostType' ) ) {
	new GroovyMenuMenuBlockPostType();
}

if ( class_exists( 'GroovyMenuCategoryPreset' ) ) {
	new GroovyMenuCategoryPreset( array( 'category', 'crane_portfolio_cats', 'post_tag', 'product_cat' ) );
}

if ( class_exists( 'GroovyMenuSingleMetaPreset' ) ) {
	new GroovyMenuSingleMetaPreset();
}

if ( class_exists( 'GroovyMenuAdminWalker' ) ) {
	GroovyMenuAdminWalker::registerWalker();
}


function groovy_menu_activation() {
	global $gm_supported_module;

	foreach ( $gm_supported_module['activate'] as $launch_function ) {
		$launch_function();
	}
}

function groovy_menu_deactivation() {
	global $gm_supported_module;

	foreach ( $gm_supported_module['deactivate'] as $launch_function ) {
		$launch_function();
	}
}


function groovy_menu_scripts() {

	wp_enqueue_style( 'groovy-menu-style', GROOVY_MENU_URL . 'assets/style/frontend.css', [], GROOVY_MENU_VERSION );
	wp_style_add_data( 'groovy-menu-style', 'rtl', 'replace' );
	wp_enqueue_script( 'groovy-menu-js', GROOVY_MENU_URL . 'assets/js/frontend.js', array( 'jquery' ), GROOVY_MENU_VERSION, true );
	wp_localize_script( 'groovy-menu-js', 'groovyMenuHelper', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	foreach ( GroovyMenuFieldIcons::getFonts() as $name => $icon ) {
		wp_enqueue_style( 'groovy-menu-style-fonts-' . $name, esc_url( GroovyMenuUtils::getUploadUri() . 'fonts/' . $name . '.css' ), [], GROOVY_MENU_VERSION );
	}

	/**
	 * Fires when enqueue_script for Groovy Menu
	 *
	 * @since 1.2.20
	 */
	do_action( 'gm_enqueue_script_actions' );

}

function groovy_menu_toolbar() {
	if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() && current_user_can( 'edit_theme_options' ) ) {
		wp_enqueue_style( 'groovy-menu-style-toolbar', GROOVY_MENU_URL . 'assets/style/toolbar.css', [], GROOVY_MENU_VERSION );
		wp_style_add_data( 'groovy-menu-style-toolbar', 'rtl', 'replace' );
	}
}

function groovy_menu_load_textdomain() {
	load_plugin_textdomain( 'groovy-menu', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'init', 'groovy_menu_load_textdomain' );

add_action( 'wp_enqueue_scripts', 'groovy_menu_toolbar' );
add_action( 'admin_enqueue_scripts', 'groovy_menu_toolbar' );
add_action( 'wp_enqueue_scripts', 'groovy_menu_scripts' );
add_action( 'in_admin_footer', function () {
	global $pagenow;
	if ( 'nav-menus.php' === $pagenow ) {
		echo GroovyMenuRenderIconsModal();
	}
} );

require GROOVY_MENU_DIR . 'vendor/update_checker/plugin-update-checker.php';
if ( class_exists( 'Puc_v4_Factory' ) ) {
	$update_checker = Puc_v4_Factory::buildUpdateChecker(
		'http://updates.grooni.com/?action=get_metadata&slug=groovy-menu',
		__FILE__,
		'groovy-menu'
	);
}

add_filter( 'body_class', 'groovy_menu_add_version_class_2_html' );
/**
 * @param $classes
 *
 * @return array
 */
function groovy_menu_add_version_class_2_html( $classes ) {
	$classes[] = 'groovy_menu_' . str_replace( '.', '-', GROOVY_MENU_VERSION );

	return $classes;
}

add_filter( 'admin_body_class', 'groovy_menu_add_admin_body_class' );
/**
 * Adds html classes to the body tag in the dashboard.
 *
 * @param  String $classes Current body classes.
 *
 * @return String          Altered body classes.
 */
function groovy_menu_add_admin_body_class( $classes ) {
	global $gm_supported_module;

	if ( 'crane' === $gm_supported_module['theme'] && defined( 'CRANE_THEME_DB_VER_OPTION' ) ) {
		$crane_db_version = get_option( CRANE_THEME_DB_VER_OPTION );
		$gta_version      = defined( 'GROONI_THEME_ADDONS_VERSION' ) ? GROONI_THEME_ADDONS_VERSION : '1';
		$gta_need_version = version_compare( $gta_version, '1.3.10', '<' );
		if ( ( ! empty( $crane_db_version ) && version_compare( $crane_db_version, '1.3.9.1563', '<' ) ) || $gta_need_version ) {
			$classes = $classes . ' crane-needs-to-update-first';
		}
	}

	return $classes;
}


function gm_is_wplogin() {
	$path = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, ABSPATH );

	return ( ( in_array( $path . 'wp-login.php', get_included_files(), true ) || in_array( $path . 'wp-register.php', get_included_files(), true ) ) || ( isset( $_GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) || '/wp-login.php' === $_SERVER['PHP_SELF'] );
}

// Start pre storage (compile groovy menu preset and nav_menu) before template.
if ( ! is_admin() && ! gm_is_wplogin() ) {
	add_action( 'wp_enqueue_scripts', 'groovy_menu_start_pre_storage', 50 );
}

function groovy_menu_start_pre_storage() {
	if ( class_exists( 'GroovyMenuPreStorage' ) ) {
		GroovyMenuPreStorage::get_instance()->start_pre_storage();
	}
}


if ( ! is_admin() && ! gm_is_wplogin() && GroovyMenuUtils::getAutoIntegration() ) {
	add_action( 'init', 'groovy_menu_start_buffer', 0, 0 );
	add_action( 'shutdown', 'groovy_menu_pre_shutdown', 0 );
	add_filter( 'gm_final_output', 'groovy_menu_add_after_body' );
	add_filter( 'gm_after_body_insert', 'gm_add_groovy_menu_markup' );
}

/**
 * Start buffering on the front-end.
 *
 * @since 1.3.1
 */
function groovy_menu_start_buffer() {
	if ( is_admin() ) {
		return;
	}
	ob_start();
}

/**
 * Before final action.
 *
 * @since 1.3.1
 */
function groovy_menu_pre_shutdown() {
	if ( is_admin() || gm_is_wplogin() ) {
		return;
	}

	$final  = '';
	$levels = ob_get_level();
	for ( $i = 0; $i < $levels; $i ++ ) {
		$final .= ob_get_clean();
	}
	echo apply_filters( 'gm_final_output', $final );
}

/**
 * Parse body tag and add additional output after.
 *
 * @param string $output additional output text for adding.
 *
 * @since 1.3.1
 *
 * @return null|string
 */
function groovy_menu_add_after_body( $output ) {
	if ( is_admin() || gm_is_wplogin() ) {
		return null;
	}

	if ( isset( $_GET['gm_action_preview'] ) ) {
		return $output;
	}

	$after_body = apply_filters( 'gm_after_body_insert', '' );
	$output     = preg_replace( '#(\<body.*\>)#', '$1' . $after_body, $output );

	return $output;
}

/**
 * Add markup
 *
 * @param string $after_body consist html code for insert after body.
 *
 * @since 1.3.1
 *
 * @return string
 */
function gm_add_groovy_menu_markup( $after_body ) {

	$saved_auto_integration = GroovyMenuUtils::getAutoIntegration();

	if ( $saved_auto_integration ) {

		$gm_ids = GroovyMenuPreStorage::get_instance()->search_ids_by_location( array( 'theme_location' => 'gm_primary' ) );

		if ( ! empty( $gm_ids ) ) {

			foreach ( $gm_ids as $gm_id ) {
				$gm_data = GroovyMenuPreStorage::get_instance()->get_gm( $gm_id );

				$after_body .= $gm_data['gm_html'];
			}

		} else {
			$after_body .= groovy_menu( [
				'gm_echo'        => false,
				'theme_location' => 'gm_primary',
			] );
		}
	}

	return $after_body;
}

// This theme uses wp_nav_menu() in one location.
register_nav_menus( array(
	'gm_primary' => esc_html__( 'Groovy menu Primary', 'groovy-menu' ),
) );

/**
 * Return public post types.
 *
 * @return array
 */
function groovy_menu_get_post_types() {
	$post_types = array();

	// get the registered data about each post type with get_post_type_object.
	foreach ( get_post_types() as $type ) {
		$type_obj = get_post_type_object( $type );

		if ( isset( $type_obj->public ) && $type_obj->public ) {
			if ( 'attachment' !== $type_obj->name ) {
				$post_types[ $type_obj->name ] = $type_obj->label;
			}
		}
	}

	return $post_types;
}


/**
 * Return script with preset customs js
 *
 * @param string $uniqid        unique string id.
 * @param bool   $return_string if true: return string wrap in html tag: script. If false return empty string and add script to wp_add_inline_script() function.
 *
 * @return string
 */
function groovy_menu_js_request( $uniqid, $return_string = false ) {
	global $groovyMenuPreview, $groovyMenuSettings;

	if ( $groovyMenuPreview ) {
		$groovyMenuPreview = $uniqid;
	}

	// TODO check 'var groovyMenuSettings = ...' for poly GM blocks
	$additional_js = 'var groovyMenuSettings = ' . wp_json_encode( $groovyMenuSettings ) . '; jQuery(function() {jQuery(\'#' . $uniqid . '\').groovyMenu(groovyMenuSettings);});';

	if ( $return_string ) {
		$tag_name = 'script';
		return "\n" . '<' . esc_attr( $tag_name ) . '>' . $additional_js . '</' . esc_attr( $tag_name ) . '>';
	} else {
		if ( function_exists( 'wp_add_inline_script' ) ) {
			wp_add_inline_script( 'groovy-menu-js', $additional_js );
		}
	}

	return '';
}


/**
 * Return style with preset customs css
 *
 * @param string|integer $preset_id
 * @param string         $compiled_css
 * @param bool           $return_string
 *
 * @return string
 */
function groovy_menu_add_preset_style( $preset_id, $compiled_css, $return_string = false ) {

	if ( empty( $compiled_css ) ) {
		$styles       = new GroovyMenuStyle( $preset_id );
		$compiled_css = $styles->get( 'general', 'compiled_css' );
	}

	if ( $return_string ) {
		$handled_compiled_css = trim( stripcslashes( $compiled_css ) );
		$tag_name             = 'style';

		return "\n" . '<' . $tag_name . ' id="gm-style-preset--' . $preset_id . '" class="gm-compiled-css">' . $handled_compiled_css . '</' . $tag_name . '>';
	} else {
		if ( function_exists( 'wp_add_inline_style' ) ) {
			wp_add_inline_style( 'groovy-menu-style', $compiled_css );
		}
	}

	return '';
}


add_action( 'admin_enqueue_scripts', 'gm_include_code_editor', 10, 1 );
if ( ! function_exists( 'gm_include_code_editor' ) ) {
	/**
	 * Enqueue scripts and styles of codemirror for textarea.
	 *
	 * @param string $hook_suffix suffix of the current page.
	 */
	function gm_include_code_editor( $hook_suffix ) {

		if ( 'toplevel_page_groovy_menu_settings' !== $hook_suffix ) {
			return;
		}

		$output   = '';
		$settings = false;

		foreach ( array( 'css', 'javascript' ) as $type ) {

			$codemirror_params = array( 'autoRefresh' => true );

			if ( 'javascript' === $type ) {
				$codemirror_params['closeBrackets'] = true;
			}

			$settings = false;
			// function wp_enqueue_code_editor() since WP 4.9 .
			if ( function_exists( 'wp_enqueue_code_editor' ) ) {
				$settings = wp_enqueue_code_editor( array(
					'type'       => 'text/' . $type,
					'codemirror' => $codemirror_params,
				) );
			}

			if ( false !== $settings ) {

				$output .= sprintf( '
					var groovyMenuCodeMirror%3$sAreas = $(".gmCodemirrorInit[data-lang_type=\'%2$s\']");
					if (groovyMenuCodeMirror%3$sAreas.length > 0) {
						$.each(groovyMenuCodeMirror%3$sAreas, function(key, element) {
							var codeEditorObj = wp.codeEditor.initialize( element, %1$s );
							codeEditorObj.codemirror.on("change", function( cm ) {
								cm.save();
							});
						});
					}',
					wp_json_encode( $settings ),
					$type,
					strtoupper( $type )
				);
			}
		}


		// Add inline js.
		if ( $output ) {
			wp_add_inline_script(
				'code-editor',
				'(function ($) { $(function () {
				' . $output . '
				});})(jQuery)'
			);
		}

	}
}


add_filter( 'woocommerce_add_to_cart_fragments', 'groovy_menu_woocommerce_add_to_cart_fragments', 50 );

/**
 * Mini cart fix
 *
 * @param array $fragments elements of cart.
 *
 * @return mixed
 */
function groovy_menu_woocommerce_add_to_cart_fragments( $fragments ) {
	global $woocommerce;
	$count = $woocommerce->cart->cart_contents_count;

	$fragments['.gm-cart-counter'] = groovy_menu_woocommerce_mini_cart_counter( $count );

	return $fragments;
}


/**
 * Mini cart counter
 *
 * @param string $count count of elements.
 *
 * @return string
 */
function groovy_menu_woocommerce_mini_cart_counter( $count = '' ) {
	if ( empty( $count ) ) {
		$count = '';
	}

	$count_text = ' <span class="gm-cart-counter">' . esc_html( $count ) . '</span> ';

	return $count_text;
}


/**
 * @param $preset_id
 * @param $font_option
 * @param $common_font_family
 *
 * @return string
 */
function groovy_menu_add_gfonts_fontface( $preset_id, $font_option, $common_font_family, $add_inline = false ) {
	$google_fonts = new GroovyMenuGFonts();

	return $google_fonts->add_gfont_face( $preset_id, $font_option, $common_font_family, $add_inline );
}


add_action( 'wp_head', 'groovy_menu_add_gfonts_from_pre_storage' );

/**
 * Add link tag with google fonts.
 */
function groovy_menu_add_gfonts_from_pre_storage() {
	$font_data = GroovyMenuPreStorage::get_instance()->get_preset_data_by_key( 'font_family' );

	if ( ! empty( $font_data ) ) {
		$font_family_exist = array();
		foreach ( $font_data as $_preset_id => $font_family_array ) {
			foreach ( $font_family_array as $index => $font_family ) {

				// Prevent duplicate.
				if ( in_array( $font_family, $font_family_exist, true ) ) {
					continue;
				}

				// Store for duplicate check.
				$font_family_exist[] = $font_family;

				echo '
<link rel="stylesheet" id="gm-google-fonts-' . esc_attr( $index ) . '" href="https://fonts.googleapis.com/css?family=' . esc_attr( $font_family ) . '" type="text/css" media="all">
';
			}
		}
	}
}


/**
 * Enable or Disable google fonts loading from local directory
 */
function groovy_menu_check_gfonts_params() {

	$google_fonts_local = false;
	$styles_class       = new GroovyMenuStyle( null );

	if ( $styles_class->getGlobal( 'tools', 'google_fonts_local' ) ) {
		$google_fonts_local = true;
	}

	$google_fonts = new GroovyMenuGFonts();

	if ( $google_fonts_local ) {

		$need_fonts = $google_fonts->get_specific_fonts();

		foreach ( $need_fonts as $_font ) {
			if ( ! empty( $_font['zip_url'] ) ) {
				$google_fonts->download_font( $_font['zip_url'] );
			}
		}
	} else {
		delete_transient( $google_fonts->get_opt_name() );
		delete_transient( $google_fonts->get_opt_name() . '__current' );
		delete_option( $google_fonts->get_opt_name() . '__downloaded' );
	}

}
