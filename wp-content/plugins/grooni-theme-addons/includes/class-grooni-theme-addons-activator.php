<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Fired during plugin activation
 *
 * @link       http://grooni.com
 * @since      1.0.0
 *
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/includes
 * @author     Di_Skyer <diskyer@gmail.com>
 */
class Grooni_Theme_Addons_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		add_action( 'init', 'flush_rewrite_rules', 100 );
		update_option('grooni_do_flush_rewrite_rules', true);

		$theme_name = wp_get_theme()->template;

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) && current_user_can( 'edit_theme_options' ) && 'crane' == $theme_name && is_admin() ) {
			set_transient( 'grooni_theme_addons_activation_action', true );
		}

	}


}
