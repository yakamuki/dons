<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Crane theme functions and definitions
 *
 *
 * Include all the needed files
 *
 * (!) Note for Clients: please, do not modify this or other theme's files. Use child theme instead!
 *
 * @package crane
 */

define( 'CRANE_THEME_VERSION', '1.4.6' );
define( 'CRANE_THEME_DB_VER_OPTION', 'crane_theme_db_version' );
define( 'CRANE_THEME_CACHE_LIFETIME', DAY_IN_SECONDS );
define( 'BSF_PRODUCTS_NOTICES', false );
if ( function_exists( 'set_revslider_as_theme' ) ) {
	if ( ! defined( 'REV_SLIDER_AS_THEME' ) ) {
		define( 'REV_SLIDER_AS_THEME', true );
	}
	set_revslider_as_theme();
}

require_once get_parent_theme_file_path( 'inc/helper-functions.php' );
require_once trailingslashit( get_template_directory() ) . 'admin/class-Crane_Dashboard.php';
new Crane_Dashboard();

require_once trailingslashit( get_template_directory() ) . 'admin/class-Crane_Addons.php';
new Crane_Addons();

require_once get_parent_theme_file_path( 'inc/crane-portfolio.php' );
require_once get_parent_theme_file_path( 'inc/crane-footer.php' );
include_once get_parent_theme_file_path( 'inc/class-Crane_Sidebars_Creator.php' );
require_once get_parent_theme_file_path( 'inc/meta/class-Crane_Meta_Data.php' );
require_once get_parent_theme_file_path( 'inc/meta/config.php' );
$Crane_Meta_Data = crane_get_meta_data();
require_once get_parent_theme_file_path( 'inc/woocommerce-functions.php' );
require_once get_parent_theme_file_path( 'inc/crane-pagination.php' );

include_once get_parent_theme_file_path( 'admin/importer/class-crane-import-settings.php' );

global $revSliderAsTheme;
$revSliderAsTheme = true;

include_once get_parent_theme_file_path( 'admin/migration/migration.php' );

include_once trailingslashit( get_template_directory() ) . 'admin/class-Crane_Theme_Activation.php';

include_once get_parent_theme_file_path( 'inc/plugin_support/list.php' );

if ( ! isset( $content_width ) ) {
	$content_width = 900;
}

