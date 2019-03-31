<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! class_exists( 'ReduxFramework_extension_crane_custom_css' ) ) {

	/**
	 * Custom WP CSS extension class for ReduxFramework
	 */
	class ReduxFramework_extension_crane_custom_css {

		// Protected vars
		protected $parent;
		public $extension_url;
		public $extension_dir;
		public static $theInstance;
		public static $version = "1.1";


		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @param array $parent
		 */
		public function __construct( $parent ) {

			$this->parent = $parent;
			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
			}
			$this->field_name = 'crane_custom_css';

			self::$theInstance = $this;

			include_once dirname( __FILE__ ) . '/validate_' . $this->field_name . '.php';

			add_filter( 'redux/' . $this->parent->args['opt_name'] . '/field/class/' . $this->field_name, array(
				$this,
				'overload_field_path'
			) ); // Adds the local field

		}

		/**
		 * @access public
		 * @return ReduxFramework_extension_crane_custom_css
		 */
		public function getInstance() {
			return self::$theInstance;
		}

		/**
		 * Forces the use of the embeded field path vs what the core typically would use
		 *
		 * @param $field
		 *
		 * @access public
		 * @return string
		 */
		public function overload_field_path( $field ) {

			return dirname( __FILE__ ) . '/field_' . $this->field_name . '.php';

		}

	}

}
