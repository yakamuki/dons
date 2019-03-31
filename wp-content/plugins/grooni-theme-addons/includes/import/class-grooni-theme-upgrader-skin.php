<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );


if ( ! class_exists( 'Grooni_Theme_Upgrader_Skin' ) ) {

	if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	}


	/**
	 * Class Grooni_Theme_Upgrader_Skin clean output installer skin
	 */
	class Grooni_Theme_Upgrader_Skin extends Plugin_Installer_Skin {
		// ... empty skin (clean) ...
		public function header() {
		}

		public function bulk_header() {
		}

		public function before( $title = '' ) {
		}

		public function feedback( $string ) {
		}

		public function add_strings() {
		}

		public function after( $title = '' ) {
		}

		public function footer() {
		}

		public function bulk_footer() {
		}
	}
}
