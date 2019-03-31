<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * @link              http://grooni.com
 * @since             1.0.0
 * @package           Grooni_Theme_Addons
 *
 * @wordpress-plugin
 * Plugin Name:       Grooni Theme Addons
 * Plugin URI:        http://grooni.com/
 * Description:       Grooni theme addons (extensions). The plugin contains custom post type, shortcodes and custom shortcodes for Visual Composer.
 * Version:           1.4.6
 * Author:            Grooni
 * Author URI:        http://grooni.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       grooni-theme-addons
 * Domain Path:       /languages
 */

$theme = wp_get_theme();
if ( ! empty( $theme['Template'] ) ) {
	$theme = wp_get_theme( $theme['Template'] );
}

define( 'GROONI_THEME_ADDONS_VERSION', '1.4.6' );
define( 'GROONI_THEME_ADDONS_SITE_URI', site_url() );
define( 'GROONI_THEME_ADDONS_INC_DIR', dirname( __FILE__ ) );
define( 'GROONI_THEME_ADDONS_URL', plugin_dir_url( __FILE__ ) );
define( 'GROONI_THEME_ADDONS_CURRENT_THEME_DIR', get_template_directory() );
define( 'GROONI_THEME_ADDONS_CURRENT_THEME_URI', get_template_directory_uri() );
define( 'GROONI_THEME_ADDONS_CURRENT_THEME_NAME', $theme['Name'] );
define( 'GROONI_THEME_ADDONS_CURRENT_THEME_SLUG', $theme['Template'] );
define( 'GROONI_THEME_ADDONS_CURRENT_THEME_VERSION', $theme['Version'] );

if ( ! defined( 'AUTH_COOKIE' ) && function_exists( 'is_multisite' ) && is_multisite() ) {
	if ( function_exists( 'wp_cookie_constants' ) ) {
		wp_cookie_constants();
	}
}

/**
 * The helper functions.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/helper-functions.php';


/**
 * The code that runs during plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-grooni-theme-addons-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-grooni-theme-addons-deactivator.php';

/** This action is documented in includes/class-grooni-theme-addons-activator.php */
register_activation_hook( __FILE__, array( 'Grooni_Theme_Addons_Activator', 'activate' ) );

/** This action is documented in includes/class-grooni-theme-addons-deactivator.php */
register_activation_hook( __FILE__, array( 'Grooni_Theme_Addons_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-grooni-theme-addons.php';

/**
 * Check plugin updates
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/vendor/update_checker/plugin-update-checker.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/updater.php';



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function grooni_theme_addons_run() {

	$plugin = new Grooni_Theme_Addons();
	$plugin->run();

}

grooni_theme_addons_run();
