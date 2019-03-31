<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );


/**
 * Class Crane_Options_Helper
 */
class Crane_Options_Helper {

	/**
	 * Get a value of the Lazy Load setting
	 *
	 * @return bool
	 */
	static public function is_lazyload_enabled() {
		global $crane_options;

		return isset( $crane_options['lazyload'] ) && $crane_options['lazyload'];

	}


}
