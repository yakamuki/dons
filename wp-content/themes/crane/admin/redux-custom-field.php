<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Custom field for Redux. Overload edd license field path.
 *
 * @package crane
 */


if ( ! class_exists( 'ReduxFramework_custom_field' ) ) {

	/**
	 * Class ReduxFramework_custom_field
	 */
	class ReduxFramework_custom_field {

		/**
		 * Field Constructor.
		 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
		 *
		 * @since ReduxFramework 1.0.0
		 */
		function __construct( $field = array(), $value = '', $parent ) {
			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;
		}


		function render() {
			echo 'custom field';
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since ReduxFramework 1.0.0
		 */
		function enqueue() {
			// dummy
		}

		public function output() {
			// dummy
		}
	}
}