if ( ! function_exists( 'crane_theme_setup' ) ) {
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this fuwp-theme/crane/languages/crane.ponction is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function crane_theme_setup() {

		require get_parent_theme_file_path( '/admin/crane-admin-init.php' );

		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 */
		load_theme_textdomain( 'crane', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'crane' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		/*
		 * Enable support for Post Formats.
		 * See http://codex.wordpress.org/Post_Formats
		 */
		add_theme_support( 'post-formats', array(
			'video',
			'audio',
			'quote',
			'link',
			'gallery',
		) );

		/*
		 * Enable Gutenberg Opt-in features.
		 * See https://wordpress.org/gutenberg/handbook/extensibility/theme-support/
		 */

		add_theme_support('align-wide');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		add_filter( 'body_class', 'crane_add_additional_body_class' );

		/*
		 * Registers an editor stylesheet for the theme
		 */
		add_action( 'admin_init', 'crane_add_editor_styles' );
		/*
		 * TinyMCE Buttons
		 */
		add_action( 'init', 'crane_tinymce_buttons' );

		/**
		 * Init import settings
		 */
		$importer_setting = new Crane_Import_Settings();
		$importer_setting->init();

		/*
		 * Add default main menu
		 */
		add_action( 'crane_primary_menu_area', 'crane_show_primary_menu_area' );

	}
} // crane_theme_setup
add_action( 'after_setup_theme', 'crane_theme_setup' );

if ( ! function_exists( 'crane_mobile_width' ) ) {
	/**
	 * Set the mobile width in pixels, based on the theme's design and stylesheet.
	 *
	 * @global int $crane_mobile_width
	 */
	function crane_mobile_width() {

		global $crane_mobile_width;
		$crane_mobile_width = apply_filters( 'crane_mobile_width', 768 );
	}
}
add_action( 'after_setup_theme', 'crane_mobile_width', 0 );

if ( ! function_exists( 'crane_show_primary_menu_area' ) ) {
	/**
	 * Show primary menu area.
	 */
	function crane_show_primary_menu_area() {
		get_template_part( 'template-parts/menu' );
	}
}


/**
 * Add additional class to body
 *
 * @param $classes
 *
 * @return array
 */
function crane_add_additional_body_class( $classes ) {
	if ( empty( $classes ) ) {
		$classes = array();
	}

	global $crane_options;
	$current_page_options = crane_get_options_for_current_page();

	// with crane version
	$classes[] = 'crane_' . str_replace( '.', '-', CRANE_THEME_VERSION );

	// with current page class
	$classes[] = $current_page_options['page_class'];

	// if sidebar exist
	if ( isset( $current_page_options['type'] ) && $current_page_options['type'] ) {
		if ( isset( $current_page_options['has-sidebar'] ) && $current_page_options['has-sidebar'] ) {
			$classes[] = $current_page_options['type'] . '--has-sidebar crane-has-sidebar';
		}
	}

	// for blog pages
	if ( 'blog' !== $current_page_options['type'] ) {
		return $classes;
	}
	if ( isset( $crane_options['blog-template'] ) && $crane_options['blog-template'] ) {
		if ( is_archive() || is_home() ) {
			$classes[] = 'crane-blog-archive-layout-' . $crane_options['blog-template'];
		}
	}

	return $classes;
}


add_filter( 'admin_body_class', 'crane_add_admin_body_class' );
/**
 * Adds html classes to the body tag in the dashboard.
 *
 * @param  String $classes Current body classes.
 *
 * @return String          Altered body classes.
 */
function crane_add_admin_body_class( $classes ) {
	if ( defined( 'GROOVY_MENU_DB_VER_OPTION' ) ) {
		$gm_db_version = get_option( GROOVY_MENU_DB_VER_OPTION );
		if ( ! empty( $gm_db_version ) && version_compare( $gm_db_version, '1.4.4.403', '<' ) ) {
			$classes = $classes . ' gm-needs-to-update-first';
		}
	}

	return $classes;
}


/**
 * Register widget areas.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function crane_widgets_init() {
	foreach ( crane_get_crane_sidebars_array() as $sidebar ) {
		register_sidebar( $sidebar );
	}
}

add_action( 'widgets_init', 'crane_widgets_init' );

if ( ! function_exists( 'crane_scripts_and_styles' ) ) {
	/**
	 * Enqueue scripts and styles for front-end.
	 */
	function crane_scripts_and_styles() {
		global $crane_options;

		$min_js = $min_css = '';
		if ( isset( $crane_options['minify-js'] ) && $crane_options['minify-js'] ) {
			$min_js = '.min';
		}
		if ( isset( $crane_options['minify-css'] ) && $crane_options['minify-css'] ) {
			$min_css = '.min';
		}

		if ( $min_js ) {

			wp_register_script( 'crane-js', get_template_directory_uri() . '/assets/js/frontend/frontend-bundle.min.js', [ 'jquery' ], CRANE_THEME_VERSION, true );

		} else {

			wp_register_script( 'crane-imagesloaded', get_template_directory_uri() . '/assets/js/frontend/vendor/imagesloaded.pkgd.min.js', [ 'jquery' ], '4.1.3', true );
			wp_register_script( 'isotope-layout', get_template_directory_uri() . '/assets/js/frontend/vendor/isotope.pkgd.min.js', [ 'jquery' ], '3.0.4', true );
			wp_register_script( 'jquery.dotdotdot', get_template_directory_uri() . '/assets/js/frontend/vendor/jquery.dotdotdot.js', [ 'jquery' ], '3.0.5', true );
			wp_register_script( 'magnific-popup', get_template_directory_uri() . '/assets/js/frontend/vendor/jquery.magnific-popup.min.js', [ 'jquery' ], '1.1.0', true );
			wp_register_script( 'loaders.css', get_template_directory_uri() . '/assets/js/frontend/vendor/loaders.css.js', [ 'jquery' ], '0.1.2', true );
			wp_register_script( 'lodash', get_template_directory_uri() . '/assets/js/frontend/vendor/lodash.min.js', [ 'jquery' ], '4.17.5', true );
			wp_register_script( 'crane-select2', get_template_directory_uri() . '/assets/js/frontend/vendor/select2.min.js', [ 'jquery' ], '4.0.5', true );
			wp_register_script( 'slick-carousel', get_template_directory_uri() . '/assets/js/frontend/vendor/slick.min.js', [ 'jquery' ], '1.8.1', true );
			wp_register_script( 'js-cookie', get_template_directory_uri() . '/assets/js/frontend/vendor/js.cookie.js', [ 'jquery' ], '1.8.1', true );
			wp_register_script( 'perfect-scrollbar', get_template_directory_uri() . '/assets/js/frontend/vendor/perfect-scrollbar.min.js', [ 'jquery' ], '1.3.0', true );
			wp_register_script( 'lazysizes-unveilhooks', get_template_directory_uri() . '/assets/js/frontend/vendor/ls.unveilhooks.min.js', [ 'jquery' ], '4.0.1', true );
			wp_register_script( 'lazysizes', get_template_directory_uri() . '/assets/js/frontend/vendor/lazysizes.min.js', [ 'jquery' ], '4.0.1', true );


			wp_enqueue_script( 'crane-js' );
			wp_enqueue_script( 'crane-imagesloaded' );
			wp_enqueue_script( 'isotope-layout' );
			wp_enqueue_script( 'jquery.dotdotdot' );
			wp_enqueue_script( 'magnific-popup' );
			wp_enqueue_script( 'loaders.css' );
			wp_enqueue_script( 'lodash' );
			wp_enqueue_script( 'crane-select2' );
			wp_enqueue_script( 'slick-carousel' );
			wp_enqueue_script( 'js-cookie' );
			wp_enqueue_script( 'perfect-scrollbar' );
			wp_enqueue_script( 'lazysizes-unveilhooks' );
			wp_enqueue_script( 'lazysizes' );


			wp_register_script( 'crane-js', get_template_directory_uri() . '/assets/js/frontend/dist/main-wp.js', [ 'jquery' ], CRANE_THEME_VERSION, true );
		}


		wp_enqueue_script( 'crane-js' );


		wp_localize_script( 'crane-js', 'crane_ajax_data',
			array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'nonce_str' => wp_create_nonce( 'crane_sec_string' ),
			)
		);
		wp_localize_script( 'crane-js', 'crane_js_l10n', crane_get_js_l10n() );

		$inline_js = 'var crane_PreloaderType = "' . esc_attr( isset( $crane_options['preloader-type'] ) ? $crane_options['preloader-type'] : 'ball-pulse' ) . '";';
		$inline_js .= 'var crane_options = ' . json_encode( $crane_options, JSON_UNESCAPED_UNICODE ) . ';';
		wp_add_inline_script( 'crane-js', $inline_js, 'before' );

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		wp_enqueue_style( 'crane-style-main', get_template_directory_uri() . '/assets/css/style-main' . $min_css . '.css', [ 'crane-style' ], CRANE_THEME_VERSION );
		wp_style_add_data( 'crane-style-main', 'rtl', 'replace' );

		if ( ! crane_custom_css_style( 'get' ) ) {
			wp_enqueue_style( 'crane-style-custom', get_template_directory_uri() . '/assets/css/custom-style.css', [ 'crane-style-main' ], crane_custom_css_style( 'get_time' ) );
		}

		wp_enqueue_style( 'crane-style', get_stylesheet_uri(), [], CRANE_THEME_VERSION );

		if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
			wp_enqueue_style( 'crane-toolbar-style', get_template_directory_uri() . '/assets/css/toolbar' . $min_css . '.css', [], CRANE_THEME_VERSION );
			wp_style_add_data( 'crane-toolbar-style', 'rtl', 'replace' );
		}

	}
}
add_action( 'wp_enqueue_scripts', 'crane_scripts_and_styles', 20 );


