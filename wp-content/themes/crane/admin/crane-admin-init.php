<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Add Redux and TGM support.
 *
 * @package crane
 */

if ( ! class_exists( 'Redux_Customizer_Control_rAds' ) ) {
	// Prevent redux ads load
	class Redux_Customizer_Control_rAds {
		// ...
	}
}

if ( ! class_exists( 'reduxDashboardWidget' ) ) {
	// Prevent redux ads load
	class reduxDashboardWidget {
		public function __construct( $parent ) {
			// ...
		}
	}
}

if ( ! class_exists( 'reduxNewsflash' ) ) {
	// Prevent redux ads load
	class reduxNewsflash {
		public function __construct( $parent, $params ) {
			// ...
		}
	}
}

// Load the TGM init if it exists
if ( file_exists( get_parent_theme_file_path( 'admin/tgm/tgm-init.php' ) ) ) {
	require_once get_parent_theme_file_path( 'admin/tgm/tgm-init.php' );
}

// Load the theme/plugin options
if ( file_exists( get_parent_theme_file_path( 'admin/class-Crane_Options_Helper.php' ) ) ) {
	require_once get_parent_theme_file_path( 'admin/class-Crane_Options_Helper.php' );
}

// Load the theme/plugin options
if ( file_exists( get_parent_theme_file_path( 'admin/crane-options.php' ) ) ) {
	require_once get_parent_theme_file_path( 'admin/crane-options.php' );
}

// Load Redux extensions
if ( file_exists( get_parent_theme_file_path( 'admin/redux-extensions/extensions-init.php' ) ) ) {
	require_once get_parent_theme_file_path( 'admin/redux-extensions/extensions-init.php' );
}
