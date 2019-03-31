<?php

/*
 * @package     Redux_Framework
 * @subpackage  Fields
 * @access      public
 * @global      $optname
 * @internal    Internal Note string
 * @link        http://reduxframework.com
 * @method      Test
 * @name        $globalvariablename
 * @param       string  $this->field['test']    This is cool.
 * @param       string|boolean  $field[default] Default value for this field.
 * @return      Test
 * @see         ParentClass
 * @since       Redux 3.0.9
 * @todo        Still need to fix this!
 * @var         string cool
 * @var         int notcool
 * @param       string[] $options {
 * @type        boolean $required Whether this element is required
 * @type        string  $label    The display name for this element
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ReduxFramework_textarea' ) ) {
	class ReduxFramework_textarea {

		/**
		 * Field Constructor.
		 *
		 * @param       $value  Constructed by Redux class. Based on the passing in $field['defaults'] value and what is stored in the database.
		 * @param       $parent ReduxFramework object is passed for easier pointing.
		 *
		 * @since ReduxFramework 1.0.0
		 * @type string $field [test] Description. Default <value>. Accepts <value>, <value>.
		 */
		function __construct( $field = array(), $value = '', $parent ) {
			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;
		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since ReduxFramework 1.0.0
		 *
		 * @param array $arr (See above)
		 *
		 * @return Object A new editor object.
		 **/
		function render() {

			$this->field['placeholder'] = isset( $this->field['placeholder'] ) ? $this->field['placeholder'] : "";
			$this->field['rows']        = isset( $this->field['rows'] ) ? $this->field['rows'] : 6;
			$readonly                   = ( isset( $this->field['readonly'] ) && $this->field['readonly'] ) ? ' readonly="readonly"' : '';
			$field_name                 = $this->field['name'] . $this->field['name_suffix'];
			$field_id                   = $this->field['id'] . '-textarea';

			// The $this->field variables are already escaped in the ReduxFramework Class.
			?>
            <textarea <?php echo $readonly; ?>
                    name="<?php echo esc_attr( $field_name ); ?>"
                    id="<?php echo esc_attr( $field_id ); ?>"
                    placeholder="<?php echo esc_attr( $this->field['placeholder'] ); ?>"
                    class="large-text <?php echo esc_attr( $this->field['class'] ); ?>"
                    rows="<?php echo esc_attr( $this->field['rows'] ); ?>"><?php echo esc_textarea( $this->value ); ?></textarea>
			<?php

			// DiS
			if (
				isset( $this->field['sub_type'] ) &&
				'codemirror' === $this->field['sub_type'] &&
				is_admin() &&
				is_customize_preview()
			) {

				$type     = empty( $this->field['content_type'] ) ? 'html' : $this->field['content_type'];
				$field_id = $this->field['id'] . '-textarea';

				$output            = '';
				$codemirror_params = array( 'autoRefresh' => true, 'closeBrackets' => true );

				$settings = false;
				// function wp_enqueue_code_editor() since WP 4.9
				if ( function_exists( 'wp_enqueue_code_editor' ) ) {
					$settings = wp_enqueue_code_editor( array(
						'type'       => 'text/' . $type,
						'codemirror' => $codemirror_params
					) );
				}

				if ( false !== $settings ) {

					$output .= sprintf( '
					var crane_%3$s = $("#%2$s");
					if (crane_%3$s.length > 0) {
						$.each(crane_%3$s, function(key, element) {
							var codeEditorObj = wp.codeEditor.initialize( element, %1$s );
							codeEditorObj.codemirror.on("change", function( cm ) {
								cm.save();
							});
							codeEditorObj.codemirror.on("blur", function( cm ) {
								$("#%2$s").change();
							});
						});
					}',
						wp_json_encode( $settings ),
						$field_id,
						md5( $field_id )
					);

				}

				if ( $output ) {
					$output =
						'<script>' .
						'(function ($) { $(document).ready(function () {' .
						$output .
						'});})(jQuery)' .
						'</script>';
					echo $output;
				}
			}
		}

		function sanitize( $field, $value ) {
			if ( ! isset( $value ) || empty( $value ) ) {
				$value = "";
			} else {
				$value = esc_textarea( $value );
			}

			return $value;
		}
	}
}