if ( ! function_exists( 'crane_add_custom_css' ) ) {
	/**
	 * Add custom CSS
	 */
	function crane_add_custom_css() {

		$css = '';

		global $crane_options;
		global $crane_mobile_width;

		$current_page_options = crane_get_options_for_current_page();

		$additional_css = '';

		if ( $current_page_options['has-sidebar'] ) {
			$page_type = esc_attr( $current_page_options['type'] );

			$additional_css .= '@media (min-width: 992px) { ';
			$additional_css .= '.' . $page_type . '--has-sidebar .crane-content-inner {width: ' . esc_attr( $current_page_options['content_width'] ) . '%;}';
			$additional_css .= '.' . $page_type . '--has-sidebar .crane-sidebar {width:' . esc_attr( $current_page_options['sidebar_width'] ) . '%;}';
			$additional_css .= ' }';
		}
		if ( $additional_css ) {
			$css .= $additional_css;
		}

		$additional_custom_css = crane_custom_css_style( 'get' );

		if ( $current_page_options['meta_override'] ) {

			switch ( $current_page_options['type'] ) {

				case 'blog':
					if ( isset( $crane_options['blog-archive-padding'] ) && is_array( $crane_options['blog-archive-padding'] ) ) {
						$additional_custom_css .= ' .crane-blog-archive .crane-content-inner, .crane-blog-archive .crane-sidebar, .crane-search-page .crane-content-inner, .crane-search-page .crane-sidebar{padding-top:' . esc_attr( $crane_options['blog-archive-padding']['padding-top'] ) . ';padding-bottom:' . esc_attr( $crane_options['blog-archive-padding']['padding-bottom'] ) . ';} ';
					}
					if ( isset( $crane_options['blog-archive-padding-mobile'] ) && is_array( $crane_options['blog-archive-padding-mobile'] ) ) {
						$additional_custom_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-blog-archive .crane-content-inner, .crane-blog-archive .crane-sidebar, .crane-search-page .crane-content-inner, .crane-search-page .crane-sidebar{padding-top:' . esc_attr( $crane_options['blog-archive-padding-mobile']['padding-top'] ) . ';padding-bottom:' . esc_attr( $crane_options['blog-archive-padding-mobile']['padding-bottom'] ) . ';} }';
					}
					if ( isset( $crane_options['blog-cell-item-bg-color'] ) && is_string( $crane_options['blog-cell-item-bg-color'] ) ) {
						$additional_custom_css .= ' .crane-blog-archive-layout-cell .crane-blog-layout-cell .crane-blog-grid-meta{background-color:' . esc_attr( $crane_options['blog-cell-item-bg-color'] ) . ';border-color:' . esc_attr( $crane_options['blog-cell-item-bg-color'] ) . ';} ';
					}
					break;

				case 'portfolio-archive':
					if ( isset( $crane_options['portfolio-archive-padding'] ) && is_array( $crane_options['portfolio-archive-padding'] ) ) {
						$additional_custom_css .= ' .crane-portfolio-archive .crane-content-inner, .crane-portfolio-archive .crane-sidebar{padding-top:' . esc_attr( $crane_options['portfolio-archive-padding']['padding-top'] ) . ';padding-bottom:' . esc_attr( $crane_options['portfolio-archive-padding']['padding-bottom'] ) . ';} ';
					}
					if ( isset( $crane_options['portfolio-archive-padding-mobile'] ) && is_array( $crane_options['portfolio-archive-padding-mobile'] ) ) {
						$additional_custom_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-portfolio-archive .crane-content-inner, .crane-blog-archive .crane-sidebar{padding-top:' . esc_attr( $crane_options['portfolio-archive-padding-mobile']['padding-top'] ) . ';padding-bottom:' . esc_attr( $crane_options['portfolio-archive-padding-mobile']['padding-bottom'] ) . ';} }';
					}
					break;

				case 'shop':
					if ( isset( $crane_options['shop-archive-padding'] ) && is_array( $crane_options['shop-archive-padding'] ) ) {
						$additional_custom_css .= ' .crane-shop-archive .crane-content-inner, .crane-shop-archive .crane-sidebar{padding-top:' . esc_attr( $crane_options['shop-archive-padding']['padding-top'] ) . ';padding-bottom:' . esc_attr( $crane_options['shop-archive-padding']['padding-bottom'] ) . ';} ';
					}
					if ( isset( $crane_options['shop-archive-padding-mobile'] ) && is_array( $crane_options['shop-archive-padding-mobile'] ) ) {
						$additional_custom_css .= '@media (max-width: ' . $crane_mobile_width . 'px) { .crane-shop-archive .crane-content-inner, .crane-blog-archive .crane-sidebar{padding-top:' . esc_attr( $crane_options['shop-archive-padding-mobile']['padding-top'] ) . ';padding-bottom:' . esc_attr( $crane_options['shop-archive-padding-mobile']['padding-bottom'] ) . ';} }';
					}
					break;
			}

		}

		if ( ! empty( $additional_custom_css ) ) {
			$css .= $additional_custom_css;
		}

		$custom_css_from_to = crane_custom_css_4_customize();
		if ( ! empty( $custom_css_from_to ) ) {
			$css .= $custom_css_from_to;
		}

		if ( $custom_font_face = crane_add_local_font_face() ) {
			$css .= $custom_font_face;
		}

		wp_add_inline_style( 'crane-style-main', $css );

	}
}

add_filter( 'wp_enqueue_scripts', 'crane_add_custom_css', 30 );


if ( ! function_exists( 'crane_update_custom_style_when_change_options' ) ) {
	/**
	 * Update custom-style.css when change theme options
	 */
	function crane_update_custom_style_when_change_options() {

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( ! class_exists( 'Redux' ) && ! class_exists( 'ReduxFrameworkInstances' ) ) {
				return null;
			}

			$redux = ReduxFrameworkInstances::get_instance( 'crane_options' );

			$all_opt = $redux->sections;
			foreach ( $all_opt as $sec_key => $section ) {
				if ( isset( $section['id'] ) && 'info-panel' === $section['id'] ) {
					unset( $all_opt[ $sec_key ] );
				}
			}

			$current_opt_hash = md5( serialize( $all_opt ) );

			if ( $saved_opt_hash = get_option( 'crane_options_hash' ) ) {

				if ( ( $current_opt_hash !== $saved_opt_hash ) ) {
					update_option( 'crane_options_hash', $current_opt_hash );
					update_option( 'crane_need_custom_css_update', true );
				}

			} else {
				update_option( 'crane_options_hash', $current_opt_hash );
				update_option( 'crane_need_custom_css_update', true );
			}

		}

	}
}
add_action( 'wp', 'crane_update_custom_style_when_change_options' );


