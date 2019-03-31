<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );


if ( ! class_exists( 'Redux_Validation_validate_crane_custom_css' ) ) {
	class Redux_Validation_validate_crane_custom_css {

		/**
		 * Field Constructor.
		 */
		function __construct( $parent, $field, $value, $current ) {

			$this->parent       = $parent;
			$this->field        = $field;
			$this->field['msg'] = '';
			$this->value        = $value;
			$this->current      = $current;

			$this->validate();
		} //function

		/**
		 * Field Render Function.
		 */
		function validate() {

			$value = $this->value;

			if ( false === $value ) {
				$value = '';
			}

			// Update custom CSS (WP way)
			if ( is_string( $value ) && function_exists( 'wp_update_custom_css_post' ) ) {
				wp_update_custom_css_post( $value );
			}

		}

	}

}
