<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Fired during plugin deactivation
 *
 * @link       http://grooni.com
 * @since      1.0.0
 *
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Grooni_Theme_Addons
 * @subpackage Grooni_Theme_Addons/includes
 * @author     Di_Skyer <diskyer@gmail.com>
 */
class Grooni_Theme_Addons_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		add_action( 'init', 'flush_rewrite_rules', 100 );

	}


}