if ( ! function_exists( 'crane_include_admin_scripts' ) ) {
	/**
	 * Enqueue scripts and styles for admin dashboard.
	 *
	 * @param $hook_suffix
	 */
	function crane_include_admin_scripts( $hook_suffix ) {

		global $crane_options;

		$min_js = $min_css = '';
		if ( isset( $crane_options['minify-js'] ) && $crane_options['minify-js'] ) {
			$min_js = '.min';
		}
		if ( isset( $crane_options['minify-css'] ) && $crane_options['minify-css'] ) {
			$min_css = '.min';
		}

		$dashboard_pages = [
			'toplevel_page_crane-theme-dashboard',
			'crane-theme_page_crane-theme-addons',
			'crane-theme_page_crane-theme-options',
			'crane-theme_page_crane_sidebars_creator',
			'crane-theme_page_crane_activate',
			'crane-theme_page_crane_import',
            'tools_page_crane_debug_page',
		];

		$editor_pages = [
			'post.php',
			'post-new.php',
			'widgets.php',
			'upload.php',
			'term.php',
			'edit.php',
			'edit-tags.php',
		];

		if ( in_array( $hook_suffix, array_merge( $dashboard_pages, $editor_pages ) ) ) {
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );

			if ( $min_js ) {

				wp_enqueue_script( 'crane-admin-js', get_template_directory_uri() . '/assets/js/admin/admin-bundle.min.js', [ 'jquery' ], CRANE_THEME_VERSION );

			} else {

				wp_enqueue_script( 'select2', get_template_directory_uri() . '/assets/js/admin/vendor/select2.min.js', [ 'jquery' ], '4.0.5', true );

				wp_enqueue_script( 'crane-admin-js', get_template_directory_uri() . '/assets/js/admin/dist/crane-admin.js', [ 'jquery' ], CRANE_THEME_VERSION );

			}

			wp_add_inline_script( 'crane-admin-js', 'var crane_js_l10n = ' . json_encode( crane_get_js_l10n( true ) ) . ';' );

			wp_enqueue_style( 'crane-admin-style', get_template_directory_uri() . '/assets/css/admin' . $min_css . '.css', [], CRANE_THEME_VERSION );
			wp_style_add_data( 'crane-admin-style', 'rtl', 'replace' );
		}

		if ( in_array( $hook_suffix, $dashboard_pages ) ) {
			wp_enqueue_style( 'crane-dashboard-style', get_template_directory_uri() . '/assets/css/admin-dashboard' . $min_css . '.css', [], CRANE_THEME_VERSION );
			wp_style_add_data( 'crane-dashboard-style', 'rtl', 'replace' );
		}

		if ( 'crane-theme_page_crane_import' === $hook_suffix ) {
			$deactivated_plugins = [];
			foreach ( crane_get_crane_plugins_array() as $plugin_slug => $plugin_data ) {
				if ( ! crane_is_plugin_installed( $plugin_slug, $plugin_data['installed_path'] ) ) {
					$deactivated_plugins[ $plugin_slug ] = $plugin_data['name'];
				}
			}

			if ( ! empty( $deactivated_plugins ) ) {
				$plugins_for_install = 'var plugins_for_install = ' . json_encode( $deactivated_plugins ) . ';';
				$plugins_for_install .= 'var plugins_for_install_text = "' . esc_html__( 'During the import process the following plug-ins to be installed and activated:', 'crane' ) . '";';
				wp_add_inline_script( 'crane-admin-js', $plugins_for_install, 'before' );
			}
		}

		if ( $hook_suffix === 'nav-menus.php' ) {
			wp_register_style( 'crane-nav-menus', get_template_directory_uri() . '/assets/css/nav-menus' . $min_css . '.css', [], CRANE_THEME_VERSION );
			wp_enqueue_style( 'crane-nav-menus' );
			wp_style_add_data( 'crane-nav-menus', 'rtl', 'replace' );
		}

		wp_enqueue_style( 'crane-admin-style-allpages', get_template_directory_uri() . '/assets/css/crane-admin-allpages' . $min_css . '.css', [], CRANE_THEME_VERSION );
		wp_style_add_data( 'crane-admin-style-allpages', 'rtl', 'replace' );

		wp_enqueue_script( 'crane-admin-allpages', get_template_directory_uri() . '/assets/js/admin/dist/crane-admin-allpages' . $min_js . '.js', [ 'jquery' ], CRANE_THEME_VERSION );

		$admin_js_allpages_vars = 'var crane_image_width_thumbnail = ' . crane_get_image_width( 'thumbnail' ) . ';';
		wp_add_inline_script( 'crane-admin-allpages', $admin_js_allpages_vars, 'before' );

		// Stop execution if not in the tgmpa-install-plugins page
		if ( ! empty( $_GET['page'] ) && $_GET['page'] === 'tgmpa-install-plugins' && ! empty( $_GET['autoaction'] ) ) {
			$tgm_style = '
			#plugins-loading-box {
				position: fixed;
				top: 0;
				right: 0;
				bottom: 0;
				left: 0;
				width: 100%;
				height: 100%;
				z-index: 9999999999;
				padding: 50px;
				background-color: #fff;
			}';
			wp_add_inline_style( 'crane-admin-style-allpages', $tgm_style );
		}


	}
}

add_action( 'admin_enqueue_scripts', 'crane_include_admin_scripts' );


if ( ! function_exists( 'crane_add_editor_styles' ) ) {
	/**
	 * Registers an editor stylesheet for the theme
	 */
	function crane_add_editor_styles() {
		add_editor_style( 'custom-editor-style.css' );
	}
}


if ( ! function_exists( 'crane_tinymce_buttons' ) ) {
	/**
	 * TinyMCE Buttons
	 */
	function crane_tinymce_buttons() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( get_user_option( 'rich_editing' ) !== 'true' ) {
			return;
		}

		add_filter( 'mce_external_plugins', 'crane_tinymce_add_buttons' );
		add_filter( 'mce_buttons', 'crane_tinymce_register_buttons' );
	}
}

