<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! class_exists( 'ReduxFramework_crane_add_image_sizes' ) ) {

	/**
	 * Main ReduxFramework_crane_groovy_menu class
	 */
	class ReduxFramework_crane_add_image_sizes extends ReduxFramework {

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
		 * @access      public
		 * @return      void
		 */
		public function render() {
			$this->field = wp_parse_args(
				$this->field,
				array(
					'full_width' => true,
					'overflow'   => 'inherit',
				)
			);

			$field_id    = $this->parent->args['opt_name'] . '-' . $this->field['id'];
			$image_sizes = json_decode( $this->value, true );

			$html_escaped = '';

			if ( ! empty( $image_sizes ) && is_array( $image_sizes ) ) {
				foreach ( $image_sizes as $data ) {
					$html_escaped .= '<div class="crane-add-image-size-group-element" data-id="' . esc_attr( $data['id'] ) . '">';

					$html_escaped .= '	<label>' . esc_html__( 'Width', 'crane' ) . ' <input data-name="width" type="number" value="' . esc_attr( $data['width'] ) . '"/></label>';
					$html_escaped .= '	<label>' . esc_html__( 'Height', 'crane' ) . ' <input data-name="height" type="number" value="' . esc_attr( $data['height'] ) . '"/></label>';
					$html_escaped .= '	<label>' . esc_html__( 'Crop image', 'crane' ) . ' <input data-name="crop" type="checkbox"' . ( $data['crop'] ? ' checked' : '' ) . '/></label>';
			    $html_escaped .= '<span class="crane-del-image-size button button-primary">Х</span>';

					$html_escaped .= '</div>';
				}
			}

			$main_input_escaped = '<input class="crane-image-sizes-value" type="hidden" data-id="' . esc_attr( $this->field['id'] ) . '" name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . '" value="' . esc_attr( htmlspecialchars( $this->value ) ) . '">';

			?>

			<div class="crane-add-image-sizes-wrapper">

				<div class="crane-add-image-sizes-group">
					<?php echo crane_clear_echo( $html_escaped ); ?>
				</div>

				<?php echo crane_clear_echo( $main_input_escaped ); ?>
				<div class="crane-add-more-size-btn button button-primary"><?php esc_html_e( 'Add image size', 'crane' );	?></div>

			</div>
			<div class="clear"></div>

			<?php
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

		/**
		 * Return unique string (randomize)
		 *
		 * @param bool|false $more_entropy
		 *
		 * @return string
		 */
		public function uniqid_base36( $more_entropy = false ) {
			$s = uniqid( '', $more_entropy );
			if ( ! $more_entropy ) {
				return base_convert( $s, 16, 36 );
			}
			$hex = substr( $s, 0, 13 );
			$dec = $s[13] . substr( $s, 15 ); // skip the dot
			return base_convert( $hex, 16, 36 ) . base_convert( $dec, 10, 36 );
		}

	}
}

