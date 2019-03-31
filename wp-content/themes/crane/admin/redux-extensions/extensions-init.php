<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( class_exists( 'Redux' ) ) {

	// All extensions placed within the extensions directory will be auto-loaded for your Redux instance.
	Redux::setExtensions( 'crane_options', dirname( __FILE__ ) . '/extensions/' );

}