if ( ! function_exists( 'crane_tinymce_add_buttons' ) ) {
	function crane_tinymce_add_buttons( $plugin_array ) {
		global $crane_options;

		$min_js = '';
		if ( isset( $crane_options['minify-js'] ) && $crane_options['minify-js'] ) {
			$min_js = '.min';
		}

		$plugin_array['quote_with_author'] = get_template_directory_uri() . '/assets/js/admin/dist/tinymce-buttons' . $min_js . '.js';

		return $plugin_array;
	}
}

if ( ! function_exists( 'crane_tinymce_register_buttons' ) ) {
	function crane_tinymce_register_buttons( $buttons ) {
		array_push( $buttons, 'quote_with_author' );

		return $buttons;
	}
}


if ( ! function_exists( 'crane_check_new_theme_version' ) ) {
	/**
	 * Check new version of theme.
	 */
	function crane_check_new_theme_version() {
		$db_versions_report = get_option( CRANE_THEME_DB_VER_OPTION . '__report' );
		$need_update        = false;

		if ( isset( $db_versions_report['theme_version'] ) && is_string( $db_versions_report['theme_version'] ) ) {

			if ( version_compare( CRANE_THEME_VERSION, $db_versions_report['theme_version'], '!=' ) ) {
				$need_update = true;
			}

		} else {
			$need_update = true;
		}

		if ( $need_update ) {
			$db_versions_report['theme_version'] = CRANE_THEME_VERSION;
			update_option( CRANE_THEME_DB_VER_OPTION . '__report', $db_versions_report );
			update_option( 'crane_need_custom_css_update', true );
		}

	}
}
add_action( 'init', 'crane_check_new_theme_version' );


/**
 * Customizer additions.
 */
include_once get_parent_theme_file_path( 'inc/customizer.php' );

include_once get_parent_theme_file_path( 'inc/class-Crane_Walker_Comment.php' );

// Add taxonomy meta fields
include_once get_parent_theme_file_path( 'inc/taxonomy-extra-fields.php' );

if ( ! function_exists( 'crane_add_override_panel_css' ) ) {
	function crane_add_override_panel_css() {
		global $crane_options;

		$min_css = '';
		if ( isset( $crane_options['minify-css'] ) && $crane_options['minify-css'] ) {
			$min_css = '.min';
		}

		wp_register_style(
			'crane-admin-dashboard',
			get_template_directory_uri() . '/assets/css/admin-dashboard' . $min_css . '.css',
			[],
			CRANE_THEME_VERSION
		);
		wp_enqueue_style( 'crane-admin-dashboard' );
		wp_style_add_data( 'crane-admin-dashboard', 'rtl', 'replace' );
	}
}
add_action( 'redux/page/crane_options/enqueue', 'crane_add_override_panel_css' );


if ( ! function_exists( 'crane_overload_edd_license_field_path' ) ) {
	/**
	 * @return string
	 */
	function crane_overload_edd_license_field_path() {
		return get_parent_theme_file_path( 'admin/redux-custom-field.php' );
	}
}
add_filter( 'redux/crane_options/field/class/custom_field', 'crane_overload_edd_license_field_path' );


if ( ! function_exists( 'crane_get_footer_social' ) ) {
	/**
	 * @return array
	 */
	function crane_get_footer_social() {
		global $crane_options;
		$socials = array(
			'twitter',
			'facebook',
			'instagram',
			'google',
			'vimeo',
			'dribbble',
			'pinterest',
			'youtube',
			'linkedin',
			'flickr',
			'vk'
		);
		$links   = array();
		foreach ( $socials as $social ) {
			$social_name = 'footer-social-' . $social;
			if ( isset( $crane_options[ $social_name ] ) && $crane_options[ $social_name ] ) {
				$links[ $social ] = $crane_options[ $social_name ];
			}
		}

		return $links;
	}
}


if ( ! function_exists( 'crane_remove_activation_redirect_actions' ) ) {
	function crane_remove_activation_redirect_actions() {
		remove_action( 'vc_activation_hook', 'vc_page_welcome_set_redirect' );
		remove_action( 'admin_init', 'vc_page_welcome_redirect' );
	}
}
add_action( 'init', 'crane_remove_activation_redirect_actions' );


if ( ! function_exists( 'crane_entry_footer' ) ) {
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function crane_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			$categories_list = get_the_category_list( ', ' );
			if ( $categories_list && crane_categorized_blog() ) {
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'crane' ) . '</span>',
					$categories_list );
			}

			$tags_list = get_the_tag_list( '', ', ' );
			if ( $tags_list ) {
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'crane' ) . '</span>',
					$tags_list );
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link( esc_html__( 'Leave a comment', 'crane' ), esc_html__( '1 Comment', 'crane' ),
				esc_html__( '% Comments', 'crane' ) );
			echo '</span>';
		}

		edit_post_link( esc_html__( 'Edit', 'crane' ), '<span class="edit-link">', '</span>' );
	}
}


if ( ! function_exists( 'crane_add_fixed_image_sizes' ) ) {
	/**
	 * @param bool|false $return_array
	 *
	 * @return array
	 */
	function crane_add_fixed_image_sizes( $return_array = false ) {
		$new_image_sizes = [
			'crane-portfolio-300' => array( 'width' => 300, 'height' => 300, 'crop' => true ),
			'crane-portfolio-600' => array( 'width' => 600, 'height' => 600, 'crop' => true ),
			'crane-portfolio-900' => array( 'width' => 900, 'height' => 900, 'crop' => true ),
			'crane-related'       => array( 'width' => 600, 'height' => 400, 'crop' => true ),
			'crane-featured'      => array( 'width' => 1000, 'height' => 600, 'crop' => true ),
		];

		// Check and add custom image sizes from theme options
		$crane_options = get_option( 'crane_options' );
		if ( ! empty( $crane_options['custom-image-sizes'] ) ) {
			$image_sizes = json_decode( $crane_options['custom-image-sizes'], true );
			if ( ! empty( $image_sizes ) && is_array( $image_sizes ) ) {
				foreach ( $image_sizes as $data ) {

					$width  = empty( $data['width'] ) ? 0 : ( intval( $data['width'] ) ? intval( $data['width'] ) : 0 );
					$height = empty( $data['height'] ) ? 0 : ( intval( $data['height'] ) ? intval( $data['height'] ) : 0 );
					$crop   = empty( $data['crop'] ) ? 0 : 1;

					$size_name = 'crane-custom-' . $width . 'x' . $height . 'x' . $crop;

					$new_image_sizes[ $size_name ] =
						[
							'width'  => $width,
							'height' => $height,
							'crop'   => ( $crop ? true : false )
						];
				}
			}
		}

		if ( $return_array ) {
			return $new_image_sizes;
		}

		foreach ( $new_image_sizes as $key => $params ) {
			add_image_size( $key, $params['width'], $params['height'], $params['crop'] );
		}

		return;
	}
}
add_action( 'after_setup_theme', 'crane_add_fixed_image_sizes', 5 );

