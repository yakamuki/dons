<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! class_exists( 'ReduxFramework_crane_custom_css' ) ) {

	/**
	 * Main ReduxFramework_crane_custom_css class
	 */
	class ReduxFramework_crane_custom_css {

		function __construct( $field = array(), $value = '', $parent ) {

			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;

			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}

			// Set default args for this field to avoid bad indexes. Change this to anything you use.
			$defaults    = array(
				'options'          => array(),
				'stylesheet'       => '',
				'output'           => true,
				'enqueue'          => false,
				'enqueue_frontend' => false
			);
			$this->field = wp_parse_args( $this->field, $defaults );

		}


		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @param array $arr (See above)
		 *
		 * @return Object A new editor object.
		 **/
		public function render() {

			$this->field['placeholder'] = isset( $this->field['placeholder'] ) ? $this->field['placeholder'] : "";
			$this->field['rows']        = isset( $this->field['rows'] ) ? $this->field['rows'] : 10;

			// The $this->field variables are already escaped in the ReduxFramework Class.

			$custom_css = '';
			if ( function_exists( 'wp_get_custom_css' ) ) {
				$custom_css = wp_get_custom_css();
			}

			?>
			<textarea <?php echo ( isset( $this->field['readonly'] ) && $this->field['readonly'] ) ? ' readonly="readonly"' : ''; ?>
				name="<?php echo esc_attr( $this->field['name'] . $this->field['name_suffix'] ); ?>"
				id="<?php echo esc_attr( $this->field['id'] ); ?>-textarea"
				placeholder="<?php echo esc_attr( $this->field['placeholder'] ); ?>"
				class="large-text <?php echo esc_attr( $this->field['class'] ); ?>"
				rows="<?php echo esc_attr( $this->field['rows'] ); ?>"><?php echo esc_textarea( $custom_css ); ?></textarea>
			<?php
		}

		public function sanitize( $field, $val ) {

			if ( ! isset( $val ) || empty( $val ) ) {
				$val = '';
			} else {
				$val = esc_textarea( $val );
			}

			return $val;
		}

		/**
		 * Output Function.
		 * Used to enqueue to the front-end
		 * @access      public
		 * @return      void
		 */
		public function output() {

			if ( $this->field['enqueue_frontend'] ) {

			}

		}

	}

}
