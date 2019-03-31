<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

if ( ! class_exists( 'ReduxFramework_crane_import_log' ) ) {

	/**
	 * Main ReduxFramework_crane_import_log class
	 */
	class ReduxFramework_crane_import_log extends ReduxFramework {

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


			$is_imported_before = false;
			$imported_flags     = get_option( 'crane_imported_flags' );
			if ( empty( $imported_flags ) || ! is_array( $imported_flags ) ) {
				$imported_flags = array();
			}
			if ( isset( $imported_flags['theme_version'] ) ) {
				$is_imported_before = true;
			}

			?>
            <div class="crane-sub_section-wrap">
                <div class="crane-sub_section-right">

                    <h4><?php esc_html_e( 'Import demo data log', 'crane' ); ?></h4>
                    <p class="crane-sub_section-field-description">
	                    <?php esc_html_e( 'For debug purpose. Show log list from last demo-data import process.', 'crane' ); ?>
                    </p>

					<?php
					if ( $is_imported_before ) { ?>

                        <div class="crane-import-log" id="crane-import-log">
                            <a href="#" class="crane-import-log--load button-secondary">
								<?php esc_html_e( 'Load last import log', 'crane' ); ?>
                            </a>
                        </div>

						<?php
					} else {
						echo esc_html__( 'No any import log yet', 'crane' );
					}
					?>

                </div>
            </div>
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
	}
}