/**
 * filter function to force wordpress to add our custom srcset values
 *
 * @param array $sources {
 *     One or more arrays of source data to include in the 'srcset'.
 *
 * @type array $width {
 * @type string $url The URL of an image source.
 * @type string $descriptor The descriptor type used in the image candidate string,
 *                                        either 'w' or 'x'.
 * @type int $value The source width, if paired with a 'w' descriptor or a
 *                                        pixel density value if paired with an 'x' descriptor.
 *     }
 * }
 *
 * @param array $size_array Array of width and height values in pixels (in that order).
 * @param string $image_src The 'src' of the image.
 * @param array $image_meta The image meta data as returned by 'wp_get_attachment_metadata()'.
 * @param int $attachment_id Image attachment ID.
 *
 * @return array
 */
function crane_add_thumb_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {

	// image base name
	$image_basename = wp_basename( $image_meta['file'] );
	// upload directory info array
	$upload_dir_info_arr = wp_get_upload_dir();
	// base url of upload directory
	$baseurl = $upload_dir_info_arr['baseurl'];

	// Uploads are (or have been) in year/month sub-directories.
	if ( $image_basename !== $image_meta['file'] ) {
		$dirname = dirname( $image_meta['file'] );

		if ( $dirname !== '.' ) {
			$image_baseurl = trailingslashit( $baseurl ) . $dirname;
		}
	}

	$image_baseurl = trailingslashit( $image_baseurl );
	foreach ( crane_add_fixed_image_sizes( true ) as $key => $params ) {
		// check custom image size exists in image current meta
		if ( array_key_exists( $key, $image_meta['sizes'] ) ) {

			// add source value for srcset
			$sources[ $image_meta['sizes'][ $key ]['width'] ] = array(
				'url'        => $image_baseurl . $image_meta['sizes'][ $key ]['file'],
				'descriptor' => 'w',
				'value'      => $image_meta['sizes'][ $key ]['width'],
			);
		}
	}

	//return sources with new srcset
	return $sources;
}


if ( ! function_exists( 'crane_is_need_custom_css_update' ) ) {
	/**
	 * Check when update needed
	 */
	function crane_is_need_custom_css_update() {
		if ( get_option( 'crane_need_custom_css_update' ) ) {
			if ( class_exists( 'Redux' ) && class_exists( 'ReduxFrameworkInstances' ) ) {
				if ( crane_update_custom_style_css() ) {
					delete_option( 'crane_need_custom_css_update' );
				}
			}
		}
	}
}

add_action( 'wp_head', 'crane_is_need_custom_css_update', 1 );


include_once get_parent_theme_file_path( 'inc/template-tags.php' );


if ( ! function_exists( 'crane_layerslider_overrides' ) ) {
	/**
	 * LayerSlider fix
	 */
	function crane_layerslider_overrides() {
		// Disable auto-updates
		$GLOBALS['lsAutoUpdateBox'] = false;
	}
}


add_action( 'layerslider_ready', 'crane_layerslider_overrides' );


if ( ! function_exists( 'crane_check_post' ) ) {
	/**
	 * Empty global $post fix (example: 404 page OR empty search page).
	 */
	function crane_check_post() {
		global $post;

		if ( $post === null ) {
			$post = new WP_Post( (object) [ 'ID' => 0 ] );
		}

	}
}
add_action( 'wp', 'crane_check_post' );


if ( ! function_exists( 'crane_check_404_page' ) ) {
	/**
	 * Query custom page as 404 page.
	 */
	function crane_check_404_page() {

		static $crane_404_override = null;

		if ( $crane_404_override ) {
			return $crane_404_override;
		}

		if ( ! is_404() ) {
			return;
		}

		global $crane_options;
		// check if 404 is custom page
		if ( isset( $crane_options['404-type'] ) && 'page' === $crane_options['404-type'] && ! empty( $crane_options['404-page'] ) ) {

			$page404            = intval( $crane_options['404-page'] );
			$crane_404_override = $page404;

			// change query to custom 404 page
			query_posts( 'page_id=' . $page404 );
			if ( have_posts() ) : while ( have_posts() ) : the_post();
				// ... empty
			endwhile; endif; // End of the loop.

		}

		return false;

	}
}
add_action( 'wp', 'crane_check_404_page' );


if ( ! function_exists( 'crane_maintenance_mode' ) ) {

	/**
	 * Display specific page when Maintenance Mode is enabled in Theme Options
	 *
	 * @return bool
	 */
	function crane_maintenance_mode() {

		global $crane_options;

		if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {

			if ( isset( $crane_options['maintenance-mode'] ) && $crane_options['maintenance-mode'] ) {

				if ( empty( $crane_options['maintenance-page'] ) ) {
					return false;
				}

				$maintenance_page = get_post( $crane_options['maintenance-page'] );

				if ( $maintenance_page && function_exists('grooni_maintenance_admin_bar_notice') ) {

					grooni_maintenance_admin_bar();

				}
			}

			return false;
		}

		if ( isset( $crane_options['maintenance-mode'] ) && $crane_options['maintenance-mode'] ) {
			$maintenance_page = get_post( $crane_options['maintenance-page'] );
			if ( $maintenance_page ) {
				add_action( 'wp', 'crane_show_maintenance_page', 9 );
			}
		}


	}

}

add_action( 'init', 'crane_maintenance_mode' );


/**
 * Change WordPress default gallery output
 *
 * @param $output
 * @param $attr
 *
 * @return string
 */
function crane_change_post_gallery_output( $output, $attr, $instance ) {

	// Don't touch gallery widget
	if ( isset( $attr['link_type'] ) ) {
		return $output;
	}

	$crane_override_options = crane_override_options();
	$layout_options         = crane_get_options_for_current_blog();
	$current_page_options   = crane_get_options_for_current_page();

	if ( isset( $current_page_options['type'] ) && 'blog' !== $current_page_options['type'] && empty( $crane_override_options ) ) {
		return $output;
	}

	if ( ! empty( $layout_options['layout'] ) && in_array( $layout_options['layout'], array( 'standard' ) ) ) {
		return $output;
	}

	if ( is_single() ) {
		return $output;
	}

	if ( isset( $layout_options['img_proportion'] ) && $layout_options['img_proportion'] && 'original' === $layout_options['img_proportion'] ) {
		return $output;
	}

	global $post;

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( ! $attr['orderby'] ) {
			unset( $attr['orderby'] );
		}
	}

	$order = '';
	extract( shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'itemtag'    => 'dl',
		'icontag'    => 'dt',
		'captiontag' => 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => ''
	), $attr ) );

	if ( 'RAND' === $order ) {
		$orderby = 'none';
	}

	if ( ! empty( $include ) ) {
		$include      = preg_replace( '/[^0-9,]+/', '', $include );
		$_attachments = get_posts( array(
			'include'        => $include,
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => $order,
			'orderby'        => $orderby
		) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[ $val->ID ] = $_attachments[ $key ];
		}
	}

	if ( empty( $attachments ) ) {
		return '';
	}

	$output = '<div class="crane-gallery-wrapper">';

	foreach ( $attachments as $attachment_id => $attachment ) {
		// Fetch the thumbnail
		$img     = wp_get_attachment_image_src( $attachment_id, 'full' );
		$tagattr = 'style';
		$styles  = 'background-image: url(' . esc_url( $img[0] ) . ');';
		$tagattr .= '="' . $styles . '"';

		$output .= '<div class="crane-gallery-image" ' . $tagattr . '></div>';
	}

	$output .= '</div>';

	return $output;
}

add_filter( 'post_gallery', 'crane_change_post_gallery_output', 10, 3 );


/**
 * @param $html
 * @param $id
 * @param $attachment
 *
 * @return mixed
 */
function crane_add_class_for_media_when_inserting_to_editor( $html, $id, $attachment ) {
	$url = empty( $attachment['url'] ) ? '' : $attachment['url'];

	if ( $url === wp_get_attachment_image_url( $id, 'full' ) ) {
		$pos  = strpos( $html, '<a ' );
		$html = $pos !== false ? substr_replace( $html, '<a class="crane-lightbox" ', $pos, strlen( '<a ' ) ) : $html;
	}

	return $html;

}

add_filter( 'media_send_to_editor', 'crane_add_class_for_media_when_inserting_to_editor', 11, 3 );


/**
 * Add style for embed iframe
 */
function crane_embed_top_style() {

	if ( class_exists( 'ReduxFrameworkInstances' ) ) {
		$redux = ReduxFrameworkInstances::get_instance( 'crane_options' );
		$redux->_enqueue_output();
	}

	global $crane_options;

	if ( empty( $crane_options ) && ! is_array( $crane_options ) ) {
		return;
	}


	$style = '
		body {
			font-family: "' . esc_attr( $crane_options['h3-typography']['font-family'] ) . '";
		}

		.wp-embed {
			border-color: ' . esc_attr( $crane_options['border-color'] ) . ';
		}

		.wp-embed a {
			text-decoration: none !important;
			transition: color 0.3s, background 0.3s;
		}

		p.wp-embed-heading {
			font-family: "' . esc_attr( $crane_options['h3-typography']['font-family'] ) . '";
			font-weight: ' . esc_attr( $crane_options['h3-typography']['font-weight'] ) . ';
			text-transform: ' . esc_attr( $crane_options['h3-typography']['text-transform'] ) . ';
			font-size: ' . esc_attr( $crane_options['h3-typography']['font-size'] ) . ';
		}

		.wp-embed-heading a {
			color: ' . esc_attr( $crane_options['heading-color'] ) . ';
		}

		.wp-embed-excerpt p {
			font-family: "' . esc_attr( $crane_options['regular-txt-typography']['font-family'] ) . '";
			font-weight: ' . esc_attr( $crane_options['regular-txt-typography']['font-weight'] ) . ';
			text-transform: ' . esc_attr( $crane_options['regular-txt-typography']['text-transform'] ) . ';
			font-size: ' . esc_attr( $crane_options['regular-txt-typography']['font-size'] ) . ';
			line-height: 25px;
			color: ' . esc_attr( $crane_options['regular-txt-color'] ) . ';
		}

		.wp-embed .wp-embed-more {
			display: block;
			margin-top: 20px;
			color: inherit;
		}

		.wp-embed .wp-embed-more:hover {
			color: ' . esc_attr( $crane_options['opt-link-color']['hover'] ) . ';
		}

		.wp-embed-share-dialog-open:hover .dashicons-share {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M14.5%2012q1.24%200%202.12.88T17.5%2015t-.88%202.12-2.12.88-2.12-.88T11.5%2015q0-.34.09-.69l-4.38-2.3Q6.32%2013%205%2013q-1.24%200-2.12-.88T2%2010t.88-2.12T5%207q1.3%200%202.21.99l4.38-2.3q-.09-.35-.09-.69%200-1.24.88-2.12T14.5%202t2.12.88T17.5%205t-.88%202.12T14.5%208q-1.3%200-2.21-.99l-4.38%202.3Q8%209.66%208%2010t-.09.69l4.38%202.3q.89-.99%202.21-.99z%27%20fill%3D%27%2382878c%27%2F%3E%3C%2Fsvg%3E");
		}

		.wp-embed-share-dialog-open:focus .dashicons {
			box-shadow: none;
		}

		.wp-embed-comments a:hover .dashicons-admin-comments {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M5%202h9q.82%200%201.41.59T16%204v7q0%20.82-.59%201.41T14%2013h-2l-5%205v-5H5q-.82%200-1.41-.59T3%2011V4q0-.82.59-1.41T5%202z%27%20fill%3D%27%2382878c%27%2F%3E%3C%2Fsvg%3E");
		}

		.wp-embed-meta a:hover {
			color: #82878c;
		}';

	wp_add_inline_style( 'crane-toolbar-style', $style );

}

add_action( 'enqueue_embed_scripts', 'crane_embed_top_style', 20 );


function crane_embed_font_link() {
	global $crane_options;
	if ( isset( $crane_options['privacy-google_fonts'] ) && $crane_options['privacy-google_fonts'] ) {
		?>
		<link rel='dns-prefetch' href='//fonts.googleapis.com'/>
		<?php
	}
}

add_action( 'embed_head', 'crane_embed_font_link', 5 );


function crane_set_class_for_video_shortcode( $html, $url, $attr, $post_ID ) {
	if ( crane_is_video_pattern( $url ) ) {
		$html = '<div class="crane-video">' . $html . '</div>';
	}

	return $html;
}
//add_filter( 'embed_oembed_html', 'crane_set_class_for_video_shortcode', 100, 4 );

function crane_get_privacy_of_embeds_text() {
	$html = '<div class="crane-privacy-blocked-content">';
	$html .= '<span class="crane-privacy-blocked-content__txt">' . sprintf(
	    esc_html__( 'This content is blocked. Please review your %s', 'crane' )
      , crane_show_privacy_block( true ) ) . '</span>';
	$html .= '</div>';

	return $html;
}

function crane_set_password_form( $output ) {

	$post   = get_post( 0 );
	$label  = 'pwbox-' . ( empty( $post->ID ) ? rand() : $post->ID );
	$output = '
	<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" class="post-password-form" method="post"><p>' . __( 'This content is password protected. To view it please enter your password below:', 'crane' ) . '</p><div class="form-group"><input name="post_password" id="' . $label . '" type="password" size="20" /></div><p><input type="submit" name="Submit" value="' . esc_attr_x( 'Enter', 'post password form', 'crane' ) . '" /></p></form>
	';

	return $output;
}

add_filter( 'the_password_form', 'crane_set_password_form', 50 );


/**
 * Change "Crane theme" submenu-order
 */
function crane_admin_menu_reorder() {
	global $submenu;

	if ( ! isset( $submenu['crane-theme-dashboard'] ) ) {
		return false;
	}

	$new_order     = array();
	$order         = 0;
	$min_new_order = count( $submenu['crane-theme-dashboard'] );

	foreach ( $submenu['crane-theme-dashboard'] as $main_key => $main_data ) {
		foreach ( $main_data as $sub_key => $sub_data ) {
			if ( 'edit.php?post_type=crane_footer' === $sub_data ) {
				$order = $min_new_order + 1;
			}
		}

		$new_order[ $order ? : $main_key ] = $main_data;
		$order                             = 0;
	}

	ksort( $new_order );

	$submenu['crane-theme-dashboard'] = $new_order;

	return false;

}

add_filter( 'custom_menu_order', 'crane_admin_menu_reorder' );


function crane_content_p_and_br_fix( $content ) {
	$replace_pairs = array( '<p>[' => '[', ']</p>' => ']', ']<br />' => ']', ']<br>' => ']', );
	$content       = strtr( $content, $replace_pairs );

	return $content;
}

add_filter( 'the_content', 'crane_content_p_and_br_fix' );

/**
 * Replace "[...]" with an " ...".
 *
 * "[...]" is appended to automatically generated excerpts.
 *
 * @param string $more The Read More text.
 *
 * @return string
 */
function crane_auto_excerpt_more( $more ) {
	if ( ! is_admin() ) {
		return ' ...';
	}

	return $more;
}

add_filter( 'excerpt_more', 'crane_auto_excerpt_more', 30 );


add_filter( 'safe_style_css', function ( $styles ) {
	$styles[] = 'position';

	return $styles;
} );


function crane_size_column_register( $columns ) {

	$columns['dimensions'] = esc_html__( 'Dimensions', 'crane' );

	return $columns;
}

add_filter( 'manage_upload_columns', 'crane_size_column_register' );


function crane_size_column_display( $column_name, $post_id ) {

	if ( 'dimensions' != $column_name || ! wp_attachment_is_image( $post_id ) ) {
		return;
	}

	list( $url, $width, $height ) = wp_get_attachment_image_src( $post_id, 'full' );

	echo esc_html( "{$width}&times;{$height}" );
}

add_action( 'manage_media_custom_column', 'crane_size_column_display', 10, 2 );


/**
 * Add classes for widgets.
 *
 * @param  array $params
 *
 * @return array
 */
function crane_add_widget_classes( $params ) {

	if ( isset( $params[0]['widget_name'] ) && $params[0]['widget_name'] === 'Archives' ) {
		$params[0] = array_replace( $params[0], array( 'before_widget' => str_replace( 'class="widget"', 'class="widget crane-archive-widget"', $params[0]['before_widget'] ) ) );
	}

	return $params;

}

add_filter( 'dynamic_sidebar_params', 'crane_add_widget_classes' );


if ( ! function_exists( 'crane_add_custom_html' ) ) {
	/**
	 * Output the custom HTML code from crane options
	 */
	function crane_add_custom_html() {
		global $crane_options;
		if ( isset( $crane_options['custom-html'] ) && ! empty( $crane_options['custom-html'] ) ) {
			echo wp_kses( $crane_options['custom-html'], crane_alowed_tags( true ) ) . "\n";
		}
	}
}

add_action( 'wp_footer', 'crane_add_custom_html' );


if ( ! function_exists( 'crane_add_custom_html_head' ) ) {
	/**
	 * Output the custom HTML (Head) code from crane options
	 */
	function crane_add_custom_html_head() {
		global $crane_options;
		if ( isset( $crane_options['custom-html_head'] ) && ! empty( $crane_options['custom-html_head'] ) ) {
			echo wp_kses( $crane_options['custom-html_head'], crane_alowed_tags_head() ) . "\n";
		}
	}
}

add_action( 'wp_head', 'crane_add_custom_html_head', 6 );


function crane_add_editor_styles() {
	add_editor_style( get_template_directory_uri() . '/assets/css/editor-styles.css' );
}

add_action( 'current_screen', 'crane_add_editor_styles' );


function crane_clear_ct_vc_block_after_menu() {
	if ( function_exists( 'crane_override_options' ) ) {
		crane_override_options( array( 'ct_vc_blog' => array() ) );
	}
}

add_action( 'crane_after_primary_menu_area', 'crane_clear_ct_vc_block_after_menu' );
